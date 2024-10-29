<?php

/** @var $formatter atkp_formatter */
/** @var $parameters atkp_template_parameters */
/** @var $products atkp_product[]|null */
?>

<div class="atkp-container atkp-box-box {{$parameters->get_css_container_class()}}">
    @if($products != null)
        @foreach ($products as $product)
            <div class="atkp-box atkp-clearfix atkp-smallbox {{$parameters->get_css_element_class()}}"
                 style="{{$formatter->get_predicate_borderstyle($product)}}">
                <div class="atkp-predicateheadline atkp-predicate-highlight{{$formatter->get_predicate_id($product)}}">
                    <span>{!! $formatter->get_predicate_text($product) !!}</span></div>
                <div class="atkp-thumb">
                    @if($parameters->get_linkimage() && $formatter->is_title_link_available($product))
                        <a {!! $formatter->get_title_link($product) !!}><img class="atkp-image"
                                                                             src="{{$formatter->get_mediumnimageurl($product)}}"
                                                                             alt="{{$formatter->get_title($product)}}"/></a>
                    @else
                        <img class="atkp-image" src="{{$formatter->get_mediumnimageurl($product)}}"
                             alt="{{$formatter->get_title($product)}}"/>
                    @endif
                        @if($parameters->get_showstarrating() && (!$parameters->get_hideemptystars() || $product->rating > 0))
                        <div class="atkp-rating">{!!$formatter->get_star_rating($product)!!}</div>
                        @if($parameters->get_showrating()  && (!$parameters->get_hideemptyrating() || $product->reviewcount > 0))
                            @if($parameters->get_linkrating())
                                <div class="atkp-reviews">
                                    <a {!! $formatter->get_reviewslink($product) !!}>{!! $formatter->get_reviewstext($product) !!}{!! $formatter->get_reviewsmark($product) !!}</a>
                                </div>
                            @else
                                <div class="atkp-reviews">{!! $formatter->get_reviewstext($product) !!}</div>
                            @endif
                        @endif
                    @endif
                </div>
                <div class="atkp-content">
                    @if($formatter->is_title_link_available($product))
                        <a class="atkp-title" {!! $formatter->get_title_link($product) !!}>{{$formatter->get_shorttitle($product)}}{!! $formatter->get_title_mark($product) !!}</a>
                    @else
                        <span class="atkp-title">{{$formatter->get_shorttitle($product)}}</span>
                    @endif
                    <div class="atkp-author">{{$formatter->get_bytext($product)}}</div>
                    @if($parameters->get_showshopname())
                        <div class="atkp-shoplogo">{!! $formatter->get_shop_logo($formatter->get_shop_value($product)) !!}</div>
                    @endif
                </div>
                <div class="atkp-bottom">
                    @if($parameters->get_showlistprice())
                        <span class="atkp-price atkp-listprice">{{$formatter->get_listpricetext($product, $translator->get_listprice() )}}</span>
                    @endif
                    @if($parameters->get_showpricediscount())
                        <span class="atkp-price atkp-savedamount">{{$formatter->get_savetext($product, $translator->get_yousave() )}}{{$formatter->get_percentagesaved($product, ' (-%s%%)')}}</span>
                    @endif
                    @if($parameters->get_showprice())
                        <span class="atkp-price atkp-saleprice">{!! $formatter->get_primelogo($product) !!}
                            &nbsp;{{$formatter->get_pricetext($product, $translator->get_price(), $translator->get_pricenotavailable())}}
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
                </div>
            </div>
        @endforeach

        @if(is_array($products) && count($products) > 0 && $parameters->get_show_disclaimer())
            <span class="atkp-disclaimer">{!! $formatter->get_disclaimer($products[0], $parameters->get_disclaimer_text()) !!}</span>
        @endif
    @endif
</div>