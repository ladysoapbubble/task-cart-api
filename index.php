<?php
include('Products.php');
include('Cart.php');
include("api.php");

session_set_cookie_params('300');
session_start();

$req = file_get_contents('php://input');
$data = (array)json_decode($req);

Cart::initCart();

try {
	$API = new CartAPI($_REQUEST['request'], $data);
	echo $API->processAPI();

} catch(Exception $e) {
	echo json_encode(array('error' => $e->getMessage()));
}

