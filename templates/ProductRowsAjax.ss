<% if Products(6) %>
	<% control Products(6) %>
	<div class="row-fluid">
		<% control Row %>
		<div class="span2">
			<a href="$Link" class="">
				$Image.SetSize(116,77)
			</a>						
			<h4><a href="$Link">$Title</a></h4>
			<p class="location"><i class="icon-map-marker"></i> <a href="">Address</a> / <a href="#">City</a></p>
		</div>
		<% end_control %>
	</div>
	<% end_control %>
<% else %>
	<p>No products fit these criteria</p>
<% end_if %>