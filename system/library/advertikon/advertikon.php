<?php
/**
 * Advertikon Class
 * @author Advertikon
 * @package Advertikon
 * @version 2.8.11
 */

namespace Advertikon {

class Advertikon {

	public $registry = null;
	public $full_name;
	public $renderer = null;
	public $query = null;
	public $log_strictness = -1;
	public static $one_time_cache = array();
	public $tables = array();
	public $type = '';
	public $code = '';
	public $option = null;
	protected $mail_instance = null;
	protected $mail_init = false;
	protected $do_cache = true;
	public static $instance = array();
	public $adk_url = null;
	public $console = null;

	const LOGS_DISABLE 	= -1;
	const LOGS_ERR 		= 0;
	const LOGS_MSG 		= 50;
	const LOGS_DEBUG 	= 100;

	const CHAR_NUMERIC	= '0123456789';
	const CHAR_ALPHA	= 'QWERTYUIOPASDFGHJKLZXCVBNMqwertyuiopasdfghjklzxcvbnm';
	const CHAR_SYMB		= '_-.';
	const CHAR_SPACE	= ' ';

	const COMPRESSION_LEVEL_NONE        = 0;
	const COMPRESSION_LEVEL_COMBINE     = 1;
	const COMPRESSION_LEVEL_WHITESPACES = 3;
	const COMPRESSION_LEVEL_FULL        = 15;

	const TICKET_BUTTON_URL = 'http://shop.advertikon.com.ua/support/ticket_button.php';

	public $log_error_flag = null;
	public $log_debug_flag = null;

	public $data_dir = '';

	public $compression_level = self::COMPRESSION_LEVEL_FULL;
	public $compression_cache = true;

	// In order to flush cache "use browser cache" setting need to be "false" as well
	public $compression_browser_cache = true;

	static public $file = '';

	public function __construct() {
		global $adk_registry;

		$this->registry = $adk_registry;
		$this->full_name = ( $this->type ? $this->type . '/' : '' ) . $this->code;

		if ( $this->full_name ) {
			$this->registry->get( 'load' )->language( $this->full_name );
		}

		$this->log_strictness = defined( 'ADK_TEST' ) ? self::LOGS_DEBUG : $this->config( 'debug', 0 );

		$this->log_error_flag = new Log_Error();
		$this->log_debug_flag = new Log_Debug();

		$this->data_dir = DIR_SYSTEM . 'storage/adk/';

		// Set custom cache
		$this->cache = new Cache();

		// Console
		$this->console = new Console( $this->registry );
	}

	public static function instance( $code = null ) {
		if ( ! is_null( $code ) ) {
			if ( empty( self::$instance[ $code ] ) ) {
				$mess = sprintf( 'Failed to load class "%s", library is not initialized', $code );
				trigger_error( $mess );
				throw new Exception( $mess );
				
			} else {
				return self::$instance[ $code ];
			}

		} else {
			if ( empty( self::$instance['main'] ) ) {
				self::$instance['main'] = new self();
			}
			
			return self::$instance['main'];
		}
	}

	/**
	 * Gets value of configuration setting regarding extension name
	 * @param string $name Configuration name
	 * @return mixed
	 */
	public function config( $name, $default = null ) {

		// Composed name eg one/two/three
		if ( strpos( $name , '/' ) !== false ) {
			$parts = explode( '/', $name );
			$conf = $this->registry->get( 'config' )->get( $this->prefix_name( array_shift( $parts ) ) );

		} else {
			$conf = $this->registry->get( 'config' )->get( $this->prefix_name( $name ) );
		}

		if ( ! empty( $parts ) ) {
			foreach( $parts as $p ) {
				if ( isset( $conf[ $p ] ) ) {
					$conf = $conf[ $p ];

				} else {
					$conf = null;
					break;
				}
				
			}
		}

		if( is_null( $conf ) ) {
			if ( defined( 'ADK_TEST' ) ) {
				trigger_error( sprintf( 'Configuration with name "%s" does not exist', $name ) );
			}

			return $default;
		}

		return $conf;
	}

	public function __get( $name ) {
		if ( isset( $this->tables[ $name ] ) ) {
			return $this->tables[ $name ];
		}

		if( $this->registry->has( $name ) ) {
			return $this->registry->get( $name );
		}

		if ( 'customer' === $name ) {
			return null;
		}

		$mess = sprintf( 'Invalid property name: "%s"', $name );

		if ( function_exists( 'adk_log' ) ) {
			adk_log( $mess );
			adk_log( debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS ) );
		}

		throw new Exception( $mess );
	}

	public function __set( $name, $value ) {
		$this->registry->set( $name, $value );
	}

	/**
	 * Renderer lazy loader
	 * @return object
	 */
	public function renderer() {
		$namespace = null;

		if ( ! $this->renderer ) {
			$parts = explode( '\\', get_class( $this ) );

			if ( isset( $parts[0] ) && isset($parts[1] ) ) {
				$namespace = $parts[0] . '\\' . $parts[1];
			}

			$this->renderer = new Renderer( $namespace );
		}

		return $this->renderer;
	}

	/**
	 * Shorthand for Advertikon\Advertikon::renderer()
	 * @return object
	 */
	public function r( $element = null ) {
		if ( is_array( $element ) ) {
			return $this->renderer()->render_element( $element );
		}

		return  $this->renderer();
	}

	/**
	 * Renderer lazy loader
	 * @return object
	 */
	public function url() {
		$namespace = null;

		if ( ! $this->adk_url ) {
			$parts = explode( '\\', get_class( $this ) );

			if ( isset( $parts[0] ) && isset($parts[1] ) ) {
				$namespace = $parts[0] . '\\' . $parts[1];
			}

			$this->adk_url = new Url( $namespace );
		}

		return $this->adk_url;
	}

	/**
	 * Shorthand for Advertikon\Advertikon::renderer()
	 * @return object
	 */
	public function u( $route = null, $query = array() ) {
		if ( ! is_null( $route ) ) {
			return $this->url()->url( $route, $query );
		}

		return  $this->url();
	}

	/**
	 * Query lazy loader
	 * @return object
	 */
	public function query() {
		if ( ! $this->query ) {
			$this->query = new Query();
		}

		return $this->query;
	}

	/**
	 * Shorthand for Advertikon\Advertikon::query
	 * @param array $query Query
	 * @return object
	 */
	public function q( $query = null ) {
		if ( is_array( $query ) ) {
			return $this->query()->run_query( $query );
		}

		return $this->query();
	}

	/**
	 * Option lazy loader
	 * @return object
	 */
	public function option() {
		if ( ! $this->option ) {
			$this->option = new Option();
		}

		return $this->option;
	}

	/**
	 * Wrapper to option
	 * @return object
	 */
	public function o(){
		return $this->option();
	}

	/**
	 * Remove extension tables from DB
	 * @return void
	 */
	public function remove_db() {
		foreach( $this->tables as $table ) {
			$this->db->query( "DROP TABLE IF EXISTS `" . DB_PREFIX . $table . "`" );
		}
	}

	/**
	 * Translator helper
	 * Supports 'sprintf' text substitutions
	 * @param String $text String to be translated
	 * @return String
	 */
	public function __( $text ) {
		$translation = $this->language->get( $text );
		$args = func_get_args();

		if ( count( $args ) > 1 ) {
			array_shift( $args );
			array_unshift( $args, $translation );
			$substitution = call_user_func_array( 'sprintf' , $args );

			if ( $substitution ) {
				return $substitution;
			}
		}

		return $translation;
	}

	/**
	 * Get value for administrative area input control element
	 * Checks firstly POST, then tries get configuration value
	 * @param String $name 
	 * @param Mixed $default Default value
	 * @return Mixed
	 */
	public function get_value_from_post( $name , $default = '' ) {

		$name = $this->prefix_name( $name );
		$data = null;
		$parts = null;

		if ( strpos( $name, '[' ) ) {
			$parts = explode( '[', str_replace( ']', '', $name ) );
			$name = array_shift( $parts );
		}

		if ( isset( $this->request->post[ $name ] ) ) {
			$data = $this->request->post[ $name ];

		} else {
			$data = $this->config->get( $name );
		}

		$ret = $this->get_from_array( $data, $parts );

		if ( ! is_null( $ret ) ) {
			return $ret;
		}

		return $default;
	}

