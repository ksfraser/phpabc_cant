#!/usr/bin/env php
<?php
/**
 * ABC Lyrics Pass CLI Tool
 *
 * Processes lyrics in ABC notation files, converting canntaireachd tokens to lyrics.
 * This is the second pass in the ABC processing pipeline.
 *
 * Usage:
 *   php abc-lyrics-pass-cli.php <abcfile> <tune_number> [options]
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
 *   php abc-lyrics-pass-cli.php tunes.abc 1
 *   php abc-lyrics-pass-cli.php tunes.abc 1 --output=with_lyrics.abc
 *   php abc-lyrics-pass-cli.php tunes.abc 1 --verbose --errorfile=lyrics.log
 *
 * Processing:
 *   - Converts canntaireachd tokens to human-readable lyrics
 *   - Adds W: lines with processed lyrics
 *   - Integrates lyrics with musical notation
 */

require_once __DIR__ . '/../vendor/autoload.php';
use Ksfraser\PhpabcCanntaireachd\AbcLyricsPass;
use Ksfraser\PhpabcCanntaireachd\AbcFileParser;
use Ksfraser\PhpabcCanntaireachd\Parse\AbcTunesToTune;
use Ksfraser\PhpabcCanntaireachd\TokenDictionary;
use Ksfraser\PhpabcCanntaireachd\CliOutputWriter;
use Ksfraser\PhpabcCanntaireachd\Parse\AbcFileToTunes;
use Ksfraser\PhpabcCanntaireachd\CLIOptions;
use Ksfraser\PhpabcCanntaireachd\AbcProcessorConfig;

// Parse command line arguments
$cli = CLIOptions::fromArgv($argv);

// Show help if requested
if (isset($cli->opts['h']) || isset($cli->opts['help'])) {
    showUsage();
    exit(0);
}

// Load configuration with precedence
$config = AbcProcessorConfig::loadWithPrecedence();
if ($cli->configFile !== null) {
    if (!file_exists($cli->configFile)) {
        fwrite(STDERR, "Error: Configuration file not found: {$cli->configFile}\n");
        exit(1);
    }
    try {
        $customConfig = AbcProcessorConfig::loadFromFile($cli->configFile);
        $config->mergeFromArray($customConfig->toArray());
    } catch (Exception $e) {
        fwrite(STDERR, "Error loading configuration: " . $e->getMessage() . "\n");
        exit(1);
    }
}
$cli->applyToConfig($config);

if ($cli->showConfig) {
    echo "=== Current Configuration ===\n";
    echo $config->toJSON(true);
    echo "\n";
    exit(0);
}

if ($cli->saveConfigFile !== null) {
    try {
        $config->saveToFile($cli->saveConfigFile);
        echo "Configuration saved to: {$cli->saveConfigFile}\n";
        exit(0);
    } catch (Exception $e) {
        fwrite(STDERR, "Error saving configuration: " . $e->getMessage() . "\n");
        exit(1);
    }
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

$tunes = AbcFileToTunes::parse($file);
$tune = AbcTunesToTune::locate($tunes, $xnum);

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

$dict = new TokenDictionary();
$pass = new AbcLyricsPass($dict);
$result = $pass->process($lines);

$output = implode("\n", $result['lines']) . "\n";
if (!empty($result['lyricsWords'])) {
    $output .= "W: " . implode(' ', $result['lyricsWords']) . "\n";
}

$logMsg = "Lyrics processing completed for tune X:$xnum\n";
if (!empty($result['lyricsWords'])) {
    $logMsg .= "Generated " . count($result['lyricsWords']) . " lyric words\n";
} else {
    $logMsg .= "No lyrics generated\n";
}

if (isset($cli->opts['v']) || isset($cli->opts['verbose'])) {
    $logMsg .= "Processed " . count($lines) . " input lines\n";
    $logMsg .= "Generated " . count($result['lines']) . " output lines\n";
}

// Output handling
if ($cli->outputFile) {
    CliOutputWriter::write($output, $cli->outputFile);
    $logMsg .= "Processed ABC with lyrics written to: {$cli->outputFile}\n";
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
    echo "ABC Lyrics Pass CLI Tool

Processes lyrics in ABC notation files, converting canntaireachd tokens to lyrics.
This is the second pass in the ABC processing pipeline.

Usage:
  php $script <abcfile> <tune_number> [options]

Arguments:
  abcfile       Path to the ABC file to process
  tune_number   X: header number of the tune to process

Options:
  -o, --output <file>      Output file for processed ABC (default: stdout)
  -e, --errorfile <file>   Output file for error messages and logs
  -h, --help               Show this help message
  -v, --verbose            Enable verbose output

Configuration Options:
  --config <file>          Load configuration from file (JSON/YAML/INI)
  --show-config            Display current configuration and exit
  --save-config <file>     Save current configuration to file and exit

Examples:
  php $script tunes.abc 1
  php $script tunes.abc 1 --output=with_lyrics.abc
  php $script tunes.abc 1 --verbose --errorfile=lyrics.log

Processing:
  - Converts canntaireachd tokens to human-readable lyrics
  - Adds W: lines with processed lyrics
  - Integrates lyrics with musical notation
";
}
