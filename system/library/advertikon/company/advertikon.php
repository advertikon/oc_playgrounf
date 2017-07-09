<?php
/**
 * @package Stock Distribution
 * @version 0.0.7
 */

namespace Advertikon\Company;

class Advertikon extends \Advertikon\Advertikon {

	static $instance = null;
	public $type = 'module';
	public $code = 'company';
	public static $c = __NAMESPACE__;
	public $tables = array(
		'company_table' => 'company',
	);
	public $format = '';

	public static $file = __FILE__;

	public function __construct() {
		if ( version_compare( VERSION, '2.3.0.0', '>=' ) ) {
			$this->type = 'extension/' . $this->type;
		}

		parent::__construct();

		$this->format = '{name}'            . "\n" .
						'{reg_no}'          . "\n" .
						'{bank}'            . "\n" .
						'{iban}'            . "\n" .
						'{address_line_1}'  . "\n" .
						'{city}'            . "\n" .
						'{zone}'            . "\n" .
						'{country}'         . "\n";
	}

	/**
	 * Returns class' singleton
	 * @return object
	 */
	public static function instance( $code = null ) {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
			parent::$instance[ self::$c ] = self::$instance;
		}

		return self::$instance;
	}

	/**
	 * @see Advertikon\Advertikon::get_version()
	 */
	static public function get_version() {
		return parent::get_version();
	}

	/**
	 * Returns company details by ID
	 * @param int $id Company ID
	 * @return array
	 */
	public function get_company_by_id( $id ) {
		$ret = array();

		$q = $this->db->query( "SELECT * FROM `" . DB_PREFIX . "company` WHERE `id` = " . (int)$id );

		if ( $q->num_rows ) {
			$ret = $q->row;
		}

		return $ret;
	}

	public function delete( $id ) {
		$q = $this->db->query( "DELETE FROM `" . DB_PREFIX . "company` WHERE `id` = " . (int)$id );
	}

	/**
	 * Returns company details for specific customer
	 * @param int $user_id Customer ID
	 * @return array
	 */
	public function get_companies_by_customer( $customer_id ) {
		$ret = array();

		$q = $this->db->query( "SELECT * FROM `" . DB_PREFIX . "company` WHERE `customer_id` = " . (int)$customer_id );

		if ( $q->num_rows ) {
			foreach( $q->rows as $row ) {
				$ret[ $row['id'] ] = $row;
			} 
		}

		return $ret;
	}

	/**
	 * Saves company details
	 * @param int $customer_id OpenCard customer ID
	 * @param array $data Company details
	 * @return boolean or integer Id of company or false if error occur
	 */
	public function save_company( $data, $customer_id = null ) {
		$ret = false;

		// Update
		if ( isset( $data['id'] ) ) {
			$this->db->query( "UPDATE `" . DB_PREFIX . "company`
				SET
					`name` = '" . $this->db->escape( $data['name'] ) . "',
					`vat_no` = '" . $this->db->escape( $data['vat'] ) . "',
					`reg_no` = '" . $this->db->escape( $data['reg'] ) . "',
					`representative` = '" . $this->db->escape( $data['representative'] ) . "',
					`address_line_1` = '" . $this->db->escape( $data['address'] ) . "',
					`zone_id` = '" . $this->db->escape( $data['zone'] ) . "',
					`country_id` = '" . $this->db->escape( $data['country'] ) . "',
					`city` = '" . $this->db->escape( $data['city'] ) . "',
					`bank` = '" . $this->db->escape( $data['bank'] ) . "',
					`iban` = '" . $this->db->escape( $data['iban'] ) . "',
					`phone` = '" . $this->db->escape( $data['phone'] ) . "'
				WHERE `id` = " . (int)$data['id']
			);

			$ret = $data['id'];

		// New company
		} else {
			$this->db->query( "INSERT INTO `" . DB_PREFIX . "company`
				SET
					`customer_id` = '" . (int)$customer_id . "',
					`name` = '" . $this->db->escape( $data['name'] ) . "',
					`vat_no` = '" . $this->db->escape( $data['vat'] ) . "',
					`reg_no` = '" . $this->db->escape( $data['reg'] ) . "',
					`representative` = '" . $this->db->escape( $data['representative'] ) . "',
					`address_line_1` = '" . $this->db->escape( $data['address'] ) . "',
					`zone_id` = '" . $this->db->escape( $data['zone'] ) . "',
					`country_id` = '" . $this->db->escape( $data['country'] ) . "',
					`city` = '" . $this->db->escape( $data['city'] ) . "',
					`bank` = '" . $this->db->escape( $data['bank'] ) . "',
					`iban` = '" . $this->db->escape( $data['iban'] ) . "',
					`phone` = '" . $this->db->escape( $data['phone'] ) . "'"
			);

			if ( $this->db->countAffected() > 0 ) {
				$ret = $this->db->getLastId();
			}
		}

		return $ret;
	}

	public function format_company( $result ) {
		$find = array(
			'{name}',
			'{reg_no}',
			'{vat_no}',
			'{representative}',
			'{address_line_1}',
			'{city}',
			'{zone}',
			'{country}',
			'{bank}',
			'{iban}',
			'{phone}',
		);

		$zones = $this->o()->zone();
		$countries = $this->o()->country();

		$replace = array(
			'name'           => $result['name'],
			'reg_no'         => $result['reg_no'],
			'vat_no'         => $result['vat_no'],
			'representative' => $result['representative'],
			'address_line_1' => $result['address_line_1'],
			'city'           => $result['city'],
			'zone'           => $zones[ $result['zone_id'] ],
			'country'        => $countries[ $result['country_id'] ],
			'bank'           => $result['bank'],
			'iban'           => $result['iban'],
			'phone'          => $result['phone'],
		);

		return str_replace(
			array("\r\n", "\r", "\n"),
			'<br>',
			preg_replace(
				array("/\s\s+/", "/\r\r+/", "/\n\n+/"),
				'<br>',
				trim( str_replace( $find, $replace, $this->format )	)
			)
		);
	}
}
