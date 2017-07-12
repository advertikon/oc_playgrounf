<?php

use PHPUnit\Framework\TestCase;

class resourceOcRecurringTest extends myUnit {

	public function __construct() {
		$this->a = new Advertikon\Stripe\Resource\Oc_Plan();
	}

	public function test() {
		$this->clean();

		$this->a->load( 'foo' );
		$this->assertFalse( $this->a->is_exists() );

		$this->a->price = 100;
		$this->a->frequency = 'day';
		$this->a->duration = 10;
		$this->a->save();

		$this->a->cycle = 8;
		$this->a->save();

		$this->assertTrue( $this->a->is_exists() );
		$this->assertSame( 'day', $this->a->frequency );
		$this->a->load( $this->a->requrrig_id );
		$this->assertTrue( $this->a->is_exists() );
		$this->assertSame( 8, $this->a->cycle );

		$this->assertTrue( is_object( $this->a->profile ) );
		$this->a->profile_id = 1;
		$this->assertSame( 1, (int)$this->a->profile->id );

		$this->a->delete();

		$this->a->load( $this->a->recurring_id );
		$this->assertFalse( $this->a->is_exists() );

		$all = $this->a->all();

		$this->assertTrue( is_object( $all ) );
		foreach( $all as $al ) {
			$this->assertTrue( ! is_null( $al->recurring_id ) );
		}

		$this->assertSame( 1, count( $all->slice( 0, 1 ) ) );

		$this->clean_end();

	}
}
