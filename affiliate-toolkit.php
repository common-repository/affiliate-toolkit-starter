<?php
/**
 * Plugin Name: affiliate-toolkit
 * Plugin URI: https://www.affiliate-toolkit.com
 * Description: A plugin for smart affiliates. This plugin provides you an interface to the best affiliate platforms.
 * Version: 3.6.7
 * Requires PHP:      7.4
 * Author: SERVIT Software Solutions
 * Author URI: https://servit.dev
 * Text Domain: affiliate-toolkit-starter
 * Domain Path: /lang
 * License: GPL2
 */

define( 'ATKP_UPDATE_VERSION', '3.6.7' );
define( 'ATKP_UPDATE_ITEM_ID', '7680' );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

define( 'ATKP_PLUGIN_PREFIX', 'ATKP' );
define( 'ATKP_PLUGIN_DIR', dirname( __FILE__ ) );
define( 'ATKP_PLUGIN_FILE', __FILE__ );

//registering the internal autoloader
require_once ATKP_PLUGIN_DIR . '/includes/atkp_autoloader.php';
new atkp_autoloader();
atkp_autoloader::$loader->register_classes();

add_action( 'plugins_loaded', 'my_affiliate_toolkit_lang' );
function my_affiliate_toolkit_lang() {
	//load_plugin_textdomain(ATKP_PLUGIN_PREFIX , false, dirname(plugin_basename(__FILE__)) .'/lang' );

	/** Set our unique textdomain string */
	$textdomain = ATKP_PLUGIN_PREFIX;

	/** The 'plugin_locale' filter is also used by default in load_plugin_textdomain() */
	$locale = apply_filters( 'plugin_locale', get_locale(), $textdomain );

	if ( ATKPTools::startsWith( $locale, 'de_' ) ) {
		$locale = 'de_DE';
	}

	/** Set filter for WordPress languages directory */
	$wp_lang_dir = WP_LANG_DIR . '/' . basename( dirname( __FILE__ ) ) . '/' . $textdomain . '-' . $locale . '.mo';
	/** Translations: First, look in WordPress' "languages" folder = custom & update-secure! */
	load_textdomain( $textdomain, $wp_lang_dir );

	/** Translations: Secondly, look in plugin's "lang" folder = default */
	$lang_dir = dirname( plugin_basename( __FILE__ ) ) . '/lang/';
	load_plugin_textdomain( $textdomain, false, $lang_dir );

	do_action( 'atkp_module_updater' );
}

function atkp_plugin_locale_callback( $locale, $domain ) {
	if ( $domain != ATKP_PLUGIN_PREFIX ) {
		return $locale;
	}

	if ( ATKPTools::startsWith( $locale, 'de_' ) ) {
		$locale = 'de_DE';
	}

	return $locale;
}

add_filter( 'plugin_locale', 'atkp_plugin_locale_callback', 10, 2 );

add_action( 'publish_to_trash', 'my_affiliate_toolkit_to_trash' );
add_action( 'draft_to_trash', 'my_affiliate_toolkit_to_trash' );
add_action( 'future_to_trash', 'my_affiliate_toolkit_to_trash' );
add_action( 'deleted_post', 'my_affiliate_toolkit_deleted', 10, 2 );


function my_affiliate_toolkit_deleted( $post_id, $post ) {

	if ( $post->post_type == 'atkp_product' ) {
		do_action( 'atkp_product_deleted', $post->ID );
	} else if ( $post->post_type == 'atkp_shop' ) {
		do_action( 'atkp_shop_deleted', $post->ID );
	} else if ( $post->post_type == 'atkp_list' ) {
		do_action( 'atkp_list_deleted', $post->ID );
	}
}

function my_affiliate_toolkit_to_trash( $post ) {

	if ( $post->post_type == 'atkp_product' ) {
		do_action( 'atkp_product_to_trash', $post->ID );
	} else if ( $post->post_type == 'atkp_shop' ) {
		do_action( 'atkp_shop_to_trash', $post->ID );
	} else if ( $post->post_type == 'atkp_list' ) {
		do_action( 'atkp_list_to_trash', $post->ID );
	}
}

require_once ATKP_PLUGIN_DIR . '/includes/atkp_basics.php';


add_action( 'atkp_initialize_widgets', 'my_affiliate_toolkit_initialize_widgets' );

function my_affiliate_toolkit_initialize_widgets() {

	new atkp_widget();
}


//** Plugin initialisieren **//

