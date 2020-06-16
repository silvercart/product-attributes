<% if $DataSheetAttributesWithValues %>
<table class="table table-striped table-bordered table-sm">
    <thead class="d-none">
        <tr>
            <th class="w-25">{$fieldLabel('ProductAttribute')}</th>
            <th class="w-75">{$fieldLabel('ProductAttributeValue')}</th>
        </tr>
    </thead>
    <tbody>
    <% loop $DataSheetAttributesWithValues %>
        <tr>
            <td class="w-25 text-top pr-3"><% if $Attribute.FontAwesomeIcon %>{$Attribute.FontAwesomeIconHTML}<% end_if %> {$Attribute.Title}</td>
            <td class="w-75 text-top pr-3"><% loop $Values %><% if not $First %>, <% end_if %>{$AdTitle}<% end_loop %></td>
        </tr>
    <% end_loop %>
    </tbody>
</table>
<% end_if %>