<?php
/**
  * Minify Class
  * @author Advertikon
  * @package Advertikon
  * @version 0.0.7
  */
 
namespace Advertikon;

class Minify {

	protected $force = false;
	protected $use_browser_cache = true;
	protected $as_is = false;

	protected $cache_dir = null;
	protected $fs = null;
	protected $base_dir = null;
	protected $hash = null;
	protected $source = null;
	protected $js_compressors = array(
		'Advertikon\\Compressor\\Js\\Google',
		'Advertikon\\Compressor\\Js\\Simple',
	);
	protected $css_compressors = array(
		'Advertikon\\Compressor\\Css\\Simple',
	);
	protected $cache_time = null;
	protected $content = false;
	protected $max_age = 10;
	protected $accepted_encoding = array(
		'deflate' => 'gzdeflate',
		'gzip'    => 'gzencode',
	);
	protected $client_encoding = array();
	protected $mtime = null;
	protected $available_encoding = null;
	protected $compression = '';
	protected $compression_level = 255;

	public function __construct( $compression_setting = 255 ) {
		ADK()->log( sprintf( 'Compression level is: %s', $compression_setting ), ADK()->log_debug_flag );

		$this->compression_level = $compression_setting;
		$this->cache_dir = ADK()->data_dir . 'minify/';

		if ( ! is_dir( $this->cache_dir ) ) {
			$fs = new Fs();
			$fs->mkdir( $this->cache_dir );
		}

		$this->force = ( $this->compression_level & 16 ) !== 16;
		$this->use_browser_cache = ( $this->compression_level & 48 ) === 48;
		$this->as_is = ( $this->compression_level & 15 ) === 1;
	}

	/**
	 * Returns minified copy of the source
	 * @param string $list Comma-separated list of sources
	 * @param string $type Source's type: script, css
	 * @return string|boolean False if compression failed
	 */
	public function get( $list, $type = 'js' ) {
		$start = microtime( true );

		try {
			if ( empty( $list ) ) {
				$mess = 'List of sources is empty';
				trigger_error( $mess );
				throw new Exception( $mess );
			}

			if ( empty( $type ) ) {
				$mess = 'Source type is undefined';
				trigger_error( $mess );
				throw new Exception( $mess );
			}

			$this->hash = md5( $list );
			$this->get_compression_methods();
			$this->source = explode( ',', $list );
			$this->type = $type;

			if ( 'js' === $type ) {
				$this->js();

			} elseif ( 'css' === $type ) {
				$this->css();

			} else {
				trigger_error( 'Undefined source type' );
			}
			
		} catch ( \Exception $e ) {
			header( 'HTTP/1.0 404 Not Found', 1, 404 );
		}

		ADK()->log(
			sprintf(
				'Source handle time: %.2f sec',
				microtime( true ) - $start
			),
			ADK()->log_debug_flag
		);

		return $this->content;
	}

	/**
	 * Checks which encoding methods client does support
	 * @return void
	 */
	protected function get_compression_methods() {
		$accept = null;

		if ( ! isset( $_SERVER['HTTP_ACCEPT_ENCODING'] ) ) {
			ADK()->log( 'Client does not support compression', ADK()->log_debug_flag );

			return;
		}
		
		$accept = $_SERVER['HTTP_ACCEPT_ENCODING'];

		ADK()->log( sprintf( 'Browser supports such encodings: %s', $accept ),ADK()->log_debug_flag );

		$this->client_encoding = array_map( function( $v ) {
			return trim( $v );
		}, explode( ',', $accept ) );
	}

	/**
	 * Handle JS sources
	 * @return string
	 */
	protected function js() {
		$this->base_dir = dirname( DIR_TEMPLATE ) . '/javascript/';
		header( 'Content-Type: application/javascript; charset=' . ADK()->get_default_charset(), 1 );
		$this->process();
	}

	/**
	 * Handle JS sources
	 * @return string
	 */
	protected function css() {
		$this->base_dir = dirname( DIR_TEMPLATE ) . '/';
		header( 'Content-Type: text/css; charset=' . ADK()->get_default_charset(), 1 );
		$this->process();
	}

	/**
	 * Process the request
	 * @return void
	 */
	protected function process() {
		try {

			// Browser's cache is valid
			if ( $this->check_expiration() ) {
				header( 'HTTP/1.1 304 Not Modified', 1, 304 );
				ADK()->log( 'Browser\'s cache is valid', ADK()->log_debug_flag );
				$this->content = true;

			// Browser's cache is stale or missing
			} else {
				ADK()->log( 'Cache is actual - serve it', ADK()->log_debug_flag );
				$this->return_cache();
			}
			
		// Cache is missing
		} catch ( Exception $e ) {
			$this->minify();
		}

		header( sprintf( "Last-Modified: %s", date( 'r' ) ) );
		header( sprintf( "Cache-Control: max-age=%s", $this->max_age ) );
		header( 'Expires: ' );
		header( 'Pragma: ' );
	}

	/**
	 * Checks whether cache is still valid
	 * @param array $list List of source files
	 * @return boolean Try if browser's cache is valid, false if browser's cache is stale
	 * @throws Advertikon\Exception if cache is missing
	 */
	protected function check_expiration() {

		// Flush cache
		if ( $this->force ) {
			ADK()->log( 'Flash cache', ADK()->log_debug_flag );

			// To fill in available encoding methods
			$this->check_cache();
			throw new Exception( 'Force mode' );
		}

		// Source set's modification time
		$this->get_m_time();

		// Check browser's cache
		if ( $this->use_browser_cache && isset( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) ) {
			$date = new \DateTime( $_SERVER['HTTP_IF_MODIFIED_SINCE'] );

			if ( $date->getTimestamp() >= $this->mtime ) {
				return true;
			}

			ADK()->log( 'Browser has stale version', ADK()->log_debug_flag );
		}

		// Cache is valid
		if ( $this->check_cache() ) {
			return false;
		}

		throw new Exception( 'Cache is missing' );
	}

