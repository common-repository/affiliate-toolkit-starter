<?php

/** @var $formatter atkp_formatter */
/** @var $parameters atkp_template_parameters */
/** @var $products atkp_product[]|null */
?>

<table class="atkp-datasheet  atkp-datasheet-box">

    @foreach($formatter->get_comparefields( $products, true, true, 'datasheet') as $comparegroup)
        @if($comparegroup->isvisible)
            <tr class="atkp-producttable-grouprow">
                <td colspan="2">
                    <span class="atkp-producttable-groupheader">{{$comparegroup->caption}}</span>
                    <span class="atkp-producttable-groupdescription">{{$comparegroup->description}}</span>
                </td>
            </tr>
        @endif


        @foreach($comparegroup->values as $comparevalue)
            <tr>
                <td class="atkp-datasheet-caption">
                    @if ( $comparevalue->description != '' )
                        <div class="atkp-tooltip">{{ $comparevalue->caption }}<span
                                    class="atkp-tooltiptext">{{ $comparevalue->description }}</span></div>
                    @else
                        {{ $comparevalue->caption }}
                    @endif
                </td>
                <td class="atkp-datasheet-value">{!! $comparevalue->detail  !!} </td>
            </tr>

        @endforeach
    @endforeach

</table>