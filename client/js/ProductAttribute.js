$(function() {
    $(document).ready(function() {
        $(document).on('click', '.clickable-silvercart-attribute a', function() {
            var attributeID = $(this).closest('.clickable-silvercart-attribute').data('attributeId'),
                attributeValueID = $(this).closest('.clickable-silvercart-attribute').data('attributeValueId');

            $('select[name="ProductAttribute' + attributeID + '"]').val(attributeValueID);
            var originalColor = $('#ProductAttribute' + attributeID + '_Holder').css('color');
            $('#ProductAttribute' + attributeID + '_Holder').css({
                backgroundColor: '#5cb85c',
                color: '#ffffff'
            });
            window.setTimeout(function() {
                $('#ProductAttribute' + attributeID + '_Holder').css({
                    backgroundColor: 'transparent',
                    color: originalColor
                });
            }, 750);
            return false;
        });
    });
});