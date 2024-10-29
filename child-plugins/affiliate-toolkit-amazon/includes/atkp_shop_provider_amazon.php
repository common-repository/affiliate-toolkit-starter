<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

define( 'ATKP_AMZ_WAIT', 3 );

class atkp_shop_provider_amazon extends atkp_shop_provider_base {
	//das ist die basis klasse für alle shop provider


	public function __construct() {

	}

	public function get_maxproductcount() {
		return 10;
	}

	public function get_caption() {
		return esc_html__( 'Amazon Product Advertising API', ATKP_PLUGIN_PREFIX );
	}

	public function get_default_logo($post_id) {
		$website = $post_id == null ? '' : ATKPTools::get_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_access_website' );

		switch ( $website ) {
			default:
				return plugins_url( 'images/logo-normal-amazon-com.png', ATKP_AMAZON_PLUGIN_FILE );
			case 'de':
				return plugins_url( 'images/logo-normal-amazon-de.jpg', ATKP_AMAZON_PLUGIN_FILE );

		}
	}

	public function get_default_small_logo($post_id) {
		$website = $post_id == null ? '' : ATKPTools::get_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_access_website' );

		switch ( $website ) {
			default:
				return plugins_url( 'images/logo-small-amazon-com.png', ATKP_AMAZON_PLUGIN_FILE );
			case 'de':
				return plugins_url( 'images/logo-small-amazon-de.jpg', ATKP_AMAZON_PLUGIN_FILE );
		}
	}

	public function get_defaultbtn1_text() {
		return esc_html__( 'Buy now at Amazon', ATKP_PLUGIN_PREFIX );
	}

	public function get_defaultbtn2_text() {
		return esc_html__( 'Add to Amazon Cart', ATKP_PLUGIN_PREFIX );
	}

	public function replace_trackingid( $shopId, $url, $trackingId ) {
		//$associateTag = ATKPTools::get_post_setting($shopId, ATKP_SHOP_POSTTYPE.'_access_tracking_id');

		if ( $url == '' ) {
			return $url;
		}

		$startpos = strrpos( $url, '&AssociateTag=' );

		if ( ! $startpos ) {
			$startpos = strrpos( $url, '&tag=' );

			if ( ! $startpos ) {
				$startpos = strrpos( $url, '?tag=' );

				if ( ! $startpos ) {
					throw new exception( esc_html__('trackingcode not found: ' . $url, ATKP_PLUGIN_PREFIX) );
				} else {
					$startpos = $startpos + 5;
				}
			} else {
				$startpos = $startpos + 5;
			}
		} else {
			$startpos = $startpos + 14;
		}

		$endofstring = substr( $url, $startpos );

		$endpos = stripos( $endofstring, '&' );

		if ( ! $endpos ) {
			$endpos = strlen( $endofstring );
		}

		//echo $url .'<br /><br />';
		//echo $startpos.'<br /><br />';
		//echo $endpos.'<br /><br />';
		//echo $endofstring.'<br /><br />';
		//echo substr($url, 0, $startpos).'<br /><br />';
		//echo  substr($url, $endpos, strlen($url) - $endpos).'<br /><br />';


		$url = substr( $url, 0, $startpos ) . $trackingId . substr( $endofstring, $endpos, strlen( $endofstring ) - $endpos );
		//echo $url;
		//exit;

		//$url =  str_replace('&AssociateTag='.$associateTag, '&AssociateTag='.$trackingId, $url);
		//$url =  str_replace('&tag='.$associateTag, '&tag='.$trackingId, $url);
		//$url =  str_replace('?tag='.$associateTag, '?tag='.$trackingId, $url);

		return $url;
	}


	private function validate_request_v5( $searchItemsRequest ) {
		$invalidPropertyList = $searchItemsRequest->listInvalidProperties();
		$length              = count( $invalidPropertyList );
		if ( $length > 0 ) {
			$txt = "Error forming the request" . PHP_EOL;
			foreach ( $invalidPropertyList as $invalidProperty ) {
				$txt .= $invalidProperty . PHP_EOL;
			}
			throw new Exception( esc_html__($txt, ATKP_PLUGIN_PREFIX) );
		}
	}

	private function validate_response_v5( $getItemsResponse ) {

		if ( $getItemsResponse->getErrors() != null ) {
			throw new Exception( esc_html__($getItemsResponse->getErrors()[0]->getCode() . ': ' . $getItemsResponse->getErrors()[0]->getMessage(), ATKP_PLUGIN_PREFIX) );
		}
	}


	private function set_default_shop( $post_id ) {
		$subshopsold = ATKPTools::get_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_default_shops' );
		$subshops    = array();

		//add subshop for amazon
		$subshop         = new subshop();
		$subshop->title  = esc_html__( 'Amazon', ATKP_PLUGIN_PREFIX );
		$subshop->shopid = $post_id;

		$website = ATKPTools::get_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_access_website' );

		switch ( $website ) {
			default:
				$subshop->logourl      = plugins_url( 'images/logo-normal-amazon.jpg', ATKP_AMAZON_PLUGIN_FILE );
				$subshop->smalllogourl = plugins_url( 'images/logo-small-amazon.jpg', ATKP_AMAZON_PLUGIN_FILE );
				break;
			case 'de':
				$subshop->logourl      = plugins_url( 'images/logo-normal-amazon-de.jpg', ATKP_AMAZON_PLUGIN_FILE );
				$subshop->smalllogourl = plugins_url( 'images/logo-small-amazon-de.jpg', ATKP_AMAZON_PLUGIN_FILE );
				break;

		}

		$subshop->enabled = true;

		array_push( $subshops, $subshop );

		//für bestehende alte subshops ist dieser teil noch drinnen
		if ( is_array( $subshopsold ) ) {
			foreach ( $subshopsold as $shopold ) {
				if ( $subshop->shopid == $shopold->shopid && $subshop->programid == $shopold->programid ) {
					$subshop->enabled            = $shopold->enabled;
					$subshop->customtitle        = $shopold->customtitle;
					$subshop->customsmalllogourl = $shopold->customsmalllogourl;
					$subshop->customlogourl      = $shopold->customlogourl;
					$subshop->customfield1       = $shopold->customfield1;
					$subshop->customfield2       = $shopold->customfield2;
					$subshop->customfield3       = $shopold->customfield3;
				}
			}
		}

		ATKPTools::set_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_default_shops', $subshops );
	}

	private function get_config( $apikey, $apisecretkey, $country ) {
		$config = new Amazon\ProductAdvertisingAPI\v1\Configuration();

		$config->setAccessKey( $apikey );
		$config->setSecretKey( $apisecretkey );

		$host   = '';
		$region = '';

		switch ( $country ) {
			default;
			case 'de':
				$host   = 'webservices.amazon.de';
				$region = 'eu-west-1';
				break;
			case 'nl':
				$host   = 'webservices.amazon.nl';
				$region = 'eu-west-1';
				break;
			case 'com':
				$host   = 'webservices.amazon.com';
				$region = 'us-east-1';
				break;
			case 'co.uk':
				$host   = 'webservices.amazon.co.uk';
				$region = 'eu-west-1';
				break;
			case 'ca':
				$host   = 'webservices.amazon.ca';
				$region = 'us-east-1';
				break;
			case 'fr':
				$host   = 'webservices.amazon.fr';
				$region = 'eu-west-1';
				break;
			case 'co.jp':
				$host   = 'webservices.amazon.co.jp';
				$region = 'us-west-2';
				break;
			case 'it':
				$host   = 'webservices.amazon.it';
				$region = 'eu-west-1';
				break;
			case 'cn':
			case 'es':
				$host   = 'webservices.amazon.es';
				$region = 'eu-west-1';
				break;
			case 'in':
				$host   = 'webservices.amazon.in';
				$region = 'eu-west-1';
				break;
			case 'au':
				$host   = 'webservices.amazon.com.au';
				$region = 'us-west-2';
				break;
			case 'com.br':
				$host   = 'webservices.amazon.com.br';
				$region = 'us-east-1';
				break;
			case 'com.mx':
				$host   = 'webservices.amazon.com.mx';
				$region = 'us-east-1';
				break;
			case 'com.tr':
				$host   = 'webservices.amazon.com.tr';
				$region = 'eu-west-1';
				break;
			case 'ae':
				$host   = 'webservices.amazon.ae';
				$region = 'eu-west-1';
				break;
			case 'pl':
				$host   = 'webservices.amazon.pl';
				$region = 'eu-west-1';
				break;
			case 'com.be':
				$host   = 'webservices.amazon.com.be';
				$region = 'eu-west-1';
				break;
		}


		$config->setHost( $host );
		$config->setRegion( $region );

		return $config;
	}

	private function check_guzzle() {
		$funcInc = [
			'GuzzleHttp\choose_handler'      => 'lib/vendor/guzzlehttp/guzzle/src/functions_include.php',
			'GuzzleHttp\Psr7\build_query'    => 'lib/vendor/guzzlehttp/psr7/src/functions.php',
			'GuzzleHttp\Promise\promise_for' => 'lib/vendor/guzzlehttp/promises/src/functions.php',
			//'Promise\promise_for' => 'lib/vendor/guzzlehttp/promises/src/functions.php',
		];

		foreach ( $funcInc as $function => $incPath ) {
			if ( ! function_exists( $function ) ) {
				$includePath = ATKP_AMAZON_PLUGIN_DIR . DIRECTORY_SEPARATOR . $incPath;
				if ( file_exists( $includePath ) ) {
					require_once $includePath;
				}
			}
		}
	}

	private function get_api_instance( $apikey, $apisecretkey, $website ) {
		require_once ATKP_AMAZON_PLUGIN_DIR . '/lib/vendor/autoload.php';

		$config = $this->get_config( $apikey, $apisecretkey, $website );

		$this->check_guzzle();

		$apiInstance = new Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\api\DefaultApi( new \GuzzleHttp\Client(), $config );

		return $apiInstance;
	}

	private function check_configuration_v5( $post_id, $apikey, $apisecretkey, $website, $usessl, $trackingid ) {
		//require_once ATKP_PLUGIN_DIR . '/lib/paapi5-sdk/vendor/autoload.php';

		$apiInstance = $this->get_api_instance( $apikey, $apisecretkey, $website );

		$searchItemsRequest = new Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\SearchItemsRequest();
		$searchItemsRequest->setSearchIndex( 'All' );
		$searchItemsRequest->setKeywords( 'Harry Potter' );
		$searchItemsRequest->setItemCount( 1 );
		$searchItemsRequest->setPartnerTag( $trackingid );
		$searchItemsRequest->setPartnerType( Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\PartnerType::ASSOCIATES );

		$this->validate_request_v5( $searchItemsRequest );

		$test = '';

		try {
			$searchItemsResponse = $apiInstance->searchItems( $searchItemsRequest );

			$this->validate_response_v5( $searchItemsResponse );

			$itemcount = $searchItemsResponse->getSearchResult()->getTotalResultCount();
			if ( $itemcount == 0 ) {
				$test = 'item count is null';
			}

		} catch ( Amazon\ProductAdvertisingAPI\v1\ApiException $exception ) {
			$test = "API-Error: " . $exception->getCode() . " " . $exception->getMessage();

			if ( $exception->getResponseObject() instanceof Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\ProductAdvertisingAPIClientException ) {
				$errors = $exception->getResponseObject()->getErrors();
				foreach ( $errors as $error ) {
					$test = "Response-Error: " . $error->getCode() . " " . $error->getMessage();
				}
			} else {
				$test .= "Error response body: " . $exception->getResponseBody();
			}
		} catch ( Exception $exception ) {
			$test = "Error Message: " . $exception->getMessage(); //. ' ' . $exception->getTraceAsString();
		}

		if ( $test == '' ) {
			$this->set_default_shop( $post_id );
		} else {
			return $test;
		}
	}

	public function check_configuration( $post_id ) {
		try {
			$apikey       = ATKPTools::get_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_access_key' );
			$apisecretkey = ATKPTools::get_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_access_secret_key' );
			$website      = ATKPTools::get_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_access_website' );
			$trackingid   = ATKPTools::get_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_access_tracking_id' );
			$usessl       = ATKPTools::get_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_access_tracking_id' );
			$sitestripe   = ATKPTools::get_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_sitestripe' );

			$this->seconds_wait = ATKPTools::get_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_seconds_wait' );
			if ( $this->seconds_wait <= 0 ) {
				$this->seconds_wait = 1;
			}


			if ( $sitestripe == 2 || $sitestripe == 3 ) {
				return '';
			}

			$message = '';
			if ( $apikey != '' && $apisecretkey != '' ) {

					return $this->check_configuration_v5( $post_id, $apikey, $apisecretkey, $website, $usessl, $trackingid ) . '';

			} else {
				//wenn zugangscodes gelöscht werden muss message auch geleert werden
				$message = 'Credientials are empty';
			}

			return $message;
		} catch ( Exception $e ) {
			if ( ATKPLog::$logenabled ) {
				ATKPLog::LogError( $e->getMessage() );
			}

			return $e->getMessage();
		}
	}