	/**
	 * Returns value from array
	 * @param array $array Target array
	 * @param string|array $path Value name to be retrieved
	 * @return mixed
	 */
	public function get_from_array( $array, $path = null ) {
		if ( ! is_array( $array ) ) {
			if ( $path ) {
				return null;
			}

			return $array;
		}

		if ( ! $path ) {
			return $array;
		}

		$ret = $array;

		if ( ! is_array( $path ) ) {
			$path = explode( '/', $path );
		}

		foreach( $path as $p ) {
			if( isset( $ret[ $p ] ) ) {
				$ret = $ret[ $p ];

			} else {
				return null;
			}
		}

		return $ret;
	}

	/**
	 * Fetch variable from POST
	 * @param String $name Variable name
	 * @param Mixed|null $default Default variable
	 * @return Mixed
	 */
	public function post( $name, $default = null ) {
		if( ! empty( $this->request->post[ $this->prefix_name( $name ) ] ) ) {
			return $this->request->post[ $this->prefix_name( $name ) ];
		}

		return $this->p( $name, $default );
	}

	/**
	 * Fetch non-prefixed variable from POST
	 * @param String $name Variable name
	 * @param Mixed|null $default Default variable
	 * @return Mixed
	 */
	public function p( $name, $default = null ) {
		if( isset ( $this->request->post[ $name ] ) ) {
			return $this->request->post[ $name ];
		}

		return $default;
	}

	/**
	 * Fetch non-prefixed variable from REQUEST
	 * @param String $name Variable name
	 * @param Mixed|null $default Default variable
	 * @return Mixed
	 */
	public function request( $name, $default = null ) {
		if( isset ( $this->request->request[ $name ] ) ) {
			return $this->request->request[ $name ];
		}

		return $default;
	}

	/**
	 * Makes extension specific (prefixed) names
	 * @param String $name 
	 * @return String
	 */
	public function prefix_name( $name ) {
		$prefix = $this->get_prefix( $name );

		if ( $prefix && strpos( $name, $prefix ) !== 0 ) {
			$name = $prefix . '_' . $name;
		}

		return $name;
	}

	/**
	 * Strips extension specific prefix from name
	 * @param string $name 
	 * @return string
	 */
	public function strip_prefix( $name ) {
		$prefix = $this->get_prefix();

		if( strpos( $name, $prefix . '_' ) === 0 ) {
			$name = substr( $name , strlen( $prefix ) + 1 );
		}

		return $name;
	}

	/**
	 * Returns extension's prefix
	 * @param null $name Does not used
	 * @return string
	 */
	public function get_prefix( $name = null ) {
		return $this->code;
	}

	/**
	 * Recursively converts object to array
	 * @param object $object Target object 
	 * @return array
	 */
	public function object_to_array( $object ) {
		if( gettype( $object ) === 'array' ) {
			foreach( $object as &$o ) {
				$o = $this->object_to_array( $o );
			}

		} elseif( gettype( $object ) === 'object' ) {
			$object = $this->object_to_array( (array)$object );
		}

		return $object;
	}

	/**
	 * Fixes JSON/MySQL issue with unicode sequences - adds backslashes, removed by MyQSL parser
	 * @param string $string JSON sting 
	 * @return string Fixed JSON string
	 */
	public function fix_json_string( $string ) {
		$string = preg_replace( '/(?<!\\\)(u[0-9a-f]{4})/', '\\\$1', $string );

		return $string;
	}

	/**
	 * Creates specified array structure
	 * @param array &$array Target array
	 * @param string $path Slash separated path of structure to be created
	 * @return array
	 */
	public function &create_array_structure( &$array, $path ) {
		$parts = explode( '/', $path );

		if( $parts ) {
			foreach( $parts as $part ) {
				if( ! isset( $array[ $part ] ) || ! is_array( $array[ $part ] ) ) {
					$array[ $part ] = array();
				}

				$array = &$array[ $part ];
			}
		}

		return $array;
	}

	/**
	 * Create element's name by given path
	 * @param string $name Path in a form of one/two/three
	 * @return string
	 */
	public function build_name( $name, $delimiter = '-_' ) {
		$parts = preg_split( '/(?<!\\\\)[' . preg_quote( $delimiter ) . ']/', $name );
		$name = array_shift( $parts );

		if( $parts ) {
			$name .= '[' . implode( '][', $parts ) . ']';

			if ( ! count( $parts ) === 1 || '' === current( $parts ) ) {
				$name .= '[]';
			}
		}

		return $name;
	}

	/**
	 * Escapes string to be used with build_name method
	 * @param string $name Name
	 * @return string
	 */
	public function escape_name( $name ) {
		return preg_replace( '/(-)/', '\\\$1', $name );
	}

	/**
	 * Checks whether haystack is ending with specific needle 
	 * @param string $haystack String to be checked
	 * @param string $needle Searched ending 
	 * @return boolean
	 */
	public function is_ended_with( $haystack, $needle ) {

		$needle_length = strlen( $needle );
		if( strlen( $haystack ) < $needle_length ) {
			return false;
		}

		return strrpos( $haystack, $needle, -1 * $needle_length ) !== false;
	}

	/**
	 * Returns URL to language flag image from language data set
	 * @param array $lang Language data set
	 * @return string
	 */
	public function get_lang_flag_url( $lang ) {
		$url = '';

		if ( version_compare( VERSION, '2.3.0.0', '>=' ) ) {
			if ( ! isset( $lang['code'] ) ) {
				trigger_error( 'Language data set missing language code' );
				return '';
			}

			$path = DIR_LANGUAGE . $lang['code'] . '/'. $lang['code'] . '.png';

		} else { 
			if ( ! isset( $lang['image'] ) ) {
				trigger_error( 'Language data set missing language image' );
				return '';
			}

			if( version_compare( VERSION, '2.2.0.0', '=' ) && 'gb.png' === $lang['image'] ) {
				$lang['image'] = 'england.png';
			}

			$path = DIR_IMAGE . 'flags/' . $lang['image'];
		}

		if ( is_file( $path ) ) {
			$url = $this->get_store_url() . substr( $path , strlen( dirname( DIR_SYSTEM ) ) );
		}

		return $url;
	}

	/**
	 * Returns current language ID
	 * @return int
	 */
	public function get_lang_id( $ssl = null ) {
		return $this->config->get( 'config_language_id' );
	}

	/**
	 * Returns URL for image from DIR_IMAGE folder
	 * @param string $path Absolute path to image
	 * @return string
	 */
	public function get_img_url( $path ) {
		$ret = '';

		// Confine to IMAGE_DIR only
		if ( strpos( $path, DIR_IMAGE ) === 0 ) {
			$ret = $this->u()->catalog_url( $ssl ) . substr( $path, strlen( dirname( DIR_IMAGE ) ) + 1 );
		}

		return $ret;
	}

	/**
	 * Returns route to a template file
	 * @param string $route Route
	 * @return string
	 */
	public function get_view_route( $route ) {
		if ( version_compare( VERSION, '2.2.0.0', '>=' ) ) {
			$template_file = $route;

		} else {
			$ending = '.tpl' === substr( $route, -4 );

			if ( ! $ending ) {
				$route .= '.tpl';
			}

			if ( defined( 'DIR_CATALOG' ) ) {
				return $route;
			}

			if ( file_exists( DIR_TEMPLATE . $this->config->get( 'config_template' ) . '/template/' . $route ) ) {
				$template_file = $this->config->get( 'config_template' ) . '/template/' . $route;

			} else {
				$template_file = 'default/template/' . $route;
			}
		}

		return $template_file;
	}

