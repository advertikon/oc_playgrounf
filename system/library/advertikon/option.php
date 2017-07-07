<?php
/**
 * Advertikon Option Class
 * @author Advertikon
 * @package Advertikon
 * @version 0.0.7
 */

namespace Advertikon;

class Option {

	static protected $cache = array();

	public function __call( $name, $args ) {
		$method = 'get_' . $name;

		if ( ! method_exists( $this, $method ) ) {
			$mess = sprintf( 'Method "%s" does not exist', $name );
			trigger_error( $mess );
			throw new Exception( $mess );
		}

		if ( ! isset( self::$cache[ $name ] ) ) {
			self::$cache[ $name ] = call_user_func_array(
				array( $this, $method ),
				$args
			);
		} 

		return self::$cache[ $name ];
	}

	/**
	 * Returns yes/no option
	 * @return array
	 */
	public function get_yes_no() {
		return array(
			ADK()->__( 'No' ),
			ADK()->__( 'Yes' ),
		);
	}

	/**
	 * Returns currencies list
	 * @return array
	 */
	public function get_currency() {
		$ret = array();

		ADK()->load->model( 'localisation/currency' );

		foreach( ADK()->model_localisation_currency->getCurrencies() as $c ) {
			$ret[ $c['currency_id'] ] = $c['title'];
		}

		return $ret;
	}

	/**
	 * Returns currencies list
	 * @return array
	 */
	public function get_currency_code() {
		$ret = array();

		ADK()->load->model( 'localisation/currency' );

		foreach( ADK()->model_localisation_currency->getCurrencies() as $c ) {
			$ret[ $c['code'] ] = $c['title'];
		}

		return $ret;
	}

	/**
	 * Formats shipping methods list to be shown in select element
	 * @return array
	 */
	public function get_shipping_methods( $active = true ) {
			$ret = array( ADK()->__( 'Select shipping method' ) );

			foreach( ADK()->get_shipping_methods() as $method ) {
				$ret[ $method['code'] ] = $method['name'];
			}
	
		return $ret;
	}

	/**
	 * Formats return statuses to be shown in select element
	 * @return array
	 */
	public function get_return_statuses( $active = true ) {
			$ret = array( ADK()->__( 'Select status' ) );

			$s = ADK()->q( array(
				'table' => 'return_status',
				'where' => array(
					'operation' => '=',
					'field'     => 'language_id',
					'value'     => ADK()->config->get( 'config_language_id' ),
				),
			) );

			foreach( $s as $status ) {
				$ret[ $status['return_status_id'] ] = $status['name'];
			}
	
		return $ret;
	}

	/**
	 * Returns system totals
	 * @return array
	 */
	public function get_totals() {
		$ret = array();

		if ( defined( 'DIR_CATALOG' ) ) {
			$query = ADK()->q( array(
				'table' => 'extension',
				'query' => 'select',
				'where' => array(
					'field'     => 'type',
					'operation' => '=',
					'value'     => 'total',
				),
				'order_by' => 'code',
			) );


			ADK()->load->model( 'localisation/tax_rate' );

			foreach ($query as $result) {
				if( ! ADK()->config->get( $result['code'] . '_status' ) ) {
					continue;
				}

				$lang = 'total/' . $result['code']; 

				if ( version_compare( VERSION, '2.3.0.0', '>=' ) ) {
					$lang = 'extension/' . $lang; 
				}

				ADK()->load->language( $lang );

				if( $result ['code'] === 'tax' ) {
					foreach( ADK()->model_localisation_tax_rate->getTaxRates() as $tax ) {
						$ret[ $result['code'] . '_' . $tax['tax_rate_id'] ] = $tax['name'];

					}
				}

				else {
					$ret[ $result['code'] ] = ADK()->language->get( 'heading_title' );
				}
			}
		}

		return $ret;
	}

	/**
	 * Returns short codes list
	 * @return array
	 */
	public function get_shortcode() {
		$shortcode = new Shortcode();
		$ret = array();

		foreach( $shortcode->get_shortcode_data() as $name => $sh ) {
			$ret[ $name ] = $sh['hint'];
		} 

		return $ret;
	}

	/**
	 * Returns system's Geo Zones
	 * @return array
	 */
	public function get_geo_zone() {
		$ret = array();
		$q = ADK()->q( array(
			'table' => 'geo_zone',
			'query' => 'select',
		) );

		foreach( $q as $geo_zone ) {
			$ret[ $geo_zone['geo_zone_id'] ] = $geo_zone['name'];
		}

		return $ret;
	}

	/**
	 * Returns system's Zones
	 * @return array
	 */
	public function get_zone() {
		$ret = array();
		$q = ADK()->q( array(
			'table' => 'zone',
			'query' => 'select',
		) );

		foreach( $q as $zone ) {
			$ret[ $zone['zone_id'] ] = $zone['name'];
		}

		return $ret;
	}


	/**
	 * Returns system's stores
	 * @return array
	 */
	public function get_store() {
		$ret = array( 0 => ADK()->config->get( 'config_name' ) );

		$q = ADK()->q( array(
			'table' => 'store',
			'query' => 'select'
		) );

		foreach( $q as $store ) {
			$ret[ $store['store_id'] ] = $store['name'];
		}

		return $ret;
	}

	/**
	 * Returns countries
	 * @return array
	 */
	public function get_country() {
		$ret = array();
		$q = ADK()->q( array(
			'table' => 'country',
			'query' => 'select',
		) );

		foreach( $q as $country ) {
			$ret[ $country['country_id'] ] = $country['name'];
		}

		return $ret;
	}


	/**
	 * Returns system's customer groups
	 * @return array
	 */
	public function get_customer_group() {
		$q = null;
		$ret = array();
		$data = array();

		$sql = "SELECT * FROM " . DB_PREFIX . "customer_group cg LEFT JOIN " . DB_PREFIX . "customer_group_description cgd ON (cg.customer_group_id = cgd.customer_group_id) WHERE cgd.language_id = '" . (int)ADK()->config->get('config_language_id') . "'";

		$sort_data = array(
			'cgd.name',
			'cg.sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY cgd.name";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = ADK()->db->query($sql);

		foreach( $query->rows as $group ) {
			$ret[ $group['customer_group_id'] ] = $group['name'];
		}

		return $ret;
	}

	/**
	 * Returns system\s order statuses
	 * @return array
	 */
	public function get_order_status() {
		$ret = array();

		$q = ADK()->q( array(
			'table' => 'order_status',
			'query' => 'select',
			'where' => array(
				'field'     => 'language_id',
				'operation' => '=',
				'value'     => ADK()->config->get( 'config_language_id' ),
			),
		) );

		foreach( $q as $status ) {
			$ret[ $status['order_status_id'] ] = $status['name'];
		}

		return $ret;
	}

	/**
	 * Returns log verbosity options
	 * @return array
	 */
	public function get_log_verbosity() {
		return array(
			Advertikon::LOGS_DISABLE => ADK()->__( 'Disabled' ),
			Advertikon::LOGS_ERR     => ADK()->__( 'Error' ),
			Advertikon::LOGS_MSG     => ADK()->__( 'Message' ),
			Advertikon::LOGS_DEBUG   => ADK()->__( 'Debug' ),
		);
	}
}