	private function convert_response( $response ) {

		//return json_decode(json_encode($response), false);
		return json_decode( json_encode( (array) simplexml_load_string( $response ) ), 0 );
	}

	public function set_configuration( $post_id ) {

		ATKPTools::set_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_amz_medium_image_size', ATKPTools::get_post_parameter( ATKP_SHOP_POSTTYPE . '_amz_medium_image_size', 'int' ) );
		ATKPTools::set_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_amz_small_image_size', ATKPTools::get_post_parameter( ATKP_SHOP_POSTTYPE . '_amz_small_image_size', 'int' ) );


		ATKPTools::set_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_access_website', ATKPTools::get_post_parameter( ATKP_SHOP_POSTTYPE . '_amz_access_website', 'string' ) );
		ATKPTools::set_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_access_tracking_id', ATKPTools::get_post_parameter( ATKP_SHOP_POSTTYPE . '_amz_access_tracking_id', 'string' ) );
		ATKPTools::set_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_languages_of_preference', ATKPTools::get_post_parameter( ATKP_SHOP_POSTTYPE . '_languages_of_preference', 'string' ) );

		ATKPTools::set_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_load_customer_reviews', ATKPTools::get_post_parameter( ATKP_SHOP_POSTTYPE . '_amz_load_customer_reviews', 'bool' ) );
		ATKPTools::set_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_load_variations', ATKPTools::get_post_parameter( ATKP_SHOP_POSTTYPE . '_amz_load_variations', 'bool' ) );
		ATKPTools::set_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_onlynew', ATKPTools::get_post_parameter( ATKP_SHOP_POSTTYPE . '_amz_onlynew', 'int' ) );

		ATKPTools::set_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_access_key', ATKPTools::get_post_parameter( ATKP_SHOP_POSTTYPE . '_amz_access_key', 'string' ) );
		ATKPTools::set_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_access_secret_key', ATKPTools::get_post_parameter( ATKP_SHOP_POSTTYPE . '_amz_access_secret_key', 'string' ) );

		ATKPTools::set_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_sitestripe', ATKPTools::get_post_parameter( ATKP_SHOP_POSTTYPE . '_sitestripe', 'int' ) );
		ATKPTools::set_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_seconds_wait', ATKPTools::get_post_parameter( ATKP_SHOP_POSTTYPE . '_amz_seconds_wait', 'int' ) );

		ATKPTools::set_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_apiversion', ATKPTools::get_post_parameter( ATKP_SHOP_POSTTYPE . '_amz_apiversion', 'string' ) );


	}

	private function get_defaultshops( $post_id ) {
		$subshops = array();

		$website = ATKPTools::get_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_access_website' );

		$subshop = new subshop();

		$subshop->title = esc_html__( 'Amazon', ATKP_PLUGIN_PREFIX );

		switch ( $website ) {
            default:
	            $subshop->logourl      = plugins_url( 'images/logo-normal-amazon.jpg', ATKP_AMAZON_PLUGIN_FILE );
	            $subshop->smalllogourl = plugins_url( 'images/logo-small-amazon.jpg', ATKP_AMAZON_PLUGIN_FILE );
	            break;
			case 'de':
				$subshop->logourl      = plugins_url( 'images/logo-normal-amazon-de.jpg', ATKP_AMAZON_PLUGIN_FILE );
				$subshop->smalllogourl = plugins_url( 'images/logo-small-amazon-de.jpg', ATKP_AMAZON_PLUGIN_FILE );
				break;

		}

		$subshop->shopid    = $post_id;
		$subshop->programid = '';

		$subshop->enabled = true;

		array_push( $subshops, $subshop );

		return $subshops;
	}

	public function get_configuration( $post ) {
		$webservice = ATKPTools::get_post_setting( $post->ID, ATKP_SHOP_POSTTYPE . '_access_webservice' );

		$apikey       = '';
		$apisecretkey = '';
		$subshops     = null;

		if ( $webservice == '1' ) {
			$apikey       = ATKPTools::get_post_setting( $post->ID, ATKP_SHOP_POSTTYPE . '_access_key' );
			$apisecretkey = ATKPTools::get_post_setting( $post->ID, ATKP_SHOP_POSTTYPE . '_access_secret_key' );
		}
		?>
        <tr>
            <th scope="row">
                <label for="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_amz_access_key') ?>">
	                <?php echo esc_html__( 'Amazon Access Key ID', ATKP_PLUGIN_PREFIX ) ?> <span
                            class="description"><?php echo esc_html__( '(required)', ATKP_PLUGIN_PREFIX ) ?></span>
                </label>
            </th>
            <td>
                <input style="width:40%" type="text" id="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_amz_access_key') ?>"
                       name="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_amz_access_key') ?>" value="<?php echo esc_attr($apikey); ?>">
                <label for="">

                </label>
	            <?php ATKPTools::display_helptext(esc_html__('You can find your API key in the Amazon Partnernet. In the Submenu "Tools > Product Advertising API > Manage Your Credentials".', ATKP_PLUGIN_PREFIX)) ?>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_amz_access_secret_key') ?>">
	                <?php echo esc_html__( 'Amazon Secret Access Key', ATKP_PLUGIN_PREFIX ) ?> <span
                            class="description"><?php echo esc_html__( '(required)', ATKP_PLUGIN_PREFIX ) ?></span>
                </label>

            </th>
            <td>
                <input style="width:40%" type="password"
                       id="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_amz_access_secret_key') ?>"
                       name="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_amz_access_secret_key') ?>"
                       value="<?php echo esc_attr($apisecretkey); ?>">
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_amz_access_website') ?>">
	                <?php echo esc_html__( 'Amazon Website', ATKP_PLUGIN_PREFIX ) ?> <span
                            class="description"><?php echo esc_html__( '(required)', ATKP_PLUGIN_PREFIX ) ?></span>
                </label>
            </th>
            <td>
                <select name="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_amz_access_website') ?>" >
					<?php
					//        public static $locations = array('de', 'com', 'co.uk', 'ca', 'fr', 'co.jp', 'it', 'cn', 'es', 'in', 'com.br');

					$locations = array(
						'de'    => esc_html__( 'Amazon Germany', ATKP_PLUGIN_PREFIX ),
						'com'   => esc_html__( 'Amazon United States', ATKP_PLUGIN_PREFIX ),
						'co.uk' => esc_html__( 'Amazon United Kingdom', ATKP_PLUGIN_PREFIX ),
						'ca'    => esc_html__( 'Amazon Canada', ATKP_PLUGIN_PREFIX ),
						'fr'    => esc_html__( 'Amazon France', ATKP_PLUGIN_PREFIX ),
						'co.jp' => esc_html__( 'Amazon Japan', ATKP_PLUGIN_PREFIX ),
						'it'    => esc_html__( 'Amazon Italy', ATKP_PLUGIN_PREFIX ),

						'es'     => esc_html__( 'Amazon Spain', ATKP_PLUGIN_PREFIX ),
						'in'     => esc_html__( 'Amazon India', ATKP_PLUGIN_PREFIX ),
						'com.br' => esc_html__( 'Amazon Brazil', ATKP_PLUGIN_PREFIX ),
						'au'     => esc_html__( 'Amazon Australia', ATKP_PLUGIN_PREFIX ),
						'com.mx' => esc_html__( 'Amazon Mexico', ATKP_PLUGIN_PREFIX ),
						'com.tr' => esc_html__( 'Amazon Turkey', ATKP_PLUGIN_PREFIX ),
						'com.be' => esc_html__( 'Amazon Belgium', ATKP_PLUGIN_PREFIX ),
						'ae'     => esc_html__( 'Amazon United Arab Emirates', ATKP_PLUGIN_PREFIX ),
						'nl'     => esc_html__( 'Amazon Netherlands', ATKP_PLUGIN_PREFIX ),
						'pl'    => esc_html__( 'Amazon Poland', ATKP_PLUGIN_PREFIX ),
					);
					//'cn'     => esc_html__( 'Amazon China', ATKP_PLUGIN_PREFIX ),

					foreach ( $locations as $value => $name ) {
						if ( $value == ATKPTools::get_post_setting( $post->ID, ATKP_SHOP_POSTTYPE . '_access_website' ) ) {
							$sel = ' selected';
						} else {
							$sel = '';
						}


						echo '<option value="' . esc_attr( $value ) . '"' . esc_attr( $sel ) . '>' . esc_html__( $name, ATKP_PLUGIN_PREFIX ) . '</option>';
					} ?>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_amz_access_tracking_id') ?>">
	                <?php echo esc_html__( 'Amazon Tracking ID', ATKP_PLUGIN_PREFIX ) ?> <span
                            class="description"><?php echo esc_html__( '(required)', ATKP_PLUGIN_PREFIX ) ?></span>
                </label>
            </th>
            <td>
                <input type="text" id="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_amz_access_tracking_id') ?>"
                       name="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_amz_access_tracking_id') ?>"
                       value="<?php echo esc_attr(ATKPTools::get_post_setting( $post->ID, ATKP_SHOP_POSTTYPE . '_access_tracking_id' )); ?>">
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_languages_of_preference') ?>">
					<?php echo esc_html__( 'Languages Of Preference', ATKP_PLUGIN_PREFIX ) ?>
                </label>

            </th>
            <td>
                <input type="text" id="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_languages_of_preference') ?>"
                       name="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_languages_of_preference') ?>"
                       value="<?php echo esc_attr(ATKPTools::get_post_setting( $post->ID, ATKP_SHOP_POSTTYPE . '_languages_of_preference' )); ?>">
	            <?php ATKPTools::display_helptext('You can set an list of languages you want to receive (comma separated). You can find the valid languages for each marketplace <a href="https://webservices.amazon.de/paapi5/documentation/locale-reference.html" target="_blank">here</a>.') ?>

            </td>
        </tr>



		<?php if ( defined( 'ATKP_AMAZNOAPI_ITEM_ID' ) && ATKP_LicenseController::get_module_license_status( 'amaznoapi' ) == 'valid' ) { ?>


            <tr>
                <th scope="row">
                    <label for="">
	                    <?php echo esc_html__( 'No API mode', ATKP_PLUGIN_PREFIX ) ?>
                    </label>
                </th>
                <td>

                    <select id="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_sitestripe') ?>"
                            name="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_sitestripe') ?>" style="width:300px">
						<?php
						$selected = ATKPTools::get_post_setting( $post->ID, ATKP_SHOP_POSTTYPE . '_sitestripe' );

						echo '<option value="1" ' . ( $selected == '' || $selected == 1 ? 'selected' : '' ) . ' >' . esc_html__( 'Disabled', ATKP_PLUGIN_PREFIX ) . '</option>';

						echo '<option value="2" ' . ( $selected == 2 ? 'selected' : '' ) . '>' . esc_html__( 'Always use', ATKP_PLUGIN_PREFIX ) . '</option>';

						echo '<option value="3" ' . ( $selected == 3 ? 'selected' : '' ) . '>' . esc_html__( 'Use in case of error', ATKP_PLUGIN_PREFIX ) . '</option>';
						?>

                    </select>
					<?php ATKPTools::display_helptext( esc_html__( 'NoAPI allows you to read the Title, Price and Image from the Amazon Widgets. You can use this option if you don\'t have API access or you receiving the "Too Many" exception. It\'s not a official Amazon functionality.', ATKP_PLUGIN_PREFIX ) ) ?>

                </td>
            </tr>
            <tr>
                <th scope="row">

                </th>
                <td>
                    <input type="checkbox" id="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_amz_load_customer_reviews') ?>"
                           name="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_amz_load_customer_reviews') ?>"
                           value="1" <?php echo checked( 1, ATKPTools::get_post_setting( $post->ID, ATKP_SHOP_POSTTYPE . '_load_customer_reviews' ), true ); ?>>
                    <!-- ATKPTools::get_post_setting($post->ID, ATKP_SHOP_POSTTYPE.'_load_customer_reviews') -->
                    <label for="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_amz_load_customer_reviews') ?>">
	                    <?php echo esc_html__( 'Scrape star ratings from website (not recommended)', ATKP_PLUGIN_PREFIX ) ?>
                    </label>
					<?php ATKPTools::display_helptext( esc_html__( 'This function is reading the star rating from the Amazon webpage. It is not allowed by Amazon and we don\'t recommend it.', ATKP_PLUGIN_PREFIX ) ) ?>
                </td>
            </tr>

		<?php } else if ( ATKP_LicenseController::get_module_license_status( 'amaznoapi' ) != 'valid' ) {
			?>
            <tr>
                <th colspan="2">
                    <div class="atkp-success">
	                    <?php echo esc_html__( 'Please activate the "affiliate-toolkit - Amazon No API Mode" extension.', ATKP_PLUGIN_PREFIX ) ?>
                    </div>

                    <style>
                        .atkp-success {
                            color: #4F8A10;
                            background-color: #DFF2BF;
                        }

                        .atkp-info, .atkp-success, .atkp-warning, .atkp-error, .atkp-validation {
                            border: 1px solid;
                            margin: 0px 0px;
                            width: 95%;
                            padding: 15px 10px 15px 10px;
                            background-repeat: no-repeat;
                            background-position: 10px center;
                            display: inline-block;
                        }
                    </style>
                </th>
            </tr>

			<?php
		} else { ?>
            <tr>
                <th colspan="2">
                    <div class="atkp-info">
	                    <?php echo esc_html__( 'Please note: If you are using the official Amazon API everything is fine. If you have no access to the API (e.g. too many requests exception) you need to download our extension for this functionality.', ATKP_PLUGIN_PREFIX ) ?>
                        <br/> <br/><a href="https://www.affiliate-toolkit.com/downloads/amazon-no-api-mode/"
                                      target="_blank"
                                      class="button atkp-button"><?php echo esc_html__( 'Download extension now', ATKP_PLUGIN_PREFIX ) ?></a>
                    </div>

                    <style>
                        .atkp-success {
                            color: #4F8A10;
                            background-color: #DFF2BF;
                        }

                        .atkp-info, .atkp-success, .atkp-warning, .atkp-error, .atkp-validation {
                            border: 1px solid;
                            margin: 0px 0px;
                            width: 95%;
                            padding: 15px 10px 15px 10px;
                            background-repeat: no-repeat;
                            background-position: 10px center;
                            display: inline-block;
                        }
                    </style>
                </th>
            </tr>

		<?php } ?>

        <tr>
            <th scope="row">
                <label for="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_amz_small_image_size') ?>">
	                <?php echo esc_html__( 'Small image size', ATKP_PLUGIN_PREFIX ) ?>
                </label>
            </th>
            <td>
                <input type="number" min="0" max="1000" placeholder="75" id="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_amz_small_image_size') ?>"
                       name="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_amz_small_image_size') ?>"
                       value="<?php echo esc_attr( ATKPTools::get_post_setting( $post->ID, ATKP_SHOP_POSTTYPE . '_amz_small_image_size' ) ); ?>"> <?php echo esc_html__( 'px', ATKP_PLUGIN_PREFIX ) ?>
	            <?php ATKPTools::display_helptext(esc_html__('Amazon offers flexible image sizes. If you wan\'t to override the default size of 75px you can change it here. Changes for already imported products are visible after the cache update.', ATKP_PLUGIN_PREFIX)) ?>
            </td>
        </tr>

        <tr>
            <th scope="row">
                <label for="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_amz_medium_image_size') ?>">
	                <?php echo esc_html__( 'Medium image size', ATKP_PLUGIN_PREFIX ) ?>
                </label>
            </th>
            <td>
                <input type="number" min="0" max="1000" placeholder="160" id="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_amz_medium_image_size') ?>"
                       name="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_amz_medium_image_size') ?>"
                       value="<?php echo esc_attr( ATKPTools::get_post_setting( $post->ID, ATKP_SHOP_POSTTYPE . '_amz_medium_image_size' ) ); ?>"> <?php echo esc_html__( 'px', ATKP_PLUGIN_PREFIX ) ?>
	            <?php ATKPTools::display_helptext(esc_html__('Amazon offers flexible image sizes. If you wan\'t to override the default size of 160px you can change it here. Changes for already imported products are visible after the cache update.', ATKP_PLUGIN_PREFIX)) ?>
            </td>
        </tr>



        <tr>
            <th scope="row">
                <label for="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_amz_onlynew') ?>">
	                <?php echo esc_html__( 'Product condition:', ATKP_PLUGIN_PREFIX ) ?>
                </label>
            </th>
            <td>


                <select id="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_amz_onlynew') ?>"
                        name="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_amz_onlynew') ?>" style="width:300px">
					<?php
					$selected = ATKPTools::get_post_setting( $post->ID, ATKP_SHOP_POSTTYPE . '_onlynew' );

					echo '<option value="" ' . ( $selected == '' || $selected == '0' ? 'selected' : '' ) . ' >' . esc_html__( 'Any', ATKP_PLUGIN_PREFIX ) . '</option>';

					echo '<option value="1" ' . ( $selected == '1' ? 'selected' : '' ) . '>' . esc_html__( 'New', ATKP_PLUGIN_PREFIX ) . '</option>';
					echo '<option value="2" ' . ( $selected == '2' ? 'selected' : '' ) . '>' . esc_html__( 'Used', ATKP_PLUGIN_PREFIX ) . '</option>';
					echo '<option value="3" ' . ( $selected == '3' ? 'selected' : '' ) . '>' . esc_html__( 'Collectible', ATKP_PLUGIN_PREFIX ) . '</option>';
					echo '<option value="4" ' . ( $selected == '4' ? 'selected' : '' ) . '>' . esc_html__( 'Refurbished', ATKP_PLUGIN_PREFIX ) . '</option>';


					?>

                </select>
	            <?php ATKPTools::display_helptext(esc_html__('You can filter if you only wan\'t prices for special conditions of products. By default you receive all offers. If you only wan\'t to show "used" products on your website you can select a different option.', ATKP_PLUGIN_PREFIX)) ?>
            </td>
        </tr>

        <tr>
            <th scope="row">
                <label for="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_amz_seconds_wait') ?>">
	                <?php echo esc_html__( 'Wait x seconds before sending the request', ATKP_PLUGIN_PREFIX ) ?>
                </label>
            </th>
            <td>
                <input type="number" min="0" max="20" id="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_amz_seconds_wait') ?>" placeholder="1"
                       name="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_amz_seconds_wait') ?>"
                       value="<?php echo esc_attr( ATKPTools::get_post_setting( $post->ID, ATKP_SHOP_POSTTYPE . '_seconds_wait' ) ); ?>"/> <?php echo esc_html__( 'seconds', ATKP_PLUGIN_PREFIX ) ?>
	            <?php ATKPTools::display_helptext(esc_html__('In normal cases you don\'t need to change to a higher limit. By default the API is waiting one second..', ATKP_PLUGIN_PREFIX)) ?>
            </td>
        </tr>



        <tr>
            <th scope="row">

            </th>
            <td>
                <input type="checkbox" id="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_amz_load_variations') ?>"
                       name="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_amz_load_variations') ?>"
                       value="1" <?php echo checked( 1, ATKPTools::get_post_setting( $post->ID, ATKP_SHOP_POSTTYPE . '_load_variations' ), true ); ?>>

                <label for="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_amz_load_variations') ?>">
	                <?php echo esc_html__( 'Load variations for products', ATKP_PLUGIN_PREFIX ) ?>
                </label>
	            <?php ATKPTools::display_helptext(esc_html__('If you wan\'t to retrieve also other colors or variations for one product (e.g. Shirts) you can enable this option but this cost one extra request per product', ATKP_PLUGIN_PREFIX)) ?>
            </td>
        </tr>
		<?php

	}

