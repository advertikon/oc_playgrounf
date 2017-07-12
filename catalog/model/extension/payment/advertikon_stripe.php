<?php
/**
  * Catalog Advertikon Stripe Model
  *
  * @author Advertikon
  * @package Stripe
  * @version 2.8.11
  */

class ModelExtensionPaymentAdvertikonStripe extends Model {

	public $a = null;

	public function __construct( $registry ) {
		parent::__construct( $registry );

		$this->a = Advertikon\Stripe\Advertikon::instance();
	}

	public function getMethod( $address, $total ) {

		// Total is for non-recurring price
		$total = 0;
		$non_recurring_count = 0;
		try{

			// if( ! $this->a->config( 'avail_systems' ) ) {
			// 	throw new Advertikon\Stripe\Exception( 'None of the payment system is enabled' );
			// }

			// Allowed Geo Zones
			$stripe_geo_zones = $this->a->config( 'geo_zone' ) ?: array( '0' );

			// if ( $this->a->is_empty( $stripe_geo_zones ) ) {
			// 	throw new Advertikon\Stripe\Exception( 'Should be selected at least one Stripe geozone' );
			// }

			if ( ! in_array( 0, $stripe_geo_zones ) ) {
				$zones = $this->db->query(
					"SELECT `geo_zone_id` FROM `" . DB_PREFIX . "zone_to_geo_zone`
					WHERE `country_id` = " . (int)$address['country_id'] . "
					AND (`zone_id` = " . (int)$address['zone_id'] . " OR `zone_id` = 0)"
				);

				$z = array();

				// Collect all geo-zones' ID in one-dimensional array
				if ( isset( $zones->rows ) ) {
					foreach( $zones->rows as $zz ) {
						$z[] = $zz['geo_zone_id'];
					}
				}

				if ( ! array_intersect( $z, $stripe_geo_zones ) ) {
					throw new Advertikon\Stripe\Exception( 'Forbidden payment Geo-Zone' );
				}
			}

			// Allowed stores
			$stores = $this->config->get( 'config_store_id' ) ?: array( '0' );
			if( ! array_intersect( array( $this->config->get( 'config_store_id' ), 0 ), $stores ) ) {
				throw new Advertikon\Stripe\Exception( 'Forbidden store' );
			}

			// Allowed customer groups
			$stripe_groups = $this->a->config( 'customer_groups' ) ?: array( '0' );

			if( 
				(
					$this->customer->isLogged() &&
					! array_intersect( array( 0, $this->customer->getGroupId() ), $stripe_groups )
				) ||
				(
					! $this->customer->isLogged() &&
					! array_intersect( array( 0, $this->config->get( 'config_customer_group_id' ) ), $stripe_groups )
				)
			) {
				throw new Advertikon\Stripe\Exception( 'Forbidden customer group' );
			}

			foreach( $this->cart->getProducts() as $product ) {
				$product_total = 0;
				$trial = ! empty( $product['recurring']['trial'] ) &&
							$product['recurring']['trial_cycle'] * $product['recurring']['trial_duration'];

				if ( $product['recurring'] ) {
					$price = ( $trial ? $product['recurring']['trial_price'] :
						$product['recurring']['price'] ) * $product['quantity'];

					$total += $price;
					$product_total = $price;

					$recurring_plan = new Advertikon\Stripe\Resource\OC_Plan( $product['recurring']['recurring_id'] );

					if ( $recurring_plan->profile && $recurring_plan->profile->first_order ) {
						$total += $product['total'];
						$non_recurring_count++;
					}

					if ( $product_total == 0 && $trial ) {
						continue;
					}

					if ( $product_total < $this->a->config( 'total_min' ) ) {
						throw new Advertikon\Stripe\Exception(
							sprintf(
								'The order total amount is: %s, minimum permitted total amount is: %s',
								$this->currency->format( $total, $this->config->get( 'config_currency' ), 1 ),
								$this->currency->format( $this->a->config( 'total_min' ),
									$this->config->get( 'config_currency' ), 1 ) 
							)
						);
					}

				} else {
					$total += $product['total'];
					$non_recurring_count++;
				}
			}

			// Min total amount
			// $total is in Base currency
			if ( $non_recurring_count && $total < $this->a->config( 'total_min' ) ) {
				throw new Advertikon\Stripe\Exception(
					sprintf(
						'The order total amount is: %s, minimum permitted total amount is: %s',
						$this->currency->format( $total, $this->config->get( 'config_currency' ), 1 ),
						$this->currency->format( $this->a->config( 'total_min' ),
							$this->config->get( 'config_currency' ), 1 )
					)
				);
			}

			// Max total amount
			if ( $this->a->config( 'total_max' ) && $total > $this->a->config( 'total_max' ) ) {
				throw new Advertikon\Stripe\Exception(
					sprintf(
						'The order total amount is: %s, maximum permitted total amount is: %s',
						$this->currency->format( $total, $this->config->get( 'config_currency' ), 1 ),
						$this->currency->format( $this->a->config( 'total_max' ),
							$this->config->get( 'config_currency' ), 1 )
					)
				);
			}

		} catch( Advertikon\Stripe\Exception $e ) {
			$this->a->log(
				sprintf( 'Stripe Gateway disabled. Reason: "%s"', $e->getMessage() ),
				$this->a->log_debug_flag
			);

			return false;

		} catch ( Advertikon\Exception $e ) {
			trigger_error( $e->getMessage() );
			$this->a->log(
				sprintf( 'Stripe Gateway disabled. Reason: script error' ),
				$this->a->log_debug_flag
			);

			return false;
		}

		$terms = '';

		if( $this->a->config( 'show_systems' ) && ( $systems = $this->a->config( 'avail_systems' ) ) ) {
			foreach( (array)$systems as $system ) {
				$terms .= '<img src="' . $this->a->get_brand_image( $system ) . '" style="height: 25px!important; width: auto!important;">';
			}

			if( $terms ) {
				$terms = '<span style="padding:3px;vertical-align:sub">' . $terms . '</span>';
			}
		}

		return array(
			'code'       => $this->a->code,
			'title'      => $this->a->config( 'test_mode' ) ?
				$this->a->caption( 'sandbox_title' ) : $this->a->caption( 'title' ),
			'sort_order' => $this->a->config( 'sort_order' ),
			'terms'      => $terms,
		);
	}

	/**
	 * Declare whether extension support recurring payments
	 * @return boolean
	 */
	public function recurringPayments() {
		try {

			if ( ! $this->customer->getId() ) {
				throw new Advertikon\Stripe\Exception( 'Customer is not logged in' );
			}

			foreach( $this->cart->getProducts() as $product ) {
				if ( $product['recurring'] && $product['recurring']['trial_duration'] > 1 ) {
					throw new Advertikon\Stripe\Exception(
						sprintf(
							'The Cart contains product "%s" with recurring plan "%s" which has more than one trial cycles',
							$product['name'],
							$product['recurring']['name']
						)
					);
				}
			}

		} catch( Advertikon\Stripe\Exception $e ) {
			$this->a->log(
				sprintf( 'Unable to handle recurring payment: %s', $e->getMessage() ),
				$this->a->log_debug_flag
			);

			return false;
		}

		return true;
	}

	/**
	 * Authorize and Capture payment
	 * @param Array $order
	 * @param Object $data
	 * @return Object
	 * @throws Exception
	 */
	public function authorize_capture( $order, $data, $type = 'ordinary' ){
		if( 'button' === $type ) {
			return $this->button_charge( $order, $data, true );
		}

		return $this->a->charge( $order, $data, true );
	}

	/**
	 * Authorize payment
	 * @param Array $order
	 * @param Object $data
	 * @return Object
	 * @throws Advertikon\Stripe\Exception
	 */
	public function authorize( $order, $data, $type = 'ordinary' ){
		if( 'button' === $type ) {
			return $this->button_charge( $order, $data );
		}
		
		return $this->a->charge( $order, $data );
	}

	/**
	 * Make payment
	 * @return Object
	 * @throws Advertikon\Stripe\Exception on error
	 */
	public function pay() {
		$order_id = isset( $this->session->data['order_id'] ) ?
			$this->session->data['order_id'] : null;

		if ( is_null( $order_id ) ) { 
			$mess = $this->a->__( 'Order ID is missing' );
			trigger_error( $mess );
			throw new Advertikon\Stripe\Exception( $mess );
		}

		$order_model = $this->a->get_order_model();

		if ( ! $order = $order_model->getOrder( $order_id ) ) {
			$mess = $this->a->__( 'Order is missing' );
			trigger_error( $mess );
			throw new Advertikon\Stripe\Exception( $mess );
		}

		$additional_data = $this->a->get_custom_field( $order_id );

		if ( isset( $additional_data->charge ) ) {
			$mess = $this->a->__( 'The order "#%s" has already been placed', $order_id );
			throw new Advertikon\Stripe\Exception( $mess );
		}

		$this->order = $order;
		$data = $this->validate();

		return $this->make_payment( $order, $data );
	}

