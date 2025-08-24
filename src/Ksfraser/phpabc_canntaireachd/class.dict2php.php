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

class dict2php
{
	protected $fp_out;
	protected $dict_arr;
	function __construct( $outfilename, $dict_arr )
	{
		$this->fp_out = fopen( $outfilename, "w" );
		$this->dict_arr = $dict_arr;
	}
	function header()
	{
		fwrite( $this->fp_out, "<?php" );
		fwrite( $this->fp_out, "/******************************************" );
		fwrite( $this->fp_out, "* Dictionary written by a script" );
		fwrite( $this->fp_out, "******************************************/" );
	}
	function write_arr()
	{
		foreach( $this->dict_arr as $k=>$v )
		{
			fwrite( $this->fp_out, "\$abc['$k'] = '$v'" );
			fflush( $this->fp_out );
		}
	}
	function write_file()
	{
		$this->header();
		$this->write_arr();
		fflush( $this->fp_out );
		fclose( $this->fp_out );
	}
}
