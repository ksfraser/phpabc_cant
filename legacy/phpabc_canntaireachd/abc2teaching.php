<?php

/**//**************************************************************
 * This program will convert an ABC setting of pipe tunes into 
 * a standardized format/layout of ABC that I will use for teaching.
 *
 * I'm going to standardize on the following:
         * [V:M] a [|: ABcd | abcd | abcd |abcd | $
         * w:    a [|: ABcd | abcd | abcd |abcd | $                          % ABC
         * w:    a [|: en em o o | en em o o | en em o o | en em o o | $     % Cainntearachd
         * [V:H] a [|: ABcd | abcd | abcd |abcd | $
         * [V:C] a [|: ABcd | abcd | abcd |abcd | $
         * [V:S] a [|: ABcd | abcd | abcd |abcd | $
         * [V:B] a [|: ABcd | abcd | abcd |abcd | $
         * [V:T] a [|: ABcd | abcd | abcd |abcd | $
         * w:    a [|: lyr-ics lyr-ics | lyr-ics lyr-ics | lyr-ics lyr-ics | lyr-ics lyr-ics | $
         * W:   Non Lyric Text on bottom
         * W:   ----
 *
 * This program will need to be able to read the existing ABC file,
 * parse the headers and retain them, parse the body, and convert/reformat it.
 * 
 * TODO
 * * ***************************************************************/

require_once( 'class.tunesetting.php' );

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
require_once( 'class.aspd_tune.php' );

/****
 * Here are the basics to create and write out a typeset tune
 *
 * require_once( 'class.tunesetting.php' );
 * 
 * $tune = new aspd_tune();        //Sets a bunch of headers
 * $tune->add_key( "HP" );         //bagpipes
 * $tune->add_index( 1 );          //bagpipes
 * $tune->add_notelength( "1/8");  // 1/16                         L
 * $tune->add_meter( "2/4" );              // 2/4                          M
 * $tune->add_tempo( "90" );               // 90                           Q
 * $tune->add_title( "Test Tune" );                // "My Tune"                    T
 * $tune->add_melody( "GA AB", 1, 1, 1 );
 * $tune->add_melody( "de f2", 1, 1, 2 );
 * $tune->add_melody( "ed Bd", 1, 1, 3 );
 * $tune->add_melody( "fe f2", 1, 1, 4 );
 * $tune->add_harmony( "GA AB", 1, 1, 1 );
 * $tune->add_harmony( "de f2", 1, 1, 2 );
 * $tune->add_harmony( "ed Bd", 1, 1, 3 );
 * $tune->add_harmony( "fe f2", 1, 1, 4 );
 * $tune->output();
 * $tune->print_tune();
 * 
*/

/**//********************SETUP*********************
*/
$infile = "abc_in.abc";
$outfile = "abc_out.abc";
$lbl = new line_by_line( $infile, $outfile );
// Create tune and ensure Bagpipes voice is present if Bagpipes key/index is set
$tune = new aspd_tune();
// If Bagpipes key or index is set, add Bagpipes voice to header
if ((isset($tune->key) && $tune->key === 'HP') || (isset($tune->index) && $tune->index == 1)) {
	use Ksfraser\PhpabcCanntaireachd\BagpipeVoiceFactory;
	$bagpipes_voice = BagpipeVoiceFactory::createVoice('Bagpipes');
	$tune->add_voice_obj($bagpipes_voice);
}
//$intune = new abc_tunesetting();
$intune = new aspd_tune();
$sourceline = 0;
$inbody = FALSE;
/**//********************RUN***********************
 * Read in File
 * Parse file for lines to keep/convert
 * Convert various formats
 * Add to tunesetting
 * Write tunesetting
 */
