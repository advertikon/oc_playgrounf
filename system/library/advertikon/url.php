<?php
/**
 * Advertikon Url Class
 * @author Advertikon
 * @package Advertikon
 * @version 0.0.7
 */

namespace Advertikon;

class Url {

	private $scheme = '';
	private $host = '';
	private $port = '';
	private $path = '';
	private $query = array();
	private $fragment = '';
	public static $extensions = array(
		'analytics',
		'captcha',
		'dashboard',
		'feed',
		'fraud',
		'module',
		'payment',
		'shipping',
		'theme',
		'total',
	);
	public static $spoof_23 = false;
	public $namespace = null;

	public function __construct( $namespace = null ) {
		$this->namespace = $namespace;
	}

	protected function is_empty() {
		return ! ( $this->scheme or $this->host or $this->port or $this->path or $this->query or $this->fragment );
	}

	public function reset() {
		$this->scheme = '';
		$this->host = '';
		$this->port = '';
		$this->path = '';
		$this->query = array();
		$this->fragment = '';

		return $this;
	}

	/**
	 * Returns URL's scheme part
	 * @return string
	 */
	public function get_scheme() {
		return $this->scheme;
	}

	/**
	 * Returns URL's host part
	 * @return string
	 */
	public function get_host() {
		return $this->host;
	}

	/**
	 * Returns URL's port part
	 * @return string
	 */
	public function get_port() {
		return $this->port;
	}

	/**
	 * Returns URL's path part
	 * @return string
	 */
	public function get_path() {
		return $this->path;
	}

	/**
	 * Returns URL's fragment part
	 * @return string
	 */
	public function get_fragment() {
		return $this->fragment;
	}

	/**
	 * Parses URL
	 * @param String $url 
	 * @return Array
	 */
	public function parse( $url ) {
		$self = null;

		if ( $this->is_empty() ) {
			$self = new self;
			$self->namespace = $this->namespace;

		} else {
			$self = $this;
		}

		$self->reset();

		if( gettype( $url ) !== 'string' ) {
			trigger_error( sprintf( '%s: URL need to be a string, %s given instead', __CLASS__, gettype( $url ) ) );
			return;
		}

		if( ! $url ) {
			trigger_error( sprintf( '%s: URL may not be an empty string', __CLASS__ ) );
			return;
		}

		$preg_str = '%' .
					'(?:(^[^/:]*?)(?=://))?' . // Scheme
					'(?::?/{2})?' .
					'([^/:?#]+?(?=\/|$|:|\?|#))?' . // Host
					':?' .
					'(?:(?<=:)(\d+))?' . // Port
					'([^:?#]+)?' . // Path
					'\??' .
					'(?:(?<=\?)([^#]+))?' . // Query
					'#?' .
					'(?:(?<=#)(.*))?' . // Fragment
					'%';

		if( preg_match( $preg_str, $url, $m ) ) {

			if( isset( $m[1] ) ) {
				$self->scheme = $m[1];
			}

			if( isset( $m[2] ) ) {
				$self->host = $m[2];
			}

			if( isset( $m[3] ) ) {
				$self->port = $m[3];
			}

			if( ! empty( $m[4] ) ) {
				$self->path = $m[4];

			} else {
				$self->path = '/';
			}
			if( isset( $m[5] ) && $m[5] ) {

				foreach( explode( '&', str_replace( '&amp;', '&', $m[5] ) ) as $part ) {
					$parts = explode( '=', $part );

					if ( empty( $parts[0] ) || ! isset( $parts[1] ) ) {
						trigger_error( sprintf( 'URL query part "%s" is invalid', $part ) );
						continue;
					}

					$self->query[ $parts[0] ] = $parts[1];	
				}
			}

			if( isset( $m[6] ) ) {
				$self->fragment = $m[6];
			}
		}

		return $self;
	}

	/**
	 * Normalizes URL
	 * @param String|Array $url URL to be normalized
	 * @return String
	 */
	public function to_string() {
		if ( ! $this->host ) {
			$this->gues_host();
		}

		$ret = 
		( $this->scheme ? $this->scheme . ':' : '' ) . '//' .
		( ! $this->host ? $_SERVER['SERVER_NAME'] : $this->host ) .
		( ! $this->port ? '' : ':' . $this->port ) .
		$this->path .
		( ! $this->query ? '' : '?' . $this->query_to_string() ) .
		( ! $this->fragment ? '' : '#' . $this->fragment );

		return $ret;
	}

	/**
	 * Define host name depending on position
	 * @return void
	 */
	protected function gues_host() {
		$host = '';

		if ( defined( 'HTTP_CATALOG' ) ) {
			$host = new self( self::admin_url() );

		} else {
			$host = new self( self::catalog_url() );
		}

		$this->scheme = $host->scheme;
		$this->host = $host->host;
	}

	/**
	 * Returns query part as a string 
	 * @return string
	 */
	public function query_to_string() {
		$p = array();

		foreach( $this->query as $k => $v ) {
			$p[] = $k . '=' . $v;
		}

		return implode( '&', $p );
	}