	public function get_shops( $post_id, $allshops = false ) {

		$subshops = ATKPTools::get_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_default_shops' );

		if ( $subshops == null || count( $subshops ) > 1 ) {
			$subshops = $this->get_defaultshops( $post_id );
		}

		foreach ( $subshops as $subshop ) {
			$subshop->shopid    = $post_id;
			$subshop->programid = '';

			$subshop->logourl      = $subshop->customlogourl == '' ? $subshop->logourl : $subshop->customlogourl;
			$subshop->smalllogourl = $subshop->customsmalllogourl == '' ? $subshop->smalllogourl : $subshop->customsmalllogourl;
			$subshop->title        = $subshop->customtitle == '' ? $subshop->title : $subshop->customtitle;

			$subshop->enabled = true;
		}

		return $subshops;
	}

	/* @var Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\api\DefaultApi $helper */
	private $helper = null;
	private $enable_ssl = false;
	private $country = '';
	private $load_customer_reviews = false;
	private $associateTag = '';
	/**
	 * @var string[]
	 */
	private $languages_of_preference = '';
	private $accessKey = '';
	private $shopid = '';

	private $smallimagesize = 0;
	private $mediumimagesize = 0;

	public $sitetripemode = 0;
	private $usev5 = 0;
	private $load_variations = 0;
	private $onlynew = 0;
    private $seconds_wait = 0;


	private function checklogon_v5( $access_website, $access_key, $access_secret_key, $access_tracking_id ) {

		$this->helper = $this->get_api_instance( $access_key, $access_secret_key, $access_website );
	}

	public function checklogon( $shop ) {
		$this->shopid                = $shop->id;
		$this->accessKey             = $access_key = ATKPTools::get_post_setting( $shop->id, ATKP_SHOP_POSTTYPE . '_access_key' );
		$access_secret_key           = ATKPTools::get_post_setting( $shop->id, ATKP_SHOP_POSTTYPE . '_access_secret_key' );
		$this->country               = $access_website = ATKPTools::get_post_setting( $shop->id, ATKP_SHOP_POSTTYPE . '_access_website' );
		$this->associateTag          = $access_tracking_id = ATKPTools::get_post_setting( $shop->id, ATKP_SHOP_POSTTYPE . '_access_tracking_id' );

        $lang = ATKPTools::get_post_setting( $shop->id, ATKP_SHOP_POSTTYPE . '_languages_of_preference' );
        $this->languages_of_preference = $lang != '' ? explode(',', $lang) : null;

		$this->load_variations       = ATKPTools::get_post_setting( $shop->id, ATKP_SHOP_POSTTYPE . '_load_variations' );
		$this->enable_ssl            = true;
		$this->onlynew =  ATKPTools::get_post_setting( $shop->id, ATKP_SHOP_POSTTYPE . '_onlynew' );

		$this->seconds_wait = ATKPTools::get_post_setting( $shop->id, ATKP_SHOP_POSTTYPE . '_seconds_wait' );
		if ( $this->seconds_wait <= 0 ) {
			$this->seconds_wait = 1;
		}

		if ( ATKP_LicenseController::get_module_license_status( 'amaznoapi' ) == 'valid' &&
		     ATKP_LicenseController::get_module_license( 'amaznoapi' ) != '' ) {
			$this->sitetripemode         = ATKPTools::get_post_setting( $shop->id, ATKP_SHOP_POSTTYPE . '_sitestripe' );
			$this->load_customer_reviews = ATKPTools::get_post_setting( $shop->id, ATKP_SHOP_POSTTYPE . '_load_customer_reviews' );
		}


		$this->smallimagesize  = ATKPTools::get_post_setting( $shop->id, ATKP_SHOP_POSTTYPE . '_amz_small_image_size' );
		$this->mediumimagesize = ATKPTools::get_post_setting( $shop->id, ATKP_SHOP_POSTTYPE . '_amz_medium_image_size' );

		if ( $this->smallimagesize <= 0 ) {
			$this->smallimagesize = 75;
		}
		if ( $this->mediumimagesize <= 0 ) {
			$this->mediumimagesize = 160;
		}

		//http://ws-eu.amazon-adsystem.com/widgets/q?ServiceVersion=20070822&OneJS=1&Operation=GetAdHtml&MarketPlace=DE&source=ss&ref=as_ss_li_til&ad_type=product_link&tracking_id=werbeanzeige1-21&language=de_DE&marketplace=amazon&region=DE&placement=B01MR8IST0&asins=B01MR8IST0&linkId=2b2c154e99d12d52b2eeedaca502173f&show_border=true&link_opens_in_new_window=true
		//http://ws-eu.amazon-adsystem.com/widgets/q?ServiceVersion=20070822&OneJS=1&Operation=GetAdHtml&MarketPlace=DE&source=ss&ref=as_ss_li_til&ad_type=product_link&tracking_id=werbeanzeige1-21&language=de_DE&marketplace=amazon&region=DE&placement=B01MR8IST0&asins=B01MR8IST0&linkId=f9c12eaff04df7a156ab2470af8795b6&show_border=false&link_opens_in_new_window=true
		//https://www.amazon.de/Anbernic-Handheld-Spielkonsole-Konsole-Retro/dp/B079KC8Y4Z/ref=as_li_ss_il?pf_rd_p=bf2f9e9c-e5d5-4935-a04d-fda59481ccaa&pd_rd_wg=o3sJU&pf_rd_r=2XY13YPN803RR4ST4DQ6&ref_=pd_gw_cr_cartx&pd_rd_w=VtV3b&pd_rd_r=9a23560a-0d57-4b39-bb61-6df20e25109c&linkCode=li3&tag=werbeanzeige1-21&linkId=3ece46f52aef6db73c170a5784594205&language=de_DE" target="_blank"><img border="0" src="//ws-eu.amazon-adsystem.com/widgets/q?_encoding=UTF8&ASIN=B079KC8Y4Z&Format=_SL250_&ID=AsinImage&MarketPlace=DE&ServiceVersion=20070822&WS=1&tag=werbeanzeige1-21&language=de_DE" ></a><img src="https://ir-de.amazon-adsystem.com/e/ir?t=werbeanzeige1-21&language=de_DE&l=li3&o=3&a=B079KC8Y4Z
		//<a href="https://www.amazon.de/Anbernic-Handheld-Spielkonsole-Konsole-Retro/dp/B079KC8Y4Z/ref=as_li_ss_il?pf_rd_p=bf2f9e9c-e5d5-4935-a04d-fda59481ccaa&pd_rd_wg=o3sJU&pf_rd_r=2XY13YPN803RR4ST4DQ6&ref_=pd_gw_cr_cartx&pd_rd_w=VtV3b&pd_rd_r=9a23560a-0d57-4b39-bb61-6df20e25109c&linkCode=li3&tag=werbeanzeige1-21&linkId=3ece46f52aef6db73c170a5784594205&language=de_DE" target="_blank"><img border="0" src="//ws-eu.amazon-adsystem.com/widgets/q?_encoding=UTF8&ASIN=B079KC8Y4Z&Format=_SL250_&ID=AsinImage&MarketPlace=DE&ServiceVersion=20070822&WS=1&tag=werbeanzeige1-21&language=de_DE" ></a><img src="https://ir-de.amazon-adsystem.com/e/ir?t=werbeanzeige1-21&language=de_DE&l=li3&o=3&a=B079KC8Y4Z" width="1" height="1" border="0" alt="" style="border:none !important; margin:0px !important;" />

		if ( $access_tracking_id == '' ) {
			$access_tracking_id = 'empty';
		}

		$this->checklogon_v5( $access_website, $access_key, $access_secret_key, $access_tracking_id );

	}

