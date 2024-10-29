<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

define( 'ATKP_EVENT', strtolower( ATKP_PLUGIN_PREFIX ) . '_event' );
define( 'ATKP_CHECK', strtolower( ATKP_PLUGIN_PREFIX ) . '_check' );
define( 'ATKP_CSVIMPORT', strtolower( ATKP_PLUGIN_PREFIX ) . '_csvimport' );

define( 'ATKP_SHOP_POSTTYPE', strtolower( ATKP_PLUGIN_PREFIX ) . '_shop' );
define( 'ATKP_LIST_POSTTYPE', strtolower( ATKP_PLUGIN_PREFIX ) . '_list' );
define( 'ATKP_PRODUCT_POSTTYPE', strtolower( ATKP_PLUGIN_PREFIX ) . '_product' );
define( 'ATKP_TEMPLATE_POSTTYPE', strtolower( ATKP_PLUGIN_PREFIX ) . '_template' );
define( 'ATKP_FIELDGROUP_POSTTYPE', strtolower( ATKP_PLUGIN_PREFIX ) . '_fieldgroup' );

define( 'ATKP_SHORTCODE', strtolower( ATKP_PLUGIN_PREFIX ) . '_shortcode' );
define( 'ATKP_LIST_SHORTCODE', strtolower( ATKP_PLUGIN_PREFIX ) . '_list' );

define( 'ATKP_PRODUCT_SHORTCODE', strtolower( ATKP_PLUGIN_PREFIX ) . '_product' );
define( 'ATKP_SEARCHFORM_SHORTCODE', strtolower( ATKP_PLUGIN_PREFIX ) . '_searchform' );
define( 'ATKP_WIDGET', strtolower( ATKP_PLUGIN_PREFIX ) . '_widget' );
define( 'ATKP_LIVELIST_SHORTCODE', strtolower( ATKP_PLUGIN_PREFIX ) . '_livelist' );

if ( ! defined( 'ATKP_VARIATION_COUNT' ) ) {
	define( 'ATKP_VARIATION_COUNT', 5 );
}
if ( ! defined( 'ATKP_MANUALOFFER_COUNT' ) ) {
	define( 'ATKP_MANUALOFFER_COUNT', 3 );
}
if ( ! defined( 'ATKP_FILTER_COUNT' ) ) {
	define( 'ATKP_FILTER_COUNT', 10 );
}
if ( ! defined( 'ATKP_LOGFILE' ) ) {
	$log_key = ATKPTools::get_setting( ATKP_PLUGIN_PREFIX . '_logkey' );
	if ( $log_key == '' ) {
		$log_key = uniqid();
		ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_logkey', $log_key );
	}

	define( 'ATKP_LOGFILE', WP_CONTENT_DIR . '/atkp-' . $log_key . '-debug.log' );
}
if ( ! defined( 'ATKP_TEMPLATEDIR' ) ) {
	define( 'ATKP_TEMPLATEDIR', ATKP_PLUGIN_DIR . '/templates' );
}

define( 'ATKP_STORE_URL', 'https://www.affiliate-toolkit.com/' );
define( 'ATKP_SUBSHOPTYPE', '-1' );

require_once( ATKP_PLUGIN_DIR . '/lib/vendor/imelgrat/barcode-validator/src/barcode-validator.php' );

ATKPSettings::load_settings();

$atkp_options = new atkp_options();


add_filter( 'atkp_variation_name', 'my_atkp_variation_name', 10 );

function my_atkp_variation_name( $variationName ) {
	switch ( $variationName ) {
		case 'Size':
			// return 'Größe';

			return __( 'Size', ATKP_PLUGIN_PREFIX );
			break;
		case 'Color':
			return __( 'Color', ATKP_PLUGIN_PREFIX );
			break;
		default:
			return $variationName;
	}
}

add_filter( 'atkp_find_product', 'my_atkp_find_product_callback', 10, 7 );


