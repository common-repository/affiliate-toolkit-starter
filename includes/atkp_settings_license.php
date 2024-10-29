<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class atkp_settings_license {
	private $base = null;

	/**
	 * Construct the plugin object
	 */
	public function __construct( $pluginbase ) {
		$base = $pluginbase;

	}

	private $placeholder_key = '***************';

	public function license_configuration_page() {

		ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_licensepage', true );

		$modules = ATKP_LicenseController::get_modules();

		if ( ATKPTools::exists_post_parameter( 'savelicense' ) && check_admin_referer( 'save', 'save' ) ) {
			//speichern der einstellungen

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'You do not have sufficient permissions to access this page', ATKP_PLUGIN_PREFIX ) );
			}

			foreach ( $modules as $moduleid => $modulename ) {

				$fieldname          = ATKP_PLUGIN_PREFIX . '_license_key_' . $modulename;
				$license_old        = ATKP_LicenseController::get_module_license( $modulename );
				$license_status_old = ATKP_LicenseController::get_module_license_status( $modulename );


				$license = ATKPTools::get_post_parameter( $fieldname, 'string' );

				if ( $license == $this->placeholder_key ) {
					continue;
				}

				if ( $license_old != $license || $license_status_old != 'active' ) {
					if ( $license_status_old == 'active' ) {
						//Deactivate
						ATKP_LicenseController::deactivate_license_request( $license_old, $moduleid );
						ATKP_LicenseController::set_module_license( $modulename, '' );
						ATKP_LicenseController::set_module_license_message( $modulename, '' );
						ATKP_LicenseController::set_module_license_status( $modulename, 'none' );
						ATKP_LicenseController::set_module_license_owner( $modulename, '');
					}

					if ( $license != '' ) {
						$result = ATKP_LicenseController::activate_license_request( $license, $moduleid, $modulename );

						ATKP_LicenseController::set_module_license( $modulename, $license );
						ATKP_LicenseController::set_module_license_message( $modulename, $result['message'] );
						ATKP_LicenseController::set_module_license_status( $modulename, $result['status'] );
						ATKP_LicenseController::set_module_license_owner( $modulename, isset( $result['customer_name'] ) ? $result['customer_name'] : '' );
					} else {
						ATKP_LicenseController::set_module_license( $modulename, '' );
					}
					//activate
				}

			}

		} else {
			ATKP_LicenseController::check_license_status();
		}

		?>
        <div class="atkp-content wrap">
            <div class="inner">


                <form method="POST"
                      action="?page=<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_affiliate_toolkit-plugin&tab=license_configuration_page') ?>">
					<?php wp_nonce_field( "save", "save" ); ?>


                    <div class="wrap">
                        <div id="atkp-add-ons">

							<?php
							if ( count( $modules ) == 0 ) {
								echo '<div class="error"><p>' . esc_html__( 'There are no extensions activated.', ATKP_PLUGIN_PREFIX ) . '</div>';
							} else {

								$allPlugins = get_plugins();


								foreach ( $modules as $moduleid => $modulename ) {
									$fieldname         = ATKP_PLUGIN_PREFIX . '_license_key_' . $modulename;
									$license           = ATKP_LicenseController::get_module_license( $modulename );
									$license_status    = ATKP_LicenseController::get_module_license_status( $modulename );
									$license_message   = ATKP_LicenseController::get_module_license_message( $modulename );
									$license_owner     = ATKP_LicenseController::get_module_license_owner( $modulename );
									$extension_version = '';
									$extension_slug    = '';
									$extension_title   = '';

									foreach ( atkp_options::$loader->edd_plugin_data as $slug => $appdata ) {

										if ( intval( $appdata['item_id'] ) == $moduleid ) {
											$extension_version = $appdata['version'];
											$extension_slug    = $slug;
											break;
										}
									}

									foreach ( $allPlugins as $key => $value ) {
										$parts = explode( '/', $key );
										if ( $parts[1] == $extension_slug . '.php' ) {
											$extension_title = str_replace( 'affiliate-toolkit - ', '', $value['Title'] );
											break;
										}
									}

									?>

                                    <div class="atkp-extension">
                                        <div class="atkp-extension-title">
                                            <h3><?php echo esc_attr( $extension_title == '' ? $modulename : $extension_title ) ?></h3>
                                        </div>

                                        <div><input id="<?php echo esc_attr($fieldname) ?>"
                                                    name="<?php echo esc_attr($fieldname) ?>" type="text"
                                                    style="width: 90%;margin:10px"
                                                    class="regular-text"
                                                    value="<?php esc_attr_e( ( $license == '' ? '' : $this->placeholder_key ) ); ?>"/>
                                        </div>

                                        <div class="atkp_license-status">

											<?php
											if ( $extension_version == $appdata['version'] ) {
												echo sprintf( esc_html__( 'Current version installed (%s)', ATKP_PLUGIN_PREFIX ), esc_html($extension_version) );
											} else
												echo sprintf( esc_html__( 'Installed version: %s / Current version: %s', ATKP_PLUGIN_PREFIX ), esc_html($extension_version), esc_html($appdata['version']) )


											?><br/>

											<?php if ( $license == '' ) { ?>
                                                <span><?php echo esc_html__( 'enter license key', ATKP_PLUGIN_PREFIX ); ?></span>
											<?php } else if ( $license_status == 'valid' ) { ?>
                                                <span style="color:green;"><?php echo esc_html__( 'active', ATKP_PLUGIN_PREFIX );
													echo( $license_owner == '' ? '' : sprintf( esc_html__( ', license owner: %s', ATKP_PLUGIN_PREFIX ), esc_html($license_owner) ) ) ?> </span>
											<?php } else if ( $license != '' ) { ?>
                                                <span style="color:red;"><?php echo esc_attr($license_message); ?><?php echo( $license_status == 'expired' ? ' ' . sprintf( __( '<a href="%s" target="_blank">Renew now</a>', ATKP_PLUGIN_PREFIX ), esc_attr('https://www.affiliate-toolkit.com/' . ( ATKPTools::is_lang_de() ? 'de/kasse' : 'checkout' ) . '/?nocache=true&edd_license_key=' . urlencode( esc_html($license) ) . '&download_id=' . esc_html($moduleid)) ) : '' ) ?></span>
											<?php } ?></div>

                                    </div>
								<?php }
							} ?>
                        </div>
                    </div>

                    <div class="atkp-submit-license">
						<?php if ( count( $modules ) > 0 ) { ?>
							<?php submit_button( '', 'primary', 'savelicense', false ); ?>
						<?php } ?>
                    </div>
                    <style>
                        .atkp_license-status {
                            position: absolute;
                            background: #fafafa;
                            padding: 14px;
                            border-top: 1px solid #eee;
                            margin: 20px 0px -14px;
                            min-height: 67px;
                            width: 100%;
                            bottom: 14px;
                            box-sizing: border-box;
                        }

                        .atkp-submit-license {
                            display: inline-block;
                            width: 100%;
                            padding-bottom: 20px;
                            margin-left: 15px;
                        }

                        #atkp-add-ons .atkp-extension {
                            background: #fff;
                            border: 1px solid #ccc;
                            float: left;
                            padding: 0px;
                            position: relative;
                            margin: 15px;
                            width: 320px;
                            height: 180px
                        }

                        .atkp-extension-title {
                            font-size: 13px;
                            margin: 0px;

                            background: #f9f9f9;
                            padding: 5px 0px;
                            border-bottom: 1px solid #ccc;
                            width: 100%;
                        }

                        #atkp-add-ons .atkp-extension .button-secondary {
                            position: absolute;
                            bottom: 14px;
                            left: 14px
                        }

                    </style>


                </form>

            </div>
        </div>


		<?php
	}
}

?>