	/**
	 * Sets itemIdType
	 *
	 * @param \Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\BrowseNodeAncestor $browsenodeancestor
	 *
	 * @return string $result
	 */
	private function getBrowseNodeTreeRec( $browsenodeancestor, &$nodes ) {
		if ( $browsenodeancestor == null ) {
			return '';
		}

		$nodes[ $browsenodeancestor->getId() ] = $browsenodeancestor->getDisplayName();

		if ( $browsenodeancestor->getAncestor() != null ) {
			$this->getBrowseNodeTreeRec( $browsenodeancestor->getAncestor(), $nodes );
		}
	}

	private function retrieve_browsenodes_v5( $keyword ) {

		$nodes = array();
		$items = null;
		try {
			$searchItemsRequest = new Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\SearchItemsRequest();
			$searchItemsRequest->setSearchIndex( 'All' );
			$searchItemsRequest->setKeywords( $keyword );
			$searchItemsRequest->setItemCount( 10 );
			$searchItemsRequest->setPartnerTag( $this->associateTag );
            if($this->languages_of_preference != null)
                $searchItemsRequest->setLanguagesOfPreference($this->languages_of_preference);
			$searchItemsRequest->setPartnerType( Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\PartnerType::ASSOCIATES );
			$searchItemsRequest->setResources(
				\Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\SearchItemsResource::getAllowableEnumValues()
			);
			$searchItemsRequest->setItemPage( 1 );

			$searchItemsResponse = $this->sendSearchRequest( $searchItemsRequest );

			if ( $searchItemsResponse->getSearchResult() != null && $searchItemsResponse->getSearchResult()->getItems() != null ) {
				$items = $searchItemsResponse->getSearchResult()->getItems();
			}

		} catch ( Amazon\ProductAdvertisingAPI\v1\ApiException $exception ) {
			$check = "API-Error: " . $exception->getCode() . " " . $exception->getMessage();

			if ( $exception->getResponseObject() instanceof Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\ProductAdvertisingAPIClientException ) {
				$errors = $exception->getResponseObject()->getErrors();
				foreach ( $errors as $error ) {
					$check = "Response-Error: " . $error->getCode() . " " . $error->getMessage();
				}
			} else {
				$check .= "Error response body: " . $exception->getResponseBody();
			}
		} catch ( Exception $exception ) {
			$check = "Error Message: " . $exception->getMessage(); //. ' ' . $exception->getTraceAsString();
		}

		if ( $items != null ) {
			foreach ( $items as $item ) {
				foreach ( $item->getBrowseNodeInfo()->getBrowseNodes() as $bnw ) {
					$this->getBrowseNodeTreeRec( $bnw->getAncestor(), $nodes );
				}
			}
		}

		return $nodes;
	}


	public function retrieve_browsenodes( $keyword ) {
		if ( $this->helper == null ) {
			throw new Exception( 'checklogon required' );
		}

			$nodes = $this->retrieve_browsenodes_v5( $keyword );


		$newNodes = array();

		foreach ( $nodes as $node => $value ) {
			if ( ! array_key_exists( $node, $newNodes ) ) {
				$newNodes[ $node ] = $value;
			}
		}

		return $newNodes;
	}

	private function retrieve_recursive_browsenodes( $parentBrowseNode ) {
		$nodes = array();
		if ( isset( $parentBrowseNode->Ancestors ) ) {
			foreach ( $parentBrowseNode->Ancestors as $browsenode ) {
				if ( ! isset( $browsenode->Name ) || ! is_string( $browsenode->Name ) ) {
					continue;
				}

				$nodes[ $browsenode->BrowseNodeId ] = $browsenode->Name;

				foreach ( $this->retrieve_recursive_browsenodes( $browsenode ) as $node => $value ) {
					$nodes[ $node ] = $value;
				}

				//array_push($nodes, $this->RecursiveBrowseNodes($browsenode));
			}
		}

		return $nodes;
	}


	private function quick_search_v5( $keyword, $searchType, $pagination ) {
		$products = new atkp_search_resp();
		$maxCount = 10;

		if ( $this->sitetripemode == 2) {

			if ( $searchType == 'product' ) {
				$products = $this->search_sitestripeproduct( $keyword, $searchType, $pagination );
			} else {
				$products->message = esc_html__( 'Search and import not supported. You enabled "sitestripe mode" in your amazon shop.', ATKP_PLUGIN_PREFIX );
			}
            return $products;
		}

		$items = array();
		try {
			//$searchType == 'ean'
			if ( $searchType == 'asin' || $searchType == 'articlenumber' ) {
				$getItemsRequest = new Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\GetItemsRequest();
				$getItemsRequest->setItemIds( explode( ',', $keyword ) );
				$getItemsRequest->setItemIdType( ( $searchType == 'ean' ? 'EAN' : 'ASIN' ) );
				$getItemsRequest->setPartnerTag( $this->associateTag );
				if($this->languages_of_preference != null)
					$getItemsRequest->setLanguagesOfPreference($this->languages_of_preference);
				$getItemsRequest->setPartnerType( \Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\PartnerType::ASSOCIATES );
				$getItemsRequest->setResources(
					\Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\GetItemsResource::getAllowableEnumValues()
				);

				$getItemsResponse = $this->sendGetItemsRequest( $getItemsRequest );

				if ( $getItemsResponse->getItemsResult() != null && $getItemsResponse->getItemsResult()->getItems() != null ) {
					$items = $getItemsResponse->getItemsResult()->getItems();
				}


			} else {
				$searchItemsRequest = new Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\SearchItemsRequest();
				$searchItemsRequest->setSearchIndex( 'All' );
				$searchItemsRequest->setKeywords( $keyword );
				$searchItemsRequest->setItemCount( $maxCount );
				$searchItemsRequest->setPartnerTag( $this->associateTag );
				if($this->languages_of_preference != null)
					$searchItemsRequest->setLanguagesOfPreference($this->languages_of_preference);
				$searchItemsRequest->setPartnerType( Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\PartnerType::ASSOCIATES );
				$searchItemsRequest->setResources(
					\Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\SearchItemsResource::getAllowableEnumValues()
				);
				$searchItemsRequest->setItemPage( $pagination );


				$searchItemsResponse = $this->sendSearchRequest( $searchItemsRequest );

				if ( $searchItemsResponse->getSearchResult() != null ) {
					$products->pagecount = ceil( floatval( $searchItemsResponse->getSearchResult()->getTotalResultCount() ) / floatval( $maxCount ) );
					$products->total     = intval( $searchItemsResponse->getSearchResult()->getTotalResultCount() );
				}
				$products->currentpage = intval( $pagination );


				if ( $searchItemsResponse->getSearchResult() != null && $searchItemsResponse->getSearchResult()->getItems() != null ) {
					$items = $searchItemsResponse->getSearchResult()->getItems();
				}
			}
		} catch ( Amazon\ProductAdvertisingAPI\v1\ApiException $exception ) {
			$check = "API-Error: " . $exception->getCode() . " " . $exception->getMessage();

			if ( $exception->getResponseObject() instanceof Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\ProductAdvertisingAPIClientException ) {
				$errors = $exception->getResponseObject()->getErrors();
				foreach ( $errors as $error ) {
					$check = "Response-Error: " . $error->getCode() . " " . $error->getMessage();
				}
			} else {
				$check .= "Error response body: " . $exception->getResponseBody();
			}
		} catch ( Exception $exception ) {
			$check = "Error Message: " . $exception->getMessage(); //. ' ' . $exception->getTraceAsString();
		}

		if ( ! empty( $check ) ) {
			throw new Exception( esc_html__($check, ATKP_PLUGIN_PREFIX) );
		}


		foreach ( $items as $result ) {
			if ( $result->getASIN() != null ) {
				$product = array();

				if ( $result->getImages() != null && $result->getImages()->getPrimary() != null ) {
					$product['imageurl'] = $this->checkimageurl( $result->getImages()->getPrimary()->getSmall()->getURL(), 'small' );
				}

				$product['articlenumber'] = $product['asin'] = $result->getASIN();
				//var_dump($result->getExternalIds());exit;

				$ean_full = '';
				if ( $result->getItemInfo()->getExternalIds() != null && $result->getItemInfo()->getExternalIds()->getEANs() != null ) {
					foreach ( $result->getItemInfo()->getExternalIds()->getEANs()->getDisplayValues() as $ean ) {
						if ( $ean_full != '' ) {
							$ean_full .= ',';
						}

						$ean_full .= $ean;
					}
				}
				$product['ean'] = $ean_full;

				if ( $result->getDetailPageURL() != null ) {
					$product['producturl'] = $result->getDetailPageURL();
				}
				if ( $result->getItemInfo() != null && $result->getItemInfo()->getTitle() != null && $result->getItemInfo()->getTitle()->getDisplayValue() != null ) {
					$product['title'] = htmlspecialchars( $result->getItemInfo()->getTitle()->getDisplayValue() );
				}
				if ( $result->getOffers() != null ) {
					foreach ( $result->getOffers()->getListings() as $listing ) {
						$product['availability'] = $listing->getAvailability();

						if ( $listing->getPrice() != null ) {
							$product['listprice'] = $listing->getPrice()->getSavings() == null ? '' : $listing->getPrice()->getSavings()->getDisplayAmount();
							$product['saleprice'] = $listing->getPrice()->getDisplayAmount();
							break;
						}
					}


					if ( $result->getOffers()->getSummaries() != null && $product['saleprice'] == '' ) {
						foreach ( $result->getOffers()->getSummaries() as $summary ) {
							$product['saleprice'] = $summary->getLowestPrice() != null ? $summary->getLowestPrice()->getDisplayAmount() : '';
							$product['listprice'] = '';
							break;
						}
					}
				}


				$description = '';
				if ( $result->getItemInfo()->getFeatures() != null && $result->getItemInfo()->getFeatures()->getDisplayValues() != null ) {
					$description = implode( '<br />', $result->getItemInfo()->getFeatures()->getDisplayValues() );
				}

				$product['features'] = $description != '' && strlen( $description ) > 350 ? substr( $description, 0, 350 ) : $description;


				//$product['availability'] = $result->Offers->Offer->OfferListing->Availability;

				array_push( $products->products, $product );
			}
		}


		return $products;
	}

	public function quick_search( $keyword, $searchType, $pagination = 1 ) {
		if ( $this->helper == null ) {
			throw new Exception( 'checklogon required' );
		}

//			try {
				$products = $this->quick_search_v5( $keyword, $searchType, $pagination );
//			} catch ( Exception $exception ) {
//
//				if ( ATKPTools::str_contains( $exception->getMessage(), 'The request was denied due to request throttling.', false ) ) {
//					sleep( ATKP_AMZ_WAIT );
//					$products = $this->quick_search_v5( $keyword, $searchType, $pagination );
//				} else {
//					throw $exception;
//				}
//			}


		return $products;
	}

	private function checkurl( $url, $enable_ssl = null ) {

		if ( $enable_ssl == null ) {
			$enable_ssl = $this->enable_ssl;
		}

		if ( $enable_ssl ) {
			$url = str_replace( 'http://', 'https://', $url );
		}

		return $url;
	}

