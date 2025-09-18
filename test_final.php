<?php
require 'vendor/autoload.php';
use Ksfraser\PhpabcCanntaireachd\AbcProcessor;

// Test the canntDiff functionality
$dict = ['cannt' => 1];
$abc = "V:Bagpipes\n%canntaireachd: old\n";
$result = AbcProcessor::process($abc, $dict);
echo 'CanntDiff: ' . (empty($result['canntDiff']) ? 'empty' : implode(', ', $result['canntDiff'])) . "\n";

// Test the melody to bagpipes copy
$abc2 = "X:1\nT:Test\nM:4/4\nL:1/4\nK:HP\nV:Melody\n|A B C D|\nw:hello world\n";
$result2 = AbcProcessor::process($abc2, $dict);
$output = implode("\n", $result2['lines']);
echo 'Has Bagpipes voice: ' . (strpos($output, 'V:Bagpipes') !== false ? 'yes' : 'no') . "\n";
echo 'Has canntaireachd comment: ' . (strpos($output, '%canntaireachd:') !== false ? 'yes' : 'no') . "\n";
?>
