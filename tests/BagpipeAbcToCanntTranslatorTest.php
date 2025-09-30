<?php
/**
 * Unit tests for BagpipeAbcToCanntTranslator and AbcTokenTranslator
 *
 * @covers \Ksfraser\PhpabcCanntaireachd\BagpipeAbcToCanntTranslator
 * @covers \Ksfraser\PhpabcCanntaireachd\AbcTokenTranslator
 */
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\BagpipeAbcToCanntTranslator;
use Ksfraser\PhpabcCanntaireachd\TokenDictionary;
use Ksfraser\PhpabcCanntaireachd\AbcNote;

class BagpipeAbcToCanntTranslatorTest extends TestCase
{
    public function testTranslatesKnownToken()
    {
        $dict = new TokenDictionary();
        $dict->prepopulate([
            'A' => ['cannt_token' => 'dare'],
        ]);
        $translator = new BagpipeAbcToCanntTranslator($dict);
        $note = new AbcNote('A');
        $cannt = $translator->translate($note);
        $note->setCanntaireachd($cannt);
        $this->assertEquals('dare', $note->getCanntaireachd());
    }

    public function testTranslatesUnknownTokenToNull()
    {
        $dict = new TokenDictionary();
        $dict->prepopulate([
            'A' => ['cannt_token' => 'dare'],
        ]);
        $translator = new BagpipeAbcToCanntTranslator($dict);
        $note = new AbcNote('B');
        $cannt = $translator->translate($note);
        $note->setCanntaireachd($cannt);
        $this->assertNull($note->getCanntaireachd());
    }

    public function testDictionaryInjection()
    {
        $dict = new TokenDictionary();
        $dict->prepopulate([
            'C' => ['cannt_token' => 'tum'],
        ]);
        $translator = new BagpipeAbcToCanntTranslator($dict);
        $note = new AbcNote('C');
        $cannt = $translator->translate($note);
        $note->setCanntaireachd($cannt);
        $this->assertEquals('tum', $note->getCanntaireachd());
    }
}