	/**
	 * get_view_route alias
	 * @param string $route Route
	 * @return string
	 */
	public function get_template( $route ) {
		return $this->get_view_route( $route );
	}

	/**
	 * Sorts array by some value
	 * @param array $array Target array
	 * @param string $name Value name
	 * @return array Output array
	 */
	public function sort_by( &$array, $name, $sort = 'numeric', $order = 'asc' ) {
		$self = $this;

		usort( $array, function ( $a, $b ) use ( $name, $sort, $order, $self ) {
			$ret = 0;

			if ( 'numeric' === $sort ) {
				$ret = $this->sort_num( $a, $b, $name, $order );

			} elseif ( 'alpha' === $sort ) {
				$ret = $this->sort_alpha( $a, $b, $name, $order );
			}

			return $ret;
		} );
	}

	/**
	 * Implements sort algorithm for numeric sort
	 * @param array $a First item
	 * @param array $b Second item
	 * @param string $name Field to sort with
	 * @param string $order Sort order
	 * @return int
	 */
	protected function sort_num( $a, $b, $name, $order = 'asc' ) {
		$res = 0;

		if ( isset( $a['name'] ) && isset( $b['name'] ) ) {
			$res = $a['name'] - $b['name'];

			if ( 'asc' !== $order ) {
				$res *= -1;
			}
		}

		return $res;
	}

	/**
	 * Implements sort algorithm for numeric sort
	 * @param array $a First item
	 * @param array $b Second item
	 * @param string $name Field to sort with
	 * @param string $order Sort order
	 * @return int
	 */
	protected function sort_alpha( $a, $b, $name, $order = 'asc' ) {
		$res = 0;

		if ( isset( $a['name'] ) && isset( $b['name'] ) ) {
			$res = strcmp( $a['name'], $b['name'] );

			if ( 'asc' !== $order ) {
				$res *= -1;
			}
		}

		return $res;
	}

	/**
	 * Returns current store URL
	 * @return string
	 */
	public function get_store_href( $ssl = null ) {
		return $this->u()->catalog_url( $ssl );
	}

	/**
	 * Returns URL for current store
	 * @return string
	 */
	public function get_store_url( $ssl = null ) {
		return $this->get_store_href( $ssl );
	}

	/**
	 * Returns target customer, if possible
	 * Logic is following
	 *   1. If front-end and customer is logged in - use it
	 *   2. If guest session in opened - use guest customer
	 * @return array|null
	 */
	public function get_customer() {
		$customer = null;

		if( $this->customer && $this->customer->isLogged() ) {
			$customer = $this->get_customer_by_email( $this->customer->getEmail() );

		} elseif ( isset( $this->session->data['guest'] ) ) {
			$customer = $this->session->data['guest'];

		}

		return $customer;
	}

	/**
	 * Returns products list for specific order
	 * @param int $order_id Order ID
	 * @return array
	 */
	public function get_order_products( $order_id ) {

		$order_info = $this->get_order_info( $order_id );

		if( isset( $order_info['language_id'] ) ) {
			$language_id = $order_info['language_id'];

		} else {
			$language_id = $this->config->get( 'config_language_id' );
		}

		if( ! $this->has_in_cache( 'order_products/' . $order_id ) ) {
			$query = $this->db->query(
				"SELECT * FROM `" . DB_PREFIX . "order_product` `op`
					LEFT JOIN `" . DB_PREFIX . "product` `p`
						USING(`product_id`)
					LEFT JOIN `" . DB_PREFIX . "product_description` `pd`
						ON(`op`.`product_id` = `pd`.`product_id` AND `pd`.`language_id` = " . (int)$language_id . ")
				WHERE `op`.`order_id` = " . (int)$order_id
			);

			$this->add_to_cache( 'order_products/' . $order_id, $query->rows );

			return $query->rows;
		}

		return $this->get_from_cache( 'order_products/' . $order_id );
	}

	/**
	 * Returns products list. Result of this function is not cached
	 * @param int $order_id Product ID
	 * @return array
	 */
	public function get_products_by_id( $product_id ) {
		if ( ! $product_id ) {
			return array();
		}

		$query = $this->db->query(
			"SELECT * FROM `" . DB_PREFIX . "product` `p`
			WHERE `p`.`product_id` IN (" . implode( ')(', (array)$product_id ) . ")"
		);

		return $query->rows;
	}

	/**
	 * Returns vouchers pertain to an order
	 * @param int $order_id Order ID
	 * @return string
	 */
	public function get_order_vouchers( $order_id ) {

		if( ! $this->has_in_cache( 'order_vouchers/' . $order_id ) ) {
			$query = $this->db->query(
				"SELECT * FROM `" . DB_PREFIX . "order_voucher`
				WHERE `order_id` = " . (int)$order_id 
			);
			
			$this->add_to_cache( 'order_vouchers/' . $order_id, $query->rows );

			return $query->rows;
		}

		return $this->get_from_cache( 'order_vouchers/' . $order_id );
	}

	/**
	 * Returns an order totals list
	 * @param int $order_id Order ID
	 * @return array
	 */
	public function get_order_totals( $order_id ) {

		if( ! $this->has_in_cache( 'order_totals/' . $order_id ) ) {
			$totals = array();

			$query = $this->db->query(
				"SELECT * FROM `" . DB_PREFIX . "order_total`
				WHERE `order_id` = " . (int)$order_id . "
				ORDER BY `sort_order` ASC"
			);

			if( $query->num_rows > 0 ) {
				foreach( $query->rows as $row ) {
					$totals[ $row['code'] ] = $row;
				}
			}
			
			$this->add_to_cache( 'order_totals/' . $order_id, $totals );

			return $totals;
		}

		return $this->get_from_cache( 'order_totals/' . $order_id );
	}

	/**
	 * Returns order status name by its ID
	 * @param int $order_status_id Order status ID
	 * @param string|null $language_id Language code, optional
	 * @return string
	 */
	public function get_order_status_name( $order_status_id, $language_id = null ) {

		if( is_null( $language_id ) ) {
			$language_id = $this->config->get( 'config_language_id' );
		}

		if( ! $this->has_in_cache( 'order_statuses/' . $language_id . '/' . $order_status_id ) ) {
			$query = $this->db->query(
				"SELECT * FROM `" . DB_PREFIX . "order_status`
				WHERE `language_id` = " . (int)$language_id
			);

			$ret = array();
			foreach( $query->rows as $row ) {
				$ret[ $row['order_status_id'] ] = $row['name'];
			}

			$this->add_to_cache( 'order_statuses/' . $language_id, $ret );
			
			if( isset( $ret[ $order_status_id ] ) ) {
				return $ret[ $order_status_id ];
			}

			return '';
		}

		return $this->get_from_cache( 'order_statuses/' . $language_id . '/' . $order_status_id );
	}

	/**
	 * Returns download-able products for specific order, if present
	 * @param int $order_id Order ID
	 * @return array
	 */
	public function get_order_downloaded_products( $order_id ) {

		if( ! $this->has_in_cache( 'downloaded_products/' . $order_id ) ) {
			$query = $this->db->query(
				"SELECT * FROM `" . DB_PREFIX . "order_product` `op`
					LEFT JOIN `" . DB_PREFIX . "product_to_download` `pd`
						USING(`product_id`)
					WHERE `op`.`order_id` = " . (int)$order_id . "
						AND `pd`.`download_id` IS NOT NULL"
			);
			
			$this->add_to_cache( 'downloaded_products/' . $order_id, $query->rows );

			return $query->rows;
		}

		return $this->get_from_cache( 'downloaded_products/' . $order_id );
	}

	/**
	 * Returns order information by its ID
	 * @param int $order_id Order ID
	 * @return object|null Advertikon\Db_Result
	 */
	public function get_order_info( $order_id ) {
		if( ! $this->has_in_cache( 'orders/' . $order_id ) ) {
			$query = $this->q( array(
				'table' => 'order',
				'query' => 'select',
				'where' => array(
					'operation' => '=',
					'field'     => 'order_id',
					'value'     => $order_id,
				),
			) );
			
			$this->add_to_cache( 'orders/' . $order_id, $query );

			return $query;
		}

		return $this->get_from_cache( 'orders/' . $order_id );
	}

