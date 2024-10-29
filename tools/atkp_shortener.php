<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class atkp_shortener {

	/**
	 * This function is for generating short links
	 *
	 * @param string $url
	 * @param string $url_title
	 * @param atkp_redirection_type|int $shortener_id
	 * @param string $api_key
	 *
	 * @return string
	 */
	public function shorten_url( string $url, string $url_title, $shortener_id, string $api_key ) {

		switch ( $shortener_id ) {
			case atkp_redirection_type::BIT_LY:

				if ( $api_key == '' ) {
					return $url;
				}

				$apiv4 = 'https://api-ssl.bitly.com/v4/bitlinks';

				$data    = array(
					'long_url' => $url,
					'title'    => $url_title
				);
				$payload = json_encode( $data );

				$header = array(
					'Authorization: Bearer ' . $api_key,
					'Content-Type: application/json',
					'Content-Length: ' . strlen( $payload )
				);

				$ch = curl_init( $apiv4 );
				curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, "POST" );
				curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload );
				curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
				curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
				$result       = curl_exec( $ch );
				$resultToJson = json_decode( $result );

				if ( isset( $resultToJson->link ) ) {
					$url = $resultToJson->link;
				}

				break;
		}

		return $url;
	}


}


