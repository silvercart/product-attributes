<% cached WidgetCacheKey %>
    <% if Attributes %>
        <% control Attributes %>
                    <h2>$Title</h2>
                    <div class="silvercart-widget-content_frame">
                        <ul class="vlist silvercart-product-attribute">
            <% if AssignedValues %>
                <% control AssignedValues %>
                            <li>
                                <input type="checkbox" name="silvercart-product-attribute-value-{$ID}" id="silvercart-product-attribute-value-{$ID}" class="silvercart-product-attribute-value silvercart-product-attribute-{$SilvercartProductAttribute.ID}" value="$ID" <% if IsFilterValue %>checked="checked"<% end_if %> />
                                <label for="silvercart-product-attribute-value-{$ID}">$Title</label>
                            </li>
                <% end_control %>
            <% end_if %>
                        </ul>
                        <a href="#" rel="$ID" class="remove-filter"><% sprintf(_t('SilvercartProductAttributeFilterWidget.DISABLE_FILTER_FOR'),$Title) %></a>
                    </div>
            <% if Last %>
            <% else %>
                </div>
            </div>
        </div>
    </div>
    <div class="widget">
        <div class="widget_content">
            <div class="silvercart-widget">
                <div class="silvercart-widget_content">
            <% end_if %>
        <% end_control %>
    <% end_if %>

    <form name="silvercart-product-attribute-filter-form" method="post" action="$FormAction">
        <input type="hidden" name="silvercart-product-attribute-selected-values" value="$CurrentPage.FilterValueList" />
        <input type="hidden" name="silvercart-product-attribute-widget" value="$ID" />
        <input type="hidden" name="ajax" value="1" />
    </form>

<script type="text/javascript">
    $(document).ready(function() {
        if (jQuery(".silvercart-product-group-page-selectors")) {
            jQuery(".silvercart-product-group-page-selectors input[type=submit]").hide();
        }
<% if CurrentPage.FilterValueDataObjectSet %>
    <% control CurrentPage.FilterValueDataObjectSet %>
            SilvercartProductAttributeFilterPush($ID);
    <% end_control %>
<% end_if %>
    });
</script>
<% end_cached %>