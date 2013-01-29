<% if Products %>
<% control Products %>
	<li class="span3 $LinkingMode $FirstLast $EvenOdd">
		<div class="product-photo">
			<a href="$Link">
				<% if Image %>
					$Image.PaddedImage(400,320)
				<% else %>
					<img src="http://placehold.it/400x320" class="img-rounded"/>
				<% end_if %>
			</a>
		</div>
		<h6><a href="$Link">$Title</a></h6>
		<p class="description">
			$Content.LimitCharacters(45)
		</p>
	</li>
<% end_control %>
<% if MoreProductsLink %><li class="allMerchantsPageMore"><a href="$MoreProductsLink" rel="Products">Meer Produkten</a></li><% end_if %>
<% else %>
<li><p><% _t('AllMerchantsPage.NO_PRODUCTS', 'Er zijn geen producten gevonden.') %></p></li>
<% end_if %>
