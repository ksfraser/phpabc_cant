<?php
namespace Ksfraser\PhpabcCanntaireachd\Tests;

use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\Dict2php;

class TokenDictionaryTest extends TestCase
{
    public function testPrepopulationFromAbcDict()
    {
        // Simulate loading from abc_dict.php
        $dict = Dict2php::loadDict();
        $tokens = Dict2php::loadTokenTable();
        foreach ($dict as $abc => $row) {
            $found = false;
            foreach ($tokens as $token) {
                if ($token['abc_token'] === $abc) {
                    $found = true;
                    $this->assertEquals($row['cannt_token'], $token['cannt_token']);
                    $this->assertEquals($row['bmw_token'], $token['bmw_token']);
                }
            }
            $this->assertTrue($found, "ABC token $abc not found in token table");
        }
    }

    public function testAddBmwTokenUpdatesExistingRow()
    {
        $abc = 'A';
        $bmw = 'BMW_A';
        Dict2php::addOrUpdateToken($abc, null, $bmw, null);
        $token = Dict2php::getToken($abc);
        $this->assertEquals($bmw, $token['bmw_token']);
    }

    public function testAddNewTokenCreatesRow()
    {
        $abc = 'NEW';
        $cannt = 'NEWC';
        $bmw = 'NEWB';
        $desc = 'New token';
        Dict2php::addOrUpdateToken($abc, $cannt, $bmw, $desc);
        $token = Dict2php::getToken($abc);
        $this->assertEquals($cannt, $token['cannt_token']);
        $this->assertEquals($bmw, $token['bmw_token']);
        $this->assertEquals($desc, $token['description']);
    }

    public function testEditTokenPersistsChanges()
    {
        $abc = 'EDIT';
        Dict2php::addOrUpdateToken($abc, 'C', 'B', 'D');
        Dict2php::addOrUpdateToken($abc, 'C2', 'B2', 'D2');
        $token = Dict2php::getToken($abc);
        $this->assertEquals('C2', $token['cannt_token']);
        $this->assertEquals('B2', $token['bmw_token']);
        $this->assertEquals('D2', $token['description']);
    }

    public function testDeleteTokenRemovesRow()
    {
        $abc = 'DEL';
        Dict2php::addOrUpdateToken($abc, 'C', 'B', 'D');
        Dict2php::deleteToken($abc);
        $token = Dict2php::getToken($abc);
        $this->assertNull($token);
    }

    public function testConversionLogic()
    {
        $abc = 'X';
        $cannt = 'Y';
        $bmw = 'Z';
        Dict2php::addOrUpdateToken($abc, $cannt, $bmw, null);
        $this->assertEquals($cannt, Dict2php::convertAbcToCannt($abc));
        $this->assertEquals($abc, Dict2php::convertBmwToAbc($bmw));
    }

    public function testEdgeCases()
    {
        $abc = 'S@!';
        $cannt = '';
        $bmw = str_repeat('B', 256);
        Dict2php::addOrUpdateToken($abc, $cannt, $bmw, null);
        $token = Dict2php::getToken($abc);
        $this->assertEquals($cannt, $token['cannt_token']);
        $this->assertEquals($bmw, $token['bmw_token']);
    }

    public function testBulkOperations()
    {
        $bulk = [
            ['abc' => 'B1', 'cannt' => 'C1', 'bmw' => 'BM1', 'desc' => 'D1'],
            ['abc' => 'B2', 'cannt' => 'C2', 'bmw' => 'BM2', 'desc' => 'D2'],
        ];
        foreach ($bulk as $row) {
            Dict2php::addOrUpdateToken($row['abc'], $row['cannt'], $row['bmw'], $row['desc']);
        }
        foreach ($bulk as $row) {
            $token = Dict2php::getToken($row['abc']);
            $this->assertEquals($row['cannt'], $token['cannt_token']);
            $this->assertEquals($row['bmw'], $token['bmw_token']);
            $this->assertEquals($row['desc'], $token['description']);
        }
    }
}
