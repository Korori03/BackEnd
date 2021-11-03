<?php

/*
	* Cookie Class Set
	* @Version 4.0.0
	* Developed by: Ami (亜美) Denault
*/
/*
	* Setup Cookie Class
	* @since 4.0.0
*/
declare(strict_types=1);
class Cookie{

/*
	* Cookie Exists
	* @since 4.0.0	
	* @Param (String Name)
*/		
	public static function exists(string $name):bool{
		return (isset($_COOKIE[$name]))?true : false;
	}

/*
	* Cookie Get Cookie
	* @since 4.0.0	
	* @Param (String Name)
*/
	public static function get(string $name):string{
		return cast::_string($_COOKIE[$name]);
	}

/*
	* Cookie Put Cookie
	* @since 4.0.0	
	* @Param (String Name, String Value, String Expiring Time)
*/	
	public static function put(string $name,$value,$expiry):bool{
		$domainName = $_SERVER['SERVER_NAME'];
		if (!$domainName) {
			$domainName = $_SERVER['HTTP_HOST'];
		}

		if(setcookie($name,$value,time() + $expiry,'/', $domainName))
			return true;
		return false;
	}

/*
	* Cookie Delete Cookie
	* @since 4.0.0	
	* @Param (String Name)
*/	
	public static function delete(string $name):void{
		self::put($name,'',time()-1);
	}
}
?>