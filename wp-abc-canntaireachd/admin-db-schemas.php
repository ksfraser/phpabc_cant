<?php
/**
 * WordPress admin screen: Load all DB schema files in bin/*_schema.sql
 * Adds a button to run all schema files and create/update tables.
 */
add_action('admin_menu', function() {
    add_submenu_page('abc-midi-defaults', 'Load DB Schemas', 'Load DB Schemas', 'manage_options', 'abc-load-db-schemas', 'abc_load_db_schemas_admin');
});
function abc_load_db_schemas_admin() {
    global $wpdb;
    echo '<h1>Load All DB Schemas</h1>';
    if (isset($_POST['load_db_schemas'])) {
        $schemaDir = dirname(__DIR__) . '/sql';
        foreach (glob($schemaDir . '/*_schema.sql') as $file) {
            $sql = file_get_contents($file);
            $stmts = array_filter(array_map('trim', explode(';', $sql)));
            foreach ($stmts as $stmt) {
                if ($stmt) $wpdb->query($stmt);
            }
        }
        echo '<div class="updated"><p>All DB schemas loaded.</p></div>';
    }
    echo '<form method="post"><input type="submit" name="load_db_schemas" value="Load All DB Schemas"></form>';
}
