<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class atkp_global_tools {

	function atkp_create_list($shopid, $title, $listtype, $searchterm, $department, $sortby,$loadmoreoffers) {
		$post_id = ATKPTools::create_list( $title, $shopid, $listtype, $searchterm, $department, $sortby, $loadmoreoffers );

		$shop = $shopid == '' ? null : atkp_shop::load( $shopid );

		$entry = new atkp_queue_entry();

		$entry->post_id      = $post_id;
		$entry->shop_id      = $shopid;
		$entry->post_type    = ATKP_LIST_POSTTYPE;
		$entry->status       = atkp_queue_entry_status::PREPARED;
		$entry->functionname = 'listupdate';

		$entries = apply_filters( 'atkp_queue_process_entries_listupdate', [ $entry ], $entry->shop_id );


		if ( $loadmoreoffers && $shopid != '' ) {
			ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_updatedon', '' );
		}


		$gif_data[] = array(
			'postid'   => $post_id,
			'title'    => get_the_title( $post_id ),
			'edit_url' => get_edit_post_link( $post_id ),
		);

		return $gif_data;
	}

	function atkp_import_product( $shopid, $asin, $asintype, $title, $status, $importurl, $brand = '', $mpn = '', $subshopid = '' ) {
		try {

			//if ( $shopid == '' ) {
			//	throw new Exception( 'shop required' );
			//}
			if ( $asin == '' ) {
				throw new Exception( 'asin required' );
			}

			//$post_id = apply_filters( 'atkp_post_exists', 0, $shopid, $asin, $asintype, $title, $brand, $mpn );
			$post_id = apply_filters( 'atkp_find_product', 0, $shopid, $asin, '', ATKPTools::clear_string( $title ), $brand, $mpn );

			$updategroup = null;

			if ( $post_id <= 0 ) {

				$defaultproductstate = get_option( ATKP_PLUGIN_PREFIX . '_defaultproductstate', 'draft' );

				if ( $status == null || $status == '' ) {
					$status = $defaultproductstate;
				}

				$post_id = ATKPTools::create_product( $title, $shopid, $asin, $status, $asintype, $subshopid );

				do_action( 'atkp_product_import_fields', $post_id);

				do_action( 'atkp_product_import_url', $post_id, $importurl );


				if ( $brand != '' ) {
					ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_brand', $brand );
				}
				if ( $mpn != '' ) {
					ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_mpn', $mpn );
				}

				//xx
				$shop = $shopid != '' ? atkp_shop::load( $shopid ) : null;

				if ( $shop != null ) {
					$entry = new atkp_queue_entry();

					$entry->post_id           = $post_id;
					$entry->shop_id           = $shop->id;
					$entry->post_type         = ATKP_PRODUCT_POSTTYPE;
					$entry->status            = atkp_queue_entry_status::PREPARED;
					$entry->functionname      = 'productupdate';
					$entry->functionparameter = strtolower( $asintype ) . ATKP_QUEUE_SEPARATOR . $asin;


					$entries = apply_filters( 'atkp_queue_process_entries_productupdate', [ $entry ], $shop->id );

					$entries2 = apply_filters( 'atkp_queue_process_entries_productfinish', $entries );

					ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_updatedon', '' );


					//do_action('atkp_export_products', [$post_id]);
				}

			}

			$gif_data[] = array(
				'postid'   => $post_id,
				'title'    => ATKPTools::get_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_title' ),
				'edit_url' => get_edit_post_link( $post_id ),
			);

			return $gif_data;

		} catch ( Exception $e ) {
			ATKPLog::LogError( $e->getMessage() );

			$gif_data[] = array(
				'error'   => 'An error has occurred.',
				'message' => $e->getMessage(),
			);

			return $gif_data;
		}
	}


}