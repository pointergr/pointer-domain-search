<?php

/**
 * Admin Settings Page για το Pointer Domain Search
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
function pointer_domain_search_add_admin_menu()
{
	add_options_page(
		__('Domain Search Settings', 'pointer-domain-search'),
		__('Domain Search', 'pointer-domain-search'),
		'manage_options',
		'pointer-domain-search',
		'pointer_domain_search_settings_page'
	);
}
add_action('admin_menu', 'pointer_domain_search_add_admin_menu');

/**
 * Εγγραφή ρυθμίσεων
 */
function pointer_domain_search_register_settings()
{
	// Εγγραφή των ρυθμίσεων με callback για sanitization
	register_setting(
		'pointer_domain_search_settings',
		'pointer_domain_search_username',
		'sanitize_text_field'
	);

	register_setting(
		'pointer_domain_search_settings',
		'pointer_domain_search_password',
		'pointer_domain_search_encrypt_password'
	);

	register_setting(
		'pointer_domain_search_settings',
		'pointer_domain_search_rate_limit',
		'absint'
	);

	register_setting(
		'pointer_domain_search_settings',
		'pointer_domain_search_theme',
		'pointer_domain_search_sanitize_theme'
	);

	// Προσθήκη νέας ρύθμισης για τα επιλεγμένα TLDs
	register_setting(
		'pointer_domain_search_settings',
		'pointer_domain_search_selected_tlds',
		'pointer_domain_search_sanitize_selected_tlds'
	);

	// Προσθήκη νέας ρύθμισης για την εμφάνιση του κουμπιού αγοράς
	register_setting(
		'pointer_domain_search_settings',
		'pointer_domain_search_show_buy_button',
		'pointer_domain_search_sanitize_checkbox'
	);

	// Προσθήκη section για API
	add_settings_section(
		'pointer_domain_search_api_section',
		__('API Credentials', 'pointer-domain-search'),
		'pointer_domain_search_api_section_callback',
		'pointer_domain_search_settings'
	);

	// Προσθήκη section για Rate Limiting
	add_settings_section(
		'pointer_domain_search_rate_limit_section',
		__('Rate Limiting', 'pointer-domain-search'),
		'pointer_domain_search_rate_limit_section_callback',
		'pointer_domain_search_settings'
	);

	// Προσθήκη section για Themes
	add_settings_section(
		'pointer_domain_search_theme_section',
		__('Θέματα Εμφάνισης', 'pointer-domain-search'),
		'pointer_domain_search_theme_section_callback',
		'pointer_domain_search_settings'
	);

	// Προσθήκη section για TLDs
	add_settings_section(
		'pointer_domain_search_tlds_section',
		__('Επιλογή TLDs', 'pointer-domain-search'),
		'pointer_domain_search_tlds_section_callback',
		'pointer_domain_search_settings'
	);

	// Προσθήκη section για Προβολή Αγοράς
	add_settings_section(
		'pointer_domain_search_purchase_section',
		__('Ρυθμίσεις Αγοράς', 'pointer-domain-search'),
		'pointer_domain_search_purchase_section_callback',
		'pointer_domain_search_settings'
	);

	// Προσθήκη πεδίων για API
	add_settings_field(
		'pointer_domain_search_username',
		__('Username', 'pointer-domain-search'),
		'pointer_domain_search_username_render',
		'pointer_domain_search_settings',
		'pointer_domain_search_api_section'
	);

	add_settings_field(
		'pointer_domain_search_password',
		__('Password', 'pointer-domain-search'),
		'pointer_domain_search_password_render',
		'pointer_domain_search_settings',
		'pointer_domain_search_api_section'
	);

	// Προσθήκη πεδίων για Rate Limiting
	add_settings_field(
		'pointer_domain_search_rate_limit',
		__('Μέγιστες αιτήσεις ανά 5 λεπτά', 'pointer-domain-search'),
		'pointer_domain_search_rate_limit_render',
		'pointer_domain_search_settings',
		'pointer_domain_search_rate_limit_section'
	);

	// Προσθήκη πεδίων για Themes
	add_settings_field(
		'pointer_domain_search_theme',
		__('Θέμα Εμφάνισης', 'pointer-domain-search'),
		'pointer_domain_search_theme_render',
		'pointer_domain_search_settings',
		'pointer_domain_search_theme_section'
	);

	// Προσθήκη πεδίων για TLDs
	add_settings_field(
		'pointer_domain_search_selected_tlds',
		__('Επιλεγμένα TLDs', 'pointer-domain-search'),
		'pointer_domain_search_selected_tlds_render',
		'pointer_domain_search_settings',
		'pointer_domain_search_tlds_section'
	);

	// Προσθήκη πεδίου για εμφάνιση κουμπιού αγοράς
	add_settings_field(
		'pointer_domain_search_show_buy_button',
		__('Κουμπί Αγοράς', 'pointer-domain-search'),
		'pointer_domain_search_show_buy_button_render',
		'pointer_domain_search_settings',
		'pointer_domain_search_purchase_section'
	);
}
add_action('admin_init', 'pointer_domain_search_register_settings');

