<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class atkp_template_table extends WP_List_Table {
	function __construct() {
		parent::__construct( array(
			'singular' => __( 'Template', ATKP_PLUGIN_PREFIX ),
			//Singular label
			'plural'   => __( 'Templates', ATKP_PLUGIN_PREFIX ),
			//plural label, also this well be one of the table css class
			'ajax'     => false
			//We won't support Ajax for this table
		) );
	}

	protected function get_views() {
		$views   = array();
		$current = ( ! empty( $_REQUEST['view'] ) ? $_REQUEST['view'] : 'custom' );

		//Foo link
		$foo_url         = add_query_arg( 'view', 'custom' );
		$class           = ( $current == 'custom' ? ' class="current"' : '' );
		$views['custom'] = "<a href='{$foo_url}' {$class}>" . __( 'Custom template', ATKP_PLUGIN_PREFIX ) . "</a>";

		//Bar link
		$bar_url         = add_query_arg( 'view', 'system' );
		$class           = ( $current == 'system' ? ' class="current"' : '' );
		$views['system'] = "<a href='{$bar_url}' {$class}>" . __( 'System template', ATKP_PLUGIN_PREFIX ) . "</a>";

		return $views;
	}


	/**
	 * Delete a customer record.
	 *
	 * @param int $id customer ID
	 */
	public static function delete_template( $id ) {
		$queue = atkp_template::load( $id );

		//$queue->delete();
	}


	/**
	 * Returns the count of records in the database.
	 *
	 * @return null|string
	 */
	public static function record_count() {
		$view = ( isset( $_REQUEST['view'] ) ? $_REQUEST['view'] : 'custom' );

		if ( $view == 'system' ) {
			return count( atkp_template::get_system_list( null, null ) );
		} else {
			return atkp_template::get_total();
		}
	}


	/** Text displayed when no customer data is available */
	public function no_items() {
		esc_html__( 'No templates available.', ATKP_PLUGIN_PREFIX );
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
				return $item['post_title'];
				break;
			case 'template_type':
				if ( is_numeric( $item['ID'] ) ) {
					$durations = array();

					$durations = apply_filters( 'atkp_get_template_types', $durations );

					foreach ( $durations as $value => $name ) {
						if ( $value == ATKPTools::get_post_setting( $item['ID'], ATKP_TEMPLATE_POSTTYPE . '_template_type' ) ) {
							return $name;
						}
					}

					return '';
				} else {
					return $item['template_type'];
				}
				break;
			case 'template_preview':
				$template_preview_image = apply_filters( 'atkp_template_preview_image_url', '', ( $item['ID'] ) );

				if ( $template_preview_image != '' ) {
					return '<div class="atkp-template-dropdown"><img alt="' . esc_attr( $item['post_title'] ) . '" src="' . esc_attr( $template_preview_image ) . '" style="max-height:120px; max-width: 180px;" />
					<div class="atkp-template-dropdown-content">
  <img src="' . esc_attr( $template_preview_image ) . '" alt="' . esc_attr( $item['post_title'] ) . '" style="max-width:600px">
  <div class="atkp-template-desc">' . ( $item['post_title'] ) . '</div>
  </div></div>';
				} else {
					return '';
				}
				break;
			case 'post_date':
				return mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $item['post_modified'] );;
				break;
			case 'Shortcode':

				$temptype = isset( $item['template_type_id'] ) ? $item['template_type_id'] : '';
				if ( $temptype == '' && is_numeric( $item['ID'] ) ) {
					$temptype = ATKPTools::get_post_setting( $item['ID'], ATKP_TEMPLATE_POSTTYPE . '_template_type' );
				}

				if ( $temptype == 5 ) {
					return '<code>[atkp_searchform template=\'' . $item['ID'] . '\'][/atkp_searchform]</code>';
				} else if ( $item['ID'] == 'simple_live' || $item['ID'] == 'default_live' ) {
					return '<code>[atkp_livelist template=\'' . $item['ID'] . '\' livetemplate=\'secondwide\'][/atkp_livelist]</code>';
				} else {
					return '<code>[atkp template=\'' . $item['ID'] . '\' ids=\'\'][/atkp]</code>';
				}
			case 'title':
			default:
				return $item[ $column_name ];




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
		return '';

		//return sprintf(
		//	'<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['ID']
		//);
	}


	/**
	 * Method for name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_name( $item ) {

		$delete_nonce = wp_create_nonce( 'atkp_edit_template' );
		$naunce       = wp_create_nonce( 'atkp-export-template' );

		if ( is_numeric( $item['ID'] ) ) {
			$title = sprintf( '<a href="post.php?post=%s&action=edit"><strong>%s</strong></a>', absint( $item['ID'] ), $item['post_title'] );

			$actions = [
				'edit'   => sprintf( __( '<a href="post.php?post=%s&action=edit">Edit</a>', ATKP_PLUGIN_PREFIX ), absint( $item['ID'] ) ),
				'delete' => sprintf( __( '<a href="?page=%s&action=%s&templateid=%s&_wpnonce=%s">Delete</a>', ATKP_PLUGIN_PREFIX ), esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['ID'] ), $delete_nonce ),
				'clone'  => sprintf( __( '<a href="?page=%s&action=%s&templateid=%s&templatename=%s&_wpnonce=%s">Duplicate</a>', ATKP_PLUGIN_PREFIX ), esc_attr( $_REQUEST['page'] ), 'clone', absint( $item['ID'] ), urlencode( $item['post_title'] ), $delete_nonce ),
				'export' => sprintf( __( '<a href="%s?action=atkp_export_template&templateid=%s&request_nonce=%s">Export</a>', ATKP_PLUGIN_PREFIX ), ATKPTools::get_endpointurl(), absint( $item['ID'] ), $naunce ),
			];

		} else {
			$title = $item['post_title'];


			$actions = [
				'clone' => sprintf( __( '<a href="?page=%s&action=%s&templateid=%s&templatename=%s&_wpnonce=%s">Duplicate</a>', ATKP_PLUGIN_PREFIX ), esc_attr( $_REQUEST['page'] ), 'clone', ( $item['ID'] ), urlencode( $item['post_title'] ), $delete_nonce ),
			];

		}

		return $title . $this->row_actions( $actions );
	}


	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	function get_columns() {
		$columns = [

			'name'          => __( 'Title', ATKP_PLUGIN_PREFIX ),
			'Shortcode'     => __( 'Shortcode', ATKP_PLUGIN_PREFIX ),
			'template_type' => __( 'Template Type', ATKP_PLUGIN_PREFIX ),
			'post_date'     => __( 'Last modified', ATKP_PLUGIN_PREFIX ),
		];//'cb'      => '<input type="checkbox" />',

		$view = ( isset( $_REQUEST['view'] ) ? $_REQUEST['view'] : 'custom' );

		if ( $view == 'system' ) {
			$columns['template_preview'] = __( 'Preview', ATKP_PLUGIN_PREFIX );
		}

		return $columns;
	}


	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'id'         => array( 'id', true ),
			'post_title' => array( 'title', false )
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

		];

		return $actions;
	}


	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items() {
		$view = ( isset( $_REQUEST['view'] ) ? $_REQUEST['view'] : 'custom' );

		$this->_column_headers = $this->get_column_info();

		/** Process bulk action */
		$this->process_bulk_action();

		$per_page     = $this->get_items_per_page( 'links_per_page', 50 );
		$current_page = $this->get_pagenum();
		$total_items  = self::record_count();

		$this->set_pagination_args( [
			'total_items' => $total_items, //WE have to calculate the total number of items
			'per_page'    => $per_page, //WE have to determine how many items to show on a page
		] );

		if ( $view == 'system' ) {
			$this->items = atkp_template::get_system_list( $per_page, $current_page, ( isset( $_REQUEST['orderby'] ) ? $_REQUEST['orderby'] : 'id' ), ( isset( $_REQUEST['order'] ) ? $_REQUEST['order'] : 'desc' ) );
		} else {
			$this->items = atkp_template::get_page_list( $per_page, $current_page, ( isset( $_REQUEST['orderby'] ) ? $_REQUEST['orderby'] : 'id' ), ( isset( $_REQUEST['order'] ) ? $_REQUEST['order'] : 'desc' ) );
		}


	}

	public function process_bulk_action() {

		//Detect when a bulk action is being triggered...
		if ( 'delete' === $this->current_action() ) {

			// In our file that handles the request, verify the nonce.
			$nonce = esc_attr( $_REQUEST['_wpnonce'] );

			if ( ! wp_verify_nonce( $nonce, 'atkp_delete_link' ) ) {
				die( 'Go get a life script kiddies' );
			} else {
				$obj = atkp_template::load( absint( $_GET['templateid'] ) );

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
				$obj = atkp_template::load( $id );
				$obj->delete();

			}

			// esc_url_raw() is used to prevent converting ampersand in url to "#038;"
			// add_query_arg() return the current url
			wp_redirect( sprintf( '?page=%s', esc_attr( $_REQUEST['page'] ) ) );
			exit;
		}
	}

}