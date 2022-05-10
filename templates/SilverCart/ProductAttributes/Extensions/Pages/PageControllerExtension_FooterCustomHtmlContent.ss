<% if $ProductAttributeNavigationItem %>
    <% with $ProductAttributeNavigationItem %>
<div class="d-none" id="container-choose-product-attribute" data-base-link="{$CurrentPage.OriginalLink}" data-url-segment="{$CurrentPage.URLSegment}">
    {$forTemplate('HeaderNavItem')}
</div>
        
<div class="modal fade <% if $CurrentPage.ShowChooseGlobalProductAttributesModal %>show-on-page-load<% end_if %>" id="modal-choose-product-attribute" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false" data-reload-page="{$CurrentPage.ReloadPageAfterChooseGlobalProductAttributesModal}" data-allow-multiple-choice="{$AllowMultipleChoice}">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <% if $FontAwesomeIcon %>
                        {$FontAwesomeIconHTML('text-blue-dark')}
                    <% end_if %>
                    <% if $NavigationItemTitle %>
                        {$NavigationItemTitle}
                    <% else %>
                        <%t SilverCart\ProductAttributes.Choose 'Choose {title}' title=$Title %>
                    <% end_if %>
                </h5>
            </div>
            <div class="modal-body">
                <div class="text-center my-5 py-5"><span class="spinner spinner-border"></span></div>
            </div>
        <% if $AllowMultipleChoice || $AllowGlobalReset %>
            <div class="modal-footer justify-content-end d-none">
            <% if $AllowGlobalReset %>
                <a href="{$ResetGloballyLink}" class="btn btn-outline-blue-dark btn-reset"><%t SilverCart\ProductAttributes.ResetSelection 'ResetSelection' %></a>
            <% end_if %>
            <% if $AllowMultipleChoice %>
                <button type="button" class="btn btn-success" data-dismiss="modal"><%t SilverCart.Done 'Done' %></button>
            <% end_if %>
            </div>
        <% end_if %>
        </div>
    </div>
</div>
<% require javascript("silvercart/product-attributes:client/js/ProductAttributeNavigationItem.js") %>
    <% end_with %>
<% end_if %>
<% if $ProductAddCartFormModals.exists %>
    <% loop $ProductAddCartFormModals %>
        <% include SilverCart\ProductAttributes\ModalAddToCart %>
    <% end_loop %>
<% end_if %>