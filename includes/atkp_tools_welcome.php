<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class atkp_tools_welcome {
	/**
	 * Construct the plugin object
	 */
	public function __construct( $pluginbase ) {


	}


	public function welcome_page() {


		?>
        <div id="atkp-welcome" class="">
            <div class="container">

                <div class="intro ">
                    <div class="sullie">
                        <img src="<?php echo esc_url(plugins_url( 'images/affiliate-toolkit-wb.jpg', ATKP_PLUGIN_FILE )) ?>"
                             alt="affiliate-toolkit-logo">
                    </div>
                    <div class="block">
                        <h1><?php echo esc_html__( 'Welcome to affiliate-toolkit', ATKP_PLUGIN_PREFIX ) ?></h1>
                        <h6><?php echo esc_html__( 'Thank you for choosing affiliate-toolkit - the most powerful WordPress affiliate plugin.', ATKP_PLUGIN_PREFIX ) ?></h6>
                    </div>
                    <div style="text-align:center">

                        <iframe width="560" height="315"
                                src="<?php echo ATKPTools::is_lang_de() ? 'https://www.youtube-nocookie.com/embed/kw5ZlBwhl08?si=dDUjhZeJwy6EpPkY&amp;controls=0' : 'https://www.youtube-nocookie.com/embed/4r5TQBPq--o?si=XdN08mYbsrF7xiM2&amp;controls=0' ?>"
                                title="YouTube video player" frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                allowfullscreen></iframe>
                    </div>
                    <div style="padding-top: 0;" class="block">
                        <h6><?php echo esc_html__( 'This video describes the most minimal steps you need to', ATKP_PLUGIN_PREFIX ) . '<br/>' . esc_html__( 'take to set up a shop and import an affiliate product.', ATKP_PLUGIN_PREFIX); ?> </h6>
                        <div class="button-wrap atkp-welcome-clearfix">
                            <div class="left">
                                <a href="<?php echo esc_url(admin_url( 'post-new.php?post_type=atkp_shop', ATKP_PLUGIN_FILE )) ?>"
                                   class="button button-primary">
	                                <?php echo esc_html__( 'Create Your First Shop', ATKP_PLUGIN_PREFIX ) ?>                </a>
                            </div>
                            <div class="right">
                                <a href="<?php echo ( ATKPTools::is_lang_de() ? 'https://www.affiliate-toolkit.com/de/kb/' : 'https://www.affiliate-toolkit.com/kb/' ) . '?utm_medium=welcome-page&amp;utm_content=KnowledgeBase&amp;utm_source=WordPress&amp;utm_campaign=starterpass' ?>"
                                   class="button button-primary" target="_blank" rel="noopener noreferrer">
	                                <?php echo esc_html__( 'Read the Knowledge Base', ATKP_PLUGIN_PREFIX ) ?>             </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="features">
                    <div class="block">
                        <h1><?php echo esc_html__( 'affiliate-toolkit Features', ATKP_PLUGIN_PREFIX ) ?></h1>
                        <h6><?php echo esc_html__( 'For beginners & professional affiliates: Boost your commissions with ', ATKP_PLUGIN_PREFIX ) . '<br/>' . esc_html__( 'attractive boxes and price comparisons!', ATKP_PLUGIN_PREFIX ) ?></h6>


                        <div class="feature-list atkp-welcome-clearfix">
                            <div class="feature-block first">
                                <img src="https://www.affiliate-toolkit.com/wp-content/uploads/2022/11/zentrale-produktdaten-150x150.png">
                                <h5><?php echo esc_html__( 'Central management', ATKP_PLUGIN_PREFIX ) ?></h5>
                                <p><?php echo esc_html__( 'All product information is stored in one place in the WordPress backend. This information can be overwritten as you wish. You can also see which products were found in other stores through the price comparison. If you want to exchange the product later, do it at this place and not at every place where you have embedded it in the blog.', ATKP_PLUGIN_PREFIX ) ?>
                                </p>
                            </div>

                            <div class="feature-block last">
                                <img src="https://www.affiliate-toolkit.com/wp-content/uploads/2022/11/produktimport-150x150.png">
                                <h5><?php echo esc_html__( 'Product imports via backend', ATKP_PLUGIN_PREFIX ) ?></h5>
                                <p><?php echo esc_html__( 'You can search for EAN, keyword or ASIN directly from the WordPress backend. You will see the found products directly in the backend. Once you have found the product, click on "import" and the product is available for embedding in the blog.', ATKP_PLUGIN_PREFIX ) ?>
                                </p>
                            </div>

                            <div class="feature-block first">
                                <img src="https://www.affiliate-toolkit.com/wp-content/uploads/2022/11/productboxen-150x150.png">
                                <h5><?php echo esc_html__( 'Product boxes', ATKP_PLUGIN_PREFIX ) ?></h5>
                                <p><?php echo esc_html__( 'You can embed the product data directly as a text link, product box or listing in your WordPress website. These product boxes are already attractively designed "out of the box". However, you can customize these boxes as you like or even design your own box with HTML & CSS.', ATKP_PLUGIN_PREFIX ) ?>
                                </p>
                            </div>

                            <div class="feature-block last">
                                <img src="https://www.affiliate-toolkit.com/wp-content/uploads/2022/11/bestseller-150x150.png">
                                <h5><?php echo esc_html__( 'Bestsellers, new releases and search lists', ATKP_PLUGIN_PREFIX ) ?></h5>
                                <p><?php echo esc_html__( 'With bestseller lists, you can add a conversation-boosting element to your website. These lists convert especially well because people like to follow others. You can output 3, 10 or 20 entries. Searching is done either via BrowseNode or via a search term.', ATKP_PLUGIN_PREFIX ) ?>
                                </p>
                            </div>

                            <div class="feature-block first">
                                <img src="https://www.affiliate-toolkit.com/wp-content/uploads/2022/11/woocommerce-150x150.png">
                                <h5><?php echo esc_html__( 'Products in WooCommerce', ATKP_PLUGIN_PREFIX ) ?></h5>
                                <p><?php echo esc_html__( 'With our WooCommerce support, you can import affiliate products into WooCommerce as external products. You can include product descriptions, product images and also the price comparison. This also allows you to use already prepared themes as WooCommerce affiliate store.', ATKP_PLUGIN_PREFIX ) ?>
                                </p>
                            </div>

                            <div class="feature-block last">
                                <img src="https://www.affiliate-toolkit.com/wp-content/uploads/2022/11/woocommerce-150x150.png">
                                <h5><?php echo esc_html__( 'Automatic feed updates', ATKP_PLUGIN_PREFIX ) ?></h5>
                                <p><?php echo esc_html__( 'All product data, images and prices are updated regularly. You do not have to worry about anything.', ATKP_PLUGIN_PREFIX ) ?> </p>
                            </div>

                            <div class="feature-block first">
                                <img src="https://www.affiliate-toolkit.com/wp-content/uploads/2022/11/geotargeting-128x128.png">
                                <h5><?php echo esc_html__( 'GeoIP Targeting', ATKP_PLUGIN_PREFIX ) ?></h5>
                                <p><?php echo esc_html__( 'With this extension you can show your visitors a suitable offer per country. This works via GeoIP targeting.', ATKP_PLUGIN_PREFIX ) ?>
                                </p>
                            </div>

                            <div class="feature-block last">
                                <img src="https://www.affiliate-toolkit.com/wp-content/uploads/2022/11/emailbenachrichtigung-128x128.png">
                                <h5><?php echo esc_html__( 'Email notifications', ATKP_PLUGIN_PREFIX ) ?>
                                </h5>
                                <p><?php echo esc_html__( 'If a product is no longer available or an error occurs regarding a product, you will receive a report with the errors upon request.', ATKP_PLUGIN_PREFIX ) ?>
                                </p>
                            </div>
                        </div>

                        <div class="button-wrap">
                            <a href="https://www.affiliate-toolkit.com/#features?utm_medium=welcome-page&amp;utm_content=AllFeatures&amp;utm_source=WordPress&amp;utm_campaign=starterpass"
                               class="button atkp-primary atkp-welcome-green" rel="noopener noreferrer" target="_blank">
	                            <?php echo esc_html__( 'See All Features', ATKP_PLUGIN_PREFIX ) ?>            </a>
                        </div>
                    </div>
                </div>
                <div class="upgrade-cta upgrade">
                    <div class="block atkp-welcome-clearfix">
                        <div class="">
                            <h2><?php esc_html__( 'Upgrade your package', ATKP_PLUGIN_PREFIX ) ?></h2>
                            <ul>
                                <li>
                                    <span class="dashicons dashicons-yes"></span> AWIN Feeds
                                </li>
                                <li>
                                    <span class="dashicons dashicons-yes"></span> billiger.de API
                                </li>
                                <li>
                                    <span class="dashicons dashicons-yes"></span> eBay API
                                </li>
                                <li>
                                    <span class="dashicons dashicons-yes"></span> Geizhals API
                                </li>
                                <li>
                                    <span class="dashicons dashicons-yes"></span> CSV Feeds
                                </li>
                                <li>
                                    <span class="dashicons dashicons-yes"></span> Yadore API
                                </li>
                                <li>
                                    <span class="dashicons dashicons-yes"></span> Shopping24 API
                                </li>
                                <li>
                                    <span class="dashicons dashicons-yes"></span> Tradedoubler API
                                </li>

                                <li>
                                    <span class="dashicons dashicons-yes"></span> Responsive Compare Table
                                </li>
                                <li>
                                    <span class="dashicons dashicons-yes"></span> Template Pack
                                </li>
                                <li>
                                    <span class="dashicons dashicons-yes"></span> Price History
                                </li>
                                <li>
                                    <span class="dashicons dashicons-yes"></span> Frontend Product Search
                                </li>
                                <li>
                                    <span class="dashicons dashicons-yes"></span> Custom Fields
                                </li>
                                <li>
                                    <span class="dashicons dashicons-yes"></span> Product Pages
                                </li>
                                <li>
                                    <span class="dashicons dashicons-yes"></span> Geo Targeting
                                </li>
                                <li>
                                    <span class="dashicons dashicons-yes"></span> WooCommerce Connector
                                </li>
                                <li>
                                    <span class="dashicons dashicons-yes"></span> Export product fields
                                </li>
                                <li>
                                    <span class="dashicons dashicons-yes"></span> Automatic price comparisons
                                </li>
                                <li>
                                    <span class="dashicons dashicons-yes"></span> Comparison tables
                                </li>
                                <li>
                                    <span class="dashicons dashicons-yes"></span> Importsets for products
                                </li>
                                <li>
                                    <span class="dashicons dashicons-yes"></span> Support for problems
                                </li>

                            </ul>
                        </div>

                        <div style="text-align:center;padding-top:10px">
                            <a href="https://www.affiliate-toolkit.com/pricing/?utm_medium=welcome-page&amp;utm_content=Upgrade+Now&amp;utm_source=WordPress&amp;utm_campaign=starterpass"
                               rel="noopener noreferrer" target="_blank" class="button atkp-primary atkp-welcome-green">
	                            <?php echo esc_html__( 'Upgrade Now', ATKP_PLUGIN_PREFIX ) ?>            </a>
                        </div>
                    </div>
                </div>
                <div class="testimonials upgrade">
                    <div class="block">
                        <h1><?php echo esc_html__( 'Testimonials', ATKP_PLUGIN_PREFIX ) ?></h1>

                        <div class="testimonial-block atkp-welcome-clearfix">
                            <img src="https://www.affiliate-toolkit.com/wp-content/uploads/2021/11/Bild2.jpg">
                            <p><?php echo esc_html__( 'Affiliate Toolkit is a powerful, comprehensive and flexible WordPress plugin that makes my daily work on my niche sites easier. Besides the "standard" features, Affiliate Toolkit also contains other helpful and valuable additional features that currently no other plugin offer. With the help of the Affiliate Toolkit, I have been able to demonstrably increase my earnings. In addition, I can now implement projects that were not possible before. In addition to the technical refinements, I especially appreciate the exceptionally good support that Christof offers customers here. He kindly helps with any problem and patiently answers even complex questions. All in all, the investment in the handy affiliate toolkit has been more than worth it!', ATKP_PLUGIN_PREFIX ) ?>
                            </p>
                            <p>
                            </p>
                            <p><strong>Simon Lüthje</strong>, Blogger</p>
                        </div>

                        <div class="testimonial-block atkp-welcome-clearfix">
                            <img src="https://www.affiliate-toolkit.com/wp-content/uploads/2021/11/peerwandiger-selbstaendig-im-netz-1.jpg">
                            <p><?php echo esc_html__( 'The Affiliate Toolkit plugin seriously surprised me. The first impression was slightly dry and it is missing some kind of introduction to the plugin. But in principle it is very easy to use it and offers many functions. The free version is also very helpful but just the paid version holds all the aces.', ATKP_PLUGIN_PREFIX ) ?>
                            </p>
                            <p>
                            </p>
                            <p><strong>Peer Wandiger</strong>, Blogger</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <style>
            #wpcontent {
                padding-left: 0;
                position: relative;
            }

            .button-wrap .button-primary {
                width: 100%;
                text-align: center;
            }

            .atkp-welcome-clearfix:before {
                content: " ";
                display: table;
            }

            .atkp-welcome-clearfix:after {
                clear: both;
                content: " ";
                display: table;
            }

            #atkp-welcome {
                border-top: 3px solid #54b9ca;
                color: #555;
                padding-top: 110px;
            }

            @media (max-width: 767px) {
                #atkp-welcome {
                    padding-top: 64px;
                }
            }

            #atkp-welcome *,
            #atkp-welcome *::before,
            #atkp-welcome *::after {
                -webkit-box-sizing: border-box;
                -moz-box-sizing: border-box;
                box-sizing: border-box;
            }

            #atkp-welcome .container {
                margin: 0 auto;
                max-width: 760px;
                padding: 0;
            }

            #atkp-welcome .block {
                padding: 40px;
            }

            @media (max-width: 767px) {
                #atkp-welcome .block {
                    padding: 20px;
                }
            }

            #atkp-welcome img {
                max-width: 100%;
                height: auto;
            }

            #atkp-welcome h1 {
                color: #222;
                font-size: 24px;
                text-align: center;
                margin: 0 0 16px 0;
            }

            #atkp-welcome h5 {
                color: #222;
                font-size: 16px;
                margin: 0 0 8px 0;
            }

            #atkp-welcome h6 {
                font-size: 16px;
                font-weight: 400;
                line-height: 1.6;
                text-align: center;
                margin: 0;
            }

            #atkp-welcome p {
                font-size: 14px;
                margin: 0 0 20px 0;
            }


            #atkp-welcome .button {
                border-radius: 10px;
            }

            #atkp-welcome .button:hover {
                background-color: #54b9ca;
                border-color: #54b9ca;
            }

            .atkp-welcome-green {
                background-color: #54b9ca !important;
                border-color: #54b9ca !important;
            }

            .atkp-welcome-green:hover {
                background-color: #005162 !important;
                border-color: #005162 !important;
            }


            #atkp-welcome .button-wrap {
                max-width: 590px;
                margin: 0 auto 0 auto;
            }

            #atkp-welcome .button-wrap .left {
                float: left;
                width: 50%;
                padding-right: 20px;
            }

            @media (max-width: 767px) {
                #atkp-welcome .button-wrap .left {
                    float: none;
                    width: 100%;
                    padding: 0;
                    margin-bottom: 20px;
                }
            }

            #atkp-welcome .button-wrap .right {
                float: right;
                width: 50%;
                padding-left: 20px;
            }

            @media (max-width: 767px) {
                #atkp-welcome .button-wrap .right {
                    float: none;
                    width: 100%;
                    padding: 0;
                }
            }

            #atkp-welcome .intro {
                background-color: #fff;
                border: 2px solid #e1e1e1;
                border-radius: 2px;
                margin-bottom: 30px;
                position: relative;
                padding-top: 40px;
            }

            #atkp-welcome .intro .sullie {
                background-color: #fff;
                border: 2px solid #e1e1e1;
                border-radius: 50%;
                height: 110px;
                width: 110px;
                padding: 18px;
                position: absolute;
                top: -58px;
                left: 50%;
                margin-left: -55px;
            }

            #atkp-welcome .intro .video-thumbnail {
                display: block;
                margin: 0 auto;
            }

            #atkp-welcome .intro .button-wrap {
                margin-top: 25px;
            }

            #atkp-welcome .features {
                background-color: #fff;
                border: 2px solid #e1e1e1;
                border-bottom: 0;
                border-radius: 2px 2px 0 0;
                position: relative;
                padding-top: 20px;
                padding-bottom: 20px;
            }

            #atkp-welcome .features .feature-list {
                margin-top: 60px;
            }

            #atkp-welcome .features .feature-block {
                float: left;
                width: 50%;
                padding-bottom: 35px;
                overflow: auto;
            }

            @media (max-width: 767px) {
                #atkp-welcome .features .feature-block {
                    float: none;
                    width: 100%;
                }
            }

            #atkp-welcome .features .feature-block.first {
                padding-right: 20px;
                clear: both;
            }

            @media (max-width: 767px) {
                #atkp-welcome .features .feature-block.first {
                    padding-right: 0;
                }
            }

            #atkp-welcome .features .feature-block.last {
                padding-left: 20px;
            }

            @media (max-width: 767px) {
                #atkp-welcome .features .feature-block.last {
                    padding-left: 0;
                }
            }

            #atkp-welcome .features .feature-block img {
                float: left;
                max-width: 46px;
            }

            #atkp-welcome .features .feature-block h5 {
                margin-left: 68px;
            }

            #atkp-welcome .features .feature-block p {
                margin: 0;
                margin-left: 68px;
            }

            #atkp-welcome .features .button-wrap {
                margin-top: 25px;
                text-align: center;
            }

            #atkp-welcome .upgrade-cta {
                background-color: #000;
                border: 2px solid #e1e1e1;
                border-top: 0;
                border-bottom: 0;
                color: #fff;
            }

            #atkp-welcome .upgrade-cta h2 {
                color: #fff;
                font-size: 20px;
                margin: 0 0 30px 0;
            }

            #atkp-welcome .upgrade-cta ul {
                display: -ms-flex;
                display: -webkit-flex;
                display: flex;
                -webkit-flex-wrap: wrap;
                flex-wrap: wrap;
                font-size: 15px;
                margin: 0;
                padding: 0;
            }

            #atkp-welcome .upgrade-cta ul li {
                flex: 33.33%;
                margin: 0 0 8px 0;
                padding: 0;
            }

            #atkp-welcome .upgrade-cta ul li .dashicons {
                color: #54b9ca;
                margin-right: 5px;
            }

            #atkp-welcome .upgrade-cta .dup-btn {
                width: 33.33%;
                margin: 30px auto 0;
            }

            #atkp-welcome .upgrade-cta h2 {
                text-align: center;
                width: 50%;
                border-bottom: 1px solid white;
                padding-bottom: 10px;
                margin: 0 auto 30px;
            }

            #atkp-welcome .upgrade-cta .right h2 span {
                display: inline-block;
                border-bottom: 1px solid #555;
                padding: 0 15px 12px;
            }

            #atkp-welcome .upgrade-cta .right .price {
                padding: 26px 0;
            }

            #atkp-welcome .upgrade-cta .right .price .amount {
                font-size: 48px;
                font-weight: 600;
                position: relative;
                display: inline-block;
            }

            #atkp-welcome .upgrade-cta .right .price .amount:before {
                content: '$';
                position: absolute;
                top: -8px;
                left: -16px;
                font-size: 18px;
            }

            #atkp-welcome .upgrade-cta .right .price .term {
                font-size: 12px;
                display: inline-block;
            }

            #atkp-welcome .testimonials {
                background-color: #fff;
                border: 2px solid #e1e1e1;
                border-top: 0;
                padding: 20px 0;
            }

            #atkp-welcome .testimonials .testimonial-block {
                margin: 50px 0 0 0;
            }

            #atkp-welcome .testimonials .testimonial-block img {
                border-radius: 50%;
                float: left;
                max-width: 100px;
                box-shadow: 0 0 18px rgba(0, 0, 0, 0.2);
            }

            @media (max-width: 767px) {
                #atkp-welcome .testimonials .testimonial-block img {
                    width: 65px;
                }
            }

            #atkp-welcome .testimonials .testimonial-block p {
                font-size: 14px;
                margin: 0 0 12px 140px;
            }

            @media (max-width: 767px) {
                #atkp-welcome .testimonials .testimonial-block p {
                    margin-left: 100px;
                }
            }

            #atkp-welcome .testimonials .testimonial-block p:last-of-type {
                margin-bottom: 0;
            }

            #atkp-welcome .footer {
                background-color: #f1f1f1;
                border: 2px solid #e1e1e1;
                border-top: 0;
                border-radius: 0 0 2px 2px;
            }

            #atkp-welcome.pro .features {
                border: 2px solid #e1e1e1;
                margin-bottom: 30px;
            }

            #atkp-welcome.pro .upgrade,
            #atkp-welcome.pro .footer {
                display: none;
            }

            #atkp-welcome.pro .testimonials {
                border: 2px solid #e1e1e1;
            }

            .dashboard_page_duplicator-getting-started .video-container {
                border: 2px solid #e1e1e1;
            }

            .dashboard_page_duplicator-getting-started #wpfooter,
            .dashboard_page_duplicator-getting-started div.notice {
                display: none !important;
            }

        </style>


		<?php

	}


}

?>