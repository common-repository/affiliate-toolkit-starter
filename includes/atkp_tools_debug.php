<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class atkp_tools_debug {
	/**
	 * Construct the plugin object
	 */
	public function __construct( $pluginbase ) {
		add_action( 'atkp_debug_action', array( $this, 'add_recreatelisttable' ) );

		add_action( 'atkp_debug_action', array( $this, 'add_recreateproducttable' ) );

		add_action( 'atkp_debug_action', array( $this, 'add_recreatequeuetable' ) );


		add_action( 'atkp_migrate_action', array( $this, 'migrate_products_plus' ) );
		add_action( 'atkp_migrate_action', array( $this, 'migrate_products' ) );
	}

	function migrate_products_plus() {
		global $wpdb;

		if ( isset( $_GET['atkp_action'] ) && $_GET['atkp_action'] == 'migrate_products_plus' ) {
			echo 'migrating products plus... ' . '<br />';

			//TODO: Migration

			$table_name = $wpdb->prefix . 'posts';

			$products = $wpdb->get_results( "SELECT ID FROM $table_name  WHERE (post_type='atkp_product') ", OBJECT );

			echo 'products: ' . count( $products ) . '<br />';

			foreach ( $products as $row ) {
				$post_id = $row->ID;
				echo 'updating ' . esc_html__( $post_id, ATKP_PLUGIN_PREFIX ) . '<br />';

				$isv3 = ATKPTools::get_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_v3_plus' );
				if ( $isv3 ) {
					continue;
				}


				ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_title', null );
				ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_mpn', null );
				ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_productgroup', null );
				ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_manufacturer', null );
				ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_author', null );
				ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_numberofpages', null );
				ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_brand', null );
				ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_releasedate', null );
				ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_description', null );
				ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_features', null );


				ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_v3_plus', true );
			}
			ATKPTools::set_setting( 'atkp_migration_done', 1 );
			echo 'migration plus finished ';

			//header( 'Location: ' . $_SERVER['HTTP_REFERER'] );
			exit;

		} else {

			echo '<span>' . esc_html__( 'If you didnt modified product data (title, brand, manufacturer, make, description, features,..) you should use this upgrade function:', ATKP_PLUGIN_PREFIX ) . '</span><br /><br />';

			echo '<a class="button" onclick="return confirm(\'' . esc_html__( 'I made a backup before I migrate my products - Migrate now!', ATKP_PLUGIN_PREFIX ) . '\')" href="?page=ATKP_affiliate_toolkit-tools&tab=debug_configuration_page&atkp_action=migrate_products_plus">' . esc_html__( 'Migrate my products (including productdata) to V3', ATKP_PLUGIN_PREFIX ) . '</a>'; ?>

            <br/>
            <br/>
			<?php
		}
	}

	function migrate_products() {
		global $wpdb;

		if ( isset( $_GET['atkp_action'] ) && $_GET['atkp_action'] == 'migrate_products' ) {
			echo 'migrating products... ' . '<br />';

			//TODO: Migration

			$table_name = $wpdb->prefix . 'posts';

			$products = $wpdb->get_results( "SELECT ID FROM $table_name  WHERE (post_type='atkp_product') ", OBJECT );

			echo 'products: ' . count( $products ) . '<br />';

			foreach ( $products as $row ) {
				$post_id = $row->ID;
				echo 'updating ' . esc_html__($post_id) . '<br />';

				$isv3 = ATKPTools::get_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_v3' );
				if ( $isv3 ) {
					continue;
				}

				$refreshreviewinfo = ATKPTools::get_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_refreshreviewinforegulary' );
				$refreshpriceinfo  = ATKPTools::get_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_refreshpriceinforegulary' );
				$refreshproducturl = ATKPTools::get_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_refreshproducturlregulary' );
				$refreshimages     = ATKPTools::get_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_refreshimagesregulary' );
				//$refreshmoreoffers = ATKPTools::get_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_refreshmoreoffersregulary' );

				if ( $refreshimages == true ) {
					ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_smallimageurl', null );
					ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_mediumimageurl', null );
					ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_largeimageurl', null );
					ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_images', null );
				}

				if ( $refreshproducturl == true ) {
					ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_producturl', null );
					ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_addtocarturl', null );
					ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_customerreviewsurl', null );
				}


				if ( $refreshreviewinfo == true ) {
					//ratings werden nur überschrieben wenn welche vorhanden sind
					ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_rating', null );
					ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_reviewcount', null );
				}

				ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_parentasin', null );
				ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_variationname', null );
				ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_variations', null );


				if ( $refreshpriceinfo == true ) {
					ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_listpricefloat', null );
					ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_salepricefloat', null );
					ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_shippingfloat', null );

					ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_listprice', null );
					ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_saleprice', null );
					ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_availability', null );
					ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_shipping', null );
					ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_isprime', null );
					ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_iswarehouse', null );

					ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_amountsaved', null );
					ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_amountsavedfloat', null );
					ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_percentagesaved', null );
				}

				ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_v3', true );
			}
			ATKPTools::set_setting( 'atkp_migration_done', 1 );
			echo 'migration finished ';

			//header( 'Location: ' . $_SERVER['HTTP_REFERER'] );
			exit;

		} else {

			echo '<span>' . esc_html__( 'If you modified product data (title, brand, manufacturer, make, description, features,..) you should use this upgrade function:', ATKP_PLUGIN_PREFIX ) . '</span><br /><br />';

			echo '<a class="button" onclick="return confirm(\'' . esc_html__( 'I made a backup before I migrate my products - Migrate now!', ATKP_PLUGIN_PREFIX ) . '\')" href="?page=ATKP_affiliate_toolkit-tools&tab=debug_configuration_page&atkp_action=migrate_products">' . esc_html__( 'Migrate my products to V3', ATKP_PLUGIN_PREFIX ) . '</a>'; ?>

            <br/>
            <br/>
			<?php
		}
	}


	function add_recreatequeuetable() {
		global $wpdb;
		$tbl        = new atkp_queuetable_helper();
		$tablename  = $tbl->exists_table();
		$tablename2 = $tbl->exists_detailtable();

		if ( isset( $_GET['atkp_action'] ) && $_GET['atkp_action'] == 'recreate_queuetable' ) {
			echo esc_html__( 'generating table structure for ' . $tablename[1], ATKP_PLUGIN_PREFIX );

			//drop table
			if ( $tablename[0] ) {
				$wpdb->query( 'DROP TABLE ' . $tablename[1] );
			}

			if ( $tablename2[0] ) {
				$wpdb->query( 'DROP TABLE ' . $tablename2[1] );
			}

			//create table
			$tbl->check_table_structure( true );

			header( 'Location: ' . $_SERVER['HTTP_REFERER'] );
			exit;

		} else {


			echo( $tablename[0] ? '<span style="">' . sprintf( esc_html__( 'SQL table "%s" exists', ATKP_PLUGIN_PREFIX ), esc_html($tablename[1]) ) . '</span>' : '<span style="">' . sprintf( esc_html__( 'SQL table "%s" does not exist', ATKP_PLUGIN_PREFIX ), esc_html($tablename[1]) ) . '</span>' ) ?>
            <br/>
			<?php
			echo( $tablename2[0] ? '<span style="">' . sprintf( esc_html__( 'SQL table "%s" exists', ATKP_PLUGIN_PREFIX ), esc_html($tablename2[1]) ) . '</span>' : '<span style="">' . sprintf( esc_html__( 'SQL table "%s" does not exist', ATKP_PLUGIN_PREFIX ), esc_html($tablename2[1]) ) . '</span>' ) ?>
            <br/>


			<?php echo '<a class="button" onclick="return confirm(\'' . esc_html__( 'Are you sure (all queue table entries will be deleted)?', ATKP_PLUGIN_PREFIX ) . '\')" href="?page=ATKP_affiliate_toolkit-tools&tab=debug_configuration_page&atkp_action=recreate_queuetable">' . esc_html__( 'Drop & create queue table (data will be deleted)', ATKP_PLUGIN_PREFIX ) . '</a>'; ?>

            <br/>
            <br/>
			<?php
		}
	}


	function add_recreateproducttable() {
		global $wpdb;

		$tbl       = new atkp_producttable_helper();
		$tablename = $tbl->exists_table();

		if ( isset( $_GET['atkp_action'] ) && $_GET['atkp_action'] == 'recreate_producttable' ) {
			echo esc_html__( 'generating table structure for ' . $tablename[1], ATKP_PLUGIN_PREFIX );

			//drop table
			if ( $tablename[0] ) {
				$wpdb->query( 'DROP TABLE ' . $tablename[1] );
			}

			//create table
			$tbl->check_table_structure( true );

			header( 'Location: ' . $_SERVER['HTTP_REFERER'] );
			exit;

		} else {


			echo( $tablename[0] ? '<span style="">' . sprintf( esc_html__( 'SQL table "%s" exists', ATKP_PLUGIN_PREFIX ), esc_html($tablename[1]) ) . '</span>' : '<span style="">' . sprintf( esc_html__( 'SQL table "%s" does not exist', ATKP_PLUGIN_PREFIX ), esc_html($tablename[1]) ) . '</span>' ) ?>
            <br/>

			<?php echo '<a class="button" onclick="return confirm(\'' . esc_html__( 'Are you sure (all product table entries will be deleted)?', ATKP_PLUGIN_PREFIX ) . '\')" href="?page=ATKP_affiliate_toolkit-tools&tab=debug_configuration_page&atkp_action=recreate_producttable">' . esc_html__( 'Drop & create list table (data will be deleted)', ATKP_PLUGIN_PREFIX ) . '</a>'; ?>

            <br/>
            <br/>
			<?php
		}
	}


	function add_recreatelisttable() {
		global $wpdb;

		$tbl       = new atkp_listtable_helper();
		$tablename = $tbl->exists_table();

		if ( isset( $_GET['atkp_action'] ) && $_GET['atkp_action'] == 'recreate_listtable' ) {
			echo esc_html__( 'generating table structure for ' . $tablename[1], ATKP_PLUGIN_PREFIX );

			//drop table
			if ( $tablename[0] ) {
				$wpdb->query( 'DROP TABLE ' . $tablename[1] );
			}

			//create table
			$tbl->check_table_structure( true );

			header( 'Location: ' . $_SERVER['HTTP_REFERER'] );
			exit;

		} else {

			$tbl       = new atkp_listtable_helper();
			$tablename = $tbl->exists_table();
			echo( $tablename[0] ? '<span style="">' . sprintf( esc_html__( 'SQL table "%s" exists', ATKP_PLUGIN_PREFIX ), esc_html($tablename[1]) ) . '</span>' : '<span style="">' . sprintf( esc_html__( 'SQL table "%s" does not exist', ATKP_PLUGIN_PREFIX ), esc_html($tablename[1]) ) . '</span>' ) ?>
            <br/>

			<?php echo '<a class="button" onclick="return confirm(\'' . esc_html__( 'Are you sure (all list table entries will be deleted)?', ATKP_PLUGIN_PREFIX ) . '\')" href="?page=ATKP_affiliate_toolkit-tools&tab=debug_configuration_page&atkp_action=recreate_listtable">' . esc_html__( 'Drop & create list table (data will be deleted)', ATKP_PLUGIN_PREFIX ) . '</a>'; ?>

            <br/>
            <br/>
			<?php
		}
	}

	public function debug_configuration_page() {
		$imported = false;

		if ( ATKPTools::exists_post_parameter( 'savedebug' ) && check_admin_referer( 'save', 'save' ) ) {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'You do not have sufficient permissions to access this page', ATKP_PLUGIN_PREFIX ) );
			}

			update_option( ATKP_PLUGIN_PREFIX . '_loglevel', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_loglevel', 'string' ) );

		}

		$nounce = wp_create_nonce( 'atkp-download-log' );

		?>
        <div class="atkp-content wrap">
            <div class="inner">
                <!-- <h2><?php echo esc_html__( 'Affiliate Toolkit - Woo', ATKP_PLUGIN_PREFIX ) ?></h2>      -->

                <form method="POST"
                      action="?page=<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_affiliate_toolkit-tools&tab=debug_configuration_page') ?>">
                    <!--_affiliate_toolkit-bestseller-->
					<?php wp_nonce_field( "save", "save" ); ?>
                    <table class="form-table" style="width:100%">
                        <tr>
                            <th scope="row" style="background-color:#bde4ea; padding:7px" colspan="2">
	                            <?php echo esc_html__( 'Configuration', ATKP_PLUGIN_PREFIX ) ?>
                            </th>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="">
	                                <?php echo esc_html__( 'Log Level', ATKP_PLUGIN_PREFIX ) ?>:
                                </label>


                            </th>
                            <td>
                                <select id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_loglevel') ?>"
                                        name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_loglevel') ?>" style="width:300px">
									<?php
									$selected = get_option( ATKP_PLUGIN_PREFIX . '_loglevel' );

									echo '<option value="off" ' . ( $selected == '' || $selected == 'off' ? 'selected' : '' ) . ' >' . esc_html__( 'OFF', ATKP_PLUGIN_PREFIX ) . '</option>';

									echo '<option value="debug" ' . ( $selected == 'debug' ? 'selected' : '' ) . '>' . esc_html__( 'DEBUG', ATKP_PLUGIN_PREFIX ) . '</option>';

									echo '<option value="error" ' . ( $selected == 'error' ? 'selected' : '' ) . '>' . esc_html__( 'ERROR', ATKP_PLUGIN_PREFIX ) . '</option>';


									?>
                                </select> <br/>

	                            <?php echo '<a ' . ( ( file_exists( ATKP_LOGFILE ) ) ? '' : 'disabled' ) . ' class="button" href="' . esc_url( ATKPTools::get_endpointurl() . '?action=atkp_download_logfile&request_nonce=' . $nounce ) . '">' . esc_html__( 'Download Logfile', ATKP_PLUGIN_PREFIX ) . '</a>'; ?>
                                &nbsp;
	                            <?php echo '<a ' . ( ( file_exists( ATKP_LOGFILE ) ) ? '' : 'disabled' ) . ' class="button" href="' . esc_url( ATKPTools::get_endpointurl() . '?action=atkp_clear_logfile&request_nonce=' . $nounce ) . '">' . esc_html__( 'Clear Logfile', ATKP_PLUGIN_PREFIX ) . '</a>'; ?>

                            </td>
                        </tr>


                        <tr>

                            <td>
								<?php submit_button( '', 'primary', 'savedebug', false ); ?>             </td>
                        </tr>

                        <tr>
                            <th scope="row" style="background-color:#bde4ea; padding:7px" colspan="2">
	                            <?php echo esc_html__( 'Migration from v2 to v3', ATKP_PLUGIN_PREFIX ) ?>
                            </th>
                        </tr>

                        <tr>

                            <td colspan="2">

								<?php
								do_action( 'atkp_migrate_action' );
								?>


                            </td>
                        </tr>

                        <tr>
                            <th scope="row" style="background-color:#bde4ea; padding:7px" colspan="2">
	                            <?php echo esc_html__( 'Status', ATKP_PLUGIN_PREFIX ) ?>
                            </th>
                        </tr>

                        <tr>

                            <td colspan="2">


								<?php
								do_action( 'atkp_debug_status_action' );


								echo '<a class="button" href="' . esc_url( ATKPTools::get_endpointurl() . '?action=atkp_reset_products&request_nonce=' . esc_html( $nounce ) ) . '">' . esc_html__( 'Mark all products for update', ATKP_PLUGIN_PREFIX ) . '</a>'; ?>
                                <br/><br/>
	                            <?php echo '<a class="button" href="' . esc_url( ATKPTools::get_endpointurl() . '?action=atkp_reset_lists&request_nonce=' . esc_html( $nounce ) ) . '">' . esc_html__( 'Mark all lists for update', ATKP_PLUGIN_PREFIX ) . '</a>'; ?>
                                <br/><br/>
	                            <?php echo '<a class="button" onclick="return confirm(\'' . esc_html__( 'Are you sure (everything from the plugin will be deleted!)?', ATKP_PLUGIN_PREFIX ) . '\')" href="' . esc_url( ATKPTools::get_endpointurl() . '?action=atkp_reset_settings&request_nonce=' . esc_html( $nounce ) ) . '">' . esc_html__( 'Remove all settings and products (clean install)', ATKP_PLUGIN_PREFIX ) . '</a>'; ?>

                            </td>
                        </tr>

                        <tr>
                            <th scope="row" style="background-color:#bde4ea; padding:7px" colspan="2">
	                            <?php echo esc_html__( 'Tables', ATKP_PLUGIN_PREFIX ) ?>
                            </th>


                        </tr>
                        <tr>
                            <td colspan="2">
			                    <?php
			                    do_action( 'atkp_debug_action' );
			                    ?>
                            </td>
                        </tr>

                        <tr>
                            <td scope="row" colspan="2">
                                WP-Info
                                <textarea readonly
                                          style="width:100%;height:250px"><?php echo esc_textarea( $this->get_wpinfo() ); ?></textarea>
                            </td>
                        </tr>

                        <tr>
                            <td scope="row" colspan="2">
                                PHP-Info
                                <textarea readonly
                                          style="width:100%;height:250px"><?php echo esc_textarea( $this->get_phpinfo() ); ?></textarea>
                            </td>
                        </tr>

                    </table>
                </form>
            </div>

        </div> <?php
	}

	private function get_phpinfo() {
		return print_r( $this->parse_phpinfo(), true );
	}

	function parse_phpinfo() {
		//retrieve php info for current server
		if ( ! function_exists( 'ob_start' ) || ! function_exists( 'phpinfo' ) || ! function_exists( 'ob_get_contents' ) || ! function_exists( 'ob_end_clean' ) || ! function_exists( 'preg_replace' ) ) {
			return 'This information is not available.';
		} else {
			ob_start();
			phpinfo();
			$s = ob_get_contents();
			ob_end_clean();

			//$s = preg_replace( '%^.*<body>(.*)</body>.*$%ms','$1',$pinfo);

		}

		$s     = strip_tags( $s, '<h2><th><td>' );
		$s     = preg_replace( '/<th[^>]*>([^<]+)<\/th>/', '<info>\1</info>', $s );
		$s     = preg_replace( '/<td[^>]*>([^<]+)<\/td>/', '<info>\1</info>', $s );
		$t     = preg_split( '/(<h2[^>]*>[^<]+<\/h2>)/', $s, - 1, PREG_SPLIT_DELIM_CAPTURE );
		$r     = array();
		$count = count( $t );
		$p1    = '<info>([^<]+)<\/info>';
		$p2    = '/' . $p1 . '\s*' . $p1 . '\s*' . $p1 . '/';
		$p3    = '/' . $p1 . '\s*' . $p1 . '/';
		for ( $i = 1; $i < $count; $i ++ ) {
			if ( preg_match( '/<h2[^>]*>([^<]+)<\/h2>/', $t[ $i ], $matchs ) ) {
				$name = trim( $matchs[1] );
				$vals = explode( "\n", $t[ $i + 1 ] );
				foreach ( $vals AS $val ) {
					if ( preg_match( $p2, $val, $matchs ) ) { // 3cols
						$r[ $name ][ trim( $matchs[1] ) ] = array( trim( $matchs[2] ), trim( $matchs[3] ) );
					} elseif ( preg_match( $p3, $val, $matchs ) ) { // 2cols
						$r[ $name ][ trim( $matchs[1] ) ] = trim( $matchs[2] );
					}
				}
			}
		}

		return $r;
	}

	public function isMinimumVersion( $version ) {
		return version_compare( get_bloginfo( 'version' ), $version ) >= 0;
	}

	private function getThemeData() {
		$themeData = null;

		if ( $this->isMinimumVersion( '3.4' ) ) {
			$themeData = wp_get_theme();
		} else {
			$themeData = get_theme_data( get_stylesheet() );
		}

		return $themeData;
	}

	private function getPlugins() {
		$array = get_plugins();

		return $array;
	}

	private function get_wpinfo() {

		$context = array(
			'plugin_name'      => esc_html__( 'Affiliate Toolkit', ATKP_PLUGIN_PREFIX ),
			'plugin_version'   => ATKPSettings::plugin_get_version(),
			'OS'               => PHP_OS,
			'uname'            => php_uname(),
			'wp_version'       => get_bloginfo( 'version' ),
			'wp_charset'       => get_bloginfo( 'charset' ),
			'wp_count_users'   => count_users()['total_users'],
			'wp_debug'         => WP_DEBUG == true ? 'true' : 'false',
			'wp_debug_log'     => WP_DEBUG_LOG == true ? 'true' : 'false',
			'wp_debug_display' => WP_DEBUG_DISPLAY == true ? 'true' : 'false',
			'plugins'          => $this->getPlugins(),
			'theme'            => $this->getThemeData(),
			'php_version'      => phpversion(),
			'php_memory_limit' => ini_get( 'memory_limit' ),
			'php_include_path' => get_include_path(),
			'php_open_basedir' => ini_get( 'open_basedir' ),
			'php_ipv6'         => defined( 'AF_INET6' ) ? "PHP was compiled without --disable-ipv6 option" : "PHP was compiled with --disable-ipv6 option",
			'mysql_version'    => ! empty( $mysql_server_info ) ? $mysql_server_info : '',
			'mysql_client'     => ! empty( $mysql_client_info ) ? $mysql_client_info : '',
			'server_software'  => $_SERVER['SERVER_SOFTWARE'],
		);


		if ( function_exists( 'mysql_get_server_info' ) ) {
			$mysql_server_info = @mysql_get_server_info();
		} else {
			$mysql_server_info = '';
		}

		if ( function_exists( 'mysql_get_client_info' ) ) {
			$mysql_client_info = @mysql_get_client_info();
		} else {
			$mysql_client_info = '';
		}

		$context['mysql_version']   = ! empty( $mysql_server_info ) ? $mysql_server_info : '';
		$context['mysql_client']    = ! empty( $mysql_client_info ) ? $mysql_client_info : '';
		$context['server_software'] = $_SERVER['SERVER_SOFTWARE'];

		if ( function_exists( 'apache_get_version' ) ) {
			$context['apache_version'] = apache_get_version();
		}
		if ( function_exists( 'apache_get_modules' ) ) {
			$context['apache_modules'] = apache_get_modules();
		}

		return print_r( $context, true );
	}

	private function get_logfile() {
		if ( file_exists( ATKP_PLUGIN_DIR . '/log/log.txt' ) ) {
			return file_get_contents( ATKP_PLUGIN_DIR . '/log/log.txt' );
		} else {
			return 'file not found';
		}
	}

}

?>