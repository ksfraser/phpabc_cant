<?php
require 'vendor/autoload.php';

use Ksfraser\PhpabcCanntaireachd\AbcTimingValidator;

$validator = new AbcTimingValidator();

$abc = [
    "X:1",
    "T:Test Tune",
    "M:4/4",
    "L:1/4",
    "V:Bagpipes",
    "|A B C D|",
    "|A B C|",
    "|A B|"
];

$result = $validator->validate($abc);

echo "Processed lines:\n";
foreach ($result['lines'] as $line) {
    echo $line . "\n";
}
echo "\nErrors:\n";
foreach ($result['errors'] as $error) {
    echo $error . "\n";
}
?>
