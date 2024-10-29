<?php
/**
 * Created by PhpStorm.
 * User: Christof
 * Date: 01.04.2018
 * Time: 10:18
 */

/**
 * Diese Klasse aktualisiert die Listen aus den Providern
 */
class atkp_listservice {

	public function build_filter( $post_id, &$requestType, &$nodeid, &$keyword, &$asin, &$maxCount, &$sortOrder, &$filter ) {
		$source = ATKPTools::get_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_source' );

		$extendedsearchlimit = ATKPTools::get_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_extendedsearch_limit' );
		$searchlimit         = ATKPTools::get_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_search_limit' );

		$searchdepartment = ATKPTools::get_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_search_department' );
		$searchkeyword    = ATKPTools::get_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_search_keyword' );
		$searchorderby    = ATKPTools::get_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_search_orderby' );

		$nodeid    = ATKPTools::get_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_node_id' );
		$keyword   = ATKPTools::get_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_keyword' );
		$productid = ATKPTools::get_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_productid' );

		$filterfield1 = ATKPTools::get_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_filterfield1' );
		$filtertext1  = ATKPTools::get_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_filtertext1' );
		$filterfield2 = ATKPTools::get_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_filterfield2' );
		$filtertext2  = ATKPTools::get_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_filtertext2' );
		$filterfield3 = ATKPTools::get_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_filterfield3' );
		$filtertext3  = ATKPTools::get_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_filtertext3' );
		$filterfield4 = ATKPTools::get_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_filterfield4' );
		$filtertext4  = ATKPTools::get_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_filtertext4' );
		$filterfield5 = ATKPTools::get_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_filterfield5' );
		$filtertext5  = ATKPTools::get_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_filtertext5' );

		$filterfield6  = ATKPTools::get_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_filterfield6' );
		$filtertext6   = ATKPTools::get_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_filtertext6' );
		$filterfield7  = ATKPTools::get_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_filterfield7' );
		$filtertext7   = ATKPTools::get_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_filtertext7' );
		$filterfield8  = ATKPTools::get_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_filterfield8' );
		$filtertext8   = ATKPTools::get_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_filtertext8' );
		$filterfield9  = ATKPTools::get_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_filterfield9' );
		$filtertext9   = ATKPTools::get_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_filtertext9' );
		$filterfield10 = ATKPTools::get_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_filterfield10' );
		$filtertext10  = ATKPTools::get_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_filtertext10' );


		$requestType = 'Search';
		$sortOrder   = '';
		$keyword     = '';
		$maxCount    = 20;
		$asin        = '';
		$filter      = array();

		switch ( $source ) {
			case 10:
				$requestType = 'TopSellers';
				break;
			case 11:
				$requestType = 'NewReleases';
				break;
			case 20:
				$requestType = 'Search';
				$keyword     = $searchkeyword;;
				$sortOrder = $searchorderby;
				if ( $searchdepartment == '' ) {
					$nodeid = 'All';
				} else {
					$nodeid = $searchdepartment;
				}
				$maxCount = $searchlimit;
				break;
			case 30:
				$requestType = 'ExtendedSearch';
				if ( $filterfield1 != '' ) {
					$filter[ $filterfield1 ] = $filtertext1;
				}
				if ( $filterfield2 != '' ) {
					$filter[ $filterfield2 ] = $filtertext2;
				}
				if ( $filterfield3 != '' ) {
					$filter[ $filterfield3 ] = $filtertext3;
				}
				if ( $filterfield4 != '' ) {
					$filter[ $filterfield4 ] = $filtertext4;
				}
				if ( $filterfield5 != '' ) {
					$filter[ $filterfield5 ] = $filtertext5;
				}


				if ( $filterfield6 != '' ) {
					$filter[ $filterfield6 ] = $filtertext6;
				}
				if ( $filterfield7 != '' ) {
					$filter[ $filterfield7 ] = $filtertext7;
				}
				if ( $filterfield8 != '' ) {
					$filter[ $filterfield8 ] = $filtertext8;
				}
				if ( $filterfield9 != '' ) {
					$filter[ $filterfield9 ] = $filtertext9;
				}
				if ( $filterfield10 != '' ) {
					$filter[ $filterfield10 ] = $filtertext10;
				}

				$maxCount = $extendedsearchlimit;
				$nodeid   = 'All';
				break;
			case 40:
				$requestType = 'Similarity';
				$asin        = $productid;
				break;
		}

		return true;
	}

	public function mark_asinvalid( $post_ids ) {
		foreach ( $post_ids as $post_id ) {
			$shopid = ATKPTools::get_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_shopid' );

			ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_updatedon', ATKPTools::get_current_utc() );

			ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_message', 'list uses an unknown shop: ' . $shopid );
		}
	}

