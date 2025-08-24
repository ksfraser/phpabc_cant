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

/** DRAFT ** DRAFT ** DRAFT **
 ** DRAFT ** DRAFT ** DRAFT **
 ** DRAFT ** DRAFT ** DRAFT **
 ** DRAFT ** DRAFT ** DRAFT **
 ** DRAFT ** DRAFT ** DRAFT **/

require_once( 'class.origin.php' );
require_once( 'class.abc_note.php' );

/**//*********************************************************
* This class creates a beat.  This can be many notes and embellished notes
*
* Any notes side by side are considered on the same beam (i.e. within the same beat)
* However there isn't anything in the standard requiring the beats to be separated
*   I want to separate them in my music to make them easier to read
*
*************************************************************/
class abc_beat extends origin
{
	protected $note_arr;	//Notes / embellished notes
	protected $callback;	//Function to process this voice

	/***********************************************************//**
	*
	***************************************************************/
	function __construct( $Beat, $callback = null )
	{
		parent::__construct();
		$this->note_arr = array();

		$this->parseBeat( $Beat );
		$this->callback = $callback;
	}
	function parseBeat( $Beat )
	{
		//class to use for the test
		$gracetest = new abc_note( "C" );
		$oct = $sf = $length = $dec = "";
		$len = strlen( $Beat );
		for( $i = 1; $i < $len; $i++)
		{
			//test each char to see what it is
			//A note string could be a decorator, sharp/flat, ptch, octave and length.
			// Piping wouldn't use the decorator, sharp/flat nor octave but could use the length.  
			//This particular class is generic though - piping will extend
			if( $gracetest->validate_decorator( $Beat[$i] ) )
			{
				//character is a decorator
				$dec = $Beat[$i];
				continue;
			}
			if( $gracetest->validate_sharpflat( $Beat[$i] ) )
			{
				//character is a decorator
				$sf = $Beat[$i];
				continue;
			}
			if( $gracetest->validate_octave( $Beat[$i] ) )
			{
				//character is a decorator
				$oct = $Beat[$i];
				continue;
			}
			if( $gracetest->validate_length( $Beat[$i] ) )
			{
				//character is a decorator
				$length = $Beat[$i];
				continue;
			}
			if( $gracetest->validate_pitch( $Beat[$i] ) )
			{
				//character is a decorator
				$grace = new abc_note( $Beat[$i], $oct, $sf, $length, $dec );
				$oct = $sf = $length = $dec = "";
				$this->set( "note_arr", $grace );
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
		throw new Exception( "Beats can't be in the header!" );
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
		if( count( $this->note_arr ) > 0 )
		{
			foreach( $this->note_arr as $grace )
			{
				$out .= $grace->get_body_out();
			}
			return "{" . $out . "}";
		}
		return $out;
	}
}

