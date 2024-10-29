<?php

/** @var $formatter atkp_formatter */
/** @var $parameters atkp_template_parameters */
/** @var $products atkp_product[]|null */
?>
<div class="atkp-container atkp-sitestripe-box {{$parameters->get_css_container_class()}}" {!! $formatter->get_shop_logourl($formatter->get_shop_value($product)) == '' ? 'style="height:206px"' : ''  !!}>
    @if($products != null)
        @foreach ($products as $product)
            <div class="atkp-sitestripe atkp-clearfix {{$parameters->get_css_element_class()}}">
                <div class="amzn-ad-white-background">
                    @if($formatter->get_shop_logourl($formatter->get_shop_value($product)) != '')
                        <div class="amzn-ad-logo-holder">
                            <a {!! $formatter->get_title_link($product) !!}>
                                <img class=""
                                     src="{!! $formatter->get_shop_logourl($formatter->get_shop_value($product)) !!}"/>
                            </a>
                        </div>
                    @endif
                    <div class="amzn-ad-image-holder">
                        <a {!! $formatter->get_title_link($product) !!}>
                            <img src="{{$formatter->get_mediumnimageurl($product)}}"
                                 alt="{{$formatter->get_title($product)}}"
                                 style="max-width:98px;max-height:95px"
                                 id="prod-image"/>
                        </a>
                    </div>
                </div>
                <div style="">
                    <div class="amzn-ad-prod-detail">
                        <div id="title">
                            <a {!! $formatter->get_title_link($product) !!}>{{ATKPTools::str_shorten($formatter->get_shorttitle($product), 23, '...')}}</a>
                        </div>
                    </div>
                    <div class="amzn-ad-price-block">
                        <span class="price" style="">&nbsp;{{$formatter->get_pricetext($product, '%s', '')}}</span>
                    </div>
                    <div class="amzn-ad-primary-btn logo">
                        <a {!! $formatter->get_title_link($product) !!}>
                            <span style="background-image:url('<?php echo esc_attr(esc_url(plugins_url('/dist/cart.png',ATKP_SITESTRIPE_PLUGIN_FILE))) ?>')"
                                  class="shop"
                                  id="amzn_assoc_shop_now">{!! $translator->get_buynow($product)!!}{!! $formatter->get_button_mark($product) !!}</span>
                        </a>
                    </div>
                </div>

            </div>
        @endforeach

    @endif
</div>