add_action( 'init', 'my_affiliate_toolkit_init' );

function my_affiliate_toolkit_init() {
	if ( version_compare( get_bloginfo( 'version' ), '4.0', '<' ) ) {
		wp_die( "You must update WordPress to use affiliate-toolkit!" );
	}


	if ( is_admin() ) {
		add_action( 'admin_menu', 'atkp_init_menu', 20 );

		$atkp_settings = new atkp_settings( array() );

		$tempsettings = array(
			__( 'General settings', ATKP_PLUGIN_PREFIX )  => array(
				new atkp_settings_toolkit( array() ),
				'toolkit_configuration_page'
			),
			__( 'Advanced settings', ATKP_PLUGIN_PREFIX ) => array(
				new atkp_settings_advanced( array() ),
				'advanced_configuration_page'
			),
			__( 'Display settings', ATKP_PLUGIN_PREFIX )  => array(
				new atkp_settings_display( array() ),
				'display_configuration_page'
			),
			__( 'Licenses', ATKP_PLUGIN_PREFIX )          => array(
				new atkp_settings_license( array() ),
				'license_configuration_page'
			)
		);


		$tempsettings = apply_filters( 'atkp_pages_settings', $tempsettings );


		$atkp_settings::$settings = $tempsettings;

		$compatibility = array();
		$compatibility = apply_filters( 'atkp_pages_compatibility', $compatibility );

		if ( count( $compatibility ) > 0 ) {
			$atkp_compatibility         = new atkp_compatibility( array() );
			$atkp_compatibility::$modes = $compatibility;
		}


		$temptools = array();


		$temptools = apply_filters( 'atkp_pages_tools', $temptools );

		$temptools[ __( 'Shop replacement', ATKP_PLUGIN_PREFIX ) ] = array(
			new atkp_tools_shopreplace( array() ),
			'shopreplace_configuration_page'
		);
		$temptools[ __( 'Debug', ATKP_PLUGIN_PREFIX ) ]            = array(
			new atkp_tools_debug( array() ),
			'debug_configuration_page'
		);
		$temptools[ __( 'Welcome', ATKP_PLUGIN_PREFIX ) ] = array(
			new atkp_tools_welcome( array() ),
			'welcome_page'
		);
		//$temptools[ __( 'Import template', ATKP_PLUGIN_PREFIX ) ] = array(
		//	new atkp_tools_import_template( array() ),
		//		'importtools_configuration_page'
		//);


		new atkp_tools_shortcodegenerator( array() );


		if ( count( $temptools ) > 0 ) {
			$atkp_tools         = new atkp_tools( array() );
			$atkp_tools::$tools = $temptools;
		}

		new atkp_posttypes_shop( array() );
		new atkp_posttypes_product( array() );
		new atkp_posttypes_list( array() );
		new atkp_posttypes_template( array() );

		do_action( 'atkp_register_cpt' );

		new atkp_shortcode_generator2( array() );
		//$g = new atkp_generator();
		//$g->register_subpage();

		add_action( 'admin_enqueue_scripts', 'my_affiliate_toolkit_admin_styles' );

		new atkp_bulkimport( array() );
		new atkp_extensions();
		new atkp_queue_view();
		new atkp_template_view();

	} else {

		new atkp_posttypes_product( array() );
		new atkp_posttypes_template( array() );


		add_action( 'wp_enqueue_scripts', 'my_affiliate_toolkit_styles' );

		//enable shortcodes at widget area
		//add_filter('widget_text', 'do_shortcode');

		if ( atkp_options::$loader->get_show_credits() ) {
			add_filter( 'the_content', 'my_affiliate_toolkit_credits', 20 );
		}
	}


	//shortcodes für diverse backend editoren immer erzeugen..

	new atkp_shortcodes_product( array() );
	new atkp_shortcodes_list( array() );
	new atkp_shortcodes_atkp( array() );

	new atkp_wp_cronjob( true);
	new atkp_queueservices();
	new atkp_output_handle();


}

$x = new atkp_wp_cronjob( false );
$x->register_cron_hooks();

register_activation_hook( ATKP_PLUGIN_FILE, 'my_affiliate_toolkit_activated');

function my_affiliate_toolkit_activated() {

	//if ((get_option('atkp_version_plugin', false)) === false) {
	update_option( 'atkp_version_plugin', '1.0' );
	update_option( 'atkp_redirect_to_welcome', true );
	//}

}


add_action( 'admin_init', 'my_affiliate_toolkit_welcome', 9999 );

