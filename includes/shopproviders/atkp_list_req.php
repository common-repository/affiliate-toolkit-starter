<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Class atkp_list_req
 * Request class for retrieve_product_list function
 *
 * @property string $request_type Use the following class: atkp_list_request_type
 * @property string $category The root category of the request
 * @property string $keyword The keyword for the search request
 * @property int $max_count The maximum count of products for the request
 * @property int $page The page which should be loaded
 * @property int $items_per_page Items per page which should be used for the request
 *
 * @property string $sort_order The sort order for the request
 * @property array|null $filter Array of filters
 *
 */
class atkp_list_req {

	public $data = array();

	function __construct() {

		$this->request_type = atkp_list_request_type::Search;
		$this->category     = '';
		$this->keyword      = '';

		$this->page           = 1;
		$this->items_per_page = 0;
		$this->max_count      = 0;

		$this->sort_order = '';
		$this->filter     = '';
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