<% if $IncludeFormTag %>
<form {$addExtraClass("form-inline price-range").AttributesHTML}>
<% end_if %>
<% include SilverCart/Forms/CustomFormMessages %>
<% loop $HiddenFields %>
    {$Field}
<% end_loop %>

    <div class="input-append">
      {$Fields.dataFieldByName(MinPrice).Field}
      <span class="add-on">{$CurrencySymbol}</span>
    </div>
    <span class="icon icon-minus"></span>
    <div class="input-append">
      {$Fields.dataFieldByName(MaxPrice).Field}
      <span class="add-on">{$CurrencySymbol}</span>
    </div>
    
    <% loop $Actions %>
        <button class="btn btn-primary pull-right float-right" type="submit" id="{$ID}" title="{$Title}">{$Title}</button> 
    <% end_loop %>
    <a href="{$CurrentPage.Link('ClearProductAttributePriceFilter')}" title="<%t SilverCart\ProductAttributes\Model\Widgets\PriceFilterWidget.DISABLE_FILTER 'Reset price filter' %>"><%t SilverCart\ProductAttributes\Model\Widgets\PriceFilterWidget.DISABLE_FILTER 'Reset price filter' %></a>

<% if $IncludeFormTag %>
</form>
<% end_if %>