function my_affiliate_toolkit_welcome() {
	if ( ! get_option( 'atkp_redirect_to_welcome', false ) ) {
		return;
	}

	delete_option( 'atkp_redirect_to_welcome' );

	wp_safe_redirect( admin_url( 'admin.php?page=ATKP_affiliate_toolkit-tools&tab=welcome_page' ) );
	exit;
}

add_action( 'plugins_loaded', 'my_atkp_loaded' );

function my_atkp_loaded() {
	do_action( 'atkp_initialize_extensions' );

}

function atkp_init_menu() {
	add_menu_page(
		__( 'affiliate-toolkit', ATKP_PLUGIN_PREFIX ),
		__( 'affiliate-toolkit', ATKP_PLUGIN_PREFIX ),
		'edit_posts',
		ATKP_PLUGIN_PREFIX . '_affiliate_toolkit-plugin',
		null,
		plugin_dir_url( __FILE__ ) . '/images/affiliate_toolkit_menu.png',
		30
	);

	do_action( 'atkp_register_submenu', ATKP_PLUGIN_PREFIX . '_affiliate_toolkit-plugin' );
}


new atkp_gutenberg_editor( array() );


new atkp_endpoints( array() );

function my_affiliate_toolkit_credits( $content ) {

	if ( is_singular() && in_the_loop() && is_main_query() ) {
		global $post;
		if ( is_a( $post, 'WP_Post' ) ) {
			if ( has_shortcode( $post->post_content, 'atkp' ) || has_shortcode( $post->post_content, 'atkp_list' ) || has_shortcode( $post->post_content, 'atkp_product' ) ) {
				$content .= '<div class="atkp-credits">' . ATKPTools::get_credits_link() . '</div>';
			}
		}


	}

	return $content;
}

