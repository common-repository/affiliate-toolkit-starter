<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class atkp_settings_advanced {
	/**
	 * Construct the plugin object
	 */
	public function __construct( $pluginbase ) {


	}


	public function advanced_configuration_page() {
		if ( ATKPTools::exists_post_parameter( 'saveadvanced' ) && check_admin_referer( 'save', 'save' ) ) {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'You do not have sufficient permissions to access this page', ATKP_PLUGIN_PREFIX ) );
			}

			//speichern der einstellungen


			//ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_duplicatecheck', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_duplicatecheck', 'int' ) );

			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_defaultproductstate', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_defaultproductstate', 'string' ) );

			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_product_imagetype', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_product_imagetype', 'int' ) );

			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_product_importimage', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_product_importimage', 'int' ) );
			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_additional_shortcode_button', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_additional_shortcode_button', 'bool' ) );

			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_pricecomparisonsort', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_pricecomparisonsort', 'int' ) );


			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_open_window', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_open_window', 'bool' ) );
			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_link_click_tracking', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_link_click_tracking', 'int' ) );
			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_jslink', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_jslink', 'bool' ) );

			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_priceasfallback', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_priceasfallback', 'bool' ) );


			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_productgroupascategory', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_productgroupascategory', 'bool' ) );
			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_productgroupdeleteoldentries', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_productgroupdeleteoldentries', 'bool' ) );
			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_productgroupsplitchar', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_productgroupsplitchar', 'string' ) );

			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_update_producttitle_when_changed', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_update_producttitle_when_changed', 'string' ) );


			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_disable_js', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_disable_js', 'bool' ) );
			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_disablestyles', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_disablestyles', 'bool' ) );

			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_disableselect2', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_disableselect2', 'bool' ) );
			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_disableselect2_widget', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_disableselect2_widget', 'bool' ) );

			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_disablediscounts', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_disablediscounts', 'bool' ) );


			//ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_ignoreoffernotfound', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_ignoreoffernotfound', 'bool' ) );
			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_disable_sponsored_attribute', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_disable_sponsored_attribute', 'bool' ) );




			$args = array(
				'public'   => true,
				'_builtin' => false
			);

			$post_types = get_post_types( $args, 'names', 'and' );

			$post_types_sel = array();

			foreach ( $post_types as $post_type ) {

				if ( ATKPTools::get_post_parameter( ATKP_SHOP_POSTTYPE . '_posttype_' . $post_type, 'bool' ) ) {
					array_push( $post_types_sel, $post_type );
				}
			}

			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_custom_posttypes', $post_types_sel );

			do_action( 'atkp_settings_advanced_savefields' );
		}

		?>
        <div class="atkp-content wrap">
            <div class="inner">
                <!-- <h2><?php echo esc_html__( 'Affiliate Toolkit - Advanced Settings', ATKP_PLUGIN_PREFIX ) ?></h2>      -->

                <form method="POST"
                      action="?page=<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_affiliate_toolkit-plugin&tab=advanced_configuration_page') ?>">

					<?php wp_nonce_field( "save", "save" ); ?>
                    <table class="form-table" style="width:100%">
                        <tr>
                            <th scope="row" style="background-color:#bde4ea; padding:7px" colspan="2">
	                            <?php echo esc_html__( 'Links & Offers', ATKP_PLUGIN_PREFIX ) ?>
                            </th>
                        </tr>

                        <tr>
                            <th scope="row">

                            </th>
                            <td>
                                <input type="checkbox"
                                       id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_disable_sponsored_attribute') ?>"
                                       name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_disable_sponsored_attribute') ?>"
                                       value="1" <?php echo checked( 1, atkp_options::$loader->get_disable_sponsored_attribute(), true ); ?>>
                                <label for="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_disable_sponsored_attribute') ?>">
	                                <?php echo esc_html__( 'Disable "sponsored" link attribute', ATKP_PLUGIN_PREFIX ) ?>
                                </label> <br/>

								<?php ATKPTools::display_helptext( 'The new sponsored attribute can be used to identify links on your site that were created as part of advertisements, sponsorships or other compensation agreements.' ) ?>

                            </td>
                        </tr>


                        <tr>
                            <th scope="row">
                                <label for="">
	                                <?php echo esc_html__( 'Main product selector', ATKP_PLUGIN_PREFIX ) ?>:
                                </label>
                            </th>
                            <td>
                                <select id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_pricecomparisonsort') ?>"
                                        name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_pricecomparisonsort') ?>"
                                        style="width:300px">
									<?php
									$selected = ATKPSettings::$pricecomparisonsort;

									echo '<option value="1" ' . ( $selected == 1 ? 'selected' : '' ) . ' >' . esc_html__( 'Price + Shipping cost', ATKP_PLUGIN_PREFIX ) . '</option>';
									echo '<option value="2" ' . ( $selected == 2 ? 'selected' : '' ) . '>' . esc_html__( 'Price', ATKP_PLUGIN_PREFIX ) . '</option>';
									echo '<option value="3" ' . ( $selected == '' || $selected == 3 ? 'selected' : '' ) . '>' . esc_html__( 'Main product and Price', ATKP_PLUGIN_PREFIX ) . '</option>';
									echo '<option value="4" ' . ( $selected == 4 ? 'selected' : '' ) . '>' . esc_html__( 'Main product and Price + Shipping cost', ATKP_PLUGIN_PREFIX ) . '</option>';
									?>
                                </select>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">

                            </th>
                            <td>
                                <input type="checkbox" id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_open_window') ?>"
                                       name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_open_window') ?>"
                                       value="1" <?php echo checked( 1, atkp_options::$loader->get_open_window(), true ); ?>>
                                <label for="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_open_window') ?>">
	                                <?php echo esc_html__( 'Open links in new window/tab', ATKP_PLUGIN_PREFIX ) ?>
                                </label>

                            </td>
                        </tr>

                        <tr>
                            <th scope="row">

                            </th>
                            <td>
                                <input type="checkbox" id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_jslink') ?>"
                                       name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_jslink') ?>"
                                       value="1" <?php echo checked( 1, atkp_options::$loader->get_openlinkswithjs(), true ); ?>>
                                <label for="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_jslink') ?>">
	                                <?php echo esc_html__( 'Open affiliate links with javascript (does not display the target link within the browser)', ATKP_PLUGIN_PREFIX ) ?>
                                </label>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="">
	                                <?php echo esc_html__( 'Click tracking', ATKP_PLUGIN_PREFIX ) ?>:
                                </label>
                            </th>
                            <td>
                                <select id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_link_click_tracking') ?>"
                                        name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_link_click_tracking') ?>"
                                        style="width:300px">
									<?php
									$selected = atkp_options::$loader->get_link_click_tracking();

									echo '<option value="" ' . ( $selected == '' || $selected == 0 ? 'selected' : '' ) . ' >' . esc_html__( 'No', ATKP_PLUGIN_PREFIX ) . '</option>';

									echo '<option value="1" ' . ( $selected == 1 ? 'selected' : '' ) . '>' . esc_html__( 'Google Universal Tracking (ga)', ATKP_PLUGIN_PREFIX ) . '</option>';
									echo '<option value="2" ' . ( $selected == 2 ? 'selected' : '' ) . '>' . esc_html__( 'Google Standard Tracking (_gaq)', ATKP_PLUGIN_PREFIX ) . '</option>';

									echo '<option value="3" ' . ( $selected == 3 ? 'selected' : '' ) . '>' . esc_html__( 'Google Tag Manager Tracking (gtag)', ATKP_PLUGIN_PREFIX ) . '</option>';
									echo '<option value="4" ' . ( $selected == 4 ? 'selected' : '' ) . '>' . esc_html__( 'Matomo (Piwik) Tracking (_paq)', ATKP_PLUGIN_PREFIX ) . '</option>';
									echo '<option value="5" ' . ( $selected == 5 ? 'selected' : '' ) . '>' . esc_html__( 'Umami Tracking (umami)', ATKP_PLUGIN_PREFIX ) . '</option>';

									?>
                                </select>

	                            <?php ATKPTools::display_helptext( 'It is required that the base script is embedded and loaded. The plugin only fires the event and does no integration.' ) ?>

                            </td>
                        </tr>

                        <tr>
                            <th scope="row">

                            </th>
                            <td>
                                <input type="checkbox" id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_priceasfallback') ?>"
                                       name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_priceasfallback') ?>"
                                       value="1" <?php echo checked( 1, atkp_options::$loader->get_priceasfallback(), true ); ?>>
                                <label for="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_priceasfallback') ?>">
	                                <?php echo esc_html__( 'Use prices as fallback in product. Do not overwrite the price.', ATKP_PLUGIN_PREFIX ) ?>
                                </label>

                            </td>
                        </tr>


                        <tr>
                            <td colspan="2">&nbsp;</td>
                        </tr>
                        <tr>
                            <th scope="row" style="background-color:#bde4ea; padding:7px" colspan="2">
	                            <?php echo esc_html__( 'Images', ATKP_PLUGIN_PREFIX ) ?>
                            </th>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="">
	                                <?php echo esc_html__( 'Post thumbnail', ATKP_PLUGIN_PREFIX ) ?>:
                                </label>
                            </th>
                            <td>
                                <select id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_product_importimage') ?>"
                                        name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_product_importimage') ?>"
                                        style="width:300px">
									<?php
									$selected = atkp_options::$loader->get_product_importimage();

									echo '<option value="0" ' . ( $selected == '' || $selected == 0 ? 'selected' : '' ) . ' >' . esc_html__( 'Do not import the image', ATKP_PLUGIN_PREFIX ) . '</option>';
									echo '<option value="1" ' . ( $selected == 1 ? 'selected' : '' ) . '>' . esc_html__( 'Import main image', ATKP_PLUGIN_PREFIX ) . '</option>';
									echo '<option value="3" ' . ( $selected == 3 ? 'selected' : '' ) . '>' . esc_html__( 'Use FIFU plugin for external image', ATKP_PLUGIN_PREFIX ) . '</option>';
									?>
                                </select>

								<?php ATKPTools::display_helptext( 'By default the plugin does not import affiliate images. If you dont want to import the image but display it you need to use the FIFU plugin.', 'https://de.wordpress.org/plugins/featured-image-from-url/', esc_html__( 'More about Featured Image from URL (FIFU)' ) ); ?>

                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="">
	                                <?php echo esc_html__( 'Image redirect', ATKP_PLUGIN_PREFIX ) ?>:
                                </label>
                            </th>
                            <td>
                                <select id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_product_imagetype') ?>"
                                        name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_product_imagetype') ?>"
                                        style="width:300px">
	                                <?php
	                                $selected = atkp_options::$loader->get_product_imagetype();

	                                echo '<option value="0" ' . ( $selected == '' || $selected == 0 ? 'selected' : '' ) . ' >' . esc_html__( 'Don´t redirect image', ATKP_PLUGIN_PREFIX ) . '</option>';

	                                echo '<option value="2" ' . ( $selected == 2 ? 'selected' : '' ) . '>' . esc_html__( 'Redirect image', ATKP_PLUGIN_PREFIX ) . '</option>';
	                                echo '<option value="3" ' . ( $selected == 3 ? 'selected' : '' ) . '>' . esc_html__( 'Redirect image and main image', ATKP_PLUGIN_PREFIX ) . '</option>';

	                                ?>
                                </select>

	                            <?php ATKPTools::display_helptext( 'If you want to be GDPR compliant you need to use a image proxy (it will stress your server). If you already import the post thumbnail you can also use the imported image and all other images will be redirected.' ) ?>

                            </td>
                        </tr>

                        <tr>
                            <th scope="row" style="background-color:#bde4ea; padding:7px" colspan="2">
	                            <?php echo esc_html__( 'Imports', ATKP_PLUGIN_PREFIX ) ?>
                            </th>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="">
	                                <?php echo esc_html__( 'Default import state', ATKP_PLUGIN_PREFIX ) ?>:
                                </label>
                            </th>
                            <td>
                                <select id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_defaultproductstate') ?>"
                                        name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_defaultproductstate') ?>"
                                        style="width:300px">
									<?php
									$selected = atkp_options::$loader->get_defaultproductstate();

									echo '<option value="draft" ' . ( $selected == '' || $selected == 'draft' ? 'selected' : '' ) . ' >' . esc_html__( 'Draft', ATKP_PLUGIN_PREFIX ) . '</option>';

									echo '<option value="publish" ' . ( $selected == 'publish' ? 'selected' : '' ) . '>' . esc_html__( 'Publish', ATKP_PLUGIN_PREFIX ) . '</option>';

									if ( atkp_options::$loader->get_woo_mode() != '' ) {
										echo '<option value="woo" ' . ( $selected == 'woo' ? 'selected' : '' ) . '>' . esc_html__( 'WooCommerce product', ATKP_PLUGIN_PREFIX ) . '</option>';
									}
									?>
                                </select>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">

                            </th>
                            <td>
                                <input type="checkbox" id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_productgroupascategory') ?>"
                                       name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_productgroupascategory') ?>"
                                       value="1" <?php echo checked( 1, atkp_options::$loader->get_productgroupascategory(), true ); ?>>
                                <label for="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_productgroupascategory') ?>">
	                                <?php echo esc_html__( 'Import field "Productgroup" as product category (taxonomy)', ATKP_PLUGIN_PREFIX ) ?>
                                </label>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">

                            </th>
                            <td>
                                <input type="checkbox"
                                       id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_productgroupdeleteoldentries') ?>"
                                       name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_productgroupdeleteoldentries') ?>"
                                       value="1" <?php echo checked( 1, atkp_options::$loader->get_productgroupdeleteoldentries(), true ); ?>>
                                <label for="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_productgroupdeleteoldentries') ?>">
	                                <?php echo esc_html__( 'Delete old product categories', ATKP_PLUGIN_PREFIX ) ?>
                                </label>

								<?php ATKPTools::display_helptext( 'If a category changes this option will delete the old one otherwise both categories will be displayed.' ) ?>

                            </td>
                        </tr>


                        <tr>
                            <th scope="row">
                                <label for="">
	                                <?php echo esc_html__( 'Split product category by character', ATKP_PLUGIN_PREFIX ) ?>
                                    :
                                </label>
                            </th>
                            <td>
                                <input type="text" id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_productgroupsplitchar') ?>"
                                       name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_productgroupsplitchar') ?>"
                                       value="<?php echo esc_attr( atkp_options::$loader->get_productgroupsplitchar() ); ?>">
                            </td>
                        </tr>


                        <tr>
                            <th scope="row">
                            </th>
                            <td>
                                <input type="checkbox"
                                       id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_update_producttitle_when_changed') ?>"
                                       name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_update_producttitle_when_changed') ?>"
                                       value="1" <?php echo checked( 1, atkp_options::$loader->get_update_producttitle_when_changed(), true ); ?>>

                                <label for="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_update_producttitle_when_changed') ?>">
	                                <?php echo esc_html__( 'Update product title and permalink when changed', ATKP_PLUGIN_PREFIX ) ?>

                                </label>
								<?php ATKPTools::display_helptext( 'By default a product title and URL will not be updated if the product behind changes. If you enable this option it will update the data regulary.' ) ?>

                            </td>
                        </tr>


						<?php
						do_action( 'atkp_settings_advanced_fields' );
						?>


                        <tr>
                            <td colspan="2">&nbsp;</td>
                        </tr>
                        <tr>
                            <th scope="row" style="background-color:#bde4ea; padding:7px" colspan="2">
	                            <?php echo esc_html__( 'Compatibility', ATKP_PLUGIN_PREFIX ) ?>
                            </th>
                        </tr>
                        <tr>
                            <th scope="row">

                            </th>
                            <td>
                                <input type="checkbox"
                                       id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_additional_shortcode_button') ?>"
                                       name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_additional_shortcode_button') ?>"
                                       value="1" <?php echo checked( 1, atkp_options::$loader->get_additional_shortcode_button(), true ); ?>>
                                <label for="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_additional_shortcode_button') ?>">
	                                <?php echo esc_html__( 'Show additional shortcode button', ATKP_PLUGIN_PREFIX ) ?>
                                </label>

                            </td>
                        </tr>
                        <tr>
                            <th scope="row">

                            </th>
                            <td>
                                <input type="checkbox" id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_disable_js') ?>"
                                       name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_disable_js') ?>"
                                       value="1" <?php echo checked( 1, atkp_options::$loader->get_disablejs(), true ); ?>>
                                <label for="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_disable_js') ?>">
	                                <?php echo esc_html__( 'Disable internal JavaScript library', ATKP_PLUGIN_PREFIX ) ?>
                                </label>
								<?php ATKPTools::display_helptext( 'This option disables the main JS library from the plugin. Please pay attention that some features are not working after disabling.' ) ?>

                            </td>
                        </tr>

                        <tr>
                            <th scope="row">

                            </th>
                            <td>
                                <input type="checkbox" id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_disablestyles') ?>"
                                       name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_disablestyles') ?>"
                                       value="1" <?php echo checked( 1, get_option( ATKP_PLUGIN_PREFIX . '_disablestyles', 0 ), true ); ?>>
                                <label for="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_disablestyles') ?>">
	                                <?php echo esc_html__( 'Disable all styles from the plugin', ATKP_PLUGIN_PREFIX ) ?>
                                </label>
								<?php ATKPTools::display_helptext( 'This option will prevent the plugin to output any style file. Also inline styles will not be displayed' ) ?>

                            </td>
                        </tr>

                        <tr>
                            <th scope="row">

                            </th>
                            <td>
                                <input type="checkbox" id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_disableselect2') ?>"
                                       name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_disableselect2') ?>"
                                       value="1" <?php echo checked( 1, atkp_options::$loader->get_disableselect2_backend(), true ); ?>>
                                <label for="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_disableselect2') ?>">
	                                <?php echo esc_html__( 'Disable select2 fields', ATKP_PLUGIN_PREFIX ) ?>
                                </label>
								<?php ATKPTools::display_helptext( 'If you have problems in the backend (controls are not opening or hiding) you can try to disable select2.' ) ?>

                            </td>
                        </tr>
                        <tr>
                            <th scope="row">

                            </th>
                            <td>
                                <input type="checkbox" id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_disableselect2_widget') ?>"
                                       name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_disableselect2_widget') ?>"
                                       value="1" <?php echo checked( 1, atkp_options::$loader->get_disableselect2_widget(), true ); ?>>
                                <label for="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_disableselect2_widget') ?>">
	                                <?php echo esc_html__( 'Disable select2 fields for widgets', ATKP_PLUGIN_PREFIX ) ?>
                                </label>
			                    <?php ATKPTools::display_helptext( 'If you have problems in the widgets area (controls are not opening or hiding) you can try to disable select2.' ) ?>

                            </td>
                        </tr>

                        <tr>
                            <th scope="row">

                            </th>
                            <td>
                                <input type="checkbox" id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_disablediscounts') ?>"
                                       name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_disablediscounts') ?>"
                                       value="1" <?php echo checked( 1, atkp_options::$loader->get_disablediscounts(), true ); ?>>
                                <label for="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_disablediscounts') ?>">
	                                <?php echo esc_html__( 'Disable discount notifications', ATKP_PLUGIN_PREFIX ) ?>
                                </label>
								<?php ATKPTools::display_helptext( 'If you don\'t want to see discounts in the backend you can disable it here..' ) ?>

                            </td>
                        </tr>


                        <tr>
                            <th scope="row">
                                <label for="">
	                                <?php echo esc_html__( 'Shortcode-Generator post-types', ATKP_PLUGIN_PREFIX ) ?>:
                                </label>
                            </th>
                            <td>
                                <div style="border:1px solid #ccc; width:600px; height: 250px; overflow-y: scroll;padding:5px">
									<?php

									$args = array(
										'public'   => true,
										'_builtin' => false
									);

									$post_types = get_post_types( $args, 'names', 'and' );

									$sel_post_types = atkp_options::$loader->get_custom_posttypes();

									foreach ( $post_types as $post_type ) {
										$found = false;
										if ( $sel_post_types != null && is_array( $sel_post_types ) ) {
											foreach ( $sel_post_types as $pp ) {
												if ( $pp == $post_type ) {
													$found = true;
													break;
												}
											}
										}


										?>

                                        <input type="checkbox"
                                               id="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_posttype_' . $post_type) ?>"
                                               name="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_posttype_' . $post_type) ?>"
                                               value="1" <?php echo checked( 1, $found, true ); ?>>
                                        <label for="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_posttype_' . $post_type) ?>">
	                                        <?php echo esc_html__( $post_type, ATKP_PLUGIN_PREFIX ); ?>
                                        </label><br/>

										<?php
									}

									?>
                                </div>
                            </td>
                        </tr>


                        <tr>
                            <th scope="row">
                            </th>
                            <td>
								<?php submit_button( '', 'primary', 'saveadvanced', false ); ?>
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
        </div> <?php
	}
}

?>