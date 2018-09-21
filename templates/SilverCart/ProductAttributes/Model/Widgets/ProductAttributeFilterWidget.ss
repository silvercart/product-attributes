<% cached $WidgetCacheKey %>
    <% if $Attributes %>
        <% loop $Attributes %>
            <% if not $First %>
</div>
<div class="widget clearfix silvercart-widget {$Top.ExtraCssClasses}" id="widget-{$Top.ID}-{$Pos}">
            <% end_if %>
    <div class="silvercart-widget-content_frame silvercart-product-attribute-filter-widget">
        <strong class="h2 <% if $HasSelectedValues %>has-selected-values<% else %>has-no-selected-values<% end_if %>">{$Title}</strong>
        <ul class="vlist unstyled silvercart-product-attribute">
            <% if $AssignedValues %>
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

<form name="silvercart-product-attribute-filter-form" method="post" action="{$FormAction}"
      data-main-selector="{$getJsMainSelector}"
      data-filter-list="<% if $CurrentPage.FilterValueArrayList %><% loop $CurrentPage.FilterValueArrayList %>{$ID}<% if not $Last %>,<% end_if %><% end_loop %><% end_if %>">
    <input type="hidden" name="silvercart-product-attribute-selected-values" value="{$CurrentPage.FilterValueList}" />
    <input type="hidden" name="silvercart-product-attribute-widget" value="{$ID}" />
    <input type="hidden" name="ajax" value="1" />
</form>
    <% end_if %>
<% end_cached %>
