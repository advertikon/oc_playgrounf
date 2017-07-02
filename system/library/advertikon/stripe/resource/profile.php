<?php
/**
 * Advertikon Stripe Payment plan's profile Resource
 * @author Advertikon
 * @package Stripe
 * @version 2.8.11
 * 
 * @depend Advertikon\Stripe\Option
 */

namespace Advertikon\Stripe\Resource;

use Advertikon\Advertikon;
use Advertikon\Resource;
use Advertikon\Stripe\Exception;
use Advertikon\Stripe\Option;


class Profile extends Resource {

	protected $protected = array( 'Default' , 'NonRecurring' );
	public $is_protected = false;
	protected $properties = array(
		'totals_to_recurring' => 'coupon,sub_total,total',
		'user_abort'          => 0,
		'add_force'           => '',
		'price_options'       => 1,
		'first_order'         => 0,
		'cancel_now'          => 0,
	);
	protected $fields = array();

	public function __construct( $id = null, $key = null ) {
		$this->get_ns();
		$this->data['option'] = new Option();
		$tax_list = array();

		foreach( $this->data['option']->totals() as $code => $name ) {
			if ( 'tax' === substr( $code, 0, 3 ) ) {
				$tax_list[] = $code;
			}
		}

		if ( $tax_list ) {
			$this->properties['totals_to_recurring'] .= ',' . implode( ',', $tax_list );
		}

		$this->data = $this->properties;
		$this->table_name = DB_PREFIX . ADK( $this->namespace )->profile_table;
		parent::__construct( $id, $key );
	}

	/**
	 * @see Advertikon\Resource::reset()
	 */
	public function reset() {
		parent::reset();
		$this->data = $this->properties;
	}

	public function __set( $name, $val ) {
		if ( 'name' === $name ) {

			// Attempt to rename protected (system) profile
			if ( $this->is_exists() ) {
				if ( $this->is_protected && $val !== $this->init_data['name'] ) {
					$mess = ADK( $this->namespace )->__( 'Profile can not be renamed' );
					ADK( $this->namespace )->log( $mess, ADK( $this->namespace )->log_debug_flag );
					throw new Exception( $mess );
				}

			// Attempt to set on custom profile name of protected profile
			} else {
				if ( in_array( $val, $this->protected ) ) {
					$mess = ADK( $this->namespace )->__( 'Profile\'s name "%s" is reserved and can not be used', $this->name );
					ADK( $this->namespace )->log( $mess, ADK( $this->namespace )->log_debug_flag );
					throw new Exception( $mess );
				}
			}
		}

		if ( in_array( $name, array( 'totals_to_recurring', 'add_force') ) ) {
			if ( is_array( $val ) ) {
				$val = implode( ',', $val );
			}

			$this->data[ $name ] = $val;

		} else {
			return parent::__set( $name, $val );
		}
	}

	public function __get( $name ) {
		if ( in_array( $name, array( 'totals_to_recurring', 'add_force' ) ) ) {
			if ( isset( $this->data[ $name ] ) ) {
				return explode( ',', $this->data[ $name ] );
			}

			return array();

		} else {
			return parent::__get( $name );
		}
	}

	/**
	 * Loads stripe profile
	 * @param int $id Profile id
	 * @return object $this
	 */
	public function load( $id, $field_name = null ) {
		$this->reset();

		$q = ADK( $this->namespace )->db->query(
			"SELECT * FROM `" . $this->table_name . "` WHERE `id` = '" . (int)$id . "'"
		);

		if ( $q->num_rows ) {
			$this->load_name( $q );
			$this->load_property();
		}

		return $this;
	}

	/**
	 * Loads default profile
	 * @return object $this
	 */
	public function load_default() {
		$this->reset();

		$q = ADK( $this->namespace )->db->query( 
			"SELECT * FROM `" . $this->table_name . "` WHERE `name` = 'Default'"
		);
		
		if ( $q->num_rows ) {
			$this->load_name( $q );
			$this->load_property();
		}

		return $this;
	}

