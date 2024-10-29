<?php
/** @var $formatter atkp_formatter */
/** @var $parameters atkp_template_parameters */
/** @var $products atkp_product[]|null */
?>
<div class="atkp-ajax_load-box {{$parameters->get_css_container_class()}}">
	<?php
	$str_params = json_encode( $parameters->data, JSON_PRETTY_PRINT );
	$prd_ids    = array();
	$list_idx   = 0;
	foreach ( $products as $p )
		$prd_ids[] = array( 'product_id' => $p->productid, 'list_id' => $p->listid, 'list_idx' => $list_idx ++ );

	$str_products = json_encode( $prd_ids, JSON_PRETTY_PRINT );

	$uid = uniqid();
	?>

    <script type="application/json"
            id="<?php echo esc_attr('atkp-data-parameters-' . $uid) ?>"><?php echo $str_params; ?></script>
    <script type="application/json"
            id="<?php echo esc_attr('atkp-data-products-' . $uid) ?>"><?php echo $str_products; ?></script>
    <div class="atkp-ajax-container" data-uid="<?php echo esc_html($uid) ?>"
         data-endpointurl="{!! ATKPTools::get_endpointurl() !!}"></div>
</div>

