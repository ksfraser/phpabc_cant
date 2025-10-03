<?php
namespace Ksfraser\PhpabcCanntaireachd;

use Ksfraser\PhpabcCanntaireachd\Tune\AbcBar;
use Ksfraser\PhpabcCanntaireachd\Tune\AbcTune;

class TuneService {
    protected $bagpipeAliases = ['P', 'pipes', 'pipe', 'bagpipe', 'BAGPIPES', 'Bagpipes'];
    protected $canntGenerator;

    public function __construct($gen) {
        $this->canntGenerator = $gen;
    }

    /**
     * Ensure a bagpipe voice exists on the tune. If missing, copy from melody ('M') and generate canntaireachd.
     */
    public function ensureBagpipeVoice(AbcTune $tune): void {
        $voices = $tune->getVoiceBars();

        // If no voice bars were parsed yet, try to populate them from tune lines
        if (empty($voices)) {
            $lines = [];
            foreach ($tune->getLines() as $lineObj) {
                if (is_string($lineObj)) {
                    $lines[] = $lineObj;
                } elseif (method_exists($lineObj, 'render')) {
                    $lines[] = trim($lineObj->render());
                } else {
                    $lines[] = (string)$lineObj;
                }
            }
            if (!empty($lines)) {
                $tune->parseBodyLines($lines);
                $voices = $tune->getVoiceBars();
            }
        }

        // Find existing bagpipe voice
        foreach ($this->bagpipeAliases as $alias) {
            if (isset($voices[$alias])) {
                // Validate/generate canntaireachd for existing voice
                $this->generateCanntForVoice($tune, $alias);
                return;
            }
        }
        // No bagpipe voice found; find melody 'M' or first voice
        $source = null;
        if (isset($voices['M'])) $source = 'M';
        else if (!empty($voices)) {
            reset($voices);
            $source = key($voices);
        }

        if ($source === null) {
            // Fallback: try to synthesize a bagpipe voice from any available body line
            $fallbackLine = '';
            foreach ($tune->getLines() as $lineObj) {
                $txt = '';
                if (is_string($lineObj)) $txt = $lineObj;
                elseif (method_exists($lineObj, 'render')) $txt = $lineObj->render();
                else $txt = (string)$lineObj;
                $txt = trim($txt);
                // Skip headers
                if ($txt === '' || preg_match('/^[A-Z]:/', $txt)) continue;
                // Prefer lines that contain note characters A-G
                if (preg_match('/[A-Ga-g]/', $txt)) {
                    $fallbackLine = $txt;
                    break;
                }
            }
            if ($fallbackLine !== '') {
                // Create a single AbcBar from the fallback line and insert as 'P'
                $bar = new AbcBar($fallbackLine);
                $tune->ensureVoiceInsertedFirst('P', [$bar]);
                $tune->addVoiceHeader('P', 'Bagpipes', 'Bagpipes');
                $headers = $tune->getHeaders();
                if (isset($headers['B']) && trim($headers['B']->get()) === '') {
                    $tune->replaceHeader('B', 'Bagpipes');
                }
                $this->generateCanntForVoice($tune, 'P');
            }
            return;
        }

        // Copy source voice to 'P' and insert as first voice
        $tune->copyVoice($source, 'P');
        // Prepend so Bagpipes becomes the first voice output
        $tune->ensureVoiceInsertedFirst('P', $tune->getVoiceBars()['P']);
        // Add a voice header meta entry for Bagpipes
        $tune->addVoiceHeader('P', 'Bagpipes', 'Bagpipes');
        // Ensure header B describes the bagpipe arrangement if missing
        $headers = $tune->getHeaders();
        if (isset($headers['B']) && trim($headers['B']->get()) === '') {
            $tune->replaceHeader('B', 'Bagpipes');
        }
        $this->generateCanntForVoice($tune, 'P');
    }

    protected function generateCanntForVoice(AbcTune $tune, string $voiceId): void {
        $voiceBars = $tune->getVoiceBars()[$voiceId] ?? [];
        foreach ($voiceBars as $barNum => $barObj) {
            // Build note sequence string from bar's notes
            $noteBody = $barObj->renderNotes();
            $cannt = $this->canntGenerator->generateForNotes($noteBody);
            if (!is_string($cannt) || trim((string)$cannt) === '') {
                $cannt = '[?]';
            }
            $barObj->setCanntaireachd($cannt);
            $canntVal = $barObj->getCanntaireachd();
            if (!is_string($canntVal)) {
                $canntVal = (string)$canntVal;
            }
            if (trim($canntVal) === '') {
                $barObj->setCanntaireachd($cannt);
            }
        }
    }
}
