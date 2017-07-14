<?php
/**
  * Catalog Model
  * @author Advertikon
  * @package XXXXXX
  * @version 0.00.000
  */

class ModelPaymentAdvertikonStripe extends Model {
	public $a = null;

	public function __construct( $registry ) {
		parent::__construct( $registry );
		$this->a = Advertikon\XXXXX\Advertikon::instance();
	}
}
?>
