<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd\NoteElement;
/**
 * Class AbcInvalidCharacter
 *
 * Represents an invalid character in the tune body of ABC notation.
 * @uml
 * @startuml
 * class AbcInvalidCharacter {
 *   + getType(): string
 *   + getName(): string
 * }
 * @enduml
 */
class AbcInvalidCharacter {
    public static function getType() { return 'invalid_character'; }
    public static function getName() { return 'invalid_character'; }
}
