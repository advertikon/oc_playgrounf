<?php

use PHPUnit\Framework\TestCase;

class resourceProfieTest extends myUnit {

	public function __construct() {
		
	}

	public function test() {
		$this->clean();

		$this->a = new Advertikon\Stripe\Resource\Profile( 1 );

		// Empty
		$this->a->load( 'foo' );
		$this->assertFalse( $this->a->is_exists() );

		// Default
		$this->a->load_default();
		$this->assertTrue( $this->a->is_exists() );

		// Non-recurring
		$this->a->load_non_recurring();
		$this->assertTrue( $this->a->is_exists() );

		$this->clean_end();
		$a = $this->a;
		$this->assertException( function() use( $a ) { $a->name = 'Default'; }, 'Advertikon\Stripe\Exception' );
		$this->assertException( function() use( $a ) { $a->name = 'test'; }, 'Advertikon\Stripe\Exception' );
		$this->clean();

		// Empty new
		$this->a->load( 'foo' );
		$this->a->name = 'test';
		$this->assertTrue( is_array( $this->a->totals_to_recurring ) );
		$this->assertTrue( is_array( $this->a->add_force ) );
		$this->a->add_force = array( 1,2,3 );
		$this->a->totals_to_recurring = array( 4,5,6 );

		$this->clean_end();
		$this->assertException( function() use( $a ) { $a->name = 'Default'; }, 'Advertikon\Stripe\Exception' );
		$this->clean();

		// Newly created
		$this->a->save();

		$this->assertEquals( array( 1, 2, 3, ), $this->a->add_force );
		$this->assertEquals( array( 4, 5, 6, ), $this->a->totals_to_recurring );
		$this->a->add_force = array( 11, 12, 13 );

		// Existing
		$this->a->save();

		$this->assertTrue( $this->a->is_exists() );
		$this->assertSame( 'test', $this->a->name );
		$this->assertEquals( array( 11, 12, 13, ), $this->a->add_force );

		// Existing
		$this->a->load( $this->a->id );
		$this->assertTrue( $this->a->is_exists() );
		$this->assertEquals( array( 4, 5, 6, ), $this->a->totals_to_recurring );

		$this->a->add_mapping( 'foo' );

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
