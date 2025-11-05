<?php
namespace Ksfraser\PhpabcCanntaireachd;

use Ksfraser\PhpabcCanntaireachd\Log\DebugLog;

/**
 * Class BagpipeAbcToCanntTranslator
 *
 * Translates a single Bagpipe ABC note/token to canntaireachd using a provided dictionary.
 * Can be extended for other translation directions (ABC<->BMW, BMW<->Cannt, etc).
 *
 * @package Ksfraser\PhpabcCanntaireachd
 *
 * @uml
 * @startuml
 * class AbcTokenTranslator {
 *   - dictionary: TokenDictionary
 *   + __construct(dictionary: TokenDictionary)
 *   + translate(token): string|null
 * }
 * AbcTokenTranslator <|-- BagpipeAbcToCanntTranslator
 * class BagpipeAbcToCanntTranslator {
 *   + translate(note: AbcNote): string|null
 * }
 * class TokenDictionary {
 *   + convertAbcToCannt(token: string): string|null
 * }
 * class AbcNote {
 *   + get_body_out(): string
 * }
 * BagpipeAbcToCanntTranslator --> TokenDictionary : uses
 * BagpipeAbcToCanntTranslator --> AbcNote : translates
 * @enduml
 *
 * @sequence
 * @startuml
 * participant User
 * participant BagpipeAbcToCanntTranslator
 * participant AbcNote
 * participant TokenDictionary
 * User -> BagpipeAbcToCanntTranslator: translate(note)
 * BagpipeAbcToCanntTranslator -> AbcNote: get_body_out()
 * AbcNote --> BagpipeAbcToCanntTranslator: abcToken
 * BagpipeAbcToCanntTranslator -> TokenDictionary: convertAbcToCannt(abcToken)
 * TokenDictionary --> BagpipeAbcToCanntTranslator: canntToken
 * BagpipeAbcToCanntTranslator -> TokenDictionary: convertAbcToCannt(norm)
 * TokenDictionary --> BagpipeAbcToCanntTranslator: canntToken|null
 * BagpipeAbcToCanntTranslator --> User: canntToken|null
 * @enduml
 *
 * @flowchart
 * @startuml
 * start
 * :Check note is AbcNote;
 * if (not AbcNote) then (no)
 *   :throw InvalidArgumentException;
 *   stop
 * else (yes)
 *   :abcToken = note.get_body_out();
 *   :cannt = dictionary.convertAbcToCannt(abcToken);
 *   if (cannt == null) then (yes)
 *     :norm = strip digits from abcToken;
 *     :cannt = dictionary.convertAbcToCannt(norm);
 *   endif
 *   :return cannt;
 *   stop
 * endif
 * @enduml
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
        DebugLog::log('BagpipeAbcToCanntTranslator::translate: abcToken=' . $abcToken, true);
        // Try exact match, then normalized (strip digits)
        $cannt = $this->dictionary->convertAbcToCannt($abcToken);
        DebugLog::log('BagpipeAbcToCanntTranslator::translate: cannt (exact)=' . var_export($cannt, true), true);
        if ($cannt === null) {
            $norm = preg_replace('/\d+/', '', $abcToken);
            $cannt = $this->dictionary->convertAbcToCannt($norm);
            DebugLog::log('BagpipeAbcToCanntTranslator::translate: cannt (norm)=' . var_export($cannt, true), true);
        }
        return $cannt;
    }
    // Future: add translateBmwToCannt, translateCanntToBmw, etc.
}
