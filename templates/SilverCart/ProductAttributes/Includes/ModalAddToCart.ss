<div class="modal fade" id="modal-product-variants-{$Product.ID}" tabindex="-1" role="dialog" aria-labelledby="modal-product-variants-{$Product.ID}-label" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form {$addExtraClass('form addcartformtile').addErrorClass('was-validated').AttributesHTML} data-product-attribute-link="{$ProductAttributeLoadProductIDLink}">
            <% loop $HiddenFields %>
                {$Field}
            <% end_loop %>
            {$CustomFormSpecialFields}
            <% with $Fields.dataFieldByName('productQuantity') %>
                <input type="hidden" value="{$Value}" name="{$Name}" />
            <% end_with %>
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-product-variants-{$Product.ID}-label"><%t SilverCart\ProductAttributes.PleaseChooseVariantFor 'Please choose a variant for {title}' title=$Product.Title %></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="clearfix silvercart-product-page-productvariant-box <% if $Product.hasVariants %>multiple-load-id<% end_if %>">
                        <% loop $Fields %>
                            <% if $Name.LimitCharacters(16, '') == 'ProductAttribute' %>
                                {$FieldHolder}
                            <% end_if %>
                        <% end_loop %>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="input-group flex-nowrap w-auto">
                    <% with $Fields.dataFieldByName('productQuantity') %>
                        <input type="text" id="{$ID}-{$Up.Up.Product.ID}" class="form-control text-right pl-0 pr-1 border-right-0" value="{$Value}" name="{$Name}" {$getAttributesHTML('id', 'class', 'type', 'value', 'name', 'style')} style="min-width: 30px;max-width: {$Form.QuantityWidth}px;" />
                        <div class="input-group-prepend input-group-append ">
                            <span class="input-group-text bg-white border-left-0 px-0 spinner-field" data-target="#{$ID}-{$Up.Up.Product.ID}"></span>
                        </div>
                    <% end_with %>
                    <% loop $Actions %>
                        <div class="input-group-append">
                            <button title="<%t SilverCart\Model\Product\Product.ADD_TO_CART 'add Cart' %>" class="btn btn-primary btn-sm text-nowrap"><ion-icon name="cart-outline" class="icon icon--size-medium lazyload align-text-top mr-0"></ion-icon> {$Up.SubmitButtontitle}</button>
                        </div>
                    <% end_loop %>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>