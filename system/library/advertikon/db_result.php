<?php
/**
 * Advertikon DB result Class
 * @author Advertikon
 * @package Advertikon
 * @version 0.0.7
 */

namespace Advertikon;

class DB_Result extends Array_Iterator {

	protected $data = array();

	public function __construct( $data = array() ) {

		if ( ! $data ) {
			return;
		}

		if ( is_array( $data ) && is_array( current( $data ) ) ) {
			$this->data = $data;

		} else {
			array_push( $this->data, (array)$data );
		}
	}

	public function offsetGet ( $offset ) {
		$current = $this->current();

		return $current[ $offset ];
	}

	public function offsetSet ( $offset , $value ) {
		if ( is_null( $offset ) ) {
			$this->append( $value );

		} else {
			$current = &$this->data[ key( $this->data ) ];

			if ( ( is_string( $offset ) || is_numeric( $offset ) ) && isset( $current[ $offset ] ) ) {
				$current[ $offset ] = $value;

			} else {
				array_push( $current, $value );
			}

			unset( $current );
		}
	}

	public function current() {
		$ret = parent::current();

		if ( false === $ret ) {
			$ret = array();
		}

		return $ret;
	}
}
