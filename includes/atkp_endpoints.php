<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly


class atkp_endpoints {
	/**
	 * Construct the plugin object
	 */
	public function __construct( $pluginbase ) {
		add_action( 'wp_ajax_atkp_search_departments', array( &$this, 'atkp_search_departments' ) );
		add_action( 'wp_ajax_atkp_search_products', array( &$this, 'atkp_search_products' ) );
		add_action( 'wp_ajax_atkp_search_browsenodes', array( &$this, 'atkp_search_browsenodes' ) );
		add_action( 'wp_ajax_atkp_search_filters', array( &$this, 'atkp_search_filters' ) );


		add_action( 'wp_ajax_atkp_get_object', array( &$this, 'atkp_get_object' ) );
		add_action( 'wp_ajax_atkp_import_product', array( &$this, 'atkp_import_product' ) );
		add_action( 'wp_ajax_atkp_create_list', array( &$this, 'atkp_create_list' ) );

		add_action( 'wp_ajax_atkp_clear_logfile', array( &$this, 'atkp_clear_logfile' ) );

		add_action( 'wp_ajax_atkp_reset_products', array( &$this, 'atkp_reset_products' ) );
		add_action( 'wp_ajax_atkp_reset_lists', array( &$this, 'atkp_reset_lists' ) );

		add_action( 'wp_ajax_atkp_reset_settings', array( &$this, 'atkp_reset_settings' ) );

		add_action( 'wp_ajax_atkp_download_logfile', array( &$this, 'atkp_download_logfile' ) );

		add_action( 'wp_ajax_atkp_search_local_products', array( &$this, 'atkp_search_local_products' ) );

		add_action( 'wp_ajax_atkp_live_search_backend', array( &$this, 'atkp_live_search_backend' ) );

		add_action( 'wp_ajax_atkp_export_template', array( &$this, 'atkp_export_template' ) );

		if ( atkp_options::$loader->get_ajax_loading_enabled() || atkp_options::$loader->get_ajax_handler_enabled() ) {
			add_action( 'wp_ajax_nopriv_atkp_render_template', array( &$this, 'atkp_render_template' ) );
		}
		add_action( 'wp_ajax_atkp_render_template', array( &$this, 'atkp_render_template' ) );

		add_action( 'wp_ajax_atkp_send_report', array( &$this, 'atkp_send_report' ) );


	}

	public function atkp_send_report() {
		try {
			$nounce = ATKPTools::get_get_parameter( 'request_nonce', 'string' );

			if ( ! wp_verify_nonce( $nounce, 'atkp-send-report' ) ) {
				throw new Exception( 'Nonce expired. Please reload page.' );
			}
			if ( ! current_user_can( 'manage_options' ) ) {
				throw new Exception( 'User has no permission.' );
			}

			$atkp_queueservices = new atkp_queueservices();

			$atkp_queueservices->send_data_report( true );

			header( 'Location: ' . $_SERVER['HTTP_REFERER'] );
			exit;

		} catch ( Exception $e ) {
			ATKPLog::LogError( $e->getMessage() );

			$gif_data = array(
				'status'  => 'error',
				'error'   => 'An error has occurred.',
				'message' => $e->getMessage(),
			);

			wp_send_json( $gif_data );
		}
	}

