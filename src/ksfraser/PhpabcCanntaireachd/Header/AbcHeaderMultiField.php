<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd\Header;

use ksfraser\PhpabcCanntaireachd\Header\AbcHeaderField; 
/**
 * Multi-value ABC header field (e.g., C: composer, B: book)
 */
abstract class AbcHeaderMultiField extends AbcHeaderField {
	public function set($value)
	{
		// Only set if not already set or empty
		if( ! isset( $this->value ) || $this->value === ''  )
		{
			$this->value = [];
		}
		$this->value[] = $value;
	}
	public function get() 
	{ 
		return $this->value; 
	}
	public function render()
	{
		$out = '';
		foreach ($this->value as $v) 
		{
			$out .= static::$label . ':' . $v . "\n";
		}
		return $out;
	}
	/**
	 * Add multi field value (stripped of X:) to array
	 *
	 * @param string
	 */
       public function add($value)
       {
	       $this->set($value);
       }
}
