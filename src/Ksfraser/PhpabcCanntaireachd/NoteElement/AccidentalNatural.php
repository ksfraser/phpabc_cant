<?php
namespace Ksfraser\PhpabcCanntaireachd\NoteElement;
/**
 * Class AccidentalNatural
 *
 * Represents a natural accidental in ABC notation.
 * @uml
 * @startuml
 * class AccidentalNatural {
 *   + getShortcut(): string
 *   + getType(): string
 *   + getName(): string
 * }
 * @enduml
 */
class AccidentalNatural {
    public static function getShortcut() { return '='; }
    public static function getType() { return 'accidental'; }
    public static function getName() { return 'natural'; }
}
