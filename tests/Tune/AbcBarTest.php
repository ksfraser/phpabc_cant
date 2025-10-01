<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\Tune\AbcBar;

class AbcBarTest extends TestCase
{
    public function testCanInstantiateAbcBar()
    {
        $bar = new AbcBar([]);
        $this->assertInstanceOf(AbcBar::class, $bar);
    }


    public function testBarContentIsSetAndGettable()
    {
    $bar = new AbcBar('A B');
    $notes = $bar->getContent();
    $this->assertCount(2, $notes);
    $this->assertEquals('A', $notes[0]->getNote());
    $this->assertEquals('B', $notes[1]->getNote());
    }

    public function testBarRendersCorrectly()
    {
    $bar = new AbcBar('A B C');
    $rendered = $bar->renderNotes();
    $this->assertIsString($rendered);
    $this->assertStringContainsString('A', $rendered);
    $this->assertStringContainsString('B', $rendered);
    $this->assertStringContainsString('C', $rendered);
    }
}
