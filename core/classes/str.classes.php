<?php
/*
	* Strings Class Set
	* @Version 4.0.0
	* Developed by: Ami (亜美) Denault
*/
/*
	* Strings
	* @Since 4.0.0
*/
declare(strict_types=1);
class str{

/*
	* Phone Href Replacement
	* @Since 4.0.0
	* @Param (String phone)
*/		
	public static function _toPhoneHref(string $phone):string {
		return str_replace(array(".","-"),'',$phone);
	}

/*
	* String Options
	* @Since 4.0.0
	* @Param (String RawJSON)
*/	
	public static function string_options(string $options = 'javascript'):string{
		$obj = json::decode(Options::get($options));
		$return_options ='';
		
		foreach ($obj as $name => $value) {

			if($options =='javascript')
				$return_options.='<!--' . $name . '--><script src="'.$value.'" type="text/javascript"></script>';
			else if($options =='css')
				$return_options.='<!--' . $name . '--><link href="'.$value.'" media="screen" rel="stylesheet" type="text/css" />';
		}
		return $return_options;
	}
	
	public static function _APIItem($str){
		$string = str_replace(' ', '-', $str); // Replaces all spaces with hyphens.
		return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars
	}

/*
	* Formate String
	* @Since 2.1.4
	* @Param (String string)
*/
	public static function _format(string $string):string{
		return ucwords(self::_strtolower(trim($string)));
	}

/*
	* Format Money
	* @Since 2.1.4
	* @Param (String Number,Boolean Fraction,Boolean Symbol)
*/
	public static function _money(mixed $number,bool $fractional=false,bool $symbol=true):string {
		if ($fractional) {
			$number = sprintf('%.2f', $number);
		} 
		while (true) { 
			$replaced = preg_replace('/(-?\d+)(\d\d\d)/', '$1,$2', $number);
			if ($replaced != $number) { 
				$number = $replaced;
			} else {
				break; 
			}
		}
		return ($symbol?'$':''). $number;
	} 

/*
	* Strip Slashes
	* @Since 4.0.2
	* @Param (String string)
*/
	public static function _stripslashes (mixed $str):string {
		if (is_array($str))
			return array_map('stripslashes', $str);

		return stripslashes($str);
	}


/*
	* Add Slashes
	* @Since 4.0.2
	* @Param (String string)
*/
	public static function _addslashes (mixed $str):string {
		if (is_array($str)) 
			return array_map('addslashes', $str);
		
		return addslashes($str);
	}

/*
	* Trim String
	* @Since 4.0.2
	* @Param (String string,Char List)
*/
	public static function _trim (mixed $str,string $charlist = " \t\n\r\0\x0B"):string {
		if (is_array($str)) {
			foreach ($str as &$s)
				$s = trim($s, $charlist);

			return $str;
		}
		return trim($str, $charlist);
	}

/*
	* Left Trim String
	* @Since 4.0.2
	* @Param (String string,Char List)
*/
	public static function _ltrim (mixed $str,string $charlist = " \t\n\r\0\x0B"):string {
		if (is_array($str)) {
			foreach ($str as &$s) 
				$s = ltrim($s);
			
			return $str;
		}
		return ltrim($str, $charlist);
	}

/*
	* Right Trim String
	* @Since 4.0.2
	* @Param (String string,Char List)
*/
	public static function _rtrim (mixed$str,string $charlist = " \t\n\r\0\x0B"):string {
		if (is_array($str)) {
			foreach ($str as &$s) 
				$s = rtrim($s, $charlist);
			
			return $str;
		}
		return rtrim($str, $charlist);
	}

/*
	* Substring String
	* @Since 4.0.2
	* @Param (String string,Int Start,int Length)
*/
	public static function _substr (mixed $string,int $start,mixed $length = null):string {
		if (is_array($string)) {
			foreach ($string as &$s)
				$s = self::_substr($s, $start, $length);
			
			return $string;
		}
		if ($length) 
			return substr($string, $start, $length);

		return substr($string, $start);
	}

/*
	* To Lower
	* @Since 4.0.2
	* @Param (String string)
*/	
	public static function _strtolower (mixed $string):string {
		$string = cast::_string($string);
		if (is_array($string))
			return array_map('strtolower', $string);

		return strtolower($string);
	}

/*
	* To Upper
	* @Since 4.0.2
	* @Param (String string)
*/
	public static function _strtoupper (mixed $string):string {
		if (is_array($string)) 
			return array_map('strtoupper', $string);
		
		return strtoupper($string);
	}

/*
	* Preg Match
	* @Since 4.0.2
	* @Param (String Pattern,String Input,Reference Matches, PREG_ OPTION, Int Offset )
*/
	public static function _preg_match (string $pattern,string $subject,mixed &$matches = null,int $flags = 0,int $offset = 0):bool {
		if (strpos($pattern, '/') === false && strpos($pattern, '#') === false)
			return false;

		$pattern = self::_trim($pattern);
		return preg_match($pattern, $subject, $matches, $flags, $offset);
	}

/*
	* Preg Replace
	* @Since 4.0.2
	* @Param (String Pattern,String Input, String/Array for Search,Int Limit, Int Number of Replacements Done)
*/
	public static function _preg_replace (string $pattern,string $replacement,string $subject,int $limit = -1,mixed &$count = null):string {
		if (strpos($pattern, '/') === false && strpos($pattern, '#') === false)
			return false;

		$pattern = trim($pattern);
		return preg_replace($pattern, $replacement, $subject, $limit, $count);
	}

/*
	* Truncate String
	* @Since 4.0.2
	* @Param (String text, Length of Returned Text, String to Concat with, Bool Exact Match, Bool Check for HTML)
*/		
	public static function _truncate (string $text,int $length = 1024,string  $ending = '...',bool $exact = false,bool $considerHtml = true):string {
		$open_tags = [];
		if ($considerHtml) {
			if (strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
				return $text;
			}
			preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);
			$total_length = mb_strlen($ending);
			$truncate     = '';
			foreach ($lines as $line_matchings) {
				if (!empty($line_matchings[1])) {
					if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|col|frame|link|meta|param)(\s.+?)?)>$/is', $line_matchings[1])) {
					} else if (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings)) {
						$pos = array_search($tag_matchings[1], $open_tags);
						if ($pos !== false) {
							unset($open_tags[$pos]);
						}
					} else if (preg_match('/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings)) {
						array_unshift($open_tags, mb_strtolower($tag_matchings[1]));
					}
					$truncate .= $line_matchings[1];
				}
				$content_length = mb_strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));
				if ($total_length + $content_length > $length) {
					$left            = $length - $total_length;
					$entities_length = 0;
					if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)) {
						foreach ($entities[0] as $entity) {
							if ($entity[1] + 1 - $entities_length <= $left) {
								$left--;
								$entities_length += mb_strlen($entity[0]);
							} else {
								break;
							}
						}
					}
					$truncate .= mb_substr($line_matchings[2], 0, $left + $entities_length);
					break;
				} else {
					$truncate .= $line_matchings[2];
					$total_length += $content_length;
				}
				if ($total_length >= $length) {
					break;
				}
			}
		} else {
			if (mb_strlen($text) <= $length)
				return $text;

				$truncate = mb_substr($text, 0, $length - mb_strlen($ending));
		}
		if (!$exact) {
			$spacepos = mb_strrpos($truncate, ' ');
			if (isset($spacepos)) {
				$truncate = mb_substr($truncate, 0, $spacepos);
			}
		}
		$truncate .= $ending;
		if ($considerHtml) {
			foreach ($open_tags as $tag)
				$truncate .= "</$tag>";
		}
		return $truncate;
	}

