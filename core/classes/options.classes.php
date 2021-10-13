<?php
/*
	* Setup Options Class
	* @since 4.0.1
*/

class Options
{

	/*
	* Get Config
	* @Version 1.0.0
	* @since 4.0.1
	* @Param (String Path)
*/
	public static function get(string $path = null)
	{
		if ($path) {
			$config = $GLOBALS['options'];
			$path = explode('/', $path);

			foreach ($path as $bit) {
				if (isset($config[$bit]))
					$config = $config[$bit];
			}
			return is_array($config) ? '' : $config;
		}
		return '';
	}
}
