<div id="SilvercartProductAttributeTableListField">
    <div class="SilvercartProductAttributeTableListField">
        <h3><% _t('SilvercartProductAttributeTableListField.UNASSIGNEDATTRIBUTES') %>:</h3>

        <div class="unAssignedAttributes">
            <% if unAssignedAttributes %>
                <select name="action_doSPAPAddUnAssignedAttribute">
                <% control unAssignedAttributes %>
                    <option value="$ID">$Title</option>
                <% end_control %>
                </select>
                <input type="submit" value="<% _t('SilvercartProductAttributeTableListField.ASSIGN_LABEL') %>" />
            <% else %>
                <% _t('SilvercartProductAttributeTableListField.NOATTRIBUTESUNATTRIBUTED') %>
            <% end_if %>
        </div>

        <h3><% _t('SilvercartProductAttributeTableListField.ASSIGNEDATTRIBUTES') %>:</h3>

        <% if AttributedAttributes %>
            <% control AttributedAttributes %>
                <div class="attributeTableSwitch" id="attributeTableSwitch{$ID}">
                    <% if CanBeUsedForVariants %>
                    <a href="#" rel="$ID" class="closed canbeusedforvariants"><strong>$Title</strong> <i>(<% _t('SilvercartProductAttribute.CAN_BE_USED_FOR_VARIANTS') %>)</i></a>
                    <% else %>
                    <a href="#" rel="$ID" class="closed"><strong>$Title</strong></a>
                    <% end_if %>
                    <div class="attributeTableSwitchActions">
                        <input type="submit" name="action_doSPAPRemoveAssignedAttribute" rel="$ID" value="<% _t('SilvercartProductAttributeTableListField.REMOVE_LABEL') %>" />
                    </div>
                </div>
                <div class="attributeTableContainer" id="attributeTableContainer{$ID}">
                    <table cellspacing="0" cellpadding="0" border="0" class="attributeTable">
                        <colgroup>
                            <col width="70%"></col>
                            <col width="30%"></col>
                        </colroup>
                        <thead>
                            <tr>
                                <th class="left">
                                    <% _t('SilvercartProductAttributeTableListField.VALUE_LABEL') %>
                                </th>
                                <th class="right">
                                    <% _t('SilvercartProductAttributeTableListField.ACTIONBAR_LABEL') %>
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                        <% if assignedValues %>
                            <% control assignedValues %>
                            <tr class="attributeRow">
                                <td class="left">$Title</td>
                                <td class="actionbar">
                                    <input type="submit" value="<% _t('SilvercartProductAttributeTableListField.ACTION_REMOVE_LABEL') %>" name="action_doSPAPRemoveAssignedValue" rel="$ID" />
                                </td>
                            </tr>
                            <% end_control %>
                        <% else %>
                            <tr class="attributeRow">
                                <td colspan="2">
                                    <% _t('SilvercartProductAttributeTableListField.NOVALUESATTRIBUTED') %>
                                </td>
                            </tr>
                        <% end_if %>
                        
                        <% if unAssignedValues %>
                            <tr class="attributeRow">
                                <td colspan="2" class="unAssignedValues">
                                    <select name="action_doSPAPAddUnAssignedValue">
                                    <% control unAssignedValues %>
                                        <option value="$ID">$Title</option>
                                    <% end_control %>
                                    </select>
                                    <input type="submit" value="<% _t('SilvercartProductAttributeTableListField.ASSIGN_LABEL') %>" />
                                </td>
                            </tr>
                        <% else %>
                            <tr class="attributeRow">
                                <td colspan="4">
                                    <% _t('SilvercartProductAttributeTableListField.NOVALUESUNATTRIBUTED') %>
                                </td>
                            </tr>
                        <% end_if %>
                        </tbody>
                    </table>
                </div>
            <% end_control %>
        <% else %>
            <% _t('SilvercartProductAttributeTableListField.NOATTRIBUTESATTRIBUTED') %>
        <% end_if %>
    </div>

    <script type="text/javascript">
        /* <![CDATA[ */
        (function($) {

            // ----------------------------------------------------------------
            // Unassigned atributes: add
            // ----------------------------------------------------------------
            $('.unAssignedAttributes input').live('click', function() {
                var form           = $('#right form');
                var selectedOption = $('.unAssignedAttributes select').find('option:selected');
                var formAction     = form.attr('action') + '/' + $('.unAssignedAttributes select').attr('name') + '/' + selectedOption.attr('value');

                // Post the data to save
                $.post(formAction, form.formToArray(), function(result){
                    $('#SilvercartProductAttributeTableListField').replaceWith(result);
                }, 'html');

                return false;
            });

            // ----------------------------------------------------------------
            // Unassigned atribute values: add
            // ----------------------------------------------------------------
            $('.unAssignedValues input').live('click', function() {
                var select          = $('select', $(this).parent());
                var selectedOption  = select.find('option:selected');
                var form            = $('#right form');
                var formAction      = form.attr('action') + '/' + $('.unAssignedValues select').attr('name') + '/' + selectedOption.attr('value');

                // Post the data to save
                $.post(formAction, form.formToArray(), function(result){
                    $('#SilvercartProductAttributeTableListField').replaceWith(result);
                }, 'html');

                return false;
            });

            // ----------------------------------------------------------------
            // Assigned atribute sets: remove
            // ----------------------------------------------------------------
            $('.attributeTableSwitchActions input').live('click', function() {
                var form       = $('#right form');
                var formAction = form.attr('action') + '/' + $(this).attr('name') + '/' + $(this).attr('rel');

                // Post the data to save
                $.post(formAction, form.formToArray(), function(result){
                    $('#SilvercartProductAttributeTableListField').replaceWith(result);
                }, 'html');

                return false;
            });

            // ----------------------------------------------------------------
            // Attribute table containers: toggle function
            // ----------------------------------------------------------------
            $('.attributeTableSwitch a').live('click', function() {
                var containerId = 'attributeTableContainer' + $(this).attr('rel');

                $('.attributeTableContainer').each(
                    function(index) {
                        var attributeTableSwitch = $('#attributeTableSwitch' + $(this).attr('ID').substr(21) + ' a');
                        attributeTableSwitch.removeClass('opened');
                        attributeTableSwitch.addClass('closed');

                        if ($(this).attr('ID') != containerId) {
                            $(this).hide();
                        }
                    }
                );

                var attributeTable    = $('#' + containerId);
                var attributeTableSwitch = $('#attributeTableSwitch' + $(this).attr('rel') + ' a');

                if (attributeTable.css('display') == 'none') {
                    attributeTableSwitch.removeClass('closed');
                    attributeTableSwitch.addClass('opened');
                    attributeTable.slideDown();
                } else {
                    attributeTableSwitch.removeClass('opened');
                    attributeTableSwitch.addClass('closed');
                    attributeTable.slideUp();
                }

                return false;
            });

            // ------------------------------------------------------------
            // Actionbar buttons
            // ------------------------------------------------------------
            $('.attributeTable tbody .actionbar input[type="submit"]').live('click', function() {
                var form       = $('#right form');
                var formAction = form.attr('action') + '/' + $(this).attr('name') + '/' + $(this).attr('rel');

                // Post the data to save
                $.post(formAction, form.formToArray(), function(result){

                    $('#SilvercartProductAttributeTableListField').replaceWith(result);
                }, 'html');

                return false;
            });

            <% if setActiveAttributeID %>
            $('#attributeTableContainer{$activeAttributeID}').show();
            $('#attributeTableSwitch{$activeAttributeID} a').removeClass('closed');
            $('#attributeTableSwitch{$activeAttributeID} a').addClass('opened');
            <% end_if %>
        })(jQuery);
        /* ]]> */
    </script>
</div>