/*
	* Convert from Ascii
	* @Since 2.1.4
	* @Param (String string)
*/
	public static function _toAscii(string $string): string{
		$ascii = array('@:#([0-9]{2,3}):@' => '&#$1;','@:amp:@' => '&amp;','@:quot:@'=>'\'');
		foreach ($ascii as $search => $replace)  								
			$string = preg_replace($search, $replace, $string);
			
		return $string;
	}

/*
	* File Size Convert
	* @Since 2.1.4
	* @Param (String Bytes)
*/
	public static function _FileSize(string $bytes): string
	{
		$bytes = floatval($bytes);
			$arBytes = array(
				0 => array(
					"UNIT" => "TB",
					"VALUE" => pow(1024, 4)
				),
				1 => array(
					"UNIT" => "GB",
					"VALUE" => pow(1024, 3)
				),
				2 => array(
					"UNIT" => "MB",
					"VALUE" => pow(1024, 2)
				),
				3 => array(
					"UNIT" => "KB",
					"VALUE" => 1024
				),
				4 => array(
					"UNIT" => "B",
					"VALUE" => 1
				),
			);

		foreach($arBytes as $arItem)
		{
			if($bytes >= $arItem["VALUE"])
			{
				$result = $bytes / $arItem["VALUE"];
				$result = str_replace(".", "," , strval(round($result, 2)))." ".$arItem["UNIT"];
				break;
			}
		}
		return $result;
	}

/*
	* Between
	* @Since 2.1.4
	* @Param (String left,String right, String string)
*/
	public static function _between(string $left,string  $right,string  $string): array
    {
        preg_match_all('/' . preg_quote($left, '/') . '(.*?)' . preg_quote($right, '/') . '/s', $string, $matches);
        return array_map('trim', $matches[1]);
    }

/*
	* Insert
	* @Since 2.1.4
	* @Param (Array KeyValues, String string)
*/
	public static function _insert(mixed $keyValue,string $string)
    {
        if (arr::_isAssoc($keyValue)) {
            foreach ($keyValue as $search => $replace) {
                $string = str_replace($search, $replace, $string);
            }
        }

        return $string;
    }

	public static function _MetaDescription(string $string, int $limit = 50): string
	{
		return self::_limitWords(strip_tags(str_replace(array('\'', '"'), '', $string)),$limit);
	}
/*
	* Limit Words
	* @Since 2.1.4
	* @Param (String string, Int Limit,String Ending)
*/
	public static function _limitWords(string $string,int  $limit = 10,string $end = '...'): string
    {
        $arrayWords = explode(' ', $string);

        if (sizeof($arrayWords) <= $limit)
            return $string;

        return implode(' ', array_slice($arrayWords, 0, $limit)) . $end;
    }

/*
	* Check if String Contains
	* @Since 2.1.4
	* @Param (Array Needle, String haystack)
*/
	public static function _contains(mixed $needle,string $haystack): bool
    {
        foreach (cast::_array($needle) as $ndl) {
            if (strpos($haystack, $ndl) !== false)
                return true;
        }

        return false;
    }

/*
	* Strip Spaces
	* @Since 2.1.4
	* @Param (String string)
*/
	public static function _stripSpace(string $string): string
    {
        return preg_replace('/\s+/', '', $string);
    }

/*
	* Pad Zero
	* @Since 2.1.4
	* @Param (String number,Int Length)
*/
	public static function _zeroPad(string $number, int $length): string
    {
        return str_pad($number, $length, '0', STR_PAD_LEFT);
    }
}
