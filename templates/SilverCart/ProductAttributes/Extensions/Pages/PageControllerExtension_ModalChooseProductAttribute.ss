<% if $ProductAttributeNavigationItem %>
    <% with $ProductAttributeNavigationItem %>
        <% if $Description %>
    <p>{$Description}</p>
        <% end_if %>
    <div class="d-flex flex-wrap justify-content-between align-content-start">
        <% loop $ProductAttributeValues %>
            <% if $IsGloballyChosen %>
        <div class="card mb-3 mr-3 cursor-pointer container-choose-product-attribute bg-success text-white">
            <div class="card-body">
                <h5 class="card-title">{$Title}</h5>
                <a href="{$ChooseLink}" class="btn btn-success btn-choose-product-attribute" data-alt-text="<%t SilverCart.Choose 'Choose' %>" data-item-id="{$ID}"><%t SilverCart.Chosen 'Chosen' %></a>
            </div>
        </div>
            <% else %>
        <div class="card mb-3 mr-3 cursor-pointer container-choose-product-attribute">
            <div class="card-body">
                <h5 class="card-title">{$Title}</h5>
                <a href="{$ChooseLink}" class="btn btn-outline-success btn-choose-product-attribute" data-alt-text="<%t SilverCart.Chosen 'Chosen' %>" data-item-id="{$ID}"><%t SilverCart.Choose 'Choose' %></a>
            </div>
        </div>
            <% end_if %>
        <% end_loop %>
    </div>
    <% end_with %>
<% end_if %>