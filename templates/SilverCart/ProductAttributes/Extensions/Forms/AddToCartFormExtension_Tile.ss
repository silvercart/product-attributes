<% if $AttributedVariantAttributeSets %>
    <div class="clearfix silvercart-product-tile-productvariant-box">
        <a href="#" class="btn float-right pull-right silvercart-product-list-productvariant-popup-button" data-formname="{$Form.FormName}"><%t SilverCart\ProductAttributes\Extensions\Forms\AddToCartFormExtension.HEADLINE 'Variants' %></a>
        <div class="silvercart-product-tile-productvariant-popup clearfix">
            <% loop $Fields %>
                <% if $Name.LimitCharacters(16, '') == 'ProductAttribute' %>
                    {$FieldHolder}
                <% end_if %>
            <% end_loop %>
        </div>
    </div>
<% end_if %>