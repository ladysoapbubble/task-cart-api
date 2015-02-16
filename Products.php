<?php
Class Products {
	public static $list = array(
		1 => array ("name" => "Product 1", "price" => 100, "description" => ""),
		2 => array ("name" => "Product 2", "price" => 200),
		3 => array ("name" => "Product 3", "price" => 300),
		4 => array ("name" => "Product 4", "price" => 400),
		18 => array ("name" => "Product 18", "price" => 50),
	);

	public static function isExist($product_id)
	{

		if(!empty(self::$list[(int) $product_id])) return true;
		return false;

	}

	public static function getList() {
		$res = array("data" => array());
		foreach(self::$list as $id=>$product) {
			$formattedProduct = array(
				"id"=>$id,
				"name"=>$product["name"],
				"description" => isset($product["description"]) ? $product["description"] : "",
				"price" => $product["price"]
			);
			$res["data"][]=$formattedProduct;
		}

		return $res;

	}

	public static function getPrice($product_id) {
		return self::$list[$product_id]["price"];
	}
}