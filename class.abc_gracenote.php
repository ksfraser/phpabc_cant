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

require_once( 'class.abc_note.php' );

/**//*********************************************************
* This class creates a gracenote (part of an embellishment)
*
*		C, D, E, F, G, A, B, C D E F G A B c d e f g a b c' d' e' f' g' a' b'
*
*	Technically a gracenote could be any valid note
*		From a piping perspective, it is a much smaller range of notes and no flat/sharps
*	Technically a gracenote could have a length modifier.  Piobaireachd does this.
*
*************************************************************/
class abc_gracenote extends abc_note
{
/***Inherited
	protected $pitch;	//a-gA-G
	protected $octave;	//, or '
	protected $sharpflat;	// =^_	null/natural/sharp/flat
	protected $length;	//!<string	(int)(/)(int)
	protected $decorator;	//!<string .MHTR!trill!	stacatto Legato Fermato Trill Roll
	protected $name;
	protected $cannt;
	protected $callback;	//Function to process this voice
***Inherited */

	/***********************************************************//**
	*
	***************************************************************/
	function __construct( $pitch, $octave = '', $sharpflat='', $length=1, $decorator="", $callback = null )
	{
		parent::__construct( $pitch, $octave, $sharpflat, $length, $decorator, $callback );
	}
	/**//**************************************************
	* Validate that the decorator is valid
	*
	*
	* I can't see decorators being valid for a gracenote
	*
	* @param string
	* @returns bool
	***************************************************************/
	function validate_decorator( $value )
	{
		switch( $value )
		{
			default:
				return false;
		}
	}
}

