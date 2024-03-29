var silvercart            = silvercart            ? silvercart            : [];
    silvercart.attributes = silvercart.attributes ? silvercart.attributes : [];
silvercart.attributes.navigationItem = (function () {
    var property = {
            allowMultipleChoice: false,
            reloadPage:          false,
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
                containerChosen:                 '.container-chosen',
                btnChooseProductAttribute:       '.btn-choose-product-attribute',
                btnReset:                        '#modal-choose-product-attribute .btn-reset',
            },
        },
        private = {
            getTargetLink: function(action)
            {
                var baseLink   = $(selector.container).data('base-link'),
                    urlSegment = $(selector.container).data('url-segment'),
                    linkWithAction;
                if (baseLink === '/') {
                    baseLink += urlSegment + '/';
                }
                if (baseLink.indexOf('?') >= 0) {
                    linkWithAction = baseLink.substring(0, baseLink.indexOf('?'))
                                   + action
                                   + baseLink.substring(baseLink.indexOf('?'));
                } else {
                    linkWithAction = baseLink + action;
                }
                return linkWithAction;
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
                            var footer = modal.find('.modal-footer');
                            if (footer.length > 0) {
                                footer.removeClass('d-none');
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            modal.find('.modal-body').html('<div class="alert alert-danger mb-0">' + ss.i18n._t('SilverCart.AnErrorOccurred', 'An error occurred. Please try again.') + '</div>');
                        }
                    });
                },
                hideModal: function(event)
                {
                    var footer = $(this).find('.modal-footer');
                    if (footer.length > 0) {
                        footer.addClass('d-none');
                    }
                },
                containerChooseProductAttributeClick: function(event)
                {
                    $(selector.modalChooseProductAttribute.btnChooseProductAttribute, $(this)).trigger('click');
                },
                btnResetClick: function(event)
                {
                    event.preventDefault();
                    var btn = $(this);
                    btn.attr('disabled', 'disabled');
                    btn.addClass('disabled');
                    btn.prepend('<span class="spinner-border spinner-border-sm mr-10"></span>');
                    $.ajax({
                        url:   btn.attr('href'),
                        type:  'POST',
                        data: {
                            'ajax': 1,
                        },
                        success: function(data) {
                            var response = $.parseJSON(data),
                                itemID   = $(btn).data('item-id'),
                                input    = $('input#silvercart-product-attribute-value-' + itemID);
                            $(selector.navItem).replaceWith(response.HTMLNavItem);
                            $(selector.modalChooseProductAttribute.btnChooseProductAttribute).each(function() {
                                if ($(this).hasClass('chosen')) {
                                    $(selector.modalChooseProductAttribute.containerChosen, $(this).closest(selector.modalChooseProductAttribute.containerChooseProductAttribute))
                                            .addClass('d-none');
                                    $(this).removeClass('chosen');
                                }
                            });
                            btn.removeAttr('disabled');
                            btn.removeClass('disabled');
                            $('.spinner-border', btn).remove();
                            $('.spinner-grow', btn).remove();
                            if (property.reloadPage) {
                                private.modalChooseProductAttribute.reloadPage(response.URLSegment);
                                return;
                            } else {
                                setTimeout(function() {
                                    $(selector.modalChooseProductAttribute.selector).modal('hide');
                                }, 300);
                            }
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
                },
                btnChooseProductAttributeClick: function(event)
                {
                    event.preventDefault();
                    var btn = $(this);
                    if (!property.allowMultipleChoice
                     && $(btn).hasClass('chosen')
                    ) {
                        setTimeout(function() {
                            $(selector.modalChooseProductAttribute.selector).modal('hide');
                        }, 300);
                        return false;
                    }
                    btn.attr('disabled', 'disabled');
                    btn.addClass('disabled');
                    btn.prepend('<span class="spinner-border spinner-border-sm p-absolute l-5 b-10"></span>');
                    $(btn).closest(selector.modalChooseProductAttribute.containerChooseProductAttribute).css({transistion: 'background-color 0.5s'});
                    $.ajax({
                        url:   btn.attr('href'),
                        type:  'POST',
                        data: {
                            'ajax': 1,
                        },
                        success: function(data) {
                            var response = $.parseJSON(data),
                                itemID   = $(btn).data('item-id'),
                                input    = $('input#silvercart-product-attribute-value-' + itemID);
                            $(selector.navItem).replaceWith(response.HTMLNavItem);
                            if (!property.allowMultipleChoice) {
                                $(selector.modalChooseProductAttribute.btnChooseProductAttribute).each(function() {
                                    if ($(this).data('item-id') === $(btn).data('item-id')) {
                                        return;
                                    }
                                    if ($(this).hasClass('chosen')) {
                                        $(selector.modalChooseProductAttribute.containerChosen, $(this).closest(selector.modalChooseProductAttribute.containerChooseProductAttribute))
                                                .addClass('d-none');
                                        $(this).removeClass('chosen');
                                    }
                                });
                            }
                            if (response.Added) {
                                $(selector.modalChooseProductAttribute.containerChosen, btn.closest(selector.modalChooseProductAttribute.containerChooseProductAttribute))
                                        .removeClass('d-none');
                                $(btn)
                                        .addClass('chosen');
                                if (typeof input === "object"
                                 && input.length > 0
                                 && !input.is(':checked')
                                ) {
                                    input.trigger('click');
                                }
                                if (!property.allowMultipleChoice) {
                                    if (property.reloadPage) {
                                        private.modalChooseProductAttribute.reloadPage(response.URLSegment);
                                        return;
                                    } else {
                                        setTimeout(function() {
                                            $(selector.modalChooseProductAttribute.selector).modal('hide');
                                        }, 500);
                                    }
                                }
                            } else {
                                $(selector.modalChooseProductAttribute.containerChosen, btn.closest(selector.modalChooseProductAttribute.containerChooseProductAttribute))
                                        .addClass('d-none');
                                $(btn)
                                        .removeClass('chosen');
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
                },
                reloadPage: function(urlSegment)
                {
                    var target     = location.origin + location.pathname,
                        search     = location.search,
                        urlParams  = new URLSearchParams(search),
                        value      = urlParams.get('scpa[' + urlSegment + ']');
                    search = search.replace('&scpasm=1', '');
                    search = search.replace('scpasm=1', '');
                    if (value !== null) {
                        search = search.replace('&scpa%5B' + urlSegment + '%5D=' + value, '');
                        search = search.replace('scpa%5B' + urlSegment + '%5D=' + value, '');
                        search = search.replace('&scpa[' + urlSegment + ']=' + value, '');
                        search = search.replace('scpa[' + urlSegment + ']=' + value, '');
                    }
                    search = search.replace('?&', '?');
                    if (search === '?') {
                        location.href = target;
                    } else {
                        location.href = target + search;
                    }
                },
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
                    $(selector.modalChooseProductAttribute.selector).on('hide.bs.modal', private.modalChooseProductAttribute.hideModal);
                    $(document).on('click', selector.modalChooseProductAttribute.btnChooseProductAttribute, private.modalChooseProductAttribute.btnChooseProductAttributeClick);
                    $(document).on('click', selector.modalChooseProductAttribute.btnReset, private.modalChooseProductAttribute.btnResetClick);
                    $(document).on('click', selector.modalChooseProductAttribute.containerChooseProductAttribute, private.modalChooseProductAttribute.containerChooseProductAttributeClick);
                    if ($(selector.modalChooseProductAttribute.selector).hasClass('show-on-page-load')) {
                        $(selector.modalChooseProductAttribute.selector).modal('show');
                    }
                    property.allowMultipleChoice = parseInt($(selector.modalChooseProductAttribute.selector).data('allow-multiple-choice')) === 1;
                    property.reloadPage          = parseInt($(selector.modalChooseProductAttribute.selector).data('reload-page')) === 1;
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