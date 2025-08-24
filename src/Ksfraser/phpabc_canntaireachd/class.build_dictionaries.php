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

require_once( 'class.tunesetting.php' );


/****************************
* Provide the BMW 2 ABC conversion array
*/
include_once( 'bmw_dict.php' );

class build_dictionaries
{
	protected $dict;
	protected $fp_in;
	protected $fp_out;
	protected $outfile;

	function __construct( $outfile )
	{
		$this->outfile = $outfile;
		$this->fp_out = fopen( $outfile, "w" );
	}
	function __destroy()
	{
		fflush( $this->fp_out );
		fclose( $this->fp_out );
	}
	/**//**
	* Take an array and build a sorted array from the keys by length desc
	*
	* @param int maxlength
	* @param array arr
	* @returns array sorted array longest first.
	*/
	function build_length_sorted_array(int $maxlength = 20, $arr = null)
	{
		if( null == $arr )
		{
			return null;
		}
		$target_arr = array();
		//$maxlength = 20;
		for( $i=$maxlength; $i>=0; $i-- )
		{
			$target_arr[$i] = array();
		}
		foreach( $arr as $key => $row )
		//foreach( $bmw as $key => $row )
		//foreach( $arr as $k => $row )
		{
			//Sort by length for later SEARCH/REPLACE best match(longest) first
			//echo "K\n\r";
			//var_dump( $k   );
			echo "Row\n\r";
			//var_dump( $row );
		// 	$keys = array_keys( $row );
			//echo "Keys\n\r";
			//var_dump( $keys );
			echo "Key\n\r";
			//var_dump( $key );
			$len = strlen( $key );
			if( $len > 0 )
			{
				$spot =  $len;
				//$spot = $maxlength - $len;
				//echo "len\n\r";
				//var_dump( $len );
				//echo "Spot\n\r";
				//var_dump( $spot );
				//sleep( 1 );
				//echo "target_arr\n\r";
				//var_dump( $target_arr[$spot] );
				//sleep( 3 );
				$target_arr[$spot][] = $key;
				//echo "target_arr after insert\n\r";
				//var_dump( $target_arr[$spot] );
				//var_dump( $target_arr );
				//sleep( 3 );
			}
			else
			{
				echo "Row didn't have a key\n\r";
			}
		}
		return $target_arr;
	}
	function build_sort_dictionary( $keys, $old_dictionary, $lang = "abc" )
	{
		$dict = array();
		foreach( $keys as $len => $keys_arr )
		{
			//echo "Keys_arr \n\r";
			//var_dump( $keys_arr );
			foreach( $keys_arr as $k )
			{
				//echo "K \n\r";
				//var_dump( $k );
				//k is the string we now want to find as an index
				// within old_dictionary
				//var_dump( $old_dictionary[ $k ] );
				$dict[ $k] = $old_dictionary[ $k ][ $lang ];
			//	var_dump( $dict );
			}
		}
		$this->dict = $dict;
		return $dict;
	}
	function write_file()
	{
		fwrite( $this->fp_out, json_encode( $this->dict ) );
	}
	/**//**
	* For ABC notation songs, I want to also show the ABC "word" underneath for begining students.
	*
	* convert ABC notation to be itself
	*
	*/
	function dict_transpose_to_self( $dict )
	{
		$out_arr = array();
		foreach( $dict as $k=>$v )
		{
			$out_arr[$v] = $v;
		}
		return $out_arr;
	}
}
