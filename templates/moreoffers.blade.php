<?php

/** @var $formatter atkp_formatter */
/** @var $parameters atkp_template_parameters */
/** @var $products atkp_product[]|null */
?>
<div class="atkp-moreoffers-box {{$parameters->get_css_container_class()}}">
    @if($products == null)
        {{ $formatter->set_temp_value('product', $product) }}
    @else
        {{ $formatter->set_temp_value('product', $products[0]) }}
    @endif

	<?php $offers = $formatter->get_offers( $formatter->get_temp_value( 'product' ), $parameters->get_moreoffers_includemainoffer(), $parameters->get_moreoffers_count() ); ?>


    @if(count($offers) > 0)
        <div class="atkp-moreoffersinfo">
            <div class="atkp-offers-dropdown">
                <a class="atkp-offers-dropbtn" style="font-size:12px">{{$parameters->get_moreoffers_title()}}</a>
                <div class="atkp-offers-dropdown-content">
                    @foreach($offers as $offer)
                        <div class="atkp-container atkp-clearfix">
                            <a {!! $formatter->get_offer_productlink($offer) !!} >
                                <span class="atkp-more-offers-left" style="width: 25%;">
                                    {!! $formatter->get_shop_smalllogo($offer->shop) !!}
                                    </span>
                                <span class="atkp-more-offers-right" style="width: 65%;">
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
    @endif
</div>