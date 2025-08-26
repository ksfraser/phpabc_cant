#!/usr/bin/env php
<?php
// Pass 1: Voice assignment and melody-to-bagpipes copy
require_once __DIR__ . '/../vendor/autoload.php';
use Ksfraser\PhpabcCanntaireachd\AbcVoicePass;
use Ksfraser\PhpabcCanntaireachd\AbcFileParser;

if ($argc < 3) {
    echo "Usage: php bin/abc-voice-pass-cli.php <abcfile> <tune_number>\n";
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
