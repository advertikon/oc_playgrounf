<?php
/**
 * Catalog Advertikon Stripe Controller
 * @author Advertikon
 * @package Stripe
 * @version 2.8.11
 * 
 * @source catalog/view/theme/default/stylesheet/advertikon/stripe/*
 * @source catalog/view/theme/default/stylesheet/advertikon/advertikon.css
 * 
 * @source catalog/view/javascript/advertikon/advertikon.js
 * @source catalog/view/javascript/advertikon/stripe/*
 * 
 * @source catalog/view/theme/default/template/extension/payment/advertikon/stripe/*
 */
class ControllerExtensionPaymentAdvertikonStripe extends Controller {

	public $a = null;
	public $model = null;

	public function __construct( $registry ) {
		parent::__construct( $registry );
		$this->a = Advertikon\Stripe\Advertikon::instance();
		$this->load->model( $this->a->full_name );
		$this->model = $this->{'model_' . str_replace( '/', '_', $this->a->full_name )};
	}

	/**
	 * Embed payment form
	 * @return void
	 */
	public function index() {
		$data['button_confirm'] = $this->language->get('button_confirm');
		$data['button_back']    = $this->language->get('button_back');
		$data['model']          = $this->model;
		$pay_url = $this->a->u()->url( 'order' );
		$data['hide_button']    = $this->a->config( 'hide_button' );
		$data['months']         = array(
			'01' => '01',
			'02' => '02',
			'03' => '03',
			'04' => '04',
			'05' => '05',
			'06' => '06',
			'07' => '07',
			'08' => '08',
			'09' => '09',
			'10' => '10',
			'11' => '11',
			'12' => '12',
		);
		$data['option']         = new Advertikon\Stripe\Option();

		$data['script_url']     = $this->a->load( array(
			$this->a->u()->catalog_url() . '/catalog/view/javascript/advertikon/advertikon.js',
			$this->a->u()->catalog_url() . '/catalog/view/javascript/advertikon/stripe/form.js',
		) );

		$name = '';

		if(
			isset( $this->session->data['payment_address']['firstname'] )
			&& isset( $this->session->data['payment_address']['lastname'] )
		) {
			$name = $this->session->data['payment_address']['firstname'] . ' ' .
				$this->session->data['payment_address']['lastname'];
		}

		$data['name'] = $name;

		$this->load->model( 'checkout/order' );
		$order_info = $this->model_checkout_order->getOrder( $this->session->data['order_id'] );
		$data['order'] = $order_info;
		$alipay_reusable = $this->a->config( 'checkout_alipay_reusable' );

		// $data['total_val'] = $total;
		$products = $this->cart->getProducts();
		$this->load->model( 'tool/image' );
		$data['image'] = $this->model_tool_image;
		$pop_up_name = $this->config->get( 'config_name' );

		if (
			$this->a->config( 'popup_image' ) === 'product' &&
			count( $products ) === 1 &&
			isset( $products[0]['image'] )
		) {
			$pop_up_image = $this->model_tool_image->resize( $products[0]['image'], 128, 128 );
			
		} else {
			$pop_up_image = $this->model_tool_image->resize( $this->config->get( 'config_logo' ), 128, 128 );
		}

		$description = array();

		foreach( $products as $product ) {
			$description[] = html_entity_decode( $product['name'] );
		}

		$pop_up_description = implode( ',', $description );
		$bitcoin_support = ! (
			$this->cart->hasRecurringProducts() || ! $this->a->config( 'checkout_bitcoin' )
		);

		$data['locale'] = array(
			'pKey'                 => $this->a->get_public_key(),
			'popUpImage'           => addslashes( $pop_up_image ),
			'popUpName'            => addslashes( $pop_up_name ),
			'popUpDescription'     => addslashes( $pop_up_description ),
			'popUpZipCode'         =>  (bool)$this->a->config( 'checkout_zip_code' ),
			'popUpBillingAddress'  => (bool)$this->a->config( 'checkout_collect_payment' ),
			'popUpShippingAddress' => (bool)$this->a->config( 'checkout_collect_shipping' ),
			'popUpLabel'           => $this->a->config( 'checkout_button_caption' ) ?
				addslashes( $this->a->config( 'checkout_button_caption' ) ) :
				$this->a->__( 'Pay' ) . ' {amount}',
			'popUpEmail'           => addslashes( $order_info['email'] ),
			'popUpRememberMe'      => (bool)$this->a->config( 'checkout_remember_me' ),
			'popUpBitcoin'         =>  $bitcoin_support,
			'popUpAlipay'          =>  (bool)$this->a->config( 'checkout_alipay' ),
			'popUpAlipayReusable'  => (bool)$alipay_reusable,
			'availSystems'         => $this->a->config( 'avail_systems' ),
			'sendNotification'     => (bool)$this->a->config( 'error_order_notification' ),
			'errorNotificationUrl' => $this->a->u()->url( 'error_order' ),
			'payUrl'               => $pay_url,
			'zipCheck'             => (bool)$this->a->config( 'zip_check' ),
			'addressCheck'         => (bool)$this->a->config( 'address_check' ),
			'sessionZip'           => isset( $this->session->data['payment_address']['postcode'] ) ?
				$this->session->data['payment_address']['postcode'] : '',

			'sessionLine1'         => isset( $this->session->data['payment_address']['address_1'] ) ?
				$this->session->data['payment_address']['address_1'] : '',

			'sessionLine2'         => isset( $this->session->data['payment_address']['address_2'] ) ?
				$this->session->data['payment_address']['address_2'] : '',

			'sessionCity'          => isset( $this->session->data['payment_address']['city'] ) ?
				$this->session->data['payment_address']['city'] : '',

			'sessionState'         => isset( $this->session->data['payment_address']['zone'] ) ?
				$this->session->data['payment_address']['zone'] : '',

			'sessionCountry'       => isset( $this->session->data['payment_address']['iso_code_2'] ) ?
				$this->session->data['payment_address']['iso_code_2'] : '',

			'compatibilityIsOn'    => (bool)$this->a->config( 'checkout_compatibility' ),
			'mobileCompatibiity'   => (bool)$this->a->config( 'mobile_compatibility' ),

			'waitLibraryLoad'      => $this->a->caption( 'caption_wait_script' ),
			'orderErrorMsg'        => $this->a->caption( 'caption_payment_error' ),
			'orderSuccessMsg'      => $this->a->caption( 'caption_payment_success' ),
			'placingOrderMsg'      => $this->a->caption( 'caption_order_placing' ),
			'cardNumberError'      => $this->a->caption( 'caption_empty_card_number' ),
			'tokenMsg'             => $this->a->caption( 'caption_token_create' ),
			'unknownVendorMsg'     => $this->a->caption( 'caption_unknown_vendor' ),
			'errorVendorMsg'       => $this->a->caption( 'caption_forbidden_vendor' ),
			'compatibilityButtonText' => $this->a->caption( 'compatibility_button_text' ),
			'secretNeededSave'     => $this->a->caption( 'caption_error_card_password_save' ),
			'secretNeededUse'      => $this->a->caption( 'caption_error_card_password_use' ),
		);

		$saved_cards = array();
		$data['show_remember_me'] = false;

		if( $this->a->config( 'remember_me' ) && ! $this->a->config( 'checkout' ) && $this->customer->isLogged() ) {
			try {
				$customer = new Advertikon\Stripe\Resource\Customer( false );
				$stripe_id = $customer->stripe_id;

				if( $stripe_id ) {
					$this->a->set_api_key();
					$stripe_customer = $this->a->fetch_api_customer( $stripe_id );
					$data['default_card'] = $stripe_customer->default_source;
					$saved_cards = $this->a->fetch_api_cards_all( $stripe_customer );
				}

				$data['show_remember_me'] = true;

			} catch ( Exception $e ) {

			}
		}

		$data['saved_cards'] = $saved_cards;

		$vendors = $this->a->config( 'avail_systems' );
		$icon_width = floor( 100 / count( $vendors ) );
		$vendor_max_width = $this->a->config( 'vendor_image_form_width' );
		$data['vendors_tab'] = '';

		foreach( $vendors as $s ) {
			$data['vendors_tab'] .=
			'<div class="adk-vendors" style="width: ' . $icon_width . '%" >' .
			sprintf(
				'<img src="%s" style="max-width: %spx">',
				$this->a->u()->catalog_url( 'auto' ) . 'image/advertikon/stripe/' . $s . '.svg',
				$vendor_max_width['value']
			) .
			'</div>';
		}

		$form_max_width = $this->a->config( 'form_width' );
		$data['form_max_width'] = sprintf( 'max-width: %spx', $form_max_width['value'] );

		try {
			$data['recurring_invoices'] = $this->model->get_recurring_invoices( $this->cart->getProducts() );

			// Calculate total after calculation of all the recurring products
			if ( ! is_null( $this->a->invoice_total ) ) {
				$total = $this->a->invoice_total;
				// $currency = $this->model->get_recurring_currency();

			} else {
				$total = $order_info['total'];
			}

			$currency = $this->a->get_order_currency( $order_info['currency_code'] );
			$total = $this->currency->convert(
				$total,
				$this->config->get( 'config_currency' ),
				$currency
			);

			$data['total'] = $total;
			$data['currency'] = $currency;
			$total = $this->a->amount_to_cents( $total, $currency );
			$data['locale']['popUpTotal'] = $total;
			$data['locale']['popUpCurrency'] = addslashes( $currency );
			$data['locale'] = json_encode( $data['locale'] );
			$data['a'] = $this->a;

			return $this->load->view( $this->a->get_template( $this->a->type . '/advertikon/stripe/form' ) , $data );

		} catch( Advertikon\Stripe\Exception $e ) {
			$this->a->log( $e->getMessage(), $this->a->log_error_flag );

			return '<div style="color:#FFFFFF;font-weight:bold;background-color:#F11F1F;text-align:center;padding:5px">' .
						$e->getMessage() .
					'</div>';

		} catch ( Exception $e ) { 
			$this->a->log( $e->getMessage(), $this->a->log_error_flag );

			return '<div style="color:#FFFFFF;font-weight:bold;background-color:#F11F1F;text-align:center;padding:5px">' .
						$this->a->caption( 'caption_script_error' ) .
					'</div>';
		}
	}

