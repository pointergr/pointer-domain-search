<?php
/**
 * Pointer API Class
 *
 * @package WpDomainSearch
 * @since 0.1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class pointer_api
 *
 * Χειρίζεται την επικοινωνία με το API της Pointer.gr
 *
 * @since 0.1.0
 */
class Pointer_API {

	/**
	 * Username για το API
	 *
	 * @var string
	 */
	protected $login_username = '';

	/**
	 * Password για το API
	 *
	 * @var string
	 */
	protected $login_password = '';

	/**
	 * Κλειδί API μετά το login
	 *
	 * @var string
	 */
	protected $key;

	/**
	 * Αποστολή αιτήματος στο API
	 *
	 * @since 0.1.0
	 * @param string $xml XML request.
	 * @return string Response from API.
	 * @throws Exception Σε περίπτωση σφάλματος επικοινωνίας.
	 */
	public function request( $xml )
	{
		$url = 'https://www.pointer.gr/api';

		// Χρησιμοποιούμε τις WordPress HTTP λειτουργίες
		$args = array(
			'body'        => $xml,
			'headers'     => array(
				'Content-Type' => 'text/xml',
				'testserver'   => '0', // 0=Normal registry, 1=test registry
			),
			'timeout'     => 20,
			'sslverify'   => true,
		);

		$response = wp_remote_post( $url, $args );

		// Έλεγχος σφαλμάτων HTTP
		if ( is_wp_error( $response ) ) {
			throw new Exception( 'API connection error: ' . esc_html( $response->get_error_message(), ENT_QUOTES, 'UTF-8' ) );
		}

		$http_code = wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );

		// Έλεγχος HTTP status code
		if ( $http_code >= 400 ) {
			throw new Exception( 'API HTTP error: ' . esc_html( $http_code, ENT_QUOTES, 'UTF-8' ) );
		}

