<?php
/**
 * Advertikon Query Class
 * @author Advertikon
 * @package Advertikon
 * @version 2.8.11
 */

namespace Advertikon;

class Query {

	static public $functions = array(
		'ABS',
		'ACOS',
		'ADDDATE',
		'ADDTIME',
		'AES_DECRYPT',
		'AES_ENCRYPT',
		'ANY_VALUE',
		'ASCII',
		'ASIN',
		'ASYMMETRIC_DECRYPT',
		'ASYMMETRIC_DERIVE',
		'ASYMMETRIC_ENCRYPT',
		'ASYMMETRIC_SIGN',
		'ASYMMETRIC_VERIFY',
		'ATAN',
		'ATAN2',
		'AVG',
		'BENCHMARK',
		'BIN',
		'BIT_AND',
		'BIT_COUNT',
		'BIT_LENGTH',
		'BIT_OR',
		'BIT_XOR',
		'CAST',
		'CEIL',
		'CEILING',
		'Centroid',
		'CHAR',
		'CHAR_LENGTH',
		'CHARACTER_LENGTH',
		'CHARSET',
		'COALESCE',
		'COERCIBILITY',
		'COLLATION',
		'COMPRESS',
		'CONCAT',
		'CONCAT_WS',
		'CONNECTION_ID',
		'Contains',
		'CONV',
		'CONVERT',
		'CONVERT_TZ',
		'ConvexHull',
		'COS',
		'COT',
		'COUNT',
		'CRC32',
		'CREATE_ASYMMETRIC_PRIV_KEY',
		'CREATE_ASYMMETRIC_PUB_KEY',
		'CREATE_DH_PARAMETERS',
		'CREATE_DIGEST',
		'Crosses',
		'CURDATE',
		'CURRENT_DATE',
		'CURRENT_TIME',
		'CURRENT_TIMESTAMP',
		'CURRENT_USER',
		'CURTIME',
		'DATABASE',
		'DATE',
		'DATE_ADD',
		'DATE_FORMAT',
		'DATE_SUB',
		'DATEDIFF',
		'DAY',
		'DAYNAME',
		'DAYOFMONTH',
		'DAYOFWEEK',
		'DAYOFYEAR',
		'DECODE',
		'DEFAULT',
		'DEGREES',
		'DES_DECRYPT',
		'DES_ENCRYPT',
		'Dimension',
		'Disjoint',
		'Distance',
		'ELT',
		'ENCODE',
		'ENCRYPT',
		'EXP',
		'EXPORT_SET',
		'ExteriorRing',
		'EXTRACT',
		'ExtractValue',
		'FIELD',
		'FIND_IN_SET',
		'FLOOR',
		'FORMAT',
		'FOUND_ROWS',
		'FROM_BASE64',
		'FROM_DAYS',
		'FROM_UNIXTIME',
		'GET_FORMAT',
		'GET_LOCK',
		'GLength',
		'GREATEST',
		'GROUP_CONCAT',
		'GTID_SUBSET',
		'GTID_SUBTRACT',
		'HEX',
		'HOUR',
		'IF',
		'IFNULL',
		'IN',
		'INET_ATON',
		'INET_NTOA',
		'INET6_ATON',
		'INET6_NTOA',
		'INSTR',
		'INTERVAL',
		'IS_FREE_LOCK',
		'IS_IPV4',
		'IS_IPV4_COMPAT',
		'IS_IPV4_MAPPED',
		'IS_IPV6',
		'IS_USED_LOCK',
		'IsClosed',
		'IsEmpty',
		'ISNULL',
		'IsSimple',
		'JSON_APPEND',
		'JSON_ARRAY',
		'JSON_ARRAY_APPEND',
		'JSON_ARRAY_INSERT',
		'JSON_CONTAINS',
		'JSON_CONTAINS_PATH',
		'JSON_DEPTH',
		'JSON_EXTRACT',
		'JSON_INSERT',
		'JSON_KEYS',
		'JSON_LENGTH',
		'JSON_MERGE',
		'JSON_OBJECT',
		'JSON_QUOTE',
		'JSON_REMOVE',
		'JSON_REPLACE',
		'JSON_SEARCH',
		'JSON_SET',
		'JSON_TYPE',
		'JSON_UNQUOTE',
		'JSON_VALID',
		'LAST_INSERT_ID',
		'LCASE',
		'LEAST',
		'LEFT',
		'LENGTH',
		'LineFromText',
		'LineFromWKB',
		'LineString',
		'LN',
		'LOAD_FILE',
		'LOCALTIME',
		'LOCALTIMESTAMP',
		'LOCATE',
		'LOG',
		'LOG10',
		'LOG2',
		'LOWER',
		'LPAD',
		'LTRIM',
		'MAKE_SET',
		'MAKEDATE',
		'MAKETIME',
		'MASTER_POS_WAIT',
		'MAX',
		'MBRContains',
		'MBRCoveredBy',
		'MBRCovers',
		'MBRDisjoint',
		'MBREqual',
		'MBREquals',
		'MBRIntersects',
		'MBROverlaps',
		'MBRTouches',
		'MBRWithin',
		'MD5',
		'MICROSECOND',
		'MID',
		'MIN',
		'MINUTE',
		'MLineFromText',
		'MLineFromWKB',
		'MOD',
		'MONTH',
		'MONTHNAME',
		'NAME_CONST',
		'NOW',
		'NULLIF',
		'NumPoints',
		'OCT',
		'OCTET_LENGTH',
		'OLD_PASSWORD',
		'ORD',
		'Overlaps',
		'PASSWORD',
		'PERIOD_ADD',
		'PERIOD_DIFF',
		'PI',
		'POSITION',
		'POW',
		'POWER',
		'PROCEDURE ANALYSE',
		'QUARTER',
		'QUOTE',
		'RADIANS',
		'RAND',
		'RANDOM_BYTES',
		'RELEASE_ALL_LOCKS',
		'RELEASE_LOCK',
		'REPEAT',
		'REPLACE',
		'REVERSE',
		'RIGHT',
		'ROUND',
		'ROW_COUNT',
		'RPAD',
		'RTRIM',
		'SCHEMA',
		'SEC_TO_TIME',
		'SECOND',
		'SESSION_USER',
		'SHA1',
		'SHA2',
		'SIGN',
		'SIN',
		'SLEEP',
		'SOUNDEX',
		'SPACE',
		'SQRT',
		'SRID',
		'STD',
		'STDDEV',
		'STDDEV_POP',
		'STDDEV_SAMP',
		'STR_TO_DATE',
		'STRCMP',
		'SUBDATE',
		'SUBSTR',
		'SUBSTRING',
		'SUBSTRING_INDEX',
		'SUBTIME',
		'SUM',
		'SYSDATE',
		'SYSTEM_USER',
		'TAN',
		'TIME',
		'TIME_FORMAT',
		'TIME_TO_SEC',
		'TIMEDIFF',
		'TIMESTAMP',
		'TIMESTAMPADD',
		'TIMESTAMPDIFF',
		'TO_BASE64',
		'TO_DAYS',
		'TO_SECONDS',
		'Touches',
		'TRIM',
		'TRUNCATE',
		'UCASE',
		'UNCOMPRESS',
		'UNCOMPRESSED_LENGTH',
		'UNHEX',
		'UNIX_TIMESTAMP',
		'UpdateXML',
		'UPPER',
		'USER',
		'UTC_DATE',
		'UTC_TIME',
		'UTC_TIMESTAMP',
		'UUID',
		'UUID_SHORT',
		'VALIDATE_PASSWORD_STRENGTH',
		'VALUES',
		'VAR_POP',
		'VAR_SAMP',
		'VARIANCE',
		'VERSION',
		'WAIT_FOR_EXECUTED_GTID_SET',
		'WAIT_UNTIL_SQL_THREAD_AFTER_GTIDS',
		'WEEK',
		'WEEKDAY',
		'WEEKOFYEAR',
		'WEIGHT_STRING',
		'YEAR',
		'YEARWEEK',
	);

