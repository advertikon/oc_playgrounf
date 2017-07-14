<?php
/**
 * Admin model
 * @package Stock distribution
 * @version 0.0.7
 */
class ModelExtensionModuleCompany extends Model {
	public $a = null;

	/**
	 * Class constructor
	 * @param Object $registry 
	 * @return void
	 */
	public function __construct( $registry ) {
		parent::__construct( $registry );
		$this->a = Advertikon\Company\Advertikon::instance();
	}

	/**
	 * Adds tables
	 * @return void
	 */
	public function add_tables() {
		$this->db->query(
			"CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $this->a->company_table . "`
			(
				`id`             INT           UNSIGNED AUTO_INCREMENT,
				`customer_id`    INT           UNSIGNED,
				`name`           VARCHAR(255),
				`vat_no`         VARCHAR(255),
				`reg_no`         VARCHAR(255),
				`representative` VARCHAR(255),
				`address_line_1` VARCHAR(255),
				`zone_id`        INT,
				`country_id`     INT,
				`city`           VARCHAR(255),
				`bank`           VARCHAR(255),
				`iban`           VARCHAR(255),
				`phone`          VARCHAR(255),
				`default`        TINYINT        DEFAULT 0,
				PRIMARY KEY(`id`)
			)
			ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin"
		);

		$this->fill_in_tables();
	}

	/**
	 * Fills in tables with content
	 * @return void
	 */
	public function fill_in_tables() {

	}

	/**
	 * Config validation;
	 * @return boolean
	 */
	public function validate_configs() {
		if ( ! $this->a->has_permissions( 'modify' ) ) {
			throw new \Advertikon\exception( $this->a->__( 'You have no permission to modify the extension' ) );
		}

		return true;
	}
}
