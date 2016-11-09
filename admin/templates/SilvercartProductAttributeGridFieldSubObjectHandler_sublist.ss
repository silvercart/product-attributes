<% if $count > 0 %>
<div class="sub-list ui-state-default" data-parent-record-id="{$ParentRecordID}" id="sub-list-{$ParentRecordID}" data-target-url="{$TargetURL}">
    <% if $Items.first.SilvercartProductAttribute.CanBeUsedForSingleVariants %>
    <table>
        <thead>
            <tr>
                <td>Wert</td>
                <td>Anpassung Artikeltitel</td>
                <td>Anpassung Artikelpreis</td>
                <td>Anpassung Artikelnummer</td>
                <td>Aktionen</td>
            </tr>
        </thead>
        <tbody>
        <% loop $Items %>
            <tr class="sub-list-record active{$IsActive} default{$IsDefault}">
                <td class="title">{$Title}</td>
                <td>
                    <select name="subItem[variantModification][{$ID}][Title][action]">
                        <option value="">- ohne -</option>
                        <option value="setTo" <% if $ModifyTitleAction == 'setTo' %>selected<% end_if %> >Überschreiben</option>
                        <option value="add" <% if $ModifyTitleAction == 'add' %>selected<% end_if %> >Anhängen</option>
                    </select>
                    <input type="text" value="{$ModifyTitleValue}" name="subItem[variantModification][{$ID}][Title][value]" />
                </td>
                <td>
                    <select name="subItem[variantModification][{$ID}][Price][action]">
                        <option value="">- ohne -</option>
                        <option value="setTo" <% if $ModifyPriceAction == 'setTo' %>selected<% end_if %> >Überschreiben</option>
                        <option value="add" <% if $ModifyPriceAction == 'add' %>selected<% end_if %> >Aufschlag</option>
                        <option value="subtract" <% if $ModifyPriceAction == 'subtract' %>selected<% end_if %> >Abschlag</option>
                    </select>
                    <input type="text" value="{$ModifyPriceValue}" name="subItem[variantModification][{$ID}][Price][value]" style="width: 50px;" />
                </td>
                <td>
                    <select name="subItem[variantModification][{$ID}][ProductNumber][action]">
                        <option value="">- ohne -</option>
                        <option value="setTo" <% if $ModifyProductNumberAction == 'setTo' %>selected<% end_if %> >Überschreiben</option>
                        <option value="add" <% if $ModifyProductNumberAction == 'add' %>selected<% end_if %> >Anhängen</option>
                    </select>
                    <input type="text" value="{$ModifyProductNumberValue}" name="subItem[variantModification][{$ID}][ProductNumber][value]" />
                </td>
                <td>
                <% if $SubObjectHasIsActive %>
                    <% if $IsActive %>
                    <span class="sub-list-record-action btn-icon-minus-circle_disabled" data-action-id="{$Up.DeactivateActionID}" data-action-name="deactivate" data-record-id="{$ID}" data-icon="minus-circle_disabled" data-alter-icon="add" data-alter-action-id="{$Up.ActivateActionID}" data-alter-action-name="activate" data-alter-title="Aktivieren" title="Deaktivieren"></span>
                    <% else %>
                    <span class="sub-list-record-action btn-icon-add" data-action-id="{$Up.ActivateActionID}" data-action-name="activate" data-record-id="{$ID}" data-icon="add" data-alter-icon="minus-circle_disabled" data-alter-action-id="{$Up.DeactivateActionID}" data-alter-action-name="deactivate" data-alter-title="Deaktivieren" title="Aktivieren"></span>
                    <% end_if %>
                <% end_if %>
                <% if $SubObjectHasIsDefault %>
                    <% if $IsDefault %>
                    <span class="sub-list-record-action btn-icon-accept" data-action-id="{$Up.UndefaultActionID}" data-action-name="undefault" data-record-id="{$ID}" data-icon="accept" data-alter-icon="accept_disabled" data-alter-action-id="{$Up.DefaultActionID}" data-alter-action-name="default" data-alter-title="Als Standard festlegen" title="Nicht mehr als Standard festlegen"></span>
                    <% else %>
                    <span class="sub-list-record-action btn-icon-accept_disabled" data-action-id="{$Up.DefaultActionID}" data-action-name="default" data-record-id="{$ID}" data-icon="accept_disabled" data-alter-icon="accept" data-alter-action-id="{$Up.UndefaultActionID}" data-alter-action-name="undefault" data-alter-title="Nicht mehr als Standard festlegen" title="Als Standard festlegen"></span>
                    <% end_if %>
                <% end_if %>
                    <span class="sub-list-record-action btn-icon-chain--minus" data-action-id="{$Up.ActionID}" data-action-name="remove" data-record-id="{$ID}" title="Merkmal von diesem Artikel entfernen"></span>
                </td>
            </tr>
        <% end_loop %>
        </tbody>
    </table>
    <% else %>
        <% loop $Items %>
    <span class="sub-list-record active{$IsActive} default{$IsDefault}">
        <span class="sub-list-record-title">{$Title}</span> 
    <% if $SubObjectHasIsActive %>
        <% if $IsActive %>
        <span class="sub-list-record-action btn-icon-minus-circle_disabled" data-action-id="{$Up.DeactivateActionID}" data-action-name="deactivate" data-record-id="{$ID}" data-icon="minus-circle_disabled" data-alter-icon="add" data-alter-action-id="{$Up.ActivateActionID}" data-alter-action-name="activate" data-alter-title="Aktivieren" title="Deaktivieren"></span>
        <% else %>
        <span class="sub-list-record-action btn-icon-add" data-action-id="{$Up.ActivateActionID}" data-action-name="activate" data-record-id="{$ID}" data-icon="add" data-alter-icon="minus-circle_disabled" data-alter-action-id="{$Up.DeactivateActionID}" data-alter-action-name="deactivate" data-alter-title="Deaktivieren" title="Aktivieren"></span>
        <% end_if %>
    <% end_if %>
    <% if $SubObjectHasIsDefault %>
        <% if $IsDefault %>
        <span class="sub-list-record-action btn-icon-accept" data-action-id="{$Up.UndefaultActionID}" data-action-name="undefault" data-record-id="{$ID}" data-icon="accept" data-alter-icon="accept_disabled" data-alter-action-id="{$Up.DefaultActionID}" data-alter-action-name="default" title="Nicht mehr als Standard festlegen" data-alter-title="Als Standard festlegen"></span>
        <% else %>
        <span class="sub-list-record-action btn-icon-accept_disabled" data-action-id="{$Up.DefaultActionID}" data-action-name="default" data-record-id="{$ID}" data-icon="accept_disabled" data-alter-icon="accept" data-alter-action-id="{$Up.UndefaultActionID}" data-alter-action-name="undefault" title="Als Standard festlegen" data-alter-title="Nicht mehr als Standard festlegen"></span>
        <% end_if %>
    <% end_if %>
        <span class="sub-list-record-action btn-icon-chain--minus" data-action-id="{$Up.ActionID}" data-action-name="remove" data-record-id="{$ID}" title="Merkmal von diesem Artikel entfernen"></span>
    </span>
        <% end_loop %>
    <% end_if %>
</div>
<% end_if %>