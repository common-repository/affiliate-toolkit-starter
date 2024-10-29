<?php
/**
 * Affiliate Toolkit
 * Diese Datei stellt eine erweiterte Cronjob-Funktionalität zur Verfügung.
 * Hiermit kan man auch eine große Anzahl an Dateien verarbeiten.
 */


//execute command line: wp eval-file --url=https://webseite.de/wp-content/plugins/affiliate-toolkit/affiliate-toolkit-wp-cli.php /var/www/html/wp-content/plugins/affiliate-toolkit/affiliate-toolkit-wp-cli.php

add_filter( 'do_rocket_generate_caching_files', '__return_false' );
ob_end_flush();
// PHP-Konfiguration optimieren
@error_reporting( E_ALL );
//1 Stunde maximale Ausführungszeit aktivieren (falls erlaubt)
@ini_set( "max_execution_time", 3600 );
@ini_set( "memory_limit", "4G" );
define( 'BASE_PATH', '/var/www/html/' );
define( 'DOING_CRON', true );

$cron = new atkp_external_cron();
$cron->execute();

class atkp_external_cron {

	function execute() {

		$crontype = atkp_options::$loader->get_crontype();
		$mode     = ATKPTools::get_get_parameter( 'mode', 'string' );


		switch ( $crontype ) {
			default:
			case 'wpcron':
				//wp cron? nothing todo...
				throw new exception( 'external cronjob deactivated' );
				exit;
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
