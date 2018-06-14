<% if $VariantAttributes.exists || $UserInputAttributes.exists %>
<div class="silvercart-product-attribute-variant-list">
    <div class="silvercart-product-attribute-variant-list-title">
        <ul>
    <% if $VariantAttributes.exists %>
        <% loop $VariantAttributes %>
            <li><span><em>{$ProductAttribute.Title}:</em></span> {$Title}</li>
        <% end_loop %>
    <% end_if %>
    <% if $UserInputAttributes.exists %>
        <% loop $UserInputAttributes %>
            <li><span><em>{$AttributeTitle}:</em></span> {$Title}</li>
        <% end_loop %>
    <% end_if %>
        </ul>
    </div>
</div>
<% end_if %>