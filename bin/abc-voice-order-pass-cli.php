#!/usr/bin/env php
<?php
/**
 * ABC Voice Order Pass CLI Tool
 *
 * Reorders voices in ABC tunes according to voice ordering strategies.
 * Supports three ordering modes:
 *   - source: Preserve original voice order (default)
 *   - orchestral: Order by orchestral score conventions (woodwinds, brass, percussion, strings, etc.)
 *   - custom: User-defined voice order from configuration file
 *
 * Usage:
 *   php abc-voice-order-pass-cli.php <abcfile> <tune_number> [options]
 *
 * Arguments:
 *   abcfile       Path to the ABC file to process
 *   tune_number   The X: number of the tune to process
 *
 * Options:
 *   -o, --output <file>        Output file for processed ABC content
 *   -e, --errorfile <file>     Output file for error messages and logs
 *   --voice-order <mode>       Voice ordering mode: source, orchestral, custom (default: source)
 *   --config <file>            Configuration file (JSON, YAML, or INI format)
 *   --show-config              Display current configuration and exit
 *   --save-config <file>       Save current configuration to file
 *   -h, --help                 Show this help message
 *   -v, --verbose              Enable verbose output
 *
 * Examples:
 *   php abc-voice-order-pass-cli.php tunes.abc 1
 *   php abc-voice-order-pass-cli.php tunes.abc 5 --voice-order=orchestral --output=reordered.abc
 *   php abc-voice-order-pass-cli.php tunes.abc 10 --voice-order=custom --config=voice_order.json
 *   php abc-voice-order-pass-cli.php tunes.abc 1 --show-config
 *   php abc-voice-order-pass-cli.php tunes.abc 1 --save-config=my_config.json
 *
 * Voice Ordering Modes:
 *   source:      Preserve original voice order from ABC file
 *   orchestral:  Order by orchestral conventions (woodwinds → brass → percussion → strings)
 *   custom:      User-defined order specified in configuration file
 */

require_once __DIR__ . '/../vendor/autoload.php';
use Ksfraser\PhpabcCanntaireachd\AbcVoiceOrderPass;
use Ksfraser\PhpabcCanntaireachd\AbcProcessorConfig;
use Ksfraser\PhpabcCanntaireachd\CliOutputWriter;
use Ksfraser\PhpabcCanntaireachd\CLIOptions;
use Ksfraser\PhpabcCanntaireachd\Config\ConfigLoader;
use Ksfraser\PhpabcCanntaireachd\Config\ConfigMerger;

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

// Initialize configuration
$config = new AbcProcessorConfig();

// Load configuration file if specified
if ($cli->configFile !== null) {
    $configFile = $cli->configFile;
    if (!file_exists($configFile)) {
        $msg = "Error: Configuration file '$configFile' not found\n";
        if ($cli->errorFile) {
            CliOutputWriter::write($msg, $cli->errorFile);
        } else {
            fwrite(STDERR, $msg);
        }
        exit(1);
    }
    
    try {
        $fileConfig = ConfigLoader::loadFromFile($configFile);
        
        // Apply config values to AbcProcessorConfig object
        if (isset($fileConfig['voiceOrderingMode'])) {
            $config->voiceOrderingMode = $fileConfig['voiceOrderingMode'];
        }
        if (isset($fileConfig['customVoiceOrder'])) {
            $config->customVoiceOrder = $fileConfig['customVoiceOrder'];
        }
    } catch (Exception $e) {
        $msg = "Error loading configuration: " . $e->getMessage() . "\n";
        if ($cli->errorFile) {
            CliOutputWriter::write($msg, $cli->errorFile);
        } else {
            fwrite(STDERR, $msg);
        }
        exit(1);
    }
}

// Apply CLI options (override config file)
if ($cli->voiceOrderMode !== null) {
    $config->voiceOrderingMode = $cli->voiceOrderMode;
}

// Show configuration if requested
if ($cli->showConfig) {
    echo "Current Configuration:\n";
    echo str_repeat('=', 50) . "\n";
    echo "Voice Ordering Mode: " . ($config->voiceOrderingMode ?? 'source') . "\n";
    if (isset($config->customVoiceOrder) && is_array($config->customVoiceOrder)) {
        echo "Custom Voice Order: " . implode(', ', $config->customVoiceOrder) . "\n";
    }
    echo str_repeat('=', 50) . "\n";
    exit(0);
}

