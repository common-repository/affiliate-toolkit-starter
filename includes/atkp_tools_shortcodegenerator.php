<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class atkp_tools_shortcodegenerator {
	/**
	 * Construct the plugin object
	 */
	public function __construct( $pluginbase ) {
		add_action( 'atkp_register_submenu', array( &$this, 'admin_menu' ), 16, 1 );
	}

	function admin_menu( $parentmenu ) {


		add_submenu_page(
			$parentmenu,
			__( 'Shortcode Generator', ATKP_PLUGIN_PREFIX ),
			__( 'Shortcode Generator', ATKP_PLUGIN_PREFIX ),
			'manage_options',
			ATKP_PLUGIN_PREFIX . '_affiliate_toolkit-shortcodegenerator',
			array( &$this, 'shortcodegenerator_configuration_page' )
		);
	}

	public function shortcodegenerator_configuration_page() {
		if ( ! is_user_logged_in() ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page', ATKP_PLUGIN_PREFIX ) );
		}

		$atkp_shortcode_generator = new atkp_shortcode_generator2( array() );

		?>
        <div>
            <div class="inner atkp-mfp-shown" id="codegenerator">

				<?php $atkp_shortcode_generator->shortcode_popup(); ?>

            </div>

        </div>


        <style>
            #codegenerator #atkp-generator-wrap {
                display: block !important;

            }

            #atkp-generator-insert {
                display: none;
            }

        </style>


		<?php
	}
}

?>