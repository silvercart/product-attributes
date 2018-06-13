<% if $ProductAttributeValuesWithImage.exists %>
<div class="row-fluid">
<ul class="thumbnails">
    <% loop $ProductAttributeValuesWithImage %>
    <li class="span2">
        <div class="thumbnail text-center clickable-silvercart-attribute" data-attribute-id="{$ProductAttributeID}" data-attribute-value-id="{$ID}">
        <% if $Image %>
            <a href="javascript:;"><img class="img-fluid" src="{$Image.Pad(116,116).URL}" alt="{$Title}"></a>
        <% end_if %>
            <h3 class="card-title"><a href="javascript:;">{$Title}</a></h3>
            <a href="javascript:;" class="btn btn-secondary btn-xs"><%t SilverCart\Forms\ChangeLanguageForm.CHOOSE 'choose' %></a>
        </div>
    </li>
    <% end_loop %>
</ul>
</div>
<% end_if %>