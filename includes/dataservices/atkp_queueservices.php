<?php

const ATKP_QUEUE_SEPARATOR = '|:|';

class atkp_queueservices {
	private $dataupdate_interval;
	private $datacheck_enabled;
	private $datacheck_interval;
	private $csvupdate_interval;

	public function __construct() {
		$this->dataupdate_interval = atkp_options::$loader->get_cache_duration();

		$this->datacheck_enabled  = atkp_options::$loader->get_check_enabled();
		$this->datacheck_interval = atkp_options::$loader->get_notification_interval();

		$this->csvupdate_interval = atkp_options::$loader->get_access_csv_intervall();


		add_filter( 'atkp_queue_collect_entries', array( $this, 'atkp_queue_collect_entries_product' ) );
		add_filter( 'atkp_queue_collect_entries', array( $this, 'atkp_queue_collect_entries_list' ) );
		add_filter( 'atkp_queue_collect_entries', array( $this, 'atkp_queue_collect_entries_listoffers' ) );
		add_filter( 'atkp_queue_collect_entries', array( $this, 'atkp_queue_collect_entries_product_finished' ) );

		add_filter( 'atkp_queue_process_entries_productupdate', array(
			$this,
			'atkp_queue_process_entries_productupdate'
		), 10, 2 );
		add_filter( 'atkp_queue_process_entries_listupdate', array(
			$this,
			'atkp_queue_process_entries_listupdate'
		), 10, 2 );
		add_filter( 'atkp_queue_process_entries_listproductupdate', array(
			$this,
			'atkp_queue_process_entries_listproductupdate'
		), 10, 2 );

		add_filter( 'atkp_queue_process_entries_productfinish', array(
			$this,
			'atkp_queue_process_entries_productfinish'
		), 15, 1 );


		add_action( 'atkp_datacheck_report', array( $this, 'atkp_datacheck_report' ), 10 );
	}


	public function atkp_queue_process_entries_productfinish( $entries ) {
		/** @var atkp_queue_entry[] $entries */
		foreach ( $entries as $entry ) {
			try {

				$atkp_producttable_helper = new atkp_producttable_helper();

				$ids              = $atkp_producttable_helper->get_sub_products( $entry->post_id, true );

				$has_temp_product = false;
				if ( count( $ids ) > 0 ) {
					$atkp_producttable_helper->delete_unused_products( $entry->post_id );
				} else {
					//clean price/availabity
					$atkp_producttable_helper->delete_old_productdata( $entry->post_id );
					$has_temp_product = true;
				}

				$products = $atkp_producttable_helper->load_products( $entry->post_id );


				if ( count( $products ) == 0 || $has_temp_product ) {
					$message = __( 'Products not found for search request. Open Queue History for details.', ATKP_PLUGIN_PREFIX );

					ATKPTools::set_post_setting( $entry->post_id, ATKP_PRODUCT_POSTTYPE . '_message', $message );
				} else {

					$saleprice = 0;
					foreach ( $products as $product ) {
						if ( $product->salepricefloat > 0 ) {
							$saleprice = $product->salepricefloat;
							break;
						}
					}

					if ( $saleprice == 0 ) {
						ATKPTools::set_post_setting( $entry->post_id, ATKP_PRODUCT_POSTTYPE . '_message', __( 'Product found but salesprice is empty', ATKP_PLUGIN_PREFIX ) );
					} else {
						ATKPTools::set_post_setting( $entry->post_id, ATKP_PRODUCT_POSTTYPE . '_message', '' );
					}

					$this->update_eans( $entry->post_id, $products );

					#region update title
					$post_title = get_the_title( $entry->post_id );
					$prd_title  = $products[0]->title;

					$prd_title = str_replace( "&nbsp;", " ", $prd_title );
					$prd_title = html_entity_decode( $prd_title );

					//correct post title
					if ( $post_title == '' || ( $post_title != $prd_title && atkp_options::$loader->get_update_producttitle_when_changed() ) ) {
						$post_title = $prd_title;
						$post_name  = sanitize_title( $prd_title );

						global $wpdb;

						$querystr = "
					                        SELECT $wpdb->posts.* 
					                        FROM $wpdb->posts
					                        WHERE 
					                         $wpdb->posts.post_type = '" . ATKP_PRODUCT_POSTTYPE . "'
					                        AND $wpdb->posts.post_name = '" . $post_name . "'
					                        AND $wpdb->posts.ID <> '" . $entry->post_id . "'
					                     ";

						$pageposts = $wpdb->get_results( $querystr, OBJECT );

						if ( $pageposts && count( $pageposts ) > 0 ) {
							$post_name .= '-' . time();
						}

						$wpdb->update( $wpdb->posts, array(
							'post_title' => $post_title,
							'post_name'  => $post_name
						), array( 'ID' => $entry->post_id ) );

					}

					do_action( 'atkp_product_finalization', $entry->post_id );

					#endregion
				}

				$productservice = new atkp_productservice();
				$productservice->update_product_categories( $entry->post_id );
				$productservice->update_product_mainimage( $entry->post_id );

				ob_start();
				do_action( 'atkp_product_updated', $entry->post_id, $entry );
				$content = ob_get_contents();
				ob_end_clean();

				if ( ATKPTools::str_contains( $content, 'WooCommerce' ) ) {
					$entry->status         = atkp_queue_entry_status::SUCCESSFULLY;
					$entry->updatedmessage = $content . " updated";
				} else if ( ATKPTools::str_contains( $content, 'ProductExport' ) ) {
					$entry->status         = atkp_queue_entry_status::SUCCESSFULLY;
					$entry->updatedmessage = $content . " processed";
				} else if ( $content != '' ) {
					$entry->status         = atkp_queue_entry_status::ERROR;
					$entry->updatedmessage = $content;
				} else {
					$entry->status = atkp_queue_entry_status::SUCCESSFULLY;
				}

				ATKPTools::set_post_setting( $entry->post_id, 'atkp_product_finalization', '1' );
				ATKPTools::set_post_setting( $entry->post_id, 'atkp_product_lockclear', '0' );

			} catch ( Exception $ex ) {
				$entry->status         = atkp_queue_entry_status::ERROR;
				$entry->updatedmessage = $ex->getMessage();

				ATKPTools::set_post_setting( $entry->post_id, 'atkp_product_finalization', '1' );
				ATKPTools::set_post_setting( $entry->post_id, 'atkp_product_lockclear', '0' );
			}

		}

		return $entries;
	}

