<form class="yform silvercart-widget-form full" $FormAttributes>
    $CustomHtmlFormMetadata
    
    <% if HasCustomHtmlFormErrorMessages %>
        <div class="silvercart-error-list">
            <div class="silvercart-error-list_content">
                $CustomHtmlFormErrorMessages
            </div>
        </div>
    <% end_if %>

    $CustomHtmlFormFieldByName(MinPrice, SilvercartProductAttributePriceRangeFormField)
    
    <a href="{$CurrentPage.Link}ClearSilvercartProductAttributePriceFilter" title="<% _t('SilvercartProductAttributePriceFilterWidget.DISABLE_FILTER') %>"><% _t('SilvercartProductAttributePriceFilterWidget.DISABLE_FILTER') %></a>

</form>
