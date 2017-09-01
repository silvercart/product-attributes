<% if hasVariants %>
    <div class="clearfix silvercart-product-page-productvariant-box">
        <h3><% _t('SilvercartProductAttributeAddCartForm.HEADLINE') %>:</h3>

        <% with Form %>
            <% loop CustomHtmlFormFieldsByGroup(SilvercartProductAttributes,CustomHtmlFormFieldSelect) %>
                $CustomHtmlFormField
            <% end_loop %>
        <% end_with %>
    </div>


<script type="text/javascript">
    
    $(document).ready(function() {
        $('.silvercart-product-page-productvariant-box select').live('change', function() {
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
                        backgroundImage: 'url(/silvercart/images/loader.gif)',
                        backgroundPosition: 'center center',
                        backgroundRepeat: 'no-repeat',
                        width: originalForm.css('width'),
                        height: originalForm.css('height')
                });
            
            $('select', parent).each(function() {
                form.append($('<input/>', {
                    type: 'hidden',
                    name: 'SilvercartProductAttributeValue[' + $(this).val() + ']',
                    value: $(this).val()
                }));    
            });
            
                
            originalForm.css('position', 'relative');
            originalForm.append(div);
            
            form.appendTo('body').submit();
        });
    });
    
</script>

<% else_if $hasSingleProductVariants %>
    <div class="clearfix silvercart-product-page-productvariant-box">
        <h3><% _t('SilvercartProductAttributeAddCartForm.HEADLINE') %>:</h3>

        <% with Form %>
            <% loop CustomHtmlFormFieldsByGroup(SilvercartProductAttributesSingle,CustomHtmlFormFieldSelect) %>
                $CustomHtmlFormField
            <% end_loop %>
        <% end_with %>
    </div>
<% end_if %>