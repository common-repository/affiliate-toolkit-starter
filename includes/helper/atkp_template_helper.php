<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class atkp_template_helper {

	public $ajax_mode = 'none';
	public $disable_custom_styles = false;
	public $preview_generation = false;

	public function add_shop_info( atkp_formatter $formatter, atkp_shop $myshop, &$placeholders ) {

		$placeholders['shoplogo']         = $formatter->get_shop_logo( $myshop );
		$placeholders['smallshoplogo']    = $formatter->get_shop_smalllogo( $myshop );
		$placeholders['shoptitle']        = $formatter->get_shop_title( $myshop );
		$placeholders['shoplogourl']      = $formatter->get_shop_logourl( $myshop );
		$placeholders['smallshoplogourl'] = $formatter->get_shop_smalllogourl( $myshop );

		$displayfields = $formatter->get_shop_displayfields( $myshop );

		foreach ( $displayfields as $key => $value ) {
			$placeholders[ $key ] = $value;
		}
	}

	public function add_list_info( atkp_formatter $formatter, atkp_list $mylist, &$placeholders ) {
		$placeholders['listtitle'] = $formatter->get_list_title( $mylist );

		$displayfields = $formatter->get_list_displayfields( $mylist );

		foreach ( $displayfields as $key => $value ) {
			$placeholders[ $key ] = $value;
		}
	}

	public function createPlaceholderArray( $myproduct, $itemIdx, $cssContainerClass, $cssElementClass, $content, $addtocart = 'notset', $listid = '', $templateid = '', $tracking_id = '', $offerstemplate = '', $imagetemplate = '', $parameters = null ) {
		$placeholders = array();
		$shop         = null;
		$myprovider   = null;

		if ( $parameters == null ) {
			$parameters       = new atkp_template_parameters();
			$shortcode_params = $parameters->buildShortcodeArray( $content, $cssContainerClass, $cssElementClass, $addtocart, false, $tracking_id, $listid, $templateid, $offerstemplate, $imagetemplate );
			$parameters->buildTemplateParameters( $templateid, $shortcode_params );
		}

		$formatter = new atkp_formatter( $this, $parameters );

		if ( $myproduct->shop != null ) {
			$this->add_shop_info( $formatter, $myproduct->shop, $placeholders );
		}
		if ( $parameters->list != null ) {
			$this->add_list_info( $formatter, $parameters->list, $placeholders );
		}

		$placeholders['shopid'] = $myproduct->shopid;

		$placeholders['listid']    = $listid;
		$placeholders['listtitle'] = $formatter->get_listtitle( $myproduct );

		$placeholders['templateid'] = $templateid;

		$placeholders['mark'] = $formatter->get_mark();

		$placeholders['title']       = $formatter->get_title( $myproduct );
		$placeholders['short_title'] = $formatter->get_shorttitle( $myproduct );

		$placeholders['detailtext'] = $parameters->get_productpage_title();

		$placeholders['detailurl']        = '';
		$placeholders['detaillink']       = '';
		$placeholders['detailvisibility'] = 'visibility: collapse;';

		$placeholders['detailurl']  = $formatter->get_detailurl( $myproduct );
		$placeholders['detaillink'] = $formatter->get_detaillink( $myproduct );

		if ( $placeholders['detaillink'] != '' ) {
			$placeholders['detailvisibility'] = 'visibility: visible;';
		}



		$placeholders['productid'] = $myproduct->productid;

		$placeholders['asin']         = $myproduct->asin;
		$placeholders['ean']          = $myproduct->ean;
		$placeholders['isbn']         = $myproduct->isbn;
		$placeholders['brand']        = $myproduct->brand;
		$placeholders['productgroup'] = $myproduct->productgroup;
		$placeholders['availability'] = $myproduct->availability;
		$placeholders['shipping']     = $formatter->get_shipping( $myproduct, '%s', '' );

		$placeholders['releasedate'] = $myproduct->releasedate;


		$placeholders['manufacturer']    = $myproduct->manufacturer;
		$placeholders['author']          = $myproduct->author;
		$placeholders['brand']           = $myproduct->brand;
		$placeholders['productcategory'] = $myproduct->productgroup;

		//Offers section
		$alloffers = $formatter->get_offers( $myproduct, true );
		$minoffer  = $formatter->get_minoffer( $myproduct, true, $alloffers );
		$maxoffer  = $formatter->get_maxoffer( $myproduct, true, $alloffers );

		$placeholders['offerscount'] = $formatter->get_offercount( $alloffers );


		$placeholders['totalprice'] = $formatter->get_total( $myproduct );

		$placeholders['minprice']     = $minoffer == null ? '' : $formatter->get_offer_price( $minoffer );
		$placeholders['minprice_url'] = $minoffer == null ? '' : $formatter->get_offer_url( $minoffer );


		$placeholders['minprice_shoptitle']        = $minoffer == null ? '' : $minoffer->shop->get_title();
		$placeholders['minprice_shoplogourl']      = $minoffer == null ? '' : $minoffer->shop->get_logourl();
		$placeholders['minprice_smallshoplogourl'] = $minoffer == null ? '' : $minoffer->shop->get_smalllogourl();


		$placeholders['maxprice']     = $maxoffer == null ? '' : $formatter->get_offer_price( $maxoffer );
		$placeholders['maxprice_url'] = $maxoffer == null ? '' : $formatter->get_offer_url( $maxoffer );


		$placeholders['maxprice_shoptitle']        = $maxoffer == null ? '' : $maxoffer->shop->get_title();
		$placeholders['maxprice_shoplogourl']      = $maxoffer == null ? '' : $maxoffer->shop->get_logourl();
		$placeholders['maxprice_smallshoplogourl'] = $maxoffer == null ? '' : $maxoffer->shop->get_smalllogourl();

		//offers section

		for ( $i = 1; $i <= 5; $i ++ ) {
			$placeholders[ 'thumbimages_' . $i ]  = '';
			$placeholders[ 'mediumimages_' . $i ] = '';
			$placeholders[ 'images_' . $i ]       = '';

			$placeholders[ 'thumbimagesurl_' . $i ]  = '';
			$placeholders[ 'mediumimagesurl_' . $i ] = '';
			$placeholders[ 'imagesurl_' . $i ]       = '';
		}

		$idx = 1;
		if ( is_array( $myproduct->images ) ) {
			foreach ( $myproduct->images as $newimage ) {

				$placeholders[ 'thumbimages_' . $idx ]  = $formatter->get_image_smallimage( $myproduct, $newimage );
				$placeholders[ 'mediumimages_' . $idx ] = $formatter->get_image_mediumimage( $myproduct, $newimage );
				$placeholders[ 'images_' . $idx ]       = $formatter->get_image_largeimage( $myproduct, $newimage );

				$placeholders[ 'thumbimagesurl_' . $idx ]  = $formatter->get_image_smallimageurl( $myproduct, $newimage );
				$placeholders[ 'mediumimagesurl_' . $idx ] = $formatter->get_image_mediumimageurl( $myproduct, $newimage );
				$placeholders[ 'imagesurl_' . $idx ]       = $formatter->get_image_largeimageurl( $myproduct, $newimage );

				$idx += 1;
			}
		}

		$placeholders['smallimageurl']  = $formatter->get_smallimageurl( $myproduct );
		$placeholders['mediumimageurl'] = $formatter->get_mediumnimageurl( $myproduct );
		$placeholders['largeimageurl']  = $formatter->get_largeimageurl( $myproduct );

		$placeholders['smallimage']  = $formatter->get_smallimage( $myproduct );
		$placeholders['mediumimage'] = $formatter->get_mediumnimage( $myproduct );
		$placeholders['largeimage']  = $formatter->get_largeimage( $myproduct );
		$placeholders['by_text']     = $formatter->get_bytext( $myproduct );

		$placeholders['productlink'] = $formatter->get_productlink( $myproduct );
		$placeholders['cartlink']    = $formatter->get_cartlink( $myproduct );


		$placeholders['producturl']         = $formatter->get_producturl( $myproduct );
		$placeholders['customerreviewsurl'] = $formatter->get_customerreviewsurl( $myproduct );

		$listurl = $formatter->get_listurl();

		if ( $listurl != '' ) {
			$placeholders['hidelistlink'] = '';
			$placeholders['listlink']     = $formatter->get_listlink();
			$placeholders['listurl']      = $listurl;
			$placeholders['listlinktext'] = __( 'Show me more products', ATKP_PLUGIN_PREFIX );

		} else {
			$placeholders['hidelistlink'] = 'style="display:none"';
			$placeholders['listurl']      = '';
			$placeholders['listlink']     = '';
			$placeholders['listlinktext'] = '';
		}

		$placeholders['link']     = $formatter->get_button_link( $myproduct );
		$placeholders['linktext'] = $formatter->get_button_text( $myproduct );
		$placeholders['linkmark'] = $formatter->get_button_mark( $myproduct );

		$placeholders['titlelink']     = $formatter->get_title_link( $myproduct );
		$placeholders['titlelinkmark'] = $formatter->get_title_mark( $myproduct );

		$placeholders['bestseller_text']   = $formatter->get_bestseller_text( $itemIdx );
		$placeholders['bestseller_number'] = $formatter->get_bestseller_number( $itemIdx );

		$placeholders['reviewsurl'] = $myproduct->reviewsurl;

		$placeholders['reviewcount2'] = '';

		if ( ATKPSettings::$showstarrating ) {

			if ( $myproduct->rating == '' ) {
				$myproduct->rating = 0;
			}

			if ( $myproduct->rating == 0 && get_option( ATKP_PLUGIN_PREFIX . '_hideemptystars', 0 ) ) {
				$placeholders['rating']      = '';
				$placeholders['star_rating'] = '';
			} else {
				$placeholders['rating']       = $formatter->get_rating_text( $myproduct );
				$placeholders['star_rating']  = $formatter->get_star_rating( $myproduct );
				$placeholders['reviewcount2'] = $placeholders['rating'];
			}
		} else {
			$placeholders['rating']      = '';
			$placeholders['star_rating'] = '';
		}

		if ( $myproduct->reviewcount == '' ) {
			$myproduct->reviewcount = 0;
		}

		if ( $myproduct->isownreview ) {
			$reviewstext = __( 'Show review', ATKP_PLUGIN_PREFIX );

			if ( atkp_options::$loader->get_showstarrating() ) {
				$placeholders['reviewcount'] = $reviewstext;
			}

			if ( $myproduct->reviewsurl != '' && atkp_options::$loader->get_linkrating() ) {

				$placeholders['reviewslink'] = $formatter->get_reviewslink( $myproduct );
				$placeholders['markrating']  = '';
			} else {
				$placeholders['reviewslink'] = '';
				$placeholders['markrating']  = '';
			}
		} else {
			$reviewstextNull = __( 'Show customer reviews', ATKP_PLUGIN_PREFIX );
			$reviewstext     = __( '%s customer reviews', ATKP_PLUGIN_PREFIX );
			$reviewstext2    = __( '1 customer review', ATKP_PLUGIN_PREFIX );

			$placeholders['reviewcount'] = '';

			if ( atkp_options::$loader->get_showstarrating() ) {
				if ( $myproduct->reviewcount == '' || $myproduct->reviewcount == 0 ) {

					if ( get_option( ATKP_PLUGIN_PREFIX . '_hideemptyrating', 0 ) ) {
						$placeholders['reviewcount'] = $reviewstextNull = '';
					} else {
						$placeholders['reviewcount'] = $reviewstextNull;
					}

				} else {
					$placeholders['reviewcount'] = sprintf( _n( $reviewstext2, $reviewstext, $myproduct->reviewcount, ATKP_PLUGIN_PREFIX ), $myproduct->reviewcount );

				}
			}

			if ( $myproduct->customerreviewurl != '' && atkp_options::$loader->get_showstarrating() && $placeholders['reviewcount'] != '' ) {
				$placeholders['reviewslink'] = $formatter->get_reviewslink( ( $myproduct ) );
				//$this->create_external_link( $myproduct->customerreviewurl, $placeholders['reviewcount'], $myproduct->title, $listid, $templateid, $myproduct->shopid, 4, $tracking_id );
				$placeholders['markrating'] = $placeholders['mark'];
			} else {
				$placeholders['reviewslink'] = '';

				if ( $placeholders['reviewcount'] == $reviewstextNull ) {
					$placeholders['reviewcount'] = '';
				}
				$placeholders['markrating'] = '';
			}

		}

		$placeholders['prime_icon'] = $formatter->get_primelogo( $myproduct );

		if ( ! ATKPSettings::$showpricediscount ) {
			$placeholders['save_percentage']  = '';
			$placeholders['save_percentage_'] = '';
		} else {
			$placeholders['save_percentage']  = $formatter->get_percentagesaved( $myproduct, '-%s%%' );
			$placeholders['save_percentage_'] = $formatter->get_percentagesaved( $myproduct, '(%s)' );
		}

		if ( $myproduct->amountsaved == '' || ! ATKPSettings::$showpricediscount ) {
			$placeholders['save_text']   = '';
			$placeholders['save_amount'] = '';

		} else {
			$placeholders['save_amount'] = $myproduct->amountsaved;

			if ( $myproduct->percentagesaved != '' && $myproduct->percentagesaved != '0' ) {
				$perc = ' (%s%%)';
			} else {
				$perc = '';
			}

			$placeholders['save_text'] = $formatter->get_savetext( $myproduct, __( 'You Save: %s', ATKP_PLUGIN_PREFIX ) ) . $formatter->get_percentagesaved( $myproduct, $perc );
		}
		if ( ! ATKPSettings::$showprice || ! ATKPSettings::$showpricediscount ) {
			$placeholders['listprice_text'] = '';
		} else {
			$placeholders['listprice_text'] = $formatter->get_listpricetext( $myproduct, __( 'List Price: %s', ATKP_PLUGIN_PREFIX ) );
		}

		if ( ! ATKPSettings::$showprice ) {
			$placeholders['listprice'] = '';
		} else {
			$placeholders['listprice'] = $formatter->get_listpricetext( $myproduct, __( '%s', ATKP_PLUGIN_PREFIX ) );
		}

		if ( ! ATKPSettings::$showprice ) {
			$placeholders['price'] = '';
		} else {
			$placeholders['price'] = $formatter->get_pricetext( $myproduct, __( '%s', ATKP_PLUGIN_PREFIX ), __( 'Price not available', ATKP_PLUGIN_PREFIX ) );
		}

		if ( ! ATKPSettings::$showprice ) {
			$placeholders['price_text'] = '';
		} else {
			$placeholders['price_text'] = $formatter->get_pricetext( $myproduct, __( 'Price: %s', ATKP_PLUGIN_PREFIX ), __( 'Price not available', ATKP_PLUGIN_PREFIX ) );
		}

		if ( atkp_options::$loader->get_showbaseprice() ) {
			$placeholders['price_text'] = $placeholders['price_text'] . ' <span class="atkp_price atkp-baseprice">' . $formatter->get_basepricetext( $myproduct ) . '</span>';
			$placeholders['price']      = $placeholders['price'] . ' <span class="atkp_price atkp-baseprice">' . $formatter->get_basepricetext( $myproduct ) . '</span>';
		}

		$placeholders['info_text'] = $formatter->get_infotext( $myproduct );

		$placeholders['predicate_id']          = $formatter->get_predicate_id( $myproduct );
		$placeholders['predicate_text']        = $formatter->get_predicate_text( $myproduct );
		$placeholders['predicate_color']       = $formatter->get_predicate_color( ( $myproduct ) );
		$placeholders['predicate_borderstyle'] = $formatter->get_predicate_borderstyle( ( $myproduct ) );

		$placeholders['testresult'] = $formatter->get_testresult( $myproduct );

		$placeholders['pro']    = $formatter->get_proslist( $myproduct );
		$placeholders['contra'] = $formatter->get_contralist( $myproduct );

		$placeholders['post_list'] = $formatter->get_postlist( $myproduct );

		$placeholders['features_text']    = $formatter->get_featuretext( $myproduct );
		$placeholders['description_text'] = $formatter->get_descriptiontext( $myproduct );

		$placeholders['priceinfo_text'] = $formatter->get_priceinfotext();
		$placeholders['cssclass']       = $cssElementClass;
		$placeholders['content']        = $content;


		if ( ATKPSettings::$show_moreoffers ) {
			$offerstemplate_temp = '';
			if ( $templateid != '' && is_numeric( $templateid ) ) {
				$offerstemplate_temp = ATKPTools::get_post_setting( $templateid, ATKP_TEMPLATE_POSTTYPE . '_moreoffers_template' );
			} else {
				$offerstemplate_temp = atkp_options::$loader->get_moreoffers_template();
			}
			if ( $offerstemplate_temp != '' ) {
				$offerstemplate = $offerstemplate_temp;
			}

			$this->disable_custom_styles = true;
			$moreoffers                  = $this->createOutput( array( $myproduct ), '', $offerstemplate == '' || $offerstemplate == null ? 'moreoffers' : $offerstemplate, 'atkp-moreoffersinfo', '', '', '', 0, 2 );

			$placeholders['moreoffers'] = $moreoffers;
		} else {
			$placeholders['moreoffers'] = '';
		}

		foreach ( $myproduct->displayfields as $key => $displayfield ) {

			$placeholders[ $key ] = $formatter->get_displayfield( $myproduct, $key );
		}

		$placeholders['total_rating'] = $formatter->get_wp_review_rating( $myproduct );

		$placeholders['refresh_date'] = $formatter->get_refreshdate( $myproduct );
		$placeholders['refresh_time'] = $formatter->get_refreshtime( $myproduct );

		$placeholders['disclaimer'] = $formatter->get_disclaimer( $myproduct );

		$placeholders = apply_filters( 'atkp_modify_placeholders', $placeholders );

		return $placeholders;
	}

	public function getPlaceholders( $fieldtype = '' ) {

		$placeholders = $this->createPlaceholderArray( new atkp_product(), 1, '', '', false );

		$newfields  = array();
		$taxonomies = array();

		$newfields = atkp_udfield::load_fields();

		$taxonomies = atkp_udtaxonomy::load_taxonomies();

		$myplaceholders = array();

		$filterplaceholders = array();

		foreach ( array_keys( $placeholders ) as $placeholder ) {

			switch ( $placeholder ) {
				case 'bestseller_number':
				case 'bestseller_text':
				case 'cartlink':
				case 'content':
				case 'cssclass':
				case 'hidelistlink':
				case 'info_text':
				case 'link':
				case 'linktext':
				case 'mark':
				case 'markrating':
				case 'productid':
				case 'productlink':
				case 'reviewslink':
				case 'listurl':
				case 'listlink':
				case 'listlinktext':
				case 'reviewcount2':
				case 'priceinfo_text':
				case 'shopid':
				case 'listid':
				case 'templateid':
				case 'moreoffers':
				case 'shoplogourl':
				case 'smallshoplogourl':
				case 'listtitle':
				case 'detailtext':
				case 'detailvisibility':
				case 'detaillink':
				case 'linkmark':
				case 'titlelinkmark':
				case 'titlelink':
				case 'predicate_id':
				case 'predicate_color':
				case 'predicate_borderstyle':
					break;
				case 'refresh_date':
					$myplaceholders[ $placeholder ] = __( 'Update date', ATKP_PLUGIN_PREFIX );
					break;
				case 'refresh_time':
					$myplaceholders[ $placeholder ] = __( 'Update time', ATKP_PLUGIN_PREFIX );
					break;
				case 'shipping':
					$myplaceholders[ $placeholder ] = __( 'Shipping', ATKP_PLUGIN_PREFIX );
					break;
				case 'shoptitle':
					$myplaceholders[ $placeholder ] = __( 'Shop title', ATKP_PLUGIN_PREFIX );
					break;
				case 'smallshoplogo':
					$myplaceholders[ $placeholder ] = __( 'Shop logo (small)', ATKP_PLUGIN_PREFIX );
					break;
				case 'shoplogo':
					$myplaceholders[ $placeholder ] = __( 'Shop logo', ATKP_PLUGIN_PREFIX );
					break;
				case 'title':
					$myplaceholders[ $placeholder ] = __( 'Title', ATKP_PLUGIN_PREFIX );
					break;
				case 'short_title':
					$myplaceholders[ $placeholder ] = __( 'Title (short)', ATKP_PLUGIN_PREFIX );
					break;
				case 'asin':
					$myplaceholders[ $placeholder ] = __( 'ASIN', ATKP_PLUGIN_PREFIX );
					break;
				case 'isbn':
					$myplaceholders[ $placeholder ] = __( 'ISBN', ATKP_PLUGIN_PREFIX );
					break;
				case 'ean':
					$myplaceholders[ $placeholder ] = __( 'EAN', ATKP_PLUGIN_PREFIX );
					break;
				case 'brand':
					$myplaceholders[ $placeholder ] = __( 'Brand', ATKP_PLUGIN_PREFIX );
					break;
				case 'productgroup':
					$myplaceholders[ $placeholder ] = __( 'Product group', ATKP_PLUGIN_PREFIX );
					break;
				case 'availability':
					$myplaceholders[ $placeholder ] = __( 'Availability', ATKP_PLUGIN_PREFIX );
					break;
				case 'smallimageurl':
					$myplaceholders[ $placeholder ] = __( 'Image small URL', ATKP_PLUGIN_PREFIX );
					break;
				case 'mediumimageurl':
					$myplaceholders[ $placeholder ] = __( 'Image medium URL', ATKP_PLUGIN_PREFIX );
					break;
				case 'largeimageurl':
					$myplaceholders[ $placeholder ] = __( 'Image large URL', ATKP_PLUGIN_PREFIX );
					break;
				case 'smallimage':
					$myplaceholders[ $placeholder ] = __( 'Image small', ATKP_PLUGIN_PREFIX );
					break;
				case 'mediumimage':
					$myplaceholders[ $placeholder ] = __( 'Image medium', ATKP_PLUGIN_PREFIX );
					break;
				case 'largeimage':
					$myplaceholders[ $placeholder ] = __( 'Image large', ATKP_PLUGIN_PREFIX );
					break;

				case 'thumbimages_1':
				case 'thumbimages_2':
				case 'thumbimages_3':
				case 'thumbimages_4':
				case 'thumbimages_5':
				case 'thumbimages_6':
					$splitted                       = explode( '_', $placeholder );
					$myplaceholders[ $placeholder ] = sprintf( __( 'Image small %s', ATKP_PLUGIN_PREFIX ), $splitted[1] );
					break;
				case 'mediumimages_1':
				case 'mediumimages_2':
				case 'mediumimages_3':
				case 'mediumimages_4':
				case 'mediumimages_5':
				case 'mediumimages_6':
					$splitted                       = explode( '_', $placeholder );
					$myplaceholders[ $placeholder ] = sprintf( __( 'Image medium %s', ATKP_PLUGIN_PREFIX ), $splitted[1] );
					break;
				case 'images_1':
				case 'images_2':
				case 'images_3':
				case 'images_4':
				case 'images_5':
				case 'images_6':
					$splitted                       = explode( '_', $placeholder );
					$myplaceholders[ $placeholder ] = sprintf( __( 'Image large %s', ATKP_PLUGIN_PREFIX ), $splitted[1] );
					break;
				case 'by_text':
					$myplaceholders[ $placeholder ] = __( '"by"-Text', ATKP_PLUGIN_PREFIX );
					break;
				case 'producturl':
					$myplaceholders[ $placeholder ] = __( 'Product page URL', ATKP_PLUGIN_PREFIX );
					break;
				case 'customerreviewsurl':
					$myplaceholders[ $placeholder ] = __( 'Customer Reviews URL', ATKP_PLUGIN_PREFIX );
					break;
				case 'reviewsurl':
					$myplaceholders[ $placeholder ] = __( 'Review URL', ATKP_PLUGIN_PREFIX );
					break;
				case 'rating':
					$myplaceholders[ $placeholder ] = __( 'Rating', ATKP_PLUGIN_PREFIX );
					break;
				case 'star_rating':
					$myplaceholders[ $placeholder ] = __( 'Star Rating', ATKP_PLUGIN_PREFIX );
					break;
				case 'reviewcount':
					$myplaceholders[ $placeholder ] = __( 'Amount of reviews', ATKP_PLUGIN_PREFIX );
					break;
				case 'prime_icon':
					$myplaceholders[ $placeholder ] = __( 'Is prime', ATKP_PLUGIN_PREFIX );
					break;
				case 'save_percentage':
					$myplaceholders[ $placeholder ] = __( 'Percentage saved', ATKP_PLUGIN_PREFIX );
					break;
				case 'save_percentage_':
					$myplaceholders[ $placeholder ] = __( '(Percentage saved)', ATKP_PLUGIN_PREFIX );
					break;
				case 'save_text':
					$myplaceholders[ $placeholder ] = __( 'You Save', ATKP_PLUGIN_PREFIX );
					break;
				case 'save_amount':
					$myplaceholders[ $placeholder ] = __( 'Amount saved', ATKP_PLUGIN_PREFIX );
					break;
				case 'listprice':
					$myplaceholders[ $placeholder ] = __( 'List price', ATKP_PLUGIN_PREFIX );
					break;
				case 'listprice_text':
					$myplaceholders[ $placeholder ] = __( 'List price (Text)', ATKP_PLUGIN_PREFIX );
					break;
				case 'price':
					$myplaceholders[ $placeholder ] = __( 'Price', ATKP_PLUGIN_PREFIX );
					break;
				case 'price_text':
					$myplaceholders[ $placeholder ] = __( 'Price (Text)', ATKP_PLUGIN_PREFIX );
					break;
				case 'features_text':
					$myplaceholders[ $placeholder ] = __( 'Features', ATKP_PLUGIN_PREFIX );
					break;
				case 'description_text':
					$myplaceholders[ $placeholder ] = __( 'Description', ATKP_PLUGIN_PREFIX );
					break;
				case 'shopcustomfield1':
					$myplaceholders[ $placeholder ] = __( 'Shop: Custom field 1', ATKP_PLUGIN_PREFIX );
					break;
				case 'shopcustomfield2':
					$myplaceholders[ $placeholder ] = __( 'Shop: Custom field 2', ATKP_PLUGIN_PREFIX );
					break;
				case 'shopcustomfield3':
					$myplaceholders[ $placeholder ] = __( 'Shop: Custom field 3', ATKP_PLUGIN_PREFIX );
					break;
				case 'detailurl':
					$myplaceholders[ $placeholder ] = __( 'Internal product page URL', ATKP_PLUGIN_PREFIX );
					break;
				case 'testresult':
					$myplaceholders[ $placeholder ] = __( 'Test Badget', ATKP_PLUGIN_PREFIX );
					break;
				case 'pro':
					$myplaceholders[ $placeholder ] = __( 'Pro arguments', ATKP_PLUGIN_PREFIX );
					break;
				case 'contra':
					$myplaceholders[ $placeholder ] = __( 'Contra arguments', ATKP_PLUGIN_PREFIX );
					break;
				case 'post_list':
					$myplaceholders[ $placeholder ] = __( 'List of posts (main product)', ATKP_PLUGIN_PREFIX );
					break;
				case 'total_rating':
					$myplaceholders[ $placeholder ] = __( 'Total rating', ATKP_PLUGIN_PREFIX );
					break;
				default:
					$myplaceholders[ $placeholder ] = $placeholder;

					if ( $newfields != null ) {
						foreach ( $newfields as $newfield ) {
							if ( 'customfield_' . $newfield->name == $placeholder ) {
								$myplaceholders[ $placeholder ] = $newfield->caption . ' (' . $placeholder . ')';

								if ( $fieldtype != '' && $fieldtype == 'html' && $newfield->type == 5 ) {
									$filterplaceholders[ $placeholder ] = $newfield->caption;
								}

								break;
							}
						}
					}

					if ( $taxonomies != null ) {
						foreach ( $taxonomies as $taxonomy ) {
							$fieldname = '';

							if ( ! $taxonomy->issystemfield ) {
								$fieldname = 'customtaxonomy_' . $taxonomy->name;
							} else {
								$fieldname = $taxonomy->name;
							}

							if ( $fieldname == $placeholder ) {
								$myplaceholders[ $placeholder ] = $taxonomy->caption == '' ? $taxonomy->name : $taxonomy->caption . ' (' . $taxonomy->name . ')';
							}
						}
					}
					break;
			}
		}

		if ( $fieldtype != '' ) {
			return $filterplaceholders;
		} else {
			return $myplaceholders;
		}
	}

	public function createLiveOutput( $shopids, $templatelive, $template, $filterstr, $elementcss, $containercss, $limit ) {

		$views = atkp_template::get_blade_directories();

		if ( $template == '' ) {
			$template = 'default_live';
		}

		$renderedOutput = apply_filters( 'atkp_livetemplate_render_output', false, $template, $filterstr, $elementcss, $containercss, $limit );

		if ( $renderedOutput ) {
			$resultValue = $renderedOutput;
		} else {
			$mytemplate = apply_filters( 'atkp_livetemplate_get_blade', '', $template );

			if ( $mytemplate == '' ) {
				if ( is_numeric( $template ) ) {

					$templatefound = get_post( $template );
					if ( isset( $templatefound ) && $templatefound != null && ( $templatefound->post_status == 'publish' || $templatefound->post_status == 'draft' ) ) {
						$mytemplate = html_entity_decode( ATKPTools::get_post_setting( $templatefound->ID, ATKP_TEMPLATE_POSTTYPE . '_body' ) );
					} else {
						return ATKPSettings::$hideerrormessages ? '' : ( 'template not found: ' . $template );
					}

				} else {
					$templatepath = '';

					if ( file_exists( ATKP_SEARCH_PLUGIN_DIR . '/templates/' . $template . '.blade.php' ) ) {
						$templatepath = ATKP_SEARCH_PLUGIN_DIR . '/templates/' . $template . '.blade.php';
					}

					if ( file_exists( $templatepath ) ) {
						$mytemplate = file_get_contents( $templatepath );
					} else {
						return ATKPSettings::$hideerrormessages ? '' : ( 'livetemplate not found: ' . $template );
					}
				}
			}

			$shops = atkp_shop::get_list();

			$filteredlist = array();

			foreach ( $shops as $shop ) {

				if ( $shop->type != atkp_shop_type::SUB_SHOPS ) {
					$filteredlist[] = $shop;
				}

				foreach ( $shop->children as $c ) {
					$filteredlist[] = $c;
				}
			}


			$shopsfiltered = array();
			foreach ( $filteredlist as $shop ) {
				if ( count( $shopids ) > 0 && ! in_array( $shop->id, $shopids ) ) {
					continue;
				}
				$shopsfiltered[] = $shop;
			}


			$init = esc_attr( ! ATKPTools::exists_get_parameter( 'search' ) ? "1" : "0" );


			$parameters = new atkp_livetemplate_parameters( '', $template, $filterstr, $elementcss, $containercss, $limit, ATKPTools::get_endpointurl(), $templatelive, $init );

			$resultValue = $this->run_blade( $mytemplate, $shopsfiltered, $views, $parameters, 'shops' );
		}

		$tempval = do_shortcode( $resultValue );

		return $tempval;
	}

	public function createOutput( $products, $content = '', $template = '', $cssContainerClass = '', $cssElementClass = '', $addtocart = '', $listid = '', $hidedisclaimer = 0, $templatetypedefault = 0, $tracking_id = '', $offerstemplate = '', $imagetemplate = '', $itemsPerPage = 0, $max_num_pages = 0, $found_posts = 0 ) {
		$original_template = $template;

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			$this->ajax_mode = 'disabled';
		}

		if ( ( atkp_options::$loader->get_ajax_loading_enabled() && $this->ajax_mode != 'disabled' ) || $this->ajax_mode == 'enabled' ) {
			$template = 'ajax_load';
		}

		$products = apply_filters( 'atkp_modify_products', $products );

		$views = atkp_template::get_blade_directories();

		$parameters       = new atkp_template_parameters();
		$shortcode_params = $parameters->buildShortcodeArray( $content, $cssContainerClass, $cssElementClass, $addtocart, $hidedisclaimer, $tracking_id, $listid, $template, $offerstemplate, $imagetemplate );
		$parameters->buildTemplateParameters( $template, $shortcode_params );

		$renderedOutput = apply_filters( 'atkp_template_render_template', false, $template, $products, $parameters );
		if ( ! $renderedOutput ) {
			$renderedOutput = apply_filters( 'atkp_template_render_output', false, $template, $products, $parameters->get_css_container_class(), $parameters->get_css_element_class(), $content, $addtocart, $listid, $tracking_id, $offerstemplate, $imagetemplate );
		}

		if ( $renderedOutput ) {
			$resultValue = $renderedOutput;


			$output = new atkp_output();
			if ( ! $this->disable_custom_styles && atkp_options::$loader->get_css_inline() == atkp_css_type::Inline ) {
				$resultValue .= '<style>' . $output->get_css_inline( $parameters ) . '</style>';
			}

			if ( count( $products ) > 0 && $parameters->get_show_disclaimer() ) {

				$formatter = new atkp_formatter( $this, $parameters );

				$resultValue .= '<span class="atkp-disclaimer">' . $formatter->get_disclaimer( $products[0] ) . '</span>';
			}

		} else {
			$mytemplate = $this->get_html_template( $template, $views );

			$output = new atkp_output();
			if ( ! $this->disable_custom_styles && atkp_options::$loader->get_css_inline() == atkp_css_type::Inline ) {
				$mytemplate .= '<style>' . $output->get_css_inline( $parameters ) . '</style>';
			}

			if ( ( atkp_options::$loader->get_ajax_loading_enabled() && $this->ajax_mode != 'disabled' ) || $this->ajax_mode == 'enabled' ) {
				$parameters->templateid = $original_template;

				//json parameters, template, products
				$resultValue = $this->run_blade( $mytemplate, $products, $views, $parameters );

			} else {
				$resultValue = $this->run_blade( $mytemplate, $products, $views, $parameters );

				$resultValue .= $this->generate_pagination( $max_num_pages );
			}
		}

		if ( is_user_logged_in() && current_user_can( 'administrator' ) && get_option( ATKP_PLUGIN_PREFIX . '_showadminsection', 1 ) && $resultValue != '' ) {

			$adminsection = '';
			$adminlinks   = array();

			if ( is_numeric( $template ) ) {
				$url = admin_url( sprintf( 'post.php?post=%d&action=edit', (int) $template ) );

				$adminlinks[] = '<a class="' . esc_attr( 'atkp-admin-button' ) . '" href="' . esc_url( $url ) . '">' . __( '(edit template)', ATKP_PLUGIN_PREFIX ) . '</a>';
			}

			if ( is_numeric( $listid ) && $listid > 0 ) {
				$url = admin_url( sprintf( 'post.php?post=%d&action=edit', (int) $listid ) );

				if ( $adminsection != '' ) {
					$adminsection .= "&#124;";
				}
				$adminlinks[] = '<a class="' . esc_attr( 'atkp-admin-button' ) . '" href="' . esc_url( $url ) . '">' . __( '(edit list)', ATKP_PLUGIN_PREFIX ) . '</a>';

			} else if ( is_array( $products ) && count( $products ) > 0 ) {
				$added = array();

				foreach ( $products as $product ) {
					if ( isset( $added[ intval( $product->listid ) + intval( $product->productid ) ] ) ) {
						continue;
					}


					if ( is_numeric( $product->listid ) && intval( $product->listid ) > 0 ) {
						$url = admin_url( sprintf( 'post.php?post=%d&action=edit', (int) $product->listid ) );

						if ( $adminsection != '' ) {
							$adminsection .= "&#124;";
						}

						$adminlinks[] = '<a class="' . esc_attr( 'atkp-admin-button' ) . '" href="' . esc_url( $url ) . '">' . __( '(edit list)', ATKP_PLUGIN_PREFIX ) . '</a>';
					} else if ( is_numeric( $product->productid ) && intval( $product->productid ) > 0 ) {
						$url = admin_url( sprintf( 'post.php?post=%d&action=edit', (int) $product->productid ) );

						if ( $adminsection != '' ) {
							$adminsection .= "&#124;";
						}
						$short_title = $product->title;
						if ( strlen( $short_title ) > 25 ) {
							$short_title = substr( $short_title, 0, 25 );
						}

						$adminlinks[] = '<a class="' . esc_attr( 'atkp-admin-button' ) . '" href="' . esc_url( $url ) . '">' . sprintf( __( '(edit %s)', ATKP_PLUGIN_PREFIX ), $short_title ) . '</a>';
					}

					$added[ $product->listid ] = $product->listid;
				}

			}

			$adminlinks = apply_filters( 'atkp_modify_adminlinks', $adminlinks, $template, $listid, $products );

			$resultValue .= apply_filters( 'atkp_modify_adminoutput', '<div class="atkp-admin-actions">' . implode( ' ', $adminlinks ) . '</div>' );
		}


		return do_shortcode( $resultValue );
	}

	public function get_html_template( $template, $views ) {

		$mytemplate = apply_filters( 'atkp_template_get_blade', '', $template );

		if ( $mytemplate == '' ) {
			if ( is_numeric( $template ) ) {

				$templatefound = get_post( $template );
				if ( isset( $templatefound ) && $templatefound != null && ( $templatefound->post_status == 'publish' || $templatefound->post_status == 'draft' ) ) {
					$mytemplate = html_entity_decode( ATKPTools::get_post_setting( $templatefound->ID, ATKP_TEMPLATE_POSTTYPE . '_body' ) );

					$css = html_entity_decode( ATKPTools::get_post_setting( $templatefound->ID, ATKP_TEMPLATE_POSTTYPE . '_css' ) );
					if ( ! $this->disable_custom_styles && atkp_options::$loader->get_css_inline() == atkp_css_type::Inline && $css != '' ) {
						$mytemplate .= '<style>' . $css . '</style>';
					}
				} else {
					return ATKPSettings::$hideerrormessages ? '' : ( 'template not found: ' . $template );
				}

			} else {
				$templatepath = '';
				foreach ( $views as $view ) {
					if ( file_exists( $view . '/' . $template . '.blade.php' ) ) {
						$templatepath = $view . '/' . $template . '.blade.php';
						break;
					}
				}

				if ( file_exists( $templatepath ) ) {
					$mytemplate = file_get_contents( $templatepath );
				} else {
					return ATKPSettings::$hideerrormessages ? '' : ( 'template not found: ' . $template );
				}
			}
		}

		return $mytemplate;
	}

	/**
	 * @param array $products
	 * @param atkp_template_parameters $parameters
	 *
	 * @return mixed|string|void
	 * @throws Exception
	 */
	public function createAjaxOutput( $products, $parameters ) {

		$views = atkp_template::get_blade_directories();

		$renderedOutput = apply_filters( 'atkp_template_render_template', false, $parameters->templateid, $products, $parameters );
		if ( ! $renderedOutput ) {
			$renderedOutput = apply_filters( 'atkp_template_render_output', false, $parameters->templateid, $products, $parameters->get_css_container_class(), $parameters->get_css_element_class(), $parameters->content, $parameters->addtocart, $parameters->listid, $parameters->trackingid, $parameters->offerstemplate, $parameters->imagetemplate );
		}

		if ( $renderedOutput ) {
			$resultValue = $renderedOutput;

			$disabledisclaimer = is_numeric( $parameters->templateid ) ? ATKPTools::get_post_setting( $parameters->templateid, ATKP_TEMPLATE_POSTTYPE . '_disabledisclaimer' ) : 0;
			if ( $disabledisclaimer ) {
				$hidedisclaimer = true;
			}

			if ( count( $products ) > 0 && atkp_options::$loader->get_show_disclaimer() && ! $hidedisclaimer ) {

				$formatter = new atkp_formatter( $this, null );

				$resultValue .= '<span class="atkp-disclaimer">' . $formatter->get_disclaimer( $products[0] ) . '</span>';
			}

		} else {
			$mytemplate = $this->get_html_template( $parameters->templateid, $views );

			$resultValue = $this->run_blade( $mytemplate, $products, $views, $parameters );
		}

		$output = new atkp_output();
		if ( atkp_options::$loader->get_css_inline() == atkp_css_type::Inline || $this->preview_generation ) {
			$resultValue .= '<style>' . $output->get_css_inline( $parameters ) . '</style>';
		}

		return $resultValue;
	}

	private function generate_pagination( $max_num_pages ) {
		if ( $max_num_pages > 1 ) {
			//add navigation links
			$paging = '';
			$page   = ATKPTools::get_get_parameter( 'tpage', 'int' );

			if ( $page <= 1 ) {
				$page = 1;
			}

			if ( ! ATKPSettings::$disablestyles ) {
				$paging .= '<div class="atkp-navigation">';
			}

			$nextpagelink = get_home_url() . remove_query_arg( 'tpage', $_SERVER['REQUEST_URI'] );

			$addpaging = false;


			if ( $page > 1 ) {
				$paging    .= '<a class="atkp-prevpage-btn atkp-infobutton" href="' . $nextpagelink . ( strpos( $nextpagelink, '?' ) > - 1 ? '&' : '?' ) . 'tpage=' . ( $page - 1 ) . '">' . __( 'Previous page', ATKP_PLUGIN_PREFIX ) . '</a>';
				$addpaging = true;
			}

			//$max_num_pages = 0;
			//$found_posts = 0;

			if ( $page < $max_num_pages ) {
				if ( $addpaging ) {
					$paging .= '&nbsp;';
				}
				$paging    .= '<a class="atkp-nextpage-btn atkp-infobutton" href="' . $nextpagelink . ( strpos( $nextpagelink, '?' ) > - 1 ? '&' : '?' ) . 'tpage=' . ( $page + 1 ) . '">' . __( 'Next page', ATKP_PLUGIN_PREFIX ) . '</a>';
				$addpaging = true;
			}


			if ( ! ATKPSettings::$disablestyles ) {
				$paging .= '</div>';
			}

			if ( $addpaging ) {
				return $paging;
			}

		}

		return '';
	}

	/**
	 * Generates the html output from the blade template
	 *
	 * @param string $bladecontent
	 * @param array $products
	 * @param array $views
	 * @param atkp_template_parameters|object $parameters
	 * @param string $name
	 *
	 * @return string
	 * @throws Exception
	 */
	private function run_blade( $bladecontent, $products, $views, $parameters, $name = 'products' ) {

		require_once ATKP_PLUGIN_DIR . "/lib/bladeone/BladeOne.php"; // you should change it and indicates the correct route.

		// $cache = ATKP_PLUGIN_DIR . '/templates/blade/cache'; // it uses the folder /cache to compile the result.
		//$cache = ATKPTools::get_uploaddir();
		$cache = ATKPTools::get_uploaddir() . '/blade';

		$blade = new BladeOne( $views, $cache, BladeOne::MODE_AUTO );

		$current_user = wp_get_current_user();

		if ( isset( $current_user ) && $current_user ) {
			$role = isset( $current_user->roles ) && $current_user->roles && is_array( $current_user->roles ) ? ( array ) $current_user->roles : null;

			$blade->setAuth( $current_user->display_name, $role != null ? $role[0] : '' );
		} else {
			$blade->setAuth( 'guest' );
		}

		$idx = 1;
		foreach ( $products as $product ) {
			$product->item_idx = $idx ++;
		}

		if ( is_array( $products ) && count( $products ) > 0 ) {
			if ( $name == 'products' && isset( $products[0] ) ) {
				$shop = $products[0]->shop;
			} else if ( $name == 'shops' ) {
				$shop = $products[0];
			} else {
				$shop = null;
			}
		}

		$formatter  = new atkp_formatter( $this, $parameters );
		$translator = new atkp_translator( $this, $parameters );

		$bladearray = array(
			'products'   => array(),
			'shops'      => array(),
			'formatter'  => $formatter,
			'shop'       => $shop,
			'parameters' => $parameters,
			'translator' => $translator
		);

		$bladearray[ $name ] = ( $products == null ? array() : $products );

		$bladearray = apply_filters( 'atkp_modify_bladedata', $bladearray );

		//extract style tags
		/*
				preg_match_all( "/<style>(.*?)<\/style>/is", $bladecontent, $matches );
				preg_match_all( "/<script>(.*?)<\/script>/is", $bladecontent, $matches2 );

				$html = str_replace( $matches[0], '', $bladecontent );
				$html = str_replace( $matches2[0], '', $html );
				$css  = implode( "\n", $matches[1] );
				$script  = implode( "\n", $matches2[1] );

				if($css != '')
					do_action('atkp_add_inline_css', $css, $parameters->templateid);
				if($script != '')
					do_action('atkp_add_inline_script', $script, $parameters->templateid);
		*/

		return $blade->runString( $bladecontent, $bladearray );
	}

	public function replace_placeholders( $result, $placeholders ) {

		preg_match_all( '/\%([a-zA-Z0-9_-]+)\%/', $result, $matches );

		foreach ( $matches[1] as $placeholder ) {
			if ( $placeholder == 'mobiletable' ) {
				continue;
			}

			$value = '';
			if ( isset( $placeholders[ $placeholder ] ) ) {
				$value = $placeholders[ $placeholder ];
			}

			$result = str_replace( '%' . $placeholder . '%', $value, $result );
		}

		return $result;
	}




}


?>