	/**
	 * Returns customer group info by its ID
	 * @param int $group_id Customer group ID
	 * @return array
	 */
	public function get_customer_group_info( $group_id ) {

		if( ! $this->has_in_cache( 'customer_groups/' . $group_id ) ) {
			$query = $this->db->query(
				"SELECT * FROM `" . DB_PREFIX . "customer_group` `cg`
					LEFT JOIN `" . DB_PREFIX . "customer_group_description` `cgd`
						USING(`customer_group_id`)
				WHERE `cg`.`customer_group_id` = " . (int)$group_id . "
					AND `cgd`.`language_id` = " . (int)$this->config->get( 'config_language_id' )
			);
			
			$this->add_to_cache( 'customer_groups/' . $group_id, $query->row );

			return $query->row;
		}

		return $this->get_from_cache( 'customer_groups/' . $group_id );
	}

	/**
	 * Returns product details
	 * @param int $product_id Product iD
	 * @return array
	 */
	public function get_product_info( $product_id ) {

		if( !  $this->has_in_cache( 'products/' . $product_id ) ) {
			$query = $this->db->query(
				"SELECT * FROM `" . DB_PREFIX . "product` `p`
					LEFT JOIN `" . DB_PREFIX . "product_description` `pd`
						USING(`product_id`)
				WHERE `p`.`product_id` = " . (int)$product_id . "
					AND `pd`.`language_id` = " . (int)$this->config->get( 'config_language_id' )
			);

			$this->add_to_cache( 'products/' . $product_id, $query->row );

			return $query->row;
		}

		return $this->get_from_cache( 'products/' . $product_id );
	}

	/**
	 * Returns region information
	 * @param int $region_id Region ID
	 * @return array
	 */
	public function get_region_info( $region_id ) {
		
		if( ! $this->has_in_cache( 'regions/' . $region_id ) ) {
			$query = $this->db->query(
				"SELECT
					`c`.`country_id`,
					`c`.`name` as `country_name`,
					`c`.`iso_code_2` as `country_iso`,
					`z`.`name` as `zone_name`,
					`z`.`code` as `zone_code`
				FROM `" .DB_PREFIX . "zone` `z`
					LEFT JOIN `" . DB_PREFIX . "country` `c`
						USING(`country_id`)
				WHERE `z`.`zone_id` = " . (int)$region_id
			);

			$this->add_to_cache( 'regions/' . $region_id, $query->row );

			return $query->row;
		}

		return $this->get_from_cache( 'regions/' . $region_id );
	}

	/**
	 * Returns voucher information
	 * @param int $voucher_id Voucher ID
	 * @return array
	 */
	public function get_voucher( $voucher_id ) {
 
		if( ! $this->has_in_cache( 'vouchers/' . $voucher_id ) ) {
			$query = $this->db->query(
				"SELECT * FROM `" . DB_PREFIX . "voucher` `v`
					LEFT JOIN `" . DB_PREFIX . "voucher_theme`
						USING(`voucher_theme_id`)
				WHERE `voucher_id` = " . (int)$voucher_id
			);

			$this->add_to_cache( 'vouchers/' . $voucher_id, $query->row );

			return $query->row;
		}

		return $this->get_from_cache( 'vouchers/' . $voucher_id );
	}

	/**
	 * Returns customer by its email. Result of this function is not cached
	 * @param string $email Email address
	 * @return array
	 */
	public function get_customer_by_email( $email ) {
		$query = $this->db->query(
			"SELECT DISTINCT * FROM `" . DB_PREFIX . "customer`
			WHERE LCASE(`email`) = '" . $this->db->escape( utf8_strtolower( $email ) ) . "'"
		);

		return $query->row;
	}

	/**
	 * Slices the string eg slice( 1,3 ): |0|1|2|3|4|5|6| => |0|4|5|6|
	 * @param string $str Sting to be sliced 
	 * @param int $start Start position (offset) (excluded from the resulting string)
	 * @param int $end End position (offset) (excluded from the resulting string)
	 * @return string
	 */
	public function str_slice( $str, $start = null, $end = null ) {
		$len = strlen( $str );

		// Fix start position
		if ( is_null( $start ) ) {
			$start = 0;

		} elseif ( $start < 0 ) {
			$start = $len + $start;
		}

		if ( $start < 0 ) {
			$start = 0;

		} elseif ( $start >= $len ) {
			$start = $len - 1;
		}

		// Fix end position
		if ( is_null( $end ) ) {
			$end = $len - 1;

		} elseif ( $end < 0 ) {
			$end = abs( $end );
		}

		if ( $end < 0 ) {
			$end = 0;

		} elseif ( $end >= $len ) {
			$end = $len - 1;
		}

		if ( $start > $end ) {
			$start = $end;
		}
		$before = substr( $str, 0, $start );
		$after = substr( $str, $end + 1 );

		return $before . $after;
	}

	/**
	 * Returns file upload errors by code
	 * @param int $code Error code
	 * @return string
	 */
	public function get_file_upload_error( $code ) {
		$ret = '';

		switch( $code ) {
			case UPLOAD_ERR_INI_SIZE:
				$ret = $this->__( 'Exceeded file size limit of %s bytes', ini_get( 'upload_max_filesize' ) );
				break;
			case UPLOAD_ERR_FORM_SIZE :
				$ret = $this->__( 'Exceeded file size limit (HTML form restriction)' );
				break;
			case UPLOAD_ERR_PARTIAL :
				$ret = $this->__( 'File has been uploaded partially' );
				break;
			case UPLOAD_ERR_NO_FILE :
				$ret = $this->__( 'File has not been uploaded' );
				break;
			case UPLOAD_ERR_NO_TMP_DIR :
				$ret = $this->__( 'Upload temporary folder is missing' );
				break;
			case UPLOAD_ERR_CANT_WRITE :
				$ret = $this->__( 'Unable to write file into disk' );
				break;
			case UPLOAD_ERR_EXTENSION :
				$ret = $this->__( 'Stopped by PHP extension' );
				break;
		}

		return $ret;
	}

	/**
	 * Formats bytes into kB. MB etc
	 * @param int $size Bytes
	 * @return string
	 */
	public function format_bytes( $size ) {
		$points = 2;
		$pow = null;
		$value = (float)$size;
		$units = array( "B", "kB", "MB", "GB" );

		if ( $value <= 0 ) {
			$value = 0;
			$pow = 0;

		} else {
			$pow = floor( log10( $value ) / 3 );
		}

		if( $pow > 0 ) {
			$value /= pow( 10, $pow * 3 );
		}

		return round( $value, $points ) . " " . $units[ $pow ];
	}

	/**
	 * Prepare JSON response
	 * @param Mixed $data Data to be sent
	 */
	public function json_response( $data = null ) {
		$json = json_encode( $data );

		if ( json_last_error() ) {
			$this->response->addHeader( 'HTTP/1.0 500 Internal Server Error', 1, 500 );
			$this->log( 'Error while encoding JSON data for response:',$data, $this->log_err_flag  );

		} else {
			$this->response->addHeader( 'Content-Type: application/json' );
			$this->response->setOutput( $json );
		}
	}

	/**
	 * Wrapper for OpnCart logger function
	 * @return void
	 */
	public function log() {
		if ( ! $this->console->is_log() && self::LOGS_DISABLE === $this->log_strictness ) {
			return;
		}

		$args = func_get_args();
		$strictness = self::LOGS_MSG;
		$i = 0;

		foreach( $args as $a ) {
			if ( is_a( $a, '\Advertikon\Log_Error' ) ) {
				$strictness = self::LOGS_ERR;
				unset( $args[ $i ] );

			} elseif ( is_a( $a, '\Advertikon\Log_Debug' ) ) {
				$strictness = self::LOGS_DEBUG;
				unset( $args[ $i ] );
			}

			$i++;
		}

		if ( ! $this->console->is_log() && $this->log_strictness < $strictness ) {
			return;
		}

		foreach( $args as $a ) {
			if ( is_callable( $a ) ) {
				$a = $a();
			}

			$this->console->log( $a );

			if ( $this->log_strictness >= $strictness ) {
				$this->log->write( $a );
			}
		}
	}

