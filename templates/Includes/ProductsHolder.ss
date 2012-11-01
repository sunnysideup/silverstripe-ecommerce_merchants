<% if Products %>
	<ul class="thumbnails" id="Products">
		<% control Products %>
			<li class="span3 $LinkingMode $FirstLast $EvenOdd">
				<div class="product-photo">
					<a href="$AddLink">$Image.CroppedImage(142,113)</a>
				</div>
				<h6><a href="$AddLink">$Title</a></h6>
				<p class="description">
					$Content.LimitCharacters(45)
				</p>
			</li>
		<% end_control %>
	</ul>
<% else %>
	<p><% _t('AllMerchantsPage.NO_PRODUCTS', 'There are no products corresponding to this search.') %></p>
<% end_if %>
