<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class atkp_posttypes_template {
//private $nounce = '';

	/**
	 * Construct the plugin object
	 */
	public function __construct( $pluginbase ) {
		$this->register_templatePostType();

		add_action( 'add_meta_boxes', array( &$this, 'template_boxes' ) );
		add_action( 'save_post', array( &$this, 'template_detail_save' ) );

		//$this->nounce = wp_create_nonce( 'atkp-export-template' );

		ATKPTools::add_column( ATKP_TEMPLATE_POSTTYPE, __( 'Action', ATKP_PLUGIN_PREFIX ), function ( $post_id ) {

			echo esc_html__( '<span style="font-weight:bold">' . esc_html__( 'ID', ATKP_PLUGIN_PREFIX ) . ':</span> <span >' . $post_id . '</span> ', ATKP_PLUGIN_PREFIX );


			do_action( 'atkp_template_action_column', $post_id );
		}, 2 );

		add_filter( 'atkp_get_template_types', array( $this, 'atkp_get_template_types_blade' ), 10 );
		add_action( 'atkp_template_fields_6', array( $this, 'atkp_template_fields_blade' ), 10, 1 );
		add_action( 'atkp_template_savefields_6', array( $this, 'atkp_template_savefields_blade' ), 10, 1 );
		add_action( 'atkp_template_savefields', array( $this, 'atkp_template_savefields_all' ), 10, 1 );

		add_filter( 'atkp_template_preview_image_url', array( $this, 'atkp_template_preview_image_url' ), 10, 2 );
		add_filter( 'atkp_template_get_blade', array( $this, 'atkp_template_get_template' ), 10, 2 );
		add_filter( 'atkp_template_get_css', array( $this, 'atkp_template_get_css' ), 10, 2 );


	}


	function atkp_template_get_template( $content, $template ) {
		$templatepath = ATKP_PLUGIN_DIR . '/templates/' . $template . '.blade.php';

		if ( file_exists( $templatepath ) ) {
			$bladecontent = file_get_contents( $templatepath );

			//extract style tags

			preg_match_all( "/<style>(.*?)<\/style>/is", $bladecontent, $matches );

			$html = str_replace( $matches[0], '', $bladecontent );

			//$css  = implode( "\n", $matches[1] );

			return $html;
		}

		return $content;
	}

	function atkp_template_get_css( $content, $template ) {
		$templatepath = ATKP_PLUGIN_DIR . '/templates/' . $template . '.blade.php';

		if ( file_exists( $templatepath ) ) {
			$bladecontent = file_get_contents( $templatepath );

			//extract style tags

			preg_match_all( "/<style>(.*?)<\/style>/is", $bladecontent, $matches );

			$css = implode( "\n", $matches[1] );

			return $css;
		}

		return $content;
	}

	function atkp_template_preview_image_url( $template_url, $template_id ) {

		$templatepath = ATKP_PLUGIN_DIR . '/template-images/' . $template_id . '.jpg';

		if ( file_exists( $templatepath ) ) {
			return plugins_url( 'template-images/' . $template_id . '.jpg', ATKP_PLUGIN_FILE );
		}

		return $template_url;
	}


	function atkp_get_template_types_blade( $templates ) {

		$templates[6] = __( 'Product template', ATKP_PLUGIN_PREFIX );

		return $templates;
	}

	function atkp_template_fields_blade( $post_id ) {
		$prdcmds                                                                = array();
		$prdcmds['get_listtitle($product)']                                     = __( 'List title', ATKP_PLUGIN_PREFIX );
		$prdcmds['get_postlist($product)']                                      = __( 'List of posts where mainproduct', ATKP_PLUGIN_PREFIX );
		$prdcmds['get_proslist($product)']                                      = __( 'Pro list', ATKP_PLUGIN_PREFIX );
		$prdcmds['get_contralist($product)']                                    = __( 'Contra list', ATKP_PLUGIN_PREFIX );
		$prdcmds['get_testresult($product)']                                    = __( 'Test result', ATKP_PLUGIN_PREFIX );
		$prdcmds['get_featuretext($product)']                                   = __( 'Features', ATKP_PLUGIN_PREFIX );
		$prdcmds['get_descriptiontext($product)']                               = __( 'Description', ATKP_PLUGIN_PREFIX );
		$prdcmds['get_featuretext_short($product)']                             = __( 'Features short', ATKP_PLUGIN_PREFIX );
		$prdcmds['get_descriptiontext_short($product)']                         = __( 'Description short', ATKP_PLUGIN_PREFIX );
		$prdcmds['get_infotext($product)']                                      = __( 'Features or Description short', ATKP_PLUGIN_PREFIX );
		$prdcmds['get_title($product)']                                         = __( 'Product title', ATKP_PLUGIN_PREFIX );
		$prdcmds['get_primelogo($product)']                                     = __( 'Prime logo', ATKP_PLUGIN_PREFIX );
		$prdcmds['get_producturl($product)']                                    = __( 'Product URL', ATKP_PLUGIN_PREFIX );
		$prdcmds['get_addtocarturl($product)']                                  = __( 'Add to Cart URL', ATKP_PLUGIN_PREFIX );
		$prdcmds['get_customerreviewsurl($product)']                            = __( 'Customer Reviews URL', ATKP_PLUGIN_PREFIX );
		$prdcmds['get_productlink($product)']                                   = __( 'Product link (href)', ATKP_PLUGIN_PREFIX );
		$prdcmds['get_cartlink($product)']                                      = __( 'Add to Cart link (href)', ATKP_PLUGIN_PREFIX );
		$prdcmds['get_percentagesaved($product, \'%s\')']                       = __( 'Percentage saved', ATKP_PLUGIN_PREFIX );
		$prdcmds['get_savetext($product, \'%s\')']                              = __( 'Amount saved', ATKP_PLUGIN_PREFIX );
		$prdcmds['get_pricetext($product, \'%s\')']                             = __( 'Price ', ATKP_PLUGIN_PREFIX );
		$prdcmds['get_listpricetext($product, \'%s\')']                         = __( 'List price', ATKP_PLUGIN_PREFIX );
		$prdcmds['get_refreshdate($product)']                                   = __( 'Date of product update', ATKP_PLUGIN_PREFIX );
		$prdcmds['get_refreshtime($product)']                                   = __( 'Time of product update', ATKP_PLUGIN_PREFIX );
		$prdcmds['get_disclaimer($product)']                                    = __( 'Disclaimer', ATKP_PLUGIN_PREFIX );
		$prdcmds['get_smallimageurl($product)']                                 = __( 'Small image URL', ATKP_PLUGIN_PREFIX );
		$prdcmds['get_mediumnimageurl($product)']                               = __( 'Medium image URL', ATKP_PLUGIN_PREFIX );
		$prdcmds['get_largeimageurl($product)']                                 = __( 'Large image URL', ATKP_PLUGIN_PREFIX );
		$prdcmds['get_smallimage($product)']                                    = __( 'Small image', ATKP_PLUGIN_PREFIX );
		$prdcmds['get_mediumimage($product)']                                   = __( 'Medium image', ATKP_PLUGIN_PREFIX );
		$prdcmds['get_largeimage($product)']                                    = __( 'Large image', ATKP_PLUGIN_PREFIX );
		$prdcmds['get_shorttitle($product)']                                    = __( 'Short title', ATKP_PLUGIN_PREFIX );
		$prdcmds['get_rating_text($product)']                                   = __( 'Rating text', ATKP_PLUGIN_PREFIX );
		$prdcmds['get_star_rating($product)']                                   = __( 'Star rating', ATKP_PLUGIN_PREFIX );
		$prdcmds['get_reviewstext($product)']                                   = __( 'Reviews text', ATKP_PLUGIN_PREFIX );
		$prdcmds['get_reviewslink($product)']                                   = __( 'Reviews link (href)', ATKP_PLUGIN_PREFIX );
		$prdcmds['get_woocommerceurl($product)']                                = __( 'Woocommerce product URL', ATKP_PLUGIN_PREFIX );
		$prdcmds['get_woocommercetitle($product)']                              = __( 'Woocommerce title', ATKP_PLUGIN_PREFIX );
		$prdcmds['get_woocommercelink($product)']                               = __( 'Woocommerce link', ATKP_PLUGIN_PREFIX );
		$prdcmds['get_shop_logourl($formatter->get_shop_value($product))']      = __( 'Shop logo URL', ATKP_PLUGIN_PREFIX );
		$prdcmds['get_shop_smalllogourl($formatter->get_shop_value($product))'] = __( 'Small shop logo URL', ATKP_PLUGIN_PREFIX );
		$prdcmds['get_shop_logo($formatter->get_shop_value($product))']         = __( 'Shop logo', ATKP_PLUGIN_PREFIX );
		$prdcmds['get_shop_smalllogo($formatter->get_shop_value($product))']    = __( 'Small shop logo', ATKP_PLUGIN_PREFIX );
		$prdcmds['get_shop_title($formatter->get_shop_value($product))']        = __( 'Shop title', ATKP_PLUGIN_PREFIX );


		$imgcmds                                               = array();
		$imgcmds['get_image_smallimageurl($product, $image)']  = __( 'Small image URL', ATKP_PLUGIN_PREFIX );
		$imgcmds['get_image_mediumimageurl($product, $image)'] = __( 'Medium image URL', ATKP_PLUGIN_PREFIX );
		$imgcmds['get_image_largeimageurl($product, $image)']  = __( 'Large image URL', ATKP_PLUGIN_PREFIX );
		$imgcmds['get_image_smallimage($product, $image)']     = __( 'Small image', ATKP_PLUGIN_PREFIX );
		$imgcmds['get_image_mediumimage($product, $image)']    = __( 'Medium image', ATKP_PLUGIN_PREFIX );
		$imgcmds['get_image_largeimage($product, $image)']     = __( 'Large image', ATKP_PLUGIN_PREFIX );


		$offercmds                                        = array();
		$offercmds['get_offer_price($offer,\'%s\')']      = __( 'Offer price', ATKP_PLUGIN_PREFIX );
		$offercmds['get_offer_total($offer,\'%s\')']      = __( 'Offer total (price + shipping)', ATKP_PLUGIN_PREFIX );
		$offercmds['get_offer_shipping($offer,\'%s\')']   = __( 'Offer shipping', ATKP_PLUGIN_PREFIX );
		$offercmds['get_offer_availability($offer)']      = __( 'Offer availability', ATKP_PLUGIN_PREFIX );
		$offercmds['get_offer_url($offer)']               = __( 'Offer url', ATKP_PLUGIN_PREFIX );
		$offercmds['get_offer_title($offer)']             = __( 'Offer title', ATKP_PLUGIN_PREFIX );
		$offercmds['get_shop_logourl($offer->shop)']      = __( 'Shop logo URL', ATKP_PLUGIN_PREFIX );
		$offercmds['get_shop_smalllogourl($offer->shop)'] = __( 'Small shop logo URL', ATKP_PLUGIN_PREFIX );
		$offercmds['get_shop_logo($offer->shop)']         = __( 'Shop logo', ATKP_PLUGIN_PREFIX );
		$offercmds['get_shop_smalllogo($offer->shop)']    = __( 'Small shop logo', ATKP_PLUGIN_PREFIX );
		$offercmds['get_shop_title($offer->shop)']        = __( 'Shop title', ATKP_PLUGIN_PREFIX );

		$customcmds = array();

		$newfields = atkp_udfield::load_fields();

		foreach ( $newfields as $newfield ) {
			$fieldname = 'customfield_' . $newfield->name;

			$customcmds[ 'get_displayfield($product, \'' . $fieldname . '\')' ] = $newfield->caption;
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

					$customcmds[ 'get_displayfield($product, \'' . $fieldname . '\')' ] = $taxonomy->caption;
				}
			}
		}

		$groups = ATKPTools::get_fieldgroups();

		foreach ( $groups as $group ) {
			$fields = ATKPTools::get_post_setting( $group->ID, ATKP_FIELDGROUP_POSTTYPE . '_fields' );

			if ( $fields != null ) {
				foreach ( $fields as $field ) {
					$fieldname = 'cf_' . $field->name;

					$customcmds[ 'get_displayfield($product, \'' . $fieldname . '\')' ] = $field->caption;
				}
			}
		}


		$customcmds['get_shop_value($product)->customfield1'] = __( 'Custom field 1 (shop)', ATKP_PLUGIN_PREFIX );
		$customcmds['get_shop_value($product)->customfield2'] = __( 'Custom field 2 (shop)', ATKP_PLUGIN_PREFIX );
		$customcmds['get_shop_value($product)->customfield3'] = __( 'Custom field 3 (shop)', ATKP_PLUGIN_PREFIX );

		$customcmds['$offer->shop->customfield1'] = __( 'Custom field 1 (offer, shop)', ATKP_PLUGIN_PREFIX );
		$customcmds['$offer->shop->customfield2'] = __( 'Custom field 2 (offer, shop)', ATKP_PLUGIN_PREFIX );
		$customcmds['$offer->shop->customfield3'] = __( 'Custom field 3 (offer, shop)', ATKP_PLUGIN_PREFIX );

		$acfcmds = array();

		//TODO: replace with atkptools function
		if ( function_exists( 'get_field_objects' ) ) {
			$groups = acf_get_field_groups( array( 'post_type' => ATKP_PRODUCT_POSTTYPE ) );

			foreach ( $groups as $group ) {
				$fields = acf_get_fields( $group['key'] );
				foreach ( $fields as $field ) {

					$label                                                          = $field["label"];
					$name                                                           = $field["name"];
					$acfcmds[ 'get_field(\'' . $name . '\', $product->productid)' ] = $label;
				}
			}

			$groups = acf_get_field_groups( array( 'post_type' => ATKP_SHOP_POSTTYPE ) );

			foreach ( $groups as $group ) {
				$fields = acf_get_fields( $group['key'] );
				foreach ( $fields as $field ) {

					$label                                                                               = $field["label"];
					$name                                                                                = $field["name"];
					$acfcmds[ 'get_field(\'' . $name . '\', $formatter->get_shop_value($product)->id)' ] = sprintf( __( '%s (shop)', ATKP_PLUGIN_PREFIX ), $label );
					$acfcmds[ 'get_field(\'' . $name . '\', $offer->shop->id)' ]                         = sprintf( __( '%s (shop, offer)', ATKP_PLUGIN_PREFIX ), $label );
				}
			}

		}

		?>

        <table style="width:100%" class="atkp-template-expert">
            <tr class="placeholderrowdetail filterrowdetail bladerow">
                <td colspan="2">



                    <div class="atkp-template-placeholder">
                        <ul>
                            <li class="dropdown">
                                <a href="javascript:void(0)"
                                   class="dropbtn"><?php echo esc_html__( 'Containers', ATKP_PLUGIN_PREFIX ) ?></a>
                                <div class="dropdown-content">
                                    <a href="javascript:void(0)" class="atkp-insert"
                                       data-insertid="default-box"><?php echo esc_html__( 'Default box', ATKP_PLUGIN_PREFIX ) ?></a>
                                    <a href="javascript:void(0)" class="atkp-insert"
                                       data-insertid="two-columns"><?php echo esc_html__( 'Two columns layout', ATKP_PLUGIN_PREFIX ) ?></a>
                                    <a href="javascript:void(0)" class="atkp-insert"
                                       data-insertid="three-columns"><?php echo esc_html__( 'Three columns layout', ATKP_PLUGIN_PREFIX ) ?></a>
                                </div>
                            </li>

                            <li class="dropdown">
                                <a href="javascript:void(0)"
                                   class="dropbtn"><?php echo esc_html__( 'Loops', ATKP_PLUGIN_PREFIX ) ?></a>
                                <div class="dropdown-content">
                                    <a href="javascript:void(0)" class="atkp-insert"
                                       data-insertid="product-loop"><?php echo esc_html__( 'Product loop', ATKP_PLUGIN_PREFIX ) ?></a>
                                    <a href="javascript:void(0)" class="atkp-insert"
                                       data-insertid="product-offer-loop"><?php echo esc_html__( 'Product + offer loop', ATKP_PLUGIN_PREFIX ) ?></a>
                                    <a href="javascript:void(0)" class="atkp-insert"
                                       data-insertid="product-image-loop"><?php echo esc_html__( 'Product + image loop', ATKP_PLUGIN_PREFIX ) ?></a>
                                </div>
                            </li>

                            <li class="dropdown">
                                <a href="javascript:void(0)"
                                   class="dropbtn"><?php echo esc_html__( 'Product fields', ATKP_PLUGIN_PREFIX ) ?></a>
                                <div class="dropdown-content">
									<?php

									foreach ( $prdcmds as $prdcmd => $caption ) {
										echo '<a href="javascript:void(0)" class="atkp-insert" data-insertvalue="{!!$formatter->' . esc_attr( $prdcmd ) . '!!}">' . esc_html__( $caption, ATKP_PLUGIN_PREFIX ) . '</a>';
									}

									?>
                                </div>
                            </li>
                            <li class="dropdown">
                                <a href="javascript:void(0)"
                                   class="dropbtn"><?php echo esc_html__( 'Offer fields', ATKP_PLUGIN_PREFIX ) ?></a>
                                <div class="dropdown-content">
									<?php

									foreach ( $offercmds as $prdcmd => $caption ) {
										echo '<a href="javascript:void(0)" class="atkp-insert" data-insertvalue="{!!$formatter->' . esc_attr( $prdcmd ) . '!!}">' . esc_html__( $caption, ATKP_PLUGIN_PREFIX ) . '</a>';
									}

									?>
                                </div>
                            </li>
                            <li class="dropdown">
                                <a href="javascript:void(0)"
                                   class="dropbtn"><?php echo esc_html__( 'Image fields', ATKP_PLUGIN_PREFIX ) ?></a>
                                <div class="dropdown-content">
									<?php

									foreach ( $imgcmds as $prdcmd => $caption ) {
										echo '<a href="javascript:void(0)" class="atkp-insert" data-insertvalue="{!!$formatter->' . esc_attr( $prdcmd ) . '!!}">' . esc_html__( $caption, ATKP_PLUGIN_PREFIX ) . '</a>';
									}

									?>
                                </div>
                            </li>
                            <li class="dropdown">
                                <a href="javascript:void(0)"
                                   class="dropbtn"><?php echo esc_html__( 'Custom fields', ATKP_PLUGIN_PREFIX ) ?></a>
                                <div class="dropdown-content">
									<?php

									foreach ( $customcmds as $prdcmd => $caption ) {
										if ( ATKPTools::startsWith( $prdcmd, '$offer' ) ) {
											echo esc_html__( '<a href="javascript:void(0)" class="atkp-insert" data-insertvalue="{!!' . esc_attr( $prdcmd ) . '!!}">' . esc_html__( $caption, ATKP_PLUGIN_PREFIX ) . '</a>', ATKP_PLUGIN_PREFIX );
										} else {
											echo esc_html__( '<a href="javascript:void(0)" class="atkp-insert" data-insertvalue="{!!$formatter->' . esc_attr( $prdcmd ) . '!!}">' . esc_html__( $caption, ATKP_PLUGIN_PREFIX ) . '</a>', ATKP_PLUGIN_PREFIX );
										}
									}

									?>
                                </div>
                            </li>
							<?php if ( function_exists( 'get_field_objects' ) ) { ?>
                                <li class="dropdown">
                                    <a href="javascript:void(0)"
                                       class="dropbtn"><?php echo esc_html__( 'ACF fields', ATKP_PLUGIN_PREFIX ) ?></a>
                                    <div class="dropdown-content">
										<?php

										foreach ( $acfcmds as $prdcmd => $caption ) {
											echo esc_html__( '<a href="javascript:void(0)" class="atkp-insert" data-insertvalue="{!!' . esc_attr( $prdcmd ) . '!!}">' . esc_html__( $caption, ATKP_PLUGIN_PREFIX ) . '</a>', ATKP_PLUGIN_PREFIX );
										}

										?>
                                    </div>
                                </li>
							<?php } ?>

                            <li class="dropdown">
                                <a href="javascript:void(0)"
                                   class="dropbtn"><?php echo esc_html__( 'Info', ATKP_PLUGIN_PREFIX ) ?></a>
                                <div class="dropdown-content">

                                    <a href="https://www.affiliate-toolkit.com/kb/what-are-templates-and-how-do-i-create-them/#Create_your_own_templates"
                                       target="_blank"><?php echo esc_html__( 'Open documentation', ATKP_PLUGIN_PREFIX ) ?></a>


                                </div>
                            </li>

                        </ul>
                        <div id="atkp-insert-container" style="display: none">
                            <div id="product-loop">
                                <!--header-->
                                @foreach ($products as $product)
                                <!--product content-->
                                {{$formatter->get_title($product)}}
                                @endforeach
                                <!--footer-->
                            </div>

                            <div id="product-offer-loop">
                                <!--header-->
                                @foreach ($products as $product)
                                <!--product content-->
                                {{$formatter->get_title($product)}}

                                @foreach($formatter->get_offers($product, true) as $offer)
                                <!-- offer content -->
                                {{$formatter->get_offer_price($offer, $translator->get_price())}}
                                @endforeach
                                @endforeach
                                <!--footer-->
                            </div>

                            <div id="product-image-loop">
                                <!--header-->
                                @foreach ($products as $product)
                                <!--product content-->
                                {{$formatter->get_title($product)}}

                                @foreach($formatter->get_images($product, true) as $image)
                                <!-- image content -->
                                {!!$formatter->get_image_mediumimage($product, $image)!!}
                                @endforeach
                                @endforeach
                                <!--footer-->
                            </div>

                            <div id="default-box">
                                <div class="atkp-container {{$parameters->cssContainerClass}}">
                                    <div class="atkp-box atkp-clearfix {{$parameters->cssElementClass}}">

                                    </div>
                                </div>
                            </div>

                            <div id="two-columns">
                                <div class="atkp-container {{$parameters->cssContainerClass}}">
                                    <div class="atkp-clearfix atkp-box-2-cols {{$parameters->cssElementClass}}">
                                        <div class="atkp-box atkp-smallbox atkp-box-2-cols-item atkp-clearfix ">
                                            text 1
                                        </div>
                                        <div class="atkp-box atkp-smallbox atkp-box-2-cols-item atkp-clearfix ">
                                            text 2
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="three-columns">
                                <div class="atkp-container {{$parameters->cssContainerClass}}">
                                    <div class="atkp-clearfix atkp-box-3-cols {{$parameters->cssElementClass}}">
                                        <div class="atkp-box atkp-smallbox atkp-box-3-cols-item atkp-clearfix ">
                                            text 1
                                        </div>
                                        <div class="atkp-box atkp-smallbox atkp-box-3-cols-item atkp-clearfix ">
                                            text 2
                                        </div>
                                        <div class="atkp-box atkp-smallbox atkp-box-3-cols-item atkp-clearfix ">
                                            text 3
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>

                    <textarea style="width:100%;height:220px" id="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_body') ?>"
                              name="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_body') ?>"><?php echo esc_textarea( ATKPTools::get_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_body', true ) ); ?></textarea>
                </td>
            </tr>

        </table>


        <style>
            .atkp-template-placeholder ul {
                list-style-type: none;
                margin: 0;
                padding: 0;
                overflow: hidden;
                background-color: #005162;
                border-radius: 3px;
            }

            .atkp-template-placeholder li {
                float: left;
                margin-bottom: 0;
            }

            .atkp-template-placeholder li a, .atkp-template-placeholder .dropbtn {
                display: inline-block;
                color: white;
                text-align: center;
                padding: 14px 16px;
                text-decoration: none;
            }

            .atkp-template-placeholder li a:hover, .atkp-template-placeholder .dropdown:hover .dropbtn {
                background-color: #bde4ea;
            }

            .atkp-template-placeholder li.dropdown {
                display: inline-block;
            }

            .atkp-template-placeholder .dropdown-content {
                display: none;
                position: absolute;
                background-color: #f9f9f9;
                min-width: 160px;
                box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.2);
                z-index: 99;
            }

            .atkp-template-placeholder .dropdown-content a {
                color: black;
                padding: 12px 16px;
                text-decoration: none;
                display: block;
                text-align: left;
            }

            .atkp-template-placeholder .dropdown-content a:hover {
                background-color: #f1f1f1;
            }

            .atkp-template-placeholder .dropdown:hover .dropdown-content {
                display: block;
            }
        </style>

		<?php
	}

	function atkp_template_savefields_blade( $post_id ) {


		$body = ATKPTools::get_post_parameter( ATKP_TEMPLATE_POSTTYPE . '_body', 'allhtml' );
		$css  = ATKPTools::get_post_parameter( ATKP_TEMPLATE_POSTTYPE . '_css', 'allhtml' );

		ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_body', $body );
		ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_css', $css );

	}

	function atkp_template_savefields_all( $post_id ) {

		$custom_styles = ATKPTools::get_post_parameter( ATKP_TEMPLATE_POSTTYPE . '_custom_styles', 'int' );

		ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_custom_styles', $custom_styles );


		if ( $custom_styles ) {
			//save colors etc.

//speichern der template settings

			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_box_background_color', ATKPTools::get_post_parameter( ATKP_TEMPLATE_POSTTYPE . '_box_background_color', 'string' ) );
			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_box_border_color', ATKPTools::get_post_parameter( ATKP_TEMPLATE_POSTTYPE . '_box_border_color', 'string' ) );
			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_box_text_color', ATKPTools::get_post_parameter( ATKP_TEMPLATE_POSTTYPE . '_box_text_color', 'string' ) );
			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_box_textlink_color', ATKPTools::get_post_parameter( ATKP_TEMPLATE_POSTTYPE . '_box_textlink_color', 'string' ) );
			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_box_textlink_hovercolor', ATKPTools::get_post_parameter( ATKP_TEMPLATE_POSTTYPE . '_box_textlink_hovercolor', 'string' ) );

			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_dropdown_textlink_color', ATKPTools::get_post_parameter( ATKP_TEMPLATE_POSTTYPE . '_dropdown_textlink_color', 'string' ) );
			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_dropdown_textlink_hovercolor', ATKPTools::get_post_parameter( ATKP_TEMPLATE_POSTTYPE . '_dropdown_textlink_hovercolor', 'string' ) );

			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_amountsaved_color', ATKPTools::get_post_parameter( ATKP_TEMPLATE_POSTTYPE . '_amountsaved_color', 'string' ) );
			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_price_color', ATKPTools::get_post_parameter( ATKP_TEMPLATE_POSTTYPE . '_price_color', 'string' ) );
			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_listprice_color', ATKPTools::get_post_parameter( ATKP_TEMPLATE_POSTTYPE . '_listprice_color', 'string' ) );


			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_primbtn_background_color', ATKPTools::get_post_parameter( ATKP_TEMPLATE_POSTTYPE . '_primbtn_background_color', 'string' ) );
			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_primbtn_hoverbackground_color', ATKPTools::get_post_parameter( ATKP_TEMPLATE_POSTTYPE . '_primbtn_hoverbackground_color', 'string' ) );
			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_primbtn_foreground_color', ATKPTools::get_post_parameter( ATKP_TEMPLATE_POSTTYPE . '_primbtn_foreground_color', 'string' ) );

			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_primbtn_border_color', ATKPTools::get_post_parameter( ATKP_TEMPLATE_POSTTYPE . '_primbtn_border_color', 'string' ) );
			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_primbtn_border_color', ATKPTools::get_post_parameter( ATKP_TEMPLATE_POSTTYPE . '_primbtn_border_color', 'string' ) );
			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_secbtn_background_color', ATKPTools::get_post_parameter( ATKP_TEMPLATE_POSTTYPE . '_secbtn_background_color', 'string' ) );
			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_secbtn_hoverbackground_color', ATKPTools::get_post_parameter( ATKP_TEMPLATE_POSTTYPE . '_secbtn_hoverbackground_color', 'string' ) );
			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_secbtn_foreground_color', ATKPTools::get_post_parameter( ATKP_TEMPLATE_POSTTYPE . '_secbtn_foreground_color', 'string' ) );
			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_secbtn_border_color', ATKPTools::get_post_parameter( ATKP_TEMPLATE_POSTTYPE . '_secbtn_border_color', 'string' ) );


			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_box_badge_color', ATKPTools::get_post_parameter( ATKP_TEMPLATE_POSTTYPE . '_box_badge_color', 'string' ) );


			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_showprice', ATKPTools::get_post_parameter( ATKP_TEMPLATE_POSTTYPE . '_showprice', 'bool' ) );
			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_showshopname', ATKPTools::get_post_parameter( ATKP_TEMPLATE_POSTTYPE . '_showshopname', 'bool' ) );

			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_linkrating', ATKPTools::get_post_parameter( ATKP_TEMPLATE_POSTTYPE . '_linkrating', 'bool' ) );
			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_showstarrating', ATKPTools::get_post_parameter( ATKP_TEMPLATE_POSTTYPE . '_showstarrating', 'bool' ) );

			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_showbaseprice', ATKPTools::get_post_parameter( ATKP_TEMPLATE_POSTTYPE . '_showbaseprice', 'bool' ) );
			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_showlistprice', ATKPTools::get_post_parameter( ATKP_TEMPLATE_POSTTYPE . '_showlistprice', 'bool' ) );

			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_hideemptystars', ATKPTools::get_post_parameter( ATKP_TEMPLATE_POSTTYPE . '_hideemptystars', 'bool' ) );

			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_hideemptyrating', ATKPTools::get_post_parameter( ATKP_TEMPLATE_POSTTYPE . '_hideemptyrating', 'bool' ) );
			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_linkimage', ATKPTools::get_post_parameter( ATKP_TEMPLATE_POSTTYPE . '_linkimage', 'bool' ) );

			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_showpricediscount', ATKPTools::get_post_parameter( ATKP_TEMPLATE_POSTTYPE . '_showpricediscount', 'bool' ) );
			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_showrating', ATKPTools::get_post_parameter( ATKP_TEMPLATE_POSTTYPE . '_showrating', 'bool' ) );

			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_secbtn_image', ATKPTools::get_post_parameter( ATKP_TEMPLATE_POSTTYPE . '_secbtn_image', 'string' ) );
			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_primbtn_image', ATKPTools::get_post_parameter( ATKP_TEMPLATE_POSTTYPE . '_primbtn_image', 'string' ) );

			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_mark_links', ATKPTools::get_post_parameter( ATKP_TEMPLATE_POSTTYPE . '_mark_links', 'bool' ) );

			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_btn_radius', ATKPTools::get_post_parameter( ATKP_TEMPLATE_POSTTYPE . '_btn_radius', 'int' ) );
			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_box_radius', ATKPTools::get_post_parameter( ATKP_TEMPLATE_POSTTYPE . '_box_radius', 'int' ) );

			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_show_disclaimer', ATKPTools::get_post_parameter( ATKP_TEMPLATE_POSTTYPE . '_show_disclaimer', 'bool' ) );
			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_disclaimer_text', ATKPTools::get_post_parameter( ATKP_TEMPLATE_POSTTYPE . '_disclaimer_text', 'html' ) );
			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_show_priceinfo', ATKPTools::get_post_parameter( ATKP_TEMPLATE_POSTTYPE . '_show_priceinfo', 'bool' ) );
			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_priceinfo_text', ATKPTools::get_post_parameter( ATKP_TEMPLATE_POSTTYPE . '_priceinfo_text', 'html' ) );


			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_affiliatechar', ATKPTools::get_post_parameter( ATKP_TEMPLATE_POSTTYPE . '_affiliatechar', 'html' ) );

			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_hideprocontra', ATKPTools::get_post_parameter( ATKP_TEMPLATE_POSTTYPE . '_hideprocontra', 'bool' ) );
			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_primbtn_size', ATKPTools::get_post_parameter( ATKP_TEMPLATE_POSTTYPE . '_primbtn_size', 'string' ) );
			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_secbtn_size', ATKPTools::get_post_parameter( ATKP_TEMPLATE_POSTTYPE . '_secbtn_size', 'string' ) );


			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_productpage_title', ATKPTools::get_post_parameter( ATKP_TEMPLATE_POSTTYPE . '_productpage_title', 'string' ) );

			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_box_show_shadow', ATKPTools::get_post_parameter( ATKP_TEMPLATE_POSTTYPE . '_box_show_shadow', 'bool' ) );


			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_short_title_length', ATKPTools::get_post_parameter( ATKP_TEMPLATE_POSTTYPE . '_short_title_length', 'int' ) );

			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_show_moreoffers', ATKPTools::get_post_parameter( ATKP_TEMPLATE_POSTTYPE . '_show_moreoffers', 'bool' ) );
			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_moreoffers_includemainoffer', ATKPTools::get_post_parameter( ATKP_TEMPLATE_POSTTYPE . '_moreoffers_includemainoffer', 'bool' ) );

			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_moreoffers_template', ATKPTools::get_post_parameter( ATKP_TEMPLATE_POSTTYPE . '_moreoffers_template', 'string' ) );
			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_moreoffers_title', ATKPTools::get_post_parameter( ATKP_TEMPLATE_POSTTYPE . '_moreoffers_title', 'string' ) );
			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_moreoffers_count', ATKPTools::get_post_parameter( ATKP_TEMPLATE_POSTTYPE . '_moreoffers_count', 'int' ) );


			//speichern der einstellungen

		} else {
			//deleting of settings

			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_box_background_color', null );
			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_box_border_color', null );
			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_box_text_color', null );
			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_box_textlink_color', null );
			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_box_textlink_hovercolor', null );

			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_dropdown_textlink_color', null );
			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_dropdown_textlink_hovercolor', null );

			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_amountsaved_color', null );
			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_price_color', null );
			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_listprice_color', null );


			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_primbtn_background_color', null );
			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_primbtn_hoverbackground_color', null );
			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_primbtn_foreground_color', null );

			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_primbtn_border_color', null );
			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_primbtn_border_color', null );
			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_secbtn_background_color', null );
			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_secbtn_hoverbackground_color', null );
			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_secbtn_foreground_color', null );
			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_secbtn_border_color', null );


			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_box_badge_color', null );


			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_showprice', null );
			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_showshopname', null );

			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_linkrating', null );
			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_showstarrating', null );

			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_showbaseprice', null );
			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_showlistprice', null );

			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_hideemptystars', null );

			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_hideemptyrating', null );
			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_linkimage', null );

			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_showpricediscount', null );
			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_showrating', null );

			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_secbtn_image', null );
			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_primbtn_image', null );

			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_mark_links', null );

			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_btn_radius', null );
			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_box_radius', null );

			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_show_disclaimer', null );
			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_disclaimer_text', null );
			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_show_priceinfo', null );
			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_priceinfo_text', null );


			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_affiliatechar', null );

			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_hideprocontra', null );
			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_primbtn_size', null );
			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_secbtn_size', null );

			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_box_show_shadow', null );


			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_short_title_length', null );

			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_show_moreoffers', null );
			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_moreoffers_includemainoffer', null );

			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_moreoffers_template', null );
			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_moreoffers_title', null );
			ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_moreoffers_count', null );


		}
	}


	function register_templatePostType() {
		$labels = array(
			'name'               => __( 'Templates', ATKP_PLUGIN_PREFIX ),
			'singular_name'      => __( 'Template', ATKP_PLUGIN_PREFIX ),
			'add_new_item'       => __( 'Add new Template', ATKP_PLUGIN_PREFIX ),
			'edit_item'          => __( 'Edit Template', ATKP_PLUGIN_PREFIX ),
			'new_item'           => __( 'New Template', ATKP_PLUGIN_PREFIX ),
			'all_items'          => __( 'Templates', ATKP_PLUGIN_PREFIX ),
			'view_item'          => __( 'View Template', ATKP_PLUGIN_PREFIX ),
			'search_items'       => __( 'Search Templates', ATKP_PLUGIN_PREFIX ),
			'not_found'          => __( 'No lists found', ATKP_PLUGIN_PREFIX ),
			'not_found_in_trash' => __( 'No lists found in the Trash', ATKP_PLUGIN_PREFIX ),
			'parent_item_colon'  => '',
			'menu_name'          => __( 'Templates', ATKP_PLUGIN_PREFIX ),
		);
		$args   = array(
			'labels'      => $labels,
			'description' => 'Holds our templates',

			'public'              => false,  // it's not public, it shouldn't have it's own permalink, and so on
			'publicly_queriable'  => false,  // you should be able to query it
			'show_ui'             => true,  // you should be able to edit it in wp-admin
			'exclude_from_search' => true,  // you should exclude it from search results
			'show_in_nav_menus'   => false,  // you shouldn't be able to add it to menus
			'has_archive'         => false,  // it shouldn't have archive page
			'rewrite'             => false,  // it shouldn't have rewrite rules

			'capability_type' => 'page',

			'menu_position' => 200,
			'supports' => array( 'title' ),
			'show_in_menu'  => false,
		);


		$args = apply_filters( 'atkp_template_register_post_type', $args );

		register_post_type( ATKP_TEMPLATE_POSTTYPE, $args );


	}

	function template_boxes() {
		add_meta_box(
			ATKP_TEMPLATE_POSTTYPE . '_detail_box',
			__( 'Template information', ATKP_PLUGIN_PREFIX ),
			array( &$this, 'template_detail_box_content' ),
			ATKP_TEMPLATE_POSTTYPE,
			'normal',
			'default'
		);

		add_meta_box(
			ATKP_TEMPLATE_POSTTYPE . '_css_box',
			__( 'Template CSS', ATKP_PLUGIN_PREFIX ),
			array( &$this, 'css_detail_box_content' ),
			ATKP_TEMPLATE_POSTTYPE,
			'normal',
			'default'
		);

		add_meta_box(
			ATKP_TEMPLATE_POSTTYPE . '_detail_box_style',
			__( 'Template preview and styling', ATKP_PLUGIN_PREFIX ),
			array( &$this, 'template_detail_box_style' ),
			ATKP_TEMPLATE_POSTTYPE,
			'normal',
			'default'
		);
	}

	function get_template_types() {
		$durations = array();

		$durations = apply_filters( 'atkp_get_template_types', $durations );

		asort( $durations );

		return $durations;
	}

	function template_detail_box_style( $post ) {
		?>
        <table class="form-table" style="overflow-x: scroll;">
            <tr>
                <td>&nbsp;</td>
            </tr>
        </table>
        <!--preview -->

        <div class="atkp-settings-preview">
			<?php
			$output = new atkp_output();

			echo "<link rel='stylesheet' id='atkp-styles-css' href='" . esc_url( plugins_url( '/dist/style.css', ATKP_PLUGIN_FILE ) ) . "' media='all' />";
			echo "<script src='" . esc_url(plugins_url( '/dist/script.js', ATKP_PLUGIN_FILE )) . "' id='atkp-scripts-js'></script>";

			echo '<style>';
			echo  $output->get_css_output() ;
			echo '</style>';
			echo '<script>';
			 $output->get_js_output() ;
			echo '</script>';

			$template_id      = $post->ID;
			$parameters       = new atkp_template_parameters();
			$shortcode_params = array();
			$parameters->buildTemplateParameters( $template_id, $shortcode_params );

			$str_params   = json_encode( $parameters->data, JSON_PRETTY_PRINT );
			$prd_ids      = array();
			$prd_ids[]    = array( 'product_id' => - 1, 'list_id' => 0 );
			$str_products = json_encode( $prd_ids, JSON_PRETTY_PRINT );

			$uid = uniqid();
			?>

            <input type="hidden" value="" id="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_templateparams') ?>"/>
            <script type="application/json" id="<?php echo esc_attr('atkp-data-parameters-' . $uid) ?>">
                <?php echo $str_params ; ?>
            </script>
            <script type="application/json" id="<?php echo esc_attr( 'atkp-data-products-' . $uid ) ?>">
                <?php echo $str_products ; ?>
            </script>

            <div style="max-width:700px;margin-left:auto;margin-right:auto;padding: 20px; border-left: 1px solid #005162;border-right: 1px solid #005162">
                <div style="">
                    <code id="atkp-shortcode" style="vertical-align: middle">[atkp template='' ids=''][/atkp]</code>
                </div>
                <p style="margin-bottom: 1.6em;-webkit-mask-image: -webkit-gradient(linear,  left bottom,left top, from(rgba(0,0,0,1)), to(rgba(0,0,0,0)));">
                    Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut
                    labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores
                    et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.
                </p>
                <div class="atkp-ajax-container" style="margin-bottom:10px;" data-uid="<?php echo esc_attr($uid) ?>"
                     data-endpointurl="<?php echo esc_url_raw(ATKPTools::get_endpointurl()); ?>"></div>
                <p style=" -webkit-mask-image: -webkit-gradient(linear, left top, left bottom, from(rgba(0,0,0,1)), to(rgba(0,0,0,0)));">
                    Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut
                    labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores
                    et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.
                </p>
            </div>
            <script>
                if (typeof jQuery === 'undefined') {
                    console.log('no jquery loaded');
                } else {
                    var $j = jQuery.noConflict();
                    $j(document).ready(function () {
                        reloadTemplatePreview();

                        $j('.atkp-template-option').on('change', function () {
                            reloadTemplatePreview();
                        });

                        if (typeof $j('.color-field').wpColorPicker !== "undefined") {
                            $j('.color-field').wpColorPicker({
                                change: function (event, ui) {
                                    console.log(event);
                                    var theColor = ui.color.toString();
                                    var name = $j(this).attr('name');
                                    $j("input[name='" + name + "']").val(theColor.trim());
                                    reloadTemplatePreview();
                                }
                            });
                        }

                    });
                }

                var lastRequest = null;

                function reloadTemplatePreview() {
                    if (lastRequest != null) {
                        lastRequest.abort();
                        lastRequest = null;
                    }

                    $j(".atkp-ajax-container").each(function (i, obj) {

                        var endpointurl = $j(obj).attr('data-endpointurl');
                        var uid = $j(obj).attr('data-uid');

                        var atkpparameters = JSON.parse($j('#atkp-data-parameters-' + uid).html());
                        var atkpproducts = JSON.parse($j('#atkp-data-products-' + uid).html());

                        //atkpparameters['offerstemplate'] = $j('#atkp_template_moreoffers_template').val();

                        $j('.atkp-template-option').each(function (index) {
                            var value = $j(this).val();
                            var name = $j(this).attr("name");

                            if ($j(this).is(':checkbox')) {
                                if ($j(this).is(":checked")) {
                                    atkpparameters[name.replace('atkp_template_', '')] = true;
                                } else {
                                    atkpparameters[name.replace('atkp_template_', '')] = false;
                                }
                            } else {
                                if (value != '')
                                    atkpparameters[name.replace('atkp_template_', '')] = value;
                            }
                        });

                        atkpparameters['templateid'] = '<?php echo esc_html__( get_the_ID() ); ?>';
                        //atkpparameters['templatecontent'] = $j('').val();
                        //atkpparameters['csscontent'] = $j('').val();

                        $j('#atkp-shortcode').html("[atkp template='" + atkpparameters['templateid'] + "' ids=''][/atkp]");

                        //console.log(atkpparameters);

                        $j(obj).html('');
                        $j(obj).addClass('atkp-spinloader-round');

                        lastRequest = $j.post(endpointurl,
                            {
                                action: 'atkp_render_template',
                                products: JSON.stringify(atkpproducts),
                                parameters: JSON.stringify(atkpparameters),
                                preview: true,
                            },
                            function (data, status) {

                                if (status == 'success') {
                                    //hide info??

                                    switch (data.status) {
                                        case 'okay':
                                            //rendering ok
                                            $j(obj).html(data.html);
                                            break;
                                        case 'error':
                                            $j(obj).html(data.error + '<br />' + data.message);
                                            break;
                                        default:
                                            $j(obj).html("unknown error on loading");
                                            break;
                                    }

                                }
                                $j.event.trigger({
                                    type: "atkp_template_rendered",
                                    status: data.status,
                                    uid: uid
                                });

                                $j(obj).removeClass('atkp-spinloader-round');
                                lastRequest = null;
                            }).fail(function () {
                            $j(obj).removeClass('atkp-spinloader-round');
                            $j(obj).html("server side error on loading");
                            lastRequest = null;
                        });
                    });
                }
            </script>
        </div>


        <div class="atkp-settings-fields">
            <input type="checkbox" id="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_custom_styles') ?>"
                   name="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_custom_styles') ?>"
                   class="atkp-custom_styles"
                   value="1" <?php echo checked( 1, ATKPTools::get_post_setting( $post->ID, ATKP_TEMPLATE_POSTTYPE . '_custom_styles' ), true ); ?>>
            <label for="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_custom_styles') ?>">
	            <?php echo esc_html__( 'Use your own styles (independent of the global style)', ATKP_TEMPLATE_POSTTYPE ) ?>
            </label>


            <table style="width:100%;   margin-left: auto;    margin-right: auto; display:none;"
                   class="atkp-template-settings">
                <tr>
                    <th colspan="5" style="background-color:#bde4ea; padding:7px">
	                    <?php echo esc_html__( 'Colors', ATKP_TEMPLATE_POSTTYPE ) ?>
                    </th>
                </tr>
                <tr>
                    <th class="atkp-settings-group">
                        <span style="writing-mode: vertical-lr; ">Box</span>
                    </th>
                    <td style="width:25%">
                        <label for="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE) . '_box_background_color' ?>">
	                        <?php echo esc_html__( 'Background', ATKP_TEMPLATE_POSTTYPE ) ?>
                        </label><br/>

                        <input type="text" class="color-field atkp-template-option"
                               id="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_box_background_color') ?>"
                               name="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_box_background_color') ?>"
                               value=" <?php echo esc_attr($parameters->get_box_background_color()) ?>">

                    </td>

                    <td style="width:25%">
                        <label for="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_box_border_color') ?>">
	                        <?php echo esc_html__( 'Border', ATKP_TEMPLATE_POSTTYPE ) ?>
                        </label><br/>

                        <input type="text" class="color-field atkp-template-option"
                               id="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_box_border_color') ?>"
                               name="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_box_border_color') ?>"
                               value=" <?php echo esc_attr($parameters->get_box_border_color()) ?>">

                    </td>

                    <td style="width:25%">
                        <label for="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_box_text_color') ?>">
	                        <?php echo esc_html__( 'Foreground', ATKP_TEMPLATE_POSTTYPE ) ?>
                        </label><br/>

                        <input type="text" class="color-field atkp-template-option"
                               id="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_box_text_color') ?>"
                               name="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_box_text_color') ?>"
                               value=" <?php echo esc_attr($parameters->get_box_text_color()) ?>">

                    </td>

                    <td style="width:25%">
                        <label for="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_box_badge_color') ?>">
	                        <?php echo esc_html__( 'Badge', ATKP_TEMPLATE_POSTTYPE ) ?>
                        </label><br/>

                        <input type="text" class="color-field atkp-template-option"
                               id="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_box_badge_color') ?>"
                               name="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_box_badge_color') ?>"
                               value=" <?php echo esc_attr($parameters->get_box_badge_color()) ?>">

                    </td>

                </tr>
                <tr>
                    <th class="atkp-settings-group">
                        <span style="writing-mode: vertical-lr; "><?php echo esc_html__( 'Link', ATKP_TEMPLATE_POSTTYPE ) ?></span>
                    </th>
                    <td>
                        <label for="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_box_textlink_color') ?>">
	                        <?php echo esc_html__( 'Foreground', ATKP_TEMPLATE_POSTTYPE ) ?>
                        </label><br/>

                        <input type="text" class="color-field atkp-template-option"
                               id="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_box_textlink_color') ?>"
                               name="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_box_textlink_color') ?>"
                               value=" <?php echo esc_attr($parameters->get_box_text_color()) ?>">

                    </td>
                    <td>
                        <label for="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_box_textlink_hovercolor') ?>">
	                        <?php echo esc_html__( 'Hover', ATKP_TEMPLATE_POSTTYPE ) ?>
                        </label><br/>

                        <input type="text" class="color-field atkp-template-option"
                               id="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_box_textlink_hovercolor') ?>"
                               name="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_box_textlink_hovercolor') ?>"
                               value=" <?php echo esc_attr($parameters->get_box_textlink_hovercolor()) ?>">

                    </td>

                    <td>
                        <label for="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_dropdown_textlink_color') ?>">
	                        <?php echo esc_html__( 'Dropdown Foreground', ATKP_TEMPLATE_POSTTYPE ) ?>
                        </label><br/>

                        <input type="text" class="color-field atkp-template-option"
                               id="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_dropdown_textlink_color') ?>"
                               name="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_dropdown_textlink_color') ?>"
                               value=" <?php echo esc_attr($parameters->get_dropdown_textlink_color()) ?>">

                    </td>
                    <td>
                        <label for="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_dropdown_textlink_hovercolor') ?>">
	                        <?php echo esc_html__( 'Dropdown Hover', ATKP_TEMPLATE_POSTTYPE ) ?>
                        </label><br/>

                        <input type="text" class="color-field atkp-template-option"
                               id="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_dropdown_textlink_hovercolor') ?>"
                               name="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_dropdown_textlink_hovercolor') ?>"
                               value=" <?php echo esc_attr($parameters->get_dropdown_textlink_hovercolor()) ?>">

                    </td>
                </tr>
                <tr>
                    <th class="atkp-settings-group">
                        <span style="writing-mode: vertical-lr; "><?php echo esc_html__( 'Button 1', ATKP_TEMPLATE_POSTTYPE ) ?></span>
                    </th>
                    <td>
                        <label for="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_primbtn_background_color') ?>">
	                        <?php echo esc_html__( 'Background', ATKP_TEMPLATE_POSTTYPE ) ?>
                        </label><br/>

                        <input type="text" class="color-field atkp-template-option"
                               id="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_primbtn_background_color') ?>"
                               name="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_primbtn_background_color') ?>"
                               value=" <?php echo esc_attr($parameters->get_primbtn_background_color()) ?>">

                    </td>
                    <td>
                        <label for="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_primbtn_hoverbackground_color') ?>">
	                        <?php echo esc_html__( 'Hover Background', ATKP_TEMPLATE_POSTTYPE ) ?>
                        </label><br/>
                        <input type="text" class="color-field atkp-template-option"
                               id="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_primbtn_hoverbackground_color') ?>"
                               name="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_primbtn_hoverbackground_color') ?>"
                               value=" <?php echo esc_attr($parameters->get_primbtn_hoverbackground_color()) ?>">
                    </td>
                    <td>
                        <label for="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_primbtn_foreground_color') ?>">
	                        <?php echo esc_html__( 'Foreground', ATKP_TEMPLATE_POSTTYPE ) ?>
                        </label><br/>
                        <input type="text" class="color-field atkp-template-option"
                               id="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_primbtn_foreground_color') ?>"
                               name="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_primbtn_foreground_color') ?>"
                               value=" <?php echo esc_attr($parameters->get_primbtn_foreground_color()) ?>">
                    </td>
                    <td>
                        <label for="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_primbtn_border_color') ?>">
	                        <?php echo esc_html__( 'Border', ATKP_TEMPLATE_POSTTYPE ) ?>
                        </label><br/>

                        <input type="text" class="color-field atkp-template-option"
                               id="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_primbtn_border_color') ?>"
                               name="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_primbtn_border_color') ?>"
                               value=" <?php echo esc_attr($parameters->get_primbtn_border_color()) ?>">

                </tr>
                <tr>
                    <th class="atkp-settings-group">
                        <span style="writing-mode: vertical-lr; ">Button 2</span>
                    </th>
                    <td>
                        <label for="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_secbtn_background_color') ?>">
	                        <?php echo esc_html__( 'Background', ATKP_TEMPLATE_POSTTYPE ) ?>
                        </label><br/>
                        <input type="text" class="color-field atkp-template-option"
                               id="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_secbtn_background_color') ?>"
                               name="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_secbtn_background_color') ?>"
                               value=" <?php echo esc_attr($parameters->get_secbtn_background_color()) ?>">

                    </td>
                    <td>
                        <label for="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_secbtn_hoverbackground_color') ?>">
	                        <?php echo esc_html__( 'Hover Background', ATKP_TEMPLATE_POSTTYPE ) ?>
                        </label><br/>
                        <input type="text" class="color-field atkp-template-option"
                               id="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_secbtn_hoverbackground_color') ?>"
                               name="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_secbtn_hoverbackground_color') ?>"
                               value=" <?php echo esc_attr($parameters->get_secbtn_hoverbackground_color()) ?>">
                    </td>
                    <td>
                        <label for="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_secbtn_foreground_color') ?>">
	                        <?php echo esc_html__( 'Foreground', ATKP_TEMPLATE_POSTTYPE ) ?>
                        </label><br/>
                        <input type="text" class="color-field atkp-template-option"
                               id="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_secbtn_foreground_color') ?>"
                               name="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_secbtn_foreground_color') ?>"
                               value=" <?php echo esc_attr($parameters->get_secbtn_foreground_color()) ?>">
                    </td>
                    <td>
                        <label for="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_secbtn_border_color') ?>">
	                        <?php echo esc_html__( 'Border', ATKP_TEMPLATE_POSTTYPE ) ?>
                        </label><br/>
                        <input type="text" class="color-field atkp-template-option"
                               id="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_secbtn_border_color') ?>"
                               name="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_secbtn_border_color') ?>"
                               value=" <?php echo esc_attr($parameters->get_secbtn_border_color()) ?>">
                    </td>
                </tr>


                <tr>
                    <th class="atkp-settings-group">
                        <span style="writing-mode: vertical-lr; ">Prices</span>
                    </th>
                    <td>
                        <label for="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_listprice_color') ?>">
	                        <?php echo esc_html__( 'List price', ATKP_TEMPLATE_POSTTYPE ) ?>
                        </label><br/>
                        <input type="text" class="color-field atkp-template-option"
                               id="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_listprice_color') ?>"
                               name="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_listprice_color') ?>"
                               value=" <?php echo esc_attr($parameters->get_listprice_color()) ?>">

                    </td>
                    <td>
                        <label for="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_amountsaved_color') ?>">
	                        <?php echo esc_html__( 'Amount saved', ATKP_TEMPLATE_POSTTYPE ) ?>
                        </label><br/>
                        <input type="text" class="color-field atkp-template-option"
                               id="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_amountsaved_color') ?>"
                               name="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_amountsaved_color') ?>"
                               value=" <?php echo esc_attr($parameters->get_amountsaved_color()) ?>">
                    </td>
                    <td>
                        <label for="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_price_color') ?>">
	                        <?php echo esc_html__( 'Price', ATKP_TEMPLATE_POSTTYPE ) ?>
                        </label><br/>
                        <input type="text" class="color-field atkp-template-option"
                               id="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_price_color') ?>"
                               name="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_price_color') ?>"
                               value="<?php echo esc_attr($parameters->get_price_color()) ?>">
                    </td>
                </tr>


                <tr>
                    <th colspan="5" style="background-color:#bde4ea; padding:7px">
	                    <?php echo esc_html__( 'Display', ATKP_TEMPLATE_POSTTYPE ) ?>
                    </th>
                </tr>
                <tr>
                    <th class="atkp-settings-group"></th>
                    <td>
                        <input type="checkbox" id="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_showshopname') ?>"
                               name="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_showshopname') ?>"
                               class=" atkp-template-option"
                               value="1" <?php echo checked( 1, $parameters->get_showshopname(), true ); ?>>
                        <label for="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_showshopname') ?>">
	                        <?php echo esc_html__( 'Show shop', ATKP_TEMPLATE_POSTTYPE ) ?>
                        </label>

                    </td>

                    <td>
                        <input type="checkbox" id="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_showstarrating') ?>"
                               name="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_showstarrating') ?>"
                               class=" atkp-template-option"
                               value="1" <?php echo checked( 1, $parameters->get_showstarrating(), true ); ?>>
                        <label for="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_showstarrating') ?>">
	                        <?php echo esc_html__( 'Show star rating', ATKP_TEMPLATE_POSTTYPE ) ?>
                        </label>
                    </td>
                    <td>
                        <input type="checkbox" id="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_linkrating') ?>"
                               name="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_linkrating') ?>"
                               class=" atkp-template-option"
                               value="1" <?php echo checked( 1, $parameters->get_linkrating(), true ); ?>>
                        <label for="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_linkrating') ?>">
	                        <?php echo esc_html__( 'Link rating', ATKP_TEMPLATE_POSTTYPE ) ?>
                        </label>
                    </td>
                    <td>
                        <input type="checkbox" id="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_linkimage') ?>"
                               name="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_linkimage') ?>"
                               class=" atkp-template-option"
                               value="1" <?php echo checked( 1, $parameters->get_linkimage(), true ); ?>>
                        <label for="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_linkimage') ?>">
	                        <?php echo esc_html__( 'Link product image', ATKP_TEMPLATE_POSTTYPE ) ?>
                        </label>
                    </td>

                </tr>
                <tr>
                    <th class="atkp-settings-group"></th>
                    <td>
                        <input type="checkbox" id="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_showprice') ?>"
                               name="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_showprice') ?>"
                               class=" atkp-template-option"
                               value="1" <?php echo checked( 1, $parameters->get_showprice(), true ); ?>>
                        <label for="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_showprice') ?>">
	                        <?php echo esc_html__( 'Show price', ATKP_TEMPLATE_POSTTYPE ) ?>
                        </label>
                    </td>
                    <td>
                        <input type="checkbox" id="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_showlistprice') ?>"
                               name="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_showlistprice') ?>"
                               class=" atkp-template-option"
                               value="1" <?php echo checked( 1, $parameters->get_showlistprice(), true ); ?>>
                        <label for="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_showlistprice') ?>">
	                        <?php echo esc_html__( 'Show list price', ATKP_TEMPLATE_POSTTYPE ) ?>
                        </label>
                    </td>
                    <td>
                        <input type="checkbox" id="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_showbaseprice') ?>"
                               name="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_showbaseprice') ?>"
                               class=" atkp-template-option"
                               value="1" <?php echo checked( 1, $parameters->get_showbaseprice(), true ); ?>>
                        <label for="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_showbaseprice') ?>">
	                        <?php echo esc_html__( 'Show base price', ATKP_TEMPLATE_POSTTYPE ) ?>
                        </label>
                    </td>
                    <td>
                        <input type="checkbox" id="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_showpricediscount') ?>"
                               name="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_showpricediscount') ?>"
                               class=" atkp-template-option"
                               value="1" <?php echo checked( 1, $parameters->get_showpricediscount(), true ); ?>>
                        <label for="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_showpricediscount') ?>">
	                        <?php echo esc_html__( 'Show price discount', ATKP_TEMPLATE_POSTTYPE ) ?>
                        </label>
                    </td>

                </tr>
                <tr>
                    <th class="atkp-settings-group"></th>

                    <td>
                        <input type="checkbox" id="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_hideemptystars') ?>"
                               name="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_hideemptystars') ?>"
                               class=" atkp-template-option"
                               value="1" <?php echo checked( 1, $parameters->get_hideemptystars(), true ); ?>>
                        <label for="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_hideemptystars') ?>">
	                        <?php echo esc_html__( 'Hide ratings with 0 stars', ATKP_TEMPLATE_POSTTYPE ) ?>
                        </label>
                    </td>
                    <td>
                        <input type="checkbox" id="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_hideemptyrating') ?>"
                               name="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_hideemptyrating') ?>"
                               class=" atkp-template-option"
                               value="1" <?php echo checked( 1, $parameters->get_hideemptyrating(), true ); ?>>
                        <label for="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_hideemptyrating') ?>">
	                        <?php echo esc_html__( 'Hide reviews without value', ATKP_TEMPLATE_POSTTYPE ) ?>
                        </label>
                    </td>
                    <td>
                        <input type="checkbox" id="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_hideprocontra') ?>"
                               name="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_hideprocontra') ?>"
                               class=" atkp-template-option"
                               value="1" <?php echo checked( 1, $parameters->get_hideprocontra(), true ); ?>>
                        <label for="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_hideprocontra') ?>">
	                        <?php echo esc_html__( 'Hide pro/contra', ATKP_TEMPLATE_POSTTYPE ) ?>
                        </label>
                    </td>

                    <td>
                        <input type="checkbox" id="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_box_show_shadow') ?>"
                               name="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_box_show_shadow') ?>"
                               class=" atkp-template-option"
                               value="1" <?php echo checked( 1, $parameters->get_box_show_shadow(), true ); ?>>
                        <label for="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_box_show_shadow') ?>">
	                        <?php echo esc_html__( 'Show Box shadow', ATKP_TEMPLATE_POSTTYPE ) ?>
                        </label>
                    </td>

                </tr>
                <tr>
                    <th class="atkp-settings-group"></th>
                    <td style="vertical-align: top;">
                        <label for="">
	                        <?php echo esc_html__( 'Button 1 image', ATKP_TEMPLATE_POSTTYPE ) ?>:
                        </label>

                        <select id="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_primbtn_image') ?>"
                                class="atkp-template-option"
                                name="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_primbtn_image') ?>" style="">
							<?php
							$selected = $parameters->get_primbtn_image();

							echo '<option value="no_image" ' . ( $selected == '' || $selected == 'no_image' ? 'selected' : '' ) . ' >' . esc_html__( 'No image ', ATKP_PLUGIN_PREFIX ) . '</option>';

							echo '<option value="amz_black" ' . ( $selected == 'amz_black' ? 'selected' : '' ) . '>' . esc_html__( 'Amazon black', ATKP_TEMPLATE_POSTTYPE ) . '</option>';
							echo '<option value="amz_white" ' . ( $selected == 'amz_white' ? 'selected' : '' ) . '>' . esc_html__( 'Amazon white', ATKP_TEMPLATE_POSTTYPE ) . '</option>';
							echo '<option value="cart_black" ' . ( $selected == 'cart_black' ? 'selected' : '' ) . '>' . esc_html__( 'Cart black', ATKP_TEMPLATE_POSTTYPE ) . '</option>';
							echo '<option value="cart_white" ' . ( $selected == 'cart_white' ? 'selected' : '' ) . '>' . esc_html__( 'Cart white', ATKP_TEMPLATE_POSTTYPE ) . '</option>';

							?>
                        </select>
                    </td>

                    <td style="vertical-align: top;">
                        <label for="">
	                        <?php echo esc_html__( 'Button 2 image', ATKP_TEMPLATE_POSTTYPE ) ?>:
                        </label>

                        <select id="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_secbtn_image') ?>"
                                class="atkp-template-option"
                                name="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_secbtn_image') ?>" style="">
							<?php
							$selected = $parameters->get_secbtn_image();

							echo '<option value="no_image" ' . ( $selected == '' || $selected == 'no_image' ? 'selected' : '' ) . ' >' . esc_html__( 'No image ', ATKP_PLUGIN_PREFIX ) . '</option>';

							echo '<option value="amz_black" ' . ( $selected == 'amz_black' ? 'selected' : '' ) . '>' . esc_html__( 'Amazon black', ATKP_TEMPLATE_POSTTYPE ) . '</option>';
							echo '<option value="amz_white" ' . ( $selected == 'amz_white' ? 'selected' : '' ) . '>' . esc_html__( 'Amazon white', ATKP_TEMPLATE_POSTTYPE ) . '</option>';
							echo '<option value="cart_black" ' . ( $selected == 'cart_black' ? 'selected' : '' ) . '>' . esc_html__( 'Cart black', ATKP_TEMPLATE_POSTTYPE ) . '</option>';
							echo '<option value="cart_white" ' . ( $selected == 'cart_white' ? 'selected' : '' ) . '>' . esc_html__( 'Cart white', ATKP_TEMPLATE_POSTTYPE ) . '</option>';

							?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th colspan="5" style="background-color:#bde4ea; padding:7px">
	                    <?php echo esc_html__( 'Radius', ATKP_TEMPLATE_POSTTYPE ) ?>
                    </th>
                </tr>
                <tr>
                    <th class="atkp-settings-group"></th>
                    <td>
                        <label for="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_btn_radius') ?>">
	                        <?php echo esc_html__( 'Button Radius', ATKP_TEMPLATE_POSTTYPE ) ?>
                        </label>
                        <input type="number" id="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_btn_radius') ?>"
                               name="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_btn_radius') ?>"
                               class=" atkp-template-option"
                               value="<?php echo esc_attr($parameters->get_btn_radius()); ?>">

                    </td>
                    <td>
                        <label for="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_box_radius') ?>">
	                        <?php echo esc_html__( 'Box Radius', ATKP_TEMPLATE_POSTTYPE ) ?>
                        </label>
                        <input type="number" id="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_box_radius') ?>"
                               name="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_box_radius') ?>"
                               class=" atkp-template-option"
                               value="<?php echo esc_attr($parameters->get_box_border_radius()) ?>">

                    </td>
                </tr>
                <tr>
                    <th colspan="5" style="background-color:#bde4ea; padding:7px">
	                    <?php echo esc_html__( 'Size & Length', ATKP_TEMPLATE_POSTTYPE ) ?>
                    </th>
                </tr>
                <tr>
                    <th class="atkp-settings-group"></th>
                    <td>
                        <label for="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_primbtn_size') ?>">
	                        <?php echo esc_html__( 'Button 1', ATKP_TEMPLATE_POSTTYPE ) ?>
                        </label>
                        <select id="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_primbtn_size') ?>"
                                name="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_primbtn_size') ?>"
                                class="atkp-template-option">
							<?php
							$selected = $parameters->get_primbtn_size();
							echo '<option value="normal" ' . ( $selected == '' || $selected == 'normal' ? 'selected' : '' ) . '>' . esc_html__( 'normal', ATKP_TEMPLATE_POSTTYPE ) . '</option>';

							echo '<option value="small" ' . ( $selected == 'small' ? 'selected' : '' ) . ' >' . esc_html__( 'small', ATKP_TEMPLATE_POSTTYPE ) . '</option>';

							echo '<option value="big" ' . ( $selected == 'big' ? 'selected' : '' ) . '>' . esc_html__( 'big', ATKP_TEMPLATE_POSTTYPE ) . '</option>';


							?>
                        </select>
                    </td>
                    <td>
                        <label for="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_primbtn_size') ?>">
	                        <?php echo esc_html__( 'Button 2', ATKP_TEMPLATE_POSTTYPE ) ?>
                        </label>
                        <select id="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_secbtn_size') ?>"
                                name="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_secbtn_size') ?>"
                                class="atkp-template-option">
							<?php
							$selected = $parameters->get_secbtn_size();
							echo '<option value="normal" ' . ( $selected == '' || $selected == 'normal' ? 'selected' : '' ) . '>' . esc_html__( 'normal', ATKP_TEMPLATE_POSTTYPE ) . '</option>';

							echo '<option value="small" ' . ( $selected == 'small' ? 'selected' : '' ) . ' >' . esc_html__( 'small', ATKP_TEMPLATE_POSTTYPE ) . '</option>';

							echo '<option value="big" ' . ( $selected == 'big' ? 'selected' : '' ) . '>' . esc_html__( 'big', ATKP_TEMPLATE_POSTTYPE ) . '</option>';


							?>
                        </select>

                    </td>
                </tr>


                <tr>
                    <th>&nbsp;</th>
                    <td>
                        <label for="">
	                        <?php echo esc_html__( 'Title length', ATKP_TEMPLATE_POSTTYPE ) ?>:
                        </label>
                        <input type="number" min="0" max="1000"
                               id="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_short_title_length') ?>"
                               name="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_short_title_length') ?>"
                               style="width:50px"
                               class="atkp-template-option"
                               value="<?php echo esc_attr($parameters->get_short_title_length()); ?>">
                    </td>

                    <td scope="row">
                        <label for="">
	                        <?php echo esc_html__( 'Description length', ATKP_TEMPLATE_POSTTYPE ) ?>:
                        </label>
                        <input type="number" min="0" max="1000"
                               id="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_description_length') ?>"
                               name="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_description_length') ?>"
                               style="width:50px"
                               class="atkp-template-option"
                               value="<?php echo esc_attr($parameters->get_description_length()); ?>">
                    </td>

                    <td scope="row">
                        <label for="">
	                        <?php echo esc_html__( 'Features count', ATKP_TEMPLATE_POSTTYPE ) ?>:
                        </label>
                        <input type="number" min="0" max="1000"
                               id="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_feature_count') ?>"
                               name="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_feature_count') ?>"
                               style="width:50px"
                               class="atkp-template-option"
                               value="<?php echo esc_attr($parameters->get_feature_count()); ?>">
                    </td>
                </tr>


                <tr>
                    <th colspan="5" style="background-color:#bde4ea; padding:7px">
	                    <?php echo esc_html__( 'Texts', ATKP_TEMPLATE_POSTTYPE ) ?>
                    </th>
                </tr>
                <tr>
                    <th class="atkp-settings-group"></th>
                    <td colspan="2">
                        <input type="checkbox" id="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_show_disclaimer') ?>"
                               name="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_show_disclaimer') ?>"
                               class=" atkp-template-option"
                               value="1" <?php echo checked( 1, $parameters->get_show_disclaimer(), true ); ?>>
                        <label for="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_show_disclaimer') ?>">
	                        <?php echo esc_html__( 'Show disclaimer', ATKP_TEMPLATE_POSTTYPE ) ?>
                        </label> <br/>
                        <textarea id="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_disclaimer_text') ?>" style="width:100%"
                                  rows="4" class=" atkp-template-option"
                                  name="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_disclaimer_text') ?>"><?php echo esc_textarea( $parameters->get_disclaimer_text() ) ?></textarea>


                    </td>
                    <td colspan="2" style="vertical-align: top">
                        <input type="checkbox" id="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_show_priceinfo') ?>"
                               name="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_show_priceinfo') ?>"
                               class=" atkp-template-option"
                               value="1" <?php echo checked( 1, $parameters->get_show_priceinfo(), true ); ?>>
                        <label for="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_priceinfo_text') ?>">
	                        <?php echo esc_html__( 'Price info text', ATKP_TEMPLATE_POSTTYPE ) ?>
                        </label><br/>
                        <textarea id="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_priceinfo_text') ?>" style="width:100%"
                                  class=" atkp-template-option" rows="4"
                                  name="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_priceinfo_text') ?>"><?php echo esc_textarea( $parameters->get_priceinfo_text() ) ?></textarea>


                    </td>


                </tr>
                <tr>
                    <th class="atkp-settings-group"></th>
                    <td style="vertical-align: top;">
                        <label for="">
	                        <?php echo esc_html__( 'Description & Features', ATKP_TEMPLATE_POSTTYPE ) ?>:
                        </label>

                        <select id="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_boxcontent') ?>"
                                class="atkp-template-option"
                                name="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_boxcontent') ?>" style="">
							<?php
							$selected = $parameters->get_box_description_content();

							echo '<option value="1" ' . ( $selected == '' || $selected == 1 ? 'selected' : '' ) . ' >' . esc_html__( 'Features and (if empty) description ', ATKP_TEMPLATE_POSTTYPE ) . '</option>';

							echo '<option value="2" ' . ( $selected == 2 ? 'selected' : '' ) . '>' . esc_html__( 'Features', ATKP_TEMPLATE_POSTTYPE ) . '</option>';
							echo '<option value="3" ' . ( $selected == 3 ? 'selected' : '' ) . '>' . esc_html__( 'Description', ATKP_TEMPLATE_POSTTYPE ) . '</option>';

							?>
                        </select>
                    </td>
                    <td style="vertical-align: top">
                        <label for="">
	                        <?php echo esc_html__( 'Product page text', ATKP_TEMPLATE_POSTTYPE ) . ' (html)' ?>:
                        </label>

                        <input type="text"
                               id="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_productpage_title') ?>"
                               name="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_productpage_title') ?>"
                               class="atkp-template-option"
                               value="<?php echo esc_attr( $parameters->get_productpage_title() ); ?>">
                    </td>

                    <td colspan="2">
                        <input type="checkbox" id="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_mark_links') ?>"
                               name="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_mark_links') ?>"
                               class=" atkp-template-option"
                               value="1" <?php echo checked( 1, $parameters->get_mark_links(), true ); ?>>
                        <label for="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_mark_links') ?>">
	                        <?php echo esc_html__( 'Mark affiliate links (*)', ATKP_TEMPLATE_POSTTYPE ) ?>
                        </label><br/>
                        <input type="text" id="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_affiliatechar') ?>"
                               name="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_affiliatechar') ?>"
                               class=" atkp-template-option"
                               value="<?php echo esc_attr( $parameters->get_affiliatechar() ); ?>">

						<?php ATKPTools::display_helptext( 'This character will be attached do your affiliate links. You can also use html for special formatting.' ) ?>

                    </td>
                </tr>

                <tr>
                    <th colspan="5" style="background-color:#bde4ea; padding:7px">
	                    <?php echo esc_html__( 'Additional Offers', ATKP_TEMPLATE_POSTTYPE ) ?>
                    </th>
                </tr>
                <tr>
                    <td colspan="5">           <?php ATKPTools::display_helptext( 'The system templates are not showing the price comparision by default. Activate this option to display different prices.' ) ?>
                    </td>
                </tr>
                <tr>
                    <th class="atkp-settings-group"></th>
                    <td>
                        <input type="checkbox" id="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_show_moreoffers') ?>"
                               name="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_show_moreoffers') ?>"
                               class="atkp-template-option"
                               value="1" <?php echo checked( 1, $parameters->get_show_moreoffers(), true ); ?>>
                        <label for="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_show_moreoffers') ?>">
	                        <?php echo esc_html__( 'Show additional offers', ATKP_TEMPLATE_POSTTYPE ) ?>
                        </label>
                    </td>
                    <td>
                        <label for="">
	                        <?php echo esc_html__( 'Template', ATKP_TEMPLATE_POSTTYPE ) ?>:
                        </label><br/>

                        <select id="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_moreoffers_template') ?>"
                                name="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_moreoffers_template') ?>"
                                class="atkp-template-option">
							<?php
							echo '<option value="">' . esc_html__( 'default', ATKP_TEMPLATE_POSTTYPE ) . '</option>';


							$templates         = atkp_template::get_list( true, false );
							$moreoffertemplate = $parameters->get_moreoffers_template();

							foreach ( $templates as $template => $caption ) {
								if ( $template == $moreoffertemplate ) {
									$sel = ' selected';
								} else {
									$sel = '';
								}

								echo '<option value="' . esc_attr( $template ) . '" ' . esc_attr( $sel ) . '>' . esc_html__( htmlentities( $caption ), ATKP_TEMPLATE_POSTTYPE ) . '</option>';
							}
							?>
                        </select>

                    </td>
                    <td>

                        <label for="">
	                        <?php echo esc_html__( 'Text', ATKP_TEMPLATE_POSTTYPE ) ?>:
                        </label><br/>
                        <input type="text" id="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_moreoffers_title') ?>"
                               name="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_moreoffers_title') ?>"
                               class="atkp-template-option"
                               value="<?php echo esc_attr($parameters->get_moreoffers_title()); ?>">

                    </td>
                    <td>

                        <label for="">
	                        <?php echo esc_html__( 'Maximum offers count', ATKP_TEMPLATE_POSTTYPE ) ?>:
                        </label><br/>
                        <input type="number" id="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_moreoffers_count') ?>"
                               name="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_moreoffers_count') ?>"
                               class="atkp-template-option"
                               value="<?php echo esc_attr($parameters->get_moreoffers_count()); ?>">

                    </td>
                </tr>

                <tr>
                    <th></th>
                    <td>
                        <input type="checkbox"
                               id="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_moreoffers_includemainoffer') ?>"
                               name="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_moreoffers_includemainoffer') ?>"
                               class="atkp-template-option"
                               value="1" <?php echo checked( 1, $parameters->get_moreoffers_includemainoffer(), true ); ?>>
                        <label for="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_moreoffers_includemainoffer') ?>">
	                        <?php echo esc_html__( 'Include main offer', ATKP_TEMPLATE_POSTTYPE ) ?>
                        </label>
                    </td>
                </tr>


				<?php
				do_action( 'atkp_settings_live_display_fields', $parameters );
				?>
            </table>
        </div>


        <style>

            .atkp-settings-group {
                width: 40px !important;
            }

            .atkp-settings-preview {
                float: left;
                width: 20%;
                min-width: 750px;
            }

            .atkp-settings-fields {
                float: left;
                width: min-content;
                margin-left: 20px;
            }

            @media only screen and (max-width: 2400px) {
                .atkp-settings-preview {
                    float: unset;
                    width: 100%;
                }

                .atkp-settings-fields {
                    float: unset;
                    width: 100%;
                    max-width: 900px;
                    margin-left: auto;
                    margin-right: auto;

                }

                .atkp-template-settings {

                    background-color: white;
                }
            }

            @media only screen and (max-width: 1820px) {
                .atkp-settings-fields {

                    max-width: initial;
                }
            }


        </style>


        <!--preview -->
        <table class="form-table" style="overflow-x: scroll;">
            <tr>
                <td>&nbsp;</td>
            </tr>
        </table>
		<?php
	}

	function css_detail_box_content( $post ) {
		$post_id = $post == null ? 0 : $post->ID;
		?>
        <table class="form-table" style="overflow-x: scroll;">

            <tr class="placeholderrow bladerow">
                <td colspan="2">

                    <textarea style="width:100%;height:160px" id="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_css') ?>"
                              data-lang="css"
                              name="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_css') ?>"><?php echo esc_textarea( ATKPTools::get_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_css', true ) ); ?></textarea>

					<?php if ( $post_id != '' ) { ?>
                        <div style="margin-top:10px"><?php echo esc_html__( 'CSS selector for this template:', ATKP_PLUGIN_PREFIX ) ?></div>
                        <code>.atkp-template-<?php echo esc_attr($post_id) ?> selector { }</code>
					<?php } ?>

                </td>
            </tr>

        </table>
		<?php
	}

	function template_detail_box_content( $post ) {
		wp_nonce_field( plugin_basename( __FILE__ ), 'template_detail_box_content_nonce' );

		$durations     = $this->get_template_types();
		$template_type = ATKPTools::get_post_setting( $post->ID, ATKP_TEMPLATE_POSTTYPE . '_template_type' );

		?>
        <table class="form-table" style="overflow-x: scroll;">
			<?php
			if ( $template_type == '6' ) {
				?>
                <input type="hidden" name="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_template_type') ?>" value="6"/>
				<?php
			} else {
				?>

                <tr>
                    <th scope="row">
                        <label for="">
	                        <?php echo esc_html__( 'Type', ATKP_PLUGIN_PREFIX ) ?>:
                        </label>
                    </th>
                    <td>
                        <select name="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_template_type') ?>"
                                id="<?php echo esc_attr(ATKP_TEMPLATE_POSTTYPE . '_template_type') ?>">
							<?php

							foreach ( $durations as $value => $name ) {
								if ( $value == $template_type ) {
									$sel = ' selected';
								} else {
									$sel = '';
								}

								echo '<option value="' . esc_attr( $value ) . '"' . esc_attr( $sel ) . '>' . esc_html__( $name, ATKP_TEMPLATE_POSTTYPE ) . '</option>';
							} ?>
                        </select>
						<?php ATKPTools::display_helptext( 'For simple product boxes you can use "product template". Search forms are fields for filtering. You can add more template types by installing extensions.', get_admin_url() . 'admin.php?page=ATKP_affiliate_toolkit-Extensions', __( 'View extensions', ATKP_PLUGIN_PREFIX ) ) ?>
                    </td>
                </tr>
				<?php
			}

			?>

            <tr>
                <td colspan="2">
					<?php
					foreach ( $durations as $id => $caption ) {
						if ( $template_type == '6' && $id != 6 ) {
							continue;
						}

						?>
                        <div id="<?php echo esc_attr('atkp-templatetype-' . $id); ?>" class="atkp-templatetype">
							<?php

							do_action( 'atkp_template_fields_' . $id, $post->ID );

							?>
                        </div>
						<?php
					}
					?>
                </td>
            </tr>


        </table>

        <div class="atkp-editor-helper" style="display:none">
            Editor help:
            <dl>
                <dt>Ctrl-F / Cmd-F</dt>
                <dd>Start searching</dd>
                <dt>Ctrl-G / Cmd-G</dt>
                <dd>Find next</dd>
                <dt>Shift-Ctrl-G / Shift-Cmd-G</dt>
                <dd>Find previous</dd>
                <dt>Shift-Ctrl-F / Cmd-Option-F</dt>
                <dd>Replace</dd>
                <dt>Shift-Ctrl-R / Shift-Cmd-Option-F</dt>
                <dd>Replace all</dd>
                <dt>Alt-F</dt>
                <dd>Persistent search (dialog doesn't autoclose,
                    enter to find next, Shift-Enter to find previous)
                </dd>
                <dt>Alt-G</dt>
                <dd>Jump to line</dd>
            </dl>
        </div>


        <script type="text/javascript">
            var $j = jQuery.noConflict();
            $j(document).ready(function () {


                var excludedIntelliSenseTriggerKeys =
                    {
                        "8": "backspace",
                        "9": "tab",
                        "13": "enter",
                        "16": "shift",
                        "17": "ctrl",
                        "18": "alt",
                        "19": "pause",
                        "20": "capslock",
                        "27": "escape",
                        "33": "pageup",
                        "34": "pagedown",
                        "35": "end",
                        "36": "home",
                        "37": "left",
                        "38": "up",
                        "39": "right",
                        "40": "down",
                        "45": "insert",
                        "46": "delete",
                        "91": "left window key",
                        "92": "right window key",
                        "93": "select",
                        "107": "add",
                        "109": "subtract",
                        "110": "decimal point",
                        "111": "divide",
                        "112": "f1",
                        "113": "f2",
                        "114": "f3",
                        "115": "f4",
                        "116": "f5",
                        "117": "f6",
                        "118": "f7",
                        "119": "f8",
                        "120": "f9",
                        "121": "f10",
                        "122": "f11",
                        "123": "f12",
                        "144": "numlock",
                        "145": "scrolllock",
                        "186": "semicolon",
                        "187": "equalsign",
                        "188": "comma",
                        "189": "dash",
                        "32": "space",
                        "191": "slash",
                        "192": "graveaccent",
                        "220": "backslash",
                        "222": "quote"
                    };


                var mainEditor = null;
                var filterEditor = null;
                var htmlelements = ["atkp_template_body", "atkp_template_mobilebody", "atkp_template_css", "atkp_template_filterbody", "atkp_template_header", "atkp_template_footer"];

                htmlelements.forEach(function (element) {

                    if (document.getElementById(element) == null)
                        console.log("html element not found: " + element);
                    else {

                        var lang = jQuery('#' + element).data('lang');
                        if (typeof lang === 'undefined')
                            lang = "htmlmixed";

                        //console.log("html element lang set: " + element);
                        var editor = CodeMirror.fromTextArea(document.getElementById(element), {
                            mode: lang,
                            autoCloseTags: true,
                            lineNumbers: true,
                            lineWrapping: true,
                            extraKeys: {
                                "Alt-F": "findPersistent",
                                "Ctrl-Space": "autocomplete"
                            },
                        });
                        //editor.setSize("100%", 500);

                        editor.on("keyup", function (editor, event) {
                            var __Cursor = editor.getDoc().getCursor();
                            var __Token = editor.getTokenAt(__Cursor);

                            if (!editor.state.completionActive &&
                                !excludedIntelliSenseTriggerKeys[(event.keyCode || event.which).toString()]) {
                                CodeMirror.commands.autocomplete(editor, null, {completeSingle: false});
                            }
                        });

                        if (element == "atkp_template_body") {
                            mainEditor = editor;
                        }
                        if (element == "atkp_template_filterbody") {
                            filterEditor = editor;
                        }
                    }


                });

                $j(".atkp-insert").click(function () {
                    var insertid = $j(this).data('insertid');
                    var html2;
                    if (!insertid) {
                        html2 = $j(this).data('insertvalue');
                    } else {
                        var html = $j('#' + insertid).html();

                        html2 = $j("<textarea/>").html(html).val()
                    }

                    insertString(mainEditor, html2);
                });

                $j(".atkp-filter-insert").click(function () {
                    var insertid = $j(this).data('insertid');
                    var html2;
                    if (!insertid) {
                        html2 = $j(this).data('insertvalue');
                    } else {
                        html2 = $j('#' + insertid).val();

                        //html2 = $j("<textarea/>").html(html).val()
                    }

                    insertString(filterEditor, html2);
                });

                $j("#atkp-editorhelp-link").click(function () {
                    $j('.atkp-editor-helper').show();
                    window.scrollTo(0, document.body.scrollHeight);
                });

                $j('.atkp-custom_styles').change(function () {
                    if ($j(this).is(":checked"))
                        $j('.atkp-template-settings').show();
                    else
                        $j('.atkp-template-settings').hide();
                });

                if ($j('.atkp-custom_styles').is(":checked")) {
                    $j('.atkp-template-settings').show();
                }

            });


            function insertString(editor, str) {

                var selection = editor.getSelection();

                if (selection.length > 0) {
                    editor.replaceSelection(str);
                } else {

                    var doc = editor.getDoc();
                    var cursor = doc.getCursor();

                    var pos = {
                        line: cursor.line,
                        ch: cursor.ch
                    }

                    doc.replaceRange(str, pos);

                }
                editor.focus();
            }

            function insertTextAtCursor(editor, text) {
                var doc = editor.getDoc();
                var cursor = doc.getCursor();
                doc.replaceRange(text, cursor);
            }


        </script>

        <style>
            .CodeMirror {
                border: 1px solid #bde4ea;
                height: auto;
            }

            .CodeMirror-scroll {
                min-height: 350px; /* the minimum height */
            }
        </style>

        <script type="text/javascript">

            var $j = jQuery.noConflict();
            $j(document).ready(function () {


                $j('#<?php echo esc_js(ATKP_TEMPLATE_POSTTYPE . '_template_type') ?>').change(function () {

                    var templatetype = $j('#<?php echo esc_js(ATKP_TEMPLATE_POSTTYPE . '_template_type') ?>').val();

                    $j('.atkp-templatetype').hide();

                    $j('#atkp-templatetype-' + templatetype).show();

                    $j('.no-search').hide();

                    if (templatetype != 5) {

                        $j('.no-search').show();
                    }


                });
                $j('#<?php echo esc_js(ATKP_TEMPLATE_POSTTYPE . '_template_type') ?>').trigger("change");


            });


        </script>

		<?php


	}


	function template_detail_save( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		$nounce = ATKPTools::get_post_parameter( 'template_detail_box_content_nonce', 'string' );

		if ( ! wp_verify_nonce( $nounce, plugin_basename( __FILE__ ) ) ) {
			return;
		}

		$post = get_post( $post_id );

		$posttype = $post->post_type; //ATKPTools::get_post_parameter('post_type', 'string');

		if ( ATKP_TEMPLATE_POSTTYPE != $posttype ) {
			return;
		}


		$templatetype = ATKPTools::get_post_parameter( ATKP_TEMPLATE_POSTTYPE . '_template_type', 'int' );


		//global template settings
		ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_template_type', $templatetype );


		do_action( 'atkp_template_savefields_' . $templatetype, $post_id );
		do_action( 'atkp_template_savefields', $post_id );

		ATKPTools::write_global_styles();
	}

}