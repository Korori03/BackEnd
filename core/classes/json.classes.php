<?php

/*
	* Json Class Set
	* @Version 1.0.0
	* Developed by: Ami (亜美) Denault
*/

/*
	* JSON Template Class
	* @since 1.0.0
*/
declare(strict_types=1);
class Json {

/*
	* Encode Jason
	* @since 1.0.0
	* @Param (Object/Array Json, Int Options)
*/	
    public static function encode(mixed $value,int $options = 0):string {
        $result = json_encode($value, $options);
        if($result)  
            return $result;
    }

/*
	* Decode Jason
	* @since 1.0.0
	* @Param (String Json, Bool Associate)
*/	
    public static function decode(string $json,bool $assoc = false) {
        $result = json_decode($json, $assoc);
	
        if($result) 
            return $result;

    }

/*
	* Url Json to XML
	* @since 1.0.0
	* @Param (String Url)
*/
	public function toXml (string $url):string {
		$fileContents= file_get_contents($url);
		$fileContents = str_replace(array("\n", "\r", "\t"), '', $fileContents);
		$fileContents = trim(str_replace('"', "'", $fileContents));
		$simpleXml = simplexml_load_string($fileContents);
		return self::encode($simpleXml);
	}

/*
	* Json to STD Array
	* @Since 4.0.0
	* @Param (String JSon)
*/	
	public static function toSTD(string $json):object {
		$array = self::decode($json,true);
		return arr::_toObject($array);
	}

/*
	* Json into MySQL
	* @Since 4.0.0
	* @Param (String Table,String JSon)
*/
	public static function toMySql(string $table,string $json){
		
		$stdAry = self::decode($json,true);
		$db = Database::getInstance();

		for($x = 1; $x < count($stdAry);$x++){
			
			$key = $stdAry[$x];
			$colLen = count($stdAry[$x]);
			$VALUES ='';
			
			for($z = 0;$z < $colLen;$z++)
				$VALUES .= ($z + 1 == $colLen?'?':'?,');
		
			$query = "INSERT INTO " . $table . " VALUES ($VALUES);";
			$db->prepare($query);
			
			$index = 1;
			foreach($key as $tblColName => $value){
				$db->bindParam($index,$value);
				$index++;
			}
			if($db->execute())
				continue;
			else{
				break;
				return $db->error();
			}
			$index = 1;
		}
		return true;	
	}

/*
	* Return Json
	* @Since 4.0.0
	* @Param (String Table,String JSon)
*/
	public static function returnJson(bool $status,$data):string{
		$response = array(
			'status' 	=> cast::_string($status),
			'message' 	=> $data
		);

		header("Content-type: application/json");
		return json::encode($response);
	}
}
?>