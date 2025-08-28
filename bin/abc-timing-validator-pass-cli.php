#!/usr/bin/env php
<?php
// Pass 5: Timing validation

require_once __DIR__ . '/../vendor/autoload.php';
use Ksfraser\PhpabcCanntaireachd\AbcTimingValidator;
use Ksfraser\PhpabcCanntaireachd\AbcFileParser;
use Ksfraser\PhpabcCanntaireachd\CliOutputWriter;

// Support --output and --errorfile options
$outputFile = null;
$errorFile = null;
foreach ($argv as $i => $arg) {
    if ($i === 0) continue;
    if (preg_match('/^--output=(.+)$/', $arg, $m)) {
        $outputFile = $m[1];
    } elseif (preg_match('/^--errorfile=(.+)$/', $arg, $m)) {
        $errorFile = $m[1];
    } elseif (!isset($file)) {
        $file = $arg;
    } elseif (!isset($xnum)) {
        $xnum = $arg;
    }
}
if (!isset($file) || !isset($xnum)) {
    $msg = "Usage: php bin/abc-timing-validator-pass-cli.php <abcfile> <tune_number> [--output=out.txt] [--errorfile=err.txt]\n";
    if ($errorFile) {
        CliOutputWriter::write($msg, $errorFile);
    } else {
        echo $msg;
    }
    exit(1);
}
if (!file_exists($file)) {
    $msg = "File not found: $file\n";
    if ($errorFile) {
        CliOutputWriter::write($msg, $errorFile);
    } else {
        echo $msg;
    }
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
    $msg = "Tune X:$xnum not found in $file\n";
    if ($errorFile) {
        CliOutputWriter::write($msg, $errorFile);
    } else {
        echo $msg;
    }
    exit(1);
}
$lines = [];
foreach ($tune->getLines() as $lineObj) {
    if (method_exists($lineObj, 'render')) {
        $lines[] = $lineObj->render();
    }
}
$pass = new AbcTimingValidator();
$result = $pass->validate($lines);

$output = implode("\n", $result['lines']) . "\n";
if (!empty($result['errors'])) {
    foreach ($result['errors'] as $err) {
        $output .= "% TIMING ERROR: $err\n";
    }
}
$logMsg = "Timing validation output written to " . ($outputFile ?: "stdout") . "\n";
if ($outputFile) {
    CliOutputWriter::write($output, $outputFile);
    if ($errorFile) {
        CliOutputWriter::write($logMsg, $errorFile);
    } else {
        echo $logMsg;
    }
} else {
    echo $output;
    if ($errorFile) {
        CliOutputWriter::write($logMsg, $errorFile);
    }
}
