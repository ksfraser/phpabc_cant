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
// use Ksfraser\PhpabcCanntaireachd\AbcVoice;
$tune = new aspd_tune();
$tune->add_key( "HP" );         //bagpipes
$tune->add_index( 1 );          //bagpipes
$bagpipes_voice = null;
$bagpipes_voice = \Ksfraser\PhpabcCanntaireachd\BagpipeVoiceFactory::createVoice('Bagpipes');
$tune->add_voice_obj($bagpipes_voice);


/****************************
* Provide the BMW 2 ABC conversion array
*/
include_once( 'bmw_dict.php' );
include_once( 'class.build_dictionaries.php' );
include_once( 'class.dict2php.php' );
include_once( 'class.simplify_abc.php' );

include_once( 'class.base_converter.php' );
include_once( 'class.line_by_line.php' );
include_once( 'class.file_by_token.php' );
include_once( 'class.bmw_file_by_token.php' );


/*
*/

$dict_file = "bmw_sort_dict.json";
$d = new build_dictionaries( $dict_file );

$newarr = $d->build_length_sorted_array( 20, $bmw );
/**
* returned array is now built longest key to shortest
* Now we can build the array for doing search and replace
*/
$sort_dict = $d->build_sort_dictionary( $newarr, $bmw );
$d->write_file();
$abc2abc_arr = $d->dict_transpose_to_self( $sort_dict );
$words_file = "abc_words_dict.php";
$cannt_file = "abc_cant_dict.php";
$infile = "bmw_in.bmw";
$outfile = "abc_out.abc";
$conv = new base_converter( $infile, $outfile );
$conv->load_file();
$conv->search_replace( $sort_dict );
$conv->write_file();

$infile = "bmw_in.bmw";
$outfile = "abc_out2.abc";
$conv = new line_by_line( $infile, $outfile );
$conv->load_file_by_line();
$conv->search_replace( $sort_dict );
$conv->write_file();

$infile = "bmw_in.bmw";
$outfile = "abc_out3.abc";
$conv = new bmw_file_by_token( $infile, $outfile );
$conv->load_file();
global $bmw_header;
$conv->bmw_search_replace( $sort_dict, $bmw_header );
$conv->write_file();

$outfile = "abc_out4.abc";
$abcw = new bmw_file_by_token( $infile, $outfile );

