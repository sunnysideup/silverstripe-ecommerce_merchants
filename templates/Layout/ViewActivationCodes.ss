<div class="typography">
	<% if Menu(2) %>
		<% include SideBar %>
		<div id="Content">
	<% end_if %>
			
	<% if Level(2) %>
	  	<% include BreadCrumbs %>
	<% end_if %>
	
		<h2>$Title</h2>
	
		$Content
		
		<% control ProductActivationCodes %>
			<% control Product %>
				<h3>$Title ($ID)</h3>
			<% end_control %>
			<% if ActivationCodes %>
				<% control ActivationCodes %>
				<p>$Code <% if Used %> - Sold - <% control Purchase %>$Name<% end_control %> <% end_if %></p>
				<% end_control %>
			<% else %>
				<p>No activation codes created for this product yet</p>
			<% end_if %>
		<% end_control %>

		$Form
		$PageComments
	<% if Menu(2) %>
		</div>
	<% end_if %>
</div>