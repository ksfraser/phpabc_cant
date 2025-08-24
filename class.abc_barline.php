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
* This class creates the barline in a line. 
*
*************************************************************/
class abc_barline extends origin
{
	protected $barline;	// | is an ordinary barline
				//|| is a double barline
				//:| is "repeat last section".
				//|: is "repeat next section".
				//:: is "repeat last and next sections".
				//|1 or |[1 or | [1 is "first repeat ending".
				//:|2 or :|[2 or :| [2 is "second repeat ending".
				//|] and [| are variants of ||.
	protected $callback;	//Function to process this voice

	/***********************************************************//**
	* Construct a NOTE
	*
	* This class assumes that the creating code has alread parsed a string
	* into the Pitch, octave, sharp/flat/...
	***************************************************************/
	function __construct( $barline, $callback = null )
	{
		$this->set( "barline", $barline );
		$this->callback = $callback;
	}
	function set( $var, $value )
	{
		switch( $var )
		{
			case "barline":
				$ok = $this->validate_barline( $value );
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
	* Validate that the barline is valid
	*
	* @param string
	* @returns bool
	***************************************************************/
	function validate_barline( $value )
	{
		switch( $value )
		{
			case "|":
			case "||";
			case "[|";
			case "|]";
			case "|:";
			case ":|";
			case "[:";
			case ":]";
			case "::";
			case "|1";
			case "|2";
			case "[1";
			case "[2";
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
		$out .= $this->barline;
		return $out;
	}
}

