<?php

/** @var $formatter atkp_formatter */
/** @var $parameters atkp_template_parameters */
/** @var $products atkp_product[]|null */
?>
<div class="atkp-moreoffers2-box {{$parameters->get_css_container_class()}}">
    @if($products == null)
        {{ $formatter->set_temp_value('product', $product) }}
    @else
        {{ $formatter->set_temp_value('product', $products[0]) }}
    @endif

    <div class="atkp-moreoffersinfo" style="padding-top:20px;padding-bottom: 20px">
        <div>
            @foreach($formatter->get_offers($formatter->get_temp_value('product'), $parameters->get_moreoffers_includemainoffer(), $parameters->get_moreoffers_count()) as $offer)
                <div class="atkp-container atkp-clearfix" style="font-size:11px;max-width:300px;margin-left:auto">

                    <a {!! $formatter->get_offer_productlink($offer) !!}>
                        <span class="atkp-more-offers-left" style="width: 35%;">
                            {!! $formatter->get_shop_smalllogo($offer->shop) !!}
                        </span>
                        <span class="atkp-more-offers-right" style="width: 55%;">
                            <span class="atkp-more-offers-price">{{$formatter->get_offer_price($offer, $translator->get_price())}}</span><br/>
                            <span class="atkp-more-offers-shipping atkp-clearfix">{{$formatter->get_offer_shipping($offer,$translator->get_shipping(), $translator->get_shippingna())}}</span>
                            </span>
                        </span>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</div>
