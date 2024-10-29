<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class atkp_curl_helper {
	private $ch;
	private $cookie_path;
	private $agent;

	// userId will be used later to keep multiple users logged
	// into ebay site at one time.
	public function __construct( $userId = 'default' ) {
		$this->cookie_path = wp_upload_dir()['basedir'] . '/atkp-cookies/' . $userId . '.txt';

		if ( ! file_exists( $this->cookie_path ) ) {
			mkdir( $this->cookie_path, 0777, true );
		}

		$this->agent = "Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)";
	}

	private function init() {
		$this->ch = curl_init();
	}

	private function close() {
		curl_close( $this->ch );
	}

	// Set cURL options
	private function setOptions( $submit_url ) {
		$headers[] = "Accept: */*";
		$headers[] = "Connection: Keep-Alive";
		curl_setopt( $this->ch, CURLOPT_URL, $submit_url );
		curl_setopt( $this->ch, CURLOPT_USERAGENT, $this->agent );
		curl_setopt( $this->ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $this->ch, CURLOPT_FOLLOWLOCATION, 1 );
		curl_setopt( $this->ch, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $this->ch, CURLOPT_HTTPHEADER, $headers );
		curl_setopt( $this->ch, CURLOPT_COOKIEFILE, $this->cookie_path );
		curl_setopt( $this->ch, CURLOPT_COOKIEJAR, $this->cookie_path );
	}

	// Grab initial cookie data
	public function curl_cookie_set( $submit_url ) {
		$this->init();
		$this->setOptions( $submit_url );
		curl_exec( $this->ch );
		echo esc_html__( curl_error( $this->ch ), ATKP_PLUGIN_PREFIX );
	}

	// Grab hidden fields
	public function get_form_fields( $submit_url ) {
		curl_setopt( $this->ch, CURLOPT_URL, $submit_url );
		$result = curl_exec( $this->ch );
		echo esc_html__( curl_error( $this->ch ), ATKP_PLUGIN_PREFIX );

		return $this->getFormFields( $result );
	}

	// Send login data
	public function curl_post_request( $referer, $submit_url, $data ) {
		$post = http_build_query( $data );
		curl_setopt( $this->ch, CURLOPT_URL, $submit_url );
		curl_setopt( $this->ch, CURLOPT_POST, 1 );
		curl_setopt( $this->ch, CURLOPT_POSTFIELDS, $post );
		curl_setopt( $this->ch, CURLOPT_REFERER, $referer );
		$result = curl_exec( $this->ch );
		echo esc_html__( curl_error( $this->ch ), ATKP_PLUGIN_PREFIX );
		$this->close();

		return $result;
	}

	// Show the logged in "My eBay" or any other page
	public function show_page( $submit_url ) {
		curl_setopt( $this->ch, CURLOPT_URL, $submit_url );
		$result = curl_exec( $this->ch );
		echo esc_html__( curl_error( $this->ch ), ATKP_PLUGIN_PREFIX );

		return $result;
	}

	// Used to parse out form
	private function getFormFields( $data ) {
		if ( preg_match( '/(<form name="SignInForm".*?<\/form>)/is', $data, $matches ) ) {
			$inputs = $this->getInputs( $matches[1] );

			return $inputs;
		} else {
			die( 'Form not found.' );
		}
	}

	// Used to parse out hidden field names and values
	private function getInputs( $form ) {
		$inputs   = array();
		$elements = preg_match_all( '/(<input[^>]+>)/is', $form, $matches );

		if ( $elements > 0 ) {
			for ( $i = 0; $i < $elements; $i ++ ) {
				$el = preg_replace( '/\s{2,}/', ' ', $matches[1][ $i ] );

				if ( preg_match( '/name=(?:["\'])?([^"\'\s]*)/i', $el, $name ) ) {
					$name  = $name[1];
					$value = '';

					if ( preg_match( '/value=(?:["\'])?([^"\'\s]*)/i', $el, $value ) ) {
						$value = $value[1];
					}

					$inputs[ $name ] = $value;
				}
			}
		}

		return $inputs;
	}

	// Destroy cookie and close curl.
	public function curl_clean() {
		// cleans and closes the curl connection
		if ( file_exists( $this->cookie_path ) ) {
			unlink( $this->cookie_path );
		}
		if ( $this->ch != '' ) {
			curl_close( $this->ch );
		}
	}
}

?>