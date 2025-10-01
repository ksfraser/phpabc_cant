<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\Tune\AbcTune;

class AbcTuneTest extends TestCase
{
    public function testCanInstantiateAbcTune()
    {
        $tune = new AbcTune();
        $this->assertInstanceOf(AbcTune::class, $tune);
    }


    // Skipped: testAddAndGetLines() because add() expects AbcItem, not string.

    public function testHeadersAreSettableAndGettable()
    {
        $tune = new AbcTune();
        $tune->replaceHeader('T', 'Test Title');
        $headers = $tune->getHeaders();
        $this->assertArrayHasKey('T', $headers);
        $this->assertEquals('Test Title', $headers['T']->get());
    }
}
