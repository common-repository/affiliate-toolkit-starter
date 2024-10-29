<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class atkp_compatibility {
	public static $modes;

	/**
	 * Construct the plugin object
	 */
	public function __construct( $pluginbase ) {
		add_action( 'atkp_register_submenu', array( &$this, 'admin_menu' ), 15, 1 );
	}

	function admin_menu( $parentmenu ) {

		add_submenu_page(
			$parentmenu,
			esc_html__( 'Compatibility', ATKP_PLUGIN_PREFIX ),
			esc_html__( 'Compatibility', ATKP_PLUGIN_PREFIX ),
			'manage_options',
			ATKP_PLUGIN_PREFIX . '_affiliate_toolkit-compatibility',
			array( &$this, 'toolkit_compatibility' )
		);

	}


	public function toolkit_compatibility() {
		if ( ! is_user_logged_in() ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page', ATKP_PLUGIN_PREFIX ) );
		}

		?>

        <h2 class="nav-tab-wrapper atkp-nav-tab">
			<?php
			$mytab = ATKPTools::get_get_parameter( 'tab', 'string' );

			if ( $mytab == '' ) {
				foreach ( atkp_compatibility::$modes as $key => $value ) {
					$mytab = $value[1];
					break;
				}
			}

			foreach ( atkp_compatibility::$modes as $key => $value ) {
				$tab_name = $value[1];
				$class    = ( $mytab == $tab_name ) ? ' nav-tab-active' : '';

				?> <a class="<?php echo esc_attr('nav-tab' . $class) ?>"
                      href="<?php echo esc_url( admin_url() . 'admin.php?page=' . ATKP_PLUGIN_PREFIX . '_affiliate_toolkit-compatibility' . '&tab=' . $tab_name ) ?>"><?php echo esc_html__( $key, ATKP_PLUGIN_PREFIX ) ?></a><?php
			}
			?>

        </h2>


		<?php

		foreach ( atkp_compatibility::$modes as $key => $value ) {
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