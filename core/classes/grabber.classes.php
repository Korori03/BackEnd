<?php
/*
	* Grabber Class Set
	* @Version 1.0.0
	* Developed by: Ami (亜美) Denault
*/
/*
	* Grabber
	* @Since 4.0.1
*/
declare(strict_types=1);
class Grabber{


	public static function jsonData(string $url):object{
		$json = file_get_contents($url);
		return json::decode($json);
	}
/*
	* Curl Function
	* @Since 1.1.4
	* @Param (Url String)
*/
	public static function fromURL(string $url):string{
		$curl = curl_init();
		$userAgent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)'; 

		curl_setopt($curl,CURLOPT_URL,$url); 
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,TRUE);
		curl_setopt($curl,CURLOPT_CONNECTTIMEOUT,5); 

		curl_setopt($curl, CURLOPT_USERAGENT, $userAgent);
		curl_setopt($curl, CURLOPT_FAILONERROR, TRUE); 
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE); 
		curl_setopt($curl, CURLOPT_AUTOREFERER, TRUE); 
		curl_setopt($curl, CURLOPT_TIMEOUT, 10); 

		$contents = curl_exec($curl);
		
		@curl_close($curl);	
		return $contents;
	}

/*
	* Html Information from URL
	* @Since 1.0.0
	* @Param (Url String,String Dom, String Object Class in html)
*/	
	public static function fromUrlData(string $url,string $DOM,string $class):array{

		$str = self::fromURL($url);
		$doc = new DOMDocument();
		$doc->loadHTML($str);    
		$selector = new DOMXPath($doc);

		$result = $selector->query('//' . $DOM . '[@class="'.$class.'"]');

		$objects = array();
		foreach($result as $node) {
			array_push($objects,$node->getAttribute('html'));
		}

		return $objects;
	}
};
?>