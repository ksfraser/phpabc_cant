#!/usr/bin/env php
<?php
// CLI: Check for duplicate X: tune numbers
require_once __DIR__ . '/../vendor/autoload.php';
use Ksfraser\PhpabcCanntaireachd\AbcTuneNumberValidatorPass;
use Ksfraser\PhpabcCanntaireachd\AbcFileParser;

if ($argc < 2) {
    echo "Usage: php bin/abc-tune-number-validator-cli.php <abcfile>\n";
    exit(1);
}
$file = $argv[1];
if (!file_exists($file)) {
    echo "File not found: $file\n";
    exit(1);
}
$abcContent = file_get_contents($file);
$parser = new AbcFileParser();
$tunes = $parser->parse($abcContent);
$lines = [];
foreach ($tunes as $tune) {
    foreach ($tune->getLines() as $lineObj) {
        if (method_exists($lineObj, 'render')) {
            $lines[] = $lineObj->render();
        }
    }
}
$pass = new AbcTuneNumberValidatorPass();
$result = $pass->validate($lines);
if (!empty($result['errors'])) {
    foreach ($result['errors'] as $err) {
        echo "% ERROR: $err\n";
    }
} else {
    echo "All X: tune numbers are unique.\n";
}
