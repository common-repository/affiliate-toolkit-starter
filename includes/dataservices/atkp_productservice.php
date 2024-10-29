<?php
/**
 * Created by PhpStorm.
 * User: Christof
 * Date: 01.04.2018
 * Time: 09:22
 */


/**
 * Diese Klasse aktualisiert die Single-Produkte aus den Providern
 */
class atkp_productservice {

	/**
	 * Exportiert die übergebenen Produkt-IDs
	 *
	 * @param array $post_ids Die Post-Ids des AT-Produktes welches aktualisiert werden soll
	 */
	public function export_products( $post_ids ) {

		do_action( 'atkp_export_products', $post_ids );

	}

	public function mark_asinvalid( $post_ids ) {
		foreach ( $post_ids as $post_id ) {
			$shopid = ATKPTools::get_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_shopid' );

			ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_updatedon', ATKPTools::get_current_utc() );

			ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_message', 'product uses an unknown shop: ' . $shopid );
		}
	}

	/**
	 * Aktualisiert die übergebenen Produkt-Ids ab V3
	 *
	 * @param atkp_shop|null $shop Das Shopobjekt welches das Produkt angehört
	 * @param atkp_queue_entry[] $entries Die Post-Ids des AT-Produktes welches aktualisiert werden soll
	 */
	public function update_products_v3( $shop, $entries ) {

		//get configuration from product
		$product_keys = array();

		foreach ( $entries as $entry ) {

			$params = explode( ATKP_QUEUE_SEPARATOR, $entry->functionparameter );

			$asin     = $params[1];
			$asintype = $params[0];

			$product_keys[ $asintype ][] = $asin;
		}

		$atkp_producttable_helper = new atkp_producttable_helper();

		//update/add product in table
		if ( $shop == null ) {
			return $entries;
		}

		if ( $shop->provider == null ) {
			foreach ( $entries as $entry ) {
				$entry->updatedmessage = 'shop api not loaded';
				$entry->status         = atkp_queue_entry_status::ERROR;
			}

			return $entries;
		}

		$message = null;

		try {
			$message = $shop->provider->checklogon( $shop );
		} catch ( Exception $e ) {
			$message = 'Shop Logon Exception: ' . $e->getMessage();
		}

		if ( $message != '' ) {
			foreach ( $entries as $entry ) {
				$entry->updatedmessage = $message;
				$entry->status         = atkp_queue_entry_status::ERROR;
			}

			return $entries;
		}

		foreach ( $product_keys as $asintype => $product_ids ) {

			$atresponse = null;

			try {
				$atresponse = $shop->provider->retrieve_products( $product_ids, strtoupper( $asintype ) );

			} catch ( Exception $e ) {
				$message = 'Request Exception: ' . $e->getMessage();
			}

			if ( $message != '' || $atresponse->errormessage != '' ) {
				foreach ( $entries as $entry ) {
					$params2 = explode( ATKP_QUEUE_SEPARATOR, $entry->functionparameter );

					$asintype2 = $params2[0];

					if ( $asintype2 == $asintype ) {
						$entry->updatedmessage = ( $atresponse != null ? $atresponse->errormessage : '' );
						$entry->status         = atkp_queue_entry_status::ERROR;
					}
				}
			} else {
				foreach ( $entries as $entry ) {
					$params2 = explode( ATKP_QUEUE_SEPARATOR, $entry->functionparameter );

					$asintypex = $params2[0];
					$asin      = $params2[1];

					$errormessage = '';
					$responseitem = array();
					foreach ( $atresponse->responseitems as $item ) {
						if ( $asin == (string) $item->uniqueid && strtoupper( $asintypex ) == strtoupper( $item->uniquetype ) ) {
							$responseitem[] = $item;
						}
					}


					$notfound = false;

					if ( $message != '' ) {
						$errormessage = sprintf( __( 'Shop error: %s', ATKP_PLUGIN_PREFIX ), $message );
					} else if ( $atresponse->errormessage != '' ) {
						$errormessage = sprintf( __( 'Request error: %s', ATKP_PLUGIN_PREFIX ), $atresponse->errormessage );
					} else if ( $responseitem == null || count( $responseitem ) == 0 ) {
						$errormessage = sprintf( __( 'Product not returned: %s', ATKP_PLUGIN_PREFIX ), $asin );;
						$notfound = true;
						if ( $asintypex != $asintype ) {
							continue;
						}
					} else if ( $responseitem[0]->errormessage != '' ) {
						$errormessage = sprintf( __( 'Item error: %s', ATKP_PLUGIN_PREFIX ), $responseitem[0]->errormessage );
					} else if ( $responseitem[0]->productitem == null ) {
						$errormessage = sprintf( __( 'Product not returned: %s', ATKP_PLUGIN_PREFIX ), $asin );
						$notfound     = true;
					} /* else if( $responseitem[0]->productitem->salepricefloat <= 0)  {
						$errormessage = sprintf(__('Sale price is equal 0: %s',ATKP_PLUGIN_PREFIX), $asin);
						$notfound = true;
					}*/

					if ( $errormessage == '' ) {

						foreach ( $responseitem as $res ) {
							$newshopid = $entry->shop_id;

							if ( $res->shopid != '' ) {
								$found      = false;
								$shopsfound = [];
								foreach ( $shop->children as $child ) {
									if ( $child->shopid == $res->shopid ) {
										$newshopid    = $child->id;
										$found        = true;
										$shopsfound[] = $child->id;
									}
								}

								if ( ! $found && $shop->autogeneratesubshops ) {

									//Create shop
									$defaultshops = ATKPTools::get_post_setting( $shop->parent_id, ATKP_SHOP_POSTTYPE . '_default_shops' );

									if ( ! is_array( $defaultshops ) ) {
										$defaultshops = array();
									}

									/** @var $selectedshops atkp_shop[] */
									$selectedshops = ATKPTools::get_post_setting( $shop->parent_id, ATKP_SHOP_POSTTYPE . '_selected_shops' );
									if ( $selectedshops == null || $selectedshops == '' ) {
										$selectedshops = array();
									}

									$found = false;
									if ( is_array( $defaultshops ) ) {
										foreach ( $defaultshops as $subshop ) {
											if ( $subshop->shopid == $res->shopid ) {
												$selectedshops[] = $subshop;
												$found           = true;
											}
										}
									}
									if ( ! $found && $res->shopname != '' ) {
										$newsubshop                     = new atkp_shop();
										$newsubshop->shopid             = $res->shopid;
										$newsubshop->title              = $res->shopname;
										$newsubshop->customlogourl      = $res->shoplogo;
										$newsubshop->customsmalllogourl = $res->shoplogo;

										$defaultshops[] = $newsubshop;
										ATKPTools::set_post_setting( $shop->parent_id, ATKP_SHOP_POSTTYPE . '_default_shops', $defaultshops );
										$selectedshops[] = $newsubshop;
									}

									ATKPTools::set_post_setting( $shop->parent_id, ATKP_SHOP_POSTTYPE . '_selected_shops', $selectedshops );

									foreach ( $selectedshops as $subshop ) {
										$subshop->parent_id = $shop->parent_id;
										$newshopid          = atkp_shop::create_subshop( $subshop );
										$children           = $shop->children;
										$children[]         = atkp_shop::load( $newshopid );
										$shop->children     = $children;
									}
								} else if ( ! $found && $newshopid != $entry->shop_id ) {
									$newshopid = null;
								}
							}

							if ( $newshopid != null ) {
								$res->productitem = apply_filters( 'atkp_product_modify_product', $res->productitem, $entry->post_id, $newshopid );

								$this->update_product_price_saved( $entry->post_id, $res->productitem );
								$this->update_product_short_url( $entry->post_id, $res->productitem, $newshopid );
								$this->update_custom_fields( $entry->post_id, $res->productitem );

								$res->productitem->isupdated = true;
								$atkp_producttable_helper->save_products( $entry->post_id, $newshopid, $entry->queue_id, [ $res->productitem ] );

								do_action( 'atkp_product_save_product', $entry->post_id, $newshopid, $res->productitem );
							}
						}
					}

					if ( $errormessage == '' && $responseitem[0]->productitem->salepricefloat <= 0 ) {
						$errormessage = sprintf( __( 'Sale price is equal 0: %s', ATKP_PLUGIN_PREFIX ), $asin );
						$notfound     = true;
					}

					$entry->updatedmessage = $errormessage;
					if ( $notfound ) {
						$entry->status = atkp_queue_entry_status::PROCESSED;
					} else {
						$entry->status = $errormessage == '' ? atkp_queue_entry_status::SUCCESSFULLY : atkp_queue_entry_status::ERROR;
					}

					ATKPTools::set_post_setting( $entry->post_id, ATKP_PRODUCT_POSTTYPE . '_updatedon', ATKPTools::get_current_utc() );
				}
			}
		}


		return $entries;
	}