function my_affiliate_toolkit_admin_styles( $hook ) {
	$fontawesome = false;
	$codemirror  = false;
	$colorpicker = false;

	if ( 'toplevel_page_ATKP_affiliate_toolkit-plugin' == $hook || 'affiliate-toolkit_page_ATKP_affiliate_toolkit-compatibility' == $hook || 'affiliate-toolkit_page_ATKP_affiliate_toolkit-tools' == $hook || 'atkp_product_page_atkp_bulkimport' == $hook || 'affiliate-toolkit_page_ATKP_affiliate_toolkit-shortcodegenerator' == $hook ) {
		wp_register_style( 'atkp-styles', plugins_url( '/css/admin-style.css', __FILE__ ) );
		wp_enqueue_style( 'atkp-styles' );

		if ( $hook == 'toplevel_page_ATKP_affiliate_toolkit-plugin' || 'affiliate-toolkit_page_ATKP_affiliate_toolkit-shortcodegenerator' == $hook ) {
			$fontawesome = true;
		}

		$colorpicker = true;
	} else if ( 'post.php' == $hook || 'post-new.php' == $hook ) {
		wp_register_style( 'atkp-styles', plugins_url( '/dist/style.css', __FILE__ ) );
		wp_enqueue_style( 'atkp-styles' );

		$fontawesome = true;

		global $post_type;

		if ( ATKP_TEMPLATE_POSTTYPE == $post_type ) {
			$codemirror  = true;
			$colorpicker = true;
		}
	}

	if ( $fontawesome ) {
		wp_register_style( 'atkp-fontawesome', plugins_url( '/lib/font-awesome/css/font-awesome.min.css', __FILE__ ) );
		wp_enqueue_style( 'atkp-fontawesome' );
	}

	if ( $codemirror ) {
		wp_register_style( 'atkp-codemirror', plugins_url( '/lib/codemirror/codemirror.css', __FILE__ ) );
		wp_enqueue_style( 'atkp-codemirror' );


		wp_register_script( 'atkp-codemirror-script', plugins_url( '/lib/codemirror/codemirror.js', __FILE__ ) );

		wp_register_script( 'atkp-codemirror-xml', plugins_url( '/lib/codemirror/mode/xml/xml.js', __FILE__ ) );
		wp_register_script( 'atkp-codemirror-html', plugins_url( '/lib/codemirror/mode/htmlmixed/htmlmixed.js', __FILE__ ) );
		wp_register_script( 'atkp-codemirror-css', plugins_url( '/lib/codemirror/mode/css/css.js', __FILE__ ) );
		wp_register_script( 'atkp-codemirror-javascript', plugins_url( '/lib/codemirror/mode/javascript/javascript.js', __FILE__ ) );


		wp_enqueue_script( 'atkp-codemirror-script' );
		wp_enqueue_script( 'atkp-codemirror-xml' );
		wp_enqueue_script( 'atkp-codemirror-html' );
		wp_enqueue_script( 'atkp-codemirror-css' );
		wp_enqueue_script( 'atkp-codemirror-javascript' );

		wp_register_script( 'atkp-codemirror-searchcursor', plugins_url( '/lib/codemirror/searchcursor.js', __FILE__ ) );
		wp_enqueue_script( 'atkp-codemirror-searchcursor' );

		wp_register_script( 'atkp-codemirror-dialog', plugins_url( '/lib/codemirror/dialog.js', __FILE__ ) );
		wp_enqueue_script( 'atkp-codemirror-dialog' );
		wp_register_script( 'atkp-codemirror-jump-to-line', plugins_url( '/lib/codemirror/jump-to-line.js', __FILE__ ) );
		wp_enqueue_script( 'atkp-codemirror-jump-to-line' );

		wp_register_script( 'atkp-codemirror-search', plugins_url( '/lib/codemirror/search.js', __FILE__ ) );
		wp_enqueue_script( 'atkp-codemirror-search' );


		wp_register_style( 'atkp-codemirror-dialogstyle', plugins_url( '/lib/codemirror/dialog.css', __FILE__ ) );
		wp_enqueue_style( 'atkp-codemirror-dialogstyle' );


		wp_register_script( 'atkp-codemirror-closetag', plugins_url( '/lib/codemirror/addon/edit/closetag.js', __FILE__ ) );
		wp_enqueue_script( 'atkp-codemirror-closetag' );
		wp_register_script( 'atkp-codemirror-xmlfold', plugins_url( '/lib/codemirror/addon/fold/xml-fold.js', __FILE__ ) );
		wp_enqueue_script( 'atkp-codemirror-xmlfold' );

		wp_register_style( 'atkp-codemirror-show-hint', plugins_url( '/lib/codemirror/addon/hint/show-hint.css', __FILE__ ) );
		wp_enqueue_style( 'atkp-codemirror-show-hint' );
		wp_register_script( 'atkp-codemirror-show-hint', plugins_url( '/lib/codemirror/addon/hint/show-hint.js', __FILE__ ) );
		wp_enqueue_script( 'atkp-codemirror-show-hint' );

		wp_register_script( 'atkp-codemirror-xml-hint', plugins_url( '/lib/codemirror/addon/hint/xml-hint.js', __FILE__ ) );
		wp_enqueue_script( 'atkp-codemirror-xml-hint' );
		wp_register_script( 'atkp-codemirror-html-hint', plugins_url( '/lib/codemirror/addon/hint/html-hint.js', __FILE__ ) );
		wp_enqueue_script( 'atkp-codemirror-html-hint' );
		wp_register_script( 'atkp-codemirror-css-hint', plugins_url( '/lib/codemirror/addon/hint/css-hint.js', __FILE__ ) );
		wp_enqueue_script( 'atkp-codemirror-css-hint' );
		wp_register_script( 'atkp-codemirror-markdown', plugins_url( '/lib/codemirror/mode/markdown.js', __FILE__ ) );
		wp_enqueue_script( 'atkp-codemirror-markdown' );



	}

	if ( $colorpicker ) {
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
	}

	$disable_select2 = ATKPTools::get_setting( ATKP_PLUGIN_PREFIX . '_disableselect2', false );

	if ( ! $disable_select2 ) {
		wp_register_style( 'atkp-select2-styles', plugins_url( '/lib/select2/css/select2atkp.min.css', __FILE__ ) );
		wp_enqueue_style( 'atkp-select2-styles' );

		wp_register_script( 'atkp-select2-scripts', plugins_url( '/lib/select2/js/select2atkp.min.js', __FILE__ ) );
		wp_enqueue_script( 'atkp-select2-scripts' );
	}
}

