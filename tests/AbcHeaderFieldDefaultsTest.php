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
        $this->assertArrayHasKey('K', $headers);
        $this->assertNotNull($headers['K']);
        $this->assertEquals('HP', $headers['K']->get());

        $this->assertArrayHasKey('Q', $headers);
        $this->assertNotNull($headers['Q']);
        $this->assertEquals('1/4=90', $headers['Q']->get());

        $this->assertArrayHasKey('L', $headers);
        $this->assertNotNull($headers['L']);
        $this->assertEquals('1/8', $headers['L']->get());

        $this->assertArrayHasKey('M', $headers);
        $this->assertNotNull($headers['M']);
        $this->assertEquals('2/4', $headers['M']->get());

        $this->assertArrayHasKey('R', $headers);
        $this->assertNotNull($headers['R']);
        $this->assertEquals('March', $headers['R']->get());

        $this->assertArrayHasKey('O', $headers);
        $this->assertNotNull($headers['O']);
        $this->assertEquals('Scots Guards I', $headers['O']->get());

        $this->assertArrayHasKey('Z', $headers);
        $this->assertNotNull($headers['Z']);
        $this->assertEquals('', $headers['Z']->get());
    }
}
