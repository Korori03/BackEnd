<?php
/*
	* Setup Date Class
	* @Version 1.0.0
	* Developed by: Ami (亜美) Denault
*/

declare(strict_types=1);
class Date
{

    public const SQL_FORMAT = 'Y-m-d H:i:s';
    public const ZOOM_FORMAT = 'm/d/Y h:i A';
    public const HUMAN_FORMAT = 'm-d-Y H:i:s';

    /*
	* Date To Custom Format
	* @Since 1.0.0
	* @Param (String Date,String TimeZone)
*/
    public static function setup(string $time = null,string $timeZone = null): DateTime
    {
        $timeZone = self::timezone($timeZone);

        if ($time instanceof DateTime)
            return $time->setTimezone($timeZone);


        $dateTime = new DateTime('@' . self::_toTimeStamp($time));
        $dateTime->setTimezone($timeZone);

        return $dateTime;
    }

    /*
	* Date To Human Format
	* @Since 1.0.0
	* @Param (String Date)
*/
    public static function _human(mixed $date): string
    {
        return self::setup($date)->format(self::HUMAN_FORMAT);
    }

    /*
	* Date To Formal Human Format
	* @Since 1.0.0
	* @Param (String Date)
*/
    public static function _formal(mixed $date): string
    {
        return self::setup($date)->format('jS \of F Y');
    }

    /*
	* Date To SQL Format
	* @Since 1.0.0
	* @Param (String Date)
*/
    public static function _sql(mixed $date): string
    {
        return self::setup($date)->format(self::SQL_FORMAT);
    }

    /*
	* Date To Custom Format
	* @Since 1.0.0
	* @Param (Date,String format)
*/
    public static function _custom(mixed $date,string $format = 'm-d-Y H:i:s'): string
    {
        return self::setup($date)->format($format);
    }

    /* 
	* Date To Zoom Format
	* @Since 1.0.0
	* @Param (String Date)
*/
    public static function _Zoom(mixed $date): string
    {
        return self::setup($date)->format(self::ZOOM_FORMAT);
    }

    /*
	* Date To Custom Format
	* @Since 1.0.0
	* @Param (String Date,Bool DefaultFormat)
*/
    public static function _toTimeStamp(string $time = null, bool $currentIsDefault = true): int
    {
        if ($time instanceof DateTime)
            return (int)$time->format('U');

        if (null !== $time)
            $time = is_numeric($time) ? (int)$time : (int)strtotime($time);

        if (!$time)
            $time = $currentIsDefault ? time() : 0;

        return $time;
    }

    /*
	* TimeZone Set
	* @Since 1.0.0
	* @Param (String TimeZone)
*/
    public static function timezone(string $timezone = null): DateTimeZone
    {
        if ($timezone instanceof DateTimeZone) {
            return $timezone;
        }

        $timezone = $timezone ?: date_default_timezone_get();

        return new DateTimeZone($timezone);
    }

    /*
	* Is TimeStamp
	* @Since 1.0.0
	* @Param (?String TimeZone)
*/
    public static function is(?string $date): bool
    {
        return strtotime(cast::_string($date)) > 0;
    }

    /*
	* Convert Numerical to Month
	* @Since 2.2.8
	* @Param (String)
*/
    public static function toMonthName(int $intMonth): string
    {
        return self::setup('2020-' . $intMonth . -'01')->format('n');
    }
}
