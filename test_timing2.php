<?php
require_once 'vendor/autoload.php';
use Ksfraser\PhpabcCanntaireachd\AbcTimingValidator;

$validator = new AbcTimingValidator();
$testLines = [
    'M:4/4',
    'L:1/4',
    'V:Bagpipes',
    '|A B C D|',
    '|A B C|',
    '|A B|'
];

$result = $validator->validate($testLines);
echo 'Lines:' . PHP_EOL;
foreach ($result['lines'] as $line) {
    echo $line . PHP_EOL;
}
echo PHP_EOL . 'Errors:' . PHP_EOL;
foreach ($result['errors'] as $error) {
    echo $error . PHP_EOL;
}
?>
