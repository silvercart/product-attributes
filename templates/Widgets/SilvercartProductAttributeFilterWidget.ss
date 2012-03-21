<% if Attributes %>
    <% control Attributes %>
                <h2>$Title</h2>
                <div class="silvercart-widget-content_frame">
                    <ul class="vlist silvercart-product-attribute">
        <% if AssignedValues %>
            <% control AssignedValues %>
                        <li>
                            <input type="checkbox" name="silvercart-product-attribute-value-{$ID}" id="silvercart-product-attribute-value-{$ID}" class="silvercart-product-attribute-value silvercart-product-attribute-{$SilvercartProductAttribute.ID}" value="$ID" <% if IsFilterValue %>checked="checked"<% end_if %> />
                            <label for="silvercart-product-attribute-value-{$ID}">$Title</label>
                        </li>
            <% end_control %>
        <% end_if %>
                    </ul>
                    <a href="#" rel="$ID" class="remove-filter"><% sprintf(_t('SilvercartProductAttributeFilterWidget.DISABLE_FILTER_FOR'),$Title) %></a>
                </div>
        <% if Last %>
        <% else %>
            </div>
        </div>
    </div>
</div>
<div class="widget">
    <div class="widget_content">
        <div class="silvercart-widget">
            <div class="silvercart-widget_content">
        <% end_if %>
    <% end_control %>
<% end_if %>

<form name="silvercart-product-attribute-filter-form" method="post" action="$FormAction">
    <input type="hidden" name="silvercart-product-attribute-selected-values" value="$CurrentPage.FilterValueList" />
    <input type="hidden" name="silvercart-product-attribute-widget" value="$ID" />
    <input type="hidden" name="ajax" value="1" />
</form>

<script type="text/javascript">
    $(document).ready(function() {
        var SilvercartProductAttributeFilter                    = [];
        var SilvercartProductAttributeFilterCallInProgress      = false;
        var SilvercartProductAttributeFilterCallback            = false;
        var SilvercartProductAttributeFilterCallbackTimeout    = false;
        
        var SilvercartProductAttributeFilterCall                = function() {
            var filterForm      = $('form[name="silvercart-product-attribute-filter-form"]');
            var filterInput     = $('input[name="silvercart-product-attribute-selected-values"]');
            var filterWidget    = $('input[name="silvercart-product-attribute-widget"]');
            if (SilvercartProductAttributeFilterCallInProgress == false) {
                SilvercartProductAttributeFilterCallInProgress = true;
                SilvercartProductAttributeFilterCallback = false;
                $.ajax({
                    'async':      false,
                    'type':       'POST',
                    'url':        filterForm.attr('action'),
                    'data':       {
                        'silvercart-product-attribute-selected-values'  : filterInput.val(),
                        'silvercart-product-attribute-widget'           : filterWidget.val(),
                        'ajax'                                          : 1
                    },
                    'success':    function(html) {
                        $('#main').html(html);
                        SilvercartProductAttributeFilterCallInProgress = false;
                        if (SilvercartProductAttributeFilterCallback) {
                            SilvercartProductAttributeFilterCall();
                        }
                    },
                    'failure':    function() {
                        alert("Fehler");
                    }
                });
            } else {
                SilvercartProductAttributeFilterCallback = true;
            }
        }
        
        $('.silvercart-product-attribute-value').change(function() {
            var checkbox    = $(this);
            var checked     = checkbox.is(':checked');
            var id          = checkbox.val();
            var filterForm  = $('form[name="silvercart-product-attribute-filter-form"]');
            var filterInput = $('input[name="silvercart-product-attribute-selected-values"]');
            if (checked) {
                SilvercartProductAttributeFilter.push(id);
            } else {
                SilvercartProductAttributeFilter = jQuery.grep(SilvercartProductAttributeFilter, function(value) {
                    return value != id;
                });
            }
            filterInput.val(SilvercartProductAttributeFilter.join(','));
            window.clearTimeout(SilvercartProductAttributeFilterCallbackTimeout);
            SilvercartProductAttributeFilterCallbackTimeout = window.setTimeout(SilvercartProductAttributeFilterCall, 800);
        });
        
        $('.remove-filter').click(function() {
            var id = $(this).attr('rel');
            $('.silvercart-product-attribute-' + id).each(function() {
                $(this).removeAttr('checked');
            });
            SilvercartProductAttributeFilterCall();
        });
        
        
<% if CurrentPage.FilterValueDataObjectSet %>
    <% control CurrentPage.FilterValueDataObjectSet %>
            SilvercartProductAttributeFilter.push($ID);
    <% end_control %>
<% end_if %>
    });
</script>