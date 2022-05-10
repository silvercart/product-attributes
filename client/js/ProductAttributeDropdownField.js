
$(function() {
    $(document).ready(function() {
        $('select[data-type="single-variant"]').change(function() {
            var productID    = $(this).data('product-id'),
                productPrice = $('option:selected', $(this)).data('price');
            if (productPrice !== 0) {
                $('#product-price-' + productID).html(productPrice);
            }
        });
        $(document).on('change', '.silvercart-product-page-productvariant-box.multiple select', function() {
            var parent  = $(this).closest('.silvercart-product-page-productvariant-box');
            var form    = $('<form/>', {
                action: $(this).data('action'),
                method: 'POST'
            });
            var originalForm = $(this).closest('form'),
                div = $('<div/>').css({
                        position: 'absolute',
                        left: '0px',
                        top: '0px',
                        opacity: '0.5',
                        backgroundColor: '#ffffff',
                        backgroundImage: 'url(resources/vendor/silvercart/silvercart/client/img/loader.gif)',
                        backgroundPosition: 'center center',
                        backgroundRepeat: 'no-repeat',
                        width: originalForm.css('width'),
                        height: originalForm.css('height')
                });
            
            $('select', parent).each(function() {
                form.append($('<input/>', {
                    type: 'hidden',
                    name: 'ProductAttributeValue[' + $(this).val() + ']',
                    value: $(this).val()
                }));    
            });
            
                
            originalForm.css('position', 'relative');
            originalForm.append(div);
            
            form.appendTo('body').submit();
        });
        $(document).on('change', '.silvercart-product-page-productvariant-box.multiple-load-id select', function() {
            var form = $(this).closest('form'),
                div  = $('<div/>')
                        .attr('id', form.attr('id') + '-loader')
                        .css({
                        position: 'absolute',
                        left: '0px',
                        top: '0px',
                        opacity: '0.5',
                        backgroundColor: '#ffffff',
                        backgroundImage: 'url(resources/vendor/silvercart/silvercart/client/img/loader.gif)',
                        backgroundPosition: 'center center',
                        backgroundRepeat: 'no-repeat',
                        width: form.css('width'),
                        height: form.css('height'),
                        zIndex: 5
                });
            form.css('position', 'relative');
            form.append(div);
            var formData = new FormData(form[0]);
            formData.append('isAjax', 1);
            formData.append('ajax', 1);
            $.ajax({
                url:  $(form).data('product-attribute-link'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(data) {
                    $('input[name="productID"]', form).val(data);
                    $('#' + form.attr('id') + '-loader').remove();
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    $('#' + form.attr('id') + '-loader').remove();
                }
            });
        });
    });
});
