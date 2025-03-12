<?php
/**
 * Plugin Name:       Wp Domain Search
 * Description:       WordPress block για αναζήτηση domain names μέσω της υπηρεσίας Pointer.gr.
 * Version:           0.1.0
 * Requires at least: 6.7
 * Requires PHP:      7.4
 * Author:            The WordPress Contributors
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wp-domain-search
 *
 * @package WpDomainSearch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Ορισμός σταθερών
define( 'WP_DOMAIN_SEARCH_VERSION', '0.1.0' );
define( 'WP_DOMAIN_SEARCH_PATH', plugin_dir_path( __FILE__ ) );

// Συμπερίληψη του Pointer API
require_once plugin_dir_path( __FILE__ ) . 'includes/pointer-api.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin-settings.php';

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function create_block_wp_domain_search_block_init() {
	register_block_type( __DIR__ . '/build/wp-domain-search' );

    // Localize script for AJAX URL
    wp_localize_script(
        'create-block-wp-domain-search-view-script',
        'wpDomainSearch',
        array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'styles' => get_option('wp_domain_search_theme', 'default'),
        )
    );
}
add_action( 'init', 'create_block_wp_domain_search_block_init' );

/**
 * Handle AJAX domain search request
 */
function wp_domain_search_ajax_handler() {
    // Έλεγχος nonce για ασφάλεια
    if ( ! check_ajax_referer( 'wp_domain_search_nonce', 'nonce', false ) ) {
        wp_send_json_error( 'Σφάλμα ασφαλείας. Παρακαλούμε ανανεώστε τη σελίδα και δοκιμάστε ξανά.' );
    }

    // Έλεγχος rate limiting
    if (!wp_domain_search_check_rate_limit()) {
        wp_send_json_error( 'Πολλές αιτήσεις. Παρακαλούμε δοκιμάστε ξανά σε λίγα λεπτά.' );
    }

    // Έλεγχος αν έχουν σταλεί όλα τα απαραίτητα δεδομένα
    if ( empty( $_POST['domain'] ) || empty( $_POST['tlds'] ) ) {
        wp_send_json_error( 'Λείπουν απαραίτητα δεδομένα.' );
    }

    // Ανάκτηση και καθαρισμός των δεδομένων
    $domain = sanitize_text_field( wp_unslash( $_POST['domain'] ) );
    $tlds = json_decode( wp_unslash( $_POST['tlds'] ), true );

    if ( ! is_array( $tlds ) ) {
        wp_send_json_error( 'Μη έγκυρα TLDs.' );
    }

    // Καθαρισμός των TLDs για ασφάλεια
    $tlds = array_map( 'sanitize_text_field', $tlds );

    // Ανάκτηση credentials από τις ρυθμίσεις
    $username = get_option('wp_domain_search_username', '');
    $password = get_option('wp_domain_search_password', '');

    // Αν δεν υπάρχουν κεντρικά credentials, ανάκτηση από το block
    if (empty($username) || empty($password)) {
        // Ανάκτηση ρυθμίσεων block για το συγκεκριμένο post
        global $post;
        $blocks = parse_blocks( $post->post_content );

        // Εύρεση του block αναζήτησης και ανάκτηση των credentials
        foreach ( $blocks as $block ) {
            if ( 'create-block/wp-domain-search' === $block['blockName'] ) {
                $username = isset( $block['attrs']['username'] ) ? $block['attrs']['username'] : '';
                $password = isset( $block['attrs']['password'] ) ? $block['attrs']['password'] : '';
                break;
            }
        }
    }

    // Έλεγχος αν έχουν οριστεί τα credentials
    if ( empty( $username ) || empty( $password ) ) {
        wp_send_json_error( 'Δεν έχουν οριστεί τα διαπιστευτήρια API. Παρακαλούμε επικοινωνήστε με τον διαχειριστή.' );
    }

    try {
        // Δημιουργία νέου αντικειμένου Pointer API
        $pointer = new pointer_api();

        // Σύνδεση στο API
        $pointer->login( $username, $password );

        // Αναζήτηση διαθεσιμότητας domain
        $results = $pointer->domainCheck( $domain, $tlds );

        // Αποσύνδεση από το API
        $pointer->logout();

        // Καταγραφή της επιτυχημένης αίτησης για rate limiting
        wp_domain_search_log_request();

        // Επιστροφή των αποτελεσμάτων
        wp_send_json_success( $results );
    } catch ( Exception $e ) {
        wp_send_json_error( 'Σφάλμα API: ' . $e->getMessage() );
    }
}

