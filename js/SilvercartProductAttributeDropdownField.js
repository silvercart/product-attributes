
$(function() {
    $(document).ready(function() {
        $('select[data-type="single-variant"]').change(function() {
            var productID    = $(this).data('product-id'),
                productPrice = $('option:selected', $(this)).data('price');
            if (productPrice !== 0) {
                $('#product-price-' + productID).html(productPrice);
            }
        });
    });
});
