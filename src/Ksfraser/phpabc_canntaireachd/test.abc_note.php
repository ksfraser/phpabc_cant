<?php

require_once( 'class.abc_gracenote.php' );
require_once( 'abc_dict.php' );

$note = new abc_note( "C" );
var_dump( $note );
var_dump( $note->get_body_out() );
$note = new abc_note( "D"   , "'", '=', 2, "", null );
var_dump( $note->get_body_out() );
$gnote = new abc_gracenote( "D"   , "'", '=', 2, "", null );
var_dump( $gnote->get_body_out() );

