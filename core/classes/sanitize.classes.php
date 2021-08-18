<?php

/*
	* Sanitize Class Set
	* @Version 4.0.0
	* Developed by: Ami (亜美) Denault
*/
/*
	* Setup Sanitize Class
	* @since 4.0.0
*/
declare(strict_types=1);
class Sanitize{

/*
	* Escape String
	* @since 4.0.0	
	* @param (String string)
*/
	public static function escape(string $string):string{
		return htmlentities($string,ENT_QUOTES,'UTF-8');
	}

/*
	* Clean String
	* @since 4.0.0	
	* @param (String/Int string,Boolean Ascii)
*/
	public static function clean(mixed $string,bool $ascii=true):string {
	
		if(is_numeric($string))
			$string = filter_var($string, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
		else{
			if($ascii==true)
				$string =self::escape(iconv('UTF-8', 'ASCII//TRANSLIT',self::normalize_msword(trim($string))));
			
			$string = preg_replace('/(?:\r\n|[\r\n])/', PHP_EOL, $string);
		}

		$string = str_replace('\"','"',$string);
		
		return $string;
	}

/*
	* Normalize Mircosoft Word String
	* @since 4.0.0	
	* @param (String string)
*/
	public static function normalize_msword(string $str):string{
		$invalid = array('Š'=>'S', 'š'=>'s', 'Đ'=>'Dj', 'đ'=>'dj', 'Ž'=>'Z', 'ž'=>'z','Č'=>'C', 'č'=>'c', 'Ć'=>'C', 'ć'=>'c', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A','Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E','Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O','Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y','Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a','æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e',  'ë'=>'e', 'ì'=>'i', 'í'=>'i','î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o','ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y',  'ý'=>'y', 'þ'=>'b','ÿ'=>'y', 'Ŕ'=>'R', 'ŕ'=>'r', "`" => "'", "´" => "'", "„" => ",", "`" => "'","´" => "'", "“" => "\"", "”" => "\"", "´" => "'", "&acirc;€™" => "'", "{" => "","~" => "", "–" => "-", "’" => "'");
		$str = str_replace(array_keys($invalid), array_values($invalid), $str);
		return $str;
	}

/**
	 * SQLi Input Check
	 * @since   2.0
	 * @param   (String Input)
*/
	public static function sqliInputCheck(string $string):bool{
		$SQLiKey = array('information_schema', 'information_schema.tables', 'concat','version()','--','0x3a','/*','*/','char(');
		for($x = 0;$x < count($SQLiKey);$x++){	
			if(strcmp($string, $SQLiKey[$x]) == 0)	
				return true;
		}

		return false;	
	}

}
?>