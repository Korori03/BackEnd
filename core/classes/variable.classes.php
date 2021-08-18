<?php

/*
	* Variables
	* @Version 1.0.0
	* Developed by: Ami (亜美) Denault
*/
/*
	* Setup Variables Class
	* @ Version 1.0.1
	* @ Since 4.5.1
*/

declare(strict_types=1);

class Variable
{

/*
    * Check if Number is Between
    * @ Since 4.5.1
    * @param (Float Number,Float Min, Float Max)
*/
    public static function isIn(float $number, float $min, float $max): bool
    {
        return ($number >= $min && $number <= $max);
    }


/*
	* Check if Number is Even
	* @ Since 4.5.1
	* @param (Int Number)
*/
    public static function isEven(int $number): bool
    {
        return ($number % 2 === 0);
    }

/*
	* Check if Number is Negative
	* @ Since 4.5.1
	* @param (Float Number)
*/
    public static function isNegative(float $number): bool
    {
        return ($number < 0);
    }

/*
	* Check if Odd
	* @ Since 4.5.1
	* @param (Int Number)
*/
    public static function isOdd(int $number): bool
    {
        return !self::isEven($number);
    }

/*
	* Check if Number is Positive
	* @ Since 4.5.1
	* @param (Float Number)
*/
    public static function isPositive(float $number): bool
    {
        return $number > 0;
    }


/*
	* Relative Percent
	* @ Since 4.5.1
	* @param (Float Number,Float Current)
*/
    public static function relativePercent(float $normal, float $current): string
    {
        if (!$normal || $normal === $current)
            return '100';

        $normal = abs($normal);
        $percent = round($current / $normal * 100);

        return number_format($percent, 0, '.', ' ');
    }
}