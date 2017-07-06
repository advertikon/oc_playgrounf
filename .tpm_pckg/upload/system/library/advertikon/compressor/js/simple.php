<?php
/**
  * Minify Class
  * @author Advertikon
  * @package Advertikon
  * @version 2.8.11
  */
  
  namespace Advertikon\Compressor\Js;

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
  		$t = '';
  		$str = false;
  		$one_line_comment = false;
  		$comment = false;
  		$new_line = false;
  		$reg_exp = false;
  		$reg_exp_set = false;
  		$ord = null;
  		$ord_next = null;
  		$ord_prev = null;
  		$before_newline = null;
  		$prev = null;
  		$debug_skip = false;

  		for( $i = 0, $len = strlen( $text ); $i < $len; $i++ ) {
  			$current = $text[ $i ];

  			// Comment end
  			if ( true === $one_line_comment ) {
  				if ( "\r" === $current || "\n" === $current ) {
  					$one_line_comment = false;
  					
  					if ( $debug ) {
  						array_pop( $colors );
  						$d .= end( $colors );
  					}
				}

  				continue;
  			}

  			$next = $text[ min( $i + 1, $len - 1 ) ];

  			// In-line comment end
  			if ( true === $comment ) {
  				if ( "*" === $current && '/' === $next ) {
  					$comment = false;
  					$i++;

  					if ( $debug ) {
  						array_pop( $colors );
  						$d .= '*/' . end( $colors );
  					}
  				}

  				continue;
  			}

  			// Whitespace
  			if ( $current <= "\x20" ) {
  				$nl = true;

  			// Not a whitespace
  			} else {
  				$nl = false;

  				// Previous char was a whitespace
  				if ( $new_line ) {
  					$new_line = false;

  					if (

  						// Previous char is alphanumeric
						(
							( $prev >= "\x41" && $prev <= "\x7A" ) ||
							( $prev >= "\x30" && $prev <= "\x39" )
						)
						&&

						// Current char is alphanumeric or $ or _
						(
							( $current >= "\x41" && $current <= "\x7A" ) ||
							( $current >= "\x30" && $current <= "\x39" ) ||
							$current === "\x24" ||
							$current === '_'
						)
					) {

						// Replace all the whitespaces in a row by only one
						$t .= ' ';

						if ( $debug ) {
							$d .= ' ';
						}
					}

					if ( $debug ) {
	  					if ( $n_count > 1 ) {
	  						$d .= $bg_red . $n_count . end( $colors );
	  					}

	  					$n_count = 0;
	  				}
  				}
  			}

  			// Not escaped character
  			if ( '\\' !== $prev ) {

	  			// Not in string
	  			if ( false === $str ) {

	  				// Start of string
	  				if ( $current === "'" || $current === '"' ) {
	  					$str = $current;
	  					
	  					if ( $debug ) {
	  						$d .= $green;
	  						$colors[] = $green;
	  					}

	  				// Slash
	  				} elseif ( '/' === $current ) {

  						// Line comment start
	  					if ( '/' === $next) {
	  						$one_line_comment = true;
	  						$i++;

	  						if ( $debug ) {
		  						$d .= $yellow . '//...';
		  						$colors[] = $yellow;
		  					}

	  						continue;
	  					}

	  					// Comment start
	  					if ( '*' === $next ) {
	  						$comment = true;
	  						$i++;

	  						if ( $debug ) {
		  						$d .= $yellow . '/*...';
		  						$colors[] = $yellow;
		  					}

	  						continue;
	  					}

	  					// Start of REGEXP
	  					if ( $next !== "\x0a" && $next !== "\x0d" ) {
		  					$r_exp = $current;
		  					$reg_exp = true;

		  					if ( $debug ) {
		  						$r_d = $blue . $current;
		  						$colors[] = $blue;
		  					}

		  					for( $r_i = $i + 1; $r_i < $len; $r_i++ ) {
		  						$r_current = $text[ $r_i ];

		  						if ( $r_current === "\x0a" || $r_current === "\x0d" ) {
		  							break;
		  						}

		  						$r_next = $text[ min( $r_i + 1, $len ) ];

		  						if ( '\\' === $r_current ) {
		  							$r_exp .= $r_current . $r_next;
		  							$r_i++;

		  							if ( $debug ) {
				  						$r_d .= $r_current . $r_next;
				  					}

		  							continue;
		  						}

		  						if ( $reg_exp_set ) {
		  							if ( ']' === $r_current ) {
		  								$reg_exp_set = false;

		  								if ( $debug ) {
		  									$r_d .= $r_current;
											array_pop( $colors );
											$r_d .= end( $colors );
		  									$debug_skip = true;
										}
		  							}

		  							$r_exp .= $r_current;

		  							if ( $debug && ! $debug_skip ) {
		  								$r_d .= $r_current;
		  							}

		  							$debug_skip = false;

		  							continue;
		  						} 

		  						if ( '[' === $r_current ) {
		  							$reg_exp_set = true;
		  							$r_exp .= $r_current;

									if ( $debug ) {
										$r_d .= $cyan . $r_current;
										$colors[] = $cyan;
									}

									continue;
		  						}

		  						if ( '/' === $r_current ) {
		  							$reg_exp = false;
		  							$r_exp .= $r_current;

		  							if ( $debug ) {
		  								$r_d .= $r_current;
		  							}

		  							if ( in_array( $r_next, array( 'i', 'm', 'g', 'y', ) ) ) {
		  								$r_exp .= $r_next;
		  								$r_i++;

		  								if ( $debug ) {
		  									$r_d .= $r_next;
		  								}
		  							}

		  							break;
		  						}

		  						$r_exp .= $r_current;

		  						if ( $debug ) {
		  							$r_d .= $r_current;
		  						}
		  					}

		  					if ( $debug ) {
  								array_pop( $colors );
  								$r_d .= end( $colors );
  							}

		  					// REGEXP exit
		  					if ( ! $reg_exp ) {
		  						$t .= $r_exp;

		  						if ( $debug ) {
		  							$d .= $r_d;
		  						}

		  						$prev = $r_exp[ $r_i - $i - 1 ];
		  						$i = $r_i;

		  						continue;
		  					}
	  					}

		  			// White space
		  			} elseif ( $nl ) {
		  				//if ( true !== $new_line ) {
		  					$new_line = true;
		  				//}

						if ( $debug ) {
							$n_count++;
						}

		  				continue;
		  			}

	  			// In string
	  			} else {
	  				if ( $str === $current ) {
	  					$str = false;

	  					if ( $debug ) {
	  						array_pop( $colors );
	  						$d .= $current . end( $colors );
	  						$debug_skip = true;
	  					}
	  				}
	  			}
  			}

  			$t .= $current;
  			$prev = $current;

  			if ( $debug && ! $debug_skip ) {
  				$d .= $current;
  			}

  			$debug_skip = false;
  		}

  		$text = $t;

  		if ( $debug && function_exists( 'l' ) ) {
  			l( "\n\n" . $d );
  		}

		if ( ! $text ) {
			throw new Exception( 'Empty output' );
		}

		return $text;
  	}
 }
	}

					if ( $debug ) {
	  					if ( $n_count > 1 ) {
	  						$d .= $bg_red . $n_count . end( $colors );
	  					}

	  					$n_count = 0;
	  				}
  				}
  			}

  			// Not escaped character
  			if ( '\\' !== $prev ) {

	  			// Not in string
	  			if ( false === $str ) {

	  				// Start of string
	  				if ( $current === "'" || $current === '"' ) {
	  					$str = $current;
	  					
	  					if ( $debug ) {
	  						$d .= $green;
	  						$colors[] = $green;
	  					}

	  				// Slash
	  				} elseif ( '/' === $current ) {

  						// Line comment start
	  					if ( '/' === $next) {
	  						$one_line_comment = true;
	  						$i++;

	  						if ( $debug ) {
		  						$d .= $yellow . '//...';
		  						$colors[] = $yellow;
		  					}

	  						continue;
	  					}

	  					// Comment start
	  					if ( '*' === $next ) {
	  						$comment = true;
	  						$i++;

	  						if ( $debug ) {
		  						$d .= $yellow . '/*...';
		  						$colors[] = $yellow;
		  					}

	  						continue;
	  					}

	  					// Start of REGEXP
	  					if ( $next !== "\x0a" && $next !== "\x0d" ) {
		  					$r_exp = $current;
		  					$reg_exp = true;

		  					if ( $debug ) {
		  						$r_d = $blue . $current;
		  						$colors[] = $blue;
		  					}

		  					for( $r_i = $i + 1; $r_i < $len; $r_i++ ) {
		  						$r_current = $text[ $r_i ];

		  						if ( $r_current === "\x0a" || $r_current === "\x0d" ) {
		  							break;
		  						}

		  						$r_next = $text[ min( $r_i + 1, $len ) ];

		  						if ( '\\' === $r_current ) {
		  							$r_exp .= $r_current . $r_next;
		  							$r_i++;

		  							if ( $debug ) {
				  						$r_d .= $r_current . $r_next;
				  					}

		  							continue;
		  						}

		  						if ( $reg_exp_set ) {
		  							if ( ']' === $r_current ) {
		  								$reg_exp_set = false;

		  								if ( $debug ) {
		  									$