$tune->set_current_voice( $tune->search_voices_by_name( "Melody" ) );
$lbl->load_file_by_line();
try {
					//0 based
	while( FALSE !== ( $line = $lbl->get_line( $sourceline ) ) )
	{
		$sourceline++;
		//Identify the type of line
		$leadA = explode( ":", $line );
		var_dump( $line );
		//$leadB = explode( "%", $line );
		if( strlen( $leadA[0] ) == 1 )
		{
			//Voice or Header
			if( strncmp( $leadA[0], "V", 1 ) == 0)
			{
				//It's a voice line
				// string will be after the colon...i.e. 1 or 2 or M or BB ...
				//K: is supposed to be the last header, so any V: before K would be voice definitions for the header
					if( ! isset( $leadA[1] ) )
					{
						var_dump( $line );
						var_dump( $leadA );
					}
				$vc = explode( " ", $leadA[1] );
				if( ! $inbody )
				{
					//Do we know about this voice already (aspd constructor)?  If not
					//create a new voice and add to the tuneset.
					$res = $tune->search_voice_by_ind( $leadA[0] );	//Object or FALSE
					if( ! is_object( $res ) OR $res === FALSE )
					{
						//add new
						if( is_numeric( $leadA[0] ) )
						{
							//some of my earlier tunes used numberic indexes
							switch( $leadA[0] )
							{
								case '1':
								case '2':
								case '3':
								case '4':
								case '5':
								case '6':
								default:
							}
						}
						else
						{
	                    $v0 = new \Ksfraser\PhpabcCanntaireachd\AbcVoice($leadA[0], "TBD", "TBD", 'down', 'up', 0, 0, null);
	                    $tune->add_voice_obj( $v0 );

						}
					}
					else
					{
						//We already know this voice. (have defined in aspd...
					}
				}
				else
				{
					//changes which Voice is current
				}
			}
			else	//strncp V
			{
			//header field
				if( 0 == strncmp( $leadA[0], "K", 1 ) )
				{
					//Theoretically the last line of the header.  All VOICES should be defined before here....
					$inbody = TRUE;
				}
					//explode doesn't return the separator as part of the string
				//var_dump( $leadA[0] );
				if( ctype_alpha( $leadA[0] ) )
				{
					$k = $tune->get_header_key( $leadA[0] . ":" );
					if( $k !== FALSE )
					{
						$call = "add_" . $k;
						//Trim the headers so that \n\r don't tag along breaking the formatting of the output
						$tune->$call( trim( $leadA[1] ) );
					}
					else
					{
						echo "No header array for this entry: $leadA[0]\n\r";
					}
				}
				else
				{
					//also seeing \n
				}
			}
		}
/*
		else if( strlen( $leadB[0] ) == 1 )
		{
			//header field
			$k = $tune->get_header_key( $leadB[0] . ":" );
			$tune->add_$k( $leadB[1] );
		}
*/
		else
		{
			//Not a legacy Header (could be an %%
			//
			//ABC can have multiple body lines
			//w:
			//V:1
			// abc
			// abc
			//[V:1] abc
			//[K/L/M/...]
			//W:
			//% comment
			//%%INSTRUCTION
			//
			//Process depending on what type of line it is.
			if( $leadA[0] == "[" )
			{
				//start of [V:M] abcd line OR other header type of data i.e. [L:]
				$leadV = explode( "]", $leadA[1] );
				// V:M
				if( $leadV[0] == "V" )
				{
					//determine the Voice
					$res = $tune->search_voice_by_ind( $leadV[0] );	//Object or FALSE
					if( is_object( $res ) )
					{
						//Use callback to determine which internal voice for the typesetting
						$call = $res->get( 'callback' );
						$bars = explode( "|", $leadV[1] );
						$line = $tune->get( 'current_linenum' );
						for( $bar=0; $bar < count( $bars ); $bar++ )
						{
							$tune->$call( trim($bars[ $bar ]), $line, $bar );
						}
					}
				}
				else
				{
					//A format change i.e. K/L/M/Q/T/...
				}
			}
			else 
			{
				switch( $leadA[0] )
				{
					case "%":
						//comment so add to MELODY line
						$voice = $tune->search_voices_by_name( "Melody" );
						break;
					case " ":
						//abcd  so add music to current voice
						$voice = $tune->get( 'current_voice' );
					default:
						$voice = $tune->search_voices_by_name( "Melody" );
						break;
				}
				$call = $voice->get(  'callback' );
				//var_dump( $leadA );
				foreach( $leadA as $bar )
				{
					//Except these aren't really bars, depending on what was in the line.  [K: xxx][M:2/4][L:1/16] [|ABC | $ 
					//I will eventually want to do token by token
					$intune->$call( $bar );
				}
			}
		}
	}
}
catch( Exception $e )
{
}
//var_dump( $tune );

//var_dump( $intune );
//Now we need to process the intune into tune.
//Melody/Harmony/C-Harminy/Snare/Bass/Tenor shouldn't get transformed.
//WORDS should be appended to
//Lyrics shouldn't be altered
//ABC and CANNT are what we need to modify
//Simplified could also be run at this time - using the SCORE option we can
//then choose what to print..on demand...

$intune->xfer_headers( $tune );
$conv = new base_converter( null, null );
require_once( 'abc_dict.php' );	//provides $abc['grace']['cannt'] = 'val';
$melody_arr = $intune->get( 'body_array[0]' );
var_dump( $melody_arr );
$conv->set( 'textin', $melody_arr );

$tune->output();
$tune->print_tune();

//$words_file = "abc_words_dict.php";
//$cannt_file = "abc_cant_dict.php";
//$conv = new base_converter( $infile, $outfile );
//$conv = new line_by_line( $infile, $outfile );
//$conv->load_file_by_line();
//$conv->search_replace( $sort_dict );
//$conv->write_file();



