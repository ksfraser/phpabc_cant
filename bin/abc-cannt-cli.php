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



$options = getopt('f:c:o:e:', ['file:', 'convert', 'output:', 'errorfile:']);
$file = $options['f'] ?? $options['file'] ?? null;
$convert = isset($options['c']) || isset($options['convert']);
$outputFile = $options['o'] ?? $options['output'] ?? null;
$errorFile = $options['e'] ?? $options['errorfile'] ?? null;


if (!$file || !file_exists($file)) {
    $msg = "ABC file not found.\n";
    if ($errorFile) {
        CliOutputWriter::write($msg, $errorFile);
    } else {
        fwrite(STDERR, $msg);
    }
    exit(1);
}


$abcContent = file_get_contents($file);
$validator = new AbcValidator();
$errors = $validator->validate($abcContent);


$parser = new AbcParser();
$result = $parser->process($abcContent);

$outputMsg = "File '$file' processed and validated.\n";
if ($convert) {
    $outputMsg .= "[Example] Would add canntaireachd lines to melody/bagpipe voices.\n";
}
// Example: Compare canntaireachd lines if present
// $outputMsg .= "[Example] Would compare existing canntaireachd lines and report differences.\n";

// Always log validation errors if present
if ($errors) {
    $errorMsg = "Validation errors found in '$file':\n";
    foreach ($errors as $err) {
        $errorMsg .= "  - $err\n";
    }
    if ($errorFile) {
        CliOutputWriter::write($errorMsg, $errorFile);
    } else {
        echo $errorMsg;
    }
}

// Write processed ABC result to output file if requested
if ($outputFile) {
    // Write processed ABC to output file ONLY
    CliOutputWriter::write($result, $outputFile);
    // Write log/status to errorfile ONLY
    if ($errorFile) {
        CliOutputWriter::write($outputMsg, $errorFile);
    } else {
        echo $outputMsg;
    }
} else {
    // Write processed ABC to stdout ONLY
    echo $result;
    // Write log/status to errorfile or stdout
    if ($errorFile) {
        CliOutputWriter::write($outputMsg, $errorFile);
    } else {
        echo $outputMsg;
    }
}
