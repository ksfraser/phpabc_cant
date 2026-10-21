<?php

require_once( 'class.abc_file.php' );
require_once( 'class.ksf_file.php' );
require_once( 'class.abcparser.php' );
require_once( 'abc_dict.php' );

if( isset( $argv[2] ) )
	$path = $argv[2];
else
	$path = "/mnt2/fs/business/PipingLessons/abc2midi";
$filename = $argv[1];
try {
        $file = new ksf_file( $filename, $path );
	$file->set( "loglevel", PEAR_LOG_DEBUG );
        $file->open();
	$tfile = new abc_file();
	$tfile->set( "loglevel", PEAR_LOG_DEBUG );
	//$tfile->set( "filedata", $file->get_all_contents() );
	$tfile->processFile( $file->get_all_contents() );
	//var_dump( $tfile );
} catch( Exception $e )
{
}
var_dump( $tfile->getTune( 0 ) );
$str = $tfile->getTune( 0 );	
