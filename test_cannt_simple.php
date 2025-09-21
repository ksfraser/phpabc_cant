<?php
require 'vendor/autoload.php';
require_once 'src/Ksfraser/PhpabcCanntaireachd/CanntGenerator.php';

$cg = new Ksfraser\PhpabcCanntaireachd\CanntGenerator();

$test = '{g}A3B {g}ce3 TIMING|';
echo "Test for actual line: '$test'" . PHP_EOL;
echo $cg->generateForNotes($test) . PHP_EOL;

$test = '[V:Bagpipes]{g}A3B {g}ce3 TIMING|';
echo "Test for bagpipes line: '$test'" . PHP_EOL;
echo $cg->generateForNotes($test) . PHP_EOL;
?>
