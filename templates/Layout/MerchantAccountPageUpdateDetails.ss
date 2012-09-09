<div id="MerchantAccountPageUserDetails" class="mainSection content-container noSidebar">

	<% if Content %><div id="ContentHolder">$Content</div><% end_if %>

<% if IsApprovedMerchantGroup %>
	<div id="MerchantAccountPageOtherAccountsOuter" class="outerHolder">
		<h3><% _t("EcommerceMerchantAccount.USERACCOUNTSFOR", "User accounts for") %> $GroupTitle</h3>
		<% if GroupMembers %>
			<ul>
			<% control GroupMembers %>
				<li class="$LinkingMode">$Name ($Email)</li>
			<% end_control %>
			</ul>
		<% else %>
			<p class="message"><% _t("EcommerceMerchantAccount.NOOTHERUSERS", "There are no other users for this merchant account.") %></p>
		<% end_if %>
	</div>
	<% end_if %>

	<div id="MerchantAccountPageOrganisationFormOuter" class="outerHolder">
		<h3><h3><% _t("EcommerceMerchantAccount.UPDATEDETAILSFOR", "Update details for") %> $GroupTitle</h3></h3>
		$OrganisationForm
	</div>

	<% if Form %><div id="FormHolder">$Form</div><% end_if %>

</div>