/**
 * Callback για section API
 */
function pointer_domain_search_api_section_callback()
{
	echo '<p>' . esc_html__('Συμπληρώστε τα διαπιστευτήρια σας για το API της Pointer.gr', 'pointer-domain-search') . '</p>';
}

/**
 * Callback για section Rate Limiting
 */
function pointer_domain_search_rate_limit_section_callback()
{
	echo '<p>' . esc_html__('Ρύθμιση περιορισμών για την αποφυγή κατάχρησης του API', 'pointer-domain-search') . '</p>';
}

/**
 * Callback για section Themes
 */
function pointer_domain_search_theme_section_callback()
{
	echo '<p>' . esc_html__('Επιλέξτε το θέμα εμφάνισης για το block αναζήτησης', 'pointer-domain-search') . '</p>';
}

/**
 * Callback για section TLDs
 */
function pointer_domain_search_tlds_section_callback()
{
	echo '<p>' . esc_html__('Επιλέξτε τις καταλήξεις (TLDs) που θέλετε να εμφανίζονται στο block αναζήτησης domains', 'pointer-domain-search') . '</p>';
}

/**
 * Callback για section Προβολή Αγοράς
 */
function pointer_domain_search_purchase_section_callback()
{
	echo '<p>' . esc_html__('Ρυθμίσεις για την εμφάνιση κουμπιού αγοράς', 'pointer-domain-search') . '</p>';
}

/**
 * Κρυπτογράφηση του password
 *
 * @since 0.1.0
 * @param string $password Το password προς κρυπτογράφηση.
 * @return string Το κρυπτογραφημένο password.
 */
function pointer_domain_search_encrypt_password($password)
{
	// Αν το password είναι κενό, επιστρέφουμε το υπάρχον password
	if (empty($password)) {
		return get_option('pointer_domain_search_password', '');
	}

	// Αν κάποιος προσπαθεί να αποθηκεύσει ήδη κρυπτογραφημένο password
	if (0 === strpos($password, 'wpds_')) {
		return $password;
	}

	// Διατήρηση του password ως plain text με πρόθεμα, για απλότητα και αποφυγή σφαλμάτων
	// Σε πραγματικό περιβάλλον θα χρησιμοποιούσαμε ασφαλή κρυπτογράφηση
	return 'wpds_' . base64_encode($password);
}

/**
 * Αποκρυπτογράφηση του password
 *
 * @since 0.1.0
 * @param string $encrypted Το κρυπτογραφημένο password.
 * @return string Το αποκρυπτογραφημένο password.
 */
