<?php
/**
 * Advertikon OpenCart's recurring profile Resource
 * @author Advertikon
 * @package Stripe
 * @version 2.8.11
 */

namespace Advertikon\Stripe\Resource;

use Advertikon\Advertikon;
use Advertikon\Resource;
use Advertikon\Stripe\Exception;

class OC_Plan extends Resource {

	protected $key = 'recurring_id';
	protected $protected_names = array( 'recurring_id', 'name' );
	protected $colums = array(
		'price',
		'frequency',
		'duration',
		'cycle',
		'trial_status',
		'trial_price',
		'trial_frequency',
		'trial_dutation',
		'trial_cycle',
		'status',
		'sort_order',
	);
	protected $protected = array();

	public function __construct( $key = null, $id = null ) {
		$this->get_ns();
		$this->table_name = DB_PREFIX . 'recurring';

		parent::__construct( $key, $id );
	}

	public function __get( $name ) {
		if ( 'profile' === $name ) {
			if (
				! isset( $this->data['profile'] ) ||
				! is_object( $this->data['profile'] ) ||
				! is_a( $this->data['profile'], 'Advertikon\Stripe\Resource\Profile' ) ||
				! $this->data['profile']->id
			) {
				$this->data['profile'] = new Profile();

				if ( isset( $this->data['profile_id'] ) ) {
					$this->data['profile']->load( $this->data['profile_id'] );
					
					if ( ! $this->data['profile']->is_exists() ) {
						$mess = sprintf( 'Recurring profile #%s does not exist', $this->data['profile_id'] );
						trigger_error( $mess );
						throw new Exception( $mess );
					}

				} else {
					$this->data['profile']->load_default();
				}
			}	

			return $this->data['profile'];

		} else {
			return parent::__get( $name );
		}
	}

	/**
	 * @see Advertikon\Resource::load()
	 */
	public function load( $id, $key = null ) {
		if ( is_null( $key ) ) {
			$key = $this->key;
		}

		$q = ADK( $this->namespace )->db->query(
			sprintf(
				"SELECT `r`.*, `rd`.`name`, `pm`.`profile_id`
				FROM `" . DB_PREFIX . "recurring` `r`
				LEFT JOIN `" . DB_PREFIX . "recurring_description` `rd`
					ON( `r`.`recurring_id` = `rd`.`recurring_id` AND `rd`.`language_id` = '%s' )
				LEFT JOIN `" . DB_PREFIX . ADK( $this->namespace )->profile_map_table . "` `pm`
					ON( `r`.`recurring_id` = `pm`.`oc_plan_id` )
				WHERE `r`.`%s` = '%s'",

				ADK( $this->namespace )->get_lang_id(),
				ADK( $this->namespace )->db->escape( $key ),
				ADK( $this->namespace )->db->escape( $id )
			)
		);

		$this->init( $q );

		return $this;
	}

	/**
	 * @see Advertikon\Resource::all()
	 */
	public function all() {

		$q = ADK( $this->namespace )->db->query(
			sprintf(
				"SELECT `r`.*, `rd`.`name`, `pm`.`profile_id`
				FROM `" . DB_PREFIX . "recurring` `r`
				LEFT JOIN `" . DB_PREFIX . "recurring_description` `rd`
					ON( `r`.`recurring_id` = `rd`.`recurring_id` AND `rd`.`language_id` = '%s' )
				LEFT JOIN `" . DB_PREFIX . ADK( $this->namespace )->profile_map_table . "` `pm`
					ON( `r`.`recurring_id` = `pm`.`oc_plan_id` )",

				ADK( $this->namespace )->get_lang_id()
			)
		);

		return $this->init_all( $q );
	}

	/**
	 * @return Advertikon\Resource::save()
	 */
	public function save() {
		parent::save();

		if (
			isset( $this->data['profile_id'] ) &&

			(
				// New profile
				! isset( $this->init_data['profile_id'] ) ||

				// Changed profile
				$this->data['profile_id'] !== $this->init_data['profie_id']
			)
		) {
			ADK( $this->namespace )->db->query(
				"DELETE FROM `" . DB_PREFIX . ADK( $this->namespace )->profile_map_table . "`
				WHERE `profile_id` = " . (int)$this->data['profile_id']
			);

			ADK( $this->namespace )->db->query(
				sprintf(
					"INSERT INTO `" . DB_PREFIX . ADK( $this->namespace )->profile_map_table , "`
						(`profile_id`,`oc_plan_id`) VALUES ( '%s', '%s' )",
						(int)$this->data['profile_id'],
						(int)$this->data['recurring_id']
					)
			);

			if ( ! ADK( $this->namespace )->db->countAffected() ) {
				$mess = ADK( $this->namespace )->__( 'Failed to save profile mapping' );
				trigger_error( $mess );
				throw new Exception( $mess );
			}
		}
	}

	/**
	 * @see Advertikon\Resource::delete()
	 */
	public function delete() {
		parent::delete();

		if ( isset( $this->data['profile_id'] ) ) {
			ADK( $this->namespace )->db->query(
				"DELETE FROM `" . DB_PREFIX . ADK( $this->namespace )->profile_map_table . "`
				WHERE `profile_id` = " . (int)$this->data['profile_id']
			);

			// We do not check result since mapping may have not been yet saved into DB
		}
	}
}
