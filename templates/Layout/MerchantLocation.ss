<div class="span9" id="ProductsHolder">
	<% if Products %>
		<ul id="Products">
			<% control Products %>
				<% include ProductGroupItem %>
			<% end_control %>
		</ul>
	<% else %>
		<p><% _t('MerchantLocation.NO_PRODUCTS', 'There are no products on sale in this store.') %></p>
	<% end_if %>
</div>
<div class="span3" id="DetailsHolder">
	$Image
	<h6>$Title</h6>
	<% include StoreLocationDetails %>
</div>
<div class="clear"></div>