/**
 * Έλεγχος για rate limiting
 */
function wp_domain_search_check_rate_limit() {
    // Παίρνουμε τη διεύθυνση IP του χρήστη
    $user_ip = wp_domain_search_get_user_ip();

    // Ανάκτηση ρυθμίσεων rate limiting
    $max_requests = get_option('wp_domain_search_rate_limit', 10);
    $time_window = 60 * 5; // 5 λεπτά σε δευτερόλεπτα

    // Παίρνουμε το ιστορικό αιτημάτων για αυτή την IP
    $requests = get_transient('wp_domain_search_requests_' . md5($user_ip));

    if (!$requests) {
        $requests = array();
    }

    // Αφαίρεση παλιών αιτημάτων
    $current_time = time();
    $requests = array_filter($requests, function($timestamp) use ($current_time, $time_window) {
        return ($current_time - $timestamp) <= $time_window;
    });

    // Έλεγχος αν έχει φτάσει το όριο αιτημάτων
    if (count($requests) >= $max_requests) {
        return false;
    }

    return true;
}

/**
 * Καταγραφή αιτήματος για rate limiting
 */
function wp_domain_search_log_request() {
    // Παίρνουμε τη διεύθυνση IP του χρήστη
    $user_ip = wp_domain_search_get_user_ip();

    // Παίρνουμε το ιστορικό αιτημάτων για αυτή την IP
    $requests = get_transient('wp_domain_search_requests_' . md5($user_ip));

    if (!$requests) {
        $requests = array();
    }

    // Προσθήκη τρέχοντος χρόνου στο ιστορικό
    $requests[] = time();

    // Αποθήκευση του ιστορικού
    set_transient('wp_domain_search_requests_' . md5($user_ip), $requests, 60 * 60); // Αποθήκευση για 1 ώρα
}

/**
 * Βοηθητική συνάρτηση για να πάρουμε την IP του χρήστη
 */
function wp_domain_search_get_user_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return apply_filters('wp_domain_search_user_ip', $ip);
}

/**
 * Εφαρμογή CSS για το επιλεγμένο θέμα
 */
function wp_domain_search_enqueue_theme_styles() {
    $theme = get_option('wp_domain_search_theme', 'default');

    if ('default' !== $theme) {
        wp_enqueue_style(
            'wp-domain-search-theme-' . $theme,
            plugin_dir_url(__FILE__) . 'assets/css/themes/' . $theme . '.css',
            array(),
            WP_DOMAIN_SEARCH_VERSION
        );
    }
}
add_action('wp_enqueue_scripts', 'wp_domain_search_enqueue_theme_styles');

/**
 * Εκκίνηση του i18n
 */
function wp_domain_search_load_textdomain() {
    load_plugin_textdomain(
        'wp-domain-search',
        false,
        dirname(plugin_basename(__FILE__)) . '/languages'
    );
}
add_action('plugins_loaded', 'wp_domain_search_load_textdomain');

// Προσθήκη των AJAX endpoints
add_action( 'wp_ajax_wp_domain_search', 'wp_domain_search_ajax_handler' );
add_action( 'wp_ajax_nopriv_wp_domain_search', 'wp_domain_search_ajax_handler' );
