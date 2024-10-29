<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class atkp_generator {
	/**
	 * Construct the plugin object
	 */
	public function __construct() {

	}

	public function show_generator_backend_page() {
		if ( ! is_user_logged_in() ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page', ATKP_PLUGIN_PREFIX ) );
		}


		?>
        <h2 class="nav-tab-wrapper atkp-nav-tab">
            <a class="nav-tab nav-tab-active" href="#">Shortcode Generator</a>
        </h2>
        <div class="atkp-content wrap">
            <div class="inner">

				<?php $this->generate_main( self::OUTPUT_SHORTCODE ); ?>

            </div>

        </div>
		<?php
	}

	const OUTPUT_SHORTCODE = 'shortcode';
	const OUTPUT_SHORTCODE_INSERT = 'shortcode_insert';
	const OUTPUT_GUTENBERG = 'gutenberg';

	public function generate_main( $output_type = self::OUTPUT_SHORTCODE ) {
		//TODO: optionen in einem array sammeln und am ende je OUTPUT ausgeben

		?>

        <link rel="stylesheet" href="<?php echo esc_url(plugins_url( 'dist/accordion.css', ATKP_PLUGIN_FILE )) ?>"/>

        <ul id="my-accordion" class="my-accordion accordionjs">

            <!-- Section 1 -->
            <li class="acc_section displaytype_section">
                <div class="acc_head"><h3
                            id="atkp-displaytype-caption"><?php echo esc_html__( 'Select a display type', ATKP_PLUGIN_FILE ) ?></h3>
                </div>
                <div class="acc_content">
                    <label for="atkp-display-type">
	                    <?php echo esc_html__( 'Select a display type', ATKP_PLUGIN_PREFIX ) ?>
                    </label>
                    <select class="atkp-display-type" id="atkp-display-type">
                        <option value=""><?php echo esc_html__( 'Select an option', ATKP_PLUGIN_PREFIX ) ?></option>

						<?php
						$prds               = array();
						$prds['box']        = esc_html__( 'Product Boxes', ATKP_PLUGIN_PREFIX );
						$prds['field']      = esc_html__( 'Fields (Single product data)', ATKP_PLUGIN_PREFIX );
						$prds['link']       = esc_html__( 'Text Link', ATKP_PLUGIN_PREFIX );
						$prds['searchform'] = esc_html__( 'Search Form', ATKP_PLUGIN_PREFIX );
						//TODO: Dynamic filter

						$prds = apply_filters( 'atkp_modify_display_types', $prds );

						foreach ( $prds as $prd => $val ) {
							echo '<option value="' . esc_attr( $prd ) . '">' . esc_html( $val ) . '</option>';
						}
						?>
                    </select>
                </div>
            </li>

            <!-- Section 2 -->
            <li class="acc_section datasource_section">
                <div class="acc_head"><h3
                            id="atkp-datasource-caption"><?php echo esc_html__( 'Select a data source', ATKP_PLUGIN_FILE ) ?></h3>
                </div>
                <div class="acc_content">
                    <label for="atkp-display-type">
	                    <?php echo esc_html__( 'Select a data source', ATKP_PLUGIN_PREFIX ) ?>
                    </label>
                    <select class="atkp-display-type" id="atkp-datasource">
                        <option value=""><?php echo esc_html__( 'Select an option', ATKP_PLUGIN_PREFIX ) ?></option>

						<?php
						$prds                                                       = array();
						$prds[ esc_html__( 'Single Product', ATKP_PLUGIN_PREFIX ) ]         = [
							'product_search' => esc_html__( 'Search Existing Product', ATKP_PLUGIN_PREFIX ),
							'product_create' => esc_html__( 'Create Single Product', ATKP_PLUGIN_PREFIX )
						];
						$prds[ esc_html__( 'Bestseller List', ATKP_PLUGIN_PREFIX ) ]        = [
							'bestseller_search' => esc_html__( 'Search Existing Bestseller Lists', ATKP_PLUGIN_PREFIX ),
							'bestseller_create' => esc_html__( 'Create New Bestseller List', ATKP_PLUGIN_PREFIX )
						];
						$prds[ esc_html__( 'New Releases List', ATKP_PLUGIN_PREFIX ) ]      = [
							'new_search' => esc_html__( 'Search Existing Releases List', ATKP_PLUGIN_PREFIX ),
							'new_create' => esc_html__( 'Create New Releases List', ATKP_PLUGIN_PREFIX )
						];
						$prds[ esc_html__( 'Keyword List', ATKP_PLUGIN_PREFIX ) ]           = [
							'keyword_search' => esc_html__( 'Search Existing Keyword List', ATKP_PLUGIN_PREFIX ),
							'keyword_create' => esc_html__( 'Create New Keyword List', ATKP_PLUGIN_PREFIX )
						];
						$prds[ esc_html__( 'Dynamic Product Filter', ATKP_PLUGIN_PREFIX ) ] = [ 'productfilter' => esc_html__( 'Dynamic Product Filter', ATKP_PLUGIN_PREFIX ) ];

						/*
						$prds['product_search'] = __('Search Single Product', ATKP_PLUGIN_PREFIX),
						$prds['product_create'] = __('Create Single Product', ATKP_PLUGIN_PREFIX)];
						$prds['bestseller_search'] = __('Search Bestseller List', ATKP_PLUGIN_PREFIX);
						$prds['bestseller_create'] = __('Create Bestseller List', ATKP_PLUGIN_PREFIX);
						$prds['new_search'] = __('Search New Releases List', ATKP_PLUGIN_PREFIX);
						$prds['new_create'] = __('Create New Releases List', ATKP_PLUGIN_PREFIX);
						$prds['keyword_search'] = __('Search Keyword List', ATKP_PLUGIN_PREFIX);
						$prds['keyword_create'] = __('Create Keyword List', ATKP_PLUGIN_PREFIX);
						$prds['productfilter'] = __('Dynamic Product Filter', ATKP_PLUGIN_PREFIX);*/
						//TODO: Dynamic filter

						$prds = apply_filters( 'atkp_modify_source_types', $prds );

						foreach ( $prds as $name => $group ) {
							echo '<optgroup label="' . esc_attr($name) . '">';
							foreach ( $group as $prd => $val ) {
								echo '<option value="' . esc_attr( $prd ) . '">' . esc_html( $val ) . '</option>';
							}
							echo '</optgroup>';
						}
						?>
                    </select>


                </div>
            </li>

            <!-- Section 3 -->
            <li class="acc_section import_section">
                <div class="acc_head"><h3
                            id="atkp-import-caption"><?php echo esc_html__( 'Search or import', ATKP_PLUGIN_FILE ) ?></h3>
                </div>
                <div class="acc_content">SEARCH OR IMPORT WINDOW</div>
            </li>

            <!-- Section 3 -->
            <li class="acc_section display_section">
                <div class="acc_head"><h3
                            id="atkp-displayoption-caption"><?php echo esc_html__( 'Setup display options', ATKP_PLUGIN_FILE ) ?></h3>
                </div>
                <div class="acc_content"><p>Quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
                        consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu
                        fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia
                        deserunt mollit anim id est laborum. Fusce aliquet neque et accumsan fermentum. Aliquam lobortis
                        neque in nulla tempus, molestie fermentum purus euismod.</p></div>
            </li>

        </ul>


        <script src="<?php echo esc_js(esc_url(plugins_url( 'dist/accordion.js', ATKP_PLUGIN_FILE ))) ?>"></script>

        <script>
            jQuery(document).ready(function ($) {

                var akkordion = $("#my-accordion").accordionjs({closeOther: true});

                $('#atkp-display-type').change(function () {
                    var val = $(this).val();
                    var txt = $('#atkp-display-type option:selected').text();

                    switch (val) {
                        case "box":
                            break;
                        case "searchform":
                            break;
                        case "field":
                            break;
                        case "link":
                            break;
                    }

                    jQuery('#atkp-displaytype-caption').html('<?php echo esc_html__( 'Select a display type', ATKP_PLUGIN_FILE ) ?>: ' + txt);
                    openSection('datasource_section');


                });

                $('#atkp-datasource').change(function () {
                    var val = $(this).val();
                    var txt = $('#atkp-datasource option:selected').text();

                    var typetex = '';
                    switch (val) {
                        case "product":
                            typetex = 'product';
                            break;
                        case "bestseller":
                            typetex = 'list';
                            break;
                        case "new":
                            typetex = 'list';
                            break;
                        case "productfilter":
                            break;
                        case "keyword":
                            typetex = 'list';
                            break;

                    }
//atkp-create-option
                    jQuery('#atkp-datasource-caption').html('<?php echo esc_html__( 'Select a data source', ATKP_PLUGIN_FILE ) ?>: ' + txt);
                    $('#atkp-import-caption').html(txt);

                    openSection('import_section');
                });

                function openSection(name) {
                    var section = $('.' + name);

                    akkordion.openSection(section);
                    akkordion.closeOtherSections(section);
                }

            });


        </script>

        <style>
            .accordionjs {
                position: relative;
                margin: 0;
                padding: 0;
                list-style: none;
                margin-top: 10px;
                margin-bottom: 20px;
            }

            .accordionjs .acc_section {
                border: 1px solid #ccc;
                position: relative;
                z-index: 10;
                margin-top: -1px;
                overflow: hidden;
            }

            .accordionjs .acc_section .acc_head {
                position: relative;
                background: #fff;
                padding: 10px;
                display: block;
                cursor: pointer;
            }

            .accordionjs .acc_section .acc_head h3 {
                line-height: 1;
                margin: 5px 0;
                padding: 0 !important;
            }

            .accordionjs .acc_section .acc_content {
                padding: 10px;
            }

            .accordionjs .acc_section:first-of-type,
            .accordionjs .acc_section:first-of-type .acc_head {
                border-top-left-radius: 3px;
                border-top-right-radius: 3px;
            }

            .accordionjs .acc_section:last-of-type,
            .accordionjs .acc_section:last-of-type .acc_content {
                border-bottom-left-radius: 3px;
                border-bottom-right-radius: 3px;
            }

            .accordionjs .acc_section.acc_active > .acc_content {
                display: block;
            }

            .accordionjs .acc_section.acc_active > .acc_head {
                background: #F9F9F9;
                border-bottom: 1px solid #ccc;
            }

        </style>
		<?php
	}

public function generate_modal_header( $id ) {
	?>

    <!-- Modal -->
    <div id="<?php echo esc_attr('atkp-modal-' . $id) ?>" class="atkp-modal lity-hide">

        <div class="atkp-modal__header">
            <div class="atkp-modal__title"><?php echo esc_html__( 'Setup your product box', esc_html( ATKP_PLUGIN_PREFIX ) ); ?></div>
            <span class="atkp-modal__close" data-atkp-close-modal="true"><span
                        class="dashicons dashicons-no"></span></span>
        </div>

        <div class="atkp-modal__content">

			<?php
			}
			public function generate_modal_footer() {
			?>
        </div><!-- .atkp-modal__content -->
        <div class="atkp-modal__footer">
            <span class="atkp-brand-icon"><img style="max-height:30px"
                                               src="<?php echo esc_url(plugins_url( '/img/affiliate-toolkit-web.png', esc_html(ATKP_GUTENBERG_PLUGIN_FILE)) ) ?>"/></span>
            <!--<span class="button atkp-modal__button" data-atkp-close-modal="true"><?php echo esc_html__( 'Close', ATKP_PLUGIN_PREFIX ); ?></span>-->
        </div>
    </div><!-- .atkp-modal -->

	<?php
}
	public function register_subpage() {
		add_action( 'atkp_register_submenu', function ( $parentmenu ) {

			add_submenu_page(
				$parentmenu,
				esc_html__( 'Shortcode Generator', ATKP_PLUGIN_PREFIX ),
				esc_html__( 'Shortcode Generator', ATKP_PLUGIN_PREFIX ),
				'manage_options',
				ATKP_PLUGIN_PREFIX . '_affiliate_toolkit-shortcodegenerator',
				array( &$this, 'show_generator_backend_page' )
			);

		}, 16, 1 );
	}
}

?>