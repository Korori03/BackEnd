<?php

/*
	* Hash Class Set
	* @Version 4.0.1
	* Developed by: Ami (亜美) Denault
*/
/*
	* Setup Hash Class
	* @since 4.0.0
*/
declare(strict_types=1);
class Hash{

/*
	* Hash Creation
	* @since 4.0.1	
	* @Param (String Name)
*/	
	public static function make(string $string):string{
		
		$temp 		= md5(htmlentities(Sanitize::clean($string)));
		$iStart 	= cast::_int(Config::get('hash/makeHash/initial/start'));
		$iLength 	= cast::_int(Config::get('hash/makeHash/initial/length'));
		$mStart1 	= cast::_int(Config::get('hash/makeHash/mid_one/start'));
		$mLength1 	= cast::_int(Config::get('hash/makeHash/mid_one/length'));
		$mStart2 	= cast::_int(Config::get('hash/makeHash/mid_two/start'));
		$mLength2 	= cast::_int(Config::get('hash/makeHash/mid_two/length'));
		$mStart3 	= cast::_int(Config::get('hash/makeHash/mid_three/start'));
		$mLength3 	= cast::_int(Config::get('hash/makeHash/mid_three/length'));
		$lStart1 	= cast::_int(Config::get('hash/makeHash/last_one/start'));
		$lLength1 	= cast::_int(Config::get('hash/makeHash/mid_one/length'));
		$lStart2 	= cast::_int(Config::get('hash/makeHash/mid_two/start'));
		$lLength2 	= cast::_int(Config::get('hash/makeHash/mid_two/length'));


		$shaTemp = hash('sha256', substr($temp, $iStart,  $iLength));

		$temp = substr($shaTemp,  $mStart1,  $mLength1) .
				substr($shaTemp, $mStart2, $mLength2 ) .
				substr($shaTemp, $mStart3, $mLength3 );
			
		$temp = self::saltedHash(substr($shaTemp,  $lStart1,  $lLength1)). $temp . self::saltedHash(substr($shaTemp, $lStart2, $lLength2));
		return $temp;
	}

/*
	* Salting Creation
	* @since 4.0.0	
	* @Param (String Length)
*/	
	public static function salt(int $length):string{
		return self::make(random_bytes($length));
	}

/*
	* Hash Salted
	* @since 4.0.0
	* @Param (String Input)
*/
	public static function saltedHash(string $input):string{
		$temp = substr($input, -1, 1);
		return  hash('sha256',md5(Config::get('hash/SaltedAlpha/'.$temp)));
		
	}

/*
	* Generate Random String
	* @since 4.0.0
	* @Param (Int Length)
*/
	public static function generateRandomString(int $length = 10):string {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		
		return substr(hash('sha256',$randomString),0,$length);
	}


	public static function randPass($abc,$length = 100) {

		$str = "";
		for ($i=0; $i<=$length; $i++) {
			$str .= $abc[rand(0, strlen($abc)-1)];
		};
		return $str;
	}

}
?>