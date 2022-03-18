<% cached $WidgetCacheKey %>
    <% if $Attributes %>
        <% loop $Attributes %>
            <% if not $First %>
</div>
<div class="widget clearfix silvercart-widget {$Top.ExtraCssClasses}" id="widget-{$Top.ID}-{$Pos}">
            <% end_if %>
    <div class="silvercart-widget-content_frame silvercart-product-attribute-filter-widget">
        <strong class="h2 <% if $HasSelectedValues %>has-selected-values<% else %>has-no-selected-values<% end_if %>">{$Title}</strong>
            <% if not $DisableFilterReset && $HasSelectedValues %>
        <a href="#" data-id="{$ID}" class="remove-filter btn btn-sm btn-light mb-2"><span class="fa fa-chevron-left"></span> <%t SilverCart\ProductAttributes\Model\Widgets\ProductAttributeFilterWidget.Clear 'Clear' %></a>
            <% end_if %>
        <ul class="vlist unstyled silvercart-product-attribute">
            <% if $AssignedValues %>
                <% loop AssignedValues %>
            <li>
            <% if $AllowMultipleChoice %>
                <label for="silvercart-product-attribute-value-{$ID}"><input type="checkbox" name="silvercart-product-attribute-value-{$ID}" id="silvercart-product-attribute-value-{$ID}" class="silvercart-product-attribute-value silvercart-product-attribute-{$ProductAttribute.ID}" value="{$ID}" <% if $IsFilterValue %>checked="checked"<% end_if %> /> {$Title}</label>
            <% else %>
                <label for="silvercart-product-attribute-value-{$ID}"><input type="radio" name="silvercart-product-attribute-value-{$ProductAttribute.ID}" id="silvercart-product-attribute-value-{$ID}" class="silvercart-product-attribute-value silvercart-product-attribute-{$ProductAttribute.ID}" value="{$ID}" <% if $IsFilterValue %>checked="checked"<% end_if %> /> {$Title}</label>
            <% end_if %>
            </li>
                <% end_loop %>
            <% end_if %>
        </ul>
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
