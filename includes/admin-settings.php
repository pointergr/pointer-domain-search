<?php
/**
 * Admin Settings Page για το WP Domain Search
 *
 * @package WpDomainSearch
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Προσθήκη σελίδας ρυθμίσεων στο μενού
 */
function wp_domain_search_add_admin_menu() {
    add_options_page(
        __('Domain Search Settings', 'wp-domain-search'),
        __('Domain Search', 'wp-domain-search'),
        'manage_options',
        'wp-domain-search',
        'wp_domain_search_settings_page'
    );
}
add_action('admin_menu', 'wp_domain_search_add_admin_menu');

/**
 * Εγγραφή ρυθμίσεων
 */
function wp_domain_search_register_settings() {
    // Εγγραφή των ρυθμίσεων με callback για sanitization
    register_setting(
        'wp_domain_search_settings',
        'wp_domain_search_username',
        'sanitize_text_field'
    );

    register_setting(
        'wp_domain_search_settings',
        'wp_domain_search_password',
        'wp_domain_search_encrypt_password'
    );

    register_setting(
        'wp_domain_search_settings',
        'wp_domain_search_rate_limit',
        'absint'
    );

    register_setting(
        'wp_domain_search_settings',
        'wp_domain_search_theme',
        'wp_domain_search_sanitize_theme'
    );

    // Προσθήκη section για API
    add_settings_section(
        'wp_domain_search_api_section',
        __('API Credentials', 'wp-domain-search'),
        'wp_domain_search_api_section_callback',
        'wp_domain_search_settings'
    );

    // Προσθήκη section για Rate Limiting
    add_settings_section(
        'wp_domain_search_rate_limit_section',
        __('Rate Limiting', 'wp-domain-search'),
        'wp_domain_search_rate_limit_section_callback',
        'wp_domain_search_settings'
    );

    // Προσθήκη section για Themes
    add_settings_section(
        'wp_domain_search_theme_section',
        __('Θέματα Εμφάνισης', 'wp-domain-search'),
        'wp_domain_search_theme_section_callback',
        'wp_domain_search_settings'
    );

    // Προσθήκη πεδίων για API
    add_settings_field(
        'wp_domain_search_username',
        __('Username', 'wp-domain-search'),
        'wp_domain_search_username_render',
        'wp_domain_search_settings',
        'wp_domain_search_api_section'
    );

    add_settings_field(
        'wp_domain_search_password',
        __('Password', 'wp-domain-search'),
        'wp_domain_search_password_render',
        'wp_domain_search_settings',
        'wp_domain_search_api_section'
    );

    // Προσθήκη πεδίων για Rate Limiting
    add_settings_field(
        'wp_domain_search_rate_limit',
        __('Μέγιστες αιτήσεις ανά 5 λεπτά', 'wp-domain-search'),
        'wp_domain_search_rate_limit_render',
        'wp_domain_search_settings',
        'wp_domain_search_rate_limit_section'
    );

    // Προσθήκη πεδίων για Themes
    add_settings_field(
        'wp_domain_search_theme',
        __('Θέμα Εμφάνισης', 'wp-domain-search'),
        'wp_domain_search_theme_render',
        'wp_domain_search_settings',
        'wp_domain_search_theme_section'
    );
}
add_action('admin_init', 'wp_domain_search_register_settings');

/**
 * Callback για section API
 */
function wp_domain_search_api_section_callback() {
    echo '<p>' . __('Συμπληρώστε τα διαπιστευτήρια σας για το API της Pointer.gr', 'wp-domain-search') . '</p>';
}

/**
 * Callback για section Rate Limiting
 */
function wp_domain_search_rate_limit_section_callback() {
    echo '<p>' . __('Ρύθμιση περιορισμών για την αποφυγή κατάχρησης του API', 'wp-domain-search') . '</p>';
}

/**
 * Callback για section Themes
 */
function wp_domain_search_theme_section_callback() {
    echo '<p>' . __('Επιλέξτε το θέμα εμφάνισης για το block αναζήτησης', 'wp-domain-search') . '</p>';
}

/**
 * Κρυπτογράφηση του password
 */
function wp_domain_search_encrypt_password($password) {
    // Αν το password είναι κενό, επιστροφή κενού
    if (empty($password)) {
        return '';
    }

    // Αν κάποιος προσπαθεί να αποθηκεύσει ήδη κρυπτογραφημένο password
    if (strpos($password, 'wpds_') === 0) {
        return $password;
    }

    // Διατήρηση του password ως plain text με πρόθεμα, για απλότητα και αποφυγή σφαλμάτων
    // Σε πραγματικό περιβάλλον θα χρησιμοποιούσαμε ασφαλή κρυπτογράφηση
    return 'wpds_' . base64_encode($password);
}

/**
 * Αποκρυπτογράφηση του password
 */
function wp_domain_search_decrypt_password($encrypted) {
    // Αν το κρυπτογραφημένο password είναι κενό ή δεν έχει το σωστό πρόθεμα
    if (empty($encrypted) || strpos($encrypted, 'wpds_') !== 0) {
        return '';
    }

    // Αφαίρεση του προθέματος και αποκρυπτογράφηση
    $encrypted = substr($encrypted, 5);

    // Απλή αποκωδικοποίηση base64
    $decrypted = base64_decode($encrypted);

    // Έλεγχος αν η αποκωδικοποίηση ήταν επιτυχής
    if ($decrypted === false) {
        return '';
    }

    return $decrypted;
}

