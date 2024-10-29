<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class atkp_queue {
	public $data = array();

	function __construct() {
		$this->id    = 0;
		$this->title = '';
		//status: active, error, successfully, abort
		$this->status         = '';
		$this->internalstatus = '';
		//type: products,lists,csv,awin
		$this->type    = '';
		$this->retries = 0;

		$this->runningtime    = '';
		$this->createdon      = '';
		$this->updatedon      = '';
		$this->updatedmessage = '';

		$this->entries = array();
	}

	public static function load( $atkp_queueid ) {
		$atkp_queuetable_helper = new atkp_queuetable_helper();

		return $atkp_queuetable_helper->load_queue( $atkp_queueid );
	}

	public static function get_total() {
		$atkp_queuetable_helper = new atkp_queuetable_helper();

		return $atkp_queuetable_helper->get_total();
	}

	public static function get_list( $per_page = 5, $page_number = 1, $orderby = '', $order = '' ) {
		$atkp_queuetable_helper = new atkp_queuetable_helper();

		return $atkp_queuetable_helper->get_list( $per_page, $page_number, $orderby, $order );
	}

	public function get_last_activity() {
		$atkp_queuetable_helper = new atkp_queuetable_helper();

		$lastactivity = $atkp_queuetable_helper->get_last_activity( $this->id );

		return $lastactivity;
	}

	public function save() {
		$atkp_queuetable_helper = new atkp_queuetable_helper();

		$queue    = $atkp_queuetable_helper->save_queue( $this );
		$this->id = $queue->id;
	}

	public function delete() {
		$atkp_queuetable_helper = new atkp_queuetable_helper();

		return $atkp_queuetable_helper->delete_queue( $this->id );
	}

	public static function clean_queues() {
		$atkp_queuetable_helper = new atkp_queuetable_helper();

		$queues = $atkp_queuetable_helper->get_old_list();

		$entries = array();
		if ( $queues != null ) {
			foreach ( $queues as $queue ) {
				$affected  = $atkp_queuetable_helper->delete_queue( $queue['id'] );
				$entries[] = $queue['id'];
			}
		}

		return $entries;
	}

	public static function get_report( $date ) {
		$atkp_queuetable_helper = new atkp_queuetable_helper();

		return $atkp_queuetable_helper->get_report( $date );
	}

	public function has_errors() {
		$atkp_queuetable_helper = new atkp_queuetable_helper();

		return $atkp_queuetable_helper->get_queue_has_errors( $this->id );
	}


	public function get_next_entries( $status ) {
		$atkp_queuetable_helper = new atkp_queuetable_helper();

		return $atkp_queuetable_helper->get_next_entries( $this->id, $status );
	}

	public static function exists_notfinished() {
		$atkp_queuetable_helper = new atkp_queuetable_helper();

		return $atkp_queuetable_helper->exists_notfinished();
	}

	public static function get_active_queue() {
		$atkp_queuetable_helper = new atkp_queuetable_helper();

		return $atkp_queuetable_helper->get_active_queue();
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

class atkp_queue_entry {
	public $data = array();

	function __construct() {
		$this->id        = 0;
		$this->queue_id  = 0;
		$this->post_id   = 0;
		$this->shop_id   = 0;
		$this->post_type = '';
		//status: prepared, error, successfully, abort
		$this->status            = '';
		$this->functionname      = '';
		$this->functionparameter = '';

		$this->createdon      = '';
		$this->updatedon      = '';
		$this->updatedmessage = '';
	}

	public static function get_total( $queue_id, $filter = '' ) {
		$atkp_queuetable_helper = new atkp_queuetable_helper();

		return $atkp_queuetable_helper->get_queue_count( $queue_id, $filter );
	}

	public static function get_list( $queue_id, $filter = '', $per_page = 5, $page_number = 1, $orderby = '', $order = '' ) {
		$atkp_queuetable_helper = new atkp_queuetable_helper();

		return $atkp_queuetable_helper->get_list_entry( $queue_id, $post_id = 0, $filter, $per_page, $page_number, $orderby, $order );
	}

	public function save() {
		$atkp_queuetable_helper = new atkp_queuetable_helper();

		$atkp_queuetable_helper->save_queue_entry( $this );
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

class atkp_queue_entry_status {
	public const PREPARED = 'prepared';
	public const ERROR = 'error';
	public const SUCCESSFULLY = 'successfully';
	public const PROCESSED = 'processed';
	public const FINISHED = 'finished';
	public const NOT_PROCESSED = 'not_processed';
}

class atkp_queue_status {
	public const ACTIVE = 'active';
	public const ERROR = 'error';
	public const SUCCESSFULLY = 'successfully';
	public const ABORT = 'abort';
}


?>