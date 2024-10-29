<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class atkp_bulkimport {
	public static $settings;

	/**
	 * Construct the plugin object
	 */
	public function __construct( $pluginbase ) {
		add_action( 'atkp_register_submenu', array( &$this, 'admin_menu' ), 12, 1 );
	}

	function admin_menu( $parentmenu ) {

		add_submenu_page(
			$parentmenu,
			esc_html__( 'Product import', ATKP_PLUGIN_PREFIX ),
			esc_html__( 'Product import', ATKP_PLUGIN_PREFIX ),
			'edit_posts',
			'atkp_bulkimport',
			array( &$this, 'toolkit_bulkimport' )
		);

	}

    private 	$list = array();

	public function toolkit_bulkimport() {
		if ( ! is_user_logged_in() ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page', ATKP_PLUGIN_PREFIX ) );
		}


		$selectedshopid = '';
		$keyword        = '';
		$page           = ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_page', 'int' );;
		$searched     = false;
		$searchoption = 'keyword';

		$asintype = ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_asintype', 'string' );;

		if ( ATKPTools::exists_post_parameter( 'nextpage' ) ) {
			unset( $_POST['searchbulk'] );
			$page ++;
		}


		if ( ATKPTools::exists_post_parameter( 'lastpage' ) ) {
			unset( $_POST['searchbulk'] );
			$page --;
		}

		if ( $page < 0 || ATKPTools::exists_post_parameter( 'searchbulk' ) ) {
			$page = 1;
		}
//bulkimport

		if ( ATKPTools::exists_post_parameter( 'searchbulk' ) || ATKPTools::exists_post_parameter( 'nextpage' ) || ATKPTools::exists_post_parameter( 'lastpage' ) ) {
			$searched = true;
		}

		if ( $searched ) {
			check_admin_referer( 'bulkimport', 'bulkimport' );

			if ( ! is_user_logged_in() ) {
				wp_die( esc_html__( 'You do not have sufficient permissions to access this page', ATKP_PLUGIN_PREFIX ) );
			}

			//wenn die seiten durchgeblättert werden dann wurde keine neue suche ausgeführt
			if ( ATKPTools::exists_post_parameter( 'nextpage' ) || ATKPTools::exists_post_parameter( 'lastpage' ) ) {
				$keyword        = ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_keyword_search', 'string' );
				$selectedshopid = ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_shop_search', 'string' );
				$searchoption   = ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_option_search', 'string' );
			} else {
				$selectedshopid = ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_shopid', 'string' );
				$keyword        = ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_keyword', 'string' );
				$searchoption   = ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_searchoption', 'string' );
			}

		}
		?>

        <form method="POST" action="?page=atkp_bulkimport" id="atkp_searchform">
			<?php wp_nonce_field( "bulkimport", "bulkimport" ); ?>
            <div class="atkp-content wrap" style="margin-bottom:30px;float:none !important">
                <h1 class="wp-heading-inline"><?php echo esc_html__( 'Product import', ATKP_PLUGIN_PREFIX ); ?></h1>

                <div class="inner metabox-holder ">

                    <div id="postbox-container-2" class="postbox-container" style="float:none">
                        <div id="normal-sortables" class="meta-box-sortables ui-sortable">
                            <div id="atkp_product_shop_box" class="postbox ">
                                <div class="postbox-header"><h2
                                            class="hndle ui-sortable-handle"><?php echo esc_html__( 'Filter Information', ATKP_PLUGIN_PREFIX ); ?></h2>
                                </div>
                                <div class="inside">

                                    <table class="form-table">
                                        <tr>
                                            <th scope="row">
                                                <label for="">
	                                                <?php echo esc_html__( 'Shop', ATKP_PLUGIN_PREFIX ) ?>:
                                                </label>
                                            </th>
                                            <td>
                                                <select id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_shopid') ?>"
                                                        name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_shopid') ?>"
                                                        style="width:300px">
													<?php

													echo '<option value=""> ' . esc_html__( 'All Shops', ATKP_PLUGIN_PREFIX ) . '</option>';

													$shps = atkp_shop::get_list( $selectedshopid );

													foreach ( $shps as $shp ) {

														$support_articlenumber = apply_filters( 'atkp_shop_support_articlenumber_search', false, $shp );
														$support_isbn          = apply_filters( 'atkp_shop_support_isbn_search', false, $shp );
														$support_gtin          = apply_filters( 'atkp_shop_support_gtin_search', false, $shp );
														$support_ean = apply_filters( 'atkp_shop_support_ean_search', true, $shp );

														if ( $shp->selected == true ) {
															$sel = ' selected';
														} else {
															$sel = '';
														}

														echo '<option ' . ( $shp->type == atkp_shop_type::SUB_SHOPS ? 'disabled' : '' ) . ' data-gtin="' . ( $support_gtin ? 'true' : 'false' ) . '" data-ean="' . ( $support_ean ? 'true' : 'false' ) . '" data-isbn="' . ( $support_isbn ? 'true' : 'false' ) . '" data-article_number="' . ( $support_articlenumber ? 'true' : 'false' ) . '" value="' . esc_attr( $shp->id ) . '"' . esc_attr( $sel ) . ' > ' . esc_html__( $shp->title, ATKP_PLUGIN_PREFIX ) . '</option>';

														foreach ( $shp->children as $child ) {
															if ( $child->selected == true ) {
																$sel = ' selected';
															} else {
																$sel = '';
															}

															echo '<option data-gtin="' . ( $support_gtin ? 'true' : 'false' ) . '" data-ean="' . ( $support_ean ? 'true' : 'false' ) . '" data-isbn="' . ( $support_isbn ? 'true' : 'false' ) . '" data-article_number="' . ( $support_articlenumber ? 'true' : 'false' ) . '" value="' . esc_attr( $child->id ) . '"' . esc_attr( $sel ) . ' >' . esc_html__( $child->title, ATKP_PLUGIN_PREFIX ) . ' [' . esc_html__( $shp->title, ATKP_PLUGIN_PREFIX ) . ']</option>';

														}
													}

													?>
                                                </select>


                                            </td>
                                        </tr>


                                        <tr>
                                            <th scope="row">
                                                <label id="searchcaption" for="">
	                                                <?php echo esc_html__( 'Search by', ATKP_PLUGIN_PREFIX ) ?>:
                                                </label>
                                            </th>
                                            <td>

                                                <input type="radio" id="searchoption1" data-name="Keyword"
                                                       name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX) ?>_searchoption" <?php if ( $searchoption == 'keyword' ) {
													echo 'checked="checked"';
												} ?> value="keyword"/> <label
                                                        for="searchoption1"><?php echo esc_html__( 'Keyword', ATKP_PLUGIN_PREFIX ) ?></label>
                                                <input type="radio" id="searchoption2" data-name="ASIN"
                                                       name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX) ?>_searchoption" <?php if ( $searchoption == 'asin' ) {
													echo 'checked="checked"';
												} ?> value="asin"/> <label
                                                        for="searchoption2"><?php echo esc_html__( 'ASIN(s)', ATKP_PLUGIN_PREFIX ) ?></label>
                                                <input type="radio" id="searchoption3" data-name="EAN"
                                                       name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX) ?>_searchoption" <?php if ( $searchoption == 'ean' ) {
													echo 'checked="checked"';
												} ?> value="ean"/> <label
                                                        for="searchoption3"><?php echo esc_html__( 'EAN(s)', ATKP_PLUGIN_PREFIX ) ?></label>
                                                <input type="radio" id="searchoption4" data-name="Articlenumber"
                                                       name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX) ?>_searchoption" <?php if ( $searchoption == 'articlenumber' ) {
													echo 'checked="checked"';
												} ?> value="articlenumber"/> <label
                                                        for="searchoption4"><?php echo esc_html__( 'Articlenumber', ATKP_PLUGIN_PREFIX ) ?></label>

                                                <input type="radio" id="searchoption5" data-name="GTIN"
                                                       name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX) ?>_searchoption" <?php if ( $searchoption == 'gtin' ) {
													echo 'checked="checked"';
												} ?> value="gtin"/> <label
                                                        for="searchoption5"><?php echo esc_html__( 'GTIN', ATKP_PLUGIN_PREFIX ) ?></label>

                                                <input type="radio" id="searchoption6" data-name="ISBN"
                                                       name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX) ?>_searchoption" <?php if ( $searchoption == 'isbn' ) {
													echo 'checked="checked"';
												} ?> value="isbn"/> <label
                                                        for="searchoption6"><?php echo esc_html__( 'ISBN', ATKP_PLUGIN_PREFIX ) ?></label>
                                            </td>
                                        </tr>

                                        <tr>
                                            <th scope="row">
                                                <label id="keywordcaption" for="keyword">
	                                                <?php echo esc_html__( 'Keyword', ATKP_PLUGIN_PREFIX ) ?>
                                                </label>
                                            </th>
                                            <td>
                                                <input required type="text" style="width:300px" id="keyword"
                                                       name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX) ?>_keyword"
                                                       value="<?php echo esc_attr( $keyword ) ?>">
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row">
                                                <label id="searchcaption" for="">
	                                                <?php echo esc_html__( 'Product filter key', ATKP_PLUGIN_PREFIX ) ?>
                                                    :
                                                </label>
                                            </th>
                                            <td>
                                                <select name="<?php echo esc_attr( ATKP_PLUGIN_PREFIX . '_asintype' ) ?>"
                                                        id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_asintype') ?>"
                                                        style="width:150px">
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

														echo '<option value="' . esc_attr( $value ) . '"' . esc_attr( $sel ) . '>' . esc_html__( $name, ATKP_PLUGIN_PREFIX ) . '</option>';
													} ?>
                                                </select>
												<?php ATKPTools::display_helptext( 'This key will be used to create a link for product updates. You will find this key after product import in the tab "filter information".' ) ?>

                                            </td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td>
                                                <input type="hidden" name="searchbulk"
                                                       value="<?php esc_attr_e( 'Search', ATKP_PLUGIN_PREFIX ) ?>"/>

                                                <a href="#" class="button primary-button"
                                                   onclick="this.closest('form').submit();return false;"
                                                   id="searchproduct-button"><span
                                                            class="dashicons dashicons-search atkp-button-icon"></span> <?php echo esc_html__( 'Search', ATKP_PLUGIN_PREFIX ) ?>
                                                </a>


                                                <input type="hidden" id="page"
                                                       name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_page') ?>"
                                                       value="<?php echo esc_attr_e($page, ATKP_PLUGIN_PREFIX) ?>">
                                                <input type="hidden" id="keyword_search"
                                                       name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_keyword_search') ?>"
                                                       value="<?php echo esc_attr_e($keyword, ATKP_PLUGIN_PREFIX) ?>">
                                                <input type="hidden" id="shop_search"
                                                       name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_shop_search') ?>"
                                                       value="<?php echo esc_attr_e($selectedshopid, ATKP_PLUGIN_PREFIX) ?>">
                                                <input type="hidden" id="option_search"
                                                       name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_option_search') ?>"
                                                       value="<?php echo esc_attr_e($searchoption, ATKP_PLUGIN_PREFIX) ?>">
                                            </td>
                                        </tr>


                                    </table>

                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <style>
                .atkp-button-icon {
                    font-size: 16px;
                    line-height: initial;
                    vertical-align: middle;
                }
            </style>

            <script type="text/javascript">
                var $j = jQuery.noConflict();
                $j(document).ready(function () {

                    $j('#keyword').keypress(function (event) {
                        if (event.keyCode == 13 || event.which == 13) {
                            this.closest('form').submit();
                        }
                    });

                    $j("[name=<?php echo esc_html('ATKP_PLUGIN_PREFIX' . '_searchoption') ?>]").change(function () {
                        $j('#keywordcaption').text($j(this).data('name'));
                    });

                    $j("#ATKP_shopid").change(function () {

                        var shopoption = $j('option:selected', $j("#ATKP_shopid"));


                        var hasarticlenumber = shopoption.attr('data-article_number') == 'true';
                        var hasgtin = shopoption.attr('data-gtin') == 'true';
                        var hasisbn = shopoption.attr('data-isbn') == 'true';
                        var hasean = shopoption.attr('data-ean') == 'true' || shopoption.attr('value') == '';

                        $j('#searchoption4').prop('disabled', !hasarticlenumber);
                        $j('#searchoption5').prop('disabled', !hasgtin);
                        $j('#searchoption6').prop('disabled', !hasisbn);
                        $j('#searchoption3').prop('disabled', !hasean);

                        if ((!hasarticlenumber && $j('#searchoption4').is(':checked')) || (!hasgtin && $j('#searchoption5').is(':checked')) || (!hasisbn && $j('#searchoption6').is(':checked')) || (!hasean && $j('#searchoption3').is(':checked'))) {
                            $j('#searchoption1').prop('checked', true);
                        }

                        if (!hasarticlenumber)
                            $j("#<?php echo esc_html(ATKP_PLUGIN_PREFIX . '_asintype') ?> option[value='ARTICLENUMBER']").remove();
                        else {
                            if ($j("#<?php echo esc_html(ATKP_PLUGIN_PREFIX . '_asintype') ?> option[value='ARTICLENUMBER']").length == 0)
                                $j("#<?php echo esc_html( ATKP_PLUGIN_PREFIX . '_asintype' ) ?>").append('<option value="ARTICLENUMBER"><?php echo esc_html__( 'Articlenumber', ATKP_PLUGIN_PREFIX ) ?></option>');
                        }


                    });

                    $j("#<?php echo esc_html(ATKP_PLUGIN_PREFIX . '_shopid') ?>").trigger('change');

                    if (typeof $j('#<?php echo esc_html(ATKP_PLUGIN_PREFIX . '_shopid') ?>').select2atkp == 'function')
                        $j('#<?php echo esc_html(ATKP_PLUGIN_PREFIX . '_shopid') ?>').select2atkp({});
                });

            </script>


			<?php


			$list = apply_filters( 'atkp_bulkimport_modify_jslist', array() );

			$this->list = $list;

			$result = null;
			if ( $searched ) {


				if ( $selectedshopid == '' ) {

					$shops = atkp_shop::get_list();

					foreach ( $shops as $shop ) {
						if ( $shop->provider == null ) {
							continue;
						}

						echo '<h2>' . esc_html__( $shop->title, ATKP_PLUGIN_PREFIX ) . ' (' . esc_html( $shop->id ) . ')</h2>';

						echo '<div ' . ( $shop->type == atkp_shop_type::SUB_SHOPS ? 'disabled' : '' ) . ' class="atkp-backend-livesearch" init="' . esc_attr( ! $searched ? "1" : "0" ) . '" shopid="' . esc_attr($shop->id) . '" keyword="' . esc_attr( $keyword ) . '" searchoption="' . esc_attr( $searchoption ) . '" asintype="' . esc_attr( $asintype ) . '" endpointurl="' . esc_url( ATKPTools::get_endpointurl() ) . '" ></div>';


						foreach ( $shop->children as $child ) {
							echo '<h2>- ' . esc_html__( $child->title, ATKP_PLUGIN_PREFIX ) . ' (' . esc_html( $child->id ) . ')</h2>';

							echo '<div  class="atkp-backend-livesearch" init="' . esc_attr( ! $searched ? "1" : "0" ) . '" shopid="' . esc_attr($child->id) . '" keyword="' . esc_attr( $keyword ) . '" searchoption="' . esc_attr( $searchoption ) . '" asintype="' . esc_attr( $asintype ) . '" endpointurl="' . esc_url( ATKPTools::get_endpointurl() ) . '" ></div>';

						}
					}


				} else {
					$shop = atkp_shop::load( $selectedshopid );

					if ( $shop == null || $shop->provider == null ) {
						echo '<div class="atkp-error">' . esc_html__( 'shop extension was not loaded. Check if extension is activated.', ATKP_PLUGIN_PREFIX ) . '</div>';

						$searched = false;
					} else {
						$shop->provider->checklogon( $shop );
						$result = null;
						try {
							//$result = $shop->provider->retrieve_departments();

							$result = $shop->provider->quick_search( $keyword, $searchoption == 'asin' || $searchoption == 'ean' || $searchoption == 'articlenumber' || $searchoption == 'isbn' || $searchoption == 'gtin' ? $searchoption : 'product', $page );
						} catch ( Exception $e ) {

							ATKPLog::LogError( $e->getMessage() );

							echo '<div class="atkp-error">' . esc_html__( $e->getMessage(), ATKP_PLUGIN_PREFIX ) . '</div>';

							$searched = false;
						}

						if ( $searched && isset( $result ) && $result != null && $result->message != '' ) {

							echo '<div class="atkp-warning">' . sprintf( esc_html__( 'API returned: %s', ATKP_PLUGIN_PREFIX ), esc_html($result->message) ) . '</div>';
							$searched = false;
						}

						if ( $searched ) {

							try {
								?>
                                <table class="wp-list-table widefat fixed striped" style="width:99%;">
                                    <thead>
                                    <tr>
                                        <th scope="col" class="manage-column"
                                            style="width: 100px;text-align:center">
	                                        <?php echo esc_html__( 'Image', ATKP_PLUGIN_PREFIX ) ?>
                                        </th>

                                        <th scope="col" class="manage-column column-primary">
	                                        <?php echo esc_html__( 'Title', ATKP_PLUGIN_PREFIX ) ?>
                                        </th>

                                        <th scope="col" class="manage-column">
	                                        <?php echo esc_html__( 'Custom Fields', ATKP_PLUGIN_PREFIX ) ?>
                                        </th>

                                        <th scope="col" class="manage-column" style="width:210px">
	                                        <?php echo esc_html__( 'Status', ATKP_PLUGIN_PREFIX ) ?>
                                        </th>

                                    </tr>
                                    </thead>

                                    <tbody>

									<?php


									if ( isset( $result ) && $result != null ) {

										foreach ( $result->products as $product ) {
											$asin   = $product['asin'];
											$uniqid = uniqid();

											?>

                                            <tr data-rowid="<?php echo esc_attr($uniqid) ?>">
                                                <td style="text-align:center">
													<?php if ( isset( $product['imageurl'] ) ) {
														echo '<img src="' . esc_url($product['imageurl']) . '" style="max-width: 100px;" />';
													} ?>
                                                </td>

                                                <td>
                                                    <input type="text" style="width:100%"
                                                           id="<?php echo esc_attr('atkp-title-' . $uniqid) ?>"
                                                           value="<?php echo esc_attr( $product['title'] ) ?>"/>
                                                    <br/>
	                                                <?php echo esc_html__( 'Unique ID', ATKP_PLUGIN_PREFIX ); ?>
                                                    : <?php echo esc_html__( $asin, ATKP_PLUGIN_PREFIX ); ?>,
                                                    EAN: <?php if ( isset( $product['ean'] ) ) {
		                                                echo esc_html__( $product['ean'], ATKP_PLUGIN_PREFIX );
													} else {
														echo '-';
	                                                } ?>
                                                    , <?php echo esc_html__( 'Articlenumber', ATKP_PLUGIN_PREFIX ); ?>
                                                    : <?php if ( isset( $product['articlenumber'] ) ) {
		                                                echo esc_html__( $product['articlenumber'], ATKP_PLUGIN_PREFIX );
													} else {
														echo '-';
													}
													echo '<br />';
													echo( isset( $product['saleprice'] ) ? sprintf( esc_html__( 'Price: %s', ATKP_PLUGIN_PREFIX ), esc_html($product['saleprice']) ) : '-' );

													$cname = '';
													$ss    = isset( $product['shopid'] ) && $product['shopid'] != '' ? atkp_shop::load_shopid( $shop, $product['shopid'] ) : null;

													if ( $ss == null && isset( $product['shopname'] ) && $product['shopname'] != '' ) {
														$cname = sprintf( esc_html__( '%s (shop is not enabled)', ATKP_PLUGIN_PREFIX ), esc_html($product['shopname']) );
													} else if ( $ss != null ) {
														$cname = $ss->title;
													}
													echo( $cname != '' ? ', ' . sprintf( esc_html__( 'Shop: %s', ATKP_PLUGIN_PREFIX ), esc_html($cname) ) : '' );
													echo( isset( $product['info'] ) ? ', Information: ' . esc_html( $product['info'] ) : '' ) ?>
                                                    <br/>
                                                    <a href="<?php echo esc_url($product['producturl']); ?>"
                                                       target="_blank"><?php echo esc_html__( 'View product', ATKP_PLUGIN_PREFIX ); ?></a>
                                                </td>

                                                <td>

													<?php do_action( 'atkp_bulkimport_customfields', $product, $uniqid ); ?>

                                                </td>

                                                <td>
													<?php
													$asin          = $orig_asin = isset( $product['asin'] ) ? $product['asin'] : '';
													$ean           = isset( $product['ean'] ) ? $product['ean'] : '';
													$articlenumber = isset( $product['articlenumber'] ) ? $product['articlenumber'] : '';
													$title         = ATKPTools::clear_string( isset( $product['title'] ) ? $product['title'] : '' );
													//$shopid = $selectedshopid; // isset( $product['shopid'] ) ? $product['shopid'] : '';

													$post_id = apply_filters( 'atkp_find_product', 0, $selectedshopid, $orig_asin, $ean, $title, '', '' );

													switch ( $asintype ) {
														default:
														case 'ARTICLENUMBER':
														case 'ASIN':
															break;
														case 'EAN':
															$asin = $ean;
															break;
														case 'TITLE':
															$asin = $title;
															break;
													}


													echo '<div id="' . esc_attr( 'atkp-status-' . $uniqid ) . '" class="atkp-status">';

													if ( $post_id > 0 ) {


														echo '<img style="vertical-align:middle" src="' . esc_url(plugins_url( 'images/yes.png', ATKP_PLUGIN_FILE )) . '" alt="' . esc_attr_e( 'Imported', ATKP_PLUGIN_PREFIX ) . '"/>';
														echo '<a style="margin-left:5px" href="' . esc_url( get_edit_post_link( $post_id ) ) . '" target="_blank">' . esc_html__( 'Product imported.', ATKP_PLUGIN_PREFIX ) . '</a><br />';

													}
													if ( $asin == '' ) {
														echo '<img style="vertical-align:middle" src="' . esc_url( plugins_url( 'images/no.png', ATKP_PLUGIN_FILE ) ) . '" alt="' . esc_html__( 'Key is empty', ATKP_PLUGIN_PREFIX ) . '"/>';
														echo sprintf( esc_html__( '%s is empty.', ATKP_PLUGIN_PREFIX ), esc_html($asintype) ) . '<br />';

													}
													/*
													if($ss == null && $product['shopname'] != '') {
														echo '<img style="vertical-align:middle" src="' . plugins_url( 'images/no.png', ATKP_PLUGIN_FILE ) . '" alt="' . __( 'Key is empty', ATKP_PLUGIN_PREFIX ) . '"/>';
														echo  sprintf(__( 'Shop %s is not enabled.', ATKP_PLUGIN_PREFIX ) , $product['shopname']). '<br />';

													}*/


													echo '</div>';

													?>

                                                    <input <?php echo $asin == '' ? 'disabled' : '' ?>
                                                            type="button"
                                                            style="margin-top:5px"
                                                            id="<?php echo esc_attr('atkp-draft-' . $uniqid) ?>"
                                                            name="<?php echo esc_attr('importdraft-' . $uniqid) ?>"
                                                            data-rowid="<?php echo esc_attr($uniqid) ?>"
                                                            data-shop="<?php echo esc_attr($selectedshopid) ?>"
                                                            data-asin="<?php echo esc_attr($product['asin']) ?>"
                                                            data-asin2="<?php echo esc_attr($asin) ?>"
                                                            data-asintype="<?php echo esc_html($asintype) ?>"
                                                            data-subshopid="<?php echo esc_attr( ( $ss == null && isset( $product['shopname'] ) && $product['shopname'] != '' ) ? $product['shopid'] : '' ) ?>"
                                                            data-subshopname="<?php echo esc_attr( ( $ss == null && isset( $product['shopname'] ) && $product['shopname'] != '' ) ? $product['shopname'] : '' ) ?>"
                                                            data-status="draft"
                                                            class="import-button button"
                                                            title="<?php esc_attr_e( 'Import as draft', ATKP_PLUGIN_PREFIX ) ?>"
                                                            value="<?php esc_attr_e( 'Import as draft', ATKP_PLUGIN_PREFIX ) ?>"/><br/>
                                                    <input <?php echo $asin == '' ? 'disabled' : '' ?>
                                                            type="button"
                                                            style="margin-top:5px"
                                                            id="<?php echo esc_attr('atkp-publish-' . $uniqid) ?>"
                                                            name="<?php echo esc_attr('importpublish-' . $uniqid) ?>"
                                                            data-rowid="<?php echo esc_attr($uniqid) ?>"
                                                            data-shop="<?php echo esc_attr($selectedshopid) ?>"
                                                            data-asin="<?php echo esc_attr($product['asin']) ?>"
                                                            data-asin2="<?php echo esc_attr($asin) ?>"
                                                            data-asintype="<?php echo esc_attr($asintype) ?>"
                                                            data-subshopid="<?php echo esc_attr( ( $ss == null && isset( $product['shopname'] ) && $product['shopname'] != '' ) ? $product['shopid'] : '' ) ?>"
                                                            data-subshopname="<?php echo esc_attr( ( $ss == null && isset( $product['shopname'] ) && $product['shopname'] != '' ) ? $product['shopname'] : '' ) ?>"
                                                            data-status="publish"
                                                            class="import-button button"
                                                            title="<?php esc_attr_e( 'Import and publish', ATKP_PLUGIN_PREFIX ) ?>"
                                                            value="<?php esc_attr_e( 'Import and publish', ATKP_PLUGIN_PREFIX ) ?>"/>

													<?php
													do_action( 'atkp_import_custombuttons', $asintype, $asin, $product, $selectedshopid, $uniqid );
													?>
                                                </td>

                                            </tr>

											<?php

										}
									}


									//TODO: wenn results < 10 dann nextpage ausblenden
									?>
                                    </tbody>
                                </table>
								<?php


							} catch ( Exception $e ) {
								echo '<span style="color:red">';
								var_dump( $e );
								echo '</span>';
								$searched = false;
							}
						}
					}
				}
			}

			if ( $selectedshopid != '' && $searched) {
				?>

                <div style="margin:10px">
					<?php
						if ( $result->currentpage > 1 ) { ?>
                            <input style="vertical-align:middle" type="submit" style="margin-top:5px" name="lastpage"
                                   class="button primary-button" title="<?php esc_attr_e( 'Last page', ATKP_PLUGIN_PREFIX ) ?>"
                                   value="<?php esc_attr_e( 'Previous page', ATKP_PLUGIN_PREFIX ) ?>"/>
						<?php }
					?>

                    <span style="margin-left:5px;margin-right:5px">
                        Seite <?php echo esc_html($result->currentpage); ?> von <?php echo esc_html($result->pagecount) ?> (<?php echo esc_html($result->total) ?> Ergebnisse)
                    </span>

					<?php if (  $result->currentpage < $result->pagecount ) { ?>
                        <input style="vertical-align:middle" type="submit" style="margin-top:5px" name="nextpage"
                               class="button primary-button" title="<?php esc_attr_e( 'Next page', ATKP_PLUGIN_PREFIX ) ?>"
                               value="<?php esc_attr_e( 'Next page', ATKP_PLUGIN_PREFIX ) ?>"/>
					<?php }
					?>
                </div>
			<?php } else if ( $searched ) {


				?>
                <div style="height:150px">&nbsp;</div>
                <div class="atkp-importoptions">

                <table style="border:0px">
                    <tr>
                        <td>
                            <input type="button" style="text-align:center;width:200px" name="atkp-importdraft"
                                   id="atkp-importdraft"
                                   class="button primary-button totalimportbutton"
                                   title="<?php esc_attr_e( 'Import as draft', ATKP_PLUGIN_PREFIX ) ?>"
                                   data-asintype="<?php echo esc_html($asintype) ?>"
                                   data-status="draft"
                                   value="<?php esc_attr_e( 'Import as draft', ATKP_PLUGIN_PREFIX ) ?>"/>
                        </td>
                        <td>
                            <input type="button" style="text-align:center;width:200px" name="atkp-importpublish"
                                   id="atkp-importpublish"
                                   class="button primary-button totalimportbutton"
                                   title="<?php esc_attr_e( 'Import and publish', ATKP_PLUGIN_PREFIX ) ?>"
                                   data-asintype="<?php echo esc_html($asintype) ?>"
                                   data-status="publish"
                                   value="<?php esc_attr_e( 'Import and publish', ATKP_PLUGIN_PREFIX ) ?>"/>
                        </td>
                        <td>
		                    <?php
		                    do_action( 'atkp_bulkimport_custombuttons', $asintype );
		                    ?>
                        </td>
                    </tr>

                </table>
                </div><?php
			} ?>


        </form>

        <script type="text/javascript">
            var $j = jQuery.noConflict();

            $j(document).ready(function () {

                $j('.totalimportbutton').click(function (e) {
                    $j('.totalimportbutton').prop('disabled', true);

                    var asintype = $j(this).attr('data-asintype');
                    var status = $j(this).attr('data-status');

                    if ($j('input.atkp-checkboxstyle:checkbox:checked').length == 0) {
                        $j('.totalimportbutton').prop('disabled', false);
                    } else {
                        $j('input.atkp-checkboxstyle:checkbox:checked').each(function () {
                            var asin = $j(this).attr('asin');

                            $j('#atkp-status-' + asin).addClass('atkp-spinloader');
                        });


                        $j('input.atkp-checkboxstyle:checkbox:checked').each(function () {


                            var shopid = $j(this).attr('shopid');
                            var asin = $j(this).attr('asin');
                            var asinkey = $j(this).attr('asinkey');
                            var ean = $j(this).attr('ean');
                            var articlenumber = $j(this).attr('articlenumber');

                            var checkbox = $j(this);

                            $j.ajax({
                                type: "POST",
                                url: "<?php echo esc_url(ATKPTools::get_endpointurl()); ?>",
                                data: {
                                    action: "atkp_import_product",
                                    shop: shopid,
                                    asin: asinkey,
                                    asintype: asintype,
                                    status: status,
                                    request_nonce: "<?php echo esc_html(wp_create_nonce( 'atkp-import-nonce' )) ?>"
                                },

                                dataType: "json",
                                success: function (data) {
                                    try {
                                        if (data.length == 0) {
                                            alert('unknown issue');
                                            return;
                                        } else if (typeof data[0].error != 'undefined') {
                                            alert(data[0].error + ': ' + data[0].message);
                                        }

                                        var posturl = data[0].edit_url;

                                        $j('#atkp-status-' + asin).removeClass('atkp-spinloader');
                                        $j('#atkp-status-' + asin).html('<img style="vertical-align:middle" src="<?php echo esc_url(plugins_url( 'images/yes.png', ATKP_PLUGIN_FILE )) ?>" alt="<?php echo esc_attr_e( 'Imported', ATKP_PLUGIN_PREFIX ) ?>"/><a style="margin-left:5px" href="'+posturl+'" target="_blank"><?php echo esc_html__( 'Product imported.', ATKP_PLUGIN_PREFIX ) ?></a><br />');

                                        if ($j("#atkp-attachproduct option[value='" + data[0].postid + "']").length == 0)
                                            $j('#atkp-attachproduct').append('<option value="' + data[0].postid + '">' + data[0].title + '</option>');
                                        $j('#atkp-attachproduct').val(data[0].postid);

                                    } catch (err) {
                                        console.log(err);
                                        alert(err.message);
                                    }

                                    $j(checkbox).prop("checked", false);
                                    $j('.totalimportbutton').prop('disabled', false);

                                },
                                error: function (xhr, status) {
                                    console.log(xhr);
                                    alert(xhr.responseText);
                                    $j('#atkp-status-' + asin).removeClass('atkp-spinloader');
                                    $j('.totalimportbutton').prop('disabled', false);
                                }
                            });
                        });
                    }

                });

                $j(".atkp-backend-livesearch").each(function (i, obj) {

                    var shopid = $j(obj).attr("shopid");
                    var keyword = $j(obj).attr("keyword");
                    var searchoption = $j(obj).attr("searchoption");
                    var endpointurl = $j(obj).attr('endpointurl');
                    var asintype = $j(obj).attr('asintype');
                    var init = $j(obj).attr('init');

                    if (init == "1") {
                        return;
                    }

                    $j(obj).addClass('atkp-spinloader');
                    $j('.atkp-livesearch-searching').show();

                    $j.post(endpointurl,
                        {
                            action: 'atkp_live_search_backend',
                            shopid: shopid,
                            keyword: keyword,
                            asintype: asintype,
                            searchoption: searchoption,
                        },
                        function (data, status) {
                            //alert("Data: " + data + "\nStatus: " + status);
                            if (status == 'success') {
                                $j('.atkp-livesearch-searching').hide();

                                if (data[0].html == 'noresultfound')
                                    $j('.atkp-livesearch-noresult').show();
                                else if (data[0].html == 'searchtermrequired')
                                    $j('.atkp-livesearch-searchtermrequired').show();
                                else
                                    $j(obj).html(data[0].html);

                                $j('.selectAll').click(function (e) {
                                    var table = $j(e.target).closest('table');
                                    $j('td input:checkbox', table).prop('checked', this.checked);
                                });
                            } else if (typeof data[0].error != 'undefined')
                                $j(obj).html("error on loading: " + data[0].error);
                            else
                                $j(obj).html("error on loading");

                            $j(obj).removeClass('atkp-spinloader');
                        });


                });
            });


            function getfieldvalue(fieldname) {

                if ($j(fieldname).val() == null)
                    return '';
                else
                    return ('' + $j(fieldname).val());
            }


            $j(document).ready(function () {

				<?php
				$disable_select2 = ATKPTools::get_setting( ATKP_PLUGIN_PREFIX . '_disableselect2', false );

				if(! $disable_select2) { ?>

                $j('.atkp-taxonomy-select').each(function (i, obj) {
                    if (!$j(obj).data('select2')) {
                        var placeholder = $j(obj).attr('placeholder');

						<?php

						$placeholder = esc_js( 'select value', ATKP_PLUGIN_PREFIX );
						?>

                        $j(obj).select2atkp({
                            placeholder: "<?php echo esc_js($placeholder); ?>",
                            tags: true,
                            tokenSeparators: [',', ' ']
                        });
                    }
                });
				<?php } ?>

                $j(".import-button").click(function (e) {
                    var $asin = $j(this).attr('data-asin');
                    var $asin2 = $j(this).attr('data-asin2');
                    var $asintype = $j(this).attr('data-asintype');
                    var rowid = $j(this).attr('data-rowid');

                    var subshopid = $j(this).attr('data-subshopid');
                    var subshopname = $j(this).attr('data-subshopname');

                    var shop = $j(this).attr('data-shop');
                    var title = $j('#atkp-title-' + rowid).val();
                    var status = $j(this).attr('data-status');
                    var importurl = $j('#atkp-importurl-' + rowid).val();

                    var statusfield = $j(this).parent().find('.atkp-status');

                    var container = $j(this).parent();

                    container.find('input').each(function (index) {
                        $j(this).prop('disabled', true);
                    });

                    $j.ajax({
                            type: "POST",
                            url: "<?php echo esc_url(ATKPTools::get_endpointurl()); ?>",
                            data: {
                                action: "atkp_import_product",
                                shop: shop,
                                asin: $asin2,
                                asintype: $asintype,
                                title: title,
                                status: status,
                                subshopid: subshopid,
                                importurl: importurl,

                            //dynamic fields
				            <?php
                            if ( isset( $this->list ) ) {
								foreach ( $this->list as $entry ) {
		                    ?> <?php echo  $entry ?>,
									<?php
								}
							}
							?>
                            request_nonce: "<?php echo esc_html(wp_create_nonce( 'atkp-import-nonce' )) ?>"
                        },
                        dataType: "json",
                        success: function (data) {
                            try {
                                if (data.length == 0) {
                                    alert('unknown issue');
                                    return;
                                } else if (typeof data[0].error != 'undefined') {
                                    alert(data[0].error + ': ' + data[0].message);
                                }

                                var $posturl = data[0].edit_url;

                                statusfield.html('<img style="vertical-align:middle" src="<?php echo esc_url( plugins_url( 'images/yes.png', ATKP_PLUGIN_FILE ) ) ?>" alt="<?php echo esc_html__( 'Imported', ATKP_PLUGIN_PREFIX ) ?>"/><a style="margin-left:5px" href="' +  $posturl + '" target="_blank"><?php echo esc_html__( 'Product imported.', ATKP_PLUGIN_PREFIX ) ?></a><br />');

                            } catch (err) {
                                alert(err.message);
                            }

                            container.find('input').each(function (index) {
                                $j(this).prop('disabled', false);
                            });
                        },
                        error: function (xhr, status) {
                            console.log('xxhr', xhr);
                            alert(xhr.responseText);

                            container.find('input').each(function (index) {
                                $j(this).prop('disabled', false);
                            });
                        }
                    });
                });
            });

        </script>

        <style>
            .atkp-info, .atkp-success, .atkp-warning, .atkp-error, .atkp-validation {
                border: 1px solid;
                margin: 0px 0px;
                padding: 15px 10px 15px 10px;
                background-repeat: no-repeat;
                background-position: 10px center;
                display: inline-block;
            }

            .atkp-info {
                color: #00529B;
                background-color: #BDE5F8;
            }

            .atkp-success {
                color: #4F8A10;
                background-color: #DFF2BF;
            }

            .atkp-warning {
                color: #9F6000;
                background-color: #FEEFB3;
            }

            .atkp-error {
                color: #D8000C;
                background-color: #FFBABA;
            }

            .atkp-validation {
                color: #D63301;
                background-color: #FFCCBA;
            }
        </style>
		<?php

	}
}


?>