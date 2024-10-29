<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class atkp_shortcodes_atkp {
	/**
	 * Construct the plugin object
	 */
	public function __construct( $pluginbase ) {
		add_shortcode( 'atkp', array( &$this, 'shortcode' ) );
	}


	function shortcode( $atts, $content = "" ) {
		try {

			$a = shortcode_atts( array(
				'ids'            => '',
				'template'       => '',
				'elementcss'     => '',
				'containercss'   => '',
				'limit'          => 0,
				'randomsort'     => 'no',
				'hidedisclaimer' => 'no',
				'tracking_id'    => '',
				'field'          => '',
				'link'           => '',
				'ajax_mode'      => 'none',
			), $atts, ATKP_PLUGIN_PREFIX );

			$ids            = '';
			$template       = 'wide';
			$buttontype     = 'notset';
			$elementcss     = '';
			$containercss   = '';
			$field          = '';
			$limit          = ATKPSettings::$list_default_count == '' || ATKPSettings::$list_default_count == '0' ? 0 : ATKPSettings::$list_default_count;
			$randomsort     = false;
			$hidedisclaimer = false;
			$tracking_id    = '';
			$field          = '';
			$link           = false;
			$ajax_mode      = 'none';

			if ( isset( $a['ids'] ) ) {
				$ids = $a['ids'];
			}
			if ( isset( $a['template'] ) && ! empty( $a['template'] ) ) {
				$template = $a['template'];
			}

			if ( isset( $a['elementcss'] ) && ! empty( $a['elementcss'] ) ) {
				$elementcss = $a['elementcss'];
			}
			if ( isset( $a['containercss'] ) && ! empty( $a['containercss'] ) ) {
				$containercss = $a['containercss'];
			}

			if ( isset( $a['randomsort'] ) && ! empty( $a['randomsort'] ) ) {
				if ( $a['randomsort'] == 'yes' ) {
					$randomsort = true;
				} else if ( $a['randomsort'] == 'no' ) {
					$randomsort = false;
				}
			}

			if ( isset( $a['hidedisclaimer'] ) && ! empty( $a['hidedisclaimer'] ) ) {
				if ( $a['hidedisclaimer'] == 'yes' ) {
					$hidedisclaimer = true;
				} else if ( $a['hidedisclaimer'] == 'no' ) {
					$hidedisclaimer = false;
				}
			}

			if ( isset( $a['limit'] ) && $a['limit'] > 0 ) {
				$limit = intval( $a['limit'] );
			}

			if ( isset( $a['tracking_id'] ) && ! empty( $a['tracking_id'] ) ) {
				$tracking_id = $a['tracking_id'];
			}

			if ( isset( $a['link'] ) && $a['link'] == 'yes' ) {
				$link = true;
			}
			if ( isset( $a['field'] ) && ! empty( $a['field'] ) ) {
				$field = $a['field'];
			}

			if ( isset( $a['ajax_mode'] ) && $a['ajax_mode'] != '' ) {
				$ajax_mode = ( $a['ajax_mode'] );
			}

			return atkp_display_box(
				$ids,
				$template,
				$limit,
				$field,
				$link,
				$content,
				$randomsort,
				$hidedisclaimer,
				$buttontype,
				$tracking_id,
				$elementcss,
				$containercss,
				$ajax_mode );
		} catch ( TypeError $e ) {
			if ( ATKPSettings::$hideerrormessages ) {
				return '';
			} else {
				return 'TypeError: ' . $e->getMessage();
			}
		} catch ( Exception $e ) {
			if ( ATKPSettings::$hideerrormessages ) {
				return '';
			} else {
				return 'Exception: ' . $e->getMessage();
			}
		}
	}
}