	/**
	 * Creates query string from supplied data
	 *	- query        - string       - query type
	 * 	- delete_from  - string       - table name to be deleted from when JOIN is used
	 * 	- distinct     - boolean      - flag to fetch distinct fields
	 * 	- table        - array|string - tables to process on (alias => name)
	 * 	- join         - array        - JOIN statement ( type (left, right, join), table, alias, using || on ( operation, left, right ) )
	 * 	- count        - array|string - fields to be count or *. Obsolete!!!!
	 * 	- calc         - boolean      - flag whether to calculate rows
	 * 	- fields       - array|string - fields to select (alias => name)
	 * 	- function     - array|string - list to functions to select (function, arguments, alias). Obsolete!!!!!
	 * 	- values       - array        - values to insert/update (name => array|string)
	 * 	- on_duplicate - array        - values to be update on key duplication ( name => value )
	 * 	- where        - array        - where clause (operation,field,value)
	 * 	- order_by     - array|string - ordering option (order field => order direction)
	 * 	- limit        - int          - query result limit
	 * 	- start        - int          - query start offset
	 * 	- where_glue   - string       - One of OR|AND 
	 * 	- group_by     - string       - DROUP BY clause
	 * @since 1.1.0
	 * @param array $data Query data
	 * @return string
	 * @throws Advertikon\Exception on error
	 */
	public function create_query( $data ) {
		if ( function_exists( 'is_log_query' ) && is_log_query() && function_exists( 'console_log' ) ) {
			console_log( $data ); 
		}

		$query = '';

		// Table name is mandatory
		if ( empty( $data['table'] ) ) {
			$mess = 'Failed to create query: table name is missing';
			trigger_error( $mess );
			throw new Exception( $mess );
		}

		// Define query type
		if ( ! empty( $data['query'] ) ) {
			$q = strtolower( $data['query'] );

			if ( ! in_array( $q, array( 'select', 'delete', 'update', 'insert' ) ) ) {
				$mess = sprintf( 'Failed to create query: unsupported query type: "%s"', $q );
				trigger_error( $mess );
				throw new Exception( $mess );
			}

		} else {
			$q = 'select';
		}

		// Add query type
		if ( 'select' === $q ) {
			$query .= "SELECT";

			if ( ! empty( $data['calc'] ) ) {
				$query .= ' SQL_CALC_FOUND_ROWS';
			}

			if ( ! empty( $data['distinct'] ) ) {
				$query .= ' DISTINCT';
			}
			
		} elseif ( 'insert' === $q ) {
			$query .= "INSERT INTO";

		} elseif ( 'delete' === $q ) {
			$query .= 'DELETE';

			if ( isset( $data['join'] ) && isset( $data['delete_from'] ) ) {
				$query .= ' ' . $this->escape_db_name( $data['delete_from'] );
			}

			$query .= ' FROM';

		} elseif ( 'update' === $q ) {
			$query .= 'UPDATE';
		}

		// Add fields to be fetched
		if ( 'select' === $q  ) {
			$fields_parts = array();
			
			// COUNT
			if ( ! empty( $data['count'] ) ) {
				trigger_error( 'Argument "COUNT" is obsolete' );

				foreach( (array)$data['count'] as $field ) {
					$fields_parts[] = 'COUNT(' . $this->escape_db_name( $field ) . ')';
				}
			}

			// Functions
			if ( ! empty( $data['function'] ) ) {
				trigger_error( 'Argument "FUNCTION" is obsolete' );

				$functions_parts = $this->create_function_clause( $data['function'] );

				if ( false !== $functions_parts ) {
					$fields_parts = array_merge( $fields_parts, $functions_parts );
				}
			}

			if ( empty( $fields_parts ) ) {
				$query .= ' ' . $this->create_select_clause( isset( $data['field'] ) ? $data['field'] : array() );

			} else {
				$query .= ' ' . implode( ', ', $fields_parts );
			}

			$query .= " FROM";
		}

		if ( is_array( $data['table'] ) ) {
			$query .= ' ' . $this->escape_db_name( DB_PREFIX . current( $data['table'] ) );

			if ( ! is_int( key( $data['table'] ) ) ) {
				$query .= ' as ' . $this->escape_db_name( key( $data['table'] ) );
			}

		} else {
			$query .= ' ' . $this->escape_db_name( DB_PREFIX . $data['table'] );
		}

		if ( isset( $data['join'] ) ) {
			$query .= ' ' . $this->create_join( $data['join'] );
		}

		// Add values
		if ( ! empty( $data['values'] ) && 'insert' === $q ) {
			$query .= ' ' . $this->create_values_clause( $data['values']);

			// ON DUPLICATE KEY UPDATE clause
			if ( ! empty( $data['on_duplicate'] ) ) {
				$query .= ' ' . $this->get_on_duplicate( $data['on_duplicate'] );
			}
		}

		// Add UPDATE SET clause
		if ( ! empty( $data['set'] ) && 'update' === $q ) {
			$query .= ' ' . $this->create_set_clause( $data['set'] );
		}

		// Add WHERE clause
		if ( ! empty( $data['where'] )  && ! in_array( $q, array( 'insert', 'truncate', ) ) ) {
			$query .= ' ' . $this->create_where_clause( $data['where'] );
		}

		// Add ORDER BY clause
		if ( ! empty( $data['order_by'] ) && 'select' === $q ) {

			$query .= ' ' . $this->get_order_by( $data['order_by'] );
		}

		// Add GROUP BY clause
		if ( ! empty( $data['group_by'] ) && 'select' === $q ) {
			$query .= ' GROUP BY ' . $this->escape_db_name( $data['group_by'] );
		}

		if ( ! empty( $data['limit'] ) && (int)$data['limit'] > 0 && 'select' === $q ) {
			$limit = (int)$data['limit'];
			$start = 0;

			if ( isset( $data['start'] ) && (int)$data['start'] >= 0 ) {
				$start = (int)$data['start'];
			}

			$query .= " LIMIT $start, $limit";	
		}

		if ( function_exists( 'is_log_query' ) && is_log_query() && function_exists( 'console_log' ) ) {
			console_log( $query );
		}

		return $query;
	}

