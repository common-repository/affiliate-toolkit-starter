<?php


class ATKP_LicenseController {

	public static function get_modules() {
		$modules = array();

		$modules = apply_filters( 'atkp_get_modules', $modules );


		return $modules;
	}

	public static function get_module_license( $module ) {
		return trim( atkp_options::$loader->get_licensekey_module( $module ) );
	}

	public static function get_module_license_status( $module ) {
		return atkp_options::$loader->get_licensestatus_module( $module );
	}

	public static function get_module_license_message( $module ) {
		return atkp_options::$loader->get_licensemessage_module( $module );
	}

	public static function get_module_license_owner( $module ) {
		return atkp_options::$loader->get_licenseowner_module( $module );
	}

	public static function set_module_license( $module, $value ) {
		atkp_options::$loader->set_licensekey_module( $module, $value );
	}

	public static function set_module_license_status( $module, $value ) {
		atkp_options::$loader->set_licensestatus_module( $module, $value );
	}

	public static function set_module_license_owner( $module, $value ) {
		atkp_options::$loader->set_licenseowner_module( $module, $value );
	}

	public static function set_module_license_message( $module, $value ) {
		atkp_options::$loader->set_licensemessage_module( $module, $value );
	}

	public static function get_license_status() {
		$modules = ATKP_LicenseController::get_modules();

		foreach ( $modules as $moduleid => $modulename ) {
			$license = ATKP_LicenseController::get_module_license( $modulename );
			if ( $license == '' ) {
				return sprintf( __( 'There is an extension without a license key. Please go to the <a href="%s">license page</a>.', ATKP_PLUGIN_PREFIX ), admin_url() . '?page=' . ATKP_PLUGIN_PREFIX . '_affiliate_toolkit-plugin&tab=license_configuration_page' );
			}

			$license_status = ATKP_LicenseController::get_module_license_status( $modulename );

			if ( $license_status == 'expired' ) {
				$license_message = ATKP_LicenseController::get_module_license_message( $modulename );
				if ( $license_message == '' ) {
					$license_message = __( 'There is an extension with an expired license key', ATKP_PLUGIN_PREFIX );
				}

				return sprintf( __( '%s. Please go to the <a href="%s">license page</a>.', ATKP_PLUGIN_PREFIX ), $license_message, admin_url() . '?page=' . ATKP_PLUGIN_PREFIX . '_affiliate_toolkit-plugin&tab=license_configuration_page' );
			} else if ( $license_status != 'valid' ) {
				return sprintf( __( 'There is an extension without a valid license key. Please go to the <a href="%s">license page</a>.', ATKP_PLUGIN_PREFIX ), admin_url() . '?page=' . ATKP_PLUGIN_PREFIX . '_affiliate_toolkit-plugin&tab=license_configuration_page' );
			}
		}

		return null;
	}

