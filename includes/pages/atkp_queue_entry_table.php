<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class atkp_queue_entry_table extends WP_List_Table {
	function __construct() {
		parent::__construct( array(
			'singular' => __( 'Queue Entry', ATKP_PLUGIN_PREFIX ),
			//Singular label
			'plural'   => __( 'Queue Entries', ATKP_PLUGIN_PREFIX ),
			//plural label, also this well be one of the table css class
			'ajax'     => false
			//We won't support Ajax for this table
		) );
	}

	/**
	 * @var atkp_queue $queue
	 */
	public static $queue;


	/**
	 * Delete a customer record.
	 *
	 * @param int $id customer ID
	 */
	public static function delete_queue( $id ) {
		//$queue = atkp_queue_entry::load($id);

		//$queue->delete();
	}


	/**
	 * Returns the count of records in the database.
	 *
	 * @return null|string
	 */
	public static function record_count( $filter = '' ) {

		return atkp_queue_entry::get_total( self::$queue->id, $filter );
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
			default:
				return $item[ $column_name ];

			case 'post_id':
				$post_id   = $item[ $column_name ];
				$post_type = $item['post_type'];

				$link = get_edit_post_link( $post_id );
				if ( $link == null ) {
					$title = get_the_title( $post_id );
					if ( $title == null ) {
						return ( $post_id == 0 ? '' : $post_id );
					} else {
						return esc_html( $title ) . ' (' . $post_id . ', ' . $post_type . ')';
					}
				} else {
					$title = get_the_title( $post_id );

					return '<a href="' . $link . '" target="_blank">' . esc_html( $title ) . ' (' . $post_id . ')</a>';
				}
				break;
			case 'shop_id':
				$shopid = $item[ $column_name ];

				if ( $shopid > 0 ) {
					$link = get_edit_post_link( $shopid );

					if ( $link == null ) {
						$title = get_the_title( $shopid );
						if ( $title == null ) {
							return $shopid;
						} else {
							return esc_html( $title ) . ' (' . $shopid . ')';
						}
					} else {
						$title = get_the_title( $shopid );

						return '<a href="' . $link . '" target="_blank">' . esc_html( $title ) . ' (' . $shopid . ')</a>';
					}
				}
				break;

			case 'post_type':
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
				switch ( $item[ $column_name ] ) {
					case atkp_queue_entry_status::SUCCESSFULLY:
						return '<span style="color:green;font-weight:bold;">' . __( 'Successfully', ATKP_PLUGIN_PREFIX ) . '</span>';

					case atkp_queue_entry_status::ERROR:
						return '<span style="color:red;font-weight:bold;">' . __( 'Error', ATKP_PLUGIN_PREFIX ) . '</span>';

					case atkp_queue_entry_status::NOT_PROCESSED:
						return '<span style="color:orange;font-weight:bold;">' . __( 'Not processed', ATKP_PLUGIN_PREFIX ) . '</span>';

					case atkp_queue_entry_status::PROCESSED:
						return '<span style="font-weight:bold;">' . __( 'Processed', ATKP_PLUGIN_PREFIX ) . '</span>';
					case atkp_queue_entry_status::FINISHED:
						return '<span style="color:green;font-weight:bold;">' . __( 'Finalized', ATKP_PLUGIN_PREFIX ) . '</span>';
					case atkp_queue_entry_status::PREPARED:
						return '<span style="color:orange;font-weight:bold;">' . __( 'Prepared for processing', ATKP_PLUGIN_PREFIX ) . '</span>';
				}
				break;
			case 'updatedon':
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
		return sprintf(
			'<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['id']
		);
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
			//'delete' => sprintf( '<a href="?page=%s&action=%s&queueid=%s&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['id'] ), $delete_nonce ),
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
			'id'                => __( 'ID', ATKP_PLUGIN_PREFIX ),
			'post_id'           => __( 'Object', ATKP_PLUGIN_PREFIX ),
			'shop_id'           => __( 'Shop', ATKP_PLUGIN_PREFIX ),
			'status'            => __( 'Status', ATKP_PLUGIN_PREFIX ),
			'functionname'      => __( 'Function', ATKP_PLUGIN_PREFIX ),
			'functionparameter' => __( 'Parameter', ATKP_PLUGIN_PREFIX ),
			'updatedon'         => __( 'Last update', ATKP_PLUGIN_PREFIX ),
			'updatedmessage'    => __( 'Message', ATKP_PLUGIN_PREFIX ),
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

		$filter = isset( $_GET['filter'] ) ? $_GET['filter'] : '';

		$per_page     = $this->get_items_per_page( 'links_per_page', 50 );
		$current_page = $this->get_pagenum();
		$total_items  = self::record_count( $filter );

		$this->set_pagination_args( [
			'total_items' => $total_items, //WE have to calculate the total number of items
			'per_page'    => $per_page //WE have to determine how many items to show on a page
		] );


		$this->items = atkp_queue_entry::get_list( self::$queue->id, $filter, $per_page, $current_page, ( isset( $_REQUEST['orderby'] ) ? $_REQUEST['orderby'] : 'id' ), ( isset( $_REQUEST['order'] ) ? $_REQUEST['order'] : 'asc' ) );
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