	private function str_contains( $string, $array, $caseSensitive = true ) {
		$strfound          = false;
		$containsPosFilter = false;

		foreach ( $array as $txt ) {
			$startswith = substr( $txt, 0, 1 );

			if ( $startswith == '-' ) {
				$txt = substr( $txt, 1 );
			} else {
				$containsPosFilter = true;
			}


			if ( stripos( $string, $txt ) !== false ) {
				$strfound = true;
			}

			if ( $strfound && $startswith == '-' ) {
				return false;
			}
		}

		return $containsPosFilter ? $strfound : true;

		//$stripedString = $caseSensitive ? str_replace($array, '', $string) : str_ireplace($array, '', $string);
		//return strlen($stripedString) !== strlen($string);
	}

	public function update_product_status( $post_id ) {


		do_action( 'atkp_product_update_status', $post_id);



	}

	public function update_product_categories( $post_id ) {


		$category = get_option( ATKP_PLUGIN_PREFIX . '_product_category_taxonomy', strtolower( __( 'Productcategory', ATKP_PLUGIN_PREFIX ) ) );

		$atkp = atkp_product_collection::load( $post_id );

		if ( $atkp == null ) {
			return;
		}

		$prd  = $atkp->get_main_product();

		$productgroup = $prd->productgroup;

		if ( atkp_options::$loader->get_productgroupascategory() ) {

			if ( atkp_options::$loader->get_productgroupdeleteoldentries() ) {
				//delete existing entries
			}

			if ( atkp_options::$loader->get_productgroupsplitchar() != '' ) {
				//split to sub categories
				$productgroup = explode( atkp_options::$loader->get_productgroupsplitchar(), $productgroup );

			}

			ATKPTools::check_taxonomy( $post_id, $category, $productgroup, false );


		}
	}

