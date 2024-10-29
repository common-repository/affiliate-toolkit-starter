<?php

class ATKP_Review_Crawler {

	protected $api_country;

	public function __construct($country) {

		$this->api_country = $country;
	}

	public function get_data( $asin, $rocket_scrape_key = '') {

		if ( empty( $asin ) || empty ( $this->api_country ) )
			return null;

		$rating = false;

		$url = 'https://www.amazon.' . $this->api_country . '/product-reviews/' . $asin;

		if($rocket_scrape_key != '') {
			$url = 'https://api.rocketscrape.com/?apiKey=' . $rocket_scrape_key . '&url=' . $url;

		}

		// First try: wp_remote_get
		if ( function_exists( 'wp_remote_get' ) ) {

			$response = wp_remote_get( $url );
			$statusCode = null;

			if ( function_exists( 'is_wp_error' ) && ! is_wp_error( $response ) ) {

				// echo $response['response']['message'];

				// Success
				if ( isset( $response['response']['code'] ) ) {
					$statusCode = $response['response']['code'];
				}

				if ( '200' == $statusCode ) {
					$page = $response['body'];
				}
			}
		}


		if ( ! empty ( $page ) ) {
			$rating = $this->extract_data_from_html( $page );
		}

		// Fallback if no reviews are available
		if ( $rating === false ) {

			if ( ini_get('allow_url_fopen') ) {

				try {
					// Trying to use file_get_contents
					$opts = array(
						'http'=>array(
							'header' => 'Connection: close',
							'ignore_errors' => true
						)
					);
					$context = stream_context_create($opts);
					@$page = file_get_contents($url, false, $context);

					if ( ! empty( $page ) ) {
						$rating = $this->extract_data_from_html( $page );
					}

				} catch(Exception $ex) {
					// Do nothing
				}
			}
		}

		return $rating;
	}

	/**
	 * Extract data from HTML
	 *
	 * @param $html
	 * @return array
	 */
	private function extract_data_from_html( $html ) {

		$data = array(
			'rating' => 0,
			'reviews' => 0
		);

		if ( ! class_exists( 'DomDocument' ) || ! class_exists( 'DOMXPath' ) || ! function_exists( 'libxml_use_internal_errors' ) )
			return $data;

		libxml_use_internal_errors(true);

		$DomDocument = new DomDocument();
		$DomDocument->loadHTML( $html );
		$DomXPath = new DOMXPath( $DomDocument );

		$ratingNodeList = $DomXPath->query( "//i[contains(@class, 'averageStarRating')]" );

		if ( ! empty ( $ratingNodeList ) ) {

			foreach ( $ratingNodeList as $node ) {
				$string = $node->nodeValue; // Returns "4,6 von 5 Sternen"
				$string_array = explode(' ', $string ); // Explode after first white space, to get the rating only
				$rating = $string_array[0];
				$rating = str_replace(',','.', $rating ); // Replace comma with dot formatting
				$data['rating'] = $rating;
				break;
			}
		}

		$reviewsNodeList = $DomXPath->query( "//div[contains(@class, 'averageStarRatingNumerical')]" );

		if ( ! empty ( $reviewsNodeList ) ) {

			foreach ( $reviewsNodeList as $node ) {
				$string = $node->nodeValue;
				$string = preg_replace('/\D/', '', $string);
				$data['reviews'] = $string;
				break;
			}
		}

		return $data;
	}
}