	/**
	 * Place order action
	 * @return void
	 */
	public function order() {
		$json = array();

		$this->a->log( 'Start Ordering', $this->a->log_debug_flag );

		try{
			$this->model->pay();
			$json['success'] = $this->url->link( 'checkout/success', '', 'SSL' );

		} catch( Advertikon\Stripe\Exception $e ) {
			$json['error'] = $e->getMessage();

		} catch ( Stripe\Error\Card $e ) {
			$json['error'] = $e->getMessage();

		} catch( Exception $e ) {
			$this->a->log( $e->getMessage(), $this->a->log_error_flag );
			$json['error'] = $this->a->caption( 'caption_payment_error' );
		}

		$this->response->setOutput( json_encode( $json ) );
	}

	/**
	 * Payment error action
	 * Sends email notification with $_POST['error'] message
	 * @return void
	 */
	public function error_order() { 
		if( isset( $this->request->post['error'] ) ) {
			try{
				if( ! $error = $this->request->post['error'] ) {
					throw new Advertikon\Exception( 'an error message is missing' );
				}

				$this->session->data['adk_current_error_message'] = $error;

				if( ! $this->a->config( 'error_order_notification' ) ) {
					throw new Advertikon\Exception( 'disabled by settings' );
				}

				$admin_email = $this->config->get( 'config_email' );

				if( version_compare( VERSION, '2.3.0.0', '>=' ) ) {
					$additional_emails = $this->config->get( 'config_mail_alert_email' );

				} else {
					$additional_emails = $this->config->get( 'config_mail_alert' );
				}

				if( empty ( $admin_email ) && empty ( $additional_emails ) ) {
					throw new Advertikon\Exception( 'no recipients specified' );
				}

				$emails = array();

				if( $admin_email ) {
					$emails[] = $admin_email;
				}
				
				if ( ! empty( $additional_emails ) ) {
					$emails = array_merge( $emails , explode( ',' , $additional_emails ) );
				}

				array_walk( $emails , function ( &$val , $key ) {
					$val = trim( $val );
				} );

				$emails = array_unique( $emails );

				$html = $thi->load->view(
					$this->a->get_template( 'advertikon/stripe/error' ),
					array( 'body' => $this->$a->config( 'template/error_order_notification' ), 'a' => $this->a )
				);

				if( ! $html ) {
					throw new Advertikon\Exception( 'empty message template' );
				}

				$shortcode = new Advertikon\Shortcode();
				$html = $shortcode->do_shortcode( $html );

				foreach( $emails as $to ) {
					$this->a->mail( trim( $to ) , $this->a->__( 'Stripe gateway error' ) , $html );
				}
			}

			catch( Advertikon\Exception $e ) {
				$mess = $this->a->__( 'Failed to sent error notification: %s', $e->getMessage() );
				$this->a->log( $mess, $this->$a->log_error_flag );
			}
		}

		unset( $this->session->data['adk_current_error_messqge'] );
	}

