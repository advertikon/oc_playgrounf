<?php

use PHPUnit\Framework\TestCase;

class bogusCartTest extends myUnit {

	public function __construct() {
		global $adk_registry;
		require_once 'boguscart.php';
		$this->a = new Advertikon\Stripe\BogusCart( $adk_registry );
	}

	public function test() {
		$this->clean();

		$this->a->add( 1, 1, array(), 1  );
		$this->a->process();

		$this->assertTrue( is_array( $this->a->getProducts() ) );
		$this->assertTrue( is_array( $this->a->getTotals() ) );
		$this->assertTrue( is_array( $this->a->getRecurringTotals() ) );
		$this->assertNull( $this->a->getRateByName( 'foo') );

		$t = $this->a->getTotals();
		$this->assertTrue( is_array( $this->a->fixTotals( $t ) ) );

		$this->assertTrue( is_bool( $this->a->onlyRecurring() ) );
		$this->assertTrue( is_object( $this->a->fixRecurringPrice() ) );
		$this->assertTrue( is_bool( $this->a->has_trial( current( $this->a->getProducts() ) ) ) );
		$this->assertNull( $this->a->resetRecurringPrice() );
		$this->assertTrue( is_object( $this->a->getRecurringPrice() ) );
		$this->a->process();
		$this->assertTrue( is_array( $this->a->ordinaryTotals() ) );
		$this->assertTrue( is_array( $this->a->recurringTotals() ) );
		$this->assertTrue( is_array( $this->a->ordinaryProducts() ) );
		$this->assertTrue( is_array( $this->a->recurringProducts() ) );
		$this->assertTrue( is_array( $this->a->sortTotals( $t ) ) );
		$this->assertTrue( is_array( $this->a->extractRecurring() ) );
		$this->assertTrue( is_array( $this->a->extractOrdinary() ) );
		$this->assertNull( $this->a->separate() );
		$totals = array();
		$total = array();
		$taxes = array();

		$this->a->session->data['coupon'] = '3333';
		$this->a->session->data['shipping_method']['cost'] = 10;
		$this->a->session->data['shipping_method']['tax_class_id'] = 10;
		$this->a->applyCoupon( $totals, $total, $tax );

		$this->a->session->data['voucher'] = '1';
		$this->a->applyVoucher( $totals, $total, $tax );

		$this->clean_end();
	}
}
