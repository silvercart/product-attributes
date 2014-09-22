<div class="silvercart-product-attribute-variant-table">
    <h2><% _t('SilvercartProductAttributeProduct.SLAVE_PRODUCTS') %></h2>
    <table class="data">
        <thead>
            <tr>
            <% loop Headings %>
                <th class="{$Name}"><span>{$Title}</span></th>
            <% end_loop %>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            <% loop Items %>
            <tr class="{$EvenOdd} {$FirstLast}">
                <% loop Fields %>
                <td class="{$FirstLast} {$Name}"><a href="{$Link}">{$Value}</a></td>
                <% end_loop %>
                <td><a href="{$Link}"><% _t('SilvercartPage.DETAILS') %></a></td>
            </tr>
            <% end_loop %>
        </tbody>
    </table>
</div>