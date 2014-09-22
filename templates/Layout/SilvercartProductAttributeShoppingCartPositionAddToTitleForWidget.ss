
<% if SilvercartProduct %>
    <% with SilvercartProduct %>
        <% loop VariantAttributeValues %>
            <span><em>$SilvercartProductAttribute.Title:</em></span> $Title<% if Last %><% else %>,<% end_if %>
        <% end_loop %>
    <% end_with %>
<% end_if %>