function my_atkp_find_product_callback( $product_id, $shop_id, $asin, $ean, $title, $brand, $mpn ) {

	//search in internal database

	if ( $product_id <= 0 && $asin != '' ) {
		$prdid = atkp_product::idbyasin( $asin );

		if ( $prdid > 0 ) {
			$product_id = $prdid;
		}
	}

	if ( $product_id <= 0 && $ean != '' ) {
		$eans = explode( ',', $ean );

		foreach ( $eans as $e ) {
			if ( $e == '' ) {
				continue;
			}

			$prdid = atkp_product::idbyean( trim( $e ) );

			if ( $prdid > 0 ) {
				$product_id = $prdid;
			}
		}
	}

	if ( $product_id <= 0 && $title != '' ) {
		$prdid = atkp_product::idbyname( $title );

		if ( $prdid > 0 ) {
			$product_id = $prdid;
		}
	}

	if ( $product_id <= 0 && $brand != '' && $mpn != '' ) {

		$args = array(
			'post_type'      => ATKP_PRODUCT_POSTTYPE,
			'post_status'    => array( 'publish', 'draft' ),
			'posts_per_page' => 2,

			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key'     => ATKP_PRODUCT_POSTTYPE . '_mpn',
					'value'   => $mpn,
					'compare' => '=',
				),
				array(
					'key'     => ATKP_PRODUCT_POSTTYPE . '_brand',
					'compare' => '=',
					'value'   => $brand,
				),
			)
		);

		$posts = get_posts( $args );

		if ( count( $posts ) == 1 ) {
			$product_id = $posts[0]->ID;
		}
	}


	return $product_id;
}


//add_filter( 'atkp_post_exists', 'my_atkp_post_exists', 10, 7 );

/**
 * @param $post_id
 * @param $shopid
 * @param $asin
 * @param $asintype
 * @param $title
 * @param $brand
 * @param $mpn
 *
 * @return int|mixed|null
 */
function my_atkp_post_exists( $post_id, $shopid, $asin, $asintype, $title, $brand, $mpn ) {

	if ( $brand != '' && $mpn != '' ) {

		$args = array(
			'post_type'      => ATKP_PRODUCT_POSTTYPE,
			'post_status'    => array( 'publish', 'draft' ),
			'posts_per_page' => 2,

			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key'     => ATKP_PRODUCT_POSTTYPE . '_mpn',
					'value'   => $mpn,
					'compare' => '=',
				),
				array(
					'key'     => ATKP_PRODUCT_POSTTYPE . '_brand',
					'compare' => '=',
					'value'   => $brand,
				),
			)
		);

		$posts = get_posts( $args );

		if ( count( $posts ) == 1 ) {
			return $posts[0]->ID;
		}

		return $post_id;
	}

	switch ( $asintype ) {
		default:
		case 'ASIN':
			$prdid = atkp_product::idbyasin( $asin );

			if ( $prdid > 0 ) {
				$post_id = $prdid;
			}
			break;
		case 'EAN':
			$prdid = atkp_product::idbyean( $asin );

			if ( $prdid > 0 ) {
				$post_id = $prdid;
			}
			break;
		case 'ARTICLENUMBER':
			break;
		case 'TITLE':
			$prdid = atkp_product::idbyname( $asin );

			if ( $prdid > 0 ) {
				$post_id = $prdid;
			}
			break;
	}

	return $post_id;

}

add_filter( 'atkp_ajax_products', 'my_atkp_ajax_products_demo', 10, 2 );

function my_atkp_ajax_products_demo( $products, $parameters ) {

	if ( count( $products ) > 0 && $products[0]->productid == - 1 && ( $parameters->templateid == 'list_display' || $parameters->templateid == 'grid_3_columns' || $parameters->templateid == 'product_table' ) ) {
		$products[] = $products[0];
		$products[] = $products[0];
	} else if ( count( $products ) > 0 && $products[0]->productid == - 1 && ( $parameters->templateid == 'grid_2_columns' ) ) {
		$products[] = $products[0];
	}

	return $products;
}


