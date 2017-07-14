<?php

use PHPUnit\Framework\TestCase;

class stripeOptionTest extends myUnit {

	public function __construct() {
		//require_once 'config.php';
		$this->a = new Advertikon\Stripe\Option();
	}

	public function test() {
		$this->clean();

		$this->assertTrue( is_array( $this->a->next_year( 100 ) ) );
		$this->assertTrue( is_array( $this->a->payment_option() ) );
		$this->assertTrue( is_array( $this->a->stripe_account() ) );
		$this->assertTrue( is_array( $this->a->payment_system() ) );

		$this->clean_end();
	}
}
