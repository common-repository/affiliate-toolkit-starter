<?php

class atkp_output_handle {
	public function __construct() {

	}
}

function atkp_get_formatter() {
	$templatehelper = new atkp_template_helper();
	$parameters     = new atkp_template_parameters();
	$parameters->buildTemplateParameters( '', array() );

	$formatter = new atkp_formatter( $templatehelper, $parameters );

	return $formatter;
}

function atkp_get_product_ids( $ids ) {
	if ( $ids == '' ) {
		if ( get_post_type() == ATKP_LIST_POSTTYPE ) {
			$ids = get_the_ID();
		} else if ( get_post_type() == ATKP_PRODUCT_POSTTYPE ) {
			$ids = get_the_ID();
		} else {
			$queried_object = get_queried_object();

			if ( $queried_object ) {
				$post_id      = $queried_object->ID;
				$product_temp = ATKPTools::get_post_setting( $post_id, ATKP_PLUGIN_PREFIX . '_list' );

				if ( $product_temp != null ) {
					$ids = $product_temp;
				}

				$product_temp = ATKPTools::get_post_setting( $post_id, ATKP_PLUGIN_PREFIX . '_product' );

				if ( $product_temp != null ) {
					$ids = $product_temp;
				}
			}
		}
	}

	$parts = is_array( $ids ) ? $ids : explode( ',', $ids );

	foreach ( $parts as $part ) {
		$part_id = 0;
		if ( is_numeric( $part ) ) {
			//load by id: list or product

			$part_id = intval( $part );
		} else {
			//load by slug list or product
			//load by title list or product
			$args = [
				'post_type'      => get_post_types(),
				'fields'         => 'ids',
				'posts_per_page' => 1,
				'post_name__in'  => [ $part ]
			];
			$q    = get_posts( $args );

			if ( count( $q ) == 0 ) {
				$args = [
					'post_type'      => get_post_types(),
					'fields'         => 'ids',
					'posts_per_page' => 1,
					's'              => $part
				];
				$q    = get_posts( $args );
			}

			foreach ( $q as $post ) {
				$part_id = intval( $post );
				break;
			}
		}


		$posttype = get_post_type( $part_id );

		if ( $posttype == 'atkp_list' ) {
			$atkp_listtable_helper = new atkp_listtable_helper();
			$selectedshopid        = ATKPTools::get_post_setting( $part_id, ATKP_LIST_POSTTYPE . '_shopid' );
			$productlist           = $atkp_listtable_helper->load_list( $part_id, $selectedshopid );

			foreach ( $productlist as $listentry ) {
				$listentry['list_id'] = $part_id;
				$product_ids[]        = $listentry;
			}

		} else if ( $posttype == 'atkp_product' ) {
			$item          = array();
			$item['type']  = 'productid';
			$item['value'] = $part_id;

			$product_ids[] = $item;
		}
	}

	return $product_ids;
}

/**
 * Creates a product box by given parameters. Returns HTML.
 *
 * @param $ids
 * @param $template
 * @param $limit
 * @param $field
 * @param $link
 * @param $content
 * @param $randomsort
 * @param $hidedisclaimer
 * @param $buttontype
 * @param $tracking_id
 * @param $elementcss
 * @param $containercss
 * @param $ajax_mode
 *
 * @return string|null
 * @throws Exception
 */
function atkp_display_box(
	$ids,
	$template,
	$limit = 0,
	$field = '',
	$link = false,
	$content = '',
	$randomsort = false,
	$hidedisclaimer = false,
	$buttontype = 'notset',
	$tracking_id = '',
	$elementcss = '',
	$containercss = '',
	$ajax_mode = 'none'
) {

	$product_ids = atkp_get_product_ids( $ids );

	$output            = new atkp_output();
	$output->ajax_mode = $ajax_mode;

	if ( $link || $field != '' ) {
		return $output->get_product_output( $product_ids[0]['value'], $template, $content, $buttontype, $field, $link, $elementcss, $containercss, $hidedisclaimer, $tracking_id );
	} else {
		return $output->get_list_output( '', $template, $content, $buttontype, $elementcss, $containercss, $limit, $randomsort, $hidedisclaimer, $tracking_id, $product_ids );
	}
}

