<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Class atkp_shop
 *
 * @property atkp_shop_provider_base|null $provider The interface of the shop
 */
class atkp_shop {
	public $data = array();


	function __construct() {
		$this->id   = '';
		$this->type = atkp_shop_type::SINGLE_SHOP;

		$this->children  = array();
		$this->parent_id = 0;


		$this->title     = '';
		$this->programid = '';
		$this->shopid    = '';

		$this->feedurl      = '';
		$this->productcount = 0;

		$this->provider   = null;
		$this->settingid  = '';
		$this->webservice = '';

		$this->displayshoplogo       = false;
		$this->enablepricecomparison = false;
		$this->buyat                 = '';
		$this->addtocart             = '';
		$this->tooltip               = '';

		$this->customtitle          = '';
		$this->customsmalllogourl   = '';
		$this->customlogourl        = '';
		$this->customfield1         = '';
		$this->customfield2         = '';
		$this->customfield3         = '';
		$this->chartcolor           = '';
		$this->hidepricecomparision = false;
		$this->autogeneratesubshops = false;

		$this->redirection_type   = atkp_redirection_type::DISABLED;
		$this->redirection_apikey = '';

		$this->holdontop = 100;
	}

	/**
	 * @var atkp_shop[]
	 */
	public $children;

	public function get_addtocart() {
		return sprintf( $this->addtocart == '' ? __( 'Buy now at %s', ATKP_PLUGIN_PREFIX ) : $this->addtocart, $this->get_title() );
	}

	public function get_buyat() {
		return sprintf( $this->buyat == '' ? __( 'Buy now at %s', ATKP_PLUGIN_PREFIX ) : $this->buyat, $this->get_title() );
	}

	public function get_tooltip() {
		return sprintf( $this->tooltip == '' ? __( 'Buy now at %s', ATKP_PLUGIN_PREFIX ) : $this->tooltip, $this->get_title() );
	}

	public function get_title() {
		return $this->customtitle == '' ? $this->title : $this->customtitle;
	}

	public function get_logourl() {
		$xx = $this->customlogourl == '' ? $this->logourl : $this->customlogourl;

		if ( $xx == '' ) {
			$xx = $this->get_smalllogourl();
		}

		return $xx;
	}

	public function get_smalllogourl() {
		return $this->customsmalllogourl == '' ? $this->smalllogourl : $this->customsmalllogourl;
	}

	/**
	 * @param $shop
	 * @param $shopid
	 *
	 * @return atkp_shop|null
	 */
	public static function load_shopid( $shop, $shopid ) {

		if ( $shop == null || $shopid == '' ) {
			return null;
		}


		foreach ( $shop->children as $child ) {

			if ( $child->shopid == $shopid ) {

				return $child;
			}
		}

		return null;
	}

	/**
	 * @param string $saved_shopid
	 *
	 * @return atkp_shop[]
	 * @throws Exception
	 */
	public static function get_list( $shop_id = '' ) {
		$shops = array();

		$posts_array = get_posts( array(
			'fields'         => 'ids', // Only get post IDs
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_type'      => ATKP_SHOP_POSTTYPE,
			'posts_per_page' => - 1,
			'post_status'    => array( 'publish', 'draft' ),
			'post_parent'      => 0,
			'suppress_filters' => true
		) );

		foreach ( $posts_array as $post_id ) {
			$shop = self::load( $post_id );

			if ( $shop != null ) {
				if ( $shop_id == $post_id ) {
					$shop->selected = true;
				} else {
					$shop->selected = false;
				}

				foreach ( $shop->children as $c ) {

					if ( $shop_id == $c->id ) {
						$c->selected = true;
					} else {
						$c->selected = false;
					}
				}

				array_push( $shops, $shop );
			}
		}

		return $shops;
	}

	public static function exists( $post_id ) {

		if ( $post_id == null || $post_id == '' ) {
			return false;
		}

		$shop = get_post( $post_id );

		if ( ! isset( $shop ) || $shop == null ) {
			return false;
		}
		if ( $shop->post_type != ATKP_SHOP_POSTTYPE ) {
			return false;
		}

		return true;
	}

