<?php


/**
 * Holds  ll products
 */
class atkp_product_collection {
	/** @var $products atkp_product[] */
	public $products = array();
	public $data = array();

	function __construct() {
		$this->productid = 0;
	}

	public static function get_fake_product() {
		$atkp_product_collection            = new atkp_product_collection();
		$atkp_product_collection->productid = - 1;

		$prds = array();

		$offer            = new atkp_product();
		$offer->productid = - 1;
		$offer->updatedon = time();

		$offer->shop                     = new atkp_shop();
		$offer->shopid                   = rand( - 5, - 500 );
		$offer->shop->displayshoplogo    = true;
		$offer->shop->shopid             = $offer->shopid;
		$offer->shop->title              = 'Amazon';
		$offer->shop->customlogourl      = plugins_url( 'images/logo-small-amazon-com.png', ATKP_PLUGIN_FILE );
		$offer->shop->customsmalllogourl = plugins_url( 'images/logo-normal-amazon-com.png', ATKP_PLUGIN_FILE );

		$offer->shipping          = '4,99 $';
		$offer->availability      = 'In stock.';
		$offer->isprime           = true;
		$offer->pro               = "Open your mind\nStarting from nothing";
		$offer->contra            = "Takes a lot of time";
		$offer->rating            = 4.3;
		$offer->reviewcount       = 134;
		$offer->customerreviewurl = 'https://www.amazon.de/dp/1733706119?tag=flaschenzug-kaufen.de09-21&linkCode=osi&th=1&psc=1';
		$offer->description       = 'Isn’t this industry long overdue for a legitimate, step-by-step guide to building an internet business?

Not a crappy $97 PDF delivered via email. Just the age-old gold standard: a reasonably priced book.

These pages contain EVERYTHING you need to start an online business in the affiliate marketing, internet marketing, blogging, and e-commerce industries… using less than $100.

It doesn’t matter if you’re brand new to this or if you’ve tried for years without seeing success.

If you can bring yourself to trust a ginger millennial as your guide (difficult, I know), you’ll be on your way to first-time success in online business the moment you begin reading.';

		$offer->features = "Step-by-step guide to building an internet business.
Not a crappy $97 PDF delivered via email. 
Just the age-old gold standard: a reasonably priced book.
These pages contain EVERYTHING you need to start an online business in the affiliate marketing.";


		$offer->listprice      = '21,99 $';
		$offer->listpricefloat = ATKPTools::price_to_float( $offer->listprice );

		$offer->amountsaved      = '2,00 $';
		$offer->amountsavedfloat = ATKPTools::price_to_float( $offer->amountsaved );
		$offer->percentagesaved = 9;

		$offer->saleprice      = '19,99 $';
		$offer->salepricefloat = ATKPTools::price_to_float( $offer->saleprice );
		$offer->shippingfloat  = ATKPTools::price_to_float( $offer->shipping );

		$offer->baseunit       = "Pcs";
		$offer->baseprice      = '19,99 $';
		$offer->basepricefloat = ATKPTools::price_to_float( $offer->baseprice );
		$offer->baseunits      = 1;

		$offer->smallimageurl  = plugins_url( 'images/fromnothing_small.jpg', ATKP_PLUGIN_FILE );
		$offer->mediumimageurl = plugins_url( 'images/fromnothing_medium.jpg', ATKP_PLUGIN_FILE );
		$offer->largeimageurl  = plugins_url( 'images/fromnothing_big.jpg', ATKP_PLUGIN_FILE );
		$offer->producturl     = 'https://www.amazon.de/dp/1733706119?tag=flaschenzug-kaufen.de09-21&linkCode=osi&th=1&psc=1';
		$offer->title          = 'From Nothing: Everything You Need to Profit from Affiliate Marketing, Internet Marketing, Blogging, Online Business, e-Commerce and More… Starting With <$100';


		$images              = array();
		$udf                 = new atkp_product_image();
		$udf->id             = uniqid();
		$udf->smallimageurl  = plugins_url( 'images/fromnothing_image2_big.jpg', ATKP_PLUGIN_FILE );
		$udf->mediumimageurl = plugins_url( 'images/fromnothing_image2_big.jpg', ATKP_PLUGIN_FILE );
		$udf->largeimageurl  = plugins_url( 'images/fromnothing_image2_big.jpg', ATKP_PLUGIN_FILE );
		array_push( $images, $udf );

		$udf                 = new atkp_product_image();
		$udf->id             = uniqid();
		$udf->smallimageurl  = plugins_url( 'images/fromnothing_image3_big.jpg', ATKP_PLUGIN_FILE );
		$udf->mediumimageurl = plugins_url( 'images/fromnothing_image3_big.jpg', ATKP_PLUGIN_FILE );
		$udf->largeimageurl  = plugins_url( 'images/fromnothing_image3_big.jpg', ATKP_PLUGIN_FILE );
		array_push( $images, $udf );


		$offer->images = $images;

		array_push( $prds, $offer );

		$offer2                           = unserialize( serialize( $offer ) );
		$offer2->shopid                   = rand( - 500, - 1000 );
		$offer2->shop->displayshoplogo    = true;
		$offer2->shop->title              = 'eBay';
		$offer2->shop->customlogourl      = plugins_url( 'images/ebay-logo.png', ATKP_PLUGIN_FILE );
		$offer2->shop->customsmalllogourl = plugins_url( 'images/ebay-logo-small.png', ATKP_PLUGIN_FILE );
		$offer2->saleprice                = '17,99 $';
		$offer->shipping                  = '6,99 $';
		$offer2->salepricefloat           = ATKPTools::price_to_float( $offer->saleprice );
		$offer2->shippingfloat            = ATKPTools::price_to_float( $offer->shipping );

		array_push( $prds, $offer2 );

		$offer3                           = unserialize( serialize( $offer ) );
		$offer3->shopid                   = rand( - 1000, - 1500 );
		$offer3->shop->displayshoplogo    = true;
		$offer3->shop->title              = 'thalia.de';
		$offer3->shop->customlogourl      = plugins_url( 'images/thalia-logo.png', ATKP_PLUGIN_FILE );
		$offer3->shop->customsmalllogourl = plugins_url( 'images/thalia-logo.png', ATKP_PLUGIN_FILE );
		$offer3->saleprice                = '20,99 $';
		$offer->shipping                  = '2,99 $';
		$offer3->salepricefloat           = ATKPTools::price_to_float( $offer->saleprice );
		$offer3->shippingfloat            = ATKPTools::price_to_float( $offer->shipping );

		array_push( $prds, $offer3 );


		$atkp_product_collection->products = $prds;

		return $atkp_product_collection;
	}