	public function update_eans( $product_id, $products ) {
		$eanbase  = ATKPTools::get_post_setting( $product_id, ATKP_PRODUCT_POSTTYPE . '_ean' );
		$isbnbase = ATKPTools::get_post_setting( $product_id, ATKP_PRODUCT_POSTTYPE . '_isbn' );
		$gtinbase = ATKPTools::get_post_setting( $product_id, ATKP_PRODUCT_POSTTYPE . '_gtin' );

		$eans  = array();
		$isbns = array();
		$gtins = array();

		$eanssplit = explode( ',', $eanbase );
		foreach ( $eanssplit as $eanx ) {
			if ( $eanx != '' ) {
				$eans[ trim( $eanx ) ] = trim( $eanx );
			}
		}

		$isbnsplit = explode( ',', $isbnbase );
		foreach ( $isbnsplit as $isbnx ) {
			if ( $isbnx != '' ) {
				$isbns[ trim( $isbnx ) ] = trim( $isbnx );
			}
		}

		$gtinsplit = explode( ',', $gtinbase );
		foreach ( $gtinsplit as $gtinx ) {
			if ( $gtinx != '' ) {
				$gtins[ trim( $gtinx ) ] = trim( $gtinx );
			}
		}

		foreach ( $products as $prd ) {
			$eanssplit = explode( ',', $prd->ean );
			foreach ( $eanssplit as $eanx ) {
				if ( $eanx != '' ) {
					$eans[ trim( $eanx ) ] = trim( $eanx );
				}
			}

			$isbnsplit = explode( ',', $prd->isbn );
			foreach ( $isbnsplit as $isbnx ) {
				if ( $isbnx != '' ) {
					$isbns[ trim( $isbnx ) ] = trim( $isbnx );
				}
			}

			$gtinsplit = explode( ',', $prd->gtin );
			foreach ( $gtinsplit as $gtinx ) {
				if ( $gtinx != '' ) {
					$gtins[ trim( $gtinx ) ] = trim( $gtinx );
				}
			}
		}

		$eanlock  = ATKPTools::get_post_setting( $product_id, ATKP_PRODUCT_POSTTYPE . '_ean_lock' );
		$isbnlock = ATKPTools::get_post_setting( $product_id, ATKP_PRODUCT_POSTTYPE . '_isbn_lock' );
		$gtinlock = ATKPTools::get_post_setting( $product_id, ATKP_PRODUCT_POSTTYPE . '_gtin_lock' );

		if ( ! boolval( $eanlock ) ) {
			ATKPTools::set_post_setting( $product_id, ATKP_PRODUCT_POSTTYPE . '_ean', implode( ',', $eans ) );
		}
		if ( ! boolval( $eanlock ) ) {
			ATKPTools::set_post_setting( $product_id, ATKP_PRODUCT_POSTTYPE . '_isbn', implode( ',', $isbns ) );
		}
		if ( ! boolval( $eanlock ) ) {
			ATKPTools::set_post_setting( $product_id, ATKP_PRODUCT_POSTTYPE . '_gtin', implode( ',', $gtins ) );
		}

	}

