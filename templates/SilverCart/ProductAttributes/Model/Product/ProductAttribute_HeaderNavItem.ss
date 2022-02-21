<% if $HasGloballyChosenValues %>
<div class="nav-item nav-item-product-attribute mr-8 pt-md-40" data-reload-link="{$ReloadGlobalNavItemLink}">
    <span class="dropdown-toggle cursor-pointer text-uppercase d-inline-block" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="true">
        <% if $FontAwesomeIcon %>
            {$FontAwesomeIconHTML('p-relative b-4 text-blue-dark')}
        <% end_if %>
        <span class="p-relative b-n1 text-nowrap text-truncate d-inline-block" style="max-width: 140px;">
            <% loop $GloballyChosenValues %>
                {$Title}<% if not $Last %>, <% end_if %>
            <% end_loop %>
        </span>
        <ion-icon name="chevron-down-outline" class="text-gray icon lazyload icon--size-regular md hydrated" role="img"></ion-icon>
    </span>
    <div class="dropdown-menu dropdown-menu-right">
        <span class="dropdown-item">
            <i><%t SilverCart\ProductAttributes.YourGlobalAttributes 'Your {title}:' title=$PluralTitle %></i><br/>
            <% loop $GloballyChosenValues %>
                {$Title}<% if not $Last %>, <% end_if %>
            <% end_loop %>
        </span>
        <div class="dropdown-divider"></div>
        <span class="dropdown-item cursor-pointer" data-toggle="modal" data-target="#modal-choose-product-attribute"><%t SilverCart\ProductAttributes.UpdateGlobalAttributes 'Update {title}' title=$PluralTitle %></span>
    </div>
</div>
<% else %>
<div class="nav-item nav-item-product-attribute ml-8 mr-8 pt-10 pt-md-40 d-none d-sm-block" data-reload-link="{$ReloadGlobalNavItemLink}">
    <span class="cursor-pointer text-uppercase text-nowrap" data-toggle="modal" data-target="#modal-choose-product-attribute">
        <% if $FontAwesomeIcon %>
            {$FontAwesomeIconHTML('p-relative b-4 text-blue-dark')}
        <% end_if %>
        <span class="p-relative b-4"><%t SilverCart\ProductAttributes.Choose 'Choose {title}' title=$Title %></span>
    </span>
</div>
<% end_if %>