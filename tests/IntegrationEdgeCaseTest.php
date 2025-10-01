<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\AbcFileParser;
use Ksfraser\PhpabcCanntaireachd\AbcProcessor;

class IntegrationEdgeCaseTest extends TestCase {
    public function testParseFullAbcFile() {
        $abc = file_get_contents(__DIR__ . '/test-bagpipe-cannt.abc');
        $parser = new AbcFileParser();
        $tunes = $parser->parse($abc);
        $this->assertIsArray($tunes);
        $this->assertNotEmpty($tunes);
        $tune = $tunes[0];
        $out = $tune->render();
        $this->assertStringContainsString('V:Bagpipes', $out);
        $this->assertStringContainsString('T:Suo Gan', $out);
        $this->assertStringContainsString('M:4/4', $out);
        $this->assertStringContainsString('K:HP', $out);
    }

    public function testMalformedAbcMissingHeaders() {
        $abc = "T:No X header\nM:4/4\nK:C\nA B C D";
        $parser = new AbcFileParser();
        $tunes = $parser->parse($abc);
        $this->assertIsArray($tunes);
        $this->assertNotEmpty($tunes);
        $tune = $tunes[0];
        $headers = $tune->getHeaders();
        $this->assertArrayNotHasKey('X', $headers, 'Should not have X header');
        $this->assertEquals('No X header', $headers['T']->get());
    }

    public function testAbcProcessorHandlesInvalidInput() {
        $dict = ['cannt' => 1];
        $abc = "V:Bagpipes\n|A B C D|A2 B2|A B|";
        $result = AbcProcessor::process($abc, $dict);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('lines', $result);
        $this->assertArrayHasKey('errors', $result);
        $this->assertNotEmpty($result['lines']);
    }

    public function testAbcProcessorHandlesEmptyInput() {
        $dict = ['cannt' => 1];
        $abc = "";
        $result = AbcProcessor::process($abc, $dict);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('lines', $result);
        $this->assertEmpty($result['lines']);
    }

    public function testAbcProcessorHandlesMalformedBarlines() {
        $dict = ['cannt' => 1];
        $abc = "V:Bagpipes\n|A B C D|A2 B2|A B| | | |";
        $result = AbcProcessor::process($abc, $dict);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('lines', $result);
        $this->assertArrayHasKey('errors', $result);
    }
}
