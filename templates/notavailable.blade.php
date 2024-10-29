<?php

/** @var $formatter atkp_formatter */
/** @var $parameters atkp_template_parameters */
/** @var $products atkp_product[]|null */
?>
<div class="atkp-container atkp-notavailable-box {{$parameters->get_css_container_class()}}">
    @foreach ($products as $product)
        <div class="atkp-box atkp-clearfix {{$parameters->get_css_element_class()}} atkp-notavailabledefault">
            <span style="font-weight:bold">{{$formatter->get_shorttitle($product)}}</span>
            <div class="atkp-notavailable">{{$translator->get_productunavailabletext()}}</div>
            <div class="atkp-notavailablebutton">
                <a {!! $formatter->get_button_link($product) !!} class="atkp-button">{{$translator->get_searchproduct()}}{!! $formatter->get_mark($product) !!}</a>
            </div>
        </div>

    @endforeach
</div>