function atkp_get_shop( $id ) {

	$shop = wp_cache_get( 'atkp_get_shop_' . $id );
	if ( false === $shop ) {
		$shop = atkp_shop::load( $id );

		if ( $shop == null ) {
			$prd  = atkp_get_product( $id );
			$shop = atkp_shop::load( $prd->shopid );
		}

		wp_cache_set( 'atkp_get_shop_' . $id, $shop );
	}

	return $shop;
}

function atkp_get_product( $id = '' ) {
	$prd = wp_cache_get( 'atkp_get_product_' . $id );
	if ( false === $prd ) {
		$product_ids = atkp_get_product_ids( $id );

		if ( count( $product_ids ) == 0 ) {
			return null;
		}

		$product_ids = atkp_get_product_ids( $id );

		if ( count( $product_ids ) == 0 ) {
			return '';
		}

		$prd_coll = atkp_product_collection::load( $product_ids[0]['value'], '' );
		$prd      = $prd_coll->get_main_product();


		wp_cache_set( 'atkp_get_product_' . $id, $prd );
	}

	return $prd;
}


/**
 * Returns the pro list of the product
 *
 * @param string $id The product id or a list id
 * @param string $content_mask The format of a list item
 *
 * @return string
 */
function atkp_get_pro_list( $id = '', $content_mask = '%s' ) {
	$prd = atkp_get_product( $id );

	$formatter = atkp_get_formatter();

	if ( $prd == null ) {
		return '';
	}

	return $formatter->get_proslist( $prd, $content_mask );
}

/**
 * Returns the contra list of the product
 *
 * @param string $id The product id or a list id
 * @param string $content_mask The format of a list item
 *
 * @return string
 */
function atkp_get_contra_list( $id = '', $content_mask = '%s' ) {
	$prd = atkp_get_product( $id );

	$formatter = atkp_get_formatter();

	if ( $prd == null ) {
		return '';
	}

	return $formatter->get_contralist( $prd, $content_mask );
}

/**
 * Returns the test badge of the product
 *
 * @param string $id The product id or a list id
 *
 * @return string
 */
function atkp_get_test_badge( $id = '' ) {
	$prd = atkp_get_product( $id );

	$formatter = atkp_get_formatter();

	if ( $prd == null ) {
		return '';
	}

	return $formatter->get_testresult( $prd );
}

/**
 * Returns the featured text of the product
 *
 * @param string $id The product id or a list id
 *
 * @return string
 */
function atkp_get_featuretext( $id = '' ) {
	$prd = atkp_get_product( $id );

	$formatter = atkp_get_formatter();

	if ( $prd == null ) {
		return '';
	}

	return $formatter->get_featuretext( $prd );
}

/**
 * Returns the description of the product
 *
 * @param string $id The product id or a list id
 *
 * @return string
 */
function atkp_get_descriptiontext( $id = '' ) {
	$prd = atkp_get_product( $id );

	$formatter = atkp_get_formatter();

	if ( $prd == null ) {
		return '';
	}

	return $formatter->get_descriptiontext( $prd );
}

/**
 * Returns the featured text (shorted)
 *
 * @param string $id The product id or a list id
 *
 * @return string
 */
function atkp_get_featuretext_short( $id = '' ) {
	$prd = atkp_get_product( $id );

	$formatter = atkp_get_formatter();

	if ( $prd == null ) {
		return '';
	}

	return $formatter->get_featuretext_short( $prd );
}

/**
 * Returns the description text (shorted)
 *
 * @param string $id The product id or a list id
 *
 * @return string
 */
function atkp_get_descriptiontext_short( $id = '' ) {
	$prd = atkp_get_product( $id );

	$formatter = atkp_get_formatter();

	if ( $prd == null ) {
		return '';
	}

	return $formatter->get_descriptiontext_short( $prd );
}