	/**
	 * Defines payment method
	 * @return int
	 */
	public function get_payment_method() {
		if( "source_bitcoin" === $this->payment_type || "alipay_account" === $this->payment_type ) {
			$method = Advertikon\Stripe\Advertikon::PAYMENT_AUTHORIZE_CAPTURE;

		} else {
			$method = $this->a->config( 'payment_method' );
		}

		return $method;
	}

	/**
	 * Selects payment method and makes payment
	 * @param array $order 
	 * @param  object $data 
	 * @return object
	 */
	public function make_payment( $order, $data, $type = 'ordinary' ) {
		$method = $this->get_payment_method();
		$ret = null;

		if (
			$method == Advertikon\Stripe\Advertikon::PAYMENT_AUTHORIZE ||
			( $method == Advertikon\Stripe\Advertikon::PAYMENT_FRAUD_CHECK && $this->check_fraud( $order['order_id'] ) )
		) {
			$ret = $this->authorize( $order, $data, $type );

		} else {
			$ret = $this->authorize_capture( $order, $data, $type );
		}

		return $ret;
	}

	/**
	 * Whether to make records at Stripe Dashboard
	 * @return boolean
	 */
	public function store_card_data(){
		if( "saved_card" === $this->payment_type ) {
			return true;
		}

		if( "source_bitcoin" === $this->payment_type ) {
			return false;
		}

		if( "alipay_account" === $this->payment_type && ! $this->payment_reusable ) {
			return false;
		}

		return (
				! empty( $this->request->post['save_card'] ) ||
				$this->cart &&
				$this->cart->hasRecurringProducts()
			) && $this->customer->isLogged();
	}

	/**
	 * Checks whether payment card need to be set as default
	 * @return boolean
	 */
	public function make_card_default() {
		return ! empty( $this->request->post['make_default'] );
	} 

	/**
	 * Validates card token
	 * Create customer record or new card in Stripe Dashboard
	 * @return Object
	 * @throws Advertikon\Stripe\Exception
	 */
	public function validate() {
		$this->a->log( 'Validation start:', $this->a->log_debug_flag );

		$return = new stdClass;
		$token = isset( $this->request->post['token'] ) ? $this->request->post['token'] : '';
		$secret = $this->a->post( 'secret' );
		$crypt_secret = $this->make_secret( $secret );
		$card_pass_setting = $this->a->config( 'saved_card_secret' );

		if( "saved_card" === $this->payment_type && $card_pass_setting && ! $secret ) {
			throw new Advertikon\Stripe\Exception( $this->a->__( 'Password for saved card missing' ) );
		}

		// Throw Exception on empty
		if ( ! $token ) {
			$mess = $this->a->__( 'Card\'s token is empty' );
			trigger_error( $mess );
			throw new Advertikon\Stripe\Exception( $mess );
		}

		$this->payment_type = isset( $this->request->post['type'] ) ? $this->request->post['type'] : '';
		$this->payment_reusable = ! empty( $this->request->post['reusable'] );

		if ( $this->payment_type ) {
			$this->a->log( sprintf( "Payment type is '%s'", $this->payment_type ), $this->a->log_debug_flag );
		}

		// Do not save credit card in Stripe
		if ( ! $this->store_card_data() ) {
			$this->a->log( 'No need to store CC in Stripe Dashboard. Stop validation', $this->a->log_debug_flag );
			$return->token = $token;

			return $return;
		}

		$this->a->log( 'Credit card\'s data need to be stored at Stripe Dashboard', $this->a->log_debug_flag );
		$oc_stripe_customer = new Advertikon\Stripe\Resource\Customer( false );

		// Flag to create new customer
		$need_to_create_new_customer = true;

		// We have already that Stripe customer
		// Obtain stripeApiCard object, stripeApiCustomer Object
		if ( $oc_stripe_customer->stripe_id ) {
			$this->a->log( 'Current customer has record at Stripe Dashboard', $this->log_debug_flag );

	    	// Fetch customer from Stripe
	    	try {
				$stripe_api_customer = $this->a->fetch_api_customer( $oc_stripe_customer->stripe_id );

    			// Check if customer was deleted on Stripe side
	    		if ( ! $stripe_api_customer->deleted ) {
	    			$need_to_create_new_customer = false;
	    		}

	    	// If customer was deleted - exception is thrown
	    	} catch ( Advertikon\Stripe\Exception $e ) {
	  
	    	}

			if ( ! $need_to_create_new_customer ) {

				// Pay using saved card
				if( "saved_card" === $this->payment_type ) {
					if ( $card_pass_setting && ! $secret ) {
						throw new Advertikon\Stripe\Exception( $this->a->__( 'Saved card password missing' ) );
					}

					$stripe_api_card = $this->a->fetch_api_card( $stripe_api_customer, $token );

					if( ! $this->check_secret( $stripe_api_card, $secret ) ) {
						$mess = $this->a->__( 'Invalid password for saved card' );
						trigger_error( $mess );
						throw new Advertikon\Stripe\Exception( $mess );
					}

				// Newly created token
				} else {

		    		// Add new card to customer
		    		$new_api_card = $this->a->add_api_card( $stripe_api_customer, $token, $crypt_secret );

		    		// If card already exists (duplicated fingerprints)
		    		if ( $initial_api_card = $this->a->card_lookup( $stripe_api_customer, $new_api_card ) ) {
		    			/*
			    		 * If that card already exists at Stripe - same fingerprint - delete duplicated card
			    		 * and use initial card's instance stored at Stripe
			    		 * All this mess because of Stripe does not expose card fingerprint at card token - only at card object
		    			 */

		    			// If password for saved card is present - save new password
		    			if ( $secret && $card_pass_setting ) {
		    				$this->a->delete_api_card( $initial_api_card );
		    				$stripe_api_card = $new_api_card;	

		    			} else {
			    			$this->a->delete_api_card( $new_api_card );
			    			$stripe_api_card = $initial_api_card;
		    			
		    			}

		    		} else {
		    			$stripe_api_card = $new_api_card;
		    		}
				}

	    		// Make this card default card if we had such setting
				if ( $this->make_card_default() ) {
					$this->a->set_default_card( $stripe_api_customer, $stripe_api_card->id );
				}
			}
		}

		// If payment made with saved card but customer doesn't exists in Stripe Dashboard - error
		if ( "saved_card" === $this->payment_type && $need_to_create_new_customer ) {
			$mess = $this->a->__( 'You have no saved card' );
			trigger_error( $mess );
			throw new Advertikon\Stripe\Exception( $mess );
		}

		// if we don't have this customer in Stripe - create new Stripe customer
		// obtain stripeApiCard object, stripeApiCustomer object
		if ( $need_to_create_new_customer ) {
			$this->a->log( 'Customer does not exist at Stripe Dashboard', $this->log_debug_flag );

			$shortcode = new Advertikon\Stripe\Shortcode();

	    	$customer_data = array(
				"description" => $shortcode->do_shortcode( $this->a->config( 'customer_description' ) ),
				"source"      => $token,
				"email"       => $shortcode->shortcode_customer_email(),
				"metadata"    => array(),
			);

	    	$stripe_api_customer = $this->a->create_api_customer( $customer_data );

	    	// Default card
	    	$stripe_api_card = $this->a->fetch_api_card( $stripe_api_customer );

	    	/*
	    	 * Make customer lookup. Check against customer email and card fingerprint.
	    	 * If customer is already exists: substitute newly created customer and card by old ones,
	    	 * delete newly created customer and it's card,
	    	 */
	    	if ( $this->a->config( 'check_customer_duplication' ) ) {
	    		$this->a->log( 'Customer lookup will be performed', $this->a->log_debug_flag );

	    		$old_customer = $this->a->customer_lookup( $stripe_api_card, $stripe_api_customer );

	    		if ( count( $old_customer ) === 1 ) {
	    			$old_customer = $old_customer[ 0 ];

	    			// Make substitution only when customer was deleted
	    			if ( $this->a->delete_api_customer( $stripe_api_customer, true ) ) {

	    				$this->a->log( sprintf(
	    					'Customer with ID#%s was substituted by already existing customer instance with ID#%s',
	    					$stripe_api_customer->id,
	    					$old_customer->id
	    				), $this->a->log_debug_flag );

	    				$stripe_api_customer = $old_customer;
	    				$deleted_card_id = $stripe_api_card->id;
	    				$stripe_api_card = $this->a->fetch_api_card( $stripe_api_customer, $stripe_api_card->id );

	    				$this->log( sprintf(
	    					'Card with ID#%s was substituted by already existing card instance with ID#%s',
	    					$deleted_card_id,
	    					$stripe_api_card->id
	    				), $this->a->log_debug_flag );
	    			}

	    		// If payment made with saved card but customer doesn't registered  - throw Exception
	    		} elseif ( count( $old_customer ) > 1 ) {
	    			$mess = $this->a->__(
	    				'More than one instance of the customer is found. Customer substitution aborted due to ambiguity'
	    			);
	    			trigger_error( $mess );
	    		}
	    	}

	    	if ( $secret && $card_pass_setting ) {
	    		$stripe_api_card->metadata = array( 'secret' => $crypt_secret );
	    		$stripe_api_card->save();
	    		$this->cache->delete( $stripe_api_customer->id );
	    	}

	    	if ( $this->customer->isLogged() ) {
	    		$this->a->log( 'Current customer is logged in', $this->a->log_debug_flag );

	    		if ( $customer_id = $this->customer->getId() ){

	    			// Import data from Stripe customer to OC customer
	    			$oc_stripe_customer->oc_customer_id = $customer_id;
		    		$oc_stripe_customer->stripe_id = $stripe_api_customer->id;
		    		// $oc_stripe_customer->date_added = date( 'c' );

		    		if ( $oc_stripe_customer->save() ) {
			    		$this->a->log( 'OC Stripe Customer was updated/created', $this->a->log_debug_flag );

		    		} else {
		    			$this->a->log( 'Customer was not saved into DB', $this->a->log_error_flag );
		    		}

	    		} else {
	    			$this->a->log(
	    				"OC Stripe Customer was not created - empty OC Customer ID",
	    				$this->a->log_error_flag
	    			);
	    		}
	    	}

	    	// Guest session
	    	else {
	    		$this->a->log( 'Current customer is a guest', $this->log_debug_flag );
	    	}
		}

		$return->card = $stripe_api_card;
		$return->customer = $stripe_api_customer;

		return $return;
	}

