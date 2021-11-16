<?php


/*
	* Config Class Set
	* @Version 4.0.0
	* Developed by: Ami (亜美) Denault
*/

/*
	* Setup Config Class
	* @since 4.0.0
*/
declare(strict_types=1);
class Config{

/*
	* Get Config
	* @ Version 1.0.0
	* @ Since 4.0.0
	* @ Param (String Path)
*/
	public static function get(string $path=null){
		if($path){
			$config = $GLOBALS['config'];
			$path =explode('/',$path);
			
			foreach($path as $bit){
				if(isset($config[$bit]))
					$config = $config[$bit];
			}
			return is_array($config)?'':$config;
		}
		return '';
	}
}

?>