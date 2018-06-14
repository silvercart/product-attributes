<% if $Product.hasVariants || $Product.hasSingleProductVariants %>
    <div class="clearfix silvercart-product-page-productvariant-box <% if $Product.hasVariants %>multiple<% end_if %>">
        <% loop $Fields %>
            <% if $Name.LimitCharacters(16, '') == 'ProductAttribute' %>
                {$FieldHolder}
            <% end_if %>
        <% end_loop %>
    </div>
<% end_if %>