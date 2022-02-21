var silvercart            = silvercart            ? silvercart            : [];
    silvercart.attributes = silvercart.attributes ? silvercart.attributes : [];
silvercart.attributes.navigationItem = (function () {
    var property = {
            allowMultipleChoice: false,
        },
        selector = {
            container:                   '#container-choose-product-attribute',
            headerLeft:                  '.header__left',
            navMainHeader:               '#main-header .nav',
            navItem:                     '.nav-item-product-attribute',
            modalChooseProductAttribute:
            {
                selector:                        '#modal-choose-product-attribute',
                containerChooseProductAttribute: '.container-choose-product-attribute',
                btnChooseProductAttribute:       '.btn-choose-product-attribute',
            },
        },
        private = {
            getTargetLink: function(action)
            {
                var baseLink   = $(selector.container).data('base-link'),
                    urlSegment = $(selector.container).data('url-segment');
                if (baseLink === '/') {
                    baseLink += urlSegment + '/';
                }
                return baseLink + action;
            },
            modalChooseProductAttribute:
            {
                initModal: function(event)
                {
                    var modal = $(this);
                    modal.find('.modal-body').html('<div class="text-center"><span class="spinner-border spinner-border-lg"></span></div>');
                    $.ajax({
                        url:   private.getTargetLink('modalChooseProductAttribute'),
                        success: function(data) {
                            modal.find('.modal-body').html(data);
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            modal.find('.modal-body').html('<div class="alert alert-danger mb-0">' + ss.i18n._t('SilverCart.AnErrorOccurred', 'An error occurred. Please try again.') + '</div>');
                        }
                    });
                },
                containerChooseProductAttributeClick: function(event)
                {
                    $(selector.modalChooseProductAttribute.btnChooseProductAttribute, $(this)).trigger('click');
                },
                btnChooseProductAttributeClick: function(event)
                {
                    event.preventDefault();
                    var btn = $(this);
                    if (!property.allowMultipleChoice
                     && $(btn).hasClass('btn-success')
                    ) {
                        setTimeout(function() {
                            $(selector.modalChooseProductAttribute.selector).modal('hide');
                        }, 300);
                        return false;
                    }
                    btn.attr('disabled', 'disabled');
                    btn.addClass('disabled');
                    btn.prepend('<span class="spinner-border spinner-border-sm"></span>');
                    $(btn).closest(selector.modalChooseProductAttribute.containerChooseProductAttribute).css({transistion: 'background-color 0.5s'});
                    $.ajax({
                        url:   btn.attr('href'),
                        type:  'POST',
                        data: {
                            'ajax': 1,
                        },
                        success: function(data) {
                            var response = $.parseJSON(data),
                                altText  = $(btn).data('alt-text'),
                                text     = $(btn).html(),
                                itemID   = $(btn).data('item-id'),
                                input    = $('input#silvercart-product-attribute-value-' + itemID);
                            $(selector.navItem).replaceWith(response.HTMLNavItem);
                            if (!property.allowMultipleChoice) {
                                $(selector.modalChooseProductAttribute.btnChooseProductAttribute).each(function() {
                                    var altText  = $(this).data('alt-text'),
                                        text     = $(this).html();
                                    if ($(this).data('item-id') === $(btn).data('item-id')) {
                                        return;
                                    }
                                    if ($(this).hasClass('btn-success')) {
                                        $(this).closest(selector.modalChooseProductAttribute.containerChooseProductAttribute)
                                                .removeClass('bg-success')
                                                .removeClass('text-white');
                                        $(this)
                                                .removeClass('btn-success')
                                                .addClass('btn-outline-success')
                                                .data('alt-text', text)
                                                .html(altText);
                                    }
                                });
                            }
                            if (response.Added) {
                                $(btn).closest(selector.modalChooseProductAttribute.containerChooseProductAttribute)
                                        .addClass('bg-success')
                                        .addClass('text-white');
                                $(btn)
                                        .removeClass('btn-outline-success')
                                        .addClass('btn-success')
                                        .data('alt-text', text)
                                        .html(altText);
                                if (typeof input === "object"
                                 && input.length > 0
                                 && !input.is(':checked')
                                ) {
                                    input.trigger('click');
                                }
                                if (!property.allowMultipleChoice) {
                                    setTimeout(function() {
                                        $(selector.modalChooseProductAttribute.selector).modal('hide');
                                    }, 500);
                                }
                            } else {
                                $(btn).closest(selector.modalChooseProductAttribute.containerChooseProductAttribute)
                                        .removeClass('bg-success')
                                        .removeClass('text-white');
                                $(btn)
                                        .removeClass('btn-success')
                                        .addClass('btn-outline-success')
                                        .data('alt-text', text)
                                        .html(altText);
                                if (typeof input === "object"
                                 && input.length > 0
                                 && input.is(':checked')
                                ) {
                                    input.trigger('click');
                                }
                            }
                            btn.removeAttr('disabled');
                            btn.removeClass('disabled');
                            $('.spinner-border', btn).remove();
                            $('.spinner-grow', btn).remove();
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            $(selector.modalChooseProductAttribute.selector).find('.modal-body').prepend('<div class="alert alert-danger">' + ss.i18n._t('SilverCart.AnErrorOccurred', 'An error occurred. Please try again.') + '</div>');
                            btn.removeAttr('disabled');
                            btn.removeClass('disabled');
                            $('.spinner-border', btn).remove();
                            $('.spinner-grow', btn).remove();
                        }
                    });
                    return false;
                }
            },
        },
        public = {
            init: function()
            {
                if (typeof $(selector.container) === "object"
                 && $(selector.container).length > 0
                ) {
                    if (typeof $(selector.headerLeft) === "object"
                     && $(selector.headerLeft).length > 0
                    ) {
                        $(selector.headerLeft).append($(selector.container).html());
                    } else if (typeof $(selector.navMainHeader) === "object"
                            && $(selector.navMainHeader).length > 0
                    ) {
                        $(selector.navItem).addClass('pt-36 pr-8 border-right reload')
                                           .removeClass('mr-8');
                        $(selector.navMainHeader).prepend($(selector.container).html());
                    }
                    $(selector.container).html('');
                    $(selector.modalChooseProductAttribute.selector).on('show.bs.modal', private.modalChooseProductAttribute.initModal);
                    $(document).on('click', selector.modalChooseProductAttribute.btnChooseProductAttribute, private.modalChooseProductAttribute.btnChooseProductAttributeClick);
                    $(document).on('click', selector.modalChooseProductAttribute.containerChooseProductAttribute, private.modalChooseProductAttribute.containerChooseProductAttributeClick);
                    if ($(selector.modalChooseProductAttribute.selector).hasClass('show-on-page-load')) {
                        $(selector.modalChooseProductAttribute.selector).modal('show');
                    }
                    property.allowMultipleChoice = parseInt($(selector.modalChooseProductAttribute.selector).data('allow-multiple-choice')) === 1;
                }
            },
            reload: function()
            {
                if (typeof $(selector.navItem) === "object"
                 && $(selector.navItem).length > 0
                ) {
                    $.ajax({
                        url:   $(selector.navItem).data('reload-link'),
                        type:  'POST',
                        data: {
                            'ajax': 1,
                        },
                        success: function(data) {
                            $(selector.navItem).replaceWith(data);
                        }
                    });
                }
            },
        };
    return public;
});
$(document).ready(function() {
    silvercart.attributes.navigationItem().init();
});