	public function atkp_datacheck_report() {
		$this->send_message( '*** data check cronjob started ***' );

		$this->send_data_report();

		$this->send_message( '*** data check cronjob finished ***' );
	}

	public function send_data_report( $manual = false ) {
		$lastdatacheck = atkp_options::$loader->get_cron_lastdatacheck();

		if ( $lastdatacheck != '' && ! $manual ) {
			$mysqltime = date( 'Y-m-d H:i:s', $lastdatacheck );
		} else {
			$mysqltime = date( 'Y-m-d H:i:s', strtotime( '-1 week', time() ) );
		}

		//TODO: send datacheck mail
		$report = atkp_queue::get_report( $mysqltime );

		if ( $report && count( $report ) > 0 ) {
			$atkp_queue_entry_table = new atkp_queue_entry_table();
			$columns                = $atkp_queue_entry_table->get_columns();

			$tdhead = '';
			foreach ( $columns as $name => $header ) {
				$tdhead .= '<div class="cell">' . $header . '</div>';
			}
			$trbody = '';

			foreach ( $report as $report_row ) {
				$trbody .= '<div class="row">';
				foreach ( $columns as $name => $header ) {
					$val = $atkp_queue_entry_table->column_default( $report_row, $name );

					$trbody .= '<div class="cell" data-title="' . esc_attr( $header ) . '">' . $val . '</div>';
				}

				$trbody .= '</div>';
			}

			$report_template = file_get_contents( ATKP_PLUGIN_DIR . '/dist/report-template.html' );

			$htmltable = '<div class="wrapper"><div class="table"><div class="row header">' . $tdhead . '</div>' . $trbody . '</div></div>';

			$report_template = str_replace( '{atkp-report-title}', __( 'affiliate-toolkit Report', ATKP_PLUGIN_PREFIX ), $report_template );
			$htmltable       = str_replace( '{atkp-report}', $htmltable, $report_template );

			$recipient = ATKPSettings::$email_recipient;

			if ( $recipient == '' ) {
				$recipient = get_bloginfo( 'admin_email' );
			}


			$headers = array(
				'From: ' . get_bloginfo( 'name' ) . ' <' . get_bloginfo( 'admin_email' ) . '>',
				'Content-Type: text/html; charset=UTF-8'
			);

			if ( $htmltable != '' ) {
				$dir      = ATKPTools::get_uploaddir();
				$filename = $dir . '/report.html';
				file_put_contents( $filename, $htmltable );

				wp_mail( $recipient, 'affiliate-toolkit Report for ' . get_bloginfo(), __( 'Attached you can find the report.', ATKP_PLUGIN_PREFIX ), $headers, $filename );

			};

			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_cron_lastdatacheck', time() );
		}
	}

	public function atkp_queue_process_entries_productupdate( $entries, $shopid ) {
		/** @var atkp_queue_entry[] $entries */
		$productservice = new atkp_productservice();

		$shop = $shopid == '' ? null : atkp_shop::load( $shopid );

		$atkp_producttable_helper = new atkp_producttable_helper();
		foreach ( $entries as $entry ) {
			$lockclear = ATKPTools::get_post_setting( $entry->post_id, 'atkp_product_lockclear' );
			if ( $lockclear != '1' ) {
				ATKPTools::set_post_setting( $entry->post_id, 'atkp_product_lockclear', 1 );
				$atkp_producttable_helper->clear_products( $entry->post_id );
			}
		}

		$entries = $productservice->update_products_v3( $shop, $entries );

		foreach ( $entries as $entry ) {
			ATKPTools::set_post_setting( $entry->post_id, 'atkp_product_finalization', '0' );
		}

		return $entries;
	}

	public function atkp_queue_process_entries_listupdate( $entries, $shopid ) {
		/** @var atkp_queue_entry[] $entries */
		$listservice = new atkp_listservice();

		$shop = $shopid == '' ? null : atkp_shop::load( $shopid );

		$entries = $listservice->update_lists_v3( $shop, $entries );

		return $entries;
	}

	public function atkp_queue_process_entries_listproductupdate( $entries, $shopid ) {
		/** @var atkp_queue_entry[] $entries */

		$listservice = new atkp_listservice();

		$shop = $shopid == '' ? null : atkp_shop::load( $shopid );

		$entries = $listservice->update_productlists_v3( $shop, $entries );

		return $entries;
	}

