<?php

/**
 * Plugin Name:       Wp Domain Search
 * Description:       WP block για αναζήτηση domain names μέσω της υπηρεσίας Pointer.gr.
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

if (! defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

// Ορισμός σταθερών
define('WP_DOMAIN_SEARCH_VERSION', '0.1.0');
define('WP_DOMAIN_SEARCH_PATH', plugin_dir_path(__FILE__));

// Συμπερίληψη του Pointer API
require_once plugin_dir_path(__FILE__) . 'includes/pointer-api.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin-settings.php';

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @since 0.1.0
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function create_block_wp_domain_search_block_init()
{
	register_block_type(__DIR__ . '/build/wp-domain-search');

	// Localize script for AJAX URL
	wp_localize_script(
		'create-block-wp-domain-search-view-script',
		'wpDomainSearch',
		array(
			'ajaxUrl' => admin_url('admin-ajax.php'),
			'styles'  => get_option('wp_domain_search_theme', 'default'),
			'nonce'   => wp_create_nonce('wp_domain_search_nonce'),
		)
	);
}
add_action('init', 'create_block_wp_domain_search_block_init');

/**
 * AJAX Handler για επαλήθευση των διαπιστευτηρίων API
 *
 * @since 0.1.0
 * @return void
 */
function wp_domain_search_verify_credentials()
{
	// Έλεγχος nonce για ασφάλεια
	if (! check_ajax_referer('wp_domain_search_verify_nonce', 'nonce', false)) {
		wp_send_json_error(__('Σφάλμα ασφαλείας. Παρακαλούμε ανανεώστε τη σελίδα και δοκιμάστε ξανά.', 'wp-domain-search'));
		return;
	}

	// Ανάκτηση credentials από τις ρυθμίσεις
	$username          = get_option('wp_domain_search_username', '');
	$encrypted_password = get_option('wp_domain_search_password', '');
	$password          = '';

	// Αποκρυπτογράφηση του password αν υπάρχει
	if (! empty($encrypted_password)) {
		$password = wp_domain_search_decrypt_password($encrypted_password);
	}

	// Έλεγχος αν έχουν οριστεί τα credentials
	if (empty($username) || empty($password)) {
		wp_send_json_error(__('Τα διαπιστευτήρια API δεν έχουν οριστεί. Παρακαλούμε συμπληρώστε τα παραπάνω και αποθηκεύστε τις ρυθμίσεις.', 'wp-domain-search'));
		return;
	}

	try {
		// Δημιουργία νέου αντικειμένου Pointer API
		$pointer = new Pointer_API();

		// Σύνδεση στο API
		$key = $pointer->login($username, $password);

		// Αποσύνδεση από το API
		$pointer->logout();

		if (! empty($key)) {
			wp_send_json_success(__('Η σύνδεση με το API της Pointer.gr ήταν επιτυχής!', 'wp-domain-search'));
		} else {
			wp_send_json_error(__('Αποτυχία σύνδεσης. Ο server επέστρεψε κενό κλειδί API.', 'wp-domain-search'));
		}
	} catch (Exception $e) {
		wp_send_json_error(__('Σφάλμα API: ', 'wp-domain-search') . $e->getMessage());
	}
}
add_action('wp_ajax_wp_domain_search_verify_credentials', 'wp_domain_search_verify_credentials');

/**
 * Handle AJAX domain search request
 *
 * @since 0.1.0
 * @return void
 */