	/**
	 * Adds query parameter
	 * @param string $name Parameter name
	 * @param strong $value Parameter value
	 * @return object
	 */
	public function add_query( $name, $value ) {
		if ( ! is_string( $name ) ) {
			trigger_error(
				sprintf(
					'%s: name of query parameter need to be a string, %s given instead',
					__CLASS__,
					gettype( $name )
				)
			);

			return $this;
		}

		if ( ! is_string( $value ) ) {
			trigger_error(
				sprintf(
					'%s: value of query parameter need to be a string, %s given instead',
					__CLASS__,
					gettype( $value )
				)
			);

			return $this;
		}

		$this->query[ $name ] = $value;

		return $this;
	}

	/**
	 * Returns query part by name
	 * @param string $name Query's part name 
	 * @return string
	 */
	public function get_query( $name = null ) {
		if ( is_null( $name ) ) {
			return $this->query;
		}

		if ( isset( $this->query[ $name ] ) ) {
			return $this->query[ $name ];
		}

		return null;
	}

	/**
	 * Returns URL to extension's action
	 * @param string $route Action name 
	 * @return string
	 */
	public function url( $route = '', $query = array() ) {
		if ( '' === $route ) {
			$parts = array();

		} else {
			$parts = explode( '/', $route );
		}

		if ( count( $parts ) <= 1 ) {
			array_unshift( $parts, ADK( $this->namespace )->type, ADK( $this->namespace )->code );
		}

		if ( version_compare( VERSION, '2.3.0.0', '>=' ) || self::$spoof_23 ) {
			if ( in_array( $parts[0], self::$extensions ) ) {
				array_unshift( $parts, 'extension' );
			}
		}
			
		$route = implode( '/', $parts );

		if( isset( ADK( $this->namespace )->session->data['token'] ) ) {
			$query = array_merge( (array)$query, array( 'token' => ADK( $this->namespace )->session->data['token'] ) );
		}

		return ADK( $this->namespace )->url->link( $route, http_build_query( $query ), 'SSL' );
	}

	/**
	 * Returns base URL for catalog area
	 * @param boolean|null|string $ssl SSL setting: true, false, auto or null 
	 * @return string
	 */
	public function catalog_url( $ssl = null ) {
		$ret = '';
		$url = '';
		$ssl_url = '';

		// Get URL depending on current position
		if ( defined( 'HTTP_CATALOG' ) ) {
			$url = HTTP_CATALOG;
			$ssl_url = HTTPS_CATALOG;

		} else {
			$url = HTTP_SERVER;
			$ssl_url = HTTPS_SERVER;
		}

		$ssl_config = ADK( $this->namespace )->config->get( 'config_secure' );

		// Explicit HTTPS
		if ( true === $ssl || ( 'auto' === $ssl && $ssl_config ) ) {
			return $ssl_url;

		// Explicit HTTP
		} elseif ( false === $ssl || ( 'auto' === $ssl && ! $ssl_config ) ) {
			return $url;

		// Protocol-less scheme
		} else {
			if ( strpos( $url, 'http://' ) === 0 ) {
				$ret = substr( $url, 5 );

			} elseif ( strpos( $ssl_url, 'https://' ) === 0 ) {
				$ret = substr( $ssl_url, 6 );
			}
		}

		return $ret;
	}

	/**
	 * Returns base URL for administrative area
	 * @param boolean|null|string $ssl SSL setting: true, false, auto or null 
	 * @return string
	 */
	public function admin_url( $ssl = null ) {
		$ret = '';

		// Return URL if only at admin area
		if ( defined( 'HTTP_CATALOG' ) ) {

			$ssl_config = ADK( $this->namespace )->config->get( 'config_secure' );

			// Explicit HTTPS
			if ( true === $ssl || ( 'auto' === $ssl && $ssl_config ) ) {
				return HTTPS_SERVER;

			// Explicit HTTP
			} elseif ( false === $ssl || ( 'auto' === $ssl && ! $ssl_config ) ) {
				return HTTP_SERVER;

			// Protocol-less scheme
			} else {
				if ( strpos( HTTP_SERVER, 'http://') === 0 ) {
					$ret = substr( HTTP_SERVER, 5 );

				} elseif ( strpos( HTTPS_SERVER, 'https://') === 0 ) {
					$ret = substr( HTTPS_SERVER, 6 );
				}
			}
		}

		return $ret;
	}

	/**
	 * Returns v2.3+ aware route
	 * @param string $route 
	 * @return string
	 */
	public function get_route( $route ) {
		if ( version_compare( VERSION, '2.3.0.0', '>=' ) || self::$spoof_23 ) {
			$parts = explode( '/', $route );

			if ( in_array( $parts[0], self::$extensions ) ) {
				array_unshift( $parts, 'extension' );

				$route = implode( '/', $parts );
			}
		}

		return $route;
	}
}
