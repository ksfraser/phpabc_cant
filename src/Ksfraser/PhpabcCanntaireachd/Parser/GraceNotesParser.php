<?php
namespace Ksfraser\PhpabcCanntaireachd\Parser;
/**
 * Parses grace notes in ABC notation.
 */
class GraceNotesParser {
    public function parse($noteStr) {
        if (preg_match('/\{([^}]*)\}/', $noteStr, $m)) {
            return preg_split('/\s+/', trim($m[1]));
        }
        return [];
    }
}
