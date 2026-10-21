#!/usr/bin/env php
<?php
/**
 * Database Migration Runner
 * 
 * Applies SQL migrations to the database
 * Usage: php run-migrations.php [migration_number]
 */

require_once __DIR__ . '/../config/db_config.php';

function connectToDatabase() {
    global $db_host, $db_name, $db_user, $db_pass;
    
    try {
        $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
        $pdo = new PDO($dsn, $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage() . "\n");
    }
}

function createMigrationsTable($pdo) {
    $sql = "CREATE TABLE IF NOT EXISTS migrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        migration_name VARCHAR(255) NOT NULL UNIQUE,
        applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
}

function getAppliedMigrations($pdo) {
    $stmt = $pdo->query("SELECT migration_name FROM migrations ORDER BY id");
    $applied = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $applied[] = $row['migration_name'];
    }
    return $applied;
}

function getPendingMigrations($migrationsDir, $applied) {
    $files = glob($migrationsDir . '/*.sql');
    sort($files);
    
    $pending = array();
    foreach ($files as $file) {
        $name = basename($file);
        if (!in_array($name, $applied)) {
            $pending[] = $file;
        }
    }
    return $pending;
}

function applyMigration($pdo, $file) {
    $name = basename($file);
    $sql = file_get_contents($file);
    
    echo "Applying migration: $name\n";
    
    try {
        $pdo->beginTransaction();
        
        /* Split by semicolons and execute each statement */
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        foreach ($statements as $statement) {
            if (empty($statement) || substr($statement, 0, 2) === '--') {
                continue;
            }
            $pdo->exec($statement);
        }
        
        /* Record migration */
        $stmt = $pdo->prepare("INSERT INTO migrations (migration_name) VALUES (?)");
        $stmt->execute(array($name));
        
        $pdo->commit();
        echo "  ✓ Successfully applied\n";
        return true;
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo "  ✗ Failed: " . $e->getMessage() . "\n";
        return false;
    }
}

/* Main execution */
$migrationsDir = __DIR__ . '/../sql/migrations';

if (!is_dir($migrationsDir)) {
    die("Migrations directory not found: $migrationsDir\n");
}

echo "=== Database Migration Runner ===\n\n";

$pdo = connectToDatabase();
echo "Connected to database\n\n";

createMigrationsTable($pdo);

$applied = getAppliedMigrations($pdo);
echo "Applied migrations: " . count($applied) . "\n";
foreach ($applied as $migration) {
    echo "  - $migration\n";
}
echo "\n";

$pending = getPendingMigrations($migrationsDir, $applied);
echo "Pending migrations: " . count($pending) . "\n";

if (empty($pending)) {
    echo "No migrations to apply\n";
    exit(0);
}

foreach ($pending as $file) {
    echo "\n";
    if (!applyMigration($pdo, $file)) {
        echo "\nMigration failed. Stopping.\n";
        exit(1);
    }
}

echo "\n=== All migrations applied successfully ===\n";
exit(0);