	public function atkp_queue_collect_entries_product( $globallist ) {
		/** @var atkp_queue_entry[] $list */
		$list = array();
		global $wpdb;

		$updatetime  = $this->get_datatime();
		$packagesize = atkp_options::$loader->get_queue_package_size();

		$sql = "
		    SELECT {$wpdb->posts}.ID 
			FROM {$wpdb->posts} 
			
			LEFT JOIN {$wpdb->postmeta} AS mt3 ON ( {$wpdb->posts}.ID = mt3.post_id AND mt3.meta_key = 'atkp_product_updatedon') 
			
			WHERE {$wpdb->posts}.post_type = 'atkp_product' and ({$wpdb->posts}.post_status = 'publish' OR {$wpdb->posts}.post_status = 'draft')	
			AND ( mt3.post_id IS NULL OR ( mt3.meta_value = '' ) OR ( CAST(mt3.meta_value AS SIGNED) < $updatetime ) )
			
			GROUP BY {$wpdb->posts}.ID 
			ORDER BY CAST(mt3.meta_value AS SIGNED) ASC 
			
			LIMIT 0, {$packagesize}";


		$sql = apply_filters( 'atkp_queue_collect_product_sql', $sql, $updatetime, $packagesize);

		$posts_found = $wpdb->get_results( $sql, OBJECT );

		$shops = atkp_shop::get_list();

		foreach ( $posts_found as $prod ) {
			$lr = $this->generate_product_entries( $shops, $prod->ID );

			foreach ( $lr as $l ) {
				$list[] = $l;
			}
		}

		//sort list by shopid, functionname
		usort( $list, array( $this, 'sort_query_entry' ) );

		return array_merge( $globallist, $list );
	}

	public function atkp_queue_collect_entries_product_finished( $list ) {
		/** @var atkp_queue_entry[] $list */

		global $wpdb;

		$packagesize = atkp_options::$loader->get_queue_package_size();

		$sql = "SELECT SQL_CALC_FOUND_ROWS {$wpdb->posts}.ID 
            FROM {$wpdb->posts} 
            
            INNER JOIN {$wpdb->postmeta} AS mt1 ON ( {$wpdb->posts}.ID = mt1.post_id AND mt1.meta_key = 'atkp_product_finalization') 
            
            WHERE {$wpdb->posts}.post_type = 'atkp_product' and ({$wpdb->posts}.post_status = 'publish' OR {$wpdb->posts}.post_status = 'draft')
            
            AND ( (mt1.meta_value = '0' ) )
            
            ORDER BY {$wpdb->posts}.ID ASC 
            LIMIT 0, {$packagesize}";


		$posts_found = $wpdb->get_results( $sql, OBJECT );

		foreach ( $posts_found as $prod ) {
			$at                    = new atkp_queue_entry();
			$at->post_id           = $prod->ID;
			$at->shop_id           = '';
			$at->post_type         = 'atkp_product';
			$at->status            = atkp_queue_entry_status::PREPARED;
			$at->functionname      = 'productfinish';
			$at->functionparameter = 'id' . ATKP_QUEUE_SEPARATOR . $prod->ID;
			$list[]                = $at;
		}

		return $list;
	}

	/**
	 * @param atkp_queue_entry $a
	 * @param atkp_queue_entry $b
	 *
	 * @return int
	 */
	public function sort_query_entry( $a, $b ) {
		if ( $a->shop_id == $b->shop_id ) {
			return strcasecmp( $a->functionname, $b->functionname );
		}

		return ( $a->shop_id < $b->shop_id ) ? - 1 : 1;
	}

