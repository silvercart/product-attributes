
<% if SilvercartProduct %>
    <% with SilvercartProduct %>
        <% if VariantAttributeValues %>
<div class="silvercart-product-attribute-variant-list">
    <div class="silvercart-product-attribute-variant-list-title">
        <ul>
        <% loop VariantAttributeValues %>
            <li><span><em>$SilvercartProductAttribute.Title:</em></span><br />$Title</li>
        <% end_loop %>
        </ul>
    </div>
</div>
        <% end_if %>
    <% end_with %>
<% end_if %>