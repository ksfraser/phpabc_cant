<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\AbcFileParser;
use Ksfraser\PhpabcCanntaireachd\TuneService;
use Ksfraser\Database\DbManager;

class TuneServiceBagpipeTest extends TestCase {
    public function testEnsureBagpipeVoiceCreatesAndGenerates() {
        $abc = "";
        $abc .= "X:1\n";
        $abc .= "T:Test Tune\n";
        $abc .= "M:4/4\n";
        $abc .= "K:G\n";
        $abc .= "L:1/8\n";
        $abc .= "V:1\n";
        $abc .= "|: GABc d2 e2 :|\n";

        // Parse the abc
        $parser = new AbcFileParser();
        $tunes = $parser->parse($abc);
        $this->assertIsArray($tunes);
        $tune = array_shift($tunes);

        // Extract raw body lines from the original ABC and populate voiceBars
        $lines = preg_split('/\r?\n/', $abc);
        $bodyLines = [];
        foreach ($lines as $line) {
            $trim = trim($line);
            if ($trim === '') continue;
            // Skip header lines like X:,T:,K:,L:,M: but keep V: so voices are created
            if (preg_match('/^[A-Z]:/', $trim) && !preg_match('/^V:/', $trim)) continue;
            $bodyLines[] = $line;
        }
        $tune->parseBodyLines($bodyLines);

        // Ensure bagpipe voice is created and cannt tokens generated
        $mockGen = new class {
            public function generateForNotes($notes) { return 'mock-cannt'; }
        };
        $service = new TuneService($mockGen);
        $service->ensureBagpipeVoice($tune);

        // Bagpipe voice should now exist
        $voices = $tune->getVoicesMeta();
        $this->assertNotEmpty($voices);
        $firstVoiceId = array_key_first($voices);
        $this->assertEquals('P', substr($firstVoiceId, 0, 1));

        // Bars in the bagpipe voice should have canntaireachd set (non-empty)
        $voiceBars = $tune->getVoiceBars();
        $this->assertArrayHasKey($firstVoiceId, $voiceBars);
        $bars = $voiceBars[$firstVoiceId];
        $this->assertNotEmpty($bars);
        foreach ($bars as $bar) {
            $this->assertNotEmpty($bar->getCanntaireachd());
        }
    }
}