	public function atkp_queue_collect_entries_list( $globallist ) {
		/** @var atkp_queue_entry[] $list */
		$list = array();

		global $wpdb;

		$updatetime = $this->get_datatime();

		$packagesize = atkp_options::$loader->get_queue_package_size();

		$sql = "
		    SELECT {$wpdb->posts}.ID 
			FROM {$wpdb->posts} 
			
			LEFT JOIN {$wpdb->postmeta} AS mt3 ON ( {$wpdb->posts}.ID = mt3.post_id AND mt3.meta_key = 'atkp_list_updatedon') 
			
			WHERE {$wpdb->posts}.post_type = 'atkp_list' and ({$wpdb->posts}.post_status = 'publish' OR {$wpdb->posts}.post_status = 'draft')	
			AND ( mt3.post_id IS NULL OR ( mt3.meta_value = '' ) OR ( CAST(mt3.meta_value AS SIGNED) < $updatetime ) )
			
			GROUP BY {$wpdb->posts}.ID 
			ORDER BY CAST(mt3.meta_value AS SIGNED) ASC 
			LIMIT 0, {$packagesize}";


		$posts_found = $wpdb->get_results( $sql, OBJECT );

		$shops = atkp_shop::get_list();

		foreach ( $posts_found as $prod ) {
			$selectedshopid = ATKPTools::get_post_setting( $prod->ID, ATKP_LIST_POSTTYPE . '_shopid' );
			$loadmoreoffers = ATKPTools::get_post_setting( $prod->ID, ATKP_LIST_POSTTYPE . '_loadmoreoffers' );

			$ltdh = new atkp_listtable_helper();

			$listentries = $ltdh->load_list( $prod->ID, $selectedshopid );
			//get entries by EAN
			$eans = array();
			foreach ( $listentries as $product ) {
				$type = $product['type'];
				if ( $type == 'product' ) {
					/** @var atkp_product $value */
					$value = $product['value'];

					if ( $value->ean != '' ) {
						$eanssplit = explode( ',', $value->ean );
						foreach ( $eanssplit as $eanx ) {
							$eans[] = $eanx;
						}
					}
				}
			}


			$shopfound = false;
			foreach ( $shops as $shophead ) {
				/** @var atkp_shop[] $list */
				$list2 = array();

				$list2[] = $shophead;

				if ( $shophead->type == atkp_shop_type::SUB_SHOPS || $shophead->type == atkp_shop_type::MULTI_SHOPS ) {
					foreach ( $shophead->children as $xx ) {
						$list2[] = $xx;
					}
				}

				foreach ( $list2 as $shop ) {
					if ( $selectedshopid == $shop->id ) {
						$shopfound        = true;
						$at               = new atkp_queue_entry();
						$at->post_id      = $prod->ID;
						$at->shop_id      = $shop->id;
						$at->post_type    = 'atkp_list';
						$at->status       = atkp_queue_entry_status::PREPARED;
						$at->functionname = 'listupdate';

						$list[] = $at;
					}
				}
			}

			if ( ! $shopfound ) {

				$shopfound        = true;
				$at               = new atkp_queue_entry();
				$at->post_id      = $prod->ID;
				$at->shop_id      = '';
				$at->post_type    = 'atkp_list';
				$at->status       = atkp_queue_entry_status::PREPARED;
				$at->functionname = 'listupdate';

				$list[] = $at;
			}
		}

		//sort list by shopid, functionname (listupdate before listproductupdate)

		usort( $list, array( $this, 'sort_query_entry' ) );

		return array_merge( $globallist, $list );
	}

	public function atkp_queue_collect_entries_listoffers( $globallist ) {
		/** @var atkp_queue_entry[] $list */
		$list = array();

		global $wpdb;

		$sql = "
		    SELECT {$wpdb->posts}.ID 
			FROM {$wpdb->posts} 
			
			LEFT JOIN {$wpdb->postmeta} AS mt3 ON ( {$wpdb->posts}.ID = mt3.post_id AND mt3.meta_key = 'atkp_list_offerupdate') 
			
			WHERE {$wpdb->posts}.post_type = 'atkp_list' and ({$wpdb->posts}.post_status = 'publish' OR {$wpdb->posts}.post_status = 'draft')	
			AND ( CAST(mt3.meta_value AS SIGNED) = 1 ) 
			
			GROUP BY {$wpdb->posts}.ID 
			LIMIT 0, 150";


		$posts_found = $wpdb->get_results( $sql, OBJECT );

		$shops = atkp_shop::get_list();

		foreach ( $posts_found as $prod ) {
			$selectedshopid = ATKPTools::get_post_setting( $prod->ID, ATKP_LIST_POSTTYPE . '_shopid' );
			$loadmoreoffers = ATKPTools::get_post_setting( $prod->ID, ATKP_LIST_POSTTYPE . '_loadmoreoffers' );

			if ( ! $loadmoreoffers )
				continue;

			$ltdh = new atkp_listtable_helper();

			$listentries = $ltdh->load_list( $prod->ID, $selectedshopid );
			//get entries by EAN
			$eans = array();
			foreach ( $listentries as $product ) {
				$type = $product['type'];
				if ( $type == 'product' ) {
					/** @var atkp_product $value */
					$value = $product['value'];

					if ( $value->ean != '' ) {
						$eanssplit = explode( ',', $value->ean );
						foreach ( $eanssplit as $eanx ) {
							if ( ATKPTools::validate_ean( $eanx ) ) {
								$eans[] = $eanx;
							}
						}
					}
				}
			}
			//remove duplicate entries
			$eans = array_unique( $eans );

			$shopfound = false;
			foreach ( $shops as $shophead ) {
				/** @var atkp_shop[] $list */
				$list2 = array();

				$list2[] = $shophead;

				foreach ( $shophead->children as $xx ) {
					$list2[] = $xx;
				}

				foreach ( $list2 as $shop ) {
					if ( $shop->type == atkp_shop_type::SUB_SHOPS || $shop->type == atkp_shop_type::SINGLE_SHOP ) {
						if ( $selectedshopid != $shop->id ) {
							foreach ( $eans as $ean ) {
								if ( $ean == '' ) {
									continue;
								}
								$at                    = new atkp_queue_entry();
								$at->post_id           = $prod->ID;
								$at->shop_id           = $shop->id;
								$at->post_type         = 'atkp_list';
								$at->status            = atkp_queue_entry_status::PREPARED;
								$at->functionname      = 'listproductupdate';
								$at->functionparameter = 'ean' . ATKP_QUEUE_SEPARATOR . $ean;

								$list[] = $at;
							}
						}
					}
				}
			}
		}

		//sort list by shopid, functionname (listupdate before listproductupdate)

		usort( $list, array( $this, 'sort_query_entry' ) );

		return array_merge( $globallist, $list );
	}


