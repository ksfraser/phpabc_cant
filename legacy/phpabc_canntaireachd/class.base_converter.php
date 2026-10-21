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


class base_converter
{
	protected $infile;
	protected $outfile;
	protected $fp_in;
	protected $fp_out;
	protected $textin;
	protected $textout;
	protected $infilesize;

	function __construct( $infile = null, $outfile = null )
	{
		$this->infile = $infile;
		$this->outfile = $outfile;
		if( null != $infile )
		{
			$this->fp_in = fopen( $infile, "r" );
			$this->infilesize = filesize( $infile );
		}
		if( null !== $outfile)
		{
			$this->fp_out = fopen( $outfile, "w" );
		}
	}
	function __destroy()
	{
		fclose( $this->fp_in );
		fflush( $this->fp_out );
		fclose( $this->fp_out );
	}
        function set( $field, $value )
        {
                $this->$field = $value;
        }
        function get( $field )
        {
                return $this->$field;
        }

	function search_replace( $sort_dict )
	{
		/**
		* Maybe easier to do a str_replace( array_keys( $sort_dict ), array_values( $sort_dict ), $textin )  
		*BUT we then can't do logging
		*/
		foreach( $sort_dict as $key => $val )
		{
			//echo $textin;
			$this->textout = str_replace( $key, $val, $this->textin );
			//echo $textout;
			$this->textin = $this->textout;
		}
		return $this->textout;
	}
	function load_file()
	{
		$this->textin = fread( $this->fp_in, $this->infilesize );
	}
	function write_file()
	{
		fwrite( $this->fp_out, $this->textout );
	}
}

