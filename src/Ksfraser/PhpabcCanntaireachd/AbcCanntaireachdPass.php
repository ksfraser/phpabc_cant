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
    public function process(array $lines, $logFlow = false): array {
    FlowLog::log('AbcCanntaireachdPass::process ENTRY', true);
    $canntDiff = [];
    $output = AbcProcessor::validateCanntaireachd($lines, $canntDiff);

        // Use BagpipeAbcToCanntTranslator for translation
        $translator = new \Ksfraser\PhpabcCanntaireachd\BagpipeAbcToCanntTranslator($this->dict);
        $translatedOutput = [];
    foreach ($output as $line) {
            // Only translate music lines (not headers/comments)
            if ($this->isMusicLine($line)) {
                $musicTokens = preg_split('/\s+/', trim($line));
                $canntTokens = array_map(function($token) use ($translator) {
                    $note = new \Ksfraser\PhpabcCanntaireachd\AbcNote($token);
                    return $translator->translate($note);
                }, $musicTokens);
                $canntTextAligned = trim(implode(' ', $canntTokens));
                $translatedOutput[] = $line;
                if ($canntTextAligned && $canntTextAligned !== '[?]') {
                    $translatedOutput[] = 'w: ' . $canntTextAligned;
                }
            } else {
                $translatedOutput[] = $line;
            }
        }
        // Remove TIMING markers from the output while keeping logs
        foreach ($translatedOutput as &$line) {
            if (strpos($line, 'TIMING') !== false) {
                \Ksfraser\PhpabcCanntaireachd\Log\GeneralLog::log("Timing issue detected: $line", true);
                $line = str_replace(' TIMING', '', $line);
            }
        }
    $result = ['lines' => $translatedOutput, 'canntDiff' => $canntDiff];
    FlowLog::log('AbcCanntaireachdPass::process EXIT', true);
    return $result;
    }
    
    // Lyrics generation now handled by LyricsGenerator class

    private function isMusicLine($line) {
        $trimmed = trim($line);
        // Exclude empty lines and comments
        if ($trimmed === '' || preg_match('/^%/', $trimmed)) {
            return false;
        }
        // Exclude header lines (X:, T:, M:, L:, K:, etc.)
        if (preg_match('/^[A-Z]:/', $trimmed)) {
            return false;
        }
        // Exclude voice definition lines (V: at start)
        if (preg_match('/^V:/', $trimmed)) {
            return false;
        }
        // Accept lines starting with [V:...] as music lines
        if (preg_match('/^\[V:[^\]]+\]/', $trimmed)) {
            return true;
        }
        // Accept lines with music notation (notes, bars, etc.)
        if (preg_match('/[A-Ga-gzZ\|\[\]\{\}]/', $trimmed)) {
            return true;
        }
        return false;
    }
}