function my_affiliate_toolkit_styles() {

	if ( ! atkp_options::$loader->get_disablestyles() ) {
		//register plugin styles
		wp_register_style( 'atkp-styles', plugins_url( '/dist/style.css', __FILE__ ) );
		wp_enqueue_style( 'atkp-styles' );

		//register custom styles
		if ( atkp_options::$loader->get_css_inline() == atkp_css_type::CssFile || atkp_options::$loader->get_css_inline() == atkp_css_type::Inline ) {
			$custom_styleurl = ATKPTools::get_global_style_url();

			if ( $custom_styleurl != null ) {
				wp_register_style( 'atkp-styles-custom', $custom_styleurl );
				wp_enqueue_style( 'atkp-styles-custom' );
			}

		} else if ( atkp_options::$loader->get_css_inline() == atkp_css_type::InlineHead ) {
			$output     = new atkp_output();
			$custom_css = $output->get_css_output();

			wp_add_inline_style( 'atkp-styles', $custom_css );

			//wp_enqueue_style( 'atkp-styles-custom' );
		}
	}

	//wp_register_script( 'atkp-jquery', plugins_url( 'dist/jquery.min.js', __FILE__ ), array( 'jquery' ), '3.4.1', true );
	//wp_enqueue_script( 'atkp-jquery' );


	if ( ! atkp_options::$loader->get_disablejs() ) {
		wp_register_script( 'atkp-scripts', plugins_url( '/dist/script.js', __FILE__ ), array( 'jquery' ) );

		$custom_scripturl = ATKPTools::get_global_script_url();
		//var_dump($custom_scripturl);exit;

		if ( $custom_scripturl == null ) {
			ATKPTools::add_global_scripts( 'atkp-custom-scripts' );
		} else {
			wp_register_script( 'atkp-custom-scripts', $custom_scripturl, array( 'jquery' ) );
		}

		wp_enqueue_script( 'atkp-scripts' );
		wp_enqueue_script( 'atkp-custom-scripts' );
	}
}

function atkp_template_list_previewtemplates( $templates ) {
	unset( $templates['ajax_load'] );
	unset( $templates['moreoffers'] );
	unset( $templates['moreoffers2'] );
	unset( $templates['floatingpanel'] );
	unset( $templates['variationboxes'] );
	unset( $templates['popup'] );


	unset( $templates['comparebox'] );
	unset( $templates['compareproduct'] );
	unset( $templates['default_live'] );
	unset( $templates['simple_live'] );
	unset( $templates['searchbox'] );
	unset( $templates['searchform'] );
	unset( $templates['searchtext'] );
	unset( $templates['productcompare_form'] );
	unset( $templates['productcompare_form-widget'] );

	return $templates;
}

add_filter( 'atkp_template_preview_list', 'atkp_template_list_previewtemplates', 10 );


do_action( 'atkp_initialize_widgets' );


/**
 * @param bool $supported
 * @param atkp_shop $shop
 *
 * @return bool
 */
function atkp_shop_support_articlenumber_search_at( $supported, $shop ) {

	$hasarticlesearch = ATKPTools::has_articlenumbersearch( $shop->webservice );

	return $supported ? $supported : $hasarticlesearch;
}

add_filter( 'atkp_shop_support_articlenumber_search', 'atkp_shop_support_articlenumber_search_at', 10, 2 );


//add_action( 'admin_notices', 'atkp_migration_admin_notice' );
add_action( 'admin_notices', 'atkp_admin_discounts' );
//add_action( 'admin_notices', 'atkp_admin_display_settings' );
add_action( 'admin_notices', 'atkp_admin_license' );

function atkp_migration_admin_notice() {
	global $pagenow;

	$done = ATKPTools::get_setting( 'atkp_migration_done', 0 );

	if ( $done ) {
		return;
	}

	$count = wp_count_posts( ATKP_PRODUCT_POSTTYPE );

	if ( ! $count || ( $count->publish == 0 && $count->draft == 0 ) ) {
		ATKPTools::set_setting( 'atkp_migration_done', 1 );

		return;
	}

	ATKPTools::show_notification( sprintf( __( '<span style="font-weight:bold">affiliate-toolkit:</span> You must migrate your products to newest version (v2 to v3). Please <a href="%s">click here</a> to migrate now. Please create a backup before you migrate.', ATKP_PLUGIN_PREFIX ), admin_url( 'admin.php?page=ATKP_affiliate_toolkit-tools&tab=debug_configuration_page' ) ), 'notice', 'warning' );


}

