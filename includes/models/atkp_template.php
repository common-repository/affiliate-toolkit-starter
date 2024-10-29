<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class atkp_template {
	public $data = array();

	function __construct() {
		$this->title              = '';
		$this->template_type      = '';
		$this->header             = '';
		$this->body               = '';
		$this->footer             = '';
		$this->disableddisclaimer = 0;
		$this->extendedview       = 0;
		$this->bodyheader         = '';
		$this->bodyfooter         = '';

		$this->detailheader = '';
		$this->detailfooter = '';

		$this->comparevalues = array();

		$this->css = '';

	}

	public static function get_directories( $add_systemdir = true ) {
		$views = array();
		//oxygen is returning "fake" as path
		if ( get_stylesheet_directory() != 'fake' ) {
			$views[] = get_stylesheet_directory() . '/atkp-templates';
			$views[] = get_template_directory() . '/atkp-templates';
		}
		if ( $add_systemdir ) {
			$views[] = ATKP_TEMPLATEDIR;
		}

		$views = apply_filters( 'atkp_template_directories', $views );

		$filtered = array();
		foreach ( $views as $view ) {
			if ( is_dir( $view ) ) {
				$filtered[] = $view;
			}
		}

		return $filtered;
	}

	public static function get_blade_directories( $add_systemdir = true ) {
		$views = array();
		//oxygen is returning "fake" as path
		if ( get_stylesheet_directory() != 'fake' ) {
			$views[] = get_stylesheet_directory() . '/atkp-templates/';
			$views[] = get_template_directory() . '/atkp-templates/';
		}
		if ( $add_systemdir ) {
			$views[] = ATKP_TEMPLATEDIR . '/';
		}

		$views = apply_filters( 'atkp_blade_directories', $views );

		$filtered = array();
		foreach ( $views as $view ) {
			if ( is_dir( $view ) ) {
				$filtered[] = $view;
			}
		}

		return $filtered;
	}

	public static function get_total() {
		global $wpdb;

		$query = "SELECT count(*) as cnt FROM {$wpdb->posts} WHERE post_type='atkp_template' ";

		$result = $wpdb->get_results( $query, ARRAY_A );

		$cnt = count( $result ) > 0 ? intval( $result[0]['cnt'] ) : 0;

		return $cnt;
	}

	public static function get_system_list( $per_page = 5, $page_number = 1, $orderby = '', $order = '' ) {
		$system_templates = self::get_list( true, false );
		$views            = atkp_template::get_blade_directories();

		$durations = apply_filters( 'atkp_get_template_types', array() );

		$result = array();
		foreach ( $system_templates as $system_template => $caption ) {
			if ( is_numeric( $system_template ) ) {
				continue;
			}

			$writetime = '';

			$templatepath = '';
			foreach ( $views as $view ) {
				if ( file_exists( $view . '/' . $system_template . '.blade.php' ) ) {
					$templatepath = $view . '/' . $system_template . '.blade.php';
					break;
				}
			}

			if ( file_exists( $templatepath ) ) {
				$writetime = date( "Y-m-d H:m:s", filemtime( $templatepath ) );
			}

			$template_type = apply_filters( 'atkp_template_get_type', '6', $system_template );

			$templatecap = '';
			foreach ( $durations as $value => $name ) {
				if ( $value == $template_type ) {
					$templatecap = $name;
					break;
				}
			}

			$xx       = array(
				'ID'               => $system_template,
				'post_title'       => $caption,
				'post_status'      => 'system',
				'post_type'        => 'atkp_template',
				'post_modified'    => $writetime,
				'template_path'    => $templatepath,
				'template_type'    => $templatecap,
				'template_type_id' => $template_type,
			);
			$result[] = $xx;
		}

		return $result;
	}

	public static function get_page_list( $per_page = 5, $page_number = 1, $orderby = '', $order = '' ) {

		global $wpdb;

		$sql = "SELECT * FROM {$wpdb->posts} where post_type='atkp_template' and post_status in ('draft', 'publish')";

		if ( ! empty( $orderby ) ) {
			$sql .= ' ORDER BY ' . esc_sql( $orderby );
			$sql .= ! empty( $order ) ? ' ' . esc_sql( $order ) : ' ASC';
		}

		$sql .= " LIMIT $per_page";
		$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;


		$result = $wpdb->get_results( $sql, 'ARRAY_A' );

		return $result;
	}

	public static function get_list( $add_systemdir = true, $is_searchform = false ) {
		$templates = array();

		if ( $is_searchform ) {


		} else {

			$templates['moreoffers']  = __( 'Additional offers (List)', ATKP_PLUGIN_PREFIX );
			$templates['moreoffers2'] = __( 'Additional offers (Dropdown)', ATKP_PLUGIN_PREFIX );
			$templates['moreoffers3'] = __( 'Additional offers (Buttons)', ATKP_PLUGIN_PREFIX );
			$templates['moreoffers4'] = __( 'Additional offers (Logos)', ATKP_PLUGIN_PREFIX );


			$templates['wide']                = __( 'Productbox with description (wide box)', ATKP_PLUGIN_PREFIX );
			$templates['secondwide']          = __( 'Productbox without description (wide box)', ATKP_PLUGIN_PREFIX );
			$templates['box']                 = __( 'Productbox (narrow box)', ATKP_PLUGIN_PREFIX );
			$templates['detailoffers']        = __( 'Price comparison', ATKP_PLUGIN_PREFIX );
			$templates['detailoffers_nologo'] = __( 'Price comparison (no logo)', ATKP_PLUGIN_PREFIX );
			$templates['offers_table']        = __( 'Price comparison (simple)', ATKP_PLUGIN_PREFIX );
			$templates['grid_2_columns']      = __( 'Grid with 2 columns', ATKP_PLUGIN_PREFIX );
			$templates['grid_3_columns']      = __( 'Grid with 3 columns', ATKP_PLUGIN_PREFIX );
			$templates['list_display']        = __( 'Numbered product list', ATKP_PLUGIN_PREFIX );

			$templates['notavailable'] = __( 'Box (Product is not available)', ATKP_PLUGIN_PREFIX );


			$templates['bestseller'] = __( 'Bestsellerbox with description', ATKP_PLUGIN_PREFIX );

			$templates['productbox'] = __( 'Productbox with description and variations (wide box)', ATKP_PLUGIN_PREFIX );
		}


		$args        = array(
			'post_type'      => ATKP_TEMPLATE_POSTTYPE,
			'posts_per_page' => 300,
			'post_status'    => array( 'publish', 'draft' )
		);
		$posts_array = get_posts( $args );
		foreach ( $posts_array as $prd ) {
			$type = ATKPTools::get_post_setting( $prd->ID, ATKP_TEMPLATE_POSTTYPE . '_template_type' );

			if ( $is_searchform ) {
				if ( $type == 5 ) {
					$templates[ $prd->ID . '' ] = $prd->post_title;
				}
			} else {
				if ( $type != 5 ) {
					$templates[ $prd->ID . '' ] = $prd->post_title;
				}
			}
		};


		$bladedirs = atkp_template::get_blade_directories( $add_systemdir );

		foreach ( $bladedirs as $dir ) {

			$files = scandir( $dir );
			foreach ( $files as $file ) {
				if ( $file == '.' || $file == '..' ) {
					continue;
				}

				$path_parts = pathinfo( $file );
				$tempname   = str_replace( '.blade', '', $path_parts['filename'] );

				if ( ! isset( $templates[ $tempname ] ) ) {
					$templates[ $tempname ] = $tempname;
				}
			}

		}

		$templates = apply_filters( 'atkp_template_list', $templates, $add_systemdir, $is_searchform );

		natcasesort( $templates );

		return $templates;
	}


	public static function get_preview_list( $add_systemdir = true, $is_searchform = false ) {
		$templates = self::get_list( $add_systemdir, $is_searchform );
		$templates = apply_filters( 'atkp_template_preview_list', $templates, $add_systemdir, $is_searchform );

		return $templates;
	}

	public static function load( $post_id ) {


		$product = get_post( $post_id );

		if ( ! isset( $product ) || $product == null ) {
			throw new Exception( esc_html__( 'template not found: ' . $post_id, ATKP_PLUGIN_PREFIX ) );
		}
		if ( $product->post_type != ATKP_TEMPLATE_POSTTYPE ) {
			throw new Exception( esc_html__( 'invalid post_type: ' . $product->post_type . ', $post_id: ' . $post_id, ATKP_PLUGIN_PREFIX ) );
		}

		$prd = new atkp_template();

		$prd->title              = $product->post_title;
		$prd->template_type      = ATKPTools::get_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_template_type' );
		$prd->header             = ATKPTools::get_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_header' );
		$prd->body               = ATKPTools::get_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_body' );
		$prd->footer             = ATKPTools::get_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_footer' );
		$prd->disableddisclaimer = ATKPTools::get_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_disabledisclaimer' );
		$prd->extendedview       = ATKPTools::get_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_extendedview' );
		$prd->bodyheader         = ATKPTools::get_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_body_header' );
		$prd->bodyfooter         = ATKPTools::get_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_body_footer' );

		$prd->detailheader = ATKPTools::get_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_detail_header' );
		$prd->detailfooter = ATKPTools::get_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_detail_footer' );

		$prd->css = ATKPTools::get_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_css' );

		$prd->comparevalues = atkp_template_comparevalue::load_comparevalues( $post_id );

		$prd->horizontalscrollbars = ATKPTools::get_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_horizontalscrollbars' );
		$prd->hideheaders          = ATKPTools::get_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_hideheaders' );
		$prd->maxmobileproducts    = ATKPTools::get_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_maxmobileproducts' );
		$prd->maxproducts          = ATKPTools::get_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_maxproducts' );
		$prd->viewtype             = ATKPTools::get_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_viewtype' );
		$prd->mobilebody           = ATKPTools::get_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_mobilebody' );


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