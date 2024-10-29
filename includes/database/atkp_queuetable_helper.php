<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! defined( 'ATKP_VERSION_QUEUETABLE' ) ) {
	define( 'ATKP_VERSION_QUEUETABLE', 2 );
}

class atkp_queuetable_helper {
	public function __construct() {
		$this->check_table_structure();

	}

	/**
	 * Gibt den internen Tabellenname inkl. Prefix für _offertable zurück
	 * @return string Der Tabellenname
	 */
	function get_queuetable_tablename() {
		global $wpdb;

		return $wpdb->prefix . 'atkp_queues';
	}

	function get_queueentrytable_tablename() {
		global $wpdb;

		return $wpdb->prefix . 'atkp_queues_entries';
	}

	public function exists_table() {
		global $wpdb;
		$tablename = $this->get_queuetable_tablename();
		$sql       = "SHOW TABLES LIKE '" . $tablename . "'";

		$result = $wpdb->get_results( $sql );

		return array( count( $result ) > 0, $tablename );
	}

	public function exists_detailtable() {
		global $wpdb;
		$tablename = $this->get_queueentrytable_tablename();
		$sql       = "SHOW TABLES LIKE '" . $tablename . "'";

		$result = $wpdb->get_results( $sql );

		return array( count( $result ) > 0, $tablename );
	}

	/**
	 * Prüft ob die Tabelle atkp_offertable vorhanden ist, und legt diese ggf. an.
	 */
	public function check_table_structure( $override = false ) {
		global $wpdb;

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' ); //required for dbDelta

		$current_version_db = get_option( ATKP_PLUGIN_PREFIX . '_version_posts_queues' );
		//$current_version_db = 0;
		$table_name  = $this->get_queuetable_tablename();
		$table_name2 = $this->get_queueentrytable_tablename();

		$charset_collate = ATKPTools::get_wp_charset_collate();

		//older or not even set
		if ( $current_version_db < ATKP_VERSION_QUEUETABLE || $current_version_db == false || $override ) {

			$sql = "CREATE TABLE $table_name (
	                    `id` bigint (50) NOT NULL AUTO_INCREMENT,	                    
	                    `title` varchar(700) NULL ,                                            
	                    `status` varchar( 250 ) NULL,                                                 
	                    `internalstatus` varchar( 250 ) NULL,                                 
	                    `type` varchar( 250 ) NULL,                                     
	                    `retries` INT( 50 ) NULL,      
	                    `createdon` datetime NULL ,
	                    `updatedon` datetime NULL ,
	                    `updatedmessage` varchar(700) NULL,
	                    PRIMARY KEY (id)		                     
	                    ) $charset_collate";

			dbDelta( $sql );

			$sql = "CREATE TABLE $table_name2 (
	                    `id` bigint (50) NOT NULL AUTO_INCREMENT,	                    
	                    `queue_id` bigint (50) NOT NULL,	                    
	                    `post_id` bigint (50) NULL,
	                    `post_type` varchar(700) NULL ,   
	                    `shop_id` bigint (50) NULL,                                         
	                    `status` varchar( 250 ) NULL,                                        
	                    `functionname` varchar( 250 ) NULL,                                  
	                    `functionparameter` varchar( 250 ) NULL,   
	                    `createdon` datetime NULL ,
	                    `updatedon` datetime NULL ,
	                    `updatedmessage` varchar(700) NULL,
	                    PRIMARY KEY (id)	                     
	                    ) $charset_collate";

			dbDelta( $sql );

			$wpdb->query( "CREATE INDEX atkp_queues_entries_queue_id ON {$table_name2} (queue_id)" );
			$wpdb->query( "CREATE INDEX atkp_queues_entries_queue_id_status ON {$table_name2} (queue_id, status)" );


