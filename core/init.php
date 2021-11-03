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
/*
	* Start Session
*/

session_start();


ini_set("display_errors", 1);
ini_set("track_errors", 1);
ini_set("html_errors", 1);
error_reporting(E_ALL);

ini_set('zlib.output_compression_level', 9);	
ob_start("ob_gzhandler");

/*
	* Global Configr
*/

require 'config.php';


/*
	* Autoload Classes
	* @ Version 1.0.5
	* @ Since 4.0.0
	* @ Param (String Classname)
*/	
spl_autoload_register(function($class_name){
	$directorys = array(
			'libs/objects',
            'libs',
            'core/classes',
			'core'
    );
	foreach($directorys as $directory)
    {
    	$dir = explode('/',$directory);
		$required = $directory.'/'.strtolower($class_name) . '.' .$dir[count($dir) -1]. '.php';
        if(file_exists($required))
			 require_once($required);
	}
});


/*
	* Get Content Mangement Options for Webpage
	* @ Version 1.0.0
	* @ Since 4.0.1
*/
if(Config::get('mysql/use')){
	$dboptions = Database::getInstance()->get(Config::get('table/options'));
	$GLOBALS['options'] = array();
	foreach($dboptions->results() as $option){
		
		if(str::_strtolower(substr($option->autoload,0,1)) =='y'){
			$option_name = $option->option_name;
			$option_value = $option->option_value;
			$GLOBALS['options'][$option_name] = $option_value;
		}
	
	}
}
else{
	$GLOBALS['options']['timezone'] = Config::get('options/timezone');
	$GLOBALS['options']['template'] = Config::get('options/template');
}

	/*
	* Module Loader
	* @ Version 1.0.0
	* @ Since 4.0.2
*/
$modules = array(
	//'dompdf'=>'autoload.inc.php',
	//'phpmailer'=>'autoload.inc.php'
);
foreach($modules as $module=>$loader)
{
	if(file_exists("core/modules/{$module}/{$loader}"))
		require_once("core/modules/{$module}/{$loader}");
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
