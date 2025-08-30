<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\HeaderDefaults;
use Ksfraser\Database\DbManager;

class HeaderDefaultsMigrationTest extends TestCase {
    public function testMigrateDoesNotOverwriteExisting() {
        // Ensure DB is available (DummyPdo will be used if necessary)
        $pdo = DbManager::getPdo();
        // Create table and insert an existing value for B
        DbManager::execute('CREATE TEMPORARY TABLE IF NOT EXISTS abc_header_field_defaults (field_name VARCHAR(10), field_value TEXT)');
        DbManager::execute('INSERT INTO abc_header_field_defaults (field_name, field_value) VALUES (?, ?)', ['B', 'Existing Book']);

        // Run migration
        HeaderDefaults::migrateFromSqlToDb();

        // Fetch from DB and ensure B equals existing value
        $row = DbManager::fetchOne('SELECT field_value FROM abc_header_field_defaults WHERE field_name = ?', ['B']);
        $this->assertIsArray($row);
        $this->assertEquals('Existing Book', $row['field_value']);

        // And ensure other seeded fields exist (e.g., K)
        $krow = DbManager::fetchOne('SELECT field_value FROM abc_header_field_defaults WHERE field_name = ?', ['K']);
        $this->assertIsArray($krow);
        $this->assertNotEmpty($krow['field_value']);
    }
}