	/**
	 * @param $post_id
	 * @param $shop_id
	 * @param $dontLoadBase
	 *
	 * @return atkp_product_collection|null
	 * @throws Exception
	 */
	public static function load( $post_id, $shop_id = '', $dontLoadBase = false ) {

		if ( $post_id == - 1 ) {
			return self::get_fake_product();
		}

		$product = get_post( $post_id );

		if ( ! isset( $product ) || $product == null ) {
			return null;
			//throw new Exception( 'product not found: ' . $post_id );
		}
		if ( $product->post_type != ATKP_PRODUCT_POSTTYPE ) {
			return null;
			//throw new Exception( 'invalid post_type: ' . $product->post_type . ', $post_id: ' . $post_id );
		}
		$atkp_product_collection            = new atkp_product_collection();
		$atkp_product_collection->productid = $post_id;

		$atkp_producttable_helper = new atkp_producttable_helper();
		$prds                     = $atkp_producttable_helper->load_products( $post_id );
		$baseproduct              = $dontLoadBase ? null : atkp_product::load( $post_id );


		$selectedshopid = $shop_id != '' ? $shop_id : ATKPTools::get_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_shopid' );

		$shops = atkp_shop::get_list();

		$hide_shops = ATKPTools::get_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_hide_shops' );

		//TODO: Add manual products


		for ( $x = 0; $x < ATKP_MANUALOFFER_COUNT; $x ++ ) {
			$name = ATKPTools::get_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_manualoffer_name_' . $x );
			$logo = ATKPTools::get_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_manualoffer_logo_' . $x );

			$price    = ATKPTools::get_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_manualoffer_price_' . $x );
			$shipping = ATKPTools::get_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_manualoffer_shipping_' . $x );
			$avail    = ATKPTools::get_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_manualoffer_availability_' . $x );
			$url      = ATKPTools::get_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_manualoffer_url_' . $x );

			if ( $name == '' || $url == '' ) {
				continue;
			}

			$offer            = new atkp_product();
			$offer->productid = $post_id;

			if ( is_numeric( $name ) ) {

				foreach ( $shops as $shop ) {
					if ( $shop->id == intval( $name ) ) {
						$offer->holdontop = $shop->holdontop;
						$offer->shop      = $shop;
						$offer->shopid    = $shop->id;
						break;
					}

					foreach ( $shop->children as $s ) {
						if ( $s->id == intval( $name ) ) {
							$offer->holdontop = $s->holdontop;
							$offer->shop      = $s;
							$offer->shopid    = $s->id;
							break;
						}
					}
				}

			} else {
				$offer->shop                     = new atkp_shop();
				$offer->shopid                   = rand( - 5, - 500 );
				$offer->shop->displayshoplogo    = $logo != '';
				$offer->shop->shopid             = $offer->shopid;
				$offer->shop->title              = $name;
				$offer->shop->customlogourl      = $logo;
				$offer->shop->customsmalllogourl = $logo;
			}

			$offer->shipping     = $shipping;
			$offer->availability = $avail;

			$offer->saleprice      = $price;
			$offer->salepricefloat = ATKPTools::price_to_float( $price );
			$offer->shippingfloat  = ATKPTools::price_to_float( $shipping );

			$offer->producturl = $url;
			$offer->title      = $product->post_title;

			array_push( $prds, $offer );
		}

		if ( count( $prds ) == 0 && $product != null ) {
			$b = atkp_product::load( $post_id );

			if ( $b->salepricefloat > 0 ) {
				array_push( $prds, $b );
			}

		}


		foreach ( $prds as $prd ) {
			$prd->holdontop = 100;

			if ( $prd->shopid == $selectedshopid ) {
				$prd->ismainshop = true;
			}

			foreach ( $shops as $shop ) {
				if ( $shop->id == $prd->shopid ) {
					$prd->holdontop = $shop->holdontop;
					$prd->shop      = $shop;
					break;
				}

				foreach ( $shop->children as $s ) {
					if ( $s->id == $prd->shopid ) {
						$prd->holdontop = $s->holdontop;
						$prd->shop      = $s;
						break;
					}
				}
			}

			if ( $baseproduct != null ) {
				$prd->customfields     = $baseproduct->customfields;
				$prd->displayfields    = $baseproduct->displayfields;
				$prd->metafields       = $baseproduct->metafields;
				$prd->iswoocommerce    = $baseproduct->iswoocommerce;
				$prd->description_mode = $baseproduct->description_mode;
				$prd->features_mode    = $baseproduct->features_mode;

				$prd->postids      = $baseproduct->postids;
				$prd->outputashtml = $baseproduct->outputashtml;
				$prd->sortorder    = $baseproduct->sortorder;
			}
		}


		usort( $prds, array( new atkp_product_collection(), "sortByPrice" ) );

		if ( isset( $_GET['post'] ) && $_GET['post'] != '' ) {
			$prds = apply_filters( 'atkp_productcollection_adminload', $prds );
		} else {
			$prds2 = array();
			foreach ( $prds as $p ) {
				if ( $p->shop != null ) {
					$ignore = false;
					if ( $p->shop->hidepricecomparision ) {
						$ignore = true;
					}

					if ( $hide_shops != null ) {
						foreach ( $hide_shops as $hide_shop ) {
							if ( $hide_shop['shop_id'] == $p->shop->id && $hide_shop['product_id'] == $p->productid ) {
								$ignore = true;
								break;
							}
						}
					}

					if ( $ignore ) {
						continue;
					}
				}
				$prds2[] = $p;
			}


			$prds = apply_filters( 'atkp_productcollection_load', $prds2 );
		}

		$atkp_product_collection->products = $prds;

		return $atkp_product_collection;
	}

