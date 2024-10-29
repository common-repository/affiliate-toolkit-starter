<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class atkp_settings_display {
	/**
	 * Construct the plugin object
	 */
	public function __construct( $pluginbase ) {


	}




	public function display_configuration_page() {
		if ( ATKPTools::exists_post_parameter( 'savedisplay' ) && check_admin_referer( 'save', 'save' ) ) {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'You do not have sufficient permissions to access this page', ATKP_PLUGIN_PREFIX ) );
			}

			//speichern der template settings

			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_box_background_color', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_box_background_color', 'string' ) );
			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_box_border_color', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_box_border_color', 'string' ) );
			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_box_text_color', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_box_text_color', 'string' ) );
			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_box_textlink_color', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_box_textlink_color', 'string' ) );
			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_box_textlink_hovercolor', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_box_textlink_hovercolor', 'string' ) );

			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_dropdown_textlink_color', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_dropdown_textlink_color', 'string' ) );
			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_dropdown_textlink_hovercolor', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_dropdown_textlink_hovercolor', 'string' ) );

			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_amountsaved_color', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_amountsaved_color', 'string' ) );
			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_price_color', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_price_color', 'string' ) );
			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_listprice_color', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_listprice_color', 'string' ) );


			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_primbtn_background_color', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_primbtn_background_color', 'string' ) );
			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_primbtn_hoverbackground_color', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_primbtn_hoverbackground_color', 'string' ) );
			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_primbtn_foreground_color', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_primbtn_foreground_color', 'string' ) );

			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_primbtn_border_color', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_primbtn_border_color', 'string' ) );
			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_primbtn_border_color', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_primbtn_border_color', 'string' ) );
			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_secbtn_background_color', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_secbtn_background_color', 'string' ) );
			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_secbtn_hoverbackground_color', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_secbtn_hoverbackground_color', 'string' ) );
			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_secbtn_foreground_color', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_secbtn_foreground_color', 'string' ) );
			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_secbtn_border_color', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_secbtn_border_color', 'string' ) );


			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_box_badge_color', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_box_badge_color', 'string' ) );


			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_showprice', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_showprice', 'bool' ) );
			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_showshopname', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_showshopname', 'bool' ) );

			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_linkrating', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_linkrating', 'bool' ) );
			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_showstarrating', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_showstarrating', 'bool' ) );

			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_showbaseprice', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_showbaseprice', 'bool' ) );
			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_showlistprice', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_showlistprice', 'bool' ) );

			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_hideemptystars', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_hideemptystars', 'bool' ) );

			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_hideemptyrating', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_hideemptyrating', 'bool' ) );
			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_linkimage', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_linkimage', 'bool' ) );

			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_showpricediscount', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_showpricediscount', 'bool' ) );
			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_showrating', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_showrating', 'bool' ) );

			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_secbtn_image', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_secbtn_image', 'string' ) );
			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_primbtn_image', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_primbtn_image', 'string' ) );

			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_mark_links', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_mark_links', 'bool' ) );

			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_btn_radius', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_btn_radius', 'int' ) );
			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_box_radius', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_box_radius', 'int' ) );

			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_show_disclaimer', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_show_disclaimer', 'bool' ) );
			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_disclaimer_text', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_disclaimer_text', 'html' ) );
			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_show_priceinfo', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_show_priceinfo', 'bool' ) );
			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_priceinfo_text', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_priceinfo_text', 'html' ) );


			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_affiliatechar', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_affiliatechar', 'html' ) );

			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_hideprocontra', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_hideprocontra', 'bool' ) );
			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_primbtn_size', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_primbtn_size', 'string' ) );
			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_secbtn_size', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_secbtn_size', 'string' ) );

			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_box_show_shadow', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_box_show_shadow', 'bool' ) );


			//speichern der einstellungen


			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_outputashtml', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_outputashtml', 'bool' ) );
			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_pricecomparisongroupshops', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_pricecomparisongroupshops', 'bool' ) );

			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_hideerrormessages', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_hideerrormessages', 'bool' ) );

			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_enable_ajax_loading', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_enable_ajax_loading', 'bool' ) );
			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_enable_ajax_handler', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_enable_ajax_handler', 'bool' ) );

			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_showadminsection', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_showadminsection', 'bool' ) );

			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_show_credits', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_show_credits', 'bool' ) );
			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_credits_ref', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_credits_ref', 'string' ) );


			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_short_title_length', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_short_title_length', 'int' ) );

			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_show_moreoffers', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_show_moreoffers', 'bool' ) );
			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_moreoffers_includemainoffer', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_moreoffers_includemainoffer', 'bool' ) );

			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_moreoffers_template', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_moreoffers_template', 'string' ) );
			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_moreoffers_title', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_moreoffers_title', 'string' ) );
			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_moreoffers_count', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_moreoffers_count', 'int' ) );


			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_show_nota_template', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_show_nota_template', 'bool' ) );
			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_nota_template', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_nota_template', 'string' ) );
			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_nota_disable_link', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_nota_disable_link', 'bool' ) );

			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_add_to_cart', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_add_to_cart', 'string' ) );
			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_title_link_type', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_title_link_type', 'string' ) );

			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_productpage_title', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_productpage_title', 'html' ) );


			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_review_text', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_review_text', 'string' ) );
			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_review_color', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_review_color', 'string' ) );




			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_test_score1_text', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_test_score1_text', 'string' ) );
			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_test_score2_text', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_test_score2_text', 'string' ) );
			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_test_score3_text', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_test_score3_text', 'string' ) );
			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_test_score4_text', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_test_score4_text', 'string' ) );
			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_test_score5_text', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_test_score5_text', 'string' ) );
















			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_list_default_count', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_list_default_count', 'int' ) );
			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_description_length', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_description_length', 'int' ) );
			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_feature_count', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_feature_count', 'int' ) );
			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_boxcontent', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_boxcontent', 'string' ) );

			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_css_inline', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_css_inline', 'string' ) );


			do_action( 'atkp_settings_display_savefields' );

			ATKPTools::write_global_scripts();
			ATKPTools::write_global_styles();

		}

		ATKPTools::set_setting( 'atkp_display_check_done', 1 );
		?>
        <div class="atkp-content wrap">
            <div class="inner">
                <!-- <h2><?php echo esc_html__( 'Affiliate Toolkit - Advanced Settings', ATKP_PLUGIN_PREFIX ) ?></h2>      -->

                <form method="POST"
                      action="?page=<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_affiliate_toolkit-plugin&tab=display_configuration_page') ?>">
                    <!--_affiliate_toolkit-bestseller-->
					<?php wp_nonce_field( "save", "save" ); ?>
                    <table class="form-table" style="width:100%">


                        <tr>
                            <th scope="row" style="background-color:#bde4ea; padding:7px" colspan="2">
	                            <?php echo esc_html__( 'Display', ATKP_PLUGIN_PREFIX ) ?>
                            </th>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                        </tr>
                    </table>

                    <div class="atkp-settings-preview">
						<?php
						$output = new atkp_output();

						echo "<link rel='stylesheet' id='atkp-styles-css' href='" . esc_url(plugins_url( '/dist/style.css', ATKP_PLUGIN_FILE )) . "' media='all' />";
						echo "<script src='" . esc_attr(plugins_url( plugins_url( '/dist/script.js', ATKP_PLUGIN_FILE ) ) ) . "' id='atkp-scripts-js'></script>";

						echo '<style>';
						echo ($output->get_css_output());
						echo '</style>';
						echo '<script>';
						echo ($output->get_js_output());
						echo '</script>';

						$template_id      = 'bestseller';
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
                        <script type="application/json" id="<?php echo esc_attr('atkp-data-parameters-' .  $uid) ?>">
                           <?php echo $str_params ; ?>

                        </script>
                        <script type="application/json"
                                id="<?php echo esc_attr( 'atkp-data-products-' . $uid ) ?>">
                            <?php echo  $str_products; ?></script>

                        <div style="max-width:700px;margin-left:auto;margin-right:auto;padding: 20px; border-left: 1px solid #005162;border-right: 1px solid #005162">
                            <div style="">
                                <label for="atkp-templateselector">
	                                <?php echo esc_html__( 'Preview', ATKP_PLUGIN_PREFIX ) ?>:
                                </label>
                                <select class="atkp-template-option"
                                        name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_template') ?>"
                                        id="atkp-templateselector">
									<?php

									$templates = atkp_template::get_preview_list( true, false );

									foreach ( $templates as $template => $caption ) {
										if ( ! is_numeric( $template ) ) {
											echo '<option value="' . esc_attr( $template ) . '" ' . ( $template == $template_id ? ' selected' : '' ) . '>' . esc_html__( htmlentities( $caption ), ATKP_PLUGIN_PREFIX ) . '</option>';
										}
									}

									?>
                                </select>

                                <br/><br/><code id="atkp-shortcode" style="vertical-align: middle">[atkp template=''
                                    ids=''][/atkp]</code>
                            </div>
                            <p style="margin-bottom: 1.6em;-webkit-mask-image: -webkit-gradient(linear,  left bottom,left top, from(rgba(0,0,0,1)), to(rgba(0,0,0,0)));">
                                Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor
                                invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et
                                accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata
                                sanctus est Lorem ipsum dolor sit amet.
                            </p>
                            <div class="atkp-ajax-container" style="margin-bottom:10px;" data-uid="<?php echo esc_html($uid) ?>"
                                 data-endpointurl="<?php echo esc_url(ATKPTools::get_endpointurl()); ?>"></div>
                            <p style=" -webkit-mask-image: -webkit-gradient(linear, left top, left bottom, from(rgba(0,0,0,1)), to(rgba(0,0,0,0)));">
                                Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor
                                invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et
                                accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata
                                sanctus est Lorem ipsum dolor sit amet.
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
                                                atkpparameters[name.replace('ATKP_', '')] = true;
                                            } else {
                                                atkpparameters[name.replace('ATKP_', '')] = false;
                                            }
                                        } else {
                                            if (value != '')
                                                atkpparameters[name.replace('ATKP_', '')] = value;
                                        }
                                    });

                                    atkpparameters['templateid'] = $j('#atkp-templateselector').val();

                                    $j('#atkp-shortcode').html("[atkp template='" + atkpparameters['templateid'] + "' ids=''][/atkp]");

                                    console.log(atkpparameters);
                                    $j(obj).html('');
                                    $j(obj).addClass('atkp-spinloader-round');

                                    lastRequest = $j.post(endpointurl,
                                        {
                                            action: 'atkp_render_template',
                                            products: JSON.stringify(atkpproducts),
                                            parameters: JSON.stringify(atkpparameters),
                                            preview: true
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
                        <table style="width:100%;   margin-left: auto;    margin-right: auto;"
                               class="atkp-template-settings">
                            <tr>
                                <th colspan="5" style="background-color:#bde4ea; padding:7px">
	                                <?php echo esc_html__( 'Colors', ATKP_PLUGIN_PREFIX ) ?>
                                </th>
                            </tr>
                            <tr>
                                <th class="atkp-settings-group">
                                    <span style="writing-mode: vertical-lr; ">Box</span>
                                </th>
                                <td style="width:25%">
                                    <label for="<?php echo esc_attr_e(ATKP_PLUGIN_PREFIX . '_box_background_color') ?>">
	                                    <?php echo esc_html__( 'Background', ATKP_PLUGIN_PREFIX ) ?>
                                    </label><br/>

                                    <input type="text" class="color-field atkp-template-option"
                                           id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_box_background_color') ?>"
                                           name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_box_background_color') ?>"
                                           value=" <?php echo esc_attr( atkp_options::$loader->get_box_background_color() ) ?>">

                                </td>

                                <td style="width:25%">
                                    <label for="<?php echo esc_attr_e(ATKP_PLUGIN_PREFIX . '_box_border_color') ?>">
	                                    <?php echo esc_html__( 'Border', ATKP_PLUGIN_PREFIX ) ?>
                                    </label><br/>

                                    <input type="text" class="color-field atkp-template-option"
                                           id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_box_border_color') ?>"
                                           name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_box_border_color') ?>"
                                           value=" <?php echo esc_attr( atkp_options::$loader->get_box_border_color() ) ?>">

                                </td>

                                <td style="width:25%">
                                    <label for="<?php echo esc_attr_e(ATKP_PLUGIN_PREFIX . '_box_text_color') ?>">
	                                    <?php echo esc_html__( 'Foreground', ATKP_PLUGIN_PREFIX ) ?>
                                    </label><br/>

                                    <input type="text" class="color-field atkp-template-option"
                                           id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_box_text_color') ?>"
                                           name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_box_text_color') ?>"
                                           value=" <?php echo esc_attr( atkp_options::$loader->get_box_text_color() ) ?>">

                                </td>

                                <td style="width:25%">
                                    <label for="<?php echo esc_attr_e(ATKP_PLUGIN_PREFIX . '_box_badge_color') ?>">
	                                    <?php echo esc_html__( 'Badge', ATKP_PLUGIN_PREFIX ) ?>
                                    </label><br/>

                                    <input type="text" class="color-field atkp-template-option"
                                           id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_box_badge_color') ?>"
                                           name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_box_badge_color') ?>"
                                           value=" <?php echo esc_attr( atkp_options::$loader->get_box_badge_color() ) ?>">

                                </td>

                            </tr>
                            <tr>
                                <th class="atkp-settings-group">
                                    <span style="writing-mode: vertical-lr; "><?php echo esc_html__( 'Link', ATKP_PLUGIN_PREFIX ) ?></span>
                                </th>
                                <td>
                                    <label for="<?php echo esc_attr_e(ATKP_PLUGIN_PREFIX . '_box_textlink_color') ?>">
	                                    <?php echo esc_html__( 'Foreground', ATKP_PLUGIN_PREFIX ) ?>
                                    </label><br/>

                                    <input type="text" class="color-field atkp-template-option"
                                           id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_box_textlink_color') ?>"
                                           name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_box_textlink_color') ?>"
                                           value=" <?php echo esc_attr( atkp_options::$loader->get_box_textlink_color() ) ?>">

                                </td>
                                <td>
                                    <label for="<?php echo esc_attr_e(ATKP_PLUGIN_PREFIX . '_box_textlink_hovercolor') ?>">
	                                    <?php echo esc_html__( 'Hover', ATKP_PLUGIN_PREFIX ) ?>
                                    </label><br/>

                                    <input type="text" class="color-field atkp-template-option"
                                           id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_box_textlink_hovercolor') ?>"
                                           name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_box_textlink_hovercolor') ?>"
                                           value=" <?php echo esc_attr( atkp_options::$loader->get_box_textlink_hovercolor() ) ?>">

                                </td>

                                <td>
                                    <label for="<?php echo esc_attr_e(ATKP_PLUGIN_PREFIX . '_dropdown_textlink_color') ?>">
	                                    <?php echo esc_html__( 'Dropdown Foreground', ATKP_PLUGIN_PREFIX ) ?>
                                    </label><br/>

                                    <input type="text" class="color-field atkp-template-option"
                                           id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_dropdown_textlink_color') ?>"
                                           name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_dropdown_textlink_color') ?>"
                                           value=" <?php echo esc_attr( atkp_options::$loader->get_dropdown_text_color() ) ?>">

                                </td>
                                <td>
                                    <label for="<?php echo esc_attr_e(ATKP_PLUGIN_PREFIX . '_dropdown_textlink_hovercolor') ?>">
	                                    <?php echo esc_html__( 'Dropdown Hover', ATKP_PLUGIN_PREFIX ) ?>
                                    </label><br/>

                                    <input type="text" class="color-field atkp-template-option"
                                           id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_dropdown_textlink_hovercolor') ?>"
                                           name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_dropdown_textlink_hovercolor') ?>"
                                           value=" <?php echo esc_attr( atkp_options::$loader->get_dropdown_text_hovercolor() ) ?>">

                                </td>
                            </tr>
                            <tr>
                                <th class="atkp-settings-group">
                                    <span style="writing-mode: vertical-lr; "><?php echo esc_html__( 'Button 1', ATKP_PLUGIN_PREFIX ) ?></span>
                                </th>
                                <td>
                                    <label for="<?php echo esc_attr_e(ATKP_PLUGIN_PREFIX . '_primbtn_background_color') ?>">
	                                    <?php echo esc_html__( 'Background', ATKP_PLUGIN_PREFIX ) ?>
                                    </label><br/>

                                    <input type="text" class="color-field atkp-template-option"
                                           id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_primbtn_background_color') ?>"
                                           name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_primbtn_background_color') ?>"
                                           value=" <?php echo esc_attr( atkp_options::$loader->get_primbtn_background_color() ) ?>">

                                </td>
                                <td>
                                    <label for="<?php echo esc_attr_e(ATKP_PLUGIN_PREFIX . '_primbtn_hoverbackground_color') ?>">
	                                    <?php echo esc_html__( 'Hover Background', ATKP_PLUGIN_PREFIX ) ?>
                                    </label><br/>
                                    <input type="text" class="color-field atkp-template-option"
                                           id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_primbtn_hoverbackground_color') ?>"
                                           name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_primbtn_hoverbackground_color') ?>"
                                           value=" <?php echo esc_attr( atkp_options::$loader->get_primbtn_hoverbackground_color() ) ?>">
                                </td>
                                <td>
                                    <label for="<?php echo esc_attr_e(ATKP_PLUGIN_PREFIX . '_primbtn_foreground_color') ?>">
	                                    <?php echo esc_html__( 'Foreground', ATKP_PLUGIN_PREFIX ) ?>
                                    </label><br/>
                                    <input type="text" class="color-field atkp-template-option"
                                           id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_primbtn_foreground_color') ?>"
                                           name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_primbtn_foreground_color') ?>"
                                           value=" <?php echo esc_attr( atkp_options::$loader->get_primbtn_foreground_color() ) ?>">
                                </td>
                                <td>
                                    <label for="<?php echo esc_attr_e(ATKP_PLUGIN_PREFIX . '_primbtn_border_color') ?>">
	                                    <?php echo esc_html__( 'Border', ATKP_PLUGIN_PREFIX ) ?>
                                    </label><br/>

                                    <input type="text" class="color-field atkp-template-option"
                                           id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_primbtn_border_color') ?>"
                                           name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_primbtn_border_color') ?>"
                                           value=" <?php echo esc_attr( atkp_options::$loader->get_primbtn_border_color() ) ?>">

                            </tr>
                            <tr>
                                <th class="atkp-settings-group">
                                    <span style="writing-mode: vertical-lr; ">Button 2</span>
                                </th>
                                <td>
                                    <label for="<?php echo esc_attr_e(ATKP_PLUGIN_PREFIX . '_secbtn_background_color') ?>">
	                                    <?php echo esc_html__( 'Background', ATKP_PLUGIN_PREFIX ) ?>
                                    </label><br/>
                                    <input type="text" class="color-field atkp-template-option"
                                           id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_secbtn_background_color') ?>"
                                           name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_secbtn_background_color') ?>"
                                           value=" <?php echo esc_attr( atkp_options::$loader->get_secbtn_background_color() ) ?>">

                                </td>
                                <td>
                                    <label for="<?php echo esc_attr_e(ATKP_PLUGIN_PREFIX . '_secbtn_hoverbackground_color') ?>">
	                                    <?php echo esc_html__( 'Hover Background', ATKP_PLUGIN_PREFIX ) ?>
                                    </label><br/>
                                    <input type="text" class="color-field atkp-template-option"
                                           id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_secbtn_hoverbackground_color') ?>"
                                           name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_secbtn_hoverbackground_color') ?>"
                                           value=" <?php echo esc_attr( atkp_options::$loader->get_secbtn_hoverbackground_color() ) ?>">
                                </td>
                                <td>
                                    <label for="<?php echo esc_attr_e(ATKP_PLUGIN_PREFIX . '_secbtn_foreground_color') ?>">
	                                    <?php echo esc_html__( 'Foreground', ATKP_PLUGIN_PREFIX ) ?>
                                    </label><br/>
                                    <input type="text" class="color-field atkp-template-option"
                                           id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_secbtn_foreground_color') ?>"
                                           name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_secbtn_foreground_color') ?>"
                                           value=" <?php echo esc_attr( atkp_options::$loader->get_secbtn_foreground_color() ) ?>">
                                </td>
                                <td>
                                    <label for="<?php echo esc_attr_e(ATKP_PLUGIN_PREFIX . '_secbtn_border_color') ?>">
	                                    <?php echo esc_html__( 'Border', ATKP_PLUGIN_PREFIX ) ?>
                                    </label><br/>
                                    <input type="text" class="color-field atkp-template-option"
                                           id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_secbtn_border_color') ?>"
                                           name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_secbtn_border_color') ?>"
                                           value=" <?php echo esc_attr( atkp_options::$loader->get_secbtn_border_color() ) ?>">
                                </td>
                            </tr>


                            <tr>
                                <th class="atkp-settings-group">
                                    <span style="writing-mode: vertical-lr; ">Prices</span>
                                </th>
                                <td>
                                    <label for="<?php echo esc_attr_e(ATKP_PLUGIN_PREFIX . '_listprice_color') ?>">
	                                    <?php echo esc_html__( 'List price', ATKP_PLUGIN_PREFIX ) ?>
                                    </label><br/>
                                    <input type="text" class="color-field atkp-template-option"
                                           id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_listprice_color') ?>"
                                           name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_listprice_color') ?>"
                                           value=" <?php echo esc_attr( atkp_options::$loader->get_listprice_color() ) ?>">

                                </td>
                                <td>
                                    <label for="<?php echo esc_attr_e(ATKP_PLUGIN_PREFIX . '_amountsaved_color') ?>">
	                                    <?php echo esc_html__( 'Amount saved', ATKP_PLUGIN_PREFIX ) ?>
                                    </label><br/>
                                    <input type="text" class="color-field atkp-template-option"
                                           id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_amountsaved_color') ?>"
                                           name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_amountsaved_color') ?>"
                                           value=" <?php echo esc_attr( atkp_options::$loader->get_amountsaved_color() ) ?>">
                                </td>
                                <td>
                                    <label for="<?php echo esc_attr_e(ATKP_PLUGIN_PREFIX . '_price_color') ?>">
	                                    <?php echo esc_html__( 'Price', ATKP_PLUGIN_PREFIX ) ?>
                                    </label><br/>
                                    <input type="text" class="color-field atkp-template-option"
                                           id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_price_color') ?>"
                                           name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_price_color') ?>"
                                           value=" <?php echo esc_attr( atkp_options::$loader->get_price_color() ) ?>">
                                </td>
                            </tr>


                            <tr>
                                <th colspan="5" style="background-color:#bde4ea; padding:7px">
	                                <?php echo esc_html__( 'Display', ATKP_PLUGIN_PREFIX ) ?>
                                </th>
                            </tr>
                            <tr>
                                <th class="atkp-settings-group"></th>
                                <td>
                                    <input type="checkbox" id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_showshopname') ?>"
                                           name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_showshopname') ?>"
                                           class=" atkp-template-option"
                                           value="1" <?php echo checked( 1, get_option( ATKP_PLUGIN_PREFIX . '_showshopname', 1 ), true ); ?>>
                                    <label for="<?php echo esc_attr_e(ATKP_PLUGIN_PREFIX . '_showshopname') ?>">
	                                    <?php echo esc_html__( 'Show shop', ATKP_PLUGIN_PREFIX ) ?>
                                    </label>

                                </td>

                                <td>
                                    <input type="checkbox" id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_showstarrating') ?>"
                                           name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_showstarrating') ?>"
                                           class=" atkp-template-option"
                                           value="1" <?php echo checked( 1, get_option( ATKP_PLUGIN_PREFIX . '_showstarrating', 1 ), true ); ?>>
                                    <label for="<?php echo esc_attr_e(ATKP_PLUGIN_PREFIX . '_showstarrating') ?>">
	                                    <?php echo esc_html__( 'Show star rating', ATKP_PLUGIN_PREFIX ) ?>
                                    </label>
                                </td>
                                <td>
                                    <input type="checkbox" id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_linkrating') ?>"
                                           name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_linkrating') ?>"
                                           class=" atkp-template-option"
                                           value="1" <?php echo checked( 1, get_option( ATKP_PLUGIN_PREFIX . '_linkrating', 0 ), true ); ?>>
                                    <label for="<?php echo esc_attr_e(ATKP_PLUGIN_PREFIX . '_linkrating') ?>">
	                                    <?php echo esc_html__( 'Link rating', ATKP_PLUGIN_PREFIX ) ?>
                                    </label>
                                </td>
                                <td>
                                    <input type="checkbox" id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_linkimage') ?>"
                                           name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_linkimage') ?>"
                                           class=" atkp-template-option"
                                           value="1" <?php echo checked( 1, get_option( ATKP_PLUGIN_PREFIX . '_linkimage', 0 ), true ); ?>>
                                    <label for="<?php echo esc_attr_e(ATKP_PLUGIN_PREFIX . '_linkimage') ?>">
	                                    <?php echo esc_html__( 'Link product image', ATKP_PLUGIN_PREFIX ) ?>
                                    </label>
                                </td>

                            </tr>
                            <tr>
                                <th class="atkp-settings-group"></th>
                                <td>
                                    <input type="checkbox" id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_showprice') ?>"
                                           name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_showprice') ?>"
                                           class=" atkp-template-option"
                                           value="1" <?php echo checked( 1, get_option( ATKP_PLUGIN_PREFIX . '_showprice', 1 ), true ); ?>>
                                    <label for="<?php echo esc_attr_e(ATKP_PLUGIN_PREFIX . '_showprice') ?>">
	                                    <?php echo esc_html__( 'Show price', ATKP_PLUGIN_PREFIX ) ?>
                                    </label>
                                </td>
                                <td>
                                    <input type="checkbox" id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_showlistprice') ?>"
                                           name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_showlistprice') ?>"
                                           class=" atkp-template-option"
                                           value="1" <?php echo checked( 1, atkp_options::$loader->get_showlistprice(), true ); ?>>
                                    <label for="<?php echo esc_attr_e(ATKP_PLUGIN_PREFIX . '_showlistprice') ?>">
	                                    <?php echo esc_html__( 'Show list price', ATKP_PLUGIN_PREFIX ) ?>
                                    </label>
                                </td>
                                <td>
                                    <input type="checkbox" id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_showbaseprice') ?>"
                                           name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_showbaseprice') ?>"
                                           class=" atkp-template-option"
                                           value="1" <?php echo checked( 1, get_option( ATKP_PLUGIN_PREFIX . '_showbaseprice', 1 ), true ); ?>>
                                    <label for="<?php echo esc_attr_e(ATKP_PLUGIN_PREFIX . '_showbaseprice') ?>">
	                                    <?php echo esc_html__( 'Show base price', ATKP_PLUGIN_PREFIX ) ?>
                                    </label>
                                </td>
                                <td>
                                    <input type="checkbox" id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_showpricediscount') ?>"
                                           name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_showpricediscount') ?>"
                                           class=" atkp-template-option"
                                           value="1" <?php echo checked( 1, get_option( ATKP_PLUGIN_PREFIX . '_showpricediscount', 1 ), true ); ?>>
                                    <label for="<?php echo esc_attr_e(ATKP_PLUGIN_PREFIX . '_showpricediscount') ?>">
	                                    <?php echo esc_html__( 'Show price discount', ATKP_PLUGIN_PREFIX ) ?>
                                    </label>
                                </td>

                            </tr>
                            <tr>
                                <th class="atkp-settings-group"></th>

                                <td>
                                    <input type="checkbox" id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_hideemptystars') ?>"
                                           name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_hideemptystars') ?>"
                                           class=" atkp-template-option"
                                           value="1" <?php echo checked( 1, get_option( ATKP_PLUGIN_PREFIX . '_hideemptystars', 0 ), true ); ?>>
                                    <label for="<?php echo esc_attr_e(ATKP_PLUGIN_PREFIX . '_hideemptystars') ?>">
	                                    <?php echo esc_html__( 'Hide ratings with 0 stars', ATKP_PLUGIN_PREFIX ) ?>
                                    </label>
                                </td>
                                <td>
                                    <input type="checkbox" id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_hideemptyrating') ?>"
                                           name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_hideemptyrating') ?>"
                                           class=" atkp-template-option"
                                           value="1" <?php echo checked( 1, get_option( ATKP_PLUGIN_PREFIX . '_hideemptyrating', 0 ), true ); ?>>
                                    <label for="<?php echo esc_attr_e(ATKP_PLUGIN_PREFIX . '_hideemptyrating') ?>">
	                                    <?php echo esc_html__( 'Hide reviews without value', ATKP_PLUGIN_PREFIX ) ?>
                                    </label>
                                </td>
                                <td>
                                    <input type="checkbox" id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_hideprocontra') ?>"
                                           name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_hideprocontra') ?>"
                                           class=" atkp-template-option"
                                           value="1" <?php echo checked( 1, get_option( ATKP_PLUGIN_PREFIX . '_hideprocontra', 0 ), true ); ?>>
                                    <label for="<?php echo esc_attr_e(ATKP_PLUGIN_PREFIX . '_hideprocontra') ?>">
	                                    <?php echo esc_html__( 'Hide pro/contra', ATKP_PLUGIN_PREFIX ) ?>
                                    </label>
                                </td>

                                <td>
                                    <input type="checkbox" id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_box_show_shadow') ?>"
                                           name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_box_show_shadow') ?>"
                                           class=" atkp-template-option"
                                           value="1" <?php echo checked( 1, atkp_options::$loader->get_box_show_shadow(), true ); ?>>
                                    <label for="<?php echo esc_attr_e(ATKP_PLUGIN_PREFIX . '_box_show_shadow') ?>">
	                                    <?php echo esc_html__( 'Show Box shadow', ATKP_PLUGIN_PREFIX ) ?>
                                    </label>
                                </td>

                            </tr>
                            <tr>
                                <th class="atkp-settings-group"></th>
                                <td style="vertical-align: top;">
                                    <label for="">
	                                    <?php echo esc_html__( 'Button 1 image', ATKP_PLUGIN_PREFIX ) ?>:
                                    </label>

                                    <select id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_primbtn_image') ?>"
                                            class="atkp-template-option"
                                            name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_primbtn_image') ?>" style="">
										<?php
										$selected = get_option( ATKP_PLUGIN_PREFIX . '_primbtn_image' );

										echo '<option value="" ' . ( $selected == '' ? 'selected' : '' ) . ' >' . esc_html__( 'No image ', ATKP_PLUGIN_PREFIX ) . '</option>';

										echo '<option value="amz_black" ' . ( $selected == 'amz_black' ? 'selected' : '' ) . '>' . esc_html__( 'Amazon black', ATKP_PLUGIN_PREFIX ) . '</option>';
										echo '<option value="amz_white" ' . ( $selected == 'amz_white' ? 'selected' : '' ) . '>' . esc_html__( 'Amazon white', ATKP_PLUGIN_PREFIX ) . '</option>';
										echo '<option value="cart_black" ' . ( $selected == 'cart_black' ? 'selected' : '' ) . '>' . esc_html__( 'Cart black', ATKP_PLUGIN_PREFIX ) . '</option>';
										echo '<option value="cart_white" ' . ( $selected == 'cart_white' ? 'selected' : '' ) . '>' . esc_html__( 'Cart white', ATKP_PLUGIN_PREFIX ) . '</option>';

										?>
                                    </select>
                                </td>

                                <td style="vertical-align: top;">
                                    <label for="">
	                                    <?php echo esc_html__( 'Button 2 image', ATKP_PLUGIN_PREFIX ) ?>:
                                    </label>

                                    <select id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_secbtn_image') ?>"
                                            class="atkp-template-option"
                                            name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_secbtn_image') ?>" style="">
										<?php
										$selected = get_option( ATKP_PLUGIN_PREFIX . '_secbtn_image' );

										echo '<option value="no_image" ' . ( $selected == '' || $selected == 'no_image' ? 'selected' : '' ) . ' >' . esc_html__( 'No image ', ATKP_PLUGIN_PREFIX ) . '</option>';

										echo '<option value="amz_black" ' . ( $selected == 'amz_black' ? 'selected' : '' ) . '>' . esc_html__( 'Amazon black', ATKP_PLUGIN_PREFIX ) . '</option>';
										echo '<option value="amz_white" ' . ( $selected == 'amz_white' ? 'selected' : '' ) . '>' . esc_html__( 'Amazon white', ATKP_PLUGIN_PREFIX ) . '</option>';
										echo '<option value="cart_black" ' . ( $selected == 'cart_black' ? 'selected' : '' ) . '>' . esc_html__( 'Cart black', ATKP_PLUGIN_PREFIX ) . '</option>';
										echo '<option value="cart_white" ' . ( $selected == 'cart_white' ? 'selected' : '' ) . '>' . esc_html__( 'Cart white', ATKP_PLUGIN_PREFIX ) . '</option>';

										?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th colspan="5" style="background-color:#bde4ea; padding:7px">
	                                <?php echo esc_html__( 'Radius', ATKP_PLUGIN_PREFIX ) ?>
                                </th>
                            </tr>
                            <tr>
                                <th class="atkp-settings-group"></th>
                                <td>
                                    <label for="<?php echo esc_attr_e(ATKP_PLUGIN_PREFIX . '_btn_radius') ?>">
	                                    <?php echo esc_html__( 'Button Radius', ATKP_PLUGIN_PREFIX ) ?>
                                    </label>
                                    <input type="number" id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_btn_radius') ?>"
                                           name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_btn_radius') ?>"
                                           class=" atkp-template-option"
                                           value="<?php echo esc_attr( atkp_options::$loader->get_button_radius() ) ?>">

                                </td>
                                <td>
                                    <label for="<?php echo esc_attr_e(ATKP_PLUGIN_PREFIX . '_box_radius') ?>">
	                                    <?php echo esc_html__( 'Box Radius', ATKP_PLUGIN_PREFIX ) ?>
                                    </label>
                                    <input type="number" id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_box_radius') ?>"
                                           name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_box_radius') ?>"
                                           class=" atkp-template-option"
                                           value="<?php echo esc_attr( atkp_options::$loader->get_box_radius() ) ?>">

                                </td>
                            </tr>
                            <tr>
                                <th colspan="5" style="background-color:#bde4ea; padding:7px">
	                                <?php echo esc_html__( 'Size & Length', ATKP_PLUGIN_PREFIX ) ?>
                                </th>
                            </tr>
                            <tr>
                                <th class="atkp-settings-group"></th>
                                <td>
                                    <label for="<?php echo esc_attr_e(ATKP_PLUGIN_PREFIX . '_primbtn_size') ?>">
	                                    <?php echo esc_html__( 'Button 1', ATKP_PLUGIN_PREFIX ) ?>
                                    </label>
                                    <select id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_primbtn_size') ?>"
                                            name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_primbtn_size') ?>"
                                            class="atkp-template-option">
										<?php
										$selected = atkp_options::$loader->get_primbtn_size();
										echo '<option value="normal" ' . ( $selected == '' || $selected == 'normal' ? 'selected' : '' ) . '>' . esc_html__( 'normal', ATKP_PLUGIN_PREFIX ) . '</option>';

										echo '<option value="small" ' . ( $selected == 'small' ? 'selected' : '' ) . ' >' . esc_html__( 'small', ATKP_PLUGIN_PREFIX ) . '</option>';

										echo '<option value="big" ' . ( $selected == 'big' ? 'selected' : '' ) . '>' . esc_html__( 'big', ATKP_PLUGIN_PREFIX ) . '</option>';
										echo '<option value="hide" ' . ( $selected == 'hide' ? 'selected' : '' ) . '>' . esc_html__( 'hide', ATKP_PLUGIN_PREFIX ) . '</option>';


										?>
                                    </select>
                                </td>
                                <td>
                                    <label for="<?php echo esc_attr_e(ATKP_PLUGIN_PREFIX . '_primbtn_size') ?>">
	                                    <?php echo esc_html__( 'Button 2', ATKP_PLUGIN_PREFIX ) ?>
                                    </label>
                                    <select id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_secbtn_size') ?>"
                                            name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_secbtn_size') ?>"
                                            class="atkp-template-option">
										<?php
										$selected = atkp_options::$loader->get_secbtn_size();
										echo '<option value="normal" ' . ( $selected == '' || $selected == 'normal' ? 'selected' : '' ) . '>' . esc_html__( 'normal', ATKP_PLUGIN_PREFIX ) . '</option>';

										echo '<option value="small" ' . ( $selected == 'small' ? 'selected' : '' ) . ' >' . esc_html__( 'small', ATKP_PLUGIN_PREFIX ) . '</option>';

										echo '<option value="big" ' . ( $selected == 'big' ? 'selected' : '' ) . '>' . esc_html__( 'big', ATKP_PLUGIN_PREFIX ) . '</option>';
										echo '<option value="hide" ' . ( $selected == 'hide' ? 'selected' : '' ) . '>' . esc_html__( 'hide', ATKP_PLUGIN_PREFIX ) . '</option>';


										?>
                                    </select>

                                </td>
                            </tr>


                            <tr>
                                <th>&nbsp;</th>
                                <td>
                                    <label for="">
	                                    <?php echo esc_html__( 'Title length', ATKP_PLUGIN_PREFIX ) ?>:
                                    </label>
                                    <input type="number" min="0" max="1000"
                                           id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_short_title_length') ?>"
                                           name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_short_title_length') ?>"
                                           style="width:50px"
                                           class="atkp-template-option"
                                           value="<?php echo esc_attr( get_option( ATKP_PLUGIN_PREFIX . '_short_title_length', 0 ) ); ?>">
                                </td>

                                <td scope="row">
                                    <label for="">
	                                    <?php echo esc_html__( 'Description length', ATKP_PLUGIN_PREFIX ) ?>:
                                    </label>
                                    <input type="number" min="0" max="1000"
                                           id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_description_length') ?>"
                                           name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_description_length') ?>"
                                           style="width:50px"
                                           class="atkp-template-option"
                                           value="<?php echo esc_attr( get_option( ATKP_PLUGIN_PREFIX . '_description_length', 0 ) ); ?>">
                                </td>

                                <td scope="row">
                                    <label for="">
	                                    <?php echo esc_html__( 'Features count', ATKP_PLUGIN_PREFIX ) ?>:
                                    </label>
                                    <input type="number" min="0" max="1000"
                                           id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_feature_count') ?>"
                                           name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_feature_count') ?>"
                                           style="width:50px"
                                           class="atkp-template-option"
                                           value="<?php echo esc_attr( get_option( ATKP_PLUGIN_PREFIX . '_feature_count', 0 ) ); ?>">
                                </td>
                            </tr>


                            <tr>
                                <th colspan="5" style="background-color:#bde4ea; padding:7px">
	                                <?php echo esc_html__( 'Texts', ATKP_PLUGIN_PREFIX ) ?>
                                </th>
                            </tr>
                            <tr>
                                <th class="atkp-settings-group"></th>
                                <td colspan="2">
                                    <input type="checkbox" id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_show_disclaimer') ?>"
                                           name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_show_disclaimer') ?>"
                                           class=" atkp-template-option"
                                           value="1" <?php echo checked( 1, atkp_options::$loader->get_show_disclaimer(), true ); ?>>
                                    <label for="<?php echo esc_attr_e(ATKP_PLUGIN_PREFIX . '_show_disclaimer') ?>">
	                                    <?php echo esc_html__( 'Show disclaimer', ATKP_PLUGIN_PREFIX ) ?>
                                    </label> <br/>
                                    <textarea id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_disclaimer_text') ?>"
                                              style="width:100%" rows="4" class=" atkp-template-option"
                                              name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_disclaimer_text') ?>"><?php echo esc_textarea( atkp_options::$loader->get_disclaimer_text() ) ?></textarea>


                                </td>
                                <td colspan="2" style="vertical-align: top">
                                    <input type="checkbox" id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_show_priceinfo') ?>"
                                           name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_show_priceinfo') ?>"
                                           class=" atkp-template-option"
                                           value="1" <?php echo checked( 1, atkp_options::$loader->get_show_priceinfo(), true ); ?>>
                                    <label for="<?php echo esc_attr_e(ATKP_PLUGIN_PREFIX . '_priceinfo_text') ?>">
	                                    <?php echo esc_html__( 'Price info text', ATKP_PLUGIN_PREFIX ) ?>
                                    </label><br/>
                                    <textarea id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_priceinfo_text') ?>"
                                              style="width:100%" class=" atkp-template-option" rows="4"
                                              name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_priceinfo_text') ?>"><?php echo esc_textarea( atkp_options::$loader->get_priceinfo_text() ) ?></textarea>


                                </td>


                            </tr>
                            <tr>
                                <th class="atkp-settings-group"></th>
                                <td style="vertical-align: top;">
                                    <label for="">
	                                    <?php echo esc_html__( 'Description & Features', ATKP_PLUGIN_PREFIX ) ?>:
                                    </label>

                                    <select id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_boxcontent') ?>"
                                            class="atkp-template-option"
                                            name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_boxcontent') ?>" style="">
										<?php
										$selected = get_option( ATKP_PLUGIN_PREFIX . '_boxcontent' );

										echo '<option value="1" ' . ( $selected == '' || $selected == 1 ? 'selected' : '' ) . ' >' . esc_html__( 'Features and (if empty) description ', ATKP_PLUGIN_PREFIX ) . '</option>';

										echo '<option value="2" ' . ( $selected == 2 ? 'selected' : '' ) . '>' . esc_html__( 'Features', ATKP_PLUGIN_PREFIX ) . '</option>';
										echo '<option value="3" ' . ( $selected == 3 ? 'selected' : '' ) . '>' . esc_html__( 'Description', ATKP_PLUGIN_PREFIX ) . '</option>';

										?>
                                    </select>
                                </td>
                                <td style="vertical-align: top">
                                    <label for="">
	                                    <?php echo esc_html__( 'Product page text', ATKP_PLUGIN_PREFIX ) . ' (html)' ?>:
                                    </label>

                                    <input type="text"
                                           id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_productpage_title') ?>"
                                           name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_productpage_title') ?>"
                                           class="atkp-template-option"
                                           value="<?php echo esc_attr( get_option( ATKP_PLUGIN_PREFIX . '_productpage_title', esc_html__( 'View Product', ATKP_PLUGIN_PREFIX ) ) ); ?>">
                                </td>

                                <td colspan="2">
                                    <input type="checkbox" id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_mark_links') ?>"
                                           name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_mark_links') ?>"
                                           class=" atkp-template-option"
                                           value="1" <?php echo checked( 1, atkp_options::$loader->get_mark_links(), true ); ?>>
                                    <label for="<?php echo esc_attr_e(ATKP_PLUGIN_PREFIX . '_mark_links') ?>">
	                                    <?php echo esc_html__( 'Mark affiliate links (*)', ATKP_PLUGIN_PREFIX ) ?>
                                    </label><br/>
                                    <input type="text" id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_affiliatechar') ?>"
                                           name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_affiliatechar') ?>"
                                           class=" atkp-template-option"
                                           value="<?php echo esc_attr( get_option( ATKP_PLUGIN_PREFIX . '_affiliatechar', '*' ) ); ?>">

									<?php ATKPTools::display_helptext( 'This character will be attached do your affiliate links. You can also use html for special formatting.' ) ?>

                                </td>
                            </tr>

                            <tr>
                                <th colspan="5" style="background-color:#bde4ea; padding:7px">
	                                <?php echo esc_html__( 'Additional Offers', ATKP_PLUGIN_PREFIX ) ?>
                                </th>
                            </tr>
                            <tr>
                                <td colspan="5">           <?php ATKPTools::display_helptext( 'The system templates are not showing the price comparision by default. Activate this option to display different prices.' ) ?>
                                </td>
                            </tr>
                            <tr>
                                <th class="atkp-settings-group"></th>
                                <td>
                                    <input type="checkbox" id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_show_moreoffers') ?>"
                                           name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_show_moreoffers') ?>"
                                           class="atkp-template-option"
                                           value="1" <?php echo checked( 1, get_option( ATKP_PLUGIN_PREFIX . '_show_moreoffers' ), true ); ?>>
                                    <label for="<?php echo esc_attr_e(ATKP_PLUGIN_PREFIX . '_show_moreoffers') ?>">
	                                    <?php echo esc_html__( 'Show additional offers', ATKP_PLUGIN_PREFIX ) ?>
                                    </label>
                                </td>
                                <td>
                                    <label for="">
	                                    <?php echo esc_html__( 'Template', ATKP_PLUGIN_PREFIX ) ?>:
                                    </label><br/>

                                    <select id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_moreoffers_template') ?>"
                                            name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_moreoffers_template') ?>"
                                            class="atkp-template-option">
										<?php
										echo '<option value="">' . esc_html__( 'default', ATKP_PLUGIN_PREFIX ) . '</option>';


										$templates         = atkp_template::get_list( true, false );
										$moreoffertemplate = get_option( ATKP_PLUGIN_PREFIX . '_moreoffers_template' );

										foreach ( $templates as $template => $caption ) {
											if ( $template == $moreoffertemplate ) {
												$sel = ' selected';
											} else {
												$sel = '';
											}

											echo '<option value="' . esc_attr( $template ) . '" ' . esc_attr($sel) . '>' . esc_textarea( $caption ) . '</option>';
										}
										?>
                                    </select>

                                </td>
                                <td>

                                    <label for="">
	                                    <?php echo esc_html__( 'Text', ATKP_PLUGIN_PREFIX ) ?>:
                                    </label><br/>
                                    <input type="text" id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_moreoffers_title') ?>"
                                           name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_moreoffers_title') ?>"
                                           class="atkp-template-option"
                                           value="<?php echo esc_attr( get_option( ATKP_PLUGIN_PREFIX . '_moreoffers_title', esc_html__( 'Additional offers »', ATKP_PLUGIN_PREFIX ) ) ); ?>">

                                </td>
                                <td>

                                    <label for="">
	                                    <?php echo esc_html__( 'Maximum offers count', ATKP_PLUGIN_PREFIX ) ?>:
                                    </label><br/>
                                    <input type="number" id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_moreoffers_count') ?>"
                                           name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_moreoffers_count') ?>"
                                           class="atkp-template-option"
                                           value="<?php echo esc_attr( get_option( ATKP_PLUGIN_PREFIX . '_moreoffers_count', '' ) ); ?>">

                                </td>
                            </tr>

                            <tr>
                                <th></th>
                                <td>
                                    <input type="checkbox"
                                           id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_moreoffers_includemainoffer') ?>"
                                           name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_moreoffers_includemainoffer') ?>"
                                           class="atkp-template-option"
                                           value="1" <?php echo checked( 1, get_option( ATKP_PLUGIN_PREFIX . '_moreoffers_includemainoffer' ), true ); ?>>
                                    <label for="<?php echo esc_attr_e(ATKP_PLUGIN_PREFIX . '_moreoffers_includemainoffer') ?>">
	                                    <?php echo esc_html__( 'Include main offer', ATKP_PLUGIN_PREFIX ) ?>
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

                        @media only screen and (max-width: 2100px) {
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

                    <table class="form-table" style="width:100%">
                        <tr>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <th scope="row" style="background-color:#bde4ea; padding:7px" colspan="2">
	                            <?php echo esc_html__( 'Advanced Display Options', ATKP_PLUGIN_PREFIX ) ?>
                            </th>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="">
	                                <?php echo esc_html__( 'CSS output type', ATKP_PLUGIN_PREFIX ) ?>:
                                </label>
                            </th>
                            <td>
                                <select id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_css_inline') ?>"
                                        name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_css_inline') ?>" style="width:300px">
									<?php
									$selected                            = atkp_options::$loader->get_css_inline();
									$values                              = array();
									$values[ atkp_css_type::Inline ]     = esc_html__( 'Inline styles', ATKP_PLUGIN_PREFIX );
									$values[ atkp_css_type::InlineHead ] = esc_html__( 'Inline styles (head)', ATKP_PLUGIN_PREFIX );
									$values[ atkp_css_type::CssFile ]    = esc_html__( 'css file', ATKP_PLUGIN_PREFIX );

									foreach ( $values as $value => $caption ) {

										echo '<option value="' . esc_attr( $value ) . '" ' . ( $selected == $value || ( $value == 'link' && $selected == '' ) ? 'selected' : '' ) . ' >' . esc_textarea( $caption ) . '</option>';
									}

									?>
                                </select>
                            </td>
                        </tr>


                        <tr>
                            <th scope="row">

                            </th>
                            <td>
                                <input type="checkbox" id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_outputashtml') ?>"
                                       name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_outputashtml') ?>"
                                       value="1" <?php echo checked( 1, get_option( ATKP_PLUGIN_PREFIX . '_outputashtml' ), true ); ?>>
                                <label for="<?php echo esc_attr_e(ATKP_PLUGIN_PREFIX . '_outputashtml') ?>">
	                                <?php echo esc_html__( 'Output description and features as html. Overwrites substring settings.', ATKP_PLUGIN_PREFIX ) ?>
                                </label>

								<?php ATKPTools::display_helptext( 'The plugin removes all html characters on your webpage. By using this option the html will not be filtered but the output will be in full length.' ) ?>

                            </td>
                        </tr>


                        <tr>
                            <th scope="row">

                            </th>
                            <td>
                                <input type="checkbox"
                                       id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_pricecomparisongroupshops') ?>"
                                       name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_pricecomparisongroupshops') ?>"
                                       value="1" <?php echo checked( 1, get_option( ATKP_PLUGIN_PREFIX . '_pricecomparisongroupshops', 1 ), true ); ?>>
                                <label for="<?php echo esc_attr_e(ATKP_PLUGIN_PREFIX . '_pricecomparisongroupshops') ?>">
	                                <?php echo esc_html__( 'Hide duplicate shops', ATKP_PLUGIN_PREFIX ) ?>
                                </label>

								<?php ATKPTools::display_helptext( 'This option filter duplicate offers. If price, shipping costs and product name is equal the second offer will be not displayed.' ) ?>

                            </td>
                        </tr>


                        <tr>
                            <th scope="row">

                            </th>
                            <td>
                                <input type="checkbox" id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_hideerrormessages') ?>"
                                       name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_hideerrormessages') ?>"
                                       value="1" <?php echo checked( 1, get_option( ATKP_PLUGIN_PREFIX . '_hideerrormessages', 1 ), true ); ?>>
                                <label for="<?php echo esc_attr_e(ATKP_PLUGIN_PREFIX . '_hideerrormessages') ?>">
	                                <?php echo esc_html__( 'Hide error messages on the web page', ATKP_PLUGIN_PREFIX ) ?>
                                </label>
								<?php ATKPTools::display_helptext( 'If you can\'t see a output in your product box you can enable this option.' ) ?>

                            </td>
                        </tr>
                        <tr>
                            <th scope="row">

                            </th>
                            <td>
                                <input type="checkbox" id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_enable_ajax_loading') ?>"
                                       name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_enable_ajax_loading') ?>"
                                       value="1" <?php echo checked( 1, get_option( ATKP_PLUGIN_PREFIX . '_enable_ajax_loading', 0 ), true ); ?>>
                                <label for="<?php echo esc_attr_e(ATKP_PLUGIN_PREFIX . '_enable_ajax_loading') ?>">
	                                <?php echo esc_html__( 'Load product displays via AJAX request', ATKP_PLUGIN_PREFIX ) ?>
                                </label>
								<?php ATKPTools::display_helptext( 'If you have problems when using a caching plugin or you want to use geo targeting extension you need to activate this option.' ) ?>

                            </td>
                        </tr>

                        <tr>
                            <th scope="row">

                            </th>
                            <td>
                                <input type="checkbox" id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_enable_ajax_handler') ?>"
                                       name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_enable_ajax_handler') ?>"
                                       value="1" <?php echo checked( 1, get_option( ATKP_PLUGIN_PREFIX . '_enable_ajax_handler', 0 ), true ); ?>>
                                <label for="<?php echo esc_attr_e(ATKP_PLUGIN_PREFIX . '_enable_ajax_handler') ?>">
	                                <?php echo esc_html__( 'Only enable AJAX request handler', ATKP_PLUGIN_PREFIX ) ?>
                                </label>
								<?php ATKPTools::display_helptext( 'This option is only enabling the AJAX handler. It will not load all product boxes via AJAX request.' ) ?>

                            </td>
                        </tr>


                        <tr>
                            <th scope="row">

                            </th>
                            <td>
                                <input type="checkbox" id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_showadminsection') ?>"
                                       name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_showadminsection') ?>"
                                       value="1" <?php echo checked( 1, get_option( ATKP_PLUGIN_PREFIX . '_showadminsection', 1 ), true ); ?>>
                                <label for="<?php echo esc_attr_e(ATKP_PLUGIN_PREFIX . '_showadminsection') ?>">
	                                <?php echo esc_html__( 'Show admin section', ATKP_PLUGIN_PREFIX ) ?>
                                </label>
								<?php ATKPTools::display_helptext( 'This displays links to template and product or list of the shortcode. It is only visible for a administrator.' ) ?>

                            </td>
                        </tr>


                        <tr>
                            <th scope="row">
                                <label for="">
	                                <?php echo esc_html__( 'Default for the button', ATKP_PLUGIN_PREFIX ) ?>:
                                </label>
                            </th>
                            <td>
                                <select id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_add_to_cart') ?>"
                                        name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_add_to_cart') ?>" style="width:300px">
									<?php
									$selected               = atkp_options::$loader->get_add_to_cart();
									$values                 = array();
									$values['link']         = esc_html__( 'affiliate link (mainproduct)', ATKP_PLUGIN_PREFIX );
									$values['addtocart']    = esc_html__( 'add to cart', ATKP_PLUGIN_PREFIX );
									$values['linkfallback'] = esc_html__( 'affiliate link (mainproduct - if not available use min offer)', ATKP_PLUGIN_PREFIX );
									$values['minofferlink'] = esc_html__( 'affiliate link (min offer)', ATKP_PLUGIN_PREFIX );
									$values['maxofferlink'] = esc_html__( 'affiliate link (max offer)', ATKP_PLUGIN_PREFIX );

									$values = apply_filters( 'atkp_modify_addtocart_options', $values );

									foreach ( $values as $value => $caption ) {

										echo '<option value="' . esc_attr( $value ) . '" ' . ( $selected == $value || ( $value == 'link' && $selected == '' ) ? 'selected' : '' ) . ' >' . esc_textarea( $caption ) . '</option>';
									}

									?>
                                </select>
                            </td>
                        </tr>


                        <tr>
                            <th scope="row">
                                <label for="">
	                                <?php echo esc_html__( 'Default for the title', ATKP_PLUGIN_PREFIX ) ?>:
                                </label>
                            </th>
                            <td>
                                <select id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_title_link_type') ?>"
                                        name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_title_link_type') ?>"
                                        style="width:300px">
									<?php
									$selected = atkp_options::$loader->get_title_link_type();

									$values         = array();
									$values['link'] = esc_html__( 'affiliate link (mainproduct)', ATKP_PLUGIN_PREFIX );

									$values = apply_filters( 'atkp_modify_title_options', $values );

									foreach ( $values as $value => $caption ) {

										echo '<option value="' . esc_attr( $value ) . '" ' . ( $selected == $value || ( $value == 'link' && $selected == '' ) ? 'selected' : '' ) . ' >' . esc_textarea( $caption ) . '</option>';
									}

									?>
                                </select>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="">
	                                <?php echo esc_html__( 'Plugin credits', ATKP_PLUGIN_PREFIX ) ?>:
                                </label>
                            </th>
                            <td>
                                <input type="checkbox" id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_show_credits') ?>"
                                       name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_show_credits') ?>"
                                       value="1" <?php echo checked( 1, atkp_options::$loader->get_show_credits(), true ); ?>>
                                <label for="<?php echo esc_attr_e(ATKP_PLUGIN_PREFIX . '_show_credits') ?>">
	                                <?php echo esc_html__( 'Show plugin credits and ', ATKP_PLUGIN_PREFIX) . '<a href="https://www.affiliate-toolkit.com/account/affiliate-area/" target="_blank">' . esc_html__( 'earn money', ATKP_PLUGIN_PREFIX) . '</a>' ?>
                                </label> <br/>
                                <input type="number" id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_credits_ref') ?>"
                                       name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_credits_ref') ?>"
                                       value="<?php echo esc_attr( atkp_options::$loader->get_credits_ref() ); ?>"
                                       placeholder="<?php echo esc_html__( 'Your ref id', ATKP_PLUGIN_PREFIX ) ?>"/>
								<?php ATKPTools::display_helptext( 'When you add your ref id, we will add this to the credits link.' ) ?>

                            </td>
                        </tr>


						<?php
						do_action( 'atkp_settings_display_fields' );
						?>


                        <tr>
                            <td colspan="2">&nbsp;</td>
                        </tr>

                        <tr>
                            <th scope="row" style="background-color:#bde4ea; padding:7px" colspan="2">
	                            <?php echo esc_html__( 'Review & Rating', ATKP_PLUGIN_PREFIX ) ?>
                            </th>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="">
	                                <?php echo esc_html__( 'Review text', ATKP_PLUGIN_PREFIX ) ?>:
                                </label>
                            </th>
                            <td>
                                <input type="text" style="width:100%"
                                       id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_review_text') ?>"
                                       name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_review_text') ?>"
                                       value="<?php echo esc_attr( get_option( ATKP_PLUGIN_PREFIX . '_review_text', esc_html__( 'Review', ATKP_PLUGIN_PREFIX ) ) ); ?>">
                                <br/><input type="text" class="color-field"
                                            id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_review_color') ?>"
                                            name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_review_color') ?>"
                                            value=" <?php echo esc_attr( get_option( ATKP_PLUGIN_PREFIX . '_review_color', '#9f9f9f' ) ) ?>">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="">
	                                <?php echo esc_html__( 'Test score 1', ATKP_PLUGIN_PREFIX ) ?>:
                                </label>
                            </th>
                            <td>
                                <input type="text" id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_test_score1_text') ?>"
                                       name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_test_score1_text') ?>"
                                       value="<?php echo esc_attr( get_option( ATKP_PLUGIN_PREFIX . '_test_score1_text', esc_html__( 'Very good', ATKP_PLUGIN_PREFIX ) ) ); ?>">
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="">
	                                <?php echo esc_html__( 'Test score 2', ATKP_PLUGIN_PREFIX ) ?>:
                                </label>
                            </th>
                            <td>
                                <input type="text" id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_test_score2_text') ?>"
                                       name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_test_score2_text') ?>"
                                       value="<?php echo esc_attr( get_option( ATKP_PLUGIN_PREFIX . '_test_score2_text', esc_html__( 'Good', ATKP_PLUGIN_PREFIX ) ) ); ?>">
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="">
	                                <?php echo esc_html__( 'Test score 3', ATKP_PLUGIN_PREFIX ) ?>:
                                </label>
                            </th>
                            <td>
                                <input type="text" id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_test_score3_text') ?>"
                                       name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_test_score3_text') ?>"
                                       value="<?php echo esc_attr( get_option( ATKP_PLUGIN_PREFIX . '_test_score3_text', esc_html__( 'Satisfying', ATKP_PLUGIN_PREFIX ) ) ); ?>">
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="">
	                                <?php echo esc_html__( 'Test score 4', ATKP_PLUGIN_PREFIX ) ?>:
                                </label>
                            </th>
                            <td>
                                <input type="text" id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_test_score4_text') ?>"
                                       name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_test_score4_text') ?>"
                                       value="<?php echo esc_attr( get_option( ATKP_PLUGIN_PREFIX . '_test_score4_text', esc_html__( 'Enough', ATKP_PLUGIN_PREFIX ) ) ); ?>">
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="">
	                                <?php echo esc_html__( 'Test score 5', ATKP_PLUGIN_PREFIX ) ?>:
                                </label>
                            </th>
                            <td>
                                <input type="text" id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_test_score5_text') ?>"
                                       name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_test_score5_text') ?>"
                                       value="<?php echo esc_attr( get_option( ATKP_PLUGIN_PREFIX . '_test_score5_text', esc_html__( 'Insufficient', ATKP_PLUGIN_PREFIX ) ) ); ?>">
                            </td>
                        </tr>


                        <tr>
                            <td colspan="2">&nbsp;</td>
                        </tr>
                        <tr>
                            <th scope="row" style="background-color:#bde4ea; padding:7px" colspan="2">
	                            <?php echo esc_html__( 'Product not available', ATKP_PLUGIN_PREFIX ) ?>
                            </th>
                        </tr>

                        <tr>
                            <th scope="row">

                            </th>
                            <td>
                                <input type="checkbox" id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_show_nota_template') ?>"
                                       name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_show_nota_template') ?>"
                                       value="1" <?php echo checked( 1, get_option( ATKP_PLUGIN_PREFIX . '_show_nota_template' ), true ); ?>>
                                <label for="<?php echo esc_attr_e(ATKP_PLUGIN_PREFIX . '_show_nota_template') ?>">
	                                <?php echo esc_html__( 'Show template if not available', ATKP_PLUGIN_PREFIX ) ?>
                                </label>

								<?php ATKPTools::display_helptext( 'If no offer is available this display can be displayed by default. It shows a button which is linked to the affiliate network.' ) ?>

                            </td>
                        </tr>


                        <tr>
                            <th scope="row">
                                <label for="">
	                                <?php echo esc_html__( 'Not available template', ATKP_PLUGIN_PREFIX ) ?>:
                                </label>
                            </th>
                            <td>
                                <select id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_nota_template') ?>"
                                        name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_nota_template') ?>" style="width:300px">
									<?php
									echo '<option value="">' . esc_html__( 'default', ATKP_PLUGIN_PREFIX ) . '</option>';

									global $post;
									$args        = array(
										'post_type'      => ATKP_TEMPLATE_POSTTYPE,
										'posts_per_page' => 300,
										'post_status'    => 'publish'
									);
									$posts_array = get_posts( $args );
									foreach ( $posts_array as $prd ) {

										if ( $prd->ID == get_option( ATKP_PLUGIN_PREFIX . '_nota_template' ) ) {
											$sel = ' selected';
										} else {
											$sel = '';
										}

										echo '<option value="' . esc_attr( $prd->ID ) . '"' . esc_attr($sel) . '>' . esc_textarea( $prd->post_title . ' (' . $prd->ID . ')' ) . '</option>';
									};
									?>
                                </select>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">

                            </th>
                            <td>
                                <input type="checkbox" id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_nota_disable_link') ?>"
                                       name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_nota_disable_link') ?>"
                                       value="1" <?php echo checked( 1, get_option( ATKP_PLUGIN_PREFIX . '_nota_disable_link' ), true ); ?>>
                                <label for="<?php echo esc_attr_e(ATKP_PLUGIN_PREFIX . '_nota_disable_link') ?>">
	                                <?php echo esc_html__( 'Disable text link if not available', ATKP_PLUGIN_PREFIX ) ?>
                                </label>
                            </td>
                        </tr>


                        <tr>
                            <td colspan="2">&nbsp;</td>
                        </tr>
                        <tr>
                            <th scope="row" style="background-color:#bde4ea; padding:7px" colspan="2">
	                            <?php echo esc_html__( 'Lists', ATKP_PLUGIN_PREFIX ) ?>
                            </th>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="">
	                                <?php echo esc_html__( 'Amount of list entries (fallback)', ATKP_PLUGIN_PREFIX ) ?>:
                                </label>
                            </th>
                            <td>
                                <input type="number" min="0" max="1000"
                                       id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_list_default_count') ?>"
                                       name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_list_default_count') ?>"
                                       value="<?php echo esc_attr( get_option( ATKP_PLUGIN_PREFIX . '_list_default_count', 10 ) ); ?>">

								<?php ATKPTools::display_helptext( 'This limit is used for the shortcodes. It\'s the fallback if no limit was defined.' ) ?>

                            </td>

                        </tr>


                        <tr>
                            <th scope="row">
                            </th>
                            <td>
								<?php submit_button( '', 'primary', 'savedisplay', false ); ?>
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
        </div> <?php
	}
}

?>