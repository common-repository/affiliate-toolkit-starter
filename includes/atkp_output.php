<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class atkp_output {
	public function __construct() {

	}

	public $ajax_mode = 'none';



	/**
	 * Erstellt die Ausgabe einer konfigurierten Liste und aufgrund der Parameter.
	 *
	 * @id int Die eindeutige ID der Liste im Wordpress-Custom-Posttype.
	 *
	 * @template string Entweder ein Standardtemplate (wide, box,...) oder die ID der Vorlage (Customposttype).
	 *
	 * @content string Ein benutzerdefinierter Text welcher bis zur Vorlage durchgeschleift wird.
	 *
	 * @return string Gibt das vollständige HTML zurück.
	 */
	public function get_list_output( $id, $template = 'wide', $content = '', $buttontype = 'notset', $elementcss = '', $containercss = '', $limit = 10, $randomsort = false, $hidedisclaimer = false, $tracking_id = '', $product_ids = array(), $offerstemplate = '', $imagetemplate = '', $parseparams = false, $itemsPerPage = 0, $filter = '' ) {

		$template = apply_filters( 'atkp_modify_template', $template );
		$template = apply_filters( 'atkp_modify_template_list', $template, $id, $content );

		//Ausgabe der Liste vorbereiten

		$outputprds = $this->get_list_products( $id, $limit, $randomsort, $product_ids, $parseparams, $itemsPerPage, $filter, $max_num_pages, $found_posts );


		return $this->get_list_output2( $outputprds, $id, $template, $content, $buttontype, $elementcss, $containercss, $hidedisclaimer, $tracking_id, $offerstemplate, $imagetemplate, $itemsPerPage, $max_num_pages, $found_posts );
	}

	public function get_list_products( $id, $limit = 10, $randomsort = false, $product_ids = array(), $parseparams = false, $itemsPerPage = 0, $filter = '', &$max_num_pages = 0, &$found_posts = 0 ) {
		if ( $itemsPerPage <= 0 ) {
			$itemsPerPage = 25;
		}

		if($limit<= 0){
			//wenn items per page mitgegeben wurde, dann wird das limit übersteuert
			$limit = 999;
		}

		$productlist   = array();
		$max_num_pages = 0;
		$found_posts   = 0;

		if ( $parseparams || $filter != '' ) {
			//TODO: parameter parsen und productids laden

			$filterhelper = new atkp_filter_helper();
			$productlist  = $filterhelper->parse_params_products( $itemsPerPage, $parseparams, $filter, $limit );

			$max_num_pages = $filterhelper->max_num_pages;
			$found_posts   = $filterhelper->found_posts;


			if($limit > 0 && $limit <= $itemsPerPage) {
				$max_num_pages = 1;
			}

			if ( $productlist == null || ! is_array( $productlist ) ) {
				return $productlist;
			}

		} else if ( count( $product_ids ) > 0 ) {
			foreach ( $product_ids as $productid ) {
				if ( is_array( $productid ) ) {
					$productlist[] = $productid;
				} else {
					$item          = array();
					$item['type']  = 'productid';
					$item['value'] = $productid;

					$productlist[] = $item;
				}
			}
		} else {
			$atkp_listtable_helper = new atkp_listtable_helper();
			$selectedshopid        = ATKPTools::get_post_setting( $id, ATKP_LIST_POSTTYPE . '_shopid' );
			$productlist           = $atkp_listtable_helper->load_list( $id, $selectedshopid );

			if ( $productlist == null || count( $productlist ) == 0 ) {
				$productlist = ATKPTools::get_post_setting( $id, ATKP_LIST_POSTTYPE . '_productlist' );
			}

		}

		$preferlocalproductinfo = ATKPTools::get_post_setting( $id, ATKP_LIST_POSTTYPE . '_preferlocalproduct' );

		$outputprds = array();
		if ( $productlist != null ) {

			if ( $randomsort ) {
				shuffle( $productlist );
			}

			$counter = 0;
			foreach ( $productlist as $product ) {
				try {
					$type    = $product['type'];
					$value   = $product['value'];
					$shop_id = $product['shop_id'] ?? '';
					$list_id = $product['list_id'] ?? $id;


					if ( $value == '' ) {
						continue;
					}

					if ( $counter >= $limit ) {
						break;
					}

					$counter = $counter + 1;

					switch ( $type ) {
						case 'product':
							//nur nach lokalen produkten suchen wenn in der
							if ( $preferlocalproductinfo ) {
								$prdfound = atkp_product::loadbyasin( $value->asin );

								if ( $prdfound != null ) {
									$prodcollection = atkp_product_collection::load( $prdfound->productid );
									if ( $prodcollection != null ) {
										$value         = $prodcollection->get_main_product();
										$value->listid = $list_id;
									} else {
										$value = null;
									}
								}
							} else if ( $value != null ) {
								$value->listid = $list_id;
							}

							break;
						case 'productid':

							if ( get_post_status( $value ) == 'publish' || get_post_status( $value ) == 'draft' ) {
								$prodcollection = atkp_product_collection::load( $value, $shop_id );
								if ( $prodcollection != null ) {
									$value         = $prodcollection->get_main_product( $shop_id );
									$value->listid = $list_id;
								} else {
									$value = null;
								}
							} else {
								$value = null;
							}

							break;
					}

					if ( $value != null ) {
						$shopid = $shop_id != '' ? $shop_id : ATKPTools::get_post_setting( $id, ATKP_LIST_POSTTYPE . '_shopid' );

						$value->init_list( $id, $shopid );

						$value->listid = $list_id;

						$outputprds[] = $value;
					}
				} catch ( Exception $e ) {
					//TODO: 'Exception: ',  $e->getMessage(), "\n";
				}
			}
		}

		return $outputprds;
	}

	public function get_list_output2( $productlist, $listid, $template = 'wide', $content = '', $buttontype = 'notset', $elementcss = '', $containercss = '', $hidedisclaimer = false, $tracking_id = '', $offerstemplate = '', $imagetemplate = '', $itemsPerPage = 0, $max_num_pages = 1, $found_posts = 0 ) {

		$templatehelper            = new atkp_template_helper();
		$templatehelper->ajax_mode = $this->ajax_mode;
		$resultValue               = $templatehelper->createOutput( $productlist, $content, $template, $containercss, $elementcss, $buttontype, $listid, $hidedisclaimer, 0, $tracking_id, $offerstemplate, $imagetemplate, $itemsPerPage, $max_num_pages, $found_posts );

		$resultValue = apply_filters( 'atkp_modify_output_list', $resultValue, $productlist, $template );

		return $resultValue;

	}


	/**
	 * @param atkp_template_helper $templatehelper
	 * @param atkp_product $product
	 * @param int|string $template
	 * @param bool $isavailable
	 *
	 * @return mixed|string
	 */
	private function override_notavailable_template($templatehelper, $product, $template, &$isavailable ) {
		$isavailable = true;
		if ( ! atkp_options::$loader->get_show_nota_template() ) {
			return $template;
		}
		$formatter  = new atkp_formatter( $templatehelper, null );

		$saleprice = $formatter->get_saleprice_value($product);

		//$saleprice = ATKPTools::get_post_setting( $product->productid, ATKP_PRODUCT_POSTTYPE . '_saleprice' );
		//$message   = ATKPTools::get_post_setting( $productid, ATKP_PRODUCT_POSTTYPE . '_message' );

		$temp = atkp_options::$loader->get_nota_template();

		if ( $saleprice <= 0) {
			$template    = $temp == '' ? 'notavailable' : $temp;
			$isavailable = false;
		}

		return $template;
	}


	public function get_product_output( $id, $template = 'box', $content = '', $buttontype = 'notset', $field = null, $link = false, $elementcss = '', $containercss = '', $hidedisclaimer = false, $tracking_id = '', $offerstemplate = '', $imagetemplate = '' ) {
		$isavailable               = true;
		$templatehelper            = new atkp_template_helper();
		$templatehelper->ajax_mode = $this->ajax_mode;


		$resultValue = '';
		if ( get_post_status( $id ) == 'trash' ) {
			throw new Exception( 'product is trashed' );
		}

		if ( ( get_post_status( $id ) != 'publish' && get_post_status( $id ) != 'draft' ) ) {
			if ( $content == '' ) {
				$content = __( 'Product', ATKP_PLUGIN_PREFIX );
			}

			return $link == true ? $content : '';
		}

		if ( ! $isavailable && atkp_options::$loader->get_nota_disable_link() && $link ) {
			return $content;
		}

		$products = atkp_product_collection::load( $id );

		if ( $products == null ) {
			return $content;
		}

		$prd = $products->get_main_product();

		$template = $this->override_notavailable_template( $templatehelper, $prd, $template, $isavailable );

		$template = apply_filters( 'atkp_modify_template', $template );
		$template = apply_filters( 'atkp_modify_template_product', $template, $id, $content );


		if ( $field != '' ) {
			$placeholders = $templatehelper->createPlaceholderArray( $prd, 1, $containercss, $elementcss, $content, $buttontype, '', '', $tracking_id );

			foreach ( array_keys( $placeholders ) as $key ) {
				if ( $key == $field ) {
					$resultValue = $placeholders[ $key ];
					break;
				}
			}


			if ( $containercss != '' ) {
				$resultValue = '<div class="' . esc_attr($containercss) . '">' . $resultValue . '</div>';
			}
		} else {

			$resultValue = $templatehelper->createOutput( array( $prd ), $content, $template, $containercss, $elementcss, $buttontype, '', $hidedisclaimer, 0, $tracking_id, $offerstemplate, $imagetemplate );

		}


		if ( $link == true ) {
			$placeholders = $templatehelper->createPlaceholderArray( $prd, 1, $containercss, $elementcss, $content, $buttontype, '', '', $tracking_id );

			$link = $placeholders['productlink'];

			if ( $field != '' ) {
				$content = $resultValue;
			} else if ( $content == '' ) {
				$content = $prd->title;
			}

			if ( atkp_options::$loader->get_mark_links() == 1 && strpos( $content, 'img src' ) == false ) {
				$content .= atkp_options::$loader->get_affiliatechar();
			}

			$link = apply_filters( 'atkp_modify_output_product_link', $link, $templatehelper, $prd, $tracking_id);

			if ( ATKPSettings::$access_mark_links == 1 && strpos( $content, 'img src' ) == true ) {
				$capt = __( 'Advertising', ATKP_PLUGIN_PREFIX );

				$resultValue = '<div class="atkp-link-image ' . $containercss . '"><div class="atkp-affiliateimage atkp-clearfix"><a ' . $link . ' >' . $content . '</a><div style="margin-top:3px">' . $capt . '</div></div></div>';
			} else {
				$resultValue = '<a class="atkp-link" ' . $link . ' >' . $content . '</a>';
			}
		}

		$resultValue = apply_filters( 'atkp_modify_output_product', $resultValue, $prd, $template );


		return $resultValue;
	}

	public function get_css_url() {
		return plugins_url( '/dist/style.css', ATKP_PLUGIN_FILE );
	}

	public function get_js_url() {
		return plugins_url( '/dist/script.js', ATKP_PLUGIN_FILE );
	}

	/**
	 * @param $parameters atkp_template_parameters|null
	 *
	 * @return string
	 */
	public function get_css_inline( $parameters = null ) {
		$custom_css = array();

		if ( $parameters == null ) {
			$parameters       = new atkp_template_parameters();
			$shortcode_params = array();
			$parameters->buildTemplateParameters( '', $shortcode_params );
		}

		$classprefix = '';
		if ( $parameters->templateid != '' && is_numeric( $parameters->templateid ) && $parameters->get_custom_styles() ) {
			$classprefix = ' .atkp-template-' . $parameters->templateid . ' ';
		}

		$custom_css[] = ( $classprefix == '' ? '.atkp-container' : $classprefix ) . ' a, ' . ( $classprefix == '' ? '.atkp-container' : $classprefix ) . ' a:visited { color: ' . $parameters->get_box_textlink_color() . '}';
		$custom_css[] = ( $classprefix == '' ? '.atkp-container' : $classprefix ) . ' a:hover { color: ' . $parameters->get_box_textlink_hovercolor() . '}';

		$custom_css[] = $classprefix . ' .atkp-moreoffersinfo a, ' . $classprefix . ' .atkp-moreoffersinfo a:visited { color: ' . $parameters->get_dropdown_textlink_color() . '} ' . $classprefix . ' .atkp-moreoffersinfo a:hover { color: ' . $parameters->get_dropdown_textlink_hovercolor() . ';}';

		$custom_css[] = $classprefix . '.atkp-listprice { color: ' . $parameters->get_listprice_color() . ' !important; } ';
		$custom_css[] = $classprefix . '.atkp-saleprice { color: ' . $parameters->get_price_color() . ' !important; } ';
		$custom_css[] = $classprefix . '.atkp-savedamount { color: ' . $parameters->get_amountsaved_color() . ' !important; } ';
		$custom_css[] = $classprefix . '.atkp-ribbon span { background: ' . $parameters->get_box_badge_color() . ' !important; } ';

		$atkp_box   = array();
		$atkp_box[] = 'background-color:' . $parameters->get_box_background_color() . ';';
		$atkp_box[] = 'border: 1px solid ' . $parameters->get_box_border_color() . ';';
		$atkp_box[] = 'border-radius: ' . $parameters->get_box_border_radius() . 'px;';
		$atkp_box[] = 'color: ' . $parameters->get_box_text_color();

		$custom_css[] = $classprefix . '.atkp-box { ' . implode( ' ', $atkp_box ) . ' }';

		$custom_css[] = $classprefix . '.atkp-box .atkp-predicate-highlight1, ' . $classprefix . '.atkp-box .atkp-predicate-highlight2, ' . $classprefix . '.atkp-box .atkp-predicate-highlight3 {border-radius: ' . $parameters->get_box_border_radius() . 'px ' . $parameters->get_box_border_radius() . 'px   0 0;}';


		if ( $parameters->get_box_show_shadow() ) {
			$custom_css[] = $classprefix . '.atkp-box { box-shadow: 5px 5px 10px 0 ' . $parameters->get_box_border_color() . '; }';
		}

		$padding  = 'padding: 5px 15px;font-size:14px;';
		$padding2 = 'padding: 5px 15px;';

		switch ( $parameters->get_primbtn_size() ) {
			case "normal":
				break;
			case "small":
				$padding = 'padding: 2px 10px;font-size:12px;';
				break;
			case "big":
				$padding = 'padding: 10px 15px;font-size:16px;';
				break;
			case "hide":
				$padding = 'display:none !important;';
				break;
		}

		switch ( $parameters->get_secbtn_size() ) {
			case "normal":
				break;
			case "small":
				$padding2 = 'padding: 2px 10px;font-size:12px;';
				break;
			case "big":
				$padding2 = 'padding: 10px 15px;font-size:16px;';
				break;
			case "hide":
				$padding2 = 'display:none !important;';
				break;
		}

		//$shadow = 'box-shadow: 0 1px 2px rgb(0 0 0 / 30%), inset 0 0 40px rgb(0 0 0 / 10%);';

		$custom_css[] = $classprefix . '.atkp-button {
							    margin: 0 auto;
							    ' . $padding . '
							    display: inline-block;
							    background-color: ' . $parameters->get_primbtn_background_color() . ';
							    border: 1px solid ' . $parameters->get_primbtn_border_color() . ';
							    color: ' . $parameters->get_primbtn_foreground_color() . ' !important;
							    font-weight: 400;
							    -webkit-border-radius: ' . $parameters->get_btn_radius() . 'px;
							    border-radius: ' . $parameters->get_btn_radius() . 'px;
							    -webkit-transition: all 0.3s ease-in-out;
							    -moz-transition: all 0.3s ease-in-out;
							    transition: all 0.3s ease-in-out;
							    text-decoration: none !important;							
							}
							
							' . $classprefix . ' .atkp-button:hover {
							    background-color: ' . $parameters->get_primbtn_hoverbackground_color() . ';
							    text-decoration: none;
							}';

		$custom_css[] = $classprefix . '.atkp-secondbutton {
							    margin: 0 auto;
							    ' . $padding2 . '
							    display: inline-block;
							    background-color: ' . $parameters->get_secbtn_background_color() . ';
							    border: 1px solid ' . $parameters->get_secbtn_border_color() . ';
							    color: ' . $parameters->get_secbtn_foreground_color() . ' !important;
							    font-weight: 400;
							    -webkit-border-radius: ' . $parameters->get_btn_radius() . 'px;
							    border-radius: ' . $parameters->get_btn_radius() . 'px;
							    -webkit-transition: all 0.3s ease-in-out;
							    -moz-transition: all 0.3s ease-in-out;
							    transition: all 0.3s ease-in-out;
							    text-decoration: none !important;							
							}
							
							' . $classprefix . ' .atkp-secondbutton:hover {
							    background-color: ' . $parameters->get_secbtn_hoverbackground_color() . ';
							    text-decoration: none;
							}';

		$btn_image_url = '';
		switch ( $parameters->get_primbtn_image() ) {
			case 'no_image':
				break;
			case 'amz_black':
				$btn_image_url = plugins_url( '/images/icon-amazon-black.svg', ATKP_PLUGIN_FILE );
				break;
			case 'amz_white':
				$btn_image_url = plugins_url( '/images/icon-amazon-white.svg', ATKP_PLUGIN_FILE );
				break;
			case 'cart_black':
				$btn_image_url = plugins_url( '/images/icon-cart-black.svg', ATKP_PLUGIN_FILE );
				break;
			case 'cart_white':
				$btn_image_url = plugins_url( '/images/icon-cart-white.svg', ATKP_PLUGIN_FILE );
				break;
		}

		if ( $btn_image_url != '' ) {
			$custom_css[] = $classprefix . '.atkp-button {
								    padding-left: 32px;
								    position: relative;
								}';
			$custom_css[] = $classprefix . '.atkp-button:before {
								    background-position: 9px;
									background-repeat: no-repeat;
									background-size: 14px 14px;
									bottom: 0;
									content: "";
									left: 0;
									position: absolute;
									right: 0;
									top: 0;
								    background-image: url(' . $btn_image_url . ');
								} ';

		}

		$secbtn_image_url = '';
		switch ( $parameters->get_secbtn_image() ) {
			case 'no_image':
				break;
			case 'amz_black':
				$secbtn_image_url = plugins_url( '/images/icon-amazon-black.svg', ATKP_PLUGIN_FILE );
				break;
			case 'amz_white':
				$secbtn_image_url = plugins_url( '/images/icon-amazon-white.svg', ATKP_PLUGIN_FILE );
				break;
			case 'cart_black':
				$secbtn_image_url = plugins_url( '/images/icon-cart-black.svg', ATKP_PLUGIN_FILE );
				break;
			case 'cart_white':
				$secbtn_image_url = plugins_url( '/images/icon-cart-white.svg', ATKP_PLUGIN_FILE );
				break;
		}

		if ( $secbtn_image_url != '' ) {
			$custom_css[] = $classprefix . '.atkp-secondbutton {
								    padding-left: 32px;
								    position: relative;
								}';
			$custom_css[] = $classprefix . '.atkp-secondbutton:before {
								    background-position: 9px;
									background-repeat: no-repeat;
									background-size: 14px 14px;
									bottom: 0;
									content: "";
									left: 0;
									position: absolute;
									right: 0;
									top: 0;
								    background-image: url(' . $secbtn_image_url . ');
								} ';
		}

		$custom_css[] = $classprefix . ' .atkp-producttable-button a {
								    width: 100%;
								    padding-left: 0px;
								    padding-right: 0px;
								}';

		$custom_css = apply_filters( 'atkp_custom_css_inline', $custom_css );

		return implode( "\r\n", $custom_css );
	}

	public function get_css_output() {

		$custom_css = array();

		//generate dynamic button styles

		$parameters = new atkp_template_parameters();
		$parameters->buildTemplateParameters( '', array() );

		if ( atkp_options::$loader->get_css_inline() == atkp_css_type::CssFile || atkp_options::$loader->get_css_inline() == atkp_css_type::InlineHead ) {
			$custom_css[] .= '/* Begin (global) */' . "\r\n" . $this->get_css_inline( $parameters ) . "\r\n" . '/* End (global) */' . "\r\n";
		}

		//load css from own templates

		$args        = array(
			'post_type'      => ATKP_TEMPLATE_POSTTYPE,
			'posts_per_page' => 300,
			'post_status'    => array( 'publish', 'draft' )
		);
		$posts_array = get_posts( $args );

		foreach ( $posts_array as $prd ) {
			$template_css = '';
			$parameters   = new atkp_template_parameters();
			$parameters->buildTemplateParameters( $prd->ID, array() );


			if ( atkp_options::$loader->get_css_inline() == atkp_css_type::CssFile || atkp_options::$loader->get_css_inline() == atkp_css_type::InlineHead ) {
				$ownstyles = ATKPTools::get_post_setting( $prd->ID, ATKP_TEMPLATE_POSTTYPE . '_custom_styles' );
				if ( $ownstyles ) {
					$template_css .= $this->get_css_inline( $parameters ) . "\r\n";
				}
			}

			$css = ATKPTools::get_post_setting( $prd->ID, ATKP_TEMPLATE_POSTTYPE . '_css' );

			if ( $css != '' ) {
				$template_css .= $css . "\r\n";
			}

			if ( $template_css != '' ) {
				$custom_css[] .= '/* Begin (#' . $prd->ID . ') */' . "\r\n" . $template_css . '/* End (#' . $prd->ID . ') */' . "\r\n";
			}
		}

		$custom_css = apply_filters( 'atkp_custom_css_output', $custom_css );

		return implode( "\r\n", $custom_css);
	}

	public function get_js_output() {

		$custom_script = array();

		$custom_script[] = '
function atkp_open_link(link, mode, trackingtype, linktype, linkcaption) {

    if (trackingtype == 1 && typeof ga !== "undefined")
        ga("send", "event", linktype, "click", linkcaption);
    else if (trackingtype == 2 && typeof _gaq !== "undefined")
        _gaq.push(["_trackEvent", linktype, "click", linkcaption]);
    else if (trackingtype == 3 && typeof gtag !== "undefined")
        gtag("event", linktype, {"event_category": "click", "event_label": linkcaption});
    else if (trackingtype == 4 && typeof _paq !== "undefined")
        _paq.push(["_trackEvent", "Clicks",linktype, linkcaption]);

    window.open(link, mode);

}';

		$custom_script = apply_filters( 'atkp_custom_script_output', $custom_script );


		return implode( "\r\n", $custom_script);
	}

}


?>