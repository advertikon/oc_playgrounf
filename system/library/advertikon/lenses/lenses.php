<?php
/**
 * Advertikon Class
 * @author Advertikon
 * @package XXXXX
 * @version 0.00.000
 */

namespace Advertikon\Stripe;

class Advertikon extends \Advertikon\Advertikon {

	// ******************** Common part **************************************//
	public $type = 'xxxxxx';
	public $code = 'xxxxxx';
	public static $c = __NAMESPACE__;
	public $tables = array(
		// Code - table name without prefix
		'xxxxxxx'         => 'xxxxxxx',
	);

	// ********************** Common part ************************************//

	static $instance = null;

	/**
	 * Returns class' singleton
	 * @return object
	 */
	public static function instance( $code = null ) {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
			parent::$instance[ self::$c ] = self::$instance;
		}

		return self::$instance;
	}

	public static $file = __FILE__;

	public function __construct() {
		if ( version_compare( VERSION, '2.3.0.0', '>=' ) ) {
			$this->type = 'extension/' . $this->type;
		}

		parent::__construct();
		$this->compression_level = parent::COMPRESSION_LEVEL_NONE;
	}


	/**
	 * @see Advertikon\Advertikon::get_version()
	 */
	static public function get_version() {
		return parent::get_version();
	}

	/**
	 * Checks library compatibility
	 * @return array
	 */
	public function check_compatibility(){
		$all_is_bad = false;
		$name = 'XXXXXXX';
		$return = parent::check_compatibility();

		// CURL library presence
		if ( $all_is_bad || ! function_exists( 'curl_version' ) ) {
			$return[ $name ]['error'][] = $this->__( 'PHP CURL library missing' ) . '. ' .
			sprintf(
				'%s<a href="%s" target="_blank">%s</a>',
				$this->__( 'Follow this link to ' ),
				'http://php.net/manual/book.curl.php',
				$this->__( 'get more details' )
			);
		}

		return $return;
	}
}