function atkp_admin_display_settings() {
	$display_check_done = ATKPTools::get_setting( 'atkp_display_check_done', 0 );

	//if(is_admin()) {
	//	ATKPTools::show_notification( sprintf( __( '<span style="font-weight:bold">affiliate-toolkit:</span> Please uninstall this plugin and install affiliate-toolkit from the <a href="%s" target="_blank">WordPress directory</a>. You will receive further updates via WordPress.org.', ATKP_PLUGIN_PREFIX ), 'https://wordpress.org/plugins/affiliate-toolkit-starter/' ), 'notice', 'warning' );
	//}

	if ( $display_check_done ) {
		return;
	}

	if ( ! isset( $_GET['tab'] ) || $_GET['tab'] == 'display_configuration_page' ) {
		return;
	}

	ATKPTools::show_notification( sprintf( __( '<span style="font-weight:bold">affiliate-toolkit:</span> Please check your display settings. Please <a href="%s">click here</a> to go to your display settings page.', ATKP_PLUGIN_PREFIX ), admin_url( 'admin.php?page=ATKP_affiliate_toolkit-plugin&tab=display_configuration_page' ) ), 'notice', 'warning' );
}

function atkp_admin_license() {
	$status = ATKP_LicenseController::get_license_status();

	if ( isset( $_GET['tab'] ) && $_GET['tab'] == 'welcome_page' ) {
		return;
	}

	if ( $status != null ) {
		ATKPTools::show_notification( sprintf( __( '<span style="font-weight:bold">affiliate-toolkit: %s</span>', ATKP_PLUGIN_PREFIX ), $status ), 'notice', 'info' );
	}
}

function atkp_admin_discounts() {
	if ( atkp_options::$loader->get_disablediscounts() ) {
		return;
	}

	$discounts = ATKP_StoreController::get_product_discounts();
	if ( $discounts->active &&
	     ( ( isset( $_GET['page'] ) && ATKPTools::startsWith( $_GET['page'], 'ATKP' ) ) || ( isset( $_GET['post_type'] ) && ATKPTools::startsWith( $_GET['post_type'], 'atkp' ) ) ) ) {
		$aktion_bis = new DateTime( $discounts->aktion_bis );

		ATKPTools::show_notification( sprintf( __( '<span style="font-weight:bold">affiliate-toolkit - %s:</span> <a target="_blank" href="%s">%s</a> (Ends on %s)', ATKP_PLUGIN_PREFIX ), $discounts->teaser, esc_attr( $discounts->link_url ), $discounts->link_text, $aktion_bis->format( get_option( 'date_format' ) . ' H:i:s' ) ), 'notice', 'info' );
	}
}

function is_atkp_page() {
	$is_atkp = false;
	if ( isset( $_GET['post_type'] ) && substr( $_GET['post_type'], 0, 4 ) == 'atkp' ) {
		$is_atkp = true;
	}

	if ( isset( $_GET['page'] ) && substr( $_GET['page'], 0, 4 ) == 'ATKP' ) {
		$is_atkp = true;
	}

	return $is_atkp;
}

if ( is_atkp_page() ) {
	add_filter( 'admin_footer_text', 'atkp_admin_rateus', 1 );
}

function atkp_admin_rateus() {
	$text = sprintf(
		wp_kses(
			_x(
				'Please rate <strong>affiliate-toolkit</strong> %1$s on %2$sWordPress.org%3$s to help us spread the word. Thank you from the affiliate-toolkit team!',
				'%1$s represents 5 start symbols linked to wordpress.org review page, %2$s,%2$s represents one,close link',
				ATKP_PLUGIN_PREFIX
			),
			array(
				'a'      => array(
					'href'   => array(),
					'target' => array(),
					'rel'    => array(),
				),
				'strong' => array(),
			)
		),
		'<a href="https://wordpress.org/support/plugin/affiliate-toolkit-starter/reviews/?filter=5#new-post" target="_blank" rel="noopener noreferrer">' .
		'&#9733;&#9733;&#9733;&#9733;&#9733;</a>',
		'<a href="https://wordpress.org/support/plugin/affiliate-toolkit-starter/reviews/?filter=5#new-post" target="_blank" rel="noopener">',
		'</a>'
	);

	return $text;
}


// directory handle
$child_plugin_dir = ATKP_PLUGIN_DIR . '/child-plugins/';
if(file_exists($child_plugin_dir)) {
	$dir = dir( $child_plugin_dir );
	if ( ! ! $dir ) {
		while ( false !== ( $entry = $dir->read() ) ) {
			if ( $entry != '.' && $entry != '..' ) {
				if ( is_dir( $child_plugin_dir . '/' . $entry ) ) {
					$child_file_name = $child_plugin_dir . '/' . $entry . '/' . $entry . '.php';

					if ( is_file( $child_file_name ) ) {
						require_once( $child_file_name );
					}

				}
			}
		}
	}
}

define( 'ATKP_INIT', '1' );

?>