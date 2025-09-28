<?php
namespace Ksfraser\PhpabcCanntaireachd\Header;

use Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderField;

/**
 * Voice Header Fields are kind of special
 *
 * There can be multiple voices, but unlike the other multis
 *  it has other values in the middle that need to be voice specific
 *  so no array for this one!
 */
class AbcHeaderV extends AbcHeaderField {
	public static $label = 'V';
	protected $name;
	protected $subname;
	protected $stem;	//up or down
	protected $gstem;	//up or down
	protected $clef;	//treble, alto, tenor, bass, perc or none.
	protected $transpose;	//semitones
	protected $middle;	//The pitch indicates what note is displayed on the 3rd line of the staff. Defaults are: treble: B; alto: C; tenor: A,; bass: D,; none: B. 
	protected $octave;	//helps reduce commas and apostraphes
	protected $stafflines;	//default 5

	public function setName( $name )
	{
		$this->name = $name;
	}
	public function setSname( $name )
	{
		$this->sname = $name;
	}
	public function setStem( $stem )
	{
		$this->stem = $stem;
	}
	public function setGstem( $gstem )
	{
		$this->gstem = $gstem;
	}
	public function setClef( $clef )
	{
		$this->clef = $clef;
	}
	public function setTranspose( $transpose )
	{
		$this->transpose = $transpose;
	}
	public function setMiddle( $middle )
	{
		$this->middle = $middle;
	}
	public function setOctave( $octave )
	{
		$this->octave = $octave;
	}
	public function setStafflines( $stafflines )
	{
		$this->stafflines = $stafflines;
	}

        public function render()
        {
                $out = '';
                foreach ($this->value as $v)
                {
                        $out .= static::$label . ':' . $v;
			if( isset( $this->sname )
			{
				$out .= ' sname="' . $this->sname . '"';
			}
			if( isset( $this->name )
			{
				$out .= ' name="' . $this->name . '"';
			}
			if( isset( $this->stem )
			{
				$out .= ' stem=' . $this->stem;
			}
			if( isset( $this->gstem )
			{
				$out .= ' gstem=' . $this->gstem;
			}
			if( isset( $this->clef )
			{
				$out .= ' clef=' . $this->clef;
			}
			if( isset( $this->transpose )
			{
				$out .= ' transpose=' . $this->transpose;
			}
			if( isset( $this->middle )
			{
				$out .= ' middle=' . $this->middle;
			}
			if( isset( $this->octave )
			{
				$out .= ' octave=' . $this->octave;
			}
			if( isset( $this->stafflines )
			{
				$out .= ' stafflines=' . $this->stafflines;
			}
                        $out .= "\n";
                }
                return $out;
        }
	public function fixNames()
	{
		$if( ! isset ($this->name ) )
		{
			$this->name = $this->value;
			if( isset( $this->sname ) )
			{
				$this->name = $this->sname;
			}
		}
		$if( ! isset ($this->sname ) )
		{
			$this->sname = $this->value;
			if( isset( $this->name ) )
			{
				$this->sname = $this->name;
			}
		}
	}
}
