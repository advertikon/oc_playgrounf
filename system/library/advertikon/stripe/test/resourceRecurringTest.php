<?php

use PHPUnit\Framework\TestCase;

class resourceRecurringTest extends myUnit {

	public function __construct() {
		$this->a = new Advertikon\Stripe\Resource\Recurring();
	}

	public function test() {
		$this->clean();

		$this->a->load( 1000000, 'recurring_order_id' );
		$this->a->delete();

		// Empty
		$this->a->load( 'foo' );
		$this->assertFalse( $this->a->is_exists() );

		$this->a->recurring_order_id = 1000000;
		$this->a->subscription_id = 2;
		$this->assertSame( 1000000, $this->a->recurring_order_id );
		$this->assertSame( 2, $this->a->subscription_id );

		// Newly created
		$this->a->save();

		$a = $this->a;
		$this->clean_end();
		$this->assertException( function() use( $a ) { $a->recurring_order_id = 'foo'; }, 'Advertikon\Stripe\Exception' );
		$this->assertException( function() use( $a ) { $a->subscription_id = 'foo'; }, 'Advertikon\Stripe\Exception' );
		$this->clean();

		$this->assertEquals( 1000000, $this->a->recurring_order_id );
		$this->assertEquals( 2, $this->a->subscription_id );
		$this->a->next = 'foo';

		// Existing
		$this->a->save();

		$this->assertEquals( 1000000, $this->a->recurring_order_id );
		$this->assertEquals( 2, $this->a->subscription_id );
		$this->assertSame( 'foo', $this->a->next );

		// Existing
		$this->a->load( $this->a->id );
		$this->assertTrue( $this->a->is_exists() );
		$this->assertEquals( '1000000', $this->a->recurring_order_id );
		$this->assertEquals( 2, $this->a->subscription_id );
		$this->assertSame( 'foo', $this->a->next );
		$this->a->next = 'foo';

		$this->assertEquals( 2, $this->a->get_subscription_id() );
		$this->assertSame( '', $this->a->get_account_name() );

		$this->clean_end();
		$this->assertException( function() use( $a ) { $a->get_stripe_customer_id(); }, 'Advertikon\Stripe\Exception' );
		$this->assertException( function() use( $a ) { $a->get_oc_recurring_order(); }, 'Advertikon\Stripe\Exception' );
		$a->update_oc_order_status( 0, 0 );
		$this->assertTrue( true );
		$this->clean();

		$this->assertTrue( is_int( $this->a->get_subscription_status( 'trialing' ) ) );

		$this->a->delete();

		// Empty
		$this->a->load( $this->a->id );
		$this->assertFalse( $this->a->is_exists() );

		$all = $this->a->all();
		// $this->assertSame( 3, count( $all ) );
		$this->assertTrue( is_object( $all ) );
		foreach( $all as $al ) {
			$this->assertTrue( ! is_null( $al->id ) );
		}

		$this->assertSame( 1, count( $all->slice( 0, 1 ) ) );

		$this->clean_end();
	}
}
