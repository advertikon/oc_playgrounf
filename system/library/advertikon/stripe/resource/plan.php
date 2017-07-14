<?php
/**
 * Advertikon Stripe Payment plan Resource
 * @author Advertikon
 * @package Stripe
 * @version 2.8.11
 */

namespace Advertikon\Stripe\Resource;

use Advertikon\Advertikon;
use Advertikon\Resource;
use Advertikon\Stripe\Exception;

class Plan extends Resource {

	protected $protected_names = array( 'id', 'date_modified', 'name' );
	protected $columns = array(
		'sp_plan_id',
		'oc_plan_id',
		'plan',
	);

	public function __construct( $id = null, $key = null ) {
		$this->get_ns();
		$this->table_name = DB_PREFIX . ADK( $this->namespace )->plan_table;
		parent::__construct( $id, $key );
	}

	public function __set( $name, $val ) {
		if ( 'plan' === $name ) {
			if ( is_string( $val ) ) {
				$this->data['plan'] = ADK( $this->namespace )->json_encode( ADK( $this->namespace )->json_decode( $val ) );

			} else {
				$this->data['plan'] = ADK( $this->namespace )->json_encode( $val );
			}

		} else {
			return parent::__set( $name, $val );
		}
	}

	public function __get( $name ) {

		// Stripe subscription\'s object
		if ( 'plan' === $name ) {
			return ADK( $this->namespace )->json_decode( $this->data['plan'] );

		} else {
			return parent::__get( $name );
		}
	}

	/**
	 * @see Advertikon\Resource::load()
	 * @return type
	 */
	public function load( $key, $field = null ) {
		if ( is_null( $field ) ) {
			$field = 'id';
		}

		$q = ADK( $this->namespace )->db->query(
			"SELECT `p`.*, `rd`.`name`, `mp`.`profile_id`
			FROM `{$this->table_name}` as `p`
			LEFT JOIN `" . DB_PREFIX . "recurring_description` as `rd`
				ON( `rd`.`recurring_id` = `p`.`oc_plan_id` AND `rd`.`language_id` = " . ADK( $this->namespace )->get_lang_id() . " )
			LEFT JOIN `" . DB_PREFIX . ADK( $this->namespace )->profile_map_table . "` `mp`
				ON( `p`.`oc_plan_id` = `mp`.`oc_plan_id` )
			WHERE `p`.`$field` = '" . ADK( $this->namespace )->db->escape( $key ) . "'"
		);

		$this->init( $q );
	}

	/**
	 * @see Advertikon\Resource::all()
	 * @return array
	 */
	public function all() {
		$q = ADK( $this->namespace )->db->query(
			"SELECT `p`.*, `rd`.`name`, `mp`.`profile_id`
			FROM `" . $this->table_name  . "` as `p`
			LEFT JOIN `" . DB_PREFIX . "recurring_description` as `rd`
				ON( `rd`.`recurring_id` = `p`.`oc_plan_id` AND `rd`.`language_id` = " . ADK( $this->namespace )->get_lang_id() . " )
			LEFT JOIN `" . DB_PREFIX . ADK( $this->namespace )->profile_map_table . "` `mp`
				ON( `p`.`oc_plan_id` = `mp`.`oc_plan_id` )"
		);

		return $this->init_all( $q );
	}

	/**
	 * Deletes Stripe plans without corresponding OpenCart plans
	 * @return void
	 */
	public function actualize() {
		ADK( $this->namespace )->db->query(
			"DELETE `p` FROM `" . $this->table_name . "` as `p`
			LEFT JOIN `" . DB_PREFIX . "recurring` as `r`
				ON (`p`.`oc_plan_id` = `r`.`recurring_id` )
			WHERE `r`.`recurring_id` IS NULL" );

		ADK( $this->namespace )->log(
			sprintf(
				'Deleted %s Stripe recurring plans without corresponding OC plans',
				ADK( $this->namespace )->db->countAffected() >= 0 ? ADK( $this->namespace )->db->countAffected() : 0
			),
			ADK( $this->namespace )->log_debug_flag
		);
	}
}
