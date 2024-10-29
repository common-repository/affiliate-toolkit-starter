<?php
/**
 * Created by PhpStorm.
 * User: Christof
 * Date: 18.12.2018
 * Time: 22:05
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class atkp_formatter {

	private $tempvalue = array();
	public function set_temp_value($name, $value) {
		$this->tempvalue[$name] = $value;

		return null;
	}

	public function get_temp_value($name) {

		if(isset($this->tempvalue[$name])) {
			return $this->tempvalue[$name];
		}else
			return null;
	}

	/** @var atkp_template_helper $templatehelper */
	private $templatehelper;
	/** @var atkp_template_parameters $parameters */
	private $parameters;

	public function __construct( $templatehelper, $parameters ) {
		$this->templatehelper = $templatehelper;
		$this->parameters     = $parameters;

		//produkt alle properties laden
		//alle benutzerdefinierten felder laden
		//cache berücksichtigen?
		//shop objekt
		//angebote array einfügen
		//bildergalerie array einfügen
		//acf felder einfügen

		//formatter hält die funktionen wie shorttitel etc
	}

	public function get_mark() {
		if ( atkp_options::$loader->get_mark_links() == 1 ) {
			return atkp_options::$loader->get_affiliatechar();
		} else {
			return '';
		}
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_listtitle( $myproduct ) {
		if ( $myproduct->listid == '' ) {
			return '';
		}

		$list = get_post( $myproduct->listid );
		if ( isset( $list ) ) {
			return $list->post_title;
		} else {
			return '';
		}
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_postlist( $myproduct ) {

		if ( $myproduct->postids == '' ) {
			return '';
		}

		$post_list = '<ul class="atkp_postlist">';
		if ( is_array( $myproduct->postids ) ) {
			foreach ( $myproduct->postids as $p ) {
				$title = get_the_title( $p );
				if ( ! isset( $title ) || $title == '' ) {
					$title = __( 'Post', ATKP_PLUGIN_PREFIX );
				}

				$post_list .= sprintf( __( '<li><a href="%s">%s</a></li>', ATKP_PLUGIN_PREFIX ), get_permalink( $p ), $title );
			}
		} else {
			$title = get_the_title( $myproduct->postids );
			if ( ! isset( $title ) || $title == '' ) {
				$title = __( 'Post', ATKP_PLUGIN_PREFIX );
			}

			$post_list .= sprintf( __( '<li><a href="%s">%s</a></li>', ATKP_PLUGIN_PREFIX ), get_permalink( $myproduct->postids ), $title );
		}
		$post_list .= '</ul>';

		return $post_list;
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_predicate_id( $myproduct ) {
		if ( $myproduct->predicate != '' && $myproduct->predicate != 0 ) {
			return $myproduct->predicate;
		}

		return '';
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_predicate_text( $myproduct ) {
		if ( $myproduct->predicate != '' && $myproduct->predicate != 0 ) {
			return get_option( ATKP_PLUGIN_PREFIX . '_predicate' . $myproduct->predicate . '_text' );
		}

		return '';
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_predicate_color( $myproduct ) {
		if ( $myproduct->predicate != '' && $myproduct->predicate != 0 ) {
			return get_option( ATKP_PLUGIN_PREFIX . '_predicate' . $myproduct->predicate . '_color' );
		}

		return '';
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_predicate_borderstyle( $myproduct ) {
		if ( $myproduct->predicate != '' && $myproduct->predicate != 0 ) {
			return 'border-color:' . $this->get_predicate_color( $myproduct );
		}

		return '';
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_proslist( $myproduct, $content_mask = '%s' ) {
		if ( $myproduct->pro == '' ) {
			return '';
		}

		$str = '<ul class="atkp-pro">';
		foreach ( explode( "\n", $myproduct->pro ) as $mypro ) {
			$str = $str . '<li>' . sprintf( $content_mask, $mypro ) . '</li>';
		}
		$str = $str . '</ul>';

		return $str;
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_contralist( $myproduct, $content_mask = '%s' ) {
		if ( $myproduct->contra == '' ) {
			return '';
		}

		$str = '<ul class="atkp-contra">';
		foreach ( explode( "\n", $myproduct->contra ) as $mycontra ) {
			$str = $str . '<li>' . sprintf( $content_mask, $mycontra ) . '</li>';
		}
		$str = $str . '</ul>';

		return $str;
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_testresult( $myproduct ) {
		if ( $myproduct->testresult == '' || $myproduct->testresult == 0 ) {
			return '';
		}

		$testresult = esc_attr( get_option( ATKP_PLUGIN_PREFIX . '_test_score' . $myproduct->testresult . '_text' ) );
		$testrating = $myproduct->testrating;
		$testdate   = $myproduct->testdate;

		return $this->get_testresult_raw( $testresult, $testrating, $testdate );
	}

	public function get_testresult_raw( $testresult, $testrating, $testdate ) {
		$testcolor   = get_option( ATKP_PLUGIN_PREFIX . '_review_color', '#9f9f9f' );
		$testcaption = esc_attr( get_option( ATKP_PLUGIN_PREFIX . '_review_text', __( 'Review', ATKP_PLUGIN_PREFIX ) ) );

		return '<div class="atkp-testbadge" style="border-color:' . $testcolor . '"><span class="atkp-testtitle" style="background-color:' . $testcolor . '">' . $testcaption . '</span><span class="atkp-testnote" style="color:' . $testcolor . '">' . $testrating . '</span><span class="atkp-testtext">' . $testresult . '</span><span class="atkp-testdate">' . $testdate . '</span></div>';

	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_featuretext( $myproduct ) {
		return wpautop( $myproduct->features );
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_descriptiontext( $myproduct ) {
		return wpautop( $myproduct->description );
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_featuretext_short( $myproduct ) {
		if ( $myproduct->outputashtml || atkp_options::$loader->get_outputashtml() ) {
			return wpautop( $myproduct->features );
		} else {
			if ( $myproduct->features == '' ) {
				$featureRows = array();
			} else {
				$featureRows = explode( '<li>', $myproduct->features );
				$featureRows = array_map( 'strip_tags', $featureRows );
			}


			$featureclean = '';
			$cnt          = 0;
			foreach ( $featureRows as $featureRow ) {
				if ( trim( $featureRow ) == '' ) {
					continue;
				}

				$featureclean .= '<li>' . $featureRow . '</li>';
				$cnt ++;

				if ( $this->parameters != null && $this->parameters->get_feature_count() > 0 ) {
					if ( $cnt >= $this->parameters->get_feature_count() ) {
						break;
					}
				}
			}

			if ( count( $featureRows ) <= 1 ) {
				$featureclean = strip_tags( $myproduct->features );
			}

			if ( $featureclean != '' ) {
				$featureclean = '<ul>' . $featureclean . '</ul>';
			}

			return wpautop( $featureclean );
		}
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_descriptiontext_short( $myproduct ) {
		if ( $myproduct->outputashtml || atkp_options::$loader->get_outputashtml() ) {
			return wpautop( $myproduct->description );
		} else {

			$descclean = strip_tags( $myproduct->description );

			if ( $this->parameters != null && $this->parameters->get_description_length() > 0 ) {
				$descclean = ( strlen( $descclean ) > $this->parameters->get_description_length() ) ? substr( $descclean, 0, $this->parameters->get_description_length() ) . '...' : $descclean;
			}

			return wpautop( $descclean );
		}
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_infotext( $myproduct ) {
		$info_text = '';

		switch ( $this->parameters->get_box_description_content() ) {
			default:
			case '1':

				if ( $myproduct->features == '' ) {
					$info_text = $this->get_descriptiontext_short( $myproduct );
				} else {
					$info_text = $this->get_featuretext_short( $myproduct );
				}

				break;
			case '2':
				$info_text = $this->get_featuretext_short( $myproduct );
				break;
			case '3':
				$info_text = $this->get_descriptiontext_short( $myproduct );
				break;

		}

		return $info_text;
	}

	public function get_listurl() {
		if ( $this->parameters->listid == '' ) {
			return '';
		}

		$listurl = ATKPTools::get_post_setting( $this->parameters->listid, ATKP_LIST_POSTTYPE . '_listurl' );

		return $listurl;
	}

	private static function get_link_rel() {
		$disablesponsored = atkp_options::$loader->get_disable_sponsored_attribute();

		if($disablesponsored)
			return 'nofollow noopener';
		else
			return 'sponsored nofollow noopener';
	}

	public function get_listlink() {
		if ( ATKPSettings::$open_window ) {
			$target = 'target="_blank"';
		} else {
			$target = '';
		}

		return 'href="' . $this->get_listurl() . '" rel="'.self::get_link_rel().'" ' . $target . ' title="' . __( 'Show me more products', ATKP_PLUGIN_PREFIX ) . '"';
	}

	public function get_link_mark() {
		$linkmark = '';

		if ( $this->parameters->get_mark_links() == 1 ) {
			$linkmark = $this->parameters->get_affiliatechar();
		}

		return $linkmark;
	}

	/**
	 * @param atkp_product|null $myproduct
	 *
	 * @return mixed|string
	 */
	public function get_button_mark( $myproduct ) {
		$buttontype = $this->get_button_type( $myproduct );

		$linkmark = $this->get_link_mark();

		switch ( $buttontype ) {
			case 'product':
			case 'woocommerce':
				$linkmark = '';
				break;
		}

		return $linkmark;
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return atkp_product
	 */
	public function get_datasource_product( $myproduct ) {
		return $myproduct;
	}


	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_button_type( $myproduct ) {
		$buttontype = atkp_options::$loader->get_add_to_cart();

		if ( $this->parameters->addtocart != '' && $this->parameters->addtocart != 'notset' ) {
			$buttontype = $this->parameters->addtocart;
		}

		//fallback falls cartlink leer ist
		if ( $buttontype == 'addtocart' && $this->get_addtocarturl( $myproduct ) == '' ) {
			$buttontype = 'link';
		}

		//falls die produktseiten nicht aktiv sind, fallback auf produktlink
		if ( $buttontype == 'product' && $this->get_detailurl( $myproduct ) == '' ) {
			$buttontype = 'link';
		}

		if ( $buttontype == 'woocommerce' && $this->get_woocommerceurl( $myproduct ) == '' ) {
			$buttontype = 'link';
		}

		if($buttontype == 'linkfallback') {
			if($myproduct->salepricefloat == 0)
				$buttontype = 'minofferlink';
			else
				$buttontype = 'link';
		}

		if($buttontype == 'minofferlink' || $buttontype == 'maxofferlink' ) {
			$offers = $this->get_offers( $myproduct, true );

			if ( count( $offers ) == 0 ) {
				$buttontype = 'link';
			}
		}

		return $buttontype;
	}

	/**
	 * @param null|atkp_product $myproduct
	 * @param null|atkp_shop $shop
	 *
	 * @return string
	 */
	public function get_button_text( $myproduct, $shop = null ) {
		$buttontype = $this->get_button_type( $myproduct );
		$linktext   = '';

		$myproduct = $this->get_datasource_product( $myproduct );

		if ( $shop != null ) {
			$myproduct->shop   = $shop;
			$myproduct->shopid = $shop->shopid;
		}

		switch ( $buttontype ) {
			case '1':
			case 'addtocart':

				if ( $myproduct->shop == null ) {
					$linktext = __( 'Add to cart', ATKP_PLUGIN_PREFIX );
				} else {

					$linktext = $myproduct->shop->get_addtocart() != '' ? $myproduct->shop->get_addtocart() : __( 'Add to Amazon Cart', ATKP_PLUGIN_PREFIX );
				}

				break;
			default:
			case 'link':
				if ( $myproduct->shop == null ) {
					$linktext = __( 'Buy now', ATKP_PLUGIN_PREFIX );
				} else {

					$linktext = $myproduct->shop->get_buyat() != '' ? $myproduct->shop->get_buyat() : __( 'Buy now at Amazon', ATKP_PLUGIN_PREFIX );
				}

				break;
			case 'product':
				$linktext = $this->get_detailtext();
				break;
			case 'woocommerce':
				$linktext = $this->get_woocommercetitle( $myproduct );
				break;
			case 'minofferlink':
			case 'maxofferlink':
				if ( $buttontype == 'maxofferlink' )
					$minoffer = $this->get_maxoffer( $myproduct, true );
				else
					$minoffer = $this->get_minoffer( $myproduct, true);

				if ( $minoffer->shop == null ) {
					$linktext = __( 'Buy now', ATKP_PLUGIN_PREFIX );
				} else {

					$linktext = $minoffer->shop->get_buyat() != '' ? $minoffer->shop->get_buyat() : __( 'Buy now at Amazon', ATKP_PLUGIN_PREFIX );
				}

				break;

		}

		return $linktext;
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function is_button_link_available( $myproduct ) {
		$link = $this->get_button_link( $myproduct );

		return $link != '';
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_button_link( $myproduct ) {
		$buttontype = $this->get_button_type( $myproduct );
		$link       = '';

		switch ( $buttontype ) {
			case '1':
			case 'addtocart':
				$link = $this->get_cartlink( $myproduct );
				break;
			default:
			case 'link':
				$link = $this->get_productlink( $myproduct );
				break;
			case 'product':
				$link = $this->get_detaillink( $myproduct );
				break;
			case 'woocommerce':
				$link = $this->get_woocommercelink( $myproduct );
				break;
			case 'minofferlink':
				$minoffer = $this->get_minoffer($myproduct, true);
				$link = $this->get_offer_productlink($minoffer );
				break;
			case 'maxofferlink':
				$minoffer = $this->get_maxoffer( $myproduct, true );
				$link     = $this->get_offer_productlink( $minoffer );
				break;
		}

		return $link;
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_title_type( $myproduct ) {
		$titletype = get_option( ATKP_PLUGIN_PREFIX . '_title_link_type', 'link' );

		if ( $titletype == 'product' && $this->get_detailurl( $myproduct ) == '' ) {
			$titletype = 'link';
		}

		if ( $titletype == 'woocommerce' && $this->get_woocommerceurl( $myproduct ) == '' ) {
			$titletype = 'link';
		}

		return $titletype;
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_title_mark( $myproduct ) {
		$titletype = $this->get_title_type( $myproduct );

		$linkmark = $this->get_link_mark();

		switch ( $titletype ) {
			case 'product':
			case 'woocommerce':
				$linkmark = '';
				break;
		}

		return $linkmark;
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function is_title_link_available( $myproduct ) {
		$link = $this->get_title_link( $myproduct );

		return $link != '';
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_title_link( $myproduct ) {
		$titlelink = $this->get_title_type( $myproduct );
		$link      = '';

		switch ( $titlelink ) {
			default:
			case 'link':
				$link = $this->get_productlink( $myproduct );
				break;
			case 'product':
				$link = $this->get_detaillink( $myproduct );
				break;
			case 'woocommerce':
				$link = $this->get_woocommercelink( $myproduct );
				break;
		}

		return $link;
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_title( $myproduct ) {
		$titletype = $this->get_title_type( $myproduct );
		$linktext  = '';

		switch ( $titletype ) {
			default:
				$linktext = $myproduct->title;
				break;
			case 'woocommerce':
				$linktext = $this->get_woocommercetitle( $myproduct );
				break;

		}

		return $linktext;
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_warehouselogo( $myproduct ) {
		$warehouselogo = '';

		if ( $myproduct->iswarehouse ) {
			$warehouselogo .= '<div class="atkp-warehouse">Warehouse</div>'; //'<img src="' . plugins_url( 'images/amazon-warehouse.gif', ATKP_PLUGIN_FILE ) . '" alt="' . __( 'Warehouse', ATKP_PLUGIN_PREFIX ) . '"/>';
		}

		return $warehouselogo;
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_primelogo( $myproduct ) {
		$primelogo = '';

		if ( $myproduct->isprime && $this->parameters->get_showprimelogo() ) {

			if ( defined( 'ATKP_AMAZON_PLUGIN_FILE' ) ) {
				$primelogo = '<img src="' . plugins_url( 'images/prime_amazon.png', ATKP_AMAZON_PLUGIN_FILE ) . '" alt="' . __( 'Prime', ATKP_PLUGIN_PREFIX ) . '"/>';
			}

			if ( $this->parameters->get_linkprime() ) {
				$amzCountry = ATKPTools::get_post_setting( $this->get_shop_value( $myproduct )->parent_id, ATKP_SHOP_POSTTYPE . '_access_website' );
				$amzTag     = ATKPTools::get_post_setting( $this->get_shop_value( $myproduct )->parent_id, ATKP_SHOP_POSTTYPE . '_access_tracking_id' );

				$primelink = self::redirect_external_url( $this->get_shopid_value( $myproduct ), 'https://www.amazon.' . $amzCountry . '/gp/prime/?primeCampaignId=prime_assoc_ft&tag=' . $amzTag . '&camp=4510&creative=670002&linkCode=ur1&adid=07VBBZ76N7ZKENHMQCDR' );

				$primelogo = '<a href="' . $primelink . '" rel="' . self::get_link_rel() . '" target="_blank" title="' . __( 'More about prime', ATKP_PLUGIN_PREFIX ) . '">' . $primelogo . '</a>';
			}
		}


		return $primelogo;
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_producturl( $myproduct ) {
		return self::redirect_external_url( $this->get_shopid_value( $myproduct ), $this->replace_tracking_code( $myproduct->shopid, $myproduct->producturl, $this->parameters->tracking_id ), $myproduct->productid, atkp_link_type::Link );
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_addtocarturl( $myproduct ) {
		return self::redirect_external_url( $this->get_shopid_value( $myproduct ), $this->replace_tracking_code( $myproduct->shopid, $myproduct->addtocarturl, $this->parameters->tracking_id ), $myproduct->productid, atkp_link_type::Cart );
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_customerreviewsurl( $myproduct ) {
		return self::redirect_external_url( $this->get_shopid_value( $myproduct ), $this->replace_tracking_code( $myproduct->shopid, $myproduct->customerreviewurl, $this->parameters->tracking_id ), $myproduct->productid, atkp_link_type::Customerreview );
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_productlink( $myproduct ) {

		$linkttitle = $myproduct->title;

		if ( $this->get_shop_value( $myproduct ) != null ) {
			$linkttitle = $this->get_shop_value( $myproduct )->get_tooltip();
		}

		return $this->build_external_link( $myproduct->producturl, $linkttitle, $myproduct->productid, $this->parameters->listid, $this->parameters->templateid, $this->get_shopid_value( $myproduct ), atkp_link_type::Link, $this->parameters->tracking_id );
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_cartlink( $myproduct ) {

		if ( $myproduct->addtocarturl == '' ) {
			return $this->get_productlink( $myproduct );
		}

		$linkttitle = $myproduct->title;

		if ( $this->get_shop_value( $myproduct ) != null ) {
			$linkttitle = $this->get_shop_value( $myproduct )->get_tooltip();
		}

		return $this->build_external_link( $myproduct->addtocarturl, $linkttitle, $myproduct->productid, $this->parameters->listid, $this->parameters->templateid, $this->get_shopid_value( $myproduct ), atkp_link_type::Cart, $this->parameters->tracking_id );
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_percentagesaved_value( $myproduct ) {
		if ( $myproduct == null ) {
			return 0;
		}

		$myproduct = $this->get_datasource_product( $myproduct );

		if ( $myproduct->salepricefloat <= 0 && $myproduct->variations != null ) {
			foreach ( $myproduct->variations as $variation ) {
				return $variation->percentagesaved == '' ? 0 : intval( $variation->percentagesaved );
			}
		}

		return $myproduct->percentagesaved == '' ? 0 : intval( $myproduct->percentagesaved );
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_amountsaved_value( $myproduct ) {
		if ( $myproduct == null ) {
			return 0;
		}

		$myproduct = $this->get_datasource_product( $myproduct );

		if ( $myproduct->salepricefloat <= 0 && $myproduct->variations != null ) {
			foreach ( $myproduct->variations as $variation ) {
				return $variation->amountsavedfloat;
			}
		}

		return $myproduct->amountsavedfloat;
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_amountsaved_fallback( $myproduct ) {
		if ( $myproduct == null ) {
			return 0;
		}

		$myproduct = $this->get_datasource_product( $myproduct );

		if ( $myproduct->salepricefloat <= 0 && $myproduct->variations != null ) {
			foreach ( $myproduct->variations as $variation ) {
				return $variation->amountsaved;
			}
		}

		return $myproduct->amountsaved;
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_listprice_value( $myproduct ) {
		if ( $myproduct == null ) {
			return 0;
		}

		$myproduct = $this->get_datasource_product( $myproduct );

		if ( $myproduct->salepricefloat <= 0 && $myproduct->variations != null ) {
			foreach ( $myproduct->variations as $variation ) {
				return $variation->listpricefloat;
			}
		}

		return $myproduct->listpricefloat;
	}


	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return int
	 */
	public function get_listprice_fallback( $myproduct ) {
		if ( $myproduct == null ) {
			return 0;
		}

		$myproduct = $this->get_datasource_product( $myproduct );

		if ( $myproduct->salepricefloat <= 0 && $myproduct->variations != null ) {
			foreach ( $myproduct->variations as $variation ) {
				return $variation->listprice;
			}
		}

		return $myproduct->listprice;
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return int
	 */
	public function get_saleprice_value( $myproduct ) {
		if ( $myproduct == null ) {
			return 0;
		}

		$myproduct = $this->get_datasource_product( $myproduct );

		if ( $myproduct->salepricefloat <= 0 && $myproduct->variations != null ) {
			foreach ( $myproduct->variations as $variation ) {
				return $variation->salepricefloat;
			}
		}

		return $myproduct->salepricefloat;
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return int
	 */
	public function get_baseprice_value( $myproduct ) {
		if ( $myproduct == null ) {
			return 0;
		}

		return $myproduct->basepricefloat;
	}


	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_unitprice_value( $myproduct ) {
		if ( $myproduct == null ) {
			return 0;
		}

		return $myproduct->unitpricefloat;
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return int
	 */
	public function get_saleprice_fallback( $myproduct ) {
		if ( $myproduct == null ) {
			return 0;
		}


		return $myproduct->baseprice;
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return int
	 */
	public function get_baseprice_fallback( $myproduct ) {
		if ( $myproduct == null ) {
			return 0;
		}

		$myproduct = $this->get_datasource_product( $myproduct );

		if ( $myproduct->salepricefloat <= 0 && $myproduct->variations != null ) {
			foreach ( $myproduct->variations as $variation ) {
				return $variation->saleprice;
			}
		}

		return $myproduct->saleprice;
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return int
	 */
	public function get_shipping_value( $myproduct ) {
		if ( $myproduct == null ) {
			return 0;
		}

		$myproduct = $this->get_datasource_product( $myproduct );

		if ( $myproduct->salepricefloat <= 0 && $myproduct->variations != null ) {
			foreach ( $myproduct->variations as $variation ) {
				return $variation->shippngfloat;
			}
		}

		return $myproduct->shippingfloat;
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return int
	 */
	public function get_shipping_fallback( $myproduct ) {
		if ( $myproduct == null ) {
			return 0;
		}

		$myproduct = $this->get_datasource_product( $myproduct );

		if ( $myproduct->salepricefloat <= 0 && $myproduct->variations != null ) {
			foreach ( $myproduct->variations as $variation ) {
				return $variation->shipping;
			}
		}


		return $myproduct->shipping;
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_shop_value( $myproduct ) {
		if ( $myproduct == null ) {
			return null;
		}

		$myproduct = $this->get_datasource_product( $myproduct );

		return $myproduct->shop;
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_shopid_value( $myproduct ) {
		if ( $myproduct == null ) {
			return null;
		}

		$myproduct = $this->get_datasource_product( $myproduct );

		return $myproduct->shopid;
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_percentagesaved( $myproduct, $format = '-%s%%' ) {

		if ( $this->get_percentagesaved_value( $myproduct ) == 0 ) {
			return '';
		} else {
			return sprintf( $format, round( $this->get_percentagesaved_value( $myproduct ), 0 ) );
		}
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_savetext( $myproduct, $format = '%s' ) {
		if ( $this->get_amountsaved_value( $myproduct ) == 0 ) {
			return '';
		} else {
			return sprintf( $format, $this->formatFloat( $this->get_amountsaved_value( $myproduct ), $this->get_amountsaved_fallback( $myproduct ), $this->get_shopid_value( $myproduct ) ) );
		}
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_total( $myproduct, $format = '%s', $emptytext = '' ) {

		if ( $this->get_saleprice_value( $myproduct ) == 0 ) {
			return $emptytext;
		} else {
			$shipping = $this->get_shipping_value( $myproduct );
			$price    = $this->get_saleprice_value( $myproduct );

			if ( $shipping < 0 || $shipping > 100 ) {
				$shipping = 0;
			}
			if ( $price < 0 || $price > 100000 ) {
				$price = 0;
			}

			return sprintf( $format, $this->formatFloat( $shipping + $price, $this->get_saleprice_fallback( $myproduct ), $this->get_shopid_value( $myproduct ) ) );
		}
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_shipping( $myproduct, $format = '%s', $emptytext = '' ) {

		if ( $this->get_shipping_value( $myproduct ) == 0 ) {
			return $emptytext;
		} else {
			return sprintf( $format, $this->formatFloat( $this->get_shipping_value( $myproduct ), $this->get_shipping_fallback( $myproduct ), $this->get_shopid_value( $myproduct ) ) );
		}
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_pricetext( $myproduct, $format = '%s', $emptytext = '' ) {

		if ( $this->get_saleprice_value( $myproduct ) == 0 ) {
			return $emptytext;
		} else {
			return sprintf( $format, $this->formatFloat( $this->get_saleprice_value( $myproduct ), $this->get_saleprice_fallback( $myproduct ), $this->get_shopid_value( $myproduct ) ) );
		}
	}

	public function get_basepricetext( $myproduct, $format = '(%s / %s)', $emptytext = '' ) {

		if ( $this->get_baseprice_value( $myproduct ) == 0 || $myproduct->baseunit == '' ) {
			return $emptytext;
		} else {
			return sprintf( $format, $this->formatFloat( $this->get_baseprice_value( $myproduct ), $this->get_baseprice_fallback( $myproduct ), $this->get_shopid_value( $myproduct ) ), $myproduct->baseunit );
		}
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_listpricetext( $myproduct, $format = '%s', $emptytext = '' ) {

		if ( $this->get_listprice_value( $myproduct ) == 0 || $this->get_listprice_value( $myproduct ) <= $this->get_saleprice_value( $myproduct ) ) {
			return $emptytext;
		} else {
			return sprintf( $format, $this->formatFloat( $this->get_listprice_value( $myproduct ), $this->get_listprice_fallback( $myproduct ), $this->get_shopid_value( $myproduct ) ) );
		}
	}

	public function get_priceinfotext() {
		return __( 'Price incl. VAT., Excl. Shipping', ATKP_PLUGIN_PREFIX );
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_wp_review_rating( $myproduct ) {
		if ( function_exists( 'wp_review_show_total' ) ) {
			return wp_review_show_total( false, 'atkp-totalrating', $myproduct->productid );
		}

		return '';
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_displayfield( $myproduct, $fieldname ) {
		//if ( ! isset( $myproduct->displayfields[ $fieldname ] ) ) {
		//	return '';
		//}

		$fieldmeta = null;

		foreach ( $myproduct->displayfields as $dfieldname => $df ) {
			if ( $dfieldname == $fieldname || $df->name == $fieldname ) {
				$fieldmeta = $df;
			}
		}
		if ( $fieldmeta == null )
			return '';

		//	$myproduct->displayfields[ $fieldname ];
		$fieldvalue = '';


		if ( is_a( $fieldmeta, 'atkp_udtaxonomy' ) || ( isset( $fieldmeta->type ) && $fieldmeta->type == 6 ) ) {
			$taxonomy = $fieldmeta;

			$product_terms = wp_get_object_terms( $myproduct->productid, $taxonomy->name );
			if ( isset( $product_terms ) && ! is_wp_error( $product_terms ) ) {
				foreach ( $product_terms as $term ) {
					//if ( get_option( ATKP_PLUGIN_PREFIX . '_product_enabled', false ) == true ) {
					//	$fieldvalue .= ( $fieldvalue == '' ? '' : ', ' ) . '<a href="' . get_term_link( $term->slug, $taxonomy->name ) . '">' . esc_html( $term->name ) . '</a>';
					//} else {
					$fieldvalue .= ( $fieldvalue == '' ? '' : ', ' ) . esc_html( $term->name );
					//}
				}

			}

		} else if ( is_a( $fieldmeta, 'atkp_udfield' ) ) {
			$field = $fieldmeta;

			$fieldvalue = $this->generate_customplaceholder( $myproduct, $field, $field->isnewfield );

		} else {
			$fieldvalue = $fieldmeta;
		}//throw new \Exception('unknown fieldmeta: '.$fieldmeta);

		return $fieldvalue;
	}


	public function get_comparefields( $products, $grouped = true, $showbasicdata = true, $filtertype = 'datasheet') {
//TODO: Duplicate code in template helper
		$fieldgroups = array();


		foreach ( $products as $product ) {

			$gg = ATKPTools::get_fieldgroups_by_productid( $product->productid );
			if ( $gg != null ) {
				foreach ( $gg as $g ) {
					array_push( $fieldgroups, $g );
				}
			}
		}

		usort( $fieldgroups, array( $this, "sortfieldgroup" ) );



		$comparefields = array();
		$comparegroups = array();

		if ( $showbasicdata ) {
			foreach ( $products as $product ) {
				$comparegroup = new  atkp_template_comparegroup();
				//$comparefield->id = $field->id;
				$comparegroup->id          = -99;
				$comparegroup->caption     = __('General', ATKP_PLUGIN_PREFIX);

				$comparegroup->sortorder   = -1;
				$comparegroup->isvisible   = $grouped && $comparegroup->caption != '';

				$comparefield = new  atkp_template_comparevalue();
				$comparefield->id          = 'manufacturer';
				$comparefield->caption     = __('Manufacturer', ATKP_PLUGIN_PREFIX);
				$comparefield->detail      = $product->manufacturer;
				$comparefield->align       = 2;

				$vals                      = array();
				array_push( $vals, $comparefield );
				$comparegroup->values = $vals;


				array_push( $comparegroups, $comparegroup );
			}
		}


		foreach ( $fieldgroups as $fieldgroup ) {

			$fields       = ATKPTools::get_post_setting( $fieldgroup->ID, ATKP_FIELDGROUP_POSTTYPE . '_fields' );
			$comparegroup = null;

			foreach ( $comparegroups as $xx ) {
				if ( $xx->id == $fieldgroup->ID ) {
					$comparegroup = $xx;
					break;
				}
			}
			if ( $comparegroup == null ) {
				$comparegroup = new  atkp_template_comparegroup();
				//$comparefield->id = $field->id;
				$comparegroup->id          = $fieldgroup->ID;
				$comparegroup->caption     = ATKPTools::get_post_setting( $fieldgroup->ID, ATKP_FIELDGROUP_POSTTYPE . '_name' );
				$comparegroup->description = ATKPTools::get_post_setting( $fieldgroup->ID, ATKP_FIELDGROUP_POSTTYPE . '_description' );
				$comparegroup->sortorder   = ATKPTools::get_post_setting( $fieldgroup->ID, ATKP_FIELDGROUP_POSTTYPE . '_sortorder' );
				$comparegroup->isvisible   = $grouped && $comparegroup->caption != '';
				$vals                      = array();
				array_push( $comparegroups, $comparegroup );
			} else {
				$vals = $comparegroup->values;
			}

			/** @var array $fields */
			foreach ( $fields as $field ) {

				if ( $filtertype == 'detaillist' ) {

					if ( ! $field->showdetaillist ) {
						continue;
					}

				} else if ( $filtertype == 'datasheet' ) {

					if ( ! $field->showdatasheet ) {
						continue;
					}

				} else {
					if ( ! $field->showcomparetable && ! $field->showmobilecomparetable ) {
						continue;
					}
				}

				$found = false;
				if ( $grouped ) {
					foreach ( $vals as $compare ) {
						if ( $compare->id == $field->name ) {
							$found = true;
							break;
						}
					}
				} else {
					foreach ( $comparefields as $compare ) {
						if ( $compare->id == $field->name ) {
							$found = true;
							break;
						}
					}
				}

				if ( $found ) {
					continue;
				}

				$comparefield = new  atkp_template_comparevalue();
				//$comparefield->id = $field->id;
				$comparefield->id          = $field->name;
				$comparefield->caption     = $field->caption;
				$comparefield->description = $field->description;

				$comparefield->detail = $this->get_displayfield( $product, $field->name );

				$comparefield->align       = 2;

				if ( $filtertype == 'comparetable' ) {
					if ( $field->showcomparetable && $field->showmobilecomparetable ) {
						$comparefield->viewtype = 1;
					} else if ( $field->showmobilecomparetable ) {
						$comparefield->viewtype = 3;
					} else {
						$comparefield->viewtype = 2;
					}
				}

				array_push( $comparefields, $comparefield );
				array_push( $vals, $comparefield );
			}

			$comparegroup->values = $vals;

		}


		return $comparegroups;
	}

	private function sortfieldgroup( $a, $b ) {

		$sortordera = ATKPTools::get_post_setting( $a->ID, ATKP_FIELDGROUP_POSTTYPE . '_sortorder' );
		$sortorderb = ATKPTools::get_post_setting( $b->ID, ATKP_FIELDGROUP_POSTTYPE . '_sortorder' );

		if ( $sortordera == '' ) {
			$sortordera = 0;
		} else {
			$sortordera = intval( $sortordera );
		}

		if ( $sortorderb == '' ) {
			$sortorderb = 0;
		} else {
			$sortorderb = intval( $sortorderb );
		}


		if ( $sortordera == $sortorderb ) {
			return 0;
		}

		return ( $sortordera < $sortorderb ) ? - 1 : 1;
	}

	private function generate_customplaceholder( $myproduct, $newfield, $isnewfield ) {

		$text_no  = get_option( ATKP_PLUGIN_PREFIX . '_text_no', '' );
		$text_yes = get_option( ATKP_PLUGIN_PREFIX . '_text_yes', '' );

		$result = '';

		if ( $isnewfield ) {
			$result = $myproduct->get_metavalue( ATKP_PRODUCT_POSTTYPE . '_cf_' . $newfield->name );
		} else {
			$result = $myproduct->get_metavalue( ATKP_PRODUCT_POSTTYPE . '_customfield_' . $newfield->name );
		}

		switch ( $newfield->type ) {
			case 4:

				//yesno
				if ( $newfield->format == 'text' ) {
					if ( $result == '1' ) {
						$result = __( 'Yes', ATKP_PLUGIN_PREFIX );
					} else if ( $result == '0' ) {
						$result = __( 'No', ATKP_PLUGIN_PREFIX );
					} else {
						$result = '';
					}
				} else {
					if ( $result == '1' ) {
						if ( $text_yes != '' ) {
							$result = $text_yes;
						} else {
							$result = '<img src="' . plugins_url( 'images/yes.png', ATKP_PLUGIN_FILE ) . '" style="width: 16px;" alt="' . __( 'Yes', ATKP_PLUGIN_PREFIX ) . '"/>';
						}
					} else if ( $result == '0' ) {
						if ( $text_no != '' ) {
							$result = $text_no;
						} else {
							$result = '<img src="' . plugins_url( 'images/no.png', ATKP_PLUGIN_FILE ) . '" style="width: 16px;" alt="' . __( 'No', ATKP_PLUGIN_PREFIX ) . '"/>';
						}
					} else {
						$result = '';
					}
				}
				break;

			case 1:
				//textfield
				if ( $newfield->format == 'stars' ) {
					$val = $result == '' || $result == 0 ? floatval( $newfield->values ) : floatval( $result );

					$class = 'atkp-star-' . number_format( $this->roundRate( $val ), 1, ' atkp-star-0', '' );
					$title = sprintf( __( '%s of 5 stars', ATKP_PLUGIN_PREFIX ), $val );

					$tempstr = '<span class="atkp-star-compare atkp-star ' . $class . '" title="' . $title . '"></span>';

					$tempstr = apply_filters( 'atkp_stars_formatvalue', $tempstr, $val, $class, $title );

					return $tempstr;
				}

				break;
			case 5:
				//htmlfield
				//$result = str_replace("\r", '<br />', $result);
				$result = wpautop( $result );
				break;

		}

		if ( $result != '' ) {
			if ( $newfield->prefix != '' ) {
				$result = $newfield->prefix . ' ' . $result;
			}
			if ( $newfield->suffix != '' ) {
				$result = $result . ' ' . $newfield->suffix;
			}
		}

		return $result;
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_refreshdate( $myproduct ) {
		return ATKPTools::get_formatted_date( $myproduct->updatedon );
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_refreshtime( $myproduct ) {
		return ATKPTools::get_formatted_time( $myproduct->updatedon );
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_disclaimer( $myproduct, $disclaimertext = '' ) {
		if ( $disclaimertext == '' ) {
			$disclaimertext = ATKPSettings::$access_disclaimer_text;
		}

		return str_replace( '%refresh_time%', $this->get_refreshtime( $myproduct ), str_replace( '%refresh_date%', $this->get_refreshdate( $myproduct ), $disclaimertext ) );
	}

	/**
	 * @param null|atkp_shop $myshop
	 *
	 * @return string
	 */
	public function get_shop_logourl( $myshop = null ) {
		if ( $myshop == null ) {
			return '';
		}

		if ( ! $myshop->displayshoplogo ) {
			return '';
		}

		if ( $myshop->get_logourl() == '' ) {
			if ( $myshop->get_smalllogourl() == '' ) {
				return $myshop->get_title();
			} else {
				return self::replace_image_url( $myshop->id, $myshop->get_smalllogourl() );
			}
		} else {
			return self::replace_image_url( $myshop->id, $myshop->get_logourl() );;
		}
	}

	/**
	 * @param null|atkp_shop $myshop
	 *
	 * @return string
	 */
	public function get_shop_smalllogourl( $myshop = null ) {
		if ( $myshop == null ) {
			return '';
		}

		if ( ! $myshop->displayshoplogo ) {
			return '';
		}

		if ( $myshop->get_smalllogourl() == '' ) {
			return $this->get_shop_logourl( $myshop );
		} else {
			return self::replace_image_url( $myshop->id, $myshop->get_smalllogourl() );
		}
	}

	/**
	 * @param null|atkp_shop $myshop
	 *
	 * @return string
	 */
	public function get_shop_logo( $myshop = null ) {
		if ( $myshop == null ) {
			return '';
		}

		if ( ! $myshop->displayshoplogo ) {
			return $myshop->get_title();
		}

		if ( $myshop->get_logourl() == '' ) {
			return $myshop->get_title();
		} else {
			return '<img src="' . self::replace_image_url( $myshop->id, $myshop->get_logourl() ) . '" alt="' . $myshop->get_title() . '" style="max-height:50px;max-width:140px" />';
		}
	}

	/**
	 * @param null|atkp_shop $myshop
	 *
	 * @return string
	 */
	public function get_shop_smalllogo( $myshop = null ) {
		if ( $myshop == null ) {
			return '';
		}

		if ( ! $myshop->displayshoplogo ) {
			return $myshop->get_title();
		}


		if ( $myshop->get_smalllogourl() == '' ) {
			return $myshop->get_title();
		} else {
			return '<img src="' . self::replace_image_url( $myshop->id, $myshop->get_smalllogourl() ) . '" alt="' . $myshop->get_title() . '" />';
		}
	}

	/**
	 * @param null|atkp_shop $myshop
	 *
	 * @return string
	 */
	public function get_shop_title( $myshop = null ) {
		if ( $myshop == null ) {
			return '';
		}

		return $myshop->get_title();
	}

	/**
	 * @param null|atkp_list $mylist
	 *
	 * @return string
	 */
	public function get_list_title( $mylist = null ) {
		if ( $mylist == null ) {
			return '';
		}

		return $mylist->title;
	}

	/**
	 * @param null|atkp_list $mylist
	 *
	 * @return array
	 */
	public function get_list_displayfields( $mylist = null ) {
		if ( $mylist == null ) {
			return '';
		}

		$array = array();


		return $array;
	}

	/**
	 * @param null|atkp_shop $myshop
	 *
	 * @return string|array
	 */
	public function get_shop_displayfields( $myshop = null ) {
		if ( $myshop == null ) {
			return '';
		}

		$array = array();

		$array['shopcustomfield1'] = $myshop->customfield1;
		$array['shopcustomfield2'] = $myshop->customfield2;
		$array['shopcustomfield3'] = $myshop->customfield3;

		return $array;
	}

	public function get_offer_linktext() {
		return __( 'Buy now', ATKP_PLUGIN_PREFIX );
	}

	/**
	 * @param null|atkp_product_offer $myoffer
	 *
	 * @return string
	 */
	public function get_offerproducturl( $myoffer ) {
		return self::redirect_external_url( $myoffer->shopid, $myoffer->link, $myoffer->id, atkp_link_type::Offer );
	}

	/**
	 * @param null|atkp_product_offer $myoffer
	 *
	 * @return string
	 */
	public function get_offer_productlink( $myoffer ) {

		$shop       = $myoffer->shopid == '' || $myoffer->shopid == 999 ? null : atkp_shop::load( $myoffer->shopid );
		$linkttitle = $myoffer->title;

		if ( $shop != null ) {
			$linkttitle = $shop->get_tooltip();
		}

		return $this->build_external_link( $myoffer->link, $linkttitle, $myoffer->id, $this->parameters->listid, $this->parameters->templateid, $myoffer->shopid, atkp_link_type::Offer );
	}

	/**
	 * @param null|atkp_product_offer $myoffer
	 *
	 * @return string
	 */
	public function get_offer_price( $myoffer, $format = '%s', $emptytext = '' ) {

		if ( $myoffer == null || $myoffer->price == '' ) {
			return $emptytext;
		} else {
			return sprintf( $format, $this->formatFloat( $myoffer->pricefloat, $myoffer->price, $myoffer->shopid ) );
		}
	}

	/**
	 * @param null|atkp_product_offer $myoffer
	 *
	 * @return string
	 */
	public function get_offer_oldprice( $myoffer, $format = '%s', $emptytext = '' ) {

		if ( $myoffer == null || $myoffer->product == null || $myoffer->product->listpricefloat == 0 ) {
			return $emptytext;
		} else {
			return sprintf( $format, $this->formatFloat( $myoffer->product->listpricefloat, $myoffer->listprice, $myoffer->shopid ) );
		}
	}

	/**
	 * @param null|atkp_product_offer $myoffer
	 *
	 * @return string
	 */
	public function get_offer_total( $myoffer, $format = '%s', $emptytext = '' ) {

		if ( $myoffer == null || $myoffer->pricefloat == 0 ) {
			return $emptytext;
		} else {
			$shipping = $myoffer->shippingfloat;
			$price    = $myoffer->pricefloat;

			if ( $shipping < 0 || $shipping > 100 ) {
				$shipping = 0;
			}
			if ( $price < 0 || $price > 100000 ) {
				$price = 0;
			}

			return sprintf( $format, $this->formatFloat( $shipping + $price, $myoffer->price, $myoffer->shopid ) );
		}
	}

	/**
	 * @param null|atkp_product_offer $myoffer
	 *
	 * @return string
	 */
	public function get_offer_shipping( $myoffer, $format = '%s', $emptytext = '' ) {

		if ( $myoffer == null || $myoffer->shippingfloat == 0 ) {
			return $emptytext;
		} else {
			return sprintf( $format, $this->formatFloat( $myoffer->shippingfloat, $myoffer->shipping, $myoffer->shopid ) );
		}
	}

	/**
	 * @param null|atkp_product_offer $myoffer
	 *
	 * @return string
	 */
	public function get_offer_availability( $myoffer, $format = 'Availability: %s' ) {
		if ( $format == 'Availability: %s' ) {
			$formattxt = __( 'Availability: %s', ATKP_PLUGIN_PREFIX );
		} else {
			$formattxt = $format;
		}

		if ( $myoffer->availability == '' ) {
			return sprintf( $formattxt, 'N/A' );
		} else {
			return sprintf( $formattxt, $myoffer->availability );
		}
	}

	/**
	 * @param null|atkp_product_offer $myoffer
	 *
	 * @return string
	 */
	public function get_offer_bestprice( $myoffer, atkp_product $myproduct ) {
		$minoffer = $this->get_minoffer( $myproduct, true );

		if ( $minoffer != null && $minoffer->id == $myoffer->id ) {
			return __( 'Best price', ATKP_PLUGIN_PREFIX );
		} else {
			return '';
		}
	}

	/**
	 * @param null|atkp_product_offer $myoffer
	 *
	 * @return string
	 */
	public function get_offer_url( $myoffer ) {
		return $myoffer == null ? '' : self::redirect_external_url( $myoffer->shopid, $myoffer->link, $myoffer->id, atkp_link_type::Offer );
	}

	/**
	 * @param null|atkp_product_offer $myoffer
	 *
	 * @return string
	 */
	public function get_offer_title( $myoffer ) {
		return $myoffer == null ? '' : $myoffer->title;
	}

	/**
	 * @param null|atkp_product $myproduct
	 * @param null|atkp_product_image $myimage
	 *
	 * @return string
	 */
	public function get_image_smallimageurl( $myproduct, $myimage ) {

		if ( $myimage->smallimageurl == '' ) {
			return plugins_url( __( '../../images/image-not-found.jpg', ATKP_PLUGIN_PREFIX ), __FILE__ );
		}

		return self::replace_image_url( $this->get_shopid_value( $myproduct ), $myimage->smallimageurl, $myproduct->productid, $myproduct->listid );
	}

	/**
	 * @param null|atkp_product $myproduct
	 * @param null|atkp_product_image $myimage
	 *
	 * @return string
	 */
	public function get_image_mediumimageurl( $myproduct, atkp_product_image $myimage ) {

		if ( $myimage->mediumimageurl == '' ) {
			return plugins_url( __( '../../images/image-not-found.jpg', ATKP_PLUGIN_PREFIX ), __FILE__ );
		}

		return self::replace_image_url( $this->get_shopid_value( $myproduct ), $myimage->mediumimageurl, $myproduct->productid, $myproduct->listid);
	}

	/**
	 * @param null|atkp_product $myproduct
	 * @param null|atkp_product_image $myimage
	 *
	 * @return string
	 */
	public function get_image_largeimageurl( $myproduct, $myimage ) {

		if ( $myimage->largeimageurl == '' ) {
			return plugins_url( __( '../../images/image-not-found.jpg', ATKP_PLUGIN_PREFIX ), __FILE__ );
		}

		return self::replace_image_url( $this->get_shopid_value( $myproduct ), $myimage->largeimageurl, $myproduct->productid, $myproduct->listid);
	}

	/**
	 * @param null|atkp_product $myproduct
	 * @param null|atkp_product_image $myimage
	 *
	 * @return string
	 */
	public function get_image_smallimage( $myproduct, $myimage ) {
		return '<img src="' . $this->get_image_smallimageurl( $myproduct, $myimage ) . '" alt="' . esc_attr( $myproduct->title ) . '" />';
	}

	/**
	 * @param null|atkp_product $myproduct
	 * @param null|atkp_product_image $myimage
	 *
	 * @return string
	 */
	public function get_image_mediumimage( $myproduct, $myimage ) {
		return '<img src="' . $this->get_image_mediumimageurl( $myproduct, $myimage ) . '" alt="' . esc_attr( $myproduct->title ) . '" />';
	}

	/**
	 * @param null|atkp_product $myproduct
	 * @param null|atkp_product_image $myimage
	 *
	 * @return string
	 */
	public function get_image_largeimage( $myproduct, $myimage ) {
		return '<img src="' . $this->get_image_largeimageurl( $myproduct, $myimage ) . '" alt="' . esc_attr( $myproduct->title ) . '" />';
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_smallimageurl( $myproduct ) {
		$myproduct = $this->get_datasource_product( $myproduct );

		$smallimageurl = atkp_product::get_mainimage( $myproduct, 'smalltolarge' );

		if ( $smallimageurl == '' ) {
			return plugins_url( __( '../../images/image-not-found.jpg', ATKP_PLUGIN_PREFIX ), __FILE__ );
		}

		return self::replace_image_url( $this->get_shopid_value( $myproduct ), $smallimageurl, $myproduct->productid, $myproduct->listid, 'small' );
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_mediumnimageurl( $myproduct ) {
		$myproduct = $this->get_datasource_product( $myproduct );

		$smallimageurl = atkp_product::get_mainimage( $myproduct, 'mediumtolarge' );

		if ( $smallimageurl == '' ) {
			return plugins_url( __( '../../images/image-not-found.jpg', ATKP_PLUGIN_PREFIX ), __FILE__ );
		}

		return self::replace_image_url( $this->get_shopid_value( $myproduct ), $smallimageurl, $myproduct->productid, $myproduct->listid, 'medium' );
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_largeimageurl( $myproduct, $default_value = '' ) {
		$myproduct = $this->get_datasource_product( $myproduct );

		$smallimageurl = atkp_product::get_mainimage( $myproduct, 'largetosmall' );

		if ( $smallimageurl == '' ) {
			if ( $default_value == '' ) {
				return plugins_url( __( '../../images/image-not-found.jpg', ATKP_PLUGIN_PREFIX ), __FILE__ );
			}

			return $default_value;
		}

		return self::replace_image_url( $this->get_shopid_value( $myproduct ), $smallimageurl, $myproduct->productid, $myproduct->listid, 'large' );
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_smallimage( $myproduct ) {
		return '<img src="' . $this->get_smallimageurl( $myproduct ) . '" alt="' . esc_attr( $myproduct->title ) . '" />';
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_mediumnimage( $myproduct ) {
		return '<img src="' . $this->get_mediumnimageurl( $myproduct ) . '" alt="' . esc_attr( $myproduct->title ) . '" />';
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_largeimage( $myproduct ) {
		return '<img src="' . $this->get_largeimageurl( $myproduct ) . '" alt="' . esc_attr( $myproduct->title ) . '" />';
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_shorttitle( $myproduct ) {
		$title = $this->get_title( $myproduct );

		if ( $this->parameters->get_short_title_length() > 0 ) {
			return ( strlen( $title ) > $this->parameters->get_short_title_length() ) ? substr( $title, 0, $this->parameters->get_short_title_length() ) : $title;
		} else {
			return $title;
		}
	}

	public function get_bestseller_text( $itemIdx, $ignoreSettings = false ) {
		if ( ! $ignoreSettings && ( ( $itemIdx > 3 && ATKPSettings::$bestsellerribbon == 1 ) || $itemIdx <= 0 ) ) {
			return '';
		} else {
			return sprintf( __( '#%s Best Seller', ATKP_PLUGIN_PREFIX ), $itemIdx );
		}
	}

	public function get_bestseller_number( $itemIdx, $ignoreSettings = false ) {
		if ( ! $ignoreSettings && ( ( $itemIdx > 3 && ATKPSettings::$bestsellerribbon == 1 ) || $itemIdx <= 0 ) ) {
			return '';
		} else {
			return sprintf( __( '#%s', ATKP_PLUGIN_PREFIX ), $itemIdx );
		}
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_rating_text( $myproduct ) {
		return sprintf( __( '%s of 5 stars', ATKP_PLUGIN_PREFIX ), $myproduct->rating );
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_star_rating( $myproduct ) {

		$class = 'atkp-star-' . number_format( $this->roundRate( floatval( $myproduct->rating ) ), 1, ' atkp-star-0', '' );

		return '<span class="atkp-star ' . $class . '" title="' . $this->get_rating_text( $myproduct ) . '"></span>';
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_reviewstext( $myproduct ) {

		$reviewstextNull = __( 'Show customer reviews', ATKP_PLUGIN_PREFIX );
		$reviewstext     = __( '%s customer reviews', ATKP_PLUGIN_PREFIX );
		$reviewstext2    = __( '1 customer review', ATKP_PLUGIN_PREFIX );

		if ( $myproduct->reviewcount == '' || $myproduct->reviewcount == 0 ) {

			if ( $this->parameters->get_hideemptyrating() ) {
				return '';
			} else {
				return $reviewstextNull;
			}

		} else {
			return sprintf( _n( $reviewstext2, $reviewstext, $myproduct->reviewcount, ATKP_PLUGIN_PREFIX ), $myproduct->reviewcount );

		}


	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_reviewslink( $myproduct ) {
		return $this->build_external_link( $myproduct->customerreviewurl, $this->get_reviewstext( $myproduct ), $myproduct->productid, $this->parameters->listid, $this->parameters->templateid, $this->get_shopid_value( $myproduct ), atkp_link_type::Customerreview, $this->parameters->tracking_id );

	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_reviewsmark( $myproduct ) {
		if ( $myproduct->isownreview ) {
			return '';
		} else {
			return $this->get_mark();
		}
	}

	public function get_detailtext() {
		return $this->parameters->get_productpage_title();
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function is_detailurl_available( $myproduct ) {
		$poststatus = get_post_status( $myproduct->productid );

		if ( $poststatus == 'publish' ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_detailurl( $myproduct ) {
		$detailurl = null;


		if ( $myproduct->productid != '' ) {
			$detailurl = apply_filters( 'atkp_get_detailurl', $detailurl, $myproduct->productid );

			if ( $detailurl == null ) {
				$postid = $myproduct->postids;


				if ( $postid != null ) {
					if ( is_array( $postid ) ) {
						foreach ( $postid as $p ) {
							if ( get_post_status( $p ) == 'publish' ) {
								$detailurl = get_permalink( $p );
								break;
							}
						}

					} else {
						if ( get_post_status( $postid ) == 'publish' ) {
							$detailurl = get_permalink( $postid );
						}
					}
				}
			}
		}

		return $detailurl;
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_detaillink( $myproduct ) {
		$detailurl = $this->get_detailurl( $myproduct );

		if ( $detailurl != '' && $detailurl != null ) {
			$detailurl = ' href="' . $detailurl . '" title="' . esc_attr( $myproduct->title ) . '"';
		}

		return $detailurl;
	}

	public function get_extended_value( $name, $atkp_product = null, $woo_product = null, $atkp_offer = null ) {
		$value = apply_filters( 'atkp_get_extended_formatter_value', '', $name, $atkp_product, $woo_product, $atkp_offer );

		return $value;
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_woocommerceurl( $myproduct, $woo_product = null ) {
		return $this->get_extended_value( 'get_woocommerceurl', $myproduct, $woo_product, null );
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_woocommercetitle( $myproduct, $woo_product = null ) {
		return $this->get_extended_value( 'get_woocommercetitle', $myproduct, $woo_product, null );
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_woocommercelink( $myproduct, $woo_product = null ) {
		return $this->get_extended_value( 'get_woocommercelink', $myproduct, $woo_product, null );

	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_images( $myproduct, $includemainimage = true, $maximages = 0 ) {
		$images = array();

		//include main image

		if ( $includemainimage ) {
			if ( $myproduct->smallimageurl != '' || $myproduct->mediumimageurl != '' || $myproduct->largeimageurl != '' ) {
				$mainimage                 = new atkp_product_image();
				$mainimage->smallimageurl  = $myproduct->smallimageurl;
				$mainimage->mediumimageurl = $myproduct->mediumimageurl;
				$mainimage->largeimageurl  = $myproduct->largeimageurl;

				array_push( $images, $mainimage );
			}
		}


		if ( is_array( $myproduct->images ) ) {
			foreach ( $myproduct->images as $image ) {
				if ( $image->smallimageurl == '' ) {
					$image->smallimageurl = $mainimage->mediumimageurl;
				}
				if ( $image->smallimageurl == '' ) {
					$image->smallimageurl = $mainimage->largeimageurl;
				}

				if ( $image->mediumimageurl == '' ) {
					$image->mediumimageurl = $mainimage->smallimageurl;
				}

				if ( $image->largeimageurl == '' ) {
					$image->largeimageurl = $mainimage->smallimageurl;
				}


				array_push( $images, $image );

				if ( $maximages > 0 && count( $images ) > $maximages ) {
					break;
				}
			}
		}

		return $images;
	}

	public function get_offercount( array $offers ) {
		return count( $offers ) == 1 ? sprintf( __( '%s offer', ATKP_PLUGIN_PREFIX ), count( $offers ) ) : sprintf( __( '%s offers', ATKP_PLUGIN_PREFIX ), count( $offers ) );

	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return atkp_product_offer
	 */
	public function get_minoffer( $myproduct, $includemainoffer = true, $alloffers = null, $minofferbyvalue = false ) {
		if ( $alloffers == null ) {
			$alloffers = $this->get_offers( $myproduct, $includemainoffer );
		}


		if ( ! $minofferbyvalue ) {
			return reset( $alloffers );
		} else {
			$minoffer = null;
			foreach ( $alloffers as $offer ) {
				if ( $minoffer == null || $minoffer->pricefloat >= $offer->pricefloat ) {
					$minoffer = $offer;
				}
			}

			return $minoffer;
		}
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return atkp_product_offer
	 */
	public function get_maxoffer( $myproduct, $includemainoffer = true, $alloffers = null, $maxofferbyvalue = false ) {
		if ( $alloffers == null ) {
			$alloffers = $this->get_offers( $myproduct, $includemainoffer );
		}

		if ( ! $maxofferbyvalue ) {
			return end( $alloffers );

		} else {
			$maxoffer = null;
			foreach ( $alloffers as $offer ) {
				if ( $maxoffer == null || $maxoffer->pricefloat <= $offer->pricefloat ) {
					$maxoffer = $offer;
				}
			}

			return $maxoffer;
		}
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return null|atkp_product_offer[]
	 */
	public function get_offers( $myproduct = null, $includemainoffer = true, $maxoffers = 0 ) {
		$offers = array();


		if ( $maxoffers <= 0 && $this->parameters != null ) {
			$maxoffers2 = $this->parameters->get_moreoffers_count();
			if ( $maxoffers2 != '' ) {
				$maxoffers = intval( $maxoffers2 );
			}
		}


		if ( $myproduct == null || ( $myproduct->productid == 0 && $myproduct->listid == 0 ) ) {
			$offers = array();
		} else if ( $myproduct->productid != '' && $myproduct->productid > 0 ) {
			$products = atkp_product_collection::load( $myproduct->productid );
			$offers   = $products->get_offers( $includemainoffer );
		} else if ( $myproduct->listid > 0 && $myproduct->ean != '' ) {
			$offers = atkp_list::get_offers( $myproduct->listid, $myproduct->ean, $myproduct->shopid );
		} else if ( $myproduct->productid == - 1 ) {
			//fake product
			$products = atkp_product_collection::load( $myproduct->productid );
			$offers   = $products->get_offers( $includemainoffer );
		}

		$offers = apply_filters('atkp_get_offers', $offers, $myproduct);

		$newarray = array();
		foreach ( $offers as $offer ) {

			array_push($newarray, $offer);
		}
		$offers = $newarray;


		usort( $offers, array( $this, "sortPrice" ) );


		//nur der erste shop wird angezeigt... so werden duplikate aufgrund zweier eans verhindert
		$shopsadded = array();

		usort( $offers, array( $this, "sortPriceWithOnTop" ) );

		$pricecomparisongroupshops = get_option( ATKP_PLUGIN_PREFIX . '_pricecomparisongroupshops', 1 );

		$newoffers = array();

		foreach ( $offers as $offer ) {
			/** @var atkp_product_offer $offer */

			if ( $offer->shopid <= - 1 || $offer->shopid == '' ) {
				$shop                     = new atkp_shop();
				$shop->customtitle        = $offer->shopname;
				$shop->customsmalllogourl = $offer->shoplogourl;
				$shop->customlogourl      = $offer->shoplogourl;
				if ( $offer->shoplogourl != '' ) {
					$shop->displayshoplogo = true;
				}
				$offer->shop = $shop;
			} else {

				if ( $offer->pricefloat == (float) 0 || $offer->hideoffer ) {
					continue;
				}

				if ( $pricecomparisongroupshops == 1 ) {
					if ( in_array( $offer->shopid, $shopsadded ) ) {
						continue;
					}
				}

				if ( ! atkp_shop::exists( $offer->shopid ) ) {
					continue;
				}

				if ( $offer->shop == null ) {
					$offer->shop = atkp_shop::load( $offer->shopid );
				}

				if ( $offer->shop->hidepricecomparision ) {
					continue;
				}
			}

			array_push( $newoffers, $offer );
			array_push( $shopsadded, $offer->shopid );

			if ( $maxoffers > 0 && count( $newoffers ) >= $maxoffers ) {
				break;
			}
		}

		if ( $pricecomparisongroupshops == 1 ) {

			$grouped_offers = array();

			foreach ( array_reverse( $newoffers ) as $offer ) {
				/** @var atkp_product_offer $offer */
				$title_trimmed = str_replace( trim( $offer->title ), ',', '' );
				$offer_key     = "{$title_trimmed}|{$offer->pricefloat}|{$offer->shippingfloat}";

				$grouped_offers[ $offer_key ] = $offer;
			}

			$newoffers = array();
			foreach ( ( $grouped_offers ) as $key => $newoffer ) {
				$newoffers[] = $newoffer;
			}

			$newoffers = array_reverse( $newoffers );
		}

		return $newoffers;
	}

	/**
	 * @param null|atkp_product $myproduct
	 *
	 * @return string
	 */
	public function get_bytext( $myproduct ) {
		if ( $myproduct->author != '' ) {
			return sprintf( __( 'by %s', ATKP_PLUGIN_PREFIX ), $myproduct->author );
		} else if ( $myproduct->manufacturer != '' ) {
			return sprintf( __( 'by %s', ATKP_PLUGIN_PREFIX ), $myproduct->manufacturer );
		} else {
			return '';
		}
	}

	public function get_currency( $shopid ) {
		$currencysign     = 1;
		$currenysettingid = '';

		if ( $shopid != '' ) {
			$shopids  = explode( '_', $shopid );
			$parentid = wp_get_post_parent_id( intval( $shopids[0] ) );

			if ( $parentid != null ) {
				$currenysettingid = $parentid;
			} else {
				$currenysettingid = $shopids[0];
			}

			$currencysign = ATKPTools::get_post_setting( $currenysettingid, ATKP_SHOP_POSTTYPE . '_currencysign' );
		}

		$currencysymbol  = 'EUR ';
		$currencysymbol2 = '';

		switch ( $currencysign ) {
			default:
			case 1:
				$currencysymbol = '&euro; ';
				break;
			case 2:
				$currencysymbol = 'EUR ';
				break;
			case 3:
				$currencysymbol = '&#36; ';
				break;
			case 4:
				$currencysymbol = 'USD ';
				break;
			case 5:
				return null;
				break;
			case 6:
				$currencysymbol  = ATKPTools::get_post_setting( $currenysettingid, ATKP_SHOP_POSTTYPE . '_currencysign_customprefix' );
				$currencysymbol2 = ATKPTools::get_post_setting( $currenysettingid, ATKP_SHOP_POSTTYPE . '_currencysign_customsuffix' );
				break;
		}

		return array( 'prefix' => $currencysymbol, 'suffix' => $currencysymbol2 );

	}

	public function formatFloat( $number, $fallback, $shopid ) {
		if($shopid == 999)
			return $fallback;

		$currency = $this->get_currency( $shopid );

		if ( $currency == null ) {
			return $fallback;
		}

		if ( $number == (float) 0 && $fallback != '' ) {
			$number = $this->price_to_float( $fallback );
		}

		return $currency['prefix'] . '' . number_format_i18n( $number, 2 ) . '' . $currency['suffix'];
	}

	private function startsWith( $haystack, $needle ) {
		// search backwards starting from haystack length characters from the end
		return $needle === "" || strrpos( $haystack, $needle, - strlen( $haystack ) ) !== false;
	}


	private function sortPriceWithOnTop( $a, $b ) {
		/** @var atkp_product_offer $a */
		/** @var atkp_product_offer $b */

		switch ( ATKPSettings::$pricecomparisonsort ) {
			case 1:
			default:
				$totalpriceA = $a->shippingfloat + $a->pricefloat;
				$totalpriceB = $b->shippingfloat + $b->pricefloat;
				break;
			case 2:
				$totalpriceA = $a->pricefloat;
				$totalpriceB = $b->pricefloat;
				break;
		}

		if ( $a->holdontop != 100 || $b->holdontop != 100 ) {
			$totalpriceA = floatval( $a->holdontop );
			$totalpriceB = floatval( $b->holdontop );
		}

		if ( $totalpriceA == $totalpriceB ) {
			$totalpriceA = floatval( $b->cpcfloat );
			$totalpriceB = floatval( $a->cpcfloat );
		}

		if ( $totalpriceA == $totalpriceB ) {
			return 0;
		}

		return ( $totalpriceA < $totalpriceB ) ? - 1 : 1;
	}

	private function sortPrice( $a, $b ) {
		/** @var atkp_product_offer $a */
		/** @var atkp_product_offer $b */

		switch ( ATKPSettings::$pricecomparisonsort ) {
			case 1:
			default:
				$totalpriceA = $a->shippingfloat + $a->pricefloat;
				$totalpriceB = $b->shippingfloat + $b->pricefloat;
				break;
			case 2:
				$totalpriceA = $a->pricefloat;
				$totalpriceB = $b->pricefloat;
				break;
		}

		if ( $totalpriceA == $totalpriceB ) {
			$totalpriceA = floatval( $b->cpcfloat );
			$totalpriceB = floatval( $a->cpcfloat );
		}

		if ( $totalpriceA == $totalpriceB ) {
			return 0;
		}

		return ( $totalpriceA < $totalpriceB ) ? - 1 : 1;
	}

	protected static function price_to_float( $s ) {
		$s = str_replace( ',', '.', $s );

		// remove everything except numbers and dot "."
		$s = preg_replace( "/[^0-9\.]/", "", $s );

		// remove all seperators from first part and keep the end
		$s = str_replace( '.', '', substr( $s, 0, - 3 ) ) . substr( $s, - 3 );

		// return float
		return round( (float) $s, 2 );
	}

	private function replace_tracking_code( $shop_id, $url, $tracking_id ) {

		if ( $shop_id == '' || $tracking_id == '' ) {
			return $url;
		}

		$shop = atkp_shop::load( $shop_id );

		if ( $shop != null && $shop->provider != null ) {
			$url = $shop->provider->replace_trackingid( $shop->parent_id, $url, $tracking_id );
		}

		return $url;
	}

	public static function get_sitekey() {
		$key_path = WP_CONTENT_DIR . '/uploads/atkp-imagereceiver-key.php';

		if ( ! file_exists( $key_path ) ) {
			$pw = chr( mt_rand( 97, 122 ) ) . mt_rand( 0, 9 ) . chr( mt_rand( 97, 122 ) ) . mt_rand( 10, 99 ) . chr( mt_rand( 97, 122 ) ) . mt_rand( 100, 999 ) . chr( mt_rand( 97, 122 ) ) . mt_rand( 10, 99 ) . chr( mt_rand( 97, 122 ) ) . mt_rand( 100, 999 );

			file_put_contents( $key_path, '<?php $x = "' . $pw . '";' );
		}

		if ( file_exists( $key_path ) ) {
			$site_key = file_get_contents( $key_path );
		} else {
			$site_key = md5( ATKP_PLUGIN_DIR . '/tools/atkp_imagereceiver.php' );
		}

		return $site_key;
	}

	public static function get_image_receiver_url( $productid, $listid, $shopid, $img_url ) {
		if ( ATKPTools::startsWith( $img_url, get_site_url() ) ) {
			return $img_url;
		}
		$name = base64_encode( $img_url ); //pathinfo($img_url, PATHINFO_FILENAME);; // to get file name
		//$outfile = plugins_url( '/tools/atkp_imagereceiver.php', ATKP_PLUGIN_FILE );

		//$site_key = self::get_sitekey();

		//return $outfile . '?image=' . rawurlencode( base64_encode( $img_url ) ) . '&hash=' . ( ( md5( $img_url . $site_key ) ) );

		return home_url() . '/?a_image=1&pid=' . rawurlencode( $productid ) . '&lid=' . rawurlencode( $listid ) . '&sid=' . rawurlencode( $shopid ) . '&name=' . rawurlencode( $name);
	}

	public static function replace_image_url( $shopid, $url, $productid = null, $listid = null, $imagesize = null ) {
		$bak_url   = $url;
		$redirtype = ATKPTools::get_setting( ATKP_PLUGIN_PREFIX . '_product_imagetype', 0 );

		switch ( $redirtype ) {
			default:
				//disabled
				break;
			case 2:
				//no mainimage + internal redirect
				$url = self::get_image_receiver_url( $productid, $listid, $shopid, $url );

				break;
			case 3:
				//mainimage + internal redirect

				$thumb_url = '';
				if ( $productid != null ) {
					//hauptbild laden
					switch ( $imagesize ) {
						case 'small':
							$thumb_url = get_the_post_thumbnail_url( $productid, 'thumbnail' );
							break;
						default:
						case 'medium':
							$thumb_url = get_the_post_thumbnail_url( $productid, 'medium' );
							break;
						case 'large':
							$thumb_url = get_the_post_thumbnail_url( $productid, 'full' );
							break;
					}
				}

				if ( $thumb_url == '' ) {
					return self::get_image_receiver_url( $productid, $listid, $shopid, $url );
				} else {
					return $thumb_url;
				}

				break;
		}

		if ( $bak_url != '' && $url == '' ) {
			return $bak_url;
		} else {
			return $url;
		}
	}

	public static function redirect_external_url( $shop_id, $url, $product_id = '', $link_type = '' ) {
		if ( $url == '' ) {
			return $url;
		}

		$shop = atkp_shop::load( $shop_id );

		if ( $shop != null && $shop->redirection_type != atkp_redirection_type::DISABLED ) {


			$site_key = self::get_sitekey();

			switch ( $shop->redirection_type ) {
				default:
					//disabled
					break;
				case atkp_redirection_type::INTERNAL_REDIRECTION:
					//internal redirect
					//$outfile = plugins_url( '/tools/atkp_out.php', ATKP_PLUGIN_FILE );
					$url = home_url() . '/a_out/?url=' . rawurlencode( base64_encode( $url ) ) . '&pid=' . $product_id . '&sid=' . $shop_id . '&pt=' . $link_type . '&hash=' . ( ( md5( $url . $site_key ) ) );
					break;
				case atkp_redirection_type::INTERNAL_REDIRECTION_NAME:
					$san_title = sanitize_title( get_the_title( $product_id ) );
					//internal redirect name
					if ( $product_id != '' )
						$url = home_url() . '/a_out/' . rawurlencode( $san_title ) . '?pid=' . $product_id . '&sid=' . $shop_id . '&pt=' . $link_type . '&hash=' . ( ( md5( $product_id . $site_key ) ) );
					break;
			}
		}

		return $url;
	}

	private function build_external_link( $url, $title, $product_id, $listid, $templateid, $shopid, $link_type, $tracking_id = '' ) {
		if ( ATKPSettings::$open_window ) {
			$target = 'target="_blank"';
		} else {
			$target = '';
		}

		if ( $tracking_id != '' ) {
			//TODO: shop laden und replace mit trackingid aufrufen
			//TODO: Shop direkt von der übergeordneten methode übernehmen wenn vorhanden

			$url = $this->replace_tracking_code( $shopid, $url, $tracking_id );
		}

		$shoptext = '';

		if ( is_numeric( $shopid ) ) {
			$shoptext .= get_the_title( $shopid );

			$url = self::redirect_external_url( $shopid, $url, $product_id, $link_type );
		} else {
			$shoptext = $shopid;
		}


		$tracking       = '';
		$trackingparams = '\'\',\'\'';

		switch ( ATKPSettings::$linktracking ) {
			case 0:
				break;
			case 1:
			case 2:
			case 3:
			case 4:
			case 5:
				//universal tracking

				$linktypetext = '';

				switch ( (int) $link_type ) {
					default:
					case atkp_link_type::Link:
						$linktypetext = __( 'Link', ATKP_PLUGIN_PREFIX );
						break;
					case atkp_link_type::Offer:
						$linktypetext = __( 'Offer', ATKP_PLUGIN_PREFIX );
						break;
					case atkp_link_type::Cart:
						$linktypetext = __( 'Cart', ATKP_PLUGIN_PREFIX );
						break;
					case atkp_link_type::Customerreview:
						$linktypetext = __( 'Customer review', ATKP_PLUGIN_PREFIX );
						break;
					case atkp_link_type::Image:
						$linktypetext = __( 'Image', ATKP_PLUGIN_PREFIX );
						break;
				}


				if ( is_numeric( $templateid ) ) {
					$linktypetext .= ' (' . get_the_title( $templateid ) . ', ' . $shoptext . ')';
				} else if ( $templateid != '' ) {
					$linktypetext .= ' (' . $templateid . ', ' . $shoptext . ')';
				} else {
					$linktypetext = __( 'Textlink', ATKP_PLUGIN_PREFIX ) . ' (' . $shoptext . ')';
				}

				$listcaption = '';

				if ( is_numeric( $listid ) ) {
					$listcaption = get_the_title( $listid );
				}

				if ( $listid != '' && $listcaption == '' ) {
					$listcaption = $listid;
				}

				$productcaption = '';

				if ( is_numeric( $product_id ) ) {
					$productcaption = ATKPTools::get_post_setting( $product_id, ATKP_PRODUCT_POSTTYPE . '_title' );
				} else if ( $product_id != '' ) {
					$productcaption = $product_id;
				} else {
					$productcaption = '-';
				}

				$finalcaption = $productcaption;

				if ( $listcaption != '' ) {
					$finalcaption .= ' (' . $listcaption . ')';
				}

				$finalcaption = str_replace( '"', '_', $finalcaption );
				$finalcaption = str_replace( '\'', '_', $finalcaption );

				$trackingparams = '\'' . $linktypetext . '\', \'' . $finalcaption . '\'';

				switch ( ATKPSettings::$linktracking ) {
					case 1:
						$tracking = 'onclick="ga(\'send\', \'event\', \'' . $linktypetext . '\', \'click\', \'' . $finalcaption . '\');"';
						break;
					case 2:
						$tracking = 'onclick="_gaq.push([\'_trackEvent\', \'' . $linktypetext . '\', \'click\', \'' . $finalcaption . '\']);"';
						break;
					case 3:
						$tracking = 'onclick="gtag(\'event\', \'' . $linktypetext . '\', {  \'event_category\' : \'click\',\'event_label\' : \'' . $finalcaption . '\'});"';
						break;
					case 4:
						$tracking = 'onclick="_paq.push([\'trackEvent\', \'affiliate-toolkit Link\', \'' . $linktypetext . '\', \'' . $finalcaption . '\']);"';
						break;
					case 5:
						$tracking = 'onclick="umami.track("atkp_click", { name: \'' . $finalcaption . '\'});"';
						break;
				}


				//https://developers.google.com/analytics/devguides/collection/gtagjs/events

				break;
		}

		//atkp_open_link

		if ( ATKPSettings::$jslink ) {
			$link = ' style="cursor: pointer;" onclick="atkp_open_link(\'' . self::urlencode( $url ) . '\', \'_blank\', ' . ATKPSettings::$linktracking . ', ' . $trackingparams . ');" title="' . esc_attr( $title ) . '"';
		} else {
			$link = 'href="' . self::urlencode( $url ) . '" rel="' . esc_attr( self::get_link_rel()).'" ' . $target . ' ' . $tracking . ' title="' . esc_attr($title) . '"';
		}

		return $link;
	}

	public function get_variationname( $product, $glue = ', ' ) {
		if ( $product->variationname != '' && ! is_array( $product->variationname ) ) {
			$product->variationname = unserialize( $product->variationname );
		}

		$displaylist = array();
		foreach ( $product->variationname as $key => $value ) {
			array_push( $displaylist, htmlentities( $value ) );
		}

		return implode( $glue, $displaylist );
	}

	public function minifyvariations( $variations, $ignoreVariation = '' ) {
		if ( $ignoreVariation == '' || $variations == null || count( $variations ) == 0 ) {
			return $variations;
		}

		$filtered = array();
		$arr      = array();

		foreach ( $variations as $variation ) {
			$vararray = $variation->variationname;
			if ( $vararray != '' && ! is_array( $vararray ) ) {
				$vararray = unserialize( $vararray );
			}

			if ( isset( $vararray[ $ignoreVariation ] ) && count( $vararray ) > 1 ) {
				unset( $vararray[ $ignoreVariation ] );
			}

			$variation->variationname = $vararray;

			$newname = $this->get_variationname( $variation );

			if ( isset( $arr[ $newname ] ) ) {
				continue;
			}

			$arr[ $newname ] = $variation;

			array_push( $filtered, $variation );
		}

		return $filtered;
	}

	private static function urlencode( $u ) {
		//$u = str_replace('%', '%25', $u);

		return $u;
	}


	public function get_randomarray( $array, $count ) {
		if ( $array == null || count( $array ) <= $count ) {
			return $array;
		}

		$keys = array_rand( $array, $count );

		$newarray = array();

		foreach ( $keys as $key ) {
			array_push( $newarray, $array[ $key ] );
		}

		return $newarray;
	}

	private function roundRate( $rate ) {
		$rate = round( ( $rate * 2 ), 0 ) / 2;

		return $rate;
	}

	public function get_minpricedays( $product ) {
		$history = atkp_product_pricehistory::get_lastxdays_history( $product->productid, false, 30 );
		$history = array_reverse( $history );

		$foundhistory = null;
		$prevhistory  = null;
		$lasthistory  = count( $history ) == 0 ? null : $history[0];
		$i            = 0;
		foreach ( $history as $historyentry ) {
			if ( $historyentry->shopid == $product->shopid ) {
				if ( $product->salepricefloat == $historyentry->pricefloat ) {
					$foundhistory = $historyentry;
					$prevhistory  = $history[ $i + 1 ];
				}
			}
			$i ++;
		}
		if ( $foundhistory != null && $prevhistory == null ) {
			$prevhistory = $foundhistory;
		}
		if ( $foundhistory == null || $prevhistory == null ) {
			return '';
		}

		if ( $foundhistory->pricefloat > $prevhistory->pricefloat ) {
			return 'Preis ist von ' . $prevhistory->pricefloat . ' auf ' . $lasthistory->pricefloat . ' gestiegen.';
		} else if ( $foundhistory->pricefloat < $prevhistory->pricefloat ) {
			return 'Preis ist von ' . $prevhistory->pricefloat . ' auf ' . $lasthistory->pricefloat . ' gesunken.';
		} else {
			$date1 = new DateTime( $lasthistory->createdon );
			$date2 = new DateTime( "2010-07-09" );

			$difference = $date1->diff( $date2 );

			return 'Preis ist seit ' . $difference->d . ' Tagen konstant.';
		}

	}

	public function get_lastxdays_pricehistory( $product, $minifier_shops = false, $days = 30 ) {
		$history = atkp_product_pricehistory::get_lastxdays_history( $product->productid, $minifier_shops, $days );

		$shopids = array();
		$months  = array();


		for ( $i = 0; $i < $days; $i ++ ) {
			$timestamp = time();
			$tm        = 86400 * $i; // 60 * 60 * 24 = 86400 = 1 day in seconds
			$tm        = $timestamp - $tm;

			$months[] = date( "d.m.", $tm );
		}
		$months = array_reverse( $months );


		foreach ( $history as $historyentry ) {

			if ( ! in_array( $historyentry->shopid, $shopids ) ) {
				array_push( $shopids, $historyentry->shopid );
			}
		}

		foreach ( $history as $historyentry ) {
			$groupname    = $historyentry->groupname;
			$parts        = explode( '-', $groupname );
			$month_number = intval( $parts[1] );
			$day_number = intval( $parts[2] );

			$groupname = $day_number . "." . ( strlen( $month_number ) == 1 ? '0' . $month_number : $month_number ) . ".";

			if ( ! in_array( $groupname, $months ) ) {
				//array_push( $months, $groupname );
			}
			$historyentry->groupname = $groupname;
		}


		$labels = '';
		$data   = '';

		foreach ( $months as $month ) {
			if ( $labels != '' ) {
				$labels .= ',';
			}

			$labels .= '\'' . $month . '\'';
		}

		foreach ( $shopids as $shopid ) {
			$shop = atkp_shop::load( $shopid );
			if ( $shop == null ) {
				continue;
			}
			if ( $data != '' ) {
				$data .= ',';
			}

			if ( $shop->chartcolor != '' )
				$color = $shop->chartcolor;
			else
				$color    = '#' . $this->get_random_color();
			$shopname = $shop->get_title();

			if ( $minifier_shops ) {
				$shopname = __( 'All Shops', ATKP_PLUGIN_PREFIX );
			}

			$data     .= '{
			                label: \'' . esc_html( $shopname) . '\',
			                borderColor: \'' . $color . '\',
			                backgroundColor: \'' . $color . '\',
			                fill: false,
			                data: [';

			$priceval = 0;
			$tempdata = '';
			foreach ( $months as $month ) {

				foreach ( $history as $historyentry ) {

					if ( $historyentry->groupname == $month && $historyentry->shopid == $shopid ) {
						$priceval = $historyentry->pricefloat;
						break;
					}
				}

				$tempdata .= $priceval . ',';
			}

			if ( $priceval == 0 ) {
				$tempdata = '';

				foreach ( $history as $historyentry ) {
					if ( $historyentry->pricefloat > 0 && $historyentry->shopid == $shopid ) {

						$priceval = $historyentry->pricefloat;
					}
				}
				foreach ( $months as $month ) {
					$tempdata .= $priceval . ',';
				}

			}

			$data .= $tempdata;

			$data .= '],
			                yAxisID: \'y-axis-1\',
			            }';
		}

		$full = 'labels: [' . $labels . '],
            datasets: [' . $data . ']';

		return $full;
	}

	public function get_monthly_pricehistory( $product, $minifier_shops = false ) {

		$history = atkp_product_pricehistory::get_monthly_history( $product->productid, $minifier_shops );

		$shopids = array();
		$months  = array();


		for ( $i = 0; $i < 6; $i ++ ) {

			$monthtemp = date( "Y-m", strtotime( date( 'Y-m-01' ) . " -$i months" ) );

			$parts        = explode( '-', $monthtemp );
			$month_number = intval( $parts[1] );

			global $wp_locale;
			$var = $wp_locale->get_month( $month_number );

			$groupname = $var . " " . $parts[0];

			$months[] = $groupname;
		}
		$months = array_reverse( $months );

		foreach ( $history as $historyentry ) {

			if ( ! in_array( $historyentry->shopid, $shopids ) ) {
				array_push( $shopids, $historyentry->shopid );
			}
		}

		foreach ( $history as $historyentry ) {
			$groupname    = $historyentry->groupname;
			$parts        = explode( '-', $groupname );
			$month_number = intval( $parts[1] );

			global $wp_locale;
			$var = $wp_locale->get_month( $month_number );

			$groupname = $var . " " . $parts[0];

			if ( ! in_array( $groupname, $months ) ) {
				array_push( $months, $groupname );
			}
			$historyentry->groupname = $groupname;
		}


		$labels = '';
		$data   = '';

		foreach ( $months as $month ) {
			if ( $labels != '' ) {
				$labels .= ',';
			}

			$labels .= '\'' . $month . '\'';
		}

		foreach ( $shopids as $shopid ) {
			$shop = atkp_shop::load( $shopid );

			if ( $data != '' ) {
				$data .= ',';
			}

			if($shop->chartcolor != '')
				$color = $shop->chartcolor;
			else
				$color    = '#' . $this->get_random_color();
			$shopname = $shop->get_title();

			if ( $minifier_shops ) {
				$shopname = __( 'All Shops', ATKP_PLUGIN_PREFIX );
			}

			$data .= '{
			                label: \'' . esc_html( $shopname ) . '\',
			                borderColor: \'' . $color . '\',
			                backgroundColor: \'' . $color . '\',
			                fill: false,
			                data: [';

			$priceval = 0;
			foreach ( $months as $month ) {

				foreach ( $history as $historyentry ) {
					if ( $historyentry->groupname == $month && $historyentry->shopid == $shopid ) {

						$priceval = $historyentry->pricefloat;
					}
				}

				$data .= $priceval . ',';
			}

			$data .= '],
			                yAxisID: \'y-axis-1\',
			            }';
		}

		$full = 'labels: [' . $labels . '],
            datasets: [' . $data . ']';

		return $full;

	}

	private function random_color_part() {
		return str_pad( dechex( mt_rand( 0, 255 ) ), 2, '0', STR_PAD_LEFT );
	}

	public function get_random_color() {
		return $this->random_color_part() . $this->random_color_part() . $this->random_color_part();
	}
}


