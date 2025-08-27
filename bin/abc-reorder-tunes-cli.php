#!/usr/bin/env php
<?php
// CLI: Reorder tunes in ABC file by X: header ascending

require_once __DIR__ . '/../vendor/autoload.php';
use Ksfraser\PhpabcCanntaireachd\AbcFileParser;
use Ksfraser\PhpabcCanntaireachd\CliOutputWriter;

if ($argc < 2) {
    echo "Usage: php bin/abc-reorder-tunes-cli.php <abcfile>\n";
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
// Sort tunes by X header
usort($tunes, function($a, $b) {
    $xa = $a->getHeaders()['X']->get();
    $xb = $b->getHeaders()['X']->get();
    return $xa - $xb;
});
$output = '';
foreach ($tunes as $tune) {
    $headers = $tune->getHeaders();
    $x = isset($headers['X']) ? $headers['X']->get() : null;
    if ($x !== null) {
        $output .= "X:$x\n";
    }
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

CliOutputWriter::write($output, $file . '.reordered');
echo "Reordered file written to $file.reordered\n";
