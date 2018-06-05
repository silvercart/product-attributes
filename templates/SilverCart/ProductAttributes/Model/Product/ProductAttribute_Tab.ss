
<% if $ProductAttributes && $ProductAttributeValues %>
<table class="table table-striped table-bordered">
    <colgroup>
        <col width="20%" />
        <col width="80%" />
    </colgroup>
    <tr>
        <th>{$fieldLabel(ProductAttribute)}</th>
        <th>{$fieldLabel(ProductAttributeValue)}</th>
    </tr>
    <% loop $AttributesWithValues %>
    <tr class="{$EvenOdd}">
        <td class="align_top padding_right">{$Attribute.Title}</td>
        <td class="align_top padding_right"><% loop $Values %><% if not $First %>, <% end_if %>{$Title}<% end_loop %></td>
    </tr>
    <% end_loop %>
</table>
<% end_if %>