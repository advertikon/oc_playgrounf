<?php
/**
 * Advertikon Stripe Class
 * @author Advertikon
 * @package Advertikon
 * @version 2.8.11  
 */

namespace Advertikon\Stripe;

class Advertikon extends \Advertikon\Advertikon {

	// ******************** Common properties ********************************//
	public $type = 'payment';
	public $code = 'advertikon_stripe';
	public static $c = __NAMESPACE__;
	public $tables = array(
		'customer_table'         => 'advertikon_stripe_customer',
		'plan_table'             => 'advertikon_stripe_plan',
		'profile_table'          => 'advertikon_stripe_profile',
		'profile_map_table'      => 'advertikon_stripe_profile_map',
		'profile_property_table' => 'advertikon_stripe_profile_property',
		'profile_value_table'    => 'advertikon_stripe_profile_value',
		'recurring_table'        => 'advertikon_stripe_recurring', 
	);
	// ******************* End of common properties **************************//

	/**
	 * Payment methods
	 * @var integer
	 */	
	const PAYMENT_AUTHORIZE 		= 0;
	const PAYMENT_AUTHORIZE_CAPTURE	= 1;
	const PAYMENT_FRAUD_CHECK		= 2;

	/**
	 * Currency source for payment
	 * @var integer
	 */
	const CURRENCY_STORE = 1;
	const CURRENCY_ORDER = 2;

	/**
	 * Status constants
	 * @var integer
	 */
	const STATUS_AUTHORIZED	= 1;
	const STATUS_CAPTURED	= 2;
	const STATUS_VOIDED		= 3;

	/**
	 * Maximum count of requests to Stripe Server
	 * @var integer
	 */
	const MAX_REQUEST_COUNT = 10;

	/**
	 * Default currency code for Stripe gateway
	 * @var string
	 */
	const DEFAULT_CURRENCY_CODE = 'USD';

	/**
	 * Minimum total amount for the Gateway
	 * @var numeric
	 */
	const MIN_AMOUNT = 0.5;

	/**
	 * @var String $secretKey Secret key
	 */
	protected $secret_key = null;

	/**
	 * @var String $pablicKey Public key
	 */
	protected $public_key = null;

	/**
	 * @var String $account Current account name
	 */
	protected $account = null;

	/**
	 * Currencies without cent fractional part
	 * @var array
	 */
	protected $non_cents_currency = array( 'BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF', 'VND', 'VUV', 'XAF', 'XOF', 'XPF', );


	// Total amount for recurring payment
	public $invoice_total = null;

	// ********************** Common part ************************************//

	static $instance = null;

	/**
	 * Returns class' singleton
	 * @return object
	 */
	public static function instance( $code = null ) {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
			parent::$instance[ self::$c ] = self::$instance;
		}

