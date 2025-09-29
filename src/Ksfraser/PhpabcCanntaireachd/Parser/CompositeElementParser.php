<?php
namespace Ksfraser\PhpabcCanntaireachd\Parser;
use Ksfraser\PhpabcCanntaireachd\NoteElement\CompositeElement;
/**
 * Parser for CompositeElement (elements with sub-elements, e.g., directives, chords, annotations).
 */
class CompositeElementParser {
    protected $composite;
    public function __construct(CompositeElement $composite) {
        $this->composite = $composite;
    }
    public function parse($noteStr) {
        $results = [];
        foreach ($this->composite->getElements() as $keyword => $element) {
            if (strpos($noteStr, $keyword) !== false) {
                $results[$keyword] = $element;
            }
        }
        return !empty($results) ? $results : null;
    }
}