	/**
	 * Checks password for saved card
	 * @param object $card Card object
	 * @param string $secret Raw password 
	 * @return boolean
	 */
	protected function check_secret( $card, $secret ) {

		// If password for saved cards not needed
		if ( ! $this->a->config( 'saved_card_secret' ) ) {
			return true;
		}

		// Password was not required before but now is required
		if ( ! isset( $card->metadata['secret'] ) ) {
			return true;
		}

		if ( !$secret ) {
			return false;
		}

		return $card->metadata['secret'] === crypt( $secret, $card->metadata['secret'] );
	}

	/**
	 * Crypts password
	 * @param string $secret Password to be crypt 
	 * @return string
	 */
	protected function make_secret( $secret ) {
		if ( CRYPT_BLOWFISH === 1 ) {
			$salt = '$2a$04$sdcvlrfogcjdesofmdsoglsdkfjsd';

		} elseif ( CRYPT_SHA256 === 1 ) {
			$salt = '$5$rounds=5000$verysecretsaltphsadf';

		} elseif ( CRYPT_MD5 === 1 ) {
			$salt = '$1$sdklvfltpdjfhr';

		} else {
			$mess = 'System does not provide supported crypt methods';
			trigger_error( $mess );
			throw new Exception( $mess );
		}

		return crypt( $secret, $salt );
	}

	/**
	 * Returns order status for specific payment option
	 * @param string $status
	 * @return string
	 */
	public function get_order_status( $status ) {
		switch( $status ) {
			case self::STATUS_AUTHORIZED :
				return $this->a->config( 'status_authrized' );
			break;
			case self::STATUS_CAPTURED :
				return $this->a->config( 'status_captured' );
			break;
			case self::STATUS_VOIDED :
				return $this->a->config( 'status_voided' );
			break;
			default : 
				trigger_error( 'Default order status was used, due to undefined configuration value' );
				return $this->a->config( 'status_authrized' );
			break;
		}
	}

	/**
	 * Whether payment will be made not in Order currency
	 * @return boolean
	 */
	public function is_different_payment_currency() {
		return $this->a->get_order_currency() !== $this->get_currency_code();
	}

	/**
	 * Returns current currency code
	 * @return String
	 */
	public function get_currency_code() {
		return $this->session->data['currency'];
	}

	/**
	 * Check order for fraud by means of enabled anti-fraud modules
	 * @param Integer $order_id Order ID
	 * @return String|null
	 */
	public function check_fraud( $order_id ) {
		$order_model = $this->a->get_order_model();
		$order_info = $order_model->getOrder( $order_id );

		if ( $order_info ) {

			// Fraud Detection
			$this->load->model( 'account/customer' );
			$customer_info = $this->model_account_customer->getCustomer( $order_info['customer_id'] );

			if ( $customer_info && $customer_info['safe'] ) {
				$safe = true;

			} else {
				$safe = false;
			}

			if ( ! $safe ) {
				$this->load->model( 'extension/extension' );
				$extensions = $this->model_extension_extension->getExtensions( 'fraud' );

				foreach ( $extensions as $extension ) {
					if ( $this->config->get( $extension['code'] . '_status' ) ) {
						$this->load->model( 'fraud/' . $extension['code'] );
						$fraud_status_id = $this->{'model_fraud_' . $extension['code']}->check( $order_info );

						if ( $fraud_status_id ) {
							return $fraud_status_id === $this->config->get( 'config_fraud_status_id' );
						}
					}
				}
			}
		}

		return false;
	}

	/**
	 * Updates plan related data
	 * @param Object $plan Stripe plan
	 * @throws Advertikon\Stripe\Exception on error
	 */
	public function update_plan( $plan ) {
		$plan_obj = new \Advertikon\Stripe\Resource\Plan( $plan->id, 'sp_plan_id' );

		if ( $plan_obj->is_exists() ) {
			$plan_obj->plan = $plan;
			$plan_obj->save();

		} else {
			$mess = sprintf( 'Stripe recurring plan with ID "%s" doesn\'t exist', $plan->id );
			trigger_error( $mess );
			throw new \Advertikon\Exception( $mess );
		}
	}

	/**
	 * Deletes plan
	 * @param Object $plan Stripe plan
	 * @throws Advertikon\Stripe\Exception on error
	 */
	public function removePlan( $plan ) {
		$plan_obj = new \Advertikon\Stripe\Resource\Plan( $plan->id );

		if ( $plan_obj->is_exists() ) {
			$plan_obj->delete();

		} else {
			$mess = sprintf( 'Stripe recurring plan with ID "%s" doesn\'t exist', $plan->id );
			trigger_error( $mess );
			throw new Advertiko\Exception( $mess );
		}
	}

	

	/**
	 * Returns recurring invoice's descriptions
	 * To paste after order details on checkout
	 * @param array $product List of products
	 * @return String
	 */
	public function get_recurring_invoices( $products ) {
		if ( ! isset( $this->session->data['order_id'] ) ) {
			$mess = $this->a->__( 'Order\'s ID is missing' );
			trigger_error( $mess );
			throw new Advertikon\Exception( $mess );
		}

		$recurring_invoice = '';

		if ( $this->cart->hasRecurringProducts() ) {
	    	$order = $this->a->get_order_model();
	    	$order_info = $order->getOrder( $this->session->data['order_id'] );

			$genue_cart = $this->cart;
			$this->set_bogus_cart();
			$this->cart = $this->bogus_cart;

			$recurring_totals = $this->cart->recurringTotals();
			$one_time_totals = $this->cart->ordinaryTotals();

			$data = array(
				'recurring_totals'		=> $recurring_totals,
				'one_time_totals'		=> $one_time_totals,
				'model'					=> $this,
				'currency'				=> $this->a->get_order_currency(),
			);

			$data['a'] = $this->a;

			$recurring_invoice = $this->load->view(
				$this->a->get_template( $this->a->type . '/advertikon/stripe/recurring_invoice' ),
				$data
			);

			$this->bogus_cart = $this->cart;
			$this->cart = $genue_cart;
		}

		return $recurring_invoice;
	}

	/**
	 * Initializes bogus cart
	 * @return void
	 */
	public function set_bogus_cart() {
		if( ! $this->bogus_cart ) {
			$bogus_cart = new Advertikon\Stripe\BogusCart( $this->registry );
			$bogus_cart->setProducts( $this->cart->getProducts() );
			$this->bogus_cart = $bogus_cart;
		}
	}

	/**
	 * Returns stripe customer|Create Stripe customer if not exists
	 * @param String $token_id Stripe token id
	 * @return Object
	 * @throws Advertikon\Stripe\Exception on gateway error
	 * @throws Advertikon\Exception on system error
	 */
	public function get_customer( $token_id ) {
		$this->a->set_api_key();

		if ( $this->customer && ( $cus_id = $this->customer->getId() ) ) {
			$customer = new Advertikon\Stripe\Resource\Customer( $cus_id );

			if ( $customer->stripe_id ) {
				return $this->a->fetch_api_customer( $customer->stripe_id );

			} else {
				$api_customer = $this->a->create_api_customer( $token_id );
	    		$customer->oc_customer_id = $cus_id;
	    		$customer->stripe_id = $customer->id;
	    		$customer->save();

				return $customer;
			}

		} else {
			return $this->a->create_api_customer( $token_id );
		}
	}

