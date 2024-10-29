<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class atkp_shortcode_generator2 {
	/**
	 * Construct the plugin object
	 */
	public function __construct( $pluginbase ) {
		add_action( 'add_meta_boxes', array( &$this, 'shortcode_boxes' ) );

		add_action( 'save_post', array( &$this, 'product_detail_save' ) );

		//https://www.sitepoint.com/adding-a-media-button-to-the-content-editor/

		add_action( 'media_buttons', array( &$this, 'shortcode_buttons' ) );


		add_action( 'admin_head', array( &$this, 'atkp_add_my_tc_button' ) );

		//add_action( 'media_buttons',    array(&$this, 'shortcode_popup' ) );
		add_action( 'admin_footer', array( &$this, 'shortcode_popup' ) );
		//add_action( 'wp_footer', array(&$this, 'shortcode_popup' ) );
	}

	function atkp_add_my_tc_button() {
		global $typenow;
		// check user permissions
		//if ( !current_user_can('edit_posts') && !current_user_can('edit_pages') ) {
		//return;
		//}
		// verify the post type

		$page = ATKPTools::get_get_parameter( 'page', 'string' );

		$allowed_pages = array();
		array_push( $allowed_pages, 'ATKP_affiliate_toolkit-plugin' );

		$sel_post_types = get_option( ATKP_PLUGIN_PREFIX . '_custom_posttypes', null );

		if ( $sel_post_types == null || ! is_array( $sel_post_types ) ) {
			$sel_post_types = array();
		}

		array_push( $sel_post_types, 'post' );
		array_push( $sel_post_types, 'page' );
		array_push( $sel_post_types, ATKP_PRODUCT_POSTTYPE );

		if ( ! in_array( $typenow, $sel_post_types ) && ! in_array( $page, $allowed_pages ) ) {
			return;
		}

		// check if WYSIWYG is enabled
		//if ( get_user_option('rich_editing') == 'true') {
		add_filter( 'mce_external_plugins', array( &$this, 'addbuttons' ) );
		add_filter( 'mce_buttons', array( &$this, 'registerbuttons' ) );
		//}

	}

	function addbuttons( $plugin_array ) {
		$plugin_array['atkp_button_picker'] = esc_url(plugins_url( '/js/editor-button.js', ATKP_PLUGIN_FILE ) ); // CHANGE THE BUTTON SCRIPT HERE

		return $plugin_array;
	}

	function registerbuttons( $buttons ) {
		array_push( $buttons, 'separator', 'atkp_button_picker' );

		return $buttons;
	}

	function shortcode_popup() {

		$args         = array();
		$args['echo'] = true;

		$this->shortcode_buttons( $args );


		//TODO: implement cache
		?>

        <div id="atkp-generator-wrap" style="display:none">
            <div id="atkp-generator">
                <div id="atkp-generatorheader">
                    <b><?php echo esc_html__( 'affiliate-toolkit Shortcodes', ATKP_PLUGIN_PREFIX ); ?></b>
                </div>
				<?php

				$this->template_detail_box_content( '' );

				?>

            </div>
        </div>

        <style>

            body.atkp-mfp-shown .mfp-bg {
                z-index: 101000 !important;
            }

            body.atkp-mfp-shown .mfp-wrap {
                z-index: 101001 !important;
            }

            body.atkp-mfp-shown .mfp-preloader {
                z-index: 101002 !important;
            }

            body.atkp-mfp-shown .mfp-content {
                z-index: 101003 !important;
            }

            body.atkp-mfp-shown button.mfp-close,
            body.atkp-mfp-shown button.mfp-arrow {
                z-index: 101004 !important;
            }

            #atkp-generator-wrap {
                display: none;
            }

            #atkp-generator {
                position: relative;
                width: 85%;
                max-width: 700px;
                height: 550px;
                margin: 60px auto;
                padding: 20px;
                background: #fff;
                -webkit-box-shadow: 0 2px 25px #000;
                -moz-box-shadow: 0 2px 25px #000;
                box-shadow: 0 2px 25px #000;
                -webkit-transition: max-width .2s;
                -moz-transition: max-width .2s;
                transition: max-width .2s;
            }

            fieldset {
                margin: 8px;
                border: 1px solid silver;
                padding: 8px;
                border-radius: 4px;
            }

            legend {
                padding: 2px;
            }

            .atkp_prdresult, .atkp_createresult {
                height: 400px;
                overflow-y: scroll;
            }

            .atkp-nav {
                bottom: 20px;
                position: absolute;
            }

            i.mce-i-atkp_button_icon {
                background-image: url(<?php echo esc_url(plugins_url( 'images/affiliate_toolkit_menu.png', ATKP_PLUGIN_FILE )); ?>);
                background-repeat: no-repeat;
            }

        </style>


        <script type="text/javascript">

            var atkp_selection = '';
            var atkp_editorvisible = false;

            jQuery(document).ready(function ($) {

                $('#atkp_txt_prdsearch').keypress(function (event) {
                    if (event.keyCode == 13 || event.which == 13) {
                        $('#atkp_btn_prdsearch').click();
                    }
                });
                $('#atkp_txt_createsearch').keypress(function (event) {
                    if (event.keyCode == 13 || event.which == 13) {
                        $('#atkp_btn_createsearch').click();
                    }
                });

                function atkpButtonHtml(e, c, ed, defaultValue) {

                    var elId = jQuery(e).attr('id');

                    $('body').on('click', '#' + elId, function (e) {
                        generator_button.trigger("click");
                    });

                    return false;
                }

                if (typeof QTags !== "undefined")
                    QTags.addButton('atkp_html_button', 'AT Shortcode', atkpButtonHtml);

                var $generator = $('#atkp-generator');
                var generator_button = $('.atkp-generator-button');

                $('body').on('click', '.mce-atkp_button_picker', function (e) {
                    generator_button.trigger("click");
                });

                //$('body').on('click', '.atkp-generator-button', function(e) {

                $('body').on('click', '.atkp-generator-button', function (e) {
                    e.preventDefault();
                    // Save the target
                    window.atkp_generator_target = $(this).data('target');
                    // Get open shortcode
                    var shortcode = $(this).data('shortcode');
                    // Open magnificPopup
                    $(this).magnificPopup({
                        type: 'inline',
                        alignTop: true,
                        callbacks: {
                            open: function () {
                                $('body').addClass('atkp-mfp-shown');
                                // Save selection

                                if ((typeof tinyMCE !== 'undefined' && tinyMCE.activeEditor != null && tinyMCE.activeEditor.hasOwnProperty('selection'))) {
                                    atkp_selection = tinyMCE.activeEditor.selection.getContent({format: "text"});
                                    atkp_editorvisible = !tinyMCE.activeEditor.isHidden();
                                } else {
                                    atkp_selection = '';
                                    atkp_editorvisible = false;
                                }

                                //set actual selection to hyperlinkbox
                                $j('#<?php echo esc_js(ATKP_SHORTCODE . '_contentlink') ?>').val(atkp_selection);

                                if (atkp_editorvisible)
                                    $('#atkp-generator-insert').show();
                                else
                                    $('#atkp-generator-insert').hide();


                            },
                            close: function () {
                                // Remove narrow class
                                $generator.removeClass('atkp-generator-narrow');

                                // Clear selection
                                atkp_selection = '';

                                $('body').removeClass('atkp-mfp-shown');
                            }
                        }
                    }).magnificPopup('open');
                });

                $('#atkp-generator').on('click', '.atkp-generator-close', function (e) {
                    // Close popup
                    $.magnificPopup.close();
                    // Prevent default action
                    e.preventDefault();
                });

            });
        </script>
		<?php


	}

	function shortcode_buttons( $args = array() ) {
		//echo '<a href="#" id="insert-my-media" class="button">Affiliate-Toolkit Shortcode</a>';

		$post = get_post();
		if ( $post ) {
			$typenow = $post->post_type;
		} else {
			$typenow = '';
		}
		$sel_post_types = get_option( ATKP_PLUGIN_PREFIX . '_custom_posttypes', null );

		$page = ATKPTools::get_get_parameter( 'page', 'string' );

		$allowed_pages = array();
		array_push( $allowed_pages, 'ATKP_affiliate_toolkit-plugin' );

		if ( $sel_post_types == null || ! is_array( $sel_post_types ) ) {
			$sel_post_types = array();
		}

		array_push( $sel_post_types, 'post' );
		array_push( $sel_post_types, 'page' );
		array_push( $sel_post_types, ATKP_PRODUCT_POSTTYPE );

		if ( ! in_array( $typenow, $sel_post_types ) && ! in_array( $page, $allowed_pages ) ) {
			return;
		}


		$target = is_string( $args ) ? $args : 'content';
		// Prepare args
		$args = wp_parse_args( $args, array(
			'target'    => $target,
			'text'      => esc_html__( 'affiliate-toolkit Shortcodes', ATKP_PLUGIN_PREFIX ),
			'class'     => 'button',
			'icon'      => esc_url(plugins_url( 'images/affiliate_toolkit_menu.png', ATKP_PLUGIN_FILE )),
			'echo'      => true,
			'shortcode' => false
		) );
		// Prepare icon
		if ( $args['icon'] ) {
			$args['icon'] = '<img src="' . $args['icon'] . '" /> ';
		}


		$additional_shortcode_button = get_option( ATKP_PLUGIN_PREFIX . '_additional_shortcode_button', 0 ) ? '' : 'display:none;';

		
		wp_register_style( 'magnific-popup', esc_url(plugins_url( 'css/magnific-popup.css', ATKP_PLUGIN_FILE )), false, '0.9.9', 'all' );
		wp_register_script( 'magnific-popup', esc_url(plugins_url( 'js/magnific-popup.js', ATKP_PLUGIN_FILE )), array( 'jquery' ), '0.9.9', true );
		wp_localize_script( 'magnific-popup', 'atkp_magnific_popup', array(
			'close'   => esc_html__( 'Close (Esc)', ATKP_PLUGIN_PREFIX ),
			'loading' => esc_html__( 'Loading...', ATKP_PLUGIN_PREFIX ),
			'prev'    => esc_html__( 'Previous (Left arrow key)', ATKP_PLUGIN_PREFIX ),
			'next'    => esc_html__( 'Next (Right arrow key)', ATKP_PLUGIN_PREFIX ),
			'counter' => sprintf( esc_html__( '%s of %s', ATKP_PLUGIN_PREFIX ), '%curr%', '%total%' ),
			'error'   => sprintf( esc_html__( 'Failed to load this link. %sOpen link%s.', ATKP_PLUGIN_PREFIX ), '<a href="%url%" target="_blank"><u>', '</u></a>' )
		) );

		wp_enqueue_style( 'magnific-popup' );
		wp_enqueue_script( 'magnific-popup' );

        // Print button
		$button = '<a href="javascript:void(0);" style="' . $additional_shortcode_button . '" class="atkp-generator-button ' . $args['class'] . '" title="' . $args['text'] . '" data-target="' . $args['target'] . '" data-mfp-src="#atkp-generator" data-shortcode="' . (string) $args['shortcode'] . '">' . $args['icon'] . $args['text'] . '</a>';

		if ( $args['echo'] ) {
			echo '<a href="javascript:void(0);" style="' . esc_attr($additional_shortcode_button) .
                '" class="atkp-generator-button ' . esc_attr( $args['class'] ) . '" title="' . esc_attr__( $args['text'], ATKP_PLUGIN_PREFIX ) . 
                '" data-target="' . esc_attr($args['target']) . '" data-mfp-src="#atkp-generator" data-shortcode="' . 
                esc_html__( (string) $args['shortcode'], ATKP_PLUGIN_PREFIX ) . '">' . 
                wp_kses( $args['icon'], array( 'img' => array( 'src' => array() ) ) ) . esc_html__( $args['text'], ATKP_PLUGIN_PREFIX ) . '</a>';
		} else {
			return $button;
		}
	}

	function shortcode_boxes() {
		$types = array( 'post', 'page' );

		$types = apply_filters( 'atkp_mainproduct_posttypes', $types );

		foreach ( $types as $type ) {

			add_meta_box(
				ATKP_PLUGIN_PREFIX . '_product_box',
				esc_html__( 'affiliate-toolkit', ATKP_PLUGIN_PREFIX ),
				array( &$this, 'product_detail_box_content' ),
				$type,
				'normal',
				'default'
			);
		}

	}

	function product_detail_box_content( $post ) {
		wp_nonce_field( plugin_basename( __FILE__ ), 'product_detail_box_content_nonce' );
		?>

        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="<?php echo esc_attr( ATKP_PLUGIN_PREFIX . '_product' ) ?>"><?php echo esc_html__( 'Main product:', ATKP_PLUGIN_PREFIX ); ?></label>
                </th>
                <td>
                    <select id="atkp-product-box-select" class="widefat atkp-product-box" data-id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_product') ?>"
                            data-posttype="<?php echo esc_attr(ATKP_PRODUCT_POSTTYPE) ?>" style="width:100%"
                            name="<?php echo esc_attr_e(ATKP_PLUGIN_PREFIX . '_product', ATKP_PLUGIN_PREFIX) ?>">
						<?php
						$val = ATKPTools::get_post_setting( $post->ID, ATKP_PLUGIN_PREFIX . '_product' );

						if ( atkp_options::$loader->get_disableselect2_backend() ) {
							echo '<option value="" ' . ( $val == '' ? 'selected' : '' ) . '>' . esc_html__( 'None', ATKP_PLUGIN_PREFIX ) . '</option>';

							global $post;
							$args        = array(
								'post_type'   => ATKP_PRODUCT_POSTTYPE,
								'numberposts' => 500,
								'post_status' => array( 'publish', 'draft' )
							);
							$posts_array = get_posts( $args );

							foreach ( $posts_array as $prd ) {
								echo '<option value="' . esc_attr( $prd->ID ) . '"' . ( $val == $prd->ID ? 'selected' : '' ) . '>' . esc_html__( $prd->post_title, ATKP_PLUGIN_PREFIX ) . ' (' . esc_html( $prd->ID ) . ')' . '</option>';
							};

						} else {
							if ( $val != '' ) {
								$prd = $val == '' ? null : get_post( $val );
								if ( $prd != null ) {
									echo '<option value="' . esc_attr( $prd->ID ) . '"' . ( $val == $prd->ID ? 'selected' : '' ) . '>' . esc_html__( $prd->post_title, ATKP_PLUGIN_PREFIX ) . ' (' . esc_html( $prd->ID ) . ')' . '</option>';
								}

							} else {
								echo '<option value="" ' . ( $val == '' ? 'selected' : '' ) . '>' . esc_html__( 'None', ATKP_PLUGIN_PREFIX ) . '</option>';
							}
						}
						?>
                    </select>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="<?php echo esc_attr( ATKP_PLUGIN_PREFIX . '_list' ) ?>"><?php echo esc_html__( 'Main list:', ATKP_PLUGIN_PREFIX ); ?></label>
                </th>
                <td>
                    <select  id="atkp-list-box-select" class="widefat atkp-product-box" data-id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_list') ?>"
                            data-posttype="<?php echo esc_attr(ATKP_LIST_POSTTYPE) ?>" style="width:100%"
                            name="<?php echo esc_attr_e(ATKP_PLUGIN_PREFIX . '_list', ATKP_PLUGIN_PREFIX) ?>">
						<?php
						$val = ATKPTools::get_post_setting( $post->ID, ATKP_PLUGIN_PREFIX . '_list' );

						if ( atkp_options::$loader->get_disableselect2_backend() ) {
							echo '<option value="" ' . ( $val == '' ? 'selected' : '' ) . '>' . esc_html__( 'None', ATKP_PLUGIN_PREFIX ) . '</option>';

							global $post;
							$args        = array(
								'post_type'   => ATKP_LIST_POSTTYPE,
								'numberposts' => 500,
								'post_status' => array( 'publish', 'draft' )
							);
							$posts_array = get_posts( $args );

							foreach ( $posts_array as $prd ) {
								echo '<option value="' . esc_attr( $prd->ID ) . '"' . ( $val == $prd->ID ? 'selected' : '' ) . '>' . esc_html__( $prd->post_title, ATKP_PLUGIN_PREFIX ) . ' (' . esc_html( $prd->ID ) . ')' . '</option>';
							};

						} else {
							if ( $val != '' ) {
								$prd = get_post( $val );
								if ( $prd != null ) {
									echo '<option value="' . esc_attr( $prd->ID ) . '"' . ( $val == $prd->ID ? 'selected' : '' ) . '>' . esc_html__( $prd->post_title, ATKP_PLUGIN_PREFIX ) . ' (' . esc_html( $prd->ID ) . ')' . '</option>';
								}

							} else {
								echo '<option value="" ' . ( $val == '' ? 'selected' : '' ) . '>' . esc_html__( 'None', ATKP_PLUGIN_PREFIX ) . '</option>';
							}
						}
						?>
                    </select>
                </td>
            </tr>
        </table>

		<?php

	}

	function product_detail_save( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		$nounce = ATKPTools::get_post_parameter( 'product_detail_box_content_nonce', 'string' );

		if ( ! wp_verify_nonce( $nounce, plugin_basename( __FILE__ ) ) ) {
			return;
		}

		$types = array( 'post', 'page' );

		$types = apply_filters( 'atkp_mainproduct_posttypes', $types );

		$this->update_main_data( $post_id, '_product', ATKP_PRODUCT_POSTTYPE, $types );
		$this->update_main_data( $post_id, '_list', ATKP_LIST_POSTTYPE, $types );


	}

	function update_main_data(
		$post_id, $field = '_product', $posttype = ATKP_PRODUCT_POSTTYPE, $posttypes = [
		'post',
		'page'
	]
	) {
		$productid    = ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . $field, 'int' );
		$oldproductid = ATKPTools::get_post_setting( $post_id, ATKP_PLUGIN_PREFIX . $field );

		//lade das produkt und schau ob es vorhanden ist
		$prd = get_post( $productid );

		if ( ! isset( $prd ) || $prd == null ) {
			//wenn es nicht existiert, verknüpfung löschen
			ATKPTools::set_post_setting( $post_id, ATKP_PLUGIN_PREFIX . $field, null );

		} else {
			//wenn das Produkt exisitert, dann lege in diesem Beitrag ein benutzerdefiniertes Feld an
			ATKPTools::set_post_setting( $post_id, ATKP_PLUGIN_PREFIX . $field, $productid );
		}

		$changedids = array();

		if ( $oldproductid != $productid ) {
			if ( $oldproductid != null && $oldproductid != 0 ) {
				array_push( $changedids, $oldproductid );
			}
			if ( $productid != null && $productid != 0 ) {
				array_push( $changedids, $productid );
			}
		}

		foreach ( $changedids as $prdid ) {
			//im produkt selbst kann es mehrere verknüpfte Beiträge geben
			$postids = array();

			$args = array(
				'post_type'   => $posttypes,  // YOUR POST TYPE
				'post_status' => array( 'publish', 'draft' ),
				'meta_query'  => array(
					array(
						'key'     => ATKP_PLUGIN_PREFIX . $field,
						'value'   => $prdid,
						'compare' => '=',
						'type'    => 'CHAR',
					),
				),
			);

			// The Query
			$query = new WP_Query( $args );

			if ( $query->have_posts() ) {
				while ( $query->have_posts() ) {
					$query->the_post();
					$postids[] = $query->post->ID;
				}

			}

			wp_reset_query();

			ATKPTools::set_post_setting( $prdid, $posttype . '_postid', $postids );
		}
	}

	function template_detail_box_content( $post ) {

		//require_once (ATKP_PLUGIN_DIR.'/includes/models/atkp_shop.php');
		//require_once (ATKP_PLUGIN_DIR.'/includes/models/atkp_product.php');
		//require_once (ATKP_PLUGIN_DIR.'/includes/models/atkp_template.php');

		?>

        <div class="atkp-pages">
            <h2 id="atkp-steptitle"><?php echo esc_html__( 'What would you like?', ATKP_PLUGIN_PREFIX ); ?></h2>

            <div id="atkp-firstpage">

                <table style="width:100%;text-align: center">
                    <tr>
                        <td>
                            <div style="margin:10px">
                                <a onclick="atkp_nextpage('searchproductorlist', '<?php echo esc_html(ATKP_PRODUCT_POSTTYPE); ?>')"
                                   href="javascript:void(0);">
                                    <i class="fa fa-search" aria-hidden="true"
                                       style="font-size:4em;text-align:center"></i>
                                    <span style="display:block;margin-top:10px">
                                    <?php echo esc_html__( 'Search for already imported products and use it in your post.', ATKP_PLUGIN_PREFIX ); ?>
                                    </span>
                                </a>
                            </div>
                        </td>
                        <td>
                            <div style="margin:10px">
                                <a onclick="atkp_nextpage('createproduct', '<?php echo esc_html(ATKP_PRODUCT_POSTTYPE); ?>')"
                                   href="javascript:void(0);">
                                    <i class="fa fa-download" aria-hidden="true"
                                       style="font-size:4em;text-align:center"></i>
                                    <span style="display:block;margin-top:10px">
                                    <?php echo esc_html__( 'Import a new product and use this in your post.', ATKP_PLUGIN_PREFIX ); ?>
                                    </span>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div style="margin:10px">
                                <a onclick="atkp_nextpage('searchproductorlist', '<?php echo esc_html(ATKP_LIST_POSTTYPE); ?>')"
                                   href="javascript:void(0);">
                                    <i class="fa fa-list" aria-hidden="true"
                                       style="font-size:4em;text-align:center"></i>
                                    <span style="display:block;margin-top:10px">
                                    <?php echo esc_html__( 'Search an already created list and use it in your post.', ATKP_PLUGIN_PREFIX ); ?>
                                    </span>
                                </a>
                            </div>
                        </td>
                        <td>
                            <div style="margin:10px">
                                <a onclick="atkp_nextpage('createlist', '<?php echo esc_html(ATKP_LIST_POSTTYPE); ?>')"
                                   href="javascript:void(0);">
                                    <i class="fa fa-plus-square-o" aria-hidden="true"
                                       style="font-size:4em;text-align:center"></i>
                                    <span style="display:block;margin-top:10px">
                                    <?php echo esc_html__( 'Create a new list (manual, best seller, etc.) and use it in your post.', ATKP_PLUGIN_PREFIX ); ?>
                                    </span>
                                </a>
                            </div>
                        </td>
                    </tr>
					<?php do_action( 'atkp_shortcodegenerator_showaction' ); ?>

                </table>


            </div>


            <div id="atkp-searchproductorlist">

                <div>
                    <div>
                        <label for=""><?php echo esc_html__( 'Keyword:', ATKP_PLUGIN_PREFIX ) ?></label>
                        <input type="text" id="atkp_txt_prdsearch" name="atkp_txt_prdsearch" value="">
                        <input type="submit" class="button" id="atkp_btn_prdsearch"
                               value="<?php echo esc_html__( 'Search', ATKP_PLUGIN_PREFIX ) ?>">
                    </div>

                    <div id="atkp_prdloading" style="display: none;text-align:center">
                        <img src="<?php echo esc_url(plugin_dir_url( ATKP_PLUGIN_FILE ) . '/images/spin.gif') ?>" style="width:32px"
                             alt="loading"/>
                    </div>
                </div>

                <div id="atkp_prdresult" class="atkp_prdresult"
                     style="border-width:1px; border-style: solid;border-color:gray;margin-top:5px;margin-bottom:5px">

                </div>

                <a onclick="atkp_previouspage()" href="javascript:void(0);" id="atkp-back"
                   class="button atkp-nav"><?php echo esc_html__( 'Back', ATKP_PLUGIN_PREFIX ) ?></a>

            </div>

            <div id="atkp-createproduct">

                <div>
                    <div>
                        <label for=""><?php echo esc_html__( 'Shop', ATKP_PLUGIN_PREFIX ) ?>:</label>
                        <select id="atkp_create_shopid" name="atkp_create_shopid" style="width:300px">
							<?php

							$shps = atkp_shop::get_list();

							foreach ( $shps as $shp ) {
								if ( $shp->selected == true ) {
									$sel = ' selected';
								} else {
									$sel = '';
								}

								echo '<option ' . ( $shp->type == atkp_shop_type::SUB_SHOPS ? 'disabled' : '' ) . ' value="' . esc_attr( $shp->id ) . '"' . esc_attr( $sel ) . ' > ' . esc_html__( $shp->title, ATKP_PLUGIN_PREFIX ) . '</option>';

								foreach ( $shp->children as $child ) {
									if ( $child->selected == true ) {
										$sel = ' selected';
									} else {
										$sel = '';
									}

									echo '<option value="' . esc_attr( $child->id ) . '"' . esc_attr( $sel ) . ' >- ' . esc_html__( $child->title, ATKP_PLUGIN_PREFIX ) . '</option>';
								}
							}

							?>
                        </select>&nbsp;

                        <label for=""><?php echo esc_html__( 'Keyword:', ATKP_PLUGIN_PREFIX ) ?></label>
                        <input type="text" id="atkp_txt_createsearch" name="atkp_txt_createsearch" value="">
                        <input type="submit" class="button" id="atkp_btn_createsearch"
                               value="<?php esc_attr_e( 'Search', ATKP_PLUGIN_PREFIX ) ?>">
                    </div>

                    <div id="atkp_createloading" style="display: none;text-align:center">
                        <img src="<?php echo esc_url(plugin_dir_url( ATKP_PLUGIN_FILE ) . '/images/spin.gif') ?>" style="width:32px"
                             alt="loading"/>
                    </div>
                </div>

                <div id="atkp_createresult" class="atkp_createresult"
                     style="border-width:1px; border-style: solid;border-color:gray;margin-top:5px;margin-bottom:5px">

                </div>

                <a onclick="atkp_previouspage()" href="javascript:void(0);" id="atkp-back"
                   class="button atkp-nav"><?php echo esc_html__( 'Back', ATKP_PLUGIN_PREFIX ) ?></a>
            </div>

            <div id="atkp-createlist">

                <div>
                    <div>
                        <table style="width:100%">
                            <tr>
                                <td style="width:30%">
                                    <label for=""><?php echo esc_html__( 'Name', ATKP_PLUGIN_PREFIX ) ?>:</label>
                                </td>
                                <td>
                                    <input type="text" id="atkp_txt_createlistname" name="atkp_txt_createlistname"
                                           value=""> <br/>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for=""><?php echo esc_html__( 'Shop', ATKP_PLUGIN_PREFIX ) ?>:</label>
                                </td>
                                <td>
                                    <select id="atkp_create_listshopid" name="atkp_create_listshopid"
                                            style="width:300px">
										<?php
										echo '<option value="" > ' . esc_attr( esc_html__( 'No shop', ATKP_PLUGIN_PREFIX ) ) . '</option>';


										$shps = atkp_shop::get_list();

										foreach ( $shps as $shp ) {
											if ( $shp->selected == true ) {
												$sel = ' selected';
											} else {
												$sel = '';
											}

											$datasources = $shp->provider == null ? '' : $shp->provider->get_supportedlistsources();

											if ( $datasources != '' ) {
												echo '<option ' . ( $shp->type == atkp_shop_type::SUB_SHOPS ? 'disabled' : '' ) . ' data-sources="' . esc_attr( $datasources ) . '" value="' . esc_attr( $shp->id ) . '"' . esc_attr( $sel ) . ' > ' . esc_html__( $shp->title, ATKP_PLUGIN_PREFIX ) . '</option>';

												foreach ( $shp->children as $child ) {

													if ( $child->selected == true ) {
														$sel = ' selected';
													} else {
														$sel = '';
													}

													echo '<option data-sources="' . esc_attr( $datasources ) . '" value="' . esc_attr( $child->id ) . '"' . esc_attr( $sel ) . ' >- ' . esc_html__( $child->title, ATKP_PLUGIN_PREFIX ) . '</option>';

												}

											}
										}

										?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for=""><?php echo esc_html__( 'Source', ATKP_PLUGIN_PREFIX ) ?>:</label>
                                </td>
                                <td>
                                    <select name="atkp_create_listsource" id="atkp_create_listsource">
										<?php

										$durations = array(
											10 => esc_html__( 'Category - Best Seller', ATKP_PLUGIN_PREFIX ),
											11 => esc_html__( 'Category - New Releases', ATKP_PLUGIN_PREFIX ),
											20 => esc_html__( 'Search', ATKP_PLUGIN_PREFIX ),
											//30 => __('Extended Search', ATKP_PLUGIN_PREFIX),
											//24 => __('Search - Order items by keywords. Rank is determined by the keywords in the product description.', ATKP_PLUGIN_PREFIX),
											//25 => __('Search - Order items by customer reviews, from highest to lowest ranked..', ATKP_PLUGIN_PREFIX),
											//40 => __('Similarity - Find similar products', ATKP_PLUGIN_PREFIX),
										);

										foreach ( $durations as $value => $name ) {
											echo '<option value="' . esc_attr( $value ) . '">' . esc_html__( $name, ATKP_PLUGIN_PREFIX ) . '</option>';
										} ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label id="atkp_lbl_createlistsearchterm"
                                           for=""><?php echo esc_html__( 'Keyword:', ATKP_PLUGIN_PREFIX ) ?></label>
                                </td>
                                <td>
                                    <input type="text" id="atkp_txt_createlistsearchterm"
                                           name="atkp_txt_createlistsearchterm" value=""> <br/>
                                </td>
                            </tr>

                            <tr>
                                <td></td>
                                <td>&nbsp;</td>
                            </tr>

                            <tr>
                                <td></td>
                                <td><?php echo esc_html__( 'This is a generation setup. Further adjustments must still be made in the list editor.', ATKP_PLUGIN_PREFIX ); ?></td>
                            </tr>
                            <tr>
                                <td></td>
                                <td>&nbsp;</td>
                            </tr>

                            <tr>
                                <td></td>
                                <td>
                                    <a class="button" id="atkp_btn_createlist"
                                       onclick="atkp_createlist('searchtemplate', cnttype, '')"
                                       href="javascript:void(0);"><?php echo esc_html__( 'Create list and use', ATKP_PLUGIN_PREFIX ) ?></a>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                <br/><br/>

                <a onclick="atkp_previouspage()" href="javascript:void(0);" id="atkp-back"
                   class="button atkp-nav"><?php echo esc_html__( 'Back', ATKP_PLUGIN_PREFIX ) ?></a>
            </div>


			<?php do_action( 'atkp_shortcodegenerator_showform' ); ?>


            <div id="atkp-searchtemplate" style="overflow-y:scroll;height:440px;">

                <div id="atkp-current" style="word-wrap: break-word;">

                </div>


                <fieldset class="atkp-group" id="atkp-group-template">
                    <legend><input type="radio" name="outputtype" value="template"
                                   checked> <?php echo esc_html__( 'Template', ATKP_PLUGIN_PREFIX ); ?></legend>
                    <table style="width:100%">
                        <tr>
                            <td style="width:30%">
                                <label for="<?php echo esc_attr( ATKP_SHORTCODE . '_template' ) ?>"><?php echo esc_html__( 'Template', ATKP_PLUGIN_PREFIX ); ?>
                                    :</label>
                            </td>
                            <td>
                                <select class="widefat" id="<?php echo esc_attr(ATKP_SHORTCODE . '_template') ?>"
                                        name="<?php echo esc_attr(ATKP_SHORTCODE . '_template') ?>">
									<?php
									echo '<option value="">' . esc_html__( 'default', ATKP_PLUGIN_PREFIX ) . '</option>';

									$templates = atkp_template::get_list( true, false );

									foreach ( $templates as $template => $caption ) {
										echo '<option value="' . esc_attr( $template ) . '">' . esc_html__( htmlentities( $caption ), ATKP_PLUGIN_PREFIX ) . '</option>';
									}

									?>
                                </select>
                            </td>
                        </tr>


                        <tr>
                            <td>
                                <label for="<?php echo esc_attr( ATKP_SHORTCODE . '_containercssclass' ) ?>"><?php echo esc_html__( 'Container CSS Class', ATKP_PLUGIN_PREFIX ); ?>
                                    :</label>
                            </td>
                            <td>
                                <input class="widefat" id="<?php echo esc_attr(ATKP_SHORTCODE . '_containercssclass') ?>"
                                       name="<?php echo esc_attr(ATKP_SHORTCODE . '_containercssclass') ?>" type="text" value=""/>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <label for="<?php echo esc_attr( ATKP_SHORTCODE . '_elementcssclass' ) ?>"><?php echo esc_html__( 'Element CSS Class', ATKP_PLUGIN_PREFIX ); ?>
                                    :</label>
                            </td>
                            <td>
                                <input class="widefat" id="<?php echo esc_attr(ATKP_SHORTCODE . '_elementcssclass') ?>"
                                       name="<?php echo esc_attr(ATKP_SHORTCODE . '_elementcssclass') ?>" type="text" value=""/>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <label for="<?php echo esc_attr( ATKP_SHORTCODE . '_align' ) ?>"><?php echo esc_html__( 'Align', ATKP_PLUGIN_PREFIX ); ?>
                                    :</label>
                            </td>
                            <td>
                                <select class="widefat" id="<?php echo esc_attr(ATKP_SHORTCODE . '_align') ?>"
                                        name="<?php echo esc_attr(ATKP_SHORTCODE . '_align') ?>">
                                    <option value=""><?php echo esc_html__( 'no alignment', ATKP_PLUGIN_PREFIX ); ?></option>
                                    <option value="atkp-left atkp-clearfix"><?php echo esc_html__( 'left', ATKP_PLUGIN_PREFIX ); ?></option>
                                    <option value="atkp-center"><?php echo esc_html__( 'center', ATKP_PLUGIN_PREFIX ); ?></option>
                                    <option value="atkp-right atkp-clearfix"><?php echo esc_html__( 'right', ATKP_PLUGIN_PREFIX ); ?></option>
                                </select>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <label for="<?php echo esc_attr( ATKP_SHORTCODE . '_content' ) ?>"><?php echo esc_html__( 'Content', ATKP_PLUGIN_PREFIX ); ?>
                                    :</label>
                            </td>
                            <td>
                                <input class="widefat" id="<?php echo esc_attr(ATKP_SHORTCODE . '_content') ?>"
                                       name="<?php echo esc_attr(ATKP_SHORTCODE . '__content') ?>" type="text" value=""/>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <label for="<?php echo esc_attr( ATKP_SHORTCODE . '_buttontype' ) ?>"><?php echo esc_html__( 'Button type', ATKP_PLUGIN_PREFIX ); ?>
                                    :</label>
                            </td>
                            <td>
                                <select class="widefat" id="<?php echo esc_attr(ATKP_SHORTCODE . '_buttontype') ?>"
                                        name="<?php echo esc_attr(ATKP_SHORTCODE . '_buttontype') ?>">
									<?php
									echo '<option value="">' . esc_html__( 'default', ATKP_PLUGIN_PREFIX ) . '</option>';
									echo '<option value="addtocart">' . esc_html__( 'add to cart', ATKP_PLUGIN_PREFIX ) . '</option>';
									echo '<option value="link">' . esc_html__( 'link', ATKP_PLUGIN_PREFIX ) . '</option>';
									echo '<option value="product">' . esc_html__( 'product page', ATKP_PLUGIN_PREFIX ) . '</option>';
									?>
                                </select>
                            </td>
                        </tr>
                        <tr class="atkp-onlylist">
                            <td>
                                <label for="<?php echo esc_attr( ATKP_SHORTCODE . '_limit' ) ?>"><?php echo esc_html__( 'Limit', ATKP_PLUGIN_PREFIX ); ?>
                                    :</label>
                            </td>
                            <td>
                                <input class="widefat" id="<?php echo esc_attr(ATKP_SHORTCODE . '_limit') ?>"
                                       name="<?php echo esc_attr(ATKP_SHORTCODE . '_limit') ?>" type="number" min="1" value=""/>
                            </td>
                        </tr>

                        <tr class="atkp-onlylist">
                            <td>

                            </td>
                            <td>
                                <input type="checkbox" id="<?php echo esc_attr(ATKP_SHORTCODE . '_random') ?>"
                                       name="<?php echo esc_attr(ATKP_SHORTCODE . '_random') ?>">
                                <label for="<?php echo esc_attr(ATKP_SHORTCODE . '_random') ?>">
	                                <?php echo esc_html__( 'Random sort', ATKP_PLUGIN_PREFIX ) ?>
                                </label>
                            </td>
                        </tr>

                        <tr>
                            <td>

                            </td>
                            <td>
                                <input type="checkbox" id="<?php echo esc_attr(ATKP_SHORTCODE . '_hidedisclaimer') ?>"
                                       name="<?php echo esc_attr(ATKP_SHORTCODE . '_hidedisclaimer') ?>">
                                <label for="<?php echo esc_attr(ATKP_SHORTCODE . '_hidedisclaimer') ?>">
	                                <?php echo esc_html__( 'Hide disclaimer', ATKP_PLUGIN_PREFIX ) ?>
                                </label>
                            </td>
                        </tr>

                    </table>
                </fieldset>

                <fieldset class="atkp-group" id="atkp-group-field">
                    <legend><input type="radio" name="outputtype"
                                   value="field"> <?php echo esc_html__( 'Field', ATKP_PLUGIN_PREFIX ); ?></legend>
                    <table style="width:100%">
                        <tr>
                            <td style="width:30%">
                                <label for="<?php echo esc_attr( ATKP_SHORTCODE . '_field' ) ?>"><?php echo esc_html__( 'Field', ATKP_PLUGIN_PREFIX ); ?>
                                    :</label>

                            </td>
                            <td>
                                <select class="widefat" id="<?php echo esc_attr(ATKP_SHORTCODE . '_field') ?>"
                                        name="<?php echo esc_attr(ATKP_SHORTCODE . '_field') ?>">
			                        <?php


			                        $bak                           = ATKPSettings::$show_moreoffers;
			                        ATKPSettings::$show_moreoffers = false;
			                        $templatehelper                = new atkp_template_helper();
			                        $placeholders                  = $templatehelper->getPlaceholders();
			                        ATKPSettings::$show_moreoffers = $bak;

			                        foreach ( $placeholders as $placeholder => $caption ) {
				                        echo '<option value="' . esc_attr( $placeholder ) . '">' . esc_html__( $caption, ATKP_PLUGIN_PREFIX ) . '</option>';
			                        };
			                        ?>
                                </select>
                            </td>
                        </tr>

                        <tr>
                            <td>


                            </td>
                            <td>
                                <input type="checkbox" id="<?php echo esc_attr(ATKP_SHORTCODE . '_fieldlink') ?>"
                                       name="<?php echo esc_attr(ATKP_SHORTCODE . '_fieldlink') ?>">
                                <label for="<?php echo esc_attr(ATKP_SHORTCODE . '_fieldlink') ?>">
	                                <?php echo esc_html__( 'Hyperlink', ATKP_PLUGIN_PREFIX ) ?>
                                </label>
                            </td>
                        </tr>

                    </table>
                </fieldset>

                <fieldset class="atkp-group" id="atkp-group-link">
                    <legend><input type="radio" name="outputtype"
                                   value="link"> <?php echo esc_html__( 'Hyperlink', ATKP_PLUGIN_PREFIX ); ?></legend>
                    <table style="width:100%">
                        <tr>
                            <td style="width:30%">
                                <label for="<?php echo esc_attr( ATKP_SHORTCODE . '_contentlink' ) ?>"><?php echo esc_html__( 'Content', ATKP_PLUGIN_PREFIX ); ?>
                                    :</label>
                            </td>
                            <td>
                                <input class="widefat" id="<?php echo esc_attr(ATKP_SHORTCODE . '_contentlink') ?>"
                                       name="<?php echo esc_attr(ATKP_SHORTCODE . '_contentlink') ?>" type="text" value=""/>
                            </td>
                        </tr>
                    </table>
                </fieldset>

                <fieldset class="atkp-group" style="display:none" id="atkp-group-compare">
                    <legend><input type="radio" name="outputtype"
                                   value="compare"> <?php echo esc_html__( 'compare table', ATKP_PLUGIN_PREFIX ); ?>
                    </legend>
                    <table style="width:100%">
                        <tr>
                            <td style="width:30%">
                                <label for=""><?php echo esc_html__( 'Compare values (multi select)', ATKP_PLUGIN_PREFIX ) ?>
                                    :</label>
                            </td>
                            <td>
                                <select style="width:100%" id="<?php echo esc_attr(ATKP_SHORTCODE . '_comparevalues') ?>"
                                        name="<?php echo esc_attr(ATKP_SHORTCODE . '_comparevalues') ?>" multiple="multiple">
			                        <?php


			                        $bak                           = ATKPSettings::$show_moreoffers;
			                        ATKPSettings::$show_moreoffers = false;
			                        $templatehelper                = new atkp_template_helper();
			                        $placeholders                  = $templatehelper->getPlaceholders();
			                        ATKPSettings::$show_moreoffers = $bak;

			                        foreach ( $placeholders as $placeholder => $caption ) {
				                        echo '<option value="' . esc_attr( $placeholder ) . '">' . esc_html__( $caption, ATKP_PLUGIN_PREFIX ) . '</option>';
			                        };
			                        ?>
                                </select>
                            </td>
                        </tr>

                        <tr>
                            <td>


                            </td>
                            <td>
                                <input type="checkbox" id="<?php echo esc_attr(ATKP_SHORTCODE . '_link') ?>"
                                       name="<?php echo esc_attr(ATKP_SHORTCODE . '_link') ?>">
                                <label for="<?php echo esc_attr(ATKP_SHORTCODE . '_link') ?>">
	                                <?php echo esc_html__( 'Horizontal scrollbars', ATKP_PLUGIN_PREFIX ) ?>
                                </label>
                            </td>
                        </tr>

                        <tr>
                            <td>


                            </td>
                            <td>
                                <input type="checkbox" id="<?php echo esc_attr(ATKP_SHORTCODE . '_link') ?>"
                                       name="<?php echo esc_attr(ATKP_SHORTCODE . '_link') ?>">
                                <label for="<?php echo esc_attr(ATKP_SHORTCODE . '_link') ?>">
	                                <?php echo esc_html__( 'Hide header', ATKP_PLUGIN_PREFIX ) ?>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <td>

                            </td>
                            <td>
                                <input type="checkbox" id="<?php echo esc_attr(ATKP_SHORTCODE . '_hidedisclaimer') ?>"
                                       name="<?php echo esc_attr(ATKP_SHORTCODE . '_hidedisclaimer') ?>">
                                <label for="<?php echo esc_attr(ATKP_SHORTCODE . '_hidedisclaimer') ?>">
	                                <?php echo esc_html__( 'Hide disclaimer', ATKP_PLUGIN_PREFIX ) ?>
                                </label>
                            </td>
                        </tr>
                    </table>
                </fieldset>


                <table style="width:100%">
                    <tr>
                        <td style="width:40%">
                            <label for=""><?php echo esc_html__( 'Override affiliate-id (amazon or ebay)', ATKP_PLUGIN_PREFIX ) ?>
                                :</label>
                        </td>
                        <td>
                            <input type="text" id="<?php echo esc_attr(ATKP_SHORTCODE . '_override-tracking_id') ?>"
                                   name="<?php echo esc_attr(ATKP_SHORTCODE . '_override-tracking_id') ?>" value=""> <br/>
                        </td>
                    </tr>


                </table>


                <div class="atkp-nav" style="display:inline-block">
                    <a onclick="atkp_previouspage()" href="javascript:void(0);" id="atkp-back" class="button"
                       style="float:left;margin-right:10px"><?php echo esc_html__( 'Back', ATKP_PLUGIN_PREFIX ) ?></a>
                    <a onclick="atkp_createshortcode('clipboard')" href="javascript:void(0);" id="atkp-generator-paste"
                       style="float:left;margin-right:10px" class="button"><i
                                class="fa fa-refresh"></i>&nbsp;<?php echo esc_html__( 'Copy shortcode to clipboard', ATKP_PLUGIN_PREFIX ) ?>
                    </a>
                    <a onclick="atkp_createshortcode('insert')" href="javascript:void(0);" id='atkp-generator-insert'
                       class="button button-primary" style="float:left;margin-right:10px"><i class="fa fa-check"></i>&nbsp;<?php echo esc_html__( 'Insert shortcode', ATKP_PLUGIN_PREFIX ) ?>
                    </a>
                </div>
            </div>

        </div>
        <script type="text/javascript">

			<?php $searchnounce = esc_js(wp_create_nonce( 'atkp-search-nonce' )); ?>

            function formatRepo(value) {
                if (value.loading) return value.text;

                if (value.id == '')
                    return "<?php echo esc_html__( 'None', ATKP_PLUGIN_PREFIX ) ?>";

                var outputresult = '<table style="width:100%">';
                outputresult += '<tr style="height:50px;">';
                outputresult += '<td><span style="font-size:10px">ID: ' + value.id + ' - ' + value.shop + '</span><br /><b>' + value.title + '</b></td>';
                outputresult += '</tr>';
                outputresult += '</table>';

                return outputresult;
            }

            function formatRepoSelection(repo) {
                if (repo.id == '')
                    return "<?php echo esc_html__( 'None', ATKP_PLUGIN_PREFIX ) ?>";
                else
                    return (repo.text || (repo.title) + ' (' + repo.id + ')');
            }

            var $j = jQuery.noConflict();
            $j(document).ready(function ($) {
                //atkp_txt_prdsearch
                //atkp_btn_prdsearch
                //atkp_prdresult
                //atkp_prdloading

				<?php

	            if(! atkp_options::$loader->get_disableselect2_backend() ) {

	            ?>

                $j('.atkp-product-box').each(function (i, obj) {
                    var posttype = $j(obj).data('posttype');

                    $j(obj).select2atkp({

                        ajax: {
                            type: "POST",
                            url: "<?php echo esc_js(esc_url(ATKPTools::get_endpointurl())); ?>",
                            dataType: 'json',
                            width: '100%',
                            delay: 250,
                            data: function (params) {
                                return {
                                    action: "atkp_search_local_products",
                                    type: posttype,
                                    request_nonce: "<?php echo esc_js($searchnounce); ?>",
                                    keyword: params.term
                                };
                            },
                            processResults: function (data, params) {
                                var count = 0;
                                $j.each(data, function (key, value) {
                                    count++;
                                });

                                if (count > 0) {

                                    if (typeof data[0].error != 'undefined') {
                                        alert(data[0].error + ": " + data[0].message);
                                    } else {

                                    }

                                }


                                var noselection = {
                                    id: "",
                                    name: "<?php echo esc_html__( 'None', ATKP_PLUGIN_PREFIX ) ?>"
                                };

                                if (count == 0)
                                    data = [];

                                data.splice(0, 0, noselection);

                                // parse the results into the format expected by Select2
                                // since we are using custom formatting functions we do not need to
                                // alter the remote JSON data, except to indicate that infinite
                                // scrolling can be used
                                params.page = params.page || 1;

                                return {
                                    results: data,
                                    pagination: {
                                        more: false
                                    }
                                };
                            },
                            cache: true
                        },

                        allowClear: true,
                        escapeMarkup: function (markup) {
                            return markup;
                        }, // let our custom formatter work
                        minimumInputLength: 3,
                        templateResult: formatRepo, // omitted for brevity, see the source of this page
                        templateSelection: formatRepoSelection // omitted for brevity, see the source of this page
                    });

                });

				<?php  } ?>

                atkp_nextpage('', '');

                $j('#atkp_create_listshopid').change(function () {
                    var option = $j('option:selected', $j('#atkp_create_listshopid')).attr('data-sources');

                    if (option != null)
                        supportedsources = option.split(",");

                    $j('#atkp_create_listsource option[value=10]').hide();
                    $j('#atkp_create_listsource option[value=11]').hide();
                    $j('#atkp_create_listsource option[value=20]').hide();
                    $j('#atkp_create_listsource option[value=30]').hide();
                    $j('#atkp_create_listsource option[value=40]').hide();

                    if (option != null) {
                        $j.each(supportedsources, function (index, value) {
                            $j('#atkp_create_listsource option[value=' + value + ']').show();
                        });

                        $j('#atkp_create_listsource').val('20').change();
                        $j('#atkp_create_listsource').prop('disabled', false);

                        $j('#atkp_lbl_createlistsearchterm').show();
                        $j('#atkp_txt_createlistsearchterm').show();
                    } else {
                        $j('#atkp_create_listsource').prop('disabled', true);

                        $j('#atkp_lbl_createlistsearchterm').hide();
                        $j('#atkp_txt_createlistsearchterm').hide();
                    }
                });


                $j('#atkp_create_listshopid').trigger("change");

                $j("#atkp_btn_prdsearch").click(function (e) {

                    $j("#atkp_prdresult").html('');
                    $j("#atkp_prdresult").hide();
                    $j("#atkp_prdloading").show();

                    $j.ajax({
                        type: "POST",
                        url: "<?php echo esc_js(esc_url(ATKPTools::get_endpointurl())); ?>",
                        data: {
                            action: "atkp_search_local_products",
                            type: cnttype,
                            keyword: $j('#atkp_txt_prdsearch').val(),
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
                                        $j("#atkp_prdresult").html('<span style="color:red">' + data[0].error + '<br /> ' + data[0].message + '</span>');
                                    } else {

                                        var outputresult = '<table style="width:100%">';
                                        var cnttext = '';
                                        $j.each(data, function (index, value) {

                                            if (cnttype == '<?php echo esc_html(ATKP_PRODUCT_POSTTYPE); ?>') {
                                                cnttext = '<?php echo esc_html__( 'Use product', ATKP_PLUGIN_PREFIX ) ?>';
                                                outputresult += '<tr style="height:100px;">';
                                                outputresult += '<td style="margin:5px;width:110px;max-height:90px;text-align:center"><img style="max-width:100px" src="' + value.imageurl + '" /></td>';
                                            } else {
                                                cnttext = '<?php echo esc_html__( 'Use list', ATKP_PLUGIN_PREFIX ) ?>';
                                                outputresult += '<tr style="">';
                                            }

                                            outputresult += '<td>ID: ' + value.id + ' - ' + value.shop + '<br /><a href="' + value.editurl + '" target="_blank">' + value.title + '</a></td>';
                                            outputresult += '<td style="width:25px; text-align:right"><a data-id="' + value.id + '" class="button" onclick="atkp_nextpage(\'searchtemplate\', cnttype, ' + value.id + ')" href="javascript:void(0);">' + cnttext + '</a></td>';
                                            outputresult += '</tr>';
                                        });

                                        outputresult += '</table>';
                                        $j("#atkp_prdresult").html(outputresult);
                                    }
                                } else {
                                    $j("#atkp_prdresult").html('<span><?php echo esc_html__( 'No results', ATKP_PLUGIN_PREFIX ); ?></span>');
                                }
                            } catch (err) {

                                $j("#atkp_prdresult").html('<span style="color:red">' + err.message + '</span>');
                                $j("#atkp_prdloading").hide();
                                $j("#atkp_prdresult").show();
                            }


                            $j("#atkp_prdloading").hide();
                            $j("#atkp_prdresult").show();
                        },
                        error: function (xhr, status, error) {
                            console.log(xhr);
                            console.log(status);
                            console.log(error);

                            $j("#atkp_prdresult").html('<span style="color:red">' + status + '<br />' + error + '<br />' + $("<div>").text(xhr.responseText).html() + '</span>');
                            $j("#atkp_prdloading").hide();
                            $j("#atkp_prdresult").show();
                        }
                    });

                });

                $j("#atkp_btn_createsearch").click(function (e) {

                    $j("#atkp_createresult").html('');
                    $j("#atkp_createresult").hide();
                    $j("#atkp_createloading").show();

                    $j.ajax({
                        type: "POST",
                        url: "<?php echo esc_js(esc_url(ATKPTools::get_endpointurl())); ?>",
                        data: {
                            action: "atkp_search_products",
                            shop: $j('#atkp_create_shopid').val(),
                            keyword: $j('#atkp_txt_createsearch').val(),
                            request_nonce: "<?php echo esc_js(wp_create_nonce( 'atkp-search-nonce' )) ?>"
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
                                        $j("#atkp_createresult").html('<span style="color:red">' + data[0].error + '<br /> ' + data[0].message + '</span>');
                                    } else {

                                        var outputresult = '<table style="width:100%">';
                                        var cnttext = '';
                                        $j.each(data, function (index, value) {

                                            if (cnttype == '<?php echo esc_html(ATKP_PRODUCT_POSTTYPE); ?>') {
                                                if (value.productid != 'null')
                                                    cnttext = '<?php echo esc_html__( 'Use product', ATKP_PLUGIN_PREFIX ) ?>';
                                                else
                                                    cnttext = '<?php echo esc_html__( 'Import and use product', ATKP_PLUGIN_PREFIX ) ?>';
                                                outputresult += '<tr style="height:100px;">';
                                                outputresult += '<td style="margin:5px;width:110px;max-height:90px;text-align:center"><img style="max-width:100px" src="' + value.imageurl + '" /></td>';
                                            } else {
                                                cnttext = '<?php echo esc_html__( 'Use list', ATKP_PLUGIN_PREFIX ) ?>';
                                                outputresult += '<tr style="">';
                                            }


                                            outputresult += '<td><a href="' + value.producturl + '" target="_blank">' + value.title + '</a><br />ID: ' + value.asin + ' - EAN: ' + (value.ean == null ? '' : value.ean) + '<br />' + (value.shoptitle != '' ? 'Shop: ' + value.shoptitle : '') + '</td>';


                                            outputresult += '<td style="width:25px; text-align:right"><input type="button" id="atkp-btn-import-' + value.asin + '" name="atkp-btn-import-' + value.asin + '" data-id="' + value.productid + '" data-asin="' + value.asin + '" onclick="atkp_importproduct(\'searchtemplate\', cnttype, \'' + value.asin + '\', ' + value.productid + ')" class="import-button button" title="' + cnttext + '" value="' + cnttext + '" /></td>';


                                            //outputresult += '<td style="width:25px; text-align:right"><a id="atkp-btn-import-'+value.asin+'" data-id="'+value.productid +'" data-asin="'+value.asin+'" class="button" onclick="atkp_importproduct(\'searchtemplate\', cnttype, \''+value.asin+'\', '+value.productid+')" href="javascript:void(0);">'+cnttext+'</a></td>';
                                            outputresult += '</tr>';
                                        });

                                        outputresult += '</table>';
                                        $j("#atkp_createresult").html(outputresult);
                                    }
                                } else {
                                    $j("#atkp_createresult").html('<span><?php echo esc_html__( 'No results', ATKP_PLUGIN_PREFIX ); ?></span>');
                                }
                            } catch (err) {
                                $j("#atkp_createresult").html('<span style="color:red">' + err.message + '</span>');
                                $j("#atkp_createloading").hide();
                                $j("#atkp_createresult").show();
                            }


                            $j("#atkp_createloading").hide();
                            $j("#atkp_createresult").show();
                        },
                        error: function (xhr, status) {
                            $j("#atkp_createresult").html('<span style="color:red">' + xhr.responseText + '</span>');
                            $j("#atkp_createloading").hide();
                            $j("#atkp_createresult").show();
                        }
                    });

                });

                $j('#atkp_create_listsource').change(function () {

                    if ($j('#atkp_create_listsource').val() == 10 || $j('#atkp_create_listsource').val() == 11) {
                        $j('#atkp_lbl_createlistsearchterm').html('<?php echo esc_html__( 'Browsenode-ID', ATKP_PLUGIN_PREFIX ) ?>:');
                    } else {
                        $j('#atkp_lbl_createlistsearchterm').html('<?php echo esc_html__( 'Keyword', ATKP_PLUGIN_PREFIX ) ?>:');
                    }

                });

                $j('#atkp_create_listsource').trigger("change");

            });

            function atkp_createshortcode(type, $formsearch = '') {

                var outputtype = $j('input[name=outputtype]:checked').val();
                var shortcode = '';

                if ($formsearch == 'formsearch') {
                    shortcode = '[atkp_searchform';

                    if ($j('#<?php echo esc_html(ATKP_SHORTCODE . '_searchform_template') ?>').val() != '')
                        shortcode += ' template=\'' + $j('#<?php echo esc_html(ATKP_SHORTCODE . '_searchform_template') ?>').val() + '\'';

                    if ($j('#<?php echo esc_html(ATKP_SHORTCODE . '_searchform_targetpage') ?>').val() != '')
                        shortcode += ' targetpage=\'' + $j('#<?php echo esc_html(ATKP_SHORTCODE . '_searchform_targetpage') ?>').val() + '\'';


                } else {
                    switch (cnttype) {
                        case 'atkp_product':
                            shortcode = '[atkp_product';

						<?php

						$page = ATKPTools::get_get_parameter( 'page', 'string' );

						if('atkp_product' != get_post_type() && 'ATKP_affiliate_toolkit-plugin' != $page) { ?>
                            if (cntid != '')
                                shortcode += ' id=\'' + cntid + '\'';

						<?php } ?>
                            break;
                        case 'atkp_list':
                            shortcode = '[atkp_list';

                            if (cntid != '')
                                shortcode += ' id=\'' + cntid + '\'';

                            if ($j('#<?php echo esc_html(ATKP_SHORTCODE . '_limit') ?>').val() != '')
                                shortcode += ' limit=\'' + $j('#<?php echo esc_html(ATKP_SHORTCODE . '_limit') ?>').val() + '\'';

                            if ($j('#<?php echo esc_html(ATKP_SHORTCODE . '_random') ?>').prop('checked'))
                                shortcode += ' randomsort=\'yes\'';
                            break;
                        case 'atkp_dynamiclist':
                            shortcode = '[atkp_list';

                            if (cntid != '')
                                shortcode += ' filter=\'' + cntid + '\'';
                            break;

                    }
                }

                if ($j('#<?php echo esc_html(ATKP_SHORTCODE . '_override-tracking_id') ?>').val() != '')
                    shortcode += ' tracking_id=\'' + $j('#<?php echo esc_html(ATKP_SHORTCODE . '_override-tracking_id') ?>').val() + '\'';

                switch (outputtype) {
                    case 'template':
                        if ($j('#<?php echo esc_html(ATKP_SHORTCODE . '_template') ?>').val() != '')
                            shortcode += ' template=\'' + $j('#<?php echo esc_html(ATKP_SHORTCODE . '_template') ?>').val() + '\'';
                        if ($j('#<?php echo esc_html(ATKP_SHORTCODE . '_elementcssclass') ?>').val() != '')
                            shortcode += ' elementcss=\'' + $j('#<?php echo esc_html(ATKP_SHORTCODE . '_elementcssclass') ?>').val() + '\'';

                        var containercss = '';

                        if ($j('#<?php echo esc_html(ATKP_SHORTCODE . '_align') ?>').val() != '')
                            containercss = $j('#<?php echo esc_html(ATKP_SHORTCODE . '_align') ?>').val();
                        if ($j('#<?php echo esc_html(ATKP_SHORTCODE . '_containercssclass') ?>').val() != '')
                            containercss += ' ' + $j('#<?php echo esc_html(ATKP_SHORTCODE . '_containercssclass') ?>').val();

                        if (containercss != '')
                            shortcode += ' containercss=\'' + containercss + '\'';

                        if ($j('#<?php echo esc_html(ATKP_SHORTCODE . '_buttontype') ?>').val() != '')
                            shortcode += ' buttontype=\'' + $j('#<?php echo esc_html(ATKP_SHORTCODE . '_buttontype') ?>').val() + '\'';

                        if ($j('#<?php echo esc_html(ATKP_SHORTCODE . '_hidedisclaimer') ?>').prop('checked'))
                            shortcode += ' hidedisclaimer=\'yes\'';

                        if (cnttype == 'atkp_dynamiclist') {
                            if ($j('#<?php echo esc_html(ATKP_SHORTCODE . '_parseparams') ?>').prop('checked'))
                                shortcode += ' parseparams=\'yes\'';

                            if ($j('#<?php echo esc_html(ATKP_SHORTCODE . '_itemsperpage') ?>').val() != '' && $j('#<?php echo esc_html(ATKP_SHORTCODE . '_itemsperpage') ?>').val() > 0)
                                shortcode += ' itemsperpage=\'' + $j('#<?php echo esc_html(ATKP_SHORTCODE . '_itemsperpage') ?>').val() + '\'';
                        }


                        shortcode += ']';

                        if ($j('#<?php echo esc_html(ATKP_SHORTCODE . '_content') ?>').val() != '')
                            shortcode += $j('#<?php echo esc_html(ATKP_SHORTCODE . '_content') ?>').val();

                        break;
                    case 'field':

                        if ($j('#<?php echo esc_html(ATKP_SHORTCODE . '_field') ?>').val() != '')
                            shortcode += ' field=\'' + $j('#<?php echo esc_html(ATKP_SHORTCODE . '_field') ?>').val() + '\'';

                        if ($j('#<?php echo esc_html(ATKP_SHORTCODE . '_fieldlink') ?>').prop('checked'))
                            shortcode += ' link=\'yes\'';

                        shortcode += ']';
                        break;
                    case 'link':
                        shortcode += ' link=\'yes\'';

                        shortcode += ']';

                        if ($j('#<?php echo esc_html(ATKP_SHORTCODE . '_contentlink') ?>').val() != '')
                            shortcode += $j('#<?php echo esc_html(ATKP_SHORTCODE . '_contentlink') ?>').val();

                        break;
                }


                if ($formsearch == 'formsearch') {
                    shortcode += '[/atkp_searchform]';

                } else {
                    switch (cnttype) {
                        case 'atkp_product':

                            shortcode += '[/atkp_product]';
                            break;
                        case 'atkp_dynamiclist':
                        case 'atkp_list':

                            shortcode += '[/atkp_list]';
                            break;

                    }
                }

                switch (type) {

                    case 'clipboard':
                        var result = window.prompt('<?php echo esc_html__( 'Copy to clipboard: Ctrl+C, Enter', ATKP_PLUGIN_PREFIX ) ?>', shortcode);

                        // Close popup
                        if (result != null)
                            $j.magnificPopup.close();
                        break;
                    case 'insert':

                        // Close popup
                        $j.magnificPopup.close();

                        // Prevent default action
                        //e.preventDefault();
                        // Save original activeeditor
                        //window.su_wpActiveEditor = window.wpActiveEditor;
                        // Set new active editor
                        //window.wpActiveEditor = window.su_generator_target;
                        // Insert shortcode

                        tinyMCE.activeEditor.selection.setContent(shortcode);
                        //old: window.wp.media.editor.insert(shortcode);

                        // Restore previous editor
                        //window.wpActiveEditor = window.su_wpActiveEditor;
                        // Check for target content editor
                        // if (typeof window.su_generator_target === 'undefined') return;
                        // Insert into default content editor
                        // else if (window.su_generator_target === 'content') window.wp.media.editor.insert(shortcode);
                        // Insert into ET page builder (text box)
                        // else if (window.su_generator_target === 'et_pb_content_new') window.wp.media.editor.insert(shortcode);
                        // Insert into textarea
                        // else {
                        // var $target = $('textarea#' + window.su_generator_target);
                        // if ($target.length > 0) $target.val($target.val() + shortcode);
                        // }

                        break;
                }
            }

            function atkp_previouspage() {
                var prvpagetype = '';

                atkp_nextpage(prvpagetype, cnttype, cntid, true);
            }

            function atkp_createfilter(pagetype, type, listid) {
                //TODO: build awesome dynamic filter

                var thefilter = '';

                $j('.atkp-backend-filter').each(function (i, obj) {

                    var param = null;

                    if ($j(obj).is(':checkbox')) {

                        if ($j(obj).attr('checked'))
                            param = $j(obj).attr('name') + '=1';
                        else
                            return;
                    } else if ($j(obj).val() != null && $j(obj).val() != '' && $j(obj).val() != 0) {

                        param = $j(obj).attr('name') + '=' + $j(obj).val();

                    } else
                        return;

                    if (thefilter != '') {
                        thefilter += '&' + param;
                    } else {
                        thefilter += param;
                    }

                    //alert($j(obj).attr('name') + ': ' +$j(obj).val());
                });

                if (thefilter == '')
                    thefilter = 'no';

                atkp_nextpage(pagetype, type, thefilter);
            }

            var cntpagetype = '';
            var cnttype = '';
            var cntid = '';

            function atkp_createlist(pagetype, type, listid) {
                btn = $j('#atkp_btn_createlist');

                btn.prop('disabled', true);
				<?php $noncex = wp_create_nonce( 'atkp-import-nonce' ); ?>

                $j.ajax({
                    type: "POST",
                    url: "<?php echo esc_js(esc_url(ATKPTools::get_endpointurl()));  ?>",
                    data: {
                        action: "atkp_create_list",
                        shop: $j('#atkp_create_listshopid').val(),
                        title: $j('#atkp_txt_createlistname').val(),
                        searchterm: $j('#atkp_txt_createlistsearchterm').val(),
                        listtype: $j('#atkp_create_listsource').val(),
                        request_nonce: "<?php echo esc_js($noncex) ?>"
                    },

                    dataType: "json",
                    success: function (data) {
                        try {
                            if (data.length == 0) {
                                alert('unknown issue');
                                return;
                            } else if (typeof data[0].error != 'undefined') {
                                alert(data[0].error + ': ' + data[0].message);
                                return;
                            }

                            atkp_nextpage(pagetype, type, data[0].postid);
                        } catch (err) {
                            alert(err.message);
                        }

                        $j('#atkp_btn_createlist').prop('disabled', false);

                    },
                    error: function (xhr, status) {
                        alert(xhr.responseText);
                        $j('#atkp_btn_createlist').prop('disabled', false);
                    }
                });
            }

            function atkp_importproduct(pagetype, type, asin, productid) {
                btn = $j('#atkp-btn-import-' + asin);

                productid = btn.attr('data-id');

                if (productid != '' && productid != 'null') {
                    //produkt bereits importiert
                    atkp_nextpage(pagetype, type, productid);
                    return;
                }
                btn.prop('disabled', true);

                $j.ajax({
                    type: "POST",
                    url: "<?php echo esc_js(esc_url(ATKPTools::get_endpointurl())); ?>",
                    data: {
                        action: "atkp_import_product",
                        shop: $j('#atkp_create_shopid').val(),
                        asin: asin,
                        asintype: 'ASIN',
                        title: '',
                        status: '',
                        request_nonce: "<?php echo esc_js(wp_create_nonce( 'atkp-import-nonce' )) ?>"
                    },

                    dataType: "json",
                    success: function (data) {
                        try {
                            if (data.length == 0) {
                                alert('unknown issue');
                                return;
                            } else if (typeof data[0].error != 'undefined') {
                                alert(data[0].error + ': ' + data[0].message);
                                return;
                            }

                            btn.attr('data-id', data[0].postid);
                            btn.html('<?php echo esc_html__( 'Use product', ATKP_PLUGIN_PREFIX ) ?>');


                            atkp_nextpage(pagetype, type, data[0].postid);
                            //$j('#atkp-btn-'+$asin).html('<img style="vertical-align:middle" src="<?php echo esc_url( plugins_url( 'images/yes.png', ATKP_PLUGIN_FILE ) ) ?>" alt="<?php echo esc_html__( 'Imported', ATKP_PLUGIN_PREFIX ) ?>"/><a style="margin-left:5px" href="'+$posturl+'" target="_blank"><?php echo esc_html__( 'Product imported.', ATKP_PLUGIN_PREFIX ) ?></a><br />');

                        } catch (err) {
                            alert(err.message);
                        }

                        btn.prop('disabled', false);

                    },
                    error: function (xhr, status) {
                        alert(xhr.responseText);
                        btn.prop('disabled', false);
                    }
                });
            }

            function isInt(value) {
                return !isNaN(value) && (function (x) {
                    return (x | 0) === x;
                })(parseFloat(value))
            }

            function atkp_show_info(div, type, id) {
                div.html('');

                if (isInt(id)) {

                    $j.ajax({
                        type: "POST",
                        url: "<?php echo esc_js(esc_url(ATKPTools::get_endpointurl())); ?>",
                        data: {
                            action: "atkp_get_object",
                            post_type: type,
                            post_id: id,
                            request_nonce: "<?php echo esc_js(wp_create_nonce( 'atkp-get-nonce' )) ?>"
                        },

                        dataType: "json",
                        success: function (data) {
                            try {
                                if (data.length == 0) {
                                    alert('unknown issue');
                                    return;
                                } else if (typeof data[0].error != 'undefined') {
                                    alert(data[0].error + ': ' + data[0].message);
                                    return;
                                }

                                if (data.title == '')
                                    data.title = '<?php echo esc_html__( 'New post', ATKP_PLUGIN_PREFIX ); ?>';

                                //div.html(JSON.stringify(data.title, null, 2));
                                div.html('<a href="' + data.edit_url + '" target="_blank">' + data.title + '</a>'); //'Type: '+ type + ', ID: ' + id + ', Title: '+ JSON.stringify(data, null, 2));
                            } catch (err) {
                                alert(err.message);
                            }

                        },
                        error: function (xhr, status) {
                            alert(xhr.responseText);
                        }
                    });
                } else {
                    div.html(id);
                }
            }

            function atkp_nextpage(pagetype, type, id = '', isback = false) {

                $j('#atkp-firstpage').hide();
                $j('#atkp-searchproductorlist').hide();
                $j('#atkp-createproduct').hide();
                $j('#atkp-createlist').hide();
                $j('#atkp-searchtemplate').hide();

                $j('#atkp-dynamicfilter').hide();
                $j('#atkp-formsearch').hide();


                switch (pagetype) {
                    default:
                        $j('#atkp-firstpage').show();
                        $j('#atkp-steptitle').html('<?php echo esc_html__( 'What do you want?', ATKP_PLUGIN_PREFIX ) ?>');

                        break;
                    case 'formsearch':
                        $j('#atkp-' + pagetype).show();

                        $j('#atkp-steptitle').html('<?php echo esc_html__( 'Embed a searchform', ATKP_PLUGIN_PREFIX ) ?>');
                        break;
                    case 'dynamicfilter':


                        $j('#atkp-' + pagetype).show();

                        $j('#atkp-steptitle').html('<?php echo esc_html__( 'Build dynamic filter', ATKP_PLUGIN_PREFIX ) ?>');


                        break;
                    case 'searchproductorlist':
                        $j('#atkp-' + pagetype).show();

                        if (!isback && cnttype != type) {
                            $j("#atkp_prdresult").html('');
                        }

                        if (type == '<?php echo esc_html(ATKP_PRODUCT_POSTTYPE); ?>')
                            $j('#atkp-steptitle').html('<?php echo esc_html__( 'Search product', ATKP_PLUGIN_PREFIX ) ?>');
                        else
                            $j('#atkp-steptitle').html('<?php echo esc_html__( 'Search list', ATKP_PLUGIN_PREFIX ) ?>');
                        break;
                    case 'createproduct':
                        $j('#atkp-' + pagetype).show();

                        $j('#atkp-steptitle').html('<?php echo esc_html__( 'Create product', ATKP_PLUGIN_PREFIX ) ?>');
                        break;
                    case 'createlist':
                        $j('#atkp-' + pagetype).show();

                        $j('#atkp-steptitle').html('<?php echo esc_html__( 'Create list', ATKP_PLUGIN_PREFIX ) ?>');
                        break;
                    case 'searchtemplate':
                        atkp_show_info($j('#atkp-current'), type, id);

                        $j('#atkp-group-field').show();
                        $j('#atkp-group-link').show();
                        $j('.atkp-onlylist').show();

                        switch (type) {
                            case 'atkp_dynamiclist':
                            case 'atkp_list':
                                $j('#atkp-group-field').hide();
                                $j('#atkp-group-link').hide();

                                break;
                            case 'atkp_product':
                                $j('.atkp-onlylist').hide();
                                break;
                        }


                        $j('#atkp-' + pagetype).show();

                        $j('#atkp-steptitle').html('<?php echo esc_html__( 'Output', ATKP_PLUGIN_PREFIX ) ?>');
                        break;

                }

                if (!isback) {
                    cntpagetype = pagetype;
                    cnttype = type;
                    cntid = id;
                }
            }
        </script>

		<?php
	}
}

?>