<% if SilvercartAttributedVariantAttributeSets %>
    <div class="clearfix silvercart-product-tile-productvariant-box">
        <div class="silvercart-product-tile-productvariant-row">
            <div class="silvercart-button right silvercart-product-tile-productvariant-popup-button">
                <div class="silvercart-button_content">
                    <a href="#" rel="$Form.FormName"><% _t('SilvercartProductVariantAddCartForm.HEADLINE') %></a>
                </div>
            </div>
        </div>

        <div class="clearfix"></div>
        <div class="silvercart-product-tile-productvariant-popup">
            <div class="silvercart-product-tile-productvariant-popup_content">
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
<% else %>
    <div class="silvercart-product-tile-productvariant-row"></div>
    <div class="clearfix"></div>
<% end_if %>