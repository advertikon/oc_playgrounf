<?php
/**
  * Minify Class
  * @author Advertikon
  * @package Advertikon
  * @version 0.0.7
  */
  
  namespace Advertikon\Compressor\Css;

  use Advertikon\Exception;

  class Simple {

  	public function run( $text ) {

  		$debug = true;
  		$d = "\e[0m";
  		$bg_green = "\e[42m";
  		$off = "\e[0m";
  		$green="\e[0;32m";
  		$yellow="\e[0;33m";
  		$bg_red="\e[41m";
  		$blue="\e[0;34m";
  		$cyan="\e[0;36m";
  		$n_count = 0;
  		$colors = array( "\e[0m" );

  		$input = $text;
  		
  		$text = preg_replace( '&/\*.*?\*/&', '', $text );
  		$text = preg_replace( '/(?<= [}{;:,(] ) \s+ /xm', '', $text );

  		if ( function_exists( 'l' ) ) {
  			l( preg_replace( '/(\s+)/m', $bg_green . '$1' . $off, $text ) );
  		}

		return $text;
  	}
 }
