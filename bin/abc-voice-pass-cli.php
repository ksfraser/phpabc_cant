#!/usr/bin/env php
<?php
/**
 * ABC Voice Pass CLI Tool
 *
 * Processes voice assignments and melody-to-bagpipes copying in ABC files.
 * This is the first pass in the ABC processing pipeline.
 *
 * Usage:
 *   php abc-voice-pass-cli.php <abcfile> <tune_number> [options]
 *
 * Arguments:
 *   abcfile       Path to the ABC file to process
 *   tune_number   X: header number of the tune to process
 *
 * Options:
 *   -o, --output <file>     Output file for processed ABC (default: stdout)
 *   -e, --errorfile <file>  Output file for error messages and logs
 *   -h, --help              Show this help message
 *   -v, --verbose           Enable verbose output
 *
 * Examples:
 *   php abc-voice-pass-cli.php tunes.abc 1
 *   php abc-voice-pass-cli.php tunes.abc 1 --output=processed.abc
 *   php abc-voice-pass-cli.php tunes.abc 1 --verbose --errorfile=errors.txt
 *
 * Processing:
 *   - Assigns voices to instruments
 *   - Copies melody voices to bagpipes when no bagpipe voice exists
 *   - Prepares ABC for subsequent processing passes
 */

require_once __DIR__ . '/../vendor/autoload.php';
use Ksfraser\PhpabcCanntaireachd\CliOutputWriter;

use Ksfraser\PhpabcCanntaireachd\AbcParser;
use Ksfraser\PhpabcCanntaireachd\AbcValidator;

use Ksfraser\PhpabcCanntaireachd\AbcVoicePass;
use Ksfraser\PhpabcCanntaireachd\AbcFileParser;
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

// Find the specified tune
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

// Extract lines from the tune
$lines = [];
foreach ($tune->getLines() as $lineObj) {
    if (method_exists($lineObj, 'render')) {
        $lines[] = $lineObj->render();
    }
}

// Apply voice pass processing
$pass = new AbcVoicePass();
$result = $pass->process($lines);

$output = implode("\n", $result) . "\n";

$logMsg = "Voice pass processing completed for tune X:$xnum\n";
if (isset($cli->opts['v']) || isset($cli->opts['verbose'])) {
    $logMsg .= "Processed " . count($lines) . " input lines\n";
    $logMsg .= "Generated " . count($result) . " output lines\n";
}

// Output handling
if ($cli->outputFile) {
    CliOutputWriter::write($output, $cli->outputFile);
    $logMsg .= "Processed ABC written to: {$cli->outputFile}\n";
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
    echo "ABC Voice Pass CLI Tool

Processes voice assignments and melody-to-bagpipes copying in ABC files.
This is the first pass in the ABC processing pipeline.

Usage:
  php $script <abcfile> <tune_number> [options]

Arguments:
  abcfile       Path to the ABC file to process
  tune_number   X: header number of the tune to process

Options:
  -o, --output <file>     Output file for processed ABC (default: stdout)
  -e, --errorfile <file>  Output file for error messages and logs
  -h, --help              Show this help message
  -v, --verbose           Enable verbose output

Examples:
  php $script tunes.abc 1
  php $script tunes.abc 1 --output=processed.abc
  php $script tunes.abc 1 --verbose --errorfile=errors.txt

Processing:
  - Assigns voices to instruments
  - Copies melody voices to bagpipes when no bagpipe voice exists
  - Prepares ABC for subsequent processing passes
";
}
