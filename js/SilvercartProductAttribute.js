(function($) {jQuery(document).ready(function() {
    $(document).on('click', '.clickable-silvercart-attribute a', function() {
        var attributeID = $(this).closest('.clickable-silvercart-attribute').data('attributeId'),
            attributeValueID = $(this).closest('.clickable-silvercart-attribute').data('attributeValueId');
            
        $('select[name="SilvercartProductAttribute' + attributeID + '"]').val(attributeValueID);
        var originalColor = $('#SilvercartProductAddCartFormDetail_customHtmlFormSubmit_1_SilvercartProductAttribute' + attributeID + '_Box').css('color');
        $('#SilvercartProductAddCartFormDetail_customHtmlFormSubmit_1_SilvercartProductAttribute' + attributeID + '_Box').css({
            backgroundColor: '#5cb85c',
            color: '#ffffff'
        });
        window.setTimeout(function() {
            $('#SilvercartProductAddCartFormDetail_customHtmlFormSubmit_1_SilvercartProductAttribute' + attributeID + '_Box').css({
                backgroundColor: 'transparent',
                color: originalColor
            });
        }, 750);
        return false;
    });
})})(jQuery);
