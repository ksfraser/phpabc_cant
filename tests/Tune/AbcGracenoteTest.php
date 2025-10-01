<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\Tune\AbcGracenote;

class AbcGracenoteTest extends TestCase
{
    public function testCanInstantiate()
    {
        $gracenote = new AbcGracenote('G');
        $this->assertInstanceOf(AbcGracenote::class, $gracenote);
    }

    public function testGetNote()
    {
        $gracenote = new AbcGracenote('A');
        $this->assertEquals('A', $gracenote->getNote());
    }
}
