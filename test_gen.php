<?php
require 'vendor/autoload.php';
$gen = new Ksfraser\PhpabcCanntaireachd\CanntGenerator();
echo 'A -> ' . $gen->generateForNotes('A') . "\n";
echo 'B -> ' . $gen->generateForNotes('B') . "\n";
echo 'C -> ' . $gen->generateForNotes('C') . "\n";
?>
