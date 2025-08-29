<?php
namespace Ksfraser\Database\Tests;

use Ksfraser\Database\DbManager;
use PHPUnit\Framework\TestCase;

class DbManagerTest extends TestCase
{
    public function testGetConfigLoadsConfigFile()
    {
        $config = DbManager::getConfig();
        $this->assertIsArray($config);
        $this->assertArrayHasKey('mysql_user', $config);
        $this->assertArrayHasKey('mysql_pass', $config);
        $this->assertArrayHasKey('mysql_db', $config);
        $this->assertArrayHasKey('mysql_host', $config);
        $this->assertArrayHasKey('mysql_port', $config);
        $this->assertArrayHasKey('dsn', $config);
    }

    public function testGetPdoReturnsConnection()
    {
        $pdo = DbManager::getPdo();
        $this->assertInstanceOf(\PDO::class, $pdo);
    }

    public function testFetchAllReturnsArray()
    {
        $result = DbManager::fetchAll('SELECT 1 AS test');
        $this->assertIsArray($result);
        $this->assertEquals(1, $result[0]['test']);
    }

    public function testFetchOneReturnsRow()
    {
        $row = DbManager::fetchOne('SELECT 2 AS test');
        $this->assertIsArray($row);
        $this->assertEquals(2, $row['test']);
    }

    public function testFetchValueReturnsValue()
    {
        $val = DbManager::fetchValue('SELECT 3 AS test');
        $this->assertEquals(3, $val);
    }

    public function testExecuteReturnsRowCount()
    {
        // Create a temp table for testing
        DbManager::execute('CREATE TEMPORARY TABLE IF NOT EXISTS temp_test (id INT)');
        $count = DbManager::execute('INSERT INTO temp_test (id) VALUES (?)', [42]);
        $this->assertEquals(1, $count);
        $row = DbManager::fetchOne('SELECT id FROM temp_test');
        $this->assertEquals(42, $row['id']);
    }
}
