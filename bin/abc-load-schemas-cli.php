<?php
// CLI to load all schema files in bin/ matching *_schema.sql
require_once __DIR__ . '/../vendor/autoload.php';
use Ksfraser\PhpabcCanntaireachd\ConfigDb;

// Use new sql/ directory for schema files
$schemaDir = dirname(__DIR__) . '/sql';
$pattern = $schemaDir . '/*_schema.sql';
$schemaFiles = glob($pattern);
if (!$schemaFiles) {
    echo "No schema files found.\n";
    exit(1);
}

// Load DB config

$config = require __DIR__ . '/../src/Ksfraser/PhpabcCanntaireachd/config_db.php';
$pdo = new PDO($config['dsn'], $config['mysql_user'], $config['mysql_pass']);

foreach ($schemaFiles as $file) {
    echo "Loading schema: $file\n";
    $sql = file_get_contents($file);
    $stmts = array_filter(array_map('trim', explode(';', $sql)));
    foreach ($stmts as $stmt) {
        if ($stmt) $pdo->exec($stmt);
    }
}
echo "All schemas loaded.\n";