	/**
	 * Creates Charge
	 * @param Array $order
	 * @param Object $data
	 * @param boolean $capture
	 * @return Object
	 * @throws Exception
	 */
	protected function button_charge( $order, $data, $capture = false ){
		$this->a->log( 'Charge start', $this->a->log_debug_flag );

		$price = $this->a->get_price( $order );

		$this->a->log(
			sprintf(
				"Payment of %s %s. Capture - '%s'",
				$price['amount'],
				$price['currency'],
				$capture ? 'true' : 'false'
			),
			$this->log_debug_flag
		);

		if ( ( $min_amount = $this->a->check_min_amount( $price['amount'], $price['currency'] ) !== true ) ) {
			$mess = $this->a->__(
				'Total amount can not be less then %s',
				$this->currency->format( $min_amount, $price['currency'], 1 )
			);
			trigger_error( $mess );
			throw new Advertikn\Stripe\Exception( $mess );
		} 

   		$this->a->set_api_key();

   		$shortcode = new Advertikon\Stripe\Shortcode();
   		$this->session->data['order_id'] = $order['order_id'];

		$charge_obj = array(
			'amount'      => $price['amount'],
			'currency'    => $price['currency'],
			'capture'     => $capture,
			'description' => $shortcode->do_shortcode( $this->a->config( 'charge_description' ) ),		
			'metadata'    => array(
								'order_id' => $order['order_id'],
							),
		);

		unset( $this->session->data['order_id'] );

		if ( empty( $data->token ) ) {
			$mess = $this->a->__( 'Payment details are missing' );
			trigger_error( $mess );
			throw new Advertikon\Exception( $mess );
		}

		$charge_obj['source'] = $data->token;

		if ( $statment_descriptor = $this->a->config( 'statement_descriptor' ) ) {
			$charge_obj['statement_descriptor'] = $statment_descriptor;
		}

		if ( $this->a->config( 'receipt_email' ) ) {
			$charge_obj['receipt_email'] = $order['email'];
		}

		$stripe_api_charge = $this->a->create_api_charge( $charge_obj );

		if ( $capture ) {
			$this->a->mark_order_as_captured(
				$order['order_id'],
				'',
				$stripe_api_charge,
				$this->config->get( 'config_order_mail' ),
				$this->a->config( 'override' )
			);

		} else {
			$this->a->mark_order_as_authorized(
				$order['order_id'],
				'',
				$stripe_api_charge,
				$this->config->get( 'config_order_mail' ),
				$this->a->config( 'override' )
			);
		}

		$this->a->log( 'Charge end', $this->a->log_debug_flag );

		return $stripe_api_charge;
	}

	/**
	 * Subscription payment failure callback
	 * @param Object $invoice Stripe invoice
	 * @throws Advertikon\Stripe\Exception on gateway error
	 * @throws Advertikon\Exception on system error
	 */
	public function subscription_pay( $invoice, $status ) {
		$subscr = $this->a->fetch_api_subscription( $invoice->customer, $invoice->subscription );

		if ( ! isset( $subscr->metadata->recurring_order_id ) ) {
			$mess = sprintf( 'Recurring order\'s ID is missing for subscription %s', $subscr->id );
			trigger_error( $mess );
			throw new Advertikon\Exception( $mess );
		}

		try {
			$this->a->update_subscription( $subscr->metadata->recurring_order_id );

		} catch ( \Exception $e ) {
			
		}

		$this->add_subscription_transaction(
			$subscr->metadata->recurring_order_id,
			$invoice->id,
			$this->currency->convert(
				$this->a->cents_to_amount( $invoice->total, $invoice->currency ),
				$invoice->currency,
				$this->config->get( 'config_currency' )
			),
			$status
		);
	}

	/**
	 * Subscription payment failure callback
	 * @param Object $invoice Stripe invoice
	 * @throws Advertikon\Stripe\Exception on gateway error
	 * @throws Advertikon\Exception on system error
	 */
	public function subscription_pay_fail( $invoice ) {
		$this->subscription_pay( $invoice, 4 );
	}

	/**
	 * Subscription payment succeed callback
	 * @param Object $invoice Stripe invoice
	 * @throws Advertikon\Stripe\Exception on gateway error
	 * @throws Advertikon\Exception on system error
	 */
	public function subscription_pay_succeed( $invoice ) {
		$this->subscription_pay( $invoice, 1 );
	}

	/**
	 * Subscription invoice created callback
	 * @param Object $invoice Stripe invoice
	 * @throws Advertikon\Stripe\Exception on gateway error
	 * @throws Advertikon\Exception on system error
	 */
	public function subscription_invoice_created( $invoice ) {
		$subscr = $this->a->fetch_api_subscription( $invoice->customer, $invoice->subscription );

		if ( ! isset( $subscr->metadata->recurring_order_id ) ) {
			$mess = sprintf( 'Recurring order\'s ID is missing for subscription %s', $subscr->id );
			trigger_error( $mess );
			throw new \Advertikon\Exception( $mess );
		}

		$recurring_id = $subscr->metadata->recurring_order_id;
		$recurring = new \Advertikon\Stripe\Resource\Recurring( $recurring_id, 'recurring_order_id' );

		if ( ! $recurring->is_exists() ) {
			$mess = sprintf( 'Recurring order with ID %s is missing', $recurring_id );
			trigger_error( $mess );
			throw new \Advertikon\Exception( $mess );
		}

		if ( ! isset( $subscr->plan->id ) ) {
			$mess = sprintf( 'Plan is missing for subscription %s', $subscr->id );
			trigger_error( $mess );
			throw new \Advertikon\Exception( $mess );
		}

		$plan = new \Advertikon\Stripe\Resource\Plan( $subscr->plan->id, 'sp_plan_id' );

		if ( ! $plan->is_exists() ) {
			$mess = sprintf( 'Stripe plan with ID %s is not exist', $subscr->plan->id );
			trigger_error( $mess );
			throw new \Advertikon\Exception( $mess );
		}

		$oc_plan = new \Advertikon\Stripe\Resource\Oc_Plan( $plan->oc_plan_id );

		if ( ! $oc_plan->is_exists() ) {
			$mess = sprintf( 'Recurring plan with ID %s is missing', $recurring_id );
			trigger_error( $mess );
			throw new \Advertikon\Exception( $mess );
		}

		// Schedule subscription cancellation if needed
		if ( ! $subscr->cancel_at_period_end && $this->is_last_cycle( $oc_plan, $recurring ) ) {
			$this->a->delete_api_subscription( $subscr->customer, $subscr->id );
		}

		// First invoice
		if ( $invoice->paid ) {
			return; 
		}

		if ( in_array( $invoice->id, explode( ',', $recurring->invoices ) ) ) {
			$mess = sprintf( 'Invoice %s has already been proceeded', $invoice->id );
			trigger_error( $mess );
			throw new \Advertikon\Exception( $mess );
		}

		if ( $subscr->id !== $recurring->subscription_id ) {
			$mess = sprintf( 'Subscription does not match recurring order %s', $recurring_id );
			trigger_error( $mess );
			throw new \Advertikon\Exception( $mess );
		}

		$totals = $this->a->object_to_array( $this->a->json_decode( $recurring->next ) );
		$order_data = $recurring->order;

		if ( ! $order_data ) {
			$mess = sprintf( 'Order is missing for recurring order #%s', $recurring_id );
			trigger_error( $mess );
			throw new Advertikon\Exception( $mess );
		}

		$bogus_cart = \Advertikon\Stripe\BogusCart::instance( $this->a->registry );
		$discounts = array(
			'coupons' => array(),
			'vouchers' => array(),
			'shipping' => false
		);

		foreach( $totals as $total ) {
			if ( $total['code'] === 'coupon' ) {
				$this->apply_coupon( $total, $totals, $recurring, $order_data, $discounts );

			} else if ( $total['code'] === 'voucher' ) {
				$this->apply_voucher( $total, $totals, $discounts );
			}
		}

		$invoice_line = array( 'value' => 0, 'description' => array(), );

		foreach( $totals as $rtt ) {
			if ( $rtt['code'] === 'total' ) {
				continue;
			}

			if ( $rtt['code'] !== 'sub_total' ) {
				$invoice_line['value'] += $rtt['value'];
				$invoice_line['description'][] = $rtt['title'];

			} else {
				$price = $this->a->cents_to_amount( $invoice->total, $invoice->currency );

				if ( ( $dif = $rtt['value'] - $price ) !== 0 ) {
					$invoice_line['value'] += $dif;
				}
			}

		}

		$invoice_line['description'] = implode( ', ', $invoice_line['description'] );

		if ( $invoice_line['value'] ) {
			$invoice_line = $this->a->create_api_invoice_item( array(
				'amount' 		=> $this->a->amount_to_cents( $invoice_line['value'], $invoice->currency ),
				'currency'		=> $invoice->currency,
				'description'	=> $invoice_line['description'],
				'customer'		=> $invoice->customer,
				'invoice'		=> $invoice->id,
			) );

			foreach( $discounts['vouchers'] as $discount ) {
				$this->confirm_voucher( $discount['code'], $discount['discount'], $order_data['order_id'] );
			}

			foreach( $discounts['coupons'] as $discount ) {
				$this->confirm_coupon(
					$discount['code'],
					$discount['discount'],
					$order_data['order_id'],
					$order_data['customer_id']
				);
			}
		}

		$i = explode( ',', $recurring->invoices );
		$i[] = $invoice->id;
		$recurring->invoices = implode( ',', $i );
		$recurring->save();
	}

