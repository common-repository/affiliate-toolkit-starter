<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! defined( 'ATKP_VERSION_PRODUCTTABLE' ) ) {
	define( 'ATKP_VERSION_PRODUCTTABLE', 2 );
}

class atkp_producttable_helper {
	public function __construct() {
		$this->check_table_structure();

	}

	/**
	 * Gibt den internen Tabellenname inkl. Prefix für _offertable zurück
	 * @return string Der Tabellenname
	 */
	function get_producttable_tablename() {
		global $wpdb;

		return $wpdb->prefix . 'atkp_products';
	}

	public function exists_table() {
		global $wpdb;
		$tablename = $this->get_producttable_tablename();
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

		$current_version_db = get_option( ATKP_PLUGIN_PREFIX . '_version_posts_products' );
		//$current_version_db = 0;
		$table_name      = $this->get_producttable_tablename();
		$charset_collate = ATKPTools::get_wp_charset_collate();

		//older or not even set
		if ( $current_version_db < ATKP_VERSION_PRODUCTTABLE || $current_version_db == false || $override ) {

			$sql = "CREATE TABLE $table_name (
	                    `id` bigint (50) NOT NULL AUTO_INCREMENT,
	                    `product_id` bigint( 50 ) NOT NULL,
	                    `shop_id` bigint( 50 ) NULL,
	                    `queue_id` bigint( 50 ) NULL,	                    
	                    `title` varchar(700) NULL ,
	                    `description` text NULL ,	                                                                                            
	                    `asin` varchar( 250 ) NULL,       
	                    `parentasin` varchar( 250 ) NULL,
	                    `ean` varchar( 250 ) NULL,
	                    `isbn` varchar( 250 ) NULL,
	                    `gtin` varchar( 250 ) NULL,
	                    `mpn` varchar( 250 ) NULL,	                    
	                    `brand` varchar( 250 ) NULL,
	                    `productgroup` varchar( 600 ) NULL,
	                    `releasedate` varchar( 250 ) NULL,	                                                        
	                    `addtocarturl` varchar( 1200 ) NULL,
	                    `producturl` varchar( 1200 ) NULL,	                                                        
	                    `smallimageurl` varchar( 600 ) NULL,
	                    `mediumimageurl` varchar( 600 ) NULL,
	                    `largeimageurl` varchar( 600 ) NULL,
	                    `images` text NULL,	                                                        
	                    `manufacturer` varchar( 250 ) NULL,
	                    `author` varchar( 250 ) NULL,
	                    `numberofpages` int(20) NULL ,
	                    `features` text NULL,
	                    `variations` text NULL,				                    
	                    `variationname` varchar( 250 ) NULL,	                    
	                    `rating` decimal(20,2) NULL ,
	                    `reviewcount` int(20) NULL ,
	                    `customerreviewurl` varchar( 600 ) NULL,		                    			
	                    `listprice` varchar( 250 ) NULL,	                    
	                    `listpricefloat` decimal(20,2) NULL ,			                    
	                    `amountsaved` varchar( 250 ) NULL,
	                    `amountsavedfloat` decimal(20,2) NULL ,		                    
	                    `percentagesaved` varchar( 250 ) NULL,
	                    `percentagesavedfloat` decimal(20,2) NULL ,              
	                    `saleprice` varchar( 250 ) NULL,
	                    `salepricefloat` decimal(20,2) NULL ,
	                    `shipping` varchar( 250 ) NULL ,
	                    `shippingfloat` decimal(20,2) NULL ,
	                    `availability` varchar( 250 ) NULL,	                    
	                    `isprime` int(1) NULL ,
	                    `iswarehouse` int(1) NULL,	                    
	                    `cpc` varchar( 150 ) NULL,	                    
	                    `cpcfloat` decimal(20,2) NULL ,	                                                        
	                    `updatedon` datetime NULL ,
	                    `updatedmessage` varchar(700) NULL,
	                                  
	                    `isupdated` int(1) NULL ,    
	                    `haserror` int(1) NULL ,                      
	                    `baseprice` varchar( 150 ) NULL,             
	                    `basepricefloat` decimal(20,2) NULL,                         
	                    `baseunit` varchar( 150 ) NULL,  
	                    
	                    
	                    PRIMARY KEY (id)	                     
	                    ) $charset_collate";

			dbDelta( $sql );

			$wpdb->query( "ALTER TABLE $table_name ADD INDEX {$table_name}_idx_product_id ( `product_id`);" );
			$wpdb->query( "ALTER TABLE $table_name ADD INDEX {$table_name}_idx_ean ( `ean`);" );
			$wpdb->query( "ALTER TABLE $table_name ADD INDEX {$table_name}_idx_asin ( `asin`);" );

			update_option( ATKP_PLUGIN_PREFIX . '_version_posts_products', ATKP_VERSION_PRODUCTTABLE );

		}
	}

	public function clear_products( $product_id ) {
		global $wpdb;

		$table_name = $this->get_producttable_tablename();

		$affected = $wpdb->query( "update $table_name set isupdated = 0 WHERE product_id=$product_id" );

		return $affected;
	}

	public function clear_products_importset( $product_id ) {
		global $wpdb;

		$table_name = $this->get_producttable_tablename();

		$affected = $wpdb->query( "update $table_name set isupdated = 1 WHERE product_id=$product_id and importset_key is not null" );
		$affected = $wpdb->query( "update $table_name set isupdated = 0 WHERE product_id=$product_id and importset_key is null" );


		return $affected;
	}


	public function get_sub_products( $product_id, $was_updated = false ) {
		global $wpdb;

		$table_name = $this->get_producttable_tablename();

		$result = $wpdb->get_results( "select id from $table_name WHERE product_id=$product_id and isupdated " . ( $was_updated ? '=' : '<>' ) . " 1", ARRAY_A );

		$post_ids = array();

		if ( $result ) {
			foreach ( $result as $row ) {
				$post_ids[] = $row['id'];
			}
		}

		return $post_ids;
	}

	public function delete_unused_products( $product_id ) {
		global $wpdb;

		$table_name = $this->get_producttable_tablename();

		$affected = $wpdb->query( "delete from $table_name WHERE product_id=$product_id and isupdated <> 1" );

		return $affected;
	}

	public function delete_old_productdata( $product_id ) {
		global $wpdb;

		$table_name = $this->get_producttable_tablename();

		$affected = $wpdb->query( "update $table_name set listprice = null, listpricefloat = null, amountsaved = null, amountsavedfloat = null, percentagesaved =null, percentagesavedfloat = null, saleprice = null, salepricefloat = null, shipping = null, shippingfloat= null, availability = null, isprime = 0, cpc = null, cpcfloat = null, baseprice = null, basepricefloat = null  WHERE product_id=$product_id and isupdated <> 1" );


		return $affected;
	}

	public function load_products_by_asin( $asin, $shop_id = '' ) {
		global $wpdb;

		$table_name = $this->get_producttable_tablename();

		if ( $shop_id != '' ) {
			$query = $wpdb->prepare( "SELECT product_id FROM $table_name inner join {$wpdb->posts} p on p.ID = product_id WHERE asin = %s and shop_id = %s GROUP BY product_id", $asin, intval( $shop_id ) );
		} else {
			$query = $wpdb->prepare( "SELECT product_id FROM $table_name inner join {$wpdb->posts} p on p.ID = product_id WHERE asin = %s GROUP BY product_id", $asin );
		}

		$result = $wpdb->get_results( $query, ARRAY_A );

		$post_ids = array();

		if ( $result ) {
			foreach ( $result as $row ) {
				$post_ids[] = $row['product_id'];
			}
		}

		return $post_ids;
	}

	public function load_products_by_ean( $ean ) {
		global $wpdb;

		$table_name = $this->get_producttable_tablename();

		$query = $wpdb->prepare( "SELECT product_id FROM $table_name inner join {$wpdb->posts} p on p.ID = product_id WHERE ean like %s GROUP BY product_id", '%' . $wpdb->esc_like( $ean ) . '%' );

		$result = $wpdb->get_results( $query, ARRAY_A );

		$post_ids = array();

		if ( $result ) {
			foreach ( $result as $row ) {
				$post_ids[] = $row['product_id'];
			}
		}

		return $post_ids;
	}

	public function load_products_by_title( $title ) {
		global $wpdb;

		$table_name = $this->get_producttable_tablename();

		$query = $wpdb->prepare( "SELECT product_id FROM $table_name inner join {$wpdb->posts} p on p.ID = product_id WHERE title like %s GROUP BY product_id", '%' . $wpdb->esc_like( $title ) . '%' );

		$result = $wpdb->get_results( $query, ARRAY_A );

		$post_ids = array();

		if ( $result ) {
			foreach ( $result as $row ) {
				$post_ids[] = $row['product_id'];
			}
		}

		return $post_ids;
	}



	public function exists_product( $product_id, $shop_id ) {
		global $wpdb;

		$table_name = $this->get_producttable_tablename();

		$query = $wpdb->prepare( "SELECT count(*) as cnt FROM $table_name WHERE product_id = %s and shop_id = %s ", $product_id, $shop_id );

		$result = $wpdb->get_results( $query, ARRAY_A );

		$cnt = count( $result ) > 0 ? intval( $result[0]['cnt'] ) : 0;

		if ( $cnt >= 1 ) {
			return true;
		} else {
			return false;
		}
	}

	public function save_products( $product_id, $shop_id, $queue_id, $products ) {
		global $wpdb;

		$table_name = $this->get_producttable_tablename();

		foreach ( $products as $product ) {
			$product->shopid = $shop_id;

			$product->title        = html_entity_decode( $product->title );
			$product->features     = html_entity_decode( $product->features );
			$product->description  = html_entity_decode( $product->description );
			$product->productgroup = html_entity_decode( $product->productgroup );

			$data = $this->get_array_from_product( $product_id, $queue_id, $product, false );

			if ( $this->exists_product( $product_id, $shop_id ) ) {
				$wpdb->update(
					$table_name,
					$data,
					array( 'shop_id' => $shop_id, 'product_id' => $product_id )
				);

				$product->id = $product_id;

			} else {
				$wpdb->insert(
					$table_name,
					$data
				);

				$product->id = $wpdb->insert_id;
			}

			if ( $wpdb->last_error !== '' || $product->id == 0 ) {
				//wenn ein Fehler aufgetreten ist, dann sollte man die beschreibungen mal fixen

				$data = $this->get_array_from_product( $product_id, $queue_id, $product, true );

				if ( $this->exists_product( $product_id, $shop_id ) ) {
					$wpdb->update(
						$table_name,
						$data,
						array( 'shop_id' => $shop_id, 'product_id' => $product_id )
					);

				} else {
					$wpdb->insert(
						$table_name,
						$data
					);

					$product->id = $wpdb->insert_id;
				}
			}
		}
	}

	/**
	 * Load Products
	 *
	 * @param $product_id
	 *
	 * @return atkp_product[]
	 */
	public function load_products( $product_id ) {
		global $wpdb;

		$table_name = $this->get_producttable_tablename();

		$query = $wpdb->prepare( "SELECT * FROM $table_name WHERE product_id = %d ", $product_id );

		$result = $wpdb->get_results( $query, ARRAY_A );

		return $this->get_product_from_array( $result );
	}

	private function fix_sql_field( $value, $maxlength = 0 ) {
		if ( $value == '' ) {
			return '';
		}

		//removing unused strings
		$value = strip_tags( $value, [ '<b>', '<br>', '<ul>', '<li>', '<i>' ] );
		//removing different encoding characters (https://stackoverflow.com/questions/7186550/what-is-this-character-%C3%82-and-how-do-i-remove-it-with-php)
		$value = preg_replace( '/[^(\x20-\x7F)\x0A\x0D]*/', '', $value );

		//validate maxlength of field
		if ( $maxlength > 0 ) {
			if ( $value != null && strlen( $value ) > $maxlength ) {
				return substr( $value, 0, $maxlength );
			}
		}

		return $value;
	}

	private function get_array_from_product( $product_id, $queue_id, atkp_product $atkp_product, $remove_character = false ) {

		$data = array(
			'product_id'           => $product_id,
			'updatedon'            => date( "Y-m-d H:i:s" ),
			'queue_id'             => $queue_id,
			'shop_id'              => $atkp_product->shopid,
			'title'                => $remove_character ? $this->fix_sql_field( $atkp_product->title ) : $atkp_product->title,
			'description'          => $remove_character ? $this->fix_sql_field( $atkp_product->description ) : $atkp_product->description,
			'asin'                 => $atkp_product->asin,
			'parentasin'           => $atkp_product->parentasin,
			'ean'                  => ( strlen( $atkp_product->ean ) > 250 ? substr( $atkp_product->ean, 0, 250 ) : $atkp_product->ean ),
			'isbn'                 => $atkp_product->isbn,
			'gtin'                 => $atkp_product->gtin,
			'mpn'                  => $atkp_product->mpn,
			'brand'                => $atkp_product->brand,
			'productgroup'         => $atkp_product->productgroup,
			'releasedate'          => $atkp_product->releasedate,
			'addtocarturl'         => $atkp_product->addtocarturl,
			'producturl'           => $atkp_product->producturl,
			'smallimageurl'        => $atkp_product->smallimageurl,
			'mediumimageurl'       => $atkp_product->mediumimageurl,
			'largeimageurl'        => $atkp_product->largeimageurl,
			'images'               => $atkp_product->images == null ? null : serialize( $atkp_product->images ),
			'manufacturer'         => $atkp_product->manufacturer,
			'author'               => $atkp_product->author,
			'numberofpages'        => $atkp_product->numberofpages,
			'features'             => $remove_character ? $this->fix_sql_field( $atkp_product->features ) : $atkp_product->features,
			'variations'           => $atkp_product->variations == null ? null : serialize( $atkp_product->variations ),
			'variationname'        => $atkp_product->variationname == null ? null : serialize( $atkp_product->variationname ),
			'rating'               => $atkp_product->rating,
			'reviewcount'          => $atkp_product->reviewcount,
			'customerreviewurl'    => $atkp_product->customerreviewurl,
			'listprice'            => $atkp_product->listprice,
			'listpricefloat'       => $atkp_product->listpricefloat,
			'amountsaved'          => $atkp_product->amountsaved,
			'amountsavedfloat'     => $atkp_product->amountsavedfloat,
			'percentagesaved'      => $atkp_product->percentagesaved,
			'percentagesavedfloat' => $atkp_product->percentagesavedfloat,
			'saleprice'            => $atkp_product->saleprice,
			'salepricefloat'       => $atkp_product->salepricefloat,
			'shipping'             => $atkp_product->shipping,
			'shippingfloat'        => $atkp_product->shippingfloat,
			'availability'         => $atkp_product->availability,
			'isprime'              => $atkp_product->isprime,
			'iswarehouse'          => $atkp_product->iswarehouse,
			'cpc'                  => $atkp_product->cpc,
			'cpcfloat'             => $atkp_product->cpcfloat,


			'baseprice'      => $atkp_product->baseprice,
			'basepricefloat' => $atkp_product->basepricefloat,
			'baseunit'       => $atkp_product->baseunit,
			'isupdated'      => $atkp_product->isupdated,
			'haserror'       => $atkp_product->haserror,

		);


		$data = apply_filters( 'atkp_modify_product_before_db_write', $data );

		return $data;
	}

	private function get_product_from_array( $result ) {

		$products = array();

		if ( $result ) {
			foreach ( $result as $row ) {

				$atkp_product                    = new atkp_product();
				$atkp_product->productid         = $row['product_id'];
				$atkp_product->shopid            = $row['shop_id'];
				$atkp_product->title             = $row['title'];
				$atkp_product->description       = $row['description'];
				$atkp_product->asin              = $row['asin'];
				$atkp_product->parentasin        = $row['parentasin'];
				$atkp_product->ean               = $row['ean'];
				$atkp_product->isbn              = $row['isbn'];
				$atkp_product->gtin              = $row['gtin'];
				$atkp_product->mpn               = $row['mpn'];
				$atkp_product->brand             = $row['brand'];
				$atkp_product->productgroup      = $row['productgroup'];
				$atkp_product->releasedate       = $row['releasedate'];
				$atkp_product->addtocarturl      = $row['addtocarturl'];
				$atkp_product->producturl        = $row['producturl'];
				$atkp_product->smallimageurl     = $row['smallimageurl'];
				$atkp_product->mediumimageurl    = $row['mediumimageurl'];
				$atkp_product->largeimageurl     = $row['largeimageurl'];
				$atkp_product->images            = $row['images'] == null ? array() : unserialize( $row['images'] );
				$atkp_product->manufacturer      = $row['manufacturer'];
				$atkp_product->author            = $row['author'];
				$atkp_product->numberofpages     = $row['numberofpages'];
				$atkp_product->features          = $row['features'];
				$atkp_product->variations        = $row['variations'] == null ? array() : unserialize( $row['variations'] );
				$atkp_product->variationname = $row['variationname'] == null ? array() : unserialize( $row['variationname'] );
				$atkp_product->rating            = $row['rating'];
				$atkp_product->reviewcount       = $row['reviewcount'];
				$atkp_product->customerreviewurl = $row['customerreviewurl'];
				$atkp_product->listprice         = $row['listprice'];
				$atkp_product->listpricefloat    = $row['listpricefloat'];
				$atkp_product->amountsaved       = $row['amountsaved'];
				$atkp_product->amountsavedfloat  = $row['amountsavedfloat'];
				$atkp_product->percentagesaved   = $row['percentagesaved'];
				$atkp_product->percentagesavedfloat = floatval( $row['percentagesavedfloat'] );
				$atkp_product->saleprice         = $row['saleprice'];
				$atkp_product->salepricefloat    = $row['salepricefloat'];
				$atkp_product->shipping          = $row['shipping'];
				$atkp_product->shippingfloat     = $row['shippingfloat'];
				$atkp_product->availability      = $row['availability'];
				$atkp_product->isprime           = $row['isprime'];
				$atkp_product->iswarehouse       = $row['iswarehouse'];
				$atkp_product->updatedon         = strtotime( $row['updatedon'] );

				$atkp_product->cpc      = $row['cpc'];
				$atkp_product->cpcfloat = $row['cpcfloat'];


				$atkp_product->baseprice      = $row['baseprice'];
				$atkp_product->basepricefloat = $row['basepricefloat'];
				$atkp_product->baseunit       = $row['baseunit'];
				$atkp_product->isupdated      = $row['isupdated'];
				$atkp_product->haserror       = $row['haserror'];

				$products[] = $atkp_product;
			}
		}

		return $products;

	}

}