<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class atkp_wp_cronjob {
	/**
	 * Construct the plugin object
	 */
	public function __construct( $add_filters = true ) {
		if ( $add_filters ) {
			add_filter( 'cron_schedules', array( $this, 'add_new_intervals' ) );
			add_action( ATKP_EVENT, array( &$this, 'new_cronjob' ) );
		}
	}

	public function register_cron_hooks() {
		register_activation_hook( __FILE__, 'my_activation' );
		register_deactivation_hook( __FILE__, 'my_deactivation' );
	}

	function add_new_intervals( $schedules ) {
		$schedules[ ATKP_PLUGIN_PREFIX . '_10' ] = array(
			'interval' => 10 * 60,
			'display'  => __( 'Every 10 minutes', ATKP_PLUGIN_PREFIX )
		);


		return $schedules;
	}


	public function my_activation() {
		$crontype = atkp_options::$loader->get_crontype();

		switch ( $crontype ) {
			default:
			case 'wpcron':
				//$inittime = strtotime('00:00:00');

				//$key = ATKP_PLUGIN_PREFIX.'_'.(ATKPSettings::$access_cache_duration);
				wp_schedule_event( time(), ATKP_PLUGIN_PREFIX . '_10', ATKP_EVENT );

				//$key = ATKP_PLUGIN_PREFIX.'_'.(ATKPSettings::$notification_interval);
				//wp_schedule_event( $inittime, $key, ATKP_CHECK);

				//$key = ATKP_PLUGIN_PREFIX.'_'.(ATKPSettings::$access_csv_intervall);
				//wp_schedule_event( $inittime, $key, ATKP_CSVIMPORT);
				break;
			case 'external':
			case 'externaloutput':
				//external cronjob? nothing todo...
				break;
		}
	}

	public function my_deactivation() {

		wp_clear_scheduled_hook( ATKP_EVENT );
		//wp_clear_scheduled_hook(ATKP_CHECK);
		//wp_clear_scheduled_hook(ATKP_CSVIMPORT);
	}

	public function my_update() {
		$this->my_deactivation();
		$this->my_activation();
	}

	public function new_cronjob() {
		try {
			if ( ATKPLog::$logenabled ) {
				ATKPLog::LogDebug( '*** wp internal event started ***' );
			}

			$crontype = atkp_options::$loader->get_crontype();

			if ( ATKPLog::$logenabled ) {
				ATKPLog::LogDebug( 'crontyp: ' . $crontype );
			}

			switch ( $crontype ) {
				default:
				case 'wpcron':
					//wp cron.. everything ok

					$cronjob = new atkp_cronjob_new( false );
					$cronjob->do_work( true );
					break;
				case 'external':
				case 'externaloutput':
					//external? nothing todo...
					return;
			}


			if ( ATKPLog::$logenabled ) {
				ATKPLog::LogDebug( '*** wp internal event finished ***' );
			}

		} catch ( Exception $e ) {
			ATKPLog::LogError( $e->getMessage() );
		}
	}

}