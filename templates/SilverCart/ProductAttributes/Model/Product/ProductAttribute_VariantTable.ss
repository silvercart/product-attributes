<div class="silvercart-product-attribute-variant-table">
    <h2><%t SilverCart\ProductAttributes\Extensions\Product\ProductExtension.SLAVE_PRODUCTS 'This product has the following variants:' %></h2>
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
            <% loop $Headings %>
                <th class="{$Name}"><span>{$Title}</span></th>
            <% end_loop %>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            <% loop $Items %>
            <tr class="{$EvenOdd} {$FirstLast}">
                <% loop $ItemFields %>
                <td class="{$FirstLast} {$Name}"><a href="{$Link}">{$Value}</a></td>
                <% end_loop %>
                <td><a href="{$Link}"><%t SilverCart\Model\Pages\Page.DETAILS 'Details' %></a></td>
            </tr>
            <% end_loop %>
        </tbody>
    </table>
</div>