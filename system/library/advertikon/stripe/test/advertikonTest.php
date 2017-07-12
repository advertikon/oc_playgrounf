<?php

use PHPUnit\Framework\TestCase;

class advertikonTest extends myUnit {

	public function __construct() {
		//require_once 'config.php';
		$this->a = ADK();
	}

	public function test() {
		$this->clean();

		// Advertikon::get_prefix()
		$this->assertEquals( 'advertikon_stripe', $this->a->get_prefix( 'status' ) );
		$this->assertEquals( 'advertikon_stripe', $this->a->get_prefix( 'sort_order' ) );
		$this->assertEquals( 'adk_stripe', $this->a->get_prefix( 'foo' ) );
		$this->assertEquals( 'adk_stripe', $this->a->get_prefix() );

		// Advertikon::check_min_amount()
		$this->assertTrue( $this->a->check_min_amount( '6', 'USD' ) );
		$this->assertTrue( $this->a->check_min_amount( 5, 'USD' ) );
		$this->assertTrue( $this->a->check_min_amount( '6.5', 'USD' ) );
		$this->assertTrue( $this->a->check_min_amount( 5.4, 'USD' ) );
		$this->assertSame( 0.5, $this->a->check_min_amount( 0.1, 'USD' ) );
		$this->assertSame( 0.5, $this->a->check_min_amount( '11,1', 'USD' ) );	

		// Advertikon::set/get_account()
		$this->assertTrue( is_string( $this->a->get_account() ) );
		$this->a->set_account();
		$this->assertTrue( is_string( $this->a->get_account() ) );

		// Advertikon::get_public_key()
		$this->assertTrue( is_string( $this->a->get_public_key() ) );

		// Advertikon::get_secret_key()
		$this->assertTrue( is_string( $this->a->get_secret_key() ) );

		// Advertikon::amount_to_cents()
		$this->assertSame( 1555, $this->a->amount_to_cents( 15.55, 'USD' ) );
		$this->assertSame( 1555, $this->a->amount_to_cents( '15.55', 'USD' ) );	
		$this->assertSame( 15, $this->a->amount_to_cents( 15.55, 'BIF' ) );	
		$this->assertSame( 15, $this->a->amount_to_cents( '15.55', 'bif' ) );
		$this->assertSame( 20, $this->a->amount_to_cents( 20, 'bif' ) );

		// Advertikon::cents_to_amount()
		$this->assertSame( 0.1555, $this->a->cents_to_amount( 15.55, 'USD' ) );
		$this->assertSame( 15.55, $this->a->cents_to_amount( '1555', 'USD' ) );	
		$this->assertSame( 15, $this->a->cents_to_amount( 15.55, 'BIF' ) );	
		$this->assertSame( 15, $this->a->cents_to_amount( '15.55', 'bif' ) );
		$this->assertSame( 20, $this->a->cents_to_amount( 20, 'bif' ) );

		// Advertikon::stringify_stripe_object()
		$this->assertTrue( is_string( $this->a->stringify_stripe_object( array() ) ) );
		$this->assertTrue( is_string( $this->a->stringify_stripe_object( new \stdClass() ) ) );
		$this->assertTrue( is_string( $this->a->stringify_stripe_object( new \Stripe\Card() ) ) );

		// Advertikon::plan_property_common()
		// $this->assertTrue( is_a( $this->a->plan_property_common( 1 ),'Advertikon\Stripe\Resource\Profile' ) );

		// Advertikon::get_oc_plan_by_order();
		$this->a->get_oc_plan_by_order( 1 );

		// Advertikon::set_api_key()
		$this->a->set_api_key();
		$this->a->set_api_key( true );

		// Advertikon::get_price(); !!!!!! We presume that real store currency code is USD !!!!!!!!!
		$o = array(
			'total'         => 1.00,
			'currency_code' => 'EUR'
		);

		$acc = array(
			'default' => array(
				'account_currency' => 'EUR'
			),
			'foo' => array(
				'account_currency' => 'USD'
			),
			'baz' => array(
				'account_currency' => 'bif',
			),
		);

		$old_acc = $this->a->config( 'account' );
		$this->a->config->set( $this->a->prefix_name( 'account' ), $acc );

		// Currency of one of accounts
		$this->assertEquals(
			array( 'currency' => 'EUR', 'amount' => round( $this->a->currency->convert( 1, 'USD', 'EUR' ) * 100 ) ),
			$this->a->get_price( $o )
		);

		// Currency of one of accounts
		$o['currency_code'] = 'usd';
		$this->assertEquals(
			array( 'currency' => 'USD', 'amount' => round( $this->a->currency->convert( 1, 'USD', 'USD' ) * 100 ) ),
			$this->a->get_price( $o )
		);

		// Cents-less currency of one of accounts
		$o['currency_code'] = 'bif';
		$this->assertEquals(
			array( 'currency' => 'BIF', 'amount' => (int)( $this->a->currency->convert( 1, 'USD', 'BIF' ) ) ),
			$this->a->get_price( $o )
		);

		$old_cur_conf = $this->a->config( 'payment_currency' );

		// Currency of default account when there is no suitable currency  - store currency configuration
		$this->a->config->set( $this->a->prefix_name( 'payment_currency' ), Advertikon\Stripe\Advertikon::CURRENCY_STORE );
		$o['currency_code'] = 'FOO';
		$this->assertEquals(
			array( 'currency' => 'EUR', 'amount' => round( $this->a->currency->convert( 1, 'USD', 'EUR' ) * 100 ) ),
			$this->a->get_price( $o )
		);

		// Order currency when there is no suitable currency  - order currency configuration
		$this->a->config->set( $this->a->prefix_name( 'payment_currency' ), Advertikon\Stripe\Advertikon::CURRENCY_ORDER );
		$this->assertEquals(
			array( 'currency' => 'FOO', 'amount' => round( $this->a->currency->convert( 1, 'USD', 'FOO' ) * 100 ) ),
			$this->a->get_price( $o )
		);

		// Order currency when there is no suitable currency  - order currency configuration
		$this->assertEquals(
			array( 'currency' => 'FOO', 'amount' => round( $this->a->currency->convert( 1, 'USD', 'FOO' ) * 100 ) ),
			$this->a->get_price( 1, 'foo' )
		);

		$this->clean_end();

		$this->assertException( function() { $this->a->get_price(); }, 'Advertikon\Exception' );
		$this->assertException( function() { $this->a->get_price( '' ); }, 'Advertikon\Exception' );

		// Invalid setting
		$this->a->config->set( $this->a->prefix_name( 'payment_currency' ), 'FOO' );
		$a = $this->a;
		$this->assertException(
			function() use( $a ) { $a->get_price( $o ); },
			'Advertikon\Exception'
		);

		// Restore setting
		$this->a->config->set( $this->a->prefix_name( 'account'), $old_acc );
		$this->a->config->set( $this->a->prefix_name( 'payment_currency'), $old_cur_conf );

		$this->clean();

		$charge = $a->create_api_charge( array(
			'amount'      => 100,
			'currency'    => 'usd',
			'capture'     => false,
			'description' => 'test',
			'source'      => array(
				'exp_month' => '02',
				'exp_year'  => '2020',
				'number'    => '5555 5555 5555 4444',
				'object'    => 'card',
			),
		) );

		$charge = $a->fetch_api_charge( $charge->id );

		$wh = json_decode( file_get_contents( __DIR__ . '/webhook' ) );

		if ( $wh ) {
			$c = $wh->{'charge.captured'};
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $a->catalog_url( false ) . 'index.php?route=' . $a->type . '/' . $a->code . '/webhooks' );
			curl_setopt($ch, CURLOPT_POST, 1);
			$this->assertTrue( true );

			// Non captured charge
			$c->data->object->id = $charge->id;
			curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $c ) );
			curl_exec( $ch );
			$this->assertTrue( true );

			if($errno = curl_errno($ch)) {
				$error_message = curl_strerror($errno);
				trigger_error( "cURL error ({$errno}):\n {$error_message}" );
			}

			// Capture
			$a->capture_charge( $charge, 100, 1 );

			// Inexisted ID
			$c->data->object->id = 'foo';
			curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $c ) );
			curl_exec( $ch );
			$this->assertTrue( true );

			if($errno = curl_errno($ch)) {
				$error_message = curl_strerror($errno);
				trigger_error( "cURL error ({$errno}):\n {$error_message}" );
			}

			// Real one
			$c->data->object->id = $charge->id;
			curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $c ) );
			curl_exec( $ch );
			$this->assertTrue( true );

			if($errno = curl_errno($ch)) {
				$error_message = curl_strerror($errno);
				trigger_error( "cURL error ({$errno}):\n {$error_message}" );
			}

			// Refund
			$c = $wh->{'charge.refunded'};

			// Unrefunded charge
			$c->data->object->id = $charge->id;
			curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $c ) );
			curl_exec( $ch );
			$this->assertTrue( true );

			if($errno = curl_errno($ch)) {
				$error_message = curl_strerror($errno);
				trigger_error( "cURL error ({$errno}):\n {$error_message}" );
			}

			$a->refund_charge( $charge->id, 100, 1 );

			// Inexistent ID
			$c->data->object->id = 'foo';
			curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $c ) );
			curl_exec( $ch );
			$this->assertTrue( true );

			if($errno = curl_errno($ch)) {
				$error_message = curl_strerror($errno);
				trigger_error( "cURL error ({$errno}):\n {$error_message}" );
			}

			// Real one
			$c->data->object->id = $charge->id;
			curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $c ) );
			curl_exec( $ch );
			$this->assertTrue( true );

			if($errno = curl_errno($ch)) {
				$error_message = curl_strerror($errno);
				trigger_error( "cURL error ({$errno}):\n {$error_message}" );
			}

			foreach( $wh as $c ) {
				curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $c ) );
				curl_exec( $ch );
				$this->assertTrue( true );
			}

			curl_close( $ch );

		} else {
			trigger_error( 'Failed to JSON decode webhooks contents' );
		}

		$this->clean_end();
	}
}
