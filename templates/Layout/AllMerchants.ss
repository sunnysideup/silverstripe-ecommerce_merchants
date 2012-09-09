<div id="ShopPage">	

			<div class="span2">
				<form id="merchant_form">
					<h6>Selecteer locatie</h6>
					<label class="checkbox"><input class="address" name="city[]" value="Haarlem" type="checkbox"> Haarlem</label>
					<label class="checkbox"><input class="address" name="city[]" value="Amsterdam" type="checkbox"> Amsterdam</label>
				
					<p>&nbsp;</p>
				
					<h6>Selecteer categorie</h6>
					<label class="checkbox">
						<input name="category[]" class="category" value="Boeken" type="checkbox"> <i class="icon-book" ></i> Boeken</label>
					<label class="checkbox">
						<input name="category[]" class="category" value="Bloemen" type="checkbox"> <i class="icon-music" ></i> Bloemen</label>
					<label class="checkbox">
						<input name="category[]" class="category" value="Horeca" type="checkbox"> <i class="icon-eye-open" ></i> Horeca</label>
					<label class="checkbox">
						<input name="category[]" class="category" value="In huis" type="checkbox"> <i class="icon-github" ></i> In huis</label>
					<label class="checkbox">
						<input name="category[]" class="category" value="Nog iets" type="checkbox"> <i class="icon-home" ></i> Nog iets</label>
					<label class="checkbox">
						<input name="category[]" class="category" value="Consectetur" type="checkbox"> <i class="icon-film" ></i> Consectetur</label>
				</form>
			</div>
			
			<div class="span10">
				<h4><i class="icon-map-marker"></i> Alle winkels in Haarlem</h4>
				<hr/>
				<div class="loading_gif">
					<img src="$Top.ThemeDir/images/loading.gif" />
					<p>Loading data...</p>
				</div>
								
				<div id="MerchantList">
				
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

					<div class="row-fluid">
						<div class="span4">
			    			<a href="#" class="">
								<img src="mysite/img/temp/1_streamer_coffee_company_01.jpg" alt="1_streamer_coffee_company_01" />
			    			</a>						
							<h4><a href="#">Anne&Max</a></h4>
							<p class="location"><i class="icon-map-marker"></i> <a href="">Haarlem</a> / <a href="#">Amsterdam</a></p>
						</div>
						<div class="span4">
			    		<a href="#" class="">
								<img src="mysite/img/temp/1_streamer_coffee_company_01.jpg" alt="1_streamer_coffee_company_01" />
			    		</a>						
							<h4><a href="#">Anne&Max</a></h4>
							<p class="location"><i class="icon-map-marker"></i> <a href="">Haarlem</a> / <a href="#">Amsterdam</a></p>
						</div>
						<div class="span4">
			    		<a href="#" class="">
								<img src="mysite/img/temp/1_streamer_coffee_company_01.jpg" alt="1_streamer_coffee_company_01" />
			    		</a>						
							<h4><a href="#">Anne&Max</a></h4>
							<p class="location"><i class="icon-map-marker"></i> <a href="">Haarlem</a> / <a href="#">Amsterdam</a></p>
						</div>
					</div>

					<div class="row-fluid">
						<div class="span4">
			    		<a href="#" class="">
								<img src="mysite/img/temp/1_streamer_coffee_company_01.jpg" alt="1_streamer_coffee_company_01" />
			    		</a>						
							<h4><a href="#">Anne&Max</a></h4>
							<p class="location"><i class="icon-map-marker"></i> <a href="">Haarlem</a> / <a href="#">Amsterdam</a></p>
						</div>
						<div class="span4">
			    		<a href="#" class="">
								<img src="mysite/img/temp/1_streamer_coffee_company_01.jpg" alt="1_streamer_coffee_company_01" />
			    		</a>						
							<h4><a href="#">Anne&Max</a></h4>
							<p class="location"><i class="icon-map-marker"></i> <a href="">Haarlem</a> / <a href="#">Amsterdam</a></p>
						</div>
						<div class="span4">
			    		<a href="#" class="">
								<img src="mysite/img/temp/1_streamer_coffee_company_01.jpg" alt="1_streamer_coffee_company_01" />
			    		</a>						
							<h4><a href="#">Anne&Max</a></h4>
							<p class="location"><i class="icon-map-marker"></i> <a href="">Haarlem</a> / <a href="#">Amsterdam</a></p>
						</div>
					</div>

					<div class="row-fluid">
						<div class="span4">
			    		<a href="#" class="">
								<img src="mysite/img/temp/1_streamer_coffee_company_01.jpg" alt="1_streamer_coffee_company_01" />
			    		</a>						
							<h4><a href="#">Anne&Max</a></h4>
							<p class="location"><i class="icon-map-marker"></i> <a href="">Haarlem</a> / <a href="#">Amsterdam</a></p>
						</div>
						<div class="span4">
			    		<a href="#" class="">
								<img src="mysite/img/temp/1_streamer_coffee_company_01.jpg" alt="1_streamer_coffee_company_01" />
			    		</a>						
							<h4><a href="#">Anne&Max</a></h4>
							<p class="location"><i class="icon-map-marker"></i> <a href="">Haarlem</a> / <a href="#">Amsterdam</a></p>
						</div>
						<div class="span4">
						</div>
					</div>
					
				</div>

				
				<div id="ProductList">
				
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
				
					<div class="row-fluid">
						<div class="span2">
			    		<a href="#" class="">
								<img src="mysite/img/temp/1_streamer_coffee_company_01.jpg" alt="1_streamer_coffee_company_01" />
			    		</a>
							<h4><a href="#">Koffie verkeerd</a></h4>
							<p class="location"><i class="icon-map-marker"></i> <a href="">Anne&Max</a></p>
						</div>
						<div class="span2">
			    		<a href="#" class="">
								<img src="mysite/img/temp/1_streamer_coffee_company_01.jpg" alt="1_streamer_coffee_company_01" />
			    		</a>
							<h4><a href="#">Espresso</a></h4>
							<p class="location"><i class="icon-map-marker"></i> <a href="">Anne&Max</a></p>
						</div>
						<div class="span2">
			    		<a href="#" class="">
								<img src="mysite/img/temp/1_streamer_coffee_company_01.jpg" alt="1_streamer_coffee_company_01" />
			    		</a>
							<h4><a href="#">Ontbijt van de dag</a></h4>
							<p class="location"><i class="icon-map-marker"></i> <a href="">Anne&Max</a></p>
						</div>
						<div class="span2">
			    		<a href="#" class="">
								<img src="mysite/img/temp/1_streamer_coffee_company_01.jpg" alt="1_streamer_coffee_company_01" />
			    		</a>
							<h4><a href="#">Espresso Double</a></h4>
							<p class="location"><i class="icon-map-marker"></i> <a href="">Anne&Max</a></p>
						</div>
						<div class="span2">
			    		<a href="#" class="">
								<img src="mysite/img/temp/1_streamer_coffee_company_01.jpg" alt="1_streamer_coffee_company_01" />
			    		</a>
							<h4><a href="#">Koffie verkeerd</a></h4>
							<p class="location"><i class="icon-map-marker"></i> <a href="">Anne&Max</a></p>
						</div>
						<div class="span2">
			    		<a href="#" class="">
								<img src="mysite/img/temp/1_streamer_coffee_company_01.jpg" alt="1_streamer_coffee_company_01" />
			    		</a>
							<h4><a href="#">Koffie verkeerd</a></h4>
							<p class="location"><i class="icon-map-marker"></i> <a href="">Anne&Max</a></p>
						</div>
					</div>

					<div class="row-fluid">
						<div class="span2">
			    		<a href="#" class="">
								<img src="mysite/img/temp/1_streamer_coffee_company_01.jpg" alt="1_streamer_coffee_company_01" />
			    		</a>
							<h4><a href="#">Koffie verkeerd</a></h4>
							<p class="location"><i class="icon-map-marker"></i> <a href="">Anne&Max</a></p>
						</div>
						<div class="span2">
			    		<a href="#" class="">
								<img src="mysite/img/temp/1_streamer_coffee_company_01.jpg" alt="1_streamer_coffee_company_01" />
			    		</a>
							<h4><a href="#">Espresso</a></h4>
							<p class="location"><i class="icon-map-marker"></i> <a href="">Anne&Max</a></p>
						</div>
						<div class="span2">
			    		<a href="#" class="">
								<img src="mysite/img/temp/1_streamer_coffee_company_01.jpg" alt="1_streamer_coffee_company_01" />
			    		</a>
							<h4><a href="#">Ontbijt van de dag</a></h4>
							<p class="location"><i class="icon-map-marker"></i> <a href="">Anne&Max</a></p>
						</div>
						<div class="span2">
			    		<a href="#" class="">
								<img src="mysite/img/temp/1_streamer_coffee_company_01.jpg" alt="1_streamer_coffee_company_01" />
			    		</a>
							<h4><a href="#">Espresso Double</a></h4>
							<p class="location"><i class="icon-map-marker"></i> <a href="">Anne&Max</a></p>
						</div>
						<div class="span2">
			    		<a href="#" class="">
								<img src="mysite/img/temp/1_streamer_coffee_company_01.jpg" alt="1_streamer_coffee_company_01" />
			    		</a>
							<h4><a href="#">Koffie verkeerd</a></h4>
							<p class="location"><i class="icon-map-marker"></i> <a href="">Anne&Max</a></p>
						</div>
						<div class="span2">
			    		<a href="#" class="">
								<img src="mysite/img/temp/1_streamer_coffee_company_01.jpg" alt="1_streamer_coffee_company_01" />
			    		</a>
							<h4><a href="#">Koffie verkeerd</a></h4>
							<p class="location"><i class="icon-map-marker"></i> <a href="">Anne&Max</a></p>
						</div>
					</div>
				</div>

			</div>
		</div>
</div>