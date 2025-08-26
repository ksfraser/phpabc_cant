<?php
namespace Ksfraser\PhpabcCanntaireachd\Header;
/**
 * Multi-value ABC header field (e.g., C: composer, B: book)
 */
class AbcHeaderMultiField {
    protected $values = [];
    public function add($value) {
        $this->values[] = $value;
    }
    public function set($value) {
        $this->values = [$value];
    }
    public function get() {
        return implode(', ', $this->values);
    }
}
