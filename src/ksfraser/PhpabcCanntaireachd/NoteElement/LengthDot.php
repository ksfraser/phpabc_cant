<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd\NoteElement;
/**
 * Class LengthDot
 *
 * Represents a dotted note length in ABC notation.
 * @uml
 * @startuml
 * class LengthDot {
 *   + getShortcut(): string
 *   + getType(): string
 *   + getName(): string
 * }
 * @enduml
 */
class LengthDot {
    public static function getShortcut() { return '.'; }
    public static function getType() { return 'length'; }
    public static function getName() { return 'dot'; }
}
