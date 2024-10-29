<?php
/**
 * Created by PhpStorm.
 * User: Christof
 * Date: 27.12.2018
 * Time: 10:19
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class atkp_template_parameters {
	public $data = array();
	public $list = null;

	/**
	 * @param $content
	 * @param $cssContainerClass
	 * @param $cssElementClass
	 * @param $addtocart
	 * @param $hidedisclaimer
	 * @param $tracking_id
	 * @param $listid
	 * @param $templateid
	 * @param $offerstemplate
	 * @param $imagetemplate
	 *
	 * @return array
	 */
	public function buildShortcodeArray( $content = '', $cssContainerClass = '', $cssElementClass = '', $addtocart = '', $hidedisclaimer = 0, $tracking_id = '', $listid = '', $templateid = '', $offerstemplate = '', $imagetemplate = '' ) {
		$array = array();

		if ( $content != '' ) {
			$array['content'] = $content;
		}
		if ( $cssContainerClass != '' ) {
			$array['cssContainerClass'] = $cssContainerClass;
		}
		if ( $cssElementClass != '' ) {
			$array['cssElementClass'] = $cssElementClass;
		}
		if ( $addtocart != '' ) {
			$array['addtocart'] = $addtocart;
		}
		if ( $hidedisclaimer != 0 ) {
			$array['show_disclaimer'] = ! $hidedisclaimer;
		}
		if ( $tracking_id != '' ) {
			$array['tracking_id'] = $tracking_id;
		}
		if ( $listid != '' ) {
			$array['listid'] = $listid;
		}
		if ( $templateid != '' ) {
			$array['templateid'] = $templateid;
		}
		if ( $offerstemplate != '' ) {
			$array['moreoffers_template'] = $offerstemplate;
		}
		if ( $imagetemplate != '' ) {
			$array['imagetemplate'] = $imagetemplate;
		}

		return $array;
	}

	public function buildGlobalArray() {
		$array = array();
		//colors
		$array['box_background_color']    = atkp_options::$loader->get_box_background_color();
		$array['box_border_color']        = atkp_options::$loader->get_box_border_color();
		$array['box_text_color']          = atkp_options::$loader->get_box_text_color();
		$array['box_textlink_color']      = atkp_options::$loader->get_box_textlink_color();
		$array['box_textlink_hovercolor'] = atkp_options::$loader->get_box_textlink_hovercolor();

		$array['primbtn_background_color']      = atkp_options::$loader->get_primbtn_background_color();
		$array['primbtn_hoverbackground_color'] = atkp_options::$loader->get_primbtn_hoverbackground_color();
		$array['primbtn_foreground_color']      = atkp_options::$loader->get_primbtn_foreground_color();
		$array['primbtn_border_color']          = atkp_options::$loader->get_primbtn_border_color();

		$array['secbtn_background_color']      = atkp_options::$loader->get_secbtn_background_color();
		$array['secbtn_hoverbackground_color'] = atkp_options::$loader->get_secbtn_hoverbackground_color();
		$array['secbtn_foreground_color']      = atkp_options::$loader->get_secbtn_foreground_color();
		$array['secbtn_border_color']          = atkp_options::$loader->get_secbtn_border_color();


		$array['price_color']       = atkp_options::$loader->get_price_color();
		$array['listprice_color']   = atkp_options::$loader->get_listprice_color();
		$array['amountsaved_color'] = atkp_options::$loader->get_amountsaved_color();

		//display
		$array['showshopname']    = atkp_options::$loader->get_showshopname();
		$array['showprice']       = atkp_options::$loader->get_showprice();
		$array['showlistprice']   = atkp_options::$loader->get_showlistprice();
		$array['showstarrating']  = atkp_options::$loader->get_showstarrating();
		$array['linkrating']      = atkp_options::$loader->get_linkrating();
		$array['showbaseprice']   = atkp_options::$loader->get_showbaseprice();
		$array['hideemptystars']  = atkp_options::$loader->get_hideemptystars();
		$array['hideemptyrating'] = atkp_options::$loader->get_hideemptyrating();
		$array['hideprocontra']   = atkp_options::$loader->get_hideprocontra();

		$array['box_show_shadow'] = atkp_options::$loader->get_box_show_shadow();


		$array['boxcontent']        = atkp_options::$loader->get_box_description_content();
		$array['productpage_title'] = atkp_options::$loader->get_productpage_title();


		$array['secbtn_image']  = atkp_options::$loader->get_secbtn_image();
		$array['primbtn_image'] = atkp_options::$loader->get_primbtn_image();


		$array['linkimage']         = atkp_options::$loader->get_linkimage();
		$array['showpricediscount'] = atkp_options::$loader->get_showpricediscount();
		$array['showrating']        = atkp_options::$loader->get_showrating();
		$array['mark_links']        = atkp_options::$loader->get_mark_links();

		//radius
		$array['box_radius'] = atkp_options::$loader->get_box_radius();
		$array['btn_radius'] = atkp_options::$loader->get_button_radius();

		$array['box_badge_color'] = atkp_options::$loader->get_box_badge_color();;
		$array['feature_count']      = atkp_options::$loader->get_feature_count();
		$array['description_length'] = atkp_options::$loader->get_description_length();
		$array['short_title_length'] = atkp_options::$loader->get_short_title_length();

		//texts
		$array['show_disclaimer'] = atkp_options::$loader->get_show_disclaimer();
		$array['disclaimer_text'] = atkp_options::$loader->get_disclaimer_text();
		$array['show_priceinfo']  = atkp_options::$loader->get_show_priceinfo();
		$array['priceinfo_text']  = atkp_options::$loader->get_priceinfo_text();

		$array['affiliatechar'] = atkp_options::$loader->get_affiliatechar();

		//more settings

		$array['primbtn_size']   = atkp_options::$loader->get_primbtn_size();
		$array['secbtn_size']    = atkp_options::$loader->get_secbtn_size();
		$array['show_primelogo'] = atkp_options::$loader->get_showprimelogo();
		$array['linkprime']      = atkp_options::$loader->get_linkprime();


		$array['show_moreoffers']             = atkp_options::$loader->get_show_moreoffers();
		$array['moreoffers_count']            = atkp_options::$loader->get_moreoffers_count();
		$array['moreoffers_title']            = atkp_options::$loader->get_moreoffers_title();
		$array['moreoffers_template']         = atkp_options::$loader->get_moreoffers_template();
		$array['moreoffers_includemainoffer'] = atkp_options::$loader->get_moreoffers_includemainoffer();


		$array['disablediscounts'] = atkp_options::$loader->get_disablediscounts();

		return $array;
	}

	public function buildTemplateArray( $templateid ) {
		$array = array();

		$ownstyles = ATKPTools::get_post_setting( $templateid, ATKP_TEMPLATE_POSTTYPE . '_custom_styles' );

		if ( $ownstyles ) {
			//Load own styles and override it

			$array['custom_styles']           = $ownstyles;
			$array['box_background_color']    = ATKPTools::get_post_setting( $templateid, ATKP_TEMPLATE_POSTTYPE . '_box_background_color' );
			$array['box_border_color']        = ATKPTools::get_post_setting( $templateid, ATKP_TEMPLATE_POSTTYPE . '_box_border_color' );
			$array['box_text_color']          = ATKPTools::get_post_setting( $templateid, ATKP_TEMPLATE_POSTTYPE . '_box_text_color' );
			$array['box_textlink_color']      = ATKPTools::get_post_setting( $templateid, ATKP_TEMPLATE_POSTTYPE . '_box_textlink_color' );
			$array['box_textlink_hovercolor'] = ATKPTools::get_post_setting( $templateid, ATKP_TEMPLATE_POSTTYPE . '_box_textlink_hovercolor' );

			$array['primbtn_background_color']      = ATKPTools::get_post_setting( $templateid, ATKP_TEMPLATE_POSTTYPE . '_primbtn_background_color' );
			$array['primbtn_hoverbackground_color'] = ATKPTools::get_post_setting( $templateid, ATKP_TEMPLATE_POSTTYPE . '_primbtn_hoverbackground_color' );
			$array['primbtn_foreground_color']      = ATKPTools::get_post_setting( $templateid, ATKP_TEMPLATE_POSTTYPE . '_primbtn_foreground_color' );
			$array['primbtn_border_color']          = ATKPTools::get_post_setting( $templateid, ATKP_TEMPLATE_POSTTYPE . '_primbtn_border_color' );

			$array['secbtn_background_color']      = ATKPTools::get_post_setting( $templateid, ATKP_TEMPLATE_POSTTYPE . '_secbtn_background_color' );
			$array['secbtn_hoverbackground_color'] = ATKPTools::get_post_setting( $templateid, ATKP_TEMPLATE_POSTTYPE . '_secbtn_hoverbackground_color' );
			$array['secbtn_foreground_color']      = ATKPTools::get_post_setting( $templateid, ATKP_TEMPLATE_POSTTYPE . '_secbtn_foreground_color' );
			$array['secbtn_border_color']          = ATKPTools::get_post_setting( $templateid, ATKP_TEMPLATE_POSTTYPE . '_secbtn_border_color' );


			$array['price_color']       = ATKPTools::get_post_setting( $templateid, ATKP_TEMPLATE_POSTTYPE . '_price_color' );
			$array['listprice_color']   = ATKPTools::get_post_setting( $templateid, ATKP_TEMPLATE_POSTTYPE . '_listprice_color' );
			$array['amountsaved_color'] = ATKPTools::get_post_setting( $templateid, ATKP_TEMPLATE_POSTTYPE . '_amountsaved_color' );

			//display
			$array['showshopname']    = ATKPTools::get_post_setting( $templateid, ATKP_TEMPLATE_POSTTYPE . '_showshopname' );
			$array['showprice']       = ATKPTools::get_post_setting( $templateid, ATKP_TEMPLATE_POSTTYPE . '_showprice' );
			$array['showlistprice']   = ATKPTools::get_post_setting( $templateid, ATKP_TEMPLATE_POSTTYPE . '_showlistprice' );
			$array['showstarrating']  = ATKPTools::get_post_setting( $templateid, ATKP_TEMPLATE_POSTTYPE . '_showstarrating' );
			$array['linkrating']      = ATKPTools::get_post_setting( $templateid, ATKP_TEMPLATE_POSTTYPE . '_linkrating' );
			$array['showbaseprice']   = ATKPTools::get_post_setting( $templateid, ATKP_TEMPLATE_POSTTYPE . '_showbaseprice' );
			$array['hideemptystars']  = ATKPTools::get_post_setting( $templateid, ATKP_TEMPLATE_POSTTYPE . '_hideemptystars' );
			$array['hideemptyrating'] = ATKPTools::get_post_setting( $templateid, ATKP_TEMPLATE_POSTTYPE . '_hideemptyrating' );
			$array['hideprocontra']   = ATKPTools::get_post_setting( $templateid, ATKP_TEMPLATE_POSTTYPE . '_hideprocontra' );

			$array['box_show_shadow'] = ATKPTools::get_post_setting( $templateid, ATKP_TEMPLATE_POSTTYPE . '_box_show_shadow' );


			$array['boxcontent']        = ATKPTools::get_post_setting( $templateid, ATKP_TEMPLATE_POSTTYPE . '_box_description_content' );
			$array['productpage_title'] = ATKPTools::get_post_setting( $templateid, ATKP_TEMPLATE_POSTTYPE . '_productpage_title' );


			$array['secbtn_image']  = ATKPTools::get_post_setting( $templateid, ATKP_TEMPLATE_POSTTYPE . '_secbtn_image' );
			$array['primbtn_image'] = ATKPTools::get_post_setting( $templateid, ATKP_TEMPLATE_POSTTYPE . '_primbtn_image' );


			$array['linkimage']         = ATKPTools::get_post_setting( $templateid, ATKP_TEMPLATE_POSTTYPE . '_linkimage' );
			$array['showpricediscount'] = ATKPTools::get_post_setting( $templateid, ATKP_TEMPLATE_POSTTYPE . '_showpricediscount' );
			$array['showrating']        = ATKPTools::get_post_setting( $templateid, ATKP_TEMPLATE_POSTTYPE . '_showrating' );
			$array['mark_links']        = ATKPTools::get_post_setting( $templateid, ATKP_TEMPLATE_POSTTYPE . '_mark_links' );

			//radius
			$array['box_radius'] = ATKPTools::get_post_setting( $templateid, ATKP_TEMPLATE_POSTTYPE . '_box_radius' );
			$array['btn_radius'] = ATKPTools::get_post_setting( $templateid, ATKP_TEMPLATE_POSTTYPE . '_button_radius' );

			$array['box_badge_color'] = ATKPTools::get_post_setting( $templateid, ATKP_TEMPLATE_POSTTYPE . '_box_badge_color' );;
			$array['feature_count']      = ATKPTools::get_post_setting( $templateid, ATKP_TEMPLATE_POSTTYPE . '_feature_count' );
			$array['description_length'] = ATKPTools::get_post_setting( $templateid, ATKP_TEMPLATE_POSTTYPE . '_description_length' );
			$array['short_title_length'] = ATKPTools::get_post_setting( $templateid, ATKP_TEMPLATE_POSTTYPE . '_short_title_length' );

			//texts
			$array['show_disclaimer'] = ATKPTools::get_post_setting( $templateid, ATKP_TEMPLATE_POSTTYPE . '_show_disclaimer' );
			$array['disclaimer_text'] = ATKPTools::get_post_setting( $templateid, ATKP_TEMPLATE_POSTTYPE . '_disclaimer_text' );
			$array['show_priceinfo']  = ATKPTools::get_post_setting( $templateid, ATKP_TEMPLATE_POSTTYPE . '_show_priceinfo' );
			$array['priceinfo_text']  = ATKPTools::get_post_setting( $templateid, ATKP_TEMPLATE_POSTTYPE . '_priceinfo_text' );

			$array['affiliatechar'] = ATKPTools::get_post_setting( $templateid, ATKP_TEMPLATE_POSTTYPE . '_affiliatechar' );

			//more settings

			$array['primbtn_size']   = ATKPTools::get_post_setting( $templateid, ATKP_TEMPLATE_POSTTYPE . '_primbtn_size' );
			$array['secbtn_size']    = ATKPTools::get_post_setting( $templateid, ATKP_TEMPLATE_POSTTYPE . '_secbtn_size' );
			$array['show_primelogo'] = ATKPTools::get_post_setting( $templateid, ATKP_TEMPLATE_POSTTYPE . '_showprimelogo' );
			$array['linkprime']      = ATKPTools::get_post_setting( $templateid, ATKP_TEMPLATE_POSTTYPE . '_linkprime' );


			$array['show_moreoffers']             = ATKPTools::get_post_setting( $templateid, ATKP_TEMPLATE_POSTTYPE . '_show_moreoffers' );
			$array['moreoffers_count']            = ATKPTools::get_post_setting( $templateid, ATKP_TEMPLATE_POSTTYPE . '_moreoffers_count' );
			$array['moreoffers_title']            = ATKPTools::get_post_setting( $templateid, ATKP_TEMPLATE_POSTTYPE . '_moreoffers_title' );
			$array['moreoffers_template']         = ATKPTools::get_post_setting( $templateid, ATKP_TEMPLATE_POSTTYPE . '_moreoffers_template' );
			$array['moreoffers_includemainoffer'] = ATKPTools::get_post_setting( $templateid, ATKP_TEMPLATE_POSTTYPE . '_moreoffers_includemainoffer' );

			$array['disablediscounts'] = ATKPTools::get_post_setting( $templateid, ATKP_TEMPLATE_POSTTYPE . '_disablediscounts' );
		}

		return $array;
	}

	public function buildTemplateParameters( $template_id, $shortcode_parameters ) {
		$this->templateid = $template_id;
		//set global settings
		$global_parameters = $this->buildGlobalArray();

		foreach ( $global_parameters as $global_parameter => $value ) {
			$this->data[ $global_parameter ] = $value ?? $this->data[ $global_parameter ];
		}

		//set template settings

		if ( is_numeric( $this->templateid ) && $this->templateid > 0 ) {
			$template_parameters = $this->buildTemplateArray( intval( $this->templateid ) );

			foreach ( $template_parameters as $template_parameter => $value ) {
				$this->data[ $template_parameter ] = $value ?? $this->data[ $template_parameter ];
			}
		}

		//set shortcode settings

		foreach ( $shortcode_parameters as $shortcode_parameter => $value ) {
			$this->data[ $shortcode_parameter ] = $value ?? $this->data[ $shortcode_parameter ];
		}

		if ( is_numeric( $this->listid ) && $this->listid > 0 ) {
			$this->list = atkp_list::load( intval( $this->listid ) );
		}

		if ( $this->moreoffers_template == '' ) {
			$this->moreoffers_template = 'moreoffers';
		}

	}

	public function __construct() {
		$this->content           = '';
		$this->cssContainerClass = '';
		$this->cssElementClass   = '';
		$this->addtocart         = '';
		$this->hidedisclaimer    = false;
		$this->trackingid        = '';
		$this->listid            = 0;
		$this->templateid        = 0;
		$this->offerstemplate    = 'moreoffers';
		$this->imagetemplate     = '';
		$this->list              = null;

	}

	private function get_option( $name, $default = null ) {
		if ( isset( $this->data[ $name ] ) ) {
			return $this->data[ $name ];
		} else {
			return $default;
		}
	}

	//texts


	public function get_custom_styles() {
		return $this->get_option( 'custom_styles', false );
	}

	public function get_content() {
		return $this->get_option( 'content', '' );
	}

	public function get_css_container_class() {
		return $this->get_option( 'cssContainerClass', '' ) . ( $this->templateid != '' ? ' atkp-template-' . $this->templateid : '' );
	}

	public function get_css_element_class() {
		return $this->get_option( 'cssElementClass', '' );
	}


	public function get_box_description_content() {
		return $this->get_option( 'boxcontent', 1 );
	}

	public function get_productpage_title() {
		return $this->get_option( 'productpage_title', __( 'View Product', ATKP_PLUGIN_PREFIX ) );
	}


	public function get_show_disclaimer() {
		return $this->get_option( 'show_disclaimer', true );
	}

	public function get_disclaimer_text() {
		return $this->get_option( 'disclaimer_text', '' );
	}

	public function get_show_priceinfo() {
		return $this->get_option( 'show_priceinfo', true );
	}

	public function get_priceinfo_text() {
		return $this->get_option( 'priceinfo_text', '' );
	}

	public function get_affiliatechar() {
		return $this->get_option( 'affiliatechar', '*' );
	}

	//display

	public function get_showshopname() {
		return $this->get_option( 'showshopname', true );
	}

	public function get_showlistprice() {
		return $this->get_option( 'showlistprice', true );
	}

	public function get_showprice() {
		return $this->get_option( 'showprice', true );
	}

	public function get_showstarrating() {
		return $this->get_option( 'showstarrating', true );
	}

	public function get_linkrating() {
		return $this->get_option( 'linkrating', false );
	}

	public function get_showbaseprice() {
		return $this->get_option( 'showbaseprice', true );
	}

	public function get_hideemptystars() {
		return $this->get_option( 'hideemptystars', false );
	}

	public function get_hideemptyrating() {
		return $this->get_option( 'hideemptyrating', false );
	}

	public function get_hideprocontra() {
		return $this->get_option( 'hideprocontra', false );
	}

	public function get_linkimage() {
		return $this->get_option( 'linkimage', false );
	}

	public function get_showpricediscount() {
		return $this->get_option( 'showpricediscount', true );
	}

	public function get_showrating() {
		return $this->get_option( 'showstarrating', true );
		//return $this->get_option( 'showrating', false );
	}

	public function get_mark_links() {
		return $this->get_option( 'mark_links', true );
	}

	public function get_secbtn_image() {
		return $this->get_option( 'secbtn_image', '' );
	}

	public function get_primbtn_image() {
		return $this->get_option( 'primbtn_image', '' );
	}

	//offers

	public function get_show_moreoffers() {
		return $this->get_option( 'show_moreoffers', false );
	}

	public function get_moreoffers_includemainoffer() {
		return $this->get_option( 'moreoffers_includemainoffer', false );
	}

	public function get_moreoffers_count() {
		return $this->get_option( 'moreoffers_count', 0 );
	}

	public function get_moreoffers_title() {
		return $this->get_option( 'moreoffers_title', __( 'Additional offers »', ATKP_PLUGIN_PREFIX ) );
	}

	public function get_moreoffers_template() {
		return $this->get_option( 'moreoffers_template', 'moreoffers' );
	}

	//radius


	public function get_box_border_radius() {
		return $this->get_option( 'box_radius', 3 );
	}

	public function get_btn_radius() {
		return $this->get_option( 'btn_radius', 3 );
	}

	//color

	public function get_box_background_color() {
		return $this->get_option( 'box_background_color', '#ffff' );
	}

	public function get_box_show_shadow() {
		return $this->get_option( 'box_show_shadow', false );
	}

	public function get_box_border_color() {
		return $this->get_option( 'box_border_color', '#ececec' );
	}

	public function get_box_text_color() {
		return $this->get_option( 'box_text_color', '#111' );
	}

	public function get_box_textlink_color() {
		return $this->get_option( 'box_textlink_color', '#2271b1' );
	}

	public function get_box_textlink_hovercolor() {
		return $this->get_option( 'box_textlink_hovercolor', '#2271b1' );
	}

	public function get_dropdown_textlink_color() {
		return $this->get_option( 'dropdown_textlink_color', '#2271b1' );
	}

	public function get_dropdown_textlink_hovercolor() {
		return $this->get_option( 'dropdown_textlink_hovercolor', '#2271b1' );
	}

	public function get_primbtn_background_color() {
		return $this->get_option( 'primbtn_background_color', '#f0c14b' );
	}

	public function get_primbtn_hoverbackground_color() {
		return $this->get_option( 'primbtn_hoverbackground_color', '#f7dfa5' );
	}

	public function get_primbtn_foreground_color() {
		return $this->get_option( 'primbtn_foreground_color', '#111' );
	}

	public function get_primbtn_border_color() {
		return $this->get_option( 'primbtn_border_color', '#f0c14b' );
	}

	public function get_listprice_color() {
		return $this->get_option( 'listprice_color', '#808080' );
	}

	public function get_amountsaved_color() {
		return $this->get_option( 'amountsaved_color', '#8b0000' );
	}

	public function get_price_color() {
		return $this->get_option( 'price_color', '#00000' );
	}

	public function get_box_badge_color() {
		return $this->get_option( 'box_badge_color', '#E47911' );
	}

	public function get_secbtn_background_color() {
		return $this->get_option( 'secbtn_background_color', '#f0c14b' );
	}

	public function get_secbtn_hoverbackground_color() {
		return $this->get_option( 'secbtn_hoverbackground_color', '#f7dfa5' );
	}

	public function get_secbtn_foreground_color() {
		return $this->get_option( 'secbtn_foreground_color', '#333333' );
	}

	public function get_secbtn_border_color() {
		return $this->get_option( 'secbtn_border_color', '#f0c14b' );
	}

	//size
	public function get_primbtn_size() {
		return $this->get_option( 'primbtn_size', 'normal' );
	}

	public function get_secbtn_size() {
		return $this->get_option( 'secbtn_size', 'normal' );
	}

	public function get_feature_count() {
		return $this->get_option( 'feature_count', 0 );
	}

	public function get_description_length() {
		return $this->get_option( 'description_length', 0 );
	}

	public function get_short_title_length() {
		return $this->get_option( 'short_title_length', 0 );
	}

	//amazon

	public function get_showprimelogo() {
		return $this->get_option( 'show_primelogo', true );
	}

	public function get_linkprime() {
		return $this->get_option( 'linkprime', false );
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