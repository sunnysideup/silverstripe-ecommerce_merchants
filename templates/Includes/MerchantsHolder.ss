<% if Merchants %>
	<% control Merchants %>
	<li class="span4 $LinkingMode $FirstLast $EvenOdd">
		<div class="store-photo">
			<a href="$Link">
				<% if Image %>
					$Image.CroppedImage(400,260)
				<% else %>
					<img src="http://placehold.it/400x260" class="img-rounded"/>
				<% end_if %>
			</a>
			<% if Categories %>
			<div class="cat-labels">
				<% control Categories %>
					<span><a>$SmallImage</a></span>
				<% end_control %>
			</div>
		</div>
		<% end_if %>
		<h6><a href="$Link">$Title</a></h6>
		<% if AllMerchantAddress2 %>
			<p class="location">
				<% control AllMerchantAddress2 %>
					<a href="$Link">$Name</a><% if Last %><% else %> / <% end_if %>
				<% end_control %>
			</p>
		<% else %>
			<%-- p class="location">@todo: Add city1 / city2 / city3</p --%>
		<% end_if %>

	</li>
	<% end_control %>
	<% if MoreMerchantsLink %><li class="allMerchantsPageMore"><a href="$MoreMerchantsLink" rel="Merchants">Meer Winkels</a></li><% end_if %>
<% else %>
	<li><p><% _t('AllMerchantsPage.NO_MERCHANTS', 'Er zijn geen winkels gevonden.') %></p></li>
<% end_if %>
