<?php
/**
 * Catalog Advertikon Stripe Controller
 * @author Advertikon
 * @package Stripe
 * @version 2.8.11
 */

namespace Advertikon\Stripe;

if ( version_compare( VERSION, '2.2.0.0', '>=' ) ) { 
	class SADKCart extends \Cart\Cart {
		public function __construct( $registry ) {
			parent::__construct( $registry );
		}
	} 

} else {
	class SADKCart extends \Cart {
		public function __construct( $registry ) {
			parent::__construct( $registry );
		}
	} 
}

class BogusCart extends SADKCart {

	static protected $self = null;

	static public function instance( $registry ) {
		if ( is_null( self::$self ) ) {
			self::$self = new self( $registry );
		}

		return self::$self;
	}

	protected $products = array();
	protected $p = array();
	protected $recurring = null;
	protected $ordinary = null;
	protected $backup = null;
	protected $isRecurring = false;
	protected $isOrdinal = false;
	protected $isOriginal = true;
	protected $isOnlyRecurring = null;
	protected $isProcessed = false;
	protected $ordinaryTotals = array();
	protected $recurringTotals = array();
	protected $bogusTotals = array();
	protected $nextTotals = array();
	protected $coupon = null;
	protected $vaucher = null;
	protected $profileId = null;
	protected $taxRates = null;
	protected $voucherApplied = 0;
	protected $couponApplied = 0;
	protected $couponUsed = 0;
	protected $couponUsedCustomer = 0;
	protected $db;
	protected $config;
	protected $registry = null;

	public function __construct( $registry ) {
		parent::__construct( $registry );
		$this->registry = $registry;
		$this->config = $registry->get('config');
		$this->db = $registry->get('db');
	}

	/**
	 * Sets products to cart
	 * @param Array $products
	 * @return Object
	 */
	public function setProducts( $products = array() ) {
		$this->products = $products;
		$this->isOnlyRecurring = null;
		return $this;
	}

	/**
	 * @see Cart::getProducts()
	 */
	public function getProducts() {
		return $this->products;
	}

	/**
	 * Returns totals for ordinary charge products
	 * @return Array
	 */
	public function getTotals( &$line = null ) {
		if( ! $this->products ) {
			return array();
		}

		$total = 0;

		if ( version_compare( VERSION, '2.2.0.0', '>=' ) ) {
			$tax = $this->getTaxes();
			$t1 = array();
			$totals = array(
				'totals' => &$t1 ,
				'total'  => &$total ,
				'taxes'  => &$tax
			);

			$ret = &$t1;
			$taxes = null;

		} else {
			$totals = array();
			$taxes = $this->getTaxes();
			$ret = &$totals;
			$total = null;
			$tax = null;
		}

		ADK( __NAMESPACE__ )->load->model( 'extension/extension' );
		$this->enableShipping = false;

		$sort_order = array();
		$results = ADK( __NAMESPACE__ )->model_extension_extension->getExtensions( 'total' );

		foreach ($results as $key => $value) {
			$sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
		}

		array_multisort( $sort_order, SORT_ASC, $results );

		foreach ($results as $result) {
			if ($this->config->get($result['code'] . '_status') ) {
				if(
					$result[ 'code' ] == 'tax' ||
					$this->isInOrdinary( $result[ 'code' ] ) ||
					! $this->lineCount( $result[ 'code' ] , $line )
				) {

					if( $result[ 'code' ] === 'coupon' ) {
						$this->applyCoupon( $totals, $total , $taxes );

					} else if( $result[ 'code' ] === 'voucher' ) {
						$this->applyVoucher( $totals , $total , $taxes );

					} else {
						$route = ADK( __NAMESPACE__ )->u()->get_route( 'total/' . $result['code'] );
						$model_name = 'model_' . str_replace( array( '/'), '_', $route );
						ADK( __NAMESPACE__ )->load->model( $route );
						$tot = ADK( __NAMESPACE__ )->{$model_name}
							->getTotal( $totals , $total , $taxes );
					}

					if( $result[ 'code' ] === 'shipping' ) {
						$this->enableShipping = true;
					}
				}
			}
		}

		return $ret;
	}

