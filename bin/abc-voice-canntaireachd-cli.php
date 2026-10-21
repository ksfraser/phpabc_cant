#!/usr/bin/env php
<?php
/**
 * ABC Voice Canntaireachd CLI Tool
 *
 * Converts the target voices of an ABC file to canntaireachd and writes the
 * result as "w:" lines directly beneath the ABC line each one belongs to.
 *
 * A voice is a target when its identifier ("V:Bagpipes") or its declared name
 * ("V:M name=\"Melody\"") is Melody, Bagpipes or Pipes, case-insensitively.
 * Every music line of every target voice is processed.
 *
 * The rest of the file is left untouched: every input line is emitted
 * unchanged, in place.
 *
 * Usage:
 *   php abc-voice-canntaireachd-cli.php --file <abcfile> [options]
 *
 * Options:
 *   -f, --file <abcfile>     Path to the ABC file to process (required)
 *   -o, --output <file>      Output file for the processed ABC (default: stdout)
 *   -e, --errorfile <file>   Output file for the summary/log messages
 *   -d, --canntdiff <file>   Output file for the generated canntaireachd
 *   --voice <name>           Voice name to treat as a target (repeatable)
 *   -h, --help               Show this help message
 *   -v, --verbose            Enable verbose output
 *
 * Examples:
 *   php abc-voice-canntaireachd-cli.php --file tunes.abc
 *   php abc-voice-canntaireachd-cli.php --file tunes.abc --output=cannt.abc
 *   php abc-voice-canntaireachd-cli.php --file tunes.abc --voice=Chanter
 *
 * @package ksfraser\PhpabcCanntaireachd
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use ksfraser\PhpabcCanntaireachd\AbcCanntaireachdPass;
use ksfraser\PhpabcCanntaireachd\CliOutputWriter;
use ksfraser\PhpabcCanntaireachd\TokenDictionary;

$options = getopt('f:o:e:d:hv', ['file:', 'output:', 'errorfile:', 'canntdiff:', 'voice:', 'help', 'verbose']);

if (isset($options['h']) || isset($options['help'])) {
    showUsage();
    exit(0);
}

$file = $options['file'] ?? null;
if ($file === null) {
    fwrite(STDERR, "Error: --file is required\n");
    showUsage();
    exit(1);
}
if (!file_exists($file)) {
    fwrite(STDERR, "Error: ABC file '$file' not found\n");
    exit(1);
}

$outputFile = $options['output'] ?? null;
$errorFile = $options['errorfile'] ?? null;
$canntDiffFile = $options['canntdiff'] ?? null;
$verbose = isset($options['v']) || isset($options['verbose']);

/** @var string[] $targetVoices */
$targetVoices = [];
if (isset($options['voice'])) {
    foreach ((array) $options['voice'] as $voice) {
        $targetVoices[] = (string) $voice;
    }
}

$content = file_get_contents($file);
if ($content === false) {
    fwrite(STDERR, "Error: could not read '$file'\n");
    exit(1);
}

$lines = preg_split('/\r?\n/', $content);
if ($lines === false) {
    $lines = [];
}
// A trailing newline produces a final empty element; keep the file shape intact.
if (count($lines) > 1 && end($lines) === '') {
    array_pop($lines);
}

$pass = new AbcCanntaireachdPass(new TokenDictionary(), $targetVoices === [] ? null : $targetVoices);
$result = $pass->process($lines);

$output = implode("\n", $result['lines']);

$summary = "ABC voice canntaireachd processing completed\n";
$summary .= "File: $file\n";
$summary .= 'Target voices: ' . ($targetVoices === [] ? 'Melody, Bagpipes, Pipes (default)' : implode(', ', $targetVoices)) . "\n";
$summary .= 'Generated canntaireachd lines: ' . count($result['canntDiff']) . "\n";
if ($verbose) {
    $summary .= 'Input lines: ' . count($lines) . "\n";
    $summary .= 'Output lines: ' . count($result['lines']) . "\n";
}

if ($canntDiffFile !== null) {
    $diffLines = [];
    foreach ($result['canntDiff'] as $entry) {
        $diffLines[] = $entry['line'] . "\n    => w: " . $entry['generated'];
    }
    CliOutputWriter::write(implode("\n", $diffLines) . "\n", $canntDiffFile);
    $summary .= "Canntaireachd differences written to: $canntDiffFile\n";
}

if ($outputFile !== null) {
    CliOutputWriter::write($output . "\n", $outputFile);
    $summary .= "Output written to: $outputFile\n";
}

if ($errorFile !== null) {
    CliOutputWriter::write($summary, $errorFile);
} elseif ($outputFile === null) {
    echo $output . "\n";
    echo $summary;
} else {
    echo $summary;
}

exit(0);

function showUsage(): void
{
    $script = basename($argv[0]);
    echo <<<USAGE
    ABC Voice Canntaireachd CLI Tool

    Converts the target voices of an ABC file to canntaireachd and writes the
    result as "w:" lines beneath the ABC line each one belongs to.

    Usage:
      php $script --file <abcfile> [options]

    Options:
      -f, --file <abcfile>     Path to the ABC file to process (required)
      -o, --output <file>      Output file for the processed ABC (default: stdout)
      -e, --errorfile <file>   Output file for the summary/log messages
      -d, --canntdiff <file>   Output file for the generated canntaireachd
      --voice <name>           Voice name to treat as a target (repeatable)
      -h, --help               Show this help message
      -v, --verbose            Enable verbose output

    Examples:
      php $script --file tunes.abc
      php $script --file tunes.abc --output=cannt.abc
      php $script --file tunes.abc --voice=Chanter

    USAGE;
}
