<% if $ProductAttributeNavigationItem %>
    <% with $ProductAttributeNavigationItem %>
        <% if $Description %>
    <p>{$Description}</p>
        <% end_if %>
    <div class="row no-gutters-">
        <% loop $ProductAttributeValues %>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3 mt-20">
            <% if $DisableGlobally %>
            <div class="h-100 mr-3px border p-relative">
            <% if $Image %>
                <img src="{$Image.Fill(333,185).URL}" class="img-fluid" alt="<% if $Image.Title %>{$Image.Title.ATT}<% else %>{$Title.ATT}<% end_if %>">
            <% else_if $FontAwesomeIcon %>
                {$FontAwesomeIconHTML('fa-3x icon w-auto')}
            <% end_if %>
                <h3 class="text-center text-hyphens-auto text-muted">{$Title}</h3>
            </div>
            <% else_if $IsGloballyChosen %>
            <div class="container-choose-product-attribute h-100 mr-3px border p-relative hover-shadow" style="transition: all 0.5s;">
                <span class="container-chosen p-absolute l-5 t-5 text-success bg-white border border-success rounded-circle p-5px"><span class="fas fa-check fa-2x"></span></span>
            <% if $Image %>
                <img src="{$Image.Fill(333,185).URL}" class="img-fluid" alt="<% if $Image.Title %>{$Image.Title.ATT}<% else %>{$Title.ATT}<% end_if %>">
            <% else_if $FontAwesomeIcon %>
                {$FontAwesomeIconHTML('fa-3x icon w-auto')}
            <% end_if %>
                <h3 class="text-center text-hyphens-auto">{$Title}</h3>
                <a href="{$ChooseLink}" class="stretched-link btn-choose-product-attribute chosen" data-alt-text="<%t SilverCart.Chosen 'Chosen' %>" data-item-id="{$ID}"></a>
            </div>
            <% else %>
            <div class="container-choose-product-attribute h-100 mr-3px border p-relative hover-shadow" style="transition: all 0.5s;">
                <span class="container-chosen p-absolute l-5 t-5 text-success bg-white border border-success rounded-circle p-5px d-none"><span class="fas fa-check fa-2x"></span></span>
            <% if $Image %>
                <img src="{$Image.Fill(333,185).URL}" class="img-fluid" alt="<% if $Image.Title %>{$Image.Title.ATT}<% else %>{$Title.ATT}<% end_if %>">
            <% else_if $FontAwesomeIcon %>
                {$FontAwesomeIconHTML('fa-3x icon w-auto')}
            <% end_if %>
                <h3 class="text-center text-hyphens-auto">{$Title}</h3>
                <a href="{$ChooseLink}" class="stretched-link btn-choose-product-attribute" data-alt-text="<%t SilverCart.Chosen 'Chosen' %>" data-item-id="{$ID}"></a>
            </div>
            <% end_if %>
        </div>
        <% end_loop %>
    </div>
    <% end_with %>
<% end_if %>