<div class="atkp-variation-container atkp-variationboxes-box {{$parameters->cssContainerClass}}">
    @foreach ($formatter->get_randomarray($formatter->minifyvariations($product->variations, 'Size'), 4) as $variation )
        <a {!! $formatter->get_productlink($variation)!!} title="{{ $formatter->get_variationname($variation) }}">
            <div class="atkp-variation">
                @if($variation->smallimageurl != '')
                    <div class="atkp-variationimage">
                        <img src="{{$formatter->get_smallimageurl($variation)}}">
                    </div>
                @endif
                <div class="atkp-variationinner" @if($variation->smallimageurl == '') style="width:100%" @endif>

                    <div class="atkp-variationname">
                        {{$formatter->get_variationname($variation) }}{!! $formatter->get_link_mark() !!}
                    </div>
                    <div class="atkp-variation-price"> {{$formatter->get_pricetext($variation)}} </div>
                </div>

            </div>
        </a>
    @endforeach
</div>
