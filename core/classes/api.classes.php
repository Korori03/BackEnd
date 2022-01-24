<?php
/*
	* API Class
	* @Version 1.0.0
	* Developed by: Ami (亜美) Denault
*/
/*
	* Api
	* @Since 4.4.7
*/

declare(strict_types=1);
class Api
{

	public static  		$_SessionApi;

	public static		$_items,
		$_callList
		= 	array();

	/*
	* Constructor for API Class
	* @since 4.5.1
	* @ Param (Object,String)
*/
	private function __construct()
	{

		$data = json::decode(file_get_contents("php://input"), true);

		if (isset($data->session)) {
			$check =  self::checkSession(self::_GetIP(), $data->session);
			if ($check)
				self::$_SessionApi = $data->session;
			else
				self::UserCheck();
		} else if (isset($data->username) && isset($data->password)) {
			self::UserCheck();
		}
	}

	/*
	* Session Check for API Class
	* @since 4.5.1
	* @ Param (Strin,String)
*/
	public static function checkSession(string $ip, string $session): bool
	{
		$sql = sprintf("SELECT id FROM " . Config::get('table/users') . " WHERE ip = '%s' and session ='%s' LIMIT 1;", $ip, $session);
		$check = Database::getInstance()->query($sql);
		return ($check->count() > 0 ? true : false);
	}

	/*
	* Set Session ID
	* @since 4.5.1
	* @ Param (String,String)
*/
	public static function _SetSessionID(string $username, string $password): string
	{
		return Hash::make(
			Config::get('api/key') .
				$username .
				$password .
				self::_GetIP(false) .
				date::_custom(null, "Ymdhis")
		);
	}

	/*
	* Get IP
	* @since 4.5.1
	* @ Param (String)
*/
	public static function _GetIP(): string
	{
		$ip = '';
		if (!empty($_SERVER['HTTP_CLIENT_IP']))
			$ip	=	$_SERVER['HTTP_CLIENT_IP'];
		elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
			$ip	=	$_SERVER['HTTP_X_FORWARDED_FOR'];
		else
			$ip	=	$_SERVER['REMOTE_ADDR'];

		return $ip;
	}

	/*
	* Json Format
	* @since 4.5.1
	* @ Param (Boolean,Object,Integer)
*/
	public static function jsonFormat(bool $status, mixed $object): void
	{
		self::$_items["status"] = $status?'true':'false';
		self::$_items["object"] = $object;
		self::$_items["session"] = self::$_SessionApi;
		echo json::encode(
			self::$_items,
			JSON_PRETTY_PRINT
		);
	}

	/*
	* Not Found Format
	* @since 4.5.1
	* @ Param (Integer)
*/
	public static function NotFound(string $string):void
	{
		self::jsonFormat(false, $string);
	}

	/*
	* Json Error
	* @since 4.5.1
*/
	public static function JSONERROR(): void
	{
		switch (json_last_error()) {
			case JSON_ERROR_NONE:
				echo ' - No errors';
				break;
			case JSON_ERROR_DEPTH:
				echo ' - Maximum stack depth exceeded';
				break;
			case JSON_ERROR_STATE_MISMATCH:
				echo ' - Underflow or the modes mismatch';
				break;
			case JSON_ERROR_CTRL_CHAR:
				echo ' - Unexpected control character found';
				break;
			case JSON_ERROR_SYNTAX:
				echo ' - Syntax error, malformed JSON';
				break;
			case JSON_ERROR_UTF8:
				echo ' - Malformed UTF-8 characters, possibly incorrectly encoded';
				break;
			default:
				echo ' - Unknown error';
				break;
		}
	}

	/*
	* utf8ize
	* @since 4.5.1
	* @ Param (Object/Array/string)
*/
	public static function utf8ize(mixed $dat): string
	{
		if (is_string($dat)) {
			return utf8_encode($dat);
		} elseif (is_array($dat)) {
			$ret = [];
			foreach ($dat as $i => $d) $ret[$i] = self::utf8ize($d);
			return $ret;
		} elseif (is_object($dat)) {
			foreach ($dat as $i => $d) $dat->$i = self::utf8ize($d);
			return $dat;
		} else {
			return $dat;
		}
	}

