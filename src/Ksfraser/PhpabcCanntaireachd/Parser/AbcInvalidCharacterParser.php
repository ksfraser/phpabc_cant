<?php
namespace Ksfraser\PhpabcCanntaireachd\Parser;
use Ksfraser\PhpabcCanntaireachd\NoteElement\AbcInvalidCharacter;
/**
 * Parser for AbcInvalidCharacter element in ABC notation.
 */
class AbcInvalidCharacterParser {
    public function parse($noteStr) {
        // Match any character not allowed in ABC tune body
        // Regex for invalid characters in ABC tune body
        // Allowable characters in ABC tune body
    $pattern = '/[^\d\w\s!"#$%&\'()*+,-.\/;<=>?@[\]^_`{|}~]/u';
        if (preg_match($pattern, $noteStr)) {
            return true;
        }
        return false;
    }
}
