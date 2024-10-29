<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class atkp_tools {
	public static $tools;

	/**
	 * Construct the plugin object
	 */
	public function __construct( $pluginbase ) {
		add_action( 'atkp_register_submenu', array( &$this, 'admin_menu' ), 18, 1 );
	}

	function admin_menu( $parentmenu ) {

		add_submenu_page(
			$parentmenu,
			esc_html__( 'Tools', ATKP_PLUGIN_PREFIX ),
			esc_html__( 'Tools', ATKP_PLUGIN_PREFIX ),
			'manage_options',
			ATKP_PLUGIN_PREFIX . '_affiliate_toolkit-tools',
			array( &$this, 'toolkit_tools' )
		);

	}


	public function toolkit_tools() {
		if ( ! is_user_logged_in() ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page', ATKP_PLUGIN_PREFIX ) );
		}
		$mytab = ATKPTools::get_get_parameter( 'tab', 'string' );
		if ( $mytab != 'welcome_page' ) {
		?>

        <h2 class="nav-tab-wrapper  atkp-nav-tab">
			<?php


			if ( $mytab == '' ) {
				foreach ( atkp_tools::$tools as $key => $value ) {
					$mytab = $value[1];
					break;
				}
			}


			foreach ( atkp_tools::$tools as $key => $value ) {
				$tab_name = $value[1];
				$class    = ( $mytab == $tab_name ) ? ' nav-tab-active' : '';


				?> <a class="<?php echo esc_attr('nav-tab' . $class) ?>"
                      href="<?php echo esc_url( admin_url() . 'admin.php?page=' . ATKP_PLUGIN_PREFIX . '_affiliate_toolkit-tools' . '&tab=' . $tab_name ) ?>"><?php echo esc_html__( $key, ATKP_PLUGIN_PREFIX ) ?></a><?php
			}


			?>

        </h2>


		<?php
		}

		$current = 1;
		foreach ( atkp_tools::$tools as $key => $value ) {
			$tab_name = $value[1];

			if ( $mytab == $tab_name ) {
				call_user_func( $value );
				break;
			}
			$current = $current + 1;
		}

		?>


		<?php

	}
}


?>