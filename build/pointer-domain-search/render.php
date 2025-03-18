<?php
/**
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */

// Παίρνουμε τα attributes
$username = isset($attributes['username']) ? $attributes['username'] : '';
$password = isset($attributes['password']) ? $attributes['password'] : '';
$tlds_string = isset($attributes['tlds']) ? $attributes['tlds'] : 'gr|com|net';

// Έλεγχος για κεντρικά credentials
$global_username = get_option('pointer_domain_search_username', '');
$global_password = get_option('pointer_domain_search_password', '');

// Χρήση των κεντρικών credentials αν υπάρχουν
if (!empty($global_username) && !empty($global_password)) {
    $username = $global_username;
    $password = $global_password;
}

// Λήψη των επιλεγμένων TLDs από τις ρυθμίσεις
$selected_tlds = get_option('pointer_domain_search_selected_tlds', array());

// Λήψη της ρύθμισης για το κουμπί αγοράς
$show_buy_button = get_option('pointer_domain_search_show_buy_button', false);

// Αν δεν έχουν επιλεγεί TLDs από τις ρυθμίσεις, χρησιμοποιούμε το tlds από το block
if (empty($selected_tlds)) {
    // Διασπάμε τα TLDs σε πίνακα
    $tlds = explode('|', $tlds_string);
    $selected_tlds = array_map('trim', $tlds);
}

// Αν λείπουν τα credentials δεν εμφανίζουμε τίποτα στον διαχειριστή
if (empty($username) || empty($password)) {
    echo '<div ' . wp_kses_post(get_block_wrapper_attributes()) . '>';
    esc_html_e('Παρακαλούμε ορίστε username και password στις ρυθμίσεις του plugin (Ρυθμίσεις > Domain Search).', 'pointer-domain-search');
    echo '</div>';
    return;
}

// Δημιουργία μοναδικού ID για το block
$block_id = 'pointer-domain-search-' . uniqid();

// Προσθήκη κλάσης για το επιλεγμένο θέμα
$theme = get_option('pointer_domain_search_theme', 'default');
$theme_class = 'pointer-domain-search-theme-' . $theme;

$wrapper_classes = array('pointer-domain-search-wrapper', $theme_class);
?>

<div <?php echo wp_kses_post(get_block_wrapper_attributes(array('class' => implode(' ', $wrapper_classes)))); ?> id="<?php echo esc_attr($block_id); ?>" data-nonce="<?php echo esc_attr(wp_create_nonce('pointer_domain_search_nonce')); ?>" data-show-buy-button="<?php echo $show_buy_button ? '1' : '0'; ?>">
    <div class="pointer-domain-search-form">
        <div class="pointer-domain-search-input-wrap">
            <input type="text" class="pointer-domain-search-input" placeholder="<?php esc_attr_e('Εισάγετε όνομα domain...', 'pointer-domain-search'); ?>" required />
            <button class="pointer-domain-search-button"><?php esc_html_e('Αναζήτηση', 'pointer-domain-search'); ?></button>
        </div>
        <div class="pointer-domain-search-tlds">
            <?php foreach ($selected_tlds as $index => $tld) : ?>
                <?php if (!empty($tld)) : ?>
                    <label class="pointer-domain-search-tld-label">
                        <input type="checkbox" class="pointer-domain-search-tld" value="<?php echo esc_attr($tld); ?>" <?php checked($index, 0); ?> />
                        <?php echo esc_html($tld); ?>
                    </label>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="pointer-domain-search-loading">
        <div class="pointer-domain-search-loading-spinner"></div>
        <p><?php esc_html_e('Αναζήτηση διαθεσιμότητας...', 'pointer-domain-search'); ?></p>
    </div>

    <div class="pointer-domain-search-error"></div>

    <div class="pointer-domain-search-results"></div>
</div>