		return $body;
	}

	/**
	 * Login στο API
	 *
	 * @since 0.1.0
	 * @param string $username Username.
	 * @param string $password Password.
	 * @return string API key.
	 * @throws Exception Σε περίπτωση σφάλματος login.
	 */
	public function login( $username = null, $password = null )
	{
		// Έλεγχος και καθαρισμός παραμέτρων
		if ( ! is_null( $username ) ) {
			$this->login_username = $username;
		}

		if ( ! is_null( $password ) ) {
			$this->login_password = $password;
		}

		// Έλεγχος αν έχουν οριστεί username και password
		if ( empty( $this->login_username ) || empty( $this->login_password ) ) {
			throw new Exception( 'API credentials are missing or invalid' );
		}

		$chksum = md5( $this->login_username . $this->login_password . 'login' );
		$xml = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
			<pointer>
				<login>
					<password>' . md5( $this->login_password ) . '</password>
				</login>
				<username>' . $this->login_username . '</username>
				<chksum>' . $chksum . '</chksum>
			</pointer>';

		$result = $this->request( $xml );

		$xml = $this->parse_request( $result );
		$xml_result = $xml->xpath( '/pointer/login/key' );

		if ( count( $xml_result ) > 0 ) {
			$this->key = (string) $xml_result[0];
			return $this->key;
		} else {
			throw new Exception( 'Login failed. Please check your API credentials.' );
		}
	}

	/**
	 * Αποσύνδεση από το API
	 *
	 * @since 0.1.0
	 * @return boolean Επιτυχία αποσύνδεσης.
	 */
	public function logout()
	{
		// Έλεγχος αν έχει γίνει login
		if ( empty( $this->key ) ) {
			return false;
		}

		$chksum = md5( $this->login_username . $this->login_password . 'logout' . $this->key );
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
				<pointer>
					<logout />
					<username>' . $this->login_username . '</username>
					<chksum>' . $chksum . '</chksum>
				</pointer>';

		$result = $this->request( $xml );
		$xml = $this->parse_request( $result );
		$xml_result = $xml->xpath( '/pointer/logout/success' );
		if ( count( $xml_result ) > 0 ) {
			return 'true' === (string) $xml_result[0];
		}
		return false;
	}

	/**
	 * Έλεγχος διαθεσιμότητας domain
	 *
	 * @since 0.1.0
	 * @param string $domain Domain name χωρίς TLD.
	 * @param array  $tlds Array με TLDs για έλεγχο.
	 * @return array Αποτελέσματα διαθεσιμότητας.
	 * @throws Exception Σε περίπτωση σφάλματος ελέγχου.
	 */
	public function domainCheck( $domain, $tlds = null )
	{
		// Έλεγχος αν έχει γίνει login
		if ( empty( $this->key ) ) {
			throw new Exception( 'You must login before checking domains' );
		}

		// Έλεγχος παραμέτρων
		if ( empty( $domain ) ) {
			throw new Exception( 'Domain name is required' );
		}

		// Καθαρισμός domain
		$domain = $this->sanitize_domain( $domain );

		// Έλεγχος και αρχικοποίηση TLDs
		if ( ! is_array( $tlds ) ) {
			$tlds = array();
		}

		if ( empty( $tlds ) ) {
			throw new Exception( 'At least one TLD is required' );
		}

		$tld_xml = '';
		foreach ( $tlds as $tld ) {
			// Καθαρισμός TLD
			$tld = $this->sanitize_tld( $tld );
			if ( ! empty( $tld ) ) {
				$tld_xml .= '<tld>' . $tld . '</tld>';
			}
		}

		// Έλεγχος αν έχουν μείνει έγκυρα TLDs
		if ( empty( $tld_xml ) ) {
			throw new Exception( 'No valid TLDs provided' );
		}

		$chksum = md5( $this->login_username . $this->login_password . 'domainCheck' . $this->key );
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
			<pointer>
				<domain-check>
					<tlds>
						' . $tld_xml . '
					</tlds>
					<domains>
						<domain>' . $domain . '</domain>
					</domains>
				</domain-check>
				<username>' . $this->login_username . '</username>
				<chksum>' . $chksum . '</chksum>
			</pointer>';

		$result = $this->request( $xml );
		$xml = $this->parse_request( $result );
		$xml_result = $xml->xpath( '/pointer/login/key' );
		$tmp = $xml->xpath( '/pointer/domain-check/result/item' );
		$arr = array();
		foreach ( $tmp as $tld_result ) {
			$arr[(string) $tld_result->domain] = (string) $tld_result->available;
		}
		return $arr;
	}

	/**
	 * Επεξεργασία απόκρισης XML
	 *
	 * @since 0.1.0
	 * @param string $request_string XML response.
	 * @return SimpleXMLElement Επεξεργασμένο XML.
	 * @throws Exception Σε περίπτωση σφάλματος επεξεργασίας XML.
	 */
	protected function parse_request( $request_string )
	{
		libxml_use_internal_errors( true );
		$xml = simplexml_load_string( $request_string );

		if ( ! $xml ) {
			$errors = libxml_get_errors();
			$error_msg = '';
			foreach ( $errors as $error ) {
				$error_msg .= $error->message . "\n";
			}
			libxml_clear_errors();
			throw new Exception( 'XML parsing error: ' . esc_html( $error_msg, ENT_QUOTES, 'UTF-8' ) );
		}

		$error = $xml->xpath( '/pointer/error' );
		if ( count( $error ) > 0 ) {
			$code = (string) $error[0]->code;
			$message = (string) $error[0]->message;
			throw new Exception( 'API error (' . esc_html( $code, ENT_QUOTES, 'UTF-8' ) . '): ' . esc_html( $message, ENT_QUOTES, 'UTF-8' ) );
		}

		return $xml;
	}

	/**
	 * Καθαρισμός διαπιστευτηρίων
	 *
	 * @since 0.1.0
	 * @param string $credential Username ή password.
	 * @return string Καθαρισμένο credential.
	 */
	protected function sanitize_credential( $credential )
	{
		// Αφαίρεση μη ασφαλών χαρακτήρων
		return preg_replace( '/[^a-zA-Z0-9@._-]/', '', $credential );
	}

	/**
	 * Καθαρισμός domain name
	 *
	 * @since 0.1.0
	 * @param string $domain Domain name.
	 * @return string Καθαρισμένο domain name.
	 */
	protected function sanitize_domain( $domain )
	{
		// Αφαίρεση του TLD αν υπάρχει
		if ( strpos( $domain, '.' ) !== false ) {
			$parts = explode( '.', $domain );
			$domain = $parts[0];
		}

		// Καθαρισμός μη έγκυρων χαρακτήρων
		$domain = preg_replace( '/[^a-z0-9-]/', '', strtolower( $domain ) );

		// Περιορισμός μήκους
		return substr( $domain, 0, 63 );
	}

	/**
	 * Καθαρισμός TLD
	 *
	 * @since 0.1.0
	 * @param string $tld TLD.
	 * @return string Καθαρισμένο TLD.
	 */
	protected function sanitize_tld( $tld )
	{
		// Αφαίρεση της τελείας αν υπάρχει στην αρχή
		$tld = ltrim( $tld, '.' );

		// Καθαρισμός μη έγκυρων χαρακτήρων
		return preg_replace( '/[^a-z0-9.-]/', '', strtolower( $tld ) );
	}
}