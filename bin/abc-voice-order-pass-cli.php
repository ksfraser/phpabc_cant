#!/usr/bin/env php
<?php
// Pass 4: Voice order reordering
require_once __DIR__ . '/../vendor/autoload.php';
use Ksfraser\PhpabcCanntaireachd\AbcVoiceOrderPass;
use Ksfraser\PhpabcCanntaireachd\AbcFileParser;

if ($argc < 3) {
    echo "Usage: php bin/abc-voice-order-pass-cli.php <abcfile> <tune_number>\n";
    exit(1);
}
$file = $argv[1];
$xnum = $argv[2];
if (!file_exists($file)) {
    echo "File not found: $file\n";
    exit(1);
}
$abcContent = file_get_contents($file);
$parser = new AbcFileParser();
$tunes = $parser->parse($abcContent);
$tune = null;
foreach ($tunes as $t) {
    $headers = $t->getHeaders();
    if (isset($headers['X']) && $headers['X']->get() == $xnum) {
        $tune = $t;
        break;
    }
}
if (!$tune) {
    echo "Tune X:$xnum not found in $file\n";
    exit(1);
}
$lines = [];
foreach ($tune->getLines() as $lineObj) {
    if (method_exists($lineObj, 'render')) {
        $lines[] = $lineObj->render();
    }
}
$pass = new AbcVoiceOrderPass();
$result = $pass->process($lines);
foreach ($result as $line) {
    echo $line . "\n";
}
