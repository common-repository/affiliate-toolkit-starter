<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class atkp_posttypes_product {

	private $taxonomies = array();

	/**
	 * Construct the plugin object
	 *
	 * @param $pluginbase
	 */
	public function __construct( $pluginbase ) {

		$this->atkp_posttypes_product_init();

		add_action( 'admin_menu', array( $this, 'atkp_product_adminmenus' ), 10 );

		add_action( 'admin_footer', array( $this, 'atkp_product_footer_function' ) );


		//add_action('parent_file', array($this, 'keep_taxonomy_menu_open'));

	}

	function atkp_product_footer_function() {
		global $pagenow;
		$cpt = isset( $_GET['post_type'] ) ? $_GET['post_type'] : '';

		if ( ( $pagenow == 'edit.php' ) && ( $cpt == ATKP_PRODUCT_POSTTYPE ) ) {

			?>

            <script>
                jQuery(document).ready(function () {

                    jQuery(jQuery(".wrap .page-title-action")[0]).after('<a href="<?php echo esc_url( admin_url( 'admin.php?page=atkp_bulkimport' ) ) ?>" class="page-title-action"><?php echo esc_html__( 'Import product', ATKP_PLUGIN_PREFIX ) ?></a>');
                });
            </script>

			<?php

		}


	}

	function atkp_product_adminmenus() {

		$taxonomies = atkp_udtaxonomy::load_taxonomies();

		if ( $taxonomies != null ) {
			foreach ( $taxonomies as $taxonomy ) {

				if ( $taxonomy->caption == '' ) {
					$taxonomy->caption = $taxonomy->name;
				}

				if ( $taxonomy->captionplural == '' ) {
					$taxonomy->captionplural = $taxonomy->caption;
				}

				if ( $taxonomy->showui && $taxonomy->issystemfield ) {
					add_submenu_page(
						ATKP_PLUGIN_PREFIX . '_affiliate_toolkit-plugin',
						$taxonomy->captionplural,
						$taxonomy->captionplural,
						'edit_posts',
						'edit-tags.php?taxonomy=' . $taxonomy->name,
						false
					);
				}
			}
		}

		add_filter( 'parent_file', function ( $file ) {
			$screen = get_current_screen();

			if ( ! isset( $screen->taxonomy ) || ! isset( $screen->base ) ) {
				return $file;
			}

			$set_file = false;
			if ( 'edit-tags' === $screen->base ) {
				$taxonomies = atkp_udtaxonomy::load_taxonomies();

				if ( $taxonomies != null ) {
					foreach ( $taxonomies as $taxonomy ) {
						if ( $taxonomy->showui && $taxonomy->issystemfield ) {
							if ( $taxonomy->name === $screen->taxonomy ) {
								$set_file = true;
							}
						}
					}
				}
			}
			// in my case I drilled down to if($screen->id...); I used what you posted in your if clause above
			if ( $set_file ) {
				$file = ATKP_PLUGIN_PREFIX . '_affiliate_toolkit-plugin';
			} //probably need to set this as a string; for the parent slug represented by $this->plugin_slug ;

			return $file;
		} );
	}


	function atkp_posttypes_product_init() {
		$this->register_productPostType();
		$this->register_filter();


		if ( is_admin() ) {

			add_action( 'add_meta_boxes', array( &$this, 'product_boxes' ) );
			add_action( 'save_post', array( &$this, 'product_detail_save' ) );

			add_thickbox();

			ATKPTools::add_column( ATKP_PRODUCT_POSTTYPE, esc_html__( 'Status', ATKP_PLUGIN_PREFIX ), function ( $post_id ) {

				$products = atkp_product_collection::load( $post_id );

				$prd = $products->get_main_product();

				$selectedshopid = $prd->shopid;

				if ( $prd->shop == null ) {
					try {
						if ( $selectedshopid != '' && atkp_shop::exists( $selectedshopid ) ) {
							$shps = atkp_shop::load( $selectedshopid );
						}
					} catch ( Exception $ex ) {
						echo '<span style="color:red">parent shop not found.</span> ';
						$shps = null;
					}
				} else {
					$shps = $prd->shop;
				}

				echo '<span style="font-weight:bold">' . esc_html__( 'ID', ATKP_PLUGIN_PREFIX ) . ':</span> <span >' . esc_html__( $post_id, ATKP_PLUGIN_PREFIX ) . '</span>, ';

				$shop_config = $prd->get_shops_from_config();

				if ( ! isset( $shps ) || $shps == null ) {
					echo '<span>' . esc_html__( 'No shop', ATKP_PLUGIN_PREFIX ) . '</span>';
				} else {
					$logourls = array();
					foreach ( $products->products as $product ) {

						$shop_config = array_diff( $shop_config, array( $product->shopid ) );

						if ( $product->shop != null ) {
							if ( $product->shop->get_smalllogourl() != '' ) {
								$logourls[] = '<a title="' . esc_attr__( $product->shop->get_title(), ATKP_PLUGIN_PREFIX ) . '" target="_blank" href="' . esc_url( $product->producturl ) . '"><img alt="' . esc_attr__( $product->shop->get_title(), ATKP_PLUGIN_PREFIX ) . '" style="' . ( $product->ismainshop ? 'border:green solid 1px' : '' ) . ';max-height:17px" src="' . ( esc_url( $product->shop->get_smalllogourl() ) ) . '" /></a>';
							} else {
								$logourls[] = '<a title="' . esc_attr__( $product->shop->get_title(), ATKP_PLUGIN_PREFIX ) . '" target="_blank" href="' . esc_url( $product->producturl ) . '"><span style="' . ( $product->ismainshop ? 'border:green solid 1px' : '' ) . '">' . ( esc_attr__( $product->shop->get_title(), ATKP_PLUGIN_PREFIX ) ) . '</span></a>';
							}

							if ( count( $logourls ) > 5 ) {
								break;
							}
						}
					}
					echo '<span style="filter: grayscale(60%);font-weight:bold">' . esc_html__( 'Shops', ATKP_PLUGIN_PREFIX ) . ':</span> <span>' . 
                        wp_kses( implode( ',', $logourls ), array(
                            'a' => array( 'title' => array(), 'target' => array(), 'href' => array() ),
                            'img' => array( 'alt' => array(), 'src' => array(), 'style' => array() )
                        ) ) . '</span>';

				}

				if ( count( $shop_config ) > 0 ) {
					$logourls = array();
					foreach ( $shop_config as $shop_co ) {
						$myshop = atkp_shop::load( ( $shop_co ));

						if ( $myshop == null ) {
							continue;
						}

						if ( $myshop->get_smalllogourl() != '' ) {
							$logourls[] = '<img title="' . esc_attr_e( 'Product not found', ATKP_PLUGIN_PREFIX ) . '" alt="' . esc_attr_e( $myshop->get_title(), ATKP_PLUGIN_PREFIX ) . '" style="filter: grayscale(60%);opacity:0.3;max-height:17px" src="' . ( esc_url( $myshop->get_smalllogourl() ) ) . '" />';
						} else {
							$logourls[] = '<span title="' . esc_attr_e( 'Product not found', ATKP_PLUGIN_PREFIX ) . '" style="opacity:0.3">' . (esc_html__( $myshop->get_title(), ATKP_PLUGIN_PREFIX ) ) . '</span>';
						}
					}
					echo '<br /><span style="filter: grayscale(60%);font-weight:bold">' . esc_html__( 'No result', ATKP_PLUGIN_PREFIX ) . ':</span> <span>' . 
                        wp_kses( implode( ',', $logourls ), array(
                            'a' => array( 'title' => array(), 'target' => array(), 'href' => array() ),
                            'img' => array( 'alt' => array(), 'src' => array(), 'style' => array() )
                        ) ) . '</span>';
				}


				$updatedon = ATKPTools::get_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_updatedon' );

				if ( isset( $updatedon ) && $updatedon != '' ) {
					$infotext = esc_html__( '%refresh_date% at %refresh_time%', ATKP_PLUGIN_PREFIX );

					$infotext = str_replace( '%refresh_date%', ATKPTools::get_formatted_date( $updatedon ), $infotext );
					$infotext = str_replace( '%refresh_time%', ATKPTools::get_formatted_time( $updatedon ), $infotext );

					echo '<br /><span style="font-weight:bold">' . esc_html__( 'Updated on', ATKP_PLUGIN_PREFIX ) . ':</span> <span>' . esc_html__( $infotext, ATKP_PLUGIN_PREFIX ) . '</span>';
				}

				$message = ATKPTools::get_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_message' );

				if ( isset( $message ) && $message != '' ) {
					echo '<br /><span style="font-weight:bold">' . esc_html__( 'Message', ATKP_PLUGIN_PREFIX ) . ':</span> <span style="color:red">' . esc_html( $message ) . '</span>';
				}

				do_action( 'atkp_product_status_column', $post_id );


			}, 3 );

			add_action( 'admin_enqueue_scripts', array( $this, 'image_enqueue' ) );
			add_action( 'admin_head', array( $this, 'hidey_admin_head' ) );


			ATKPTools::add_column( ATKP_PRODUCT_POSTTYPE, esc_html__( 'Main image', ATKP_PLUGIN_PREFIX ), function ( $post_id ) {
				$prodcollection = atkp_product_collection::load( $post_id );
				$product        = $prodcollection->get_main_product();

				$imageurl = $product->smallimageurl;
				if ( $imageurl == '' ) {
					$imageurl = $product->mediumimageurl;
				}
				if ( $imageurl == '' ) {
					$imageurl = $product->largeimageurl;
				}

				if ( $imageurl != '' ) {
					echo '<img src="' . esc_url($imageurl) . '" style="max-width:60px" />';
				}
			}, 1 );

			add_action( 'before_delete_post', 'atkp_delete_images' );
			function atkp_delete_images( $post_id ) {

				// We check if the global post type isn't ours and just return
				global $post_type;
				if ( $post_type == ATKP_PRODUCT_POSTTYPE ) {

					if ( has_post_thumbnail( $post_id ) ) {
						$attachment_id = get_post_thumbnail_id( $post_id );
						if ( $attachment_id ) {
							wp_delete_attachment( $attachment_id, true );
						}
					}

					do_action( 'atkp_product_delete_images', $post_id );

				}
			}
		}
	}


	function hidey_admin_head() {
		echo '<style type="text/css">';
		echo '.column-' . esc_html__( sanitize_title( esc_html__( 'Main image', ATKP_PLUGIN_PREFIX ) ), ATKP_PLUGIN_PREFIX ) . ' { width: 70px; }';
		echo '</style>';
	}

	/**
	 * Loads the image management javascript
	 */
	function image_enqueue() {
		global $typenow;
		if ( $typenow == ATKP_PRODUCT_POSTTYPE ) {
			wp_enqueue_media();

			// Registers and enqueues the required javascript.
			wp_register_script( 'meta-box-image', plugin_dir_url( ATKP_PLUGIN_FILE ) . 'js/meta-box-image.js', array( 'jquery' ) );
			wp_localize_script( 'meta-box-image', 'meta_image',
				array(
					'title'  => esc_html__( 'Choose or Upload an image', ATKP_PLUGIN_PREFIX ),
					'button' => esc_html__( 'Use this image', ATKP_PLUGIN_PREFIX ),
				)
			);
			wp_enqueue_script( 'meta-box-image' );
		}
	}

	function register_filter() {

		add_filter( 'parse_query', array( &$this, 'admin_posts_filter' ) );
		add_action( 'restrict_manage_posts', array( &$this, 'admin_posts_filter_restrict_manage_posts' ) );

	}

	function admin_posts_filter( $query ) {

		$filterfield = ATKPTools::get_get_parameter( ATKP_PLUGIN_PREFIX . '_filterfield', 'string' );
		$posttype    = ATKPTools::get_get_parameter( 'post_type', 'string' );

		if ( $posttype == ATKP_PRODUCT_POSTTYPE && ! atkp_posttypes_product::$overridefilter ) {
			global $pagenow;
			if ( is_admin() && $pagenow == 'edit.php' && isset( $filterfield ) && $filterfield != '' ) {

				if ( isset( $query->query_vars['post_type'] ) && $query->query_vars['post_type'] == 'atkp_product' ) {
					if ( $filterfield == 'filter_error' ) {
						$query->query_vars['meta_key']     = ATKP_PRODUCT_POSTTYPE . '_message';
						$query->query_vars['meta_value']   = '';
						$query->query_vars['meta_compare'] = 'EXISTS';
					} else {
						$parts   = explode( '_', $filterfield );
						$sfilter = isset( $parts[1] ) ? $parts[1] : '';

						$filds = array();
						for ( $x = 1; $x < ( $sfilter == '' ? 2 : ( ATKP_FILTER_COUNT + 1 ) ); $x ++ ) {
							$filds[] = array(
								'key'     => ATKP_PRODUCT_POSTTYPE . '_shopid' . ( $x > 1 ? '_' . $x : '' ),
								'value'   => $sfilter,
								'compare' => $sfilter != '' ? '=' : 'NOT EXISTS'
							);

						}

						$filds['relation'] = 'OR';

						$query->set( 'meta_query', $filds );
					}

					remove_filter( 'parse_query', 'admin_posts_filter' );
				}
			}

		}


	}

	private static $overridefilter;

	function admin_posts_filter_restrict_manage_posts() {
		$posttype = ATKPTools::get_get_parameter( 'post_type', 'string' );

		if ( $posttype != ATKP_PRODUCT_POSTTYPE ) {
			return;
		}

		atkp_posttypes_product::$overridefilter = true;
		$shops                                  = atkp_shop::get_list();

		atkp_posttypes_product::$overridefilter = false;
		?>
        <select name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_filterfield') ?>">
            <option value=""><?php echo esc_html__( 'Filter products by', ATKP_PLUGIN_PREFIX ); ?></option>
			<?php
			//Alle Listen
			//Fehlerhafte Listen
			//Leere Listen
			//Shop: xx
			$filterfield = ATKPTools::get_get_parameter( ATKP_PLUGIN_PREFIX . '_filterfield', 'string' );

			echo '<option value="' . esc_attr( 'filter_error' ) . '" ' . ( $filterfield == 'filter_error' ? 'selected' : '' ) . '>' . esc_html__( 'Products with error', ATKP_PLUGIN_PREFIX ) . '</option>';
			echo '<option value="' . esc_attr( 'shop_' ) . '" ' . ( $filterfield == 'shop_' ? 'selected' : '' ) . '>' . esc_html__( 'No shop', ATKP_PLUGIN_PREFIX ) . '</option>';

			foreach ( $shops as $shop ) {


				echo '<option ' . ( $shop->type == atkp_shop_type::SUB_SHOPS ? 'disabled' : '' ) . ' value="' . esc_attr( 'shop_' . $shop->id ) . '" ' . ( $filterfield == 'shop_' . $shop->id ? 'selected' : '' ) . '>' . sprintf( esc_html__( 'Shop: %s (%s)', ATKP_PLUGIN_PREFIX ), esc_html($shop->title), esc_html($shop->id) ) . '</option>';

				foreach ( $shop->children as $child ) {

					echo '<option value="' . esc_attr( 'shop_' . $child->id ) . '" ' . ( $filterfield == 'shop_' . $child->id ? 'selected' : '' ) . '>- ' . sprintf( esc_html__( '%s (%s)', ATKP_PLUGIN_PREFIX ), esc_html($child->title), esc_html($child->id) ) . '</option>';

				}
			}
			?>
        </select>
		<?php
	}

	function register_productPostType() {
		$labels = array(
			'name'               => esc_html__( 'Products', ATKP_PLUGIN_PREFIX ),
			'singular_name'      => esc_html__( 'Product', ATKP_PLUGIN_PREFIX ),
			'add_new_item'       => esc_html__( 'Add new Product', ATKP_PLUGIN_PREFIX ),
			'edit_item'          => esc_html__( 'Edit Product', ATKP_PLUGIN_PREFIX ),
			'new_item'           => esc_html__( 'New Product', ATKP_PLUGIN_PREFIX ),
			'all_items'          => esc_html__( 'Products', ATKP_PLUGIN_PREFIX ),
			'view_item'          => esc_html__( 'View Product', ATKP_PLUGIN_PREFIX ),
			'search_items'       => esc_html__( 'Search Products', ATKP_PLUGIN_PREFIX ),
			'not_found'          => esc_html__( 'No products found', ATKP_PLUGIN_PREFIX ),
			'not_found_in_trash' => esc_html__( 'No products found in the Trash', ATKP_PLUGIN_PREFIX ),
			'parent_item_colon'  => '',
			'menu_name'          => esc_html__( 'AT Products', ATKP_PLUGIN_PREFIX ),
		);

		$importProductimage = atkp_options::$loader->get_product_importimage();

		$supports = array( 'title', 'author' );

		if ( $importProductimage ) {
			$supports = array( 'title', 'thumbnail', 'author' );
		}


		$args = array(
			'labels'      => $labels,
			'description' => esc_html__( 'Holds our products and product specific data', ATKP_PLUGIN_PREFIX ),

			'public'              => false,
			// it's not public, it shouldn't have it's own permalink, and so on
			'publicly_queriable'  => false,
			// you should be able to query it
			'show_ui'             => true,
			// you should be able to edit it in wp-admin
			'exclude_from_search' => true,
			// you should exclude it from search results
			'show_in_nav_menus'   => true,
			// you shouldn't be able to add it to menus
			'has_archive'         => false,
			// it shouldn't have archive page
			'rewrite'             => false,
			'taxonomies'          => array(),
			'query_var'           => true,

			'menu_position'   => 20,
			'supports'        => $supports,
			'capability_type' => 'post',
			'menu_icon'       => plugin_dir_url( ATKP_PLUGIN_FILE ) . '/images/affiliate_toolkit_menu.png',
			'show_in_menu'    => ATKP_PLUGIN_PREFIX . '_affiliate_toolkit-plugin',
		);

		$args = apply_filters( 'atkp_product_register_post_type', $args );

		register_post_type( ATKP_PRODUCT_POSTTYPE, $args );


		$taxonomies = atkp_udtaxonomy::load_taxonomies();

		if ( $taxonomies != null ) {
			foreach ( $taxonomies as $taxonomy ) {
				//falls taxonomie bereits von anderem plugin installiert wurde
				if ( taxonomy_exists( $taxonomy->name ) || $taxonomy->name == '' ) {
					continue;
				}

				if ( $taxonomy->caption == '' ) {
					$taxonomy->caption = $taxonomy->name;
				}

				if ( $taxonomy->captionplural == '' ) {
					$taxonomy->captionplural = $taxonomy->caption;
				}

				$this->taxonomies[] = $taxonomy->name;

				$labels = array(
					'name'          => $taxonomy->captionplural,
					'singular_name' => $taxonomy->caption,
					'search_items'  => sprintf( esc_html__( 'Search %s', ATKP_PLUGIN_PREFIX ), esc_html($taxonomy->captionplural) ),
					'all_items'     => sprintf( esc_html__( 'All %s', ATKP_PLUGIN_PREFIX ), esc_html($taxonomy->captionplural) ),
					'edit_item'     => sprintf( esc_html__( 'Edit %s', ATKP_PLUGIN_PREFIX ), esc_html($taxonomy->caption) ),
					'update_item'   => sprintf( esc_html__( 'Update %s', ATKP_PLUGIN_PREFIX ), esc_html($taxonomy->caption) ),
					'add_new_item'  => sprintf( esc_html__( 'Add New %s', ATKP_PLUGIN_PREFIX ), esc_html($taxonomy->caption) ),
					'new_item_name' => sprintf( esc_html__( 'New %s', ATKP_PLUGIN_PREFIX ), esc_html($taxonomy->caption) ),
					'menu_name'     => $taxonomy->captionplural
				);

				// register taxonomy

				$taxs = array( ATKP_PRODUCT_POSTTYPE );

				$taxs = apply_filters( 'atkp_taxonomy_posttypes', $taxs, $taxonomy );

				$taxargs = array(
					'taxonomies'        => $taxs,
					'hierarchical'      => true,
					'labels'            => $labels,
					'show_admin_column' => $taxonomy->isproductgroup,
					'show_ui'           => $taxonomy->showui,
					'public'            => false,
                    'update_count_callback' => '_update_generic_term_count',
					'capabilities'      => array(
						'manage_terms' => 'edit_posts',
						'edit_terms'   => 'edit_posts',
						'delete_terms' => 'edit_posts',
						'assign_terms' => 'edit_posts'
					),
					'show_in_menu'      => false,
					'query_var' => true
				);
				$taxargs = apply_filters( 'atkp_register_taxonomy', $taxargs, $taxonomy->name );

				register_taxonomy( $taxonomy->name, $taxs, $taxargs );
			}
		}
	}


	function keep_taxonomy_menu_open( $parent_file ) {
		global $current_screen;
		$taxonomy_current = $current_screen->taxonomy;

		if ( isset( $taxonomy_current ) && $taxonomy_current ) {
			foreach ( $this->taxonomies as $taxonomy ) {
				if ( $taxonomy->name == $taxonomy_current->name ) {
					$parent_file = ATKP_PLUGIN_PREFIX . '_affiliate_toolkit-plugin';
					break;
				}
			}
		}

		if ( isset( $current_screen->post_type ) && $current_screen->post_type = 'atkp_template' ) {
			$parent_file = ATKP_PLUGIN_PREFIX . '_affiliate_toolkit-plugin';

		}

		return $parent_file;
	}


	function product_boxes() {

		add_meta_box(
			ATKP_PRODUCT_POSTTYPE . '_shop_box',
			esc_html__( 'Filter Information', ATKP_PLUGIN_PREFIX ),
			array( &$this, 'product_shop_box_content' ),
			ATKP_PRODUCT_POSTTYPE,
			'normal',
			'high'
		);

		add_meta_box(
			ATKP_PRODUCT_POSTTYPE . '_products_box',
			esc_html__( 'Products found', ATKP_PLUGIN_PREFIX ),
			array( &$this, 'product_products_box_content' ),
			ATKP_PRODUCT_POSTTYPE,
			'normal',
			'high'
	);


		add_meta_box(
			ATKP_PRODUCT_POSTTYPE . '_detail_box',
			esc_html__( 'Detailed information', ATKP_PLUGIN_PREFIX ),
			array( &$this, 'product_detail_box_content' ),
			ATKP_PRODUCT_POSTTYPE,
			'normal',
			'high'
		);

		add_meta_box(
			ATKP_PRODUCT_POSTTYPE . '_queue_box',
			esc_html__( 'Queue History', ATKP_PLUGIN_PREFIX ),
			array( &$this, 'product_queue_box_content' ),
			ATKP_PRODUCT_POSTTYPE,
			'normal',
			'low'
	);


	}

	function product_queue_box_content( $post ) {
		$atkp_queuetable_helper = new atkp_queuetable_helper();
		if ( ! $atkp_queuetable_helper->exists_table()[0] ) {
			echo esc_html__( 'database table does not exists: ' . $atkp_queuetable_helper->get_producttable_tablename(), ATKP_PLUGIN_PREFIX );

			return;
		}

		$entries = $atkp_queuetable_helper->get_list_entry( 0, $post->ID, null, 100, 1, 'id', 'desc' );

		?>
        <table class="wp-list-table widefat fixed striped table-view-list queueentries">
            <thead>
            <tr>
                <th><?php echo esc_html__( 'ID', ATKP_PLUGIN_PREFIX ) ?></th>
                <th><?php echo esc_html__( 'Queue', ATKP_PLUGIN_PREFIX ) ?></th>
                <th><?php echo esc_html__( 'Shop', ATKP_PLUGIN_PREFIX ) ?></th>
                <th><?php echo esc_html__( 'Status', ATKP_PLUGIN_PREFIX ) ?></th>
                <th><?php echo esc_html__( 'Function', ATKP_PLUGIN_PREFIX ) ?></th>
                <th><?php echo esc_html__( 'Parameter', ATKP_PLUGIN_PREFIX ) ?></th>
                <th><?php echo esc_html__( 'Last update', ATKP_PLUGIN_PREFIX ) ?></th>
                <th><?php echo esc_html__( 'Message', ATKP_PLUGIN_PREFIX ) ?></th>
            </tr>
            </thead>
            <tbody>
			<?php

			foreach ( $entries as $entry ) {

				?>

                <tr>
                    <td class="id column-id has-row-actions column-primary" data-colname="ID">
	                    <?php echo esc_html__( $entry['id'], ATKP_PLUGIN_PREFIX ); ?>
                    </td>
                    <td class="queue_id column-id has-row-actions column-primary" data-colname="ID">
						<?php
						$queueid = $entry['queue_id'];
						if ( $queueid > 0 ) {
							$link = admin_url( 'admin.php?page=ATKP_viewqueue&action=detail&queueid=' . $queueid );
							if ( $link == null ) {
								echo esc_html($queueid);
							} else {
								$title = esc_html__( 'Queue', ATKP_PLUGIN_PREFIX );

								echo '<a href="' . esc_url( $link ) . '" target="_blank">' . esc_html__( $title, ATKP_PLUGIN_PREFIX ) . ' (' . esc_html( $queueid ) . ')</a>';
							}
						}

						?>

                    </td>
                    <td class="shop_id column-shop_id" data-colname="Shop">

						<?php
						$shopid = $entry['shop_id'];
						if ( $shopid > 0 ) {
							$link = get_edit_post_link( $shopid );
							if ( $link == null ) {
								echo esc_html($shopid);
							} else {
								$title = get_the_title( $shopid );

								echo '<a href="' . esc_url( $link ) . '" target="_blank">' . esc_html__( $title, ATKP_PLUGIN_PREFIX ) . ' (' . esc_html( $shopid ) . ')</a>';
							}
						}

						?>

                    </td>
                    <td class="status column-status" data-colname="Status">
						<?php

						switch ( $entry['status'] ) {
							case atkp_queue_entry_status::SUCCESSFULLY:
								echo '<span style="color:green;font-weight:bold;">' . esc_html__( 'Successfully', ATKP_PLUGIN_PREFIX ) . '</span>';
								break;
							case atkp_queue_entry_status::ERROR:
								echo '<span style="color:red;font-weight:bold;">' . esc_html__( 'Error', ATKP_PLUGIN_PREFIX ) . '</span>';
								break;
							case atkp_queue_entry_status::NOT_PROCESSED:
								echo '<span style="color:orange;font-weight:bold;">' . esc_html__( 'Not processed', ATKP_PLUGIN_PREFIX ) . '</span>';
								break;
							case atkp_queue_entry_status::PROCESSED:
								echo '<span style="font-weight:bold;">' . esc_html__( 'Processed', ATKP_PLUGIN_PREFIX ) . '</span>';
								break;
							case atkp_queue_entry_status::FINISHED:
								echo '<span style="color:green;font-weight:bold;">' . esc_html__( 'Finalized', ATKP_PLUGIN_PREFIX ) . '</span>';
								break;
							case atkp_queue_entry_status::PREPARED:
								echo '<span style="color:orange;font-weight:bold;">' . esc_html__( 'Prepared for processing', ATKP_PLUGIN_PREFIX ) . '</span>';
								break;
						}

						?>
                    </td>
                    <td class="functionname column-functionname" data-colname="Function">
	                    <?php echo esc_html__( $entry['functionname'], ATKP_PLUGIN_PREFIX ) ?>
                    </td>
                    <td class="functionparameter column-functionparameter" data-colname="Parameter">
	                    <?php echo esc_html__( $entry['functionparameter'], ATKP_PLUGIN_PREFIX ) ?>
                    </td>
                    <td class="updatedon column-updatedon" data-colname="Last update">
	                    <?php echo esc_html__( ATKPTools::get_formatted_date( strtotime( $entry['updatedon'] ) ), ATKP_PLUGIN_PREFIX ) . esc_html__( ' at ', ATKP_PLUGIN_PREFIX ) . esc_html__( ATKPTools::get_formatted_time( strtotime( $entry['updatedon'] ) ), ATKP_PLUGIN_PREFIX ); ?>
                    </td>
                    <td class="updatedmessage column-updatedmessage" data-colname="Message">
	                    <?php echo esc_html__( $entry['updatedmessage'], ATKP_PLUGIN_PREFIX ) ?>
                    </td>
                </tr>


				<?php

			}

			?>
            </tbody>
        </table>

		<?php


	}

	function product_products_box_content( $post ) {
		$atkp_producttable_helper = new atkp_producttable_helper();
		if ( ! $atkp_producttable_helper->exists_table()[0] ) {
			echo esc_html__( 'database table does not exists: ' . $atkp_producttable_helper->get_producttable_tablename(), ATKP_PLUGIN_PREFIX );

			return;
		}


		if ( $this->atkp_product_collection == null ) {
			$this->atkp_product_collection = atkp_product_collection::load( $post->ID, '', true );
		}

		?><?php
		$hide_shops = ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_hide_shops' );

		$idx = 1;
		foreach ( $this->atkp_product_collection->products as $product ) {
			?>
            <div style="padding:5px;float:left;">
				<?php $ss = $product->shop == null ? atkp_shop::load( $product->shopid ) : $product->shop;

				$is_checked = '';
				if ( $ss != null ) {
					if ( $hide_shops != null ) {
						foreach ( $hide_shops as $h ) {

							if ( $h['shop_id'] == $ss->id && $h['product_id'] == $product->productid ) {
								$is_checked = 'checked';
								break;
							}
						}
					}
				}

				?>
                <table style="border-collapse: collapse;width:550px;height:250px;overflow-wrap: anywhere;font-size:12px;"
                       class="<?php echo esc_attr(( $ss != null ? ' atkp-shop-id-' . esc_html($ss->id) . ' ' : '' ) . ( $ss != null && ( $ss->hidepricecomparision || $is_checked == 'checked' ) ? ' atkp-hide-shop ' : '' )) ?>">
                    <tr style="border:1px solid #bde4ea;; ">
                        <td style="width:140px;text-align:center">

							<?php

							$imageurl = $product->mediumimageurl;
							if ( $imageurl == '' ) {
								$imageurl = $product->smallimageurl;
							}
							if ( $imageurl == '' ) {
								$imageurl = $product->largeimageurl;
							}
							if ( $imageurl != '' ) {
								echo '<img src="' . esc_url($imageurl) . '" style="max-width:140px;max-height:200px" />';
							}


							?>
                        </td>
                        <td style="vertical-align: middle">

                            <table>
								<?php do_action( 'atkp_product_before_item', $product, $ss ); ?>
                                <tr>
                                    <th class="atkp_shop_head"><?php echo esc_html__( 'Sort order', ATKP_PLUGIN_PREFIX ); ?>
                                        :
                                    </th>
                                    <td><?php echo $idx == 1 ? esc_html__( 'Main product', ATKP_PLUGIN_PREFIX ) : '#' . esc_html( $idx ) ?></td>
                                </tr>
                                <tr>
                                    <th class="atkp_shop_head"><?php echo esc_html__( 'Shop', ATKP_PLUGIN_PREFIX ); ?>
                                        :
                                    </th>
                                    <td>
										<?php

										if ( $ss != null && $ss->get_smalllogourl() != '' ) {
											echo '<a title="' . esc_attr_e( $ss->get_title() . ( $ss->hidepricecomparision ? esc_html__( ' (hidden)', ATKP_PLUGIN_PREFIX ) : '' ), ATKP_PLUGIN_PREFIX ) . '"  target="_blank" href="' . esc_url( get_edit_post_link( $product->shopid ) ) . '"><img alt="' . esc_attr_e( $ss->get_title(), ATKP_PLUGIN_PREFIX ) . '" style="' . ( $product->ismainshop ? 'border:green solid 1px' : '' ) . ';max-height:17px" src="' . ( esc_url( $ss->get_smalllogourl() ) ) . '" /></a>';
										} else if ( $ss != null ) {
											echo '<a title="' . esc_attr_e( $ss->get_title() . ( $ss->hidepricecomparision ? esc_html__( ' (hidden)', ATKP_PLUGIN_PREFIX ) : '' ), ATKP_PLUGIN_PREFIX ) . '" target="_blank" href="' . esc_url( get_edit_post_link( $product->shopid ) ) . '"><span style="' . ( $product->ismainshop ? 'border:green solid 1px' : '' ) . '">' . ( esc_html__( $ss->get_title(), ATKP_PLUGIN_PREFIX ) ) . '</span></a>';
										} else {
											echo 'no shop?';
										}

										?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="atkp_shop_head"><?php echo esc_html__( 'Unique ID', ATKP_PLUGIN_PREFIX ); ?>
                                        :
                                    </th>
                                    <td><?php echo esc_html__( $product->asin, ATKP_PLUGIN_PREFIX ); ?></td>
                                </tr>
                                <tr>
                                    <th class="atkp_shop_head"><?php echo esc_html__( 'Name', ATKP_PLUGIN_PREFIX ); ?>
                                        :
                                    </th>
                                    <td><a href="<?php echo esc_url($product->producturl) ?>"
                                           target="_blank"><?php echo esc_html__( strlen( $product->title ) > 40 ? substr( $product->title, 0, 37 ) . '...' : $product->title, ATKP_PLUGIN_PREFIX ); ?></a>
                                    </td>

                                </tr>
                                <tr>
                                    <th class="atkp_shop_head"><?php echo esc_html__( 'Image Gallery', ATKP_PLUGIN_PREFIX ); ?>
                                        :
                                    </th>
                                    <td><?php echo( $product->images == null ? 0 : count( $product->images ) ); ?></td>
                                </tr>
                                <tr>
                                    <th class="atkp_shop_head"><?php echo esc_html__( 'CPC Price', ATKP_PLUGIN_PREFIX ); ?>
                                        :
                                    </th>
                                    <td><?php echo esc_html__( $product->cpcfloat == 0 ? esc_html__( 'Affiliate product', ATKP_PLUGIN_PREFIX ) : ( $product->cpc . ' (' . $product->cpcfloat . ')' ), ATKP_PLUGIN_PREFIX ) ?></td>
                                </tr>

                                <tr>
                                    <th class="atkp_shop_head"><?php echo esc_html__( 'Sale Price', ATKP_PLUGIN_PREFIX ); ?>
                                        :
                                    </th>
                                    <td><?php if ( $product->saleprice == '' ) { ?>
                                            <span style="color:red"><?php echo esc_html__( 'This product is a fallback. Please replace it.', ATKP_PLUGIN_PREFIX ) ?></span>
										<?php } else { ?>
                                            <b><?php echo esc_html__( $product->saleprice, ATKP_PLUGIN_PREFIX ); ?>
                                                (<?php echo esc_html__( $product->salepricefloat, ATKP_PLUGIN_PREFIX ); ?>
                                                )</b>
										<?php } ?>
                                    </td>
                                </tr>

                                <tr>
                                    <th class="atkp_shop_head"><?php echo esc_html__( 'Shipping', ATKP_PLUGIN_PREFIX ); ?>
                                        :
                                    </th>
                                    <td><?php echo esc_html__( $product->shipping != '' && strlen( $product->shipping ) > 40 ? substr( $product->shipping, 0, 37 ) . '..' : $product->shipping, ATKP_PLUGIN_PREFIX ); ?></td>
                                </tr>
                                <tr>
                                    <th class="atkp_shop_head"><?php echo esc_html__( 'Availability', ATKP_PLUGIN_PREFIX ); ?>
                                        :
                                    </th>
                                    <td><?php echo esc_html__( $product->availability != '' && strlen( $product->availability ) > 40 ? substr( $product->availability, 0, 37 ) . '..' : $product->availability, ATKP_PLUGIN_PREFIX ); ?></td>
                                </tr>
                                <tr>
                                    <th class="atkp_shop_head"><?php echo esc_html__( 'Updated on', ATKP_PLUGIN_PREFIX ); ?>
                                        :
                                    </th>
                                    <td><?php echo esc_html__( ATKPTools::get_formatted_date( ( $product->updatedon ) ) . ' ' . ATKPTools::get_formatted_time( ( $product->updatedon ) ) . ( $product->isupdated != null && $product->isupdated == false ? ' ' . esc_html__( 'Update planned', ATKP_PLUGIN_PREFIX ) : '' ), ATKP_PLUGIN_PREFIX ) ?></td>
                                </tr>
								<?php if ( $ss != null && ! $ss->hidepricecomparision ) {


									?>
                                    <tr>
                                        <th class="atkp_shop_head"><?php echo esc_html__( 'Hide product', ATKP_PLUGIN_PREFIX ); ?>
                                            :
                                        </th>
                                        <td><input type="checkbox"
                                                   data-shopid="<?php echo esc_attr($ss->id) ?>" <?php echo esc_html($is_checked); ?>
                                                   class="atkp-hide-product"
                                                   name="<?php echo esc_html('atkp_hide_product_' . $ss->id . '_' . $product->productid) ?>"
                                                   value="1"></td>
                                    </tr>
								<?php } ?>
								<?php do_action( 'atkp_product_after_item', $product, $ss ); ?>

                            </table>
                        </td>
                    </tr>
                </table>

            </div>
			<?php
			$idx ++;
		}
		?>
        <div class="atkp-clearfix"></div>
        <style>
            .atkp_shop_head {
                vertical-align: top;
                width: 150px;
                text-align: left;
            }

            .atkp-hide-shop {
                opacity: 0.6;


                background: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' version='1.1' preserveAspectRatio='none' viewBox='0 0 100 100'><path d='M100 0 L0 100 ' stroke='gainsboro' stroke-width='1'/><path d='M0 0 L100 100 ' stroke='gainsboro' stroke-width='1'/></svg>");
                background-repeat: no-repeat;
                background-position: center center;
                background-size: 100% 100%, auto;
            }

        </style>
		<?php
	}

	function product_shop_box_content( $post ) {
		wp_nonce_field( plugin_basename( __FILE__ ), 'product_shop_box_content_nonce' );


		?>
        <table class="form-table atkp-filter-table">

			<?php
			$i = 0;
			for ( $x = 1; $x < ( ATKP_FILTER_COUNT + 1 ); $x ++ ) {

				$asin           = ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_asin' . ( $x > 1 ? '_' . $x : '' ) );
				$asintype       = ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_asintype' . ( $x > 1 ? '_' . $x : '' ) );
				$selectedshopid = ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_shopid' . ( $x > 1 ? '_' . $x : '' ) );


				if ( $asin == null && $asintype == null && $selectedshopid == null && $i != 0 ) {
					continue;
				}
				$i ++;


				?>


                <tr class="atkp_filter-row-base <?php echo( $x > 1 ? 'atkp_filter-row-child' : 'atkp-filter-row' ) ?> ">

                    <td style="width:30%">
                        <select id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_shopid' . ( $x > 1 ? '_' . $i : '' )) ?>"
                                name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_shopid' . ( $x > 1 ? '_' . $i : '' )) ?>"
                                style="width:100%" class="atkp_keychange atkp_shopfield">
							<?php
							echo '<option value="">' . esc_html__( 'No shop', ATKP_PLUGIN_PREFIX ) . '</option>';
							echo '<option value="-1" ' . ( $selectedshopid == - 1 ? 'selected' : '' ) . '>' . esc_html__( 'All shops', ATKP_PLUGIN_PREFIX ) . '</option>';

							$shps = atkp_shop::get_list( $selectedshopid );

							foreach ( $shps as $shp ) {
								if ( $shp->selected == true ) {
									$sel = ' selected';
								} else {
									$sel = '';
								}

								echo '<option ' . ( $shp->type == atkp_shop_type::SUB_SHOPS ? 'disabled' : '' ) . '  value="' . esc_attr( $shp->id ) . '"' . esc_attr( $sel ) . ' > ' . esc_html__( $shp->title, ATKP_PLUGIN_PREFIX ) . '</option>';

								foreach ( $shp->children as $child ) {
									if ( $child->selected == true ) {
										$sel = ' selected';
									} else {
										$sel = '';
									}

									echo '<option value="' . esc_attr( $child->id ) . '"' . esc_attr( $sel ) . ' >' . esc_html__( $child->title, ATKP_PLUGIN_PREFIX ) . ' [' . esc_html__( $shp->title, ATKP_PLUGIN_PREFIX ) . ']</option>';
								}
							}

							?>
                        </select>


                    </td>

                    <td style="width:30%">
                        <select title="<?php echo esc_attr_e('select key type', ATKP_PLUGIN_PREFIX ); ?>"
                                name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_asintype' . ( $x > 1 ? '_' . $i : '' ) ) ?>"
                                id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_asintype' . ( $x > 1 ? '_' . $i : '' ) )?>"
                                style="width:100%" class="atkp_keychange atkp_keytype">
							<?php

							if ( $asintype == '' || $asintype == null ) {
								$asintype = 'ASIN';
							}

							$durations = array(
								'ASIN'          => esc_html__( 'Unique productid', ATKP_PLUGIN_PREFIX ),
								'EAN'           => esc_html__( 'EAN', ATKP_PLUGIN_PREFIX ),
								'TITLE'         => esc_html__( 'Title', ATKP_PLUGIN_PREFIX ),
								'ARTICLENUMBER' => esc_html__( 'Articlenumber', ATKP_PLUGIN_PREFIX ),
							);

							foreach ( $durations as $value => $name ) {
								if ( $value == $asintype ) {
									$sel = ' selected';
								} else {
									$sel = '';
								}

								echo '<option value="' . esc_attr( $value ) . '" ' . esc_attr( $sel ) . '>' . esc_html__( $name, ATKP_PLUGIN_PREFIX ) . '</option>';
							} ?>
                        </select>

                    </td>
                    <td style="width:30%">
                        <input type="text"
                               id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_asin' . ( $x > 1 ? '_' . $i : '' ) ) ?>"
                               style="width:100%"
                               name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_asin' . ( $x > 1 ? '_' . $i : '' ) ) ?>"
                               value="<?php echo esc_attr($asin); ?>" class="atkp_keychange atkp_keyvalue"/>

                    </td>
                    <td>
                        <a href="javascript:void(0)" class="button atkp_searchbutton atkp_prdbtnsearch"
                           data-keyvalue="<?php echo esc_attr('atkp_product_asin' . ( $x > 1 ? '_' . $i : '' ) ) ?>"
                           data-keytype="<?php echo esc_attr('atkp_product_asintype' . ( $x > 1 ? '_' . $i : '' ) ) ?>"
                           data-shopid="<?php echo esc_attr('atkp_product_shopid' . ( $x > 1 ? '_' . $i : '' ) ) ?>"><span
                                    class="dashicons dashicons-filter atkp-button-icon"></span> <?php echo esc_html__( 'Find', ATKP_PLUGIN_PREFIX ) ?>
                        </a>
                        <a href="javascript:void(0)" class="button atkp_searchbutton atkp_prdbtndelete"><span
                                    class="dashicons dashicons-trash atkp-button-icon"></span> </a>
                    </td>
                </tr>

				<?php
			}

			?>

            <tr class="atkp-filter-container">
                <td colspan="4">
                    <div class="atkp-lookupcontainer">
                        <div>
                            <label for=""><?php echo esc_html__( 'Keyword:', ATKP_PLUGIN_PREFIX ) ?></label>
                            <input type="text" id="atkp_prdlookupsearch" name="atkp_prdlookupsearch" value=""
                                   placeholder="<?php echo esc_attr_e('Your keyword...', ATKP_PLUGIN_PREFIX ) ?>"/>
                            <a href="javascript:void(0)" class="button atkp_searchbutton atkp_prdlookupbtnsearch"
                               id="atkp_prdlookupbtnsearch"><span
                                        class="dashicons dashicons-search atkp-button-icon"></span> <?php echo esc_html__( 'Search', ATKP_PLUGIN_PREFIX ) ?>
                            </a>
                            <a href="javascript:void(0)" class="button atkp_searchbutton atkp_closeprdsearch"
                               id="atkp_closeprdsearch"><span
                                        class="dashicons dashicons-dismiss atkp-button-icon"></span> <?php echo esc_html__( 'Close', ATKP_PLUGIN_PREFIX ) ?>
                            </a>


                            <div id="atkp_loadingimage" style="display: none;text-align:center">
                                <img src="<?php echo esc_url(plugin_dir_url( ATKP_PLUGIN_FILE ) . '/images/spin.gif' )?>"
                                     style="width:32px" alt="loading"/>
                            </div>
                        </div>

                        <div id="atkp_prdlookupresult" class="atkp_prdlookupresult">
                        </div>
                    </div>
                </td>

            </tr>


            <tr>

                <td style="width:30%">
                    <select disabled id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_ean_shopid' ) ?>"
                            name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_ean_shopid' ) ?>" style="width:100%"
                            class="atkp_keychange">
						<?php
						echo '<option value="">' . esc_html__( 'All shops', ATKP_PLUGIN_PREFIX ) . '</option>';

						?>
                    </select>

                </td>

                <td style="width:30%">

                    <select disabled title="<?php echo esc_attr_e('select key type', ATKP_PLUGIN_PREFIX ); ?>" style="width:100%"
                            class="atkp_keychange">
						<?php
						echo '<option value="EAN" ' . esc_attr( $sel ) . '>' . esc_html__( 'EAN', ATKP_PLUGIN_PREFIX ) . '</option>';
						?>
                    </select>
                </td>
                <td style="width:30%">
                    <input type="text" id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_ean' ) ?>" style="width:100%"
                           name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_ean' ) ?>" class="atkp_keychange"
                           value="<?php echo esc_attr( ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_ean' ) ); ?>">

                </td>
                <td>
                    <label><input type="checkbox" id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_ean_lock' )?>"
                                  name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_ean_lock' )?>" value="1"
							<?php echo checked( 1, ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_ean_lock' ), false ); ?>
                        > <?php echo esc_html__( 'Lock EANs', ATKP_PLUGIN_PREFIX ) ?></label>
                </td>
            </tr>

            <tr>

                <td style="width:30%">

                    <select disabled id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_isbn_shopid')?>" class="atkp_keychange"
                            name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_isbn_shopid') ?>" style="width:100%">
						<?php
						echo '<option value="">' . esc_html__( 'All shops', ATKP_PLUGIN_PREFIX ) . '</option>';

						?>
                    </select>
                </td>

                <td style="width:30%">

                    <select disabled title="<?php echo esc_attr_e('select key type', ATKP_PLUGIN_PREFIX ); ?>"
                            class="atkp_keychange" style="width:100%">
						<?php
						echo '<option value="ISBN" ' . esc_attr( $sel ) . '>' . esc_html__( 'ISBN', ATKP_PLUGIN_PREFIX ) . '</option>';
						?>
                    </select>
                </td>
                <td style="width:30%">
                    <input type="text" id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_isbn') ?>" style="width:100%"
                           class="atkp_keychange"
                           name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_isbn') ?>"
                           value="<?php echo esc_attr( ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_isbn' ) ); ?>">
                </td>
                <td>
                    <label><input type="checkbox" id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_isbn_lock') ?>"
                                  name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_isbn_lock' ) ?>" value="1"
							<?php echo checked( 1, ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_isbn_lock' ), false ); ?>
                        > <?php echo esc_html__( 'Lock ISBNs', ATKP_PLUGIN_PREFIX ) ?></label>
                </td>
            </tr>

            <tr>

                <td style="width:30%">

                    <select disabled id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_gtin_shopid') ?>" class="atkp_keychange"
                            name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_gtin_shopid') ?>" style="width:100%">
						<?php
						echo '<option value="">' . esc_html__( 'All shops', ATKP_PLUGIN_PREFIX ) . '</option>';

						?>
                    </select>
                </td>

                <td style="width:30%">

                    <select disabled title="<?php echo esc_attr_e('select key type', ATKP_PLUGIN_PREFIX ); ?>"
                            class="atkp_keychange" style="width:100%">
						<?php
						echo '<option value="GTIN" ' . esc_attr( $sel ) . '>' . esc_html__( 'GTIN', ATKP_PLUGIN_PREFIX ) . '</option>';
						?>
                    </select>
                </td>
                <td style="width:30%">
                    <input type="text" id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_gtin') ?>" style="width:100%"
                           class="atkp_keychange"
                           name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_gtin') ?>"
                           value="<?php echo esc_attr( ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_gtin' ) ); ?>">
                </td>
                <td>
                    <label><input type="checkbox" id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_gtin_lock') ?>"
                                  name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_gtin_lock') ?>" value="1"
							<?php echo checked( 1, ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_gtin_lock' ), false ); ?>
                        > <?php echo esc_html__( 'Lock GTINs', ATKP_PLUGIN_PREFIX ) ?></label>
                </td>
            </tr>

            <tr>

                <td colspan="4"><i>
                        <a href="javascript:void(0)" class="button atkp-add-filter"><span
                                    class="dashicons dashicons-insert atkp-button-icon"></span> <?php echo esc_html__( 'Add a new search key', ATKP_PLUGIN_PREFIX ) ?>
                        </a>
                </td>
            </tr>


			<?php

			$updatedon = ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_updatedon' );
			$message   = ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_message' );


			?>

            <tr style="padding:0 10px">

                <td colspan="4" style="padding: 15px 10px">
                    <label><input type="checkbox" id="atkp_product_filter_changed"
                                  name="atkp_product_filter_changed" value="1"
                        > <?php echo esc_html__( 'Load products from shops on save', ATKP_PLUGIN_PREFIX ) ?></label>
					<?php ATKPTools::display_helptext( 'If you select this option the product will be searched in the selected shop on save.' ) ?>
                </td>
            </tr>
            <tr>

                <td colspan="4"><span class="atkp-status-text" style="font-style: italic">


						<?php
						if ( isset( $updatedon ) && $updatedon != '' ) {
							$infotext = esc_html__( 'Product updated on %refresh_date% at %refresh_time%', ATKP_PLUGIN_PREFIX );

							$infotext = str_replace( '%refresh_date%', ATKPTools::get_formatted_date( $updatedon ), $infotext );
							$infotext = str_replace( '%refresh_time%', ATKPTools::get_formatted_time( $updatedon ), $infotext );


							echo esc_html__( $infotext, ATKP_PLUGIN_PREFIX ); ?><br/>
						<?php } else { ?>
                            <span><?php echo esc_html__( 'This product will be added to the next queue.', ATKP_PLUGIN_PREFIX ) ?></span>
						<?php } ?>
		                <?php echo '<div style="color:red; overflow-wrap: break-word; max-width: 500px;">' . esc_html__( $message, ATKP_PLUGIN_PREFIX ) . '</div>'; ?>

						<?php do_action( 'atkp_product_message', $post->ID ); ?>

                    </span></td>
            </tr>
        </table>


        <script>

            jQuery(document).ready(function ($) {
                //atkp-filter-row
                $('body').on('click', '.atkp-add-filter', function (e) {
                    if (typeof $j('.atkp_shopfield').select2atkp == 'function')
                        $(".atkp_shopfield").select2atkp('destroy');
                    var count = $('.atkp_filter-row-base').length;
                    var id = count + 1;

                    if (id > <?php echo esc_html(ATKP_FILTER_COUNT) ?>) {
                        alert('<?php echo sprintf( esc_html__( 'Maximum allowed fields of %s exceeded.', ATKP_PLUGIN_PREFIX ), esc_html(ATKP_FILTER_COUNT) ) ?>');
                        return;
                    }

                    var filterrow = $('.atkp-filter-row');
                    var newfilterrow = filterrow.clone();
                    newfilterrow.attr('class', 'atkp_filter-row-base atkp_filter-row-child atkp-filter-row-copy')
                    var atkp_shopfield = newfilterrow.find('.atkp_shopfield');
                    var atkp_keytype = newfilterrow.find('.atkp_keytype');
                    var atkp_keyvalue = newfilterrow.find('.atkp_keyvalue');
                    var atkp_prdbtnsearch = newfilterrow.find('.atkp_prdbtnsearch');
                    atkp_shopfield.val('-1')
                    atkp_keytype.val('ASIN');
                    atkp_keyvalue.val('');

                    atkp_shopfield.attr('id', 'atkp_product_shopid_' + id)
                    atkp_shopfield.attr('name', 'atkp_product_shopid_' + id)

                    atkp_keytype.attr('id', 'atkp_product_asintype_' + id)
                    atkp_keytype.attr('name', 'atkp_product_asintype_' + id)

                    atkp_keyvalue.attr('id', 'atkp_product_asin_' + id)
                    atkp_keyvalue.attr('name', 'atkp_product_asin_' + id)

                    atkp_prdbtnsearch.attr('data-keyvalue', 'atkp_product_asin_' + id);
                    atkp_prdbtnsearch.attr('data-keytype', 'atkp_product_asintype_' + id);
                    atkp_prdbtnsearch.attr('data-shopid', 'atkp_product_shopid_' + id);


                    filterrow.after(newfilterrow);
                    if (typeof $j('.atkp_shopfield').select2atkp == 'function')
                        $j('.atkp_shopfield').select2atkp({});
                });

                $('body').on('change', '.atkp_keychange', function (e) {//$('#atkp_product_filter_changed').val(1);
                    $('#atkp_product_filter_changed').prop("checked", true);

                });

                $('body').on('change', '.atkp_shopfield', atkp_key_changing);

                function atkp_key_changing(e) {
                    if ($('.atkp_shopfield').val() == '') {
                        //hide
                        $('.atkp_keytype').hide();
                        $('.atkp_keyvalue').hide();
                        $('.atkp_searchbutton').hide();
                    } else if ($('.atkp_shopfield').val() == '-1') {
                        $('.atkp_keytype').show();
                        $('.atkp_keyvalue').show();
                        $('.atkp_searchbutton').hide();
                    } else {
                        //show
                        $('.atkp_keytype').show();
                        $('.atkp_keyvalue').show();
                        $('.atkp_searchbutton').show();
                    }


                }

                atkp_key_changing();

                $('body').on('change', '#atkp_product_filter_changed', function (e) {
                    $('.atkp-status-text').hide();

                });

                $('body').on('click', '.atkp_closeprdsearch', function (e) {
                    $('.atkp-filter-container').hide();
                    $('.atkp_prdbtnsearch').removeAttr('disabled');
                });

                $('body').on('click', '.atkp_prdbtndelete', function (e) {
                    $(this).parent().parent().remove();
                    $j('#atkp_offerschanged').val('1');
                });
                $('body').on('click', '.atkp_prdbtnsearch', function (e) {
                    $('.atkp_prdbtnsearch').attr('disabled', 'disabled');
                    $('.atkp-filter-container').data('shopid', $(this).data('shopid'));
                    $('.atkp-filter-container').data('keytype', $(this).data('keytype'));
                    $('.atkp-filter-container').data('keyvalue', $(this).data('keyvalue'));

                    if ($('.atkp-filter-container').is(":hidden"))
                        $('.atkp-filter-container').show();
                    else
                        $('.atkp-filter-container').hide();
                });

                $('body').on('click', '.atkp-useproduct', function (e) {

                    var id = $(this).data('id');
                    var keytype = $(this).data('keytype');
                    var valuefield = $(this).data('valuefield');

                    $("#" + valuefield).val(id);

                    $('#atkp_product_filter_changed').prop("checked", true);

                    $('.atkp-filter-container').hide();
                    $('.atkp_prdbtnsearch').removeAttr('disabled');
                });


                $('body').on('change', '.atkp-hide-product', function (e) {
                    var s = $(this).data('shopid');

                    if ($(this).is(":checked")) {
                        $('.atkp-shop-id-' + s).addClass('atkp-hide-shop');
                    } else {
                        $('.atkp-shop-id-' + s).removeClass('atkp-hide-shop');
                    }
                });

                $('body').on('click', '.atkp_prdlookupbtnsearch', function (e) {
                    var shopid = $('.atkp-filter-container').data('shopid');
                    var keytype = $('.atkp-filter-container').data('keytype');
                    var keyvalue = $('.atkp-filter-container').data('keyvalue');


                    $j("#atkp_prdlookupresult").html('');
                    $j("#atkp_loadingimage").show();

                    //console.log($j('#'+shopid).val());
                    //console.log($j('#atkp_prdlookupsearch').val());

                    $j.ajax({
                        type: "POST",
                        url: "<?php echo esc_js(ATKPTools::get_endpointurl()); ?>",
                        data: {
                            action: "atkp_search_products",
                            shop: $j('#' + shopid).val(),
                            keyword: $j('#atkp_prdlookupsearch').val(),
                            request_nonce: "<?php echo esc_html(wp_create_nonce( 'atkp-search-nonce' )) ?>"
                        },

                        dataType: "json",
                        success: function (data) {
                            console.log(data);
                            try {
                                var count = 0;
                                $j.each(data, function (key, value) {
                                    count++;
                                });

                                if (count > 0) {

                                    if (typeof data[0].error != 'undefined') {
                                        $j("#atkp_prdlookupresult").html('<span style="color:red">' + data[0].error + '<br /> ' + data[0].message + '</span>');
                                    } else {

                                        var outputresult = '';

                                        $j.each(data, function (index, value) {
                                            outputresult += ' <div style="padding:10px;float:left;">' +
                                                '<table style="border-collapse: collapse;width:550px;height:150px;overflow-wrap: anywhere;font-size:12px" class="atkp-result-table">' +
                                                '<tr style="border:1px solid #bde4ea;padding:10px; ">' +
                                                '<td style="width:140px;text-align:center">' +
                                                '<img style="max-width:100px" src="' + value.imageurl + '" />' +
                                                '</td>' +
                                                '<td style="vertical-align: middle">' +
                                                '<table>' +
                                                '<tr>' +
                                                '<th class="atkp_shop_head"><?php echo esc_html__( 'Unique ID' ); ?>:</th> <td>' + value.asin + '</td>' +
                                                '</tr>' +
                                                '<tr>' +
                                                '<th class="atkp_shop_head"><?php echo esc_html__( 'Name' ); ?>:</th> <td><a href="' + value.producturl + '" target="_blank">' + truncateStr(value.title, 25, true) + '</a></td>' +
                                                '</tr>' +
                                                '<tr>' +
                                                '<th class="atkp_shop_head"><?php echo esc_html__( 'EAN' ); ?>:</th> <td>' + value.ean + '</td>' +
                                                '</tr>' +
                                                '<tr>' +
                                                '<th class="atkp_shop_head"><?php echo esc_html__( 'Sale Price' ); ?>:</th> <td>' + value.saleprice + '</td>' +
                                                '</tr>' +
                                                '<tr>' +
                                                '<td colspan="2" style="text-align:center"><a href="javascript:void(0)" class="button atkp_searchbutton atkp-useproduct" data-id="' + value.asin + '" data-valuefield="' + keyvalue + '" data-keytype="' + $j('#' + keytype).val() + '" data-shopid="' + $j('#' + shopid).val() + '"><span class="dashicons dashicons-plus-alt atkp-button-icon"></span> <?php echo esc_html__( 'Select this product', ATKP_PLUGIN_PREFIX ); ?></a></td>' +
                                                '</tr>' +
                                                '</table>' +
                                                '</td>' +
                                                '</tr>' +
                                                '</table>' +
                                                '</div>';

                                        });

                                        outputresult += '<div class="atkp-clearfix"></div>';
                                        $j("#atkp_prdlookupresult").html(outputresult);
                                    }
                                }
                            } catch (err) {
                                $j("#atkp_prdlookupresult").html('<span style="color:red">' + err.message + '</span>');
                                $j("#atkp_loadingimage").hide();
                            }

                            $j("#atkp_loadingimage").hide();
                        },
                        error: function (xhr, status) {
                            $j("#atkp_prdlookupresult' ?>").html('<span style="color:red">' + xhr.responseText + '</span>');
                            $j("#atkp_loadingimage").hide();
                        }
                    });
                });

                if (typeof $j('.atkp_shopfield').select2atkp == 'function')
                    $j('.atkp_shopfield').select2atkp({});

            });

            function truncateStr(str, n, useWordBoundary) {
                if (str.length <= n) {
                    return str;
                }
                const subString = str.substr(0, n - 1); // the original check
                console.log(subString);

                var x = subString.lastIndexOf(" ");
                if (x <= 0)
                    x = subString.length;

                return (useWordBoundary
                    ? subString.substr(0, x)
                    : subString) + "&hellip;";
            };
        </script>

        <style>
            .atkp-result-table {
                font-size: 12px !important;
            }

            .atkp_prdbtndelete {
                display: none !important;

            }

            .atkp_filter-row-child .atkp_prdbtndelete {

                display: inline-block !important;
            }

            .atkp-button-icon {
                font-size: 16px;
                line-height: initial;
                vertical-align: middle;
            }

            .atkp-filter-table td {
                padding: 5px;
            }

            .atkp-result-table td, .atkp-result-table th {
                padding: 3px;
            }

            .atkp-filter-container {
                display: none;
            }

            .atkp-lookupcontainer {
                background-image: linear-gradient(to top, #fafafa 0, #fdfdfd 20%, #fff 60%);
                border: 1px solid #ececec;
                padding: 10px;
            }


        </style>


		<?php

	}

	/* @var atkp_product_collection $atkp_product_collection */
	private
		$atkp_product_collection = null;

	function product_detail_box_content( $post ) {
		wp_nonce_field( plugin_basename( __FILE__ ), 'product_detail_box_content_nonce' );

		if ( $this->atkp_product_collection == null ) {
			$this->atkp_product_collection = atkp_product_collection::load( $post->ID, '', true );
		}
		?>

        <ul class='atkp-tabs'>

			<?php
			$tabs                                                                                                                                  = [];
			$tabs[ '<span class="dashicons dashicons-products atkp-button-icon"></span> ' . esc_html__( 'Product Information', ATKP_PLUGIN_PREFIX ) ] = array(
				$this,
				'product_tab1'
			);
			$tabs[ '<span class="dashicons dashicons-admin-links atkp-button-icon"></span> ' . esc_html__( 'Link Information', ATKP_PLUGIN_PREFIX ) ] = array(
				$this,
				'product_tab2'
			);
			$tabs[ '<span class="dashicons dashicons-admin-comments atkp-button-icon"></span> ' . esc_html__( 'Review Information', ATKP_PLUGIN_PREFIX ) ] = array(
				$this,
				'product_tab3'
			);
			$tabs[ '<span class="dashicons dashicons-format-image atkp-button-icon"></span> ' . esc_html__( 'Images', ATKP_PLUGIN_PREFIX ) ] = array(
				$this,
				'product_tab4'
			);
			$tabs[ '<span class="dashicons dashicons-cart atkp-button-icon"></span> ' . esc_html__( 'Price Information', ATKP_PLUGIN_PREFIX ) ] = array(
				$this,
				'product_tab5'
			);
			$tabs[ '<span class="dashicons dashicons-networking atkp-button-icon"></span> ' . esc_html__( 'Variations', ATKP_PLUGIN_PREFIX ) ] = array(
				$this,
				'product_tab8'
			);
			$tabs[ '<span class="dashicons dashicons-editor-ol atkp-button-icon"></span> ' . esc_html__( 'Manual offers', ATKP_PLUGIN_PREFIX ) ] = array(
				$this,
				'product_tab7'
			);


			$tabs = apply_filters( 'atkp_tabs_product', $tabs );

			$idx = 1;
			foreach ( $tabs as $tabcap => $tabcontent ) {
				echo '<a href="' . esc_url( '#atkp-tab' . $idx ++ ) . '" class="button" style="margin-right:10px">' . wp_kses( $tabcap, array( 'span' => array( 'class' => array() ) ) ) . '</a>';
			}
			?>

        </ul>

		<?php

		$idx = 1;
		foreach ( $tabs as $tabcap => $tabcontent ) {
			echo '<div id="' . esc_attr('atkp-tab' . $idx ++) . '">';
			call_user_func( $tabcontent, $post );
			echo '</div>';
		}

		?>


        <style>
            .atkp-tabs li {
                list-style: none;
                display: inline;
            }

            .atkp-tabs a {
                padding: 5px 10px;
                margin-left: -5px;
                display: inline-block;
                background: #666;
                border: 1px solid #666;
                color: #fff;
                text-decoration: none;
                line-height: 1.3;
                font-size: 14px;
            }

            .atkp-tabs .active {
                background: #fff;
                color: #000;
            }

        </style>

        <script type="text/javascript">
            var $j = jQuery.noConflict();

            $j(document).ready(function ($) {

//tabs

                $j('ul.atkp-tabs').each(function () {
                    // For each set of tabs, we want to keep track of
                    // which tab is active and its associated content
                    var $active, $content, $links = $(this).find('a');

                    // If the location.hash matches one of the links, use that as the active tab.
                    // If no match is found, use the first link as the initial active tab.
                    $active = $($links.filter('[href="' + location.hash + '"]')[0] || $links[0]);
                    $active.addClass('active');

                    $content = $($active[0].hash);

                    // Hide the remaining content
                    $links.not($active).each(function () {
                        $(this.hash).hide();
                    });

                    // Bind the click event handler
                    $j(this).on('click', 'a', function (e) {
                        // Make the old tab inactive.
                        $active.removeClass('active');
                        $content.hide();

                        // Update the variables with the new link and content
                        $active = $(this);
                        $content = $(this.hash);

                        // Make the tab active.
                        $active.addClass('active');
                        $content.show();

                        // Prevent the anchor's default click action
                        e.preventDefault();
                    });
                });

//tabs

                // Instantiates the variable that holds the media library frame.
                var meta_image_frame;
                var image_button;
                // Runs when the image button is clicked.
                $j('.meta-image-button').click(function (e) {

                    // Prevents the default action from occuring.
                    e.preventDefault();

                    // If the frame already exists, re-open it.
                    //if ( meta_image_frame ) {
                    //    meta_image_frame.open();
                    //    return;
                    //}

                    // Sets up the media library frame
                    meta_image_frame = wp.media.frames.meta_image_frame = wp.media({
                        title: meta_image.title,
                        button: {text: meta_image.button},
                        library: {type: 'image'}
                    });

                    image_button = $j(this).attr('id');

                    // Runs when an image is selected.
                    meta_image_frame.on('select', function () {

                        // Grabs the attachment selection and creates a JSON representation of the model.
                        var media_attachment = meta_image_frame.state().get('selection').first().toJSON();

                        // Sends the attachment URL to our custom image input field.
                        if (image_button == $j('#smallimage-button').attr('id'))
                            $j('#<?php echo esc_js(ATKP_PRODUCT_POSTTYPE . '_smallimageurl') ?>').val(media_attachment.url);
                        else if (image_button == $j('#mediumimage-button').attr('id'))
                            $j('#<?php echo esc_js(ATKP_PRODUCT_POSTTYPE . '_mediumimageurl') ?>').val(media_attachment.url);
                        else if (image_button == $j('#largeimage-button').attr('id'))
                            $j('#<?php echo esc_js(ATKP_PRODUCT_POSTTYPE . '_largeimageurl') ?>').val(media_attachment.url);
                    });

                    // Opens the media library frame.
                    meta_image_frame.open();
                });
            });

        </script>

		<?php
	}

	function product_tab1( $post ) {
		$mainproduct = $this->atkp_product_collection->get_main_product( '', true );

		?>

        <table class="form-table">

            <tr>
                <th scope="row">
                    <label for="">
	                    <?php echo esc_html__( 'Title', ATKP_PLUGIN_PREFIX ) ?>:
                    </label>
                </th>
                <td>
                    <input style="width:90%" type="text" id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_title') ?>"
                           name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_title') ?>"
                           placeholder="<?php echo esc_attr_e( $mainproduct != null ? $mainproduct->title : '', ATKP_PLUGIN_PREFIX ) ?>"
                           value="<?php echo esc_attr( ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_title' ) ); ?>">
	                <?php $this->create_clipboard_button( ATKP_PRODUCT_POSTTYPE . '_title' ); ?>
                </td>
            </tr>


            <tr>
                <th scope="row">
                    <label for="">
	                    <?php echo esc_html__( 'Manufacturer Part Number', ATKP_PLUGIN_PREFIX ) ?>:
                    </label>
                </th>
                <td>
                    <input type="text" id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_mpn') ?>"
                           name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_mpn') ?>"
                           placeholder="<?php echo esc_attr_e( $mainproduct != null ? $mainproduct->mpn : '', ATKP_PLUGIN_PREFIX ) ?>"
                           value="<?php echo esc_attr( ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_mpn' ) ); ?>">
	                <?php $this->create_clipboard_button( ATKP_PRODUCT_POSTTYPE . '_mpn' ); ?>
                </td>
            </tr>


            <tr>
                <th scope="row">
                    <label for="">
	                    <?php echo esc_html__( 'Product group', ATKP_PLUGIN_PREFIX ) ?>:
                    </label>
                </th>
                <td>
                    <input style="width:50%" type="text" id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_productgroup') ?>"
                           name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_productgroup') ?>"
                           placeholder="<?php echo esc_attr_e( $mainproduct != null ? $mainproduct->productgroup : '', ATKP_PLUGIN_PREFIX ) ?>"
                           value="<?php echo esc_attr( ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_productgroup' ) ); ?>">
	                <?php $this->create_clipboard_button( ATKP_PRODUCT_POSTTYPE . '_productgroup' ); ?>
                </td>
            </tr>


            <tr>
                <th scope="row">
                    <label for="">
	                    <?php echo esc_html__( 'Manufacturer', ATKP_PLUGIN_PREFIX ) ?>:
                    </label>
                </th>
                <td>
                    <input style="width:50%" type="text" id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_manufacturer') ?>"
                           name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_manufacturer') ?>"
                           placeholder="<?php echo esc_attr_e( $mainproduct != null ? $mainproduct->manufacturer : '', ATKP_PLUGIN_PREFIX ) ?>"
                           value="<?php echo esc_attr( ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_manufacturer' ) ); ?>">
	                <?php $this->create_clipboard_button( ATKP_PRODUCT_POSTTYPE . '_manufacturer' ); ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="">
	                    <?php echo esc_html__( 'Author', ATKP_PLUGIN_PREFIX ) ?>:
                    </label>
                </th>
                <td>
                    <input style="width:50%" type="text" id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_author') ?>"
                           name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_author') ?>"
                           placeholder="<?php echo esc_attr_e( $mainproduct != null ? $mainproduct->author : '', ATKP_PLUGIN_PREFIX ) ?>"
                           value="<?php echo esc_attr( ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_author' ) ); ?>">
	                <?php $this->create_clipboard_button( ATKP_PRODUCT_POSTTYPE . '_author' ); ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="">
	                    <?php echo esc_html__( 'Number of pages', ATKP_PLUGIN_PREFIX ) ?>:
                    </label>
                </th>
                <td>
                    <input type="number" min="0" id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_numberofpages') ?>"
                           name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_numberofpages') ?>"
                           placeholder="<?php echo esc_attr_e( $mainproduct != null ? $mainproduct->numberofpages : '', ATKP_PLUGIN_PREFIX ) ?>"
                           value="<?php echo esc_attr( ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_numberofpages' ) ); ?>">
	                <?php $this->create_clipboard_button( ATKP_PRODUCT_POSTTYPE . '_numberofpages' ); ?>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="">
	                    <?php echo esc_html__( 'Brand', ATKP_PLUGIN_PREFIX ) ?>:
                    </label>
                </th>
                <td>
                    <input style="width:50%" type="text" id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_brand') ?>"
                           name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_brand') ?>"
                           placeholder="<?php echo esc_attr_e( $mainproduct != null ? $mainproduct->brand : '', ATKP_PLUGIN_PREFIX ) ?>"
                           value="<?php echo esc_attr( ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_brand' ) ); ?>">
	                <?php $this->create_clipboard_button( ATKP_PRODUCT_POSTTYPE . '_brand' ); ?>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="">
	                    <?php echo esc_html__( 'Release date', ATKP_PLUGIN_PREFIX ) ?>:
                    </label>
                </th>
                <td>
                    <input style="width:50%" type="text" id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_releasedate') ?>"
                           name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_releasedate') ?>"
                           placeholder="<?php echo esc_attr_e( $mainproduct != null ? $mainproduct->releasedate : '', ATKP_PLUGIN_PREFIX ) ?>"
                           value="<?php echo esc_attr( ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_releasedate' ) ); ?>">
	                <?php $this->create_clipboard_button( ATKP_PRODUCT_POSTTYPE . '_releasedate' ); ?>
                </td>
            </tr>


            <tr>
                <th scope="row">
                    <label for="">
	                    <?php echo esc_html__( 'Description', ATKP_PLUGIN_PREFIX ) ?>:
                    </label>
                </th>
                <td>
					<?php
					$desc_mode_str = ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_description_mode' );
					$desc_str      = ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_description' );

					if ( $desc_mode_str == '' && $desc_str != '' ) {
						$desc_mode_str = 1;
					}

					$desc_mode = intval( $desc_mode_str );

					?>
                    <div style="padding-bottom:10px">
                        <input type="radio" id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_description_mode1') ?>"
                               name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_description_mode') ?>"
                               value="0" <?php echo checked( 0, $desc_mode, true ); ?>>
                        <label for="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_description_mode1') ?>">
	                        <?php echo esc_html__( 'Description from shop', ATKP_PLUGIN_PREFIX ) ?>
                        </label>
                        <input type="radio" id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_description_mode2') ?>"
                               name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_description_mode') ?>"
                               value="1" <?php echo checked( 1, $desc_mode, true ); ?>>
                        <label for="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_description_mode2') ?>">
	                        <?php echo esc_html__( 'Overwrite description', ATKP_PLUGIN_PREFIX ) ?>
                        </label>

                        <input type="radio" id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_description_mode3') ?>"
                               name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_description_mode') ?>"
                               value="2" <?php echo checked( 2, $desc_mode, true ); ?>>
                        <label for="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_description_mode3') ?>">
	                        <?php echo esc_html__( 'Empty description', ATKP_PLUGIN_PREFIX ) ?>
                        </label>

                        <script>
                            $j = jQuery.noConflict();
                            $j(document).ready(function () {

                                $j('input[type=radio][name=<?php echo esc_js(ATKP_PRODUCT_POSTTYPE . '_description_mode') ?>]').change(function () {
                                    if (this.value == '0') {
                                        $j('.atkp-write-desc-html').hide();
                                        $j('.atkp-readonly-desc-html').show();
                                    } else if (this.value == '1') {
                                        $j('.atkp-write-desc-html').show();
                                        $j('.atkp-readonly-desc-html').hide();
                                    } else if (this.value == '2') {
                                        $j('.atkp-write-desc-html').hide();
                                        $j('.atkp-readonly-desc-html').hide();
                                    }
                                });

                                var myval = $j('input[type="radio"][name="<?php echo esc_js(ATKP_PRODUCT_POSTTYPE . '_description_mode') ?>"]:checked').val();

                                if (myval == '0') {
                                    $j('.atkp-write-desc-html').hide();
                                    $j('.atkp-readonly-desc-html').show();
                                } else if (myval == '1') {
                                    $j('.atkp-write-desc-html').show();
                                    $j('.atkp-readonly-desc-html').hide();
                                } else if (myval == '2') {
                                    $j('.atkp-write-desc-html').hide();
                                    $j('.atkp-readonly-desc-html').hide();
                                }
                            });


                        </script>
                    </div>


					<?php
					$pl = ( $mainproduct != null ? $mainproduct->description : '' );
					echo '<div class="atkp-readonly-html atkp-readonly-desc-html">' . wp_kses( __( $pl, ATKP_PLUGIN_PREFIX ), array( 'br' => array(), 'ul' => array(), 'li' => array() ) ) . '</div>';

					ob_start();
					wp_editor( $desc_str, ATKP_PRODUCT_POSTTYPE . '_description', array(
						'media_buttons' => false,
						'textarea_name' => ATKP_PRODUCT_POSTTYPE . '_description',
						'textarea_rows' => 10,
					) );
					$textarea_html = ob_get_clean();
					echo '<div class="atkp-write-desc-html">' . ($textarea_html) . '</div>';

					?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="">
	                    <?php echo esc_html__( 'Features', ATKP_PLUGIN_PREFIX ) ?>:
                    </label>
                </th>
                <td>

					<?php
					$feat_mode_str = ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_features_mode' );
					$feat_str      = ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_features' );

					if ( $feat_mode_str == '' && $feat_str != '' ) {
						$feat_mode_str = 1;
					}

					$feat_mode = intval( $feat_mode_str );

					?>

                    <div style="padding-bottom:10px">
                        <input type="radio" id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_features_mode1') ?>"
                               name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_features_mode') ?>"
                               value="0" <?php echo checked( 0, $feat_mode, true ); ?>>
                        <label for="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_features_mode1') ?>">
	                        <?php echo esc_html__( 'Features from shop', ATKP_PLUGIN_PREFIX ) ?>
                        </label>
                        <input type="radio" id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_features_mode2') ?>"
                               name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_features_mode') ?>"
                               value="1" <?php echo checked( 1, $feat_mode, true ); ?>>
                        <label for="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_features_mode2') ?>">
	                        <?php echo esc_html__( 'Overwrite features', ATKP_PLUGIN_PREFIX ) ?>
                        </label>

                        <input type="radio" id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_features_mode3') ?>"
                               name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_features_mode') ?>"
                               value="2" <?php echo checked( 2, $feat_mode, true ); ?>>
                        <label for="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_features_mode3') ?>">
	                        <?php echo esc_html__( 'Empty features', ATKP_PLUGIN_PREFIX ) ?>
                        </label>

                        <script>
                            $j = jQuery.noConflict();
                            $j(document).ready(function () {

                                $j('input[type=radio][name=<?php echo esc_js(ATKP_PRODUCT_POSTTYPE . '_features_mode') ?>]').change(function () {
                                    if (this.value == '0') {
                                        $j('.atkp-write-feature-html').hide();
                                        $j('.atkp-readonly-feature-html').show();
                                    } else if (this.value == '1') {
                                        $j('.atkp-write-feature-html').show();
                                        $j('.atkp-readonly-feature-html').hide();
                                    } else if (this.value == '2') {
                                        $j('.atkp-write-feature-html').hide();
                                        $j('.atkp-readonly-feature-html').hide();
                                    }
                                });

                                var myval = $j('input[type="radio"][name="<?php echo esc_js(ATKP_PRODUCT_POSTTYPE . '_features_mode') ?>"]:checked').val();

                                if (myval == '0') {
                                    $j('.atkp-write-feature-html').hide();
                                    $j('.atkp-readonly-feature-html').show();
                                } else if (myval == '1') {
                                    $j('.atkp-write-feature-html').show();
                                    $j('.atkp-readonly-feature-html').hide();
                                } else if (myval == '2') {
                                    $j('.atkp-write-feature-html').hide();
                                    $j('.atkp-readonly-feature-html').hide();
                                }
                            });


                        </script>
                    </div>

					<?php
					$pl = ( $mainproduct != null ? $mainproduct->features : '' );
					echo '<div class="atkp-readonly-html atkp-readonly-feature-html">' . wp_kses( __( $pl, ATKP_PLUGIN_PREFIX ), array( 'ul' => array(), 'li' => array() ) ) . '</div>';

					ob_start();
					wp_editor( $feat_str, ATKP_PRODUCT_POSTTYPE . '_features', array(
						'media_buttons' => false,
						'textarea_name' => ATKP_PRODUCT_POSTTYPE . '_features',
						'textarea_rows' => 10,
					) );
					$textarea_html = ob_get_clean();

					echo '<div class="atkp-write-feature-html">' . ( $textarea_html) . '</div>';
					?>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="">
	                    <?php echo esc_html__( 'Post', ATKP_PLUGIN_PREFIX ) ?>:
                    </label>
                </th>
                <td>
					<?php
					$postid = ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_postid' );

					if ( $postid != null ) {
						if ( is_array( $postid ) ) {
							foreach ( $postid as $p ) {
								$title = get_the_title( $p );
								if ( ! isset( $title ) || $title == '' ) {
									$title = esc_html__( 'edit post', ATKP_PLUGIN_PREFIX );
								}

								echo sprintf( esc_html__( '<a href="%s" target="_blank">%s</a> ', ATKP_PLUGIN_PREFIX ), esc_url( get_edit_post_link( $p ) ), esc_html__( $title, ATKP_PLUGIN_PREFIX ) );
							}
						} else {
							$title = get_the_title( $postid );
							if ( ! isset( $title ) || $title == '' ) {
								$title = esc_html__( 'edit post', ATKP_PLUGIN_PREFIX );
							}
							echo sprintf( esc_html__( '<a href="%s" target="_blank">%s</a>', ATKP_PLUGIN_PREFIX ), esc_url( get_edit_post_link( $postid ) ), esc_html__( $title, ATKP_PLUGIN_PREFIX ) );
						}
					} else {
						esc_html__( 'This product is not used as a main product in any post.', ATKP_PLUGIN_PREFIX );
					}
					?>
                </td>
            </tr>


			<?php
			do_action( 'atkp_product_detail_after_fields', $post->ID );

			$importProductimage = atkp_options::$loader->get_product_importimage();
			if ( $importProductimage ) {
				?>

                <tr>
                    <th scope="row">

                    </th>
                    <td>
                        <input type="checkbox" id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_dontimportmainimage') ?>"
                               name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_dontimportmainimage') ?>"
                               value="1" <?php echo checked( 1, ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_dontimportmainimage' ), false ); ?>>
                        <label for="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_dontimportmainimage') ?>">
	                        <?php echo esc_html__( 'Do not import the main image of this product', ATKP_PLUGIN_PREFIX ) ?>
                        </label>
                    </td>
                </tr>
			<?php } ?>

            <tr>
                <th scope="row">

                </th>
                <td>
                    <input type="checkbox" id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_outputashtml') ?>"
                           name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_outputashtml') ?>"
                           value="1" <?php echo checked( 1, ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_outputashtml' ), true ); ?>>
                    <label for="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_outputashtml') ?>">
	                    <?php echo esc_html__( 'Output description and features as html. Overwrites substring settings.', ATKP_PLUGIN_PREFIX ) ?>
                    </label>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="">
	                    <?php echo esc_html__( 'Override Price comparison sort', ATKP_PLUGIN_PREFIX ) ?>:
                    </label>
                </th>
                <td>
                    <select id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_sortorder') ?>"
                            name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_sortorder') ?>"
                            style="width:300px">
						<?php
						$selected = ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_sortorder' );
						echo '<option value="" ' . ( $selected == '' ? 'selected' : '' ) . ' >' . esc_html__( 'Global setting', ATKP_PLUGIN_PREFIX ) . '</option>';

						echo '<option value="1" ' . ( $selected == 1 ? 'selected' : '' ) . ' >' . esc_html__( 'Price + Shipping cost', ATKP_PLUGIN_PREFIX ) . '</option>';
						echo '<option value="2" ' . ( $selected == 2 ? 'selected' : '' ) . '>' . esc_html__( 'Price', ATKP_PLUGIN_PREFIX ) . '</option>';
						echo '<option value="3" ' . ( $selected == 3 ? 'selected' : '' ) . '>' . esc_html__( 'Main product and Price', ATKP_PLUGIN_PREFIX ) . '</option>';
						echo '<option value="4" ' . ( $selected == 4 ? 'selected' : '' ) . '>' . esc_html__( 'Main product and Price + Shipping cost', ATKP_PLUGIN_PREFIX ) . '</option>';
						?>
                    </select>
                </td>
            </tr>


        </table>

        <style>
            .atkp-readonly-html {
                margin-left: 0px;
                margin-right: 0px;
                border: 1px solid #bde4ea;
                padding: 10px;
            }
        </style>

		<?php

	}

	function create_clipboard_button( $fieldname ) {
		echo '<a href="javascript:void(0)" data-fieldname="' . esc_html__( $fieldname, ATKP_PLUGIN_PREFIX) . '" class="button copy-clipboard-button"><span class="dashicons dashicons-admin-page atkp-button-icon"></span> </a>';
	}

	function product_tab2( $post ) {
		$mainproduct = $this->atkp_product_collection->get_main_product( '', true );
		?>

        <table class="form-table">

            <tr>
                <th scope="row">
                    <label for="">
	                    <?php echo esc_html__( 'Product page URL', ATKP_PLUGIN_PREFIX ) ?>:
                    </label>
                </th>
                <td>
                    <input type="url" style="width:90%" id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_producturl') ?>"
                           name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_producturl') ?>"
                           placeholder="<?php echo esc_attr_e( $mainproduct != null ? $mainproduct->producturl : '', ATKP_PLUGIN_PREFIX ) ?>"
                           value="<?php echo esc_attr( ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_producturl' ) ); ?>">
	                <?php $this->create_clipboard_button( ATKP_PRODUCT_POSTTYPE . '_producturl' ); ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="">
	                    <?php echo esc_html__( 'Add to cart URL', ATKP_PLUGIN_PREFIX ) ?>:
                    </label>
                </th>
                <td>
                    <input type="url" style="width:90%" id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_addtocarturl') ?>"
                           name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_addtocarturl') ?>"
                           placeholder="<?php echo esc_attr_e( $mainproduct != null ? $mainproduct->addtocarturl : '', ATKP_PLUGIN_PREFIX ) ?>"
                           value="<?php echo esc_attr( ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_addtocarturl' ) ); ?>">
	                <?php $this->create_clipboard_button( ATKP_PRODUCT_POSTTYPE . '_addtocarturl' ); ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="">
	                    <?php echo esc_html__( 'Customer Reviews URL', ATKP_PLUGIN_PREFIX ) ?>:
                    </label>
                </th>
                <td>
                    <input type="url" style="width:90%"
                           id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_customerreviewsurl') ?>"
                           name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_customerreviewsurl') ?>"
                           placeholder="<?php echo esc_attr_e( $mainproduct != null ? $mainproduct->customerreviewurl : '', ATKP_PLUGIN_PREFIX ) ?>"
                           value="<?php echo esc_attr( ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_customerreviewsurl' ) ); ?>">
	                <?php $this->create_clipboard_button( ATKP_PRODUCT_POSTTYPE . '_customerreviewsurl' ); ?>
                </td>
            </tr>


        </table>

		<?php
	}

	function product_tab3( $post ) {
		$mainproduct = $this->atkp_product_collection->get_main_product( '', true );
		?>

        <table class="form-table">


            <tr>
                <th scope="row">
                    <label for="">
	                    <?php echo esc_html__( 'Rating', ATKP_PLUGIN_PREFIX ) ?>:
                    </label>
                </th>
                <td>
                    <input type="number" step="0.01" min="0" max="5"
                           id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_rating') ?>"
                           name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_rating') ?>"
                           placeholder="<?php echo esc_attr_e( $mainproduct != null ? $mainproduct->rating : '', ATKP_PLUGIN_PREFIX ) ?>"
                           value="<?php echo esc_attr( ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_rating' ) ); ?>">
	                <?php $this->create_clipboard_button( ATKP_PRODUCT_POSTTYPE . '_rating' ); ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="">
	                    <?php echo esc_html__( 'Amount of reviews', ATKP_PLUGIN_PREFIX ) ?>:
                    </label>
                </th>
                <td>
                    <input type="number" min="0" id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_reviewcount') ?>"
                           name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_reviewcount') ?>"
                           placeholder="<?php echo esc_attr_e( $mainproduct != null ? $mainproduct->reviewcount : '', ATKP_PLUGIN_PREFIX ) ?>"
                           value="<?php echo esc_html(ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_reviewcount' )); ?>">
	                <?php $this->create_clipboard_button( ATKP_PRODUCT_POSTTYPE . '_reviewcount' ); ?>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="">
	                    <?php echo esc_html__( 'Predicate', ATKP_PLUGIN_PREFIX ) ?>:
                    </label>
                </th>
                <td>

                    <select id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_predicate') ?>"
                            name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_predicate') ?>" style="width:300px">
						<?php
						$selected = ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_predicate' );

						echo '<option value="" ' . ( $selected == '' || $selected == 0 ? 'selected' : '' ) . ' >' . esc_html__( 'None', ATKP_PLUGIN_PREFIX ) . '</option>';

						echo '<option value="1" ' . ( $selected == 1 ? 'selected' : '' ) . '>' . esc_html__( wp_strip_all_tags( atkp_options::$loader->get_predicate1_text() ), ATKP_PLUGIN_PREFIX ) . '</option>';
						echo '<option value="2" ' . ( $selected == 2 ? 'selected' : '' ) . '>' . esc_html__( wp_strip_all_tags( atkp_options::$loader->get_predicate2_text() ), ATKP_PLUGIN_PREFIX ) . '</option>';
						echo '<option value="3" ' . ( $selected == 3 ? 'selected' : '' ) . '>' . esc_html__( wp_strip_all_tags( atkp_options::$loader->get_predicate3_text() ), ATKP_PLUGIN_PREFIX ) . '</option>';

						?>
                    </select>

                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="">
	                    <?php echo esc_html__( 'Test result', ATKP_PLUGIN_PREFIX ) ?>:
                    </label>
                </th>
                <td>

                    <select id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_testresult') ?>"
                            name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_testresult') ?>" style="width:300px">
						<?php
						$selected = ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_testresult' );

						echo '<option value="" ' . ( $selected == '' || $selected == 0 ? 'selected' : '' ) . ' >' . esc_html__( 'None', ATKP_PLUGIN_PREFIX ) . '</option>';

						echo '<option value="1" ' . ( $selected == 1 ? 'selected' : '' ) . '>' . esc_html__( atkp_options::$loader->get_test_score1_text(), ATKP_PLUGIN_PREFIX ) . '</option>';
						echo '<option value="2" ' . ( $selected == 2 ? 'selected' : '' ) . '>' . esc_html__( atkp_options::$loader->get_test_score2_text(), ATKP_PLUGIN_PREFIX ) . '</option>';
						echo '<option value="3" ' . ( $selected == 3 ? 'selected' : '' ) . '>' . esc_html__( atkp_options::$loader->get_test_score3_text(), ATKP_PLUGIN_PREFIX ) . '</option>';
						echo '<option value="4" ' . ( $selected == 4 ? 'selected' : '' ) . '>' . esc_html__( atkp_options::$loader->get_test_score4_text(), ATKP_PLUGIN_PREFIX ) . '</option>';
						echo '<option value="5" ' . ( $selected == 5 ? 'selected' : '' ) . '>' . esc_html__( atkp_options::$loader->get_test_score5_text(), ATKP_PLUGIN_PREFIX ) . '</option>';

						?>
                    </select>

                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="">
	                    <?php echo esc_html__( 'Rating of test', ATKP_PLUGIN_PREFIX ) ?>:
                    </label>
                </th>
                <td>
                    <input type="text" id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_testrating') ?>"
                           name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_testrating') ?>"
                           value="<?php echo esc_attr( ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_testrating' ) ); ?>">
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="">
	                    <?php echo esc_html__( 'Date of test', ATKP_PLUGIN_PREFIX ) ?>:
                    </label>
                </th>
                <td>
                    <input type="text" id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_testdate') ?>"
                           name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_testdate') ?>"
                           value="<?php echo esc_attr( ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_testdate' ) ); ?>">
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="">
	                    <?php echo esc_html__( 'Pro (per line)', ATKP_PLUGIN_PREFIX ) ?>:
                    </label>
                </th>
                <td>
                        <textarea style="width:100%;height:100px" id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_pro') ?>"
                                  name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_pro') ?>"><?php echo esc_textarea( ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_pro' ) ); ?></textarea>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="">
	                    <?php echo esc_html__( 'Contra (per line)', ATKP_PLUGIN_PREFIX ) ?>:
                    </label>
                </th>
                <td>
                        <textarea style="width:100%;height:100px" id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_contra') ?>"
                                  name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_contra') ?>"><?php echo esc_textarea( ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_contra' ) ); ?></textarea>
                </td>
            </tr>

        </table>

		<?php
	}

	function product_tab4( $post ) {
		$mainproduct = $this->atkp_product_collection->get_main_product( '', true );
		?>
        <table class="form-table">

			<?php


			$newimages = atkp_product_image::load_images( $post->ID );


			?>

            <tr>
                <td scope="row">
                    <label for="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_overridemainimage') ?>">
	                    <?php echo esc_html__( 'Override main image', ATKP_PLUGIN_PREFIX ) ?>:
                    </label>

                    <select id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_overridemainimage') ?>"
                            name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_overridemainimage') ?>" style="width:300px">
						<?php
						echo '<option value="">' . esc_html__( 'Default', ATKP_PLUGIN_PREFIX ) . '</option>';

						$selectedmainimage = ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_overridemainimage' );

						$idx = 1;
						foreach ( $newimages as $newimage ) {
							if ( $idx == $selectedmainimage ) {
								$sel = ' selected';
							} else {
								$sel = '';
							}


							echo '<option value="' . esc_attr($idx) . '"' . esc_attr($sel) . ' > ' . sprintf( esc_html__( 'Image %s', ATKP_PLUGIN_PREFIX ), esc_html($idx) ) . '</option>';
							$idx ++;
						}

						?>
                    </select>


                </td>
            </tr>

            <tr>

                <td>

                    <table style="width:100%">

                        <tr class="mainrow">
                            <td style="width:80px;text-align:center;"><?php echo esc_html__( 'Main image', ATKP_PLUGIN_PREFIX ) ?></td>
                            <td style="vertical-align:middle; text-align:center; width:120px">

								<?php
								$imageurl = ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_mediumimageurl' );

								if ( $imageurl == '' ) {
									$imageurl = ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_smallimageurl' );
								}


								if ( $imageurl == '' ) {
									$imageurl = ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_largeimageurl' );
								}

								if ( $imageurl == '' && $mainproduct != null ) {
									$imageurl = $mainproduct->mediumimageurl;
								}
								if ( $imageurl == '' && $mainproduct != null ) {
									$imageurl = $mainproduct->smallimageurl;
								}
								if ( $imageurl == '' && $mainproduct != null ) {
									$imageurl = $mainproduct->largeimageurl;
								}

								?>


                                <img style="max-width:250px" src="<?php echo esc_url($imageurl); ?>"/>

                            </td>
                            <td>
                                <table style="width:100%;margin:1px">
                                    <tr>
                                        <th>
                                            <label for="">
	                                            <?php echo esc_html__( 'Small image URL', ATKP_PLUGIN_PREFIX ) ?>:
                                            </label>
                                        </th>
                                        <td>
                                            <input type="url" style="width:90%"
                                                   id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_smallimageurl') ?>"
                                                   name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_smallimageurl') ?>"
                                                   placeholder="<?php echo esc_attr_e( $mainproduct != null ? $mainproduct->smallimageurl : '', ATKP_PLUGIN_PREFIX ) ?>"
                                                   value="<?php echo esc_attr( ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_smallimageurl' ) ); ?>">
	                                        <?php $this->create_clipboard_button( ATKP_PRODUCT_POSTTYPE . '_smallimageurl' ); ?>
                                        </td>
                                        <td style="    width: 50px;"><input type="button" id="smallimage-button"
                                                                            class="button meta-image-button"
                                                                            value="<?php echo esc_attr_e('Choose or Upload an image', ATKP_PLUGIN_PREFIX ) ?>"/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <label for="">
	                                            <?php echo esc_html__( 'Medium image URL', ATKP_PLUGIN_PREFIX ) ?>:
                                            </label>
                                        </th>
                                        <td>
                                            <input type="url" style="width:90%"
                                                   id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_mediumimageurl') ?>"
                                                   name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_mediumimageurl') ?>"
                                                   placeholder="<?php echo esc_attr_e( $mainproduct != null ? $mainproduct->mediumimageurl : '', ATKP_PLUGIN_PREFIX ) ?>"
                                                   value="<?php echo esc_attr( ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_mediumimageurl' ) ); ?>">
	                                        <?php $this->create_clipboard_button( ATKP_PRODUCT_POSTTYPE . '_mediumimageurl' ); ?>

                                        </td>
                                        <td><input type="button" id="mediumimage-button"
                                                   class="button meta-image-button"
                                                   value="<?php echo esc_attr_e('Choose or Upload an image', ATKP_PLUGIN_PREFIX ) ?>"/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <label for="">
	                                            <?php echo esc_html__( 'Large image URL', ATKP_PLUGIN_PREFIX ) ?>:
                                            </label>
                                        </th>
                                        <td>
                                            <input type="url" style="width:90%"
                                                   id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_largeimageurl') ?>"
                                                   name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_largeimageurl') ?>"
                                                   placeholder="<?php echo esc_attr_e( $mainproduct != null ? $mainproduct->largeimageurl : '', ATKP_PLUGIN_PREFIX ) ?>"
                                                   value="<?php echo esc_attr( ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_largeimageurl' ) ); ?>">
	                                        <?php $this->create_clipboard_button( ATKP_PRODUCT_POSTTYPE . '_largeimageurl' ); ?>

                                        </td>
                                        <td><input type="button" id="largeimage-button"
                                                   class="button meta-image-button"
                                                   value="<?php echo esc_attr_e('Choose or Upload an image', ATKP_PLUGIN_PREFIX ) ?>"/>
                                        </td>
                                    </tr>

                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td style="width:80px;text-align:center;">
	                            <?php echo esc_html__( 'Gallery images', ATKP_PLUGIN_PREFIX ) ?>
                            </td>
                            <td colspan="2">

								<?php
								if ( $mainproduct != null ) {
									foreach ( $mainproduct->images as $img ) {
										echo '<img style="max-width:120px;padding:10px" src="' . esc_url($img->mediumimageurl) . '" />';
									}
								}

								?>

                            </td>
                        </tr>
                    </table>

                </td>
            </tr>


            <tr>

                <td>
                    <input type="button" id="addimage-button" class="button add-image"
                           title="<?php echo esc_attr_e('Add Image', ATKP_PLUGIN_PREFIX ) ?>"
                           value="<?php echo esc_attr_e('Add Image', ATKP_PLUGIN_PREFIX ) ?>"/>

                </td>
            </tr>
            <tr>
                <td>
                    <table style="width:100%;border-collapse:collapse" id="images">
						<?php
						$idx = 1;
						foreach ( $newimages as $newimage ) {

							?>
                            <tr style="border-top:1px solid #bde4ea;" class="mainrow">
                                <td style="width:80px;text-align:center;">
									<?php echo esc_html('#' . $idx); ?>

                                    <input type="button" id="<?php echo esc_attr('removeimage-button_' . $newimage->id) ?>"
                                           class="button remove-image atkp-galleryitem"
                                           value="<?php echo esc_attr_e('Delete', ATKP_PLUGIN_PREFIX ) ?>"/>
                                </td>
                                <td style="vertical-align:middle; text-align:center; width:120px">
                                    <img id="<?php echo esc_attr('image-preview_' . $newimage->id) ?>"
                                         src="<?php echo esc_html__( $newimage->mediumimageurl, ATKP_PLUGIN_PREFIX ); ?>"
                                         style="max-width:250px"/>
                                </td>
                                <td>
                                    <table style="width:100%">
                                        <tr>
                                            <th>
                                                <label for="">
	                                                <?php echo esc_html__( 'Small image URL', ATKP_PLUGIN_PREFIX ) ?>:
                                                </label>
                                            </th>
                                            <td>
                                                <input type="url" class="galleryimage atkp-galleryitem"
                                                       style="width:100%"
                                                       id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_smallimageurl_gallery_' . $newimage->id) ?>"
                                                       name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_smallimageurl_gallery_' . $newimage->id) ?>"
                                                       value="<?php echo esc_html__( $newimage->smallimageurl, ATKP_PLUGIN_PREFIX ); ?>">
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>
                                                <label for="">
	                                                <?php echo esc_html__( 'Medium image URL', ATKP_PLUGIN_PREFIX ) ?>:
                                                </label>
                                            </th>
                                            <td>
                                                <input type="url" class="galleryimage atkp-galleryitem"
                                                       style="width:100%"
                                                       id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_mediumimageurl_gallery_' . $newimage->id) ?>"
                                                       name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_mediumimageurl_gallery_' . $newimage->id) ?>"
                                                       value="<?php echo esc_html__( $newimage->mediumimageurl, ATKP_PLUGIN_PREFIX ); ?>">
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>
                                                <label for="">
	                                                <?php echo esc_html__( 'Large image URL', ATKP_PLUGIN_PREFIX ) ?>:
                                                </label>
                                            </th>
                                            <td>
                                                <input type="url" class="atkp-galleryitem" style="width:100%"
                                                       id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_largeimageurl_gallery_' . $newimage->id) ?>"
                                                       name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_largeimageurl_gallery_' . $newimage->id) ?>"
                                                       value="<?php echo esc_html__( $newimage->largeimageurl, ATKP_PLUGIN_PREFIX ); ?>">
                                            </td>
                                        </tr>

                                    </table>
                                </td>
                            </tr>


							<?php
							$idx ++;
						}
						?>
                        <tr class="mainrow"></tr>
                    </table>

                </td>
            </tr>
        </table>

        <script type="text/javascript">

            function Generator() {
            };
            Generator.prototype.rand = Math.floor(Math.random() * 50000);

            Generator.prototype.getId = function () {
                return this.rand++;
            };
            var idGen = new Generator();
            var mediumimagecaption = '<?php  echo esc_html__( 'Medium image URL', ATKP_PLUGIN_PREFIX ) ?>';
            var smallimagecaption = '<?php  echo esc_html__( 'Small image URL', ATKP_PLUGIN_PREFIX ) ?>';
            var largeimagecaption = '<?php  echo esc_html__( 'Large image URL', ATKP_PLUGIN_PREFIX ) ?>';
            var deletebtncaption = '<?php  echo esc_html__( 'Delete', ATKP_PLUGIN_PREFIX ) ?>';

            var $j = jQuery.noConflict();
            /*
		 * Attaches the add field to the input field
		 */
            $j(document).ready(function ($) {

                // Runs when the image button is clicked.
                $j('#addimage-button').click(function (e) {
                    var id = idGen.getId();

                    $j('#images .mainrow:last').after('<tr style="border-top:1px solid #bde4ea;" class="mainrow"><td style="width:80px;text-align:center;"><input type="button" id="removeimage-button_' + id + '" class="button remove-image atkp-galleryitem" value="' + deletebtncaption + '" /></td><td style="vertical-align:middle; text-align:center; width:120px"></td><td><table style="width:100%"><tr><th><label for="">' + smallimagecaption + ':</label></th><td><input type="url" style="width:100%" class="galleryimage atkp-galleryitem" id="atkp_product_smallimageurl_gallery_' + id + '" name="atkp_product_mediumimageurl_gallery_' + id + '" value=""></td></tr><tr><th><label for="">' + mediumimagecaption + ':</label></th><td><input type="url" style="width:100%" class="galleryimage atkp-galleryitem" id="atkp_product_mediumimageurl_gallery_' + id + '" name="atkp_product_mediumimageurl_gallery_' + id + '" value=""></td></tr><tr><th ><label for="">' + largeimagecaption + ':</label></th><td><input type="url" style="width:100%" id="atkp_product_largeimageurl_gallery_' + id + '" class= "atkp-galleryitem" name="atkp_product_largeimageurl_gallery_' + id + '" value=""></td></tr></table></td></tr>');


                    $j('#removeimage-button_' + id).click(function (e) {

                        if (confirm('<?php echo esc_html__( 'Are you sure?', ATKP_PLUGIN_PREFIX ) ?>')) {
                            $j(this).parent().parent().remove();
                        }

                    });
                });

                $j('.remove-image').click(function (e) {

                    if (confirm('<?php echo esc_html__( 'Are you sure?', ATKP_PLUGIN_PREFIX ) ?>')) {
                        $j(this).parent().parent().remove();
                    }
                });


                $j('.remove-offer').click(function (e) {

                    if (confirm('<?php echo esc_html__( 'Are you sure?', ATKP_PLUGIN_PREFIX ) ?>')) {
                        $j(this).parent().parent().prev().remove();
                        $j(this).parent().parent().prev().remove();
                        $j(this).parent().parent().remove();
                        $j('#atkp_offerschanged').val('1');
                    }
                });

                $j('.copy-clipboard-button').click(function (e) {
                    var name = $j(this).data('fieldname');

                    var element = $j("[name='" + name + "']");

                    var val = element.val();
                    if (val == '')
                        val = element.attr('placeholder');

                    navigator.clipboard.writeText(val).then(function () {
                        console.log('Async: Copying to clipboard was successful!');
                    }, function (err) {
                        console.error('Async: Could not copy text: ', err);
                    });
                });
            });

        </script>
		<?php
	}

	function product_tab5( $post ) {
		$mainproduct = $this->atkp_product_collection->get_main_product( '', true );
		?>

        <table class="form-table">

            <tr>
                <th scope="row">
                    <label for="">
	                    <?php echo esc_html__( 'List price', ATKP_PLUGIN_PREFIX ) ?>:
                    </label>
                </th>
                <td>
                    <input type="text" id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_listprice') ?>"
                           name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_listprice') ?>"
                           placeholder="<?php echo esc_attr_e( $mainproduct != null ? $mainproduct->listprice : '', ATKP_PLUGIN_PREFIX ) ?>"
                           value="<?php echo esc_attr( ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_listprice' ) ); ?>">
	                <?php $this->create_clipboard_button( ATKP_PRODUCT_POSTTYPE . '_listprice' ); ?>

					<?php if ( ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_listprice' ) != '' ) { ?>
                        (<?php echo esc_html__( ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_listpricefloat' ), ATKP_PLUGIN_PREFIX ); ?>)<?php } ?>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="">
	                    <?php echo esc_html__( 'Amount saved', ATKP_PLUGIN_PREFIX ) ?>:
                    </label>
                </th>
                <td>
                    <input type="text" id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_amountsaved') ?>"
                           name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_amountsaved') ?>"
                           placeholder="<?php echo esc_attr_e( $mainproduct != null ? $mainproduct->amountsaved : '', ATKP_PLUGIN_PREFIX ) ?>"
                           value="<?php echo esc_attr( ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_amountsaved' ) ); ?>">
	                <?php $this->create_clipboard_button( ATKP_PRODUCT_POSTTYPE . '_amountsaved' ); ?>

					<?php if ( ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_amountsaved' ) != '' ) { ?>
                        (<?php echo esc_html__( ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_amountsavedfloat' ), ATKP_PLUGIN_PREFIX ); ?>) <?php } ?>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="">
	                    <?php echo esc_html__( 'Percentage saved', ATKP_PLUGIN_PREFIX ) ?>:
                    </label>
                </th>
                <td>
                    <input type="number" min="0" max="100" step="0.01"
                           id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_percentagesaved') ?>"
                           name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_percentagesaved') ?>"
                           placeholder="<?php echo esc_attr_e( $mainproduct != null ? ( $mainproduct->percentagesaved ) : '', ATKP_PLUGIN_PREFIX ) ?>"
                           value="<?php echo esc_attr( ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_percentagesaved' ) ); ?>">
	                <?php $this->create_clipboard_button( ATKP_PRODUCT_POSTTYPE . '_percentagesaved' ); ?>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="">
	                    <?php echo esc_html__( 'Sale price', ATKP_PLUGIN_PREFIX ) ?>:
                    </label>
                </th>
                <td>
                    <input type="text" id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_saleprice') ?>"
                           name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_saleprice') ?>"
                           placeholder="<?php echo esc_attr_e( $mainproduct != null ? $mainproduct->saleprice : '', ATKP_PLUGIN_PREFIX ) ?>"
                           value="<?php echo esc_attr( ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_saleprice' ) ); ?>">
	                <?php $this->create_clipboard_button( ATKP_PRODUCT_POSTTYPE . '_saleprice' ); ?>

					<?php if ( ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_saleprice' ) != '' ) { ?>
                        (<?php echo esc_html__( ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_salepricefloat' ), ATKP_PLUGIN_PREFIX ); ?>) <?php } ?>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="">
	                    <?php echo esc_html__( 'Base price', ATKP_PLUGIN_PREFIX ) ?>:
                    </label>
                </th>
                <td>
                    <input type="text" id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_baseprice') ?>"
                           name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_baseprice') ?>"
                           placeholder="<?php echo esc_attr_e( $mainproduct != null ? $mainproduct->baseprice : '', ATKP_PLUGIN_PREFIX ) ?>"
                           value="<?php echo esc_attr( ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_baseprice' ) ); ?>">
	                <?php $this->create_clipboard_button( ATKP_PRODUCT_POSTTYPE . '_baseprice' ); ?>

					<?php if ( ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_baseprice' ) != '' ) { ?>
                        (<?php echo esc_html__( ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_basepricefloat' ), ATKP_PLUGIN_PREFIX ); ?>) <?php } ?>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="">
	                    <?php echo esc_html__( 'Base unit', ATKP_PLUGIN_PREFIX ) ?>:
                    </label>
                </th>
                <td>
                    <input type="text" id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_baseunit') ?>"
                           name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_baseunit') ?>"
                           placeholder="<?php echo esc_attr_e( $mainproduct != null ? $mainproduct->baseunit : '', ATKP_PLUGIN_PREFIX ) ?>"
                           value="<?php echo esc_attr( ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_baseunit' ) ); ?>">
	                <?php $this->create_clipboard_button( ATKP_PRODUCT_POSTTYPE . '_baseunit' ); ?>

                </td>
            </tr>


            <tr>
                <th scope="row">
                    <label for="">
	                    <?php echo esc_html__( 'Base units', ATKP_PLUGIN_PREFIX ) ?>:
                    </label>
                </th>
                <td>
                    <input type="number" id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_baseunits') ?>"
                           name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_baseunits') ?>"
                           value="<?php echo esc_attr(ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_baseunits' )); ?>">
	                <?php $this->create_clipboard_button( ATKP_PRODUCT_POSTTYPE . '_baseunits' ); ?>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="">
	                    <?php echo esc_html__( 'Availability', ATKP_PLUGIN_PREFIX ) ?>:
                    </label>
                </th>
                <td>
                    <input type="text" style="width:50%" id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_availability') ?>"
                           name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_availability') ?>"
                           placeholder="<?php echo esc_attr_e( $mainproduct != null ? $mainproduct->availability : '', ATKP_PLUGIN_PREFIX ) ?>"
                           value="<?php echo esc_attr( ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_availability' ) ); ?>">
	                <?php $this->create_clipboard_button( ATKP_PRODUCT_POSTTYPE . '_availability' ); ?>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="">
	                    <?php echo esc_html__( 'Shipping', ATKP_PLUGIN_PREFIX ) ?>:
                    </label>
                </th>
                <td>
                    <input type="text" style="width:50%" id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_shipping') ?>"
                           name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_shipping') ?>"
                           placeholder="<?php echo esc_attr_e( $mainproduct != null ? $mainproduct->shipping : '', ATKP_PLUGIN_PREFIX ) ?>"
                           value="<?php echo esc_attr( ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_shipping' ) ); ?>">
	                <?php $this->create_clipboard_button( ATKP_PRODUCT_POSTTYPE . '_shipping' ); ?>

					<?php if ( ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_shipping' ) != '' ) { ?>
                        (<?php echo esc_html__( ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_shippingfloat' ), ATKP_PLUGIN_PREFIX ); ?>)<?php } ?>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="">
	                    <?php echo esc_html__( 'Is prime', ATKP_PLUGIN_PREFIX ) ?>:
                    </label>
                </th>
                <td>
                    <input type="checkbox" id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_isprime') ?>"
                           name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_isprime') ?>"
                           value="1" <?php echo checked( 1, ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_isprime' ), true ); ?>>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="">
	                    <?php echo esc_html__( 'Is warehouse', ATKP_PLUGIN_PREFIX ) ?>:
                    </label>
                </th>
                <td>
                    <input type="checkbox" id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_iswarehouse') ?>"
                           name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_iswarehouse') ?>"
                           value="1" <?php echo checked( 1, ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_iswarehouse' ), true ); ?>>
                </td>
            </tr>

        </table>

		<?php
	}

	function product_tab8( $post ) {
		$mainproduct = $this->atkp_product_collection->get_main_product( '', true );
		?>
        <table class="form-table">

            <tr>

                <th>
					<?php $parentasin = $mainproduct == null ? null : $mainproduct->parentasin; ?>
					<?php
					esc_html__( 'Parent ASIN:', ATKP_PLUGIN_PREFIX );
					?><br/>
	                <?php echo esc_html__( $parentasin, ATKP_PLUGIN_PREFIX ) ?>

                </th>
                <td>
                    <table style="width:100%">
						<?php
						$variations    = $mainproduct == null ? null : $mainproduct->variations; // ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_variations' );
						$variationname = $mainproduct == null ? null : $mainproduct->variationname; // ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_variationname' );


						if ( isset( $variations ) && is_array( $variations ) ) {

							?>
                            <tr>
                                <th><?php
									esc_html__( 'ASIN', ATKP_PLUGIN_PREFIX );
									?> </th>
                                <th><?php
									$displaylist = array();
									if ( is_array( $variationname ) ) {
										foreach ( $variationname as $key => $value ) {
											array_push( $displaylist, htmlentities( $value ) );
										}
									}

	                                echo esc_html__( implode( ' &#8594; ', $displaylist ), ATKP_PLUGIN_PREFIX );
									?> </th>
                                <th><?php
	                                echo esc_html__( 'Product page URL', ATKP_PLUGIN_PREFIX );


									?> </th>
                            </tr> <?php
							foreach ( $variations as $variation ) {
								?>
                                <tr>
                                    <td style="padding:0"><?php
										echo esc_attr($variation->asin);
										?> </td>
                                    <td style="padding:0"><?php

										$displaylist = array();
										foreach ( $variation->variationname as $key => $value ) {
											array_push( $displaylist, htmlentities( $value ) );
										}

	                                    echo esc_html__( implode( ' &#8594; ', $displaylist ), ATKP_PLUGIN_PREFIX );
										?> </td>
                                    <td style="padding:0"><?php
	                                    echo '<a href="' . esc_url( $variation->producturl ) . '" target="blank">' . esc_html__( $variation->title, ATKP_PLUGIN_PREFIX ) . '</a>';


										?> </td>
                                </tr> <?php
							}
						}

						?>
                    </table>
                </td>

            </tr>
            <tr>
                <th scope="row">
                    <label for="">
	                    <?php echo esc_html__( 'Manual Variations', ATKP_PLUGIN_PREFIX ) ?>:
                    </label>
                </th>
                <td>
                    <table style="width:100%">
                        <tr>
                            <th><?php echo esc_html__( 'Name', ATKP_PLUGIN_PREFIX ) ?></th>
                            <th><?php echo esc_html__( 'Product-URL', ATKP_PLUGIN_PREFIX ) ?></th>
                            <th><?php echo esc_html__( 'Small-Image-URL', ATKP_PLUGIN_PREFIX ) ?></th>
                        </tr>


						<?php for ( $x = 0; $x < ATKP_VARIATION_COUNT; $x ++ ) { ?>

                            <tr>
                                <td><input style="width:100%" type="text"
                                           id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_variation_name_' . $x) ?>"
                                           name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_variation_name_' . $x) ?>"
                                           value="<?php echo esc_attr( ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_variation_name_' . $x ) ); ?>">
                                </td>
                                <td><input style="width:100%" type="url"
                                           id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_variation_url_' . $x) ?>"
                                           name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_variation_url_' . $x) ?>"
                                           value="<?php echo esc_attr( ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_variation_url_' . $x ) ); ?>">
                                </td>
                                <td><input style="width:100%" type="url"
                                           id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_variation_imageurl_' . $x) ?>"
                                           name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_variation_imageurl_' . $x) ?>"
                                           value="<?php echo esc_attr( ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_variation_imageurl_' . $x ) ); ?>">
                                </td>
                            </tr>


						<?php } ?>

                    </table>
                </td>
            </tr>
        </table>
		<?php
	}

	function product_tab7( $post ) {
	?>


        <table style="width:100%">
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="">
	                    <?php echo esc_html__( 'Manual Offers', ATKP_PLUGIN_PREFIX ) ?>:
                    </label>
                </th>
                <td>
                    <table style="width:100%">
                        <tr>
                            <th><?php echo esc_html__( 'Shop-Name', ATKP_PLUGIN_PREFIX ) ?></th>
                            <th><?php echo esc_html__( 'Shop-Logo-URL', ATKP_PLUGIN_PREFIX ) ?></th>
                            <th><?php echo esc_html__( 'Price', ATKP_PLUGIN_PREFIX ) ?></th>
                            <th><?php echo esc_html__( 'Shipping', ATKP_PLUGIN_PREFIX ) ?></th>
                            <th><?php echo esc_html__( 'Availability', ATKP_PLUGIN_PREFIX ) ?></th>
                            <th><?php echo esc_html__( 'Product-URL', ATKP_PLUGIN_PREFIX ) ?></th>
                        </tr>


						<?php for ( $x = 0; $x < ATKP_MANUALOFFER_COUNT; $x ++ ) { ?>

                            <tr>
                                <td><input style="width:100%" type="text"
                                           id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_manualoffer_name_' . $x) ?>"
                                           name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_manualoffer_name_' . $x) ?>"
                                           value="<?php echo esc_attr( ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_manualoffer_name_' . $x ) ); ?>">
                                </td>
                                <td><input style="width:100%" type="url"
                                           id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_manualoffer_logo_' . $x) ?>"
                                           name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_manualoffer_logo_' . $x) ?>"
                                           value="<?php echo esc_attr( ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_manualoffer_logo_' . $x ) ); ?>">
                                </td>
                                <td><input style="width:100%" type="text"
                                           id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_manualoffer_price_' . $x) ?>"
                                           name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_manualoffer_price_' . $x) ?>"
                                           value="<?php echo esc_attr( ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_manualoffer_price_' . $x ) ); ?>">
                                </td>
                                <td><input style="width:100%" type="text"
                                           id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_manualoffer_shipping_' . $x) ?>"
                                           name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_manualoffer_shipping_' . $x) ?>"
                                           value="<?php echo esc_attr( ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_manualoffer_shipping_' . $x ) ); ?>">
                                </td>
                                <td><input style="width:100%" type="text"
                                           id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_manualoffer_availability_' . $x) ?>"
                                           name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_manualoffer_availability_' . $x) ?>"
                                           value="<?php echo esc_attr( ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_manualoffer_availability_' . $x ) ); ?>">
                                </td>


                                <td><input style="width:100%" type="url"
                                           id="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_manualoffer_url_' . $x) ?>"
                                           name="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE . '_manualoffer_url_' . $x) ?>"
                                           value="<?php echo esc_attr( ATKPTools::get_post_setting( $post->ID, ATKP_PRODUCT_POSTTYPE . '_manualoffer_url_' . $x ) ); ?>">
                                </td>
                            </tr>


						<?php } ?>

                    </table>
                </td>
            </tr>
        </table>
		<?php
	}


	function atkp_offercompare( $a, $b ) {
		if ( $a == null ) {
			return - 1;
		}
		if ( $b == null ) {
			return 1;
		}

		if ( $a->id == $b->id ) {
			return 0;
		}

		return ( $a->id < $b->id ) ? - 1 : 1;
	}

	function substr_startswith( $haystack, $needle ) {
		return substr( $haystack, 0, strlen( $needle ) ) === $needle;
	}


	function product_detail_save( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		$nounce = ATKPTools::get_post_parameter( 'product_detail_box_content_nonce', 'string' );

		if ( ! wp_verify_nonce( $nounce, plugin_basename( __FILE__ ) ) ) {
			return;
		}


		$post = get_post( $post_id );

		$posttype = $post->post_type; //ATKPTools::get_post_parameter('post_type', 'string');

		if ( ATKP_PRODUCT_POSTTYPE != $posttype ) {
			return;
		}

		$shopid   = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_shopid', 'string' );
		$asin     = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_asin', 'string' );
		$asintype = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_asintype', 'string' );

		ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_asin', $asin );
		ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_asintype', $asintype );
		ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_shopid', $shopid );

		for ( $x = 2; $x < ( ATKP_FILTER_COUNT + 2 ); $x ++ ) {
			$shopid2   = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_shopid_' . $x, 'string' );
			$asin2     = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_asin_' . $x, 'string' );
			$asintype2 = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_asintype_' . $x, 'string' );

			ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_asin_' . $x, $asin2 );
			ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_asintype_' . $x, $asintype2 );
			ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_shopid_' . $x, $shopid2 );
		}


		$ean  = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_ean', 'string' );
		$isbn = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_isbn', 'string' );
		ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_ean', $ean );
		ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_isbn', $isbn );


		$lock_ean  = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_ean_lock', 'bool' );
		$lock_isbn = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_isbn_lock', 'bool' );
		$lock_gtin = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_gtin_lock', 'bool' );

		ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_ean_lock', $lock_ean );
		ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_isbn_lock', $lock_isbn );
		ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_gtin_lock', $lock_gtin );


		$title = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_title', 'string' );

		$description = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_description', 'html' );


		$description_mode = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_description_mode', 'int' );
		$features_mode    = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_features_mode', 'int' );


		$outputashtml = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_outputashtml', 'bool' );


		$sortorder = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_sortorder', 'int' );

		$mpn          = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_mpn', 'string' );
		$brand        = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_brand', 'string' );
		$productgroup = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_productgroup', 'string' );

		$releasedate = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_releasedate', 'string' );


		$producturl        = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_producturl', 'url' );
		$addtocarturl      = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_addtocarturl', 'url' );
		$customerreviewurl = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_customerreviewsurl', 'url' );


		$overridemainimage = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_overridemainimage', 'string' );

		$smallimageurl  = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_smallimageurl', 'url' );
		$mediumimageurl = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_mediumimageurl', 'url' );
		$largeimageurl  = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_largeimageurl', 'url' );

		$manufacturer  = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_manufacturer', 'string' );
		$author        = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_author', 'string' );
		$numberofpages = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_numberofpages', 'int' );
		$features      = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_features', 'html' );

		$isownreview = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_isownreview', 'bool' );

		$rating      = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_rating', 'double' );
		$reviewcount = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_reviewcount', 'int' );
		$reviewsurl  = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_reviewsurl', 'url' );

		$listprice       = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_listprice', 'string' );
		$amountsaved     = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_amountsaved', 'string' );
		$percentagesaved = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_percentagesaved', 'string' );
		$saleprice       = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_saleprice', 'string' );
		$availability    = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_availability', 'string' );
		$shipping        = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_shipping', 'string' );
		$isprime         = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_isprime', 'bool' );
		$iswarehouse     = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_iswarehouse', 'bool' );


		$baseprice = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_baseprice', 'string' );
		$baseunit  = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_baseunit', 'string' );
		$baseunits = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_baseunits', 'int' );


		$predicate  = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_predicate', 'string' );
		$testdate   = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_testdate', 'string' );
		$testrating = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_testrating', 'string' );
		$testresult = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_testresult', 'string' );

		$reviewtext = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_reviewtext', 'string' );

		$pro    = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_pro', 'multistring' );
		$contra = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_contra', 'multistring' );


		$dontimportmainimage = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_dontimportmainimage', 'bool' );

		$hide_shops = array();
		foreach ( $_POST as $x => $xval ) {

			if ( ATKPTools::startsWith( $x, 'atkp_hide_product_' ) ) {
				$str          = str_replace( 'atkp_hide_product_', '', $x );
				$parts        = explode( '_', $str );
				$hide_shops[] = array( 'shop_id' => intval( $parts[0] ), 'product_id' => intval( $parts[1] ) );
			}
		}

		ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_hide_shops', $hide_shops );

		ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_overridemainimage', $overridemainimage );

		ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_dontimportmainimage', $dontimportmainimage );


		ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_pro', $pro );
		ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_contra', $contra );


		ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_predicate', $predicate );
		ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_testdate', $testdate );
		ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_testrating', $testrating );
		ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_testresult', $testresult );

		ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_reviewtext', $reviewtext );


		ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_outputashtml', $outputashtml );
		ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_sortorder', $sortorder );


		ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_description_mode', $description_mode );
		ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_features_mode', $features_mode );

		ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_title', $title );
		ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_description', $description );


		ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_mpn', $mpn );
		ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_brand', $brand );

		ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_productgroup', $productgroup );
		ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_releasedate', $releasedate );


		ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_manufacturer', $manufacturer );
		ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_author', $author );
		ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_numberofpages', $numberofpages );
		ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_features', $features );


		ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_producturl', $producturl );
		ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_addtocarturl', $addtocarturl );
		ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_customerreviewsurl', $customerreviewurl );


		ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_isownreview', $isownreview );
		ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_reviewsurl', $reviewsurl );

		ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_smallimageurl', $smallimageurl );
		ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_mediumimageurl', $mediumimageurl );
		ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_largeimageurl', $largeimageurl );


		ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_rating', $rating );
		ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_reviewcount', $reviewcount );


		$salepricefloat = ATKPTools::price_to_float( $saleprice );
		$listpricefloat = ATKPTools::price_to_float( $listprice );

		$product                 = new atkp_product();
		$product->listpricefloat = $listpricefloat;
		$product->salepricefloat = $salepricefloat;

		if ( $product->listpricefloat > 0 && $product->salepricefloat > 0 ) {

			$productservice = new atkp_productservice();
			$productservice->update_product_price_saved( $post_id, $product, true );
		} else {

			ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_amountsaved', $amountsaved );
			ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_percentagesaved', $percentagesaved );
			ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_amountsavedfloat', ATKPTools::price_to_float( $amountsaved ) );
		}


		ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_baseprice', $baseprice );
		ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_basepricefloat', ATKPTools::price_to_float( $baseprice ) );
		ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_baseunit', $baseunit );
		ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_baseunits', $baseunits );


		ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_listprice', $listprice );
		ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_saleprice', $saleprice );
		ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_availability', $availability );
		ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_shipping', $shipping );
		ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_isprime', $isprime );
		ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_iswarehouse', $iswarehouse );

		ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_listpricefloat', ATKPTools::price_to_float( $listprice ) );
		ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_salepricefloat', $salepricefloat );
		ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_shippingfloat', ATKPTools::price_to_float( $shipping ) );


		do_action( 'atkp_product_save_pricefields', $post_id );


		for ( $x = 0; $x < ATKP_VARIATION_COUNT; $x ++ ) {

			$name     = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_variation_name_' . $x, 'string' );
			$url      = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_variation_url_' . $x, 'string' );
			$imageurl = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_variation_imageurl_' . $x, 'string' );


			ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_variation_name_' . $x, $name );
			ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_variation_url_' . $x, $url );
			ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_variation_imageurl_' . $x, $imageurl );
		}


		for ( $x = 0; $x < ATKP_MANUALOFFER_COUNT; $x ++ ) {

			$name         = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_manualoffer_name_' . $x, 'string' );
			$logo         = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_manualoffer_logo_' . $x, 'url' );
			$price        = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_manualoffer_price_' . $x, 'string' );
			$shipping     = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_manualoffer_shipping_' . $x, 'string' );
			$availability = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_manualoffer_availability_' . $x, 'string' );
			$url          = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_manualoffer_url_' . $x, 'url' );


			ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_manualoffer_name_' . $x, $name );
			ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_manualoffer_url_' . $x, $url );
			ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_manualoffer_logo_' . $x, $logo );
			ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_manualoffer_price_' . $x, $price );
			ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_manualoffer_shipping_' . $x, $shipping );
			ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_manualoffer_availability_' . $x, $availability );
		}

		$images = array();

		//echo serialize($_POST);

		foreach ( $_POST as $key => $value ) {
			$key   = sanitize_text_field( $key );
			$value = sanitize_text_field( $value );

			$id = str_replace( 'atkp_product_largeimageurl_gallery_', '', $key );
			$id = str_replace( 'atkp_product_smallimageurl_gallery_', '', $id );
			$id = str_replace( 'atkp_product_mediumimageurl_gallery_', '', $id );

			$checkit = 0;
			$add     = 1;
			$udf     = new atkp_product_image();
			$udf->id = $id;

			foreach ( $images as $image ) {
				if ( $image->id == $udf->id ) {
					$udf = $image;
					$add = 0;
					break;
				}
			}


			if ( $this->substr_startswith( $key, 'atkp_product_largeimageurl_gallery' ) ) {
				$checkit            = 1;
				$udf->largeimageurl = $value;
			} else if ( $this->substr_startswith( $key, 'atkp_product_smallimageurl_gallery' ) ) {
				$checkit            = 1;
				$udf->smallimageurl = $value;
			} else if ( $this->substr_startswith( $key, 'atkp_product_mediumimageurl_gallery' ) ) {
				$checkit             = 1;
				$udf->mediumimageurl = $value;
			}

			if ( $checkit && $add ) {
				array_push( $images, $udf );
			}
		}

		ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_images', $images );


		do_action( 'atkp_product_save_fields', $post_id );

		if ( ATKPTools::get_post_parameter( 'atkp_product_filter_changed', 'int' ) == 1 ) {

			atkp_queueservices::do_manual_product_update( $post_id, esc_html__( 'Manual product update', ATKP_PLUGIN_PREFIX ) );

		} else {
			$productservice = new atkp_productservice();
			$productservice->update_product_categories( $post_id );
			$productservice->update_product_mainimage( $post_id );
			$productservice->update_product_status( $post_id );

			do_action( 'atkp_product_updated', $post_id, null );
		}

}

}

?>