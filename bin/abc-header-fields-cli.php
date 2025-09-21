<?php
/**
 * ABC Header Fields CLI Tool
 *
 * Manages ABC header field values (composer, book, etc.) in a database.
 * Allows adding, editing, deleting, and listing header field values.
 *
 * Usage:
 *   php abc-header-fields-cli.php <command> [options]
 *
 * Commands:
 *   --add        Add a new header field value
 *   --edit       Edit an existing header field value
 *   --delete     Delete a header field value
 *   --list       List all header field values
 *
 * Options:
 *   --field <name>        Header field name (composer, book, etc.)
 *   --value <value>       Field value to add or delete
 *   --old <value>         Old value to replace (for --edit)
 *   --new <value>         New value to set (for --edit)
 *   -e, --errorfile <file> Output file for messages (default: stdout)
 *   -h, --help            Show this help message
 *
 * Examples:
 *   php abc-header-fields-cli.php --add --field composer --value "John Smith"
 *   php abc-header-fields-cli.php --edit --field composer --old "John Smith" --new "Jane Doe"
 *   php abc-header-fields-cli.php --delete --field composer --value "Jane Doe"
 *   php abc-header-fields-cli.php --list
 *   php abc-header-fields-cli.php --list --errorfile=fields.txt
 */

require_once __DIR__ . '/../vendor/autoload.php';
use Ksfraser\PhpabcCanntaireachd\AbcHeaderFieldTable;
use Ksfraser\PhpabcCanntaireachd\CLIOptions;

// Parse command line arguments
$cli = CLIOptions::fromArgv($argv);

// Show help if requested or no valid command
if (isset($cli->opts['h']) || isset($cli->opts['help']) ||
    (!isset($cli->opts['add']) && !isset($cli->opts['list']) &&
     !isset($cli->opts['edit']) && !isset($cli->opts['delete']))) {
    showUsage();
    exit(0);
}

$headerTable = new AbcHeaderFieldTable();

// Get error file from CLIOptions
$errorFile = $cli->errorFile;

if (isset($cli->opts['add'])) {
    if (isset($cli->opts['field'], $cli->opts['value'])) {
        $headerTable->addFieldValue($cli->opts['field'], $cli->opts['value']);
        $msg = "✓ Added {$cli->opts['field']}: {$cli->opts['value']}\n";
    } else {
        $msg = "✗ --add requires --field and --value\n";
        showUsage();
        exit(1);
    }
} elseif (isset($cli->opts['edit'])) {
    if (isset($cli->opts['field'], $cli->opts['old'], $cli->opts['new'])) {
        if ($headerTable->editFieldValue($cli->opts['field'], $cli->opts['old'], $cli->opts['new'])) {
            $msg = "✓ Updated {$cli->opts['field']}: '{$cli->opts['old']}' → '{$cli->opts['new']}'\n";
        } else {
            $msg = "✗ Value '{$cli->opts['old']}' not found for field '{$cli->opts['field']}'\n";
        }
    } else {
        $msg = "✗ --edit requires --field, --old, and --new\n";
        showUsage();
        exit(1);
    }
} elseif (isset($cli->opts['delete'])) {
    if (isset($cli->opts['field'], $cli->opts['value'])) {
        if ($headerTable->deleteFieldValue($cli->opts['field'], $cli->opts['value'])) {
            $msg = "✓ Deleted {$cli->opts['field']}: {$cli->opts['value']}\n";
        } else {
            $msg = "✗ Value '{$cli->opts['value']}' not found for field '{$cli->opts['field']}'\n";
        }
    } else {
        $msg = "✗ --delete requires --field and --value\n";
        showUsage();
        exit(1);
    }
} elseif (isset($cli->opts['list'])) {
    $allFields = $headerTable->getAllFields();
    if (empty($allFields)) {
        $msg = "No header field values found.\n";
    } else {
        $msg = "Header Field Values:\n";
        foreach ($allFields as $field => $values) {
            $msg .= "\n$field:\n";
            foreach ($values as $value) {
                $msg .= "  • $value\n";
            }
        }
    }
} else {
    $msg = "✗ No valid command specified\n";
    showUsage();
    exit(1);
}

if ($errorFile) {
    \Ksfraser\PhpabcCanntaireachd\CliOutputWriter::write($msg, $errorFile);
} else {
    echo $msg;
}

function showUsage() {
    global $argv;
    $script = basename($argv[0]);
    echo "ABC Header Fields CLI Tool

Manages ABC header field values (composer, book, etc.) in a database.
Allows adding, editing, deleting, and listing header field values.

Usage:
  php $script <command> [options]

Commands:
  --add        Add a new header field value
  --edit       Edit an existing header field value
  --delete     Delete a header field value
  --list       List all header field values

Options:
  --field <name>        Header field name (composer, book, etc.)
  --value <value>       Field value to add or delete
  --old <value>         Old value to replace (for --edit)
  --new <value>         New value to set (for --edit)
  -e, --errorfile <file> Output file for messages (default: stdout)
  -h, --help            Show this help message

Examples:
  php $script --add --field composer --value \"John Smith\"
  php $script --edit --field composer --old \"John Smith\" --new \"Jane Doe\"
  php $script --delete --field composer --value \"Jane Doe\"
  php $script --list
  php $script --list --errorfile=fields.txt
";
}