	/**
	 *
	 * @return atkp_product_offer[]
	 */
	public function get_offers( $includemainoffer = true ) {
		$offers = array();
		$shops  = atkp_shop::get_list();
		$idx    = 0;
		foreach ( $this->products as $myproduct ) {
			$offer          = new atkp_product_offer();
			$offer->product = $myproduct;

			if ( $idx == 0 ) {
				$offer->ismainoffer = true;
			} else {
				$offer->ismainoffer = false;
			}
			$offer->id       = $myproduct->productid;
			$offer->type     = 2;
			$offer->shopid   = $myproduct->shopid;
			$offer->number   = $myproduct->asin;
			$offer->cpcfloat = $myproduct->cpcfloat;

			if ( $myproduct->shopid <= - 1 ) {
				$offer->shopname    = $myproduct->shop->title;
				$offer->shoplogourl = $myproduct->shop->customlogourl;
			}

			$offer->shipping     = $myproduct->shipping;
			$offer->availability = $myproduct->availability;

			$offer->price         = $myproduct->saleprice;
			$offer->pricefloat    = $myproduct->salepricefloat;
			$offer->shippingfloat = $myproduct->shippingfloat;
			$offer->holdontop     = 100;

			foreach ( $shops as $shop ) {
				if ( $myproduct->shopid == $shop->id ) {
					$offer->holdontop = $shop->holdontop;
					$offer->shop      = $shop;
				}
				if ( $shop->children != null ) {
					foreach ( $shop->children as $child ) {
						if ( $myproduct->shopid == $child->id ) {
							$offer->holdontop = $child->holdontop;
							$offer->shop      = $child;
						}
					}
				}
			}

			$offer->link  = $myproduct->producturl;
			$offer->title = $myproduct->title;

			if ( $includemainoffer == true || ! $offer->ismainoffer ) {
				array_push( $offers, $offer );
			}

			$idx ++;
		}

		return $offers;
	}

