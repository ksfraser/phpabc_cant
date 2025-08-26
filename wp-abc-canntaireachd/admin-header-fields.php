<?php
/**
 * WordPress Admin CRUD for AbcHeaderFieldTable
 * Allows list/add/edit/delete of header field values (composer, book, etc.)
 */
use Ksfraser\PhpabcCanntaireachd\AbcHeaderFieldTable;

// Assume $headerTable is an instance of AbcHeaderFieldTable
$headerTable = new AbcHeaderFieldTable();

// List all fields and values
function wp_abc_header_fields_list($headerTable) {
    echo "<h2>ABC Header Fields</h2>";
    foreach ($headerTable->getAllFields() as $field => $values) {
        echo "<h3>$field</h3><ul>";
        foreach ($values as $val) {
            echo "<li>" . htmlspecialchars($val) . "</li>";
        }
        echo "</ul>";
    }
}

// Add a new field value
function wp_abc_header_fields_add($headerTable, $field, $value) {
    $headerTable->addFieldValue($field, $value);
    echo "Added $field: $value";
}

// Edit a field value (replace old with new)
function wp_abc_header_fields_edit($headerTable, $field, $oldValue, $newValue) {
    if ($headerTable->editFieldValue($field, $oldValue, $newValue)) {
        echo "Updated $field: $oldValue to $newValue";
    }
}

// Delete a field value
function wp_abc_header_fields_delete($headerTable, $field, $value) {
    if ($headerTable->deleteFieldValue($field, $value)) {
        echo "Deleted $field: $value";
    }
}

// Example usage (replace with WP admin form handlers)
// wp_abc_header_fields_add($headerTable, 'composer', 'John Smith');
// wp_abc_header_fields_edit($headerTable, 'composer', 'John Smith', 'Jane Doe');
// wp_abc_header_fields_delete($headerTable, 'composer', 'Jane Doe');
// wp_abc_header_fields_list($headerTable);
