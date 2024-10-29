<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class atkp_export_provider_base {
	//das ist die basis klasse für alle shop provider

	public function __construct() {

	}

	public static function get_provider( $type ) {

		return new atkp_export_provider_woo();
	}

	public function check() {
		throw new exception( 'not implemented' );
	}

	public function checklogon() {
		throw new exception( 'not implemented' );
	}

	public function export_product( $productid ) {
		throw new exception( 'not implemented' );

	}

	public function update_key_product( $productid ) {
		throw new exception( 'not implemented' );
	}

	public function import_product( $wooproductid ) {

		throw new exception( 'not implemented' );
	}
}