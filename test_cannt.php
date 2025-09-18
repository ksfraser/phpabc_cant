<?php
require 'vendor/autoload.php';

$abc = "X:1\nT:Test\nM:4/4\nL:1/4\nK:HP\nV:Melody\n|A B C D|\nw:hello world\n";
echo "Input:\n$abc\n\n";

$result = Ksfraser\PhpabcCanntaireachd\AbcProcessor::process($abc, ['cannt' => 1]);
echo "Output:\n" . implode("\n", $result['lines']) . "\n";
