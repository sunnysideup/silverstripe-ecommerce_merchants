<?php

//to do: why not use ProductGroup Page?
class Category extends DataObject {

	static $db = array(
		'Name' => 'Text'
	);

	static $has_many = array(
		'Products' => 'Product'
	);

}