	private function get_datatime( $datetimeformat = false ) {
		$updatetime = time();

		$dataupdateinterval = atkp_options::$loader->get_cache_duration();

		if ( $dataupdateinterval > 60 ) {
			$updatetime = strtotime( '-' . ( $dataupdateinterval / 60 ) . ' hours' );
		} else {
			$updatetime = strtotime( '-' . ( $dataupdateinterval ) . ' min' );
		}

		if ( $datetimeformat ) {
			return date( 'd.m.y H:m:s', $updatetime );
		}

		//$updatetime = ATKPTools::get_time( $updatetime, 'timestamp' );

		return $updatetime;
	}


	public function generate_product_entries( $shops, $product_id, $generate_only_main_offers = false, $generate_only_price_compare = false ) {
		// define filter for multipe options

		$shoplist = array();
		foreach ( $shops as $shophead ) {
			/** @var atkp_shop $shop */
			$shoplist[] = $shophead;

			foreach ( $shophead->children as $xx ) {
				$shoplist[] = $xx;
			}
		}

		$filters       = array();
		$main_shop_ids = array();

		for ( $x = 1; $x < ( ATKP_FILTER_COUNT + 1 ); $x ++ ) {
			$asin     = ATKPTools::get_post_setting( $product_id, ATKP_PRODUCT_POSTTYPE . '_asin' . ( $x > 1 ? '_' . $x : '' ) );
			$asintype = ATKPTools::get_post_setting( $product_id, ATKP_PRODUCT_POSTTYPE . '_asintype' . ( $x > 1 ? '_' . $x : '' ) );
			$shopid   = ATKPTools::get_post_setting( $product_id, ATKP_PRODUCT_POSTTYPE . '_shopid' . ( $x > 1 ? '_' . $x : '' ) );

			if ( $asin == null && $asintype == null && $shopid == null ) {
				continue;
			}

			$filter = array();
			if ( $shopid == '' || $shopid == 0 ) {
				continue;
			}

			$filtered_shops = array();
			if ( $shopid == - 1 ) {
				foreach ( $shoplist as $shop ) {
					$parentshop = null;
					if ( $shop->type == atkp_shop_type::CHILD_SHOP ) {
						foreach ( $shoplist as $pshop ) {
							if ( $pshop->id == $shop->parent_id ) {
								$parentshop = $pshop;
								break;
							}
						}
					}

					if ( $shop->enablepricecomparison && ( $shop->type == atkp_shop_type::MULTI_SHOPS || ( $shop->type == atkp_shop_type::CHILD_SHOP && $parentshop->type == atkp_shop_type::SUB_SHOPS ) || $shop->type == atkp_shop_type::SINGLE_SHOP ) ) {
						$filtered_shops[] = $shop;
					}
				}

			} else {
				foreach ( $shoplist as $shop ) {
					if ( $shop->id == $shopid ) {
						$filtered_shops[] = $shop;
						$main_shop_ids[]  = $shopid;
						break;
					}
				}
			}

			$filter['shop']     = $filtered_shops;
			$filter['asintype'] = $asintype == '' ? 'asin' : strtolower( $asintype );
			$filter['asin']     = $asin;

			if ( $asin != '' ) {
				$filters[] = $filter;
			}
		}

		if ( ! $generate_only_main_offers ) {
			$eans     = array();
			$eanfield = ATKPTools::get_post_setting( $product_id, ATKP_PRODUCT_POSTTYPE . '_ean' );

			if ( $eanfield != '' ) {
				$eanssplit = explode( ',', $eanfield );
				foreach ( $eanssplit as $eanx ) {
					if ( ! in_array( $eanx, $eans ) ) {
						if ( ATKPTools::validate_ean( $eanx ) ) {
							$eans[] = $eanx;
						}
					}
				}
			}

			foreach ( $eans as $ean ) {
				$filtered_shops = array();
				foreach ( $shoplist as $shop ) {
					$parentshop = null;
					if ( $shop->type == atkp_shop_type::CHILD_SHOP ) {
						foreach ( $shoplist as $pshop ) {
							if ( $pshop->id == $shop->parent_id ) {
								$parentshop = $pshop;
								break;
							}
						}
					}

					if ( ! in_array( $shop->id, $main_shop_ids ) && $shop->enablepricecomparison && ( $shop->type == atkp_shop_type::MULTI_SHOPS || ( $shop->type == atkp_shop_type::CHILD_SHOP && $parentshop->type == atkp_shop_type::SUB_SHOPS ) || $shop->type == atkp_shop_type::SINGLE_SHOP ) ) {
						$filtered_shops[] = $shop;
					}
				}

				$filter['shop']     = $filtered_shops;
				$filter['asintype'] = 'ean';
				$filter['asin']     = $ean;

				$filters[] = $filter;
			}

			$isbns     = array();
			$isbnfield = ATKPTools::get_post_setting( $product_id, ATKP_PRODUCT_POSTTYPE . '_isbn' );

			if ( $isbnfield != '' ) {
				$isbnsplit = explode( ',', $isbnfield );
				foreach ( $isbnsplit as $isbnx ) {
					if ( ! in_array( $isbnx, $isbns ) ) {
						//TODO: Validate ISBN
						if ( ATKPTools::validate_isbn( $isbnx ) ) {
							$isbns[] = $isbnx;
						}
					}
				}
			}

			foreach ( $isbns as $isbn ) {
				$filtered_shops = array();
				foreach ( $shoplist as $shop ) {
					$parentshop = null;
					if ( $shop->type == atkp_shop_type::CHILD_SHOP ) {
						foreach ( $shoplist as $pshop ) {
							if ( $pshop->id == $shop->parent_id ) {
								$parentshop = $pshop;
								break;
							}
						}
					}

					if ( ! in_array( $shop->id, $main_shop_ids ) && $shop->enablepricecomparison && ( $shop->type == atkp_shop_type::MULTI_SHOPS || ( $shop->type == atkp_shop_type::CHILD_SHOP && $parentshop->type == atkp_shop_type::SUB_SHOPS ) || $shop->type == atkp_shop_type::SINGLE_SHOP ) ) {

						$supportisbn = apply_filters( 'atkp_shop_support_isbn_search', false, $shop );

						if ( $supportisbn ) {
							$filtered_shops[] = $shop;
						}
					}
				}


				$filter['shop']     = $filtered_shops;
				$filter['asintype'] = 'isbn';
				$filter['asin']     = $isbn;

				$filters[] = $filter;
			}

			$gtins     = array();
			$gtinfield = ATKPTools::get_post_setting( $product_id, ATKP_PRODUCT_POSTTYPE . '_gtin' );

			if ( $gtinfield != '' ) {
				$gtinsplit = explode( ',', $gtinfield );
				foreach ( $gtinsplit as $gtinx ) {
					if ( ! in_array( $gtinx, $gtins ) ) {
						//TODO: Validate GTIN
						if ( ATKPTools::validate_gtin( $gtinx ) ) {
							$gtins[] = $gtinx;
						}
					}
				}
			}

			foreach ( $gtins as $gtin ) {
				$filtered_shops = array();
				foreach ( $shoplist as $shop ) {
					$parentshop = null;
					if ( $shop->type == atkp_shop_type::CHILD_SHOP ) {
						foreach ( $shoplist as $pshop ) {
							if ( $pshop->id == $shop->parent_id ) {
								$parentshop = $pshop;
								break;
							}
						}
					}

					if ( ! in_array( $shop->id, $main_shop_ids ) && $shop->enablepricecomparison && ( $shop->type == atkp_shop_type::MULTI_SHOPS || ( $shop->type == atkp_shop_type::CHILD_SHOP && $parentshop->type == atkp_shop_type::SUB_SHOPS ) || $shop->type == atkp_shop_type::SINGLE_SHOP ) ) {
						$supportgtin = apply_filters( 'atkp_shop_support_gtin_search', false, $shop );

						if ( $supportgtin ) {
							$filtered_shops[] = $shop;
						}
					}
				}

				$filter['shop']     = $filtered_shops;
				$filter['asintype'] = 'gtin';
				$filter['asin']     = $gtin;

				$filters[] = $filter;
			}
		}

		$list = array();
		foreach ( $filters as $filter ) {
			foreach ( $filter['shop'] as $shop ) {
				if ( $generate_only_price_compare && $filter['asintype'] == 'asin' ) {
					continue;
				}


				$at                    = new atkp_queue_entry();
				$at->post_id           = $product_id;
				$at->shop_id           = $shop->id;
				$at->post_type         = 'atkp_product';
				$at->status            = atkp_queue_entry_status::PREPARED;
				$at->functionname      = 'productupdate';
				$at->functionparameter = $filter['asintype'] . ATKP_QUEUE_SEPARATOR . $filter['asin'];

				$found = false;
				foreach ( $list as $e ) {
					if ( $e->functionname == $at->functionname && $e->functionparameter == $at->functionparameter && $at->post_id == $e->post_id && $at->shop_id == $e->shop_id ) {
						$found = true;
						break;
					}
				}

				if ( ! $found ) {
					$list[] = $at;
				}
			}
		}

		return $list;
	}


