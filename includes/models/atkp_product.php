<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class atkp_product {
	public $data = array();

	public function get_shops_from_config() {

		$shop_ids = array();
		for ( $x = 1; $x < ( ATKP_FILTER_COUNT + 1 ); $x ++ ) {
			$shopid = ATKPTools::get_post_setting( $this->productid, ATKP_PRODUCT_POSTTYPE . '_shopid' . ( $x > 1 ? '_' . $x : '' ) );

			if ( intval( $shopid ) != 0 ) {
				$shop_ids[] = intval( $shopid );
			}

		}

		return $shop_ids;
	}

	public static function get_fields( $custom_fields = false ) {
		$arr = array();
		$tmp = new atkp_product();

		foreach ( $tmp->data as $key => $val ) {
			$cont = false;
			switch ( $key ) {
				case 'listid':
				case 'holdontop':
				case 'offers':
				case 'post_title':
				case 'lowestnewprice':
				case 'iswoocommerce':
				case 'totalnew':
				case 'postids':
				case 'outputashtml':
				case 'sortorder':
				case 'variations':
				case 'variationname':
				case 'cpc':
				case 'cpcfloat':
				case 'isupdated':
				case 'haserror':
				case 'images':
					$cont = true;
					break;
			}
			if ( $cont ) {
				continue;
			}

			if ( $key == 'shop' ) {
				$arr['shopname'] = 'caption';
			} else {
				$arr[ $key ] = 'caption';
			}
		}

		if ( $custom_fields ) {
			$newfields = atkp_udfield::load_fields();

			foreach ( $newfields as $newfield ) {
				$fieldname         = ATKP_PRODUCT_POSTTYPE . '_' . 'customfield_' . $newfield->name;
				$arr[ $fieldname ] = $newfield->caption;
			}

			$taxonomies = atkp_udtaxonomy::load_taxonomies();

			foreach ( $taxonomies as $taxonomy ) {
				if ( $taxonomy->isnewtax ) {
					$fieldname = 'ct_' . $taxonomy->name;
				} else {
					$fieldname = 'customtaxonomy_' . $taxonomy->name;
				}

				$arr[ $fieldname ] = $taxonomy->caption;

			}
		}

		return $arr;
	}

	//Dient für den csv provider zum übertragen erweiterter felder
	//intern
	public $customfields = array();

	public $displayfields = array();

	function __construct() {

		//wenn produkt in einer liste ist...
		$this->listid     = 0;
		$this->productid  = 0;
		$this->holdontop  = 100;
		$this->asin       = '';
		$this->parentasin = '';
		$this->ean        = '';
		$this->shopid     = '';
		/**
		 * @var atkp_shop|null
		 */
		$this->shop = null;

		$this->gtin         = '';
		$this->isbn         = '';
		$this->mpn          = '';
		$this->brand        = '';
		$this->productgroup = '';
		$this->releasedate  = '';

		$this->addtocarturl      = '';
		$this->producturl        = '';
		$this->customerreviewurl = '';

		$this->smallimageurl  = '';
		$this->mediumimageurl = '';
		$this->largeimageurl  = '';
		$this->manufacturer   = '';
		$this->author         = '';
		$this->numberofpages  = 0;
		$this->features       = '';

		$this->features_mode    = 0;
		$this->description_mode = 0;
		//$this->thumbimagesurl ='';
		//$this->imagesurl ='';
		/**
		 * @var atkp_product_image[]|null
		 */
		$this->images = array();
		$this->offers = array();

		$this->rating      = 0;
		$this->reviewcount = 0;
		$this->reviewsurl  = '';

		//interne postid
		$this->post_title  = '';
		$this->title       = '';
		$this->description = '';

		$this->listprice   = '';
		$this->amountsaved = '';
		$this->saleprice   = '';

		$this->listpricefloat   = (float) 0;
		$this->amountsavedfloat = (float) 0;
		$this->percentagesaved  = '';
		$this->percentagesavedfloat = (float) 0;
		$this->salepricefloat = 0;
		$this->unitpricefloat   = (float) 0;
		$this->shippingfloat    = (float) 0;

		$this->availability = '';
		$this->shipping     = '';
		$this->isprime      = 0;
		$this->iswarehouse  = false;

		$this->iswoocommerce = false;

		//wird nicht verwendet:
		$this->lowestnewprice = '';
		$this->totalnew       = 0;

		$this->predicate  = '';
		$this->testresult = '';
		$this->testrating = '';
		$this->testdate   = '';

		$this->pro    = '';
		$this->contra = '';

		$this->postids = '';

		$this->outputashtml = false;
		$this->sortorder    = '';

		$this->customfields = array();

		$this->variations    = array();
		$this->variationname = '';

		$this->cpc      = '';
		$this->cpcfloat = 0;

		$this->baseprice      = '';
		$this->basepricefloat = 0;
		$this->baseunit       = '';
		$this->baseunits      = 0;
		$this->isupdated      = false;
		$this->haserror       = false;
		$this->updatedon      = '';
	}

	public static function get_mainimage( $productid, $type = 'largetosmall' ) {

		if ( ! is_a( $productid, 'atkp_product' ) ) {
			$product = atkp_product::load( $productid );
		} else {
			$product = $productid;
		}

		$imageurl = '';

		$overridemainimage = $product->productid == '' ? '' : ATKPTools::get_post_setting( $product->productid, ATKP_PRODUCT_POSTTYPE . '_overridemainimage' );

		if ( $overridemainimage != '' ) {

			$newimages = atkp_product_image::load_images( $product->productid );

			$idx = 1;
			foreach ( $newimages as $newimage ) {
				if ( $idx == $overridemainimage ) {

					switch ( $type ) {
						default:
						case 'largetosmall':
							if ( $imageurl == '' && ! ATKPTools::str_contains( $newimage->largeimageurl, 'no_pic', false ) ) {
								$imageurl = $newimage->largeimageurl;
							}
							if ( $imageurl == '' && ! ATKPTools::str_contains( $newimage->mediumimageurl, 'no_pic', false ) ) {
								$imageurl = $newimage->mediumimageurl;
							}
							if ( $imageurl == '' && ! ATKPTools::str_contains( $newimage->smallimageurl, 'no_pic', false ) ) {
								$imageurl = $newimage->smallimageurl;
							}
							break;
						case 'smalltolarge':
							if ( $imageurl == '' && ! ATKPTools::str_contains( $newimage->smallimageurl, 'no_pic', false ) ) {
								$imageurl = $newimage->smallimageurl;
							}
							if ( $imageurl == '' && ! ATKPTools::str_contains( $newimage->mediumimageurl, 'no_pic', false ) ) {
								$imageurl = $newimage->mediumimageurl;
							}
							if ( $imageurl == '' && ! ATKPTools::str_contains( $newimage->largeimageurl, 'no_pic', false ) ) {
								$imageurl = $newimage->largeimageurl;
							}
							break;
						case 'mediumtolarge':
							if ( $imageurl == '' && ! ATKPTools::str_contains( $newimage->mediumimageurl, 'no_pic', false ) ) {
								$imageurl = $newimage->mediumimageurl;
							}
							if ( $imageurl == '' && ! ATKPTools::str_contains( $newimage->smallimageurl, 'no_pic', false ) ) {
								$imageurl = $newimage->smallimageurl;
							}
							if ( $imageurl == '' && ! ATKPTools::str_contains( $newimage->largeimageurl, 'no_pic', false ) ) {
								$imageurl = $newimage->largeimageurl;
							}
							break;
					}


					break;
				}

				$idx ++;
			}
		} else {

			switch ( $type ) {
				default:
				case 'largetosmall':
					if ( $imageurl == '' && ! ATKPTools::str_contains( $product->largeimageurl, 'no_pic', false ) ) {
						$imageurl = $product->largeimageurl;
					}
					if ( $imageurl == '' && ! ATKPTools::str_contains( $product->mediumimageurl, 'no_pic', false ) ) {
						$imageurl = $product->mediumimageurl;
					}
					if ( $imageurl == '' && ! ATKPTools::str_contains( $product->smallimageurl, 'no_pic', false ) ) {
						$imageurl = $product->smallimageurl;
					}
					break;
				case 'smalltolarge':

					if ( $imageurl == '' && ! ATKPTools::str_contains( $product->largeimageurl, 'no_pic', false ) ) {
						$imageurl = $product->smallimageurl;
					}
					if ( $imageurl == '' && ! ATKPTools::str_contains( $product->largeimageurl, 'no_pic', false ) ) {
						$imageurl = $product->mediumimageurl;
					}
					if ( $imageurl == '' && ! ATKPTools::str_contains( $product->largeimageurl, 'no_pic', false ) ) {
						$imageurl = $product->largeimageurl;
					}
					break;
				case 'mediumtolarge':

					if ( $imageurl == '' && ! ATKPTools::str_contains( $product->mediumimageurl, 'no_pic', false ) ) {
						$imageurl = $product->mediumimageurl;
					}
					if ( $imageurl == '' && ! ATKPTools::str_contains( $product->smallimageurl, 'no_pic', false ) ) {
						$imageurl = $product->smallimageurl;
					}
					if ( $imageurl == '' && ! ATKPTools::str_contains( $product->largeimageurl, 'no_pic', false ) ) {
						$imageurl = $product->largeimageurl;
					}
					break;
			}
		}

		return $imageurl;
	}

	public static function get_product_from_woo( $wooid ) {
		$product = null;

		$productid = ATKPTools::get_post_setting( $wooid, ATKP_PLUGIN_PREFIX . '_sourceproductid' );

		if ( $productid != '' && $productid != 0 ) {
			//$product = atkp_product::load( $productid );

			return $productid;
		}

		$eanfield = atkp_options::$loader->get_woo_ean_field();
		$keytype  = atkp_options::$loader->get_woo_keytype();

		if ( $eanfield == '' || $eanfield == 'sku' ) {
			$ean = ATKPTools::get_post_setting( $wooid, '_sku' );
		} else {
			$ean = ATKPTools::get_post_setting( $wooid, $eanfield );
		}


		if ( $keytype == 'id' ) {
			$exists = atkp_product::exists( $ean );

			if ( $exists ) {
				$product = $ean;
			}
		} else {
			$product = atkp_product::idbyean( $ean );
		}

		return $product;
	}

	private static $product_mapping = array();

	public static function get_woo_product( $productid ) {

		//ATKPTools::set_post_setting( $result->ID, ATKP_PLUGIN_PREFIX . '_sourceproductid', $productid );

		$woo_product = null;

		if ( isset( self::$product_mapping[ $productid ] ) ) {
			$woo_id = self::$product_mapping[ $productid ];

			$woo_product = get_post( $woo_id );
		}

		if ( $woo_product == null ) {
			//find 1

			$args = array(
				'post_type'    => array( 'product' ),
				'post_status'  => array( 'draft', 'publish' ),
				'meta_key'     => ATKP_PLUGIN_PREFIX . '_sourceproductid',
				'meta_value'   => $productid,
				'meta_compare' => '=',
			);


			$posts = get_posts( $args );

			if ( count( $posts ) > 0 ) {
				$woo_product = $posts[0];
			}
		}

		if ( $woo_product == null ) {
			//find 1

			$product = atkp_product::load( $productid );

			$keytype = atkp_options::$loader->get_woo_keytype();

			$woo_product = null;
			$eans        = array();

			if ( $keytype == 'id' ) {
				$eans[] = $productid;
			} else {
				$eans = explode( ',', $product->ean );

				if ( $product->ean == '' ) {
					return null;
				}
			}

			if ( ATKPLog::$logenabled ) {
				ATKPLog::LogDebug( 'export_product woo productid: ' . $productid );
				ATKPLog::LogDebug( 'export_product woo ean exists: ' . ( ! ( $product->ean == '' || count( $eans ) == 0 ) ) );
			}


			if ( count( $eans ) == 0 ) {
				return null;
			}

			$eanfield = atkp_options::$loader->get_woo_ean_field();

			foreach ( $eans as $ean ) {
				$meta_key = $eanfield;

				if ( $eanfield == '' || $eanfield == 'sku' ) {
					$meta_key = '_sku';
				}

				$args = array(
					'post_type'    => array( 'product' ),
					'post_status'  => array( 'draft', 'publish' ),
					'meta_key'     => $meta_key,
					'meta_value'   => $ean,
					'meta_compare' => '=',
				);

				$posts = get_posts( $args );

				if ( count( $posts ) > 0 ) {
					$woo_product = $posts[0];
				}
				wp_reset_query();

			};
		}

		if ( $woo_product != null && ! isset( self::$product_mapping[ $productid ] ) ) {
			self::$product_mapping[ $productid ] = $woo_product->ID;
		}

		return $woo_product;
	}

	public static function loadbyasin( $asin, $shop_id = '' ) {

		$table_helper = new atkp_producttable_helper();
		$post_ids     = $table_helper->load_products_by_asin( $asin, $shop_id );

		if ( count( $post_ids ) > 0 ) {
			$products = atkp_product_collection::load( $post_ids[0] );

			if ( $products != null ) {
				return $products->get_main_product();
			}
		}

		return null;
	}

	public static function idbyasin( $asin, $shop_id = '' ) {
		$table_helper = new atkp_producttable_helper();
		$post_ids     = $table_helper->load_products_by_asin( $asin, $shop_id );

		if ( count( $post_ids ) == 0 ) {
			//search in the internal wp fields

			$post_ids = get_posts( array(
				'numberposts'    => - 1,
				'post_type'      => ATKP_PRODUCT_POSTTYPE,
				'meta_key'       => ATKP_PRODUCT_POSTTYPE . '_asin',
				'meta_value'     => $asin,
				'fields'         => 'ids',
				'post_status'    => array( 'publish', 'draft' ),
				'posts_per_page' => - 1
			) );
		}

		if ( count( $post_ids ) == 0 ) {
			return null;
		} else {
			return $post_ids[0];
		}
	}

	public static function idbyean( $ean ) {

		$table_helper = new atkp_producttable_helper();
		$post_ids     = $table_helper->load_products_by_ean( $ean );

		if ( count( $post_ids ) == 0 ) {
			//search in the internal wp fields

			$post_ids = get_posts( array(
				'numberposts'    => - 1,
				'post_type'      => ATKP_PRODUCT_POSTTYPE,
				'meta_key'       => ATKP_PRODUCT_POSTTYPE . '_ean',
				'meta_value'     => $ean,
				'fields'         => 'ids',
				'post_status'    => array( 'publish', 'draft' ),
				'posts_per_page' => - 1
			) );
		}

		if ( count( $post_ids ) == 0 ) {
			return null;
		} else {
			return $post_ids[0];
		}
	}

	public static function idbyname( $name ) {
		/*
		* 'publish' - a published post or page
		* 'pending' - post is pending review
		* 'draft' - a post in draft status
		* 'auto-draft' - a newly created post, with no content
		* 'future' - a post to publish in the future
		* 'private' - not visible to users who are not logged in
		* 'inherit' - a revision. see get_children.
		* 'trash' - post is in trashbin. added with Version 2.9.
		*/

		$table_helper = new atkp_producttable_helper();
		$post_ids     = $table_helper->load_products_by_title( $name );

		if ( count( $post_ids ) == 0 ) {
			//search in the internal wp fields

			$post_ids = get_posts( array(
				'numberposts'    => - 1,
				'post_type'      => ATKP_PRODUCT_POSTTYPE,
				'title'          => $name,
				'fields'         => 'ids',
				'post_status'    => array( 'publish', 'draft' ),
				'posts_per_page' => - 1
			) );
		}

		if ( count( $post_ids ) == 0 ) {
			return null;
		} else {
			return $post_ids[0];
		}
	}

	public static function exists( $post_id ) {

		$product = get_post( $post_id );

		if ( ! isset( $product ) || $product == null ) {
			return false;
		}
		if ( $product->post_type != ATKP_PRODUCT_POSTTYPE ) {
			return false;
		}

		if ( $product->post_status == 'publish' || $product->post_status == 'draft' ) {
			return true;
		}

		return false;
	}

	public $metafields = array();

	public function get_metavalue( $name ) {
		$value = isset( $this->metafields[ $name ] ) ? $this->metafields[ $name ] : null;

		if ( isset( $value ) && is_array( $value ) && count( $value ) > 0 ) {
			return $value[0];
		} else {
			return '';
		}
	}

	public static function load( $post_id ) {


		$product = get_post( $post_id );

		if ( ! isset( $product ) || $product == null ) {
			return null;
			//throw new Exception( 'product not found: ' . $post_id );
		}
		if ( $product->post_type != ATKP_PRODUCT_POSTTYPE ) {
			return null;
			//throw new Exception( 'invalid post_type: ' . $product->post_type . ', $post_id: ' . $post_id );
		}

		$prd = new atkp_product();

		$prd->metafields = get_post_meta( $post_id );

		//$prd->title = $product->post_title;
		//$prd->description = $product->post_content;
		$prd->productid = $post_id;
		$prd->shopid    = $prd->get_metavalue( ATKP_PRODUCT_POSTTYPE . '_shopid' );
		if ( $prd->shopid != '' && atkp_shop::exists($prd->shopid ) ) {
			$prd->shop = atkp_shop::load( $prd->shopid );
		}

		$prd->post_title  = $product->post_title;
		$prd->title       = $prd->get_metavalue( ATKP_PRODUCT_POSTTYPE . '_title' );
		$prd->description = $prd->get_metavalue( ATKP_PRODUCT_POSTTYPE . '_description' );

		$prd->productid = $post_id; //ATKP_PRODUCT_POSTTYPE.'_updatedon'
		$prd->updatedon = $prd->get_metavalue( ATKP_PRODUCT_POSTTYPE . '_updatedon' );

		$prd->predicate = $prd->get_metavalue( ATKP_PRODUCT_POSTTYPE . '_predicate' );

		$prd->pro    = $prd->get_metavalue( ATKP_PRODUCT_POSTTYPE . '_pro' );
		$prd->contra = $prd->get_metavalue( ATKP_PRODUCT_POSTTYPE . '_contra' );


		$prd->testresult = $prd->get_metavalue( ATKP_PRODUCT_POSTTYPE . '_testresult' );
		$prd->testrating = $prd->get_metavalue( ATKP_PRODUCT_POSTTYPE . '_testrating' );
		$prd->testdate   = $prd->get_metavalue( ATKP_PRODUCT_POSTTYPE . '_testdate' );

		$prd->asin = $prd->get_metavalue( ATKP_PRODUCT_POSTTYPE . '_asin' );
		$prd->ean  = $prd->get_metavalue( ATKP_PRODUCT_POSTTYPE . '_ean' );

		$prd->mpn  = $prd->get_metavalue( ATKP_PRODUCT_POSTTYPE . '_mpn' );
		$prd->gtin = $prd->get_metavalue( ATKP_PRODUCT_POSTTYPE . '_gtin' );

		$prd->isbn         = $prd->get_metavalue( ATKP_PRODUCT_POSTTYPE . '_isbn' );
		$prd->brand        = $prd->get_metavalue( ATKP_PRODUCT_POSTTYPE . '_brand' );
		$prd->productgroup = $prd->get_metavalue( ATKP_PRODUCT_POSTTYPE . '_productgroup' );
		$prd->releasedate  = $prd->get_metavalue( ATKP_PRODUCT_POSTTYPE . '_releasedate' );

		$prd->addtocarturl      = $prd->get_metavalue( ATKP_PRODUCT_POSTTYPE . '_addtocarturl' );
		$prd->producturl        = $prd->get_metavalue( ATKP_PRODUCT_POSTTYPE . '_producturl' );
		$prd->customerreviewurl = $prd->get_metavalue( ATKP_PRODUCT_POSTTYPE . '_customerreviewsurl' );


		$prd->smallimageurl  = $prd->get_metavalue( ATKP_PRODUCT_POSTTYPE . '_smallimageurl' );
		$prd->mediumimageurl = $prd->get_metavalue( ATKP_PRODUCT_POSTTYPE . '_mediumimageurl' );
		$prd->largeimageurl  = $prd->get_metavalue( ATKP_PRODUCT_POSTTYPE . '_largeimageurl' );
		$prd->manufacturer   = $prd->get_metavalue( ATKP_PRODUCT_POSTTYPE . '_manufacturer' );
		$prd->author         = $prd->get_metavalue( ATKP_PRODUCT_POSTTYPE . '_author' );
		$prd->numberofpages  = $prd->get_metavalue( ATKP_PRODUCT_POSTTYPE . '_numberofpages' );
		$prd->features       = $prd->get_metavalue( ATKP_PRODUCT_POSTTYPE . '_features' );

		$feat_mode_str = $prd->get_metavalue( ATKP_PRODUCT_POSTTYPE . '_features_mode' );

		if ( $feat_mode_str == '' && $prd->features != '' ) {
			$feat_mode_str = 1;
		}

		$prd->features_mode = intval( $feat_mode_str );

		$desc_mode_str = $prd->get_metavalue( ATKP_PRODUCT_POSTTYPE . '_description_mode' );

		if ( $desc_mode_str == '' && $prd->description != '' ) {
			$desc_mode_str = 1;
		}

		$prd->description_mode = intval( $desc_mode_str );

		$prd->iswoocommerce = $prd->get_metavalue( ATKP_PRODUCT_POSTTYPE . '_iswoocommerce' );

		$prd->images = atkp_product_image::load_images( $post_id );
		//$prd->offers = atkp_product_offer::load_offers( $post_id );
		//$prd->imagesurl = ATKPTools::get_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE.'_imagesurl');
		//$prd->thumbimagesurl = ATKPTools::get_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE.'_thumbimagesurl');

		$variations   = $prd->get_metavalue( ATKP_PRODUCT_POSTTYPE . '_variations' );
		$tmpvariation = $variations == '' ? null : unserialize( $variations );

		for ( $x = 0; $x < ATKP_VARIATION_COUNT; $x++)  {

			$name = $prd->get_metavalue(ATKP_PRODUCT_POSTTYPE .  '_variation_name_'.$x );
			$url = $prd->get_metavalue(ATKP_PRODUCT_POSTTYPE .  '_variation_url_'.$x );
			$imageurl =$prd->get_metavalue(ATKP_PRODUCT_POSTTYPE .  '_variation_imageurl_'.$x );

			if($name != '') {
				if($tmpvariation == null)
					$tmpvariation = array();

				$prd2 = new atkp_product();
				$prd2->productid = $prd->productid;
				$prd2->variationname = array($name);
				$prd2->title = $name;
				$prd2->producturl = ($url != '' ? $url : $prd->producturl);
				$prd2->smallimageurl = $imageurl;
				$prd2->mediumimageurl =  $imageurl;
				$prd2->largeimageurl =  $imageurl;

				$tmpvariation[] = $prd2;
			}
		}

		$prd->variations = $tmpvariation;

		$prd->rating      = $prd->get_metavalue( ATKP_PRODUCT_POSTTYPE . '_rating' );
		$prd->reviewcount = $prd->get_metavalue( ATKP_PRODUCT_POSTTYPE . '_reviewcount' );
		$prd->reviewsurl  = $prd->get_metavalue( ATKP_PRODUCT_POSTTYPE . '_reviewsurl' );

		$prd->listprice       = $prd->get_metavalue( ATKP_PRODUCT_POSTTYPE . '_listprice' );
		$prd->amountsaved     = $prd->get_metavalue( ATKP_PRODUCT_POSTTYPE . '_amountsaved' );

		$prd->percentagesaved      = ( $prd->get_metavalue( ATKP_PRODUCT_POSTTYPE . '_percentagesaved' ) );
		$prd->percentagesavedfloat = floatval( $prd->get_metavalue( ATKP_PRODUCT_POSTTYPE . '_percentagesavedfloat' ));
		$prd->saleprice       = $prd->get_metavalue( ATKP_PRODUCT_POSTTYPE . '_saleprice' );
		$prd->availability    = $prd->get_metavalue( ATKP_PRODUCT_POSTTYPE . '_availability' );
		$prd->shipping        = $prd->get_metavalue( ATKP_PRODUCT_POSTTYPE . '_shipping' );
		$prd->isprime         = $prd->get_metavalue( ATKP_PRODUCT_POSTTYPE . '_isprime' );
		$prd->iswarehouse     = $prd->get_metavalue( ATKP_PRODUCT_POSTTYPE . '_iswarehouse' );

		$prd->listpricefloat   = (float) $prd->get_metavalue( ATKP_PRODUCT_POSTTYPE . '_listpricefloat' );
		$prd->amountsavedfloat = (float) $prd->get_metavalue( ATKP_PRODUCT_POSTTYPE . '_amountsavedfloat' );
		$prd->salepricefloat   = (float) $prd->get_metavalue( ATKP_PRODUCT_POSTTYPE . '_salepricefloat' );
		$prd->shippingfloat    = (float) $prd->get_metavalue( ATKP_PRODUCT_POSTTYPE . '_shippingfloat' );

		$prd->baseprice      = $prd->get_metavalue( ATKP_PRODUCT_POSTTYPE . '_baseprice' );
		$prd->basepricefloat = (float) $prd->get_metavalue( ATKP_PRODUCT_POSTTYPE . '_basepricefloat' );
		$prd->baseunit       = $prd->get_metavalue( ATKP_PRODUCT_POSTTYPE . '_baseunit' );
		$prd->baseunits      = $prd->get_metavalue( ATKP_PRODUCT_POSTTYPE . '_baseunits' );

		$postid = $prd->get_metavalue( ATKP_PRODUCT_POSTTYPE . '_postid' );

		if ( $postid != '' && is_numeric( $postid ) ) {
			$prd->postids = array( intval( $postid ) );
		} else if ( $postid != '' ) {
			$prd->postids = unserialize( $postid );
		} else {
			$prd->postids = '';
		}


		$prd->outputashtml = $prd->get_metavalue( ATKP_PRODUCT_POSTTYPE . '_outputashtml' );
		$prd->sortorder    = $prd->get_metavalue( ATKP_PRODUCT_POSTTYPE . '_sortorder' );

		$prd->load_displayfields();

		return $prd;
	}

	public function get_pricehistory() {
		return atkp_product_pricehistory::load_history( $this->productid );
	}

	public function load_displayfields() {


		$this->load_fields();
		$this->load_fieldgroups();

	}


	private function load_fields() {
		$newfields = atkp_udfield::load_fields();

		foreach ( $newfields as $newfield ) {
			$fieldname = 'customfield_' . $newfield->name;

			$this->displayfields[ $fieldname ] = '';

			if ( $this->productid == '' || $this->productid == '0' ) {
				continue;
			}

			$newfield->isnewfield = false;

			$this->displayfields[ $fieldname ] = $newfield;
		}

		$taxonomies = atkp_udtaxonomy::load_taxonomies();

		if ( $taxonomies != null ) {
			foreach ( $taxonomies as $taxonomy ) {
				if ( ! $taxonomy->issystemfield ) {
					if ( $taxonomy->isnewtax ) {
						$fieldname = 'ct_' . $taxonomy->name;
					} else {
						$fieldname = 'customtaxonomy_' . $taxonomy->name;
					}

				} else {

					if ( $taxonomy->ismanufacturer ) {
						$fieldname = 'manufacturer';
					} else if ( $taxonomy->isauthor ) {
						$fieldname = 'author';
					} else if ( $taxonomy->isbrand ) {
						$fieldname = 'brand';
					} else if ( $taxonomy->isproductcategory ) {
						$fieldname = 'productcategory';
					} else {
						$fieldname = $taxonomy->name;
					}
				}

				if ( $this->productid != '' ) {
					$this->displayfields[ $fieldname ] = $taxonomy;
				}
			}
		}
	}

	private function load_fieldgroups( $prefix = 'cf_' ) {

		$groups = ATKPTools::get_fieldgroups_by_productid( $this->productid );

		foreach ( $groups as $group ) {
			$fields = ATKPTools::get_post_setting( $group->ID, ATKP_FIELDGROUP_POSTTYPE . '_fields' );

			if ( $fields != null ) {
				foreach ( $fields as $field ) {
					$field->isnewfield = true;
					if ( $field->type != 6 ) {
						$this->displayfields[ $prefix . $field->name ] = $field;
					}
				}
			}
		}
	}

	/**
	 * Function wird aufgerufen wenn eine Liste geladen wird
	 * Hier können notwendige Infos an das Produkt übergeben werden.
	 *
	 * @param $list_id
	 */
	public function init_list( $list_id, $shop_id ) {
		$this->listid = $list_id;

		//if ( $this->productid != '' ) {
		//	return;
		//}

		//$this->offers = atkp_product_offer::load_offers_by_listid( $list_id, $this->asin );

		if ( $shop_id != '' && $shop_id != 0 ) {
			$this->shopid = $shop_id;
			$this->shop   = atkp_shop::load( $shop_id );
		}
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

	protected static function price_to_float( $s ) {
		return ATKPTools::price_to_float( $s );
		//	$s = str_replace( ',', '.', $s );

		// remove everything except numbers and dot "."
		//	$s = preg_replace( "/[^0-9\.]/", "", $s );

		// remove all seperators from first part and keep the end
		//	$s = str_replace( '.', '', substr( $s, 0, - 3 ) ) . substr( $s, - 3 );

		// return float
		//	return round( (float) $s, 2 );
	}
}


?>