	/**
	 * Aktualisiert die übergebenen Produkt-Ids ab V3
	 *
	 * @param atkp_shop|null $shop Das Shopobjekt welches das Produkt angehört
	 * @param atkp_queue_entry[] $entries Die Post-Ids des AT-Produktes welches aktualisiert werden soll
	 */
	public function update_productlists_v3( $shop, $entries ) {

		//get configuration from product
		$product_ids = array();
		$asintype    = '';

		foreach ( $entries as $entry ) {
			$params = explode( ATKP_QUEUE_SEPARATOR, $entry->functionparameter );

			$asin     = $params[1];
			$asintype = $params[0];

			array_push( $product_ids, $asin );
		}

		//update/add product in table
		if ( $shop != null && $shop->provider != null ) {
			$message = null;

			try {
				$message = $shop->provider->checklogon( $shop );
			} catch ( Exception $e ) {
				$message = 'Shop Logon Exception: ' . $e->getMessage();
			}

			$atresponse = null;

			if ( $message == '' ) {
				try {
					$atresponse = $shop->provider->retrieve_products( $product_ids, strtoupper( $asintype ) );
				} catch ( Exception $e ) {
					$message = 'Request Exception: ' . $e->getMessage();
				}

			}

			if ( $message != '' || $atresponse->errormessage != '' ) {
				foreach ( $entries as $entry ) {
					$entry->updatedmessage = $message . ( $atresponse != null ? $atresponse->errormessage : '' );
					$entry->status         = atkp_queue_entry_status::ERROR;
				}

				return $entries;
			} else {
				foreach ( $entries as $entry ) {
					$params = explode( ATKP_QUEUE_SEPARATOR, $entry->functionparameter );

					$asin = $params[1];

					$errormessage = '';
					$responseitem = array();
					foreach ( $atresponse->responseitems as $item ) {
						if ( (string) $asin == (string) $item->uniqueid ) {
							$responseitem[] = $item;
						}
					}

					$notfound = false;

					if ( $message != '' ) {
						$errormessage = 'Shop error: ' . $message;
					} else if ( $atresponse->errormessage != '' ) {
						$errormessage = 'Request error: ' . $atresponse->errormessage;
					} else if ( $responseitem == null || count( $responseitem ) == 0 ) {
						$errormessage = 'Product not returned: ' . $asin;
						$notfound     = true;
					} else if ( $responseitem[0]->errormessage != '' ) {
						$errormessage = 'Item error: ' . $responseitem[0]->errormessage;
					} else if ( $responseitem[0]->productitem == null ) {
						$errormessage = 'Product not returned: ' . $asin;
						$notfound     = true;
					}

					if ( $errormessage == '' ) {
						$atkp_listtable_helper = new atkp_listtable_helper();
						foreach ( $responseitem as $res ) {
							$newshopid = $entry->shop_id;
							$found     = false;
							foreach ( $shop->children as $child ) {
								if ( $child->shopid == $res->shopid ) {
									$newshopid = $child->id;
									$found     = true;
								}
							}
							if ( ! $found ) {
								//Create shop
								$defaultshops = ATKPTools::get_post_setting( $shop->parent_id, ATKP_SHOP_POSTTYPE . '_default_shops' );

								/** @var $selectedshops atkp_shop[] */
								$selectedshops = ATKPTools::get_post_setting( $shop->parent_id, ATKP_SHOP_POSTTYPE . '_selected_shops' );
								if ( $selectedshops == null || $selectedshops == '' ) {
									$selectedshops = array();
								}

								if ( is_array( $defaultshops ) ) {
									foreach ( $defaultshops as $subshop ) {

										if ( $subshop->shopid == $res->shopid ) {
											array_push( $selectedshops, $subshop );
										}
									}
								}
								ATKPTools::set_post_setting( $shop->parent_id, ATKP_SHOP_POSTTYPE . '_selected_shops', $selectedshops );

								foreach ( $selectedshops as $subshop ) {

									$subshop->parent_id = $shop->parent_id;
									$newshopid          = atkp_shop::create_subshop( $subshop );
								}
							}
							$product_service = new atkp_productservice();

							$res->productitem = apply_filters( 'atkp_list_modify_product', $res->productitem, $entry->post_id, $newshopid );

							$product_service->update_product_price_saved( $entry->post_id, $res->productitem );

							$product_service->update_product_short_url( $entry->post_id, $res->productitem, $newshopid );

							$atkp_listtable_helper->save_productlist( $entry->post_id, $newshopid, [ $res->productitem ] );
						}
					}

					$entry->updatedmessage = $errormessage;
					if ( $notfound ) {
						$entry->status = atkp_queue_entry_status::PROCESSED;
					} else {
						$entry->status = $errormessage == '' ? atkp_queue_entry_status::SUCCESSFULLY : atkp_queue_entry_status::ERROR;
					}
				}
			}
		}

		$lists = array();
		foreach ( $entries as $entry ) {
			$lists[ $entry->post_id ] = $entry->post_id;
		}
		foreach ( $lists as $list => $val ) {
			ATKPTools::set_post_setting( $list, 'atkp_list_offerupdate', '0' );
		}


		return $entries;

	}

