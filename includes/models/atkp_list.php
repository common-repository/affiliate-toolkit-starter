<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class atkp_list {
	public $data = array();

	function __construct() {
		$this->listid    = '';
		$this->title     = '';
		$this->updatedon = '';
		$this->message   = '';

		//1= dynamic / 2 = static
		$this->listtype     = '';
		$this->source       = '';
		$this->nodeid       = '';
		$this->keyword      = '';
		$this->filterfield1 = '';
		$this->filtertext1  = '';

		$this->filterfield2 = '';
		$this->filtertext2  = '';

		$this->filterfield3 = '';
		$this->filtertext3  = '';

		$this->filterfield4 = '';
		$this->filtertext4  = '';

		$this->filterfield5 = '';
		$this->filtertext5  = '';

		$this->products = '';
	}

	public static function get_offers( $listid, $ean, $shopid, $includemainoffer = false ) {
		$atkp_listtable_helper = new atkp_listtable_helper();
		$productlist           = $atkp_listtable_helper->load_list( $listid );

		$offers = array();
		$shops  = atkp_shop::get_list();
		$idx    = 0;
		foreach ( $productlist as $prdarray ) {
			if ( $prdarray['type'] == 'productid' ) {
				$collection = atkp_product_collection::load( $prdarray['value'] );
				$myproduct  = $collection->get_main_product();
			} else {
				$myproduct = $prdarray['value'];

			}
			if ( ! ATKPTools::str_contains( $myproduct->ean, $ean, false ) ) {
				continue;
			}

			$offer          = new atkp_product_offer();
			$offer->product = $myproduct;

			if ( $myproduct->shopid == $shopid ) {
				$offer->ismainoffer = true;
			} else {
				$offer->ismainoffer = false;
			}
			$offer->id     = $myproduct->productid;
			$offer->type   = 2;
			$offer->shopid = $myproduct->shopid;
			$offer->number = $myproduct->asin;

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
				foreach ( $shop->children as $child ) {
					if ( $myproduct->shopid == $child->id ) {
						$offer->holdontop = $child->holdontop;
						$offer->shop      = $child;
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

	public static function loadbyname( $name ) {
		$args  = array(
			'title'          => $name,
			'post_type'      => ATKP_LIST_POSTTYPE,
			'post_status'    => array( 'publish', 'draft' ),
			'posts_per_page' => - 1
		);
		$posts = get_posts( $args );


		if ( count( $posts ) == 0 ) {
			return null;
		} else {
			return atkp_list::load( $posts[0]->ID );
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

		$args  = array(
			'title'          => $name,
			'post_type'      => ATKP_LIST_POSTTYPE,
			'post_status'    => array( 'publish', 'draft' ),
			'posts_per_page' => 1
		);
		$posts = get_posts( $args );


		if ( count( $posts ) == 0 ) {
			return null;
		} else {
			return $posts[0]->ID;
		}
	}


	public static function load( $post_id ) {
		$list = get_post( $post_id );

		if ( ! isset( $list ) || $list == null ) {
			return null;
			//throw new Exception( 'list not found: ' . $post_id );
		}
		if ( $list->post_type != ATKP_LIST_POSTTYPE ) {
			return null;
			//throw new Exception( 'invalid post_type: ' . $list->post_type );
		}

		$prd = new atkp_list();

		$prd->title  = $list->post_title;
		$prd->listid = $post_id;

		$prd->updatedon = ATKPTools::get_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_updatedon' );
		$prd->message   = ATKPTools::get_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_message' );

		$prd->listtype = ATKPTools::get_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_listtype' );
		$prd->source   = ATKPTools::get_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_source' );
		$prd->nodeid   = ATKPTools::get_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_node_id' );
		$prd->keyword  = ATKPTools::get_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_keyword' );
		$prd->products = ATKPTools::get_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_products' );


		$prd->filterfield1 = ATKPTools::get_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_filterfield1' );
		$prd->filtertext1  = ATKPTools::get_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_filtertext1' );
		$prd->filterfield2 = ATKPTools::get_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_filterfield2' );
		$prd->filtertext2  = ATKPTools::get_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_filtertext2' );
		$prd->filterfield3 = ATKPTools::get_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_filterfield3' );
		$prd->filtertext3  = ATKPTools::get_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_filtertext3' );
		$prd->filterfield4 = ATKPTools::get_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_filterfield4' );
		$prd->filtertext4  = ATKPTools::get_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_filtertext4' );
		$prd->filterfield5 = ATKPTools::get_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_filterfield5' );
		$prd->filtertext5  = ATKPTools::get_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_filtertext5' );

		return $prd;
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


?>