	/**
	 * Adds value to one-time cache
	 * @param string $name Value name
	 * @param mixed $value Value 
	 * @return void
	 */
	public function add_to_cache( $name, $value ) {
		$this->create_array_structure( self::$one_time_cache, $name );
		$pointer =& self::$one_time_cache;

		foreach( explode( '/', $name ) as $part ) {
			$pointer = &$pointer[ $part ];
		}

		$pointer = $value;
	}

	/**
	 * Checks if value exists in one-time cache
	 * @param string $name Value name
	 * @return boolean
	 */
	public function has_in_cache( $name ) {
		$pointer = self::$one_time_cache;

		foreach( explode( '/', $name ) as $part ) {
			if( ! isset( $pointer[ $part ] ) ) {
				return false;
			}

			$pointer = $pointer[ $part ];
		}

		return true;
	}

	/**
	 * Fetches value form one-time cache
	 * @param string $name Value name
	 * @return mixed
	 */
	public function get_from_cache( $name ) {
		$pointer = self::$one_time_cache;

		foreach( explode( '/', $name ) as $part ) {
			if( ! isset( $pointer[ $part ] ) ) {
				return null;
			}

			$pointer = $pointer[ $part ];
		}

		return $pointer;
	}

	/**
	 * Convert string from camelCase to underscore_notation
	 * @param string $name
	 * @return string
	 */
	public function underscore( $name ) {
		return strtolower( preg_replace( '#(.)([A-Z])#', '$1_$2', $name ) );
	}

	/**
	 * Camel-case name
	 * @param string $name
	 * @return string
	 */
	public function camelcase( $name ) {
		$names = explode( '_', $name );
		$c_name = '';

		foreach( $names as $part ) {
			$c_name .= ucfirst( $part );
		}

		return lcfirst( $c_name );
	}

	/**
	 * Check whether user has specific permission
	 * @param string $permission
	 * @return boolean
	 */
	public function has_permissions( $permission_name ) {
		if ( defined( 'DIR_CATALOG' ) ) {
			return $this->user->hasPermission( $permission_name, $this->full_name );
		}

		return false;
	}

	/**
	 * Check element for empty
	 * @param mixed $elem
	 * @param boolean $rec Flag as to perform recursive check
	 * @return boolean
	 */
	public function is_empty( $elem, $rec = true ) {
		$empty = true;

		try {

			// Object or array
			if ( is_object( $elem ) || is_array( $elem ) ) {
				foreach( $elem as $v ) {
					$empty = false;

					if ( $rec && ( is_object( $v ) || is_array( $v ) ) ) {
						$empty = true;

						if ( ! $this->is_empty( $v, $rec ) ) {
							$empty = false;
						} 
					}

					if ( ! $empty ) {
						throw new Exception( 'not empty' );
					}
				}

			} else {
				if( ! empty( $elem ) ) {
					throw new Exception( 'not empty' );
				}
			}

		} catch ( Exception $e ) {
			$empty = false;
		}

		return $empty;
	}

	/**
	 * Obscure part of a string
	 * @param string $str
	 * @param integer $part
	 * @param string $obscureChar
	 * @param boolean $obscureSpace
	 * @return string
	 */
	public function obscure_str( $str, $part = 75, $char = '*', $obscure_space = false ) {

		if ( ! is_string( $str ) ) {
			return '';
		}

		if ( is_null( $part ) ) {
			$part = 75;
		}

		if ( is_null( $char ) ) {
			$char = '*';
		}

		$part = is_int( $part ) ? min( (int)$part, 100 ) : 100;
		$len = ceil( strlen( $str ) * ( $part / 100 ) );

		for( $i = 0; $i < $len ; $i++ ) {
			if ( $obscure_space || $str[ $i ] !== self::CHAR_SPACE ) {
				$str[ $i ] = $char;
			}
		}

		return $str;
	}

	/**
	 * Decoding JSON string
	 * @param String $str String to be evaluated
	 * @return Mixed
	 * @throws Advertikon\Exception on evaluation error
	 */
	public function json_decode( $str ) {
		$json = json_decode( $str, false );

		if ( json_last_error() ) {
			$mess = $mess = $this->__( 'Failed to decode JSON string: %s', $this->get_json_error() );
			trigger_error( $mess );
			throw new Exception( $mess );
		}

		return $json;
	}

	/**
	 * Encodes value to JSON representation
	 * @param mixed $value Value to be encoded
	 * @return string
	 * @throws Advertikon\Exception on error
	 */
	public function json_encode( $value ) {
		$ret = json_encode( $value, JSON_HEX_QUOT | JSON_HEX_APOS );

		if ( false === $ret ) {
			$mess = $mess = $this->__( 'Failed to encode value to JSON string: %s', $this->get_json_error() );
			trigger_error( $mess );
			throw new Exception( $mess );
		}

		return $ret;
	}

	/**
	 * Returns last JSON error
	 * @return string|int
	 */
	public function get_json_error() {
		$mess = '';

		if ( function_exists( 'json_last_error_msg' ) ) {
			$mess = json_last_error_msg();

		} else {
			 $mess = json_last_error();
		}

		return $mess;
	}

	/**
	 * Adds value to custom field of OpenCarts' DB table
	 * @param int|array $order Order ID or order itself
	 * @param string $name Value name, not used when field_val is supplied
	 * @param mixed $value Value, not used when field_val is supplied
	 * @param string $field Custom field name
	 * @param mixed $field_val Optional field value
	 * @throws Advertikon\Exception on operation failure
	 * @return void
	 */
	public function add_custom_field( $order, $name, $value, $field = null, $field_val = null ) {
		if ( is_null( $field ) ) {
			$field = 'payment';
		}

		if ( is_null( $field_val ) ) {
			$field_val = $this->get_custom_field( $order, $field );

			if ( is_object( $field_val ) ) {
				$field_val->{$name} = $value;
				
			} elseif ( is_array( $field_val ) ) {
				$field_val[ $name ] = $value;

			// Initial serialized value
			} elseif ( 0 == $field_val ) {
				$field_val = array();

			} else {
				$mess = $this->__(
					'Failed to add data to custom field of order #%s: custom field is not array nor object',
					is_array( $order ) ? $order['order_id'] : $order
				);

				trigger_error( $mess );
				throw new Exception( $mess );
			}
		}

		if ( version_compare( VERSION, '2.1.0.1', '>=' ) ) {
			$serialized = $this->json_encode( $field_val );

		} else {
			$serialized = serialize( $field_val );

			if ( ! $serialized ) {
				throw new Exception( $this->__( 'Failed to add data to custom field' ) );
			}
		}

		$field = ( $field ? $field . '_' : '' ) . 'custom_field';

		$this->q( array(
			'table' => 'order',
			'query' => 'update',
			'set'   => array(
				$field => $serialized,
			),
			'where' => array(
				'field'     => 'order_id',
				'operation' => '=',
				'value'     => is_array( $order ) ? $order['order_id'] : $order,
			)
		) );
	}