	public function send_message( $message ) {
		if ( class_exists( 'WP_CLI' ) ) {
			WP_CLI::log( $message );
		} else {
			echo esc_html__( $message . '<br />' . PHP_EOL, ATKP_PLUGIN_PREFIX );
		}

		if ( ATKPLog::$logenabled ) {
			ATKPLog::LogDebug( $message );
		}
	}


	public static function do_manual_list_update( $post_id, $queue_title = '' ) {

		$shopid         = ATKPTools::get_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_shopid' );
		$loadmoreoffers = ATKPTools::get_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_loadmoreoffers' );

		$shop = $shopid == '' ? null : atkp_shop::load( $shopid );

		$entry = new atkp_queue_entry();

		$entry->post_id      = $post_id;
		$entry->shop_id      = $shopid;
		$entry->post_type    = ATKP_LIST_POSTTYPE;
		$entry->status       = atkp_queue_entry_status::PREPARED;
		$entry->functionname = 'listupdate';

		$queue = ATKPTools::create_queue( 'atkp_list', $queue_title, false, [ $entry ], atkp_queue_status::ERROR );
		try {
			$entries = apply_filters( 'atkp_queue_process_entries_listupdate', [ $entry ], $entry->shop_id );

			foreach ( $entries as $entry ) {
				$entry->save();
			}

			if ( $loadmoreoffers && $shopid != '' ) {
				ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_updatedon', '' );
			}

			$queue->status         = atkp_queue_status::SUCCESSFULLY;
			$queue->updatedmessage = "updated";
			$queue->save();
		} catch ( Exception $e ) {
			$queue->status         = atkp_queue_status::ERROR;
			$queue->updatedmessage = $e->getMessage();
			$queue->save();
		}
	}