	/**
	 * Saved cards management page
	 * @return void
	 */
	public function account_cards() {

		if ( ! $this->customer->isLogged() ) {
			$this->session->data['redirect'] = $this->a->u()->url( 'account_cards' );
			$this->response->redirect( $this->a->u()->url( 'account/login' ) );
		}

		$this->document->setTitle( $this->a->__( 'My cards' ) );
		$this->document->addScript( $this->a->u()->catalog_url() . 'catalog/view/javascript/advertikon/advertikon.js' );
		$this->document->addStyle( $this->a->u()->catalog_url() . 'catalog/view/theme/default/stylesheet/advertikon/advertikon.css' );

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->a->__( 'Home' ),
			'href' => $this->url->link( 'common/home' ),
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->a->__( 'Account' ),
			'href' => $this->url->link( 'account/account', '', 'SSL' )
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->a->__( 'My cards' ),
			'href' => $this->url->link( 'payment/', '', 'SSL')
		);

		$data['heading_title'] = $this->a->__( 'My cards' );
		$data['button_back'] = $this->a->__( 'Back' );

		$data['back'] = $this->url->link('account/account', '', 'SSL');

		$cards = null;
		$default_card = null;

		if( $this->customer->isLogged() ) {
			$oc_stripe_customer = new Advertikon\Stripe\Resource\Customer( false );

			if ( $oc_stripe_customer->stripe_id ) {

		    	// Fetch customer from Stripe
		    	try{
		    		$this->a->set_api_key();
	    			$stripe_api_customer = $this->a->fetch_api_customer( $oc_stripe_customer->stripe_id );
		    		$cards = $this->a->fetch_api_cards_all( $stripe_api_customer );
		    		$default_card = $stripe_api_customer->default_source;

		    	} catch( Exception $e ) {
		    	
		    	}
		    }
		}

		$data['cards']          = $cards;
		$data['a']              = $this->a;
		$data['default_card']   = $default_card;
		$data['column_left']    = $this->load->controller( 'common/column_left' );
		$data['column_right']   = $this->load->controller( 'common/column_right' );
		$data['content_top']    = $this->load->controller( 'common/content_top' );
		$data['content_bottom'] = $this->load->controller( 'common/content_bottom' );
		$data['footer']         = $this->load->controller( 'common/footer' );
		$data['header']         = $this->load->controller( 'common/header' );

		$data['locale']         = array(
			'deleteCard'   => $this->a->__( 'Are you really want to delete the card?' ),
			'deleteUrl'    => $this->a->u()->url( 'delete_card' ),
			'networkError' => $this->a->__( 'Network error' ),
			'cardDeleted'  => $this->a->__( 'Card has been deleted' ),
			'scriptError'  => $this->a->__( 'Script error' ),
			'defaultUrl'   => $this->a->u()->url( 'default_card' ),
			'cardChanged'  => $this->a->__( 'Default card has been changed' ),
		);

		$data['a'] = $this->a;

