<?php
include("Products.php");
Class Cart {

	private static $products;

	private function __construct() { }

	public static function initCart() {
		if(!empty($_SESSION["cart"])) {
			self::$products = $_SESSION["cart"];
		} else {

			self::$products = array();
		}
		self::updateSession();

	}

	public static function getCart() {
		$totalSum = 0;
		$totalQuantity = 0;
		$result = array();
		foreach (self::$products as $id => $product) {
			$result["products"][]= array(
				"id" => $id,
				"quantity" => $product["quantity"],
				"sum" => $product["quantity"] * Products::getPrice($id)
			);
			$totalQuantity+= $product["quantity"];
			$totalSum+= $product["quantity"] * Products::getPrice($id);
		}

		$result["total_sum"] = $totalSum;
		$result["products_count"] = $totalQuantity;


		return $result;


	}

	public static function updateSession() {
		$_SESSION["cart"] = self::$products;
	}

	public static function addProduct($product_id, $quantity) {
		if(Products::isExist($product_id) === false) {
			throw new Exception (" Product with ID=$product_id is not found. ");
		}
		if ( is_int($quantity)==false || $quantity < 0 || $quantity > 10) {
			throw new Exception ("Quantity must be integer value from 1 to 10. Not $quantity. ");
		}
		if(!empty(self::$products[$product_id])) {
			self::$products[$product_id]["quantity"] = self::$products[$product_id]["quantity"] + $quantity;
		} else {
			self::$products[$product_id] = array("id" => $product_id, "quantity" => $quantity);
		}

		self::updateSession();
		return true;

	}

	public static function deleteProduct($product_id) {
		if(Products::isExist($product_id) == false) {
			throw new Exception ("Product with ID=$product_id is not found.");
		}
		if(empty(self::$products[$product_id])) {
			throw new Exception ("Product with ID=$product_id is not found at cart.");
		}
		if(self::$products[$product_id]["quantity"] > 1) {
			self::$products[$product_id]["quantity"] = self::$products[$product_id]["quantity"] - 1;
		} else {
			unset(self::$products[$product_id]);
		}
		self::updateSession();
		return true;

	}

}