	/*
	* Check If User has Access
	* @since 4.5.1
*/
	public static function UserCheck(): array
	{
		$products_arr = array();

		$validate = new Validate();
		$data = json::decode(file_get_contents("php://input"), true);

		$validation = $validate->check($data, array(
			'username'	=>	array('name' => 'Username', 'required' => true),
			'password'	=>	array('name' => 'Password', 'required' => true)
		));

		if ($validation->passed()) {
			$user = new User();
			$username = $data['username'];
			$password =  $data['password'];

			$login = $user->login($username, $password);

			if ($login) {
				$products_arr["status"]		= true;
				$products_arr["object"]		=	"Login Session Created Successfully";
				$products_arr["session"]	=  self::_SetSessionID($username, $password);

				$sql = sprintf("Update " . Config::get('table/users') . " SET `ip` = '%s', `session` = '%s' WHERE `username` ='%s';",self::_GetIP(),$products_arr["session"],$username);

				Database::getInstance()->query($sql);
				self::$_SessionApi = $products_arr["session"];
				Session::set('session_api', $products_arr["session"]);
			} else {
				$products_arr["status"] 	= false;
				$products_arr["object"] 	= "Sorry, logging in failed";
				$products_arr["session"]	= '';
			}
		} else {
			$products_arr["status"]	=false;
			$products_arr["object"]	= implode(',', $validation->errors());
			$products_arr["session"]	= '';
		}
		return $products_arr;
	}

	/*
	* Get API Type
	* @since 4.5.1
*/
	public static function GetAPI():void
	{
		$data = json::decode(file_get_contents("php://input"));

		if (isset($data->session)) {
			$check =  self::checkSession(self::_GetIP(), $data->session);
			if ($check){
				self::$_SessionApi = $data->session;

				$return = self::UserCheck();
				if ($return['status']) {
					self::Grabber($data);
				}
				else
					self::jsonFormat($return['status'], $return['object']);
			}
		} else if (Session::exists("session_api")) {

			$check =  self::checkSession(self::_GetIP(), Session::get("session_api"));

			if ($check){
				self::$_SessionApi = Session::get("session_api");

				$return = self::UserCheck();
				if ($return['status']) {
					self::Grabber($data);
				}
				else
					self::jsonFormat($return['status'], $return['object']);
			}
		} else {
			if (empty(self::$_SessionApi)) {
				if (isset($data->username) && isset($data->password)) {
					$return = self::UserCheck();
					if ($return['status']) {
						self::Grabber($data);
					}
					else
						self::jsonFormat($return['status'], $return['object']);

				} else {
					$object = new stdClass();
					$object->message = "Please sign in to create session API ID";
					self::jsonFormat(true, $object);
				}
			}
		}

	}
	public static function Grabber($data):void{

		//Session validation
		header("Access-Control-Allow-Origin: *");
		header("Content-Type: application/json; charset=UTF-8");

		if (
			$_SERVER['REQUEST_METHOD'] === 'POST' ||
			$_SERVER['REQUEST_METHOD'] === 'DELETE' ||
			$_SERVER['REQUEST_METHOD'] === 'PUT'
		) {
			header("Access-Control-Allow-Methods: POST");
			header("Access-Control-Max-Age: 3600");
			header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
		}


		$table_ref = ucfirst($data->table);
		$method = $data->method;

		if (!empty($data)) {
			if (class_exists($table_ref)) {
				if (method_exists($table_ref, $method)) {
					$obj = new $table_ref();
					if (list($dynamicCall, $object) = call_user_func_array(array($obj, $method), array($data))) { //call_user_func(array($table_ref,$method),$data)){
						if ($dynamicCall)
							self::jsonFormat(true, $object);
						else
							self::jsonFormat(false, $object);
					} else
						self::jsonFormat(false, "Unable to call function for API (" . $table_ref . "::" . $method . ")");
				} else
					self::jsonFormat(false, "Method " . $method . " is not found.");
			} else
				self::jsonFormat(false, "'" . ucfirst($table_ref) . "' Class does not exist");
		} else
			self::jsonFormat(false, "Unable to create product. Data is incomplete.");
	}