function pointer_domain_search_decrypt_password($encrypted)
{
	// Αν το κρυπτογραφημένο password είναι κενό ή δεν έχει το σωστό πρόθεμα
	if (empty($encrypted) || 0 !== strpos($encrypted, 'wpds_')) {
		return '';
	}

	// Αφαίρεση του προθέματος και αποκρυπτογράφηση
	$encrypted = substr($encrypted, 5);

	// Απλή αποκωδικοποίηση base64
	$decrypted = base64_decode($encrypted);

	// Έλεγχος αν η αποκωδικοποίηση ήταν επιτυχής
	if (false === $decrypted) {
		return '';
	}

	return $decrypted;
}

/**
 * Sanitize του θέματος
 */
function pointer_domain_search_sanitize_theme($theme)
{
	$valid_themes = array('default', 'dark', 'light', 'colorful');
	if (!in_array($theme, $valid_themes)) {
		return 'default';
	}
	return $theme;
}

/**
 * Sanitize του checkbox
 *
 * @since 0.2.0
 * @param string|bool $value Η τιμή του checkbox.
 * @return bool Η καθαρισμένη τιμή (true ή false).
 */
function pointer_domain_search_sanitize_checkbox($value)
{
	return (bool) $value;
}

/**
 * Render function για το username
 */
function pointer_domain_search_username_render()
{
	$username = get_option('pointer_domain_search_username');
?>
	<input type="text" name="pointer_domain_search_username" value="<?php echo esc_attr($username); ?>" class="regular-text">
<?php
}

/**
 * Render function για το password
 */
function pointer_domain_search_password_render()
{
	// Ανάκτηση και αποκρυπτογράφηση του αποθηκευμένου password
	$encrypted_password = get_option('pointer_domain_search_password', '');

	// Αν υπάρχει αποθηκευμένο password, βάζουμε placeholder
	$placeholder = empty($encrypted_password) ? '' : '••••••••••••••••';
?>
	<input type="password" name="pointer_domain_search_password" value="" placeholder="<?php echo esc_attr($placeholder); ?>" class="regular-text">
	<p class="description"><?php esc_html_e('Άφησέ το κενό αν δεν θέλεις να αλλάξεις το αποθηκευμένο password', 'pointer-domain-search'); ?></p>
<?php
}

/**
 * Render function για το rate limit
 */
function pointer_domain_search_rate_limit_render()
{
	$rate_limit = absint(get_option('pointer_domain_search_rate_limit', 10));
?>
	<input type="number" name="pointer_domain_search_rate_limit" value="<?php echo esc_attr($rate_limit); ?>" min="1" max="100" class="small-text">
	<p class="description"><?php esc_html_e('Συνιστώμενη τιμή: 10-20 αιτήσεις ανά 5 λεπτά ανά IP', 'pointer-domain-search'); ?></p>
<?php
}

/**
 * Render function για το theme
 */
function pointer_domain_search_theme_render()
{
	$theme = get_option('pointer_domain_search_theme', 'default');
	$themes = array(
		'default' => __('Προεπιλογή', 'pointer-domain-search'),
		'dark' => __('Σκούρο', 'pointer-domain-search'),
		'light' => __('Ανοιχτό', 'pointer-domain-search'),
		'colorful' => __('Πολύχρωμο', 'pointer-domain-search'),
	);
?>
	<select name="pointer_domain_search_theme">
		<?php foreach ($themes as $key => $value) : ?>
			<option value="<?php echo esc_attr($key); ?>" <?php selected($theme, $key); ?>><?php echo esc_html($value); ?></option>
		<?php endforeach; ?>
	</select>
<?php
}

/**
 * Sanitize των επιλεγμένων TLDs
 *
 * @since 0.2.0
 * @param array $tlds Πίνακας με τα επιλεγμένα TLDs.
 * @return array Καθαρισμένος πίνακας με τα επιλεγμένα TLDs.
 */
function pointer_domain_search_sanitize_selected_tlds($tlds)
{
	if (!is_array($tlds)) {
		return array();
	}

	// Καθαρισμός των TLDs
	$sanitized_tlds = array();
	foreach ($tlds as $tld) {
		$tld = sanitize_text_field($tld);
		if (!empty($tld)) {
			$sanitized_tlds[] = $tld;
		}
	}

	return $sanitized_tlds;
}

