<?php
/**
 * Created by PhpStorm.
 * User: Christof
 * Date: 01.12.2018
 * Time: 11:35
 */

class ATKPSettings {
	//Plugin-Prefix: Affiliate Toolkit Plugin (atkp)


	public static $access_csv_intervall;

	public static $access_cache_duration;
	public static $access_mark_links;
	public static $access_show_disclaimer;
	public static $access_disclaimer_text;
	public static $add_to_cart;
	public static $open_window;

	public static $enable_ssl;

	public static $show_linkinfo;
	public static $linkinfo_template;

	public static $check_enabled;
	public static $notification_interval;
	public static $email_recipient;
	public static $short_title_length;

	public static $show_moreoffers;
	public static $moreoffers_template;

	public static $list_default_count;
	public static $feature_count;
	public static $description_length;
	public static $boxcontent;

	public static $boxstyle;
	public static $bestsellerribbon;
	public static $showprice;
	public static $showpricediscount;
	public static $showstarrating;
	public static $showrating;

	public static $jslink;
	public static $linktracking;
	public static $linkprime;

	public static $disablestyles;
	public static $hideerrormessages;

	public static $pricecomparisonsort;


	public static $affiliate_char;

	/**
	 * Returns current plugin version.
	 *
	 * @return string Plugin version
	 */
	public static function plugin_get_version() {

		$plugin_data    = get_plugin_data( ATKP_PLUGIN_FILE );
		$plugin_version = $plugin_data['Version'];

		return $plugin_version;
	}

	public static function load_settings() {

		ATKPSettings::$disablestyles     = get_option( ATKP_PLUGIN_PREFIX . '_disablestyles', 0 );
		ATKPSettings::$hideerrormessages = get_option( ATKP_PLUGIN_PREFIX . '_hideerrormessages', 1 );


		ATKPSettings::$linktracking = get_option( ATKP_PLUGIN_PREFIX . '_link_click_tracking', 0 );

		ATKPSettings::$access_cache_duration = get_option( ATKP_PLUGIN_PREFIX . '_cache_duration', 1440 );
		ATKPSettings::$access_mark_links     = get_option( ATKP_PLUGIN_PREFIX . '_mark_links', 1 );

		ATKPSettings::$access_show_disclaimer = get_option( ATKP_PLUGIN_PREFIX . '_show_disclaimer', 0 );
		ATKPSettings::$access_disclaimer_text = get_option( ATKP_PLUGIN_PREFIX . '_disclaimer_text' );

		ATKPSettings::$add_to_cart = get_option( ATKP_PLUGIN_PREFIX . '_add_to_cart', 0 );
		ATKPSettings::$open_window = get_option( ATKP_PLUGIN_PREFIX . '_open_window', 1 );

		ATKPSettings::$show_linkinfo     = get_option( ATKP_PLUGIN_PREFIX . '_show_linkinfo', 0 );
		ATKPSettings::$linkinfo_template = get_option( ATKP_PLUGIN_PREFIX . '_linkinfo_template' );


		ATKPSettings::$access_csv_intervall = get_option( ATKP_PLUGIN_PREFIX . '_access_csv_intervall', 1440 );

		ATKPSettings::$check_enabled         = get_option( ATKP_PLUGIN_PREFIX . '_check_enabled' );
		ATKPSettings::$notification_interval = get_option( ATKP_PLUGIN_PREFIX . '_notification_interval', 4320 );
		ATKPSettings::$email_recipient       = get_option( ATKP_PLUGIN_PREFIX . '_email_recipient' );

		ATKPSettings::$short_title_length = get_option( ATKP_PLUGIN_PREFIX . '_short_title_length', 0 );

		ATKPSettings::$show_moreoffers     = get_option( ATKP_PLUGIN_PREFIX . '_show_moreoffers', 0 );
		ATKPSettings::$moreoffers_template = get_option( ATKP_PLUGIN_PREFIX . '_moreoffers_template', '' );


		ATKPSettings::$list_default_count = get_option( ATKP_PLUGIN_PREFIX . '_list_default_count', 0 );
		ATKPSettings::$feature_count      = get_option( ATKP_PLUGIN_PREFIX . '_feature_count', 0 );
		ATKPSettings::$description_length = get_option( ATKP_PLUGIN_PREFIX . '_description_length', 0 );
		ATKPSettings::$boxcontent         = get_option( ATKP_PLUGIN_PREFIX . '_boxcontent', '' );

		ATKPSettings::$boxstyle         = get_option( ATKP_PLUGIN_PREFIX . '_boxstyle', 1 );
		ATKPSettings::$bestsellerribbon = 2;
		ATKPSettings::$showprice        = get_option( ATKP_PLUGIN_PREFIX . '_showprice', 1 );
		ATKPSettings::$linkprime        = get_option( ATKP_PLUGIN_PREFIX . '_linkprime', 0 );
		ATKPSettings::$jslink           = get_option( ATKP_PLUGIN_PREFIX . '_jslink', 0 );

		ATKPSettings::$pricecomparisonsort = get_option( ATKP_PLUGIN_PREFIX . '_pricecomparisonsort', 1 );


		ATKPSettings::$affiliate_char = get_option( ATKP_PLUGIN_PREFIX . '_affiliatechar', '*' );

		ATKPSettings::$showpricediscount = get_option( ATKP_PLUGIN_PREFIX . '_showpricediscount', 1 );
		ATKPSettings::$showstarrating    = get_option( ATKP_PLUGIN_PREFIX . '_showstarrating', 1 );
		ATKPSettings::$showrating        = get_option( ATKP_PLUGIN_PREFIX . '_showrating', 1 );

		$loglevel = get_option( ATKP_PLUGIN_PREFIX . '_loglevel', 'off' );

		ATKPLog::Init( ATKP_LOGFILE, $loglevel );

	}

	public static function atkp_version_compare( $ver1, $ver2, $operator = null ) {
		$p    = '#(\.0+)+($|-)#';
		$ver1 = preg_replace( $p, '', $ver1 );
		$ver2 = preg_replace( $p, '', $ver2 );

		return isset( $operator ) ?
			version_compare( $ver1, $ver2, $operator ) :
			version_compare( $ver1, $ver2 );
	}

}
