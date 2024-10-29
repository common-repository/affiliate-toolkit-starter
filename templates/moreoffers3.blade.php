<?php

/** @var $formatter atkp_formatter */
/** @var $parameters atkp_template_parameters */
/** @var $products atkp_product[]|null */
?>
<div class="atkp-moreoffers3-box {{$parameters->get_css_container_class()}}">
    @if($products == null)
        {{ $formatter->set_temp_value('product', $product) }}
    @else
        {{ $formatter->set_temp_value('product', $products[0]) }}
    @endif

    <div class="atkp-moreoffersinfo" style="">
        <div>
            @foreach($formatter->get_offers($formatter->get_temp_value('product'), $parameters->get_moreoffers_includemainoffer(), $parameters->get_moreoffers_count()) as $offer)
                <div class=" atkp-clearfix atkp-moreoffers3-container">

                    <a {!! $formatter->get_offer_productlink($offer) !!} class="atkp-secondbutton atkp-moreoffers3-button"
                       style="">
                        <span style="border-right: gray dotted 1px;padding-right:5px;margin-right:5px">{!! $formatter->get_button_text($product, $offer->shop)!!} {!! $formatter->get_button_mark($product) !!}</span>
                        {!! $formatter->get_shop_smalllogo($offer->shop) !!}
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</div>