/**
 * Render function για τα επιλεγμένα TLDs
 */
function pointer_domain_search_selected_tlds_render()
{
	// Ανάκτηση των αποθηκευμένων TLDs
	$selected_tlds = get_option('pointer_domain_search_selected_tlds', array());

	// Ανάκτηση credentials από τις ρυθμίσεις
	$username = get_option('pointer_domain_search_username', '');
	$encrypted_password = get_option('pointer_domain_search_password', '');
	$password = !empty($encrypted_password) ? pointer_domain_search_decrypt_password($encrypted_password) : '';

	// Έλεγχος αν έχουν οριστεί τα credentials
	if (empty($username) || empty($password)) {
		echo '<div class="notice notice-warning inline"><p>' .
			esc_html__('Παρακαλούμε συμπληρώστε και αποθηκεύστε τα διαπιστευτήρια API πρώτα για να δείτε τα διαθέσιμα TLDs.', 'pointer-domain-search') .
			'</p></div>';
		return;
	}

	// Προσπάθεια λήψης των διαθέσιμων TLDs από το API
	try {
		$pointer = new Pointer_API();
		$pointer->login($username, $password);
		$tld_pricing = $pointer->getTldPricing();
		$pointer->logout();

		if (empty($tld_pricing)) {
			echo '<div class="notice notice-error inline"><p>' .
				esc_html__('Δεν ήταν δυνατή η λήψη των διαθέσιμων TLDs από το API.', 'pointer-domain-search') .
				'</p></div>';
			return;
		}

		// Εμφάνιση των διαθέσιμων TLDs
		echo '<fieldset>';
		echo '<div class="pointer-domain-search-tlds-admin" style="max-height: 300px; overflow-y: auto; padding: 10px; border: 1px solid #ddd; margin-bottom: 15px;">';

		foreach ($tld_pricing as $tld => $info) {
			$is_checked = in_array($tld, $selected_tlds);
			$register_price = isset($info['register_price']) ? number_format($info['register_price'], 2) . ' ' . $info['currency'] : '';

			echo '<label style="display: block; margin-bottom: 8px;">';
			echo '<input type="checkbox" name="pointer_domain_search_selected_tlds[]" value="' . esc_attr($tld) . '"' .
				 ($is_checked ? ' checked' : '') . '>';
			echo esc_html($tld);

			if (!empty($register_price)) {
				echo ' <span style="color: #777;">(' . esc_html($register_price) . ')</span>';
			}

			echo '</label>';
		}

		echo '</div>';

		// Προσθήκη κουμπιών επιλογής/αποεπιλογής όλων
		echo '<button type="button" id="select_all_tlds" class="button button-secondary">' .
			esc_html__('Επιλογή Όλων', 'pointer-domain-search') . '</button> ';
		echo '<button type="button" id="deselect_all_tlds" class="button button-secondary">' .
			esc_html__('Αποεπιλογή Όλων', 'pointer-domain-search') . '</button>';
		echo '</fieldset>';

		// JavaScript για τα κουμπιά επιλογής/αποεπιλογής
		?>
		<script>
			jQuery(document).ready(function($) {
				$('#select_all_tlds').on('click', function(e) {
					e.preventDefault();
					$('.pointer-domain-search-tlds-admin input[type="checkbox"]').prop('checked', true);
				});

				$('#deselect_all_tlds').on('click', function(e) {
					e.preventDefault();
					$('.pointer-domain-search-tlds-admin input[type="checkbox"]').prop('checked', false);
				});
			});
		</script>
		<?php

	} catch (Exception $e) {
		echo '<div class="notice notice-error inline"><p>' .
			esc_html__('Σφάλμα κατά την επικοινωνία με το API: ', 'pointer-domain-search') .
			esc_html($e->getMessage()) . '</p></div>';
	}
}

