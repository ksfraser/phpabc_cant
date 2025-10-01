<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\Tune\AbcEmbellishment;

class AbcEmbellishmentTest extends TestCase
{
    public function testCanInstantiate()
    {
        $emb = new AbcEmbellishment('T');
        $this->assertInstanceOf(AbcEmbellishment::class, $emb);
    }

    public function testGetNote()
    {
        $emb = new AbcEmbellishment('H');
        $this->assertEquals('H', $emb->getNote());
    }
}
