<?php
/**
 * Admin controller
 * @package Mail template manager
 * @author Advertikon
 * @version 0.7.2
 * 
 * @source admin/view/javascript/advertikon/jquery.ui.touch-punch.min.js
 * @source admin/view/javascript/advertikon/lodash.js
 * @source admin/view/javascript/advertikon/tinycolor-min.js
 * @source admin/view/javascript/advertikon/elfinder/*
 * @source admin/view/javascript/advertikon/jquery-ui.min.js
 * @source admin/view/javascript/advertikon/iris.min.js
 * @source admin/view/javascript/advertikon/summernote/*
 * @source admin/view/javascript/advertikon/select2/*
 * @source admin/view/javascript/advertikon/advertikon.js
 * @source admin/view/javascript/advertikon/mail/adk_mail.js
 * @source admin/view/javascript/advertikon/mail/adk_mail_common.js
 * @source admin/view/javascript/advertikon/elfinder_attachment_init.js File browser initialization script
 * @source admin/view/javascript/advertikon/chart.min.js
 * @source admin/view/stylesheet/advertikon/fileupload/js/jquery.fileupload.js
 * @source admin/view/stylesheet/advertikon/fileupload/js/jquery.fileupload-ui.js
 * @source admin/view/stylesheet/advertikon/fileupload/js/jquery.fileupload-process.js
 * @source admin/view/stylesheet/advertikon/fileupload/css/jquery.fileupload.css
 * @source admin/view/stylesheet/advertikon/jquery-ui.min.css
 * @source admin/view/stylesheet/advertikon/jquery-ui.theme.min.css
 * @source admin/view/stylesheet/advertikon/fa/*
 * @source admin/view/stylesheet/advertikon/summernote/*
 * @source admin/view/stylesheet/advertikon/select2/*
 * @source admin/view/stylesheet/advertikon/advertikon.css
 * @source admin/view/stylesheet/advertikon/mail/adk_mail.css
 * @source admin/view/stylesheet/advertikon/images/* jQuery UI icons
 * @source admin/view/stylesheet/advertikon/elfinder/*
 * @source image/social/*
 * @source catalog/view/theme/default/template/mail/adk_concise.tpl
 * @source catalog/view/theme/default/template/mail/adk_extended.tpl
 * @source catalog/view/theme/default/template/mail/adk_textual.tpl
 * @source catalog/view/theme/default/template/module/advertikon/mail/*
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
 * @source system/library/advertikon/console.php
 * @source system/library/advertikon/url.php
 * @source system/library/advertikon/mail/*
 */
class ControllerModuleAdkMail extends Controller {

	protected $model = null;
	protected $a = null;

	/**
	 * Class constructor
	 * @param Object $registry 
	 * @return void
	 */
	public function __construct( $registry ) {
		parent::__construct( $registry );
		$this->a = \Advertikon\Mail\Advertikon::instance();
		$this->load->model( $this->a->full_name );
		$this->model = $this->{'model_' . str_replace( '/', '_', $this->a->full_name )};
	}

