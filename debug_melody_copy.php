<?php
require 'vendor/autoload.php';

$abc = "X:1\nT:Test\nM:4/4\nL:1/4\nK:HP\nV:Melody\n|A B C D|\nw:hello world\n";
$lines = explode("\n", trim($abc));

echo "Input lines:\n";
foreach ($lines as $i => $line) {
    echo "[$i]: '$line'\n";
}

$result = Ksfraser\PhpabcCanntaireachd\AbcProcessor::process($abc, ['cannt' => 1]);
echo "\nOutput lines:\n";
foreach ($result['lines'] as $i => $line) {
    echo "[$i]: '$line'\n";
}
