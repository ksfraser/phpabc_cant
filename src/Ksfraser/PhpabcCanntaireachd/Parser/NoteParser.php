<?php
namespace Ksfraser\PhpabcCanntaireachd\Parser;
/**
 * Parses the note (pitch) in ABC notation.
 */
class NoteParser {
    public static function getRegex() {
        return '/([a-gA-GzZ])/';
    }
    public function parse($noteStr) {
        if (preg_match('/([a-gA-GzZ])/', $noteStr, $m)) {
            return $m[1];
        }
        return '';
    }
}
