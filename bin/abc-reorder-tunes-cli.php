#!/usr/bin/env php
<?php
/**
 * ABC Reorder Tunes CLI Tool
 *
 * Reorders tunes in an ABC file by their X: header numbers in ascending order.
 * Creates a new file with the suffix '.reordered'.
 *
 * Usage:
 *   php abc-reorder-tunes-cli.php <abcfile> [options]
 *
 * Arguments:
 *   abcfile       Path to the ABC file to process
 *
 * Options:
 *   -o, --output <file>   Output file for reordered ABC content (default: <abcfile>.reordered)
 *   -e, --errorfile <file> Output file for error messages and logs
 *   -h, --help            Show this help message
 *   -v, --verbose         Enable verbose output
 *
 * Examples:
 *   php abc-reorder-tunes-cli.php tunes.abc
 *   php abc-reorder-tunes-cli.php tunes.abc --output=sorted.abc
 *   php abc-reorder-tunes-cli.php tunes.abc --verbose --errorfile=reorder.log
 *
 * Processing:
 *   - Parses all tunes from the input ABC file
 *   - Sorts tunes by X: header number (ascending)
 *   - Writes reordered content to output file
 *   - Preserves all headers and tune content
 */

require_once __DIR__ . '/../vendor/autoload.php';
use Ksfraser\PhpabcCanntaireachd\AbcFileParser;
use Ksfraser\PhpabcCanntaireachd\CliOutputWriter;
use Ksfraser\PhpabcCanntaireachd\CLIOptions;

// Parse command line arguments
$cli = CLIOptions::fromArgv($argv);

// Show help if requested
if (isset($cli->opts['h']) || isset($cli->opts['help'])) {
    showUsage();
    exit(0);
}

// Get positional arguments from CLIOptions
$file = $cli->file;

if (!$file) {
    showUsage();
    exit(1);
}

if (!file_exists($file)) {
    $msg = "Error: Input file '$file' not found\n";
    if ($cli->errorFile) {
        CliOutputWriter::write($msg, $cli->errorFile);
    } else {
        fwrite(STDERR, $msg);
    }
    exit(1);
}

$outputFile = $cli->outputFile ?: $file . '.reordered';

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

CliOutputWriter::write($output, $outputFile);

$logMsg = "Tune reordering completed\n";
$logMsg .= "✓ Processed " . count($tunes) . " tunes\n";
$logMsg .= "✓ Output written to: $outputFile\n";

if (isset($cli->opts['v']) || isset($cli->opts['verbose'])) {
    $logMsg .= "✓ Tunes sorted by X: header in ascending order\n";
}

if ($cli->errorFile) {
    CliOutputWriter::write($logMsg, $cli->errorFile);
} else {
    echo $logMsg;
}

function showUsage() {
    global $argv;
    $script = basename($argv[0]);
    echo "ABC Reorder Tunes CLI Tool

Reorders tunes in an ABC file by their X: header numbers in ascending order.
Creates a new file with the suffix '.reordered'.

Usage:
  php $script <abcfile> [options]

Arguments:
  abcfile       Path to the ABC file to process

Options:
  -o, --output <file>   Output file for reordered ABC content (default: <abcfile>.reordered)
  -e, --errorfile <file> Output file for error messages and logs
  -h, --help            Show this help message
  -v, --verbose         Enable verbose output

Examples:
  php $script tunes.abc
  php $script tunes.abc --output=sorted.abc
  php $script tunes.abc --verbose --errorfile=reorder.log

Processing:
  - Parses all tunes from the input ABC file
  - Sorts tunes by X: header number (ascending)
  - Writes reordered content to output file
  - Preserves all headers and tune content
";
}