	public static function do_manual_product_update( $post_id, $queue_title = '' ) {
		$atkp_prodcttable_helper = new atkp_producttable_helper();
		$atkp_prodcttable_helper->clear_products( $post_id );

		$shops = atkp_shop::get_list();

		$atkp_queueservices = new atkp_queueservices();
		$lr                 = $atkp_queueservices->generate_product_entries( $shops, $post_id, true );

		$newgroup = array();
		foreach ( $lr as $x ) {
			if ( ! isset( $newgroup[ $x->shop_id ] ) ) {
				$newgroup[ $x->shop_id ] = array();
			}

			$newgroup[ $x->shop_id ][] = $x;

		}

		if ( count( $newgroup ) == 0 ) {
			$atkp_prodcttable_helper->delete_unused_products( $post_id );
		}

		$queue = ATKPTools::create_queue( 'atkp_product', $queue_title, false, $lr, atkp_queue_status::SUCCESSFULLY );

		atkp_timeouter::start( 20 );
		try {
			foreach ( $newgroup as $shopid => $list ) {
				$entries = apply_filters( 'atkp_queue_process_entries_productupdate', $list, $shopid );

				foreach ( $entries as $entry ) {
					$entry->save();
				}
			}

			$at                    = new atkp_queue_entry();
			$at->post_id           = $post_id;
			$at->shop_id           = 0;
			$at->post_type         = 'atkp_product';
			$at->status            = atkp_queue_entry_status::PREPARED;
			$at->functionname      = 'productfinish';
			$at->functionparameter = 'id' . ATKP_QUEUE_SEPARATOR . $post_id;

			apply_filters( 'atkp_queue_process_entries_productfinish', [ $at ] );

			$queue->entries = [ $at ];

			$queue->status         = atkp_queue_status::SUCCESSFULLY;
			$queue->updatedmessage = "updated";
			$queue->save();

			atkp_timeouter::end();

		} catch ( Exception $e ) {
			atkp_timeouter::end();

			$queue->status         = atkp_queue_status::ERROR;
			$queue->updatedmessage = $e->getMessage();
			$queue->save();
		}

		ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_updatedon', '' );
	}
}