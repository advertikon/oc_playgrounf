<?php

use PHPUnit\Framework\TestCase;

class resourceCustomerTest extends myUnit {

	public function __construct() {
		$this->a = new Advertikon\Stripe\Resource\Customer();
	}

	public function test() {
		$this->clean();

		$this->a->load( 'foo' );
		$this->assertFalse( $this->a->is_exists() );

		$this->a->stripe_id = 'foo';
		$this->a->description = 'test';
		$this->a->oc_customer_id = 10000000;
		$this->a->save();

		$this->a->metadata = 'meta';
		$this->a->save();

		$this->assertTrue( $this->a->is_exists() );
		$this->assertSame( 'foo', $this->a->stripe_id );
		$this->a->load( $this->a->id );
		$this->assertTrue( $this->a->is_exists() );
		$this->assertSame( 'test', $this->a->description );
		$this->a->delete();
		$this->a->load( $this->a->oc_customer_id, 'oc_customer_id' );
		$this->assertFalse( $this->a->is_exists() );

		$all = $this->a->all();

		$this->assertTrue( is_object( $all ) );
		foreach( $all as $al ) {
			$this->assertTrue( ! is_null( $al->id ) );
		}

		$this->assertSame( 1, count( $all->slice( 0, 1 ) ) );

		$this->clean_end();

	}
}
