<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class atkp_default_template_base {
	public function __construct() {

	}

	public function get_header() {
		return '';
	}

	public function get_detail_header() {
		return '';
	}

	public function get_body_header() {
		return '';
	}

	public function get_detail() {
		return '';
	}

	public function get_body_footer() {
		return '';
	}

	public function get_detail_footer() {
		return '';
	}

	public function get_footer() {
		return '';
	}

	public function get_hidedisclaimer() {
		return null;
	}

	public function get_templatetype() {
		return null;
	}

	public function get_includemainoffer() {
		return null;
	}

	public function get_maxoffercount() {
		return null;
	}

	public function get_customdisclaimer() {
		return null;
	}

	public function get_filtertarget() {
		return null;
	}


}

?>