	/**
	 * Apply coupon
	 * @param Array $line Totals coupon line
	 * @param Array $totals Totals
	 * @param Array $recurring Recurring order data
	 * @param Array $order Order details
	 * @param Array $discounts Discounts list
	 * @throws Advertikon\Stripe\Exception on error
	 */
	protected function apply_coupon( $line, &$totals, $recurring, $order, &$discounts ) {
		$fix_taxes = array();
		$bogus_cart = Advertikon\Stripe\BogusCart::instance( $this->a->registry );

		if ( ! is_a( $recurring, '\Advertikon\Stripe\Resource\Recurring' ) ) {
			$recurring = new Advertikon\Stripe\Resource\Recurring( $recurring['order_recurring_id'] );

		}

		$line['value'] *= -1;
		$discounted = 0;

		if ( $shipping = $bogus_cart->getLine( $totals, 'shipping' ) ) {
			$shipping_tax = $this->a->json_decode( $recurring->shipping_tax );

			if ( ! $shipping_tax ) {
				$mess = sprintf(
					'Shipping tax details are missing for recurring order #%',
					$recurring->order_recurring_id
				);

				trigger_error( $mess );
				throw new Advertikon\Exception( $mess );
			}
		}

		$total_tax = $this->a->json_decode( $recurring->total_tax );

		if ( ! $total_tax ) {
			$mess = sprintf(
				'Total tax details are missing for recurring order #%s',
				$recurring->order_recurring_id
			);

			trigger_error( $mess );
			throw new Advertikon\Exception( $mess );
		}

		try {

			if ( ! preg_match( '/\((\w+)\)/', $line['title'], $m ) || ! isset( $m[ 1 ] ) ) {
				$shippingDif = 0;
				$totalDif = $line['value'];
				$discounted = 0;
				throw new Exception( 'Coupon name missing' );
			}

			$st = $bogus_cart->getLine( $totals, 'sub_total' );

			$coupon_info = $this->get_oc_coupon(
				$m[ 1 ],
				$st['value'],
				$order['customer_id'],
				$recurring->product_id
			);

	    	if ( ! $coupon_info ) {
				$shipping_dif = ! $discounts['shipping'] &&
								isset( $shipping['value'] ) &&
								$this->is_coupon-has_shipping( $m[ 1 ] ) ?
							$shipping['value'] : 0;

				$total_dif = $line['value'] - $shipping_dif;
				$discounted = 0;

				$mess = 'Coupon\'s info are missing';
	    		throw new Advertikon\Exception( $mess );
	    	}

	    	if ( $coupon_info['type'] === 'F' ) {
		    	$discounted = $st['value'];
	    		$discounted = min( $coupon_info['discount'], $discounted );

	    	} else {
	    		$discounted = $st['value'] * $coupon_info['discount'] / 100;
	    	}

			if ( $coupon_info['shipping'] && $shipping ) {
				$discounted += $shipping['value'];
			}

			if ( $discounted !== $line['value'] ) {
				if ( ! $discounts['shipping'] && $shipping ) {
					$shipping_dif = $shippingTax['value'] - ( isset( $shipping['value'] ) ?
						$shipping['value'] : 0 );

				} else {
					$shipping_dif = 0;
				}

				$total_dif = $line['value'] - $discounted - $shipping_dif;

				$mess = 'Different discount amount';
				throw new Advertikon\Exception( $mess );
			}

			$discounted *= -1;

		} catch( Advertikon\Exception $e ) {
			if ( $shipping ) {
				if ( $shipping_dif ) {
					$discpints['shipping'] = true;
				}

				$fix_taxes = array_merge( $fix_taxes, $this->get_taxes( $shipping_dif, $shipping_tax->tax ) );
			}

			$fix_taxes = array_merge( $fix_taxes, $this->get_taxes( $total_dif, $total_tax->tax ) );
			$discounted *= -1;

			$fix_coupon = array(
				array(
					'code' 	=> 'coupon',
					'value'	=> $line['value'],
					'title'	=> $line['title'],
				),
				array(
					'code' 	=> 'coupon',
					'value'	=> $discounted,
					'title'	=> $line['title'],
				),
			);

			$totals = array_merge( $totals, $fix_taxes, $fix_coupon );
			$bogus_cart->fixTotals( $totals );
		}

		if ( $discounted ) {
			$discounts['coupons'][] = array( 'code' => $m[ 1 ], 'discount' => $discounted );
		}
	}

   /**
	* Apply voucher
	* @param Array $line Totals coupon line
	* @param Array $totals Totals
	* @param Array $discounts Discounts list
	* @throws Advertikon\Stripe\Exception on error
	*/
	protected function apply_voucher( $line, &$totals, &$discounts ) {
		$bogus_cart = Advertikon\Stripe\BogusCart::instance( $this->a->registry );
		$voucher_discount = 0;

		try {
			$line['value'] *= -1;

			if ( ! preg_match( '/\((\w+)\)/', $line['title'], $m ) || ! isset( $m[ 1 ] ) ) {
				$voucher_dif = $line['value'];
				$voucher_discount = 0;
				throw new Advertikon\Exception( 'Voucher name is missing' );
			}

			$voucher_info = $bogus_cart->getVoucher( $m[ 1 ] );

			if ( ! $voucher_info ) {
				$voucher_dif = $line['value'];
				$voucher_discount = 0;
				throw new Advertikon\Exception( 'Voucher data is missing' );
			}

			$totals_line = $bogus_cart->getLine( $totals, 'total' );
			$t = $totals_line['value'] + $line['value'];

			$voucher_discount = min( $voucher_info['amount'], $t );

			if ( $line['value'] != $voucher_discount ) {
				$voucher_dif = $line['value'] - $voucher_discount;
				throw new Advertikon\Exception( 'Different voucher amount' );
			}

		} catch( Advertikon\Exception $e ) {
			$fix_voucher = array(
				array(
					'code' 	=> 'voucher',
					'value'	=> $voucher_dif,
					'title'	=> $line['title'],
				),
			);

			$totals = array_merge( $totals, $fix_voucher );
			$bogus_cart->fixTotals( $totals );
		}

		if ( $voucher_discount ) {
			$discounts['vouchers'][] = array( 'code' => $m[ 1 ], 'discount' => $voucher_discount * -1 );
		}
	}

	/**
	 * Defines whether coupon has shipping discount
	 * @param String $code Coupon code
	 * @return Boolean
	 */
	protected function is_coupon_has_shipping( $code ) {
		$shipping = $this->a->q->run_query( array(
			'table'  => 'coupon',
			'query'  => 'select',
			'fields' => 'shipping',
			'where'  => array(
				'field'     => 'code',
				'operation' => '=',
				'value'     => $code,
			),
		) );

		if ( count( $shipping ) ) {
			return (bool)$shipping['shipping'];
		}

		return false;
	}

	/**
	 * Returns taxes
	 * @param Numeric $value Value tax to be calculated for
	 * @param Array $taxRates Tax rates array
	 * @param Boolean $clear Whether to remove fixed taxes
	 * @return Array
	 */
	protected function get_taxes( $value, $tax_rates ) {
		$taxes = array();

		foreach( $tax_rates as $rate ) {
			if ( $rate->type === 'F' ) {
					continue;

			} else {
				$taxes[] = array(
					'code'	=> 'tax',
					'title' => $rate->name,
					'value' => $rate->rate * $value / 100,
				);
			}
		}

		return $taxes;
	}

	/**
	 * Adds voucher history
	 * @param String $code Voucher code
	 * @param Numeric $amount Voucher discount amount
	 * @param Integer $orderId Order ID
	 */
	protected function confirm_voucher( $code, $amount, $order_id ) {
		$this->db->query(
			"INSERT INTO `" . DB_PREFIX . "voucher_history`
			SET `voucher_id` = (SELECT `voucher_id` FROM `" . DB_PREFIX . "voucher`
								WHERE `code` = '" . $this->db->escape( $code ) . "'),
				`order_id` = '" . (int)$order_id . "',
				`amount` = '" . (float)$amount . "',
				`date_added` = NOW()"
		);
	}

