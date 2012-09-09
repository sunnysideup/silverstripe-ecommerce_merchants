<?php
class AllMerchants extends Page {

	static $db = array();

	static $has_one = array();

}

class AllMerchants_Controller extends Page_Controller {

	//to do: controller methods are all lowercase

	public function AjaxMerchants(){
		$merchants = $this->Merchants(3, $_REQUEST['params']);
		return $this->customise(array('Merchants' => $merchants))->renderWith('MerchantRowsAjax');
	}

	public function AjaxProducts(){
		$products = $this->Products(6, $_REQUEST['params']);
		return $this->customise(array('Products' => $products))->renderWith('ProductRowsAjax');
	}

	public function Merchants($numPerLine, $clauses = false){
		$merchants = Merchant::get_by_search_criteria($clauses);
		return self::group_into_rows_of($numPerLine, $merchants);
	}

	public function Products($numPerLine, $clauses = false){
		$products = MerchantProductDecorator::get_by_search_criteria($clauses);
		return self::group_into_rows_of($numPerLine, $products);
	}

	//to do: please cleanup indenting... always use tabs please
	//to do: ask Nicolaas how to do a smart way for listing per row....
	static function group_into_rows_of($numPerLine, $dataObjectSet){
		$rows = array();
		$row = new DataObjectSet();
		$count = 0;
			if($dataObjectSet) foreach($dataObjectSet as $item){
			$row->push($item);
			$count++;
			if($count == $numPerLine){
				$rows[] = array('Row' => $row);
				$count = 0;
				$row = new DataObjectSet();
			}
			}
		if($count){ $rows[] = array('Row' => $row); }

			return new DataObjectSet($rows);
	}

}
