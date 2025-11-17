#!/usr/bin/env php
<?php
/**
 * ABC MIDI Defaults CLI Tool
 *
 * Manages MIDI channel and program defaults for ABC voices in the database.
 * Supports listing, adding, editing, and deleting voice MIDI settings.
 *
 * Usage:
 *   php abc-midi-defaults-cli.php <command> [options]
 *
 * Commands:
 *   --list                    List all voice MIDI defaults
 *   --add <voice>             Add a new voice MIDI default
 *   --edit <voice>            Edit an existing voice MIDI default
 *   --delete <voice>          Delete a voice MIDI default
 *   --validate <file.abc>     Validate ABC file with MIDI defaults
 *   --save <file.abc>         Process and save ABC file with MIDI defaults
 *
 * Options:
 *   --midi_channel <N>        MIDI channel number (0-15)
 *   --midi_program <N>        MIDI program number (0-127)
 *   --voice_output_style <s>  Voice output style for processing
 *   --interleave_bars <N>     Bars to interleave
 *   --bars_per_line <N>       Bars per line in output
 *   --join_bars_with_backslash Join bars with backslash
 *   --mysql_user <user>       MySQL username
 *   --mysql_pass <pass>       MySQL password
 *   --mysql_db <db>           MySQL database name
 *   --mysql_host <host>       MySQL host (default: localhost)
 *   --mysql_port <port>       MySQL port (default: 3306)
 *   -e, --errorfile <file>    Output file for messages and errors
 *   -h, --help                Show this help message
 *
 * Examples:
 *   php abc-midi-defaults-cli.php --list
 *   php abc-midi-defaults-cli.php --add Bagpipe --midi_channel 1 --midi_program 109
 *   php abc-midi-defaults-cli.php --edit Melody --midi_program 25
 *   php abc-midi-defaults-cli.php --delete Drums
 *   php abc-midi-defaults-cli.php --validate tune.abc
 *   php abc-midi-defaults-cli.php --save tune.abc --errorfile=process.log
 */

require_once __DIR__ . '/../vendor/autoload.php';
use Ksfraser\PhpabcCanntaireachd\AbcProcessorConfig;

// Parse command line arguments
$cli = \Ksfraser\PhpabcCanntaireachd\CLIOptions::fromArgv($argv);

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

// Load database configuration
if (class_exists('Symfony\Component\Runtime\Secrets\getSecret')) {
    $getSecret = \Closure::fromCallable(['Symfony\Component\Runtime\Secrets', 'getSecret']);
    $env = getenv();
    $config = [
        'mysql_user' => $env['MYSQL_USER'] ?? $getSecret('MYSQL_USER') ?? null,
        'mysql_pass' => $env['MYSQL_PASS'] ?? $getSecret('MYSQL_PASS') ?? null,
        'mysql_db'   => $env['MYSQL_DB']   ?? $getSecret('MYSQL_DB')   ?? null,
        'mysql_host' => $env['MYSQL_HOST'] ?? $getSecret('MYSQL_HOST') ?? 'localhost',
        'mysql_port' => $env['MYSQL_PORT'] ?? $getSecret('MYSQL_PORT') ?? 3306,
    ];
    // Fallback to config/db_config.php if any are missing
    $fallback = require __DIR__ . '/../config/db_config.php';
    foreach ($fallback as $k => $v) {
        if (!isset($config[$k]) || $config[$k] === null) $config[$k] = $v;
    }
} else {
    $config = require __DIR__ . '/../config/db_config.php';
}

// Override config with CLI options if provided
if (isset($cli->opts['mysql_user'])) $config['mysql_user'] = $cli->opts['mysql_user'];
if (isset($cli->opts['mysql_pass'])) $config['mysql_pass'] = $cli->opts['mysql_pass'];
if (isset($cli->opts['mysql_db']))   $config['mysql_db']   = $cli->opts['mysql_db'];
if (isset($cli->opts['mysql_host'])) $config['mysql_host'] = $cli->opts['mysql_host'];
if (isset($cli->opts['mysql_port'])) $config['mysql_port'] = $cli->opts['mysql_port'];

$config['dsn'] = "mysql:host={$config['mysql_host']};port={$config['mysql_port']};dbname={$config['mysql_db']};charset=utf8mb4";

