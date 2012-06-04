
var SilvercartProductAttributeFilter                    = [];
var SilvercartProductAttributeFilterCallInProgress      = false;
var SilvercartProductAttributeFilterCallback            = false;
var SilvercartProductAttributeFilterCallbackTimeout     = false;

var SilvercartProductAttributeFilterCall                = function() {
    var filterForm      = $('form[name="silvercart-product-attribute-filter-form"]');
    var filterInput     = $('input[name="silvercart-product-attribute-selected-values"]');
    var filterWidget    = $('input[name="silvercart-product-attribute-widget"]');
    if (SilvercartProductAttributeFilterCallInProgress == false) {
        SilvercartProductAttributeFilterCallInProgress = true;
        SilvercartProductAttributeFilterCallback = false;
        if ($('.silvercart-product-attribute-filter-mask').length == 0) {
            $('#main').append('<div class="silvercart-product-attribute-filter-mask"></div>');
            $('#main').append('<img class="silvercart-product-attribute-filter-loading-bar" src="/silvercart_product_attributes/images/loader.gif" title="" />');
            $('#main').css({
                position : 'relative'
            });
        }
        $('.silvercart-product-attribute-filter-mask').css({
            width : $('#main').css('width'),
            height : $('#main').css('height'),
            display : 'none'
        });
        $('.silvercart-product-attribute-filter-loading-bar').css({
            display : 'none',
            left : (window.innerWidth / 2) - 64,
            top : (window.innerHeight / 2) - 7
        });
        $('.silvercart-product-attribute-filter-mask').fadeTo('slow', 0.7, function() {
            $('.silvercart-product-attribute-filter-loading-bar').show();
            $.ajax({
                'async':      true,
                'type':       'POST',
                'url':        filterForm.attr('action'),
                'data':       {
                    'silvercart-product-attribute-selected-values'  : filterInput.val(),
                    'silvercart-product-attribute-widget'           : filterWidget.val(),
                    'ajax'                                          : 1
                },
                'success':    function(html) {
                    $('#main').html(html);
                    SilvercartProductAttributeFilterCallInProgress = false;
                    if (SilvercartProductAttributeFilterCallback) {
                        SilvercartProductAttributeFilterCall();
                    }
                },
                'error':    function() {
                    $('.silvercart-product-attribute-filter-loading-bar').hide('slow', function() {
                        $('.silvercart-product-attribute-filter-mask').fadeOut('slow');
                    });
                }
            });
        });
    } else {
        SilvercartProductAttributeFilterCallback = true;
    }
}

var SilvercartProductAttributeFilterRefreshSelectedFilters  = function(checkbox) {
    var checked     = checkbox.is(':checked');
    var id          = checkbox.val();
    var filterForm  = $('form[name="silvercart-product-attribute-filter-form"]');
    var filterInput = $('input[name="silvercart-product-attribute-selected-values"]');
    if (checked) {
        SilvercartProductAttributeFilter.push(id);
    } else {
        SilvercartProductAttributeFilter = jQuery.grep(SilvercartProductAttributeFilter, function(value) {
            return value != id;
        });
    }
    filterInput.val(SilvercartProductAttributeFilter.join(','));
}
var SilvercartProductAttributeFilterPush                = function(id) {
    SilvercartProductAttributeFilter.push(id);
}
$(function() {
    if ($('.silvercart-product-attribute-filter-mask').length == 0) {
        $('#main').append('<div class="silvercart-product-attribute-filter-mask"></div>');
        $('#main').append('<img class="silvercart-product-attribute-filter-loading-bar" src="/silvercart_product_attributes/images/loader.gif" alt="Loading..." />');
        $('#main').css({
            position : 'relative'
        });
        $('.silvercart-product-attribute-filter-mask').css({
            position : 'absolute',
            width : $('#main').css('width'),
            height : $('#main').css('height'),
            display : 'none',
            top : '0px',
            background : '#fff'
        });
        $('.silvercart-product-attribute-filter-loading-bar').css({
            display : 'none',
            position : 'fixed',
            left : (window.innerWidth / 2) - 64,
            top : (window.innerHeight / 2) - 7,
            width : 128,
            height : 15
        });
    }

    $('.silvercart-product-attribute-value').live('change',function() {
        SilvercartProductAttributeFilterRefreshSelectedFilters($(this));
        window.clearTimeout(SilvercartProductAttributeFilterCallbackTimeout);
        SilvercartProductAttributeFilterCallbackTimeout = window.setTimeout(SilvercartProductAttributeFilterCall, 800);
    });

    $('.remove-filter').live('click', function(event) {
        event.preventDefault();
        var id = $(this).attr('rel');
        $('.silvercart-product-attribute-' + id).each(function() {
            $(this).removeAttr('checked');
            SilvercartProductAttributeFilterRefreshSelectedFilters($(this));
        });
        SilvercartProductAttributeFilterCall();
        return false;
    });
});