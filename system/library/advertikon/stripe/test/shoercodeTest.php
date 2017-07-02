<?php

use PHPUnit\Framework\TestCase;

class shortcodeTest extends myUnit {

	public function __construct() {
		$this->a = new Advertikon\Stripe\Shortcode();
	}

	public function test() {
		$this->clean();

		$this->assertFalse( strpos( $this->a->do_shortcode( '{storeName}. Order #{orderId} for {customerName}') , '}' ) );

		$this->clean_end();
	}
}
