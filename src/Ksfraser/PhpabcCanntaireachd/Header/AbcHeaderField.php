<?php
namespace Ksfraser\PhpabcCanntaireachd\Header;

abstract class AbcHeaderField {
    protected $value = '';
    public static $label = '';
    public function __construct($value = '') { $this->set( $value ); }
	//For headers that allow multiple lines, we will need to override the set fcn
    public function set($value) 
    { 
        // Only set if not already set or empty
        if( ! isset( $this->value ) || $this->value === ''  )
	{
       		$this->value = $value; 
        }
    }
    public function get() { return $this->value; }
    public function render()
    {
        return static::$label . ':' . $this->value . "\n";
    }
}
