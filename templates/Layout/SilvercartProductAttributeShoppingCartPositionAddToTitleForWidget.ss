
<% if SilvercartProduct %>
    <% control SilvercartProduct %>
        <% control VariantAttributeValues %>
            <span><em>$SilvercartProductAttribute.Title:</em></span> $Title<% if Last %><% else %>,<% end_if %>
        <% end_control %>
    <% end_control %>
<% end_if %>