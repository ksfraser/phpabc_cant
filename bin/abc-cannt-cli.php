#!/usr/bin/env php
<?php
/**
 * ABC Canntaireachd CLI Tool
 *
 * Main CLI launcher for ABC Canntaireachd processing tools.
 * Processes ABC files with canntaireachd conversion, validation, and voice management.
 *
 * Usage:
 *   php abc-cannt-cli.php --file <abcfile> [options]
 *
 * Required Arguments:
 *   --file, -f <abcfile>    Path to ABC file to process
 *
 * Options:
 *   --convert, -c           Add canntaireachd lines to melody/bagpipe voices
 *   --output, -o <file>     Output file for processed ABC content
 *   --errorfile, -e <file>  Output file for error messages and logs
 *   --canntdiff, -d <file>  Output file for canntaireachd differences
 *   --update_voice_names_from_midi, -u  Update voice names based on MIDI instrument assignments
 *   -h, --help              Show this help message
 *   -v, --verbose           Enable verbose output
 *
 * Examples:
 *   php abc-cannt-cli.php --file tune.abc
 *   php abc-cannt-cli.php --file tune.abc --convert --output processed.abc
 *   php abc-cannt-cli.php --file tune.abc --canntdiff diff.txt --errorfile log.txt
 *   php abc-cannt-cli.php --file tune.abc --update_voice_names_from_midi --verbose
 *
 * Processing:
 *   - Validates ABC file syntax and structure
 *   - Processes canntaireachd tokens if --convert is specified
 *   - Updates voice names from MIDI assignments if requested
 *   - Outputs processed ABC content to file or stdout
 *   - Logs errors, differences, and processing status
 */

require __DIR__ . '/../vendor/autoload.php';
use Ksfraser\PhpabcCanntaireachd\CliOutputWriter;
use Ksfraser\PhpabcCanntaireachd\CLIOptions;
use Ksfraser\PhpabcCanntaireachd\AbcProcessor;
use Ksfraser\PhpabcCanntaireachd\TokenDictionary;
use Ksfraser\PhpabcCanntaireachd\AbcValidator;

// Parse command line arguments
$cli = CLIOptions::fromArgv($argv);

// Show help if requested
if (isset($cli->opts['h']) || isset($cli->opts['help'])) {
    showUsage();
    exit(0);
}

$file = $cli->file;
$convert = $cli->convert;
$outputFile = $cli->outputFile;
$errorFile = $cli->errorFile;
$canntDiffFile = $cli->canntDiffFile;
$updateVoiceNamesFromMidi = $cli->updateVoiceNamesFromMidi;
$verbose = isset($cli->opts['v']) || isset($cli->opts['verbose']);

if (!$file) {
    showUsage();
    exit(1);
}

if (!file_exists($file)) {
    $msg = "Error: ABC file '$file' not found\n";
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

// Load token dictionary for canntaireachd processing
$dict = new TokenDictionary();
// Try to load legacy abc_dict.php if available
$legacyPath = __DIR__ . '/../src/Ksfraser/PhpabcCanntaireachd/abc_dict.php';
if (file_exists($legacyPath)) {
    // include inside isolated scope to avoid leaking symbols
    $abc = [];
    try {
        include $legacyPath; // populates $abc in many legacy files
    } catch (\Throwable $e) {
        // ignore
    }
    if (!empty($abc) && is_array($abc)) {
        $pre = [];
        foreach ($abc as $k => $v) {
            $pre[$k] = [
                'cannt_token' => $v['cannt'] ?? ($v['cannt_token'] ?? null),
                'bmw_token' => $v['bmw'] ?? null,
                'description' => $v['desc'] ?? null,
            ];
        }
        $dict->prepopulate($pre);
    }
}

$config = [
    'updateVoiceNamesFromMidi' => $updateVoiceNamesFromMidi
];
$result = AbcProcessor::process($abcContent, $dict);

$outputMsg = "ABC Canntaireachd processing completed\n";
$outputMsg .= "✓ File: $file\n";

if ($convert) {
    $outputMsg .= "✓ Canntaireachd processing: enabled\n";
} else {
    $outputMsg .= "✓ Canntaireachd processing: disabled\n";
}

if ($updateVoiceNamesFromMidi) {
    $outputMsg .= "✓ Voice name updates from MIDI: enabled\n";
}

// Handle processing results
$processedLines = $result['lines'];
$canntDiff = $result['canntDiff'] ?? [];
$processingErrors = $result['errors'] ?? [];

// Add processing errors to the main errors array
if (!empty($processingErrors)) {
    $errors = array_merge($errors, $processingErrors);
}

// Log canntaireachd differences if any
if (!empty($canntDiff)) {
    $outputMsg .= "✓ Canntaireachd differences found: " . count($canntDiff) . "\n";
    if ($verbose) {
        $outputMsg .= "Differences:\n";
        foreach ($canntDiff as $diff) {
            $outputMsg .= "  - $diff\n";
        }
    }

    // Write canntaireachd diff to separate file if specified
    if ($canntDiffFile) {
        CliOutputWriter::write(implode("\n", $canntDiff), $canntDiffFile);
        $outputMsg .= "✓ Canntaireachd differences written to: $canntDiffFile\n";
    }
} else {
    $outputMsg .= "✓ Canntaireachd differences: none\n";
}

// Always log validation errors if present
if ($errors) {
    $errorMsg = "Validation errors found:\n";
    foreach ($errors as $err) {
        $errorMsg .= "  - $err\n";
    }
    $errorMsg .= "✓ Total errors: " . count($errors) . "\n";

    if ($errorFile) {
        CliOutputWriter::write($errorMsg, $errorFile);
    } else {
        echo $errorMsg;
    }
} else {
    $outputMsg .= "✓ Validation: passed\n";
}

// Write processed ABC result to output file if requested
if ($outputFile) {
    CliOutputWriter::write(implode("\n", $processedLines), $outputFile);
    $outputMsg .= "✓ Output written to: $outputFile\n";

    if ($errorFile) {
        CliOutputWriter::write($outputMsg, $errorFile);
    } else {
        echo $outputMsg;
    }
} else {
    echo implode("\n", $processedLines) . "\n";

    if ($errorFile) {
        CliOutputWriter::write($outputMsg, $errorFile);
    } else {
        echo $outputMsg;
    }
}

function showUsage() {
    global $argv;
    $script = basename($argv[0]);
    echo "ABC Canntaireachd CLI Tool

Main CLI launcher for ABC Canntaireachd processing tools.
Processes ABC files with canntaireachd conversion, validation, and voice management.

Usage:
  php $script --file <abcfile> [options]

Required Arguments:
  --file, -f <abcfile>    Path to ABC file to process

Options:
  --convert, -c           Add canntaireachd lines to melody/bagpipe voices
  --output, -o <file>     Output file for processed ABC content
  --errorfile, -e <file>  Output file for error messages and logs
  --canntdiff, -d <file>  Output file for canntaireachd differences
  --update_voice_names_from_midi, -u  Update voice names based on MIDI instrument assignments
  -h, --help              Show this help message
  -v, --verbose           Enable verbose output

Examples:
  php $script --file tune.abc
  php $script --file tune.abc --convert --output processed.abc
  php $script --file tune.abc --canntdiff diff.txt --errorfile log.txt
  php $script --file tune.abc --update_voice_names_from_midi --verbose

Processing:
  - Validates ABC file syntax and structure
  - Processes canntaireachd tokens if --convert is specified
  - Updates voice names from MIDI assignments if requested
  - Outputs processed ABC content to file or stdout
  - Logs errors, differences, and processing status
";
}
