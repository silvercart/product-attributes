<div class="silvercart-product-attribute-variant-table">
    <h2><% _t('SilvercartProductAttributeProduct.SLAVE_PRODUCTS') %></h2>
    <table class="data">
        <thead>
            <tr>
            <% control Headings %>
                <th class="{$Name}"><span>{$Title}</span></th>
            <% end_control %>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            <% control Items %>
            <tr class="{$EvenOdd} {$FirstLast}">
                <% control Fields %>
                <td class="{$FirstLast} {$Name}"><a href="{$Link}">{$Value}</a></td>
                <% end_control %>
                <td><a href="{$Link}"><% _t('SilvercartPage.DETAILS') %></a></td>
            </tr>
            <% end_control %>
        </tbody>
    </table>
</div>