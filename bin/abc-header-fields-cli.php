<?php
// CLI tool for managing ABC header fields (composer, book, etc.)
// Usage: php bin/abc-header-fields-cli.php --add --field composer --value "John Smith"
//        php bin/abc-header-fields-cli.php --list
//        php bin/abc-header-fields-cli.php --edit --field composer --old "John Smith" --new "Jane Doe"
//        php bin/abc-header-fields-cli.php --delete --field composer --value "Jane Doe"

require_once __DIR__ . '/../vendor/autoload.php';
use Ksfraser\PhpabcCanntaireachd\AbcHeaderFieldTable;
use Ksfraser\PhpabcCanntaireachd\CLIOptions;

$headerTable = new AbcHeaderFieldTable();

// Centralized CLI options (provides --errorfile among others)
$cli = CLIOptions::fromArgv($argv);
$errorFile = $cli->errorFile;

// Script-specific flags (header management commands)
$options = getopt('', ['add', 'list', 'edit', 'delete', 'field:', 'value:', 'old:', 'new:']);

if (isset($options['add'])) {
    if (isset($options['field'], $options['value'])) {
        $headerTable->addFieldValue($options['field'], $options['value']);
        $msg = "Added {$options['field']}: {$options['value']}\n";
    } else {
        $msg = "--add requires --field and --value\n";
    }
    if ($errorFile) {
        \Ksfraser\PhpabcCanntaireachd\CliOutputWriter::write($msg, $errorFile);
    } else {
        echo $msg;
    }
} elseif (isset($options['edit'])) {
    if (isset($options['field'], $options['old'], $options['new'])) {
        if ($headerTable->editFieldValue($options['field'], $options['old'], $options['new'])) {
            $msg = "Updated {$options['field']}: {$options['old']} to {$options['new']}\n";
        } else {
            $msg = "Value not found for edit\n";
        }
    } else {
        $msg = "--edit requires --field, --old, and --new\n";
    }
    if ($errorFile) {
        \Ksfraser\PhpabcCanntaireachd\CliOutputWriter::write($msg, $errorFile);
    } else {
        echo $msg;
    }
} elseif (isset($options['delete'])) {
    if (isset($options['field'], $options['value'])) {
        if ($headerTable->deleteFieldValue($options['field'], $options['value'])) {
            $msg = "Deleted {$options['field']}: {$options['value']}\n";
        } else {
            $msg = "Value not found for delete\n";
        }
    } else {
        $msg = "--delete requires --field and --value\n";
    }
    if ($errorFile) {
        \Ksfraser\PhpabcCanntaireachd\CliOutputWriter::write($msg, $errorFile);
    } else {
        echo $msg;
    }
} elseif (isset($options['list'])) {
    $msg = "";
    foreach ($headerTable->getAllFields() as $field => $values) {
        $msg .= "$field:\n";
        foreach ($values as $val) {
            $msg .= "  - $val\n";
        }
    }
    if ($errorFile) {
        \Ksfraser\PhpabcCanntaireachd\CliOutputWriter::write($msg, $errorFile);
    } else {
        echo $msg;
    }
} else {
    $msg = "Usage:\n";
    $msg .= "  --add --field <name> --value <value>\n";
    $msg .= "  --edit --field <name> --old <oldvalue> --new <newvalue>\n";
    $msg .= "  --delete --field <name> --value <value>\n";
    $msg .= "  --list\n";
    if ($errorFile) {
        \Ksfraser\PhpabcCanntaireachd\CliOutputWriter::write($msg, $errorFile);
    } else {
        echo $msg;
    }
}
