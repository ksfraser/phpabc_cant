#!/usr/bin/env php
<?php
/**
 * ABC Load Schemas CLI Tool
 *
 * Loads all database schema files from the sql/ directory into the configured database.
 * Schema files must match the pattern *_schema.sql.
 *
 * Usage:
 *   php abc-load-schemas-cli.php [options]
 *
 * Options:
 *   -c, --config <file>   Database config file (default: config/db_config.php)
 *   -d, --directory <dir> Schema directory (default: sql/)
 *   -h, --help            Show this help message
 *   -v, --verbose         Enable verbose output
 *
 * Examples:
 *   php abc-load-schemas-cli.php
 *   php abc-load-schemas-cli.php --config=my_config.php
 *   php abc-load-schemas-cli.php --directory=custom_schemas --verbose
 *
 * Schema Files:
 *   - abc_dict_schema.sql
 *   - abc_header_field_defaults_schema.sql
 *   - abc_midi_defaults_schema.sql
 *   - abc_voice_names_schema.sql
 *   - abc_voice_order_defaults_schema.sql
 */

require_once __DIR__ . '/../vendor/autoload.php';
use Ksfraser\PhpabcCanntaireachd\ConfigDb;
use Ksfraser\PhpabcCanntaireachd\CLIOptions;

// Parse command line arguments
$cli = CLIOptions::fromArgv($argv);

// Show help if requested
if (isset($cli->opts['h']) || isset($cli->opts['help'])) {
    showUsage();
    exit(0);
}

$configFile = isset($cli->opts['config']) ?
    $cli->opts['config'] : __DIR__ . '/../config/db_config.php';
$schemaDir = isset($cli->opts['directory']) ?
    $cli->opts['directory'] : dirname(__DIR__) . '/sql';

if (!file_exists($configFile)) {
    echo "Error: Config file '$configFile' not found\n";
    exit(1);
}

if (!is_dir($schemaDir)) {
    echo "Error: Schema directory '$schemaDir' not found\n";
    exit(1);
}

// Load DB config
$config = require $configFile;

try {
    $pdo = new PDO($config['dsn'], $config['mysql_user'], $config['mysql_pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    echo "Error: Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Find schema files
$pattern = $schemaDir . '/*_schema.sql';
$schemaFiles = glob($pattern);

if (!$schemaFiles) {
    echo "No schema files found in '$schemaDir'\n";
    exit(1);
}

$loadedCount = 0;
$failedCount = 0;

foreach ($schemaFiles as $file) {
    $fileName = basename($file);
    if (isset($cli->opts['v']) || isset($cli->opts['verbose'])) {
        echo "Loading schema: $fileName\n";
    }

    try {
        $sql = file_get_contents($file);
        $stmts = array_filter(array_map('trim', explode(';', $sql)));

        foreach ($stmts as $stmt) {
            if ($stmt) {
                $pdo->exec($stmt);
            }
        }
        $loadedCount++;
    } catch (Exception $e) {
        echo "Error loading $fileName: " . $e->getMessage() . "\n";
        $failedCount++;
    }
}

echo "Schema loading completed\n";
echo "✓ Successfully loaded: $loadedCount schema(s)\n";

if ($failedCount > 0) {
    echo "✗ Failed to load: $failedCount schema(s)\n";
    exit(1);
}

if (isset($cli->opts['v']) || isset($cli->opts['verbose'])) {
    echo "✓ Config file: $configFile\n";
    echo "✓ Schema directory: $schemaDir\n";
}

function showUsage() {
    global $argv;
    $script = basename($argv[0]);
    echo "ABC Load Schemas CLI Tool

Loads all database schema files from the sql/ directory into the configured database.
Schema files must match the pattern *_schema.sql.

Usage:
  php $script [options]

Options:
  -c, --config <file>   Database config file (default: config/db_config.php)
  -d, --directory <dir> Schema directory (default: sql/)
  -h, --help            Show this help message
  -v, --verbose         Enable verbose output

Examples:
  php $script
  php $script --config=my_config.php
  php $script --directory=custom_schemas --verbose

Schema Files:
  - abc_dict_schema.sql
  - abc_header_field_defaults_schema.sql
  - abc_midi_defaults_schema.sql
  - abc_voice_names_schema.sql
  - abc_voice_order_defaults_schema.sql
";
}
