if (typeof jQuery === 'undefined') {
    console.log('no jquery loaded');
} else {
    var $j = jQuery.noConflict();
    $j(document).ready(function () {
        $j(".atkp-ajax-container").each(function (i, obj) {

            var endpointurl = $j(obj).attr('data-endpointurl');
            var uid = $j(obj).attr('data-uid');

            var x = $j('#atkp-data-parameters-' + uid).html();
            var x2 = $j('#atkp-data-products-' + uid).html();

            var atkpparameters = JSON.parse(x);
            var atkpproducts = JSON.parse(x2);

            $j(obj).addClass('atkp-spinloader-round');

            $j.post(endpointurl,
                {
                    action: 'atkp_render_template',
                    products: JSON.stringify(atkpproducts),
                    parameters: JSON.stringify(atkpparameters),
                },
                function (data, status) {

                    if (status == 'success') {
                        //hide info??

                        switch (data.status) {
                            case 'okay':
                                //rendering ok
                                $j(obj).html(data.html);
                                break;
                            case 'error':
                                $j(obj).html(data.error + '<br />' + data.message);
                                break;
                            default:
                                $j(obj).html("unknown error on loading");
                                break;
                        }

                    }

                    $j(obj).removeClass('atkp-spinloader-round');
                }).fail(function () {
                $j(obj).removeClass('atkp-spinloader-round');
                $j(obj).html("server side error on loading");
            });
        });
    });
}