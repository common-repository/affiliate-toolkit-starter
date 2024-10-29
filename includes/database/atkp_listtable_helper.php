<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! defined( 'ATKP_VERSION_LISTTABLE' ) ) {
	define( 'ATKP_VERSION_LISTTABLE', 2 );
}

class atkp_listtable_helper {
	public function __construct() {
		$this->check_table_structure();


	}


	/**
	 * Gibt den internen Tabellenname inkl. Prefix für _offertable zurück
	 * @return string Der Tabellenname
	 */
	function get_listtable_tablename() {
		global $wpdb;

		return ( $wpdb->prefix . 'atkp_lists' );;
	}

	public function exists_table() {
		global $wpdb;
		$tablename = $this->get_listtable_tablename();
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

		$current_version_db = get_option( ATKP_PLUGIN_PREFIX . '_version_posts_lists' );
		//$current_version_db = 0;
		$table_name      = $this->get_listtable_tablename();
		$charset_collate = ATKPTools::get_wp_charset_collate();

		//older or not even set
		if ( $current_version_db < ATKP_VERSION_LISTTABLE || $current_version_db == false || $override ) {

			$sql = "CREATE TABLE $table_name (
	                    `id` bigint (50) NOT NULL AUTO_INCREMENT,
	                    `list_id` bigint (50) NOT NULL,
	                    `shop_id` bigint( 50 ) NULL,
	                    `product_id` bigint( 50 ) NULL,
	                    `list_idx` int(1) NOT NULL ,	                    
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
	                    `updatedon` datetime NULL ,
	                    `updatedmessage` varchar(700) NULL,
	                    	                       
	                    `haserror` int(1) NULL ,                      
	                    `baseprice` varchar( 150 ) NULL,             
	                    `basepricefloat` decimal(20,2) NULL,                         
	                    `baseunit` varchar( 150 ) NULL,  
	                    
	                    PRIMARY KEY (id) 		                     
	                    ) {$charset_collate}";

			dbDelta( $sql );


			$wpdb->query( "ALTER TABLE $table_name ADD INDEX {$table_name}_idx_list_id ( `list_id`);" );
			$wpdb->query( "ALTER TABLE $table_name ADD INDEX {$table_name}_idx_product_id ( `product_id`);" );

			update_option( ATKP_PLUGIN_PREFIX . '_version_posts_lists', ATKP_VERSION_LISTTABLE );

		}
	}

	public function clear_list( $list_id ) {
		global $wpdb;

		$table_name = $this->get_listtable_tablename();

		$affected = $wpdb->query( "DELETE FROM $table_name WHERE list_id=$list_id" );

		return $affected;
	}

	public function save_list( $list_id, $products ) {
		global $wpdb;

		$table_name = $this->get_listtable_tablename();

		$this->clear_list( $list_id );

		$list_idx = 1;
		foreach ( $products as $product ) {

			$data = $this->get_array_from_product( $list_id, $list_idx ++, $product, false );

			$wpdb->insert(
				$table_name,
				$data
			);

			if ( $wpdb->last_error !== '' || $wpdb->insert_id == 0 ) {

				$data = $this->get_array_from_product( $list_id, $list_idx ++, $product, true );

				$result = $wpdb->insert(
					$table_name,
					$data
				);
			}

			$id = $wpdb->insert_id;
		}
	}

	public function save_productlist( $list_id, $shop_id, $products ) {
		global $wpdb;

		$table_name = $this->get_listtable_tablename();

		foreach ( $products as $product ) {
			$product->title        = html_entity_decode( $product->title );
			$product->features     = html_entity_decode( $product->features );
			$product->description  = html_entity_decode( $product->description );
			$product->productgroup = html_entity_decode( $product->productgroup );

			$data            = $this->get_array_from_product( $list_id, 0, $product, false );
			$data['shop_id'] = $shop_id;

			$wpdb->insert(
				$table_name,
				$data
			);
			if ( $wpdb->last_error !== '' ) {

				$data            = $this->get_array_from_product( $list_id, 0, $product, true );
				$data['shop_id'] = $shop_id;

				$wpdb->insert(
					$table_name,
					$data
				);

			}

			$id = $wpdb->insert_id;

		}
	}

	public function load_list( $list_id, $shop_id = '' ) {
		global $wpdb;

		$table_name = $this->get_listtable_tablename();

		if ( $shop_id != '' ) {
			$query = $wpdb->prepare( "SELECT * FROM $table_name WHERE list_id = %d and shop_id = %d order by list_idx ", $list_id, $shop_id );
		} else {
			$query = $wpdb->prepare( "SELECT * FROM $table_name WHERE list_id = %d order by list_idx ", $list_id );
		}

		$result = $wpdb->get_results( $query, ARRAY_A );

		return $this->get_product_from_array( $result );
	}

	private function get_array_from_product( $list_id, $list_idx, $productarray, $remove_character = false ) {

		if ( is_a( $productarray, 'atkp_product' ) ) {
			$prd                   = $productarray;
			$productarray          = array();
			$productarray['type']  = 'product';
			$productarray['value'] = $prd;
		}

		$type = $productarray['type'];

		if ( $type == 'product' ) {
			/** @var atkp_product $atkp_product */

			$atkp_product = $productarray['value'];

			$data = array(
				'list_id'   => $list_id,
				'list_idx'  => $list_idx,
				'updatedon' => date( "Y-m-d H:i:s" ),

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
				'variationname' => $atkp_product->variationname == null ? null : serialize( $atkp_product->variationname ),
				'rating'               => $atkp_product->rating,
				'reviewcount'          => $atkp_product->reviewcount,
				'customerreviewurl'    => $atkp_product->customerreviewurl,
				'listprice'            => $atkp_product->listprice,
				'listpricefloat'       => $atkp_product->listpricefloat,
				'amountsaved'          => $atkp_product->amountsaved,
				'amountsavedfloat'     => $atkp_product->amountsavedfloat,
				'percentagesaved'      => $atkp_product->percentagesaved,
				'percentagesavedfloat' => $atkp_product->percentagesaved,
				'saleprice'            => $atkp_product->saleprice,
				'salepricefloat'       => $atkp_product->salepricefloat,
				'shipping'             => $atkp_product->shipping,
				'shippingfloat'        => $atkp_product->shippingfloat,
				'availability'         => $atkp_product->availability,
				'isprime'              => $atkp_product->isprime,
				'iswarehouse'          => $atkp_product->iswarehouse,


				'baseprice'      => $atkp_product->baseprice,
				'basepricefloat' => $atkp_product->basepricefloat,
				'baseunit'       => $atkp_product->baseunit,
				'haserror'       => $atkp_product->haserror,
			);

		} else if ( $type == 'productid' ) {
			$productid = $productarray['value'];

			$data = array(
				'list_id'    => $list_id,
				'product_id' => intval( $productid ),
				'list_idx'   => $list_idx,
				'updatedon'  => date( "Y-m-d H:i:s" ),
			);

		} else {
			throw new Exception( esc_html__( 'unknown producttype: ' . $type, ATKP_PLUGIN_PREFIX ) );
		}

		$data = apply_filters( 'atkp_modify_list_before_db_write', $data );

		return $data;
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

	private function get_product_from_array( $result ) {

		$products = array();

		if ( $result ) {
			foreach ( $result as $row ) {
				$itemnew = array();

				if ( $row['shop_id'] == '' ) {
					$itemnew['type']  = 'productid';
					$itemnew['value'] = $row['product_id'];
				} else {
					$atkp_product = new atkp_product();

					$atkp_product->shopid               = $row['shop_id'];
					$atkp_product->title                = $row['title'];
					$atkp_product->description          = $row['description'];
					$atkp_product->asin                 = $row['asin'];
					$atkp_product->parentasin           = $row['parentasin'];
					$atkp_product->ean                  = $row['ean'];
					$atkp_product->isbn                 = $row['isbn'];
					$atkp_product->gtin                 = $row['gtin'];
					$atkp_product->mpn                  = $row['mpn'];
					$atkp_product->brand                = $row['brand'];
					$atkp_product->productgroup         = $row['productgroup'];
					$atkp_product->releasedate          = $row['releasedate'];
					$atkp_product->addtocarturl         = $row['addtocarturl'];
					$atkp_product->producturl           = $row['producturl'];
					$atkp_product->smallimageurl        = $row['smallimageurl'];
					$atkp_product->mediumimageurl       = $row['mediumimageurl'];
					$atkp_product->largeimageurl        = $row['largeimageurl'];
					$atkp_product->images               = $row['images'] == null ? null : unserialize( $row['images'] );
					$atkp_product->manufacturer         = $row['manufacturer'];
					$atkp_product->author               = $row['author'];
					$atkp_product->numberofpages        = $row['numberofpages'];
					$atkp_product->features             = $row['features'];
					$atkp_product->variations           = $row['variations'] == null ? null : unserialize( $row['variations'] );
					$atkp_product->variationname = $row['variationname'] == null ? null : unserialize( $row['variationname'] );
					$atkp_product->rating               = $row['rating'];
					$atkp_product->reviewcount          = $row['reviewcount'];
					$atkp_product->customerreviewurl    = $row['customerreviewurl'];
					$atkp_product->listprice            = $row['listprice'];
					$atkp_product->listpricefloat       = $row['listpricefloat'];
					$atkp_product->amountsaved          = $row['amountsaved'];
					$atkp_product->amountsavedfloat     = $row['amountsavedfloat'];
					//$atkp_product->percentagesaved      = $row['percentagesaved'];
					$atkp_product->percentagesavedfloat = intval( $row['percentagesavedfloat'] );
					$atkp_product->saleprice            = $row['saleprice'];
					$atkp_product->salepricefloat       = $row['salepricefloat'];
					$atkp_product->shipping             = $row['shipping'];
					$atkp_product->shippingfloat        = $row['shippingfloat'];
					$atkp_product->availability         = $row['availability'];
					$atkp_product->isprime              = $row['isprime'];
					$atkp_product->iswarehouse          = $row['iswarehouse'];

					$atkp_product->updatedon      = strtotime( $row['updatedon'] );
					$atkp_product->baseprice      = $row['baseprice'];
					$atkp_product->basepricefloat = $row['basepricefloat'];
					$atkp_product->baseunit       = $row['baseunit'];
					$atkp_product->haserror       = $row['haserror'];


					$itemnew['type']  = 'product';
					$itemnew['value'] = $atkp_product;
				}

				array_push( $products, $itemnew );
			}
		}

		return $products;

	}

}