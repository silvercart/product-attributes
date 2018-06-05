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
                    <div class="silvercart-widget-content_frame silvercart-product-attribute-filter-widget">
                        <strong class="h2 <% if HasSelectedValues %>has-selected-values<% else %>has-no-selected-values<% end_if %>">{$Title}</strong>
                        <ul class="vlist silvercart-product-attribute">
            <% if AssignedValues %>
                <% loop AssignedValues %>
                            <li>
                                <label for="silvercart-product-attribute-value-{$ID}"><input type="checkbox" name="silvercart-product-attribute-value-{$ID}" id="silvercart-product-attribute-value-{$ID}" class="silvercart-product-attribute-value silvercart-product-attribute-{$ProductAttribute.ID}" value="{$ID}" <% if $IsFilterValue %>checked="checked"<% end_if %> /> {$Title}</label>
                            </li>
                <% end_loop %>
            <% end_if %>
                        </ul>
                        <a href="#" data-id="{$ID}" class="remove-filter"><%t SilverCart\ProductAttributes\Model\Widgets\ProductAttributeFilterWidget.DISABLE_FILTER_FOR 'Reset all filters for &quot;{title}&quot;' title=$Title %></a>
                    </div>
        <% end_loop %>

    <form name="silvercart-product-attribute-filter-form" method="post" action="$FormAction">
        <input type="hidden" name="silvercart-product-attribute-selected-values" value="$CurrentPage.FilterValueList" />
        <input type="hidden" name="silvercart-product-attribute-widget" value="$ID" />
        <input type="hidden" name="ajax" value="1" />
    </form>

<script type="text/javascript">
    silvercart.attributes.filter.setMainSelector('{$getJsMainSelector}');
<% if CurrentPage.FilterValueArrayList %>
    <% loop CurrentPage.FilterValueArrayList %>
    silvercart.attributes.filter.Push($ID);
    <% end_loop %>
<% end_if %>
    $(document).ready(function() {
        if (jQuery(".silvercart-product-group-page-selectors")) {
            jQuery(".silvercart-product-group-page-selectors input[type=submit]").hide();
        }
    });
</script>
    <% end_if %>
<% end_cached %>
