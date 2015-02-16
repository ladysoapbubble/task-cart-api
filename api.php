<?php
abstract class API {

	protected $method = '';

	protected $endpoint = '';

	protected $args = Array();


	const STATUS_OK = 200;
	const STATUS_NOT_FOUND = 404;
	const STATUS_WRONG_PARAM = 400;


	public function __construct($request, $data) {

		header("Access-Control-Allow-Orgin: *");
		header("Access-Control-Allow-Methods: *");
		header("Content-Type: application/json");

		$this->args = explode('/', rtrim($request, '/'));

		$this->endpoint = array_shift($this->args);

		$getarg = array();
		for($i = 0; $i < count($this->args); $i += 2) {
			$getarg[$this->args[$i]] = $this->args[$i + 1];

		}
		$this->args = $getarg;


		if(!empty($data)) $this->args = array_merge($this->args, $data);


		$this->method = $_SERVER['REQUEST_METHOD'];
		if($this->method == 'POST' && array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER)) {
			if($_SERVER['HTTP_X_HTTP_METHOD'] == 'DELETE') {
				$this->method = 'DELETE';
			} else {
				if($_SERVER['HTTP_X_HTTP_METHOD'] == 'PUT') {
					$this->method = 'PUT';
				} else {
					throw new Exception("Unexpected Header");
				}
			}
		}

		switch($this->method) {
			case 'DELETE':
			case 'POST':
				$this->request = $this->_cleanInputs($_POST);
				break;
			case 'GET':
				$this->request = $this->_cleanInputs($_GET);
				break;
			case 'PUT':
				$this->request = $this->_cleanInputs($_GET);
				$this->file = file_get_contents("php://input");
				break;
			default:
				$this->_response('Invalid Method', 405);
				break;
		}
	}

	public function processAPI() {
		$data = array(
			"error" => array(
				"type" => "invalid_request_error",
				"message" => ""
			)
		);
		if((int)method_exists($this, $this->endpoint) > 0) {
			return $this->_response($this->{$this->endpoint}($this->args));
		}

		$data["message"] = "Unable to resolve the request {$this->endpoint}";
		return $this->_response(array("status" => self::STATUS_NOT_FOUND, "data" => $data));
	}

	private function _response($data) {
		$status = $data["status"];
		header("HTTP/1.1 ".$status." ".$this->_requestStatus($status));
		return json_encode($data["data"]);
	}

	private function _cleanInputs($data) {
		$clean_input = Array();
		if(is_array($data)) {
			foreach($data as $k => $v) {
				$clean_input[$k] = $this->_cleanInputs($v);
			}
		} else {
			$clean_input = trim(strip_tags($data));
		}
		return $clean_input;
	}

	private function _requestStatus($code) {
		$status = array(
			self::STATUS_OK => 'OK',
			self::STATUS_NOT_FOUND => 'Not Found',
			self::STATUS_WRONG_PARAM => 'Wrong Param',
			500 => 'Internal Server Error',
		);
		return ($status[$code]) ? $status[$code] : $status[500];
	}
}

class CartAPI extends API {

	protected function products() {
		$data = array(
			"error" => array(
				"type" => "invalid_param_error",
				"message" => "Unexpected method for this request."
			)
		);
		$res = array("status" => 500, "data" => $data);

		if($this->method == 'GET') {
			try {
				$res["status"] = self::STATUS_OK;
				$res["data"] = Products::getList();
			} catch(Exception $e) {
				$res["status"] = self::STATUS_WRONG_PARAM;
				$data["error"]["message"] = $e->getMessage();
				$res["data"] = $data;
			}
		}
		return $res;
	}

	protected function cart($args) {
		$data = array(
			"error" => array(
				"type" => "invalid_param_error",
				"message" => "Unexpected method for this request."
			),
			"params" => array(
				array(
					"code" => "required",
					"message" => "Product cannot be blank",
					"name" => "product_id"
				),
				array(
					"code" => "required",
					"message" => "Quantity cannot be blank.",
					"name" => "quantity"
				)
			)
		);
		$res = array("status" => self::STATUS_WRONG_PARAM, "data" => $data);


		try {
			if($this->method == 'POST' || $this->method == 'DELETE') {
				if(empty($args["product_id"])) {
					$data["error"]["message"] = "Product id cannot be blank";
					$res["data"] = $data;
					return $res;
				}
				$product_id = $args["product_id"];

				if(Products::isExist($product_id) == FALSE) {
					$data["error"]["message"] = "Product with ID=$product_id is not found.";
					$res["data"] = $data;
					return $res;
				}

				if($this->method == 'POST' && empty($args["quantity"])) {
					$data["error"]["message"] = "Quantity cannot be blank";
					$res["data"] = $data;
					return $res;
				}
				$quantity = $args["quantity"];
			}


			if($this->method == 'GET') {
				$res["data"] = Cart::getCart();
				$res["status"] = self::STATUS_OK;

			} elseif($this->method == 'POST') {
				Cart::addProduct($product_id, $quantity);

				$res["data"] = "";
				$res["status"] = self::STATUS_OK;

			} elseif($this->method == 'DELETE') {

				Cart::deleteProduct($product_id);
				$res["data"] = "";
				$res["status"] = self::STATUS_OK;

			}

		} catch(Exception $e) {

			$data["error"]["message"] = $e->getMessage();
			$res["data"] = $data;
		}

		return $res;

	}


}