<?php
namespace Ksfraser\PhpabcCanntaireachd\Parser;
/**
 * Parses note length in ABC notation.
 */
class NoteLengthParser {
    public function parse($noteStr) {
        if (preg_match('/[a-gA-GzZ]([0-9]+)/', $noteStr, $m)) {
            return $m[1];
        }
        return '';
    }
}
