<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class atkp_cronjob_new {
	/**
	 * Construct the cron
	 */
	public function __construct( $echo_messages ) {

		$this->echo_messages = $echo_messages;

	}

	private function collect_queue() {
		/** @var atkp_queue_entry[] $list */

		$list = array();

		$list = apply_filters( 'atkp_queue_collect_entries', $list );

		return $list;
	}

	private function clean_queues() {
		$deleted = atkp_queue::clean_queues();

		$this->send_message( "deleted queues: " . implode( ',', $deleted ) );
	}

	public function do_work( $iswpcronjob = false, $mode = '' ) {

		try {
			define( 'ATKP_CRONJOB', true );
			$this->send_message( '### cronjob started ###' );

			$max_execution = intval( ini_get( "max_execution_time" ) );
			$time_start    = microtime( true );

			ATKPTools::set_setting( 'atkp_cron_last_start', time() );

			$this->send_message( 'Max Execution Time: ' . $max_execution );

			if ( defined( 'SAVEQUERIES' ) && SAVEQUERIES == 1 ) {
				$this->send_message( '"SAVEQUERIES" defined: ' . SAVEQUERIES );
				$this->send_message( 'The SAVEQUERIES definition saves the database queries to an array and that array can be displayed to help analyze those queries. This will have a performance impact on your site, so make sure to turn this off when you are not debugging.' );
			} else {
				$this->send_message( '"SAVEQUERIES" not defined' );
			}

			$atkp_queue = null;

			if ( atkp_queue::exists_notfinished() ) {
				$override = ATKPTools::get_get_parameter( 'override_lastactivity', 'string' );

				$this->send_message( 'queue already exists' );

				//if last activity older then 5 minutes -> run queue
				$atkp_queue = atkp_queue::get_active_queue();

				$lastactivity = $atkp_queue->get_last_activity();
				$this->send_message( 'last activity: ' . $atkp_queue->updatedon );
				$this->send_message( 'last activity entries: ' . $lastactivity );

				if ( ! $override ) {
					$diff_lastactivity = round( abs( time() - strtotime( $lastactivity ) ) / 60, 2 );

					if ( $diff_lastactivity <= 5 ) {
						$this->send_message( 'queue is still running...' );

						return;
					}
				}

			} else {
				//if nothing open, create a new package

				$list = $this->collect_queue();

				if ( count( $list ) > 0 ) {
					$this->send_message( 'new queue will be created...' );

					$grouped = array();
					$type    = '';
					foreach ( $list as $entry ) {
						$grouped[ $entry->post_type ] = $entry->post_type;
					}

					if ( array_key_exists( 'atkp_shop', $grouped ) ) {
						$newlist = array();

						foreach ( $list as $entry ) {
							if ( $entry->post_type == 'atkp_shop' ) {
								$newlist[] = $entry;
							}
						}
						$list                 = $newlist;
						$grouped              = array();
						$grouped['atkp_shop'] = 'atkp_shop';
					}

					foreach ( $grouped as $xx => $xx2 ) {
						if ( $type == '' ) {
							$type = $xx;
						} else {
							$type .= ', ' . $xx;
						}
					}

					ATKPTools::create_queue( $type, '', $iswpcronjob, $list, atkp_queue_status::ACTIVE );

					$atkp_queue = atkp_queue::get_active_queue();
				} else {
					$this->send_message( 'no new queue created' );
				}
			}

			$this->do_basic_work();

			if ( $atkp_queue != null ) {
				$override = ATKPTools::get_get_parameter( 'override_timeframe', 'string' );

				if ( $override != 'yes' ) {
					$from = atkp_options::$loader->get_cron_from();
					$to   = atkp_options::$loader->get_cron_to();

					if ( $from != '' && $to != '' && ( $from != '00:00' && $to != '00:00' ) ) {
						$begin = strtotime( $from );
						$end   = strtotime( $to );
						$now   = time();

						if ( $begin > $end ) {
							$end = $end + 86400;
						}

						if ( ! ( $now >= $begin && $now <= $end ) ) {
							$this->send_message( 'time ' . date( 'd.m.y H:i:s', $now ) . ' is NOT between ' . date( 'd.m.y H:i:s', $begin ) . ' and ' . date( 'd.m.y H:i:s', $end ) . ' - queue will not be processed' );

							return;
						}
					}
				}

				$this->send_message( 'queue will be processed...' );

				$atkp_queue->retries = $atkp_queue->retries + 1;
				$atkp_queue->save();

				//process the queue

				//get entries (grouped shopid) 10 pieces and run the update

				ATKPTools::set_setting( 'atkp_cron_last_processed', time() );

				while ( true ) {

					/** @var atkp_queue_entry[] $entries */

					if ( ! $iswpcronjob && ! class_exists( 'WP_CLI' ) && $max_execution > 0 ) {
						set_time_limit( $max_execution );
					}

					$entries = $atkp_queue->get_next_entries( atkp_queue_entry_status::PREPARED );

					$this->send_message( 'entries to process: ' . count( $entries ) );

					if ( count( $entries ) == 0 ) {
						break;
					} else {
						$functionname = $entries[0]->functionname;
						$shopid       = $entries[0]->shop_id;

						if ( $shopid != '' && $shopid != '0' ) {
							$status = get_post_status( $shopid );

							if ( ! ( $status == 'draft' || $status == 'publish' ) ) {
								foreach ( $entries as $entry ) {
									$entry->status         = atkp_queue_entry_status::ERROR;
									$entry->updatedmessage = __( 'Shop status invalid: ', ATKP_PLUGIN_PREFIX ) . $status;

									$entry->save();
								}
								continue;
							}
						}

						$this->send_message( 'atkp_queue_process_entries_' . $functionname . ' before call' );
						//$this->send_message('$entries: '. serialize($entries));
						//$this->send_message('$shopid: '. serialize($shopid));
						//$this->send_message('attached filters: '. ATKPTools::get_attached_filters('atkp_queue_process_entries_' . $functionname, true));

						try {
							$entries_bak = $entries;
							$entries     = apply_filters( 'atkp_queue_process_entries_' . $functionname, $entries, $shopid );

							if ( $entries == null || count( $entries ) == 0 ) {
								$entries = $entries_bak;
								$this->send_message( 'atkp_queue_process_entries_' . $functionname . ' did not returned $entries' );
							} else {
								$this->send_message( 'atkp_queue_process_entries_' . $functionname . ' returned $entries: ' . count( $entries ) );
							}

							foreach ( $entries as $entry ) {
								if ( $entry->status == atkp_queue_entry_status::PREPARED ) {
									$entry->status         = atkp_queue_entry_status::NOT_PROCESSED;
									$entry->updatedmessage = __( 'Entry was not updated via function', ATKP_PLUGIN_PREFIX );
								}

								$entry->save();
							}
						} catch ( Exception $e ) {
							foreach ( $entries as $entry ) {
								$entry->status         = atkp_queue_entry_status::ERROR;
								$entry->updatedmessage = sprintf( __( 'Exception in entries hook: %s', ATKP_PLUGIN_PREFIX ), $e->getMessage() );

								$entry->save();
							}
						}
					}
				}

				//TODO: Collect errors from entries and define status
				if ( $atkp_queue->has_errors() ) {
					$atkp_queue->status = atkp_queue_status::ERROR;
				} else {
					$atkp_queue->status = atkp_queue_status::SUCCESSFULLY;
				}
				$atkp_queue->save();

				try {
					do_action( 'atkp_queue_finished', $atkp_queue->id );
				} catch ( Exception $ex ) {
					$this->send_message( $ex->getMessage() );
				}


				$this->send_message( 'clean queues' );
				$this->clean_queues();
				$this->send_message( 'clean queues finished' );
			}

			if ( atkp_options::$loader->get_check_enabled() ) {
				$lastdatacheck = atkp_options::$loader->get_cron_lastdatacheck();
				$run_check     = true;
				if ( $lastdatacheck != '' ) {
					$diff_lastdatacheck = round( abs( time() - $lastdatacheck ) / 60, 2 );

					//wenn keine fortsetzung, dann prüfen
					if ( $diff_lastdatacheck <= atkp_options::$loader->get_notification_interval() ) {
						$this->send_message( 'next data check (hours): ' . round( ( atkp_options::$loader->get_notification_interval() - $diff_lastdatacheck ) / 60, 2 ) );
						$run_check = false;
					}
				}

				if ( $run_check ) {
					try {
						do_action( 'atkp_datacheck_report' );
					} catch ( Exception $e ) {
						ATKPLog::LogError( $e->getMessage() );
					}

					update_option( ATKP_PLUGIN_PREFIX . '_cron_lastdatacheck', time() );
				}
			}

			$this->send_message( 'Total Execution Time: ' . ( microtime( true ) - $time_start ) . ' Seconds' );
			$this->send_message( '### cronjob finished ###' );
		} catch ( Exception $e ) {
			$this->send_message( '### cronjob error ###' );
			$this->send_message( $e->getMessage() );
		}

		ATKPTools::set_setting( 'atkp_cron_last_processed', time() );

		if ( ! $iswpcronjob ) {
			echo 'OK';
			exit;
		}
	}

	function do_basic_work() {
		ATKP_StoreController::get_product_discounts();

		ATKP_LicenseController::check_license_status();
	}

	public function send_message( $message ) {
		if ( class_exists( 'WP_CLI' ) ) {
			WP_CLI::log( $message );
		} else {
			if ( $this->echo_messages ) {
				echo esc_html__( $message . '<br />' . PHP_EOL, ATKP_PLUGIN_PREFIX );
			}
		}

		if ( ATKPLog::$logenabled ) {
			ATKPLog::LogDebug( $message );
		}
	}


}


?>
