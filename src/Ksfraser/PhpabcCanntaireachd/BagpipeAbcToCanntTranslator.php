<?php
namespace Ksfraser\PhpabcCanntaireachd;

/**
 * Class BagpipeAbcToCanntTranslator
 *
 * Translates a single Bagpipe ABC note/token to canntaireachd using a provided dictionary.
 * Can be extended for other translation directions (ABC<->BMW, BMW<->Cannt, etc).
 *
 * @package Ksfraser\PhpabcCanntaireachd
 */
require_once __DIR__ . '/AbcTokenTranslator.php';

class BagpipeAbcToCanntTranslator extends AbcTokenTranslator {
    /**
     * Translate a single AbcNote to canntaireachd.
     * @param AbcNote $note
     * @return string|null The canntaireachd token, or null if not found.
     */
    public function translate($note) {
        if (!($note instanceof AbcNote)) {
            throw new \InvalidArgumentException('Expected AbcNote');
        }
        $abcToken = $note->get_body_out();
        file_put_contents('debug.log', "BagpipeAbcToCanntTranslator::translate: abcToken={$abcToken}\n", FILE_APPEND);
        // Try exact match, then normalized (strip digits)
        $cannt = $this->dictionary->convertAbcToCannt($abcToken);
        file_put_contents('debug.log', "BagpipeAbcToCanntTranslator::translate: cannt (exact)=".var_export($cannt, true)."\n", FILE_APPEND);
        if ($cannt === null) {
            $norm = preg_replace('/\d+/', '', $abcToken);
            $cannt = $this->dictionary->convertAbcToCannt($norm);
            file_put_contents('debug.log', "BagpipeAbcToCanntTranslator::translate: cannt (norm)=".var_export($cannt, true)."\n", FILE_APPEND);
        }
        return $cannt;
    }
    // Future: add translateBmwToCannt, translateCanntToBmw, etc.
}
