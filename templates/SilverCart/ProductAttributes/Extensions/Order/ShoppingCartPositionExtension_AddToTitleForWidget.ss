<% if $VariantAttributes.exists || $UserInputAttributes.exists %>
    <% if $VariantAttributes.exists %>
        <% loop $VariantAttributes %>
<span><em>{$ProductAttribute.Title}:</em></span> {$Title}<% if not $Last %>,<% end_if %>
        <% end_loop %>
    <% end_if %>
    <% if $UserInputAttributes.exists %>
        <% if $VariantAttributes.exists %>,<% end_if %>
        <% loop $UserInputAttributes %>
<span><em>{$AttributeTitle}:</em></span> {$Title}<% if not $Last %>,<% end_if %>
        <% end_loop %>
    <% end_if %>
<% end_if %>