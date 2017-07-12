<?php
/**
 * Advertikon Stripe Recurring order data Resource
 * @author Advertikon
 * @package Stripe
 * @version 2.8.11
 */

namespace Advertikon\Stripe\Resource;

use Advertikon\Resource;
use Advertikon\Stripe\Exception;

class Recurring extends Resource {

	protected $columns = array(
		'subscription_id',
		'next',
		'total_tax',
		'shipping_tax',
		'invoices',
		'recurring_order_id',
	);

	public function __construct( $id = null, $field = null ) {
		$this->get_ns();
		$this->table_name = DB_PREFIX . ADK( $this->namespace )->recurring_table;
		parent::__construct( $id, $field );
	}

	public function __set( $name, $val ) {
		if ( in_array( $name, array( 'recurring_order_id', 'subscription_id' ) ) ) {
			if ( $this->is_exists() ) {
				$mess = sprintf( 'Value "%s" can not be modified', $name );
				throw new Exception( $mess );
			}
		}

		return parent::__set( $name, $val );
	}

	public function __get( $name ) {
		if ( $name === 'recurring_order' ) {
			return $this->get_oc_recurring_order();
		}

		if ( $name === 'order' ) {
			return $this->get_oc_order();
		}

		if ( $name === 'subscription_id' ) {
			return $this->get_subscription_id();
		}

		return parent::__get( $name );
	}

	/**
	 * Returns Stripe subscription ID
	 * As of v 2.6.0 subscription ID consists of subscription_iD:account_name
	 * @return String
	 */
	public function get_subscription_id() {
		$id = strchr( $this->data['subscription_id'], ':', 1 );

		return $id ? $id : $this->data['subscription_id'];
	}

	/**
	 * Get account name
	 * As of v 2.6.0 subscription ID consists of subscription_iD:account_name
	 * @return String
	 */
	public function get_account_name() {
		$name = substr( strchr( $this->data['subscription_id'], ':' ), 1 );

		return $name ?: '';
	}

	/**
	 * Returns Stripe customer's ID for this recurring order
	 * @return string
	 * @throws Advertikon\Stripe\Exception if customer doesn't exist for existing order
	 */
	public function get_stripe_customer_id() {
		if ( empty( $this->data['stripe_customer'] ) ) {
			$q = ADK( $this->namespace )->db->query(
				"SELECT * FROM `" . DB_PREFIX . ADK( $this->namespace )->customer_table . "`
				WHERE `oc_customer_id` = (SELECT `customer_id` FROM `" . DB_PREFIX . "order`
					WHERE `order_id` = (SELECT `order_id` FROM `" . DB_PREFIX . "order_recurring`
						WHERE `order_recurring_id` = " . (int)$this->recurring_order_id . ") )"
			);

			if ( $q->num_rows ) {
				$this->data['stripe_customer'] = $q->row['stripe_id'];
				$this->init_data['stripe_customer'] = $q->row['stripe_id'];

			} elseif ( $this->is_exists() ) {
				$mess = sprintf( 'Customer is missing for recurring order #%s', $this->recurring_order_id );
				ADK( $this->namespace )->log( $mess, ADK( $this->namespace )->log_error_flag );
				throw new Exception( $mess );
			}
		}

		return $this->data['stripe_customer'];
	}

	/**
	 * Returns corresponding OpenCart order
	 * @param int|null $oc_recurroing_order_id 
	 * @return array
	 */
	public function get_oc_recurring_order( $oc_recurring_order_id = null ) {
		if ( isset( $this->data['oc_recurring_order'] ) ) {
			return $this->data['oc_recurring_order'];
		}

		if ( is_null( $oc_recurring_order_id ) ) {
			$oc_recurring_order_id = $this->data['recurring_order_id'];
		}

		$this->data['oc_recurring_order'] = array();

		$req = ADK( $this->namespace )->db->query(
			"SELECT * FROM `" . DB_PREFIX . "order_recurring`
			WHERE `order_recurring_id` = " . (int)$oc_recurring_order_id
		);

		if ( $req->num_rows ) {
			$this->data['recurring_order_id'] = $req->row['order_recurring_id'];
			$this->data['oc_recurring_order'] = $req->row;

		} elseif ( $this->is_exists() ) {
			$mess = sprintf( 'OpenCart recurring order #%s is missing', $oc_recurring_order_id );
			ADK( $this->namespace )->log( $mess, ADK( $this->namespace )->log_error_flag );
			throw new Exception( $mess );
		}

		return $this->data['oc_recurring_order'];
	}

	/**
	 * Returns ordinary order for recurring one
	 * @return array
	 */
	public function get_oc_order() {
		if ( ! isset( $this->data['order'] ) ) {
			$recurring_order_id = $this->recurring_order_id;

			$q = ADK( $this->namespace )->db->query(
				"SELECT * FROM `" . DB_PREFIX . "order`
				WHERE `order_id` =
					(SELECT `order_id` FROM `" . DB_PREFIX . "order_recurring`
					WHERE `order_recurring_id` = " . (int)$recurring_order_id . ")"
			);

			$this->data['order'] = $q->row;
		}

		return $this->data['order'];
	}

	/**
	 * Returns OpenCart's recurring plan status depend on Stripe's recurring status
	 * @param string $status Stripe subscription status
	 * @return int
	 */
	public function get_subscription_status( $status ) {
		switch( $status ) {
			case 'trialing' :
			case 'active' :
				return version_compare( VERSION, '2.2.0.0', '>=' ) ? 1 : 2;//active
			break;
			case 'unpaid' :
				return version_compare( VERSION, '2.2.0.0', '>=' ) ? 4 : 3;//suspended
			break;
			case 'canceled' :
				return version_compare( VERSION, '2.2.0.0', '>=' ) ? 3 : 4;//canceled
			break;
			case 'past_due' :
				return version_compare( VERSION, '2.2.0.0', '>=' ) ? 5 : 6;//expired
			break;
		}
	}

	/**
	 * Updates status of corresponding OprnCart order
	 * @param string $oc_stat Status
	 * @param int|null $oc_order_id OpenCart recurring order ID
	 * @return boolean
	 */
	public function update_oc_order_status( $oc_stat, $oc_order_id = null ) {
		$req1 = ADK( $this->namespace )->db->query(
			"UPDATE `" . DB_PREFIX . "order_recurring`
			SET `status` = " . (int)$oc_stat . "
			WHERE `order_recurring_id` = " . (int)$oc_order_id
		);

		return ! ADK( $this->namespace )->db->countAffected();
	}
}
