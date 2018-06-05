<% if $IncludeFormTag %>
<form {$AttributesHTML}>
<% end_if %>
<% include SilverCart/Forms/CustomFormMessages %>
<% loop $HiddenFields %>
    {$Field}
<% end_loop %>

    <% with $Fields.dataFieldByName(MinPrice) %>
    <div id="{$HolderID}" class="control-group type-range <% if $extraClass %>{$extraClass}<% end_if %>">
        <label class="control-label" for="{$ID}">{$Title}</label>
        <div class="controls">
        {$Field} {$Up.CurrencySymbol}
    <% end_with %>
    {$Fields.dataFieldByName(MaxPrice).Field} {$CurrencySymbol}
        
    <% loop $Actions %>
        <button class="btn btn-primary" type="submit" id="{$ID}" title="{$Title}">{$Title}</button> 
    <% end_loop %> 
        </div>
    </div>
    
    <a href="{$CurrentPage.Link}ClearProductAttributePriceFilter" title="<%t SilverCart\ProductAttributes\Model\Widgets\PriceFilterWidget.DISABLE_FILTER 'Reset price filter' %>"><%t SilverCart\ProductAttributes\Model\Widgets\PriceFilterWidget.DISABLE_FILTER 'Reset price filter' %></a>

<% if $IncludeFormTag %>
</form>
<% end_if %>
