<?php
namespace Ksfraser\PhpabcCanntaireachd\NoteElement;
/**
 * Class OctaveUp
 *
 * Represents an octave up modifier in ABC notation.
 * @uml
 * @startuml
 * class OctaveUp {
 *   + getShortcut(): string
 *   + getType(): string
 *   + getName(): string
 * }
 * @enduml
 */
class OctaveUp {
    public static function getShortcut() { return "'"; }
    public static function getType() { return 'octave'; }
    public static function getName() { return 'up'; }
}
