<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Class atkp_list_resp
 * Response class for retrieve_list request
 *
 * @property string $listid The ID of the list
 * @property string $title The title of the list request
 * @property string $updatedon Timestamp when updated
 * @property string $message The message when an error occured
 * @property string $listurl The URL of the list request
 * @property int $total_pages Total pages of the response
 * @property int $total_items_count Total items of the response
 * @property array|null $products Array of atkp_product
 *
 */
class atkp_list_resp {

	public $data = array();

	function __construct() {
		$this->listid    = '';
		$this->title     = '';
		$this->updatedon = '';
		$this->message   = '';
		$this->listurl   = '';

		$this->total_pages       = 0;
		$this->total_items_count = 0;
	}

	/**
	 * @var array A list of unique IDs of the response
	 */
	public array $asins = array();

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