	function string_cleaner( $string ) {
		// Leerzeichen durch Minus ersetzen
		$string = str_replace( ' ', '-', $string );

		// Umlaute ersetzen
		$umlauts   = array( '/ä/', '/ü/', '/ö/', '/Ä/', '/Ü/', '/Ö/', '/ß/' );
		$noumlauts = array( 'ae', 'ue', 'oe', 'Ae', 'Ue', 'Oe', 'ss' );
		$string    = preg_replace( $umlauts, $noumlauts, $string );

		// die restlichen Sonderzeichen entfernen
		$string = preg_replace( '/[^A-Za-z0-9\-]/', '', $string );

		// mehrere Minusse durch ein einziges ersetzen
		return preg_replace( '/-+/', '-', $string );
	}

	/**
	 * Aktualisiert ein angelegtes Produkt mit dem übergebenen Produkt. Je Nach Refresh-Parameter wird der jeweilige Teil aktualisiert.
	 *
	 * @param int $post_id Die Post-ID des AT-Produktes
	 * @param atkp_product $product Das atkp_product (aus dem Shopprovider)
	 * @param string $asin Die ASIN, ean,title, articlenumber mit welcher das atkp_product geladen wurde
	 * @param string $asintype Der ASIN-Typ welche $asin ist
	 * @param bool $refreshproductinfo
	 * @param bool $refreshpriceinfo
	 * @param bool $refreshreviewinfo
	 * @param bool $refreshimages
	 * @param bool $refreshproducturl
	 * @param bool $refreshmoreoffers
	 * @param bool $dontUpdatetitle Wenn Ein Titel beim erstmaligen Import angegeben wurde, soll dieser nicht überschrieben werden
	 */
	public function update_product_postdata( $post_id, $product, $asin, $asintype, $refreshproductinfo, $refreshpriceinfo, $refreshvariations, $refreshreviewinfo, $refreshimages, $refreshproducturl, $dontUpdatetitle = false ) {
		try {
			$product = apply_filters( 'atkp_retrieve_product', $product, $asin, $asintype );

			if ( ! isset( $product ) || $product == null ) {
				throw new Exception( 'product is null: ' . $post_id );
			}

			//atkp_product_updateproduct

			$updateproduct = ATKPTools::get_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_updateproduct' );

			if ( $updateproduct != '' ) {
				//set update flags
				$refreshproductinfo = true;
				$refreshpriceinfo   = true;
				$refreshvariations  = true;
				$refreshreviewinfo  = true;
				$refreshimages      = true;
				$refreshproducturl  = true;

				ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_updateproduct', '' );
			}

			ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_asin_caption', $product->title );

			if ( $refreshproductinfo == true ) {
				$post_loaded = get_post( $post_id );

				$posttitle = html_entity_decode( $product->title );
				if ( $post_loaded->post_title == '' ) {
					global $wpdb;

					$charset = $wpdb->get_col_charset( $wpdb->posts, 'post_title' );
					if ( 'utf8' === $charset ) {
						$posttitle = wp_encode_emoji( $posttitle );
					}

					$data = array(
						'post_title' => $posttitle,
						'post_name'  => sanitize_title( $product->title )
					);
					$data = wp_unslash( $data );

					do_action( 'pre_post_update', $post_id, $data );

					$wpdb->update( $wpdb->posts, $data, array( 'ID' => $post_id ) );
				}

				if ( ! $dontUpdatetitle ) {
					ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_title', $posttitle );
				}
				ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_description', $product->description );

				ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_ean', $product->ean );
				ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_isbn', $product->isbn );
				ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_mpn', $product->mpn );
				ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_brand', $product->brand );


				$pg = ATKPTools::get_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_productgroup' );
				if ( $pg == '' ) {
					ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_productgroup', $product->productgroup );
				}
				ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_releasedate', $product->releasedate );
				ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_manufacturer', $product->manufacturer );
				ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_author', $product->author );
				ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_numberofpages', $product->numberofpages );
				ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_features', $product->features );

				$this->update_product_categories( $post_id );
			}

			if ( isset( $product->customfields ) && $product->customfields != null && sizeof( $product->customfields ) > 0 ) {

				$allfields = array();

				$newfields = atkp_udfield::load_fields();

				foreach ( $newfields as $field ) {
					$field->isold = true;
					array_push( $allfields, $field );
				}


				$groups = ATKPTools::get_fieldgroups_by_productid( $post_id );

				foreach ( $groups as $group ) {

					$fields = ATKPTools::get_post_setting( $group->ID, ATKP_FIELDGROUP_POSTTYPE . '_fields' );

					foreach ( $fields as $newfield ) {

						array_push( $allfields, $newfield );
					}
				}

				foreach ( $product->customfields as $name => $value ) {
					try {
						$udf = null;


						foreach ( $allfields as $field ) {
							if ( $field->name == $name ) {
								$udf = $field;
								break;
							}
						}

						if ( $udf == null ) {
							continue;
						}

						$fieldvalue = sanitize_text_field( $value );

						if ( $udf->type == 6 ) {

							//update taxonomy
							ATKPTools::check_taxonomy( $post_id, $udf->name, $fieldvalue, false );

						} else {
							if ( $field->isold ) {
								$fieldname = 'customfield_' . $udf->name;
							} else {
								$fieldname = 'cf_' . $udf->name;
							}

							if ( $udf->format == 'number' ) {
								//extract number from string

								$fieldvalue = preg_replace( "/[^0-9]/", "", $fieldvalue );

								$fieldvalue = intval( $fieldvalue );
							}

							//$oldval =ATKPTools::get_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE.'_'.$fieldname);
							//if($oldval == '')
							ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_' . $fieldname, $fieldvalue );


						}

					} catch ( Exception $e ) {

					}
				}

			}

			if ( $refreshimages == true ) {
				ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_smallimageurl', $product->smallimageurl );
				ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_mediumimageurl', $product->mediumimageurl );
				ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_largeimageurl', $product->largeimageurl );
				ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_images', $product->images );
			}


			if ( $refreshproducturl == true ) {
				ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_producturl', $this->shorten_url( $post_id, $product->shopid, $product->producturl ) );
				ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_addtocarturl', $this->shorten_url( $post_id, $product->shopid, $product->addtocarturl ) );
				ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_customerreviewsurl', $this->shorten_url( $post_id, $product->shopid, $product->customerreviewurl ) );
			}

			if ( $refreshreviewinfo == true ) {
				//ratings werden nur überschrieben wenn welche vorhanden sind
				if ( $product->rating != '' && $product->rating != '0' ) {
					ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_rating', $product->rating );
				}
				if ( $product->reviewcount != '' && $product->reviewcount != '0' ) {
					ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_reviewcount', $product->reviewcount );
				}
			}

			if ( $refreshvariations == true ) {
				ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_parentasin', $product->parentasin );
				ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_variationname', $product->variationname );
				ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_variations', $product->variations );
			}


			if ( $refreshpriceinfo == true ) {

				$this->update_product_price_saved( $post_id, $product );

				ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_listpricefloat', $product->listpricefloat );
				ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_salepricefloat', $product->salepricefloat );
				ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_shippingfloat', $product->shippingfloat );

				ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_listprice', $product->listprice );
				ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_saleprice', $product->saleprice );
				ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_availability', $product->availability );
				ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_shipping', $product->shipping );
				ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_isprime', $product->isprime );
				ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_iswarehouse', $product->iswarehouse );

				if ( $product->saleprice == '' ) {
					throw new Exception( __( 'sale\'s price is empty', ATKP_PLUGIN_PREFIX ) );
				}

				do_action( 'atkp_product_save_pricefields', $post_id );
			}

			ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_message', null );
		} catch ( Exception $e ) {
			ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_message', 'Error: ' . $e->getMessage() );

			$this->reset_product_postdata( $post_id, $refreshpriceinfo );
		}

	}

