<?php
/**
 * Plugin Name: affiliate-toolkit - Amazon Partner Program
 * Plugin URI: https://www.affiliate-toolkit.com
 * Description: Adds the Amazon Partner Program to affiliate-toolkit
 * Version: 1.1.3
 * Author: Christof Servit
 * Author URI: https://www.servit.biz
 * Text Domain: ATKP
 * Domain Path: /lang
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

define( 'ATKP_AMAZON_ITEM_VERSION', '1.1.3' );
define( 'ATKP_AMAZON_ITEM_ID', '7630' );

define( 'ATKP_AMAZON_PLUGIN_DIR', dirname( __FILE__ ) );
define( 'ATKP_AMAZON_PLUGIN_FILE', __FILE__ );

add_action('atkp_initialize_extensions', function() {
	atkp_autoloader::$loader->add_class_external( 'simple_html_dom', ATKP_AMAZON_PLUGIN_DIR . '/lib/simple_html_dom.php' );
	atkp_autoloader::$loader->add_class_external( 'ATKP_Review_Crawler', ATKP_AMAZON_PLUGIN_DIR . '/includes/ATKP_Review_Crawler.php' );

	if ( function_exists( 'my_affiliate_toolkit_amazon_init' ) ) {

		add_action( 'admin_notices', 'atkp_amazon_admin_notice' );

		function atkp_amazon_admin_notice() {
			ATKPTools::show_notification( sprintf( __( '<span style="font-weight:bold">affiliate-toolkit - Amazon:</span> You are using affiliate-toolkit from the WordPress directory. Please uninstall the Amazon extension. It\'s already included in the main plugin.', ATKP_PLUGIN_PREFIX ), admin_url( 'admin.php?page=ATKP_affiliate_toolkit-tools&tab=2' ) ), 'notice', 'warning' );
		}

	} else {
		//** Plugin initialisieren **//

		add_action( 'init', 'my_affiliate_toolkit_amazon_init' );

		function my_affiliate_toolkit_amazon_init() {
			if ( ! class_exists( 'atkp_options' ) ) {
				//plugin is not activated
				return;
			}
			//TODO: Register License Key Module
			//TOOD: Register Modul


		}

		/**
		 * @param atkp_template_parameters $parameters
		 *
		 * @return void
		 */
		function atkp_settings_display_fields_amazon($parameters) {
			?>
            <tr>
                <th colspan="5" style="background-color:#bde4ea; padding:7px">
	                <?php esc_html__( 'Amazon', ATKP_PLUGIN_PREFIX ) ?>
                </th>
            </tr>

            <tr>
                <td  class="atkp-settings-group"></td>
                <td>
                    <input type="checkbox" id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_show_primelogo') ?>"
                           name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_show_primelogo') ?>"
                           class="atkp-template-option"
                           value="1" <?php echo checked( 1, $parameters->get_showprimelogo(), true ); ?>>
                    <label for="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_show_primelogo') ?>">
	                    <?php esc_html__( 'Show Prime logo', ATKP_PLUGIN_PREFIX ) ?>
                    </label>
                </td>
                <td>
                    <input type="checkbox" id="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_linkprime') ?>"
                           name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_linkprime') ?>"
                           class="atkp-template-option"
                           value="1" <?php echo checked( 1, $parameters->get_linkprime(), true ); ?>>
                    <label for="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_linkprime') ?>">
	                    <?php esc_html__( 'Link Prime logo', ATKP_PLUGIN_PREFIX ) ?>
                    </label>
                </td>
            </tr>


			<?php
		}

		add_action( 'atkp_settings_live_display_fields', 'atkp_settings_display_fields_amazon', 10, 1 );


		function atkp_settings_display_savefields_amazon() {

			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_show_primelogo', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_show_primelogo', 'bool' ) );
			ATKPTools::set_setting( ATKP_PLUGIN_PREFIX . '_linkprime', ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . '_linkprime', 'bool' ) );

		}

		add_action( 'atkp_settings_display_savefields', 'atkp_settings_display_savefields_amazon', 10 );

		// Our filter callback function
		function atkp_load_amazon_providers( $providers, $providerid ) {

			if ( $providerid == '1' || $providerid == null ) {
				require_once ATKP_AMAZON_PLUGIN_DIR . '/includes/atkp_shop_provider_amazon.php';
				$providers['1'] = new atkp_shop_provider_amazon();
			}

			return $providers;
		}

		add_filter( 'atkp_load_providers', 'atkp_load_amazon_providers', 10, 2 );

		/**
		 * @param bool $supported
		 * @param atkp_shop $shop
		 *
		 * @return bool
		 */
		function atkp_shop_amazon_support_articlenumber_search_callback( $supported, $shop ) {

			$hasarticlesearch = ATKPTools::has_articlenumbersearch( $shop->webservice );

			if ( $shop->webservice == 1 ) {
				$x = new atkp_shop_provider_amazon();
				$x->checklogon( $shop );
				if ( $x->sitetripemode == 2 ) {
					return false;
				} else {
					return true;
				}
			}

			return $supported ? $supported : $hasarticlesearch;
		}

		add_filter( 'atkp_shop_support_articlenumber_search', 'atkp_shop_amazon_support_articlenumber_search_callback', 10, 2 );

		/**
		 * @param bool $supported
		 * @param atkp_shop $shop
		 *
		 * @return bool
		 */
		function atkp_shop_amazon_support_isbn_search( $supported, $shop ) {

			if ( $shop->webservice == 1 ) {
				$x = new atkp_shop_provider_amazon();
				$x->checklogon( $shop );
				if ( $x->sitetripemode == 2 ) {
					return false;
				} else {
					return true;
				}
			}

			return $supported;
		}

		add_filter( 'atkp_shop_support_isbn_search', 'atkp_shop_amazon_support_isbn_search', 10, 2 );

		/**
		 * @param bool $supported
		 * @param atkp_shop $shop
		 *
		 * @return bool
		 */
		function atkp_shop_amazon_support_gtin_search( $supported, $shop ) {

			if ( $shop->webservice == 1 ) {
				$x = new atkp_shop_provider_amazon();
				$x->checklogon( $shop );
				if ( $x->sitetripemode == 2 ) {
					return false;
				}
			}

			return $supported;
		}

		add_filter( 'atkp_shop_support_gtin_search', 'atkp_shop_amazon_support_gtin_search', 10, 2 );

		/**
		 * @param bool $supported
		 * @param atkp_shop $shop
		 *
		 * @return bool
		 */
		function atkp_shop_amazon_support_ean_search( $supported, $shop ) {

			if ( $shop->webservice == 1 ) {
				$x = new atkp_shop_provider_amazon();
				$x->checklogon( $shop );
				if ( $x->sitetripemode == 2 ) {
					return false;
				} else {
					return true;
				}
			}

			return $supported;
		}

		add_filter( 'atkp_shop_support_ean_search', 'atkp_shop_amazon_support_ean_search', 10, 2 );
	}
});


/*
function atkp_get_amazon_modules( $modules ) {
	$modules[ ATKP_AMAZON_ITEM_ID ] = 'amazon';

	return $modules;
}

add_filter( 'atkp_get_modules', 'atkp_get_amazon_modules', 10 );

add_action( 'atkp_module_updater', 'atkp_module_updater_amazon' );

function atkp_module_updater_amazon() {

	$license = ATKP_LicenseController::get_module_license( 'amazon' );

	// setup the updater
	$edd_updater = new ATKP_SL_Plugin_Updater( ATKP_STORE_URL, ATKP_AMAZON_PLUGIN_FILE, array(
		'version' => ATKP_AMAZON_ITEM_VERSION,        // current version number
		'license' => $license,    // license key (used get_option above to retrieve from DB)
		'item_id' => ATKP_AMAZON_ITEM_ID,    // id of this plugin
		'author'  => 'Christof Servit',    // author of this plugin
		'url'     => home_url(),
		'beta'    => false, // set to true if you wish customers to receive update notifications of beta releases
	) );
}
*/

?>