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

include_once( 'class.dict2php.php' );

class simplify_abc extends dict2php
{
	function write_arr()
	{
		foreach( $this->dict_arr as $k=>$v )
		{
			if( strncmp( $v, "{", 1 ) )
			{
				if( strlen( $v ) > 6 )
				{
					//bigger than a taorluath
				}
				else
				{
					if( strlen( $v ) == 5 )
					{
						//doubling
						$v = "{g}";
					}
				}
			}
			fwrite( $this->fp_out, "\$abc['$k'] = '$v'" );
			fflush( $this->fp_out );
		}
	}
}
	
