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
}
