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
            'G' => ['cannt_token' => 'em'],
        ]);
        $translator = new BagpipeAbcToCanntTranslator($dict);
        $note = new AbcNote('G');
        $cannt = $translator->translate($note);
        $note->setCanntaireachd($cannt);
        $this->assertEquals('em', $note->getCanntaireachd());
    }

    public function testTranslatesUnknownTokenToNull()
    {
        $dict = new TokenDictionary();
        $dict->prepopulate([
            'G' => ['cannt_token' => 'em'],
        ]);
        $translator = new BagpipeAbcToCanntTranslator($dict);
        $note = new AbcNote('Z');
        $cannt = $translator->translate($note);
        $note->setCanntaireachd($cannt);
        $this->assertNull($note->getCanntaireachd());
    }

    public function testDictionaryInjection()
    {
        $dict = new TokenDictionary();
        $dict->prepopulate([
            'A' => ['cannt_token' => 'en'],
        ]);
        $translator = new BagpipeAbcToCanntTranslator($dict);
        $note = new AbcNote('A');
        $cannt = $translator->translate($note);
        $note->setCanntaireachd($cannt);
        $this->assertEquals('en', $note->getCanntaireachd());
    }
}
