
<% if SilvercartProduct %>
    <% control SilvercartProduct %>
<div class="silvercart-product-attribute-variant-list">
    <div class="silvercart-product-attribute-variant-list-title">
        <ul>
        <% control VariantAttributeValues %>
            <li><span><em>$SilvercartProductAttribute.Title:</em></span><br />$Title</li>
        <% end_control %>
        </ul>
    </div>
</div>
    <% end_control %>
<% end_if %>