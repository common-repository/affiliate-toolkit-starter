<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class atkp_template_comparegroup {
	public $data = array();

	function __construct() {
		$this->id          = '';
		$this->caption     = '';
		$this->description = '';
		$this->isvisible   = true;
		$this->sortorder   = 0;
		$this->values      = array();
		$this->isgroup     = true;
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