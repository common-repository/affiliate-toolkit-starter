<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class atkp_queue_table extends WP_List_Table {
	function __construct() {
		parent::__construct( array(
			'singular' => __( 'Queue', ATKP_PLUGIN_PREFIX ),
			//Singular label
			'plural'   => __( 'Queues', ATKP_PLUGIN_PREFIX ),
			//plural label, also this well be one of the table css class
			'ajax'     => false
			//We won't support Ajax for this table
		) );
	}


	/**
	 * Delete a customer record.
	 *
	 * @param int $id customer ID
	 */
	public static function delete_queue( $id ) {
		$queue = atkp_queue::load( $id );

		$queue->delete();
	}


	/**
	 * Returns the count of records in the database.
	 *
	 * @return null|string
	 */
	public static function record_count() {

		return atkp_queue::get_total();
	}


	/** Text displayed when no customer data is available */
	public function no_items() {
		esc_html__( 'No queues available.', ATKP_PLUGIN_PREFIX );
	}


	/**
	 * Render a column when no column specific method exist.
	 *
	 * @param array $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'name':
			case 'id':
			case 'title':
			default:
				return $item[ $column_name ];

			case 'runtime':
				$createdon = $item['createdon'];
				$updatedon = $item['updatedon'];
				$seconds   = abs( strtotime( $updatedon ) - strtotime( $createdon ) );

				if ( $seconds > 60 ) {
					return sprintf( __( '%s Minutes', ATKP_PLUGIN_PREFIX ), round( $seconds / 60, 0 ) );
				} else {
					return sprintf( __( '%s Seconds', ATKP_PLUGIN_PREFIX ), round( $seconds, 0 ) );
				}

				break;

			case 'retries':
				return sprintf( __( '%s Retries', ATKP_PLUGIN_PREFIX ), $item[ $column_name ] == null || $item[ $column_name ] <= 1 ? 0 : ( $item[ $column_name ] - 1 ) );
			case 'entries':
				$atkp_queuetable_helper = new atkp_queuetable_helper();

				$cnt      = $atkp_queuetable_helper->get_queue_count( $item['id'] );
				$finished = $atkp_queuetable_helper->get_queue_finished( $item['id'] );

				$createdon = $item['createdon'];
				$updatedon = $item['updatedon'];
				$seconds   = abs( strtotime( $updatedon ) - strtotime( $createdon ) );

				$itemspersecond = $finished > 0 ? ( $seconds / $finished ) : 0;

				return '<span style="">' . sprintf( __( '%s Entries', ATKP_PLUGIN_PREFIX ), $cnt ) . '</span>' . ( $finished != $cnt ? '<br /><span style="">' . sprintf( __( '%s Finished', ATKP_PLUGIN_PREFIX ), $finished ) . '</span>' : '' ) . ( $itemspersecond > 0 ? '<br /><span style="">' . sprintf( __( '%s Entries/Minute', ATKP_PLUGIN_PREFIX ), round( $itemspersecond * 60, 2 ) ) . '</span>' : '' );


				break;
			case 'type':
				$posttypes = explode( ', ', $item[ $column_name ] );

				$names = array();
				foreach ( $posttypes as $pt ) {
					$post_type_obj = get_post_type_object( $pt );
					if ( $post_type_obj != null ) {
						$names[] = $post_type_obj->labels->singular_name;
					} //Ice Cream.
					else {
						$names[] = $pt;
					}
				}

				return implode( '<br />', $names );

				break;
			case 'status':
				$atkp_queuetable_helper = new atkp_queuetable_helper();

				$percent = '0,0%';

				$total = $atkp_queuetable_helper->get_queue_count( $item['id'] );
				if ( $total > 0 ) {
					$finished = $atkp_queuetable_helper->get_queue_finished( $item['id'] );
					$percent  = number_format( round( ( ( $finished / $total ) * 100 ), 1 ), 1, ',', '.' ) . '%';
				}

				switch ( $item[ $column_name ] ) {
					case atkp_queue_status::SUCCESSFULLY:
						return '<span style="color:green;font-weight:bold;">' . __( 'Successfully', ATKP_PLUGIN_PREFIX ) . '</span>' . '<br /> ';

					case atkp_queue_status::ERROR:
						$cnt = $atkp_queuetable_helper->get_queue_errors( $item['id'] );

						if ( $cnt > 0 ) {
							return sprintf( '<a href="?page=%s&action=%s&queueid=%s&filter=error"><span style="color:red;font-weight:bold;text-decoration:underline">' . __( '%s Errors', ATKP_PLUGIN_PREFIX ) . '</span></a>', esc_attr( $_REQUEST['page'] ), 'detail', absint( $item['id'] ), $cnt );
						} else {
							return '<span style="color:red;font-weight:bold;">' . sprintf( __( '%s Errors', ATKP_PLUGIN_PREFIX ), $cnt ) . '</span>';
						}

					case atkp_queue_status::ABORT:
						return '<span style="color:orange;font-weight:bold;">' . __( 'Abort', ATKP_PLUGIN_PREFIX ) . '</span>' . ' (' . $percent . ')';

					case atkp_queue_status::ACTIVE:
						return '<span style="color:green;font-weight:bold;">' . __( 'Running', ATKP_PLUGIN_PREFIX ) . '</span>' . ' (' . $percent . ')';
				}
				break;
			case 'updatedon':
			case 'createdon':

				return ATKPTools::get_formatted_date( strtotime( $item[ $column_name ] ) ) . __( ' at ', ATKP_PLUGIN_PREFIX ) . ATKPTools::get_formatted_time( strtotime( $item[ $column_name ] ) );
				break;


			//default:
			//		return print_r( $item, true ); //Show the whole array for troubleshooting purposes
		}
	}


	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	function column_cb( $item ) {
		return ''; //sprintf(			'<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['id']		);
	}


	/**
	 * Method for name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_name( $item ) {

		$delete_nonce = wp_create_nonce( 'atkp_edit_queue' );

		$title = sprintf( '<a href="?page=%s&action=%s&queueid=%s&_wpnonce=%s"><strong>%s</strong></a>', esc_attr( $_REQUEST['page'] ), 'detail', absint( $item['id'] ), $delete_nonce, $item['title'] );

		$actions = [
			//'edit' => sprintf( '<a href="?page=%s&action=%s&queueid=%s&_wpnonce=%s">Edit</a>', esc_attr( $_REQUEST['page'] ), 'edit', absint( $item['id'] ), $delete_nonce ),
			'delete' => sprintf( '<a href="?page=%s&action=%s&queueid=%s&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['id'] ), $delete_nonce ),

		];

		return $title . $this->row_actions( $actions );
	}


	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	function get_columns() {
		$columns = [
			'cb'        => '<input type="checkbox" />',
			'name'      => __( 'Title', ATKP_PLUGIN_PREFIX ),
			'status'    => __( 'Status', ATKP_PLUGIN_PREFIX ),
			'type'      => __( 'Type', ATKP_PLUGIN_PREFIX ),
			'entries'   => __( 'Entries', ATKP_PLUGIN_PREFIX ),
			'createdon' => __( 'Created on', ATKP_PLUGIN_PREFIX ),
			'updatedon' => __( 'Last Activity', ATKP_PLUGIN_PREFIX ),
			'runtime'   => __( 'Runtime', ATKP_PLUGIN_PREFIX ),
			'retries'   => __( 'Retries', ATKP_PLUGIN_PREFIX ),
		];

		return $columns;
	}


	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'id'   => array( 'id', true ),
			'name' => array( 'title', false )
		);

		return $sortable_columns;
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = [
			//'bulk-delete' => 'Delete'
		];

		return $actions;
	}


	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items() {

		$this->_column_headers = $this->get_column_info();

		/** Process bulk action */
		$this->process_bulk_action();

		$per_page     = $this->get_items_per_page( 'links_per_page', 25 );
		$current_page = $this->get_pagenum();
		$total_items  = self::record_count();

		$this->set_pagination_args( [
			'total_items' => $total_items, //WE have to calculate the total number of items
			'per_page'    => $per_page //WE have to determine how many items to show on a page
		] );

		$this->items = atkp_queue::get_list( $per_page, $current_page, ( isset( $_REQUEST['orderby'] ) ? $_REQUEST['orderby'] : 'id' ), ( isset( $_REQUEST['order'] ) ? $_REQUEST['order'] : 'desc' ) );
	}

	public function process_bulk_action() {

		//Detect when a bulk action is being triggered...
		if ( 'delete' === $this->current_action() ) {

			// In our file that handles the request, verify the nonce.
			$nonce = esc_attr( $_REQUEST['_wpnonce'] );

			if ( ! wp_verify_nonce( $nonce, 'atkp_delete_link' ) ) {
				die( 'Go get a life script kiddies' );
			} else {

				$obj = atkp_queue::load( absint( $_GET['queueid'] ) );

				$obj->delete();

				// esc_url_raw() is used to prevent converting ampersand in url to "#038;"
				// add_query_arg() return the current url
				wp_redirect( sprintf( '?page=%s', esc_attr( $_REQUEST['page'] ) ) );
				exit;
			}

		}

		// If the delete bulk action is triggered
		if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' )
		     || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' )
		) {


			$delete_ids = esc_sql( $_POST['bulk-delete'] );

			// loop over the array of record IDs and delete them
			foreach ( $delete_ids as $id ) {
				$obj = atkp_queue::load( $id );
				$obj->delete();

			}

			// esc_url_raw() is used to prevent converting ampersand in url to "#038;"
			// add_query_arg() return the current url
			wp_redirect( sprintf( '?page=%s', esc_attr( $_REQUEST['page'] ) ) );
			exit;
		}
	}

}