	/**
	 * Returns ON DUPLICATE part
	 * @param array $data Data
	 * @return string
	 */
	public function get_on_duplicate( $data ) {
		$ret = '';
		$duplicate_parts = array();

		if ( ! is_array( $data ) ) {
			$mess = 'Data of "ON DUPLICATE KEY UPDATE" clause need to be wrapped into array';
			trigger_error( $mess );
			throw new Exception( $mess );
		}

		foreach( $data as $name => $value ) {
			if ( ! is_scalar( $value ) ) {
				$mess = sprintf( 'Value need to be scalar, %s given instead', gettype( $value ) );
				trigger_error( $mess );
				throw new Exception( $mess );
			}

			$duplicate_parts[] = $this->escape_db_name( $name ) . ' = ' . $this->escape_db( $value );
		}

		if ( ! empty( $duplicate_parts ) ) {
			$ret = 'ON DUPLICATE KEY UPDATE ' .implode( ', ', $duplicate_parts ); 
		}

		return $ret;
	}

	/**
	 * Returns ORDER BY clause
	 * @param array $data Data
	 * @return string
	 */
	public function get_order_by( $data ) {
		$order_by_parts = array();

		foreach( (array)$data as $order_by => $order_dir ) {
			if ( is_int( $order_by ) ) {
				$order_by = $order_dir;
			}

			$order = 'ASC';
			if ( $order_dir !== $order_by ) {
				if ( in_array( strtolower( $order_dir ), array( 'desc', 'asc', ) ) ) {
					$order = strtoupper( $order_dir );
				}
			}

			$order_by_parts[] = $this->escape_db_name( $order_by ) . ' ' . $order;
		}

		return 'ORDER BY ' . implode( ', ', $order_by_parts );
	}

