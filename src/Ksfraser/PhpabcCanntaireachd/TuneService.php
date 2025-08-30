<?php
namespace Ksfraser\PhpabcCanntaireachd;

class TuneService {
    protected $bagpipeAliases = ['P', 'pipes', 'pipe', 'bagpipe', 'BAGPIPES'];
    protected $canntGenerator;

    public function __construct(?CanntGenerator $gen = null) {
        $this->canntGenerator = $gen ?? new CanntGenerator();
    }

    /**
     * Ensure a bagpipe voice exists on the tune. If missing, copy from melody ('M') and generate canntaireachd.
     */
    public function ensureBagpipeVoice(AbcTune $tune): void {
        $voices = $tune->getVoiceBars();
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
        if ($source === null) return;
        // Copy source voice to 'P'
        $tune->copyVoice($source, 'P');
        $this->generateCanntForVoice($tune, 'P');
    }

    protected function generateCanntForVoice(AbcTune $tune, string $voiceId): void {
        $voiceBars = $tune->getVoiceBars()[$voiceId] ?? [];
        foreach ($voiceBars as $barNum => $barObj) {
            // Build note sequence string from bar's notes
            $noteBody = $barObj->renderNotes();
            $cannt = $this->canntGenerator->generateForNotes($noteBody);
            $barObj->setCanntaireachd($cannt);
        }
    }
}
