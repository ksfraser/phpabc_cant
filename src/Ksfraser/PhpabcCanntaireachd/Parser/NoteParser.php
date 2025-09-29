<?php
namespace Ksfraser\PhpabcCanntaireachd\Parser;
/**
 * Parses the note (pitch) in ABC notation.
 */
class NoteParser {
    public function parse($noteStr) {
        if (preg_match('/([a-gA-GzZ])/', $noteStr, $m)) {
            return $m[1];
        }
        return '';
    }
}
