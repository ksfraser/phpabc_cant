<?php
namespace Ksfraser\PhpabcCanntaireachd\Parser;
/**
 * Parses annotations/decorations in ABC notation.
 */
class AnnotationsParser {
    public function parse($noteStr) {
        preg_match_all('/!(.*?)!/', $noteStr, $m);
        return $m[0] ?? [];
    }
}
