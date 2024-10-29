<?php
/**
 * Created by PhpStorm.
 * User: Christof
 * Date: 01.12.2018
 * Time: 11:35
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class ATKPTools {

	public static function acf_exists() {
		return function_exists( 'get_field_objects' );
	}

	public static function is_lang_de() {
		return ( strpos( get_bloginfo( 'language' ), 'de-' ) !== false ) ? true : false;
	}

	/**
	 * Credits link
	 *
	 * @return string
	 */
	public static function get_credits_link() {

		$url = ATKPTools::is_lang_de() ? 'https://www.affiliate-toolkit.com/de/' : 'https://www.affiliate-toolkit.com/';
		$url .= "?utm_medium=credits&utm_content=Footer+Link&utm_source=WordPress&utm_campaign=starterpass";
		$rel = '';

		if ( atkp_options::$loader->get_credits_ref() != '' ) {
			$rel = 'rel="nofollow"';
			$url .= '?ref=' . atkp_options::$loader->get_credits_ref();
		}

		if ( ATKPTools::is_lang_de() ) {
			return sprintf( __( 'Die Anzeige der Produkte wurde mit dem <a href="%s" %s target="_blank" title="Affiliate WordPress Plugin">affiliate-toolkit</a> <a href="https://servit.dev/?utm_medium=credits&utm_content=Footer+Link&utm_source=WordPress&utm_campaign=starterpass" target="_blank" title="WordPress Webentwicklung">WordPress Plugin</a> umgesetzt.', ATKP_PLUGIN_PREFIX ), esc_url( $url ), esc_attr( $rel ) );
		} else {
			return sprintf( __( 'The display of the products was implemented with the <a href="%s" %s target="_blank" title="Affiliate WordPress Plugin">affiliate-toolkit</a> plugin.', ATKP_PLUGIN_PREFIX ), esc_url( $url ), esc_attr( $rel ) );
		}
	}

	public static function get_acf_fields( $post_type ) {
		$fieldarray = array();
		if ( function_exists( 'get_field_objects' ) ) {
			$groups = acf_get_field_groups( array( 'post_type' => $post_type ) );

			foreach ( $groups as $group ) {
				$fields = acf_get_fields( $group['key'] );
				foreach ( $fields as $field ) {

					$label               = $field["label"];
					$name                = $field["name"];
					$fieldarray[ $name ] = $label;
				}
			}
		}

		return $fieldarray;
	}

	/**
	 * @param string $type
	 * @param string $title
	 * @param bool $iswpcronjob
	 * @param array $list
	 * @param string $status
	 *
	 * @return atkp_queue mixed
	 */
	public static function create_queue( $type, $title, $iswpcronjob, $list, $status ) {
		$atkp_queue         = new atkp_queue();
		$atkp_queue->status = $status; //atkp_queue_status::ACTIVE;
		$atkp_queue->type   = $type;

		if ( $title != '' ) {
			$atkp_queue->title = $title;
		} else {
			if ( $iswpcronjob ) {
				$atkp_queue->title = __( 'WordPress Cronjob', ATKP_PLUGIN_PREFIX );
			} else if ( class_exists( 'WP_CLI' ) ) {
				$atkp_queue->title = __( 'WordPress CLI Cronjob', ATKP_PLUGIN_PREFIX );
			} else {
				$atkp_queue->title = __( 'affiliate-toolkit Cronjob', ATKP_PLUGIN_PREFIX );
			}
		}

		$atkp_queue->entries = $list;

		$atkp_queuetable_helper = new atkp_queuetable_helper();

		return $atkp_queuetable_helper->save_queue( $atkp_queue );
	}

	public static function get_string_between( $string, $start, $end ) {
		$string = ' ' . $string;
		$ini    = strpos( $string, $start );
		if ( $ini == 0 ) {
			return '';
		}
		$ini += strlen( $start );
		$len = strpos( $string, $end, $ini ) - $ini;

		return substr( $string, $ini, $len );
	}

	public static function validate_ean( $ean ) {
		return BarcodeValidator::IsValidEAN8( $ean ) || BarcodeValidator::IsValidEAN13( $ean ) || BarcodeValidator::IsValidEAN14( $ean );
	}

	public static function validate_isbn( $isbn ) {
		return BarcodeValidator::IsValidISBN( $isbn );
	}

	public static function validate_gtin( $gtin ): bool {
		return BarcodeValidator::IsValidEAN8( $gtin ) || BarcodeValidator::IsValidEAN13( $gtin ) || BarcodeValidator::IsValidEAN14( $gtin );
	}


	public static function display_helptext( $text, $url = '', $urltitle = 'Read more' ) {
		if ( $url != '' ) {
			$text .= ' <a href="' . esc_url($url) . '" target="_blank">' . esc_html__( $urltitle, ATKP_PLUGIN_PREFIX ) . '</a>';
		}
		$allowed_html = array(
			'div' => array(
				'class' => array(),
				'style' => array(),
			),
			'span' => array(
				'class' => array(),
				'style' => array(),
			),
			'a' => array(
				'href' 	=> array(),
				'target' => array(),
				'title' => array(),
			)
		);

		echo wp_kses( __('<div class="atkp-helptext" style="margin: 5px;font-size: 11px;display:table;"><span class="dashicons dashicons-editor-help" style="color:#2271b1;display:table-cell;"></span><span style="vertical-align: middle;display:table-cell;padding-left:5px;">' . $text . '</span></div>', ATKP_PLUGIN_PREFIX ), $allowed_html );
	}


	public static function display_warntext( $text, $url = '', $urltitle = 'Read more' ) {
		if ( $url != '' ) {
			$text .= ' <a href="' . $url . '" target="_blank">' . $urltitle . '</a>';
		}
		$allowed_html = array(
			'div' => array(
				'class' => array(),
				'style' => array(),
			),
			'span' => array(
				'class' => array(),
				'style' => array(),
			),
			'a' => array(
				'href' 	=> array(),
				'target' => array(),
				'title' => array(),
			)
		);


		echo wp_kses( __('<div class="atkp-helptext" style="margin: 5px;font-size: 11px;display:table;"><span class="dashicons dashicons-info" style="color:orangered;display:table-cell;"></span> <span style="vertical-align: middle;display:table-cell;padding-left:5px;">' . $text . '</span></div>', ATKP_PLUGIN_PREFIX ), $allowed_html );
	}


	public static function check_sslurl( $url ) {
		if ( is_ssl() && ATKPTools::startsWith( $url, 'http://' ) && ! ATKPTools::startsWith( $url, 'https://' ) ) {
			$url = 'https://' . substr( $url, 7 );
		}

		return $url;
	}

	public static function get_fieldgroups() {

		$args = array(
			'posts_per_page' => 100,
			'post_type'      => array( ATKP_FIELDGROUP_POSTTYPE ),
			'post_status'    => array( 'publish', 'draft' ),
		);

		$groups = get_posts( $args );

		return $groups;
	}

	public static function get_fieldgroups_with_taxonomy() {

		$args = array(
			'posts_per_page' => 100,
			'post_type'      => array( ATKP_FIELDGROUP_POSTTYPE ),
			'post_status'    => array( 'publish', 'draft' ),
			'meta_query'     => array(
				'key'     => ATKP_FIELDGROUP_POSTTYPE . '_hastaxonomy',
				'value'   => 1,
				'compare' => '=',
				'type'    => 'NUMERIC'
			)
		);

		$groups = get_posts( $args );

		return $groups;
	}

	public static function get_fieldgroups_by_productid( $postId ) {

		$category       = get_option( ATKP_PLUGIN_PREFIX . '_product_category_taxonomy', strtolower( __( 'Productcategory', ATKP_PLUGIN_PREFIX ) ) );
		$categoryvalues = array();

		//$terms = get_the_terms( $postId, $category );
		$categoryvalues = wp_get_post_terms( intval( $postId ), $category, array( 'fields' => 'ids' ) );

		if ( $categoryvalues == null || ! $categoryvalues || ! is_array( $categoryvalues ) ) {
			return array();
		}

		$args = array(
			'posts_per_page'   => 25,
			'post_type'        => array( ATKP_FIELDGROUP_POSTTYPE ),
			'post_status'      => array( 'publish', 'draft' ),
			'include_children' => true,
			'tax_query'        => array(
				array(
					'taxonomy' => $category, //or tag or custom taxonomy
					'field'    => 'term_id',
					'terms'    => $categoryvalues,
					'operator' => 'IN',
				)
			)
		);

		$groups = get_posts( $args );
		//var_dump($categoryvalues);
		//var_dump($groups);exit;


		return $groups;
	}

	public static function has_subshops( $shoptype ) {
		$shoptypes   = array();
		$shoptypes[] = '2';
		$shoptypes[] = '3';
		$shoptypes[] = '5';
		$shoptypes[] = '8';
		$shoptypes[] = '9';

		$shoptypes[] = '10';
		$shoptypes[] = '13';
		$shoptypes[] = '14';
		$shoptypes[] = '15';
		$shoptypes   = apply_filters( 'atkp_subshop_supported', $shoptypes );


		return in_array( $shoptype, $shoptypes);
	}

	public static function has_eanpricecompare( $shoptype ) {
		$shoptypes = array();
		$shoptypes[] = '8';

		$shoptypes = apply_filters( 'atkp_subshop_ean_notsupported', $shoptypes );

		return !in_array($shoptype, $shoptypes);
	}

	public static function has_articlenumbersearch( $shoptype ) {
		$shoptypes = array();
		$shoptypes[] = '1';
		$shoptypes[] = '2';
		$shoptypes[] = '4';
		$shoptypes[] = '10';
		$shoptypes[] = '13';

		$shoptypes = apply_filters( 'atkp_subshop_articlenumber_supported', $shoptypes );


		return in_array($shoptype, $shoptypes);
	}

	public static function create_list( $title, $shopid, $listtype, $searchterm, $department = '', $sortby = '', $loadmoreoffers = false ) {
		//throw new exception($listtype);

		if ( $title == '' ) {
			$title = $listtype . '-' . $searchterm . ( $department != '' ? '-' . $department : '' );
		}

		global $user_ID;
		$new_post = array(
			'post_title'  => $title,
			'post_status' => 'publish',
			'post_author' => $user_ID,
			'post_type'   => ATKP_LIST_POSTTYPE,
		);
		$post_id  = wp_insert_post( $new_post );

		ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_shopid', $shopid );
		ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_source', $listtype );


		ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_preferlocalproduct', true );
		ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_loadmoreoffers', $loadmoreoffers );
		ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_search_department', $department == '' ? 'All' : $department );
		if ( $listtype == '10' || $listtype == '11' ) {
			ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_node_id', $searchterm );
			ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_search_limit', 10 );

		} else {
			ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_search_keyword', $searchterm );
			ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_search_limit', 20 );
		}

		if ( $sortby != '' ) {
			ATKPTools::set_post_setting( $post_id, ATKP_LIST_POSTTYPE . '_search_orderby', $sortby );
		}

		return $post_id;
	}

	public static function get_current_utc() {
		$script_at = date_default_timezone_get();

		date_default_timezone_set( "UTC" );
		$time = time();
		date_default_timezone_set( $script_at );

		return $time;
	}


	public static function get_formatted_date( $updatedon ) {
		if ( $updatedon == '' ) {
			return '';
		}

		$localtime = date_i18n( get_option( 'date_format' ), $updatedon );
		//$localtime = get_date_from_gmt( $utctime, 'Y-m-d H:i:s' );

		//$localtime = $utctime; //date_i18n(get_option( 'date_format' ), $utctime);

		return $localtime;
	}

	public static function get_formatted_time( $updatedon ) {
		if ( $updatedon == '' ) {
			return '';
		}

		$utctime   = date_i18n( 'Y-m-d H:i:s', $updatedon, true );
		$localtime = get_date_from_gmt( $utctime, get_option( 'time_format' ) );


		return $localtime;
	}

	private static $iscreating = false;

	public static function publish_product( $post_id, $posttitle = '' ) {

		$query = array(
			'ID'          => $post_id,
			'post_status' => 'publish'
		);
		wp_update_post( $query, true );

		if ( $posttitle != '' ) {
			global $wpdb;

			$wpdb->update( $wpdb->posts, array(
				'post_title' => $posttitle,
				'post_name'  => sanitize_title( $posttitle )
			), array( 'ID' => $post_id ) );
		}
	}

	public static function create_product( $title, $shopid, $asin, $status, $asintype = 'ASIN', $subshopid = '' ) {
		if ( self::$iscreating ) {
			return null;
		}
		self::$iscreating = true;

		$defaultproductstate = atkp_options::$loader->get_defaultproductstate(); // get_option( ATKP_PLUGIN_PREFIX . '_defaultproductstate', 'draft' );

		if ( $status == null || $status == '' ) {
			$status = $defaultproductstate;
		}

		$title = str_replace( "\'", "", $title );
		//25.06.2018 -> $status == 'publish' ? 'publish' :  veröffentlich wird erst am ende
		global $user_ID;
		$new_post = array(
			'post_title'  => $title,
			'post_status' => $status == 'publish' ? 'publish' : 'draft',
			'post_author' => $user_ID,
			'post_type'   => ATKP_PRODUCT_POSTTYPE,
		);


		$post_id          = wp_insert_post( $new_post );
		self::$iscreating = false;

		if ( $status == 'woo' ) {
			ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_iswoocommerce', 1 );
		}

		ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_shopid', $shopid );

		ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_init_subshopid', $subshopid );

		ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_refreshmoreoffersregulary', 1 );

		if ( $shopid == '' ) {
			ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_asin', $asin );
			ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_asintype', $asintype );

			if ( $asintype == 'EAN' ) {
				ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_ean', $asin );
				ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_refreshmoreoffersregulary', 1 );
			}

		} else {
			ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_asin', $asin );
			ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_asintype', $asintype );
			ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_refreshreviewinforegulary', 1 );
			ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_refreshpriceinforegulary', 1 );
			ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_refreshproducturlregulary', 1 );
			ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_refreshimagesregulary', 1 );
			ATKPTools::set_post_setting( $post_id, ATKP_PRODUCT_POSTTYPE . '_refreshmoreoffersregulary', 1 );
		}

		return $post_id;
	}

	public static function delete_image_attachment( $attach_id ) {
		//wenn thumbnail vorhanden dann nicht setzen
		if ( $attach_id != '' ) {
			delete_post_thumbnail( $attach_id );
			wp_delete_attachment( $attach_id, false );
		}
	}

	public static function upload_image( $image_url, $image_name, $post_id, $idx = 1 ) {
		if ( ATKPLog::$logenabled ) {
			ATKPLog::LogDebug( '*** upload_image started (' . $image_url . ' / ' . $image_name . ' / ' . $post_id . ') ***' );
		}

		if ( ! function_exists( 'file_get_contents' ) ) {
			if ( ATKPLog::$logenabled ) {
				ATKPLog::LogDebug( 'function file_get_contents not exists' );
			}

			return false;
		}

		// Add Featured Image to Post
		$upload_dir = wp_upload_dir(); // Set upload folder

		$context = stream_context_create(
			array(
				'http' => array(
					'method' => "GET",
					'header' => "Accept-language: en\r\n" .
					            "Cookie: foo=bar\r\n" .  // check function.stream-context-create on php.net
					            "User-Agent: Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.102011-10-16 20:23:10\r\n"
					// i.e. An iPad
				)
			)
		);

		$image_data = @file_get_contents( $image_url, false, null ); // Get image data

		//wenn fehler beim lesen auftritt, wird false zurück gegeben
		if ( ! $image_data ) {
			$error = error_get_last();

			if ( ATKPLog::$logenabled ) {
				ATKPLog::LogDebug( '$image_data is empty: ' . $image_url );
			}

			ATKPLog::LogError( "HTTP request failed. Error was: " . $error['message'] );

			return false;
		}


		//find attachmentid
		$args = array(
			'post_status' => 'inherit',
			'post_type'   => 'attachment',
			'meta_query'  => array(
				array(
					'key'     => ATKP_PLUGIN_PREFIX . '_key',
					'compare' => '=',
					'value'   => $post_id . '-' . $idx
				)
			)
		);

		$attach_query = new WP_Query( $args );

		$posts_array = $attach_query->posts;

		if ( ATKPLog::$logenabled ) {
			ATKPLog::LogDebug( 'attachment found: ' . serialize( $posts_array ) );
		}

		if ( isset( $posts_array ) && count( $posts_array ) > 0 ) {
			$attachmentid = $posts_array[0]->ID;

			$theurl = wp_get_attachment_image_src( $attachmentid, 'full' );
		}

		$basedir = $upload_dir['basedir'];
		$baseurl = $upload_dir['baseurl'];

		if ( ATKPLog::$logenabled ) {
			ATKPLog::LogDebug( '$basedir: ' . serialize( $basedir ) );
			ATKPLog::LogDebug( '$baseurl: ' . serialize( $baseurl ) );
		}

		if ( isset( $theurl ) && $theurl ) {

			$file     = $basedir . str_replace( $baseurl, '', $theurl[0] );
			$filename = $posts_array[0]->post_name;

		} else {
			$ext = substr( strrchr( $image_url, '.' ), 1 );
			//dateiendung hat nur 3 stellen
			$ext = strlen( $ext ) <= 3 ? $ext : '';

			if ( $ext == '' || $ext == ' ' ) {
				$ext = 'jpg';
			}

			$image_name = strlen( $image_name ) > 30 ? substr( $image_name, 0, 30 ) : $image_name;

			$unique_file_name = sanitize_file_name( $post_id . '-' . $idx . '-' . sanitize_title( $image_name ) . '.' . $ext );
			//wp_unique_filename( $upload_dir['path'], $image_name.'.'.$ext ); // Generate unique name

			$filename = strtolower( basename( $unique_file_name ) ); // Create image file name
			$basedir  = '';

			// Check folder permission and define file location
			if ( wp_mkdir_p( $upload_dir['path'] ) ) {
				$basedir = $upload_dir['path'];
			} else {
				$basedir = $upload_dir['basedir'];
			}

			//$basedir = wp_upload_dir('2017/01')['basedir']. '/product-image';
			//wp_mkdir_p($basedir);

			$file = $basedir . '/' . $filename;


			$file = urldecode( $file );
		}


		/* Restore original Post Data */
		wp_reset_postdata();

		if ( ATKPLog::$logenabled ) {
			ATKPLog::LogDebug( '$file: ' . serialize( $file ) );
			ATKPLog::LogDebug( '$filename: ' . serialize( $filename ) );
		}


		// Create the image  file on the server
		if ( ! file_put_contents( $file, $image_data ) ) {
			if ( ATKPLog::$logenabled ) {
				ATKPLog::LogDebug( 'image cannot be saved: ' . $file );
			}

			return false;
		}


		if ( ATKPLog::$logenabled ) {
			ATKPLog::LogDebug( 'file saved: ' . $file );
		}

		//find attachmentid
		//$args = array(
		//    'post_per_page' => 1,
		//    'post_type'     => 'attachment',
		//    'name'          => $filename,
		//);
		//$posts_array = get_posts( $args );

		// Check image file type
		$wp_filetype = wp_check_filetype( $filename, null );

		if ( isset( $posts_array ) && count( $posts_array ) > 0 ) {
			$attach_id = $posts_array[0]->ID;

			$data = array(
				'post_mime_type' => $wp_filetype['type'],
				'post_title'     => $filename,
				'post_status'    => 'inherit'
			);

			//wp_update_attachment_metadata( $attach_id, $data );

			if ( ATKPLog::$logenabled ) {
				ATKPLog::LogDebug( '*** existing attachment updated: ' . $attach_id . '' );
				ATKPLog::LogDebug( serialize( $data ) );
			}


		} else {
			// Set attachment data
			$attachment = array(
				'post_mime_type' => $wp_filetype['type'],
				'post_title'     => $filename,
				'post_status'    => 'inherit'
			);

			// Create the attachment
			$attach_id = wp_insert_attachment( $attachment, $file, $post_id );

			if ( ATKPLog::$logenabled ) {
				ATKPLog::LogDebug( '*** attachment created: ' . $attach_id . '' );
				ATKPLog::LogDebug( serialize( $attachment ) );
			}

		}

		// Include image.php
		require_once( ABSPATH . 'wp-admin/includes/image.php' );


		// Assign metadata to attachment
		wp_update_attachment_metadata( $attach_id, wp_generate_attachment_metadata( $attach_id, $file ) );
		//set atkp key
		ATKPTools::set_post_setting( $attach_id, ATKP_PLUGIN_PREFIX . '_key', $post_id . '-' . $idx );


		if ( ATKPLog::$logenabled ) {
			ATKPLog::LogDebug( '*** upload_image finished (' . $attach_id . ') ***' );
		}

		return $attach_id;
	}

	public static function set_featured_image( $image_url, $image_name, $post_id ) {
		$imgurl = ATKPTools::get_post_setting( $post_id, ATKP_PLUGIN_PREFIX . '_current_imageurl');

		$thumbnail = get_post_thumbnail_id( $post_id );

		//wenn bildurl gleich ist und thumbnail vorhanden, dann ist ein update nicht erforderlich
		if($imgurl == $image_url && $thumbnail != '')
			return;


		$attach_id = ATKPTools::upload_image( $image_url, $image_name, $post_id, 1 );

		//lösche das alte thumbnail wenn der upload nicht erfolgreich war
		if ( $attach_id == false ) {
			//ATKPTools::delete_image_attachment($thumbnail);
			if ( has_post_thumbnail( $post_id ) ) {
				$attachment_id = get_post_thumbnail_id( $post_id );
				if ( $attachment_id ) {
					wp_delete_attachment( $attachment_id, true );
				}
			}
		}

		if ( ATKPLog::$logenabled ) {
			ATKPLog::LogDebug( 'existing thumbnailid: ' . $thumbnail . ', new thumbnailid: ' . $attach_id );
		}

		// And finally assign featured image to post
		set_post_thumbnail( $post_id, $attach_id );
		ATKPTools::set_post_setting( $post_id, ATKP_PLUGIN_PREFIX . '_current_imageurl', $image_url);
	}



	public static function exists_cron_job( $hookParam = ATKP_EVENT, $output = false ) {
		//$cron = _get_cron_array();
		//$hook = wp_get_schedule( $hookParam );

		$crons  = _get_cron_array();
		$events = array();

		if ( $crons ) {
			foreach ( $crons as $time => $cron ) {
				foreach ( $cron as $hook => $dings ) {
					foreach ( $dings as $sig => $data ) {
						if ( $hook == $hookParam ) {

							if ( $data['schedule'] ) {

								if ( $output ) {
									$localtime = get_date_from_gmt( date( 'Y-m-d H:i:s', $time ), get_option( 'time_format' ) );
									$localdate = get_date_from_gmt( date( 'Y-m-d H:i:s', $time ), get_option( 'date_format' ) );

									$text = '';
									$text .= ' ' . sprintf( esc_html__( 'Cronjob\'s next execution: %s %s', ATKP_PLUGIN_PREFIX ), $localdate, $localtime );
									$text .= ' (' . self::time_since( time(), $time ) . ')';
									$text .= ' Interval: ' . self::interval( isset( $data['interval'] ) ? $data['interval'] : null );

									echo esc_html($text);
								}

								return true;
							}
						}
					}
				}
			}
		}

		if ( $output ) {
			echo esc_html__( 'cronjob is not running! Click the "Save Changes" button.', ATKP_PLUGIN_PREFIX );
		}

		return false;
	}

	public static function time_since( $older_date, $newer_date ) {
		return self::interval( $newer_date - $older_date );
	}

	public static function interval( $since ) {
		__( '%s years', ATKP_PLUGIN_PREFIX );
		__( '%s months', ATKP_PLUGIN_PREFIX );
		__( '%s weeks', ATKP_PLUGIN_PREFIX );
		__( '%s days', ATKP_PLUGIN_PREFIX );
		__( '%s hours', ATKP_PLUGIN_PREFIX );
		__( '%s minutes', ATKP_PLUGIN_PREFIX );
		__( '%s seconds', ATKP_PLUGIN_PREFIX );

		// array of time period chunks
		$chunks = array(
			array( 60 * 60 * 24 * 365, _n_noop( '%s year', '%s years', ATKP_PLUGIN_PREFIX ) ),
			array( 60 * 60 * 24 * 30, _n_noop( '%s month', '%s months', ATKP_PLUGIN_PREFIX ) ),
			array( 60 * 60 * 24 * 7, _n_noop( '%s week', '%s weeks', ATKP_PLUGIN_PREFIX ) ),
			array( 60 * 60 * 24, _n_noop( '%s day', '%s days', ATKP_PLUGIN_PREFIX ) ),
			array( 60 * 60, _n_noop( '%s hour', '%s hours', ATKP_PLUGIN_PREFIX ) ),
			array( 60, _n_noop( '%s minute', '%s minutes', ATKP_PLUGIN_PREFIX ) ),
			array( 1, _n_noop( '%s second', '%s seconds', ATKP_PLUGIN_PREFIX ) ),
		);

		if ( $since <= 0 ) {
			return __( 'now', ATKP_PLUGIN_PREFIX );
		}

		// we only want to output two chunks of time here, eg:
		// x years, xx months
		// x days, xx hours
		// so there's only two bits of calculation below:

		// step one: the first chunk
		for ( $i = 0, $j = count( $chunks ); $i < $j; $i ++ ) {
			$seconds = $chunks[ $i ][0];
			$name    = $chunks[ $i ][1];

			// finding the biggest chunk (if the chunk fits, break)
			if ( ( $count = floor( $since / $seconds ) ) != 0 ) {
				break;
			}
		}

		// set output var
		$output = sprintf( translate_nooped_plural( $name, $count, ATKP_PLUGIN_PREFIX ), $count );

		// step two: the second chunk
		if ( $i + 1 < $j ) {
			$seconds2 = $chunks[ $i + 1 ][0];
			$name2    = $chunks[ $i + 1 ][1];

			if ( ( $count2 = floor( ( $since - ( $seconds * $count ) ) / $seconds2 ) ) != 0 ) {
				// add to output var
				$output .= ' ' . sprintf( translate_nooped_plural( $name2, $count2, ATKP_PLUGIN_PREFIX ), $count2 );
			}
		}

		return $output;
	}


	public static function mix_colors( $basecolor, $mixcolor, $ratio, $addHash = true ) {
		if ( $basecolor == '' || $basecolor == null ) {
			return $basecolor;
		}


		$baseComponentOffset = strlen( $basecolor ) == 7 ? 1 : 0;
		$baseComponentRed    = hexdec( substr( $basecolor, $baseComponentOffset, 2 ) );
		$baseComponentGreen  = hexdec( substr( $basecolor, $baseComponentOffset + 2, 2 ) );
		$baseComponentBlue   = hexdec( substr( $basecolor, $baseComponentOffset + 4, 2 ) );

		$mixComponentOffset = strlen( $mixcolor ) == 7 ? 1 : 0;
		$mixComponentRed    = hexdec( substr( $mixcolor, $mixComponentOffset, 2 ) );
		$mixComponentGreen  = hexdec( substr( $mixcolor, $mixComponentOffset + 2, 2 ) );
		$mixComponentBlue   = hexdec( substr( $mixcolor, $mixComponentOffset + 4, 2 ) );

		$Rsum = $baseComponentRed + $mixComponentRed;
		$Gsum = $baseComponentGreen + $mixComponentGreen;
		$Bsum = $baseComponentBlue + $mixComponentBlue;

		$R = ( $baseComponentRed * ( 100 - $ratio ) + $mixComponentRed * $ratio ) / 100;
		$G = ( $baseComponentGreen * ( 100 - $ratio ) + $mixComponentGreen * $ratio ) / 100;
		$B = ( $baseComponentBlue * ( 100 - $ratio ) + $mixComponentBlue * $ratio ) / 100;

		$redPercentage   = max( $R, $G, $B ) > 255 ? $R / max( $Rsum, $Gsum, $Bsum ) : $R / 255;
		$greenPercentage = max( $R, $G, $B ) > 255 ? $G / max( $Rsum, $Gsum, $Bsum ) : $G / 255;
		$bluePercentage  = max( $R, $G, $B ) > 255 ? $B / max( $Rsum, $Gsum, $Bsum ) : $B / 255;

		$redRGB   = floor( 255 * $redPercentage );
		$greenRGB = floor( 255 * $greenPercentage );
		$blueRGB  = floor( 255 * $bluePercentage );

		$color = sprintf( "%02X%02X%02X", $redRGB, $greenRGB, $blueRGB );

		return $addHash ? '#' . $color : $color;
	}

	/**
	 * Recursively sort an array of taxonomy terms hierarchically. Child categories will be
	 * placed under a 'children' member of their parent term.
	 * @param Array   $cats     taxonomy term objects to sort
	 * @param Array   $into     result array to put them in
	 * @param integer $parentId the current parent ID to put them in
	 */
	private static function sort_terms_hierarchicaly(Array &$cats, Array &$into, $parentId = 0)
	{
		foreach ($cats as $i => $cat) {
			if ($cat->parent == $parentId) {
				$into[$cat->term_id] = $cat;
				unset($cats[$i]);
			}
		}

		foreach ($into as $topCat) {
			$topCat->children = array();
			self::sort_terms_hierarchicaly($cats, $topCat->children, $topCat->term_id );
		}
	}



	public static function copy_taxonomy($from_productid, $from_taxonomyname, $to_productid, $to_taxonomyname) {

		$categories = wp_get_post_terms( $from_productid, $from_taxonomyname );


		$categoryHierarchy = array();
		self::sort_terms_hierarchicaly($categories, $categoryHierarchy );

		//delete existing terms
		wp_set_object_terms( $to_productid, null, $to_taxonomyname );

		self::copy_taxonomy_h( $from_productid, $from_taxonomyname, $to_productid, $to_taxonomyname, $categoryHierarchy, 0 );


		//get all categories for the post
		$categories = wp_get_object_terms( $to_productid, $to_taxonomyname );

		$default_product_cat = get_option( 'default_product_cat' );

		//if there is more than one category set, check to see if one of them is the default
		if ( count( $categories ) > 1 ) {
			foreach ( $categories as $key => $category ) {
				//if category is the default, then remove it
				if ( $category->term_id == $default_product_cat ) {
					wp_remove_object_terms( $to_productid, $category->term_id, $to_taxonomyname );
				}
			}
		}

	}

	private static function copy_taxonomy_h($from_productid, $from_taxonomyname, $to_productid, $to_taxonomyname, $children, $parentid) {


		foreach($children as $cat) {
			$termid = 0;

			if ( ! term_exists( $cat->name, $to_taxonomyname, $parentid ) ) {

				if ( $parentid > 0 ) {
					$term = wp_insert_term(
						$cat->name, // the term
						$to_taxonomyname, // the taxonomy
						array( 'parent' => $parentid )
					);
				} else {
					$term = wp_insert_term(
						$cat->name, // the term
						$to_taxonomyname // the taxonomy
					);
				}

				if ( is_wp_error( $term ) ) {
					$error_string = $term->get_error_message();
					throw new Exception ( esc_html__( 'Term error (parent: ' . $parentid . '): ' . $error_string . ' - TaxonomyName: ' . $to_taxonomyname . " - Value: " . $cat->name, ATKP_PLUGIN_PREFIX ) );
				}

				$termid = intval( $term['term_id'] );

			}else {

				$term = null;
				if ( $parentid > 0 ) {
					$terms = get_term_children( $parentid, $to_taxonomyname );

					foreach ( $terms as $termx ) {
						$termxf = get_term_by( 'id', $termx, $to_taxonomyname );

						if ( $termxf->name == $cat->name ) {
							$term = $termxf;
							break;
						}
					}
				} else {
					$term = get_term_by( 'name', $cat->name, $to_taxonomyname );

				}

				if($term != null) {
					$termid = $term->term_id;
				}
			}


			if ( $termid > 0 ) {
				$term_taxonomy_ids = wp_set_object_terms( $to_productid, $termid, $to_taxonomyname, true );

				if ( isset( $cat->children ) && count($cat->children) > 0) {
					self::copy_taxonomy_h( $from_productid, $from_taxonomyname, $to_productid, $to_taxonomyname, $cat->children, $termid );
				}
			}
		}
	}

	public static function check_taxonomy( $post_id, $taxonomyName, $taxonomyValue, $hierchical_mode = true ) {
		if ( $taxonomyValue == '' || $taxonomyValue == null ) {
			wp_set_object_terms( $post_id, null, $taxonomyName );

			return;
		}

		$taxonomyValues = array();

		if ( $hierchical_mode && ! is_array( $taxonomyValue ) ) {
			$taxonomyValues = explode( ',', $taxonomyValue );
		} else if ( ! is_array( $taxonomyValue ) ) {
			array_push( $taxonomyValues, $taxonomyValue );
		} else {
			$taxonomyValues = $taxonomyValue;
		}

		$taxonomyValues = array_map( 'trim', $taxonomyValues );

		$cat_ids   = array();
		$parent_id = - 1;
		foreach ( $taxonomyValues as $taxonomyValue ) {
			if ( $taxonomyValue == '' ) {
				continue;
			}

			$termid = - 1;

			if ( ! term_exists( $taxonomyValue, $taxonomyName, $parent_id == - 1 ? null : $parent_id ) ) {

				if ( $parent_id >= 0 ) {
					$term = wp_insert_term(
						$taxonomyValue, // the term
						$taxonomyName, // the taxonomy
						array( 'parent' => $parent_id )
					);
				} else {
					$term = wp_insert_term(
						$taxonomyValue, // the term
						$taxonomyName // the taxonomy
					);
				}

				if ( is_wp_error( $term ) ) {
					$error_string = $term->get_error_message();

					return;
					//throw new Exception ( 'Term error (parent: ' . $parent_id . '/' . $hierchical_mode . '): ' . $error_string . ' - TaxonomyName: ' . $taxonomyName . " - Value: " . $taxonomyValue );
				}

				$termid = intval( $term['term_id'] );
			} else {
				$term = null;
				if ( $parent_id >= 0 ) {
					$terms = get_term_children( $parent_id, $taxonomyName );

					foreach ( $terms as $termx ) {
						$termxf = get_term_by( 'id', $termx, $taxonomyName );


						if ( $termxf->name == $taxonomyValue || $termxf->slug == $taxonomyValue ) {
							$term = $termxf;
							break;
						}
					}

				}

				if ( $term == null ) {
					$term = get_term_by( 'name', $taxonomyValue, $taxonomyName );

					if ( ! $term ) {
						$term = get_term_by( 'slug', $taxonomyValue, $taxonomyName );
					}
				}

				if ( $term ) {
					$termid = $term->term_id;
				} //term_taxonomy_id

			}

			if ( $termid != - 1 ) {
				array_push( $cat_ids, $termid );
				if ( ! $hierchical_mode ) {
					$parent_id = $termid;
				}
			}
		}

		$cat_ids = array_map( 'intval', $cat_ids );
		$cat_ids = array_unique( $cat_ids );

		if ( count( $cat_ids ) > 0 ) {
			$term_taxonomy_ids = wp_set_object_terms( $post_id, $cat_ids, $taxonomyName, false );

			if ( is_wp_error( $term_taxonomy_ids ) ) {
				$error_string = $term_taxonomy_ids->get_error_message();

				return;
				//throw new Exception ( 'Term error: ' . $error_string . ' - TaxonomyName: ' . $taxonomyName . " - IDs: " . serialize( $cat_ids ) );
				// There was an error somewhere and the terms couldn't be set.
				//TODO: logging
			} else {
				// Success! These categories were added to the post.
			}
		}

	}


	public static function add_global_scripts( $name ) {

		$output    = new atkp_output();
		$custom_js = $output->get_js_output();

		wp_add_inline_script( $name, $custom_js );
	}

	public static function write_global_scripts() {

		$output    = new atkp_output();
		$custom_js = $output->get_js_output();

		self::write_file( $custom_js, 'scripts.js' );
	}

	public static function write_global_styles() {

		if ( atkp_options::$loader->get_css_inline() == atkp_css_type::CssFile || atkp_options::$loader->get_css_inline() == atkp_css_type::Inline ) {
			$output    = new atkp_output();
			$custom_js = $output->get_css_output();

			self::write_file( $custom_js, 'styles.css' );
		} else {
			self::delete_file( 'styles.css' );
		}
	}

	public static function get_global_style_url() {
		return self::get_file( 'styles.css' );
	}

	public static function get_global_script_url() {
		return self::get_file( 'scripts.js' );
	}


	public static function get_file( $name ) {
		$upload_dir = wp_upload_dir();

		if ( empty( $upload_dir['basedir'] ) ) {
			return null;
		}
		$user_dirname = $upload_dir['basedir'] . '/affiliate-toolkit';
		if ( ! file_exists( $user_dirname ) ) {
			return null;
		}

		$user_filename = $user_dirname . '/' . $name;

		if ( ! file_exists( $user_filename ) ) {
			return null;
		}

		return $upload_dir['baseurl'] . '/affiliate-toolkit' . '/' . $name;
	}

	public static function get_uploaddir( $subfolder = '' ) {
		$upload_dir = wp_upload_dir();

		if ( ! empty( $upload_dir['basedir'] ) ) {
			$user_dirname = $upload_dir['basedir'] . '/affiliate-toolkit' . ( $subfolder != '' ? '/' . $subfolder : '' );
			if ( ! file_exists( $user_dirname ) ) {
				wp_mkdir_p( $user_dirname );
			}
		}

		return $user_dirname;
	}


	public static function delete_file( $name ) {
		try {
			$user_dirname = self::get_uploaddir();

			if ( file_exists( $user_dirname . '/' . $name ) ) {
				unlink( $user_dirname . '/' . $name );
			}
		} catch ( Exception $e ) {

		}
	}

	public static function write_file( $content, $name ) {
		try {
			$user_dirname = self::get_uploaddir();

			file_put_contents( $user_dirname . '/' . $name, $content );
		} catch ( Exception $e ) {

		}
	}

	public static function add_column( $post_types, $label, $callback, $priority = 10 ) {
		if ( ! is_array( $post_types ) ) {
			$post_types = array( $post_types );
		}
		foreach ( $post_types as $post_type ) {
			$filter_name = 'manage_' . $post_type . '_posts_columns';

			add_filter( $filter_name, function ( $columns ) use ( $label, $priority ) {
				$key = sanitize_title( $label );
				$col = array( $key => $label );
				if ( $priority < 0 ) {
					return array_merge( $col, $columns );
				} else if ( $priority > count( $columns ) ) {
					return array_merge( $columns, $col );
				} else {
					$offset = $priority;
					$sorted = array_slice( $columns, 0, $offset, true ) + $col + array_slice( $columns, $offset, null, true );

					return $sorted;
				}
			}, $priority );

			add_action( 'manage_' . $post_type . '_posts_custom_column', function ( $col, $pid ) use ( $label, $callback ) {
				$key = sanitize_title( $label );
				if ( $col == $key ) {
					$callback( $pid );
				}
			}, $priority, 2 );
		}
	}

	public static function show_notification( $text, $class = 'updated', $type = 'info' ) {
		if ( $class == 'yellow' ) {
			$class = 'updated';
		}
		if ( $class == 'red' ) {
			$class = 'error';
		}

		if ( $class == 'blue' ) {
			$class = 'notice';
		}

		echo ( '<div class="' . $class . ' ' . $class . '-' . $type . '"><p>' . $text . '</p></div>' );

	}


	public static function get_siteurl() {

		$url = 'unknown';

		if ( is_multisite() ) {
			$url = network_site_url();
		} else {
			$url = site_url();
		}

		return $url;
	}

	public static function get_endpointurl() {

		$url = admin_url( 'admin-ajax.php' );

		return $url;
	}

	public static function exists_get_parameter( $key ) {
		return isset( $_GET[ $key ] );
	}

	public static function get_get_parameter( $key, $type ) {
		$parametervalue = null;

		if ( isset( $_GET[ $key ] ) ) {
			$parametervalue = $_GET[ $key ];
		}

		return ATKPTools::get_casted_value( $parametervalue, $type );
	}

	public static function exists_post_parameter( $key ) {
		return isset( $_POST[ $key ] );
	}

	public static function get_post_parameter( $key, $type ) {
		$parametervalue = null;

		if ( isset( $_POST[ $key ] ) ) {
			$parametervalue = $_POST[ $key ];
		}

		return ATKPTools::get_casted_value( $parametervalue, $type );
	}

	public static function get_casted_value( $parametervalue, $type ) {

		switch ( $type ) {
			case 'bool':
				if ( $parametervalue == null || $parametervalue == '' ) {
					return false;
				} else {
					//hack for older versions than 5.5
					if ( function_exists( 'boolval' ) ) {
						return boolval( $parametervalue );
					} else {
						return (bool) $parametervalue;
					}
				}
				break;
			case 'intarray':
				if ( $parametervalue == null || ! is_array( $parametervalue ) ) {
					return 0;
				} else {
					return array_map( 'intval', $parametervalue );
				}
				break;
			case 'int':
				if ( $parametervalue == null || $parametervalue == '' ) {
					return 0;
				} else {
					return intval( $parametervalue );
				}
				break;
			case 'double':
				if ( $parametervalue == null || $parametervalue == '' ) {
					return 0;
				} else {
					return floatval( $parametervalue );
				}
				break;
			case 'stringarray':
				if ( $parametervalue == null || ! is_array( $parametervalue ) ) {
					return 0;
				} else {
					return array_map( 'sanitize_text_field', $parametervalue );
				}
				break;
			case 'string':
				if ( $parametervalue == null || $parametervalue == '' ) {
					return '';
				} else {
					return sanitize_text_field( $parametervalue );
				}
				break;
			case 'multistring2':
			case 'multistring':
				if ( $parametervalue == null || $parametervalue == '' ) {
					return '';
				} else {
					return implode( "\n", array_map( 'sanitize_text_field', (array) explode( "\n", (string) $parametervalue ) ) );
				}
				break;
			case 'allhtml':
				if ( $parametervalue == null || $parametervalue == '' ) {
					return '';
				} else {
					return ( $parametervalue );
				}
				break;
			case 'html':
				if ( $parametervalue == null || $parametervalue == '' ) {
					return '';
				} else {
					return wp_kses_post( $parametervalue );
				}

				break;
			case 'url':
				if ( $parametervalue == null || $parametervalue == '' ) {
					return '';
				} else {
/*
					return  strip_tags(
						stripslashes(
							filter_var($parametervalue, FILTER_VALIDATE_URL)
						)
					);*/
					return $parametervalue; //sanitize_text_field( $parametervalue );
				}
				break;
			default:
				throw new exception( esc_html__( 'type unkown: ' . $type, ATKP_PLUGIN_PREFIX ) );
		}
	}



	public static function get_time( $time, $type, $gmt = 0 ) {
		switch ( $type ) {
			case 'mysql':
				return ( $gmt ) ? gmdate( $time, 'Y-m-d H:i:s' ) : gmdate( $time, 'Y-m-d H:i:s', ( time() + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) ) );
			case 'timestamp':
				return ( $gmt ) ? $time : $time + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
			default:
				return ( $gmt ) ? date( $time, $type ) : date( $time, $type, $time + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) );
		}
	}

	/**
	 * Lädt ein Metafield von einem Post nach Key/Feldname
	 *
	 * @param int $post_id Die Post-ID von welchem das Metafield geladen werden soll
	 * @param string $key Der Name des Metafields
	 *
	 * @return mixed|string Gibt entweder einen Leerstring oder das gespeicherte Objekt zurück
	 */
	public static function get_post_setting( $post_id, $key, $default_value = '' ) {
		$value = get_post_meta( $post_id, $key );

		if ( $value != null && is_array( $value ) && count( $value ) > 0 ) {
			return $value[0];
		} else {
			return '';
		}
	}


	/**
	 * Schreibt ein Metafield von einem Post
	 *
	 * @param int $post_id Die Post-ID von welchem das Metafield geladen werden soll
	 * @param string $key Der Name des Metafields
	 * @param mixed $value Der Wert des Metafields
	 */
	public static function set_post_setting( $post_id, $key, $value ) {

		delete_post_meta( $post_id, $key );
		if ( $value != null ) {
			add_post_meta( $post_id, $key, $value );
		}

	}

	public static function set_setting( $key, $value ) {

		delete_option( $key );

		add_option( $key, $value );

	}

	public static function get_setting( $key, $defaultvalue = null ) {

		$value = get_option( $key );

		if ( isset( $value ) ) {
			return $value;
		} else {
			return $defaultvalue;
		}
	}

	public static function delete_all_options() {
		global $wpdb;

		$plugin_options = $wpdb->get_results( "SELECT option_name FROM $wpdb->options WHERE option_name LIKE '" . ATKP_PLUGIN_PREFIX . "_%'" );

		foreach ( $plugin_options as $option ) {
			delete_option( $option->option_name );
		}
	}


	public static function substrwords( $text, $maxchar, $end = '...' ) {
		if ( strlen( $text ) > $maxchar || $text == '' ) {
			$words  = preg_split( '/\s/', $text );
			$output = '';
			$i      = 0;
			while ( 1 ) {
				$length = strlen( $output ) + strlen( $words[ $i ] );
				if ( $length > $maxchar ) {
					break;
				} else {
					$output .= " " . $words[ $i ];
					++ $i;
				}
			}
			$output .= $end;
		} else {
			$output = $text;
		}

		return $output;
	}

	public static function str_shorten( $string, $max_length, $suffix = '' ) {
		if ( strlen( $string ) > $max_length ) {
			return substr( $string, 0, $max_length ) . $suffix;
		}

		return $string;
	}
	public static function str_contains( $string, $searchstring, $caseSensitive = true ) {

		if ( $caseSensitive ) {
			if ( strpos( $string, $searchstring ) !== false ) {
				return true;
			} else {
				return false;
			}
		} else {
			if ( stripos( $string, $searchstring ) !== false ) {
				return true;
			} else {
				return false;
			}
		}
	}

	public static function startsWith( $haystack, $needle ) {
		// search backwards starting from haystack length characters from the end
		return $needle === "" || strrpos( $haystack, $needle, - strlen( $haystack ) ) !== false;
	}

	public static function price_to_float( $s ) {
		//$s = str_replace( ',', '.', $s );

		// remove everything except numbers and dot "."
		//$s = preg_replace( "/[^0-9\.]/", "", $s );

		// remove all seperators from first part and keep the end
		//$s = str_replace( '.', '', substr( $s, 0, - 3 ) ) . substr( $s, - 3 );

		$f = self::strToFloat( $s ); //(float) $s

		// return float
		return round( $f, 2 );
	}

	private static function strToFloat( $str ) {
		$str = preg_replace( "/[^0-9.,]/", "", $str );

		$str = preg_replace( '[^0-9\,\.\-\+]', '', strval( $str ) );

		$str = strtr( $str, ',', '.' );
		$pos = strrpos( $str, '.' );

		return ( $pos === false ? floatval( $str ) : floatval( str_replace( '.', '', substr( $str, 0, $pos ) ) . substr( $str, $pos ) ) );
	}

	public static function clear_string( $string ) {
		if ( $string == '' ) {
			return '';
		}

		// Strip HTML Tags
		$clear = strip_tags( $string );
// Clean up things like &amp;
		//$clear = html_entity_decode( $clear );
// Strip out any url-encoded stuff
		$clear = urldecode( $clear );
// Replace non-AlNum characters with space
		//$clear = preg_replace('/[^A-Za-z0-9(),]/', ' ', $clear);
// Replace Multiple spaces with single space
		$clear = preg_replace( '/ +/', ' ', $clear );
// Trim the string of leading/trailing space
		$clear = trim( $clear );

		$clear = str_replace( '\'', '', $clear );
		$clear = str_replace( '&nbsp;', ' ', $clear );

		$umlaute = array(
			"Ö" => "&Ouml;",
			"ö" => "&ouml;",
			"Ä" => "&Auml;",
			"ä" => "&auml;",
			"Ü" => "&Uuml;",
			"ü" => "&uuml;",
			"ß" => "&szlig;",
		);

		foreach ( $umlaute as $html => $umlaut ) {
			$clear = str_replace( $html, $umlaut, $clear );

		}

		return $clear;
	}

	public static function casttoclass( $class, $object ) {
		return unserialize( preg_replace( '/^O:\d+:"[^"]++"/', 'O:' . strlen( $class ) . ':"' . $class . '"', serialize( $object ) ) );
	}

	public static function get_currenttime() {
		return time();
	}

	public static function get_client_ip_address() {
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$address = $_SERVER['HTTP_CLIENT_IP'];
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$address = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$address = $_SERVER['REMOTE_ADDR'];
		}

		return $address;
	}


	public static function get_user_agent() {

		$agents = array(
			#Chrome
			'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.90 Safari/537.36',
			'Mozilla/5.0 (Windows NT 5.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.90 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.2; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.90 Safari/537.36',
			'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36',
			'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.133 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.133 Safari/537.36',
			'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36',
			#Firefox
			'Mozilla/4.0 (compatible; MSIE 9.0; Windows NT 6.1)',
			'Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; rv:11.0) like Gecko',
			'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0)',
			'Mozilla/5.0 (Windows NT 6.1; Trident/7.0; rv:11.0) like Gecko',
			'Mozilla/5.0 (Windows NT 6.2; WOW64; Trident/7.0; rv:11.0) like Gecko',
			'Mozilla/5.0 (Windows NT 10.0; WOW64; Trident/7.0; rv:11.0) like Gecko',
			'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.0; Trident/5.0)',
			'Mozilla/5.0 (Windows NT 6.3; WOW64; Trident/7.0; rv:11.0) like Gecko',
			'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0)',
			'Mozilla/5.0 (Windows NT 6.1; Win64; x64; Trident/7.0; rv:11.0) like Gecko',
			'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)',
			'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)',
			'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0; .NET CLR 2.0.50727; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729)'
		);

		$agent = array_rand( $agents, 1 );

		return $agent;
	}

	/**
	 * Get table charset and collation.
	 *
	 * @return string
	 * @since  2012.10.22
	 */
	public static function get_wp_charset_collate() {

		global $wpdb;
		$charset_collate = '';

		if ( ! empty ( $wpdb->charset ) ) {
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		}

		if ( ! empty ( $wpdb->collate ) ) {
			$charset_collate .= " COLLATE $wpdb->collate";
		}

		return $charset_collate;
	}

	public static function get_attached_filters( $hook, $format = false ) {
		global $wp_filter;
		if ( empty( $hook ) || ! isset( $wp_filter[ $hook ] ) ) {
			return '';
		}

		$array_names = array();
		foreach ( $wp_filter[ $hook ]->callbacks as $prio => $function ) {
			foreach ( $function as $nam => $f ) {
				$array_names[] = $f['function'];
			}
		}

		return $format ? ( '<pre>' . print_r( $array_names, true ) . '</pre>' ) : $array_names;
	}
}
