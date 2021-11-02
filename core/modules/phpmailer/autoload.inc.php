<?php
$files = array('Exception.php', 'PHPMailer.php', 'SMTP.php');


foreach ($files as $required) {
    $composerAutoloadFile = __DIR__ .  '/'.$required;
    require_once $composerAutoloadFile;
}