function wp_domain_search_ajax_handler()
{
	// Έλεγχος nonce για ασφάλεια
	if (! check_ajax_referer('wp_domain_search_nonce', 'nonce', false)) {
		wp_send_json_error('Σφάλμα ασφαλείας. Παρακαλούμε ανανεώστε τη σελίδα και δοκιμάστε ξανά.');
	}

	// Έλεγχος rate limiting
	if (! wp_domain_search_check_rate_limit()) {
		wp_send_json_error('Πολλές αιτήσεις. Παρακαλούμε δοκιμάστε ξανά σε λίγα λεπτά.');
	}

	// Έλεγχος αν έχουν σταλεί όλα τα απαραίτητα δεδομένα
	if (empty($_POST['domain']) || empty($_POST['tlds'])) {
		wp_send_json_error('Λείπουν απαραίτητα δεδομένα.');
	}

	// Ανάκτηση και καθαρισμός των δεδομένων
	$domain = sanitize_text_field(wp_unslash($_POST['domain']));
	$tlds_json = sanitize_text_field(wp_unslash($_POST['tlds']));
	$tlds = json_decode($tlds_json, true);

	if (! is_array($tlds)) {
		wp_send_json_error('Μη έγκυρα TLDs.');
	}

	// Επιπλέον καθαρισμός κάθε στοιχείου του πίνακα
	$tlds = array_map('sanitize_text_field', $tlds);

	// Ανάκτηση credentials από τις ρυθμίσεις
	$username          = get_option('wp_domain_search_username', '');
	$encrypted_password = get_option('wp_domain_search_password', '');
	$password          = '';

	// Debug info
	if (current_user_can('manage_options')) {
		if (defined('WP_DEBUG') && WP_DEBUG === true) {
			// phpcs:disable WordPress.PHP.DevelopmentFunctions
			error_log('Encrypted password from options: ' . $encrypted_password);
			// phpcs:enable
		}
	}

	// Αποκρυπτογράφηση του password αν υπάρχει
	if (! empty($encrypted_password)) {
		$password = wp_domain_search_decrypt_password($encrypted_password);
		// Debug info
		if (current_user_can('manage_options')) {
			if (defined('WP_DEBUG') && WP_DEBUG === true) {
				// phpcs:disable WordPress.PHP.DevelopmentFunctions
				error_log('Decrypted password length: ' . strlen($password));
				// phpcs:enable
			}
		}
	}

	// Έλεγχος αν έχουν οριστεί τα credentials
	if (empty($username) || empty($password)) {
		wp_send_json_error('Δεν έχουν οριστεί τα διαπιστευτήρια API. Παρακαλούμε επικοινωνήστε με τον διαχειριστή.');
	}

	try {
		// Δημιουργία νέου αντικειμένου Pointer API
		$pointer = new Pointer_API();

		// Σύνδεση στο API
		$pointer->login($username, $password);

		// Θέλουμε να αφαιρρέσουμε το tld αν υπάρχει από το domain
		$domain = explode('.', $domain);
		$domain = $domain[0];

		// Έλεγχος rate limit
		if (! wp_domain_search_check_rate_limit()) {
			wp_send_json_error('Έχετε υπερβεί το όριο αιτημάτων. Παρακαλώ προσπαθήστε ξανά αργότερα.');
		}

		// Αναζήτηση διαθεσιμότητας domain
		$results = $pointer->domainCheck($domain, $tlds);

		// Αποσύνδεση από το API
		$pointer->logout();

		// Καταγραφή της επιτυχημένης αίτησης για rate limiting
		wp_domain_search_log_request();

		// Επιστροφή των αποτελεσμάτων
		wp_send_json_success($results);
	} catch (Exception $e) {
		// Καταγραφή του σφάλματος για debugging
		if (defined('WP_DEBUG') && WP_DEBUG === true) {
			// phpcs:disable WordPress.PHP.DevelopmentFunctions
			error_log('Pointer API Error: ' . $e->getMessage());
			// phpcs:enable
		}
		wp_send_json_error('Σφάλμα API: ' . $e->getMessage());
	}
}

/**
 * Έλεγχος για rate limiting
 *
 * @since 0.1.0
 * @return bool Αν το αίτημα επιτρέπεται
 */