	public function atkp_render_template() {
		try {
			//render
			$products_str   = $_REQUEST['products'];
			$parameters_str = $_REQUEST['parameters'];
			$preview        = isset( $_REQUEST['preview'] ) ? $_REQUEST['preview'] : false;

			$product_ids     = json_decode( stripslashes( $products_str ), true );
			$parameters_data = json_decode( stripslashes( $parameters_str ), true );

			$parameters = new atkp_template_parameters();
			$da         = array();
			foreach ( $parameters_data as $key => $val ) {
				$name        = str_replace( ATKP_PLUGIN_PREFIX . '_', '', $key );
				$da[ $name ] = $val;
			}
			$parameters->data = $da;

			if ( $parameters->offerstemplate == '' ) {
				$parameters->offerstemplate = 'moreoffers';
			}


			$products = array();
			$lists    = array();

			foreach ( $product_ids as $product_id ) {
				$listid = $product_id['list_id'];
				$list   = array();
				if ( $listid > 0 && ! isset( $lists[ $listid ] ) ) {
					$list_idx[ $listid ]    = 0;
					$atkp_listtable_helper  = new atkp_listtable_helper();
					$selectedshopid         = ATKPTools::get_post_setting( $listid, ATKP_LIST_POSTTYPE . '_shopid' );
					$preferlocalproductinfo = ATKPTools::get_post_setting( $listid, ATKP_LIST_POSTTYPE . '_preferlocalproduct' );
					$productlist            = $atkp_listtable_helper->load_list( $listid, $selectedshopid );

					foreach ( $productlist as $p ) {
						$type  = $p['type'];
						$value = $p['value'];

						switch ( $type ) {
							case 'product':
								//nur nach lokalen produkten suchen wenn in der
								if ( $preferlocalproductinfo ) {
									$prdfound = atkp_product::loadbyasin( $value->asin );

									if ( $prdfound != null ) {
										$prodcollection = atkp_product_collection::load( $prdfound->productid );
										if ( $prodcollection != null ) {
											$value         = $prodcollection->get_main_product();
											$value->listid = $listid;
										} else {
											$value = null;
										}
									}
								} else if ( $value != null ) {
									$value->listid = $listid;
								}

								break;
							case 'productid':
								if ( get_post_status( $value ) == 'publish' || get_post_status( $value ) == 'draft' ) {
									$prodcollection = atkp_product_collection::load( $value, $selectedshopid );
									if ( $prodcollection != null ) {
										$value = $prodcollection->get_main_product( $selectedshopid );
									} else {
										$value = null;
									}
								} else {
									$value = null;
								}

								break;
						}

						$list[] = $value;
					}
					$lists[ $listid ] = $list;
				}
			}


			foreach ( $product_ids as $product_id ) {
				$prdid    = $product_id['product_id'];
				$listid   = $product_id['list_id'];
				$list_idx = $product_id['list_idx'];

				if ( $listid > 0 ) {
					$list       = $lists[ $listid ];
					$products[] = $list[ $list_idx ];

				} else {
					$prd_coll   = atkp_product_collection::load( $prdid );
					$products[] = $prd_coll->get_main_product();
				}
			}

			$products = apply_filters( 'atkp_ajax_products', $products, $parameters );

			$templatehelper                     = new atkp_template_helper();
			$templatehelper->preview_generation = $preview;
			$resultValue                        = $templatehelper->createAjaxOutput( $products, $parameters );

			wp_send_json( array( 'status' => 'okay', 'html' => $resultValue ) );

		} catch ( Exception $e ) {
			ATKPLog::LogError( $e->getMessage() );

			$gif_data = array(
				'status'  => 'error',
				'error'   => 'An error has occurred.',
				'message' => $e->getMessage(),
			);

			wp_send_json( $gif_data );
		}
	}