	/**
	 * Aktualisiert die übergebenen Listen
	 *
	 * @param atkp_queue_entry[] $entries
	 */
	public function update_lists_v3( $shop, $entries ) {

		foreach ( $entries as $entry ) {

			$titlekeywords      = null;
			$searchttitlefilter = ATKPTools::get_post_setting( $entry->post_id, ATKP_LIST_POSTTYPE . '_search_titelfilter' );
			$loadmoreoffers     = ATKPTools::get_post_setting( $entry->post_id, ATKP_LIST_POSTTYPE . '_loadmoreoffers' );
			$autoimportproducts = ATKPTools::get_post_setting( $entry->post_id, ATKP_LIST_POSTTYPE . '_autoimportproducts' );
			$autodeleteproducts = ATKPTools::get_post_setting( $entry->post_id, ATKP_LIST_POSTTYPE . '_autodeleteproducts' );

			if ( $searchttitlefilter != null ) {
				$titlekeywords = array_map( 'strtolower', explode( "\n", $searchttitlefilter ) );
			}

			if ( $shop != null ) {
				if ( $shop->provider == null ) {
					$entry->updatedmessage = 'shop api not loaded';
					$entry->status         = atkp_queue_entry_status::ERROR;

					ATKPTools::set_post_setting( $entry->post_id, ATKP_LIST_POSTTYPE . '_updatedon', ATKPTools::get_current_utc() );

					continue;
				}

				$message = null;
				try {
					$message = $shop->provider->checklogon( $shop );
				} catch ( Exception $e ) {
					$message = 'Shop Exception: ' . $e->getMessage();
				}


				$atresponse = null;

				if ( $message == '' ) {
					try {
						$requestType = '';
						$sortOrder   = '';
						$keyword     = '';
						$maxCount    = 20;
						$asin        = '';
						$filter      = array();
						$nodeid      = '';

						if ( ! $this->build_filter( $entry->post_id, $requestType, $nodeid, $keyword, $asin, $maxCount, $sortOrder, $filter ) ) {
							$message = 'filter build error';
						}

						$atresponse = $shop->provider->retrieve_list( $requestType, $nodeid, $keyword, $asin, $maxCount, $sortOrder, $filter );

					} catch ( Exception $e ) {
						$message = 'Request Exception: ' . $e->getMessage();
					}

					$errormessage = '';

					if ( $atresponse != null && $atresponse->message != '' ) {
						$errormessage = 'Request error: ' . $atresponse->message;
					}

					if ( $errormessage != '' || $message != '' ) {
						$entry->updatedmessage = $errormessage . ' ' . $message;
						$entry->status         = atkp_queue_entry_status::ERROR;


						ATKPTools::set_post_setting( $entry->post_id, ATKP_LIST_POSTTYPE . '_updatedon', ATKPTools::get_current_utc() );
						ATKPTools::set_post_setting( $entry->post_id, ATKP_LIST_POSTTYPE . '_message', $errormessage . ' ' . $message );

						continue;
					} else {
						$productlist = array();

						//wenn shop vorhanden dann listeneinträge schreiben
						$atresponse->listid = $entry->post_id;

						if ( $atresponse->products != null ) {
							$prefiltered = array();

							if ( $titlekeywords != null && count( $titlekeywords ) > 0 ) {
								$deletekeywords = array();
								$addkeywords    = array();

								foreach ( $titlekeywords as $titlekeyword ) {
									if ( $titlekeyword == '' ) {
										continue;
									}

									if ( substr( $titlekeyword, 0, 1 ) == '-' ) {
										array_push( $deletekeywords, substr( $titlekeyword, 1 ) );
									} else {
										array_push( $addkeywords, $titlekeyword );
									}
								}

								foreach ( $atresponse->products as $azproduct ) {
									$title             = ATKPTools::clear_string( $azproduct->title );
									$azproduct->shopid = $shop->id;


									$addit = count( $addkeywords ) == 0;

									foreach ( $deletekeywords as $del ) {
										if ( $this->str_contains( $title, $del, false ) ) {
											$addit = false;
											break;
										}
									}

									foreach ( $addkeywords as $add ) {
										if ( $this->str_contains( $title, $add, false ) ) {
											$addit = true;
											break;
										}
									}


									if ( $addit ) {
										array_push( $prefiltered, $azproduct );
									}
								}

							} else {

								foreach ( $atresponse->products as $azproduct ) {
									$azproduct->shopid = $shop->id;
									$prefiltered[]     = $azproduct;
								}
							}

							foreach ( $prefiltered as $azproduct ) {
								$item          = array();
								$item['type']  = 'product';
								$item['value'] = $azproduct;

								array_push( $productlist, $item );
							}
						}

						$productlist = apply_filters( 'atkp_list_modify_productlist', $productlist, $entry->post_id );

						if ( $autodeleteproducts ) {
							$this->autodelete_products( $shop, $entry->post_id, $productlist );
						} else if ( $autoimportproducts ) {
							$this->autoimport_products( $shop, $entry->post_id, $productlist, $entry->queue_id );
						}

						$listentrymessage = $this->update_list_entries( $entry->post_id, $productlist );
						if ( $listentrymessage != '' && $errormessage != '' ) {
							$errormessage = $listentrymessage;
						}

						ATKPTools::set_post_setting( $entry->post_id, ATKP_LIST_POSTTYPE . '_node_caption', $atresponse->browsenodename );
					}


					$entry->updatedmessage = $errormessage;
					$entry->status         = $errormessage == '' ? atkp_queue_entry_status::SUCCESSFULLY : atkp_queue_entry_status::ERROR;

					if ( $entry->status == atkp_queue_entry_status::SUCCESSFULLY ) {
						$loadmoreoffers = ATKPTools::get_post_setting( $entry->post_id, 'atkp_list_loadmoreoffers' );
						if ( $loadmoreoffers ) {
							ATKPTools::set_post_setting( $entry->post_id, 'atkp_list_offerupdate', '1' );
						}
					}

				} else {
					$entry->updatedmessage = $message;
					$entry->status         = atkp_queue_entry_status::ERROR;
				}


				ATKPTools::set_post_setting( $entry->post_id, ATKP_LIST_POSTTYPE . '_updatedon', ATKPTools::get_current_utc() );
				ATKPTools::set_post_setting( $entry->post_id, ATKP_LIST_POSTTYPE . '_message', $entry->updatedmessage );

			} else {

				$products = ATKPTools::get_post_setting( $entry->post_id, ATKP_LIST_POSTTYPE . '_products' );
				$prdarray = explode( "\n", $products );

				$productlist = array();
				foreach ( $prdarray as $azproduct ) {
					$prdid      = intval( $azproduct );
					$poststatus = get_post_status( $prdid );

					if ( ! $poststatus || ( $poststatus != 'draft' && $poststatus != 'publish' ) ) {
						continue;
					}

					$item          = array();
					$item['type']  = 'productid';
					$item['value'] = $prdid;

					array_push( $productlist, $item );
				}

				$errormessage = $this->update_list_entries( $entry->post_id, $productlist );

				$entry->updatedmessage = $errormessage;
				$entry->status         = $errormessage == '' ? atkp_queue_entry_status::SUCCESSFULLY : atkp_queue_entry_status::ERROR;

				ATKPTools::set_post_setting( $entry->post_id, ATKP_LIST_POSTTYPE . '_updatedon', ATKPTools::get_current_utc() );
				ATKPTools::set_post_setting( $entry->post_id, ATKP_LIST_POSTTYPE . '_message', $entry->updatedmessage );
			}
		}


		return $entries;
	}