	/**
	 * Loads non-recurring profile
	 * @return void
	 */
	public function load_non_recurring() {
		$this->reset();

		$q = ADK( $this->namespace )->db->query(
			"SELECT * FROM `" . $this->table_name . "` WHERE `name` = 'NonRecurring'"
		);

		if ( $q->num_rows ) {
			$this->load_name( $q );
			$this->load_property();
		}

		return $this;
	}

	/**
	 * Loads profile name
	 * @param object $q Query object 
	 * @return void
	 */
	protected function load_name( $q ) {
		if( $q->row ) {
			$this->data['name'] = $q->row['name'];
			$this->data['id'] = $q->row['id'];
			$this->is_new = false;

			if ( in_array( $q->row['name'], $this->protected ) ) {
				$this->is_protected = true;
			}
		}
	}

	/**
	 * Loads profile's properties
	 * @return void
	 */
	protected function load_property() {
		if ( $this->id ) {
			$query = ADK( $this->namespace )->db->query(
				"SELECT `property_id` as property, `value`
					FROM `" . DB_PREFIX . ADK( $this->namespace )->profile_value_table . "`
					WHERE `profile_id` = " . (int)$this->id
				);

			foreach( $query->rows as $row ) {
				$this->data[ $row['property'] ] = isset( $row['value'] ) ?
					$row['value'] : $this->properties[ $row['property'] ];
			}

			$this->init_data = $this->data;
		}
	}

	/**
	 * Saves profile
	 * @return void
	 */
	public function save() {
		if( empty( $this->data['name'] ) ) {
			$mess = ADK( $this->namespace )->__( 'Profiles\'s name is missing' );
			trigger_error( $mess );
			throw new Exception( $mess );
		}

		// Save data
		// Existing profile
		if ( $this->is_exists() ) {
			if ( $this->data['name'] !== $this->init_data['name'] ) {
				ADK( $this->namespace )->db->query(
					"UPDATE `" . $this->table_name . "` SET
						`name` = '" . ADK( $this->namespace )->db->escape( $this->data['name'] ) . "'
						WHERE `id` = " . (int)$this->data['id']
				);

				if ( ADK( $this->namespace )->db->countAffected() < 1 ) {
					$mess = ADK( $this->namespace )->__( 'Failed to update profile' );
					trigger_error( $mess );
					throw new Exception( $mess );
				}
			}

		// New profile
		} else {
			ADK( $this->namespace )->db->query(
				sprintf(
					"INSERT INTO `" . $this->table_name . "` SET`name` = '%s'",
					ADK( $this->namespace )->db->escape( $this->name )
				)
			);

			if ( ADK( $this->namespace )->db->countAffected() < 1 ) {
				$mess = ADK( $this->namespace )->__( 'Failed to save profile' );
				trigger_error( $mess );
				throw new Exception( $mess );
			}

			$this->data['id'] = ADK( $this->namespace )->db->getLastId();
		}

		$names = array();
		$values = array();
		$changed = false;

		foreach( $this->data as $name => $val ) {
			if ( ! array_key_exists( $name, $this->properties ) ) {
				continue;
			}

			if ( ! isset( $this->init_data[ $name ] ) || $this->init_data[ $name ] !== $val ) {
				$names[] = $name;
				$values[] = $val;
			}
		}

		// Some properties have been changed or it's a new profile
		if ( $names ) {
			if ( $this->is_exists() ) {
				ADK( $this->namespace )->db->query(
					"DELETE FROM `" . DB_PREFIX . ADK( $this->namespace )->profile_value_table . "`
						WHERE `profile_id` = " . (int)$this->data['id'] . " AND
							`property_id` IN (" . implode( ',', ADK( $this->namespace )->q()->escape_db_value( $names ) ) . ")"
				);

				// Protected profiles have no data by default
				if ( ! $this->is_protected && ADK( $this->namespace )->db->countAffected() < 1 ) {
					$mess = ADK( $this->namespace )->__( 'Failed to save profile' );
					trigger_error( $mess );
					throw new Exception( $mess );
				}
			}

			ADK( $this->namespace )->db->query(
				sprintf(
					"INSERT INTO `" . DB_PREFIX . ADK( $this->namespace )->profile_value_table . "`
					(`profile_id`,`property_id`,`value`) VALUES %s",
					implode(
						',',
						ADK( $this->namespace )->q()->create_value_set(
							ADK( $this->namespace )->q()->escape_db_value( $this->id ),
							ADK( $this->namespace )->q()->escape_db_value( $names ),
							ADK( $this->namespace )->q()->escape_db_value( $values )
						)
					)
				)
			);

			if ( ADK( $this->namespace )->db->countAffected() < 1 ) {
				$mess = ADK( $this->namespace )->__( 'Failed to save profile' );
				trigger_error( $mess );
				throw new Exception( $mess );
			}
		}

		$this->is_new = false;
		$this->init_data = $this->data;
	}

