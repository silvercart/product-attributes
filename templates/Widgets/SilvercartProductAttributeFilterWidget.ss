<% cached WidgetCacheKey %>
    <% if Attributes %>
        <% loop Attributes %>
            <% if First %>
            <% else %>
                </div>
            </div>
        </div>
    </div>
    <div class="widget {$Top.ExtraCssClasses}">
        <div class="widget_content">
            <div class="silvercart-widget">
                <div class="silvercart-widget_content">
            <% end_if %>
                    <strong class="h2 <% if HasSelectedValues %>has-selected-values<% else %>has-no-selected-values<% end_if %>">{$Title}</strong>
                    <div class="silvercart-widget-content_frame silvercart-product-attribute-filter-widget">
                        <ul class="vlist silvercart-product-attribute">
            <% if AssignedValues %>
                <% loop AssignedValues %>
                            <li>
                                <input type="checkbox" name="silvercart-product-attribute-value-{$ID}" id="silvercart-product-attribute-value-{$ID}" class="silvercart-product-attribute-value silvercart-product-attribute-{$SilvercartProductAttribute.ID}" value="$ID" <% if IsFilterValue %>checked="checked"<% end_if %> />
                                <label for="silvercart-product-attribute-value-{$ID}">$Title</label>
                            </li>
                <% end_loop %>
            <% end_if %>
                        </ul>
                        <a href="#" rel="$ID" class="remove-filter"><% sprintf(_t('SilvercartProductAttributeFilterWidget.DISABLE_FILTER_FOR'),$Title) %></a>
                    </div>
        <% end_loop %>

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
<% if CurrentPage.FilterValueArrayList %>
    <% loop CurrentPage.FilterValueArrayList %>
            SilvercartProductAttributeFilterPush($ID);
    <% end_loop %>
<% end_if %>
    });
</script>
    <% end_if %>
<% end_cached %>
