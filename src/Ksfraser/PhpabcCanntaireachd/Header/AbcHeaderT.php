<?php
namespace Ksfraser\PhpabcCanntaireachd\Header;

abstract class AbcHeaderField {
    protected $value = '';
    public static $label = '';
    public function __construct($value = '') { $this->value = $value; }
    public function set($value) { $this->value = $value; }
    public function get() { return $this->value; }
    public function render() { return static::$label . ':' . $this->value . "\n"; }
}

class AbcHeaderT extends AbcHeaderField {
    public static $label = 'T';
}
