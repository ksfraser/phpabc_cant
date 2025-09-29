<?php
namespace Ksfraser\PhpabcCanntaireachd\Parser;
use Ksfraser\PhpabcCanntaireachd\NoteElement\AbcBrokenRhythm;
/**
 * Parser for AbcBrokenRhythm ('<' and '>') element in ABC notation.
 */
class AbcBrokenRhythmParser {
    public function parse($noteStr) {
        $found = [];
        if (strpos($noteStr, '<') !== false) {
            $found[] = '<';
        }
        if (strpos($noteStr, '>') !== false) {
            $found[] = '>';
        }
        return !empty($found) ? $found : null;
    }
}