add_action( 'template_redirect', 'my_atkp_out_redirect', 20 );
function my_atkp_out_redirect() {

	$request_url = $_SERVER['REQUEST_URI'];

	if ( ATKPTools::str_contains( $request_url, '/a_out/' ) ) {
		$hash     = ! isset( $_GET['hash'] ) ? null : $_GET['hash'];
		$site_key = atkp_formatter::get_sitekey();

		if ( isset( $_GET['url'] ) ) {
			$url = $_GET['url'];

			//redirect by url
			$url = base64_decode( $url );

			if ( md5( $url . $site_key ) != $hash ) {
				die( 'Checksum is invalid' );
			}

			if ( substr( strtolower( $url ), 0, 7 ) !== 'http://' && substr( strtolower( $url ), 0, 8 ) !== 'https://' ) {
				die( 'URL must start with http:// or https://' );
			}

			do_action( 'atkp_out_link_redirect', intval( $_GET['pid'] ), intval( $_GET['sid'] ), intval( $_GET['pt'] ) );

			wp_redirect( $url, 301 );
		} else if ( isset( $_GET['pid'] ) && isset( $_GET['sid'] ) && isset( $_GET['pt'] ) ) {
			//redirect by id
			$url        = '';
			$product_id = intval( $_GET['pid'] );
			$shop_id    = intval( $_GET['sid'] );
			$link_type  = intval( $_GET['pt'] );

			if ( md5( $product_id . $site_key ) != $hash ) {
				die( 'Checksum is invalid' );
			}

			$prds = atkp_product_collection::load( $product_id, $shop_id );

			$prd = null;
			foreach ( $prds->products as $p ) {

				if ( $p->ismainshop ) {

					$prd = $p;
					break;
				}
			}

			if ( $prd == null ) {
				$prd = $prds->get_main_product();
			}


			switch ( $link_type ) {
				default:
				case atkp_link_type::Link:
				case atkp_link_type::Offer:
				case atkp_link_type::Image:
					$url = $prd->producturl;
					break;
				case atkp_link_type::Cart:
					$url = $prd->addtocarturl;
					break;
				case atkp_link_type::Customerreview:
					$url = $prd->customerreviewurl;
					break;
			}

			do_action( 'atkp_out_link_redirect', $product_id, $shop_id, $link_type );

			wp_redirect( $url, 301 );
		}
	} else if ( isset( $_GET['a_image'] ) && intval( $_GET['a_image'] ) == 1 ) {

		if ( isset( $_GET['pid'] ) && isset( $_GET['sid'] ) && isset( $_GET['name'] ) ) {
			$product_id = intval( $_GET['pid'] );
			$list_id    = intval( $_GET['lid'] );
			$shop_id    = intval( $_GET['sid'] );
			$image_name = strval( base64_decode( $_GET['name'] ) );

			if ( $image_name == '' ) {
				wp_die( 'image missing' );
			}
			//if($product_id < 0)
			//	wp_die('pid missing');
			if ( $shop_id < 0 && $list_id < 0 && $product_id < 0 ) {
				wp_die( 'sid missing' );
			}
			$image_url = '';

			if ( $product_id <= 0 && $list_id <= 0 ) {
				//shop logo
				$shop = atkp_shop::load( $shop_id );

				if ( $shop != null ) {
					if ( ATKPTools::str_contains( $shop->customlogourl, $image_name ) ) {
						$image_url = $shop->customlogourl;
					} else if ( ATKPTools::str_contains( $shop->customsmalllogourl, $image_name ) ) {
						$image_url = $shop->customsmalllogourl;
					}
				}

				if ( ATKPTools::startsWith( $image_url, '//' ) ) {
					$image_url = 'https:' . $image_url;
				}


			} else if ( $product_id > 0 ) {
				$prds    = atkp_product_collection::load( $product_id, $shop_id );
				$prd_man = atkp_product::load( $product_id );

				$shop_id = null;
				foreach ( $prds->products as $p ) {

					if ( $p->ismainshop ) {

						$shop_id = $p->shopid;
						break;
					}
				}

				$prd = $prds->get_main_product( $shop_id );

				if ( ATKPTools::str_contains( $prd->largeimageurl, $image_name ) ) {
					$image_url = $prd->largeimageurl;
				} else if ( ATKPTools::str_contains( $prd->mediumimageurl, $image_name ) ) {
					$image_url = $prd->mediumimageurl;
				} else if ( ATKPTools::str_contains( $prd->smallimageurl, $image_name ) ) {
					$image_url = $prd->smallimageurl;
				} else if ( $prd->images != null ) {
					foreach ( $prd->images as $img ) {
						if ( ATKPTools::str_contains( $img->largeimageurl, $image_name ) ) {
							$image_url = $img->largeimageurl;
						} else if ( ATKPTools::str_contains( $img->mediumimageurl, $image_name ) ) {
							$image_url = $img->mediumimageurl;
						} else if ( ATKPTools::str_contains( $img->smallimageurl, $image_name ) ) {
							$image_url = $img->smallimageurl;
						}
					}
				}

				if ( $prd->variations != null ) {
					foreach ( $prd->variations as $var ) {
						if ( ATKPTools::str_contains( $var->smallimageurl, $image_name ) ) {
							$image_url = $var->smallimageurl;
						}
					}
				}
			} else if ( $list_id > 0 ) {
				$list = atkp_list::load( $list_id );

				$x          = new atkp_output();
				$outputprds = $x->get_list_products( $list_id, 100, false, array(), false, 999, '', $max_num_pages, $found_posts );

				foreach ( $outputprds as $prd ) {

					if ( ATKPTools::str_contains( $prd->largeimageurl, $image_name ) ) {
						$image_url = $prd->largeimageurl;
					} else if ( ATKPTools::str_contains( $prd->mediumimageurl, $image_name ) ) {
						$image_url = $prd->mediumimageurl;
					} else if ( ATKPTools::str_contains( $prd->smallimageurl, $image_name ) ) {
						$image_url = $prd->smallimageurl;
					} else if ( $prd->images != null ) {
						foreach ( $prd->images as $img ) {
							if ( ATKPTools::str_contains( $img->largeimageurl, $image_name ) ) {
								$image_url = $img->largeimageurl;
							} else if ( ATKPTools::str_contains( $img->mediumimageurl, $image_name ) ) {
								$image_url = $img->mediumimageurl;
							} else if ( ATKPTools::str_contains( $img->smallimageurl, $image_name ) ) {
								$image_url = $img->smallimageurl;
							}
						}
					}
				}


			}

			if ( $image_url == '' ) {
				wp_die( esc_html__('image not found in our db: ' . $image_name, ATKP_PLUGIN_PREFIX) );
			}

			$uploads_dir = wp_upload_dir()['basedir'];

			header( 'Content-Type: image/jpeg' );
			header( 'X-Robots-Tag: noindex' );
			header( "HTTP/1.1 200 OK" );

			if ( file_exists( $uploads_dir ) ) {
				$cache_dir = $uploads_dir . '/affiliate-toolkit-cache/';

				if ( ! file_exists( $cache_dir ) ) {
					mkdir( $cache_dir );
				}

				$filename = md5( $image_url );

				if ( file_exists( $cache_dir . $filename ) && filemtime( $cache_dir . $filename ) >= ( time() - 86400 ) ) {
					readfile( $cache_dir . $filename );
					exit;
				} else {
					file_put_contents( $cache_dir . $filename, file_get_contents( $image_url ) );
					readfile( $cache_dir . $filename );
					exit;
				}
			}

			readfile( $image_url );
			exit;
		}

	}
}




