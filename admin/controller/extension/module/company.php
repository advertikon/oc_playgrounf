<?php
/**
 * Admin Controller
 *
 * @package Stock distribution
 * @version 0.0.7
 * 
 * @source admin/view/javascript/advertikon/advertikon.js
 * 
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
 * @source system/library/advertikon/company/*
 * @source system/library/advertikon/console.php
 */

class ControllerExtensionModuleCompany extends Controller {
	public $a = null;
	public $model = null;

	public function __construct( $registry ) {
		parent::__construct( $registry );

		$this->a = Advertikon\Company\Advertikon::instance();
		$this->load->model( $this->a->full_name );
		$this->model = $this->{'model_' . str_replace( '/', '_', $this->a->full_name )};
	}

	/**
	 * index action
	 * @return void
	 */
	public function index() {
		$data = array();

		try {
			if ( $this->request->server['REQUEST_METHOD'] == 'POST' && $this->model->validate_configs() ) {
				$this->load->model( 'setting/setting' );

				$settings = $this->model_setting_setting->editSetting(
					$this->a->code,
					array_merge(
						$this->model_setting_setting->getSetting( $this->a->code ),
						$this->request->post
					)
				);

				$this->session->data['success'] = $this->a->__( 'Settings has been successfully changed' );
				$this->response->redirect( $this->a->u()->url() );
			}

		} catch( \Advertikon\Exception $e ) {
			$data['error'] = $e->getMessage();
		}

		$this->document->addScript( $this->a->u()->admin_url() . 'view/javascript/advertikon/advertikon.js' );
		$this->document->addStyle( $this->a->u()->admin_url() . 'view/stylesheet/advertikon/advertikon.css' );

		$name = $this->a->__( 'Companies' );

		$this->document->setTitle( $name );
		$data['name'] = $name;

		$extension_route = version_compare( VERSION, '2.3.0.0', '>' ) ?
			'extension/extension' : 'extension/' . $this->a->type;

		if ( isset( $this->session->data['success'] ) ) {
			$data['success'] = $this->session->data['success'];
			unset( $this->session->data['success'] );
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link(
				'common/dashboard',
				'token=' . $this->session->data['token'],
				'SSL'
			),
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->a->__( 'Modules' ),
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
			$this->a->type . '/' . $this->a->code,
			'token=' . $this->session->data['token'],
			'SSL'
		);

		$data['cancel'] = $this->url->link(
			'extension/' . ( version_compare( VERSION, '2.3.0.0', '>=' ) ? 'extension' : 'module' ),
			'token=' . $this->session->data['token'],
			'SSL'
		);

		$data['button_cancel'] = $this->a->__( 'Cancel' );
		$data['button_save'] = $this->a->__( 'Save' );

		$data['compatibility'] = $this->a->check_compatibility();

		$data['status'] = $this->a->r()->render_form_group( array(
			'label' => $this->a->__( 'Status' ),
			'element' => $this->a->r( array(
				'type'   => 'select',
				'value'  => array( $this->a->__( 'Disabled' ), $this->a->__( 'Enabled' ) ),
				'active' => $this->a->config( 'status' ),
				'name'   => 'status',
				'class'  => 'form-control'
			) ),
		) );

		$data['version'] = \Advertikon\Company\Advertikon::get_version();
		$data['header'] = $this->load->controller( 'common/header' );
		$data['footer'] = $this->load->controller( 'common/footer' );
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller( 'common/footer' );
		$data['a'] = $this->a;


		$data['locale'] = json_encode( array(

			// Common stuff
			'networkError'              => $this->a->__( 'Network error' ),
			'parseError'                => $this->a->__( 'Unable to parse server response string' ),
			'undefServerResp'           => $this->a->__( 'Undefined server response' ),
			'serverError'               => $this->a->__( 'Server error' ),
			'sessionExpired'            => $this->a->__( 'Current session has expired' ),
			'modalHeader'               => $name,
			'yes'                       => $this->a->__( 'Yes' ),
			'no'                        => $this->a->__( 'No' ),
			'clipboard'                 => '',
		) );

		$data['a'] = $this->a;
		$this->response->setOutput( $this->load->view( $this->a->get_template( $this->a->full_name ), $data ) );
	}

	/**
	 * Extension's installation method
	 * @return void
	 */
	public function install(){
		$this->model->add_tables();
	}

	/**
	 * Extension\'s uninstallation method
	 * @return void
	 */
	public function uninstall() {
		$this->a->remove_db();
	}
}

