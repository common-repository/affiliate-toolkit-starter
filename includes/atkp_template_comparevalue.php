<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class atkp_template_comparevalue {
	public $data = array();

	function __construct() {
		$this->id          = '';
		$this->caption     = '';
		$this->description = '';
		$this->detail      = '';
		$this->viewtype    = 1;
		$this->align       = 1;
		$this->cssclass    = '';

	}

	public static function load_comparevalues( $productid ) {

		$values = ATKPTools::get_post_setting( $productid, ATKP_TEMPLATE_POSTTYPE . '_comparevalues' );

		if ( ! is_array( $values ) ) {
			$values = array();
		}

		return $values;
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