/**
 * Returns the info text of the product
 *
 * @param string $id The product id or a list id
 *
 * @return string
 */
function atkp_get_infotext( $id = '' ) {
	$prd = atkp_get_product( $id );

	$formatter = atkp_get_formatter();

	if ( $prd == null ) {
		return '';
	}

	return $formatter->get_infotext( $prd );
}

/**
 * Returns the button text of the product
 *
 * @param string $id The product id or a list id
 *
 * @return string
 */
function atkp_get_button_text( $id = '', $shop_id = '' ) {
	$prd       = atkp_get_product( $id );
	$shop      = atkp_get_shop( $shop_id );
	$formatter = atkp_get_formatter();

	if ( $prd == null ) {
		return '';
	}

	return $formatter->get_button_text( $prd, $shop );
}

/**
 * Returns the button link of the product
 *
 * @param string $id The product id or a list id
 *
 * @return string
 */
function atkp_get_button_link( $id = '' ) {
	$prd       = atkp_get_product( $id );
	$formatter = atkp_get_formatter();

	if ( $prd == null ) {
		return '';
	}

	return $formatter->get_button_link( $prd );
}

/**
 * Returns the title link of the product
 *
 * @param string $id The product id or a list id
 *
 * @return string
 */
function atkp_get_title_link( $id = '' ) {
	$prd       = atkp_get_product( $id );
	$formatter = atkp_get_formatter();

	if ( $prd == null ) {
		return '';
	}

	return $formatter->get_title_link( $prd );
}

/**
 * Returns the title of the product
 *
 * @param string $id The product id or a list id
 *
 * @return string
 */
function atkp_get_title( $id = '' ) {
	$prd       = atkp_get_product( $id );
	$formatter = atkp_get_formatter();

	if ( $prd == null ) {
		return '';
	}

	return $formatter->get_title( $prd );
}

/**
 * Returns the prime logo
 *
 * @param string $id The product id or a list id
 *
 * @return string
 */
function atkp_get_primelogo( $id = '' ) {
	$prd       = atkp_get_product( $id );
	$formatter = atkp_get_formatter();

	if ( $prd == null ) {
		return '';
	}

	return $formatter->get_primelogo( $prd );
}

/**
 * Returns the product url
 *
 * @param string $id The product id or a list id
 *
 * @return string
 */
function atkp_get_producturl( $id = '' ) {
	$prd       = atkp_get_product( $id );
	$formatter = atkp_get_formatter();

	if ( $prd == null ) {
		return '';
	}

	return $formatter->get_producturl( $prd );
}

/**
 * Returns the product link
 *
 * @param string $id The product id or a list id
 *
 * @return string
 */
function atkp_get_productlink( $id = '' ) {
	$prd       = atkp_get_product( $id );
	$formatter = atkp_get_formatter();

	if ( $prd == null ) {
		return '';
	}

	return $formatter->get_productlink( $prd );
}

/**
 * Returns the add to cart url
 *
 * @param string $id The product id or a list id
 *
 * @return string
 */
function atkp_get_addtocarturl( $id = '' ) {
	$prd       = atkp_get_product( $id );
	$formatter = atkp_get_formatter();

	if ( $prd == null ) {
		return '';
	}

	return $formatter->get_addtocarturl( $prd );
}

/**
 * Returns the add to cart link
 *
 * @param string $id The product id or a list id
 *
 * @return string
 */
function atkp_get_addtocartlink( $id = '' ) {
	$prd       = atkp_get_product( $id );
	$formatter = atkp_get_formatter();

	if ( $prd == null ) {
		return '';
	}

	return $formatter->get_cartlink( $prd );
}

/**
 * Returns the customer reviews url
 *
 * @param string $id The product id or a list id
 *
 * @return string
 */
function atkp_get_customerreviewsurl( $id = '' ) {
	$prd       = atkp_get_product( $id );
	$formatter = atkp_get_formatter();

	if ( $prd == null ) {
		return '';
	}

	return $formatter->get_customerreviewsurl( $prd );
}

