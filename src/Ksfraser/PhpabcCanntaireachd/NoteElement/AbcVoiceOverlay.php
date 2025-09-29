<?php
namespace Ksfraser\PhpabcCanntaireachd\NoteElement;
/**
 * Class AbcVoiceOverlay
 *
 * Represents the '&' operator for overlaying voices within a measure in ABC notation.
 * @uml
 * @startuml
 * class AbcVoiceOverlay {
 *   + getShortcut(): string
 *   + getType(): string
 *   + getName(): string
 * }
 * @enduml
 */
class AbcVoiceOverlay {
    public static function getShortcut() { return '&'; }
    public static function getType() { return 'voice_overlay'; }
    public static function getName() { return 'voice_overlay'; }
}
