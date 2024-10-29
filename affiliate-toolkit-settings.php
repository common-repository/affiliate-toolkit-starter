<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class atkp_settings {
	public static $settings;

	/**
	 * Construct the plugin object
	 */
	public function __construct( $pluginbase ) {
		add_action( 'atkp_register_submenu', array( &$this, 'admin_menu' ), 20, 1 );
	}

	function admin_menu( $parentmenu ) {


		add_submenu_page(
			$parentmenu,
			__( 'Settings', ATKP_PLUGIN_PREFIX ),
			__( 'Settings', ATKP_PLUGIN_PREFIX ),
			'manage_options',
			ATKP_PLUGIN_PREFIX . '_affiliate_toolkit-plugin',
			array( &$this, 'toolkit_settings' )
		);

	}


	public function toolkit_settings() {
		if ( ! is_user_logged_in() ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page', ATKP_PLUGIN_PREFIX ) );
		}

		?>

        <h2 class="nav-tab-wrapper atkp-nav-tab">
			<?php
			$mytab = ATKPTools::get_get_parameter( 'tab', 'string' );

			if ( $mytab == '' ) {
				foreach ( atkp_settings::$settings as $key => $value ) {
					$mytab = $value[1];
					break;
				}
			}

			foreach ( atkp_settings::$settings as $key => $value ) {
				$tab_name = $value[1];
				$class    = ( $mytab == $tab_name ) ? ' nav-tab-active' : '';

				?> <a class="nav-tab<?php echo esc_attr($class) ?>"
                      href="<?php echo esc_url( admin_url() ) ?>admin.php?page=<?php echo esc_html__( ATKP_PLUGIN_PREFIX, ATKP_PLUGIN_PREFIX ) . '_affiliate_toolkit-plugin' ?>&tab=<?php echo esc_html__( $tab_name, ATKP_PLUGIN_PREFIX ) ?>"><?php echo esc_html__( $key, ATKP_PLUGIN_PREFIX ) ?></a><?php
			}
			?>

        </h2>


		<?php

		foreach ( atkp_settings::$settings as $key => $value ) {
			$tab_name = $value[1];

			if ( $mytab == $tab_name ) {
				call_user_func( $value );
				break;
			}
		}

		?>




		<?php

	}
}


?>