/**
 * Returns the raw amount saved
 *
 * @param string $id The product id or a list id
 *
 * @return int
 */
function atkp_get_amount_saved( $id = '' ) {
	$prd       = atkp_get_product( $id );
	$formatter = atkp_get_formatter();

	if ( $prd == null ) {
		return 0;
	}

	return $formatter->get_amountsaved_value( $prd );
}

/**
 * Returns the formatted amount saved
 *
 * @param string $id The product id or a list id
 * @param string $format override the format
 *
 * @return string
 */
function atkp_get_amount_saved_formatted( $id = '', $format = '%s' ) {
	$prd       = atkp_get_product( $id );
	$formatter = atkp_get_formatter();

	if ( $prd == null ) {
		return '';
	}

	return $formatter->get_savetext( $prd, $format );
}

/**
 * Returns the raw percentage saved
 *
 * @param string $id The product id or a list id
 *
 * @return int
 */
function atkp_get_percentage_saved( $id = '' ) {
	$prd       = atkp_get_product( $id );
	$formatter = atkp_get_formatter();

	if ( $prd == null ) {
		return 0;
	}

	return $formatter->get_percentagesaved_value( $prd );
}

/**
 * Returns the formatted percentage saved
 *
 * @param string $id The product id or a list id
 * @param string $format override the format
 *
 * @return string
 */
function atkp_get_percentage_saved_formatted( $id = '', $format = '-%s%%' ) {
	$prd       = atkp_get_product( $id );
	$formatter = atkp_get_formatter();

	if ( $prd == null ) {
		return '';
	}

	return $formatter->get_percentagesaved( $prd, $format );
}

/**
 * Returns the raw list price
 *
 * @param string $id The product id or a list id
 *
 * @return int
 */
function atkp_get_listprice( $id = '' ) {
	$prd       = atkp_get_product( $id );
	$formatter = atkp_get_formatter();

	if ( $prd == null ) {
		return 0;
	}

	return $formatter->get_listprice_value( $prd );
}

/**
 * Returns the raw sale price
 *
 * @param string $id The product id or a list id
 *
 * @return int
 */
function atkp_get_saleprice( $id = '' ) {
	$prd       = atkp_get_product( $id );
	$formatter = atkp_get_formatter();

	if ( $prd == null ) {
		return 0;
	}

	return $formatter->get_saleprice_value( $prd );
}

/**
 * Returns the raw base price
 *
 * @param string $id The product id or a list id
 *
 * @return int
 */
function atkp_get_baseprice( $id = '' ) {
	$prd       = atkp_get_product( $id );
	$formatter = atkp_get_formatter();

	if ( $prd == null ) {
		return 0;
	}

	return $formatter->get_baseprice_value( $prd );
}

/**
 * returns the raw unit price
 *
 * @param string $id The product id or a list id
 *
 * @return int
 */
function atkp_get_unitprice( $id = '' ) {
	$prd       = atkp_get_product( $id );
	$formatter = atkp_get_formatter();

	if ( $prd == null ) {
		return 0;
	}

	return $formatter->get_unitprice_value( $prd );
}

/**
 * Returns the raw shipping price
 *
 * @param string $id The product id or a list id
 *
 * @return int
 */
function atkp_get_shipping( $id = '' ) {
	$prd       = atkp_get_product( $id );
	$formatter = atkp_get_formatter();

	if ( $prd == null ) {
		return 0;
	}

	return $formatter->get_shipping_value( $prd );
}

/**
 * Returns the formatted shipping price
 *
 * @param string $id The product id or a list id
 * @param string $format override the format
 * @param string $emptytext fallback text
 *
 * @return string
 */
function atkp_get_shipping_formatted( $id = '', $format = '%s', $emptytext = '' ) {
	$prd       = atkp_get_product( $id );
	$formatter = atkp_get_formatter();

	if ( $prd == null ) {
		return '';
	}

	return $formatter->get_shipping( $prd, $format, $emptytext );
}

/**
 * Returns the formatted list price
 *
 * @param string $id The product id or a list id
 * @param string $format override the format
 * @param string $emptytext fallback text
 *
 * @return string
 */
