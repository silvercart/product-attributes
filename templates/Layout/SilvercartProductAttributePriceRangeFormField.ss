<div id="{$FormName}_{$FieldName}_Box" class="type-range<% if errorMessage %> error<% end_if %><% if isRequiredField %> requiredField<% end_if %>">
    <% if errorMessage %>
        <div class="errorList">
            <% with errorMessage %>
            <strong class="message">
                {$message}
            </strong>
            <% end_with %>
        </div>
    <% end_if %>

    <label for="{$FieldID}">{$Label}</label>
    $FieldTag $Parent.CurrencySymbol
    
    &nbsp;
    
    <% with Parent %>
        $CustomHtmlFormFieldByName(MaxPrice, CustomHtmlFormFieldPlain) $CurrencySymbol
        
        <% loop Actions %>
            $Field
        <% end_loop %>
    <% end_with %>
</div>
