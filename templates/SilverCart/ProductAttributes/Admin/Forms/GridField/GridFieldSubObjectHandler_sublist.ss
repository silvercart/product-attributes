<% if $count > 0 %>
<div class="sub-list ui-state-default" data-parent-record-id="{$ParentRecordID}" id="sub-list-{$ParentRecordID}" data-target-url="{$TargetURL}" data-field-name="{$FieldName}">
    <% if $Items.first.ProductAttribute.CanBeUsedForSingleVariants %>
    <table>
        <thead>
            <tr>
                <td><%t SilverCart\ProductAttributes\Model\Product\ProductAttributeValue.DefaultModifyValue 'Value' %></td>
                <td><%t SilverCart\ProductAttributes\Model\Product\ProductAttributeValue.ModifyTitle 'Modify product title' %></td>
                <td><%t SilverCart\ProductAttributes\Model\Product\ProductAttributeValue.ModifyPrice 'Modify product price' %></td>
                <td><%t SilverCart\ProductAttributes\Model\Product\ProductAttributeValue.ModifyProductNumber 'Modify product number' %></td>
                <td><%t SilverCart\ProductAttributes\Model\Product\ProductAttributeValue.DefaultModifyAction 'Action' %></td>
            </tr>
        </thead>
        <tbody>
        <% loop $Items %>
            <tr class="sub-list-record active{$IsActive} default{$IsDefault}">
                <td class="title">{$Title}</td>
                <td>
                    <select name="subItem[variantModification][{$ID}][Title][action]">
                        <option value=""><%t SilverCart\ProductAttributes\Model\Product\ProductAttributeValue.DefaultModifyActionNone '- none -' %></option>
                        <option value="setTo" <% if $ModifyTitleAction == 'setTo' %>selected<% end_if %> ><%t SilverCart\ProductAttributes\Model\Product\ProductAttributeValue.DefaultModifyActionSetTo 'Set to' %></option>
                        <option value="add" <% if $ModifyTitleAction == 'add' %>selected<% end_if %> ><%t SilverCart\ProductAttributes\Model\Product\ProductAttributeValue.DefaultModifyActionAdd 'Add' %></option>
                    </select>
                    <input type="text" value="{$ModifyTitleValue}" name="subItem[variantModification][{$ID}][Title][value]" />
                    <% if $DefaultModifyTitle %>{$DefaultModifyTitleText}<% end_if %>
                </td>
                <td>
                    <select name="subItem[variantModification][{$ID}][Price][action]">
                        <option value=""><%t SilverCart\ProductAttributes\Model\Product\ProductAttributeValue.DefaultModifyActionNone '- none -' %></option>
                        <option value="setTo" <% if $ModifyPriceAction == 'setTo' %>selected<% end_if %> ><%t SilverCart\ProductAttributes\Model\Product\ProductAttributeValue.DefaultModifyActionSetTo 'Set to' %></option>
                        <option value="add" <% if $ModifyPriceAction == 'add' %>selected<% end_if %> ><%t SilverCart\ProductAttributes\Model\Product\ProductAttributeValue.DefaultModifyActionAdd 'Add' %></option>
                        <option value="subtract" <% if $ModifyPriceAction == 'subtract' %>selected<% end_if %> ><%t SilverCart\ProductAttributes\Model\Product\ProductAttributeValue.DefaultModifyActionSubtract 'Subtract' %></option>
                    </select>
                    <input type="text" value="{$ModifyPriceValue}" name="subItem[variantModification][{$ID}][Price][value]" style="width: 50px;" />
                    <% if $DefaultModifyPrice %>{$DefaultModifyPriceText}<% end_if %>
                </td>
                <td>
                    <select name="subItem[variantModification][{$ID}][ProductNumber][action]">
                        <option value=""><%t SilverCart\ProductAttributes\Model\Product\ProductAttributeValue.DefaultModifyActionNone '- none -' %></option>
                        <option value="setTo" <% if $ModifyProductNumberAction == 'setTo' %>selected<% end_if %> ><%t SilverCart\ProductAttributes\Model\Product\ProductAttributeValue.DefaultModifyActionSetTo 'Set to' %></option>
                        <option value="add" <% if $ModifyProductNumberAction == 'add' %>selected<% end_if %> ><%t SilverCart\ProductAttributes\Model\Product\ProductAttributeValue.DefaultModifyActionAdd 'Add' %></option>
                    </select>
                    <input type="text" value="{$ModifyProductNumberValue}" name="subItem[variantModification][{$ID}][ProductNumber][value]" />
                    <% if $DefaultModifyProductNumber %>{$DefaultModifyProductNumberText}<% end_if %>
                </td>
                <td>
                <% if $SubObjectHasIsActive %>
                    <% if $IsActive %>
                    <span class="sub-list-record-action icon font-icon-cancel" data-action-id="{$Up.DeactivateActionID}" data-action-name="deactivate" data-record-id="{$ID}" data-icon="cancel" data-alter-icon="plus" data-alter-action-id="{$Up.ActivateActionID}" data-alter-action-name="activate" data-alter-title="Aktivieren" title="Deaktivieren"></span>
                    <% else %>
                    <span class="sub-list-record-action icon font-icon-plus" data-action-id="{$Up.ActivateActionID}" data-action-name="activate" data-record-id="{$ID}" data-icon="plus" data-alter-icon="cancel" data-alter-action-id="{$Up.DeactivateActionID}" data-alter-action-name="deactivate" data-alter-title="Deaktivieren" title="Aktivieren"></span>
                    <% end_if %>
                <% end_if %>
                <% if $SubObjectHasIsDefault %>
                    <% if $IsDefault %>
                    <span class="sub-list-record-action icon font-icon-block" data-action-id="{$Up.UndefaultActionID}" data-action-name="undefault" data-record-id="{$ID}" data-icon="block" data-alter-icon="check-mark" data-alter-action-id="{$Up.DefaultActionID}" data-alter-action-name="default" title="Nicht mehr als Standard festlegen" data-alter-title="Als Standard festlegen"></span>
                    <% else %>
                    <span class="sub-list-record-action icon font-icon-check-mark" data-action-id="{$Up.DefaultActionID}" data-action-name="default" data-record-id="{$ID}" data-icon="check-mark" data-alter-icon="block" data-alter-action-id="{$Up.UndefaultActionID}" data-alter-action-name="undefault" title="Als Standard festlegen" data-alter-title="Nicht mehr als Standard festlegen"></span>
                    <% end_if %>
                <% end_if %>
                    <span class="sub-list-record-action icon font-icon-trash-bin" data-action-id="{$Up.ActionID}" data-action-name="remove" data-record-id="{$ID}" title="Merkmal von diesem Artikel entfernen"></span>
                </td>
            </tr>
        <% end_loop %>
        </tbody>
    </table>
    <% else %>
        <% loop $Items %>
    <span class="sub-list-record active{$IsActive} default{$IsDefault}">
        <span class="sub-list-record-title title">{$Title}</span>
    <% if $SubObjectHasIsActive %>
        <% if $IsActive %>
        <span class="sub-list-record-action icon font-icon-cancel" data-action-id="{$Up.DeactivateActionID}" data-action-name="deactivate" data-record-id="{$ID}" data-icon="cancel" data-alter-icon="plus" data-alter-action-id="{$Up.ActivateActionID}" data-alter-action-name="activate" data-alter-title="Aktivieren" title="Deaktivieren"></span>
        <% else %>
        <span class="sub-list-record-action icon font-icon-plus" data-action-id="{$Up.ActivateActionID}" data-action-name="activate" data-record-id="{$ID}" data-icon="plus" data-alter-icon="cancel" data-alter-action-id="{$Up.DeactivateActionID}" data-alter-action-name="deactivate" data-alter-title="Deaktivieren" title="Aktivieren"></span>
        <% end_if %>
    <% end_if %>
    <% if $SubObjectHasIsDefault %>
        <% if $IsDefault %>
        <span class="sub-list-record-action icon font-icon-block" data-action-id="{$Up.UndefaultActionID}" data-action-name="undefault" data-record-id="{$ID}" data-icon="block" data-alter-icon="check-mark" data-alter-action-id="{$Up.DefaultActionID}" data-alter-action-name="default" title="Nicht mehr als Standard festlegen" data-alter-title="Als Standard festlegen"></span>
        <% else %>
        <span class="sub-list-record-action icon font-icon-check-mark" data-action-id="{$Up.DefaultActionID}" data-action-name="default" data-record-id="{$ID}" data-icon="check-mark" data-alter-icon="block" data-alter-action-id="{$Up.UndefaultActionID}" data-alter-action-name="undefault" title="Als Standard festlegen" data-alter-title="Nicht mehr als Standard festlegen"></span>
        <% end_if %>
    <% end_if %>
        <span class="sub-list-record-action icon font-icon-trash-bin" data-action-id="{$Up.ActionID}" data-action-name="remove" data-record-id="{$ID}" title="Merkmal von diesem Artikel entfernen"></span>
    </span>
        <% end_loop %>
    <% end_if %>
</div>
<% end_if %>