#!/usr/bin/env php
<?php
// Pass 3: Canntaireachd validation

require_once __DIR__ . '/../vendor/autoload.php';
use Ksfraser\PhpabcCanntaireachd\AbcCanntaireachdPass;
use Ksfraser\PhpabcCanntaireachd\AbcFileParser;
use Ksfraser\PhpabcCanntaireachd\CliOutputWriter;

// Support --output option
$outputFile = null;
foreach ($argv as $i => $arg) {
    if ($i === 0) continue;
    if (preg_match('/^--output=(.+)$/', $arg, $m)) {
        $outputFile = $m[1];
    } elseif (!isset($file)) {
        $file = $arg;
    } elseif (!isset($xnum)) {
        $xnum = $arg;
    }
}
if (!isset($file) || !isset($xnum)) {
    echo "Usage: php bin/abc-canntaireachd-pass-cli.php <abcfile> <tune_number> [--output=out.txt]\n";
    exit(1);
}
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
$pass = new AbcCanntaireachdPass();
$result = $pass->process($lines);

$output = implode("\n", $result['lines']) . "\n";
if (!empty($result['canntDiff'])) {
    $output .= "% Canntaireachd diff: " . json_encode($result['canntDiff']) . "\n";
}
if ($outputFile) {
    CliOutputWriter::write($output, $outputFile);
    echo "Canntaireachd output written to $outputFile\n";
} else {
    echo $output;
}
