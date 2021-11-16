<?php
/*
	Initize Set
	Developed by: Ami Denault
	Coded on: 24th June 2014

*/
/*	@Updates
	*9th May 2017
	-Added Global Options
	-Set Timezone from Global Options

*/
/*	@Updates
	*16th November 2021
	-Added Modules Option
	-Added Variable File for Global Defintions
*/
/*
	* Start Session
*/

declare(strict_types=1);
session_start();

/*
	* Global Config
*/

require 'config.php';

/*
	* Global Variables
*/

require 'variables.php';

ini_set("display_errors", (string)DISPLAY_ERRORS);
ini_set("track_errors", (string)TRACK_ERRORS);
ini_set("html_errors", (string)HTML_ERRORS);
error_reporting(E_ALL);

ini_set('zlib.output_compression_level', (string)COMPRESSION_LEVEL);
ob_start("ob_gzhandler");


/*
	* Autoload Classes
	* @ Version 1.0.5
	* @ Since 4.0.0
	* @ Param (String Classname)
*/	
spl_autoload_register(function($class_name){
	$directorys = CLASS_DIRECTORY;
	foreach($directorys as $directory)
    {
    	$dir = explode(DIRECTORY_SEPARATOR,$directory);
		$required = $directory.DIRECTORY_SEPARATOR.strtolower($class_name) . '.' .$dir[count($dir) -1]. '.php';
        if(file_exists($required))
			 require_once($required);
	}
});

/*
	* Fix Document Root Path
	* @ Version 1.0.0
	* @ Since 4.0.2
*/
	if ((!isset($_SERVER['DOCUMENT_ROOT'])) OR (empty($_SERVER['DOCUMENT_ROOT']))) {
		if(isset($_SERVER['SCRIPT_FILENAME']))
			$_SERVER['DOCUMENT_ROOT'] = str_replace( '\\', DIRECTORY_SEPARATOR, substr($_SERVER['SCRIPT_FILENAME'], 0, 0-strlen($_SERVER['PHP_SELF'])));
		elseif(isset($_SERVER['PATH_TRANSLATED']))
			$_SERVER['DOCUMENT_ROOT'] = str_replace( '\\', DIRECTORY_SEPARATOR, substr(str_replace('\\\\', '\\', $_SERVER['PATH_TRANSLATED']), 0, 0-strlen($_SERVER['PHP_SELF'])));
		else
			$_SERVER['DOCUMENT_ROOT'] = DIRECTORY_SEPARATOR;
	}
	$_SERVER['DOCUMENT_ROOT'] = str_replace('//', DIRECTORY_SEPARATOR, $_SERVER['DOCUMENT_ROOT']);
	if (substr($_SERVER['DOCUMENT_ROOT'], -1) != DIRECTORY_SEPARATOR)
		$_SERVER['DOCUMENT_ROOT'] .= DIRECTORY_SEPARATOR;

/*
	* Module Loader
	* @ Version 1.0.0
	* @ Since 4.0.2
*/
	$modules = $GLOBALS['config']['modules'];

	foreach($modules as $module=>$loader)
    {
		if(file_exists(MODULES_DIR ."{$loader}"))
			require_once(MODULES_DIR ."{$module}/{$loader}");
	}


/*
	* Get Content Mangement Options for Webpage
	* @ Version 1.0.0
	* @ Since 4.0.1
*/
	$dboptions = Database::getInstance()->get(Config::get('table/options'));
	$GLOBALS['options'] = array();
	foreach($dboptions->results() as $option){
		if(filter::bool($option->autoload))
			$GLOBALS['options'][$option->option_name] = $option->option_value;
	}
/*
	* Get Cookie Instances for Remember Me
	* @ Version 1.0.3
	* @ Since 4.0.0
*/	
if(Cookie::exists(Config::get('remember/cookie_name')) && !Session::exists(Config::get('session/session_name'))){
	$hash = Cookie::get(Config::get('remember/cookie_name'));
	$hashCheck = DataBase::getInstance()->get('users',array('session','=',$hash));
	if($hashCheck->count()){
		$user = new User($hashCheck->first()->id);
		$user->login();
	}
}

/*
	* Set Time Zone from Options in Mysql
	* @ Version 1.0.5
	* @ Since 4.0.0
*/
if(function_exists('date_default_timezone_set'))
	date_default_timezone_set(Options::get('timezone'));
else
   putenv("TZ=" . Options::get('timezone'));


?>