	/*
	* Setup Where or Set
	* @since 4.5.1
	* @ Param (Array,Object,String)
*/
	public static function APISetup(mixed $columns,mixed $data, string $type = 'where'): array
	{

		$update = array();
		$where = array();

		foreach ($data as $key => $value) {
			if (isset($data->{$key}->update)) {
				if ($data->{$key}->update == true)
					$update[$key] = $data->{$key};
			} else
				$where[$key] = $data->{$key};
		}

		$updates = array();
		for ($x = 0; $x < count($columns); $x++) {
			if (array_key_exists($columns[$x], $update))
				$updates[] = '`' . $columns[$x] . "`=:$columns[$x]";
		}

		$wheres = array();
		for ($x = 0; $x < count($columns); $x++) {
			if (array_key_exists($columns[$x], $where))
				$wheres[] = '`' . $columns[$x] . '`' . (!empty($data->{$columns[$x]}->operator) ? ' ' . $data->{$columns[$x]}->operator . ' ' : '=') . ":$columns[$x]";
		}
		$concat = ($type === 'where' ? ', ' : ' AND ');

		return array(implode(' AND ', $wheres), implode($concat, $updates));
	}

	/*
	* Submit Function
	* @since 4.5.1
	* @ Param (Object,Array,String)
*/
	public static function submit(mixed $data,mixed $columns,mixed $query): array
	{
		$stmt = Database::getInstance()->queryAPI($query, $data->data, $columns);
		$object = new stdClass();

		$success = (int)$stmt->error() === 0 ? true : false;
		$results = ($success ? $stmt->results() : $stmt->errorMsg());

		switch (explode(' ', str::_toupper($query))[0]) {
			case 'DELETE':
				$results = ($success ? $object->message = "Successfully deleted record" : $stmt->errorMsg());
				break;
			case 'INSERT':
				$results = ($success ? $object->message = "Successfully inserted record" : $stmt->errorMsg());
				break;
			case 'UPDATE':
				$results = ($success ? $object->message = "Successfully updated record" : $stmt->errorMsg());
				break;
		}

		return array($success, $results);
	}

	/*
	* Call API
	* @since 4.5.1
	* @ Param (String,String,Array,Array)
*/
	public static function CallAPI(string $method, string $url, array $data = [], array $headers = []):void
	{
		$_header 	=	['Content-Type: application/json'];
		$curl 		=	curl_init();

		switch (str::_toupper($method)) {
			case 'POST':
				curl_setopt($curl, CURLOPT_POST, true);
				curl_setopt($curl, CURLOPT_POSTFIELDS, json::encode($data));
				break;
			case 'GET':
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
				if ($data)
					curl_setopt($curl, CURLOPT_POSTFIELDS, json::encode($data));
				break;
			case 'DELETE':
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
				curl_setopt($curl, CURLOPT_POSTFIELDS, json::encode($data));
				break;
			case 'PUT':
				curl_setopt($curl, CURLOPT_PUT, true);
				curl_setopt($curl, CURLOPT_POSTFIELDS, json::encode($data));
				break;
		}

		if ($headers)
			array_push($_header, (object)$headers);

		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $_header);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

		$result = curl_exec($curl);

		$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		curl_close($curl);

		$error_status = '';
		switch ($httpCode) {
			case 404:
				$error_status = '404: API Not found';
				break;
			case 500:
				$error_status = '500: servers replied with an error.';
				break;
			case 502:
				$error_status = '502: servers may be down or being upgraded.';
				break;
			case 503:
				$error_status = '503: service unavailable.';
				break;
		}

		if ($error_status)
			return self::jsonFormat(false, $error_status);
		else
			return self::jsonFormat(true, $result);
	}
}
