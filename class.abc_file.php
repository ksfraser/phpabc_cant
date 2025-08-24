<?php

/**//***********************
* https://github.com/jwdj/EasyABC/blob/master/tune_elements.py has items we may want eventually (but in python)
***************************/

/***********************************
*	ABC File Structure
*		Tune
*		blankline
*		Tune(s)(optional)
*
*	Tune Structure
*		X: index
*		... header lines
*		K:
*		...body lines
*
*	Body Lines
*		[Instruction]
*		Bar indicators [|:
*		Notes and Embellishments, decorators, etc
*		Bar indicator | [1 |1 ...
*		EOL indicator
*
*
*************************************/


require_once( 'class.origin.php' );
require_once( 'defines.inc.php' );

/**//**********************************************************************
* Handle an ABC file which will have 1 or more tunes.
*
* Tunes are separated by a blank line.  
* Tunes start with an X: line
*
**************************************************************************/
class abc_file extends origin
{
	protected $filedata;
	protected $lines;		//!< array
	protected $tune_start_lines;	//!< array which line numbers are tune starts
	protected $tune_end_lines;	//!< array which line numbers are tune ends (first blank line)
	protected $tunes;	//!< array tunes as strings
	protected $tunes_arr;	//!< array tunes as arrays (lines)
	protected $fileheaderlines_arr;
	function __construct( $filedata = null )
	{
		parent::__construct();
		$this->lines = array();
		$this->tune_start_lines = array();
		$this->tune_end_lines = array();
		$this->tunes = array();
		$this->fileheaderlines_arr = array();
		if( null !== $filedata )
		{
			$this->set( "filedata", $filedata );
		}
	}
	function isArray_tune_start_lines()
	{
		$this->var_dump( "***********************************" );
		$this->var_dump(  "Is tune_start_lines an array? :: " . is_array( $this->tune_start_lines ) );
		$this->var_dump( "***********************************" );
	}
	/**//************************************************
	* Convert the file's data into an array of lines
	*
	****************************************************/
	function data2lines()
	{
		if( ! isset( $this->filedata ) )
		{
			throw new Exception( "Data not set", KSF_FIELD_NOT_SET );
		}
		$this->lines = explode("\n", $this->filedata);
	}
	/**//************************************************
	* Take file data apart.  Determine if multiple tunes
	*
	****************************************************/
	function processFile( $filedata )
	{
		if( ! isset( $this->filedata ) )
		{ 
			$this->set( "filedata", $filedata );
		}
		$this->data2lines();
		$this->findTunes();
		//Above the first tune could be a file header i.e. formatting, PS definitions, etc
		$this->extractFileHeader();
		$this->separateTunes();
		//$this->var_dump( $this->tunes );
	}
	function extractFileHeader()
	{
		if( count( $this->tune_start_lines ) > 0 )
		{
			if( $this->tune_start_lines[0] > 0 )
			{
				//There is file header lines
				for( $i=0; $i < $this->tune_start_lines[0]; $i++ )
				{
					$this->set( "fileheaderlines_arr",  $this->lines[$i] );
				}
			}
			else
			{
				return null;
			}
		}
			
	}
	/**//************************************************
	* Look for the X of tune starts, and a blank line at the end.
	*
	* This function DOES NOT do error checking for blank lines in the middle of a tune.
	* This function DOES NOT check that there is a blank line immediately in front of a tune start.
	*
	****************************************************/
	function findTunes()
	{
		if( ! isset( $this->lines ) )
		{
			throw new Exception( "Data not set", KSF_FIELD_NOT_SET );
		}
		$currentline = 0;
		$inTune = false;
		$lastend = -1;
		foreach( $this->lines as $line )
		{
			$this->var_dump( "Current Line $currentline: " . $line );
			if( $inTune )
			{
				//looking for blank line indicating end of tune
				$l = trim( $line );
				if( 0 == strlen( $l ) )
				{
					$this->var_dump( "Blank Line - Tune End" );
					//blank line
					$this->set( "tune_end_lines", $currentline );
					$inTune = false;
					$lastend = $currentline;
				}
			}
			else
			{
				//looking for Tune start
				if( 0 == strncmp( $line, "X:", 2 ) )
				{
					//Start line 
					$this->var_dump( "X: header - Tune Start" );
					$this->isArray_tune_start_lines();
					$inTune = true;
					$this->set( "tune_start_lines", $currentline );
				}
			}
			$currentline++;
		}
		//in case we hit the end of file and it's not blank.
		$this->set( "tune_end_lines", $currentline );
	}
	/**//************************************************
	* convert the file into an array of tunes
	*
	* Each tune will be 1 array element.
	*
	****************************************************/
	function separateTunes()
	{
		if( ! isset( $this->tune_start_lines ) )
		{
			throw new Exception( "Data not set", KSF_FIELD_NOT_SET );
		}
		if( count( $this->tune_start_lines ) > 0 )
		{
			$currentindex = 0;
			foreach( $this->tune_start_lines as $index )
			{
				$start = $index;
				$end = $this->tune_end_lines[$currentindex];
				$tunearr = array();
				for( $i=$start; $i<$end; $i++ )
				{
					$tunearr[] = $this->lines[$i];
				}
				$currentindex++;
				$t = implode( "\r\n", $tunearr );
				$this->set( "tunes_arr", $tunearr );
				$this->set( "tunes", $t );
				unset( $tunearr );
			}
		}
	}
	/**//************************************************
	* Return the desired tune
	*
	* @param int tunenumber
	* @return bool|string tune data
	*****************************************************/
	function getTune( $tunenum )
	{
		if( isset( $this->tunes[$tunenum] ) )
			return $this->tunes[$tunenum];
		else
			return false;
	}
	/**//************************************************
	* Return the desired tune but as lines (array)
	*
	* @param int tunenumber
	* @return bool|string tune data
	*****************************************************/
	function getTuneLines( $tunenum )
	{
		if( isset( $this->tunes_arr[$tunenum] ) )
			return $this->tunes_arr[$tunenum];
		else
			return false;
	}
	function getFileHeaderLines()
	{
		$fileheader = implode( "\r\n", $this->fileheaderlines_arr );
		return $fileheader;
	}
}
		
		
