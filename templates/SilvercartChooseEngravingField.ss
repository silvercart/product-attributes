<% if $hasOptions %>
<div {$AttributesHTML}>
    {$OptionsetField.Field}
    <div class="hidden-textfield">{$TextField.Field}</div>
</div>
<% else %>
    {$TextField.Field}
<% end_if %>