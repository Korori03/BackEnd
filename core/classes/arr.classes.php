<?php
/*
	* Array Class Set
	* @Version 1.0.2
	* Developed by: Ami (亜美) Denault
*/
/*
	* Arrays
	* @Since 4.0.0
*/

declare(strict_types=1);
class arr
{

	/*
	* Convert Numerical to Month
	* @Since 4.0.0
	* @Param (Array, String, String)
*/
	public static function _sort(array $array,string $on,int $order = SORT_ASC): array
	{
		$new_array = array();
		$sortable_array = array();

		if (count($array) > 0) {
			foreach ($array as $k => $v) {
				if (is_array($v)) {
					foreach ($v as $k2 => $v2) {
						if ($k2 == $on) {
							$sortable_array[$k] = $v2;
						}
					}
				} else {
					$sortable_array[$k] = $v;
				}
			}

			switch ($order) {
				case SORT_ASC:
					asort($sortable_array);
					break;
				case SORT_DESC:
					arsort($sortable_array);
					break;
			}

			foreach ($sortable_array as $k => $v) {
				$new_array[$k] = $array[$k];
			}
		}

		return $new_array;
	}

	/*
	* Array to Object Array
	* @Since 4.0.0
	* @Param (Array Object)
*/
	public static function _toObject(mixed $array): object | array
	{
		if (is_array($array)) {
			$object = new stdClass();
			foreach ($array as $key => $value)
				$object->$key = self::_toObject($value);

			return $object;
		} else
			return [];
	}

	/*
	* Array Sort
	* @Since 4.0.0
	* @Param (Array Object,String Key, Bool Sorting)
*/
	public static function _sksort(array &$array,string $subkey = "id",bool $sort_ascending = false): array
	{

		$temp_array = array();
		if (count($array))
			$temp_array[key($array)] = array_shift($array);

		foreach ($array as $key => $val) {
			$offset = 0;
			$found = false;
			foreach ($temp_array as $tmp_key => $tmp_val) {
				if (!$found && str::_strtolower($val[$subkey]) > str::_strtolower($tmp_val[$subkey])) {
					$temp_array = array_merge(
						(array)array_slice($temp_array, 0, $offset),
						array($key => $val),
						array_slice($temp_array, $offset)
					);
					$found = true;
				}
				$offset++;
			}
			if (!$found) $temp_array = array_merge($temp_array, array($key => $val));
		}

		if ($sort_ascending) $array = array_reverse($temp_array);

		else $array = $temp_array;

		return $array;
	}

/*
	* Array Replace
	* @Since 4.0.0
	* @Param (Array Object,Array Object, String)
*/
	public static function _replace(array $ary1,array $ary2,string $string): string
	{

		if (count($ary1) !== count($ary2))
			return '';

		for ($x = 0; $x < count($ary1); $x++)
			$string = str_replace($ary1[$x], $ary2[$x], $string);

		return $string;
	}

/*
	* Get Last Item in Array
	* @Since 4.1.5
	* @Param (Array Object)
*/
	public static function _last(array $array): array
	{
		return $array[array_keys($array)[sizeof($array) - 1]];
	}

/*
	* Get First Item in Array
	* @Since 4.1.5
	* @Param (Array Object)
*/
	public static function _first(array $array): array
	{
		return $array[array_keys($array)[0]];
	}

/*
	* Make Sure Array has Unique
	* @Since 4.1.5
	* @Param (Array Object,Bool Keys)
*/
	public static function _unique(array $array, bool $keepKeys = false): array
	{
		if ($keepKeys)
			$array = array_unique($array);
		else
			$array = array_keys(array_flip($array));


		return $array;
	}

/*
	* Find Item with Key Value
	* @Since 4.1.5
	* @Param (String Key,Array Array,Bool ReturnValue)
*/
	public static function _key(string $key, array $array, bool $returnValue = false): array
	{
		$isExists = array_key_exists(cast::_string($key), $array);

		if ($returnValue) {
			if ($isExists)
				return $array[$key];

			return array();
		}

		return $isExists;
	}

/*
	* First Key in Array
	* @Since 4.1.5
	* @Param (Array Array)
*/
	public static function firstKey(array $array): string
	{
		return key(self::_first($array));
	}

/*
	* Last Key in Array
	* @Since 4.1.5
	* @Param (Array Array)
*/
	public static function lastKey(array $array): string
	{
		return key(self::_last($array));
	}

/*
	* Searc Array with Keys
	* @Since 4.1.5
	* @Param (Array Array,String Search,?String Field)
*/
	public static function search(array $array, $search, ?string $field = null): string
	{
		$search = cast::_string($search);
		foreach ($array as $key => $element) {

			$key = cast::_string($key);

			if ($field) {
				if (is_object($element) && $element->{$field} === $search)
					return $key;

				if (is_array($element) && $element[$field] === $search)
					return $key;

				if (is_scalar($element) && $element === $search)
					return $key;

			} elseif (is_object($element)) {
				$element = (array)$element;
				if (in_array($search, $element, false))
					return $key;

			} elseif (is_array($element) && in_array($search, $element, false))
				return $key;
			 elseif (is_scalar($element) && $element === $search)
				return $key;

		}

		return 'Unable to Find';
	}

/*
	* Group By Key
	* @Since 4.1.5
	* @Param (Array Array,String Key)
*/
	public static function groupByKey(array $arrayList, string $key = 'id'): array
	{
		$result = [];

		foreach ($arrayList as $item) {
			if (is_object($item)) {
				if (isset($item->{$key}))
					$result[$item->{$key}][] = $item;
			} elseif (is_array($item)) {
				if (array_key_exists($key, $item))
					$result[$item[$key]][] = $item;
			}
		}

		return $result;
	}

/*
	* Is Associate Array
	* @Since 4.1.5
	* @Param (Array Array)
*/
	public static function _isAssoc(array $arr): bool
	{
		if (array() === $arr) return false;
		return array_keys($arr) !== range(0, count($arr) - 1);
	}

/*
	* Find in Array
	* @Since 4.1.5
	* @Param (String Value,Array Array,Bool ReturnKey)
*/
	public static function in(string $value, array $array, bool $returnKey = false)
	{
		$inArray = in_array($value, $array, true);

		if ($returnKey) {
			if ($inArray) 
				return array_search($value, $array, true);

			return '';
		}

		return $inArray;
	}
}