	/**
	 * Adds coupon history
	 * @param String $code Voucher code
	 * @param Numeric $amount Voucher discount amount
	 * @param Integer $orderId Order ID
	 * @param Integer $customerId Customer ID
	 */
	protected function confirm_coupon( $code, $amount, $order_id, $customer_id ) {
		$this->db->query(
			"INSERT INTO `" . DB_PREFIX . "coupon_history`
			SET `coupon_id` = (SELECT `coupon_id` FROM `" . DB_PREFIX . "coupon`
								WHERE `code` = '" . $this->db->escape( $code ) . "'),
				`order_id` = '" . (int)$order_id . "',
				`customer_id` = '" . (int)$customer_id . "',
				`amount` = '" . (float)$amount . "',
				`date_added` = NOW()"
		);
	}

	/**
	 * Defines whether current cycle is the last
	 * @param Array $recurring Recurring order data
	 * @return Boolean
	 */
	public function is_last_cycle( $recurring, $recurring_order = null ) {
		if ( is_array( $recurring ) ) {
			$recurring = new \Advertikon\Stripe\Resource\OC_Plan( $recurring['recurring_id'] );
		}

		// Duration is set to "0"
		if ( ! $recurring->duration ) {
			return false;
		}

		if ( $recurring_order && isset( $recurring_order->order['date_added'] ) ) {
			$date_added = $recurring_order->order['date_added'];

		} else {
			$date_added = 'now';
		}

		$start = new DateTime( $date_added );

		if ( $recurring->trial_status || $recurring->trial ) {
			$duration_tr = $recurring->trial_duration * $recurring->trial_cycle;
			$frequency = $recurring->trial_frequency;

	    	if ( strtolower( $frequency ) === 'semi_month' ) {
	    		$s = 'P' . 2 * $duration_tr . 'W';

	    	} else {
	    		$s = 'P' . $duration_tr . strtoupper( substr( $frequency, 0, 1 ) );
	    	}

	    	$start->add( new DateInterval( $s ) );
		}

		$now = new DateTime;
		$frequency = $recurring->frequency;
		$cycle = $recurring->cycle;

		// We still trialling
		if ( $now < $start ) {
			return false;
		}

		$dif = $start->diff( $now );

		if ( $frequency === 'semi_month' ) {
			$l = 'd';
			$cycle *= 14;

		} else if ( $frequency === 'week' ) {
			$l = 'd';
			$cycle *= 7;

		} else {
			$l = strtolower( substr( $frequency, 0, 1 ) );	
		}

		$real_cycles = floor( $dif->$l / $cycle );

		return ( $recurring->duration - $real_cycles ) <= 1;
	}

	/**
	 * Adds subscription transaction line
	 * @param Integer $recurringId Recurring order ID
	 * @param String $invoiceId Stripe invoice ID
	 * @param Numeric $amount Invoice amount
	 * @param String $type Invoice type
	 * @throws Advertikon\Exception on invoice duplication and on error
	 */
	public function add_subscription_transaction( $recurring_id, $invoice_id, $amount, $type ) {

		$r = $this->db->query(
			"SELECT * FROM `" . DB_PREFIX . "order_recurring_transaction`
			WHERE `reference` = '" . $this->db->escape( $invoice_id ) . "'"
		);

		if ( ! $r->num_rows ) {
			$this->db->query(
				"INSERT INTO `" . DB_PREFIX . "order_recurring_transaction`
				( `order_recurring_id`, `reference`, `amount`, `type`, `date_added` )
				VALUES (
					" . (int)$recurring_id . ",
					'" . $this->db->escape( $invoice_id ) . "',
					" . (float)$amount . ",
					'" . $this->db->escape( $type ) . "',
					NOW()
				)"
			);

			if ( $this->db->countAffected() ) {
				return true;

			} else {
				$mess = $this->a->__(
					'Failed to add subscription transaction for invoice %s on amount of %f',
					$invoice_id,
					$amount
				);

				trigger_error( $mess );
				throw new Advertikon\Exception( $mess );
			}

		} else {
			$mess = $this->a->__( 'Invoice #%s has already been processed', $invoice_id );
			throw new \Advertikon\Exception( $mess );
		}
	}

	/**
	 * Returns coupon discount from totals
	 * @param Array $totals Totals list
	 * @return Numeric
	 */
	public function get_coupon_discount( $totals ) {
		foreach( $totals as $total ) {
			if ( $total['code'] === 'coupon' ) {
				return $total['value'];
			}
		}

		return 0;
	}

	/**
	 * Sets trial period to compensate zero total amount due to coupon/voucher application
	 * @param Array $recurring Recurring data
	 * @param Object $plan Stripe plan
	 * @return Integer|Null
	 */
	protected function get_trial_end( $recurring, $plan  ) {
		if ( $plan->trial_period_days ) {
			return null;
		}

		$total = $recurring['charge']['recurring']['total'];

		if ( ! is_null( $total ) && ! $total ) {
			$i = sprintf( 'P%s%s', $plan->interval_count, strtoupper( substr( $plan->interval, 0, 1 ) ) );
			$now = new DateTime;

			return $now->add( new DateInterval( $i ) )->getTimestamp();
		}

		return null;
	}

	/**
	 * Creates Stripe's one time coupon to adjust voucher and/or coupon amount for recurring payment for first payment
	 * @param Array $recurring Recurring charge details
	 * @param String $currency currency code
	 * @return Object|null
	 * @throws Advertikon\Stripe\Exception on gateway error
	 * @throws Advertikon\Exception on system error
	 */
	protected function get_coupon( $recurring, $currency ) {
		$amount = 0;
		$total = null;

		foreach( $recurring['total'] as $line ) {
			if ( in_array( $line['code'], array( 'coupon', 'voucher' ) ) ) {
				$amount += $line['value'];

			} elseif ( $line['code'] == 'total' ) {
				$total = $line['value'];
			}
		}

		if ( is_null( $total ) ) {
			throw new Advertion\Exception( 'Total amount is missing for recurring order' );
		}

		if ( $total === 0 || $amount === 0 ) {
			return null;
		}

		$coupon_data = array(
			'duration'		=> 'once',
			'amount_off'	=> $this->a->amount_to_cents(
				$this->currency->convert( $amount, $this->config->get( 'config_currency' ), $currency ),
				$currency
			),
			'currency'		=> $currency,
		);

		$this->a->set_api_key();

		return $this->a->create_api_coupon( $coupon_data );
	}

