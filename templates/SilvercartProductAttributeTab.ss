
<% if SilvercartProductAttributes %>
<% if SilvercartProductAttributeValues %>
<table class="full silvercart-default-table">
    <colgroup>
        <col width="20%" />
        <col width="80%" />
    </colgroup>
    <tr>
        <th>{$fieldLabel(SilvercartProductAttribute)}</th>
        <th>{$fieldLabel(SilvercartProductAttributeValue)}</th>
    </tr>
    <% loop AttributesWithValues %>
    <tr class="$EvenOdd">
        <td class="align_top padding_right">{$Attribute.Title}</td>
        <td class="align_top padding_right">
            <% loop Values %>
                <% if First %><% else %>, <% end_if %>{$Title}
            <% end_loop %>
        </td>
    </tr>
    <% end_loop %>
</table>
<% end_if %>
<% end_if %>