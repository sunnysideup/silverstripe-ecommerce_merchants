<% if Merchants %>
	<ul class="thumbnails" id="Merchants">
		<% control Merchants %>
			<li class="span4 $LinkingMode $FirstLast $EvenOdd">
				<div class="store-photo">
					<a href="$Link">$Image.CroppedImage(420, 260)</a>
					<% if Categories %>
					<div class="cat-labels">
						<% control Categories %>
							<span><a class="$Code.ATT">$SmallImage.SetSize(10, 10)</a></span>
						<% end_control %>
					</div>
				</div>
				<% end_if %>
				<h6><a href="$Link">$Title</h6>
			</li>
		<% end_control %>
	</ul>
<% else %>
	<p><% _t('AllMerchantsPage.NO_MERCHANTS', 'There are no merchants corresponding to this search.') %></p>
<% end_if %>
