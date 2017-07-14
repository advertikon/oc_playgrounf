<?php
/**
 * @package Mail template manager
 * @author Advertikon
 * @version 1.0.8
 */

namespace Advertikon\Mail;

use \Advertikon\Url;
use \Advertikon\Fs;
use \Advertikon\DB_Result;
use \Advertikon\Exception;

class Advertikon extends \Advertikon\Advertikon {

	/**
	 * @var String Extension type
	 */
	public $type = 'module';

	/**
	 * @var String Extension code
	 */
	public $code = 'adk_mail';
	public static $c = __NAMESPACE__;

	/**
	 * @var Global registry object
	 */
	public $registry = null;

	/**
	 * @var String Prefix to distinguish error depend on context
	 */
	public $error_prefix = array( 'Mail');

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
	protected $email_log = '';
	protected $attached_img = array();
	public $adk_template = '';
	public $adk_newsletter_id = null;
	public $adk_subscriber_email = null;
	public $adk_newsletter_name = null;
	public $adk_sample_order = null;
	public $caller_args = null;
	public $adk_subscriber_name = null;
	public $archive_file = null;
	public $tracking_visit_id = null;
	public $private_template = false;
	public $tracking_id = null;

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

	/**
	 * @see Advertikon\Advertikon::get_version()
	 */
	static public function get_version() {
		return parent::get_version();
	}

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

		$this->data_dir            = $this->data_dir . 'mail/';
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
	 * Sends email message
	 * @param array $data Email data 
	 * @return boolean
	 */
	public function send_email( $data ) {
		$mail = new \Mail();
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

		if ( ! class_exists( 'html2text' ) ) {
			require_once( __DIR__ . '/html2text.php' );
		}

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
			$this->get_store_url( true ) . '?route=' . $this->type . '/' . $this->code . '/track',
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
		$order = ADK( __NAMESPACE__ )->get_from_cache( 'old_order' ); $this->log->write( $order );
		$store_id = null;

		if ( $order ) {
			$store_id = $order['store_id'];

		} else {
			$store_id = $this->config->get( 'config_store_id' );
		}

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
		$order = ADK( __NAMESPACE__ )->get_from_cache( 'old_order' );

		if ( $order ) {
			$config_lang = $order['language_id'];

		} else {
			$config_lang = $this->config->get( 'config_language_id' );
		}

		foreach( $this->get_languages() as $language ) {
			if( $language['language_id'] === $config_lang ) {
				$language_id = $language['code'];
				break;
			}
		}

		return $language_id;
	}

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

