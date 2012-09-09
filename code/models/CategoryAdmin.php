<?php
//to do: we can add to the "Products" model admin

class CategoryAdmin extends ModelAdmin {

	public static $managed_models = array(
		'Category'
	);

	static $url_segment = 'category';

	static $menu_title = 'Product Categories';

}