	private function str_contains( $string, $searchstring, $caseSensitive = true ) {
		return ATKPTools::str_contains( $string, $searchstring, $caseSensitive );
	}


	private function autodelete_products( $shop, $post_id, $productlist ) {
		foreach ( $productlist as $item ) {
			$type  = $item['type'];
			$value = $item['value'];

			switch ( $type ) {
				case "product":
					$prdfound = atkp_product::loadbyasin( $value->asin );

					if ( $prdfound != null ) {
						wp_delete_post($prdfound->productid);
					}

					break;
				case "productid":
					//lokales produkt? dann natürlich nicht anlegen
					break;
			}
		}

	}

	private function autoimport_products( $shop, $post_id, $productlist, $queue_id ) {


		$productservice = new atkp_productservice();

		$product_list             = array();
		$atkp_producttable_helper = new atkp_producttable_helper();

		foreach ( $productlist as $item ) {
			$type  = $item['type'];
			$value = $item['value'];

			$added = false;
			switch ( $type ) {
				case "product":
					//$prdfound = atkp_product::loadbyasin( $value->asin );

					$value->title = ATKPTools::clear_string( $value->title );

					$post_id              = apply_filters( 'atkp_find_product', 0, $shop->id, $value->asin, $value->ean, $value->title, $value->brand, $value->mpn );

					if ( $post_id <= 0 ) {
						$status = get_option( ATKP_PLUGIN_PREFIX . '_defaultproductstate', 'draft' );


						//produkt mit der eindeutigen ID ist nicht in der Datenbank. Also, neu anlegen!
						$product_id = ATKPTools::create_product( $value->title, $shop->id, $value->asin, $status, 'ASIN' );

						$atkp_producttable_helper->save_products( $product_id, $shop->id, $queue_id, [ $value ] );

						//$productservice->update_product_postdata( $product_id, $value, $value->asin, 'ASIN', true, true, true, true, true, true );
						ATKPTools::set_post_setting( $product_id, ATKP_PRODUCT_POSTTYPE . '_updatedon', ATKPTools::get_current_utc() );
						ATKPTools::set_post_setting( $product_id, ATKP_PRODUCT_POSTTYPE . '_message', '' );

						$prdfound = atkp_product::load( $product_id );

						$post_id = $prdfound->productid;
						if ( $status == 'publish' ) {
							ATKPTools::publish_product( $product_id );
						}

						$productservice->export_products( array( $product_id ) );

						ob_start();
						do_action( 'atkp_product_updated', $product_id, null );
						$content = ob_get_contents();
						ob_end_clean();
					}

					if ( $post_id > 0 ) {
						$itemnew          = array();
						$itemnew['type']  = 'productid';
						$itemnew['value'] = $post_id;

						$product_list[] = $itemnew;
						$added          = true;
					}
					break;
				case "productid":
					//lokales produkt? dann natürlich nicht anlegen
					break;
			}
			if ( ! $added ) {
				$product_list[] = $item;
			}
		}

		do_action( 'atkp_product_autoimport', $product_list, $post_id );

		return $product_list;
	}