	/**
	 * @param $child_id
	 * @param $parent_id
	 * @param atkp_shop_provider_base|null $provider
	 *
	 * @return atkp_shop
	 */
	private static function load_subshop( $child_id, $parent_id, $myprovider = null ) {
		if ( $child_id == null || $child_id == '' ) {
			throw new Exception( '$child_id is empty' );
		}

		$shop = get_post( $child_id );

		if ( ! isset( $shop ) || $shop == null ) {
			throw new Exception( esc_html__( 'subshop not found: ' . $child_id, ATKP_PLUGIN_PREFIX ) );
		}
		if ( $shop->post_type != ATKP_SHOP_POSTTYPE ) {
			throw new Exception( esc_html__( 'invalid shop post_type: ' . $shop->post_type . ', $child_id: ' . $child_id, ATKP_PLUGIN_PREFIX ) );
		}

		$webservice = ATKPTools::get_post_setting( $parent_id, ATKP_SHOP_POSTTYPE . '_access_webservice' );

		$sshp            = new atkp_shop();
		$sshp->type      = atkp_shop_type::CHILD_SHOP;
		$sshp->id        = $child_id;
		$sshp->parent_id = $parent_id;
		$sshp->title     = ( $shop->post_title == '' ? $child_id : $shop->post_title );

		$sshp->settingid  = $parent_id;
		$sshp->webservice = $webservice;

		if ( $myprovider == null ) {
			$myprovider = atkp_shop_provider_base::retrieve_provider( $webservice );

			if ( $myprovider != null ) {
				$sshp->provider = $myprovider;
			} else {
				$sshp->provider = null;
			}
		} else {
			$sshp->provider = $myprovider;
		}

		$sshp->programid = ATKPTools::get_post_setting( $child_id, ATKP_SHOP_POSTTYPE . '_programid' );
		$sshp->shopid    = ATKPTools::get_post_setting( $child_id, ATKP_SHOP_POSTTYPE . '_shopid' );

		$sshp->feedurl      = ATKPTools::get_post_setting( $child_id, ATKP_SHOP_POSTTYPE . '_feedurl' );
		$sshp->productcount = ATKPTools::get_post_setting( $child_id, ATKP_SHOP_POSTTYPE . '_productcount' );

		$sshp->redirection_type   = ATKPTools::get_post_setting( $parent_id, ATKP_SHOP_POSTTYPE . '_redirectiontype' );
		$sshp->redirection_apikey = ATKPTools::get_post_setting( $parent_id, ATKP_SHOP_POSTTYPE . '_apikey' );


		$sshp->displayshoplogo       = (bool) ATKPTools::get_post_setting( $parent_id, ATKP_SHOP_POSTTYPE . '_displayshoplogo' );
		$sshp->enablepricecomparison = (bool) ATKPTools::get_post_setting( $parent_id, ATKP_SHOP_POSTTYPE . '_enableofferload' );
		$sshp->buyat                 = ATKPTools::get_post_setting( $parent_id, ATKP_SHOP_POSTTYPE . '_text_buyat' );
		$sshp->addtocart             = ATKPTools::get_post_setting( $parent_id, ATKP_SHOP_POSTTYPE . '_text_addtocart' );
		$sshp->tooltip               = ATKPTools::get_post_setting( $parent_id, ATKP_SHOP_POSTTYPE . '_text_tooltip' );

		$sshp->holdontop = ATKPTools::get_post_setting( $child_id, ATKP_SHOP_POSTTYPE . '_holdshopontop' );
		if ( $sshp->holdontop == '' ) {
			$sshp->holdontop = 100;
		} else {
			$sshp->holdontop = intval( $sshp->holdontop );
		}

		$sshp->hidepricecomparision = ATKPTools::get_post_setting( $child_id, ATKP_SHOP_POSTTYPE . '_hidepricecomparision' );


		$sshp->customtitle          = ATKPTools::get_post_setting( $child_id, ATKP_SHOP_POSTTYPE . '_customtitle' );
		$sshp->customsmalllogourl   = ATKPTools::get_post_setting( $child_id, ATKP_SHOP_POSTTYPE . '_customsmalllogourl' );
		$sshp->customlogourl        = ATKPTools::get_post_setting( $child_id, ATKP_SHOP_POSTTYPE . '_customlogourl' );
		$sshp->chartcolor           = ATKPTools::get_post_setting( $child_id, ATKP_SHOP_POSTTYPE . '_chartcolor' );
		$sshp->hidepricecomparision = ATKPTools::get_post_setting( $child_id, ATKP_SHOP_POSTTYPE . '_hidepricecomparision' );
		$sshp->customfield1         = ATKPTools::get_post_setting( $child_id, ATKP_SHOP_POSTTYPE . '_customfield1' );
		$sshp->customfield2         = ATKPTools::get_post_setting( $child_id, ATKP_SHOP_POSTTYPE . '_customfield2' );
		$sshp->customfield3         = ATKPTools::get_post_setting( $child_id, ATKP_SHOP_POSTTYPE . '_customfield3' );

		return $sshp;
	}

