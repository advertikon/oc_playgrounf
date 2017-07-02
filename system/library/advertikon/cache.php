<?php
/**
 * Advertikon Cache Class
 * @author Advertikon
 * @package Advertikon
 * @version 2.8.11
 */

namespace Advertikon;

class Cache {

	protected $dir = null;
	protected $exp = 3600;
	protected $fs = null;
	protected $is_win = false;

	public function __construct( $expiration = null, $dir = null) {
		if ( $this->is_win() ) {
			$this->is_win = true;
			return;
		}

		$this->fs = new Fs();

		if ( ! is_null( $expiration ) ) {
			$this->exp = $expiration;
		}

		if ( ! is_null( $dir ) ) {
			$this->dir = rtrim( $dir, '/' ) . '/';

			if ( ! $this->dir ) {
				$mess = 'Cache folder is mandatory';
				trigger_error( $mess );
				throw new Exception( $mess );
			}

			if ( ! $this->fs->above_store_root( $this->dir ) ) {
				$mess = sprintf( 'Cache directory "%s" may not be below the store root folder', $this->dir );
				trigger_error( $mess );
				throw new Exception( $mess );
			}

		} else {
			$this->dir = DIR_CACHE . 'adk/stripe/';
		}

		if ( (int)$this->exp <= 0 ) {
			$mess = 'Expiration time should be a number which is greater than 0';
			trigger_error( $mess );
			throw new Exception( $mess );
		}

		if ( ! is_dir( $this->dir ) ) {
			$this->fs->mkdir( $this->dir );
			$this->add_htaccess();
			
		} else {
			$this->clear();
		}
	}

	/**
	 * Caches value
	 * @param string $key Value's key 
	 * @param mixed $val Value
	 * @param int|null $exp Expiration period in seconds
	 * @return void
	 */
	public function set( $key, $val, $exp = null ) {
		if ( $this->is_win ) {
			return;
		}

		if ( is_null( $exp ) ) {
			$exp = $this->exp;
		}

		$this->delete( $key );

		$entry = $this->dir . $key . '.' . ( time() + $exp );
		file_put_contents( $entry, serialize( $val ) );

		// Grant access only to owner
		chmod( $entry, 0600 );
	}

	/**
	 * Returns cached value
	 * @param string $key Cache key
	 * @return mixed|null
	 */
	public function get( $key ) {
		if ( $this->is_win ) {
			return null;
		}

		$cache = glob( $this->dir . $key . '.*' );

		if ( $cache ) {
			$c = current( $cache );

			if ( is_file( $c ) && substr( strstr( $c, '.' ), 1 ) >= time() ) {
				return unserialize( file_get_contents( $c ) );
			}
		}

		return null;
	}

	/**
	 * Delete cache entry
	 * @param string $key Cache key
	 * @return void
	 */
	public function delete( $key ) {
		if ( $this->is_win ) {
			return;
		}

		foreach( glob( $this->dir . $key . '.*' ) as $c ) {
			@unlink( $c );
		}
	}

	/**
	 * Removes expired cache entries 
	 * @return void
	 */
	public function clear() {
		if ( $this->is_win ) {
			return;
		}

		$this->fs->iterate_directory( $this->dir, function( $file ) {
			if ( is_file( $file ) && substr( strstr( $file, '.' ), 1 ) < time() ) {
				unlink( $file );
			}
		} );
	}

	/**
	 * Adds .htaccess file to restrict access to cache from outside
	 * @return void
	 */
	protected function add_htaccess() {
		if ( ! is_file( $this->dir . '.htaccess' ) ) {
			$content = '# Automatically generated .htaccess file by Advertikon Cache class
			Order Deny,allow
			Deny from all
			<Files "*">
				Order allow,deny
				Deny from all
			</Files>';

			file_put_contents( $this->dir . '.htaccess', $content );
			chmod( $this->dir . '.htaccess', 0644 );
		}
	}

	protected function is_win() {
		return strtoupper( substr( PHP_OS, 0, 3 ) ) === 'WIN';
	}

}
