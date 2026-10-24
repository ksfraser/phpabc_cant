<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd\Parser;
/**
 * Parses accidentals in ABC notation.
 */
class AccidentalsParser {
    public static function getRegex() {
        return '/[=_^]/';
    }
    public function parse($noteStr) {
        preg_match_all('/[=_^]/', $noteStr, $m);
        return $m[0] ?? [];
    }
}
