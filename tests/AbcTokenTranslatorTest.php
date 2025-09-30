<?php
/**
 * Unit tests for AbcTokenTranslator (abstract) via BagpipeAbcToCanntTranslator
 *
 * @covers \Ksfraser\PhpabcCanntaireachd\AbcTokenTranslator
 */
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\BagpipeAbcToCanntTranslator;
use Ksfraser\PhpabcCanntaireachd\TokenDictionary;
use Ksfraser\PhpabcCanntaireachd\AbcNote;

class AbcTokenTranslatorTest extends TestCase
{
    public function testTranslateReturnsNullForUnknownToken()
    {
        $dict = new TokenDictionary();
        $dict->prepopulate([
            'A' => ['cannt_token' => 'dare'],
        ]);
        $translator = new BagpipeAbcToCanntTranslator($dict);
        $note = new AbcNote('B');
        $this->assertNull($translator->translate($note));
    }

    public function testTranslateReturnsCanntForKnownToken()
    {
        $dict = new TokenDictionary();
        $dict->prepopulate([
            'A' => ['cannt_token' => 'dare'],
        ]);
        $translator = new BagpipeAbcToCanntTranslator($dict);
        $note = new AbcNote('A');
        $this->assertEquals('dare', $translator->translate($note));
    }
}
