<?php
use Ksfraser\PhpabcCanntaireachd\AbcFileParser;
use PHPUnit\Framework\TestCase;

class AbcHeaderFieldDefaultsTest extends TestCase {
    public function testDefaultsAreApplied() {
        $abc = "X:1\nT:Test Tune\n"; // Missing K, Q, L, M, R, O, Z
        $parser = new AbcFileParser();
        $tunes = $parser->parse($abc);
        $this->assertNotEmpty($tunes);
        $tune = $tunes[0];
        $headers = $tune->getHeaders();
        $this->assertEquals('HP', $headers['K']->get());
        $this->assertEquals('1/4=90', $headers['Q']->get());
        $this->assertEquals('1/8', $headers['L']->get());
        $this->assertEquals('2/4', $headers['M']->get());
        $this->assertEquals('March', $headers['R']->get());
        $this->assertEquals('Kevin Fraser', $headers['O']->get());
        $this->assertEquals('Kevin Fraser', $headers['Z']->get());
    }
}
