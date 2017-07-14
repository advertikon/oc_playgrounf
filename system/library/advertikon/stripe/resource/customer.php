<?php
/**
 * Advertikon Stripe Customer Resource
 * @author Advertikon
 * @package Stripe
 * @version 2.8.11
 */

namespace Advertikon\Stripe\Resource;

use Advertikon\Resource;

class Customer extends Resource {

	public function __construct( $id = null, $key = null ) {
		$this->get_ns();
		$this->table_name = DB_PREFIX . ADK( $this->namespace )->customer_table;
		parent::__construct( $id, $key );
	}

	protected $columns = array(
		'stripe_id',
		'description',
		'metadata',
		'oc_customer_id',
	);

	/**
	 * @see Advertikon\Resource::save()
	 */
	public function save() {
		if ( ! $this->stripe_id ) {
			$mess = 'Stripe\'s customer ID is mandatory';
			trigger_error( $mess );
			throw new \Advertikon\Exception( $mess );
		}

		if ( ! $this->oc_customer_id ) {
			$mess = 'OpenCart\'s customer ID is mandatory';
			trigger_error( $mess );
			throw new \Advertikon\Exception( $mess );
		}

		parent::save();
	}

	/**
	 * @see Advertikon\Resource::load()
	 */
	public function load( $value, $field_name = null ) {
		if ( ! $value ) {
			if ( ADK( $this->namespace )->customer->isLogged() ) {
				$value = ADK( $this->namespace )->customer->getId();
				$field_name = 'oc_customer_id';

			} else {
				$mess = 'Customer\'s ID is mandatory';
				trigger_error( $mess );
				throw new \Advertikon\Exception( $mess );
			}
		}

		parent::load( $value, $field_name );
	}
}
