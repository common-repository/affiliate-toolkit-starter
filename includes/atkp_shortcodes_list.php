<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class atkp_shortcodes_list {
	/**
	 * Construct the plugin object
	 */
	public function __construct( $pluginbase ) {
		add_shortcode( ATKP_LIST_SHORTCODE, array( &$this, 'shortcode' ) );
	}


	function shortcode( $atts, $content = "" ) {
		try {

			$a = shortcode_atts( array(
				'id'             => '',
				'template'       => '',
				'elementcss'     => '',
				'containercss'   => '',
				'buttontype'     => '',
				'limit'          => 0,
				'randomsort'     => 'no',
				'hidedisclaimer' => 'no',
				'tracking_id'    => '',
				'product_ids'    => '',
				'offerstemplate' => '',
				'imagetemplate'  => '',
				'parseparams'    => 'no',
				'itemsperpage'   => 0,
				'filter'         => '',
				'keyword'        => '',
				'shopid'         => '',
				'ajax_mode'      => 'none',
			), $atts, 'atkp_list' );

			$id             = '';
			$template       = 'wide';
			$buttontype     = 'notset';
			$elementcss     = '';
			$containercss   = '';
			$field          = '';
			$limit          = ATKPSettings::$list_default_count == '' || ATKPSettings::$list_default_count == '0' ? 0 : ATKPSettings::$list_default_count;
			$randomsort     = false;
			$hidedisclaimer = false;
			$tracking_id    = '';
			$product_ids    = array();
			$filter         = '';
			$shopid         = '';
			$ajax_mode      = 'none';

			if ( isset( $a['shopid'] ) ) {
				$shopid = $a['shopid'];
			}

			$parseparams  = false;
			$itemsperpage = 0;

			$offerstemplate = '';
			$imagetemplate  = '';

			if ( isset( $a['id'] ) ) {
				$id = $a['id'];
			}
			if ( isset( $a['template'] ) && ! empty( $a['template'] ) ) {
				$template = $a['template'];
			}

			if ( isset( $a['filter'] ) && ! empty( $a['filter'] ) ) {
				$filter = $a['filter'];
			}

			if ( isset( $a['elementcss'] ) && ! empty( $a['elementcss'] ) ) {
				$elementcss = $a['elementcss'];
			}
			if ( isset( $a['containercss'] ) && ! empty( $a['containercss'] ) ) {
				$containercss = $a['containercss'];
			}

			if ( isset( $a['buttontype'] ) && ! empty( $a['buttontype'] ) ) {
				$buttontype = $a['buttontype'];
			}

			if ( isset( $a['offerstemplate'] ) && ! empty( $a['offerstemplate'] ) ) {
				$offerstemplate = $a['offerstemplate'];
			}
			if ( isset( $a['imagetemplate'] ) && ! empty( $a['imagetemplate'] ) ) {
				$imagetemplate = $a['imagetemplate'];
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

			if ( isset( $a['parseparams'] ) && ! empty( $a['parseparams'] ) ) {
				if ( $a['parseparams'] == 'yes' ) {
					$parseparams = true;
				} else if ( $a['parseparams'] == 'no' ) {
					$parseparams = false;
				}
			}


			if ( isset( $a['ajax_mode'] ) && $a['ajax_mode'] != '' ) {
				$ajax_mode = ( $a['ajax_mode'] );
			}

			if ( isset( $a['limit'] ) && $a['limit'] > 0 ) {
				$limit = intval( $a['limit'] );
			}

			if ( isset( $a['itemsperpage'] ) && $a['itemsperpage'] > 0 ) {
				$itemsperpage = intval( $a['itemsperpage'] );
			}

			if ( isset( $a['tracking_id'] ) && ! empty( $a['tracking_id'] ) ) {
				$tracking_id = $a['tracking_id'];
			}

			if ( isset( $a['product_ids'] ) && ! empty( $a['product_ids'] ) ) {
				$product_ids = explode( ',', $a['product_ids'] );
			}

			if ( isset( $a['keyword'] ) && ! empty( $a['keyword'] ) && $shopid != '' ) {
				$keyword = $a['keyword'];

				$id = atkp_list::idbyname($keyword);

				if($id == null) {
					$globaltools = new atkp_global_tools();

					$gif_data = $globaltools->atkp_create_list( $shopid, $keyword, '20', $keyword, 'All', '', false );

					if ( isset( $gif_data['postid'] ) ) {
						$id = $gif_data['postid'];
					}
				}
			}

			if ( count( $product_ids ) == 0 && $id != '' ) {

				//wenn der Name der Liste übergeben wurde, dann ist es kein numerischer wert
				if ( ! is_numeric( $id ) ) {

					$id2 = atkp_list::idbyname( $id );

					if ( $id2 == null ) {
						throw new Exception( 'list (name) not found: ' . $id );
					} else {
						$id = $id2;
					}
				}

				//validation der liste
				$list = get_post( $id );

				if ( ! isset( $list ) || $list == null ) {
					throw new Exception( 'list not found: ' . $id );
				}
				if ( $list->post_type != ATKP_LIST_POSTTYPE ) {
					throw new Exception( 'invalid post_type: ' . $list->post_type . ', id: ' . $id );
				}
				if ( $list->post_status != 'publish' && $list->post_status != 'draft' ) {
					throw new Exception( 'list not available: ' . $id );
				}
			}

			if ( $id == '' ) {
				if ( get_post_type() == ATKP_LIST_POSTTYPE ) {
					$id             = get_the_ID();
					$hidedisclaimer = true;

				} else {
					$queried_object = get_queried_object();

					if ( $queried_object ) {
						$post_id      = $queried_object->ID;
						$product_temp = ATKPTools::get_post_setting( $post_id, ATKP_PLUGIN_PREFIX . '_list' );

						if ( $product_temp != null ) {
							$id = $product_temp;
						}
					}
				}
			}

			$output            = new atkp_output();
			$output->ajax_mode = $ajax_mode;

			return $output->get_list_output( $id, $template, $content, $buttontype, $elementcss, $containercss, $limit, $randomsort, $hidedisclaimer, $tracking_id, $product_ids, $offerstemplate, $imagetemplate, $parseparams, $itemsperpage, $filter );

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


