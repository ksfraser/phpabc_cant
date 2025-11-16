<?php
require_once 'vendor/autoload.php';

use Ksfraser\PhpabcCanntaireachd\CanntGenerator;
use Ksfraser\PhpabcCanntaireachd\TokenDictionary;

$dict = new TokenDictionary();
$generator = new CanntGenerator($dict);
$testInput = '{g}A3B {g}ce3';
echo "Testing tokenizeAndConvert with: '$testInput'\n";
$result = $generator->generateForNotes($testInput);
echo "Result: '$result'\n";
?>