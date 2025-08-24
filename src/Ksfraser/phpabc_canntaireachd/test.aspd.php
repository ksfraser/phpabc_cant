<?php

require_once( 'class.abc_file.php' );
require_once( 'class.ksf_file.php' );
require_once( 'class.abcparser.php' );
require_once( 'class.aspd_tune.php' );
require_once( 'abc_dict.php' );

if( isset( $argv[2] ) )
	$path = $argv[2];
else
	$path = "/mnt2/fs/business/PipingLessons/abc2midi";
$filename = $argv[1];
try {
        $file = new ksf_file( $filename, $path );
	$file->set( "loglevel", PEAR_LOG_ERROR );
        $file->open();
	$tfile = new abc_file();
	$tfile->set( "loglevel", PEAR_LOG_ERROR );
	$tfile->processFile( $file->get_all_contents() );
} catch( Exception $e )
{
}
//var_dump( $tfile->getTune( 0 ) );
$str = $tfile->getTune( 0 );	
//var_dump( $str );

$parser = new abcparser();
$parser->set( "loglevel", PEAR_LOG_DEBUG );
$parser->set( "tune", $str );
$parser->process();

//Parser no longer outputs anything - moved to aspd_tune etc
//var_dump( $parser );

$aspd = new aspd_tune();
$body = $parser->get( 'body_arr' );
$aspd->set( "loglevel", PEAR_LOG_DEBUG );
$aspd->process_bars( $body[0][1] );
//var_dump( $aspd );
$aspd->output();
$aspd->print_tune();
var_dump( $aspd->log );

//$tfile->

