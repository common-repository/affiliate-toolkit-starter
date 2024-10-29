<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class atkp_auto_generator {

	public function createOutput( $outputprds, $content, $template, $buttontype, $hidedisclaimer, $tracking_id, $offerstemplate, $imagetemplate, $itemsPerPage ) {
		if ( $offerstemplate == '' || $offerstemplate == null ) {
			$offerstemplate = ATKPSettings::$moreoffers_template;
		}

		if ( ATKPSettings::$access_show_disclaimer ) {
			$disclaimer = ATKPSettings::$access_disclaimer_text;
		}

		if ( ! ATKPSettings::$disablestyles ) {
			$resultValue = '<div class="atkp-container ' . $cssContainerClass . '">';
		} else {
			$resultValue = '';
		}

		$count = 1;


		return 'ha';
	}

}