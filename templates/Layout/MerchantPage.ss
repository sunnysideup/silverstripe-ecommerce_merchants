<div id="MemberFormHolder">
	$MemberForm
</div>
<div id="SocialFormHolder">
</div>
<div class="clear"></div>
<div id="ProductsHolder">
	<h2><% _t('MerchantPage.MY_PRODUCTS', 'My Products') %> (<% if Products %>$Products.Count<% else %>0<% end_if %>)</h2>
	<p><a href="$AddProductLink">Click here</a> to add a new product.</p>
	<% if Products %>
		<ul id="Products">
			<% control Products %>
				<li class="$LinkingMode $FirstLast $EvenOdd"><span class="title">$Title</span> <span class="links">(<a href="$Link" title="<% _t('MerchantPage.VIEW', 'View') %>"><% _t('MerchantPage.VIEW', 'View') %></a>, <a href="$EditLink" title="<% _t('MerchantPage.EDIT', 'Edit') %>"><% _t('MerchantPage.EDIT', 'Edit') %></a>)</span></li>
			<% end_control %>
		</ul>
	<% end_if %>
</div>
<div id="StoreAndLocationHolder">
	<h2>My Store ($Title)</h2>
	$MerchantPageForm
	<h2><% _t('MerchantPage.MY_STORE_LOCATIONS', 'My Store Locations') %> (<% if Locations %>$Locations.Count<% else %>0<% end_if %>)</h2>
	<p><a href="$AddLocationLink">Click here</a> to add a new location.</p>
	<% if Locations %>
		<ul id="Locations">
			<% control Locations %>
				<li class="$LinkingMode $FirstLast $EvenOdd"><span class="title">$Title</span> <span class="links">(<a href="$Link" title="<% _t('MerchantPage.VIEW', 'View') %>"><% _t('MerchantPage.VIEW', 'View') %></a>, <a href="$EditLink" title="<% _t('MerchantPage.EDIT', 'Edit') %>"><% _t('MerchantPage.EDIT', 'Edit') %></a>)</span></li>
			<% end_control %>
		</ul>
	<% end_if %>
</div>
<div class="clear"></div>