<?php
/**
 * Created by PhpStorm.
 * User: Christof
 * Date: 02.12.2018
 * Time: 16:46
 */

class atkp_udtaxonomy {
	public $data = array();

	function __construct() {
		$this->id            = '';
		$this->caption       = '';
		$this->name          = '';
		$this->captionplural = '';
		$this->showui        = true;
		$this->issystemfield = false;
		$this->isnewtax      = false;

		$this->ismanufacturer    = false;
		$this->isauthor          = false;
		$this->isbrand           = false;
		$this->isproductcategory = false;

	}

	public function get_fieldname() {
		$taxonomy  = $this;
		$fieldname = '';

		if ( ! $taxonomy->issystemfield ) {
			if ( $taxonomy->isnewtax ) {
				$fieldname = 'ct_' . $taxonomy->name;
			} else {
				$fieldname = 'customtaxonomy_' . $taxonomy->name;
			}
		} else {
			if ( $taxonomy->ismanufacturer ) {
				$fieldname = 'manufacturer';
			} else if ( $taxonomy->isauthor ) {
				$fieldname = 'author';
			} else if ( $taxonomy->isbrand ) {
				$fieldname = 'brand';
			} else if ( $taxonomy->isproductcategory ) {
				$fieldname = 'productcategory';
			} else {
				$fieldname = $taxonomy->name;
			}
		}

		return $fieldname;
	}

	public static function load_taxonomies() {

		$newfields = get_option( ATKP_PLUGIN_PREFIX . '_udt_product' );

		if ( ! isset( $newfields ) || $newfields == '' ) {
			$newfields = array();
		}


		//load fieldgroups with taxonomy flag and add taxonomy fields to array

		$groups = ATKPTools::get_fieldgroups_with_taxonomy();

		foreach ( $groups as $group ) {
			$fields = ATKPTools::get_post_setting( $group->ID, ATKP_FIELDGROUP_POSTTYPE . '_fields' );
			if ( $fields != null ) {
				foreach ( $fields as $field ) {
					if ( $field->type == 6 ) {
						//TODO: add tax
						$udf                = new atkp_udtaxonomy();
						$udf->id            = uniqid();
						$udf->caption       = $field->caption;
						$udf->captionplural = $field->caption;
						$udf->name          = $field->name;
						$udf->isnewtax      = true;

						array_push( $newfields, $udf );
					}
				}
			}
		}


		foreach ( $newfields as $newfield ) {
			$newfield->showui = true;
		}


		$newfields = array_reverse( $newfields );

		$category = get_option( ATKP_PLUGIN_PREFIX . '_product_category_taxonomy', strtolower( __( 'Productcategory', ATKP_PLUGIN_PREFIX ) ) );

		//remove system taxonomies

		$clearedfields = array();

		foreach ( $newfields as $newfield ) {
			if ( $newfield->name == $category ) {

			} else {
				array_push( $clearedfields, $newfield );
			}
		}

		$newfields = $clearedfields;

		$udf                    = new atkp_udtaxonomy();
		$udf->id                = uniqid();
		$udf->caption           = __( 'Product category', ATKP_PLUGIN_PREFIX );
		$udf->captionplural     = __( 'Product category', ATKP_PLUGIN_PREFIX );
		$udf->name              = $category;
		$udf->showui            = true;
		$udf->isproductgroup    = true;
		$udf->issystemfield     = true;
		$udf->isproductcategory = true;

		array_push( $newfields, $udf );


		return array_reverse( $newfields );
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