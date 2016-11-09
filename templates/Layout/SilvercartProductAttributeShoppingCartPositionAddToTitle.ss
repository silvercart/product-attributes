<% if $VariantAttributes %>
<div class="silvercart-product-attribute-variant-list">
    <div class="silvercart-product-attribute-variant-list-title">
        <ul>
    <% loop $VariantAttributes %>
            <li><span><em>{$SilvercartProductAttribute.Title}:</em></span> {$Title}</li>
    <% end_loop %>
        </ul>
    </div>
</div>
<% end_if %>