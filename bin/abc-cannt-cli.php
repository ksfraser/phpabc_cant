#!/usr/bin/env php
<?php
/**
 * CLI launcher for ABC Canntaireachd tools
 *
 * Usage:
 *   php bin/abc-cannt-cli.php --file=path/to/file.abc [--convert]
 *
 * Options:
 *   --file, -f      Path to ABC file to process
 *   --convert, -c   Add canntaireachd lines to melody/bagpipe voices
 */

require __DIR__ . '/../vendor/autoload.php';
use Ksfraser\PhpabcCanntaireachd\CliOutputWriter;

use Ksfraser\PhpabcCanntaireachd\AbcParser;
use Ksfraser\PhpabcCanntaireachd\AbcValidator;


$options = getopt('f:c:o:', ['file:', 'convert', 'output:']);
$file = $options['f'] ?? $options['file'] ?? null;
$convert = isset($options['c']) || isset($options['convert']);
$outputFile = $options['o'] ?? $options['output'] ?? null;


if (!$file || !file_exists($file)) {
    fwrite(STDERR, "ABC file not found.\n");
    exit(1);
}


$abcContent = file_get_contents($file);
$validator = new AbcValidator();
$errors = $validator->validate($abcContent);

if ($errors) {
    $errorMsg = "Validation errors found in '$file':\n";
    foreach ($errors as $err) {
        $errorMsg .= "  - $err\n";
    }
    if ($outputFile) {
        CliOutputWriter::write($errorMsg, $outputFile);
    } else {
        echo $errorMsg;
    }
    exit(2);
}

$parser = new AbcParser();
$result = $parser->process($abcContent);

$outputMsg = "File '$file' processed and validated.\n";
if ($convert) {
    $outputMsg .= "[Example] Would add canntaireachd lines to melody/bagpipe voices.\n";
}
// Example: Compare canntaireachd lines if present
// $outputMsg .= "[Example] Would compare existing canntaireachd lines and report differences.\n";

if ($outputFile) {
    CliOutputWriter::write($outputMsg, $outputFile);
} else {
    echo $outputMsg;
}