			// In case profile was deleted
			if ( ! is_null( $profile_id ) && ! $this->get_profile( $profile_id ) ) {
				$profile_id = null;
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
	 * Returns template content part by its name
	 * @param array $template Target template 
	 * @param string $content_name Content name to search for
	 * @param int $store_id Store ID
	 * @param string $lang_id Language code 
	 * @return string
	 */
	public function get_template_content( $template, $content_name, $store_id, $lang_id ) {
		$ret = '';
		$template = json_decode( json_encode( $template ), 1 );
		$this->adk_template = $template;
		list( $store_id, $lang_id ) = $this->get_template_store_lang( $template, $store_id, $lang_id );
		
		$shortcode = new Shortcode();

		if( isset( $template['data'][ $store_id ]['lang'][ $lang_id ]['content'][ $content_name ] ) ) {
			if( in_array( $content_name, array( 'content', 'subject' ) ) ) {
				$ret = $shortcode->do_shortcode(
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
	 * @param array $template Target template
	 * @param string $data_name Data name to search for
	 * @param int|null $store_id Store ID, optional 
	 * @param int|null $lang_id Language code, optional 
	 * @return string
	 */
	public function get_template_data( $template, $data_name, $store_id = null, $lang_id = null ) {

		$template = json_decode( json_encode( $template ), 1 );

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
		$shortcode = new Shortcode();
		return $shortcode->do_shortcode( htmlspecialchars_decode( $ret ) );
	}

	/**
	 * Returns template header part's contents
	 * @param array $header Template header part
	 * @return type
	 */
	public function get_header_content( $header ) {
		$ret = isset( $header['text']['content'] ) ? $header['text']['content'] : '';
		$shortcode = new Shortcode();
		return $shortcode->do_shortcode( htmlspecialchars_decode( $ret ) );
	}

	/**
	 * Returns template footer part's content
	 * @param array $footer Template footer part
	 * @return string
	 */
	public function get_footer_content( $footer ) {
		$ret = isset( $footer['text']['content'] ) ? $footer['text']['content'] : '';
		$shortcode = new Shortcode();
		return $shortcode->do_shortcode( htmlspecialchars_decode( $ret ) );
	}

	/**
	 * Returns template bottom part's content
	 * @param array $footer Template bottom part
	 * @return string
	 */
	public function get_bottom_content( $bottom ) {
		$ret = isset( $bottom['text']['content'] ) ? $bottom['text']['content'] : '';
		$shortcode = new Shortcode();
		return $shortcode->do_shortcode( htmlspecialchars_decode( $ret ) );
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
		$s = new Shortcode();
		return $s->brace_shortcode_name( $shortcode['category'] . '(' . $shortcode['shortcode_id'] . ')' );
	}

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
					WHERE `level` = 'store' AND `id` = $store_id  AND `enabled` = 1 LIMIT 1) as store,
				( SELECT `profile_id` FROM `" . DB_PREFIX . $this->profile_mapping_table . "`
					WHERE `level` = 'lang' AND `id` = '$lang_code' AND `enabled` = 1 LIMIT 1) as lang,
				( SELECT `profile_id` FROM `" . DB_PREFIX . $this->profile_mapping_table . "`
					WHERE `level` = 'template' AND `id` = $template_id AND `enabled` = 1 LIMIT 1 ) as template"
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

			// If template is disabled we do not use its configurations
			$ret = $configs['template'][ $template_id ][ $name ] && $configs['template'][ $template_id ]['enabled'];

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
		$ret = array();

		if( ! empty( $this->session->data['adk_mail']['temp'][ $object][ $id ] ) ) {
			$pointer =& $this->snapshot_pointer( $id, $object );
			$snapshot = $this->session->data['adk_mail']['temp'][ $object ][ $id ][ $pointer ];
			$ret = $snapshot;
		}

		foreach( $_SESSION as $name => &$entry ) {
			if ( isset( $entry['adk_mail']['temp'][ $object ][ $id ] ) ) {
				$entry['adk_mail']['temp'][ $object ][ $id ] = array( $snapshot );
			}

			if ( isset( $entry['adk_mail']['temp']['pointers'][ $object ][ $id ] ) ) {
				$enrty['adk_mail']['temp']['pointers'][ $object ][ $id ] = 0;
			}
		}

		return $ret;
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
		if ( isset( $this->session->data['adk_mail'] ) ) {
			$this->session->data['adk_mail'] = json_decode( json_encode( $this->session->data['adk_mail'] ), true );
		}

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
		foreach( $_SESSION as &$entry ) {
			if( isset( $entry['adk_mail']['temp'] ) ) {
				unset( $entry['adk_mail']['temp'] );
			}
		}
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
			$url = $this->u()->parse( $img );

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
			$url = $this->u()->catalog_url( true ) . 'image/' . $img;
		}

		return $url;
	}

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

		return $this->u()->catalog_url() . 'image/' . $file;
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
		$this->caller_args = $stack[3]['args'];
		$function = '';
		$hook = array();

		if( isset( $stack[2]['file'] ) ) {
			$caller = strtolower( $this->undo_modifications( $stack[2]['file'] ) );
			$caller = str_replace( DIRECTORY_SEPARATOR, '/', $caller );

		} else {
			$this->email_msg( 'Caller file name is empty. Bounce to default caller', 'warn' );
			return '';
		}

		if( isset( $stack[3]['function'] ) ) {
			$function = strtolower( $stack[3]['function'] );
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

				if ( isset( $this->caller_args[1]['return_status_id'] ) ) {
					$hook[] = $this->caller_args[1]['return_status_id'];
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
				$mail->setReplyTo( $mail->from );
				$mail->from = $mail->to;
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

		$self = $this;

		array_walk( $hooks, function( &$v ) use ( $self ) {
			$v = "'" . $self->db->escape( $v ) . "'";
		} );

		$templates = $this->db->query(
			"SELECT * FROM `" . DB_PREFIX . $this->templates_table . "`
			WHERE `hook` IN (" . implode( ',', $hooks ) . ")
			ORDER BY LENGTH( `hook` ) DESC"
		);

		// Filter disabled templates
		if ( $templates->num_rows ) {
			foreach( $templates->rows as $t ) {
				if ( $this->get_configuration( 'enabled', $t['template_id'] ) ) {
					$t = $this->get_mail_template( $t['template_id'] );

					return $t;
				}
			}
		}

		return false;
	}

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
		$langCode = $this->get_lang();
		$return = false;
		$mail->to = trim( $mail->to );
		$mail->from = trim( $mail->from );
		$mail->sender = trim( $mail->sender );
		$mail_regexp = '/^[A-Za-z0-9._+-]+@[A-Za-z0-9._-]+\.[A-Za-z]{2,4}$/';
		$fs = new Fs();
		$shortcode = new Shortcode();

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
				$to_name = $shortcode->shortcode_customer_full_name();
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
					$reply = $default_reply_to;
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
		$message = \Swift_Message::newInstance( $subject );
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

		if ( function_exists( 'adk_log' ) ) {
			adk_log( $msg );
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
		$data['smtp_server'] = trim( $data['smtp_server'] );

		$prefix = strstr( $data['smtp_server'], '://', true );
		$postfix = strstr( $data['smtp_server'], '://' );
		$postfix = $postfix ? substr( $postfix, 3 ) : $data['smtp_server'];
		$secure = in_array( strtolower( $prefix ), array( 'ssl', 'tls', ) ) ? 'ssl' : null;

		if ( null === $secure && in_array( $data['smtp_port'], array( 465, 587, ) ) ) {
			$secure = 'tls';
		}

		$this->email_msg( 'Host: ' . $postfix );
		$this->email_msg( 'Prefix: ' . $prefix );
		$this->email_msg( 'Port: ' . $data['smtp_port'] );
		$this->email_msg( 'Secure: ' . $secure );

		if( 'smtp' === strtolower( $data['protocol'] ) ) {
			$transport = \Swift_SmtpTransport::newInstance( $postfix, $data['smtp_port'], $secure ) 
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
	public function make_sample_order( $order_id = null ) {
		if ( is_null( $order_id ) ) {
			$id = $this->db->query( "SELECT `order_id` FROM `" . DB_PREFIX . "order` WHERE `order_status_id` > 0 LIMIT 1" );

			if ( $id->num_rows < 1 ) {
				trigger_error( 'Failed to create sample order - there is no orders to use as sample' );
				return;
			}

			$order_id = $id->row['order_id'];
		}

		if( defined( DIR_CATALOG ) ) {
			$this->load->model( 'checkout/order' );
			$order = $this->model_checkout_order->getOrder( $order_id );

		} else {
			$this->load->model( 'sale/order' );
			$order = $this->model_sale_order->getOrder( $order_id );
		}

		$order['order_status'] = $this->get_order_status_name( $order['order_status_id'] );

		if ( defined( 'JSON_PRETTY_PRINT' ) ) {
			file_put_contents( dirname( __FILE__ ) . '/sample_order', json_encode( $order, JSON_PRETTY_PRINT ) );

		} else {
			file_put_contents( dirname( __FILE__ ) . '/sample_order', json_encode( $order ) );
		}
	}

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
			(
				SELECT `price` FROM `" . DB_PREFIX . "product_special`
				WHERE `product_id` = `p`.`product_id`
					AND `customer_group_id` = " . $customer_group_id . "
					AND ( `date_start` = '0000-00-00' OR `date_start` < NOW() )
					AND ( `date_end` = '0000-00-00' OR `date_end` > NOW() )
				ORDER BY `priority` DESC LIMIT 1
			) as special,
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
				$q .= " AND `pc`.`category_id` IN ( SELECT `category_id` FROM `" . DB_PREFIX . "product_to_category` WHERE `product_id` IN(" . implode( ',', (array)$data['product_id'] ) . ") OR `p`.`manufacturer_id` IN ( SELECT manufacturer_id FROM `" . DB_PREFIX . "product` WHERE product_id IN(" . implode( ',', (array)$data['product_id'] ) . " ) ) )";
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
		$shortcode = new Shortcode();

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
			'embedTooltip' => $this->r()->render_popover( $this->__( 'Attachments just appear as files that can be saved to the Desktop if desired. You can make attachment appear inline where possible by mark attachment as "Embed".' ) ),
			'shortcodes'   => $shortcode->get_shortcodes_hint(),
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

		if ( ! $data && $this->has_in_cache( 'newsletter_list' ) ) {
			return $this->get_from_cache( 'newsletter_list' );
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

		$newsletter = new \Advertikon\DB_Result( $this->db->query( $q )->rows );

		if ( !$data ) {
			$this->add_to_cache( 'newsletter_list', $newsletter );
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
	 * Returns email history
	 * @param array $data History filter
	 * @return array
	 */
	public function get_history( $data ) {
		$history = $this->db->query( $this->q()->create_query( $data ) );
		return $history->rows;
	}

	/**
	 * Returns records count from history table
	 * @param array $data Filter data
	 * @return int
	 */
	public function get_history_count( $data ) {
		$ret  = 0;
		$data['field'] = array_merge(
			isset( $data['field'] ) ? $data['field'] : array(),
			array( 'count' => 'count(*)' )
		);

		$query = $this->db->query( $this->q()->create_query( $data ) );

		if ( $query ) {
			$ret = $query->row['count'];
		}

		return $ret;
	}

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
			'filter'      => array(),
		);

		$query = str_replace( array( '%', '_' ), array( '\\%', '\\_' ), $query );

		if ( 'newsletter' === $type ) {
			$query_type = '`h`.`newsletter`, `nl`.`name`';
			$where_type = '`nl`.`name`';

		} else {
			$query_type = '`' . $this->db->escape( $type ) . '`';
			$where_type = $query_type;
		}

		$f_query = $this->db->query(
			"SELECT SQL_CALC_FOUND_ROWS DISTINCT " . $query_type .
			"FROM `" . DB_PREFIX . $this->history_table . "` `h`
			LEFT JOIN `" . DB_PREFIX . $this->newsletter_list_table . "` `nl`
				ON (`h`.`newsletter` = `nl`.`id`)
			WHERE " . $where_type . " LIKE '%" . $this->db->escape( $query ) . "%'
			LIMIT " . (int)$start . ", " . (int)$limit
		);

		if( $f_query->num_rows ) {
			foreach( $f_query->rows as $row ) {
				$data = array();

				$data['id'] = $row[ $type ];

				if ( 'newsletter' === $type ) {
					$data['text'] = $row[ 'name' ];

				} else {
					$data['text'] = $row[ $type ];
				}

				$ret['filter'][] = $data;
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
	 * Rewrites email
	 * @param object $mail Mail bject
	 * @return boolean
	 */
	public function process( $mail ) {
		if(

			// If test email from admin area
			defined( 'TEST_TEMPLATE' ) ||

			// or extension is enabled and there is template's hook for current action
			( $this->config( 'status' ) && false !== ( $template = $this->has_action( $this->get_caller( $mail ) ) ) ) )
		{
			if( defined( 'TEST_TEMPLATE' ) ) {
				$template = $this->get_mail_template( TEST_TEMPLATE );
			}

			return $this->modify_mail( $mail, $template );
		}
	}

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
			$ret = $this->__( 'Undefined' );
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

link.rel  = 'stylesheet';
link.type = 'text/css';
link.href = '{$this->get_store_url( true )}index.php?route={$this->type}/{$this->code}/widget_css&id={$id}';
link.media = 'all';
head.appendChild(link);

s1.async=true;
s1.src='{$this->get_store_url( true )}index.php?route={$this->type}/{$this->code}/widget&id={$id}';
s1.charset='UTF-8';
s1.setAttribute('crossorigin','*');
s0.parentNode.insertBefore(s1,s0);

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
			LEFT JOIN `" . DB_PREFIX . $this->newsletter_to_widget_table . "` n2w
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
		$cur_date = new \DateTime();

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

		return $cur_date->add( new \DateInterval( $offset ) )->format( 'Y-m-d H:i:s' );
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
			! $this->get_configuration( 'track_visit', $this->adk_template['template_id'] ) ||
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
				$url  = $self->u()->parse( $matches['url'] );
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
			$mess = $this->__( 'Socket error: ' . $err_str );
			trigger_error( $mess );
			throw new Exception( $mess );
		}

		if ( ! is_resource( $sp ) ) {
			if ( 0 === $err_no ) {
				$mess = $this->__( 'Failed to initialize socket to ' . $url );
				trigger_error( $mess );
				throw new Exception( $mess );
			}

			$mess = $this->__( 'Failed to open socket to ' . $url );
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

		if ( function_exists( 'adk_log' ) ) {
			adk_log( '< ' . $comand );
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

			if ( function_exists( 'adk_log' ) && $next ) {
				adk_log( '> ' . $next );
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
