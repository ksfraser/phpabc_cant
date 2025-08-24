<?php
use Ksfraser\PhpabcCanntaireachd\AbcValidator;
use PHPUnit\Framework\TestCase;

class TestAbcValidator extends TestCase {
    public function testBagpipeVoiceAutoInsert() {
        $abc = "X:1\nT:Test\nK:C\nV:Melody name=\"Melody\" sname=\"Melody\"\nc d e f g\n";
        $validator = new AbcValidator();
        $validator->validate($abc);
        $this->assertStringContainsString('V:Bagpipes', $abc);
        $this->assertStringContainsString('%score {Bagpipes}', $abc);
    }
    public function testHeaderValidation() {
        $abc = "X:1\nT:Test\nK:C\nV:Melody name=\"Melody\" sname=\"Melody\"\nc d e f g\n";
        $validator = new AbcValidator();
        $errors = $validator->validate($abc);
        $allErrors = implode(' ', $errors);
        $this->assertStringContainsString('missing C: header', $allErrors);
        $this->assertStringContainsString('missing B: header', $allErrors);
        $this->assertStringContainsString('missing O: header', $allErrors);
        $this->assertStringContainsString('missing Z: header', $allErrors);
    }
}
