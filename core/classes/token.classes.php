<?php

/*
	* Token Class Set
	* @Version 4.0.0
	* Developed by: Ami (亜美) Denault
*/
/*
	* Setup Token Class
	* @since 4.0.0
*/
declare(strict_types=1);
class Token{

/*
	* Generate
	* @since 4.0.0	
*/	
	public static function generate(){
		return Session::put(Config::get('session/token_name'),md5((uniqid())));
	}

/*
	* Check
	* @since 4.0.0
	* @param (String Token)
*/
	public static function check(string $token):bool{
		$tokenName =Config::get('session/token_name');

		if(Session::exists($tokenName) && $token == Session::get($tokenName)){
			Session::delete($tokenName);
			return true;
		}
		return false;
	}

}
?>