	public function get_filters() {
		$issubshop = $this->id != $this->parent_id;

		if ( $issubshop ) {
			return $this->shopid;
		} else {
			$shopids = array();

			foreach ( $this->children as $c ) {
				$shopids[] = $c->shopid;
			}

			return implode( ',', $shopids );
		}
	}

	public static function load( $post_id ) {

		if ( $post_id == null || $post_id == '' ) {
			return null;
			//throw new Exception( 'post_id is empty' );
		}

		$shop = get_post( $post_id );

		if ( ! isset( $shop ) || $shop == null ) {
			return null;
			//throw new Exception( 'shop not found: ' . $post_id );
		}
		if ( $shop->post_type != ATKP_SHOP_POSTTYPE ) {
			return null;
			//throw new Exception( 'invalid shop post_type: ' . $shop->post_type . ', $post_id: ' . $post_id );
		}


		if ( $shop->post_parent == 0 ) {
			//dann wird ein hauptshop geladen

			$shp            = new atkp_shop();
			$shp->id        = $post_id;
			$shp->parent_id = $post_id;
			$shp->settingid = $post_id;
			$shp->title     = ( $shop->post_title == '' ? $post_id : $shop->post_title );

			$shp->displayshoplogo       = (bool) ATKPTools::get_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_displayshoplogo' );
			$shp->enablepricecomparison = (bool) ATKPTools::get_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_enableofferload' );
			$shp->buyat                 = ATKPTools::get_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_text_buyat' );
			$shp->addtocart             = ATKPTools::get_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_text_addtocart' );
			$shp->tooltip               = ATKPTools::get_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_text_tooltip' );
			$shp->autogeneratesubshops  = ATKPTools::get_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_auto_generate_subshops' );

			$shp->holdontop            = ATKPTools::get_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_holdshopontop' );
			$shp->hidepricecomparision = ATKPTools::get_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_hidepricecomparision' );

			if ( $shp->holdontop == '' ) {
				$shp->holdontop = 100;
			} else {
				$shp->holdontop = intval( $shp->holdontop );
			}

			$shp->customtitle          = ATKPTools::get_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_customtitle' );
			$shp->customsmalllogourl   = ATKPTools::get_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_customsmalllogourl' );
			$shp->customlogourl        = ATKPTools::get_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_customlogourl' );
			$shp->chartcolor           = ATKPTools::get_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_chartcolor' );
			$shp->hidepricecomparision = ATKPTools::get_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_hidepricecomparision' );
			$shp->customfield1         = ATKPTools::get_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_customfield1' );
			$shp->customfield2         = ATKPTools::get_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_customfield2' );
			$shp->customfield3         = ATKPTools::get_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_customfield3' );


			$shp->redirection_type   = ATKPTools::get_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_redirectiontype' );
			$shp->redirection_apikey = ATKPTools::get_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_apikey' );

			$webservice      = ATKPTools::get_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_access_webservice' );
			$shp->webservice = $webservice;
			$myprovider      = atkp_shop_provider_base::retrieve_provider( $webservice );

			if ( $myprovider != null ) {
				$shp->provider = $myprovider;
			} else {
				$shp->provider = null;
			}


			$shp->type = apply_filters( 'atkp_get_shoptype', atkp_shop_type::SINGLE_SHOP, $post_id, $webservice );

			switch ( $shp->type ) {
				case atkp_shop_type::MULTI_SHOPS:
				case atkp_shop_type::SUB_SHOPS:

					$children = get_posts( array(
						'fields'         => 'ids', // Only get post IDs
						'post_type'      => ATKP_SHOP_POSTTYPE,
						'posts_per_page' => - 1,
						'post_parent'    => $post_id,
						'post_status'    => array( 'publish', 'draft' )
					) );

					foreach ( $children as $child_id ) {
						$sshp = self::load_subshop( $child_id, $post_id, $myprovider );

						array_push( $shp->children, $sshp );
					}

					break;
			}

		} else {
			//dann wird direkt ein subshop geladen

			return self::load_subshop( $post_id, $shop->post_parent );
		}

		return $shp;
	}