	/**
	 * Defines modification time for source set
	 * @return void
	 */
	protected function get_m_time() {
		foreach( $this->source as $source ) {
			$time = filemtime( $this->base_dir . $source );

			if ( false === $time ) {
				throw new \Exception( sprintf( 'Source file "%s" is missing', $source ) );
			}

			$times[] = $time;
		}

		$this->mtime = max( $times );
	}

	/**
	 * Checks storage for cache
	 * @return boolean True if cache exists and is valid
	 */
	protected function check_cache() {
		$possible_cache = array();
		$cache = array();

		$this->available_encoding = array_intersect(
			array_keys( $this->accepted_encoding ), $this->client_encoding
		);

		foreach( $this->available_encoding as $method ) {
			$possible_cache[] = $this->cache_dir . $this->hash . '-' . $method;
		}

		// There is no one matching compression method
		if ( ! $possible_cache ) {
			$possible_cache[] = $this->cache_dir . $this->hash;
		}

		for( $i = 0, $len = count( $possible_cache ); $i < $len; $i++ ) {
			$time = @filemtime( $possible_cache[ $i ] );

			if ( false === $time || $time < $this->mtime ) {
				continue;
			}

			$cache[ filesize( $possible_cache[ $i ] ) ] = $possible_cache[ $i ];
		}

		if ( ! $cache ) {
			return false;
		}

		ksort( $cache );
		$this->cache = current( $cache );

		return true;
	}

	/**
	 * Returns content of a cache
	 * @return string
	 */
	protected function return_cache() {
		$this->content = file_get_contents( $this->cache );

		$compression = strstr( $this->cache, '-' );

		if ( false !== $compression ) {
			header( sprintf( "Content-Encoding: %s", substr( $compression, 1 ) ) );
		}
	}

	/**
	 * Runs JS compression
	 * @return void
	 */
	public function minify() {
		$text = '';

		foreach( $this->source as $source ) {
			if ( ! is_file( $this->base_dir . $source ) ) {
				$mess = sprintf( 'File "%s" doesn not exists', $this->base_dir . $source );
				ADK()->log( $mess, ADK()->log_error_flag );
				throw new \Exception( $mess );
			} 

			$text .= file_get_contents( $this->base_dir . $source );
		}

		if ( ! $text ) {
			return;
		}

		if ( $this->as_is ) {
			$this->content = $text;
			ADK()->log( 'Return concatenated source', ADK()->log_debug_flag );

		} else {
			$compression_methods = $this->{$this->type . '_compressors'};

			// Remove only whitespaces, use full compression as fall back
			if ( ( $this->compression_level & 15 ) !== 15 ) {
				$compression_methods = array_reverse( $compression_methods );
			}

			foreach( $compression_methods as $compressor_name ) {
				ADK()->log( sprintf( 'Compressing source by "%s"', $compressor_name ), ADK()->log_debug_flag );

				$compressor = new $compressor_name();
				
				try {
					$this->content = $compressor->run( $text );

					ADK()->log(
						sprintf(
							'Source has been compressed. Compression rate: %.2f%%',
							( ( strlen( $text ) - strlen( $this->content ) ) / strlen( $text ) ) * 100
						),
						ADK()->log_debug_flag
					);

					break;

				} catch ( Exception $e ) {

					ADK()->log(
						sprintf(
							'Compression error: "%s"',
							$e->getMessage()
						), 
						ADK()->log_error_flag
					);
				}
			}
		}

		if ( $this->content ) {
			$this->compress();
			file_put_contents(
				$this->cache_dir . $this->hash . ( $this->compression ? '-' . $this->compression : '' ),
				$this->content
			);

		} else {
			ADK()->log(
				'Failed to compress source - return an uncompressed copy',
				ADK()->log_debug_flag
			);

			$this->content = $text;
			$this->compress();
		}
	}

	/**
	 * Compresses contents
	 * @return void
	 */
	protected function compress() {
		$compressed = null;

		if ( ! $this->available_encoding ) {
			return;
		}

		foreach( $this->available_encoding as $name ) {

			ADK()->log(
				sprintf( 'Use "%s" method to compress source', $name ),
				ADK()->log_debug_flag
			);

			$compressed = call_user_func_array(
				$this->accepted_encoding[ $name ],
				array( $this->content, 9 )
			);

			if ( false === $compressed ) {
				ADK()->log(
					sprintf( '"%s" compression failed', $name ),
					ADK()->log_error_flag
				);

			} else {
				ADK()->log(
					sprintf(
						'Compression rate: %.2f%%',
						( ( strlen( $this->content ) - strlen( $compressed ) ) / strlen( $this->content ) ) * 100
					),
					ADK()->log_debug_flag
				);

				$this->content = $compressed;
				$this->compression = $name;
				header( sprintf( "Content-Encoding: %s", $name ) );

				break;
			}
		}

		if ( false === $compressed ) {
			ADK()->log( 'Failed to compress source. Uncompressed copy returned', ADK()->log_error_flag );

		} elseif ( is_null( $compressed ) ) {
			ADK()->log(
				'No matched compressed method available. Uncompressed version returned',
				ADK()->log_debug_flag
			);
		}
	}

}