	public static function check_license_status() {
		$cache = get_transient( 'atkp_license' );

		if ( false === $cache ) {
			$modules = ATKP_LicenseController::get_modules();

			foreach ( $modules as $moduleid => $modulename ) {

				$license = ATKP_LicenseController::get_module_license( $modulename );
				if ( $license == '' ) {
					continue;
				}


				$license_status  = ATKP_LicenseController::get_module_license_status( $modulename );
				$license_message = ATKP_LicenseController::get_module_license_message( $modulename );

				$api_params = array(
					'edd_action' => 'check_license',
					'license'    => $license,
					'url'        => home_url(),
					'item_id'    => $moduleid
				);
				// Call the custom API.
				$response = wp_remote_post( ATKP_STORE_URL, array(
					'timeout'   => 15,
					'sslverify' => false,
					'body'      => $api_params
				) );

				set_transient( 'atkp_license', $response, 43200 );

				$result = [];

				// make sure the response came back okay
				if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
					$result['message'] = ( is_wp_error( $response ) && ! empty( $response->get_error_message() ) ) ? $response->get_error_message() : __( 'An error occurred, please try again.' );
				} else {

					$license_data = json_decode( wp_remote_retrieve_body( $response ) );

					if ( isset( $license_data ) && $license_data != null ) {
						$result['status']  = $license_data->license;
						$result['message'] = self::get_license_message( $license_data->license, $modulename );
						if ( isset( $license_data->customer_name ) ) {
							$result['customer_name'] = $license_data->customer_name;
						}
						if ( isset( $license_data->customer_email ) ) {
							$result['customer_email'] = $license_data->customer_email;
						}
					} else {

						$result['message'] = __( 'Your license key is invalid.', ATKP_PLUGIN_PREFIX );
						$result['status']  = 'invalid';
					}
				}

				ATKP_LicenseController::set_module_license( $modulename, $license );
				ATKP_LicenseController::set_module_license_message( $modulename, $result['message'] );
				ATKP_LicenseController::set_module_license_status( $modulename, $result['status'] );
				ATKP_LicenseController::set_module_license_owner( $modulename, $result['customer_name'] );

			}
		}
	}


	public static function deactivate_license_request( $license, $item_id ) {
		$api_params = array(
			'edd_action' => 'deactivate_license',
			'license'    => $license,
			'url'        => home_url(),
			'item_id'    => $item_id
		);
		// Call the custom API.
		$response = wp_remote_post( ATKP_STORE_URL, array(
			'timeout'   => 15,
			'sslverify' => false,
			'body'      => $api_params
		) );

		$result = [];

		// make sure the response came back okay
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			$result['message'] = ( is_wp_error( $response ) && ! empty( $response->get_error_message() ) ) ? $response->get_error_message() : __( 'An error occurred, please try again.' );
		} else {

			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			$result['message'] = $license_data->license;
		}

		return $result;
	}

	public static function activate_license_request( $license, $item_id, $productname ) {
		// data to send in our API request
		$api_params = array(
			'edd_action' => 'activate_license',
			'license'    => $license,
			'url'        => home_url(),
			'item_id'    => $item_id
		);
		// Call the custom API.
		$response = wp_remote_post( ATKP_STORE_URL, array(
			'timeout'   => 15,
			'sslverify' => false,
			'body'      => $api_params
		) );


		$result = [];

		// make sure the response came back okay
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			$result['message'] = ( is_wp_error( $response ) && ! empty( $response->get_error_message() ) ) ? $response->get_error_message() : __( 'An error occurred, please try again.' );
		} else {

			$license_data = json_decode( wp_remote_retrieve_body( $response ) );
		}

		// $license_data->license will be either "valid" or "invalid"

		if ( isset( $license_data ) && $license_data != null ) {
			$result['status']  = $license_data->license;
			$result['message'] = self::get_license_message( isset( $license_data->error ) ? $license_data->error : '', $productname );
			if ( isset( $license_data->customer_name ) ) {
				$result['customer_name'] = $license_data->customer_name;
			}
			if ( isset( $license_data->customer_email ) ) {
				$result['customer_email'] = $license_data->customer_email;
			}
		} else {

			$result['message'] = __( 'Your license key is invalid.', ATKP_PLUGIN_PREFIX );
			$result['status']  = 'invalid';
		}

		return $result;
	}


	static function get_license_message( $license_status, $productname ) {
		$message = '';

		switch ( $license_status ) {
			case 'expired' :
				$message = sprintf(
					__( 'Your license key expired on %s.', ATKP_PLUGIN_PREFIX ),
					date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
				);
				break;
			case 'revoked' :
				$message = __( 'Your license key has been disabled.', ATKP_PLUGIN_PREFIX );
				break;
			case 'missing' :
				$message = __( 'Invalid license.', ATKP_PLUGIN_PREFIX );
				break;
			case 'site_inactive' :
				$message = __( 'Your license is not active for this URL.', ATKP_PLUGIN_PREFIX );
				break;
			case 'item_name_mismatch' :
				$message = sprintf( __( 'This appears to be an invalid license key for %s.', ATKP_PLUGIN_PREFIX ), $productname );
				break;
			case 'no_activations_left':
				$message = __( 'Your license key has reached its activation limit.', ATKP_PLUGIN_PREFIX );
				break;
			case 'invalid':
				$message = __( 'Your license key is invalid.', ATKP_PLUGIN_PREFIX );
				break;
			case 'invalid_item_id':
				$message = __( 'Your license key is for another product.', ATKP_PLUGIN_PREFIX );
				break;
			default :
				$message = $license_status;
				break;
		}

		if ( $message != '' && $message != $license_status ) {
			$message .= ' (' . $license_status . ')';
		}

		return $message;
	}

}