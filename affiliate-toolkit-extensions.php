<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class atkp_extensions {

	/**
	 * Construct the plugin object
	 */
	public function __construct() {
		add_action( 'atkp_register_submenu', array( &$this, 'admin_menu' ), 25, 1 );
	}

	function admin_menu( $parentmenu ) {

		add_submenu_page(
			$parentmenu,
			esc_html__( 'Extensions', ATKP_PLUGIN_PREFIX ),
			esc_html__( 'Extensions', ATKP_PLUGIN_PREFIX ),
			'manage_options',
			ATKP_PLUGIN_PREFIX . '_affiliate_toolkit-Extensions',
			array( &$this, 'toolkit_extensions' )
		);

	}


	public function toolkit_extensions() {
		if ( ! is_user_logged_in() ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page', ATKP_PLUGIN_PREFIX ) );
		}

		$products = ATKP_StoreController::get_products_feed();
		?>
        <div class="wrap">
            <h1 class="wp-heading-inline">
	            <?php echo esc_html__( 'Apps and Integrations for affiliate-toolkit', ATKP_PLUGIN_PREFIX ); ?>

                <a href="<?php echo esc_urL( ( ATKPTools::is_lang_de() ? 'https://www.affiliate-toolkit.com/de/erweiterungen/' : 'https://www.affiliate-toolkit.com/extensions/' ) . '?utm_medium=extension-page&amp;utm_content=AllExtensions&amp;utm_source=WordPress&amp;utm_campaign=starterpass' ) ?>"
                   class="button-primary" style="vertical-align: top;"
                   target="_blank"><?php echo esc_html__( 'View all extensions', ATKP_PLUGIN_PREFIX ) ?></a>

                <a href="<?php echo esc_urL( ( ATKPTools::is_lang_de() ? 'https://www.affiliate-toolkit.com/de/preise/' : 'https://www.affiliate-toolkit.com/pricing/' ) . '?utm_medium=extension-page&amp;utm_content=AllPackages&amp;utm_source=WordPress&amp;utm_campaign=starterpass' ) ?>"
                   class="button-secondary"
                   target="_blank"><?php echo esc_html__( 'View all packages', ATKP_PLUGIN_PREFIX ) ?></a>
            </h1>

            <div id="atkp-add-ons">

				<?php
				if ( $products == null ) {
					echo '<div class="error"><p>' . esc_html__( 'There was an error retrieving the extensions list from the server. Please try again later.', ATKP_PLUGIN_PREFIX ) . '</div>';
				} else {

					foreach ( $products->products as $product ) {

						if ( ATKPTools::str_contains( $product->info->title, 'Pass' ) || $product->info->title == 'affiliate-toolkit' || $product->licensing->enabled != true ) {
							continue;
						}

						?>

                        <div class="atkp-extension"><h3
                                    class="atkp-extension-title"><?php echo esc_attr( $product->info->title ) ?></h3><a
                                    href="<?php echo esc_url($product->info->link) ?>"
                                    title="<?php echo esc_attr( $product->info->title ) ?>"><?php if ( $product->info->thumbnail != '' )  { ?>
                                <img width="540" height="270"
                                     src="<?php echo esc_attr($product->info->thumbnail) ?>"
                                     class="attachment-download-grid-thumb size-download-grid-thumb wp-post-image"
                                     alt="<?php echo esc_attr( $product->info->title ) ?> logo"
                                     title="<?php echo esc_attr( $product->info->title ) ?>"></a><?php } ?>
                            <p></p>
                            <p><?php echo esc_html__( $product->info->excerpt, ATKP_PLUGIN_PREFIX ) ?><?php ?></p>
                            <div>
                                <div>
									<?php echo sprintf( esc_html__( 'Version: %s - %s', ATKP_PLUGIN_PREFIX ), esc_html($product->licensing->version), ( isset( $product->pricing->amount ) && $product->pricing->amount == '0.00' ? 'Free' : ( sprintf( esc_html__( 'Price starts at: %s€', ATKP_PLUGIN_PREFIX ), esc_html(str_replace( '.', ',', ( ( ! isset( $product->pricing->amount ) ) ? esc_html($product->pricing->singlesite) : esc_html($product->pricing->amount) ) )) ) ) ) ) ?>
                                </div>

                                <div>
                                    <a href="<?php echo esc_url(( $product->info->permalink ) . '?utm_medium=extension-page&amp;utm_content=GetExtension&amp;utm_source=WordPress&amp;utm_campaign=starterpass') ?>"
                                       target="_blank"
                                       title="<?php echo esc_attr( $product->info->title ) ?>"
                                       class="button-secondary"><?php echo esc_html__( 'Get this Extension', ATKP_PLUGIN_PREFIX ) ?></a>
                                </div>

                            </div>


                        </div>
					<?php }
				} ?>
            </div>
        </div>
        <style>

            #atkp-add-ons .atkp-extension {
                background: #fff;
                border: 1px solid #ccc;
                float: left;
                padding: 14px;
                position: relative;
                margin: 20px 15px 15px 0;
                width: 320px;
                height: 350px
            }

            #atkp-add-ons .atkp-extension h3 {
                font-size: 13px;
                margin: 0 0 8px

            }

            #atkp-add-ons .atkp-extension .button-secondary {
                position: absolute;
                bottom: 14px;
                left: 14px
            }

            #atkp-add-ons .atkp-extension .wp-post-image {
                width: 100%;
                height: auto;
                vertical-align: bottom
            }
        </style>
		<?php

	}
}


?>