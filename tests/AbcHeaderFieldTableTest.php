<?php
namespace Ksfraser\PhpabcCanntaireachd\Tests;

use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\AbcHeaderFieldTable;
use Ksfraser\PhpabcCanntaireachd\AbcHeaderFieldMatcher;

class AbcHeaderFieldTableTest extends TestCase
{
    public function testAddAndGetFieldValue()
    {
        $table = new AbcHeaderFieldTable();
        $table->addFieldValue('composer', 'John Smith');
        $table->addFieldValue('composer', 'Jane Doe');
        $values = $table->getFieldValues('composer');
        $this->assertContains('John Smith', $values);
        $this->assertContains('Jane Doe', $values);
    }

    public function testEditFieldValue()
    {
        $table = new AbcHeaderFieldTable();
        $table->addFieldValue('book', 'Scots Guard I');
        $table->editFieldValue('book', 'Scots Guard I', 'Scots Guards Vol I');
        $values = $table->getFieldValues('book');
        $this->assertContains('Scots Guards Vol I', $values);
        $this->assertNotContains('Scots Guard I', $values);
    }

    public function testDeleteFieldValue()
    {
        $table = new AbcHeaderFieldTable();
        $table->addFieldValue('composer', 'John Smith');
        $table->deleteFieldValue('composer', 'John Smith');
        $values = $table->getFieldValues('composer');
        $this->assertNotContains('John Smith', $values);
    }

    public function testMatcherLowScoreAddsValue()
    {
        $table = new AbcHeaderFieldTable();
        $matcher = new AbcHeaderFieldMatcher($table);
        $tuneFields = ['composer' => 'New Composer'];
        $suggestions = $matcher->processTuneFields($tuneFields);
        $values = $table->getFieldValues('composer');
        $this->assertContains('New Composer', $values);
        $this->assertEmpty($suggestions);
    }

    public function testMatcherHighButNotExactSuggests()
    {
        $table = new AbcHeaderFieldTable();
        $table->addFieldValue('composer', 'John Smith');
        $matcher = new AbcHeaderFieldMatcher($table);
        $tuneFields = ['composer' => 'Jon Smith'];
        $suggestions = $matcher->processTuneFields($tuneFields);
        $this->assertNotEmpty($suggestions);
        $this->assertEquals('composer', $suggestions[0]['field']);
        $this->assertEquals('Jon Smith', $suggestions[0]['value']);
        $this->assertEquals('John Smith', $suggestions[0]['bestMatch']);
        $this->assertGreaterThan(0.5, $suggestions[0]['score']);
        $this->assertLessThan(0.95, $suggestions[0]['score']);
    }
}
