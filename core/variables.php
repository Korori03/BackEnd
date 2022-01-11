<?php
/*
	Global Variable Set
	Developed by: Ami Denault
	Coded on: 15th Novemeber 2021
*/
const _VERSION = '4.1.5';
const DISPLAY_ERRORS = 0;
const TRACK_ERRORS = 0;
const HTML_ERRORS = 0;
const COMPRESSION_LEVEL = 9;
const CLASS_DIRECTORY = array(
			'libs/objects',
            'libs',
            'core/classes',
			'core'
    );


if (!defined('PHP_QUOTE'))
    DEFINE("PHP_QUOTE", "\"");

if (!defined('PHP_DIR')) 
    define('PHP_DIR', dirname(__FILE__) . DIRECTORY_SEPARATOR);

if (!defined('MODULES_DIR'))
    define('MODULES_DIR',  '/core/modules/');


?>