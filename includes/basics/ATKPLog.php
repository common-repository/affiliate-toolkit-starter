<?php
/**
 * Created by PhpStorm.
 * User: Christof
 * Date: 01.12.2018
 * Time: 11:36
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class ATKPLog {
	private static $log;
	public static $logenabled;

	public static function Init( $filepath, $priority ) {
		ATKPLog::$logenabled = false;

		if ( $priority != 'off' && $priority != '' ) {
			if ( ! class_exists( 'KLogger' ) ) {
				require_once ATKP_PLUGIN_DIR . '/lib/klogger.php';
			}

			$logpriority = KLogger::OFF;

			switch ( $priority ) {
				case 'debug':
					ATKPLog::$logenabled = true;
					$logpriority         = KLogger::DEBUG;
					break;
				case 'error':
					ATKPLog::$logenabled = true;
					$logpriority         = KLogger::ERROR;
					break;
			}

			ATKPLog::$log = new KLogger ( $filepath, $logpriority );
		}
	}

	public static function LogInfo( $line ) {
		if ( ! ATKPLog::$logenabled ) {
			return;
		}

		ATKPLog::$log->LogInfo( $line );
	}

	public static function LogDebug( $line, $context = null ) {
		if ( ! ATKPLog::$logenabled ) {
			return;
		}

		ATKPLog::$log->LogDebug( $line );

		if ( $context != null ) {
			ATKPLog::$log->LogDebug( ATKPLog::contextToString( $context ) );
		}
	}

	/**
	 * Takes the given context and coverts it to a string.
	 *
	 * @param  array $context The Context
	 *
	 * @return string
	 */
	protected static function contextToString( $context ) {
		$export = '';
		foreach ( $context as $key => $value ) {
			$export .= "{$key}: ";
			$export .= preg_replace( array(
				'/=>\s+([a-zA-Z])/im',
				'/array\(\s+\)/im',
				'/^  |\G  /m'
			), array(
				'=> $1',
				'array()',
				'    '
			), str_replace( 'array (', 'array(', var_export( $value, true ) ) );
			$export .= PHP_EOL;
		}

		return $export == null ? null : str_replace( array( '\\\\', '\\\'' ), array( '\\', '\'' ), rtrim( $export ) );
	}

	public static function LogWarn( $line ) {
		if ( ! ATKPLog::$logenabled ) {
			return;
		}

		ATKPLog::$log->LogWarn( $line );
	}

	public static function LogError( $line ) {
		if ( ! ATKPLog::$logenabled ) {
			return;
		}

		ATKPLog::$log->LogError( $line );
	}

}