function wp_domain_search_check_rate_limit()
{
	// Παίρνουμε τη διεύθυνση IP του χρήστη
	$user_ip = wp_domain_search_get_user_ip();

	// Ανάκτηση ρυθμίσεων rate limiting
	$max_requests = absint(get_option('wp_domain_search_rate_limit', 10));
	$time_window  = 60 * 5; // 5 λεπτά σε δευτερόλεπτα

	// Παίρνουμε το ιστορικό αιτημάτων για αυτή την IP
	$requests = get_transient('wp_domain_search_requests_' . md5($user_ip));

	if (! $requests) {
		$requests = array();
	}

	// Αφαίρεση παλιών αιτημάτων
	$current_time = time();
	$requests     = array_filter(
		$requests,
		function ($timestamp) use ($current_time, $time_window) {
			return ($current_time - $timestamp) <= $time_window;
		}
	);

	// Έλεγχος αν έχει φτάσει το όριο αιτημάτων
	if (count($requests) >= $max_requests) {
		return false;
	}

	return true;
}

/**
 * Καταγραφή αιτήματος για rate limiting
 *
 * @since 0.1.0
 * @return void
 */
function wp_domain_search_log_request()
{
	// Παίρνουμε τη διεύθυνση IP του χρήστη
	$user_ip = wp_domain_search_get_user_ip();

	// Παίρνουμε το ιστορικό αιτημάτων για αυτή την IP
	$requests = get_transient('wp_domain_search_requests_' . md5($user_ip));

	if (! $requests) {
		$requests = array();
	}

	// Προσθήκη τρέχοντος χρόνου στο ιστορικό
	$requests[] = time();

	// Αποθήκευση του ιστορικού
	set_transient('wp_domain_search_requests_' . md5($user_ip), $requests, 60 * 60); // Αποθήκευση για 1 ώρα
}

/**
 * Ανάκτηση IP διεύθυνσης χρήστη
 *
 * @since 0.1.0
 * @return string IP διεύθυνση χρήστη.
 */
function wp_domain_search_get_user_ip()
{
	if (! empty($_SERVER['HTTP_CLIENT_IP'])) {
		$ip = sanitize_text_field(wp_unslash($_SERVER['HTTP_CLIENT_IP']));
	} elseif (! empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$ip = sanitize_text_field(wp_unslash($_SERVER['HTTP_X_FORWARDED_FOR']));
	} else {
		$ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '';
	}
	return apply_filters('wp_domain_search_user_ip', $ip);
}

/**
 * Εφαρμογή CSS για το επιλεγμένο θέμα
 *
 * @since 0.1.0
 * @return void
 */
function wp_domain_search_enqueue_theme_styles()
{
	$theme        = get_option('wp_domain_search_theme', 'default');
	$valid_themes = array('default', 'dark', 'light', 'colorful');

	// Έλεγχος εγκυρότητας θέματος
	if (! in_array($theme, $valid_themes, true)) {
		$theme = 'default';
	}

	if ('default' !== $theme) {
		// Δημιουργία του URL με βάση την απόλυτη διαδρομή για να αποφύγουμε λάθη
		$css_url = plugin_dir_url(__FILE__) . 'assets/css/themes/' . $theme . '.css';

		// Προσθήκη μοναδικού αναγνωριστικού για να αποφύγουμε την cache
		$cache_buster = filemtime(plugin_dir_path(__FILE__) . 'assets/css/themes/' . $theme . '.css');
		if (! $cache_buster) {
			$cache_buster = time();
		}

		wp_enqueue_style(
			'wp-domain-search-theme-' . $theme,
			$css_url,
			array(),
			$cache_buster
		);
	}
}
add_action('wp_enqueue_scripts', 'wp_domain_search_enqueue_theme_styles');

/**
 * Εκκίνηση του i18n
 *
 * @since 0.1.0
 * @return void
 */
function wp_domain_search_load_textdomain()
{
	load_plugin_textdomain(
		'wp-domain-search',
		false,
		dirname(plugin_basename(__FILE__)) . '/languages'
	);
}
add_action('plugins_loaded', 'wp_domain_search_load_textdomain');

// Προσθήκη των AJAX endpoints
add_action('wp_ajax_wp_domain_search', 'wp_domain_search_ajax_handler');
add_action('wp_ajax_nopriv_wp_domain_search', 'wp_domain_search_ajax_handler');
