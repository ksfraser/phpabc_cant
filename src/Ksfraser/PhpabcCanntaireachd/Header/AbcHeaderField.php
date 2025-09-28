<?php
namespace Ksfraser\PhpabcCanntaireachd\Header;

abstract class AbcHeaderField {
    protected $value = '';
    public static $label = '';
    public static $multi = false;
    public function __construct($value = '') { $this->value = $value; }
	//For headers that allow multiple lines, we will need to override the set fcn
    public function set($value) 
    { 
        // Only set if not already set or empty
        if( ! isset( $this->value ) || $this->value === ''  )
	{
		if( ! $this->multi )
		{
            		$this->value = $value; 
		}
		else
		{
            		$this->value[] = $value; 
		}
    }
    public function get() { return $this->value; }
    public function render()
	{
        if (is_array($this->value)) {
            $out = '';
            foreach ($this->value as $v) {
                $out .= static::$label . ':' . $v . "\n";
            }
            return $out;
        }
        return static::$label . ':' . $this->value . "\n";
    }
    /**
     * Add multi field value (stripped of X:) to array
     * 
     * @param string
     */
    public add( $value )
    {
	//If multi, add to array else ignore
	if( $this->multi )
	{
		$this->value[] = $value;
	}
    }
}
