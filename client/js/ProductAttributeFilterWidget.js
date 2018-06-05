var silvercart                   = silvercart                   ? silvercart                   : [];
    silvercart.attributes        = silvercart.attributes        ? silvercart.attributes        : [];
    silvercart.attributes.filter = silvercart.attributes.filter ? silvercart.attributes.filter : [];
    
silvercart.attributes.filter.MainSelector        = '#main';
silvercart.attributes.filter.FilterValues        = [];
silvercart.attributes.filter.CallInProgress      = false;
silvercart.attributes.filter.Callback            = false;
silvercart.attributes.filter.CallbackTimeout     = false;
silvercart.attributes.filter.CallbackFunction    = false;

silvercart.attributes.filter.Call                = function() {
    var filterForm      = $('form[name="silvercart-product-attribute-filter-form"]');
    var filterInput     = $('input[name="silvercart-product-attribute-selected-values"]');
    var filterWidget    = $('input[name="silvercart-product-attribute-widget"]');
    if (silvercart.attributes.filter.CallInProgress === false) {
        silvercart.attributes.filter.CallInProgress = true;
        silvercart.attributes.filter.Callback = false;
        $('.silvercart-product-attribute-filter-mask').remove();
        $('.silvercart-product-attribute-filter-loading-bar').remove();
        var mainContainer = $(silvercart.attributes.filter.MainSelector);
        if ($('.silvercart-product-attribute-filter-mask').length === 0) {
            mainContainer.append('<div class="silvercart-product-attribute-filter-mask"></div>');
            mainContainer.append('<img class="silvercart-product-attribute-filter-loading-bar" src="/resources/vendor/silvercart/silvercart/client/img/loader.gif" title="" />');
            mainContainer.css({
                position : 'relative'
            });
        }
        
        $('.silvercart-product-attribute-filter-mask').css({
            width : mainContainer.css('width'),
            height : mainContainer.css('height'),
            position : 'absolute',
            display : 'none',
            top : '0px',
            background : '#fff'
        });
        $('.silvercart-product-attribute-filter-loading-bar').css({
            display : 'none',
            left : (window.innerWidth / 2) - 64,
            top : (window.innerHeight / 2) - 7,
            position : 'fixed',
            width : 128,
            height : 15
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
                    mainContainer.html(html);
                    silvercart.attributes.filter.CallInProgress = false;
                    if (silvercart.attributes.filter.Callback) {
                        silvercart.attributes.filter.Call();
                    }
                    if (typeof silvercart.attributes.filter.CallbackFunction === 'function') {
                        silvercart.attributes.filter.CallbackFunction();
                    }
                },
                'error':    function() {
                    document.location.reload();
                    $('.silvercart-product-attribute-filter-loading-bar').hide('slow', function() {
                        $('.silvercart-product-attribute-filter-mask').fadeOut('slow');
                    });
                }
            });
        });
    } else {
        silvercart.attributes.filter.Callback = true;
    }
};
silvercart.attributes.filter.RefreshSelectedFilters = function(checkbox) {
    var checked     = checkbox.is(':checked');
    var id          = checkbox.val();
    var filterForm  = $('form[name="silvercart-product-attribute-filter-form"]');
    var filterInput = $('input[name="silvercart-product-attribute-selected-values"]');
    if (checked) {
        silvercart.attributes.filter.FilterValues.push(id);
    } else {
        silvercart.attributes.filter.FilterValues = jQuery.grep(silvercart.attributes.filter.FilterValues, function(value) {
            return parseInt(value) !== parseInt(id);
        });
    }
    filterInput.val(silvercart.attributes.filter.FilterValues.join(','));
};
silvercart.attributes.filter.Push = function(id) {
    silvercart.attributes.filter.FilterValues.push(id);
};
silvercart.attributes.filter.setMainSelector = function(selector) {
    silvercart.attributes.filter.MainSelector = selector;
};
$(function() {
    $(document).ready(function() {
        if ($('.silvercart-product-attribute-filter-mask').length === 0) {
            $(silvercart.attributes.filter.MainSelector).append('<div class="silvercart-product-attribute-filter-mask"></div>');
            $(silvercart.attributes.filter.MainSelector).append('<img class="silvercart-product-attribute-filter-loading-bar" src="/resources/vendor/silvercart/silvercart/client/img/loader.gif" alt="Loading..." />');
            $(silvercart.attributes.filter.MainSelector).css({
                position : 'relative'
            });
            $('.silvercart-product-attribute-filter-mask').css({
                position : 'absolute',
                width : $(silvercart.attributes.filter.MainSelector).css('width'),
                height : $(silvercart.attributes.filter.MainSelector).css('height'),
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
    });

    var triggerFilter = function() {
        silvercart.attributes.filter.RefreshSelectedFilters($(this));
        window.clearTimeout(silvercart.attributes.filter.CallbackTimeout);
        silvercart.attributes.filter.CallbackTimeout = window.setTimeout(silvercart.attributes.filter.Call, 800);
    };

    var removeFilter = function(event) {
        event.preventDefault();
        var id = $(this).data('id');
        $('.silvercart-product-attribute-' + id).each(function() {
            $(this).removeAttr('checked');
            silvercart.attributes.filter.RefreshSelectedFilters($(this));
        });
        silvercart.attributes.filter.Call();
        return false;
    };
    
    if (typeof $('.silvercart-product-attribute-value').live === 'function') {
        $('.silvercart-product-attribute-value').live('change', triggerFilter);
        $('.remove-filter').live('click', removeFilter);
    } else if (typeof $('.silvercart-product-attribute-value').on === 'function') {
        $('body').on('change', '.silvercart-product-attribute-value', triggerFilter);
        $('body').on('click', '.remove-filter', removeFilter);
    }
});