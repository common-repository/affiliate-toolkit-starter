<?php
/**
 * Created by PhpStorm.
 * User: Christof
 * Date: 01.12.2018
 * Time: 11:08
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class atkp_autoloader {
	/* @var $loader array */
	private $classes;

	/* @var $loader null|atkp_autoloader */
	public static $loader = null;

	public function __construct() {
		if ( atkp_autoloader::$loader == null ) {
			$this->classes           = array();
			atkp_autoloader::$loader = $this;

			if ( ! spl_autoload_register( array( $this, 'load_class' ) ) ) {
				//autoload can't be registered
			}
		}
	}

	public function register_classes() {
		$this->add_class( 'atkp_basics', '/includes/atkp_basics.php' );
		$this->add_class( 'atkp_widget', '/includes/widgets/atkp_widget.php' );
		$this->add_class( 'atkp_settings', '/affiliate-toolkit-settings.php' );

		$this->add_class( 'ATKPSettings', '/includes/basics/ATKPSettings.php' );
		$this->add_class( 'ATKPLog', '/includes/basics/ATKPLog.php' );
		$this->add_class( 'ATKPTools', '/includes/basics/ATKPTools.php' );
		$this->add_class( 'atkp_options', '/includes/basics/atkp_options.php' );

		$this->add_class( 'atkp_settings', '/affiliate-toolkit-settings.php' );

		$this->add_class( 'atkp_settings_toolkit', '/includes/atkp_settings_toolkit.php' );
		$this->add_class( 'atkp_settings_advanced', '/includes/atkp_settings_advanced.php' );
		$this->add_class( 'atkp_settings_display', '/includes/atkp_settings_display.php' );
		$this->add_class( 'atkp_settings_license', '/includes/atkp_settings_license.php' );

		$this->add_class( 'ATKP_SL_Plugin_Updater', '/lib/ATKP_SL_Plugin_Updater.php' );

		$this->add_class( 'atkp_tools_debug', '/includes/atkp_tools_debug.php' );
		$this->add_class( 'atkp_tools_welcome', '/includes/atkp_tools_welcome.php' );
		$this->add_class( 'atkp_tools_shortcodegenerator', '/includes/atkp_tools_shortcodegenerator.php' );
		$this->add_class( 'atkp_generator', '/includes/atkp_generator.php' );


		$this->add_class( 'atkp_compatibility', '/affiliate-toolkit-compatibility.php' );
		$this->add_class( 'atkp_tools', '/affiliate-toolkit-tools.php' );

		$this->add_class( 'atkp_posttypes_shop', '/includes/atkp_posttypes_shop.php' );
		$this->add_class( 'atkp_posttypes_product', '/includes/atkp_posttypes_product.php' );
		$this->add_class( 'atkp_posttypes_list', '/includes/atkp_posttypes_list.php' );
		$this->add_class( 'atkp_posttypes_template', '/includes/atkp_posttypes_template.php' );
		$this->add_class( 'atkp_shortcode_generator2', '/includes/atkp_shortcode_generator2.php' );
		$this->add_class( 'atkp_bulkimport', '/affiliate-toolkit-bulkimport.php' );
		$this->add_class( 'atkp_shortcodes_product', '/includes/atkp_shortcodes_product.php' );
		$this->add_class( 'atkp_shortcodes_list', '/includes/atkp_shortcodes_list.php' );
		$this->add_class( 'atkp_shortcodes_atkp', '/includes/atkp_shortcodes_atkp.php' );

		$this->add_class( 'atkp_wp_cronjob', '/includes/atkp_wp_cronjob.php' );
		$this->add_class( 'atkp_endpoints', '/includes/atkp_endpoints.php' );

		$this->add_class( 'atkp_shop_provider_base', '/includes/shopproviders/atkp_shop_provider_base.php' );

		$this->add_class( 'atkp_control_helper', '/includes/helper/atkp_control_helper.php' );
		$this->add_class( 'atkp_cronjob_new', '/includes/atkp_cronjob_new.php' );
		$this->add_class( 'atkp_template_helper', '/includes/helper/atkp_template_helper.php' );

		$this->add_class( 'atkp_export_base', '/includes/exportproviders/atkp_export_base.php' );
		$this->add_class( 'atkp_list_resp', '/includes/shopproviders/atkp_list_resp.php' );
		$this->add_class( 'atkp_list_req', '/includes/shopproviders/atkp_list_req.php' );
		$this->add_class( 'atkp_list_request_type', '/includes/models/atkp_list_request_type.php' );
		$this->add_class( 'atkp_list_source_type', '/includes/models/atkp_list_source_type.php' );

		$this->add_class( 'atkp_search_resp', '/includes/shopproviders/atkp_search_resp.php' );
		$this->add_class( 'atkp_global_tools', '/includes/helper/atkp_global_tools.php' );
		$this->add_class( 'atkp_response', '/includes/shopproviders/atkp_response.php' );
		$this->add_class( 'atkp_response_item', '/includes/shopproviders/atkp_response_item.php' );
		$this->add_class( 'atkp_search_type', '/includes/models/atkp_search_type.php' );

		$this->add_class( 'atkp_listservice', '/includes/dataservices/atkp_listservice.php' );
		$this->add_class( 'atkp_productservice', '/includes/dataservices/atkp_productservice.php' );
		$this->add_class( 'atkp_product_offer', '/includes/atkp_product_offer.php' );
		$this->add_class( 'atkp_output', '/includes/atkp_output.php' );

		$this->add_class( 'atkp_template_comparevalue', '/includes/atkp_template_comparevalue.php' );
		$this->add_class( 'atkp_shortcode_generator2', '/includes/atkp_shortcode_generator2.php' );
		$this->add_class( 'atkp_shortener', '/tools/atkp_shortener.php' );
		$this->add_class( 'atkp_default_template_base', '/includes/atkp_default_template_base.php' );
		$this->add_class( 'atkp_template_comparegroup', '/includes/atkp_template_comparegroup.php' );

		$this->add_class( 'atkp_export_provider_base', '/includes/exportproviders/atkp_export_base.php' );


		$this->add_class( 'atkp_formatter', '/includes/helper/atkp_formatter.php' );

		$this->add_class( 'atkp_translator', '/includes/helper/atkp_translator.php' );

		$this->add_class( 'atkp_template_parameters', '/includes/helper/atkp_template_parameters.php' );

		$this->add_class( 'atkp_gutenberg_editor', '/includes/atkp_gutenberg_editor.php' );

		$this->add_class( 'ATKP_LicenseController', '/includes/controllers/ATKP_LicenseController.php' );
		$this->add_class( 'ATKP_StoreController', '/includes/controllers/ATKP_StoreController.php' );
		$this->add_class( 'atkp_extensions', '/affiliate-toolkit-extensions.php' );

		$this->add_class( 'atkp_listtable_helper', '/includes/database/atkp_listtable_helper.php' );
		$this->add_class( 'atkp_producttable_helper', '/includes/database/atkp_producttable_helper.php' );
		$this->add_class( 'atkp_queuetable_helper', '/includes/database/atkp_queuetable_helper.php' );

		$this->add_class( 'atkp_product', '/includes/models/atkp_product.php' );
		$this->add_class( 'atkp_product_image', '/includes/models/atkp_product_image.php' );
		$this->add_class( 'atkp_product_offer', '/includes/models/atkp_product_offer.php' );
		$this->add_class( 'atkp_list', '/includes/models/atkp_list.php' );
		$this->add_class( 'atkp_shop', '/includes/models/atkp_shop.php' );
		$this->add_class( 'atkp_shop_type', '/includes/models/atkp_shop_type.php' );
		$this->add_class( 'atkp_redirection_type', '/includes/models/atkp_redirection_type.php' );
		$this->add_class( 'atkp_link_type', '/includes/models/atkp_link_type.php' );
		$this->add_class( 'atkp_css_type', '/includes/models/atkp_css_type.php' );


		$this->add_class( 'atkp_queue', '/includes/models/atkp_queue.php' );
		$this->add_class( 'atkp_udfield', '/includes/models/atkp_udfield.php' );
		$this->add_class( 'atkp_udtaxonomy', '/includes/models/atkp_udtaxonomy.php' );
		$this->add_class( 'atkp_template', '/includes/models/atkp_template.php' );
		$this->add_class( 'atkp_queue_view', '/includes/pages/atkp_queue_view.php' );
		$this->add_class( 'atkp_queue_table', '/includes/pages/atkp_queue_table.php' );
		$this->add_class( 'atkp_queue_entry_table', '/includes/pages/atkp_queue_entry_table.php' );
		$this->add_class( 'atkp_queue_entry', '/includes/models/atkp_queue.php' );
		$this->add_class( 'atkp_queue_status', '/includes/models/atkp_queue.php' );
		$this->add_class( 'atkp_template_view', '/includes/pages/atkp_template_view.php' );
		$this->add_class( 'atkp_template_table', '/includes/pages/atkp_template_table.php' );

		$this->add_class( 'atkp_product_collection', '/includes/models/atkp_product_collection.php' );
		$this->add_class( 'atkp_queueservices', '/includes/dataservices/atkp_queueservices.php' );

		$this->add_class( 'atkp_filter_helper', '/includes/helper/atkp_filter_helper.php' );
		$this->add_class( 'atkp_queue_entry_status', '/includes/models/atkp_queue.php' );

		$this->add_class( 'atkp_tools_shopreplace', '/includes/atkp_tools_shopreplace.php' );

		$this->add_class( 'atkp_timeouter', '/includes/helper/atkp_timeouter.php' );
		$this->add_class( 'atkp_tools_import_template', '/includes/atkp_tools_import_template.php' );

		$this->add_class( 'atkp_output_handle', '/includes/basics/atkp_output_handle.php' );

	}


	public function add_class_external( $class_name, $file ) {
		$this->classes[ $class_name ] = $file;
	}

	public function add_class( $class_name, $file ) {
		$this->classes[ $class_name ] = ATKP_PLUGIN_DIR . $file;
	}

	public function load_class( $class_name ) {
		if ( $class_name == '' || ! in_array( substr( $class_name, 0, 4 ), array( 'atkp', 'ATKP' ) ) ) {
			return;
		}

		if ( ! isset( $this->classes[ $class_name ] ) ) {
			return;
			//throw new Exception( "class not found: " . $class_name );
		}

		$file = $this->classes[ $class_name ];

		if ( file_exists( $file ) ) {
			require_once( $file );
		}
	}
}