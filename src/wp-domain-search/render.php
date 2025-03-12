<?php
/**
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */

// Παίρνουμε τα attributes
$username = isset($attributes['username']) ? $attributes['username'] : '';
$password = isset($attributes['password']) ? $attributes['password'] : '';
$tlds_string = isset($attributes['tlds']) ? $attributes['tlds'] : 'gr|com|net';

// Έλεγχος για κεντρικά credentials
$global_username = get_option('wp_domain_search_username', '');
$global_password = get_option('wp_domain_search_password', '');

// Χρήση των κεντρικών credentials αν υπάρχουν
if (!empty($global_username) && !empty($global_password)) {
    $username = $global_username;
    $password = $global_password;
}

// Διασπάμε τα TLDs σε πίνακα
$tlds = explode('|', $tlds_string);

// Αν λείπουν τα credentials δεν εμφανίζουμε τίποτα στον διαχειριστή
if (empty($username) || empty($password)) {
    echo '<div ' . get_block_wrapper_attributes() . '>';
    esc_html_e('Παρακαλούμε ορίστε username και password στις ρυθμίσεις του block ή στις γενικές ρυθμίσεις του plugin.', 'wp-domain-search');
    echo '</div>';
    return;
}

// Δημιουργία μοναδικού ID για το block
$block_id = 'wp-domain-search-' . uniqid();

// Προσθήκη κλάσης για το επιλεγμένο θέμα
$theme = get_option('wp_domain_search_theme', 'default');
$theme_class = 'wp-domain-search-theme-' . $theme;

$wrapper_classes = array('wp-domain-search-wrapper', $theme_class);
?>

<div <?php echo get_block_wrapper_attributes(array('class' => implode(' ', $wrapper_classes))); ?> id="<?php echo esc_attr($block_id); ?>" data-nonce="<?php echo esc_attr(wp_create_nonce('wp_domain_search_nonce')); ?>">
    <div class="wp-domain-search-form">
        <div class="wp-domain-search-input-wrap">
            <input type="text" class="wp-domain-search-input" placeholder="<?php esc_attr_e('Εισάγετε όνομα domain...', 'wp-domain-search'); ?>" required />
            <button class="wp-domain-search-button"><?php esc_html_e('Αναζήτηση', 'wp-domain-search'); ?></button>
        </div>
        <div class="wp-domain-search-tlds">
            <?php foreach ($tlds as $index => $tld) : ?>
                <?php $tld = trim($tld); ?>
                <?php if (!empty($tld)) : ?>
                    <label class="wp-domain-search-tld-label">
                        <input type="checkbox" class="wp-domain-search-tld" value="<?php echo esc_attr($tld); ?>" <?php checked($index, 0); ?> />
                        .<?php echo esc_html($tld); ?>
                    </label>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="wp-domain-search-loading">
        <div class="wp-domain-search-loading-spinner"></div>
        <p><?php esc_html_e('Αναζήτηση διαθεσιμότητας...', 'wp-domain-search'); ?></p>
    </div>

    <div class="wp-domain-search-error"></div>

    <div class="wp-domain-search-results"></div>
</div>
