<?php
use PHPUnit\Framework\TestCase;
use ksfraser\PhpabcCanntaireachd\AbcLine;
use ksfraser\PhpabcCanntaireachd\Tune\AbcBar;
use ksfraser\PhpabcCanntaireachd\AbcNote;
use ksfraser\PhpabcCanntaireachd\BagpipeAbcToCanntTranslator;
use ksfraser\PhpabcCanntaireachd\TokenDictionary;

class AbcLineCanntLyricLineTest extends TestCase
{
    public function testRenderCanntLyricLineAggregatesBarCanntaireachd()
    {
        file_put_contents('debug.log', "TEST: testRenderCanntLyricLineAggregatesBarCanntaireachd START\n", FILE_APPEND);
        $dict = new TokenDictionary();
        $dict->prepopulate([
            'A' => ['cannt_token' => 'dare'],
            'B' => ['cannt_token' => 'tum'],
        ]);
        $translator = new BagpipeAbcToCanntTranslator($dict);
    $bar1 = new AbcBar(1, '|');
    $bar1->addNote('A');
    $bar2 = new AbcBar(2, '|');
    $bar2->addNote('B');
    file_put_contents('debug.log', "TEST: bar1 class=".get_class($bar1)."\n", FILE_APPEND);
    file_put_contents('debug.log', "TEST: bar2 class=".get_class($bar2)."\n", FILE_APPEND);
    $line = new AbcLine();
    $line->add($bar1);
    $line->add($bar2);
        file_put_contents('debug.log', "TEST: calling translateBars\n", FILE_APPEND);
        $line->translateBars($translator);
        file_put_contents('debug.log', "TEST: calling renderCanntLyricLine\n", FILE_APPEND);
        $wLine = $line->renderCanntLyricLine();
        file_put_contents('debug.log', "TEST: wLine={$wLine}\n", FILE_APPEND);
        file_put_contents('debug.log', "TEST: testRenderCanntLyricLineAggregatesBarCanntaireachd END\n", FILE_APPEND);
        $this->assertEquals('w: dare tum', $wLine);
    }
}
