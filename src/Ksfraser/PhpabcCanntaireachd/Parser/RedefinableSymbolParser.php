<?php
namespace Ksfraser\PhpabcCanntaireachd\Parser;
use Ksfraser\PhpabcCanntaireachd\NoteElement\RedefinableSymbol;
/**
 * Parser for RedefinableSymbol (user-defined shortcuts via U: field) in ABC notation.
 */
class RedefinableSymbolParser {
    protected $userSymbols = [];
    public function __construct($userSymbols = []) {
        $this->userSymbols = $userSymbols;
    }
    public function parse($noteStr) {
        foreach ($this->userSymbols as $shortcut => $name) {
            if (strpos($noteStr, $shortcut) !== false) {
                return RedefinableSymbol::fromUserDefinition($shortcut, $name);
            }
        }
        return null;
    }
}
