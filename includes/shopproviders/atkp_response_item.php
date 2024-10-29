<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class atkp_response_item {
	public $data = array();

	public $productitem;

	function __construct() {

		$this->errormessage = '';

		$this->shopid = '';

		$this->uniqueid    = '';
		$this->uniquetype  = '';
		$this->productitem = null;
	}

	public function __get( $member ) {
		if ( isset( $this->data[ $member ] ) ) {
			return $this->data[ $member ];
		}
	}

	public function __set( $member, $value ) {
		// if (isset($this->data[$member])) {
		$this->data[ $member ] = $value;
		//}
	}
}


?>