	/**
	 * Returns glue string
	 * @param array &$data Data
	 * @return string
	 */
	public function get_glue( &$data ) {
		if ( isset( $data['glue'] ) ) {
			$glue = strtoupper( $data['glue'] );
			unset( $data['glue'] );

			if ( ! in_array( $glue, array( 'AND', 'OR', ) ) ) {
				$glue = 'AND';
			}
		} else {
			$glue = 'AND';
		}

		return ' '. $glue . ' ';
	}

	/**
	 * Creates WHERE clause for a query
	 * @param array $where Where data
	 * @param type|array &$parts Where parts
	 * @return string Where clause
	 */
	public function create_where_clause( $where, &$parts = array() ) {
		$glue = $this->get_glue( $where );

		if ( is_array( current( $where ) ) ) {
			foreach( $where as $w ) {
				$ret = $this->create_where_clause( $w, $parts );
			}

		} else {
			if( empty( $where['operation'] ) ) {
				$mess = 'WHERE clause\'s operation field is missing';
				trigger_error( $mess );
				throw new Exception( $mess );
			}

			if ( empty( $where['field'] ) ) {
				$mess = 'WHERE clause\'s field name is missing';
				trigger_error( $mess );
				throw new Exception( $mess );
			}

			if ( ! isset( $where['value'] ) ) {
				$mess = 'WHERE clause\'s value list missing';
				trigger_error( $mess );
				throw new Exception( $mess );
			}

			$where_operation = ADK()->db->escape( strtolower( htmlspecialchars_decode( $where['operation'] ) ) );

			switch( $where_operation ) {
			case 'in' :
				$parts[] = $this->escape_db_name( $where['field'] ) .
					" IN (" . implode( ", ", (array)$this->escape_db( $where['value'] ) ) . ")";
				break;
			case '>':
			case '<':
			case '>=':
			case '<=':
			case '=':
			case '<>':
				if ( 'NULL' === $where['value'] || 'null' === $where['value'] ) {
					if ( '<>' === $where_operation ) {
						$parts[] = $this->escape_db_name( $where['field'] ) . ' IS NOT NULL';
						
					} elseif ( '=' === $where_operation ) {
						$parts[] = $this->escape_db_name( $where['field'] ) . ' IS NULL';
						
					}

				} else {
					$parts[] = $this->escape_db_name( $where['field'] ) . ' ' . $where_operation . ' ' .
						$this->escape_db( $where['value'] );
				}
				break;
			case 'like' :
				$parts[] = $this->escape_db_name( $where['field'] ) .
					' LIKE (' . $this->escape_db( $where['value'] ) . ')';
			}
		}

		return 'WHERE ' . implode( $glue, $parts );
	}

