<?php
/**
 * Admin Controller
 *
 * @package Stock distribution
 * @version 0.0.7
 * 
 * @source admin/view/javascript/advertikon/advertikon.js
 * @#source admin/view/javascript/advertikon/company/*
 * 
 * @#source admin/view/stylesheet/advertikon/company/*
 * @source admin/view/stylesheet/advertikon/advertikon.css
 */

class ControllerExtensionModuleCompany extends Controller {
	public $a = null;
	public $model = null;
	protected $error = array();

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
	public function companies() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->a->u( 'companies' );
			$this->response->redirect($this->url->link('account/login', '', 'SSL'));
		}

		$this->document->setTitle($this->a->__( 'Companies' ) );

		$data['breadcrumbs'][] = array(
			'text' => $this->a->__( 'Home' ),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->a->__( 'Account' ),
			'href' => $this->url->link('account/account', '', 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->a->__( 'Companies' ),
			'href' => $this->a->u( 'companies' )
		);

		$data['heading_title'] = $this->a->__( 'Companies' );

		$data['text_address_book'] = $this->a->__( 'Companies list');
		$data['text_empty'] = $this->a->__( 'List is empty' );

		$data['button_new_address'] = $this->a->__( 'Add company' );
		$data['button_edit'] = $this->a->__( 'Edit company' );
		$data['button_delete'] = $this->a->__( 'Delete company' );
		$data['button_back'] = $this->a->__( 'Back' );

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$data['addresses'] = array();

		$results = $this->a->get_companies_by_customer( $this->customer->getId() );

		foreach ($results as $result) {
			$data['addresses'][] = array(
				'address_id' => $result['id'],
				'address'    => $this->a->format_company( $result ),
				'update'     => $this->a->u( 'edit', array( 'id' => $result['id'] ) ),
				'delete'     => $this->a->u( 'delete', array( 'id' => $result['id'] ) )
			);
		}

		$data['add'] = $this->a->u( 'edit' );
		$data['back'] = $this->url->link('account/account', '', 'SSL');

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$data['a'] = $this->a;
		$this->response->setOutput( $this->load->view( $this->a->get_template( $this->a->full_name . '/companies.tpl' ), $data ) );
	}

	public function edit() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->a->u( 'edit' );
			$this->response->redirect($this->url->link('account/login', '', 'SSL'));
		}

		$this->document->setTitle( $this->a->__( 'Edit company' ) );

		if ( $this->a->post( 'company' ) && $this->validate_form() ) {
			$c = $this->a->post( 'company' );

			$data = array(
				'name'           => $c['name'],
				'vat'            => $c['vat'],
				'reg'            => $c['reg'],
				'representative' => $c['representative'],
				'address'        => $c['address'],
				'zone'           => $c['zone'],
				'country'        => $c['country'],
				'city'           => $c['city'],
				'bank'           => $c['bank'],
				'iban'           => $c['iban'],
				'phone'          => $c['phone'],
			);
			if ( isset( $this->request->request['id'] ) ) {
				$data['id'] = $this->request->get['id'];
			}

			$this->a->save_company( $data, $this->customer->getId() );
			$this->session->data['success'] = $this->a->__( 'Company details were modified' );

			$this->response->redirect( $this->a->u( 'companies' ) );
		}

		$this->get_form();
	}

	public function validate_form() {
		$json = array();
		$adk = $this->a;

		if ( ! trim( $this->request->post['company']['name'] ) ) {
			$json['error']['company_name'] = $adk->__( 'Company name is required' );
		}

		if ( ! trim( $this->request->post['company']['vat'] ) ) {
			$json['error']['company_vat'] =  $adk->__( 'Company VAT number is required' );
		}

		if ( ! trim( $this->request->post['company']['reg'] ) ) {
			$json['error']['company_reg'] =  $adk->__( 'Company registration number is required' );
		}

		if ( ! trim( $this->request->post['company']['representative'] ) ) {
			$json['error']['company_representative'] =  $adk->__( 'Company legal representative is required' );
		}

		if ( ! trim( $this->request->post['company']['address'] ) ) {
			$json['error']['company_address'] =  $adk->__( 'Company address is required' );
		}

		if ( ! trim( $this->request->post['company']['city'] ) ) {
			$json['error']['company_city'] =  $adk->__( 'City name is required' );
		}

		if ( $this->request->post['company']['country'] === '' ) {
			$json['error']['company_country'] =  $adk->__( 'Cuntry is required' );
		}

		if (
			! isset( $this->request->post['company']['zone'] ) ||
			$this->request->post['company']['zone'] == '' ||
			! is_numeric($this->request->post['company']['zone'] )
		) {
			$json['error']['company_zone'] = $adk->__( 'Country zone is required' );
		}

		if ( ! trim( $this->request->post['company']['bank'] ) ) {
			$json['error']['company_bank'] = $adk->__( 'Bank name name is required' );
		}

		if ( ! trim( $this->request->post['company']['iban'] ) ) {
			$json['error']['company_iban'] = $adk->__( 'Bank account is required' );
		}

		if ( ! trim( $this->request->post['company']['phone'] ) ) {
			$json['error']['company_phone'] = $adk->__( 'Phone number is required' );
		}

		$this->error = $json;

		return ! $json;
	}

	protected function get_form() {
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->a->__( 'Home' ),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->a->__( 'Account' ),
			'href' => $this->url->link('account/account', '', 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->a->__( 'Companies' ),
			'href' => $this->a->u( 'companies' )
		);

		$data['heading_title'] = $this->a->__( 'Company' );

		if (!isset($this->request->get['id'])) {
			$data['breadcrumbs'][] = array(
				'text' => $this->a->__( 'Company' ),
				'href' => $this->a->u( 'edit' )
			);
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->a->__( 'Company' ),
				'href' => $this->a->u( 'edit', array( 'id' => $this->request->get['id'] ) )
			);
		}

		$data['heading_title'] = $this->a->__( 'Company' );

		$data['text_edit_address'] = $this->a->__( 'Edit  company' );
		$data['text_yes'] = $this->a->__( 'Yes' );
		$data['text_no'] = $this->a->__( 'No' );
		$data['text_select'] = $this->a->__( 'Select' );
		$data['text_none'] = $this->a->__( 'None' );
		$data['text_loading'] = $this->a->__( 'Loading' );

		$data['button_continue'] = $this->a->__( 'Save' );
		$data['button_back'] = $this->a->__( 'Back' );
		
		if (!isset($this->request->get['id'])) {
			$data['action'] = $this->a->u( 'edit' );
		} else {
			$data['action'] = $this->a->u( 'edit', array( 'id' => $this->request->get['id'] ) );
		}

		if (isset($this->request->get['id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$company = $this->a->get_company_by_id($this->request->get['id']);
		}

		$data['company_name'] = isset( $company['name'] ) ? $company['name'] : '';
		$data['company_vat'] = isset( $company['name'] ) ? $company['vat_no'] : '';
		$data['company_reg'] = isset( $company['name'] ) ? $company['reg_no'] : '';
		$data['company_representative'] = isset( $company['representative'] ) ? $company['representative'] : '';
		$data['company_address'] = isset( $company['address_line_1'] ) ? $company['address_line_1'] : '';
		$data['company_zone_id'] = isset( $company['zone_id'] ) ? $company['zone_id'] : '';
		$data['company_country_id'] = isset( $company['country_id'] ) ? $company['country_id'] : '';
		$data['company_city'] = isset( $company['city'] ) ? $company['city'] : '';
		$data['company_bank'] = isset( $company['bank'] ) ? $company['bank'] : '';
		$data['company_iban'] = isset( $company['iban'] ) ? $company['iban'] : '';
		$data['company_phone'] = isset( $company['phone'] ) ? $company['phone'] : '';

		$this->load->model('localisation/country');

		$data['countries'] = $this->model_localisation_country->getCountries();

		$data['back'] = $this->a->u( 'companies' );

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$data['error'] = isset( $this->error['error'] ) ? $this->error['error'] : null;;

		$data['adk'] = $this->a;
		$this->response->setOutput( $this->load->view( $this->a->get_template( $this->a->full_name . '/company.tpl' ), $data ) );
	}

	public function delete() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/address', '', 'SSL');
			$this->response->redirect($this->url->link('account/login', '', 'SSL'));
		}

		if ( isset( $this->request->get['id'] ) ) {
			$this->a->delete( $this->request->get['id'] );
			$this->session->data['success'] = $this->a->__( 'Company data were deleted' );
			$this->response->redirect($this->a->u( 'companies' ));
		}

		$this->companies();
	}

}

