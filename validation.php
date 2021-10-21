<?php

$workDir = '/etc/encryption365';
$host = $_SERVER['HTTP_HOST'];
$content = file_get_contents($workDir . '/validation.txt');
echo $content;

?>
