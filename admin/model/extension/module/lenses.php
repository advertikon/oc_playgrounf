<?php
/**
 * Admin model
 * @author Advertikon
 * @package XXXXX
 * @version 0.00.000
 */

class ModelPaymentAdvertikonStripe extends Model {
	protected $a = null;

	public function __construct( $registry ) {
		parent::__construct( $registry );
		$this->a = Advertikon\Stripe\Advertikon::instance();
	}

	/**
	 * Creates all the extension's tables
	 * @return void
	 */
	public function create_tables() {
		$this->db->query(
			"CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $this->a->xxxxxx . "`
			(
				`id`            INT(11)      UNSIGNED AUTO_INCREMENT,
				`xxxxxxxxxx`    VARCHAR(255),
				PRIMARY KEY(`id`)
			)
			ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin"
		);
	}

	/**
	 * Validates configuration data
	 * @param array $errors
	 * @return boolean
	 */
	public function validate_configs() {
		global $adk_errors;

		$errors = &$adk_errors;

		try {

			// Check permissions to modify the extension's settings
			if ( ! $this->a->has_permissions( 'modify' ) ) {
				$errors['warning'][] = $this->a->__( 'You have no permissions to modify extension settings' );
				throw new Advertikon\Exception( '' );
			}

			// XXXXXXXXXX
			if ( ! $this->a->post( 'xxxxxx' ) ) {
					$errors['input_errors']['xxxxxxx'] = $this->a->__( 'xxxxxxxxxxxxxxxxx' );
			}

			// Done without errors
			if ( $this->a->is_empty( $errors['input_errors'] ) ) {
				$errors['success'] = true;

			//Got errors
			} else {
				$errors['warning'][] = $this->a->__( 'In order to continue you have to correct some data' );
				$errors ['success'] = false; 
			}

		} catch( Advertikon\Exception $e ) {
			$errors['success'] = false;
		} 

		return $errors['success'];
	}
}