	/**
	 * Update custom fields from provider
	 *
	 * @param $post_id
	 * @param atkp_product $product
	 *
	 * @return void
	 */
	public function update_custom_fields( $post_id, $product ) {

		if ( $product == null || $product->customfields == null ) {
			return;
		}

		$customfields = apply_filters( 'atkp_product_modify_customfields', $product->customfields, $post_id );

		if ( count( $customfields ) > 0 ) {

			foreach ( $customfields as $fieldname => $fieldvalue ) {
				$updated = false;

				$newfields = atkp_udfield::load_fields();

				foreach ( $newfields as $field ) {
					if ( $field->name == $fieldname ) {
						$fieldvalue = str_replace( $field->suffix, '', $fieldvalue );

						ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_customfield_' . $field->name, $fieldvalue );
						$updated = true;
						break;
					}
				}

				if ( ! $updated ) {
					$groups = ATKPTools::get_fieldgroups();

					foreach ( $groups as $group ) {
						$fields = ATKPTools::get_post_setting( $group->ID, ATKP_FIELDGROUP_POSTTYPE . '_fields' );

						if ( $fields != null ) {
							foreach ( $fields as $field ) {

								if ( $field->name == $fieldname ) {
									$fieldvalue = str_replace( $field->suffix, '', $fieldvalue );
									ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_cf_' . $field->name, $fieldvalue );
									break;
								}
							}
						}
					}
				}
			}
		}
	}

