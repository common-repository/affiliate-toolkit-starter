<?php
/**
 * Created by PhpStorm.
 * User: Christof
 * Date: 08.12.2018
 * Time: 14:23
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class atkp_options {

	/* @var $loader atkp_options */
	public static $loader = null;
	private $loaded_values;

	public $edd_plugin_data;


	public function __construct() {
		$this->loaded_values  = array();
		atkp_options::$loader = $this;
		$edd_plugin_data      = array();
	}

	/**
	 * Get the value of a settings field
	 *
	 * @param string $option settings field name
	 * @param string $section the section name this field belongs to
	 * @param string $default default text if it's not found
	 *
	 * @return mixed
	 */
	private function get_option_pfx( $option, $default = false ) {

		$option_value = get_option( ATKP_PLUGIN_PREFIX . $option, $default );

		return $option_value;
	}

	private function get_cachedoption( $name, $default ) {
		if ( isset( $this->loaded_values[ $name ] ) ) {
			return $this->loaded_values[ $name ];
		} else {
			$optionvalue                  = $this->get_option_pfx( $name, $default );
			$this->loaded_values[ $name ] = $optionvalue;

			return $optionvalue;
		}
	}

	public function set_option( $name, $value ) {
		$this->loaded_values[ $name ] = $value;
		update_option( ATKP_PLUGIN_PREFIX . $name, $value );

	}

	public function get_sitekey() {
		$sitekey = $this->get_cachedoption( '_sitekey', '' );
		if ( $sitekey == '' ) {
			$sitekey = md5( microtime( true ) . AUTH_SALT );
			update_option( ATKP_PLUGIN_PREFIX . '_sitekey', $sitekey );
		}

		return $sitekey;
	}


	public function get_affiliatechar() {
		return $this->get_cachedoption( '_affiliatechar', '*' );
	}

	public function get_access_mark_links() {
		return $this->get_cachedoption( '_mark_links', 1 );
	}

	//region modules
	public function get_customfields_module_enabled() {
		return $this->get_cachedoption( '_customfields_module_enabled', true );
	}

	public function get_productpages_module_enabled() {
		return $this->get_cachedoption( '_productpages_module_enabled', true );
	}



	public function get_debug_module_enabled() {
		return $this->get_cachedoption( '_debug_module_enabled', true );
	}

	public function get_stats_module_enabled() {
		return $this->get_cachedoption( '_stats_module_enabled', false );
	}


	public function get_floatingbar_module_enabled() {
		return $this->get_cachedoption( '_floatingbar_module_enabled', true );
	}

	public function get_outputashtml() {
		return $this->get_cachedoption( '_outputashtml', false );
	}

	public function get_ajax_loading_enabled() {
		return $this->get_cachedoption( '_enable_ajax_loading', false );
	}

	public function get_ajax_handler_enabled() {
		return $this->get_cachedoption( '_enable_ajax_handler', false );
	}


	public function get_setproductstatus_enabled() {
		return $this->get_cachedoption( '_product_pricenull', false );
	}

	public function get_acfenabled() {
		return class_exists( 'acf' ) && $this->get_cachedoption( '_activateacf', false );
	}

	public function get_licensekey() {
		return $this->get_cachedoption( '_license_key', '' );

	}

	public function get_licensestatus() {
		return $this->get_cachedoption( '_license_status', 'invalid' );

	}

	public function get_licensekey_module( $moduleid ) {
		return $this->get_cachedoption( '_license_key_' . $moduleid, '' );

	}

	public function get_licensestatus_module( $moduleid ) {
		return $this->get_cachedoption( '_license_status_' . $moduleid, 'none' );

	}

	public function get_licenseowner_module( $moduleid ) {
		return $this->get_cachedoption( '_license_owner_' . $moduleid, '' );

	}



	public function get_licensemessage_module( $moduleid ) {
		return $this->get_cachedoption( '_license_message_' . $moduleid, '' );

	}


	public function set_licensekey_module( $moduleid, $value ) {
		$this->set_option( '_license_key_' . $moduleid, $value );

	}

	public function set_licensestatus_module( $moduleid, $value ) {
		$this->set_option( '_license_status_' . $moduleid, $value );

	}

	public function set_licenseowner_module( $moduleid, $value ) {
		$this->set_option( '_license_owner_' . $moduleid, $value );

	}



	public function set_licensemessage_module( $moduleid, $value ) {
		$this->set_option( '_license_message_' . $moduleid, $value );

	}

	public function get_licensemessage() {
		return $this->get_cachedoption( '_license_message', '' );

	}

	public function get_licenseproductid() {
		return $this->get_cachedoption( '_license_productid', 13 );

	}

	public function get_showprimelogo() {
		return $this->get_cachedoption( '_show_primelogo', true );

	}


	public function get_showshopname() {
		return $this->get_cachedoption( '_showshopname', true );

	}


	//endregion


	public function get_woo_mode() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return '';
		}
		if ( ! defined( 'ATKP_WOOCOMMERCE_PLUGIN_FILE' ) ) {
			return '';
		}

		return $this->get_cachedoption( '_woo_mode', '' );
	}

	public function get_woo_imagegallerymode() {
		return $this->get_cachedoption( '_woo_imagegallery', 0 );
	}

	public function get_woo_importimagemode() {
		return $this->get_cachedoption( '_woo_importimage', 1 );
	}

	public function get_woo_ean_field() {
		return $this->get_cachedoption( '_woo_ean_field', '' );
	}

	public function get_woo_keytype() {
		return $this->get_cachedoption( '_woo_keytype', 'ean' );
	}


	public function get_cache_duration() {
		return $this->get_cachedoption( '_cache_duration', 1440 );
	}

	public function get_check_enabled() {
		return $this->get_cachedoption( '_check_enabled', false );
	}

	public function get_notification_interval() {
		return $this->get_cachedoption( '_notification_interval', 4320 );
	}

	public function get_access_csv_intervall() {
		return $this->get_cachedoption( '_access_csv_intervall', 1440 );
	}

	public function get_access_awin_intervall() {
		return $this->get_cachedoption( '_access_awin_intervall', 1440 );
	}

	public function get_queue_clean_days() {
		$xx = $this->get_cachedoption( '_queue_clean_days', '7' );

		if ( $xx <= 0 ) {
			$xx = 7;
		}

		return $xx;
	}

	public function get_queue_package_size() {
		$size = $this->get_cachedoption( '_queue_package_size', '2000' );

		if ( $size <= 0 ) {
			$size = 2000;
		}

		return $size;
	}

	/**
	 * OBSOLETE
	 * @return string
	 */
	public function get_rocketscrape_key() {

		return '';
	}


	public function get_crontype() {
		return $this->get_cachedoption( '_crontype', 'wpcron' );
	}


	public function get_cron_lastclean() {
		return $this->get_cachedoption( '_cron_lastclean', '' );
	}

	public function get_cron_offer_lastimport() {
		return $this->get_cachedoption( '_cron_offer_lastimport', '' );
	}

	public function get_cron_product_lastexport() {
		return $this->get_cachedoption( '_cron_product_lastexport', '' );
	}

	public function get_cron_product_lastimport() {
		return $this->get_cachedoption( '_cron_product_lastimport', '' );
	}

	public function get_cron_csv_lastimport() {
		return $this->get_cachedoption( '_cron_csv_lastimport', '' );
	}

	public function get_cron_csv_lastimportfinished() {
		return $this->get_cachedoption( '_cron_csv_lastimportfinished', '' );
	}

	public function get_cron_list_lastimport() {
		return $this->get_cachedoption( '_cron_list_lastimport', '' );
	}

	public function get_cron_lastlicensecheck() {
		return $this->get_cachedoption( '_cron_lastlicensecheck', '' );

	}

	public function get_cron_lastdatacheck() {
		return $this->get_cachedoption( '_cron_lastdatacheck', '' );
	}


	public function get_cron_from() {
		return $this->get_cachedoption( '_cron_from', '' );
	}

	public function get_cron_to() {
		return $this->get_cachedoption( '_cron_to', '' );
	}

	public function get_product_importimage() {
		return $this->get_cachedoption( '_product_importimage', false );
	}

	public function get_product_imagetype() {
		return $this->get_cachedoption( '_product_imagetype', 0 );
	}

	public function get_defaultproductstate() {
		return $this->get_cachedoption( '_defaultproductstate', 'draft' );
	}

	public function get_redirectsearchresult() {
		return $this->get_cachedoption( '_redirectsearchresult', 0 );
	}

	public function get_redirectsearchresulttarget() {
		return $this->get_cachedoption( '_redirectsearchresulttarget', '' );
	}

	public function get_additional_shortcode_button() {
		return $this->get_cachedoption( '_additional_shortcode_button', 0 );
	}

	public function get_custom_posttypes() {
		return $this->get_cachedoption( '_custom_posttypes', null );
	}


	public function get_product_commentenabled() {
		return $this->get_cachedoption( '_product_commentenabled', false );
	}

	public function get_product_slug() {
		return $this->get_cachedoption( '_product_slug', 'product' );
	}

	public function get_product_hideslug() {
		return $this->get_cachedoption( '_product_hideslug', false );
	}

	public function get_product_template() {
		return $this->get_cachedoption( '_product_template', '' );
	}

	public function get_product_archivetemplate() {
		return $this->get_cachedoption( '_product_archivetemplate', '' );
	}

	public function get_product_category_taxonomy() {
		return $this->get_cachedoption( '_product_category_taxonomy', strtolower( __( 'productcategory', ATKP_PLUGIN_PREFIX ) ) );
	}

	public function get_product_importimagemode() {
		return $this->get_cachedoption( '_product_importimage', 0 );
	}

	public function get_hide_error_message() {
		return $this->get_cachedoption( '_hideerrormessages', true );
	}

	public function get_show_floatingbar_productpage() {
		return $this->get_cachedoption( '_show_floatingbar_productpage', false );
	}

	public function get_show_floatingbar_mainproduct() {
		return $this->get_cachedoption( '_show_floatingbar_mainproduct', false );
	}

	public function get_show_floatingbar_woocommerce() {
		return $this->get_cachedoption( '_show_floatingbar_woocommerce', false );
	}

	public function get_floatingbar_template() {
		return $this->get_cachedoption( '_floatingbar_template', '' );
	}

	public function get_hide_floatingbar_mobile() {
		return $this->get_cachedoption( '_hide_floatingbar_mobile', false );
	}

	public function get_floatingbar_position() {
		return $this->get_cachedoption( '_floatingbar_position', 0 );
	}

	public function get_disablestyles() {
		return $this->get_cachedoption( '_disablestyles', false );
	}

	public function get_disablejs() {
		return $this->get_cachedoption( '_disable_js', false );
	}

	public function get_disableselect2_backend() {
		return $this->get_cachedoption( '_disableselect2', false );
	}

	public function get_disableselect2_widget() {
		return $this->get_cachedoption( '_disableselect2_widget', false );
	}




	public function get_disablediscounts() {
		return $this->get_cachedoption( '_disablediscounts', false );
	}


	//region styles
	public function get_buttonstyle() {
		return $this->get_cachedoption( '_buttonstyle', 1 );
	}

	public function get_btn_color_background_top() {
		return $this->get_cachedoption( '_btn_color_background_top', '#FFB22A' );
	}

	public function get_btn_color_background_bottom() {
		return $this->get_cachedoption( '_btn_color_background_bottom', '#ffab23' );
	}

	public function get_btn_color_foreground() {
		return $this->get_cachedoption( '_btn_color_foreground', '#333333' );
	}

	public function get_btn_color_border() {
		return $this->get_cachedoption( '_btn_color_border', '#ffaa22' );
	}

	public function get_btn_color_background_top_2() {
		return $this->get_cachedoption( '_btn_color_background_top_2', '#FFB22A' );
	}

	public function get_btn_color_background_bottom_2() {
		return $this->get_cachedoption( '_btn_color_background_bottom_2', '#ffab23' );
	}

	public function get_btn_color_foreground_2() {
		return $this->get_cachedoption( '_btn_color_foreground_2', '#333333' );
	}

	public function get_btn_color_border_2() {
		return $this->get_cachedoption( '_btn_color_border_2', '#ffaa22' );
	}

	public function get_boxstyle() {
		return $this->get_cachedoption( '_boxstyle', 1 );
	}

	public function get_predicate1_color() {
		return $this->get_cachedoption( '_predicate1_color', '' );
	}

	public function get_predicate1_highlightcolor() {
		return $this->get_cachedoption( '_predicate1_highlightcolor', '' );
	}

	public function get_predicate2_color() {
		return $this->get_cachedoption( '_predicate2_color', '' );
	}

	public function get_predicate2_highlightcolor() {
		return $this->get_cachedoption( '_predicate2_highlightcolor', '' );
	}

	public function get_predicate3_color() {
		return $this->get_cachedoption( '_predicate3_color', '' );
	}

	public function get_predicate3_highlightcolor() {
		return $this->get_cachedoption( '_predicate3_highlightcolor', '' );
	}

	public function get_color_background() {
		return $this->get_cachedoption( '_color_background', '#ffff' );
	}

	public function get_color_border() {
		return $this->get_cachedoption( '_color_border', '#ececec' );
	}

	public function get_color_text() {
		return $this->get_cachedoption( '_color_text', '#111' );
	}

	public function get_color_textlink() {
		return $this->get_cachedoption( '_color_textlink', '#2271b1' );
	}

	public function get_btn_color_background() {
		return $this->get_cachedoption( '_btn_color_background_top', '#f0c14b' );
	}

	public function get_btn_color_hover() {
		return $this->get_cachedoption( '_btn_color_hover_top', '#f7dfa5' );
	}

	public function get_secondbtn_color_background() {
		return $this->get_cachedoption( '_btn_color_background_top_2', '#f0c14b' );
	}

	public function get_secondbtn_color_background_hover() {
		return $this->get_cachedoption( '_btn_color_background_bottom_2', '#f7dfa5' );
	}


	public function get_predicate1_text() {
		return $this->get_cachedoption( '_predicate1_text', __( 'Winner', ATKP_PLUGIN_PREFIX ) );
	}

	public function get_predicate2_text() {
		return $this->get_cachedoption( '_predicate2_text', __( 'Price Tip', ATKP_PLUGIN_PREFIX ) );
	}

	public function get_predicate3_text() {
		return $this->get_cachedoption( '_predicate3_text', __( 'Custom', ATKP_PLUGIN_PREFIX ) );
	}

	public function get_test_score1_text() {
		return $this->get_cachedoption( '_test_score1_text', __( 'Very good', ATKP_PLUGIN_PREFIX ) );
	}

	public function get_test_score2_text() {
		return $this->get_cachedoption( '_test_score2_text', __( 'Good', ATKP_PLUGIN_PREFIX ) );
	}

	public function get_test_score3_text() {
		return $this->get_cachedoption( '_test_score3_text', __( 'Satisfying', ATKP_PLUGIN_PREFIX ) );
	}

	public function get_test_score4_text() {
		return $this->get_cachedoption( '_test_score4_text', __( 'Enough', ATKP_PLUGIN_PREFIX ) );
	}

	public function get_test_score5_text() {
		return $this->get_cachedoption( '_test_score5_text', __( 'Insufficient', ATKP_PLUGIN_PREFIX ) );
	}

	//endregion

	public function get_disable_sponsored_attribute() {
		return $this->get_cachedoption( '_disable_sponsored_attribute', false );
	}

	public function get_show_priceinfo() {
		return $this->get_cachedoption( '_show_priceinfo', true );
	}

	public function get_show_disclaimer() {
		return $this->get_cachedoption( '_show_disclaimer', true );
	}

	public function get_show_credits() {
		return $this->get_cachedoption( '_show_credits', true );
	}

	public function get_credits_ref() {
		return $this->get_cachedoption( '_credits_ref', '' );
	}

	public function get_disclaimer_text() {
		return $this->get_cachedoption( '_disclaimer_text', stripslashes( __( 'Last updated on %refresh_date% at %refresh_time% - Image source: Amazon Affiliate Program. All statements without guarantee.', ATKP_PLUGIN_PREFIX ) ) );
	}

	public function get_priceinfo_text() {
		return $this->get_cachedoption( '_priceinfo_text', stripslashes( __( 'Price incl. VAT., Excl. Shipping', ATKP_PLUGIN_PREFIX ) ) );
	}

	public function get_add_to_cart() {
		return $this->get_cachedoption( '_add_to_cart', 'link' );
	}

	public function get_css_inline() {
		return $this->get_cachedoption( '_css_inline', atkp_css_type::InlineHead );
	}

	public function get_title_link_type() {
		return $this->get_cachedoption( '_title_link_type', '' );
	}

	public function get_mark_links() {
		return $this->get_cachedoption( '_mark_links', 1 );
	}

	public function get_open_window() {
		return $this->get_cachedoption( '_open_window', 1 );
	}

	public function get_openlinkswithjs() {
		return $this->get_cachedoption( '_jslink', 0 );
	}


	public function get_link_click_tracking() {
		return $this->get_cachedoption( '_link_click_tracking', '' );
	}

	public function get_priceasfallback() {
		return $this->get_cachedoption( '_priceasfallback', '' );
	}



	public function get_linkimage() {
		return $this->get_cachedoption( '_linkimage', false );
	}

	public function get_showrating() {
		return $this->get_cachedoption( '_showrating', true );
	}

	public function get_linkrating() {
		return $this->get_cachedoption( '_linkrating', false );
	}

	public function get_show_moreoffers() {
		return $this->get_cachedoption( '_show_moreoffers', false );
	}

	public function get_moreoffers_includemainoffer() {
		return $this->get_cachedoption( '_moreoffers_includemainoffer', false );
	}

	public function get_moreoffers_title() {
		return $this->get_cachedoption( '_moreoffers_title', __( 'Additional offers »', ATKP_PLUGIN_PREFIX ) );
	}

	public function get_moreoffers_count() {
		return $this->get_cachedoption( '_moreoffers_count', 0 );
	}

	public function get_moreoffers_template() {
		return $this->get_cachedoption( '_moreoffers_template', '' );
	}

	public function get_version_csv() {
		return $this->get_cachedoption( '_version_csv', 0 );
	}


	public function get_show_nota_template() {
		return $this->get_cachedoption( '_show_nota_template', false );
	}


	public function get_nota_template() {
		return $this->get_cachedoption( '_nota_template', '' );
	}


	public function get_nota_disable_link() {
		return $this->get_cachedoption( '_nota_disable_link', false );
	}

	//region ASA1 fields
	public function get_asa1_enabled() {
		return $this->get_cachedoption( '_asa_activate', false );
	}

	public function get_asa1_shopid() {
		return $this->get_cachedoption( '_asa_shopid', '' );
	}

	public function get_asa1_poststatus() {
		return $this->get_cachedoption( '_asa_poststatus', 'publish' );
	}

	public function get_asa1_templateid( $i ) {
		return $this->get_cachedoption( '_asa_templateid' . $i, '' );
	}

	public function get_asa1_templatename( $i ) {
		return $this->get_cachedoption( '_asa_templatename' . $i, '' );
	}

	public function get_asa1_importresult() {
		return $this->get_cachedoption( '_asa_importresult', '' );
	}

	public function get_asa1_allcollections() {
		return $this->get_cachedoption( '_asa_allcollections', 0 );
	}
	//endregion

	//region ASA2 fields
	public function get_asa2_enabled() {
		return $this->get_cachedoption( '_asa2_activate', false );
	}

	public function get_asa2_shopid() {
		return $this->get_cachedoption( '_asa2_shopid', '' );
	}

	public function get_asa2_poststatus() {
		return $this->get_cachedoption( '_asa2_poststatus', 'publish' );
	}

	public function get_asa2_templateid( $i ) {
		return $this->get_cachedoption( '_asa2_templateid' . $i, '' );
	}

	public function get_asa2_templatename( $i ) {
		return $this->get_cachedoption( '_asa2_templatename' . $i, '' );
	}

	public function get_asa2_importresult() {
		return $this->get_cachedoption( '_asa2_importresult', '' );
	}

	public function get_asa2_allcollections() {
		return $this->get_cachedoption( '_asa2_allcollections', 0 );
	}

	public function get_asa2_descriptionfield() {
		return $this->get_cachedoption( '_asa2_descriptionfield', ATKP_PRODUCT_POSTTYPE . '_description' );
	}

	//endregion
	public function get_sitestripe_posttypes() {
		$sel_post_types = $this->get_cachedoption( '_sitestripe_cpts', null );
		$sel_post_types = explode( ',', $sel_post_types );

		return $sel_post_types;
	}

	public function get_sitestripe_backup() {
		return $this->get_cachedoption( '_sitestripe_backup', false );
	}

	public function get_sitestripe_onlycreate() {
		return $this->get_cachedoption( '_sitestripe_onlycreate', false );
	}

	public function get_sitestripe_multishops() {
		return $this->get_cachedoption( '_sitestripe_multishops', false );
	}


	public function get_sitestripe_shopid( $value = '' ) {
		return $this->get_cachedoption( '_sitestripe_shopid' . ( $value != '' ? '_' . $value : '' ), '' );
	}

	public function get_sitestripe_poststatus() {
		return $this->get_cachedoption( '_sitestripe_poststatus', 'publish' );
	}

	public function get_sitestripe_templateid() {
		return $this->get_cachedoption( '_sitestripe_templateid', 'sitestripe' );
	}

	public function get_sitestripe_imageid() {
		return $this->get_cachedoption( '_sitestripe_imageid', '' );
	}


	public function get_sitestripe_importresult() {
		return $this->get_cachedoption( '_sitestripe_importresult', '' );
	}

	//region AAWP fields
	public function get_aawp_enabled() {
		return $this->get_cachedoption( '_aawp_activate', false );
	}

	public function get_sitestripe_enabled() {
		return $this->get_cachedoption( '_sitestripe_activate', false );
	}


	public function get_aawp_shopid() {
		return $this->get_cachedoption( '_aawp_shopid', '' );
	}


	public function get_aawp_shortcodename() {
		return $this->get_cachedoption( '_aawp_shortcodename', '' );
	}

	public function get_aawp_poststatus() {
		return $this->get_cachedoption( '_aawp_poststatus', 'publish' );
	}

	public function get_aawp_templateid( $i ) {
		return $this->get_cachedoption( '_aawp_templateid' . $i, '' );
	}

	public function get_aawp_templatename( $i ) {
		return $this->get_cachedoption( '_aawp_templatename' . $i, '' );
	}

	public function get_aawp_importresult() {
		return $this->get_cachedoption( '_aawp_importresult', '' );
	}

	public function get_aawp_descriptionfield() {
		return $this->get_cachedoption( '_aawp_descriptionfield', ATKP_PRODUCT_POSTTYPE . '_description' );
	}

	//endregion

	public function get_productgroupascategory() {
		return $this->get_cachedoption( '_productgroupascategory', false );
	}

	public function get_productgroupdeleteoldentries() {
		return $this->get_cachedoption( '_productgroupdeleteoldentries', false );
	}

	public function get_productgroupsplitchar() {
		return $this->get_cachedoption( '_productgroupsplitchar', '' );
	}

	public function get_update_producttitle_when_changed() {
		return $this->get_cachedoption( '_update_producttitle_when_changed', false );
	}

	public function get_ignoreoffernotfound() {
		return $this->get_cachedoption( '_ignoreoffernotfound', false );
	}


	public function get_showlistprice() {
		return $this->get_cachedoption( '_showlistprice', true );
	}

	public function get_showprice() {
		return $this->get_cachedoption( '_showprice', true );
	}

	public function get_showbaseprice() {
		return $this->get_cachedoption( '_showbaseprice', true );
	}

	public function get_linkprime() {
		return $this->get_cachedoption( '_linkprime', false );
	}

	public function get_showpricediscount() {
		return $this->get_cachedoption( '_showpricediscount', true );
	}

	public function get_showstarrating() {
		return $this->get_cachedoption( '_showstarrating', true );
	}

	public function get_hideemptystars() {
		return $this->get_cachedoption( '_hideemptystars', false );
	}

	public function get_hideemptyrating() {
		return $this->get_cachedoption( '_hideemptyrating', false );
	}

	public function get_hideprocontra() {
		return $this->get_cachedoption( '_hideprocontra', false );
	}

	public function get_box_show_shadow() {
		return $this->get_cachedoption( '_box_show_shadow', false );
	}

	public function get_box_description_content() {
		return $this->get_cachedoption( '_boxcontent', 1 );
	}

	public function get_productpage_title() {
		return $this->get_cachedoption( '_productpage_title', __( 'View Product', ATKP_PLUGIN_PREFIX ) );
	}

	public function get_secbtn_image() {
		return $this->get_cachedoption( '_secbtn_image', '' );
	}

	public function get_primbtn_image() {
		return $this->get_cachedoption( '_primbtn_image', '' );
	}

	//radius

	public function get_box_radius() {
		return $this->get_cachedoption( '_box_radius', 5 );
	}

	public function get_button_radius() {
		return $this->get_cachedoption( '_btn_radius', 5 );
	}

	//color
	public function get_box_background_color() {
		return $this->get_cachedoption( '_box_background_color', '#ffff' );
	}

	public function get_box_border_color() {
		return $this->get_cachedoption( '_box_border_color', '#ececec' );
	}

	public function get_box_text_color() {
		return $this->get_cachedoption( '_box_text_color', '#111' );
	}

	public function get_box_badge_color() {
		return $this->get_cachedoption( '_box_badge_color', '#E47911' );
	}

	public function get_feature_count() {
		return $this->get_cachedoption( '_feature_count', 0 );
	}

	public function get_description_length() {
		return $this->get_cachedoption( '_description_length', 0 );
	}

	public function get_short_title_length() {
		return $this->get_cachedoption( '_short_title_length', 0 );
	}

	public function get_box_textlink_hovercolor() {
		return $this->get_cachedoption( '_box_textlink_hovercolor', '#111' );
	}

	public function get_dropdown_text_color() {
		return $this->get_cachedoption( '_dropdown_textlink_color', '#111' );
	}

	public function get_dropdown_text_hovercolor() {
		return $this->get_cachedoption( '_dropdown_textlink_hovercolor', '#111' );
	}


	public function get_box_textlink_color() {
		return $this->get_cachedoption( '_box_textlink_color', '#2271b1' );
	}


	public function get_primbtn_size() {
		return $this->get_cachedoption( '_primbtn_size', 'mormal' );
	}

	public function get_secbtn_size() {
		return $this->get_cachedoption( '_secbtn_size', 'mormal' );
	}

	public function get_primbtn_background_color() {
		return $this->get_cachedoption( '_primbtn_background_color', '#f0c14b' );
	}

	public function get_primbtn_hoverbackground_color() {
		return $this->get_cachedoption( '_primbtn_hoverbackground_color', '#f7dfa5' );
	}

	public function get_primbtn_foreground_color() {
		return $this->get_cachedoption( '_primbtn_foreground_color', '#111' );
	}

	public function get_primbtn_border_color() {
		return $this->get_cachedoption( '_primbtn_border_color', '#f0c14b' );
	}

	public function get_secbtn_background_color() {
		return $this->get_cachedoption( '_secbtn_background_color', '#f0c14b' );
	}

	public function get_secbtn_hoverbackground_color() {
		return $this->get_cachedoption( '_secbtn_hoverbackground_color', '#f7dfa5' );
	}

	public function get_secbtn_foreground_color() {
		return $this->get_cachedoption( '_secbtn_foreground_color', '#333333' );
	}

	public function get_secbtn_border_color() {
		return $this->get_cachedoption( '_secbtn_border_color', '#f0c14b' );
	}

	public function get_listprice_color() {
		return $this->get_cachedoption( '_listprice_color', '#808080' );
	}

	public function get_amountsaved_color() {
		return $this->get_cachedoption( '_amountsaved_color', '#8b0000' );
	}

	public function get_price_color() {
		return $this->get_cachedoption( '_price_color', '#00000' );
	}

	//color

}