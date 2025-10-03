<?php 

namespace Ksfraser\PhpabcCanntaireachd;

use Ksfraser\PhpabcCanntaireachd\Log\FlowLog;
/**
 * Class AbcCanntaireachdPass
 *
 * Processes ABC lines and generates canntaireachd lyrics using the translation pipeline.
 *
 * @uml
 * @startuml
 * class AbcCanntaireachdPass {
 *   - dict: TokenDictionary
 *   + process(lines: array): array
 * }
 * AbcCanntaireachdPass --> BagpipeAbcToCanntTranslator : uses
 * BagpipeAbcToCanntTranslator --> TokenDictionary : uses
 * @enduml
 *
 * @sequence
 * @startuml
 * participant User
 * participant AbcCanntaireachdPass
 * participant BagpipeAbcToCanntTranslator
 * participant TokenDictionary
 * User -> AbcCanntaireachdPass: process(lines)
 * AbcCanntaireachdPass -> BagpipeAbcToCanntTranslator: translate(note)
 * BagpipeAbcToCanntTranslator -> TokenDictionary: convertAbcToCannt(token)
 * TokenDictionary --> BagpipeAbcToCanntTranslator: canntToken
 * BagpipeAbcToCanntTranslator --> AbcCanntaireachdPass: canntToken
 * AbcCanntaireachdPass --> User: output
 * @enduml
 *
 * @flowchart
 * @startuml
 * start
 * :Receive ABC lines;
 * :Validate lines;
 * :For each music line, split into tokens;
 * :For each token, create AbcNote and translate;
 * :Collect canntaireachd tokens;
 * :Output original and w: lines;
 * stop
 * @enduml
 */
class AbcCanntaireachdPass {
    private $dict;
    /**
     * @param array|TokenDictionary $dict
     */
    public function __construct($dict) {
        if ($dict instanceof TokenDictionary) {
            $this->dict = $dict;
        } else {
            $td = new TokenDictionary();
            $td->prepopulate($dict);
            $this->dict = $td;
        }
    }
    /**
     * @param array $lines
     * @return array
     */
    /**
     * Process a parsed AbcTune, generating canntaireachd for Bagpipe voices only.
     * @param \Ksfraser\PhpabcCanntaireachd\Tune\AbcTune $tune
     * @param bool $logFlow
     * @return void
     */
    public function process($tune, $logFlow = false): void {
        FlowLog::log('AbcCanntaireachdPass::process ENTRY', true);
        $translator = new \Ksfraser\PhpabcCanntaireachd\BagpipeAbcToCanntTranslator($this->dict);
        foreach ($tune->getVoices() as $voiceId => $voice) {
            if ($voice instanceof \Ksfraser\PhpabcCanntaireachd\Voices\BagpipeVoice) {
                $bars = $tune->getVoiceBars()[$voiceId] ?? [];
                foreach ($bars as $bar) {
                    if (!isset($bar->notes) || !is_array($bar->notes)) continue;
                    foreach ($bar->notes as $note) {
                        if ($note instanceof \Ksfraser\PhpabcCanntaireachd\AbcNote) {
                            $cannt = $translator->translate($note);
                            $note->setCanntaireachd($cannt);
                        }
                    }
                }
            } else {
                // For non-bagpipe voices, ensure canntaireachd is null on all notes
                $bars = $tune->getVoiceBars()[$voiceId] ?? [];
                foreach ($bars as $bar) {
                    if (!isset($bar->notes) || !is_array($bar->notes)) continue;
                    foreach ($bar->notes as $note) {
                        if ($note instanceof \Ksfraser\PhpabcCanntaireachd\AbcNote) {
                            $note->setCanntaireachd(null);
                        }
                    }
                }
            }
        }
        FlowLog::log('AbcCanntaireachdPass::process EXIT', true);
    }
    
    // Lyrics generation now handled by LyricsGenerator class

    // (Legacy line-oriented code removed; now fully object-based)
}
