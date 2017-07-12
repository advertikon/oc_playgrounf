<?php
/**
 * Advertikon Short-code Class
 * @author Advertikon
 * @package Stripe
 * @version 2.8.11
 */

namespace Advertikon\Stripe;

class Shortcode extends \Advertikon\Shortcode {

	/**
	 * @see Advertikon\Shortcode::shortcode_name()
	 */
	public function shortcode_name( $name ) {
		if ( ! $name ) {
			return $name;
		}

		// Fall back in case if name is in camel case form (old versions)
		if ( 'customerName' === $name ) {
			$name = 'customer_full_name';

		} elseif ( false === strstr( $name, '_' ) ) {
			$name = ADK( __NAMESPACE__ )->underscore( $name );
		}

		return $name;
	}

}
