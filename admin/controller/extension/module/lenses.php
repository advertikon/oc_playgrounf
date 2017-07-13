<?php
/**
 * Admin Controller
 *
 * @author Advertikon
 * @package 
 * @version 0.00.000    
 * 
 * @source admin/view/javascript/advertikon/jquery-ui.min.js
 * @source admin/view/javascript/advertikon/iris.min.js
 * @source admin/view/javascript/advertikon/select2/*
 * @source admin/view/javascript/advertikon/summernote/*
 * @source admin/view/javascript/advertikon/advertikon.js
 * 
 * @source admin/view/stylesheet/advertikon/fa/*
 * @source admin/view/stylesheet/advertikon/jquery-ui.min.css
 * @source admin/view/stylesheet/advertikon/jquery-ui.theme.min.css
 * @source admin/view/stylesheet/advertikon/images/* jQuery-IU images
 * @source admin/view/stylesheet/advertikon/select2/*
 * @source admin/view/stylesheet/advertikon/summernote/*
 * @source admin/view/stylesheet/advertikon/advertikon.css
 * 
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
	public $a = null;
	public $model = null;

	public function __construct( $registry ) {
		parent::__construct( $registry );

		$this->a = Advertikon\XXXXXX\Advertikon::instance();
		$this->load->model( $this->a->full_name );
		$this->model = $this->{'model_' . str_replace( '/', '_', $this->a->full_name )};
	}

	/**
	 * indexAction
	 * @return void
	 */
	public function index() {
		$this->document->addScript(
			$this->a->u()->admin_url() . 'view/javascript/advertikon/jquery-ui.min.js'
		);

		$this->document->addScript(
			$this->a->u()->admin_url() . 'view/javascript/advertikon/iris.min.js'
		);

		$this->document->addScript(
			$this->a->u()->admin_url() . 'view/javascript/advertikon/select2/select2.min.js'
		);

		$this->document->addScript(
			$this->a->u()->admin_url() . 'view/javascript/advertikon/summernote/summernote.min.js'
		);

		$this->document->addScript(
			$this->a->u()->admin_url() . 'view/javascript/advertikon/advertikon.js'
		);

		$this->document->addStyle(
			$this->a->u()->admin_url() . 'view/stylesheet/advertikon/fa/css/font-awesome.min.css'
		);

		$this->document->addStyle(
			$this->a->u()->admin_url() . 'view/stylesheet/advertikon/jquery-ui.min.css'
		);

		$this->document->addStyle(
			$this->a->u()->admin_url() . 'view/stylesheet/advertikon/jquery-ui.theme.min.css'
		);

		$this->document->addStyle(
			$this->a->u()->admin_url() . 'view/stylesheet/advertikon/select2/select2.min.css'
		);

		$this->document->addStyle(
			$this->a->u()->admin_url() . 'view/stylesheet/advertikon/summernote/summernote.css'
		);

		$this->document->addStyle( 
			$this->a->u()->admin_url() . 'view/stylesheet/advertikon/advertikon.css'
		);

		$this->document->setTitle( $this->a->__( 'XXXXXXXXXX' ) );

		$extension_route = version_compare( VERSION, '2.3.0.0', '>' ) ?
			'extension/extension' : 'extension/' . $this->a->type;

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
			'text' => $this->language->get('xxxxxxxx'),
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

		$id = 'template-adk_stripe_template_error_order';
		$name = $this->a->build_name( $id, '-' );
		$data['template'] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Payment error template' ),
			'label_for' => $name,
			'tooltip'   => $this->a->__( 'The email message\'s template to send to the store owner on payment failure. List of supported shortcodes: %s', implode( ', ', $shortcode->list_of_supported() ) ),
			'element'   => array(
				'type'        => 'textarea',
				'name'        => $name,
				'class'       => 'form-control summertime',
				'value'       => $this->a->get_value_from_post( $name ),
				'id'          => $name,
			),
			'error'       => isset( $resp['input_errors']['template']['adk_stripe_template_errro_order'] ) ?
				$resp['input_errors']['template']['adk_stripe_template_errro_order'] : null,
		) );

		$data['locale'] = json_encode( array(

			// Common stuff
			'networkError'              => $this->a->__( 'Network error' ),
			'parseError'                => $this->a->__( 'Unable to parse server response string' ),
			'undefServerResp'           => $this->a->__( 'Undefined server response' ),
			'serverError'               => $this->a->__( 'Server error' ),
			'sessionExpired'            => $this->a->__( 'Current session has expired' ),
			'modalHeader'               => 'xxxxxxx',
			'yes'                       => $this->a->__( 'Yes' ),
			'no'                        => $this->a->__( 'No' ),
			'clipboard'                 => '',
			'ticketButtonUrl'           => $this->a->u()->url( 'ticket_button' ),
		) );

		$data['a']        = $this->a;

		$this->response->setOutput(
			$this->load->view( $this->a->get_template( $this->a->full_name ), $data )
		);
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
		$this->a->remove_db();
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

