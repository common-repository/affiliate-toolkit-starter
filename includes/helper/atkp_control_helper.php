<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class atkp_control_helper {


	public function read_control_value( $newfield, $fieldname ) {

		$fieldvalue = null;

		switch ( $newfield->type ) {
			case 0:
			case 1:
				//Text
				$type = 'text';
				switch ( $newfield->format ) {
					default:
					case 'text':
						$fieldvalue = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_' . $fieldname, 'string' );
						break;
					case 'stars':
					case 'number':
						$fieldvalue = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_' . $fieldname, 'double' );
						break;
					case 'url':
						$fieldvalue = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_' . $fieldname, 'url' );
						break;
				}


				break;
			case 2:
				//multiline
				$fieldvalue = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_' . $fieldname, 'multistring' );
				break;
			case 3:
				//dropdown
				$fieldvalue = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_' . $fieldname, 'string' );
				break;
			case 4:
				//yesno
				$fieldvalue = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_' . $fieldname, 'string' );

				switch ( $fieldvalue ) {
					default:
					case '':
						$fieldvalue = null;
						break;
					case '0':
						$fieldvalue = '0';
						break;
					case '1';
						$fieldvalue = '1';
						break;

				}
				break;
			case 5:
				//html
				$fieldvalue = ATKPTools::get_post_parameter( ATKP_PRODUCT_POSTTYPE . '_' . $fieldname, 'html' );
				break;
			default:
				throw new exception( esc_html__( 'unknown newfield->type: ' . $newfield->type, ATKP_PLUGIN_PREFIX ) );
		}

		return $fieldvalue;
	}

	public function create_control( $newfield, $controlname, $value, $isnewfield = false, $generaterandom = true ) {
		$result = '';

		if ( $newfield != null ) {
			$newfield->isnewfield = $isnewfield;
		}

		$value = apply_filters( 'atkp_backend_control_value', $value, $newfield );

		switch ( $newfield->type ) {
			default:
			case 1:
				//Text
				$type       = 'text';
				$attributes = '';
				switch ( $newfield->format ) {
					case 'text':
						$type = 'text';
						break;
					case 'number':
						$type       = 'number';
						$attributes = ' step="0.01" ';
						break;
					case 'url':
						$type = 'url';
						break;
					case 'email':
						$type = 'email';
						break;
					case 'date':
						$type = 'date';
						break;
					case 'stars':
						$type       = 'number';
						$attributes = ' step="0.5" min="0" max="5" ';
						break;
				}


				$result = '<input style="width:100%" type="' . esc_attr( $type ) . '" ' . $attributes . ' id="' . esc_attr( $controlname . ( $generaterandom ? random_int( 1, 9999 ) : '' ) ) . '" name="' . esc_attr( $controlname ) . '" value="' . esc_attr( $value ) . '"> ';

				break;
			case 2:
				//multiline

				$result = '<textarea style="width:100%;height:100px" id="' . esc_attr( $controlname . ( $generaterandom ? random_int( 1, 9999 ) : '' ) ) . '" name="' . esc_attr( $controlname ) . '">' . esc_textarea( $value ) . '</textarea>';

				break;
			case 3:
				//dropdown

				if ( $isnewfield ) {
					$values = explode( "\n", $newfield->values );
				} else {
					$values = explode( ';', $newfield->format );
				}

				$result = '<select id="' . esc_attr( $controlname . ( $generaterandom ? random_int( 1, 9999 ) : '' ) ) . '" name="' . esc_attr( $controlname ) . '" style="width:300px">  ';

				$result .= '<option value="" ' . ( $value == '' ? 'selected' : '' ) . '>' . __( 'None', ATKP_PLUGIN_PREFIX ) . '</option>';

				foreach ( $values as $value2 ) {
					$value2 = trim( $value2 );
					if ( $value2 != '' ) {
						$result .= '<option value="' . $value2 . '" ' . ( $value == $value2 ? 'selected' : '' ) . '>' . esc_attr( $value2 ) . '</option>';
					}
				}

				$result .= '</select>';

				break;
			case 4:
				//yesno

				$result .= '<select id="' . esc_attr( $controlname . ( $generaterandom ? random_int( 1, 9999 ) : '' ) ) . '" name="' . esc_attr( $controlname ) . '" style="width:300px"> ';

				$result .= '<option value="" ' . ( $value == '' || $value == null ? 'selected' : '' ) . '>' . __( 'None', ATKP_PLUGIN_PREFIX ) . '</option>';
				$result .= '<option value="1" ' . ( $value == '1' ? 'selected' : '' ) . '>' . __( 'Yes', ATKP_PLUGIN_PREFIX ) . '</option>';
				$result .= '<option value="0" ' . ( $value == '0' ? 'selected' : '' ) . '>' . __( 'No', ATKP_PLUGIN_PREFIX ) . '</option>';

				$result .= '</select>';

				break;
			case 5:
				//html

				ob_start();

				wp_editor( $value, $controlname.($generaterandom ? random_int(1, 9999) : ''), array(
					'media_buttons' => false,
					'textarea_name' => $controlname,
					'textarea_rows' => 5,
				) );

				$result = ob_get_contents();

				ob_end_clean();
				break;

		}
		echo ($result);
	}

	public function get_minmaxvalue( $newfield, $order = 'ASC' ) {
		$minvalue = 0;


		global $wpdb;
		if ( $newfield->name == 'price' ) {
			$results = $wpdb->get_results( $wpdb->prepare( '
								SELECT min(products.salepricefloat) as minprice, max(products.salepricefloat) as maxprice
								FROM ' . $wpdb->prefix . 'posts posts 
								inner join ' . $wpdb->prefix . 'atkp_products products on products.product_id = posts.id
								WHERE posts.post_type in ("atkp_product") and posts.post_status in ("draft","publish")', '' ) );

			if ( count( $results ) > 0 ) {

				return $order == 'ASC' ? $results[0]->minprice : $results[0]->maxprice;
			}

			return 0;
		}

		if ( ! $newfield->isnewfield ) {
			$fieldname = ATKP_PRODUCT_POSTTYPE . '_customfield_' . $newfield->name;
		} else {
			$fieldname = ATKP_PRODUCT_POSTTYPE . '_cf_' . $newfield->name;
		}

		$args = array(
			'post_type'   => ATKP_PRODUCT_POSTTYPE,
			'post_status' => array( 'publish' ),
			'orderby'     => 'meta_value_num',
			'meta_key'    => $fieldname,
			'order'       => $order,
			'limit'       => 1,
		);


		$args = apply_filters( 'atkp_product_filter_getminmaxvalue', $args, $fieldname );

		$the_query = new WP_Query( $args );

		while ( $the_query->have_posts() ) {
			try {
				$the_query->the_post();

				$prd = $the_query->post;

				$minvalue = ATKPTools::get_post_setting( $prd->ID, $fieldname );
				break;

			} catch ( Exception $e ) {
				//TODO: logfile, falls ein wert nicht geparst werden kann?

			}
		}

		wp_reset_query();

		return $minvalue;
	}

	public function get_meta_values( $fieldname, $type = 'post', $status = 'publish' ) {

		global $wpdb;

		if ( $fieldname == ATKP_PRODUCT_POSTTYPE . '_manufacturer' ) {
			$results = $wpdb->get_results( $wpdb->prepare( '
								SELECT products.manufacturer
								FROM ' . $wpdb->prefix . 'posts posts 
								inner join ' . $wpdb->prefix . 'atkp_products products on products.product_id = posts.id
								WHERE posts.post_type in ("atkp_product") and posts.post_status in ("draft","publish") group by products.manufacturer', '' ) );

			$simple = array();

			foreach ( $results as $id ) {
				$simple[] = $id->manufacturer;

			}


			return $simple;
		}

		if ( $fieldname == ATKP_PRODUCT_POSTTYPE . '_brand' ) {
			$results = $wpdb->get_results( $wpdb->prepare( '
								SELECT products.brand
								FROM ' . $wpdb->prefix . 'posts posts 
								inner join ' . $wpdb->prefix . 'atkp_products products on products.product_id = posts.id
								WHERE posts.post_type in ("atkp_product") and posts.post_status in ("draft","publish") group by products.brand', '' ) );

			$simple = array();

			foreach ( $results as $id ) {
				$simple[] = $id->brand;

			}

			return $simple;
		}

		$r = $wpdb->get_col( $wpdb->prepare( "
        SELECT pm.meta_value FROM {$wpdb->postmeta} pm
        LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
        WHERE pm.meta_key = %s 
        AND p.post_status = %s 
        AND p.post_type = %s
    ", $fieldname, $status, $type ) );

		$simple = array();

		foreach ( $r as $key => $id ) {
			$simple[] = $id;

		}

		return $simple;
	}

	public function get_pricecontrol( $filterparams ) {
		//range
		$pricefield          = new atkp_udfield();
		$pricefield->id      = uniqid();
		$pricefield->caption = 'Price';
		$pricefield->name    = 'price';
		$pricefield->type    = 1;
		$pricefield->format  = 'number';

		$pricefield->prefix = get_option( ATKP_PLUGIN_PREFIX . '_searchform_price_prefix', '&euro;' );
		$pricefield->suffix = get_option( ATKP_PLUGIN_PREFIX . '_searchform_price_suffix', '' );

		return $this->create_frontendcontrol( $pricefield, null, 'price', '', false, $filterparams );;

	}

	public function get_custom_frontend_control( $fieldname2, $filterparams = null ) {
		$newfields = atkp_udfield::load_fields();

		foreach ( $newfields as $newfield ) {
			$fieldname = 'customfield_' . $newfield->name;

			if ( $fieldname != $fieldname2 ) {
				continue;
			}

			switch ( $newfield->type ) {
				case 3:
				case 4:
					//dropdown & yes no
					return $this->create_frontendcontrol( $newfield, null, $fieldname, '', false, $filterparams );
					break;
				case 1:
					//range
					if ( $newfield->format == 'number' ) {
						return $this->create_frontendcontrol( $newfield, null, $fieldname, '', false, $filterparams );
					}
					break;
			}


		}

		$groups = ATKPTools::get_fieldgroups();

		foreach ( $groups as $group ) {
			$fields = ATKPTools::get_post_setting( $group->ID, ATKP_FIELDGROUP_POSTTYPE . '_fields' );

			if ( $fields != null ) {
				foreach ( $fields as $field ) {
					$fieldname = 'cf_' . $field->name;

					if ( $fieldname != $fieldname2 ) {
						continue;
					}

					switch ( $field->type ) {
						case 3:
						case 4:
							//dropdown & yes no
							return $this->create_frontendcontrol( $field, null, $fieldname, '', true, $filterparams );
							break;
						case 1:
							//range
							if ( $field->format == 'number' ) {
								return $this->create_frontendcontrol( $field, null, $fieldname, '', true, $filterparams );
							}
							break;
					}
				}
			}
		}

		$taxonomies = atkp_udtaxonomy::load_taxonomies();

		if ( $taxonomies != null ) {
			foreach ( $taxonomies as $taxonomy ) {
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

				if ( $fieldname != $fieldname2 ) {
					continue;
				}

				//combo mit dropdown
				return $this->create_frontendcontrol( null, $taxonomy, $fieldname, '', false, $filterparams );
			}
		}

		return '';
	}

	public function create_frontendcontrol( $newfield, $taxonomy, $controlname, $value, $isnewfield = false, $filterparams = null ) {
		$result = '';

		if ( $newfield != null ) {
			$newfield->isnewfield = $isnewfield;
		}

		$filterparams = apply_filters( 'atkp_frontend_control_params', $filterparams, $newfield );

		if ( $taxonomy != null ) {
			$caption = sprintf( __( 'select %s', ATKP_PLUGIN_PREFIX ), $taxonomy->caption );

			$intvals = isset( $filterparams[ $controlname ] ) ? ( is_array( $filterparams[ $controlname ] ) ? $filterparams[ $controlname ] : array_map( 'intval', explode( ',', $filterparams[ $controlname ] ) ) ) : null;


			$result .= '<select id="' . esc_attr( $controlname ) . ( random_int( 1, 9999 ) ) . '" name="' . esc_attr( $controlname ) . '[]" class="atkp-selectcontrol"  placeholder="' . esc_attr( $caption ) . '" style="width:100%" multiple="multiple">';


			$categories = null;
			$categories = apply_filters( 'atkp_product_filter_gettaxonomies', $categories, $taxonomy->name );

			if($categories === null)
				$categories = get_categories( 'orderby=name&hide_empty=1&taxonomy=' . $taxonomy->name );

			foreach ( $categories as $category ) {
				$option = '<option value="' . esc_attr( $category->term_id ) . '" ' . ( is_array( $intvals ) && in_array( $category->term_id, $intvals ) ? 'selected' : '' ) . '>';
				$option .= $category->cat_name;
				$option .= '</option>';
				$result .= $option;
			}

			$result .= '</select>';

		} else if ( $newfield != null ) {
			switch ( $newfield->type ) {
				case 1:
					//Text
					$type = 'text';
					switch ( $newfield->format ) {
						case 'text':
							break;
						case 'number':

							$maxvalue = $this->get_minmaxvalue( $newfield, $order = 'DESC' );
							$minvalue = 0;

							if ( isset( $filterparams[ 'min' . $controlname ] )) {
								$minvalue_value = intval( $filterparams[ 'min' . $controlname ] ) ;
							} else {
								$minvalue_value = $minvalue;
							}
							if ( isset( $filterparams[ 'max' . $controlname ] ) ) {
								$maxvalue_value = intval( $filterparams[ 'max' . $controlname ] );
							} else {
								if($maxvalue == 0)
									$maxvalue_value = 100;
								else
									$maxvalue_value = $maxvalue;
							}


							$result = '<div class="atkp-rangeslider-container"><div class="atkp-minprice">' . $newfield->prefix . ' <span class="minprice-display" id="min' . $controlname . '-display"></span> ' . $newfield->suffix . '</div><div class="atkp-rangeslider" minname="min' . $controlname . '" maxname="max' . $controlname . '"><input id="min' . $controlname . '" name="min' . $controlname . '" type="hidden" defaultvalue="' . esc_attr( $minvalue ) . '" value="' . esc_attr( $minvalue_value ) . '" /><input id="max' . $controlname . '" name="max' . $controlname . '" type="hidden"  defaultvalue="' . esc_attr( $maxvalue ) . '" value="' . esc_attr( $maxvalue_value ) . '" /></div><div class="atkp-maxprice">' . $newfield->prefix . ' <span class="maxprice-display" id="max' . $controlname . '-display"></span> ' . $newfield->suffix . '</div></div><div class="atkp-clearfix"></div>';
							break;
						case 'url':
							break;
						case 'stars':

							$minvalue_value = $minvalue = 0;
							$maxvalue_value = $maxvalue = 5;

							$result = '<div class="atkp-rangeslider-container"><div class="atkp-minprice">' . $newfield->prefix . ' <span class="minprice-display" id="min' . $controlname . '-display"></span> ' . $newfield->suffix . '</div><div class="atkp-rangeslider" minname="min' . $controlname . '" maxname="max' . $controlname . '"><input id="min' . $controlname . '" name="min' . $controlname . '" type="hidden" defaultvalue="' . esc_attr( $minvalue ) . '" value="' . esc_attr( $minvalue_value ) . '" /><input id="max' . $controlname . '" name="max' . $controlname . '" type="hidden"  defaultvalue="' . esc_attr( $maxvalue ) . '" value="' . esc_attr( $maxvalue_value ) . '" /></div><div class="atkp-maxprice">' . $newfield->prefix . ' <span class="maxprice-display" id="max' . $controlname . '-display"></span> ' . $newfield->suffix . '</div></div><div class="atkp-clearfix"></div>';
							break;
					}


					break;
				case 2:
					//multiline

					//$result = '<textarea style="width:100%;height:100px" id="'. $controlname .'" name="'. $controlname .'">'.esc_textarea($value).'</textarea>';

					break;
				case 3:
					//dropdown

					if ( $isnewfield ) {
						$values = explode( "\n", $newfield->values );
					} else {
						$values = explode( ';', $newfield->format );
					}

					$caption = sprintf( __( 'select %s', ATKP_PLUGIN_PREFIX ), $newfield->caption );

					$stringvals = isset( $filterparams[ $controlname ] ) ? ( is_array( $filterparams[ $controlname ] ) ? $filterparams[ $controlname ] : explode( ',', $filterparams[ $controlname ] ) ) : null;


					$result = '<select id="' . esc_attr( $controlname . ( random_int( 1, 9999 ) ) ) . '" name="' . esc_attr( $controlname . '[]' ) . '"  class="atkp-selectcontrol" style="width:100%" placeholder="' . esc_attr( $caption ) . '" multiple="multiple" >  ';


					foreach ( $values as $value2 ) {
						$value2 = trim( $value2 );
						if ( $value2 != '' ) {
							$result .= '<option value="' . esc_attr( $value2 ) . '" ' . ( is_array( $stringvals ) && in_array( $value2, $stringvals ) ? 'selected' : '' ) . '>' . esc_textarea( $value2 ) . '</option>';
						}
					}

					$result .= '</select>';

					break;
				case 4:
					//yesno

					$boolval = isset( $filterparams[ $controlname ] ) ? boolval( $filterparams[ $controlname ] ) : false;

					$result .= '<div class="atkp-checkbox"><input type="checkbox" id="' . esc_attr( $controlname . ( random_int( 1, 9999 ) ) ) . '" name="' . esc_attr( $controlname ) . '" value="1" ' . ( $boolval ? ' checked' : '' ) . '>';
					$result .= '<label for="' . esc_attr( $controlname ) . '" >' . __( 'Yes', ATKP_PLUGIN_PREFIX ) . '</label></div>';

					break;
				case 5:
					//html

					/*ob_start();

					wp_editor($value, $controlname, array(
							'media_buttons' => false,
							'textarea_name' => $controlname,
							'textarea_rows' => 5,
					));

					$result = ob_get_contents();

					ob_end_clean();*/
					break;

			}
		} else {
			//product dropdown
			$searchnounce = wp_create_nonce( 'atkp-search-nonce' );

			switch ( $controlname ) {
				case 'search':
					$strval      = isset( $filterparams[ $controlname ] ) ? strval( $filterparams['search'] ) : '';

					$result .= '<input type="text" name="search" placeholder="' . __( 'Enter a search term', ATKP_PLUGIN_PREFIX ) . '" value="' . esc_attr( $strval ) . '" />';
					break;
				case 'submit':
					$result .= '<input type="submit" class="atkp-submitbutton" value="' . __( 'Find', ATKP_PLUGIN_PREFIX ) . '" />';
					break;
				case 'orderby':
					//neuheiten
					//bewertungen
					//preis auf bzw. absteigend
					//produktname auf bzw. absteigend

					$values = array(
						'price-asc'      => __( 'Price', ATKP_PLUGIN_PREFIX ),
						'price-desc'     => __( 'Price (descending)', ATKP_PLUGIN_PREFIX ),
						'titlerank-asc'  => __( 'Alphabetic (A to Z)', ATKP_PLUGIN_PREFIX ),
						'titlerank-desc' => __( 'Alphabetic (Z to A)', ATKP_PLUGIN_PREFIX ),
					);


					$caption = __( 'sort by', ATKP_PLUGIN_PREFIX );

					$stringvals =isset( $filterparams[ $controlname ] ) ?  strval( $filterparams[$controlname] ) : '';


					$result = '<select id="' . $controlname.(random_int(1, 9999)) . '" name="' . $controlname . '"  class="atkp-selectcontrol" style="width:100%" placeholder="' . $caption . '" >  ';

					$result .= '<option value="" ' . ( $stringvals == '' ? 'selected' : '' ) . '>' . esc_textarea( $caption ) . '</option>';

					$values = apply_filters( 'atkp_product_filter_orderby', $values, $newfield );

					foreach ( $values as $key => $value2 ) {
						$result .= '<option value="' . esc_attr( $key ) . '" ' . ( $stringvals == $key ? 'selected' : '' ) . '>' . esc_textarea( $value2 ) . '</option>';
					}

					$result .= '</select>';
					break;
				case "shop":
					$caption = __( 'select shop', ATKP_PLUGIN_PREFIX );
					$result  = '<select id="' . $controlname . ( random_int( 1, 9999 ) ) . '" name="' . esc_attr( $controlname ) . '"   style="width:100%" data-placeholder= "' . esc_attr( $caption ) . '" placeholder="' . esc_attr( $caption ) . '">  ';
					$result  .= '<option value="">' . esc_textarea( $caption ) . '</option>';

					$shopid = isset( $filterparams[ $controlname ] ) ? intval( $filterparams[ $controlname ] ) : '';

					$shoplist     = atkp_shop::get_list( $shopid );
					$filteredlist = array();

					foreach ( $shoplist as $shop ) {
						if ( $shop->provider == null ) {
							continue;
						}

						$filteredlist[] = $shop;

						foreach ( $shop->children as $child ) {
							$filteredlist[] = $child;

						}
					}
					$filteredlist = apply_filters( 'atkp_product_filter_shop', $filteredlist, $newfield );

					foreach ( $filteredlist as $shop ) {
						$result .= '<option ' . ( $shop->selected == true ? 'selected' : '' ) . ' value="' . esc_attr( $shop->id ) . '">' . esc_textarea( $shop->get_title() ) . '</option>';
					}
					$result .= '</select>';
					break;
				case "brand":
					$caption = __( 'select brand', ATKP_PLUGIN_PREFIX );
					$result  = '<select id="' . esc_attr( $controlname . ( random_int( 1, 9999 ) ) ) . '" name="' . esc_attr( $controlname ) . '"  class="atkp-selectcontrol" style="width:100%" data-placeholder= "' . esc_attr( $caption ) . '" placeholder="' . esc_attr( $caption ) . '">  ';

					$result .= '<option value="">' . esc_textarea( $caption ) . '</option>';

					$shopid = isset( $filterparams[ $controlname ] ) ? ( $filterparams[ $controlname ] ) : '';
//TODO: Hersteller laden
					$shoplist = $this->get_meta_values( ATKP_PRODUCT_POSTTYPE . '_brand', ATKP_PRODUCT_POSTTYPE );

					$shoplist = apply_filters( 'atkp_product_filter_brand', $shoplist, $newfield );

					foreach ( $shoplist as $shop ) {
						$result .= '<option ' . ( $shop == $shopid ? 'selected' : '' ) . ' value="' . esc_attr( $shop ) . '">' . esc_textarea( $shop ) . '</option>';
					}

					$result .= '</select>';
					break;

				case "manufacturer":
					$caption = __( 'select manufacturer', ATKP_PLUGIN_PREFIX );
					$result  = '<select id="' . esc_attr( $controlname . ( random_int( 1, 9999 ) ) ) . '" name="' . esc_attr( $controlname ) . '" class="atkp-selectcontrol"  style="width:100%" data-placeholder= "' . esc_attr( $caption ) . '" placeholder="' . esc_attr( $caption ) . '">  ';

					$result .= '<option value="">' . esc_textarea( $caption ) . '</option>';

					$shopid = isset( $filterparams[ $controlname ] ) ? ( $filterparams[ $controlname ] ) : '';
//TODO: Hersteller laden
					$shoplist = $this->get_meta_values( ATKP_PRODUCT_POSTTYPE . '_manufacturer', ATKP_PRODUCT_POSTTYPE );

					$shoplist = apply_filters( 'atkp_product_filter_manufacturer', $shoplist, $newfield );

					foreach ( $shoplist as $shop ) {
						$result .= '<option ' . ( $shop == $shopid ? 'selected' : '' ) . ' value="' . esc_attr( $shop ) . '">' . esc_textarea( $shop ) . '</option>';
					}

					$result .= '</select>';
					break;
				case "product1":
				case "product2":
				case "product3":
				case "product4":
				case "product5":
					$caption    = __( 'select product', ATKP_PLUGIN_PREFIX );

					$productid = isset( $filterparams[$controlname] )  ? intval( $filterparams[$controlname] ) : 0;

					$inputtooshort = __( 'You must enter at least 3 characters.', ATKP_PLUGIN_PREFIX );

					$result = '<select id="' . esc_attr( $controlname . ( random_int( 1, 9999 ) ) ) . '" name="' . esc_attr( $controlname ) . '"  class="atkp-product-selectcontrol" style="width:100%" data-placeholder= "' . esc_attr( $caption ) . '" placeholder="' . esc_attr( $caption ) . '" searchnounce="' . esc_attr( $caption ) . '" inputtooshort="' . esc_attr( $inputtooshort ) . '" endpointurl="' . esc_attr( ATKPTools::get_endpointurl()) . '">  ';

					//$result .= '<option></option>';


					if ( $productid != 0 ) {
						$productitle = ATKPTools::get_post_setting( $productid, ATKP_PRODUCT_POSTTYPE . '_title', true );
						$result      .= '<option value="' . esc_attr( $productid ) . '" selected>' . esc_textarea( $productitle ) . '</option>';
					}


					$result .= '</select>';
					break;
			}

		}

		return $result;
	}


	public function create_backendcontrol( $newfield, $taxonomy, $controlname, $value, $isnewfield = false ) {
		$result = '';

		if ( $taxonomy != null ) {
			$caption = sprintf( __( 'select %s', ATKP_PLUGIN_PREFIX ), $taxonomy->caption );

			$intvals = array();


			$result .= '<select id="' . esc_attr( $controlname ) . '" name="' . esc_attr( $controlname ) . '"  class="atkp-backend-filter" placeholder="' . esc_attr( $caption ) . '" style="width:100%" multiple="multiple">';

			$categories = get_categories( 'orderby=name&hide_empty=0&taxonomy=' . $taxonomy->name );
			foreach ( $categories as $category ) {
				$option = '<option value="' . esc_attr( $category->term_id ) . '">';
				$option .= esc_textarea( $category->cat_name );
				$option .= '</option>';
				$result .= $option;
			}

			$result .= '</select>';

		} else if ( $newfield != null ) {
			switch ( $newfield->type ) {
				case 1:
					//Text
					$type = 'text';
					switch ( $newfield->format ) {
						case 'text':
							break;
						case 'number':


							$maxvalue = 0;
							$minvalue = 0; //$this->get_minmaxvalue($newfield, $order = 'ASC');


							$result = '<input id="minprice" name="min' . $controlname . '" type="number" value="' . esc_attr( $minvalue ) . '"  class="atkp-backend-filter" /> - <input id="maxprice"  class="atkp-backend-filter" name="max' . $controlname . '" type="number" value="' . esc_attr( $maxvalue ) . '" />';
							break;
						case 'url':
							break;
					}


					break;
				case 2:
					//multiline

					//$result = '<textarea style="width:100%;height:100px" id="'. $controlname .'" name="'. $controlname .'">'.esc_textarea($value).'</textarea>';

					break;
				case 3:
					//dropdown

					if ( $isnewfield ) {
						$values = explode( "\n", $newfield->values );
					} else {
						$values = explode( ';', $newfield->format );
					}

					$caption = sprintf( __( 'select %s', ATKP_PLUGIN_PREFIX ), $newfield->caption );


					$result = '<select id="' . esc_attr( $controlname ) . '" name="' . esc_attr( $controlname ) . '"  style="width:100%"  class="atkp-backend-filter" placeholder="' . esc_attr( $caption ) . '" multiple="multiple" >  ';


					foreach ( $values as $value2 ) {
						$value2 = trim( $value2 );
						if ( $value2 != '' ) {
							$result .= '<option value="' . esc_attr( $value2 ) . '">' . esc_textarea( $value2 ) . '</option>';
						}
					}

					$result .= '</select>';

					break;
				case 4:
					//yesno


					$result .= '<div class="atkp-checkbox"><input type="checkbox" id="' . esc_attr( $controlname ) . '" class="atkp-backend-filter" name="' . esc_attr( $controlname ) . '" value="0">';
					$result .= '<label for="' . esc_attr( $controlname ) . '" >' . __( 'Yes', ATKP_PLUGIN_PREFIX ) . '</label></div>';

					break;
				case 5:
					//html

					/*ob_start();

					wp_editor($value, $controlname, array(
							'media_buttons' => false,
							'textarea_name' => $controlname,
							'textarea_rows' => 5,
					));

					$result = ob_get_contents();

					ob_end_clean();*/
					break;

			}
		} else {
			//product dropdown

			switch ( $controlname ) {
				case 'orderby':
					//neuheiten
					//bewertungen
					//preis auf bzw. absteigend
					//produktname auf bzw. absteigend

					$values = array(
						'price-asc'      => __( 'Price', ATKP_PLUGIN_PREFIX ),
						'price-desc'     => __( 'Price (descending)', ATKP_PLUGIN_PREFIX ),
						'amountsaved-asc'      => __( 'Amount saved', ATKP_PLUGIN_PREFIX ),
						'amountsaved-desc'     => __( 'Amount saved (descending)', ATKP_PLUGIN_PREFIX ),
						'titlerank-asc'  => __( 'Alphabetic (A to Z)', ATKP_PLUGIN_PREFIX ),
						'titlerank-desc' => __( 'Alphabetic (Z to A)', ATKP_PLUGIN_PREFIX ),
					);


					$caption = __( 'sort by', ATKP_PLUGIN_PREFIX );


					$result = '<select id="' . esc_attr( $controlname ) . '" name="' . esc_attr( $controlname ) . '" class="atkp-backend-filter"  style="width:100%" placeholder="' . esc_attr( $caption) . '" >  ';

					$result .= '<option value="">' . esc_textarea( $caption ) . '</option>';

					foreach ( $values as $key => $value2 ) {
						$result .= '<option value="' . esc_attr( $key ) . '">' . esc_textarea( $value2 ) . '</option>';
					}

					$result .= '</select>';
					break;
				case 'productstatus':
					$result = '<select id="' . esc_attr( $controlname ) . '" name="' . esc_attr( $controlname ) . '" class="atkp-backend-filter"  style="width:100%">  ';
					$result .= '<option value="draft">' . __( 'Draft', ATKP_PLUGIN_PREFIX ) . '</option>';
					$result .= '<option value="publish" selected>' . __( 'Published', ATKP_PLUGIN_PREFIX ) . '</option>';
					$result .= '<option value="all">' . __( 'All', ATKP_PLUGIN_PREFIX ) . '</option>';
					$result .= ' </select>';
					break;
				case "shop":
					$caption = __( 'input shopid', ATKP_PLUGIN_PREFIX );
					$result  = '<input type="number" id="' . esc_attr( $controlname ) . '" name="' . esc_attr( $controlname ) . '" class="atkp-backend-filter"  style="width:100%" placeholder="' . esc_attr( $caption ) . '" />  ';


					break;
				case 'manufacturer':
					$caption = __( 'input manufacturer', ATKP_PLUGIN_PREFIX );
					$result  = '<input type="number" id="' . esc_attr( $controlname ) . '" name="' . esc_attr( $controlname ) . '" class="atkp-backend-filter"  style="width:100%" placeholder="' . esc_attr( $caption ) . '" />  ';

					break;

				case 'brand':
					$caption = __( 'input brand', ATKP_PLUGIN_PREFIX );
					$result  = '<input type="number" id="' . esc_attr( $controlname ) . '" name="' . esc_attr( $controlname ) . '" class="atkp-backend-filter"  style="width:100%" placeholder="' . esc_attr( $caption ) . '" />  ';

					break;
				case "product1":
				case "product2":
				case "product3":
				case "product4":
				case "product5":
					$caption = __( 'input productid', ATKP_PLUGIN_PREFIX );

					$inputtooshort = __( 'You must enter at least 3 characters.', ATKP_PLUGIN_PREFIX );

					//atkp-product-box
					$disable_select2 = true; // ATKPTools::get_setting(ATKP_PLUGIN_PREFIX.'_disableselect2', false);

					if ( $disable_select2 ) {
						$result = '<input type="number" id="' . esc_attr( $controlname ) . '" name="' . esc_attr( $controlname ) . '" class="atkp-backend-filter"  style="width:100%" placeholder="' . esc_attr( $caption ) . '" />  ';
					} else {
						$result = '<select id="' . esc_attr( $controlname ) . '" name="' . esc_attr( $controlname ) . '" class="atkp-backend-filter atkp-product-box"  style="width:100%">  ';
						$result .= '<option value="" selected>' . __( 'None', ATKP_PLUGIN_PREFIX ) . '</option>';
						$result .= ' </select>';
					}


					break;
			}

		}

		return $result;
	}
}


?>