	private function checkimageurl( $url, $size ) {

		//if ( $this->enable_ssl ) {
		//	$url = str_replace( 'http://ecx.images-amazon.com', 'https://images-na.ssl-images-amazon.com', $url );
		//}

		if ( $size == 'small' && $this->smallimagesize > 0 ) {
			$url = str_replace( 'SL75', 'SL' . $this->smallimagesize, $url );
		}
		if ( $size == 'medium' && $this->mediumimagesize > 0 ) {
			$url = str_replace( 'SL160', 'SL' . $this->mediumimagesize, $url );
		}

		return $url;
	}

	private function checkimageurl_sitestripe( $url, $size ) {

		//if ( $this->enable_ssl ) {
		//	$url = str_replace( 'http://ecx.images-amazon.com', 'https://images-na.ssl-images-amazon.com', $url );
		//}

		if ( $size == 'small' && $this->smallimagesize > 0 ) {
			$url = str_replace( '._AC_AC_SR98,95_', '.SL' . $this->smallimagesize, $url );
		}
		if ( $size == 'medium' && $this->mediumimagesize > 0 ) {
			$url = str_replace( '._AC_AC_SR98,95_', '.SL' . $this->mediumimagesize, $url );
		}
		if ( $size == 'large' ) {
			$url = str_replace( '._AC_AC_SR98,95_', '', $url );
		}

		return $url;
	}

	private function checkResponse( $response ) {
		$requestHelp = null;
		if ( isset( $response->BrowseNodes->Request ) ) {
			$requestHelp = $response->BrowseNodes->Request;
		} else if ( isset( $response->Items->Request ) ) {
			$requestHelp = $response->Items->Request;
		}

		//echo('$response: ' .serialize($response));

		$message = '';

		if ( isset( $requestHelp->IsValid ) && $requestHelp->IsValid != 'True' ) {

			$message .= 'Invalid Request. IsValid: ' . $requestHelp->IsValid;

			//echo('xx '.serialize($requestHelp->Errors->Error));

		}

		if ( isset( $requestHelp->Errors->Error ) ) {

			if ( isset( $requestHelp->Errors->Error->Code ) && $requestHelp->Errors->Error->Code != '' ) {
				$error = $requestHelp->Errors->Error;
				if ( $message != '' ) {
					$message .= ' ';
				}
				$message .= 'ErrorCode: ' . $error->Code;
				if ( $message != '' ) {
					$message .= ' ';
				}
				$message .= 'Message: ' . $error->Message;
			} else {
				foreach ( $requestHelp->Errors->Error as $error ) {
					if ( $message != '' ) {
						$message .= ' ';
					}
					$message .= 'ErrorCode: ' . $error->Code;
					if ( $message != '' ) {
						$message .= ' ';
					}
					$message .= 'Message: ' . $error->Message;
				}
			}
		}

		return $message;
	}

	private function parse_department_file( $filename ) {
		$departments = array();

		if ( ( $handle = fopen( ATKP_AMAZON_PLUGIN_DIR . '/files/' . $filename, "r" ) ) !== false ) {
			while ( ( $data = fgetcsv( $handle, 1000, ";" ) ) !== false ) {

				$departments[ $data[0] ] = array(
					'caption'    => $data[1],
					'sortvalues' => array(
						'AvgCustomerReviews' => esc_html__( 'Sorts results according to average customer reviews', ATKP_PLUGIN_PREFIX ),
						'Featured'           => esc_html__( 'Sorts results with featured items having higher rank', ATKP_PLUGIN_PREFIX ),
						'NewestArrivals'     => esc_html__( 'Sorts results with according to newest arrivals', ATKP_PLUGIN_PREFIX ),
						'Price:HighToLow'    => esc_html__( 'Sorts results according to most expensive to least expensive', ATKP_PLUGIN_PREFIX ),
						'Price:LowToHigh'    => esc_html__( 'Sorts results according to least expensive to most expensive', ATKP_PLUGIN_PREFIX ),
						'Relevance'          => esc_html__( 'Sorts results with relevant items having higher rank', ATKP_PLUGIN_PREFIX ),
					)
				);

			}
			fclose( $handle );
		}

		return $departments;
	}

	private function retrieve_departments_v5() {
		switch ( $this->country ) {
			case 'de':
				return $this->parse_department_file( 'germany.csv' );
				break;
			default:
			case 'en':
				return $this->parse_department_file( 'unitedstates.csv' );
				break;
			case 'co.uk':
				return $this->parse_department_file( 'unitedkingdom.csv' );
				break;
			case 'ca':
				return $this->parse_department_file( 'canada.csv' );
				break;
			case 'fr':
				return $this->parse_department_file( 'france.csv' );
				break;
			case 'com.be':
				return $this->parse_department_file( 'belgium.csv' );
				break;
			case 'co.jp':
				return $this->parse_department_file( 'japan.csv' );
				break;
			case 'it':
				return $this->parse_department_file( 'italy.csv' );
				break;
			case 'cn':
			case 'es':
				return $this->parse_department_file( 'spain.csv' );
				break;
			case 'in':
				return $this->parse_department_file( 'india.csv' );
				break;
			case 'au':
				return $this->parse_department_file( 'australia.csv' );
				break;
			case 'com.br':
				return $this->parse_department_file( 'brazil.csv' );
				break;
			case 'com.mx':
				return $this->parse_department_file( 'mexico.csv' );
				break;
			case 'com.tr':
				return $this->parse_department_file( 'turkey.csv' );
				break;
			case 'ae':
				return $this->parse_department_file( 'emirates.csv' );
				break;
			case 'pl':
				return $this->parse_department_file( 'poland.csv' );
				break;
            case 'nl':
	            return $this->parse_department_file( 'netherlands.csv' );
                break;
		}
	}

	public function retrieve_departments() {
		if ( $this->helper == null ) {
			throw new Exception( 'checklogon required' );
		}

			$departments = $this->retrieve_departments_v5();


		return $departments;
	}


	private function retrieve_filters_v5() {
		$durations = array(
			'' => esc_html__( 'Not selected', ATKP_PLUGIN_PREFIX ),

			'Actor'                 => esc_html__( 'Actor', ATKP_PLUGIN_PREFIX ),
			'Artist'                => esc_html__( 'Artist', ATKP_PLUGIN_PREFIX ),
			'Author'                => esc_html__( 'Author', ATKP_PLUGIN_PREFIX ),
			'Availability'          => esc_html__( 'Availability', ATKP_PLUGIN_PREFIX ),
			'Brand'                 => esc_html__( 'Brand', ATKP_PLUGIN_PREFIX ),
			'BrowseNode'            => esc_html__( 'BrowseNode', ATKP_PLUGIN_PREFIX ),
			'Condition'             => esc_html__( 'Condition', ATKP_PLUGIN_PREFIX ),
			'CurrencyOfPreference'  => esc_html__( 'Currency Of Preference', ATKP_PLUGIN_PREFIX ),
			'DeliveryFlags'         => esc_html__( 'DeliveryFlags', ATKP_PLUGIN_PREFIX ),
			'LanguagesOfPreference' => esc_html__( 'Languages Of Preference', ATKP_PLUGIN_PREFIX ),
			'Marketplace'           => esc_html__( 'Marketplace', ATKP_PLUGIN_PREFIX ),

			'MaximumPrice'     => esc_html__( 'Maximum price', ATKP_PLUGIN_PREFIX ),
			'MinimumPrice'     => esc_html__( 'Minimum price', ATKP_PLUGIN_PREFIX ),
			'MerchantId'       => esc_html__( 'Merchant Id', ATKP_PLUGIN_PREFIX ),
			'MinReviewsRating' => esc_html__( 'Min Reviews Rating', ATKP_PLUGIN_PREFIX ),
			'MinPercentageOff' => esc_html__( 'Min percentage off', ATKP_PLUGIN_PREFIX ),

			'Keywords'    => esc_html__( 'Keywords', ATKP_PLUGIN_PREFIX ),
			'SearchIndex' => esc_html__( 'SearchIndex', ATKP_PLUGIN_PREFIX ),
			'Sort'        => esc_html__( 'Sort', ATKP_PLUGIN_PREFIX ),

			'Title' => esc_html__( 'Title', ATKP_PLUGIN_PREFIX ),
		);

		return $durations;
	}

	public function retrieve_filters() {

			$durations = $this->retrieve_filters_v5();


		return $durations;
	}

	public function retrieve_products( $asins, $id_type = 'ASIN' ) {

			//try {
				return $this->retrieve_products_v5( $asins, $id_type );
//			} catch ( Exception $exception ) {
//
//				if ( ATKPTools::str_contains( $exception->getMessage(), 'The request was denied due to request throttling.', false ) ) {
//					sleep( ATKP_AMZ_WAIT );
//
//					return $this->retrieve_products_v5( $asins, $id_type );
//				} else {
//					throw $exception;
//				}
//			}

	}

	private $second_try_api = false;

	private function search_sitestripeproduct( $keyword, $searchType, $pagination ) {

		$license = ATKP_LicenseController::get_module_license( 'amaznoapi' );

		try {
			$url = 'https://api.affiliate-toolkit.com/amazon/noapi.php?keywords=' . urlencode( $keyword ) . '&tag=' . $this->associateTag . '&country=' . strtoupper( $this->country ) . '&key=' . $license . '&page_number=' . $pagination;

			$page       = '';
			$statusCode = null;

			if ( function_exists( 'wp_remote_get' ) ) {

				$response = wp_remote_get( $url );

				if ( function_exists( 'is_wp_error' ) && ! is_wp_error( $response ) ) {

					// Success
					if ( isset( $response['response']['code'] ) ) {
						$statusCode = $response['response']['code'];
					}

					if ( isset( $response['body'] ) ) {
						$page = $response['body'];
					}
				}
			}

			$products   = new atkp_search_resp();
			$products_x = array();

			$products->currentpage = 0;
			$products->pagecount   = 0;
			$products->total       = 0;

			if ( 200 == $statusCode ) {
				$xx = json_decode( $page );


				if ( $xx != null && isset( $xx->products ) ) {
					foreach ( $xx->products as $p ) {
						$xxd             = array();
						$xxd['imageurl'] = $p->imageurl;

						$xxd['asin'] = $p->asin;
						//$product['ean'] = $ean_full;
						$xxd['producturl'] = $p->producturl;
						$xxd['title']      = $p->title;

						//$product['availability'] = $listing->getAvailability();

						$xxd['saleprice'] = isset( $p->saleprice ) ? $p->saleprice : '';
						$xxd['listprice'] = '';

						$products_x[] = $xxd;
					}
				}

				$products->products = $products_x;
				if ( isset( $xx->data->currentpage ) ) {
					$products->currentpage = intval( $xx->data->currentpage );
					$products->pagecount   = intval( $xx->data->pagecount );
					$products->total       = intval( $xx->data->total );
				}
			} else {
				if ( ! $this->second_try_api ) {
					$this->second_try_api = true;
					sleep( 1 );

					return $this->search_sitestripeproduct( $keyword, $searchType, $pagination );
				}

				echo esc_html__( 'Status code: ' . $statusCode, ATKP_PLUGIN_PREFIX );
				echo esc_html__( '<pre>' . esc_url( $url ) . '</pre>', ATKP_PLUGIN_PREFIX );
				echo esc_html__( '<pre>' . $page . '</pre>', ATKP_PLUGIN_PREFIX );

			}

			return $products;

		} catch ( Exception $e ) {
			$titlecheck = $e->getMessage();

			$products          = new atkp_search_resp();
			$products->message = $titlecheck;

			return $products;
		}

	}

	/**
	 * @param $asins
	 *
	 * @return atkp_response
	 */
	private function load_sitestripeproduct( $asins ) {
		$atkpresponse = new atkp_response();

		//https://ws-eu.amazon-adsystem.com/widgets/q?ServiceVersion=20070822&OneJS=1&Operation=GetAdHtml&MarketPlace=DE&ad_type=product_link&tracking_id=werbeanzeige1-21&marketplace=amazon&region=DE&asins=B07H2BRGPS

		$license = ATKP_LicenseController::get_module_license( 'amaznoapi');

		foreach ( $asins as $asin ) {

			try {
				$url = 'https://api.affiliate-toolkit.com/amazon/noapi.php?asin=' . $asin . '&tag=' . $this->associateTag . '&country=' . strtoupper( $this->country ) . '&key=' . $license;
                $page = '';

				if ( function_exists( 'wp_remote_get' ) ) {

					$response   = wp_remote_get( $url, array(
						'timeout'    => 20,
					) );
					$statusCode = null;

					if ( function_exists( 'is_wp_error' ) && ! is_wp_error( $response ) ) {

						// Success
						if ( isset( $response['response']['code'] ) ) {
							$statusCode = $response['response']['code'];
						}

						if ( '200' == $statusCode ) {
							$page = $response['body'];
						}
					}
				}

                $xx = json_decode($page );

				if ( $xx != null && $xx->productitem != null) {
					$myproduct = new atkp_product();

					foreach ( $xx->productitem->data as $key => $val ) {
						$myproduct->$key = $val;
					}
					$myproduct->updatedon = ATKPTools::get_currenttime();
					$myproduct->shopid    = $this->shopid;
					$myproduct->asin      = $asin;

					$product = new atkp_response_item();
					if ( isset( $xx->errormessage ) ) {
						$product->errormessage = $xx->errormessage;
					}
					$product->uniqueid   = $asin;
					$product->uniquetype = 'ASIN';

					$product->productitem = $myproduct;

					array_push( $atkpresponse->responseitems, $product );
				}

			} catch ( Exception $e ) {
				if ( ! $this->second_try_api ) {
					$this->second_try_api = true;
					sleep( 1 );

					return $this->load_sitestripeproduct( $asins );
				}
				$titlecheck = $e->getMessage();

				$product               = new atkp_response_item();
				$product->errormessage = 'product error: ' . $titlecheck;
				$product->uniqueid     = $asin;
				$product->uniquetype   = 'ASIN';

				array_push( $atkpresponse->responseitems, $product );
			}
		}


		return $atkpresponse;
	}

