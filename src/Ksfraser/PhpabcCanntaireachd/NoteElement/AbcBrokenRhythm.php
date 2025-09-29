<?php
namespace Ksfraser\PhpabcCanntaireachd\NoteElement;
/**
 * Class AbcBrokenRhythm
 *
 * Represents broken rhythm notation ('<' and '>') in ABC.
 * @uml
 * @startuml
 * class AbcBrokenRhythm {
 *   + getShortcut(): string
 *   + getType(): string
 *   + getName(): string
 * }
 * @enduml
 */
class AbcBrokenRhythm {
    public static function getShortcut() { return '<>'; }
    public static function getType() { return 'broken_rhythm'; }
    public static function getName() { return 'broken_rhythm'; }
}