	/**
	 * Returns recurring totals for products
	 * @return Array
	 */
	public function getRecurringTotals( &$line = null , $next = false ) {
		if( ! $this->products ) {
			return array();
		}

		$total = 0;

		if ( version_compare( VERSION, '2.2.0.0', '>=' ) ) {
			$tax = $this->getTaxes();
			$t1 = array();
			$t2 = array();
			$totals = array(
				'totals' => &$t1 ,
				'total'  => &$total,
				'taxes'  =>  &$tax
			);

			$excluded = array(
				'totals' => &$t2 ,
				'total'  => &$total,
				'taxes'  =>  &$tax
			);
			$taxes = null;

		} else {
			$totals = array();
			$excluded = array();
			$taxes = $this->getTaxes();
		}

		ADK( __NAMESPACE__ )->load->model( 'extension/extension' );
		$this->enableShipping = false;

		$sort_order = array();
		$results = ADK( __NAMESPACE__ )->model_extension_extension->getExtensions( 'total' );
		foreach ( $results as $key => $value ) {
			$sort_order[ $key ] = $this->config->get( $value['code'] . '_sort_order' );
		}

		array_multisort( $sort_order, SORT_ASC, $results );
		foreach( $results as $result ) {
			if ( ! $this->config->get( $result[ 'code' ] . '_status' ) ) {
				continue;
			}

			$route = ADK( __NAMESPACE__ )->u()->get_route( 'total/' . $result['code'] );
			$model_name = 'model_' . str_replace( array( '/'), '_', $route );
			ADK( __NAMESPACE__ )->load->model( $route );
			$tot = ADK( __NAMESPACE__ )->{$model_name};

			if(
				$result['code'] !== 'tax' &&
				! $this->isInRecurring( $result['code'] )
			) {
				if(
					! $next &&
					( $this->isAddForce( $this->products[ 0 ][ 'product_id' ] , $result[ 'code' ] ) ||
						$this->lineCount( $result[ 'code' ] , $line ) < 1 )
				) {
					if( $result[ 'code' ] === 'coupon' ) {
						$this->applyCoupon( $excluded , $total , $taxes );

					} else if( $result[ 'code' ] === 'voucher' ) {
						$this->applyVoucher( $excluded, $total, $taxes );
					} else {
						$tot->getTotal( $excluded , $total , $taxes );
					}

					if( $result[ 'code' ] == 'shipping' ) {
						$this->enableShipping = true;
					}
				}

			} else {
				if( $result[ 'code' ] == 'coupon' ) {
					$this->applyCoupon( $totals , $total , $taxes );

				} else if( $result[ 'code' ] == 'voucher' ) {
					$this->applyVoucher( $totals, $total, $taxes );

				} else {
					$tot->getTotal( $totals , $total , $taxes );

					if( $result[ 'code' ] == 'shipping' ) {
						$this->enableShipping = true;
					}
				}
			}
		}

		if ( version_compare( VERSION, '2.2.0.0', '>=' ) ) {
			return array( 'recurring' => $t1 , 'one_time' => $t2 , );
		}

		return array( 'recurring' => $totals , 'one_time' => $excluded , );
	}

	/**
	 * Defines whether to put total line to recurring totals
	 * @param String $code Total line code
	 * @return Boolean
	 */
	protected function isInRecurring( $code ) {
		$plan = new Resource\OC_Plan( $this->profileId );

		return in_array( $code , $plan->profile->totals_to_recurring );
	}

	/**
	 * Defines whether to consider product option when calculate recurring price
	 * @param String $profileId Product profile ID
	 * @return Boolean
	 */
	protected function isConsiderOptions( $profileId ) {
		$plan = new Resource\OC_Plan( $this->profileId );

		return $plan->profile->price_options;
	}

	/**
	 * Defines whether to create ordinary order on the first payment
	 * @param String $profileId Product profile ID
	 * @return Boolean
	 */
	protected function isFirstOrder( $profileId ) {
		$plan = new Resource\OC_Plan( $this->profileId );

		return $plan->profile->first_order;
	}

	/**
	 * Defines if total should be present in non-recurring charge
	 * @param String $code Total line code
	 * @return Boolean
	 */
	protected function isInOrdinary( $code ) {
		$plan = new Resource\Profile();
		$plan->load_non_recurring();

		return in_array( $code , $plan->totals_to_recurring );
	}

	/**
	 * Returns tax rate by name
	 * @param String $name tax name
	 * @return Array|null
	 */
	public function getRateByName( $name ) {
		if( ! $this->taxRates ) {
			$tax_query = $this->db->query(
				"SELECT * FROM `" . DB_PREFIX . "tax_rate` WHERE 1=1"
			);

			foreach( $tax_query->rows as $q ) {
				$this->taxRates[ $q[ 'name' ] ] = $q;
			}
		}

		return isset( $this->taxRates[ $name ] ) ? $this->taxRates[ $name ] : null;
	}

	/**
	 * Adds total line and merges lines
	 * @param array $totals Total lines
	 * @return array
	 */
	public function fixTotals( &$totals ) {
		$total = 0;
		$totalLine = null;
		$exists = array();

		for( $i = 0; $i < count( $totals ); ++$i ) {

			if( ! $totals[ $i ] ) {
				array_splice( $totals , $i-- , 1 );
				continue;
			}

			if( $totals[ $i ][ 'code' ] == 'total' ) {
				$totalLine = $total[ $i ];
				array_splice( $totals , $i-- , 1 );
				continue;
			}

			$total += $totals[ $i ][ 'value' ];
			$name = $totals[ $i ][ 'code' ] . $totals[ $i ][ 'title' ];

			if( ! array_key_exists( $name , $exists ) ) {
				$exists[ $name ] = &$totals[ $i ];

			} else {
				$exists[ $name ][ 'value' ] += $totals[ $i ][ 'value' ];
				array_splice( $totals , $i-- , 1 );
				continue;
			}

		}

		if( ! count( $totals ) ) {
			return $totals;
		}

		if( ! $totalLine ) {
			$totals[] = array(
				'code'       => 'total',
				'value'      => $total,
				'title'      => ADK( __NAMESPACE__ )->__( 'Total' ),
				'sort_order' => 100,
			);

		} else {
			$totalLine[ 'value' ] = $total;
			$totals[] = $totalLine;
		}

		return $totals;
	}

