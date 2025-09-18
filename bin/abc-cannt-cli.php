#!/usr/bin/env php
<?php
/**
 * CLI launcher for ABC Canntaireachd tools
 *
 * Usage:
 *   php bin/abc-cannt-cli.php --file=path/to/file.abc [--convert] [--update_voice_names_from_midi] [--canntdiff=path/to/diff.txt]
 *
 * Options:
 *   --file, -f      Path to ABC file to process
 *   --convert, -c   Add canntaireachd lines to melody/bagpipe voices
 *   --canntdiff, -d Path to output file for canntaireachd differences
 *   --update_voice_names_from_midi, -u   Update voice names based on MIDI instrument assignments
 */

require __DIR__ . '/../vendor/autoload.php';
use Ksfraser\PhpabcCanntaireachd\CliOutputWriter;
use Ksfraser\PhpabcCanntaireachd\CLIOptions;

use Ksfraser\PhpabcCanntaireachd\AbcProcessor;
use Ksfraser\PhpabcCanntaireachd\TokenDictionary;
use Ksfraser\PhpabcCanntaireachd\AbcValidator;



$cli = CLIOptions::fromArgv($argv);
$file = $cli->file;
$convert = $cli->convert;
$outputFile = $cli->outputFile;
$errorFile = $cli->errorFile;
$canntDiffFile = $cli->canntDiffFile;
$updateVoiceNamesFromMidi = $cli->updateVoiceNamesFromMidi;


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

$outputMsg = "File '$file' processed and validated.\n";
if ($convert) {
    $outputMsg .= "Canntaireachd processing enabled.\n";
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
    $outputMsg .= "Canntaireachd differences found:\n";
    foreach ($canntDiff as $diff) {
        $outputMsg .= "  - $diff\n";
    }
    
    // Write canntaireachd diff to separate file if specified
    if ($canntDiffFile) {
        CliOutputWriter::write(implode("\n", $canntDiff), $canntDiffFile);
        $outputMsg .= "Canntaireachd differences written to: $canntDiffFile\n";
    }
}

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
    CliOutputWriter::write(implode("\n", $processedLines), $outputFile);
    // Write log/status to errorfile ONLY
    if ($errorFile) {
        CliOutputWriter::write($outputMsg, $errorFile);
    } else {
        echo $outputMsg;
    }
} else {
    // Write processed ABC to stdout ONLY
    echo implode("\n", $processedLines) . "\n";
    // Write log/status to errorfile or stdout
    if ($errorFile) {
        CliOutputWriter::write($outputMsg, $errorFile);
    } else {
        echo $outputMsg;
    }
}
