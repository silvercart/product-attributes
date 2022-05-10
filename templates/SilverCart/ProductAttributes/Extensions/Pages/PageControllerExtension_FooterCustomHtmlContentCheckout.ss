<% if $ProductAddCartFormModals.exists %>
    <% loop $ProductAddCartFormModals %>
        <% include SilverCart\ProductAttributes\ModalAddToCart %>
    <% end_loop %>
<% end_if %>