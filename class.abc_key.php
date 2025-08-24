<?php

/**//**************************************************************
 * I have built an array of items from the DOCs from bmw to try
 * and convert into ABC.  This should save a bunch of typesetting tim.
 *
 * * ***************************************************************/

/***USAGE***
 * Quick Start
 *
 */

require_once( 'class.origin.php' );

/**//*********************************************************
* This class creates the voice line describing the voice for the header
*
*************************************************************/
class abc_key extends origin
{
	//gstem=up stem=down name="Snare" sname="Snare" ');
	protected $key;	//e.g. M or BB or ...
	protected $name;
	protected $octave;	//!<int +/- 2 would eliminate the need for ,, or '' on notes
	protected $transpose;	//!<int +/- integers indicating semitones +/-
				//This transposes outputted music (midi) 
				//without affecting the notes on the printed staff
	protected $callback;	//Function to process this voice
	protected $clef;	//!<string treble, bass, baritone, tenor, alto, mezzo and soprano
				//	+/-8 are recognized for these clefs.  Transposition may be
				// 	performed for TREBLE clefs (bass assumes all ,, included)

	/***********************************************************//**
	*
	***************************************************************/
	function __construct( $key, $octave = 0, $transpose = 0, $clef="treble", $callback = null )
	{
		$this->key = $key;
		$this->octave = $octave;
		$this->transpose = $transpose;
		$this->clef = $clef;
		$this->callback = $callback;
	}
	/**//******************************************
	* Format the class variables into the Voice line in the header
	*
	* returns something like "V:M name="Melody" sname="Melody" stem=down gstem=done octave=0 transpose=0 clef="Treble""
	* @param none
	* @returns string
	***********************************************/
	function get_header_out()
	{
		$out = "";
		$out .= "K:" . $this->key;
		if( isset( $this->octave ) )
		{
			$out .= " octave=" . $this->octave;
		}  
		if( isset( $this->transpose ) )
		{
			$out .= " transpose=" . $this->transpose;
		}  
		if( isset( $this->clef ) )
		{
			$out .= ' clef="' . $this->clef . '"';
		}  
		return $out;
	}
	/**//******************************************
	* Format the class variables into the Voice line in the body  
	*
	* returns something like "[V:M name="Melody" sname="Melody" stem=down gstem=done octave=0 transpose=0 clef="Treble"]"
	* @param none
	* @returns string
	***********************************************/
	function get_body_out()
	{
		$out = "[";
		$out .= "K:" . $this->key;
		if( isset( $this->octave ) )
		{
			$out .= " octave=" . $this->octave;
		}  
		if( isset( $this->transpose ) )
		{
			$out .= " transpose=" . $this->transpose;
		}  
		if( isset( $this->clef ) )
		{
			$out .= ' clef="' . $this->clef . '"';
		}  
		$out .= "]";
		return $out;
	}
}

