<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * The base class of the shop providers.
 */
class atkp_shop_provider_base {

	public function __construct() {
	}

	/**
	 * Returns the default logo url
	 *
	 * @param int $post_id The ID of the shop
	 *
	 * @return string The URL of the logo
	 */
	public function get_default_logo( $post_id ) {
		return '';
	}

	/**
	 * Returns the default small logo url
	 *
	 * @param int $post_id The ID of the shop
	 *
	 * @return string The URL of the logo
	 */
	public function get_default_small_logo( $post_id ) {
		return '';
	}

	/**
	 * Converts the price string into a float value
	 *
	 * @param string $price_str The price as string including currency sign.
	 *
	 * @return float A float value for price
	 */
	protected static function price_to_float( $price_str ) {
		return ATKPTools::price_to_float( $price_str );
	}

	/**
	 * This is the string for "Buy now" default value in the shop settings.
	 * @return string The default string value
	 */
	public function get_defaultbtn1_text() {
		return __( 'Buy now at %s!', ATKP_PLUGIN_PREFIX );
	}

	/**
	 * This is the string for "Add to cart" default value in the shop settings.
	 * @return string The default string value
	 */
	public function get_defaultbtn2_text() {
		return __( 'Buy now at %s!', ATKP_PLUGIN_PREFIX );
	}

	/**
	 * Loads a specific shop provider from registered list.
	 *
	 * @param int $id The ID of the provider. Don't mix it with the shop ID.
	 *
	 * @return atkp_shop_provider_base|null Returns the provider or null when not found
	 */
	public static function retrieve_provider( $id ) {

		$providers = array();
		$providers = apply_filters( 'atkp_load_providers', $providers, $id );

		if ( $id != '' && isset( $providers[ strval( $id ) ] ) ) {
			return $providers[ $id ];
		} else {
			return null;
		}
	}

	/**
	 * Returns all registered shop providers for this instance.
	 * @return array An array of atkp_shop_provider_base
	 */
	public static function retrieve_providers() {
		$providers = array();

		$providers = apply_filters( 'atkp_load_providers', $providers, null );

		return $providers;
	}

	/**
	 * Returns the display name of this shop provider.
	 * @return string The display name
	 */
	public function get_caption() {
		return 'base';
	}

	/**
	 * This function is testing the shop provider connection. It is sending a test request.
	 *
	 * @param int $shop_id The ID of the shop
	 *
	 * @return string If the test is successfull the function returns a empty string. Otherwise the error message.
	 */
	public function check_configuration( $shop_id ) {
		return '';
	}

	/**
	 * This function is for saving all provider specific data for a shop.
	 *
	 * @param int $shop_id The ID of the shop
	 *
	 * @return void
	 */
	public function set_configuration( $shop_id ) {

	}

	/**
	 * This function is for displaying shop settings relevant fields in the UI.
	 * Echo the relevant fields as needed.
	 *
	 * @param WP_Post $post The post object of the shop.
	 *
	 * @return void
	 */
	public function get_configuration( $post ) {


	}

	/**
	 * This function must be called before sending search or retrieve requests to the API.
	 *
	 * @param atkp_shop $shop The shop which is used to send the requests.
	 *
	 * @return void
	 */
	public function checklogon( $shop ) {

	}

	/**
	 * Quick search for products with less response
	 *
	 * @param string $keyword
	 * @param string $searchType typeof atkp_search_type
	 * @param int $page
	 *
	 * @return atkp_search_resp
	 */
	public function quick_search( $keyword, $searchType, $page = 1 ) {

	}

	/**
	 * Retrieving a list of product for a load request
	 *
	 * @param array $asins
	 * @param string $id_type
	 *
	 * @return atkp_response
	 */
	public function retrieve_products( $asins, $id_type = 'ASIN' ) {

	}

	/**
	 * Retrieving a list of products for a search request
	 *
	 * @param string $request_type
	 * @param string $nodeid
	 * @param string $keyword
	 * @param string $asin
	 * @param int $max_count
	 * @param string $sort_order
	 * @param array|null $filter
	 *
	 * @return atkp_list_resp
	 * @deprecated deprecated since version 3.3.4 - instead use function retrieve_product_list
	 */
	public function retrieve_list( $request_type, $nodeid, $keyword, $asin, $max_count, $sort_order, $filter ) {

	}

	/**
	 * Retrieving a list of products for a search request
	 *
	 * @param atkp_list_req $search_request
	 *
	 * @return atkp_list_resp
	 */
	public function retrieve_product_list( $search_request ) {

	}

	/**
	 * Make a search request to all categories of a shop
	 *
	 * @param string $keyword
	 *
	 * @return array
	 */
	public function retrieve_browsenodes( $keyword ) {

	}

	/**
	 * Retrieve the root categories for a shop
	 * @return array
	 */
	public function retrieve_departments() {

	}

	/**
	 * Retrieve supported fields for filter (extended search)
	 * @return array
	 */
	public function retrieve_filters() {

	}

	/**
	 * Returns the maximum allowed items per page
	 * @return int
	 */
	public function get_maximum_items_per_page() {
		return 10;
	}

	/**
	 * Returns the maximum allowed pages
	 * @return int
	 */
	public function get_maximum_pages() {
		return 10;
	}

	/**
	 * It defines the supported search methods in a list. See type atkp_list_source_type
	 * @return string Returns a string separated by comma.
	 */
	public function get_supportedlistsources() {

	}

	/**
	 * This can manipulate a URL and replace the tracking ID for a URL.
	 *
	 * @param int $shop_id The ID of the shop
	 * @param string $url The affiliate url
	 * @param string $trackingId The new tracking code
	 *
	 * @return string The URL including the replaced tracking ID.
	 */
	public function replace_trackingid( $shop_id, $url, $trackingId ) {
		return $url;
	}
}

//compatiblity for old functionality
require_once( 'subshop.php' );