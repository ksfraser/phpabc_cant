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
require_once( 'class.abc_gracenote.php' );

/**//*********************************************************
* This class creates an embellishment (multiple gracenotes)
*
*************************************************************/
class abc_embellishment extends origin
{
	protected $gracenote_arr;
	protected $name;
	protected $cannt;
	protected $callback;	//Function to process this voice

	/***********************************************************//**
	*
	***************************************************************/
	function __construct( $embellishment, $callback = null )
	{
		parent::__construct();
		$this->gracenote_arr = array();
		$len = strlen( $embellishment );
		if( strncmp( "{", $embellishment, 1 ) == 0 )
		{
			//at least the first char indicates an embellishment
			if( substr_compare( $embellishment, "}", -1, 1, false ) == 0 )
			{
				$this->parseEmbellishment( $embellishment );
			}
			else
			{
				throw new Exception( "String doesn't end with } so not a gracenote embellishment" );
			}
		}
		else
		{
		}
		$this->callback = $callback;
	}
	function parseEmbellishment( $embellishment )
	{
		//class to use for the test
		$gracetest = new abc_gracenote( "C" );
		$oct = $sf = $length = $dec = "";
		$len = strlen( $embellishment );
		for( $i = 1; $i < $len; $i++)
		{
			//test each char to see what it is
			//A gracenote string could be a decorator, sharp/flat, ptch, octave and length.
			// Piping wouldn't use the decorator, sharp/flat nor octave but could use the length.  
			//This particular class is generic though - piping will extend
			if( $gracetest->validate_decorator( $embellishment[$i] ) )
			{
				//character is a decorator
				$dec = $embellishment[$i];
				continue;
			}
			if( $gracetest->validate_sharpflat( $embellishment[$i] ) )
			{
				//character is a decorator
				$sf = $embellishment[$i];
				continue;
			}
			if( $gracetest->validate_octave( $embellishment[$i] ) )
			{
				//character is a decorator
				$oct = $embellishment[$i];
				continue;
			}
			if( $gracetest->validate_length( $embellishment[$i] ) )
			{
				//character is a decorator
				$length = $embellishment[$i];
				continue;
			}
			if( $gracetest->validate_pitch( $embellishment[$i] ) )
			{
				//character is a decorator
				$grace = new abc_gracenote( $embellishment[$i], $oct, $sf, $length, $dec );
				$oct = $sf = $length = $dec = "";
				$this->set( "gracenote_arr", $grace );
				unset( $grace );
				continue;
			}
		}
	}
	/**//******************************************
	* Format the class variables into the Voice line in the header
	*
	* @param none
	* @returns string
	***********************************************/
	function get_header_out()
	{
		throw new Exception( "Embellishments can't be in the header!" );
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
		if( count( $this->gracenote_arr ) > 0 )
		{
			foreach( $this->gracenote_arr as $grace )
			{
				$out .= $grace->get_body_out();
			}
			return "{" . $out . "}";
		}
		return $out;
	}
}