	/**
	 * Index action
	 * @return void
	 */
	public function index() {
		if ( ! $this->user->hasPermission( 'access', $this->a->type . '/' . $this->a->code ) ) {
			die;
		}

		// JS scripts
		$this->document->addScript( HTTPS_SERVER . 'view/javascript/advertikon/jquery-ui.min.js' );
		$this->document->addScript( HTTPS_SERVER . 'view/javascript/advertikon/iris.min.js' );
		$this->document->addScript( HTTPS_SERVER . 'view/javascript/advertikon/summernote/summernote.min.js' );
		$this->document->addScript( HTTPS_SERVER . 'view/javascript/advertikon/select2/select2.min.js' );
		$this->document->addScript( HTTPS_SERVER . 'view/javascript/advertikon/jquery.ui.touch-punch.min.js' );
		$this->document->addScript( HTTPS_SERVER . 'view/javascript/advertikon/tinycolor-min.js' );
		$this->document->addScript( HTTPS_SERVER . 'view/javascript/advertikon/chart.min.js' );

		$this->document->addScript( HTTPS_SERVER . 'view/stylesheet/advertikon/fileupload/js/jquery.fileupload.js' );
		$this->document->addScript( HTTPS_SERVER . 'view/stylesheet/advertikon/fileupload/js/jquery.fileupload-ui.js' );
		$this->document->addScript( HTTPS_SERVER . 'view/stylesheet/advertikon/fileupload/js/jquery.fileupload-process.js' );

		$this->document->addScript( HTTPS_SERVER . 'view/javascript/advertikon/advertikon.js' );
		$this->document->addScript( HTTPS_SERVER . 'view/javascript/advertikon/mail/adk_mail_common.js' );
		$this->document->addScript( HTTPS_SERVER . 'view/javascript/advertikon/mail/adk_mail.js' );

		// CSS rules
		$this->document->addStyle( HTTPS_SERVER . 'view/stylesheet/advertikon/fa/css/font-awesome.min.css' );
		$this->document->addStyle( HTTPS_SERVER . 'view/stylesheet/advertikon/jquery-ui.min.css' );
		$this->document->addStyle( HTTPS_SERVER . 'view/stylesheet/advertikon/jquery-ui.theme.min.css' );
		$this->document->addStyle( HTTPS_SERVER . 'view/stylesheet/advertikon/summernote/summernote.css' );
		$this->document->addStyle( HTTPS_SERVER . 'view/stylesheet/advertikon/select2/select2.min.css' );
		$this->document->addStyle( HTTPS_SERVER . 'view/stylesheet/advertikon/fileupload/css/jquery.fileupload.css' );
		$this->document->addStyle( HTTPS_SERVER . 'view/stylesheet/advertikon/advertikon.css' );
		$this->document->addStyle( HTTPS_SERVER . 'view/stylesheet/advertikon/mail/adk_mail.css' );

		$this->model->fix();

		$shortcode = new \Advertikon\Mail\Shortcode();
		$fs = new \Advertikon\Fs();

		if ( isset( $this->error['warning'] ) ) {
			$data['error_warning'] = $this->error['warning'];

		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->a->__( 'Home' ),
			'href' => $this->url->link(
				'common/dashboard',
				'token=' . $this->session->data['token'],
				'SSL'
			)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->a->__( 'Modules' ),
			'href' => $this->url->link(
				'extension/module',
				'token=' . $this->session->data['token'],
				'SSL'
			)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get( 'heading_title' ),
			'href' => $this->url->link(
				$this->a->type . '/' . $this->a->code,
				'token=' . $this->session->data['token'],
				'SSL'
			)
		);

		$data['action'] = $this->url->link(
			$this->a->type . '/' . $this->a->code . '/preview',
			'token=' . $this->session->data['token'],
			'SSL'
		);

		$data['cancel'] = $this->url->link(
			'extension/module',
			'token=' . $this->session->data['token'],
			'SSL'
		);

		$this->a->clear_all_snapshots();

		$data['profiles'] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Profile' ),
			'label_for' => 'profiles',
			'cols'      => array( 'col-sm-2', 'col-sm-10' ),
			'element'   => $this->a->r( array(
				'type'   => 'select',
				'id'     => 'profiles',
				'class'  => 'form-control',
				'value'  => $this->model->profiles_to_select(),
				) ),
			'description' => $this->a->__( 'Load profile' ),
			) );

		$data['send'] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Send test letter' ),
			'label_for' => 'send-email-addr',
			'cols'      => array( 'col-sm-2', 'col-sm-10' ),
			'element'   => $this->a->r( array(
				'type'    => 'inputgroup',
				'element' => array(
					'type'        => 'text',
					'placeholder' => 'E-mail',
					'class'       => 'form-control',
					'value'       => $this->config->get( 'config_email' ),
					'id'          => 'send-email-addr',
				),
				'addon_after' => array(
					'type' => 'button',
					'icon' => 'fa-envelope-o',
					'id'   => 'send-email',
				),
			) ),
			'description' => $this->a->__( 'Send test email to see the result in different email clients. Profile will be chosen depend on existing profile mapping' ),
		) );

		$data['add_template_row_1'] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Add template' ),
			'label_for' => '',
			'cols'      => array( 'col-sm-2', 'col-sm-10' ),
			'element'   => $this->a->r( array(
				'type' => 'inputgroup',
				'element' => array(
					'type'   => 'text',
					'id'     => 'new-template-name',
					'class'  => 'form-control',
					'placeholder' => $this->a->__( 'Template name' ),
				),
				'addon_after' => array(
					'type'        => 'button',
					'icon'        => 'fa-plus-circle',
					'button_type' => 'success',
					'id'          => 'new-template-add',
					'custom_data' => 'data-url="' . $this->url->link(
						$this->a->type . '/' . $this->a->code . '/add_template',
						'token=' . $this->session->data['token'],
						'SSL'
					) . '"',
					'title'       => $this->a->__( 'Add template' ),
				),
			) )
		) );

		$data['add_template_row_2'] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Template hook' ),
			'label_for' => '',
			'cols'      => array( 'col-sm-2', 'col-sm-10' ),
			'element'   => '<span class="col-sm-6">' .
								'<input type="text" id="new-template-file" class="form-control" placeholder="' . $this->a->__( 'File name' ) . '">' .
								'<span id="helpBlock" class="help-block">' . $this->a->__( 'File name should start from the store\'s root' ) . '</span>' .
							'</span>' .
							'<span class="col-sm-6">' .
								'<input type="text" id="new-template-function" class="form-control" placeholder="' . $this->a->__( 'Function name' ) . '">' .
								'<span id="helpBlock" class="help-block">' . $this->a->__( 'Function\'s name without parentheses' ) . '</span>' .
							'</span>',
			'tooltip' => $this->a->__( 'Use these fields to add templates for third parties\' extensions' ),
		) );

		$data['add_template_row_3'] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Template hook' ),
			'label_for' => '',
			'cols'      => array( 'col-sm-2', 'col-sm-10' ),
			'element'   => '<span class="col-sm-12">' .
								'<input type="text" id="new-template-hook" class="form-control" placeholder="' . $this->a->__( 'Template hook' ) . '">' .
								'<span id="helpBlock" class="help-block">' . $this->a->__( 'Specify template hook e.g. newsletter.subscribe.newsletter_name - where newsletter-name - name of the newsletter' ) . '</span>' .
							'</span>',
			'tooltip' => $this->a->__( 'Use these fields to extend existing templates (e.g. add different subscription confirmation email for each newsletter)' ),
		) );

		$data['add_template_row_4'] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Sample' ),
			'label_for' => '',
			'cols'      => array( 'col-sm-2', 'col-sm-10' ),
			'element'   => $this->a->r( array(
				'type'   => 'select',
				'id'     => 'new-template-sample',
				'class'  => 'form-control',
				'value'  => array_replace( array( '-1' => $this->a->__( 'Pick a template' ) ), $this->model->get_templates_name() ),
			) ),
			'description' => $this->a->__( 'Use sample template to copy its content' ),
		) );

		$data['templates'] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Load template' ),
			'label_for' => 'templates',
			'cols'      => array( 'col-sm-2', 'col-sm-10' ),
			'element'   => $this->a->r( array(
				'type'   => 'select',
				'id'     => 'templates',
				'class'  => 'form-control',
				'value'  => $this->model->get_templates_name(),
				) )
			) );

		$data['preview_controls'] = $this->get_preview_controls();

		$data['panels'] = array(
			'panels' => array(
				'profiles-manager' => array(
					'id'     => 'profiles-manager',
					'name'   => $this->a->__( 'Profiles' ),
					'active' => true,
					'icon'   => 'fa-object-ungroup fa-2x',
					'class'  => 'sway-able',
				),
				'contents-manager' => array(
					'id'     => 'contents-manager',
					'name'   => $this->a->__( 'Content' ),
					'icon'   => 'fa-cogs fa-2x',
					'class'  => 'sway-able',

				),
				'shortcodes-manager' => array(
					'id'     => 'shortcodes-manager',
					'name'   => $this->a->__( 'Shortcodes' ),
					'icon'   => 'fa-code fa-2x',
					'class'  => 'sway-able',
				),
				'profile-mapping' => array(
					'id'     => 'profile-mapping',
					'name'   => $this->a->__( 'Configuration' ),
					'icon'   => 'fa-map-o fa-2x',
					'class'  => 'sway-able',
				),
				'shortcodes-list' => array(
					'id'     => 'shortcodes-list',
					'name'   => $this->a->__( 'Shortcodes list' ),
					'icon'   => 'fa-list fa-2x',
					'class'  => 'sway-able',
				),

				// Since 1.1.0
				'newsletter-pane' => array(
					'id'     => 'newsletter-pane',
					'name'   => $this->a->__( 'Newsletter' ),
					'icon'   => 'fa-paper-plane fa-2x',
					'class'  => 'sway-able',
				),
				'history-pane' => array(
					'id'     => 'history-pane',
					'name'   => $this->a->__( 'History' ),
					'icon'   => 'fa-history fa-2x',
					'class'  => 'sway-able',
				),
				'settings' => array(
					'id'     => 'settings',
					'name'   => $this->a->__( 'Settings' ),
					'icon'   => 'fa-wrench fa-2x',
					'class'  => 'sway-able',
				),
				'queue-pane' => array(
					'id'     => 'queue-pane',
					'name'   => $this->a->__( 'Queue' ),
					'icon'   => 'fa-clock-o fa-2x',
					'class'  => 'sway-able',
				),
				'bounced-emails' => array(
					'id'     => 'bounced-emails',
					'name'   => $this->a->__( 'Bounce' ),
					'icon'   => 'fa-reply fa-2x',
					'class'  => 'sway-able',
				),
			),
		);

		$shortcode_context = array( '-1' => $this->a->__( 'Disable filter' ) );
		foreach( $shortcode->get_shortcode_data() as $s ) {
			$data['shortcodes_list'][] = array(
				'name'        => $s['hint'],
				'shortcode'   => $shortcode->brace_shortcode_name( $s['hint'] ),
				'description' => $s['description'],
				'context'     => $s['context'],
			);

			$shortcode_context = array_merge( $shortcode_context, $s['context'] );
		}

		$shortcode_context = array_unique( $shortcode_context );
		foreach( $shortcode_context as $record ) {
			if( strpos( $record, '-' ) ) {
				$parts = explode( '-', $record, 2 );
					$part = trim( $parts[0] );
					if( ! in_array( $part, $shortcode_context ) ) {
						$shortcode_context[] = $part;
				}
			}
		}

		usort( $data['shortcodes_list'], function( $a, $b ) {
			if( $a['name'] === $b['name'] ) {
				return 0;
			}

			return $a['name'] > $b['name'];
		} );

		$data['context_list'] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Context filter' ),
			'label_for' => 'filter_context',
			'cols'      => array( 'col-sm-2', 'col-sm-10' ),
			'element'   => $this->a->r( array(
				'type'   => 'select',
				'id'     => 'filter-context',
				'class'  => 'form-control',
				'value'  => $shortcode_context,
				) )
			) );

		$profiles = array( '-1' => $this->a->__( 'Do not map' ) );
		foreach( $this->model->profiles_to_select() as $id => $p ) {
			$profiles[ $id ] = $p;
		}

		$data['store_mapping'] = $this->render_store_mapping( $profiles );
		$data['lang_mapping'] = $this->render_lang_mapping( $profiles );
		$data['template_mapping'] = $this->render_template_mapping( $profiles );

		$stores_to_be_added = array();
		foreach( $this->a->get_stores() as $store ) {
			$stores_to_be_added[ $store['id'] ] = $store['name'];
		}

		$this->load->model( 'localisation/language' );
		$languages = $this->model_localisation_language->getLanguages();
		$langs = array();
		foreach( $languages as $l ) {
			$langs[ $l['language_id'] ] = $l['name'];
		}

		$data['shortcodes'] = $this->render_shortcodes();
		$data['history'] = $this->render_history();
		$data['history_filter'] = $this->render_history_filter();
		$data['newsletter_filter'] = $this->render_newsletter_filter();

		$data['status'] = $this->a->r()->render_form_group( array(
			'label'       => $this->a->__( 'Extension status' ),
			'label_for'   => 'status',
			'cols'        => array( 'col-sm-2', 'col-sm-10' ),
			'element'   => $this->a->r()->render_fancy_checkbox( array(
				'id'    => 'status',
				'value' => $this->a->config( 'status' ),
				'class' => 'setting-data',
			) ),
			'description' => $this->a->__( 'On/off the extension' ),
		) );

		$data['hints_on'] = $this->a->r()->render_form_group( array(
			'label'       => $this->a->__( 'Enable hints' ),
			'label_for'   => 'hints',
			'cols'        => array( 'col-sm-2', 'col-sm-10' ),
			'element'     => $this->a->r()->render_fancy_checkbox( array(
				'id'    => 'hints',
				'value' => $this->a->config( 'hint' ),
				'class' => 'setting-data',
			) ),
			'description' => $this->a->__( 'Hide/show information hints' ),
		) );

		// Since 1.1.0 - changed checkbox implementation
		$data['auto_update'] = $this->a->r()->render_form_group( array(
			'label'       => $this->a->__( 'Auto update' ),
			'label_for'   => 'auto-update',
			'cols'        => array( 'col-sm-2', 'col-sm-10' ),
			'element'     => $this->a->r()->render_fancy_checkbox( array(
				'id'    => 'auto-update',
				'value' => $this->a->config( 'auto_update', 1 ),
				'class' => 'simple-setting',
				'custom_data' => 'data-name="auto_update"',
			) ),
			'description' => $this->a->__( 'Disable auto update of preview window to speed up user interface' ),
		) );

		$data['throttle_item'] = $this->a->r()->render_form_group( array(
			'label' => $this->a->__( 'Throttle by count' ),
			'element' => $this->a->r( array(
				'type'    => 'inputgroup',
				'element' => array(
					'type'        => 'number',
					'class'       => 'form-control',
					'value'       => $this->a->config( 'throttle-item' ) ?: 10,
					'id'          => 'throttle-item-value'
				),
				'addon_before' => $this->a->__( 'Emails per minute' ),
				'addon_after' => array(
					'type'    => 'buttons',
					'buttons' => array(
						array(
							'type'        => 'button',
							'icon'        => 'fa-power-off',
							'id'          => 'throttle-item-on',
							'button_type' => $this->a->config( 'throttle-item' ) ? 'success' : '',
							'class'       => 'switchable',
							'custom_data' => 'data-values="0,1" data-value="' . ( $this->a->config( 'throttle-item' ) ? '1' : '0' ) . '"',
						),
						array(
							'type'        => 'button',
							'icon'        => 'fa-save',
							'button_type' => 'primary',
							'id'          => 'throttle-item'
						),
					),
				),
			) ),
			'description' => $this->a->__( 'Limits sending rate by quantity of emails per minute' ),
		) );

		$data['throttle_traffic'] = $this->a->r()->render_form_group( array(
			'label' => $this->a->__( 'Throttle by traffic' ),
			'element' => $this->a->r( array(
				'type'    => 'inputgroup',
				'element' => array(
					'type'        => 'number',
					'class'       => 'form-control',
					'value'       => $this->a->config( 'throttle-traffic' ) ?: 10,
					'id'          => 'throttle-traffic-value'
				),
				'addon_before' => $this->a->__( 'MB per minute'),
				'addon_after' => array(
					'type'    => 'buttons',
					'buttons' => array(
						array(
							'type'        => 'button',
							'icon'        => 'fa-power-off',
							'id'          => 'throttle-traffic-on',
							'button_type' => $this->a->config( 'throttle-traffic' ) ? 'success' : '',
							'class'       => 'switchable',
							'custom_data' => 'data-values="0,1" data-value="' . ( $this->a->config( 'throttle-traffic' ) ? '1' : '0' ) . '"',
						),
						array(
							'type'        => 'button',
							'icon'        => 'fa-save',
							'button_type' => 'primary',
							'id'          => 'throttle-traffic'
						),
					),
				),
			) ),
			'description' => $this->a->__( 'Limits sending rate by quantity of traffic per minute' ),
		) );

		$data['archive_size'] = $this->a->r()->render_form_group( array(
			'label'       => $this->a->__( 'Clear archive' ),
			'label_for'   => 'archive-days',
			'cols'        => array( 'col-sm-2', 'col-sm-10' ),
			'element'     => $this->a->r( array(
				'type'       => 'dimension',
				'id'         => 'archive-days',
				'texts'      => 'day(s)',
				'units'      => 'day(s)',
				'value'      => 60,
				'maxes'      => 1000,
				'max'        => 1000,
				'titles'     => $this->a->__( 'Archive depth in days' ),
				'addon'      => array(
					'type' => 'button',
					'icon' => 'close',
				),
			) ),
			'description' => $this->a->__( 'Remove archive entries, older than specific number of days. Current archive size: <b>%s</b>', $this->a->format_bytes( $fs->get_dir_size( $this->a->archive_dir ) ) ) . ' ' .
				$this->a->r( array(
				'type'        => 'button',
				'id'          => 'archive-clean',
				'icon'        => 'fa-close',
				'class'       => 'pull-right',
				'button_type' => 'danger',
				'custom_data' => 'data-url="' . $this->url->link(
						$this->a->type . '/' . $this->a->code . '/clean_archive',
						'token=' . $this->session->data['token'],
						'SSL'
					) . '"',
			) ),
			'tooltip'     => $this->a->__( 'Email version to "Open in browser" are stored in archive' ),
		) );

		$data['extended_newsletter'] = $this->a->r()->render_form_group( array(
			'label'       => $this->a->__( 'Extended newsletter' ),
			'label_for'   => 'extended_newsletter',
			'cols'        => array( 'col-sm-2', 'col-sm-10' ),
			'element'     => $this->a->r()->render_fancy_checkbox( array(
				'id'    => 'extended_newsletter',
				'value' => $this->a->config( 'extended_newsletter' ),
				'class' => 'setting-data',
			) ),
			'description' => $this->a->__( 'Add extended functionality to OpenCart\'s Marketing page' ),
		) );

		$data['profile_tip'] = $this->a->r()->render_info_box( $this->a->__( 'Use this tab to manage profiles - the general view of templates. Profiles are designed to wrap the contents of the letters and give them desired look and feel. You can modify existing profiles or create custom on their basis. <b>Important:</b> to modifications take effect you need to save profile, by clicking save button below' ) );

		$data['contents_tip'] = $this->a->r()->render_info_box( $this->a->__( 'In this tab, you can manage the contents of the template. To do this, download the desired template or create a custom. You can create different contents for each store and for each store\'s language. If you create only one contents instance - it will be used for all the stores and the languages. <b>Important:</b> to modifications take effect you need to save contents, by clicking save button from "Manage template" panel below' ) );

		$data['create_template_tip'] = $this->a->r()->render_info_box( $this->a->__( 'The extension covers all the emails sent by the OpenCart and also has the templates\' fall-back system to catch emails from custom extensions. If you want to add your own template for a custom extension, you need to know a function name (method) (eg <b>addOrderHistory</b>) in which method <b>send</b> of <b>Mail</b> class is called (when a method is called a similar construction <b>$mail->send();</b> should be present) and a file name where such function (method) resides (eg <b>/catalog/model/checkout/order.php</b>). If you not sure - refer extension\'s developer. When you have all needed information fill in corresponding fields below, name the template and click the "Add template" button' ) );

		$data['mapping_tip'] = $this->a->r()->render_info_box( $this->a->__( 'At this tab, you can set template configuration. Almost every configurations are subject to the template/language/store configuration resolution mechanism. That means configuration will be taken from hierarchy template/language/store, and will be considered disabled only if there is no one enabled configuration in the hierarchy' ) );

		$data['shortcode_list_tip'] = $this->a->r()->render_info_box( $this->a->__( 'At this tab, you can see the list of all the available shortcodes. <b>Important:</b> contents of shortcodes are only available in specific context, for example, shortcode {order_id} is only available when the new order is created or status changed for the existing order, and it will return an empty string in any other context. To see which context supports specific shortcode refer "context" column of the table below. Also, you can filter shortcodes by context' ) );

		$data['settings_tip'] = $this->a->r()->render_info_box( $this->a->__( 'At this tab, you can see the list of all the available shortcodes. <b>Important:</b> contents of shortcodes are only available in specific context, for example, shortcode {order_id} is only available when the new order is created or status changed for the existing order, and it will return an empty string in any other context. To see which context supports specific shortcode refer "context" column of the table below. Also, you can filter shortcodes by context' ) );

		$data['preview_tip'] = $this->a->r()->render_info_box( $this->a->__( '"Live preview" allows you to see how email template will look at devices with different display width and orientation. <b>Important:</b> the vast majority of email clients do not show images by default, so you need to test how your template will look without images. To do so click on "image" button on the control tab of  "Live preview". To refresh "Live preview" window click "refresh" button. Profile, used for preview will depend on context, template you can select manually' ) );

		$data['queue_tip'] = $this->a->r()->render_info_box( $this->a->__( 'When "Queue" is enabled emails will be sending in background, that will increase user experience bay far, and make it possible to resend email after network error or such.  In order for this option to work, you must run task form "Cron job" field as server\'s cron job every minute' ) );

		$data['shortcode_tip'] = $this->a->r()->render_info_box( $this->a->__( 'On this tab, you can by yourself create certain kinds of shortcodes, to meet your specific requirements' ) );

		$data['newsletter_tip'] = $this->a->r()->render_info_box( $this->a->__( 'At this tab you can create newsletter, create opt-in widget for a newsletter, track newsletter\'s stats and conversion. To send the newsletter go to "Marketing>Mail" page and select newsletter\'s name from drop-down "To" list' ) );

		$data['settings_tip'] = $this->a->r()->render_info_box( $this->a->__( 'At this tab, you can change some extension settings' ) );

		$data['bounce_tip'] = $this->a->r()->render_info_box( $this->a->__( 'Specify separate inbox for all bounced emails. Also, all email addresses from this inbox can be blacklisted, so newsletter will never be sent to these email addresses again' ) );

		$data['missed_order_templates'] = '';
		$missed_templates = $this->model->get_missed_order_status_templates();

		if ( $missed_templates ) {
			$data['missed_order_templates'] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Order statuses templates' ),
			'label_for' => '',
			'cols'      => array( 'col-sm-2', 'col-sm-10' ),
			'class'     => 'add-status-template',
			'element'   => $this->a->r( array(
				'type'         => 'inputgroup',
				'addon_after' => array(
					'type'        => 'button',
					'icon'        => 'fa-plus-circle',
					'button_type' => 'success',
					'id'          => 'add-status-template'
				),
				'element'      => array(
					'type'        => 'select',
					'class'       => 'form-control select2',
					'value'       => $missed_templates,
					'custom_data' => 'multiple="multiple"',
					'id'          => 'missed-order-templates-list'
				)
			) ),
			'description' => $this->a->__( 'Email templates are missing for some order\'s statuses. To automatically create templates select needed order\'s status and click the "Create" button' ),
			) );
		}

		$data['missed_return_templates'] = '';
		$missed_r_templates = $this->model->get_missed_return_status_templates();

		if ( $missed_r_templates ) {
			$data['missed_return_templates'] = $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Return statuses templates' ),
			'label_for' => '',
			'cols'      => array( 'col-sm-2', 'col-sm-10' ),
			'class'     => 'add-status-template',
			'element'   => $this->a->r( array(
				'type'         => 'inputgroup',
				'addon_after' => array(
					'type'        => 'button',
					'icon'        => 'fa-plus-circle',
					'button_type' => 'success',
					'id'          => 'add-r-status-template'
				),
				'element'      => array(
					'type'        => 'select',
					'class'       => 'form-control select2',
					'value'       => $missed_r_templates,
					'custom_data' => 'multiple="multiple"',
					'id'          => 'missed-return-templates-list'
				)
			) ),
			'description' => $this->a->__( 'Email templates are missing for some returns\'s statuses. To automatically create templates select needed return\'s status and click the "Create" button' ),
			) );
		}

		// Since 1.1.0
		// Newsletter panel's tabs
		$data['newsletter_panels'] = array(
			'panels' => array(
				'newsletter-list-pane' => array(
					'id'     => 'newsletter-list-pane',
					'name'   => $this->a->__( 'List' ),
					'active' => true,
					'icon'   => 'fa-list-ol fa-2x',
					'class'  => 'sway-able',
				),
				'newsletter-manage-pane' => array(
					'id'     => 'newsletter-manage-pane',
					'name'   => $this->a->__( 'Manage' ),
					'icon'   => 'fa-pencil fa-2x',
					'class'  => 'sway-able',

				),
				'newsletter-add-pane' => array(
					'id'     => 'newsletter-add-pane',
					'name'   => $this->a->__( 'Add' ),
					'icon'   => 'fa-plus-circle fa-2x',
					'class'  => 'sway-able',
				),
				'newsletter-form-pane' => array(
					'id'     => 'newsletter-form-pane',
					'name'   => $this->a->__( 'Widget' ),
					'icon'   => 'fa-puzzle-piece fa-2x',
					'class'  => 'sway-able',
				),
				'newsletter-caption-pane' => array(
					'id'     => 'newsletter-caption-pane',
					'name'   => $this->a->__( 'Text customization' ),
					'icon'   => 'fa-font fa-2x',
					'class'  => 'sway-able',
				),
			),
		);

		$data['newsletter_url'] = $this->url->link(
			$this->a->type . '/' . $this->a->code . '/fetch_newsletter_list',
			'token=' . $this->session->data['token'],
			'SSL'
		);

		$data['history_url'] = $this->url->link(
			$this->a->type . '/' . $this->a->code . '/fetch_history',
			'token=' . $this->session->data['token'],
			'SSL'
		);

		$data['add_newsletter'] = $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Add newsletter' ),
			'element' => $this->a->r( array(
				'type'    => 'inputgroup',
				'element' => array(
					'type'        => 'text',
					'class'       => 'form-control',
					'id'          => 'add-newsletter-name',
					'placeholder' => $this->a->__( 'Newsletter name' ),
				),
				'addon_after' => array(
					'type'        => 'button',
					'id'          => 'add-newsletter',
					'icon'        => 'fa-plus-circle',
					'button_type' => 'success',
					'custom_data' => 'data-url="' .
						$this->url->link(
							$this->a->type . '/' . $this->a->code . '/add_newsletter',
							'token=' . $this->session->data['token'],
							'SSL'
						) .
					'"',
				),
			) ),
		) ) .

		$this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Description' ),
			'element' => $this->a->r( array(
				'type'        => 'text',
				'class'       => 'form-control',
				'id'          => 'add-newsletter-description',
				'placeholder' => $this->a->__( 'Newsletter description' ),
			) ),
		) );

		$newsletter_list_for_select = array( '-1' => $this->a->__( 'Select newsletter' ) );

		if ( $newsletter_list = $this->a->get_newsletter_list() ) {
			foreach( $newsletter_list as $newsletter ) {
				$newsletter_list_for_select[ $newsletter['id'] ] = $newsletter['name'];
			}
		}

		$data['select_newsletter'] = $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Select newsletter' ),
			'element' => $this->a->r( array(
				'type'  => 'select',
				'class' => 'form-control',
				'id'    => 'select-newsletter',
				'value' => $newsletter_list_for_select,
			) ),
		) );

		$data['newsletter_controls_url'] = $this->url->link(
			$this->a->type . '/' . $this->a->code . '/newsletter_controls',
			'token=' . $this->session->data['token'],
			'SSL'
		);

		$data['form_newsletter'] = $this->render_newsletter_form_builder();

		$data['newsletter_caption'] = $this->model->render_newsletter_caption_tab();

		$data['queue_content'] = $this->model->render_queue_tab();

		$data['bounce_content'] = $this->model->render_bounce_tab();

		$data['locale'] = json_encode( array(
			'profileUrl' => $this->url->link(
				$this->a->type . '/'  . $this->a->code . '/profile',
				'token=' . $this->session->data['token'],
				'SSL'
			),
			'changeUrl' => $this->url->link(
				$this->a->type . '/'  . $this->a->code . '/set_tmp',
				'token=' . $this->session->data['token'],
				'SSL'
			),
			'previewUrl' => $this->url->link(
				$this->a->type . '/' . $this->a->code . '/preview',
				'token=' . $this->session->data['token'],
				'SSL'
			),
			'templateUrl' => $this->url->link(
				$this->a->type . '/'  . $this->a->code . '/template',
				'token=' . $this->session->data['token'],
				'SSL'
			),
			'addStoreUrl' => $this->url->link(
				$this->a->type . '/' . $this->a->code . '/add_store',
				'token=' . $this->session->data['token'],
				'SSL'
			),
			'addLangUrl' => $this->url->link(
				$this->a->type . '/' . $this->a->code . '/add_lang',
				'token=' . $this->session->data['token'],
				'SSL'
			),
			'deleteStoreUrl' => $this->url->link(
				$this->a->type . '/' . $this->a->code . '/delete_store',
				'token=' . $this->session->data['token'],
				'SSL'
			),
			'deleteLangUrl' => $this->url->link(
				$this->a->type . '/' . $this->a->code . '/delete_lang',
				'token=' . $this->session->data['token'],
				'SSL'
			),
			'saveTemplateUrl' => $this->url->link(
				$this->a->type . '/' . $this->a->code . '/save_template',
				'token=' . $this->session->data['token'],
				'SSL'
			),
			'saveTemplateTempUrl' => $this->url->link(
				$this->a->type . '/' . $this->a->code . '/save_template_tmp',
				'token=' . $this->session->data['token'],
				'SSL'
			),
			'setProfileUrl' => $this->url->link(
				$this->a->type . '/' . $this->a->code . '/set_profile',
				'token=' . $this->session->data['token'],
				'SSL'
			),
			'sendEmailUrl' => $this->url->link(
				$this->a->type . '/' . $this->a->code . '/send_email',
				'token=' . $this->session->data['token'],
				'SSL'
			),
			'saveShortcodeUrl' => $this->url->link(
				$this->a->type . '/' . $this->a->code . '/save_shortcode',
				'token=' . $this->session->data['token'],
				'SSL'
			),
			'fetchShortcodeUrl' => $this->url->link(
				$this->a->type . '/' . $this->a->code . '/fetch_shortcode',
				'token=' . $this->session->data['token'],
				'SSL'
			),
			'deleteShortcodeUrl' => $this->url->link(
				$this->a->type . '/' . $this->a->code . '/delete_shortcode',
				'token=' . $this->session->data['token'],
				'SSL'
			),
			'productAutofillUrl' => $this->url->link(
				$this->a->type . '/' . $this->a->code . '/product_autofill',
				'token=' . $this->session->data['token'],
				'SSL'
			),
			'saveProfileMappingUrl' => $this->url->link(
				$this->a->type . '/' . $this->a->code . '/save_profile_mapping',
				'token=' . $this->session->data['token'],
				'SSL'
			),
			'statusUrl' => $this->url->link(
				$this->a->type . '/' . $this->a->code . '/status',
				'token=' . $this->session->data['token'],
				'SSL'
			),
			'hintUrl' => $this->url->link(
				$this->a->type . '/' . $this->a->code . '/hint',
				'token=' . $this->session->data['token'],
				'SSL'
			),

			'settingUrl' => $this->url->link(
				$this->a->type . '/' . $this->a->code . '/setting',
				'token=' . $this->session->data['token'],
				'SSL'
			),
			'elfinderAttachmentAction' => $this->url->link(
				$this->a->type . '/' . $this->a->code . '/attachments_connector',
				'token=' . $this->session->data['token'],
				'SSL'
			),

			// What to render on admin area as select preview
			'socialImageList' => array(
				'facebook',
				'google+',
				'instagram',
				'linkedin',
				'twitter',
				'youtube',
			),

			'elfinderAttachmentHref' => $this->url->link(
				$this->a->type . '/' . $this->a->code . '/attachment_href',
				'token=' . $this->session->data['token'],
				'SSL'
			),

			'elfinderImgHref' => $this->url->link(
				$this->a->type . '/' . $this->a->code . '/img_href',
				'token=' . $this->session->data['token'],
				'SSL'
			),

			'thumbnailUrl' => $this->url->link(
				$this->a->type . '/' . $this->a->code . '/thumbnail',
				'token=' . $this->session->data['token'],
				'SSL'
			),

			'tableFilterAutofilllUrl' => $this->url->link(
				$this->a->type . '/' . $this->a->code . '/table_filter_autofill',
				'token=' . $this->session->data['token'],
				'SSL'
			),

			'historyCleanUrl' => $this->url->link(
				$this->a->type . '/' . $this->a->code . '/history_clean',
				'token=' . $this->session->data['token'],
				'SSL'
			),

			'saveLogUrl' => $this->url->link(
				$this->a->type . '/' . $this->a->code . '/save_history_log',
				'token=' . $this->session->data['token'],
				'SSL'
			),

			'updateNewsletterUrl' => $this->url->link(
				$this->a->type . '/' . $this->a->code . '/update_newsletter',
				'token=' . $this->session->data['token'],
				'SSL'
			),

			'serverUrl'         => HTTPS_CATALOG,
			'elfinderI18n'      => HTTPS_SERVER . 'view/javascript/advertikon/elfiner/js/i18n',

			'addStoreTemplate'  => $this->a->r()->render_panel_header(
				array(
					'dropdown' => true,
					'name'     => $this->a->__( 'Add store' ),
					'options'  => $stores_to_be_added,
				)
			),
			'addLangTemplate'  => $this->a->r()->render_panel_header(
				array(
					'dropdown' => true,
					'name'     => $this->a->__( 'Add language' ),
					'options'  => $langs,
				)
			),

			'imageBase'                 => HTTPS_CATALOG . 'image/',
			'networkError'              => $this->a->__( 'Network error' ),
			'parseError'                => $this->a->__( 'Unable to parse server response string' ),
			'undefServerResp'           => $this->a->__( 'Undefined server response' ),
			'unloadAlert'               => $this->a->__( 'You have unsaved data' ),
			'serverError'               => $this->a->__( 'Server error' ),
			'sessionExpired'            => $this->a->__( 'Current session has expired' ),
			'scriptError'               => $this->a->__( 'Script error' ),
			'profileSaved'              => $this->a->__( 'Profile data have been saved' ),
			'shortcodeSaved'            => $this->a->__( 'Shortcode has been saved' ),
			'templateSaved'             => $this->a->__( 'Template data have been saved' ),
			'profileCloned'             => $this->a->__( 'Profile has been cloned' ),
			'emailSent'                 => $this->a->__( 'Email has been successfully sent' ),
			'profileDeleted'            => $this->a->__( 'Profile has been deleted' ),
			'prefix'                    => $this->a->code . '_',
			'modalHeader'               => $this->language->get( 'heading_title' ),
			'no'                        => $this->a->__( 'No' ),
			'yes'                       => $this->a->__( 'Yes' ),
			'sureDeleteProfile'         => $this->a->__( 'Do you really want to delete profile' ),
			'missingCategory'           => $this->a->__( 'Shortcode category is missing' ),
			'needToFillIn'              => $this->a->__( 'Field is mandatory' ),
			'noShortcode'               => $this->a->__( 'You need select shortcode' ),
			'sureDeleteStore'           => $this->a->__( 'Do you really want to delete template for current store' ),
			'sureDeleteLang'            => $this->a->__( 'Do you really want to delete template for current language' ),
			'shortcodes'                => $shortcode->get_shortcodes_hint(),
			'templateNameMandatory'     => $this->a->__( 'Template name is mandatory' ),
			'templateFunctionMandatory' => $this->a->__( 'Working function name is mandatory' ),
			'templateFileMandatory'     => $this->a->__( 'Working file name is mandatory' ),
			'storeLangMissing'          => $this->a->__( 'Unable to detect store and language for the template' ),
			'showTips'                  => 1 == $this->a->config( 'hint' ) ? '1' : '0',
			'fileBrowser'               => $this->a->__( 'File browser' ),
			'name'                      => $this->a->__( 'Name' ),
			'size'                      => $this->a->__( 'Size' ),
			'embed'                     => $this->a->__( 'Embed attachment' ),
			'del'                       => $this->a->__( 'Delete' ),
			'embedTooltip'              => $this->a->r()->render_popover( $this->a->__( 'Attachments just appear as files that can be saved to the Desktop if desired. You can make attachment appear inline where possible by mark attachment as "Embed".' ) ),
			'clipboard'                 => $this->a->__( 'Data have been copied into clipboard'),
			'shortcodeSupport'          => $this->a->__( 'Field supports shortcodes' ),
			'cleanArchive'              => $this->a->__( 'You are about to remove archive entries older than %s day(s)' ),
			'logCaption'                => $this->a->__( 'Log' ),
			'from'                      => $this->a->__( 'From' ),
			'to'                        => $this->a->__( 'To' ),
			'pickPeriod'                => $this->a->__( 'Pick the period' ),
			'all'                       => $this->a->__( 'all' ),
			'cleanHistory'              => $this->a->__( 'You are about to delete %s history entry(s). Newsletter\'s statistic will be lost' ),
			'save'                      => $this->a->__( 'Save' ),
			'emtyStatusTemplatesList'   => $this->a->__( 'At least one status need to be chosen'),
			'selectNewsletter'          => $this->a->__( 'Newsletter ID is mandatory' ),
			'sureDeleteNewsletter'      => $this->a->__( 'You are about to delete newsletter' ),
			'subscriberNameNeeded'      => $this->a->__( 'Subscriber\'s name is mandatory' ),
			'subscriberEmailNeeded'     => $this->a->__( 'Subscriber\'s e-mail is mandatory' ),
			'sureDeleteSubscriber'      => $this->a->__( 'You are about to delete subscriber' ),
			'sureDeleteWidget'          => $this->a->__( 'You are about to delete widget' ),
			'templateAnyField'          => $this->a->__( 'Select one of the options' ),
			'subscribersNeeded'         => $this->a->__( 'Subscribers group are mandatory' ),
			'filterMandatory'           => $this->a->__( 'Subscribers filter value is mandatory' ),
			'autoUpdate'                => $this->a->config( 'auto_update',1 ),
			'importCsvUrl'              => $this->url->link(
				$this->a->type . '/' . $this->a->code . '/import_csv',
				'token=' . $this->session->data['token'],
				'SSL'
			),
			'saveCaption'               => ! $this->a->caption( 'caption_confirm_expire_code' ),
		) ) . PHP_EOL;

		$data['version']     = \Advertikon\Mail\Advertikon::get_version();
		$data['name']        = $this->language->get( 'heading_title' );
		$data['helper']      = $this->helper;
		$data['header']      = $this->load->controller( 'common/header' );
		$data['column_left'] = $this->load->controller( 'common/column_left' );
		$data['footer']      = $this->load->controller( 'common/footer' );
		$data['a']           = $this->a;

		$this->response->setOutput( $this->load->view(
			$this->a->type . '/' . $this->a->code .'.tpl',
			$data
		) );
	}

	/**
	 * Install action
	 * @return void
	 */
	public function install() {
		$this->model->add_tables();
		$fs = new \Advertikon\Fs();

		$fs->mkdir( $this->a->tmp_dir );
		$fs->mkdir( $this->a->archive_dir );
		$fs->mkdir( $this->a->attachments_root );

		$this->model->set_settings();

		// Add queue task
		$task = new \Advertikon\Task( $this->a );
		$task->install()

			// Run Queue each minute with threshold of 600 sec
			->add_task(
				$this->a->get_store_url() . 'index.php?route=' . $this->a->type . '/' . $this->a->code . '/run_queue',
				'* * * * *',
				600
			)

			// Run Blacklister once a day with threshold of 600 sec
			->add_task(
				$this->a->get_store_url() . 'index.php?route=' . $this->a->type . '/' . $this->a->code . '/check_bounced',
				'* 0 * * *',
				600
			);
	}

	/**
	 * Uninstall action
	 * @return void
	 */
	public function uninstall() {
		$this->a->remove_db();

		if ( is_dir( $this->a->data_dir ) ) {
			$fs = new \Advertikon\Fs();
			$fs->rmdir( $this->a->data_dir );
		}

		if ( version_compare( VERSION, '2.0.0.0', '>' ) ) {
			$this->load->model( 'extension/module' );
			$this->model_extension_module->deleteModulesByCode( $this->a->code );
		}

		$task = new \Advertikon\Task( $this->a );
		$task->uninstall();
	}

	public function get_mapping_table_header() {
		return
'<div class="table-responsive">' .
'<table class="table mapping-table">' .
	'<thead>' .
		'<tr>' .
			'<th>' .
				$this->a->__( 'Level' ) . '&nbsp;' .
				$this->a->r()->render_popover( $this->a->__( 'Configuration scope' ) ) .
			'</th>' .

			'<th>' .
				$this->a->__( 'Profile' ) . '&nbsp;' .
				$this->a->r()->render_popover( $this->a->__( 'Profile to be applied to all the templates within the configuration scope. Subject to template/language/store scopes configuration resolution' ) ) .
			'</th>' .

			'<th>' .
				$this->a->__( 'Enable' ) . '&nbsp;' .
				$this->a->r()->render_popover( $this->a->__( 'If disabled - fall-back template system will be applied' ) ) .
			'</th>' .

			'<th>' .
				$this->a->__( 'Log' ) . '&nbsp;' .
				$this->a->r()->render_popover( $this->a->__( 'If enabled - email history will be recorded into log. Subject to template/language/store scopes configuration resolution' ) ) .
			'</th>' .

			'<th>' .
				$this->a->__( 'Track open' ) . '&nbsp' .
				$this->a->r()->render_popover( $this->a->__( 'If enabled - an email opening will be tracked. Subject to template/language/store scopes configuration resolution' ) ) .
			'</th>' .

			'<th>' .
				$this->a->__( 'Track visit' ) . '&nbsp' .
				$this->a->r()->render_popover( $this->a->__( 'If enabled - visit after an email receiving, will be tracked. Subject to template/language/store scopes configuration resolution' ) ) .
			'</th>' .
		'</tr>' .
	'</thead>' .
	'<tbody>';
	}

	/**
	 * Renders mapping table's footer
	 * @return string
	 */
	public function get_mapping_table_footer() {
		return
	'</tbody>' .
'</table>' .
'</div>';
	}

	/**
	 * Renders sore-level templates mapping
	 * @param array $profiles Profiles 
	 * @return string
	 */
	protected function render_store_mapping( $profiles ) {
		$stores = $this->a->get_stores();
		$ret = $this->get_mapping_table_header();

		foreach( $stores as $store ) {
			$ret .= $this->render_mapping_line( $store, $profiles, 'store' );
		}

		$ret .= $this->get_mapping_table_footer();

		return $ret;
	}

	/**
	 * Renders language-level templates mapping
	 * @param array $profiles Profiles
	 * @return string
	 */
	public function render_lang_mapping( $profiles ) {
		$this->load->model( 'localisation/language' );
		$ret = $this->get_mapping_table_header();

		foreach( $this->model_localisation_language->getLanguages() as $code => $lang ) {
			$lang['id'] = $lang['code'];
			$ret .= $this->render_mapping_line( $lang, $profiles, 'lang' );
		}

		$ret .= $this->get_mapping_table_footer();

		return $ret;
	}

	/**
	 * Renders template-level templates mapping
	 * @param array $profiles Profiles
	 * @return string
	 */
	public function render_template_mapping( $profiles ) {
		$ret = $this->get_mapping_table_header();

		foreach( $this->a->get_templates() as $template ) {
			$template['id'] = $template['template_id'];
			$ret .= $this->render_mapping_line( $template, $profiles, 'template' );
		}

		$ret .= $this->get_mapping_table_footer();

		return $ret;
	}

	/**
	 * Renders template mapping line
	 * @param  array $subject Subject to map
	 * @param array $map_to Mapping object
	 * @param string $mapping_level Mapping level
	 * @return string
	 */
	protected function render_mapping_line( $subject, $map_to, $mapping_level ) {
		$profiles_mapping = $this->a->get_profile_mappings();

		$ret =
		'<tr data-level="' . $mapping_level . '">';

		$ret.=
			'<td>';
		$ret .= $this->a->r( array(
			'type'        => 'text',
			'value'       => $subject['name'],
			'custom_data' => 'readonly data-level-id="' . $subject['id'] . '"',
			'class'       => 'form-control',
		) );
		$ret .=
			'</td>';


		$ret .=
			'<td>';
		$ret .= $this->a->r( array(
			'type'        => 'select',
			'value'       => $map_to,
			'active'      => isset( $profiles_mapping[ $mapping_level ][ $subject['id'] ]['profile'] ) ?
				$profiles_mapping[ $mapping_level ][ $subject['id'] ]['profile'] : '',
			'class'       => 'form-control template-configuration',
			'custom_data' => 'data-name="profile"',
		) );
		$ret .=
			'</td>';


		$ret .=
			'<td>';
		$ret .= $this->a->r()->render_fancy_checkbox( array(
			'value'       => isset( $profiles_mapping[ $mapping_level ][ $subject['id'] ]['enabled'] ) ?
				(boolean)$profiles_mapping[ $mapping_level ][ $subject['id'] ]['enabled'] : 1,
			'class'       => 'template-configuration',
			'custom_data' => 'data-name="enable"',
		) );
		$ret .=
			'</td>';

		$ret .=
			'<td>';
		$ret .= $this->a->r()->render_fancy_checkbox( array(
			'value'       => isset( $profiles_mapping[ $mapping_level ][ $subject['id'] ]['log'] ) ?
				(boolean)$profiles_mapping[ $mapping_level ][ $subject['id'] ]['log'] : 1,
			'class'       => 'template-configuration',
			'custom_data' => 'data-name="log"',
		) );
		$ret .=
			'</td>';


		$ret .=
			'<td>';
		$ret .= $this->a->r()->render_fancy_checkbox( array(
			'value'       => isset( $profiles_mapping[ $mapping_level ][ $subject['id'] ]['track'] ) ?
				(boolean)$profiles_mapping[ $mapping_level ][ $subject['id'] ]['track'] : 1,
			'class'       => 'template-configuration',
			'custom_data' => 'data-name="track"',
		) );
		$ret .=
			'</td>';

		$ret .=
			'<td>';
		$ret .= $this->a->r()->render_fancy_checkbox( array(
			'value'       => isset( $profiles_mapping[ $mapping_level ][ $subject['id'] ]['track_visit'] ) ?
				(boolean)$profiles_mapping[ $mapping_level ][ $subject['id'] ]['track_visit'] : 1,
			'class'       => 'template-configuration',
			'custom_data' => 'data-name="track_visit"',
		) );
		$ret .=
			'</td>';

		$ret .=
		'</tr>';

		return $ret;
	}

	/**
	 * Renders profile tab contents
	 * @return string
	 */
	protected function profile_contents() {
		$str = '';

		$profile_id = ! empty( $this->request->request['id'] ) ?
			$this->request->request['id'] : null;


		if( is_null( $profile_id ) ) {
			throw new \Advertikon\Exception( $this->a->__( 'Profile is missing' ) );
		}

		$profile = $this->a->get_profile( $profile_id );

		if( ! $profile ) {
			throw new \Advertikon\Exception(
				$this->a->__( 'Profile with ID #%s does not exist', $profile_id )
			);
		}

		// Profile description, if present
		if( isset( $profile['data']['name'] ) && isset( $profile['description'] ) ) {
			$str .= $this->a->r()->render_info_box( $profile['data']['name'] . ' - ' . $profile['description'] );
		}

		$str .= $this->model->render_color_scheme_picker( 'profile-color-scheme' );

		/**
		 * @var int $max_col_count Number of columns to render common profile controls 
		 */
		$max_col_count = 1;
		$col_count = 1;
		$col_part = 12 / $max_col_count;

		$str .=
		'<div id="profile-inputs">';

		foreach( $profile['inputs'] as $input_name ) {

			if( 1 === $col_count ) {
				$str .=
			'<div class="row">';
			}

			$str .=
				'<div class="col-sm-' . $col_part . '" style="padding-bottom:10px">' .
					$this->model->render_profile_control( $input_name, $profile ) .
				'</div>';

			if( $col_count === $max_col_count ) {
				$col_count = 1;
				$str .=
			'</div>'; // .row

				continue;
			}

			$col_count++;
		}

		if( array_filter( $profile['fields'] ) ) {
			$str .=
			'<ul class="nav nav-tabs">';

			// Make first pane active
			$active = 0;

			foreach( $profile['fields'] as $field ) {

			$str .=
				'<li class="' . ( 0 === $active++ ? 'active' : '' ) . '">' .
					'<a href="#tab-' . $field . '" data-toggle="tab">' . $this->model->get_panel_icon( $field, $profile['fields'] ) .
						'<i class="profile-tab-text">' . $this->model->get_panel_name( $field ) . '</i>' .
					'</a>' .
				'</li>';
			}

			$str .=
			'</ul>';
		}


		$str .=
			'<div class="tab-content">';

		$active = 0;

		foreach( $profile['fields'] as $field ) {

			$str .=
				'<div class="tab-pane' . ( 0 === $active++ ? ' active' : '' )  . '" id="tab-' . $field . '">';

			/**
			 * @var int $max_field_col_count NUmber of columns to render profile field's controls
			 */
			$max_field_col_count = 1;
			$col_field_count = 1;
			$col_field_part = 12 / $max_field_col_count;

			foreach( $this->model->get_field_inputs( $field ) as $input_name ) {

				if( 1 === $col_field_count ) {
						$str .=
					'<div class="row">';
				}

				$str .=
						'<div class="col-sm-' . $col_field_part . '" style="padding-bottom:10px">' .
							$this->model->render_profile_control( $field . '_' . $input_name, $profile ) .
						'</div>';

				if( $col_field_count === $max_field_col_count ) {
					$col_field_count = 1;
					$str .=
					'</div>'; // .row

					continue;
				}

				$col_field_count++;
			}

			$str .=
				'</div>'; // .tab-pane
		}

		$str .=
				'</div>' . // .tab-content
			'</div>'; // #profile-inputs

		return $str;
	}

	/**
	 * Fetch profile contents action
	 * @return void
	 */
	public function profile() {
		$ret = array();

		try {
			$ret['success'] = $this->profile_contents();

		} catch ( \Advertikon\Exception $e ) {
			$ret['error'] = $e->getMessage();
		}

		$this->response->setOutput( json_encode( $ret ) );
	}

	/**
	 * Save profile action
	 * @return void
	 */
	public function save() {
		$ret = array();

		try {

			if( ! $this->user->hasPermission( 'modify', $this->a->type . '/' . $this->a->code ) ) {
				throw new \Advertikon\Exception( $this->a->__( 'You have no permissions to modify extension data' ) );
			}

			if( empty( $this->request->request['id'] ) ) {
				throw new \Advertikon\Exception( 'Profile ID is missing' );
			}

			$id = $this->request->request['id'];

			if( ! $this->a->has_profile_snapshots( $id ) ) {
				throw new \Advertikon\Exception( 'Nothing to save' );
			}

			$data = $this->a->leave_current_profile_snapshot( $id );

			if( ! $data ) {
				throw new \Advertikon\Exception( 'Empty dataset' );
			}

			if( $this->model->save_profile_data( $id, $data ) > 0 ) {
				$ret['success'] = 1;
			} else {
				throw new \Advertikon\Exception( $this->a->__( 'Unable to save profile data' ) );
			}

		} catch( \Advertikon\Exception $e ) {
			$ret['error'] = $e->getMessage();
		}

		$this->response->setOutput( json_encode( $ret ) );
	}

	/**
	 * Clone profile action
	 * @return void
	 */
	public function clone_profile() {
		$ret = array();

		try {

			if( ! $this->user->hasPermission( 'modify', $this->a->type . '/' . $this->a->code ) ) {
				throw new \Advertikon\Exception( $this->a->__( 'You have no permissions to modify extension data' ) );
			}

			if( empty( $this->request->request['id'] ) ) {
				throw new \Advertikon\Exception( 'Profile ID is missing' );
			}

			$id = $this->request->request['id'];

			if( $clone = $this->model->clone_profile( $id ) ) {
				$ret['success'] = array( 'id' => $clone['id'], 'name' => $clone['name'] );
			} else {
				throw new \Advertikon\Exception( $this->a->__( 'Unable to clone profile' ) );
			}

		} catch( \Advertikon\Exception $e ) {
			$ret['error'] = $e->getMessage();
		}

		$this->response->setOutput( json_encode( $ret ) );
	}

	/**
	 * Delete profile action
	 * @return void
	 */
	public function delete() {
		$ret = array();

		try {

			if( ! $this->user->hasPermission( 'modify', $this->a->type . '/' . $this->a->code ) ) {
				throw new \Advertikon\Exception( $this->a->__( 'You have no permissions to modify extension data' ) );
			}

			if( empty( $this->request->request['id'] ) ) {
				throw new \Advertikon\Exception( 'Profile ID is missing' );
			}

			$id = $this->request->request['id'];

			if( $this->model->delete_profile( $id ) ) {
				$ret['success'] = 1;
				$this->a->remove_profile_snapshots( $id );
			} else {
				throw new \Advertikon\Exception( $this->a->__( 'Unable to delete profile' ) );
			}

		} catch( \Advertikon\Exception $e ) {
			$ret['error'] = $e->getMessage();
		}

		$this->response->setOutput( json_encode( $ret ) );
	}

	/**
	 * Renders preview window controls
	 * @return string
	 */
	public function get_preview_controls() {
		$str =
		$this->a->r()->render_button_group( array( 'buttons' => array(
			array(
				'type'        => 'button',
				'icon'        => 'fa-laptop',
				'title'       => $this->a->__( 'Laptop view' ),
				'id'          => 'laptop',
				'fixed_width' => true,
				'class'       => 'active',
			),
			array(
				'type'        => 'button',
				'icon'        => 'fa-tablet',
				'title'       => $this->a->__( 'Tablet view' ),
				'id'          => 'tablet',
				'fixed_width' => true,
			),
			array(
				'type'        => 'button',
				'icon'        => 'fa-mobile',
				'title'       => $this->a->__( 'Mobile view' ),
				'id'          => 'mobile',
				'fixed_width' => true,
			),
			array(
				'type'        => 'button',
				'icon'        => '',
				'id'          => 'rotate-view',
				'fixed_width' => true,
				'custom_data' => 'data-values="portrait,landscape" data-icons="fa-arrows-v,fa-arrows-h" data-titles="' .
				$this->a->__( 'Portrait orientation' ) . ', ' . $this->a->__( 'Landscape orientation') .	'" ' .
				'disabled="disabled" data-value="landscape"',
				'class'       => 'switchable',
			),
			array(
				'type'        => 'button',
				'icon'        => 'fa-refresh',
				'title'       => $this->a->__( 'Rload preview' ),
				'id'          => 'preview-reload',
				'fixed_width' => true,
			),
			array(
				'type'        => 'button',
				'icon'        => '',
				'id'          => 'preview-images',
				'fixed_width' => true,
				'class'       => 'switchable',
				'custom_data' => 'data-values="1,0" data-icons="fa-image,fa-eye-slash" data-titles="' .
				$this->a->__( 'Images enabled' ) . ', ' . $this->a->__( 'Images blocked') .	'"',
			),
		) ) ) .
		$this->a->r( array(
			'type'  => 'select',
			'value' => $this->model->get_templates_name(),
			'class' => 'form-control',
			'css'   => 'width:255px',
			'id'    => 'preview-template',
		) );

		return $str;
	}	

	/**
	 * Preview URL
	 * @return void
	 */
	public function preview() {
		$resp = '';
		$profile = null;
		$store_id = null;
		$lang_id = null;

		try {
			if( ! isset( $this->request->request['template_id'] ) ) {
				throw new \Advertikon\Exception( 'Template is missing' );
			}

			$template_id = $this->request->request['template_id'];

			if( isset( $this->request->request['profile_id'] ) ) {
				$profile = $this->a->get_profile( $this->request->request['profile_id'] );
			}

			if( isset( $this->request->request['store_id'] ) ) {
				$store_id = $this->request->request['store_id'];
			}

			if( isset( $this->request->request['lang_id'] ) ) {
				$lang_id = $this->request->request['lang_id'];
			}

			define( 'PREVIEW', 1 );
			define( 'SHOW_IMAGE', ! empty( $this->request->request['show_img'] ) );

			$resp = $this->a->render_mail_template( $template_id, $profile, $store_id, $lang_id );
			$resp = $this->a->fetch_html_variants( $resp );

		} catch ( \Advertikon\Exception $e ) {
			$this->log->write( $this->a->code . ': ' . $e->getMessage() );
		}

		$this->response->setOutput( $resp );
	}

	/**
	 * Add profile snapshot action
	 * @return void
	 */
	public function set_tmp() {
		$data = $this->request->request;
		$ret = array();

		try {
			if( empty( $data['data'] ) ) {
				throw new \Advertikon\Exception( 'Profile data are missing' );
			}

			if( empty( $data['id'] ) ) {
				throw new \Advertikon\Exception( 'Profile ID is missing' );
			}

			$id = $data['id'];

			if( false !== ( $depth = $this->a->add_profile_snapshot( $id, $data['data'] ) ) ) {
				$ret['success'] = 1;
				$ret['depth'] = $depth;
				$ret['current'] = $this->a->current_profile_snapshot_pointer( $id );

			} else {
				throw new \Advertikon\Exception( 'Unable to save snapshot' );
			}

		} catch ( \Advertikon\Exception $e ) {
			$ret['error'] = $e->getMessage();
		}

		$this->response->setOutput( json_encode( $ret ) );
	}

	/**
	 * Undo profile data action
	 * @return void
	 */
	public function undo() {
		$ret = array();

		try {

			if( ! isset( $this->request->request['id'] ) ) {
				throw new \Advertikon\Exception( 'Profile ID is missing' );
			}

			$id = $this->request->request['id'];

			$snapshot = $this->a->prev_profile_snapshot( $id );

			if( ! $snapshot ) {
				throw new \Advertikon\Exception( 'No data available' );
			}

			$ret['success'] = $this->profile_contents();

		} catch ( \Advertikon\Exception $e ) {
			$ret['error'] = $e->getMessage();
		}

		$this->response->setOutput( json_encode( $ret ) );
	}

	/**
	 * Redo profile data action
	 * @return void
	 */
	public function redo() {
		$ret = array();

		try {

			if( ! isset( $this->request->request['id'] ) ) {
				throw new \Advertikon\Exception( 'Profile ID is missing' );
			}

			$id = $this->request->request['id'];

			$snapshot = $this->a->next_profile_snapshot( $id );

			if( ! $snapshot ) {
				throw new \Advertikon\Exception( 'No data available' );
			}

			$ret['success'] = $this->profile_contents();

		} catch ( \Advertikon\Exception $e ) {
			$ret['error'] = $e->getMessage();
		}

		$this->response->setOutput( json_encode( $ret ) );
	}

	/**
	 * Fetch template data action
	 * @return void
	 */
	public function template() {
		$ret = array();
		$template_stores = array();

		try {

			if( ! isset( $this->request->request['template_id'] ) ) {
				throw new \Advertikon\Exception( $this->a->__( 'Template ID s missing' ) );
			}

			$template_id = $this->request->request['template_id'];

			$template = $this->a->get_mail_template( $template_id );

			if( ! $template ) {
				throw new \Advertikon\Exception( $this->a->__( 'Template\'s data missing' ) );
			}

			$str = '';

			if( ! empty( $template['description'] ) ) {
				$str .= $this->a->r()->render_info_box( $template['description'] );
			}

			$str .= $this->a->r()->render_form_group( array(
				'label' => $this->a->__( 'Manage template' ),
				'element' => $this->a->r( array(
						'type'    => 'buttongroup',
						'buttons' => array(
							'delete' =>
							array(
								'type'        => 'button',
								'button_type' => 'danger',
								'id'          => 'template-delete',
								'title'       => $this->a->__( 'Delete template' ),
								'icon'	      => 'fa-close',
								'custom_data' => 'data-url="' . $this->url->link(
									$this->a->type . '/' . $this->a->code . '/delete_template',
									'token=' . $this->session->data['token'],
									'SSL'
								) . '"' .
								( empty( $template['deletable'] ) ? ' disabled="disabled"' : '' ),
							),
							'save' =>
							array(
								'type'        => 'button',
								'button_type' => 'primary',
								'id'          => 'template-save',
								'title'       => $this->a->__( 'Save template' ),
								'icon'	      => 'fa-save',
								'custom_data' => 'data-url="' . $this->url->link(
									$this->a->type . '/' . $this->a->code . '/save_template',
									'token=' . $this->session->data['token'],
									'SSL'
								) . '"' .
								( ! $this->a->can_save_template_snapshot( $template_id ) ? ' disabled="disabled"' : '' ),
							),
							'undo' =>
							array(
								'type'        => 'button',
								'button_type' => 'primary',
								'id'          => 'template-undo',
								'title'       => $this->a->__( 'Undo changes' ),
								'icon'	      => 'fa-undo',
								'custom_data' => 'data-url="' . $this->url->link(
									$this->a->type . '/' . $this->a->code . '/undo_template',
									'token=' . $this->session->data['token'],
									'SSL'
								) . '"' .
								( ! $this->a->can_undo_template_snapshot( $template_id ) ? ' disabled="disabled"' : '' ),
							),
							'redo' =>
							array(
								'type'        => 'button',
								'button_type' => 'primary',
								'id'          => 'template-redo',
								'title'       => $this->a->__( 'Redo changes' ),
								'icon'	      => 'fa-undo fa-flip-horizontal',
								'custom_data' => 'data-url="' . $this->url->link(
									$this->a->type . '/' . $this->a->code . '/redo_template',
									'token=' . $this->session->data['token'],
									'SSL'
								) . '"' .
								( ! $this->a->can_redo_template_snapshot( $template_id ) ? ' disabled="disabled"' : '' ),
							),
						),
					)
				)
			) );

			$str .= $this->a->r()->render_form_group( array(
				'label'     => $this->a->__( 'Action hook' ),
				'label_for' => '',
				'cols'      => array( 'col-sm-2', 'col-sm-10' ),
				'element'   => $this->a->r( array(
					'type'         => 'inputgroup',
					'addon_before' => '<i class="fa fa-anchor"></i>',
					'element'      => array(
						'type'        => 'text',
						'class'       => 'form-control',
						'value'       => empty ( $template['path_hook'] ) ?
							$template['hook'] : $template['path_hook'],
						'custom_data' => 'readonly="readonly"',
					)
				) ),
				'description' => $this->a->__( '' ),
			) );

			$template_stores = $this->a->get_template_stores( $template );

			$can_be_added = array_diff_key( $this->a->get_stores(), $template_stores );

			if( $can_be_added ) {
				$options = array();
				foreach( $can_be_added as $be_added ) {
					$options[ $be_added['id'] ] = $be_added['name'];
				}

				$template_stores[] = array(
					'dropdown' => true,
					'id'       => 'add',
					'name'     => $this->a->__( 'Add store' ),
					'options'  => $options,
				);
			}

			$str .= $this->a->r()->render_panels_headers( array(
				'panels'    => $template_stores,
				'id_prefix' => 'store-',
				'class'     => 'store-tab-headers',
			) );

			$str .= $this->model->render_template_tab_body( $template, 'store-' );

			$ret['success'] = $str;

		} catch ( \Advertikon\Exception $e ) {
			$ret['error'] = $e->getMessage();
		}

		$this->response->setOutput( json_encode( $ret ) );
	}

	/**
	 * Add template store action
	 * @return void
	 */
	public function add_store() {
		$ret = array();

		try {

			if( ! isset( $this->request->request['store_id'] ) ) {
				throw new \Advertikon\Exception( 'Store ID is missing' );
			}

			$store_id = $this->request->request['store_id'];

			$stores = $this->a->get_stores();

			if( ! array_key_exists( $store_id, $stores ) ) {
				throw new \Advertikon\Exception( $this->a->__( 'Store with ID %s does not exist', $store_id ) );
			}

			if( ! isset( $this->request->request['template_id'] ) ) {
				throw new \Advertikon\Exception( $this->a->__( 'Template ID is missing' ) );
			}

			$template_id = $this->request->request['template_id'];

			$template = $this->a->get_mail_template( $template_id );

			if( ! $template ) {
				throw new \Advertikon\Exception( $this->a->__( 'Template data are missing' ) );
			}

			$profile = $this->a->get_template_profile( $template_id );

			if( ! $profile ) {
				throw new \Advertikon\Exception( $this->a->__( 'Profile data are missing' ) );
			}

			$snapshot = $this->a->current_template_snapshot( $template_id );
			$this->a->create_array_structure( $snapshot, $store_id );

			$this->a->add_template_snapshot( $template_id, $snapshot );

		} catch ( \Advertikon\Exception $e ) {
			$ret['error'] = $e->getMessage();
			$this->response->setOutput( json_encode( $ret ) );
			die;
		}

		$this->template();

	}

	/**
	 * Add template language action
	 * @return void
	 */
	public function add_lang() {
		$ret = array();

		try {

			if( empty( $this->request->request['lang_id'] ) ) {
				throw new \Advertikon\Exception( 'Language ID is missing' );
			}

			$lang_id = $this->request->request['lang_id'];

			$this->load->model( 'localisation/language' );
			$lang = $this->model_localisation_language->getLanguage( $lang_id );

			if( ! $lang ) {
				throw new \Advertikon\Exception( $this->a->__( 'Language with ID %s does not exist', $lang_id ) );
			}

			if( ! isset( $this->request->request['template_id'] ) ) {
				throw new \Advertikon\Exception( $this->a->__( 'Template ID is missing' ) );
			}

			$template_id = $this->request->request['template_id'];

			$profile = $this->a->get_template_profile( $template_id );

			if( ! $profile ) {
				throw new \Advertikon\Exception( $this->a->__( 'Profile data are missing' ) );
			}

			if( ! isset( $this->request->request['store_id'] ) ) {
				throw new \Advertikon\Exception( $this->a->__( 'Store ID is missing' ) );
			}

			$store_id = $this->request->request['store_id'];

			$snapshot = $this->a->current_template_snapshot( $template_id );

			if( false === ( $lang_code = strstr( $lang['code'], '-', true ) ) ) {
				$lang_code = $lang['code'];
			}

			$this->a->create_array_structure( $snapshot, $store_id . '/lang/'. $lang_code );
			$this->a->add_template_snapshot( $template_id, $snapshot );

		} catch ( \Advertikon\Exception $e ) {
			$ret['error'] = $e->getMessage();
			$this->response->setOutput( json_encode( $ret ) );
			die;
		}

		$this->template();
	}

	/**
	 * Delete template store action
	 * @return void
	 */
	public function delete_store() {
		$ret = array();

		try {

			if( ! isset( $this->request->request['store_id'] ) ) {
				throw new \Advertikon\Exception( 'Store ID is missing' );
			}

			$store_id = $this->request->request['store_id'];

			if( ! isset( $this->request->request['template_id'] ) ) {
				throw new \Advertikon\Exception( $this->a->__( 'Template ID is missing' ) );
			}

			$template_id = $this->request->request['template_id'];

			$snapshot = $this->a->current_template_snapshot( $template_id );
			if( isset( $snapshot[ $store_id ] ) ) {
				unset( $snapshot[ $store_id ] );
				$this->a->add_template_snapshot( $template_id, $snapshot );
			}

		} catch ( \Advertikon\Exception $e ) {
			$ret['error'] = $e->getMessage();
			$this->response->setOutput( json_encode( $ret ) );	
			die;
		}

		$this->template();

	}

	/**
	 * Delete template language action
	 * @return void
	 */
	public function delete_lang() {
		$ret = array();

		try {

			if( empty( $this->request->request['lang_id'] ) ) {
				throw new \Advertikon\Exception( 'Language ID is missing' );
			}

			$lang_id = $this->request->request['lang_id'];

			if( ! isset( $this->request->request['store_id'] ) ) {
				throw new \Advertikon\Exception( 'Store ID is missing' );
			}

			$store_id = $this->request->request['store_id'];

			if( ! isset( $this->request->request['template_id'] ) ) {
				throw new \Advertikon\Exception( $this->a->__( 'Template ID is missing' ) );
			}

			$template_id = $this->request->request['template_id'];

			$snapshot = $this->a->current_template_snapshot( $template_id );

			if( isset( $snapshot[ $store_id ]['lang'][ $lang_id ] ) ) {
				unset( $snapshot[ $store_id ]['lang'][ $lang_id ] );
				$this->a->add_template_snapshot( $template_id, $snapshot );
			}

		} catch ( \Advertikon\Exception $e ) {
			$ret['error'] = $e->getMessage();
			$this->response->setOutput( json_encode( $ret ) );
			die;
		}

		$this->template();

	}

	/**
	 * Save template action
	 * @return void
	 */
	public function save_template() {
		$ret = array();

		try {

			if( ! $this->user->hasPermission( 'modify', $this->a->type . '/' . $this->a->code ) ) {
				throw new \Advertikon\Exception( $this->a->__( 'You have no permissions to modify extension data' ) );
			}

			if( ! isset( $this->request->request['template_id'] ) ) {
				throw new \Advertikon\Exception( $this->a->__( 'Template ID is missing' ) );
			}

			$template_id = $this->request->request['template_id'];

			if( ! $this->a->has_template_snapshots( $template_id ) ) {
				throw new \Advertikon\Exception( 'Nothing to save' );
			}

			$data = $this->a->leave_current_template_snapshot( $template_id );

			if( ! $data ) {
				throw new \Advertikon\Exception( 'Empty dataset' );
			}

			if( $this->model->save_template_data( $template_id, $data ) > 0 ) {
				$ret['success'] = 1;
			} else {
				throw new \Advertikon\Exception( $this->a->__( 'Unable to save template data' ) );
			}

		} catch( \Advertikon\Exception $e ) {
			$ret['error'] = $e->getMessage();
		}

		$this->response->setOutput( json_encode( $ret ) );
	}

	/**
	 * Undo template data action
	 * @return void
	 */
	public function undo_template() {
		$ret = array();

		try {

			if( ! isset( $this->request->request['template_id'] ) ) {
				throw new \Advertikon\Exception( $this->a->__( 'Template ID is missing' ) );
			}

			$template_id = $this->request->request['template_id'];
			$this->a->prev_template_snapshot( $template_id );
		} catch ( \Advertikon\Exception $e ) {
			$ret['error'] = $e->getMessage();
			$this->response->setOutput( json_encode( $ret ) );
			die;
		}

		$this->template();
	}

	/**
	 * Redo template data action
	 * @return void
	 */
	public function redo_template() {
		$ret = array();

		try {

			if( ! isset( $this->request->request['template_id'] ) ) {
				throw new \Advertikon\Exception( $this->a->__( 'Template ID is missing' ) );
			}

			$template_id = $this->request->request['template_id'];
			$this->a->next_template_snapshot( $template_id );

		} catch ( \Advertikon\Exception $e ) {
			$ret['error'] = $e->getMessage();
			$this->response->setOutput( json_encode( $ret ) );
			die;
		}

		$this->template();
	}

	/**
	 * Save template data snapshot action
	 * @return void
	 */
	public function save_template_tmp() {
		$ret = array();

		try {

			if( ! isset( $this->request->request['template_id'] ) ) {
				throw new \Advertikon\Exception( $this->a->__( 'Template ID is missing' ) );
			}

			$template_id = $this->request->request['template_id'];

			if( ! isset( $this->request->request['data'] ) ) {
				throw new \Advertikon\Exception( $this->a->__( 'Missing template data' ) );
			}

			$data = $this->request->request['data'];
			$this->a->add_template_snapshot( $template_id, $data );

			$ret['success'] = 1;

		} catch ( \Advertikon\Exception $e ) {
			$ret['error'] = $e->getMessage();
		}
		
		$this->response->setOutput( json_encode( $ret ) );
	}

	/**
	 * Set profile action
	 * @return void
	 */
	public function set_profile() {
		$ret = array();

		try {

			if( ! isset( $this->request->request['template_id'] ) ) {
				throw new \Advertikon\Exception( $this->a->__( 'Template ID is missing' ) );
			}

			$template_id = $this->request->request['template_id'];

			if( ! isset( $this->request->request['store_id'] ) ) {
				throw new \Advertikon\Exception( $this->a->__( 'Store ID is missing' ) );
			}

			$store_id = $this->request->request['store_id'];

			if( isset( $this->request->request['lang_id'] ) ) {
				$lang_id = $this->request->request['lang_id'];
			}

			if( ! isset( $this->request->request['profile_id'] ) ) {
				throw new \Advertikon\Exception( $this->a->__( 'Profile ID is missing' ) );
			}

			$profile_id = $this->request->request['profile_id'];

			$snapshot = $this->a->current_template_snapshot( $template_id );

			if( isset( $lang_id ) ) {
				if( -1 == $profile_id ) {
					unset( $snapshot[ $store_id ]['lang'][ $lang_id ][ 'profile' ] );

				} else {
					$templ = &$this->a->create_array_structure( $snapshot, $store_id . '/lang/' . $lang_id . '/profile' );
					$templ = $profile_id;
				}

			} else {
				if( -1 == $profile_id ) {
					unset( $snapshot[ $store_id ][ 'profile' ] );

				} else {
					$templ = &$this->a->create_array_structure( $snapshot, $store_id . '/profile' );
					$templ = $profile_id;
				}
			}

			$this->a->add_template_snapshot( $template_id, $snapshot );

		} catch ( \Advertikon\Exception $e ) {
			$ret['error'] = $e->getMessage();
			$this->response->setOutput( json_encode( $ret ) );
			die;
		}

		$this->template();
	}

	/**
	 * Send tens email action
	 * @return void
	 */
	public function send_email() {
		$ret = array();

		try {

			if( empty( $this->request->request['to'] ) ) {
				throw new \Advertikon\Exception( $this->a->__( 'Email recipient is missing' ) );
			}

			$to = $this->request->request['to'];

			if( ! filter_var( $to, FILTER_VALIDATE_EMAIL ) ) {
				throw new \Advertikon\Exception( $this->a->__( 'Invalid Email format' ) );
			}

			if( ! isset( $this->request->request['template_id'] ) ) {
				throw new \Advertikon\Exception( $this->a->__( 'Template ID is missing' ) );
			}

			$template_id = $this->request->request['template_id'];

			define( 'TEST_TEMPLATE', $template_id );
			define( 'PREVIEW', 1 );

			if( $this->a->send_email( array(
				'html'    => '',
				'to'      => $to,
				'subject' => $this->a->__( 'Mail templates manager: test email' ),
			) ) ) {
				$ret['success'] = $this->a->__( 'Mail has been successfully sent' );

			} else {
				throw new \Advertikon\Exception( $this->a->__( 'Failed to send email' ) );
			}

			
		} catch ( \Advertikon\Exception $e ) {
			$ret['error'] = $e->getMessage();
		}

		$this->response->setOutput( json_encode( $ret ) );
	}

	/**
	 * Renders shortcodes tab
	 * @return string
	 */
	public function render_shortcodes() {
		$panels = array(

			// Vitrine tab
			'pane-vitrine' => array(
				'name'   => $this->a->__( 'Vitrine' ),
				'active' => 1,
				'icon'   => 'fa-image fa-2x',
				'class'  => 'sway-able',
			),

			// Social tab
			'pane-social'  => array(
				'name' => $this->a->__( 'Social' ),
				'icon' => 'fa-facebook-official fa-2x',
				'class'  => 'sway-able',
			),

			// Button tab
			'pane-button'  => array(
				'name' => $this->a->__( 'Button' ),
				'icon' => 'fa-hand-pointer-o fa-2x',
				'class'  => 'sway-able',
			),

			// QR Code tab
			'pane-qrcode'  => array(
				'name' => $this->a->__( 'QR Code' ),
				'icon' => 'fa-qrcode fa-2x',
				'class'  => 'sway-able',
			),

			// Invoice table tab
			'pane-invoice'  => array(
				'name' => $this->a->__( 'Invoice' ),
				'icon' => 'fa-clone fa-2x',
				'class'  => 'sway-able',
			),
		);

		$ret = '';
		$ret .= $this->a->r()->render_panels_headers( array(
			'panels' => $panels,
		) );

		$ret .=
		'<div class="tab-content shortcodes-manager">';

		$ret .= $this->render_vitrine_content();
		$ret .= $this->render_social_content();
		$ret .= $this->render_button_content();
		$ret .= $this->render_qrcode_content();
		$ret .= $this->render_invoice_content();

		$ret .= 
		'</div>';

		return $ret;
	}

	/**
	 * Renders shortcode vitrine tab's contents
	 * @param int|null $shortcode_id Shortcode ID
	 * @return string
	 */
	public function render_vitrine_content( $shortcode_id = null ) {
		$shortcode = null;

		if( is_null( $shortcode_id ) && isset( $this->request->request['shortcode_id'] ) ) {
			$shortcode_id = $this->request->request['shortcode_id'];
		}

		if( $shortcode_id ) {
			$shortcode = $this->a->get_shortcode( $shortcode_id );
		}

		$ret  = '';

		$ret .=
		'<div class="tab-pane" id="pane-vitrine">';

		$ret .= $this->a->r()->render_info_box( 'Vitrine shortcode represents concise information about  the product (thumbnail image, description, price). You can group in the set as many products as you wish to, as well as choose category to show (bestseller, special etc)' );

		// Saved shortcodes
		$shortcodes = array( '-1' => $this->a->__( 'Create new' ) );
		foreach( $this->a->get_shortcodes_by_category( 'vitrine' ) as $id => $e_shortcode ) {
			if( ! isset( $e_shortcode['data']['type'] ) ) {
				continue;
			}

			$shortcodes[ $id ] = empty( $e_shortcode['data']['name'] ) ?
				'vitrine-' . $e_shortcode['data']['type'] . '-' . $id : $e_shortcode['data']['name'];
		}

		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Load saved shortcode' ),
			'element' => $this->a->r( array(
				'type'   => 'select',
				'value'  => $shortcodes,
				'active' => isset( $shortcode['shortcode_id'] ) ? $shortcode['shortcode_id'] : '',
				'class'  => 'form-control shortcode-data select-shortcode',
				'id'     => 'vitrine-shortcode_id',
			) ),
			'description' => $this->a->__( 'Choose existing shortcode or create new' ),
		) );

		// Name
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Name' ),
			'tooltip' => $this->a->__( 'Shortcode name to distinguish it from other' ),
			'element' => $this->a->r( array(
				'type'  => 'text',
				'value' => isset( $shortcode['data']['name'] ) ? $shortcode['data']['name'] : '',
				'class' => 'form-control shortcode-data',
				'id'    => 'vitrine-name',
			) )
		) );

		// Vitrine type
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Vitrine type' ),
			'element' => $this->a->r( array(
				'type'   => 'select',
				'value'  => $this->model->get_vitrine_types(),
				'id'     => 'vitrine-type',
				'active' => isset( $shortcode['data']['type'] ) ?
					$shortcode['data']['type'] : '',
				'class'  => 'form-control shortcode-data',
			) )
		) );

		// Arbitrary product
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Arbitrary products' ),
			'element' => $this->a->r( array(
				'type'       => 'select',
				'value'      => ! empty( $shortcode['data']['product']['arbitrary'] ) ?
					$this->model->get_product_for_select( $shortcode['data']['product']['arbitrary'] ) : array(),

				'active'      => isset( $shortcode['data']['product']['arbitrary'] ) ?
					$shortcode['data']['product']['arbitrary'] :'',
				'class'       => 'form-control shortcode-data ',
				'id'          => 'vitrine-product-arbitrary',
				'custom_data' => 'multiple="multiple"',
			) )
		) );

		// Element height
		$element_height_value = array( 'value' => 200 );
		if( isset( $shortcode['data']['element']['height'] ) ) {
			$element_height_value['value'] = $shortcode['data']['element']['height'];
		}

		$element_height_value['units'] = 'px';

		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Element height' ),
			'element' => $this->model->render_dimension( array(
				'values' => 'px',
				'titles' => $this->a->__( 'Element height' ),
				'value'  => $element_height_value,
				'id'     => 'vitrine-element-height',
				'maxes'  => '500',
			) ),
			'description' => $this->a->__( 'The height of vitrine element. Use it to align vitrine elements' ),
		) );

		// Element width
		$element_width_value = array( 'value' => 200 );
		if( isset( $shortcode['data']['element']['width'] ) ) {
			$element_width_value['value'] = $shortcode['data']['element']['width'];
		}

		$element_width_value['units'] = 'px';

		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Element width' ),
			'element' => $this->model->render_dimension( array(
				'values' => 'px',
				'titles' => $this->a->__( 'Element width' ),
				'value'  => $element_width_value,
				'id'     => 'vitrine-element-width',
				'maxes'  => '500',
			) ),
		) );

		// Title
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Title' ),
			'element' => $this->a->r( array(
				'type'  => 'text',
				'value' => isset( $shortcode['data']['title']['text'] ) ?
					$shortcode['data']['title']['text'] : $this->a->__( 'You may be interested in' ),
				'class' => 'form-control shortcode-data',
				'id'    => 'vitrine-title-text',
			) )
		) );

		// Title font color
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Title text color' ),
			'element' => $this->a->r( array(
				'type'  => 'color',
				'value' => isset( $shortcode['data']['title']['color'] ) ?
					$shortcode['data']['title']['color'] : '#000000',
				'class' => 'form-control iris shortcode-data',
				'id'    => 'vitrine-title-color',
			) ),
			'description' => $this->a->__( 'Caption to show above the vitrine' ),
		) );

		// Title text font height
		$title_text_height_value = array( 'value' => 20 );
		if( isset( $shortcode['data']['title']['height'] ) ) {
			$title_text_height_value['value'] = $shortcode['data']['title']['height'];
		}

		$title_text_height_value['units'] = 'px';

		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Title text height' ),
			'element' => $this->model->render_dimension( array(
				'values' => 'px',
				'titles' => $this->a->__( 'Title text height' ),
				'value'  => $title_text_height_value,
				'id'     => 'vitrine-title-height',
				'maxes'  => '30',
			) )
		) );

		// Title align
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Title text align' ),
			'element' => $this->a->r( array(
				'type' => 'inputgroup',
				'element' => array(
					'type'   => 'select',
					'value'  => $this->model->get_align_values(),
					'id'     => 'vitrine-title-align',
					'active' => isset( $shortcode['data']['title']['align'] ) ?
						$shortcode['data']['title']['align' ] : '',
					'class'  => 'form-control shortcode-data',
				),
				'addon_before' => '<i class="fa fa-align-' .
					( isset( $shortcode['data']['title']['align'] ) ? $shortcode['data']['title']['align'] : 'left' ) .
					'"></i>',
			) )
		) );

		// Vitrine number of products
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Number of products' ),
			'element' => $this->a->r( array(
				'type'  => 'number',
				'value' => isset( $shortcode['data']['number'] ) ?
					$shortcode['data']['number'] : '3',
				'id'    => 'vitrine-number',
				'class' => 'form-control shortcode-data',
			) )
		) );

		// Image width
		$img_width_value = array( 'value' => 100 );
		if( isset( $shortcode['data']['img']['width'] ) ) {
			$img_width_value['value'] = $shortcode['data']['img']['width'];
		}

		$img_width_value['units'] = 'px';

		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Icon width' ),
			'element' => $this->model->render_dimension( array(
				'values' => 'px',
				'titles' => $this->a->__( 'Icon width' ),
				'value'  => $img_width_value,
				'id'     => 'vitrine-img-width',
				'maxes'  => '300',
				'max'    => 300,
			) )
		) );

		// Image header height
		$img_header_width_value = array( 'value' => 0 );
		if( isset( $shortcode['data']['img']['header']['height'] ) ) {
			$img_header_height_value['value'] = $shortcode['data']['img']['header']['height'];
		}

		$img_header_height_value['units'] = 'px';

		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Icon header height' ),
			'element' => $this->model->render_dimension( array(
				'values' => 'px',
				'titles' => $this->a->__( 'Icon header height' ),
				'value'  => $img_header_height_value,
				'id'     => 'vitrine-img-header-height',
				'maxes'  => '300',
				'max'    => 300,
			) )
		) );

		// Vitrine contains product set, related to some specific product
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Relate to the product' ),
			'element' => $this->a->r()->render_fancy_checkbox( array(
				'value'  => ! empty( $shortcode['data']['related'] ) ? '1' : '0',
				'class'  => 'shortcode-data',
				'id'     => 'vitrine-related',
			) ),
			'description' => $this->a->__( 'Defines whether vitrine products should be related to some specific product (the same category or developer), deduced from the email context' ),
		) );

		// Embed images
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Embed product icons' ),
			'element' => $this->a->r()->render_fancy_checkbox( array(
				'value'  => ! empty( $shortcode['data']['img']['embed'] ) ? '1' : '0',
				'class'  => 'shortcode-data',
				'id'     => 'vitrine-img-embed',
			) ),
			'description' => $this->a->__( 'If images will be embedded into the email body, they will be displayed even if an email client blocks images by default' ),
		) );

		// Default product
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Default product' ),
			'element' => $this->a->r( array(
				'type'   => 'select',
				'value'  => ! empty( $shortcode['data']['product']['default'] ) ?
					$this->model->get_product_for_select( $shortcode['data']['product']['default'] ) : array(),

				'active' => isset( $shortcode['data']['product']['default'] ) ? $shortcode['data']['product']['default'] : '',
				'class'  => 'form-control shortcode-data ',
				'id'     => 'vitrine-product-default',
			) ),
			'description' => $this->a->__( 'If related product cannot be determined from the email context, the default product will be used' ),
		) );

		// Shortcode
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Shortcode' ),
			'element' => $this->a->r( array(
				'type'        => 'text',
				'value'       => $shortcode ? $this->a->get_shortcode_name( $shortcode ) : '',
				'class'       => 'form-control clipboard',
				'custom_data' => 'readonly',
			) ),
			'description' => $this->a->__( 'To use shortcode copy this field value and paste in any field, which supports shortcodes' ),
		) );

		// Save shortcode
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Manage shortcode' ),
			'element' => $this->a->r( array(
				'type'        => 'buttongroup',
				'buttons' => array(
					array(
						'type'        => 'button',
						'id'          => 'save-vitrine-shortcode',
						'class'       => 'save-shortcode',
						'text_before' => $this->a->__( 'Save' ),
						'button_type' => 'primary',
						'icon'        => 'fa-save',
					),
					array(
						'type'        => 'button',
						'id'          => 'delete-vitrine-shortcode',
						'class'       => 'delete-shortcode',
						'text_before' => $this->a->__( 'Delete' ),
						'icon'        => 'fa-remove',
					)
				),
			) ),
			'description' => $this->a->__( 'To see made changes on the preview screen don\'t forget to save shortcode and refresh preview' ),
		) );


		$ret .=
		'</div>'; // .tab-pane

		return $ret;
	}

	/**
	 * Renders contents of social shortcode manager tab
	 * @param type $shortcode_id Shortcode ID, optional
	 * @return string
	 */
	public function render_social_content( $shortcode_id = null ) {
		$ret = '';

		$ret .=
		'<div class="tab-pane" id="pane-social">';

		$shortcode = null;

		if( is_null( $shortcode_id ) && isset( $this->request->request['shortcode_id'] ) ) {
			$shortcode_id = $this->request->request['shortcode_id'];
		}

		if( $shortcode_id ) {
			$shortcode = $this->a->get_shortcode( $shortcode_id );
		}

		$ret .= $this->a->r()->render_info_box( 'Social shortcode represents set of social media icons, linked with the corresponding accounts' );

		// Saved shortcodes
		$shortcodes = array( '-1' => $this->a->__( 'Create new' ) );
		foreach( $this->a->get_shortcodes_by_category( 'social' ) as $id => $e_shortcode ) {
			$shortcodes[ $id ] = empty( $e_shortcode['data']['name'] ) ?
				'social-' . '-' . $id : $e_shortcode['data']['name'];
		}

		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Load saved shortcode' ),
			'element' => $this->a->r( array(
				'type'   => 'select',
				'value'  => $shortcodes,
				'active' => isset( $shortcode['shortcode_id'] ) ? $shortcode['shortcode_id'] : '',
				'class'  => 'form-control shortcode-data select-shortcode',
				'id'     => 'social-shortcode_id',
			) )
		) );

		// Name
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Name' ),
			'tooltip' => $this->a->__( 'Shortcode name to distinguish it from other' ),
			'element' => $this->a->r( array(
				'type'  => 'text',
				'value' => isset( $shortcode['data']['name'] ) ? $shortcode['data']['name'] : '',
				'class' => 'form-control shortcode-data',
				'id'    => 'social-name',
			) )
		) );

		// Social appearance
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Icons appearance' ),
			'element' => $this->a->r( array(
				'type'   => 'select',
				'value'  => $this->model->get_social_appearances(),
				'id'     => 'social-appearance',
				'active' => isset( $shortcode['data']['appearance'] ) ?
					$shortcode['data']['appearance'] : '',
				'class'  => 'form-control shortcode-data',
			) ),
			'tooltip' => $this->a->__(
				'You can add your own set of social icons. Just create a new folder in %s and put in there a list of next icons: %s. All icons are optional',
				DIR_IMAGE . 'social',
				implode( ', ', array_map( function( $i ){ return $i . '.png'; }, array_keys( $this->a->social_set ) ) )
			),
		) );

		// Icon height
		$icon_height_value = array( 'value' => 20 );
		if( isset( $shortcode['data']['icon']['height'] ) ) {
			$icon_height_value['value'] = $shortcode['data']['icon']['height'];
		}

		$icon_height_value['units'] = 'px';

		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Icon height' ),
			'element' => $this->model->render_dimension( array(
				'values' => 'px',
				'titles' => $this->a->__( 'Icon height' ),
				'value'  => $icon_height_value,
				'id'     => 'social-icon-height',
				'maxes'  => '100',
			) )
		) );

		// Icon margins
		$icon_margin_value = array( 'value' => 0 );
		if( isset( $shortcode['data']['icon']['margin'] ) ) {
			$icon_margin_value['value'] = $shortcode['data']['icon']['margin'];
		}

		$icon_margin_value['units'] = 'px';

		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Icon margin' ),
			'element' => $this->model->render_dimension( array(
				'values' => 'px',
				'titles' => $this->a->__( 'Icon margin' ),
				'value'  => $icon_margin_value,
				'id'     => 'social-icon-margin',
				'maxes'  => '100',
			) )
		) );

		$ret .= $this->a->r()->render_info_box( 'Enter an  account URL (starting with http(s)://) into the corresponding input field. If you don\'t want the icon to appear in the set - disable it by clicking on "eye" button' );

		// Render social items controls
		$ret .= $this->render_social_items( $shortcode );

		// Title text
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Title' ),
			'element' => $this->a->r( array(
				'type'  => 'text',
				'value' => isset( $shortcode['data']['title']['text'] ) ?
					$shortcode['data']['title']['text'] : ' ',
				'class' => 'form-control shortcode-data',
				'id'    => 'social-title-text',
			) ),
			'description' => $this->a->__( 'An optional caption which will appear above the icon set' ),
		) );

		// Title font color
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Title text color' ),
			'element' => $this->a->r( array(
				'type'  => 'color',
				'value' => isset( $shortcode['data']['title']['color'] ) ?
					$shortcode['data']['title']['color'] : '#000000',
				'class' => 'form-control iris shortcode-data',
				'id'    => 'social-title-color',
			) )
		) );

		// Title text font height
		$title_text_height_value = array( 'value' => 20 );
		if( isset( $shortcode['data']['title']['height'] ) ) {
			$title_text_height_value['value'] = $shortcode['data']['title']['height'];
		}

		$title_text_height_value['units'] = 'px';

		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Title text height' ),
			'element' => $this->model->render_dimension( array(
				'values' => 'px',
				'titles' => $this->a->__( 'Title text height' ),
				'value'  => $title_text_height_value,
				'id'     => 'social-title-height',
				'maxes'  => '30',
			) )
		) );

		// Embed images
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Embed icons' ),
			'element' => $this->a->r()->render_fancy_checkbox( array(
				'value'  => ! empty( $shortcode['data']['icon']['embed'] ) ? '1' : '0',
				'class'  => 'shortcode-data',
				'id'     => 'social-icon-embed',
			) ),
			'description' => $this->a->__( 'If images will be embedded into the email body, they will be displayed even if an email client blocks images by default' ),
		) );

		// Title align
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Content align' ),
			'element' => $this->a->r( array(
				'type' => 'inputgroup',
				'element' => array(
					'type'   => 'select',
					'value'  => $this->model->get_align_values(),
					'id'     => 'social-title-align',
					'active' => isset( $shortcode['data']['title']['align'] ) ?
						$shortcode['data']['title']['align' ] : '',
					'class'  => 'form-control shortcode-data',
				),
				'addon_before' => '<i class="fa fa-align-' .
					( isset( $shortcode['data']['title']['align'] ) ? $shortcode['data']['title']['align'] : 'left' ) .
					'"></i>',
			) ),
			'description' => 'Alignment of the icon set',
		) );

		// Shortcode
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Shortcode' ),
			'element' => $this->a->r( array(
				'type'        => 'text',
				'value'       => $shortcode ? $this->a->get_shortcode_name( $shortcode ) : '',
				'class'       => 'form-control clipboard',
				'custom_data' => 'readonly',
			) ),
			'description' => $this->a->__( 'To use shortcode copy this field value and paste in any field, which supports shortcodes' ),
		) );

		// Save shortcode
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Manage shortcode' ),
			'element' => $this->a->r( array(
				'type'        => 'buttongroup',
				'buttons' => array(
					array(
						'type'        => 'button',
						'id'          => 'save-social-shortcode',
						'class'       => 'save-shortcode',
						'text_before' => $this->a->__( 'Save' ),
						'button_type' => 'primary',
						'icon'        => 'fa-save',
					),
					array(
						'type'        => 'button',
						'id'          => 'delete-social-shortcode',
						'class'       => 'delete-shortcode',
						'text_before' => $this->a->__( 'Delete' ),
						'icon'        => 'fa-remove',
					)
				),
			) ),
			'description' => $this->a->__( 'To see made changes on the preview screen don\'t forget to save shortcode and refresh preview' ),
		) );


		$ret .=
		'</div>'; // .tab-pane

		return $ret;
	}

	/**
	 * Returns socials items set
	 * @param array $shortcode Social shortcode data 
	 * @param array|null $socials Socials items list, if omitted all items will be returned
	 * @return string
	 */
	public function render_social_items( $shortcode, $socials = null ) {
		if( is_null( $socials ) ) {
			$socials = array_keys( $this->a->social_set );
		}

		$ret = '';

		foreach( $socials as $social ) {
			if( array_key_exists( $social, $this->a->social_set ) ) {
				$ret .= $this->a->r()->render_form_group( array(
					'label'   => '&nbsp;',
					'element' => $this->a->r( array(
						'type'        => 'inputgroup',
						'element'     => array(
							'type'        => 'text',
							'class'       => 'form-control shortcode-data',
							'placeholder' => $this->a->social_set[ $social ]['placeholder'],
							'id'          => 'social-item-' . $social . '-url',
							'value'       => isset( $shortcode['data']['item'][ $social ]['url'] ) ?
								 $shortcode['data']['item'][ $social ]['url'] : '',
						),
						'addon_before' => '<i class="fa ' . $this->a->social_set[ $social ]['icon'] .
							'" title="' . $this->a->social_set[ $social ]['title'] . '"></i>',
						'addon_after' => array(
							'type'        => 'button',
							'custom_data' => 'data-values="1,0" ' .
								'data-value="' . ( isset( $shortcode['data']['item'][ $social ]['status'] ) ?
								$shortcode['data']['item'][ $social ]['status'] : 0 ) . '"' .
								'data-titles="' . $this->a->__( 'Enabled' ) . ',' . $this->a->__( 'Disabled') . '" ' .
								'data-icons="fa-eye, fa-eye-slash" ',
							'class'       => 'switchable social-toggle shortcode-data',
							'icon'        => 'fa-eye',
							'id'          => 'social-item-' . $social . '-status',
						),
					) ),
					'description' => $this->a->social_set[ $social ]['placeholder'],
				) );
			}
		}

		return $ret;
	}

	/**
	 * Returns shortcode button management tab contents
	 * @param int $shortcode_id Shortcode iD, optional
	 * @return string
	 */
	public function render_button_content( $shortcode_id = null ) {
		$ret = '';

		$ret .=
		'<div class="tab-pane" id="pane-button">';

		$shortcode = null;

		if( is_null( $shortcode_id ) && isset( $this->request->request['shortcode_id'] ) ) {
			$shortcode_id = $this->request->request['shortcode_id'];
		}

		if( $shortcode_id ) {
			$shortcode = $this->a->get_shortcode( $shortcode_id );
		}

		// Saved shortcodes
		$shortcodes = array( '-1' => $this->a->__( 'Create new' ) );
		foreach( $this->a->get_shortcodes_by_category( 'button' ) as $id => $e_shortcode ) {
			$shortcodes[ $id ] = empty( $e_shortcode['data']['name'] ) ?
			'button-' . '-' . $id : $e_shortcode['data']['name'];
		}

		$ret .= $this->a->r()->render_info_box( 'The Button shortcode renders a "Call to action button"' );

		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Load saved shortcode' ),
			'element' => $this->a->r( array(
				'type'   => 'select',
				'value'  => $shortcodes,
				'active' => isset( $shortcode['shortcode_id'] ) ? $shortcode['shortcode_id'] : '',
				'class'  => 'form-control shortcode-data select-shortcode',
				'id'     => 'button-shortcode_id',
			) )
		) );

		// Name
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Name' ),
			'tooltip' => $this->a->__( 'Shortcode name to distinguish it from other' ),
			'element' => $this->a->r( array(
				'type'  => 'text',
				'value' => isset( $shortcode['data']['name'] ) ? $shortcode['data']['name'] : '',
				'class' => 'form-control shortcode-data',
				'id'    => 'button-name',
			) )
		) );

		// Text
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Button text' ),
			'element' => $this->a->r( array(
				'type'  => 'text',
				'value' => isset( $shortcode['data']['caption']['text'] ) ?
					$shortcode['data']['caption']['text'] : '',
				'class' => 'form-control shortcode-data',
				'id'    => 'button-caption-text',
			) ),
			'description' => $this->a->__( 'Button caption' ),
		) );

		// Text color
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Button text color' ),
			'element' => $this->a->r( array(
				'type'  => 'color',
				'value' => isset( $shortcode['data']['caption']['color'] ) ?
					$shortcode['data']['caption']['color'] : '#000000',
				'class' => 'form-control iris shortcode-data',
				'id'    => 'button-caption-color',
			) )
		) );

		// Text font height
		$text_height_value = array( 'value' => 16 );
		if( isset( $shortcode['data']['caption']['height'] ) ) {
			$text_height_value['value'] = $shortcode['data']['caption']['height'];
		}

		$text_height_value['units'] = 'px';

		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Button text height' ),
			'element' => $this->model->render_dimension( array(
				'values' => 'px',
				'titles' => $this->a->__( 'Button text height' ),
				'value'  => $text_height_value,
				'id'     => 'button-caption-height',
				'maxes'  => '60',
			) )
		) );

		// Background color
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Button color' ),
			'element' => $this->a->r( array(
				'type'  => 'color',
				'value' => isset( $shortcode['data']['color'] ) ?
					$shortcode['data']['color'] : '#0000ff',
				'class' => 'form-control iris shortcode-data',
				'id'    => 'button-color',
			) ),
			'description' => $this->a->__( 'Button fill color' ),
		) );

		// Width
		$width_value = array( 'value' => 100 );
		if( isset( $shortcode['data']['width'] ) ) {
			$width_value['value'] = $shortcode['data']['width'];
		}

		$width_value['units'] = 'px';

		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Width' ),
			'element' => $this->model->render_dimension( array(
				'values' => 'px',
				'titles' => $this->a->__( 'Button width' ),
				'value'  => $width_value,
				'id'     => 'button-width',
				'maxes'  => '1000',
			) ),
		) );

		// 100% width
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Full width' ),
			'element' => $this->a->r()->render_fancy_checkbox( array(
				'value' => ! empty( $shortcode['data']['fullwidth'] ) ? '1' : '0',
				'class'  => 'shortcode-data',
				'id'     => 'button-fullwidth',
			) ),
			'description' => $this->a->__( 'Defines whether the button should occupy all the available width' ),
		) );

		// Height
		$height_value = array( 'value' => 60 );
		if( isset( $shortcode['data']['height'] ) ) {
			$height_value['value'] = $shortcode['data']['height'];
		}

		$height_value['units'] = 'px';

		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Height' ),
			'element' => $this->model->render_dimension( array(
				'values' => 'px',
				'titles' => $this->a->__( 'Button height' ),
				'value'  => $height_value,
				'id'     => 'button-height',
				'maxes'  => '500',
			) )
		) );

		// Text font height
		$padding_value = array( 'value' => 5 );
		if( isset( $shortcode['data']['padding'] ) ) {
			$padding_value['value'] = $shortcode['data']['padding'];
		}

		$padding_value['units'] = 'px';

		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Button padding' ),
			'element' => $this->model->render_dimension( array(
				'values' => 'px',
				'titles' => $this->a->__( 'Button padding' ),
				'value'  => $padding_value,
				'id'     => 'button-padding',
				'maxes'  => '100',
			) )
		) );

		// Border color
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Border color' ),
			'element' => $this->a->r( array(
				'type'  => 'color',
				'value' => isset( $shortcode['data']['border']['color'] ) ?
					$shortcode['data']['border']['color'] : '#0000ff',
				'class' => 'form-control iris shortcode-data',
				'id'    => 'button-border-color',
			) )
		) );

		// Border width
		$border_width_value = array( 'value' => 1 );
		if( isset( $shortcode['data']['border']['width'] ) ) {
			$border_width_value['value'] = $shortcode['data']['border']['width'];
		}

		$border_width_value['units'] = 'px';

		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Border width' ),
			'element' => $this->model->render_dimension( array(
				'values' => 'px',
				'titles' => $this->a->__( 'Border width' ),
				'value'  => $border_width_value,
				'id'     => 'button-border-width',
				'maxes'  => '30',
			) )
		) );

		// Border radius
		$border_radius_value = array( 'value' => 3 );
		if( isset( $shortcode['data']['border']['radius'] ) ) {
			$border_radius_value['value'] = $shortcode['data']['border']['radius'];
		}

		$border_radius_value['units'] = 'px';

		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Border radius' ),
			'element' => $this->model->render_dimension( array(
				'values' => 'px',
				'titles' => $this->a->__( 'Border radius' ),
				'value'  => $border_radius_value,
				'id'     => 'button-border-radius',
				'maxes'  => '100',
			) )
		) );

		// Href
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Call to action URl' ),
			'element' => $this->a->r( array(
				'type'        => 'text',
				'value'       => isset( $shortcode['data']['url'] ) ?
					$shortcode['data']['url'] : ' ',
				'class'       => 'form-control shortcode-data',
				'id'          => 'button-url'
			) ),
			'description' => $this->a->__( 'Absolute URL - starting with http(s)://. Can be shortcode, which generates a link'),
		) );

		// Button align
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Button align' ),
			'element' => $this->a->r( array(
				'type' => 'inputgroup',
				'element' => array(
					'type'   => 'select',
					'value'  => $this->model->get_align_values(),
					'id'     => 'button-align',
					'active' => isset( $shortcode['data']['align'] ) ?
						$shortcode['data']['align' ] : 'center',
					'class'  => 'form-control shortcode-data',
				),
				'addon_before' => '<i class="fa fa-align-' .
					( isset( $shortcode['data']['align'] ) ? $shortcode['data']['align'] : 'center' ) .
					'"></i>',
			) ),
			'description' => 'Alignment of the icon set',
		) );

		// Shortcod
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Shortcode' ),
			'element' => $this->a->r( array(
				'type'        => 'text',
				'value'       => $shortcode ? $this->a->get_shortcode_name( $shortcode ) : '',
				'class'       => 'form-control clipboard',
				'custom_data' => 'readonly',
			) ),
			'description' => $this->a->__( 'To use shortcode copy this field value and paste in any field, which supports shortcodes' ),
		) );

		// Save shortcode
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Manage shortcode' ),
			'element' => $this->a->r( array(
				'type'        => 'buttongroup',
				'buttons' => array(
					array(
						'type'        => 'button',
						'id'          => 'save-button-shortcode',
						'class'       => 'save-shortcode',
						'text_before' => $this->a->__( 'Save' ),
						'button_type' => 'primary',
						'icon'        => 'fa-save',
					),
					array(
						'type'        => 'button',
						'id'          => 'delete-button-shortcode',
						'class'       => 'delete-shortcode',
						'text_before' => $this->a->__( 'Delete' ),
						'icon'        => 'fa-remove',
					)
				),
			) ),
			'description' => $this->a->__( 'To see made changes on the preview screen don\'t forget to save shortcode and refresh preview' ),
		) );

		$ret .=
		'</div>'; // .tab-pane

		return $ret;
	}

	/**
	 * Returns shortcode QR Code management tab contents
	 * @param int $shortcode_id Shortcode iD, optional
	 * @return string
	 */
	public function render_qrcode_content( $shortcode_id = null ) {
		$ret = '';

		$ret .=
		'<div class="tab-pane" id="pane-qrcode">';

		$shortcode = null;

		if( is_null( $shortcode_id ) && isset( $this->request->request['shortcode_id'] ) ) {
			$shortcode_id = $this->request->request['shortcode_id'];
		}

		if( $shortcode_id ) {
			$shortcode = $this->a->get_shortcode( $shortcode_id );
		}

		// Saved shortcodes
		$shortcodes = array( '-1' => $this->a->__( 'Create new' ) );
		foreach( $this->a->get_shortcodes_by_category( 'qrcode' ) as $id => $e_shortcode ) {
			$shortcodes[ $id ] = empty( $e_shortcode['data']['name'] ) ?
			'qrcode-' . '-' . $id : $e_shortcode['data']['name'];
		}

		//$ret .= $this->a->r()->render_info_box( 'The "QR Code" shortcode renders a "Call to action button"' );

		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Load saved shortcode' ),
			'element' => $this->a->r( array(
				'type'   => 'select',
				'value'  => $shortcodes,
				'active' => isset( $shortcode['shortcode_id'] ) ? $shortcode['shortcode_id'] : '',
				'class'  => 'form-control shortcode-data select-shortcode',
				'id'     => 'qrcode-shortcode_id',
			) )
		) );

		// Name
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Name' ),
			'tooltip' => $this->a->__( 'Shortcode name to distinguish it from other' ),
			'element' => $this->a->r( array(
				'type'  => 'text',
				'value' => isset( $shortcode['data']['name'] ) ? $shortcode['data']['name'] : '',
				'class' => 'form-control shortcode-data',
				'id'    => 'qrcode-name',
			) )
		) );

		// Content
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'QR Code contents' ),
			'element' => $this->a->r( array(
				'type'        => 'textarea',
				'value'       => isset( $shortcode['data']['content'] ) ? $shortcode['data']['content'] : '',
				'class'       => 'form-control shortcode-data shortcode-able oneline',
				'id'          => 'qrcode-content',
				'custom_data' => 'data-height="35"',
			) ),
		) );

		// Text font height
		$square = array( 'value' => 4 );
		$square['units'] = 'px';
		if( isset( $shortcode['data']['square'] ) ) {
			$square['value'] = $shortcode['data']['square'];
		}

		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Pixel zoom' ),
			'element' => $this->model->render_dimension( array(
				'values' => 'px',
				'titles' => $this->a->__( 'Code square size' ),
				'value'  => $square,
				'id'     => 'qrcode-square',
				'maxes'  => '10',
			) ),
			'description' => $this->a->__( 'Code square size' ),
		) );

		// Border width
		$border = array( 'value' => 2 );
		$border['units'] = 'sq';
		if( isset( $shortcode['data']['border'] ) ) {
			$border['value'] = $shortcode['data']['border'];
		}

		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Border width' ),
			'element' => $this->model->render_dimension( array(
				'values' => 'sq',
				'titles' => $this->a->__( 'Border width' ),
				'value'  => $border,
				'id'     => 'qrcode-border',
				'maxes'  => '10',
			) ),
			'description' => $this->a->__( 'Border width measured in the code squares' ),
		) );

		// Error correction level 
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Error correction level' ),
			'element' => $this->a->r( array(
				'type' => 'inputgroup',
				'element' => array(
					'type'   => 'select',
					'value'  => $this->model->get_ec_values(),
					'active' => isset( $shortcode['data']['level'] ) ? $shortcode['data']['level' ] : 'L',
					'id'     => 'qrcode-level',
					'class'  => 'form-control shortcode-data',
				),
			) ),
			'description' => 'The quantity of data redundancy for error correction',
		) );

		// Shortcode
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Shortcode' ),
			'element' => $this->a->r( array(
				'type'        => 'text',
				'value'       => $shortcode ? $this->a->get_shortcode_name( $shortcode ) : '',
				'class'       => 'form-control clipboard',
				'custom_data' => 'readonly',
			) ),
			'description' => $this->a->__( 'To use shortcode copy this field value and paste to any field, which supports shortcodes' ),
		) );

		// Save shortcode
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Manage shortcode' ),
			'element' => $this->a->r( array(
				'type'        => 'buttongroup',
				'buttons' => array(
					array(
						'type'        => 'button',
						'id'          => 'save-qrcode-shortcode',
						'class'       => 'save-shortcode',
						'text_before' => $this->a->__( 'Save' ),
						'button_type' => 'primary',
						'icon'        => 'fa-save',
					),
					array(
						'type'        => 'button',
						'id'          => 'delete-qrcode-shortcode',
						'class'       => 'delete-shortcode',
						'text_before' => $this->a->__( 'Delete' ),
						'icon'        => 'fa-remove',
					)
				),
			) ),
			'description' => $this->a->__( 'To see made changes on the preview screen don\'t forget to save shortcode and refresh preview' ),
		) );

		$ret .=
		'</div>'; // .tab-pane

		return $ret;
	}

	/**
	 * Returns shortcode invoice management tab contents
	 * @param int $shortcode_id Shortcode iD, optional
	 * @return string
	 */
	public function render_invoice_content( $shortcode_id = null ) {
		$ret = '';

		$ret .=
		'<div class="tab-pane" id="pane-invoice">';

		$shortcode = null;

		if( is_null( $shortcode_id ) && isset( $this->request->request['shortcode_id'] ) ) {
			$shortcode_id = $this->request->request['shortcode_id'];
		}

		if( $shortcode_id ) {
			$shortcode = $this->a->get_shortcode( $shortcode_id );
		}

		// Saved shortcodes
		$shortcodes = array( '-1' => $this->a->__( 'Create new' ) );
		foreach( $this->a->get_shortcodes_by_category( 'invoice' ) as $id => $e_shortcode ) {
			$shortcodes[ $id ] = empty( $e_shortcode['data']['name'] ) ?
			'invoice' . '-' . $id : $e_shortcode['data']['name'];
		}

		$ret .= $this->a->r()->render_info_box( 'This shortcode renders an in-line invoice table' );

		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Load saved shortcode' ),
			'element' => $this->a->r( array(
				'type'   => 'select',
				'value'  => $shortcodes,
				'active' => isset( $shortcode['shortcode_id'] ) ? $shortcode['shortcode_id'] : '',
				'class'  => 'form-control shortcode-data select-shortcode',
				'id'     => 'invoice-shortcode_id',
			) )
		) );

		// Name
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Name' ),
			'tooltip' => $this->a->__( 'Shortcode name to distinguish it from other' ),
			'element' => $this->a->r( array(
				'type'  => 'text',
				'value' => isset( $shortcode['data']['name'] ) ? $shortcode['data']['name'] : '',
				'class' => 'form-control shortcode-data',
				'id'    => 'invoice-name',
			) )
		) );

		$ret .= $this->model->render_color_scheme_picker( 'invoice-color-scheme' );

		// Header color
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Header color' ),
			'element' => $this->a->r( array(
				'type'  => 'color',
				'value' => isset( $shortcode['data']['header']['color'] ) ?
					$shortcode['data']['header']['color'] : '#0000ff',
				'class' => 'form-control iris shortcode-data',
				'id'    => 'invoice-header-color',
			) ),
			'description' => $this->a->__( 'Blocks headers background color' ),
		) );

		// Header text color
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Header text color' ),
			'element' => $this->a->r( array(
				'type'  => 'color',
				'value' => isset( $shortcode['data']['header']['text']['color'] ) ?
					$shortcode['data']['header']['text']['color'] : '#0000ff',
				'class' => 'form-control iris shortcode-data',
				'id'    => 'invoice-header-text-color',
			) ),
			'description' => $this->a->__( 'Text color of block header' ),
		) );

		// Header text size
		$header_text_height = array( 'value' => 18 );
		$header_text_height['units'] = 'px';
		if( isset( $shortcode['data']['header']['text']['height'] ) ) {
			$header_text_height['value'] = $shortcode['data']['header']['text']['height'];
		}

		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Header text size' ),
			'element' => $this->model->render_dimension( array(
				'values' => 'px',
				'titles' => $this->a->__( 'Blocks header text height in pixels' ),
				'value'  => $header_text_height,
				'id'     => 'invoice-header-text-height',
				'maxes'  => '40',
				'max'    => 40,
			) ),
			'description' => $this->a->__( 'Text height of block header' ),
		) );

		// Header border color
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Header\'s borders color' ),
			'element' => $this->a->r( array(
				'type'  => 'color',
				'value' => isset( $shortcode['data']['header']['border']['color'] ) ?
					$shortcode['data']['header']['border']['color'] : '#0000ff',
				'class' => 'form-control iris shortcode-data',
				'id'    => 'invoice-header-border-color',
			) ),
			'description' => $this->a->__( 'Color of header\'s bottom and left borders' ),
		) );

		// Header border size
		$header_border_width = array( 'value' => 1 );
		$header_border_width['units'] = 'px';
		if( isset( $shortcode['data']['header']['border']['width'] ) ) {
			$header_border_width['value'] = $shortcode['data']['header']['border']['width'];
		}

		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Header\' borders width' ),
			'element' => $this->model->render_dimension( array(
				'values' => 'px',
				'titles' => $this->a->__( 'Width of header\'s borders' ),
				'value'  => $header_border_width,
				'id'     => 'invoice-header-border-width',
				'maxes'  => '10',
				'max'    => 10,
			) ),
			'description' => $this->a->__( 'Width of header\'s borders' ),
		) );

		// Body color
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Body color' ),
			'element' => $this->a->r( array(
				'type'  => 'color',
				'value' => isset( $shortcode['data']['body']['color'] ) ?
					$shortcode['data']['body']['color'] : '#0000ff',
				'class' => 'form-control iris shortcode-data',
				'id'    => 'invoice-body-color',
			) ),
			'description' => $this->a->__( 'Background color of block body' ),
		) );

		// Body text color
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Body text color' ),
			'element' => $this->a->r( array(
				'type'  => 'color',
				'value' => isset( $shortcode['data']['body']['text']['color'] ) ?
					$shortcode['data']['body']['text']['color'] : '#0000ff',
				'class' => 'form-control iris shortcode-data',
				'id'    => 'invoice-body-text-color',
			) ),
			'description' => $this->a->__( 'Text color of block header' ),
		) );

		// Body text size
		$body_text_height = array( 'value' => 16 );
		$body_text_height['units'] = 'px';
		if( isset( $shortcode['data']['body']['text']['height'] ) ) {
			$body_text_height['value'] = $shortcode['data']['body']['text']['height'];
		}

		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Body text size' ),
			'element' => $this->model->render_dimension( array(
				'values' => 'px',
				'titles' => $this->a->__( 'Blocks body text height in pixels' ),
				'value'  => $body_text_height,
				'id'     => 'invoice-body-text-height',
				'maxes'  => '40',
				'max'    => 40,
			) ),
			'description' => $this->a->__( 'Blocks body text height' ),
		) );

		// Body border color
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Body\'s borders color' ),
			'element' => $this->a->r( array(
				'type'  => 'color',
				'value' => isset( $shortcode['data']['body']['border']['color'] ) ?
					$shortcode['data']['body']['border']['color'] : '#0000ff',
				'class' => 'form-control iris shortcode-data',
				'id'    => 'invoice-body-border-color',
			) ),
			'description' => $this->a->__( 'Color of body\'s bottom and left borders' ),
		) );

		// Body border size
		$body_border_width = array( 'value' => 1 );
		$body_border_width['units'] = 'px';

		if( isset( $shortcode['data']['body']['border']['width'] ) ) {
			$body_border_width['value'] = $shortcode['data']['body']['border']['width'];
		}

		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Body\'s borders width' ),
			'element' => $this->model->render_dimension( array(
				'values' => 'px',
				'titles' => $this->a->__( 'Width of body\'s borders' ),
				'value'  => $body_border_width,
				'id'     => 'invoice-body-border-width',
				'maxes'  => '40',
				'max'    => 40,
			) ),
			'description' => $this->a->__( 'Width of body\'s borders' ),
		) );


		// Table border color
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Table\'s borders color' ),
			'element' => $this->a->r( array(
				'type'  => 'color',
				'value' => isset( $shortcode['data']['table']['border']['color'] ) ?
					$shortcode['data']['table']['border']['color'] : '#0000ff',
				'class' => 'form-control iris shortcode-data',
				'id'    => 'invoice-table-border-color',
			) ),
			'description' => $this->a->__( 'Color of table\'s top and left borders' ),
		) );

		// Table border size
		$table_border_width = array( 'value' => 1 );
		$table_border_width['units'] = 'px';
		if( isset( $shortcode['data']['table']['border']['width'] ) ) {
			$table_border_width['value'] = $shortcode['data']['table']['border']['width'];
		}

		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Table\'s borders width' ),
			'element' => $this->model->render_dimension( array(
				'values' => 'px',
				'titles' => $this->a->__( 'Width of table\'s borders' ),
				'value'  => $table_border_width,
				'id'     => 'invoice-table-border-width',
				'maxes'  => '40',
				'max'    => 40,
			) ),
			'description' => $this->a->__( 'Border width of entire table' ),
		) );

		// Product image width
		$product_image_width = array( 'value' => 60 );
		$product_image_width['units'] = 'px';
		if( isset( $shortcode['data']['product']['image']['width'] ) ) {
			$product_image_width['value'] = $shortcode['data']['product']['image']['width'];
		}

		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Product\'s image width' ),
			'element' => $this->model->render_dimension( array(
				'values' => 'px',
				'titles' => $this->a->__( 'Product\'s image width' ),
				'value'  => $product_image_width,
				'id'     => 'invoice-product-image-width',
				'maxes'  => '600',
				'max'    => 600,
			) ),
			'description' => $this->a->__( '' ),
		) );

		// Show order details
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Order details' ),
			'element' => $this->a->r()->render_fancy_checkbox( array(
				'value' => ! empty( $shortcode['data']['fields']['order'] ) ? '1' : '0',
				'class'  => 'shortcode-data',
				'id'     => 'invoice-fields-order',
			) ),
			'description' => $this->a->__( 'Show order details' ),
		) );

		// Show payment address
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Payment address' ),
			'element' => $this->a->r()->render_fancy_checkbox( array(
				'value' => ! empty( $shortcode['data']['fields']['payment'] ) ? '1' : '0',
				'class'  => 'shortcode-data',
				'id'     => 'invoice-fields-payment',
			) ),
			'description' => $this->a->__( 'Show payment address' ),
		) );

		// Show shipping address]
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Shipping address' ),
			'element' => $this->a->r()->render_fancy_checkbox( array(
				'value' => ! empty( $shortcode['data']['fields']['shipping'] ) ? '1' : '0',
				'class'  => 'shortcode-data',
				'id'     => 'invoice-fields-shipping',
			) ),
			'description' => $this->a->__( 'Show shipping address' ),
		) );

		// Show products
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Products' ),
			'element' => $this->a->r()->render_fancy_checkbox( array(
				'value'         => ! empty( $shortcode['data']['fields']['products'] ) ? '1' : '0',
				'class'         => 'shortcode-data',
				'id'            => 'invoice-fields-products',
				'dependent_off' => '#invoice-fields-image,#invoice-fields-totals',
			) ),
			'description' => $this->a->__( 'Show order products' ),
		) );

		// Show product image
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Product image' ),
			'element' => $this->a->r()->render_fancy_checkbox( array(
				'value'        => ! empty( $shortcode['data']['fields']['image'] ) ? '1' : '0',
				'class'        => 'shortcode-data',
				'id'           => 'invoice-fields-image',
				'dependent_on' => '#invoice-fields-products',
			) ),
			'description' => $this->a->__( 'Show products images' ),
		) );

		// Show totals
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Totals' ),
			'element' => $this->a->r()->render_fancy_checkbox( array(
				'value' => ! empty( $shortcode['data']['fields']['totals'] ) ? '1' : '0',
				'class'  => 'shortcode-data',
				'id'     => 'invoice-fields-totals',
				'dependent_on' => '#invoice-fields-products',
			) ),
			'description' => $this->a->__( 'Show order totals fields' ),
		) );

		// Show comment
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Comment' ),
			'element' => $this->a->r()->render_fancy_checkbox( array(
				'value' => ! empty( $shortcode['data']['fields']['comment'] ) ? '1' : '0',
				'class'  => 'shortcode-data',
				'id'     => 'invoice-fields-comment',
			) ),
			'description' => $this->a->__( 'Show comment to an order' ),
		) );

		// Shortcod
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Shortcode' ),
			'element' => $this->a->r( array(
				'type'        => 'text',
				'value'       => $shortcode ? $this->a->get_shortcode_name( $shortcode ) : '',
				'class'       => 'form-control clipboard',
				'custom_data' => 'readonly',
			) ),
			'description' => $this->a->__( 'To use shortcode copy this field value and paste to any field, which supports shortcodes' ),
		) );

		// Save shortcode
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Manage shortcode' ),
			'element' => $this->a->r( array(
				'type'        => 'buttongroup',
				'buttons' => array(
					array(
						'type'        => 'button',
						'id'          => 'save-invoice-shortcode',
						'class'       => 'save-shortcode',
						'text_before' => $this->a->__( 'Save' ),
						'button_type' => 'primary',
						'icon'        => 'fa-save',
					),
					array(
						'type'        => 'button',
						'id'          => 'delete-invoice-shortcode',
						'class'       => 'delete-shortcode',
						'text_before' => $this->a->__( 'Delete' ),
						'icon'        => 'fa-remove',
					)
				),
			) ),
			'description' => $this->a->__( 'To see made changes on the preview screen don\'t forget to save shortcode and refresh preview' ),
		) );

		$ret .=
		'</div>'; // .tab-pane

		return $ret;
	}

	/**
	 * Save shortcode action
	 * @return void
	 */
	public function save_shortcode() {
		$ret = array();
		$shortcode_data = array( 'data' => array(), );

		try {

			$data = $this->request->post;

			if( empty( $data['category'] ) ) {
				throw new \Advertikon\Exception( 'Shortcode category is missing' );
			}

			$shortcode_data['category'] = $data['category'];
			unset( $data['category'] );

			foreach( $data as $k => $s_data ) {
				if( strpos( $k, $shortcode_data['category'] . '-' ) !== 0 ) {
					continue;
				}

				$parts = explode( '-', $k );
				array_shift( $parts );

				$current_data = &$this->a->create_array_structure( $shortcode_data['data'], implode( '/', $parts ) );
				$current_data = $s_data;

				unset( $current_data );
			}

			if( isset( $shortcode_data['data']['shortcode_id'] ) ) {
				if( -1 != $shortcode_data['data']['shortcode_id'] ) {
					$shortcode_data['shortcode_id'] = $shortcode_data['data']['shortcode_id'];
				}

				unset( $shortcode_data['data']['shortcode_id'] );
			}

			if( ! $shortcode_data['data'] ) {
				throw new \Advertikon\Exception( $this->a->__( 'Shortcode data are missing' ) );
			}

			if( false === ( $shortcode_id =  $this->a->save_shortcode( $shortcode_data ) ) ) {
				throw new \Advertikon\Exception( $this->a->__( 'Unable to save shortcode' ) );
			}

			$shortcode_data['shortcode_id'] = $shortcode_id;

			$shortcode_tab_content = $this->render_shortcode_tab( $shortcode_data );

			if( ! $shortcode_tab_content ) {
				throw new \Advertikon\Exception( $this->a->__( 'Fail to render shortcode tab contents' ) );
			}

			$ret['success'] = $shortcode_tab_content;
			
		} catch ( \Advertikon\Exception $e ) {
			$ret['error'] = $e->getMessage();
		}

		$this->response->setOutput( json_encode( $ret ) );
	}

	/**
	 * Renders shortcode tab
	 * @param string $shortcode Tab name
	 * @return string
	 */
	public function render_shortcode_tab( $shortcode ) {
		$ret = '';

		if( 'vitrine' === $shortcode['category'] ) {
			$ret = $this->render_vitrine_content( $shortcode['shortcode_id'] );

		} elseif ( 'social' === $shortcode['category'] ) {
			$ret = $this->render_social_content( $shortcode['shortcode_id'] );

		} elseif ( 'button' === $shortcode['category'] ) {
			$ret = $this->render_button_content( $shortcode['shortcode_id'] );

		} elseif ( "qrcode" == $shortcode['category'] ) {
			$ret = $this->render_qrcode_content( $shortcode['shortcode_id'] );

		} elseif ( "invoice" == $shortcode['category'] ) {
			$ret = $this->render_invoice_content( $shortcode['shortcode_id'] );
		}

		return $ret;
	}

	/**
	 * Fetch shortcode action
	 * @return void
	 */
	public function fetch_shortcode() {
		$ret = array();

		try {

			if( ! isset( $this->request->request['shortcode_id'] ) ) {
				throw new \Advertikon\Exception( 'Shortcode ID is missing' );
			}

			$shortcode_id = $this->request->request['shortcode_id'];

			if( -1 == $shortcode_id ) {
				if( ! isset( $this->request->request['category'] ) ) {
					throw new \Advertikon\Exception( $this->a->__( 'Shortcode category is missing' ) );
				}

				$category = $this->request->request['category'];
				$shortcode = array( 'category' => $category, 'shortcode_id' => -1 );
			} else {
				$shortcode = $this->a->get_shortcode( $shortcode_id );

				if( ! $shortcode ) {
					throw new \Advertikon\Exception( $this->a->__( 'Shortcode with ID %s is missing', $shortcode_id ) );
				}
			}

			$shortcode_tab = $this->render_shortcode_tab( $shortcode );

			if( ! $shortcode_tab ) {
				throw new \Advertikon\Exception( $this->a->__( 'Fail to render shortcode tab contents' ) );
			}

			$ret['success'] = $shortcode_tab;
			
		} catch ( \Advertikon\Exception $e ) {
			$ret['error'] = $e->getMessage();
		}

		$this->response->setOutput( json_encode( $ret ) );
	}

	/**
	 * Delete shortcode action
	 * @return void
	 */
	public function delete_shortcode() {
		$ret = array();

		try {

			if( ! $this->user->hasPermission( 'modify', $this->a->type . '/' . $this->a->code ) ) {
				throw new \Advertikon\Exception( $this->a->__( 'You have no permissions to modify extension data' ) );
			}

			if( ! isset( $this->request->request['shortcode_id'] ) ) {
				throw new \Advertikon\Exception( $this->a->__( 'Shortcode ID is missing' ) );
			}

			$shortcode_id = $this->request->request['shortcode_id'];

			if( ! isset( $this->request->request['category'] ) ) {
				throw new \Advertikon\Exception( $this->a->__( 'Shortcode category is missing' ) );
			}

			$category = $this->request->request['category'];


			if( ! $this->a->delete_shortcode( $shortcode_id ) ) {
				throw new \Advertikon\Exception( $this->a->__( 'Unable delete shortcode' ) );
			}

			$next_shortcode = $this->a->get_shortcodes_by_category( $category );

			if( ! $next_shortcode || ! is_array( $next_shortcode ) ) {

				// Render blank tab of the same category
				$next_shortcode = array( 'category' => $category, 'shortcode_id' => false );
			} else {

				// Render first shortcode at category
				$next_shortcode = current( $next_shortcode );
			}

			$shortcode_tab = $this->render_shortcode_tab( $next_shortcode );

			if( ! $shortcode_tab ) {
				throw new \Advertikon\Exception( $this->a->__( 'Fail to render shortcode tab contents' ) );
			}

			$ret['success'] = $shortcode_tab;
			
		} catch ( \Advertikon\Exception $e ) {
			$ret['error'] = $e->getMessage();
		}

		$this->response->setOutput( json_encode( $ret ) );
	}

	/**
	 * Product autofill action
	 * @return void
	 */
	public function product_autofill() {

		$object = array();

		$page = isset( $this->request->request['page'] ) ? $this->request->request['page'] : 1;
		$count = isset( $this->request->request['count'] ) ? $this->request->request['count'] : 10;
		$query = ! empty( $this->request->request['q'] ) ? $this->request->request['q'] : null;

		if( ! is_null( $query ) ) {
			$products = $this->model->get_product_autofill( $query, ( $page - 1 ) * $count, $count );
			$object = $products;
		} 

		$this->response->setOutput( json_encode( $object ) );
	}

	/**
	 * Save profile mapping action
	 * @return void
	 */
	public function save_profile_mapping() {
		$ret = array();
		$total_count = 0;
		$count = 0;

		try {

			if ( empty( $this->request->post['config'] ) ) {
				throw new \Advertikon\Exception( $this->a->__( 'Configuration data missing' ) );
			}

			$configuration = $this->request->post['config'];
			$total_count = count( $configuration );

			foreach( $configuration as $config ) {

				$values = array();
				$where = array();

				if( ! isset( $config['level'] ) ) {
					trigger_error( $this->a->__( 'Configuration scope is missing' ) );
					continue;
				}

				$values['level'] = $config['level'];
				$where[] = array(
					'field'     => 'level',
					'operation' => '=',
					'value'     => $config['level'],
				); 

				if( ! isset( $config['id'] ) ) {
					trigger_error( $this->a->__( 'Configuration scope ID is missing' ) );
					continue;
				}

				$values['id'] = $config['id'];
				$where[] = array(
					'field'     => 'id',
					'operation' => '=',
					'value'     => $config['id'],
				); 

				if( ! isset( $config['profile'] ) ) {
					trigger_error( $this->a->__( 'Profile ID is missing' ) );
					continue;
				}

				if ( -1 == $config['profile'] ) {
					$values['profile_id'] = 'NULL';

				} else {
					$values['profile_id'] = $config['profile'];
				}

				$values['enabled'] = empty( $config['enable'] ) ? 0 : 1;
				$values['log'] = empty( $config['log'] ) ? 0 : 1;
				$values['track'] = empty( $config['track'] ) ? 0 : 1;
				$values['track_visit'] = empty( $config['track_visit'] ) ? 0 : 1;

				$data = array(
					'table' => $this->a->profile_mapping_table,
					'query' => 'delete',
					'where' => $where,
				);

				$this->a->q( $data );

				$data = array(
					'table'  => $this->a->profile_mapping_table,
					'query'  => 'insert',
					'values' => $values,
				);

				if( ! $this->a->q( $data ) ) {
					trigger_error( $this->a->__( 'Fail to save configuration' ) );

				} else {
					$count++;
				}
			}

			if ( $total_count !== $count ) {
				throw new \Advertikon\Exception(
					$this->a->__(
						'%s of %s configurations were saved. Reload page to see the changes',
						$count,
						$total_count
					)
				);
			}

			$ret['success'] = $this->a->__( 'Configuration has been saved successfully' );
			
		} catch ( \Advertikon\Exception $e ) {
			$ret['error'] = $e->getMessage();
		}

		$this->response->setOutput( json_encode( $ret ) );
	}

	/**
	 * Save newsletter template action
	 * @return void
	 */
	public function save_template_mail() {
		$ret = array();

		try {

			if( ! isset( $this->request->request['name'] ) ) {
				throw new \Advertikon\Exception( $this->a->__( 'Template name is mandatory' ) );
			}

			$name = $this->a->p( 'name' );

			$data = array(
				'name'       => $name,
				'subject'    => $this->a->p( 'subject', '' ),
				'message'    => $this->a->p( 'message', '' ),
				'cc'         => $this->a->p( 'cc', '' ),
				'bcc'        => $this->a->p( 'bcc', '' ),
				'attachment' => $this->a->p( 'attachment', '' ),
				'return'     => $this->a->p( 'return', '' ),
			);

			// Existed template
			$id = $this->a->post( 'template_id' );

			$f_name = $id && -1 != $id ? $id : uniqid();
			$this->a->mkdir( $this->a->newsletter_template );
			$result = file_put_contents(
				$this->a->newsletter_template . $f_name,

				// Put template's name into first 100 bytes for further use
				str_pad( substr( $name, 0, 100 ), 100 ) .
				json_encode( $data )
			);

			if( $result ) {

				// Backward compatibility - remove record from DB
				if ( $id ) {
					$this->a->q( array(
						'table' => $this->a->template_mail_table,
						'query' => 'delete',
						'where' => array(
							'field'     => 'id',
							'operation' => '=',
							'value'     => $id,
						),
					) );
				}

				$ret['success'] = $this->a->__( 'Template has been successfully saved' );
				$ret['id'] = $f_name;

			} else {
				throw new \Advertikon\Exception( $this->a->__( 'Failed to save template' ) );
			}
			
		} catch ( \Advertikon\Exception $e ) {
			$ret['error'] = $e->getMessage();
		}

		$this->response->setOutput( json_encode( $ret ) );
	}

	/**
	 * Delete newsletter template action
	 * @return void
	 */
	public function delete_template_mail() {
		$ret = array();
		$result = null;

		try {

			if( ! isset( $this->request->request['id'] ) ) {
				throw new \Advertikon\Exception( $this->a->__( 'Template ID is mandatory' ) );
			}

			$id = $this->request->request['id'];

			if ( file_exists( $this->a->newsletter_template . $id ) ) {
				$result = unlink( $this->a->newsletter_template . $id );
			}

			// Backward compatibility
			if ( is_null( $result ) ) {
				$result = $this->a->q( array(
					'table' => $this->a->template_mail_table,
					'query' => 'delete',
					'where' => array(
						'field'     => 'id',
						'operation' => '=',
						'value'     => $id,
					),
				) );
			}

			if( $result ) {
				$ret['success'] = $this->a->__( 'Template has been deleted successfully' );

			} else {
				throw new \Advertikon\Exception( $this->a->__( 'Fail to delete template' ) );
			}
			
		} catch ( \Advertikon\Exception $e ) {
			$ret['error'] = $e->getMessage();
		}

		$this->response->setOutput( json_encode( $ret ) );
	}

	/**
	 * Get newsletter template action
	 * @return void
	 */
	public function get_template_list() {
		$ret = array();

		try {

			if( ! isset( $this->request->request['id'] ) ) {
				throw new \Advertikon\Exception( $this->a->__( 'Template ID is mandatory' ) );
			}

			$id = $this->request->request['id'];

			if ( is_file( $this->a->newsletter_template . $id ) ) {

				// First 100 bytes - template's name
				$cont = file_get_contents( $this->a->newsletter_template . $id, null, null, 100 );

				if ( false === $cont ) {
					throw new \Advertikon\Exception( $this->a->__( 'Failed to get template\' data' ) );
				}

				$ret['template'] = json_decode( $cont );
					
			// Backward compatibility
			} else {
				$query = $this->a->q( array(
					'table' => $this->a->template_mail_table,
					'query' => 'select',
					'where' => array(
						'field'     => 'id',
						'operation' => '=',
						'value'     => $id,
					)
				) );

				if( $query ) {
					$ret['template'] = $query->current();

				} else {
					throw new \Advertikon\Exception( $this->a->__( 'Database error' ) );
				}
			}

			$ret['success'] = 1;
			
		} catch ( \Advertikon\Exception $e ) {
			$ret['error'] = $e->getMessage();
		}

		$this->response->setOutput( json_encode( $ret ) );
	}

	/**
	 * Change setting action
	 * @return void
	 */
	public function setting() {
		$ret = array();

		try {

			if( ! $this->user->hasPermission( 'modify', $this->a->type . '/' . $this->a->code ) ) {
				throw new \Advertikon\Exception( $this->a->__( 'You have no permissions to modify extension\'s data' ) );
			}

			if( empty( $this->request->request['name'] ) ) {
				throw new \Advertikon\Exception( $this->a->__( 'Setting name is missing' ) );
			}

			if( ! isset( $this->request->request['value'] ) ) {
				throw new \Advertikon\Exception( $this->a->__( 'Setting value is missing' ) );
			}

			$name = $this->request->request['name'];
			$value = $this->request->request['value'];

			if( ! $this->model->set_setting( $name, $value ) ) {
				throw new \Advertikon\Exception( $this->a->__( 'Unable to change setting "%s"', $name ) );
			}

			$msg = '';

			if( false && is_scalar( $value ) ) {
				$msg = $this->a->__( 'Setting "%s" set to "%s"' );

			} else {
				$msg = $this->a->__( 'Setting has been changed' );
			}

			$ret['success'] = $msg;
			
		} catch ( \Advertikon\Exception $e ) {
			$ret['error'] = $e->getMessage();
		}

		$this->response->setOutput( json_encode( $ret ) );
	}

	/**
	 * Add custom template action
	 * @return void
	 */
	public function add_template() {
		$ret = array();
		$id = false;
		$data = array();

		try {

			if ( $this->a->p( 'statuses' ) ) {
				$id = $this->model->add_order_status_template(
					array( 'statuses' => $this->a->p( 'statuses' ), )
				);

			} elseif ( $this->a->p( 'returns' ) ) {
				$id = $this->model->add_return_status_template(
					array( 'statuses' => $this->a->p( 'returns' ), )
				);

			} elseif ( $this->a->p( 'file' ) || $this->a->p( 'hook' ) ) {

				$data['name'] = $this->a->p( 'name' );
				$data['parent'] = $this->a->p( 'sample' );
				$data['data'] = array();
				$data['description'] = '';

				if ( $this->a->p( 'file' ) ) {
					$file = $this->a->p( 'file' );
					$function = $this->a->p( 'func' );

					if( ! $function ) {
						throw new \Advertikon\Exception( $this->a->__( 'Function name is missing' ) );
					}

					if( ! $file ) {
						throw new \Advertikon\Exception( $this->a->__( 'File name is missing' ) );
					}

					$data['path_hook'] = $file . '-' . $function;

				} else {
					$data['hook'] = $this->a->p( 'hook' );
				}

				$id = $this->model->add_template( $data );

			} else {
				throw new \Advertikon\Exception( $this->a->__( 'Template data missing' ) );
			}

			if( ! $id ) {
				throw new \Advertikon\Exception( $this->a->__( 'Fail to add template' ) );
			}

			$ret['success'] = $this->a->__( 'Template has been successfully added' );
			$ret['template'] = $id;
			
		} catch ( \Advertikon\Exception $e ) {
			$ret['error'] = $e->getMessage();
		}

		$this->response->setOutput( json_encode( $ret ) );
	}

	/**
	 * Delete template action
	 * @return void
	 */
	public function delete_template() {
		$ret = array();

		try {

			if( ! isset( $this->request->request['id'] ) ) {
				throw new \Advertikon\Exception( $this->a->__( 'Template ID is missing' ) );
			}

			$template_id = $this->request->request['id'];

			if( false === ( $this->model->delete_template( $template_id ) ) ) {
				throw new \Advertikon\Exception( $this->a->__( 'Unable to delete template' ) );
			}

			$ret['success'] = $this->a->__( 'Template has been successfully removed' );
			
		} catch ( \Advertikon\Exception $e ) {
			$ret['error'] = $e->getMessage();
		}

		$this->response->setOutput( json_encode( $ret ) );
	}

	/**
	 * Select attachment connector action 
	 * @return void
	 */
	public function attachment_connector() {
		require_once( $this->a->elfinder_root . 'autoload.php' );

		/**
		 * Simple function to demonstrate how to control file access using "accessControl" callback.
		 * This method will disable accessing files/folders starting from '.' (dot)
		 *
		 * @param  string  $attr  attribute name (read|write|locked|hidden)
		 * @param  string  $path  file path relative to volume root directory started with directory separator
		 * @return bool|null
		 **/
		function access($attr, $path, $data, $volume) {
			return strpos(basename($path), '.') === 0
				? !($attr == 'read' || $attr == 'write')
				:  null;
		}

		$fs = new \Advertikon\Fs();

		$fs->mkdir( $this->a->attachments_root );
		$allowed_mime = explode( "\n", str_replace( "\r\n", "\n", $this->config->get( 'config_file_mime_allowed' ) ) );

		$opts = array(
			'debug' => true,
			'roots' => array(
				array(
					'driver'        => 'LocalFileSystem',
					'path'          => $this->a->attachments_root,
					'uploadDeny'    => array( 'all' ),
					'uploadAllow'   => $allowed_mime,
					'uploadOrder'   => array( 'deny', 'allow' ),
					'accessControl' => 'access',
					'attributes'    => array(
						array(
							'pattern' => '/\.csv$/',
							'read'    => true,
							'write'   => true,
							'locked'  => true,
							'hidden'  => false,
						)
					)
				)
			)
		);

		// run elFinder
		$connector = new elFinderConnector(new elFinder($opts));
		$this->response->setOutput( json_encode( $connector->run() ) );
	}

	/**
	 * Select attachment connector action 
	 * @return void
	 */
	public function img_connector() {
		require_once( $this->a->elfinder_root . 'autoload.php' );

		/**
		 * Simple function to demonstrate how to control file access using "accessControl" callback.
		 * This method will disable accessing files/folders starting from '.' (dot)
		 *
		 * @param  string  $attr  attribute name (read|write|locked|hidden)
		 * @param  string  $path  file path relative to volume root directory started with directory separator
		 * @return bool|null
		 **/
		function access($attr, $path, $data, $volume) {
			return strpos(basename($path), '.') === 0
				? !($attr == 'read' || $attr == 'write')
				:  null;
		}

		$allowed_mime = explode( "\n", str_replace( "\r\n", "\n", $this->config->get( 'config_file_mime_allowed' ) ) );

		$opts = array(
			'debug' => true,
			'roots' => array(
				array(
					'driver'        => 'LocalFileSystem',
					'path'          => DIR_IMAGE . 'catalog',
					'uploadDeny'    => array( 'all' ),
					'uploadAllow'   => $allowed_mime,
					'uploadOrder'   => array( 'deny', 'allow' ),
					'accessControl' => 'access',
				)
			)
		);

		// run elFinder
		$connector = new elFinderConnector(new elFinder($opts));
		$this->response->setOutput( json_encode( $connector->run() ) );
	}

	/**
	 * File browser's iFrame contents (attachments)
	 * @return void
	 */
	public function attachment_href() {
		$url = preg_replace(
			'/&amp;/',
			'&',
			$this->url->link(
				$this->a->type . '/' . $this->a->code . '/attachment_connector',
				'token=' . $this->session->data['token'],
				'SSL'
			)
		);

		echo $this->file_browser_content( $url );
	}

	/**
	 * File browser's iFrame contents (images)
	 * @return void
	 */
	public function img_href() {
		$url = preg_replace(
			'/&amp;/',
			'&',
			$this->url->link(
				$this->a->type . '/' . $this->a->code . '/img_connector',
				'token=' . $this->session->data['token'],
				'SSL'
			)
		);

		echo $this->file_browser_content( $url );
	}

	/**
	 * File browser's iFrame contents
	 * @return void
	 */
	public function file_browser_content( $url ) {
		$ret = ob_start();
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title><?php echo $this->a->__( 'File browser' ); ?></title>
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=2" />

		<link rel="stylesheet" type="text/css" href="view/stylesheet/advertikon/jquery-ui.min.css">
		<link rel="stylesheet" type="text/css" href="view/stylesheet/advertikon/jquery-ui.theme.min.css">
		<link rel="stylesheet" type="text/css" href="view/stylesheet/advertikon/elfinder/css/elfinder.min.css">
		<link rel="stylesheet" type="text/css" href="view/stylesheet/advertikon/elfinder/css/theme.css">

		<script src="view/javascript/jquery/jquery-2.1.1.min.js"></script>
		<script src="view/javascript/advertikon/jquery-ui.min.js"></script>
		<script src="view/javascript/advertikon/elfinder/js/elfinder.full.js"></script>

		<script type="text/javascript" charset="utf-8">
		(function s( $ ) {

			var i18nPath = 'js/i18n',
				start = function( lng ) {
					$().ready( function() {
						var elf = $( '#elfinder' ).elfinder( {
							lang : lng,
							url  : "<?php echo $url; ?>",
							commands : [
								// 'custom',
								'open',
								'reload',
								'home',
								'up',
								'back',
								'forward',
								'getfile',
								'quicklook', 
								'download',
								'rm',
								'duplicate',
								'rename',
								'mkdir',
								'mkfile',
								'upload',
								'copy', 
								'cut',
								'paste',
								'edit',
								'extract',
								'archive',
								'search',
								'info',
								'view',
								'resize',
								'sort',
								'chmod'
							],
							contextmenu : {
								navbar : ['open', '|', 'copy', '|', 'cut', 'paste', 'duplicate', '|', 'rm', '|', 'info'],
								cwd    : ['reload', 'back', '|', 'upload', 'mkdir', 'mkfile', 'paste', '|', 'sort', '|', 'info'],
								files  : ['getfile', '|', 'custom', 'quicklook', '|', 'download', '|', 'copy', 'cut', 'paste', 'duplicate', '|', 'rm', '|', 'edit', 'rename', 'resize', '|', 'archive', 'extract', '|', 'info', 'chmod']
							},
							handlers: {
								dblclick: function(e){
									var fileData = elf.file( e.data.file );

									if( fileData.mime !== "directory" ) {

										fileData.buttonId = window.frameElement.button_id;
										fileData.path = elf.path( fileData.phash );
										fileData.type = "file";

										window.parent.postMessage( fileData, "*" );

										return false;
									}
								}
							}
						} ).elfinder( 'instance' );
					} );
				},
				loct = window.location.search,
				full_lng, locm, lng;

			// detect language
			if ( loct && ( locm = loct.match( /lang=([a-zA-Z_-]+)/ ) ) ) {
				full_lng = locm[ 1 ];
			} else {
				full_lng = ( navigator.browserLanguage || navigator.language || navigator.userLanguage );
			}

			lng = full_lng.substr( 0, 2 );

			if ( lng === 'ja' ) {
				lng = 'jp';
			} else if ( lng === 'pt' ) {
				lng = 'pt_BR';
			} else if ( lng === 'zh' ) {
				lng = ( full_lng.substr( 0, 5 ) == 'zh-tw' )? 'zh_TW' : 'zh_CN';
			}

			if ( lng != 'en' ) {
				$.ajax( {
					url:      i18nPath+'/elfinder.'+lng+'.js',
					cache:    true,
					dataType: 'script'
				} )
				.done(function() {
					start( lng );
				} )
				.fail(function() {
					start( 'en' );
				} );
			} else {
				start( lng );
			}
		} )( jQuery );
		</script>
	</head>
	<body>
		<div id="elfinder"></div>
	</body>
</html>
<?php
		return ob_get_clean();
	}

	/**
	 * Creates thumbnail image in image cache directory and print its contents into browser
	 * @return void
	 */
	public function thumbnail() {
		if( empty( $this->request->request['path'] ) ) {
			$path = 'no_image.png';

		} else {
			$path = $this->request->request['path'];
		}

		$realpath = realpath( DIR_IMAGE . $path );

		if( strpos( $realpath, DIR_IMAGE ) !== 0 ) {
			$realpath = DIR_IMAGE . 'no_image.jpg';
		}

		if ( ! is_file( $realpath ) ) {
			return '';
		}

		$width = 120;
		$height = 120;

		$extension = pathinfo( $path, PATHINFO_EXTENSION );

		$old_image = $realpath;
		$new_image = DIR_IMAGE . 'cache/' . utf8_substr( $path, 0, utf8_strrpos( $path, '.' ) ) . '-' . $width . 'x' . $height . '.' . $extension;

		if ( ! is_file( $new_image ) || ( filectime( $old_image ) > filectime( $new_image ) ) ) {

			$this->a->mkdir( dirname( $new_image ) );

			$img = new \Advertikon\Image( $realpath );
			$img->resize( $width, $height );

			imagepng( $img->getImage(), $new_image );
		}

		header( "Content-Type: " . image_type_to_mime_type( exif_imagetype( $new_image ) ) );
		echo file_get_contents( $new_image );
	}

	/**
	 * Clean archive folder action
	 * @return void
	 */
	public function clean_archive() {
		$ret = array();

		try {

			if( ! isset( $this->request->request['days'] ) ) {
				throw new \Advertikon\Exception( $this->a->__( 'Number of days is missing' ) );
			}

			$days = $this->request->request['days'];

			if( false === ( $result = $this->model->clear_archive( $days ) ) ) {
				throw new \Advertikon\Exception( $this->a->__( 'Unable to clear archive' ) );
			}

			$result['text'] = $this->a->__(
				'%s item(s) was deleted (%s)',
				$result['count'],
				$this->a->format_bytes( $result['size'] )
			);

			$ret['success'] = $result;
			
		} catch ( \Advertikon\Exception $e ) {
			$ret['error'] = $e->getMessage();
		}

		$this->response->setOutput( json_encode( $ret ) );
	}

	/**
	 * Renders history pane content
	 * @param array $data Filter array
	 * @return string
	 */
	public function render_history( $data = array() ) {
		if ( ! empty( $data['where'] ) ) {
			array_walk( $data['where'], function( &$i ) {
				if ( ! empty( $i['field'] ) ) {
					$i['field'] = 'h.' . $i['field'];
				}
			} );
		}

		$data['table'] = array( 'h' => $this->a->history_table );

		if ( isset( $data['where'] ) ) {
			array_walk_recursive( $data['where'], function( &$v ) {
				$v = htmlspecialchars_decode( $v );
			} );
		}

		$data['join'] = array(
			'table' => $this->a->newsletter_list_table,
			'alias' => 'n',
			'type' => 'left',
			'on'    => array(
				'left'      => '`h.newsletter`',
				'operation' => '=',
				'right'     => '`n.id`'
			),
		);

		$data['field'] = array( '`h.*`', '`newsletter_name`' => '`n.name`', 'date_added' => '`h.date_added`' );

		if ( empty( $data['order_by' ] ) ) {
			$data['order_by'] = array( 'h.date_added' => 'desc' );
		}

		$total = $this->a->get_history_count( $data );

		$page = 1;

		if ( isset( $data['page'] ) ) {
			if ( 'last' === $data['page'] ) {
				$page = ceil( $total / $data['limit'] );

			} else {
				$page = (int)$data['page'];
				$page = $page <= 0 ? 1 : $page;
			}
		}

		if ( empty( $data['limit'] ) ) {
			$data['limit'] = 20;
		}

		$data['start'] = ( $page - 1 ) * $data['limit'];

		$columns = array(
			'id'           => '<input id="history-select-all" type="checkbox">',
			'date_added'   => $this->a->__( 'Date' ),
			'to'           => $this->a->__( 'To' ),
			'subject'      => $this->a->__( 'Subject' ),
			'status'       => $this->a->__( 'Status' ),
			'newsletter'   => $this->a->__( 'Newsletter' ),
			'template'     => $this->a->__( 'Template' ),
			'attachment'   => $this->a->__( 'Attachment' ),
			'date_visited' => $this->a->__( 'Visited' ),
			'date_viewed'  => $this->a->__( 'Viewed' ),
			'log'          => $this->a->__( 'Log' ),
		);

		$ret =
'<div class="table-responsive">' .
'<table class="table table-bordered table-hover adk-table history-table" data-url="' .
	$this->url->link( 
		$this->a->type . '/' . $this->a->code . '/fetch_history',
		'token=' . $this->session->data['token'],
		'SSL'
	) .
'" data-type="history">' .
	'<colgroup>';

		$hide_fields = explode( ',', $this->a->config( 'history_fields_hide', '' ) );

		foreach( $columns as $column => $name ) {
			if ( ! in_array( $column, $hide_fields ) ) {
				$ret .=
			'<col id="col-' . $column . '"' . ( $this->is_filterd_by( $data, $column ) ?
				' class="history-highlight-col"' : '' ) . '>';
			}
		}

		$ret .=
	'</colgroup>' .
	'<thead>' .
		'<tr>';

		foreach( $columns as $column => $name ) {
			if ( ! in_array( $column, $hide_fields ) ) {
				$ret .=
				'<th data-type="' .$column . '">' .
					$this->get_order_icon( $data, $column ) . '&nbsp;' . $name .
				'</th>';
			}
		}

		$ret .=
		'</tr>' .
	'</thead>' .
	'<tbody>';

		foreach( $this->a->get_history( $data ) as $record ) {
			$ret .=
		'<tr data-id="' . $record['id'] . '">' .
			'<td><input type="checkbox">'                                       . '</td>';
			$ret .= ! in_array( 'date_added', $hide_fields ) ? '<td>' . $record['date_added'] . '</td>' : '';
			$ret .= ! in_array( 'to', $hide_fields ) ? '<td>' . htmlentities( $record['to'] ) . '</td>' : '';
			$ret .= ! in_array( 'subject', $hide_fields ) ? '<td>' . htmlentities( $record['subject'] ) . '</td>' : '';
			$ret .= ! in_array( 'status', $hide_fields ) ? '<td>' . $this->model->get_history_status_name( $record['status'] ) . '</td>' : '';
			$ret .= ! in_array( 'newsletter', $hide_fields ) ? '<td>' . $record['newsletter_name'] . '</td>' : '';
			$ret .= ! in_array( 'template', $hide_fields ) ? '<td>' . $record['template'] . '</td>' : ''; 
			$ret .= ! in_array( 'attachment', $hide_fields ) ? '<td>' . $this->model->format_files( $record['attachment'] ) . '</td>' : '';
			$ret .= ! in_array( 'date_visited', $hide_fields ) ? '<td align="center">' . $this->is_visited_icon( $record ) . '</td>' : '';
			$ret .= ! in_array( 'date_viewed', $hide_fields ) ? '<td align="center">' . $this->is_viewed_icon( $record ) . '</td>' : '';
			$ret .= ! in_array( 'log', $hide_fields ) ? '<td>' . $this->a->r( array(
					'type'        => 'button',
					'icon'        => 'fa-binoculars',
					'button_type' => 'primary',
					'text_after'  => $this->a->__( 'Log' ),
					'custom_data' => 'data-log=\'' . $record['log'] . '\'',
					'class'       => 'history-log',
				) ) . '</td>' : '';
			$ret .= '</tr>';
		} 

		$ret .=
	'</tbody></table></div>';

		$pagination = new Pagination();
		$pagination->total = $total;
		$pagination->page = $page;
		$pagination->limit = $data['limit'];
		$pagination->url = '{page}';

		$ret .= $pagination->render();

		return $ret;
	}

	/**
	 * Returns icon to show whether newsletter was visited
	 * @param type $data 
	 * @return type
	 */
	public function is_visited_icon( $data ) {
		$ret = '';

		if ( $data['tracking_visit_id'] ) {
			if ( $data['date_visited'] ) {
				$ret = '<i class="fa fa-check fa-2x text-success" title="' . $data['date_visited'] .'"></i>';

			} else {
				$ret = '<i class="fa fa-close fa-2x text-danger"></i>';
			}
		}

		return $ret;
	}

	/**
	 * Returns icon to show whether newsletter was viewed
	 * @param type $data 
	 * @return type
	 */
	public function is_viewed_icon( $data ) {
		$ret = '';

		if ( $data['tracking_id'] ) {
			if ( $data['date_viewed'] ) {
				$ret = '<i class="fa fa-check fa-2x text-success" title="' . $data['date_viewed'] .'"></i>';

			} else {
				$ret = '<i class="fa fa-close fa-2x text-danger"></i>';
			}
		}

		return $ret;
	}

	/**
	 * Returns sort order icon for history table
	 * @param array $data Filter data
	 * @param string $item Table column name
	 * @return string
	 */
	public function get_order_icon( $data, $item ) {
		if ( 'id' === $item ) {
			$ret = '';

		} else {
			$ret = '<i class="fa fa-sort-amount-asc table-sort"></i>';

			if ( ! empty( $data['order_by'][ $item ] ) ) {
				if ( 'asc' === strtolower( $data['order_by'][ $item ] ) ) {
					$ret = '<i class="fa fa-sort-amount-asc table-sort active-sort"></i>';

				} elseif ( 'desc' === strtolower( $data['order_by'][ $item ] ) ) {
					$ret = '<i class="fa fa-sort-amount-desc table-sort active-sort"></i>';
				}
			}
		}

		return $ret;
	}

	/**
	 * Check whether history data filtered by specific field
	 * @param array $data Filter data
	 * @param string $field Field name
	 * @return boolean
	 */
	public function is_filterd_by( $data, $field ) {
		$ret = false;

		if ( ! empty( $data['where'] ) ) {
			foreach( (array)$data['where'] as $where ) {
				if (
					isset( $where['field'] ) &&
					strtolower( substr( strstr( $where['field'], '.' ), 1 ) ) === strtolower( $field )
				) {
					$ret = true;
					break;
				}
			}
		}

		return $ret;
	}

	/**
	 * History fetch action
	 * @return void
	 */
	public function fetch_history() {
		$data = $this->request->post;
		$this->response->setOutput( $this->render_history( $data ) );
	}

	/**
	 * Renders history management tab
	 * @return string
	 */
	public function render_history_filter() {
		$ret = '';

		// Management row
		$ret .= '<div class="row">';
		$ret .= '<div class="pull-right filter-controls">';

		// Apply filter button
		$ret .= $this->a->r( array(
			'type'        => 'button',
			'button_type' => 'success',
			'id'          => 'apply-history-filter',
			'class'       => 'apply-table-filter',
			'icon'        => 'fa-filter',
			'text_before' => $this->a->__( 'Apply' ),
			'custom_data' => 'data-url="' . $this->url->link(
				$this->a->type . '/' . $this->a->code . '/history_clean',
				'token=' . $this->session->data['token'],
				'SSL'
			) . '"',
		) );

		// Reset filter button
		$ret .= $this->a->r( array(
			'type'        => 'button',
			'button_type' => 'warning',
			'id'          => 'reset-history-filter',
			'class'       => 'clear-table-filter',
			'icon'        => 'fa-eraser',
			'text_before' => $this->a->__( 'Reset' ),
			'custom_data' => 'data-url="' . $this->url->link(
				$this->a->type . '/' . $this->a->code . '/history_clean',
				'token=' . $this->session->data['token'],
				'SSL'
			) . '"',
		) );

		// Refresh history button
		$ret .= $this->a->r( array(
			'type'        => 'button',
			'button_type' => 'primary',
			'id'          => 'refresh-history',
			'class'       => '',
			'icon'        => 'fa-refresh',
			'text_before' => $this->a->__( 'Refresh' ),
		) );

		// Clear history button
		$ret .= $this->a->r( array(
			'type'        => 'button',
			'button_type' => 'danger',
			'id'          => 'clear-history',
			'icon'        => 'fa-close',
			'text_before' => $this->a->__( 'Clear history' ),
			'custom_data' => 'data-url="' . $this->url->link(
				$this->a->type . '/' . $this->a->code . '/history_clean',
				'token=' . $this->session->data['token'],
				'SSL'
			) . '"',
		) );
		$ret .= '</div>';
		$ret .= '</div>'; // Row end

		// First row
		$ret .= '<div class="row">';

		$ret .= '<div class="col-sm-4"><b>' . $this->a->__( 'To' ) . '</b>';
		$ret .= $this->a->r( array(
			'type'        => 'select',
			'class'       => 'table-filter-autofill table-filter',
			'custom_data' => 'data-type="to" multiple="multiple"',
		) );
		$ret .= '</div>';

		$ret .= '<div class="col-sm-4"><b>' . $this->a->__( 'Subject' ) . '</b>';
		$ret .= $this->a->r( array(
			'type'        => 'select',
			'class'       => 'table-filter-autofill table-filter',
			'custom_data' => 'data-type="subject" multiple="multiple"',
		) );
		$ret .= '</div>';

		$ret .= '<div class="col-sm-4"><b>' . $this->a->__( 'Status' ) . '</b>';
		$ret .= $this->a->r( array(
			'type'        => 'select',
			'class'       => 'table-filter select2',
			'custom_data' => 'data-type="status" multiple="multiple"',
			'value'       => array(
				\Advertikon\Mail\Advertikon::EMAIL_STATUS_SUCCESS => $this->model->get_history_status_name( \Advertikon\Mail\Advertikon::EMAIL_STATUS_SUCCESS ),
				\Advertikon\Mail\Advertikon::EMAIL_STATUS_FAIL    => $this->model->get_history_status_name( \Advertikon\Mail\Advertikon::EMAIL_STATUS_FAIL),
			)
		) );
		$ret .= '</div>';

		$ret .= '</div>'; // Row end

 		// Second row
		$ret .= '<div class="row">';

		$ret .= '<div class="col-sm-4"><b>' . $this->a->__( 'Template' ) . '</b>';
		$ret .= $this->a->r( array(
			'type'        => 'select',
			'class'       => 'table-filter-autofill table-filter',
			'custom_data' => 'data-type="template" multiple="multiple"',
		) );
		$ret .= '</div>';

		$ret .= '<div class="col-sm-4"><b>' . $this->a->__( 'Attachment' ) . '</b>';
		$ret .= $this->a->r( array(
			'type'        => 'select',
			'class'       => 'table-filter-autofill table-filter',
			'custom_data' => 'data-type="attachment" multiple="multiple"',
		) );
		$ret .= '</div>';

		$ret .= '<div class="col-sm-4"><b>' . $this->a->__( 'Date' ) . '</b>';
		$ret .= $this->a->r( array(
			'type'        => 'select',
			'id'          => 'history-filter-date',
			'class'       => 'form-control table-filter table-filter-date',
			'custom_data' => 'data-type="date_added"',
			'value'       => array(
				'select'    => '',
				'day'       => $this->a->__( 'Last day' ),
				'week'      => $this->a->__( 'Last week' ), 
				'two_weeks' => $this->a->__( 'Last 14 days' ),
				'month'     => $this->a->__( 'Last month'  ),
				'custom'    => $this->a->__( 'Custom' ),
			),
		) );

		$ret .= '</div>';
		$ret .= '</div>'; // Row end

		// Third row
		$ret .= '<div class="row">';

		$ret .= '<div class="col-sm-4"><b>' . $this->a->__( 'Newsletter' ) . '</b>';
		$ret .= $this->a->r( array(
			'type'        => 'select',
			'class'       => 'table-filter-autofill table-filter',
			'custom_data' => 'data-type="newsletter" multiple="multiple"',
		) );
		$ret .= '</div>';

		$hide_fields = explode( ',', $this->a->config( 'history_fields_hide', '' ) );

		$ret .= '<div class="col-sm-4"><b>' . $this->a->__( 'Visible fields' ) . '</b>';
		$ret .= $this->a->r( array(
			'type'        => 'buttongroup',
			'class'       => '',
			'custom_data' => 'data-name="history_fields_hide"',
			'css'         => 'display: block;',
			'buttons'     => array(
				array(
					'type'        => 'button',
					'icon'        => 'fa-calendar',
					'title'       => $this->a->__( 'Date' ),
					'custom_data' => 'data-value="date_added"',
					'class'       => 'table-show-field',
					'button_type' => in_array( 'date_added', $hide_fields ) ? 'void' : 'default',
				),
				array(
					'type'        => 'button',
					'icon'        => 'fa-address-book',
					'title'       => $this->a->__( 'To' ),
					'custom_data' => 'data-value="to"',
					'class'       => 'table-show-field',
					'button_type' => in_array( 'to', $hide_fields ) ? 'void' : 'default',
				),
				array(
					'type'        => 'button',
					'icon'        => 'fa-edit',
					'title'       => $this->a->__( 'Subject' ),
					'custom_data' => 'data-value="subject"',
					'class'       => 'table-show-field',
					'button_type' => in_array( 'subject', $hide_fields ) ? 'void' : 'default',
				),
				array(
					'type'        => 'button',
					'icon'        => 'fa-info-circle',
					'title'       => $this->a->__( 'Status' ),
					'custom_data' => 'data-value="status"',
					'class'       => 'table-show-field',
					'button_type' => in_array( 'status', $hide_fields ) ? 'void' : 'default',
				),
				array(
					'type'        => 'button',
					'icon'        => 'fa-paper-plane',
					'title'       => $this->a->__( 'Newsletter' ),
					'custom_data' => 'data-value="newsletter"',
					'class'       => 'table-show-field',
					'button_type' => in_array( 'newsletter', $hide_fields ) ? 'void' : 'default',
				),
				array(
					'type'        => 'button',
					'icon'        => 'fa-cogs',
					'title'       => $this->a->__( 'Template' ),
					'custom_data' => 'data-value="template"',
					'class'       => 'table-show-field',
					'button_type' => in_array( 'template', $hide_fields ) ? 'void' : 'default',
				),
				array(
					'type'        => 'button',
					'icon'        => 'fa-file-zip-o',
					'title'       => $this->a->__( 'Attachment' ),
					'custom_data' => 'data-value="attachment"',
					'class'       => 'table-show-field',
					'button_type' => in_array( 'attachment', $hide_fields ) ? 'void' : 'default',
				),
				array(
					'type'        => 'button',
					'icon'        => 'fa-sign-in',
					'title'       => $this->a->__( 'Visited' ),
					'custom_data' => 'data-value="date_visited"',
					'class'       => 'table-show-field',
					'button_type' => in_array( 'date_visited', $hide_fields ) ? 'void' : 'default',
				),
				array(
					'type'        => 'button',
					'icon'        => 'fa-eye',
					'title'       => $this->a->__( 'Viewed' ),
					'custom_data' => 'data-value="date_viewed"',
					'class'       => 'table-show-field',
					'button_type' => in_array( 'date_viewed', $hide_fields ) ? 'void' : 'default',
				),
				array(
					'type'        => 'button',
					'icon'        => 'fa-book',
					'title'       => $this->a->__( 'Log' ),
					'custom_data' => 'data-value="log"',
					'class'       => 'table-show-field',
					'button_type' => in_array( 'log', $hide_fields ) ? 'void' : 'default',
				),
			),
		) );
		$ret .= '</div>';

		$ret .= '</div>'; // End of row

		return $ret;
	}

	/**
	 * Autofill back-end for table's filter data
	 * @return void
	 */
	public function table_filter_autofill() {
		$list = array();
		$page = 1;

		try {

			if ( ! isset( $this->request->request['type'] ) ) {
				throw new \Advertikon\Exception( 'Type missing' );
			}

			if ( ! isset( $this->request->request['table'] ) ) {
				throw new \Advertikon\Exception( 'Table missing' );
			}

			$table = $this->request->request['table'];

			if ( isset( $this->request->request['page'] ) ) {
				$page = $this->request->request['page'];
			}

			if ( isset( $this->request->request['count'] ) ) {
				$count = $this->request->request['count'];
			}

			$query = '';
			if ( isset( $this->request->request['q'] ) ) {
				$query = $this->request->request['q'];
			}

			$type = $this->request->request['type'];
			$page = max( 1, (int)$page );
			$count = (int)$count === 0 ? 20 : $count;
			$start = ( $page - 1 ) * $count;

			switch ( $table ) {
			case 'history' :
				$list = $this->a->get_history_by( $type, $query, $start, $count );
				break;
			case 'newsletter_list' :
				$list = $this->model->get_newsletter_autofill( $type, $query, $start, $count );
				break;
			case 'subscribers' :
				$id = $this->a->request( 'custom' );

				if ( empty( $id ) ) {
					$id = -1;
				}

				$list = $this->model->get_subscribers_autofill( $type, $query, $start, $count, $id );
				break;
			default:
				throw new \Advertikon\Exception( sprintf( 'Undefined table name "%s" to get autofill data for', $table ) );
				break;
			}

			if( ! $list ) {
				throw new \Advertikon\Exception( 'Empty list' );
			}

		} catch( \Advertikon\Exception $e ) {
			if ( function_exists( 'adk_log' ) ) {
				adk_log( $e->getMessage() );
			}
		}

		$this->response->setOutput( json_encode( $list ) );
	}

	/**
	 * Clean email history action
	 * @return void
	 */
	public function history_clean() {
		$resp = array();
		$ids = isset( $this->request->request['id'] ) ?
			$this->request->request['id'] : null;

		try {
			if ( ! $this->model->clean_history( $ids ) ) {
				throw new \Advertikon\Exception( $this->a->__( 'Fail to clean email history' ) );
			}

			$resp['success'] = $this->a->__( 'History was successfully cleaned' );

		} catch ( \Advertikon\Exception $e ) {
			$resp['error'] = $e->getMessage();
		}

		$this->response->setOutput( json_encode( $resp ) );
	}

	/**
	 * Saves history log into browser
	 * @return void
	 */
	public function save_history_log() {
		if ( isset( $this->request->get['log_id'] ) ) {

			$history = $this->a->q( array(
				'table' => $this->a->history_table,
				'query' => 'select',
				'where' => array(
					'field'      => 'id',
					'value'     => $this->request->get['log_id'],
					'operation' => '=', 
				),
			) );

			if ( $history ) {
				$name = str_replace(
					array( ' ', '<', '>' ),
					array( '_', '' ),
					trim( $history['to'] ) . '-' . trim( $history['date_added'] )
				);

				header( 'Content-Type: text/plain' );
				header( 'Content-Disposition: attachment; filename="' . $name . '.txt"' );
				echo $history['log'];
			}
		}
	}

	/**
	 * Newsletter action
	 * @return void
	 */
	public function newsletter() {
		global $adk_mail_hook;

		$hook = 'newsletter.';
		$page_limit = 20;
		$ret = array();

		try {

			if ( ! $this->user->hasPermission( 'modify', 'marketing/contact' ) ) {
				throw new \Advertikon\Exception( $this->a->__( 'You have no permissions to send newsletter') );
			}

			if ( ! $this->a->post( 'message' ) ) {
				throw new \Advertikon\Exception( $this->a->__( 'The message is mandatory' ) );
			}

			$this->load->model( 'setting/store' );
			$store_info = $this->model_setting_store->getStore( $this->a->post( 'store_id' ) );

			if ( $store_info ) {
				$store_name = $store_info['name'];

			} else {
				$store_name = $this->config->get('config_name');
			}

			// $this->load->model('marketing/affiliate');
			$this->load->model('sale/order');

			if ( $this->a->request( 'page') ) {
				$page = $this->a->request( 'page' );

			} else {
				$page = 1;
			}

			$email_total = 0;
			$emails = array();
			$is_queue = $this->a->config( 'queue' );
			$newsletter = $this->a->post( 'to' );
			$hook .= $newsletter;
			$this->a->adk_newsletter_id = 0;
			$subscribers_only = (boolean)$this->a->p( 'adk_subscribers_only' );
			$data = array();

			if ( ! $is_queue ) {
				$data['start'] = ( $page - 1 ) * $page_limit;
				$data['limit'] = $page_limit;
			}

			switch ( $newsletter ) {
				case 'newsletter':
					$data['filter_newsletter'] = 1;

					if ( ! $is_queue ) {
						$email_total = $this->model->get_total_customers( $data );
					}

					$results = $this->model->get_customers( $data );

					foreach ( $results as $result ) {
						$emails[] = array(
							'email'=> $result['email'],
							'name' => $result['firstname'] . ' ' . $result['lastname'], 
						);
					}
					break;
				case 'customer_all':
					if ( ! $is_queue ) {
						$email_total = $this->model->get_total_customers( $data );
					}

					$results = $this->model->get_customers( $data );

					foreach ( $results as $result ) {
						$emails[] = array(
							'email'=> $result['email'],
							'name' => $result['firstname'] . ' ' . $result['lastname'], 
						);
					}
					break;
				case 'customer_group':
					$data['filter_customer_group_id'] = $this->request->post[ 'customer_group_id' ];

					if ( $subscribers_only ) {
						$data['filter_newsletter'] = 1;
					}

					if ( ! $is_queue ) {
						$email_total = $this->model->get_total_customers( $data );	
					}

					$results = $this->model->get_customers( $data );

					foreach ( $results as $result ) {
						$emails[] = array(
							'email'=> $result['email'],
							'name' => $result['firstname'] . ' ' . $result['lastname'], 
						);
					}
					break;
				case 'customer':
					if ( $this->a->p( 'customer' ) ) {
						$data['customer_id'] = $this->a->p( 'customer' );

						if ( ! $is_queue ) {
							$email_total = $this->model->get_total_customers( $data );	
						}

						foreach ( $this->model->get_customers( $data ) as $customer ) {
							$emails[] = array(
								'email' => $customer['email'],
								'name' => $customer['firstname'] . ' ' . $customer['lastname'],
							);
						}
					}
					break;
				case 'affiliate_all':
					if ( $subscribers_only ) {
						$data['filter_newsletter'] = 1;
					}

					$affiliates = $this->model->get_affiliates( $data );

					if ( ! $is_queue ) {
						$email_total = count( $affiliates );
					}

					foreach ( $affiliates as $affiliate ) {
						$emails[] = array(
							'email' => $affiliate['email'],
							'name' => $affiliate['firstname'] . ' ' . $affiliate['lastname'],
						);
					}
					break;
				case 'affiliate':
					if ( $this->a->p( 'affiliate' ) ) {
						$data['affiliate_id'] = $this->a->p( 'affiliate' );

						$affiliates = $this->model->get_affiliates( $data );

						if ( ! $is_queue ) {
							$email_total = count( $affiliates );
						}

						foreach ( $affiliates as $affiliate ) {
							$emails[] = array(
								'email' => $affiliate['email'],
								'name' => $affiliate['firstname'] . ' ' . $affiliate['lastname'],
							);
						}
					}
					break;
				case 'product':
					if ( $this->a->p( 'product' ) ) {
						if ( $is_queue ) {
							$start = null;
							$limit = null;

						} else {
							$start = $data['start'];
							$limit = $data['limit'];
						}

						if ( ! $is_queue ) {
							$email_total = count(
								$this->model->get_order_subscribers(
									$this->a->p( 'product' ),
									$subscribers_only
								)
							);
						}

						$results = $this->model->get_order_subscribers(
							$this->a->request( 'product' ),
							$subscribers_only,
							$start,
							$limit
						);

						foreach ( $results as $result ) {
							$emails[] = array(
								'email'=> $result['email'],
								'name' => $result['firstname'] . ' ' . $result['lastname'], 
							);
						}
					}
					break;
				default: 
					if ( ! is_numeric( $newsletter ) ) {
						trigger_error( sprintf( 'Newsletter: undefined newsletter "%s"', $newsletter ) );
						throw new \Advertikon\Exception( $this->a->__( 'Undefined newsletter' ) );
					}

					$this->a->adk_newsletter_id = $newsletter;

					$results = $this->a->q( array_merge( array(
						'table'  => $this->a->newsletter_subscribers_table,
						'query'  => 'select',
						'calc'   => true,
						'where'  => array(
							array(
								'field'     => 'newsletter',
								'operation' => '=',
								'value'     => $newsletter,
							),
							array(
								'field'     => 'status',
								'operation' => '=',
								'value'     => \Advertikon\Mail\Advertikon::SUBSCRIBER_STATUS_ACTIVE,
							),
						),
					), $data ) );

					if ( ! $is_queue ) {
						$email_total = $this->a->q()->get_calc_rows();
					}

					foreach ( $results as $result ) {
						$emails[] = array(
							'email'=> $result['email'],
							'name' => $result['name'], 
						);
					}
					break;
			}

			if ( $emails ) {
				$sent_count = 0;

				if ( $is_queue ) {
					$ret['success'] =  $this->a->__( '%s email(s) have been added to the queue', count( $emails ) );

				} else {
					$start = ( $page - 1 ) * $page_limit;
					$end = $start + $page_limit;

					if ( $end < $email_total ) {
						$ret['success'] = $this->a->__( '%s email(s) of %s have been sent', $page * $page_limit, $email_total );

					} else {
						$ret['success'] = $this->a->__( '%s email(s) have been successfully sent', $email_total );
					}

					if ( $end < $email_total ) {
						$ret['next'] = str_replace( 
							'&amp;',
							'&',
							$this->url->link(
								$this->a->type . '/' . $this->a->code . '/newsletter',
								'token=' . $this->session->data['token'] . '&page=' . ( $page + 1 ),
								'SSL'
							)
						);

					} else {
						$ret['next'] = '';
					}
				}

				$message  = '<html dir="ltr" lang="en">' . "\n";
				$message .= '  <head>' . "\n";
				$message .= '    <title>' . $this->request->post['subject'] . '</title>' . "\n";
				$message .= '    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">' . "\n";
				$message .= '  </head>' . "\n";
				$message .= '  <body>' . html_entity_decode($this->request->post['message'], ENT_QUOTES, 'UTF-8') . '</body>' . "\n";
				$message .= '</html>' . "\n";

				foreach ( $emails as $email ) {
					$adk_mail_hook = $hook;
					$this->a->adk_subscriber_name = $email['name'];
					$this->a->adk_subscriber_email = $email['email'];

					if ( $this->a->is_email( $email['email'] ) ) {
						$mail = new Mail();
						$this->a->init_mail( $mail );

						$mail->cc = $this->a->post( 'cc' );
						$mail->bcc = $this->a->post( 'bcc' );
						$mail->setReplyTo( $this->a->post( 'return' ) );
						$mail->addAttachment( $this->a->post( 'attachment' ) );

						$mail->setTo( $email['email'] );
						$mail->setFrom( $this->config->get( 'config_email' ) );
						$mail->setSender( html_entity_decode( $store_name, ENT_QUOTES, 'UTF-8' ) );
						$mail->setSubject( html_entity_decode( $this->a->post( 'subject' ), ENT_QUOTES, 'UTF-8' ) );
						$mail->setHtml( $message );
						
						if( $mail->send() ) {
							$sent_count++;
						}

					} else {
						trigger_error(
							sprintf(
								'Newsletter: invalid recipient\'s email format "%s" - sending skipped',
								$email['email']
							)
						);
					}
				}

			} else {
				throw new \Advertikon\Exception( $this->a->__( 'Subscribers not found' ) );
			}

		} catch ( \Advertikon\Exception $e ) {
			$ret['error'] = $e->getMessage();
		}

		$this->response->setOutput( json_encode( $ret ) );
	}

	/**
	 * Renders newsletter's list pane content
	 * @param array $data Filter array
	 * @return string
	 */
	public function render_newsletter( $data = array() ) {

		$data['table'] = $this->a->history_table;

		$total_query = $this->a->q( array(
			'table' => $this->a->newsletter_list_table,
			'query' => 'select',
			'field' => array( 'count' => 'count(*)' ),
		) );

		$total = $total_query['count'] + 1;
		$page = 1;

		if ( empty( $data['limit'] ) ) {
			$data['limit'] = 20;
		}

		if ( isset( $data['page'] ) ) {
			if ( 'last' === $data['page'] ) {
				$page = ceil( $total / $data['limit'] );

			} else {
				$page = (int)$data['page'];
				$page = $page <= 0 ? 1 : $page;
			}
		}

		$data['start'] = ( $page - 1 ) * $data['limit'];
		$hide_fields = explode( ',', $this->a->config( 'newsletter_fields_hide', '' ) );

		$columns = array(
			'id'          => $this->a->__( 'ID' ),
			'name'        => $this->a->__( 'Name' ),
			'description' => $this->a->__( 'Description' ),
			'date_added'  => $this->a->__( 'Date added' ),
			'status'      => $this->a->__( 'Status' ),
			'widget'      => $this->a->__( 'Widget' ),
			'active'      => $this->a->__( 'Active subscribers' ),
			'inactive'    => $this->a->__( 'Inactive subscribers' ),
		);

		$ret =
'<div class="table-responsive">' .
'<table class="table table-bordered table-hover adk-table newsletter-list-table" data-type="newsletter_list">' .
	'<colgroup>';

		foreach( $columns as $column => $name ) {
			if ( ! in_array( $column, $hide_fields ) ) {
				$ret .=
			'<col id="col-' . $column . '"' . ( $this->is_filterd_by( $data, $column ) ?
				' class="history-highlight-col"' : '' ) . '>';
			}
		}

		$ret .=
	'</colgroup>' .
	'<thead>' .
		'<tr>';

		foreach( $columns as $column => $name ) {
			if ( ! in_array( $column, $hide_fields ) ) {
				$ret .=
				'<th data-type="' . $column . '">' . $this->get_order_icon( $data, $column ) . '&nbsp;' . $name . '</th>';
			}
		}

		$ret .=
		'</tr>' .
	'</thead>' .
	'<tbody>';

		$list = $this->a->get_newsletter_list( $data );

		if ( count( $list ) ) {
			foreach( $list as $record ) { 
				$ret .=
		'<tr data-id="' . $record['id'] . '" class="newsletter-line">' .
			'<td>' . '<input type="checkbox" class="newsletter-select">'                                                       . '</td>' .
			( in_array( 'name', $hide_fields ) ? '': '<td>' . $record['name']                                                    . '</td>' ) .
			( in_array( 'description', $hide_fields ) ? '': '<td>' . $record['description']                                      . '</td>' ) .
			( in_array( 'date_added', $hide_fields ) ? '': '<td>' . $record['date_added']                                        . '</td>' ) .
			( in_array( 'status', $hide_fields ) ? '': '<td align="center">' . $this->get_newsletter_status( $record['status'] ) . '</td>' ) .
			( in_array( 'widget', $hide_fields ) ? '': '<td>' . $record['widget_name']                                           . '</td>' ) . 
			( in_array( 'active', $hide_fields ) ? '': '<td>' . $record['active']                                                . '</td>' ) . 
			( in_array( 'inactive', $hide_fields ) ? '': '<td>' . $record['inactive']                                            . '</td>' ) .
		'</tr>';
			} 
		}

		$ret .=
	'</tbody></table></div>';

		$pagination = new Pagination();
		$pagination->total = $total;
		$pagination->page = $page;
		$pagination->limit = $data['limit'];
		$pagination->url = '{page}';

		$ret .= $pagination->render();

		return $ret;
	}

	/**
	 * Returns newsletter status icon
	 * @param string $status Status code
	 * @return string
	 */
	public function get_newsletter_status( $status ) {
		switch ( $status ) {

		// Initial state
		case \Advertikon\Mail\Advertikon::NEWSLETTER_STATUS_INACTIVE:
			$icon = 'fa-ban';
			$style = 'text-danger';
			$title = $this->a->__( 'Not active' );
			break;

		// Active
		case \Advertikon\Mail\Advertikon::NEWSLETTER_STATUS_ACTIVE:
			$icon = 'fa-check';
			$style = 'text-success';
			$title = $this->a->__( 'Active' );
			break;
		default:
			$style = 'text-muted';
			$style = '';
			$title = '';
			break;
		}

		return sprintf( '<i class="fa %s fa-2x %s" title="%s"></>', $icon, $style, $title );
	}

	/**
	 * History fetch action
	 * @return void
	 */
	public function fetch_newsletter_list() {
		$data = $this->request->post;
		$this->response->setOutput( $this->render_newsletter( $data ) );
	}

	/**
	 * Renders newsletter filter tab
	 * @return string
	 */
	public function render_newsletter_filter() {
		$ret = '';

		// Management row
		$ret .= '<div class="row">';
		$ret .= '<div class="pull-right filter-controls">';

		// Apply filter button
		$ret .= $this->a->r( array(
			'type'        => 'button',
			'button_type' => 'success',
			'id'          => 'apply-newsletter-list-filter',
			'class'       => 'apply-table-filter',
			'icon'        => 'fa-filter',
			'text_before' => $this->a->__( 'Apply' ),
		) );

		// Reset filter button
		$ret .= $this->a->r( array(
			'type'        => 'button',
			'button_type' => 'warning',
			'id'          => 'reset-newsletter-list-filter',
			'class'       => 'clear-table-filter',
			'icon'        => 'fa-eraser',
			'text_before' => $this->a->__( 'Reset' ),
		) );

		// Clear history button
		$ret .= $this->a->r( array(
			'type'        => 'button',
			'button_type' => 'danger',
			'id'          => 'delete-newsletter',
			'icon'        => 'fa-close',
			'text_before' => $this->a->__( 'Delete newsletter' ),
			'custom_data' => 'data-url="' . $this->url->link(
				$this->a->type . '/' . $this->a->code . '/delete_newsletter',
				'token=' . $this->session->data['token'],
				'SSL'
			) . '"',
		) );
		$ret .= '</div>';
		$ret .= '</div>'; // Row end

		// First row
		$ret .= '<div class="row">';

		$ret .= '<div class="col-sm-4"><b>' . $this->a->__( 'Status' ) . '</b>';
		$ret .= $this->a->r( array(
			'type'        => 'select',
			'class'       => 'table-filter newsletter-status-select',
			'custom_data' => 'data-type="status" multiple="multiple"',
			'value'       => $this->model->get_newsletter_statuses_for_select( $this->a->newsletter_list_table ),
		) );
		$ret .= '</div>';

		$ret .= '<div class="col-sm-4"><b>' . $this->a->__( 'Widget' ) . '</b>';
		$ret .= $this->a->r( array(
			'type'        => 'select',
			'class'       => 'table-filter table-filter-autofill',
			'custom_data' => 'data-type="widget" multiple="multiple"',
		) );
		$ret .= '</div>';

		$ret .= '<div class="col-sm-4"><b>' . $this->a->__( 'Date' ) . '</b>';
		$ret .= $this->a->r( array(
			'type'        => 'select',
			'id'          => 'newsletter-list-filter-date',
			'class'       => 'form-control table-filter-date table-filter',
			'custom_data' => 'data-type="date_added"',
			'value'       => array(
				'select'    => '',
				'day'       => $this->a->__( 'Last day' ),
				'week'      => $this->a->__( 'Last week' ), 
				'two_weeks' => $this->a->__( 'Last 14 days' ),
				'month'     => $this->a->__( 'Last month'  ),
				'custom'    => $this->a->__( 'Custom' ),
			),
		) );
		$ret .= '</div>';

		$ret .= '</div>'; // Row end
		$ret .= '<div class="row">'; // New row

		$hide_fields = explode( ',', $this->a->config( 'newsletter_fields_hide', '' ) );

		$ret .= '<div class="col-sm-4"><b>' . $this->a->__( 'Visible fields' ) . '</b>';
		$ret .= $this->a->r( array(
			'type'        => 'buttongroup',
			'class'       => '',
			'custom_data' => 'data-name="newsletter_fields_hide"',
			'css'         => 'display: block;',
			'buttons'     => array(
				array(
					'type'        => 'button',
					'icon'        => 'fa-tags',
					'title'       => $this->a->__( 'Name' ),
					'custom_data' => 'data-value="name"',
					'class'       => 'table-show-field',
					'button_type' => in_array( 'name', $hide_fields ) ? 'void' : 'default',
				),
				array(
					'type'        => 'button',
					'icon'        => 'fa-font',
					'title'       => $this->a->__( 'Description' ),
					'custom_data' => 'data-value="description"',
					'class'       => 'table-show-field',
					'button_type' => in_array( 'description', $hide_fields ) ? 'void' : 'default',
				),
				array(
					'type'        => 'button',
					'icon'        => 'fa-calendar',
					'title'       => $this->a->__( 'Date added' ),
					'custom_data' => 'data-value="date_added"',
					'class'       => 'table-show-field',
					'button_type' => in_array( 'date_added', $hide_fields ) ? 'void' : 'default',
				),
				array(
					'type'        => 'button',
					'icon'        => 'fa-info-circle',
					'title'       => $this->a->__( 'Status' ),
					'custom_data' => 'data-value="status"',
					'class'       => 'table-show-field',
					'button_type' => in_array( 'status', $hide_fields ) ? 'void' : 'default',
				),
				array(
					'type'        => 'button',
					'icon'        => 'fa-puzzle-piece',
					'title'       => $this->a->__( 'Widget' ),
					'custom_data' => 'data-value="widget"',
					'class'       => 'table-show-field',
					'button_type' => in_array( 'widget', $hide_fields ) ? 'void' : 'default',
				),
				array(
					'type'        => 'button',
					'icon'        => 'fa-check',
					'title'       => $this->a->__( 'Active' ),
					'custom_data' => 'data-value="active"',
					'class'       => 'table-show-field',
					'button_type' => in_array( 'active', $hide_fields ) ? 'void' : 'default',
				),
				array(
					'type'        => 'button',
					'icon'        => 'fa-close',
					'title'       => $this->a->__( 'Inactive' ),
					'custom_data' => 'data-value="inactive"',
					'class'       => 'table-show-field',
					'button_type' => in_array( 'inactive', $hide_fields ) ? 'void' : 'default',
				),
			),
		) );
		$ret .= '</div>';
		$ret .= '</div>'; // Row end

		return $ret;
	}

	/**
	 * Adds new newsletter
	 * @return type
	 */
	public function add_newsletter() {
		$ret = array();

		try {
			if ( empty( $this->request->request['name'] ) ) {
				throw new \Advertikon\Exception( $this->a->__( 'Name of newsletter is mandatory' ) );
			}

			$name = $this->request->request['name'];

			$description = isset( $this->request->request['description'] ) ?
				$this->request->request['description'] : '';

			$exists = $this->a->q( array(
				'table' => $this->a->newsletter_list_table,
				'query' => 'select',
				'where' => array(
					'field'     => 'name',
					'operation' => '=',
					'value'     => $name,
				),
			) );

			if ( count( $exists ) ) {
				throw new \Advertikon\Exception( $this->a->__( 'Newsletter "%s" already exists', $name ) );
			}

			$query = $this->a->q( array(
				'table' => $this->a->newsletter_list_table,
				'query' => 'insert',
				'values' => array(
					'name'        => $name,
					'description' => $description,
					'date_added'  => date( 'c' ),
					'status'      => 0,
				),
			) );

			if ( ! $query ) {
				throw new \Advertikon\Exception( $this->a->__( 'Fail to create newsletter' ) );
			}

			$resp['success'] = $this->a->__( 'Newsletter has been created' );
			$resp['id'] = $this->db->getLastId();

		} catch ( \Advertikon\Exception $e ) {
			$resp['error'] = $e->getMessage();
		}

		$this->response->setOutput( json_encode( $resp ) );
	}

	/**
	 * Delete newsletter action
	 * @return void
	 */
	public function delete_newsletter() {
		$ret = array();

		try {

			if ( empty( $this->request->request['ids'] ) ) {
				throw new \Advertikon\Exception( $this->a->__( 'Newsletters\' ID is mandatory' ) );
			}

			$ids = $this->request->request['ids'];

			$query = $this->a->q( array(
				'table' => $this->a->newsletter_list_table,
				'query' => 'delete',
				'where' => array(
					'field' => 'id',
					'operation' => 'in',
					'value' => $ids,
				),
			) );

			if ( ! $query ) {
				throw new \Advertikon\Exception( $this->a->__( 'Fail to delete newsletter' ) );
			}

			$this->a->q( array(
				'table' => $this->a->history_table,
				'query' => 'delete',
				'where' => array(
					'field'     => 'newsletter',
					'operation' => 'in',
					'value'     => $ids,
				),
			) );

			$ret['success'] = $this->a->__( 'Newsletter has been successfully deleted' );

		} catch ( \Advertikon\Exception $e ) {
			$ret['error'] = $e->getMessage();
		}

		$this->response->setOutput( json_encode( $ret ) );
	}

	/**
	 * Newsletter controls action
	 * @return void
	 */
	public function newsletter_controls() {
		$ret = '';

		try {
			if ( ! isset( $this->request->request['id'] ) ) {
				throw new \Advertikon\Exception( 'Newsletter id missing' );
			}

			$id = $this->request->request['id'];

			$newsletter = $this->a->q( array(
				'table' => $this->a->newsletter_list_table,
				'query' => 'select',
				'where' => array(
					'field'     => 'id',
					'operation' => '=',
					'value'     => $id,
				),
			) );

			if ( ! $newsletter ) {
				throw new \Advertikon\Exception( sprintf( 'Newsletter with ID "%s" is missing', $id ) );
			}

			$active_widget = -1;
			$a['active'] = &$active_widget;
			$widgets_list = $this->model->get_avail_widgets( $id , $a );

			$template_hook_common = 'newsletter.' . $newsletter['id'];
			$template_hook_sbscribe = 'newsletter.subscribe.' . $newsletter['id'];
			$template_hook_unsbscribe = 'newsletter.unsubscribe.' . $newsletter['id'];

			$template_name_common = 'Newsletter - ' . $newsletter['name'];
			$template_name_subscribe = 'Newsletter - Successful subscription to ' . $newsletter['name'];
			$template_name_unsubscribe = 'Newsletter - Successful unsubscription from ' . $newsletter['name'];

			$template_common = null;
			$template_subscribe = null;
			$template_unsubscribe = null;

			$sample_common = null;
			$sample_subscribe = null;
			$sample_unsubscribe = null;

			$templates = $this->a->q( array(
				'table' => $this->a->templates_table,
				'query' => 'select',
				'where' => array(
					'operation' => 'in',
					'field'     => 'hook',
					'value'     => array(
						$template_hook_common,
						$template_hook_sbscribe,
						$template_hook_unsbscribe,
						'newsletter',
						'newsletter.subscribe',
						'newsletter.unsubscribe',
					),
				),
			) );

			foreach( $templates as $t ) {
				if ( $t['hook'] === $template_hook_common ) {
					$template_common = $t;

				} elseif ( $t['hook'] === $template_hook_sbscribe ) {
					$template_subscribe = $t;

				} elseif ( $t['hook'] === $template_hook_unsbscribe ) {
					$template_unsubscribe = $t;

				} elseif ( 'newsletter' === $t['hook'] ) {
					$sample_common = $t['template_id'];

				} elseif ( 'newsletter.subscribe' === $t['hook'] ) {
					$sample_subscribe = $t['template_id'];

				} else {
					$sample_unsubscribe = $t['template_id' ];
				}
			}

			$subsciber_status = array();
			foreach( array( 0, 1, 2 ,3, 4 ) as $s_id ) {
				$subscriber_status[ $s_id ] = $this->a->get_status_name( $s_id ); 
			}

			$subscriber_status[ 100 ] = $this->a->__( 'All' );

			$newsletters_list = $this->a->get_newsletter_for_select();
			foreach( $newsletters_list as $l_id => $l_name ) {
				if ( $id == $l_id ) {
					unset( $newsletters_list[ $l_id ] );
				}
			}

			$sql = "SELECT * FROM `" . DB_PREFIX . "customer_group` `cg`
					LEFT JOIN `" . DB_PREFIX . "customer_group_description` `cgd`
						ON (`cg`.`customer_group_id` = `cgd`.`customer_group_id`)
					WHERE `cgd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "'";

			$query = $this->db->query($sql);

			foreach( $query->rows as $g ) {
				$customer_groups[ $g['customer_group_id' ] ] = $g['name' ];
			}

			$ret .= 
'<div class="row" >' .
	'<div class="col-sm-6 newsletter-controls-wrapper">' .

		// Newsletter name
		$this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Name' ),
			'label_for' => '',
			'cols'      => array( 'col-sm-12', 'col-sm-12' ),
			'element'   => $this->a->r( array(
				'type'         => 'text',
				'value'        => $newsletter['name'],
				'class'        => 'form-control newsletter-control',
				'custom_data'  => 'data-name="name"',
			) ),
			'description' => $this->a->__( 'Newsletter\'s name' ),
		) ) .

		// Newsletter description
		$this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Description' ),
			'label_for' => '',
			'cols'      => array( 'col-sm-12', 'col-sm-12' ),
			'element'   => $this->a->r( array(
				'type'         => 'text',
				'value'        => $newsletter['description'],
				'class'        => 'form-control newsletter-control',
				'custom_data'  => 'data-name="description"',
			) ),
			'description' => $this->a->__( 'Newsletter\'s description' ),
		) ) .

		// Widget
		$this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Widget' ),
			'label_for' => '',
			'cols'      => array( 'col-sm-12', 'col-sm-12' ),
			'element'   => $this->a->r( array(
				'type'          => 'select',
				'value'         => $widgets_list,
				'active'        => $active_widget,
				'class'         => 'form-control newsletter-control',
				'custom_data'   => 'data-name="widget"',
			) ),
			'description' => $this->a->__( 'Newsletter\'s widget' ),
		) ) .

		// Status
		$this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Status' ),
			'label_for' => '',
			'cols'      => array( 'col-sm-12', 'col-sm-12' ),
			'element'   => $this->a->r()->render_fancy_checkbox( array(
				'value'        => $newsletter['status'],
				'class'        => 'newsletter-control',
				'custom_data'  => 'data-name="status"',
			) ),
			'description' => $this->a->__( 'Newsletter\'s status' ),
		) ) .

		// Double opt-in option
		$this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Double opt-in' ),
			'label_for' => '',
			'cols'      => array( 'col-sm-12', 'col-sm-12' ),
			'element'   => $this->a->r()->render_fancy_checkbox( array(
				'value'        => $newsletter['double_opt_in'],
				'class'        => 'newsletter-control',
				'custom_data'  => 'data-name="double_opt_in"',
			) ),
			'description' => $this->a->__( 'Double opt-in setting means that subscriber needs to confirm its email address by following a link in confirmation email' ),
		) ) .

		// Notify on subscription
		$this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Notify on subscription' ),
			'label_for' => '',
			'cols'      => array( 'col-sm-12', 'col-sm-12' ),
			'element'   => $this->a->r()->render_fancy_checkbox( array(
				'value'        => $newsletter['notify_subscription'],
				'class'        => 'newsletter-control',
				'custom_data'  => 'data-name="notify_subscription"',
			) ),
			'description' => $this->a->__( 'Send notification email to subscriber on successful subscription to a newsletter' ),
		) ) .

		// Notify on cancellation
		$this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Notify on unsubscription' ),
			'label_for' => '',
			'cols'      => array( 'col-sm-12', 'col-sm-12' ),
			'element'   => $this->a->r()->render_fancy_checkbox( array(
				'value'        => $newsletter['notify_unsubscription'],
				'class'        => 'newsletter-control',
				'custom_data'  => 'data-name="notify_unsubscription"',
			) ),
			'description' => $this->a->__( 'Send notification email to subscriber on successful cancellation of a newsletter' ),
		) ) .

		// Create template for the newsletter
		$this->a->r()->render_form_group( array(
			'label' => $this->a->__( 'Create template' ),
			'element' => $this->a->r( array(
				'type'        => 'button',
				'text_before' => $this->a->__( 'Add template' ),
				'custom_data' => ( $template_common ? 'disabled="disabled"' : '' ) .
					'data-name="' . $template_name_common . '" ' .
					'data-hook="' . $template_hook_common . '"' .
					'data-sample="' . $sample_common . '"',
				'title'       => $this->a->__( 'Template for the newsletter' ),
				'class'       => 'add-newsletter-template', 
				'icon'        => 'fa-plus-circle', 
				'button_type' => 'primary',
			) ),
			'description' => $this->a->__( 'Create separate template for the newsletter. If separate template is missing "Newsletter" template will be used' ),
		) ) .

		// Create confirmation template
		$this->a->r()->render_form_group( array(
			'label' => $this->a->__( 'Create confirmation template' ),
			'element' => $this->a->r( array(
				'type'        => 'button',
				'text_before' => $this->a->__( 'Add template' ),
				'custom_data' => ( $template_subscribe ? 'disabled="disabled"' : '' ) .
					'data-name="' . $template_name_subscribe . '" ' .
					'data-hook="' . $template_hook_sbscribe . '"' .
					'data-sample="' . $sample_subscribe . '"',
				'title'       => $this->a->__( 'Add confirmation template' ),
				'class'       => 'add-newsletter-template', 
				'icon'        => 'fa-plus-circle', 
				'button_type' => 'primary',
			) ),
			'description' => $this->a->__( 'Create separate template to notify a subscriber about successful subscription. If separate template is missing "Newsletter - Successful subscription" template will be used' ),
		) ) .

		// Create confirmation template
		$this->a->r()->render_form_group( array(
			'label' => $this->a->__( 'Create confirmation template' ),
			'element' => $this->a->r( array(
				'type'        => 'button',
				'text_before' => $this->a->__( 'Add template' ),
				'custom_data' => ( $template_unsubscribe ? 'disabled="disabled"' : '' ) .
					'data-name="' . $template_name_unsubscribe . '" ' .
					'data-hook="' . $template_hook_unsbscribe . '"' .
					'data-sample="' . $sample_unsubscribe . '"',
				'title'       => $this->a->__( 'Add confirmation template' ),
				'class'       => 'add-newsletter-template', 
				'icon'        => 'fa-plus-circle', 
				'button_type' => 'primary',
			) ),
			'description' => $this->a->__( 'Create separate template to notify a subscriber about successful subscription cancellation. If separate template is missing "Newsletter - Successful unsubscription" template will be used' ),
		) ) .

		// Save button
		$this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Save' ),
			'label_for' => '',
			'cols'      => array( 'col-sm-12', 'col-sm-12' ),
			'element'   => $this->a->r( array(
				'type'        => 'button',
				'id'          => 'update-newsletter',
				'icon'        => 'fa-save',
				'text_after'  => $this->a->__( 'Save' ),
				'button_type' => 'success',
			) ),
			'description' => $this->a->__( 'Save changes' ),
		) ) .

	'</div>' .
	'<div class="col-sm-6" id="newsletter-stat">' .
		'<div class="chart-wrapper">' .
			'<h3>' . $this->a->__( 'Mailing summary' ) . '</h3>' .
			'<div class="col-sm-12"><canvas id="newsletter-summary-email" width="100%"></canvas></div>' .
		'</div>' .
		'<div class="chart-wrapper">' .
			'<h3>' . $this->a->__( 'Subscription summary' ) . '</h3>' .
			'<div class="col-sm-12"><canvas id="newsletter-summary-subscription" width="100%"></canvas></div>' .
		'</div>' .
	'</div>' .
'</div>' .

'<div class="row table-overall">' .
	'<h3>' . $this->a->__( 'Newsletter\'s statistic' ) . '</h3>' .
	'<div class="col-sm-4">' .
	$this->a->r( array(
			'type'        => 'select',
			'id'          => 'newsletter-chart-period',
			'class'       => 'form-control table-filter table-filter-date',
			'custom_data' => 'data-type="date_added"',
			'value'       => array(
				'select'    => '',
				'day'       => $this->a->__( 'Last day' ),
				'week'      => $this->a->__( 'Last week' ), 
				'two_weeks' => $this->a->__( 'Last 14 days' ),
				'month'     => $this->a->__( 'Last month'  ),
				'custom'    => $this->a->__( 'Custom' ),
			),
		) ) .
	'</div>' .
	'<div class="col-sm-2">' .
	$this->a->r( array(
		'type'        => 'button',
		'icon'        => 'fa-save',
		'button_type' => 'success',
		'text_before' => $this->a->__( 'Apply' ),
		'id'          => 'newsletter-chart-reload',
		'custom_data' => 'data-url="' . $this->url->link(
			$this->a->type . '/' . $this->a->code . '/chart',
			'token=' . $this->session->data['token'],
			'SSL'
		) . '"',
	) ) .
	'</div>' .
	'<div class="col-sm-12"><canvas id="newsletter-chart" width="50%"></canvas></div>' .
'</div>' .

'<div class="row">' .
	'<h3>' . $this->a->__( 'Add subscriber' ) . '</h3>' .
	'<div class="col-sm-5">' .

		$this->a->r( array(
			'type' => 'inputgroup',
			'element' => array(
				'type'        => 'text',
				'class'       => 'form-control',
				'id'          => 'add-subscriber-name',
				'placeholder' => $this->a->__( 'Name' ),
			),
			'addon_after' => '<i class="fa fa-user"></i>',
		) ) .

	'</div>' .
	'<div class="col-sm-5">' .

		$this->a->r( array(
			'type' => 'inputgroup',
			'element' => array(
				'type'        => 'text',
				'class'       => 'form-control',
				'id'          => 'add-subscriber-email',
				'placeholder' => $this->a->__( 'E-mail' ),
			),
			'addon_after' => '<i class="fa fa-at"></i>',
		) ) .

	'</div>' .
	'<div class="col-sm-2" style="text-align: right;">' .

		$this->a->r( array(
			'type'        => 'button',
			'id'          => 'add-subscriber',
			'icon'        => 'fa-check',
			'text_after'  => $this->a->__( 'Subscribe' ),
			'button_type' => 'success',
			'custom_data' => 'data-url="' . $this->url->link(
				$this->a->type . '/' . $this->a->code . '/subscribe',
				'token=' . $this->session->data['token'],
				'SSL'
			) . '"',
		) ) .

	'</div>' .
'</div>' .

'<div class="row">' .
	'<h3>' . $this->a->__( 'Import subscribers from source' ) . '</h3>' .
	'<div class="col-sm-4">' .
		'<label>' .
		$this->a->__( 'Source newsletter' ) .
		'</label>' .
		$this->a->r( array(
			'type'  => 'select',
			'class' => 'form-control',
			'id'    => 'import-subscribers-source',
			'value' => array_replace(
				array( '-1' => $this->a->__( 'Select existing newsletter' ) ),
				$newsletters_list
			)
		) ) .
	'</div>' .
	'<div class="col-sm-4">' .
		'<label>' .
		$this->a->__( 'Subscribers filter' ) .
		'</label>' .
		$this->a->r( array(
			'type'        => 'select',
			'class'       => 'form-control select2',
			'custom_data' => 'multiple="multiple"',
			'value'       => $subscriber_status,
			'active'      => 100,
			'id'          => 'import-subscribers-subscribers'
		) ) .
	'</div>' .
	'<div class="col-sm-2" style="text-align: right;">' .
		'<label>' .
		$this->a->__( 'Override' ) . ' ' . $this->a->r()->render_popover( $this->a->__( 'Override existing records' ) ) .
		'</label>' .
		$this->a->r()->render_fancy_checkbox( array(
			'value' => 0,
			'id'    => 'import-subscribers-override',
		) ) .
	'</div>' .
	'&nbsp' .
	'<div class="col-sm-2" style="text-align: right;">' .
		$this->a->r( array(
			'type'        => 'button',
			'text_before' => $this->a->__( 'Import' ),
			'icon'        => 'fa-exchange',
			'button_type' => 'primary',
			'custom_data' => 'data-url="' .
				$this->url->link(
					$this->a->type . '/' . $this->a->code . '/import_subscribers',
					'token=' . $this->session->data['token'],
					'SSL'
				) . '"' .
				' data-target="' . $id . '"',
			'id'          => 'import-subscribers',
		) ) .
	'</div>' .
'</div>' .

'<div class="row" id="import-oc">' .
	'<h3>' . $this->a->__( 'Import OpenCart\'s subscribers' ) . '</h3>' .
	'<div class="col-md-3">' .
		'<label>' .
		$this->a->__( 'Subscribers filter' ) .
		'</label>' .
		$this->a->r( array(
			'type'        => 'select',
			'class'       => 'form-control select2 filter-oc',
			'custom_data' => 'multiple="multiple"',
			'value'       => array(
				'-1'             => $this->a->__( 'All' ),
				'newsletter'     => $this->a->__( 'Newsletter subscribers' ),
				'customer_all'   => $this->a->__( 'All the customers' ),
				'customer_group' => $this->a->__( 'Specific customer\'s group' ),
				'customer'       => $this->a->__( 'Specific customer' ),
				'affiliate_all'  => $this->a->__( 'All the affiliates' ),
				'affiliate'      => $this->a->__( 'Specific affiliate' ),
				'product'        => $this->a->__( 'Specific product\'s purchasers' ),
			),
			'active'      => -1,
			'id'          => 'import-oc-subscribers-select',
			'name'        => 'subscriptions[]'
		) ) .
	'</div>' .
	'<div class="col-md-3">' .
		'<div class="filter-oc-wrapper wrapper-customer">' .
			'<b>' . $this->a->__( 'Select customer' ) . '</b>' .
			$this->a->r( array(
				'type'        => 'text',
				'class'       => 'form-control',
				'id'          => 'filter-oc-customers',
				'custom_data' => 'autocomplete="off" data-url="' .
					$this->url->link( 'customer/customer/autocomplete', 'token=' . $this->session->data['token'], 'SSL' ) .
				'"',
			) ) .
		'</div>' .
		'<div class="filter-oc-wrapper wrapper-affiliate">' .
			'<b>' . $this->a->__( 'Select affiliate' ) . '</b>' .
			$this->a->r( array(
				'type'        => 'text',
				'class'       => 'form-control',
				'id'          => 'filter-oc-affiliates',
				'custom_data' => 'autocomplete="off" data-url="' .
					$this->url->link( $this->a->type . '/' . $this->a->code . '/affiliate_autocomplete', 'token=' . $this->session->data['token'], 'SSL' ) .
				'"',
			) ) .
		'</div>' .
		'<div class="filter-oc-wrapper wrapper-product">' .
			'<b>' . $this->a->__( 'Select product' ) . '</b>' .
			$this->a->r( array(
				'type'        => 'text',
				'class'       => 'form-control',
				'id'          => 'filter-oc-products',
				'custom_data' => 'autocomplete="off" data-url="' .
					$this->url->link( 'catalog/product/autocomplete', 'token=' . $this->session->data['token'], 'SSL' ) .
				'"',
			) ) .
		'</div>' .
		'<div class="filter-oc-wrapper wrapper-customer_group">' .
			'<b>' . $this->a->__( 'Select customer\'s group' ) . '</b>' .
			$this->a->r( array(
				'type'        => 'select',
				'class'       => 'form-control select2',
				'id'          => 'filter-oc-customer-groups',
				'value'       => $customer_groups,
				'custom_data' => 'multiple="multiple"',
				'name'        => 'customer_group[]'
			) ) .
		'</div>' .
	'</div>' .
	'<div class="col-md-3">' .
		'<div id="filter-oc-aux" class="well well-sm" style="margin-bottom: 0; min-height: 55px;"></div>' .
	'</div>' .
	'<div class="col-md-1" style="text-align: right;">' .
		'<label>' .
		$this->a->__( 'Override' ) . ' ' . $this->a->r()->render_popover( $this->a->__( 'Override existing records' ) ) .
		'</label>' .
		$this->a->r()->render_fancy_checkbox( array(
			'value' => 0,
			'id'    => 'import-subscribers-override',
			'name'  => 'override',
		) ) .
	'</div>' .
	'&nbsp' .
	'<div class="col-md-2" style="text-align: right;">' .
		$this->a->r( array(
			'type'        => 'button',
			'text_before' => $this->a->__( 'Import' ),
			'icon'        => 'fa-exchange',
			'button_type' => 'primary',
			'custom_data' => 'data-url="' .
				$this->url->link(
					$this->a->type . '/' . $this->a->code . '/import_oc_subscribers',
					'token=' . $this->session->data['token'],
					'SSL'
				) . '"' .
				' data-target="' . $id . '"',
			'id'          => 'import-oc-subscribers',
		) ) .
	'</div>' .
'</div>' .

'<div class="row">' .
	'<h3>' . $this->a->__( 'Import subscribers from file' ) . '</h3>' .
	'<div class="col-sm-8">' .
		'<label>' .
		$this->a->__( 'Columns' ) . ' ' .
		$this->a->r()->render_popover(
			$this->a->__(
				'To make import as precise as possible please specify column number (starting from 1) for email and name of a subscriber'
			)
		) .
		'</label>' .
		'<div class="table-responsive">' .
			'<table class="table">' .
				'<tr>' .
				'<td>' . $this->a->__( 'Email' )      . '#<input class="import-column" id="import-csv-email">'     . '</td>' .
				'<td>' . $this->a->__( 'Full name' )  . '#<input class="import-column" id="import-csv-fullname">'  . '</td>' .
				'<td>' . $this->a->__( 'First name' ) . '#<input class="import-column" id="import-csv-firstname">' . '</td>' .
				'<td>' . $this->a->__( 'Last name' )  . '#<input class="import-column" id="import-csv-lastname">'  . '</td>' .
				'</tr>' .
			'</table>' .
		'</div>' .
	'</div>' .
	'<div class="col-sm-2" style="text-align: right;">' .
		'<label>' .
		$this->a->__( 'Override' ) . ' ' . $this->a->r()->render_popover( $this->a->__( 'Override existing records' ) ) .
		'</label>' .
		$this->a->r()->render_fancy_checkbox( array(
			'value' => 0,
			'id'    => 'import-csv-override',
		) ) .
	'</div>' .
	'<div class="col-sm-2" style="text-align: right;">' .
		'<label>' .
		$this->a->__( 'Select source file' ) .
		'</label>' .
		 '<span class="btn btn-success fileinput-button">' .
			'<i class="glyphicon glyphicon-plus"></i>' .
			'<span>Add files...</span>' .
			'<input id="fileupload" type="file" name="files[]" multiple>' .
		'</span>' .
	'</div>' .
'</div>' .

'<div class="row">' .
	'<h3>' . $this->a->__( 'Export subscribers' ) . '</h3>' .
	'<div class="col-sm-5">' .
		'<label>' .
		$this->a->__( 'Subscribers filter' ) .
		'</label>' .
		$this->a->r( array(
			'type'        => 'select',
			'class'       => 'form-control select2',
			'custom_data' => 'multiple="multiple"',
			'value'       => $subscriber_status,
			'id'          => 'export-subscribers-filter',
			'active'      => 100,
		) ) .
	'</div>' .
	'<div class="col-sm-5">' .
		'<label>' . $this->a->__( 'Fields to export' ) . '</label>' .
		'<div class="col-sm-12 csv-item-wrapper">' .
			'<label class="csv-item col-sm-6" style="width: 50%;">' .
				'<input type="checkbox" id="csv-name" data-id="name" checked="checked"><span>Name</span>' .
			'</label>' .
			'<label class="csv-item col-sm-6" style="width: 50%;">' .
				'<input type="checkbox" id="csv-email" data-id="email" checked="checked"><span>Email</span>' .
			'</label>' .
		'</div>' .
	'</div>' .
	'&nbsp' .
	'<div class="col-sm-2" style="text-align: right;">' .
		'<a class="btn btn-primary" id="export-subscribers-link" href="'	.
				$this->url->link(
					$this->a->type . '/' . $this->a->code . '/export_subscribers',
					'token=' . $this->session->data['token'] . '&id=' . $id . '&filter=100&sort=name,email',
					'SSL'
				)
				. '" role="button">' . $this->a->__( 'Export' ) . '&nbsp;<i class="fa fa-arrow-down"></i>' .
		'</a>' .
	'</div>' .
'</div>' .

'<h3>' . $this->a->__( 'List of subscribers' ) . '</h3>' .
'<div id="subscribers-list" class="table-overall">' . 
	'<div id="subscribers-list-filter" class="table-filter-wrapper">' . $this->render_subscribers_filter( $id ) . '</div>' .
	'<div id="subscribers-list-contents" data-url="' . 
	$this->url->link(
		$this->a->type . '/' . $this->a->code . '/fetch_subscribers',
		'token=' . $this->session->data['token'] . '&id=' . $id,
		'SSL'
	) .
	'">' .
	$this->render_subscribers(
		array(
			'where' => array(
				'field'     => 'newsletter',
				'operation' => '=',
				'value'     => $id,
			) 
		)
	) .
	'</div>' .
'</div>';
			
		} catch ( \Advertikon\Exception $e ) {
			trigger_error( $e->getMessage() );
		}

		$this->response->setOutput( $ret );
	}

	/**
	 * Update newsletter action
	 * @return void
	 */
	public function update_newsletter() {
		$ret = array();

		try {

			if ( ! isset( $this->request->request['id'] ) ) {
				$this->exception( $this->a->__( 'Newsletter ID is missing' ) );
			}

			$id = $this->a->request( 'id' );
			$widget_id = $this->a->request( 'widget' );

			$result = $this->a->q( array(
				'table' => $this->a->newsletter_list_table,
				'query' => 'update',
				'where' => array(
					'field'     => 'id',
					'operation' => '=',
					'value'     => $id,
				),
				'set' => array(
					'name'                  => $this->a->request( 'name' ),
					'description'           => $this->a->request( 'description' ),
					'status'                => $this->a->request( 'status' ),
					'widget'                => $widget_id,
					'double_opt_in'         => $this->a->request( 'double_opt_in' ),
					'notify_subscription'   => $this->a->request( 'notify_subscription' ),
					'notify_unsubscription' => $this->a->request( 'notify_unsubscription' ),
				),
			) );

			if ( ! $result ) {
				throw new \Advertikon\Exception( $this->a->__( 'Fail to update newsletter' ) );
			}

			// Prevent mapping to non-existing widget (id = -1)
			if ( $widget_id > 0 ) {
				$this->a->q( array(
					'table' => $this->a->newsletter_to_widget_table,
					'query' => 'delete',
					'where' => array(
						'field'     => 'newsletter_id',
						'operation' => '=',
						'value'     => $id,
					),
				) );

				$widget_map = $this->a->q( array(
					'table' => $this->a->newsletter_to_widget_table,
					'query' => 'insert',
					'values' => array(
						'widget_id'     => $widget_id,
						'newsletter_id' => $id
					),
				) );

				if ( ! $widget_map ) {
					trigger_error( sprintf( 'Failed to update widget mapping for newsletter #"%s"', $id ) );
				}
			}

			$ret['success'] = $this->a->__( 'Newsletter has been successfully updated' );

		} catch ( \Advertikon\Exception $e ) {
			$ret['error'] = $e->getMessage();
		}

		$this->response->setOutput( json_encode( $ret ) );
	}

	/**
	 * Privileged subscribe action
	 * @return void
	 */
	public function subscribe() {
		$ret = array();

		try {

			$name = $this->a->request( 'name' );

			if ( ! $this->a->request( 'email' ) ) {
				throw new \Advertikon\Exception( $this->a->__( 'Subscriber\'s e-mail missing' ) );
			}

			$email = $this->a->request( 'email' );

			if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
				throw new \Advertikon\Exception(
					$this->a->__( sprintf( 'E-mail address "%s" is invalid', $email ) )
				);
			}

			if ( ! $this->a->request( 'newsletter' ) ) {
				throw new \Advertikon\Exception( $this->a->__( 'Subscriber\'s newsletter missing' ) );
			}

			$newsletter = $this->a->request( 'newsletter' );

			if ( $this->a->check_subscriber( $email, $newsletter ) ) {
				throw new \Advertikon\Exception(
					$this->a->__( 'E-mail "%s" is already subscribed to the newsletter', $email )
				);
			}

			$result = $this->a->q( array(
				'table'  => $this->a->newsletter_subscribers_table,
				'query'  => 'insert',
				'values' => array(
					'name'       => $name,
					'email'      => $email,
					'status'     => 1,
					'newsletter' => $newsletter,
					'date_added' => date( 'c' ),
				),
			) );

			if ( ! $result ) {
				throw new \Advertikon\Exception( $this->a->__( 'Fail to add subscription' ) );
			}

			$ret['success'] = $this->a->__( 'Subscription has been created successfully' );
 
		} catch ( \Advertikon\Exception $e ) {
			$ret['error'] = $e->getMessage();
		}

		$this->response->setOutput( json_encode( $ret ) );
	}

	/**
	 * Renders history management tab
	 * @param int $id Subscription ID
	 * @return string
	 */
	public function render_subscribers_filter( $id ) {
		$ret = '';

		// Management row
		$ret .= '<div class="row">';
		$ret .= '<div class="pull-right filter-controls">';

		// Apply filter button
		$ret .= $this->a->r( array(
			'type'        => 'button',
			'button_type' => 'success',
			'id'          => 'apply-subscribers-filter',
			'class'       => 'apply-table-filter',
			'icon'        => 'fa-filter',
			'text_before' => $this->a->__( 'Apply' ),
		) );

		// Reset filter button
		$ret .= $this->a->r( array(
			'type'        => 'button',
			'button_type' => 'warning',
			'id'          => 'reset-subscribers-filter',
			'class'       => 'clear-table-filter',
			'icon'        => 'fa-eraser',
			'text_before' => $this->a->__( 'Reset' ),
		) );

		// Refresh list button
		$ret .= $this->a->r( array(
			'type'        => 'button',
			'button_type' => 'primary',
			'id'          => 'refresh-subscribers',
			'icon'        => 'fa-refresh',
			'text_before' => $this->a->__( 'Refresh' ),
		) );

		// Clear history button
		$ret .= $this->a->r( array(
			'type'        => 'button',
			'button_type' => 'danger',
			'id'          => 'delete-subscriber',
			'icon'        => 'fa-close',
			'text_before' => $this->a->__( 'Delete subscriber' ),
			'custom_data' => 'data-url="' . $this->url->link(
				$this->a->type . '/' . $this->a->code . '/delete_subscriber',
				'token=' . $this->session->data['token'],
				'SSL'
			) . '"',
		) );
		$ret .= '</div>';
		$ret .= '</div>'; // Row end

		// First row
		$ret .= '<div class="row">';

		$ret .= '<div class="col-sm-4"><b>' . $this->a->__( 'Name' ) . '</b>';
		$ret .= $this->a->r( array(
			'type'        => 'select',
			'class'       => 'table-filter-autofill table-filter',
			'custom_data' => 'data-type="name" multiple="multiple" data-custom="' . $id . '"',
		) );
		$ret .= '</div>';

		$ret .= '<div class="col-sm-4"><b>' . $this->a->__( 'Status' ) . '</b>';
		$ret .= $this->a->r( array(
			'type'        => 'select',
			'class'       => 'table-filter newsletter-status-select select2',
			'custom_data' => 'data-type="status" multiple="multiple"',
			'value'       => $this->model->get_newsletter_statuses_for_select( $this->a->newsletter_subscribers_table ),
		) );
		$ret .= '</div>';

		$ret .= '<div class="col-sm-4"><b>' . $this->a->__( 'Date' ) . '</b>';
		$ret .= $this->a->r( array(
			'type'        => 'select',
			'id'          => 'history-filter-date',
			'class'       => 'form-control table-filter table-filter-date',
			'custom_data' => 'data-type="date_added"',
			'value'       => array(
				'select'    => '',
				'day'       => $this->a->__( 'Last day' ),
				'week'      => $this->a->__( 'Last week' ), 
				'two_weeks' => $this->a->__( 'Last 14 days' ),
				'month'     => $this->a->__( 'Last month'  ),
				'custom'    => $this->a->__( 'Custom' ),
			),
		) );
		$ret .= '</div>';

		$ret .= '</div>'; // Row end

		$ret .= '<div class="row">'; // New row

		$hide_fields = explode( ',', $this->a->config( 'subscriber_fields_hide', '' ) );

		$ret .= '<div class="col-sm-4"><b>' . $this->a->__( 'Visible fields' ) . '</b>';
		$ret .= $this->a->r( array(
			'type'        => 'buttongroup',
			'class'       => '',
			'custom_data' => 'data-name="subscriber_fields_hide"',
			'css'         => 'display: block;',
			'buttons'     => array(
				array(
					'type'        => 'button',
					'icon'        => 'fa-tags',
					'title'       => $this->a->__( 'Name' ),
					'custom_data' => 'data-value="name"',
					'class'       => 'table-show-field',
					'button_type' => in_array( 'name', $hide_fields ) ? 'void' : 'default',
				),
				array(
					'type'        => 'button',
					'icon'        => 'fa-at',
					'title'       => $this->a->__( 'Email' ),
					'custom_data' => 'data-value="email"',
					'class'       => 'table-show-field',
					'button_type' => in_array( 'email', $hide_fields ) ? 'void' : 'default',
				),
				array(
					'type'        => 'button',
					'icon'        => 'fa-info-circle',
					'title'       => $this->a->__( 'Status' ),
					'custom_data' => 'data-value="status"',
					'class'       => 'table-show-field',
					'button_type' => in_array( 'status', $hide_fields ) ? 'void' : 'default',
				),
				array(
					'type'        => 'button',
					'icon'        => 'fa-calendar',
					'title'       => $this->a->__( 'Date added' ),
					'custom_data' => 'data-value="date_added"',
					'class'       => 'table-show-field',
					'button_type' => in_array( 'date_added', $hide_fields ) ? 'void' : 'default',
				),
				array(
					'type'        => 'button',
					'icon'        => 'fa-feed',
					'title'       => $this->a->__( 'Subscribe' ),
					'custom_data' => 'data-value="subscribe"',
					'class'       => 'table-show-field',
					'button_type' => in_array( 'subscribe', $hide_fields ) ? 'void' : 'default',
				),
				array(
					'type'        => 'button',
					'icon'        => 'fa-ban',
					'title'       => $this->a->__( 'Unsubscribe' ),
					'custom_data' => 'data-value="unsubscribe"',
					'class'       => 'table-show-field',
					'button_type' => in_array( 'unsubscribe', $hide_fields ) ? 'void' : 'default',
				),
				array(
					'type'        => 'button',
					'icon'        => 'fa-close',
					'title'       => $this->a->__( 'Delete' ),
					'custom_data' => 'data-value="delete"',
					'class'       => 'table-show-field',
					'button_type' => in_array( 'delete', $hide_fields ) ? 'void' : 'default',
				),
			),
		) );
		$ret .= '</div>';
		$ret .= '</div>'; // Row end

		return $ret;
	}

	/**
	 * Renders subscribers table contents
	 * @param array $data Filter array
	 * @return string
	 */
	public function render_subscribers( $data = array() ) {
		$data = array_merge( $data, array(
			'table' => $this->a->newsletter_subscribers_table,
			'query' => 'select',
		) );

		$total_data = $data;
		$total_data['field'] = array( 'count' => 'count(*)' );
		$total_query = $this->a->q( $total_data );
		$total = $total_query['count'];
		$page = 1;

		if ( empty( $data['limit'] ) ) {
			$data['limit'] = 20;
		}

		if ( isset( $data['page'] ) ) {
			if ( 'last' === $data['page'] ) {
				$page = ceil( $total / $data['limit'] );

			} else {
				$page = (int)$data['page'];
				$page = $page <= 0 ? 1 : $page;
			}
		}

		$data['start'] = ( $page - 1 ) * $data['limit'];

		$columns = array(
			'id'          => '',
			'name'        => $this->a->__( 'Name' ),
			'email'       => $this->a->__( 'E-mail' ),
			'status'      => $this->a->__( 'Status' ),
			'date_added'  => $this->a->__( 'Date' ),
			'subscribe'   => $this->a->__( 'Subscribe' ),
			'unsubscribe' => $this->a->__( 'Unsubscribe' ),
			'delete'      => $this->a->__( 'Delete' ),
		);

		$ret =
'<div class="table-responsive">' .
'<table class="table table-bordered table-hover adk-table subscribers-table" data-url="' .
	$this->url->link( 
		$this->a->type . '/' . $this->a->code . '/fetch_subscribers',
		'token=' . $this->session->data['token'],
		'SSL'
	) .
'" data-type="subscribers">' .
	'<colgroup>';

		$hide_fields = explode( ',', $this->a->config( 'subscriber_fields_hide', '' ) );

		foreach( $columns as $column => $name ) {
			if ( ! in_array( $column, $hide_fields ) ) {
				$ret .=
			'<col id="col-' . $column . '"' . ( $this->is_filterd_by( $data, $column ) ?
				' class="history-highlight-col"' : '' ) . '>';

			}
		}

		$ret .=
	'</colgroup>' .
	'<thead>' .
		'<tr>';

		foreach( $columns as $column => $name ) {
			if ( ! in_array( $column, $hide_fields ) ) {
				$ret .=
				'<th data-type="' . $column . '">' . $this->get_order_icon( $data, $column ) . '&nbsp;' . $name . '</th>';
			}
		}

		$ret .=
		'</tr>' .
	'</thead>' .
	'<tbody>';

		foreach( $this->model->get_subscribers( $data ) as $record ) {
			$ret .=
		'<tr data-id="' . $record['id'] . '">' .
			'<td>' . '<input type="checkbox" class="newsletter-select">'                   . '</td>' .
			( in_array( 'name', $hide_fields ) ? '' : '<td>' . $record['name'] . '</td>' ) .
			( in_array( 'email', $hide_fields ) ? '' : '<td>' . $record['email'] . '</td>' ) .
			( in_array( 'status', $hide_fields ) ? '' : '<td align="center">' . $this->get_status_name( $record['status'] ) . '</td>' ) .
			( in_array( 'date_added', $hide_fields ) ? '' : '<td>' . $record['date_added'] . '</td>' ) .
			( in_array( 'subscribe', $hide_fields ) ? '' : '<td align="center">' . $this->render_subscriber_subscribe_button( $record ) . '</td>' ) .
			( in_array( 'unsubscribe', $hide_fields ) ? '' : '<td align="center">' . $this->render_subscriber_unsubscribe_button( $record ) . '</td>' ) .
			( in_array( 'delete', $hide_fields ) ? '' : '<td align="center">' . $this->render_subscriber_delete_button( $record ) . '</td>' ) .
		'</tr>';
		} 

		$ret .=
	'</tbody></table></div>';

		$pagination = new Pagination();
		$pagination->total = $total;
		$pagination->page = $page;
		$pagination->limit = $data['limit'];
		$pagination->url = '{page}';

		$ret .= $pagination->render();

		return $ret;
	}

	/**
	 * Renders button to activate subscription
	 * @return string
	 */
	public function render_subscriber_subscribe_button( $record ) {
		return $this->a->r( array(
			'type'        => 'button',
			'icon'        => 'fa-feed',
			'custom_data' => 'data-url="' . $this->url->link(
				$this->a->type . '/' . $this->a->code . '/manage_subscription',
				'token=' . $this->session->data['token'],
				'SSL'
			) . '"' .
			'data-id="' . $record['id'] . '"' .
			'data-action="activate"' .
			( \Advertikon\Mail\Advertikon::SUBSCRIBER_STATUS_ACTIVE == $record['status'] ? ' disabled="disabled"' : '' ),
			'class'       => 'manage-subscription',
			'button_type' => 'success',
		) );
	}

	/**
	 * Renders button to activate subscription
	 * @return string
	 */
	public function render_subscriber_unsubscribe_button( $record ) {
		return $this->a->r( array(
			'type'        => 'button',
			'icon'        => 'fa-ban',
			'custom_data' => 'data-url="' . $this->url->link(
				$this->a->type . '/' . $this->a->code . '/manage_subscription',
				'token=' . $this->session->data['token'],
				'SSL'
			) . '"' .
			'data-id="' . $record['id'] . '"' .
			'data-action="deactivate"' .
			( \Advertikon\Mail\Advertikon::SUBSCRIBER_STATUS_ACTIVE != $record['status'] ? ' disabled="disabled"' : '' ),
			'class'       => 'manage-subscription',
			'button_type' => 'warning',
		) );
	}
	/**
	 * Renders button to activate subscription
	 * @return string
	 */
	public function render_subscriber_delete_button( $record ) {
		return $this->a->r( array(
			'type'        => 'button',
			'icon'        => 'fa-close',
			'custom_data' => 'data-url="' . $this->url->link(
				$this->a->type . '/' . $this->a->code . '/manage_subscription',
				'token=' . $this->session->data['token'],
				'SSL'
			) . '"' .
			'data-id="' . $record['id'] . '"' .
			'data-action="delete"',
			'class'       => 'manage-subscription',
			'button_type' => 'danger',
		) );
	}

	/**
	 * Returns status by its code
	 * @param int $status Status' code
	 * @param bool $pretty Flag to format the output, optional 
	 * @return string
	 */
	public function get_status_name( $status, $pretty = true ) {

		$name = $this->a->get_status_name( $status );

		if ( $pretty ) {
			switch ( $status ) {

			// Initial state
			case \Advertikon\Mail\Advertikon::SUBSCRIBER_STATUS_INACTIVE:
				$icon = 'fa-hourglass-o';
				$style = 'text-muted';
				$title = $this->a->__( 'Not initialized' );
				break;

			// Active
			case \Advertikon\Mail\Advertikon::SUBSCRIBER_STATUS_ACTIVE:
				$icon = 'fa-check';
				$style = 'text-success';
				$title = $this->a->__( 'Active' );
				break;

			// Deactivated by admin
			case \Advertikon\Mail\Advertikon::SUBSCRIBER_STATUS_SUSPENDED:
				$icon = 'fa-ban';
				$style = 'text-warning';
				$title = $this->a->__( 'Suspended' );
				break;

			// Email need to be verified
			case \Advertikon\Mail\Advertikon::SUBSCRIBER_STATUS_VERIFICATION:
				$style = 'text-primary';
				$icon = 'fa-question';
				$title = $this->a->__( 'Email address under verification' );
				break;

			// Canceled by customer
			case \Advertikon\Mail\Advertikon::SUBSCRIBER_STATUS_CANCELLED:
				$style = 'text-warning';
				$icon = 'fa-close';
				$title = $this->a->__( 'Canceled by customer' );
				break;

			// Canceled by customer
			case \Advertikon\Mail\Advertikon::SUBSCRIBER_STATUS_BLACKLISTED:
				$style = 'text-warning';
				$icon = 'fa-thumbs-o-down';
				$title = $this->a->__( 'Blacklisted' );
				break;
			default:
				$style = 'text-muted';
				$icon = 'fa-question';
				$title = $this->a->__( 'Undefined' );
				break;
			}

			$name = sprintf( '<i class="fa %s fa-2x %s" title="%s"></>', $icon, $style, $title );
		}

		return $name;
	}

	/**
	 * Fetch subscribers action
	 * @return void
	 */
	public function fetch_subscribers() {
		$ret = '';

		if ( ! $this->a->request( 'id' ) ) {
			trigger_error( 'Subscription ID is missing' );

		} else {
			$id = $this->a->request( 'id' );

			$where = array(
				'field'     => 'newsletter',
				'operation' => '=',
				'value'     => $id,
			);

			$data = $this->request->request;

			if ( empty( $data['where'] ) || ! is_array( $data['where'] ) ) {
				$data['where'] = $where;

			} elseif ( is_array( current( $data['where'] ) ) ) {
				$data['where'][] = $where;

			} else {
				$data['where'] = array(
					array( $data['where'] ),
					$where,
				);
			}

			$ret = $this->render_subscribers( $data );
		}

		$this->response->setOutput( $ret );
	}

	/**
	 * Deletes subscribers action
	 * @return void
	 */
	public function delete_subscriber() {
		$ret = array();

		try {

			if ( ! $this->a->request( 'ids' ) ) {
				throw new \Advertikon\Exception( $this->a->__( 'Subscriber\'s ID is missing' ) );
			}

			$ids = $this->a->request( 'ids' );

			$result = $this->a->q( array(
				'table' => $this->a->newsletter_subscribers_table,
				'query' => 'delete',
				'where' => array(
					'field'     => 'id',
					'operation' => 'in',
					'value'     => $ids,
				),
			) );

			if ( ! $result ) {
				$this->exception( $this->a->__( 'Fail to delete subscriber' ) );
			}

			$ret['success'] = $this->a->__( 'Subscriber has been deleted successfully' );

		} catch ( \Advertikon\Exception $e ) {
			$ret['error'] = $e->getMessage();
		}

		$this->response->setOutput( json_encode( $ret ) );
	}

	/**
	 * Renders newsletter form builder tab's content
	 * @return string
	 */
	public function render_newsletter_form_builder() {
		$ret = '';
		$d = $this->a->subscribe_widget_defaults;

		$widgets = array( '-1' => $this->a->__( 'Select widget' ) );

		foreach( $this->a->q( array(
			'table' => $this->a->newsletter_widget_table,
			'query' => 'select',
			'fields' => array( 'id', 'name' ),
		) ) as $w ) {
			$widgets[ $w['id'] ] = $w['name']; 
		}

		// A Select widget select
		$ret = $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Select widget' ),
			'element' => $this->a->r( array(
				'type'  => 'select',
				'class' => 'form-control',
				'id'    => 'widget-select',
				'value' => $widgets,
				'custom_data' => 'data-url="' .
					$this->url->link(
						$this->a->type . '/' . $this->a->code . '/fetch_widget',
						'token=' . $this->session->data['token'],
						'SSL'
					) .
				'" ' .
				'data-defaults="' . htmlentities( json_encode( $d, JSON_HEX_QUOT ) ) . '"',
			) ),
		) );

		$ret .= '<div class="col-sm-6 widget-controls">';

		// Widget's name
		$ret .= $this->a->r()->render_form_group( array(
			'label' => $this->a->__( 'Name' ),
			'element' => $this->a->r( array(
				'type'        => 'text',
				'class'       => 'form-control',
				'placeholder' => $this->a->__( 'Widget\'s name' ),
				'custom_data' => 'data-name="name"'
			) ),
		) );

		$ret .= $this->model->render_color_scheme_picker( 'widget-color-scheme' );

		// Widget's size
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Widget\'s size' ),
			'element' => $this->a->r( array(
				'class'       => 'form-control', 
				'type'        => 'dimension',
				'custom_data' => 'data-name="width"',
				'titles'      => $this->a->__( 'Width in pixels' ) . ',' . $this->a->__( 'Width in percentage' ),
				'maxes'       => '1000,100',
				'value'       => (int)$d['width'],
				'id'          => 'widget-width',
 			) ),
		) );

		// Background color
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Background color' ),
			'element' => $this->a->r( array(
				'class'       => 'form-control iris', 
				'type'        => 'color',
				'custom_data' => 'data-name="background_color"',
				'value'       => $d['background_color'],
				'id'          => 'widget-background',
			) ),
		) );

		// Widgets' title
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Title' ),
			'element' => $this->a->r( array(
				'type'        => 'text',
				'class'       => 'form-control',
				'placeholder' => $this->a->__( 'Widget\'s title' ),
				'custom_data' => 'data-name="title"',
				'value'       => $d['title'],
				'id'          => 'widget-title',
			) ),
		) );

		// Widget's box shadow X
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Box shadow X-offset' ),
			'element' => $this->a->r( array(
				'class'       => 'form-control', 
				'type'        => 'dimension',
				'custom_data' => 'data-name="box_shadow_x"',
				'titles'      => $this->a->__( 'Box shadow X-offset in pixels' ),
				'maxes'       => '40',
				'values'      => 'px',
				'id'          => 'widget-box-shadow-x',
				'value'       => (int)$d['box_shadow_x'],
 			) ),
		) );

		// Widget's box shadow Y
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Box shadow Y-offset' ),
			'element' => $this->a->r( array(
				'class'       => 'form-control', 
				'type'        => 'dimension',
				'custom_data' => 'data-name="box_shadow_y"',
				'titles'      => $this->a->__( 'Box shadow Y-offset in pixels' ),
				'maxes'       => '40',
				'values'      => 'px',
				'id'          => 'widget-box-shadow-y',
				'value'       => (int)$d['box_shadow_y'],
 			) ),
		) );

		// Widget's box shadow dispersion
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Box shadow dispersion' ),
			'element' => $this->a->r( array(
				'class'       => 'form-control', 
				'type'        => 'dimension',
				'custom_data' => 'data-name="box_shadow_dispersion"',
				'titles'      => $this->a->__( 'Box shadow dispersion in pixels' ),
				'maxes'       => '40',
				'values'      => 'px',
				'id'          => 'widget-box-shadow-dispersion',
				'value'       => (int)$d['box_shadow_dispersion'],
 			) ),
		) );

		// Widget's border radius
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Border radius' ),
			'element' => $this->a->r( array(
				'class'       => 'form-control', 
				'type'        => 'dimension',
				'custom_data' => 'data-name="border_radius"',
				'titles'      => $this->a->__( 'Border radius in pixels' ),
				'maxes'       => '100',
				'values'      => 'px',
				'id'          => 'widget-border-radius',
				'value'       => (int)$d['border_radius'],
 			) ),
		) );

		// Title's color
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Title\'s color' ),
			'element' => $this->a->r( array(
				'class'       => 'form-control iris', 
				'type'        => 'color',
				'custom_data' => 'data-name="title_color"',
				'value'       => $d['title_color'],
				'id'          => 'widget-title-color',
			) ),
		) );

		// Title's size
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Title\'s size' ),
			'element' => $this->a->r( array(
				'class'       => 'form-control', 
				'type'        => 'dimension',
				'custom_data' => 'data-name="title_height"',
				'titles'      => $this->a->__( 'Height in pixels' ) . ',' . $this->a->__( 'Height as a percentage of font\'s height' ),
				'maxes'       => '40,400',
				'id'          => 'widget-title-height',
				'value'       => (int)$d['title_height'],
 			) ),
		) );

		// Captions' color
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Captions\' color' ),
			'element' => $this->a->r( array(
				'class'       => 'form-control iris', 
				'type'        => 'color',
				'custom_data' => 'data-name="caption_color"',
				'value'       => $d['caption_color'],
				'id'          => 'widget-caption-color',
			) ),
		) );

		// Captions' size
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Captions\' size' ),
			'element' => $this->a->r( array(
				'class'       => 'form-control', 
				'type'        => 'dimension',
				'custom_data' => 'data-name="caption_height"',
				'titles'      => $this->a->__( 'Height in pixels' ) . ',' . $this->a->__( 'Height as a percentage of font\'s height' ),
				'maxes'       => '40,400',
				'id'          => 'widget-caption-height',
				'value'       => (int)$d['caption_height'],
 			) ),
		) );

		// Button's title
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Button\'s text' ),
			'element' => $this->a->r( array(
				'type'        => 'text',
				'class'       => 'form-control',
				'placeholder' => $this->a->__( 'Button\'s text' ),
				'custom_data' => 'data-name="button_text"',
				'value'       => $d['button_text'],
				'id'          => 'widget-button-text',
			) ),
		) );

		// Button's text color
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Button\'s text color' ),
			'element' => $this->a->r( array(
				'class'       => 'form-control iris', 
				'type'        => 'color',
				'custom_data' => 'data-name="button_text_color"',
				'value'       => $d['button_text_color'],
				'id'          => 'widget-button-text-color',
			) ),
		) );

		// Button's text size
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Button\'s text size' ),
			'element' => $this->a->r( array(
				'class'       => 'form-control', 
				'type'        => 'dimension',
				'custom_data' => 'data-name="button_text_height"',
				'titles'      => $this->a->__( 'Height in pixels' ) . ',' . $this->a->__( 'Height as a percentage of font\'s height' ),
				'maxes'       => '40,400',
				'id'          => 'widget-button-text-height',
				'value'       => (int)$d['button_text_height'],
 			) ),
		) );

		// Button's color
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Button\'s color' ),
			'element' => $this->a->r( array(
				'class'       => 'form-control iris', 
				'type'        => 'color',
				'custom_data' => 'data-name="button_color"',
				'value'       => $d['button_color'],
				'id'          => 'widget-button-color',
			) ),
		) );

		// Button's border color
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Button\'s border color' ),
			'element' => $this->a->r( array(
				'class'       => 'form-control iris', 
				'type'        => 'color',
				'custom_data' => 'data-name="button_border_color"',
				'value'       => $d['button_color'],
				'id'          => 'widget-button-border-color'
			) ),
		) );

		// Button's border radius
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Button\'s border radius' ),
			'element' => $this->a->r( array(
				'class'       => 'form-control', 
				'type'        => 'dimension',
				'custom_data' => 'data-name="button_border_radius"',
				'titles'      => $this->a->__( 'Radius in pixels' ),
				'maxes'       => '40',
				'values'      => 'px',
				'id'          => 'widget-button-border-radius',
				'value'       => (int)$d['button_border_radius'],
 			) ),
		) );

		// Fields' border radius
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Fields\' border radius' ),
			'element' => $this->a->r( array(
				'class'       => 'form-control', 
				'type'        => 'dimension',
				'custom_data' => 'data-name="button_border_radius"',
				'titles'      => $this->a->__( 'Radius in pixels' ),
				'maxes'       => '40',
				'values'      => 'px',
				'id'          => 'widget-field-border-radius',
				'value'       => (int)$d['field_border_radius'],
 			) ),
		) );

		// Fields' background color
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Fields\' background color' ),
			'element' => $this->a->r( array(
				'class'       => 'form-control iris',
				'type'        => 'color',
				'custom_data' => 'data-name="field_background_color"',
				'value'       => $d['field_background_color'],
				'id'          => 'widget-field-background',
			) ),
		) );

		// Fields' background color
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Code' ),
			'element' => $this->a->r( array(
				'class'       => 'form-control clipboard',
				'type'        => 'textarea',
				'custom_data' => 'data-name="code"',
				'value'       => $d['code'],
				'id'          => 'widget-code',
			) ),
			'description'     => $this->a->__('To display the widget on any site - just paste this code into any place of a page of a target site. Also, you can use OpenCart\'s <a href="%s" target="_blank">layout management system</a> to add the widget to available layouts of your store', $this->url->link( 'design/layout', 'token=' . $this->session->data['token'], 'SSL' ) ),
		) );

		// Fields' background color
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Status' ),
			'element' => $this->a->r()->render_fancy_checkbox( array(
				'custom_data' => 'data-name="status"',
				'value'       => $d['status'],
				'id'          => 'widget-status',
			) ),
		) );

		$ret .= $this->a->r( array(
			'type'        => 'hidden',
			'value'       => $d['module_id'],
			'custom_data' => 'data-name="module_id"',
			'id'          => 'widget-module-id',
		) );

		// Save widget
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Manage widget' ),
			'element' => $this->a->r( array(
				'type'        => 'buttongroup',
				'buttons' => array(
					array(
						'type'        => 'button',
						'custom_data' => 'data-url="' .
							$this->url->link(
								$this->a->type . '/' . $this->a->code . '/save_widget',
								'token=' . $this->session->data['token'],
								'SSL'
							) .
						'"',
						'button_type' => 'primary',
						'text_before' => $this->a->__( 'Save' ),
						'icon'        => 'fa-save',
						'id'          => 'widget-save',
					),
					array(
						'type'        => 'button',
						'custom_data' => 'data-url="' .
							$this->url->link(
								$this->a->type . '/' . $this->a->code . '/delete_widget',
								'token=' . $this->session->data['token'],
								'SSL'
							) .
						'"',
						'button_type' => 'danger',
						'text_before' => $this->a->__( 'Delete' ),
						'icon'        => 'fa-close',
						'id'          => 'widget-delete',
					),
				),
				
			) ),
		) );

		$ret .= '</div>';
		$ret .= '<div class="col-sm-6">';
		$ret .= '<div id="form-wrapper">' . $this->a->get_widget_script() . '</div>';
		$ret .= '</div>';

		return $ret;
	}

	/**
	 * Save subscription widget action
	 * @return void
	 */
	public function save_widget() {
		$ret = array();

		try {

			if ( ! $this->a->p( 'name' ) ) {
				throw new \Advertikon\Exception( $this->a->__( 'Widget name missing' ) );
			}

			$name = $this->a->p( 'name' );
			$data = $this->request->post;
			$id = $this->a->p( 'id' );

			$module_id = $id > 0 ? $this->a->p( 'module_id' ) : null;
			$module = array();

			// Check if layout module for widget is exists
			if ( $module_id ) {
				$module = $this->a->q( array(
					'table' => 'module',
					'query' => 'select',
					'where' => array(
						'field'     => 'module_id',
						'operation' => '=',
						'value'     => $module_id,
					),
				) );
			}

			// If not exists - create it
			if ( ! count( $module ) ) {
				$this->a->q( array(
					'table' => 'module',
					'query' => 'insert',
					'values' => array(
						'name'    => $name,
						'code'    => $this->a->code,
						'setting' => version_compare( VERSION, '2.1.0.1', '>=' ) ?
							json_encode( array( 'status' => $data['status'], 'widget_id' => $id ) ) :
							serialize( array( 'status' => $data['status'], 'widget_id' => $id ) ),
					),
				) );

				$module_id = $this->db->getLastId();
			}

			$data['module_id'] = $module_id;

			// Update operation
			if ( $id >= 0 ) {

				$result = $this->a->q( array(
					'table' => $this->a->newsletter_widget_table,
					'query' => 'update',
					'set' => array(
						'name' => $name,
						'data' => json_encode( $data, JSON_HEX_QUOT | JSON_HEX_APOS ),
					),
					'where' => array(
						'field'     => 'id',
						'operation' => '=',
						'value'     => $id,
					),
				) );

				if ( ! $result ) {
					throw new \Advertikon\Exception( $this->a->__( 'Failed to update the widget' ) );
				}

			// New widget
			} else {

				$result = $this->a->q( array(
					'table' => $this->a->newsletter_widget_table,
					'query' => 'insert',
					'values' => array(
						'name'       => $name,
						'data'       => json_encode( $data, JSON_HEX_QUOT | JSON_HEX_APOS ),
						'date_added' => date( 'c' ),
					),
				) );

				if ( ! $result ) {

					// Remove layout module on failure
					$this->a->q( array(
						'table' => 'module',
						'query' => 'delete',
						'where' => array(
							'field'     => 'module_id',
							'operation' => '=',
							'value'     => $modile_id,
						),
					) );

					throw new \Advertikon\Exception( $this->a->__( 'Failed to save the widget' ) );
				}

				$id = $this->db->getLastId();
			}

			// Update layout module
			$this->a->q( array(
				'table' => 'module',
				'query' => 'update',
				'where' => array(
					'field'     => 'module_id',
					'operation' => '=',
					'value'     => $module_id,
				),
				'set' => array(
					'setting' => version_compare( VERSION, '2.1.0.1', '>=' ) ?
						json_encode( array( 'status' => $data['status'], 'widget_id' => $id, ) ) :
						serialize( array( 'status' => $data['status'], 'widget_id' => $id, ) ),
				),
			) );

			$ret['success'] = $this->a->__( 'Widget has been saved successfully' );
			$ret['id'] = $id;
			$ret['code'] = $this->a->get_widget_script( $id );
			$ret['module_id'] = $module_id;

		} catch ( \Advertikon\Exception $e ) {
			$ret['error'] = $e->getMessage();
		}

		$this->response->setOutput( json_encode( $ret ) );
	}

	/**
	 * Fetch subscription widget action
	 * @return void
	 */
	public function fetch_widget() {
		$ret = array();

		try {
			if ( ! $this->a->request( 'id' ) ) {
				throw new \Advertikon\Exception( $this->a->__( 'Widget ID missing' ) );
			}

			$id = $this->a->request( 'id' );

			$result = $this->a->q( array(
				'table' => $this->a->newsletter_widget_table,
				'query' => 'select',
				'where' => array(
					'field'     => 'id',
					'operation' => '=',
					'value'     => $id,
				),
			) );

			if ( ! $result ) {
				throw new \Advertikon\Exception( $this->helepr->__( 'Widget with ID "%s" doesn\'t exist', $id ) );
			}

			$data = json_decode( $result['data'] );

			$data->code = $this->a->get_widget_script( $id );

			$ret['success'] = json_encode( $data, JSON_HEX_QUOT );

		} catch ( \Advertikon\Exception $e ) {
			$ret['error'] = $e->getMessage();
		}

		$this->response->setOutput( json_encode( $ret ) );
	}

	/**
	 * Delete subscription widget action
	 * @return void
	 */
	public function delete_widget() {
		$ret = array();

		try {

			if ( ! $this->a->request( 'id' ) || '-1' == $this->a->request( 'id' ) ) {
				$this->helepr->exception( $this->a->__( 'Widget ID missing' ) );
			}

			$id = $this->a->request( 'id' );

			$widget = $this->a->q( array(
				'table' => $this->a->newsletter_widget_table,
				'query' => 'select',
				'where' => array(
					'field'     => 'id',
					'operation' => '=',
					'value'     => $id,
				),
			) );

			if ( ! count( $widget ) ) {
				throw new \Advertikon\Exception( $this->a->__( 'Widget with ID "%s" doesn\'t exist', $id ) );
			}

			$data = json_decode( $widget['data'] );

			$result = $this->a->q( array(
				'table' => $this->a->newsletter_widget_table,
				'query' => 'delete',
				'where' => array(
					'field'     => 'id',
					'operation' => '=',
					'value'     => $id,
				),
			) );

			if ( ! $result ) {
				throw new \Advertikon\Exception( $this->a->__( 'Failed to delete widget' ) );
			}

			if ( ! empty( $data->module_id ) ) {
				$module_result = $this->a->q( array(
					'table' => 'module',
					'query' => 'delete',
					'where' => array(
						'field'     => 'module_id',
						'operation' => '=',
						'value'     => $data->module_id,
					),
				) );

				if ( ! $module_result ) {
					trigger_error(
						sprintf(
							'Failed to remove widget #"%s" from OpenCart\'s modules (ID "%s")',
							$id,
							$data->module_id
						)
					);
				}
			}

			$ret['success'] = $this->a->__( 'Widget has been deleted' );

		} catch ( \Advertikon\Exception $e ) {
			$ret['error'] = $e->getMessage();
		}

		$this->response->setOutput( json_encode( $ret ) );
	}

	/**
	 * Save extension setting action
	 * @return void
	 */
	public function save_settings( ) {
		$ret = array();

		try {
			if ( empty( $this->request->post ) ) {
				throw new \Advertikon\Exception( $this->a->__( 'Settings data are missing' ) );
			}

			$this->load->model( 'setting/setting' );
			$this->model_setting_setting->editSetting(
				$this->a->code,
				array_merge(
					$this->model_setting_setting->getSetting( $this->a->code ),
					$this->request->post
				)
			);

			$ret['success'] = $this->a->__( 'Data have been successfully saved' );

		} catch ( \Advertikon\Exception $e ) {
			$ret['error'] = $e->getMessage();
		}

		$this->response->setOutput( json_encode( $ret ) );
	}

	/**
	 * Import subscribers action
	 * @return void
	 */
	public function import_subscribers() {
		$ret = array();

		try {

			if ( ! $this->a->request( 'source' ) ) {
				throw new \Advertikon\Exception( $this->a->__( 'Source newsletter is missing') );
			}

			if ( ! $this->a->request( 'subscribers' ) ) {
				throw new \Advertikon\Exception( $this->a->__( 'Subscribers groups are missing' ) );
			}

			if ( ! $this->a->request( 'target' ) ) {
				throw new \Advertikon\Exception( $this->a->__( 'Target newsletter is missing' ) );
			}

			$source = $this->a->request( 'source' );
			$subscribers = $this->a->request( 'subscribers' );
			$override = (boolean)$this->a->request( 'override' );
			$target = $this->a->request( 'target' );

			if ( $override ) {
				$result = $this->model->import_subscribers_override( $target, $source, $subscribers );

			} else {
				$result = $this->model->import_subscribers( $target, $source, $subscribers );
			}

			if ( -1 == $result ) {
				$resp['error'] = $this->a->__( 'Failed to import subscribers' );
			}

			$ret['success'] = $this->a->__( '%s record(s) was imported', $result );

		} catch ( \Advertikon\Exception $e ) {
			$ret['error'] = $e->getMessage();
		} 

		$this->response->setOutput( json_encode( $ret ) );
	}

	/**
	 * Import OC subscribers action
	 * @return void
	 */
	public function import_oc_subscribers() {

		$ret = array();
		$count = 0;

		try {

			$id = $this->a->request( 'id' );

			if ( is_null( $id ) ) {
				$this->a->excpetion( $this->a->__( 'Target newsletter\'s ID is missing' ) );
			}

			$list = (array)$this->a->post( 'subscriptions' );

			if ( ! $list ) {
				throw new \Advertikon\Exception( $this->a->__( 'You need to specify subscribers\' filter' ) );
			}

			$override = (bool)$this->a->post( 'override' );

			// Import customers and affiliates
			if ( in_array( -1, $list ) ) {
				$result = $this->model->import_subscribers_oc_customers( $id, $override );

				if ( ! $result ) {
					throw new \Advertikon\Exception( $this->a->__( 'Failed to import subscribers' ) );
				}

				$count += $this->db->countAffected();

				$result = $this->model->import_subscribers_oc_affiliates( $id, $override );

				if ( ! $result ) {
					throw new \Advertikon\Exception( $this->a->__( 'Failed to import subscribers' ) );
				}

				$count += $this->db->countAffected();

			} else {

				// Import all the customers
				if ( in_array( 'customer_all', $list ) ) {
					$result = $this->model->import_subscribers_oc_customers( $id, $override );

					if ( ! $result ) {
						throw new \Advertikon\Exception( $this->a->__( 'Failed to import subscribers' ) );
					}

					$count += $this->db->countAffected();

				// Import customers depend on conditions
				} else {

					// Import specific customer
					if ( in_array( 'customer', $list ) ) {
						$customers = $this->a->post( 'customer' );

						if ( ! $customers ) {
							throw new \Advertikon\Exception( $this->a->__( 'You need to specify customers to be imported' ) );
						}

						$result = $this->model->import_subscribers_oc_customers(
							$id,
							$override,
							array( 'operation' => 'in', 'field' => 'customer_id', 'value' => $customers )
						);

						if ( ! $result ) {
							throw new \Advertikon\Exception( $this->a->__( 'Failed to import subscribers' ) );
						}

						$count += $this->db->countAffected();
					}

					// Import all the newsletter subscribers
					if ( in_array( 'newsletter', $list ) ) {
						$result = $this->model->import_subscribers_oc_customers(
							$id,
							$override,
							array( 'operation' => '=', 'field' => 'o.newsletter', 'value' => 1 )
						);

						if ( ! $result ) {
							throw new \Advertikon\Exception( $this->a->__( 'Failed to import subscribers' ) );
						}

						$count += $this->db->countAffected();
					}

					// Import specific customer group
					if ( in_array( 'customer_group', $list ) ) {
						$customer_groups = $this->a->post( 'customer_group' );

						if ( ! $customer_groups ) {
							throw new \Advertikon\Exception( $this->a->__( 'You need to specify customer groups to be imported' ) );
						}

						$result = $this->model->import_subscribers_oc_customers(
							$id,
							$override,
							array( 'operation' => 'in', 'field' => 'customer_group_id', 'value' => $customer_groups )
						);

						if ( ! $result ) {
							throw new \Advertikon\Exception( $this->a->__( 'Failed to import subscribers' ) );
						}

						$count += $this->db->countAffected();
					}
				}

				// Import all the affiliates
				if ( in_array( 'affiliate_all', $list ) ) {
					$result = $this->model->import_subscribers_oc_affiliates( $id, $override );

					if ( ! $result ) {
						throw new \Advertikon\Exception( $this->a->__( 'Failed to import subscribers' ) );
					}

					$count += $this->db->countAffected();

				// Import specific affiliate
				} elseif ( in_array( 'affiliate', $list ) ) {
					$affilaites = $this->a->post( 'affiliate' );

					if ( ! $affilaites ) {
						throw new \Advertikon\Exception( $this->a->__( 'You need to specify affiliates to be imported' ) );
					}

					$result = $this->model->import_subscribers_oc_affiliates(
						$id,
						$override,
						array( 'operation' => 'in', 'field' => 'affiliate_id', 'value' => $affilaites )
					);

					if ( ! $result ) {
						throw new \Advertikon\Exception( $this->a->__( 'Failed to import subscribers' ) );
					}

					$count += $this->db->countAffected();
				}

				// Import specific product's purchasers
				if ( in_array( 'product', $list ) ) {
					$products = $this->a->post( 'product' );

					if ( ! $products ) {
						throw new \Advertikon\Exception( $this->a->__( 'You need to specify products' ) );
					}

					$result = $this->model->import_subscribers_oc_products( $id, $override, $products );

					if ( ! $result ) {
						throw new \Advertikon\Exception( $this->a->__( 'Failed to import subscribers' ) );
					}

					$count += $this->db->countAffected();
				}
			}

			$ret['success'] = $this->a->__( '%s subscriber(s) have been imported', $count );

		} catch ( \Advertikon\Exception $e ) {
			$ret['error'] = $e->getMessage();
		}

		$this->response->setOutput( json_encode( $ret ) );
	}

	/**
	 * Export subscribers action
	 * @return void
	 */
	public function export_subscribers() {
		try {

			if ( ! $this->a->request( 'id' ) ) {
				throw new \Advertikon\Exception( 'Failed export subscribers - newsletter ID is missing' );
			}

			$id = $this->a->request( 'id' );

			if ( ! $this->a->request( 'sort' ) ) {
				throw new \Advertikon\Exception( 'Failed export subscribers - missed fields to be exported' );
			}

			$sort = explode( ',', $this->a->request( 'sort' ) );

			$query = array(
				'table' => $this->a->newsletter_subscribers_table,
				'query' => 'select',
				'where' => array(
					array(
						'field'     => 'newsletter',
						'operation' => '=',
						'value'     => $id,
					)
				),
			);

			if ( $this->a->request( 'filter' ) ) {
				$filter = explode( ',', $this->a->request( 'filter' ) );
				if ( ! in_array( 100, $filter ) ) {
					$query['where'][] = array(
						'field'     => 'status',
						'operation' => 'in',
						'value'     => $filter,
					);
				}
			}

			$subscribers = $this->a->q( $query );

			foreach( $sort as $sort_item ) {
				if ( ! array_key_exists( $sort_item, $subscribers->current() ) ) {
					throw new \Advertikon\Exception( sprintf( 'Field "%s" is missing in data set', $sort_item ) );
				}
			}

			header( 'Content-Type: text/csv' );
			header( 'Content-Disposition: attachment; filename="subscribers.csv"' );

			// Header
			echo '"' . implode( '","', $sort ) . '"' . "\r\n";


			foreach( $subscribers as $s ) {

				echo '"' . implode( '","', array_map( function( $name ) use( $s ) {
					return $s[ $name ];
				}, $sort ) ) . '"' . "\r\n";
			}

		} catch ( \Advertikon\Exception $e ) {
			trigger_error( $e->getMessage() );
		}
	}

	/**
	 * Chart's data action
	 * @return void
	 */
	public function chart() {
		$ret = array();

		try {

			if ( ! $this->a->request( 'id' ) ) {
				throw new \Advertikon\Exception( $this->a->__( 'Newsletter ID is missing' ) );
			}

			$id = $this->a->request( 'id' );

			$ret['data'] = $this->model->get_chart_data( $id, $this->a->q()->merge_where( array(
				'field'     => 'newsletter',
				'operation' => '=',
				'value'     => $id,
			), $this->a->p( 'where', array() ) ) );

			if ( true || $this->a->request( 'all' ) ) {
				$ret['summary'] = $this->model->get_total_chart_data( $id );
			}

			$ret['success'] = 'ok';

		} catch ( \Advertikon\Exception $e ) {
			$ret['error'] = $e->getMEssage();
		}

		$this->response->setOutput( json_encode( $ret ) );
	} 

	/**
	 * Affiliate autocomplete action
	 * @return void
	 */
	public function affiliate_autocomplete() {
		$ret = array();
		$query = $this->a->request( 'filter_name', '' );

		$query = str_replace( array( '%', '_' ), array( '\\%', '\\_', ), $query );
		$query = '%' . $query . '%';

		$res = $this->a->q( array(
			'table'  => 'affiliate',
			'query'  => 'select',
			'field' => array(
				'name' => 'concat(`firstname`, " ", `lastname` )',
				'id'   => 'affiliate_id',
			),
			'where' => array(
				array(
					'field'     => 'firstname',
					'operation' => 'like',
					'value'     => $query,
				),
				array(
					'field'     => 'lastname',
					'operation' => 'like',
					'value'     => $query,
				),
			),
		) );

		if ( $res ) {
			$ret = $res->getArrayCopy();
		}

		$this->response->setOutput( json_encode( $ret ) );
	}

	/**
	 * Runs the queue
	 * @return void
	 */
	public function run_queue() {
		$ret = array();
		$timeout = (int)$this->a->config( 'queue_time' );
		$timeout = $timeout > 0 ? $timeout : 30;

		set_time_limit( $timeout );

		error_reporting(E_ALL);
		ini_set('display_errors', 1);

		ignore_user_abort(true);
		header("Connection: close");
		header("Content-Encoding: none");  
		ob_start();

		// Need to be non-empty string for Content-length header to appear   
		echo json_encode( array( 'success' => $this->a->__( 'The queue has been run' ) ) ); 
		$size = ob_get_length();   
		header( "Content-Length: $size" );  
		ob_end_flush();
		@ob_flush();
		flush();

		$this->a->run_queue();
	}

	/**
	 * Flushes the queue
	 * @return void
	 */
	public function flush_queue() {
		$ret = array();

		try {
			$this->db->query( "TRUNCATE TABLE `" . DB_PREFIX . $this->a->queue_table . "`" );
			$ret['success'] = $this->a->__( 'Queue has been flushed' );

		} catch ( \Advertikon\Exception $e ) {
			$ret['error'] = $e->getMessage();
		}

		$this->response->setOutput( json_encode( $ret ) );
	}

	/**
	 * Manage subscription action
	 * @return void
	 */
	public function manage_subscription() {
		$ret = array();

		try {
			$id = $this->a->post( 'id' );
			$action = $this->a->post( 'action' );

			if ( ! $id ) {
				throw new \Advertikon\Exception( $this->a->__( 'Subscriber\'s ID is missing' ) );
			}

			if ( ! $action ) {
				throw new \Advertikon\Exception( $this->a->__( 'Unsupported action' ) );
			}

			switch( $action ) {
			case 'activate':
				$this->a->q( array(
					'table' => $this->a->newsletter_subscribers_table,
					'query' => 'update',
					'set'   => array(
						'status' => \Advertikon\Mail\Advertikon::SUBSCRIBER_STATUS_ACTIVE
					),
					'where' => array(
						'field'     => 'id',
						'operation' => '=',
						'value'     => $id,
					),
				 ) );

				$ret['success'] = $this->a->__( 'Subscription has been activated' );
				break;
			case 'deactivate':
				$this->a->q( array(
					'table' => $this->a->newsletter_subscribers_table,
					'query' => 'update',
					'set'   => array(
						'status' => \Advertikon\Mail\Advertikon::SUBSCRIBER_STATUS_SUSPENDED
					),
					'where' => array(
						'field'     => 'id',
						'operation' => '=',
						'value'     => $id,
					),
				 ) );

				$ret['success'] = $this->a->__( 'Subscription has been deactivated' );
				break;
			case 'delete':
				$this->a->q( array(
					'table' => $this->a->newsletter_subscribers_table,
					'query' => 'delete',
					'where' => array(
						'field'     => 'id',
						'operation' => '=',
						'value'     => $id,
					),
				 ) );

				$ret['success'] = $this->a->__( 'Subscription has been removed' );
				break;
			default:
				throw new \Advertikon\Exception( $this->a->__( 'Unknown action' ) );
				break;
			}

		} catch ( \Advertikon\Exception $e ) {
			$ret['error'] = $e->getMessage();
		}

		$this->response->setOutput( json_encode( $ret ) );
	}

	/**
	 * Imports subscribers from CSV file
	 * @return void
	 */
	public function import_csv() {
		$ret          = array();
		$values       = array();
		$names        = array( 'email', 'name', 'status', 'newsletter', 'date_added', );
		$email_preg   = '/^[A-Za-z0-9._-]+@[A-Za-z0-9._-]+$/';

		$email_no     = (int)$this->a->post( 'email' ) - 1;
		$fullname_no  = (int)$this->a->post( 'fullname' ) - 1;
		$firstname_no = (int)$this->a->post( 'firstname' ) - 1;
		$lastname_no  = (int)$this->a->post( 'lastname' ) - 1;
		$override     = (int)$this->a->post( 'override' );
		$newsletter   = (int)$this->a->post( "newsletter" );

		try {

			if ( $newsletter <= 0 ) {
				throw new \Advertikon\Exception( $this->a->__( 'Invalid newsletter' ) );
			}

			foreach( $_FILES as $f ) {
				for( $i = 0; $i < count( $f['name'] ); $i++ ) {
					
					// Non-textual file
					if ( 'text' !== strstr( $f['type'][ $i ], '/', true ) ) {
						throw new \Advertikon\Exception(
							$this->a->__( 'Text files only allowed (%s)', $f['name'][ $i ] )
						);
					}

					$test_run_count = 5;

					$fp = fopen( $f['tmp_name'][ $i ], 'r' );

					// Test run
					while( $test_run_count-- > 0 && false !== ( $line = fgetcsv( $fp ) ) ) {

						// Email field position is not specified - try to guess
						if ( $email_no < 0 ) {
							foreach( $line as $index => $record ) {
								if ( preg_match( $email_preg, trim( $record ) ) ) {
									$email_no = $index;
									break;
								}
							}

						// Email position is specified or was guessed earlier - confirm its position
						} else {
							if ( ! preg_match( $email_preg, trim( $line[ $email_no ] ) ) ) {
								throw new \Advertikon\Exception(
									$this->a->__(
										'File %s does not contain column with valid email addresses',
										$f['name'][ $i ]
									)
								);
							}
						}

						if ( $fullname_no >=0 ) {
							if ( ! isset( $line[ $fullname_no ] ) ) {
								throw new \Advertikon\Exception(
									$this->a->__(
										'Full name column (%s) is missing in file %s',
										$fullname_no + 1,
										$f['name'][ $i ]
									)
								);
							}

						} else {
							if ( $firstname_no >=0 && ! isset( $line[ $firstname_no ] ) ) {
								throw new \Advertikon\Exception(
									$this->a->__(
										'First name column (%s) is missing in file %s',
										$firstname_no + 1,
										$f['name'][ $i ]
									)
								);
							}

							if ( $lastname_no >=0 && ! isset( $line[ $lastname_no ] ) ) {
								throw new \Advertikon\Exception(
									$this->a->__(
										'Last name column (%s) is missing in file %s',
										$lastname_no + 1,
										$f['name'][ $i ]
									)
								);
							}
						}
					}

					if ( $email_no < 0 ) {
						throw new \Advertikon\Exception(
							$this->a->__(
								'Failed to detect email field column for file %s',
								$f['name'][ $i ]
							)
						);
					}

					rewind( $fp );

					while( false !== ( $line = fgetcsv( $fp ) ) ) {
						$email = trim( $line[ $email_no ] );
						$name = '';

						if ( $fullname_no > 0 ) {
							$name = trim( $line[ $fullname_no ] );

						} else {
							if ( $firstname_no > 0 ) {
								$name = trim( $line[ $firstname_no ] );
							}

							if ( $lastname_no > 0 ) {
								if ( $name ) {
									$name .= ' ';
								}

								$name .= trim( $line[ $lastname_no ] );
							}
						}

						$values[] = array(
							$email,
							$name,
							\Advertikon\Mail\Advertikon::SUBSCRIBER_STATUS_ACTIVE,
							$newsletter,
							date( 'c' )
						);
					}

					fclose( $fp );
				}
			}

			$ret['success'] = $this->a->__(
				'%s record(s) have been imported', $this->model->import_csv( $values, $names, $override )
			);

		} catch ( \Advertikon\Exception $e ) {
			$ret['error'] = $e->getMessage();
		}

		$this->response->setOutput( json_encode( $ret ) );
	}

	/**
	 * Action to blacklist bounced emails
	 * @return type
	 */
	public function do_blacklist() {
		$ret = array();

		try {
			$ret['success'] = $this->a->__( '%s email(s) were blacklisted', $this->a->do_blacklist() );

		} catch( \Advertikon\Exception $e ) {
			$ret['error'] = $e->getMessage();
		}

		$this->response->setOutput( json_encode( $ret ) );
	}

	/**
	 * Saves IMAP settings
	 * @return void
	 */
	public function save_imap() {
		$url       = $this->a->post( 'url' );
		$port      = $this->a->post( 'port' );
		$ssl       = $this->a->post( 'ssl' );
		$blacklist = $this->a->post( 'blacklist' );
		$unseen    = $this->a->post( 'unseen' );
		$action    = $this->a->post( 'action' );
		$login     = $this->a->post( 'login' );
		$password  = $this->a->post( 'password' );
		$ret       = array();

		try {
			if ( ! $url ) {
				throw new \Advertikon\Exception( $this->a->__( 'URL of IMAP server is missing' ) );
			}

			if ( ! $port ) {
				throw new \Advertikon\Exception( $this->a->__( 'Port of IMAP server is missing' ) );
			}

			if ( ! $login ) {
				throw new \Advertikon\Exception( $this->a->__( 'Log in name is missing' ) );
			}

			if ( ! $password ) {
				throw new \Advertikon\Exception( $this->a->__( 'Password for account is missing' ) );
			}

			$this->load->model( 'setting/setting' );

			$settings = $this->model_setting_setting->editSetting(
				$this->a->code,
				array_merge(
					$this->model_setting_setting->getSetting( $this->a->code ),
					array(
						$this->a->prefix_name( 'imap_url' )       => $url,
						$this->a->prefix_name( 'imap_port' )      => $port,
						$this->a->prefix_name( 'imap_ssl' )       => (int)$ssl,
						$this->a->prefix_name( 'imap_blacklist' ) => (int)$blacklist,
						$this->a->prefix_name( 'imap_unseen' )    => (int)$unseen,
						$this->a->prefix_name( 'imap_action' )    => (int)$action,
						$this->a->prefix_name( 'imap_login' )     => $login,
						$this->a->prefix_name( 'imap_password' )  => $password,
					)
				)
			);

			if ( $this->db->countAffected() > 0 ) {
				$ret['success'] = $this->a->__( 'Configurations have been successfully changed');

			} else {
				throw new \Advertikon\Exception( $this->a->__( 'Failed to save configurations' ) );
			}

		} catch ( \Advertikon\Exception $e ) {
			$ret['error'] = $e->getMessage();
		}

		$this->response->setOutput( json_encode( $ret ) );
	}

	/**
	 * Check IMAP configurations action
	 * @return void
	 */
	public function check_imap() {
		$ret = array();
		$url       = $this->a->post( 'url' );
		$port      = $this->a->post( 'port' );
		$login     = $this->a->post( 'login' );
		$password  = $this->a->post( 'password' );
		$ssl       = $this->a->post( 'ssl' );

		try {
			if ( ! $url ) {
				throw new \Advertikon\Exception( $this->a->__( 'URL of IMAP server is missing' ) );
			}

			if ( ! $port ) {
				throw new \Advertikon\Exception( $this->a->__( 'Port of IMAP server is missing' ) );
			}

			if ( ! $login ) {
				throw new \Advertikon\Exception( $this->a->__( 'Log in name is missing' ) );
			}

			if ( ! $password ) {
				throw new \Advertikon\Exception( $this->a->__( 'Password for account is missing' ) );
			}

			$this->model->test_imap( $url, $port, $login, $password, $ssl );
			$ret['success'] = 'IMAP configuration is correct';

		} catch( \Advertikon\Exception $e ) {
			$ret['error'] = $e->getMessage();
		}

		$this->response->setOutput( json_encode( $ret ) );
	}
}