	/**
	 *
	 * @return atkp_product
	 */
	public function get_min_product() {
		$prd = $this->products;

		usort( $prd, array( $this, "sortByPrice_asc" ) );

		return $prd[0];
	}

	/**
	 *
	 * @return atkp_product
	 */
	public function get_max_product() {
		$prd = $this->products;

		usort( $prd, array( $this, "sortByPrice_desc" ) );

		return $prd[0];
	}


	/**
	 *
	 * @return atkp_product
	 */
	public function get_main_product( $shop_id = '', $dontOverride = false ) {
		$sourceproduct = count( $this->products ) > 0 ? $this->products[0] : null;

		if ( $shop_id != '' ) {
			foreach ( $this->products as $product ) {
				if ( $product->shopid == $shop_id ) {
					$sourceproduct = $product;
					break;
				}
			}
		} else {
			if ( $sourceproduct != null ) {
				if ( $sourceproduct->salepricefloat == 0 ) {
					foreach ( $this->products as $product ) {
						if ( $product->salepricefloat > 0 ) {
							$sourceproduct = $product;
							break;
						}
					}
				}
			}
		}

		if ( $dontOverride ) {
			return $sourceproduct;
		}

		$overrideproduct = atkp_product::load( $this->productid );

		if ( $sourceproduct == null ) {
			$sourceproduct = $overrideproduct;
		} else {
			//override fields

			if ( $overrideproduct->title != '' ) {
				$sourceproduct->title = $overrideproduct->title;
			}

			if ( $overrideproduct->description_mode == 1 ) {
				$sourceproduct->description = $overrideproduct->description;
			} else if ( $overrideproduct->description_mode == 2 ) {
				$sourceproduct->description = '';
			}

			if ( $overrideproduct->mpn != '' ) {
				$sourceproduct->mpn = $overrideproduct->mpn;
			}
			if ( $overrideproduct->gtin != '' ) {
				$sourceproduct->gtin = $overrideproduct->gtin;
			}

			if ( $overrideproduct->predicate != '' ) {
				$sourceproduct->predicate = $overrideproduct->predicate;
			}

			if ( $overrideproduct->pro != '' ) {
				$sourceproduct->pro = $overrideproduct->pro;
			}
			if ( $overrideproduct->contra != '' ) {
				$sourceproduct->contra = $overrideproduct->contra;
			}

			if ( $overrideproduct->testresult != '' ) {
				$sourceproduct->testresult = $overrideproduct->testresult;
			}
			if ( $overrideproduct->testrating != '' ) {
				$sourceproduct->testrating = $overrideproduct->testrating;
			}
			if ( $overrideproduct->testdate != '' ) {
				$sourceproduct->testdate = $overrideproduct->testdate;
			}

			if ( $overrideproduct->ean != '' ) {
				$sourceproduct->ean = $overrideproduct->ean;
			}
			if ( $overrideproduct->brand != '' ) {
				$sourceproduct->brand = $overrideproduct->brand;
			}
			if ( $overrideproduct->isbn != '' ) {
				$sourceproduct->isbn = $overrideproduct->isbn;
			}

			if ( $overrideproduct->releasedate != '' ) {
				$sourceproduct->releasedate = $overrideproduct->releasedate;
			}
			if ( $overrideproduct->productgroup != '' ) {
				$sourceproduct->productgroup = $overrideproduct->productgroup;
			}

			if ( $overrideproduct->customerreviewurl != '' ) {
				$sourceproduct->customerreviewurl = $overrideproduct->customerreviewurl;
			}


			if ( $overrideproduct->mediumimageurl != '' ) {
				$sourceproduct->mediumimageurl = $overrideproduct->mediumimageurl;
			}
			if ( $overrideproduct->largeimageurl != '' ) {
				$sourceproduct->largeimageurl = $overrideproduct->largeimageurl;
			}
			if ( $overrideproduct->smallimageurl != '' ) {
				$sourceproduct->smallimageurl = $overrideproduct->smallimageurl;
			}
			if ( $overrideproduct->manufacturer != '' ) {
				$sourceproduct->manufacturer = $overrideproduct->manufacturer;
			}
			if ( $overrideproduct->author != '' ) {
				$sourceproduct->author = $overrideproduct->author;
			}
			if ( $overrideproduct->numberofpages > 0 ) {
				$sourceproduct->numberofpages = $overrideproduct->numberofpages;
			}

			$sourceproduct->features_mode    = $overrideproduct->features_mode;
			$sourceproduct->description_mode = $overrideproduct->description_mode;

			if ( $overrideproduct->features_mode == 1 ) {
				$sourceproduct->features = $overrideproduct->features;
			} else if ( $overrideproduct->features_mode == 2 ) {
				$sourceproduct->features = '';
			}

			if ( $overrideproduct->images != null && count( $overrideproduct->images ) > 0 ) {
				$sourceproduct->images = $overrideproduct->images;
			}

			if ( $overrideproduct->variations != null && count( $overrideproduct->variations ) > 0 ) {
				$sourceproduct->variations = $overrideproduct->variations;
			}

			if ( $overrideproduct->variationname != '' ) {
				$sourceproduct->variationname = $overrideproduct->variationname;
			}

			if ( $overrideproduct->rating > 0 ) {
				$sourceproduct->rating = $overrideproduct->rating;
			}
			if ( $overrideproduct->reviewcount > 0 ) {
				$sourceproduct->reviewcount = $overrideproduct->reviewcount;
			}
			if ( $overrideproduct->reviewsurl != '' ) {
				$sourceproduct->reviewsurl = $overrideproduct->reviewsurl;
			}

			//PRICE

			if ( atkp_options::$loader->get_priceasfallback() ) {
				if ( $sourceproduct->addtocarturl == '' && $overrideproduct->addtocarturl != '' ) {
					$sourceproduct->addtocarturl = $overrideproduct->addtocarturl;
				}
				if ( $sourceproduct->producturl == '' && $overrideproduct->producturl != '' ) {
					$sourceproduct->producturl = $overrideproduct->producturl;
				}

				if ( $sourceproduct->listpricefloat == 0 && $overrideproduct->listpricefloat != 0 ) {
					$sourceproduct->listpricefloat = $overrideproduct->listpricefloat;
					$sourceproduct->listprice      = $overrideproduct->listprice;
				}
				if ( $sourceproduct->percentagesavedfloat == 0 && $overrideproduct->percentagesavedfloat != 0 ) {
					$sourceproduct->percentagesavedfloat = $overrideproduct->percentagesavedfloat;
					$sourceproduct->percentagesaved      = $overrideproduct->percentagesaved;
				}
				if ( $sourceproduct->amountsavedfloat == 0 && $overrideproduct->amountsavedfloat != 0 ) {
					$sourceproduct->amountsavedfloat = $overrideproduct->amountsavedfloat;
					$sourceproduct->amountsaved      = $overrideproduct->amountsaved;
				}
				if ( $sourceproduct->salepricefloat == 0 && $overrideproduct->salepricefloat != 0 ) {
					$sourceproduct->salepricefloat = $overrideproduct->salepricefloat;
					$sourceproduct->saleprice      = $overrideproduct->saleprice;
				}
				if ( $sourceproduct->shippingfloat == 0 && $overrideproduct->shippingfloat != 0 ) {
					$sourceproduct->shippingfloat = $overrideproduct->shippingfloat;
					$sourceproduct->shipping      = $overrideproduct->shipping;
				}
				if ( $sourceproduct->basepricefloat == 0 && $overrideproduct->basepricefloat != 0 ) {
					$sourceproduct->basepricefloat = $overrideproduct->basepricefloat;
					$sourceproduct->baseprice      = $overrideproduct->baseprice;
				}

			} else {

				if ( $overrideproduct->addtocarturl != '' ) {
					$sourceproduct->addtocarturl = $overrideproduct->addtocarturl;
				}
				if ( $overrideproduct->producturl != '' ) {
					$sourceproduct->producturl = $overrideproduct->producturl;
				}

				if ( $overrideproduct->listprice != '' ) {
					$sourceproduct->listprice = $overrideproduct->listprice;
				}
				if ( $overrideproduct->amountsaved != '' ) {
					$sourceproduct->amountsaved = $overrideproduct->amountsaved;
				}
				if ( $overrideproduct->percentagesaved != '' ) {
					$sourceproduct->percentagesaved = $overrideproduct->percentagesaved;
				}
				if ( $overrideproduct->percentagesavedfloat != '' ) {
					$sourceproduct->percentagesavedfloat = $overrideproduct->percentagesavedfloat;
				}
				if ( $overrideproduct->saleprice != '' ) {
					$sourceproduct->saleprice = $overrideproduct->saleprice;
				}

				if ( $overrideproduct->shipping != '' ) {
					$sourceproduct->shipping = $overrideproduct->shipping;
				}

				if ( $overrideproduct->listpricefloat > 0 ) {
					$sourceproduct->listpricefloat = $overrideproduct->listpricefloat;
				}
				if ( $overrideproduct->amountsavedfloat > 0 ) {
					$sourceproduct->amountsavedfloat = $overrideproduct->amountsavedfloat;
				}
				if ( $overrideproduct->salepricefloat > 0 ) {
					$sourceproduct->salepricefloat = $overrideproduct->salepricefloat;
				}
				if ( $overrideproduct->shippingfloat > 0 ) {
					$sourceproduct->shippingfloat = $overrideproduct->shippingfloat;
				}


				if ( $overrideproduct->basepricefloat > 0 ) {
					$sourceproduct->basepricefloat = $overrideproduct->basepricefloat;
				}
				if ( $overrideproduct->baseprice != '' ) {
					$sourceproduct->baseprice = $overrideproduct->baseprice;
				}
			}

			if ( $overrideproduct->baseunit != '' ) {
				$sourceproduct->baseunit = $overrideproduct->baseunit;
			}
			if ( $overrideproduct->baseunits != '' ) {
				$sourceproduct->baseunits = $overrideproduct->baseunits;
			}
			if ( $overrideproduct->baseunits > 0 && $sourceproduct->baseunit != '' ) {
				$sourceproduct->basepricefloat = round( ( floatval( $sourceproduct->salepricefloat ) / floatval( $sourceproduct->baseunits ) ), 2 );
			}


			if ( $overrideproduct->availability != '' ) {
				$sourceproduct->availability = $overrideproduct->availability;
			}


			//if ( $overrideproduct->isprime != '' ) {
			$sourceproduct->isprime = $overrideproduct->isprime;
			//}
			//if ( $overrideproduct->iswarehouse != '' ) {
			$sourceproduct->iswarehouse = $overrideproduct->iswarehouse;
			//}

			//$sourceproduct->updatedon = $overrideproduct->updatedon;

			$sourceproduct->customfields  = $overrideproduct->customfields;
			$sourceproduct->displayfields = $overrideproduct->displayfields;
			$sourceproduct->metafields    = $overrideproduct->metafields;

			$sourceproduct->postids      = $overrideproduct->postids;
			$sourceproduct->outputashtml = $overrideproduct->outputashtml;
			$sourceproduct->sortorder    = $overrideproduct->sortorder;


		}

		$sourceproduct = apply_filters( 'atkp_load_mainproduct', $sourceproduct );

		return $sourceproduct;
	}