	/**
	 * Checks whether cart contains only recurring product
	 * @param Array $products Cart products list
	 * @return Boolean
	 */
	public function onlyRecurring() {
		if( ! $this->backup) {
			if( is_null( $this->isOnlyRecurring ) ) {
				foreach( $this->getProducts() as $product ) {
					if( ! $product[ 'recurring' ] ) {
						$this->isOnlyRecurring = false;
						break;
					}
				}
			}

			$this->isOnlyRecurring = true;

			return $this->isOnlyRecurring;

		} else {
			return ! $this->ordinary;
		}
	}

	/**
	* Fixes recurring products price
	* Substitute product's price by recurring plan price
	* @return Object
	*/
	public function fixRecurringPrice() {
		foreach( $this->products as &$product ) {

			if( empty( $product[ 'recurring' ] ) ) {
				continue;
			}

			$optPrice = 0;

			if( $this->isConsiderOptions( $product[ 'recurring' ][ 'recurring_id' ] ) && $product[ 'option' ] ) {

				foreach( $product[ 'option' ] as $option ) {
					$optPrice += $option[ 'price' ] * ( $option[ 'price_prefix' ] == '+' ? 1 : -1 );
				}
			}

			$product[ 'old_price' ] = $product[ 'price' ];
			$product[ 'old_total' ] = $product[ 'total' ];
			$product[ 'price' ] = $this->has_trial( $product ) ?
				$product[ 'recurring' ][ 'trial_price' ] :
				$product[ 'recurring' ][ 'price' ] + $optPrice;
			$product[ 'total' ] = $product[ 'price' ] * $product[ 'quantity' ];
		}

		return $this;
	}

	/**
	 * Checks whether product has recurring trial period
	 * @param array $product 
	 * @return boolean
	 */
	public function has_trial( $product ) {
		return $product['recurring']['trial'] &&
		(int)$product['recurring']['trial_cycle'] > 0 &&
		(int)$product['recurring']['trial_duration'] > 0;
	}
	
	/**
	 * Resets initial product price
	 * @return void
	 */
	public function resetRecurringPrice() {
		foreach( $this->products as &$product ) {
			if( isset( $product[ 'old_price' ] ) && isset( $product[ 'old_total' ] ) ) {
				$product[ 'price' ] = $product[ 'old_price' ];
				$product[ 'total' ] = $product[ 'old_total' ];
			}
		}
	}

	/**
	 * Returns recurring products price (for next charge)
	 * Substitute product's price by recurring plan price
	 * @return Object
	 */
	public function getRecurringPrice() {
		foreach( $this->products as &$product ) {
			if( ! isset( $product[ 'recurring' ] ) || ! $product[ 'recurring' ] ) {
				continue;
			}

			$optPrice = 0;
			if( $this->isConsiderOptions( $product[ 'recurring' ][ 'recurring_id' ] ) && $product[ 'option' ] ) {
				foreach( $product[ 'option' ] as $option ) {
					$optPrice += $option[ 'price' ] * ( $option[ 'price_prefix' ] == '+' ? 1 : -1 );
				}
			}

			$product[ 'price' ] = $product[ 'recurring' ][ 'price' ] + $optPrice;
			$product[ 'total' ] = $product[ 'price' ] * $product[ 'quantity' ];
		}

		return $this;
	}

