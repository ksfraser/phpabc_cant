<?php

require_once( 'class.abc_embellishment.php' );
require_once( 'abc_dict.php' );

$embellishment = new abc_embellishment( "C" );
var_dump( $embellishment );
var_dump( $embellishment->get_body_out() );
$embellishment = new abc_embellishment( "{g}",  null );
var_dump( $embellishment->get_body_out() );
$gembellishment = new abc_embellishment( "{Gdc}", null );
var_dump( $gembellishment->get_body_out() );
$gembellishment = new abc_embellishment( "{TG2.=dRc}", null );
var_dump( $gembellishment->get_body_out() );

