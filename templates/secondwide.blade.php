<div class="atkp-container atkp-secondwide-box {{$parameters->get_css_container_class()}}">
    @if($products != null)
        @foreach ($products as $product)
            <div class="atkp-box atkp-secondbox atkp-clearfix {{$parameters->get_css_element_class()}}"
                 style="{{$formatter->get_predicate_borderstyle($product)}}">
                <div class="atkp-predicateheadline atkp-predicate-highlight{{$formatter->get_predicate_id($product)}}">
                    <span>{!! $formatter->get_predicate_text($product) !!}</span></div>
                @if($formatter->is_title_link_available($product))
                    <a class="atkp-title" {!! $formatter->get_title_link($product) !!}>{{$formatter->get_shorttitle($product)}}{!! $formatter->get_title_mark($product) !!}</a>
                @else
                    <span class="atkp-title">{{$formatter->get_shorttitle($product)}}</span>
                @endif

                <div class="atkp-thumb">
                    @if(atkp_options::$loader->get_linkimage() && $formatter->is_title_link_available($product) )
                        <a {!! $formatter->get_title_link($product) !!}><img class="atkp-image"
                                                                             src="{{$formatter->get_mediumnimageurl($product)}}"
                                                                             alt="{{$formatter->get_title($product)}}"/></a>
                    @else
                        <img class="atkp-image" src="{{$formatter->get_mediumnimageurl($product)}}"
                             alt="{{$formatter->get_title($product)}}"/>
                    @endif
                </div>
                <div class="atkp-bottom">
                    @if(atkp_options::$loader->get_showprice())
                        <span class="atkp-price atkp-saleprice">{!! $formatter->get_primelogo($product) !!}
                            &nbsp;{{$formatter->get_pricetext($product, $translator->get_price(), $translator->get_pricenotavailable())}}
                            @if(atkp_options::$loader->get_showbaseprice())
                                <span class="atkp_price atkp-baseprice">{{$formatter->get_basepricetext($product)}}</span>
                            @endif
                        </span>
                    @endif


                        @if(atkp_options::$loader->get_showstarrating() && (!atkp_options::$loader->get_hideemptystars() || $product->rating > 0))
                        <div class="atkp-rating">{!!$formatter->get_star_rating($product)!!}
                            <span>
                                @if(atkp_options::$loader->get_showrating()  && (!atkp_options::$loader->get_hideemptyrating() || $product->reviewcount > 0))
                                    @if(atkp_options::$loader->get_linkrating())
                                        <a {!! $formatter->get_reviewslink($product) !!}>({!! $formatter->get_reviewstext($product) !!}{!! $formatter->get_reviewsmark($product) !!})</a>
                                    @else
                                        ({!! $formatter->get_reviewstext($product) !!})
                                    @endif
                                @endif
                            </span>
                        </div>
                    @endif

                    @if($formatter->is_button_link_available($product))
                        <a {!! $formatter->get_button_link($product) !!} class="atkp-button">{!! $formatter->get_button_text($product)!!}{!! $formatter->get_button_mark($product) !!}</a>
                    @endif
                    @if($parameters->get_show_moreoffers() && count($formatter->get_offers($product, false)) > 0)
                        @include($parameters->get_moreoffers_template(),['formatter' => $formatter, 'translator' => $translator, 'product' => $product, 'parameters' =>$parameters])
                    @endif
                    <div class="atkp-container"></div>
                    <span class="atkp-priceinfo">{{$formatter->get_priceinfotext($product)}}</span>
                    @if(atkp_options::$loader->get_showshopname())
                        <div class="atkp-shoplogo">{!! $formatter->get_shop_logo($formatter->get_shop_value($product)) !!}</div>
                    @endif
                </div>
            </div>
        @endforeach
        @if(is_array($products) && count($products) > 0 && $parameters->get_show_disclaimer())
            <span class="atkp-disclaimer">{!! $formatter->get_disclaimer($products[0]) !!}</span>
        @endif
    @endif
</div>
