<?php
use Ksfraser\PhpabcCanntaireachd\Voices\AbcVoice;
use PHPUnit\Framework\TestCase;
/**
 * @covers \Ksfraser\PhpabcCanntaireachd\AbcVoice
 */

class AbcVoiceTest extends TestCase
{
    public function testAbcVoiceHeaderAndBodyOut()
    {
        $voice = new AbcVoice('Melody', 'Melody', 'Mel', 'up', 'down', 1, 2, null, 'treble');
        $header = $voice->getHeaderOut();
        $body = $voice->getBodyOut();
        $this->assertStringContainsString('V:Melody', $header);
        $this->assertStringContainsString('name="Melody"', $header);
        $this->assertStringContainsString('sname="Mel"', $header);
        $this->assertStringContainsString('stem=up', $header);
        $this->assertStringContainsString('gstem=down', $header);
        $this->assertStringContainsString('octave=1', $header);
        $this->assertStringContainsString('transpose=2', $header);
        $this->assertStringContainsString('clef="treble"', $header);
        $this->assertStringContainsString('[V:Melody', $body);
    }

    public function testAbcVoiceLineStartOut()
    {
        $voice = new AbcVoice('Melody');
        $this->assertEquals('[V:Melody]', $voice->getLineStartOut());
    }
}
