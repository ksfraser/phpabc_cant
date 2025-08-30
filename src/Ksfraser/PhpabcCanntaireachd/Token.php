<?php
namespace Ksfraser\PhpabcCanntaireachd;

/**
 * Token: represents a musical element (note, decorator, etc.)
 */
class Token {
    public $type;
    public $value;
    public $meta = [];
    public function __construct($type, $value, $meta = []) {
        $this->type = $type;
        $this->value = $value;
        $this->meta = $meta;
    }
}
