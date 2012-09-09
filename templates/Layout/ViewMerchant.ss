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
		
		<% if MemberIsAdmin %>
			<div style="width:300px; float:right;">
				<h3>Admin</h3>
				<p><a href="$Merchant.EditMerchantLink">edit merchant</a></p>
				<p><a href="$Merchant.AddProductLink">add products</a></p>
				<p><a href="$Merchant.ViewActivationCodesLink">view activation codes</a></p>
			</div>
		<% end_if %>
		
		<% control Merchant %>
			$Image.SetSize(100,100)
			<p>Name: $Name</p>
			<p>Address: $Address</p>
			<p>City: $City</p>
			<p>Country: $Country</p>
			<p>Owned by: $MerchantAdminID</p>
		<% end_control %>
		
		<h2>Products</h2>
		<% control Products %>
			<div style="border: 1px solid black; padding: 10px;">
				$Image.SetSize(100,100)
				<p>Title: $Title</p>
				<p>Model: $Model</p>
				<p>Price: $Price</p>
				<p>On sale: $ActivationCodesCount</p>
				<p>Stock left: $ActivationCodesLeft</p>
				<p><a href="$Link">view item</a></p>
				<% if Top.MemberIsAdmin %>
				<p><a href="$EditLink">edit item</a></p>
				<% end_if %>
			</div>
		<% end_control %>
		
		$Form
		$PageComments
	<% if Menu(2) %>
		</div>
	<% end_if %>
</div>