		return self::$instance;
	}

	public static $file = __FILE__;

	public function __construct() {
		if ( version_compare( VERSION, '2.3.0.0', '>=' ) ) {
			$this->type = 'extension/' . $this->type;
		}

		parent::__construct();

		$this->data_dir = $this->data_dir . 'stripe/';
		$this->set_account();

		$this->compression_level = parent::COMPRESSION_LEVEL_NONE;
	}


	/**
	 * @see Advertikon\Advertikon::get_version()
	 */
	static public function get_version() {
		return parent::get_version();
	}

	// ************************** Module specific ****************************//

	/**
	 * Returns extension's prefix
	 * @param str $name Name of variable
	 * @return string
	 */
	public function get_prefix( $name = null ) {
		if ( ! is_null( $name ) && in_array( $name, array( 'status', 'sort_order' ) ) ) {
			return $this->code;
		}

		return 'adk_stripe';
	}

	// **************************** Own methods ******************************//

	/**
	 * Check minimum total amount for the Gateway
	 * @param numeric $amount
	 * @param string $currencyCode
	 * @return boolean|numeric
	 */
	public function check_min_amount( $amount, $currency_code ) {
		$amount = is_numeric( $amount ) ? (float)$amount : 0;
		$amount = $this->currency->convert( $amount, $currency_code, self::DEFAULT_CURRENCY_CODE );

		if ( $amount >= self::MIN_AMOUNT ) {
			return true;
		}

		return $this->currency->convert( self::MIN_AMOUNT, self::DEFAULT_CURRENCY_CODE, $currency_code );
	}

	/**
	 * Get active Stripe account slug
	 * @return String
	 */
	public function get_account() {
		return $this->account ? $this->account : 'default';
	}

	/**
	 * Set name of current account
	 * @param String $account Account name (optional)
	 */
	public function set_account( $account = null ) {
		$a_name = '';

		if ( ! $account ) {
			if ( ! defined( 'DIR_CATALOG' ) ) {
				$order_currency = $this->session->data['currency'];

				foreach( $this->config( 'account' ) as $name => $a ) {
					if ( $order_currency === $a['account_currency'] ) {
						$account = $name;
						$a_name = $a['account_name'];

						break;
					}
				}
			}
		}
		
		$this->account = $account ?: 'default';
		$this->log( sprintf( 'Current account is: "%s"', $a_name ?: $this->account ), $this->log_debug_flag );
		$this->set_api_key();
	}

	/**
	 * Get publishable key
	 * @return string
	 */
	public function get_public_key(){
		if ( ! $this->public_key ) {
			$account = $this->get_account();
			$s = $this->config( 'account' );

			$this->public_key = $this->config( 'test_mode' ) ?
				$s[ $account ]['test_public_key'] : $s[ $account ]['live_public_key'];
		}

		return $this->public_key;
	}

	/**
	 * Get secret key
	 * @return string
	 */
	public function get_secret_key(){
		if ( ! $this->secret_key ) {
			if ( ! empty( $_SERVER['TestEnv'] ) && file_exists( $_SERVER['DOCUMENT_ROOT'] . '/k.php' ) ) {
				$this->secret_key = require( $_SERVER['DOCUMENT_ROOT'] . '/k.php' );

			} else {
				$account = $this->get_account();
				$s = $this->config( 'account' );
				$this->secret_key = $this->config( 'test_mode' ) ?
					$s[ $account ]['test_secret_key'] : $s[ $account ]['live_secret_key'];
			}
		}

		return $this->secret_key;
	}

	/**
	 * Convert amount in cents
	 * @param number
	 * @param string
	 */
   	public function amount_to_cents( $amount, $currency ) {
   		$amount = (float)$amount;

   		if ( in_array( strtoupper( $currency ), $this->non_cents_currency ) ) {
   			return (int)$amount;
   		}

   		return (int)round( $amount * 100 );
   	}

   	/**
	 * Convert cents to amount
	 * @param integer
	 * @param string
	 * @return number
	 */
   	public function cents_to_amount( $cents, $currency ){
   		if ( in_array( strtoupper( $currency ), $this->non_cents_currency ) ) {
   			return (int)$cents;
   		}

   		return $cents / 100 ;
   	}

	/**
	 * Lookup for duplicated cards on Customer
	 * @param object $customer Stripe Customer
	 * @param object $card Stripe Card
	 * @return object Duplicated Stripe Card
	 * @throws Stripe\Error\Base
	 */
	public function card_lookup( $customer, $card ) {

		if ( empty( $card->fingerprint ) ) {
			$this->log(
				'Card lookup: searched card fingerprint is missing. Stop lookup',
				$this->log_debug_flag
			);

			return false;
		}

		$duplicated_card = null;

		$cards_list = $this->fetch_api_cards_all( $customer );

		foreach( $cards_list as $old_card ) {
			if ( $card->fingerprint === $old_card->fingerprint && $card->id !== $old_card->id ) {
				$duplicated_card = $old_card;
				$this->log( 'Card lookup: duplicated card found', $this->log_debug_flag );
				break;
			}
		}

		return $duplicated_card;
	}

	/**
	 * Lookup for duplicated Customer records
	 * @param object $card Stripe\Card object
	 * @param object $customer Stripe\Customer object to search against
	 * @return array Duplicate customers
	 * @throws Advertikon\Stripe\Exception
	 * @throws Stripe\Error\Base
	 */
	public function customer_lookup( $card, $customer ) {
		$duplicated_customer = array();

		if ( ! $card->fingerprint ) {
			$this->log(
				'Customer lookup: fingerprint of searched card is missing. Abort lookup',
				$this->log_debug_flag
			);

			return $duplicated_customer;
		}

		if ( ! $customer->email ) {
			$this->log(
				'Customer lookup: email of searched customer is missing. Abort lookup',
				$this->log_debug_flag
			);

			return $duplicated_customer;
		}

		if ( ! $customer->id ) {
			$this->log(
				'Customer lookup: id of searched customer is missing. Abort lookup',
				$this->log_debug_flag
			);

			return $duplicated_customer;
		}

		foreach( $this->fetch_api_customer_all() as $old_customer ) {
			if ( $old_customer->id === $customer->id || $old_customer->email !== $customer->email ) {
				continue;
			}

			foreach( $this->fetch_api_cards_all( $old_customer ) as $old_card ) {
				if ( $card->fingerprint === $old_card->fingerprint ) {
					$duplicated_customer[] = $old_customer;
	    			$this->log(
	    				sprintf(
	    					'Customer lookup: found duplicated customer with ID#%s',
	    					$old_customer->id
	    				),
	    				$this->log_debug_flag
	    			);
	    			break;
				}
			}
		}

		$this->log(
			sprintf(
				'Customer lookup: found %d duplicated entries',
				count( $duplicated_customer )
			),
			$this->log_debug_flag
		);

		return $duplicated_customer;
	}

	/**
	 * Returns order's currency code depend on setting
	 * @return String
	 */
	public function get_order_currency( $default_currency = null  ) {

		// If order contains at least one recurring product - select currency of default account
		if ( ! defined( 'DIR_CATALOG' ) && $this->cart->hasRecurringProducts() ) {
			return $this->get_recurring_currency();
		}

		$s = $this->config( 'account' );

		if ( is_null( $default_currency ) && defined( 'DIR_CATALOG' ) ) {
			$mess = 'You need to specify default currency when you at back-end';
			trigger_error( $mess );
			throw new \Advertikon\Exception( $mess );
		}

		$order_currency = $default_currency ?: $this->session->data['currency'];

		foreach( $s as $account ) {
			if ( $account['account_currency'] === $order_currency ) {
				return $order_currency;
			}
		}

		if ( \Advertikon\Stripe\Advertikon::CURRENCY_STORE == $this->config( 'payment_currency' ) ) {
			$order_currency = $s['default']['account_currency'];
		}

		return $order_currency;
	}

	/**
	 * Returns currency code for recurring payment
	 * @return String
	 */
	public function get_recurring_currency() {
		$account = $this->config( 'account' );

		return $account['default']['account_currency'];
	}

	/**
	 * Creates Stripe Charge
	 * @param array $order OC order
	 * @param object $data Order payment details
	 * @param boolean $capture Flag to capture payment
	 * @return object Charge object
	 * @throws Advertikon\Stripe\Exception
	 * @throws Stripe\Error\Base
	 */
	public function charge( $order, $data, $capture = false ) {
		$this->log( 'Charge start:', $this->log_debug_flag );

		$price = $this->get_price( $order );
		$one_time = false;

		if ( $this->cart->hasRecurringProducts() ) {

			if (
				! isset( $this->session->data['adk_recurring'] ) ||
				! isset( $this->session->data['adk_totals'] )
			) {
				$mess = 'Missing session data for recurring payment';
				trigger_error( $mess );
				throw new \Advertikon\Exception( $mess );
	    	}

	    	$one_time = isset( $this->session->data['adk_one_time'] ) ?
	    		$this->session->data['adk_one_time'] : null;

			if ( ! isset( $data->customer ) ) {
				$data->customer = $this->create_customer( $data->token );
				unset( $data->token );
			}

			//Set currency for charges
			$currency = $this->get_order_currency();

	    	//Fix order totals
	    	$this->db->query(
	    		"DELETE FROM `" . DB_PREFIX . "order_total`
	    		WHERE `order_id` = '" . (int)$order['order_id'] . "'"
	    	);

	    	foreach ( $this->session->data['adk_totals'] as $total ) {
	    		if ( $total['code'] === 'total' ) {
	    			$t = $total['value'];
	    		}

	    		$this->db->query(
	    			sprintf(
	    				"INSERT INTO `%sorder_total` (`order_id`, `code`, `title`, `value`, `sort_order` )
	    					VALUES( '%u', '%s', '%s', '%F', '%u' ) ",
	    				DB_PREFIX,
	    				$order['order_id'],
	    				$this->db->escape( $total['code'] ),
	    				$this->db->escape( $total['title'] ),
	    				$this->db->escape( $total['value'] ),
	    				$this->db->escape( $total['sort_order'] )
	    			)
	    		);

	    		// TODO: make roll-back of order totals
	    		if ( ! $this->db->countAffected() ) {
	    			$mess = sprintf(
	    				'Failed to add total line with code "%s" to totals of the order #%s',
	    				$total['code'],
	    				$order['order_id']
	    			);

	    			trigger_error( $mess );
	    			throw new \Advertikon\Exception( $mess );
	    		}
	    	}

	    	$this->db->query(
	    		sprintf(
	    			'UPDATE `%sorder` SET `total` = %F WHERE `order_id` = %u',
	    			DB_PREFIX,
	    			$t,
	    			$order['order_id']
	    		)
	    	);

	    	$o = $this->get_order_model();
	    	$order = $o->getOrder( $order['order_id'] );

	    	if ( $one_time ) {

		    	// Get amount for one time charge
		    	$one_time_total = isset( $one_time['charge']['total'] ) ? $one_time['charge']['total'] : 0;

		    	$price = array(
		    		'currency' => $currency,
		    		'amount' => $this->amount_to_cents(
		    			$this->currency->convert( $one_time_total, $this->config->get( 'config_currency' ), $currency ),
		    			$currency
		    		)
		    	);

		    	if ( $min_amount = $this->check_min_amount( $price['amount'], $price['currency'] ) !== true ) {
		    		throw new Exception(
		    			$this->__(
		    				'Total amount can not be less then %s',
		    				$this->currency->format( $min_amount, $price['currency'], 1 )
		    			)
		    		);
		    	} 
	    	}

	    	// Create all the recurring charges
	    	foreach( $this->session->data['adk_recurring'] as $recurring ) {
	    		$this->charge_recurring( $recurring, $data, $currency );
	    	}

	    	unset( $this->session->data['adk_recurring'], $this->session->data['adk_totals'] );
		}

		$this->log(
			sprintf(
				"Payment of %s %s. Capture - '%s'",
				$price['amount'],
				$price['currency'],
				$capture ? 'true' : 'false'
			),
			$this->log_debug_flag
		);

		if (
			! is_null( $one_time ) &&
			( $min_amount = $this->check_min_amount( $price['amount'], $price['currency'] ) !== true )
		) {
			throw new Exception(
				sprintf(
					'Total amount can not be less then %s',
					$this->currency->format( $minAmount, $price['currency'], 1 )
				)
			);
		} 

   		$this->set_api_key();
   		$shortcode = new Shortcode();

		$charge_obj = array(
			'amount'      => $price['amount'],
			'currency'    => $price['currency'],
			'capture'     => $capture,
			'description' => $shortcode->do_shortcode( $this->config( 'charge_description' ) ),		
			'metadata'    => array(
								'order_id' => $order['order_id'],
							),
		);

		if ( empty( $data->token ) && empty( $data->customer) ) {
			$mess = $this->__( 'Payment details are missing' );
			trigger_error( $mess );
			$this->log( $data, $this->log_debug_flag );
			throw new \Advertikon\Exception( $data );
		}

		if ( isset( $data->token ) ) {
			$charge_obj['source'] = $data->token;
		}

		if ( isset( $data->customer ) ) {
			$charge_obj['customer'] = $data->customer->id;
		}

		if ( ! empty( $data->card ) ) {
			$charge_obj['source'] = $data->card->id;
		}

		if ( $statment_descriptor = $this->config( 'statement_descriptor' ) ) {
			$charge_obj['statement_descriptor'] = $statment_descriptor;
		}

		if ( $this->config( 'receipt_email' ) && ( $email = $shortcode->shortcode_customer_email() ) ) {
			$charge_obj['receipt_email'] = $email;
		}

		if ( is_null( $one_time ) ) {
			$stripe_api_charge = new \stdClass;
			$stripe_api_charge->bogus = true;

		} else {
			$stripe_api_charge = $this->create_api_charge( $charge_obj );
		}

		if ( $capture ) {
			$this->mark_order_as_captured(
				$order['order_id'],
				isset( $this->session->data['comment'] ) ? $this->session->data['comment'] : '',
				$stripe_api_charge,
				$this->config->get( 'config_order_mail' ),
				$this->config( 'override' )
			);

		} else {
			$this->mark_order_as_authorized(
				$order['order_id'],
				isset( $this->session->data['comment'] ) ? $this->session->data['comment'] : '',
				$stripe_api_charge,
				$this->config->get( 'config_order_mail' ),
				$this->config( 'override' )
			);
		}

		$this->log( 'Charge end', $this->log_debug_flag );

		if ( $one_time ) {
			unset( $this->session->data['adk_one_time'] );
		}

		return $stripe_api_charge;
	}

	/**
	 * Creates recurring charge
	 * @param Array $recurring Recurring data
	 * @param Object $data Payment details data
	 * @param String $currency Currency code in which made order
	 * @return Integer
	 * @throws Advertikon\Stripe\Exception on payment gateway error
	 * @throws Advertikon\Exception on system error
	 */
	protected function charge_recurring( $recurring, $data, $currency ) {
		$rec = $this->load->model( 'checkout/recurring' );
		$rec_id = $this->model_checkout_recurring->create(
			$recurring['product'],
			$this->session->data['order_id'],
			$recurring['product']['recurring']['name']
		);

		$plan = $this->get_stripe_plan( $recurring['product']['recurring'], $currency );
		$one_time_invoice_line = null;

		if ( ! empty( $recurring['charge']['one_time']['total'] ) ) {
			$c = $recurring['charge']['one_time'];
			$one_time_invoice_line = $this->create_api_invoice_item( array(
				'amount'      => $this->amount_to_cents(
					$this->currency->convert(
						$c['total'],
						$this->config->get( 'config_currency' ),
						$currency
					),
					$currency
				),
				'currency'    => $currency,
				'description' => 'One-time setup fee',
				'customer'    => $data->customer->id,
			) );
		}

		$delete = false;

		try {
	    	$invoice_line = null;

	    	if ( $recurring['charge']['recurring']['invoice_line']['value'] ) {
	    		$c = $recurring['charge']['recurring']['invoice_line'];
	    		$invoice_line = $this->create_api_invoice_item( array(
	    			'amount'     => $this->amount_to_cents(
	    				$this->currency->convert(
	    					$c['value'],
	    					$this->config->get( 'config_currency' ),
	    					$currency
	    				),
	    				$currency
	    			),
	    			'currency'    => $currency,
	    			'description' => $c['description'],
	    			'customer'    => $data->customer->id,
	    		) );
	    	}

		} catch( \Advertikon\Exception $e ) {
			$delete = true;

		} catch ( \Stripe\Error\Base $e ) {
			$delete = true;
		}

		if ( $delete ) {
			if ( $one_time_invoiceLine ) {
				$one_time_invoice_line->delete();
			}

			$this->delete_oc_recurring( $rec_id );

			$mess = $this->__( 'Failed to create invoice item for one-time charge' );
			trigger_error( $mess );
			throw new \Advertikn\Exception( $mess );
		}

		$next_sub_total = 0;
		$shipping = 0;

		foreach( $recurring['next'] as $next ) {
			if ( $next['code'] === 'sub_total' ) {
				$next_sub_total = $next['value'];

			} else if ( $next['code'] === 'shipping' ) {
				$shipping = $next['value'];
			}
		}

		$next_sub_total = $this->currency->convert(
			$next_sub_total,
			$this->config->get( 'config_currency' ),
			$currency
		);

		$s = array();

		if ( $shipping ) {
			$shipping = $this->currency->convert(
				$shipping,
				$this->config->get( 'config_currency' ),
				$currency
			);

			$s['value'] = $shipping;
			$s['tax'] = isset( $this->session->data['shipping_method']['tax_class_id'] ) ?
				$this->tax->getRates( $shipping, $this->session->data['shipping_method']['tax_class_id'] ) : array();
		}

		$subscr_data = array(
			'plan'     => $plan->id,
			'quantity' => $recurring['product']['quantity'],
			'metadata' => array(
				'recurring_order_id' => $rec_id,
				),
			);

		foreach( $recurring['next'] as &$line ) {
			$line['value'] = $this->currency->convert(
				$line['value'],
				$this->config->get( 'config_currency' ),
				$currency
			);
		} 

		$delete = false;

		try {
			$recurring_obj = new \Advertikon\Stripe\Resource\Recurring();
			$subscription = $this->create_api_subscription( $data->customer->id, $subscr_data );

			$recurring_obj->recurring_order_id = $rec_id;
			$recurring_obj->next = $this->json_encode( $recurring['next'] );
			$recurring_obj->total_tax = $this->json_encode( array(
				'value' => $next_sub_total,
				'tax'   => $this->tax->getRates( $next_sub_total, $recurring['product']['tax_class_id'] )
			) );
			$recurring_obj->shipping_tax = $this->json_encode( $s );
			$recurring_obj->subscription_id = $subscription->id . ':' . $this->get_account();
			$recurring_obj->save();

		} catch( \Advertikon\Exception $e ) {
			$delete = true;

		} catch ( \Stripe\Error\Base $e ) {
			$delete = true;
		}

		if ( $delete ) {
			if ( $one_time_invoice_line ) {
				$one_time_invoice_line->delete();
			}

			if ( $invoice_line ) {
				$invoice_line->delete();
			}

			$this->delete_oc_recurring( $rec_id );

			$mess = $this->__( 'Failed to create recurring subscription' );
			trigger_error( $mess );
			throw new \Advertikon\Exception( $mess );
		}

		return $rec_id;
	}

	/**
	 * Deletes OpenCart recurring order
	 * @param int $id Order ID
	 * @return void
	 */
	public function delete_oc_recurring( $id ) {
		$this->db->query( "DELETE FROM `" . DB_PREFIX . "recurring` WHERE `order_recurring_id` = " . (int)$id );
	}

	/**
	 * Returns stripe plan object or create new if not exists
	 * @param Array $plan OpenCart recurring order data
	 * @param String $currency Currency code
	 * @return Object
	 * @throws Advertikon\Stripe\Exception on error
	 */
	protected function get_stripe_plan( $recurring, $currency ) {
		$r = $recurring;
		$plan_res = new \Advertikon\Stripe\Resource\Plan( $r['recurring_id'], 'oc_plan_id' );
		$update = false;

		// If OC pan mapped to SP plan
		$plan_id = $plan_res->is_exists() ? $plan_res->sp_plan_id : $r['recurring_id'] . '_' . strtolower( $currency );

		try {
			$plan = $this->fetch_api_plan( $plan_id );

			if ( ! $this->compare_plans( $plan, $r, $currency ) ) {
				$this->log( 'Plans do not match', $this->log_debug_flag );
				$plan_id .= uniqid();
				throw new \Stripe\Error\Api( 'Need new' );
			}

			$this->log( 'Plans match', $this->log_debug_flag );

		// If plan does not exists at Stripe Dashboard or it does not match OC one
		} catch( \Stripe\Error\Base $e ) {
			$oc_trial_status = isset( $r['trial_status'] ) ?
				$r['trial_status'] : ( isset( $r['trial'] ) ? $r['trial'] : false );

			$data = array(
					'id'                   => $plan_id,
					'amount'               => $this->amount_to_cents(
						$this->currency->convert(
							$r['price'],
							$this->config->get( 'config_currency' ),
							$currency
						),
						$currency
					),
					'currency'             => $currency,
					'interval'             => strtolower( $r['frequency'] ) === 'semi_month' ?
						'week' : $r['frequency'],

					'name'                 => $r['name'],
					'interval_count'       => strtolower( $r['frequency'] ) === 'semi_month' ?
						$r['cycle'] * 2 : $r['cycle'],

					'metadata'             => array(
						'account' => $this->get_account(),
					),
					'statement_descriptor' => substr( $r['name'], 0, 22 ),
					'trial_period_days'    => $oc_trial_status ? $this->get_trial_days( $r ) : null,
				);

			$plan = $this->create_api_plan( $data );
			$update = true;
		}

		if ( $update || ! $plan_res->is_exists() ) {
			$plan_res->oc_plan_id = $r['recurring_id'];
			$plan_res->sp_plan_id = $plan->id;
			$plan_res->plan = $plan;
			$plan_res->save();
			$this->log( 'Stripe\'s recurring plan was updated', $this->log_debug_flag );
		}

		return $plan;
	}

	/**
	 * Returns order price (in cents, at current period ) and currency depend on settings
	 * @param Array|String $orderOrTotal Order information or order total in store currency
	 * @param String|null $orderCurrency Order currency string (optional)
	 * @return Array
	 * @throws Advertikon\Exception on error
	 */
	public function get_price( $order_or_total, $order_currency = null ) {
		$price = array();
		$amount = '';

		// Order passed
		if ( is_array( $order_or_total ) ) {
			$amount = $order_or_total['total'];
			$order_currency = $order_or_total['currency_code']; 
		}

		// Amount value passed
		else {
			$amount = $order_or_total;
		}

		if ( $amount === '' || is_null( $order_currency ) ) {
			$mess = 'Missing amount or currency code';
			trigger_error( $mess );
			throw new \Advertikon\Exception( $mess );
		}

		$order_currency = strtoupper( $order_currency );
		$store_currency =  $this->config->get( 'config_currency' );
		$s = $this->config( 'account' );
		$price['currency'] = $order_currency;
		$found = false;

		foreach( $s as $account ) {
			if ( strtoupper( $account['account_currency'] ) === $order_currency ) {
				$found = true;
				break;
			}
		}

		if ( ! $found ) {
			switch( $this->config( 'payment_currency' ) ) {
				case self::CURRENCY_ORDER :
				break;
				case self::CURRENCY_STORE :
					$price['currency'] 	= strtoupper( $s['default']['account_currency'] );
				break;
				default :
					$mess = 'Failed to determine payment currency';
					trigger_error( $mess );
					throw new \Advertikon\Exception( $mess );
				break;
			}
		}

		$price['amount'] = $this->amount_to_cents(
			$this->currency->convert( $amount, $store_currency, $price['currency'] ),
			$price['currency']
		);

		return $price;
	}

	/**
	 * Updates OC subscription status corresponding to Stripe subscription's status
	 * @param object $subscr Stripe subscription
	 * @throws Advertikon\Exception on error
	 */
	public function update_subscription_status( $subscr ) {
		if ( ! isset( $subscr->metadata->recurring_order_id ) ) {
			$mess = 'Recurring oder ID is missing in subscription\'s meta-data';
			$this->log( $mess, $this->log_error_flag );
			$this->log( $subscr, $this->log_debug_flag );
			throw new Exception( $mess );
		}

		$order_id = $subscr->metadata->recurring_order_id;

		if ( ! isset( $subscr->status ) ) {
			$mess = 'Subscription status is missing';
			$this->log( $mess, $this->log_error_flag );
			$this->log( $subscr, $this->log_debug_flag );
			throw new Exception( $mess );
		}

		$recurring = new Resource\Recurring();
		$oc_recurring_order = $recurring->get_oc_recurring_order( $order_id );

		if ( ! $oc_recurring_order ) {
			$mess = sprintf( 'Recurring subscription with ID %s doesn\'t exist', $order_d );
			$this->log( $mess, $this->log_error_flag );
			$this->log( $subscr, $this->log_debug_flag );
			throw new Exception( $mess );
		}

		$oc_stat = $recurring->get_subscription_status( $subscr->status );

		if ( $oc_stat !== $oc_recurring_order['status'] ) {
			$recurring->update_oc_order_status( $oc_stat, $order_id );
		}
	}

	/**
	 * Synchronize Stripe subscription with OC plan
	 * @param int $recurring_order_id Recurring order ID
	 * @throws Advertikon\Stripe\Exception on error
	 */
	public function update_subscription( $recurring_order_id ) {
		$recurring = new Resource\Recurring( $recurring_order_id, 'recurring_order_id' );

		if ( ! $recurring->is_exists() ) {
			$mess = sprintf( 'Subscription with ID %s is missing', $recurring_order_id );
			$this->log( $mess, $this->log_error_flag );
			throw new Exception( $mess );
		}

		$stripe_customer_id = $recurring->get_stripe_customer_id();
		$this->set_account( $recurring->get_account_name() );

		try{
			$subscr = $this->fetch_api_subscription( $stripe_customer_id, $recurring->get_subscription_id() );

		} catch( \Stripe\Error\Base $e ) {
			$subscr = new \stdClass;
			$subscr->metadata = new \stdClass;
			$subscr->metadata->recurring_order_id = $recurring_order_id;
			$subscr->status = 'canceled';
		}

		$this->update_subscription_status( $subscr );
	}

	/**
	 * Cancels Stripe subscription
	 * @param int $recurring_order_id Stripe recurring order ID
	 * @throws Advertikon\Stripe\Exception on error
   	 */
	public function cancel_recurring( $recurring_order_id, $at_period_end = true ) {
		$recurring = new Resource\Recurring();
		$recurring->load( $recurring_order_id, 'recurring_order_id' );

		if ( ! $recurring->is_exists() ) {
			$mess = sprintf( 'Subscription with ID %s is missing', $recurring_order_id );
			$this->log( $mess, $this->log_error_flag );
			throw new Exception( $mess );
		}

		$stripe_customer_id = $recurring->get_stripe_customer_id();
		$this->set_account( $recurring->get_account_name() );
		$this->delete_api_subscription( $stripe_customer_id, $recurring->subscription_id, $at_period_end );
	}

	/**
	 * Mark order as authorized
	 * @param int $order_id Order ID
	 * @param string $comment Comment
	 * @param object $charge Charge object
	 */
	public function mark_order_as_authorized( $order_id, $comment, $charge, $email, $override ) {
		$order_model = $this->get_order_model();

		$order_model->addOrderHistory(
			$order_id,
			$this->config( 'status_authorized' ),
			$comment,
			$email,
			$override
		);

		if ( is_a( $charge, '\Stripe\StripeObject' ) ) {
			$charge = $charge->__toArray( true );
		}

		$this->add_custom_field( $order_id, 'charge', $charge  );
		$this->add_custom_field( $order_id, 'account', $this->get_account() );
	}

	/**
	 * Check charge object retrieved from web-hook
	 * @param Object $evt Web-hook event object
	 * @return Object
     * @throws Advertikon\Stripe\Exception on error
 	 */
	public function check_webhook_charge( $evt ) { 
		if ( ! is_object( $evt ) || ! isset( $evt->data->object ) ) {
			throw new \Advertikon\Exception( 'Charge object missed' );
		}

		$charge = $evt->data->object;

		if ( ! is_object( $charge ) || $charge->object !== 'charge' || ! $charge->id ) {
			throw new \Advertikon\Exception( sprintf( 'Web-hook object is not a charge: "%s"', $charge->object ) );
		}

		if ( ! isset( $charge->metadata->order_id ) ) {
			throw new \Advertikon\Exception( 'Order ID is missing' );
		}

		$order_id = $charge->metadata->order_id;
		$o = $this->get_order_model();
		$order = $o->getOrder( $order_id );

		if ( ! $order ) {
			throw new \Advertikon\Exception( sprintf( 'Order with ID "%s" is missing', $order_id ) );
		}

		$ch = $this->get_custom_field( $order );

		if ( ! is_object( $ch ) ) {
			$ch = json_decode( json_encode( $ch ) );
		}

		if ( isset( $ch->charge ) ) {
			$ch = $ch->charge;

		} else {
			$ch = null;
		}

		if ( ! $ch ) {
			throw new \Advertikon\Exception(
				sprintf( 'Order "%s" has not been payed yet (charge object missing)', $order_id )
			);
		}

		if ( $ch->id !== $charge->id ) {
			throw new \Advertikon\Exception(
				sprintf(
					'Charge mismatch: order "%s" has charge "%s", web-hook supply charge "%s"',
					$order_id,
					$ch->id,
					$charge->id
				)
			);
		}

		if ( $ch->livemode !== $evt->livemode ) {
			throw new \Advertikon\Exception(
				sprintf(
					'Charge was made in %s mode, but event in %s mode',
					$ch->livemode ? 'live' : 'test',
					$evt->livemode ? 'live' : 'test'
				)
			);
		}

		return $ch;
	}

	/**
	 * Check whether charge can be captured
	 * @param Object $old_charge Charge object already existing on order
	 * @param Object $evt Web-hook event object
	 * @return Object
	 * @throws Advertikon\Stripe\Exception if charge cant be captured
	 */
	public function check_webhook_charge_capture( $old_charge, $evt ) {
		if ( $old_charge->captured ) {
			throw new Exception( sprintf( 'charge "%s" have been captured already', $old_charge->id ) );
		}

		return $evt->data->object;
	}

	/**
	 * Converts Stripe object to string
	 * @param object $obj Object 
	 * @return string
	 */
	public function stringify_stripe_object( $obj ) {
		$ret = '';

		if ( is_a( $obj, 'Stripe\StripeObject' ) ) {
			$ret = $obj->__toJSON();

		} else {
			if ( defined( 'JSON_PRETTY_PRINT' ) ) {
				$ret = json_encode( $obj, JSON_PRETTY_PRINT );

			} else {
				$ret = json_encode( $obj );
			}
		}

		return $ret;
	}

	/**
	 * Check whether charge can be refunded
	 * @param object $old_charge Charge object already existing on order
	 * @param object $evt Web-hook event object
	 * @return object
	 * @throws Advertikon\Stripe\Exception if charge cant be captured
	 */
	public function check_webhook_charge_refund( $old_charge, $evt ) {
		if ( $old_charge->refunded ) {
			$this->log( 'Charge has already been refunded', $old_charge, $this->log_debug_flag );
			throw new Exception( sprintf( 'charge "%s" have been refunded already', $old_charge->id ) );
		}

		$new_charge = $evt->data->object;
		$refund = $this->find_refund( $evt );

		if ( ! $refund ) {
			$mess = 'Can not find refund object in event';
			trigger_error( $mess );
			$this->log( $this->stringify_stripe_object( $evt ), $this->log_debug_flag );
			throw new Exception( $mess );
		}

		if ( $this->refund_exists( $old_charge, $refund ) ) {
			throw new Exception( sprintf( 'Refund "%s" already exists on charge "%s"', $refund->id, $oldCharge->id ) );
		}

		return $new_charge;
	}

	/**
	 * Returns new refund from web-hook event
	 * @param object $evt Event object
	 * @return object|null
	 * @throws Advertikon\Stripe\Exception on error
	 */ 
	public function find_refund( $evt ) {
		if (
			! isset( $evt->data->object->refunds->data ) ||
			! is_array( $evt->data->object->refunds->data ) ||
			! $evt->data->object->refunds->data 
		) {
			$mess = 'Charge doesn\'t have refund data';
			trigger_error( $mess );

			$charge = is_a( $evt->data->object, '\Stripe\StripeObject' ) ?
				$evt->data->object->__toJSON( true ) : $evt->data->object;
			$this->log( $charge, $this->log_debug_flag );

			throw new Exception( $mess );
		}

		if (
			! isset( $evt->data->previous_attributes->refunds->data ) ||
			! is_array( $evt->data->previous_attributes->refunds->data )
		) {
			$mess = 'Charge doesn\'t have updated refunds data';
			trigger_error( $mess );

			$charge = is_a( $evt->data->object, '\Stripe\StripeObject' ) ?
				$evt->data->object->__toJSON( true ) : $evt->data->object;
			$this->log( $charge, $this->log_debug_flag );

			throw new Exception( $mess );
		}

		$refunds = array();

		foreach( $evt->data->object->refunds->data as $r1 ) {
			foreach( $evt->data->previous_attributes->refunds->data as $r2 ) {

				// Old refund
				if ( $r1->id && $r2->id && $r1->id === $r2->id ) {
					continue 2;
				}
			}

			$refunds[] = $r1;
		}

		if ( ! $refunds ) {
			return null;
		}

		if ( count( $refunds ) > 1 ) {
			$mess = 'More then one refund found in event object';
			trigger_error( $mess );

			$event = is_a( $evt->data, '\Stripe\StripeObject' ) ?
				$evt->data->__toJSON( true ) : $evt->data;

			$this->log( $event, $this->log_debug_flag );

			throw new Exception( $mess );
		}

		return $refunds[ 0 ];
	}

	/**
	 * Checks whether order has specific refund
	 * @param Object $charge Charge object to search refund for
	 * @param Object $refund Refund object to search for
	 * @return Object|Boolean
	 * @throws Advertikon\Stripe\Exception on error
	 */
	public function refund_exists( $charge, $refund ) {
		if ( ! isset( $charge->refunds->data ) || ! is_array( $charge->refunds->data ) ) {
			$mess = 'Refunds should be represented by "list" objects';
			trigger_error( $mess );
			throw new Exception( $mess );
		}

		if ( ! is_object( $refund ) || ! $refund->id ) {
			$mess = 'Missing refund to search for';
			trigger_error( $mess );
			throw new Exception( $mess );
		}

		if ( false && $charge->refunds->has_more ) {
			//TODO: fetch all existing refunds
		}

		$refunds = $charge->refunds->data;

		foreach( $refunds as $r ) {
			if ( $r->id && $r->id === $refund->id ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Mark order as captured
	 * @param int $order_id Order ID
	 * @param string $comment Comment
	 * @param object $charge Charge object
	 */
	public function mark_order_as_captured( $order_id, $comment, $charge, $email, $override ) {
		$order_model = $this->get_order_model();
		$order_model->addOrderHistory(
			$order_id,
			$this->config( 'status_captured' ),
			$comment,
			$email,
			$override
		);

		if ( is_a( $charge, '\Stripe\StripeObject' ) ) {
			$charge = $charge->__toArray( true );
		}

		//$order = $order_model->getOrder( $order_id );
		$this->add_custom_field( $order_id, 'charge', $charge );
		$this->add_custom_field( $order_id, 'account', $this->get_account() );
	}

	/**
	 * Mark order as refunded
	 * @param integer $order_id Order ID
	 * @param string $comment Comment
	 * @param object $charge Charge object
	 * @throws Advertikon\Stripe\Exception on error
	 */
	public function mark_order_as_refunded( $order_id, $comment, $charge, $email, $override ) {
		$order_model = $this->get_order_model();
		$order = $order_model->getOrder( $order_id );

		if ( $charge->refunded ) {
			$status = $this->config( 'status_voided' );

		} else {

			if ( isset( $order['order_status_id'] ) ) {
				$status = $order['order_status_id'];

			} else {
				$mess = sprintf( 'Order $%s has no status"', $order_id );
				trigger_error( $mess );
				throw new Exception( $mess );
			}
		}

		$order_model->addOrderHistory( $order_id, $status, $comment, $email, $override );

		if ( is_a( $charge, '\Stripe\StripeObject' ) ) {
			$charge = $charge->__toArray( true );
		}

		$this->add_custom_field( $order, 'charge', $charge );
	}

	/**
	 * Creates Stripe coupon based on OpenCart coupon
	 * @param array $oc_coupon
	 * @return object
	 */
	public function create_stripe_coupon( $oc_coupon, $stripe_invoice ) {
		$is_fixed = strtoupper( $oc_coupon['type'] ) === 'F';

		$coupon_data = array(
			'duration'    => 'once',
			'amount_off'  => $is_fixed ?
				$this->currency->convert(
					$oc_coupon['discount'],
					$this->config->get( 'config_currency' ),
					$stripe_invoice->currency
				) : null,

			'currency'    => $is_fixed ? $stripe_invoice->currency : null,
			'persent_off' => ! $is_fixed ? $oc_coupon['discount'] : null,
		);

		return $this->create_api_coupon( $coupon_data );
	}

	/**
	 * Get string which describes Stripe plan
	 * @param object $plan Stripe plan object
	 * @return string
	 */
	public function get_stripe_plan_string( $plan ) {
		return $this->__(
			'%s each %s %s(s) with %u trial day(s)',
			$this->currency->format(
				$this->cents_to_amount( $plan->amount, $plan->currency ),
				strtoupper( $plan->currency ),
				1,
				1
			),
			$plan->interval_count,
			$plan->interval,
			$plan->trial_period_days
		);
	}

	/**
	 * Trial period notification string in case if coupon / voucher will set charge total to 0
	 * @param array $recurring Recurring details as returned by BogusCart
	 * @return string
	 */
	public function trial_string_if_zero_total( $recurring ) {
		if (
			! $recurring['product']['recurring']['trial'] &&
			( $recurring['charge']['one_time']['total'] + $recurring['charge']['recurring']['total'] ) == 0
		) {
			return $this->__(
				' (to cover coupon (voucher) amount trial period of %s %s(s) will be applied)'
				, $recurring['product']['recurring']['trial_cycle']
				, $recurring['product']['recurring']['trial_frequency'] );
		}
	}

	/**
	 * Get string which describes OC recurring plan
	 * @param array $recurring OpenCart recurring plan
	 * @return string
	 */
	public function get_oc_plan_string( $recurring ) {
		$s = $this->__(
			'%s every %s %s(s) for %s payment(s)',
			$this->currency->format( $recurring['price'] ),
			$recurring['cycle'] > 1 ? $recurring['cycle'] : '',
			$recurring['frequency'], $recurring['duration']
		);

		if ( $recurring['trial'] ) {
			$s .= $this->__(
				' with trial period of %s %s(s)',
				$recurring['trial_cycle'] * $recurring['trial_duration'],
				$recurring['trial_frequency']
			);
		}

		return $s;
	}

	/**
	* Compares Stripe plan with OpenCart one
	* @param object $sp_plan Stripe plan object
	* @param array $oc_plan OpenCart plan representation
	* @param string $currency Oc_plan currency code. Optional. Default to store currency
	* @return boolean
	*/
	public function compare_plans( $sp_plan, $oc_plan, $currency = null ) {

		$currency = is_null( $currency ) ? $this->config->get( 'config_currency' ) : $currency;

		// Trial period match
		$oc_trial_status = isset( $oc_plan['trial_status'] ) ?
			$oc_plan['trial_status'] : ( isset( $oc_plan['trial'] ) ? $oc_plan['trial'] : 0 );

		if ( (bool)$sp_plan->trial_period_days !== (bool)$oc_trial_status ) {
			return false;
		}

		if ( $oc_trial_status && $sp_plan->trial_period_days !== $this->get_trial_days( $oc_plan ) ) {
			return false;
		}

		// Currency match
		if ( 
			strtolower( $sp_plan->currency ) !== strtolower( $currency ) // ||
		) {
			return false;
		}

		if( $sp_plan->amount != $this->amount_to_cents(
			$this->currency->convert(
				$oc_plan['price'],
				$this->config->get( 'config_currency' ),
				$currency
			),
			$currency
		) ) {
			return false;
		}

		// Cycles and frequencies match
		if ( strtolower( $oc_plan['frequency'] ) === 'semi_month' && $sp_plan->interval === 'week' ) {

			if ( ( $oc_plan['cycle'] * 2 ) !== $sp_plan->interval_count ) {

				return false;
			}

		} elseif ( strtolower( $oc_plan['frequency'] ) !== strtolower( $sp_plan->interval ) ) {

			return false;

		} elseif ( $oc_plan['cycle'] != $sp_plan->interval_count ) {

			return false;
		}

		return true;
	}

	/**
	 * Returns trial period days quantity
	 * @param Array $recurring Recurring OC plan
	 * @return Integer
	 */
	public function get_trial_days( $recurring ) {
		$days = 0;
		$r = $recurring;

		if ( $r['trial_frequency'] && $r['trial_cycle'] && $r['trial_duration'] ) {
			switch( strtolower( $r['trial_frequency'] ) ) {
				case 'day' :
					$days = 1;
				break;
				case 'week' :
					$days = 7;
				break;
				case 'month' :
					$today = new DateTime;
					$month = new DateTime( '+1 month' );
					$days = $today->diff( $month, true )->format( '%a' );
				break;
				case 'semi_month' :
					$today = new DateTime;
					$month = new DateTime( '+1 fortnight' );
					$days = $today->diff( $month, true )->format( '%a' );
				break;
				case 'year' :
					$today = new DateTime;
					$month = new DateTime( '+1 year' );
					$days = $today->diff( $month, true )->format( '%a' );
				break;
			}

			$days = $days * $r['trial_cycle'] * $r['trial_duration'];
		}

		return $days;
	}

	/**
	 * Checks whether customer can cancel subscription
	 * @param int $recurring_id OC recurring plan ID
	 * @return boolean
	 * @throws Advertikon\Stripe\exception if corresponding Stripe profile doesn't exist
	 */
	public function plan_customer_can_cancel( $recurring_id ) {
		$profie = new Resource\Profile();
		$profile->load_oc_plan( $recurring_id );

		if ( ! $profile->is_exists() ) {
			throw new Exception( $this->__( 'Recurring plan "%s" has no corresponding Stripe profile', $recurring_id ) );
		}

		return (bool)$profile->user_abort;
	}

	/**
	 * Checks whether subscription can be canceled immediately
	 * @param int $recurring_id OC recurring plan ID
	 * @return boolean
	 * @throws Advertikon\Stripe\Exception if corresponding Stripe profile doesn't exist
	 */
	public function plan_cancel_now( $recurring_id ) {
		$profie = new Resource\Profile();
		$profile->load_oc_plan( $recurring_id );

		if ( ! $profile->is_exists() ) {
			throw new Exception( $this->__( 'Recurring plan "%s" has no corresponding Stripe profile', $recurring_id ) );
		}

		return (bool)$profile->cancel_now;
	}

	/**
	 * Returns plan ID by its recurring order ID
	 * @param int $recurring_order_id OC recurring order ID
	 * @return int|null
	 */
	public function get_oc_plan_by_order( $recurring_order_id ) {
		$ret = null;

		$result = $this->q( array(
			'table' => 'order_recurring',
			'query' => 'select',
			'field' => 'recurring_id',
			'where' => array(
				'field'     => 'order_recurring_id',
				'operation' => '=',
				'value'     => $recurring_order_id,
			),
		) );

		if ( count( $result ) ) {
			$ret = $result['recurring_id'];
		}

		return $ret;
	}

	/**
	 * Set Stripe API key
	 * @param void
	 * @throws Stripe\Error\Base
	 */
	public function set_api_key( $secret = true ){
		$key = $secret ? $this->get_secret_key() : $this->get_public_key();
		\Stripe\Stripe::setApiKey( $key );

		$msg = $secret ? 'Set secret API key "'  . $this->obscure_str( $key ) . '"' : 'Set public API key "'  . $key . '"';
		$this->log( $msg, $this->log_debug_flag );
	}

	/**
	 * Creates Stripe Card Token
	 * @param array Card details
	 * @return object Token
	 * @throws Stripe\Error\Base
	 */
	public function create_api_token( $param ){
		$stripe_api_token = \Stripe\Token::create( array( 'card' => $param ) );
		$this->log( 'New token was created', $stripe_api_token->__toJSON(), $this->log_debug_flag );

		return $stripe_api_token;
	}

	/**
	 * Fetches Stripe plan
	 * @param string $stripe_plan_id Plan ID
	 * @return object Plan
	 * @throws Stripe\Error\Base
	 */
	public function fetch_api_plan( $stripe_plan_id ){
		$stripe_api_plan = \Stripe\Plan::retrieve( $stripe_plan_id );
		$this->log( 'Stripe plan was fetched', $stripe_api_plan->__toJSON(), $this->log_debug_flag  );

		return $stripe_api_plan;
	}

	/**
	 * Creates Stripe plan
	 * @param array $data Plan information
	 * @return object Plan
	 * @throws Stripe\Error\Base on Creation error
	 */
	public function create_api_plan( array $data ) {
		$stripe_api_plan = \Stripe\Plan::create( $data );
		$this->log( 'New plan was created', $stripe_api_plan->__toJSON(), $this->log_debug_flag );

		return $stripe_api_plan;
	}

	/**
	 * Fetches all Stripe Plans
	 * @param string $start Object ID to start after. Optional. Default behavior is start from the begin
	 * @param int $limit List element count
	 * @return object List
	 * @throws Stripe\Error\Base on error
	 */
	public function fetch_api_plan_all( $start = null, $limit = null ) {
		return $this->paginate( array( '\Stripe\Plan', 'all' ), $start, $limit );
	}

	/**
	 * Changes statement descriptor for plan
	 * @param string $sp_plan_id Stripe plan ID
	 * @param string|null $statement New Statement descriptor 
	 * @return object Plan
	 * @throws Stripe\Error\Base on Stripe error
	 */
	public function plan_new_statement_descriptor( $sp_plan_id, $statement = null ) {
		$statement = $statement ?: null;
		$plan  = $this->fetch_api_plan( $sp_plan_id );
		$plan->statement_descriptor = $statement;
		$plan = $plan->save();
		$this->log( 'Statement descriptor of the plan was changed', $this->log_debug_flag );

		return $plan;
	}

	/**
	 * Changes Stripe plan\'s' name
	 * @param string $sp_plan_id Stripe plan ID
	 * @param string $name Plan's new name
	 * @return object Plan
	 * @throws Stripe\Error\Base on Stripe error
	 * @throws Advertikon\Stripe\Error if plan's name is missing
	 */
	public function plan_rename( $sp_plan_id, $name ) {
		if ( ! $name ) {
			$mess = 'Plan\'s name mandatory';
			$this->log( $mess, $this->log_debug_flag );
			throw new Exception( $mess );
		}

		$plan  = $this->fetch_api_plan( $sp_plan_id );
		$plan->name = $name;
		$plan = $plan->save();
		$this->log( 'Name of the plan was changed', $this->log_debug_flag );

		return $plan;
	}

	/**
	 * Deletes Stripe plan
	 * @param String $spPlanId Stripe plan ID
	 * @throws Stripe\Error on Stripe error
	 * @return void
	 */
	public function plan_delete( $sp_plan_id ) {
		$plan  = \Stripe\Plan::retrieve( $sp_plan_id );
		$delete = $plan->delete();
		$this->log( sprintf( 'Plan "%s" was deleted', $sp_plan_id ), $this->log_debug_flag );
	}

	/**
	 * Paginate over list element
	 * @param Callable $callable Callable to return list element
	 * @param string $start Object ID to start from next element after or NULL to start from the beginning
	 * @param Integer $limit Count of element per list to be fetched
	 * @return Object
	 * @throws Stripe\Error\Base on error
	 */
	public function paginate( $callable, $start, $limit ) {
		$max_limit = 100;
		$limit = is_null( $limit ) ? $max_limit : $limit;

		$list = call_user_func_array(
			$callable,
			array( array( 'limit' => min( $max_limit, $limit ), 'starting_after' => $start ) )
		);

		if ( $list->has_more && $limit > $max_limit ) {
			$last = $list->data[ count( $list->data ) - 1 ];
			$list_next = $this->paginate( $callable, $last->id, $limit - $max_limit );
			$list->data = array_merge( $list->data, $list_next->data );
			$list->has_more = $list_next->has_more;
		}

		return $list;
	}

	/**
	 * Create Stripe coupon
	 * @param array $data Coupon data
	 * @return object Coupon
	 * @throws Stripe\Error
	 */
	public function create_api_coupon( $data ) {
		$stripe_api_coupon = \Stripe\Coupon::create( $data );
		$this->log( 'New Stripe\'s coupon was created', $stripe_api_coupon->__toJSON(), $this->log_debug_flag );

		return $stripe_api_coupon;
	}

	/**
	 * Deletes Stripe coupon
	 * @param string $coupon_code Stripe coupon code
	 * @return void
	 * @throws Stripe\Error\Base on Stripe error
	 */
	public function delete_api_coupon( $coupon_code ) {
		$coupon = \Stripe\Coupon::retrieve( $coupon_code );
		$deleted = $coupon->delete();

		$this->log( sprintf( "Coupon '%s' was deleted", $deleted->id ), $this->log_debug_flag );
	}

	/**
	 * Creates subscription
	 * @param string $customer_id Stripe customer ID
	 * @param array $data Subscription details
	 * @return object subscription
	 * @throws Stripe\Error\Base
	 * @throws Advertikon\Stripe\Exception if customer was deleted
	 */
	public function create_api_subscription( $customer_id, $data ) {
		$customer = $this->fetch_api_customer( $customer_id );
		$subscription = $customer->subscriptions->create( $data );

		$this->log(
			sprintf( 'New subscription created fr customer', $customer_id ),
			$subscription->__toJSON(),
			$this->log_debug_flag
		);

		return $subscription;
	}

	/**
	 * Cancels Stripe subscription
	 * @param string $customerId Stripe customer ID
	 * @param string $subscriptionId Stripe subscription ID
	 * @param boolean $at_period_end Whether to cancel subscription at the end of current period
	 * @throws Stripe\Error\Base on gateway error
	 * @throws Advertikon\Stripe\Exception if customer was deleted
	 * @return object Subscription
	 */
	public function delete_api_subscription( $customer_id, $subscription_id, $at_period_end = true ){
		$customer = $this->fetch_api_customer( $customer_id );
		$subscription = $customer->subscriptions->retrieve( $subscription_id )
			->cancel( array( 'at_period_end' => $at_period_end ) );

		$this->log(
			sprintf(
				'Subscription "%s" for customer "%s" was canceled %s at Stripe',
				$subscription->id,
				$customer_id,
				$at_period_end ? '(scheduled at period end)' : ''
			),
			$this->log_debug_flag
		);

		return $subscription;
	}

	/**
	 * Fetches specific subscription for customer
	 * @param string $cistomer_id Customer ID
	 * @param string $subscription_id Subscription ID
	 * @return object Subscription
	 * @throws Stripe\Error\Base
	 * @throws Advertikon\Stripe\Exception if customer was deleted
	 */
	public function fetch_api_subscription( $customer_id, $subscription_id ) {
		$customer = $this->fetch_api_customer( $customer_id );
		$subscr = $customer->subscriptions->retrieve( $subscription_id );

		$this->log(
			sprintf( 'Subscription %s retrieved for customer %s', $subscr->id, $customer->id ),
			$subscr->__toJSON(),
			$this->log_debug_flag
		);

		return $subscr;
	}

	/**
	 * Creates customer
	 * @param array $data Customer's data
	 * @return object Customer
	 * @throws Stripe\Error\Base
	 */
	public function create_api_customer( $data ){
		$stripe_api_customer = \Stripe\Customer::create( $data );
		$this->log( 'New customer was created', $stripe_api_customer->__toJSON(), $this->log_debug_flag );

		if ( $this->do_cache ) {
			$this->cache->set( $stripe_api_customer->id, $stripe_api_customer, 300 );
		}

		return $stripe_api_customer;
	}

	/**
	 * Fetches customer
	 * @param string $stripe_customer_id Customer ID
	 * @return object customer
	 * @throws Stripe\Error\Base
	 * @throws Advertikon\Stripe\Exception if customer was deleted
	 */
	public function fetch_api_customer( $stripe_customer_id ) {
		$stripe_api_customer = null;

		if ( $this->do_cache ) {
			$stripe_api_customer = $this->cache->get( $stripe_customer_id );
		}

		if ( ! $stripe_api_customer ) {
			$stripe_api_customer = \Stripe\Customer::retrieve( $stripe_customer_id );

			if ( $this->do_cache ) {
				$this->cache->set( $stripe_api_customer->id, $stripe_api_customer, 300 );
			}
		}

		if ( $stripe_api_customer->deleted ) {
			$mess =  $this->__( 'Target customer was deleted' );
			$this->log( $mess, $this->log_debug_flag );
			$this->cache->delete( $stripe_api_customer->id );
			throw new Exception( $mess );
		}

		$this->log(
			'Customer fetched from Stripe',
			$this->stringify_stripe_object( $stripe_api_customer ),
			$this->log_debug_flag
		);

		return $stripe_api_customer;
	}

	/**
	 * Fetches all customers
	 * @param array $customers Customers list
	 * @param int $count Number of iteration
	 * @return array
	 */
	public function fetch_api_customer_all( &$customers = null, $count = 0 ){
		if ( $count >= self::MAX_REQUEST_COUNT ) {
			$mess = $this->__( 'Maximum request (%s) to Stripe server reached', $count );
			trigger_error( $mess );
			throw new Exception( $mess );
		}

		$options = array( 'limit' => 100 );

		// Recursion
		if ( $customers ) {
			$last_customer = $customers[ count( $customers ) - 1 ];
			$options['starting_after'] = $last_customer->id;
		}

		$stripe_api_customer_list = \Stripe\Customer::all( $options );
		$count++;

		if ( ! $customers ) {
			$customers = $stripe_api_customer_list->data;

		} else {
			$customers = array_merge( $customers, $stripe_api_customer_list->data );
		}

		if ( $stripe_api_customer_list->has_more ) {
			$this->fetch_api_customer_all( $customers, $count );
		}

		$this->log(
			$this->__( " %s Stripe Customers was fetched", count( $customers ) ),
			$this->log_debug_flag
		);

		return $customers;
	}

	/**
	 * Deletes all customers
	 * $param array $filter Customers ID filter
	 * @return int Number of deleted customers
	 */
	public function delete_api_customers_all( $filter = array() ){
		$this->log( 'Start of customer deletion', $this->log_debug_flag );

		$customers = $this->fetch_api_customer_all();
		$skipped = 0;
		$deleted = 0;
		$error = 0;
		$filter_id = isset( $filter['id'] ) ? $filte['id'] : null;

		foreach( $customers as $customer ) {
			if ( $customer->deleted ) {
				continue;
			}

			if ( $filter_id === $customer->id ) {
				$this->log( $this->__( "Customer with ID #%s was skipped", $customer->id ), $this->log_debug_flag );
				$skipped++;
				continue;
			}

			try {
				$this->delete_api_customer( $customer );
				$deleted++;

				$this->cache->delete( $customer->id );

			} catch ( \Stripe\Error\Base $e ) {
				$error++;
			}
		}

		$this->log(
			sprintf( 'Deletion details: deleted - %s, skipped - %s, error - %s', $deleted, $skipped, $error ),
			$this->log_debug_flag
		);

		return $deleted;
	}

	/**
	 * Deletes customer
	 * @param {object|string} $customer Customer object or customer ID
	 * @return void
	 * @throws Stripe\Error\Base
	 */
	public function delete_api_customer( $customer ) {
		if ( is_string( $customer ) ) {
			$customer = $this->fetch_api_customer( $customer );

		} elseif ( ! is_a( $customer, '\Stripe\Customer' ) ) {
			$this->__( 'Customer need to be an instance of "Stripe\Customer" or string Customer ID. "%s" given instead',
				is_object( $customer ) ? get_class( $customer ) : gettype( $customer )
			);

			trigger_error( $mess );
			throw new \Advertikon\Exception( $mess );
		}

		$customer->delete();
		$this->cache->delete( $customer->id );

		$this->log( sprintf( 'Customer "%s" was deleted', $customer->id ) );

		return true;
	}

	/**
	 * Fetches card
	 * @param object $stripe_api_customer Customer
	 * @param string $card_id Card ID, optional
	 * @return object Card
	 * @throws Stripe\Error\Base
	 * @throws Advertikon\Stripe\Exception
	 */
	public function fetch_api_card( $stripe_api_customer, $card_id = null ) {
		if ( is_null( $card_id ) ) {
			$card_id = $stripe_api_customer->default_source;
		}

		if ( ! $card_id ) {
			$mess = $this->__( 'Failed to fetch default card for customer "%s', $stripe_api_customer->id ); 
			trigger_error( $mess );
			$this->log( $stripe_api_customer, $this->log_error_flag );
			throw new Exception( $mess );
		}

		$stripe_api_card = $stripe_api_customer->sources->retrieve( $card_id );
		$this->log( 'Card fetched from Stripe', $stripe_api_card->__toJSON(), $this->log_debug_flag );

		return $stripe_api_card;
	}

	/**
	 * Adds card to customer
	 * @param object $stripe_api_customer Customer
	 * @param string $stripe_api_token Card's token
	 * @param string $secret Additional password for saved card
	 * @return object card
	 * @throws Stripe\Error\Base
	 */
	public function add_api_card( $stripe_api_customer, $stripe_api_token, $secret = null ) {
		$data = array( 'source' => $stripe_api_token );

		if ( $secret ) {
			$data['metadata'] = array( 'secret' => $secret );
		}

		$stripe_api_card = $stripe_api_customer->sources->create( $data );

		$this->log(
			sprintf( 'Customer "%s" has been added a card "%s"', $stripe_api_customer->id, $stripe_api_card->id ),
			$this->log_debug_flag
		);

		$this->cache->delete( $stripe_api_customer->id );

		return $stripe_api_card;
	}

	/**
	 * Replaces sources with card and make it default
	 * @param object $stripe_api_customer Stripe customer
	 * @param string $stripe_api_token Card's token
	 * @return object card
	 */
	public function set_api_card( $stripe_api_customer, $stripe_api_token ){
		$stripe_api_card = $stripe_api_customer->source = $stripe_api_token ;
		$stripe_api_customer->save();

		$this->log(
			sprintf( 'Default source for customer "%s" was set to "%s"', $stripe_api_customer->id, $id ),
			$this->log_debug_flag
		);

		return $stripe_api_card;
	}

	/**
	 * Sets default course for customer
	 * @param object $stripe_api_customer Stripe customer object
	 * @param string $id Source ID
	 * @return object Customer
	 * @throws Stripe\Error\Base on error
	 */
	public function set_default_card( $stripe_api_customer, $id ){
		$stripe_api_customer->default_source = $id;
		$stripe_api_customer->save();

		$this->log(
			sprintf( 'Default source for customer "%s" was set to "%s"', $stripe_api_customer->id, $id ),
			$this->log_debug_flag
		);

		return $stripe_api_customer;
	}

	 /**
	 * Fetches all the cards of specific customer
	 * @param object $customer Stripe customer
	 * @param array $cards Cards list
	 * @param int $count Number of iteration
	 * @return array
	 * @throws Stripe\Error\Base
	 */
	public function fetch_api_cards_all( $customer, &$cards = null, $count = 0 ) {

		// Stripe Customer always contains last 10 cards, so if total count less then 10 - these are all customers cards
		if ( count( $customer->sources->data ) < 10 ) {
			$card_list = $customer->sources->data;
			$this->log( sprintf( " %s Stripe Card(s) was fetched", count( $card_list ) ), $this->log_debug_flag );
			return $card_list;
		}

		if ( $count >= self::MAX_REQUEST_COUNT ) {
			$mess = $this->__( "Reached maximum number of request to the Stripe server" );
			trigger_error( $mess );
			throw new Exception( $mes );
		}

		$options = array( 'limit' => 100, 'object' => 'card', );

		// Recursion
		if ( $cards ) {
			$last_card = $cards[ count( $cards ) - 1 ];
			$options['starting_after'] = $last_card->id;
		}

		$stripe_api_card_list = $customer->sources->all( $options );
		$count++;

		if ( ! $cards ) {
			$cards = $stripe_api_card_list->data;

		} else {
			$cards = array_merge( $cards, $stripe_api_card_list->data );
		}

		if ( $stripe_api_card_list->has_more ) {
			$this->_fetch_api_cards_all( $customer, $cards, $count );
		}

		$this->log( "%s Stripe Card(s) was fetched", count( $cards ), $this->log_debug_flag );

		return $cards;
	}

	/**
	 * Deletes Stripe card
	 * @param object $card Stripe card object
	 * @throws Stripe\Error\Base
	 * @return void
	 */
	public function delete_api_card( $card ) {
		$deleted = $card->delete();
		$this->log( sprintf( 'Card "%s" was deleted', $card->id ), $this->log_debug_flag );
	}

	/**
	 * Charge Stripe customer
	 * @param array
	 * @return object
	 * @throws Stripe\Error\Base
	 */
	public function create_api_charge( $data ) {
		$stripe_api_charge = \Stripe\Charge::create( $data );
		$this->log( 'New Stripe charge:', $stripe_api_charge->__toJSON(), $this->log_debug_flag );

		return $stripe_api_charge;
	}

	/**
	 * Fetch charge object from Stripe
	 * @param String $chargeId Charge ID
	 * @return Object
	 * @throws Stripe\Error\Base On error
	 */
	public function fetch_api_charge( $charge_id ) {
		$stripe_api_charge = \Stripe\Charge::retrieve( $charge_id );
		$this->log( 'Charge fetched from Stripe server:', $stripe_api_charge->__toJSON(), $this->log_debug_flag );

		return $stripe_api_charge;
	}

	/**
	 * Capture stripe charge
	 * @param Object $charge Charge object
	 * @param Integer $amount Amount (in cents) to be captured
	 * @param Integer $order_id Order ID, which charge belongs to
	 * @return Object
	 * @throws Advertikon\Stripe\Exception On charge fetching error
	 */
	public function capture_charge( $charge, $amount, $order_id ) {
		$order = $this->get_order_info( $order_id );

		if ( ! count( $order ) ) {
			$mess = sprintf( 'Order #%s is missing', $order_id );
			trigger_error( $mess );
			throw new Exception( $mess );
		}

		$data = array(
			'amount' => $amount,
		);

		if ( $statment_descriptor = $this->config( 'statement_descriptor' ) ) {
			$data['statement_descriptor'] = $statment_descriptor;
		}

		// In case receipt email setting was changed since a charge authorization
		if ( $this->config( 'receipt_email' ) && ! $charge->receipt_email ) {
			$cdata['receipt_email'] = $order['email'];
		}

		$this->set_api_key();
		$stripe_api_charge = $this->fetch_api_charge( $charge->id )->capture( $data );

		if ( $stripe_api_charge->captured ) {
			$this->log(
				sprintf(
					'Charge for the order %s was captured on sum of %s %s',
					$order_id,
					$this->cents_to_amount( $amount, $stripe_api_charge->currency ),
					$stripe_api_charge->currency
				),
				$this->log_debug_flag
			);

			$this->add_custom_field( $order_id, 'charge', $stripe_api_charge->__toArray( true ) );
			$this->add_custom_field( $order_id, 'transaction', array( 'capture' => $stripe_api_charge->balance_transaction ) );

			$this->log( sprintf( 'Transaction #%s was stored in DB', $stripe_api_charge->balance_transaction ) );

			return $stripe_api_charge;
		}

		$mess = $this->__( 'Failed to capture payment for order %s', $order_id );
		trigger_error( $mess );
		throw new Exception( $mess );
	}

	/**
	 * Refund stripe charge
	 *
	 * @param string $charge_id Charge ID
	 * @param Integer $amount Amount (in cents) to be refunded
	 * @param Integer $order_id Order ID, which charge belongs to
	 * @return Boolean
	 * @throws Stripe\Error\Base On error
	 */
	public function refund_charge( $charge_id, $amount, $order_id ) {

		$data = array(
			'amount' => $amount,
			'charge' => $charge_id,
		);


		$this->set_api_key();
		$refund = \Stripe\Refund::create( $data );

		$charge = $this->fetch_api_charge( $charge_id );

		$this->log(
			sprintf(
				'Order #%s was refunded on sum of %s %s',
				$order_id,
				$this->cents_to_amount( $amount, $charge->currency ),
				$charge->currency
			),
			$this->log_debug_flag
		);

		$this->add_custom_field( $order_id, 'charge', $charge->__toArray( true ) );


		return true;
	}

	/**
	 * Create invoice item as Stripe side
	 * @param Array $data Invoice item information
	 * @return Object
	 * @throws Stripe\Error\Base on error
	 */
	public function create_api_invoice_item( array $data ){
		$item = \Stripe\InvoiceItem::create( $data );
		$this->log( 'Invoice item created', $item->__toJSON(), $this->log_debug_flag );

		return $item;
	}

	/**
	 * Fetch invoice from Stripe server
	 * @param String $id Invoice id
	 * @return Object
	 * @throws Stripe\Error\Base on error
	 */
	public function fetch_api_invoice( $id ){
		$invoice = \Stripe\Invoice::retrieve( $id );
		$this->log( 'Invoice fetched from  Stripe', $invoice->__toJSON(), $this->log_debug_flag );

		return $invoice;
	}

	/**
	 * Synchronize charge information with data Stripe
	 * @param Integer $order_id Order id
	 * @throws Advertikon\Stripe\Exception On charge fetching error
	 */
	public function refresh_charge_info( $order_id ) {

		$payment_data = $this->get_custom_field( $order_id );

		if ( ! $payment_data->charge ) {
			$mess = $this->__( 'Missing charge for order #%s', $order_id );
			trigger_error( $mess );
			throw new Exception( $mess );
		}

		$charge = $payment_data->charge;

		if ( ! $charge->id ) {
			$mess = $this->__( 'Missing charge ID for order #', $order_id );
			trigger_error( $mess );
			throw new Exception( $mess );
		}

		$this->set_api_key();
		$stripe_api_charge = $this->fetch_api_charge( $charge->id );
		$payment_data->charge = $stripe_api_charge->__toArray( true );
		$this->add_custom_field( $order_id, null, null, null, $payment_data );

		$this->log( sprintf( 'Charge information of order #%s was updated', $order_id ), $this->log_debug_flag );

		return true;
	}

	/**
	 * Returns payment card vendor's image
	 * @param string $brand 
	 * @return string image URL
	 */
	public function get_brand_image( $brand ) {
		$card_image = '';
		$name = str_replace( ' ', '', strtolower( $brand ) );

		switch( $name ) {
			case 'mastercard' :
				$card_img = 'mc.svg';
				break;
			case 'americanexpress' :
				$card_img = 'ae.svg';
				break;
			case 'dinersclub' :
				$card_img = 'dc.svg';
				break;
			default :
				$card_img = $name . '.svg';
		}

		return $this->u()->catalog_url() . 'image/advertikon/stripe/' . $card_img; 
	}

	/**
	 * Get count of days for charge to be un-captured
	 * @param Object $charge Charge object
	 * @return Integer
	 */
	public function remain_to_be_captured( $charge ) {
		if ( ! is_object( $charge ) || ! $charge->created ) {
			return 0;
		}

		return 7 - ceil( ( time() - $charge->created ) / ( 60 * 60 * 24 ) );
	}

	/**
	 * Checks library compatibility
	 * @return array
	 */
	public function check_compatibility(){
		$all_is_bad = false;
		$name = 'Stripe gateway';
		$return = parent::check_compatibility();

		// CURL library presence
		if ( $all_is_bad || ! function_exists( 'curl_version' ) ) {
			$return[ $name ]['error'][] = $this->__( 'PHP CURL library missing' ) . '. ' .
			sprintf(
				'%s<a href="%s" target="_blank">%s</a>',
				$this->__( 'Follow this link to ' ),
				'http://php.net/manual/book.curl.php',
				$this->__( 'get more details' )
			);
		}

		// CURL SSL support
		else {
			$cv = curl_version();

			if (
				$all_is_bad ||
				isset( $cv['features'] ) &&
				! ( $cv['features'] & CURL_VERSION_SSL )
			) {
				$return[ $name ]['error'][] =  $this->__( 'PHP CURL library has no support of SSL' ) . '. ' .
				sprintf(
					'%s<a href="%s" target="_blank">%s</a>',
					$this->__( 'Follow this link to ' ),
					'http://php.net/manual/book.curl.php',
					$this->__( 'get more digitals' )
				);
			}

			// if (
			// 	$all_is_bad ||
			// 	! version_compare( $cv['version'], '7.36', '>=' )
			// ) {
			// 	$return[ $name ]['alert'][] =  $this->__( 'PHP CURL library need to be updated at least to v 7.36' ) . '. ' .
			// 	$this->__( 'Refer your host support to solve this issue.' );
			// }
		}

		// JSON library support
		if (  $all_is_bad || ! function_exists( 'json_decode' ) ) {
			$return[ $name ]['error'][] =  $this->__( 'PHP JSON library missing' ) . '. ' .
			sprintf(
				'%s<a href="%s" target="_blank">%s</a>',
				$this->__( 'Follow this link to ' ),
				'http://php.net/manual/book.json.php',
				$this->__( 'get more ditails' )
			);
		}

		// MBString library support
		if (  $all_is_bad || ! function_exists( 'mb_detect_encoding' ) ) {
			$return[ $name ]['error'][] = $this->__( 'PHP Multi-byte String library missing' ) . '. ' .
			sprintf(
				'%s<a href="%s" target="_blank">%s</a>',
				$this->__( 'Follow this link to ' ),
				'http://php.net/manual/book.mbstring.php',
				$this->__( 'get more ditails' )
			);
		}

		return $return;
	}

	/**
	 * Delete Stripe plan action
	 * @param string $id Stripe plan ID
	 * @param string $account Account name
	 * @return void
	 */
	public function plan_remove( $id = null, $account = 'default' ) {
		if ( is_null( $id ) ) {
			$mess = $this->a->__( 'Plan\'s ID is missing' );
			$this->a->log( $mess, $this->a->log_error_flag );
			throw new Advertikon\Exception( $mess );
		}

		$this->set_account( $account );
		$this->plan_delete( $id );
		$plan_res = new Advertikon\Stripe\Resource\Plan( $id, 'sp_plan_id' );

		if ( $plan_res->is_exists() ) {
			$plan_res->delete();
		}
	}
}
