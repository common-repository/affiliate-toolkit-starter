<div class="atkp-list-outer-container {{$parameters->get_css_container_class()}}">
    <div class="atkp-display">
        <ol class="atkp-ol-list-display">
            @if($products != null)
                @foreach ($products as $product)
                    <div class="atkp-list-container {{$parameters->get_css_element_class()}}">
                        <li>{{$formatter->get_shorttitle($product)}}</li>
                        <div class="atkp-list">
                            <a class="atkp-list-link">

                                @if($parameters->get_linkimage() && $formatter->is_title_link_available($product))
                                    <a {!! $formatter->get_title_link($product) !!}><img
                                                src="{{$formatter->get_mediumnimageurl($product)}}"
                                                alt="{{$formatter->get_title($product)}}"/></a>
                                @else
                                    <img src="{{$formatter->get_mediumnimageurl($product)}}"
                                         alt="{{$formatter->get_title($product)}}"/>
                                @endif
                            </a>
                            <div>
                                {!! $formatter->get_infotext($product) !!}
                            </div>
                            @if($formatter->is_button_link_available($product))
                                <a {!! $formatter->get_button_link($product) !!} class="atkp-button box-shadow">{!! $formatter->get_button_text($product)!!}{!! $formatter->get_button_mark($product) !!}</a>
                            @endif

                        </div>
                    </div>
                @endforeach
            @endif
        </ol>

    </div>
</div>
