<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class atkp_gutenberg_editor {
	/**
	 * Construct the plugin object
	 */
	public function __construct( $pluginbase ) {

		global $pagenow;

		if ( $pagenow != 'widgets.php' ) {
			add_action( 'enqueue_block_editor_assets', array( $this, 'block_editor_button' ) );
		}

	}


	public static function block_editor_button() {


		$supportedblocks = apply_filters(
			'atkp_supported_blocks',
			array(
				'core/paragraph',
				'core/shortcode',
				'core/freeform',
			)
		);

		wp_register_script( 'shortcodes-atkp-block-editor', plugins_url( 'js/block-editor.js', ATKP_PLUGIN_FILE ), array(
			'wp-element',
			'wp-editor',
			'wp-components'
		), '1.0', true );

		wp_enqueue_script( 'shortcodes-atkp-block-editor' );

		wp_localize_script( 'shortcodes-atkp-block-editor', 'ATKPSETT', array(
			'iconurl' => plugins_url( '/images/affiliate_toolkit_menu.png', ATKP_PLUGIN_FILE ),
			'insertShortcode' => __( 'AT shortcode', ATKP_PLUGIN_PREFIX ),
			'supportedBlocks' => $supportedblocks
		) );

		wp_localize_script(
			'shortcodes-atkp-block-editor',
			'ATKPBlockEditorSettings',
			array( 'supportedBlocks' => get_option( 'atkp_option_supported_blocks', array() ) )
		);
	}


}