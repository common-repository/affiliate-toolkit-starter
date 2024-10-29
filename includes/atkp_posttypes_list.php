<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class atkp_posttypes_list {
	/**
	 * Construct the plugin object
	 */
	public function __construct( $pluginbase ) {
		$this->register_listPostType();
		$this->register_filter();

		add_action( 'add_meta_boxes', array( &$this, 'list_boxes' ) );
		add_action( 'save_post', array( &$this, 'list_detail_save' ) );

		ATKPTools::add_column( ATKP_LIST_POSTTYPE, __( 'Status', ATKP_PLUGIN_PREFIX ), function ( $post_id ) {

			$selectedshopid = ATKPTools::get_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_shopid' );

			try {
				if ( $selectedshopid != '' && atkp_shop::exists( $selectedshopid ) ) {
					$shps = atkp_shop::load( $selectedshopid );
				}
			} catch ( Exception $ex ) {
				echo '<span style="color:red">parent shop not found.</span> ';
				$shps = null;
			}

			echo '<span style="font-weight:bold">' . esc_html__( 'ID', ATKP_PLUGIN_PREFIX ) . ':</span> <span >' . esc_html__( $post_id, ATKP_PLUGIN_PREFIX ) . '</span>, ';

			if ( ! isset( $shps ) || $shps == null ) {
				echo '<span>' . esc_html__( 'No shop', ATKP_PLUGIN_PREFIX ) . '</span>';
			} else {
				$shop = '';
				if ( $shps->get_smalllogourl() != '' ) {
					$shop = '<a title="' . esc_attr( $shps->get_title() ) . '" target="_blank" href="' . esc_attr( get_edit_post_link( $shps->id ) ) . '"><img alt="' . esc_attr( $shps->get_title() ) . '" style=";max-height:17px" src="' . ( esc_attr( $shps->get_smalllogourl() ) ) . '" /></a>';
				} else {
					$shop = '<a title="' . esc_attr( $shps->get_title() ) . '" target="_blank" href="' . esc_attr( get_edit_post_link( $shps->id ) ) . '"><span>' . ( esc_attr( $shps->get_title() ) ) . '</span></a>';
				}


				echo '<span style="font-weight:bold">' . esc_html__( 'Shop', ATKP_PLUGIN_PREFIX ) . ':</span> <span>' . 
                    wp_kses( $shop, array( 
                        'a' => array( 'title' => array(), 'target' => array(), 'href' => array() ),
                        'img' => array( 'alt' => array(), 'style' => array(), 'src' => array() )
                    ) ) . 
                    '</span>';
			}


			$updatedon = ATKPTools::get_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_updatedon' );

			if ( isset( $updatedon ) && $updatedon != '' ) {
				$infotext = esc_html__( '%refresh_date% at %refresh_time%', ATKP_PLUGIN_PREFIX );

				$infotext = str_replace( '%refresh_date%', ATKPTools::get_formatted_date( $updatedon ), $infotext );
				$infotext = str_replace( '%refresh_time%', ATKPTools::get_formatted_time( $updatedon ), $infotext );

				echo '<br /><span style="font-weight:bold">' . esc_html__( 'Updated on', ATKP_PLUGIN_PREFIX ) . ':</span> <span>' . esc_html__( $infotext, ATKP_PLUGIN_PREFIX ) . '</span>';
			}

			$selectedsourceval = ATKPTools::get_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_source' );

			$durations = array(
				10 => esc_html__( 'Category - Best Seller', ATKP_PLUGIN_PREFIX ),
				11 => esc_html__( 'Category - New Releases', ATKP_PLUGIN_PREFIX ),
				20 => esc_html__( 'Search', ATKP_PLUGIN_PREFIX ),
				30 => esc_html__( 'Extended Search', ATKP_PLUGIN_PREFIX ),
				//24 => __('Search - Order items by keywords. Rank is determined by the keywords in the product description.', ATKP_PLUGIN_PREFIX),
				//25 => __('Search - Order items by customer reviews, from highest to lowest ranked..', ATKP_PLUGIN_PREFIX),
				//40 => __( 'Similarity - Find similar products', ATKP_PLUGIN_PREFIX ),
			);

			foreach ( $durations as $value => $name ) {
				if ( $value == $selectedsourceval ) {
					echo '<br /><span style="font-weight:bold">' . esc_html__( 'Type', ATKP_PLUGIN_PREFIX ) . ':</span> <span>' . esc_html__( $name, ATKP_PLUGIN_PREFIX ) . '</span>';
					break;
				}
			}

			$message = ATKPTools::get_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_message' );

			if ( isset( $message ) && $message != '' ) {
				echo '<br /><span style="font-weight:bold">' . esc_html__( 'Message', ATKP_PLUGIN_PREFIX ) . ':</span> <span style="color:red">' . esc_html( $message ) . '</span>';
			}

			do_action( 'atkp_list_status_column', $post_id );
		}, 2 );

		ATKPTools::add_column( ATKP_LIST_POSTTYPE, esc_html__( 'Products', ATKP_PLUGIN_PREFIX ), function ( $post_id ) {

			$count = ATKPTools::get_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_count' );

			echo '<span >' . esc_html( $count ) . '</span>';


			do_action( 'atkp_list_count_column', $post_id );
		}, 3 );
	}

	function register_filter() {

		add_filter( 'parse_query', array( &$this, 'admin_posts_filter' ) );
		add_action( 'restrict_manage_posts', array( &$this, 'admin_posts_filter_restrict_manage_posts' ) );

	}

	function admin_posts_filter( $query ) {
		$filterfield = ATKPTools::get_get_parameter( ATKP_PLUGIN_PREFIX . '_filterfield', 'string' );
		$posttype    = ATKPTools::get_get_parameter( 'post_type', 'string' );

		if ( $posttype == ATKP_LIST_POSTTYPE && ! atkp_posttypes_list::$overridefilter ) {
			global $pagenow;
			if ( is_admin() && $pagenow == 'edit.php' && isset( $filterfield ) && $filterfield != '' ) {

				if ( $filterfield == 'filter_error' ) {
					$query->query_vars['meta_key']     = ATKP_LIST_POSTTYPE . '_message';
					$query->query_vars['meta_value']   = '';
					$query->query_vars['meta_compare'] = 'EXISTS';
				} else {
					$parts = explode( '_', $filterfield );

					$query->set( 'meta_query', array(
						array(
							'key'     => ATKP_LIST_POSTTYPE . '_shopid',
							'value'   => isset( $parts[1] ) ? $parts[1] : '',
							'compare' => isset( $parts[1] ) && $parts[1] != '' ? '=' : 'NOT EXISTS'
						)
					) );
				}

			}
		}
	}

	private static $overridefilter;

	function admin_posts_filter_restrict_manage_posts() {
		$posttype = ATKPTools::get_get_parameter( 'post_type', 'string' );

		if ( $posttype != ATKP_LIST_POSTTYPE ) {
			return;
		}

		atkp_posttypes_list::$overridefilter = true;

		$shops = atkp_shop::get_list(  );

		atkp_posttypes_list::$overridefilter = false;
		?>
        <select name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_filterfield') ?>">
            <option value=""><?php echo esc_html__( 'Filter lists by', ATKP_PLUGIN_PREFIX ); ?></option>
			<?php
			//Alle Listen
			//Fehlerhafte Listen
			//Leere Listen
			//Shop: xx
			$filterfield = ATKPTools::get_get_parameter( ATKP_PLUGIN_PREFIX . '_filterfield', 'string' );

			echo '<option value="' . esc_attr( 'filter_error' ) . '" ' . ( $filterfield == 'filter_error' ? 'selected' : '' ) . '>' . esc_html__( 'Lists with error', ATKP_PLUGIN_PREFIX ) . '</option>';
			echo '<option value="' . esc_attr( 'shop_' ) . '" ' . ( $filterfield == 'shop_' ? 'selected' : '' ) . '>' . esc_html__( 'No shop', ATKP_PLUGIN_PREFIX ) . '</option>';

			foreach ( $shops as $shop ) {
				echo '<option ' . ( $shop->type == atkp_shop_type::SUB_SHOPS ? 'disabled' : '' ) . ' value="' . esc_attr( 'shop_' . $shop->id ) . '" ' . ( $filterfield == 'shop_' . $shop->id ? 'selected' : '' ) . '>' . sprintf( esc_html__( 'Shop: %s (%s)', ATKP_PLUGIN_PREFIX ), esc_html__( $shop->title, ATKP_PLUGIN_PREFIX ), esc_html__( $shop->id, ATKP_PLUGIN_PREFIX ) ) . '</option>';


				foreach ( $shop->children as $child ) {

					echo '<option value="' . esc_attr( 'shop_' . $child->id ) . '" ' . ( $filterfield == 'shop_' . $child->id ? 'selected' : '' ) . '>- ' . sprintf( esc_html__( '%s (%s)', ATKP_PLUGIN_PREFIX ), esc_html__( $child->title, ATKP_PLUGIN_PREFIX ), esc_html__( $child->id, ATKP_PLUGIN_PREFIX ) ) . '</option>';

				}

			}
			?>
        </select>
		<?php
	}

	function register_listPostType() {
		$labels = array(
			'name'               => esc_html__( 'Lists', ATKP_PLUGIN_PREFIX ),
			'singular_name'      => esc_html__( 'List', ATKP_PLUGIN_PREFIX ),
			'add_new_item'       => esc_html__( 'Add New List', ATKP_PLUGIN_PREFIX ),
			'edit_item'          => esc_html__( 'Edit List', ATKP_PLUGIN_PREFIX ),
			'new_item'           => esc_html__( 'New List', ATKP_PLUGIN_PREFIX ),
			'all_items'          => esc_html__( 'Lists', ATKP_PLUGIN_PREFIX ),
			'view_item'          => esc_html__( 'View List', ATKP_PLUGIN_PREFIX ),
			'search_items'       => esc_html__( 'Search Lists', ATKP_PLUGIN_PREFIX ),
			'not_found'          => esc_html__( 'No lists found', ATKP_PLUGIN_PREFIX ),
			'not_found_in_trash' => esc_html__( 'No lists found in the Trash', ATKP_PLUGIN_PREFIX ),
			'parent_item_colon'  => '',
			'menu_name'          => esc_html__( 'AT Lists', ATKP_PLUGIN_PREFIX ),
		);
		$args   = array(
			'labels'      => $labels,
			'description' => 'Holds our lists',

			'public'              => false,  // it's not public, it shouldn't have it's own permalink, and so on
			'publicly_queriable'  => true,  // you should be able to query it
			'show_ui'             => true,  // you should be able to edit it in wp-admin
			'exclude_from_search' => true,  // you should exclude it from search results
			'show_in_nav_menus'   => true,  // you shouldn't be able to add it to menus
			'has_archive'         => false,  // it shouldn't have archive page
			'rewrite'             => false,  // it shouldn't have rewrite rules

			'supports' => array( 'title' ),

			'capability_type' => 'post',
			'menu_position'   => 20,
			'menu_icon'       => plugin_dir_url( ATKP_PLUGIN_FILE ) . '/images/affiliate_toolkit_menu.png',
			'show_in_menu'    => ATKP_PLUGIN_PREFIX . '_affiliate_toolkit-plugin',
		);


		$args = apply_filters( 'atkp_list_register_post_type', $args );

		register_post_type( ATKP_LIST_POSTTYPE, $args );
	}

	function list_boxes() {
		add_meta_box(
			ATKP_LIST_POSTTYPE . '_shop_box',
			esc_html__( 'Shop Information', ATKP_PLUGIN_PREFIX ),
			array( &$this, 'list_shop_box_content' ),
			ATKP_LIST_POSTTYPE,
			'normal',
			'default'
		);

		add_meta_box(
			ATKP_LIST_POSTTYPE . '_detail_box',
			esc_html__( 'List Information', ATKP_PLUGIN_PREFIX ),
			array( &$this, 'list_detail_box_content' ),
			ATKP_LIST_POSTTYPE,
			'normal',
			'default'
		);

		add_meta_box(
			ATKP_LIST_POSTTYPE . '_preview_box',
			esc_html__( 'List Preview', ATKP_PLUGIN_PREFIX ),
			array( &$this, 'list_preview_box_content' ),
			ATKP_LIST_POSTTYPE,
			'normal',
			'default'
		);

		add_meta_box(
			ATKP_LIST_POSTTYPE . '_queue_box',
			esc_html__( 'Queue History', ATKP_PLUGIN_PREFIX ),
			array( &$this, 'list_queue_box_content' ),
			ATKP_LIST_POSTTYPE,
			'normal',
			'low'
		);

	}

	function list_queue_box_content( $post ) {
		$atkp_queuetable_helper = new atkp_queuetable_helper();
		if ( ! $atkp_queuetable_helper->exists_table()[0] ) {
			echo 'database table does not exists: ' . esc_html__( $atkp_queuetable_helper->get_producttable_tablename(), ATKP_PLUGIN_PREFIX );

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
								echo esc_html__( $queueid, ATKP_PLUGIN_PREFIX );
							} else {
								$title = esc_html__( 'Queue', ATKP_PLUGIN_PREFIX );

								echo '<a href="' . esc_url( $link ) . '" target="_blank">' . esc_html__( $title, ATKP_PLUGIN_PREFIX ) . ' (' . esc_html__( $queueid, ATKP_PLUGIN_PREFIX ) . ')</a>';
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
								echo esc_html__( $shopid, ATKP_PLUGIN_PREFIX );
							} else {
								$title = get_the_title( $shopid );

								echo '<a href="' . esc_url( $link ) . '" target="_blank">' . esc_html__( $title, ATKP_PLUGIN_PREFIX ) . ' (' . esc_html__( $shopid, ATKP_PLUGIN_PREFIX ) . ')</a>';
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

	function list_shop_box_content( $post ) {
		wp_nonce_field( plugin_basename( __FILE__ ), 'list_shop_box_content_nonce' );


		?>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="">
	                    <?php echo esc_html__( 'Shop', ATKP_PLUGIN_PREFIX ) ?>:
                    </label>
                </th>
                <td>
                    <select id="<?php echo esc_attr(ATKP_LIST_POSTTYPE . '_shopid') ?>"
                            name="<?php echo esc_attr(ATKP_LIST_POSTTYPE . '_shopid') ?>" style="width:300px">
						<?php
						$selectedshopid = ATKPTools::get_post_setting( $post->ID, ATKP_LIST_POSTTYPE . '_shopid' );

						echo '<option value="">' . esc_html__( 'No shop', ATKP_PLUGIN_PREFIX ) . '</option>';

						$shps = atkp_shop::get_list( $selectedshopid );

						foreach ( $shps as $shp ) {
							if ( $shp->selected == true ) {
								$sel = ' selected';
							} else {
								$sel = '';
							}

							if ( $shp->provider == null ) {
								continue;
							}

							$datasources = $shp->provider->get_supportedlistsources();


							if ( $datasources != '' ) {
								echo '<option ' . ( $shp->type == atkp_shop_type::SUB_SHOPS ? 'disabled' : '' ) . ' data-sources="' . esc_html__( $datasources, ATKP_PLUGIN_PREFIX ) . '" value="' . esc_attr( $shp->id ) . '"' . esc_attr( $sel ) . ' > ' . esc_html__( $shp->title, ATKP_PLUGIN_PREFIX ) . '</option>';


								foreach ( $shp->children as $child ) {
									if ( $child->selected == true ) {
										$sel = ' selected';
									} else {
										$sel = '';
									}

									echo '<option data-sources="' . esc_attr( $datasources ) . '" value="' . esc_attr( $child->id ) . '"' . esc_attr( $sel ) . ' >' . esc_html__( $child->title, ATKP_PLUGIN_PREFIX ) . ' [' . esc_html__( $shp->title, ATKP_PLUGIN_PREFIX ) . ']</option>';

								}
							}

						}


						?>
                    </select>

	                <?php ATKPTools::display_helptext( 'You can create you list by using a shop as source (retrieved from the API) or you can create a "hand selected" list from already imported products (select "no shop").' ) ?>
                </td>
            </tr>

			<?php


			$updatedon = ATKPTools::get_post_setting( $post->ID, ATKP_LIST_POSTTYPE . '_updatedon', true );
			$message   = ATKPTools::get_post_setting( $post->ID, ATKP_LIST_POSTTYPE . '_message', true );

			?>

            <tr>

                <td colspan="2"><i>
						<?php
						if ( isset( $updatedon ) && $updatedon != '' ) {
							$infotext = esc_html__( 'List updated on %refresh_date% at %refresh_time%', ATKP_PLUGIN_PREFIX );

							$infotext = str_replace( '%refresh_date%', ATKPTools::get_formatted_date( $updatedon ), $infotext );
							$infotext = str_replace( '%refresh_time%', ATKPTools::get_formatted_time( $updatedon ), $infotext );


							echo esc_html__( $infotext, ATKP_PLUGIN_PREFIX ); ?><br/>
						<?php } else { ?>
                            <span><?php echo esc_html__( 'This list will be added to the next queue.', ATKP_PLUGIN_PREFIX ) ?></span>
						<?php } ?>
						<?php echo '<div style="color:red; overflow-wrap: break-word; max-width: 500px;">' . esc_html( $message ) . '</div>'; ?>
                    </i></td>
            </tr>
        </table>


        <div id="modal-browsenode-lookup" style="display:none;">

            <div class="atkp-lookupbox">
                <p><label for=""><?php echo esc_html__( 'Keyword', ATKP_PLUGIN_PREFIX ) ?>:</label> <input type="text"
                                                                                              id="<?php echo esc_attr(ATKP_LIST_POSTTYPE . '_nodelookupsearch') ?>"
                                                                                              name="<?php echo esc_attr(ATKP_LIST_POSTTYPE . '_nodelookupsearch') ?>"
                                                                                              value=""> <input
                            type="submit" class="button" id="<?php echo esc_attr(ATKP_LIST_POSTTYPE . '_nodelookupbtnsearch') ?>"
                            value="<?php echo esc_html__( 'Search', ATKP_PLUGIN_PREFIX ) ?>">
                <div id="LoadingImageLookup" style="display: none;text-align:center"><img
                            src="<?php echo esc_url(plugin_dir_url( ATKP_PLUGIN_FILE )) ?>/images/spin.gif" style="width:32px"
                            alt="loading"/></div>
                </p>

                <div id="<?php echo esc_attr(ATKP_LIST_POSTTYPE . '_nodelookupresult') ?>">

                </div>


            </div>
        </div>

        <style>
            .atkp-button-icon {
                font-size: 16px;
                height: auto;
                line-height: initial;
                vertical-align: middle;
            }
        </style>

        <script type="text/javascript">
            <?php $searchnounce = wp_create_nonce( 'atkp-search-nonce' ); ?>

            var $j = jQuery.noConflict();
            $j(document).ready(function () {

                $j(document).ready(function () {
                    $j(".pricecomplareentry").hide();
                    $j("#toggle-pricecompare").data('name', 'hide')

                    $j("#toggle-pricecompare").click(function () {
                        if ($j(this).data('name') == 'show') {
                            $j(".pricecomplareentry").hide();
                            $j(this).data('name', 'hide');
                        } else {
                            $j(".pricecomplareentry").show();
                            $j(this).data('name', 'show');
                        }
                    });
                });

                $j("#atkp_btn_prdsearch").click(function (e) {

                    $j("#select-from").empty();
                    $j("#atkp_search-message").hide();

                    $j.ajax({
                        type: "POST",
                        url: "<?php echo esc_js(ATKPTools::get_endpointurl()); ?>",
                        data: {
                            action: "atkp_search_local_products",
                            type: 'atkp_product',
                            keyword: $j('#atkp_txt_manualprdsearch').val(),
                            request_nonce: "<?php echo esc_js($searchnounce); ?>"
                        },

                        dataType: "json",
                        success: function (data) {
                            try {
                                //$j("#atkp_prdresult").html(JSON.stringify(data, null, 2));

                                var count = 0;
                                $j.each(data, function (key, value) {
                                    count++;
                                });

                                if (count > 0) {

                                    if (typeof data[0].error != 'undefined') {
                                        $j("#atkp_search-message").html('<span style="color:red">' + data[0].error + '<br /> ' + data[0].message + '</span>');
                                    } else {
                                        $j("#atkp_search-message").html('<span>'+ count + ' products found</span>');

                                        $j.each(data, function (index, value) {
                                            $j("#select-from").append(new Option(value.title, value.id));

                                        });

                                    }
                                } else {
                                    $j("#atkp_search-message").html('<span><?php echo esc_html__( 'No results', ATKP_PLUGIN_PREFIX ); ?></span>');
                                    $j("#atkp_search-message").show();
                                }
                            } catch (err) {
                                $j("#atkp_search-message").html('<span style="color:red">' + err.message + '</span>');
                                $j("#atkp_search-message").show();
                            }
                        },
                        error: function (xhr, status) {
                            $j("#atkp_search-message").html('<span style="color:red">' + xhr.responseText + '</span>');
                            $j("#atkp_search-message").show();
                        }
                    });
                });

                $j(<?php echo esc_js(ATKP_LIST_POSTTYPE . '_nodelookupbtnsearch') ?>).click(function (e) {

                    $j("#<?php echo esc_js(ATKP_PRODUCT_POSTTYPE. '_nodelookupresult') ?>").html('');
                    $j("#LoadingImageLookup").show();

                    $j.ajax({
                        type: "POST",
                        url: "<?php echo esc_js(ATKPTools::get_endpointurl()); ?>",
                        data: {
                            action: "atkp_search_browsenodes",
                            shop: $j('#<?php echo esc_js(ATKP_LIST_POSTTYPE . '_shopid') ?>').val(),
                            keyword: $j('#<?php echo esc_js(ATKP_LIST_POSTTYPE . '_nodelookupsearch') ?>').val(),
                            request_nonce: "<?php echo esc_js(wp_create_nonce( 'atkp-search-nonce' )) ?>"
                        },

                        dataType: "json",
                        success: function (data) {

                            if (data.length > 0) {
                                if (typeof data[0].error != 'undefined') {
                                    $j("#<?php echo esc_js(ATKP_PRODUCT_POSTTYPE . '_nodelookupresult') ?>").html('<span style="color:red">' + data[0].error + '<br /> ' + data[0].message + '</span>');

                                }
                            } else {
                                var outputresult = '<ul class="node-link">';

                                $j.each(data, function (key, value) {
                                    outputresult += '<li>';
                                    outputresult += '<h3 data-id=' + key + '>' + value + '</h3>';
                                    outputresult += '<p>BrowseNode: ' + key + ' </p>';
                                    outputresult += '</li>';
                                });
                                outputresult += '</ul>';

                                $j("#<?php echo esc_js(ATKP_PRODUCT_POSTTYPE . '_nodelookupresult') ?>").html(outputresult);


                                $j('ul.node-link li h3').click(function (e) {
                                    var id = $j(this).attr("data-id");
                                    $j("#<?php echo esc_js(ATKP_PRODUCT_POSTTYPE . '_node_id') ?>").val(id);
                                    $j("#<?php echo esc_js(ATKP_PRODUCT_POSTTYPE . '_node_id') ?>").trigger('change');
                                    tb_remove();
                                });
                            }
                            $j("#LoadingImageLookup").hide();
                        },
                        error: function (xhr, status) {
                            $j("#<?php echo esc_js(ATKP_PRODUCT_POSTTYPE . '_nodelookupresult') ?>").html('<span style="color:red">' + xhr.responseText + '</span>');
                            $j("#LoadingImageLookup").hide();
                        }
                    });
                });
                $j('#<?php echo esc_js(ATKP_LIST_POSTTYPE . '_node_id') ?>').change(function () {

                    $j('#<?php echo esc_js(ATKP_LIST_POSTTYPE . '_node_caption') ?>').empty();
                });


                var loadeddepartments;
                var loadedfilters;

                $j('#<?php echo esc_js(ATKP_LIST_POSTTYPE . '_shopid') ?>').change(function () {

                    if ($j('#<?php echo esc_js(ATKP_LIST_POSTTYPE . '_shopid') ?>').val() == '') {
                        $j('#settings-1').hide();
                        $j('#settings-2').show();

                        $j('#<?php echo esc_js(ATKP_LIST_POSTTYPE . '_listurl') ?>').prop('disabled', false);
                    } else {
                        $j('#settings-2').hide();
                        $j('#settings-1').show();

                        //

                        var option = $j('option:selected', $j('#<?php echo esc_js(ATKP_LIST_POSTTYPE . '_shopid') ?>')).attr('data-sources');
                        var supportedsources = option.split(",");

                        $j('#<?php echo esc_js(ATKP_LIST_POSTTYPE . '_source') ?> option[value=10]').hide();
                        $j('#<?php echo esc_js(ATKP_LIST_POSTTYPE . '_source') ?> option[value=11]').hide();
                        $j('#<?php echo esc_js(ATKP_LIST_POSTTYPE . '_source') ?> option[value=20]').hide();
                        $j('#<?php echo esc_js(ATKP_LIST_POSTTYPE . '_source') ?> option[value=30]').hide();
                        $j('#<?php echo esc_js(ATKP_LIST_POSTTYPE . '_source') ?> option[value=40]').hide();

                        var selectedval = $j('#<?php echo esc_js(ATKP_LIST_POSTTYPE . '_source') ?>').attr('selected-id');


                        var isset = false;
                        $j.each(supportedsources, function (index, value) {
                            $j('#<?php echo esc_js(ATKP_LIST_POSTTYPE . '_source') ?> option[value=' + value + ']').show();

                            if (selectedval == '') {
                                if (!isset) {
                                    $j('#<?php echo esc_js(ATKP_LIST_POSTTYPE . '_source') ?>').val(value).change();

                                    isset = true;
                                }
                            }
                        });

                        if (selectedval != '')
                            $j('#<?php echo esc_js(ATKP_LIST_POSTTYPE . '_source') ?>').val(selectedval).change();

                        $j('#<?php echo esc_js(ATKP_LIST_POSTTYPE . '_listurl') ?>').prop('disabled', true);

                        //load shop departments
                        $j("#LoadingImage").show();
                        $j("#LoadingImage2").show();
                        loadeddepartments = null;
                        loadedfilters = null;

                        var searchdepbox = $j("#<?php echo esc_js(ATKP_LIST_POSTTYPE . '_search_department') ?>");
                        var searchorderbox = $j("#<?php echo esc_js(ATKP_LIST_POSTTYPE . '_search_orderby') ?>");


                        var selectedvalue = searchdepbox.val();


                        if (selectedvalue == null)
                            selectedvalue = searchdepbox.attr('data-value');

                        searchdepbox.empty();
                        searchorderbox.empty();


                        $j.ajax({
                            type: "POST",
                            url: "<?php echo esc_js(ATKPTools::get_endpointurl()); ?>",
                            data: {
                                action: "atkp_search_departments",
                                shop: $j('#<?php echo esc_js(ATKP_LIST_POSTTYPE . '_shopid') ?>').val(),
                                request_nonce: "<?php echo esc_js(wp_create_nonce( 'atkp-search-nonce' )) ?>"
                            },

                            dataType: "json",
                            success: function (data) {
                                console.log('success');
                                if (data.length > 0) {
                                    if (typeof data[0].error != 'undefined') {
                                        alert(data[0].error + ': ' + data[0].message);
                                    }
                                } else {

                                    $j.each(data, function (key, value) {
                                        searchdepbox.append($j('<option>', {
                                            value: key,
                                            text: value.caption
                                        }));
                                    });


                                    loadeddepartments = data;

                                    searchdepbox.val(selectedvalue);
                                    searchdepbox.trigger("change");
                                }

                                $j("#LoadingImage").hide();
                            },
                            error: function (xhr, status) {
                                console.log('error');
                                console.log(xhr);
                                $j("#LoadingImage").hide();
                            }
                        });


                        $j.ajax({
                            type: "POST",
                            url: "<?php echo esc_js(ATKPTools::get_endpointurl()); ?>",
                            data: {
                                action: "atkp_search_filters",
                                shop: $j('#<?php echo esc_js(ATKP_LIST_POSTTYPE . '_shopid') ?>').val(),
                                request_nonce: "<?php echo esc_js(wp_create_nonce( 'atkp-search-nonce' )) ?>"
                            },

                            dataType: "json",
                            success: function (data) {
                                if (data.length > 0) {
                                    if (typeof data[0].error != 'undefined') {
                                        alert(data[0].error + ': ' + data[0].message);
                                    }
                                } else {


                                    var idx = 1;
                                    while (idx <= 10) {

                                        var searchfilterfield = $j("#<?php echo esc_js(ATKP_LIST_POSTTYPE . '_filterfield') ?>" + idx);
                                        var selectedfiltervalue = searchfilterfield.attr('data-value');

                                        searchfilterfield.empty();

                                        $j.each(data, function (key, value) {


                                            searchfilterfield.append($j('<option>', {
                                                value: key,
                                                text: value
                                            }));
                                        });

                                        searchfilterfield.val(selectedfiltervalue);

                                        idx++;
                                    }


                                    loadedfilters = data;


                                }

                                $j("#LoadingImage").hide();
                                $j("#LoadingImage2").hide();
                            },
                            error: function (xhr, status) {
                                $j("#LoadingImage").hide();
                                $j("#LoadingImage2").hide();
                            }
                        });
                    }

                });

                $j('#<?php echo esc_js(ATKP_LIST_POSTTYPE . '_shopid') ?>').trigger("change");

                $j('#<?php echo esc_js(ATKP_LIST_POSTTYPE . '_search_department') ?>').change(function () {
                    if (loadeddepartments == null)
                        return;

                    var searchdepbox = $j("#<?php echo esc_js(ATKP_LIST_POSTTYPE . '_search_department') ?>");
                    var searchorderbox = $j("#<?php echo esc_js(ATKP_LIST_POSTTYPE . '_search_orderby') ?>");

                    var selectedvalue = searchorderbox.val();

                    if (selectedvalue == null)
                        selectedvalue = searchorderbox.attr('data-value');

                    searchorderbox.empty();

                    searchorderbox.append($j('<option>', {
                        value: '',
                        text: '<?php echo esc_html__( 'no sorting', ATKP_PLUGIN_PREFIX ) ?>'
                    }));


                    $j.each(loadeddepartments, function (key, value) {
                        if (key == searchdepbox.val()) {

                            //alert(JSON.stringify(value), null, 2);

                            if (typeof value.sortvalues !== 'undefined') {
                                $j.each(value.sortvalues, function (key2, value2) {
                                    searchorderbox.append($j('<option>', {
                                        value: key2,
                                        text: value2
                                    }));

                                });
                            }


                        }
                    });

                    searchorderbox.val(selectedvalue);

                    //alert(JSON.stringify(loadeddepartments, null, 2));
                });


                $j('.drop-down-show-hide').hide();
                $j('#div' + $j('#<?php echo esc_js(ATKP_LIST_POSTTYPE . '_source') ?>').val().substring(0, 1)).show();


                $j('#<?php echo esc_js(ATKP_LIST_POSTTYPE . '_source') ?>').change(function () {

                    if ($j('#<?php echo esc_js(ATKP_LIST_POSTTYPE . '_source') ?>').val() == 20) {
                        if ($j('#<?php echo esc_js(ATKP_LIST_POSTTYPE . '_search_department') ?>').val() == '')
                            $j('#<?php echo esc_js(ATKP_LIST_POSTTYPE . '_search_department') ?>').val('All');

                    } else if ($j('#<?php echo esc_js(ATKP_LIST_POSTTYPE . '_source') ?>').val().substring(0, 1) == 2) {
                        if ($j('#<?php echo esc_js(ATKP_LIST_POSTTYPE . '_search_department') ?>').val() == 'All')
                            $j('#<?php echo esc_js(ATKP_LIST_POSTTYPE . '_search_department') ?>').val('');
                    }

                    $j('.drop-down-show-hide').hide()
                    $j('#div' + this.value.substring(0, 1)).show();

                });


                $j('#btn-add').click(function () {
                    $j('#select-from option:selected').each(function () {
                        $j('#<?php echo esc_js(ATKP_LIST_POSTTYPE . '_products') ?>').append("<option value='" + $j(this).val() + "'>" + $j(this).text() + "</option>");
                        $j(this).remove();
                    });
                });
                $j('#btn-remove').click(function () {
                    $j('#<?php echo esc_js(ATKP_LIST_POSTTYPE . '_products') ?> option:selected').each(function () {
                        $j('#select-from').append("<option value='" + $j(this).val() + "'>" + $j(this).text() + "</option>");
                        $j(this).remove();
                    });
                });
                $j('#btn-up').bind('click', function () {
                    $j('#<?php echo esc_js(ATKP_LIST_POSTTYPE . '_products') ?> option:selected').each(function () {
                        var newPos = $j('#<?php echo esc_js(ATKP_LIST_POSTTYPE . '_products') ?> option').index(this) - 1;
                        if (newPos > -1) {
                            $j('#<?php echo esc_js(ATKP_LIST_POSTTYPE . '_products') ?> option').eq(newPos).before("<option value='" + $j(this).val() + "' selected='selected'>" + $j(this).text() + "</option>");
                            $j(this).remove();
                        }
                    });
                });

                jQuery.fn.reverse = function () {
                    return this.pushStack(this.get().reverse(), arguments);
                };

                $j('#btn-down').bind('click', function () {
                    var countOptions = $j('#<?php echo esc_js(ATKP_LIST_POSTTYPE . '_products') ?> option').size();
                    $j('#<?php echo esc_js(ATKP_LIST_POSTTYPE . '_products') ?> option:selected').reverse().each(function () {
                        var newPos = $j('#<?php echo esc_js(ATKP_LIST_POSTTYPE . '_products') ?> option').index(this) + 1;
                        if (newPos < countOptions) {
                            $j('#<?php echo esc_js(ATKP_LIST_POSTTYPE . '_products') ?> option').eq(newPos).after("<option value='" + $j(this).val() + "' selected='selected'>" + $j(this).text() + "</option>");
                            $j(this).remove();
                        }
                    });
                });

                $j("#post").submit(function (event) {
                    $j("#<?php echo (ATKP_LIST_POSTTYPE . '_products') ?> option:selected").removeAttr("selected");

                    $j("#<?php echo (ATKP_LIST_POSTTYPE . '_products') ?> option").prop('selected', true);

                    return true;
                });

                if (typeof $j('#<?php echo esc_js(ATKP_LIST_POSTTYPE . '_shopid') ?>').select2atkp == 'function')
                    $j('#<?php echo esc_js(ATKP_LIST_POSTTYPE . '_shopid') ?>').select2atkp({});

            });
        </script>

		<?php

	}

	function list_detail_box_content( $post ) {
		wp_nonce_field( plugin_basename( __FILE__ ), 'list_detail_box_content_nonce' );


		?>

        <table class="form-table">
            <tr>
                <td colspan="2">

                    <table class="form-table" id="settings-1">
                        <tr>
                            <th scope="row">
                                <label for="">
	                                <?php echo esc_html__( 'Source', ATKP_PLUGIN_PREFIX ) ?>:
                                </label>

                            </th>
                            <td>
								<?php $selectedsourceval = ATKPTools::get_post_setting( $post->ID, ATKP_LIST_POSTTYPE . '_source', 10 ); ?>

                                <select selected-id="<?php echo esc_attr($selectedsourceval) ?>"
                                        name="<?php echo esc_attr(ATKP_LIST_POSTTYPE . '_source') ?>"
                                        id="<?php echo esc_attr(ATKP_LIST_POSTTYPE . '_source') ?>">
									<?php

									$durations = array(
										atkp_list_source_type::BestSeller     => esc_html__( 'Category - Best Seller', ATKP_PLUGIN_PREFIX ),
										atkp_list_source_type::NewReleases    => esc_html__( 'Category - New Releases', ATKP_PLUGIN_PREFIX ),
										atkp_list_source_type::Search         => esc_html__( 'Search', ATKP_PLUGIN_PREFIX ),
										atkp_list_source_type::ExtendedSearch => esc_html__( 'Extended Search', ATKP_PLUGIN_PREFIX ),
									);

									foreach ( $durations as $value => $name ) {
										if ( $value == $selectedsourceval ) {
											$sel = ' selected';
										} else {
											$sel = '';
										}

										$item_translated = '';

										echo '<option value="' . esc_attr( $value ) . '"' . esc_attr( $sel ) . '>' . esc_html__( $name, ATKP_PLUGIN_PREFIX ) . '</option>';
									} ?>
                                </select>
                            </td>
                        </tr>

                        <tr>
                            <td></td>
                            <td>
                                <table id="div1" class="drop-down-show-hide form-table" style="display: none;">
                                    <tr>
                                        <th scope="row">
                                            <label for="">
	                                            <?php echo esc_html__( 'BrowseNode', ATKP_PLUGIN_PREFIX ) ?>:
                                            </label>
                                        </th>
                                        <td>
                                            <input type="number" id="<?php echo esc_attr(ATKP_LIST_POSTTYPE . '_node_id') ?>"
                                                   name="<?php echo esc_attr(ATKP_LIST_POSTTYPE . '_node_id') ?>"
                                                   value="<?php echo esc_attr( ATKPTools::get_post_setting( $post->ID, ATKP_LIST_POSTTYPE . '_node_id', true ) ) ?>">
                                            <label id="<?php echo esc_attr( ATKP_LIST_POSTTYPE . '_node_caption' ) ?>"><?php echo esc_html__( ATKPTools::get_post_setting( $post->ID, ATKP_LIST_POSTTYPE . '_node_caption', true ), ATKP_PLUGIN_PREFIX ); ?></label>
                                            <br/>
                                            <input type="button" id="searchbrowsenode-button"
                                                   class="button browsenode-lookup thickbox"
                                                   title="<?php echo esc_html__( 'Search BrowseNode', ATKP_PLUGIN_PREFIX ) ?>"
                                                   alt="#TB_inline?height=400&amp;width=500&amp;inlineId=modal-browsenode-lookup"
                                                   value="<?php echo esc_html__( 'Search BrowseNode', ATKP_PLUGIN_PREFIX ) ?>"/>
                                        </td>
                                    </tr>

                                </table>
                                <table id="div2" class="drop-down-show-hide form-table" style="display: none;">
                                    <tr>
                                        <th scope="row">
                                            <label for="">
	                                            <?php echo esc_html__( 'Department', ATKP_PLUGIN_PREFIX ) ?>:
                                            </label>
                                        </th>
                                        <td>
                                            <div id="LoadingImage" style="display: none"><img
                                                        src="<?php echo esc_url(plugin_dir_url( ATKP_PLUGIN_FILE )) ?>/images/spin.gif"
                                                        style="width:32px" alt="loading"/></div>
                                            <select style="width: 600px;"
                                                    id="<?php echo esc_attr(ATKP_LIST_POSTTYPE . '_search_department') ?>"
                                                    name="<?php echo esc_attr(ATKP_LIST_POSTTYPE . '_search_department') ?>"
                                                    data-value="<?php echo esc_attr(ATKPTools::get_post_setting( $post->ID, ATKP_LIST_POSTTYPE . '_search_department', true )); ?>">
                                            </select>
	                                        <?php ATKPTools::display_helptext( 'You can only find the root categories of the shop. We are not loading the full category tree.' ) ?>
                                        </td>
                                    </tr>
                                    <th scope="row">
                                        <label for="">
	                                        <?php echo esc_html__( 'Order by ', ATKP_PLUGIN_PREFIX ) ?>:
                                        </label>
                                    </th>
                                    <td>

                                        <select id="<?php echo esc_attr(ATKP_LIST_POSTTYPE . '_search_orderby') ?>"
                                                name="<?php echo esc_attr(ATKP_LIST_POSTTYPE . '_search_orderby') ?>"
                                                data-value="<?php echo esc_attr(ATKPTools::get_post_setting( $post->ID, ATKP_LIST_POSTTYPE . '_search_orderby', true )); ?>">
                                        </select>

                                    </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="">
	                                <?php echo esc_html__( 'Keyword', ATKP_PLUGIN_PREFIX ) ?>:<br/>

                                </label>
                            </th>
                            <td>
                                <input type="text" id="<?php echo esc_attr(ATKP_LIST_POSTTYPE . '_search_keyword') ?>"
                                       name="<?php echo esc_attr(ATKP_LIST_POSTTYPE . '_search_keyword') ?>"
                                       value="<?php echo esc_attr( ATKPTools::get_post_setting( $post->ID, ATKP_LIST_POSTTYPE . '_search_keyword', true ) ); ?>">

                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="">
	                                <?php echo esc_html__( 'Limit', ATKP_PLUGIN_PREFIX ) ?>:<br/>

                                </label>
                            </th>
                            <td>
								<?php

								$searchlimit = ATKPTools::get_post_setting( $post->ID, ATKP_LIST_POSTTYPE . '_search_limit', true );
								if ( $searchlimit == null || $searchlimit == '' ) {
									$searchlimit = 10;
								}

								?>

                                <input type="number" min="1" max="100000"
                                       id="<?php echo esc_attr(ATKP_LIST_POSTTYPE . '_search_limit') ?>"
                                       name="<?php echo esc_attr(ATKP_LIST_POSTTYPE . '_search_limit') ?>"
                                       value="<?php echo esc_attr( $searchlimit ); ?>">

                            </td>
                        </tr>

                    </table>
                    <table id="div3" class="drop-down-show-hide form-table" style="display: none;">

                        <tr>

                            <td colspan="2">
                                <div id="LoadingImage2" style="display: none"><img
                                            src="<?php echo esc_url(plugin_dir_url( ATKP_PLUGIN_FILE )) ?>/images/spin.gif"
                                            style="width:32px" alt="loading"/></div>
								<?php for ( $i = 1; $i <= 10; $i ++ ) { ?>
                                    <select name="<?php echo esc_attr(ATKP_LIST_POSTTYPE . '_filterfield' . $i) ?>"
                                            id="<?php echo esc_attr(ATKP_LIST_POSTTYPE . '_filterfield' . $i) ?>"
                                            data-value="<?php echo esc_attr( ATKPTools::get_post_setting( $post->ID, ATKP_LIST_POSTTYPE . '_filterfield' . $i, true ) ); ?>">

                                    </select>
                                    <input type="text" id="<?php echo esc_attr(ATKP_LIST_POSTTYPE . '_filtertext' . $i) ?>"
                                           name="<?php echo esc_attr(ATKP_LIST_POSTTYPE . '_filtertext' . $i) ?>"
                                           value="<?php echo esc_attr( ATKPTools::get_post_setting( $post->ID, ATKP_LIST_POSTTYPE . '_filtertext' . $i, true ) ); ?>">
                                    <br/>
								<?php } ?>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="">
	                                <?php echo esc_html__( 'Limit', ATKP_PLUGIN_PREFIX ) ?>:<br/>

                                </label>
                            </th>
                            <td>
								<?php
								$extsearchlimit = ATKPTools::get_post_setting( $post->ID, ATKP_LIST_POSTTYPE . '_extendedsearch_limit', true );
								if ( $extsearchlimit == null || $extsearchlimit == '' ) {
									$extsearchlimit = 10;
								}

								?>
                                <input type="number" min="1" max="100000"
                                       id="<?php echo esc_attr(ATKP_LIST_POSTTYPE . '_extendedsearch_limit') ?>"
                                       name="<?php echo esc_attr(ATKP_LIST_POSTTYPE . '_extendedsearch_limit') ?>"
                                       value="<?php echo esc_attr( $extsearchlimit ); ?>">

                            </td>
                        </tr>

                    </table>

                    <table id="div4" class="drop-down-show-hide form-table" style="display: none;">
                        <tr>
                            <th scope="row">
                                <label for="">
	                                <?php echo esc_html__( 'Unique productid', ATKP_PLUGIN_PREFIX ) ?>:
                                </label>
                            </th>
                            <td>
                                <input type="text" id="<?php echo esc_attr(ATKP_LIST_POSTTYPE . '_productid') ?>"
                                       name="<?php echo esc_attr(ATKP_LIST_POSTTYPE . '_productid') ?>"
                                       value="<?php echo esc_attr( ATKPTools::get_post_setting( $post->ID, ATKP_LIST_POSTTYPE . '_productid' ) ); ?>">


                            </td>
                        </tr>

                    </table>
                </td>
            </tr>


            <tr>
                <th scope="row">

                </th>
                <td>
                    <input type="checkbox" id="<?php echo esc_attr(ATKP_LIST_POSTTYPE . '_preferlocalproduct') ?>"
                           name="<?php echo esc_attr(ATKP_LIST_POSTTYPE . '_preferlocalproduct') ?>"
                           value="1" <?php echo checked( 1, ATKPTools::get_post_setting( $post->ID, ATKP_LIST_POSTTYPE . '_source' ) == '' ? true : ATKPTools::get_post_setting( $post->ID, ATKP_LIST_POSTTYPE . '_preferlocalproduct' ), true ); ?>>
                    <label for="<?php echo esc_attr(ATKP_LIST_POSTTYPE . '_preferlocalproduct') ?>">
	                    <?php echo esc_html__( 'Prefer local product information', ATKP_PLUGIN_PREFIX ) ?>
                    </label>
	                <?php ATKPTools::display_helptext( 'The plugin is searching in your local product database (by id) if you imported this product already and will use the local information.' ) ?>
                </td>
            </tr>
            <tr>
                <th scope="row">

                </th>
                <td>
                    <input type="checkbox" id="<?php echo esc_attr(ATKP_LIST_POSTTYPE . '_autoimportproducts') ?>"
                           name="<?php echo esc_attr(ATKP_LIST_POSTTYPE . '_autoimportproducts') ?>"
                           value="1" <?php echo checked( 1, ATKPTools::get_post_setting( $post->ID, ATKP_LIST_POSTTYPE . '_autoimportproducts' ), true ); ?>>
                    <label for="<?php echo esc_attr(ATKP_LIST_POSTTYPE . '_autoimportproducts') ?>">
	                    <?php echo esc_html__( 'Auto import products', ATKP_PLUGIN_PREFIX ) ?>
                    </label>
	                <?php ATKPTools::display_helptext( 'If you enable this option the products from this list will be imported as local product.' ) ?>
                </td>
            </tr>
            <tr>
                <th scope="row">

                </th>
                <td>
                    <input type="checkbox" id="<?php echo esc_attr(ATKP_LIST_POSTTYPE . '_autodeleteproducts') ?>"
                           name="<?php echo esc_attr(ATKP_LIST_POSTTYPE . '_autodeleteproducts') ?>"
                           value="1" <?php echo checked( 1, ATKPTools::get_post_setting( $post->ID, ATKP_LIST_POSTTYPE . '_autodeleteproducts' ), true ); ?>>
                    <label for="<?php echo esc_attr(ATKP_LIST_POSTTYPE . '_autodeleteproducts') ?>">
	                    <?php echo esc_html__( 'Auto delete products', ATKP_PLUGIN_PREFIX ) ?>
                    </label>
	                <?php ATKPTools::display_helptext( 'All products from this list will be deleted in your local product database. Take care: If you enable "Auto import" and "Auto delete" the products will be deleted directly after creation.' ) ?>
                </td>
            </tr>

            <tr>
                <th scope="row">

                </th>
                <td>
                    <input type="checkbox" id="<?php echo esc_attr(ATKP_LIST_POSTTYPE . '_loadmoreoffers') ?>"
                           name="<?php echo esc_attr(ATKP_LIST_POSTTYPE . '_loadmoreoffers') ?>"
                           value="1" <?php echo checked( 1, ATKPTools::get_post_setting( $post->ID, ATKP_LIST_POSTTYPE . '_loadmoreoffers' ) ); ?>>
                    <label for="<?php echo esc_attr(ATKP_LIST_POSTTYPE . '_loadmoreoffers') ?>">
	                    <?php echo esc_html__( 'Load offers from other shops', ATKP_PLUGIN_PREFIX ) ?>
                    </label>
	                <?php ATKPTools::display_helptext( 'If you want to search for offers from other shops you need to activate this option. The price search will be performed in the background.' ) ?>
                </td>
            </tr>
            <tr>
                <th scope="row">

                </th>
                <td>
                    <label for="">
                        <strong>
	                        <?php echo esc_html__( 'Title filter (one keyword per line)', ATKP_PLUGIN_PREFIX ) ?>:<br/>
                        </strong>
                    </label> <br/>
					<?php

					$searchtitelfilter = ATKPTools::get_post_setting( $post->ID, ATKP_LIST_POSTTYPE . '_search_titelfilter', true );

					?>
                    <textarea style="width:100%;height:100px"
                              id="<?php echo esc_attr(ATKP_LIST_POSTTYPE . '_search_titelfilter') ?>"
                              name="<?php echo esc_attr(ATKP_LIST_POSTTYPE . '_search_titelfilter') ?>"><?php echo esc_textarea( $searchtitelfilter ); ?></textarea>
	                <?php ATKPTools::display_helptext( 'For removing entries from your list you can use this field. Add one keyword per line. If you add "<b>keyword</b>" only products including this keyword will be displayed. If you add "<b>-keyword</b>" the plugin remove only products including the "keyword".' ) ?>
                </td>
            </tr>

        </table>

        <table class="form-table" id="settings-2">

            <tr>

                <td style="width:50%;text-align: right;">
                    <div id="from">
                        <div style="width:100%;text-align: left;margin-bottom:10px">
                            <label for=""><?php echo esc_html__( 'Keyword:', ATKP_PLUGIN_PREFIX ) ?></label>
                            <input type="text" id="atkp_txt_manualprdsearch" name="atkp_txt_manualprdsearch" value=""
                                   placeholder="<?php echo esc_html__( 'Your keyword...', ATKP_PLUGIN_PREFIX ) ?>">


                            <a href="#" class="button atkp_searchbutton atkp_prdlookupbtnsearch"
                               id="atkp_btn_prdsearch"><span
                                        class="dashicons dashicons-search atkp-button-icon"></span> <?php echo esc_html__( 'Search', ATKP_PLUGIN_PREFIX ) ?>
                            </a>

                        </div>
                        <div id="atkp_search-message" class="atkp_search-message"
                             style="width:100%;text-align: left;margin-bottom:10px">
	                        <?php ATKPTools::display_helptext( 'This list shows the first 25 products. Please use the search if you cannot find your product below.' ) ?>
                        </div>

						<?php $products = ATKPTools::get_post_setting( $post->ID, ATKP_LIST_POSTTYPE . '_products', true ); ?>
                        <select name="selectfrom" id="select-from" multiple size="18" style="width:100%">

							<?php
							$args        = array(
								'post_type'      => ATKP_PRODUCT_POSTTYPE,
								'posts_per_page' => 25,
								'post_status'    => array( 'publish', 'draft' ),
								'orderby'        => 'ID',
								'order'          => 'desc',
							);
							$posts_array = get_posts( $args );

							$posts_selected   = array();

							foreach ( explode( "\n", $products ) as $productid ) {
								if ( $productid == '' ) {
									continue;
								}

								$prd = get_post( $productid );
								if ( $prd != null ) {
									$option = '<option value="' . esc_attr( $prd->ID ) . '">' . esc_textarea( $prd->post_title . ' (' . $prd->ID . ')' ) . '</option>';
								} else {
									$option = '<option value="' . esc_attr( $productid ) . '">unknown product (' . $productid . ')' . '</option>';
								}

								array_push( $posts_selected, $option );
							}

							$posts_selectable = array();

							foreach ( $posts_array as $prd ) {
								$option = '<option value="' . esc_attr( $prd->ID ) . '">' . ( $prd->post_title == '' ? 'no title' : $prd->post_title ) . ' (' . $prd->ID . ')' . '</option>';
								array_push( $posts_selectable, $option );
							}

							foreach ( $posts_selectable as $prd ) {
								echo(  $prd );
							}
							?>


                        </select>

                    </div>
                    <div id="middle" style="padding-top:10px">
                        <a href="JavaScript:void(0);" id="btn-add" class="button"><span
                                    class="dashicons dashicons-insert atkp-button-icon"></span> <?php echo esc_html__( 'Add', ATKP_PLUGIN_PREFIX ); ?>
                        </a>
                        <a href="JavaScript:void(0);" id="btn-remove" class="button"><span
                                    class="dashicons dashicons-remove atkp-button-icon"></span> <?php echo esc_html__( 'Remove', ATKP_PLUGIN_PREFIX ); ?>
                        </a>
                    </div>
                </td>
                <td style="width:50%;">
                    <div id="to">
                        <select id="<?php echo esc_attr(ATKP_LIST_POSTTYPE . '_products') ?>"
                                name="<?php echo ATKP_LIST_POSTTYPE . '_products[]' ?>" multiple="multiple" size="21"
                                style="width:100%; margin-top:8px">
							<?php
							foreach ( $posts_selected as $prd ) {
								echo $prd;
							}
                            ?>
                        </select>
                    </div>
                    <div id="updown" style="padding-top:10px">
                        <a href="JavaScript:void(0);" id="btn-up" class="button"><span
                                    class="dashicons dashicons-arrow-up-alt2 atkp-button-icon"></span> <?php echo esc_html__( 'Up', ATKP_PLUGIN_PREFIX ); ?>
                        </a>
                        <a href="JavaScript:void(0);" id="btn-down" class="button"><span
                                    class="dashicons dashicons-arrow-down-alt2 atkp-button-icon"></span> <?php echo esc_html__( 'Down', ATKP_PLUGIN_PREFIX ); ?>
                        </a>
                    </div>
                    </fieldset>


                </td>
            </tr>
        </table>

        </td>
        </tr>

        <tr>
            <th scope="row">
                <label for="">
	                <?php echo esc_html__( 'List URL', ATKP_PLUGIN_PREFIX ) ?>:
                </label>
            </th>
            <td>
                <input type="url" style="width:100%" id="<?php echo esc_attr(ATKP_LIST_POSTTYPE . '_listurl') ?>"
                       name="<?php echo esc_attr(ATKP_LIST_POSTTYPE . '_listurl') ?>"
                       value="<?php echo esc_attr( ATKPTools::get_post_setting( $post->ID, ATKP_LIST_POSTTYPE . '_listurl', true ) ); ?>">

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
				$postidx = ATKPTools::get_post_setting( $post->ID, ATKP_LIST_POSTTYPE . '_postid' );

				if ( $postidx != null ) {
					if ( is_array( $postidx ) ) {
						foreach ( $postidx as $p ) {
							$title = get_the_title( $p );
							if ( ! isset( $title ) || $title == '' ) {
								$title = esc_html__( 'edit post', ATKP_PLUGIN_PREFIX );
							}

							echo sprintf( esc_html__( '<a href="%s" target="_blank">%s</a> ', ATKP_PLUGIN_PREFIX ), esc_url(get_edit_post_link( $p )), esc_html($title) );
						}
					} else {
						$title = get_the_title( $postidx );
						if ( ! isset( $title ) || $title == '' ) {
							$title = esc_html__( 'edit post', ATKP_PLUGIN_PREFIX );
						}
						echo sprintf( esc_html__( '<a href="%s" target="_blank">%s</a>', ATKP_PLUGIN_PREFIX ), esc_url(get_edit_post_link( $postidx )), esc_html($title) );
					}
				} else {
					echo esc_html__( 'This List is not used as a main list in any contribution.', ATKP_PLUGIN_PREFIX );
				}
				?>
            </td>
        </tr>

		<?php do_action( 'atkp_list_after_fields', $post->ID ); ?>

        </table>


		<?php
	}

	function list_preview_box_content( $post ) {
		$atkp_listtable_helper = new atkp_listtable_helper();
		if ( ! $atkp_listtable_helper->exists_table()[0] ) {
			echo esc_html__( 'database table does not exists: ' . $atkp_listtable_helper->get_listtable_tablename(), ATKP_PLUGIN_PREFIX );

			return;
		}

		$selectedshopid = ATKPTools::get_post_setting( $post->ID, ATKP_LIST_POSTTYPE . '_shopid' );

		$productlist = $atkp_listtable_helper->load_list( $post->ID, $selectedshopid );//ATKPTools::get_post_setting( $post->ID, ATKP_LIST_POSTTYPE . '_productlist' );

		// echo("$productlist: ".serialize($productlist));

		$preferlocalproductinfo = ATKPTools::get_post_setting( $post->ID, ATKP_LIST_POSTTYPE . '_preferlocalproduct' );

		if ( $productlist != null ) {

			?>



            <table style="width:100%;border-collapse:collapse" id="prices" width="100%">
            <tr>
                <td style="width:40%">

                    <b><?php echo esc_html__( 'Title', ATKP_PLUGIN_PREFIX ) ?></b>


                </td>


            </tr>

			<?php

			$counter = 1;
			$shps    = atkp_shop::get_list(  );
			foreach ( $productlist as $product ) {
				try {
					$type  = $product['type'];
					$value = $product['value'];

					if ( $value == '' ) {
						continue;
					}

					switch ( $type ) {
						case 'product':
							if ( $preferlocalproductinfo ) {

								$prd_found = atkp_product::loadbyasin( $value->asin );


								if ( $prd_found != '' ) {
									$value = $prd_found;
								}
							}

							break;
						case 'productid':
							$prodcollection = atkp_product_collection::load( $value );
							if ( $prodcollection != null ) {
								$value = $prodcollection->get_main_product();
							}
							break;
					}

					if ( $value == '' ) {
						continue;
					}

					$prdid = $value->productid;
					if ( $prdid == '' ) {
						$prdid = '-';
					}


					?>
                    <tr>
                        <td> <?php

							if ( $value->producturl != '' ) {
								echo sprintf( '%s <a href="%s" target="_blank">%s</a>',  esc_html($counter), esc_url( $value->producturl ), esc_html__( substr( $value->title, 0, 180 ), ATKP_PLUGIN_PREFIX ) );
							} else {
                                echo sprintf( '%s %s', esc_html($counter), esc_html__( substr( $value->title, 0, 180 ), ATKP_PLUGIN_PREFIX ) );
							}

							echo sprintf( ' (Unique-ID: %s, Product-ID: %s)<br />', esc_html($value->asin), ( $value->productid > 0 ? '<a href="' . esc_url(get_edit_post_link( $value->productid )) . '" target="_blank">' . esc_html($value->productid) . '</a>' : esc_html($value->productid) ) );

							?></td>
                    </tr> <?php


					$counter = $counter + 1;
				} catch ( Exception $e ) {
					//TODO: 'Exception: ',  $e->getMessage(), "\n";
				}
			}


			?> </table><?php
		}
	}

	function list_detail_save( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		$nounce = ATKPTools::get_post_parameter( 'list_detail_box_content_nonce', 'string' );

		if ( ! wp_verify_nonce( $nounce, plugin_basename( __FILE__ ) ) ) {
			return;
		}


		$post = get_post( $post_id );

		$posttype = $post->post_type; //ATKPTools::get_post_parameter('post_type', 'string');

		if ( ATKP_LIST_POSTTYPE != $posttype ) {
			return;
		}

		$shopid = ATKPTools::get_post_parameter( ATKP_LIST_POSTTYPE . '_shopid', 'string' );

		$source = ATKPTools::get_post_parameter( ATKP_LIST_POSTTYPE . '_source', 'string' );
		$nodeid = ATKPTools::get_post_parameter( ATKP_LIST_POSTTYPE . '_node_id', 'string' );

		$searchdepartment = ATKPTools::get_post_parameter( ATKP_LIST_POSTTYPE . '_search_department', 'string' );
		$searchkeyword    = ATKPTools::get_post_parameter( ATKP_LIST_POSTTYPE . '_search_keyword', 'string' );
		$searchorderby    = ATKPTools::get_post_parameter( ATKP_LIST_POSTTYPE . '_search_orderby', 'string' );

		$autoimportproducts = ATKPTools::get_post_parameter( ATKP_LIST_POSTTYPE . '_autoimportproducts', 'bool' );
		$autodeleteproducts = ATKPTools::get_post_parameter( ATKP_LIST_POSTTYPE . '_autodeleteproducts', 'bool' );

		if($autodeleteproducts)
			$autoimportproducts = false;

		$preferlocalproduct = ATKPTools::get_post_parameter( ATKP_LIST_POSTTYPE . '_preferlocalproduct', 'bool' );
		$loadmoreoffers     = ATKPTools::get_post_parameter( ATKP_LIST_POSTTYPE . '_loadmoreoffers', 'bool' );

		$productid = ATKPTools::get_post_parameter( ATKP_LIST_POSTTYPE . '_productid', 'string' );
		$listurl   = ATKPTools::get_post_parameter( ATKP_LIST_POSTTYPE . '_listurl', 'url' );

		$extsearchlimit = ATKPTools::get_post_parameter( ATKP_LIST_POSTTYPE . '_extendedsearch_limit', 'int' );
		$searchlimit    = ATKPTools::get_post_parameter( ATKP_LIST_POSTTYPE . '_search_limit', 'int' );


		$searchtitelfilter = implode( "\n", array_map( 'sanitize_text_field', explode( "\n", ATKPTools::get_post_parameter( ATKP_LIST_POSTTYPE . '_search_titelfilter', 'multistring' ) ) ) );


		$filterfield1 = ATKPTools::get_post_parameter( ATKP_LIST_POSTTYPE . '_filterfield1', 'string' );
		$filtertext1  = ATKPTools::get_post_parameter( ATKP_LIST_POSTTYPE . '_filtertext1', 'string' );
		$filterfield2 = ATKPTools::get_post_parameter( ATKP_LIST_POSTTYPE . '_filterfield2', 'string' );
		$filtertext2  = ATKPTools::get_post_parameter( ATKP_LIST_POSTTYPE . '_filtertext2', 'string' );
		$filterfield3 = ATKPTools::get_post_parameter( ATKP_LIST_POSTTYPE . '_filterfield3', 'string' );
		$filtertext3  = ATKPTools::get_post_parameter( ATKP_LIST_POSTTYPE . '_filtertext3', 'string' );
		$filterfield4 = ATKPTools::get_post_parameter( ATKP_LIST_POSTTYPE . '_filterfield4', 'string' );
		$filtertext4  = ATKPTools::get_post_parameter( ATKP_LIST_POSTTYPE . '_filtertext4', 'string' );
		$filterfield5 = ATKPTools::get_post_parameter( ATKP_LIST_POSTTYPE . '_filterfield5', 'string' );
		$filtertext5  = ATKPTools::get_post_parameter( ATKP_LIST_POSTTYPE . '_filtertext5', 'string' );

		$filterfield6  = ATKPTools::get_post_parameter( ATKP_LIST_POSTTYPE . '_filterfield6', 'string' );
		$filtertext6   = ATKPTools::get_post_parameter( ATKP_LIST_POSTTYPE . '_filtertext6', 'string' );
		$filterfield7  = ATKPTools::get_post_parameter( ATKP_LIST_POSTTYPE . '_filterfield7', 'string' );
		$filtertext7   = ATKPTools::get_post_parameter( ATKP_LIST_POSTTYPE . '_filtertext7', 'string' );
		$filterfield8  = ATKPTools::get_post_parameter( ATKP_LIST_POSTTYPE . '_filterfield8', 'string' );
		$filtertext8   = ATKPTools::get_post_parameter( ATKP_LIST_POSTTYPE . '_filtertext8', 'string' );
		$filterfield9  = ATKPTools::get_post_parameter( ATKP_LIST_POSTTYPE . '_filterfield9', 'string' );
		$filtertext9   = ATKPTools::get_post_parameter( ATKP_LIST_POSTTYPE . '_filtertext9', 'string' );
		$filterfield10 = ATKPTools::get_post_parameter( ATKP_LIST_POSTTYPE . '_filterfield10', 'string' );
		$filtertext10  = ATKPTools::get_post_parameter( ATKP_LIST_POSTTYPE . '_filtertext10', 'string' );

		$products = '';

		$productpara = isset( $_POST[ ATKP_LIST_POSTTYPE . '_products' ] ) ? $_POST[ ATKP_LIST_POSTTYPE . '_products' ] : null;

		if ($productpara != null ) {
			foreach ( $productpara as $selectedproduct ) {
				if ( $products == '' ) {
					$products = $selectedproduct;
				} else {
					$products .= "\n" . $selectedproduct;
				}
			}
		}


		ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_shopid', $shopid );

		if ( $shopid == '' ) {

			ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_listurl', $listurl );
			ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_products', $products );
		} else {

			ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_source', $source );


			ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_autoimportproducts', $autoimportproducts );
			ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_autodeleteproducts', $autodeleteproducts );


			ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_preferlocalproduct', $preferlocalproduct );
			ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_loadmoreoffers', $loadmoreoffers );

			ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_search_department', $searchdepartment );
			ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_search_keyword', $searchkeyword );
			ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_search_orderby', $searchorderby );

			ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_node_id', $nodeid );
			//ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE.'_keyword', $keyword);
			ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_productid', $productid );

			ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_extendedsearch_limit', $extsearchlimit );
			ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_search_limit', $searchlimit );

			ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_search_titelfilter', $searchtitelfilter );

			ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_filterfield1', $filterfield1 );
			ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_filtertext1', $filtertext1 );
			ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_filterfield2', $filterfield2 );
			ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_filtertext2', $filtertext2 );
			ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_filterfield3', $filterfield3 );
			ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_filtertext3', $filtertext3 );
			ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_filterfield4', $filterfield4 );
			ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_filtertext4', $filtertext4 );
			ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_filterfield5', $filterfield5 );
			ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_filtertext5', $filtertext5 );

			ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_filterfield6', $filterfield6 );
			ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_filtertext6', $filtertext6 );
			ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_filterfield7', $filterfield7 );
			ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_filtertext7', $filtertext7 );
			ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_filterfield8', $filterfield8 );
			ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_filtertext8', $filtertext8 );
			ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_filterfield9', $filterfield9 );
			ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_filtertext9', $filtertext9 );
			ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_filterfield10', $filterfield10 );
			ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_filtertext10', $filtertext10 );
		}


		do_action( 'atkp_list_save_fields', $post_id );

		//wenn die Extension nicht geladen ist, kann das Plugin nicht arbeiten
		//Wenn keine Einstellungen definiert wurden um Daten zu laden, keine Liste generieren

		atkp_queueservices::do_manual_list_update( $post_id, esc_html__( 'Manual list update', ATKP_PLUGIN_PREFIX ) );
    }

}

?>