<?php
namespace Ksfraser\PhpabcCanntaireachd\NoteElement;
/**
 * Class RedefinableSymbol
 *
 * Represents a user-defined symbol shortcut (via U: field) in ABC notation.
 * @uml
 * @startuml
 * class RedefinableSymbol {
 *   + getShortcut(): string
 *   + getType(): string
 *   + getName(): string
 *   + fromUserDefinition(string $shortcut, string $name): RedefinableSymbol
 * }
 * @enduml
 */
class RedefinableSymbol {
    protected $shortcut;
    protected $name;
    public function __construct($shortcut, $name) {
        $this->shortcut = $shortcut;
        $this->name = $name;
    }
    public static function getType() { return 'redefinable_symbol'; }
    public static function getName() { return 'redefinable_symbol'; }
    public function getShortcut() { return $this->shortcut; }
    public static function fromUserDefinition($shortcut, $name) {
        return new self($shortcut, $name);
    }
}