	public function update_product_price_saved( $post_id, $product, $save = false ) {
		if ( $product->title != '' ) {
			$product->title = str_replace( '&nbsp;', ' ', $product->title );
		}

		if ( $product->salepricefloat > 0 && $product->listpricefloat > 0 ) {
			//$product->amountsavedfloat = 0;
			//$product->percentagesaved = 0;
			if ( $product->amountsavedfloat <= 0 ) {
				//calculate $product->amountsavedfloat

				$product->amountsavedfloat = $product->listpricefloat - $product->salepricefloat;

				if ( $product->amountsavedfloat <= 0 ) {
					$product->amountsavedfloat = 0;
				} else {
					$product->amountsavedfloat = round( $product->amountsavedfloat, 2 );
				}

				$product->amountsaved = $product->amountsavedfloat;
			}

			if ( $product->percentagesaved <= 0 && $product->amountsavedfloat > 0 ) {
				$product->percentagesaved = round( ( $product->amountsavedfloat / $product->listpricefloat ) * 100, 0 );
			}
		}

		if ( $product->percentagesaved > 0 )
			$product->percentagesaved = round( $product->percentagesaved, 0 );

		if ( $save ) {
			ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_amountsaved', $product->amountsaved );
			ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_amountsavedfloat', $product->amountsavedfloat );
			ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_percentagesaved', $product->percentagesaved );
		}
	}