		$this->response->setOutput(
			$this->load->view(
				$this->a->get_template( $this->a->type . '/advertikon/stripe/account_cards' ),
				$data
			)
		);
	}

	/**
	 * Delete saved card action
	 * @return void
	 */
	public function delete_card() {
		$ret = array();
		$card_name = '';

		$log = $this->a->config( 'log_activity' );

		if( $log ) {
			$this->load->model( 'account/activity' );
		}

		try {

			if( ! $this->customer->isLogged() ) {
				$log = false;
				throw new Advertikon\Exception( $this->a->__( 'Current session has expired' ) );
			}

			if( empty( $this->request->request['card_id'] ) ) {
				throw new Advertikon\Exception( $this->a->__( 'Card ID is missing' ) );
			}

			$card_id = $this->request->request['card_id'];
			$oc_stripe_customer = new Advertikon\Stripe\Resource\Customer( false );

			if ( ! $oc_stripe_customer->stripe_id ) {
				throw new Advertikon\Exception( $this->a->__( 'You have no saved cards' ) );
		    }
		    	
    		$this->a->set_api_key();
			$stripe_api_customer = $this->a->fetch_api_customer( $oc_stripe_customer->stripe_id );

			if(
				$stripe_api_customer->subscriptions->total_count > 0 &&
				$stripe_api_customer->sources->total_count < 2
			) {
				throw new Advertikon\Exception(
					$this->a->__(
						'You can not delete the only payment source until you have an active subscription'
					)
				);
			}

			$card = $stripe_api_customer->sources->retrieve( $card_id );
			$card_name = $card->brand . ' **** ' . $card->last4 . ' (' . $card->exp_month . '/' . $card->exp_year . ')';
			$card->delete();
			$this->cache->delete( $oc_stripe_customer->stripe_id );

			if( $log ) {
	    		$activity_data = array(
					'customer_id' => $this->customer->getId(),
					'name'        => $this->customer->getFirstName() . ' ' . $this->customer->getLastName(),
					'card'        => $card_name,
				);

				$this->model_account_activity->addActivity( 'delete_card', $activity_data );
			}

			if( $stripe_api_customer->default_source === $card_id ) {
				$stripe_api_customer = $this->a->fetch_api_customer( $oc_stripe_customer->stripe_id );
	    		$ret['default_source'] = $stripe_api_customer->default_source;
			}

    		$ret['success'] = '1';

		} catch ( Advertikon\Exception $e ) {

			// Log activity
			if( $log ) {
				$activity_data = array(
					'customer_id' => $this->customer->getId(),
					'name'        => $this->customer->getFirstName() . ' ' . $this->customer->getLastName(),
					'card_name'   => $card_name,
					'message'     => $e->getMessage(),
				);

				$this->model_account_activity->addActivity( 'fail_delete_card', $activity_data );
			}

			$ret['error'] = $e->getMessage();
		}

		$this->response->setOutput( json_encode( $ret ) );
	}

	/**
	 * Make saved card default
	 * @return void
	 */
	public function default_card() {
		$ret = array();
		$new_card_name = '';
		$old_card_name = '';

		$log = $this->a->config( 'log_activity' );

		if( $log ) {
			$this->load->model( 'account/activity' );
		}

		try {

			if( ! $this->customer->isLogged() ) {
				$log = false;
				throw new Advertikon\Exception( $this->a->__( 'Current session has expired' ) );
			}

			if( empty( $this->request->request['card_id'] ) ) {
				throw new Advertikon\Exception( $this->a->__( 'Card ID is missing' ) );
			}

			$card_id = $this->request->request['card_id'];
			$oc_stripe_customer = new Advertikon\Stripe\Resource\Customer( false );

			if ( ! $oc_stripe_customer->stripe_id ) {
				throw new Advertikon\Exception( $this->a->__( 'You have no saved cards' ) );
		    }
		    	
    		$this->a->set_api_key();
			$stripe_api_customer = $this->a->fetch_api_customer( $oc_stripe_customer->stripe_id );

			$card = $stripe_api_customer->sources->retrieve( $card_id );
			$new_card_name = $card->brand . ' **** ' . $card->last4 . ' (' . $card->exp_month . '/' . $card->exp_year . ')';

			$card = $stripe_api_customer->sources->retrieve( $stripe_api_customer->default_source );
			$old_card_name = $card->brand . ' **** ' . $card->last4 . ' (' . $card->exp_month . '/' . $card->exp_year . ')';

    		$stripe_api_customer->default_source = $card_id;
    		$stripe_api_customer->save();
    		$this->cache->delete( $oc_stripe_customer->stripe_id );

    		if( $log ) {
	    		$activity_data = array(
					'customer_id' => $this->customer->getId(),
					'name'        => $this->customer->getFirstName() . ' ' . $this->customer->getLastName(),
					'old_card'    => $old_card_name,
					'new_card'    => $new_card_name,
				);

				$this->model_account_activity->addActivity( 'default_card', $activity_data);
    		}

    		$ret['success'] = '1';

		} catch ( Advertikon\Exception $e ) {

			if( $log ) {
				$activity_data = array(
					'customer_id' => $this->customer->getId(),
					'name'        => $this->customer->getFirstName() . ' ' . $this->customer->getLastName(),
					'card'        => $card_name,
					'message'     => $e->getMessage(),
				);

				$this->model_account_activity->addActivity( 'fail_default_card', $activity_data);
			}

			$ret['error'] = $e->getMessage();
		}

		$this->response->setOutput( json_encode( $ret ) );
	}

	/**
	 * Pay in one click button contents
	 * @param int $product_id 
	 * @return string
	 */
	public function pay_button( $product_id ) {

		$button = '';

		// If button is enabled
		if( $this->a->config( 'button' ) ) {
			$this->document->addScript( $this->a->u()->admin_url() . 'catalog/view/javascript/advertikon/advertikon.js' );
			$product = $this->a->get_product( $product_id );

			// Missing stock
			if( $product['quantity'] < $product['minimum']  && ! $this->config->get( 'config_stock_checkout' ) ) {
				return '';
			}

			// If product is download-able and customer is not logged - do not show button
			if( $product['download'] && ! $this->customer->isLogged() ) {
				return '';
			}

			// Do not show button if product has at least one required option or recurring plan, associate this it
			if( $this->model->has_required_options( $product) || ! empty( $product['recurring'] ) ) {
				return '';
			}

			// If product requires shipping but customer is not logged or there is no default shipping method - skip
			if(
				$product['shipping'] &&
				( ! $this->customer->isLogged() || $this->a->config( 'button_shipping' ) )
			) {
				return '';
			}

			$totals = $this->model->get_button_totals( $product_id );
			$currency = $this->a->get_order_currency();

			$this->load->model( 'tool/image' );

			$data['image'] = $this->model_tool_image;

			//$pop_up_name = $this->config->get( 'config_name' );

			if (
				$this->a->config( 'popup_image' ) === 'product' &&
				isset( $product['image'] )
			) {
				$pop_up_image = $this->model_tool_image->resize( $product['image'], 128, 128 );
				
			} else {
				$pop_up_image = $this->model_tool_image->resize( $this->config->get( 'config_logo' ), 128, 128 );
			}

			$data['totals'] = $totals;
			//$this->load->model( 'tool/image' );

			// Button caption
			$text = $this->a->config( 'button_text' );
			if( false === strpos( $text, '{{amount}}') ) {
				$text .= ' {{amount}}';
			}

			$pop_up_text = $text;

			if( $this->a->config( 'describe_price' ) ) {
				$text .= '*';
			}

			$order_total = $this->model->get_totals_sum( $totals );

			$order_total = $this->currency->convert(
				$order_total,
				$this->config->get( 'config_currency' ),
				$currency
			);

			$text = str_replace(
				'{{amount}}',
				$this->currency->format( $order_total, $currency, 1 ),
				$text
			);

			$order_total = $this->a->amount_to_cents( $order_total, $currency );

			// CSS styles
			$css = array();

			// Button width
			if( $this->a->config( 'button_full_width' ) ) {
				$css[] = 'width: 100%;';
			}

			// Fill color
			if( ! $background = $this->a->config( 'button_color' ) ) {
				$background = '#008cdd';
			}

			$css[] = "background-color: $background;";

			// Text color
			if( ! $color = $this->a->config( 'button_text_color' ) ) {
				$color = '#ffffff';
			}

			$css[] = "color: $color;";

			// Text height
			$text_height = $this->a->config( 'button_text_height' );

			if( ! is_array( $text_height ) ) {
				$text_height = array();
			}

			if( ! isset( $text_height['value'] ) ) {
				$text_height['value'] = 20;
			}

			if( ! isset( $text_height['units'] ) ) {
				$text_height['units'] = 'px';
			}

			$css[] = "font-size: {$text_height['value']}{$text_height['units']};";

			// Button height
			$height = $this->a->config( 'button_height' );

			if( ! is_array( $height ) ) {
				$height = array();
			}

			if( ! isset( $height['value'] ) ) {
				$height['value'] = 40;
			}

			if( ! isset( $height['units'] ) ) {
				$height['units'] = 'px';
			}

			$css[] = "height: {$height['value']}{$height['units']};";

			// Border radius
			$radius = $this->a->config( 'button_radius' );

			if( ! is_array( $radius ) ) {
				$radius = array();
			}

			if( ! isset( $radius['value'] ) ) {
				$radius['value'] = 5;
			}

			if( ! isset( $radius['units'] ) ) {
				$radius['units'] = 'px';
			}

			$css[] = "border-radius: {$radius['value']}{$radius['units']};";

			// Vertical margins
			$v_margin = $this->a->config( 'button_margin_vertical' );

			if( ! is_array( $v_margin ) ) {
				$v_margin = array();
			}

			if( ! isset( $v_margin['value'] ) ) {
				$v_margin['value'] = 5;
			}

			if( ! isset( $v_margin['units'] ) ) {
				$v_margin['units'] = 'px';
			}

			// Horizontal margins
			$h_margin = $this->a->config( 'button_margin_horizontal' );

			if( ! is_array( $h_margin ) ) {
				$h_margin = array();
			}

			if( ! isset( $h_margin['value'] ) ) {
				$h_margin['value'] = 0;
			}

			if( ! isset( $h_margin['units'] ) ) {
				$h_margin['units'] = 'px';
			}

			$css[] = "margin: {$v_margin['value']}{$v_margin['units']} {$h_margin['value']}{$h_margin['units']};";

			// Render button
			$data['button'] = $this->a->r( array( 
				'type'        => 'button',
				'text_before' => $text,
				'button_type' => '',
				'css'         => implode( ' ', $css ),
				'id'          => 'adk-stripe-button',
				'custom_data' => 'disabled="disabled"',
			) );

			$data['locale'] = json_encode( array(
					'publicKey'   => $this->a->get_public_key(),
					'image'       => $pop_up_image,
					'productId'   => $product_id,
					'name'        => $this->config->get( 'config_name' ),
					'description' => html_entity_decode( $product['name'] ),
					'zipCode'     => $this->a->config( 'checkout_zip_code' ) ? 1 : 0,
					'currency'    => $currency,
					'label'       => $pop_up_text,
					'email'       => $this->customer->getEmail() ? $this->customer->getEmail() : '',
					'rememberMe'  => $this->a->config( 'checkout_remember_me' ) ? 1 : 0,
					'bitcoin'     => $this->a->config( 'checkout_bitcoin' ) ? 1 : 0,
					'alipay'      => $this->a->config( 'checkout_alipay' ) ? 1 : 0,
					'amount'      => $order_total,
					'placingText' => $this->a->caption( 'caption_button_placing' ),
					'payUrl'      => $this->a->u()->url( 'one_button_pay' ),
					'errorText'   => $this->a->caption( 'caption_payment_error' ),
					'modalHeader' => $this->config->get( 'config_name' ),
			) );

			$data['margin_bottom'] = $v_margin['value'] . $v_margin['units'];
			$data['a'] = $this->a;

			$button = $this->load->view(
				$this->a->get_template( $this->a->type . '/advertikon/stripe/button' ),
				$data
			);
		}

		return $button;
	}

	/**
	 * Pay in one click action
	 * @return void
	 */
	public function one_button_pay() {
		$ret = array();

		try {

			if(
				! isset( $this->request->request['token'] ) ||
				! isset( $this->request->request['args'] )
			) {
				$mess = $this->a->__( 'Order is missing' );
				trigger_error( $mess );
				throw new Advertikon\Stripe\Exception( $mess );
			}

			if( ! isset( $this->request->request['product_id'] ) ) {
				$mess = $this->a->__( 'Product\'s ID is missing' );
				trigger_error( $mess );
				throw new Advertikon\Stripe\Exception( $mess );
			}

			$order_data = array_merge( $this->request->request['token'], $this->request->request['args'] );
			$order_data['product_id'] = $this->request->request['product_id'];

			$order_id = $this->model->place_button_pay_order( $order_data );
	    	$order_model = $this->a->get_order_model();
	    	$order = $order_model->getOrder( $order_id );

			if( ! $order ) {
				$mess = $this->a->__( 'Order is missing' );
				trigger_error( $mess );
				throw new Advertikon\Stripe\Exception( $mess );
			}

	    	if ( isset( $this->a->get_custom_field( $order_id )->charge ) ) {
	    		$mess = $this->a->__( 'The order "#%s" has already been placed', $order_id );
	    		trigger_error( $mess );
				throw new Advertikon\Stripe\Exception( $mess );
	    	}

	    	$data = new stdClass();
	    	$data->token = $order_data['id'];
	    	$this->model->make_payment( $order, $data, 'button' );
	    	$ret['success'] = $this->a->caption( 'caption_payment_success' );

		} catch ( Advertikon\Stripe\Exception $e ) {
			trigger_error( $e );
			$ret['error'] = $e->getMessage();

		} catch ( Exception $e ) {
			$ret['error'] = $this->a->caption( 'caption_payment_error' );
		}

		$this->response->setOutput( json_encode( $ret ) );
	}

	/**
	 * Web-hooks action
	 * @return void
	 */
	public function webhooks() {
		$content = file_get_contents( 'php://input' );
		$show_evt = true;
		$out = '';
		try {
			if ( ! $content ) {
				throw new Exception( 'empty request body' );
			}

			$evt = json_decode( $content );
			if ( ! $evt ) {
				throw new Exception( 'unable to parse JSON request body' );
			}

			if ( ! is_object( $evt ) || $evt->object !== 'event' ) {
				throw new Exception( 'event object missing' );
			}

			if ( (bool)$this->a->config( 'test_mode' ) === (bool)$evt->livemode ) {
				throw new Exception( 'Extension has different mode then event' );
			}

			$this->a->log(
				sprintf( 'Event with type "%s" was received', $evt->type ),
				$evt,
				$this->a->log_debug_flag
			);

			header( 'HTTP/1.0 200 OK' );

			switch( $evt->type ) {
			case 'charge.captured' :
				$this->webhook_capture_charge( $evt );
				break;
			case 'charge.refunded' :
				$this->webhook_refund_charge( $evt );
				break;
			case 'plan.updated' : 
				$this->update_plan( $evt );
				break;
			case 'plan.deleted' : 
				$this->remove_plan( $evt );
				break;
			case 'customer.subscription.created' :
				$this->subscription_change_status( $evt );
				$this->callback( $evt );
				break;
			case 'customer.subscription.deleted' :
				$this->subscription_deleted( $evt );
				$this->callback( $evt );
				break;
			case 'customer.subscription.updated' :
				$this->subscription_change_status( $evt );
				break;
			case 'invoice.payment_failed' :
				$this->subscription_pay_fail( $evt );
				break;
			case 'invoice.payment_succeeded' :
				$this->subscription_pay_succeed( $evt );
				break;
			case 'invoice.created' :
				$this->subscription_invoice_created( $evt );
				break;
			}

		} catch( Exception $e ) {
			if ( true ) {
				// include DIR_SYSTEM . 'library/advertikon/terminalColors.php';
				// $red = '';//"\e[0;91m";
				// $off = '';//"\e[0m";
				$out = PHP_EOL . $e->getMessage() . PHP_EOL;

			} else {
				// $red = $off = '';
				$this->a->log(
					sprintf( 'Stripe\'s web-hook error: %s', $e->getMessage() ),
					$this->a->log_error_flag
				);
			}
		}

		echo $out;
	}

	public function log() {
		$this->a->console->tail();
	}

	/**
	 * Mark the charge as captured in respond to web-hook
	 * @param Object $evt Web-hook event object
	 * @throws Advertikon\Stripe\Exception on error
	 * @return void
	 */
	public function webhook_capture_charge( $evt ) {
		$this->a->log(
			'Web-hook notification about capture of a payment was received',
			$this->a->log_debug_flag
		);

		$charge = $this->a->check_webhook_charge_capture( $this->a->check_webhook_charge( $evt ), $evt );
		$sum = $this->a->cents_to_amount( $charge->amount - $charge->amount_refunded, strtoupper( $charge->currency ) );
		$comment = sprintf( 'Capture on sum of %s', $this->currency->format( $sum, strtoupper( $charge->currency ) ) );
		$this->a->mark_order_as_captured(
			$charge->metadata->order_id,
			$comment,
			$charge,
			$this->a->config( 'notify_customer' ),
			$this->a->config( 'override' )
		);
	}

	/**
	 * Mark charge as captured in respond to web-hook
	 * @param Object $evt Web-hook event object
	 * @throws Advertikon\Stripe\Exception on error
	 * @return void
	 */
	public function webhook_refund_charge( $evt ) {
		$this->a->log(
			'Web-hook notification about refund of a charge was received',
			$this->a->log_debug_flag
		);

		$charge = $this->a->check_webhook_charge_refund( $this->a->check_webhook_charge( $evt ), $evt );
		$sum = $this->a->cents_to_amount( $charge->refunds->data[ 0 ]->amount, strtoupper( $charge->currency ) );
		$comment = sprintf( 'Refund on sum of %s', $this->currency->format( $sum, strtoupper( $charge->currency ) ) );
		$this->a->mark_order_as_refunded(
			$charge->metadata->order_id,
			$comment,
			$charge,
			$this->a->config( 'notify_customer' ),
			$this->a->config( 'override' )
		);
	}

	/**
	 * Updated plan in response to web-hook web-hook
	 * @param Object $evt Event object
	 * @throws Advertikon\Stripe\Exception on error
	 * @return void
	 */
	public function update_plan( $evt ) {
		$id = isset( $evt->data->object->id ) ? $evt->data->object->id : null;
		$account = isset( $evt->data->oject->metadata['account'] ) ?
			 $evt->data->oject->metadata['account']: null;

		if ( is_null( $id ) ) {
			throw new Exception( 'Plan ID is missing' );
		}

		$this->a->set_account( $account );
		$plan = $this->a->fetch_api_plan( $id );
		$this->model->update_plan( $plan );

		$this->a->log(
			sprintf( 'Plan %s was updated at store (account %s)', $id, $this->a->get_account() ),
			$this->a->log_debug_flag
		);
	}

	/**
	 * Deleted plan in response to web-hook
	 * @param Object $evt Event object
	 * @throws Advertikon\Stripe\Exception on error
	 * @return void
	 */
	public function remove_plan( $evt ) {
		$id = isset( $evt->data->object->id ) ? $evt->data->object->id : null;
		$account = isset( $evt->data->oject->metadata['account'] ) ?
			 $evt->data->oject->metadata['account']: null;

		if ( is_null( $id ) ) {
			throw new \Advertikon\Exception( 'Plan ID is missing' );
		}

		$plan = null;
		$this->a->set_account( $account );

		try {
			$plan = $this->a->fetch_api_plan( $id );

		} catch( \Stripe\Error\Base $e ) {

		}

		if ( ! is_null( $plan ) ) {
			throw new \Advertikon\Exception( 'Plan still exists in Stripe' );
		}
		
		$plan_res = new Advertikon\Stripe\Resource\Plan( $id, 'sp_plan_id' );

		if ( $plan_res->is_exists() ) {
			$plan_res->delete();
			$this->a->log(
				sprintf( 'Plan %s was deleted from store (account %s)', $id, $this->a->get_account() ),
				$this->a->log_debug_flag
			);
		}
	}

	/**
	 * Change subscription status in response to web-hook
	 * @param Object $evt Event object
	 * @throws Advertikon\Stripe\Exception on error
	 * @return void
	 */
	public function subscription_change_status( $evt ) {
		if (
			! isset( $evt->data->object->object ) &&
			$evt->data->object->object !== 'subscription'
		) {
			throw new \Advertikon\Exception( 'Subscription object is missing' );
		}

		$subscr = $evt->data->object;
		$account = isset( $subscr->plan->metadata->account ) ? $subscr->plan->metadata->account : null;
		$this->a->set_account( $account );
		$subscr = $this->a->fetch_api_subscription( $subscr->customer, $subscr->id );

		if ( ! isset( $subscr->metadata['recurring_order_id'] ) ) {
			throw new \Advertikon\Exception( 'Recurring order ID is missing' );
		}

		$this->a->update_subscription( $subscr->metadata['recurring_order_id'] );

		$this->a->log(
			sprintf( 'Status of subscription %s was updated (account %s)', $subscr->id, $this->a->get_account() ),
			$this->a->log_debug_flag
		);
	}

	/**
	 * Subscription failed web-hook notification
	 * @param Object $evt Stripe event
	 * @throws Advertikon\Exception on error
	 * @return void
	 */
	public function subscription_pay_fail( $evt ) {
		if (
			! isset( $evt->data->object->object ) &&
			$evt->data->object->object !== 'invoice'
		) {
			throw new Exception( 'Invoice object is missing' );
		}

		$invoice = $evt->data->object;

		if ( isset( $invoice->lines->data ) ) {
			foreach( $invoice->lines->data as $line_item ) {
				if ( isset( $line_item->plan->metadata->account ) ) {
					$this->a->set_account( $line_item->plan->metadata->account );
					break;
				}
			}
		}

		$invoice = $this->a->fetch_api_invoice( $invoice->id );


		if ( ! isset( $invoice->subscription ) ) {
			throw new Exception( 'Subscription is missing' );
		}

		$this->model->subscription_pay_fail( $invoice );
	}

	/**
	 * Subscription succeed web-hook notification
	 * @param Object $evt Stripe event
	 * @throws Advertikon\Exception on error
	 * @return void
	 */
	public function subscription_pay_succeed( $evt ) {
		if (
			! isset( $evt->data->object->object ) &&
			$evt->data->object->object !== 'invoice'
		) {
			throw new Exception( 'Invoice object is missing' );
		}

		$invoice = $evt->data->object;

		if ( isset( $invoice->lines->data ) ) {
			foreach( $invoice->lines->data as $line_item ) {
				if ( isset( $line_item->plan->metadata->account ) ) {
					$this->a->set_account( $line_item->plan->metadata->account );
					break;
				}
			}
		}

		$invoice = $this->a->fetch_api_invoice( $invoice->id );

		if ( ! isset( $invoice->subscription ) ) {
			throw new Exception( 'Subscription is missing' );
		}

		$this->model->subscription_pay_succeed( $invoice );
	}

	/**
	 * Invoice creation web-hook notification
	 * @param Object $evt Stripe event
	 * @throws Advertikon\Exception on error
	 * @return void
	 */
	public function subscription_invoice_created( $evt ) {
		if (
			! isset( $evt->data->object->object ) &&
			$evt->data->object->object !== 'invoiceitem'
		) {
			throw new \Advertikon\Exception( 'Invoice object is missing' );
		}

		$invoice = $evt->data->object;

		if ( isset( $invoice->lines->data ) ) {
			foreach( $invoice->lines->data as $line_item ) {
				if ( isset( $line_item->plan->metadata->account ) ) {
					$this->a->set_account( $line_item->plan->metadata->account );
					break;
				}
			}
		}

		$invoice = $this->a->fetch_api_invoice( $invoice->id );

		if ( ! isset( $invoice->subscription ) ) {
			throw new \Advertikon\Exception( 'Subscription is missing' );
		}

		$this->model->subscription_invoice_created( $invoice );
	}

	/**
	 * Buttons to manage recurring plan for customer's account page
	 * @return String
	 */
	public function recurringButtons() {
		$data  = array();

		$data['order_recurring_id'] = isset( $this->request->request['order_recurring_id'] ) ?
			$this->request->request['order_recurring_id'] :
			( isset( $this->request->request['recurring_id'] ) ? $this->request->request['recurring_id'] : 0 );

		$plan = $this->a->get_oc_plan_by_order( $data['order_recurring_id'] );
		$oc_plan = new \Advertikon\Stripe\Resource\OC_Plan( $plan );

		$data['refresh_url']         = $this->a->u()->url( 'recurring_refresh' );
		$data['delete_now_url']      = $this->a->u()->url( 'recurring_delete_now' );
		$data['delete_period_url']   = $this->a->u()->url( 'recurring_delete' );
		$data['customer_can_cancel'] = $oc_plan->profile->user_abort;
		$data['cancel_now']          = $oc_plan->profile->cancel_now;
		$data['a'] = $this->a;

		return $this->load->view(
			$this->a->get_template( $this->a->type . '/advertikon/stripe/recurring_button' ),
			$data
		);
	}

	/**
	 * Update recurring order
	 * @return void
	 */
	public function recurring_refresh() {
		try {
			if ( ! isset( $this->request->request['order_recurring_id'] ) ) {
				throw new Advertikon\Exception( 'Recurring order ID is missing' );
			}

			$this->a->update_subscription( $this->request->request['order_recurring_id'] );

			if ( defined( 'DIR_CATALOG' ) ) {
				$this->load->controller( 'sale/recurring/info' );

			} else {
				$this->load->controller( 'account/recurring/info' );
			}

		} catch( Advertikon\Exception $e ) {
			$this->response->setOutput( $e->getMessage() . 'error' );

		} catch( Exception $e ) {
			$this->response->setOutput( $e->getMessage() );
		}
	}

	/**
	 * Cancel immediately recurring
	 * @return void
	 */
	public function recurring_delete_now() {
		try {
			if ( ! isset( $this->request->request['order_recurring_id'] ) ) {
				throw new Advertikon\Exception( 'Recurring order ID is missing' );
			}

			$this->a->cancel_recurring( $this->request->request['order_recurring_id'], false );
			$this->response->setOutput( $this->a->__( 'Subscription has been canceled' ) . 'success' );

		} catch( Advertikon\Stripe\Exception $e ) {
			$this->response->setOutput( $e->getMessage() . 'error' );

		} catch( \Stripe\Error\Base $e ) {
			$this->response->setOutput( $e->getMessage() . 'error' );

		}
	}

	/**
	 * Cancel subscription at the next period end
	 * @return void
	 */
	public function recurring_delete() {
		try {
			if ( ! isset( $this->request->request['order_recurring_id'] ) ) {
				throw new Advertikon\Exception( 'Recurring order ID is missing' );
			}

			$this->a->cancel_recurring( $this->request->request['order_recurring_id'] );
			$this->response->setOutput(
				$this->a->__(
					'Subscription cancellation was scheduled at the next period end' ) . 'success'
			);

		} catch( Exception $e ) {
			$this->response->setOutput( $e->getMessage() . 'error' );

		} catch( \Stripe\Exception\Base $e ) {
			$this->response->setOutput( $e->getMessage() . 'error' );

		}
	}

	/**
	 * Update subscription status on deletion
	 * @return void
	 */
	public function subscription_deleted( $evt ) {
		if ( empty( $evt->data->object->id ) ) {
			throw new Exception( 'Subscription\'s ID is missing' );
		}

		$subscription = $evt->data->object;

		if ( empty( $subscription->metadata->recurring_order_id ) ) {
			throw new \Aadvertikon\Exception( sprintf( 'Metadata of subscription %s doesn\'t contain recurring order id' ) );
		}

		$account = isset( $subscription->plan->metadata->account ) ?
			$subscription->plan->metadata->account : null;

		$this->a->set_account( $account );

		$this->a->update_subscription( $subscription->metadata->recurring_order_id );

		$this->a->log(
			sprintf(
				'Status of subscription %s was changed (account %s)',
				$subscription->id,
				$this->a->get_account()
			),
			$this->a->log_debug_flag
		);
	}

	/**
	 * Load JS action
	 * @return void
	 */
	public function compress() {
		echo $this->a->compress();die;
	}

	/**
	 * Sends callback on subscription status change
	 * @param object $evt Stripe event object
	 * @return void
	 */
	public function callback( $evt ) {
		$url = '';
		$post = array();
		$data = '';
		$status = '';

		if ( 'customer.subscription.created' === $evt->type ) {
			$url = $this->a->config( 'create_subscription_callback' );
			$data = $this->a->config( 'create_subscription_callback_data' );
			$status = 'new';

		} elseif ( 'customer.subscription.deleted' === $evt->type ) {
			$url = $this->a->config( 'cancel_subscription_callback' );
			$data = $this->a->config( 'cancel_subscription_callback_data' );
			$status = 'cancel';
		}

		if ( ! $url ) {
			return;
		}

		$recurring = new \Advertikon\Stripe\Resource\Recurring( $evt->data->object->id, 'subscription_id' );

		if ( ! $recurring->is_exists() ) {
			$mess = sprintf(
				'Failed to send query to callback URL: stripe subscription %s is not registered in OC store',
				$evt->data->object->id
			);

			trigger_error( $mess );
			throw new \Advertikon\Exception( $mess );
		}

		$oc_customer         = $recurring->order['customer_id'];
		$stripe_customer     = $evt->data->object->customer;
		$oc_subscription     = $recurring->recurring_order_id;
		$stripe_subscription = $evt->data->object->id;

		if ( ! $oc_customer ) {
			$mess = 'Failed to send query to callback URL: OC customer is undefined';

			trigger_error( $mess );
			throw new \Advertikon\Exception( $mess );
		}

		if ( ! $oc_subscription ) {
			$mess = 'Failed to send query to callback URL: OC subscription is undefined';

			trigger_error( $mess );
			throw new \Advertikon\Exception( $mess );
		}

		if ( ! $status ) {
			$mess = 'Failed to send query to callback URL: subscription status is undefined';

			trigger_error( $mess );
			throw new \Advertikon\Exception( $mess );
		}

		foreach( explode( ',', $data ) as $d1 ) {
			list( $name, $val ) = explode( '=', $d1 );

			if( ! ( $name = trim( $name ) ) ) {
				continue;
			}

			$post[ urlencode( $name ) ] = urlencode( trim( $val ) );
		}

		$post['oc_customer']         = urlencode( $oc_customer );
		$post['stripe_customer']     = urlencode( $stripe_customer );
		$post['oc_subscription']     = urlencode( $oc_subscription );
		$post['stripe_subscription'] = urlencode( $stripe_subscription );
		$post['status']              = urlencode( $status );

		$fd = fopen( 'php://temp', 'w' );
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_HEADER, 0 );
		curl_setopt( $ch, CURLOPT_POST, 1 );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $post );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_VERBOSE, 1 );
		curl_setopt( $ch, CURLOPT_STDERR, $fd );
		curl_exec( $ch );

		if( curl_errno( $ch ) ) {
			$mess = 'CURL error: ' . curl_error( $ch );
			trigger_error( $mess );

			rewind( $fd );
			$this->a->log( fread( $fd, 4096 ), $this->a->log_debug_flag );
		}

		curl_close( $ch );
		fclose( $fd );
	}
}
?>
	/**
	 * Update subscription status on deletion
	 * @return void
	 */
	public function subscription_deleted( $evt ) {
		if ( empty( $evt->data->object->id ) ) {
			throw new Exception( 'Subscription\'s ID is missing' );
		}

		$subscription = $evt->data->object;

		if ( empty( $subscription->metadata->recurring_order_id ) ) {
			throw new \Aadvertikon\Exception( sprintf( 'Metadata of subscription %s doesn\'t contain recurring order id' ) );
		}

		$account = isset( $subscription->plan->metadata->account ) ?
			$subscription->plan->metadata->account : null;

		$this->a->set_account( $account );

		$this->a->update_subscription( $subscription->metadata->recurring_order_id );

		$this->a->log(
			sprintf(
				'Status of subscription %s was changed (account %s)',
				$subscription->id,
				$this->a->get_account()
			),
			$this->a->log_debug_flag
		);
	}

	/**
	 * Load JS action
	 * @return void
	 */
	public function compress() {
		echo $this->a->compress();die;
	}

	/**
	 * Sends callback on subscription status change
	 * @param object $evt Stripe event object
	 * @return void
	 */
	public function callback( $evt ) {
		$url = '';
		$post = array();
		$data = '';
		$status = '';

		if ( 'customer.subscription.created' === $evt->type ) {
			$url = $this->a->config( 'create_subscription_callback' );
			$data = $this->a->config( 'create_subscription_callback_data' );
			$status = 'new';

		} elseif ( 'customer.subscription.deleted' === $evt->type ) {
			$url = $this->a->config( 'cancel_subscription_callback' );
			$data = $this->a->config( 'cancel_subscription_callback_data' );
			$status = 'cancel';
		}

		if ( ! $url ) {
			return;
		}

		$recurring = new \Advertikon\Stripe\Resource\Recurring( $evt->data->object->id, 'subscription_id' );

		if ( ! $recurring->is_exists() ) {
			$mess = sprintf(
				'Failed to send query to callback URL: stripe subscription %s is not registered in OC store',
				$evt->data->object->id
			);

			trigger_error( $mess );
			throw new \Advertikon\Exception( $mess );
		}

		$oc_customer         = $recurring->order['customer_id'];
		$stripe_customer     = $evt->data->object->customer;
		$oc_subscription     = $recurring->recurring_order_id;
		$stripe_subscription = $evt->data->object->id;

		if ( ! $oc_customer ) {
			$mess = 'Failed to send query to callback URL: OC customer is undefined';

			trigger_error( $mess );
			throw new \Advertikon\Exception( $mess );
		}

		if ( ! $oc_subscription ) {
			$mess = 'Failed to send query to callback URL: OC subscription is undefined';

			trigger_error( $mess );
			throw new \Advertikon\Exception( $mess );
		}

		if ( ! $status ) {
			$mess = 'Failed to send query to callback URL: subscription status is undefined';

			trigger_error( $mess );
			throw new \Advertikon\Exception( $mess );
		}

		foreach( explode( ',', $data ) as $d1 ) {
			list( $name, $val ) = explode( '=', $d1 );

			if( ! ( $name = trim( $name ) ) ) {
				continue;
			}

			$post[ urlencode( $name ) ] = urlencode( trim( $val ) );
		}

		$post['oc_customer']         = urlencode( $oc_customer );
		$post['stripe_customer']     = urlencode( $stripe_customer );
		$post['oc_s