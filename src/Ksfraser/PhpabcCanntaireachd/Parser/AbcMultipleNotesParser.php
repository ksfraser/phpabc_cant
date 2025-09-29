<?php
namespace Ksfraser\PhpabcCanntaireachd\Parser;
use Ksfraser\PhpabcCanntaireachd\NoteElement\AbcMultipleNotes;
/**
 * Parser for AbcMultipleNotes element in ABC notation.
 */
class AbcMultipleNotesParser {
    public function parse($noteStr) {
        // Match multiple notes (e.g., "A B C")
        $matches = preg_match_all('/[a-gA-G]/', $noteStr, $out);
        if ($matches > 1) {
            return $out[0];
        }
        return null;
    }
}