	/**
	 * Returns OpenCart's coupon
	 * @param String $couponCode Coupon code to search for
 	 * @return Array|Boolean
	 */
	public function get_oc_coupon( $coupon_code, $sub_total, $customer_id, $product_id ) {
		$coupon_query = $this->db->query(
			"SELECT * FROM `" . DB_PREFIX . "coupon`
			WHERE `code` = '" . $this->db->escape( $coupon_code ) . "'
				AND ((`date_start` = '0000-00-00' OR `date_start` < NOW())
				AND (`date_end` = '0000-00-00' OR `date_end` > NOW()))
				AND `status` = '1'"
		);

		try {

			if ( ! $coupon_query->num_rows ) {
				$mess = $this->a->__( 'Coupon #"%s" does not exist', $coupon_code );
				throw new Advertikon\Exception( $mess );
			}

			if ( $coupon_query->row['total'] > $sub_total ) {
				$mess = $this->a->__(
					'Order\'s amount should be at least "%s" to apply coupon with code "%s"',
					$coupon_query->row['total'],
					$coupon_code
				);
				$this->a->log( $mess, $this->a->log_error_flag );
				throw new Advertikon\Exception( $mess );
			}

			if ( $coupon_query->row['uses_total'] > 0 ) {
				$coupon_history_query = $this->db->query(
					"SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "coupon_history` `ch`
					WHERE `ch`.`coupon_id` = '" . (int)$coupon_query->row['coupon_id'] . "'"
				);

				if ( $coupon_history_query->row['total'] >= $coupon_query->row['uses_total'] ) {
					$mess = $this->a->__(
						'Maximum of "%s" uses per coupon "%s" has been reached',
						$coupon_query->row['uses_total'],
						$coupon_code
					);
					$this->a->log( $mess, $this->a->log_error_flag );
					throw new Advertikon\Exception( $mess );
				}
			}

			if ( $coupon_query->row['logged'] ) {
				// Presume that customer is always logged in since he made recurring order
			}

			if ( $coupon_query->row['uses_customer'] > 0 ) {
				$coupon_history_query = $this->db->query(
					"SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "coupon_history` `ch`
					WHERE `ch`.`coupon_id` = '" . (int)$coupon_query->row['coupon_id'] . "'
					AND `ch`.`customer_id` = '" . (int)$customer_id . "'"
				);

				if ( $coupon_history_query->row['total'] >= $coupon_query->row['uses_customer'] ) {
					$mess = $this->a->__(
						'Maximum of "%s" uses per user has been reached for coupon "%s"',
						$coupon_query->row['uses_customer'],
						$coupon_code
					);
					$this->a->log( $mess, $this->a->log_error_flag );
					throw new Advertikon\Exception( $mess );
				}
			}

			// Products
			$coupon_product_data = array();

			$coupon_product_query = $this->db->query(
				"SELECT * FROM `" . DB_PREFIX . "coupon_product`
				WHERE `coupon_id` = '" . (int)$coupon_query->row['coupon_id'] . "'"
			);

			foreach ( $coupon_product_query->rows as $product ) {
				$coupon_product_data[] = $product['product_id'];
			}

			// Categories
			$coupon_category_data = array();

			$coupon_category_query = $this->db->query(
				"SELECT * FROM `" . DB_PREFIX . "coupon_category` `cc`
					LEFT JOIN `" . DB_PREFIX . "category_path` `cp` ON (`cc`.`category_id` = `cp`.`path_id`)
				WHERE `cc`.`coupon_id` = '" . (int)$coupon_query->row['coupon_id'] . "'"
			);

			foreach( $coupon_category_query->rows as $category ) {
				$coupon_category_data[] = $category['category_id'];
			}

			$product_data = array();

			if ( $coupon_product_data || $coupon_category_data ) {
				if ( in_array( $product_id, $coupon_product_data ) ) {
					$product_data[] = $productId;
				}

				if ( ! $product_data ) {
					foreach ( $coupon_category_data as $category_id ) {
						$coupon_category_query = $this->db->query(
							"SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "product_to_category`
							WHERE `product_id` = '" . (int)$productId . "'
								AND `category_id` = '" . (int)$category_id . "'"
						);

						if ( $coupon_category_query->row['total'] ) {
							$product_data[] = $productId;
							break;
						}
					}
				}

				if ( ! $product_data ) {
					$mess = $this->a->__( 'Coupon not applicable to product or product category' );
					$this->a->log( $mess, $this->a->log_error_flag );
					throw new Advertikon\Exception( $mess );
				}
			}

			return array(
				'coupon_id'     => $coupon_query->row['coupon_id'],
				'code'          => $coupon_query->row['code'],
				'name'          => $coupon_query->row['name'],
				'type'          => $coupon_query->row['type'],
				'discount'      => $coupon_query->row['discount'],
				'shipping'      => $coupon_query->row['shipping'],
				'total'         => $coupon_query->row['total'],
				'product'       => $product_data,
				'date_start'    => $coupon_query->row['date_start'],
				'date_end'      => $coupon_query->row['date_end'],
				'uses_total'    => $coupon_query->row['uses_total'],
				'uses_customer' => $coupon_query->row['uses_customer'],
				'status'        => $coupon_query->row['status'],
				'date_added'    => $coupon_query->row['date_added']
			);

		} catch( Advertikon\Exception $e ) {
			return false;
		}
	}

	/**
	 * Returns next charge date for plan
	 * @param Array $recurring Recurring plan
	 * @return Object
	 */
	public function get_next_plan_date( $recurring ) {
		$now = new DateTime;

		if ( $recurring['trial'] ) {
			$duration = $recurring['trial_duration'] * $recurring['trial_cycle'];
			$frequency = $recurring['trial_frequency'];

		} else {
			$duration = $recurring['cycle'];
			$frequency = $recurring['frequency'];
		}

		if ( strtolower( $frequency ) === 'semi_month' ) {
			$s = 'P' . 2 * $duration . 'W';

		} else {
			$s = 'P' . $duration . strtoupper( substr( $frequency, 0, 1 ) );
		}

		return $now->add( new DateInterval( $s ) );
	}

	/**
	 * Defines whether to create ordinary order for recurring order on first charge
	 * @param String $profileId Product profile ID
	 * @return Boolean
	 */
	protected function is_first_order( $recurring_id ) {
		return (bool)$this->a->plan_property_common( $recurringId )->first_order;
	}

	/**
	 * Returns order total created with pay in one click button
	 * @param int $product_id 
	 * @return array
	 */
	public function get_button_totals( $product_id ) {
		$totals = array();
		$product = ADK()->get_product( $product_id );

		if( $product ) {
			$price = (float)$product['price'];

			if( isset( $product['special'] ) ) {
				$price = min( $price, (float)$product['special'] );
			}

			if( isset( $product['discount'] ) ) {
				$price = min( $price, (float)$product['discount'] );
			}

			$product_price = $price * $product['minimum'];

			$totals[] = array(
				'value'      => $product_price,
				'title'      => $this->a->__( 'Sub total' ),
				'code'       => 'sub_total',
				'sort_order' => 0,
			);

			if( ! empty( $product['tax_class_id'] ) ) {

				// For correct calculation of fixed taxes
				for( $i = 0; $i < $product['minimum' ]; ++$i ) {
	    			$this->get_tax( $totals, $product['tax_class_id'], $price );
				}
			}

	    	if(
	    		$product['shipping'] &&
	    		( $shipping_code = $this->a->config( 'button_shipping' ) ) &&
	    		$this->customer &&
	    		$this->customer->isLogged() &&
	    		$this->config->get( $shipping_code . '_status')
	    	) {

	    		$address_query = $this->db->query(
	    			"SELECT * FROM `" . DB_PREFIX . "address`
	    			WHERE `address_id` = " . (int)$this->customer->getAddressId()
	    		);

	    		if( $address_query && $address_query->num_rows > 0 ) {
	    			if( version_compare( VERSION, '2.3.0.0', '>=' ) ) {
	    				$shipping_module = 'extension/shipping/' . $shipping_code;
	    				$underscored = 'model_extension_shipping_' . $shipping_code;

	    			} else {
	    				$shipping_module = 'shipping/' . $shipping_code;
	    				$underscored = 'model_shipping_' . $shipping_code;
	    			}

	    			$this->load->model( $shipping_module );
	    			$shipping = $this->{$underscored}->getQuote( $address_query->row );

	    			if( $shipping ) {
	    				foreach( $shipping['quote'] as $quote ) {
		    				$totals['shipping'] = array(
		    					'title'      => html_entity_decode( $quote['title'] ),
		    					'value'      => $quote['cost'],
		    					'sort_order' => 1,
		    					'code'       => 'shipping',
		    				);

		    				if( $quote['tax_class_id'] ) {
		    					$this->get_tax( $totals, $quote['tax_class_id'], $quote['cost'] );
		    				}
	    				}
	    			}
	    		}
	    	}
 		}

 		$total = 0;
 		foreach( $totals as $line ) {
 			$total += $line['value'];
 		}

 		$totals[] = array(
 			'code'       => 'total',
 			'title'      => $this->a->__( 'Total' ),
 			'value'      => $total,
 			'sort_order' => 100,
 		);

 		$this->a->sort_by( $totals, 'sort_order' );

		return $totals;
	}

	/**
	 * Calculates taxes
	 * @param array &$totals Totals
	 * @param int $tax_class_id 
	 * @param float $value 
	 * @return void
	 */
	public function get_tax( &$totals, $tax_class_id, $value ) {
		foreach( $this->tax->getRates( $value, $tax_class_id ) as $rate ) {
			$exists = false;

			foreach( $totals as &$total ) {
				if( 'tax' === $total['code'] && $total['title'] === html_entity_decode( $rate['name'] ) ) {
					$total['value'] += $rate['amount'];
					$exists = true;

					break;
				}
			}

			if( ! $exists ) {
				$totals[] = array(
					'title'      => html_entity_decode( $rate['name'] ),
					'value'      => $rate['amount'],
					'sort_order' => 10,
					'code'       => 'tax',
				);
			}
		}
	}

	/**
	 * Makes status-less order for pay button
	 * @param array $data 
	 * @return int Order ID
	 */
	public function place_button_pay_order( $data ) {
		$order_data = array();

		$order_data['totals'] = $this->get_button_totals( $data['product_id'] );
		$order_data['total'] = $this->get_totals_sum( $order_data['totals'], 'total' );
		$product = $this->a->get_product( $data['product_id'] );

		$this->load->language('checkout/checkout');

		$order_data['invoice_prefix'] = $this->config->get('config_invoice_prefix');
		$order_data['store_id'] = $this->config->get('config_store_id');
		$order_data['store_name'] = $this->config->get('config_name');

		if ($order_data['store_id']) {
			$order_data['store_url'] = $this->config->get('config_url');

		} else {
			$order_data['store_url'] = HTTP_SERVER;
		}

		if ($this->customer->isLogged()) {
			$this->load->model('account/customer');

			$customer_info = $this->model_account_customer->getCustomer($this->customer->getId());

			$order_data['customer_id'] = $this->customer->getId();
			$order_data['customer_group_id'] = $customer_info['customer_group_id'];
			$order_data['firstname'] = $customer_info['firstname'];
			$order_data['lastname'] = $customer_info['lastname'];
			$order_data['email'] = $customer_info['email'];
			$order_data['telephone'] = $customer_info['telephone'];
			$order_data['fax'] = $customer_info['fax'];
			$order_data['custom_field'] = json_decode($customer_info['custom_field'], true);
		} else {
			$order_data['customer_id'] = 0;
			$order_data['customer_group_id'] = $this->config->get( 'config_customer_group_id' );
			$order_data['firstname'] = strstr( $data['billing_name'], ' ', true );
			$order_data['lastname'] = substr( strstr( $data['billing_name'], ' ' ), 1 );
			$order_data['email'] = $data['email'];
			$order_data['telephone'] = '';
			$order_data['fax'] = '';
			$order_data['custom_field'] = array();
		}

		$order_data['payment_firstname'] = strstr( $data['billing_name'], ' ', true );
		$order_data['payment_lastname'] = substr( strstr( $data['billing_name'], ' ' ), 1 );
		$order_data['payment_company'] = '';
		$order_data['payment_address_1'] = $data['billing_address_line1'];
		$order_data['payment_address_2'] = '';
		$order_data['payment_city'] = $data['billing_address_city'];
		$order_data['payment_postcode'] = $data['billing_address_zip'];
		$order_data['payment_zone'] = '';
		$order_data['payment_zone_id'] = '';
		$order_data['payment_country'] = $data['billing_address_country'];
		$order_data['payment_country_id'] = ( $country = $this->get_country_by_name( $data['billing_address_country'] ) ) ? $country['country_id'] : '';
		$order_data['payment_address_format'] = '';
		$order_data['payment_custom_field'] = array();

		$order_data['payment_method'] = $this->a->config( 'button_name' );
		$order_data['payment_code'] = 'advertikon_stripe';

		if ( $product['shipping' ] && isset( $data['shipping_address_city'] ) ) {
			$order_data['shipping_firstname'] = strstr( $data['shipping_name'], 'ng ', true );
			$order_data['shipping_lastname'] = substr( strstr( $data['shipping_name'], ' ' ), 1 );
			$order_data['shipping_company'] = '';
			$order_data['shipping_address_1'] = $data['shipping_address_line1'];
			$order_data['shipping_address_2'] = '';
			$order_data['shipping_city'] = $data['shipping_address_city'];
			$order_data['shipping_postcode'] = $data['shipping_address_zip'];
			$order_data['shipping_zone'] = '';
			$order_data['shipping_zone_id'] = '';
			$order_data['shipping_country'] = $data['shipping_address_country'];
			$order_data['shipping_country_id'] = $country = $this->get_country_by_name( $data['shipping_address_country'] ) ?
				$country['country_id'] : '';
			$order_data['shipping_address_format'] = '';
			$order_data['shipping_custom_field'] = array();

			if ( $this->a->config( 'button_shipping' ) &&
				( $shipping_totals = $this->get_total_line( $order_data['totals'], 'shipping' ) ) ) {

				$order_data['shipping_method'] = $shipping_totals[0]['title'];
				$order_data['shipping_code'] = $this->a->config( 'button_shipping' );

			} else {
				$order_data['shipping_method'] = '';
				$order_data['shipping_code'] = '';
			}

		} else {
			$order_data['shipping_firstname'] = '';
			$order_data['shipping_lastname'] = '';
			$order_data['shipping_company'] = '';
			$order_data['shipping_address_1'] = '';
			$order_data['shipping_address_2'] = '';
			$order_data['shipping_city'] = '';
			$order_data['shipping_postcode'] = '';
			$order_data['shipping_zone'] = '';
			$order_data['shipping_zone_id'] = '';
			$order_data['shipping_country'] = '';
			$order_data['shipping_country_id'] = '';
			$order_data['shipping_address_format'] = '';
			$order_data['shipping_custom_field'] = array();
			$order_data['shipping_method'] = '';
			$order_data['shipping_code'] = '';
		}

		$order_data['products'] = array();
		$option_data = array();
		$sub_total = $this->get_totals_sum( $order_data['totals'], 'sub_total' );

		$order_data['products'][] = array(
			'product_id' => $product['product_id'],
			'name'       => $product['name'],
			'model'      => $product['model'],
			'option'     => $option_data,
			'download'   => $product['download'],
			'quantity'   => $product['minimum'],
			'subtract'   => $product['subtract'],
			'price'      => $sub_total / $product['minimum'],
			'total'      => $sub_total,
			'tax'        => $this->tax->getTax( $sub_total / $product['minimum'], $product['tax_class_id'] ),
			'reward'     => $product['reward'] * $product['minimum'],
		);

		// Gift Voucher
		$order_data['vouchers'] = array();
		$order_data['comment'] = '';

		if (isset($this->request->cookie['tracking'])) {
			$order_data['tracking'] = $this->request->cookie['tracking'];

			// Affiliate
			$this->load->model('affiliate/affiliate');

			$affiliate_info = $this->model_affiliate_affiliate->getAffiliateByCode($this->request->cookie['tracking']);

			if ($affiliate_info) {
				$order_data['affiliate_id'] = $affiliate_info['affiliate_id'];
				$order_data['commission'] = ($sub_total / 100) * $affiliate_info['commission'];
			} else {
				$order_data['affiliate_id'] = 0;
				$order_data['commission'] = 0;
			}

			// Marketing
			$this->load->model('checkout/marketing');

			$marketing_info = $this->model_checkout_marketing->getMarketingByCode($this->request->cookie['tracking']);

			if ($marketing_info) {
				$order_data['marketing_id'] = $marketing_info['marketing_id'];

			} else {
				$order_data['marketing_id'] = 0;
			}

		} else {
			$order_data['affiliate_id'] = 0;
			$order_data['commission'] = 0;
			$order_data['marketing_id'] = 0;
			$order_data['tracking'] = '';
		}

		$order_data['language_id'] = $this->config->get('config_language_id');
		$order_data['currency_id'] = $this->currency->getId( $this->session->data['currency'] );
		$order_data['currency_code'] = $this->session->data['currency'];
		$order_data['currency_value'] = $this->currency->getValue( $this->session->data['currency'] );
		$order_data['ip'] = $this->request->server['REMOTE_ADDR'];

		if (!empty($this->request->server['HTTP_X_FORWARDED_FOR'])) {
			$order_data['forwarded_ip'] = $this->request->server['HTTP_X_FORWARDED_FOR'];

		} elseif (!empty($this->request->server['HTTP_CLIENT_IP'])) {
			$order_data['forwarded_ip'] = $this->request->server['HTTP_CLIENT_IP'];

		} else {
			$order_data['forwarded_ip'] = '';
		}

		if (isset($this->request->server['HTTP_USER_AGENT'])) {
			$order_data['user_agent'] = $this->request->server['HTTP_USER_AGENT'];

		} else {
			$order_data['user_agent'] = '';
		}

		if (isset($this->request->server['HTTP_ACCEPT_LANGUAGE'])) {
			$order_data['accept_language'] = $this->request->server['HTTP_ACCEPT_LANGUAGE'];

		} else {
			$order_data['accept_language'] = '';
		}

		$this->load->model('checkout/order');

		return $this->model_checkout_order->addOrder($order_data);
	}

	/**
	 * Returns all total lines with specific code value
	 * @param array $totals 
	 * @param string $code 
	 * @return array
	 */
	public function get_total_line( $totals, $code = 'total' ) {
		$ret = array();

		foreach( (array)$totals as $total ) {
			if( $total['code'] === $code ) {
				$ret[] = $total;
			}
		}

		return $ret;
	}

	/**
	 * Calculates sum of total amounts with specific code value
	 * @param aray $totals
	 * @param string $code 
	 * @return numeric
	 */
	public function get_totals_sum( $totals, $code = 'total' ) {
		$sum = 0;

		foreach( $this->get_total_line( $totals, $code ) as $total ) {
			$sum += $total['value'];
		}

		return $sum;
	}

	/**
	 * Checks whether product has at least one required option
	 * @param array $product 
	 * @return boolean
	 */
	public function has_required_options( $product ) {

		$q = $this->db->query(
			"SELECT * FROM `" . DB_PREFIX . "product_option`
			WHERE `required` = 1
				AND `product_id` = " . (int)$product['product_id']
		);

		return $q->num_rows > 0;
	}

	/**
	 * Fetches country by name
	 * @param string $country_name 
	 * @return array
	 */
	public function get_country_by_name( $country_name ) {
		if( ! $this->a->has_in_cache( 'countries/' . $country_name ) ) {
			$country = array();

			$country_query = $this->db->query(
				"SELECT * FROM `" . DB_PREFIX . "country`
				WHERE LOWER(`name`) = '" . $this->db->escape( strtolower( $country_name ) ) . "'"
			);

			if( $country_query && $country_query->num_rows > 0) {
				$country = $country_query->row;
			}

			$this->a->add_to_cache( 'countries/' . $country_name, $country );

			return $country;
		}

		return $this->a->get_from_cache( 'countries/' . $country_name );
	}
}
?>
