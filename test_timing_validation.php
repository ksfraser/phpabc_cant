<?php
require 'vendor/autoload.php';

use Ksfraser\PhpabcCanntaireachd\AbcTimingValidator;

$validator = new AbcTimingValidator();

// Test that bars with too many beats are marked, but incomplete bars are not
$abc = [
    "M:4/4",
    "L:1/4",
    "V:Bagpipes",
    "|A B C D|",
    "|A2 B2|",
    "|A|",
    "|A B C D E F G H|"
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

$timingFound = false;
foreach ($result['lines'] as $line) {
    if (strpos($line, 'TIMING') !== false) {
        $timingFound = true;
        break;
    }
}

$incompleteBarMarked = false;
foreach ($result['lines'] as $line) {
    if (strpos($line, '|A| TIMING') !== false) {
        $incompleteBarMarked = true;
        break;
    }
}

echo "\nTest Results:\n";
echo "TIMING marker found: " . ($timingFound ? "YES" : "NO") . "\n";
echo "Incomplete bar marked: " . ($incompleteBarMarked ? "YES" : "NO") . "\n";
echo "Errors present: " . (!empty($result['errors']) ? "YES" : "NO") . "\n";
?>
