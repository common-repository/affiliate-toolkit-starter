<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

// Creating the widget 
class atkp_widget extends WP_Widget {

	function __construct() {
		parent::__construct(
		// Base ID of your widget
			ATKP_WIDGET,

			// Widget name will appear in UI
			__( 'affiliate-toolkit widget', ATKP_PLUGIN_PREFIX ),

			// Widget description
			array( 'description' => __( 'Shows a product or a list.', ATKP_PLUGIN_PREFIX ), )
		);


		add_action( 'admin_footer', array( $this, 'add_script_footer' ) );
	}

	function add_script_footer() {
		?>

        <script type="text/javascript">

			<?php $searchnounce = wp_create_nonce( 'atkp-search-nonce' ); ?>

            function formatRepo(value) {
                if (value.loading) return value.text;

                if (value.id == '')
                    return "<?php echo esc_html__( 'no product', ATKP_PLUGIN_PREFIX ) ?>";

                var outputresult = '<table style="width:100%">';
                outputresult += '<tr style="height:50px;">';
                outputresult += '<td style="margin-left:3px;width:60px;height:50px;text-align:center"><img style="max-width:50px" src="' + value.imageurl + '" /></td>';
                outputresult += '<td><span style="font-size:10px">ID: ' + value.id + ' - ' + value.shop + '</span><br /><b>' + value.title + '</b></td>';
                outputresult += '</tr>';
                outputresult += '</table>';

                return outputresult;
            }

            function formatList(value) {
                if (value.loading) return value.text;

                if (value.id == '')
                    return "<?php echo esc_html__( 'no list', ATKP_PLUGIN_PREFIX ) ?>";

                var outputresult = '<table style="width:100%">';
                outputresult += '<tr style="height:50px;">';
                outputresult += '<td><span style="font-size:10px">ID: ' + value.id + ' - ' + value.shop + '</span><br /><b>' + value.title + '</b></td>';
                outputresult += '</tr>';
                outputresult += '</table>';

                return outputresult;
            }

            function formatRepoSelection(repo) {
                if (repo.id == '')
                    return "<?php echo esc_html__( 'no product', ATKP_PLUGIN_PREFIX ) ?>";
                else
                    return (repo.text || (repo.title) + ' (' + repo.id + ')');
            }

            function formatListSelection(repo) {
                if (repo.id == '')
                    return "<?php echo esc_html__( 'no list', ATKP_PLUGIN_PREFIX ) ?>";
                else
                    return (repo.text || (repo.title) + ' (' + repo.id + ')');
            }

            var $j = jQuery.noConflict();
            $j(document).ready(function ($) {

                function handle_widget_loading() {

	                <?php $disable_select2 = atkp_options::$loader->get_disableselect2_backend() || atkp_options::$loader->get_disableselect2_widget(); // ATKPTools::get_setting( ATKP_PLUGIN_PREFIX . '_disableselect2', false );

					if(! $disable_select2) {
					?>

                    $j(".atkp-widget-select-product").select2atkp({

                        ajax: {
                            type: "POST",
                            url: "<?php echo esc_url_raw(ATKPTools::get_endpointurl()); ?>",
                            dataType: 'json',
                            delay: 250,
                            data: function (params) {
                                return {
                                    action: "atkp_search_local_products",
                                    type: "<?php echo esc_html__( ATKP_PRODUCT_POSTTYPE, ATKP_PLUGIN_PREFIX ); ?>",
                                    request_nonce: "<?php echo esc_html__( $searchnounce, ATKP_PLUGIN_PREFIX ); ?>",
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
                                    name: "<?php echo esc_html__( 'no product', ATKP_PLUGIN_PREFIX ) ?>"
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
                        escapeMarkup: function (markup) {
                            return markup;
                        }, // let our custom formatter work
                        minimumInputLength: 3,
                        templateResult: formatRepo, // omitted for brevity, see the source of this page
                        templateSelection: formatRepoSelection // omitted for brevity, see the source of this page
                    });

                    $j(".atkp-widget-select-list").select2atkp({

                        ajax: {
                            type: "POST",
                            url: "<?php echo esc_url_raw(ATKPTools::get_endpointurl()); ?>",
                            dataType: 'json',
                            delay: 250,
                            data: function (params) {
                                return {
                                    action: "atkp_search_local_products",
                                    type: "<?php echo esc_html__( ATKP_LIST_POSTTYPE, ATKP_PLUGIN_PREFIX ); ?>",
                                    request_nonce: "<?php echo esc_html__( $searchnounce, ATKP_PLUGIN_PREFIX ); ?>",
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
                                    name: "<?php echo esc_html__( 'no list', ATKP_PLUGIN_PREFIX ) ?>"
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
                        escapeMarkup: function (markup) {
                            return markup;
                        }, // let our custom formatter work
                        minimumInputLength: 3,
                        templateResult: formatList, // omitted for brevity, see the source of this page
                        templateSelection: formatListSelection // omitted for brevity, see the source of this page
                    });

					<?php } ?>
                }

                jQuery(document).ready(handle_widget_loading);
                jQuery(document).on('widget-updated widget-added', handle_widget_loading);

                handle_widget_loading();

                //atkp_txt_prdsearch
                //atkp_btn_prdsearch
                //atkp_prdresult
                //atkp_prdloading


            });
        </script>

		<?php
	}

	// Creating widget front-end
	// This is where the action happens
	public function widget( $args, $instance ) {

		$title = apply_filters( 'widget_title', isset( $instance[ ATKP_WIDGET . '_title' ] ) ? $instance[ ATKP_WIDGET . '_title' ] : '', $instance );
		// before and after widget arguments are defined by themes

		$product           = 0;
		$list              = 0;
		$template          = 'box';
		$addintocart       = 'notset';
		$containercssclass = '';
		$elementcssclass   = '';
		$limit             = 10;
		$randomsort        = false;
		$usemainproduct    = false;
		$content           = '';

		if ( isset( $instance[ ATKP_WIDGET . '_product' ] ) ) {
			$product = intval( $instance[ ATKP_WIDGET . '_product' ] );
		}
		if ( isset( $instance[ ATKP_WIDGET . '_list' ] ) ) {
			$list = intval( $instance[ ATKP_WIDGET . '_list' ] );
		}
		if ( isset( $instance[ ATKP_WIDGET . '_template' ] ) && ! empty( $instance[ ATKP_WIDGET . '_template' ] ) ) {
			$template = $instance[ ATKP_WIDGET . '_template' ];
		}

		if ( isset( $instance[ ATKP_WIDGET . '_containercssclass' ] ) && ! empty( $instance[ ATKP_WIDGET . '_containercssclass' ] ) ) {
			$containercssclass = $instance[ ATKP_WIDGET . '_containercssclass' ];
		}
		if ( isset( $instance[ ATKP_WIDGET . '_elementcssclass' ] ) && ! empty( $instance[ ATKP_WIDGET . '_elementcssclass' ] ) ) {
			$elementcssclass = $instance[ ATKP_WIDGET . '_elementcssclass' ];
		}
		if ( isset( $instance[ ATKP_WIDGET . '_limit' ] ) && ! empty( $instance[ ATKP_WIDGET . '_limit' ] ) ) {
			$limit = intval( $instance[ ATKP_WIDGET . '_limit' ] );
		}
		if ( isset( $instance[ ATKP_WIDGET . '_random' ] ) && ! empty( $instance[ ATKP_WIDGET . '_random' ] ) ) {
			$randomsort = (bool) $instance[ ATKP_WIDGET . '_random' ];
		}

		if ( isset( $instance[ ATKP_WIDGET . '_usemainproduct' ] ) && ! empty( $instance[ ATKP_WIDGET . '_usemainproduct' ] ) ) {
			$usemainproduct = (bool) $instance[ ATKP_WIDGET . '_usemainproduct' ];
		}

		if ( isset( $instance[ ATKP_WIDGET . '_content' ] ) && ! empty( $instance[ ATKP_WIDGET . '_content' ] ) ) {
			$content = $instance[ ATKP_WIDGET . '_content' ];
		}

		$elementcssclass = $elementcssclass . ' atkp-widget';

		if ( $template == '' ) {
			$template = 'box';
		}

		require_once ATKP_PLUGIN_DIR . '/includes/atkp_output.php';

		$output = new atkp_output();

		if ( $usemainproduct && ( is_single() || is_page() ) ) {

			if ( get_post_type() == 'product' ) {
				//require_once ATKP_PLUGIN_DIR . '/includes/atkp_product.php';

				$woo_id  = get_the_ID();
				$product = atkp_product::get_product_from_woo( $woo_id );

			} else if ( get_post_type() == ATKP_PRODUCT_POSTTYPE ) {
				$product        = get_the_ID();
				$hidedisclaimer = true;

			} else {
				$queried_object = get_queried_object();

				if ( $queried_object ) {
					$post_id = $queried_object->ID;
					$product = ATKPTools::get_post_setting( $post_id, ATKP_PLUGIN_PREFIX . '_product' );
				}
			}


		}

		if ( $product != '' && $product != 0 ) {
			echo ( $args['before_widget'] );
			if ( ! empty( $title ) ) {
				echo ( $args['before_title'] . $title . $args['after_title'] );
			}

			try {
				echo ( $output->get_product_output( $product, $template, $content, 'notset', '', false, $elementcssclass, $containercssclass, false ) );
			} catch ( Exception $e ) {
				echo ( 'Exception: ' . $e->getMessage() );
			}

			echo ( $args['after_widget'] );
		} else if ( $list != '' && $list != 0 ) {
			echo ( $args['before_widget'] );
			if ( ! empty( $title ) ) {
				echo ( $args['before_title'] . $title . $args['after_title'] );
			}

			//create list and output
			try {
				echo ( $output->get_list_output( $list, $template, $content, 'notset', $elementcssclass, $containercssclass, $limit, $randomsort, false ) );
			} catch ( Exception $e ) {
				echo ( 'Exception: ' . $e->getMessage() );
			}
			echo ( $args['after_widget'] );
		}


	}

	// Widget Backend
	public function form( $instance ) {

		// Widget admin form
		?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php echo esc_html__( 'Title', ATKP_PLUGIN_PREFIX ); ?></label>:
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id( 'title' )); ?>"
                   name="<?php echo esc_attr($this->get_field_name( ATKP_WIDGET . '_title' )); ?>" type="text"
                   value="<?php echo isset( $instance[ ATKP_WIDGET . '_title' ] ) ? esc_attr( $instance[ ATKP_WIDGET . '_title' ] ) : ''; ?>"/>
        </p>

        <p>
            <input type="checkbox" id="<?php echo esc_attr($this->get_field_id( ATKP_WIDGET . '_usemainproduct' )); ?>"
                   name="<?php echo esc_attr($this->get_field_name( ATKP_WIDGET . '_usemainproduct' )); ?>"
                   value="1" <?php echo checked( 1, isset( $instance[ ATKP_WIDGET . '_usemainproduct' ] ) ? $instance[ ATKP_WIDGET . '_usemainproduct' ] : false, true ); ?>>

            <label for="<?php echo esc_attr($this->get_field_id( ATKP_WIDGET . '_usemainproduct' )); ?>">
	            <?php echo esc_html__( 'Use main product', ATKP_PLUGIN_PREFIX ) ?>
            </label>
        </p>

        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( ATKP_WIDGET . '_product' ) ); ?>"><?php echo esc_html__( 'Product', ATKP_PLUGIN_PREFIX ); ?>
                :</label>
            <select style="width: 100%" class="widefat atkp-widget-select-product"
                    id="<?php echo esc_attr($this->get_field_id( ATKP_WIDGET . '_product' )); ?>"
                    name="<?php echo esc_attr($this->get_field_name( ATKP_WIDGET . '_product' )); ?>" >
				<?php

				$disable_select2 = atkp_options::$loader->get_disableselect2_backend() || atkp_options::$loader->get_disableselect2_widget(); // ATKPTools::get_setting( ATKP_PLUGIN_PREFIX . '_disableselect2', false );

				if ( $disable_select2 ) {

					echo '<option value="">' . esc_html__( 'no product', ATKP_PLUGIN_PREFIX ) . '</option>';

					$args        = array(
						'post_type'      => ATKP_PRODUCT_POSTTYPE,
						'posts_per_page' => 500,
						'post_status'    => array( 'publish', 'draft' )
					);
					$posts_array = get_posts( $args );
					foreach ( $posts_array as $prd ) {

						if ( isset( $instance[ ATKP_WIDGET . '_product' ] ) && $prd->ID == $instance[ ATKP_WIDGET . '_product' ] ) {
							$sel = ' selected';
						} else {
							$sel = '';
						}

						echo '<option value="' . esc_attr( $prd->ID ) . '"' . esc_attr( $sel ) . '>' . esc_html__( $prd->post_title ) . ' (' . esc_html__( $prd->ID, ATKP_PLUGIN_PREFIX ) . ')' . '</option>';
					};

				} else {
					if ( isset( $instance[ ATKP_WIDGET . '_product' ] ) && $instance[ ATKP_WIDGET . '_product' ] != '' ) {
						$prd = get_post( $instance[ ATKP_WIDGET . '_product' ] );
						if ( $prd != null ) {
							echo '<option value="' . esc_attr( $prd->ID ) . '">' . esc_html__( $prd->post_title, ATKP_PLUGIN_PREFIX ) . ' (' . esc_html__( $prd->ID, ATKP_PLUGIN_PREFIX ) . ')' . '</option>';
						}

					} else {
						echo '<option value="" >' . esc_html__( 'no product', ATKP_PLUGIN_PREFIX ) . '</option>';
					}
				}

				?>
            </select>
        </p>

        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( ATKP_WIDGET . '_list' ) ); ?>"><?php echo esc_html__( 'List', ATKP_PLUGIN_PREFIX ); ?>
                :</label>
            <select style="width: 100%" class="widefat atkp-widget-select-list" id="<?php echo esc_attr($this->get_field_id( ATKP_WIDGET . '_list' )); ?>"
                    name="<?php echo esc_attr($this->get_field_name( ATKP_WIDGET . '_list' )); ?>">
				<?php

				if ( $disable_select2 ) {

					echo '<option value="">' . esc_html__( 'no list', ATKP_PLUGIN_PREFIX ) . '</option>';

					$args        = array(
						'post_type'      => ATKP_LIST_POSTTYPE,
						'posts_per_page' => 500,
						'post_status'    => array( 'publish', 'draft' )
					);
					$posts_array = get_posts( $args );
					foreach ( $posts_array as $prd ) {

						if ( isset( $instance[ ATKP_WIDGET . '_list' ] ) && $prd->ID == $instance[ ATKP_WIDGET . '_list' ] ) {
							$sel = ' selected';
						} else {
							$sel = '';
						}

						echo '<option value="' . esc_attr( $prd->ID ) . '"' . esc_attr( $sel ) . '>' . esc_html__( $prd->post_title, ATKP_PLUGIN_PREFIX ) . ' (' . esc_html__( $prd->ID, ATKP_PLUGIN_PREFIX ) . ')' . '</option>';
					};

				} else {

					if ( isset( $instance[ ATKP_WIDGET . '_list' ] ) && $instance[ ATKP_WIDGET . '_list' ] != '' ) {
						$prd = get_post( $instance[ ATKP_WIDGET . '_list' ] );
						if ( $prd != null ) {
							echo '<option value="' . esc_attr( $prd->ID ) . '">' . esc_html__( $prd->post_title, ATKP_PLUGIN_PREFIX ) . ' (' . esc_html__( $prd->ID, ATKP_PLUGIN_PREFIX ) . ')' . '</option>';
						}

					} else {
						echo '<option value="">' . esc_html__( 'no list', ATKP_PLUGIN_PREFIX ) . '</option>';
					}

				}

				?>
            </select></p>

        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( ATKP_WIDGET . '_template' ) ); ?>"><?php echo esc_html__( 'Template', ATKP_PLUGIN_PREFIX ); ?>
                :</label>
            <select class="widefat" id="<?php echo esc_attr($this->get_field_id( ATKP_WIDGET . '_template' )); ?>"
                    name="<?php echo esc_attr($this->get_field_name( ATKP_WIDGET . '_template' )); ?>">
				<?php
				echo '<option value="">' . esc_html__( 'default', ATKP_PLUGIN_PREFIX ) . '</option>';

				$templates = atkp_template::get_list( true, false );

				foreach ( $templates as $template => $caption ) {
					if ( isset( $instance[ ATKP_WIDGET . '_template' ] ) && $template == $instance[ ATKP_WIDGET . '_template' ] ) {
						$sel = ' selected';
					} else {
						$sel = '';
					}

					echo '<option value="' . esc_attr( $template ) . '" ' . esc_attr( $sel ) . '>' . esc_html__( htmlentities( $caption ), ATKP_PLUGIN_PREFIX ) . '</option>';
				}

				?>
            </select>
        </p>

        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( ATKP_WIDGET . '_containercssclass' ) ); ?>"><?php echo esc_html__( 'Container CSS Class', ATKP_PLUGIN_PREFIX ); ?>
                :</label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id( ATKP_WIDGET . '_containercssclass' )); ?>"
                   name="<?php echo esc_attr($this->get_field_name( ATKP_WIDGET . '_containercssclass' )); ?>" type="text"
                   value="<?php echo esc_attr( isset( $instance[ ATKP_WIDGET . '_containercssclass' ] ) ? $instance[ ATKP_WIDGET . '_containercssclass' ] : '' ); ?>"/>
        </p>

        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( ATKP_WIDGET . '_elementcssclass' ) ); ?>"><?php echo esc_html__( 'Element CSS Class', ATKP_PLUGIN_PREFIX ); ?>
                :</label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id( ATKP_WIDGET . '_elementcssclass' )); ?>"
                   name="<?php echo esc_attr($this->get_field_name( ATKP_WIDGET . '_elementcssclass' )); ?>" type="text"
                   value="<?php echo esc_attr( isset( $instance[ ATKP_WIDGET . '_elementcssclass' ] ) ? $instance[ ATKP_WIDGET . '_elementcssclass' ] : '' ); ?>"/>
        </p>

        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( ATKP_WIDGET . '_limit' ) ); ?>"><?php echo esc_html__( 'Limit', ATKP_PLUGIN_PREFIX ); ?>
                :</label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id( ATKP_WIDGET . '_limit' )); ?>"
                   name="<?php echo esc_attr($this->get_field_name( ATKP_WIDGET . '_limit' )); ?>" type="number" min="1" max="10"
                   value="<?php echo esc_attr( isset( $instance[ ATKP_WIDGET . '_limit' ] ) ? $instance[ ATKP_WIDGET . '_limit' ] : '3' ); ?>"/>
        </p>

        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( ATKP_WIDGET . '_content' ) ); ?>"><?php echo esc_html__( 'Content', ATKP_PLUGIN_PREFIX ); ?>
                :</label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id( ATKP_WIDGET . '_content' )); ?>"
                   name="<?php echo esc_attr($this->get_field_name( ATKP_WIDGET . '_content' )); ?>" type="text"
                   value="<?php echo esc_attr( isset( $instance[ ATKP_WIDGET . '_content' ] ) ? $instance[ ATKP_WIDGET . '_content' ] : '' ); ?>"/>
        </p>

        <p>
            <input type="checkbox" id="<?php echo esc_attr($this->get_field_id( ATKP_WIDGET . '_random' )); ?>"
                   name="<?php echo esc_attr($this->get_field_name( ATKP_WIDGET . '_random' )); ?>"
                   value="1" <?php echo checked( 1, isset( $instance[ ATKP_WIDGET . '_random' ] ) ? $instance[ ATKP_WIDGET . '_random' ] : false, true ); ?>>

            <label for="<?php echo esc_attr($this->get_field_id( ATKP_WIDGET . '_random' )); ?>">
	            <?php echo esc_html__( 'Random list sorting', ATKP_PLUGIN_PREFIX ) ?>
            </label>
        </p>


		<?php
	}

	// Updating widget replacing old instances with new
	public function update( $new_instance, $old_instance ) {
		$instance = array();

		$instance[ ATKP_WIDGET . '_title' ]             = ( ! empty( $new_instance[ ATKP_WIDGET . '_title' ] ) ) ? strip_tags( $new_instance[ ATKP_WIDGET . '_title' ] ) : '';
		$instance[ ATKP_WIDGET . '_product' ]           = ( ! empty( $new_instance[ ATKP_WIDGET . '_product' ] ) ) ? $new_instance[ ATKP_WIDGET . '_product' ] : '';
		$instance[ ATKP_WIDGET . '_list' ]              = ( ! empty( $new_instance[ ATKP_WIDGET . '_list' ] ) ) ? $new_instance[ ATKP_WIDGET . '_list' ] : '';
		$instance[ ATKP_WIDGET . '_template' ]          = ( ! empty( $new_instance[ ATKP_WIDGET . '_template' ] ) ) ? $new_instance[ ATKP_WIDGET . '_template' ] : '';
		$instance[ ATKP_WIDGET . '_containercssclass' ] = ( ! empty( $new_instance[ ATKP_WIDGET . '_containercssclass' ] ) ) ? $new_instance[ ATKP_WIDGET . '_containercssclass' ] : '';
		$instance[ ATKP_WIDGET . '_elementcssclass' ]   = ( ! empty( $new_instance[ ATKP_WIDGET . '_elementcssclass' ] ) ) ? $new_instance[ ATKP_WIDGET . '_elementcssclass' ] : '';
		$instance[ ATKP_WIDGET . '_limit' ]             = ( ! empty( $new_instance[ ATKP_WIDGET . '_limit' ] ) ) ? $new_instance[ ATKP_WIDGET . '_limit' ] : '';
		$instance[ ATKP_WIDGET . '_random' ]            = ( ! empty( $new_instance[ ATKP_WIDGET . '_random' ] ) ) ? $new_instance[ ATKP_WIDGET . '_random' ] : '';
		$instance[ ATKP_WIDGET . '_usemainproduct' ]    = ( ! empty( $new_instance[ ATKP_WIDGET . '_usemainproduct' ] ) ) ? $new_instance[ ATKP_WIDGET . '_usemainproduct' ] : '';
		$instance[ ATKP_WIDGET . '_content' ]           = ( ! empty( $new_instance[ ATKP_WIDGET . '_content' ] ) ) ? $new_instance[ ATKP_WIDGET . '_content' ] : '';

		return $instance;
	}
} // Class wpb_widget ends here

// Register and load the widget
function atkp_load_widget() {
	register_widget( ATKP_WIDGET );
}

add_action( 'widgets_init', 'atkp_load_widget' );

?>