#!/usr/bin/env php
<?php
/**
 * ABC Timing Validator CLI Tool
 *
 * Validates timing in ABC notation files by checking bar lengths against meter signatures.
 * Only validates music lines (skips comments, headers, directives, etc.).
 *
 * Usage:
 *   php abc-timing-validator-pass-cli.php <abcfile> <tune_number> [options]
 *
 * Arguments:
 *   abcfile       Path to the ABC file to validate
 *   tune_number   X: header number of the tune to validate
 *
 * Options:
 *   -o, --output <file>     Output file for processed ABC (default: stdout)
 *   -e, --errorfile <file>  Output file for error messages and logs
 *   -h, --help              Show this help message
 *   -v, --verbose           Enable verbose logging of timing calculations
 *
 * Examples:
 *   php abc-timing-validator-pass-cli.php tunes.abc 1
 *   php abc-timing-validator-pass-cli.php tunes.abc 1 --output=validated.abc
 *   php abc-timing-validator-pass-cli.php tunes.abc 1 --verbose --errorfile=errors.txt
 *
 * Output:
 *   - TIMING markers are added to bars with incorrect beat counts
 *   - Error messages show expected vs actual beats for each bar
 *   - Only bars with too many beats are flagged (incomplete bars are allowed)
 */

require_once __DIR__ . '/../vendor/autoload.php';
use Ksfraser\PhpabcCanntaireachd\AbcTimingValidator;
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
$xnum = $cli->xnum;

if (!$file || !$xnum) {
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
$tune = null;
foreach ($tunes as $t) {
    $headers = $t->getHeaders();
    if (isset($headers['X']) && $headers['X']->get() == $xnum) {
        $tune = $t;
        break;
    }
}
if (!$tune) {
    $msg = "Error: Tune X:$xnum not found in $file\n";
    if ($cli->errorFile) {
        CliOutputWriter::write($msg, $cli->errorFile);
    } else {
        fwrite(STDERR, $msg);
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

$logMsg = "Timing validation completed for tune X:$xnum\n";
if (!empty($result['errors'])) {
    $logMsg .= "Found " . count($result['errors']) . " timing errors\n";
} else {
    $logMsg .= "No timing errors found\n";
}

if (isset($cli->opts['v']) || isset($cli->opts['verbose'])) {
    $logMsg .= "Processed " . count($lines) . " lines\n";
}

// Output handling
if ($cli->outputFile) {
    CliOutputWriter::write($output, $cli->outputFile);
    $logMsg .= "Validated ABC written to: {$cli->outputFile}\n";
}

if ($cli->errorFile) {
    CliOutputWriter::write($logMsg, $cli->errorFile);
} else {
    if ($cli->outputFile) {
        echo $logMsg;
    } else {
        echo $output;
    }
}

function showUsage() {
    global $argv;
    $script = basename($argv[0]);
    echo "ABC Timing Validator CLI Tool

Validates timing in ABC notation files by checking bar lengths against meter signatures.
Only validates music lines (skips comments, headers, directives, etc.).

Usage:
  php $script <abcfile> <tune_number> [options]

Arguments:
  abcfile       Path to the ABC file to validate
  tune_number   X: header number of the tune to validate

Options:
  -o, --output <file>     Output file for processed ABC (default: stdout)
  -e, --errorfile <file>  Output file for error messages and logs
  -h, --help              Show this help message
  -v, --verbose           Enable verbose logging of timing calculations

Examples:
  php $script tunes.abc 1
  php $script tunes.abc 1 --output=validated.abc
  php $script tunes.abc 1 --verbose --errorfile=errors.txt

Output:
  - TIMING markers are added to bars with incorrect beat counts
  - Error messages show expected vs actual beats for each bar
  - Only bars with too many beats are flagged (incomplete bars are allowed)
";
}
