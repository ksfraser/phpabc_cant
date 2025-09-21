#!/usr/bin/env php
<?php
/**
 * ABC Renumber Tunes CLI Tool
 *
 * Renumbers duplicate X: tune numbers in ABC files, ensuring all tunes have unique numbers.
 * Creates a new file with the suffix '.renumbered'.
 *
 * Usage:
 *   php abc-renumber-tunes-cli.php <abcfile> [options]
 *
 * Arguments:
 *   abcfile       Path to the ABC file to process
 *
 * Options:
 *   --width <N>           Width for zero-padding tune numbers (default: 5)
 *   -e, --errorfile <file> Output file for error messages and logs
 *   -h, --help            Show this help message
 *   -v, --verbose         Enable verbose output
 *
 * Examples:
 *   php abc-renumber-tunes-cli.php tunes.abc
 *   php abc-renumber-tunes-cli.php tunes.abc --width 3
 *   php abc-renumber-tunes-cli.php tunes.abc --verbose --errorfile=renumber.log
 *
 * Output:
 *   - Creates a new file with '.renumbered' suffix
 *   - All X: headers are renumbered to be unique
 *   - Duplicate numbers are assigned the next available number
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

$width = isset($cli->opts['width']) ? (int)$cli->opts['width'] : 5;

$abcContent = file_get_contents($file);
$parser = new AbcFileParser();
$tunes = $parser->parse($abcContent);

$newX = 1;
$output = '';
$renumberedCount = 0;
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
        $renumberedCount++;
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

$outputFile = $file . '.renumbered';
CliOutputWriter::write($output, $outputFile);

$logMsg = "Renumbering completed\n";
$logMsg .= "✓ Processed " . count($tunes) . " tunes\n";
$logMsg .= "✓ Renumbered $renumberedCount duplicate tune numbers\n";
$logMsg .= "✓ Output written to: $outputFile\n";

if (isset($cli->opts['v']) || isset($cli->opts['verbose'])) {
    $logMsg .= "✓ Used width: $width digits\n";
    $logMsg .= "✓ Next available number: $newX\n";
}

if ($cli->errorFile) {
    CliOutputWriter::write($logMsg, $cli->errorFile);
} else {
    echo $logMsg;
}

function showUsage() {
    global $argv;
    $script = basename($argv[0]);
    echo "ABC Renumber Tunes CLI Tool

Renumbers duplicate X: tune numbers in ABC files, ensuring all tunes have unique numbers.
Creates a new file with the suffix '.renumbered'.

Usage:
  php $script <abcfile> [options]

Arguments:
  abcfile       Path to the ABC file to process

Options:
  --width <N>           Width for zero-padding tune numbers (default: 5)
  -e, --errorfile <file> Output file for error messages and logs
  -h, --help            Show this help message
  -v, --verbose         Enable verbose output

Examples:
  php $script tunes.abc
  php $script tunes.abc --width 3
  php $script tunes.abc --verbose --errorfile=renumber.log

Output:
  - Creates a new file with '.renumbered' suffix
  - All X: headers are renumbered to be unique
  - Duplicate numbers are assigned the next available number
";
}
