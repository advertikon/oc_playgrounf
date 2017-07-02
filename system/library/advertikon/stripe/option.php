<?php
/**
 * Advertikon Stripe Option Class
 * @author Advertikon
 * @package Advertikon
 * @version 2.8.11
 */

namespace Advertikon\Stripe;

class Option extends \Advertikon\Option {

	/**
	 * Returns years' sequence $from up to $count counts
	 * @param int $count Years count
	 * @param int|null $from Year to start from
	 * @return array
	 */
	public function get_next_year( $count, $from = null ) {
		$ret = array();

		if ( ! is_numeric( $count ) ) {
			$mess = sprintf( 'Numeric count expected, "%s" given instead', gettype( $count ) );
			trigger_error( $count );
			throw new Exception( $mess );
		}

		
		if ( is_null( $from ) ) {
			$from = date( 'Y' );

		} elseif ( ! is_numeric( $from ) ) {
			$mess = sprintf( 'Expected numeric year value, "%s" given instead', gettype( $from ) );
			trigger_error( $mess );
			throw new Exception( $mess );
		}

		for( $from, $len = $from + $count; $from < $len; $from++ ) {
			$ret[ $from ] = $from;
		}

		return $ret;
	}

	/**
	 * Returns list of available payment options
	 * @return array
	 */
	public function get_payment_option() {
		return array(
			Advertikon::PAYMENT_AUTHORIZE         => ADK( __NAMESPACE__ )->__( 'Authorize' ),
			Advertikon::PAYMENT_AUTHORIZE_CAPTURE => ADK( __NAMESPACE__ )->__( 'Capture' ),
			Advertikon::PAYMENT_FRAUD_CHECK       => ADK( __NAMESPACE__ )->__( 'Authorize if fraud' ),
		);
	}

	/**
	 * Returns list of Stripe accounts
	 * @return array
	 */
	public function get_stripe_account() {
		$ret = array();

		foreach( ADK( __NAMESPACE__ )->config( 'account', array() ) as $code => $account ) {
			$ret[ $code ] = $account['account_name'];
		}

		return $ret;
	}

	/**
	 * Returns list of payment systems supported by Stripe
	 * @return array
	 */
	public function get_payment_system() {
		return array(
			'visa'     => 'Visa',
			'mc'       => 'MasterCard',
			'ae'       => 'American Express',
			'jcb'      => 'JCB',
			'discover' => 'Discover',
			'dc'       => 'Diners Club',
		);
	}

}