// Save configuration if requested
if ($cli->saveConfigFile !== null) {
    $saveFile = $cli->saveConfigFile;
    $configData = [
        'voiceOrderingMode' => $config->voiceOrderingMode ?? 'source',
    ];
    if (isset($config->customVoiceOrder)) {
        $configData['customVoiceOrder'] = $config->customVoiceOrder;
    }
    
    $format = strtolower(pathinfo($saveFile, PATHINFO_EXTENSION));
    try {
        // Write config based on format
        if ($format === 'json') {
            $json = json_encode($configData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            file_put_contents($saveFile, $json);
        } elseif ($format === 'ini') {
            $ini = "";
            foreach ($configData as $key => $value) {
                if (is_array($value)) {
                    $ini .= "$key = \"" . implode(',', $value) . "\"\n";
                } else {
                    $ini .= "$key = \"$value\"\n";
                }
            }
            file_put_contents($saveFile, $ini);
        } else {
            throw new Exception("Unsupported format: $format (use .json or .ini)");
        }
        
        $msg = "Configuration saved to: $saveFile\n";
        if ($cli->errorFile) {
            CliOutputWriter::write($msg, $cli->errorFile);
        } else {
            echo $msg;
        }
        exit(0);
    } catch (Exception $e) {
        $msg = "Error saving configuration: " . $e->getMessage() . "\n";
        if ($cli->errorFile) {
            CliOutputWriter::write($msg, $cli->errorFile);
        } else {
            fwrite(STDERR, $msg);
        }
        exit(1);
    }
}

// Read ABC file
$abcContent = file_get_contents($file);
$lines = explode("\n", $abcContent);

// Find the target tune
$tuneStart = null;
$tuneEnd = null;
$inTune = false;

foreach ($lines as $i => $line) {
    if (preg_match('/^X:\s*' . preg_quote($xnum, '/') . '\s*$/', $line)) {
        $tuneStart = $i;
        $inTune = true;
        continue;
    }
    if ($inTune && preg_match('/^X:\s*\d+\s*$/', $line)) {
        $tuneEnd = $i - 1;
        break;
    }
}

if ($tuneStart === null) {
    $msg = "Error: Tune number $xnum not found in file\n";
    if ($cli->errorFile) {
        CliOutputWriter::write($msg, $cli->errorFile);
    } else {
        fwrite(STDERR, $msg);
    }
    exit(1);
}

if ($tuneEnd === null) {
    $tuneEnd = count($lines) - 1;
}

// Extract tune lines
$tuneLines = array_slice($lines, $tuneStart, $tuneEnd - $tuneStart + 1);

// Process with voice ordering
$pass = new AbcVoiceOrderPass(null, $config);
$processedLines = $pass->process($tuneLines);

// Reconstruct output
$output = implode("\n", $processedLines) . "\n";

$logMsg = "Voice order processing completed for tune $xnum\n";
$mode = $config->voiceOrderingMode ?? 'source';
$logMsg .= "✓ Ordering mode: $mode\n";

if (isset($cli->opts['v']) || isset($cli->opts['verbose'])) {
    $logMsg .= "✓ Processed " . count($processedLines) . " lines\n";
    if ($mode === 'orchestral') {
        $logMsg .= "✓ Order: Woodwinds → Brass → Percussion → Strings → Keyboards → Vocals → Bagpipes\n";
    } elseif ($mode === 'custom' && isset($config->customVoiceOrder)) {
        $logMsg .= "✓ Custom order: " . implode(' → ', $config->customVoiceOrder) . "\n";
    } elseif ($mode === 'source') {
        $logMsg .= "✓ Preserved original voice order\n";
    }
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

Reorders voices in ABC tunes according to voice ordering strategies.
Supports three ordering modes:
  - source: Preserve original voice order (default)
  - orchestral: Order by orchestral score conventions
  - custom: User-defined voice order from configuration file

Usage:
  php $script <abcfile> <tune_number> [options]

Arguments:
  abcfile       Path to the ABC file to process
  tune_number   The X: number of the tune to process

Options:
  -o, --output <file>        Output file for processed ABC content
  -e, --errorfile <file>     Output file for error messages and logs
  --voice-order <mode>       Voice ordering mode: source, orchestral, custom
  --config <file>            Configuration file (JSON, YAML, or INI)
  --show-config              Display current configuration and exit
  --save-config <file>       Save current configuration to file
  -h, --help                 Show this help message
  -v, --verbose              Enable verbose output

Examples:
  php $script tunes.abc 1
  php $script tunes.abc 5 --voice-order=orchestral --output=reordered.abc
  php $script tunes.abc 10 --voice-order=custom --config=voice_order.json
  php $script tunes.abc 1 --show-config
  php $script tunes.abc 1 --save-config=my_config.json

Voice Ordering Modes:
  source:      Preserve original voice order from ABC file
  orchestral:  Order by orchestral conventions (woodwinds → brass → 
               percussion → strings → keyboards → vocals → bagpipes)
  custom:      User-defined order specified in configuration file

Configuration File Format (JSON example):
  {
    \"voiceOrderingMode\": \"custom\",
    \"customVoiceOrder\": [\"Bagpipes\", \"Tenor\", \"Snare\", \"Bass\", \"Piano\"]
  }
";
}
