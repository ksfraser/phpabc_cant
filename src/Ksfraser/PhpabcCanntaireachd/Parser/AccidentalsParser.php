<?php
namespace Ksfraser\PhpabcCanntaireachd\Parser;
/**
 * Parses accidentals in ABC notation.
 */
class AccidentalsParser {
    public function parse($noteStr) {
        preg_match_all('/[=_^]/', $noteStr, $m);
        return $m[0] ?? [];
    }
}
