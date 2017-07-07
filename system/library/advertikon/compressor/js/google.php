<?php
/**
  * Minify Class
  * @author Advertikon
  * @package Advertikon
  * @version 0.0.7
  */
  
  namespace Advertikon\Compressor\Js;

  use Advertikon\Exception;

  class Google {

  	protected $url = 'http://closure-compiler.appspot.com/compile';
  	protected $timeout = 10;
  	protected $compilation_level = 'SIMPLE_OPTIMIZATIONS';

  	/**
  	 * Runs JS compilation
  	 * @param string $text Input script
  	 * @return text Output text
  	 * @throws Advertikon\Exception on error
  	 */
  	public function run( $text ) {
  		$data = array(
  			'js_code'           => $text,
    		'compilation_level' => $this->compilation_level,
    		'output_format'     => 'text',
    		'output_info'       => 'compiled_code',
  		);

		$opts = array(
			CURLOPT_URL            => $this->url,
			CURLOPT_HEADER         => false,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST           => true,
			CURLOPT_TIMEOUT        => $this->timeout,
			CURLOPT_POSTFIELDS     => http_build_query( $data ),
		);

  		$ch = curl_init();
		curl_setopt_array( $ch, $opts );
		$ret = curl_exec( $ch );
		$error = curl_error( $ch );
		curl_close( $ch );

		if ( $error ) {
			throw new Exception( $error );
		}

		if ( ! $ret ) {
			throw new Exception( 'Empty result' );
		}

		if ( 'error' === strtolower( substr( $ret, 0, 5 ) ) ) {
			throw new Exception( $ret );
		}

		return $ret;
  	}
}
