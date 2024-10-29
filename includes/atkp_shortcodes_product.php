<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class atkp_shortcodes_product {
	/**
	 * Construct the plugin object
	 */
	public function __construct( $pluginbase ) {
		add_shortcode( ATKP_PRODUCT_SHORTCODE, array( &$this, 'shortcode' ) );
	}


	function shortcode( $atts, $content = "" ) {
		try {

			$a = shortcode_atts( array(
				'id'             => '',
				'ean'            => '',
				'asin'           => '',
				'template'       => '',
				'elementcss'     => '',
				'containercss'   => '',
				'buttontype'     => '',
				'field'          => '',
				'link'           => '',
				'hidedisclaimer' => 'no',
				'tracking_id'    => '',
				'offerstemplate' => '',
				'imagetemplate'  => '',
				'shopid'         => '',
				'ajax_mode'      => 'none',
			), $atts );

			$id           = '';
			$template     = 'box';
			$buttontype   = 'notset';
			$field        = '';
			$link         = false;
			$elementcss   = '';
			$containercss = '';
			$tracking_id  = '';
			$ean          = '';
			$asin          = '';
			$shopid = '';

			$offerstemplate           = '';
			$imagetemplate            = '';
			$ajax_mode                = 'none';

			if ( isset( $a['shopid'] ) ) {
				$shopid = $a['shopid'];
			}

			if ( isset( $a['id'] ) ) {
				$id = $a['id'];
			}

			if ( isset( $a['ajax_mode'] ) && $a['ajax_mode'] != '' ) {
				$ajax_mode = ( $a['ajax_mode'] );
			}

			if ( ! is_numeric( $id ) && $id != '' ) {

				$args = [
					'post_type'      => get_post_types(),
					'fields'         => 'ids',
					'posts_per_page' => 1,
					'post_name__in'  => [ $id ]
				];
				$q    = get_posts( $args );

				if ( count( $q ) == 0 ) {
					$args = [
						'post_type'      => get_post_types(),
						'fields'         => 'ids',
						'posts_per_page' => 1,
						's'              => $id
					];
					$q    = get_posts( $args );
				}

				foreach ( $q as $post ) {
					$id = intval( $post );
					break;
				}

				if ( count( $q ) == 0 ) {

					$id2 = atkp_product::idbyname( $id );

					if ( $id2 == null ) {
						throw new Exception( 'product (name) not found: ' . $id );
					} else {
						$id = $id2;
					}
				}
			}
			if ( isset( $a['asin'] ) && $id == '' ) {
				$asin = $a['asin'];
				if ( $asin != '' ) {
					$id = atkp_product::idbyasin( $asin );

					if($id == null && $shopid != '') {
						$globaltools = new atkp_global_tools();

						$gif_data = $globaltools->atkp_import_product( $shopid, $asin, 'ASIN','', '', '', '', '' );
						if(isset($gif_data['postid']))
							$id = $gif_data['postid'];
					}
				}
			}
			if ( isset( $a['ean'] ) && $id == '' ) {
				$ean = $a['ean'];
				if ( $ean != '' ) {
					$id = atkp_product::idbyean( $ean );

					if($id == null) {
						$globaltools = new atkp_global_tools();

						$gif_data = $globaltools->atkp_import_product( $shopid, $ean, 'EAN','', '', '', '', '' );
						if(isset($gif_data['postid']))
							$id = $gif_data['postid'];
					}

				}
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

			if ( isset( $a['field'] ) && ! empty( $a['field'] ) ) {
				$field = $a['field'];
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

			if ( isset( $a['hidedisclaimer'] ) && ! empty( $a['hidedisclaimer'] ) ) {
				if ( $a['hidedisclaimer'] == 'yes' ) {
					$hidedisclaimer = true;
				} else if ( $a['hidedisclaimer'] == 'no' ) {
					$hidedisclaimer = false;
				}
			}

			if ( isset( $a['link'] ) && $a['link'] == 'yes' ) {
				$link = true;
			}

			if ( isset( $a['tracking_id'] ) && ! empty( $a['tracking_id'] ) ) {
				$tracking_id = $a['tracking_id'];
			}


			if ( $id == '' ) {
				if ( get_post_type() == ATKP_PRODUCT_POSTTYPE ) {
					$id             = get_the_ID();
					$hidedisclaimer = true;

				} else if ( get_post_type() == 'product' ) {
					$woo_id         = get_the_ID();
					$hidedisclaimer = true;
					//woocommerce
					//INFO: Auch in external_featuredimage in verwendung

					$productid = ATKPTools::get_post_setting( $woo_id, ATKP_PLUGIN_PREFIX . '_sourceproductid' );

					if ( $productid != '' && $productid != 0 ) {
						$id = $productid;
					} else {
						$eanfield = get_option( ATKP_PLUGIN_PREFIX . '_woo_ean_field', '' );
						$keytype  = get_option( ATKP_PLUGIN_PREFIX . '_woo_keytype', 'ean' );

						if ( $eanfield == '' || $eanfield == 'sku' ) {
							$ean = ATKPTools::get_post_setting( $woo_id, '_sku' );
						} else {
							$ean = ATKPTools::get_post_setting( $woo_id, $eanfield );
						}


						if ( $keytype == 'id' ) {
							$exists = atkp_product::exists( $ean );

							if ( $exists ) {
								$id = $ean;
							}
						} else {
							$id = atkp_product::idbyean( $ean );
						}
					}
				} else {
					$queried_object = get_queried_object();

					if ( $queried_object ) {
						$post_id      = $queried_object->ID;
						$product_temp = ATKPTools::get_post_setting( $post_id, ATKP_PLUGIN_PREFIX . '_product' );

						if ( $product_temp != null ) {
							$id = $product_temp;
						}
					}
				}
			}


			$output            = new atkp_output();
			$output->ajax_mode = $ajax_mode;

			return $output->get_product_output( $id, $template, $content, $buttontype, $field, $link, $elementcss, $containercss, $hidedisclaimer, $tracking_id, $offerstemplate, $imagetemplate );

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


?>