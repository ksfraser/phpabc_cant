<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\AbcVoiceOrderPass;

class AbcVoiceOrderPassTest extends TestCase {
    public function testVoiceOrderOutput() {
        $lines = [
            'V:Melody',
            'V:Guitar',
            'A B C',
            'G F E',
        ];
        $pass = new AbcVoiceOrderPass();
        $result = $pass->process($lines);
        $this->assertIsArray($result);
        $this->assertContains('V:Melody', $result);
        $this->assertContains('V:Guitar', $result);
    }
}
