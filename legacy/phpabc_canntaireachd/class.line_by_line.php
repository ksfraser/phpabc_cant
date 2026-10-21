<?php

/**//**************************************************************
 * I have built an array of items from the DOCs from bmw to try
 * and convert into ABC.  This should save a bunch of typesetting tim.
 *
 * In addition, I want to be able to add Cainnteraichd to each of the tunes
 * as well as the ABC notes below for students who don't read music lines well.
 *
 * Being able to convert cainnteraichd into ABC would be a bonus.
 *
 * Last possibility would be the conversion of ABC into bmw but that isn't 
 * a current goal for me as I don't intend to use BMW.  MuseScore OTOH...
 * 
 *
 * TODO
 *  During the search/replace, we end up in the situation where
 * a doubling become {gde} and then the next pass replaces
 * the gd with {d} so we end up with {{d}e}.
 *
 * Solution would be to do a string tokenization, and then replace
 * each token once instead of S/R the entire string a whole bunch of times.
 * Another way would be to change the dictionary so that "gd" becomes " gd "
 * for the match so that {gd doesn't trigger...
 * * ***************************************************************/

include_once( 'class.base_converter.php' );
class line_by_line extends base_converter
{
	protected $textin_array;
	protected $textout_array;
	protected $linecount;
	function __construct( $infile = null, $outfile = null )
	{
		parent::__construct( $infile, $outfile );
		$this->textin_array = array();
		$this->textout_array = array();
		$this->linecount = 0;
	}
	function load_line()
	{
		$line = fgets( $this->fp_in );
		if( $line )
		{
			//var_dump( $line );
			$this->textin_array[] = $line;
			$this->linecount++;
			return TRUE;
		}
		else
			return FALSE;
	}
	function get_line( $line )
	{
		if( $line < $this->linecount )
		{
			return $this->textin_array[ $line ];
		}
		else 
		{
			return FALSE;
		}
	}
	function load_file_by_line()
	{
		$res = true;
		$count = 0;
		while( $res )
		{
			$res = $this->load_line();
			$count++;
		}
		echo "Read $count lines\n\r";
	}
	function search_replace( $sort_dict )
	{
		$textout = "";
		foreach( $this->textin_array as $key => $val )
		{
			$this->textin = $val;
			parent::search_replace( $sort_dict );
			$this->textout_array[] = $this->textout;
			$textout .= $this->textout;
		}
		$this->textout = $textout;
		return $textout;
	}
}

