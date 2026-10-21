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

include_once( 'class.file_by_token.php' );

class bmw_file_by_token extends file_by_token
{
	function bmw_search_replace( $sort_dict, $header_dict )
	{
		//Separate the input into beats.
		$this->textin = str_replace( array_keys( $header_dict ), array_values( $header_dict ), $this->textin );
		//Make text (Title, composer) lose the formatting off of the back
		//	"Journey to Skye",(T,L,0,0,arial,16,700,0,0,18,0,0,0)
		$this->textin = preg_replace( '/"(.*?)",(\(.*?)/is', 'T:${1} % ', $this->textin );
		//$this->textin = preg_replace( '/"(.*?)",(\(.*?)/s', 'T:${1}     % -${2}--${3}', $this->textin );
		//var_dump( $this->textin_beats_array );
		//var_dump( $this->textin );
		parent::search_replace( $sort_dict );
		//We now need to fix some formatting issues with the output.
		$form = array();
		$form['} '] = "}";
		$form['|t'] = "|";
		$this->textout = str_replace( array_keys( $form ), array_values( $form ), $this->textout );

		return $this->textout;
	}
}
