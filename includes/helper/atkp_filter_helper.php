<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class atkp_filter_helper {
	public $max_num_pages;
	public $found_posts;

	public function parse_params_filter( $filter = '', $parseparams = false ) {

		//*** Parse url parameters and merge existing filter array *** //

		if ( $filter == '' ) {
			$filterparams = null;
		} else {
			parse_str( str_replace( '&amp;', '&', $filter ), $filterparams );
		}


		$newfields  = atkp_udfield::load_fields();
		$taxonomies = atkp_udtaxonomy::load_taxonomies();

		//die seite muss auch one parseparams gelesen werden
		if ( ATKPTools::exists_get_parameter( 'tpage' ) ) {
			$filterparams['paged'] = ATKPTools::get_get_parameter( 'tpage', 'int' );
		}

		if ( ATKPTools::exists_get_parameter( 'group_result' ) ) {
			$filterparams['group_result'] = ATKPTools::get_get_parameter( 'group_result', 'int' );
		}

		if ( $parseparams ) {
			if ( ATKPTools::exists_get_parameter( 'search' ) ) {
				$filterparams['search'] = ATKPTools::get_get_parameter( 'search', 'string' );
			}
			if ( ATKPTools::exists_get_parameter( 'shop' ) ) {
				$filterparams['shop'] = ATKPTools::get_get_parameter( 'shop', 'int' );
			}
			if ( ATKPTools::exists_get_parameter( 'product1' ) ) {
				$filterparams['product1'] = ATKPTools::get_get_parameter( 'product1', 'int' );
			}
			if ( ATKPTools::exists_get_parameter( 'product2' ) ) {
				$filterparams['product2'] = ATKPTools::get_get_parameter( 'product2', 'int' );
			}
			if ( ATKPTools::exists_get_parameter( 'product3' ) ) {
				$filterparams['product3'] = ATKPTools::get_get_parameter( 'product3', 'int' );
			}
			if ( ATKPTools::exists_get_parameter( 'product4' ) ) {
				$filterparams['product4'] = ATKPTools::get_get_parameter( 'product4', 'int' );
			}
			if ( ATKPTools::exists_get_parameter( 'product5' ) ) {
				$filterparams['product5'] = ATKPTools::get_get_parameter( 'product5', 'int' );
			}

			if ( ATKPTools::exists_get_parameter( 'productstatus' ) ) {
				$filterparams['productstatus'] = ATKPTools::get_get_parameter( 'productstatus', 'string' );
			}

			if ( ATKPTools::exists_get_parameter( 'minprice' ) || ATKPTools::exists_get_parameter( 'maxprice' ) ) {
				$filterparams['minprice'] = ATKPTools::get_get_parameter( 'minprice', 'int' );
				$filterparams['maxprice'] = ATKPTools::get_get_parameter( 'maxprice', 'int' );
			}

			if ( ATKPTools::exists_get_parameter( 'minamountsaved' ) || ATKPTools::exists_get_parameter( 'maxamountsaved' ) ) {
				$filterparams['minamountsaved'] = ATKPTools::get_get_parameter( 'minamountsaved', 'int' );
				$filterparams['maxamountsaved'] = ATKPTools::get_get_parameter( 'maxamountsaved', 'int' );
			}

			if ( ATKPTools::exists_get_parameter( 'manufacturer' ) ) {
				$filterparams['manufacturer'] = ATKPTools::get_get_parameter( 'manufacturer', 'string' );
			}

			if ( ATKPTools::exists_get_parameter( 'brand' ) ) {
				$filterparams['brand'] = ATKPTools::get_get_parameter( 'brand', 'string' );
			}

			if ( ATKPTools::exists_get_parameter( 'orderby' ) ) {
				$filterparams['orderby'] = ATKPTools::get_get_parameter( 'orderby', 'string' );
			}

			foreach ( $newfields as $newfield ) {
				$fieldname = 'customfield_' . $newfield->name;

				switch ( $newfield->type ) {
					case 3:
						//dropdown
						if ( ATKPTools::exists_get_parameter( $fieldname ) ) {
							$filterparams[ $fieldname ] = ATKPTools::get_get_parameter( $fieldname, 'stringarray' );
						}
						break;
					case 4:
						//yes no
						if ( ATKPTools::exists_get_parameter( $fieldname ) ) {
							$filterparams[ $fieldname ] = ATKPTools::get_get_parameter( $fieldname, 'bool' );
						}
						break;
					case 1:
						//range

						if ( $newfield->format == 'number' ) {
							if ( ATKPTools::exists_get_parameter( 'min' . $fieldname ) ) {
								$filterparams[ 'min' . $fieldname ] = ATKPTools::get_get_parameter( 'min' . $fieldname, 'int' );
							}
							if ( ATKPTools::exists_get_parameter( 'max' . $fieldname ) ) {
								$filterparams[ 'max' . $fieldname ] = ATKPTools::get_get_parameter( 'max' . $fieldname, 'int' );
							}
						}
						break;
				}
			}


			$groups = ATKPTools::get_fieldgroups();

			foreach ( $groups as $group ) {

				$fields = ATKPTools::get_post_setting( $group->ID, ATKP_FIELDGROUP_POSTTYPE . '_fields' );
				if ( $fields != null ) {
					foreach ( $fields as $newfield ) {
						$fieldname = 'cf_' . $newfield->name;

						switch ( $newfield->type ) {
							case 3:
								//dropdown
								if ( ATKPTools::exists_get_parameter( $fieldname ) ) {
									$filterparams[ $fieldname ] = ATKPTools::get_get_parameter( $fieldname, 'stringarray' );
								}
								break;
							case 4:
								//yes no
								if ( ATKPTools::exists_get_parameter( $fieldname ) ) {
									$filterparams[ $fieldname ] = ATKPTools::get_get_parameter( $fieldname, 'bool' );
								}
								break;
							case 1:
								//range

								if ( $newfield->format == 'number' ) {
									if ( ATKPTools::exists_get_parameter( 'min' . $fieldname ) ) {
										$filterparams[ 'min' . $fieldname ] = ATKPTools::get_get_parameter( 'min' . $fieldname, 'int' );
									}
									if ( ATKPTools::exists_get_parameter( 'max' . $fieldname ) ) {
										$filterparams[ 'max' . $fieldname ] = ATKPTools::get_get_parameter( 'max' . $fieldname, 'int' );
									}
								}
								break;
						}
					}
				}
			}

			if ( $taxonomies != null ) {
				foreach ( $taxonomies as $taxonomy ) {
					$fieldname = $taxonomy->get_fieldname();

					//combo mit dropdown
					if ( ATKPTools::exists_get_parameter( $fieldname ) ) {
						$filterparams[ $fieldname ] = ATKPTools::get_get_parameter( $fieldname, 'intarray' );
					}
				}
			}
		}

		return $filterparams;
	}

	public function parse_params_products( $itemsPerPage = 25, $parseparams = false, $filter = '' , $limit = -1) {
		$productlist = array();

		//*** Parse url parameters and merge existing filter array *** //
		$filterparams = $this->parse_params_filter( $filter, $parseparams );

		$newfields  = atkp_udfield::load_fields();
		$taxonomies = atkp_udtaxonomy::load_taxonomies();

		global $wpdb;
		$where   = array();
		$orderby = array();
		$params  = array();
		$joins   = array();


		if ( isset( $filterparams['productstatus'] ) && $filterparams['productstatus'] == 'all' ) {
			$where[] = "posts.post_status in ('publish', 'draft')";
		} else if ( isset( $filterparams['productstatus'] ) && $filterparams['productstatus'] == 'publish' ) {
			$where[]  = "posts.post_status = %s";
			$params[] = 'publish';
		} else if ( isset( $filterparams['productstatus'] ) && $filterparams['productstatus'] == 'draft' ) {
			$where[]  = "posts.post_status = %s";
			$params[] = 'draft';
		} else {

			if ( ! is_plugin_active( 'affiliate-toolkit-productpage/affiliate-toolkit-productpage.php' ) ) {
				$where[] = "posts.post_status in ('publish', 'draft')";
			} else {
				$where[]  = "posts.post_status = %s";
				$params[] = 'publish';
			}
		}

		if ( $limit > 0 && $itemsPerPage > $limit ) {
			$itemsPerPage = $limit;
		}

		if ( isset( $filterparams['paged'] ) ) {
			$page = intval( $filterparams['paged'] );
		} else {
			$page = 1;
		}
		//TODO: $itemsPerPage

		$where[]  = "posts.post_type = %s";
		$params[] = ATKP_PRODUCT_POSTTYPE;


		if ( isset( $filterparams['search'] ) ) {
			$clean_string = trim( $filterparams['search'] );
			//$clean_string = str_replace( ' ', '%', $clean_string );

			$where[]  = "posts.post_title like %s";
			$params[] = '%' . $wpdb->esc_like( $clean_string ) . '%';

			$criteriaExists = true;
		}


		$productids = array();
		if ( isset( $filterparams['product1'] ) ) {
			$productids[] = intval( $filterparams['product1'] );
		}
		if ( isset( $filterparams['product2'] ) ) {
			$productids[] = intval( $filterparams['product2'] );
		}
		if ( isset( $filterparams['product3'] ) ) {
			$productids[] = intval( $filterparams['product3'] );
		}
		if ( isset( $filterparams['product4'] ) ) {
			$productids[] = intval( $filterparams['product4'] );
		}
		if ( isset( $filterparams['product5'] ) ) {
			$productids[] = intval( $filterparams['product5'] );
		}

		if ( count( $productids ) > 0 ) {
			$where[] = "posts.id in (" . implode( ',', $productids ) . ")";
		}

		if ( isset( $filterparams['shop'] ) && $filterparams['shop'] != '' ) {
			$where[] = "products.shop_id in (" . $filterparams['shop'] . ")";
		}

		if ( isset( $filterparams['minprice'] ) || isset( $filterparams['maxprice'] ) ) {
			$minprice = isset( $filterparams['minprice'] ) ? floatval( $filterparams['minprice'] ) : 0;
			$maxprice = isset( $filterparams['maxprice'] ) ? floatval( $filterparams['maxprice'] ) : 0;

			if ( $minprice == 0 && $maxprice > 0 ) {
				$where[]  = "products.salepricefloat < %f";
				$params[] = $maxprice;

				$criteriaExists = true;

			} else if ( $minprice > 0 && $maxprice == 0 ) {
				$where[]  = "products.salepricefloat > %f";
				$params[] = $minprice;

				$criteriaExists = true;
			}else if($minprice > 0 && $maxprice > 0 ) {
				$where[]  = "products.salepricefloat between %f and %f";
				$params[] = $minprice;
				$params[] = $maxprice;

				$criteriaExists = true;
			}
		}

		$minamountsaved = 0;
		$maxamountsaved = 0;
		if ( isset( $filterparams['minamountsaved'] ) || isset( $filterparams['maxamountsaved'] ) ) {
			$minamountsaved = isset( $filterparams['minamountsaved'] )  ? intval( $filterparams['minamountsaved'] ) : 0;
			$maxamountsaved = isset( $filterparams['maxamountsaved'] )  ? intval( $filterparams['maxamountsaved'] ) : 0;


			if ( $minamountsaved == 0 && $maxamountsaved > 0 ) {
				$where[]  = "products.amountsavedfloat < %f";
				$params[] = $maxamountsaved;

				$criteriaExists = true;

			} else if( $minamountsaved > 0 && $maxamountsaved == 0 ) {
				$where[]  = "products.amountsavedfloat > %f";
				$params[] = $minamountsaved;

				$criteriaExists = true;
			} else if( $minamountsaved > 0 && $maxamountsaved > 0 ) {
				$where[]  = "products.amountsavedfloat between %f and %f";
				$params[] = $minamountsaved;
				$params[] = $maxamountsaved;

				$criteriaExists = true;
			}
		}

		if ( isset( $filterparams['orderby'] ) ) {
			$orderBy = strval( $filterparams['orderby'] );

			switch ( $orderBy ) {
				case 'price-asc':
					$orderby[] = 'products.salepricefloat asc';
					break;
				case 'price-desc':
					$orderby[] = 'products.salepricefloat desc';
					break;
				case 'amountsaved-asc':
					$orderby[] = 'products.amountsavedfloat asc';
					break;
				case 'amountsaved-desc':
					$orderby[] = 'products.amountsavedfloat desc';
					break;
				case 'titlerank-asc':
					$orderby[] = 'products.title asc';
					break;
				case 'titlerank-desc':
					$orderby[] = 'products.title desc';
					break;
			}
		}

		if ( isset( $filterparams['manufacturer'] ) && $filterparams['manufacturer'] != '' ) {
			$manufacturer = strval( $filterparams['manufacturer'] );

			$where[]  = "products.manufacturer = %s";
			$params[] = $manufacturer;
		}

		if ( isset( $filterparams['brand'] ) && $filterparams['brand'] != '' ) {
			$brand = strval( $filterparams['brand'] );

			$where[]  = "products.brand = %s";
			$params[] = $brand;
		}

		#region wp filter fields
		$has_wp_filter_fields = false;
		$args                 = array(
			'fields'         => 'ids',
			'posts_per_page' => - 1,
			'post_type'      => array( ATKP_PRODUCT_POSTTYPE ),
			'meta_query'     => array(),
			'tax_query'      => array(),
			'relation'       => 'AND'
		);

		foreach ( $newfields as $newfield ) {
			$fieldname = 'customfield_' . $newfield->name;

			switch ( $newfield->type ) {
				case 3:
					//dropdown
					$dropdown = isset( $filterparams[ $fieldname ] ) ? ( is_array( $filterparams[ $fieldname ] ) ? $filterparams[ $fieldname ] : explode( ',', $filterparams[ $fieldname ] ) ) : null;

					if ( $dropdown != null && count( $dropdown ) > 0 ) {
						$para = array(
							'key'     => ATKP_PRODUCT_POSTTYPE . '_' . $fieldname,
							'value'   => $dropdown,
							'compare' => 'IN',
							'type'    => 'CHAR'
						);

						$args['meta_query'][] = $para;

						$has_wp_filter_fields = true;
					}

					break;
				case 4:
					//yes no
					$yesno = isset( $filterparams[ $fieldname ] ) ? boolval( $filterparams[ $fieldname ] ) : false;

					if ( $yesno != null && $yesno == true ) {
						$args['meta_query'][] = array(
							'key'     => ATKP_PRODUCT_POSTTYPE . '_' . $fieldname,
							'value'   => $yesno,
							'compare' => '=',
							'type'    => 'NUMERIC'
						);

						$has_wp_filter_fields = true;
					}

					break;
				case 1:
					//range
					if ( $newfield->format == 'number' ) {
						$minnumber = isset( $filterparams[ 'min' . $fieldname ] ) ? intval( $filterparams[ 'min' . $fieldname ] ) : null;
						$maxnumber = isset( $filterparams[ 'max' . $fieldname ] ) ? intval( $filterparams[ 'max' . $fieldname ] ) : null;


						if ( ( $minnumber != null || $minnumber == 0 ) && $maxnumber != null ) {

							if ( $minnumber == 0 ) {
								$args['meta_query'][] = array(
									'relation' => 'OR',
									array(
										'key'     => ATKP_PRODUCT_POSTTYPE . '_' . $fieldname,
										'compare' => 'NOT EXISTS',
									),
									array(
										'key'     => ATKP_PRODUCT_POSTTYPE . '_' . $fieldname,
										'value'   => array( $minnumber, $maxnumber ),
										'compare' => 'BETWEEN',
										'type'    => 'numeric'
									)
								);


							} else {
								$args['meta_query'][] = array(
									'key'     => ATKP_PRODUCT_POSTTYPE . '_' . $fieldname,
									'value'   => array( $minnumber, $maxnumber ),
									'compare' => 'BETWEEN',
									'type'    => 'numeric'
								);
							}

							$has_wp_filter_fields = true;
						}

					}
					break;
			}
		}

		$groups = ATKPTools::get_fieldgroups();

		foreach ( $groups as $group ) {

			$fields = ATKPTools::get_post_setting( $group->ID, ATKP_FIELDGROUP_POSTTYPE . '_fields' );
			if ( $fields != null ) {
				foreach ( $fields as $newfield ) {
					$fieldname = 'cf_' . $newfield->name;

					switch ( $newfield->type ) {
						case 3:
							//dropdown
							$dropdown = isset( $filterparams[ $fieldname ] ) ? ( is_array( $filterparams[ $fieldname ] ) ? $filterparams[ $fieldname ] : explode( ',', $filterparams[ $fieldname ] ) ) : null;

							if ( $dropdown != null && count( $dropdown ) > 0 ) {
								$args['meta_query'][] = array(
									'key'     => ATKP_PRODUCT_POSTTYPE . '_' . $fieldname,
									'value'   => $dropdown,
									'compare' => 'IN',
									'type'    => 'CHAR'
								);

								$has_wp_filter_fields = true;
							}

							break;
						case 4:
							//yes no
							$yesno = isset( $filterparams[ $fieldname ] ) ? boolval( $filterparams[ $fieldname ] ) : false;

							if ( $yesno != null && $yesno == true ) {
								$args['meta_query'][] = array(
									'key'     => ATKP_PRODUCT_POSTTYPE . '_' . $fieldname,
									'value'   => $yesno,
									'compare' => '=',
									'type'    => 'NUMERIC'
								);

								$has_wp_filter_fields = true;
							}

							break;
						case 1:
							//range
							if ( $newfield->format == 'number' ) {
								$minnumber = isset( $filterparams[ 'min' . $fieldname ] ) ? intval( $filterparams[ 'min' . $fieldname ] ) : null;
								$maxnumber = isset( $filterparams[ 'max' . $fieldname ] ) ? intval( $filterparams[ 'max' . $fieldname ] ) : null;


								if ( ( $minnumber != null || $minnumber == 0 ) && $maxnumber != null ) {

									if ( $minnumber == 0 ) {
										$args['meta_query'][] = array(
											'relation' => 'OR',
											array(
												'key'     => ATKP_PRODUCT_POSTTYPE . '_' . $fieldname,
												'compare' => 'NOT EXISTS',
											),
											array(
												'key'     => ATKP_PRODUCT_POSTTYPE . '_' . $fieldname,
												'value'   => array( $minnumber, $maxnumber ),
												'compare' => 'BETWEEN',
												'type'    => 'numeric'
											)
										);


									} else {
										$args['meta_query'][] = array(
											'key'     => ATKP_PRODUCT_POSTTYPE . '_' . $fieldname,
											'value'   => array( $minnumber, $maxnumber ),
											'compare' => 'BETWEEN',
											'type'    => 'numeric'
										);
									}
									$has_wp_filter_fields = true;
								}
							}
							break;
					}
				}
			}
		}


		if ( $taxonomies != null ) {
			foreach ( $taxonomies as $taxonomy ) {
				$fieldname = $taxonomy->get_fieldname();

				//combo mit dropdown
				$number = isset( $filterparams[ $fieldname ] ) ? ( is_array( $filterparams[ $fieldname ] ) ? $filterparams[ $fieldname ] : array_map( 'intval', explode( ',', $filterparams[ $fieldname ] ) ) ) : null;

				if ( $number != null && count( $number ) > 0 ) {
					$args['tax_query'][] = array(
						'taxonomy' => $taxonomy->name, //or tag or custom taxonomy
						'field'    => 'id',
						'terms'    => $number
					);

					$has_wp_filter_fields = true;
				}
			}
		}

		$args                 = apply_filters( 'atkp_product_filter_args', $args );
		$has_wp_filter_fields = apply_filters( 'atkp_product_filter_has_wp_filter', $has_wp_filter_fields );


		if ( $has_wp_filter_fields ) {
			$wp_post_filter = get_posts( $args );
			if ( count( $wp_post_filter ) == 0 ) {
				return '<span class="atkp-noproducts">' . __( 'No products found for these search criteria.', ATKP_PLUGIN_PREFIX ) . '</span>';
			}

			$where[] = "posts.id in (" . implode( ',', $wp_post_filter ) . ")";
		}
		#endregion


		if ( isset( $filterparams['paged'] ) ) {
			$page = intval( $filterparams['paged'] );
		} else {
			$page = 1;
		}
		if ( $limit > 0 && $itemsPerPage > $limit ) {
			$itemsPerPage = $limit;
		}

		$offset = ( ( $page - 1 ) * $itemsPerPage );

		$group_result = isset( $filterparams['group_result'] ) && $filterparams['group_result'] != '';

		$hide_shop_values = $wpdb->get_results( 'SELECT posts.id, pm.meta_value as hide_shops
FROM ' . $wpdb->prefix . 'posts posts 
inner join ' . $wpdb->prefix . 'postmeta pm on pm.post_id = posts.id and pm.meta_key = "atkp_product_hide_shops"
WHERE posts.post_type = "atkp_product"' );

		$sql_ignore_productshop = array();
		foreach ( $hide_shop_values as $hide_shop_value ) {
			$x = unserialize( $hide_shop_value->hide_shops );

			foreach ( $x as $hidden ) {
				$sql_ignore_productshop[] = '\'' . $hidden['shop_id'] . '_' . $hidden['product_id'] . '\'';
			}
		}

		$hide_shop_values = $wpdb->get_results( 'SELECT posts.id, pm.meta_value as hide_shop
FROM ' . $wpdb->prefix . 'posts posts 
inner join ' . $wpdb->prefix . 'postmeta pm on pm.post_id = posts.id and pm.meta_key = "atkp_shop_hidepricecomparision"
WHERE posts.post_type = "atkp_shop" and pm.meta_value = 1' );

		$sql_ignore_shop = array();
		foreach ( $hide_shop_values as $hide_shop_value ) {
			$sql_ignore_shop[] = $hide_shop_value->id;
		}

		$sql_ignore_string = '';

		if ( count( $sql_ignore_productshop ) > 0 ) {
			$sql_ignore_string .= ' AND CONCAT(products.shop_id, "_", posts.id ) NOT IN (' . implode( ',', $sql_ignore_productshop ) . ')';
		}
		if ( count( $sql_ignore_shop ) > 0 ) {
			$sql_ignore_string .= ' AND products.shop_id NOT IN (' . implode( ',', $sql_ignore_shop ) . ')';
		}


		$total_rows = $wpdb->get_results( $wpdb->prepare( '
SELECT count(posts.id) as count, products.shop_id
FROM ' . $wpdb->prefix . 'posts posts 
left join ' . $wpdb->prefix . 'atkp_products products on products.product_id = posts.id ' . $sql_ignore_string . '
' . implode( ' ', $joins ) . '
WHERE ' . implode( ' and ', $where ) . ( $group_result ? '
GROUP BY posts.id' : '' ), $params ) );

		$total = is_array( $total_rows ) && count( $total_rows ) > 0 ? count( $total_rows ) : 0;

		if ( $total == 1 && is_array( $total_rows ) && count( $total_rows ) > 0 ) {
			$total = reset( $total_rows )->count;
		}


		$this->max_num_pages = ceil( $total / $itemsPerPage );
		$this->found_posts   = $total;


		$results = $wpdb->get_results( $wpdb->prepare( '
SELECT posts.id, products.shop_id
FROM ' . $wpdb->prefix . 'posts posts 
left join ' . $wpdb->prefix . 'atkp_products products on products.product_id = posts.id ' . $sql_ignore_string.'
' . implode( ' ', $joins ) . '
WHERE ' . implode( ' and ', $where ) . ( $group_result ? '
GROUP BY posts.id' : '' ) . '
 ' . ( count( $orderby ) > 0 ? 'ORDER BY ' . implode( ', ', $orderby ) : '' ) . "
LIMIT $offset, $itemsPerPage", $params ) );

		if ( $wpdb->last_error != '' ) {
			return '<span class="atkp-noproducts">' . sprintf( __( 'Error occurred on search: %s', ATKP_PLUGIN_PREFIX ), $wpdb->last_error ) . '</span>';
		}

		foreach ( $results as $result ) {

			$product = array();

			$product['type']    = 'productid';
			$product['value']   = $result->id;
			$product['shop_id'] = $result->shop_id;


			$productlist[] = $product;
		}


		if ( $productlist == null || count( $productlist ) == 0 ) {
			return '<span class="atkp-noproducts">' . __( 'No products found for these search criteria.', ATKP_PLUGIN_PREFIX ) . '</span>';
		} else {
			return $productlist;
		}
	}
}