	/**
	 * Serializes custom field data
	 * @param int|array $order Order ID or order itself
	 * @param string $field Field name, optional
	 * @throws Advertikon\Exception on operation error
	 * @return mixed
	 */
	public function get_custom_field( $order_id, $field = null ) {
		if ( is_null( $field ) ) {
			$field = 'payment';
		}

		$field = ( $field ? $field . '_' : '' ) . 'custom_field';

		if ( ! is_array( $order_id ) ) {
			$order = $this->q( array(
				'table'  => 'order',
				'query'  => 'select',
				'where'  => array(
					'field'     => 'order_id',
					'operation' => '=',
					'value'     => $order_id,
				)
			) );

			if ( ! count( $order ) ) {
				$mess = $this->__(
					'Failed to fetch custom field\'s data for order #%s - Order is missing',
					$order_id
				);

				trigger_error( $mess );
				throw new Exception( $mess );
			}

		} else {
			$order = $order_id;
		}

		if ( is_string( $order[ $field ] ) ) {
			if ( version_compare( VERSION, '2.1.0.1', '>=' ) ) {
				$ret = $this->json_decode( $order[ $field ] );

			} else {
				$ret = unserialize( $order[ $field ] );
			}

		} else {
			$ret = $order[ $field ];
		}


		return $ret;
	}

	/**
	 * Send email message
	 * @param String $to Recipient's address
	 * @param String $subject Email subject
	 * @param String $message Message body
	 * @param Boolean $singleton SIngleton flag.
	 * Optional, default value - true. If true will reuse existing object.
	 */
	public function mail( $to, $subject, $message, $singleton = true ) {
		if ( ! $this->mail_instance || ! $singleton ) {
			$this->mail_instance = new \Mail;

			$mail->protocol = $this->config->get( 'config_mail_protocol' );
			$mail->parameter = $this->config->get( 'config_mail_parameter' );
			$mail->smtp_hostname = $this->config->get( 'config_mail_smtp_hostname' );
			$mail->smtp_username = $this->config->get( 'config_mail_smtp_username' );
			$mail->smtp_password = html_entity_decode($this->config->get( 'config_mail_smtp_password' ), ENT_QUOTES, 'UTF-8' );
			$mail->smtp_port = $this->config->get( 'config_mail_smtp_port' );
			$mail->smtp_timeout = $this->config->get( 'config_mail_smtp_timeout' );
			$this->mail_init = 1;
		}

		$mail = $this->mail_instance;

		$mail->setTo( $to );
		$mail->setFrom( $this->config->get('config_email') );
		$mail->setSender( html_entity_decode( $this->config->get( 'config_name' ), ENT_QUOTES, 'UTF-8') );
		$mail->setSubject( html_entity_decode( $subject, ENT_QUOTES, 'UTF-8' ) );
		$mail->setHtml( $message );
		$mail->send();

		return true;
	}

	/**
	 * Returns total product details
	 * @param int $product_id 
	 * @return array
	 */
	public function get_product( $product_id ) {
		$product_id = (int)$product_id;

		if( ! $this->has_in_cache( 'product/' . $product_id ) ) {
			$product = array();

			if( $this->customer && $this->customer->isLogged() ) {
				$customer_group_id = $this->customer->getGroupId();

			} else {
				$customer_group_id = $this->config->get( 'config_customer_group_id' );
			}

			$customer_group_id = (int)$customer_group_id;

			// Min query
			$product_query = $this->db->query(
				"SELECT
					*,
					( SELECT `price`
						FROM `" . DB_PREFIX . "product_special`
						WHERE `product_id` = '$product_id'
							AND `customer_group_id` = '$customer_group_id'
							AND ( `date_start` = '000-00-00' OR `date_start` <= NOW() )
							AND ( `date_end` = '0000-00-00' OR `date_end` > NOW())
						ORDER BY `priority` DESC LIMIT 1 ) as special
				FROM `" . DB_PREFIX . "product` `p`
					LEFT JOIN `" . DB_PREFIX . "product_description` `pd`
						USING(`product_id`)
				WHERE `p`.`product_id` = " . (int)$product_id . "
					AND `pd`.`language_id` = " . (int)$this->config->get( 'config_language_id')
			);

			if( $product_query && $product_query->num_rows > 0 ) {
				$product = $product_query->row;

				// Discount query
				$discount_query = $this->db->query(
					"SELECT `price` FROM `" . DB_PREFIX . "product_discount`
					WHERE `product_id` = '$product_id'
						AND `customer_group_id` = '$customer_group_id'
						AND `quantity` <= " . $product['minimum'] . "
						AND ( `date_start` <= NOW() OR `date_start` = '0000-00-00' )
						AND ( `date_end` > NOW() OR `date_end` = '0000-00-00' )
					ORDER BY `priority` DESC LIMIT 1"
				);

				if( $discount_query && $discount_query->num_rows > 0 ) {
					$product['discount'] = $discount_query->row['price'];
				}

				// Recurring query
				$recurring_query = $this->db->query(
					"SELECT `recurring_id` FROM `" . DB_PREFIX . "product_recurring`
					WHERE `product_id` = '$product_id'
						AND `customer_group_id` = '$customer_group_id' LIMIT 1"
				);

				if( $recurring_query && $recurring_query->num_rows > 0 ) {
					$product['recurring'] = $recurring_query->rows;
				}

				// Options query
				$options = array();
				$option_query = $this->db->query(
					"SELECT
						`po`.`option_id`,
						`po`.`value`,
						`po`.`required`,
						`pod`.`name`
					FROM `" . DB_PREFIX . "product_option` `po`
						LEFT JOIN `" . DB_PREFIX . "option_description` `pod`
							USING(`option_id`)
					WHERE `po`.`product_id` = '$product_id'
						AND `pod`.`language_id` = " . (int)$this->config->get( 'config_language_id' )
				);

				if( $option_query && $option_query->num_rows > 0 ) {
					$o = array();

					foreach( $option_query->rows as $option ) {

						$options[ $option['option_id'] ] = array(
							'value'    => ! empty( $option['value'] ) ? $option['value'] : array(),
							'required' => $option['required'],
							'name'     => $option['name'],
						);

						if( empty( $option['value'] ) ) {
							$o[] = $option['option_id'];
						}

						if( $o ) {
							$option_value_query = $this->db->query(
								"SELECT pov.*, `povd`.`name`
								FROM `" . DB_PREFIX . "product_option_value` `pov`
									LEFT JOIN `" . DB_PREFIX . "option_value_description` `povd`
										USING(`option_value_id`)
								WHERE `pov`.`option_id` IN (" . implode( ',', $o ) . ")
									AND `povd`.`language_id` = " . (int)$this->config->get( 'config_language_id' )
							);

							if( $option_value_query && $option_value_query->num_rows > 0 ) {
								foreach( $option_value_query->rows as $value ) {
									$options[ $value['option_id'] ] = $value;
								}
							}
						}
					}
				}

				$product['options'] = $options;

				// Download query
				$download = array();
				$download_query = $this->db->query(
					"SELECT * FROM `" . DB_PREFIX . "product_to_download` `p2d`
						LEFT JOIN `" . DB_PREFIX . "download` `d`
							USING(`download_id`)
						LEFT JOIN `" . DB_PREFIX . "download_description` `dd`
							USING(`download_id`)
					WHERE `p2d`.`product_id` = '$product_id'
						AND `dd`.`language_id` = " . (int)$this->config->get( 'confog_language_id' )
				);

				if( $download_query && $download_query->num_rows > 0 ) {
					$download = $download_query->rows;
				}

				$product['download'] = $download;

				// Reward query
				$reward = 0;
				$reward_query = $this->db->query(
					"SELECT * FROM `" . DB_PREFIX . "product_reward`
					WHERE `product_id` = '$product_id'
						AND `customer_group_id` = '$customer_group_id'"
				);

				if( $reward_query && $reward_query->num_rows > 0 ) {
					$reward = $reward_query->row['points'];
				}

				$product['reward'] = $reward;
			}

			$this->add_to_cache( 'product/' . $product_id, $product );

			return $product;
		}

		return $this->get_from_cache( 'product/' . $product_id );
	}

	/**
	 * Returns order model
	 * @return object
	 */
	public function get_order_model( $side = null ) {
		global $adk_registry;
		$model = null;

		if ( 'catalog' === $side ) {
			if ( defined( 'DIR_CATALOG' ) ) {
				require_once dirname( DIR_SYSTEM ) . '/catalog/model/checkout/order.php';
				$model = new \ModelCheckoutOrder( $adk_registry );

			} else {
				$this->load->model( 'checkout/order' );
				$model = $this->model_checkout_order;
			}

		} else {
			if ( defined( 'DIR_CATALOG' ) ) {
				$this->load->model( 'sale/order' );
				$model = $this->model_sale_order;

			} else {
				$this->load->model( 'checkout/order' );
				$model = $this->model_checkout_order;
			}
		}


		return $model;
	}