	/**
	 * Creates function statement clause for a query
	 * @param array $function Where data
	 * @param type|array &$parts Where parts
	 * @return array Function parts
	 */
	public function create_function_clause( $function, &$parts = array() ) {
		trigger_error( 'create_function_clause is obsolete' );

		$args = array();

		if ( is_string( $function ) ) {
			$function = array( 'function' => $function );
		}

		if ( is_array( current( $function ) ) ) {
			foreach( $function as $f ) {
				$ret = $this->create_function_clause( $f, $parts );

				if ( false === $ret ) {
					return false;
				}
			}

		} else {
			if( empty( $function['function'] ) ) {
				trigger_error( 'Function name missing' );
				return false;
			}

			if ( preg_match( '/^([^(]+)\(([^)]*)\)/', $function['function'], $m ) ) {

				$function['function'] = $m[1];

				if ( isset( $m[2] ) ) {
					$argums = array_map( 'trim', explode( ',', $m[2] ) );


					if ( isset( $function['arguments'] ) ) {
						$function['arguments'] = array_merge( (array)$function['arguments'], $argums );

					} else {
						$function['arguments'] = $argums;
					}
				}
			}

			$function_name = $this->db_escape( strtoupper( $function['function'] ) );

			if ( isset( $function['arguments' ] ) ) {
				foreach( (array)$function['arguments'] as $a ) {

					if ( is_array( $a ) && array_key_exists( 'function', $a ) ) {
						$args = array_merge( $args, $this->create_function_clause( $a ) );

					} else {
						$first_char = substr( $a, 0, 1 );

						if ( in_array( $first_char, array( '"', "'", '`' ) ) ) {
							$a = $first_char . $this->db_escape( substr( $a , 1, -1 ) ) . $first_char;

						} elseif ( in_array( $a, array( '*' ) ) ) {
							// Do nothing

						} elseif (strpos( $a, '=' ) !== false ) {
							// do nothing

						} else {
							$a = $this->escape_db_name( $a );
						}

						$args[] = $a;
					}
				}
			}

			$parts[] = $function_name . '(' .
				( ! empty( $args ) ? implode( ', ', $args ) : '' ) .
				')' . 
				( ! empty( $function['alias'] ) ? ' as ' . $this->escape_db_name( $function['alias'] ) : '' );
		}

		return $parts;
	}

	/**
	 * Mergers WHERE clauses
	 * @param array $where WHERE to be merged
	 * @param array $with WHERE to be merged with
	 * @return array
	 */
	public function merge_where( $where, $with ) {
		if ( ! is_array( $where ) ) {
			$mess = sprintf(
				'Merging WHERE clause needs to be an array, %s given instead',
				gettype( $where )
			);

			trigger_error( $mess );
			throw new Exception( $mess );
		}

		if ( ! is_array( $with ) ) {
			$mess = sprintf(
				'WHERE clause to be merged with needs to be an array, %s given instead',
				gettype( $with )
			);

			trigger_error( $mess );
			throw new Exception( $mess );
		}

		// WHERE is array of arrays
		if ( is_array( current( $where ) ) ) {

			// WITH is array of arrays
			if ( is_array( current( $with ) ) ) {
				$where = array_merge( $where, $with );

			} else {
				$where[] = $with;
			}

			return $where;

		} else {
			$ret = array();

			if ( $where ) {
				$ret[] = $where;
			}

			if ( is_array( current( $with ) ) ) {
				$ret = array_merge( $ret, $with );

			} elseif ( $with ) {
				$ret[] = $with;
			}

			return $ret;
		}
	}

	/**
	 * Escapes strings to be used in DB
	 * @param string $value String to be escaped
	 * @return string
	 */
	public function db_escape( $value ) {
		return ADK()->db->escape( $value );
	}

	/**
	 * Creates UPDATE SET clause for a query
	 * @param array $set Set data
	 * @param type|array &$parts Set parts
	 * @return string Set parts
	 */
	public function create_set_clause( $set, &$parts = array() ) {

		if ( ! is_array( $set ) ) {
			$mess = 'SET values need to be wrapped into array';
			trigger_error( $mess );
			throw new Exception( $mess );
		}

		foreach( $set as $name => $val ) {
			$parts[] = $this->escape_db_name( $name ) . ' = ' . $this->escape_db( $val );
		}

		return 'SET ' . implode( ', ', $parts );
	}

	/**
	 * Escapes value to be inserted into DB
	 * @param scalar $value Value
	 * @return string
	 */
	public function escape_db_value( $value ) {
		$ret = '';

		if ( is_array( $value ) ) {
			$ret = array_map( array( $this, 'escape_db_value' ), $value );

		} else {
			if ( 'NULL' === $value ) {
				$ret = $value;

			} elseif ( '*' === $value ) {
				$ret = $value;

			} else {
				$value = preg_replace( '/^([\'"])(.*)\1$/', '$2', $value );
				$value = $this->db_escape( $value );
				$ret = "'" . $value . "'";
			}
		}

		return $ret;
	}

	/**
	 * Escapes field name to be inserted into DB
	 * @param string $name Name
	 * @return string
	 */
	public function escape_db_name( $name ) {
		$ret = '';

		if ( is_array( $name ) ) {
			$ret = array_map( array( $this, 'escape_db_name' ), $name );

		} else {
			$parts = array();

			foreach( explode( '.', $name ) as $n ) {
				if ( '*' === $n ) {
					$parts[] = $n;

				} else {
					$n = trim( $n, '`' );
					$parts[] = '`' . $this->db_escape( $n ) . '`';
				}
			}

			$ret = implode( '.', $parts );
		}

		return $ret;
	}

