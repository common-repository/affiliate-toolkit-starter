<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class atkp_shortcode_generator {
	/**
	 * Construct the plugin object
	 */
	public function __construct( $pluginbase ) {
		add_action( 'add_meta_boxes', array( &$this, 'shortcode_boxes' ) );

		add_action( 'save_post', array( &$this, 'product_detail_save' ) );

		//https://www.sitepoint.com/adding-a-media-button-to-the-content-editor/
		add_action( 'media_buttons', array( &$this, 'shortcode_buttons' ) );


	}

	function shortcode_popup() {

		//TODO: implement cache
		?>

        <div id="atkp-generator-wrap" style="display:none">
            <div id="atkp-generator">
                <div id="atkp-generatorheader">
                    <b>Affiliate-Toolkit Shortcodes</b>
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
                max-width: 1000px;
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
        </style>

        <script>

            jQuery(document).ready(function ($) {
                var $generator = $('#atkp-generator'), mce_selection = '';


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
                                mce_selection = (typeof tinyMCE !== 'undefined' && tinyMCE.activeEditor != null && tinyMCE.activeEditor.hasOwnProperty('selection')) ? tinyMCE.activeEditor.selection.getContent({
                                    format: "text"
                                }) : '';
                            },
                            close: function () {
                                // Remove narrow class
                                $generator.removeClass('atkp-generator-narrow');
                                // Show filters

                                // Clear selection
                                mce_selection = '';

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

                $('#atkp-generator').on('click', '#atkp-generator-insert', function (e) {
                    // Prepare data
                    $j('#<?php echo esc_js(ATKP_SHORTCODE . '_shortcode_btn') ?>').trigger('click');

                    var shortcode = $j('#<?php echo esc_js(ATKP_SHORTCODE . '_shortcode_txt') ?>').val();

                    // Close popup
                    $.magnificPopup.close();

                    // Prevent default action
                    e.preventDefault();
                    // Save original activeeditor
                    window.su_wpActiveEditor = window.wpActiveEditor;
                    // Set new active editor
                    window.wpActiveEditor = window.su_generator_target;
                    // Insert shortcode
                    window.wp.media.editor.insert(shortcode);
                    // Restore previous editor
                    window.wpActiveEditor = window.su_wpActiveEditor;
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
                });
            });
        </script>
		<?php


	}

	function shortcode_buttons( $args = array() ) {
		//echo '<a href="#" id="insert-my-media" class="button">Affiliate-Toolkit Shortcode</a>';

		$target = is_string( $args ) ? $args : 'content';
		// Prepare args
		$args = wp_parse_args( $args, array(
			'target'    => $target,
			'text' => esc_html__( 'Affiliate-Toolkit shortcodes', ATKP_PLUGIN_PREFIX ),
			'class'     => 'button',
			'icon'      => plugins_url( 'images/affiliate_toolkit_menu.png', ATKP_PLUGIN_FILE ),
			'echo'      => true,
			'shortcode' => false
		) );
		// Prepare icon
		if ( $args['icon'] ) {
			$args['icon'] = '<img src="' . $args['icon'] . '" /> ';
		}

		add_action( 'wp_footer', array( &$this, 'shortcode_popup' ) );
		add_action( 'admin_footer', array( &$this, 'shortcode_popup' ) );

		wp_register_style( 'magnific-popup', plugins_url( 'css/magnific-popup.css', ATKP_PLUGIN_FILE ), false, '0.9.9', 'all' );
		wp_register_script( 'magnific-popup', plugins_url( 'js/magnific-popup.js', ATKP_PLUGIN_FILE ), array( 'jquery' ), '0.9.9', true );
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
        $button = '<a href="javascript:void(0);" class="atkp-generator-button ' . $args['class'] . '" title="' . $args['text'] . '" data-target="' . $args['target'] . '" data-mfp-src="#atkp-generator" data-shortcode="' . (string) $args['shortcode'] . '">' . $args['icon'] . $args['text'] . '</a>';

		if ( $args['echo'] ) {
            echo '<a href="javascript:void(0);" class="atkp-generator-button ' . esc_attr( $args['class'] ) . 
                '" title="' . esc_html__( $args['text'], ATKP_PLUGIN_PREFIX ) . '" data-target="' . esc_attr( $args['target'] ) . 
                '" data-mfp-src="#atkp-generator" data-shortcode="' . esc_html__( (string) $args['shortcode'], ATKP_PLUGIN_PREFIX) . '">' . 
                wp_kses( $args['icon'], array( 'img' => array( 'src' => array() ) ) ) . esc_html__( $args['text'], ATKP_PLUGIN_PREFIX ) . '</a>';
		} else {
			return $button;
		}
	}

	function shortcode_boxes() {

		$types = array( 'post', 'page' );

		foreach ( $types as $type ) {

			//add_meta_box(
			//    ATKP_SHORTCODE.'_detail_box',
			//    esc_html__( 'Affiliate Toolkit Shortcodes', ATKP_PLUGIN_PREFIX),
			//    array(&$this, 'template_detail_box_content'),
			//   $type,
			//    'side',
			//    'default'
			//);

			add_meta_box(
				ATKP_PLUGIN_PREFIX . '_product_box',
				esc_html__( 'Affiliate Toolkit Product', ATKP_PLUGIN_PREFIX ),
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
                    <label for="<?php echo esc_attr( ATKP_PLUGIN_PREFIX . '_product' ) ?>"><?php echo esc_html__( 'Main product', ATKP_PLUGIN_PREFIX ); ?>
                        :</label>
                </th>
                <td>
                    <select class="widefat" id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_product') ?>"
                            name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_product') ?>">
						<?php
						$val = ATKPTools::get_post_setting( $post->ID, ATKP_PLUGIN_PREFIX . '_product' );

						echo '<option value="" ' . ( $val == '' ? 'selected' : '' ) . '>' . esc_html__( 'None', ATKP_PLUGIN_PREFIX ) . '</option>';

						global $post;
						$args        = array(
							'post_type'      => ATKP_PRODUCT_POSTTYPE,
							'posts_per_page' => 300,
							'post_status'    => array( 'publish', 'draft' )
						);
						$posts_array = get_posts( $args );
						foreach ( $posts_array as $prd ) {
							echo '<option value="' . esc_attr( $prd->ID ) . '"' . ( $val == $prd->ID ? 'selected' : '' ) . '>' . esc_html__( $prd->post_title, ATKP_PLUGIN_PREFIX ) . ' (' . esc_html( $prd->ID ) . ')' . '</option>';
						};
						?>
                    </select>
                </td>
            </tr>
        </table

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

		$productid = ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_product', 'int' );

		//lade das produkt und schau ob es vorhanden ist
		$prd = get_post( $productid );

		if ( ! isset( $prd ) || $prd == null ) {
			//wenn es nicht existiert, verknüpfung löschen
			ATKPTools::set_post_setting( $post_id, ATKP_PLUGIN_PREFIX . '_product', null );
		} else {
			//wenn das Produkt exisitert, dann lege in diesem Beitrag ein benutzerdefiniertes Feld an
			ATKPTools::set_post_setting( $post_id, ATKP_PLUGIN_PREFIX . '_product', $productid );


			//im produkt selbst kann es mehrere verknüpfte Beiträge geben
			$postids = array();

			$args = array(
				'post_type'   => array( 'post', 'page' ),  // YOUR POST TYPE
				'post_status' => array( 'publish', 'draft' ),
				'meta_query'  => array(
					array(
						'key'     => ATKP_PLUGIN_PREFIX . '_product',
						'value'   => $productid,
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
					array_push( $postids, $query->post->ID );
				}

			}

			wp_reset_postdata();

			ATKPTools::set_post_setting( $productid, ATKP_PRODUCT_POSTTYPE . '_postid', $postids );

		}

	}

	function template_detail_box_content( $post ) {
		?>


        <p>
            <label for="<?php echo esc_attr( ATKP_SHORTCODE . '_type' ) ?>"><?php echo esc_html__( 'Type', ATKP_PLUGIN_PREFIX ); ?>
                :</label>
            <select class="widefat" id="<?php echo esc_attr(ATKP_SHORTCODE . '_type') ?>"
                    name="<?php echo esc_attr(ATKP_SHORTCODE . '_type') ?>">
				<?php
				echo '<option value="1">' . esc_html__( 'product', ATKP_PLUGIN_PREFIX ) . '</option>';
				echo '<option value="2">' . esc_html__( 'list', ATKP_PLUGIN_PREFIX ) . '</option>';
				echo '<option value="3">' . esc_html__( 'field', ATKP_PLUGIN_PREFIX ) . '</option>';
				echo '<option value="4">' . esc_html__( 'link', ATKP_PLUGIN_PREFIX ) . '</option>';
				?>
            </select>
        </p>

        <div id="<?php echo esc_attr(ATKP_SHORTCODE . '_product_div') ?>">
            <p>
                <label for="<?php echo esc_attr( ATKP_SHORTCODE . '_product' ) ?>"><?php echo esc_html__( 'Product', ATKP_PLUGIN_PREFIX ); ?>
                    :</label>
                <select class="widefat" id="<?php echo esc_attr(ATKP_SHORTCODE . '_product') ?>"
                        name="<?php echo esc_attr(ATKP_SHORTCODE . '_product') ?>">
					<?php

					global $post;
					$args        = array(
						'post_type'      => ATKP_PRODUCT_POSTTYPE,
						'posts_per_page' => 300,
						'post_status'    => array( 'publish', 'draft' )
					);
					$posts_array = get_posts( $args );
					foreach ( $posts_array as $prd ) {

						echo '<option value="' . esc_attr( $prd->ID ) . '"' . esc_attr( $sel ) . '>' . esc_html__( $prd->post_title, ATKP_PLUGIN_PREFIX ) . ' (' . esc_html( $prd->ID ) . ')' . '</option>';
					};
					?>
                </select>
            </p>

        </div>
        <div id="<?php echo esc_attr(ATKP_SHORTCODE . '_list_div') ?>">
            <p>
                <label for="<?php echo esc_attr( ATKP_SHORTCODE . '_list' ) ?>"><?php echo esc_html__( 'List', ATKP_PLUGIN_PREFIX ); ?>
                    :</label>
                <select class="widefat" id="<?php echo esc_attr(ATKP_SHORTCODE . '_list') ?>"
                        name="<?php echo esc_attr(ATKP_SHORTCODE . '_list') ?>">
					<?php

					global $post;
					$args        = array(
						'post_type'      => ATKP_LIST_POSTTYPE,
						'posts_per_page' => 300,
						'post_status'    => 'publish'
					);
					$posts_array = get_posts( $args );
					foreach ( $posts_array as $prd ) {

						echo '<option value="' . esc_attr( $prd->ID ) . '"' . esc_attr( $sel ) . '>' . esc_html__( $prd->post_title, ATKP_PLUGIN_PREFIX ) . ' (' . esc_html( $prd->ID ) . ')' . '</option>';
					};
					?>
                </select></p>


            <p>
                <label for="<?php echo esc_attr( ATKP_SHORTCODE . '_limit' ) ?>"><?php echo esc_html__( 'Limit', ATKP_PLUGIN_PREFIX ); ?>
                    :</label>
                <input class="widefat" id="<?php echo esc_attr(ATKP_SHORTCODE . '_limit') ?>"
                       name="<?php echo esc_attr(ATKP_SHORTCODE . '_limit') ?>" type="number" min="1" value=""/>
            </p>

            <p>
                <input type="checkbox" id="<?php echo esc_attr(ATKP_SHORTCODE . '_random') ?>"
                       name="<?php echo esc_attr(ATKP_SHORTCODE . '_random') ?>">

                <label for="<?php echo esc_attr(ATKP_SHORTCODE . '_random') ?>">
	                <?php echo esc_html__( 'Random sort', ATKP_PLUGIN_PREFIX ) ?>
                </label>
            </p>
        </div>
        <div id="<?php echo esc_attr(ATKP_SHORTCODE . '_template_div') ?>">
            <p>
                <label for="<?php echo esc_attr( ATKP_SHORTCODE . '_template' ) ?>"><?php echo esc_html__( 'Template', ATKP_PLUGIN_PREFIX ); ?>
                    :</label>
                <select class="widefat" id="<?php echo esc_attr(ATKP_SHORTCODE . '_template') ?>"
                        name="<?php echo esc_attr(ATKP_SHORTCODE . '_template') ?>">
					<?php
					echo '<option value="">' . esc_html__( 'default', ATKP_PLUGIN_PREFIX ) . '</option>';

					echo '<option value="bestseller">' . esc_html__( 'bestseller', ATKP_PLUGIN_PREFIX ) . '</option>';
					echo '<option value="wide">' . esc_html__( 'wide', ATKP_PLUGIN_PREFIX ) . '</option>';
					echo '<option value="secondwide">' . esc_html__( 'secondwide', ATKP_PLUGIN_PREFIX ) . '</option>';
					echo '<option value="box">' . esc_html__( 'box', ATKP_PLUGIN_PREFIX ) . '</option>';
					echo '<option value="detailoffers">' . esc_html__( 'all offers', ATKP_PLUGIN_PREFIX ) . '</option>';



						global $post;
						$args        = array(
							'post_type'      => ATKP_TEMPLATE_POSTTYPE,
							'posts_per_page' => 300,
							'post_status'    => array( 'publish', 'draft' )
						);
						$posts_array = get_posts( $args );
						foreach ( $posts_array as $prd ) {

							echo '<option value="' . esc_attr( $prd->ID ) . '"' . esc_attr( $sel ) . '>' . esc_html__( $prd->post_title, ATKP_PLUGIN_PREFIX ) . ' (' . esc_html( $prd->ID ) . ')' . '</option>';
						};
					?>
                </select>
            </p>

            <p>
                <label for="<?php echo esc_attr( ATKP_SHORTCODE . '_containercssclass' ) ?>"><?php echo esc_html__( 'Container CSS Class', ATKP_PLUGIN_PREFIX ); ?>
                    :</label>
                <input class="widefat" id="<?php echo esc_attr(ATKP_SHORTCODE . '_containercssclass') ?>"
                       name="<?php echo esc_attr(ATKP_SHORTCODE . '_containercssclass') ?>" type="text" value=""/>
            </p>

            <p>
                <label for="<?php echo esc_attr( ATKP_SHORTCODE . '_elementcssclass' ) ?>"><?php echo esc_html__( 'Element CSS Class', ATKP_PLUGIN_PREFIX ); ?>
                    :</label>
                <input class="widefat" id="<?php echo esc_attr(ATKP_SHORTCODE . '_elementcssclass') ?>"
                       name="<?php echo esc_attr(ATKP_SHORTCODE . '_elementcssclass') ?>" type="text" value=""/>
            </p>
        </div>

        <div id="<?php echo esc_attr(ATKP_SHORTCODE . '_align_div') ?>">
            <p>
                <label for="<?php echo esc_attr( ATKP_SHORTCODE . '_align' ) ?>"><?php echo esc_html__( 'Align', ATKP_PLUGIN_PREFIX ); ?>
                    :</label>
                <select class="widefat" id="<?php echo esc_attr(ATKP_SHORTCODE . '_align') ?>"
                        name="<?php echo esc_attr(ATKP_SHORTCODE . '_align') ?>">
                    <option value=""><?php echo esc_html__( 'no alignment', ATKP_PLUGIN_PREFIX ); ?></option>
                    <option value="atkp-left atkp-clearfix"><?php echo esc_html__( 'left', ATKP_PLUGIN_PREFIX ); ?></option>
                    <option value="atkp-center"><?php echo esc_html__( 'center', ATKP_PLUGIN_PREFIX ); ?></option>
                    <option value="atkp-right atkp-clearfix"><?php echo esc_html__( 'right', ATKP_PLUGIN_PREFIX ); ?></option>
                </select>
            </p>
        </div>

        <div id="<?php echo esc_attr(ATKP_SHORTCODE . '_content_div') ?>">
            <p>
                <label for="<?php echo esc_attr( ATKP_SHORTCODE . '_content' ) ?>"><?php echo esc_html__( 'Content', ATKP_PLUGIN_PREFIX ); ?>
                    :</label>
                <input class="widefat" id="<?php echo esc_attr(ATKP_SHORTCODE . '_content') ?>"
                       name="<?php echo esc_attr(ATKP_SHORTCODE . '__content') ?>" type="text" value=""/>
            </p>
        </div>

        <div id="<?php echo esc_attr(ATKP_SHORTCODE . '_field_div') ?>">
            <p>
                <label for="<?php echo esc_attr( ATKP_SHORTCODE . '_field' ) ?>"><?php echo esc_html__( 'Field', ATKP_PLUGIN_PREFIX ); ?>
                    :</label>
                <select class="widefat" id="<?php echo esc_attr(ATKP_SHORTCODE . '_field') ?>"
                        name="<?php echo esc_attr(ATKP_SHORTCODE . '_field') ?>">
					<?php


					$templatehelper = new atkp_template_helper();
					$placeholders   = $templatehelper->getPlaceholders();

					foreach ( $placeholders as $placeholder => $caption ) {
						echo '<option value="' . esc_attr( $placeholder ) . '">' . esc_html__( $caption, ATKP_PLUGIN_PREFIX ) . '</option>';

					};
					?>
                </select>
            </p>
            <p>
                <input type="checkbox" id="<?php echo esc_attr(ATKP_SHORTCODE . '_link') ?>"
                       name="<?php echo esc_attr(ATKP_SHORTCODE . '_link') ?>">

                <label for="<?php echo esc_attr(ATKP_SHORTCODE . '_link') ?>">
	                <?php echo esc_html__( 'Hyperlink', ATKP_PLUGIN_PREFIX ) ?>
                </label>
            </p>
        </div>
        <div id="<?php echo esc_attr(ATKP_SHORTCODE . '_button_div') ?>">
            <p>
                <label for="<?php echo esc_attr( ATKP_SHORTCODE . '_buttontype' ) ?>"><?php echo esc_html__( 'Button type', ATKP_PLUGIN_PREFIX ); ?>
                    :</label>
                <select class="widefat" id="<?php echo esc_attr(ATKP_SHORTCODE . '_buttontype') ?>"
                        name="<?php echo esc_attr(ATKP_SHORTCODE . '_buttontype') ?>">
					<?php

					echo '<option value="">' . esc_html__( 'default', ATKP_PLUGIN_PREFIX ) . '</option>';
					echo '<option value="addtocart">' . esc_html__( 'add to cart', ATKP_PLUGIN_PREFIX ) . '</option>';
					echo '<option value="link">' . esc_html__( 'link', ATKP_PLUGIN_PREFIX ) . '</option>';
					echo '<option value="product">' . esc_html__( 'product page', ATKP_PLUGIN_PREFIX ) . '</option>';
					?>
                </select>
            </p>
        </div>
        <div class="atkp-short_result">
            <input type="text" id="<?php echo esc_attr(ATKP_SHORTCODE . '_shortcode_txt') ?>" style="width:100%" readonly="">

            <div>
                <a href="javascript:void(0);" id="<?php echo esc_attr(ATKP_SHORTCODE . '_shortcode_btn') ?>"
                   style="margin-top:5px" class="button"><i
                            class="fa fa-refresh"></i>&nbsp;<?php echo esc_html__( 'Update shortcode', ATKP_PLUGIN_PREFIX ) ?>
                </a>

                <a href="javascript:void(0);" id='atkp-generator-insert' class="button button-primary"
                   style="margin-top:5px"><i
                            class="fa fa-check"></i>&nbsp;<?php echo esc_html__( 'Insert shortcode', ATKP_PLUGIN_PREFIX ) ?>
                </a>
            </div>
            &nbsp;
        </div>
        <script type="text/javascript">
            var $j = jQuery.noConflict();
            $j(document).ready(function ($) {

                $j('#<?php echo esc_js(ATKP_SHORTCODE . '_type') ?>').change(function () {

                    var $productdiv = $j('#<?php echo esc_js(ATKP_SHORTCODE . '_product_div') ?>');
                    var $listdiv = $j('#<?php echo esc_js(ATKP_SHORTCODE . '_list_div') ?>');
                    var $templatediv = $j('#<?php echo esc_js(ATKP_SHORTCODE . '_template_div') ?>');

                    var $contentdiv = $j('#<?php echo esc_js(ATKP_SHORTCODE . '_content_div') ?>');
                    var $fielddiv = $j('#<?php echo esc_js(ATKP_SHORTCODE . '_field_div') ?>');
                    var $aligndiv = $j('#<?php echo esc_js(ATKP_SHORTCODE . '_align_div') ?>');
                    var $buttondiv = $j('#<?php echo esc_js(ATKP_SHORTCODE . '_button_div') ?>');

                    $productdiv.hide();
                    $listdiv.hide();
                    $templatediv.hide();
                    $aligndiv.hide();

                    $contentdiv.hide();
                    $fielddiv.hide();
                    $buttondiv.hide();


                    switch ($j('#<?php echo esc_js(ATKP_SHORTCODE . '_type') ?>').val()) {

                        case '1':
                            //product
                            $productdiv.show();
                            $templatediv.show();
                            $contentdiv.show();
                            $buttondiv.show();
                            $aligndiv.show();
                            break;
                        case '2':
                            //list
                            $templatediv.show();
                            $listdiv.show();
                            $contentdiv.show();
                            $buttondiv.show();
                            break;
                        case '3':
                            //field
                            $productdiv.show();
                            $fielddiv.show();
                            $aligndiv.show();
                            break;
                        case '4':
                            //link
                            $productdiv.show();
                            $contentdiv.show();
                            break;
                    }


                });


                $j('#<?php echo esc_js(ATKP_SHORTCODE . '_type') ?>').trigger("change");


                $j('#<?php echo esc_js(ATKP_SHORTCODE . '_shortcode_btn') ?>').click(function (e) {
                    var $shortcode = '';

                    switch ($j('#<?php echo esc_js(ATKP_SHORTCODE . '_type') ?>').val()) {

                        case '1':
                            //product
                            $shortcode = '[atkp_product'

                            if ($j('#<?php echo esc_js(ATKP_SHORTCODE . '_product') ?>').val() != '')
                                $shortcode += ' id=\'' + $j('#<?php echo esc_js(ATKP_SHORTCODE . '_product') ?>').val() + '\'';

                            if ($j('#<?php echo esc_js(ATKP_SHORTCODE . '_template') ?>').val() != '')
                                $shortcode += ' template=\'' + $j('#<?php echo esc_js(ATKP_SHORTCODE . '_template') ?>').val() + '\'';

                            if ($j('#<?php echo esc_js(ATKP_SHORTCODE . '_elementcssclass') ?>').val() != '')
                                $shortcode += ' elementcss=\'' + $j('#<?php echo esc_js(ATKP_SHORTCODE . '_elementcssclass') ?>').val() + '\'';

                            var $containercss = '';

                            if ($j('#<?php echo esc_js(ATKP_SHORTCODE . '_align') ?>').val() != '')
                                $containercss = $j('#<?php echo esc_js(ATKP_SHORTCODE . '_align') ?>').val();
                            if ($j('#<?php echo esc_js(ATKP_SHORTCODE . '_containercssclass') ?>').val() != '')
                                $containercss = $containercss + ' ' + $j('#<?php echo esc_js(ATKP_SHORTCODE . '_containercssclass') ?>').val();


                            if ($containercss != '')
                                $shortcode += ' containercss=\'' + $containercss + '\'';

                            if ($j('#<?php echo esc_js(ATKP_SHORTCODE . '_buttontype') ?>').val() != '')
                                $shortcode += ' buttontype=\'' + $j('#<?php echo esc_js(ATKP_SHORTCODE . '_buttontype') ?>').val() + '\'';


                            $shortcode += ']';

                            if ($j('#<?php echo esc_js(ATKP_SHORTCODE . '_content') ?>').val() != '')
                                $shortcode += $j('#<?php echo esc_js(ATKP_SHORTCODE . '_content') ?>').val();


                            $shortcode += '[/atkp_product]';

                            break;
                        case '2':
                            //list
                            $shortcode = '[atkp_list'

                            if ($j('#<?php echo esc_js(ATKP_SHORTCODE . '_list') ?>').val() != '')
                                $shortcode += ' id=\'' + $j('#<?php echo esc_js(ATKP_SHORTCODE . '_list') ?>').val() + '\'';

                            if ($j('#<?php echo esc_js(ATKP_SHORTCODE . '_template') ?>').val() != '')
                                $shortcode += ' template=\'' + $j('#<?php echo esc_js(ATKP_SHORTCODE . '_template') ?>').val() + '\'';

                            if ($j('#<?php echo esc_js(ATKP_SHORTCODE . '_elementcssclass') ?>').val() != '')
                                $shortcode += ' elementcss=\'' + $j('#<?php echo esc_js(ATKP_SHORTCODE . '_elementcssclass') ?>').val() + '\'';

                            if ($j('#<?php echo esc_js(ATKP_SHORTCODE . '_containercssclass') ?>').val() != '')
                                $shortcode += ' containercss=\'' + $j('#<?php echo esc_js(ATKP_SHORTCODE . '_containercssclass') ?>').val() + '\'';

                            if ($j('#<?php echo esc_js(ATKP_SHORTCODE . '_buttontype') ?>').val() != '')
                                $shortcode += ' buttontype=\'' + $j('#<?php echo esc_js(ATKP_SHORTCODE . '_buttontype') ?>').val() + '\'';

                            if ($j('#<?php echo esc_js(ATKP_SHORTCODE . '_limit') ?>').val() != '')
                                $shortcode += ' limit=\'' + $j('#<?php echo esc_js(ATKP_SHORTCODE . '_limit') ?>').val() + '\'';

                            if ($j('#<?php echo esc_js(ATKP_SHORTCODE . '_random') ?>').prop('checked'))
                                $shortcode += ' randomsort=\'yes\'';

                            $shortcode += ']';

                            if ($j('#<?php echo esc_js(ATKP_SHORTCODE . '_content') ?>').val() != '')
                                $shortcode += $j('#<?php echo esc_js(ATKP_SHORTCODE . '_content') ?>').val();

                            $shortcode += '[/atkp_list]';

                            break;
                        case '3':
                            //field

                            $shortcode = '[atkp_product'

                            if ($j('#<?php echo esc_js(ATKP_SHORTCODE . '_product') ?>').val() != '')
                                $shortcode += ' id=\'' + $j('#<?php echo esc_js(ATKP_SHORTCODE . '_product') ?>').val() + '\'';

                            if ($j('#<?php echo esc_js(ATKP_SHORTCODE . '_field') ?>').val() != '')
                                $shortcode += ' field=\'' + $j('#<?php echo esc_js(ATKP_SHORTCODE . '_field') ?>').val() + '\'';

                            var $containercss = '';

                            if ($j('#<?php echo esc_js(ATKP_SHORTCODE . '_align') ?>').val() != '')
                                $containercss = $j('#<?php echo esc_js(ATKP_SHORTCODE . '_align') ?>').val();

                            if ($containercss != '')
                                $shortcode += ' containercss=\'' + $containercss + '\'';

                            if ($j('#<?php echo esc_js(ATKP_SHORTCODE . '_link') ?>').prop('checked')) {
                                $shortcode += ' link=\'yes\'';
                            }

                            $shortcode += ']';


                            $shortcode += '[/atkp_product]';


                            break;
                        case '4':
                            //link
                            $shortcode = '[atkp_product]'

                            $shortcode = '[atkp_product'

                            if ($j('#<?php echo esc_js(ATKP_SHORTCODE . '_product') ?>').val() != '')
                                $shortcode += ' id=\'' + $j('#<?php echo esc_js(ATKP_SHORTCODE . '_product') ?>').val() + '\'';


                            $shortcode += ' link=\'yes\'';


                            $shortcode += ']';

                            if ($j('#<?php echo esc_js(ATKP_SHORTCODE . '_content') ?>').val() != '')
                                $shortcode += $j('#<?php echo esc_js(ATKP_SHORTCODE . '_content') ?>').val();


                            $shortcode += '[/atkp_product]';
                            break;
                    }

                    $j('#<?php echo esc_js(ATKP_SHORTCODE . '_shortcode_txt') ?>').val($shortcode)

                });

                $j('#<?php echo esc_js(ATKP_SHORTCODE . '_shortcode_txt') ?>').click(function (e) {


                    $j('#<?php echo esc_js(ATKP_SHORTCODE . '_shortcode_txt') ?>').select();

                });
            });
        </script>

		<?php
	}
}

?>