	/**
	 * Returns recurring order info model
	 * @return object
	 */
	public function get_recurring_info_model() {
		$model = null;

		if ( defined( 'DIR_CATALOG' ) ) {
			$this->load->model( 'sale/recurring' );
			$model = $this->model_sale_recurring;

		} else {
			$this->load->model( 'account/recurring' );
			$model = $this->model_account_recurring;
		}

		return $model;
	}

	/**
	 * Returns prepared array of sources to be downloaded from the server
	 * @param array $source Sources array
	 * @return array
	 */
	public function load( $source, $type = 'script' ) {
		$ret = array();
		
		if ( ! is_array( $source ) ) {
			$mess = sprintf( 'Source list need to be an array. "%s" got instead', gettype( $source ) );
			trigger_error( $mess );
			throw new Exception( $mess );
		}

		if ( ! $source ) {
			$mess = 'Source is an empty array';
			trigger_error( $mess );
			throw new Exception( $mess );
		}

		// No compression - return list of resources
		if ( $this->compression_level === self::COMPRESSION_LEVEL_NONE ) {
			if ( 'script' === $type ) {
				foreach( $source as $s ) {
					$ret[] = sprintf( '<script src="%s"></script>', $this->u()->parse( $s )->to_string() );
				}

			} elseif ( 'stylesheet' === $type ) {
				foreach( $source as $s ) {
					$ret[] = sprintf( '<link rel="stylesheet" href="%s">', $this->u()->parse( $s )->to_string() );
				}
			}

		// Compress sources
		} else {
			if ( 'script' === $type ) {
				$ret[] = sprintf(
					'<script src="%s"></script>',
					$this->get_compresses_src( $source, $type, $level = self::COMPRESSION_LEVEL_FULL )
				);	

			} elseif ( 'stylesheet' === $type ) {
				$ret[] = sprintf(
					'<link rel="stylesheet" href="%s">',
					$this->get_compresses_src( $source, $type, $level = self::COMPRESSION_LEVEL_FULL )
				);
			}
		}

		return implode( PHP_EOL, $ret );
	}

	/**
	 * Returns compressor action URL
	 * @param array $source List of source's URL to be compressed
	 * @param string $type Source's type
	 * @return string
	 */
	public function get_compresses_src( $source, $type = 'script', $level = self::COMPRESSION_LEVEL_FULL ) {
		if ( 'script' === $type ) {
			$t = 'js';

		} elseif ( 'stylesheet' === $type) {
			$t = 'css';

		} else {
			$mess = sprintf( '"%s" is unsupported type for compression' );
			trigger_error( $mess );
			throw new Exception( $mess );
		}

		return $this->u( 'compress', array( 'src' => implode( ',', $source ), 't' => $t, 'l' => $level ) );
	}

	/**
	 * Adds scripts to page header
	 * @param array $source List of script's URLs to be added
	 * @return void
	 */
	public function add_script( $source, $level = self::COMPRESSION_LEVEL_FULL ) {
		$this->document->addScript( $this->get_compresses_src( $source, 'script', $level ) );

	}

	/**
	 * Adds stylesheet to page header
	 * @param array $source List of script's URLs to be added
	 * @return void
	 */
	public function add_style( $source, $level = self::COMPRESSION_LEVEL_FULL ) {
		$this->document->addStyle( $this->get_compresses_src( $source, 'stylesheet', $level ) );

	}

	/**
	 * Compressor wrapper
	 * @return string
	 */
	public function compress() {
		$ret = '';
		$type = $this->request( 't' );
		$level = $this->request( 'l', self::COMPRESSION_LEVEL_FULL );

		if ( ! in_array( $type, array( 'css', 'js', ) ) ) {
			trigger_error( sprintf( 'Unsupported source type "%"', $type ) );

		} else {
			$minify = new Minify(
				$level | // 0 - none, 1 - concatenate sources into bundle, 3 - remove whitespaces, 15 - full
				(int)$this->compression_cache<<4 | // 0 - flush cache, 32 - serve cache if possible
				( (int)$this->compression_browser_cache * 3 )<<4 // 0 - make browser to reload source, 48 - use browser's cache
			);

			$data = $minify->get( $this->request( 'src', '' ), $type );

			if ( false === $data ) {
				trigger_error( 'Failed to compress resources: ' . $this->request( 'src' )  );

			} else {
				$ret = $data;
			}
		}

		return $ret;
	}

	/**
	 * Returns system shipping methods
	 * @return array
	 */
	public function get_shipping_methods( $active = true ) {
		$ext = array();

		if ( defined( 'DIR_CATALOG' ) ) {
			$this->load->model( 'extension/extension' );
			$extensions = $this->model_extension_extension->getInstalled('shipping');

			if( version_compare( VERSION, '2.3.0.0', '>=' ) ) {
				$route = 'extension/shipping';

			} else {
				$route = 'shipping';
			}
				
			$files = glob( DIR_APPLICATION . 'controller/' . $route . '/*.php' );

			if ($files) {
				foreach ($files as $file) {
					$extension = basename($file, '.php');

					if( $active && ( ! $this->config->get( $extension . '_status' ) ||
						! in_array( $extension, $extensions ) ) ) {
						continue;
					}

					$this->load->language( $route . '/' . $extension );

					$ext[] = array(
						'name'       => $this->language->get('heading_title'),
						'status'     => $this->config->get($extension . '_status'),
						'sort_order' => $this->config->get($extension . '_sort_order'),
						'installed'  => in_array($extension, $extensions),
						'code'       => $extension,
					);
				}
			}
		}

		return $ext;
	}

	/**
	 * Returns localized caption
	 * @param string $key Caption key
	 * @param string $lang_code Language code
	 * @param string $default Optional default value
	 * @return string
	 */
	public function get_lang_caption( $key, $lang_code = null, $default = '' ) {
		$ret = null;

		if ( '' === $key ) {
			return '';
		}

		$conf = $this->p( $key );

		if ( ! $conf ) {
			$conf = $this->config( $key );
		}

		if ( is_null( $lang_code ) ) {
			if ( isset( $this->session->data['language'] ) ) {
				$lang_code = $this->session->data['language'];

			} else {
				$lang_code = $this->config->get( 'config_admin_language' );
			}
		}

		if ( is_array( $conf ) ) {
			if ( isset( $conf[ $lang_code ] ) ) {
				$ret = $conf[ $lang_code ];

			} else {
				$def_lang_code = $this->config->get( 'config_admin_language' );

				if ( isset( $conf[ $def_lang_code ] ) ) {
					$ret = $conf[ $def_lang_code ];
				}
			}

		} else {
			$ret = $conf;
		}

		if ( is_null( $ret ) ) {
			$ret = $default;
		}

		return $ret;
	}

	/**
	 * Returns DB languages
	 * @return array
	 */
	public function get_languages() {
		if( ! $this->has_in_cache( 'languages' ) ) {
			$query = $this->db->query( "SELECT * FROM `" . DB_PREFIX . "language`" );

			$this->add_to_cache( 'languages', $query->rows );
			return $query->rows;
		}

		return $this->get_from_cache( 'languages' );
	}

	/**
	 * Returns customizable caption to show to user
	 * @param string $name Caption code 
	 * @return string
	 */
	public function caption( $name ) {
		return nl2br( $this->get_lang_caption( $name ) );
	}

	/**
	 * Check compatibility
	 * @return array
	 */
	public function check_compatibility() {
		return array();
	}

