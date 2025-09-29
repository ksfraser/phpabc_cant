<?php
namespace Ksfraser\PhpabcCanntaireachd\NoteElement;
/**
 * Class AbcMultipleNotes
 *
 * Represents a sequence of multiple notes in ABC notation.
 * @uml
 * @startuml
 * class AbcMultipleNotes {
 *   + getType(): string
 *   + getName(): string
 * }
 * @enduml
 */
class AbcMultipleNotes {
    public static function getType() { return 'multiple_notes'; }
    public static function getName() { return 'multiple_notes'; }
}