function atkp_get_saleprice_formatted( $id = '', $format = '%s', $emptytext = '' ) {
	$prd       = atkp_get_product( $id );
	$formatter = atkp_get_formatter();

	if ( $prd == null ) {
		return '';
	}

	return $formatter->get_pricetext( $prd, $format, $emptytext );
}

/**
 * Returns the formatted base price
 *
 * @param string $id The product id or a list id
 * @param string $format override the format
 * @param string $emptytext fallback text
 *
 * @return string
 */
function atkp_get_baseprice_formatted( $id = '', $format = '(%s / %s)', $emptytext = '' ) {
	$prd       = atkp_get_product( $id );
	$formatter = atkp_get_formatter();

	if ( $prd == null ) {
		return '';
	}

	return $formatter->get_basepricetext( $prd, $format, $emptytext );
}

/**
 * Returns the formatted list price
 *
 * @param string $id The product id or a list id
 * @param string $format override the format
 * @param string $emptytext fallback text
 *
 * @return string
 */
function atkp_get_listprice_formatted( $id = '', $format = '%s', $emptytext = '' ) {
	$prd       = atkp_get_product( $id );
	$formatter = atkp_get_formatter();

	if ( $prd == null ) {
		return '';
	}

	return $formatter->get_listpricetext( $prd, $format, $emptytext );
}

/**
 * Returns a custom field for a product
 *
 * @param string $id The product id or a list id
 * @param string $field_name The name of the custom field
 *
 * @return string
 */
function atkp_get_custom_field( $id = '', $field_name ) {
	$prd       = atkp_get_product( $id );
	$formatter = atkp_get_formatter();

	if ( $prd == null ) {
		return '';
	}

	return $formatter->get_displayfield( $prd, $field_name );
}

/**
 * Returns the last update date for a product
 *
 * @param string $id The product id or a list id
 *
 * @return string
 */
function atkp_get_updatedate( $id = '' ) {
	$prd       = atkp_get_product( $id );
	$formatter = atkp_get_formatter();

	if ( $prd == null ) {
		return '';
	}

	return $formatter->get_refreshdate( $prd );
}

/**
 * Returns the last update time for a product
 *
 * @param string $id The product id or a list id
 *
 * @return string
 */
function atkp_get_updatetime( $id = '' ) {
	$prd       = atkp_get_product( $id );
	$formatter = atkp_get_formatter();

	if ( $prd == null ) {
		return '';
	}

	return $formatter->get_refreshtime( $prd );
}

/**
 * Returns the disclaimer text
 *
 * @param string $id The product id or a list id
 * @param string $disclaimertext
 *
 * @return string
 */
function atkp_get_disclaimer( $id = '', $disclaimertext = '' ) {
	$prd       = atkp_get_product( $id );
	$formatter = atkp_get_formatter();

	if ( $prd == null ) {
		return '';
	}

	return $formatter->get_disclaimer( $prd, $disclaimertext );
}

/**
 * Returns the shop logo url
 *
 * @param int $shop_id
 *
 * @return string
 */
function atkp_get_shop_logo_url( $shop_id = '' ) {
	$shop      = atkp_get_shop( $shop_id );
	$formatter = atkp_get_formatter();

	if ( $shop == null ) {
		return '';
	}

	return $formatter->get_shop_logourl( $shop );
}

/**
 * Returns the shop logo
 *
 * @param int $shop_id
 *
 * @return string
 */
function atkp_get_shop_logo( $shop_id = '' ) {
	$shop      = atkp_get_shop( $shop_id );
	$formatter = atkp_get_formatter();

	if ( $shop == null ) {
		return '';
	}

	return $formatter->get_shop_logo( $shop );
}

/**
 * Resturns the shop title for a shop
 *
 * @param int $shop_id
 *
 * @return string
 */
function atkp_get_shop_title( $shop_id = '' ) {
	$shop      = atkp_get_shop( $shop_id );
	$formatter = atkp_get_formatter();

	if ( $shop == null ) {
		return '';
	}

	return $formatter->get_shop_title( $shop );
}