	/**
	 * @param atkp_product $a
	 * @param atkp_product $b
	 *
	 * @return int
	 */
	private function sortByPrice( $a, $b ) {
		$sortorder = ATKPSettings::$pricecomparisonsort;
		if ( $a->sortorder != '' && $a->sortorder > 0 ) {
			$sortorder = $a->sortorder;
		}

		switch ( $sortorder ) {
			case 1:
				$totalpriceA = $a->shippingfloat + $a->salepricefloat;
				$totalpriceB = $b->shippingfloat + $b->salepricefloat;
				break;
			case 2:
				$totalpriceA = $a->salepricefloat;
				$totalpriceB = $b->salepricefloat;
				break;
			case 3:
			default:
				if ( $a->ismainshop ) {
					$totalpriceA = 0;
				} else {
					$totalpriceA = $a->salepricefloat;
				}
				if ( $b->ismainshop ) {
					$totalpriceB = 0;
				} else {
					$totalpriceB = $b->salepricefloat;
				}
				break;
			case 4:
				if ( $a->ismainshop ) {
					$totalpriceA = 0;
				} else {
					$totalpriceA = $a->salepricefloat + $a->shippingfloat;
				}
				if ( $b->ismainshop ) {
					$totalpriceB = 0;
				} else {
					$totalpriceB = $b->salepricefloat + $b->shippingfloat;
				}
				break;
		}


		if ( $a->holdontop != 100 || $b->holdontop != 100 ) {
			if ( $sortorder == 3 || $sortorder == 4 && ! $a->ismainshop ) {
				$totalpriceA = floatval( $a->holdontop );
			}
			if ( $sortorder == 3 || $sortorder == 4 && ! $b->ismainshop ) {
				$totalpriceB = floatval( $b->holdontop );
			}
		}

		if ( $totalpriceA == $totalpriceB ) {
			$totalpriceA = floatval( $b->cpcfloat );
			$totalpriceB = floatval( $a->cpcfloat );
		}

		if ( $totalpriceA == $totalpriceB ) {
			return 0;
		}

		return ( $totalpriceA < $totalpriceB ) ? - 1 : 1;
	}

