<% if $hasSingleProductVariants %>
    <div class="clearfix silvercart-product-page-productvariant-box">
        <h3><% _t('SilvercartProductAttributeAddCartForm.HEADLINE') %>:</h3>

        <% with Form %>
            <% loop CustomHtmlFormFieldsByGroup(SilvercartProductAttributesSingle,CustomHtmlFormFieldSelect) %>
                $CustomHtmlFormField
            <% end_loop %>
        <% end_with %>
    </div>
<% end_if %>
<hr>
<hr>
<hr>
<hr>
<hr>

<% if SilvercartAttributedVariantAttributeSets %>
    <div class="clearfix silvercart-product-list-productvariant-box">
        <div class="silvercart-product-list-productvariant-row">
            <div class="silvercart-button right silvercart-product-list-productvariant-popup-button">
                <div class="silvercart-button_content">
                    <a href="#" rel="$Form.FormName"><% _t('SilvercartProductVariantAddCartForm.HEADLINE') %></a>
                </div>
            </div>
        </div>
        
        <div class="clearfix"></div>
        <div class="silvercart-product-list-productvariant-popup">
            <div class="silvercart-product-list-productvariant-popup_content">
                <% control Form %>
                    <% control CustomHtmlFormFieldsByGroup(SilvercartProductVariantAttributeSets,CustomHtmlFormFieldSelect) %>
                        $CustomHtmlFormField
                    <% end_control %>
                    <% control CustomHtmlFormFieldsByGroup(SilvercartProductVariantUserInputAttributeSets,CustomHtmlFormFieldSelectionGroup) %>
                        $CustomHtmlFormField
                    <% end_control %>
                <% end_control %>
            </div>
        </div>
    </div>
<% end_if %>