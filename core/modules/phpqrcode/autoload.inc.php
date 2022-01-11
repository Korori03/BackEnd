<?php
$files = array('qrlib.php');


foreach ($files as $required) {
    $composerAutoloadFile = __DIR__ .  '/'.$required;
    require_once $composerAutoloadFile;
}