			update_option( ATKP_PLUGIN_PREFIX . '_version_posts_queues', ATKP_VERSION_QUEUETABLE );

		}
	}

	public function delete_queue( $queue_id ) {
		global $wpdb;

		$table_name  = $this->get_queuetable_tablename();
		$table_name2 = $this->get_queueentrytable_tablename();

		$affected = $wpdb->query( "DELETE FROM $table_name2 WHERE queue_id=$queue_id" );

		$affected = $wpdb->query( "DELETE FROM $table_name WHERE id=$queue_id" );

		return $affected;
	}


	public function save_queue_entry( $queue_entry ) {
		global $wpdb;
		$table_name = $this->get_queueentrytable_tablename();

		$data = $this->get_array_from_queueentry( $queue_entry->queue_id, $queue_entry, $queue_entry->id == null );

		if ( $queue_entry->id == null ) {
			$wpdb->insert(
				$table_name,
				$data
			);
			$queue_entry->id = $wpdb->insert_id;
		} else {
			$wpdb->update(
				$table_name,
				$data,
				array( 'id' => $queue_entry->id )
			);
		}
	}


	public function save_queue( $queue ) {
		global $wpdb;

		$table_name = $this->get_queuetable_tablename();

		$data = $this->get_array_from_queue( $queue->id, $queue, $queue->id == null );

		if ( $queue->id == null ) {
			$wpdb->insert(
				$table_name,
				$data
			);

			$queue->id = $wpdb->insert_id;
		} else {
			$wpdb->update(
				$table_name,
				$data,
				array( 'id' => $queue->id )
			);
		}

		foreach ( $queue->entries as $entry ) {
			$entry->queue_id = $queue->id;
			$this->save_queue_entry( $entry );
		}

		return $queue;
	}

	public function get_last_activity( $queue_id ) {
		global $wpdb;

		$table_name = $this->get_queueentrytable_tablename();

		$query = $wpdb->prepare( "SELECT updatedon FROM $table_name WHERE queue_id = %d order by updatedon desc limit 1", $queue_id );

		$result = $wpdb->get_results( $query, ARRAY_A );

		$updatedon = count( $result ) > 0 ? $result[0]['updatedon'] : null;

		return $updatedon;
	}

	public function get_queue_errors( $queue_id ) {

		global $wpdb;

		$table_name = $this->get_queueentrytable_tablename();

		$query = $wpdb->prepare( "SELECT count(*) as cnt FROM $table_name WHERE queue_id = %d and (status = %s or status = %s)", $queue_id, atkp_queue_entry_status::ERROR, atkp_queue_entry_status::NOT_PROCESSED );

		$result = $wpdb->get_results( $query, ARRAY_A );

		$cnt = count( $result ) > 0 ? intval( $result[0]['cnt'] ) : 0;

		return $cnt;
	}

	public function get_queue_finished( $queue_id ) {

		global $wpdb;

		$table_name = $this->get_queueentrytable_tablename();

		$query = $wpdb->prepare( "SELECT count(*) as cnt FROM $table_name WHERE queue_id = %d and (status <> %s)", $queue_id, atkp_queue_entry_status::PREPARED );

		$result = $wpdb->get_results( $query, ARRAY_A );

		$cnt = count( $result ) > 0 ? intval( $result[0]['cnt'] ) : 0;

		return $cnt;
	}

	public function get_queue_count( $queue_id, $filter = null ) {

		global $wpdb;

		$table_name = $this->get_queueentrytable_tablename();

		$filter_query = ( $filter == 'error' ? ' and status in ("error", "not_processed")' : '' );

		$query = $wpdb->prepare( "SELECT count(*) as cnt FROM $table_name WHERE queue_id = %d " . $filter_query, $queue_id );

		$result = $wpdb->get_results( $query, ARRAY_A );

		$cnt = count( $result ) > 0 ? intval( $result[0]['cnt'] ) : 0;

		return $cnt;
	}

	public function get_queue_has_errors( $queue_id ) {

		$cnt = $this->get_queue_errors( $queue_id );

		if ( $cnt >= 1 ) {
			return true;
		} else {
			return false;
		}
	}

	public function get_queue_get_finished( $queue_id ) {

		$cnt = $this->get_queue_finished( $queue_id );

		if ( $cnt >= 1 ) {
			return true;
		} else {
			return false;
		}
	}

	public function get_next_entries( $queue_id, $status ) {

		if ( $status == '' ) {
			$status = atkp_queue_entry_status::PREPARED;
		}


		if ( is_array( $status ) ) {
			$t = '';
			foreach ( $status as $x ) {
				if ( $t == '' ) {
					$t = "'$x'";
				} else {
					$t .= ",'$x'";
				}
			}
			$stat = $t;
		} else {
			$stat = "'$status'";
		}

		global $wpdb;

		$table_name = $this->get_queueentrytable_tablename();

		if ( ATKPTools::str_contains( $stat, atkp_queue_entry_status::PREPARED, false ) ) {
			$query = $wpdb->prepare( "SELECT * FROM $table_name WHERE queue_id = %d and status in (" . $stat . ") order by id limit 0,10", $queue_id );
		} else {
			$query = $wpdb->prepare( "SELECT * FROM $table_name WHERE queue_id = %d and status in (" . $stat . ") group by queue_id,post_id,post_type,functionname order by id limit 0,10", $queue_id );
		}

		$result = $wpdb->get_results( $query, ARRAY_A );

		$list         = array();
		$shopid       = - 1;
		$functionname = '';
		foreach ( $result as $ent ) {
			$entry = $this->get_queueentry_from_array( $ent );
			if ( $shopid == - 1 ) {
				$shopid = $entry->shop_id;
			}
			if ( $functionname == '' ) {
				$functionname = $entry->functionname;
			}

			if ( $shopid != $entry->shop_id || $functionname != $entry->functionname ) {
				break;
			}

			$list[] = $entry;
		}

		return $list;
	}

	public function get_old_list() {
		global $wpdb;

		$tablename = self::get_queuetable_tablename();

		$days = intval( atkp_options::$loader->get_queue_clean_days() );

		if ( $days == 0 ) {
			return 0;
		}

		$sql = "SELECT id FROM {$tablename} where  DATE(createdon) <= CURDATE() - INTERVAL $days DAY";

		$result = $wpdb->get_results( $sql, 'ARRAY_A' );

		return $result;
	}

	public function get_list( $per_page = 5, $page_number = 1, $orderby = '', $order = '' ) {

		global $wpdb;

		$tablename = self::get_queuetable_tablename();

		$sql = "SELECT * FROM {$tablename}";

		if ( ! empty( $orderby ) ) {
			$sql .= ' ORDER BY ' . esc_sql( $orderby );
			$sql .= ! empty( $order ) ? ' ' . esc_sql( $order ) : ' ASC';
		}

		$sql .= " LIMIT $per_page";
		$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;


		$result = $wpdb->get_results( $sql, 'ARRAY_A' );

		return $result;
	}

	public function get_report( $date, $limit = 200 ) {
		global $wpdb;

		$tablename = self::get_queueentrytable_tablename();

		$sql = "SELECT * FROM {$tablename} where createdon >= %s and status in ('error')";

		if ( ! empty( $orderby ) ) {
			$sql .= ' ORDER BY ' . esc_sql( $orderby );
			$sql .= ! empty( $order ) ? ' ' . esc_sql( $order ) : ' ASC';
		}

		$sql .= " LIMIT " . $limit;


		$result = $wpdb->get_results( $wpdb->prepare( $sql, $date ), 'ARRAY_A' );

		return $result;
	}

	public function get_list_entry( $queue_id, $post_id = 0, $filter = null, $per_page = 5, $page_number = 1, $orderby = '', $order = '' ) {

		global $wpdb;

		$tablename = self::get_queueentrytable_tablename();

		$sql = "SELECT * FROM {$tablename} where " . ( $post_id > 0 ? " post_id = $post_id" : "queue_id=$queue_id" ) . ( $filter == 'error' ? ' and status in ("error", "not_processed")' : '' );

		if ( ! empty( $orderby ) ) {
			$sql .= ' ORDER BY ' . esc_sql( $orderby );
			$sql .= ! empty( $order ) ? ' ' . esc_sql( $order ) : ' ASC';
		}

		$sql .= " LIMIT $per_page";
		$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;


		$result = $wpdb->get_results( $sql, 'ARRAY_A' );

		return $result;
	}

	public function get_total() {
		global $wpdb;

		$table_name = $this->get_queuetable_tablename();

		$query = "SELECT count(*) as cnt FROM $table_name  ";

		$result = $wpdb->get_results( $query, ARRAY_A );

		$cnt = count( $result ) > 0 ? intval( $result[0]['cnt'] ) : 0;

		return $cnt;
	}

	public function load_queue( $queue_id ) {
		global $wpdb;

		$table_name = $this->get_queuetable_tablename();

		$query = $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d ", $queue_id );

		$result = $wpdb->get_results( $query, ARRAY_A );

		return $this->get_queue_from_array( $result[0] );
	}

	public function get_active_queue() {
		global $wpdb;

		$table_name = $this->get_queuetable_tablename();

		$query = $wpdb->prepare( "SELECT * FROM $table_name WHERE status = %s ", atkp_queue_status::ACTIVE );

		$result = $wpdb->get_results( $query, ARRAY_A );

		if ( count( $result ) > 0 ) {
			return $this->get_queue_from_array( $result[0] );
		} else {
			return null;
		}
	}

	public function exists_notfinished() {
		global $wpdb;

		$table_name = $this->get_queuetable_tablename();

		$query = $wpdb->prepare( "SELECT count(*) as cnt FROM $table_name WHERE status = %s ", atkp_queue_status::ACTIVE );

		$result = $wpdb->get_results( $query, ARRAY_A );

		$cnt = count( $result ) > 0 ? intval( $result[0]['cnt'] ) : 0;

		if ( $cnt >= 1 ) {
			return true;
		} else {
			return false;
		}
	}

	private function get_array_from_queueentry( $queue_id, atkp_queue_entry $atkp_queue, $initial_insert = false ) {

		$data = array(
			'queue_id'          => $queue_id,
			'post_type'         => $atkp_queue->post_type,
			'post_id'           => $atkp_queue->post_id,
			'shop_id'           => $atkp_queue->shop_id,
			'functionname'      => $atkp_queue->functionname,
			'functionparameter' => $atkp_queue->functionparameter,
			'status'            => $atkp_queue->status,
			'updatedon'         => date( "Y-m-d H:i:s" ),
			'updatedmessage'    => $atkp_queue->updatedmessage,
		);

		if ( $initial_insert ) {
			$data['createdon'] = date( "Y-m-d H:i:s" );
		}

		$data = apply_filters( 'atkp_modify_queueentry_before_db_write', $data );

		return $data;
	}


	private function get_queueentry_from_array( $row ) {

		$atkp_queue                    = new atkp_queue_entry();
		$atkp_queue->id                = $row['id'];
		$atkp_queue->queue_id          = $row['queue_id'];
		$atkp_queue->post_type         = $row['post_type'];
		$atkp_queue->post_id           = $row['post_id'];
		$atkp_queue->shop_id           = $row['shop_id'];
		$atkp_queue->functionname      = $row['functionname'];
		$atkp_queue->functionparameter = $row['functionparameter'];
		$atkp_queue->status            = $row['status'];
		$atkp_queue->updatedmessage    = $row['updatedmessage'];
		$atkp_queue->updatedon         = $row['updatedon'];
		$atkp_queue->createdon         = $row['createdon'];

		return $atkp_queue;

	}

	private function get_array_from_queue( $queue_id, atkp_queue $atkp_queue, $initial_insert = false ) {

		$data = array(
			'id'             => $queue_id,
			'title'          => $atkp_queue->title,
			'status'         => $atkp_queue->status,
			'internalstatus' => $atkp_queue->internalstatus,
			'type'           => $atkp_queue->type,
			'retries'        => $atkp_queue->retries,
			'updatedon'      => date( "Y-m-d H:i:s" ),
			'updatedmessage' => $atkp_queue->updatedmessage,
		);

		if ( $initial_insert ) {
			$data['createdon'] = date( "Y-m-d H:i:s" );
		}

		$data = apply_filters( 'atkp_modify_queue_before_db_write', $data );

		return $data;
	}

	private function get_queue_from_array( $row ) {

		$atkp_queue                 = new atkp_queue();
		$atkp_queue->id             = $row['id'];
		$atkp_queue->title          = $row['title'];
		$atkp_queue->status         = $row['status'];
		$atkp_queue->internalstatus = $row['internalstatus'];
		$atkp_queue->type           = $row['type'];
		$atkp_queue->retries        = $row['retries'] == null ? 0 : $row['retries'];
		$atkp_queue->updatedmessage = $row['updatedmessage'];
		$atkp_queue->updatedon      = $row['updatedon'];
		$atkp_queue->createdon      = $row['createdon'];

		return $atkp_queue;

	}

}