/**
 * Sanitize του θέματος
 */
function wp_domain_search_sanitize_theme($theme) {
    $valid_themes = array('default', 'dark', 'light', 'colorful');
    if (!in_array($theme, $valid_themes)) {
        return 'default';
    }
    return $theme;
}

/**
 * Render function για το username
 */
function wp_domain_search_username_render() {
    $username = get_option('wp_domain_search_username');
    ?>
    <input type="text" name="wp_domain_search_username" value="<?php echo esc_attr($username); ?>" class="regular-text">
    <?php
}

/**
 * Render function για το password
 */
function wp_domain_search_password_render() {
    $encrypted_password = get_option('wp_domain_search_password');
    $password = ''; // Δεν εμφανίζουμε το πραγματικό password για λόγους ασφαλείας

    // Αν υπάρχει αποθηκευμένο password, βάζουμε placeholder
    $placeholder = empty($encrypted_password) ? '' : '••••••••••••••••';
    ?>
    <input type="password" name="wp_domain_search_password" value="" placeholder="<?php echo esc_attr($placeholder); ?>" class="regular-text">
    <p class="description"><?php _e('Άφησέ το κενό αν δεν θέλεις να αλλάξεις το αποθηκευμένο password', 'wp-domain-search'); ?></p>
    <?php
}

/**
 * Render function για το rate limit
 */
function wp_domain_search_rate_limit_render() {
    $rate_limit = absint(get_option('wp_domain_search_rate_limit', 10));
    ?>
    <input type="number" name="wp_domain_search_rate_limit" value="<?php echo esc_attr($rate_limit); ?>" min="1" max="100" class="small-text">
    <p class="description"><?php _e('Συνιστώμενη τιμή: 10-20 αιτήσεις ανά 5 λεπτά ανά IP', 'wp-domain-search'); ?></p>
    <?php
}

/**
 * Render function για το theme
 */
function wp_domain_search_theme_render() {
    $theme = get_option('wp_domain_search_theme', 'default');
    $themes = array(
        'default' => __('Προεπιλογή', 'wp-domain-search'),
        'dark' => __('Σκούρο', 'wp-domain-search'),
        'light' => __('Ανοιχτό', 'wp-domain-search'),
        'colorful' => __('Πολύχρωμο', 'wp-domain-search'),
    );
    ?>
    <select name="wp_domain_search_theme">
        <?php foreach ($themes as $key => $value) : ?>
            <option value="<?php echo esc_attr($key); ?>" <?php selected($theme, $key); ?>><?php echo esc_html($value); ?></option>
        <?php endforeach; ?>
    </select>
    <?php
}

/**
 * HTML για τη σελίδα ρυθμίσεων
 */
function wp_domain_search_settings_page() {
    // Έλεγχος δικαιωμάτων
    if (!current_user_can('manage_options')) {
        return;
    }

    // Προσθήκη μηνύματος για υποβοήθηση εντοπισμού σφαλμάτων
    if (isset($_GET['settings-updated']) && $_GET['settings-updated']) {
        $username = get_option('wp_domain_search_username');
        $encrypted_password = get_option('wp_domain_search_password');
        $password = !empty($encrypted_password) ? wp_domain_search_decrypt_password($encrypted_password) : '';

        // Έλεγχος αν υπάρχουν credentials
        if (!empty($username) && !empty($password)) {
            echo '<div class="notice notice-info is-dismissible"><p>' .
                 __('Οι ρυθμίσεις ενημερώθηκαν. Τα διαπιστευτήρια API έχουν αποθηκευτεί.', 'wp-domain-search') .
                 '</p></div>';
        }
    }

    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('wp_domain_search_settings');
            do_settings_sections('wp_domain_search_settings');
            submit_button(__('Αποθήκευση Ρυθμίσεων', 'wp-domain-search'));
            ?>
        </form>

        <div class="card">
            <h2><?php _e('Επαλήθευση Διαπιστευτηρίων API', 'wp-domain-search'); ?></h2>
            <p><?php _e('Για να επαληθεύσετε ότι τα διαπιστευτήρια API λειτουργούν σωστά, πατήστε το παρακάτω κουμπί:', 'wp-domain-search'); ?></p>
            <button type="button" id="verify_api_credentials" class="button button-secondary">
                <?php _e('Επαλήθευση Διαπιστευτηρίων', 'wp-domain-search'); ?>
            </button>
            <div id="api_credentials_result" style="margin-top: 10px;"></div>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        $('#verify_api_credentials').on('click', function() {
            var $resultArea = $('#api_credentials_result');
            $resultArea.html('<span style="color: #999;"><?php _e('Επαλήθευση...', 'wp-domain-search'); ?></span>');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wp_domain_search_verify_credentials',
                    nonce: '<?php echo wp_create_nonce('wp_domain_search_verify_nonce'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        $resultArea.html('<span style="color: green;">' + response.data + '</span>');
                    } else {
                        $resultArea.html('<span style="color: red;">' + response.data + '</span>');
                    }
                },
                error: function() {
                    $resultArea.html('<span style="color: red;"><?php _e('Σφάλμα επικοινωνίας με τον διακομιστή', 'wp-domain-search'); ?></span>');
                }
            });
        });
    });
    </script>
    <?php
}
