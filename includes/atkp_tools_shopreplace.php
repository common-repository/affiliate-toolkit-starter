<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class atkp_tools_shopreplace {
	/**
	 * Construct the plugin object
	 */
	public function __construct( $pluginbase ) {


	}


	public function shopreplace_configuration_page() {
		if ( ATKPTools::exists_post_parameter( 'replaceshops' ) && check_admin_referer( 'save', 'save' ) ) {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'You do not have sufficient permissions to access this page', ATKP_PLUGIN_PREFIX ) );
			}

			global $wpdb;

			//replace shopid in atkp_product

			$atkp_old_shop_id = ATKPTools::get_post_parameter( 'atkp_old_shop_id', 'int' );
			$atkp_new_shop_id = ATKPTools::get_post_parameter( 'atkp_new_shop_id', 'int' );

			$wpdb->query( $wpdb->prepare( "update {$wpdb->postmeta} set meta_value = %d where meta_key =%s and meta_value = %d", $atkp_new_shop_id, 'atkp_product_shopid', $atkp_old_shop_id ) );
			for ( $x = 2; $x < ( ATKP_FILTER_COUNT + 2 ); $x ++ ) {
				$wpdb->query( $wpdb->prepare( "update {$wpdb->postmeta} set meta_value = %d where meta_key =%s and meta_value = %d", $atkp_new_shop_id, 'atkp_product_shopid_' . $x, $atkp_old_shop_id ) );
			}

			//replace shopid in atkp_list

			$wpdb->query( $wpdb->prepare( "update {$wpdb->postmeta} set meta_value = %d where meta_key =%s and meta_value = %d", $atkp_new_shop_id, 'atkp_list_shopid', $atkp_old_shop_id ) );

			//replace shopid in products table

			$wpdb->query( $wpdb->prepare( "update {$wpdb->prefix}atkp_products set shop_id = %d where shop_id = %d", $atkp_new_shop_id, $atkp_old_shop_id ) );

			//replace shopid in lists table

			$wpdb->query( $wpdb->prepare( "update {$wpdb->prefix}atkp_lists set shop_id = %d where shop_id = %d", $atkp_new_shop_id, $atkp_old_shop_id ) );

			echo esc_html__( 'Shops are replaced.', ATKP_PLUGIN_PREFIX );

		} else {


			?>

            <div class="atkp-content wrap">
                <div class="inner">
                    <form method="POST"
                          action="?page=<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_affiliate_toolkit-tools&tab=shopreplace_configuration_page') ?>">
                        <!--_affiliate_toolkit-bestseller-->
						<?php wp_nonce_field( "save", "save" ); ?>
                        <table class="form-table" style="width:100%">
                            <tr>
                                <th scope="row" style="background-color:#bde4ea; padding:7px" colspan="2">
	                                <?php echo esc_html__( 'Replace old shop by new shop', ATKP_PLUGIN_PREFIX ) ?>
                                </th>
                            </tr>

                            <tr>
                                <th scope="row">
                                    <label for="atkp_old_shop_id">
	                                    <?php echo esc_html__( 'Old shop ID', ATKP_PLUGIN_PREFIX ) ?>:
                                    </label>
                                </th>
                                <td>
                                    <input type="number" required id="atkp_old_shop_id"
                                           name="atkp_old_shop_id" style="width:300px"
                                           value="<?php echo esc_attr(ATKPTools::get_post_parameter( 'atkp_old_shop_id', 'int' )); ?>">
                                </td>
                            </tr>

                            <tr>
                                <th scope="row">
                                    <label for="atkp_new_shop_id">
	                                    <?php echo esc_html__( 'New shop ID', ATKP_PLUGIN_PREFIX ) ?>:
                                    </label>
                                </th>
                                <td>
                                    <select id="atkp_new_shop_id"
                                            name="atkp_new_shop_id" style="width:300px">
										<?php

										$newshopid = ATKPTools::get_post_parameter( 'atkp_new_shop_id', 'int' );


										$shps = atkp_shop::get_list( $newshopid );

										foreach ( $shps as $shp ) {
											if ( $shp->selected == true ) {
												$sel = ' selected';
											} else {
												$sel = '';
											}

											echo '<option ' . ( $shp->type == atkp_shop_type::SUB_SHOPS ? 'disabled' : '' ) . ' value="' . esc_attr($shp->id) . '"' . esc_attr($sel) . ' > ' . esc_attr( $shp->title . ' (' . $shp->id . ')' ) . '</option>';


											foreach ( $shp->children as $child ) {
												if ( $child->selected == true ) {
													$sel = ' selected';
												} else {
													$sel = '';
												}

												echo '<option value="' . esc_attr($child->id) . '"' . esc_attr($sel) . ' >- ' . esc_attr( $child->title . ' (' . $child->id . ')' ) . '</option>';

											}


										}


										?>
                                    </select>
                                </td>
                            </tr>

                            <tr>
                                <td>&nbsp;</td>
                                <td>
									<?php submit_button( esc_html__( 'Replace shops', ATKP_PLUGIN_PREFIX ), 'primary', 'replaceshops', false ); ?>
                                </td>
                            </tr>

                        </table>
                    </form>
                </div>

            </div> <?php
		}
	}


}

?>