	public function retrieve_products_v5( $asins, $id_type ) {
		$atkpresponse = new atkp_response();

		if ( count( $asins ) == 0 ) {
			return $atkpresponse;
		}

		switch ( strtoupper($id_type) ) {
			case 'TITLE':
			case "EAN":

                if ( $this->sitetripemode == 2) {
                    return $atkpresponse;
                }

				foreach ( $asins as $title ) {
					$items      = null;
					$titlecheck = '';
					try {
						$searchItemsRequest = new Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\SearchItemsRequest();
						$searchItemsRequest->setSearchIndex( 'All' );
						$searchItemsRequest->setKeywords( $title );
						$searchItemsRequest->setItemCount( 2 );
						if($this->languages_of_preference != null)
							$searchItemsRequest->setLanguagesOfPreference($this->languages_of_preference);
						$searchItemsRequest->setPartnerTag( $this->associateTag );
						$searchItemsRequest->setPartnerType( Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\PartnerType::ASSOCIATES );
						$searchItemsRequest->setResources(
							\Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\SearchItemsResource::getAllowableEnumValues()
						);
						$searchItemsRequest->setItemPage( 1 );

						$searchItemsResponse = $this->sendSearchRequest( $searchItemsRequest );

						if ( $searchItemsResponse->getSearchResult() != null && $searchItemsResponse->getSearchResult()->getItems() != null ) {
							$items = $searchItemsResponse->getSearchResult()->getItems();
						}

					} catch ( Amazon\ProductAdvertisingAPI\v1\ApiException $exception ) {
						$titlecheck = "API-Error: " . $exception->getCode() . " " . $exception->getMessage();

						if ( $exception->getResponseObject() instanceof Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\ProductAdvertisingAPIClientException ) {
							$errors = $exception->getResponseObject()->getErrors();
							foreach ( $errors as $error ) {
								$titlecheck = "Response-Error: " . $error->getCode() . " " . $error->getMessage();
							}
						} else {
							$titlecheck .= "Error response body: " . $exception->getResponseBody();
						}
					} catch ( Exception $exception ) {

                        //NoResults
						if ( !ATKPTools::str_contains( $exception->getMessage(), 'NoResults: No results found for your request.', false ) ) {
							$titlecheck = "Error Message: " . $exception->getMessage(); //. ' ' . $exception->getTraceAsString();
						}
					}

					$added = false;

					if ( $titlecheck != '') {

						$responseitem               = new atkp_response_item();
						$responseitem->errormessage = $titlecheck;

						$responseitem->uniqueid   = $title;
						$responseitem->uniquetype = $id_type;

						array_push( $atkpresponse->responseitems, $responseitem );
						$added = true;

					} else {

						if ( $items != null ) {
							foreach ( $items as $result2 ) {
								if ( $result2->getASIN() == null || $added ) {
									continue;
								}

								$result = $result2;
								break;
							}


							if ( $result != null ) {
								$responseitem              = new atkp_response_item();
								$responseitem->productitem = $this->fill_product_v5( $result );

								$responseitem->uniqueid   = $title;
								$responseitem->uniquetype = $id_type;

								array_push( $atkpresponse->responseitems, $responseitem );
								$added = true;
							}
						}

					}
				}

				break;
			case 'ARTICLENUMBER':
			case 'ASIN':

				if ( $this->sitetripemode == 2 && $id_type == 'ASIN' ) {
					//bevorzugte nutzung
					$atkpresponse = $this->load_sitestripeproduct( $asins );

					return $atkpresponse;
				}

				foreach ( $asins as $asin ) {
					$items = array();
					$check = '';
					try {
						$getItemsRequest = new Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\GetItemsRequest();
						$getItemsRequest->setItemIds( array( $asin ) );
						$getItemsRequest->setItemIdType( ( 'ASIN' ) );
						if($this->languages_of_preference != null)
							$getItemsRequest->setLanguagesOfPreference($this->languages_of_preference);
						$getItemsRequest->setPartnerTag( $this->associateTag );
						$getItemsRequest->setPartnerType( \Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\PartnerType::ASSOCIATES );
						$getItemsRequest->setResources(
							\Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\GetItemsResource::getAllowableEnumValues()
						);


						$getItemsResponse = $this->sendGetItemsRequest( $getItemsRequest );

						if ( $getItemsResponse->getItemsResult() != null && $getItemsResponse->getItemsResult()->getItems() != null ) {
							$items = $getItemsResponse->getItemsResult()->getItems();
						}

					} catch ( Amazon\ProductAdvertisingAPI\v1\ApiException $exception ) {
						$check = "API-Error: " . $exception->getCode() . " " . $exception->getMessage();

						if ( $exception->getResponseObject() instanceof Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\ProductAdvertisingAPIClientException ) {
							$errors = $exception->getResponseObject()->getErrors();
							foreach ( $errors as $error ) {
								$check = "Response-Error: " . $error->getCode() . " " . $error->getMessage();
							}
						} else {
							$check .= "Error response body: " . $exception->getResponseBody();
						}
					} catch ( Exception $exception ) {
						$check = "Error Message: " . $exception->getMessage(); //. ' ' . $exception->getTraceAsString();
					}


					$added = false;

					if ( ! empty( $check ) && $this->sitetripemode == 3 && strtoupper($id_type) == 'ASIN' ) {
						$atkpresponse2 = $this->load_sitestripeproduct( array( $asin ) );

						array_push( $atkpresponse->responseitems, $atkpresponse2->responseitems[0] );
						$added = true;
					} else if ( $check != '' || $items == null ) {

						$responseitem               = new atkp_response_item();
						$responseitem->errormessage = empty( $check ) ? 'product not found' : $check;

						$responseitem->uniqueid   = $asin;
						$responseitem->uniquetype = $id_type;

						array_push( $atkpresponse->responseitems, $responseitem );
						$added = true;

					} else {

						if ( $items != null ) {
							$result = null;
							foreach ( $items as $result2 ) {
								if ( $result2->getASIN() == null || $added ) {
									continue;
								}

								$result = $result2;
								break;
							}
							if ( $result != null ) {
								$responseitem              = new atkp_response_item();
								$responseitem->productitem = $this->fill_product_v5( $result );

								$responseitem->uniqueid   = $asin;
								$responseitem->uniquetype = $id_type;


								array_push( $atkpresponse->responseitems, $responseitem );
								$added = true;
							}
						}
					}

				}


				break;
			default:
				throw new Exception( esc_html__( 'unknown id_type: ' . $id_type, ATKP_PLUGIN_PREFIX ) );
				break;
		}

		return $atkpresponse;
	}


	/**
	 * Sets itemIdType
	 *
	 * @param atkp_product $myproduct
	 * @param \Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\Item $result
	 *
	 * @return $myproduct atkp_product
	 */
	public function load_variations_v5( $myproduct, $result ) {
		$variations = array();
		$dimmension = array();


		$parentasin = $result->getParentASIN();
		if ( $parentasin == '' ) {
			//try to load the variations
			try {
				$getItemsRequest = new Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\GetVariationsRequest();
				$getItemsRequest->setASIN( $result->getASIN() );
				$getItemsRequest->setVariationCount( 10 );
				$getItemsRequest->setPartnerTag( $this->associateTag );
				if($this->languages_of_preference != null)
					$getItemsRequest->setLanguagesOfPreference($this->languages_of_preference);
				$getItemsRequest->setPartnerType( \Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\PartnerType::ASSOCIATES );

				$getItemsRequest->setResources(
					\Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\GetVariationsResource::getAllowableEnumValues()
				);


				$getItemsResponse = $this->sendVariationRequest( $getItemsRequest );


				if ( $getItemsResponse->getVariationsResult() != null && $getItemsResponse->getVariationsResult()->getItems() != null ) {

					foreach ( $getItemsResponse->getVariationsResult()->getItems() as $variationItem ) {
						$att = $variationItem->getVariationAttributes();

						foreach ( $att as $tmp ) {
							$dimmension[ $tmp['name'] ] = apply_filters( 'atkp_variation_name', $tmp['name'] );
						}

						$varpd = $this->fill_product_v5( $variationItem, $result );


						$dimmfullname = array();

						foreach ( $att as $tmp ) {
							$dimmfullname[ $tmp['name'] ] = $tmp['value'];
						}

						$varpd->variationname = $dimmfullname;
						array_push( $variations, $varpd );

					}
				}
			} catch ( Exception $x ) {

			}
		}
		$myproduct->variationname = $dimmension;
		$myproduct->variations    = $variations;

		return $myproduct;
	}

	private function allowedCondition($condition) {
		//Any       	Offer Listings for items across any condition
		//New	        Offer Listings for New items
		//Used	        Offer Listings for Used items
		//Collectible	Offer Listings for Collectible items
		//Refurbished	Offer Listings for Certified Refurbished items

        return true;
/*
		if($condition == '' || $condition == null)
		    return true;

		if(ATKPTools::str_contains($condition, 'New', true)) {
		    return true;
        } else if($this->onlynew)
            return false;
		else
		    return true;
*/
    }

