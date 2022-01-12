<?php
$GLOBALS['config']=array(

	'mysql'=>array(
		'host'=>'host',
		'username'=>'username',
		'password'=>'password',
		'db'=>'Database',
		'use'=>false
	),
	'options'=>	array(
		'timezone' => 'America/Chicago',
		'template' => 'smooth'
	),
	'api'=>array(
		'key'=>'50 char hash here',
		'bypassKey'=>'50 char hash here',
	),
	'domains'=>
	'{
	}',
	'remember'=>array(
		'cookie_name'=>'hash',
		'cookie_expiry'=>604800
	),
	'session'=>array(
		'session_name'=>'user'
	),
	'hash'=>array(
		'blowfish'=>'XXXXXXX',
		'makeHash'=>array(
			'initial'=>array('start'=>{Number},'length'=>{Number}),
			'mid_one'=>array('start'=>{Number},'length'=>{Number}),
			'mid_two'=>array('start'=>{Number},'length'=>{Number}),
			'mid_three'=>array('start'=>{Number},'length'=>{Number}),
			'last_one'=>array('start'=>{Number},'length'=>{Number}),
			'last_two'=>array('start'=>{Number},'length'=>{Number}),
		),
		'auth_key'=>'40 char hash here',
		'salt'=>'40 char hash here',
		'saltedAlpha'=> array(
			'a' => '15 char hash here',
			'b' => '15 char hash here',
			'c' => '15 char hash here',
			'd' => '15 char hash here',
			'e' => '15 char hash here',
			'f' => '15 char hash here',
			'g' => '15 char hash here',
			'h' => '15 char hash here',
			'i' => '15 char hash here',
			'j' => '15 char hash here',
			'k' => '15 char hash here',
			'l' => '15 char hash here',
			'm' => '15 char hash here',
			'n' => '15 char hash here',
			'o' => '15 char hash here',
			'p' => '15 char hash here',
			'q' => '15 char hash here',
			'r' => '15 char hash here',
			's' => '15 char hash here',
			't' => '15 char hash here',
			'u' => '15 char hash here',
			'v' => '15 char hash here',
			'w' => '15 char hash here',
			'x' => '15 char hash here',
			'y' => '15 char hash here',
			'z' => '15 char hash here',
			'1' => '15 char hash here',
			'2' => '15 char hash here',
			'3' => '15 char hash here',
			'4' => '15 char hash here',
			'5' => '15 char hash here',
			'6' => '15 char hash here',
			'7' => '15 char hash here',
			'8' => '15 char hash here',
			'9' => '15 char hash here',
			'0' => '15 char hash here'
		)
	),
	'time'=>array(
		'HOUR_IN_SECONDS'=>3600,
		'DAY_IN_SECONDS'=>86400,
		'WEEK_IN_SECONDS'=>604800
	),
	'table'=>array(
		'users'=>'XXXXXXXXX'
	),
	'modules' =>array(
		'dompdf'=>'autoload.inc.php',
		'phpmailer'=>'autoload.inc.php',
		'phpqrcode'=>'autoload.inc.php'
	),
	'settings'=>array(
		'word_limit'=>60
	),
	'api'=>array(
		'username'=>'XXXXXXXXXX',
		'password'=>'XXXXXXXXXXXXXX'
	),
	'emailer'=>array(
		'sender'=>array(
			'name'=>'XXXXXXXXXXXXXXXXXXXX',
			'email'=>'XXXXX@XXXXXXXXX.XXXXX'
		),
		'job'=>array(
			'name'=>'Houston County',
			'email'=>'XXXXXXXXXXX@XXXXXXXXXXX.XXXX'
		),
		'roadbridge'=>array(
			'name'=>'XXXXXXXXXXXXXX',
			'email'=>'XXXXXX@XXXXXXXXXXX.XXXXXXXXX'
		)
		'from'=>'XXXXXXXXXXXXXXXXXXXXXXXXX'
		),
	'zoom'=>array(
		'api_key' => '',
		'secret_key' => '',
	),
	'youtube'=>array(
		'api_key' => '',
		'channel_id' => '',
	)
);
?>