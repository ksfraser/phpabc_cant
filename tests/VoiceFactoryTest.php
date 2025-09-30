<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\Voices\AbcVoice;
use Ksfraser\PhpabcCanntaireachd\Voices\VoiceFactory;
use Ksfraser\PhpabcCanntaireachd\Voices\GenericVoiceFactory;
use Ksfraser\PhpabcCanntaireachd\Voices\BagpipeVoiceFactory;

class VoiceFactoryTest extends TestCase
{
    public function testGenericVoiceFactoryCreatesDefaultVoice()
    {
        $factory = new GenericVoiceFactory();
        $voice = $factory->createVoice();
        $this->assertInstanceOf(AbcVoice::class, $voice);
        $header = $voice->getHeaderOut();
        $this->assertStringContainsString('V:Generic', $header);
    }

    public function testBagpipeVoiceFactoryCreatesBagpipeVoice()
    {
        $factory = new BagpipeVoiceFactory();
        $voice = $factory->createVoice();
        $this->assertInstanceOf(AbcVoice::class, $voice);
        $header = $voice->getHeaderOut();
        $this->assertStringContainsString('V:Bagpipes', $header);
        $this->assertStringContainsString('name="Bagpipes"', $header);
        $this->assertStringContainsString('stem=down', $header);
        $this->assertStringContainsString('gstem=up', $header);
    }

    public function testVoiceFactoryValidationThrows()
    {
        $this->expectException(InvalidArgumentException::class);
        new VoiceFactory('TOOLONGVOICEID');
    }

    public function testVoiceFactoryClefValidation()
    {
        $factory = new VoiceFactory('A');
        $this->expectException(InvalidArgumentException::class);
        $factory->setClef('invalidclef');
    }

    public function testVoiceFactoryStemValidation()
    {
        $factory = new VoiceFactory('A');
        $this->expectException(InvalidArgumentException::class);
        $factory->setStem('sideways');
    }
}
