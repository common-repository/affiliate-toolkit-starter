<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class atkp_product_image {
	public $data = array();

	function __construct() {
		$this->id             = '';
		$this->smallimageurl  = '';
		$this->mediumimageurl = '';
		$this->largeimageurl  = '';
	}

	public static function load_images( $productid ) {

		$thumbimagesold = ATKPTools::get_post_setting( $productid, ATKP_PRODUCT_POSTTYPE . '_thumbimagesurl', true );
		$imagesold      = ATKPTools::get_post_setting( $productid, ATKP_PRODUCT_POSTTYPE . '_imagesurl', true );

		$newimages = ATKPTools::get_post_setting( $productid, ATKP_PRODUCT_POSTTYPE . '_images' );

		if ( ! isset( $newimages ) || $newimages == '' ) {
			$newimages = array();

			$images = explode( "\n", $imagesold );

			$count = 0;
			foreach ( explode( "\n", $thumbimagesold ) as $thumbimage ) {
				$thumbimagetemp = str_replace( array( "\n", "\r" ), '', $thumbimage );

				if ( ! isset( $thumbimagetemp ) || empty( $thumbimagetemp ) ) {
					continue;
				}

				$udf                 = new atkp_product_image();
				$udf->id             = uniqid();
				$udf->smallimageurl  = $thumbimagetemp;
				$udf->mediumimageurl = $thumbimagetemp;
				if ( sizeof( $images ) > $count ) {
					$udf->largeimageurl = str_replace( array( "\n", "\r" ), '', $images[ $count ] );
				}

				array_push( $newimages, $udf );

				$count = $count + 1;
			}
		}


		return $newimages;
	}

	public function __get( $member ) {
		if ( isset( $this->data[ $member ] ) ) {
			return $this->data[ $member ];
		}
	}

	public function __set( $member, $value ) {
		// if (isset($this->data[$member])) {
		$this->data[ $member ] = $value;
		//}
	}
}


?>