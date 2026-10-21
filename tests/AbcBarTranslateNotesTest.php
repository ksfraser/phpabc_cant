<?php
use PHPUnit\Framework\TestCase;
use ksfraser\PhpabcCanntaireachd\Tune\AbcBar;
use ksfraser\PhpabcCanntaireachd\AbcNote;
use ksfraser\PhpabcCanntaireachd\BagpipeAbcToCanntTranslator;
use ksfraser\PhpabcCanntaireachd\TokenDictionary;

class AbcBarTranslateNotesTest extends TestCase
{
    public function testTranslateNotesSetsCanntaireachdOnAllNotes()
    {
        $dict = new TokenDictionary();
        $dict->prepopulate([
            'A' => ['cannt_token' => 'dare'],
            'B' => ['cannt_token' => 'tum'],
        ]);
        $translator = new BagpipeAbcToCanntTranslator($dict);
        $bar = new AbcBar(1, '|');
        $bar->addNote('A');
        $bar->addNote('B');
        $bar->translateNotes($translator);
        $notes = $bar->notes;
        $this->assertEquals('dare', $notes[0]->getCanntaireachd());
        $this->assertEquals('tum', $notes[1]->getCanntaireachd());
    }
}