try {
    $pdo = new PDO($config['dsn'], $config['mysql_user'], $config['mysql_pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    $msg = "Error: Database connection failed: " . $e->getMessage() . "\n";
    if ($cli->errorFile) {
        \Ksfraser\PhpabcCanntaireachd\CliOutputWriter::write($msg, $cli->errorFile);
    } else {
        fwrite(STDERR, $msg);
    }
    exit(1);
}

$errorFile = $cli->errorFile;

// Check if table exists, create/populate if missing
$table = 'abc_midi_defaults';
$tableExists = false;
try {
    $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
    $tableExists = $stmt->fetchColumn() !== false;
} catch (Exception $e) {
    $tableExists = false;
}

if (!$tableExists) {
    $schemaFile = __DIR__ . '/../sql/abc_midi_defaults_schema.sql';
    if (file_exists($schemaFile)) {
        $sql = file_get_contents($schemaFile);
        foreach (explode(';', $sql) as $query) {
            $query = trim($query);
            if ($query) $pdo->exec($query);
        }
        $msg = "Created and populated $table from schema.\n";
        if ($errorFile) {
            \Ksfraser\PhpabcCanntaireachd\CliOutputWriter::write($msg, $errorFile);
        } else {
            echo $msg;
        }
    } else {
        $msg = "Error: Schema file $schemaFile not found.\n";
        if ($errorFile) {
            \Ksfraser\PhpabcCanntaireachd\CliOutputWriter::write($msg, $errorFile);
        } else {
            fwrite(STDERR, $msg);
        }
        exit(1);
    }
}

// Support multiple files via wildcard for validate/save
$abcFiles = [];
if (isset($cli->opts['validate'])) {
    $abcFiles = glob($cli->opts['validate']);
} elseif (isset($cli->opts['save'])) {
    $abcFiles = glob($cli->opts['save']);
}

if (isset($cli->opts['list'])) {
    $stmt = $pdo->query("SELECT * FROM $table ORDER BY voice_name");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $msg = "MIDI Defaults:\n";
    foreach ($rows as $row) {
        $msg .= "  {$row['voice_name']}: Channel {$row['midi_channel']}, Program {$row['midi_program']}\n";
    }
    $msg .= "✓ Listed " . count($rows) . " voice MIDI default(s)\n";

    if ($errorFile) {
        \Ksfraser\PhpabcCanntaireachd\CliOutputWriter::write($msg, $errorFile);
    } else {
        echo $msg;
    }

} elseif (isset($cli->opts['add'])) {
    $voice = $cli->opts['add'];
    $channel = $cli->opts['midi_channel'] ?? 0;
    $program = $cli->opts['midi_program'] ?? 0;

    $stmt = $pdo->prepare("INSERT INTO $table (voice_name, midi_channel, midi_program) VALUES (?, ?, ?)");
    $stmt->execute([$voice, $channel, $program]);

    $msg = "MIDI defaults added for voice '$voice'\n";
    $msg .= "✓ Channel: $channel, Program: $program\n";

    if ($errorFile) {
        \Ksfraser\PhpabcCanntaireachd\CliOutputWriter::write($msg, $errorFile);
    } else {
        echo $msg;
    }

} elseif (isset($cli->opts['edit'])) {
    $voice = $cli->opts['edit'];
    $channel = $cli->opts['midi_channel'] ?? null;
    $program = $cli->opts['midi_program'] ?? null;

    $updates = [];
    if ($channel !== null) {
        $stmt = $pdo->prepare("UPDATE $table SET midi_channel=? WHERE voice_name=?");
        $stmt->execute([$channel, $voice]);
        $updates[] = "channel=$channel";
    }
    if ($program !== null) {
        $stmt = $pdo->prepare("UPDATE $table SET midi_program=? WHERE voice_name=?");
        $stmt->execute([$program, $voice]);
        $updates[] = "program=$program";
    }

    $msg = "MIDI defaults updated for voice '$voice'\n";
    $msg .= "✓ Updated: " . implode(', ', $updates) . "\n";

    if ($errorFile) {
        \Ksfraser\PhpabcCanntaireachd\CliOutputWriter::write($msg, $errorFile);
    } else {
        echo $msg;
    }

} elseif (isset($cli->opts['delete'])) {
    $voice = $cli->opts['delete'];
    $stmt = $pdo->prepare("DELETE FROM $table WHERE voice_name=?");
    $result = $stmt->execute([$voice]);

    $msg = "MIDI defaults deleted for voice '$voice'\n";
    $msg .= "✓ Deleted " . $stmt->rowCount() . " record(s)\n";

    if ($errorFile) {
        \Ksfraser\PhpabcCanntaireachd\CliOutputWriter::write($msg, $errorFile);
    } else {
        echo $msg;
    }

} elseif (!empty($abcFiles)) {
    $configObj = new \Ksfraser\PhpabcCanntaireachd\AbcProcessorConfig();
    if (isset($cli->opts['voice_output_style'])) $configObj->voiceOutputStyle = $cli->opts['voice_output_style'];
    if (isset($cli->opts['interleave_bars'])) $configObj->interleaveBars = (int)$cli->opts['interleave_bars'];
    if (isset($cli->opts['bars_per_line'])) $configObj->barsPerLine = (int)$cli->opts['bars_per_line'];
    if (isset($cli->opts['join_bars_with_backslash'])) $configObj->joinBarsWithBackslash = true;

    $processedCount = 0;
    foreach ($abcFiles as $abcFile) {
        if (!file_exists($abcFile)) {
            $msg = "Warning: File '$abcFile' not found, skipping\n";
            if ($errorFile) {
                \Ksfraser\PhpabcCanntaireachd\CliOutputWriter::write($msg, $errorFile);
            } else {
                echo $msg;
            }
            continue;
        }

        $abcContent = file_get_contents($abcFile);
        $dict = include __DIR__ . '/../src/Ksfraser/PhpabcCanntaireachd/abc_dict.php';
        $result = \Ksfraser\PhpabcCanntaireachd\AbcProcessor::process($abcContent, $dict);

        $output = $result['lines'];
        $canntDiff = $result['canntDiff'];

        $newFile = preg_replace('/\.abc$/', '_processed.abc', $abcFile);
        file_put_contents($newFile, implode("\n", $output));

        $files = [$newFile];
        $msg = "Processed ABC file: $abcFile\n";
        $msg .= "✓ Output: $newFile\n";

        if ($canntDiff) {
            $diffFile = preg_replace('/\.abc$/', '_cannt_diff.txt', $abcFile);
            file_put_contents($diffFile, implode("\n", $canntDiff));
            $msg .= "✓ Canntaireachd diff: $diffFile\n";
            $files[] = $diffFile;
        }

        if (isset($result['errors']) && !empty($result['errors'])) {
            $errFile = preg_replace('/\.abc$/', '_errors.txt', $abcFile);
            file_put_contents($errFile, implode("\n", $result['errors']));
            $msg .= "✓ Errors: $errFile\n";
            $files[] = $errFile;
        }

        $processedCount++;
        if ($errorFile) {
            \Ksfraser\PhpabcCanntaireachd\CliOutputWriter::write($msg, $errorFile);
        } else {
            echo $msg;
        }
    }

    $summaryMsg = "Processing completed\n";
    $summaryMsg .= "✓ Processed $processedCount file(s)\n";

    if ($errorFile) {
        \Ksfraser\PhpabcCanntaireachd\CliOutputWriter::write($summaryMsg, $errorFile);
    } else {
        echo $summaryMsg;
    }

} else {
    showUsage();
    exit(1);
}

function showUsage() {
    global $argv;
    $script = basename($argv[0]);
    echo "ABC MIDI Defaults CLI Tool

Manages MIDI channel and program defaults for ABC voices in the database.
Supports listing, adding, editing, and deleting voice MIDI settings.

Usage:
  php $script <command> [options]

Commands:
  --list                    List all voice MIDI defaults
  --add <voice>             Add a new voice MIDI default
  --edit <voice>            Edit an existing voice MIDI default
  --delete <voice>          Delete a voice MIDI default
  --validate <file.abc>     Validate ABC file with MIDI defaults
  --save <file.abc>         Process and save ABC file with MIDI defaults

Options:
  --midi_channel <N>        MIDI channel number (0-15)
  --midi_program <N>        MIDI program number (0-127)
  --voice_output_style <s>  Voice output style for processing
  --interleave_bars <N>     Bars to interleave
  --bars_per_line <N>       Bars per line in output
  --join_bars_with_backslash Join bars with backslash
  --mysql_user <user>       MySQL username
  --mysql_pass <pass>       MySQL password
  --mysql_db <db>           MySQL database name
  --mysql_host <host>       MySQL host (default: localhost)
  --mysql_port <port>       MySQL port (default: 3306)
  -e, --errorfile <file>    Output file for messages and errors
  -h, --help                Show this help message

Configuration Options:
  --config <file>           Load configuration from file (JSON/YAML/INI)
  --show-config             Display current configuration and exit
  --save-config <file>      Save current configuration to file and exit

Examples:
  php $script --list
  php $script --add Bagpipe --midi_channel 1 --midi_program 109
  php $script --edit Melody --midi_program 25
  php $script --delete Drums
  php $script --validate tune.abc
  php $script --save tune.abc --errorfile=process.log
";
}