	/**
	 * Checks whether argument is DB function
	 * @param type $str Function
	 * @return string|boolean
	 */
	public function is_db_function( $str ) {
		$ret = false;

		if ( preg_match( '/^ ( [^(]+ ) # function body 
			                 \(        # opening parenthesis
			                    (.*)   # arguments (optional)
			                  \)       #closing parenthesis
			                  $/x', $str, $m ) ) {
			$func = strtoupper( $m[1] );

			if ( ! in_array( $func, self::$functions ) ) {
				return false;
			}

			$ret = $func . '(';

			if ( isset( $m[2] ) ) {
				$args = array_map( 'trim', explode( ',', $m[2] ) );

				if ( trim( $m[2] ) !== '' ) {
					$ret .= ' ' . implode( ', ', $this->escape_db( $args ) ) . ' ';
					
				} else {
					$ret .= '';
				}
			}

			$ret .= ')';
		}

		return $ret;
	}

	/**
	 * Escape value
	 * @param string|array $value Value to be escaped 
	 * @return string|array
	 */
	public function escape_db( $value ) {
		$ret = $value;

		if ( is_array( $value ) ) {
			$ret = array_map( array( $this, 'escape_db' ), $value );

		} else {
			$first_char = substr( $value, 0, 1 );
			$last_char = substr( $value , -1, 1 );
			$func = $this->is_db_function( $value );

			// Function
			if ( $func ) {
				$ret = $func;

			// Implicit name
			} elseif (  '`' === $first_char && '`' === $last_char ) {
				$ret = $this->escape_db_name( substr( $value, 1, -1 ) );

			// Implicit value
			} elseif ( $first_char === $last_char && in_array( $first_char, array( '"', "'", ) ) ) {
				$ret = $this->escape_db_value( substr( $value, 1, -1 ) );

			// NULL
			} elseif ( 'NULL' === $value || 'null' === $value ) {
				$ret = strtoupper( $value );

			// Numeric
			} elseif ( preg_match( '/^\d+\.?\d*$/', $value ) ) {
				$ret = $value;

			// Everything else is a name
			} else {
				$ret = $this->escape_db_value( $value );
			}
		}

		return $ret;
	}

	/**
	 * Creates JOIN clause
	 * @param array $join Join data
	 * @param type|array &$parts Join parts
	 * @return string Join parts
	 * @throws Advertikon\Exception on error
	 */
	public function create_join( $join, &$parts = array() ) {
		$ret = '';

		if ( is_array( current( $join ) ) ) {
			foreach( $join as $j ) {
				$res = $this->create_join( $j, $parts );
			}

		} else {
			if ( ! empty( $join['type'] ) ) {
				switch( $join['type'] ) {
				case 'left' :
					$ret .= 'LEFT JOIN';
					break;
				case 'right' :
					$ret .= 'RIGHT JOIN';
					break;
				default :
					$ret = 'JOIN';
					break;
				}

			} else {
				$ret .= 'JOIN';
			}

			if ( empty( $join['table'] ) ) {
				$mess = 'Failed to create query - missing name of the table to be joined';
				trigger_error( $mess );
				throw new Exception( $mess );
			}

			$ret .= ' `' . DB_PREFIX . $this->db_escape( $join['table'] ) . '`';

			if ( empty( $join['alias'] ) ) {
				$mess = 'Failed to create query - missing alias of the table to be joined';
				trigger_error( $mess );
				throw new Exception( $mess );
			}

			$ret .=  ' ' . $this->escape_db_name( $join['alias'] );

			if ( empty( $join['on'] ) && empty( $join['using'] ) ) {
				$mess = 'Failed to create query - joining condition missing';
				trigger_error( $mess );
				throw new Exception( $mess );
			}

			if ( ! empty( $join['using'] ) ) {
				$ret .= ' USING(' . $this->escape_db_name( $join['using'] ) . ')';

			} else {
				$ret .= ' ' . $this->create_on_clause( $join['on'] );
			}

			$parts[] = $ret;
		}

		return implode( ', ', $parts );
	}

	/**
	 * Creates ON clause for a JOIN statement
	 * @param array $on On data
	 * @param type|array &$parts On parts
	 * @return string On parts
	 * @throws Advertikon\Exception on error
	 */
	public function create_on_clause( $on, &$parts = array() ) {
		$glue = $this->get_glue( $on );

		if ( is_array( $on ) && is_array( current( $on ) ) ) {
			foreach( $on as $o ) {
				$this->create_on_clause( $o, $parts );
			}

		} else {
			if( empty( $on['operation'] ) ) {
				$mess = 'Failed to create query - ON clause\'s OPERATION part is missing';
				trigger_error( $mess );
				throw new Exception( $mess );
			}

			if ( empty( $on['left'] ) ) {
				$mess = 'Failed to create query - ON clause\'s LEFT part is missing';
				trigger_error( $mess );
				throw new Exception( $mess );
			}

			if ( ! isset( $on['right'] ) ) {
				$mess = 'Failed to create query - ON clause\'s RIGHT part is missing';
				trigger_error( $mess );
				throw new exception( $mess );
			}

			$on_operation = ADK()->db->escape( strtolower( htmlspecialchars_decode( $on['operation'] ) ) );

			switch( $on_operation ) {
			case '>':
			case '<':
			case '>=':
			case '<=':
			case '=':
			case '<>':
				$parts[] = $this->escape_db_name( $on['left'] ) . ' ' . $on_operation . ' ' .
					$this->escape_db( $on['right'] );
				break;
			default: 
				$mess = 'Failed to create query - forbidden ON clause operator: ' . $on_operation;
				trigger_error( $mess );
				throw new Exception( $mess );
				break;
			}
		}

		return 'ON(' . implode( $glue, $parts ) . ')';
	}

	/**
	 * Returns SQL_CALC_FOUND_ROWS result
	 * @return int
	 */
	public function get_calc_rows() {
		$ret = 0;
		$query = ADK()->db->query( "SELECT FOUND_ROWS()" );

		if ( $query && isset( $query->row ) ) {
			$ret = $query->row['FOUND_ROWS()'];
		}

		return $ret;
	}

	/**
	 * Returns formatted SQL string with data range query
	 * @param string $from SQL date from 
	 * @param string $to SQL date to 
	 * @param string $interval SQL INTERVAL value
	 * @return string
	 */
	public function get_sql_date_range( $from, $to, $interval ) {
		$d = "DATE_ADD( '" . ADK()->db->escape( $from) . "', INTERVAL a + b + c " . ADK()->db->escape( $interval ) .")";

		$ret = "SELECT  $d as `date` FROM
		( SELECT 0 a UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) aa,
		( SELECT 0 b UNION SELECT 10 UNION SELECT 20 UNION SELECT 33 UNION SELECT 40 UNION SELECT 50 UNION SELECT 60 UNION SELECT 70 UNION SELECT 80 UNION SELECT 90) bb,
		( SELECT 0 c UNION SELECT 100 UNION SELECT 200 UNION SELECT 300 UNION SELECT 400 UNION SELECT 500 UNION SELECT 600 UNION SELECT 700 UNION SELECT 800 UNION SELECT 900 ) cc WHERE $d BETWEEN '" . ADK()->db->escape( $from ) . "' AND '" . ADK()->db->escape( $to ) . "'";

		return $ret;
	}

	/**
	 * Returns array representation of SQL date
	 * @param string $str Date string 
	 * @return array
	 */
	public function parse_sql_date( &$str ) {
		$ret = array(
			'y' => 0,
			'm' => 0,
			'd' => 0,
			'h' => 0,
			'i' => 0,
			's' => 0,
		);

		if ( preg_match( '/^(\d{4})-(\d{2})-(\d{2})(?:\s+(\d{2}):(\d{2})(?::(\d{2}))?)?$/', $str, $m ) ) {
			$ret['y'] = $m[1];
			$ret['m'] = $m[2];
			$ret['d'] = $m[3];

			if ( isset( $m[4] ) ) {
				$ret['h'] = $m[4];
				$ret['i'] = $m[5];
			}

			if ( isset( $m[6] ) ) {
				$ret['s'] = $m[6];
			}
		}

		$str = $ret;

		return $ret;
	}

	/**
	 * Converts SQL date array to string
	 * @param array $date SQL date
	 * @return string
	 */
	public function sql_date_to_str( &$date ) {
		foreach( array( 'y', 'm', 'd', 'h', 'i', 's', ) as $i ) {
			if ( ! isset( $date[ $i ] ) ) {
				$date[ $i ] = '';
			}
		}

		$ret =
			str_pad( $date['y'], 4, '0', STR_PAD_LEFT ) . '-' .
			str_pad( $date['m'], 2, '0', STR_PAD_LEFT ) . '-' .
			str_pad( $date['d'], 2, '0', STR_PAD_LEFT ) . ' ' .
			str_pad( $date['h'], 2, '0', STR_PAD_LEFT ) . ':' .
			str_pad( $date['i'], 2, '0', STR_PAD_LEFT ) . ':' .
			str_pad( $date['s'], 2, '0', STR_PAD_LEFT );

		$date = $ret;

		return $ret;
	}

	/**
	 * Usort callback to sort sql date
	 * @param array $a Date A
	 * @param array $b Date B 
	 * @return int
	 */
	public function compare_sql_date( $a, $b ) {
		foreach( array( 'y', 'm', 'd', 'h', 'i', 's' ) as $part ) {
			if ( ! isset( $a[ $part ] ) && ! isset( $b[ $part ] ) ) {
				continue;
			}

			if ( ! isset( $a[ $part ] ) ) {
				return -1;
			}

			if ( ! isset( $b[ $part ] ) ) {
				return 1;
			}

			$res = (int)$a[ $part ] - (int)$b[ $part ];

			if ( 0 !== $res ) {
				return $res;
			}
		}

		return 0;
	}

	/**
	 * Runs query to DB
	 * @param array $data Query data 
	 * @return boolean|int|array
	 * @throws Advertikon\Exception on error
	 */
	public function run_query( $data ) {
		$ret = null;

		if ( is_array( $data ) ) {
			$data = $this->create_query( $data );
		}

		if ( ! $data ) {
			$mess = 'Failed to run query - dataset is empty';
			trigger_error( $mess );
			throw new Exception( $mess );
		}

		$query = ADK()->db->query( $data );

		if ( $query ) {

			// Delete, insert, update
			if ( gettype( $query ) === 'boolean' ) {

				if ( ADK()->db->countAffected() > 0 ) {
					$ret = ADK()->db->countAffected();

				} else {
					$ret = null;
				}

			// Select
			} elseif ( gettype( $query ) === 'object' && ( isset( $query->row ) || isset( $query->rows ) ) ) {
				$ret = new DB_Result( $query->rows );
			}
		}

		return $ret;
	}

	/**
	 * Returns set(s) of values to be INSERT into table
	 * @return array
	 */
	public function create_value_set() {
		$args = func_get_args();
		$data = array();
		$max_length = 0;
		$set = array();

		foreach( $args as $a ) {
			$a = (array)$a;
			$max_length = max( $max_length, count( $a ) );
			$data[] = array( 'data' => $a, 'last' => null );
		}

		for( $i = 0; $i < $max_length; $i++ ) {
			$line = array();

			for( $y = 0, $len = count( $data ); $y < $len; $y++ ) {
				if ( isset( $data[ $y ]['data'][ $i ] ) ) {
					$line[] = $data[ $y ]['data'][ $i ];
					$data[ $y ]['last'] = $data[ $y ]['data'][ $i ];

				} else {
					$line[] = $data[ $y ]['last'];
				}
			}

			$set[] = '(' . implode( ', ', $this->escape_db( $line ) ) . ')';
		}

		return $set;
	}

	/**
	 * Creates VALUES clause
	 * @param array $data Set clause data
	 * @return string
	 */
	public function create_values_clause( $data ) {
		if ( ! is_array( $data ) ) {
			$mess = 'Failed to create query: data for VALUES clause need to be an array';
			trigger_error( $mess );
			throw new Exception( $mess );
		}

		if ( 0 === count( $data ) ) {
			$mess = 'Failed to create query: empty data set for VALUES clause';
			trigger_error( $mess );
			throw new Exception( $mess );
		}

		$names = array();
		$values = array();

		foreach( $data as $name => $value ) {
			if ( is_int( $name ) ) {
				$mess = 'Failed to create query: array key need to be a field name';
				trigger_error( $mess );
				throw new Exception( $mess );
			}

			$names[] = $this->escape_db_name( $name );
			$values[] = $value;
		}

		return '(' . implode( ', ', $names ) . ') VALUES ' .
			implode( ', ', call_user_func_array( array( $this, 'create_value_set' ), $values ) );
	}

	/**
	 * Creates SELECT clause
	 * @param array $data Select data
	 * @return string
	 */
	public function create_select_clause( $data = null ) {
		$parts = array();
		$ret = '';

		if ( $data  ) {
			foreach( (array)$data as $alias => $name ) {

				// Sub-query
				if ( preg_match( '/^\(?\s*select/i', $name ) ) {
					if ( is_int( $alias ) ) {
						$mess = 'Failed to create query: subquery need to be aliased';
						trigger_error( $mess );
						throw new Exception( $mess );
					}

					$name = trim( $name );

					if ( '(' !== substr( $name, 0, 1 ) || ')' !== substr( $name, -1, 1 ) ) {
						$name = '(' . $name . ')';
					}

					$parts[] = $name . ' as ' . $this->escape_db_name( $alias );

				} else {

					// Without an alias
					if ( is_int( $alias ) ) {
						$parts[] =  $this->escape_db( $name );

					} else {
						$parts[] = $this->escape_db( $name ) . ' as ' . $this->escape_db_name( $alias );
					}
				}
			}
		}

		if ( empty( $parts ) ) {
			$ret = '*';

		} else {
			$ret = implode( ', ', $parts );
		}

		return $ret;
	}
}