	/**
	 * Process totals fetching
	 * @return void
	 */
	public function process() {
		if( $this->isProcessed ) {
			return;
		}

		unset( $this->session->data[ 'adk_totals' ] );

		foreach( $this->getProducts() as $product ) {
			if( isset( $this->p[ $product[ 'product_id' ] ] ) ) {
				$mess = ADK( __NAMESPACE__ )->__( 'Current method does not allow to assign different profiles to similar products. Please use another payment method' );
				ADK( __NAMESPACE__ )->log( $mess, ADK( __NAMESPACE__ )->log_error_flag );
				throw new Exception( $mess );
			}

			$this->p[ $product[ 'product_id' ] ] = $product;
		}

		unset( $product );

		$this->extractOrdinary();
		$this->ordinaryTotals = $this->getTotals();
		$this->extractRecurring();
		$products = $this->getProducts();
		$this->recurringTotals = array();

		foreach( $products as $product ) {
			$this->setProducts( array( $product ) );
			$this->fixRecurringPrice();
			$this->profileId = $product[ 'recurring' ][ 'recurring_id' ];
			$this->recurringTotals[ $product[ 'product_id' ] ] = $this->getRecurringTotals();
		}

		//Second run
		$this->couponApplied = 0;
		$this->voucherApplied = 0;

		$this->setProducts( $this->ordinary );
		$this->ordinaryTotals = $this->getTotals( $this->ordinaryTotals );

		foreach( $products as $product ) {
			$this->setProducts( array( $product ) );
			$this->fixRecurringPrice();
			$this->profileId = $product[ 'recurring' ][ 'recurring_id' ];
			$this->recurringTotals[ $product[ 'product_id' ] ] = $this->getRecurringTotals(
				$this->recurringTotals[ $product[ 'product_id' ] ]
			);
		}

		//Get next charges
		$this->couponApplied = 0;
		$this->couponUsed++;
		$this->couponUsedCustomer++;

		foreach( $products as $product ) {
			$this->setProducts( array( $product ) );
			$this->profileId = $product[ 'recurring' ][ 'recurring_id' ];
			$this->getRecurringPrice();
			$next = $this->getRecurringTotals( $this->recurringTotals[ $product[ 'product_id' ] ] , true );
			$this->nextTotals[ $product[ 'product_id' ] ] = $next[ 'recurring' ];
			$this->couponUsed++;
			$this->couponUsedCustomer++;
			$this->couponApplied = 0;
		}

		$o = array();
		$o = array_merge( $o , $this->ordinaryTotals );

		foreach( $this->recurringTotals as $rt ) {
			$o = array_merge( $o , $rt[ 'one_time' ] , $rt[ 'recurring' ] );
		}

		$this->fixTotals( $o );
		$this->session->data[ 'adk_totals' ] = $o;
		$this->isProcessed = true;
	}

	/**
	 * Counts total line
	 * @param String $code Total line code to search for
	 * @return Integer
	 */
	protected function lineCount( $code , &$line = null ) {
		$count = 0;

		if( $line !== $this->ordinaryTotals ) {
			foreach( $this->ordinaryTotals as $t ) {
				if( $t[ 'code' ] === $code ) {
					$count++;
				}
			}
		}

		foreach( $this->recurringTotals as $p_id => $rt ) {
			if( ! ( ! $line || $line !== $rt ) ) {
				continue;
			}

			foreach( $rt[ 'recurring' ] as $t1 ) {
				if( ! $t1 ) {
					continue;
				}

				if( $t1[ 'code' ] === $code ) {
					$count++;
				}
			}

			foreach( $rt[ 'one_time' ] as $t2 ) {
				if( ! $t2 ) {
					continue;
				}

				if( $t2[ 'code' ] === $code ) {
					$count++;
				}
			}
		}

		return $count;
	}

	/**
	 * Returns tax code string
	 * @param Array $t Total line
	 * @return String;
	 */
	protected function getTaxCode( $t ) {
		$code = $t['code'];

		if( $code === 'tax' ) {
			$tr = $this->getRateByName( $t['title'] );
			$code .= '_' . $tr['tax_rate_id'];
		}

		return $code;
	}

	/**
	 * Defines if total line should be add to totals by force
	 * @param Integer $p_id Recurring product id
	 * @param String $code Total line code
	 * @return Boolean
	 */
	protected function isAddForce( $p_id , $code ) {
		if( ! isset( $this->p[ $p_id ]['recurring']['recurring_id'] ) ) {
			return false;
		}

		$plan = new Resource\OC_Plan( $this->p[ $p_id ][ 'recurring' ][ 'recurring_id' ] );
	
		return in_array( $code , $plan->profile->add_force );
	}

	/**
	 * Finds total line
	 * @param Array $where Array of totals where to search
	 * @param String $what Total name to search for
	 * @return Array|null
	 */
	public function &getLine( &$where , $what ) {
		for( $i = 0, $len = count( $where ); $i < $len; $i++ ) {
			if( $where[ $i ][ 'code' ] === $what ) {
				return $where[ $i ];
			}
		}

		$v = null;

		return $v;
	}

	/**
	 * Finds tax total line by tax name
	 * @param Array $where Array of totals where to search
	 * @param String $what Total name to search for
	 * @return Array|null
	 */
	protected function &getTaxLineByName( &$where , $what ) {
		foreach( $where as &$w ) {
			if( $w[ 'code' ] === 'tax' && $w[ 'name' ] = $what ) {
				return $w;
			}
		}

		return null;
	}

	/**
	 * Returns totals for ordinary part of an order
	 *
	 * @return Array
	 */
	public function ordinaryTotals() {
		$ret = array();

		unset( $this->session->data[ 'adk_one_time' ] );
		$this->process();

		if( ! $this->ordinaryTotals ) {
			return $ret;
		}

		$this->fixTotals( $this->ordinaryTotals );
		$this->sortTotals( $this->ordinaryTotals );
		$ret['total'] = $this->ordinaryTotals;
		$ret['product'] = $this->ordinary;

		$tax = 0;
		$subTotal = 0;

		foreach( $this->ordinaryTotals as $line ) {
			if( $line[ 'code' ] === 'total' ) {
				continue;
			}

			if( $line[ 'code' ] === 'tax' ) {
				$tax += $line[ 'value' ];

			} else {
				$subTotal += $line[ 'value' ];
			}
		}

		$ret['charge'] = array(
			'tax'       => $tax,
			'sub-total' => $subTotal,
			'total'     => $tax + $subTotal,
		);

		$this->session->data['adk_one_time'] = $ret;

		return $ret;
	}

