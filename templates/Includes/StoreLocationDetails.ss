<table>
	<% if Image %>$Image.SetWidth(250)<% end_if %>
	<% if Map %><a href="$Map.GMapLink"><img src="$Map.ImageLink" alt="Location for $Title.ATT"/></a><% end_if %>
	<% if OpeningHours %><tr><th><% _t('MerchantLocation.OPENING_HOURS', 'Opening Hours') %></th><td>$OpeningHours</td></tr><% end_if %>
	<% if Address %><tr><th><% _t('OrderAddress.ADDRESS', 'Address') %></th><td><% if Map %><a href="$Map.GMapLink"><% end_if %>$Address<br/><% if Address2 %><br />$Address2<% end_if %></a></td></tr><% end_if %>
	<% if PostalCode %><tr><th><% _t('OrderAddress.POSTALCODE', 'Postal Code') %></th><td>$PostalCode</td></tr><% end_if %>
	<% if City %><tr><th><% _t('City.SINGULARNAME', 'City') %></th><td>$City.Name</td></tr><% end_if %>
	<% if Phone %><tr><th><% _t('OrderAddress.PHONE', 'Phone') %></th><td>$Phone</td></tr><% end_if %>
	<% control Parent %><% if Website %><tr><th><% _t('MerchantAdminDOD.WEBSITE', 'Website') %></th><td><a href="$Website.URL">$Website</a></td></tr><% end_if %><% end_control %>
</table>


