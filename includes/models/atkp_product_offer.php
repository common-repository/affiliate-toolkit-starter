<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class atkp_product_offer {
	public $data = array();

	const OFFER_TXPE_EAN = 1;
	const OFFER_TYPE_PRODUCTID = 2;


	function __construct() {
		$this->id            = '';
		$this->shipping      = '';
		$this->availability  = '';
		$this->shipping      = '';
		$this->shippingfloat = (float) 0;
		$this->price         = '';
		$this->pricefloat    = (float) 0;

		$this->shopid      = '';
		$this->type        = '';
		$this->number      = '';
		$this->link        = '';
		$this->hideoffer   = false;
		$this->holdontop   = 0;
		$this->ismainoffer = false;
		$this->cpcfloat    = 0;

		$this->shop = null;

		$this->product = null;

		$this->title = '';
	}

	/**
	 * Lädt die Angebote zu der übergebenen Produkt-ID aus der offertable
	 *
	 * @param int $productid Die AT-Produkt-ID
	 *
	 * @return array Ein Array von Angeboten
	 */
	public static function load_offers( $productid ) {


		return array();
	}

	public static function load_offers_by_listid( $list_id, $asin ) {


		return array();
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


?>