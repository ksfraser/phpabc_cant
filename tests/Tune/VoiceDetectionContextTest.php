<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\Tune\VoiceDetectionContext;

class VoiceDetectionContextTest extends TestCase
{
    public function testCanInstantiate()
    {
        $ctx = new VoiceDetectionContext();
        $this->assertInstanceOf(VoiceDetectionContext::class, $ctx);
    }

    public function testSetAndGetVoice()
    {
        $ctx = new VoiceDetectionContext();
        $ctx->setVoice('pipes');
        $this->assertEquals('pipes', $ctx->getVoice());
    }

    public function testIsBagpipeVoice()
    {
        $ctx = new VoiceDetectionContext();
        $ctx->setVoice('pipes');
        $this->assertTrue($ctx->isBagpipeVoice());
        $ctx->setVoice('melody');
        $this->assertFalse($ctx->isBagpipeVoice());
    }
}