	function atkp_export_template() {
		try {
			$nounce = ATKPTools::get_get_parameter( 'request_nonce', 'string' );

			if ( ! wp_verify_nonce( $nounce, 'atkp-export-template' ) ) {
				throw new Exception( 'Nonce expired. Please reload page.' );
			}
			if ( ! current_user_can( 'manage_options' ) )
				throw new Exception( 'User has no permission.' );

			//if ( !wp_verify_nonce( $nounce, 'atkp-export-template' ) )
			//	throw new Exception('Nonce invalid');

			$templateid = ATKPTools::get_get_parameter( 'templateid', 'int' );

			//$atkp_template = atkp_template::load( $templateid );

			$postmetas = get_post_meta( $templateid);

			$array_fields = array();
			foreach ( $postmetas as $meta_key => $meta_value ) {
				if ( substr( $meta_key, 0, 5 ) == 'atkp_' ) {
					$array_fields[ $meta_key ] = ( $meta_value );
				}
			}

			$string = json_encode( array( 'template_id'   => $templateid,
			                              'template_name' => get_the_title( $templateid ),
			                              'fields'        => $array_fields
			), JSON_PRETTY_PRINT );
			$name   = sanitize_title( get_the_title( $templateid ) );

			# send the file to the browser as a download

			header( "Pragma: public" );
			header( "Expires: 0" );
			header( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
			header( "Cache-Control: public" );
			header( "Content-Description: File Transfer" );
			header( "Content-type: application/octet-stream" );
			header( "Content-Disposition: attachment; filename=\"" . $name . ".json\"" );
			header( "Content-Transfer-Encoding: utf-8" );
			header( "Content-Length: " . strlen( $string ) );

			echo esc_js($string);

			exit;
		} catch ( Exception $e ) {
			ATKPLog::LogError( $e->getMessage() );

			$gif_data[] = array(
				'error'   => 'An error has occurred.',
				'message' => $e->getMessage(),
			);

			wp_send_json( $gif_data );
		}
	}

	function atkp_reset_products() {
		try {
			$nounce = ATKPTools::get_get_parameter( 'request_nonce', 'string' );

			if ( ! wp_verify_nonce( $nounce, 'atkp-download-log' ) )
				throw new Exception( 'Nonce expired. Please reload page.' );
			if ( ! current_user_can( 'manage_options' ) )
				throw new Exception( 'User has no permission.' );

			global $wpdb;
			$table = $wpdb->prefix . 'postmeta';
			$wpdb->delete( $table, array( 'meta_key' => ATKP_PRODUCT_POSTTYPE . '_updatedon' ) );


			header( 'Location: ' . $_SERVER['HTTP_REFERER'] );
			exit;

		} catch ( Exception $e ) {
			ATKPLog::LogError( $e->getMessage() );

			$gif_data[] = array(
				'error'   => 'An error has occurred.',
				'message' => $e->getMessage(),
			);

			wp_send_json( $gif_data );
		}
	}


	function atkp_reset_settings() {
		try {
			$nounce = ATKPTools::get_get_parameter( 'request_nonce', 'string' );

			if ( ! wp_verify_nonce( $nounce, 'atkp-download-log' ) )
				throw new Exception( 'Nonce expired. Please reload page.' );
			if ( ! current_user_can( 'manage_options' ) )
				throw new Exception( 'User has no permission.' );

			global $wpdb;

			echo esc_html__($wpdb->query( 'DELETE FROM `' . $wpdb->prefix . 'options` where option_name like \'atkp_%\'' ));
			echo esc_html__($wpdb->query( 'DELETE FROM `' . $wpdb->prefix . 'comments` where comment_post_ID in (select ID FROM `' . $wpdb->prefix . 'posts` where post_type like \'atkp_%\')' ));
			echo esc_html__($wpdb->query( 'DELETE FROM `' . $wpdb->prefix . 'term_relationships` where object_id in (select ID FROM `' . $wpdb->prefix . 'posts` where post_type like \'atkp_%\')' ));
			echo esc_html__($wpdb->query( 'DELETE FROM `' . $wpdb->prefix . 'postmeta` where meta_key like \'atkp_%\'' ));
			echo esc_html__($wpdb->query( 'DELETE FROM `' . $wpdb->prefix . 'posts` where post_type like \'atkp_%\'' ));


			echo esc_html__($wpdb->query( 'DROP TABLE `' . strtolower( $wpdb->prefix . ATKP_PLUGIN_PREFIX . '_products' ) . '`' ));
			echo esc_html__($wpdb->query( 'DROP TABLE `' . strtolower( $wpdb->prefix . ATKP_PLUGIN_PREFIX . '_lists' ) . '`' ));
			echo esc_html__($wpdb->query( 'DROP TABLE `' . strtolower( $wpdb->prefix . ATKP_PLUGIN_PREFIX . '_queues' ) . '`' ));

			echo esc_html__($wpdb->query( 'DROP TABLE `' . strtolower( $wpdb->prefix . ATKP_PLUGIN_PREFIX . '_productdata' ) . '`' ));
			echo esc_html__($wpdb->query( 'DROP TABLE `' . strtolower( $wpdb->prefix . ATKP_PLUGIN_PREFIX . '_offertable' ) . '`' ));

			//header( 'Refresh:5; url='.$_SERVER['HTTP_REFERER'], true, 303);
			header( 'Location: ' . $_SERVER['HTTP_REFERER'] );
			exit;

		} catch ( Exception $e ) {
			ATKPLog::LogError( $e->getMessage() );

			$gif_data[] = array(
				'error'   => 'An error has occurred.',
				'message' => $e->getMessage(),
			);

			wp_send_json( $gif_data );
		}
	}


	function atkp_reset_lists() {
		try {
			$nounce = ATKPTools::get_get_parameter( 'request_nonce', 'string' );

			if ( ! wp_verify_nonce( $nounce, 'atkp-download-log' ) )
				throw new Exception( 'Nonce expired. Please reload page.' );
			if ( ! current_user_can( 'manage_options' ) )
				throw new Exception( 'User has no permission.' );

			global $wpdb;
			$table = $wpdb->prefix . 'postmeta';
			$wpdb->delete( $table, array( 'meta_key' => ATKP_LIST_POSTTYPE . '_updatedon' ) );


			header( 'Location: ' . $_SERVER['HTTP_REFERER'] );
			exit;

		} catch ( Exception $e ) {
			ATKPLog::LogError( $e->getMessage() );

			$gif_data[] = array(
				'error'   => 'An error has occurred.',
				'message' => $e->getMessage(),
			);

			wp_send_json( $gif_data );
		}
	}


	function atkp_clear_logfile() {
		try {
			$nounce = ATKPTools::get_get_parameter( 'request_nonce', 'string' );

			if ( ! wp_verify_nonce( $nounce, 'atkp-download-log' ) )
				throw new Exception( 'Nonce expired. Please reload page.' );
			if ( ! current_user_can( 'manage_options' ) )
				throw new Exception( 'User has no permission.' );

			if ( file_exists( ATKP_LOGFILE ) ) {
				unlink( ATKP_LOGFILE );
			}

			header( 'Location: ' . $_SERVER['HTTP_REFERER'] );
			exit;

		} catch ( Exception $e ) {
			ATKPLog::LogError( $e->getMessage() );

			$gif_data[] = array(
				'error'   => 'An error has occurred.',
				'message' => $e->getMessage(),
			);

			wp_send_json( $gif_data );
		}
	}


	function atkp_download_logfile() {
		try {
			$nounce = ATKPTools::get_get_parameter( 'request_nonce', 'string' );

			if ( ! wp_verify_nonce( $nounce, 'atkp-download-log', false ) )
				throw new Exception( 'Nonce expired. Please reload page.' );
			if ( ! current_user_can( 'manage_options' ) )
				throw new Exception( 'User has no permission.' );

			$string = '';
			if ( file_exists( ATKP_LOGFILE ) ) {
				$string = file_get_contents( ATKP_LOGFILE );
			}

			$name = sanitize_title( 'affiliate-toolkit-log.txt' );

			# send the file to the browser as a download

			header( "Pragma: public" );
			header( "Expires: 0" );
			header( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
			header( "Cache-Control: public" );
			header( "Content-Description: File Transfer" );
			header( "Content-type: text/plain" );
			header( "Content-Disposition: attachment; filename=\"" . $name . ".txt\"" );
			header( "Content-Transfer-Encoding: utf-8" );
			header( "Content-Length: " . strlen( $string ) );

			echo esc_js($string);

			exit;
		} catch ( Exception $e ) {
			ATKPLog::LogError( $e->getMessage() );

			$gif_data[] = array(
				'error'   => 'An error has occurred.',
				'message' => $e->getMessage(),
			);

			wp_send_json( $gif_data );
		}
	}

	function atkp_create_list() {
		try {
			$nounce = ATKPTools::get_post_parameter( 'request_nonce', 'string' );

			if ( ! wp_verify_nonce( $nounce, 'atkp-import-nonce', false ) )
				throw new Exception( 'Nonce expired. Please reload page.' );
			if ( ! current_user_can( 'edit_posts' ) )
				throw new Exception( 'User has no permission.' );

			$shopid     = ATKPTools::get_post_parameter( 'shop', 'string' );
			$searchterm = ATKPTools::get_post_parameter( 'searchterm', 'string' );
			$listtype   = ATKPTools::get_post_parameter( 'listtype', 'string' );
			$title      = ATKPTools::get_post_parameter( 'title', 'string' );

			$department     = ATKPTools::get_post_parameter( 'department', 'string' );
			$sortby         = ATKPTools::get_post_parameter( 'sortby', 'string' );
			$loadmoreoffers = ATKPTools::get_post_parameter( 'loadmoreoffers', 'bool' );

			$globaltools = new atkp_global_tools();

			$gif_data = $globaltools->atkp_create_list( $shopid, $title, $listtype, $searchterm, $department, $sortby, $loadmoreoffers );

			wp_send_json( $gif_data );

		} catch ( Exception $e ) {
			ATKPLog::LogError( $e->getMessage() );

			$gif_data[] = array(
				'error'   => 'An error has occurred.',
				'message' => $e->getMessage(),
			);

			wp_send_json( $gif_data );
		}
	}

	function atkp_import_product() {
		try {
			$nounce = ATKPTools::get_post_parameter( 'request_nonce', 'string' );

			if ( ! wp_verify_nonce( $nounce, 'atkp-import-nonce' ) )
				throw new Exception( 'Nonce expired. Please reload page.' );
			if ( ! current_user_can( 'edit_posts' ) )
				throw new Exception( 'User has no permission.' );

			$shopid    = ATKPTools::get_post_parameter( 'shop', 'string' );
			$asin      = ATKPTools::get_post_parameter( 'asin', 'string' );
			$asintype  = ATKPTools::get_post_parameter( 'asintype', 'string' );
			$title     = ATKPTools::get_post_parameter( 'title', 'string' );
			$status    = ATKPTools::get_post_parameter( 'status', 'string' );
			$importurl = ATKPTools::get_post_parameter( 'importurl', 'allhtml' );

			$subshopid = ATKPTools::get_post_parameter( 'subshopid', 'string' );

			$brand = ATKPTools::get_post_parameter( 'brand', 'string' );
			$mpn   = ATKPTools::get_post_parameter( 'mpn', 'string' );

			if ( $shopid == '' ) {
				throw new Exception( 'shop required' );
			}
			if ( $asin == '' ) {
				throw new Exception( 'asin required' );
			}

			$globaltools = new atkp_global_tools();

			$gif_data = $globaltools->atkp_import_product( $shopid, $asin, $asintype, $title, $status, $importurl, $brand, $mpn, $subshopid );

			wp_send_json( $gif_data );

		} catch ( Exception $e ) {
			ATKPLog::LogError( $e->getMessage() );

			$gif_data[] = array(
				'error'   => 'An error has occurred.',
				'message' => $e->getMessage(),
			);

			wp_send_json( $gif_data );
		}
	}

	function atkp_get_object() {
		try {
			$nounce = ATKPTools::get_post_parameter( 'request_nonce', 'string' );

			if ( ! wp_verify_nonce( $nounce, 'atkp-get-nonce' ) )
				throw new Exception( 'Nonce expired. Please reload page.' );


			$post_type = ATKPTools::get_post_parameter( 'post_type', 'string' );
			$id        = ATKPTools::get_post_parameter( 'post_id', 'string' );

			if ( $post_type == '' ) {
				throw new Exception( 'post_type required' );
			}
			if ( $id == '' ) {
				throw new Exception( 'id required' );
			}

			$gif_data[] = array();

			switch ( $post_type ) {
				case ATKP_PRODUCT_POSTTYPE:
				case ATKP_LIST_POSTTYPE:
					$gif_data['post_id']   = $id;
					$gif_data['post_type'] = $post_type;
					$gif_data['title']     = get_the_title( $id );
					$gif_data['edit_url']  = get_edit_post_link( $id );
					break;
				default:
					throw new exception( 'unknown posttype: ' . $post_type );
			}

			wp_send_json( $gif_data );

		} catch ( Exception $e ) {
			ATKPLog::LogError( $e->getMessage() );

			$gif_data[] = array(
				'error'   => 'An error has occurred.',
				'message' => $e->getMessage(),
			);

			wp_send_json( $gif_data );
		}
	}

	function atkp_live_search_backend() {
		try {
			$html = $this->liveSearchBackend();

			$gif_data[] = array(
				'html' => $html,
			);

			wp_send_json( $gif_data );

		} catch ( Exception $e ) {
			ATKPLog::LogError( $e->getMessage() );

			$gif_data[] = array(
				'error'   => 'An error has occurred.',
				'message' => $e->getMessage(),
			);

			wp_send_json( $gif_data );
		}
	}

	function atkp_search_local_products() {

		try {
			ob_start();
			$azproducts = $this->localSearch();

			$data = ob_get_clean();

			if ( $azproducts != null ) {
				wp_send_json( $azproducts );

			}
		} catch ( Exception $e ) {
			ATKPLog::LogError( $e->getMessage() );

			$gif_data[] = array(
				'error'   => 'An error has occurred.',
				'message' => $e->getMessage(),
			);

			wp_send_json( $gif_data );
		}

	}

	function atkp_search_products() {
		try {
			$azproducts = $this->quickSearch( 'product' );

			if ( $azproducts != null ) {
				wp_send_json( $azproducts );
			}
		} catch ( Exception $e ) {
			ATKPLog::LogError( $e->getMessage() );

			$gif_data[] = array(
				'error'   => 'An error has occurred.',
				'message' => $e->getMessage(),
			);

			wp_send_json( $gif_data );
		}
	}

	function atkp_search_filters() {
		try {
			$azproducts = $this->quickSearch( 'filter' );

			if ( $azproducts != null ) {
				wp_send_json( $azproducts );
			}
		} catch ( Exception $e ) {
			ATKPLog::LogError( $e->getMessage() );

			$gif_data[] = array(
				'error'   => 'An error has occurred.',
				'message' => $e->getMessage(),
			);

			wp_send_json( $gif_data );
		}
	}

	function atkp_search_departments() {
		try {
			$azproducts = $this->quickSearch( 'department' );

			if ( $azproducts != null ) {
				wp_send_json( $azproducts );
			}
		} catch ( Exception $e ) {
			ATKPLog::LogError( $e->getMessage() );

			$gif_data[] = array(
				'error'   => 'An error has occurred.',
				'message' => $e->getMessage(),
			);

			wp_send_json( $gif_data );
		}
	}

	function atkp_search_browsenodes() {
		try {
			$aznodes = $this->quickSearch( 'browsenode' );

			if ( $aznodes != null ) {
				wp_send_json( $aznodes );
			}
		} catch ( Exception $e ) {
			ATKPLog::LogError( $e->getMessage() );

			$gif_data[] = array(
				'error'   => 'An error has occurred.',
				'message' => $e->getMessage(),
			);

			wp_send_json( $gif_data );
		}
	}

	function liveSearchBackend() {
		$shopid       = ATKPTools::get_post_parameter( 'shopid', 'int' );
		$keyword      = ATKPTools::get_post_parameter( 'keyword', 'string' );
		$searchoption = ATKPTools::get_post_parameter( 'searchoption', 'string' );
		$asintype     = ATKPTools::get_post_parameter( 'asintype', 'string' );

		if ( $keyword == '' ) {
			return __( 'Search term is required', ATKP_PLUGIN_PREFIX );
		}

		$shop = atkp_shop::load( $shopid );

		$resultVal = '';

		if ( $shop != null && $shop->provider != null ) {

			$resultVal = '
						<table class="wp-list-table widefat fixed striped" style="width:99%;">
                <thead>
                <tr>
                    <th scope="col" class="manage-column" style="width:20px">
						<input type="checkbox" class="selectAll" />
                    </th>
                    <th scope="col" class="manage-column"
                        style="width: 100px;text-align:center">
								        ' . __( 'Image', ATKP_PLUGIN_PREFIX ) . '
                    </th>

                    <th scope="col" class="manage-column column-primary">
								        ' . __( 'Title', ATKP_PLUGIN_PREFIX ) . '
                    </th>

                    <th scope="col" class="manage-column" style="width:210px"
                    ">
						        ' . __( 'Status', ATKP_PLUGIN_PREFIX ) . '
                    </th>

                </tr>
                </thead>

                <tbody>
						';


			try {
				$shop->provider->checklogon( $shop );
				$result = $shop->provider->quick_search( $keyword, $searchoption == 'asin' || $searchoption == 'ean' || $searchoption == 'articlenumber' ? $searchoption : 'product', 1 );

				if ( isset( $result ) && $result != null) {
					foreach ( $result->products as $product ) {
						$asin2 = '';
						switch ( strtoupper( $asintype ) ) {
							default:
							case 'ASIN':
								$asin2 = $product['asin'];
								break;
							case 'EAN':
								$asin2 = isset( $product['ean'] ) ? $product['ean'] : '';
								break;
							case 'TITLE':
								$asin2 = $product['title'];
								break;
							case 'ARTICLENUMBER':
								$asin2 = isset( $product['articlenumber'] ) ? $product['articlenumber'] : '';
								break;

						}


						$asin      = $product['asin'];
						$resultVal .= '
												<tr>
														<td><input class="atkp-checkboxstyle" type="checkbox" name="atkp_prd_' . $shop->id . '_' . $asin . '" class="atkp-productimport" asintype="' . esc_attr( $asintype ) . '" shopid="' . esc_attr( $shop->id ) . '"  asin="' . esc_attr( $asin ) . '" asinkey="' . esc_attr( $asin2 ) . '"  ean="' . esc_attr( isset( $product['ean'] ) ? $product['ean'] : '' ) . '"   articlenumber="' . esc_attr( isset( $product['articlenumber'] ) ? $product['articlenumber'] : '' ) . '" /> </td>
														<td style="text-align:center">
																' . ( isset( $product['imageurl'] ) ? '<img src="' . $product['imageurl'] . '" style="max-width: 100px;" />' : '' ) . '
														</td>

														<td>
																<span>' . $product['title'] . '</span>
																<br/>
																' . __( 'Unique ID', ATKP_PLUGIN_PREFIX ) . ': ' . $asin . ', EAN: ' . ( isset( $product['ean'] ) ? $product['ean'] : '-' ) . ', ' . __( 'Articlenumber', ATKP_PLUGIN_PREFIX ) . ': ' . ( isset( $product['articlenumber'] ) ? $product['articlenumber'] : '-' ) . ' 
																' . ( isset( $product['saleprice'] ) ? ', ' . sprintf( __( 'Price: %s', ATKP_PLUGIN_PREFIX ), $product['saleprice'] ) : '' ) . '
																<br/>
																<a href="' . $product['producturl'] . '"
																   target="_blank">' . __( 'View product', ATKP_PLUGIN_PREFIX ) . '</a>
														</td>

														<td>
																' . ( $this->get_product_status( $searchoption, $product, $asin2 ) ) . '
														</td>

												</tr>';
					}
				}

			} catch ( Exception $e ) {

				ATKPLog::LogError( $e->getMessage() );

				return $e->getMessage();
			}

			$resultVal .= '</tbody></table>';

		}

		return $resultVal;
	}

	private function get_product_status( $searchoption, $product, $asin ) {

		if ( $asin != '' ) {
			$args     = array(
				'meta_key'       => ATKP_PRODUCT_POSTTYPE . '_asin',
				'meta_value'     => $asin,
				'post_type'      => ATKP_PRODUCT_POSTTYPE,
				'post_status'    => array( 'publish', 'draft' ),
				'posts_per_page' => - 1
			);
			$subposts = get_posts( $args );
		}

		$result = '<div id="atkp-status-' . $product['asin'] . '">';

		if ( isset( $subposts ) && count( $subposts ) > 0 ) {
			$myprd = $subposts[0];

			$result .= '<img style="vertical-align:middle" src="' . plugins_url( 'images/yes.png', ATKP_PLUGIN_FILE ) . '" alt="' . __( 'Imported', ATKP_PLUGIN_PREFIX ) . '"/>';
			$result .= '<a style="margin-left:5px" href="' . get_edit_post_link( $myprd->ID ) . '" target="_blank">' . __( 'Product imported.', ATKP_PLUGIN_PREFIX ) . '</a><br />';

		}

		$result .= '</div>';

		return $result;
	}

	function localSearch( $isfrontend = false ) {
		$nounce = ATKPTools::get_post_parameter( 'request_nonce', 'string');

		//if ( !wp_verify_nonce( $nounce, 'atkp-search-nonce' ) )
		//   throw new Exception('Nonce invalid');

		$type    = ATKPTools::get_post_parameter( 'type', 'string' );
		$keyword = ATKPTools::get_post_parameter( 'keyword', 'string' );

		if ( ! $isfrontend ) {
			if ( $type == '' ) {
				throw new Exception( 'type is required' );
			}
		} else {
			if ( $type != ATKP_PRODUCT_POSTTYPE ) {
				throw new Exception( 'type not supported' );
			}
		}

		$products = array();
		$args     = array(
			'post_type'        => array( $type ),
			'suppress_filters' => true,
			's'                => $keyword,
			'post_status'      => $isfrontend ? array( 'publish' ) : array( 'draft', 'publish' ),
			'paged'            => 1,
			'posts_per_page'   => 50,

			'orderby' => 'relevance',
			'order'   => 'ASC',
		);

		$the_query = new WP_Query( $args );

		while ( $the_query->have_posts() ) {
			try {
				$the_query->the_post();

				$prd = $the_query->post;



				$product = array();
				//info: je nach anbieter wird entweder small oder large zurückgeliefert?!

				$product['title'] = get_the_title();
				$product['id']    = $post_id = $prd->ID;
				if ( ! $isfrontend ) {
					$product['editurl'] = get_edit_post_link( $prd->ID );
				}

				switch ( $type ) {
					case ATKP_PRODUCT_POSTTYPE:
						$productcoll = atkp_product_collection::load( $prd->ID );
						if ( $product['title'] == '' ) {
							$product['title'] = $productcoll->get_main_product()->title;
						}

						$imageurl = $productcoll->get_main_product()->smallimageurl;
						if ( $imageurl == '' ) {
							$imageurl = $productcoll->get_main_product()->mediumimageurl;
						}
						if ( $imageurl == '' ) {
							$imageurl = $productcoll->get_main_product()->largeimageurl;
						}

						$product['imageurl'] = $imageurl;

						$selectedshopid = $productcoll->get_main_product()->shopid;

						if ( $productcoll->get_main_product()->shop != null ) {
							$shps = $productcoll->get_main_product()->shop;
						} else if ( $selectedshopid != '' ) {
							$shps = atkp_shop::load( $selectedshopid );
						}

						if ( ! isset( $shps ) || $shps == null ) {
							$product['shop'] = __( 'No shop', ATKP_PLUGIN_PREFIX );
						} else {
							$product['shop'] = __( 'Shop', ATKP_PLUGIN_PREFIX ) . ': ' . $shps->title;
						}
						break;
					case ATKP_LIST_POSTTYPE:

						$selectedshopid = ATKPTools::get_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_shopid' );

						if ( $selectedshopid != '' ) {
							$shps = atkp_shop::load( $selectedshopid );
						}

						if ( ! isset( $shps ) || $shps == null ) {
							$product['shop'] = __( 'No shop', ATKP_PLUGIN_PREFIX );
						} else {
							$product['shop'] = __( 'Shop', ATKP_PLUGIN_PREFIX ) . ': ' . $shps->title;
						}
						break;
					default:
						throw new exception( 'unknown type: ' . $type );
				}

				array_push( $products, $product );
			} catch ( Exception $e ) {


			}
		}

		wp_reset_postdata();

		return $products;
	}

	function quickSearch( $searchType ) {

		$nounce = ATKPTools::get_post_parameter( 'request_nonce', 'string' );

		if ( ! wp_verify_nonce( $nounce, 'atkp-search-nonce' ) ) {
			throw new Exception( 'Nonce expired. Please reload page.' );
		}

		$shopid = ATKPTools::get_post_parameter( 'shop', 'string' );

		if ( $shopid == '' ) {
			throw new Exception( 'shop required' );
		}

		$shop_ids = explode( ',', $shopid );

		$result_values = array();

		foreach ( $shop_ids as $shop_id ) {

			$shop = atkp_shop::load( $shop_id );

			if ( $shop == null || $shop->provider == null ) {
				continue;
			}

			$shop->provider->checklogon( $shop );

			switch ( $searchType ) {
				case 'department':
					$depar = $shop->provider->retrieve_departments();

					$departments = array();
					foreach ( $depar as $key => $department ) {
						$department['caption'] = $department['caption'] . ' (' . $key . ')';

						$departments[ $key ] = $department;
					}

					$result_values[ $shop_id ] = $departments;

					break;
				case 'filter':
					$result_values[ $shop_id ] = $shop->provider->retrieve_filters();
					break;
				case 'browsenode':
					$keyword = ATKPTools::get_post_parameter( 'keyword', 'string' );
					if ( $keyword == '' ) {
						throw new Exception( 'keyword required' );
					}


					$result_values[ $shop_id ] = $shop->provider->retrieve_browsenodes( $keyword );
					break;
				default:
					$keyword = ATKPTools::get_post_parameter( 'keyword', 'string' );
					if ( $keyword == '' ) {
						throw new Exception( 'keyword required' );
					}


					$products = $shop->provider->quick_search( $keyword, $searchType );

					$newproducts = array();

					if ( isset( $products ) && $products != null && is_array( $products->products ) ) {
						foreach ( $products->products as $product ) {
							if ( isset( $product['asin'] ) ) {
								$id = atkp_product::idbyasin( $product['asin'] );

								if ( $id == null ) {
									$product['productid'] = 'null';
								} else {
									$product['productid'] = $id;
								}
							} else {
								$product['productid'] = 'null';
							}

							$ss = isset( $product['shopid'] ) && $product['shopid'] != '' ? atkp_shop::load_shopid( $shop, $product['shopid'] ) : null;

							if ( $ss != null ) {
								$product['shoptitle'] = $ss->title;
							} else {
								$product['shoptitle'] = '';
							}

							$newproducts[] = $product;
						}
					}

					$result_values[ $shop_id ] = $newproducts;
					break;
			}
		}

		if ( count( $result_values ) == 0 )
			return $result_values;
		else if ( count( $shop_ids ) > 1 || isset( $_POST['groups'] ) ) {
			return $result_values;
		} else
			return reset( $result_values);
	}

}

