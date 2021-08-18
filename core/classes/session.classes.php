<?php

/*
	* Session Class Set
	* @Version 4.0.0
	* Developed by: Ami (亜美) Denault
*/
/*
	* Setup Session Class
	* @since 4.0.0
*/
declare(strict_types=1);
class Session{

/*
	* Session Exists
	* @since 4.0.0
	* @param (String Name)
*/
	public static function exists(string $name):bool{
		return (isset($_SESSION[$name]))?true:false;
	}

/*
	* Session Put/Set
	* @since 4.0.0	
	* @param (String Name)
*/	
	public static function put(string $name,mixed  $value):bool{
		$_SESSION[$name] = cast::_string($value);
		return true;
	}
	public static function set(string $name,mixed  $value){
		self::put($name,$value);
	}
	
/*
	* Session Put/Set PDF
	* @since 4.0.1	
	* @param (String Name)
*/		
	public static function putPDF(string $name,mixed  $value):bool{
		$_SESSION['pdf'][$name] = cast::_string($value);
		return true;
	}
	
	public static function setPDF(string $name,mixed $value):void{
		self::putPDF($name,$value);
	}

/*
	* Session Get
	* @since 4.0.1	
	* @param (String Name)
*/		
	public static function getPDF(string $name):string{
		return cast::_string($_SESSION['pdf'][$name]);
	}

/*
	* Session Delete PDF
	* @since 4.0.1	
	* @param (String Name)
*/		
	public static function deletePdfAll():void{
		if(self::exists('pdf')){
			unset($_SESSION['pdf']);
		}
	}
/*
	* Session Get
	* @since 4.0.0	
	* @param (String Name)
*/	
	public static function get(string $name):string{
		return cast::_string($_SESSION[$name]);
	}
	
/*
	* Session Delete
	* @since 4.0.0	
	* @param (String Name)
*/
	public static function delete(string $name):void{
		if(self::exists($name)){
			unset($_SESSION[$name]);
		}
	}

/*
	* Session Flash
	* @since 4.0.0	
	* @param (String Name,String)
*/	
	public static function flash(string $name,$string=''):string{
		if(self::exists($name)){
			$session=self::get($name);
			self::delete($name);
			return $session;
		}
		else{
			self::put($name,$string);
		}
	}
}
?>