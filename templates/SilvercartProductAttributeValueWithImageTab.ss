<% if $ProductAttributeValuesWithImage.exists %>
<div class="row">
    <% loop $ProductAttributeValuesWithImage %>
    <div class="col-lg-2 col-md-4 col-xs-12">
        <div class="card clickable-silvercart-attribute" data-attribute-id="{$SilvercartProductAttributeID}" data-attribute-value-id="{$ID}">
                <% if $Image %>
            <a href="javascript:;"><img class="card-img-top img-fluid" src="{$Image.Pad(116,116).URL}" alt="{$Title}"></a>
                <% end_if %>
            <div class="card-body text-center">
                <h5 class="card-title"><a href="javascript:;">{$Title}</a></h5>
                <a href="javascript:;" class="btn btn-secondary btn-xs"><%t Silvercart.CHOOSE 'choose' %></a>
            </div>
        </div>
    </div>
    <% end_loop %>
</div>
<% end_if %>
