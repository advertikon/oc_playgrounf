<?php
/**
* Advertikon Stripe transport Exception
*
* @author Advertikon
* @package Stripe
* @version 2.8.11
*/

namespace Advertikon\Exception;

use Advertikon\Exception as AdvertikonException;

class Transport extends AdvertikonException {

	protected $_data;

	public function __construct( $data ){
		if( is_scalar( $data ) ) {
			parent::__construct( $data );
		}
		else {
			$this->_data = $data;
			parent::__construct( '' );
		}
	}

	/**
	* Get transported object
	*
	* @return mixed
	*/
	public function getData(){
		return $this->_data;
	}
}