<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\AbcProcessingPipeline;
use Ksfraser\PhpabcCanntaireachd\AbcTuneNumberValidatorPass;
use Ksfraser\PhpabcCanntaireachd\AbcVoicePass;
use Ksfraser\PhpabcCanntaireachd\AbcLyricsPass;
use Ksfraser\PhpabcCanntaireachd\AbcCanntaireachdPass;
use Ksfraser\PhpabcCanntaireachd\AbcVoiceOrderPass;
use Ksfraser\PhpabcCanntaireachd\AbcTimingValidator;
use Ksfraser\PhpabcCanntaireachd\TokenDictionary;
use Ksfraser\PhpabcCanntaireachd\Exceptions\AbcProcessingException;

/**
 * @covers \Ksfraser\PhpabcCanntaireachd\AbcProcessingPipeline
 * @group pipeline
 * @uml
 * @startuml
 * start
 * :setup passes;
 * :run pipeline;
 * :assert output;
 * stop
 * @enduml
 */
class AbcProcessingPipelineTest extends TestCase {
    public function testPipelineRunsSuccessfully() {
        $lines = [
            'X:1',
            'T:Test Tune',
            'M:4/4',
            'K:C',
            'V:Bagpipes',
            '[V:Bagpipes]A B c d |'
        ];
        $dict = new TokenDictionary();
        $passes = [
            new AbcTuneNumberValidatorPass(),
            new AbcVoicePass(),
            new AbcLyricsPass($dict),
            new AbcCanntaireachdPass($dict),
            new AbcVoiceOrderPass(),
            new AbcTimingValidator()
        ];
        $pipeline = new AbcProcessingPipeline($passes);
        $headerFields = [
            'C' => '', 'B' => '', 'K' => 'C', 'T' => 'Test Tune', 'M' => '4/4', 'L' => '', 'Q' => ''
        ];
        $suggestions = [];
        $result = $pipeline->run($lines, $headerFields, $suggestions);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('lines', $result);
        $this->assertArrayHasKey('canntDiff', $result);
        $this->assertArrayHasKey('errors', $result);
        $this->assertContains('V:Bagpipes', $result['lines']);
    }

    public function testPipelineThrowsExceptionOnError() {
        $lines = null; // Invalid input
        $dict = new TokenDictionary();
        $passes = [new AbcTuneNumberValidatorPass()];
        $pipeline = new AbcProcessingPipeline($passes);
        $headerFields = [];
        $suggestions = [];
        $this->expectException(AbcProcessingException::class);
        $pipeline->run($lines, $headerFields, $suggestions);
    }
}
