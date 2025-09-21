#!/usr/bin/env php
<?php
/**
 * ABC Tune Number Validator CLI Tool
 *
 * Validates that all X: tune numbers in an ABC file are unique.
 * Reports any duplicate tune numbers found in the file.
 *
 * Usage:
 *   php abc-tune-number-validator-cli.php <abcfile> [options]
 *
 * Arguments:
 *   abcfile       Path to the ABC file to validate
 *
 * Options:
 *   -e, --errorfile <file> Output file for validation results and error messages
 *   -h, --help            Show this help message
 *   -v, --verbose         Enable verbose output with detailed validation info
 *
 * Examples:
 *   php abc-tune-number-validator-cli.php tunes.abc
 *   php abc-tune-number-validator-cli.php tunes.abc --errorfile=validation.log
 *   php abc-tune-number-validator-cli.php tunes.abc --verbose
 *
 * Output:
 *   - Reports duplicate X: numbers if found
 *   - Confirms uniqueness if no duplicates exist
 *   - Uses ABC comment format (% ERROR:) for error messages
 */

require_once __DIR__ . '/../vendor/autoload.php';
use Ksfraser\PhpabcCanntaireachd\AbcTuneNumberValidatorPass;
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

$logMsg = "";
if (!empty($result['errors'])) {
    $logMsg .= "Validation failed - duplicate tune numbers found:\n";
    foreach ($result['errors'] as $err) {
        $logMsg .= "% ERROR: $err\n";
    }
    $logMsg .= "✓ Found " . count($result['errors']) . " duplicate tune number(s)\n";
} else {
    $logMsg .= "Validation passed - all X: tune numbers are unique.\n";
    $logMsg .= "✓ Checked " . count($tunes) . " tunes\n";
}

if (isset($cli->opts['v']) || isset($cli->opts['verbose'])) {
    $logMsg .= "✓ File: $file\n";
    $logMsg .= "✓ Total tunes processed: " . count($tunes) . "\n";
}

if ($cli->errorFile) {
    CliOutputWriter::write($logMsg, $cli->errorFile);
} else {
    echo $logMsg;
}

function showUsage() {
    global $argv;
    $script = basename($argv[0]);
    echo "ABC Tune Number Validator CLI Tool

Validates that all X: tune numbers in an ABC file are unique.
Reports any duplicate tune numbers found in the file.

Usage:
  php $script <abcfile> [options]

Arguments:
  abcfile       Path to the ABC file to validate

Options:
  -e, --errorfile <file> Output file for validation results and error messages
  -h, --help            Show this help message
  -v, --verbose         Enable verbose output with detailed validation info

Examples:
  php $script tunes.abc
  php $script tunes.abc --errorfile=validation.log
  php $script tunes.abc --verbose

Output:
  - Reports duplicate X: numbers if found
  - Confirms uniqueness if no duplicates exist
  - Uses ABC comment format (% ERROR:) for error messages
";
}
