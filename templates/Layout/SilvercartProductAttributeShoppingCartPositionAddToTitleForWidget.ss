<% if $VariantAttributes %>
    <% loop $VariantAttributes %>
<span><em>{$SilvercartProductAttribute.Title}:</em></span> {$Title}<% if not Last %>,<% end_if %>
    <% end_loop %>
<% end_if %>