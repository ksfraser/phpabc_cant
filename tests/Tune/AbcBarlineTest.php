<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\Tune\AbcBarline;

class AbcBarlineTest extends TestCase
{
    public function testCanInstantiate()
    {
        $barline = new AbcBarline('|:');
        $this->assertInstanceOf(AbcBarline::class, $barline);
    }

    public function testGetType()
    {
        $barline = new AbcBarline('||');
        $this->assertEquals('||', $barline->getType());
    }
}
