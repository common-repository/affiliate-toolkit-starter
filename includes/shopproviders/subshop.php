<?php

class subshop {
	public $data = array();

	function __construct() {
		$this->logourl      = '';
		$this->shopid       = '';
		$this->programid    = '';
		$this->feedurl      = '';
		$this->productcount = 0;
		$this->title        = '';
		$this->enabled      = false;
	}

	public function copy_from_shop( $shopold ) {

		$this->enabled            = $shopold->enabled;
		$this->customtitle        = $shopold->customtitle;
		$this->customsmalllogourl = $shopold->customsmalllogourl;
		$this->customlogourl      = $shopold->customlogourl;
		$this->customfield1       = $shopold->customfield1;
		$this->customfield2       = $shopold->customfield2;
		$this->customfield3       = $shopold->customfield3;
		$this->chartcolor         = $shopold->chartcolor;
		if ( $this->feedurl == '' ) {
			$this->feedurl = $shopold->feedurl;
		}
		if ( $this->productcount == 0 ) {
			$this->productcount = $shopold->productcount;
		}

		return $this;
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