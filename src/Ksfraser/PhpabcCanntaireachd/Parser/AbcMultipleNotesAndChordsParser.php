<?php
namespace Ksfraser\PhpabcCanntaireachd\Parser;
use Ksfraser\PhpabcCanntaireachd\NoteElement\AbcMultipleNotesAndChords;
/**
 * Parser for AbcMultipleNotesAndChords element in ABC notation.
 */
class AbcMultipleNotesAndChordsParser {
    public function parse($noteStr) {
        // Match multiple notes and chords (e.g., "A [CEG] B")
        $noteMatches = preg_match_all('/[a-gA-G]/', $noteStr, $notes);
        $chordMatches = preg_match_all('/\[[a-gA-G]+\]/', $noteStr, $chords);
        if ($noteMatches + $chordMatches > 1) {
            return array_merge($notes[0], $chords[0]);
        }
        return null;
    }
}
