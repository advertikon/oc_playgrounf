<?php
/**
 * Advertikon Resource
 * @author Advertikon
 * @package Advertikon
 * @version 2.8.11
 */

namespace Advertikon;

use Model;

class Resource extends Model {

	protected $table_name;
	protected $key = 'id';
	protected $data = array();
	protected $columns = array();
	protected $init_data = array();
	protected $protected_names = array( 'id' );
	protected $is_new = true;
	protected $protected = array();
	protected $is_protected = false;
	public $namespace = null;

	public function __construct( $id = null, $key = null ) {
		if( ! $this->namaspace ) {
			$this->get_ns();
		}

		if ( ! is_null( $id ) ) {
			$this->load( $id, $key );
		}
	}

	public function __get( $name ) {
		if ( isset( $this->data[ $name ] ) ) {
			return $this->data[ $name ];
		}

		return null;
	}

	public function get_ns() {
		$parts = explode( '\\', get_class( $this ) );

		if ( isset( $parts[0] ) && isset( $parts[1] ) ) {
			$this->namespace = $parts[0] . '\\' . $parts[1];
		}
	}

	public function __set( $name, $value ) {
		if ( in_array( $name, $this->protected_names ) ) {
			$mess = sprintf( 'Property "%s" is protected and can not be modified', $name );
			trigger_error( $name );
			throw new Exception( $mess );
		}

		$this->data[ $name ] = $value;
	}

	/**
	 * Checks whether recourse field's name is protected
	 * @param string $name Fields name
	 * @return boolean
	 */
	public function is_protected_field( $name ) {
		return in_array( $name, $this->protected_names );
	}

	/**
	 * Resets the resource
	 * @return object self
	 */
	public function reset() {
		$this->data = array();
		$this->init_data = array();
		$this->is_new = true;

		return $this;
	}

	/**
	* Loads resource
	* @param string $value
	* @return Object
	*/
	public function load( $value , $field_name = null ){
		$this->reset();

		if( is_null( $field_name ) ) {
			$field_name = $this->key;
		}

		$query = ADK()->db->query(
			"SELECT * FROM `" . $this->table_name . "` 
				WHERE `" . ADK()->db->escape( $field_name ) . "` = '" . ADK()->db->escape( $value ) . "'"
		);

		$this->init( $query );

		return $this;
	}

	/**
	 * Loads all the resources
	 * @return Object Query result
	 */
	public function all() {
		$q = ADK()->db->query( "SELECT * FROM `" . $this->table_name . "`" );

		return $this->init_all( $q );
	}

	/**
	 * Returns list of lazy-loaded resources
	 * @param object $query Query object
	 * @return object Advertikon\Resource_Wrapper
	 */
	protected function init_all( $query ) {
		$wrapper = new Resource_Wrapper( get_class( $this ) );

		if ( $query && $query->num_rows ) {
			foreach( $query->rows as $row ) {
				$wrapper[] = $row[ $this->key ];
			}
		}

		return $wrapper;
	}

	/**
	 * Initializes resource by query result
	 * @param object $query Query result
	 * @return void
	 */
	protected function init( $query ) {
		if( $query->num_rows ) {
			$this->data = $query->row;
			$this->init_data = $query->row;
			$this->is_new = false;
		}
	}

	/**
	 * Checks if resource exists in DB
	 * @return boolean
	 */
	public function is_exists() {
		return ! $this->is_new;
	}

	/**
	 * Saves resource
	 * @return void
	 * @throws Advertikon\Exception on error
	 */
	public function save() {
		$names = array();
		$values = array();
		$changed = false;

		foreach( $this->data as $name => $val ) {
			if ( in_array( $name, $this->protected_names ) || ! in_array( $name, $this->columns ) ) {
				continue;
			}

			if ( ! isset( $this->init_data[ $name ] ) || $this->init_data[ $name ] !== $val ) {
				$changed = true;
			}

			$names[] = $name;
			$values[] = $val;
		}

		// Save into DB only if resource was changed or its a new resource
		if ( $changed || $this->is_new ) {

			// New resource
			if ( $this->is_new ) {
				ADK()->db->query(
					sprintf(
						"INSERT INTO `" . $this->table_name . "` (%s) VALUES (%s)",
						implode( ',', ADK()->q()->escape_db_name( $names ) ),
						implode( ',', ADK()->q()->escape_db_value( $values ) )
					)
				);

				if ( ADK()->db->countAffected() ) {
					$this->data[ $this->key ] = ADK()->db->getLastId();
				}

			// Changed resource
			} else {
				ADK()->db->query(
					sprintf(
						"UPDATE `" . $this->table_name. "` SET %s
						WHERE `{$this->key}` = '" . $this->data[ $this->key ] . "'",
						implode( ', ', $this->prepare_set( $names, $values ) )
					)
				);
			}

			if ( ! ADK()->db->countAffected() ) {
				$mess = ADK()->__( 'Failed to save resource' );
				trigger_error( $mess );
				throw new Exception( $mess );
			}

			$this->init_data = $this->data;
			$this->is_new = false;
		}
	}

	/**
	 * Prepares SET clause
	 * @param array $name Names
	 * @param array $values Values
	 * @return array
	 */
	protected function prepare_set( $name, $values ) {

		if ( count( $name ) > count( $values ) ) {
			$mess = 'Not enough values to match names';
			trigger_error( $mess );
			throw Exception( $mess );
		}

		$out = array();
		for( $i = 0, $len = count( $name ); $i < $len; $i++ ) {
			$out[] = "`" . ADK()->db->escape( $name[ $i ] ) . "` = '" . ADK()->db->escape( $values[ $i ] ) . "'";
		}

		return $out;
	}

	/**
	 * Deletes resource
	 * @return void
	 * @throws Advertikon\Exception on error
	 */
	public function delete() {
		if( ! $this->is_new ) {
			ADK()->db->query(
				"DELETE FROM `" . $this->table_name . "`
					WHERE `" . $this->key . "` = '" . ADK()->db->escape( $this->data[ $this->key ] ) . "'"
			);

			if( ! ADK()->db->countAffected() ) {
				$mess = ADK()->__( 'Failed to delete resource' );
				trigger_error( $mess );
				throw new Exception( $mess );
			}

			$this->is_new = true;
		}
	}
}
