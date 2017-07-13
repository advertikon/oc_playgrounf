<?php
/**
 * Admin Advertikon Stripe Controller
 *
 * @author Advertikon
 * @package Stripe
 * @version 2.8.11   
 * 
 * @source admin/view/javascript/advertikon/jquery-ui.min.js
 * @source admin/view/javascript/advertikon/iris.min.js
 * @source admin/view/javascript/advertikon/select2/*
 * @source admin/view/javascript/advertikon/summernote/*
 * @source admin/view/javascript/advertikon/advertikon.js
 * @source admin/view/javascript/advertikon/stripe/*
 * 
 * @source admin/view/stylesheet/advertikon/fa/*
 * @source admin/view/stylesheet/advertikon/jquery-ui.min.css
 * @source admin/view/stylesheet/advertikon/jquery-ui.theme.min.css
 * @source admin/view/stylesheet/advertikon/images/* jQuery-IU images
 * @source admin/view/stylesheet/advertikon/select2/*
 * @source admin/view/stylesheet/advertikon/summernote/*
 * @source admin/view/stylesheet/advertikon/stripe/*
 * @source admin/view/stylesheet/advertikon/advertikon.css
 * 
 * @source image/advertikon/stripe/*
 * @source admin/view/image/payment/advertikon_stripe.gif
 * 
 * @source admin/view/template/extension/payment/advertikon/stripe/*
 * @source system/library/advertikon/advertikon.php
 * @source system/library/advertikon/compressor/*
 * @source system/library/advertikon/exception/*
 * @source system/library/advertikon/array_iterator.php
 * @source system/library/advertikon/cache.php
 * @source system/library/advertikon/db_result.php
 * @source system/library/advertikon/exception.php
 * @source system/library/advertikon/fs.php
 * @source system/library/advertikon/log_debug.php
 * @source system/library/advertikon/log_error.php
 * @source system/library/advertikon/minify.php
 * @source system/library/advertikon/option.php
 * @source system/library/advertikon/query.php
 * @source system/library/advertikon/renderer.php
 * @source system/library/advertikon/resource.php
 * @source system/library/advertikon/resource_wrapper.php
 * @source system/library/advertikon/shortcode.php
 * @source system/library/advertikon/socket.php
 * @source system/library/advertikon/task.php
 * @source system/library/advertikon/terminalColors.php
 * @source system/library/advertikon/url.php
 * @source system/library/advertikon/console.php
 * @source system/library/advertikon/stripe/*
 */

class ControllerExtensionPaymentAdvertikonStripe extends Controller {

	protected $_minAmountAdjastment = '';

	public $a = null;
	public $model = null;

	public function __construct( $registry ) {

		parent::__construct( $registry );

		$this->a = Advertikon\Stripe\Advertikon::instance();
		$this->load->model( $this->a->full_name );
		$this->model = $this->{'model_' . str_replace( '/', '_', $this->a->full_name )};
	}

	/**
	 * indexAction
	 * @return void
	 */
	public function index() {
		$this->document->addScript( $this->a->u()->admin_url() . 'view/javascript/advertikon/jquery-ui.min.js' );
		$this->document->addScript( $this->a->u()->admin_url() . 'view/javascript/advertikon/iris.min.js' );
		$this->document->addScript( $this->a->u()->admin_url() . 'view/javascript/advertikon/select2/select2.min.js' );
		$this->document->addScript( $this->a->u()->admin_url() . 'view/javascript/advertikon/summernote/summernote.min.js' );
		$this->document->addScript( $this->a->u()->admin_url() . 'view/javascript/advertikon/advertikon.js' );
		$this->document->addScript( $this->a->u()->admin_url() . 'view/javascript/advertikon/stripe/adk_stripe.js' );

		// $this->a->add_script( array(
		// 	'advertikon/jquery-ui.min.js',
		// 	'advertikon/iris.min.js',
		// 	'advertikon/select2/select2.min.js',
		// 	'advertikon/summernote/summernote.min.js',
		// ), \Advertikon\Advertikon::COMPRESSION_LEVEL_COMBINE );

		// $this->a->add_script( array(
		// 	'advertikon/advertikon.js',
		// 	'advertikon/stripe/adk_stripe.js',
		// ) );

		$this->document->addStyle( $this->a->u()->admin_url() . 'view/stylesheet/advertikon/fa/css/font-awesome.min.css' );
		$this->document->addStyle( $this->a->u()->admin_url() . 'view/stylesheet/advertikon/jquery-ui.min.css' );
		$this->document->addStyle( $this->a->u()->admin_url() . 'view/stylesheet/advertikon/jquery-ui.theme.min.css' );
		$this->document->addStyle( $this->a->u()->admin_url() . 'view/stylesheet/advertikon/select2/select2.min.css' );
		$this->document->addStyle( $this->a->u()->admin_url() . 'view/stylesheet/advertikon/summernote/summernote.css' );
		$this->document->addStyle( $this->a->u()->admin_url() . 'view/stylesheet/advertikon/advertikon.css' );
		$this->document->addStyle( $this->a->u()->admin_url() . 'view/stylesheet/advertikon/stripe/adk_stripe.css' );

		// $this->a->add_style( array(
		// 	'stylesheet/advertikon/fa/css/font-awesome.min.css',
		// 	'stylesheet/advertikon/jquery-ui.min.css',
		// 	'stylesheet/advertikon/jquery-ui.theme.min.css',
		// 	'stylesheet/advertikon/select2/select2.min.css',
		// ), \Advertikon\Advertikon::COMPRESSION_LEVEL_COMBINE );

		// $this->a->add_style( array(
		// 	// 'stylesheet/advertikon/summernote/summernote.css',
		// 	'stylesheet/advertikon/advertikon.css',
		// 	'stylesheet/advertikon/stripe/adk_stripe.css',
		// ) );

		$this->document->setTitle( $this->a->__( 'Stripe Settings' ) );

		$extension_route = version_compare( VERSION, '2.3.0.0', '>' ) ?
			'extension/extension' : 'extension/' . $this->a->type;

		// Fix setting's code names
		if ( ! $this->config->get( 'db_fix' ) && $this->model->fix_db() ) {
			$this->a->q( array(
				'table'  => 'setting',
				'query'  => 'insert',
				'values' => array(
					'code'     => $this->a->code,
					'key'      => 'db_fix',
					'value'    => 1,
					'store_id' => 0,
				),
			) );

			$this->config->set( 'db_fixed', 1 );
		}

		global $adk_errors;

		$adk_errors = array(
			'warning'      => array(),
			'input_errors' => array(),
			'info'         => array(),
			'success'      => false,
		);

		if ( $this->request->server['REQUEST_METHOD'] == 'POST' ) {

			// Support feature
			if ( isset( $this->request->post[ $this->a->prefix_name( 'support_subject' ) ] ) ) {
				if ( $this->a->ask_support( $adk_errors ) ) {
					$this->session->data['success'] = $this->a->__(
						'Support request has been successfully sent'
					);
				}

			} else {

				// Successful validation
				if( $this->model->validate_configs() ) {
					$this->load->model( 'setting/setting' );

					$settings = $this->model_setting_setting->editSetting(
						'adk_stripe',
						array_merge(
							$this->model_setting_setting->getSetting( 'adk_stripe' ),
							$this->request->post
						)
					);

					$settings = $this->model_setting_setting->editSetting(
						'advertikon_stripe',
						array_merge(
							$this->model_setting_setting->getSetting( 'advertikon_stripe' ),
							$this->request->post
						)
					);

					$this->session->data['success'] = sprintf(
						'%s %s',
						$this->a->__( 'Settings has been successfully changed' ),
						$adk_errors['info'] ? implode( '. ', $adk_errors['info'] ) : ''
					);

					$this->response->redirect( $this->a->u()->url() );
				}
			}
		}

		$resp = $adk_errors;

		/**
		 * Passes error messages to the view
		 * Errors from ['input_errors'] goes to inputs' error labels
		 * ['warning'] array passes as ['error_warning']
		 * ['attention'] array passes as ['error_attention']
		 */
		foreach( $adk_errors as $key => $val ){
			$data['error_' . $key ] = $val;
		}

		if ( isset( $this->session->data['success'] ) ) {
			$data['success'] = $this->session->data['success'];
			unset( $this->session->data['success'] );
		}

		$option = new Advertikon\Stripe\Option();
		$shortcode = new Advertikon\Stripe\Shortcode();

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link(
				'common/home',
				'token=' . $this->session->data['token'],
				'SSL'
			),
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_payment'),
			'href' => $this->url->link(
				$extension_route,
				'token=' . $this->session->data['token'],
				'SSL'
			),
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link(
				$this->a->full_name, 
				'token=' . $this->session->data['token'],
				'SSL'
			),
		);

		$data['action'] = $this->url->link(
			$this->a->full_name,
			'token=' . $this->session->data['token'],
			'SSL'
		);
		$data['cancel'] = $this->url->link(
			$extension_route, 
			'token=' . $this->session->data['token'],
			'SSL'
		);

		$data['compatibility'] = $this->a->check_compatibility();

		$data['version'] = \Advertikon\Stripe\Advertikon::get_version();
		$data['header'] = $this->load->controller( 'common/header' );
		$data['footer'] = $this->load->controller( 'common/footer' );
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller( 'common/footer' );
		$data['a'] = $this->a;

		$data['check_tls'] = $this->a->r( array(
			'type'        => 'button',
			'text_before' => 'TLS 1.2',
			'button_type' => 'default',
			'title'       => $this->a->__( 'Check TLS 1.2 support' ),
			'icon'        => 'fa-lock',
			'id'          => 'check-tls',
		) );

		$data['add_account_btn'] = $this->a->r()->render_form_group( array(
			'label' => ' ',
				'element' => array(
					'type'        => 'button',
					'button_type' => 'primary',
					'class'       => 'pull-right',
					'id'          => 'add-account',
					'title'       => $this->a->__( 'Add Stripe account' ),
					'icon'        => 'fa-plus',
				),
			'cols'      => array( 'col-sm-10', 'col-sm-2', ),
			'label'     => '<a href="//dashboard.stripe.com/account/apikeys" target="_blank">' .
				$this->a->__( 'To obtain API keys visit Stripe Dashboard' ) . '</a>',
		) );

