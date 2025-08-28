#!/usr/bin/env php
<?php
// CLI: Check for duplicate X: tune numbers
require_once __DIR__ . '/../vendor/autoload.php';
use Ksfraser\PhpabcCanntaireachd\AbcTuneNumberValidatorPass;
use Ksfraser\PhpabcCanntaireachd\AbcFileParser;

// Usage: php bin/abc-tune-number-validator-cli.php <abcfile> [--errorfile=err.txt]
$file = null;
$errorFile = null;
foreach ($argv as $arg) {
    if (preg_match('/^--errorfile=(.+)$/', $arg, $m)) {
        $errorFile = $m[1];
    } elseif ($arg !== $argv[0]) {
        $file = $arg;
    }
}
if (!$file) {
    $msg = "Usage: php bin/abc-tune-number-validator-cli.php <abcfile> [--errorfile=err.txt]\n";
    if ($errorFile) {
        \Ksfraser\PhpabcCanntaireachd\CliOutputWriter::write($msg, $errorFile);
    } else {
        echo $msg;
    }
    exit(1);
}
if (!file_exists($file)) {
    $msg = "File not found: $file\n";
    if ($errorFile) {
        \Ksfraser\PhpabcCanntaireachd\CliOutputWriter::write($msg, $errorFile);
    } else {
        echo $msg;
    }
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
    $msg = "";
    foreach ($result['errors'] as $err) {
        $msg .= "% ERROR: $err\n";
    }
    if ($errorFile) {
        \Ksfraser\PhpabcCanntaireachd\CliOutputWriter::write($msg, $errorFile);
    } else {
        echo $msg;
    }
} else {
    $msg = "All X: tune numbers are unique.\n";
    if ($errorFile) {
        \Ksfraser\PhpabcCanntaireachd\CliOutputWriter::write($msg, $errorFile);
    } else {
        echo $msg;
    }
}
