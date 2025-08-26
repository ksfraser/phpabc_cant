#!/usr/bin/env php
<?php
// CLI: Renumber duplicated X: tune numbers
require_once __DIR__ . '/../vendor/autoload.php';
use Ksfraser\PhpabcCanntaireachd\AbcFileParser;

// Usage: php bin/abc-renumber-tunes-cli.php <abcfile> [--width=N]
$width = 5;
$file = null;
foreach ($argv as $arg) {
    if (preg_match('/^--width=(\d+)$/', $arg, $m)) {
        $width = (int)$m[1];
    } elseif ($arg !== $argv[0]) {
        $file = $arg;
    }
}
if (!$file) {
    echo "Usage: php bin/abc-renumber-tunes-cli.php <abcfile> [--width=N]\n";
    exit(1);
}
if (!file_exists($file)) {
    echo "File not found: $file\n";
    exit(1);
}
$abcContent = file_get_contents($file);
$parser = new AbcFileParser();
$tunes = $parser->parse($abcContent);
$newX = 1;
$output = '';
$seenX = [];
foreach ($tunes as $tune) {
    $headers = $tune->getHeaders();
    $x = isset($headers['X']) ? $headers['X']->get() : null;
    $xStr = null;
    if ($x !== null && isset($seenX[$x])) {
        // Duplicate, assign next available new X
        while (isset($seenX[$newX])) {
            $newX++;
        }
        $xStr = str_pad($newX, $width, '0', STR_PAD_LEFT);
        $headers['X']->set($xStr);
        $output .= "X:$xStr\n";
        $seenX[$newX] = true;
        $newX++;
    } else if ($x !== null) {
        $seenX[$x] = true;
        $xStr = str_pad($x, $width, '0', STR_PAD_LEFT);
        $headers['X']->set($xStr);
        $output .= "X:$xStr\n";
    }
    // Render other headers and lines
    foreach ($headers as $key => $headerObj) {
        if ($key !== 'X') {
            $val = $headerObj->get();
            if ($val !== '') $output .= "$key:$val\n";
        }
    }
    foreach ($tune->getLines() as $lineObj) {
        if (method_exists($lineObj, 'render')) {
            $line = trim($lineObj->render());
            if ($line !== '') $output .= $line . "\n";
        }
    }
    $output .= "\n";
}
file_put_contents($file . '.renumbered', $output);
echo "Renumbered file written to $file.renumbered\n";
