<?php
namespace Ksfraser\PhpabcCanntaireachd\Header;

abstract class AbcHeaderField {
    protected $value = '';
    public static $label = '';
    public function __construct($value = '') { $this->value = $value; }
    public function set($value) { $this->value = $value; }
    public function get() { return $this->value; }
    public function render() {
        if (is_array($this->value)) {
            $out = '';
            foreach ($this->value as $v) {
                $out .= static::$label . ':' . $v . "\n";
            }
            return $out;
        }
        return static::$label . ':' . $this->value . "\n";
    }
}

// Multi-value field base
abstract class AbcHeaderMultiField extends AbcHeaderField {
    protected $value = [];
    public function __construct($value = null) { if ($value !== null) $this->value = (array)$value; }
    public function add($v) { $this->value[] = $v; }
    public function set($v) { $this->value = (array)$v; }
    public function get() { return $this->value; }
    public function render() {
        if (!$this->value) return static::$label . ":\n";
        $out = '';
        foreach ($this->value as $v) $out .= static::$label . ':' . $v . "\n";
        return $out;
    }
}

class AbcHeaderA extends AbcHeaderField { public static $label = 'A'; }
class AbcHeaderB extends AbcHeaderMultiField { public static $label = 'B'; }
class AbcHeaderC extends AbcHeaderMultiField { public static $label = 'C'; }
class AbcHeaderD extends AbcHeaderField { public static $label = 'D'; }
class AbcHeaderE extends AbcHeaderField { public static $label = 'E'; }
class AbcHeaderF extends AbcHeaderField { public static $label = 'F'; }
class AbcHeaderG extends AbcHeaderField { public static $label = 'G'; }
class AbcHeaderH extends AbcHeaderField { public static $label = 'H'; }
class AbcHeaderI extends AbcHeaderField { public static $label = 'I'; }
class AbcHeaderK extends AbcHeaderField { public static $label = 'K'; }
class AbcHeaderL extends AbcHeaderField { public static $label = 'L'; }
class AbcHeaderM extends AbcHeaderField { public static $label = 'M'; }
class AbcHeaderN extends AbcHeaderField { public static $label = 'N'; }
class AbcHeaderO extends AbcHeaderField { public static $label = 'O'; }
class AbcHeaderP extends AbcHeaderField { public static $label = 'P'; }
class AbcHeaderQ extends AbcHeaderField { public static $label = 'Q'; }
class AbcHeaderR extends AbcHeaderField { public static $label = 'R'; }
class AbcHeaderS extends AbcHeaderField { public static $label = 'S'; }
class AbcHeaderT extends AbcHeaderField { public static $label = 'T'; }
class AbcHeaderU extends AbcHeaderField { public static $label = 'U'; }
class AbcHeaderV extends AbcHeaderField { public static $label = 'V'; }
class AbcHeaderW extends AbcHeaderField { public static $label = 'W'; }
class AbcHeaderX extends AbcHeaderField { public static $label = 'X'; }
class AbcHeaderY extends AbcHeaderField { public static $label = 'Y'; }
class AbcHeaderZ extends AbcHeaderField { public static $label = 'Z'; }
