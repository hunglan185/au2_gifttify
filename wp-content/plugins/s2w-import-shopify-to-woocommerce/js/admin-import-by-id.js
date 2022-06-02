'use strict';
jQuery(document).ready(function ($) {
    let active = false;
    $('.s2w-button-import').on('click', function () {
        if (active) {
            return;
        }
        let button = $(this);
        let id = $('#s2w-shopify_product_id');
        let product_id = id.val();
        if (product_id) {
            active = true;
            button.addClass('loading');
            $.ajax({
                url: s2w_params_admin_import_by_id.url,
                type: 'POST',
                dataType: 'JSON',
                data: {
                    action: 's2w_import_shopify_to_woocommerce_by_id',
                    product_id: product_id,
                    _s2w_nonce: s2w_params_admin_import_by_id._s2w_nonce,
                },
                success: function (response) {
                    $('.s2w-import-message').append('<p>' + response.message.toString() + '</p>')
                },
                error: function (err) {

                },
                complete: function () {
                    active = false;
                    button.removeClass('loading');
                }
            })
        } else {
            alert('Please enter Shopify product id');
            id.focus();
        }
    })
});
