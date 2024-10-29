<?php
/**
 * Affiliate Toolkit
 * Diese Datei stellt eine erweiterte Cronjob-Funktionalität zur Verfügung.
 * Hiermit kan man auch eine große Anzahl an Dateien verarbeiten.
 */

// PHP-Konfiguration optimieren
//@error_reporting( E_ALL );
//1 Stunde maximale Ausführungszeit aktivieren (falls erlaubt)
//@ini_set( "max_execution_time", 3600 );
//@ini_set( "memory_limit", "512M" );
if ( ! defined( 'WP_MAX_MEMORY_LIMIT' ) ) {
	define( 'WP_MAX_MEMORY_LIMIT', '512M' );
}

if ( ! defined( 'SAVEQUERIES' ) ) {
	define( 'SAVEQUERIES', 0 );
}

if ( ! defined( 'WP_DISABLE_FATAL_ERROR_HANDLER' ) ) {
	define( 'WP_DISABLE_FATAL_ERROR_HANDLER', true );
}

if ( defined( 'WP_LOAD_PATH' ) ) {
	$default_wp_path = WP_LOAD_PATH;
} else {
	$default_wp_path = './../../../wp-load.php';

	if ( ! file_exists( $default_wp_path ) && isset( $_SERVER['DOCUMENT_ROOT'] ) ) {
		$default_wp_path = $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php';
	}

	if ( ! file_exists( $default_wp_path ) && isset( $_SERVER['SCRIPT_FILENAME'] ) ) {
		$parse_uri       = explode( 'wp-content', $_SERVER['SCRIPT_FILENAME'] );
		$default_wp_path = $parse_uri[0] . 'wp-load.php';
	}
}

if ( ! file_exists( $default_wp_path ) ) {
	echo 'wp-load.php not found';
	exit;
}

require( $default_wp_path );

$cron = new atkp_external_cron();
$cron->execute();

class atkp_external_cron {

	function execute() {
		$crontype = atkp_options::$loader->get_crontype();
		$mode     = ATKPTools::get_get_parameter( 'mode', 'string' );
		$key = ATKPTools::get_get_parameter( 'key', 'string' );

		$cron_key = ATKPTools::get_setting( ATKP_PLUGIN_PREFIX . '_cronkey' );

		if( php_sapi_name() == 'cli' )
			echo 'cmd mode running';
		else if ( defined( 'WP_CLI' ) && WP_CLI ) {
			echo 'cli mode running';
		}else {
			if ( $key == '' || trim( $cron_key ) != trim( $key ) ) {
				echo 'key invalid';
				die( 401 );
			}
		}


		switch ( $crontype ) {
			default:
			case 'wpcron':
				//wp cron? nothing todo...
			echo 'external cronjob deactivated';
			die( 404 );
			case 'external':
			case 'externaloutput':
				$cronjob = new atkp_cronjob_new( $crontype == 'externaloutput' );
				$cronjob->do_work( false, $mode );
				break;
		}

		//exit the script
		exit;
	}


}

?>