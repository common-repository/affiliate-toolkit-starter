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

class atkp_translator {

	/** @var atkp_template_helper $templatehelper */
	private $templatehelper;
	/** @var atkp_template_parameters $parameters */
	private $parameters;

	public function __construct( $templatehelper, $parameters ) {
		$this->templatehelper = $templatehelper;
		$this->parameters     = $parameters;

	}


	public function get_manufacturer() {
		return __( 'Manufacturer', ATKP_PLUGIN_PREFIX );
	}

	public function get_price_simple() {
		return __( 'Price', ATKP_PLUGIN_PREFIX );
	}

	public function get_sortby() {
		return __( 'Sort by', ATKP_PLUGIN_PREFIX );
	}

	public function get_search() {
		return __( 'Search', ATKP_PLUGIN_PREFIX );
	}

	public function get_product1() {
		return __( 'Product 1', ATKP_PLUGIN_PREFIX );
	}

	public function get_product2() {
		return __( 'Product 2', ATKP_PLUGIN_PREFIX );
	}

	public function get_product3() {
		return __( 'Product 3', ATKP_PLUGIN_PREFIX );
	}

	public function get_product4() {
		return __( 'Product 4', ATKP_PLUGIN_PREFIX );
	}

	public function get_compareproducts() {
		return __( 'Compare products', ATKP_PLUGIN_PREFIX );
	}

	public function get_searching() {
		return __( 'Searching...', ATKP_PLUGIN_PREFIX );
	}

	public function get_searchterm() {
		return __( 'Search term is required.', ATKP_PLUGIN_PREFIX );
	}

	public function get_noproducts() {
		return __( 'No products were found with this search term.', ATKP_PLUGIN_PREFIX );
	}

	public function get_searchresultsat( $name ) {
		return sprintf( __( 'Search results at %s', ATKP_PLUGIN_PREFIX ), $name );
	}

	public function get_listprice() {
		return __( 'List Price: %s', ATKP_PLUGIN_PREFIX );
	}

	public function get_yousave() {
		return __( 'You Save: %s', ATKP_PLUGIN_PREFIX );
	}

	public function get_price() {
		return __( 'Price: %s', ATKP_PLUGIN_PREFIX );
	}

	public function get_pricenotavailable() {
		return __( 'Price not available', ATKP_PLUGIN_PREFIX );
	}

	public function get_pricedouble() {
		return __( 'Price:', ATKP_PLUGIN_PREFIX );
	}

	public function get_val() {
		return __( '%s', ATKP_PLUGIN_PREFIX );
	}

	public function get_insteadof() {
		return __( 'instead of:', ATKP_PLUGIN_PREFIX );
	}

	public function get_shipping() {
		return __( 'Shipping: %s', ATKP_PLUGIN_PREFIX );
	}

	public function get_shippingna() {
		return __( 'Shipping: N/A', ATKP_PLUGIN_PREFIX );
	}

	public function get_productunavailabletext() {
		return __( 'This product is currently unavailable.', ATKP_PLUGIN_PREFIX );
	}

	public function get_searchproduct() {
		return __( 'Search product ...', ATKP_PLUGIN_PREFIX );
	}

	public function get_monthlypricehistorytitle() {
		return __( 'Monthly price history', ATKP_PLUGIN_PREFIX );
	}

	public function get_lastxdayspricehistorytitle( $days ) {
		return sprintf( __( 'Last %s days price history', ATKP_PLUGIN_PREFIX ), $days );
	}

	public function get_buynow() {
		return __( 'Buy now', ATKP_PLUGIN_PREFIX );
	}
}