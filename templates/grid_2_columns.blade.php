<?php

/** @var $formatter atkp_formatter */
/** @var $parameters atkp_template_parameters */
/** @var $products atkp_product[]|null */
?>
<div class="atkp-container atkp-grid_2_columns-box {{$parameters->get_css_container_class()}}">
    @if($products != null)
        <div class="atkp-clearfix atkp-box-2-cols {{$parameters->get_css_element_class()}}">
            @foreach ($products as $product)
                <div class="atkp-box atkp-smallbox atkp-box-2-cols-item atkp-clearfix ">
                    <div class="atkp-thumb">
                        @if($parameters->get_linkimage() && $formatter->is_title_link_available($product))
                            <a {!! $formatter->get_title_link($product) !!}><img class="atkp-image"
                                                                                 src="{{$formatter->get_mediumnimageurl($product)}}"
                                                                                 alt="{{$formatter->get_title($product)}}"/></a>
                        @else
                            <img class="atkp-image" src="{{$formatter->get_mediumnimageurl($product)}}"
                                 alt="{{$formatter->get_title($product)}}"/>
                        @endif
                    </div>
                    <div class="atkp-content">
                        @if($formatter->is_title_link_available($product))
                            <a class="atkp-title" {!! $formatter->get_title_link($product) !!}>{{$formatter->get_shorttitle($product)}}{!! $formatter->get_title_mark($product) !!}</a>
                        @else
                            <span class="atkp-title">{{$formatter->get_shorttitle($product)}}</span>
                        @endif
                        <div class="atkp-author">{{$formatter->get_bytext($product)}}</div>
                    </div>
                    <div class="atkp-bottom">
                        <div class="atkp-ratingbar">
                            @if($parameters->get_showstarrating() && (!$parameters->get_hideemptystars() || $product->rating > 0))
                                <div class="atkp-rating">{!!$formatter->get_star_rating($product)!!}</div>
                            @endif
                            <div class="atkp-primelogo">{!! $formatter->get_primelogo($product) !!}</div>
                            <div class="atkp-clearfix"></div>
                        </div>
                        @if($parameters->get_showprice())
                            <span class="atkp-price atkp-saleprice">
                                {{$formatter->get_pricetext($product, $translator->get_price(), $translator->get_pricenotavailable())}}
                                @if($parameters->get_showbaseprice())
                                    <span class="atkp_price atkp-baseprice">{{$formatter->get_basepricetext($product)}}</span>
                                @endif
                            </span>
                        @endif
                        @if($formatter->is_button_link_available($product))
                            <a {!! $formatter->get_button_link($product) !!} class="atkp-button">{!! $formatter->get_button_text($product)!!}{!! $formatter->get_button_mark($product) !!}</a>
                        @endif

                        @if($parameters->get_show_moreoffers() && count($formatter->get_offers($product, false)) > 0)
                            @include($parameters->get_moreoffers_template(),['formatter' => $formatter, 'translator' => $translator, 'product' => $product, 'parameters' =>$parameters])
                        @endif

                        @if($parameters->get_show_priceinfo())
                            <span class="atkp-priceinfo">{{$parameters->get_priceinfo_text()}}</span>
                        @endif
                        @if($parameters->get_showshopname())
                            <div class="atkp-shoplogo">{!! $formatter->get_shop_logo($formatter->get_shop_value($product)) !!}</div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        @if(is_array($products) && count($products) > 0 && $parameters->get_show_disclaimer())
            <span class="atkp-disclaimer">{!! $formatter->get_disclaimer($products[0], $parameters->get_disclaimer_text()) !!}</span>
        @endif
    @endif
</div>