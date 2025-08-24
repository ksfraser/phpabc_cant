<?php

require_once( 'class.tunesetting.php' );
require_once( 'abc_dict.php' );

function get_cannt( $note )
{
	if( isset( $abc[$note]['cannt'] ) )
		return $abc[$note]['cannt'];
	else 
		return $note;
}

$tune = new aspd_tune();	//Sets a bunch of headers
$tune->add_key( "HP" );		//bagpipes
$tune->add_index( 1 );		//bagpipes
$tune->add_notelength( "1/8");	// 1/16				L
$tune->add_meter( "2/4" );		// 2/4				M
$tune->add_tempo( "90" );		// 90				Q
$tune->add_title( "Test Tune" );		// "My Tune"			T
$tune->add_melody( "{g}GA {GAG}AB",  1, 1 );
$tune->add_melody( "{Gdc}de {gfg}f2",  1, 2 );
$tune->add_melody( "{gef}ed {gBd}Bd",  1, 3 );
$tune->add_melody( "{gfg}fe {gfg}f2",  1, 4 );
/*
$tune->add_melody( "GA AB",  2, 1 );
$tune->add_melody( "de f2",  2, 2 );
$tune->add_melody( "ed BG",  2, 3 );
$tune->add_melody( "A2 A2",  2, 4 );
*/
$tune->add_harmony( "GA AB",  1, 1 );
$tune->add_harmony( "de f2",  1, 2 );
$tune->add_harmony( "ed Bd",  1, 3 );
$tune->add_harmony( "fe f2",  1, 4 );
/*
$tune->add_canntaireachd( "em en en o",  1, 1 );
$tune->add_canntaireachd( "a e ve",  1, 2 );
$tune->add_canntaireachd( "e a o a",  1, 3 );
$tune->add_canntaireachd( "ve e ve",  1, 4 );
$tune->add_canntaireachd( "em en en o",  2, 1 );
$tune->add_canntaireachd( "a e ve",  2, 2 );
$tune->add_canntaireachd( "e a o em",  2, 3 );
$tune->add_canntaireachd( "en en",  2, 4 );
*/
$tune->add_ABC( "GA AB",  1, 1 );
/*
$tune->add_ABC( "de f2",  1, 2 );
$tune->add_ABC( "ed Bd",  1, 3 );
$tune->add_ABC( "fe f2",  1, 4 );
$tune->add_ABC( "GA AB",  2, 1 );
$tune->add_ABC( "de f2",  2, 2 );
$tune->add_ABC( "ed BG",  2, 3 );
$tune->add_ABC( "A2 A2",  2, 4 );
*/
$tune->add_lyrics( "Skip the gaily on",  1, 1 );
$tune->add_words_bottom( "Made Famous by the Rankins",  1, 4 );
$tune->add_words( "2nd Words Line",  1, 4 );
/*
*/
$tune->output();
$tune->print_tune();
