<?php
namespace Ksfraser\PhpabcCanntaireachd\Tune;

/**
 * Context for detecting melody and bagpipes voices in a tune.
 * SRP: Holds state for melody/bagpipes detection logic.
 */
class VoiceDetectionContext {
    public $melodyHeaderLine = null;
    public $bagpipesHeaderPresent = false;
    public $bagpipesHeader = null;
    public $inserted = false;
    public $hasMelody = false;
    public $melodyVoiceId = null;

    private $voice = null;

    public function setVoice($voice) {
        $this->voice = $voice;
    }
    public function getVoice() {
        return $this->voice;
    }
    public function isBagpipeVoice() {
        return in_array(strtolower($this->voice), ['pipes', 'bagpipe', 'bagpipes', 'p']);
    }
}