		$name = 'title';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Title' ),
			'label_for' => 'input-' . $name,
			'tooltip'   => $this->a->__( 'The title under which the extension appears on checkout' ),
			'element' => array(
				'type' => 'lang_set',
				'element'   => array(
					'type'        => 'text',
					'name'        => $name,
					'class'       => 'form-control',
					'value'       => 'Stripe',
					'id'          => 'input-' . $name,
					'placeholder' => $this->a->__( 'Title' ),
				),
			),
			'error'       => isset( $resp['input_errors'][ $name ] ) ?
				$resp['input_errors'][ $name ] : null,
		) );

		$name = 'sandbox_title';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Sandbox title' ),
			'label_for' => 'input-' . $name,
			'tooltip'   => $this->a->__( 'The title under which the extension appears on checkout when test mode is enabled' ),
			'element' => array(
				'type' => 'lang_set',
				'element'   => array(
					'type'        => 'text',
					'name'        => $name,
					'class'       => 'form-control',
					'value'       => 'Test mode',
					'id'          => 'input-' . $name,
					'placeholder' => $this->a->__( 'Sandbox title' ),
				),
			),
			'error'       => isset( $resp['input_errors'][ $name ] ) ?
				$resp['input_errors'][ $name ] : null,
		) );

		$name = 'payment_method';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Payment method' ),
			'label_for' => 'input-' . $name,
			'tooltip'   => $this->a->__( 'Authorize: payment need to be accepted manually over stripe account or admin panel, Capture: payments are accepted automatically when order is purchase, Authorize if fraud: if fraud detected by one of anti-fraud extensions payment will be authorized, otherwise - captured ' ),
			'element'   => array(
				'type'        => 'select',
				'name'        => $name,
				'class'       => 'form-control',
				'active'      => $this->a->get_value_from_post( $name ),
				'value'       => $option->payment_option(),
				'id'          => 'input-' . $name,
			),
			'error'       => isset( $resp['input_errors'][ $name ] ) ?
				$resp['input_errors'][ $name ] : null,
		) );

		$name = 'payment_currency';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Payment currency' ),
			'label_for' => 'input-' . $name,
			'tooltip'   => $this->a->__( '"Store" - order will be placed in a currency of Stripe account with a currency of which it coincides, in a case of mismatch - in a currency of the "Default" account.  "Order" - order will be placed in a currency of Stripe account with a currency of which it coincides, in a case of mismatch - an order\'s currency will be used' ),
			'element'   => array(
				'type'        => 'select',
				'name'        => $name,
				'class'       => 'form-control',
				'active'      => $this->a->get_value_from_post( $name ),
				'value'       => array(
									Advertikon\Stripe\Advertikon::CURRENCY_STORE => $this->a->__( 'Store' ),
									Advertikon\Stripe\Advertikon::CURRENCY_ORDER => $this->a->__( 'Order' ),
								),
				'id'          => 'input-' . $name,
			),
			'error'       => isset( $resp['input_errors'][ $name ] ) ?
				$resp['input_errors'][ $name ] : null,
		) );

		$name = 'charge_description';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Charge description' ),
			'label_for' => 'input-' . $name,
			'tooltip'   => $this->a->__( 'Customizable charge description. Supported variables are:' ) .
				implode( ', ', $option->shortcode() ),
			'element'   => array(
				'type'        => 'text',
				'name'        => $name,
				'class'       => 'form-control',
				'value'       => $this->a->get_value_from_post( $name ),
				'id'          => 'input-' . $name,
				'placeholder' => $this->a->__( 'Charge description' ),
			),
			'error'       => isset( $resp['input_errors'][ $name ] ) ?
				$resp['input_errors'][ $name ] : null,
		) );

		$name = 'customer_description';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Customer description' ),
			'label_for' => 'input-' . $name,
			'tooltip'   => $this->a->__( 'Customizable description of Stripe Dashboard\'s customer. Supported variables are:' ) .
				implode( ', ', $option->shortcode() ),
			'element'   => array(
				'type'        => 'text',
				'name'        => $name,
				'class'       => 'form-control',
				'value'       => $this->a->get_value_from_post( $name ),
				'id'          => 'input-' . $name,
				'placeholder' => $this->a->__( 'Customer description' ),
			),
			'error'       => isset( $resp['input_errors'][ $name ] ) ?
				$resp['input_errors'][ $name ] : null,
		) );

		$name = 'statement_descriptor';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Statement descriptor' ),
			'label_for' => 'input-' . $name,
			'tooltip'   => $this->a->__( 'An arbitrary string to be displayed on your customer\'s credit card statement' ),
			'element'   => array(
				'type'        => 'text',
				'name'        => $name,
				'class'       => 'form-control',
				'value'       => $this->a->get_value_from_post( $name ),
				'id'          => 'input-' . $name,
				'placeholder' => $this->a->__( 'Statement descriptor' ),
			),
			'error'       => isset( $resp['input_errors'][ $name ] ) ?
				$resp['input_errors'][ $name ] : null,
		) );

		$name = 'total_min';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Min total amount' ),
			'label_for' => 'input-' . $name,
			'tooltip'   => $this->a->__( 'Minimum order amount for the payment gateway' ),
			'element'   => $this->a->r()->render_input_group( array(
				'element'      =>  array(
					'type'        => 'number',
					'name'        => $name,
					'class'       => 'form-control',
					'value'       => $this->a->get_value_from_post( $name ),
					'id'          => 'input-' . $name,
					'placeholder' => $this->a->__( 'Min total amount' ),
				),
				'error'       => isset( $resp['input_errors'][ $name ] ) ?
					$resp['input_errors'][ $name ] : null,
				'addon_before' => $this->currency->getSymbolLeft(
					$this->config->get( 'config_currency' )
				),
				'addon_after'  => $this->currency->getSymbolRight(
					$this->config->get( 'config_currency' )
				),
			) ),
		) );

		$name = 'total_max';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Max total amount' ),
			'label_for' => 'input-' . $name,
			'tooltip'   => $this->a->__( 'Maximum order amount for the payment gateway' ),
			'element'   => $this->a->r()->render_input_group( array(
				'element'      =>  array(
					'type'        => 'number',
					'name'        => $name,
					'class'       => 'form-control',
					'value'       => $this->a->get_value_from_post( $name ),
					'id'          => 'input-' . $name,
					'placeholder' => $this->a->__( 'Max total amount' ),
				),
				'error'       => isset( $resp['input_errors'][ $name ] ) ?
					$resp['input_errors'][ $name ] : null,
				'addon_before' => $this->currency->getSymbolLeft(
					$this->config->get( 'config_currency' )
				),
				'addon_after'  => $this->currency->getSymbolRight(
					$this->config->get( 'config_currency' )
				),
			) ),
		) );

		$name = 'geo_zone';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Geo Zones' ),
			'label_for' => 'input-' . $name,
			'tooltip'   => $this->a->__( 'Permitted Geo Zones' ),
			'element'   => array(
				'type'        => 'select',
				'name'        => $name,
				'class'       => 'form-control select2',
				'active'      => $this->a->get_value_from_post( $name ),
				'value'       => array_merge( array( '0' => $this->a->__( 'All Geo Zones') ), $option->geo_zone() ),
				'id'          => 'input-' . $name,
				'multiple'    => true,
			),
			'error'       => isset( $resp['input_errors'][ $name ] ) ?
				$resp['input_errors'][ $name ] : null,
		) );

		$id = 'avail_systems';
		$name = $this->a->build_name( $id, '-' );
		$data[ $id ] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Payment systems' ),
			'label_for' => $id,
			'tooltip'   => $this->a->__( 'Permitted payment systems' ),
			'element'   => array(
				'type'        => 'select',
				'name'        => $name,
				'class'       => 'form-control select2',
				'active'      => $this->a->get_value_from_post( $name ),
				'value'       => array_merge( array( '0' => $this->a->__( 'All systems' ) ), $option->payment_system() ),
				'id'          => $id,
				'multiple'    => true,
			),
			'error'       => isset( $resp['input_errors'][ $name ] ) ?
				$resp['input_errors'][ $name ] : null,
		) );

		$name = 'stores';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Stores' ),
			'label_for' => 'input-' . $name,
			'tooltip'   => $this->a->__( 'Permitted stores' ),
			'element'   => array(
				'type'        => 'select',
				'name'        => $name,
				'class'       => 'form-control select2',
				'active'      => $this->a->get_value_from_post( $name ),
				'value'       => array_merge( array( '0' => $this->a->__( 'All stores' ) ), $option->store() ),
				'id'          => 'input-' . $name,
				'multiple'    => true,
			),
			'error'       => isset( $resp['input_errors'][ $name ] ) ?
				$resp['input_errors'][ $name ] : null,
		) );

		$name = 'customer_groups';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Customer groups' ),
			'label_for' => 'input-' . $name,
			'tootlip'   => $this->a->__( 'Permitted customer groups' ),
			'element'   => array(
				'type'        => 'select',
				'name'        => $name,
				'class'       => 'form-control select2',
				'active'      => $this->a->get_value_from_post( $name ),
				'value'       => array_merge( array( '0' => $this->a->__( 'All groups' ) ), $option->customer_group() ),
				'id'          => 'input-' . $name,
				'multiple'    => true,
			),
			'error'       => isset( $resp['input_errors'][ $name ] ) ?
				$resp['input_errors'][ $name ] : null,
		) );

		$name = 'sort_order';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Sort Order' ),
			'label_for' => 'input-' . $name,
			'element'   => $this->a->r( array(
				'type'        => 'number',
				'name'        => $name,
				'class'       => 'form-control',
				'value'       => $this->a->get_value_from_post( $name ),
				'id'          => 'input-' . $name,
				'placeholder' => $this->a->__( 'Sort Order' ),
			) ),
			'error'       => isset( $resp['input_errors'][ $name ] ) ?
				$resp['input_errors'][ $name ] : null,
		) );

		$name = 'receipt_email';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Receipt Email' ),
			'tooltip' => $this->a->__( 'Whether to send charge receipt at customer\'s email address. Receipts will not be sent for test mode charges. In live mode if set to "Yes" will override Stripe Dashboard\'s setting, if set to "No" Stripe Dashboard\'s setting will be applied' ),
			'element' => $this->a->r()->render_fancy_checkbox( array(
				'name'     => $name,
				'id'       => $name,
				'value'    => $this->a->get_value_from_post( $name ),
				'text_on'  => $this->a->__( 'Yes' ),
				'text_off' => $this->a->__( 'No' ),
			) ),
		) );

		$name = 'notify_customer';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Notify customer' ),
			'tooltip' => $this->a->__( 'Whether to notify customer on payment capture or refund which made from Stripe Dashboard' ),
			'element' => $this->a->r()->render_fancy_checkbox( array(
				'name'          => $name,
				'id'            => $name,
				'value'         => $this->a->get_value_from_post( $name ),
				'text_on'       => $this->a->__( 'Yes' ),
				'text_off'      => $this->a->__( 'No' ),
				'dependent_off' => '#override',
			) ),
		) );

		$name = 'override';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Override' ),
			'tooltip' => $this->a->__( 'Whether to override order status, blocked by anti-fraud extensions, on payment capture or refund made from Stripe Dashboard' ),
			'element' => $this->a->r()->render_fancy_checkbox( array(
				'name'         => $name,
				'id'           => $name,
				'value'        => $this->a->get_value_from_post( $name ),
				'text_on'      => $this->a->__( 'Yes' ),
				'text_off'     => $this->a->__( 'No' ),
				'dependent_on' => '#notify_customer',
			) ),
		) );

		$name = 'hide_button';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Hide button' ),
			'tooltip' => $this->a->__( 'If you use a quick (one page) checkout extension and the payment button was not hidden by means of this extension you can hide the payment button forcibly' ),
			'element' => $this->a->r()->render_fancy_checkbox( array(
				'name'     => $name,
				'id'       => $name,
				'value'    => $this->a->get_value_from_post( $name ),
				'text_on'  => $this->a->__( 'Yes' ),
				'text_off' => $this->a->__( 'No' ),
			) ),
		) );

		$name = 'button_class';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Button\'s CSS class' ),
			'label_for' => 'input-' . $name,
			'tooltip'   => $this->a->__( 'If you use a quick (one page) checkout extension and it uses a non-standard selectors to trigger payment button you can add this selectors - comma separated list of class names - to the payment button' ),
			'element'   => array(
				'type'        => 'text',
				'name'        => $name,
				'class'       => 'form-control',
				'value'       => $this->a->get_value_from_post( $name ),
				'id'          => 'input-' . $name,
				'placeholder' => $this->a->__( 'Button\'s CSS class' ),
			),
			'error'       => isset( $resp['input_errors'][ $name ] ) ?
				$resp['input_errors'][ $name ] : null,
		) );

		$name = 'status';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Extension\'s status' ),
			'element' => $this->a->r()->render_fancy_checkbox( array(
				'name'     => $name,
				'id'       => $name,
				'value'    => $this->a->get_value_from_post( $name ),
			) ),
		) );

		$name = 'status_authorized';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Authorized Payment Status' ),
			'label_for' => 'input-' . $name,
			'tooltip'   => $this->a->__( 'Order\'s status when payment was just authorized (customer card\'s has not been charged)' ),
			'element'   => array(
				'type'        => 'select',
				'name'        => $name,
				'class'       => 'form-control',
				'active'      => $this->a->get_value_from_post( $name ),
				'value'       => $option->order_status(),
				'id'          => 'input-' . $name,
			),
			'error'       => isset( $resp['input_errors'][ $name ] ) ?
				$resp['input_errors'][ $name ] : null,
		) );

		$name = 'status_captured';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Captured Payment Status' ),
			'label_for' => 'input-' . $name,
			'tooltip'   => $this->a->__( 'Which status to assign to an order upon payment capture' ),
			'element'   => array(
				'type'        => 'select',
				'name'        => $name,
				'class'       => 'form-control',
				'active'      => $this->a->get_value_from_post( $name ),
				'value'       => $option->order_status(),
				'id'          => 'input-' . $name,
			),
			'error'       => isset( $resp['input_errors'][ $name ] ) ?
				$resp['input_errors'][ $name ] : null,
		) );

		$name = 'status_voided';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Refunded Payment Status' ),
			'label_for' => 'input-' . $name,
			'tooltip'   => $this->a->__( 'Which status to assign to an order upon payment\'s full refund' ),
			'element'   => array(
				'type'        => 'select',
				'name'        => $name,
				'class'       => 'form-control',
				'active'      => $this->a->get_value_from_post( $name ),
				'value'       => $option->order_status(),
				'id'          => 'input-' . $name,
			),
			'error'       => isset( $resp['input_errors'][ $name ] ) ?
				$resp['input_errors'][ $name ] : null,
		) );

		$name = 'show_systems';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Show permitted payment systems' ),
			'tooltip' => $this->a->__( 'Defines whether to show icons of permitted payment systems, along with payment method name on checkout' ),
			'element' => $this->a->r()->render_fancy_checkbox( array(
				'name'     => $name,
				'id'       => $name,
				'value'    => $this->a->get_value_from_post( $name ),
				'text_on'  => $this->a->__( 'Yes' ),
				'text_off' => $this->a->__( 'No' ),
			) ),
		) );

		$name = 'debug';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Logging verbosity' ),
			'label_for' => 'input-' . $name,
			'element'   => array(
				'type'        => 'select',
				'name'        => $name,
				'class'       => 'form-control',
				'active'      => $this->a->get_value_from_post( $name ),
				'value'       => $option->log_verbosity(),
				'id'          => 'input-' . $name,
			),
			'error'       => isset( $resp['input_errors'][ $name ] ) ?
				$resp['input_errors'][ $name ] : null,
		) );

		$name = 'test_mode';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Test mode' ),
			'tooltip' => $this->a->__( 'Defines whether the extension is in the test mode. You also need to change corresponding setting of Stripe Dashboard' ),
			'element' => $this->a->r()->render_fancy_checkbox( array(
				'name'     => $name,
				'id'       => $name,
				'value'    => $this->a->get_value_from_post( $name ),
			) ),
		) );

		$name = 'uninstall_clear_settings';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Clear settings' ),
			'tooltip' => $this->a->__( 'Whether to clear the extension\'s settings during uninstallation' ),
			'element' => $this->a->r()->render_fancy_checkbox( array(
				'name'     => $name,
				'id'       => $name,
				'value'    => $this->a->get_value_from_post( $name ),
				'text_on'  => $this->a->__( 'Yes' ),
				'text_off' => $this->a->__( 'No' ),
			) ),
		) );

		$name = 'uninstall_clear_db';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Clear DB' ),
			'tooltip' => $this->a->__( 'Whether to remove the extension\'s tables from database during uninstallation' ),
			'element' => $this->a->r()->render_fancy_checkbox( array(
				'name'     => $name,
				'id'       => $name,
				'value'    => $this->a->get_value_from_post( $name ),
				'text_on'  => $this->a->__( 'Yes' ),
				'text_off' => $this->a->__( 'No' ),
			) ),
		) );

		$name = 'error_order_notification';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Notification on Order Error' ),
			'tooltip' => $this->a->__( 'Whether to sent email notification to store administrator on payment operation error' ),
			'element' => $this->a->r()->render_fancy_checkbox( array(
				'name'     => $name,
				'id'       => $name,
				'value'    => $this->a->get_value_from_post( $name ),
				'text_on'  => $this->a->__( 'Yes' ),
				'text_off' => $this->a->__( 'No' ),
			) ),
		) );

		$name = 'cvc_check';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'CVC Verification' ),
			'tooltip' => $this->a->__( 'Whether to perform CVC/CVV verification' ),
			'element' => $this->a->r()->render_fancy_checkbox( array(
				'name'     => $name,
				'id'       => $name,
				'value'    => $this->a->get_value_from_post( $name ),
				'text_on'  => $this->a->__( 'Yes' ),
				'text_off' => $this->a->__( 'No' ),
			) ),
		) );

		$name = 'zip_check';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'ZIP-code Verification' ),
			'tooltip' => $this->a->__( 'Whether to perform address ZIP-code verification' ),
			'element' => $this->a->r()->render_fancy_checkbox( array(
				'name'     => $name,
				'id'       => $name,
				'value'    => $this->a->get_value_from_post( $name ),
				'text_on'  => $this->a->__( 'Yes' ),
				'text_off' => $this->a->__( 'No' ),
			) ),
		) );

		$name = 'address_check';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Address line 1 Verification' ),
			'tooltip' => $this->a->__( 'Whether to perform address line 1 verification' ),
			'element' => $this->a->r()->render_fancy_checkbox( array(
				'name'     => $name,
				'id'       => $name,
				'value'    => $this->a->get_value_from_post( $name ),
				'text_on'  => $this->a->__( 'Yes' ),
				'text_off' => $this->a->__( 'No' ),
			) ),
		) );

		$name = 'show_card_image';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Show card image' ),
			'tooltip' => $this->a->__( 'If enabled then image of payment card will be shown on checkout page to help fill in corresponding fields' ),
			'element' => $this->a->r()->render_fancy_checkbox( array(
				'name'     => $name,
				'id'       => $name,
				'value'    => $this->a->get_value_from_post( $name ),
				'text_on'  => $this->a->__( 'Yes' ),
				'text_off' => $this->a->__( 'No' ),
			) ),
		) );

		$name = 'form_width';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Form width' ),
			'tooltip' => $this->a->__( 'Restrict payment form width to some maximum value in pixels' ),
			'element' => $this->a->r()->render_dimension( array(
				'values' => 'px',
				'titles' => $this->a->__( 'Width in px' ),
				'maxes'  => '1000',
				'name'   => $name,
				'id'     => $name,
				'value'  => $this->a->get_value_from_post( $name, array( 'value' => 500 ) ),
			) ),
		) );

		$name = 'form_caption';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Form\'s caption' ),
			'label_for' => 'input-' . $name,
			'tooltip'   => $this->a->__( 'Payment form\'s caption' ),
			'element' => array(
				'type' => 'lang_set',
				'element'   => array(
					'type'        => 'text',
					'name'        => $name,
					'class'       => 'form-control',
					'value'       => '',
					'id'          => 'input-' . $name,
					'placeholder' => $this->a->__( 'Form\'s caption' ),
				),
			),
			'error'       => isset( $resp['input_errors'][ $name ] ) ?
				$resp['input_errors'][ $name ] : null,
		) );

		$name = 'vendor_image_form';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Cards vendors' ),
			'tooltip' => $this->a->__( 'Defines whether to show images of supported card vendors on payment form header' ),
			'element' => $this->a->r()->render_fancy_checkbox( array(
				'name'     => $name,
				'id'       => $name,
				'value'    => $this->a->get_value_from_post( $name ),
				'text_on'  => $this->a->__( 'Yes' ),
				'text_off' => $this->a->__( 'No' ),
			) ),
		) );

		$name = 'vendor_image_form_width';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Maximum image width' ),
			'tooltip' => $this->a->__( 'Restrict vendor image width to some value in pixels' ),
			'element' => $this->a->r()->render_dimension( array(
				'values' => 'px',
				'titles' => $this->a->__( 'Width in px' ),
				'maxes'  => '600',
				'name'   => $name,
				'id'     => $name,
				'value'  => $this->a->get_value_from_post( $name, array( 'value' => 200 ) ),
			) ),
		) );

		$name = 'card_name';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Cardholder\'s name' ),
			'tooltip' => $this->a->__( 'Whether to display a cardholder\'s name field at payment form' ),
			'element' => $this->a->r()->render_fancy_checkbox( array(
				'name'     => $name,
				'id'       => $name,
				'value'    => $this->a->get_value_from_post( $name ),
				'text_on'  => $this->a->__( 'Yes' ),
				'text_off' => $this->a->__( 'No' ),
			) ),
		) );

		$name = 'remember_me';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Save card' ),
			'tooltip' => $this->a->__( 'Allows customer to decide whether to save payment card in Stripe Dashboard for further use and select saved card to pay with' ),
			'element' => $this->a->r()->render_fancy_checkbox( array(
				'name'          => $name,
				'id'            => $name,
				'value'         => $this->a->get_value_from_post( $name ),
				'text_on'       => $this->a->__( 'Yes' ),
				'text_off'      => $this->a->__( 'No' ),
				'dependent_off' => '#saved_card_secret,#log_activity'
			) ),
		) );

		$name = 'saved_card_secret';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Saved card password' ),
			'tooltip' => $this->a->__( 'Customer need to supply an additional password for saved card in order to use card in the future' ),
			'element' => $this->a->r()->render_fancy_checkbox( array(
				'name'         => $name,
				'id'           => $name,
				'value'        => $this->a->get_value_from_post( $name ),
				'text_on'      => $this->a->__( 'Yes' ),
				'text_off'     => $this->a->__( 'No' ),
				'dependent_on' => '#remember_me',
			) ),
		) );

		$name = 'edit_cards';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Allow customers to edit cards' ),
			'tooltip' => $this->a->__( 'Allow customers delete saved cards and select default card via its account' ),
			'element' => $this->a->r()->render_fancy_checkbox( array(
				'name'     => $name,
				'id'       => $name,
				'value'    => $this->a->get_value_from_post( $name ),
				'text_on'  => $this->a->__( 'Yes' ),
				'text_off' => $this->a->__( 'No' ),
			) ),
		) );

		$name = 'log_activity';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Log customer\'s activity' ),
			'tooltip' => $this->a->__( 'Record client activity related to the card management, into "Customer Activity Report"' ),
			'element' => $this->a->r()->render_fancy_checkbox( array(
				'name'     => $name,
				'id'       => $name,
				'value'    => $this->a->get_value_from_post( $name ),
				'text_on'  => $this->a->__( 'Yes' ),
				'text_off' => $this->a->__( 'No' ),
			) ),
		) );

		$name = 'check_customer_duplication';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Prevent customer duplication' ),
			'tooltip' => $this->a->__( 'Whether to perform a customer duplication check on new customer creation at Stripe Dashboard. If this option is enabled, then the extension, before save new customer will check whether customer with the same email and the same payment card already exists at Stripe Dashboard. If so - new customer won\'t be saved  - existing account will be used instead. Use this option only if customer duplication is very unwanted to you. To minify customers duplication set option "Clear DB" to "No" to prevent customers\' data removing from OpenCart on the extension uninstallation' ),
			'element' => $this->a->r()->render_fancy_checkbox( array(
				'name'     => $name,
				'id'       => $name,
				'value'    => $this->a->get_value_from_post( $name ),
				'text_on'  => $this->a->__( 'Yes' ),
				'text_off' => $this->a->__( 'No' ),
			) ),
		) );

		$name = 'checkout';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Status' ),
			'tooltip' => $this->a->__( 'If enabled the Stripe pop-up checkout form will be used rather than the embed checkout form' ),
			'element' => $this->a->r()->render_fancy_checkbox( array(
				'name'     => $name,
				'id'       => $name,
				'value'    => $this->a->get_value_from_post( $name ),
			) ),
		) );

		$name = 'checkout_button_caption';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Checkout button\'s caption' ),
			'label_for' => 'input-' . $name,
			'tooltip'   => $this->a->__( 'The label of the payment button of the Checkout form (e.g. Subscribe, Pay {{amount}}, etc.). If you include {{amount}} in the label text, it will be replaced by a localized version of the amount. Otherwise, a localized amount will be appended to the end of the label' ),
			'element'   => array(
				'type'        => 'text',
				'name'        => $name,
				'class'       => 'form-control',
				'value'       => $this->a->get_value_from_post( $name ),
				'id'          => 'input-' . $name,
				'placeholder' => $this->a->__( 'Checkout button\'s caption' ),
			),
			'error'       => isset( $resp['input_errors'][ $name ] ) ?
				$resp['input_errors'][ $name ] : null,
		) );

		$name = 'checkout_zip_code';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Zip-code validation' ),
			'tooltip' => $this->a->__( 'Defines whether to perform ZIP-code validation' ),
			'element' => $this->a->r()->render_fancy_checkbox( array(
				'name'     => $name,
				'id'       => $name,
				'value'    => $this->a->get_value_from_post( $name ),
				'text_on'  => $this->a->__( 'Yes' ),
				'text_off' => $this->a->__( 'No' ),
			) ),
		) );

		$name = 'checkout_collect_payment';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Collect payment address' ),
			'tooltip' => $this->a->__( 'Defines whether to collect payment address details' ),
			'element' => $this->a->r()->render_fancy_checkbox( array(
				'name'     => $name,
				'id'       => $name,
				'value'    => $this->a->get_value_from_post( $name ),
				'text_on'  => $this->a->__( 'Yes' ),
				'text_off' => $this->a->__( 'No' ),
			) ),
		) );

		$name = 'checkout_collect_shipping';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Collect shipping address' ),
			'tooltip' => $this->a->__( 'Defines whether to collect shipping address details' ),
			'element' => $this->a->r()->render_fancy_checkbox( array(
				'name'     => $name,
				'id'       => $name,
				'value'    => $this->a->get_value_from_post( $name ),
				'text_on'  => $this->a->__( 'Yes' ),
				'text_off' => $this->a->__( 'No' ),
			) ),
		) );

		$name = 'checkout_remember_me';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Allow remember Me' ),
			'tooltip' => $this->a->__( 'Specify whether to include the option to "Remember Me" for future purchases' ),
			'element' => $this->a->r()->render_fancy_checkbox( array(
				'name'     => $name,
				'id'       => $name,
				'value'    => $this->a->get_value_from_post( $name ),
				'text_on'  => $this->a->__( 'Yes' ),
				'text_off' => $this->a->__( 'No' ),
			) ),
		) );

		$name = 'checkout_bitcoin';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Bitcoin support' ),
			'tooltip' => $this->a->__( 'Defines whether to enable Bitcoin payments' ),
			'element' => $this->a->r()->render_fancy_checkbox( array(
				'name'     => $name,
				'id'       => $name,
				'value'    => $this->a->get_value_from_post( $name ),
				'text_on'  => $this->a->__( 'Yes' ),
				'text_off' => $this->a->__( 'No' ),
			) ),
		) );

		$name = 'checkout_alipay';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Alipay support' ),
			'tooltip' => $this->a->__( 'Defines whether to enable Alipay payments' ),
			'element' => $this->a->r()->render_fancy_checkbox( array(
				'name'          => $name,
				'id'            => $name,
				'value'         => $this->a->get_value_from_post( $name ),
				'text_on'       => $this->a->__( 'Yes' ),
				'text_off'      => $this->a->__( 'No' ),
				'dependent_off' => '#checkout_alipay_reusable',
			) ),
		) );

		$name = 'checkout_alipay_reusable';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Alipay reusable' ),
			'tooltip' => $this->a->__( 'Specifies if you need reusable access to the customer\'s Alipay account' ),
			'element' => $this->a->r()->render_fancy_checkbox( array(
				'name'         => $name,
				'id'           => $name,
				'value'        => $this->a->get_value_from_post( $name ),
				'text_on'      => $this->a->__( 'Yes' ),
				'text_off'     => $this->a->__( 'No' ),
				'dependent_on' => '#checkout_alipay',
			) ),
		) );

		$name = 'checkout_compatibility';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Quick checkout compatibility' ),
			'tooltip' => $this->a->__( 'Some browsers, especially on mobile devices, may block pop-up payment form. To prevent it you need to enable compatibility mode' ),
			'element' => $this->a->r()->render_fancy_checkbox( array(
				'name'          => $name,
				'id'            => $name,
				'value'         => $this->a->get_value_from_post( $name ),
				'text_on'       => $this->a->__( 'Yes' ),
				'text_off'      => $this->a->__( 'No' ),
				'dependent_off' => '#mobile_compatibility',
			) ),
		) );

		$name = 'mobile_compatibility';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Mobile devices only' ),
			'tooltip' => $this->a->__( 'Apply the quick checkout compatibility mode only to mobile devices' ),
			'element' => $this->a->r()->render_fancy_checkbox( array(
				'name'         => $name,
				'id'           => $name,
				'value'        => $this->a->get_value_from_post( $name ),
				'text_on'      => $this->a->__( 'Yes' ),
				'text_off'     => $this->a->__( 'No' ),
				'dependent_on' => '#checkout_compatibility',
			) ),
		) );

		$name = 'popup_image';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Image' ),
			'label_for' => 'input-' . $name,
			'tooltip'   => $this->a->__( 'Select whether to show store logo or product image at pop-up form header (only if order consists of one product)' ),
			'element'   => array(
				'type'        => 'select',
				'name'        => $name,
				'class'       => 'form-control',
				'active'      => $this->a->get_value_from_post( $name ),
				'value'       => array(
					'store'     => $this->a->__( 'Store' ),
					'product'   => $this->a->__( 'Product' ),
				),
				'id'          => 'input-' . $name,
			),
			'error'       => isset( $resp['input_errors'][ $name ] ) ?
				$resp['input_errors'][ $name ] : null,
		) );

		$name = 'compatibility_button_text';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Button\'s text' ),
			'label_for' => 'input-' . $name,
			'tooltip'   => $this->a->__( 'Compatibility mode implies a use of the pop-up button that user needs to explicitly click to trigger the pop-up window. You need to specify caption of that button' ),
			'element'   => array(
				'type'        => 'text',
				'name'        => $name,
				'class'       => 'form-control',
				'value'       => $this->a->get_value_from_post( $name ),
				'id'          => 'input-' . $name,
				'placeholder' => $this->a->__( 'Button\'s text' ),
			),
			'error'       => isset( $resp['input_errors'][ $name ] ) ?
				$resp['input_errors'][ $name ] : null,
		) );

		$name = 'button';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Status' ),
			'tooltip' => $this->a->__( 'Specifies whether to enable the "Pay in one click" button' ),
			'element' => $this->a->r()->render_fancy_checkbox( array(
				'name'     => $name,
				'id'       => $name,
				'value'    =>$this->a->get_value_from_post( $name ),
				'text_on'  => $this->a->__( 'Yes' ),
				'text_off' => $this->a->__( 'No' ),
			) ),
		) );

		$name = 'button_text';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Button\'s text' ),
			'label_for' => 'input-' . $name,
			'tooltip'   => $this->a->__( 'The label of the payment button. If you include {{amount}} in the label, it will be replaced by a localized version of amount. Otherwise, a localized amount will be appended to the end of your label' ),
			'element'   => array(
				'type'        => 'text',
				'name'        => $name,
				'class'       => 'form-control',
				'value'       => $this->a->get_value_from_post( $name ),
				'id'          => 'input-' . $name,
				'placeholder' => $this->a->__( 'Button\'s text' ),
			),
			'error'       => isset( $resp['input_errors'][ $name ] ) ?
				$resp['input_errors'][ $name ] : null,
		) );

		$name = 'button_name';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Payment method name' ),
			'label_for' => 'input-' . $name,
			'tooltip'   => $this->a->__( 'The description of payment method, to be shown in order\'s information' ),
			'element'   => array(
				'type'        => 'text',
				'name'        => $name,
				'class'       => 'form-control',
				'value'       => $this->a->get_value_from_post( $name ),
				'id'          => 'input-' . $name,
				'placeholder' => $this->a->__( 'Payment method name' ),
			),
			'error'       => isset( $resp['input_errors'][ $name ] ) ?
				$resp['input_errors'][ $name ] : null,
		) );

		$name = 'button_shipping';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Shipping method' ),
			'label_for' => 'input-' . $name,
			'tooltip'   => $this->a->__( 'Shipping method which to use for all orders created with "Pay in one click" button. If the product requires shipping - button will be shown only for logged customers and if this setting is specified' ),
			'element'   => array(
				'type'        => 'select',
				'name'        => $name,
				'class'       => 'form-control',
				'active'      => $this->a->get_value_from_post( $name ),
				'value'       => $option->shipping_methods(),
				'id'          => 'input-' . $name,
			),
			'error'       => isset( $resp['input_errors'][ $name ] ) ?
				$resp['input_errors'][ $name ] : null,
		) );

		$name = 'describe_price';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Describe price' ),
			'tooltip' => $this->a->__( 'Specifies whether to show detailed information which describes price structure (subtotal, tax, shipping cost)' ),
			'element' => $this->a->r()->render_fancy_checkbox( array(
				'name'     => $name,
				'id'       => $name,
				'value'    => $this->a->get_value_from_post( $name ),
				'text_on'  => $this->a->__( 'Yes' ),
				'text_off' => $this->a->__( 'No' ),
			) ),
		) );

		$name = 'button_text_height';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Text\'s height' ),
			'tooltip' => $this->a->__( 'Height of button caption' ),
			'element' => $this->a->r()->render_dimension( array(
				'values' => 'px',
				'titles' => $this->a->__( 'Height in px' ),
				'maxes'  => '50',
				'name'   => $name,
				'id'     => $name,
				'value'  => $this->a->get_value_from_post( $name, array( 'value' => 20 ) ),
			) ),
		) );

		$name = 'button_height';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Button\'s height' ),
			'element' => $this->a->r()->render_dimension( array(
				'values' => 'px',
				'titles' => $this->a->__( 'Height in px' ),
				'maxes'  => '50',
				'name'   => $name,
				'id'     => $name,
				'value'  => $this->a->get_value_from_post( $name, array( 'value' => 40 ) ),
			) ),
		) );

		$name = 'button_radius';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Button\'s border radius' ),
			'element' => $this->a->r()->render_dimension( array(
				'values' => 'px',
				'titles' => $this->a->__( 'Radius in px' ),
				'maxes'  => '50',
				'name'   => $name,
				'id'     => $name,
				'value'  => $this->a->get_value_from_post( $name, array( 'value' => 5 ) ),
			) ),
		) );

		$name = 'button_margin_vertical';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Vertical margins' ),
			'tooltip' => $this->a->__( 'Top and bottom margins between the button and page contents' ),
			'element' => $this->a->r()->render_dimension( array(
				'values' => 'px',
				'titles' => $this->a->__( 'Margins in px' ),
				'maxes'  => '50',
				'name'   => $name,
				'id'     => $name,
				'value'  => $this->a->get_value_from_post( $name, array( 'value' => 5 ) ),
			) ),
		) );

		$name = 'button_margin_horizontal';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Horizontal margins' ),
			'tooltip' => $this->a->__( 'Left and right margins between the button and page contents' ),
			'element' => $this->a->r()->render_dimension( array(
				'values' => 'px',
				'titles' => $this->a->__( 'Margins in px' ),
				'maxes'  => '50',
				'name'   => $name,
				'id'     => $name,
				'value'  => $this->a->get_value_from_post( $name, array( 'value' => 5 ) ),
			) ),
		) );

		$name = 'button_color';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Button\'s color' ),
			'tooltip' => $this->a->__( 'A fill color of the payment button' ),
			'element' => $this->a->r()->render_color( array(
				'name'   => $name,
				'id'     => $name,
				'value'  => $this->a->get_value_from_post( $name, '#1d7deb' ),
				'class'  => 'iris',
			) ),
		) );

		$name = 'button_text_color';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Button\'s text color' ),
			'tooltip' => $this->a->__( 'Color of the payment button\'s caption' ),
			'element' => $this->a->r()->render_color( array(
				'name'   => $name,
				'id'     => $name,
				'value'  => $this->a->get_value_from_post( $name, '#ffffff' ),
				'class'  => 'iris',
			) ),
		) );

		$name = 'button_full_width';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Full width' ),
			'tooltip' => $this->a->__( 'Layout of the button becomes responsive - filling in all available width' ),
			'element' => $this->a->r()->render_fancy_checkbox( array(
				'name'     => $name,
				'id'       => $name,
				'value'    => $this->a->get_value_from_post( $name ),
				'text_on'  => $this->a->__( 'Yes' ),
				'text_off' => $this->a->__( 'No' ),
			) ),
		) );

		$name = 'webhook_url';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Webhook\'s URL' ),
			'label_for' => 'input-' . $name,
			'tooltip'   => $this->a->__( 'The Stripe\'s webhooks endpoint for your store' ),
			'element'   => array(
				'type'        => 'text',
				'name'        => $name,
				'class'       => 'form-control clipboard',
				'custom_data' => 'readonly="readonly"',
				'value'       => $this->a->u()->catalog_url( 'auto' ) . 'index.php?route=' . $this->a->full_name . '/webhooks',
				'id'          => 'input-' . $name,
			),
			'error'       => isset( $resp['input_errors'][ $name ] ) ?
				$resp['input_errors'][ $name ] : null,
		) );

		$name = 'create_subscription_callback';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Subscription creation callback' ),
			'label_for' => 'input-' . $name,
			'tooltip'   => $this->a->__( 'URL to send callback to on subscription creation' ),
			'element'   => array(
				'type'        => 'text',
				'name'        => $name,
				'class'       => 'form-control',
				'value'       => $this->a->get_value_from_post( $name ),
				'id'          => 'input-' . $name,
				'placeholder' => 'http://callback_url.com',
			),
			'error'       => isset( $resp['input_errors'][ $name ] ) ?
				$resp['input_errors'][ $name ] : null,
		) );

		$name = 'create_subscription_callback_data';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Creation callback data' ),
			'label_for' => 'input-' . $name,
			'tooltip'   => $this->a->__( 'Additional data in a form of foo=bar, baz=boo, to be sent as POST query along with: oc_customer, stripe_customer, oc_subscription, stripe_subscription, status (new) to callback URL on subscription creation' ),
			'element'   => array(
				'type'        => 'text',
				'name'        => $name,
				'class'       => 'form-control',
				'value'       => $this->a->get_value_from_post( $name ),
				'id'          => 'input-' . $name,
				'placeholder' => 'foo=bar,baz=boo',
			),
			'error'       => isset( $resp['input_errors'][ $name ] ) ?
				$resp['input_errors'][ $name ] : null,
		) );

		$name = 'cancel_subscription_callback';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Subscription cancellation callback' ),
			'label_for' => 'input-' . $name,
			'tooltip'   => $this->a->__( 'URL to send callback to on subscription cancellation' ),
			'element'   => array(
				'type'        => 'text',
				'name'        => $name,
				'class'       => 'form-control',
				'value'       => $this->a->get_value_from_post( $name ),
				'id'          => 'input-' . $name,
				'placeholder' => 'http://callback_url.com',
			),
			'error'       => isset( $resp['input_errors'][ $name ] ) ?
				$resp['input_errors'][ $name ] : null,
		) );

		$name = 'cancel_subscription_callback_data';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Cancellation callback data' ),
			'label_for' => 'input-' . $name,
			'tooltip'   => $this->a->__( 'Additional data in a form of foo=bar, baz=boo, to be sent as POST query along with: oc_customer, stripe_customer, oc_subscription, stripe_subscription, status (cancel) to callback URL on subscription cancellation' ),
			'element'   => array(
				'type'        => 'text',
				'name'        => $name,
				'class'       => 'form-control',
				'value'       => $this->a->get_value_from_post( $name ),
				'id'          => 'input-' . $name,
				'placeholder' => 'foo=bar,baz=boo',
			),
			'error'       => isset( $resp['input_errors'][ $name ] ) ?
				$resp['input_errors'][ $name ] : null,
		) );

		$id = 'template-adk_stripe_template_error_order';
		$name = $this->a->build_name( $id, '-' );
		$data['template'] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Payment error template' ),
			'label_for' => $name,
			'tooltip'   => $this->a->__( 'The email message\'s template to send to the store owner on payment failure. List of supported shortcodes: %s', implode( ', ', $shortcode->list_of_supported() ) ),
			'element'   => array(
				'type'        => 'textarea',
				'name'        => $name,
				'class'       => 'form-control summernote',
				'value'       => $this->a->get_value_from_post( $name ),
				'id'          => $name,
			),
			'error'       => isset( $resp['input_errors']['template']['adk_stripe_template_errro_order'] ) ?
				$resp['input_errors']['template']['adk_stripe_template_errro_order'] : null,
		) );

		$data['select_stripe_account'] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Select Stripe account' ),
			'label_for' => 'select-account',
			'element'   => array(
				'type'        => 'select',
				'class'       => 'form-control',
				'active'      => null,
				'value'       => $option->stripe_account(),
				'id'          => 'select-account',
			),
			'class'     => 'static',
		) );

		$name = 'caption_charge_value';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Different currency' ),
			'label_for' => 'input-' . $name,
			'tooltip'   => $this->a->__( 'A caption to show to the customer when a store level currency conversion will occur, eg when charge will be made in store\'s currency rather than in currency of an order' ),
			'element' => array(
				'type' => 'lang_set',
				'element' => array(
					'type'        => 'text',
					'name'        => $name,
					'class'       => 'form-control',
					'value'       => 'Your card will be charged for',
					'id'          => 'input-' . $name,
				),
			),
			'error'       => isset( $resp['input_errors'][ $name ] ) ?
				$resp['input_errors'][ $name ] : null,
		) );

		$name = 'caption_form_cardholder_name';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Cardholder name' ),
			'label_for' => 'input-' . $name,
			'tooltip'   => $this->a->__( 'Label for the cardholder name input of embedded payment form' ),
			'element' => array(
				'type' => 'lang_set',
				'element'   => array(
					'type'        => 'text',
					'name'        => $name,
					'class'       => 'form-control',
					'value'       => 'Cardholder\'s name',
					'id'          => 'input-' . $name,
				),
			),
			'error'       => isset( $resp['input_errors'][ $name ] ) ?
				$resp['input_errors'][ $name ] : null,
		) );

		$name = 'caption_form_card_nmber';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Card number' ),
			'label_for' => 'input-' . $name,
			'tooltip'   => $this->a->__( 'Label for the card number input of embedded payment form' ),
			'element' => array(
				'type' => 'lang_set',
				'element'   => array(
					'type'        => 'text',
					'name'        => $name,
					'class'       => 'form-control',
					'value'       => 'Card number',
					'id'          => 'input-' . $name,
				),
			),
			'error'       => isset( $resp['input_errors'][ $name ] ) ?
				$resp['input_errors'][ $name ] : null,
		) );

		$name = 'caption_form_switch_mode';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Switch mode' ),
			'label_for' => 'input-' . $name,
			'tooltip'   => $this->a->__( 'Title of button which switches plain and formatted mode of a card number input field of embedded form' ),
			'element' => array(
				'type' => 'lang_set',
				'element'   => array(
					'type'        => 'text',
					'name'        => $name,
					'class'       => 'form-control',
					'value'       => 'Switch mode',
					'id'          => 'input-' . $name,
				),
			),
			'error'       => isset( $resp['input_errors'][ $name ] ) ?
				$resp['input_errors'][ $name ] : null,
		) );

		$name = 'caption_form_card_expiration';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Expiration date' ),
			'label_for' => 'input-' . $name,
			'tooltip'   => $this->a->__( 'Label for the card expiration date inputs of embedded payment form' ),
			'element' => array(
				'type' => 'lang_set',
				'element'   => array(
					'type'        => 'text',
					'name'        => $name,
					'class'       => 'form-control',
					'value'       => 'Expiration date',
					'id'          => 'input-' . $name,
				),
			),
			'error'       => isset( $resp['input_errors'][ $name ] ) ?
				$resp['input_errors'][ $name ] : null,
		) );

		$name = 'caption_form_card_cvc';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'CVV code' ),
			'label_for' => 'input-' . $name,
			'tooltip'   => $this->a->__( 'Label for the card CVV code input of embedded payment form' ),
			'element' => array(
				'type' => 'lang_set',
				'element'   => array(
					'type'        => 'text',
					'name'        => $name,
					'class'       => 'form-control',
					'value'       => 'CVV code',
					'id'          => 'input-' . $name,
				),
			),
			'error'       => isset( $resp['input_errors'][ $name ] ) ?
				$resp['input_errors'][ $name ] : null,
		) );

		$name = 'caption_form_rerember_me';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Save card' ),
			'label_for' => 'input-' . $name,
			'tooltip'   => $this->a->__( 'Label for "Save card" checkbox of embedded payment form' ),
			'element' => array(
				'type' => 'lang_set',
				'element'   => array(
					'type'        => 'text',
					'name'        => $name,
					'class'       => 'form-control',
					'value'       => 'Save payment card',
					'id'          => 'input-' . $name,
				),
			),
			'error'       => isset( $resp['input_errors'][ $name ] ) ?
				$resp['input_errors'][ $name ] : null,
		) );

		$name = 'caption_form_rerember_me_description';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Save card description' ),
			'label_for' => 'input-' . $name,
			'tooltip'   => $this->a->__( 'Description text for "Save card" checkbox of embedded payment form' ),
			'element' => array(
				'type' => 'lang_set',
				'element'   => array(
					'type'        => 'textarea',
					'name'        => $name,
					'class'       => 'form-control',
					'value'       => strip_tags( 'The next time you will be able to choose a saved card and safely operate with token of that card instead of a card\'s data itself, which will increase security of payment transactions. You can manage saved cards from Your account' ),
					'id'          => 'input-' . $name,
				),
			),
			'error'       => isset( $resp['input_errors'][ $name ] ) ?
				$resp['input_errors'][ $name ] : null,
		) );

		$name = 'caption_form_make_default';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Make default' ),
			'label_for' => 'input-' . $name,
			'tooltip'   => $this->a->__( 'Label for "Make saved card default" checkbox of embedded payment form' ),
			'element' => array(
				'type' => 'lang_set',
				'element'   => array(
					'type'        => 'text',
					'name'        => $name,
					'class'       => 'form-control',
					'value'       => 'Make default',
					'id'          => 'input-' . $name,
				),
			),
			'error'       => isset( $resp['input_errors'][ $name ] ) ?
				$resp['input_errors'][ $name ] : null,
		) );

		$name = 'caption_form_make_default_description';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Make default description' ),
			'label_for' => 'input-' . $name,
			'tooltip'   => $this->a->__( 'Description text for "Make saved card default" checkbox of embedded payment form' ),
			'element' => array(
				'type' => 'lang_set',
				'element'   => array(
					'type'        => 'textarea',
					'name'        => $name,
					'class'       => 'form-control',
					'value'       => strip_tags( 'You may set new card as default card and it will be charged for all recurring payments' ),
					'id'          => 'input-' . $name,
				),
			),
			'error'       => isset( $resp['input_errors'][ $name ] ) ?
				$resp['input_errors'][ $name ] : null,
		) );

		$name = 'caption_form_select_card';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Choose saved card' ),
			'label_for' => 'input-' . $name,
			'tooltip'   => $this->a->__( 'Label for drop-down select with list of saved cards' ),
			'element' => array(
				'type' => 'lang_set',
				'element'   => array(
					'type'        => 'text',
					'name'        => $name,
					'class'       => 'form-control',
					'value'       => 'Choose saved card',
					'id'          => 'input-' . $name,
				),
			),
			'error'       => isset( $resp['input_errors'][ $name ] ) ?
				$resp['input_errors'][ $name ] : null,
		) );

		$name = 'caption_form_card_password';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Saved card password' ),
			'label_for' => 'input-' . $name,
			'tooltip'   => $this->a->__( 'Label for password input of saved card' ),
			'element' => array(
				'type' => 'lang_set',
				'element'   => array(
					'type'        => 'text',
					'name'        => $name,
					'class'       => 'form-control',
					'value'       => 'Password',
					'id'          => 'input-' . $name,
				),
			),
			'error'       => isset( $resp['input_errors'][ $name ] ) ?
				$resp['input_errors'][ $name ] : null,
		) );

		$name = 'caption_form_card_password_description';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Password description' ),
			'label_for' => 'input-' . $name,
			'tooltip'   => $this->a->__( 'Description text for password field of saved card' ),
			'element' => array(
				'type' => 'lang_set',
				'element'   => array(
					'type'        => 'textarea',
					'name'        => $name,
					'class'       => 'form-control',
					'value'       => strip_tags( 'Create a password and only you can use this saved card. If you\'ll forget password - save the card again' ),
					'id'          => 'input-' . $name,
				),
			),
			'error'       => isset( $resp['input_errors'][ $name ] ) ?
				$resp['input_errors'][ $name ] : null,
		) );

		$name = 'caption_wait_script';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Script loading' ),
			'label_for' => 'input-' . $name,
			'tooltip'   => $this->a->__( 'Notification for customer that he/she need to wait till all scripts will be fully loaded' ),
			'element' => array(
				'type' => 'lang_set',
				'element'   => array(
					'type'        => 'text',
					'name'        => $name,
					'class'       => 'form-control',
					'value'       => 'Please wait until scripts will be fully loaded',
					'id'          => 'input-' . $name,
				),
			),
			'error'       => isset( $resp['input_errors'][ $name ] ) ?
				$resp['input_errors'][ $name ] : null,
		) );

		$name = 'caption_payment_error';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Payment error' ),
			'label_for' => 'input-' . $name,
			'tooltip'   => $this->a->__( 'Error message on payment gateway failure' ),
			'element' => array(
				'type' => 'lang_set',
				'element'   => array(
					'type'        => 'text',
					'name'        => $name,
					'class'       => 'form-control',
					'value'       => 'There was an error when ordering. Please use another payment method',
					'id'          => 'input-' . $name,
				),
			),
			'error'       => isset( $resp['input_errors'][ $name ] ) ?
				$resp['input_errors'][ $name ] : null,
		) );

		$name = 'caption_payment_success';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Successful payment' ),
			'label_for' => 'input-' . $name,
			'tooltip'   => $this->a->__( 'Message about successful payment' ),
			'element' => array(
				'type' => 'lang_set',
				'element'   => array(
					'type'        => 'text',
					'name'        => $name,
					'class'       => 'form-control',
					'value'       => 'Order was successfully placed',
					'id'          => 'input-' . $name,
				),
			),
			'error'       => isset( $resp['input_errors'][ $name ] ) ?
				$resp['input_errors'][ $name ] : null,
		) );

		$name = 'caption_order_placing';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Order placing' ),
			'label_for' => 'input-' . $name,
			'tooltip'   => $this->a->__( 'Message which being shown to a customer while order is placed' ),
			'element' => array(
				'type' => 'lang_set',
				'element'   => array(
					'type'        => 'text',
					'name'        => $name,
					'class'       => 'form-control',
					'value'       => 'Placing order...',
					'id'          => 'input-' . $name,
				),
			),
			'error'       => isset( $resp['input_errors'][ $name ] ) ?
				$resp['input_errors'][ $name ] : null,
		) );

		$name = 'caption_empty_card_number';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Empty card number' ),
			'label_for' => 'input-' . $name,
			'tooltip'   => $this->a->__( 'An error message which says that card\'s number field of embedded form need to be filled' ),
			'element' => array(
				'type' => 'lang_set',
				'element'   => array(
					'type'        => 'text',
					'name'        => $name,
					'class'       => 'form-control',
					'value'       => 'Please fill in card\'s number field',
					'id'          => 'input-' . $name,
				),
			),
			'error'       => isset( $resp['input_errors'][ $name ] ) ?
				$resp['input_errors'][ $name ] : null,
		) );

		$name = 'caption_token_create';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Token creation' ),
			'label_for' => 'input-' . $name,
			'tooltip'   => $this->a->__( 'Message which being shown to a customer while card\'s token being created' ),
			'element' => array(
				'type' => 'lang_set',
				'element'   => array(
					'type'        => 'text',
					'name'        => $name,
					'class'       => 'form-control',
					'value'       => 'Token of a card is being created...',
					'id'          => 'input-' . $name,
				),
			),
			'error'       => isset( $resp['input_errors'][ $name ] ) ?
				$resp['input_errors'][ $name ] : null,
		) );

		$name = 'caption_button_placing';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Pay button caption' ),
			'label_for' => 'input-' . $name,
			'tooltip'   => $this->a->__( 'Caption of "Pay-in-one-click" button which being shown while order is placed' ),
			'element' => array(
				'type' => 'lang_set',
				'element'   => array(
					'type'        => 'text',
					'name'        => $name,
					'class'       => 'form-control',
					'value'       => 'Placing order...',
					'id'          => 'input-' . $name,
				),
			),
			'error'       => isset( $resp['input_errors'][ $name ] ) ?
				$resp['input_errors'][ $name ] : null,
		) );

		$name = 'caption_unknown_vendor';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Unknown vendor' ),
			'label_for' => 'input-' . $name,
			'tooltip'   => $this->a->__( 'Error message which is shown to a customer on attempt to pay by card which is not supported by Stripe' ),
			'element' => array(
				'type' => 'lang_set',
				'element'   => array(
					'type'        => 'text',
					'name'        => $name,
					'class'       => 'form-control',
					'value'       => 'Unknown card vendor',
					'id'          => 'input-' . $name,
				),
			), 
			'error'       => isset( $resp['input_errors'][ $name ] ) ?
				$resp['input_errors'][ $name ] : null,
		) );

		$name = 'caption_forbidden_vendor';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Forbidden vendor' ),
			'label_for' => 'input-' . $name,
			'tooltip'   => $this->a->__( 'Error message which is shown to a customer on attempt to pay by card which is disabled in extension\'s configurations' ),
			'element' => array(
				'type' => 'lang_set',
				'element'   => array(
					'type'        => 'text',
					'name'        => $name,
					'class'       => 'form-control',
					'value'       => 'Payment system is not permitted, please use another payment card or payment method',
					'id'          => 'input-' . $name,
				),
			),
			'error'       => isset( $resp['input_errors'][ $name ] ) ?
				$resp['input_errors'][ $name ] : null,
		) );

		$name = 'caption_error_card_password_save';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Saved card password' ),
			'label_for' => 'input-' . $name,
			'tooltip'   => $this->a->__( 'Error message which says that customer need to specify password in order to save payment card' ),
			'element' => array(
				'type' => 'lang_set',
				'element'   => array(
					'type'        => 'text',
					'name'        => $name,
					'class'       => 'form-control',
					'value'       => 'You need specify password for this card',
					'id'          => 'input-' . $name,
				),
			),
			'error'       => isset( $resp['input_errors'][ $name ] ) ?
				$resp['input_errors'][ $name ] : null,
		) );

		$name = 'caption_error_card_password_use';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Saved card password (use)' ),
			'label_for' => 'input-' . $name,
			'tooltip'   => $this->a->__( 'Error message which says that customer need to specify password in order to use saved payment card' ),
			'element' => array(
				'type' => 'lang_set',
				'element'   => array(
					'type'        => 'text',
					'name'        => $name,
					'class'       => 'form-control',
					'value'       => 'In order to use saved card you need to specify password',
					'id'          => 'input-' . $name,
				),
			),
			'error'       => isset( $resp['input_errors'][ $name ] ) ?
				$resp['input_errors'][ $name ] : null,
		) );

		$name = 'caption_script_error';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Script error' ),
			'label_for' => 'input-' . $name,
			'tooltip'   => $this->a->__( 'Error message which is shown to a customer if script error will occur' ),
			'element' => array(
				'type' => 'lang_set',
				'element'   => array(
					'type'        => 'text',
					'name'        => $name,
					'class'       => 'form-control',
					'value'       => 'Script error. Please use another payment method',
					'id'          => 'input-' . $name,
				),
			),
			'error'       => isset( $resp['input_errors'][ $name ] ) ?
				$resp['input_errors'][ $name ] : null,
		) );

		$name = 'pc_number_input';
		$data[ $name ] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Card number input' ),
			'label_for' => 'input-' . $name,
			'tooltip'   => $this->a->__( 'Choose how payment card number input field should look like' ),
			'element'   => array(
				'type'        => 'select',
				'name'        => $name,
				'class'       => 'form-control',
				'active'      => $this->a->get_value_from_post( $name ),
				'value'       => $option->input_appearance(),
				'id'          => 'input-' . $name,
			),
			'error'      => isset( $resp['input_errors'][ $name ] ) ?
				$resp['input_errors'][ $name ] : null,
		) );

		$shortcode = new Advertikon\Shortcode();

		$data['locale'] = json_encode( array(
			'checkTlsUrl'               => $this->a->u()->url( 'check_tls' ),
			'variables'                 => '{' . implode( '},{' , array_keys( $shortcode->get_shortcode_data() ) ) . '}',
			'plansError'                => $this->a->__( "Unable to load plans" ),
			'deletePlanSure'            => $this->a->__( 'You about to delete Stripe plan' ),
			'deleteProfileSure'         => $this->a->__( 'You about to delete profile' ),
			'errorCantProfilesList'     => $this->a->__( 'Failed to load list of profiles' ),
			'errorLoadProfilesMap'      => $this->a->__( 'Failed to load profiles mapping list' ),
			'accountDetailsTemplate'    => $this->get_stripe_account_field(),
			'errorNameExists'           => $this->a->__( 'Name already exists' ),
			'errorAcountSameCurrency'   => $this->a->__( 'Two accounts can not have the same currency' ),
			'imgStripeUrl'              => $this->a->u()->catalog_url() . 'image/advertikon/stripe/',
			'dirLogs'                   => DIR_LOGS,
			'profileUrl'                => $this->a->u()->url( 'profile_table' ),
			'profileTemplate'           => $this->profile_line(),
			'profileMapUrl'             => $this->a->u()->url( 'profile_map_table' ),
			'profileMapMapUrl'          => $this->a->u()->url( 'profile_map' ),
			'plansUrl'                  => $this->a->u()->url( 'plan_table' ),
			'currency'                  => array_values( $option->currency() ),

			// Common stuff
			'networkError'              => $this->a->__( 'Network error' ),
			'parseError'                => $this->a->__( 'Unable to parse server response string' ),
			'undefServerResp'           => $this->a->__( 'Undefined server response' ),
			'serverError'               => $this->a->__( 'Server error' ),
			'sessionExpired'            => $this->a->__( 'Current session has expired' ),
			'modalHeader'               => 'Stripe',
			'yes'                       => $this->a->__( 'Yes' ),
			'no'                        => $this->a->__( 'No' ),
			'clipboard'                 => '',
			'ticketButtonUrl'           => $this->a->u()->url( 'ticket_button' ),
		) );

		$data['accounts'] = array();
		$data['a']        = $this->a;

		foreach( $this->a->get_value_from_post( 'account', array( 'default' => array() ) ) as $name => $account ) {
			$data['accounts'][] = $this->get_stripe_account_field( $data, $name );
		}

		$this->response->setOutput( $this->load->view( $this->a->get_template( $this->a->full_name ), $data ) );
	}

	/**
	 * Returns opencart recurring plan's table
	 * @param int $page Page number
	 * @return string
	 * @throws Stripe\Error\Base
	 */
	protected function get_plans_table( $page = 1 ) {
		$limit = $this->config->get( 'config_limit_admin' );

		// Since Stripe had freakish pagination system - fetch all and slice manually
		$plans = $this->a->fetch_api_plan_all();
		$oc_plan_resource = new Advertikon\Stripe\Resource\Plan();
		$oc_plans = $oc_plan_resource->all();
		$pagination = new Pagination;
		$pagination->page = $page;
		$pagination->limit = $limit;
		$pagination->total = count( $plans->data );

		$from =  min( max( ( ( $page - 1 ) * $limit ), 0 ), $pagination->total );
		$plans->data = array_slice( $plans->data, $from, $limit );
		$pagination->url = $this->a->u()->url( 'plan_table', array( 'page' => '{page}' ) );
		$out = '<table class="table table-hover" >'
					.'<thead>'
						.'<tr>'
							.'<th>' . $this->a->__( 'OpenCart Recurring Profile' ) . '</th>'
							.'<th>' . $this->a->__( 'Stripe Plan' ) . '</th>'
							.'<th>' . $this->a->__( 'Statement Descriptor') . '</th>'
						.'</tr>'
					.'</thead>'
					.'<tbody>';

		foreach( $plans->data as $plan ) {
			$out .= '<tr data-sp-plan="' . htmlentities( $plan->__toJSON() ) . '">';
			$match = false;
			foreach( $oc_plans as $oc_plan ) {
				if ( $plan->id === $oc_plan->sp_plan_id ) {
					$out .= sprintf( $this->exists_oc_plan(), $oc_plan->name, $oc_plan->oc_plan_id );
					$match = true;
					break;
				}
			}

			if ( ! $match ){
				$out .= sprintf( $this->empty_oc_plan(), htmlentities( $plan->__toJSON() ) );
			}

			$exists = true;

			try {

				// Throws exception when currencies are different
				$exists = $this->model->is_plan_exists( $plan );

			} catch ( Advertikon\Exception $e ) {

			}

			$out .= sprintf(
				$this->stripe_plan(),
				! $exists ? 'active' : '',
				$plan->name,
				$plan->id,
				$this->a->get_stripe_plan_string( $plan )
			);

			$out .= sprintf( $this->stripe_plan_descriptor(), $plan->statement_descriptor );
			$out .= '</tr>';
		}

		$out .= '</tbody></table>';
		$out .= $pagination->render();

		return $out;
	}

	/**
	 * Returns plan's profile table contents
	 * @param int $page Page number
	 * @return string
	 */
	protected function get_plan_profile_table( $page = 1 ) {

		$profile = new Advertikon\Stripe\Resource\Profile();
		$profiles = $profile->all();
		$limit = ceil( $this->config->get( 'config_limit_admin' ) / 5 );
		$pagination = new Pagination;
		$pagination->page = $page;
		$pagination->limit = $limit;
		$pagination->total = count( $profiles );
		$from =  min( max( ( ( $page - 1 ) * $limit ), 0 ), $pagination->total );

		$profiles->slice( $from, $limit );
		$pagination->url = $this->a->u()->url( 'profile_table', array('page' => '{page}' ) );

		$data['profile']        = $profile->load_non_recurring();
		$data['option']         = new Advertikon\Option();
		$data['totals']         = $data['option']->totals();
		$data['profile_totals'] = $profile->totals_to_recurring;
		$data['profiles']       = $profiles;
		$data['self']           = $this;
		$data['pagination']     = $pagination;
		$data['add_profile']    = $this->a->r( array(
			'type'  => 'button',
			'icon'  => 'fa-plus',
			'class' => 'active add',
			'title' => $this->a->__( 'Add profile' ),
			'id'    => 'add-profile',
		) );
		$data['save_button'] = $this->a->r( array(
			'type'  => 'button',
			'icon'  => 'fa-save',
			'class' => 'active edit save-profile',
			'title' => $this->a->__( 'Save' ),
			'custom_data' => 'data-url="' . $this->a->u()->url( 'profile_edit' ) . '"'
		) );

		$data['a'] = $this->a;

		return $this->load->view(
			$this->a->get_template( $this->a->type . '/advertikon/stripe/profile_table' ),
			$data
		);
	}

	/**
	 * Returns profiles map table contents
	 * @param int $page Page number
	 * @return string
	 */
	protected function get_plans_profile_map_table( $page = 1 ) {

		$oc_recurring = new Advertikon\Stripe\Resource\OC_Plan();
		$plans = $oc_recurring->all();

		$profile = new Advertikon\Stripe\Resource\Profile();
		$profiles = $profile->all();

		$limit = $this->config->get( 'config_limit_admin' );
		$pagination = new Pagination;
		$pagination->page = $page;
		$pagination->limit = $limit;
		$pagination->total = count( $plans );
		$from =  min( max( ( ( $page - 1 ) * $limit ), 0 ), $pagination->total );
		$pagination->url = $this->a->u()->url( 'profile_map_table', array( 'page' => '{page}' ) );

		$plans->slice( $from, $limit );

		$data['plans'] = $plans;
		$data['profiles']   = $profiles;
		$data['pagination'] = $pagination;
		$data['a'] = $this->a;

		return $this->load->view( $this->a->get_template( $this->a->type . '/advertikon/stripe/profile_map' ), $data );
	}

	/**
	 * Returns line for template mapping
	 * @param object|null Recurring profile
	 * @return string
	 */
	public function profile_line( $profile = null ) {
		$checked = array( 'total', 'sub_total' );
		$fixed = array( 'total', 'sub_total' );

		$data['option']     = new Advertikon\Stripe\Option();
		$data['totals']     = $data['option']->totals();
		$data['profile']    = $profile;
		$data['save_button'] = $this->a->r( array(
			'type'        => 'button',
			'class'       => 'save-profile',
			'custom_data' => 'data-url="' . $this->a->u()->url( 'profile_edit' ) . '"',
			'title'       => $this->a->__( 'Save' ),
			'icon'        => 'fa-save',
			'button_type' => 'success',
		) );

		$data['delete_button'] = $this->a->r( array(
			'type'        => 'button',
			'class'       => 'delete-profile',
			'custom_data' => 'data-url="' . $this->a->u()->url( 'profile_delete' ) . '"',
			'title'       => $this->a->__( 'Delete' ),
			'icon'        => 'fa-save',
			'button_type' => 'danger',
		) );

		$data['a'] = $this->a;
		
		return $this->load->view( $this->a->get_template( $this->a->type . '/advertikon/stripe/profile_line' ), $data );
	}

	/**
	 * Returns table's cell contents for OpenCart recurring profile when profile is missing
	 * @return String
	 */
	protected function empty_oc_plan() {
		$map_url = $this->a->u()->url( 'plan_map' );
		$input = $this->a->r( array(
			'type'        => 'text',
			'class'       => 'form-control',
			'placeholder' => $this->a->__( 'Start typing to auto-complete' ),
			'custom_data' => 'data-autocomplete-url="' . $this->a->u()->url( 'recurring_autocomplete' ) . '" ' .
							'data-autocomplete="%s"'
		) );

		return <<<HTML
<td class="col-xs-4">
	<div class="input-group">
		$input
		<span
			class="input-group-addon main map hidden-on"
			data-action="$map_url"
			title="{$this->a->__( 'Map' )}"
		>
			<i class="fa fa-lock"></i>
		</span>
		<span class="input-group-addon busy hidden-on"><i class="fa fa-spinner fa-pulse"></i></span>
		<span class="input-group-addon error hidden-on" title="{$this->a->__( 'Error' )}">
			<i class="fa fa-exclamation-circle"></i>
		</span>
	</div>
</td>
HTML;
	}

	/**
	 * Returns table's cell contents for OpenCart recurring profile when profile is exists
	 * @return String
	 */
	protected function exists_oc_plan() {
		$unmap_url = $this->a->u()->url( 'plan_unmap' );

		return <<<HTML
<td class="col-xs-4">
	<div class="input-group">
		<span
			class="input-group-addon main unmap active hidden-on"
			title="{$this->a->__( 'Remove mapping' )}"
			data-action="$unmap_url"
		>
			<i class="fa fa-unlock"></i>
		</span>
		<input type="text" class="form-control oc-exists" value="%s" data-plan-id="%s" readonly="readonly">
		<span class="input-group-addon busy hidden-on"><i class="fa fa-spinner fa-pulse"></i></span>
		<span class="input-group-addon error hidden-on" title="{$this->a->__( 'Error' )}">
			<i class="fa fa-exclamation-circle"></i>
		</span>
	</div>
</td>
HTML;
	}

	/**
	 * Returns table's cell contents for Stripe recurring profile
	 * @return String
	 */
	protected function stripe_plan() {
		$export_url = $this->a->u()->url( 'plan_export' );
		$delete_url = $this->a->u()->url( 'plan_delete' );
		$rename_url = $this->a->u()->url( 'plan_rename' );

		return <<<HTML
<td class="col-xs-4">
	<div class="input-group">
		<span
			class="input-group-addon main export hidden-on  %s"
			title="{$this->a->__( 'Export plan' )}"
			data-action="$export_url"
		>
			<i class="fa fa-chevron-left"></i>
		</span>
		<span
			class="input-group-addon main delete active hidden-on"
			title="{$this->a->__( 'Delete plan' )}"
			data-action="$delete_url"
		>
			<i class="fa fa-close"></i>
		</span>
		<span
			class="input-group-addon main rename active hidden-on"
			title="{$this->a->__( 'Rename palan' )}"
			data-action="$rename_url"
		>
			<i class="fa fa-pencil"></i>
		</span>
		<input type="text" class="form-control sp-plan" value="%s" data-plan-id="%s" title="%s">
		<span class="input-group-addon busy hidden-on"><i class="fa fa-spinner fa-pulse"></i></span>
		<span class="input-group-addon error hidden-on" title="{$this->a->__( 'Error' )}">
			<i class="fa fa-exclamation-circle"></i>
		</span>
	</div>
</td>
HTML;
	}

	/**
	 * Returns table's cell contents for Stripe recurring profile statement descriptor
	 * @return String
	 */
	protected function stripe_plan_descriptor() {
		$statement_url = $this->a->u()->url( 'plan_statement' );

		return <<<HTML
<td class="col-xs-4">
	<div class="input-group">
		<span
			class="input-group-addon active descriptor main hidden-on"
			title="{$this->a->__( 'Rename' )}"
			data-action="$statement_url"
		>
			<i class="fa fa-pencil"></i>
		</span>
		<input
			type="text"
			class="form-control descriptor"
			placeholder="{$this->a->__( 'Satement descriptor' )}" value="%s"
		>
		<span class="input-group-addon busy hidden-on"><i class="fa fa-spinner fa-pulse"></i></span>
		<span class="input-group-addon error hidden-on" title="{$this->a->__( 'Error' )}">
			<i class="fa fa-exclamation-circle"></i>
		</span>
	</div>
</td>
HTML;
	}

	/**
	 * Plan table action
	 * @return void
	 */
	public function plan_table() {
		$ret = '';

		try {
			$page = isset( $this->request->request['page'] ) ? $this->request->request['page'] : 1;
			$this->a->set_account( isset( $this->request->request['account'] ) ? $this->request->request['account'] : 'default' );
			$page = (int)$page ?: 1;
			$ret = $this->get_plans_table( $page ) . 'success';

		} catch ( Stripe\Error\Base $e ) {
			$ret = $e->getMessage() . 'error';

		} catch ( Advertikon\Exception $e ) {
			$ret = $e->getMessage() . 'error';
		}

		$this->response->setOutput( $ret );
	}

	/**
	 * Plan's profiles table action
	 * @return void
	 */
	public function profile_table() {
		$ret = '';

		try {
			$page = isset( $this->request->request['page'] ) ? $this->request->request['page'] : 1;
			$page = (int)$page ?: 1;
			$ret = $this->get_plan_profile_table( $page ) . 'success';
			
		} catch ( Stripe\Error\Base $e ) {
			$ret = $e->getMessage();

		} catch ( Advertikon\Exception $e ) {
			$ret = $e->getMessage();
		}

		$this->response->setOutput( $ret );
	}

	/**
	 * Plan's profiles map table action
	 * @return void
	 */
	public function profile_map_table() {
		$ret = '';

		try {
			$page = isset( $this->request->request['page'] ) ? $this->request->request['page'] : 1;
			$page = (int)$page ?: 1;
			$ret = $this->get_plans_profile_map_table( $page ) . 'success';
			
		} catch ( Stripe\Error\Base $e ) {
			$ret = $e->getMessage();

		} catch ( Advertikon\Exception $e ) {
			$ret = $e->getMessage();
		}

		$this->response->setOutput( $ret );
	}

	/**
	 * Map OC recurring plan to Stripe Plan action
	 * @return void
	 */
	public function plan_map() {
		$resp = '';

		try {
			$oc_plan_id = isset( $this->request->request['oc_id'] ) ? $this->request->request['oc_id'] : null;
			$sp_plan = isset( $this->request->request['plan'] ) ? $this->request->request['plan'] : null;

			if ( is_null( $oc_plan_id ) ) {
				$mess = $this->a->__(
					'Failed to map OpenCart recurring plan to Stripe Plan - OpenCart plan\'s ID is missing'
				);

				$this->a->log( $mess, $this->a->log_error_flag );
				throw new Advertikon\Exception( $mess );
			}

			if ( ! is_null( $sp_plan ) ) {
				$count = 0;
				while( preg_match( '/(&quot;|&amp;)/', $sp_plan ) && $count++ < 10 ) {
					$sp_plan = html_entity_decode( $sp_plan );
				}

				$sp_plan = trim( $sp_plan, '" ' );
				@$json = json_decode( $sp_plan );

				if ( $json && is_object( $json ) ) {
					$sp_plan = $json;
				}
			}

			if ( is_null( $sp_plan ) ) {
				$mess = $this->a->__( 'Failed to export Stripe plan - Stripe Plan is missing' );
				$this->a->log( $mess, $this->A->log_error_flag );
				throw new Advertikon\Exception( $mess );
			}

			// We do not made any checks, since all check have been made earlier
			$plan = new Advertikon\Stripe\Resource\Plan( $sp_plan->id , 'sp_plan_id');

			if ( $plan->is_exists() ) {
				$mess = $this->a->__( 'Stripe\'s Plan "%s" has already been mapped', $sp_plan->id );
				$this->a->log( $mess, $this->log_error_flag );
				throw new Advertikon\Exception( $mess );

			} else {
				$plan->oc_plan_id = $oc_plan_id;
				$plan->sp_plan_id = $sp_plan->id;
				$plan->plan = $sp_plan;
				$plan->save();

				// To get plan name
				$plan->load( $plan->sp_plan_id, 'sp_plan_id' );
				$resp = sprintf( $this->exists_oc_plan(), $plan->name, $oc_plan_id ) . 'success';
			}
			
		} catch ( Stripe\Error\Base $e ) {
			$resp = $e->getMessage() . 'error';

		} catch ( Advertikon\Exception $e ) {
			$resp = $e->getMessage() . 'error';
		}

		$this->response->setOutput( $resp );
	}

	/**
	 * Remove plans mapping action
	 * @return void
	 */
	public function plan_unmap() {
		$resp = '';

		try {
			$oc_plan_id = isset( $this->request->request['oc_id'] ) ? $this->request->request['oc_id'] : null;
			$sp_plan = isset( $this->request->request['plan'] ) ? $this->request->request['plan'] : null;

			if ( is_null( $oc_plan_id ) ) {
				$mess = $this->a->__(
					'Failed to remove mapping of OpenCart recurring plan to Stripe Plan - OpenCart\'s Plan ID is missing'
				);
				$model->log( $mess, $this->a->log_error_flag );
				throw new Advertikon\Exception( $mess );
			}

			if ( ! is_null( $sp_plan ) ) {
				$count = 0;
				while( preg_match( '/(&quot;|&amp;)/', $sp_plan ) && $count++ < 10 ) {
					$sp_plan = html_entity_decode( $sp_plan );
				}

				$sp_plan = trim( $sp_plan, '" ' );
				@$json = json_decode( $sp_plan );

				if ( $json && is_object( $json ) ) {
					$sp_plan = $json;
				}
			}

			if ( is_null( $sp_plan ) ) {
				$mess = $this->a->__(
					'Failed to cancel mapping of OpenCart recurring plan to Stripe Plan - Stripe\'s Plan is missing'
				);
				$model->log( $mess, $this->a->log_error_flag );
				throw new Adverikon\Exception( $mess );
			}

			$plan = new Advertikon\Stripe\Resource\Plan();
			$plan->load( $oc_plan_id, 'oc_plan_id' );

			if ( ! $plan->is_exists() ) {
				$mess = $this->a->__( 'OpenCart\'s Plan #%s is not mapped to any Stripe\'s Plan', $oc_plan_id );
				$this->a->log( $mess, $this->a->log_error_flag );
				throw new Advertikon\Exception( $mess );

			} else {
				$plan->delete();
				$resp = sprintf( $this->empty_oc_plan(), htmlentities( json_encode( $sp_plan ) ) ) . 'success';
			}

		} catch ( Stripe\Error\Base $e ) {
			$resp = $e->getMessage() . 'error';

		} catch ( Advertikon\Exception $e ) {
			$resp = $e->getMessage() . 'error';
		}

		$this->response->setOutput( $resp );
	}

	/**
	 * Rename Stripe's plan statement descriptor action
	 * @return void
	 */
	public function plan_statement() {
		$resp = '';

		try {
			$statement = isset( $this->request->request['statement'] ) ?
				$this->request->request['statement'] : null;
			$sp_plan_id = isset( $this->request->request['sp-plan-id'] ) ?
				$this->request->request['sp-plan-id'] : null;

			$this->a->set_account( isset( $this->request->request['account'] ) ?
				$this->request->request['account'] : 'default' );		

			if ( is_null( $sp_plan_id ) ) {
				$mess = $this->a->__( 'Stripe plan\'s is missing ID' );
				$this->a->log( $mess, $this->a->log_error_flag );
				throw Advertikon\Exception( $mess );
			}

			if ( is_null( $statement ) ) {
				$mess = $this->a->log( 'Statement descriptor is missing' );
				$model->log( $mess, $this->a->log_error_flag );
				throw new Advertikon\Exception( $mess );
			}

			$plan = $this->a->plan_new_statement_descriptor( $sp_plan_id, $statement );
			$resp = sprintf( $this->stripe_plan_descriptor(), $plan->statement_descriptor ) . 'success';

			$plan_res = new Advertikon\Stripe\Resource\Plan();
			$plan_res->load( $plan->id, 'sp_plan_id' );

			if ( $plan_res->is_exists() ) {
				$plan_res->plan = $plan;
				$plan_res->save();
			}

		} catch( Advertikon\Exception $e ) {
			$resp = $e->getMessage() . 'error';

		} catch( Exception $e ) {
			$resp = $e->getMessage() . 'error';
		}

		$this->response->setOutput( $resp );
	}

	/**
	 * Rename Stripe plan action
	 * @return void
	 */
	public function plan_rename() {
		$resp = '';

		try {

			$name = isset( $this->request->request['name'] ) ? $this->request->request['name'] : null;
			$sp_plan_id = isset( $this->request->request['sp-plan-id'] ) ? $this->request->request['sp-plan-id'] : null;
			$this->a->set_account( isset( $this->request->request['account'] ) ? $this->request->request['account'] : 'default' );		

			if ( is_null( $sp_plan_id ) ) {
				$mess = $this->a->__( 'Plan\'s ID is missing' );
				$this->a->log( $mess, $this->a->log_error_flag );
				throw new Advertikon\Exception( $mess );
			}

			if ( is_null( $name ) ) {
				$mess = $this->a->__( 'Plan\'s name is missing' );
				$this->a->log( $mess, $this->a->log_error_flag );
				throw new Advertikon\Exception( $mess );
			}

			$plan = $this->a->plan_rename( $sp_plan_id, $name );
			$plan_res = new Advertikon\Stripe\Resource\Plan( $plan->id, 'sp_plan_id' );

			if ( $plan_res->is_exists() ) {
				$plan_res->plan = $plan;
				$plan_res->save();
			}

			$exists = true;

			try {

				// Throws exception if currencies are different
				$exists = $this->model->is_plan_exists( $plan );
			} catch ( Advertikon\Exception $e ) {

			}

			$resp =  sprintf(
				$this->stripe_plan(),
				! $exists ? 'active' : '',
				$plan->name,
				$plan->id,
				$this->a->get_stripe_plan_string( $plan )
			). 'success';

		} catch( Advertikon\Exception $e ) {
			$resp = $e->getMessage() . 'error';

		} catch( Exception $e ) {
			$resp = $e->getMessage() . 'error';
		}

		$this->response->setOutput( $resp );
	}

	/**
	 * Delete Stripe plan action
	 * @return void
	 */
	public function plan_delete() {
		$resp = '';

		try {
			$sp_plan_id = isset( $this->request->request['sp-plan-id'] ) ?
				$this->request->request['sp-plan-id'] : null;

			$account = isset( $this->request->request['account'] ) ?
				$this->request->request['account'] : null;		

			$this->a->plan_remove( $sp_plan_id, $account );
			$resp = 'success';

		} catch( Advertikon\Exception $e ) {
			$resp = $e->getMessage() . 'error';

		} catch( Exception $e ) {
			$resp = $e->getMessage() . 'error';
		}

		$this->response->setOutput( $resp );
	}

	/**
	 * Export Stripe plan to OpenCart action
	 * @return void
	 */
	public function plan_export() {
		$resp = '';

		try {
			$sp_plan = isset( $this->request->request['plan'] ) ? $this->request->request['plan'] : null;

			if ( ! is_null( $sp_plan ) ) {
				$count = 0;

				while( preg_match( '/(&quot;|&amp;)/', $sp_plan ) && $count++ < 10 ) {
					$sp_plan = html_entity_decode( $sp_plan );
				}

				$sp_plan = trim( $sp_plan, '" ' );
				@$json = json_decode( $sp_plan );

				if ( $json && is_object( $json ) ) {
					$sp_plan = $json;
				}
			}

			if ( is_null( $sp_plan ) ) {
				$mess = $this->a->__( 'Plan\'s object is missing' );
				$this->a->log( $mess, $this->a->log_error_flag );
				throw new Advertikon\Exception( $mess );
			}

			if ( $this->model->is_plan_exists( $sp_plan ) ) {
				$mess = $this->a->__( 'Failed to export plan: it was already exported' );
				$this->a->log( $mess, $this->a->log_error_flag );
				throw new Advertikon\Exception( $mess );

			} else {
				$this->load->model( 'catalog/recurring' );
				$oc_plan_id = $this->model_catalog_recurring->addRecurring(
					$this->model->convert_sp_plan_to_oc( $sp_plan )
				);

				if ( $oc_plan_id ) {
					$resp =  sprintf(
						$this->stripe_plan(),
						'',
						$sp_plan->name,
						$sp_plan->id,
						$this->a->get_stripe_plan_string( $sp_plan )
					) . 'success';

				} else{
					$mess = $this->a->__( 'Failed to export plan' );
					$this->a->log( $mess, $this->a->log_error_flag );
					throw new Advertikon\Exception( $mess );
				}
			}
			
		} catch( Advertikon\Exception $e ) {
			$resp = $e->getMessage() . 'error';

		} catch( Exception $e ) {
			$resp = $e->getMessage() . 'error';
		}

		$this->response->setOutput( $resp );
	}

	/**
	 * Edit profile action
	 * @return void
	 */
	public function profile_edit() {
		$resp = array();
		$properties = isset( $this->request->request['properties'] ) ? $this->request->request['properties'] : null;

		try {
			if ( ! $properties || ! is_array( $properties ) ) {
				$mess = $this->a->__( 'Profile properties are missing' );
				$this->a->log( $mess, $this->a->log_error_flag );
				throw new Advertikon\Exception( $mess );
			}

			if ( ! $properties['name'] ) {
				$mess = $this->a->__( 'Profile\'s name is mandatory' );
				$this->a->log( $mess, $this->$a->log_error_flag );
				throw new Advertikon\Exception( $mess );
			}

			$profile_res = new Advertikon\Stripe\Resource\Profile();

			if ( isset( $properties['id'] ) ) {
				$profile_res->load( $properties['id'] );
			}

			foreach( $properties as $p => $val ) {
				if ( $profile_res->is_protected_field( $p ) ) {
					continue;
				}

				$profile_res->{$p} = $val;
			} 

			$profile_res->save();
			$resp['success'] = $this->a->__( 'Profile has been modified' );
			$resp['id'] = $profile_res->id;

		} catch( Advertikon\Exception $e ) {
			$resp['error'] = $e->getMessage();
		}

		$this->response->setOutput( json_encode( $resp ) );
	}

	/**
	 * Delete profile action
	 * @return void
	 */
	public function profile_delete() {
		$ret = array();
		$profile_id = isset( $this->request->request['profile_id'] ) ? $this->request->request['profile_id'] : '';

		try {
			if ( ! $profile_id ) {
				$mess = $this->a->__( 'Profile\'s ID is missing' );
				$this->a->log( $mess, $this->$a->log_error_flag );
				throw new Advertikon\Exception( $mess );
			}

			$profile_res = new Advertikon\Stripe\Resource\Profile( $profile_id );

			if ( ! $profile_res->is_exists() ) {
				$mess = $this->a->__( 'Profile doesn\'t exist' );
				$this->a->log( $mess, $this->a->log_error_flag );
				throw new Advertikon\Exception( $mess );
			}

			$profile_res->delete();
			$ret['success'] = $this->a->__( 'Profile has been deleted' );

		} catch( Advertikon\Exception $e ) {
			$ret['error'] = $e->getMessage();
		}

		$this->response->setOutput( json_encode( $ret ) );
	}

	/**
	 * Map profile to OC recurring plan action
	 * @return void
	 */
	public function profile_map() {
		$resp = array();

		$profile_id = isset( $this->request->request['profile_id'] ) ?
			$this->request->request['profile_id'] : null;
		$recurring_id = isset( $this->request->request['recurring_id'] ) ?
			$this->request->request['recurring_id'] : null;

		try {
			if ( ! $profile_id ) {
				$mess = $this->a->__( 'Profile\'s ID is missing' );
				$this->a->log( $mess, $this->$a->log_error_flag );
				throw new Advertikon\Exception( $mess );
			}

			if ( ! $recurring_id ) {
				$mess = $this->a->__( 'Recurring profile\'s ID is missing' );
				$this->a->log( $mess, $this->$a->log_error_flag );
				throw new Advertikon\Exception( $mess );
			}

			$profile_res = new Advertikon\Stripe\Resource\Profile();
			$profile_res->add_mapping( $recurring_id, $profile_id );
			$resp['success'] = $this->a->__( 'Profile has been mapped' );

		} catch( Advertikon\Exception $e ) {
			$resp['error'] = $e->getMessage();
		}

		$this->response->setOutput( json_encode( $resp ) );
	}

	/**
	 * Extension's installation method
	 * @return void
	 */
	public function install(){
		$this->model->create_tables();
	}

	/**
	 * Extension\'s uninstallation method
	 * @return void
	 */
	public function uninstall() {
		if ( $this->a->config( 'uninstall_clear_db' ) ) {
			$this->a->remove_db();
		}

		if ( $this->a->config( 'uninstall_clear_settings' ) ) {
			$this->clear_settings();
		}
	}


	/**
	 * Removes extension settings
	 * @return void
	 */
	protected function clear_settings() {
		$this->a->q( array(
			'table' => 'setting',
			'query' => 'delete',
			'where' => array(
				'field'     => 'code',
				'operation' => '=',
				'value'     => 'adk_stripe',
			),
		) );
	}

	/**
	 * Order details action
	 * @return void
	 */
	public function ordershow() {
		$this->response->setOutput( $this->order() );
	}

	/**
	 * Auto-complete recurring profiles action
	 * @return void
	 */
	public function recurring_autocomplete() {
		
		$query = isset( $this->request->request['query'] ) ? $this->request->request['query'] : '';
		$data = isset( $this->request->request['data'] ) ? $this->request->request['data'] : '';

		if ( $data ) {
			$count = 0;

			while( preg_match( '/(&quot;|&amp;)/', $data ) && $count++ < 10 ) {
				$data = html_entity_decode( $data );
			}

			$data = trim( $data, '" ' );
			$json = @json_decode( $data );
		}
		if ( $json ) {
			$data = $json;
		}

		$list = $this->model->get_matching_oc_plans( $data, $query );
		$this->response->setOutput( json_encode( $list ) );
	}

	/**
	 * Display order details at admin area
	 * @return string
	 */
	public function order() {
		try {

			$order = $this->a->get_order_model();
			$order_id = $this->request->get['order_id'];
			$token = $this->session->data['token'];
			$order_info = $order->getOrder( $order_id );

			$additional_data = $this->a->get_custom_field( $order_id );

			if( isset( $additional_data->account ) ) {
				$this->a->set_account( $additional_data->account );
			}

			if ( ! isset( $order_info['order_status_id'] ) ) {
				return 'Order is missing';
			}

			if( ! empty( $additional_data->charge ) ) {
				$charge = $additional_data->charge;

			} else {
				return 'Charge is missing';
			}

			if ( isset( $charge->bogus ) ) {
				$r = $this->a->q( array(
					'table' => 'order_recurring',
					'query' => 'select',
					'where' => array(
						'field'     => 'order_id',
						'operation' => '=',
						'value'     => $order_id,
					),
				) );

				return $this->a->__( 'Charge was not created for this order, since it\'s a part of <a href="%s" target="_blank">recurring order</a>', $this->a->u( 'sale/recurring/info', array( 'order_recurring_id' => $r['order_recurring_id' ] ) ) );
			}

			if (
				( ! $charge->refunded || ! $charge->captured ) &&
				$charge->created < ( time() - 60 * 60 * 24 * 7 )
			) {
				$this->a->refresh_charge_info( $order_id );
				$charge = $this->a->get_custom_field( $order_id )->charge;
			}

			$card_image = null;
			$address_line1_check = 'unavailable';
			$address_zip_check = 'unavailable';
			$cvc_check = 'unavailable';
			$last4 = null;
			$card_type = null;

			if ( isset( $additional_data->charge->source ) ) {
				$source = $additional_data->charge->source;
				$brand = isset( $source->brand ) ? $source->brand : '';
				$card_image = $this->a->get_brand_image( $brand );
				$last4 = isset( $source->last4 ) ? $source->last4 : '';
				$address_line1_check = isset( $source->address_line1_check ) ? $source->address_line1_check : '';
				$address_zip_check = isset( $source->address_zip_check ) ? $source->address_zip_check : '';
				$cvc_check = isset( $source->cvc_check ) ? $source->cvc_check : '';
				$card_type = isset( $source->funding ) ? $source->funding : '';
			}

			$data = array(
				'model' 			  => $this->model,
				'order' 			  => $charge,
				'order_url'			  => $this->a->u()->url( 'ordershow', array( 'order_id' => $order_id ) ),
				'capture_url'		  => $this->a->u()->url( 'capture' ),
				'refresh_url'		  => $this->a->u()->url( 'refresh' ),
				'refund_url'		  => $this->a->u()->url( 'refund' ),
				'history_url'		  => $this->a->u()->catalog_url() . 'index.php?route=api/order/history&order_id=' . $order_id,
				'order_id'			  => $order_id,
				'token'				  => $token,
				'captured_status'	  => $this->a->config( 'status_captured' ),
				'refunded_status'	  => $this->a->config( 'status_voided' ),
				'current_status'	  => $order_info['order_status_id'],
				'card_image'          => $card_image,
				'address_line1_check' => $address_line1_check,
				'address_zip_check'   => $address_zip_check,
				'cvc_check'           => $cvc_check,
				'last4'               => $last4,
				'card_type'           => $card_type,
			);

			$data['a'] = $this->a;

			$return =  $this->load->view(
				$this->a->get_template( $this->a->type . '/advertikon/stripe/order'	),
				$data
			);

		} catch( Advertikon\Stripe\Exception $e ) {
			$return  = sprintf( '<b>%s</b>: %s', $this->a->__( 'Unable to display order data' ), $e->getMessage() );

		} catch( Exception $e ) {
			trigger_error( $e->getMessage() );
			$return  = sprintf(
				'%s: <b>%s</b>',
				$this->a->__( 'Unable to display order data due to' ),
				$this->a->__( 'Script error' )
			);
		}

		return $return;
	}

	/**
	 * Capture payment from admin area action
	 * @return void
	 */
	public function capture() {
		$resp = array();

		try {
			$order_model = $this->a->get_order_model();
			$order_id = isset( $this->request->get['order_id'] ) ? $this->request->get['order_id'] : null;

			$custom_field = $this->a->get_custom_field( $order_id );
			$charge = $custom_field->charge;
			$this->a->set_account( $custom_field->account );

			$amount = isset( $this->request->get['amount'] ) ? $this->request->get['amount'] : '';

			if ( ! $order_id ) {
				$mess = $this->a->__( 'Order ID is missing' );
				$this->a->log( $mess, $this->log_error_flag );
				throw new Advertikon\Exception( $mess );
			}
			if ( ! $charge ) {
				$mess = $this->a->__( 'Charge is missing' );
				$this->a->log( $mess, $this->log_error_flag );
				throw new Advertikon\Exception( $mess );
			}

			if ( $charge->captured ) {
				$mess = $this->a->__( 'Charge has already been captured' );
				$this->a->log( $mess, $this->log_error_flag );
				throw new Advertikon\Exception( $mess );
			}

			if ( ! is_numeric( $amount ) ) {
				$mess = $this->a->__( 'Amount to be captured is not a number' );
				$this->a->log( $mess, $this->log_error_flag );
				throw new Advertikon\Exception( $mess );
			}

			$cents = $this->a->amount_to_cents( $amount, $charge->currency );

			if ( $cents > $charge->amount ) {
				$mess = $this->a->__(
					'Amount to capture may not exceed %s $s',
					$this->a->amount_to_cents( $charge->amount, $charge->currency ),
					$charge->currency
				);

				$this->a->log( $mess, $this->log_error_flag );
				throw new Advertikon\Exception( $mess );
			}

			if ( $charge = $this->a->capture_charge( $charge, $cents, $order_id ) ) {

				$this->response->setOutput( $this->order() );
				return;

			} else {
				$resp['error'] = 'Undefined error. Charge can\'t be captured';
			}

		} catch( Advertikon\Stripe\Exception $e ) {
			$resp['error'] = $e->getMessage();

		} catch( Stripe\Error\Base $e ) {
			$resp['error'] = $e->getMessage();

		} catch( Exception $e ) {
			$this->log( $e->getMessage(), $this->log_error_flag );
			$resp['error'] = 'Error occurred. Charge can\'t be captured';

		}

		$this->response->setOutput( json_encode( $resp ) );
	}

	/**
	 * Refresh charge details for specific order on admin area
	 * @return void
	 */
	public function refresh() {
		$resp = array();

		try {
			$order_id = isset( $this->request->get['order_id'] ) ? $this->request->get['order_id'] : null;

			if ( ! $order_id ) {
				$mess = $this->a->__( 'Order ID is missing' );
				$this->a->log( $mess, $this->log_error_flag );
				throw new Advertikon\Exception( $mess );
			}
			
			$order_model = $this->a->get_order_model();
			$this->a->set_account( $this->a->get_custom_field( $order_id )->account );

			if ( $this->a->refresh_charge_info( $order_id ) ) {
				$this->response->setOutput( $this->order() );

				return;

			} else {
				$mess = $this->a->__( 'Failed to refresh data' );
				$this->a->log( $mess, $this->log_error_flag );
				throw new Advertikon\Stripe\Exception( $mess );
			}

		} catch( Advertikon\Stripe\Exception $e ) {
			$resp['error'] = $e->getMessage();

		} catch( Stripe\Error\Base $e ) {
			$resp['error'] = $e->getMessage();

		} catch( Exception $e ) {
			$resp['error'] = 'Script error';

		}

		$this->response->setOutput( json_encode( $resp ) );
	}

	/**
	 * Refund payment from admin area action
	 * @return void
	 */
	public function refund() {
		$resp = array();

		try {

			$order_id = isset( $this->request->get['order_id'] ) ? $this->request->get['order_id'] : null;

			if ( ! $order_id ) {
				$mess = $this->a->__( 'Order ID is missing' );
				$this->a->log( $mess, $this->log_error_flag );
				throw new Advertikon\Exception( $mess );
			}

			$amount = isset( $this->request->get['amount'] ) ? $this->request->get['amount'] : '';
			
			if ( ! is_numeric( $amount ) ) {
				$mess = $this->a->__( 'Amount to refund need to be numeric' );
				$this->a->log( $mess, $this->log_error_flag );
				throw new Advertikon\Exception( $mess );
			}

			$payment_data = $this->a->get_custom_field( $order_id );
			$charge = $payment_data->charge;

			if ( ! $charge ) {
				$mess = $this->a->__( 'Charge is missing' );
				$this->a->log( $mess, $this->log_error_flag );
				throw new Advertikon\Exception( $mess );
			}
			
			if ( $charge->refunded ) {
				$mess = $this->a->__( 'Charge has already been refunded' );
				$this->a->log( $mess, $this->log_error_flag );
				throw new Advertikon\Exception( $mess );
			}

			$this->a->set_account( $payment_data->account );

			$cents = $this->a->amount_to_cents( $amount, $charge->currency );

			if ( $cents > ( $charge->amount - $charge->amount_refunded ) ) {
				$mess = $this->a->__(
					'Amount to refund may not exceed %s $s',
					$this->a->amount_to_cents( $charge->amount - $charge->amount_refunded , $charge->currency ),
					$charge->currency
				);

				$this->a->log( $mess, $this->log_error_flag );
				throw new Advertikon\Exception( $mess );
			}

			if ( $this->a->refund_charge( $charge->id, $cents, $order_id ) ) {
				$this->response->setOutput( $this->order() );
				return;

			} else {
				$resp['error'] = 'Undefined error. Charge can\'t be refunded';
			}

		} catch( Advertikon\Stripe\Exception $e ) {
			$resp['error'] = $e->getMessage();

		} catch( Stripe\Error\Base $e ) {
			$resp['error'] = $e->getMessage();

		}

		$this->response->setOutput( json_encode( $resp ) );
	}

	/**
	 * Get stripe account template for administrative area
	 * @param array $data Template data
	 * @param string $teomlate_name Template name
	 * @return String
	 */
	public function get_stripe_account_field( $data = array(), $template_name = null ) {
		$templ = false;

		if ( is_null( $template_name ) ) {
			$template_name = '{template_name}';
			$templ = true;
		}

		extract( $data );

		$data['default'] = $template_name === 'default';
		$option = new Advertikon\Stripe\Option();
		$name_prefix = "account-" . $template_name;

		//Account name field
		$field = 'account_name';
		$id = $name_prefix . '-' . $field;
		$name = $this->a->build_name( $id, '-' );

		$data['account_name'] = $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Account name' ),
			'element' => $this->a->r( array(
				'type'        => 'text',
				'id'          => $id,
				'name'        => $templ ? '' : $name,
				'class'       => 'form-control account_name',
				'placeholder' => $this->a->__( 'Account name' ),
				'value'       => $this->a->get_value_from_post( $name, 'New' ),
				'custom_data' => 'data-name="' . $this->a->prefix_name( $name ) . '"',
			) ),
			'error'   => isset( $data['error_input_errors']['account'][ $template_name ]['account_name'] ) ?
				$data['error_input_errors']['account'][ $template_name ]['account_name'] : null,
		) );

		// Account currency field
		$field = 'account_currency';
		$id = $name_prefix . '-' . $field;
		$name = $this->a->build_name( $id, '-' );

		$data['account_currency'] = $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Account currency' ),
			'element' => $this->a->r( array(
				'type'        => 'select',
				'id'          => $id,
				'name'        => $templ ? '' : $name,
				'class'       => 'form-control account-currency',
				'active'      => $this->a->get_value_from_post( $name ),
				'value'       => $option->currency_code(),
				'custom_data' => 'data-name="' . $this->a->prefix_name( $name ) . '"',
			) ), 
			'tooltip' => $this->a->__( 'Front end currency, associated with this Stripe account' ),
			'error'   => isset( $data['error_input_errors']['account'][ $template_name ]['account_currency'] ) ?
				$data['error_input_errors']['account'][ $template_name ]['account_currency'] : null,
		) );

		// Test secret key field
		$field = 'test_secret_key';
		$id = $name_prefix . '-' . $field;
		$name = $this->a->build_name( $id, '-' );

		$data['test_secret_key'] = $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Test secret key' ),
			'element' => $this->a->r( array(
				'type'        => 'text',
				'id'          => $id,
				'name'        => $templ ? '' : $name,
				'class'       => 'form-control',
				'placeholder' => $this->a->__( 'Test secret key' ),
				'value'       => $this->a->get_value_from_post( $name ),
				'custom_data' => 'data-name="' . $this->a->prefix_name( $name ) . '"',
			) ), 
			'error'   => isset( $data['error_input_errors']['account'][ $template_name ]['test_secret_key'] ) ?
				$data['error_input_errors']['account'][ $template_name ]['test_secret_key'] : null,
		) );

		// Test public key field
		$field = 'test_public_key';
		$id = $name_prefix . '-' . $field;
		$name = $this->a->build_name( $id, '-' );

		$data['test_public_key'] = $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Test publishable key' ),
			'element' => $this->a->r( array(
				'type'        => 'text',
				'id'          => $id,
				'name'        => $templ ? '' : $name,
				'class'       => 'form-control',
				'placeholder' => $this->a->__( 'Test publishable key' ),
				'value'       => $this->a->get_value_from_post( $name ),
				'custom_data' => 'data-name="' . $this->a->prefix_name( $name ) . '"',
			) ), 
			'error'   => isset( $data['error_input_errors']['account'][ $template_name ]['test_public_key'] ) ?
				$data['error_input_errors']['account'][ $template_name ]['test_public_key'] : null,
		) );

		// Live secret key field
		$field = 'live_secret_key';
		$id = $name_prefix . '-' . $field;
		$name = $this->a->build_name( $id , '-');

		$data['live_secret_key'] = $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Live secret key' ),
			'element' => $this->a->r( array(
				'type'        => 'text',
				'id'          => $id,
				'name'        => $templ ? '' : $name,
				'class'       => 'form-control',
				'placeholder' => $this->a->__( 'Live secret key' ),
				'value'       => $this->a->get_value_from_post( $name ),
				'custom_data' => 'data-name="' . $this->a->prefix_name( $name ) . '"',
			) ), 
			'error'   => isset( $data['error_input_errors']['account'][ $template_name ]['live_secret_key'] ) ?
				$data['error_input_errors']['account'][ $template_name ]['live_secret_key'] : null,
		) );

		// Live public key field
		$field = 'live_public_key';
		$id = $name_prefix . '-' . $field;
		$name = $this->a->build_name( $id, '-' );

		$data['live_public_key'] = $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Live publishable key' ),
			'element' => $this->a->r( array(
				'type'        => 'text',
				'id'          => $id,
				'name'        => $templ ? '' : $name,
				'class'       => 'form-control',
				'placeholder' => $this->a->__( 'Live publishable key' ),
				'value'       => $this->a->get_value_from_post( $name ),
				'custom_data' => 'data-name="' . $this->a->prefix_name( $name ) . '"',
			) ), 
			'error'   => isset( $data['error_input_errors']['account'][ $template_name ]['live_public_key'] ) ?
				$data['error_input_errors']['account'][ $template_name ]['live_public_key'] : null,
		) );

		$data['a'] = $this->a;

		return $this->load->view(
			$this->a->get_template( $this->a->type . '/advertikon/stripe/account' ), $data
		);
	}

	/**
	 * Tests TLS 1.2 support
	 * @return void
	 */
	public function check_tls() {

		$resp = array();

		try {
			$ch = curl_init();
			$f = fopen( 'php://temp', 'r+' );

			$opts = array(
				CURLOPT_URL            => "https://api-tls12.stripe.com",
				CURLOPT_HEADER         => true,
				CURLOPT_CERTINFO       => true,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_NOBODY         => true,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_VERBOSE        => true,
				CURLOPT_STDERR         => $f,
			);

			curl_setopt_array( $ch, $opts );
			$body = curl_exec( $ch );

			if ( ! $body ) {
				throw new Advertikon\Exception( $this->a->__( 'Your server doesn\'t support TLS v 1.2' ) );
			}

			if ( curl_errno( $ch ) ) {
				throw new Advertikon\Exception( curl_error( $ch ) );
			}

			curl_close( $ch );
			rewind( $f );
			$cont = fread( $f, 1024 );
			fclose( $f );

			// if ( ! preg_match( '/ssl connection using ([A-Za-z0-9._]+)\s/im', $cont, $m ) ) {
			// 	throw new Advertikon\Exception( $this->a->__( 'Unknown protocol' ) );
			// }

			$resp['ssl'] = $m[1];
			$resp['success'] = $this->a->__( 'Your hosting supports TLS v 1.2' );
			
		} catch ( Advertikon\Exception $e ) {
			$resp['error'] = $e->getMessage();
		}

		$this->response->setOutput( json_encode( $resp ) );
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
		$data['a']                   = $this->a;

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

		} catch( \Exception $e ) {
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

		} catch( Advertikon\Exception $e ) {
			$this->response->setOutput( $e->getMessage() . 'error' );

		} catch( Exception $e ) {
			$this->response->setOutput( $e->getMessage() );
		}
	}

	/**
	 * Get ticket button action
	 * @return void
	 */
	public function ticket_button() {
		$this->response->setOutput( $this->a->ticket_button() );
	}

	/**
	 * Load JS action
	 * @return void
	 */
	public function compress() {
		echo $this->a->compress();die;
	}
}

