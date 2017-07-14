<?php
/**
 * @package Mail template manager
 * @author Advertikon
 * @version 1.0.8
 */

namespace Advertikon;

class Mail extends Advertikon {

	/**
	 * @var String Extension type
	 */
	public $type = 'module';

	/**
	 * @var String Extension code
	 */
	public $code = 'adk_mail';

	/**
	 * @var Global registry object
	 */
	public $registry = null;

	/**
	 * @var String Prefix to distinguish error depend on context
	 */
	public $error_prefix = array( 'Mail');

	/**
	 * @var String Prefix for debugging messages
	 */
	public $debug_prefix = array( 'Mail[Debug]' );

	/**
	 * @var Boolean Flag, which shows whether debug is enabled
	 */
	protected $debug_enabled = false;

	/**
	 * @var Array Extension's tables
	 */
	public $tables = array(
		'profiles_table'               => 'adk_mail_profile',
		'fields_table'                 => 'adk_mail_field',
		'templates_table'              => 'adk_mail_template',
		'shortcodes_table'             => 'adk_mail_shortcode',
		'profile_mapping_table'        => 'adk_mail_profile_mapping',
		'template_mail_table'          => 'adk_mail_template_mail',
		'newsletter_code_table'        => 'adk_mail_newsletter_code',
		'history_table'                => 'adk_mail_history',
		'newsletter_list_table'        => 'adk_mail_newsletter_list',
		'newsletter_subscribers_table' => 'adk_mail_newsletter_subscribers',
		'newsletter_widget_table'      => 'adk_mail_newsletter_widget',
		'newsletter_to_widget_table'   => 'adk_mail_newsletter_to_widget',
		'queue_table'                  => 'adk_mail_queue',
	);

	/**
	 * @var array
	 */
	public $subscribe_widget_defaults = array();

	/**
	 * @var int $max_tenp_stack Number of snapshots (temp session savings) for each profile
	 */
	protected $max_temp_stack = 20;

	/**
	 * @var object Instance of Mail to be modified
	 */
	public $modified_mail = null;

	/**
	 * @var string File name to store mail templates for marketing newsletter
	 */
	public $mail_template_file = '';

	protected $profiles = null;
	protected $templates = null;
	protected $stores = null;
	protected $shortcodes = null;
	protected $shortcode_set = null;
	protected $profile_mappings = null;
	public $attachments_root = '';
	public $message = null;
	public $swift_loader = null;
	public $tmp_dir = null;
	public $shortcodes_stack = array();
	public $eldinder_root = null;
	public $archive_dir = null;
	public $data_dir = null;
	protected $email_log = '';
	protected $attached_img = array();
	
	// Code's expiration period in days
	const CODE_EXPIRATION_CONFIRM     = 5;
	const CODE_EXPIRATION_CANCEL      = 5;
	const CODE_EXPIRATION_TRACK_VISIT = 20;
	const CODE_EXPIRATION_DEFAULT     = 5;

	const QUEUE_STATUS_INACTIVE = 0;
	const QUEUE_STATUS_ACTIVE   = 1;

	const SUBSCRIBER_STATUS_INACTIVE      = 0;
	const SUBSCRIBER_STATUS_ACTIVE        = 1;
	const SUBSCRIBER_STATUS_SUSPENDED     = 2;
	const SUBSCRIBER_STATUS_VERIFICATION  = 3;
	const SUBSCRIBER_STATUS_CANCELLED     = 4;
	const SUBSCRIBER_STATUS_BLACKLISTED   = 5;

	const NEWSLETTER_STATUS_INACTIVE = 0;
	const NEWSLETTER_STATUS_ACTIVE   = 1;

	const NEWSLETTER_CODE_SUBSCRIBE     = 1;
	const NEWSLETTER_CODE_CANCEL        = 2;
	const NEWSLETTER_CODE_TRACK_VISITOR = 3;

	const EMAIL_STATUS_FAIL    = 0;
	const EMAIL_STATUS_SUCCESS = 1;

	const IMAP_ACTION_NOTING = 0;
	const IMAP_ACTION_SEEN   = 1;
	const IMAP_ACTION_DELETE = 2;

	public $social_set = array();

	/**
	 * Class constructor
	 * @return type
	 */
	public function __construct() {
		global $adk_registry;

		if ( ! is_a( $adk_registry, '\Registry' ) ) {
			die(
				sprintf(
					'Please refresh modifications by clicking "Refresh" button from "Modifications" page. <a href="%s">Home</a>',
					HTTP_SERVER
				)
			);
		}

		if ( version_compare( VERSION, '2.3.0.0', '>=' ) ) {
			$this->type = 'extension/' . $this->type;
		}

		parent::__construct();

		$this->data_dir            = parent::$data_dir . 'mail/';
		$this->newsletter_template = $this->data_dir . 'newsletter/template/';
		$this->attachments_root    = $this->data_dir . 'attachments/';
		$this->tmp_dir             = $this->data_dir . 'tmp/';
		$this->archive_dir         = $this->data_dir . 'archive/';
		$this->swift_loader        = __DIR__ . '/swift_required.php';
		$this->elfinder_root       = __DIR__ . '/elfinder/';

		$this->subscribe_widget_defaults = array(
			'width'                  => '400px',
			'title'                  => $this->__( 'Subscribe to newsletter' ),
			'title_height'           => '20px',
			'title_color'            => '#000',
			'caption_color'          => '#000',
			'caption_height'         => '14px',
			'button_text'            => $this->__( 'Subscribe' ),
			'button_text_color'      => '#fff',
			'button_text_height'     => '16px',
			'button_color'           => '#00a3d9',
			'button_border_color'    => '#00a3d9',
			'button_border_radius'   => '10px',
			'field_border_radius'    => '5px',
			'background_color'       => '#fff',
			'field_background_color' => '#fff',
			'box_shadow_x'           => '5px',
			'box_shadow_y'           => '5px',
			'box_shadow_dispersion'  => '5px',
			'border_radius'          => '5px',
			'name'                   => '',
			'code'                   => '',
			'status'                 => 1,
			'module_id'              => '',
		);

		$this->social_set = array(
			'facebook' => array(
				'icon'        => 'fa-facebook-official',
				'placeholder' => $this->__( 'Facebook account URL' ),
				'title'       => 'Facebook',
			),
			'google+' => array(
				'icon'        => 'fa-google-plus',
				'placeholder' => $this->__( 'Google+ account URL' ),
				'title'       => 'Google+',
			),
			'instagram' => array(
				'icon'        => 'fa-instagram',
				'placeholder' => $this->__( 'Instargam account URL' ),
				'title'       => 'Instagram',
			),
			'linkedin' => array(
				'icon'        => 'fa-linkedin',
				'placeholder' => $this->__( 'Linkedin account URL' ),
				'title'       => 'Linkedin',
			),
			'twitter' => array(
				'icon'        => 'fa-twitter',
				'placeholder' => $this->__( 'Twitter account URL' ),
				'title'       => 'Twitter',
			),
			'tumblr' => array(
				'icon'        => 'fa-tumblr',
				'placeholder' => $this->__( 'Tumblr account URL' ),
				'title'       => 'Tumblr',
			),
			'yelp' => array(
				'icon'        => 'fa-yelp',
				'placeholder' => $this->__( 'Yelp account URL' ),
				'title'       => 'Yelp',
			),
			'vine' => array(
				'icon'        => 'fa-vine',
				'placeholder' => $this->__( 'Vine video URL' ),
				'title'       => 'Vine',
			),
			'youtube' => array(
				'icon'        => 'fa-youtube',
				'placeholder' => $this->__( 'Youtube video URL' ),
				'title'       => 'Youtube',
			),
			'behance' => array(
				'icon'        => 'fa-behance',
				'placeholder' => $this->__( 'Behance' ),
				'title'       => 'Bhance',
			),
			'deviantart' => array(
				'icon'        => 'fa-deviantart',
				'placeholder' => $this->__( 'Deviantart' ),
				'title'       => 'Deviantart',
			),
			'dribbble' => array(
				'icon'        => 'fa-dribbble',
				'placeholder' => $this->__( 'Dribbble' ),
				'title'       => 'Dribbble',
			),
			'github' => array(
				'icon'        => 'fa-github',
				'placeholder' => $this->__( 'Github' ),
				'title'       => 'Github',
			),
			'pinterest' => array(
				'icon'        => 'fa-pinterest',
				'placeholder' => $this->__( 'Pnterest' ),
				'title'       => 'Pinterest',
			),
			'reddit' => array(
				'icon'        => 'fa-reddit',
				'placeholder' => $this->__( 'Reddit' ),
				'title'       => 'Reddit',
			),
			'snapchat' => array(
				'icon'        => 'fa-snapchat',
				'placeholder' => $this->__( 'Snapchat' ),
				'title'       => 'Snapchat',
			),
			'stumbleupon' => array(
				'icon'        => 'fa-stumbleupon',
				'placeholder' => $this->__( 'Stumbleupon' ),
				'title'       => 'Stumbleupon',
			),
			'whatsapp' => array(
				'icon'        => 'fa-whatsapp',
				'placeholder' => $this->__( 'Whatsapp' ),
				'title'       => 'Whatsapp',
			),
		);
	}

	/**
	 * Gets value of configuration setting regarding extension name
	 * @param string $name Configuration name
	 * @return mixed
	 */
	// public function config( $name, $default = null ) {

	// 	// Composed name eg one/two/three
	// 	if ( strpos( $name , '/' ) !== false ) {
	// 		$parts = explode( '/', $name );
	// 		$conf = $this->registry->get( 'config' )->get( $this->prefix_name( array_shift( $parts ) ) );

	// 	} else {
	// 		$conf = $this->registry->get( 'config' )->get( $this->prefix_name( $name ) );
	// 	}

	// 	if ( ! empty( $parts ) ) {
	// 		foreach( $parts as $p ) {
	// 			if ( isset( $conf[ $p ] ) ) {
	// 				$conf = $conf[ $p ];

	// 			} else {
	// 				$conf = null;
	// 				break;
	// 			}
				
	// 		}
	// 	}

	// 	if( is_null( $conf ) ) {
	// 		return $default;
	// 	}

	// 	return $conf;
	// }

	// public function __get( $name ) {
	// 	if ( isset( $this->tables[ $name ] ) ) {
	// 		return $this->tables[ $name ];
	// 	}

	// 	if( $this->registry->has( $name ) ) {
	// 		return $this->registry->get( $name );
	// 	}
	// }

	// public function __set( $name, $value ) {
	// 	$this->registry->set( $name, $value );
	// }


	/**
	 * Translator helper
	 * Supports 'sprintf' text substitutions
	 * @param String $text String to be translated
	 * @return String
	 */
	// public function __( $text ) {

	// 	$translation = $this->language->get( $text );

	// 	$args = func_get_args();

	// 	if ( count( $args ) > 1 ) {
	// 		array_shift( $args );
	// 		array_unshift( $args, $translation );
	// 		$substitution = call_user_func_array( 'sprintf' , $args );
	// 		if ( $substitution ) {

	// 			return $substitution;
	// 		}
	// 	}

	// 	return $translation;
	// }

	/**
	 * Renders Admin area panels headers
	 * @param Array $panels Panels list to be rendered
	 * @return void
	 */
	// public function render_panels_headers( $data ) {
	// 	$id        = isset( $data['id'] ) ? $data['id'] : '';
	// 	$class     = isset( $data['class'] ) ? $data['class'] : '';
	// 	$panels    = isset( $data['panels'] ) ? $data['panels'] : array();
	// 	$id_prefix = isset( $data['id_prefix'] ) ? $data['id_prefix'] : '';

	// 	$output = '<ul id="' . $id . '" class="nav nav-tabs ' . $class . '">';

	// 	foreach( $panels as $panel_name => $panel ) {
	// 		$output .= $this->render_panel_header( $panel, $panel_name, $id_prefix );
	// 	}

	// 	$output .= '</ul>';

	// 	return $output;
	// }

	// /**
	//  * Renders single panel header
	//  * @param array $panel 
	//  * @return string
	//  */
	// public function render_panel_header( $panel, $id = '', $id_prefix = '' ) {
	// 	$ret = '';
	// 	$id = $id_prefix . $id;
	// 	$class = isset( $panel['class'] ) ? ' ' . $panel['class'] : '';

	// 	if ( isset( $panel['dropdown'] ) ) {

	// 		$ret .=
	// 		'<li role="presentation" class="dropdown tab-dropdown' . $class . '">' .
	// 			'<a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">' .
	// 			 	$panel['name'] .
	// 				'<span class="caret"></span>' .
	// 			'</a>' .
	// 			'<ul class="dropdown-menu">';

	// 		foreach( $panel['options'] as $key => $option ) {
	// 			$ret .=
	// 				'<li><a href="#" data-value="' . $key . '">' . $option . '</a></li>';
	// 		}

	// 		$ret .=
	// 			'</ul>' .
	// 		'</li>';

	// 	} else {
	// 		$ret .=
	// 		'<li class="' . ( isset( $panel['active'] ) ? 'active' : '' ) . $class . '">' .
	// 			'<a href="#' . $id . '" data-toggle="tab">' .
	// 				( isset( $panel['image'] ) ? '<img style="margin-right:5px" src="' . $panel['image'] . '">' : '' ) .
	// 				( isset( $panel['icon'] ) ? '<i style="margin-right:5px" class="fa ' . $panel['icon'] . '"></i>' : '' ) .
	// 				$panel['name'] .
	// 			'</a>' .
	// 		'</li>';
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Renders HTML element
	//  * @param array $data Element data 
	//  * @return string
	//  */
	// public function render_element( $data ) {
	// 	if( ! $data ) {
	// 		return '';
	// 	}

	// 	if( empty( $data['type'] ) ) {
	// 		$data['type'] = 'text';
	// 	}

	// 	$ret = '';

	// 	switch( $data['type'] ) {
	// 		case 'text' :
	// 		case 'number' :
	// 		case 'password' :
	// 		case 'file' :
	// 		case 'hidden' :
	// 			$ret = $this->render_input( $data );
	// 			break;
	// 		case 'select' :
	// 		case 'multiselect' :
	// 			$ret = $this->render_select( $data );
	// 			break;
	// 		case 'button' :
	// 			$ret = $this->render_button( $data );
	// 			break;
	// 		case 'buttongroup' :
	// 			$ret = $this->render_button_group( $data );
	// 			break;
	// 		case 'checkbox' :
	// 			$ret = $this->render_checkbox( $data );
	// 			break;
	// 		case 'inputgroup' :
	// 			$ret = $this->render_input_group( $data );
	// 			break;
	// 		case 'color' :
	// 			$ret = $this->render_color( $data );
	// 			break;
	// 		case 'image' :
	// 			$ret = $this->render_image( $data );
	// 			break;
	// 		case 'elfinder_image' :
	// 			$ret = $this->render_elfinder_image( $data );
	// 			break;
	// 		case 'textarea' :
	// 			$ret = $this->render_textarea( $data );
	// 			break;
	// 		case 'dimension' :
	// 			$ret = $this->render_dimension( $data );
	// 			break;
	// 		case 'lang_set':
	// 			$ret = $this->render_lang_set( $data );
	// 			break;
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Some stuff here 
	//  * @param array $data 
	//  * @return array
	//  */
	// public function fetch_element_data( $data ) {
	// 	$return = array();

	// 	$return['id']          = isset( $data['id'] ) ? htmlentities( $data['id'] ) : '';
	// 	$return['name']        = ! empty( $data['name'] ) ? htmlentities( $this->prefix_name( $data['name'] ) ) : '';
	// 	$return['type']        = isset( $data['type'] ) ? htmlentities( $data['type'] ) : 'text';
	// 	$return['placeholder'] = isset( $data['placeholder'] ) ? htmlentities( $data['placeholder'] ) : '';
	// 	$return['class']       = isset( $data['class'] ) ? htmlentities( $data['class'] ) : '';
	// 	$return['custom_data'] = '';
	// 	$return['css']         = isset( $data['css'] ) ? $data['css'] : '';
	// 	$return['multiple']    = ! empty( $data['multiple'] ) ? ' multiple ' : '';
	// 	$return['values']      = isset( $data['value'] ) ? (array)$data['value'] : array();
	// 	$return['active']      = isset( $data['active'] ) ? (array)$data['active'] : array();
	// 	$return['title']       = isset( $data['title'] ) ? htmlentities( $data['title'] ) : '';

	// 	if ( isset( $data['value' ] ) ) {
	// 		if ( ! is_array( $data['value'] ) ) {
	// 			$return['value'] = htmlentities( $data['value'] );

	// 		} else {
	// 			$return['value'] = $data['value'];
	// 		}

	// 	} else {
	// 		$return['value'] = '';
	// 	}

	// 	if ( isset( $data['custom_data'] ) ) { 
	// 		$custom_data_parts = array();

	// 		preg_match_all( '/([^\'\"=\s]+)=(\'|\")(.+?)\2/s', $data['custom_data'], $m, PREG_SET_ORDER );

	// 		if ( $m ) {
	// 			foreach( $m as $attr ) {
	// 				$custom_data_parts[] = htmlentities( $attr[1] ) . '="' . htmlentities( $attr[3] ) . '"';
	// 			}
	// 		}

	// 		$return['custom_data'] = implode( ' ', $custom_data_parts );
	// 	}

	// 	return $return;
	// }

	// /**
	//  * Renders single input element
	//  * @param Array $data Input data 
	//  * @return void
	//  */
	// public function render_input( $data ) {
	// 	extract( $this->fetch_element_data( $data ) );		

	// 	return 
	// 	'<input
	// 		type="' . $type . '"
	// 		id="' . $id . '"
	// 		name="' . $name . '"
	// 		class="' . $class . '"
	// 		value="' . $value . '"
	// 		placeholder="' . $placeholder . '" ' .
	// 		$custom_data .
	// 	'>';
	// }

	// /**
	//  * Renders single select element
	//  * @param Array $data Select data
	//  * @return void
	//  */
	// public function render_select( $data ) {
	// 	extract( $this->fetch_element_data( $data ) );	
		
	// 	$ret = 
	// 	'<select
	// 		name="' . $name . '"
	// 		id="' . $id . '"
	// 		class="' . $class . '"' .
	// 		$multiple . $custom_data .
	// 		' style="' . $css . '"
	// 	>';

	// 	foreach( $values as $value => $text ) {
	// 		$selected = $this->compare_select_value( $value, $active, true ) ? ' selected="selected"' : '';

	// 		$ret .=
	// 		'<option value="' . htmlentities( $value ) . '"' . $selected . '>' . $text . '</option>';
	// 	}

	// 	$ret .=
	// 	'</select>';

	// 	return $ret;
	// }

	// /**
	//  * Renders single button
	//  * @param Array $data Button data
	//  * @return void
	//  */
	// public function render_button( $data ) {
	// 	extract( $this->fetch_element_data( $data ) );	
	
	// 	$button_type = isset( $data['button_type'] ) ? htmlentities( $data['button_type'] ) : 'default';
	// 	$fixed_width = isset( $data['fixed_width'] ) ? true : false;
	// 	$icon        = isset( $data['icon'] ) ?
	// 		'<i class="fa ' . htmlspecialchars( $data['icon'] ) . ( $fixed_width ? ' fa-fw' : '' ) . '"></i>' : '';

	// 	$text_before = isset( $data['text_before'] ) ?
	// 		'<i>' . $data['text_before'] . '</i>' . ( $icon ? ' ' : '' ) : '';

	// 	$text_after  = isset( $data['text_after'] ) ?
	// 		( $icon ? ' ' : '' ) . '<i>' . $data['text_after'] . '</i>' : '';

	// 	$data_icon = $icon ? ' data-i ="' . htmlentities( $data['icon'] ) . '"' : '';
	// 	$stack_before = isset( $data['stack'] ) ?
	// 		'<span class="fa-stack fa-lg">' .
 //  				'<span class="fa ' . htmlentities( $data['stack'] ) . '"></span>' : '';
 //  		$stack_after = $stack_before ? '</span>' : '';

	// 	$output =
	// 	'<button
	// 		type="' . $type . '"
	// 		id="' . $id . '"
	// 		name="' . $name . '"
	// 		class="btn btn-' . $button_type . ' ' . $class . '"
	// 		style="' . $css . '"
	// 		title="' . $title . '" ' .
	// 		$custom_data . ' ' . $data_icon .
	// 	'>' .
	// 		$text_before .
	// 		$stack_before .
	// 		$icon .
	// 		$stack_after .
	// 		$text_after .
	// 	'</button>';

	// 	return $output;

	// }

	// /**
	//  * Renders single check box element
	//  * @param Array $data Check box data
	//  * @return void
	//  */
	// public function render_checkbox( $data ) {
	// 	extract( $this->fetch_element_data( $data ) );	

	// 	$checked = '';
	// 	if( isset( $data['check_non_empty_value'] ) ) {
	// 		$checked = ! empty( $value ) ? 'checked="checked"' : '';
	// 	}
	// 	$text_on = isset( $data['text_on'] ) ? htmlentities( $data['text_on'] ) : $this->__( 'On' );
	// 	$text_off = isset( $data['text_off'] ) ? htmlentities( $data['text_off'] ) : $this->__( 'Off' );
	// 	$custom_data .= ' data-text-on="' . $text_on . '" data-text-off="' . $text_off . '"';
	// 	$label = ! empty( $data['label'] ) ? htmlentities( $data['label'] ) : ( $checked ? $text_on : $text_off );

	// 	return 
	// 	'<input
	// 		type="checkbox"
	// 		id="' . $id . '"
	// 		name="' . $name . '"
	// 		class="' . $class . '"
	// 		value="' . $value . '" ' . $custom_data . ' ' . $checked .
	// 	'>' .
	// 		( $label ? '<label for="' . $id . '">' . $label . '</label>' : '' );
	// }

	// /**
	//  * Renders Bootstrap form-group
	//  * @param array $data Form-group data
	//  * @return string
	//  */
	// public function render_form_group( $data ) {
	// 	$label = isset( $data['label'] ) ? $data['label'] : '';
	// 	$for = isset( $data['label_for'] ) ? htmlentities( $data['label_for'] ) : '';
	// 	$element = isset( $data['element'] ) ? $data['element'] : '';
	// 	$cols = isset( $data['cols'] ) ? $data['cols'] : ( isset( $this->cols ) ? $this->cols : array( 'col-sm-2', 'col-sm-10', ) );
	// 	$tooltip = isset( $data['tooltip'] ) ? $data['tooltip'] : '';
	// 	$description = isset( $data['description'] ) ? $data['description'] : '';
	// 	$css = isset( $data['css'] ) ? htmlentities( $data['css'] ) : '';
	// 	$feedback = isset( $data['feedback'] ) ? $data['feedback'] : '';
	// 	$has_status = isset( $data['status'] ) ? 'has-' . htmlentities( $data['status'] ) : '';
	// 	$has_feedback = isset( $data['feedback'] ) ? ' has-feedback' : '';
	// 	$class = isset( $data['class'] ) ? ' ' . htmlspecialchars( $data['class'] ) : '';

	// 	$str =
	// 	'<div class="form-group ' . $has_status . $has_feedback . $class . '" style="' . $css . '">';

	// 	if( $label ) {
	// 		$str .=
	// 		'<label for="' . $for . '" class="' . $cols[0] . '">' .
	// 			$label . ' ' . $this->render_popover( $tooltip ) .
	// 		'</label>';
	// 	}

	// 	$str .=
	// 		'<div class="' . $cols[1] . '">' .
	// 			$element .
	// 			'<span class="help-block">' . $description . '</span>' .
	// 		'</div>' .
	// 		$feedback .
	// 	'</div>';

	// 	return $str;
	// }

	// /**
	//  * Renders bootstrap information box
	//  * @param array $info Element data
	//  * @return string
	//  */
	// public function render_info_box( $info ) {
	// 	$ret =
	// 	'<div class="alert alert-info alert-dismissible tip" role="alert">' .
	// 		'<button type="button" class="close" data-dismiss="alert" aria-label="Close">' .
	// 			'<span aria-hidden="true">&times;</span>' .
	// 		'</button>' .
	// 		'<i class="fa fa-info-circle fa-2x tip-icon"></i> ' . $info .
	// 	'</div>';

	// 	return $ret;
	// }

	// /**
	//  * Renders bootstrap tooltip element
	//  * @param string $tooltip Tooltip text 
	//  * @return string
	//  */
	// public function render_tooltip( $tooltip ) {
	// 	if( ! $tooltip ) {
	// 		return '';
	// 	}

	// 	$str =
	// 	'<span
	// 		class="glyphicon"
	// 		data-toggle="tooltip"
	// 		title="' . htmlspecialchars( $tooltip ) . '"
	// 		style="cursor:pointer;"
	// 	>';

	// 	return $str;
	// }

	// /**
	//  * Renders bootstrap popover element
	//  * @param string $text Popover text 
	//  * @return string
	//  */
	// public function render_popover( $content, $title = '' ) {

	// 	if( ! $content && ! $title ) {
	// 		return '';
	// 	}

	// 	$str =
	// 	'<span
	// 		class="fa fa-question-circle popover-icon"
	// 		title="' . htmlspecialchars( $title ) . '"
	// 		data-content="' . htmlspecialchars( $content ) . '"
	// 	>';

	// 	return $str;
	// }

	// /**
	//  * Renders bootstrap input-group element
	//  * @param array $data Element data 
	//  * @return string
	//  */
	// public function render_input_group( $data ) {
	// 	$element = isset( $data['element'] ) ? $data['element'] : '';
	// 	$addon_before = isset( $data['addon_before'] ) ? $data['addon_before'] : '';
	// 	$addon_after = isset( $data['addon_after'] ) ? $data['addon_after'] : '';

	// 	$str =
	// 	'<div class="input-group">' .
	// 		$this->render_addon( $addon_before ) .
	// 		$this->r( $element ) . 
	// 		$this->render_addon( $addon_after ) .
	// 	'</div>';

	// 	return $str;
	// }

	// /**
	//  * Renders bootstrap button-group element
	//  * @param array $data Element data
	//  * @return string
	//  */
	// public function render_button_group( $data ) {
	// 	$str =
	// 	'<div class="btn-group" role="group">';

	// 	foreach( $data['buttons'] as $button ) {
	// 		$str .= $this->r( $button );
	// 	}

	// 	$str .=
	// 	'</div>';

	// 	return $str;
	// }

	// /**
	//  * Renders button addon
	//  * @param array|string $data Addon data
	//  * @return string
	//  */
	// public function render_addon( $data ) {

	// 	if( ! $data ) {
	// 		return '';
	// 	}

	// 	$str = '';

	// 	if( ! is_array( $data ) || empty( $data['type'] ) ) {
	// 		$str .=
	// 		'<span class="input-group-addon">' . $data . '</span>';

	// 	} elseif( 'button' === $data['type'] ) {
	// 		$str .=
	// 		'<span class="input-group-btn">' . $this->r( $data ) . '</span>';

	// 	} elseif ( 'buttons' === $data['type'] ) {
	// 		$str .= '<span class="input-group-btn">';

	// 		foreach( $data['buttons'] as $button ) {
	// 			$str .= $this->r( $button );
	// 		}

	// 		$str .= '</span>';

	// 	} else {
	// 		$str .=
	// 		'<span class="input-group-addon">' . $this->r( $data ) . '</span>';
	// 	}

	// 	return $str;
	// }

	// /**
	//  * Renders 
	//  * @param type $data color-picker element
	//  * @return string
	//  */
	// public function render_color( $data ) {
	// 	$data['type']  = 'text';
	// 	$data['class'] = ( isset( $data['class'] ) ? $data['class'] . ' ' : '' ) . 'form-control';

	// 	$str = $this->render_input_group( array(
	// 		'element'     => $data,
	// 		'addon_after' => '<i class="fa fa-paint-brush"></i>',
	// 		)
	// 	);

	// 	return $str;
	// }

	// /**
	//  * Renders image element
	//  * @param array $data Element data
	//  * @return string
	//  */
	// public function render_image( $data ) {
	// 	extract( $this->fetch_element_data( $data ) );	
	// 	$this->load->model( 'tool/image' );

	// 	$value_path = ! empty( $data['value'] ) ? htmlentities( $data['value'] ) : '';
	// 	$img = $value_path ? $value_path : 'no_image.png';
	// 	$value = htmlentities( $this->model_tool_image->resize( $img, 100, 100 ) );

	// 	$str =
	// 	'<a
	// 		href=""
	// 		id="thumb-' . $id . '"
	// 		data-toggle="image"
	// 		class="img-thumbnail"
	// 		data-original-title=""
	// 		title=""
	// 	>' .
	// 		'<img
	// 			src="' . $value . '"
	// 			alt=""
	// 			title=""
	// 			data-placeholder="' . $value . '"
	// 		>' .
	// 	'</a>' .
	// 	'<input
	// 		class="img-value"
	// 		type="hidden"
	// 		name="' . $name . '"
	// 		value="' . $value_path . '"
	// 		id="' . $id . '"
	// 	>';

	// 	return $str;
	// }


	// /**
	//  * Renders image element facilitated by Elfinder library
	//  * @param array $data Element data
	//  * @return string
	//  */
	// public function render_elfinder_image( $data ) {
	// 	extract( $this->fetch_element_data( $data ) );	
	// 	$this->load->model( 'tool/image' );

	// 	$value_path = ! empty( $data['value'] ) ? htmlentities( $data['value'] ) : '';
	// 	$img = $value_path ? $value_path : 'no_image.png';
	// 	$value = htmlentities( $this->model_tool_image->resize( $img, 120, 120 ) );
	// 	$embed = empty( $data['embed_value'] ) ? 0 : 1;
	// 	$uid = uniqid();

	// 	// One level up
	// 	$embed_name = preg_replace( '/\[[^]]+\]$/', '[embed]', $name );
	// 	if( ! $embed_name ) {
	// 		$embed_name = '';
	// 	}

	// 	$str =
	// 	'<a href="#" class="elfinder ' . ( $embed ? 'embedded' : 'attached' ) . ( empty( $value_path ) ? ' removing' : '' ) . '" data-key="' . $uid . '">' .
	// 		'<img src="' . $value . '"' .'data-placeholder="' . $value . '" style="width: 120px; height: auto;">' .
	// 		'<input type="hidden" name="' . $name . '" value="' . $value_path . '" id="' . $id . '" data-key="' . $uid . '">' .
	// 		'<input type="hidden" name="' . $embed_name . '" value="' . $embed . '" class="embed-input">' .
	// 		'<span class="disposition-name">' .
	// 			'<span class="disposition-embedded"><i>' . $this->__( 'Embedded' ) . '</i></span>' .
	// 			'<span class="disposition-attached"><i>' . $this->__( 'Attached' ) . '</i></span>' .
	// 		'</span>' .
	// 		'<i class="fa fa-close fa-fw remove-image"></i>' .
	// 	'</a>';

	// 	return $str;
	// }

	// /**
	//  * Renders single input element
	//  * @param Array $data Input data 
	//  * @return void
	//  */
	// public function render_textarea( $data ) {
	// 	extract( $this->fetch_element_data( $data ) );	

	// 	$row = isset( $data['row'] ) ? htmlentities( $data['row'] ) : 3;

	// 	return 
	// 	'<textarea id="' . $id . '" name="' . $name . '" rows="' . $row . '" ' .
	// 		'class="' . $class . '" placeholder="' . $placeholder . '" ' .
	// 		$custom_data . '>' .
	// 		htmlspecialchars_decode( $value ) .
	// 	'</textarea>';
	// }

	// /**
	//  * Renders fancy checkbox element
	//  * @since 1.1.0
	//  * @param array $data 
	//  * @return string
	//  */
	// public function render_fancy_checkbox( $data ) {
	// 	extract( $this->fetch_element_data( $data ) );	

	// 	$value_on = isset( $data['value_on'] ) ? htmlentities( $data['value_on'] ) : 1;
	// 	$value_off = isset( $data['value_off'] ) ? htmlentities( $data['value_off'] ) : 0;
	// 	$value = isset( $data['value'] ) && $data['value'] == $value_on ? $value_on : $value_off;
	// 	$text_on = isset( $data['text_on'] ) ? htmlentities( $data['text_on'] ) : $this->__( 'On' );
	// 	$text_off = isset( $data['text_off'] ) ? htmlentities( $data['text_off'] ) : $this->__( 'Off' );
	// 	$dependent_on = isset( $data['dependent_on'] ) ? htmlentities( $data['dependent_on' ] ) : '';
	// 	$dependent_off = isset( $data['dependent_off'] ) ? htmlentities( $data['dependent_off' ] ) : '';

	// 	$ret =
	// 	'<input
	// 		type="hidden"
	// 		name="' . $name . '"
	// 		id="' . $id . '"
	// 		class="fancy-checkbox ' . $class . '"
	// 		value="' . $value . '"
	// 		data-text-on="' . $text_on . '"
	// 		data-text-off="' . $text_off . '"
	// 		data-value-on="' . $value_on . '"
	// 		data-value-off="' . $value_off . '"
	// 		data-dependent-on="' . $dependent_on . '"
	// 		data-dependent-off="' . $dependent_off . '"
	// 		' . $custom_data .
	// 	'>';

	// 	return $ret;
	// }

	// /**
	//  * Renders dimensions form control
	//  * @since 1.1.0
	//  * @param array $data  Control data
	//  * @return string
	//  */
	// public function render_dimension( $data ) {
	// 	extract( $this->fetch_element_data( $data ) );	

	// 	$values = isset( $data['values'] ) ? htmlentities( $data['values'] ) : 'px,%';
	// 	$texts  = isset( $data['texts'] ) ? htmlentities( $data['texts'] ) : 'px,%';
	// 	$titles = isset( $data['titles'] ) ? htmlentities( $data['titles'] ) :
	// 		$this->helper->__( 'Width measured in pixels' ) . ',' .
	// 		$this->helper->__( 'Width measured in percentage of available width' );

	// 	$maxes  = isset( $data['maxes'] ) ? htmlentities( $data['maxes'] ) : '2000,100';
	// 	$value = empty( $value ) ? 0 : $value;
	// 	$units = isset( $data['units'] ) ? htmlentities( $data['units'] ) : 'px';
	// 	$max = isset( $data['max'] ) ? 'data-max="' . htmlentities( $data['max'] ) . '"' : '';

	// 	$str =
	// 	'<div class="dimension-wrapper" ' . $custom_data . '>' .
	// 		'<div class="dimension-slider-wrapper">' .
	// 			'<div id="" class="slider" data-value1="' . $value . '" ' .
	// 				'data-value1-target="#' . $id . '-value"' .
	// 				$max .
	// 			'>' .
	// 			'</div>' .
	// 		'</div>' .
	// 		'<div class="dimension-input-gr-wrapper">' .
	// 			$this->helper->render_element( array(
	// 				'type'    => 'inputgroup',
	// 				'element' => array(
	// 					'type'  => 'text',
	// 					'id'    => $id . '-value',
	// 					'name'  => $name ? $name . '[value]' : '',
	// 					'value' => $value,
	// 					'css'   => 'width:80px',
	// 					'class' => 'form-control',
	// 				),
	// 				'addon_after' => array(
	// 					'type'        => 'button',
	// 					'id'          => $id . '-units',
	// 					'name'        => $name ? $name . '[units]' : '',
	// 					'text_before' => $units,
	// 					'custom_data' => 'data-values="' . $values . '"
	// 										data-texts="' . $texts . '"
	// 										data-value="' . $units . '"
	// 										data-titles="' . $titles . '"
	// 										data-maxes="' . $maxes . '"
	// 										data-toggle="tooltip"',

	// 					'class'       => 'switchable measure-units',
	// 				),
	// 			) ) .
	// 			'</div>' .
	// 	'</div>';

	// 	return $str;
	// }

	// /**
	//  * Renders input field for each store languages
	//  * @param Array $data Input data 
	//  * @return void
	//  */
	// public function render_lang_set( $data ) {
	// 	$ret = '';
	// 	$languages = $this->get_languages();
	// 	$admin_lang = $this->config->get( 'config_admin_language' );
	// 	$id = uniqid();
	// 	$d = $data['element'];
	// 	$name = isset( $data['element']['name'] ) ? $data['element']['name'] : '';
	// 	$key = isset( $data['key'] ) ? $data['key'] : '';
	// 	$default_value = isset( $data['element']['value'] ) ? $data['element']['value'] : '';

	// 	if ( count( $languages ) > 1 ) {
	// 		$ret .= '<ul class="nav nav-tabs" role="tablist">';

	// 		foreach( $languages as $language ) {
	// 			$a_c = $admin_lang === $language['code'] ? 'active' : '';

	// 			$ret .= '<li role="presentation" class="' . $a_c . '">' .
	// 						'<a href="#caption-' . $id . '-' . $language['code'] . '" role="tab" data-toggle="tab">' .
	// 							'<img src="' . $this->get_lang_flag_url( $language ) . '">' .
	// 						'</a>' .
	// 					'</li>';
	// 		}

	// 		$ret .= '</ul>';
	// 		$ret .= '<div class="tab-content">';

	// 		foreach( $languages as $language ) {
	// 			$a_c = $admin_lang === $language['code'] ? 'active' : '';
	// 			$d['name'] = $name ? $name . '[' . $language['code'] . ']' : '';
	// 			$d['value'] = $this->get_lang_caption(
	// 				! empty( $key ) ? $key : $name,
	// 				$language['code'],
	// 				$default_value
	// 			);

	// 			$ret .= '<div id="caption-' . $id . '-' . $language['code'] . '" class="tab-pane ' . $a_c . '" >';
	// 			$ret .= $this->r( $d );
	// 			$ret .= '</div>';	
	// 		}

	// 		$ret .= '</div>';
			
	// 	} else {
	// 		$language = current( $languages );
	// 		$d['name'] = $name ? $name . '[' . $language['code'] . ']' : '';
	// 		$d['value'] = $this->get_lang_caption(
	// 			! empty( $key ) ? $key : $name,
	// 			$language['code'],
	// 			$default_value
	// 		);
	// 		$ret .= $this->r( $d );
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Returns localized caption
	//  * @param string $key Caption key
	//  * @param string $lang_code Language code
	//  * @param string $default Optional default value
	//  * @return string
	//  */
	// public function get_lang_caption( $key, $lang_code = null, $default = '' ) {
	// 	$ret = null;
	// 	$conf = $this->config( $key );

	// 	if ( is_null( $lang_code ) ) {
	// 		if ( isset( $this->session->data['language'] ) ) {
	// 			$lang_code = $this->session->data['language'];

	// 		} else {
	// 			$lang_code = $this->congif->get( 'config_admin_language' );
	// 		}
	// 	}

	// 	if ( is_array( $conf ) ) {
	// 		if ( isset( $conf[ $lang_code ] ) ) {
	// 			$ret = $conf[ $lang_code ];

	// 		} else {
	// 			$def_lang_code = $this->config->get( 'config_admin_language' );

	// 			if ( isset( $conf[ $def_lang_code ] ) ) {
	// 				$ret = $conf[ $def_lang_code ];
	// 			}
	// 		}

	// 	} else {
	// 		$ret = $conf;
	// 	}

	// 	if ( is_null( $ret ) ) {
	// 		$ret = $default;
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Get value for admin area input control element
	//  * Checks firstly POST, then tries get configuration value
	//  * @param String $name 
	//  * @param Mixed $default Default value
	//  * @return Mixed
	//  */
	// public function get_value_from_post( $name , $default = '' ) {

	// 	if ( isset( $this->request->post[ $this->prefix_name( $name ) ] ) ) {
	// 		return $this->request->post[ $this->prefix_name( $name ) ];
	// 	}

	// 	$conf_value = $this->config->get( $this->prefix_name( $name ) );

	// 	if ( $conf_value ) {
	// 		return $conf_value;
	// 	}

	// 	return $default;
	// }

	/**
	 * Defines if $value belongs to select's values
	 * @param String $value Value to search for
	 * @param Array $select_value Select element values
	 * @return Boolean
	 */
	// public function compare_select_value( $value, $select_value ) {

	// 	if( empty( $value ) || empty( $select_value ) ) {
	// 		return false;
	// 	}

	// 	foreach( (array)$select_value as $sv ) {

	// 		if( in_array( gettype( $sv ), array( 'array', 'object', 'resource' ) ) ) {
	// 			continue;
	// 		}

	// 		if( $value == $sv ) {
	// 			return true;
	// 		}
	// 	}

	// 	return false;
	// }

	// /**
	//  * Fetch variable from POST
	//  * @param String $name Variable name
	//  * @param Mixed|null $default Default variable
	//  * @return Mixed
	//  */
	// public function post( $name, $default = null ) {

	// 	if( ! empty( $this->request->post[ $this->prefix_name( $name ) ] ) ) {
	// 		return $this->request->post[ $this->prefix_name( $name ) ];
	// 	}

	// 	return $this->p( $name, $default );
	// }

	// /**
	//  * Fetch unprefixed variable from POST
	//  * @param String $name Variable name
	//  * @param Mixed|null $default Default variable
	//  * @return Mixed
	//  */
	// public function p( $name, $default = null ) {

	// 	if( isset ( $this->request->post[ $name ] ) ) {
	// 		return $this->request->post[ $name ];
	// 	}

	// 	return $default;
	// }

	// /**
	//  * Fetch unprefixed variable from REQUEST
	//  * @param String $name Variable name
	//  * @param Mixed|null $default Default variable
	//  * @return Mixed
	//  */
	// public function r( $name, $default = null ) {

	// 	if( isset ( $this->request->request[ $name ] ) ) {
	// 		return $this->request->request[ $name ];
	// 	}

	// 	return $default;
	// }

	// /**
	//  * Makes extension specific (prefixed) names
	//  * @param String $name 
	//  * @return String
	//  */
	// public function prefix_name( $name ) {
	// 	return $this->code . '-' . $name;
	// }

	// /**
	//  * Strips extension specific prefix from name
	//  * @param string $name 
	//  * @return string
	//  */
	// public function strip_prefix( $name ) {
	// 	if( strpos( $name, $this->code . '-' ) === 0 ) {
	// 		$name = substr( $name , strlen( $this->code . '_' ) );
	// 	}

	// 	return $name;
	// }

	// /**
	//  * Throws general package exception
	//  * @param String|null $message Error message.
	//  * @throws Adk_Exception
	//  * @return void
	//  */
	// public function exception( $message = null ) {
	// 	if( $message ) {
	// 		throw new Adk_Exception( $message );
	// 	}
	// }

	// /**
	//  * Throws form field exception
	//  * @param null|String $message Error message.
	//  * @param type $field_name Field name with error
	//  * @throws Adk_Form_Exception
	//  * @return void
	//  */
	// public function form_exception( $message = null, $field_name = null ) {
	// 	if( $message ) {
	// 		throw new Adk_Form_Exception( $message, $field_name );
	// 	}
	// }

	// /**
	//  * Open socket connection
	//  * @param String $address URL
	//  * @param string $method Method (Default - HEAD)
	//  * @param Integer $folow_redirects Maximum redirects count
	//  * @param Array $data Data to be send
	//  * @param integer $timeout Connection timeout
	//  * @return Array
	//  */
	// public function socket( $address, $method = 'HEAD', $folow_redirects = 5, $data = array(), $timeout = 10 ) {

	// 	$this->error_prefix[] = '[Socket]';
	// 	$this->debug_prefix[] = '[Socket]';

	// 	$output = array();

	// 	if( is_null( $method ) ) {
	// 		$method = 'HEAD';
	// 	}

	// 	if( is_null( $folow_redirects ) ) {
	// 		$folow_redirects = 5;
	// 	}

	// 	if( is_null( $data ) ) {
	// 		$data = array();
	// 	}

	// 	if( is_null( $timeout ) ) {
	// 		$timeout = 10;
	// 	}

	// 	try{
	// 		do {
	// 			$socket = $this->socket_create( $address, $timeout );
	// 			$this->socket_write( $socket, $address, $method, $data, $timeout );
	// 			$output = $this->socket_read( $socket, $method , $timeout);

	// 			fclose( $socket );

	// 			$code = isset( $output['code'] ) ? $output['code'] : null;
	// 			$location = isset( $output['headers']['Location'] ) ?
	// 				$output['headers']['Location'] : '';

	// 			if( $code == 302 || $code == 301 ) {
	// 				$this->debug( sprintf( 'Redirect to %s detected, redirect counts remain: %s', $location, $folow_redirects ) );
	// 			}

	// 			if( $folow_redirects > 0 && ( $code == 302 || $code == 301 ) && $location ) {
	// 				$address = $location;
	// 				$folow_redirects--;

	// 			} else {
	// 				$folow_redirects = 0;
	// 			}

	// 		} while( $folow_redirects > 0 );


	// 	} catch ( Adk_Exception $e ) {

	// 	} catch ( Exception $e ) {
	// 		trigger_error( $this->error_prefix() . $e->getMessage() );
	// 	}

	// 	if( isset( $socket ) && 'resource' === gettype( $socket ) ) {
	// 		fclose( $socket );
	// 	}

	// 	array_pop( $this->error_prefix );
	// 	array_pop( $this->debug_prefix );

	// 	return $output;
	// }

	// /**
	//  * Creates socket
	//  * @param string $address URL
	//  * @param integer $timeout Connection timeout
	//  * @throws Adk_Exception
	//  * @return Resource
	//  */
	// protected function socket_create( $address, &$timeout) {

	// 	$start = time();

	// 	$this->debug( 'Socket create start' );

	// 	$protocol = 'http';
	// 	$transport = 'tcp';

	// 	if( ! filter_var( $address, FILTER_VALIDATE_IP ) ) {

	// 		$this->debug( 'Address is URL' );

	// 		$components = $this->parse_url( $address );

	// 		$this->debug( 'Address components:' );
	// 		$this->debug( $components );

	// 		if( ! $components['host'] ) {
	// 			trigger_error( sprintf( $this->error_prefix() . 'Unable to parse url %s', $address ) );
	// 			$this->exeption( 'error' );
	// 		}

	// 		$address = $components['host'];

	// 		if( $components['scheme'] ) {
	// 			$protocol = $components['scheme'];

	// 			if( 'https' === strtolower( $protocol ) ) {
	// 				$transport = 'ssl';
	// 			}
 // 			}

	// 	}

	// 	$port = getservbyname( $protocol, 'tcp' );

	// 	$this->debug( 'Transport: ' .$transport );
	// 	$this->debug( 'Protocol: ' . $protocol );
	// 	$this->debug( 'Port: ' . $port );

	// 	if( false === ( $socket = @stream_socket_client( "$transport://$address:$port", $errno, $errstr, $timeout ) ) ) {
	// 		$this->debug( sprintf( 'Unable to create socket connection to :%s', "$transport://$address:$port" ) );
	// 		$this->exception( 'error' );
	// 	}

	// 	$this->debug( 'Socket successfully created' );
	// 	$this->debug( 'Socket create end' );

	// 	$timeout = $timeout - ( time() - $start );

	// 	return $socket;
	// }

	// /**
	//  * Write data into socket
	//  * @param Resource $socket Socket descriptor
	//  * @param String $address URL
	//  * @param string $method Method
	//  * @param array $data Data to send
	//  * @return boolean
	//  */
	// protected function socket_write( $socket, $address, $method, $data, &$timeout ) {

	// 	$start = time();

	// 	$res = false;

	// 	$this->debug( 'Socket data write start' );

	// 	$components = $this->parse_url( $address );
	// 	$path = $components['path'] .
	// 		( ! empty( $components['query'] ) ? '?' . $components['query'] : '' ) .
	// 		( ! empty( $components['fragment'] ) ? '#' . $components['fragment'] : '' );

	// 	$in = "$method $path HTTP/1.1\r\n";
	// 	$in .= "Host: {$components['host']}\r\n";
	// 	$in .= "Connection: Close\r\n\r\n";


	// 	$this->debug( 'Data to write into socket:' );
	// 	$this->debug( $in );

	// 	$write_res = fwrite( $socket, $in );

	// 	if( $write_res === strlen( $in ) ) {
	// 		$this->debug( 'Data have written successfully' );
	// 		$res = true;
	// 	}

	// 	$timeout = $timeout - ( time() - $start );

	// 	if( ! $res ) {
	// 		$this->debug( 'Can not write into socket' );
	// 	}

	// 	return $res;
	// }

	// /**
	//  * Read data from socket
	//  * @param Resource $socket Socket descriptor
	//  * @throws Adk_Exception
	//  * @return Array
	//  */
	// protected function socket_read( $socket, $method, $timeout ) {

	// 	$block_size = 8192;
	// 	$output = '';
	// 	$ret = '';
	// 	$content_length = -1;
	// 	$read_usleep  = 10000;
	// 	$read_max_count = floor( ( 1000000 * $timeout ) / $read_usleep );

	// 	stream_set_blocking( $socket, 0 );

	// 	$this->debug( sprintf( 'Start to read data from socket by %s chunks', $block_size ) );

	// 	$read_count = 0;
	// 	$read_start = time();

	// 	while ( ! feof( $socket ) ) {

	// 		usleep( $read_usleep );
	// 		$output .= fread( $socket, $block_size );

	// 		// Get HTTP headers
	// 		if( ! $ret && ( $pos = strpos( $output, "\r\n\r\n" ) ) !== false ) {

	// 			$ret = $this->parse_http_header( substr( $output, 0, $pos ) );

	// 			if( isset( $ret['headers']['Connection'] ) &&
	// 				'close' === strtolower( $ret['headers']['Connection'] ) ) {

	// 				if( strtolower( $method ) === 'head' ) {
	// 					$content_length = 0;
	// 				} elseif ( isset( $ret['headers']['Content-Length'] ) ) {
	// 					$content_length = $ret['headers']['Content-Length'];
	// 				} else {
	// 					$content_length = 0;
	// 				}
	// 			}

	// 			$output = substr( $output, $pos + 4 );
	// 		}

	// 		// If we got close connection header - check to close connection
	// 		if( -1 !== $content_length && strlen( $output ) >= $content_length ) {
	// 			$this->debug( sprintf( 'Close connection, content length %s', $content_length ) );
	// 			break;
	// 		}

	// 		if( time() - $read_start >= $timeout ) {
	// 			$this->debug( sprintf( 'Data read partly - exceeded read timeout of %s sec', $timeout ) );
	// 			break;
	// 		}

	// 		if( $read_count >= $read_max_count ) {
	// 			$this->debug( sprintf( 'Data read partly - exceeded %d read counts', $read_max_count ) );
	// 			break;
	// 		}

	// 		$read_max_count++;
	// 	}

	// 	$ret['body'] = $output;

	// 	$this->debug( 'Socket output:' );
	// 	$this->debug( $output );

	// 	return $ret;
	// }

	// /**
	//  * Parses HTTP response to array
	//  * @param String $response 
	//  * @return Array
	//  */
	// public function parse_http_header( $header_str ) {

	// 	$header = str_replace( "\r", '', $header_str );
	// 	$h = array();
	// 	$ret = array();

	// 	$headers = explode( "\n", $header );

	// 	foreach( $headers as $header ) {

	// 		$header = trim( $header );

	// 		if( empty( $ret['code'] ) && 'HTTP' === strtoupper( substr( $header, 0, 4 ) ) ) {

	// 			preg_match( '/^http[^ ]+\s+(\d+)\s+(.+)/i', $header, $m );

	// 			if( isset( $m[1] ) ) {
	// 				$ret['code'] = $m[1];
	// 			}

	// 			if( isset( $m[2] ) ) {
	// 				$ret['code_descr'] = $m[2];
	// 			}

	// 			continue;
	// 		}

	// 		$parts = explode( ': ', $header );

	// 		if( isset( $parts[0] ) && isset( $parts[1] ) &&
	// 			( $p1 = trim( $parts[0] ) ) && ( $p2 = trim( $parts[1] ) ) ) {

	// 			$h[ $p1 ] = $p2;
	// 		}
	// 	}

	// 	$ret['headers'] = $h;

	// 	return $ret;
	// }

	// /**
	//  * Parses URL
	//  * @param String $url 
	//  * @return Array
	//  */
	// public function parse_url( $url ) {
	// 	$ret = array(
	// 			'scheme'   => '',
	// 			'host'     => '',
	// 			'port'     => '',
	// 			'path'     => '',
	// 			'query'    => '',
	// 			'fragment' => '',
	// 		);

	// 	if( ! $url || gettype( $url ) !== 'string' ) {
	// 		return $ret;
	// 	}

	// 	$preg_str = '%' .
	// 				'(?:(^[^/:]*?)(?=://))?' . // Scheme
	// 				'(?::?/{2})?' .
	// 				'([^/:?]+)?' . // Host
	// 				':?' .
	// 				'(?:(?<=:)(\d+))?' . // Port
	// 				'([^?]+)?' . // Path
	// 				'\??' .
	// 				'(?:(?<=\?)([^#]+))?' . // Query
	// 				'#?' .
	// 				'(?:(?<=#)(.*))?' . // Fragment
	// 				'%';

	// 	if( preg_match( $preg_str, $url, $m ) ) {

	// 		if( isset( $m[1] ) ) {
	// 			$ret['scheme'] = $m[1];
	// 		}

	// 		if( isset( $m[2] ) ) {
	// 			$ret['host'] = $m[2];
	// 		}

	// 		if( isset( $m[3] ) ) {
	// 			$ret['port'] = $m[3];
	// 		}

	// 		if( ! empty( $m[4] ) ) {
	// 			$ret['path'] = $m[4];
	// 		} else {
	// 			$ret['path'] = '/';
	// 		}

	// 		if( isset( $m[5] ) ) {
	// 			$ret['query'] = $m[5];
	// 		}

	// 		if( isset( $m[6] ) ) {
	// 			$ret['fragment'] = $m[6];
	// 		}
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Normalizes URL
	//  * @param String|Array $url URL to be normalized
	//  * @return String
	//  */
	// public function normalize_url( $url ) {

	// 	if ( ! in_array( gettype( $url ), array( 'array', 'string' ) ) ) {
	// 		return $url;
	// 	}

	// 	if ( ! is_array( $url ) ) {
	// 		$url = $this->parse->url( $url );
	// 	}

	// 	$ret = 
	// 	( empty( $url['scheme'] ) ? '//' : $url['scheme'] ) . '://' .
	// 	( empty( $url['host'] ) ? $this->request->server['SERVER_NAME'] : $url['host'] ) .
	// 	( empty( $url['port'] ) ? '' : ':' . $url['port'] ) .
	// 	$url['path'] .
	// 	( empty( $url['query'] ) ? '' : '?' . $url['query'] ) .
	// 	( empty( $url['fragment'] ) ? '' : '#' . $url['fragment'] );

	// 	return $ret;
	// }

	// /**
	//  * Prints debug massage to error log
	//  * @param String|Mixed $msg Debug message
	//  * @return void
	//  */
	// public function debug( $msg ) {
	// 	if( $this->debug_enabled ) {
	// 		if( gettype( $msg ) === 'string' ) {
	// 			$this->log->write( implode( '', $this->debug_prefix ) . ': ' . $msg );

	// 		} else {
	// 			$this->log->write( $msg );
	// 		}
	// 	}
	// }

	// /**
	//  * Return error prefix to use for error log file
	//  * @return String
	//  */
	// public function error_prefix() {
	// 	return implode( '', $this->error_prefix ) . ': ';
	// }

	// /**
	//  * Try to construct file path up from the root
	//  * If fails - returns FALSE
	//  * @param String $file File path
	//  * @return Boolean|String
	//  */
	// public function plant_file( $file ) {

	// 	if( 'string' !== gettype( $file ) ) {
	// 		return false;
	// 	}

	// 	$base = dirname( DIR_SYSTEM );
	// 	$file_path = $file;

	// 	// Windows
	// 	if( substr( PHP_OS, 0, 3 ) === 'WIN' ) {

	// 		// Find driver letter for file path (if applied)
	// 		if( preg_match( '/^([a-zA-Z]:\\\\?|(\\\\){2})/', $file, $m ) ) {
	// 			$dir_letter = $m[0];
	// 			$file = substr( $file, strlen( $dir_letter ) );
	// 		}

	// 		// Find driver letter for store base directory
	// 		if( preg_match( '/^([a-zA-Z]:\\\\?|(\\\\){2})/', $base, $m ) ) {
	// 			$dir_base_letter = $m[0];
	// 			$base = substr( $base, strlen( $dir_base_letter ) );
	// 		} else {
	// 			$dir_base_letter = '\\\\';
	// 		}

	// 		// File specified from the root explicitly
	// 		if( preg_match( '/^([a-zA-Z]:\\\\|(\\\\){2})/', $file_path, $m ) ) {

	// 			$same_dir_letter = strtolower( $dir_letter ) === strtolower( $dir_base_letter );

	// 			// Same driver letters or one file/base has UNC path format
	// 			if( $same_dir_letter || $dir_base_letter === '\\\\' || $dir_letter === '\\\\' ) {
	// 				if( strpos( strtolower( $file ), strtolower( $base ) ) === 0 ) {

	// 					return $dir_base_letter . $file;
	// 				} else {

	// 					return false;
	// 				}
	// 			} else {

	// 				return false;
	// 			}
	// 		}

	// 	// Right OS
	// 	} else {
	// 		$dir_base_letter = '/';
	// 	}

	// 	$ds = DIRECTORY_SEPARATOR;
	// 	$base_parts = explode( $ds, trim( $base, $ds ) );
	// 	$file_parts = $fp = explode( $ds, trim( $file, $ds ) );
	// 	$root_parts = array();
	// 	$count = 0;

	// 	// Iterate over path parts to check
	// 	// whether file path have subsequent entries in store base path.
	// 	foreach( $base_parts as $part ) {
	// 		if( $part === $file_parts[0] ) {
	// 			array_shift( $file_parts );
	// 		} else {
	// 			if( count( $root_parts ) !== $count ) {

	// 				return false;
	// 			}
	// 			$root_parts[] = $part;
	// 		}
	// 		$count++;
	// 	}

	// 	if( count( $root_parts ) === count( $base_parts ) ) {
	// 		return false;
	// 	}

	// 	$fp = array_merge( $root_parts, $fp );

	// 	return $dir_base_letter . implode( DIRECTORY_SEPARATOR, $fp );
	// }

	// /**
	//  * Checks whether file above store root
	//  * @param String $path 
	//  * @return Boolean|Null
	//  */
	// public function above_store_root( $path ) {
	// 	$file = realpath( $path );
	// 	$base = dirname( DIR_SYSTEM );

	// 	if( ! $file ) {
	// 		return null;
	// 	}

	// 	return strpos( $file, $base . DIRECTORY_SEPARATOR ) !== 0;
	// }

	// /**
	//  * Returns yes/no option for select input element
	//  * @return array
	//  */
	// public function yes_no() {
	// 	return array(
	// 		$this->__( 'No' ),
	// 		$this->__( 'Yes' ),
	// 		);
	// }

	/**
	 * Initializes OC Mail object with store mail configuration
	 * @param object $mail Mail object
	 * @param array $data Additional parameters
	 * @return void
	 */
	public function init_mail( $mail, $data = array() ) {
		$mail->setSender( $this->config->get( 'config_email' ) );
		$mail->setFrom( $this->config->get( 'config_email' ) );

		if ( version_compare( VERSION, '2.0.1.1', '<=' ) ) {
			foreach( $this->config->get('config_mail') as $k => $v ) {
				$mail->{$k} = $v;
			}

		} else {
			$mail->protocol = $this->config->get( 'config_mail_protocol' );

			if( 'smtp' === $mail->protocol ) {
				$mail->smtp_hostname = $this->config->get( 'config_mail_smtp_hostname' );
				$mail->smtp_username = $this->config->get( 'config_mail_smtp_username' );
				$mail->smtp_password = html_entity_decode( $this->config->get( 'config_mail_smtp_password' ), ENT_QUOTES, 'UTF-8' );

				if( $this->config->has( 'config_mail_smtp_port' ) ) {
					$mail->smtp_port = $this->config->get( 'config_mail_smtp_port' );
				}

				if( $this->config->has( 'config_mail_smtp_timeout' ) ) {
					$mail->smtp_timeout = (float)$this->config->get( 'config_mail_smtp_timeout' );
				}

			} else {
				$mail->parameter = $this->config->get( 'config_mail_parameter' );
			}
		}

		if( $data ) {
			foreach( $data as $k => $v ) {
				$method = 'set' . ucfirst( $k );
				if( method_exists( $mail, $method ) ) {
					call_user_func( array( $mail, $method ), $v );
				}
			}
		}
	}

	/**
	 * Returns array value
	 * @param string $name Array value name in form of name1/name2/name3
	 * @param array $array Array to search value in 
	 * @return mixed Searched value
	 */
	// public function get_recursive_from_array( $name, $array ) {
	// 	$ret = $array;
	// 	if( ! $name || gettype($name ) !== 'string' ) {
	// 		return $ret;
	// 	}

	// 	foreach( explode( '/', $name ) as $part ) {
	// 		if( isset( $ret[ $part ] ) ) {
	// 			$ret = $ret[ $part ];
	// 		} else {
	// 			return null;
	// 		}
	// 	}
		
	// 	return $ret;
	// }

	// *
	//  * Recursively converts object to array
	//  * @param object $object Target object 
	//  * @return array
	 
	// public function object_to_array( $object ) {
	// 	if( gettype( $object ) === 'array' ) {
	// 		foreach( $object as &$o ) {
	// 			$o = $this->object_to_array( $o );
	// 		}

	// 	} elseif( gettype( $object ) === 'object' ) {
	// 		$object = $this->object_to_array( (array)$object );
	// 	}

	// 	return $object;
	// }

	/**
	 * Fixes JSON/MySQL issue with unicode sequences - adds backslashes, removed by MyQSL parser
	 * @param string $string JSON sting 
	 * @return string Fixed JSON string
	 */
	// public function fix_json_string( $string ) {
	// 	$string = preg_replace( '/(?<!\\\)(u[0-9a-f]{4})/', '\\\$1', $string );

	// 	return $string;
	// }

	/**
	 * Sends email message
	 * @param array $data Email data 
	 * @return boolean
	 */
	public function send_email( $data ) {
		$mail = new Mail();
		$this->init_mail( $mail, $data );

		return $mail->send();
	}

	/**
	 * Renders mail template
	 * @param int $template_id Mail template ID
	 * @param int|null $profile Profile ID
	 * @param int|null $store_id Store ID
	 * @param string|null $lang_id Language code
	 * @return string
	 */
	public function render_mail_template( $template_id, $profile = null, $store_id = null, $lang_id = null ) {
		if( ! isset( $template_id ) ) {
			trigger_error( 'Template ID is missing' );
			return '';
		}

		$template_content = $this->get_mail_template( $template_id );

		// Email contain sensitive data 
		if ( in_array( $template_content['hook'], array( 'admin.forgotten', 'customer.forgotten' ) ) ) {
			$this->private_template = true;
		}

		if( ! $template_content ) {
			trigger_error( sprintf( 'Data for template #%s are missing', $template_id ) );
			return '';
		}

		if( is_null( $store_id ) ) {
			$store_id = $this->get_store();
		}

		if( is_null( $lang_id ) ) {
			$lang_id = $this->get_lang();
		}

		if( is_null( $profile ) ) {
			$profile = $this->get_template_profile( $store_id, $lang_id, $template_id );
		}

		if( ! $profile ) {
			trigger_error(
				sprintf(
					'Unable fetch profile for template with ID #%s, store #%s, language %s',
					$profile_id,
					$store_id,
					$lang_id
				)
			);
		}

		$content = '';
		$tpl_file = ( defined( 'DIR_CATALOG' ) ? DIR_CATALOG : DIR_APPLICATION ) .
			'view/theme/default/template/mail/adk_' . $profile['profile' ] . '.tpl';

		if( file_exists( $tpl_file ) ) {
			extract( $profile['data'] );
			ob_start();
			include $tpl_file;
			$content = ob_get_clean();

			// Inline CSS
			if( 'textual' !== $profile['profile'] ) {
				require_once( __DIR__ . '/Emogrifier.php' );
				$emogi = new \Pelago\Emogrifier();
				$emogi->enableCssToHtmlMapping();
				$emogi->setHtml( $content );
				$content = $emogi->emogrify();
			}

		} else {
			trigger_error( sprintf( 'Mail template "%s" doesn\'t exist', $tpl_file ) );
		}

		$this->adk_template = $template_content;
		$content = $this->add_visit_track_code( $content );
		$this->adk_template = null;
		$this->private_template = false;

		return $content;
	}

	/**
	 * Converts HTML email template to textual representation
	 * @param string $html HTML template 
	 * @return string
	 */
	public function html_to_text( $html ) {
		$html = $this->fetch_text_variants( $html );
		require_once( __DIR__ . '/html2text' );
		$html2text = new \html2text( $html );

		return $html2text->get_text();
	}

	/**
	 * Returns tracking pixel image
	 * @param int $template_id  Template ID
	 * @return string
	 */
	public function get_tracking_pixel( $template_id ) {
		if ( ! $this->get_configuration( 'track', $template_id ) ) {
			return '';
		}

		$this->tracking_id = uniqid();

		return sprintf(
			'<img src="%s&email_id=%s" width="1px" height="1px" style="float: left;" />',
			$this->get_store_url() . '?route=' . $this->type . '/' . $this->code . '/track',
			$this->tracking_id
		); 
	}

	/**
	 * Marks email as viewed
	 * @param string $track_id Tracking code 
	 * @return void
	 */
	public function mark_as_viwed( $track_id ) {
		$this->q( array(
			'table' => $this->history_table,
			'where' => array(
				'field'     => 'tracking_id',
				'operation' => '=',
				'value'     => $track_id,
			),
			'query' => 'update',
			'set'   => array(
				'date_viewed' => 'now()',
			),
		) );
	}

	/**
	 * Returns current store ID
	 * @return int
	 */
	public function get_store() {
		$store_id = $this->config->get( 'config_store_id' );
		if( is_null( $store_id ) ) {
			$store_id = 0;
		}

		return $store_id;
	}

	/**
	 * Returns code for current language, on fail bounces to English
	 * @return string
	 */
	public function get_current_lang() {
		if( isset( $this->session->data['language'] ) ) {
			return $this->session->data['language'];
		}

		$lang_code = $this->get_language( 'en' );

		if( isset( $lang_code['code'] ) ) {
			return $lang_code['code'];
		}

		return 'en';
	}

	/**
	 * Returns store language code
	 * @return string
	 */
	public function get_language_code() {
		$code = 'en';

		if ( isset( $this->session->data['language' ] ) ) {
			$code = $this->session->data['language'];

		} elseif ( $this->config->get( 'config_languge' ) ) {
			$code = $this->config->get( 'config_language' );

		} elseif ( $this->config->get( 'config_language_id ') ) {
			$code = $this->get_lang();
		}

		return $code;
	}


	/**
	 * Returns language code by configuration setting
	 * @return string
	 */
	public function get_lang() {
		$language_id = null;
		$config_lang = $this->config->get( 'config_language_id' );

		foreach( $this->get_languages() as $language ) {
			if( $language['language_id'] === $config_lang ) {
				$language_id = $language['code'];
				break;
			}
		}

		return $language_id;
	}

	/**
	 * Returns DB languages
	 * @return array
	 */
	// public function get_languages() {
	// 	if( ! $this->has_in_cache( 'languages' ) ) {
	// 		$query = $this->db->query( "SELECT * FROM `" . DB_PREFIX . "language`" );

	// 		$this->add_to_cache( 'languages', $query->rows );
	// 		return $query->rows;
	// 	}

	// 	return $this->get_from_cache( 'languages' );
	// }

	/**
	 * Returns language data by its code
	 * @param string $code 
	 * @return array
	 */
	public function get_language( $code ) {
		$languages = $this->get_languages();

		// Strict match
		foreach( $languages as $language ) {
			if( $code === $language['code'] ) {
				return $language;
			}
		}

		// Case insensitive search
		$code = strtolower( $code );
		foreach( $languages as $language ) {
			if( $code === strtolower( $language['code'] ) ) {
				return $language;
			}
		}

		// Locale insensitive search
		$c_code = $code;

		if ( strpos( $code, '_' ) !== false ) {
			$c_code = strstr( $code, '_', true );
		}

		foreach( $languages as $language ) {
			if( $c_code === strstr( $language['code'], '_', true ) ) {
				return $language;
			}
		}

		$c_code = $code;

		if ( strpos( $code, '-' ) !== false ) {
			$c_code = strstr( $code, '-', true );
		}

		foreach( $languages as $language ) {
			if( $c_code === strstr( $language['code'], '-', true ) ) {
				return $language;
			}
		}
	}

	/**
	 * Returns shortcode/set by its name
	 * @param string|null $shortcode_name Shortcode name, if omitted - all lest will be returned 
	 * @return array
	 */
	// public function get_shortcode_data( $shortcode_name = null ) {
	// 	if( is_null( $this->shortcode_set ) ) {
	// 		$this->shortcode_set = array(
	// 			'vitrine' => array(
	// 				'callback'    => 'shortcode_vitrine',
	// 				'hint'        => 'vitrine(ID)',
	// 				'description' => $this->__( 'Shows brief information of several products in some category (bestsellers, latest etc)' ) .
	// 					' ' . $this->__( 'Need to be created prior to use '),
	// 				'context'     => array( $this->__( 'Customer' ), $this->__( 'Dashboard'), $this->__( 'Affiliate' ) ),
	// 			),
	// 			'social' => array(
	// 				'callback'    => 'shortcode_social',
	// 				'hint'        => 'social(ID)',
	// 				'description' => $this->__( 'Shows set of social media icons' ) . ' ' .
	// 					$this->__( 'Need to be created prior to use '),
	// 				'context'     => array( $this->__( 'Customer' ), $this->__( 'Dashboard'), $this->__( 'Affiliate' ) ),
	// 			),
	// 			'button' => array(
	// 				'callback'    => 'shortcode_button',
	// 				'hint'        => 'button(ID)',
	// 				'description' => $this->__( 'Shows call to action button' ) . ' ' .
	// 					$this->__( 'Need to be created prior to use '),
	// 				'context'     => array( $this->__( 'Customer' ), $this->__( 'Dashboard'), $this->__( 'Affiliate' ) ),
	// 			),
	// 			'qrcode' => array(
	// 				'callback'    => 'shortcode_qrcode',
	// 				'hint'        => 'qrcode(ID)',
	// 				'description' => $this->__( 'Shows QR Code' ) . ' ' .
	// 					$this->__( 'Need to be created prior to use '),
	// 				'context'     => array( $this->__( 'Customer' ), $this->__( 'Dashboard'), $this->__( 'Affiliate' ) ),
	// 			),
	// 			'open_in_browser' => array(
	// 				'callback'    => 'shortcode_open_in_browser',
	// 				'hint'        => 'open_in_browser(Text)',
	// 				'description' => $this->__( 'Renders link to open email contents in browser' ),
	// 				'context'     => array( $this->__( 'Customer' ), $this->__( 'Dashboard'), $this->__( 'Affiliate' ) ),
	// 			),
	// 			'restore_password_url' => array(
	// 				'callback'    => 'shortcode_restore_password_url',
	// 				'hint'        => 'restore_password_url',
	// 				'description' => $this->__( 'Shows URL link, which contains code to restore password' ),
	// 				'context'     => array( $this->__( 'Dashboard - Forgotten password' ) ),
	// 			),
	// 			'store_name' => array(
	// 				'callback'    => 'shortcode_store_name',
	// 				'hint'        => 'store_name',
	// 				'description' => $this->__( 'Shows name of current store' ),
	// 				'context'     => array( $this->__( 'Customer' ), $this->__( 'Dashboard'), $this->__( 'Affiliate' ) ),
	// 			),
	// 			'store_url' => array(
	// 				'callback'    => 'shortcode_store_url',
	// 				'hint'        => 'store_url(Text)',
	// 				'description' => $this->__( 'Shows link to the current store' ),
	// 				'context'     => array( $this->__( 'Customer' ), $this->__( 'Dashboard'), $this->__( 'Affiliate' ) ),
	// 			),
	// 			'ip' => array(
	// 				'callback'    => 'shortcode_ip',
	// 				'hint'        => 'ip',
	// 				'description' => $this->__( 'Shows IP for current session' ),
	// 				'context'     => array( $this->__( 'Customer' ), $this->__( 'Dashboard'), $this->__( 'Affiliate' ) ),
	// 			),
	// 			'customer_first_name' => array(
	// 				'callback'    => 'shortcode_customer_first_name',
	// 				'hint'        => 'customer_first_name',
	// 				'description' => $this->__( 'Shows first name for current customer' ),
	// 				'context'     => array( $this->__( 'Customer' ) ),
	// 			),
	// 			'customer_last_name' => array(
	// 				'callback'    => 'shortcode_customer_last_name',
	// 				'hint'        => 'customer_last_name',
	// 				'description' => $this->__( 'Shows last name for current customer' ),
	// 				'context'     => array( $this->__( 'Customer' ) ),
	// 			),
	// 			'customer_full_name' => array(
	// 				'callback'    => 'shortcode_customer_full_name',
	// 				'hint'        => 'customer_full_name',
	// 				'description' => $this->__( 'Shows full name for current customer' ),
	// 				'context'     => array( $this->__( 'Customer' ) ),
	// 			),
	// 			'affiliate_first_name' => array(
	// 				'callback'    => 'shortcode_affiliate_first_name',
	// 				'hint'        => 'affiliate_first_name',
	// 				'description' => $this->__( 'Shows first name for current affiliate' ),
	// 				'context'     => array( $this->__( 'Affiliate - Approve' ), $this->__( 'Dashboard - New affiliate') ),
	// 			),
	// 			'affiliate_last_name' => array(
	// 				'callback'    => 'shortcode_affiliate_last_name',
	// 				'hint'        => 'affiliate_last_name',
	// 				'description' => $this->__( 'Shows last name for current affiliate' ),
	// 				'context'     => array( $this->__( 'Affiliate - Approve' ), $this->__( 'Dashboard - New affiliate') ),
	// 			),
	// 			'affiliate_full_name' => array(
	// 				'callback'    => 'shortcode_affiliate_full_name',
	// 				'hint'        => 'affiliate_full_name',
	// 				'description' => $this->__( 'Shows full name for current affiliate' ),
	// 				'context'     => array( $this->__( 'Affiliate - Approve' ), $this->__( 'Dashboard - New affiliate') ),
	// 			),
	// 			'initial_contents' => array(
	// 				'callback'    => 'shortcode_initial_contents',
	// 				'hint'        => 'initial_contents',
	// 				'description' => $this->__( 'Shows contents of the initial letter (eg predefined by OpenCart)' ),
	// 				'context'     => array( $this->__( 'Customer' ), $this->__( 'Dashboard'), $this->__( 'Affiliate' ) ),
	// 			),
	// 			'unsubscribe' => array(
	// 				'callback'    => 'shortcode_unsubscribe',
	// 				'hint'        => 'unsubscribe(Text)',
	// 				'description' => $this->__( 'Creates link to a page, where customer can cancel a newsletter subscription (if such exists)' ),
	// 				'context'     => array( $this->__( 'Customer' ), $this->__( 'Dashboard'), $this->__( 'Affiliate' ) ),
	// 			),
	// 			'account_login_url' => array(
	// 				'callback'    => 'shortcode_account_login_url',
	// 				'hint'        => 'account_login_url(Text)',
	// 				'description' => $this->__( 'Creates link to a customers\'s account login page' ),
	// 				'context'     => array( $this->__( 'Customer' ), $this->__( 'Dashboard'), $this->__( 'Affiliate' ) ),
	// 			),
	// 			'transaction_amount' => array(
	// 				'callback'    => 'shortcode_transaction_amount',
	// 				'hint'        => 'transaction_amount',
	// 				'description' => $this->__( 'Shows formatted amount of credit transaction upon customer\'s account' ),
	// 				'context'     => array( $this->__( 'Customer - Add credit' ) ),
	// 			),
	// 			'transaction_description' => array(
	// 				'callback'    => 'shortcode_transaction_description',
	// 				'hint'        => 'transaction_description',
	// 				'description' => $this->__( 'Shows description for credit transaction upon customer\'s account' ),
	// 				'context'     => array( $this->__( 'Customer - Add credit' ) ),
	// 			),
	// 			'if_transaction_description' => array(
	// 				'callback'    => 'shortcode_if_transaction_description',
	// 				'hint'        => 'if_transaction_description}' . $this->__( 'Conditional text' ) . '{/if_transaction_description' ,
	// 				'description' => $this->__( 'Shows conditional text up to the closing tag, if description for credit transaction, upon customer\'s account is present' ),
	// 				'context'     => array( $this->__( 'Customer - Add credit' ) ),
	// 			),
	// 			'transaction_total' => array(
	// 				'callback'    => 'shortcode_transaction_total',
	// 				'hint'        => 'transaction_total',
	// 				'description' => $this->__( 'Shows formatted credit amount of customer\'s account' ),
	// 				'context'     => array( $this->__( 'Customer - Add credit' ) ),
	// 			),
	// 			'reward_points' => array(
	// 				'callback'    => 'shortcode_reward_points',
	// 				'hint'        => 'reward_points',
	// 				'description' => $this->__( 'Shows amount of reward points, added to customer\'s account' ),
	// 				'context'     => array( $this->__( 'Customer - Add reward points' ) ),
	// 			),
	// 			'reward_description' => array(
	// 				'callback'    => 'shortcode_reward_description',
	// 				'hint'        => 'reward_description',
	// 				'description' => $this->__( 'Shows description for reward points transaction upon customer\'s account' ),
	// 				'context'     => array( $this->__( 'Customer - Add reward points' ) ),
	// 			),
	// 			'if_reward_description' => array(
	// 				'callback'    => 'shortcode_if_reward_description',
	// 				'hint'        => 'if_reward_description}' . $this->__( 'Conditional text' ) . '{/if_reward_description',
	// 				'description' => $this->__( 'Shows conditional text, up to the closing tag, if description for an add reward points transaction is present' ),
	// 				'context'     => array( $this->__( 'Customer - Add reward points' ) ),
	// 			),
	// 			'reward_total' => array(
	// 				'callback'    => 'shortcode_reward_total',
	// 				'hint'        => 'reward_total',
	// 				'description' => $this->__( 'Shows total amount of customer\'s reward points' ),
	// 				'context'     => array( $this->__( 'Customer - Add reward points' ) ),
	// 			),
	// 			'affiliate_login_url' => array(
	// 				'callback'    => 'shortcode_affiliate_login_url',
	// 				'hint'        => 'affiliate_login_url(Text)',
	// 				'description' => $this->__( 'Creates link to a affiliate\'s account login page' ),
	// 				'context'     => array( $this->__( 'Customer' ), $this->__( 'Dashboard'), $this->__( 'Affiliate' ) ),
	// 			),
	// 			'affiliate_commission' => array(
	// 				'callback'    => 'shortcode_affiliate_commission',
	// 				'hint'        => 'affiliate_commission',
	// 				'description' => $this->__( 'Shows amount of commission, added to affiliate\'s account' ),
	// 				'context'     => array( $this->__( 'Affiliate - Add commission' ) ),
	// 			),
	// 			'affiliate_commission_total' => array(
	// 				'callback'    => 'shortcode_affiliate_commission_total',
	// 				'hint'        => 'affiliate_commission_total',
	// 				'description' => $this->__( 'Shows total amount of affiliate\'s commission' ),
	// 				'context'     => array( $this->__( 'Affiliate - Add commission' ) ),
	// 			),
	// 			'affiliate_commission_description' => array(
	// 				'callback'    => 'shortcode_affiliate_commission_description',
	// 				'hint'        => 'affiliate_commission_description',
	// 				'description' => $this->__( 'Shows description to affiliate commission transaction' ),
	// 				'context'     => array( $this->__( 'Affiliate - Add commission' ) ),
	// 			),
	// 			'if_affiliate_commission_description' => array(
	// 				'callback'    => 'shortcode_if_affiliate_commission_description',
	// 				'hint'        => 'if_affiliate_commission_description}' . $this->__( 'Conditional text' ) . '{/if_affiliate_commission_description',
	// 				'description' => $this->__( 'Shows conditional text, up to the closing tag, if description for an add affiliate commission transaction is present' ),
	// 				'context'     => array( $this->__( 'Affiliate - Add commission' ) ),
	// 			),
	// 			'return_id' => array(
	// 				'callback'    => 'shortcode_return_id',
	// 				'hint'        => 'return_id',
	// 				'description' => $this->__( 'Shows ID of a return' ),
	// 				'context'     => array( $this->__( 'Customer - Return update' ) ),
	// 			),
	// 			'return_date' => array(
	// 				'callback'    => 'shortcode_return_date',
	// 				'hint'        => 'return_date',
	// 				'description' => $this->__( 'Shows creation date of a return' ),
	// 				'context'     => array( $this->__( 'Customer - Return update' ) )
	// 			),
	// 			'return_status' => array(
	// 				'callback'    => 'shortcode_return_status',
	// 				'hint'        => 'return_status',
	// 				'description' => $this->__( 'Shows status of a return' ),
	// 				'context'     => array( $this->__( 'Customer - Return update' ) )
	// 			),
	// 			'return_comment' => array(
	// 				'callback'    => 'shortcode_return_comment',
	// 				'hint'        => 'return_comment',
	// 				'description' => $this->__( 'Shows description of a changing status transaction for current return' ),
	// 				'context'     => array( $this->__( 'Customer - Return update' ) )
	// 			),
	// 			'if_return_comment' => array(
	// 				'callback'    => 'shortcode_if_return_comment',
	// 				'hint'        => 'if_return_comment}' . $this->__( 'Conditional text' ) . '{/if_return_comment',
	// 				'description' => $this->__( 'Shows "conditional text" up to closing tag, if return transaction has comment' ),
	// 				'context'     => array( $this->__( 'Customer - Return update' ) )
	// 			),
	// 			'voucher_from' => array(
	// 				'callback'    => 'shortcode_voucher_from',
	// 				'hint'        => 'voucher_from',
	// 				'description' => $this->__( 'Shows voucher sender name' ),
	// 				'context'     => array( $this->__( 'Customer - Voucher' ) )
	// 			),
	// 			'voucher_amount' => array(
	// 				'callback'    => 'shortcode_voucher_amount',
	// 				'hint'        => 'voucher_amount',
	// 				'description' => $this->__( 'Shows voucher total amount' ),
	// 				'context'     => array( $this->__( 'Customer - Voucher' ) )
	// 			),
	// 			'voucher_code' => array(
	// 				'callback'    => 'shortcode_voucher_code',
	// 				'hint'        => 'voucher_code',
	// 				'description' => $this->__( 'Shows voucher code' ),
	// 				'context'     => array( $this->__( 'Customer - Voucher' ) )
	// 			),
	// 			'voucher_theme_image' => array(
	// 				'callback'    => 'shortcode_voucher_theme_image',
	// 				'hint'        => 'voucher_theme_image(width,height)',
	// 				'description' => $this->__( 'Shows voucher\'s theme image, if present' ),
	// 				'context'     => array( $this->__( 'Customer - Voucher' ) )
	// 			),
	// 			'voucher_message' => array(
	// 				'callback'    => 'shortcode_voucher_message',
	// 				'hint'        => 'voucher_message',
	// 				'description' => $this->__( 'Shows voucher message' ),
	// 				'context'     => array( $this->__( 'Customer - Voucher' ) )
	// 			),
	// 			'voucher_to' => array(
	// 				'callback'    => 'shortcode_voucher_to',
	// 				'hint'        => 'voucher_to',
	// 				'description' => $this->__( 'Shows the name of the recipient of the voucher' ),
	// 				'context'     => array( $this->__( 'Customer - Voucher' ) )
	// 			),
	// 			'voucher_from_email' => array(
	// 				'callback'    => 'shortcode_voucher_from_email',
	// 				'hint'        => 'voucher_from_email',
	// 				'description' => $this->__( 'Shows the name of the email of the voucher sender' ),
	// 				'context'     => array( $this->__( 'Customer - Voucher' ) )
	// 			),
	// 			'enquiry_from_email' => array(
	// 				'callback'    => 'shortcode_enquiry_from_email',
	// 				'hint'        => 'enquiry_from_email',
	// 				'description' => $this->__( 'Shows email address of a person, who has sent an enquiry' ),
	// 				'context'     => array( $this->__( 'Dashboard - Enquiry' ) )
	// 			),
	// 			'enquiry_from_name' => array(
	// 				'callback'    => 'shortcode_enquiry_from_name',
	// 				'hint'        => 'enquiry_from_name',
	// 				'description' => $this->__( 'Shows name of a person, who has sent an enquiry' ),
	// 				'context'     => array( $this->__( 'Dashboard - Enquiry' ) )
	// 			),
	// 			'enquiry' => array(
	// 				'callback'    => 'shortcode_enquiry',
	// 				'hint'        => 'enquiry',
	// 				'description' => $this->__( 'Shows contents of an enquiry' ),
	// 				'context'     => array( $this->__( 'Dashboard - Enquiry' ) )
	// 			),
	// 			'if_account_approve' => array(
	// 				'callback'    => 'shortcode_if_account_approve',
	// 				'hint'        => 'if_account_approve}' . $this->__( 'Conditional text' ) . '{/if_account_approve',
	// 				'description' => $this->__( 'Shows conditional text, up to closing tag, if a newly created customer\'s account need to be approved before became active' ),
	// 				'context'     => array( $this->__( 'Customer - New' ) )
	// 			),
	// 			'if_account_no_approve' => array(
	// 				'callback'    => 'shortcode_if_account_no_approve',
	// 				'hint'        => 'if_account_no_approve}' . $this->__( 'Conditional text' ) . '{/if_account_no_approve',
	// 				'description' => $this->__( 'Shows conditional text, up to closing tag, if a newly created customer\'s account has no need to be approved before became active' ),
	// 				'context'     => array( $this->__( 'Customer - New' ) )
	// 			),
	// 			'new_customer_first_name' => array(
	// 				'callback'    => 'shortcode_new_customer_first_name',
	// 				'hint'        => 'new_customer_first_name',
	// 				'description' => $this->__( 'Shows first name of newly registered customer' ),
	// 				'context'     => array( $this->__( 'Customer - New' ), $this->__( 'Dashboard - New customer' ) )
	// 			),
	// 			'new_customer_last_name' => array(
	// 				'callback'    => 'shortcode_new_customer_last_name',
	// 				'hint'        => 'new_customer_last_name',
	// 				'description' => $this->__( 'Shows last name of newly registered customer' ),
	// 				'context'     => array( $this->__( 'Customer - New' ), $this->__( 'Dashboard - New customer' ) )
	// 			),
	// 			'new_customer_group' => array(
	// 				'callback'    => 'shortcode_new_customer_group',
	// 				'hint'        => 'new_customer_group',
	// 				'description' => $this->__( 'Shows group for newly registered customer' ),
	// 				'context'     => array( $this->__( 'Customer - New' ), $this->__( 'Dashboard - New customer' ) )
	// 			),
	// 			'new_customer_email' => array(
	// 				'callback'    => 'shortcode_new_customer_email',
	// 				'hint'        => 'new_customer_email',
	// 				'description' => $this->__( 'Shows email of newly registered customer' ),
	// 				'context'     => array( $this->__( 'Customer - New' ), $this->__( 'Dashboard - New customer' ) )
	// 			),
	// 			'new_customer_telephone' => array(
	// 				'callback'    => 'shortcode_new_customer_telephone',
	// 				'hint'        => 'new_customer_first_name',
	// 				'description' => $this->__( 'Shows telephone of newly registered customer' ),
	// 				'context'     => array( $this->__( 'Customer - New' ), $this->__( 'Dashboard - New customer' ) )
	// 			),
	// 			'new_customer_address_1' => array(
	// 				'callback'    => 'shortcode_new_customer_address_1',
	// 				'hint'        => 'new_customer_address_1',
	// 				'description' => $this->__( 'Shows address line 1 of newly registered customer' ),
	// 				'context'     => array( $this->__( 'Customer - New' ), $this->__( 'Dashboard - New customer' ) )
	// 			),
	// 			'new_customer_city' => array(
	// 				'callback'    => 'shortcode_new_customer_city',
	// 				'hint'        => 'new_customer_city',
	// 				'description' => $this->__( 'Shows city of newly registered customer' ),
	// 				'context'     => array( $this->__( 'Customer - New' ), $this->__( 'Dashboard - New customer' ) )
	// 			),
	// 			'new_customer_country' => array(
	// 				'callback'    => 'shortcode_new_customer_country',
	// 				'hint'        => 'new_customer_country',
	// 				'description' => $this->__( 'Shows country of newly registered customer' ),
	// 				'context'     => array( $this->__( 'Customer - New' ), $this->__( 'Dashboard - New customer' ) )
	// 			),
	// 			'new_customer_first_name' => array(
	// 				'callback'    => 'shortcode_new_customer_first_name',
	// 				'hint'        => 'new_customer_first_name',
	// 				'description' => $this->__( 'Shows first name of newly registered customer' ),
	// 				'context'     => array( $this->__( 'Customer - New' ), $this->__( 'Dashboard - New customer' ) )
	// 			),
	// 			'new_customer_region' => array(
	// 				'callback'    => 'shortcode_new_customer_region',
	// 				'hint'        => 'new_customer_region',
	// 				'description' => $this->__( 'Shows address region of newly registered customer' ),
	// 				'context'     => array( $this->__( 'Customer - New' ), $this->__( 'Dashboard - New customer' ) )
	// 			),
	// 			'new_affiliate_first_name' => array(
	// 				'callback'    => 'shortcode_new_affiliate_first_name',
	// 				'hint'        => 'new_affiliate_first_name',
	// 				'description' => $this->__( 'Shows first name of newly registered affiliate' ),
	// 				'context'     => array( $this->__( 'Affiliate - New' ), $this->__( 'Dashboard - New affiliate' ) )
	// 			),
	// 			'new_affiliate_last_name' => array(
	// 				'callback'    => 'shortcode_new_affiliate_last_name',
	// 				'hint'        => 'new_affiliate_last_name',
	// 				'description' => $this->__( 'Shows last name of newly registered affiliate' ),
	// 				'context'     => array( $this->__( 'Affiliate - New' ), $this->__( 'Dashboard - New affiliate' ) )
	// 			),
	// 			'new_affiliate_email' => array(
	// 				'callback'    => 'shortcode_new_affiliate_email',
	// 				'hint'        => 'new_affiliate_email',
	// 				'description' => $this->__( 'Shows email of newly registered affiliate' ),
	// 				'context'     => array( $this->__( 'Affiliate - New' ), $this->__( 'Dashboard - New affiliate' ) )
	// 			),
	// 			'new_affiliate_telephone' => array(
	// 				'callback'    => 'shortcode_new_affiliate_telephone',
	// 				'hint'        => 'new_affiliate_telephone',
	// 				'description' => $this->__( 'Shows first telephone number of newly registered affiliate' ),
	// 				'context'     => array( $this->__( 'Affiliate - New' ), $this->__( 'Dashboard - New affiliate' ) )
	// 			),
	// 			'new_affiliate_company' => array(
	// 				'callback'    => 'shortcode_new_affiliate_company',
	// 				'hint'        => 'new_affiliate_company',
	// 				'description' => $this->__( 'Shows company name of newly registered affiliate' ),
	// 				'context'     => array( $this->__( 'Affiliate - New' ), $this->__( 'Dashboard - New affiliate' ) )
	// 			),
	// 			'new_affiliate_website' => array(
	// 				'callback'    => 'shortcode_new_affiliate_website',
	// 				'hint'        => 'new_affiliate_website',
	// 				'description' => $this->__( 'Shows website name of newly registered affiliate' ),
	// 				'context'     => array( $this->__( 'Affiliate - New' ), $this->__( 'Dashboard - New affiliate' ) )
	// 			),
	// 			'new_affiliate_address_1' => array(
	// 				'callback'    => 'shortcode_new_affiliate_address_1',
	// 				'hint'        => 'new_affiliate_address_1',
	// 				'description' => $this->__( 'Shows address line 1 for newly registered affiliate' ),
	// 				'context'     => array( $this->__( 'Affiliate - New' ), $this->__( 'Dashboard - New affiliate' ) )
	// 			),
	// 			'new_affiliate_city' => array(
	// 				'callback'    => 'shortcode_new_affiliate_city',
	// 				'hint'        => 'new_affiliate_city',
	// 				'description' => $this->__( 'Shows city for newly registered affiliate' ),
	// 				'context'     => array( $this->__( 'Affiliate - New' ), $this->__( 'Dashboard - New affiliate' ) )
	// 			),
	// 			'new_affiliate_country' => array(
	// 				'callback'    => 'shortcode_new_affiliate_country',
	// 				'hint'        => 'new_affiliate_country',
	// 				'description' => $this->__( 'Shows country for newly registered affiliate' ),
	// 				'context'     => array( $this->__( 'Affiliate - New' ), $this->__( 'Dashboard - New affiliate' ) )
	// 			),
	// 			'new_affiliate_region' => array(
	// 				'callback'    => 'shortcode_new_affiliate_region',
	// 				'hint'        => 'new_affiliate_region',
	// 				'description' => $this->__( 'Shows region for newly registered affiliate' ),
	// 				'context'     => array( $this->__( 'Affiliate - New' ), $this->__( 'Dashboard - New affiliate' ) )
	// 			),
	// 			'if_affiliate_approve' => array(
	// 				'callback'    => 'shortcode_if_affiliate_approve',
	// 				'hint'        => 'if_affiliate_approve}' . $this->__( 'Conditional text' ) . '{/if_affiliate_approve',
	// 				'description' => $this->__( 'Shows conditional text, up to the closing tag, if a newly created affiliate account need to be approved, before became active' ),
	// 				'context'     => array( $this->__( 'Affiliate - New' ), $this->__( 'Dashboard - New affiliate' ) )
	// 			),
	// 			'if_affiliate_no_approve' => array(
	// 				'callback'    => 'shortcode_if_affiliate_no_approve',
	// 				'hint'        => 'if_affiliate_no_approve}' . $this->__( 'Conditional text' ) . '{/if_affiliate_no_approve',
	// 				'description' => $this->__( 'Shows conditional text, up to the closing tag, if a newly created affiliate account has no need to be approved, before became active' ),
	// 				'context'     => array( $this->__( 'Affiliate - New' ), $this->__( 'Dashboard - New affiliate' ) )
	// 			),
	// 			'review_product' => array(
	// 				'callback'    => 'shortcode_review_product',
	// 				'hint'        => 'review_product',
	// 				'description' => $this->__( 'Shows product name, which has been reviewed' ),
	// 				'context'     => array( $this->__( 'Dashboard - Review' ) )
	// 			),
	// 			'review_person' => array(
	// 				'callback'    => 'shortcode_review_person',
	// 				'hint'        => 'review_person',
	// 				'description' => $this->__( 'Shows name of a reviewer' ),
	// 				'context'     => array( $this->__( 'Dashboard - Review' ) )
	// 			),
	// 			'review_rating' => array(
	// 				'callback'    => 'shortcode_review_rating',
	// 				'hint'        => 'review_rating',
	// 				'description' => $this->__( 'Shows review rating' ),
	// 				'context'     => array( $this->__( 'Dashboard - Review' ) )
	// 			),
	// 			'review_text' => array(
	// 				'callback'    => 'shortcode_review_text', 
	// 				'hint'        => 'review_text',
	// 				'description' => $this->__( 'Shows text contents of a review' ),
	// 				'context'     => array( $this->__( 'Dashboard - Review' ) )
	// 			),
	// 			'order_id' => array(
	// 				'callback'    => 'shortcode_order_id', 
	// 				'hint'        => 'order_id',
	// 				'description' => $this->__( 'Shows order ID' ),
	// 				'context'     => array( $this->__( 'Dashboard - New order' ), $this->__( 'Customer - New order' ), $this->__( 'Customer - Order update' ) )
	// 			),
	// 			'if_order_download' => array(
	// 				'callback'    => 'shortcode_if_order_download', 
	// 				'hint'        => 'if_order_download}' . $this->__( 'Conditional text' ) . '{/if_order_download',
	// 				'description' => $this->__( 'Shows conditional text, up to the closing tag, if a newly created order contains downloadable product' ),
	// 				'context'     => array( $this->__( 'Dashboard - New order' ), $this->__( 'Customer - New order' ), $this->__( 'Customer - Order update' ) )
	// 			),
	// 			'invoice_table' => array(
	// 				'callback'    => 'shortcode_invoice_table', 
	// 				'hint'        => 'invoice_table',
	// 				'description' => $this->__( 'Shows tabulated invoice data for current order' ),
	// 				'context'     => array( $this->__( 'Dashboard - New order' ), $this->__( 'Customer - New order' ) )
	// 			),
	// 			'invoice' => array(
	// 				'callback'    => 'shortcode_invoice', 
	// 				'hint'        => 'invoice(ID)',
	// 				'description' => $this->__( 'Shows a customizable inlined invoice table' ) . ' ' .
	// 					$this->__( 'Need to be created prior to use '),
	// 				'context'     => array( $this->__( 'Dashboard - New order' ), $this->__( 'Customer - New order' ) )
	// 			),
	// 			'invoice_table_text' => array(
	// 				'callback'    => 'shortcode_invoice_table_text', 
	// 				'hint'        => 'invoice_table_text',
	// 				'description' => $this->__( 'Shows textul invoice data for current order' ),
	// 				'context'     => array( $this->__( 'Dashboard - New order' ), $this->__( 'Customer - New order' ), $this->__( 'Customer - Order update' ) )
	// 			),
	// 			'if_order_approve' => array(
	// 				'callback'    => 'shortcode_if_order_approve', 
	// 				'hint'        => 'if_order_approve}' . $this->__( 'Conditional text' ) . '{/if_order_approve',
	// 				'description' => $this->__( 'Shows conditional text, up to the closing tag, if a newly created order is need to be approved (has uncompleted status)' ),
	// 				'context'     => array( $this->__( 'Dashboard - New order' ), $this->__( 'Customer - New order' ), $this->__( 'Customer - Order update' ) )
	// 			),
	// 			'if_order_no_approve' => array(
	// 				'callback'    => 'shortcode_if_order_no_approve', 
	// 				'hint'        => 'if_order_no_approve}' . $this->__( 'Conditional text' ) . '{/if_order_no_approve',
	// 				'description' => $this->__( 'Shows conditional text, up to the closing tag, if a newly created order has no need to be approved (has completed status)' ),
	// 				'context'     => array( $this->__( 'Dashboard - New order' ), $this->__( 'Customer - New order' ), $this->__( 'Customer - Order update' ) )
	// 			),
	// 			'order_url' => array(
	// 				'callback'    => 'shortcode_order_url', 
	// 				'hint'        => 'order_url(Text)',
	// 				'description' => $this->__( 'Shows link to the order page' ),
	// 				'context'     => array( $this->__( 'Dashboard - New order' ), $this->__( 'Customer - New order' ), $this->__( 'Customer - Order update' ) )
	// 			),
	// 			'download_url' => array(
	// 				'callback'    => 'shortcode_download_url', 
	// 				'hint'        => 'download_url(Text)',
	// 				'description' => $this->__( 'Shows link to customer\'s account download page' ),
	// 				'context'     => array( $this->__( 'Customer' ), $this->__( 'Dashboard'), $this->__( 'Affiliate' ) ),
	// 			),
	// 			'order_date_added' => array(
	// 				'callback'    => 'shortcode_order_date_added', 
	// 				'hint'        => 'order_date_added',
	// 				'description' => $this->__( 'Shows date of order placement' ),
	// 				'context'     => array( $this->__( 'Dashboard - New order' ), $this->__( 'Customer - New order' ), $this->__( 'Customer - Order update' ) )
	// 			),
	// 			'order_status' => array(
	// 				'callback'    => 'shortcode_order_status', 
	// 				'hint'        => 'order_status',
	// 				'description' => $this->__( 'Shows order status' ),
	// 				'context'     => array( $this->__( 'Dashboard - New order' ), $this->__( 'Customer - New order' ), $this->__( 'Customer - Order update' ) )
	// 			),
	// 			'order_status_new' => array(
	// 				'callback'    => 'shortcode_order_status_new', 
	// 				'hint'        => 'order_status_new',
	// 				'description' => $this->__( 'Shows new status for an order' ),
	// 				'context'     => array( $this->__( 'Dashboard - New order' ), $this->__( 'Customer - New order' ), $this->__( 'Customer - Order update' ) )
	// 			),
	// 			'order_status_old' => array(
	// 				'callback'    => 'shortcode_order_status_old', 
	// 				'hint'        => 'order_status_old',
	// 				'description' => $this->__( 'Shows old status for an order' ),
	// 				'context'     => array( $this->__( 'Dashboard - New order' ), $this->__( 'Customer - New order' ), $this->__( 'Customer - Order update' ) )
	// 			),
	// 			'order_products' => array(
	// 				'callback'    => 'shortcode_order_products', 
	// 				'hint'        => 'order_products',
	// 				'description' => $this->__( 'Shows a list of order\'s products' ),
	// 				'context'     => array( $this->__( 'Dashboard - New order' ), $this->__( 'Customer - New order' ), $this->__( 'Customer - Order update' ) )
	// 			),
	// 			'if_products_sku' => array(
	// 				'callback'    => 'shortcode_if_products_sku', 
	// 				'hint'        => 'if_products_sku(SKU1,SKU2,...)}Conditional text{/if_products_sku',
	// 				'description' => $this->__( 'Shows conditional tag if an order contains at least one product with specific SKU' ),
	// 				'context'     => array( $this->__( 'Dashboard - New order' ), $this->__( 'Customer - New order' ), $this->__( 'Customer - Order update' ) )
	// 			),
	// 			'if_no_products_sku' => array(
	// 				'callback'    => 'shortcode_if_no_products_sku', 
	// 				'hint'        => 'if_no_products_sku(SKU1,SKU2,...)}Conditional text{/if_no_products_sku',
	// 				'context'     => array( $this->__( 'Dashboard - New order' ), $this->__( 'Customer - New order' ), $this->__( 'Customer - Order update' ) ),
	// 				'description' => $this->__( 'Shows conditional tag if an order does not contain at least one product with specific SKU' ),
	// 			),
	// 			'if_products_sku_all' => array(
	// 				'callback'    => 'shortcode_if_products_sku_all', 
	// 				'hint'        => 'if_products_sku_all(SKU1,SKU2,...)}Conditional text{/if_products_sku_all',
	// 				'description' => $this->__( 'Shows conditional tag if an order contains all the products with specific SKU' ),
	// 				'context'     => array( $this->__( 'Dashboard - New order' ), $this->__( 'Customer - New order' ), $this->__( 'Customer - Order update' ) )
	// 			),
	// 			'if_no_products_sku_all' => array(
	// 				'callback'    => 'shortcode_if_no_products_sku_all', 
	// 				'hint'        => 'if_no_products_sku_all(SKU1,SKU2,...)}Conditional text{/if_no_products_sku_all',
	// 				'description' => $this->__( 'Shows conditional tag if an order does not contain all of the products with specific SKU' ),
	// 				'context'     => array( $this->__( 'Dashboard - New order' ), $this->__( 'Customer - New order' ), $this->__( 'Customer - Order update' ) )
	// 			),
	// 			'order_totals' => array(
	// 				'callback'    => 'shortcode_order_totals', 
	// 				'hint'        => 'order_totals',
	// 				'description' => $this->__( 'Shows totals for an order' ),
	// 				'context'     => array( $this->__( 'Dashboard - New order' ), $this->__( 'Customer - New order' ), $this->__( 'Customer - Order update' ) )
	// 			),
	// 			'order_comment' => array(
	// 				'callback'    => 'shortcode_order_comment', 
	// 				'hint'        => 'order_comment',
	// 				'description' => $this->__( 'Shows comment. left by customer' ),
	// 				'context'     => array( $this->__( 'Dashboard - New order' ), $this->__( 'Customer - New order' ), $this->__( 'Customer - Order update' ) )
	// 			),
	// 			'order_status_comment' => array(
	// 				'callback'    => 'shortcode_order_status_comment', 
	// 				'hint'        => 'order_status_comment',
	// 				'description' => $this->__( 'Shows comment, pertain to an order status' ),
	// 				'context'     => array( $this->__( 'Dashboard - New order' ), $this->__( 'Customer - New order' ), $this->__( 'Customer - Order update' ) )
	// 			),
	// 			'if_order_status_comment' => array(
	// 				'callback'    => 'shortcode_if_order_status_comment', 
	// 				'hint'        => 'if_order_status_comment}' . $this->__( 'Conditional text' ) . '{/if_order_status_comment',
	// 				'description' => $this->__( 'Shows conditional text, up to the closing tab, when comment, pertain to an order status is present' ),
	// 				'context'     => array( $this->__( 'Dashboard - New order' ), $this->__( 'Customer - New order' ), $this->__( 'Customer - Order update' ) )
	// 			),
	// 			'if_order_status_no_comment' => array(
	// 				'callback'    => 'shortcode_if_order_status_no_comment', 
	// 				'hint'        => 'if_order_status_no_comment}' . $this->__( 'Conditional text' ) . '{/if_order_status_no_comment',
	// 				'description' => $this->__( 'Shows conditional text, up to the closing tab, when comment, pertain to an order status is not present' ),
	// 				'context'     => array( $this->__( 'Dashboard - New order' ), $this->__( 'Customer - New order' ), $this->__( 'Customer - Order update' ) )
	// 			),
	// 			'if_order_comment' => array(
	// 				'callback'    => 'shortcode_if_order_comment', 
	// 				'hint'        => 'if_order_comment}' . $this->__( 'Conditional text' ) . '{/if_order_comment',
	// 				'description' => $this->__( 'Shows conditional text, up to the closing tab, when order contains customer\'s comment' ),
	// 				'context'     => array( $this->__( 'Dashboard - New order' ), $this->__( 'Customer - New order' ), $this->__( 'Customer - Order update' ) )
	// 			),
	// 			'if_order_no_comment' => array(
	// 				'callback'    => 'shortcode_if_order_no_comment', 
	// 				'hint'        => 'if_order_no_comment}' . $this->__( 'Conditional text' ) . '{/if_order_no_comment',
	// 				'description' => $this->__( 'Shows conditional text, up to the closing tab, when order doesn\'t contain customer\'s comment' ),
	// 				'context'     => array( $this->__( 'Dashboard - New order' ), $this->__( 'Customer - New order' ), $this->__( 'Customer - Order update' ) )
	// 			),
	// 			'newsletter_name' => array(
	// 				'callback'    => 'shortcode_newsletter_name', 
	// 				'hint'        => 'newsletter_name',
	// 				'description' => $this->__( 'Shows name for current newsletter' ),
	// 				'context'     => array( $this->__( 'Newsletter' ), ),
	// 			),
	// 			'subscriber_name' => array(
	// 				'callback'    => 'shortcode_subscriber_name', 
	// 				'hint'        => 'subscriber_name',
	// 				'description' => $this->__( 'Shows subscribers name' ),
	// 				'context'     => array( $this->__( 'Newsletter' ), ),
	// 			),
	// 			'subscriber_email' => array(
	// 				'callback'    => 'shortcode_subscriber_email', 
	// 				'hint'        => 'subscriber_email',
	// 				'description' => $this->__( 'Shows subscriber email address' ),
	// 				'context'     => array( $this->__( 'Newsletter' ), ),
	// 			),
	// 			'confirm_subscription_url' => array(
	// 				'callback'    => 'shortcode_confirm_subscription_url',
	// 				'hint'        => 'confirm_subscription_url(Text)',
	// 				'description' => $this->__( 'Shows link to a subscription confirmation page' ),
	// 				'context'     => array( $this->__( 'Newsletter' ), ),
	// 			),
	// 			// 'r1' => array(
	// 			// 	'callback'    => 'r1', 
	// 			// 	'hint'        => 'if_order_no_comment}' . $this->__( 'Conditional text' ) . '{/if_order_no_comment',
	// 			// 	'description' => $this->__( 'Shows conditional text, up to the closing tab, when order doesn\'t contain customer\'s comment' ),
	// 			// 	'context'     => array( $this->__( 'Dashboard - New order' ), $this->__( 'Customer - New order' ), $this->__( 'Customer - Order update' ) )
	// 			// ),
	// 			// 'r2' => array(
	// 			// 	'callback'    => 'r2', 
	// 			// 	'hint'        => 'if_order_no_comment}' . $this->__( 'Conditional text' ) . '{/if_order_no_comment',
	// 			// 	'description' => $this->__( 'Shows conditional text, up to the closing tab, when order doesn\'t contain customer\'s comment' ),
	// 			// 	'context'     => array( $this->__( 'Dashboard - New order' ), $this->__( 'Customer - New order' ), $this->__( 'Customer - Order update' ) )
	// 			// ),
	// 			// 'r3' => array(
	// 			// 	'callback'    => 'r3', 
	// 			// 	'hint'        => 'if_order_no_comment}' . $this->__( 'Conditional text' ) . '{/if_order_no_comment',
	// 			// 	'description' => $this->__( 'Shows conditional text, up to the closing tab, when order doesn\'t contain customer\'s comment' ),
	// 			// 	'context'     => array( $this->__( 'Dashboard - New order' ), $this->__( 'Customer - New order' ), $this->__( 'Customer - Order update' ) )
	// 			// ),
	// 		);
	// 	}

	// 	if( is_null( $shortcode_name ) ) {
	// 		return $this->shortcode_set;
	// 	}

	// 	if( isset( $this->shortcode_set[ $shortcode_name ] ) ) {
	// 		return $this->shortcode_set[ $shortcode_name ];
	// 	}

	// 	return null;
	// }

	// public function r1() {
	// 	return '{r2}';
	// }

	// public function r2() {
	// 	return '{r3}';
	// }

	// public function r3() {
	// 	return 'hello{r1}';
	// }

	// /**
	//  * Returns hints for all the shortcodes
	//  * @return array
	//  */
	// public function get_shortcodes_hint() {
	// 	$ret = array();

	// 	foreach( $this->get_shortcode_data() as $shortcode ) {
	// 		$ret[] = $this->brace_shortcode_name( $shortcode['hint'] );
	// 	}

	// 	return $ret;
	// }

	/**
	 * Returns corresponding profile for template
	 * @param int|null $store_id Store ID, optional
	 * @param string|null $lang_code Language code, optional 
	 * @param int|null $template_id Template ID, optional 
	 * @return array
	 */
	public function get_template_profile( $store_id = null, $lang_code = null, $template_id = null ) {
		$profile_id = null;

		// Template ID and at least store ID are present
		if( ! is_null( $template_id ) && ! is_null( $store_id ) ) {
			$template = $this->get_mail_template( $template_id );

			// Template doesn't exist - bounce to profile ID #1
			if( ! $template ) {
				trigger_error( sprintf( 'Template with ID #%s is missing', $template_id ) );
				$profile_id = 1;

			} else {

				// Language code is present and language mapping is defined - use language mapping
				if( ! is_null( $lang_code ) && isset( $template['data'][ $store_id ]['lang'][ $lang_code ]['profile'] ) ) {
					$profile_id = $template['data'][ $store_id ]['lang'][ $lang_code ]['profile'];

				// If not - try store mapping
				} elseif ( isset( $template['data'][ $store_id ]['profile'] ) ) {
					$profile_id = $template['data'][ $store_id ]['profile'];
				}
			}
		}

		// Still no luck - use global mapping
		if( is_null( $profile_id ) ) {
			$profile_id = $this->get_profile_mapping( $store_id, $lang_code, $template_id );
		}

		// It's impossible... Bounce to profile with ID #1
		if( is_null( $profile_id ) ) {
			$profile_id = 1;
		}

		return $this->get_profile( $profile_id );
	}

	/**
	 * Checks if some value is present in array/object
	 * @param array|object $where Target element
	 * @param string $what Value name, may be in format name1/name2/name3 
	 * @return boolean
	 */
	// public function is_set( $where, $what ) {
	// 	if( is_scalar( $where ) ) {
	// 		return false;
	// 	}

	// 	if( gettype( $where ) === 'object' ) {
	// 		$where = $this->object_to_array( $where );
	// 	}

	// 	if( ! is_array( $where ) ) {
	// 		return false;
	// 	}

	// 	foreach( explode( '/', $what ) as $part ) {
	// 		if( ! isset( $where[ $part ] ) ) {
	// 			return false;
	// 		}

	// 		$where = $where[ $part ];
	// 	}

	// 	return true;
	// }

	/**
	 * Returns template content part by its name
	 * @param array $template Target template 
	 * @param string $content_name Content name to search for
	 * @param int $store_id Store ID
	 * @param string $lang_id Language code 
	 * @return string
	 */
	public function get_template_content( $template, $content_name, $store_id, $lang_id ) {
		$ret = '';
		$this->adk_template = $template;
		list( $store_id, $lang_id ) = $this->get_template_store_lang( $template, $store_id, $lang_id );

		if( isset( $template['data'][ $store_id ]['lang'][ $lang_id ]['content'][ $content_name ] ) ) {
			if( in_array( $content_name, array( 'content', 'subject' ) ) ) {
				$ret = $this->do_shortcode(
					htmlspecialchars_decode(
						$template['data'][ $store_id ]['lang'][ $lang_id ]['content'][ $content_name ]
					)
				);
			}
		}

		$this->adk_tempalte = null;

		return $ret;
	}

	/**
	 * Returns template data regarding data storing levels
	 * @since 1.1.0
	 * @param array $template Target template
	 * @param string $data_name Data name to search for
	 * @param int|null $store_id Store ID, optional 
	 * @param int|null $lang_id Language code, optional 
	 * @return string
	 */
	public function get_template_data( $template, $data_name, $store_id = null, $lang_id = null ) {

		// Language level data
		if( isset( $template['data'][ $store_id ]['lang'][ $lang_id ]['data'][ $data_name ] ) ) {
			return $template['data'][ $store_id ]['lang'][ $lang_id ]['data'][ $data_name ];
		}

		// Store level data
		if( ! in_array( $data_name, array( 'lang' ) ) &&
				isset( $template['data'][ $store_id ][ $data_name ] ) ) {

			return $template['data'][ $store_id ][ $data_name ];
		}

		// Template level data
		if( isset( $template['data']['data'][ $data_name ] ) ) {
			return $template['data']['data'][ $data_name ];
		}

		return '';
	}

	/**
	 * Returns available for template store ID / language code pair
	 * Algorithm is following:
	 * check passed store ID and language code for template contents,
	 * then search for contents for current language in other stores,
	 * then search for any language contents in current store,
	 * then search for ant language contents in any stores.
	 * @param array $template template data 
	 * @param int|null $store_id Store ID, optional 
	 * @param null|string $lang_id Language code 
	 * @return array
	 */
	public function get_template_store_lang( $template, $store_id = 0, $lang_id = 'en' ) {

		// Everything as expected
		if( isset( $template['data'][ $store_id ]['lang'][ $lang_id ] ) ) {
			return array( $store_id, $lang_id );
		}

		// Empty template data - nowhere to search
		if( ! isset( $template['data'] ) ) {
			return array( $store_id, $lang_id );
		}

		// Search for corresponding language template in other stores
		foreach( $template['data'] as $s_id => $store_data ) {
			if( isset( $store_data['lang'][ $lang_id ] ) ) {
				return array( $s_id, $lang_id );
			}
		}

		// Search for any language for the store
		if( isset( $template['data'][ $store_id ]['lang'] ) ) {
			foreach( $template['data'][ $store_id ]['lang'] as $l_id => $lang_data ) {
				return array( $store_id, $l_id );
			}
		}

		// Search for any language in any other store
		foreach( $template['data'] as $store_id => $store_data ) {
			if( ! isset( $store_data['lang'] ) ) {
				continue;
			}

			foreach( $store_data['lang'] as $lang_id => $lang_data ) {
				return array( $store_id, $lang_id );
			}
		}
	}

	/**
	 * Returns template subject
	 * @param array $template Template 
	 * @param int $store_id 
	 * @param string $lang_id 
	 * @return string
	 */
	public function get_template_subject( $template, $store_id, $lang_id ) {
		return $this->get_template_content( $template, 'subject', $store_id, $lang_id );
	}

	/**
	 * Recursively evaluates shortcodes
	 * @since 1.1.0 - rebuild
	 * @param type $text 
	 * @return type
	 */
	// public function do_shortcode( $text ) {
	// 	$ret = $text;
		
	// 	$ret = $this->conditional_print( $ret );
	// 	$ret = $this->evaluate_shortcode( $ret, $count );
	// 	$ret = $this->fix_content( $ret );

	// 	return $ret;
	// }

	/**
	 * Preforms shortcode evaluation over text
	 * @since 1.1.0
	 * @param string $text Text with shortcodes 
	 * @param int $count Number of evaluations
	 * @return string
	 */
	// public function evaluate_shortcode( $text, &$counts = 0 ) {
	// 	$self = $this;

	// 	// Search for {shortcode(param)} and pass into callback function
	// 	$ret = preg_replace_callback( '/\{(\w+)(?:\(([^\)]*)\))?}/', function( $matches ) use( $self, &$counts ) {

	// 		$replace = $matches[0];

	// 		// Ignore conditional tags
	// 		if( strpos( $matches[0], '{if_' ) === 0 || strpos( $matches[0], '{/if_' ) ) {
	// 			return $replace;
	// 		} 

	// 		// $matches[1] - shortcode name, $matches[2] - arguments 
	// 		$recursion = in_array( $matches[0], $self->shortcodes_stack );

	// 		if( $recursion ) {
	// 			trigger_error( sprintf( 'Recursion detected for shortcode "%s"', $matches[0] ) );
	// 		}

	// 		// Shortcode exists
	// 		if( !$recursion && $shortcode_data = $self->get_shortcode_data( $matches[1] ) ) {

	// 			// Shortcode has callback
	// 			if( isset( $shortcode_data['callback'] ) && is_callable( array( $self, $shortcode_data['callback'] ) ) ) {

	// 				// Shortcode name is the first argument
	// 				$args = array( $matches[1] );

	// 				// Define arguments
	// 				if( isset( $matches[2] ) ) {
	// 					$f_args = explode( ',', $matches[2] );
	// 					foreach( $f_args as $arg ) {
	// 						$args[] = trim( $arg );
	// 					}
	// 				}

	// 				$replace = call_user_func_array( array( $self, $shortcode_data['callback'] ), $args );

	// 				// Active shortcodes stack
	// 				array_push( $self->shortcodes_stack, $matches[0] );

	// 				// Enter recursion
	// 				$replace = $self->do_shortcode( $replace );

	// 				array_pop( $self->shortcodes_stack );

	// 			} else {
	// 				trigger_error( sprintf( 'Callback is missing for shortcode "%s"', $matches[0] ) );
	// 			}
	// 		}

	// 		return $replace;
	// 	}, $text );

	// 	return $ret;
	// }

	/**
	 * Fixes email contents issues
	 * @param string $text Text to be fixed 
	 * @return string
	 */
	// public function fix_content( $text ) {
	// 	$search = array(
	// 		'http://http://', // 1 double protocol parts, added by summernote
	// 	);

	// 	$replace = array(
	// 		'http://', // 1
	// 	);

	// 	// Anchors with empty href attributes
	// 	$text = preg_replace( '#<a\s+[^>]*href=(\'|\")(?:[^/]*//\s*|\s*)\1[^>]*>[^<]*</a>#', '', $text );

	// 	// Folded HREF attributes
	// 	$text = preg_replace( '#href=(\"|\')\s*<([^\s])+\s[^>]*?href=(\"|\')([^>]+?)\3.+?</\2>\1#', 'href=$1$4$1', $text );

	// 	$text = str_replace( $search, $replace, $text );

	// 	return $text;
	// }

	/**
	 * Checks if template item has content
	 * @param array $item Target item
	 * @return boolean
	 */
	public function has_content( $item ) {
		return ! empty( $item['text']['content'] );
	}

	/**
	 * Returns content of template's top part
	 * @param array $top Template top part 
	 * @return string
	 */
	public function get_top_content( $top ) {
		$ret = isset( $top['text']['content'] ) ? $top['text']['content'] : '';
		return $this->do_shortcode( htmlspecialchars_decode( $ret ) );
	}

	/**
	 * Returns template header part's contents
	 * @param array $header Template header part
	 * @return type
	 */
	public function get_header_content( $header ) {
		$ret = isset( $header['text']['content'] ) ? $header['text']['content'] : '';
		return $this->do_shortcode( htmlspecialchars_decode( $ret ) );
	}

	/**
	 * Returns template footer part's content
	 * @param array $footer Template footer part
	 * @return string
	 */
	public function get_footer_content( $footer ) {
		$ret = isset( $footer['text']['content'] ) ? $footer['text']['content'] : '';
		return $this->do_shortcode( htmlspecialchars_decode( $ret ) );
	}

	/**
	 * Returns template bottom part's content
	 * @param array $footer Template bottom part
	 * @return string
	 */
	public function get_bottom_content( $bottom ) {
		$ret = isset( $bottom['text']['content'] ) ? $bottom['text']['content'] : '';
		return $this->do_shortcode( htmlspecialchars_decode( $ret ) );
	}

	/**
	 * Returns all system profiles
	 * @return array
	 */
	public function get_profiles() {
		if( is_null( $this->profiles ) ) {
			$ret = array();
			$query = $this->db->query( "SELECT * FROM `" . DB_PREFIX . $this->profiles_table . "`" );

			foreach( $query->rows as $k => $row ) {
				$ret[ $row['profile_id'] ] = $row;
				$ret[ $row['profile_id'] ]['data'] = $this->object_to_array( json_decode( $this->fix_json_string( $row['data'] ) ) );
				$ret[ $row['profile_id'] ]['fields'] = explode( ',', $row['fields'] );
				$ret[ $row['profile_id'] ]['inputs'] = explode( ',', $row['inputs'] );
				$ret[ $row['profile_id'] ]['content_fields'] = explode( ',', $row['content_fields'] );
			}

			$this->profiles = $ret;
		}

		return $this->profiles;
	}

	/**
	 * Returns all system templates
	 * @return array
	 */
	public function get_templates() {
		if( is_null( $this->templates ) ) {
			$ret = array();
			$query = $this->db->query( "SELECT * FROM `" . DB_PREFIX . $this->templates_table . "` ORDER BY `name`" );

			foreach( $query->rows as $row ) {
				$ret[ $row['template_id'] ] = $row;

				if( $row['data'] ) {
					$ret[ $row['template_id'] ]['data'] = $this->object_to_array( json_decode( $this->fix_json_string( $row['data'] ) ) );

				} else {
					$ret[ $row['template_id'] ]['data'] = array();
				}
			}

			$this->templates = $ret;
		}

		return $this->templates;
	}

	/**
	 * Returns template by its ID
	 * @param int $template_id Template ID
	 * @return array
	 */
	public function get_mail_template( $template_id ) {
		if( ! isset( $this->templates[ $template_id ] ) ) {
			$query = $this->db->query(
				"SELECT * FROM `" . DB_PREFIX . $this->templates_table . "`
				WHERE `template_id` = " . (int)$template_id . " LIMIT 1"
			);

			if( $query->num_rows ) {
				if( ! is_null( $data = $this->current_template_snapshot( $template_id ) ) ) {
					$query->row['data'] = $data;

				} elseif ( $query->row['data'] ) {
					$query->row['data'] = $this->object_to_array( json_decode( $this->fix_json_string( $query->row['data'] ) ) );
					$this->set_first_template_snapshot( $template_id, $query->row['data'] );

				} else {
					$query->row['data'] = array();
				}

				return $query->row;
				
			} else {
				trigger_error( sprintf( 'Template with ID #%s is missing', $template_id ) );
				return array();
			}

		} else {
			return $this->templates[ $template_id ];
		}
	}

	/**
	 * Returns profile by its ID
	 * @param int $profile_id Profile ID
	 * @return array
	 */
	public function get_profile( $profile_id ) {
		if( ! isset( $this->profiles[ $profile_id ] ) ) {

			$query = $this->db->query(
				"SELECT * FROM `" . DB_PREFIX . $this->profiles_table . "`
				WHERE `profile_id` = " . (int)$profile_id . " LIMIT 1"
			);

			if( $query->num_rows ) {
				if( $data = $this->current_profile_snapshot( $profile_id ) ) {
					$query->row['data'] = $data;

				} else {
					$query->row['data'] = $this->object_to_array( json_decode( $this->fix_json_string( $query->row['data'] ) ) );
					$this->set_first_profile_snapshot( $profile_id, $query->row['data'] );
				}

				$query->row['fields'] = explode( ',', $query->row['fields'] );
				$query->row['inputs'] = explode( ',', $query->row['inputs'] );
				$query->row['content_fields'] = explode( ',', $query->row['content_fields'] );

				return $query->row;

			} else {
				trigger_error( sprintf( "Profile with ID #%s is missing", $profile_id ) );

				return array();
			}

		} else {
			return $this->profiles[ $profile_id ];
		}
	}

	/**
	 * Returns shortcode by its ID
	 * @param int $shortcode_id Shortcode ID
	 * @return array
	 */
	public function get_shortcode( $shortcode_id ) {
		$query = $this->db->query(
			"SELECT * FROM `" . DB_PREFIX . $this->shortcodes_table . "`
			WHERE `shortcode_id` = " . (int)$shortcode_id . " LIMIT 1"
		);

		if( $query->num_rows ) {
			$query->row['data'] = $this->object_to_array(
				json_decode( $this->fix_json_string( $query->row['data'] ) )
			);

			return $query->row;

		} else {

			return array();
		}
	}

	/**
	 * Deletes shortcode
	 * @param int $shortcode_id Shortcode ID
	 * @return boolean Operation result
	 */
	public function delete_shortcode( $shortcode_id ) {
		if( ! is_numeric( $shortcode_id ) ) {
			trigger_error( 'Shortcode ID is not numeric' );
			return false;
		}

		$this->db->query(
			"DELETE FROM `" . DB_PREFIX . $this->shortcodes_table . "`
			WHERE `shortcode_id` = " . (int)$shortcode_id
		);

		return $this->db->countAffected() > 0;
	}

	/**
	 * Returns shortcodes by their category
	 * @param string $category Category name 
	 * @return array
	 */
	public function get_shortcodes_by_category( $category ) {
		$ret = array();

		foreach( $this->get_shortcodes() as $id => $shortcode ) {
			if( $shortcode['category'] === $category ) {
				$ret[ $id ] = $shortcode;
			}
		}

		return $ret;
	}

	/**
	 * Saves shortcode data
	 * @param array $data Shortcode data
	 * @return boolean Operation result
	 */
	public function save_shortcode( $data ) {
		if( empty( $data['category'] ) ) {
			trigger_error( 'Shortcode category is missing' );
			return false;
		}

		if( empty( $data['data'] ) ) {
			trigger_error( 'Shortcode data are missing' );
			return false;
		}

		if( ! is_array( $data['data'] ) ) {
			trigger_error( 'Shortcode data are not an array' );
			return false;
		}

		if( isset( $data['shortcode_id'] ) ) {
			if( ! is_numeric( $data['shortcode_id'] ) ) {
				trigger_error( 'Shortcode ID is not numeric' );
				return false;
			}

			$id = $data['shortcode_id'];

			$this->db->query(
				"UPDATE `" . DB_PREFIX . $this->shortcodes_table . "`
				SET `data` = '" . json_encode( $data['data'], JSON_HEX_QUOT ) . "'
				WHERE `shortcode_id` = '" . (int)$id . "'"
			);

		} else {
			$this->db->query(
				"INSERT INTO `" . DB_PREFIX . $this->shortcodes_table . "`
				(`category`,`data`)
				VALUES (
					'" . $this->db->escape( $data['category'] ) . "',
					'" . json_encode( $data['data'], JSON_HEX_QUOT ) . "'
				)"
			);

			$id = $this->db->getLastId();
		}

		if( $this->db->countAffected() > 0 ) {
			return $id;
		}

		return false;
	}

	/**
	 * Returnes name for complexed shortcodes (eg vitrine)
	 * @param array $shortcode Shortcode data
	 * @return string
	 */
	public function get_shortcode_name( $shortcode ) {
		$shortcode = new Shortcode();
		return $shortcode->brace_shortcode_name( $shortcode['category'] . '(' . $shortcode['shortcode_id'] . ')' );
	}

	/**
	 * Brace shortcode name into braces
	 * @param string $name Shortcode name 
	 * @return string
	 */
	// public function brace_shortcode_name( $name ) {
	// 	return '{' . $name . '}';
	// }

	/**
	 * Returns list of all system shortcodes
	 * @return array
	 */
	public function get_shortcodes() {
		if( is_null( $this->shortcodes ) ) {
			$ret = array();
			$query = $this->db->query( "SELECT * FROM `" . DB_PREFIX . $this->shortcodes_table . "`" );

			foreach( $query->rows as $row ) {
				$ret[ $row['shortcode_id'] ] = $row;

				if( $row['data'] ) {
					$ret[ $row['shortcode_id'] ]['data'] = $this->object_to_array( json_decode( $row['data'] ) );

				} else {
					$ret[ $row['shortcode_id'] ]['data'] = array();
				}
			}

			$this->shortcodes = $ret;
		}

		return $this->shortcodes;		
	}

	/**
	 * Returns profile mapping
	 * @param int|null $store_id Store ID
	 * @param string|null $lang_code Language code
	 * @param int|null $template_id Template ID
	 * @param string $name Value name. Optional, default - profile_id
	 * @return array
	 */
	public function get_profile_mapping( $store_id = null, $lang_code = null, $template_id = null ) {
		$ret = null;
		$store_id = is_null( $store_id ) || ! is_numeric( $store_id ) ? -1 : (int)$store_id;
		$template_id = is_null( $template_id ) || ! is_numeric( $template_id ) ? -1 : (int)$template_id;
		$lang_code = $this->db->escape( $lang_code );

		$query = $this->db->query(
			" SELECT
				( SELECT `profile_id` FROM `" . DB_PREFIX . $this->profile_mapping_table . "`
					WHERE `level` = 'store' AND `id` = $store_id LIMIT 1) as store,
				( SELECT `profile_id` FROM `" . DB_PREFIX . $this->profile_mapping_table . "`
					WHERE `level` = 'lang' AND `id` = '$lang_code' LIMIT 1) as lang,
				( SELECT `profile_id` FROM `" . DB_PREFIX . $this->profile_mapping_table . "`
					WHERE `level` = 'template' AND `id` = $template_id LIMIT 1 ) as template"
			);

		if( ! is_null( $query->row['template'] ) ) {
			$ret = $query->row['template' ];

		} elseif( ! is_null( $query->row['lang'] ) ) {
			$ret = $query->row['lang' ];

		} elseif( ! is_null( $query->row['store'] ) ) {
			$ret = $query->row['store' ];
		}

		return $ret;

	}

	/**
	 * Returns session based configuration
	 * @param string $name Setting name
	 * @return mixed
	 */
	public function personal_config( $name ) {
		return isset( $this->session->data['adk']['settings'][ $name ] ) ?
			$this->session->data['adk']['settings'][ $name ] : $this->config( $name );
	}

	/**
	 * Returns global profile mappings data
	 * @return array
	 */
	public function get_profile_mappings() {
		if( is_null( $this->profile_mappings ) ) {
			$query = $this->db->query( "SELECT * FROM `" . DB_PREFIX . $this->profile_mapping_table . "`" );
			$this->profile_mappings = array();

			foreach( $query->rows as $row ) {
				if( ! isset( $this->profile_mappings[ $row['level'] ] ) ) {
					$this->profile_mappings[ $row['level'] ] = array();
				}

				$this->profile_mappings[ $row['level'] ][ $row['id'] ] = array(
					'profile'     => isset( $row['profile_id'] ) ? $row['profile_id'] : '',
					'enabled'     => isset( $row['enabled'] ) ? (boolean)$row['enabled'] : 1,
					'track'       => isset( $row['track'] ) ? (boolean)$row['track'] : 1,
					'log'         => isset( $row['log'] ) ? (boolean)$row['log'] : 1,
					'track_visit' => isset( $row['track_visit'] ) ? (boolean)$row['track_visit'] : 1,
				);
			}
		}

		return $this->profile_mappings;
	}

	/**
	 * Returns template configuration
	 * @param string $name Configuration name
	 * @param int $template_id Template ID
	 * @return boolean|null
	 */
	public function get_configuration( $name, $template_id ) {
		$configs = $this->get_profile_mappings();
		$ret = 0;

		if ( isset( $configs['template'][ $template_id ][ $name ] ) ) {
			$ret = $configs['template'][ $template_id ][ $name ];

		} else {
			$ret = 1;
		}

		if ( $ret ) {
			return $ret;
		}

		if ( 'enabled' === $name ) {
			return $ret;
		}

		$language_code = $this->get_language_code();

		if ( isset( $configs['lang'][ $language_code ][ $name ] ) ) {
			$ret = $configs['lang'][ $language_code ][ $name ];

		} else {
			$ret = 1;
		}

		if ( $ret ) {
			return $ret;
		}

		$store_id = is_null( $this->config->get( 'config_store_id' ) ) ? 0 :
			$this->config->get( 'config_store_id' ); 

		if ( isset( $configs['store'][ $store_id ][ $name ] ) ) {
			$ret = $configs['store'][ $store_id ][ $name ] ;

		} else {
			$ret = 1;
		}

		return $ret;
	}

	/**
	 * Creates specified array structure
	 * @param array &$array Target array
	 * @param string $path Slash separated path of structure to be created
	 * @return array
	 */
	// public function &create_array_structure( &$array, $path ) {
	// 	$parts = explode( '/', $path );

	// 	if( $parts ) {
	// 		foreach( $parts as $part ) {
	// 			if( ! isset( $array[ $part ] ) ) {
	// 				$array[ $part ] = array();
	// 			}

	// 			$array = &$array[ $part ];
	// 		}
	// 	}

	// 	return $array;
	// }

	/**
	 * Adds snapshot to the session
	 * @param int $id Snapshot ID
	 * @param array $snapshot Snapshot data
	 * @param  string $object Snapshot type
	 * @return int Snapshot depth
	 */
	protected function add_snapshot( $id, $snapshot, $object ) {
		$snapshots =& $this->create_array_structure( $this->session->data, 'adk_mail/temp/' . $object . '/' . $id );
		$pointer =& $this->snapshot_pointer( $id, $object );

		if( $pointer !== count( $snapshots ) - 1 ) {
			array_splice( $snapshots, $pointer + 1 );
		}

		array_push( $snapshots, $snapshot );

		if( count( $snapshots ) > $this->max_temp_stack ) {
			$snapshots = array_slice( $snapshots, $this->max_temp_stack * -1 );
		}

		$depth = count( $snapshots );
		$pointer = $depth - 1;

		return $depth;
	}

	/**
	 * Adds profile snapshot into session stack
	 * @param int $id Profile ID 
	 * @param array $snapshot Snapshot data
 	 * @return int Depth of snapshots stack
	 */
	public function add_profile_snapshot( $id, $snapshot ) {
		if( ! is_numeric( $id ) ) {
			trigger_error( 'ID is not numeric' );
			return false;
		}

		if( ! is_array( $snapshot ) ) {
			trigger_error( 'Profile snapshot data is not an array' );
			return false;
		}

		return $this->add_snapshot( $id, $snapshot, 'profile' );
	}

	/**
	 * Adds template snapshot into session stack
	 * @param int $id Template ID 
	 * @param array $snapshot Snapshot data
 	 * @return int Depth of snapshots stack
	 */
	public function add_template_snapshot( $id, $snapshot ) {
		if( ! is_numeric( $id ) ) {
			trigger_error( 'ID is not numeric' );
			return false;
		}

		if( ! is_array( $snapshot ) ) {
			trigger_error( 'Template snapshot data is not an array' );
			return false;
		}

		return $this->add_snapshot( $id, $snapshot, 'template' );
	}

	/**
	 * Checks if snapshot stack has snapshots in it
	 * @param int $id Snapshot stack ID 
	 * @param string $object Snapshot type 
	 * @return boolean
	 */
	protected function has_snapshots( $id, $object ) {
		if( ! isset( $this->session->data['adk_mail']['temp'][ $object ][ $id ] ) ) {
			return false;
		}

		return count( $this->session->data['adk_mail']['temp'][ $object ][ $id ] );
	}

	/**
	 * Checks if profile snapshots stack has snapshots in it
	 * @param int $id Profile ID 
	 * @return boolean
	 */
	public function has_profile_snapshots( $id ) {
		return $this->has_snapshots( $id, 'profile' );
	}

	/**
	 * Checks if templates snapshots stack has snapshots in it
	 * @param int $id Template ID 
	 * @return boolean
	 */
	public function has_template_snapshots( $id ) {
		return $this->has_snapshots( $id, 'template' );
	}

	/**
	 * Returns top snapshot from the stack
	 * @param int $id Stack ID 
	 * @param string $object Snapshot type 
	 * @return array
	 */
	protected function last_snapshot( $id, $object ) {
		$count = $this->has_snapshots( $id, $object );
		if( ! $count ) {
			return array();
		}

		$pointer =& $this->snapshot_pointer( $id, $object );

		$last = $this->session->data['adk_mail']['temp'][ $object ][ $id ][ $count - 1 ];
		$pointer = $count - 1;

		return $last;
	}

	/**
	 * Returns top snapshot from profiles stack
	 * @param int $id Profile ID
	 * @return array
	 */
	public function last_profile_snapshot( $id ) {
		$this->last_snapshot( $id, 'profile' );
	}

	/**
	 * Returns top snapshot from templates stack
	 * @param int $id Template ID
	 * @return array
	 */
	public function last_template_snapshot( $id ) {
		$this->last_snapshot( $id, 'template' );
	}

	/**
	 * Removes snapshot from the stack
	 * @param int $id Stack ID
	 * @param string $object Snapshot type
	 * @return boolean Operation result
	 */
	protected function remove_snapshots( $id, $object ) {
		$count = $this->has_snapshots( $id, $object );
		if( false === $count ) {
			return true;
		}

		unset( $this->session->data['adk_mail']['temp'][ $object ][ $id ] );
		unset( $this->session->data['adk_mail']['temp']['pointers'][ $object ][ $id ] );

		return true;
	}

	/**
	 * Removes snapshot from the profile stack
	 * @param int $id Profile ID
	 * @return boolean Operation result
	 */
	public function remove_profile_snapshots( $id ) {
		return $this->remove_snapshots( $id, 'profile' );
	}

	/**
	 * Removes snapshot from the templates stack
	 * @param int $id Template ID
	 * @return boolean Operation result
	 */
	public function remove_template_snapshots( $id ) {
		return $this->remove_snapshots( $id, 'template' );
	}

	/**
	 * Removes stack pointer to the previous element and returns it
	 * @param int $id Stack ID 
	 * @param string $object Snapshot type 
	 * @return array
	 */
	protected function prev_snapshot( $id, $object ) {
		$count = $this->has_snapshots( $id, $object );

		if( ! $count ) {
			return array();
		}

		$pointer =& $this->snapshot_pointer( $id, $object );
		if( $pointer - 1 < 0 ) {
			return array();
		}

		$snapshot = $this->session->data['adk_mail']['temp'][ $object ][ $id ][ --$pointer ];

		return $snapshot;
	}

	/**
	 * Removes stack pointer to the previous element of profile stack and returns it
	 * @param int $id Profile ID 
	 * @return array
	 */
	public function prev_profile_snapshot( $id ) {
		return $this->prev_snapshot( $id, 'profile' );
	}

	/**
	 * Removes stack pointer to the previous element of template stack and returns it
	 * @param int $id Template ID 
	 * @return array
	 */
	public function prev_template_snapshot( $id ) {
		return $this->prev_snapshot( $id, 'template' );
	}

	/**
	 * Removes stack pointer to the next element and returns it
	 * @param int $id Stack ID 
	 * @param string $object Snapshot type 
	 * @return array
	 */
	protected function next_snapshot( $id, $object ) {
		$count = $this->has_snapshots( $id, $object );
		if( ! $count ) {
			return array();
		}

		$pointer =& $this->snapshot_pointer( $id, $object );

		if( $pointer + 1 >= $count ) {
			return array();
		}

		$snapshot = $this->session->data['adk_mail']['temp'][ $object ][ $id ][ $pointer++ ];

		return $snapshot;
	}

	/**
	 * Removes stack pointer to the next element of profile stack and returns it
	 * @param int $id Profile ID 
	 * @return array
	 */
	public function next_profile_snapshot( $id ) {
		return $this->next_snapshot( $id, 'profile' );
	}

	/**
	 * Removes stack pointer to the next element of template stack and returns it
	 * @param int $id Template ID 
	 * @return array
	 */
	public function next_template_snapshot( $id ) {
		return $this->next_snapshot( $id, 'template' );
	}

	/**
	 * Removes all snapshots from the stack but current
	 * @param int $id Stack ID 
	 * @param string $object Snapshot type
	 * @return array
	 */
	protected function leave_current_snapshot( $id, $object ) {
		if( ! empty( $this->session->data['adk_mail']['temp'][ $object][ $id ] ) ) {
			$pointer =& $this->snapshot_pointer( $id, $object );
			$snapshot = $this->session->data['adk_mail']['temp'][ $object ][ $id ][ $pointer ];
			$pointer = 0;
			$this->session->data['adk_mail']['temp'][ $object ][ $id ] = array( $snapshot );

			return $snapshot;
		}

		return array();
	}

	/**
	 * Removes all snapshots from the profile stack but current
	 * @param int $id Profile ID 
	 * @return array
	 */
	public function leave_current_profile_snapshot( $id ) {
		return $this->leave_current_snapshot( $id, 'profile' );
	}

	/**
	 * Removes all snapshots from the template stack but current
	 * @param int $id Template ID 
	 * @return array
	 */
	public function leave_current_template_snapshot( $id ) {
		return $this->leave_current_snapshot( $id, 'template' );
	}

	/**
	 * Makes given snapshot the only snapshot in the stack
	 * @param int $id Stack ID 
	 * @param array $data Snapshot data 
	 * @param string $object Snapshot type
	 * @return boolean Operation result
	 */
	protected function set_first_snapshot( $id, $data, $object ) {
		$snapshots =& $this->create_array_structure( $this->session->data, 'adk_mail/temp/' . $object . '/' . $id );
		$pointer =& $this->snapshot_pointer( $id, $object );

		$pointer  = 0;
		$snapshots = array( $data );

		return true;
	}

	/**
	 * Makes given snapshot the only snapshot in the profile stack
	 * @param int $id Profile ID 
	 * @param array $data Snapshot data 
	 * @return boolean Operation result
	 */
	public function set_first_profile_snapshot( $id, $data ) {
		if( ! is_numeric( $id ) ) {
			trigger_error( 'ID is not numeric' );
			return false;
		}

		if( ! is_array( $data ) ) {
			trigger_error( 'Profile data is not an array' );
			return false;
		}

		return $this->set_first_snapshot( $id, $data, 'profile' );
	}

	/**
	 * Makes given snapshot the only snapshot in the template stack
	 * @param int $id Template ID 
	 * @param array $data Snapshot data 
	 * @return boolean Operation result
	 */
	public function set_first_template_snapshot( $id, $data ) {
		if( ! is_numeric( $id ) ) {
			trigger_error( 'ID is not numeric' );
			return false;
		}

		if( ! is_array( $data ) ) {
			trigger_error( 'Template data is not an array' );
			return false;
		}

		return $this->set_first_snapshot( $id, $data, 'template' );
	}

	/**
	 * Returns current snapshot
	 * @param int $id  Stack ID
	 * @param string $object Snapshot type
	 * @return array
	 */
	protected function current_snapshot( $id, $object ) {
		if( ! empty( $this->session->data['adk_mail']['temp'][ $object ][ $id ] ) ) {
			$pointer = $this->snapshot_pointer( $id, $object );
			$snapshot = $this->session->data['adk_mail']['temp'][ $object ][ $id ][ $pointer ];

			return $snapshot;
		}

		return null;
	}

	/**
	 * Returns current snapshot in profile stack
	 * @param int $id  Profile ID
	 * @return array
	 */
	public function current_profile_snapshot( $id ) {
		return $this->current_snapshot( $id, 'profile' );
	}

	/**
	 * Returns current snapshot in template stack
	 * @param int $id Template ID
	 * @return array
	 */
	public function current_template_snapshot( $id ) {
		return $this->current_snapshot( $id, 'template' );
	}

	/**
	 * Checks whether snapshot stack can be undone
	 * @param int $id Stack ID 
	 * @param string $object Snapshot type 
	 * @return boolean
	 */
	protected function can_undo_snapshot( $id, $object ) {
		if( ! $this->has_snapshots( $id, $object ) ) {
			return false;
		}

		return $this->snapshot_pointer( $id, $object ) > 0;
	}

	/**
	 * Checks whether profile snapshots stack can be undone
	 * @param int $id Profile ID 
	 * @return boolean
	 */
	public function can_undo_profile_snapshot( $id ) {
		return $this->can_undo_snapshot( $id, 'profile' );
	}

	/**
	 * Checks whether template snapshots stack can be undone
	 * @param int $id Profile ID 
	 * @return boolean
	 */
	public function can_undo_template_snapshot( $id ) {
		return $this->can_undo_snapshot( $id, 'template' );
	}

	/**
	 * Checks whether snapshot stack can be redone
	 * @param int $id Stack ID 
	 * @param string $object Snapshot type 
	 * @return boolean
	 */
	protected function can_redo_snapshot( $id, $object ) {
		if( ! ( $count = $this->has_snapshots( $id, $object ) ) ) {
			return false;
		}

		$pointer = $this->snapshot_pointer( $id, $object ); 

		return ++$pointer < $count;
	}

	/**
	 * Checks whether profile snapshots stack can be redone
	 * @param int $id Profile ID 
	 * @return boolean
	 */
	public function can_redo_profile_snapshot( $id ) {
		return $this->can_redo_snapshot( $id, 'profile' );
	}

	/**
	 * Checks whether template snapshots stack can be redone
	 * @param int $id Profile ID 
	 * @return boolean
	 */
	public function can_redo_template_snapshot( $id ) {
		return $this->can_redo_snapshot( $id, 'template' );
	}

	/**
	 * Checks whether stack contains data that can be saved
	 * @param int $id Stack ID 
	 * @param string $object Snapshot type
	 * @return boolean
	 */
	protected function can_save_snapshot( $id, $object ) { 
		if( $this->has_snapshots( $id, $object ) < 2 || 0 === $this->snapshot_pointer( $id, $object ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Checks whether profiles stack contains data that can be saved
	 * @param int $id Profile ID 
	 * @return boolean
	 */
	public function can_save_profile_snapshot( $id ) {
		return $this->can_save_snapshot( $id, 'profile' );
	}

	/**
	 * Checks whether templates stack contains data that can be saved
	 * @param int $id Template ID 
	 * @return boolean
	 */
	public function can_save_template_snapshot( $id ) {
		return $this->can_save_snapshot( $id, 'template' );
	}

	/**
	 * Returns stack pointer
	 * @param int $id Stack ID
	 * @param string $object Snapshot type
	 * @return pointer
	 */
	public function &snapshot_pointer( $id, $object ) {
		$pointers = &$this->create_array_structure( $this->session->data, 'adk_mail/temp/pointers/' . $object );
		if( ! isset( $pointers[ $id ] ) ) {
			$pointers[ $id ] = 0;
		}

		return $pointers[ $id ];
	}

	/**
	 * Returns profiles stack pointer
	 * @param int $id Profile ID
	 * @return pointer
	 */
	public function &current_profile_snapshot_pointer( $id ) {
		return $this->snapshot_pointer( $id, 'profile' );
	}

	/**
	 * Returns templates stack pointer
	 * @param int $id Template ID
	 * @return pointer
	 */
	public function &current_template_snapshot_pointer( $id ) {
		return $this->snapshot_pointer( $id, 'template' );
	}

	/**
	 * Removes all snapshots from the session
	 * @return void
	 */
	public function clear_all_snapshots() {
		unset( $this->session->data['adk_mail']['temp'] );
	}

	/**
	 * Returns store data for all the templates stores
	 * @param array $template Template data
	 * @return array
	 */
	public function get_template_stores( $template ) {
		$existing_stores = $this->get_stores();
		$template_stores = array();

		foreach( $template['data'] as $store_id => $data ) {
			if( array_key_exists( $store_id, $existing_stores ) ) {
				$template_stores[ $store_id ] = $existing_stores[ $store_id ];
			}
		}

		return $template_stores;
	}

	/**
	 * Returns all stores as ID => name pairs
	 * @return array
	 */
	public function get_stores() {
		if( is_null( $this->stores ) ) {
			$this->stores = array();
			$query = $this->db->query( "SELECT * FROM `" . DB_PREFIX . "setting` WHERE `key` = 'config_name'" );

			foreach( $query->rows as $store ) {
				$this->stores[ $store['store_id'] ] = array( 'id'=> $store['store_id'], 'name' => $store['value'], );
			}
		}

		return $this->stores;
	}

	/**
	 * Create element's name by given path
	 * @param string $name Path in a form of one/two/three
	 * @return string
	 */
	// public function build_name( $name ) {
	// 	$name = str_replace( array( '_', '-' ), '/', $name );

	// 	$parts = explode( '/', $name );

	// 	$name = array_shift( $parts );

	// 	if( $parts ) {
	// 		$name .= '[' . implode( '][', $parts ) . ']';
	// 	}

	// 	return $name;
	// }

	/**
	 * Checks whether haystack is ending with specific needle 
	 * @param string $haystack String to be checked
	 * @param string $needle Searched ending 
	 * @return boolean
	 */
	// public function is_ended_with( $haystack, $needle ) {

	// 	$needle_length = strlen( $needle );
	// 	if( strlen( $haystack ) < $needle_length ) {
	// 		return false;
	// 	}

	// 	return strrpos( $haystack, $needle, -1 * $needle_length ) !== false;
	// }

	/**
	 * Returns URL to language flag image from language data set
	 * @param array $lang Language data set
	 * @return string
	 */
	// public function get_lang_flag_url( $lang ) {
	// 	$ret = '';

	// 	if ( version_compare( VERSION, '2.2.0.0', '<' ) ) {
	// 		if ( isset( $lang['image'] ) ) {
	// 			if ( is_file( DIR_IMAGE . 'flags/' . $lang['image'] ) ) {
	// 				$ret = DIR_IMAGE . 'flags/' . $lang['image'];
	// 			}
	// 		}

	// 	} elseif ( isset( $lang['code'] ) ) {
	// 		if ( is_file( DIR_LANGUAGE . $lang['code'] . '/' . $lang['code'] . '.png' ) ) {
	// 			$ret = DIR_LANGUAGE . $lang['code'] . '/' . $lang['code'] . '.png';
	// 		}
	// 	}

	// 	if ( $ret ) {
	// 		$ret = $this->get_store_url() . substr( $ret , strlen( dirname( DIR_SYSTEM ) ) );
	// 	}

	// 	return $ret;
	// }

	/**
	 * Returns URL for image, placed into image folder
	 * @param image path $img Image path, relative to image folder
	 * @param boolean $embed Flag, whether image is embedded
	 * @return string
	 */
	public function get_img( $img, $embed = false ) {
		if ( ! $img ) {
			return '';
		}

		if ( preg_match( '/^(https?:)?\/\//', $img ) ) {
			$url = new Url( $img );

			if ( 0 === strpos( $url->get_path(), '/image/' ) ) {
				$img = substr( $url->get_path(), 7 );

			} else {
				return '';
			}

		}

		if ( ! is_file( DIR_IMAGE . $img ) ) {
			if( is_file( DIR_IMAGE . 'no_image.png' ) ) {
				$img = 'no_image.png';

			} else {
				return '';
			}
		}

		if ( $embed ) {
			$url = $this->embed_img( DIR_IMAGE . $img );
			
		// Show image placeholder
		} elseif( defined( 'SHOW_IMAGE' ) && ! SHOW_IMAGE ) {
			$url = $this->get_img_placeholder_url( DIR_IMAGE . $img );

		// Show image itself
		} else {
			$url = URL::catalog_url() . 'image/' . $img;
		}

		return $url;
	}

	/**
	 * Returns URL for image from DIR_IMAGE folder
	 * @param string $path Absolute path to image
	 * @return string
	 */
	// public function get_img_url( $path ) {
	// 	$ret = '';

	// 	if ( strpos( $path, DIR_IMAGE ) === 0 ) {
	// 		$ret = $this->get_store_url() . substr( $path, strlen( dirname( DIR_IMAGE ) ) + 1 );
	// 	}

	// 	return $ret;
	// }

	/**
	 * Returns background image CSS notation
	 * @since 1.1.0 - rebuilt
	 * @param string $img Image path, relative to image/ folder 
	 * @param bool $embed Flag, whether image is embedded
	 * @return string
	 */
	public function get_bg_img( $img, $embed ) {
		$ret = $this->get_img( $img, $embed );

		if ( ! $ret ) {
			return '';
		}

		return "url('$ret')";
	}

	/**
	 * Returns base64 encoded image representation
	 * @param string $path File path 
	 * @return strong
	 */
	public function embed_img( $path ) {
		$ret = '';

		if( $this->message ) {
			$img = \Swift_Image::fromPath( $path );
			$ret = $this->message->embed( $img );
			$this->attached_img[] = array(
				'object' => $img,
				'url'    => $this->get_img_url( $path ),
			);

		} else {
			$info = getimagesize( $path );
			$ret = 'data:' . $info['mime'] . ';base64,' . base64_encode( file_get_contents( $path ) );
		}

		return $ret;
	}

	/**
	 * Returns Swift Mailer message instance
	 * @return object
	 */
	public function get_message_instance() {
		if( ! $this->message ) {
			require_once( $this->swift_loader );
			$this->message = \Swift_Message::newInstance( 'test' );
		}

		return $this->message;
	}

	/**
	 * Returns URl of image placeholder shape
	 * @param string $path Image path
	 * @return string
	 */
	public function get_img_placeholder_url( $path ) {
		$size = getimagesize( $path );
		$r = 154;
		$g = 174;
		$b = 185;
		$file = sprintf( 'cache/placeholder-%s-%s-%s-%sx%s.png', $r, $g, $b, $size[0], $size[1] ); 
		$path = DIR_IMAGE . $file;
		$fs = new Fs();

		$fs->mkdir( dirname( $path ) );

		if( ! is_file( $path ) ) {
			file_put_contents( $path, '' );
			$im = imagecreate( $size[0], $size[1] );
			$color = imagecolorallocate( $im, $r, $g, $b );
			imagefill( $im, 0, 0, $color );
			imagepng( $im, $path );
			imagedestroy($im);
		}

		return URL::catalog_url() . 'image/' . $file;
	}

	/**
	 * Returns caller signature (file name - function - some distinction)
	 * @since 1.1.0 - rebuilt
	 * @param object $mail OpenCart mailer
	 * @return string
	 */
	public function get_caller( $mail ) {
		global $adk_mail_hook;

		$stack = debug_backtrace( null );
		$this->caller_args = $stack[2]['args'];
		$function = '';
		$hook = array();

		if( isset( $stack[1]['file'] ) ) {
			$caller = strtolower( $this->undo_modifications( $stack[1]['file'] ) );
			$caller = str_replace( DIRECTORY_SEPARATOR, '/', $caller );

		} else {
			$this->email_msg( 'Caller file name is empty. Bounce to default caller', 'warn' );
			return '';
		}

		if( isset( $stack[2]['function'] ) ) {
			$function = strtolower( $stack[2]['function'] );
		}

		// Custom hook
		$this->path_hook = $caller . ( $function ? '-' . $function : '' );

		// Custom hook
		if ( ! is_null( $adk_mail_hook ) ) {
			$hook = $adk_mail_hook;
			$adk_mail_hook = null;

		} else {
			switch( $caller ) {
			case '/admin/controller/common/forgotten.php' :
				$hook[] = 'admin.forgotten';
				break;
			case '/admin/controller/marketing/contact.php' :
				$hook[] = 'newsletter';

				if( isset( $this->request->post['to'] ) ) {
					$hook[] = $this->request->post['to'];
				}

				break;
			case '/admin/model/customer/customer.php' :
				$hook[] = 'customer';

				if ( $function ) {
					$hook[] = $function;
				}

				break;
			case '/admin/model/marketing/affiliate.php' :
				$hook[] = 'affiliate';

				if ( $function ) {
					$hook[] = $function;
				}

				break;
			case '/admin/model/sale/return.php': 
				$hook[] = 'customer';

				if ( 'addreturnhistory' === $function ) {
					$hook[] = 'addreturnhistory';
				}

				break;
			case '/admin/model/sale/voucher.php' :
				$hook[] = 'customer';

				if ( 'sendvoucher' === $function ) {
					$hook[] = 'sendvoucher';
				}

				break;
			case '/catalog/controller/account/forgotten.php' :
				$hook[] = 'customer.forgotten';
				break;
			case '/catalog/controller/affiliate/forgotten.php' :
				$hook[] = 'affiliate.forgotten';
				break;
			case '/catalog/controller/information/contact.php' :
				$hook[] = 'admin.enquiry';
				break;
			case '/catalog/model/account/customer.php' :
				if ( 'addcustomer' === $function && isset( $this->caller_args[0]['email'] ) ) {

					// Confirmation email to customer
					if( $mail->to === $this->caller_args[0]['email'] ) {
						$hook[] = 'customer.new';

					// Alert email to admin
					} else {
						$hook[] = 'admin.newcustomer';
					}
				}

				break;
			case '/catalog/model/affiliate/affiliate.php' :
				if ( 'addaffiliate' === $function && isset( $this->caller_args[0]['email'] ) ) {

					// Confirmation email to customer
					if( $mail->to === $this->caller_args[0]['email'] ) {
						$hook[] = 'affiliate.new';

					// Alert email to admin
					} else {
						$hook[] = 'admin.newaffiliate';
					}

				} elseif ( 'addtransaction' === $function ) {
					$hook[] = 'affiliate';

					if ( $function ) {
						$hook[] = $function;
					}
				}

				break; 
			case '/catalog/model/catalog/review.php' :
				$hook[] = 'admin.review';
				break;
			case '/catalog/model/checkout/order.php' :
				if ( 'addorderhistory' === $function &&

					// Order ID
					isset( $this->caller_args[0] ) &&

					// New order status ID
					isset( $this->caller_args[1] )
				) {

					$old_order = $this->get_from_cache( 'old_order' );

					// Confirmation email to customer
					if( $old_order && $mail->to === $old_order['email'] ) {
						$hook[] = 'customer.order';

					// Alert email to admin
					} else {
						$hook[] = 'admin.order';
					}

					if ( $old_order && isset( $old_order['order_status_id'] ) &&
						! $old_order['order_status_id'] && $this->caller_args[1] ) {
						$hook[] = 'new';

					} else {
						$hook[] = 'update';
						$hook[] = $this->caller_args[1];
					}
				}
				break;
			default:
				if ( isset( $this->caller_args[0]['email'] ) ) {
					if ( $mail->to === $this->caller_args[0]['email'] ) {
						$hook[] = 'customer';

					} else {
						$hook[] = 'admin';
					}

				} else {
					$hook[] = '*';
				}
			}
		}

		if ( is_array( $hook ) ) {
			$hook = implode( '.', $hook );
		}

		$this->email_msg( sprintf( 'Caller resolved into hook "%s"', $hook ) );

		return $hook;
	}

	/**
	 * Removes file name modifications made by VQMode
	 * @param string $file File name 
	 * @return string Unmodified file name
	 */
	public function undo_modifications( $file ) {
		$d = DIRECTORY_SEPARATOR;
		$this->email_msg( sprintf( 'Undoing possible file modifications for file "%s"', $file ) );
		$file = strtolower( $file );

		// File was VQModed
		if( strpos( $file, "vqmod{$d}vqcache$d" ) !== false ) {
			$this->email_msg( 'VQMod modification detected' );
			$file = preg_replace( '/^[^-]+-/', '', $file );
			$this->email_msg( sprintf( 'VQMod removed: "%s"', $file ) );

			// File was OCModed as well
			if( strpos( $file, 'system_storage_modification_' ) === 0 ) {
				$this->email_msg( 'OCMod modification detected' );
				$file = substr( $file , 28 );
				$this->email_msg( sprintf( 'OCMod modification removed: "%s"', $file ) );
			}

			// OC <= 2.0.3.1
			elseif( strpos( $file, 'system_modification_' ) === 0 ) {
				$this->email_msg( 'OCMod modification detected' );
				$file = substr( $file , 20 );
				$this->email_msg( sprintf( 'OCMod modification removed: "%s"', $file ) );
			}

			$this->email_msg( 'Start file name reconstruction' );

			// Reconstruct file name
			$parts = explode( '_', $file );
			$may_be_file = implode( $d, $parts );

			while ( ! is_file( dirname( DIR_SYSTEM ) . $d . $may_be_file ) && ! empty( $parts ) ) {
				$tail = array_pop( $parts );
				$may_be_file = implode( $d, $parts ) . '_' . $tail;
				$this->email_msg( sprintf( 'Checking file name "%s"', $may_be_file ) );
			}

			$file = $may_be_file;

			if ( empty( $parts ) ) {
				trigger_error( 'Fail to construct file name' );
			}

		} elseif ( strpos( $file, "{$d}storage{$d}modification{$d}" ) !== false ) {
			$this->email_msg( 'OCMod modification detected' );
			$file = substr( $file, strlen( DIR_SYSTEM . "storage{$d}modification{$d}" ) );

		// OC <= 2.0.3.1
		} elseif ( strpos( $file, "{$d}modification{$d}" ) !== false ) {
			$this->email_msg( 'OCMod modification detected' );
			$file = substr( $file, strlen( DIR_SYSTEM . "modification{$d}" ) );

		} else {
			$file = substr( $file, strlen( dirname( DIR_SYSTEM ) ) );
		}

		$file = $d . ltrim( $file, $d );
		$this->email_msg( sprintf( 'Output file name: "%s"', $file ) );

		return $file;
	}

	/**
	 * Returns route to a template file
	 * @param string $route Route
	 * @return string
	 */
	// public function get_view_route( $route ) {
	// 	if( version_compare( VERSION, '2.2.0.0', '>=' ) ) {
	// 		return $route;
	// 	}

	// 	if ( file_exists( DIR_TEMPLATE . $this->config->get('config_template') . '/template/' . $route . '.tpl' ) ) {
	// 		$template_file = $this->config->get( 'config_template' ) . '/template/' . $route . '.tpl';

	// 	} else {
	// 		$template_file = 'default/template/' . $route . '.tpl';
	// 	}

	// 	return $template_file;
	// }

	/**
	 * Checks whether one of the templates has specific hook action
	 * @since 1.1.0 - rebuilt
	 * @param string $caller Hook action
	 * @return boolean|array Template data in case of positive match - false otherwise
	 */
	public function has_action( $caller ) {
		$template = null;

		// Custom template
		if ( $this->path_hook ) {
			$template = $this->q( array(
				'table' => $this->templates_table,
				'query' => 'select',
				'where' => array(
					'field'     => 'path_hook',
					'operation' => '=',
					'value'     => $this->path_hook,
				),
				'limit' => 1,
			) );
		} 

		$hooks = array( $caller );
		$parts = explode( '.', $caller );
		array_pop( $parts );

		// Collect all the possible fall-back combinations
		while( $parts ) {
			$hooks[] = implode( '.', $parts );
			array_pop( $parts );
		}

		if ( ! in_array( '*', $hooks ) ) {
			$hooks[] = '*';
		}

		$templates = $this->q( array(
			'table' => $this->templates_table,
			'where' => array(
				'field'     => 'hook',
				'operation' => 'in',
				'value'     => $hooks,
			),
			'order_by' => array(
				'length(hook)' => 'desc',
			),
		) );

		foreach( $templates as $t ) {
			$template->append( $t );
		}

		// Filter disabled templates
		foreach( $template as $t ) {
			if ( $this->get_configuration( 'enabled', $t['template_id'] ) ) {
				$t = $this->get_mail_template( $t['template_id'] );

				return $t;
			}
		}

		return false;
	}

	/**
	 * Recursively creates directory
	 * @since 1.1.0
	 * @param string $path 
	 * @param int $mode Newly created directories permissions 
	 * @return boolean Operation result
	 */
	// public function mkdir( $path, $mode = 0755 ) {
	// 	$path = $this->plant_file( $path );

	// 	if( false === $path ) {
	// 		return $path;
	// 	}

	// 	$path = trim( substr( $path, strlen( dirname( DIR_SYSTEM ) ) ), DIRECTORY_SEPARATOR );
	// 	$current_path = dirname( DIR_SYSTEM ) . '/';
	// 	$created = array();

	// 	try {
	// 		foreach( explode( DIRECTORY_SEPARATOR, $path ) as $part ) {
	// 			if( ! is_dir( $current_path . $part ) ) {
	// 				if( ! mkdir( $current_path . $part ) ) {
	// 					$this->exception( 'Error' );
	// 				}

	// 				chmod( $current_path . $part, $mode );
	// 				$created[] = $current_path . $part;
	// 			}

	// 			$current_path .= $part . '/';
	// 		}

	// 	} catch ( Adk_Exception $e ) {
	// 		foreach( $created as $dir ) {
	// 			if( is_dir( $dir ) ) {
	// 				rmdir( $dir );
	// 			}

	// 			return false;
	// 		}
	// 	}

	// 	return true;
	// }

	// /**
	//  * Recursively deletes folder with its content 
	//  * @param sting $dir Directory name 
	//  * @return void
	//  */
	// public function rmdir( $dir ) {
	// 	if ( is_file( $dir ) ) {
	// 		unlink( $dir );

	// 	} elseif ( is_dir( $dir ) ) {
	// 		$dir = rtrim( $dir, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;

	// 		foreach( scandir( $dir ) as $item ) {
	// 			if ( '.' === $item || '..' === $item ) {
	// 				continue;
	// 			}

	// 			$this->rmdir( $dir . $item );
	// 		}
			
	// 		rmdir( $dir );
	// 	}
	// }

	/**
	 * Sends email
	 * @since 1.1.0 - modified
	 * @param object $mail System email object 
	 * @param array $template Template data
	 * @return boolean
	 */
	public function modify_mail( $mail, $template ) {
		$this->modified_mail = $mail;
		$storeId = $this->get_store();
		$langCode = $this->get_language_code();
		$return = false;
		$mail->to = trim( $mail->to );
		$mail->from = trim( $mail->from );
		$mail->sender = trim( $mail->sender );
		$mail_regexp = '/^[A-Za-z0-9._+-]+@[A-Za-z0-9._-]+\.[A-Za-z]{2,4}$/';
		$fs = new Fs();

		if ( property_exists( $mail, 'reply_to' ) ) {
			$mail->reply_to = trim( $mail->reply_to );
			
		} elseif ( property_exists( $mail, 'replyto' ) ) {
			$mail->reply_to = trim( $mail->replyto );

		} else {
			$mail->reply_to = '';
		}

		// To field
		$to = $mail->to;
		if( ! preg_match( $mail_regexp, $to ) ) {
			$this->email_msg(
				$this->__( 'Recipient email address ("%s") is invalid. Abort email sending', $to ),
				'error'
			);

			return false;
		}

		$this->email_msg( $this->__( 'Start initialization of email to %s', $to ), 'notification' );
		$to_name = '';

		if ( strpos( $template['hook'], 'admin.' ) !== 0 ) {
			if ( $this->adk_subscriber_name ) {
				$to_name = $this->adk_subscriber_name;

			} else {
				$to_name = $this->shortcode_customer_full_name();
			}
		}

		if ( $to_name ) {
			$to = array( $to => $to_name );

		} else {
			$to = array( $to );
		}

		// Mail subject
		$subject = $this->get_template_content( $template, 'subject', $storeId, $langCode );
		if( ! $subject ) {
			$subject = $mail->subject;
		}

		// Sender name
		$from_name = $this->get_template_data( $template, 'from_name', $storeId, $langCode );

		if ( ! $from_name && $mail->sender && ! preg_match( $mail_regexp, $mail->sender ) ) {
			$from_name = $mail->sender;
		} 

		// Mail from
		// 1. Take from template
		// 2. Take from original email
		// 3. Take "Sender" field from original email
		$from_address = trim( $this->get_template_data( $template, 'from_email', $storeId, $langCode ) );
		$from = '';

		if ( $from_address ) {
			if ( preg_match( $mail_regexp, $from_address ) ) {
				$from = $from_address;
				$this->email_msg( $this->__( 'Using FROM field form template' ) );

			} else {
				$this->email_msg( $this->__( 'Template FROM field is invalid' ), 'warn' );
			}

		} else {
			$this->email_msg( $this->__( 'Template FROM field is empty' ) );
		}

		if ( ! $from ) {
			if ( $mail->from ) {
				if ( preg_match( $mail_regexp, $mail->from ) ) {
					$from = $mail->from;
					$this->email_msg( $this->__( 'Using FROM field form original email' ) );

				} else {
					$this->email_msg( $this->__( 'Original email FROM field is invalid' ), 'warn' );
				}

			} else {
				$this->email_msg( $this->__( 'Original email FROM field is empty' ) );
			}
		}

		if ( ! $from ) {
			if ( $mail->sender ) {
				if ( preg_match( $mail_regexp, $mail->sender ) ) {
					$from = $mail->sender;
					$this->email_msg( $this->__( 'Using SENDER field from original email as FROM field' ) );

				} else {
					$this->email_msg( $this->__( 'Original email SENDER field is invalid' ), 'warn' );
				}

			} else {
				$this->email_msg( $this->__( 'Original email SENDER field is empty' ) );
			}
		}

		if ( ! $from ) {
			$this->email_msg( $this->__( 'FROM field is empty. Skip email sending' ), 'error' );

			return false;
		}

		if ( $from_name ) {
			$from = array( $from => $from_name );

		} else {
			$from = array( $from );
		}

		$this->email_msg(
			sprintf( 'From %s', str_replace( array( '<', '>', ), '', $this->print_email_address( $from ) ) ),
			'notification'
		);

		// Reply to
		$reply = '';
		$reply_to = trim( $this->get_template_data( $template, 'return_path', $storeId, $langCode ) );
		$default_reply_to = $mail->reply_to;

		if( $reply_to ) {
			if( preg_match( $mail_regexp, $reply_to ) ) {
				$reply = $reply_to;
				$this->email_msg( $this->__( 'Using REPLY_TO from template' ) );

			} else {
				$this->email_msg( $this->__( 'Template REPLY-TO field is invalid: "%s"', $reply_to ), 'warn' );
			}

		} else {
			$this->email_msg( $this->__( 'Template REPLY-TO field is empty' ), 'warn' );
		}

		if ( ! $reply ) {
			if( $default_reply_to ) {
				if( preg_match( $mail_regexp, $default_reply_to ) ) {
					$reply = $reply_to;
					$this->email_msg( $this->__( 'Using REPLY_TO from original email' ) );

				} else {
					$this->email_msg( $this->__( 'Original email REPLY-TO field is invalid: "%s"', $default_reply_to ), 'warn' );
				}

			} else {
				$this->email_msg( $this->__( 'Original email REPLY-TO field is empty' ), 'warn' );
			}
		}

		if ( ! $reply ) {
			$reply =  $this->fetch_email_addr( $from );
			$this->email_msg( $this->__( 'Using FROM value as REPLY-TO' ) );
		}

		// Sender
		$sender = $mail->sender;
		if( ! preg_match( $mail_regexp, $sender ) ) {
			$this->email_msg( $this->__( 'SENDER field "%s" is invalid. Use FROM value', $sender ), 'warn' );
			$sender = $this->fetch_email_addr( $from );
		}

		// Carbon copy
		$cc = $this->get_template_data( $template, 'cc', $storeId, $langCode );
		if ( ! $cc && isset( $mail->cc ) ) {
			$cc = $mail->cc;
		}

		$cc_array = array();

		if( $cc ) {
			foreach( explode( ',', $cc ) as $cc_email ) {
				$cc_email = trim( $cc_email );
				if( ! preg_match( $mail_regexp, $cc_email ) ) {
					$this->email_msg(
						$this->__( 'Cc address "%s" is omitted due to invalid format', $cc_email ),
						'warn'
					);

				} else {
					$cc_array[] = $cc_email;
				}
			}
		}

		// Blind carbon copy
		$bcc = $this->get_template_data( $template, 'bcc', $storeId, $langCode );
		if ( ! $bcc && isset( $mail->bcc ) ) {
			$bcc = $mail->bcc;
		}

		$bcc_array = array();

		if( $bcc ) {
			foreach( explode( ',', $bcc ) as $bcc_email ) {
				$bcc_email = trim( $bcc_email );
				if( ! preg_match( $mail_regexp, $bcc_email ) ) {
					$this->email_msg(
						$this->__( 'Bcc address "%s" is omitted due to invalid format', $bcc_email ),
						'warn'
					);

				} else {
					$bcc_array[] = $bcc_email;
				}
			}
		}

		// Attachments
		$attachments = array();

		// Fetch all the OpenCart attachments, they are embedded by default 
		if( is_array( $mail->attachments ) ) {
			foreach( $mail->attachments as $attachment ) {
				if( ! file_exists( $attachment ) ) {
					$this->email_msg( 
						$this->__( 'Attachment "%s" does not exist. FIle skipped', $attachment ),
						'warn'
					);

					continue;
				}

				$attachments[] = array(
					'path'  => $attachment,
					'embed' => true,
				);
			}
		}

		// Fetch all the custom attachments
		$mail_attachments = $this->get_template_data( $template, 'attachment', $storeId, $langCode );
		if( $mail_attachments ) {
			$mail_attachments = json_decode( html_entity_decode( $mail_attachments ) );

			if( $mail_attachments ) {
				foreach( $this->object_to_array( $mail_attachments ) as $attachment ) {
					$file = dirname( $this->attachments_root ) .
						DIRECTORY_SEPARATOR . trim( $attachment['path'], DIRECTORY_SEPARATOR ) .
						DIRECTORY_SEPARATOR . $attachment['name'];

					if( ! file_exists( $file ) ) {
						$this->email_msg(
							$this->__( 'Attachment %s does not exist. FIle skipped', $file ),
							'warn'
						);

						continue;
					}

					$attachments[] = array(
						'path'  => $file,
						'embed' => ! empty( $attachment['embed'] ) ? true : false,
						'mime'  => $attachment['mime'],
					);
				} 

			} else {
				$this->email_msg(
					$this->__( 'Error occurred while fetching attachment data. Attachments omitted' )
				);
			}
		}

		$this->email_msg( $this->__( 'Initializing sender...' ) );
		$mailer = $this->get_mailer_from_mail( $mail );

		$this->email_msg( $this->__( 'Instantiating message class...' ) );
		$message = Swift_Message::newInstance( $subject );
		$this->message = $message;

		$this->email_msg( $this->__( 'Rendering contents...' ), 'notification' );
		$this->email_msg( $this->__( 'Using template "%s"', $template['name' ] ) );

		$html = $this->render_mail_template( $template['template_id'] );
		$text = $this->html_to_text( $html );
		$html = $this->fetch_html_variants( $html );
		$return = true;

		$this->message = null;

		if ( function_exists( 'mb_internal_encoding' ) && ( (int)ini_get( 'mbstring.func_overload' ) ) & 2 ) {
			$mbEncoding = mb_internal_encoding();
			mb_internal_encoding('ASCII');
		}

		$message
			->setSender( $sender )
			->setFrom( $from )
			->setTo( $to )
			->setBody( $html, 'text/html' )
			->addPart( $text )
			->setSubject( $subject )
			->setReplyTo( $reply );

		if ( $ret_p = $this->config( 'imap_login' ) ) {
			$message->setReturnPath( $ret_p );
		}

		if( $cc_array ) {
			$message->setCc( $cc_array );
		}

		if( $bcc_array ) {
			$message->setBcc( $bcc_array );
		}

		$attachment_list = array();

		foreach( $attachments as $attachment ) {
			$mail_attachment = \Swift_Attachment::fromPath( $attachment['path'] );

			if( $attachment['embed'] ) {
				$mail_attachment->setDisposition( 'inline' );
			}

			if( isset( $attachment['mime'] ) ) {
				$mail_attachment->setContentType( $attachment['mime'] );
			}

			$message->attach( $mail_attachment );
			$attachment_list[] = substr( $attachment['path'], strlen( $this->attachments_root ) );
		}

		$attachment_list = implode( ', ', $attachment_list );

		// Create archive copy on demand
		if ( $this->archive_file ) {
			$fs->mkdir( dirname( $this->archive_file ) );

			if ( file_put_contents( $this->archive_file, $this->detach_img( $html ) ) ) {
				$this->email_msg( $this->__( 'Archive copy "%s" was created for this message', $this->archive_file ) );

			} else {
				$this->email_msg( $this->__( 'Failed to create archive copy for email', 'warn' ) );
			}

			$this->archive_file = null;
		}

		if ( isset( $mbEncoding ) ) {
			mb_internal_encoding( $mbEncoding );
		}

		$mail_data = array(
			'attachment'        => $attachment_list,
			'log'               => $this->email_log,
			'tracking_id'       => $this->tracking_id,
			'tracking_visit_id' => $this->tracking_visit_id,
			'template_name'     => $template['name'],
			'add_history'       => $this->get_configuration( 'log', $template['template_id'] ) ||
				$this->get_configuration( 'track', $template['template_id'] ) ||
				$this->get_configuration( 'track_visit', $template['template_id'] ),
			'newsletter'        => $this->adk_newsletter_id,
		);

		$this->email_log = '';

		if ( $this->config( 'queue' ) ) {
			$return = $this->put_to_queue( $mailer, $message, $mail_data );

		} else {
			$return = $this->run_mailer( $mailer, $message, $mail_data );
		}

		return $return;
	}

	/**
	 * Runs email mailer
	 * @param object $mailer Mailer instance 
	 * @param object $message Message instance
	 * @param string $attachment_list List of attached filed
	 * @param string $log Log
	 * @param string $tracking_id Mail tracking code
	 * @return boolean Operation status
	 */
	public function run_mailer( $mailer, $message, $data ) {
		$this->email_msg( $this->__( 'Initializing logger plugin...' ), 'notification', $data['log'] );
		$logger = new \Swift_Plugins_Loggers_ArrayLogger();
		$mailer->registerPlugin( new \Swift_Plugins_LoggerPlugin( $logger ) );

		if ( $this->config( 'throttle-item' ) ) {
			$this->email_msg( $this->__( 'Initializing throttle (item per minute) plugin...' ), 'notification', $data['log'] );
			$mailer->registerPlugin(
				new \Swift_Plugins_ThrottlerPlugin(
					(int)$this->config( 'throttle-item' ),
					\Swift_Plugins_ThrottlerPlugin::MESSAGES_PER_MINUTE
				)
			);
		}

		if ( $this->config( 'throttle-traffic' ) ) {
			$this->email_msg( $this->__( 'Initializing throttle (MB per minute) plugin...' ), 'notification', $data['log'] );
			$mailer->registerPlugin(
				new \Swift_Plugins_ThrottlerPlugin(
					1024 * 1024 * (int)$this->config( 'throttle-traffic' ),
					\Swift_Plugins_ThrottlerPlugin::BYTES_PER_MINUTE
				)
			);
		}

		$this->email_msg( $this->__( 'Sending email...' ), 'notification', $data['log'] );

		try {
			$result = $mailer->send( $message );

		} catch ( \Swift_TransportException $e ) {
			$result = false;

		} catch ( \SwiftException $e ) {
			$result = false;
		}

		if( ! $result ) {
			$status = self::EMAIL_STATUS_FAIL;

			$this->email_msg(
				$this->__(
					'Email was not sent. Log information: %s',
					$logger->dump()
				),
				'error',
				$data['log']
			);

		} else {

			$this->email_msg( $this->__( 'Email has been sent ' ), 'notification', $data['log'] );
			$this->email_msg(
				'Header: ' . PHP_EOL . str_replace( array( '<', '>', ), ' ', $message->getHeaders()->toString() ),
				'notification',
				$data['log']
			);

			$status = self::EMAIL_STATUS_SUCCESS;
		}

		// Add history record
		if ( $data['add_history'] ) {
			$this->email_msg( 'Add to log', 'notification', $data['log'] );

			$this->q( array(
				'table' => $this->history_table,
				'query' => 'insert',
				'values' => array(
					'to'                => $this->print_email_address( $message->getTo() ),
					'from'              => $this->print_email_address( $message->getFrom() ),
					'subject'           => $message->getSubject(),
					'status'            => $status,
					'template'          => $data['template_name'],
					'attachment'        => $data['attachment'],
					'log'               => $data['log'],
					'tracking_id'       => $data['tracking_id'] ?: '',
					'tracking_visit_id' => $data['tracking_visit_id'] ?: '',
					'date_added'        => 'now()',
					'newsletter'        => $data['newsletter'],
				),
			) );
		}

		return $result;
	}

	/**
	 * Puts email into queue
	 * @param object $mailer Mailer instance
	 * @param object $message Message instance
	 * @param array $data Email data
	 * @return boolean Operation status
	 */
	public function put_to_queue( $mailer, $message, $data ) {
		$content = array(
			'mailer'  => serialize( $mailer ),
			'message' => serialize( $message ),
			'data'    => $data,
		);

		$file = $this->tmp_dir . uniqid();
		file_put_contents( $file, json_encode( $content, JSON_HEX_QUOT ) );

		$result = $this->q( array(
			'table' => $this->queue_table,
			'query' => 'insert',
			'values' => array(
				'content'    => $file,
				'date_added' => date( 'c' ),
				'status'     => self::QUEUE_STATUS_ACTIVE,
				'attempt'    => 0,
 			),
		) );

		return $result;
	}

	/**
	 * Returns padding string
	 * @param type|string $iput Input
	 * @param type|int $count Multiplier
	 * @return string
	 */
	public function pad( $input = ' ', $count = 37 ) {
		return str_repeat( $input , $count );
	}

	/**
	 * Sends message pertain to emailing proses
	 * @since 1.1.0
	 * @param string $msg Message contents 
	 * @param string $level Message level: notification, warn, default value - notification
	 * @return void
	 */
	public function email_msg( $msg, $level = 'notification', &$log = null ) {
		if ( is_null( $log ) ) {
			$log =& $this->email_log;
		}

		$msg = date( 'Y-m-d H:i:s' ) . ' ' . '[' . $level . ']' . ' ' . $msg . PHP_EOL;
		$log .= $msg;

		if ( function_exists( 'l' ) ) {
			l( $msg );
		}
	}

	/**
	 * Returns email address from SWift Email specific email format
	 * @since 1.1.0
	 * @param array|string $address 
	 * @return string
	 */
	public function fetch_email_addr( $address ) {
		if( gettype( $address ) === 'string' ) {
			return $address;
		}

		if( gettype( $address ) === 'array' ) {
			$key = key( $address );

			if( is_numeric( $key ) ) {
				return current( $address );
			}

			return $key;
		}

		return '';
	}

	/**
	 * Returns attachments files from Swift email attachments list
	 * @since 1.1.0
	 * @param array $attachments List of attachments 
	 * @return string
	 */
	public function fetch_attachments( $attachments ) {
		$att = array();

		foreach( $attachments as $attachment ) {
			$att[] = $attachment['path'];
		}

		return implode( ', ', $att );
	} 

	/**
	 * Print out email addresses for email messaging purposes 
	 * @since 1.1.0
	 * @param array|string $addresses Email addresses
	 * @return string
	 */
	public function print_email_address( $addresses ) {
		$addr = array();

		foreach( (array)$addresses as $address => $name ) {
			if( ! is_numeric( $address ) ) {
				$addr[] = $name . ' ' .  $address;

			} else {
				$addr[] = $name;
			}
		}

		return implode( ', ', $addr );
	}

	/**
	 * Gets custom mailer and initialize it by OpenCart mailed data
	 * @param object $mail OpenCart mailer 
	 * @return object
	 */
	public function get_mailer_from_mail( $mail ) {
		return $this->get_mailer( array(
			'protocol'    => $mail->protocol,
			'smtp_server' => $mail->smtp_hostname,
			'smtp_user'   => $mail->smtp_username,
			'smtp_pass'   => $mail->smtp_password,
			'smtp_port'   => $mail->smtp_port,
		) );
	}

	/**
	 * Instantiates Swift mailer
	 * @param array $data Mailer data
	 * @return object
	 */
	public function get_mailer( $data ) {
		require_once( $this->swift_loader );
		$transport = null;

		if( 'smtp' === strtolower( $data['protocol'] ) ) {
			$transport = \Swift_SmtpTransport::newInstance( $data['smtp_server'], $data['smtp_port'] ) 
				->setUsername( $data['smtp_user'] )
				->setPassword( $data['smtp_pass'] );

		} else {
			$transport = \Swift_MailTransport::newInstance();
		}

		return \Swift_Mailer::newInstance( $transport );
	}

	/**
	 * If template has HtmL/Text variant  - leaves html ones
	 * @param string $text Template contents
	 * @return void
	 */
	public function fetch_html_variants( $text ) {
		return preg_replace( '%<template_variant.*?<html_variant[^>]*?>(.*?)</html_variant>.*?</template_variant>%s', '$1', $text );
	}

	/**
	 * If template has HtmL/Text variant  - leaves text ones
	 * @param string $text Template contents
	 * @return void
	 */
	public function fetch_text_variants( $text ) {
		return preg_replace( '%<template_variant.*?<text_variant[^>]*?>(.*?)</text_variant>.*?</template_variant>%s', '$1', $text );
	}

	/**
	 * Detaches images from email body
	 * @param string $html Email body
	 * @return string
	 */
	public function detach_img( $html ) {
		foreach( $this->attached_img as $img ) {
			$cid = $img['object']->getId();
			$html = preg_replace( '/cid\:' . preg_quote( $cid ) . '/', $img['url'], $html );
		}

		return $html;
	}

	// Shortcode callbacks

	// /**
	//  * Renders vitrine shortcode
	//  * @return string
	//  */
	// public function shortcode_vitrine() {

	// 	$args = func_get_args();
	// 	$shortcode_id = isset( $args[1] ) ? $args[1] : null;

	// 	if( is_null( $shortcode_id ) ) {
	// 		return '';
	// 	}

	// 	$shortcode = $this->get_shortcode( $shortcode_id );

	// 	if( ! $shortcode ) {
	// 		return '';
	// 	}

	// 	$img_width = isset( $shortcode['data']['img']['width'] ) ?
	// 		$shortcode['data']['img']['width'] : 100;
	// 	$img_header_height = isset( $shortcode['data']['img']['header']['height'] ) ?
	// 		$shortcode['data']['img']['header']['height'] : 0;

	// 	$products = $this->get_vitrine_products( $shortcode );

	// 	$embed = ! empty( $shortcode['data']['img']['embed'] );
	// 	$height = ! empty( $shortcode['data']['element']['height'] ) ?
	// 		$shortcode['data']['element']['height'] : 200;

	// 	$width = ! empty( $shortcode['data']['element']['width'] ) ?
	// 		$shortcode['data']['element']['width'] : 120;

	// 	$width += 2;

	// 	$margins = ( $width - $img_width ) / 2;

	// 	$this->load->model( 'tool/image' );

	// 	$ret = '';

	// 	$ret .=
	// 	'<!--[if lt mso 12]>-->' .
	// 	'<table width="100%" cellpadding="0" cellspacing="0" style="table-layout: fixed" class="vitrine-table">' .
	// 		'<tr>' .
	// 			'<td style="font-size:' . $shortcode['data']['title']['height'] . 'px;color:' . $shortcode['data']['title']['color'] . ';" align="' . $shortcode['data']['title']['align'] . '">' .
	// 				$shortcode['data']['title']['text'] .
	// 			'</td>' .
	// 		'</tr>' .
	// 		'<tr>' .
	// 			'<td>';

	// 	foreach( $products as $product ) {
	// 		$currency_from = $this->config->get( 'config_currency' );
	// 		$currency_to = isset( $this->session->data['currency'] ) ? $this->session->data['currency'] : $currency_from;

	// 		$price = $this->currency->format( $this->currency->convert( $product['price'], $currency_from, $currency_to ), $currency_to );
	// 		$special = null;

	// 		if( $product['special'] ) {
	// 			$special = $this->currency->format( $this->currency->convert( $product['special'], $currency_from, $currency_to ), $currency_to );
	// 		}

	// 		$file_name = $this->model_tool_image->resize( $product['image'], $img_width, $img_width );
	// 		$file_name = str_replace( $this->get_store_url() . 'image/', '' , $file_name );
	// 		$url  = $this->get_img( $file_name, $embed );

	// 		$ret .=
	// 			'<div style="float: left; width: ' . $width . 'px; height: ' . $height . 'px;">' .
	// 				'<table cellpadding="0" cellspacing="0" valign="top" align="center" class="vitrine-element">' .
	// 					'<tr ' .  ( $img_header_height > 0 ? 'style="height: ' . $img_header_height . 'px"' : '' ) . '>' .
	// 						'<td align="center">' .
	// 							$product['name'] .
	// 						'</td>' .
	// 					'</tr>' .
	// 					'<tr>' .
	// 						'<td align="center">' .
	// 							'<a href="' . $this->get_store_url() . 'index.php?route=product/product&product_id=' . $product['product_id'] . '" target="_blank">' .
	// 								'<img src="' . $url . '" width="' . $img_width . '" height="' . $img_width . '" style="max-width=' . $img_width . 'px; width=' . $img_width . 'px;" />' .
	// 							'</a>' .
	// 						'</td>' .
	// 					'</tr>' .
	// 					'<tr>' .
	// 						'<td align="center">' .
	// 							( $special ? $special . '<br>' : '' ) . 
	// 							( $special ? '<strike>' . $price . '</strike>' : $price ) .
	// 						'</td>' .
	// 					'</tr>' .
	// 				'</table>' .
	// 			'</div>';
	// 	}

	// 	$ret .=
	// 			'</td>' .
	// 		'</tr>' .
	// 	'</table>' .
	// 	'<!--<![endif]-->';

	// 	return $ret;
	// }

	// /**
	//  * Renders contents of social shortcode tab
	//  * @return sting
	//  */
	// public function shortcode_social() {
	// 	$args = func_get_args();
	// 	$shortcode_id = isset( $args[1] ) ? $args[1] : null;

	// 	if( is_null( $shortcode_id ) ) {
	// 		return '';
	// 	}

	// 	$shortcode = $this->get_shortcode( $shortcode_id );

	// 	if( ! $shortcode ) {
	// 		return '';
	// 	}

	// 	$margin = isset( $shortcode['data']['icon']['margin'] ) ? $shortcode['data']['icon']['margin'] : 0;

	// 	$this->load->model( 'tool/image' );

	// 	$ret = '';

	// 	$ret .=
	// 	'<table width="100%" cellpadding="0" cellspacing="0">' .
	// 		'<tr>' .
	// 			'<td style="font-size:' . $shortcode['data']['title']['height'] . 'px;color:' . $shortcode['data']['title']['color'] . ';" align="' . $shortcode['data']['title']['align'] . '">' .
	// 				$shortcode['data']['title']['text'] .
	// 			'<td>' .
	// 		'</tr>' .
	// 		'<tr>' .
	// 			'<td align="' . $shortcode['data']['title']['align'] . '"><div>';

	// 	$items = isset( $shortcode['data']['item'] ) ? $shortcode['data']['item'] : array();
	// 	$appearance = isset( $shortcode['data']['appearance'] ) ? $shortcode['data']['appearance' ] : '';
	// 	$size = isset( $shortcode['data']['icon']['height'] ) ? $shortcode['data']['icon']['height'] : 40;

	// 	foreach( $items as $name => $item ) {
	// 		if( empty( $item['status'] ) ) {
	// 			continue;
	// 		}

	// 		$this->model_tool_image->resize( 'social/' . $appearance . '/' . $name . '.png' , $size, $size );
	// 		$src = $this->get_img( 'social/' . $appearance . '/' . $name . '.png', ! empty( $shortcode['data']['icon']['embed'] ) );
	// 		$href = isset( $item['url'] ) ? $item['url'] : '#';

	// 		$ret .=
	// 				'<a href="' . $href . '" target="_blank" style="margin-right: ' . $margin . 'px;">' .
	// 					'<img src="' . $src . '" width="' . $size . '" height="' . $size . '"/>' .
	// 				'</a>';
	// 	}

	// 	$ret .=
	// 			'</div></td>' .
	// 		'</tr>' .
	// 	'</table>';

	// 	return $ret;
	// }

	// /**
	//  * Renders call to action shortcode
	//  * @return string
	//  */
	// public function shortcode_button() {

	// 	$args = func_get_args();
	// 	$shortcode_id = isset( $args[1] ) ? $args[1] : null;

	// 	if( is_null( $shortcode_id ) ) {
	// 		return '';
	// 	}

	// 	$shortcode = $this->get_shortcode( $shortcode_id );

	// 	if( ! $shortcode ) {
	// 		return '';
	// 	}

	// 	$href = ! empty( $shortcode['data']['url'] ) ? $shortcode['data']['url'] : '';
	// 	$height = isset( $shortcode['data']['height'] ) ? $shortcode['data']['height'] : '40';
	// 	$padding = isset( $shortcode['data']['padding'] ) ? $shortcode['data']['padding'] : '5';
	// 	$height_px = $height . 'px';
	// 	$width = isset( $shortcode['data']['width'] ) ? $shortcode['data']['width'] : '140';
	// 	$width_px = $width . 'px';
	// 	$bg_color =  isset( $shortcode['data']['color'] ) ? $shortcode['data']['color'] : '#0000ff';
	// 	$border_color =  isset( $shortcode['data']['border']['color'] ) ? $shortcode['data']['border']['color'] : '#0000ff';
	// 	$caption_color = isset( $shortcode['data']['caption']['color'] ) ? $shortcode['data']['caption']['color'] : '#000000';
	// 	$caption_height = ( isset( $shortcode['data']['caption']['height'] ) ? $shortcode['data']['caption']['height'] : '16' ) . 'px';
	// 	$caption = isset( $shortcode['data']['caption']['text'] ) ? $shortcode['data']['caption']['text'] : ' ';
	// 	$border_width = ( isset( $shortcode['data']['border']['width'] ) ? $shortcode['data']['border']['width'] : '1' ) . 'px';
	// 	$border_radius = ( isset( $shortcode['data']['border']['radius'] ) ? $shortcode['data']['border']['radius'] : '3' ) . 'px';
	// 	$align = isset( $shortcode['data']['align'] ) ? $shortcode['data']['align'] : 'center';
	// 	if( ! empty( $shortcode['data']['fullwidth'] ) ) {
	// 		$width = "100%";
	// 		$width_px = "100%";
	// 	}

	// 	// Call to action URL supports shortcodes
	// 	if( $href ) {
	// 		$href = $this->do_shortcode( $href );
	// 	}
		
	// 	// Get href attributr from the anchor
	// 	if( preg_match( '/<a.+href=("|\')(.+?)\1/', $href, $m ) ) {
	// 		$href = $m[2];
	// 	}

	// 	$ret =
	// 	'<center><div>' .
	// 		'<!--[if mso]>' .
	// 		'<v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="' . $href . '" style="height:' . $height_px . ';v-text-anchor:middle;width:' . $width_px . ';" arcsize="10%" stroke="f" fillcolor="' . $bg_color . '">' .
	// 		'<w:anchorlock/>' .
	// 		'<center style="color:' . $caption_color . ';font-family:sans-serif;font-size:' . $caption_height . ';font-weight:bold;">' .
	// 			$caption .
	// 		'</center>' .
	// 		'</v:roundrect>' .
	// 		'<![endif]-->' .

	// 		'<!--[if !mso]>-->' .
	// 		'<table cellspacing="0" cellpadding="' . $padding .'px" width="100%">' .
	// 			'<tr align="' . $align . '">' . 
	// 				'<td align="center" width="' . $width .'" height="' . $height .'" bgcolor="' . $bg_color . '" style="-webkit-border-radius: ' . $border_radius . '; -moz-border-radius: ' . $border_radius . '; border-radius: ' . $border_radius . '; color: ' . $caption_color . '; display: block; border: solid ' . $border_width . ' ' . $border_color . '; height: ' . $height . 'px; ">' .
	// 					'<a href="' . $href . '" style="font-size: ' . $caption_height . '; font-family: sans-serif; line-height: ' . $height_px . '; width: 100%; display: inline-block; text-decoration: none; font-weight: bold; color: ' . $caption_color . '; " target="_blank">' .
	// 						'<span style="color: ' . $caption_color . '; line-height: ' . $height . 'px; height: ' . $height . 'px; ">' .
	// 							$caption .
	// 						'</span>' .
	// 					'</a>' .
	// 				'</td>' . 
	// 			'</tr>' .
	// 		'</table>' . 
	// 		'<!--<![endif]-->' .
	// 	'</div></center>';

	// 	return $ret;
	// }

	// /**
	//  * Renders QR Code. Supports recursion
	//  * @return string
	//  */
	// public function shortcode_qrcode() {
	// 	$args = func_get_args();
	// 	$shortcode_id = isset( $args[1] ) ? $args[1] : null;

	// 	if( is_null( $shortcode_id ) ) {
	// 		return '';
	// 	}

	// 	$shortcode = $this->get_shortcode( $shortcode_id );

	// 	if( ! $shortcode ) {
	// 		return '';
	// 	}

	// 	require_once( dirname(  __FILE__ ) . '/phpqrcode/qrlib.php' );

	// 	$this->mkdir( $this->tmp_dir );
	// 	$tmp_file = $this->tmp_dir . '/shortcode' . uniqid();
	// 	file_put_contents( $tmp_file, '' );

	// 	$text = htmlspecialchars_decode( $shortcode['data']['content'] );
	// 	$text = str_replace(array( '&nbsp;', '<p>', '<br>' ), ' ', $text );
	// 	$text = str_replace( array( '</p>' ), '', $text );

	// 	$text = $this->do_shortcode( $text );

	// 	// Wrap off links, if any
	// 	$text = preg_replace( '/<a[^>]+?href=(\'|")(.*?)\1[^>]+>(.*?)<\/a>/', '$2 $3', $text );

	// 	QRcode::png(

	// 		// QR Code contents
	// 		$text,

	// 		// Target file
	// 		$tmp_file,

	// 		// Error correction level
	// 		$shortcode['data']['level'],

	// 		// Code square size
	// 		$shortcode['data']['square'],

	// 		// White border width
	// 		$shortcode['data']['border']
	// 	);

	// 	$ret = '<img src="data:image/png;base64,' . base64_encode( file_get_contents( $tmp_file ) ) . '" />';

	// 	unlink( $tmp_file );

	// 	return $ret;
	// }

	// /**
	//  * Returns URL to restore admin password
	//  * @return string
	//  */
	// public function shortcode_restore_password_url() {
	// 	if( empty( $this->request->post['email'] ) ) {
	// 		return '';
	// 	}

	// 	$email = $this->request->post['email'];
	// 	$code = $this->get_admin_restore_password_code( $email );

	// 	if( ! $code ) {
	// 		return '';
	// 	}

	// 	return $this->url->link( 'common/reset', 'code=' . $code, 'SSL' );
	// }

	// /**
	//  * Returns current store name
	//  * @return string
	//  */
	// public function shortcode_store_name() {
	// 	return $this->config->get( 'config_name' );
	// }

	// /**
	//  * Returns client IP address
	//  * @return string
	//  */
	// public function shortcode_ip() {
	// 	$ip = '';

	// 	if ( isset( $_SERVER ) ) {

	// 		if( isset( $_SERVER["HTTP_X_FORWARDED_FOR"] ) ) {
	// 			$ip = $_SERVER["HTTP_X_FORWARDED_FOR"];

	// 			if( strpos( $ip, "," ) ){
	// 				$exp_ip = explode( ",", $ip );
	// 				$ip = $exp_ip[0];
	// 			}

	// 		} else if( isset( $_SERVER["HTTP_CLIENT_IP"] ) ) {
	// 			$ip = $_SERVER["HTTP_CLIENT_IP"];
	// 		} else{
	// 			$ip = $_SERVER["REMOTE_ADDR"];
	// 		}

	// 	} else {
	// 		if( getenv( 'HTTP_X_FORWARDED_FOR' ) ) {
	// 			$ip = getenv( 'HTTP_X_FORWARDED_FOR' );

	// 			if( strpos( $ip, "," ) ) {
	// 				$exp_ip = explode( ",", $ip );
	// 				$ip = $exp_ip[0];
	// 			}

	// 		} else if( getenv( 'HTTP_CLIENT_IP' ) ) {
	// 			$ip = getenv( 'HTTP_CLIENT_IP' );
	// 		} else {
	// 			$ip = getenv( 'REMOTE_ADDR' );
	// 		}
	// 	}

	// 	return $ip;
	// }

	// /**
	//  * Returns customer's full name
	//  * @return string
	//  */
	// public function shortcode_customer_full_name() {
	// 	$customer = $this->get_mail_customer();
	// 	$ret = '';
		
	// 	if( isset( $customer['firstname'] ) && isset( $customer['lastname'] ) ) {
	// 		$ret = $customer['firstname'] . ' ' . $customer['lastname'];
	// 	}

	// 	if ( ! $ret && defined( 'PREVIEW' ) ) {
	// 		$ret = 'John Smith';
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Returns customer's first name
	//  * @return string
	//  */
	// public function shortcode_customer_first_name() {
	// 	$customer = $this->get_mail_customer();
	// 	$ret = '';
		
	// 	if( isset( $customer['firstname'] ) ) {
	// 		$ret = $customer['firstname'];
	// 	}

	// 	if ( ! $ret && defined( 'PREVIEW' ) ) {
	// 		$ret = 'John';
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Returns customers last name
	//  * @return string
	//  */
	// public function shortcode_customer_last_name() {
	// 	$customer = $this->get_mail_customer();
	// 	$ret = '';
		
	// 	if( isset( $customer['lastname'] ) ) {
	// 		$ret = $customer['lastname'];
	// 	}

	// 	if ( ! $ret && defined( 'PREVIEW' ) ) {
	// 		$ret = 'Smith';
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Returns affiliate full name
	//  * @return string
	//  */
	// public function shortcode_affiliate_full_name() {
	// 	$affiliate = $this->get_mail_affiliate();
	// 	$ret = '';
		
	// 	if( isset( $affiliate['firstname'] ) && isset( $affiliate['lastname'] ) ) {
	// 		$ret = $affiliate['firstname'] . ' ' . $affiliate['lastname'];
	// 	}

	// 	if ( ! $ret && defined( 'PREVIEW' ) ) {
	// 		$ret = 'John Smith';
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Returns affiliate first name
	//  * @return string
	//  */
	// public function shortcode_affiliate_first_name() {
	// 	$affiliate = $this->get_mail_affiliate();
	// 	$ret = '';
		
	// 	if( isset( $affiliate['firstname'] ) ) {
	// 		$ret = $affiliate['firstname'];
	// 	}

	// 	if ( ! $ret && defined( 'PREVIEW' ) ) {
	// 		$ret = 'John';
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Returns affiliate last name
	//  * @return string
	//  */
	// public function shortcode_affiliate_last_name() {
	// 	$affiliate = $this->get_mail_affiliate();
	// 	$ret = '';
		
	// 	if( isset( $affiliate['lastname'] ) ) {
	// 		$ret = $affiliate['lastname'];
	// 	}

	// 	if ( ! $ret && defined( 'PREVIEW' ) ) {
	// 		$ret = 'Smith';
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Returns contents of original email letter
	//  * @return string
	//  */
	// public function shortcode_initial_contents() {
	// 	$ret = '';

	// 	if( ! is_null( $this->modified_mail ) ) {

	// 		if( $this->modified_mail->html ) {
	// 			$ret = $this->modified_mail->html;
	// 		} elseif ( $this->modified_mail->text ) {
	// 			$ret = $this->modified_mail->text;
	// 		}
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Returns link to page where customer can unsubscribe from newsletter
	//  * @return string
	//  */
	// public function shortcode_unsubscribe() {
	// 	$ret = '';
	// 	$args = func_get_args();
	// 	$text = empty( $args[1] ) ? $this->__( 'Cancel subscription' ) : $args[1];
	// 	$email = null;

	// 	if ( $this->adk_newsletter_id && $this->adk_subscriber_email ) {
	// 		$n = $this->run_query( array(
	// 			'table' => $this->newsletter_subscribers_table,
	// 			'query' => 'select',
	// 			'where' => array(
	// 				array(
	// 					'field'     => 'email',
	// 					'operation' => '=',
	// 					'value'     => $this->adk_subscriber_email,
	// 				),
	// 				array(
	// 					'field'     => 'newsletter',
	// 					'operation' => '=',
	// 					'value'     => $this->adk_newsletter_id,
	// 				),
	// 				array(
	// 					'field'     => 'status',
	// 					'operation' => '=',
	// 					'value'     => self::SUBSCRIBER_STATUS_ACTIVE,
	// 				),
	// 			),
	// 		) );

	// 		if ( count( $n ) ) {
	// 			$newsletter = $this->adk_newsletter_id;
	// 			$email = $this->adk_subscriber_email;
	// 		}

	// 	// Presume OpenCart newsletter
	// 	} else {
	// 		$customer = $this->get_mail_customer();

	// 		if ( $customer && ! empty( $customer['newsletter'] ) && ! empty( $customer['email'] ) ) {

	// 			// 0 -is OpenCart newsletter's code
	// 			$newsletter = 0;
	// 			$email = $customer['email'];
	// 		}

	// 	}

	// 	// Do not show cancellation link in admin email
	// 	if ( $this->modified_mail && $this->modified_mail->to != $email ) {
	// 		$newsletter = null;
	// 	}

	// 	if( isset( $newsletter ) ) {
	// 		$code = uniqid();

	// 		$values = array(
	// 			'code'        => $code,
	// 			'expiration'  => $this->get_sql_expiration_date( 'unsubscribe' ),
	// 			'email'       => $email,
	// 			'newsletter'  => $newsletter,
	// 			'operation'   => self::NEWSLETTER_CODE_CANCEL,
	// 		);

	// 		if ( 0 === $newsletter ) {
	// 			$values['customer_id'] = $customer['customer_id'];
	// 		}

	// 		$result = $this->run_query( array(
	// 			'table' => $this->newsletter_code_table,
	// 			'query' => 'insert',
	// 			'values' => $values,
	// 		) );

	// 		if( $result ) {
	// 			$ret = $this->get_store_url() .
	// 			'index.php?route=' . $this->type . '/' . $this->code . '/unsubscribe&code=' . $code;

	// 		} else {
	// 			trigger_error( 'Failed to render unsubscrine shortcode\'s content due to DB error' );
	// 		}

	// 	} elseif ( defined( 'PREVIEW' ) ) {
	// 		$ret = $this->get_store_url() .
	// 			'index.php?route=' . $this->type . '/' . $this->code . '/unsubscribe&code=test';
	// 	}

	// 	if( $ret ) {
	// 		$ret = sprintf( '<a href="%s" target="_blank">%s</a>', $ret, $text );
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Returns link to the log-in to customer account page 
	//  * @return string
	//  */
	// public function shortcode_account_login_url() {
	// 	$args = func_get_args();
	// 	$text = empty( $args[1] ) ? $this->__( 'Sign in' ) : $args[1];

	// 	$ret =  ( defined( 'HTTPS_CATALOG' ) ? HTTPS_CATALOG : HTTPS_SERVER ) .
	// 		'index.php?route=account/login';

	// 	return sprintf( '<a href="%s" target="_blank">%s</a>', $ret, $text );
	// }

	// /**
	//  * Returns link to open email in browser
	//  * @return string
	//  */
	// public function shortcode_open_in_browser() {

	// 	// Do not save archive copy for email containing sensitive data
	// 	if ( $this->private_template ) {
	// 		return '';
	// 	}

	// 	$args = func_get_args();
	// 	$text = empty( $args[1] ) ? $this->__( 'Open in browser' ) : $args[1];

	// 	if ( $this->archive_file ) {
	// 		$file_name = substr( $this->archive_file, strlen( $this->archive_dir ) );

	// 	} else {
	// 		$date = new DateTime();
	// 		$file_name = $date->format( 'Y' ) .'/' . $date->format( 'm' ) . '/' .
	// 			$date->format( 'd' ) . '/' . uniqid();
	// 		$this->archive_file = $this->archive_dir . $file_name;
	// 	}

	// 	$ret =  $this->get_store_url() . '/' .
	// 		'index.php?route=' . $this->type . '/' . $this->code . '/archive&email=' . $file_name;

	// 	return sprintf( '<a href="%s" target="_blank">%s</a>', $ret, $text );
	// }

	// /**
	//  * Returns link to the log-in to affiliate account page
	//  * @return type
	//  */
	// public function shortcode_affiliate_login_url() {
	// 	$args = func_get_args();
	// 	$text = empty( $args[1] ) ? $this->__( 'Sign in' ) : $args[1];

	// 	$ret = ( defined( 'HTTPS_CATALOG' ) ? HTTPS_CATALOG : HTTPS_SERVER ) .
	// 		'index.php?route=affiliate/login';

	// 	return sprintf( '<a href="%s" target="_blank">%s</a>', $ret, $text );
	// }

	// /**
	//  * Returns amount of add credit transaction
	//  * @return string
	//  */
	// public function shortcode_transaction_amount() {
	// 	$ret = '';

	// 	if( isset( $this->request->get['route'] )
	// 			&& 'customer/customer/addtransaction' === strtolower( $this->request->get['route'] ) ) {

	// 		if( isset( $this->request->post['amount'] ) ) {
	// 			$ret = $this->currency->format( $this->request->post['amount'], $this->config->get( 'config_currency' ) );
	// 		}

	// 	} elseif ( defined( 'PREVIEW' ) ) {
	// 		$ret = $this->currency->format( 100, $this->config->get( 'config_currency' ) );
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Returns current credit balance for customer account 
	//  * @return string
	//  */
	// public function shortcode_transaction_total() {
	// 	$ret = '';
	// 	$customer = $this->get_mail_customer();

	// 	if( $customer && isset( $customer['customer_id'] ) ) {
	// 		$query = $this->db->query( "SELECT SUM(`amount`) AS total FROM `" . DB_PREFIX . "customer_transaction` WHERE `customer_id` = '" . (int)$customer['customer_id'] . "'" );

	// 		$ret = $this->currency->format( $query->row['total'], $this->config->get( 'config_currency' ) );
	// 	} elseif ( defined( 'PREVIEW' ) ) {
	// 		$ret = $this->currency->format( 1000, $this->config->get( 'config_currency' ) );
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Returns description for credit balance transaction
	//  * @return string
	//  */
	// public function shortcode_transaction_description() {
	// 	$ret = '';

	// 	if( isset( $this->request->get['route'] ) &&
	// 			'customer/customer/addtransaction' === strtolower( $this->request->get['route'] ) ) {
	// 		if( isset( $this->request->post['description'] ) ) {

	// 			$ret = $this->request->post['description'];
	// 		}

	// 	} elseif ( defined( 'PREVIEW' ) ) {
	// 		$ret = $this->__( 'Order #123 partial refund' );
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Conditional shortcode, shows contents if there is transaction description
	//  * @return boolean
	//  */
	// public function shortcode_if_transaction_description() {
	// 	return (boolean)$this->shortcode_transaction_description();
	// }

	// /**
	//  * Returns the add reward points transaction amount
	//  * @return string
	//  */
	// public function shortcode_reward_points() {
	// 	$ret = '';

	// 	if( isset( $this->request->get['route'] )
	// 			&& 'customer/customer/addreward' === strtolower( $this->request->get['route'] ) ) {

	// 		if( isset( $this->request->post['points'] ) ) {
	// 			$ret = $this->request->post['points'];
	// 		}

	// 	} elseif ( defined( 'PREVIEW' ) ) {
	// 		$ret = 100;
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Returns customer account's total reward points amount
	//  * @return string
	//  */
	// public function shortcode_reward_total() {
	// 	$ret = '';

	// 	$customer = $this->get_mail_customer();

	// 	if( $customer && isset( $customer['customer_id'] ) ) {
	// 		$query = $this->db->query( "SELECT SUM(`points`) AS total FROM `" . DB_PREFIX . "customer_reward` WHERE `customer_id` = '" . (int)$customer['customer_id'] . "'" );

	// 		$ret = $query->row['total'];

	// 	} elseif ( defined( 'PREVIEW' ) ) {
	// 		$ret = 1200;
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Returns description to add reward points transaction
	//  * @return string
	//  */
	// public function shortcode_reward_description() {
	// 	$ret = '';
	// 	if( isset( $this->request->get['route'] ) &&
	// 			'customer/customer/addreward' === strtolower( $this->request->get['route'] ) ) {

	// 		if( isset( $this->request->post['description'] ) ) {
	// 			$ret = $this->request->post['description'];
	// 		}

	// 	} elseif ( defined( 'PREVIEW' ) ) {
	// 		$ret = 'Reward points to order #243';
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Conditional tag. Shows contents if an add reward points transaction description is present
	//  * @return boolean
	//  */
	// public function shortcode_if_reward_description() {
	// 	return (boolean)$this->shortcode_reward_description();
	// }

	// /**
	//  * Shows an add affiliate commissions balance transaction
	//  * @return string
	//  */
	// public function shortcode_affiliate_commission() {
	// 	$ret = '';

	// 	if( isset( $this->request->get['route'] ) ) {

	// 		if( 'marketing/affiliate/addtransaction' === strtolower( $this->request->get['route'] ) ) {
	// 			if( isset( $this->request->post['amount'] ) ) {

	// 				$ret = $this->currency->format(
	// 					$this->request->post['amount'],
	// 					$this->config->get( 'config_currency' )
	// 				);
	// 			}

	// 		// One of the payment methods
	// 		} elseif ( strpos( strtolower( $this->request->get['route'] ), 'payment' ) !== false  ) {
	// 			if( $this->caller_args && isset( $this->caller_args[1] ) ) {

	// 				$ret = $this->currency->format(
	// 					$ret = $this->caller_args[1],
	// 					$this->session->data['currency']
	// 				);
	// 			}
	// 		}
	// 	}

	// 	if( ! $ret && defined( 'PREVIEW' ) ) {
	// 		$ret = $this->currency->format( 102.44, $this->config->get( 'config_currency' ) );
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Returns description to an add affiliate commission balance transaction
	//  * @return string
	//  */
	// public function shortcode_affiliate_commission_description() {
	// 	$ret = '';

	// 	if( isset( $this->request->get['route'] )
	// 			&& 'marketing/affiliate/addtransaction' === strtolower( $this->request->get['route'] ) ) {

	// 		if( isset( $this->request->post['description'] ) ) {

	// 			$ret = $this->request->post['description'];
	// 		}

	// 	} elseif ( defined( 'PREVIEW' ) ) {
	// 		$ret = $this->__( 'Commission for order #324' );
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Conditional tag. Shows contents if there is description to an add affiliate commission transaction
	//  * @return string
	//  */
	// public function shortcode_if_affiliate_commission_description() {
	// 	return (boolean)$this->shortcode_affiliate_commission_description();
	// }

	// /**
	//  * Shows total amount of commissions for affiliate account
	//  * @return string
	//  */
	// public function shortcode_affiliate_commission_total() {
	// 	$ret = '';
	// 	$affiliate_id = null;

	// 	if( isset( $this->request->get['affiliate_id' ] ) ) {
	// 		$affiliate_id = $this->request->get['affiliate_id'];

	// 	// One of the payment methods
	// 	} elseif ( isset( $this->request->get['route'] )
	// 		&& strpos( strtolower( $this->request->get['route'] ), 'payment' ) !== false ) {

	// 		if( $this->caller_args && isset( $this->caller_args[0] ) ) {
	// 			$affiliate_id = $this->caller_args[0];
	// 		}
	// 	}

	// 	if( $affiliate_id ) {
	// 		$query = $this->db->query( "SELECT SUM(`amount`) AS total FROM `" . DB_PREFIX . "affiliate_transaction` WHERE `affiliate_id` = '" . (int)$affiliate_id . "'" );

	// 		$ret = $this->currency->format(
	// 			$query->row['total'],
	// 			$this->config->get( 'config_currency' )
	// 		);
	// 	}

	// 	if ( ! $ret && defined( 'PREVIEW' ) ) {
	// 		$ret = $this->currency->format(
	// 			1023.21,
	// 			$this->config->get( 'config_currency' )
	// 		);
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Returns return ID
	//  * @return string
	//  */
	// public function shortcode_return_id() {
	// 	$ret = '';

	// 	if( isset( $this->request->request['return_id'] ) ) {
	// 			$ret = $this->request->request['return_id'];

	// 	} elseif ( defined( 'PREVIEW' ) ) {
	// 		$ret = '23';
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Returns return creation date
	//  * @return sting
	//  */
	// public function shortcode_return_date() {
	// 	$ret = '';

	// 	if( isset( $this->request->request['return_id'] ) ) {
	// 		$data = $this->get_return_data( $this->request->request['return_id'] );

	// 		if( $data && isset( $data['date_added'] ) ) {
	// 			$d = new DateTime( $data['date_added'] );
	// 			$ret = $d->format( 'd/m/Y' );
	// 		}

	// 	} elseif ( defined( 'PREVIEW' ) ) {
	// 		$d = new DateTime( 'today' );
	// 		$ret = $d->format( 'd/m/Y' );
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Returns current a Return status
	//  * @return sting
	//  */
	// public function shortcode_return_status() {
	// 	$ret = '';

	// 	if( isset( $this->request->request['return_id'] ) ) {
	// 		$data = $this->get_return_data( $this->request->request['return_id'] );

	// 		if( $data && isset( $data['status'] ) ) {
	// 			$ret = $data['status'];
	// 		}

	// 	} elseif ( defined( 'PREVIEW' ) ) {
	// 		$ret = $this->__( 'Awaiting Products' );
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Returns comment to a return transaction
	//  * @return string
	//  */
	// public function shortcode_return_comment() {
	// 	$ret = '';

	// 	if( isset( $this->request->get['route'] ) &&
	// 		'sale/return/history' === strtolower( $this->request->get['route'] ) &&
	// 		isset( $this->request->post['comment'] ) ) {

	// 		$ret = $this->request->post['comment'];

	// 	} elseif ( defined( 'PREVIEW' ) ) {
	// 		$ret = $this->__( 'Wrong product' );
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Conditional tag. Shows contents if a return comment is present
	//  * @return string
	//  */
	// public function shortcode_if_return_comment() {
	// 	return (boolean)$this->shortcode_return_comment();
	// }

	// /**
	//  * Returns link to the store
	//  * @return string
	//  */
	// public function shortcode_store_url() {
	// 	$ret = '';

	// 	$args = func_get_args();
	// 	$text = empty( $args[1] ) ? $this->__( 'Store' ) : $args[1]; 
	// 	$ret = $this->get_store_href();

	// 	return sprintf( '<a href="%s" target="_blank">%s</a>', $ret, $text );
	// }

	// /**
	//  * Returns voucher render name
	//  * @return string
	//  */
	// public function shortcode_voucher_from() {
	// 	$ret = '';
	// 	$voucher_id = null;

	// 	if( isset( $this->request->get['route'] ) && 'sale/voucher/send' === strtolower( $this->request->get['route'] ) ) {
	// 		if( isset( $this->request->post['voucher_id'] ) ) {

	// 			$voucher_id = $this->request->post['voucher_id'];
	// 		} elseif ( isset( $this->request->post['selected'] ) ) {
	// 			if( $this->caller_args ) {

	// 				// Voucher ID passed to model's sendVoucher method
	// 				$voucher_id = $this->caller_args[0];
	// 			}
	// 		}
	// 	}

	// 	if( ! is_null( $voucher_id ) ) {
	// 		$voucher = $this->get_voucher( $voucher_id );

	// 		$ret = isset( $voucher['from_name'] ) ? $voucher['from_name'] : '';
	// 	}

	// 	if ( ! $ret && defined( 'PREVIEW' ) ) {
	// 		$ret = 'Jane Smith';
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Returns voucher amount
	//  * @return string
	//  */
	// public function shortcode_voucher_amount() {
	// 	$ret = '';
	// 	$voucher_id = null;

	// 	if( isset( $this->request->get['route'] ) && 'sale/voucher/send' === strtolower( $this->request->get['route'] ) ) {
	// 		if( isset( $this->request->post['voucher_id'] ) ) {

	// 			$voucher_id = $this->request->post['voucher_id'];
	// 		} elseif ( isset( $this->request->post['selected'] ) ) {
	// 			if( $this->caller_args ) {

	// 				// Voucher ID passed to model's sendVoucher method
	// 				$voucher_id = $this->caller_args[0];
	// 			}
	// 		}
	// 	}

	// 	if( ! is_null( $voucher_id ) ) {
	// 		$voucher = $this->get_voucher( $voucher_id );

	// 		$ret = isset( $voucher['amount'] ) ?
	// 			$this->currency->format( $voucher['amount'], $this->config->get( 'config_currency' ) ) : '';
	// 	}

	// 	if ( ! $ret && defined( 'PREVIEW' ) ) {
	// 		$ret = $this->currency->format( 200, $this->config->get( 'config_currency' ) );
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Returns gift voucher message
	//  * @return string
	//  */
	// public function shortcode_voucher_message() {
	// 	$ret = '';
	// 	$voucher_id = null;

	// 	if( isset( $this->request->get['route'] ) && 'sale/voucher/send' === strtolower( $this->request->get['route'] ) ) {
	// 		if( isset( $this->request->post['voucher_id'] ) ) {

	// 			$voucher_id = $this->request->post['voucher_id'];
	// 		} elseif ( isset( $this->request->post['selected'] ) ) {
	// 			if( $this->caller_args ) {

	// 				// Voucher ID passed to model's sendVoucher method
	// 				$voucher_id = $this->caller_args[0];
	// 			}
	// 		}
	// 	}

	// 	if( ! is_null( $voucher_id ) ) {
	// 		$voucher = $this->get_voucher( $voucher_id );

	// 		$ret = isset( $voucher['message'] ) ? $voucher['message'] : '';
	// 	}

	// 	if ( ! $ret & defined( 'PREVIEW' ) ) {
	// 		$ret = $this->__( 'With best regards from Jane Smith' );
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Returns gift voucher recipient
	//  * @return string
	//  */
	// public function shortcode_voucher_to() {
	// 	$ret = '';
	// 	$voucher_id = null;

	// 	if( isset( $this->request->get['route'] ) && 'sale/voucher/send' === strtolower( $this->request->get['route'] ) ) {
	// 		if( isset( $this->request->post['voucher_id'] ) ) {

	// 			$voucher_id = $this->request->post['voucher_id'];
	// 		} elseif ( isset( $this->request->post['selected'] ) ) {
	// 			if( $this->caller_args ) {

	// 				// Voucher ID passed to model's sendVoucher method
	// 				$voucher_id = $this->caller_args[0];
	// 			}
	// 		}
	// 	}

	// 	if( ! is_null( $voucher_id ) ) {
	// 		$voucher = $this->get_voucher( $voucher_id );

	// 		$ret = isset( $voucher['to_name'] ) ? $voucher['to_name'] : '';
	// 	}

	// 	if ( ! $ret && defined( 'PREVIEW' ) ) {
	// 		$ret = 'John Smith';
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Returns an email address of voucher sender
	//  * @return string
	//  */
	// public function shortcode_voucher_from_email() {
	// 	$ret = '';
	// 	$voucher_id = null;

	// 	if( isset( $this->request->get['route'] ) && 'sale/voucher/send' === strtolower( $this->request->get['route'] ) ) {
	// 		if( isset( $this->request->post['voucher_id'] ) ) {

	// 			$voucher_id = $this->request->post['voucher_id'];
	// 		} elseif ( isset( $this->request->post['selected'] ) ) {
	// 			if( $this->caller_args ) {

	// 				// Voucher ID passed to model's sendVoucher method
	// 				$voucher_id = $this->caller_args[0];
	// 			}
	// 		}
	// 	}

	// 	if( ! is_null( $voucher_id ) ) {
	// 		$voucher = $this->get_voucher( $voucher_id );

	// 		$ret = isset( $voucher['from_email'] ) ? $voucher['from_email'] : '';
	// 	}

	// 	if ( ! $ret && defined( 'PREVIEW' ) ) {
	// 		$ret = 'jane_smith@google.com';
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Returns gift voucher code
	//  * @return string
	//  */
	// public function shortcode_voucher_code() {
	// 	$ret = '';
	// 	$voucher_id = null;

	// 	if( isset( $this->request->get['route'] ) && 'sale/voucher/send' === strtolower( $this->request->get['route'] ) ) {
	// 		if( isset( $this->request->post['voucher_id'] ) ) {

	// 			$voucher_id = $this->request->post['voucher_id'];
	// 		} elseif ( isset( $this->request->post['selected'] ) ) {
	// 			if( $this->caller_args ) {

	// 				// Voucher ID passed to model's sendVoucher method
	// 				$voucher_id = $this->caller_args[0];
	// 			}
	// 		}
	// 	}

	// 	if( ! is_null( $voucher_id ) ) {
	// 		$voucher = $this->get_voucher( $voucher_id );

	// 		$ret = isset( $voucher['code'] ) ? $voucher['code'] : '';
	// 	}

	// 	if ( ! $ret && defined( 'PREVIEW' ) ) {
	// 		$ret = '2345332';
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Returns a gift voucher theme image
	//  * @return string
	//  */
	// public function shortcode_voucher_theme_image() {
	// 	$ret = '';
	// 	$voucher_id = null;
	// 	$args = func_get_args();

	// 	$width = empty( $args[1] ) ? 0 : (int)$args[1];
	// 	$height = empty( $args[2] ) ? 0 : (int)$args[2];

	// 	if ( $width <= 0 && $height <= 0 ) {
	// 		$width = $height = 200;
	// 	} else {
	// 		if( $width <= 0 ) {
	// 			$width = $height;
	// 		} elseif ( $height <= 0 ) {
	// 			$height = $width;
	// 		}
	// 	}

	// 	if( isset( $this->request->get['route'] ) && 'sale/voucher/send' === strtolower( $this->request->get['route'] ) ) {
	// 		if( isset( $this->request->post['voucher_id'] ) ) {

	// 			$voucher_id = $this->request->post['voucher_id'];
	// 		} elseif ( isset( $this->request->post['selected'] ) ) {
	// 			if( $this->caller_args ) {

	// 				// Voucher ID passed to model's sendVoucher method
	// 				$voucher_id = $this->caller_args[0];
	// 			}
	// 		}
	// 	}

	// 	if( ! is_null( $voucher_id ) ) {
	// 		$voucher = $this->get_voucher( $voucher_id );

	// 		if( isset( $voucher['image'] ) && is_file( DIR_IMAGE . $voucher['image'] ) ) {
	// 			$ret = $voucher['image'];
	// 		}
	// 	}

	// 	if ( ! $ret && defined( 'PREVIEW' ) && is_file( DIR_IMAGE . 'no_image.png') ) {
	// 		$ret = 'no_image.png';
	// 	}

	// 	 if ( $ret ) {
	// 	 	$this->load->model( 'tool/image' );

	// 	 	$ret = sprintf(
	//  			'<div style="float: right; margin-left: 20px;">' .
	// 				'<a href="%1$s" title="%2$s">' .
	// 					'<img src="%3$s" alt="%2$s" />' .
	// 				'</a>' .
	// 			'</div>',
	// 			$this->get_store_href(),
	// 			$this->shortcode_store_name(),
	// 			$this->model_tool_image->resize( $ret, $width, $height )
	//  		);
	// 	 }

	// 	return $ret;
	// }

	// /**
	//  * Returns email address of enquirer
	//  * @return string
	//  */
	// public function shortcode_enquiry_from_email() {
	// 	$ret = '';

	// 	if( isset( $this->request->get['route'] ) &&
	// 		'information/contact' === strtolower( $this->request->get['route'] ) &&
	// 		isset( $this->request->post['email'] ) ) {

	// 		$ret = $this->request->post['email'];

	// 	} elseif ( defined( 'PREVIEW' ) ) {
	// 		$ret = 'john_smith@google.com';
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Returns name of enquirer
	//  * @return string
	//  */
	// public function shortcode_enquiry_from_name() {
	// 	$ret = '';

	// 	if( isset( $this->request->get['route'] ) &&
	// 		'information/contact' === strtolower( $this->request->get['route'] ) &&
	// 		isset( $this->request->post['name'] ) ) {

	// 		$ret = $this->request->post['name'];

	// 	} elseif ( defined( 'PREVIEW' ) ) {
	// 		$ret = 'John Smith';
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Returns enquiry text
	//  * @return type
	//  */
	// public function shortcode_enquiry() {
	// 	$ret = '';

	// 	if( isset( $this->request->get['route'] ) &&
	// 		'information/contact' === strtolower( $this->request->get['route'] ) &&
	// 		isset( $this->request->post['enquiry'] ) ) {

	// 		$ret = $this->request->post['enquiry'];

	// 	} elseif ( defined( 'PREVIEW' ) ) {
	// 		$ret = 'Hi, just want to say that your store is marvelous :)';
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Conditional tag. Shows contents in newly created customer account need to be approved
	//  * @return boolean
	//  */
	// public function shortcode_if_account_approve() {
	// 	$status = null;
	// 	$voucher_id = null;

	// 	if( isset( $this->request->request['customer_group_id'] ) ) {
	// 		$group_info = $this->get_customer_group_info( $this->request->request['customer_group_id']  );
	// 		$status = ! empty( $group_info['approval'] );
	// 	}

	// 	return $status;
	// }

	// /**
	//  * Conditional tag. Shows contents in newly created customer account has no need to be approved
	//  * @return boolean
	//  */
	// public function shortcode_if_account_no_approve() {
	// 	return ! $this->shortcode_if_account_approve();
	// }

	// /**
	//  * Returns first name of newly registered customer
	//  * @return string
	//  */
	// public function shortcode_new_customer_first_name() {
	// 	$ret = '';

	// 	if( isset( $this->request->request['firstname'] ) ) {
	// 		$ret = $this->request->post['firstname'];

	// 	} elseif ( defined( 'PREVIEW' ) ) {
	// 		$ret = 'John';
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Returns last name of newly registered customer
	//  * @return string
	//  */
	// public function shortcode_new_customer_last_name() {
	// 	$ret = '';

	// 	if( isset( $this->request->request['lastname'] ) ) {
	// 		$ret = $this->request->request['lastname'];

	// 	} elseif ( defined( 'PREVIEW' ) ) {
	// 		$ret = 'Smith';
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Returns email of newly created customer
	//  * @return string
	//  */
	// public function shortcode_new_customer_email() {
	// 	$ret = '';

	// 	if( isset( $this->request->get['route'] ) &&
	// 		( 'account/register' === strtolower( $this->request->get['route'] ) ||
	// 			'checkout/register/save' === strtolower( $this->request->get['route'] ) ) &&
	// 		isset( $this->request->post['email'] ) ) {

	// 		$ret = $this->request->post['email'];

	// 	} elseif ( defined( 'PREVIEW' ) ) {
	// 		$ret = 'john_smith@google.com';
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Returns telephone number of newly created customer
	//  * @return type
	//  */
	// public function shortcode_new_customer_telephone() {
	// 	$ret = '';
	// 	if( isset( $this->request->request['telephone'] ) ) {
	// 		$ret = $this->request->request['telephone'];

	// 	} elseif ( defined( 'PREVIEW' ) ) {
	// 		$ret = '+7(123)234 45 56';
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Returns address line 1 of newly created customer
	//  * @return string
	//  */
	// public function shortcode_new_customer_address_1() {
	// 	$ret = '';

	// 	if( isset( $this->request->request['address_1'] ) ) {
	// 		$ret = $this->request->request['address_1'];

	// 	} elseif ( defined( 'PREVIEW' ) ) {
	// 		$ret = 'Puddledock 22';
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Returns city of newly created customer
	//  * @return string
	//  */
	// public function shortcode_new_customer_city() {
	// 	$ret = '';

	// 	if( isset( $this->request->request['city'] ) ) {
	// 		$ret = $this->request->request['city'];

	// 	} elseif ( defined( 'PREVIEW' ) ) {
	// 		$ret = 'London';
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Returns customer group name for newly created customer
	//  * @return type
	//  */
	// public function shortcode_new_customer_group() {
	// 	$ret = '';

	// 	if( isset( $this->request->request['customer_group_id'] ) ) {
	// 		$group = $this->get_customer_group_info( $this->request->request['customer_group_id'] );

	// 		if( isset( $group['name'] ) ) {
	// 			$ret = $group['name'];
	// 		}

	// 	} elseif ( defined( 'PREVIEW' ) ) {
	// 		$ret = 'Default';
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Returns country of newly registered customer
	//  * @return string
	//  */
	// public function shortcode_new_customer_country() {
	// 	$ret = '';

	// 	if( isset( $this->request->request['zone_id'] ) ) {
	// 		$region = $this->get_region_info( $this->request->request['zone_id'] );

	// 		if( isset( $region['country_name'] ) ) {
	// 			$ret = $region['country_name'];
	// 		}

	// 	} elseif ( defined( 'PREVIEW' ) ) {
	// 		$ret = 'United Kingdom';
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Returns region of newly created customer
	//  * @return sting
	//  */
	// public function shortcode_new_customer_region() {
	// 	$ret = '';

	// 	if( isset( $this->request->request['zone_id'] ) ) {
	// 		$region = $this->get_region_info( $this->request->request['zone_id'] );

	// 		if( isset( $region['zone_name'] ) ) {
	// 			$ret = $region['zone_name'];
	// 		}

	// 	} elseif ( defined( 'PREVIEW' ) ) {
	// 		$ret = 'Yorkshire';
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Returns first name of newly registered affiliate
	//  * @return string
	//  */
	// public function shortcode_new_affiliate_first_name() {
	// 	$ret = '';

	// 	if( isset( $this->request->get['route'] ) &&
	// 		'affiliate/register' === strtolower( $this->request->get['route'] ) &&
	// 		isset( $this->request->post['firstname'] ) ) {

	// 		$ret = $this->request->post['firstname'];

	// 	} elseif ( defined( 'PREVIEW' ) ) {
	// 		$ret = 'John';
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Returns last name of newly registered affiliate
	//  * @return string
	//  */
	// public function shortcode_new_affiliate_last_name() {
	// 	$ret = '';

	// 	if( isset( $this->request->get['route'] ) &&
	// 		'affiliate/register' === strtolower( $this->request->get['route'] ) &&
	// 		isset( $this->request->post['lastname'] ) ) {

	// 		$ret = $this->request->post['lastname'];

	// 	} elseif ( defined( 'PREVIEW' ) ) {
	// 		$ret = 'Smith';
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Returns email of newly registered affiliate
	//  * @return string
	//  */
	// public function shortcode_new_affiliate_email() {
	// 	$ret = '';

	// 	if( isset( $this->request->get['route'] ) &&
	// 		'affiliate/register' === strtolower( $this->request->get['route'] ) &&
	// 		isset( $this->request->post['email'] ) ) {

	// 		$ret = $this->request->post['email'];

	// 	} elseif ( defined( 'PREVIEW' ) ) {
	// 		$ret = 'john_smith@google.com';
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * returns telephone of newly registered affiliate
	//  * @return string
	//  */
	// public function shortcode_new_affiliate_telephone() {
	// 	$ret = '';

	// 	if( isset( $this->request->get['route'] ) &&
	// 		'affiliate/register' === strtolower( $this->request->get['route'] ) &&
	// 		isset( $this->request->post['telephone'] ) ) {

	// 		$ret = $this->request->post['telephone'];

	// 	} elseif ( defined( 'PREVIEW' ) ) {
	// 		$ret = '+7(123)345 43 56';
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Returns company name of newly registered affiliate
	//  * @return string
	//  */
	// public function shortcode_new_affiliate_company() {
	// 	$ret = '';

	// 	if( isset( $this->request->get['route'] ) &&
	// 		'affiliate/register' === strtolower( $this->request->get['route'] ) &&
	// 		isset( $this->request->post['company'] ) ) {

	// 		$ret = $this->request->post['company'];

	// 	} elseif ( defined( 'PREVIEW' ) ) {
	// 		$ret = 'Wholesale LLC';
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Returns website URl of newly registered affiliate
	//  * @return string
	//  */
	// public function shortcode_new_affiliate_website() {
	// 	$ret = '';

	// 	if( isset( $this->request->get['route'] ) &&
	// 		'affiliate/register' === strtolower( $this->request->get['route'] ) &&
	// 		isset( $this->request->post['website'] ) ) {

	// 		$ret = $this->request->post['website'];

	// 	} elseif ( defined( 'PREVIEW' ) ) {
	// 		$ret = 'http://wholesale.com';
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Returns address line 1 of newly registered affiliate
	//  * @return string
	//  */
	// public function shortcode_new_affiliate_address_1() {
	// 	$ret = '';

	// 	if( isset( $this->request->get['route'] ) &&
	// 		'affiliate/register' === strtolower( $this->request->get['route'] ) &&
	// 		isset( $this->request->post['address_1'] ) ) {

	// 		$ret = $this->request->post['address_1'];

	// 	} elseif ( defined( 'PREVIEW' ) ) {
	// 		$ret = 'Puddledock 11';
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Returns city name of newly registered affiliate
	//  * @return string
	//  */
	// public function shortcode_new_affiliate_city() {
	// 	$ret = '';

	// 	if( isset( $this->request->get['route'] ) &&
	// 		'affiliate/register' === strtolower( $this->request->get['route'] ) &&
	// 		isset( $this->request->post['city'] ) ) {

	// 		$ret = $this->request->post['city'];

	// 	} elseif ( defined( 'PREVIEW' ) ) {
	// 		$ret = 'London';
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Returns country name for newly registered affiliate
	//  * @return string
	//  */
	// public function shortcode_new_affiliate_country() {
	// 	$ret = '';

	// 	if( isset( $this->request->get['route'] ) &&
	// 		'affiliate/register' === strtolower( $this->request->get['route'] ) &&
	// 		isset( $this->caller_args[0]['zone_id'] ) ) {

	// 		$region = $this->get_region_info( $this->caller_args[0]['zone_id'] );

	// 		if( isset( $region['country_name'] ) ) {
	// 			$ret = $region['country_name'];
	// 		}

	// 	} elseif ( defined( 'PREVIEW' ) ) {
	// 		$ret = 'United Kingdom';
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Returns region of newly created affiliate
	//  * @return type
	//  */
	// public function shortcode_new_affiliate_region() {
	// 	$ret = '';

	// 	if( isset( $this->request->get['route'] ) &&
	// 		'affiliate/register' === strtolower( $this->request->get['route'] ) &&
	// 		isset( $this->caller_args[0]['zone_id'] ) ) {

	// 		$region = $this->get_region_info( $this->caller_args[0]['zone_id'] );

	// 		if( isset( $region['zone_name'] ) ) {
	// 			$ret = $region['zone_name'];
	// 		}

	// 	} elseif ( defined( 'PREVIEW' ) ) {
	// 		$ret = 'Yorkshire';
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Conditional tag. Shows contents if affiliate account need to be approved
	//  * @return boolean
	//  */
	// public function shortcode_if_affiliate_approve() {
	// 	$status = null;
	// 	$voucher_id = null;

	// 	if( isset( $this->request->get['route'] ) && 'affiliate/register' === strtolower( $this->request->get['route'] ) ) {
	// 		$status = $this->config->get( 'config_affiliate_approval' );
	// 	}

	// 	return $status;;
	// }

	// /**
	//  * Conditional tag. Shows contents if affiliate account has no need to be approved
	//  * @return boolean
	//  */
	// public function shortcode_if_affiliate_no_approve() {
	// 	return ! $this->shortcode_if_affiliate_approve();
	// }

	// /**
	//  * Returns a product review text
	//  * @return string
	//  */
	// public function shortcode_review_text() {
	// 	$ret = '';

	// 	if( isset( $this->request->get['route'] ) &&
	// 		'product/product/write' === strtolower( $this->request->get['route'] ) &&
	// 		isset( $this->request->post['text'] ) ) {

	// 		$ret = $this->request->post['text'];

	// 	} elseif ( defined( 'PREVIEW' ) ) {
	// 		$ret = 'Awesome product';
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Returns name of reviewer
	//  * @return string
	//  */
	// public function shortcode_review_person() {
	// 	$ret = '';

	// 	if( isset( $this->request->get['route'] ) &&
	// 		'product/product/write' === strtolower( $this->request->get['route'] ) &&
	// 		isset( $this->request->post['name'] ) ) {

	// 		$ret = $this->request->post['name'];

	// 	} elseif ( defined( 'PREVIEW' ) ) {
	// 		$ret = 'John Smith';
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Returns review rating
	//  * @return string
	//  */
	// public function shortcode_review_rating() {
	// 	$ret = '';

	// 	if( isset( $this->request->get['route'] ) &&
	// 		'product/product/write' === strtolower( $this->request->get['route'] ) &&
	// 		isset( $this->request->post['rating'] ) ) {

	// 		$ret = $this->request->post['rating'];

	// 	} elseif ( defined( 'PREVIEW' ) ) {
	// 		$ret = 5;
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Returns reviewed product name
	//  * @return string
	//  */
	// public function shortcode_review_product() {
	// 	$ret = '';

	// 	if( isset( $this->request->get['route'] ) &&
	// 		'product/product/write' === strtolower( $this->request->get['route'] ) &&
	// 		isset( $this->request->get['product_id'] ) ) {

	// 		$product_id = $this->request->get['product_id'];
	// 		$product = $this->get_product_info( $product_id );

	// 		if( $product ) {
	// 			$ret = $product['name'];
	// 		}

	// 	} elseif ( defined( 'PREVIEW' ) ) {
	// 		$ret = 'Canon EOS 5D';
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Returns order ID
	//  * @return string
	//  */
	// public function shortcode_order_id() {
	// 	$ret = '';

	// 	$order = $this->get_from_cache( 'old_order' );
	// 	if( $order && isset( $order['order_id'] ) ) {
	// 		$ret = $order['order_id'];

	// 	} elseif ( defined( 'PREVIEW' ) && ( $sample_order = $this->get_sample_order() ) ) {
	// 		$ret = $sample_order['order_id'];
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Conditional tag. Shows content if order contains download-able product
	//  * @return boolean
	//  */
	// public function shortcode_if_order_download() {
	// 	$status = null;

	// 	$order = $this->get_from_cache( 'old_order' );
	// 	if( $order && isset( $order['order_id'] ) ) {
	// 		$products = $this->get_order_downloaded_products( $order['order_id'] );

	// 		$status = (boolean)$products;
	// 	}

	// 	return $status;;
	// }

	// /**
	//  * Conditional tag. Shows contents if order need to be approved
	//  * @return boolean
	//  */
	// public function shortcode_if_order_approve() {
	// 	$status = null;

	// 	if( isset( $this->caller_args[1] ) ) {
	// 		$status = in_array( $this->caller_args[1], $this->config->get( 'config_complete_status' ) );
	// 	}

	// 	return $status;
	// }
	// /**
	//  * Conditional tab. Shows contents if order has no need to be approved
	//  * @return type
	//  */
	// public function shortcode_if_order_no_approve( $shortcode ) {
	// 	return ! $this->shortcode_if_order_approve();
	// }

	/**
	 * Formats address pertain to an order
	 * @param array $data Order detail
	 * @param string $type Address type
	 * @return string
	 */
	public function format_address( $data, $type ) {
		if ( ! empty( $data[ $type . '_address_format' ] ) ) {
				$format = $data[ $type . '_address_format' ];

		} else {
			$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
		}

		$find = array(
			'{firstname}',
			'{lastname}',
			'{company}',
			'{address_1}',
			'{address_2}',
			'{city}',
			'{postcode}',
			'{zone}',
			'{zone_code}',
			'{country}'
		);

		$replace = array(
			'firstname' => isset( $data[ $type . '_firstname' ] ) ?
				$data[ $type . '_firstname' ]: '',
			'lastname'  => isset( $data[ $type . '_lastname' ] ) ?
				$data[ $type . '_lastname' ] : '',
			'company'   => isset( $data[ $type . '_company' ] ) ?
				$data[ $type . '_company' ] : '',
			'address_1' => isset( $data[ $type . '_address_1' ] ) ?
				$data[ $type . '_address_1' ] : '',
			'address_2' => isset( $data[ $type . '_address_2' ] ) ?
				$data[ $type . '_address_2' ] : '',
			'city'      => isset( $data[ $type . '_city' ] ) ?
				$data[ $type . '_city' ] : '',
			'postcode'  => isset( $data[ $type . '_postcode' ] ) ?
				$data[ $type . '_postcode' ] : '',
			'zone'      => isset( $data[ $type . '_zone' ] ) ?
				$data[ $type . '_zone' ] : '',
			'zone_code' => isset( $data[ $type . '_zone_code' ] ) ? 
				$data[ $type . '_zone_code' ] : '',
			'country'   => isset( $data[ $type . '_country' ] ) ?
				$data[ $type . '_country' ] : '',
		);

		$ret = preg_split( '/[\r\n]{1,}/', trim( str_replace( $find, $replace, $format ) ) );
		array_walk( $ret, function( &$e ) { $e = '<p>' . $e . '</p>'; } );

		return implode( '', $ret );
	}

	/**
	 * Sorts array by some value
	 * @param array $array Target array
	 * @param string $name Value name
	 * @return array Output array
	 */
	// public function sort_by( $array, $name ) {
	// 	$out = array();

	// 	foreach( $array as $item ) {
	// 		$index = 0;

	// 		while( $index < count( $out ) && $item[ $name ] >= $out[ $index ][ $name ] ) {
	// 			$index++;
	// 		}

	// 		array_splice( $out, $index, 0, array( $item ) );
	// 	}

	// 	return $out;
	// }

// 	/**
// 	 * Returns tabulated invoice details
// 	 * @return string
// 	 */
// 	public function shortcode_invoice_table() {
// 		$comment = '';

// 		$data = $this->get_from_cache( 'old_order_data' );

// 		if( ! $data && defined( 'PREVIEW' ) ) {
// 			$data = $this->get_sample_order();

// 			$products = $this->get_order_products( $data['order_id' ] );
// 			$vouchers = $this->get_order_vouchers( $data['order_id'] );
// 			$totals = $this->sort_by( $this->get_order_totals( $data['order_id'] ), 'sort_order' );
// 		}

// 		if( ! $data ) {
// 			return '';
// 		}

// 		if( isset( $this->caller_args[2] ) ) {
// 			$comment = $this->caller_args[2];
// 		}
// 		$text_order_id = $this->__( 'Order ID' );
// 		$text_date_added = $this->__( 'Date added' );
// 		$text_payment_method = $this->__( 'Payment method' );
// 		$text_shipping_method = $this->__( 'Shipping method' );
// 		$text_email = $this->__( 'Email' );
// 		$text_telephone = $this->__( 'Telephone' );
// 		$text_ip = $this->__( 'IP address' );
// 		$text_order_status = $this->__( 'Order status' );
// 		$text_instruction = $this->__( 'Instructions' );
// 		$text_payment_address = $this->__( 'Payment address' );
// 		$text_shipping_address = $this->__( 'Shipping address' );
// 		$text_product = $this->__( 'Product' );
// 		$text_model = $this->__( 'Model' );
// 		$text_quantity = $this->__( 'Quantity' );
// 		$text_price = $this->__( 'Price' );
// 		$text_total = $this->__( 'Total' );
// 		$text_order_detail = $this->__( 'Order details' );

// 		$payment_address = $this->format_address( $data, 'payment' );
// 		$shipping_address = $this->format_address( $data, 'shipping' );

// 		extract( $data );

// 		$ret =
// <<<HTML
// <style>
// .invoice-table td {
// 	margin: 1px;
// 	line-height: 1em;
// 	height: 1em;
// }
// </style>
// <table class="invoice-table" style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">
// 	<thead>
// 		<tr>
// 			<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: left; padding: 7px; color: #222222;" colspan="2">
// 				$text_order_detail
// 			</td>
// 		</tr>
// 	</thead>
// 	<tbody>
// 		<tr>
// 			<td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">
// 				<b>$text_order_id</b> $order_id
// 				<br/>
// 				<b>$text_date_added</b> $date_added
// 				<br/>
// 				<b>$text_payment_method</b> $payment_method
// 				<br/>
// HTML;

// 		if ( $shipping_method ) {
// 			$ret .=
// 				"<b>$text_shipping_method</b> $shipping_method";
// 		}

// 		$ret .=
// 			<<<HTML
// 			</td>
// 			<td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">
// 				<b>$text_email</b> $email
// 				<br/>
// 				<b>$text_telephone</b> $telephone
// 				<br/>
// 				<b>$text_ip</b> $ip
// 				<br/>
// 				<b>$text_order_status</b> $order_status
// 				<br/>
// 			</td>
// 		</tr>
// 	</tbody>
// </table>
// HTML;

// 		if ($comment) {
// 			$ret .=
// <<<HTML
// <table class="invoice-table" style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">
// 	<thead>
// 		<tr>
// 			<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: left; padding: 7px; color: #222222;">
// 				$text_instruction
// 			</td>
// 		</tr>
// 	</thead>
// 	<tbody>
// 		<tr>
// 			<td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">
// 				$comment
// 			</td>
// 		</tr>
// 	</tbody>
// </table>
// HTML;
// 		}

// 		$ret .=
// <<<HTML
// <table class="invoice-table" style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">
// 	<thead>
// 		<tr>
// 			<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: left; padding: 7px; color: #222222;">
// 				$text_payment_address
// 			</td>
// HTML;
// 		if ($shipping_address) {
// 			$ret .=
//        		"<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: left; padding: 7px; color: #222222;\">
//        			$text_shipping_address
//        		</td>";
// 		}

// 		$ret .=
// <<<HTML
// 		</tr>
// 	</thead>
// 	<tbody>
// 		<tr>
// 			<td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">
// 				$payment_address
// 			</td>
// HTML;
// 		if ($shipping_address) {
// 			$ret .=
// 			"<td style=\"font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\">
// 				$shipping_address
// 			</td>";
// 		}

// 		$ret .=
// <<<HTML
// 		</tr>
// 	</tbody>
// </table>
// <table class="invoice-table" style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">
// 	<thead>
// 		<tr>
// 			<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: left; padding: 7px; color: #222222;">
// 				$text_product
// 			</td>
// 			<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: left; padding: 7px; color: #222222;">
// 				$text_model
// 			</td>
// 			<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: right; padding: 7px; color: #222222;">
// 				$text_quantity
// 			</td>
// 			<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: right; padding: 7px; color: #222222;">
// 				$text_price
// 			</td>
// 			<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: right; padding: 7px; color: #222222;">
// 				$text_total
// 			</td>
// 		</tr>
// 	</thead>
// 	<tbody>
// HTML;
// 	     foreach ( $products as $product ) {
// 			$ret .=
// 		"<tr>
// 			<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\">
// 				{$product['name']}";

// 			if ( isset( $product['option'] ) ) {
// 				foreach ($product['option'] as $option) {
// 					$ret .=
// 					"<br/>
// 					&nbsp;<small>
// 						{$option['name']} : {$option['value']}
// 					</small>";
// 				}
// 			}

// 			$ret .=
// <<<HTML
// 			</td>
// 			<td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">
// 				{$product['model']}
// 			</td>
// 			<td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: right; padding: 7px;">
// 				{$product['quantity']}
// 			</td>
// 			<td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: right; padding: 7px;">
// 				{$product['price']}
// 			</td>
// 			<td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: right; padding: 7px;">
// 				{$product['total']}
// 			</td>
// 		</tr>
// HTML;
// 		}

// 		foreach ( $vouchers as $voucher ) {
// 			$ret .=
// <<<HTML
// 		<tr>
// 			<td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">
// 				{$voucher['description']}
// 			</td>
// 			<td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;"></td>
// 			<td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: right; padding: 7px;">1</td>
// 			<td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: right; padding: 7px;">
// 				{$voucher['amount']}
// 			</td>
// 			<td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: right; padding: 7px;">
// 				{$voucher['amount']}
// 			</td>
// 		</tr>
// HTML;
// 		}

// 		$ret .=
// 	'</tbody>
// 	<tfoot>';

// 		foreach ($totals as $total) {
// 			$total_text = '';

// 			if ( isset( $total['text'] ) ) {
// 				$total_text = $total['text'];

// 			// Preview mode
// 			} elseif ( isset( $total['value'] ) && isset( $currency_code ) ) {
// 				$total_text = $this->currency->format( $total['value'], $currency_code );
// 			}

// 			$ret .=
// <<<HTML
// 		<tr>
// 			<td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: right; padding: 7px;" colspan="4">
// 				<b>{$total['title']}:</b>
// 			</td>
// 			<td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: right; padding: 7px;">
// 				$total_text
// 			</td>
// 		</tr>
// HTML;
// 		}

// 		$ret .=
// 	'</tfoot>
// </table>';

// 	return '<template_variant><html_variant>' . $ret . '</html_variant><text_variant>' . $this->shortcode_invoice_table_text() . '</text_variant></template_variant>';

// 	}

// 	/**
// 	 * Returns tabulated invoice details
// 	 * @return string
// 	 */
// 	public function shortcode_invoice() {
// 		$args = func_get_args();

// 		if( ! isset( $args[1] ) ) {
// 			return '';
// 		}

// 		$shortcode = $this->get_shortcode( $args[1] );

// 		if ( ! $shortcode ) {
// 			trigger_error( sprintf( 'Shortcode with ID "#%s" is missing', $args[1] ) );
// 			return '';
// 		}

// 		$data = $this->get_from_cache( 'old_order_data' );

// 		if( ! $data && defined( 'PREVIEW' ) ) {
// 			$data = $this->get_sample_order();
// 			$products = $this->get_order_products( $data['order_id' ] );
// 			$vouchers = $this->get_order_vouchers( $data['order_id'] );
// 			$totals = $this->sort_by( $this->get_order_totals( $data['order_id'] ), 'sort_order' );
// 			$payment_address = $this->format_address( $data, 'payment' );

// 			if ( empty( $data['shipping_firstname'] ) ) {
// 				$shipping_address = $this->format_address( $data, 'payment' );

// 			} else {
// 				$shipping_address = $this->format_address( $data, 'shipping' );	
// 			}
// 		}

// 		if( ! $data ) {
// 			return '';
// 		}

// 		$text_order_id = $this->__( 'Order ID' );
// 		$text_date_added = $this->__( 'Date added' );
// 		$text_payment_method = $this->__( 'Payment method' );
// 		$text_shipping_method = $this->__( 'Shipping method' );
// 		$text_email = $this->__( 'Email' );
// 		$text_telephone = $this->__( 'Telephone' );
// 		$text_ip = $this->__( 'IP address' );
// 		$text_order_status = $this->__( 'Order status' );
// 		$text_instruction = $this->__( 'Instructions' );
// 		$text_payment_address = $this->__( 'Payment address' );
// 		$text_shipping_address = $this->__( 'Shipping address' );
// 		$text_product = $this->__( 'Product' );
// 		$text_model = $this->__( 'Model' );
// 		$text_quantity = $this->__( 'Quantity' );
// 		$text_price = $this->__( 'Price' );
// 		$text_total = $this->__( 'Total' );
// 		$text_order_detail = $this->__( 'Order details' );

// 		$header_color = $shortcode['data']['header']['color'];
// 		$header_text_color = $shortcode['data']['header']['text']['color'];
// 		$header_text_height = $shortcode['data']['header']['text']['height'];
// 		$body_color = $shortcode['data']['body']['color'];
// 		$body_text_color = $shortcode['data']['body']['text']['color'];
// 		$body_text_height = $shortcode['data']['body']['text']['height'];
// 		$table_border = "{$shortcode['data']['table']['border']['width']}px solid {$shortcode['data']['table']['border']['color']}";
// 		$header_border = "{$shortcode['data']['header']['border']['width']}px solid {$shortcode['data']['header']['border']['color']}";
// 		$body_border = "{$shortcode['data']['body']['border']['width']}px solid {$shortcode['data']['body']['border']['color']}";

// 		$product_width = $shortcode['data']['product']['image']['width'];

// 		$show_order_details = ! empty( $shortcode['data']['fields']['order'] );
// 		$show_shipping_address = ! empty( $shortcode['data']['fields']['shipping'] );
// 		$show_payment_address = ! empty( $shortcode['data']['fields']['payment'] );
// 		$show_products = ! empty( $shortcode['data']['fields']['products'] );
// 		$show_image = ! empty( $shortcode['data']['fields']['image'] );
// 		$show_totals = ! empty( $shortcode['data']['fields']['totals'] );
// 		$show_comment = ! empty( $shortcode['data']['fields']['comment'] );

// 		extract( $data );

// 		// Comment will be empty, in order's data, if a notify customer upon new order setting will be disabled
// 		if( empty( $data['comment'] ) && $this->has_in_cache( 'old_order' ) ) {
// 			$old_order = $this->get_from_cache( 'old_order' );
// 			$comment = isset( $old_order['comment'] ) ? $old_order['comment'] : '';
// 		}

// 		if ( ! $comment && defined( 'PREVIEW' ) ) {
// 			$comment = $this->__( 'Deliver the order between 2pm and 4pm' );
// 		}

// 		if ( $show_image ) {
// 			$this->load->model( 'tool/image' );

// 			// Wee need full product information in order to show image
// 			if ( defined( 'PREVIEW' ) ) {
// 				foreach( $products as $p ) {
// 					$products_info[ $p['model'] ] = $p;
// 				}

// 			} else {
// 				foreach( $this->get_order_products( $data['order_id' ] ) as $p ) {
// 					$products_info[ $p['model'] ] = $p;
// 				}
// 			}
// 		}

// 		$ret =
// <<<HTML
// <style>
// 	.invoice-table {
// 		border-collapse: collapse;
// 		width: 100%;
// 		border-top: $table_border;
// 		border-left: $table_border;
// 		margin-bottom: 20px;
// 	}

// 	.invoice-table img {
// 		float: left;
// 		margin-right: 10px;
// 	}

// 	.invoice-table td {
// 		margin: 1px;
// 		line-height: 1em;
// 		height: 1em;
// 	}

// 	.invoice-table p {
// 		padding-top: 4px;
// 	}

// 	.invoice-head{
// 		font-size: {$header_text_height}px;
// 		color: $header_text_color;
// 		background-color: $header_color;
// 		border-right: $header_border;
// 		border-bottom: $header_border;
// 		font-weight: bold;
// 		text-align: left;
// 		padding: 7px;
// 	}

// 	.invoice-body {
// 		font-size: {$body_text_height}px;
// 		border-right: $body_border;
// 		border-bottom: $body_border;
// 		text-align: left;
// 		padding: 7px;
// 		background-color: $body_color;
// 	}
// </style>
// HTML;

// 		if ( $show_order_details ) :
// 			$ret .=
// <<<HTML
// <table class="invoice-table">
// 	<thead>
// 		<tr>
// 			<td class="invoice-head" colspan="2">
// 				$text_order_detail
// 			</td>
// 		</tr>
// 	</thead>
// 	<tbody>
// 		<tr>
// 			<td class="invoice-body">
// 				<p><b>$text_order_id</b> $order_id</p>
				
// 				<p><b>$text_date_added</b> $date_added</p>
				
// 				<p><b>$text_payment_method</b> $payment_method</p>
				
// HTML;

// 		if ( $shipping_method ) {
// 			$ret .=
// 				"<p><b>$text_shipping_method</b> $shipping_method</p>";
// 		}

// 		$ret .=
// <<<HTML
// 			</td>
// 			<td class="invoice-body">
// 				<p><b>$text_email</b> $email</p>
				
// 				<p><b>$text_telephone</b> $telephone</p>
				
// 				<p><b>$text_ip</b> $ip</p>
				
// 				<p><b>$text_order_status</b> $order_status</p>
				
// 			</td>
// 		</tr>
// 	</tbody>
// </table>
// HTML;

// 		endif;

// 		if ( $comment && $show_comment ) :
// 			$ret .=
// <<<HTML
// <table class="invoice-table">
// 	<thead>
// 		<tr>
// 			<td class="invoice-head">
// 				$text_instruction
// 			</td>
// 		</tr>
// 	</thead>
// 	<tbody>
// 		<tr>
// 			<td class="invoice-body">
// 				$comment
// 			</td>
// 		</tr>
// 	</tbody>
// </table>
// HTML;
// 		endif;

// 		if ( $show_payment_address || $show_shipping_address ) :

// 		$ret .=
// <<<HTML
// <table class="invoice-table">
// 	<thead>
// 		<tr>
// HTML;
// 		if ( $show_payment_address ) :
// 			$ret .=
// <<<HTML
// 			<td class="invoice-head">
// 				$text_payment_address
// 			</td>
// HTML;
// 		endif;

// 		if ( $shipping_address && $show_shipping_address ) :
// 			$ret .=
// <<<HTML
//        		<td class="invoice-head">
//        			$text_shipping_address
//        		</td>
// HTML;
// 		endif;

// 		$ret .=
// <<<HTML
// 		</tr>
// 	</thead>
// 	<tbody>
// 		<tr>
// HTML;
// 		if ( $show_payment_address ) : 
// 			$ret .= <<<HTML
// 			<td class="invoice-body">
// 				$payment_address
// 			</td>
// HTML;
// 		endif;

// 		if ( $shipping_address && $show_shipping_address ) :
// 			$ret .= <<<HTML
// 			<td class="invoice-body">
// 				$shipping_address
// 			</td>
// HTML;
// 		endif;

// 		$ret .= <<<HTML
// 		</tr>
// 	</tbody>
// </table>
// HTML;
// 		endif;

// 		if ( $show_products ) :
// 			$ret .=
// <<<HTML
// <table class="invoice-table">
// 	<thead>
// 		<tr>
// 			<td class="invoice-head">
// 				$text_product
// 			</td>
// 			<td class="invoice-head">
// 				$text_model
// 			</td>
// 			<td class="invoice-head">
// 				$text_quantity
// 			</td>
// 			<td class="invoice-head">
// 				$text_price
// 			</td>
// 			<td class="invoice-head">
// 				$text_total
// 			</td>
// 		</tr>
// 	</thead>
// 	<tbody>
// HTML;
// 	     foreach ( $products as $product ) {

// 	     	if ( $show_image ) {
// 	     		$resized_image = $this->model_tool_image->resize(
// 	     			$products_info[ $product['model'] ]['image'], $product_width, $product_width
// 	     		);
// 	     	}

// 			$ret .=
// 		"<tr>
// 			<td class='invoice-body'>" .
// 			( $show_image ? '<img src="' . $this->get_img( $resized_image, true ) . '" />' : '' ) .
// 				"<div style='" . ( $show_image ? 'padding-top:' . ( ( $product_width - $body_text_height ) / 2 ) . 'px' : '' ) .
// 				"'>{$product['name']}</div>";

// 			if ( isset( $product['option'] ) ) {
// 				foreach ($product['option'] as $option) {
// 					$ret .=
// 					"<br/>
// 					&nbsp;<small>
// 						{$option['name']} : {$option['value']}
// 					</small>";
// 				}
// 			}

// 			$ret .=
// <<<HTML
// 			</td>
// 			<td class="invoice-body">
// 				{$product['model']}
// 			</td>
// 			<td class="invoice-body">
// 				{$product['quantity']}
// 			</td>
// 			<td class="invoice-body">
// 				{$product['price']}
// 			</td>
// 			<td class="invoice-body">
// 				{$product['total']}
// 			</td>
// 		</tr>
// HTML;
// 		}

// 		foreach ( $vouchers as $voucher ) {
// 			$ret .=
// <<<HTML
// 		<tr>
// 			<td class="invoice-body">
// 				{$voucher['description']}
// 			</td>
// 			<td class="invoice-body"></td>
// 			<td class="invoice-body">1</td>
// 			<td class="invoice-body">
// 				{$voucher['amount']}
// 			</td>
// 			<td class="invoice-body">
// 				{$voucher['amount']}
// 			</td>
// 		</tr>
// HTML;
// 		}

// 		$ret .=
// 	'</tbody>';

// 	if ( $show_totals ) :
// 			$ret .=
// 	'<tfoot>';

// 		foreach ( $totals as $total ) {
// 			$total_value = '';

// 			if( isset( $total['text'] ) ) {
// 				$total_value = $total['text'];

// 			} elseif ( isset( $total['value'] ) && isset( $currency_code ) ) {
// 				$total_value = $this->currency->format( $total['value'], $currency_code );
// 			}

// 			$ret .=
// <<<HTML
// 		<tr>
// 			<td class="invoice-body" colspan="4">
// 				<b>{$total['title']}:</b>
// 			</td>
// 			<td class="invoice-body">
// 				{$total_value}
// 			</td>
// 		</tr>
// HTML;
// 		}

// 		$ret .=
// 	'</tfoot>';

// 		endif;
// 		$ret .=
// '</table>';

// 		endif;

// 	return '<template_variant><html_variant>' . $ret . '</html_variant><text_variant>' . $this->shortcode_invoice_table_text() . '</text_variant></template_variant>';

// 	}

// 	/**
// 	 * Returns invoice text representation
// 	 * @return string
// 	 */
// 	public function shortcode_invoice_table_text() {

// 		if( ! defined( 'PREVIEW' ) ) {
// 			if( ! isset( $this->caller_args[0] ) ) {
// 				return '';
// 			}

// 			$order_info = $this->get_order_info( $this->caller_args[0] );

// 		} else {
// 			$order_info = $this->get_sample_order();
// 		}

// 		if( ! $order_info ) {
// 			return '';
// 		}

// 		$order_id = $order_info['order_id'];
// 		$this->load->model( 'tool/upload' );

// 		$comment = '';
// 		if( isset( $this->caller_args[2] ) ) {
// 			$comment = $this->caller_args[2];
// 		}

// 		$text  = '';
// 		$text .= $this->__( 'Order ID' ) . ' ' . $order_info['order_id'] . '<br>';
// 		$text .= $this->__( 'Date added' ) . ' ' .
// 			date( 'd/m/Y', strtotime( $order_info['date_added'] ) ) . '<br>';

// 		$text .= $this->__( 'Order status' ) . ' ' .
// 			$this->get_order_status_name( $order_info['order_status_id'], $order_info['language_id'] ) .
// 			'<br><br>';

// 		if ( $comment ) {
// 			$text .= $this->__( 'Instructions' ) . '<br><br>';
// 			$text .= $comment . '<br><br>';
// 		}

// 		// Products
// 		$text .= $this->__( 'Products' ) . '<br>';
// 		$text .= $this->shortcode_order_products(); 
// 		$text .= $this->shortcode_order_vouchers();
// 		$text .= '<br>';

// 		$text .= $this->__( 'Total' ) . '<br>';
// 		$text .= $this->shortcode_order_totals();
// 		$text .= '<br>';

// 		if ($order_info['customer_id']) {
// 			$text .= $this->__( 'Link to the order' ) . '<br>';
// 			$text .= $order_info['store_url'] . 'index.php?route=account/order/info&order_id=' . $order_id . '<br><br>';
// 		}

// 		if ( $this->get_order_downloaded_products( $order_id ) ) {
// 			$text .= $this->__( 'Download' ) . '<br>';
// 			$text .= $order_info['store_url'] . 'index.php?route=account/download' . '<br><br>';
// 		}

// 		// Comment
// 		if ($order_info['comment']) {
// 			$text .= $this->__( 'Comment' ) . '<br><br>';
// 			$text .= $order_info['comment'] . '<br><br>';
// 		}

// 		return $text;
// 	}

// 	/**
// 	 * Returns order creation data
// 	 * @return sting
// 	 */
// 	public function shortcode_order_date_added() {
// 		$ret = '';

// 		if( isset( $this->caller_args[0] ) ) {
// 			$order_info = $this->get_order_info( $this->caller_args[0] );

// 		} elseif ( defined( 'PREVIEW' ) ) {
// 			$order_info = $this->get_sample_order();
// 		}

// 		if( isset( $order_info['date_added'] ) ) {
// 			$ret = $order_info['date_added' ];
// 		}

// 		return $ret;
// 	}

// 	/**
// 	 * Returns comment added by customer to the order
// 	 * @return string
// 	 */
// 	public function shortcode_order_comment() {
// 		$ret = '';

// 		if( isset( $this->caller_args[0] ) ) {
// 			$order_info = $this->get_order_info( $this->caller_args[0] );

// 		} elseif ( defined( 'PREVIEW' ) ) {
// 			$order_info = $this->get_sample_order();
// 		}

// 		if( isset( $order_info['comment'] ) ) {
// 			$ret = $order_info['comment' ];
// 		}

// 		return $ret;
// 	}

// 	/**
// 	 * Returns comment added on order status change
// 	 * @return string
// 	 */
// 	public function shortcode_order_status_comment() {
// 		$ret = '';

// 		if( isset( $this->caller_args[2] ) ) {
// 			$ret = $this->caller_args[2];

// 		} elseif ( defined( 'PREVIEW' ) ) {
// 			$ret = $this->__( 'Your order has been approved' );
// 		}

// 		return $ret;
// 	}

// 	/**
// 	 * Conditional tag. Shows contents if an order change status comment is present
// 	 * @return string
// 	 */
// 	public function shortcode_if_order_status_comment( $shortcode ) {
// 		return (boolean)$this->shortcode_order_status_comment();
// 	}

// 	/**
// 	 * Conditional tag. Shows contents if an order change status comment is not present
// 	 * @return string
// 	 */
// 	public function shortcode_if_order_status_no_comment( $shortcode ) {
// 		return ! $this->shortcode_order_status_comment();
// 	}

// 	/**
// 	 * Conditional tag. Shows contents if comment to an order is present
// 	 * @return string
// 	 */
// 	public function shortcode_if_order_comment( $shortcode ) {
// 		return (boolean)$this->shortcode_order_comment();
// 	}

// 	/**
// 	 * Conditional tag. Shows contents if comment to an order is not present
// 	 * @return string
// 	 */
// 	public function shortcode_if_order_no_comment( $shortcode ) {
// 		return ! $this->shortcode_order_comment();
// 	}

// 	/**
// 	 * Returns a new order status name
// 	 * @return string
// 	 */
// 	public function shortcode_order_status() {
// 		return $this->shortcode_order_status_new();
// 	}

// 	/**
// 	 * Returns a new order status name
// 	 * @return string
// 	 */
// 	public function shortcode_order_status_new() {
// 		$ret = '';

// 		if( isset( $this->caller_args[0] ) ) {
// 			$order_info = $this->get_order_info( $this->caller_args[0] );

// 		} elseif ( defined( 'PREVIEW' ) ) {
// 			$order_info = $this->get_sample_order();
// 		}

// 		if( $order_info && isset( $order_info['order_status_id'] ) && isset( $order_info['language_id'] ) ) {
// 			$ret = $this->get_order_status_name( $order_info['order_status_id'], $order_info['language_id'] );
// 		}

// 		return $ret;
// 	}

// 	/**
// 	 * Returns a previous order status name
// 	 * @return string
// 	 */
// 	public function shortcode_order_status_old() {
// 		$ret = '';

// 		if( $this->has_in_cache( 'old_order_data' ) ) {
// 			$order_info = $this->get_from_cache( 'old_order_data' );

// 		} elseif ( defined( 'PREVIEW' ) ) {
// 			$order_info = $this->get_sample_order();
// 		}

// 		if( $order_info && isset( $order_info['order_status'] ) ) {
// 			$ret = $order_info['order_status'];
// 		}

// 		return $ret;
// 	}

// 	/**
// 	 * Returns link to an order page
// 	 * @return string
// 	 */
// 	public function shortcode_order_url() {
// 		$ret = '';
// 		$args = func_get_args();
// 		$text = empty( $args[1] ) ? $this->__( 'Order' ) : $args[1];
// 		$order = $this->get_from_cache( 'old_order' );

// 		if( ! $order && defined( 'PREVIEW' ) ) {
// 			$order = $this->get_sample_order();
// 		}

// 		if ( isset( $order['order_id'] ) && isset( $order['store_url'] ) ) {
// 			$ret = sprintf(
// 				'<a href="%s" target="_blank">%s</a>',
// 				$order['store_url'] . 'index.php?route=account/order/info&order_id=' . $order['order_id'],
// 				$text
// 			);

// 		}

// 		return $ret;
// 	}

// 	/**
// 	 * Returns link to the download order page
// 	 * @return type
// 	 */
// 	public function shortcode_download_url() {
// 		$ret = '';
// 		$args = func_get_args();
// 		$text = empty( $args[1] ) ? $this->__( 'Download' ) : $args[1];

// 		$ret = sprintf(
// 			'<a href="%s" target="_blank">%s</a>',
// 			( defined( 'HTTPS_CATALOG' ) ? HTTPS_CATALOG : HTTPS_SERVER ) . 'index.php?route=account/download',
// 			$text
// 		);

// 		return $ret;
// 	}

// 	/**
// 	 * Returns product(s) details for an order
// 	 * @return string
// 	 */
// 	public function shortcode_order_products() {
// 		$text = '';

// 		if( isset( $this->caller_args[0] ) ) {
// 			$order_info = $this->get_order_info( $this->caller_args[0] );

// 		} elseif ( defined( 'PREVIEW' ) ) {
// 			$order_info = $this->get_sample_order();
// 		}

// 		if( $order_info && isset( $order_info['order_id'] ) ) {
// 			$order_id = $order_info['order_id'];

// 			foreach ( $this->get_order_products( $order_id ) as $product ) {
// 				$text .= $product['quantity'] . 'x ' . $product['name'] . ' (' . $product['model'] . ') ' .
// 					html_entity_decode(
// 						$this->currency->format(
// 							$product['total'] + ( $this->config->get( 'config_tax' ) ? ( $product['tax'] * $product['quantity'] ) : 0 ),
// 							$order_info['currency_code'],
// 							$order_info['currency_value']
// 						),
// 						ENT_NOQUOTES,
// 						'UTF-8'
// 					) . '<br>';

// 				$order_option_query = $this->db->query( "SELECT * FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . $product['order_product_id'] . "'" );

// 				foreach ($order_option_query->rows as $option) {
// 					if ($option['type'] != 'file') {
// 						$value = $option['value'];

// 					} else {
// 						$upload_info = $this->model_tool_upload->getUploadByCode( $option['value'] );

// 						if ( $upload_info ) {
// 							$value = $upload_info['name'];

// 						} else {
// 							$value = '';
// 						}
// 					}

// 					$text .= chr(9) . '-' . $option['name'] . ' ' . ( utf8_strlen( $value ) > 20 ? utf8_substr( $value, 0, 20 ) . '..' : $value ) . '<br>';
// 				}
// 			}
// 		}

// 		return $text;
// 	}

// 	/**
// 	 * Conditional tag. Shows contents if order does not contain all of the products with specific SKU
// 	 * @return string
// 	 */
// 	public function shortcode_if_no_products_sku_all() {
// 		return ! call_user_func_array( array( $this, 'shortcode_if_products_sku' ), func_get_args() );
// 	}

// 	/**
// 	 * Conditional tag. Shows contents if order contains at least one product with specific SKU
// 	 * @return string
// 	 */
// 	public function shortcode_if_products_sku() {
// 		$ret = false;

// 		// List of products SKUs
// 		$products = array_slice( func_get_args(), 1 );

// 		if( ! $products ) {
// 			return $ret;
// 		}

// 		if( isset( $this->caller_args[0] ) ) {
// 			$order_info = $this->get_order_info( $this->caller_args[0] );

// 		} elseif ( defined( 'PREVIEW' ) ) {
// 			$order_info = $this->get_sample_order();
// 		}

// 		if( $order_info && isset( $order_info['order_id'] ) ) {

// 			$product_ids = array();

// 			foreach ( $this->get_order_products( $order_info['order_id'] ) as $product ) {
// 				$product_ids[] = $product['product_id'];
// 			}

// 			foreach( $this->get_products_by_id( $product_ids ) as $product ) {
// 				if( in_array( $product['sku'], $products ) ) {
// 					$ret = true;
// 					break;
// 				}
// 			}
// 		}

// 		return $ret;
// 	}

// 	/**
// 	 * Conditional tag. Shows contents if order contains all the product with specific SKU
// 	 * @return string
// 	 */
// 	public function shortcode_if_products_sku_all() {
// 		$ret = false;

// 		// List of products SKUs
// 		$products = array_slice( func_get_args(), 1 );

// 		if( ! $products ) {
// 			return $ret;
// 		}

// 		if( isset( $this->caller_args[0] ) ) {
// 			$order_info = $this->get_order_info( $this->caller_args[0] );

// 		} elseif ( defined( 'PREVIEW' ) ) {
// 			$order_info = $this->get_sample_order();
// 		}

// 		if( $order_info && isset( $order_info['order_id'] ) ) {

// 			$product_ids = array();

// 			foreach ( $this->get_order_products( $order_info['order_id'] ) as $product ) {
// 				$product_ids[] = $product['product_id'];
// 			}

// 			$skus = array();

// 			foreach( $this->get_products_by_id( $product_ids ) as $product ) {
// 				$skus[] = $product['sku'];
// 			}

// 			$ret = (boolean)$skus;

// 			foreach( $products as $product_sku ) {
// 				if( ! in_array( $product_sku, $skus ) ) {
// 					$ret = false;
// 					break;
// 				}
// 			}
// 		}

// 		return $ret;
// 	}

// 	/**
// 	 * Conditional tag. Shows contents if order does not contain at least one of the product with specific SKU
// 	 * @return string
// 	 */
// 	public function shortcode_if_no_products_sku() {
// 		return ! call_user_func_array( array( $this, 'shortcode_if_products_sku_all' ), func_get_args() );
// 	}

// 	/**
// 	 * Returns list of vouchers pertain to an order
// 	 * @return string
// 	 */
// 	public function shortcode_order_vouchers() {
// 		$text = '';

// 		if( isset( $this->caller_args[0] ) ) {
// 			$order_info = $this->get_order_info( $this->caller_args[0] );

// 		} elseif ( defined( 'PREVIEW' ) ) {
// 			$order_info = $this->get_sample_order();
// 		}

// 		if( $order_info && ( isset( $order_info['order_id'] ) ) ) {
// 			$order_id = $order_info['order_id'];

// 			foreach ( $this->get_order_vouchers( $order_id ) as $voucher) {
// 				$text .= '1x ' . $voucher['description'] . ' ' .
// 					$this->currency->format(
// 						$voucher['amount'],
// 						$order_info['currency_code'],
// 						$order_info['currency_value']
// 					);
// 			}
// 		}

// 		return $text;
// 	}

// 	/**
// 	 * Returns an order totals list
// 	 * @return string
// 	 */
// 	public function shortcode_order_totals() {
// 		$text = '';

// 		if( isset( $this->caller_args[0] ) ) {

// 			$order_info = $this->get_order_info( $this->caller_args[0] );

// 		} elseif ( defined( 'PREVIEW' ) ) {
// 			$order_info = $this->get_sample_order();
// 		}

// 		if( $order_info && ( isset( $order_info['order_id'] ) ) ) {
// 			$order_id = $order_info['order_id'];

// 			foreach ( $this->get_order_totals( $order_id ) as $total ) {
// 				$text .= $total['title'] . ': ' . html_entity_decode(
// 					$this->currency->format(
// 						$total['value'],
// 						$order_info['currency_code'],
// 						$order_info['currency_value']
// 					),
// 					ENT_NOQUOTES,
// 					'UTF-8'
// 				) . '<br>';
// 			}
// 		}

// 		return $text;
// 	}

// 	/**
// 	 * Returns newsletter subscriber's email
// 	 * @return type
// 	 */
// 	public function shortcode_subscriber_email() {
// 		$ret = '';

// 		if ( $this->adk_subscriber_email ) {
// 			$ret = $this->adk_subscriber_email;

// 		} elseif ( defined( 'PREVIEW' ) ) {
// 			$ret = 'john_smith@gmail.com';
// 		}

// 		return $ret;
// 	}

// 	/**
// 	 * Returns newsletter subscriber's name
// 	 * @return type
// 	 */
// 	public function shortcode_subscriber_name() {
// 		$ret = '';

// 		if ( $this->adk_subscriber_name ) {
// 			$ret = $this->adk_subscriber_name;

// 		} elseif ( defined( 'PREVIEW' ) ) {
// 			$ret = 'John Smith';
// 		}

// 		return $ret;
// 	}

// 	/**
// 	 * Returns newsletter name
// 	 * @return type
// 	 */
// 	public function shortcode_newsletter_name() {
// 		$ret = '';

// 		if ( $this->adk_newsletter_name ) {
// 			$ret = $this->adk_newsletter_name;

// 		} elseif ( defined( 'PREVIEW' ) ) {
// 			$ret = 'Arrival of a new product';
// 		}

// 		return $ret;
// 	}

// 	/**
// 	 * Returns confirm subscription link
// 	 * @return string
// 	 */
// 	public function shortcode_confirm_subscription_url() {
// 		$args = func_get_args();
// 		$text = empty( $atgs[1] ) ? $this->__( 'Confirm subscription' ) : $args[1];
// 		$code = uniqid();
// 		$url = $this->get_store_url() .
// 			'index.php?route=' . $this->type . '/' . $this->code . '/confirm_subscription&code=' . $code;

// 		if ( $this->adk_subscriber_email && $this->adk_newsletter_id ) {

// 			$result = $this->run_query( array(
// 				'table' => $this->newsletter_code_table,
// 				'query' => 'insert',
// 				'values' => array(
// 					'code'       => $code,
// 					'newsletter' => $this->adk_newsletter_id,
// 					'operation'  => 1,
// 					'expiration' => $this->get_sql_expiration_date( 'confirm_subscription' ),
// 					'email'      => $this->adk_subscriber_email,
// 				),
// 			) );

// 			if ( ! $result ) {
// 				trigger_error(
// 					sprintf(
// 						'Failed to add subscription confirmation code into DB, email: "%s", newsletter ID: "%s"',
// 						$this->adk_subscriber_email,
// 						$this->adk_newsletter_id
// 					)
// 				);

// 				return '';
// 			}

// 		} elseif ( ! defined( 'PREVIEW' ) ) {
// 			return '';

// 		}

// 		return sprintf(
// 			'<a href="%s" target="_blank">%s</a>',
// 			$url,
// 			$text
// 		);
// 	}

	/**
	 * Returns sample order details
	 * @return array
	 */
	public function get_sample_order() {
		if ( $this->adk_sample_order ) {
			return $this->adk_sample_order;
		}

		$data = array();
		$file = __DIR__ . '/sample_order';

		if( is_file( $file ) ) {
			$data = $this->object_to_array( json_decode( file_get_contents( $file ) ) );

		} else {
			$this->make_sample_order();
			$data = $this->object_to_array( json_decode( file_get_contents( $file ) ) );
		}

		$this->adk_sample_order = $data;

		return $data;
	}

	/**
	 * Creates data for sample order for preview purpose
	 * @param int $order_id Real order ID
	 * @return void
	 */
	public function make_sample_order( $order_id = 1 ) {
		if( defined( DIR_CATALOG ) ) {
			$this->load->model( 'checkout/order' );
			$order = $this->model_checkout_order->getOrder( $order_id );

		} else {
			$this->load->model( 'sale/order' );
			$order = $this->model_sale_order->getOrder( $order_id );
		}

		$order['order_status'] = $this->get_order_status_name( $order['order_status_id'] );

		if ( defined( 'JSON_PRETTY_PRINT' ) ) {
			file_put_contents( __DIR__ . '/sample_order', json_encode( $order, JSON_PRETTY_PRINT ) );

		} else {
			file_put_contents( __DIR__ . '/sample_order', json_encode( $order ) );
		}
	}

	/**
	 * Returns current store URL
	 * @return string
	 */
	// public function get_store_href() {
	// 	$ret = $this->config->get( 'config_ssl' );

	// 	if( is_null( $ret ) ) {
	// 		$ret = defined( 'HTTPS_CATALOG' ) ? HTTPS_CATALOG : HTTPS_SERVER;
	// 	}

	// 	return $ret;
	// }

	/**
	 * Returns URL for current store
	 * @return string
	 */
	// public function get_store_url() {
	// 	return $this->get_store_href();
	// }

	/**
	 * Adds value to one-time cache
	 * @param string $name Value name
	 * @param mixed $value Value 
	 * @return void
	 */
	// public function add_to_cache( $name, $value ) {
	// 	$adk_cache = is_array( $this->adk_cache ) ? $this->adk_cache : array();
	// 	$this->create_array_structure( $adk_cache, $name );
	// 	$pointer =& $adk_cache;

	// 	foreach( explode( '/', $name ) as $part ) {
	// 		$pointer = &$pointer[ $part ];
	// 	}

	// 	$pointer = $value;
	// 	$this->adk_cache = $adk_cache;
	// }

	// /**
	//  * Checks if value exists in one-time cache
	//  * @param string $name Value name
	//  * @return boolean
	//  */
	// public function has_in_cache( $name ) {
	// 	$adk_cache = $this->adk_cache;
	// 	$pointer = $adk_cache;

	// 	foreach( explode( '/', $name ) as $part ) {
	// 		if( ! isset( $pointer[ $part ] ) ) {
	// 			return false;
	// 		}

	// 		$pointer =& $pointer[ $part ];
	// 	}

	// 	return true;
	// }

	// /**
	//  * Fetches value form one-time cache
	//  * @param string $name Value name
	//  * @return mixed
	//  */
	// public function get_from_cache( $name ) {
	// 	$adk_cache = $this->adk_cache;
	// 	$pointer = $adk_cache;

	// 	foreach( explode( '/', $name ) as $part ) {
	// 		if( ! isset( $pointer[ $part ] ) ) {
	// 			return null;
	// 		}

	// 		$pointer = &$pointer[ $part ];
	// 	}

	// 	return $pointer;
	// }

	/**
	 * Returns products list for specific order
	 * @param int $order_id Order ID
	 * @return array
	 */
	// public function get_order_products( $order_id ) {
	// 	$order_info = $this->get_order_info( $order_id );

	// 	if( isset( $order_info['language_id'] ) ) {
	// 		$language_id = $order_info['language_id'];

	// 	} else {
	// 		$language_id = $this->config->get( 'config_language_id' );
	// 	}

	// 	if( ! $this->has_in_cache( 'order_products/' . $order_id ) ) {
	// 		$query = $this->db->query(
	// 			"SELECT *, `op`.`quantity` FROM `" . DB_PREFIX . "order_product` op
	// 			LEFT JOIN `" . DB_PREFIX . "product_description` pd
	// 				USING(`product_id`)
	// 			LEFT JOIN `" . DB_PREFIX . "product` p
	// 				USING(`product_id`)
	// 			WHERE `op`.`order_id` = " . (int)$order_id . "
	// 				AND `pd`.`language_id` = " . (int)$language_id
	// 		);
			
	// 		$this->add_to_cache( 'order_products/' . $order_id, $query->rows );
	// 		return $query->rows;
	// 	}

	// 	return $this->get_from_cache( 'order_products/' . $order_id );
	// }

	/**
	 * Returns products list
	 * @param int $order_id Product ID
	 * @return array
	 */
	// public function get_products_by_id( $product_id ) {

	// 	if ( ! $product_id ) {
	// 		return array();
	// 	}

	// 	$query = $this->db->query( "SELECT * FROM `" . DB_PREFIX . "product` p WHERE `p`.`product_id` IN (" . implode( ')(', (array)$product_id ) . ")" );

	// 	return $query->rows;
	// }

	/**
	 * Returns vouchers pertain to an order
	 * @param int $order_id Order ID
	 * @return string
	 */
	// public function get_order_vouchers( $order_id ) {

	// 	if( ! $this->has_in_cache( 'order_vouchers/' . $order_id ) ) {
	// 		$query = $this->db->query( "SELECT * FROM `" . DB_PREFIX . "order_voucher` WHERE `order_id` = " . (int)$order_id  );
			
	// 		$this->add_to_cache( 'order_vouchers/' . $order_id, $query->rows );
	// 		return $query->rows;
	// 	}

	// 	return $this->get_from_cache( 'order_vouchers/' . $order_id );
	// }

	/**
	 * Returns an order totals list
	 * @param int $order_id Order ID
	 * @return array
	 */
	// public function get_order_totals( $order_id ) {

	// 	if( ! $this->has_in_cache( 'order_totals/' . $order_id ) ) {
	// 		$query = $this->db->query( "SELECT * FROM `" . DB_PREFIX . "order_total` WHERE `order_id` = " . (int)$order_id . " ORDER BY `sort_order` ASC" );
			
	// 		$this->add_to_cache( 'order_totals/' . $order_id, $query->rows );
	// 		return $query->rows;
	// 	}

	// 	return $this->get_from_cache( 'order_totals/' . $order_id );
	// }

	/**
	 * Returns order status name by its ID
	 * @param int $order_status_id Order status ID
	 * @param string|null $language_id Language code, optional
	 * @return string
	 */
	// public function get_order_status_name( $order_status_id, $language_id = null ) {

	// 	if( is_null( $language_id ) ) {
	// 		$language_id = $this->config->get( 'config_language_id' );
	// 	}

	// 	if( ! $this->has_in_cache( 'order_statuses/' . $language_id . '/' . $order_status_id ) ) {
	// 		$query = $this->db->query( "SELECT * FROM `" . DB_PREFIX . "order_status` WHERE `language_id` = " . (int)$language_id );

	// 		$ret = array();
	// 		foreach( $query->rows as $row ) {
	// 			$ret[ $row['order_status_id'] ] = $row['name'];
	// 		}

	// 		$this->add_to_cache( 'order_statuses/' . $language_id, $ret );
			
	// 		if( isset( $ret[ $order_status_id ] ) ) {
	// 			return $ret[ $order_status_id ];
	// 		}

	// 		return '';
	// 	}

	// 	return $this->get_from_cache( 'order_statuses/' . $language_id . '/' . $order_status_id );
	// }

	/**
	 * Returns download-able products for specific order, if present
	 * @param int $order_id Order ID
	 * @return array
	 */
	// public function get_order_downloaded_products( $order_id ) {

	// 	if( ! $this->has_in_cache( 'downloaded_products/' . $order_id ) ) {
	// 		$query = $this->db->query( "SELECT * FROM `" . DB_PREFIX . "order_product` op LEFT JOIN `" . DB_PREFIX . "product_to_download` pd USING(`product_id`) WHERE `op`.`order_id` = " . (int)$order_id . " AND `pd`.`download_id` IS NOT NULL" );
			
	// 		$this->add_to_cache( 'downloaded_products/' . $order_id, $query->rows );
	// 		return $query->rows;
	// 	}

	// 	return $this->get_from_cache( 'downloaded_products/' . $order_id );
	// }

	/**
	 * Returns order information by its ID
	 * @param int $order_id Order ID
	 * @return array
	 */
	// public function get_order_info( $order_id ) {

	// 	if( ! $this->has_in_cache( 'orders/' . $order_id ) ) {
	// 		$query = $this->db->query( "SELECT * FROM `" . DB_PREFIX . "order` WHERE `order_id` = " . (int)$order_id );
			
	// 		$this->add_to_cache( 'orders/' . $order_id, $query->row );
	// 		return $query->row;
	// 	}

	// 	return $this->get_from_cache( 'orders/' . $order_id );
	// }

	/**
	 * Returns customer group info by its ID
	 * @param int $group_id Customer group ID
	 * @return array
	 */
	// public function get_customer_group_info( $group_id ) {

	// 	if( ! $this->has_in_cache( 'customer_groups/' . $group_id ) ) {
	// 		$query = $this->db->query( "SELECT * FROM `" . DB_PREFIX . "customer_group` cg LEFT JOIN `" . DB_PREFIX . "customer_group_description` cgd USING(`customer_group_id`) WHERE `cg`.`customer_group_id` = " . (int)$group_id . " AND `cgd`.`language_id` = " . (int)$this->config->get( 'config_language_id' ) );
			
	// 		$this->add_to_cache( 'customer_groups/' . $group_id, $query->row );
	// 		return $query->row;
	// 	}

	// 	return $this->get_from_cache( 'customer_groups/' . $group_id );
	// }

	/**
	 * Returns product details
	 * @param int $product_id Product iD
	 * @return array
	 */
	// public function get_product_info( $product_id ) {

	// 	if( !  $this->has_in_cache( 'products/' . $product_id ) ) {
	// 		$query = $this->db->query( "SELECT * FROM `" . DB_PREFIX . "product` p LEFT JOIN `" . DB_PREFIX . "product_description` pd USING(`product_id`) WHERE `p`.`product_id` = " . (int)$product_id . " AND `pd`.`language_id` = " . (int)$this->config->get( 'config_language_id' ) );

	// 		$this->add_to_cache( 'products/' . $product_id, $query->row );
	// 		return $query->row;
	// 	}

	// 	return $this->get_from_cache( 'products/' . $product_id );
	// }

	/**
	 * Returns region information
	 * @param int $region_id Region ID
	 * @return array
	 */
	// public function get_region_info( $region_id ) {
		
	// 	if( ! $this->has_in_cache( 'regions/' . $region_id ) ) {
	// 		$query = $this->db->query( "SELECT `c`.`country_id`, `c`.`name` as country_name, `c`.`iso_code_2` as country_iso, `z`.`name` as zone_name, `z`.`code` as zone_code FROM `" .DB_PREFIX . "zone` z LEFT JOIN `" . DB_PREFIX . "country` c USING(`country_id`) WHERE `z`.`zone_id` = " . (int)$region_id );

	// 		$this->add_to_cache( 'regions/' . $region_id, $query->row );
	// 		return $query->row;
	// 	}

	// 	return $this->get_from_cache( 'regions/' . $region_id );
	// }

	/**
	 * Returns voucher information
	 * @param int $voucher_id Voucher ID
	 * @return array
	 */
	// public function get_voucher( $voucher_id ) {
 
	// 	if( ! $this->has_in_cache( 'vouchers/' . $voucher_id ) ) {
	// 		$query = $this->db->query( "SELECT * FROM `" . DB_PREFIX . "voucher` v LEFT JOIN `" . DB_PREFIX . "voucher_theme` USING(`voucher_theme_id`) WHERE `voucher_id` = " . (int)$voucher_id );

	// 		$this->add_to_cache( 'vouchers/' . $voucher_id, $query->row );
	// 		return $query->row;
	// 	}

	// 	return $this->get_from_cache( 'vouchers/' . $voucher_id );
	// }

	/**
	 * Returns a return data by its ID
	 * @param int $return_id Return ID
	 * @return array
	 */
	public function get_return_data( $return_id ) {
		if( ! $this->has_in_cache( 'return' ) ) {

			$return_query = $this->db->query(
				"SELECT *, `rs`.`name` AS `status` FROM `" . DB_PREFIX . "return` `r`
				LEFT JOIN `" . DB_PREFIX . "return_status` `rs`
					ON (`r`.`return_status_id` = `rs`.`return_status_id`)
				WHERE `r`.`return_id` = '" . (int)$return_id . "'
					AND `rs`.`language_id` = '" . (int)$this->config->get('config_language_id') . "'"
			);

			$this->add_to_cache( 'return', $return_query->row );
			return $return_query->row;
		}

		return $this->get_from_cache( 'return' );
	}

	/**
	 * Returns target customer, if possible
	 * Logic is following
	 *   1. If front-end and customer is logged in - use it
	 *   2. If guest session in opened - use guest customer
	 *   3. Use customer from "To" of original email message 
	 * @return array|null
	 */
	public function get_mail_customer() {
		$customer = null;

		if( $this->customer && $this->customer->isLogged() ) {
			$customer = $this->get_customer_by_email( $this->customer->getEmail() );

		} elseif ( isset( $this->session->data['guest'] ) ) {
			$customer = $this->session->data['guest'];

		} elseif( ! is_null( $this->modified_mail ) ) {
			$customer = $this->get_customer_by_email( $this->modified_mail->to );
		}

		return $customer;
	}

	/**
	 * Returns customer by its email
	 * @param string $email Email address
	 * @return array
	 */
	// public function get_customer_by_email( $email ) {
	// 	$query = $this->db->query( "SELECT DISTINCT * FROM `" . DB_PREFIX . "customer` WHERE LCASE(`email`) = '" . $this->db->escape( utf8_strtolower( $email ) ) . "'" );

	// 	if ( $query->num_rows === 0 && isset( $this->caller_args[0] ) ) {
	// 		$query = $this->db->query(
	// 			"SELECT `firstname`, `lastname`, `email` FROM `" . DB_PREFIX . "order`
	// 			WHERE `order_id` = " . (int)$this->caller_args[0]
	// 		);
	// 	}

	// 	return $query->row;
	// }

	/**
	 * Returns affiliate object by email address which fetched from field "To" of original email
	 * @return array|null
	 */
	public function get_mail_affiliate() {
		$affiliate = null;

		if( ! is_null( $this->modified_mail ) ) {
			$query = $this->db->query(
				"SELECT * FROM `" . DB_PREFIX . "affiliate`
				WHERE `email` = '" . $this->db->escape( $this->modified_mail->to ) . "'"
			);
			$affiliate = $query->row;
		}

		return $affiliate;
	}

	/**
	 * Returns customer ID for customer, for whom an unsubscribe code was created
	 * @param string $code Unsubscribe code 
	 * @return int
	 */
	public function get_customer_by_unsubscription_code( $code ) {
		$this->db->query(
			"DELETE FROM `" . DB_PREFIX . $this->unsubscribers_table . "` WHERE `expiration` < NOW()"
		);

		$query = $this->db->query(
			"SELECT `customer_id` FROM `" . DB_PREFIX . $this->unsubscribers_table . "`
			WHERE `code` = '" . $this->db->escape( $code ) . "'"
		);

		if( $query->num_rows > 0 ) {
			return $query->row['customer_id'];
		} 

		return -1;
	}

	/**
	 * Unsubscribes customer from OpenCart newsletter subscription
	 * @param array $data Data
	 * @return boolean Operation result
	 */
	public function unsubscribe( $data ) {

		// OpenCart newsletter
		if ( 0 == $data['newsletter'] ) {
			$result = $this->q( array(
				'table' => 'customer',
				'query' => 'update',
				'set'   => array(
					'newsletter' => 0,
				),
				'where' => array(
					'field'     => 'customer_id',
					'operation' => '=',
					'value'     => $data['customer_id'],
				),
			) );

		} else {
			$result = $this->q( array(
				'table' => $this->newsletter_subscribers_table,
				'query' => 'update',
				'set'   => array(
					'status'           => self::SUBSCRIBER_STATUS_CANCELLED,
					'date_unsubscribe' => date( 'c' ),
				),
				'where' => array(
					array(
						'field'     => 'email',
						'operation' => '=',
						'value'     => $data['email'],
					),
					array(
						'field'     => 'newsletter',
						'operation' => '=',
						'value'     => $data['newsletter']
					),
				),
			) );
		}
		
		return $result;
	}

	/**
	 * Returns restore admin password code
	 * @param string $email Admin email address
	 * @return string
	 */
	public function get_admin_restore_password_code( $email ) {
		$this->load->helper( 'utf8' );
		$query = $this->db->query(
			"SELECT `code` FROM `" . DB_PREFIX . "user`
			WHERE LCASE(email) = '" . $this->db->escape( utf8_strtolower( $email ) ) . "'"
		);

		return $query->row['code'];
	}

	// /**
	//  * Auto detect product(s) pertain to email 
	//  * @param int|null $default_product_id Bounce product ID
	//  * @return int|array
	//  */
	// public function get_shortcode_product_id( $default_product_id = null ) {
	// 	$ret = $default_product_id;

	// 	// Newsletter to a specific product
	// 	if( isset( $this->request->post['product'] ) ) {
	// 		$ret = $this->request->post['product'];

	// 	// New order case
	// 	} elseif ( $this->has_in_cache( 'old_order_data') ) {
	// 		$order = $this->get_from_cache( 'old_order_data' );

	// 		$ids = $this->run_query( array(
	// 			'table'  => 'order_product',
	// 			'query'  => 'select',
	// 			'fields' => 'product_id',
	// 			'where'  => array(
	// 				'field'     => 'order_id',
	// 				'operation' => '=',
	// 				'value'     => $order['order_id']
	// 			),
	// 		) );

	// 		$r = array();
	// 		foreach ( $ids as $id ) {
	// 			$r[] = $id['product_id'];
	// 		}

	// 		if ( $r ) {
	// 			$ret = $r;
	// 		}

	// 	// Return case
	// 	} elseif ( $return_id = $this->shortcode_return_id() ) {
	// 		$return = $this->get_return_data( $return_id );
	// 		if( isset( $return['product_id'] ) ) {
	// 			$ret = $return['product_id'];
	// 		}
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Auto-detects customer group ID for customer pertain to email
	//  * @return int
	//  */
	// public function get_shortcode_customer_group_id() {
	// 	$ret = 1;
	// 	$customer = $this->get_mail_customer();

	// 	if( $customer && isset( $customer['customer_group_id'] ) ) {
	// 		$ret =  $customer['customer_group_id'];
	// 	} 

	// 	return $ret;
	// }

	// /**
	//  * Returns vitrine shortcode specific products
	//  * @param array $shortcode Vitrine shortcode
	//  * @return array
	//  */
	// public function get_vitrine_products( $shortcode ) {

	// 	$type = isset( $shortcode['data']['type'] ) ? $shortcode['data']['type'] : 'bestseller';
	// 	$limit = isset( $shortcode['data']['number'] ) ? $shortcode['data']['number'] : 3;

	// 	if( ! empty( $shortcode['data']['related'] ) || 'related' === $type ) {

	// 		$default_product_id = isset( $shortcode['data']['product']['default'] ) ?
	// 			$shortcode['data']['product']['default'] : null;

	// 		$product_id = $this->get_shortcode_product_id( $default_product_id );

	// 	} else {
	// 		$product_id = null;
	// 	}

	// 	switch( $type ) {
	// 		case 'bestseller' :
	// 			$products = $this->get_products( array(
	// 				'limit'             => $limit,
	// 				'sort'              => 'sold',
	// 				'order'             => 'DESC',
	// 				'customer_group_id' => $this->get_shortcode_customer_group_id(),
	// 				'product_id'        => $product_id,
	// 			) );
	// 			break;
	// 		case 'latest' :
	// 			$products = $this->get_products( array(
	// 				'limit'             => $limit,
	// 				'sort'              => 'added',
	// 				'order'             => 'DESC',
	// 				'customer_group_id' => $this->get_shortcode_customer_group_id(),
	// 				'product_id'        => $product_id,
	// 			) );
	// 			break;
	// 		case 'popular' :
	// 			$products = $this->get_products( array(
	// 				'limit'             => $limit,
	// 				'sort'              => 'viewed',
	// 				'order'             => 'DESC',
	// 				'customer_group_id' => $this->get_shortcode_customer_group_id(),
	// 				'product_id'        => $product_id,
	// 			) );
	// 			break;
	// 		case 'special' :
	// 			$products = $this->get_products( array(
	// 				'sort'              => 'viewed',
	// 				'order'             => 'DESC',
	// 				'customer_group_id' => $this->get_shortcode_customer_group_id(),
	// 				'product_id'        => $product_id,
	// 			) );

	// 			$p = array();
	// 			foreach( $products as $product ) {
	// 				if( isset( $product['special'] ) ) {
	// 					$p[] = $product;
	// 					if( count( $p ) >= $limit ) {
	// 						break;
	// 					} 
	// 				}
	// 			}

	// 			$products = $p;
	// 			break;
	// 		case 'related' :
	// 			$products = $this->get_products( array(
	// 				'limit'             => $limit,
	// 				'sort'              => 'viewed',
	// 				'order'             => 'DESC',
	// 				'customer_group_id' => $this->get_shortcode_customer_group_id(),
	// 				'product_id'        => $product_id,
	// 				'related'           => true,
	// 			) );
	// 			break;
	// 		case 'arbitrary' :
	// 			$arbitrary = isset( $shortcode['data']['product']['arbitrary'] ) ?
	// 				$shortcode['data']['product']['arbitrary'] : array();

	// 			$products = $this->get_products( array(
	// 				'limit'             => $limit,
	// 				'sort'              => 'viewed',
	// 				'order'             => 'DESC',
	// 				'customer_group_id' => $this->get_shortcode_customer_group_id(),
	// 				'arbitrary'         => $arbitrary,
	// 			) );
	// 			break;
	// 		default:
	// 			$products = $this->get_products();
	// 			break;
	// 	}

	// 	return $products;

	// }

	/**
	 * Returns filtered products list for vitrine shortcode
	 * @param array $data FIlter data, optional
	 * @return array
	 */
	public function get_products( $data = array() ) {
		$customer_group_id = isset( $data['customer_group_id'] ) ? (int)$data['customer_group_id'] : -1;

		$q = "SELECT
			`p`.`product_id` as 'product_id',
			`p`.`image` as image,
			`p`.`price` as price,
			`p`.`viewed` as viewed,
			(SELECT SUM(`quantity`) FROM `" . DB_PREFIX . "order_product` WHERE `product_id` = `p`.`product_id`) as sold,
			(SELECT `price` FROM `" . DB_PREFIX . "product_special`
				WHERE `product_id` = `p`.`product_id`
					AND `customer_group_id` = " . $customer_group_id . "
					AND ( `date_start` = '0000-00-00' OR `date_start` < NOW() )
					AND ( `date_end` = '0000-00-00' OR `date_end` > NOW() )
				ORDER BY `priority` DESC LIMIT 1 ) as special,
			`pd`.`name`  as 'name',
			`p`.`date_added` as added
			FROM `" . DB_PREFIX . "product` p
				LEFT JOIN `" . DB_PREFIX . "product_description` pd
					ON( `p`.`product_id` = `pd`.`product_id`)
				LEFT JOIN `" . DB_PREFIX . "product_to_category` pc
					ON( `p`.`product_id` =  `pc`.`product_id`)
				LEFT JOIN `" . DB_PREFIX . "category_description` cd
					ON(`pc`.`category_id` = `cd`.`category_id`)
			WHERE `pd`.`language_id` = " . (int)$this->config->get( 'config_language_id' ) . "
				AND `cd`.`language_id` = " . (int)$this->config->get( 'config_language_id' );

		if( isset( $data['arbitrary'] ) ) {
			$q .= " AND `p`.`product_id` IN (" . implode( ',', (array)$data['arbitrary'] ) . ")";

		// If we have related product - select only those products, which are in the same category or have the same manufacturer
		} elseif( ! empty( $data['product_id'] ) ) {
			if( ! empty( $data['related'] ) ) {
				$q .= " AND `p`.`product_id` IN ( SELECT `related_id` FROM `" . DB_PREFIX . "product_related` WHERE `product_id` IN (" . implode( ',', (array)$data['product_id'] ) . ") )";

			} else {
				$q .= " AND `pc`.`category_id` IN ( SELECT `category_id` FROM `" . DB_PREFIX . "product_to_category` WHERE `product_id` IN(" . implode( ',', (array)$data['product_id'] ) . ") OR `p`.`manufacturer_id` = ( SELECT manufacturer_id FROM `" . DB_PREFIX . "product` WHERE product_id IN(" . implode( ',', (array)$data['product_id'] ) . " ) ) )";
			}
		}

		$q .= " GROUP BY `p`.`product_id`";

		$sort_data = array(
			'pd.name',
			'p.model',
			'p.price',
			'p.quantity',
			'p.status',
			'p.sort_order',
			'added',
			'sold',
			'viewed',
			'p.manufacturer',
		);

		if ( isset($data['sort'] ) && in_array( $data['sort'], $sort_data ) ) {
			$q .= " ORDER BY " . $data['sort'];

		} else {
			$q .= " ORDER BY `pd`.`name`";
		}

		if ( isset( $data['order'] ) && ( $data['order'] == 'DESC') ) {
			$q .= " DESC";

		} else {
			$q .= " ASC";
		}

		if ( isset($data['start'] ) || isset( $data['limit']) ) {
			if ( ! isset( $data['start'] ) || $data['start'] < 0 ) {
				$data['start'] = 0;
			}

			if ( ! isset( $data['limit'] ) || $data['limit'] < 1 ) {
				$data['limit'] = 20;
			}

			$q .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query( $q );

		return $query->rows;
	}

	/**
	 * Returns template data for marketing page
	 * @since 1.1.0
	 * @return array
	 */
	public function marketing_data() {
		$this->document->addScript( HTTPS_SERVER . 'view/javascript/advertikon/jquery-ui.min.js' );
		$this->document->addScript( HTTPS_SERVER . 'view/javascript/advertikon/iris.min.js' );
		$this->document->addScript( HTTPS_SERVER . 'view/javascript/advertikon/summernote/summernote.min.js' );
		$this->document->addScript( HTTPS_SERVER . 'view/javascript/advertikon/advertikon.js' );
		$this->document->addScript( HTTPS_SERVER . 'view/javascript/advertikon/mail/adk_mail_common.js' );

		$this->document->addStyle( HTTPS_SERVER . 'view/stylesheet/advertikon/jquery-ui.min.css' );
		$this->document->addStyle( HTTPS_SERVER . 'view/stylesheet/advertikon/jquery-ui.theme.min.css' );
		$this->document->addStyle( HTTPS_SERVER . 'view/stylesheet/advertikon/summernote/summernote.css' );
		$this->document->addStyle( HTTPS_SERVER . 'view/stylesheet/advertikon/advertikon.css' );
		$this->document->addStyle( HTTPS_SERVER . 'view/stylesheet/advertikon/mail/adk_mail.css' );

		$data = array();

		$data['header'] = $this->load->controller( 'common/header' );

		$data['adkLocale'] = json_encode( array(

			'elfinderAttachmentAction' => $this->url->link(
				$this->type . '/' . $this->code . '/attachments_connector',
				'token=' . $this->session->data['token'],
				'SSL'
			),
			'elfinderAttachmentHref' => $this->url->link(
				$this->type . '/' . $this->code . '/attachment_href',
				'token=' . $this->session->data['token'],
				'SSL'
			),
			'fileBrowser'  => $this->__( 'File browser' ),
			'name'         => $this->__( 'Name' ),
			'size'         => $this->__( 'Size' ),
			'embed'        => $this->__( 'Embed attachment' ),
			'del'          => $this->__( 'Delete' ),
			'embedTooltip' => $this->render_popover( $this->__( 'Attachments just appear as files that can be saved to the Desktop if desired. You can make attachment appear inline where possible by mark attachment as "Embed".' ) ),
			'shortcodes'   => $this->get_shortcodes_hint(),
			'nextRun'      => $this->__( 'Next run' ),
			'networkError' => $this->__( 'Network error' ),
			'scriptError'  => $this->__( 'Script error' ),
 		) );

		$data['save_template'] = $this->renderer()->render_form_group( array(
			'label'     => $this->__( 'Save template' ),
			'label_for' => 'available-profiles',
			'cols'      => array( 'col-sm-2', 'col-sm-10' ),
			'element'   => $this->r( array(
				'type'    => 'inputgroup',
				'element' => array(
					'type'        => 'text',
					'placeholder' => $this->__( 'Template name' ),
					'id'          => 'adk-mail-template-name',
					'class'       => 'form-control mail-content',
					'custom_data' => 'data-type="name"',
				),
				'addon_before' => array(
					'type'        => 'button',
					'icon'        => 'fa-save',
					'id'          => 'adk-mail-template-save',
					'title'       => $this->__( 'Save template' ),
					'button_type' => 'primary',
					'custom_data' => 'data-url="' . $this->url->link(
						$this->type . '/' . $this->code . '/save_template_mail',
						'token=' . $this->session->data['token'],
						'SSL'
					) . '"',
				),
				'addon_after' => array(
					'type'        => 'button',
					'icon'        => 'fa-close',
					'id'          => 'adk-mail-template-delete',
					'title'       => $this->__( 'Delete template' ),
					'button_type' => 'danger',
					'custom_data' => 'data-url="' . $this->url->link(
						$this->type . '/' . $this->code . '/delete_template_mail',
						'token=' . $this->session->data['token'],
						'SSL'
					) . '"',
				),
			) )
		) );

		$list = array( '-1' => $this->__( 'Select template' ) );
		foreach( $this->get_template_mail_names() as $id => $name ) {
			$list[ $id ] = $name;
		}

		$data['select_template'] = $this->renderer()->render_form_group( array(
			'label'     => $this->__( 'Load template' ),
			'label_for' => 'available-profiles',
			'cols'      => array( 'col-sm-2', 'col-sm-10' ),
			'element'   => $this->r( array(
				'type'        => 'select', 
				'value'       => $list,
				'class'       => 'form-control mail-content',
				'id'          => 'adk-mail-template-list',
				'custom_data' => 'data-url="' . $this->url->link(
						$this->type . '/' . $this->code . '/get_template_list',
						'token=' . $this->session->data['token'],
						'SSL'
					) . '" data-type="template_id"',
			) )
		) );

		$data['name_needed'] = $this->__( 'Template name is mandatory' );
		$data['newsletter_url' ] = $this->url->link(
			$this->type . '/' . $this->code . '/newsletter',
			'token=' . $this->session->data['token'],
			'SSL'
		);

		// Cc
		$data['cc'] = $this->renderer()->render_form_group( array(
			'label'     => $this->__( 'Carbon copy' ),
			'label_for' => '',
			'cols'      => array( 'col-sm-2', 'col-sm-10' ),
			'element'   => $this->r( array(
				'type'         => 'inputgroup',
				'addon_before' => 'Cc',
				'element'      => array(
					'type'        => 'text',
					'class'       => 'form-control mail-content',
					'value'       => '',
					'custom_data' => 'data-type="cc"',
					'name'        => 'cc',
				)
			) ),
			'description' => $this->__( 'Comma separated list of recipients' ),
		) );

		// Bcc
		$data['bcc'] = $this->renderer()->render_form_group( array(
			'label'     => $this->__( 'Blind carbon copy' ),
			'label_for' => '',
			'cols'      => array( 'col-sm-2', 'col-sm-10' ),
			'element'   => $this->r( array(
				'type'         => 'inputgroup',
				'addon_before' => 'Bcc',
				'element'      => array(
					'type'        => 'text',
					'class'       => 'form-control mail-content',
					'value'       => '',
					'custom_data' => 'data-type="bcc"',
					'name'        => 'bcc',
				),
			) ),
			'description' => $this->__( 'Comma separated list of recipients' ),
		) );

		// Return-to
		$data['return'] = $this->renderer()->render_form_group( array(
			'label'     => $this->__( 'Return path' ),
			'label_for' => '',
			'cols'      => array( 'col-sm-2', 'col-sm-10' ),
			'element'   => $this->r( array(
				'type'         => 'inputgroup',
				'addon_before' => '<i class="fa fa-at"></i>',
				'element'      => array(
					'type'        => 'text',
					'class'       => 'form-control mail-content',
					'value'       => '',
					'custom_data' => 'data-type="return"',
					'name'        => 'return',
				),
			) ),
			'description' => $this->__( 'Specifies where bounce notifications should be sent' ),
		) );

		$data['helper'] = $this;

		$key = uniqid();

		// Add attachment button
		$data['attachment_button'] = $this->renderer()->render_form_group( array(
			'label'     => $this->__( 'Attachment' ),
			'label_for' => '',
			'cols'      => array( 'col-sm-2', 'col-sm-10' ),
			'element'   => $this->r( array(
				'type'        => 'button',
				'css'         => 'font-weight: bold',
				'icon'        => 'fa-plus',
				'text_after'  => $this->__( 'Add' ),
				'button_type' => 'primary',
				'class'       => 'attachment',
				'custom_data' => 'data-key="' . $key . '"',
			) ),
		) );

		// Attachment
		$data['attachment'] = $this->renderer()->render_form_group( array(
			'label'     => $this->__( 'Attachments list' ),
			'label_for' => '',
			'cols'      => array( 'col-sm-2', 'col-sm-10' ),
			'css'       => 'display: none',
			'element'   => $this->r( array(
				'type'        => 'hidden',
				'value'       => '',
				'custom_data' => 'data-type="attachment" data-key="' . $key . '" data-type="attachment"',
				'class'       => 'mail-content attachment-field',
				'name'        => 'attachment',
				'id'          => 'attachment',
			) ),
		) );

		$newsletter = array(
			'newsletter'     => $this->__( 'OpenCart newsletter' ),
			'customer_all'   => $this->__( 'All customers' ),
			'customer_group' => $this->__( 'Customer group' ),
			'customer'       => $this->__( 'Customer' ),
			'affiliate_all'  => $this->__( 'All affiliates' ),
			'affiliate'      => $this->__( 'Affiliate' ),
			'product'        => $this->__( 'Product' ),
		);

		$query_data = array(
			'where' => array(
				array(
					'operation' => '=',
					'field'     => 'status',
					'value'     => self::NEWSLETTER_STATUS_ACTIVE,
				),
			),
		);

		// To 
		$data['newsletter_select'] = $this->r( array(
			'type'        => 'select',
			'id'          => 'input-to',
			'class'       => 'form-control',
			'value'       => array_replace( $newsletter, $this->get_newsletter_for_select( $query_data ) ),
			'name'        => 'to',
			'custom_data' => 'data-type="to"'
		) );

		$data['send_button'] = $this->r( array(
			'type'        => 'button',
			'icon'        => 'fa-envelope',
			'text_before' => $this->__( 'Send' ),
			'button_type' => 'primary',
			'id'          => 'button-send',
			'title'       => $this->__( 'Send newsletter' ),
		) );

		$data['affiliate_autocomplete_url'] = $this->url->link(
			$this->type . '/' . $this->code . '/affiliate_autocomplete',
			'token=' . $this->session->data['token'] . '&filter_name=',
			'SSL'
		);

		return $data;
	}

	/**
	 * Returns list of all the newsletters
	 * @return array
	 */
	public function get_newsletter_list( $data = array() ) {
		$ret = array();

		if ( ! $data && $this->newsletter_list ) {
			return $this->newsletter_list;
		}

		if ( isset( $data['where'] ) ) {
			foreach( $data['where'] as &$w ) {
				if ( is_array( $w ) ) {
					if ( 'date_added' === $w['field'] ) {
						$w['field'] = 'l.date_added';
					}

				} elseif ( 'date_aded' === $w ) {
					$w = 'l.date_added';
				}
			}
		}

		$q = "SELECT `l`.*, `w`.`name` as `widget_name`, `l`.`id` as `newsletter_id`,
				(SELECT COUNT(*)
				FROM  `" . DB_PREFIX . "adk_mail_newsletter_subscribers`
				WHERE `newsletter` = `newsletter_id` AND `status` = '" . self::NEWSLETTER_STATUS_ACTIVE . "') as 'active',
				(SELECT COUNT(*)
				FROM  `" . DB_PREFIX . "adk_mail_newsletter_subscribers`
				WHERE `newsletter` = `newsletter_id` AND `status` <> '" . self::NEWSLETTER_STATUS_ACTIVE . "') as 'inactive'
			FROM  `" . DB_PREFIX . "adk_mail_newsletter_list` as `l`
			LEFT JOIN `" . DB_PREFIX . "adk_mail_newsletter_widget` `w`
				ON (`l`.`widget` = `w`.`id`)";

		if ( isset( $data['start'] ) || isset( $data['limit'] ) ) {
			$l = ' LIMIT';

			if ( empty( $data['start'] ) ) {
				$l .= ' 0,';

			} else {
				$l .= ' ' . $data['start'] . ',';
			}

			if ( empty( $data['limit'] ) ) {
				$l .= ' 20';

			} else {
				$l .= ' ' . $data['limit'];
			}

			$q .= $l;
		}

		$newsletter = new DB_Result( $this->db->query( $q )->rows );

		if ( !$data ) {
			$this->newsletter_list = $newsletter;
		}

		return $newsletter;
	}

	/**
	 * Returns list of newsletters to use with select element
	 * @param array $data Query options
	 * @return array
	 */
	public  function get_newsletter_for_select( $data = array()) {
		$ret = array();

		foreach( $this->get_newsletter_list( $data ) as $n ) {
			$ret[ $n['id'] ] = $n['name'];
		}

		return $ret;
	}

	/**
	 * Returns newsletter template
	 * @param int $id Newsletter template ID
	 * @return array
	 */
	public function get_template_mail( $id ) {
		$query = $this->db->query( "SELECT * FROM `" . DB_PREFIX . $this->template_mail_table . "` WHERE `id` = " . (int)$id );

		return $query->row;
	}

	/**
	 * Returns list of available newsletter templates
	 * @return array
	 */
	public function get_template_mail_names() {
		$ret = array();

		if ( is_dir( $this->newsletter_template ) ) {
			foreach ( scandir( $this->newsletter_template ) as $file ) {
				if ( '.' !== substr( $file, 0, 1 ) && is_file( $this->newsletter_template . $file ) ) {
					$ret[ $file ] = trim(

						// First 100 bytes is template's name
						file_get_contents( $this->newsletter_template . $file, null, null, 0, 100 ) 
					);
				}
			}
		}

		// Backward compatibility
		$query = $this->q( array(
			'table' => $this->template_mail_table,
			'query' => 'select',
			'fields' => array( 'id', 'name', ),
		) );

		if ( $query ) {
			foreach( $query as $row ) {
				$ret[ $row['id'] ] = $row['name'];
			}
		}

		return $ret;
	}

	/**
	 * Parses string with conditional tags and hides contents of tags which evaluate to false
	 * @since 1.1.0 - changed logic
	 * @param string $str Target string 
	 * @return string
	 */
	// public function conditional_print( $str ) {
	// 	$offset = 0;
	// 	$debug = false;

	// 	if( $debug ) {
	// 		$str = '{if_no_products_sku_all(32,2,1,3)}text{/if_no_products_sku_all}';
	// 	}

	// 	if( ! $this->parse_conditional_string( $str ) ) {
	// 		return $str;
	// 	}

	// 	while( ( $close_tag_start = strpos( $str, '{/if_', $offset ) ) !== false ) {

	// 		$close_tag_end = strpos( $str, '}', $close_tag_start );
	// 		$offset = $close_tag_start;
	// 		$tag = substr( $str, $close_tag_start + 2, $close_tag_end - $close_tag_start - 2 );

	// 		if ( $debug ) {
	// 			console_log( 'Tag: ' . $tag );
	// 			console_log( 'Before:' );
	// 			console_log( 'Offset: ' . $offset );
	// 			console_log( $str );
	// 			console_log( str_repeat( '_', $offset ) . '|' );
	// 		}

	// 		// Shortcode exists
	// 		$shortcode_data = $this->get_shortcode_data( $tag );

	// 		if( ! $shortcode_data ) {
	// 			trigger_error( sprintf( 'Missing data for conditional shortcode %s', $tag ) );
	// 			$offset++;
	// 			continue;
	// 		}

	// 		// Shortcode has callback
	// 		if( ! isset( $shortcode_data['callback'] ) ||
	// 				! is_callable( array( $this, $shortcode_data['callback'] ) ) ) {
	// 			trigger_error( sprintf( 'Conditional shortcode %s has no callback function', $tag ) );
	// 			$offset++;
	// 			continue;
	// 		}

	// 		if ( $debug ) {
	// 			console_log( 'Search opening tag in: ' . substr( $str, 0, $close_tag_start ) );
	// 			console_log( 'Searching for: ' . '{' . $tag );
	// 		}

	// 		$open_tag_start = strrpos( substr( $str, 0, $close_tag_start ), '{' . $tag );
	// 		$open_tag_end = strpos( $str, '}', $open_tag_start );

	// 		if ( $debug ) {
	// 			console_log( 'Opening tag start position: ' . $open_tag_start );
	// 			console_log( $str );
	// 			console_log( str_repeat( '_', $open_tag_start ) . '|' );
	// 			console_log( 'Opening tag end position: ' . $open_tag_end );
	// 			console_log( $str );
	// 			console_log( str_repeat( '_', $open_tag_end ) . '|' );
	// 			$open_tag = substr( $str, $open_tag_start + 1, $open_tag_end - $open_tag_start - 1 );
	// 			console_log( 'Opening tag: ' . $open_tag );
	// 		}

	// 		$args_start = $open_tag_start + strlen( '{' . $tag );
	// 		$maybe_args_start = substr( $str, $args_start, 1 );
	// 		$str_args = '';

	// 		if( '(' === $maybe_args_start ) {
	// 			$str_args = substr( $str, $args_start + 1, strpos( $str, ')', $args_start ) - $args_start - 1 );

	// 			if ( $debug ) {
	// 				console_log( 'Arguments: ' . $str_args );
	// 			}
	// 		}

	// 		$tag_args = array();

	// 		if ( $str_args ) {
	// 			foreach( explode( ',', $str_args ) as $arg ) {
	// 				$tag_args[] = trim( $arg );
	// 			}
	// 		}

	// 		// Shortcode name is the first argument
	// 		$args = array_merge( array( $tag ), $tag_args );

	// 		$result = (boolean)call_user_func_array( array( $this, $shortcode_data['callback'] ), $args );

	// 		// Conditional tag evaluated to true
	// 		if( $result ) {
	// 			$str = $this->str_slice( $str, $close_tag_start, $close_tag_end );
	// 			$str = $this->str_slice( $str, $open_tag_start, $open_tag_end );
	// 			$offset -= $open_tag_end - $open_tag_start + 1;

	// 			if ( $debug ) {
	// 				console_log( 'Offset subtraction: ' . ( $open_tag_end - $open_tag_start + 1 ) );
	// 			}

	// 		// Conditional tag evaluated to false
	// 		} else {
	// 			$str = $this->str_slice( $str, $open_tag_start, $close_tag_end );
	// 			$offset -= $close_tag_start - $open_tag_start;

	// 			if ( $debug ) {
	// 				console_log( 'Offset subtraction: ' . ( $close_tag_start - $open_tag_start ) );
	// 			}
	// 		}

	// 		if ( $debug ) {
	// 			console_log( 'After:' );
	// 			console_log( 'Offset: ' . $offset );
	// 			console_log( $str );
	// 			console_log( str_repeat( '_', $offset ) . '|' );
	// 		}
	// 	}

	// 	return $str;
	// }

	// /**
	//  * Checks string with conditional tags for correctness or presence of conditional tags
	//  * @param string $str Target string 
	//  * @return boolean
	//  */
	// public function parse_conditional_string( $str ) {
	// 	$stack = array();
	// 	$count = 0;

	// 	preg_replace_callback( '@\{(/?if_[^{(]+)(\([^)]*\))?\}@', function( $m ) use( &$stack, &$count ) {

	// 		// Error previously had occurred 
	// 		if ( false === $count ) {
	// 			return;
	// 		}

	// 		$tag = $m[1];

	// 		// Non-conditional tag - ignore it
	// 		if ( '/if_' === substr( $tag, 0, 4 ) ) {
	// 			$open = false;

	// 		} elseif ( 'if_' === substr( $tag, 0, 3 ) ) {
	// 			$open = true;

	// 		// Non-conditional tag
	// 		} else {
	// 			return;
	// 		}

	// 		if ( $open ) {
	// 			array_push( $stack, $tag );

	// 		} else {
	// 			$expect_tag = array_pop( $stack );

	// 			if ( $tag !== '/' . $expect_tag ) {

	// 				if( $expect_tag ) {
	// 					trigger_error( sprintf( 'Opening tag "%s" is not matching to closing one "%s"', $expect_tag, $tag ) );

	// 				} else {
	// 					trigger_error( sprintf( 'Closing tag "%s" has no counterpart tag', $tag ) );
	// 				}

	// 				$count = false;

	// 				return;
	// 			}

	// 			$count++;
	// 		}

	// 	}, $str );

	// 	return $count > 0;
	// }

	/**
	 * Slices the string
	 * @param string $str Sting to be sliced 
	 * @param int $start Start position (excluded from the resulting string)
	 * @param int $end End position (excluded from the resulting string)
	 * @return string
	 */
	// public function str_slice( $str, $start, $end ) {
	// 	$before = substr( $str, 0, $start );
	// 	$after = substr( $str, $end + 1 );
	// 	return $before . $after;
	// }

	/**
	 * Returns file upload errors by code
	 * @param int $code Error code
	 * @return string
	 */
	public function get_file_upload_error( $code ) {
		$ret = '';

		switch( $code ) {
			case UPLOAD_ERR_INI_SIZE:
				$ret = $this->__( 'Exceeded file size limit of %s bytes', ini_get( 'upload_max_filesize' ) );
				break;
			case UPLOAD_ERR_FORM_SIZE :
				$ret = $this->__( 'Exceeded file size limit (HTML form restriction)' );
				break;
			case UPLOAD_ERR_PARTIAL :
				$ret = $this->__( 'File has been uploaded partially' );
				break;
			case UPLOAD_ERR_NO_FILE :
				$ret = $this->__( 'File has not been uploaded' );
				break;
			case UPLOAD_ERR_NO_TMP_DIR :
				$ret = $this->__( 'Upload temporary folder is missing' );
				break;
			case UPLOAD_ERR_CANT_WRITE :
				$ret = $this->__( 'Unable to write file into disk' );
				break;
			case UPLOAD_ERR_EXTENSION :
				$ret = $this->__( 'Stopped by PHP extension' );
				break;
		}

		return $ret;
	}

	/**
	 * Iterates over directory structure and fire callback each time file or directory encountered
	 * @param string $dir Directory name
	 * @param function Iterator callback
	 * @param boolean $all Flag, to collect hidden and temporary
	 * @return array
	 */
	// public function iterate_directory( $dir, $callback, $all = false ) {

	// 	if ( is_file( $dir ) ) {
	// 		if ( $all || ( '.' !== substr( $dir, 0 , 1 ) && '~' !== substr( $dir, 0, 1 ) ) ) {
	// 			call_user_func( $callback, $dir );
	// 		}

	// 	} elseif ( is_dir( $dir ) ) {
	// 		$dir = rtrim( $dir, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;

	// 		foreach( scandir( $dir ) as $item ) {
	// 			if ( '.' === $item || '..' === $item ) {
	// 				continue;
	// 			}

	// 			$this->iterate_directory( $dir . $item, $callback, $all );
	// 		}

	// 		call_user_func( $callback, $dir );
	// 	}
	// }

	// /**
	//  * Returns directory's content size
	//  * @param string $dir Directory path
	//  * @return integer
	//  */
	// public function get_dir_size( $dir ) {
	// 	$fir = (string)$dir;
	// 	$totalSize = null;
	// 	$count = 0;

	// 	if ( is_dir( $dir ) ) {
	// 		$os = strtoupper( substr( PHP_OS, 0, 3 ) );

	// 		// Windows
	// 		if ( $os === 'WIN' ) {
	// 			if ( extension_loaded( 'com_dotnet' ) ) {
	// 				$obj = new COM( 'scripting.filesystemobject' );

	// 				if ( is_object( $obj ) ) {
	// 					$ref = $obj->getfolder($dir);
	// 					$totalSize = $ref->size;
	// 					$obj = null;
	// 				}
	// 			}
	// 		}

	// 		// Real OS
	// 		if ( is_null( $totalSize ) && $os !== 'WIN' && extension_loaded( 'popen') ) {
	// 			$io = popen('du -sb ' . $dir, 'r');

	// 			if ( $io !== false ) {
	// 				$totalSize = intval( fgets( $io, 80 ) );
	// 				pclose( $io );
	// 			}
	// 		}

	// 		if ( is_null( $totalSize ) ) {
	// 			$totalSize = 0;
	// 			$files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir ) );

	// 			foreach ( $files as $file ) {
	// 				$totalSize += $file->getSize();
	// 				$count++;
	// 			}
	// 		}

	// 	} else if (is_file( $dir ) ) {
	// 		$totalSize = filesize( $dir );
	// 	}

	// 	return $totalSize;
	// }

	/**
	 * Formats bytes into kB. MB etc
	 * @param int $size Bytes
	 * @return string
	 */
	// public function format_bytes( $size ) {
	// 	$points = 2;
	// 	$pow = null;
	// 	$value = (int)$size;
	// 	$units = array( "B", "kB", "MB", "GB" );

	// 	if ( $value <= 0 ) {
	// 		$value = 0;
	// 		$pow = 0;

	// 	} else {
	// 		$pow = floor( log10( $value ) / 3 );
	// 	}

	// 	if( $pow > 0 ) {
	// 		$value /= pow( 10, $pow * 3 );
	// 	}

	// 	return round( $value, $points ) . " " . $units[ $pow ];
	// }

	/**
	 * Returns email history
	 * @param array $data History filter
	 * @return array
	 */
	public function get_history( $data ) {
		$history = $this->db->query( $this->create_query( $data ) );
		return $history->rows;
	}

	/**
	 * Returns records count from history table
	 * @since 1.1.0
	 * @param array $data Filter data
	 * @return int
	 */
	public function get_history_count( $data ) {
		$ret  = 0;
		$data['count'] = '*';
		$query = $this->db->query( $this->create_query( $data ) );

		if ( $query ) {
			$ret = $query->row['COUNT(*)'];
		}

		return $ret;
	}

	/**
	 * Creates query string from supplied data
	 *	- query        - string       - query type
	 * 	- delete_from  - string       - table name to be deleted from when JOIN is used
	 * 	- distinct     - boolean      - flag to fetch distinct fields
	 * 	- table        - array|string - tables to process on (alias => name)
	 * 	- join         - array        - JOIN statement ( type (left, right, join), table, alias, using || on ( operation, left, right ) )
	 * 	- count        - array|string - fields to be count or *
	 * 	- calc         - boolean      - flag whether to calculate rows
	 * 	- fields       - array|string - fields to select (alias => name)
	 * 	- function     - array|string - list to functions to select (function, arguments, alias)
	 * 	- values       - array        - values to insert/update (name => array|string)
	 * 	- on_duplicate - array        - values to be update on key duplication ( name => value )
	 * 	- where        - array        - where clause (operation,field,value)
	 * 	- order_by     - array|string - ordering option (order field => order direction)
	 * 	- limit        - int          - query result limit
	 * 	- start        - int          - query start offset
	 * 	- where_glue   - string       - One of OR|AND 
	 * 	- group_by     - string       - DROUP BY clause
	 * @since 1.1.0
	 * @param array $data Query data
	 * @return string
	 */
	// public function create_query( $data ) {
	// 	if ( function_exists( 'is_log_query' ) && is_log_query() && function_exists( 'console_log' ) ) {
	// 		console_log( $data ); 
	// 	}

	// 	$query = '';

	// 	// Table name is mandatory
	// 	if ( empty( $data['table'] ) ) {
	// 		trigger_error( 'Table missing' );
	// 		return '';
	// 	}

	// 	// Define query type
	// 	if ( empty( $data['query'] ) ) {
	// 		$q = 'select';

	// 	} else {
	// 		$q = strtolower( $data['query'] );
	// 	}

	// 	// Add query type
	// 	if ( 'select' === $q ) {
	// 		$query .= "SELECT";

	// 		if ( ! empty( $data['calc'] ) ) {
	// 			$query .= ' SQL_CALC_FOUND_ROWS';
	// 		}

	// 		if ( ! empty( $data['distinct'] ) ) {
	// 			$query .= ' DISTINCT';
	// 		}
			
	// 	} elseif ( 'insert' === $q ) {
	// 		$query .= "INSERT INTO";

	// 	} elseif ( 'delete' === $q ) {
	// 		$query .= 'DELETE';

	// 		if ( isset( $data['join'] ) && isset( $data['delete_from'] ) ) {
	// 			$query .= ' `' . $this->db->escape( $data['delete_from'] ) . '`';
	// 		}

	// 		$query .= ' FROM';

	// 	} elseif ( 'update' === $q ) {
	// 		$query .= 'UPDATE';
	// 	}

	// 	// Add fields to be fetched
	// 	if ( 'select' === $q  ) {
	// 		$fields_parts = array();
			
	// 		// COUNT
	// 		if ( ! empty( $data['count'] ) ) {
	// 			foreach( (array)$data['count'] as $field ) {
	// 				$fields_parts[] = 'COUNT(' . $this->escape_db_name( $field ) . ')';
	// 			}
	// 		}

	// 		// Functions
	// 		if ( ! empty( $data['function'] ) ) {
	// 			$functions_parts = $this->create_function_clause( $data['function'] );

	// 			if ( false !== $functions_parts ) {
	// 				$fields_parts = array_merge( $fields_parts, $functions_parts );
	// 			}
	// 		} 

	// 		if ( ! empty( $data['fields'] ) ) {

	// 			foreach( (array)$data['fields'] as $alias => $name ) {
	// 				if ( is_numeric( $alias ) ) {
	// 					$fields_parts[] =  $this->escape_db_name( $name );

	// 				} else {

	// 					// Sub-query
	// 					if ( preg_match( '/select/i', $name ) ) {
	// 						$fields_parts[] = '(' . $name . ') as ' . $this->escape_db_value( $alias );

	// 					// Also sub-query
	// 					} elseif ( '(' === substr( $name , 0, 1 ) && ')' === substr( $name, -1, 1 ) ) {
	// 						$fields_parts[] = $name . ' as ' . $this->escape_db_value( $alias );

	// 					} else {
	// 						$fields_parts[] = $this->escape_db_name( $name ) . ' as ' . $this->escape_db_name( $alias );
	// 					}
	// 				}
	// 			}
	// 		}


	// 		if ( empty( $fields_parts ) ) {
	// 			$query .= ' *';

	// 		} else {
	// 			$query .= ' ' . implode( ', ', $fields_parts );
	// 		}

	// 		$query .= " FROM ";
	// 	}

	// 	// Add tables
	// 	foreach( (array)$data['table'] as $alias => $name ) {
	// 		$tables_parts = array();

	// 		if ( is_numeric( $alias ) ) {
	// 			$tables_parts[] = '`' . DB_PREFIX . $this->db->escape( $name ) . '`';

	// 		} else {
	// 			$tables_parts[] = '`' . DB_PREFIX . $this->db->escape( $name ) . '` as `' . $this->db->escape( $alias ) . '`';
	// 		}

	// 		$query .= ' ' . implode( ', ', $tables_parts );
	// 	}

	// 	if ( isset( $data['join'] ) ) {
	// 		$join_parts = $this->create_join( $data['join'] );

	// 		if ( false === $join_parts ) {
	// 			return '';
	// 		}

	// 		$query .= ' ' . implode( ', ', $join_parts );
	// 	}

	// 	// Add values
	// 	if ( ! empty( $data['values'] ) && 'insert' === $q ) {

	// 		if ( ! is_array( $data['values'] ) ) {
	// 			trigger_error( 'DB values need to be packed into array' );
	// 			return '';
	// 		}

	// 		$values = array();
	// 		$names = array();

	// 		foreach( $data['values'] as $name => $value ) {
	// 			$names[] = $this->escape_db_name( $name );
	// 			$values[] = $this->escape_db_value( $value );
	// 		}

	// 		$query .= ' (' . implode( ', ', $names ) . ') VALUES';

	// 		$array = 0;

	// 		// Define maximum values count (dimension)
	// 		foreach( (array)$values as $val ) {
	// 			if ( is_array( $val ) ) {
	// 				$array = max( $array, count( $val ) );
	// 			}
	// 		}

	// 		$pack = array();
	// 		for( $i = 0; $i <= $array; $i++ ) {
	// 			foreach( $values as $v ) {
	// 				$v = (array)$v;

	// 				if ( isset( $v[ $i ] ) ) {
	// 					$pack[] = $v[ $i ];

	// 				} else {
	// 					$pack[] = '';
	// 				}
	// 			}

	// 			$query .= ' (' . implode( ',', $pack ) . ')';
	// 		}

	// 		// ON DUPLICATE KEY UPDATE clause
	// 		if ( ! empty( $data['on_duplicate'] ) ) {
	// 			if ( ! is_array( $data['on_duplicate' ] ) ) {
	// 				trigger_error( 'Data of "ON DUPLICATE KEY UPDATE" clause need to be wrapped into array' );

	// 			} else {
	// 				$duplicate_parts = array();

	// 				foreach( $data['on_duplicate'] as $name => $value ) {

	// 					if ( ! is_scalar( $value ) ) {
	// 						trigger_error( sprintf( 'Value need to be scalar, %s given instead', gettype( $value ) ) );
	// 						continue;
	// 					}

	// 					$duplicate_parts[] = $this->escape_db_name( $name ) . ' = ' . $this->escape_db_value( $value );
	// 				}

	// 				if ( ! empty( $duplicate_parts ) ) {
	// 					$query .= ' ON DUPLICATE KEY UPDATE ' .implode( ', ', $duplicate_parts ); 
	// 				}
	// 			}
	// 		}
	// 	}

	// 	// Add UPDATE SET clause
	// 	if ( ! empty( $data['set'] ) && 'update' === $q ) {
	// 		$set_parts = $this->create_set_clause( $data['set'] );

	// 		if ( false === $set_parts ) {
	// 			return '';
	// 		}

	// 		$query .= ' SET ' . implode( ', ', $set_parts );
	// 	}

	// 	// Add WHERE clause
	// 	if ( ! empty( $data['where'] )  && ! in_array( $q, array( 'insert', 'truncate', ) ) ) {
	// 		$where_parts = $this->create_where_clause( $data['where'] );

	// 		if ( false === $where_parts ) {
	// 			return '';
	// 		}

	// 		if ( isset( $data['where_glue'] ) && 'or' === $data['where_glue'] ) {
	// 			$where_glue = ' OR ';

	// 		} else {
	// 			$where_glue = ' AND ';
	// 		}

	// 		$query .= ' WHERE ' . implode( $where_glue, $where_parts );
	// 	}

	// 	// Add ORDER BY clause
	// 	if ( ! empty( $data['order_by'] ) && 'select' === $q ) {
	// 		$order_by_parts = array();

	// 		foreach( (array)$data['order_by'] as $order_by => $order_dir ) {
	// 			if ( is_numeric( $order_by ) ) {
	// 				$order_by_parts[] = $this->escape_db_name( $order_dir );

	// 			} else {
	// 				if ( 'asc' === strtolower( $order_dir ) || 'desc' === strtolower( $order_dir ) ) {
	// 					$order_by_parts[] = $this->escape_db_name( $order_by ) . ' ' . strtoupper( $order_dir );

	// 				} else {
	// 					$order_by_parts[] = $this->escape_db_name( $order_by );
	// 				}
	// 			}
	// 		}

	// 		$query .= ' ORDER BY ' . implode( ', ', $order_by_parts );
	// 	}

	// 	// Add GROUP BY clause
	// 	if ( ! empty( $data['group_by'] ) && 'select' === $q ) {
	// 		$query .= ' GROUP BY ' . $this->escape_db_name( $data['group_by'] );
	// 	}

	// 	if ( ! empty( $data['limit'] ) && (int)$data['limit'] > 0 && 'select' === $q ) {
	// 		$limit = (int)$data['limit'];
	// 		$start = 0;

	// 		if ( isset( $data['start'] ) && (int)$data['start'] >= 0 ) {
	// 			$start = (int)$data['start'];
	// 		}

	// 		$query .= " LIMIT $start, $limit;";	
	// 	}

	// 	if ( function_exists( 'is_log_query' ) && is_log_query() && function_exists( 'console_log' ) ) {
	// 		console_log( $query );
	// 	}

	// 	return $query;
	// }

	// /**
	//  * Creates WHERE clause for a query
	//  * @param array $where Where data
	//  * @param type|array &$parts Where parts
	//  * @return array Where parts
	//  */
	// public function create_where_clause( $where, &$parts = array() ) {

	// 	if ( is_array( current( $where ) ) ) {
	// 		foreach( $where as $w ) {
	// 			$ret = $this->create_where_clause( $w, $parts );

	// 			if ( false === $ret ) {
	// 				return false;
	// 			}
	// 		}

	// 	} else {
	// 		if( empty( $where['operation'] ) ) {
	// 			trigger_error( 'Where clause operation missing' );
	// 			return false;
	// 		}

	// 		if ( empty( $where['field'] ) ) {
	// 			trigger_error( 'Where clause field name missing' );
	// 			return false;
	// 		}

	// 		if ( ! isset( $where['value'] ) ) {
	// 			trigger_error( 'Where clause values list missing' );
	// 			return false;
	// 		}

	// 		$where_operation = $this->db->escape( strtolower( htmlspecialchars_decode( $where['operation'] ) ) );

	// 		switch( $where_operation ) {
	// 		case 'in' :
	// 			$parts[] = $this->escape_db_name( $where['field'] ) .
	// 				" IN (" . implode( ", ", (array)$this->escape_db_value( $where['value'] ) ) . ")";
	// 			break;
	// 		case '>':
	// 		case '<':
	// 		case '>=':
	// 		case '<=':
	// 		case '=':
	// 		case '<>':
	// 			if ( '<>' === $where_operation && 'NULL' === $where['value'] ) {
	// 				$parts[] = $this->escape_db_name( $where['field'] ) . ' IS NOT NULL';

	// 			} elseif ( '=' === $where_operation && 'NULL' === $where['value'] ) {
	// 				$parts[] = $this->escape_db_name( $where['field'] ) . ' IS NULL';

	// 			} else {
	// 				$parts[] = $this->escape_db_name( $where['field'] ) . ' ' . $where_operation . ' ' .
	// 					$this->escape_db_value( $where['value'] );
	// 			}
	// 			break;
	// 		case 'like' :
	// 			$parts[] = $this->escape_db_name( $where['field'] ) .
	// 				' LIKE (' . $this->escape_db_value( $where['value'] ) . ')';
			
	// 		}
	// 	}

	// 	return $parts;
	// }

	// /**
	//  * Creates function statement clause for a query
	//  * @param array $function Where data
	//  * @param type|array &$parts Where parts
	//  * @return array Function parts
	//  */
	// public function create_function_clause( $function, &$parts = array() ) {

	// 	$args = array();

	// 	if ( is_string( $function ) ) {
	// 		$function = array( 'function' => $function );
	// 	}

	// 	if ( is_array( current( $function ) ) ) {
	// 		foreach( $function as $f ) {
	// 			$ret = $this->create_function_clause( $f, $parts );

	// 			if ( false === $ret ) {
	// 				return false;
	// 			}
	// 		}

	// 	} else {
	// 		if( empty( $function['function'] ) ) {
	// 			trigger_error( 'Function name missing' );
	// 			return false;
	// 		}

	// 		if ( preg_match( '/^([^(]+)\(([^)]*)\)/', $function['function'], $m ) ) {

	// 			$function['function'] = $m[1];

	// 			if ( isset( $m[2] ) ) {
	// 				$argums = array_map( 'trim', explode( ',', $m[2] ) );


	// 				if ( isset( $function['arguments'] ) ) {
	// 					$function['arguments'] = array_merge( (array)$function['arguments'], $argums );

	// 				} else {
	// 					$function['arguments'] = $argums;
	// 				}
	// 			}
	// 		}

	// 		$function_name = $this->db_escape( strtoupper( $function['function'] ) );

	// 		if ( isset( $function['arguments' ] ) ) {
	// 			foreach( (array)$function['arguments'] as $a ) {

	// 				if ( is_array( $a ) && array_key_exists( 'function', $a ) ) {
	// 					$args = array_merge( $args, $this->create_function_clause( $a ) );

	// 				} else {
	// 					$first_char = substr( $a, 0, 1 );

	// 					if ( in_array( $first_char, array( '"', "'", '`' ) ) ) {
	// 						$a = $first_char . $this->db_escape( substr( $a , 1, -1 ) ) . $first_char;

	// 					} elseif ( in_array( $a, array( '*' ) ) ) {
	// 						// Do nothing

	// 					} elseif (strpos( $a, '=' ) !== false ) {
	// 						// do nothing

	// 					} else {
	// 						$a = $this->escape_db_name( $a );
	// 					}

	// 					$args[] = $a;
	// 				}
	// 			}
	// 		}

	// 		$parts[] = $function_name . '(' .
	// 			( ! empty( $args ) ? implode( ', ', $args ) : '' ) .
	// 			')' . 
	// 			( ! empty( $function['alias'] ) ? ' as ' . $this->escape_db_name( $function['alias'] ) : '' );
	// 	}

	// 	return $parts;
	// }

	// /**
	//  * Mergers WHERE clauses
	//  * @param array $where WHERE to be merged
	//  * @param array $with WHERE to be merged with
	//  * @return array
	//  */
	// public function merge_where( $where, $with ) {
	// 	if ( ! is_array( $where ) ) {
	// 		trigger_error( sprintf( 'Merging WHERE clause needs to be an array, %s given instead', gettype( $where ) ) );
	// 		return $where;
	// 	}

	// 	if ( ! is_array( $with ) ) {
	// 		trigger_error( sprintf( 'WHERE clause to be merged with needs to be an array, %s given instead', gettype( $with ) ) );
	// 		return $where;
	// 	}

	// 	if ( is_array( current( $where ) ) ) {
	// 		if ( is_array( current( $with ) ) ) {
	// 			$where = array_merge( $where, $with );

	// 		} else {
	// 			$where[] = $with;
	// 		}

	// 		return $where;

	// 	} else {
	// 		$ret = array();

	// 		if ( $where ) {
	// 			$ret[] = $where;
	// 		}

	// 		if ( is_array( current( $with ) ) ) {
	// 			$ret = array_merge( $ret, $with );

	// 		} elseif ( $with ) {
	// 			$ret[] = $with;
	// 		}

	// 		return $ret;
	// 	}

	// }

	// /**
	//  * Escapes strings to be used in DB
	//  * @param string $value String to be escaped
	//  * @return string
	//  */
	// public function db_escape( $value ) {
	// 	return $this->db->escape( $value );
	// }

	// /**
	//  * Creates UPDATE SET clause for a query
	//  * @param array $set Set data
	//  * @param type|array &$parts Set parts
	//  * @return array Set parts
	//  */
	// public function create_set_clause( $set, &$parts = array() ) {

	// 	if ( ! is_array( $set ) ) {
	// 		trigger_error( 'SET values need to wrapped into array' );
	// 		return false;
	// 	}

	// 	foreach( $set as $name => $val ) {
	// 		$parts[] = $this->escape_db_name( $name ) . ' = ' . $this->escape_db_value( $val );
	// 	}

	// 	return $parts;
	// }

	// /**
	//  * Escapes value to be inserted into DB
	//  * @param scalar $value Value
	//  * @return string
	//  */
	// public function escape_db_value( $value ) {
	// 	$ret = '';

	// 	if ( is_array( $value ) ) {
	// 		$ret = array_map( array( $this, 'escape_db_value' ), $value );

	// 	} else {
	// 		if ( 'NULL' === $value ) {
	// 			$ret = $value;

	// 		// } elseif ( strpos( $value, '=' ) ) {
	// 		// 	$ret = $value;

	// 		} elseif ( $func = $this->is_db_function( $value ) ) {
	// 			$ret = $func;

	// 		} else {
	// 			$ret = "'" . $this->db->escape( $value ) . "'";
	// 		}
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Escapes field name to be inserted into DB
	//  * @param string $name Name
	//  * @return string
	//  */
	// public function escape_db_name( $name ) {
	// 	$ret = '';

	// 	if ( ! is_string( $name ) ) {
	// 		trigger_error( sprintf( 'Failed to create query - field name need to be a string, %s given instead', gettype( $name ) ) );

	// 	} else {
	// 		$parts = array();

	// 		foreach( explode( '.', $name ) as $n ) {
	// 			if ( '*' === $n ) {
	// 				$parts[] = $n;

	// 			} elseif ( $func = $this->is_db_function( $n ) ) {
	// 				$parts[] = $func;

	// 			} else {
	// 				$parts[] = '`' . $this->db->escape( $n ) . '`';
	// 			}
	// 		}
	// 	}

	// 	return implode( '.', $parts );
	// }

	// /**
	//  * Checks whether argument is DB function
	//  * @param type $str Function
	//  * @return string|boolean
	//  */
	// public function is_db_function( $str ) {
	// 	$ret = false;
	// 	$functions = array(
	// 		'ABS',
	// 		'ACOS',
	// 		'ADDDATE',
	// 		'ADDTIME',
	// 		'AES_DECRYPT',
	// 		'AES_ENCRYPT',
	// 		'ANY_VALUE',
	// 		'ASCII',
	// 		'ASIN',
	// 		'ASYMMETRIC_DECRYPT',
	// 		'ASYMMETRIC_DERIVE',
	// 		'ASYMMETRIC_ENCRYPT',
	// 		'ASYMMETRIC_SIGN',
	// 		'ASYMMETRIC_VERIFY',
	// 		'ATAN',
	// 		'ATAN2',
	// 		'AVG',
	// 		'BENCHMARK',
	// 		'BIN',
	// 		'BIT_AND',
	// 		'BIT_COUNT',
	// 		'BIT_LENGTH',
	// 		'BIT_OR',
	// 		'BIT_XOR',
	// 		'CAST',
	// 		'CEIL',
	// 		'CEILING',
	// 		'Centroid',
	// 		'CHAR',
	// 		'CHAR_LENGTH',
	// 		'CHARACTER_LENGTH',
	// 		'CHARSET',
	// 		'COALESCE',
	// 		'COERCIBILITY',
	// 		'COLLATION',
	// 		'COMPRESS',
	// 		'CONCAT',
	// 		'CONCAT_WS',
	// 		'CONNECTION_ID',
	// 		'Contains',
	// 		'CONV',
	// 		'CONVERT',
	// 		'CONVERT_TZ',
	// 		'ConvexHull',
	// 		'COS',
	// 		'COT',
	// 		'COUNT',
	// 		'CRC32',
	// 		'CREATE_ASYMMETRIC_PRIV_KEY',
	// 		'CREATE_ASYMMETRIC_PUB_KEY',
	// 		'CREATE_DH_PARAMETERS',
	// 		'CREATE_DIGEST',
	// 		'Crosses',
	// 		'CURDATE',
	// 		'CURRENT_DATE',
	// 		'CURRENT_TIME',
	// 		'CURRENT_TIMESTAMP',
	// 		'CURRENT_USER',
	// 		'CURTIME',
	// 		'DATABASE',
	// 		'DATE',
	// 		'DATE_ADD',
	// 		'DATE_FORMAT',
	// 		'DATE_SUB',
	// 		'DATEDIFF',
	// 		'DAY',
	// 		'DAYNAME',
	// 		'DAYOFMONTH',
	// 		'DAYOFWEEK',
	// 		'DAYOFYEAR',
	// 		'DECODE',
	// 		'DEFAULT',
	// 		'DEGREES',
	// 		'DES_DECRYPT',
	// 		'DES_ENCRYPT',
	// 		'Dimension',
	// 		'Disjoint',
	// 		'Distance',
	// 		'ELT',
	// 		'ENCODE',
	// 		'ENCRYPT',
	// 		'EXP',
	// 		'EXPORT_SET',
	// 		'ExteriorRing',
	// 		'EXTRACT',
	// 		'ExtractValue',
	// 		'FIELD',
	// 		'FIND_IN_SET',
	// 		'FLOOR',
	// 		'FORMAT',
	// 		'FOUND_ROWS',
	// 		'FROM_BASE64',
	// 		'FROM_DAYS',
	// 		'FROM_UNIXTIME',
	// 		'GET_FORMAT',
	// 		'GET_LOCK',
	// 		'GLength',
	// 		'GREATEST',
	// 		'GROUP_CONCAT',
	// 		'GTID_SUBSET',
	// 		'GTID_SUBTRACT',
	// 		'HEX',
	// 		'HOUR',
	// 		'IF',
	// 		'IFNULL',
	// 		'IN',
	// 		'INET_ATON',
	// 		'INET_NTOA',
	// 		'INET6_ATON',
	// 		'INET6_NTOA',
	// 		'INSTR',
	// 		'INTERVAL',
	// 		'IS_FREE_LOCK',
	// 		'IS_IPV4',
	// 		'IS_IPV4_COMPAT',
	// 		'IS_IPV4_MAPPED',
	// 		'IS_IPV6',
	// 		'IS_USED_LOCK',
	// 		'IsClosed',
	// 		'IsEmpty',
	// 		'ISNULL',
	// 		'IsSimple',
	// 		'JSON_APPEND',
	// 		'JSON_ARRAY',
	// 		'JSON_ARRAY_APPEND',
	// 		'JSON_ARRAY_INSERT',
	// 		'JSON_CONTAINS',
	// 		'JSON_CONTAINS_PATH',
	// 		'JSON_DEPTH',
	// 		'JSON_EXTRACT',
	// 		'JSON_INSERT',
	// 		'JSON_KEYS',
	// 		'JSON_LENGTH',
	// 		'JSON_MERGE',
	// 		'JSON_OBJECT',
	// 		'JSON_QUOTE',
	// 		'JSON_REMOVE',
	// 		'JSON_REPLACE',
	// 		'JSON_SEARCH',
	// 		'JSON_SET',
	// 		'JSON_TYPE',
	// 		'JSON_UNQUOTE',
	// 		'JSON_VALID',
	// 		'LAST_INSERT_ID',
	// 		'LCASE',
	// 		'LEAST',
	// 		'LEFT',
	// 		'LENGTH',
	// 		'LineFromText',
	// 		'LineFromWKB',
	// 		'LineString',
	// 		'LN',
	// 		'LOAD_FILE',
	// 		'LOCALTIME',
	// 		'LOCALTIMESTAMP',
	// 		'LOCATE',
	// 		'LOG',
	// 		'LOG10',
	// 		'LOG2',
	// 		'LOWER',
	// 		'LPAD',
	// 		'LTRIM',
	// 		'MAKE_SET',
	// 		'MAKEDATE',
	// 		'MAKETIME',
	// 		'MASTER_POS_WAIT',
	// 		'MAX',
	// 		'MBRContains',
	// 		'MBRCoveredBy',
	// 		'MBRCovers',
	// 		'MBRDisjoint',
	// 		'MBREqual',
	// 		'MBREquals',
	// 		'MBRIntersects',
	// 		'MBROverlaps',
	// 		'MBRTouches',
	// 		'MBRWithin',
	// 		'MD5',
	// 		'MICROSECOND',
	// 		'MID',
	// 		'MIN',
	// 		'MINUTE',
	// 		'MLineFromText',
	// 		'MLineFromWKB',
	// 		'MOD',
	// 		'MONTH',
	// 		'MONTHNAME',
	// 		'NAME_CONST',
	// 		'NOW',
	// 		'NULLIF',
	// 		'NumPoints',
	// 		'OCT',
	// 		'OCTET_LENGTH',
	// 		'OLD_PASSWORD',
	// 		'ORD',
	// 		'Overlaps',
	// 		'PASSWORD',
	// 		'PERIOD_ADD',
	// 		'PERIOD_DIFF',
	// 		'PI',
	// 		'POSITION',
	// 		'POW',
	// 		'POWER',
	// 		'PROCEDURE ANALYSE',
	// 		'QUARTER',
	// 		'QUOTE',
	// 		'RADIANS',
	// 		'RAND',
	// 		'RANDOM_BYTES',
	// 		'RELEASE_ALL_LOCKS',
	// 		'RELEASE_LOCK',
	// 		'REPEAT',
	// 		'REPLACE',
	// 		'REVERSE',
	// 		'RIGHT',
	// 		'ROUND',
	// 		'ROW_COUNT',
	// 		'RPAD',
	// 		'RTRIM',
	// 		'SCHEMA',
	// 		'SEC_TO_TIME',
	// 		'SECOND',
	// 		'SESSION_USER',
	// 		'SHA1',
	// 		'SHA2',
	// 		'SIGN',
	// 		'SIN',
	// 		'SLEEP',
	// 		'SOUNDEX',
	// 		'SPACE',
	// 		'SQRT',
	// 		'SRID',
	// 		'STD',
	// 		'STDDEV',
	// 		'STDDEV_POP',
	// 		'STDDEV_SAMP',
	// 		'STR_TO_DATE',
	// 		'STRCMP',
	// 		'SUBDATE',
	// 		'SUBSTR',
	// 		'SUBSTRING',
	// 		'SUBSTRING_INDEX',
	// 		'SUBTIME',
	// 		'SUM',
	// 		'SYSDATE',
	// 		'SYSTEM_USER',
	// 		'TAN',
	// 		'TIME',
	// 		'TIME_FORMAT',
	// 		'TIME_TO_SEC',
	// 		'TIMEDIFF',
	// 		'TIMESTAMP',
	// 		'TIMESTAMPADD',
	// 		'TIMESTAMPDIFF',
	// 		'TO_BASE64',
	// 		'TO_DAYS',
	// 		'TO_SECONDS',
	// 		'Touches',
	// 		'TRIM',
	// 		'TRUNCATE',
	// 		'UCASE',
	// 		'UNCOMPRESS',
	// 		'UNCOMPRESSED_LENGTH',
	// 		'UNHEX',
	// 		'UNIX_TIMESTAMP',
	// 		'UpdateXML',
	// 		'UPPER',
	// 		'USER',
	// 		'UTC_DATE',
	// 		'UTC_TIME',
	// 		'UTC_TIMESTAMP',
	// 		'UUID',
	// 		'UUID_SHORT',
	// 		'VALIDATE_PASSWORD_STRENGTH',
	// 		'VALUES',
	// 		'VAR_POP',
	// 		'VAR_SAMP',
	// 		'VARIANCE',
	// 		'VERSION',
	// 		'WAIT_FOR_EXECUTED_GTID_SET',
	// 		'WAIT_UNTIL_SQL_THREAD_AFTER_GTIDS',
	// 		'WEEK',
	// 		'WEEKDAY',
	// 		'WEEKOFYEAR',
	// 		'WEIGHT_STRING',
	// 		'YEAR',
	// 		'YEARWEEK',
	// 	);

	// 	if ( preg_match( '/^([^(]+)\(([^)]*)\)$/', $str, $m ) ) {

	// 		$func = strtoupper( $m[1] );

	// 		if ( ! in_array( $func, $functions ) ) {
	// 			return false;
	// 		}

	// 		$ret = $this->db->escape( $func ) . '(';

	// 		if ( isset( $m[2] ) ) {
	// 			$args = array_map( 'trim', explode( ',', $m[2] ) );

	// 			if ( trim( $m[2] ) !== '' ) {
	// 				$ret .= ' ' . implode( ', ', $this->escape_db( $args ) ) . ' ';
					
	// 			} else {
	// 				$ret .= '';
	// 			}
	// 		}

	// 		$ret .= ')';
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Escape value
	//  * @param string|array $value Value to be escaped 
	//  * @return string|array
	//  */
	// public function escape_db( $value ) {
	// 	$ret = $value;

	// 	if ( is_array( $value ) ) {
	// 		$ret = array_map( array( $this, 'escape_db' ), $value );

	// 	} else {
	// 		$first_char = substr( $value, 0, 1 );
	// 		$last_char = substr( $value , -1, 1 );

	// 		if (  '`' === $first_char && '`' === $last_char ) {
	// 			$ret = $this->escape_db_name( substr( substr( $value, 1 ), 0, -1 ) );

	// 		} elseif ( in_array( $first_char, array( '"', "'", ) ) && in_array( $last_char, array( '"', "'", ) ) ) {
	// 			$ret = $this->escape_db_value( substr( substr( $value, 1 ), 0, -1 ) );

	// 		// } elseif ( strpos( $value, '=' ) ) {
	// 		// 	$ret = $value;

	// 		} elseif ( 'NULL' === $value ) {
	// 			$ret = $value;

	// 		} elseif ( is_numeric( $value ) ) {
	// 			$ret = preg_replace( '/[^0-9.,]/', '', $value );

	// 		} else {
	// 			$ret = $this->escape_db_name( $value );
	// 		}
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Creates JOIN clause
	//  * @param array $join Joinn data
	//  * @param type|array &$parts Join parts
	//  * @return array Join parts
	//  */
	// public function create_join( $join, &$parts = array() ) {
	// 	$ret = '';

	// 	if ( is_array( current( $join ) ) ) {
	// 		foreach( $join as $j ) {
	// 			$res = $this->create_join( $j, $parts );

	// 			if ( false === $res ) {
	// 				return false;
	// 			}
	// 		}

	// 	} else {
	// 		if ( ! empty( $join['type'] ) ) {
	// 			switch( $join['type'] ) {
	// 			case 'left' :
	// 				$ret .= 'LEFT JOIN';
	// 				break;
	// 			case 'right' :
	// 				$ret .= 'RIGHT JOIN';
	// 				break;
	// 			default :
	// 				$ret = 'JOIN';
	// 				break;
	// 			}

	// 		} else {
	// 			$ret .= 'JOIN';
	// 		}

	// 		if ( empty( $join['table'] ) ) {
	// 			trigger_error( 'Failed to create query - missing name of the table to be joined' );
	// 			return false;
	// 		}

	// 		$ret .= ' `' . DB_PREFIX . $join['table'] . '`';

	// 		if ( empty( $join['alias'] ) ) {
	// 			trigger_error( 'Failed to create query - missing alias of the table to be joined' );
	// 		}

	// 		$ret .= ' `' . $join['alias'] . '`';

	// 		if ( empty( $join['on'] ) && empty( $join['using'] ) ) {
	// 			trigger_error( 'Failed to create query - joining condition missing' );
	// 			return false;
	// 		}

	// 		if ( ! empty( $join['using'] ) ) {
	// 			$ret .= ' USING(' . $this->escape_db_name( $join['using'] ) . ')';

	// 		} else {
	// 			$on_parts = $this->create_on_clause( $join['on'] );

	// 			if ( false === $on_parts ) {
	// 				return false;
	// 			}

	// 			$ret .= ' ' . implode( ', ', $on_parts );
	// 		}

	// 		$parts[] = $ret;
	// 	}

	// 	return $parts;
	// }

	// /**
	//  * Creates ON clause for a JOIN statement
	//  * @param array $on On data
	//  * @param type|array &$parts On parts
	//  * @return array On parts
	//  */
	// public function create_on_clause( $on, &$parts = array() ) {

	// 	if ( is_array( current( $on ) ) ) {
	// 		foreach( $on as $o ) {
	// 			$ret = $this->create_on_clause( $o, $parts );

	// 			if ( false === $ret ) {
	// 				return false;
	// 			}
	// 		}

	// 	} else {
	// 		if( empty( $on['operation'] ) ) {
	// 			trigger_error( 'Failed to create query - ON clause\'s OPERATION part is missing' );
	// 			return false;
	// 		}

	// 		if ( empty( $on['left'] ) ) {
	// 			trigger_error( 'Failed to create query - ON clause\'s LEFT part is missing' );
	// 			return false;
	// 		}

	// 		if ( ! isset( $on['right'] ) ) {
	// 			trigger_error( 'Failed to create query - ON clause\'s RIGHT part is missing' );
	// 			return false;
	// 		}

	// 		$on_operation = $this->db->escape( strtolower( htmlspecialchars_decode( $on['operation'] ) ) );

	// 		switch( $on_operation ) {
	// 		case '>':
	// 		case '<':
	// 		case '>=':
	// 		case '<=':
	// 		case '=':
	// 			$parts[] = 'ON (' . $this->escape_db_name( $on['left'] ) . ' ' . $on_operation . ' ' .
	// 				$this->escape_db_name( $on['right'] ) . ')';
	// 			break;
	// 		default: 
	// 			trigger_error( 'Failed to create query - forbidden ON clause operator: ' . $on_operation );
	// 			return false;
	// 			break;
	// 		}
	// 	}

	// 	return $parts;
	// }

	// /**
	//  * Returns SQL_CALC_FOUND_ROWS result
	//  * @return int
	//  */
	// public function get_calc_rows() {
	// 	$ret = 0;
	// 	$query = $this->db->query( "SELECT FOUND_ROWS()" );

	// 	if ( $query && isset( $query->row ) ) {
	// 		$ret = $query->row['FOUND_ROWS()'];
	// 	}

	// 	return $ret;
	// }

	// /**
	//  * Returns formatted SQL string with data range query
	//  * @param string $from SQL date from 
	//  * @param string $to SQL date to 
	//  * @param string $interval SQL INTERVAL value
	//  * @return string
	//  */
	// public function get_sql_date_range( $from, $to, $interval ) {
	// 	$d = "DATE_ADD( '" . $this->db->escape( $from) . "', INTERVAL a + b + c " . $this->db->escape( $interval ) .")";

	// 	$ret = "SELECT  $d as `date` FROM
	// 	( SELECT 0 a UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) aa,
	// 	( SELECT 0 b UNION SELECT 10 UNION SELECT 20 UNION SELECT 33 UNION SELECT 40 UNION SELECT 50 UNION SELECT 60 UNION SELECT 70 UNION SELECT 80 UNION SELECT 90) bb,
	// 	( SELECT 0 c UNION SELECT 100 UNION SELECT 200 UNION SELECT 300 UNION SELECT 400 UNION SELECT 500 UNION SELECT 600 UNION SELECT 700 UNION SELECT 800 UNION SELECT 900 ) cc WHERE $d BETWEEN '" . $this->db->escape( $from ) . "' AND '" . $this->db->escape( $to ) . "'";

	// 	return $ret;
	// }

	// /**
	//  * Returns array representation of SQL date
	//  * @param string $str Date string 
	//  * @return array
	//  */
	// public function parse_sql_date( &$str ) {
	// 	$ret = array(
	// 		'y' => 0,
	// 		'm' => 0,
	// 		'd' => 0,
	// 		'h' => 0,
	// 		'i' => 0,
	// 		's' => 0,
	// 	);

	// 	if ( preg_match( '/^(\d{4})-(\d{2})-(\d{2})(?:\s+(\d{2}):(\d{2})(?::(\d{2}))?)?$/', $str, $m ) ) {
	// 		$ret['y'] = $m[1];
	// 		$ret['m'] = $m[2];
	// 		$ret['d'] = $m[3];

	// 		if ( isset( $m[4] ) ) {
	// 			$ret['h'] = $m[4];
	// 			$ret['i'] = $m[5];
	// 		}

	// 		if ( isset( $m[6] ) ) {
	// 			$ret['s'] = $m[6];
	// 		}
	// 	}

	// 	$str = $ret;

	// 	return $ret;
	// }

	// /**
	//  * Converts SQL date array to string
	//  * @param array $date SQL date
	//  * @return string
	//  */
	// public function sql_date_to_str( &$date ) {
	// 	$ret = $date['y'] . '-' .
	// 		str_pad( $date['m'], 2, '0', STR_PAD_LEFT ) . '-' .
	// 		str_pad( $date['d'], 2, '0', STR_PAD_LEFT ) . ' ' .
	// 		str_pad( $date['h'], 2, '0', STR_PAD_LEFT ) . ':' .
	// 		str_pad( $date['i'], 2, '0', STR_PAD_LEFT ) . ':' .
	// 		str_pad( $date['s'], 2, '0', STR_PAD_LEFT );

	// 	$date = $ret;

	// 	return $ret;
	// }

	// /**
	//  * Usort callback to sort sql date
	//  * @param array $a Date A
	//  * @param array $b Date B 
	//  * @return int
	//  */
	// public function compare_sql_date( $a, $b ) {
	// 	foreach( array( 'y', 'm', 'd', 'h', 'i', 's' ) as $part ) {
	// 		if ( $a[ $part ] > $b[ $part ] ) {
	// 			return 1;

	// 		} else if ( $a[ $part ] < $b[ $part ] ) {
	// 			return -1;
	// 		}
	// 	}

	// 	return 0;
	// }

	// /**
	//  * Runs query to DB
	//  * @param array $data Query data 
	//  * @return boolean|int|array
	//  */
	// public function run_query( $data ) {
	// 	$ret = null;

	// 	if ( is_array( $data ) ) {
	// 		$data = $this->create_query( $data );
	// 	}

	// 	if ( ! $data ) {
	// 		return $ret;
	// 	}

	// 	$query = $this->db->query( $data );

	// 	if ( $query ) {

	// 		// Delete, insert, update
	// 		if ( gettype( $query ) === 'boolean' ) {

	// 			if ( $this->db->countAffected() > 0 ) {
	// 				$ret = $this->db->countAffected();

	// 			} else {
	// 				$ret = null;
	// 			}

	// 		// Select
	// 		} elseif ( gettype( $query ) === 'object' && ( isset( $query->row ) || isset( $query->rows ) ) ) {
	// 			$ret = new DB_Result( $query->rows );
	// 		}
	// 	}

	// 	return $ret;
	// }

	/**
	 * Selects distinct fields from history table
	 * @param string $type Field name
	 * @param string $query Query string
	 * @param int $start Start offset
	 * @param int $limit Elements count to fetch
	 * @since 1.1.0
	 * @return array
	 */
	public function get_history_by( $type, $query, $start, $limit ) {
		$ret = array(
			'products'    => array(),
			'total_count' => 0,
		);

		$query = str_replace( array( '%', '_' ), array( '\\%', '\\_' ), $query );

		$f_query = $this->db->query(
			"SELECT SQL_CALC_FOUND_ROWS DISTINCT `" . $this->db->escape( $type ) . "`
			FROM `" . DB_PREFIX . $this->history_table . "`
			WHERE `" . $this->db->escape( $type ) . "` LIKE '%" . $this->db->escape( $query ) . "%'
			LIMIT " . (int)$start . ", " . (int)$limit
		);
		
		if( $f_query->num_rows ) {
			foreach( $f_query->rows as $row ) {
				$ret['filter'][] = array( 'id' => trim( $row[ $type ] ), 'text' => $row[ $type ] );
			} 

			$count = $this->db->query( "SELECT FOUND_ROWS()" );
			$ret['total_count'] = $count->row['FOUND_ROWS()'];
		}

		return $ret;
	}

	/**
	 * Selects total count of distinct fields of some name at history table
	 * @since 1.1.0
	 * @param string $type Field name
	 * @return int
	 */
	public function get_history_by_count( $type ) {
		$query = $this->db->query(
			"SELECT COUNT(DISTINCT `" . $this->db->escape( $type ) . "`) as count FROM `" . DB_PREFIX . $this->history_table . "`"
		);

		return $query->row['count'];
	}

	/**
	 * Returns fa-icon, which corresponds to specific mine type
	 * @since 1.1.0
	 * @param string mime Mime type
	 * @return string
	 */
	// public function get_mime_icon( $mime ) {
	// 	$icon = null;

	// 	switch( $mime ) {
	// 	case "application/msword":
	// 	case "application/vnd.ms-word.document.macroenabled.12" :
	// 	case "application/vnd.ms-word.template.macroenabled.12" :
	// 	case "application/vnd.openxmlformats-officedocument.wordprocessingml.document" :
	// 	case "application/vnd.openxmlformats-officedocument.wordprocessingml.template" :
	// 		$icon = "file-word-o";
	// 		break;
	// 	case "application/rtf" :
	// 		$icon = "fle-text";
	// 		break;
	// 	case "application/pdf" :
	// 		$icon = "file-pdf-o";
	// 		break;
	// 	case "application/zip" :
	// 		$icon = "file-zip-o";
	// 		break;
	// 	case "application/vnd.ms-excel" :
	// 	case "application/vnd.ms-excel.addin.macroenabled.12" :
	// 	case "application/vnd.ms-excel.sheet.binary.macroenabled.12" :
	// 	case "application/vnd.ms-excel.sheet.macroenabled.12" :
	// 	case "application/vnd.ms-excel.template.macroenabled.12" :
	// 	case "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" :
	// 	case "application/vnd.openxmlformats-officedocument.spreadsheetml.template" :
	// 		$icon = "file-excel-o";
	// 		break;
	// 	case "application/vnd.ms-powerpoint" :
	// 	case "application/vnd.ms-powerpoint.addin.macroenabled.12" :
	// 	case "application/vnd.ms-powerpoint.presentation.macroenabled.12" :
	// 	case "application/vnd.ms-powerpoint.slide.macroenabled.12" :
	// 	case "application/vnd.ms-powerpoint.slideshow.macroenabled.12" :
	// 	case "application/vnd.ms-powerpoint.template.macroenabled.12" :
	// 	case "application/vnd.openxmlformats-officedocument.presentationml.presentation" :
	// 	case "application/vnd.openxmlformats-officedocument.presentationml.slide" :
	// 	case "application/vnd.openxmlformats-officedocument.presentationml.slideshow" :
	// 	case "application/vnd.openxmlformats-officedocument.presentationml.template" :
	// 		$icon = "file-powerpoint-o";
	// 		break;
	// 	case "text/cache-manifest" :
	// 	case "text/calendar" :
	// 	case "text/css" :
	// 	case "text/csv" :
	// 	case "text/html" :
	// 	case "text/x-php" :
	// 		$icon = "file-code-o";
	// 		break;
	// 	case "application/mp21" :
	// 	case "application/mp4" :
	// 	case "application/ogg" :
	// 		$icon = "file-audio";
	// 		break;
	// 	}

	// 	if( null === $icon ) {
	// 		if( preg_match( '/^text\//', $mime ) ) {
	// 			$icon = "file-text-o";

	// 		} elseif ( preg_match( '/^application\//', $mime ) ) {
	// 			$icon = "file-code-o";

	// 		} elseif ( preg_match( '/^audio\//', $mime ) ) {
	// 			$icon = "file-audio-o";

	// 		} elseif ( preg_match( '/^image\//', $mime ) ) {
	// 			$icon = "file-image-o";

	// 		} elseif ( preg_match( '/^video\//', $mime ) ) {
	// 			$icon = "file-video-o";

	// 		} else {
	// 			$icon = "file-o";
	// 		}
	// 	}

	// 	return $icon;
	// }

	/**
	 * Returns status name by its code
	 * @param int $code Status code 
	 * @return string
	 */
	public function get_status_name( $code ) {
		switch( $code ) {
		case self::SUBSCRIBER_STATUS_INACTIVE :
			$ret = $this->__( 'Inactive' );
			break;
		case self::SUBSCRIBER_STATUS_ACTIVE:
			$ret = $this->__( 'Active' );
			break;
		case self::SUBSCRIBER_STATUS_SUSPENDED:
			$ret = $this->__( 'Suspended' );
			break;
		case self::SUBSCRIBER_STATUS_VERIFICATION:
			$ret = $this->__( 'Under verification' );
			break;
		case self::SUBSCRIBER_STATUS_CANCELLED:
			$ret = $this->__( 'Canceled' );
			break;
		default:
			$ret = $this->helper->__( 'Undefined' );
			break;
		}

		return $ret;
	}

	/**
	 * Checks whether subscriber already exists
	 * @param string $email Email address 
	 * @param int $newsletter Newsletter ID
	 * @return boolean
	 */
	public function check_subscriber( $email, $newsletter ) {
		$result = $this->q( array(
			'table' => $this->newsletter_subscribers_table,
			'query' => 'select',
			'where' => array(
				array(
					'field'     => 'email',
					'operation' => '=',
					'value'     => $email,
				),
				array(
					'field'     => 'newsletter',
					'operation' => '=',
					'value'     => $newsletter,
				),
			),
		) );

		return count( $result ) > 0;
	}

	/**
	 * Subscribes subscriber
	 * @param array $data subscription data
	 * @return boolean
	 */
	public function subscribe( $data ) {
		$result = $this->q( array(
			'table' => $this->newsletter_subscribers_table,
			'query' => 'insert',
			'values' => array(
				'name'       => $data['name'],
				'email'      => $data['email'],
				'status'     => $data['status'],
				'date_added' => date( 'c' ),
				'newsletter' => $data['newsletter']
			), 
		) );

		return $result;
	}

	/**
	 * Returns script to pull-in subscription widget
	 * @param int $id Widget ID, optional
	 * @return string
	 */
	public function get_widget_script( $id = '' ) {
		return <<<script
<script>
(function(){
var s1=document.createElement("script"),
s0=document.getElementsByTagName("script")[0],
head  = document.getElementsByTagName('head')[0],
link  = document.createElement('link');

s1.async=true;
s1.src='{$this->get_store_url()}index.php?route={$this->type}/{$this->code}/widget&id={$id}';
s1.charset='UTF-8';
s1.setAttribute('crossorigin','*');
s0.parentNode.insertBefore(s1,s0);

link.rel  = 'stylesheet';
link.type = 'text/css';
link.href = '{$this->get_store_url()}index.php?route={$this->type}/{$this->code}/widget_css&id={$id}';
link.media = 'all';
head.appendChild(link);
})();
</script>
<span id="adk-widget-span"></span>
script;
	}

	/**
	 * Removes white spaces from script string
	 * @param string $str Input string 
	 * @return string
	 */
	public function remove_whitespaces( $str ) {
		$str = preg_replace( '/\s+/', ' ', $str );
		$str = preg_replace( '/(?<=[;,})({\[:\-+\\*!&|\]\'\"=])\s/i', '', $str );
		$str = preg_replace( '/\s(?!\w)/i', '', $str );

		return $str;
	}

	/**
	 * Returns newsletter-widget pair by widget ID
	 * @param int $widget_id Widget's ID
	 * @return array
	 */
	public function get_newsletter_by_widget( $widget_id ) {
		$query = $this->db->query(
			"SELECT `n`.* FROM `" . DB_PREFIX . $this->newsletter_list_table . "` n
			LEFT JOIN `" . DB_PREFIX . $this->h->newsletter_to_widget_table . "` n2w
				ON(`n`.`id` = `n2w`.`newsletter_id`)
			LEFT JOIN `" . DB_PREFIX . $this->newsletter_widget_table . "` nw
				ON( `n2w`.`widget_id` = `nw`.`id`)
			WHERE `nw`.`id` = " . (int)$widget_id
		);

		return new DB_Result( $query->rows );
	}

	/**
	 * Returns QsL-formatted date string of code expiration date
	 * @param string $operation Operation code
	 * @return string
	 */
	public function get_sql_expiration_date( $operation ) {
		$cur_date = new DateTime();

		switch( $operation ) {
		case 'confirm_subscription' :
			$offset = 'P' . self::CODE_EXPIRATION_CONFIRM . 'D';
			break;
		case 'unsubscribe' :
			$offset = 'P' . self::CODE_EXPIRATION_CANCEL . 'D';
			break;
		case 'track_visit' :
			$offset = 'P' . self::CODE_EXPIRATION_TRACK_VISIT . 'D';
			break;
		default:
			$offset = 'P' . self::CODE_EXPIRATION_DEFAULT . 'D';
			break;
		}

		return $cur_date->add( new DateInterval( $offset ) )->format( 'Y-m-d H:i:s' );
	}

	/**
	 * Removes all the expired codes from newsletter's codes table
	 * @return void
	 */
	public function remove_expired_code() {
		$this->q( array(
			'table' => $this->newsletter_code_table,
			'query' => 'delete',
			'where' => array(
				'field'     => 'expiration',
				'operation' => '<',
				'value'     => 'now()',
			),
		) );
	}

	/**
	 * Removes specific code form newsletter's code table 
	 * @param string $code Newsletter code 
	 * @return boolean Operation status
	 */
	public function remove_newsletter_code( $code ) {
		return $this->q( array(
			'table' => $this->newsletter_code_table,
			'query' => 'delete',
			'where' => array(
				'field'     => 'code',
				'operation' => '=',
				'value'     => $code,
			),
		) );
	}

	/**
	 * Sanitizes hook's template part
	 * @param type $name 
	 * @return type
	 */
	public function sanitize_hook_name( $name ) {
		$name = str_replace( array( ' ' ), '_', $name );
		$name = preg_replace( '/[^a-z0-9_]/i', '', $name );

		return $name;
	}

	/**
	 * Returns customizable caption to show to user
	 * @param string $name Caption code 
	 * @return string
	 */
	// public function caption( $name ) {
	// 	return nl2br( $this->get_lang_caption( $name ) );
	// }

	/**
	 * Returns queue heartbeat status
	 * @return boolean
	 */
	public function get_queue_status() {
		if ( file_exists( $this->tmp_dir . 'queue_status' ) ) {
			return time() - fileatime( $this->tmp_dir . 'queue_status' ) < 120;
		}

		return false;
	}

	/**
	 * Runs the queue
	 * @return void
	 */
	public function run_queue() {
		$attempt = (int)$this->config( 'queue_attempts' );

		// Remove attempted emails
		if ( $attempt ) {
			$this->q( array(
				'table' => $this->queue_table,
				'query' => 'delete',
				'where' => array(
					'field'     => 'attempt',
					'operation' => '>',
					'value'     => $attempt,
				),
			) );
		} 

		// Fetch queue
		$queue = $this->q( array(
			'table' => $this->queue_table,
			'query' => 'select',
		) );

		require_once( $this->swift_loader );

		foreach( $queue as $q ) {
			$file = $q['content'];

			if ( ! file_exists( $file ) ) {
				trigger_error( 'Queue\'s content file missing' );
				$this->remove_from_queue( $q['id'] );
				return false;
			}

			$content = json_decode( file_get_contents( $file ) );

			if ( json_last_error() ) {
				trigger_error( 'Queue content is corrupted. Content: ' . file_get_contents( $file ) );
				$this->remove_from_queue( $q['id'], $file );
				return false;
			}

			$mailer = unserialize( $content->mailer );
			$message = unserialize( $content->message );
			$data = $this->object_to_array( $content->data );

			if ( $this->run_mailer( $mailer, $message, $data ) ) {
				$this->remove_from_queue( $q['id'], $file );

			} else {
				$this->db->query(
					"UPDATE `" . DB_PREFIX . $this->queue_table . "`
					SET `attempt` = `attempt` + 1 WHERE `id` = " . (int)$q['id']
				);
			}
		}
	}

	/**
	 * Removes email from the queue
	 * @param int $id Queue ID, optional
	 * @param string $file Content file name, optional
	 * @return void
	 */
	public function remove_from_queue( $id = null, $file = null ) {
		if ( $file ) {
			unlink( $file );
		}

		if ( ! is_null( $id ) ) {
			$this->q( array(
				'table' => $this->queue_table,
				'query' => 'delete',
				'where' => array(
					'field'     => 'id',
					'operation' => '=',
					'value'     => $id,
				),
			) );
		}
	}

	/**
	 * Adds tracking visits facility into email contents
	 * @param string $content Email contents
	 * @return string
	 */
	public function add_visit_track_code( $content ) {
		if ( ! $this->adk_template ||
			! $this->get_configuration( 'track_visit', $this->adk_tempalte['template_id'] ) ||
			( defined( 'PREVIEW' ) && 1 === PREVIEW ) ) {

			return $content;
		}

		$code = uniqid();
		$count = 0;
		$self = $this;

		$content = preg_replace_callback(
			array(
				'/(?P<before><a.+?href=)(\"|\')(?P<url>.+?)\2/'
			), function( $matches ) use( &$count, $code, $self ) {

			$ret = '';

			// Fall-back
			if ( isset( $matches[0] ) ) {
				$ret = $matches[0];
			}

			if ( isset( $matches['url'] ) && '#' !== $matches['url'] ) {
				$url  = new Url( $matches['url'] );
				$route = $url->get_query( 'route' );
				$do_not_track = array(

					// Unsubscribe link
					$self->type . '/'. $self->code . '/unsubscribe',

					// Open in browser link
					$self->type . '/' . $self->code . '/archive',
				);

				if ( ! $route || ! in_array( $route, $do_not_track ) ) {
					if ( isset( $matches['before'] ) ) {
						$ret = $matches['before'] . '"' . $url->add_query( 'adk', $code )->to_string() . '"';

					} else {
						$ret = $url->add_query( 'adk', $code )->to_string();
					}

					$count++;
				}
			}

			return $ret;

		}, $content );

		if ( $count ) {
			$insert = $this->q( array(
				'table' => $this->newsletter_code_table,
				'query' => 'insert',
				'values' => array(
					'code'        => $code,
					'expiration'  => $this->get_sql_expiration_date( 'track_visit' ),
					'newsletter'  => $this->adk_newsletter_id,
					'operation'   => self::NEWSLETTER_CODE_TRACK_VISITOR,
				)
			) );

			if ( $insert ) {
				$this->tracking_visit_id = $code;

			} else {
				trigger_error( 'Failed to create track visitors code' );
			}
		}

		return $content;
	}

	/**
	 * Tracks visitors from newsletter
	 * @return void
	 */
	public function track_subscriber() {

		// TODO: make something
		if( version_compare( VERSION, '2.2.0.0', '<' ) && ! $this->config( 'status' ) ) {
			return;
		}

		$code = $this->request->get['adk'];

		$code_data = $this->q( array(
			'table' => $this->newsletter_code_table,
			'query' => 'select',
			'where' => array(
				'field'     => 'code',
				'operation' => '=',
				'value'     => $code,
			),
		) );

		if ( count( $code_data ) && (int)$code_data['operation'] === self::NEWSLETTER_CODE_TRACK_VISITOR ) {
			$result = $this->q( array(
				'table' => $this->history_table,
				'query' => 'update',
				'set'   => array(
					'date_visited' => 'now()',
				),
				'where' => array(
					'field'     => 'tracking_visit_id',
					'operation' => '=',
					'value'     => $code,
				),
			) );

			if ( $result ) {
				$this->remove_newsletter_code( $code );
				$this->remove_expired_code();
			}
		}
	}

	/**
	 * Performs main IMAP connection actions
	 * @param array $commands  Commands list
	 * @param string $url IMAP server URL
	 * @param string $port IMAP server port
	 * @param string $user Login name
	 * @param string $pwd Password
	 * @param boolean $ssl Flag to use secure connection
	 * @return void
	 */
	public function imap( $commands, $url = null, $port = null, $user = null, $pwd = null, $ssl = null ) {
		if ( is_null( $url ) ) {
			$url = $this->config( 'imap_url' );
		}

		if ( is_null( $port ) ) {
			$port = $this->config( 'imap_port' );
		}

		if ( is_null( $user ) ) {
			$user = $this->config( 'imap_login' );
		}

		if ( is_null( $pwd ) ) {
			$pwd = $this->config( 'imap_password' );
		}

		$url = trim( $url );
		$port = trim( $port );
		$user = trim( $user );
		$pwd = trim( $pwd );

		if ( is_null( $ssl ) ) {
			$ssl = $this->config( 'imap_ssl' ) || '993' == $port;
		}

		$err_no = null;
		$err_str = '';
		$c_count = 0;

		// Specify transport
		$transport = $this->get_transport( $port, $ssl );

		if ( ! defined( 'COMMAND_PREFIX_WIDTH' ) ) {
			define( 'COMMAND_PREFIX_WIDTH', 4 );
		}

		$ip = gethostbyname( $url );

		if ( $ip === $url ) {
			throw new Exception( 'Failed to resolve IP address for ' . $url );
		}

		$sp = stream_socket_client( "$transport://$ip:$port", $err_no, $err_str );
		
		if ( $err_no ) {
			$mess = $this->h->__( 'Socket error: ' . $err_str );
			trigger_error( $mess );
			throw new Exception( $mess );
		}

		if ( ! is_resource( $sp ) ) {
			if ( 0 === $err_no ) {
				$mess = $this->h->__( 'Failed to initialize socket to ' . $url );
				trigger_error( $mess );
				throw new Exception( $mess );
			}

			$mess = $this->h->__( 'Failed to open socket to ' . $url );
			trigger_error( $mess );
			throw new Exception( $mess );
		}

		stream_set_blocking( $sp, false );

		// read welcome message
		$resp = $this->imap_out( $sp );

		if ( strpos( $resp, '* OK' ) !== 0 ) {
			$mess = 'IMAP server did not respond with ready status';
			trigger_error( $mess );
			throw new Exception( $mess );
		}

		// Ask for capability
		$this->imap_in( $sp, "CAPABILITY", $c_count++ );
		$out = $this->imap_out( $sp );

		if ( false === $out ) {
			throw new Exception( 'Failed to get capabilities list from IMAP server');
		}

		if ( strpos( $out, 'LOGINDISABLED' ) ) {
			throw new Exception( 'IMAP server does not support authentication' );
		}

		// Log in
		if ( strpos( $out, 'AUTH=CRAM-MD5' ) ) {
			$this->imap_in( $sp, 'AUTHENTICATE CRAM-MD5', $c_count++ );
			$out = $this->imap_out( $sp );

			if ( strpos( $out, '+' ) !== false ) {
				$challenge = base64_decode( substr( $out, 2 ) );
				$key = str_pad( $pwd, 64, 0 );

				if ( strlen( $pwd ) > 64 ) {
					$pwd = pack( 'H32', md5( $pwd ) );
				}

				if ( strlen( $pwd ) < 64 ) {
					$pwd = str_pad( $pwd, 64, chr( 0 ) );
				}

				$k_ipad = '';
				$k_opad = '';

				for( $i = 0; $i < 64; $i++ ) {
					$byte = ord($pwd{$i});
					$k_ipad .= chr( $byte ^ 0x36);
					$k_opad .= chr( $byte ^ 0x5C);
				}

				$inner = pack( 'H32', md5 ($k_ipad . $challenge ) );
				$digest = md5( $k_opad . $inner );

				$this->imap_in( $sp, base64_encode( $user . ' ' . $digest ) );
				$out = $this->imap_out( $sp );

				if ( ! $out ) {
					throw new Exception( 'Failed to log in to ICMP server' );
				}
			}

		} elseif ( strpos( $out, 'AUTH=LOGIN' ) ) {
			$this->imap_in( $sp, 'AUTHENTICATE LOGIN', $c_count++ );
			$out = $this->imap_out( $sp );

			if ( strpos( $out, '+' ) !== false ) {
				if ( ! base64_decode( substr( $out, 2 ) ) === 'Username' ) {
					throw new Exception( 'Server did not send username request' );
				}

				$this->imap_in( $sp, base64_encode( "$user" ) );
				$out = $this->imap_out( $sp );

				if ( ! base64_decode( substr( $out, 2 ) ) === 'Password' ) {
					throw new Exception( 'Server did not send password request' );
				}

				$this->imap_in( $sp, base64_encode( "$pwd" ) );
				$out = $this->imap_out( $sp );

				if ( false === $out ) {
					throw new Exception( 'Failed to log in to ICMP server' );
				}
			}

		} elseif ( strpos( $out, 'AUTH=PLAIN' ) ) {
			$this->imap_in( $sp, 'AUTHENTICATE PLAIN', $c_count++ );
			$out = $this->imap_out( $sp );

			if ( strpos( $out, '+' ) !== false ) {
				$this->imap_in( $sp, base64_encode( "$user\0$user\0$pwd" ) );
				$out = $this->imap_out( $sp );

				if ( ! $out ) {
					throw new Exception( 'Failed to log in to ICMP server' );
				}
			}
		}

		// Perform some specific stuff
		foreach( $commands as $command ) {
			if ( is_callable( $command ) ) {
				call_user_func_array( $command, array( $sp, &$c_count ) );
			}
		}

		$this->imap_in( $sp, 'LOGOUT', $c_count++ );
		$out = $this->imap_out( $sp );

		fclose( $sp );
	}

	/**
	 * Returns transport depending on configuration and port number
	 * @param string $port  Port number
	 * @param boolean $ssl Flag to use secure connection
	 * @return string
	 */
	protected function get_transport( $port, $ssl ) {
		$transport = 'tcp';
		$transports = stream_get_transports();

		if ( $ssl ) {
			if ( in_array( 'tls', $transports ) ) {
				$transport = 'tls';

			} elseif ( in_array( 'ssl', $transport ) ) {
				$transport = 'ssl';

			} else {
				throw new Exception( 'System does not provide secure transports' );
			}

		} elseif ( in_array( 'tcp', $transports ) ) {
			$transport = 'tcp';

		} else {
			throw new Exception( 'System does not provide suitable transport' );
		}

		return $transport;
	}

	/**
	 * Tell to IMAP
	 * @param resource $socket Socket pointer
	 * @param string $command Command
	 * @param int|null $command_count Sequential command number
	 * @return void
	 * @throws Adk_Exception on error
	 */
	public function imap_in( $socket, $command, $command_count = null ) {
		if ( ! is_null( $command_count ) ) {
			$comand = sprintf( 'A%0' . ( COMMAND_PREFIX_WIDTH ) . 's %s%s', $command_count, $command, "\r\n" );

		} else {
			$comand = sprintf( '%s%s', $command, "\r\n" );
		}

		if ( function_exists( 'l' ) ) {
			l( '< ' . $comand );
		}

		if( ! is_resource( $socket ) ) {
			$mess = sprintf( 'Socket resource expected, got %s instead', gettype( $socket ) );
			trigger_error( $mess );
			throw new Exception( $mess );
		}

		fwrite( $socket, $comand );
	}

	/**
	 * Listen toIMAP
	 * @param resource $socket Socket pointer
	 * @return string|boolean IMAP response or false on NO response
	 * @throws Adk_Exception on error
	 */
	public function imap_out( $socket ) {
		$out   = false;
		$limit = 300;
		$wait  = 0.05 * 1000000;
		$chunk = 1024;

		if( ! is_resource( $socket ) ) {
			$mess = sprintf( 'Socket resource expected, got %s instead', gettype( $socket ) );
			trigger_error( $mess );
			throw new Exception( $mess );
		}

		while( true ) {
			if ( --$limit <= 0 ) {
				$mess = 'Limit of reads from socket was exceeded';
				trigger_error( $mess );
				throw new Exception( $mess );
			}

			$next = fread( $socket, $chunk );

			if ( function_exists( 'l' ) && $next ) {
				l( '> ' . $next );
			}

			if (
				'* OK' === substr( $next, 0, 4 )  ||
				preg_match( '/^A\d{' . COMMAND_PREFIX_WIDTH . '} OK/im', $next ) ||
				substr( $next, 0, 1 ) === '+'
			) {
				$out .= $next;
				break;

			} else if ( preg_match( '/^A\d{' . COMMAND_PREFIX_WIDTH . '} NO/im', $next ) ) {
				$out = false;
				break;

			} elseif ( preg_match( '/^A\d{' . COMMAND_PREFIX_WIDTH . '} BAD/im', $next ) ) {
				$mess = 'IMAP error: ' . substr( $next , COMMAND_PREFIX_WIDTH + 5 );
				trigger_error( $mess );
				throw new Exception( $mess );
			}

			$out .= $next;

			usleep( $wait );
		}

		return $out;
	}

	/**
	 * Performs blacklisting of bounced emails
	 * @return int Number of processed email addresses
	 */
	public function do_blacklist() {
		$count = 0;

		if ( ! $this->config( 'imap_blacklist' ) ) {
			return;
		}

		$this->imap( array( function( $sp, &$c_count ) use( &$count ) {
			$flags = array();

			$action = $this->config( 'imap_action' );

			if ( self::IMAP_ACTION_NOTING == $action ) {
				$this->imap_in( $sp, 'EXAMINE INBOX', $c_count++ );

			} else {
				$this->imap_in( $sp, 'SELECT INBOX', $c_count++ );
			}

			$out = $this->imap_out( $sp );

			// Define permitted permanent flags
			preg_match( '/\[PERMANENTFLAGS\s\((.*?)\)\]/', $out, $m );

			if ( isset( $m[ 1 ] ) ) {
				$flags = explode( ' ', trim( $m[ 1 ] ) );
			}

			if ( $this->config( 'imap_unseen' ) ) {
				$this->imap_in( $sp, 'SEARCH NOT SEEN', $c_count++ );

			} else {
				$this->imap_in( $sp, 'SEARCH ALL', $c_count++ );
			}

			$out = $this->imap_out( $sp );

			// List of matched letters (space separated string)
			$first_line = strstr( $out, "\r", true );
			$first_line = substr( $first_line, 9 );
			$list = explode( ' ', $first_line );

			foreach( $list as $id ) {
				if ( '' === $id ) {
					continue;
				}

				$this->imap_in( $sp, 'FETCH ' . $id .' (body[text])', $c_count++ );
				$out = $this->imap_out( $sp );
				preg_match( '/^Final-Recipient\s*:\s*(?:rfc822;)?\s*(.+)\r\n/im', $out, $m );

				if ( ! isset( $m[ 1 ] ) ) {
					trigger_error( 'Failed to retrieve "To" field. Email address skipped' );
					continue;
				}

				$email = $m[ 1 ];

				$this->q( array(
					'table' => $this->newsletter_subscribers_table,
					'query' => 'update',
					'set'   => array( 'status' => self::SUBSCRIBER_STATUS_BLACKLISTED ),
					'where' => array(
						'field'     => 'email',
						'operation' => '=',
						'value'     => $email,
					),
				) );

				$count += $this->db->countAffected();
				
				if ( self::IMAP_ACTION_SEEN == $action ) {
					if( ! in_array( '\\Seen', $flags ) ) {
						trigger_error( 'Server does not support flag "Seen". Proceeded email won\'t be marked as seen' );
						
					} else {
						$this->imap_in( $sp, 'STORE ' . $id . ' +FLAGS \\Seen', $c_count++ );
						if( ! $this->imap_out( $sp ) ) {
							trigger_error( 'Failed to mark email as "Seen"' );
						}
					}

				} elseif ( self::IMAP_ACTION_DELETE == $action ) {
					if( ! in_array( '\\Deleted', $flags ) ) {
						trigger_error( 'Server does not support flag "Deleted". Proceeded email won\'t be deleted' );
						
					} else {
						$this->imap_in( $sp, 'STORE ' . $id . ' +FLAGS \\Deleted', $c_count++ );
						if( ! $this->imap_out( $sp ) ) {
							trigger_error( 'Failed to mark email as "Deleted"' );
						}
					}
				}
			}

			if ( $action == self::IMAP_ACTION_DELETE ) {
				$this->imap_in( $sp, 'CLOSE', $c_count++ );

				if ( ! $this->imap_out( $sp ) ) {
					trigger_error( 'Failed to perform CLOSE command' );
				}
			} 
		} ) );

		return $count;
	}
}

// if( ! class_exists( 'Adk_Exception' ) ) {

// 	/**
// 	 * Advertikon exception class
// 	 */
// 	class Adk_Exception extends Exception {

// 	}
// }

// if( ! class_exists( 'Adk_Form_Exception' ) ) {

// 	/**
// 	 * Advertikon Form exception class
// 	 */
// 	class Adk_Form_Exception extends Adk_exception {

// 		/**
// 		 * @var String Form field with error
// 		 */
// 		protected $field_name = '';

// 		/**
// 		 * Class constructor
// 		 * @param String $message Error message
// 		 * @param String $field_name Field name with error
// 		 * @return void
// 		 */
// 		public function __construct( $message, $field_name ) {
// 			parent::__construct( $message );
// 			$this->field_name = $field_name;
// 		}

// 		/**
// 		 * Returns form field pertain to error
// 		 * @return String
// 		 */
// 		public function get_field_name() {

// 			return $this->field_name;
// 		}
// 	}
// }

// if ( ! class_exists( 'DB_Result' ) ) {

// 	/**
// 	 * Data base result class
// 	 */
// 	class DB_Result extends ArrayIterator {

// 		protected $data = array();

// 		public function __construct( $data = array() ) {

// 			if ( ! $data ) {
// 				return;
// 			}

// 			if ( is_array( $data ) && is_array( current( $data ) ) ) {
// 				$this->data = $data;

// 			} else {
// 				array_push( $this->data, (array)$data );
// 			}
// 		}

// 		// ArrayIterator //

// 		public function append( $value ) {
// 			array_push( $this->data, $value );
// 		}

// 		public function getArrayCopy() {
// 			return $this->data;
// 		}

// 		// ArrayAccess //

// 		public function offsetExists ( $offset ) {
// 			$current = $this->current();

// 			return isset( $current[ $offset ] );
// 		}

// 		public function offsetGet ( $offset ) {
// 			$current = $this->current();

// 			return $current[ $offset ];
// 		}

// 		public function offsetSet ( $offset , $value ) {
// 			if ( is_null( $offset ) ) {
// 				array_push( $this->data, $value );

// 			} else {
// 				$current = &$this->data[ key( $this->data ) ];

// 				if ( ( is_string( $offset ) || is_numeric( $offset ) ) && isset( $current[ $offset ] ) ) {
// 					$current[ $offset ] = $value;

// 				} else {
// 					array_push( $current, $value );
// 				}

// 				unset( $current );
// 			}
// 		}

// 		public function offsetUnset ( $offset ) {
// 				$current = &$this->data[ key( $this->data ) ];

// 				if ( isset( $current[ $offset ] ) ) {
// 					unset( $current[ $offset ] );
// 				}
// 		}

// 		// SeekableIterator //

// 		public function seek( $key ) {
// 			if ( ! array_key_exists( $key, $this->data ) ) {
// 				throw new OutOfBoundsException( 'error' );
// 			}

// 			reset( $this->data );
// 			while ( key( $this->data ) != $key) {
// 				next( $this->data );
// 			}
// 		}

// 		// Iterator //

// 		public function current() {
// 			return current( $this->data );
// 		}

// 		public function key() {
// 			return key( $this->data );
// 		}

// 		public function next() {
// 			next( $this->data );
// 		}

// 		public function rewind() {
// 			reset( $this->data );
// 		}

// 		public function valid() {
// 			return ! is_null( key( $this->data ) );
// 		}

// 		// Countable //

// 		public function count() {
// 			return count( $this->data );
// 		}

// 		// Seriazible //

// 		public function serialize() {

// 		}

// 		public function unserialize( $str ) {

// 		}
// 	}
// }

// if ( ! class_exists( 'Adk_Task' ) ) {
// 	class Adk_Task {

// 		public $task = '';
// 		public $schedule = '';
// 		public $status = '';
// 		public $last_run = '';
// 		public $p_id = '';
// 		public $threshold = '';
// 		public $h = '';
// 		private $tasks = '';
// 		public $id = '';
// 		public $table = 'adk_task';

// 		public function __construct( $h ) {
// 			$this->h = $h;
// 		}

// 		/**
// 		 * Initializes object
// 		 * @return void
// 		 */
// 		public function init() {
// 			if ( ! $this->tasks ) {
// 				$this->tasks = $this->h->run_query( array(
// 					'table' => $this->table,
// 					'query' => 'select',
// 					'where' => array(
// 						'field'     => 'status',
// 						'operation' => '<>',
// 						'value'     => 1,
// 					), 
// 				) );
// 			}
// 		}

// 		/**
// 		 * Installs task manager into system
// 		 * @return object
// 		 */
// 		public function install() {
// 			$this->h->db->query( "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $this->table . "`
// 			(`id` INT UNSIGNED AUTO_INCREMENT KEY,
// 			 `task` TEXT,
// 			 `schedule` VARCHAR(20),
// 			 `status` TINYINT UNSIGNED DEFAULT 0,
// 			 `last_run` DATETIME,
// 			 `p_id` VARCHAR(50),
// 			 `threshold` INT UNSIGNED
// 			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin" );

// 			$action = $this->h->get_store_url() . 'index.php?route=' . $this->h->type . '/' . $this->h->code . '/amend_task';
// 			$schedule = '*/10 * * * *';
// 			$threshold = 10;

// 			if ( ! $this->task_exists( $action, $schedule, $threshold ) ) {
// 				$this->add_task( $action, $schedule, $threshold );
// 			}

// 			return $this;
// 		}

// 		/**
// 		 * Removes task manager from the system
// 		 * @return object
// 		 */
// 		public function uninstall() {
// 			$this->h->db->query( "DROP TABLE IF EXISTS `" . DB_PREFIX . $this->table . "`" );

// 			return $this;
// 		}

// 		/**
// 		 * Adds cron task
// 		 * @param string $task Task action 
// 		 * @param string $schedule Schedule structure
// 		 * @param int $threshold Staleness threshold in seconds
// 		 * @return object
// 		 */
// 		public function add_task( $task, $schedule, $threshold ) {
// 			$this->h->run_query( array(
// 				'table' => $this->table,
// 				'query' => 'insert',
// 				'values' => array(
// 					'task'      => '"' . $task . '"',
// 					'schedule'  => $schedule,
// 					'threshold' => $threshold,
// 				),
// 			) );

// 			return $this;
// 		}

// 		/**
// 		 * Checks whether task is exists
// 		 * @param string $action Task's task action 
// 		 * @param string $schedule Task's schedule
// 		 * @param int $threshold Task's threshold 
// 		 * @return boolean
// 		 */
// 		public function task_exists( $action, $schedule, $threshold ) {
// 			$query = $this->h->run_query( array(
// 				'table'    => $this->table,
// 				'query'    => 'select',
// 				'function' => 'count(*)',
// 				'where'    => array(
// 					array(
// 						'field'     => 'task',
// 						'operation' => '=',
// 						'value'     => '"' . $action . '"',
// 					),
// 					array(
// 						'field'     => 'schedule',
// 						'operation' => '=',
// 						'value'     => $schedule,
// 					),
// 					array(
// 						'field'     => 'threshold',
// 						'operation' => '=',
// 						'value'     => $threshold,
// 					),
// 				),
// 			) );

// 			return (boolean)$query['COUNT(*)'];
// 		}

// 		/**
// 		 * Fetches new task from queue
// 		 * @return boolean Operation result
// 		 */
// 		public function fetch_new() {
// 			$this->init();

// 			if ( $this->task ) {
// 				$this->reset();
// 				$this->tasks->next();
// 			}

// 			while (  $this->tasks->valid() && ! $this->is_scheduled() ) {
// 				$this->tasks->next();
// 			}

// 			if ( $this->tasks->valid() ) {
// 				$task = $this->tasks->current();

// 				$this->task = $task['task'];
// 				$this->schedule = $task['schedule'];
// 				$this->status = $task['status'];
// 				$this->last_run = $task['last_run'];
// 				$this->p_id = $task['p_id'];
// 				$this->threshold = $task['threshold'];
// 				$this->id = $task['id'];

// 				return true;
// 			}

// 			return false;
// 		}

// 		/**
// 		 * Resets task
// 		 * @return void
// 		 */
// 		public function reset() {
// 			$this->task = '';
// 			$this->schedule = '';
// 			$this->status = '';
// 			$this->last_run = '';
// 			$this->p_id = '';
// 			$this->threshold = '';
// 			$this->id = '';
// 		}

// 		/**
// 		 * Checks whether task is scheduled to run NOW
// 		 * @return boolean
// 		 */
// 		public function is_scheduled( $schedule = null ) {
// 			if ( is_null( $schedule ) ) {
// 				$task = $this->tasks->current();
// 				$schedule = $task['schedule'];
// 			}

// 			$date = new DateTime();
// 			$parts = explode( ' ', $schedule );

// 			if ( ! isset( $parts[ 4 ] ) ) {
// 				trigger_error( sprintf( 'Task schedule: invalid schedule format: "%s"', $schedule ) );
// 				return false;
// 			}

// 			return $this->is_min( $parts[0], $date ) &&
// 					$this->is_hour( $parts[1], $date ) &&
// 					$this->is_month( $parts[3], $date ) &&
// 					( $this->is_day( $parts[2], $date ) ||
// 					$this->is_week_day( $parts[4], $date ) );
// 		}

// 		/**
// 		 * Checks minute part of task schedule
// 		 * @param string $min Minutes part of schedule
// 		 * @param object $date DateTime object
// 		 * @return boolean
// 		 */
// 		public function is_min( $min, $date ) {
// 			try {

// 				if ( false === ( $parts = $this->parse_part( $min ) ) ) {
// 					$this->h->exception( 'error' );
// 				}

// 				if ( '*' === $parts['from'] ) {
// 					return true;
// 				}

// 				$min = (int)$date->format( 'i' );

// 				if ( $parts['from'] < 0 || $parts['from'] > 59 ) {
// 					$this->h->exception( 'error' );
// 				}

// 				if ( $parts['to'] < 0 || $parts['to'] > 59 ) {
// 					$this->h->exception( 'error' );
// 				}

// 				if ( $min < $parts['from'] || $min > $parts['to'] || 0 !== $min % $parts['divider'] ) {
// 					return false;
// 				}

// 			} catch ( Adk_Exception $e ) {
// 				trigger_error( 'Task schedule: invalid format of schedule\'s minutes part' );
// 				return false;
// 			}

// 			return true;
// 		}

// 		/**
// 		 * Checks hour's part of task schedule
// 		 * @param string $min Hour's part of schedule
// 		 * @param object $date DateTime object
// 		 * @return boolean
// 		 */
// 		public function is_hour( $hour, $date ) {
// 			try {

// 				if ( false === ( $parts = $this->parse_part( $hour ) ) ) {
// 					$this->h->exception( 'error' );
// 				}

// 				if ( '*' === $parts['from'] ) {
// 					return true;
// 				}

// 				$min = (int)$date->format( 'H' );

// 				if ( $parts['from'] < 0 || $parts['from'] > 23 ) {
// 					$this->h->exception( 'error' );
// 				}

// 				if ( $parts['to'] < 0 || $parts['to'] > 23 ) {
// 					$this->h->exception( 'error' );
// 				}

// 				if ( $hour < $parts['from'] || $hour > $parts['to'] || 0 !== $hour % $parts['divider'] ) {
// 					return false;
// 				}

// 			} catch ( Adk_Exception $e ) {
// 				trigger_error( 'Task schedule: invalid format of schedule\'s hours part' );
// 				return false;
// 			}

// 			return true;
// 		}

// 		/**
// 		 * Checks day's part of task schedule
// 		 * @param string $min Day's part of schedule
// 		 * @param object $date DateTime object
// 		 * @return boolean
// 		 */
// 		public function is_day( $day, $date ) {
// 			try {

// 				if ( false === ( $parts = $this->parse_part( $day ) ) ) {
// 					$this->h->exception( 'error' );
// 				}

// 				if ( '*' === $parts['from'] ) {
// 					return true;
// 				}

// 				$day = (int)$date->format( 'd' );

// 				if ( $parts['from'] < 1 || $parts['from'] > 31 ) {
// 					$this->h->exception( 'error' );
// 				}

// 				if ( $parts['to'] < 1 || $parts['to'] > 31 ) {
// 					$this->h->exception( 'error' );
// 				}

// 				if ( $day < $parts['from'] || $day > $parts['to'] || 0 !== $day % $parts['divider'] ) {
// 					return false;
// 				}

// 			} catch ( Adk_Exception $e ) {
// 				trigger_error( 'Task schedule: invalid format of schedule\'s day part' );
// 				return false;
// 			}

// 			return true;
// 		}

// 		/**
// 		 * Checks month's part of task schedule
// 		 * @param string $min Month's part of schedule
// 		 * @param object $date DateTime object
// 		 * @return boolean
// 		 */
// 		public function is_month( $month, $date ) {
// 			try {

// 				if ( false === ( $parts = $this->parse_part( $month ) ) ) {
// 					$this->h->exception( 'error' );
// 				}

// 				if ( '*' === $parts['from'] ) {
// 					return true;
// 				}

// 				$month = (int)$date->format( 'm' );

// 				if ( $parts['from'] < 1 || $parts['from'] > 12 ) {
// 					$this->h->exception( 'error' );
// 				}

// 				if ( $parts['to'] < 1 || $parts['to'] > 12 ) {
// 					$this->h->exception( 'error' );
// 				}

// 				if ( $month < $parts['from'] || $month > $parts['to'] || 0 !== $month % $parts['divider'] ) {
// 					return false;
// 				}

// 			} catch ( Adk_Exception $e ) {
// 				trigger_error( 'Task schedule: invalid format of schedule\'s month part' );
// 				return false;
// 			}

// 			return true;
// 		}

// 		/**
// 		 * Checks minute part of task schedule
// 		 * @param string $min Minutes part of schedule
// 		 * @param object $date DateTime object
// 		 * @return boolean
// 		 */
// 		public function is_week_day( $day, $date ) {
// 			try {

// 				if ( false === ( $parts = $this->parse_part( $day ) ) ) {
// 					$this->h->exception( 'error' );
// 				}

// 				if ( '*' === $parts['from'] ) {
// 					return true;
// 				}

// 				// 1 though 7
// 				$day = (int)$date->format( 'N' );

// 				if ( $parts['from'] < 0 || $parts['from'] > 7 ) {
// 					$this->h->exception( 'error' );
// 				}

// 				if ( $parts['to'] < 0 || $parts['to'] > 7 ) {
// 					$this->h->exception( 'error' );
// 				}

// 				if ( 0 === $parts['from'] ) {
// 					$parts['from'] = 7;
// 				}

// 				if ( 0 === $parts['to'] ) {
// 					$parts['to'] = 7;
// 				}

// 				if ( $day < $parts['from'] || $day > $parts['to'] || 0 !== $day % $parts['divider'] ) {
// 					return false;
// 				}

// 			} catch ( Adk_Exception $e ) {
// 				trigger_error( 'Task schedule: invalid format of schedule\'s week\'s day part' );
// 				return false;
// 			}

// 			return true;
// 		}

// 		/**
// 		 * Parses schedule's parts
// 		 * @param strong $part Schedule's part
// 		 * @return array|false
// 		 */
// 		public function parse_part( $part ) {
// 			if ( ! preg_match( '/(\*|\d+)(?:\s*-\s*(\d+))?(?:\s*\/\s*(\d+))?/', $part, $m ) ) {
// 				return false;
// 			}

// 			return array(
// 				'from'    => '*' === $m[1] ? '*' : (int)$m[1],
// 				'to'      => isset( $m[2] ) ? (int)$m[2] : (int)$m[1],
// 				'divider' => isset( $m[3] ) ? (int)$m[3] : 1,
// 			);
// 		}

// 		/**
// 		 * Removes handed tasks
// 		 * @return void
// 		 */
// 		public function amend_task() {
// 			$this->h->db->query( "UPDATE `" . DB_PREFIX . $this->table . "` set `status` = 0 WHERE `status` = 1 AND DATE_ADD( `last_run`,INTERVAL `threshold` SECOND ) < NOW() " );
// 		}

// 		/**
// 		 * Marks task as running
// 		 * @param int $id Task ID
// 		 * @return boolean Operation status
// 		 */
// 		public function run_task( $id = null ) {
// 			if ( is_null( $id ) ) {
// 				if ( $this->id ) {
// 					$id = $this->id;
					
// 				} else {
// 					trigger_error( 'Task schedule: failed to run task - task ID is missing' );
// 					return false;
// 				}
// 			}

// 			$result = $this->h->run_query( array(
// 				'table' => $this->table,
// 				'query' => 'update',
// 				'set' => array(
// 					'last_run' => date( 'c' ),
// 					'status'   => 1,
// 				),
// 				'where' => array(
// 					'field'     => 'id',
// 					'operation' => '=',
// 					'value'     => $id,
// 				),
// 			) );

// 			return $result;
// 		}

// 		/**
// 		 * Marks task as stopped
// 		 * @param int $id Task ID
// 		 * @return boolean Operation status
// 		 */
// 		public function stop_task( $id = null ) {
// 			if ( is_null( $id ) ) {
// 				if ( $this->id ) {
// 					$id = $this->id;
					
// 				} else {
// 					trigger_error( 'Task schedule: failed to stop task - task ID is missing' );
// 					return false;
// 				}
// 			}

// 			$result = $this->h->run_query( array(
// 				'table' => $this->table,
// 				'query' => 'update',
// 				'set' => array(
// 					'status'   => 0,
// 				),
// 				'where' => array(
// 					'field'     => 'id',
// 					'operation' => '=',
// 					'value'     => $id,
// 				),
// 			) );

// 			return $result;
// 		}
// 	}
// }

// if ( ! class_exists( 'Adk_Url' ) ) {
// 	class Adk_Url {

// 		private $scheme = '';
// 		private $host = '';
// 		private $port = '';
// 		private $path = '';
// 		private $query = array();
// 		private $fragment = '';

// 		public function __construct( $url = null ) {
// 			if ( $url ) {
// 				$this->parse( $url );
// 			}
// 		}

// 		public function reset() {
// 			$this->scheme = '';
// 			$this->host = '';
// 			$this->port = '';
// 			$this->path = '';
// 			$this->query = array();
// 			$this->fragment = '';

// 			return $this;
// 		}

// 		/**
// 		 * Parses URL
// 		 * @param String $url 
// 		 * @return Array
// 		 */
// 		public function parse( $url ) {

// 			$this->reset();

// 			if( gettype( $url ) !== 'string' ) {
// 				trigger_error( sprintf( '%s: URL need to be a string, %s given instead', __CLASS__, gettype( $url ) ) );
// 				return;
// 			}

// 			if( ! $url ) {
// 				trigger_error( sprintf( '%s: URL may not be an empty string', __CLASS__ ) );
// 				return;
// 			}

// 			$preg_str = '%' .
// 						'(?:(^[^/:]*?)(?=://))?' . // Scheme
// 						'(?::?/{2})?' .
// 						'([^/:?]+)?' . // Host
// 						':?' .
// 						'(?:(?<=:)(\d+))?' . // Port
// 						'([^?]+)?' . // Path
// 						'\??' .
// 						'(?:(?<=\?)([^#]+))?' . // Query
// 						'#?' .
// 						'(?:(?<=#)(.*))?' . // Fragment
// 						'%';

// 			if( preg_match( $preg_str, $url, $m ) ) {

// 				if( isset( $m[1] ) ) {
// 					$this->scheme = $m[1];
// 				}

// 				if( isset( $m[2] ) ) {
// 					$this->host = $m[2];
// 				}

// 				if( isset( $m[3] ) ) {
// 					$this->port = $m[3];
// 				}

// 				if( ! empty( $m[4] ) ) {
// 					$this->path = $m[4];

// 				} else {
// 					$this->path = '/';
// 				}
// 				if( isset( $m[5] ) ) {

// 					foreach( explode( '&', str_replace( '&amp;', '&', $m[5] ) ) as $part ) {
// 						$parts = explode( '=', $part );

// 						if ( empty( $parts[0] ) || ! isset( $parts[1] ) ) {
// 							trigger_error( sprintf( '%s: URL query part "%s" is invalid', __CLASS__, $part ) );
// 							continue;
// 						}

// 						$this->query[ $parts[0] ] = $parts[1];	
// 					}
// 				}

// 				if( isset( $m[6] ) ) {
// 					$this->fragment = $m[6];
// 				}
// 			}

// 			return $this;
// 		}

// 		/**
// 		 * Normalizes URL
// 		 * @param String|Array $url URL to be normalized
// 		 * @return String
// 		 */
// 		public function to_string() {

// 			$ret = 
// 			( ! $this->scheme ? '//' : $this->scheme ) . '://' .
// 			( ! $this->host ? $_SERVER['SERVER_NAME'] : $this->host ) .
// 			( ! $this->port ? '' : ':' . $this->port ) .
// 			$this->path .
// 			( ! $this->query ? '' : '?' . $this->query_to_string() ) .
// 			( ! $this->fragment ? '' : '#' . $this->fragment );

// 			return $ret;
// 		}

// 		/**
// 		 * Returns query part as a string 
// 		 * @return string
// 		 */
// 		public function query_to_string() {
// 			$p = array();

// 			foreach( $this->query as $k => $v ) {
// 				$p[] = $k . '=' . $v;
// 			}

// 			return implode( '&', $p );
// 		}

// 		/**
// 		 * Adds query prameter
// 		 * @param string $name Parameter name
// 		 * @param strong $value Parameter value
// 		 * @return object
// 		 */
// 		public function add_query( $name, $value ) {
// 			if ( ! is_string( $name ) ) {
// 				trigger_error( sprintf( '%s: name of query parameter need to be a string, %s given instead', __CLASS__, gettype( $name ) ) );
// 				return $this;
// 			}

// 			if ( ! is_string( $value ) ) {
// 				trigger_error( sprintf( '%s: value of query parameter need to be a string, %s given instead', __CLASS__, gettype( $value ) ) );
// 				return $this;
// 			}

// 			$this->query[ $name ] = $value;

// 			return $this;
// 		}

// 		/**
// 		 * Returns query part by name
// 		 * @param string $name Query's part name 
// 		 * @return string
// 		 */
// 		public function get_query( $name = null ) {
// 			if ( is_null( $name ) ) {
// 				return $this->query;
// 			}

// 			if ( isset( $this->query[ $name ] ) ) {
// 				return $this->query[ $name ];
// 			}

// 			return null;
// 		}
// 	}
// }

// if ( ! class_exists( 'Adk_Img') ) {
// class Adk_Image {
// 	private $file;
// 	private $image;
// 	private $width;
// 	private $height;
// 	private $bits;
// 	private $mime;

// 	public function __construct($file) {
// 		if (file_exists($file)) {
// 			$this->file = $file;

// 			$info = getimagesize($file);

// 			$this->width  = $info[0];
// 			$this->height = $info[1];
// 			$this->bits = isset($info['bits']) ? $info['bits'] : '';
// 			$this->mime = isset($info['mime']) ? $info['mime'] : '';

// 			if ($this->mime == 'image/gif') {
// 				$this->image = imagecreatefromgif($file);
// 			} elseif ($this->mime == 'image/png') {
// 				$this->image = imagecreatefrompng($file);
// 			} elseif ($this->mime == 'image/jpeg') {
// 				$this->image = imagecreatefromjpeg($file);
// 			}
// 		} else {
// 			exit('Error: Could not load image ' . $file . '!');
// 		}
// 	}

// 	public function getFile() {
// 		return $this->file;
// 	}

// 	public function getImage() {
// 		return $this->image;
// 	}

// 	public function getWidth() {
// 		return $this->width;
// 	}

// 	public function getHeight() {
// 		return $this->height;
// 	}

// 	public function getBits() {
// 		return $this->bits;
// 	}

// 	public function getMime() {
// 		return $this->mime;
// 	}

// 	public function save($file, $quality = 90) {
// 		$info = pathinfo($file);

// 		$extension = strtolower($info['extension']);

// 		if (is_resource($this->image)) {
// 			if ($extension == 'jpeg' || $extension == 'jpg') {
// 				imagejpeg($this->image, $file, $quality);
// 			} elseif ($extension == 'png') {
// 				imagepng($this->image, $file);
// 			} elseif ($extension == 'gif') {
// 				imagegif($this->image, $file);
// 			}

// 			imagedestroy($this->image);
// 		}
// 	}

// 	public function resize($width = 0, $height = 0, $default = '') {
// 		if (!$this->width || !$this->height) {
// 			return;
// 		}

// 		$xpos = 0;
// 		$ypos = 0;
// 		$scale = 1;

// 		$scale_w = $width / $this->width;
// 		$scale_h = $height / $this->height;

// 		if ($default == 'w') {
// 			$scale = $scale_w;
// 		} elseif ($default == 'h') {
// 			$scale = $scale_h;
// 		} else {
// 			$scale = min($scale_w, $scale_h);
// 		}

// 		if ($scale == 1 && $scale_h == $scale_w && $this->mime != 'image/png') {
// 			return;
// 		}

// 		$new_width = (int)($this->width * $scale);
// 		$new_height = (int)($this->height * $scale);
// 		$xpos = (int)(($width - $new_width) / 2);
// 		$ypos = (int)(($height - $new_height) / 2);

// 		$image_old = $this->image;
// 		$this->image = imagecreatetruecolor($width, $height);

// 		if ($this->mime == 'image/png') {
// 			imagealphablending($this->image, false);
// 			imagesavealpha($this->image, true);
// 			$background = imagecolorallocatealpha($this->image, 255, 255, 255, 127);
// 			imagecolortransparent($this->image, $background);
// 		} else {
// 			$background = imagecolorallocate($this->image, 255, 255, 255);
// 		}

// 		imagefilledrectangle($this->image, 0, 0, $width, $height, $background);

// 		imagecopyresampled($this->image, $image_old, $xpos, $ypos, 0, 0, $new_width, $new_height, $this->width, $this->height);
// 		imagedestroy($image_old);

// 		$this->width = $width;
// 		$this->height = $height;
// 	}

// 	public function watermark($watermark, $position = 'bottomright') {
// 		switch($position) {
// 			case 'topleft':
// 				$watermark_pos_x = 0;
// 				$watermark_pos_y = 0;
// 				break;
// 			case 'topright':
// 				$watermark_pos_x = $this->width - $watermark->getWidth();
// 				$watermark_pos_y = 0;
// 				break;
// 			case 'bottomleft':
// 				$watermark_pos_x = 0;
// 				$watermark_pos_y = $this->height - $watermark->getHeight();
// 				break;
// 			case 'bottomright':
// 				$watermark_pos_x = $this->width - $watermark->getWidth();
// 				$watermark_pos_y = $this->height - $watermark->getHeight();
// 				break;
// 		}

// 		imagecopy($this->image, $watermark->getImage(), $watermark_pos_x, $watermark_pos_y, 0, 0, $watermark->getWidth(), $watermark->getHeight());

// 		imagedestroy($watermark->getImage());
// 	}

// 	public function crop($top_x, $top_y, $bottom_x, $bottom_y) {
// 		$image_old = $this->image;
// 		$this->image = imagecreatetruecolor($bottom_x - $top_x, $bottom_y - $top_y);

// 		imagecopy($this->image, $image_old, 0, 0, $top_x, $top_y, $this->width, $this->height);
// 		imagedestroy($image_old);

// 		$this->width = $bottom_x - $top_x;
// 		$this->height = $bottom_y - $top_y;
// 	}

// 	public function rotate($degree, $color = 'FFFFFF') {
// 		$rgb = $this->html2rgb($color);

// 		$this->image = imagerotate($this->image, $degree, imagecolorallocate($this->image, $rgb[0], $rgb[1], $rgb[2]));

// 		$this->width = imagesx($this->image);
// 		$this->height = imagesy($this->image);
// 	}

// 	private function filter() {
//         $args = func_get_args();

//         call_user_func_array('imagefilter', $args);
// 	}

// 	private function text($text, $x = 0, $y = 0, $size = 5, $color = '000000') {
// 		$rgb = $this->html2rgb($color);

// 		imagestring($this->image, $size, $x, $y, $text, imagecolorallocate($this->image, $rgb[0], $rgb[1], $rgb[2]));
// 	}

// 	private function merge($merge, $x = 0, $y = 0, $opacity = 100) {
// 		imagecopymerge($this->image, $merge->getImage(), $x, $y, 0, 0, $merge->getWidth(), $merge->getHeight(), $opacity);
// 	}

// 	private function html2rgb($color) {
// 		if ($color[0] == '#') {
// 			$color = substr($color, 1);
// 		}

// 		if (strlen($color) == 6) {
// 			list($r, $g, $b) = array($color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5]);
// 		} elseif (strlen($color) == 3) {
// 			list($r, $g, $b) = array($color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2]);
// 		} else {
// 			return false;
// 		}

// 		$r = hexdec($r);
// 		$g = hexdec($g);
// 		$b = hexdec($b);

// 		return array($r, $g, $b);
// 	}
// }

// }
