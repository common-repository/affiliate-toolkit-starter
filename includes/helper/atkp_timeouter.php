<?php
declare( ticks=1 );

class atkp_timeouter {

	private static $start_time = false,
		$timeout;

	public static function start( $timeout ) {
		self::$start_time = microtime( true );
		self::$timeout    = (float) $timeout;
		register_tick_function( array( 'atkp_timeouter', 'tick' ) );
	}

	public static function end() {
		unregister_tick_function( array( 'atkp_timeouter', 'tick' ) );
	}

	public static function tick() {
		if ( ( microtime( true ) - self::$start_time ) > self::$timeout ) {
			throw new Exception( 'timeout detected' );
		}
	}

}