	/**
	 * Returns totals for recurring part of an order
	 * @return Array
	 */
	public function recurringTotals() {
		unset( $this->session->data[ 'adk_recurring' ] , $this->session->data[ 'adk_next' ] );

		$this->process();

		foreach( $this->recurringTotals as &$total ) {
			$recurring = &$total['recurring'];
			$one_time = &$total['one_time'];
			$this->fixTotals( $recurring );
			$this->fixTotals( $one_time );
			$this->sortTotals( $recurring );
			$this->sortTotals( $one_time );
		}

		unset( $recurring , $one_time , $total );
		
		$ret = array();

		foreach( $this->recurringTotals as $p_id => $l ) {
			$this->fixTotals( $this->nextTotals[ $p_id ] );
			$this->sortTotals( $this->nextTotals[ $p_id ] );
			$ret[ $p_id ] = array(
				'total'   => $l,
				'next'    => $this->nextTotals[ $p_id ],
				'product' => array(),
  				'charge'  => array(),
			);

			$oneTime = $l['one_time'];
			$recurring = $l['recurring'];

			// Add charge
			$subTotal = 0;
			$tax = 0;

			foreach( $oneTime as $ott ) {
				if( $ott['code'] === 'total' ) {
					continue;
				}

				if( $ott['code'] === 'tax' ) {
					$tax += $ott['value'];

				} else {
					$subTotal += $ott['value'];
				}
			}

			$ret[ $p_id]['charge']['one_time'] = array(
				'tax'       => $tax,
				'sub_total' => $subTotal,
				'total'     => $tax + $subTotal,
			);

			$subTotal = 0;
			$tax = 0;
			$invoiceLine = array( 'value' => 0 , 'description' => array() , );

			foreach( $recurring as $rtt ) {
				if( ! $rtt ) {
					continue;
				}

				if( $rtt['code'] === 'total' ) {
					continue;
				}

				if ( $rtt['code'] !== 'sub_total' ) {
					$invoiceLine['value'] += $rtt['value'];
					$invoiceLine['description'][] = $rtt['title'];

				} else {
					$price = $this->p[ $p_id ]['recurring']['trial'] ?
						0 : $this->p[ $p_id ][ 'recurring' ][ 'price' ];

					if( ( $dif = $rtt['value'] - ( $price * $this->p[ $p_id ]['quantity'] ) ) != 0 ) {
						$invoiceLine['value'] += $dif;
					}

					$subTotal += ( $rtt['value'] - $dif );
				}
			}

			$invoiceLine['description'] = implode( ', ' , $invoiceLine['description'] );
			$ret[ $p_id ]['charge']['recurring'] = array(
				'tax'          => $tax,
				'sub_total'    => $subTotal,
				'total'        => $tax + $subTotal,
				'invoice_line' => $invoiceLine,
			);

			// Add product
			foreach( $this->recurring as $p ) {
				if( $p['product_id'] == $p_id ) {
					$ret[ $p_id ]['product'] = $p;
					break;
				}
			}
		}

		$this->session->data['adk_recurring'] = $ret;
		$this->session->data['adk_next'] = $this->nextTotals;

		return $ret;
	}

	/**
	 * Returns products for one time charge
	 * @return Array
	 */
	public function ordinaryProducts() {
		$p = array();

		foreach( $this->ordinary as $product ) {
			$p[ $product['product_id'] ] = $product;
		}

		return $p;
	}

	/**
	 * Returns products for recurring charge
	 * @return Array
	 */
	public function recurringProducts() {
		$p = array();

		foreach( $this->recurring as $product ) {
			$p[ $product['product_id'] ] = $product;
		}

		return $p;
	}

	/**
	 * Sorts totals array
	 * @param Array $totals Totals array to sort
	 */
	public function sortTotals( &$totals ) {
		for( $i = 1, $len = count( $totals ); $i < $len; ++$i ) {
			$current = $i;

			while( $current > 0 && $totals[ $current ]['sort_order'] < $totals[ $current - 1 ]['sort_order'] ) {
				$temp = $totals[ $current - 1 ];
				$totals[ $current -1 ] = $totals[ $current ];
				$totals[ $current ] = $temp;
				$current--;
			}
		}

		return $totals;
	}

	/**
	 * Extracts recurring products from products list
	 * @return Array
	 */
	public function extractRecurring() {
		if( ! $this->recurring && ! $this->ordinary ) {
			$this->separate();
		}

		$this->products = $this->recurring;
		$this->isRecurring = true;
		$this->isOriginal = false;
		$this->isOrdinal = false;

		return $this->products;
	}

	/**
	 * Extracts ordinary products from products list
	 * @return Array
	 */
	public function extractOrdinary() {
		if( ! $this->recurring && ! $this->ordinary ) {
			$this->separate();
		}

		$this->products = $this->ordinary;
		$this->isRecurring = false;
		$this->isOriginal = false;
		$this->isOrdinal = true;

		return $this->products;
	}

