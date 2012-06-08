<div id="{$FormName}_{$FieldName}_Box" class="type-range<% if errorMessage %> error<% end_if %><% if isRequiredField %> requiredField<% end_if %>">
    <% if errorMessage %>
        <div class="errorList">
            <% control errorMessage %>
            <strong class="message">
                {$message}
            </strong>
            <% end_control %>
        </div>
    <% end_if %>

    <label for="{$FieldID}">{$Label}</label>
    $FieldTag $Parent.CurrencySymbol
    
    &nbsp;
    
    <% control Parent %>
        $CustomHtmlFormFieldByName(MaxPrice, CustomHtmlFormFieldPlain) $CurrencySymbol
        
        <% control Actions %>
            $Field
        <% end_control %>
    <% end_control %>
</div>
