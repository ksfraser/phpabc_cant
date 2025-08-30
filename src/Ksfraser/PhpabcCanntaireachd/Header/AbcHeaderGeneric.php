<?php
namespace Ksfraser\PhpabcCanntaireachd\Header;
//require_once __DIR__ . '/AbcHeaderT.php';
use Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderMultiField;
class AbcHeaderGeneric {
    protected $value = '';
    public static $label = '';
   public function __construct($value = null) {
        $this->value = [];
        if ($value !== null) $this->add($value);
    }
    public function add($value) {
        if (!is_array($this->value)) $this->value = [];
        $this->value[] = $value;
    }

    public function set($value) 
    { 
        $this->value = is_array($value) ? $value : [$value];
    }
    public function setLabel( $label ) {
        static::$label = $label;
    }   
    public function get() {     return is_array($this->value) ? implode(', ', $this->value) : $this->value; }
    public function render() {
        $out = '';
        if (is_array($this->value)) {
            foreach ($this->value as $v) {
                $out .= static::$label . ':' . $v . "\n";
            }
        } else {
            $out .= static::$label . ':' . $this->value . "\n";
        }
        return $out;
    }
}