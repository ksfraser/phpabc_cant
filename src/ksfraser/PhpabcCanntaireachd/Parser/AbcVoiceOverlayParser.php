<?php
namespace ksfraser\PhpabcCanntaireachd\Parser;
use ksfraser\PhpabcCanntaireachd\NoteElement\AbcVoiceOverlay;
/**
 * Parser for AbcVoiceOverlay ('&') element in ABC notation.
 */
class AbcVoiceOverlayParser {
    public function parse($noteStr) {
        if (strpos($noteStr, AbcVoiceOverlay::getShortcut()) !== false) {
            return AbcVoiceOverlay::getShortcut();
        }
        return null;
    }
}