	/**
	 * Ticket button contents
	 * @return type
	 */
	public function ticket_button() {
		$ch = curl_init( self::TICKET_BUTTON_URL );
		$curl_data = array(
			'oc_version' => VERSION,
			'code'       => $this->code,
			'store'      => defined( 'HTTPS_CATALOG' ) ? HTTPS_CATALOG : HTTPS_SERVER,
			'version'    => self::get_version(),
		);

		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $curl_data );
		$res = curl_exec( $ch );

		if( curl_errno( $ch ) ) {
			$mess =  'CURL error: ' . curl_error( $ch );
			trigger_error( $mess );

			return $this->__( 'Failed to fetch support form: Network Error' );
		}

		curl_close( $ch );

		if ( ! $res ) {
			return 'Support functionality was blocked for Your installation. To enable support - email us support@advertikon.com.ua';
		}

		$name = $this->r()->render_form_group( array(
			'label' => $this->__( 'Name' ),
			'element' => array(
				'type'         => 'text',
				'class'        => 'form-control',
				'placeholder'  => $this->__( 'Tell how to address you' ),
				'name'         => 'support_name',
			),
			'cols'      => array( 'col-sm-2', 'col-sm-10', ),
		) );

		$email = $this->r()->render_form_group( array(
			'label' => $this->__( 'Email' ),
			'element' => array(
				'type'         => 'text',
				'class'        => 'form-control',
				'placeholder'  => $this->__( 'Where to send answer' ),
				'name'         => 'support_email',
				'value'        => $this->config->get( 'config_email' ),
			),
			'cols'      => array( 'col-sm-2', 'col-sm-10', ),
		) );

		$subject = $this->r()->render_form_group( array(
			'label' => $this->__( 'Your question' ),
			'element' => array(
				'type'         => 'textarea',
				'class'        => 'form-control',
				'name'         => 'support_subject',
			),
			'cols'      => array( 'col-sm-2', 'col-sm-10', ),
		) );

		$attachments = $this->r()->render_form_group( array(
			'label' => $this->__( 'Attachments' ),
			'element' => array(
				'type'        => 'file',
				'name'        => 'support_attachment[]',
				'custom_data' => 'multiple="multiple"'
			),
			'cols'      => array( 'col-sm-2', 'col-sm-10', ),
		) );

		$button = $this->r()->render_form_group( array(
			'label' => $this->__( ' ' ),
			'element' => array(
				'type'        => 'submit',
				'text_before' => $this->__( 'Send' ),
				'class'       => 'fa fa-send-o',
				'button_type' => 'primary',
			),
			'cols'      => array( 'col-sm-2', 'col-sm-10', ),
		) );

		$support_id = $this->r( array(
			'type'  => 'hidden',
			'value' => $res,
			'name'  => 'support_id',
		) );

		return <<<HTML
<form class="form-horizontal" method="post" enctype="multipart/form-data">
	<h3 style="text-align: center;">{$this->__( 'Get support' )}</h3>
	$name
	$email
	$subject
	$attachments
	$button
	$support_id
</form>
HTML;
	}

	/**
	 * Returns current version
	 * @return string
	 */
	static public function get_version() {
		$cont = file_get_contents( static::$file, 1000 );
		preg_match( '/@version\s+([0-9.]+)/m', $cont, $m );

		return isset( $m[1] ) ? $m[1] : '';
	}

	/**
	 * Initializes OC Mail object with store mail configuration
	 * @param object $mail Mail object
	 * @param array $data Additional parameters
	 * @return void
	 */
	public function init_mail( $mail, $data = array() ) {
		$mail->setSender( $this->config->get( 'config_email' ) );
		$mail->setFrom( $this->config->get( 'config_email' ) );

		if ( version_compare( VERSION, '2.0.1.1', '<=' ) ) {
			foreach( $this->config->get('config_mail') as $k => $v ) {
				$mail->{$k} = $v;
			}

		} else {
			$mail->protocol = $this->config->get( 'config_mail_protocol' );

			if( 'smtp' === $mail->protocol ) {
				$mail->smtp_hostname = $this->config->get( 'config_mail_smtp_hostname' );
				$mail->smtp_username = $this->config->get( 'config_mail_smtp_username' );
				$mail->smtp_password = html_entity_decode( $this->config->get( 'config_mail_smtp_password' ), ENT_QUOTES, 'UTF-8' );

				if( $this->config->has( 'config_mail_smtp_port' ) ) {
					$mail->smtp_port = $this->config->get( 'config_mail_smtp_port' );
				}

				if( $this->config->has( 'config_mail_smtp_timeout' ) ) {
					$mail->smtp_timeout = (float)$this->config->get( 'config_mail_smtp_timeout' );
				}

			} else {
				$mail->parameter = $this->config->get( 'config_mail_parameter' );
			}
		}

		if( $data ) {
			foreach( $data as $k => $v ) {
				$method = 'set' . ucfirst( $k );
				if( method_exists( $mail, $method ) ) {
					call_user_func( array( $mail, $method ), $v );
				}
			}
		}
	}

	/**
	 * Sends support request
	 * @param array &$errors Error structure
	 * @return boolean
	 */
	public function ask_support( &$errors ) {
		global $adk_mail_hook;

		$name = $this->post( 'support_name', '' );
		$email = $this->post( 'support_email' );
		$subject = $this->post( 'support_subject' );
		$id = $this->post( 'support_id' );

		try {
			if ( ! $email ) {
				throw new Exception( $this->__( 'Email address is mandatory' ) );
			}

			if ( ! $this->is_email( $email ) ) {
				throw new Exception( $this->__( 'Invalid format of email address' ) );
			}

			if ( ! $subject ) {
				throw new Exception( $this->__( 'Support subject is mandatory' ) );
			}

			$mail = new \Mail();
			$this->init_mail( $mail );
			$subject .= '<br>Installation ID - ' . $id;

			if ( $name ) {
				$subject .= '<br>Contact person: ' . $name;
			}

			$mail->setTo( 'support@advertikon.com.ua' );
			$mail->setSubject( 'Support request' );
			$mail->setFrom( $email );
			$mail->setHtml( $subject );
			$mail->setText( $subject );

			if( ! empty( $_FILES[ $this->prefix_name() . 'support_attachment' ]['tmp_name'] ) ) {
				foreach( $_FILES[ $this->prefix_name() . 'support_attachment' ]['tmp_name'] as $a ) {
					$mail->addAttachment( $a );
				}
			}

			// Adk_Mail support
			$adk_mail_hook = 'admin.support';
			$mail->send();

			$this->session->data['success'] = $this->__(
				'Support request has been successfully sent. We will get back to you shortly'
			);

			$this->response->redirect( $this->u()->url() );

		} catch ( \Exception $e ) {
			$errors['warning'][] = $e->getMessage();

			return false;
		}

		return true;
	} 

	/**
	 * Tests string whether it looks like email address
	 * @param string $email 
	 * @return boolean
	 */
	public function is_email( $email ) {
		return preg_match( '/^[A-Za-z0-9._+-]+@[A-Za-z0-9._-]+\.[A-Za-z]{2,4}$/', $email );
	}

	/**
	 * Parses exception's stack
	 * @param object $e Exception
	 * @return string
	 */
	public function trace_exception( $e ) {
		$stack = array();

		if ( is_subclass_of( $e, '\Exception' ) ) {
			$stack[] = 'Trace of exception: ' . $e->getMessage();

			foreach( $e->getTrace() as $n => $line ) {
				$stack[] = sprintf(
					'%n - %s %s %s',
					$n,
					isset( $line['function'] ) ? $line['function'] : '',
					isset( $line['file'] ) ? $line['file'] : '',
					isset( $line['line'] ) ? $line['line'] : ''
				);
			}
		}

		return implode( chr( 10 ), $stack );
	}

	/**
	 * Returns default PHP charset
	 * @return string
	 */
	public function get_default_charset() {
		$ini =  ini_get( 'default_charset' );

		return empty( $ini ) ? 'UTF-8' : $ini; 
	}
}
} //<-- Advertikon namespace end

namespace {
	function ADK( $code = null ) {
		return Advertikon\Advertikon::instance( $code );
	}
}
