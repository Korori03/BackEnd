<?php

/*
	* Input Class Set
	* @Version 4.0.0
	* Developed by: Ami (亜美) Denault
*/
declare(strict_types=1);
class Input{

/*
	* Input Exists
	* @since 4.0.0	
	* @Param (String Type {Post or Get})
*/			
		public static function exists(string $type ='post'):bool{
			switch($type){
				case 'post':
					return (!empty($_POST))?true:false;
					break;
				case 'get':
					return (!empty($_GET))?true:false;
					break;
				default:
					return false;
					break;
			}
		}

/*
	* Get Input Item
	* @since 4.0.0	
	* @Param (String Item)
*/		
	public static function get(string $item):string{
		if(isset($_POST[$item])){
			return Sanitize::clean($_POST[$item]);
		}else if(isset($_GET[$item])){
			return Sanitize::clean($_GET[$item]);
		}
		return '';
	}
}
?>