/**
 * Returns the large image url
 *
 * @param string $id The product id or a list id
 *
 * @return string
 */
function atkp_get_largeimageurl( $id = '' ) {
	$prd = atkp_get_product( $id );

	$formatter = atkp_get_formatter();

	if ( $prd == null ) {
		return '';
	}

	return $formatter->get_largeimageurl( $prd );
}

/**
 * Returns the medium image url
 *
 * @param string $id The product id or a list id
 *
 * @return string
 */
function atkp_get_smallimageurl( $id = '' ) {
	$prd = atkp_get_product( $id );

	$formatter = atkp_get_formatter();

	if ( $prd == null ) {
		return '';
	}

	return $formatter->get_smallimageurl( $prd );
}

/**
 * Returns the medium image url
 *
 * @param string $id The product id or a list id
 *
 * @return string
 */
function atkp_get_mediumimageurl( $id = '' ) {
	$prd = atkp_get_product( $id );

	$formatter = atkp_get_formatter();

	if ( $prd == null ) {
		return '';
	}

	return $formatter->get_mediumnimageurl( $prd );
}

/**
 * The short title of the product
 *
 * @param string $id The product id or a list id
 *
 * @return string
 */
function atkp_get_short_title( $id = '' ) {
	$prd = atkp_get_product( $id );

	$formatter = atkp_get_formatter();

	if ( $prd == null ) {
		return '';
	}

	return $formatter->get_shorttitle( $prd );
}

/**
 * The customer review text
 *
 * @param string $id The product id or a list id
 *
 * @return string
 */
function get_get_reviews_text( $id = '' ) {
	$prd = atkp_get_product( $id );

	$formatter = atkp_get_formatter();

	if ( $prd == null ) {
		return '';
	}

	return $formatter->get_reviewstext( $prd );
}

/**
 * The external review url of the product
 *
 * @param string $id The product id or a list id
 *
 * @return string
 */
function atkp_get_reviews_link( $id = '' ) {
	$prd = atkp_get_product( $id );

	$formatter = atkp_get_formatter();

	if ( $prd == null ) {
		return '';
	}

	return $formatter->get_reviewslink( $prd );
}

/**
 * Returns the product page url
 *
 * @param string $id The product id or a list id
 *
 * @return string|null
 */
function atkp_get_product_page_url( $id = '' ) {
	$prd = atkp_get_product( $id );

	$formatter = atkp_get_formatter();

	if ( $prd == null ) {
		return '';
	}

	return $formatter->get_detailurl( $prd );
}

/**
 * Returns the woocommerce url by the given atkp product id
 *
 * @param string $id The product id or a list id
 *
 * @return string The URL of the woocommerce product
 */
function atkp_get_woocommerce_page_url( $id = '' ) {
	$prd = atkp_get_product( $id );

	$formatter = atkp_get_formatter();

	if ( $prd == null ) {
		return '';
	}

	return $formatter->get_woocommerceurl( $prd );
}

/**
 * Returns a list of images for the product
 *
 * @param string $id The product id or a list id
 * @param bool $include_main_image
 * @param int $max_images
 *
 * @return array|null
 */
function atkp_get_images( $id = '', $include_main_image = true, $max_images = 0 ) {
	$prd = atkp_get_product( $id );

	$formatter = atkp_get_formatter();

	if ( $prd == null ) {
		return null;
	}

	return $formatter->get_images( $prd, $include_main_image, $max_images );
}

/**
 * Returns a list of offers for the product
 *
 * @param string $id The product id or a list id
 * @param bool $include_main_offer
 * @param int $max_offers
 *
 * @return atkp_product_offer[]|null
 */
function atkp_get_offers( $id = '', $include_main_offer = true, $max_offers = 0 ) {
	$prd = atkp_get_product( $id );

	$formatter = atkp_get_formatter();

	if ( $prd == null )
		return null;

	return $formatter->get_offers( $prd, $include_main_offer, $max_offers );
}