	private function sortByPrice_asc( $a, $b ) {

		switch ( ATKPSettings::$pricecomparisonsort ) {
			case 1:
			default:
				$totalpriceA = $a->shippingfloat + $a->salepricefloat;
				$totalpriceB = $b->shippingfloat + $b->salepricefloat;
				break;
			case 2:
				$totalpriceA = $a->salepricefloat;
				$totalpriceB = $b->salepricefloat;
				break;
		}


		if ( $a->holdontop != 100 || $b->holdontop != 100 ) {
			$totalpriceA = intval( $a->holdontop );
			$totalpriceB = intval( $b->holdontop );
		}

		if ( $totalpriceA == $totalpriceB ) {
			return 0;
		}

		return ( $totalpriceA < $totalpriceB ) ? - 1 : 1;
	}

	private function sortByPrice_desc( $a, $b ) {

		switch ( ATKPSettings::$pricecomparisonsort ) {
			case 1:
			default:
				$totalpriceA = $a->shippingfloat + $a->salepricefloat;
				$totalpriceB = $b->shippingfloat + $b->salepricefloat;
				break;
			case 2:
				$totalpriceA = $a->salepricefloat;
				$totalpriceB = $b->salepricefloat;
				break;
		}


		if ( $a->holdontop != 100 || $b->holdontop != 100 ) {
			$totalpriceA = intval( $a->holdontop );
			$totalpriceB = intval( $b->holdontop );
		}

		if ( $totalpriceA == $totalpriceB ) {
			return 0;
		}

		return ( $totalpriceA < $totalpriceB ) ? 1 : - 1;
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
}