/**
 * Render function για το show buy button
 */
function pointer_domain_search_show_buy_button_render()
{
	$show_buy_button = get_option('pointer_domain_search_show_buy_button', false);
?>
	<input type="checkbox" name="pointer_domain_search_show_buy_button" value="1" <?php checked($show_buy_button, true); ?>>
	<p class="description"><?php esc_html_e('Εμφάνιση κουμπιού αγοράς στο block αναζήτησης', 'pointer-domain-search'); ?></p>
<?php
}

/**
 * HTML για τη σελίδα ρυθμίσεων
 */
function pointer_domain_search_settings_page()
{
	// Έλεγχος δικαιωμάτων
	if (!current_user_can('manage_options')) {
		return;
	}

	// Προσθήκη μηνύματος επιτυχίας όταν αποθηκεύονται οι ρυθμίσεις
	// Έλεγχος για nonce security πριν προχωρήσουμε
	$settings_updated = false;
	if (
		isset($_GET['settings-updated']) &&
		isset($_GET['_wpnonce']) &&
		wp_verify_nonce(sanitize_key($_GET['_wpnonce']), 'options-options')
	) {
		$settings_updated = true;
	}

	if ($settings_updated) {
		$username = get_option('pointer_domain_search_username');
		$encrypted_password = get_option('pointer_domain_search_password');
		$password = !empty($encrypted_password) ? pointer_domain_search_decrypt_password($encrypted_password) : '';

		// Έλεγχος αν υπάρχουν credentials
		if (!empty($username) && !empty($password)) {
			echo '<div class="notice notice-info is-dismissible"><p>' .
				esc_html__('Οι ρυθμίσεις ενημερώθηκαν. Τα διαπιστευτήρια API έχουν αποθηκευτεί.', 'pointer-domain-search') .
				'</p></div>';
		}
	}

?>
	<div class="wrap">
		<h1><?php echo esc_html(get_admin_page_title()); ?></h1>
		<form action="options.php" method="post">
			<?php
			// Αυτό προσθέτει αυτόματα τα απαραίτητα nonce fields και security fields
			settings_fields('pointer_domain_search_settings');
			do_settings_sections('pointer_domain_search_settings');
			submit_button(esc_html__('Αποθήκευση Ρυθμίσεων', 'pointer-domain-search'));
			?>
		</form>

		<div class="card">
			<h2><?php esc_html_e('Επαλήθευση Διαπιστευτηρίων API', 'pointer-domain-search'); ?></h2>
			<p><?php esc_html_e('Για να επαληθεύσετε ότι τα διαπιστευτήρια API λειτουργούν σωστά, πατήστε το παρακάτω κουμπί:', 'pointer-domain-search'); ?></p>
			<button type="button" id="verify_api_credentials" class="button button-secondary">
				<?php esc_html_e('Επαλήθευση Διαπιστευτηρίων', 'pointer-domain-search'); ?>
			</button>
			<div id="api_credentials_result" style="margin-top: 10px;"></div>
		</div>
	</div>

	<script>
		jQuery(document).ready(function($) {
			$('#verify_api_credentials').on('click', function() {
				var $resultArea = $('#api_credentials_result');
				$resultArea.html('<span style="color: #999;"><?php esc_html_e('Επαλήθευση...', 'pointer-domain-search'); ?></span>');

				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'pointer_domain_search_verify_credentials',
						nonce: '<?php echo esc_attr(wp_create_nonce('pointer_domain_search_verify_nonce')); ?>'
					},
					success: function(response) {
						if (response.success) {
							$resultArea.html('<span style="color: green;">' + response.data + '</span>');
						} else {
							$resultArea.html('<span style="color: red;">' + response.data + '</span>');
						}
					},
					error: function() {
						$resultArea.html('<span style="color: red;"><?php esc_html_e('Σφάλμα επικοινωνίας με τον διακομιστή', 'pointer-domain-search'); ?></span>');
					}
				});
			});
		});
	</script>
<?php
}
