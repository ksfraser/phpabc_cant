<?php
require 'vendor/autoload.php';
echo 'Autoload loaded' . PHP_EOL;
$ref = new ReflectionClass('Ksfraser\PhpabcCanntaireachd\CanntGenerator');
echo 'File: ' . $ref->getFileName() . PHP_EOL;
?>