<?php
namespace Ksfraser\PhpabcCanntaireachd\NoteElement;
/**
 * Class AccidentalDoubleFlat
 *
 * Represents a double flat accidental in ABC notation.
 * @uml
 * @startuml
 * class AccidentalDoubleFlat {
 *   + getShortcut(): string
 *   + getType(): string
 *   + getName(): string
 * }
 * @enduml
 */
class AccidentalDoubleFlat {
    public static function getShortcut() { return '__'; }
    public static function getType() { return 'accidental'; }
    public static function getName() { return 'double_flat'; }
}
