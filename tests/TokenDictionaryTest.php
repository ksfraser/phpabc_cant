<?php

namespace Ksfraser\PhpabcCanntaireachd\Tests;

use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\TokenDictionary;

class TokenDictionaryTest extends TestCase
{
    public function testPrepopulationFromAbcDict()
    {
        $dict = [
            'A' => ['cannt_token' => 'en', 'bmw_token' => 'A', 'description' => 'Low A'],
            'B' => ['cannt_token' => 'o', 'bmw_token' => 'B', 'description' => 'B'],
            '{g}G' => ['cannt_token' => 'hem', 'bmw_token' => 'gG', 'description' => 'G gracenote on G'],
        ];
        $td = new TokenDictionary();
        $td->prepopulate($dict);
        foreach ($dict as $abc => $row) {
            $token = $td->getToken($abc);
            $this->assertEquals($row['cannt_token'], $token['cannt_token']);
            $this->assertEquals($row['bmw_token'], $token['bmw_token']);
            $this->assertEquals($row['description'], $token['description']);
        }
    }

    public function testBmwTokensPresentForBasicNotesAndGracenotes()
    {
        $dict = [
            'G' => ['cannt_token' => 'em', 'bmw_token' => 'G', 'description' => 'Low G'],
            'A' => ['cannt_token' => 'en', 'bmw_token' => 'A', 'description' => 'Low A'],
            '{g}G' => ['cannt_token' => 'hem', 'bmw_token' => 'gG', 'description' => 'G gracenote on G'],
            '{d}A' => ['cannt_token' => 'dan', 'bmw_token' => 'dA', 'description' => 'D gracenote on A'],
        ];
        $td = new TokenDictionary();
        $td->prepopulate($dict);
        foreach ($dict as $abc => $row) {
            $token = $td->getToken($abc);
            $this->assertNotEmpty($token['bmw_token'], "BMW token missing for $abc");
        }
    }

    public function testAbcBmwCanntConversionLogic()
    {
        $td = new TokenDictionary();
        $td->addOrUpdateToken('G', 'em', 'G', 'Low G');
        $td->addOrUpdateToken('A', 'en', 'A', 'Low A');
        $td->addOrUpdateToken('{g}G', 'hem', 'gG', 'G gracenote on G');
        $this->assertEquals('em', $td->convertAbcToCannt('G'));
        $this->assertEquals('G', $td->convertCanntToBmw('em'));
        $this->assertEquals('gG', $td->convertAbcToBmw('{g}G'));
        $this->assertEquals('{g}G', $td->convertBmwToAbc('gG'));
    }

    public function testAddBmwTokenUpdatesExistingRow()
    {
        $td = new TokenDictionary();
        $td->addOrUpdateToken('A', 'C', null, 'desc');
        $td->addOrUpdateToken('A', null, 'BMW_A', 'desc2');
        $token = $td->getToken('A');
        $this->assertEquals('BMW_A', $token['bmw_token']);
        $this->assertEquals('desc2', $token['description']);
    }

    public function testAddNewTokenCreatesRow()
    {
        $td = new TokenDictionary();
        $td->addOrUpdateToken('NEW', 'NEWC', 'NEWB', 'New token');
        $token = $td->getToken('NEW');
        $this->assertEquals('NEWC', $token['cannt_token']);
        $this->assertEquals('NEWB', $token['bmw_token']);
        $this->assertEquals('New token', $token['description']);
    }

    public function testEditTokenPersistsChanges()
    {
        $td = new TokenDictionary();
        $td->addOrUpdateToken('EDIT', 'C', 'B', 'D');
        $td->addOrUpdateToken('EDIT', 'C2', 'B2', 'D2');
        $token = $td->getToken('EDIT');
        $this->assertEquals('C2', $token['cannt_token']);
        $this->assertEquals('B2', $token['bmw_token']);
        $this->assertEquals('D2', $token['description']);
    }

    public function testDeleteTokenRemovesRow()
    {
        $td = new TokenDictionary();
        $td->addOrUpdateToken('DEL', 'C', 'B', 'D');
        $td->deleteToken('DEL');
        $token = $td->getToken('DEL');
        $this->assertNull($token);
    }

    public function testConversionLogic()
    {
        $td = new TokenDictionary();
        $td->addOrUpdateToken('X', 'Y', 'Z', null);
        $this->assertEquals('Y', $td->convertAbcToCannt('X'));
        $this->assertEquals('X', $td->convertBmwToAbc('Z'));
    }

    public function testEdgeCases()
    {
        $td = new TokenDictionary();
        $abc = 'S@!';
        $cannt = '';
        $bmw = str_repeat('B', 256);
        $td->addOrUpdateToken($abc, $cannt, $bmw, null);
        $token = $td->getToken($abc);
        $this->assertEquals($cannt, $token['cannt_token']);
        $this->assertEquals($bmw, $token['bmw_token']);
    }

    public function testBulkOperations()
    {
        $td = new TokenDictionary();
        $bulk = [
            ['abc' => 'B1', 'cannt' => 'C1', 'bmw' => 'BM1', 'desc' => 'D1'],
            ['abc' => 'B2', 'cannt' => 'C2', 'bmw' => 'BM2', 'desc' => 'D2'],
        ];
        $td->bulkImport($bulk);
        foreach ($bulk as $row) {
            $token = $td->getToken($row['abc']);
            $this->assertEquals($row['cannt'], $token['cannt_token']);
            $this->assertEquals($row['bmw'], $token['bmw_token']);
            $this->assertEquals($row['desc'], $token['description']);
        }
    }
}
