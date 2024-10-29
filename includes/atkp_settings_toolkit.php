<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class atkp_settings_toolkit {
	private $base = null;

	/**
	 * Construct the plugin object
	 */
	public function __construct( $pluginbase ) {
		$base = $pluginbase;
	}

	private function display_cron_job( $hookParam = ATKP_EVENT, $output = true ) {
		$crontype = get_option( ATKP_PLUGIN_PREFIX . '_crontype', 'wpcron' );

		switch ( $crontype ) {
			default:
			case 'wpcron':
				return ATKPTools::exists_cron_job( $hookParam, $output );
				break;
			case 'external':
			case 'externaloutput':

				break;
		}


	}

	public function toolkit_configuration_page() {
		if ( ATKPTools::exists_post_parameter( 'saveglobal' ) && check_admin_referer( 'save', 'save' ) ) {
			//speichern der einstellungen

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'You do not have sufficient permissions to access this page', ATKP_PLUGIN_PREFIX ) );
			}

			$duration    = ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_cache_duration', 'int' );

			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_cache_duration', $duration );


			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_crontype', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_crontype', 'string' ) );

			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_cron_from', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_cron_from', 'string' ) );
			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_cron_to', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_cron_to', 'string' ) );


			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_check_enabled', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_check_enabled', 'bool' ) );
			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_email_recipient', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_email_recipient', 'string' ) );

			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_queue_clean_days', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_queue_clean_days', 'int' ) );
			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_queue_package_size', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_queue_package_size', 'int' ) );
			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_notification_interval', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_notification_interval', 'int' ) );


			do_action( 'atkp_settings_toolkit_savefields' );

			$cronjob = new atkp_wp_cronjob( false );

			if ( isset( $cronjob ) ) {
				ATKPSettings::load_settings();

				$cronjob->my_update();
			}

			echo '<script>window.location.reload();</script>';
			exit;
			//header("Refresh:0");
			//exit;
		}


		?>
        <div class="atkp-content wrap">
            <div class="inner">


                <form method="POST"
                      action="?page=<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_affiliate_toolkit-plugin') ?>&tab=toolkit_configuration_page">
					<?php wp_nonce_field( "save", "save" ); ?>
                    <table class="form-table" style="width:100%">


                        <tr>
                            <th scope="row" style="background-color:#bde4ea; padding:7px" colspan="2">
	                            <?php echo esc_html__( 'Global settings', ATKP_PLUGIN_PREFIX ) ?>
                            </th>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="">
	                                <?php echo esc_html__( 'cronjob type', ATKP_PLUGIN_PREFIX ) ?>:
                                </label>
                            </th>
                            <td>
                                <select name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_crontype') ?>" class="atkp-cronjob-type">
									<?php
									$crontype  = get_option( ATKP_PLUGIN_PREFIX . '_crontype', 'wpcron' );
									$durations = array(
										'wpcron'         => esc_html__( 'WordPress Cronjob', ATKP_PLUGIN_PREFIX ),
										'external'       => esc_html__( 'External Cronjob', ATKP_PLUGIN_PREFIX ),
										'externaloutput' => esc_html__( 'External Cronjob + Output', ATKP_PLUGIN_PREFIX ),
									);

									foreach ( $durations as $value => $name ) {
										if ( $value == $crontype ) {
											$sel = ' selected';
										} else {
											$sel = '';
										}

										echo '<option value="' . esc_attr( $value ) . '"' . esc_attr( $sel ) . '>' . esc_html__( $name, ATKP_PLUGIN_PREFIX ) . '</option>';
									} ?>
                                </select>

								<?php ATKPTools::display_helptext( 'For small projects (maximum of 1000 products without price comparision) you can use WP cronjob. If you are importing a hugh amount of products you should the external cronjob.', 'https://helpdesk.affiliate-toolkit.com/portal/en/kb/articles/how-to-setup-the-cronjob#WordPress_Cronjob' ) ?>

                                <script>

                                    jQuery(document).ready(function () {

                                        function toggle_url() {
                                            if (jQuery('.atkp-cronjob-type').val() == 'wpcron') {
                                                jQuery('.atkp-cronjob-type-url').hide();
                                            } else {
                                                jQuery('.atkp-cronjob-type-url').show();
                                            }
                                        }

                                        jQuery('.atkp-cronjob-type').change(() => {
                                            toggle_url();
                                        });

                                        toggle_url();
                                    });


                                </script>
                            </td>
                        </tr>


                        <tr class="atkp-cronjob-type-url">
                            <th scope="row" style="padding-top:0">

                            </th>
                            <td style="padding-top:0">
								<?php ATKPTools::display_warntext( 'Please setup a server side cronjob on your hosting account. Call this URL every 10 minutes:' ) ?>
	                            <?php
	                            $cron_key = ATKPTools::get_setting( ATKP_PLUGIN_PREFIX . '_cronkey' );
	                            if ( $cron_key == '' ) {
		                            $cron_key = uniqid();
		                            ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_cronkey', $cron_key );
	                            }
	                            ?>


                                <input style="background-color:gainsboro; width:100%" readonly="readonly" type="text"
                                       value="<?php echo esc_attr( esc_url( ATKPTools::get_siteurl() ) . '/wp-content/plugins/' . str_replace( '.php', '-cron.php', plugin_basename( ATKP_PLUGIN_FILE ) ) . '?key=' . urlencode( $cron_key ) ); ?>">

                            </td>
                        </tr>

                        <tr>
                            <td>

                            </td>
                            <td>
								<?php
								$last_start = ATKPTools::get_setting( 'atkp_cron_last_start' );

								if ( $last_start == '' ) {
									?>
                                    <div class="atkp-validation">
                                        <b><?php echo esc_html__( 'Cronjob was never started yet.', ATKP_PLUGIN_PREFIX ); ?></b>
                                        <p><?php echo esc_html__( 'This is normal if you installed the plugin freshly. If you have been using the plugin for a while, you need to check where the problem is.', ATKP_PLUGIN_PREFIX ); ?></p>
										<?php if ( $crontype == 2 ) { ?>
                                            <p><?php echo esc_html__( 'You can try to save this settings page and attach the plugin again in the WordPress scheduler.', ATKP_PLUGIN_PREFIX ) ?></p>
										<?php } else { ?>
                                            <p><?php echo esc_html__( 'Please check the server-side cronjob configuration on the server. There seems to be something not configured correctly outside of the plugin.', ATKP_PLUGIN_PREFIX ) ?></p>
										<?php } ?>
                                    </div>
									<?php
								} else {
									//$last_start = strtotime( '2023-03-13 17:45:00 - 30 hours');
									$minutes_ago = ( time() - $last_start ) / 60;
									$timesince   = ATKPTools::time_since( $last_start, time() );

									if ( $minutes_ago > ( 60 * 24 ) ) {
										?>
                                        <div class="atkp-validation">
                                            <b><?php echo sprintf( esc_html__( 'Last cronjob execution was %s ago', ATKP_PLUGIN_PREFIX ), esc_html($timesince) ); ?></b>
                                            <p><?php echo esc_html__( 'The cronjob was last called a day ago. This is suspicious. If you don\'t know why this is, you should check the problem more closely.', ATKP_PLUGIN_PREFIX ); ?></p>
											<?php if ( $crontype == 2 ) { ?>
                                                <p><?php echo esc_html__( 'You can try to save this settings page and attach the plugin again in the WordPress scheduler.', ATKP_PLUGIN_PREFIX ) ?></p>
											<?php } else { ?>
                                                <p><?php echo esc_html__( 'Please check the server-side cronjob configuration on the server. There seems to be something not configured correctly outside of the plugin.', ATKP_PLUGIN_PREFIX ) ?></p>
											<?php } ?>
                                        </div>
										<?php
									} else if ( $minutes_ago > 30 ) {
										?>
                                        <div class="atkp-info">
                                            <b><?php echo sprintf( esc_html__( 'Last cronjob execution was %s ago', ATKP_PLUGIN_PREFIX ), esc_html($timesince) ); ?></b>
                                            <p><?php echo esc_html__( 'The cronjob was last called more than 30 minutes ago. Depending on the configuration (e.g. execution in special time windows) this can be normal.', ATKP_PLUGIN_PREFIX ); ?></p>
                                        </div>
										<?php
									} else {

										$last_processed = ATKPTools::get_setting( 'atkp_cron_last_processed' );
										$minutes_ago2   = $last_processed == '' ? 60 : ( ( $last_processed - $last_start ) / 60 );

										if ( $last_processed == '' || $minutes_ago2 > 30 ) {
											?>
                                            <div class="atkp-validation">
                                                <b><?php echo sprintf( esc_html__( 'Last cronjob execution was %s ago but nothing was processed.', ATKP_PLUGIN_PREFIX ), esc_html($timesince) ); ?></b>
                                                <p><?php echo esc_html__( 'The cronjob was called correctly but nothing was processed. This is an could be an issue.', ATKP_PLUGIN_PREFIX ); ?></p>

                                                <p><?php echo esc_html__( 'Please check if the cronjob is running into an HTTP 500 error.', ATKP_PLUGIN_PREFIX ) ?></p>

                                            </div>
											<?php
										} else {
											?>
                                            <div class="atkp-success">
                                                <b><?php echo sprintf( esc_html__( 'Last cronjob execution was %s ago', ATKP_PLUGIN_PREFIX ), esc_html($timesince) ); ?></b>
                                                <p><?php echo esc_html__( 'Gratualation, the configuration of the cronjob seems to be correct. The product update is called regularly.', ATKP_PLUGIN_PREFIX ); ?></p>
                                            </div>
											<?php
										}
									}

								}

								?>

                                <style>
                                    .atkp-info, .atkp-success, .atkp-warning, .atkp-error, .atkp-validation {
                                        border: 1px solid;
                                        margin: 0px 0px;
                                        padding: 15px 10px 15px 10px;
                                        background-repeat: no-repeat;
                                        background-position: 10px center;
                                        display: inline-block;
                                    }

                                    .atkp-info {
                                        color: #00529B;
                                        background-color: #BDE5F8;
                                    }

                                    .atkp-success {
                                        color: #4F8A10;
                                        background-color: #DFF2BF;
                                    }

                                    .atkp-warning {
                                        color: #9F6000;
                                        background-color: #FEEFB3;
                                    }

                                    .atkp-error {
                                        color: #D8000C;
                                        background-color: #FFBABA;
                                    }

                                    .atkp-validation {
                                        color: #D63301;
                                        background-color: #FFCCBA;
                                    }
                                </style>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="">
	                                <?php echo esc_html__( 'Productdata updates between', ATKP_PLUGIN_PREFIX ) ?>:
                                </label>
                            </th>
                            <td>
                                <input type="time" id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_cron_from') ?>"
                                       name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_cron_from') ?>"
                                       value="<?php echo esc_attr( get_option( ATKP_PLUGIN_PREFIX . '_cron_from' ) ); ?>">
                                -
                                <input type="time" id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_cron_to') ?>"
                                       name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_cron_to') ?>"
                                       value="<?php echo esc_attr( get_option( ATKP_PLUGIN_PREFIX . '_cron_to' ) ); ?>">

	                            <?php esc_html__( 'Current time', ATKP_PLUGIN_PREFIX ) ?>:
	                            <?php esc_html__( date( 'd.m.y H:i:s', time() ), ATKP_PLUGIN_PREFIX ); ?>

								<?php ATKPTools::display_helptext( 'If you are using api keys on multipe websites it is good to gave every website a own time frame to process the product updates.' ) ?>

                            </td>
                        </tr>


                        <tr>
                            <th scope="row">
                                <label for="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_queue_clean_days') ?>">
	                                <?php echo esc_html__( 'Delete Queue logs', ATKP_PLUGIN_PREFIX ) ?>
                                </label>
                            </th>
                            <td>
                                <input type="text" id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_queue_clean_days') ?>"
                                       name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_queue_clean_days') ?>"
                                       placeholder="7"
                                       value="<?php echo esc_attr( atkp_options::$loader->get_queue_clean_days() ); ?>">
                                <label for="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_queue_clean_days') ?>">
	                                <?php echo esc_html__( '(after x days)', ATKP_PLUGIN_PREFIX ) ?>
                                </label>
								<?php ATKPTools::display_helptext( 'We are logging errors into the product queue table. If the table has too many rows it is recommended to set the value lower then 7 days.' ) ?>

                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_queue_package_size') ?>">
	                                <?php esc_html_e( 'Queue package size', ATKP_PLUGIN_PREFIX ) ?>
                                </label>
                            </th>
                            <td>
                                <input type="text" id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_queue_package_size') ?>"
                                       name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_queue_package_size') ?>"
                                       placeholder="2000"
                                       value="<?php echo esc_attr( atkp_options::$loader->get_queue_package_size() ); ?>">

								<?php ATKPTools::display_helptext( 'You can configure how many products, lists or shops should be in one queue object. For database problems try a value around 200 items or lower.' ) ?>

                            </td>
                        </tr>

                        <tr class="atkp-modulerowa">
                            <td scope="row">
                                &nbsp;
                            </td>
                        </tr>

                        <tr>
                            <th scope="row" style="background-color:#bde4ea; padding:7px" colspan="2">
	                            <?php echo esc_html__( 'Settings for data check', ATKP_PLUGIN_PREFIX ) ?>
                            </th>
                        </tr>

                        <tr>
                            <th scope="row">

                            </th>
                            <td>
                                <input type="checkbox" id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_check_enabled') ?>"
                                       name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_check_enabled') ?>"
                                       value="1" <?php echo checked( 1, get_option( ATKP_PLUGIN_PREFIX . '_check_enabled' ), true ); ?>>
                                <label for="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_check_enabled') ?>">
	                                <?php echo esc_html__( 'Enable data check', ATKP_PLUGIN_PREFIX ) ?>
                                </label>
								<?php ATKPTools::display_helptext( 'Besides the queue protocols in the backend we can also send you a mail report. This report contains only product and list errors.' ) ?>

                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label for="">
	                                <?php echo esc_html__( 'Notification interval', ATKP_PLUGIN_PREFIX ) ?>:
                                </label>
                            </th>
                            <td>
                                <select name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_notification_interval') ?>">
									<?php

									$durations = array(
										360   => esc_html__( '6 Hours', ATKP_PLUGIN_PREFIX ),
										720   => esc_html__( '12 Hours', ATKP_PLUGIN_PREFIX ),
										1440  => esc_html__( '1 Day', ATKP_PLUGIN_PREFIX ),
										4320  => esc_html__( '3 Days', ATKP_PLUGIN_PREFIX ),
										10080 => esc_html__( '1 Week', ATKP_PLUGIN_PREFIX ),
									);

									foreach ( $durations as $value => $name ) {
										if ( $value == atkp_options::$loader->get_notification_interval() ) {
											$sel = ' selected';
										} else {
											$sel = '';
										}

										echo '<option value="' . esc_attr( $value ) . '"' . esc_attr( $sel ) . '>' . esc_html__( $name, ATKP_PLUGIN_PREFIX ) . '</option>';
									} ?>
                                </select>

                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="">
	                                <?php echo esc_html__( 'Recipient of e-mail report', ATKP_PLUGIN_PREFIX ) ?>:
                                </label>
                            </th>
                            <td>
                                <input type="text" id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_email_recipient') ?>"
                                       name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_email_recipient') ?>"
                                       value="<?php echo esc_attr( get_option( ATKP_PLUGIN_PREFIX . '_email_recipient' ) ); ?>"/>
								<?php ATKPTools::display_helptext( 'You can add multiple recipients. Separate more recipients via comma (,).' ) ?>
                            </td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>
								<?php

								$filename = ATKPTools::get_uploaddir() . '/report.html';
								if ( file_exists( $filename ) ) {
									echo '<span class="dashicons dashicons-list-view"></span>&nbsp;<a href="' . esc_url( ATKPTools::get_file( 'report.html' ) ) . '" target="_blank">' . esc_html__( 'Open last report', ATKP_PLUGIN_PREFIX ) . '</a><br /><br />';
								}

								?>

	                            <?php $reportnounce = wp_create_nonce( 'atkp-send-report' ); ?>

                                <a href="<?php echo( esc_url(ATKPTools::get_endpointurl() . '?action=atkp_send_report&request_nonce=' . esc_html($reportnounce)) ) ?>"
                                   class="button atkp-btn-report" style="margin-right:10px"><span
                                            class="dashicons dashicons-email"
                                            style="margin-top:3px"></span> <?php echo esc_html__( 'Send report now', ATKP_PLUGIN_PREFIX ) ?>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row" style="background-color:#bde4ea; padding:7px" colspan="2">
	                            <?php echo esc_html__( 'Settings for data cache', ATKP_PLUGIN_PREFIX ) ?>
                            </th>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="">
	                                <?php echo esc_html__( 'Cache duration', ATKP_PLUGIN_PREFIX ) ?>:
                                </label>
                            </th>
                            <td>
                                <select name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_cache_duration') ?>">
									<?php

									$durations = array(
										60    => esc_html__( '1 Hour', ATKP_PLUGIN_PREFIX ),
										360   => esc_html__( '6 Hours', ATKP_PLUGIN_PREFIX ),
										720   => esc_html__( '12 Hours', ATKP_PLUGIN_PREFIX ),
										1440  => esc_html__( '1 Day', ATKP_PLUGIN_PREFIX ),
										4320  => esc_html__( '3 Days', ATKP_PLUGIN_PREFIX ),
										10080 => esc_html__( '1 Week', ATKP_PLUGIN_PREFIX ),
									);

									foreach ( $durations as $value => $name ) {
										if ( $value == get_option( ATKP_PLUGIN_PREFIX . '_cache_duration', 1440 ) ) {
											$sel = ' selected';
										} else {
											$sel = '';
										}

										$item_translated = '';

										echo '<option value="' . esc_attr( $value ) . '"' . esc_attr( $sel ) . '>' . esc_html__( $name, ATKP_PLUGIN_PREFIX ) . '</option>';
									} ?>
                                </select>

								<?php ATKPTools::display_helptext( 'This is the duration how long product data is cached. If the cache expires the plugin is updating the product data. It has nothing todo with WordPress cache plugins.' ) ?>

                            </td>
                        </tr>

						<?php
						do_action( 'atkp_settings_toolkit_fields' );
						?>


                        <tr>
                            <th scope="row">
                            </th>
                            <td>
								<?php submit_button( '', 'primary', 'saveglobal', false ); ?>
                            </td>
                        </tr>

                    </table>
                </form>

            </div>
        </div>


		<?php
	}
}

?>