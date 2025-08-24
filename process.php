<?php

require_once( 'class.tunesetting.php' );
require_once( 'abc_dict.php' );
require_once( 'class.abcparser.php' );
require_once( 'class.ksf_file.php' );

function get_cannt( $note )
{
	if( isset( $abc[$note]['cannt'] ) )
		return $abc[$note]['cannt'];
	else 
		return $note;
}

$path = "/mnt2/fs/business/PipingLessons/abc2midi";
$filename = $argv[1];
try {
	$file = new ksf_file( $filename, $path );
	$file->open();
	$parser = new abcparser();
} catch( Exception $e )
{
}
try {
	$contents = $file->get_all_contents();  
	//echo __FILE__ . "::" . __LINE__ . "\n\r";
	$parser->set( "tune", $contents );
	//echo __FILE__ . "::" . __LINE__ . "\n\r";
} catch( Exception $e )
{
	//var_dump( __FILE__ . "::" . __LINE__ . "\n\r" );
	echo "Exception from " . $e->getFile() . "::" . $e->getLine() . "::" . $e->getCode() . "::" . $e->getMessage() . " \n\r";
}
try {
	//var_dump( __FILE__ . "::" . __LINE__ . "\n\r" );
	$parser->process();
	var_dump( $parser );
	//var_dump( __FILE__ . "::" . __LINE__ . "\n\r" );
	$parser->output();
	//var_dump( __FILE__ . "::" . __LINE__ . "\n\r" );
	$parser->print_tune();
}
catch( Exception $e )
{
	echo "Exception from " . $e->getFile() . "::" . $e->getLine() . "::" . $e->getCode() . "::" . $e->getMessage() . " \n\r";
}
/*

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
$tune->add_harmony( "GA AB",  1, 1 );
$tune->add_harmony( "de f2",  1, 2 );
$tune->add_harmony( "ed Bd",  1, 3 );
$tune->add_harmony( "fe f2",  1, 4 );
$tune->add_ABC( "GA AB",  1, 1 );
$tune->add_lyrics( "Skip the gaily on",  1, 1 );
$tune->add_words_bottom( "Made Famous by the Rankins",  1, 4 );
$tune->add_words( "2nd Words Line",  1, 4 );
$tune->output();
$tune->print_tune();
*/