	/**
	 * Deletes profile
	 * @return void
	 */
	public function delete() {

		if( ! isset( $this->data[ 'id' ] ) ) {
			return;
		}

		if( in_array( $this->name , $this->protected ) ) {
			throw new Exception( sprintf( 'Profile "%s" can not be deleted' , $this->data['name'] ) );
		}

		ADK( $this->namespace )->db->query( "DELETE FROM `" . $this->table_name . "` WHERE `id`= " . (int)$this->data['id'] );

		if( ADK( $this->namespace )->db->countAffected() ) {
			ADK( $this->namespace )->db->query(
				"DELETE FROM `" . DB_PREFIX . ADK( $this->namespace )->profile_value_table . "`
				WHERE `profile_id`= " . (int)$this->data['id']
			);

			if( ADK( $this->namespace )->db->countAffected() < 1 ) {
				$mess = sprintf( 'Failed to delete data of profile #%s', $this->data['id'] );
				ADK( $this->namespace )->log( $mess, ADK( $this->namespace )->log_error_flag );
			}

			ADK( $this->namespace )->db->query(
				"DELETE FROM `" . DB_PREFIX . ADK( $this->namespace )->profile_map_table . "`
				WHERE `profile_id`= " . (int)$this->data['id']
			);

			if( ADK( $this->namespace )->db->countAffected() < 1 ) {
				$mess = sprintf( 'Failed to delete mappings for profile #%s', $this->data['id'] );
				ADK( $this->namespace )->log( $mess, ADK( $this->namespace )->log_error_flag );
			}

		} else {
			$mess = ADK( $this->namespace )->__( 'Failed to delete profile' );
			trigger_error( $mess );
			throw new Exception( $mess );
		}
	}

	/**
	 * Maps OC plan to profile
	 * @param int $oc_plan_id OC plan ID
	 * @param int|null $profile_id Profile ID, optional
	 * @return void
	 * @throws Advertikon\Stripe\EXception on error
	 */
	public function add_mapping( $oc_plan_id, $profile_id = null ) {
		if ( is_null( $profile_id ) ) {
			if ( ! $this->is_exists() ) {
				$mess = ADK( $this->namespace )->__( 'Empty profile can not be mapped' );
				trigger_error( $mess );
				throw new Exception( $mess );
			}

			$profile_id = $this->id;
		}

		ADK( $this->namespace )->db->query(
			sprintf(
				"DELETE FROM `" . DB_PREFIX . ADK( $this->namespace )->profile_map_table . "`
				WHERE `oc_plan_id` = '%s'",
				(int)$oc_plan_id
			)
		);

		ADK( $this->namespace )->db->query(
			sprintf(
				"INSERT INTO `" . DB_PREFIX . ADK( $this->namespace )->profile_map_table . "`
				(`profile_id`, `oc_plan_id`) VALUES ('%s','%s')",
				(int)$profile_id,
				(int)$oc_plan_id
			)
		);

		if( ADK( $this->namespace )->db->countAffected() < 1 ) {
			$mess = ADK( $this->namespace )->__( 'Failed to map profile' );
			trigger_error( $mess );
			throw new Exception( $mess );
		}
	}
}