	/**
	 * Shorten URL if activated in shop settings
	 *
	 * @param $post_id
	 * @param atkp_product $product
	 * @param $shop_id
	 *
	 * @return void
	 */
	public function update_product_short_url( $post_id, atkp_product $product, $shop_id ) {
		$shop = atkp_shop::load( $shop_id );

		if ( $shop != null && $shop->redirection_type != atkp_redirection_type::DISABLED ) {
			$shortener                  = new atkp_shortener();
			$product->producturl        = $shortener->shorten_url( $product->producturl, $product->title, $shop->redirection_type, $shop->redirection_apikey );
			$product->customerreviewurl = $shortener->shorten_url( $product->customerreviewurl, $product->title . ' Reviews', $shop->redirection_type, $shop->redirection_apikey );
			$product->addtocarturl      = $shortener->shorten_url( $product->addtocarturl, $product->title . ' Add to Cart', $shop->redirection_type, $shop->redirection_apikey );
		}
	}


	public function reset_product_postdata( $post_id, $refreshpriceinfo ) {
		//den verkaufspreis zurücksetzen wenn ein Fehler aufgetreten ist
		if ( $refreshpriceinfo == true ) {
			ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_listpricefloat', 0 );
			ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_amountsavedfloat', 0 );
			ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_salepricefloat', 0 );
			ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_shippingfloat', 0 );

			ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_listprice', '' );
			ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_amountsaved', '' );
			ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_percentagesaved', '' );
			ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_saleprice', '' );
			ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_availability', '' );
			ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_shipping', '' );
			ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_isprime', false );


			ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_isexported', false );
		}
	}

	/**
	 * Kürzt eine URL von einem Produkt aufgrund des Shops
	 *
	 * @param int $productid Die AT-Produkt-ID (wird nicht verwendet)
	 * @param int $shopid Die Shop-ID mit welcher die URL gekürzt wird. Ist diese leer, wird die URL einfach zurückgegeben
	 * @param string $url Die URL welche gekürzt werden soll
	 *
	 * @return string
	 */
	public function shorten_url( $productid, $shopid, $url ) {
		//TODO: Wenn ShopId leer ist, dann Shopid vom Produkt laden?
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




	/**
	 * Importiert das jeweilige Hauptbild vom AT-Produkt in die Medienbibliothek oder aktualisiert dieses
	 *
	 * @param int $post_id Die ID des AT-Produktes welches das Hauptbild aktualisiert wird
	 *
	 * @throws Exception Wenn der Import-Imagemode nicht untersützt wird, wird eine Exception erzeugt
	 */
	public function update_product_mainimage( $post_id ) {

		$dontimportimage = ATKPTools::get_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_dontimportmainimage' );

		if ( $dontimportimage ) {
			return;
		}

		$importProductimage = get_option( ATKP_PLUGIN_PREFIX . '_product_importimage', 0 );
		$poststatus         = get_post_status( $post_id );

		if ( $importProductimage != 0 && ( $poststatus == 'publish' || $poststatus == 'draft' ) ) {

			$prodcoll = atkp_product_collection::load( $post_id );
			$product  = $prodcoll->get_main_product();
			switch ( $importProductimage ) {
				case 1:
					//importiert das bild in die bibliothek
					$imageurl = atkp_product::get_mainimage( $product, 'largetosmall' );
					$title    = $product->title;


					if ( $imageurl != '' ) {
						ATKPTools::set_featured_image( $imageurl, $title == '' ? $post_id : $title, $post_id );
					}
					break;
				case 3:
					$imageurl = atkp_product::get_mainimage( $product, 'largetosmall' );
					$title    = $product->title;

					$newurl = atkp_formatter::replace_image_url( $product->shopid, $imageurl, $product->productid, $product->listid );

					//fifu plugin

					if ( is_plugin_active( 'fifu-premium/fifu-premium.php' ) ) {
						fifu_dev_set_image( $post_id, $newurl );
					} elseif ( is_plugin_active( 'featured-image-from-url/featured-image-from-url.php' ) ) {
						fifu_dev_set_image( $post_id, $newurl );
					}

					break;
				case 2:
					//old mode.. do nothing...
					break;
				default:
					//throw new Exception( 'unknown imagemode: ' . $importProductimage );
					break;
			}

		}

	}

}