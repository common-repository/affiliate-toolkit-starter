<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class atkp_search_resp {

	public $data = array();

	function __construct() {
		$this->pagecount   = 0;
		$this->total       = 0;
		$this->currentpage = 0;
		$this->message     = '';
	}

	public $products = array();

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