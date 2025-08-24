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
* This class assumes that the creating code has alread parsed a string
* into the Pitch, octave, sharp/flat/...
*
*	From the documentation (https://abc.sourceforge.net/standard/abc2midi.txt)
*		A note consists of a pitch specifier followed by a length. Available pitch
*		specifiers are :
*		
*		C, D, E, F, G, A, B, C D E F G A B c d e f g a b c' d' e' f' g' a' b'
*		
*		This covers 4 octaves. abc2midi allows lower octaves to be reached by
*		adding extra , characters and higher octaves to be reached by adding
*		extra ' characters. However, this is not standard abc and may not be
*		supported by other abc utilities.
*		
*		You can raise or lower the pitch specifier a semitone by preceding it with
*		^ or _ respectively. The key signature and preceding sharps, flats and
*		barlines modify the default pitch in the same way as on a stave. Preceding 
*		a note with = generates natural pitch and ^^ and __ can be used for double 
*		sharp and double flat respectively.
*		
*		The length is in general specified by a fraction following the pitch
*		specifier. However, the notation is made more concise by allowing much
*		of the fraction to be omitted.
*		
*		C  - selects a note of 1 unit note length.
*		C2 - selects a note of 2 unit note lengths.
*		C/2 - selects a note of 1/2 unit note length.
*		C3/4 - selects a note of 3/4 unit note length.
*		
*		C/ is allowed as an abbreviation of C/2.
*		C// is allowed as an abbreviation of C/4. However, this is not standard
*		notation and is not allowed by all abc programs.
*		
*		No space is allowed within a note, but space may be used to separate
*		notes in the tune.
*		
*		Rests are written by using 'z' as the pitch specifier.
*
*************************************************************/
class abc_note extends origin
{
	protected $pitch;	//a-gA-G
	protected $octave;	//, or '
	protected $sharpflat;	// =^_	null/natural/sharp/flat
	protected $length;	//!<string	(int)(/)(int)
	protected $decorator;	//!<string .MHTR!trill!	stacatto Legato Fermato Trill Roll
	protected $name;
	protected $cannt;
	protected $callback;	//Function to process this voice

	/***********************************************************//**
	* Construct a NOTE
	*
	* This class assumes that the creating code has alread parsed a string
	* into the Pitch, octave, sharp/flat/...
	***************************************************************/
	function __construct( $pitch, $octave = '', $sharpflat='', $length=1, $decorator="", $callback = null )
	{
		parent::__construct();
		$this->set( "pitch", $pitch );
		$this->set( "octave", $octave );
		$this->set( "sharpflat", $sharpflat );
		$this->set( "length", $length );
		$this->set( "decorator", $decorator );
		$this->callback = $callback;
	}
	function set( $var, $value, $enforce = false )
	{
		switch( $var )
		{
			case "pitch":
				$ok = $this->validate_pitch( $value );
				if( $ok )
					parent::set( $var, $value );
				break;
			case "octave":
				$ok = $this->validate_octave( $value );
				if( $ok )
					parent::set( $var, $value );
				break;
			case "sharpflat":
				$ok = $this->validate_sharpflat( $value );
				if( $ok )
					parent::set( $var, $value );
				break;
			case "length":
				$ok = $this->validate_length( $value );
				if( $ok )
					parent::set( $var, $value );
				break;
			case "decorator":
				$ok = $this->validate_decorator( $value );
				if( $ok )
					parent::set( $var, $value );
				break;
			default:
				//Either we don't allow anything else to be set
					//return false;
				// or we let inherited fields be set.
					parent::set( $var, $value, true );
		}
	}
	/**//**************************************************
	* Validate that the pitch is valid
	*
	* @param string
	* @returns bool
	***************************************************************/
	function validate_pitch( $value )
	{
		switch( $value )
		{
			case "A":
			case "B":
			case "C":
			case "D":
			case "E":
			case "F":
			case "G":
			case "a":
			case "b":
			case "c":
			case "d":
			case "e":
			case "f":
			case "g":
				return true;
			default:
				return false;
		}
	}
	/**//**************************************************
	* Validate that the octave is valid
	*
	* @param string
	* @returns bool
	***************************************************************/
	function validate_octave( $value )
	{
		switch( $value )
		{
			case ",":
			case "'":
				return true;
			//The following aren't strictly to spec
			case ",,":
			case "''":
				return true;
			default:
				return false;
		}
	}
	/**//**************************************************
	* Validate that the sharpflat is valid
	*
	* @param string
	* @returns bool
	***************************************************************/
	function validate_sharpflat( $value )
	{
		switch( $value )
		{
			case "=":
			case "^":
			case "_":
				return true;
			default:
				return false;
		}
	}
	/**//**************************************************
	* Validate that the length is valid
	*
	* @param string
	* @returns bool
	***************************************************************/
	function validate_length( $value )
	{
		switch( $value )
		{
			case "1":
			case "2":
			case "3":
			case "4":
			case "5":
			case "6":
			case "7":
			case "8":
			case "9":
			case "10":
			case "11":
			case "12":
			case "13":
			case "14":
			case "15":
			case "16":
				//Anything more than 16 and we need to re-evaluate our L:
				return true;
			//Abbrev
			case "/":
			//'broken' time (hold-cut)
			case ">":
			case "<":
				return true;
			//Non Standard
			case "//":
				return true;
			default:
				return false;
		}
	}
	/**//**************************************************
	* Validate that the decorator is valid
	*
	* @param string
	* @returns bool
	***************************************************************/
	function validate_decorator( $value )
	{
		switch( $value )
		{
			case ".":
			case "<":
			case "H":
			case "T":
			case "R":
			case "!trill!":
			case "!fermata!":
				return true;
			default:
				return false;
		}
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
		throw new Exception( "Notes can't be in the header!" );
		$out = "";
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
		$out = "";
		if( isset( $this->decorator ) )
		{
			$out .= $this->decorator;
		}  
		if( isset( $this->sharpflat ) )
		{
			$out .= $this->sharpflat;
		}  
		$out .= $this->pitch;
		if( isset( $this->octave ) )
		{
			$out .= $this->octave;
		}  
		if( isset( $this->length ) )
		{
			$out .= $this->length;
		}  
		return $out;
	}
}

