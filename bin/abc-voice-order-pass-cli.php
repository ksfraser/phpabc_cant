#!/usr/bin/env php
<?php
/**
 * ABC Voice Order Pass CLI Tool
 *
 * Reorders voices in ABC tunes according to predefined voice order preferences.
 * Bagpipes, Melody, and Harmony voices are prioritized, followed by other voices
 * in alphabetical order, then percussion voices (Snare, Tenor, Bass).
 *
 * Usage:
 *   php abc-voice-order-pass-cli.php <abcfile> <tune_number> [options]
 *
 * Arguments:
 *   abcfile       Path to the ABC file to process
 *   tune_number   The X: number of the tune to process
 *
 * Options:
 *   -o, --output <file>   Output file for processed ABC content
 *   -e, --errorfile <file> Output file for error messages and logs
 *   -h, --help            Show this help message
 *   -v, --verbose         Enable verbose output
 *
 * Examples:
 *   php abc-voice-order-pass-cli.php tunes.abc 1
 *   php abc-voice-order-pass-cli.php tunes.abc 5 --output=reordered.abc
 *   php abc-voice-order-pass-cli.php tunes.abc 10 --verbose --errorfile=voice.log
 *
 * Voice Order:
 *   1. Bagpipe voices
 *   2. Melody voices
 *   3. Harmony voices
 *   4. Other voices (alphabetical)
 *   5. Snare voices
 *   6. Tenor voices
 *   7. Bass voices
 */

require_once __DIR__ . '/../vendor/autoload.php';
use Ksfraser\PhpabcCanntaireachd\AbcVoiceOrderPass;
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

// Load voice order preferences from database
$voiceOrder = [];
$exclude = ['Bagpipe', 'Melody', 'Harmony', 'Snare', 'Tenor', 'Bass'];

try {
    $pdo = new PDO('sqlite:' . __DIR__ . '/../sql/abc_voice_order_defaults_schema.sql');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("SELECT voice_name, order_position FROM voice_order_defaults ORDER BY order_position");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $voiceOrder[$row['voice_name']] = $row['order_position'];
    }
} catch (Exception $e) {
    $msg = "Warning: Could not load voice order preferences from database: " . $e->getMessage() . "\n";
    if ($cli->errorFile) {
        CliOutputWriter::write($msg, $cli->errorFile);
    } else {
        fwrite(STDERR, $msg);
    }
}

$abcContent = file_get_contents($file);
$parser = new AbcFileParser();
$tunes = $parser->parse($abcContent);

$targetTune = null;
foreach ($tunes as $tune) {
    $headers = $tune->getHeaders();
    if (isset($headers['X']) && $headers['X']->get() == $xnum) {
        $targetTune = $tune;
        break;
    }
}

if (!$targetTune) {
    $msg = "Error: Tune number $xnum not found in file\n";
    if ($cli->errorFile) {
        CliOutputWriter::write($msg, $cli->errorFile);
    } else {
        fwrite(STDERR, $msg);
    }
    exit(1);
}

// Extract voice lines from the tune
$lines = [];
foreach ($targetTune->getLines() as $lineObj) {
    if (method_exists($lineObj, 'render')) {
        $line = trim($lineObj->render());
        if ($line !== '') $lines[] = $line;
    }
}

// Custom voice order logic
function reorderVoices($lines, $voiceOrder, $exclude) {
    $bagpipes = [];
    $melody = [];
    $harmony = [];
    $snare = [];
    $tenor = [];
    $bass = [];
    $other = [];

    foreach ($lines as $line) {
        if (preg_match('/^V:.*Bagpipe/i', $line)) {
            $bagpipes[] = $line;
        } elseif (preg_match('/^V:.*Melody/i', $line)) {
            $melody[] = $line;
        } elseif (preg_match('/^V:.*Harmony/i', $line)) {
            $harmony[] = $line;
        } elseif (preg_match('/^V:.*Snare/i', $line)) {
            $snare[] = $line;
        } elseif (preg_match('/^V:.*Tenor/i', $line)) {
            $tenor[] = $line;
        } elseif (preg_match('/^V:.*Bass/i', $line)) {
            $bass[] = $line;
        } else {
            // Try to extract voice name
            if (preg_match('/^V:\s*([^\s]+)/', $line, $m)) {
                $voice = $m[1];
                $other[] = ['line' => $line, 'voice' => $voice];
            } else {
                $other[] = ['line' => $line, 'voice' => null];
            }
        }
    }

    // Sort 'other' by voice order, excluding main and percussion voices
    $other = array_filter($other, function($v) use ($exclude) {
        return !in_array($v['voice'], $exclude, true);
    });
    usort($other, function($a, $b) use ($voiceOrder) {
        $aOrder = isset($voiceOrder[$a['voice']]) ? $voiceOrder[$a['voice']] : 99;
        $bOrder = isset($voiceOrder[$b['voice']]) ? $voiceOrder[$b['voice']] : 99;
        return $aOrder <=> $bOrder;
    });
    $otherLines = array_map(function($v) { return $v['line']; }, $other);

    return array_merge($bagpipes, $melody, $harmony, $otherLines, $snare, $tenor, $bass);
}

$reorderedLines = reorderVoices($lines, $voiceOrder, $exclude);

// Reconstruct the tune with reordered voices
$output = '';
$headers = $targetTune->getHeaders();
foreach ($headers as $key => $headerObj) {
    $val = $headerObj->get();
    if ($val !== '') $output .= "$key:$val\n";
}
$output .= "\n";
$output .= implode("\n", $reorderedLines) . "\n";

$logMsg = "Voice order reordering completed for tune $xnum\n";
$logMsg .= "✓ Reordered " . count($reorderedLines) . " voice lines\n";

if (isset($cli->opts['v']) || isset($cli->opts['verbose'])) {
    $logMsg .= "✓ Voice order applied: Bagpipe → Melody → Harmony → Other → Snare → Tenor → Bass\n";
}

if ($cli->outputFile) {
    CliOutputWriter::write($output, $cli->outputFile);
    $logMsg .= "✓ Output written to: {$cli->outputFile}\n";
    if ($cli->errorFile) {
        CliOutputWriter::write($logMsg, $cli->errorFile);
    } else {
        echo $logMsg;
    }
} else {
    echo $output;
    if ($cli->errorFile) {
        CliOutputWriter::write($logMsg, $cli->errorFile);
    }
}

function showUsage() {
    global $argv;
    $script = basename($argv[0]);
    echo "ABC Voice Order Pass CLI Tool

Reorders voices in ABC tunes according to predefined voice order preferences.
Bagpipes, Melody, and Harmony voices are prioritized, followed by other voices
in alphabetical order, then percussion voices (Snare, Tenor, Bass).

Usage:
  php $script <abcfile> <tune_number> [options]

Arguments:
  abcfile       Path to the ABC file to process
  tune_number   The X: number of the tune to process

Options:
  -o, --output <file>   Output file for processed ABC content
  -e, --errorfile <file> Output file for error messages and logs
  -h, --help            Show this help message
  -v, --verbose         Enable verbose output

Examples:
  php $script tunes.abc 1
  php $script tunes.abc 5 --output=reordered.abc
  php $script tunes.abc 10 --verbose --errorfile=voice.log

Voice Order:
  1. Bagpipe voices
  2. Melody voices
  3. Harmony voices
  4. Other voices (alphabetical)
  5. Snare voices
  6. Tenor voices
  7. Bass voices
";
}