	/**
	 * Restores initial cart products
	 * @return Object
	 */
	public function restoreProducts() {
		if( $this->backup ) {
			$this->products = $this->backup;
			$this->backup = array();
			$this->recurring = array();
			$this->ordinary = array();
		}

		$this->isRecurring = false;
		$this->isOriginal = true;
		$this->isOrdinal = false;
		$this->isOnlyRecurring = null;

		return $this;
	}

	/**
	 * Separate products to recurring and non-recurring
	 * @return void
	 */
	public function separate() {
		$this->backup = $this->products;
		$this->recurring = array();
		$this->ordinary = array();

		foreach( $this->products as $product ) {
			if( isset( $product[ 'recurring' ] ) && $product[ 'recurring' ] ) {
				$this->recurring[] = $product;

				if( $this->isFirstOrder( $product[ 'recurring' ][ 'recurring_id' ] ) ) {
					$this->ordinary[] = $product;
				}

			} else {
				$this->ordinary[] = $product;
			}
		}
	}

	/**
	 * Returns coupon
	 * @return array
	 */
	public function getCoupon( $code ) {
		$coupon_query = ADK( __NAMESPACE__ )->db->query(
			"SELECT * FROM `" . DB_PREFIX . "coupon`
			WHERE `code` = '" . $this->db->escape( $code ) . "'
				AND (`date_start` = '0000-00-00' OR `date_start` < NOW() )
				AND (`date_end` = '0000-00-00' OR `date_end` > NOW() )
				AND `status` = '1'"
		);

		try {
			if( ! $coupon_query->num_rows ) {
				throw new \Advertikon\Exception( 'Unresisting coupon' );
			}
			
			if( $coupon_query->row['total'] > $this->getSubTotal() ) {
				throw new \Advertikon\Exception(
					sprintf( 'Min total %s > %s', $coupon_query->row['total'], $this->getSubTotal() )
				);
			}

			if( $coupon_query->row['uses_total'] > 0 ) {
				$coupon_history_query = ADK( __NAMESPACE__ )->db->query(
					"SELECT COUNT(*) AS `total`
					FROM `" . DB_PREFIX . "coupon_history` `ch`
					WHERE `ch`.`coupon_id` = '" . (int)$coupon_query->row['coupon_id'] . "'"
				);
				
				if( $coupon_history_query->row['total'] + $this->couponUsed >= $coupon_query->row[ 'uses_total' ] ) {
					throw new \Advertikon\Exception( 'Count exceeded' );
				}
			}

			if( $coupon_query->row['logged'] && ! $this->customer->getId() ) {
				throw new \Advertikon\Exception( 'Customer not logged' );
			}

			if( $coupon_query->row['uses_customer'] > 0 && $this->customer->getId() ) {

				$coupon_history_query = ADK( __NAMESPACE__ )->db->query(
					"SELECT COUNT(*) AS `total`
					FROM `" . DB_PREFIX . "coupon_history` `ch`
					WHERE `ch`.`coupon_id` = '" . (int)$coupon_query->row['coupon_id'] . "'
						AND `ch`.`customer_id` = '" . (int)$this->customer->getId() . "'"
				);

				if( $coupon_history_query->row['total'] + $this->couponUsedCustomer >= $coupon_query->row['uses_customer'] ) {
					throw new \Advertikon\Exception( 'user count exceeded' );
				}
			}

			// Products
			$coupon_product_data = array();

			$coupon_product_query = ADK( __NAMESPACE__ )->db->query(
				"SELECT * FROM `" . DB_PREFIX . "coupon_product`
				WHERE `coupon_id` = '" . (int)$coupon_query->row['coupon_id'] . "'"
			);

			foreach( $coupon_product_query->rows as $product ) {
				$coupon_product_data[] = $product['product_id'];
			}

			// Categories
			$coupon_category_data = array();

			$coupon_category_query = ADK( __NAMESPACE__ )->db->query(
				"SELECT * FROM `" . DB_PREFIX . "coupon_category` `cc`
					LEFT JOIN `" . DB_PREFIX . "category_path` `cp`
						ON (`cc`.`category_id` = `cp`.`path_id`)
				WHERE `cc`.`coupon_id` = '" . (int)$coupon_query->row['coupon_id'] . "'"
			);

			foreach( $coupon_category_query->rows as $category ) {
				$coupon_category_data[] = $category['category_id'];
			}

			$product_data = array();

			if ($coupon_product_data || $coupon_category_data) {
				foreach ($this->getProducts() as $product) {
					if ( in_array( $product['product_id'], $coupon_product_data ) ) {
						$product_data[] = $product['product_id'];

						continue;
					}

					foreach ( $coupon_category_data as $category_id ) {
						$coupon_category_query = ADK( __NAMESPACE__ )->db->query(
							"SELECT COUNT(*) AS `total`
							FROM `" . DB_PREFIX . "product_to_category`
							WHERE `product_id` = '" . (int)$product['product_id'] . "'
							AND `category_id` = '" . (int)$category_id . "'"
						);

						if ( $coupon_category_query->row['total'] ) {
							$product_data[] = $product['product_id'];

							continue;
						}
					}
				}

				if ( ! $product_data ) {
					throw new \Advertikon\Exception( 'no products' );
				}
			}
			
		} catch( \Advertikon\Exception $e ) {
			return null;
		}

		return array(
			'coupon_id'     => $coupon_query->row['coupon_id'],
			'code'          => $coupon_query->row['code'],
			'name'          => $coupon_query->row['name'],
			'type'          => $coupon_query->row['type'],
			'discount'      => $coupon_query->row['discount'] - $this->couponApplied,
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
	}

	/**
	 * Apples coupon
	 * @return void
	 */
	public function applyCoupon( &$total_data , &$total , &$taxes ) {
		if (
			isset( $total_data['total'] ) &&
			isset( $total_data['taxes'] )
		) {
			$total =& $total_data['total'];
			$taxes =& $total_data['taxes'];
			$totals =& $total_data['totals'];

		}  else {
			$totals =& $total_data;
		}

		if( isset( ADK( __NAMESPACE__ )->session->data[ 'coupon' ] ) ) {
			$this->registry->get( 'load' )->language('total/coupon');
			$coupon_info = $this->getCoupon( $this->session->data['coupon'] );

			try {
				if( ! $coupon_info ) {

					// Do nothing
					throw new \Exception( 'coupon is missing' );
				}

				$discount_total = 0;

				if ( ! $coupon_info[ 'product' ] ) {
					$sub_total = $this->getSubTotal();

				} else {
					$sub_total = 0;

					foreach ( $this->getProducts() as $product ) {
						if( in_array( $product['product_id'], $coupon_info['product'] ) ) {
							$sub_total += $product['total'];
						}
					}
				}

				if( ! $sub_total ) {

					// Do nothing
					throw new \Exception( 'zero subtotal' );
				}

				if ($coupon_info['type'] === 'F') {
					$coupon_info['discount'] = min( $coupon_info['discount'], $sub_total );
				}

				foreach ($this->getProducts() as $product) {
					$discount = 0;

					if ( ! $coupon_info['product'] ) {
						$status = true;

					} else {
						if ( in_array( $product['product_id'], $coupon_info['product'] ) ) {
							$status = true;

						} else {
							$status = false;
						}
					}

					if ( $status ) {
						if ( $coupon_info['type'] === 'F' ) {
							$discount = $coupon_info['discount'] * ( $product['total'] / $sub_total );

						} elseif ( $coupon_info['type'] === 'P' ) {
							$discount = $product['total'] / 100 * $coupon_info['discount'];
						}

						if ( $product['tax_class_id'] ) {
							$tax_rates = $this->tax->getRates(
								$product['total'] - ($product['total'] - $discount ),
								$product['tax_class_id']
							);

							foreach ($tax_rates as $tax_rate) { 
								if ($tax_rate['type'] === 'P') {
									$taxes[ $tax_rate['tax_rate_id'] ] -= $tax_rate['amount'];
								}
							}
						}
					}

					$discount_total += $discount;
				}

				// Apply coupon only if it covers all shipping fee
				if(
					$this->enableShipping && $coupon_info['shipping'] &&
					isset( ADK( __NAMESPACE__ )->session->data[ 'shipping_method' ] )
				) {
					if( ! empty( ADK( __NAMESPACE__ )->session->data['shipping_method']['tax_class_id'] ) ) {
						$tax_rates = ADK( __NAMESPACE__ )->tax->getRates(
							ADK( __NAMESPACE__ )->session->data['shipping_method']['cost'],
							ADK( __NAMESPACE__ )->session->data['shipping_method']['tax_class_id']
						);

						foreach( $tax_rates as $tax_rate ) {
						    if( $tax_rate['type'] === 'P' ) {
						        $taxes[ $tax_rate['tax_rate_id'] ] -= $tax_rate['amount'];
						    }
						}
					}

					$discount_total += ADK( __NAMESPACE__ )->session->data['shipping_method']['cost'];
					$coupon_info['discount'] -= $discount_total;
				}

				// If discount greater than total
				if ($discount_total > $total) {
					$discount_total = $total;
				}

				throw new \Advertikon\Exception( 'stop' );

			} catch( \Advertikon\Exception $e ) {
				if ( $discount_total > 0) {
					$totals[] = array(
						'code'       => 'coupon',
						'title'      => sprintf(
							$this->registry->get( 'language' )->get('text_coupon'),
							ADK( __NAMESPACE__ )->session->data['coupon']
						),
						'value'      => -$discount_total,
						'sort_order' => ADK( __NAMESPACE__ )->config->get( 'coupon_sort_order' )
					);

					$this->couponApplied += $discount_total;
					$total -= $discount_total;
				}

			} catch( \Exception $e ) {
				return null;
			}
		}
	}

	/**
	 * Returns voucher
	 * @param string $code Voucher's code
	 * @return array
	 */
	public function getVoucher( $code ) {
		$status = true;

		$voucher_query = ADK( __NAMESPACE__ )->db->query(
			"SELECT *, `vtd`.`name` AS `theme`
			FROM `" . DB_PREFIX . "voucher` `v`
				LEFT JOIN `" . DB_PREFIX . "voucher_theme` `vt`
					ON (`v`.`voucher_theme_id` = `vt`.`voucher_theme_id`)
				LEFT JOIN `" . DB_PREFIX . "voucher_theme_description` `vtd`
					ON (
					`vt`.`voucher_theme_id` = `vtd`.`voucher_theme_id`
					AND `vtd`.`language_id` = '" . (int)ADK( __NAMESPACE__ )->config->get( 'config_language_id' ) . "'
				)
			WHERE `v`.`code` = '" . ADK( __NAMESPACE__ )->db->escape($code) . "'
				AND `v`.`status` = '1'"
		);

		if ( $voucher_query->num_rows ) {
			if ( $voucher_query->row['order_id'] ) {
				$implode = array();

				foreach ( ADK( __NAMESPACE__ )->config->get( 'config_complete_status' ) as $order_status_id ) {
					$implode[] = "'" . (int)$order_status_id . "'";
				}

				$order_query = ADK( __NAMESPACE__ )->db->query(
					"SELECT * FROM `" . DB_PREFIX . "order`
					WHERE `order_id` = '" . (int)$voucher_query->row['order_id'] . "'
						AND `order_status_id` IN(" . implode(",", $implode) . ")"
				);

				if ( ! $order_query->num_rows ) {
					$status = false;
				}

				$order_voucher_query = ADK( __NAMESPACE__ )->db->query(
					"SELECT * FROM `" . DB_PREFIX . "order_voucher`
					WHERE `order_id` = '" . (int)$voucher_query->row['order_id'] . "'
						AND `voucher_id` = '" . (int)$voucher_query->row['voucher_id'] . "'"
				);

				if ( ! $order_voucher_query->num_rows ) {
					$status = false;
				}
			}

			$voucher_history_query = ADK( __NAMESPACE__ )->db->query(
				"SELECT SUM(`amount`) AS `total`
				FROM `" . DB_PREFIX . "voucher_history` `vh`
				WHERE `vh`.`voucher_id` = '" . (int)$voucher_query->row['voucher_id'] . "'
				GROUP BY `vh`.`voucher_id`"
			);

			if ( $voucher_history_query->num_rows ) {
				$amount = $voucher_query->row['amount'] + $voucher_history_query->row['total'];

			} else {
				$amount = $voucher_query->row['amount'];
			}

			if ($amount <= 0) {
				$status = false;
			}

		} else {
			$status = false;
		}

		if ( $status ) {
			return array(
				'voucher_id'       => $voucher_query->row['voucher_id'],
				'code'             => $voucher_query->row['code'],
				'from_name'        => $voucher_query->row['from_name'],
				'from_email'       => $voucher_query->row['from_email'],
				'to_name'          => $voucher_query->row['to_name'],
				'to_email'         => $voucher_query->row['to_email'],
				'voucher_theme_id' => $voucher_query->row['voucher_theme_id'],
				'theme'            => $voucher_query->row['theme'],
				'message'          => $voucher_query->row['message'],
				'image'            => $voucher_query->row['image'],
				'amount'           => $amount,
				'status'           => $voucher_query->row['status'],
				'date_added'       => $voucher_query->row['date_added']
			);
		}
	}

	/**
	 * Applies voucher
	 * @param array &$total_data Totals
	 * @param number &$total Total
	 * @param array &$taxes Taxes
	 * @return void
	 */
	public function applyVoucher( &$total_data, &$total, &$taxes ) {
		if (
			isset( $total_data['total'] ) &&
			isset( $total_data['taxes'] )
		) {
			$total =& $total_data['total'];
			$taxes =& $total_data['taxes'];
			$totals =& $total_data['totals'];

		}  else {
			$totals =& $total_data;
		}


		if ( isset( ADK( __NAMESPACE__ )->session->data['voucher'] ) ) {
			$this->registry->get( 'load')->language( 'total/voucher' );
			$this->registry->get( 'load' )->model( 'total/coupon' );
			$voucher_info = $this->getVoucher( $this->session->data['voucher'] );

			if ( $voucher_info ) {
				$voucher_info['amount'] -= $this->voucherApplied;

				if ( $voucher_info['amount'] > $total ) {
					$amount = $total;

				} else {
					$amount = $voucher_info['amount'];
				}

				$this->voucherApplied += $amount;

				if ( $amount > 0 ) {
					$totals[] = array(
						'code'       => 'voucher',
						'title'      => sprintf(
							$this->registry->get( 'language' )->get( 'text_voucher' ),
							ADK( __NAMESPACE__ )->session->data['voucher']
						),
						'value'      => -$amount,
						'sort_order' => ADK( __NAMESPACE__ )->config->get( 'voucher_sort_order' )
					);

					$total -= $amount;
				}
			}
		}
	}

}
