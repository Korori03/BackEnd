<?php

/*
	* Cast Class Set
	* @Version 1.0.0
	* Developed by: Ami (亜美) Denault
*/

declare(strict_types=1);
class cast
{

    /*
	* Cast Int
	* @since 4.5.1
	* @param (Int Number)
*/
    public static function _int(mixed $in): int
    {
        if (is_array($in)) {
            return array_map(
                function ($in) {
                    return (int)$in;
                },
                $in
            );
        }
        return (int)$in;
    }

    /*
* Cast Float
* @since 4.5.1
* @param (Float Number)
*/
    public static function _float(mixed $in): float
    {
        if (is_array($in)) {
            return array_map(
                function ($in) {
                    return (float)$in;
                },
                $in
            );
        }
        return (float)$in;
    }

    /*
* Cast String
* @since 4.1.5
* @param (String Input)
*/
    public static function _string(mixed $in): string
    {
        if (!is_array($in)) {
            return (string)$in;
        }
        return array_map(
            function ($in) {
                return (string)$in;
            },
            $in
        );
    }

    /*
* Cast Array
* @since 4.1.5
* @param (Array Input)
*/
    public static function _array(mixed $in): array
    {
        if (!is_array($in)) {
            return (array)$in;
        }
        return array_map(
            function ($in) {
                return (array)$in;
            },
            $in
        );
    }

    /*
* Validate if Item is Not Null
* @since 4.5.1
* @param (String Input)
*/
    public static function _isNull(mixed $in)
    {
        if (is_array($in)) {
            foreach ($in as &$val)
                $val = self::_isNull($val);
        } else
            $in = str_replace(chr(0), '', cast::_string($in));

        return $in;
    }

    /*
* Convert to Bool
* @since 4.5.1
* @param (String Input)
*/
    public static function _bool(mixed $in)
    {
        return filter::bool(cast::_string($in));
    }

    /*
	* Check if Base 64
	* @since 4.5.1
	* @Param (String Value)
*/
    public static function _isBase64(string $value): string
    {
        return self::_string(preg_replace('#[^A-Z0-9\/+=]#i', '', $value));
    }
}
