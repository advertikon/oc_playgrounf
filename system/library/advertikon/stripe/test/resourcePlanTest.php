<?php

use PHPUnit\Framework\TestCase;

class resourcePlanTest extends myUnit {

	public function __construct() {
		
	}

	public function test() {
		$this->clean();

		$this->a = new Advertikon\Stripe\Resource\Plan( 1 );

		$this->a->load( 'foo' );
		$this->assertFalse( $this->a->is_exists() );

		$this->a->oc_plan_id = 1;
		$this->a->sp_plan_id = 'test';
		$this->a->save();

		$this->a->plan = '["a","1"]';
		$this->assertEquals( array( 'a', '1' ), $this->a->plan );
		$this->a->plan = array( 'foo' => 'bar' );
		$ob = new \stdClass();
		$ob->foo = 'bar';
		$this->assertEquals( $ob, $this->a->plan );

		$this->a->save();

		$this->assertTrue( $this->a->is_exists() );
		$this->assertSame( 'test', $this->a->sp_plan_id );

		$this->a->load( $this->a->id );
		$this->assertTrue( $this->a->is_exists() );
		$this->assertEquals( $ob, $this->a->plan );

		$this->a->delete();

		$this->a->load( $this->a->id );
		$this->assertFalse( $this->a->is_exists() );

		$all = $this->a->all();

		$this->assertTrue( is_object( $all ) );
		foreach( $all as $al ) {
			$this->assertTrue( ! is_null( $al->id ) );
		}

		$this->assertTrue( count( $all->slice( 0, 1 ) ) >= 0 );

		$this->clean_end();

	}
}