	/**
	 * Sets itemIdType
	 *
	 * @param \Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\Item $result
	 *
	 * @return $myproduct atkp_product
	 */
	private function fill_product_v5( $result, $parentResult = null ) {

		$myproduct            = new atkp_product();
		$myproduct->updatedon = ATKPTools::get_currenttime();
		$myproduct->shopid    = $this->shopid;

		//store the ASIN code in case we need it
		$myproduct->asin       = $result->getASIN();
		$myproduct->parentasin = $result->getParentASIN();

		//TODO: Variationen einbauen mit optionsfeld im shop

		if ( $parentResult == null && $this->load_variations ) {
			$myproduct = $this->load_variations_v5( $myproduct, $result );
		}

		if ( $result->getDetailPageURL() != null ) {
			$myproduct->producturl = urldecode( $result->getDetailPageURL() );
			//für 100% im titel - replace muss geklärt werden..
			$myproduct->producturl = str_replace( '%', '%25', $myproduct->producturl );

			$myproduct->addtocarturl = $this->checkurl( 'http://www.amazon.' . $this->country . '/gp/aws/cart/add.html?AWSAccessKeyId=' . $this->accessKey . '&AssociateTag=' . $this->associateTag . '&ASIN.1=' . $myproduct->asin . '&Quantity.1=1' );
		} else if ( $parentResult != null && $parentResult->getDetailPageURL() != null ) {

			$myproduct->producturl = urldecode( $parentResult->getDetailPageURL() );
			//für 100% im titel - replace muss geklärt werden..
			$myproduct->producturl = str_replace( '%', '%25', $myproduct->producturl );

			$myproduct->producturl   = $this->checkurl( 'https://www.amazon.' . $this->country . '/dp/' . $myproduct->asin . '?tag=' . $this->associateTag );
			$myproduct->addtocarturl = $this->checkurl( 'https://www.amazon.' . $this->country . '/gp/aws/cart/add.html?AWSAccessKeyId=' . $this->accessKey . '&AssociateTag=' . $this->associateTag . '&ASIN.1=' . $parentResult->ASIN . '&Quantity.1=1' );

		} else {
			$myproduct->producturl   = '';
			$myproduct->addtocarturl = '';
		}


		$images = array();
		if ( $result->getImages() != null ) {
			if ( $result->getImages()->getPrimary() != null ) {
				$myproduct->smallimageurl  = $this->checkimageurl( $result->getImages()->getPrimary()->getSmall()->getURL(), 'small' );
				$myproduct->mediumimageurl = $this->checkimageurl( $result->getImages()->getPrimary()->getMedium()->getURL(), 'medium' );
				$myproduct->largeimageurl  = $this->checkimageurl( $result->getImages()->getPrimary()->getLarge()->getURL(), 'large' );
			}

			if ( $result->getImages()->getVariants() != null ) {
				foreach ( $result->getImages()->getVariants() as $variant ) {
					if ( $variant->getLarge() == null && $variant->getMedium() == null && $variant->getSmall() == null ) {
						continue;
					}

					$udf     = new atkp_product_image();
					$udf->id = uniqid();
					if ( $variant->getSmall() != null ) {
						$udf->smallimageurl = $this->checkimageurl( $variant->getSmall()->getURL(), 'small' );
					}
					if ( $variant->getMedium() != null ) {
						$udf->mediumimageurl = $this->checkimageurl( $variant->getMedium()->getURL(), 'medium' );
					}
					if ( $variant->getLarge() != null ) {
						$udf->largeimageurl = $this->checkimageurl( $variant->getLarge()->getURL(), 'large' );
					}

					array_push( $images, $udf );
				}
			}
		}
		$myproduct->images = $images;

		if($result->getCustomerReviews() != null) {
		    if($result->getCustomerReviews()->getStarRating() != null)
		        $myproduct->rating = $result->getCustomerReviews()->getStarRating()->getValue();
		    if($result->getCustomerReviews() != null)
    			$myproduct->reviewcount = $result->getCustomerReviews()->getCount();
		}

		if ( $this->load_customer_reviews && $myproduct->reviewcount == 0) {

			$averageRating = 0;
			$totalReviews  = 0;

			$this->get_customer_rating_api( $myproduct->asin, $averageRating, $totalReviews );

			$myproduct->rating      = $averageRating;
			$myproduct->reviewcount = $totalReviews;

		}

		$myproduct->customerreviewurl = $this->checkurl( 'http://www.amazon.' . $this->country . '/product-reviews/' . $myproduct->asin . '/?tag=' . $this->associateTag );

		$description = '';
		$features = '';

		if ( $result->getItemInfo()->getFeatures() != null && $result->getItemInfo()->getFeatures()->getDisplayValues() != null ) {
			foreach ( $result->getItemInfo()->getFeatures()->getDisplayValues() as $feature ) {
				$features .= '<li>' . $feature . '</li>';
				$description .= $feature.' <br />';
			}
		}

		$myproduct->features    = $features == '' ? '' : '<ul>' . $features . '</ul>';
		$myproduct->description = $description;
		if ( $result->getItemInfo() != null && $result->getItemInfo()->getTitle() != null && $result->getItemInfo()->getTitle()->getDisplayValue() != null ) {
			$myproduct->title = htmlentities( $result->getItemInfo()->getTitle()->getDisplayValue() );
		} else {
			$myproduct->title = '';
		}

        if($result->getItemInfo() != null && $result->getItemInfo()->getProductInfo() != null) {
            $productInfo = $result->getItemInfo()->getProductInfo();

            if($productInfo->getColor() != null)
                $myproduct->customfields["a_color"] = $productInfo->getColor()->getDisplayValue();

	        if($productInfo->getItemDimensions() != null) {
                if( $productInfo->getItemDimensions()->getHeight() != null)
		            $myproduct->customfields["a_height"] = round($productInfo->getItemDimensions()->getHeight()->getDisplayValue(), 2). ' '.($productInfo->getItemDimensions()->getHeight()->getUnit());
                if($productInfo->getItemDimensions()->getLength() != null)
		            $myproduct->customfields["a_length"] = round($productInfo->getItemDimensions()->getLength()->getDisplayValue(),2). ' '.($productInfo->getItemDimensions()->getLength()->getUnit());
                if($productInfo->getItemDimensions()->getWidth() != null)
		            $myproduct->customfields["a_width"] = round($productInfo->getItemDimensions()->getWidth()->getDisplayValue(), 2). ' '.($productInfo->getItemDimensions()->getWidth()->getUnit());
                if($productInfo->getItemDimensions()->getWeight() != null)
		            $myproduct->customfields["a_weight"] = round($productInfo->getItemDimensions()->getWeight()->getDisplayValue(), 2) . ' '.($productInfo->getItemDimensions()->getWeight()->getUnit());
	        }
        }

		//preise laden

		if ( $result->getOffers() != null ) {
			$offerlisting = null;
			if($result->getOffers()->getListings() != null) {
				foreach ( $result->getOffers()->getListings() as $listing ) {
					if ( $listing->getIsBuyBoxWinner() &&
                         $this->allowedCondition( $listing->getCondition() == null ? null : $listing->getCondition()->getValue() ) ) {
						$offerlisting = $listing;
						break;
					}
				}

				if ( $offerlisting == null) {
					$listings = array();
					foreach ( $result->getOffers()->getListings() as $list ) {
						if ( $this->allowedCondition( $list->getCondition() == null ? null : $list->getCondition()->getValue() ) ) {
							$listings[] = $list;
						}
					}

					$offerlisting = reset( $listings );
				}
			}

			$myproduct->iswarehouse = false;
			if ( $offerlisting && $offerlisting != null ) {
				if($offerlisting->getDeliveryInfo() != null) {
					$myproduct->isprime = $offerlisting->getDeliveryInfo()->getIsPrimeEligible();
					if ( $offerlisting->getDeliveryInfo()->getShippingCharges() != null && count( $offerlisting->getDeliveryInfo()->getShippingCharges() ) > 0 ) {
						$myproduct->shipping = $offerlisting->getDeliveryInfo()->getShippingCharges()[0]->getDisplayAmount();
					}
				}

				if($offerlisting->getMerchantInfo() != null) {
				    if($offerlisting->getMerchantInfo()->getName() == 'Amazon Warehouse') {
					    $myproduct->iswarehouse = true;
                    }
                }

                if($offerlisting->getPrice() != null && $offerlisting->getPrice()->getPricePerUnit() > 0) {
                    $displayval = $offerlisting->getPrice()->getDisplayAmount();
                    $raw = ATKPTools::get_string_between($displayval, '(', ')');

	                $myproduct->baseprice      = $raw;
	                $myproduct->basepricefloat = $offerlisting->getPrice() == null ? 0 : $offerlisting->getPrice()->getPricePerUnit();

                    $parts = explode(' / ', $raw);

                    if(count($parts) > 0) {
	                    $myproduct->baseunit = $parts[1];
                    }
                }



				$myproduct->saleprice      = $offerlisting->getPrice() == null ? '' : $offerlisting->getPrice()->getDisplayAmount();
				$myproduct->salepricefloat = $offerlisting->getPrice() == null ? 0 : $offerlisting->getPrice()->getAmount();
				$myproduct->unitpricefloat = $offerlisting->getPrice() == null || $offerlisting->getPrice()->getPricePerUnit() == null ? 0 : $offerlisting->getPrice()->getPricePerUnit();

				if ( $offerlisting->getPrice() != null && $offerlisting->getPrice()->getSavings() != null ) {
					$myproduct->percentagesaved  = $offerlisting->getPrice() == null ? '' : $offerlisting->getPrice()->getSavings()->getPercentage();
					$myproduct->amountsaved      = $offerlisting->getPrice() == null ? '' : $offerlisting->getPrice()->getSavings()->getDisplayAmount();
					$myproduct->amountsavedfloat = $offerlisting->getPrice() == null ? 0 : $offerlisting->getPrice()->getSavings()->getAmount();
				}

				if ( $offerlisting->getSavingBasis() != null ) {
					$myproduct->listprice      = $offerlisting->getSavingBasis()->getDisplayAmount();
					$myproduct->listpricefloat = $offerlisting->getSavingBasis()->getAmount();
				}

				if ( $offerlisting->getAvailability() != null ) {
                    /*
					if(ATKPTools::str_contains($offerlisting->getAvailability()->getMessage() ,'Nicht auf Lager', false)) {
						//preis = 0 ?
						$myproduct->saleprice = '';
						$myproduct->salepricefloat = 0;
					}*/
					$myproduct->availability = $offerlisting->getAvailability()->getMessage();
				}

			}
		}


		//$myproduct->salepricefloat   = $this->price_to_float( $myproduct->saleprice );
		//$myproduct->amountsavedfloat = $this->price_to_float( $myproduct->amountsaved );
		//$myproduct->listpricefloat   = $this->price_to_float( $myproduct->listprice );
		$myproduct->shippingfloat = (float) 0;


		if ( $result->getItemInfo() != null && $result->getItemInfo()->getByLineInfo() != null && $result->getItemInfo()->getByLineInfo()->getManufacturer() != null ) {
			$myproduct->manufacturer = $result->getItemInfo()->getByLineInfo()->getManufacturer()->getDisplayValue();
		}
		if ( $result->getItemInfo() != null && $result->getItemInfo()->getByLineInfo() != null && $result->getItemInfo()->getByLineInfo()->getBrand() != null ) {
			$myproduct->brand = $result->getItemInfo()->getByLineInfo()->getBrand()->getDisplayValue();
		}

		$isbn_full = '';
		if ( $result->getItemInfo()->getExternalIds() != null && $result->getItemInfo()->getExternalIds()->getISBNs() != null ) {
			foreach ( $result->getItemInfo()->getExternalIds()->getISBNs()->getDisplayValues() as $ean ) {
				if ( $isbn_full != '' ) {
					$isbn_full .= ',';
				}

				$isbn_full .= $ean;
			}
		}
		$myproduct->isbn = $isbn_full;

		$ean_full = '';
		if ( $result->getItemInfo()->getExternalIds() != null && $result->getItemInfo()->getExternalIds()->getEANs() != null ) {
			foreach ( $result->getItemInfo()->getExternalIds()->getEANs()->getDisplayValues() as $ean ) {
				if ( $ean_full != '' ) {
					$ean_full .= ',';
				}

				$ean_full .= $ean;
			}
		}
		$myproduct->ean = $ean_full;

		$category = '';
		if ( $result->getBrowseNodeInfo() != null ) {
			foreach ( $result->getBrowseNodeInfo()->getBrowseNodes() as $bnw ) {
				$category .= $this->getBrowseNodeTree( $bnw->getAncestor() );
				break;
			}
		}

		$myproduct->productgroup = $category;

		if ( $result->getItemInfo()->getProductInfo() && $result->getItemInfo()->getProductInfo()->getReleaseDate() ) {
			$myproduct->releasedate = substr( $result->getItemInfo()->getProductInfo()->getReleaseDate()->getDisplayValue(), 0, 10 );
		}
		if ( $result->getItemInfo()->getByLineInfo() != null && $result->getItemInfo()->getByLineInfo()->getContributors() != null ) {
			foreach ( $result->getItemInfo()->getByLineInfo()->getContributors() as $const ) {
				if ( $const->getRole() == 'Autor' ) {
					$myproduct->author = $const->getName();
					break;
				}
			}
		}

		if ( $result->getItemInfo()->getContentInfo() != null && $result->getItemInfo()->getContentInfo()->getPagesCount() != null ) {
			$myproduct->numberofpages = $result->getItemInfo()->getContentInfo()->getPagesCount()->getDisplayValue();
		}

		$myproduct->mpn = '';
		if ( $result->getItemInfo()->getManufactureInfo() != null && $result->getItemInfo()->getManufactureInfo()->getItemPartNumber() != null ) {
			$myproduct->mpn = $result->getItemInfo()->getManufactureInfo()->getItemPartNumber()->getDisplayValue();
		}


		if ( count( $myproduct->variations ) > 0 && $myproduct->salepricefloat == 0 ) {
			foreach ( $myproduct->variations as $variation ) {

				if ( $variation->salepricefloat > 0 ) {
					$myproduct->listprice   = $variation->listprice;
					$myproduct->amountsaved = $variation->amountsaved;
					$myproduct->saleprice   = $variation->saleprice;

					$myproduct->listpricefloat   = $variation->listpricefloat;
					$myproduct->amountsavedfloat = $variation->amountsavedfloat;
					$myproduct->percentagesaved  = $variation->percentagesaved;
					$myproduct->salepricefloat   = $variation->salepricefloat;
					$myproduct->shippingfloat    = $variation->shippingfloat;

					$myproduct->availability = $variation->availability;
					$myproduct->shipping     = $variation->shipping;
					$myproduct->isprime      = $variation->isprime;
					$myproduct->iswarehouse = $variation->iswarehouse;

					$myproduct->smallimageurl  = $variation->smallimageurl;
					$myproduct->mediumimageurl = $variation->mediumimageurl;
					$myproduct->largeimageurl  = $variation->largeimageurl;
					break;
				}
			}
		}

		if ($myproduct->salepricefloat == 0 && $this->sitetripemode == 3 && $myproduct->asin != '') {
				//bevorzugte nutzung
				$atkpresponse = $this->load_sitestripeproduct( array($myproduct->asin) );
				if(count($atkpresponse->responseitems) > 0) {
					$myproduct->saleprice = $atkpresponse->responseitems[0]->productitem->saleprice;
					$myproduct->salepricefloat = $atkpresponse->responseitems[0]->productitem->salepricefloat;
				}
		}

		return $myproduct;
	}

