<?php
/**
 * Catalog Controller
 * @author Advertikon
 * @package XXXXXXXX
 * @version 0.00.000
 * 
 * @source catalog/view/theme/default/stylesheet/advertikon/xxxxxx/*
 * @source catalog/view/theme/default/stylesheet/advertikon/advertikon.css
 * 
 * @source catalog/view/javascript/advertikon/advertikon.js
 * @source catalog/view/javascript/advertikon/xxxxxxx/*
 */
class ControllerPaymentAdvertikonStripe extends Controller {

	public $a = null;
	public $model = null;

	public function __construct( $registry ) {
		parent::__construct( $registry );
		$this->a = Advertikon\XXXXX\Advertikon::instance();
		$this->load->model( $this->a->full_name );
		$this->model = $this->{'model_' . str_replace( '/', '_', $this->a->full_name )};
	}

	/**
	 * Logging facility
	 */
	public function log() {
		$this->a->console->tail();
	}

	/**
	 * Load JS action
	 * @return void
	 */
	public function compress() {
		echo $this->a->compress();
		die;
	}
}
?>
