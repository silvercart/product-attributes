
$(function() {
    $(document).ready(function() {
        $('select[data-type="single-variant"]').change(function() {
            var productID = $(this).data('product-id');
            $('#product-price-' + productID).html($('option:selected', $(this)).data('price'));
        });
    });
});
