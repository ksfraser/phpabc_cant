<?php
require __DIR__ . '/vendor/autoload.php';
use Ksfraser\PhpabcCanntaireachd\AbcParser;

$abcContent = 'X:1
T:Test MIDI Voice Update
M:4/4
L:1/4
K:HP
V:1
%%MIDI program 73 % flute
V:2
%%MIDI program 109 % bagpipes
|A B C D|';

$parser = new AbcParser();
$config = ['updateVoiceNamesFromMidi' => true];
$result = $parser->process($abcContent, $config);

echo "Result:\n";
echo $result;
echo "\n";
echo "Config passed: " . json_encode($config) . "\n";