	private function update_list_postdata( $post_id, $mylist ) {

		try {
			$mylist = apply_filters( 'atkp_retrieve_list', $mylist );

			if ( ! isset( $mylist ) || $mylist == null ) {
				throw new Exception( 'list is null: ' . $post_id );
			}

			//ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_listurl', $mylist->listurl );
			ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_node_caption', $mylist->browsenodename );


			ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_message', null );
		} catch ( Exception $e ) {
			ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_message', 'Error: ' . $e->getMessage() );
		}
	}

	private function update_list_entries( $post_id, $productlist ) {

		try {
			$listtable = new atkp_listtable_helper();

			if ( $productlist == null || count( $productlist ) == 0 ) {
				$listtable->clear_list( $post_id );

				//ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_message', 'list has no entries' );

				ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_count', 0 );

				return 'list has no entries';
			} else {
				$listtable->save_list( $post_id, $productlist );
				//Delete old metafield values to cleanup the database
				ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_productlist', null );

				//ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_message', null );
				ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_count', count( $productlist ) );

				return '';
			}

		} catch ( Exception $e ) {
			ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_message', 'Error: ' . $e->getMessage() );
		}
	}

	/**
	 * Kürzt eine URL von einem Liste aufgrund des Shops
	 *
	 * @param int $productid Die AT-Produkt-ID (wird nicht verwendet)
	 * @param int $shopid Die Shop-ID mit welcher die URL gekürzt wird. Ist diese leer, wird die URL einfach zurückgegeben
	 * @param string $url Die URL welche gekürzt werden soll
	 *
	 * @return string
	 */
	public function shorten_url( $listid, $shopid, $url ) {
		//TODO: Wenn ShopId leer ist, dann Shopid von der Liste laden?
		if ( $shopid == '' ) {
			return $url;
		}

		$redirtype = ATKPTools::get_post_setting( $shopid, ATKP_SHOP_POSTTYPE . '_redirectiontype' );

		$apikey   = ATKPTools::get_post_setting( $shopid, ATKP_SHOP_POSTTYPE . '_apikey' );
		$apilogin = ATKPTools::get_post_setting( $shopid, ATKP_SHOP_POSTTYPE . '_apilogin' );

		switch ( $redirtype ) {
			default:
				//disabled
				break;
			case 3:
			case 4:
			case 5:
			case 6:

				$shortener = new atkp_shortener();

				$url = $shortener->shorten_url( $apikey, $apilogin, $redirtype, $url );

				break;
		}

		return $url;
	}
}