	/**
	 * Sets itemIdType
	 *
	 * @param \Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\BrowseNodeAncestor $browsenodeinfo
	 *
	 * @return string $result
	 */
	private function getBrowseNodeTree( $browsenodeancestor ) {
		if ( $browsenodeancestor == null ) {
			return '';
		}
		$result = '';

		if ( $browsenodeancestor->getAncestor() != null ) {
			$result .= ( $this->getBrowseNodeTree( $browsenodeancestor->getAncestor() ) ) . ' > ';
		}

		$result .= $browsenodeancestor->getDisplayName();

		return $result;
	}


    private function get_customer_rating_api($asin, &$averageRating, &$totalReviews) {
	    $p = $this->load_sitestripeproduct(array($asin));

        if(count($p->responseitems) > 0) {
	        $averageRating = $p->responseitems[0]->productitem->rating;
	        $totalReviews  = $p->responseitems[0]->productitem->reviewcount;
        }
    }
	private function setCondition($request) {

	    switch($this->onlynew) {
            default:

	            //$request->setCondition(\Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\Condition::ANY);
                break;
            case  1:
	            $request->setCondition(\Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\Condition::_NEW);
                break;
		    case  2:
			    $request->setCondition(\Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\Condition::USED);
			    break;
		    case  3:
			    $request->setCondition(\Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\Condition::COLLECTIBLE);
			    break;
		    case  4:
			    $request->setCondition(\Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\Condition::REFURBISHED);
			    break;
        }
	    return $request;
    }

	private function sendSearchRequest( $searchItemsRequest ) {
		$searchItemsRequest = $this->setCondition($searchItemsRequest);
		$this->validate_request_v5( $searchItemsRequest );

		if ( $this->seconds_wait > 0 ) {
			sleep( $this->seconds_wait );
		}

		//try {
			$searchItemsResponse = $this->helper->searchItems( $searchItemsRequest );
//		} catch ( Exception $exception ) {
//			if ( ATKPTools::str_contains( $exception->getMessage(), '429 Too Many Requests', false ) ) {
//
//				sleep( ATKP_AMZ_WAIT );
//				$searchItemsResponse = $this->helper->searchItems( $searchItemsRequest );
//			} else {
//				throw $exception;
//			}
//		}

		$this->validate_response_v5( $searchItemsResponse );

		return $searchItemsResponse;
	}

	private function sendVariationRequest( $variationRequest ) {

		$this->validate_request_v5( $variationRequest );

		if ( $this->seconds_wait > 0 ) {
			sleep( $this->seconds_wait );
		}


		//try {
			$getItemsResponse = $this->helper->getVariations( $variationRequest );
//		} catch ( Exception $exception ) {
//			if ( ATKPTools::str_contains( $exception->getMessage(), '429 Too Many Requests', false ) ) {
//				sleep( ATKP_AMZ_WAIT );
//				$getItemsResponse = $this->helper->getVariations( $variationRequest );
//			} else {
//				throw $exception;
//			}
//		}

		$this->validate_response_v5( $getItemsResponse );

		return $getItemsResponse;
	}

	private function sendGetItemsRequest( $getItemsRequest ) {
		$searchItemsRequest = $this->setCondition($getItemsRequest);

		$this->validate_request_v5( $getItemsRequest );

		if ( $this->seconds_wait > 0 ) {
			sleep( $this->seconds_wait );
		}

		//try {
			$getItemsResponse = $this->helper->getItems( $getItemsRequest );
//		} catch ( Exception $exception ) {
//			if ( ATKPTools::str_contains( $exception->getMessage(), '429 Too Many Requests', false ) ) {
//				sleep( ATKP_AMZ_WAIT );
//				$getItemsResponse = $this->helper->getItems( $getItemsRequest );
//			} else {
//				throw $exception;
//			}
//		}

		$this->validate_response_v5( $getItemsResponse );

		return $getItemsResponse;
	}

	public function retrieve_product_list( $search_request ) {
		if ( $this->helper == null ) {
			throw new Exception( 'checklogon required' );
		}

		$mylist            = new atkp_list_resp();
		$mylist->updatedon = ATKPTools::get_currenttime();
		$mylist->asins     = array();
		$mylist->products  = null;

		switch ( $search_request->request_type ) {
            case atkp_list_request_type::TopSellers:
            case atkp_list_request_type::NewReleases:

				$searchItemsRequest = new Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\SearchItemsRequest();
				//$searchItemsRequest->setSearchIndex( 'All' );
				$searchItemsRequest->setSortBy( $search_request->request_type == atkp_list_request_type::TopSellers ? 'Featured' : 'NewestArrivals' );

				$searchItemsRequest->setBrowseNodeId( $search_request->category );
				$searchItemsRequest->setKeywords( "*" );
                if($this->languages_of_preference != null)
                    $searchItemsRequest->setLanguagesOfPreference($this->languages_of_preference);
				$searchItemsRequest->setPartnerTag( $this->associateTag );
				$searchItemsRequest->setPartnerType( Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\PartnerType::ASSOCIATES );
				$searchItemsRequest->setResources(
					\Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\SearchItemsResource::getAllowableEnumValues()
				);

				$searchItemsRequest->setItemCount( 10 );
				$searchItemsRequest->setItemPage( 1 );

				$searchItemsResponse = $this->sendSearchRequest( $searchItemsRequest );

				$products = array();

                if ( $searchItemsResponse->getSearchResult() != null) {
                    $mylist->total_items_count = $searchItemsResponse->getSearchResult()->getTotalResultCount();
                    $mylist->total_pages       = ceil( $mylist->total_items_count / $this->get_maximum_items_per_page() );

                    if ($searchItemsResponse->getSearchResult()->getItems() != null ) {
                        $items = $searchItemsResponse->getSearchResult()->getItems();

                        foreach ( $items as $item ) {
                            if ( $item->getASIN() != null ) {
                                $products[] = $this->fill_product_v5( $item );
                            }
                        }
                    }
                }

				$mylist->products = $products;
				break;
            case atkp_list_request_type::ExtendedSearch:
            case atkp_list_request_type::Search:

				$searchItemsRequest = new Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\SearchItemsRequest();
				if ( $search_request->category != '' && $search_request->category != 'All' ) {
					$searchItemsRequest->setSearchIndex( $search_request->category );

					if ( $search_request->sort_order != '' ) {
						//AvgCustomerReviews', 'Featured', 'NewestArrivals', 'Price:HighToLow', 'Price:LowToHigh', 'Relevance

						switch ( $search_request->sort_order ) {
							case '-pubdate':
							case '-publication_date':
							case 'date-desc-rank':
							case 'launch_date':
								$sortorder = 'NewestArrivals';
								break;
							case 'popularity-rank':
							case 'relevancerank':
							case 'salesrank':
							case 'psrank':
							case 'titlerank':
							case '-titlerank':
							case '-unit-sales':
							default:
								$sortorder = 'Relevance';
								break;
							case 'reviewrank':
							case 'pmrank':
							case 'reviewrank_authority':
							case 'review-rank':
								$sortorder = 'AvgCustomerReviews';
								break;
							case 'price':
							case 'price-asc-rank':
							case 'pricerank':
								$sortorder = 'Price:LowToHigh';
								break;
							case '-price':
							case 'price-desc-rank':
							case 'inverse-pricerank':
								$sortorder = 'Price:HighToLow';
								break;
							case 'featured':
								$sortorder = 'Featured';
								break;
							case 'AvgCustomerReviews':
							case 'Featured':
							case 'NewestArrivals':
							case 'Price:HighToLow':
							case 'Price:LowToHigh':
							case 'Relevance':
								break;
						}

						$searchItemsRequest->setSortBy( $sortorder );
					}
				}

				//TODO: Filterfelder ergänzen
			    $keyword = $search_request->keyword;

				if ( $search_request->filter != null ) {
					foreach ( $search_request->filter as $field => $value ) {
						switch ( $field ) {
							case 'Keywords':
								$keyword = $value;
								break;
							case 'SearchIndex':
								$searchItemsRequest->setSearchIndex( $value );
								break;
							case 'Sort':
								$searchItemsRequest->setSortBy( $value );
								break;
							case 'Actor':
								$searchItemsRequest->setActor( $value );
								break;
							case 'Artist':
								$searchItemsRequest->setArtist( $value );
								break;
							case 'Author':
								$searchItemsRequest->setAuthor( $value );
								break;
							case 'Availability':
								$searchItemsRequest->setAvailability( $value );
								break;
							case 'Brand':
								$searchItemsRequest->setBrand( $value );
								break;
							case 'Condition':
								$searchItemsRequest->setCondition( $value );
								break;
							case 'DeliveryFlags':
								$searchItemsRequest->setDeliveryFlags( explode(',', $value) );
								break;
							case 'CurrencyOfPreference':
								$searchItemsRequest->setCurrencyOfPreference( $value );
								break;
							case 'LanguagesOfPreference':
								$searchItemsRequest->setLanguagesOfPreference( $value );
								break;
							case 'Marketplace':
								$searchItemsRequest->setMarketplace( $value );
								break;
							case 'MaximumPrice':
								$searchItemsRequest->setMaxPrice( floatval($value) );
								break;
							case 'MinimumPrice':
								$searchItemsRequest->setMinPrice( floatval($value) );
								break;
							case 'MerchantId':
								$searchItemsRequest->setMerchant( $value );
								break;
							case 'MinPercentageOff':
								$searchItemsRequest->setMinSavingPercent( intval($value) );
								break;
							case 'MinReviewsRating':
								$searchItemsRequest->setMinReviewsRating( intval($value) );
								break;
							case 'Title':
								$searchItemsRequest->setTitle( $value );
								break;
							case 'BrowseNode':
								$searchItemsRequest->setBrowseNodeId( $value );
								break;
						}
					}
				}

				if ( $keyword != '' ) {
					$keywords = explode( ',', $keyword );
					if ( $keywords != null && count( $keywords ) > 1 ) {
						$searchItemsRequest->setKeywords( $keywords );
					} else {
						$searchItemsRequest->setKeywords( $keyword );
					}
				}
                if($this->languages_of_preference != null)
                    $searchItemsRequest->setLanguagesOfPreference($this->languages_of_preference);
				$searchItemsRequest->setPartnerTag( $this->associateTag );
				$searchItemsRequest->setPartnerType( Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\PartnerType::ASSOCIATES );
				$searchItemsRequest->setResources(
					\Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\SearchItemsResource::getAllowableEnumValues()
				);

                $init_page = 1;
                if($search_request->items_per_page > 0) {
	                $searchItemsRequest->setItemCount( $search_request->items_per_page );
	                $init_page = $search_request->page;
	                $pages = $search_request->page;
                }else {
	                $itemsperpage = $this->get_maximum_items_per_page() > $search_request->max_count ? $search_request->max_count : $this->get_maximum_items_per_page();
	                $pages        = ceil( $search_request->max_count / $itemsperpage );

	                $searchItemsRequest->setItemCount( intval( $itemsperpage ) );
                }

				$products = array();
				for ( $x = $init_page; $x <= $pages; $x ++ ) {
					$searchItemsRequest->setItemPage( $x );

					$searchItemsResponse = $this->sendSearchRequest( $searchItemsRequest );

                    if($searchItemsResponse->getSearchResult() != null) {
                        $mylist->total_items_count = $searchItemsResponse->getSearchResult()->getTotalResultCount();
                        $mylist->total_pages       = ceil( $mylist->total_items_count / $this->get_maximum_items_per_page() );

                        if ($searchItemsResponse->getSearchResult()->getItems() != null ) {
                            $items = $searchItemsResponse->getSearchResult()->getItems();

                            foreach ( $items as $item ) {
                                if ( $item->getASIN() != null ) {
                                    $products[] = $this->fill_product_v5( $item );

                                    if ( $search_request->items_per_page == 0 && count( $products ) >= $search_request->max_count ) {
                                        break;
                                    }
                                }
                            }

                            if ( count( $items ) < $this->get_maximum_items_per_page() || ($search_request->items_per_page == 0 && count( $products ) >= $search_request->max_count   )) {
                                break;
                            }
                        }
                    }
				}

				$mylist->products = $products;

				break;
			default:
				$mylist->message =  'unknown request_type: ' . $search_request->request_type ;
				break;
		}

        return $mylist;
	}

	public function get_maximum_items_per_page() {
		return 10;
	}
	public function get_maximum_pages() {
		return 10;
	}

	public function retrieve_list( $requestType, $nodeid, $keyword, $asin, $maxCount, $sortByOrder, $filters ) {
		$my_request            = new atkp_list_req();
		$my_request->keyword = $keyword;
		$my_request->request_type = $requestType;
		$my_request->max_count = $maxCount;
		$my_request->filter = $filters;
		$my_request->category = $nodeid;
		$my_request->sort_order = $sortByOrder;

		return $this->retrieve_product_list($my_request);
	}

	public function get_supportedlistsources() {
		return implode(',', array(atkp_list_source_type::BestSeller,atkp_list_source_type::NewReleases, atkp_list_source_type::Search, atkp_list_source_type::ExtendedSearch));
	}

}


?>