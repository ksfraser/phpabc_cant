<?php
// CLI tool for managing ABC header fields (composer, book, etc.)
// Usage: php bin/abc-header-fields-cli.php --add --field composer --value "John Smith"
//        php bin/abc-header-fields-cli.php --list
//        php bin/abc-header-fields-cli.php --edit --field composer --old "John Smith" --new "Jane Doe"
//        php bin/abc-header-fields-cli.php --delete --field composer --value "Jane Doe"

require_once __DIR__ . '/../vendor/autoload.php';
use Ksfraser\PhpabcCanntaireachd\AbcHeaderFieldTable;

$headerTable = new AbcHeaderFieldTable();

$options = getopt('', ['add', 'list', 'edit', 'delete', 'field:', 'value:', 'old:', 'new:']);

if (isset($options['add'])) {
    if (isset($options['field'], $options['value'])) {
        $headerTable->addFieldValue($options['field'], $options['value']);
        echo "Added {$options['field']}: {$options['value']}\n";
    } else {
        echo "--add requires --field and --value\n";
    }
} elseif (isset($options['edit'])) {
    if (isset($options['field'], $options['old'], $options['new'])) {
        if ($headerTable->editFieldValue($options['field'], $options['old'], $options['new'])) {
            echo "Updated {$options['field']}: {$options['old']} to {$options['new']}\n";
        } else {
            echo "Value not found for edit\n";
        }
    } else {
        echo "--edit requires --field, --old, and --new\n";
    }
} elseif (isset($options['delete'])) {
    if (isset($options['field'], $options['value'])) {
        if ($headerTable->deleteFieldValue($options['field'], $options['value'])) {
            echo "Deleted {$options['field']}: {$options['value']}\n";
        } else {
            echo "Value not found for delete\n";
        }
    } else {
        echo "--delete requires --field and --value\n";
    }
} elseif (isset($options['list'])) {
    foreach ($headerTable->getAllFields() as $field => $values) {
        echo "$field:\n";
        foreach ($values as $val) {
            echo "  - $val\n";
        }
    }
} else {
    echo "Usage:\n";
    echo "  --add --field <name> --value <value>\n";
    echo "  --edit --field <name> --old <oldvalue> --new <newvalue>\n";
    echo "  --delete --field <name> --value <value>\n";
    echo "  --list\n";
}