	/**
	 * @param atkp_shop $subshop
	 *
	 * @return null
	 * @throws Exception
	 */
	public static function create_subshop( $subshop ) {
		if ( $subshop->parent_id == null || $subshop->parent_id == '' ) {
			throw new Exception( 'post_id is empty' );
		}

		$shop = get_post( $subshop->parent_id );

		if ( ! isset( $shop ) || $shop == null ) {
			return null; //throw new Exception( 'shop not found: ' . $post_id );
		}
		if ( $shop->post_type != ATKP_SHOP_POSTTYPE ) {
			return null;  //throw new Exception( 'invalid shop post_type: ' . $shop->post_type . ', $post_id: ' . $post_id );
		}


		$children = get_posts( array(
			'fields'         => 'ids', // Only get post IDs
			'post_type'      => ATKP_SHOP_POSTTYPE,
			'posts_per_page' => - 1,
			'post_parent'    => $subshop->parent_id,
			'post_status'    => array( 'publish', 'draft', 'trash' )
		) );

		$child_id = 0;
		asort( $children );

		foreach ( $children as $cid ) {

			$shopid_child    = ATKPTools::get_post_setting( $cid, ATKP_SHOP_POSTTYPE . '_shopid' );
			$programid_child = ATKPTools::get_post_setting( $cid, ATKP_SHOP_POSTTYPE . '_programid' );

			if ( $shopid_child == $subshop->shopid && $programid_child == $subshop->programid ) {
				$child_id = $cid;
				break;
			}
		}


		if ( $child_id == 0 ) {
			//TODO: Create child
			global $user_ID;
			$new_post = array(
				'post_title'  => $subshop->title,
				'post_status' => 'publish',
				'post_author' => $user_ID,
				'post_type'   => ATKP_SHOP_POSTTYPE,
				'post_parent' => $subshop->parent_id,
			);
			$child_id = wp_insert_post( $new_post );

		} else {
			$my_post = array(
				'ID'          => $child_id,
				'post_title'  => $subshop->title,
				'post_status' => 'publish',
			);

			// Update the post into the database
			wp_update_post( $my_post );

			$children = self::get_posts_children( $child_id );

			foreach ( $children as $child ) {
				wp_delete_post( $child->ID );
			}
		}


		ATKPTools::set_post_setting( $child_id, ATKP_SHOP_POSTTYPE . '_feedurl', $subshop->feedurl );
		ATKPTools::set_post_setting( $child_id, ATKP_SHOP_POSTTYPE . '_productcount', $subshop->productcount );

		ATKPTools::set_post_setting( $child_id, ATKP_SHOP_POSTTYPE . '_shopid', $subshop->shopid );
		ATKPTools::set_post_setting( $child_id, ATKP_SHOP_POSTTYPE . '_programid', $subshop->programid );

		ATKPTools::set_post_setting( $child_id, ATKP_SHOP_POSTTYPE . '_access_webservice', ATKP_SUBSHOPTYPE );

		if ( $subshop->customtitle != '' ) {
			ATKPTools::set_post_setting( $child_id, ATKP_SHOP_POSTTYPE . '_customtitle', $subshop->customtitle );
		}
		if ( $subshop->customsmalllogourl != '' ) {
			ATKPTools::set_post_setting( $child_id, ATKP_SHOP_POSTTYPE . '_customsmalllogourl', $subshop->customsmalllogourl );
		}
		if ( $subshop->customlogourl != '' ) {
			ATKPTools::set_post_setting( $child_id, ATKP_SHOP_POSTTYPE . '_customlogourl', $subshop->customlogourl );
		}
		if ( $subshop->customfield1 != '' ) {
			ATKPTools::set_post_setting( $child_id, ATKP_SHOP_POSTTYPE . '_customfield1', $subshop->customfield1 );
		}
		if ( $subshop->customfield2 != '' ) {
			ATKPTools::set_post_setting( $child_id, ATKP_SHOP_POSTTYPE . '_customfield2', $subshop->customfield2 );
		}
		if ( $subshop->customfield3 != '' ) {
			ATKPTools::set_post_setting( $child_id, ATKP_SHOP_POSTTYPE . '_customfield3', $subshop->customfield3 );
		}

		$subshops   = array();
		$subshops[] = $subshop;

		ATKPTools::set_post_setting( $child_id, ATKP_SHOP_POSTTYPE . '_default_shops', $subshops );

		return $child_id;
	}

	private static function get_posts_children( $parent_id ) {
		$children = array();
		// grab the posts children
		$posts = get_posts( array( 'numberposts'      => - 1,
		                           'post_status'      => 'publish',
		                           'post_type'        => ATKP_SHOP_POSTTYPE,
		                           'post_parent'      => $parent_id,
		                           'suppress_filters' => false
		) );
		// now grab the grand children
		foreach ( $posts as $child ) {
			// recursion!! hurrah
			$gchildren = self::get_posts_children( $child->ID );
			// merge the grand children into the children array
			if ( ! empty( $gchildren ) ) {
				$children = array_merge( $children, $gchildren );
			}
		}
		// merge in the direct descendants we found earlier
		$children = array_merge( $children, $posts );

		return $children;
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