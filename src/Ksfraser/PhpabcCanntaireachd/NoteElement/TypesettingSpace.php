<?php
namespace Ksfraser\PhpabcCanntaireachd\NoteElement;
/**
 * Class TypesettingSpace
 *
 * Represents extra space between notes in ABC notation, allowing chord/decorator attachment.
 * @uml
 * @startuml
 * class TypesettingSpace {
 *   + getShortcut(): string
 *   + getType(): string
 *   + getName(): string
 * }
 * @enduml
 */
class TypesettingSpace {
    public static function getShortcut() { return 'y'; }
    public static function getType() { return 'typesetting_space'; }
    public static function getName() { return 'typesetting_space'; }
}
