<% if Merchants(3) %>
	<% control Merchants(3) %>
	<div class="row-fluid">
		<% control Row %>
		<div class="span4">
			<a href="$ViewLink" class="">
				$Image.SetSize(248,165)
			</a>						
			<h4><a href="$ViewLink">$Name</a></h4>
			<p class="location"><i class="icon-map-marker"></i> <a href="">$Address</a> / <a href="#">$City</a></p>
		</div>
		<% end_control %>
	</div>
	<% end_control %>
<% else %>
	<p>No merchants fit these criteria</p>
<% end_if %>