<?php
namespace Ksfraser\PhpabcCanntaireachd\Parser;
use Ksfraser\PhpabcCanntaireachd\NoteElement\TypesettingSpace;
/**
 * Parser for TypesettingSpace ('y') element in ABC notation.
 */
class TypesettingSpaceParser {
    public function parse($noteStr) {
        // Match 'y' anywhere in the note string
        if (strpos($noteStr, TypesettingSpace::getShortcut()) !== false) {
            return TypesettingSpace::getShortcut();
        }
        return null;
    }
}
