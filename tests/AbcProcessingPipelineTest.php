<?php
use PHPUnit\Framework\TestCase;
use ksfraser\PhpabcCanntaireachd\AbcProcessingPipeline;
use ksfraser\PhpabcCanntaireachd\AbcTuneNumberValidatorPass;
use ksfraser\PhpabcCanntaireachd\AbcVoicePass;
use ksfraser\PhpabcCanntaireachd\AbcLyricsPass;
use ksfraser\PhpabcCanntaireachd\AbcCanntaireachdPass;
use ksfraser\PhpabcCanntaireachd\AbcVoiceOrderPass;
use ksfraser\PhpabcCanntaireachd\AbcTimingValidator;
use ksfraser\PhpabcCanntaireachd\TokenDictionary;
use ksfraser\PhpabcCanntaireachd\Exceptions\AbcProcessingException;

/**
 * @covers \ksfraser\PhpabcCanntaireachd\AbcProcessingPipeline
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
