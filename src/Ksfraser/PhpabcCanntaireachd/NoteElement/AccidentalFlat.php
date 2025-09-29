<?php
namespace Ksfraser\PhpabcCanntaireachd\NoteElement;
/**
 * Class AccidentalFlat
 *
 * Represents a flat accidental in ABC notation.
 * @uml
 * @startuml
 * class AccidentalFlat {
 *   + getShortcut(): string
 *   + getType(): string
 *   + getName(): string
 * }
 * @enduml
 */
class AccidentalFlat {
    public static function getShortcut() { return '_'; }
    public static function getType() { return 'accidental'; }
    public static function getName() { return 'flat'; }
}
