<?php

class ATKP_StoreController {

	public static function get_product_discounts( $override = false ) {
		$cache = $override ? false : get_transient( 'atkp_discount' );

		if ( false === $cache ) {
			$url = ATKP_STORE_URL . '/wp-json/affiliate-toolkit/v1/aktion?version=' . ATKP_UPDATE_VERSION;

			$feed = wp_remote_get( esc_url_raw( $url ), array( 'sslverify' => false ) );

			if ( ! is_wp_error( $feed ) ) {
				if ( isset( $feed['body'] ) && strlen( $feed['body'] ) > 0 ) {
					$cache = json_decode( wp_remote_retrieve_body( $feed ) );

					try {
						if ( isset( $cache->aktion_filename ) && $cache->aktion_filename != '' && $cache->aktion_filecontent ) {
							$upload_dir = wp_upload_dir();

							if ( ! empty( $upload_dir['basedir'] ) ) {
								$user_dirname = $upload_dir['basedir'];
								if ( file_exists( $user_dirname ) ) {
									$user_filename = $user_dirname . '/' . $cache->aktion_filename;
									file_put_contents( $user_filename, $cache->aktion_filecontent );
								}
							}
						}
					} catch ( Exception $ex ) {

					}

					set_transient( 'atkp_discount', $cache, 10800 );
				}
			} else {

				$cache = null;
			}
		} else {
			return ( $cache );
		}

		return $cache;
	}

	public static function get_products_feed( $tab = 'popular' ) {
		//ATKP_STORE_URL.'/de/edd-api/v2/products/'


		$cache = get_transient( 'atkp_add_ons_feed' );

		if ( false === $cache ) {
			$url = ATKP_STORE_URL . '/edd-api/v2/products/?number=100&lang=en';

			$feed = wp_remote_get( esc_url_raw( $url ), array( 'sslverify' => false ) );

			if ( ! is_wp_error( $feed ) ) {
				if ( isset( $feed['body'] ) && strlen( $feed['body'] ) > 0 ) {
					$cache = json_decode( wp_remote_retrieve_body( $feed ) );

					set_transient( 'atkp_add_ons_feed', $cache, 3600 );
				}
			} else {

				$cache = null;
			}
		} else {
			return ( $cache );
		}

		return $cache;
	}

}