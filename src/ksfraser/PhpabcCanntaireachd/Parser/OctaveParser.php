<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd\Parser;
/**
 * Parses octave modifiers in ABC notation.
 */
class OctaveParser {
    public static function getRegex() {
        return "/([',]+)/";
    }
    public function parse($noteStr) {
        if (preg_match("/([',]+)/", $noteStr, $m)) {
            return $m[1];
        }
        return '';
    }
}
