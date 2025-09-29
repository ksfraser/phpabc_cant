<?php
namespace Ksfraser\PhpabcCanntaireachd\NoteElement;
/**
 * Class CompositeElement
 *
 * Represents a composite ABC element with sub-elements (e.g., directives, chords, annotations).
 * @uml
 * @startuml
 * class CompositeElement {
 *   + addElement($element): void
 *   + getElement($keyword)
 *   + getElements(): array
 * }
 * @enduml
 */
class CompositeElement {
    protected $elements = [];
    public function addElement($element) {
        $this->elements[$element->getName()] = $element;
    }
    public function getElement($keyword) {
        return $this->elements[$keyword] ?? null;
    }
    public function getElements() {
        return $this->elements;
    }
}
