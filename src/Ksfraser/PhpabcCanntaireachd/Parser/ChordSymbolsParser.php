<?php
namespace Ksfraser\PhpabcCanntaireachd\Parser;
/**
 * Parses chord symbols in ABC notation.
 */
class ChordSymbolsParser {
    public static function getRegex() {
        return '/"([^"]+)"/';
    }
    public function parse($noteStr) {
        if (preg_match('/"([^"]+)"/', $noteStr, $m)) {
            return $m[1];
        }
        return null;
    }
}
