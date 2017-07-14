<?php
/**
 * Amin model
 * @package Mail template manager
 * @author Advertikon
 * @version 0.7.2
 */
class ModelModuleAdkMail extends Model {

	protected $model = null;
	protected $a = null;

	/**
	 * Class constructor
	 * @param Object $registry 
	 * @return void
	 */
	public function __construct( $registry ) {
		parent::__construct( $registry );
		$this->a = Advertikon\Mail\Advertikon::instance();
	}

	/**
	 * Adds tables
	 * @return void
	 */
	public function add_tables() {
		$this->db->query( "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $this->a->profiles_table . "`
			(`profile_id` INT UNSIGNED AUTO_INCREMENT KEY,
			 `profile` VARCHAR(255),
			 `fields` VARCHAR(255),
			 `inputs` TEXT,
			 `data` BLOB,
			 `content_fields` TEXT,
			 `description` TEXT,
			 `removable` TINYINT UNSIGNED DEFAULT 1
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin" );

		$this->db->query( "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $this->a->fields_table . "`
			(`field_id` INT UNSIGNED AUTO_INCREMENT KEY,
			 `name` VARCHAR(255),
			 `inputs` TEXT
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin" );

		$this->db->query( "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $this->a->templates_table . "`
			(`template_id` INT UNSIGNED AUTO_INCREMENT KEY,
			 `name` VARCHAR(255),
			 `hook` VARCHAR(255),
			 `path_hook` VARCHAR(255),
			 `description` TEXT,
			 `data` BLOB,
			 `deletable` TINYINT DEFAULT 0
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin" );

		$this->db->query( "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $this->a->shortcodes_table . "`
			(`shortcode_id` INT UNSIGNED AUTO_INCREMENT KEY,
			 `category` VARCHAR(255),
			 `data` BLOB
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin" );

		$this->db->query( "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $this->a->profile_mapping_table . "`
			(`mapping_id` INT UNSIGNED AUTO_INCREMENT KEY,
			 `level` VARCHAR(255),
			 `id` VARCHAR(20),
			 `profile_id` INT UNSIGNED,
			 `enabled` TINYINT UNSIGNED,
			 `log` TINYINT UNSIGNED,
			 `track` TINYINT UNSIGNED,
			 `track_visit` TINYINT UNSIGNED
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin" );

		$this->db->query( "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $this->a->template_mail_table . "`
			(`id` INT UNSIGNED AUTO_INCREMENT KEY,
			 `name` VARCHAR(255) UNIQUE,
			 `subject` VARCHAR(255),
			 `message` TEXT,
			 `cc` VARCHAR(255),
			 `bcc` VARCHAR(255),
			 `attachment` TEXT,
			 `return` VARCHAR(255)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin" );

		$this->db->query( "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $this->a->newsletter_code_table . "`
			(`id` INT UNSIGNED AUTO_INCREMENT KEY,
			 `code` VARCHAR(255),
			 `customer_id` INT UNSIGNED,
			 `email` VARCHAR(255),
			 `newsletter` INT UNSIGNED,
			 `operation` TINYINT UNSIGNED,
			 `added` TIMESTAMP,
			 `expiration` DATETIME
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin" );

		$this->db->query( "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $this->a->history_table . "`
			(`id` INT UNSIGNED AUTO_INCREMENT KEY,
			 `to` VARCHAR(255),
			 `from` VARCHAR(255),
			 `subject` VARCHAR(255),
			 `date_added` DATETIME,
			 `date_viewed` DATETIME,
			 `date_visited` DATETIME,
			 `template` VARCHAR(255),
			 `attachment` TEXT,
			 `status` TINYINT UNSIGNED,
			 `log` TEXT,
			 `tracking_id` VARCHAR(255),
			 `tracking_visit_id` VARCHAR(255),
			 `newsletter` INT UNSIGNED
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin" );

		$this->db->query( "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $this->a->newsletter_list_table . "`
			(`id` INT UNSIGNED AUTO_INCREMENT KEY,
			 `name` VARCHAR(255),
			 `description` TEXT,
			 `status` TINYINT UNSIGNED,
			 `date_added` DATETIME,
			 `widget` INT UNSIGNED,
			 `double_opt_in` TINYINT UNSIGNED,
			 `notify_subscription` TINYINT UNSIGNED,
			 `notify_unsubscription` TINYINT UNSIGNED
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin" );

		$this->db->query( "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $this->a->newsletter_subscribers_table . "`
			(`id` INT UNSIGNED AUTO_INCREMENT KEY,
			 `name` VARCHAR(255),
			 `email` VARCHAR(255),
			 `status` TINYINT UNSIGNED,
			 `date_added` DATETIME,
			 `newsletter` VARCHAR(255),
			 `date_unsubscribe` DATETIME
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin" );

		$this->db->query( "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $this->a->newsletter_widget_table . "`
			(`id` INT UNSIGNED AUTO_INCREMENT KEY,
			 `name` VARCHAR(255),
			 `data` BLOB,
			 `date_added` DATETIME
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin" );

		$this->db->query( "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $this->a->newsletter_to_widget_table . "`
			(`id` INT UNSIGNED AUTO_INCREMENT KEY,
			 `widget_id` INT UNSIGNED UNIQUE,
			 `newsletter_id` INT UNSIGNED
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin" );

		$this->db->query( "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $this->a->queue_table . "`
			(`id` INT UNSIGNED AUTO_INCREMENT KEY,
			 `content` VARCHAR(255),
			 `date_added` DATETIME,
			 `status` TINYINT UNSIGNED DEFAULT 0,
			 `attempt` TINYINT UNSIGNED DEFAULT 0
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin" );

		$this->fill_in_tables();
	}

	/**
	 * Fills in tables with content
	 * @return void
	 */
	public function fill_in_tables() {
		$concise_data = array(
			'name'     => $this->a->__( 'Concise' ),
			'template' => array(
				'width' => array(
					'value' => 100,
					'units' => '%',
				),
				'range' => array(
					'min' => 320,
					'max' => 800,
				),
			),
			'btn'     => array(
				'color' => '#0000ff',
				'text'  => array( 'color' => '#ffffff', ),
			),
			'bg'      => array(
				'color' => '#6b6b6b',
				'img'   => array(
					'img'    => '',
					'repeat' => 'repeat',
				),
			),        
			'header' => array(
				'logo' => array(
					'logo'  => 'catalog/logo.png',
					'align' => 'center',
					'valign' => 'bottom',
					'width' => array(
						'value' => 60,
						'units' => '%',
					),
				),
				'link' => array(
					'color' => '#0000ff',
					'size'  => array(
						'value' => 14,
						'units' => 'px',
					),
				),
				'bg'   => array(
					'color' => '#f77878',
					'img'   => array(
						'img'    => '',
						'repeat' => 'repeat',
					),
				),
				'text' => array(
					'align'   => 'center',
					'valign'  => 'middle',
					'content' => '<b>Mail Templates Manager</b>',
					'color'   => '#ffffff',
					'size'    => array(
						'value' => 30,
						'units' => 'px',
					),
				),
				'height'  => array(
					'value' => 200,
					'units' => 'px',
				),
				'margin' => array(
					'top' => array(
						'size' => array(
							'value' => 10,
							'units' => 'px',
						),
					),
				),
			),
			'content'        => array(
				'link' => array(
					'color' => '#0000ff',
					'size' => array(
						'value' => 14,
						'units' => 'px',
					),
				),
				'bg'   => array(
					'color' => '#ffffff',
					'img'   => array(
						'img'    => '',
						'repeat' => 'repeat',
					),
				),
				'text' => array(
					'align'   => 'left',
					'content' => '',
					'valign'  => 'middle',
					'color'    => '#000000',
					'size'    => array(
						'value' => 16,
						'units' => 'px',
					),
				),
				'margin' => array(
					'top' => array(
						'size' => array(
							'value' => 10,
							'units' => 'px',
						),
					),
				),
			),
			'footer'         => array(
				'link' => array(
					'color' => '#0000ff',
					'size'  => array(
						'value' => 14,
						'units' => 'px',
					),
				),
				'bg'   => array(
					'color' => '#f77878',
					'img'   => array(
						'img'    => '',
						'repeat' => 'repeat',
					),
				),
				'text' => array(
					'align'   => 'left',
					'valign'  => 'middle',
					'content' => '{social(3)}<br>{unsubscribe(Cancel {newsletter_name})}',
					'color'   => '#000000',
					'size'    => array(
						'value' => 14,
						'units' => 'px',
					),
				),
				'height'  => array(
					'value' => 60,
					'units' => 'px',
				),
				'margin' => array(
					'top'    => array(
						'size' => array(
							'value' => 10,
							'units' => 'px',
						),
					),
					'bottom' => array(
						'size' => array(
							'value' => 10,
							'units' => 'px',
						),
					),
				),
			),
		);

		$extended_data = array(
			'name'     => $this->a->__( 'Extended' ),
			'template' => array(
				'width' => array(
					'value' => 100,
					'units' => '%',
				),
				'range' => array(
					'min' => 320,
					'max' => 800,
				),
			),
			'btn'     => array(
				'color' => '#0000ff',
				'text'  => array( 'color' => '#ffffff', ),
			),
			'bg'      => array(
				'color' => '#6b6b6b',
				'img'   => array(
					'img'    => '',
					'repeat' => 'repeat',
				),
			),        
			'header' => array(
				'logo' => array(
					'logo'  => 'catalog/logo.png',
					'align' => 'center',
					'valign' => 'bottom',
					'width' => array(
						'value' => 60,
						'units' => '%',
					),
				),
				'link' => array(
					'color' => '#0000ff',
					'size'  => array(
						'value' => 14,
						'units' => 'px',
					),
				),
				'bg'   => array(
					'color' => '#f77878',
					'img'   => array(
						'img'    => '',
						'repeat' => 'repeat',
					),
				),
				'text' => array(
					'align'   => 'center',
					'valign'  => 'middle',
					'content' => '<b>Mail Templates Manager</b>',
					'color'   => '#ffffff',
					'size'    => array(
						'value' => 30,
						'units' => 'px',
					),
				),
				'height'  => array(
					'value' => 200,
					'units' => 'px',
				),
				'margin' => array(
					'top' => array(
						'size' => array(
							'value' => 10,
							'units' => 'px',
						),
					),
				),
			),
			'content'        => array(
				'link' => array(
					'color' => '#0000ff',
					'size' => array(
						'value' => 14,
						'units' => 'px',
					),
				),
				'bg'   => array(
					'color' => '#ffffff',
					'img'   => array(
						'img'    => '',
						'repeat' => 'repeat',
					),
				),
				'text' => array(
					'align'   => 'left',
					'valign'  => 'middle',
					'content' => '',
					'color'    => '#000000',
					'size'    => array(
						'value' => 16,
						'units' => 'px',
					),
				),
				'margin' => array(
					'top' => array(
						'size' => array(
							'value' => 10,
							'units' => 'px',
						),
					),
				),
			),
			'footer'         => array(
				'link' => array(
					'color' => '#0000ff',
					'size'  => array(
						'value' => 14,
						'units' => 'px',
					),
				),
				'bg'   => array(
					'color' => '#ffffff',
					'img'   => array(
						'img'    => '',
						'repeat' => 'repeat',
					),
				),
				'text' => array(
					'align'   => 'left',
					'valign'  => 'middle',
					'content' => '{vitrine(1)}',
					'color'   => '#000000',
					'size'    => array(
						'value' => 14,
						'units' => 'px',
					),
				),
				'height'  => array(
					'value' => 200,
					'units' => 'px',
				),
				'margin' => array(
					'top'    => array(
						'size' => array(
							'value' => 10,
							'units' => 'px',
						),
					),
					'bottom' => array(
						'size' => array(
							'value' => 10,
							'units' => 'px',
						),
					),
				),
			),
			'top'         => array(
				'link' => array(
					'color' => '#ffffff',
					'size'  => array(
						'value' => 18,
						'units' => 'px',
					),
				),
				'bg'   => array(
					'color' => '#6b6b6b',
					'img'   => array(
						'img'    => '',
						'repeat' => 'repeat',
					),
				),
				'text' => array(
					'align'   => 'right',
					'valign'  => 'bottom',
					'content' => '{unsubscribe(Cancel {newsletter_name})}',
					'color'   => '#000000',
					'size'    => array(
						'value' => 14,
						'units' => 'px',
					),
				),
				'height'  => array(
					'value' => 20,
					'units' => 'px',
				),
				'margin' => array(
					'top'    => array(
						'size' => array(
							'value' => 10,
							'units' => 'px',
						),
					),
					'bottom' => array(
						'size' => array(
							'value' => 0,
							'units' => 'px',
						),
					),
				),
			),
			'bottom'         => array(
				'link' => array(
					'color' => '#0000ff',
					'size'  => array(
						'value' => 14,
						'units' => 'px',
					),
				),
				'bg'   => array(
					'color' => '#f77878',
					'img'   => array(
						'img'    => '',
						'repeat' => 'repeat',
					),
				),
				'text' => array(
					'align'   => 'right',
					'valign'  => 'top',
					'content' => '{social(3)}',
					'color'   => '#000000',
					'size'    => array(
						'value' => 14,
						'units' => 'px',
					),
				),
				'height'  => array(
					'value' => 60,
					'units' => 'px',
				),
				'margin' => array(
					'top'    => array(
						'size' => array(
							'value' => 10,
							'units' => 'px',
						),
					),
					'bottom' => array(
						'size' => array(
							'value' => 10,
							'units' => 'px',
						),
					),
				),
			)
		);

		$two_columns_data = array(
			'name'     => $this->a->__( 'Two columns' ),
			'template' => array(
				'width' => array(
					'value' => 100,
					'units' => '%',
				),
				'range' => array(
					'min' => 320,
					'max' => 800,
				),
			),
			'columns' => array( 'width'  => '70' ),
			'btn'     => array(
				'color' => '#0000ff',
				'text'  => array( 'color' => '#ffffff', ),
			),
			'bg'      => array(
				'color' => '#6b6b6b',
				'img'   => array(
					'img'    => '',
					'repeat' => 'repeat',
				),
			),        
			'header' => array(
				'logo' => array(
					'logo'  => 'catalog/logo.png',
					'align' => 'center',
					'valign' => 'bottom',
					'width' => array(
						'value' => 60,
						'units' => '%',
					),
				),
				'link' => array(
					'color' => '#0000ff',
					'size'  => array(
						'value' => 14,
						'units' => 'px',
					),
				),
				'bg'   => array(
					'color' => '#f77878',
					'img'   => array(
						'img'    => '',
						'repeat' => 'repeat',
					),
				),
				'text' => array(
					'align'   => 'center',
					'valign'  => 'middle',
					'content' => '<b>Mail Templates Manager</b>',
					'color'   => '#ffffff',
					'size'    => array(
						'value' => 30,
						'units' => 'px',
					),
				),
				'height'  => array(
					'value' => 200,
					'units' => 'px',
				),
				'margin' => array(
					'top' => array(
						'size' => array(
							'value' => 10,
							'units' => 'px',
						),
					),
				),
			),
			'content'        => array(
				'link' => array(
					'color' => '#0000ff',
					'size' => array(
						'value' => 14,
						'units' => 'px',
					),
				),
				'bg'   => array(
					'color' => '#ffffff',
					'img'   => array(
						'img'    => '',
						'repeat' => 'repeat',
					),
				),
				'text' => array(
					'align'   => 'left',
					'valign'  => 'middle',
					'content' => '',
					'color'    => '#000000',
					'size'    => array(
						'value' => 16,
						'units' => 'px',
					),
				),
				'margin' => array(
					'top' => array(
						'size' => array(
							'value' => 10,
							'units' => 'px',
						),
					),
				),
			),
			'sidebar'        => array(
				'link' => array(
					'color' => '#0000ff',
					'size' => array(
						'value' => 14,
						'units' => 'px',
					),
				),
				'bg'   => array(
					'color' => '#ffffff',
					'img'   => array(
						'img'    => '',
						'repeat' => 'repeat',
					),
				),
				'text' => array(
					'align'   => 'center',
					'valign'  => 'middle',
					'content' => '',
					'color'    => '#000000',
					'size'    => array(
						'value' => 16,
						'units' => 'px',
					),
				),
			),
			'footer'         => array(
				'link' => array(
					'color' => '#0000ff',
					'size'  => array(
						'value' => 14,
						'units' => 'px',
					),
				),
				'bg'   => array(
					'color' => '#ffffff',
					'img'   => array(
						'img'    => '',
						'repeat' => 'repeat',
					),
				),
				'text' => array(
					'align'   => 'left',
					'valign'  => 'middle',
					'content' => '{vitrine(1)}',
					'color'   => '#000000',
					'size'    => array(
						'value' => 14,
						'units' => 'px',
					),
				),
				'height'  => array(
					'value' => 200,
					'units' => 'px',
				),
				'margin' => array(
					'top'    => array(
						'size' => array(
							'value' => 10,
							'units' => 'px',
						),
					),
					'bottom' => array(
						'size' => array(
							'value' => 10,
							'units' => 'px',
						),
					),
				),
			),
			'top'         => array(
				'link' => array(
					'color' => '#ffffff',
					'size'  => array(
						'value' => 18,
						'units' => 'px',
					),
				),
				'bg'   => array(
					'color' => '#6b6b6b',
					'img'   => array(
						'img'    => '',
						'repeat' => 'repeat',
					),
				),
				'text' => array(
					'align'   => 'right',
					'valign'  => 'bottom',
					'content' => '{unsubscribe(Cancel {newsletter_name})}',
					'color'   => '#000000',
					'size'    => array(
						'value' => 14,
						'units' => 'px',
					),
				),
				'height'  => array(
					'value' => 20,
					'units' => 'px',
				),
				'margin' => array(
					'top'    => array(
						'size' => array(
							'value' => 10,
							'units' => 'px',
						),
					),
					'bottom' => array(
						'size' => array(
							'value' => 0,
							'units' => 'px',
						),
					),
				),
			),
			'bottom'         => array(
				'link' => array(
					'color' => '#0000ff',
					'size'  => array(
						'value' => 14,
						'units' => 'px',
					),
				),
				'bg'   => array(
					'color' => '#f77878',
					'img'   => array(
						'img'    => '',
						'repeat' => 'repeat',
					),
				),
				'text' => array(
					'align'   => 'right',
					'valign'  => 'top',
					'content' => '{social(3)}',
					'color'   => '#000000',
					'size'    => array(
						'value' => 14,
						'units' => 'px',
					),
				),
				'height'  => array(
					'value' => 60,
					'units' => 'px',
				),
				'margin' => array(
					'top'    => array(
						'size' => array(
							'value' => 10,
							'units' => 'px',
						),
					),
					'bottom' => array(
						'size' => array(
							'value' => 10,
							'units' => 'px',
						),
					),
				),
			)
		);

		$two_columns_right_data = $two_columns_data;
		$two_columns_right_data['name'] = $this->a->__( 'Two columns right' );

		$textual_data = array( 'name' => $this->a->__( 'Textual' ) );

		$this->db->query( "INSERT INTO `" . DB_PREFIX . $this->a->profiles_table . "`
				( `profile`, `fields`, `inputs`, `removable`, `data`, `content_fields`, `description` ) VALUES
				( 'extended', 'top,header,content,footer,bottom', 'name,manage,template_width,template_range,bg_color,bg_img_img,bg_img_repeat', 0, '" . json_encode( $extended_data, JSON_HEX_QUOT ) . "', 'content', '" . $this->a->__( 'More advanced template than "Concise", has two additional (top and bottom) sections' ) . "' ),
				( 'concise', 'header,content,footer', 'name,manage,template_width,template_range,bg_color,bg_img_img,bg_img_repeat', 0, '" . json_encode( $concise_data, JSON_HEX_QUOT ) . "', 'content', '" . $this->a->__( 'One of the simplest templates. Consists of the header, content body and the footer' ) . "' ),
				/*( 'two_columns', 'top,header,content,sidebar,footer,bottom', 'name,manage,template_width,template_range,bg_color,bg_img_img,bg_img_repeat,columns_width', 0, '" . json_encode( $two_columns_data, JSON_HEX_QUOT ) . "', 'content,sidebar', '" . $this->a->__( 'Has the same layout as "Extended" with an additional left sidebar in the the content section. On devices with display\\\'s width less than 480px transforms to one column layout - sidebar goes up' ) . "' ),
				( 'two_columns_right', 'top,header,content,sidebar,footer,bottom', 'name,manage,template_width,template_range,bg_color,bg_img_img,bg_img_repeat,columns_width', 0, '" . json_encode( $two_columns_right_data, JSON_HEX_QUOT ) . "', 'content,sidebar', '" . $this->a->__( 'Has the same layout as "Extended" with an additional right sidebar in the the content section. On devices with display\\\'s width less than 480px transforms to one column layout - sidebar goes down') . "' ),*/
				(  'textual', '', 'name,manage', 0, '" . json_encode( $textual_data ) . "', 'content', '" . $this->a->__( 'Doesn\\\'t contain the header, the footer, images or any other HTML contents - simple text. All other templates have a textual fallback for browsers, which do not support HTML emails, so use this template only if you intentionally want to send textual email, for example, to save the traffic' ) . "' )
			" );

		$common_fields = 'text_color,text_size,link_color,link_size,bg_color,bg_img_img,bg_img_repeat,text_align,text_valign';
		$not_so_common = 'height,text_content';

		$this->db->query( "INSERT INTO `" . DB_PREFIX . $this->a->fields_table . "`
				( `name`, `inputs` ) VALUES
				( 'top', '$common_fields,$not_so_common,margin_top_size' ),
				( 'header', '$common_fields,$not_so_common,logo_logo,logo_align,logo_valign,logo_width,margin_top_size' ),
				( 'content', '$common_fields,margin_top_size' ),
				( 'sidebar', '$common_fields' ),
				( 'sidebar_flex', 'right,$common_fields' ),
				( 'footer', '$common_fields,$not_so_common,margin_top_size,margin_bottom_size' ),
				( 'bottom', '$common_fields,$not_so_common,margin_bottom_size' )
			" );

		$default_template_data = array(
			0 => array(
				'lang' => array(
					'en' => array(
						'content' => array(
							'content' => 'Type in some content here',
						),
					)
				),
			),
		);

		$this->db->query( "INSERT INTO `" . DB_PREFIX . $this->a->templates_table . "`
				( `name`, `hook`, `description`, `data` ) VALUES

				(	
					'" . $this->a->__( 'Admin' ) . "',
					'admin',
					'" . $this->a->__( 'This is the fallback template for all the emails sent to the store administrator' ) . "',
					'" . json_encode( array(
							0 => array(
								'lang' => array(
									'en' => array(
										'content' => array(
											'subject' => '{store_name}',
											'content' => '{initial_contents}',
										),
									)
								),
							),
						), JSON_HEX_QUOT ) . "'
				),

				(	
					'" . $this->a->__( 'Default' ) . "',
					'*',
					'" . $this->a->__( 'This is the fallback template for all the emails' ) . "',
					'" . json_encode( array(
							0 => array(
								'lang' => array(
									'en' => array(
										'content' => array(
											'subject' => '{store_name}',
											'content' => '{initial_contents}',
										),
									)
								),
							),
						), JSON_HEX_QUOT ) . "'
				),

				(	
					'" . $this->a->__( 'Dashboard - Forgotten password' ) . "',
					'admin.forgotten',
					'" . $this->a->__( 'This template represents contents of a letter, which send in response to a restore user password request' ) . "',
					'" . json_encode( array(
							0 => array(
								'lang' => array(
									'en' => array(
										'content' => array(
											'content' => '&lt;p&gt;&lt;span style=&quot;color: rgb(51, 51, 51); font-family: monospace; line-height: normal;&quot;&gt;A new password was requested for &lt;/span&gt;{store_name}&lt;span style=&quot;color: rgb(51, 51, 51); font-family: monospace; line-height: normal;&quot;&gt;&amp;nbsp;administration.&lt;/span&gt;&lt;br style=&quot;color: rgb(51, 51, 51); font-family: monospace; line-height: normal;&quot;&gt;&lt;br style=&quot;color: rgb(51, 51, 51); font-family: monospace; line-height: normal;&quot;&gt;&lt;span style=&quot;color: rgb(51, 51, 51); font-family: monospace; line-height: normal;&quot;&gt;To reset your password click on &lt;a href=&quot;http://{restore_password_url}&quot; target=&quot;_blank&quot;&gt;the link&lt;/a&gt;.&lt;/span&gt;&lt;br style=&quot;color: rgb(51, 51, 51); font-family: monospace; line-height: normal;&quot;&gt;&lt;br style=&quot;color: rgb(51, 51, 51); font-family: monospace; line-height: normal;&quot;&gt;&lt;span style=&quot;color: rgb(51, 51, 51); font-family: monospace; line-height: normal;&quot;&gt;The IP used to make this request was: {ip}&lt;/span&gt;&lt;br&gt;&lt;/p&gt;',
											'subject' => '{store_name} - Password reset request',
										),
									)
								),
							),
						), JSON_HEX_QUOT ) . "'
				),

				(	
					'" . $this->a->__( 'Newsletter - Subscribers' ) . "',
					'newsletter.newsletter',
					'" . $this->a->__( 'This template represents contents of newsletter, which send from the Marketing page to all the subscribed customers. Use shortcode {initial contents} to get letter message contents of Marketing/Mail page' ) . "',
					'" . json_encode( array(
							0 => array(
								'lang' => array(
									'en' => array(
										'content' => array(
											'content' => '{initial_contents}',
											'subject' => '{store_name} Newsletter',
										),
									)
								),
							),
						), JSON_HEX_QUOT ) . "'
				),

				(	
					'" . $this->a->__( 'Newsletter - Customer All' ) . "',
					'newsletter.customer_all',
					'" . $this->a->__( 'This template represents contents of newsletter, which send from the Marketing page to all the customers. Use shortcode {initial contents} to get letter message contents of Marketing/Mail page' ) . "',
					'" . json_encode( array(
							0 => array(
								'lang' => array(
									'en' => array(
										'content' => array(
											'content' => '{initial_contents}',
											'subject' => '{store_name} Newsletter',
										),
									)
								),
							),
						), JSON_HEX_QUOT ) . "'
				),

				(	
					'" . $this->a->__( 'Newsletter - Customer Group' ) . "',
					'newsletter.customer_group',
					'" . $this->a->__( 'This template represents contents of a newsletter, which send from the Marketing page to specific customer groups. Use shortcode {initial contents} to get letter message contents of Marketing/Mail page' ) . "',
					'" . json_encode( array(
							0 => array(
								'lang' => array(
									'en' => array(
										'content' => array(
											'content' => '{initial_contents}',
											'subject' => '{store_name} Newsletter',
										),
									)
								),
							),
						), JSON_HEX_QUOT ) . "'
				),

				(	
					'" . $this->a->__( 'Newsletter - Customer' ) . "',
					'newsletter.customer',
					'" . $this->a->__( 'This template represents contents of a newsletter, which send from the Marketing page to specific customers. Use shortcode {initial contents} to get letter message contents of Marketing/Mail page' ) . "',
					'" . json_encode( array(
							0 => array(
								'lang' => array(
									'en' => array(
										'content' => array(
											'content' => '{initial_contents}',
											'subject' => '{store_name} Newsletter',
										),
									)
								),
							),
						), JSON_HEX_QUOT ) . "'
				),

				(	
					'" . $this->a->__( 'Newsletter - Affiliates All' ) . "',
					'newsletter.affiliate_all',
					'" . $this->a->__( 'This template represents contents of newsletter,which  send from the Marketing page to all the affiliates. Use shortcode {initial contents} to get letter message contents of Marketing/Mail page' ) . "',
					'" . json_encode( array(
							0 => array(
								'lang' => array(
									'en' => array(
										'content' => array(
											'content' => '{initial_contents}',
											'subject' => '{store_name} Newsletter',
										),
									)
								),
							),
						), JSON_HEX_QUOT ) . "'
				),

				(	
					'" . $this->a->__( 'Newsletter - Affiliate' ) . "',
					'newsletter.affiliate',
					'" . $this->a->__( 'This template represents contents of newsletter, which send from the Marketing page to specific affiliates. Use shortcode {initial contents} to get letter message contents of Marketing/Mail page' ) . "',
					'" . json_encode( array(
							0 => array(
								'lang' => array(
									'en' => array(
										'content' => array(
											'content' => '{initial_contents}',
											'subject' => '{store_name} Newsletter',
										),
									)
								),
							),
						), JSON_HEX_QUOT ) . "'
				),

				(	
					'" . $this->a->__( 'Newsletter - Product' ) . "',
					'newsletter.product',
					'" . $this->a->__( 'This template represents contents of newsletter, which send from the Marketing page to all the customers, which have bought specific product. Use shortcode {initial contents} to get letter message contents of Marketing/Mail page' ) . "',
					'" . json_encode( array(
							0 => array(
								'lang' => array(
									'en' => array(
										'content' => array(
											'content' => '{initial_contents}',
											'subject' => '{store_name} Newsletter',
										),
									)
								),
							),
						), JSON_HEX_QUOT ) . "'
				),

				(	
					'" . $this->a->__( 'Newsletter' ) . "',
					'newsletter',
					'" . $this->a->__( 'This is a general template for newsletter emails' ) . "',
					'" . json_encode( array(
							0 => array(
								'lang' => array(
									'en' => array(
										'content' => array(
											'content' => '{initial_contents}',
											'subject' => '{store_name}: Subscription',
										),
									)
								),
							),
						), JSON_HEX_QUOT ) . "'
				),

				(	
					'" . $this->a->__( 'Newsletter - Confirm subscription' ) . "',
					'newsletter.confirm',
					'" . $this->a->__( 'This is a template of a confirmation email for newsletter subscription (\"Double Opt-in\" option)' ) . "',
					'" . json_encode( array(
							0 => array(
								'lang' => array(
									'en' => array(
										'content' => array(
											'content' => 'This email address was subscribed to newsletter {newsletter_name} from {store_name}.<br>To confirm subscription follow {confirm_subscription_url(this link)}.<br>If you got this email by mistake - just ignore it.<br>Regards,<br>{store_name}',
											'subject' => '{store_name}: Confirmation of subscription to a newsletter',
										),
									)
								),
							),
						), JSON_HEX_QUOT ) . "'
				),

				(	
					'" . $this->a->__( 'Newsletter - Successful subscription' ) . "',
					'newsletter.subscribe',
					'" . $this->a->__( 'This is a general template for an email, sent on a successful subscription to a newsletter. To create separate template for the newsletter - add new template and use \"Action hook\" of this template as a basis - add the newsletter name to it after the dot - newsletter.subscribe.the_newsletter_name' ) . "',
					'" . json_encode( array(
							0 => array(
								'lang' => array(
									'en' => array(
										'content' => array(
											'content' => 'Hi, {subscriber_name},<br>Congratulations you have been successfully subscribed to {newsletter_name} newsletter<br>{store_name}',
											'subject' => '{store_name}: Successful subscription to newsletter',
										),
									)
								),
							),
						), JSON_HEX_QUOT ) . "'
				),

				(	
					'" . $this->a->__( 'Newsletter - Successful unsubscription' ) . "',
					'newsletter.unsubscribe',
					'" . $this->a->__( 'This is a general template for an email, sent on a successful unsubscription from a newsletter. To create separate template for the newsletter - add new template and use \"Action hook\" of this template as a basis - add the newsletter name to it after the dot - newsletter.unsubscribe.the_newsletter_name' ) . "',
					'" . json_encode( array(
							0 => array(
								'lang' => array(
									'en' => array(
										'content' => array(
											'content' => 'Hi, {subscriber_name},<br>Your subscription to {newsletter_name} newsletter has been successfully canceled<br>{store_name}',
											'subject' => '{store_name}: Subscription cancellation',
										),
									)
								),
							),
						), JSON_HEX_QUOT ) . "'
				),

				(	
					'" . $this->a->__( 'Customer' ) . "',
					'customer',
					'" . $this->a->__( 'This is the fallback template for all the emails sent to customer' ) . "',
					'" . json_encode( array(
							0 => array(
								'lang' => array(
									'en' => array(
										'content' => array(
											'subject' => '{store_name}',
											'content' => '{initial_contents}',
										),
									)
								),
							),
						), JSON_HEX_QUOT ) . "'
				),

				(	
					'" . $this->a->__( 'Customer - Approve' ) . "',
					'customer.approve',
					'" . $this->a->__( 'This template represents contents of a letter, which send to a customer on account approval' ) . "',
					'" . json_encode( array(
							0 => array(
								'lang' => array(
									'en' => array(
										'content' => array(
											'subject' => '{store_name} Your account has been activated',
											'content' => 'Dear {customer_full_name}, welcome and thank you for registering at {store_name}!<br><br>Your account has now been created and you can log in by using your email address and password.<br><br><br>{button(2)}<br><br>Upon logging in, you will be able to access other services including reviewing past orders, printing invoices and editing your account information.<br><br>Thanks,<br>{store_name}',
										),
									)
								),
							),
						), JSON_HEX_QUOT ) . "'
				),

				(	
					'" . $this->a->__( 'Customer - Add credit' ) . "',
					'customer.addtransaction',
					'" . $this->a->__( 'This template represents contents of a letter, which send to a customer, in response to credit transaction upon customer&quot;s account' ) . "',
					'" . json_encode( array(
							0 => array(
								'lang' => array(
									'en' => array(
										'content' => array(
											'subject' => '{store_name} - Account Credit',
											'content' => 'Dear {customer_full_name}<br><br>You have received {transaction_amount} credit!<br>Your total amount of credit is now {transaction_total}.<br><br>Your account credit will be automatically deducted from your next purchase.<br><br>Thanks,<br>{store_name}',
										),
									)
								),
							),
						), JSON_HEX_QUOT ) . "'
				),

				(
					'" . $this->a->__( 'Customer - Add reward points' ) . "',
					'customer.addreward',
					'" . $this->a->__( 'This template represents contents of a letter, which send to a customer, in response to reward points addition' ) . "',
					'" . json_encode( array(
							0 => array(
								'lang' => array(
									'en' => array(
										'content' => array(
											'subject' => '{store_name} - Reward Points',
											'content' => 'Dear {customer_full_name}<br><br>You have received {reward_points} reward points!<br>Your total amount of reward points is now {reward_total}.<br><br>Thanks,<br>{store_name}',
										),
									)
								),
							),
						), JSON_HEX_QUOT ) . "'
				),

				(	
					'" . $this->a->__( 'Affiliate' ) . "',
					'affiliate',
					'" . $this->a->__( 'This is the fallback template for all the emails sent to an  affiliate' ) . "',
					'" . json_encode( array(
							0 => array(
								'lang' => array(
									'en' => array(
										'content' => array(
											'subject' => '{store_name}',
											'content' => '{initial_contents}',
										),
									)
								),
							),
						), JSON_HEX_QUOT ) . "'
				),

				(	
					'" . $this->a->__( 'Affiliate - Approve' ) . "',
					'affiliate.approve',
					'" . $this->a->__( 'This template represents contents of a letter, which send to a affiliate on account approval' ) . "',
					'" . json_encode( array(
							0 => array(
								'lang' => array(
									'en' => array(
										'content' => array(
											'subject' => '{store_name} Your affiliate account has been activated',
											'content' => 'Dear {affiliate_full_name}, welcome and thank you for registering at {store_name}!<br><br>Your account has now been created and you can log in by using your email address and password.<br><br><br>{button(4)}<br><br>Upon logging in, you will be able to access other services including reviewing past orders, printing invoices and editing your account information.<br><br>Thanks,<br>{store_name}',
										),
									)
								),
							),
						), JSON_HEX_QUOT ) . "'
				),

				(	
					'" . $this->a->__( 'Affiliate - Add commission' ) . "',
					'affiliate.addtransaction',
					'" . $this->a->__( 'This template represents contents of a letter, which send to a affiliate, in response to commission transaction' ) . "',
					'" . json_encode( array(
							0 => array(
								'lang' => array(
									'en' => array(
										'content' => array(
											'subject' => '{store_name} - Affiliate commission',
											'content' => 'Dear {affiliate_full_name}<br><br>You have received {affiliate_commission} commission!<br>Your total amount of commission is now {affiliate_commission_total}.<br><br>Thanks,<br>{store_name}',
										),
									)
								),
							),
						), JSON_HEX_QUOT ) . "'
				),

				(	
					'" . $this->a->__( 'Customer - Return update' ) . "',
					'customer.addreturnhistory',
					'" . $this->a->__( 'This template represents contents of a letter, which send to a customer, in response to change status of a return' ) . "',
					'" . json_encode( array(
							0 => array(
								'lang' => array(
									'en' => array(
										'content' => array(
											'subject' => '{store_name} - Return update',
											'content' => 'Return ID: {return_id}<br>Return Date: {return_date}<br><br>Your return has been updated to the following status: {return_status}{if_return_comment}<br><br>The comments for your return are:<br><br>{return_comment}{/if_return_comment}<br><br>Please reply to this email if you have any questions.',
										),
									)
								),
							),
						), JSON_HEX_QUOT ) . "'
				),

				(	
					'" . $this->a->__( 'Customer - Voucher' ) . "',
					'customer.sendvoucher',
					'" . $this->a->__( 'This template represents voucher, which sent to customer' ) . "',
					'" . json_encode( array(
							0 => array(
								'lang' => array(
									'en' => array(
										'content' => array(
											'subject' => '{store_name} - You have been sent a gift voucher from {voucher_from}',
											'content' => 'You have received a Gift Certificate worth {voucher_amount}<br><br>This Gift Certificate has been sent to you by {voucher_from}<br>With a message saying<br><br>{voucher_message}<br><br>To redeem this Gift Certificate, write down the redemption code which is {voucher_code} then click on the the link below and purchase the product you wish to use this gift voucher on. You can enter the gift voucher code on the shopping cart page before you click checkout.<br><br><a href="{store_url} target="_blank" >{store_name}</a><br><br>Please reply to this email if you have any questions.',
										),
									)
								),
							),
						), JSON_HEX_QUOT ) . "'
				),

				(	
					'" . $this->a->__( 'Customer - Password reset' ) . "',
					'customer.forgotten',
					'" . $this->a->__( 'This template represents a letter, which send to a user in response to a password reset request. Since there is no secure way to fetch sensitive customer information you should use shortcode {initial_contents} to insert all the letter contents, provided by OpenCart, which contains a password, code etc' ) . "',
					'" . json_encode( array(
							0 => array(
								'lang' => array(
									'en' => array(
										'content' => array(
											'subject' => '{store_name} - Password reset request',
											'content' => '{initial_contents}',
										),
									)
								),
							),
						), JSON_HEX_QUOT ) . "'
				),

				(	
					'" . $this->a->__( 'Affiliate - Password reset' ) . "',
					'affiliate.forgotten',
					'" . $this->a->__( 'This template represents a letter, which send to an affiliate in response to a password reset request. Since there is no secure way to fetch sensitive customer information you should use shortcode {initial_contents} to insert all the letter contents, provided by OpenCart, which contains a password, code etc' ) . "',
					'" . json_encode( array(
							0 => array(
								'lang' => array(
									'en' => array(
										'content' => array(
											'subject' => '{store_name} - Password reset request for affiliate account',
											'content' => '{initial_contents}',
										),
									)
								),
							),
						), JSON_HEX_QUOT ) . "'
				),

				(	
					'" . $this->a->__( 'Dashboard - Enquiry' ) . "',
					'admin.enquiry',
					'" . $this->a->__( 'This template represents contents of an enquiry' ) . "',
					'" . json_encode( array(
							0 => array(
								'lang' => array(
									'en' => array(
										'content' => array(
											'subject' => 'You got enquiry from {enquiry_from_name}',
											'content' => '<a href="mailto:{enquiry_from_email}">{enquiry_from_name}</a> has question:<br><br>{enquiry}',
										),
									)
								),
							),
						), JSON_HEX_QUOT ) . "'
				),

				(	
					'" . $this->a->__( 'Customer - New' ) . "',
					'customer.new',
					'" . $this->a->__( 'This template represents contents of a letter, which send to a newly registered customer' ) . "',
					'" . json_encode( array(
							0 => array(
								'lang' => array(
									'en' => array(
										'content' => array(
											'subject' => '{store_name} - Thank you for registering',
											'content' => 'Welcome and thank you for registering at {store_name}!<br><br>{if_account_approve}Your account must be approved before you can login. Once approved you can log in by using your email address and password by visiting our website.{/if_account_approve}{if_account_no_approve}{button(2)}{/if_account_no_approve}<br><br>Upon logging in, you will be able to access other services including reviewing past orders, printing invoices and editing your account information.<br><br>Thanks,<br>{store_name}',
										),
									)
								),
							),
						), JSON_HEX_QUOT ) . "'
				),

				(	
					'" . $this->a->__( 'Dashboard - New customer' ) . "',
					'admin.newcustomer',
					'" . $this->a->__( 'This template represents contents of a letter, which send to the store administrator to notify about a newly registered customer' ) . "',
					'" . json_encode( array(
							0 => array(
								'lang' => array(
									'en' => array(
										'content' => array(
											'subject' => 'New customer',
											'content' => 'A new customer has signed up:<br><br>Web Site: {store_name}<br>First Name: {new_customer_first_name}<br>Last Name: {new_customer_last_name}<br>Customer Group: {new_customer_group}<br>E-Mail: {new_customer_email}<br>Telephone: {new_customer_telephone}',
										),
									)
								),
							),
						), JSON_HEX_QUOT ) . "'
				),

				(	
					'" . $this->a->__( 'Affiliate - New' ) . "',
					'affiliate.new',
					'" . $this->a->__( 'This template represents contents of a letter, which send to a newly registered affiliate' ) . "',
					'" . json_encode( array(
							0 => array(
								'lang' => array(
									'en' => array(
										'content' => array(
											'subject' => '{store_name} - Affiliate program',
											'content' => 'Thank you for joining the {store_name} Affiliate Program!<br><br>{if_affiliate_approve}Your account must be approved before you can login. Once approved you can log in by using your email address and password by visiting our website or click button below.<br>{/if_affiliate_approve}<br>{button(4)}<br><br>Upon logging in, you will be able to generate tracking codes, track commission payments and edit your account information.<br><br>Thanks,<br>{store_name}',
										),
									)
								),
							),
						), JSON_HEX_QUOT ) . "'
				),

				(	
					'" . $this->a->__( 'Dashboard - New affiliate' ) . "',
					'admin.newafiliate',
					'" . $this->a->__( 'This template represents contents of a letter, which send to the store administrator to notify about a newly registered affiliate' ) . "',
					'" . json_encode( array(
							0 => array(
								'lang' => array(
									'en' => array(
										'content' => array(
											'subject' => 'New affiliate',
											'content' => 'A new affiliate has signed up:<br><br>Web Site: {store_name}<br>First Name: {new_affiliate_first_name}<br>Last Name: {new_affiliate_last_name}<br>E-Mail: {new_affiliate_email}<br>Telephone: {new_affiliate_telephone}',
										),
									)
								),
							),
						), JSON_HEX_QUOT ) . "'
				),

				(	
					'" . $this->a->__( 'Dashboard - New review' ) . "',
					'admin.review',
					'" . $this->a->__( 'This template represents contents of a letter, which send to the store administrator to notify about a new product review' ) . "',
					'" . json_encode( array(
							0 => array(
								'lang' => array(
									'en' => array(
										'content' => array(
											'subject' => '{store_name} - Product review',
											'content' => 'You have a new product review waiting.<br><br>Product: {review_product}<br>Reviewer: {review_person}<br>Rating: {review_rating}<br>Review Text: {review_text}',
										),
									)
								),
							),
						), JSON_HEX_QUOT ) . "'
				),

				(	
					'" . $this->a->__( 'Customer - New order' ) . "',
					'customer.order.new',
					'" . $this->a->__( 'This template represents contents of a letter, which send to customer in response to the successful placement of the order' ) . "',
					'" . json_encode( array(
							0 => array(
								'lang' => array(
									'en' => array(
										'content' => array(
											'subject' => '{store_name} - Order {order_id}',
											'content' => 'Thank you for your interest in {store_name} products.<br><br>Your order has been received {if_order_approve}and will be processed once payment has been confirmed.{if_order_download}<br> As soon as the order is processed, you can download it by going to your account.{/if_order_download}{/if_order_approve}{if_order_no_approve}and processed.<br>{if_order_download}You can download your order by going to your account.{/if_order_download}{/if_order_no_approve}<br><br>To view your order click on the link below:<br><br>{button(5)}<br><br>Order details:<br>{invoice_table}<br><br>Thanks,<br>{store_name}',
										),
									)
								),
							),
						), JSON_HEX_QUOT ) . "'
				),

				(	
					'" . $this->a->__( 'Dashboard - New order' ) . "',
					'admin.order.new',
					'" . $this->a->__( 'This template represents contents of a letter, which send to the store administrator in response to the successful placement of the order' ) . "',
					'" . json_encode( array(
							0 => array(
								'lang' => array(
									'en' => array(
										'content' => array(
											'subject' => '{store_name} - Order {order_id}',
											'content' => 'You have received an order<br><br>Order details:<br>{invoice_table}',
										),
									)
								),
							),
						), JSON_HEX_QUOT ) . "'
				),

				(	
					'" . $this->a->__( 'Customer - Order update' ) . "',
					'customer.order.update',
					'" . $this->a->__( 'This template represents contents of a general letter, which send to the customer in response to an order status change. If template for a specific order status is missing - it will be used' ) . "',
					'" . json_encode( array(
							0 => array(
								'lang' => array(
									'en' => array(
										'content' => array(
											'subject' => '{store_name} - Order {order_id} update',
											'content' => 'Order ID: {order_id}<br>Date Ordered: {order_date_added}<br><br>Your order has been updated to the following status: {order_status_new}<br><br>{button(5)}{if_order_status_comment}<br><br>The comments for your order are: {order_status_comment}{/if_order_status_comment}<br><br>Please reply to this email if you have any questions.',
										),
									)
								),
							),
						), JSON_HEX_QUOT ) . "'
				)

			" );

		$this->db->query( "INSERT INTO `" . DB_PREFIX . $this->a->shortcodes_table . "` VALUES (1,'vitrine','{\"type\":\"bestseller\",\"title\":{\"text\":\"You may be interested in\",\"color\":\"#27c1ef\",\"align\":\"center\",\"height\":\"30\"},\"number\":\"6\",\"related\":\"0\",\"product\":{\"default\":\"\",\"arbitrary\":\"\"}}'),(2,'button','{\"caption\":{\"text\":\"Login into account\",\"color\":\"#ffffff\",\"height\":\"20\"},\"color\":\"#27c1ef\",\"fullwidth\":\"0\",\"border\":{\"color\":\"#0000ff\",\"radius\":\"3\",\"width\":\"1\"},\"url\":\"{account_login_url}\",\"align\":\"center\",\"height\":\"60\",\"width\":\"300\"}'),(3,'social','{\"appearance\":\"square_grey\",\"item\":{\"facebook\":{\"url\":\"#\",\"status\":\"1\"},\"google+\":{\"url\":\"#\",\"status\":\"1\"},\"instagram\":{\"url\":\"#\",\"status\":\"1\"},\"linkedin\":{\"url\":\"#\",\"status\":\"1\"},\"twitter\":{\"url\":\"#\",\"status\":\"1\"},\"tumblr\":{\"url\":\"#\",\"status\":\"1\"},\"yelp\":{\"url\":\"#\",\"status\":\"1\"},\"vine\":{\"url\":\"#\",\"status\":\"1\"},\"youtube\":{\"url\":\"#\",\"status\":\"1\"}},\"title\":{\"text\":\" \",\"color\":\"#000000\",\"align\":\"right\",\"height\":\"20\"},\"icon\":{\"height\":\"40\"}}'),(4,'button','{\"caption\":{\"text\":\"Login into account\",\"color\":\"#ffffff\",\"height\":\"20\"},\"color\":\"#27c1ef\",\"fullwidth\":\"0\",\"border\":{\"color\":\"#0000ff\",\"radius\":\"3\",\"width\":\"1\"},\"url\":\"{affiliate_login_url}\",\"align\":\"center\",\"height\":\"60\",\"width\":\"300\"}'),(5,'button','{\"caption\":{\"text\":\"View order details\",\"color\":\"#ffffff\",\"height\":\"20\"},\"color\":\"#27c1ef\",\"fullwidth\":\"0\",\"border\":{\"color\":\"#0000ff\",\"radius\":\"3\",\"width\":\"1\"},\"url\":\"{order_url}\",\"align\":\"center\",\"height\":\"60\",\"width\":\"300\"}')" );
	}

	/**
	 * Returns templates list to be used in select elements
	 * @return array
	 */
	public function get_templates_name() {
		$ret = array();
		foreach( $this->a->get_templates() as $template ) {
			$ret[ $template['template_id'] ] = $this->a->__( $template['name'] );
		}

		return $ret;
	}

	/**
	 * Adds custom template
	 * @param string $name Template name
	 * @param string $hook Template's activation hook 
	 * @param string $description Template description, optional
	 * @param array $data Template data, optional 
	 * @param bool $silent Flag whether to raise exception on error
	 * @throws \Advertikon\Exception on error
	 * @return boolean|array Operation status
	 */
	public function add_template( $data ) {
		$name = isset( $data['name'] ) ? $data['name'] : '';
		$hook = isset( $data['hook'] ) ? $data['hook'] : '';
		$path_hook = isset( $data['path_hook'] ) ? $data['path_hook'] : '';
		$parent = isset( $data['parent'] ) ? $data['parent'] : '';
		$description = isset( $data['description'] ) ? $data['description'] : '';
		$data = isset( $data['data'] ) ? $data['data'] : array();
		$silent = isset( $data['silent'] ) ? $data['silent'] : true;

		try {

			if( ! $name ) {
				throw new \Advertikon\Exception( $this->a->__( 'Template name is empty' ) );
			}

			if( ! $hook && ! $path_hook ) {
				throw new \Advertikon\Exception( $this->a->__( 'Template hook is empty' ) );
			}

			if( ! is_array( $data ) ) {
				throw new \Advertikon\Exception( $this->a->__( 'Template\'s data should be an array' ) );
			}

			if ( $path_hook ) {
				$hook_parts = explode( '-', $hook );
				$file =  trim( array_shift( $hook_parts ) );
				$file = dirname( DIR_SYSTEM ) . DIRECTORY_SEPARATOR . trim( $file, DIRECTORY_SEPARATOR );

				if( ! $hook_parts ) {
					throw new \Advertikon\Exception(
						$this->a->__(
							'Template hook need to has function/method name, as it\'s second part, in which mail send method is called'
						)
					);
				}

				$func_name = trim( array_shift( $hook_parts ) );

				if( ! file_exists( $file ) ) {
					throw new \Advertikon\Exception(
						$this->a->__(
							'First part of the template\'s hook needs to be a file. File "%s" doesn\'t exist',
							$file
						)
					);
				}

				$func_name = preg_replace( '/\([^)]*\)/', '', $func_name );

				if( ! preg_match( '/function\s+' . preg_quote( $func_name ) . '\s*\(/i', file_get_contents( $file ) ) ) {
					throw new \Advertikon\Exception(
						$this->a->__(
							'Second part of the template\'s hook needs to be a function/class method. Function "%s" doesn\'t exist in file "%s"',
							$func_name,
							$file
						)
					);
				}

				$root = dirname( DIR_SYSTEM );
				$file = substr( $file, strlen( $root ) );
				$field_name = 'path_hook';
				$field_value = strtolower( $file . '-' . $func_name . ( $hook_parts ? '-' . implode( '-', $hook_parts ) : '' ) );

			} else {
				$field_name = 'hook';
				$field_value = $hook;
			}

			$query = $this->a->q( array(
				'table' => $this->a->templates_table,
				'query' => 'select',
				'where' => array(
					'field'     => $field_name,
					'operation' => '=',
					'value'     => $field_value,
				),
				'limit' => 1,
			) );

			if( count( $query ) ) {
				throw new \Advertikon\Exception( $this->a->__( 'Template hook "%s" is already exists', $field_value ) );
			}

			if ( ! $data && $parent && -1 != $parent ) {
				if ( is_numeric( $parent ) ) {
					$sample_template = $this->a->get_mail_template( $parent );

				} else {
					$sample_template = $this->a->q( array(
						'table' => $this->a->templates_table,
						'query' => 'select',
						'where' => array(
							'field'     => 'hook',
							'operation' => '=',
							'value'     => $parent,
						),
					) );
				}

				if ( ! $sample_template ) {
					trigger_error(
						sprintf(
							'Failed to use sample template to fill in custom\'s template data - template with ID/hook "%s" is missing',
							$parent
						)
					);

				} else {
					$data = $sample_template['data'];
				}
			}

			$query = $this->a->q( array(
				'table' => $this->a->templates_table,
				'query' => 'insert',
				'values' => array(
					'name'        => $name,
					$field_name   => $field_value,
					'description' => $description,
					'data'        => json_encode( $data, JSON_HEX_QUOT ),
					'deletable'   => 1,
				),
			) );

			if( $query ) {
				return array(
					'id'   => $this->db->getLastId(),
					'name' => $name
				);
			}

		} catch ( \Advertikon\Exception $e ) {
			if ( $silent ) {
				trigger_error( $e->getMessage() );
				return false;
			}

			throw $e;
		}

		return false;
	}

	/**
	 * Saves order status template
	 * @param array $data Template data
	 * @throws \Advertikon\Exception when some of templates was not created
	 * @return boolean
	 */
	public function add_order_status_template( $data ) {
		$ids = array();

		// Check if templates already exist for order statuses
		$templates = $this->a->q( array(
			'table' => $this->a->templates_table,
			'query' => 'select',
			'where' => array(
				'field'     => 'hook',
				'operation' => 'in',
				'value'     => array_map( array( $this, 'order_status_hook' ), $data['statuses'] ),
			),
		) );
		
		if ( count( $templates) ) {
			$mes = $this->a->__( 'Order\'s status template already exists' );
			trigger_error( $mes );
			throw new \Advertikon\Exception( $mes );
		}

		// Fetch template to use as template
		$base = $this->a->q( array(
			'table' => $this->a->templates_table,
			'query' => 'select',
			'where' => array(
				'field'     => 'hook',
				'operation' => '=',
				'value'     => 'customer.order.update',
			),
		) );

		// Bounce to empty template
		if ( ! $base ) {
			$b = array();

		} else {
			$b = array();
			$b['data'] = $base['data'];
		}

		// Get order statuses names
		$this->load->model( 'localisation/order_status' );
		$order_statuses = array();

		foreach( $this->model_localisation_order_status->getOrderStatuses() as $s ) {
			$order_status[ $s['order_status_id'] ] = $s['name'];
		}

		foreach( $data['statuses'] as $status ) {
			$name = 'Customer - Order status: ' . $order_status[ $status ];

			// Add template into DB
			$res = $this->a->q( array(
				'table'  => $this->a->templates_table,
				'query'  => 'insert',
				'values' => array_merge( $b, array(
					'hook'        => $this->order_status_hook( $status ),
					'name'        => $name,
					'deletable'   => 1,
					'template_id' => 'NULL',
				) ),
			) );

			if ( $res ) {
				$ids[] = array(
					'id'   => $this->db->getLastId(),
					'name' => $name,
				);

			} else {
				$mes = $this->a->__( 'Fail to create template for order status %s', $order_status[ $status ] );
				trigger_error( $mes );
				throw new \Advertikon\Exception( $mes );
			}
		}

		return $ids;
	}

	/**
	 * Creates hook name for specific order status by its ID
	 * @param int $status_id Order status ID
	 * @return string
	 */
	public function order_status_hook( $status_id ) {
		return 'customer.order.update.' . $status_id;
	}

	/**
	 * Saves order status template
	 * @param array $data Template data
	 * @throws \Advertikon\Exception when some of templates was not created
	 * @return boolean
	 */
	public function add_return_status_template( $data ) {
		$ids = array();

		// Check if templates already exist for order statuses
		$templates = $this->a->q( array(
			'table' => $this->a->templates_table,
			'query' => 'select',
			'where' => array(
				'field'     => 'hook',
				'operation' => 'in',
				'value'     => array_map( array( $this, 'return_status_hook' ), $data['statuses'] ),
			),
		) );
		
		if ( count( $templates) ) {
			$mes = $this->a->__( 'Returns\'s status template already exists' );
			trigger_error( $mes );
			throw new \Advertikon\Exception( $mes );
		}

		// Fetch template to use as template
		$base = $this->a->q( array(
			'table' => $this->a->templates_table,
			'query' => 'select',
			'where' => array(
				'field'     => 'hook',
				'operation' => '=',
				'value'     => 'customer.addreturnhistory',
			),
		) );

		// Bounce to empty template
		if ( ! $base ) {
			$b = array();

		} else {
			$b = array();
			$b['data'] = $base['data'];
		}

		// Get order statuses names
		$this->load->model( 'localisation/return_status' );
		$order_statuses = array();

		foreach( $this->model_localisation_return_status->getReturnStatuses() as $s ) {
			$order_status[ $s['return_status_id'] ] = $s['name'];
		}

		foreach( $data['statuses'] as $status ) {
			$name = 'Customer - Return update: ' . $order_status[ $status ];

			// Add template into DB
			$res = $this->a->q( array(
				'table'  => $this->a->templates_table,
				'query'  => 'insert',
				'values' => array_merge( $b, array(
					'hook'        => $this->return_status_hook( $status ),
					'name'        => $name,
					'deletable'   => 1,
					'template_id' => 'NULL',
				) ),
			) );

			if ( $res ) {
				$ids[] = array(
					'id'   => $this->db->getLastId(),
					'name' => $name,
				);

			} else {
				$mes = $this->a->__( 'Fail to create template for return status %s', $order_status[ $status ] );
				trigger_error( $mes );
				throw new \Advertikon\Exception( $mes );
			}
		}

		return $ids;
	}

	/**
	 * Creates hook name for specific return status by its ID
	 * @param int $status_id Order status ID
	 * @return string
	 */
	public function return_status_hook( $status_id ) {
		return 'customer.addreturnhistory.' . $status_id;
	}

	/**
	 * Deletes template
	 * @param int $template_id Template ID 
	 * @return boolean Operation status
	 */
	public function delete_template( $template_id ) {
		$this->db->query(
			"DELETE FROM `" . DB_PREFIX . $this->a->templates_table . "`
			WHERE `template_id` = " . (int)$template_id . " AND `deletable` = 1"
		);

		return $this->db->countAffected() > 0;
	}

	/**
	 * Returns profiles list ti be used in select elements
	 * @return array
	 */
	public function profiles_to_select() {
		$ret = array();

		foreach( $this->a->get_profiles() as $profile ) {
			$ret[ $profile['profile_id'] ] = $profile['data']['name'];
		}

		return $ret;
	}

	/**
	 * Renders profile controls
	 * @param string $name Profile name 
	 * @param array $profile Profile data
	 * @return string
	 */
	public function render_profile_control( $name, $profile ) {
		$ret = '';

		// Profile form group
		$input_data = $this->get_profile_control_data( $name );

		if( is_null( $input_data ) ) {
			return $ret;
		}

		$setting_name = 'data/'  . str_replace( '_', '/', $name );

		// Implicit control ID
		if( empty( $input_data['id'] ) ) {
			$input_data['id'] = str_replace( '_', '-', $name );
		}

		// Implicit control name
		if( empty( $input_data['name'] ) ) {
			$input_data['name'] = $this->a->build_name( $name );
		}

		if( 'name' === $name ) {
			$input_data['value'] = $profile['data']['name'];

		} elseif ( isset( $input_data['type'] ) && 'select' === $input_data['type'] ) {
			$input_data['active'] = $this->a->get_from_array( $profile, $setting_name );

		} elseif ( 'manage' === $name ) {
			if( 0 == $profile['removable'] ) {
				$input_data['buttons']['delete']['custom_data'] .= ' disabled="disabled"';
			}

			if( ! $this->a->can_undo_profile_snapshot( $profile['profile_id'] ) ) {
				$input_data['buttons']['undo']['custom_data'] .= ' disabled="disabled"';
			}

			if( ! $this->a->can_redo_profile_snapshot( $profile['profile_id'] ) ) {
				$input_data['buttons']['redo']['custom_data'] .= ' disabled="disabled"';
			}

			if( ! $this->a->can_save_profile_snapshot( $profile['profile_id'] ) ) {
				$input_data['buttons']['save']['custom_data'] .= ' disabled="disabled"';
			}
			
		} else {
			$input_data['value'] = $this->a->get_from_array( $profile, $setting_name );

			// If image is elfinder image - get embed flag
			if( isset( $input_data['type'] ) && 'elfinder_image' === $input_data['type'] ) {
				$input_data['embed_value'] = $this->a->get_from_array(
					$profile,
					preg_replace( '@/[^/]+$@', '/embed', $setting_name )
				);
			}
		}

		// Profile name
		if( 'template_width' === $name ) {
			$input_data['element'] = $this->render_dimension( $input_data );

		// Template width range
		} elseif ( 'template_range' === $name ) {
			$input_data['element'] = $this->render_profile_width_range( $input_data);

		// Not used
		} elseif ( 'columns_width' === $name ) {
			$input_data['element'] = $this->render_profile_columns_width( $input_data );

		// Size element
		} elseif ( $this->a->is_ended_with( $name, '_size' ) ) {
			$input_data['element'] = $this->render_dimension( $input_data );

		// Logo width
		} elseif (  $this->a->is_ended_with( $name, 'logo_width' ) ) {
			$input_data['element'] = $this->render_dimension( $input_data );

		// Height element
		} elseif ( $this->a->is_ended_with( $name, 'height' ) ) {
			$input_data['element'] = $this->render_dimension( $input_data );

		// Align element
		} elseif ( $this->a->is_ended_with( $name, '_align' ) ) {
			$input_data['element'] = $this->a->r( array(
				'type'         => 'inputgroup',
				'element'      => $input_data,
				'addon_before' => '<i class="fa fa-align-' . ( isset( $input_data['active'] ) ? $input_data['active'] : 'left' ) . '"></i>'
			) );

		// Valign element
		} elseif ( $this->a->is_ended_with( $name, '_valign' ) ) {
			$valign = 'justify';
			if( isset( $input_data['active'] ) ) {
				switch( $input_data['active'] ) {
				case 'top' :
					$valign = 'left';
					break;
				case 'bottom' :
					$valign = 'right';
					break;
				default :
					$valign = 'justify';
					break;
				}
			}

			$input_data['element'] = $this->a->r( array(
				'type'         => 'inputgroup',
				'element'      => $input_data,
				'addon_before' => '<i class="fa fa-rotate-90 fa-align-' . $valign . '"></i>'
			) );
		}

		// other elements
		else {
			$input_data['element'] = $this->a->r( $input_data );
		}

		$ret .= $this->render_profile_control_group( $input_data );

		return $ret;
	}

	/**
	 * Returns profile form group by control name
	 * @param string $name Profile control's name
	 * @return array
	 */
	protected function get_profile_control_data( $name ) {
		static $data;
		$data = array(
			'name' => array(
				'type'  => 'text',
				'class' => 'form-control',
				'label' => $this->a->__( 'Profile name' ),
				'name'  => 'name',
			),
			'manage' => array(
				'type'    => 'buttongroup',
				'buttons' => array(
					'clone' =>
					array(
						'type'        => 'button',
						'button_type' => 'success',
						'id'          => 'profile-clone',
						'title'       => $this->a->__( 'Clone profile' ),
						'icon'	      => 'fa-copy',
						'custom_data' => 'data-url="' . $this->url->link(
							$this->a->type . '/' . $this->a->code . '/clone_profile',
							'token=' . $this->session->data['token'],
							'SSL'
						) . '" data-toggle="tooltip"',
					),
					'delete' =>
					array(
						'type'        => 'button',
						'button_type' => 'danger',
						'id'          => 'profile-delete',
						'title'       => $this->a->__( 'Delete profile' ),
						'icon'	      => 'fa-close',
						'custom_data' => 'data-url="' . $this->url->link(
							$this->a->type . '/' . $this->a->code . '/delete',
							'token=' . $this->session->data['token'],
							'SSL'
						) . '" data-toggle="tooltip"',
					),
					'save' =>
					array(
						'type'        => 'button',
						'button_type' => 'primary',
						'id'          => 'profile-save',
						'title'       => $this->a->__( 'Save profile' ),
						'icon'	      => 'fa-save',
						'custom_data' => 'data-url="' . $this->url->link(
							$this->a->type . '/' . $this->a->code . '/save',
							'token=' . $this->session->data['token'],
							'SSL'
						) . '" data-toggle="tooltip"',
					),
					'undo' =>
					array(
						'type'        => 'button',
						'button_type' => 'primary',
						'id'          => 'profile-undo',
						'title'       => $this->a->__( 'Undo changes' ),
						'icon'	      => 'fa-undo',
						'custom_data' => 'data-url="' . $this->url->link(
							$this->a->type . '/' . $this->a->code . '/undo',
							'token=' . $this->session->data['token'],
							'SSL'
						) . '" data-toggle="tooltip"',
					),
					'redo' =>
					array(
						'type'        => 'button',
						'button_type' => 'primary',
						'id'          => 'profile-redo',
						'title'       => $this->a->__( 'Redo changes' ),
						'icon'	      => 'fa-undo fa-flip-horizontal',
						'custom_data' => 'data-url="' . $this->url->link(
							$this->a->type . '/' . $this->a->code . '/redo',
							'token=' . $this->session->data['token'],
							'SSL'
						) . '" data-toggle="tooltip"',
					),
				),
			),
			'template_width' => array(
				'label' => $this->a->__( 'Template width' ),
				'description' => $this->a->__( 'You can set template width in pixel units or in percent from an available width (not always a display width). Optimal width for template 600 - 800px. To switch units of measure click on "Measure units button"' ),
				'as_info' => true,
			),
			'template_range' => array(
				'label' => $this->a->__( 'Template width range' ),
				'description' => $this->a->__( 'If you set template width in percent of an available width, you can also set minimum and maximum template width (in pixels). For example, if you set minimum and maximum template width to 320 and 800px correspondingly, then the template will adjust it\'s width to available width, but never became less than 320px or greater than 800px, so you never get unpredictable template appearance' ),
				'as_info' => true,
			),
			'btn_color' => array(
				'type'  => 'color',
				'label' => $this->a->__( 'Button color' ),
				'class' => 'iris',
			),
			'btn_text_color' => array(
				'type'  => 'color',
				'label' => $this->a->__( 'Button text color' ),
				'class' => 'iris',
			),
			'bg_color' => array(
				'type'  => 'color',
				'label' => $this->a->__( 'Background color' ),
				'class' => 'iris',
			),
			'bg_img_img' => array(
				'type'  => 'elfinder_image',
				'label' => $this->a->__( 'Background image' ),
				'description' => $this->a->__( 'Click to change disposition. Double click to set image' ),
				'tooltip'     => $this->a->__( 'To image show in email template, regardless of settings of customer email client, select the embedded disposition option' ),
			),
			'bg_img_repeat' => array(
				'type'  => 'select',
				'value' => $this->get_bg_img_repeat_values(),
				'label' => $this->a->__( 'Background image repeat' ),
				'class' => 'form-control',
				'description' => $this->a->__( 'This setting defines a background image behavior when its size is lesser than space to be filled. This option is not supported  by Outlook.com, Gmail.com, Outlook 2007 and Gmail App so try to use an image which covers all background space when it\'s possible' ),
				'as_info' => true,
			),
			'columns_width' => array(
				'label' => $this->a->__( 'Columns width' ),
			),
			'logo_logo' => array(
				'type'        => 'elfinder_image',
				'id'          => 'logo',
				'label'       => $this->a->__( 'Logo' ),
				'description' => $this->a->__( 'Click to change disposition. Double click to set image' ),
				'tooltip'     => $this->a->__( 'To image show in email template, regardless of settings of customer email client, select the embedded disposition option' ),
			),
			'logo_width' => array(
				'label' => $this->a->__( 'Logo width' ),
			),
			'logo_align' => array(
				'type'  => 'select',
				'value' => $this->get_align_values(),
				'label' => $this->a->__( 'Logo align' ),
				'class' => 'form-control',
			),
			'logo_valign' => array(
				'type'  => 'select',
				'value' => $this->get_valign_values(),
				'label' => $this->a->__( 'Logo vertical align' ),
				'class' => 'form-control',
			),
			'text_align' => array(
				'type'  => 'select',
				'value' => $this->get_align_values(),
				'label' => $this->a->__( 'Text align' ),
				'class' => 'form-control',
			),
			'text_valign' => array(
				'type'  => 'select',
				'value' => $this->get_valign_values(),
				'label' => $this->a->__( 'Text vertical align' ),
				'class' => 'form-control',
			),
			'text_мalign' => array(
				'type'  => 'select',
				'value' => $this->get_valign_values(),
				'label' => $this->a->__( 'Text vertical_align' ),
				'class' => 'form-control',
			),
			'text_color' => array(
				'type'  => 'color',
				'label' => $this->a->__( 'Text color' ),
				'class' => 'iris',
			),
			'text_size' => array(
				'label'  => $this->a->__( 'Font size' ),
				'values' => 'px',
				'titles' => $this->a->__( 'Font size is measured in pixel units' ),
				'maxes'  => '30'
			),
			'link_color' => array(
				'type'  => 'color',
				'label' => $this->a->__( 'Link color' ),
				'class' => 'iris',
			),
			'link_size' => array(
				'label'  => $this->a->__( 'Link font size' ),
				'values' => 'px',
				'titles' => $this->a->__( 'Font size is measured in pixel units' ),
				'maxes'  => '30'
			),
			'_content' => array(
				'type'  => 'textarea',
				'label' => 'Content',
				'class' => 'form-control shortcode-able',
				'description' => '',
			),
			'_height' => array(
				'label' => $this->a->__( 'Height' ),
				'values' => 'px',
			),
			'margin_top_size' => array(
				'label'  => $this->a->__( 'Top margin' ),
				'values' => 'px',
				'titles' => $this->a->__( 'Top margin in pixel units' ),
				'maxes'  => '100'
			),
			'margin_bottom_size' => array(
				'label'  => $this->a->__( 'Bottom margin' ),
				'values' => 'px',
				'titles' => $this->a->__( 'Top margin in pixel units' ),
				'maxes'  => '100'
			),
			'right' => array(
				'label'                 => $this->a->__( 'Place right' ),
				'type'                  => 'checkbox',
				'check_non_empty_value' => 1,
			),
		);

		if( isset( $data[ $name ] ) ) {
			return $data[ $name ];

		} else {
			foreach( $data as $n => $val ) {
				if( strlen( $n ) <= strlen( $name ) && strrpos( $name, $n, -1 * strlen( $n ) ) !== false ) {
					return $val;
				}
			}
		}

		trigger_error( sprintf( "Data for profile control with name '%s' is missing", $name ) );
		return null;
	}

	/**
	 * Renders profile form group
	 * @param array $data Profile form group data 
	 * @return string
	 */
	public function render_profile_control_group( $data ) {
		$label = isset( $data['label'] ) ? $data['label'] : '';
		$for = isset( $data['label_for'] ) ? $data['label_for'] : '';
		$element = isset( $data['element'] ) ? $data['element'] : '';
		$cols = isset( $data['cols'] ) ? $data['cols'] : ( isset( $this->a->cols ) ? $this->a->cols : array( 'col-sm-2', 'col-sm-10', ) );
		$has_status = isset( $data['status'] ) ? 'has-' . $data['status'] : '';
		$has_feedback = isset( $data['feedback'] ) ? 'has-feedback' : '';
		$feedback = isset( $data['feedback'] ) ? $data['feedback'] : '';
		$tooltip = isset( $data['tooltip'] ) ? $data['tooltip'] : '';
		$description = isset( $data['description'] ) ? $data['description'] : '';
		$as_info = ! empty( $data['as_info'] ) ? true : false;

		$str = '';

		if( $label ) {
			$str .=
			'<label for="' . $for . '" class="' . $cols[0] . '">' .
				$label . ' ' . $this->a->r()->render_popover( $tooltip ) .
			'</label>';
		}

		$str .=
			'<div class="' . $cols[1] . ' profile-control-data-wrapper">' .
				$element .
				( ! $as_info ? '<span class="help-block">' . $description . '</span>' :

				'<div class="alert alert-info alert-dismissible tip" role="alert">' .
					'<button type="button" class="close" data-dismiss="alert" aria-label="Close">' .
						'<span aria-hidden="true">&times;</span>' .
					'</button>' .
					'<i class="fa fa-info-circle fa-2x tip-icon"></i>' . $description .
				'</div>' ) .

			'</div>' .
			$feedback;


		return $str;
	}

	/**
	 * Renders dimension form control
	 * @param array $data  Control data
	 * @return string
	 */
	public function render_dimension( $data ) {
		return $this->a->r()->render_dimension( $data );
		// $values = isset( $data['values'] ) ? $data['values'] : 'px,%';
		// $texts  = isset( $data['texts'] ) ? $data['texts'] : 'px,%';
		// $titles = isset( $data['titles'] ) ? $data['titles'] : $this->a->__( 'Width measured in pixels' ) . ',' .
		// 	$this->a->__( 'Width measured in percentage of available width' );
		// $maxes  = isset( $data['maxes'] ) ? $data['maxes'] : '2000,100';
		// $name = isset( $data['name'] ) ? $data['name'] : '';
		// $id = isset( $data['id'] ) ? $data['id'] : '';
		// $value = isset( $data['value']['value'] ) ? $data['value']['value'] : '';
		// $units = isset( $data['value']['units'] ) ? $data['value']['units'] : '';
		// $max = isset( $data['max'] ) ? 'data-max="' . $data['max'] . '"' : '';

		// $str =
		// '<div class="profile-width-wrapper dimension-wrapper">' .
		// 	'<div class="profile-width-slider-wrapper">' .
		// 		'<div id="" class="slider" data-value1="' . $value . '" ' .
		// 			'data-value1-target="#' . $id . '-value"' .
		// 			$max .
		// 		'>' .
		// 		'</div>' .
		// 	'</div>' .
		// 	'<div class="profile-width-input-gr-wrapper">' .
		// 		$this->a->r( array(
		// 			'type'    => 'inputgroup',
		// 			'element' => array(
		// 				'type'  => 'text',
		// 				'id'    => $id . '-value',
		// 				'name'  => $name ? $name . '[value]' : '',
		// 				'value' => $value,
		// 				'css'   => 'width:80px',
		// 				'class' => 'form-control',
		// 			),
		// 			'addon_after' => array(
		// 				'type'        => 'button',
		// 				'id'          => $id . '-units',
		// 				'name'        => $name ? $name . '[units]' : '',
		// 				'text_before' => 'px',
		// 				'custom_data' => 'data-values="' . $values . '" data-texts="' . $texts . '" data-value="' . $units .
		// 				'" data-titles="' . $titles. '" data-maxes="' . $maxes . '" data-toggle="tooltip"',

		// 				'class'       => 'switchable measure-units',
		// 			),
		// 		) ) .
		// 		'</div>' .
		// '</div>';

		// return $str;
	}

	/**
	 * Renders profile width range form control
	 * @param array $data Control data
	 * @return array
	 */
	protected function render_profile_width_range( $data ) {
		$str =
		'<div class="profile-width-wrapper">' .
			'<div class="profile-width-input-gr-wrapper-left">' .
				$this->a->r( array(
					'type'    => 'inputgroup',
					'element' => array(
						'type'  => 'text',
						'id'    => $data['id'] . '-min',
						'name'  => $data['name'] . '[min]',
						'value' => $data['value']['min'],
						'css'   => 'width:70px',
						'class' => 'form-control',
					),
					'addon_before' => 'px',
				) ) .
			'</div>' .
			'<div class="profile-width-slider-wrapper-range">' .
				'<div id="profile-width-range-slider" class="slider" data-value1="' . $data['value']['min'] .
				'" data-value2="' . $data['value']['max'] .
					'" data-value1-target="#' . $data['id'] . '-min" data-value2-target="#' . $data['id'] . '-max" data-max="2000">' .
				'</div>' .
			'</div>' .
			'<div class="profile-width-input-gr-wrapper">' .
				$this->a->r( array(
					'type'    => 'inputgroup',
					'element' => array(
						'type'  => 'text',
						'id'    => $data['id'] . '-max',
						'name'  => $data['name'] . '[max]',
						'value' => $data['value']['max'],
						'css'   => 'width:70px',
						'class' => 'form-control',
					),
					'addon_after' => 'px',
				) ) .
				'</div>' .
		'</div>';

		return $str;
	}

	/**
	 * Renders profile columns width form control
	 * @param array $data Control data
	 * @return string
	 */
	protected function render_profile_columns_width( $data ) {
		$str =
		'<div class="profile-width-wrapper">' .
			'<div class="profile-width-input-gr-wrapper-left">' .
				$this->a->r( array(
					'type'    => 'inputgroup',
					'element' => array(
						'type'  => 'text',
						'id'    => $data['id'] . '-left',
						'value' => $data['value'],
						'css'   => 'width:70px',
						'class' => 'form-control',
					),
					'addon_before' => '%',
				) ) .
			'</div>' .
			'<div class="profile-width-slider-wrapper-range">' .
				'<div id="profile-columns-width-slider" class="slider" data-value1="' . $data['value'] . '"' .
					'" data-value1-target="#' . $data['id'] . '-right">' .
				'</div>' .
			'</div>' .
			'<div class="profile-width-input-gr-wrapper">' .
				$this->a->r( array(
					'type'    => 'inputgroup',
					'element' => array(
						'type'  => 'text',
						'id'    => $data['id'] . '-right',
						'name'  => $data['name'],
						'value' => 100 - (int)$data['value'],
						'css'   => 'width:70px',
						'class' => 'form-control',
					),
					'addon_after' => '%',
				) ) .
				'</div>' .
		'</div>';

		return $str;
	}

	/**
	 * Returns background image repeat values to use in select element
	 * @return array
	 */
	protected function get_bg_img_repeat_values() {
		return array(
			'no-repeat' => $this->a->__( 'Do not repeat' ),
			'repeat'    => $this->a->__( 'Repeat along X and Y axises' ),
			'repeat-x'  => $this->a->__( 'Repeat along X axis' ),
			'repeat-y'  => $this->a->__( 'Repeat along Y axis' ),
			);
	}

	/**
	 * Returns text align values to use in select element
	 * @return array
	 */
	public function get_align_values() {
		return array(
			'left'   => $this->a->__( 'Left' ),
			'center' => $this->a->__( 'Center' ),
			'right'  => $this->a->__( 'Right' ),
		);
	}

	/**
	 * Returns text valign repeat values to use in select element
	 * @return array
	 */
	protected function get_valign_values() {
		return array(
			'top'     => $this->a->__( 'Top' ),
			'middle'  => $this->a->__( 'Middle' ),
			'bottom'  => $this->a->__( 'Bottom' ),
		);
	}

	/**
	 * Returns list of QR Code error correction levels
	 * @return type
	 */
	public function get_ec_values() {
		return array(
			'L' => 'L - 7%',
			'M' => 'M - 15%',
			'Q' => 'Q - 25%',
			'H' => 'H - 30%',
		);
	}

	/**
	 * Returns name for profile part tab name
	 * @param string $code Tab code
	 * @return string Tab name
	 */
	public function get_panel_name( $code ) {
		$name = '';

		if( 'top' === $code ) {
			$name = $this->a->__( 'Top' );

		} elseif( 'header' === $code ) {
			$name = $this->a->__( 'Header' );

		} elseif( 'content' === $code ) {
			$name = $this->a->__( 'Content' );

		} elseif( 'footer' === $code ) {
			$name = $this->a->__( 'Footer' );

		} elseif( 'bottom' === $code ) {
			$name = $this->a->__( 'Bottom' );

		} elseif( 'sidebar' === $code ) {
			$name = $this->a->__( 'Sidebar' );
		}

		return $name;
	}

	/**
	 * Returns profile representation icon for specific part
	 * @param string $code 
	 * @param array $fields List of all the fields codes in the set
	 * @return string Icon HTML
	 */
	public function get_panel_icon( $code, $fields ) {
		$icon = 
		'<div class="panel-icon-body">';

		if( in_array( 'top', $fields ) ) {
			$icon .=
			'<div class="panel-icon-top' . ( 'top' === $code ? ' active' : '' ) . '"></div>';
		}

		$icon .=
			'<div class="panel-icon-header' . ( 'header' === $code ? ' active' : '' ) . '"></div>' .
			'<div class="panel-icon-content' . ( 'content' === $code ? ' active' : '' ) . '"></div>' .
			'<div class="panel-icon-footer' . ( 'footer' === $code ? ' active' : '' ) . '"></div>';

		if( in_array( 'bottom', $fields ) ) {
			$icon .=
			'<div class="panel-icon-bottom' . ( 'bottom' === $code ? ' active' : '' ) . '"></div>';
		}
			$icon .=
		'</div>';

		return $icon;
	}

	/**
	 * Returns vitrine shortcodes name values to use in select element
	 * @return array
	 */
	public function get_vitrine_types() {
		return array(
			'bestseller' => $this->a->__( 'Bestseller' ),
			'latest'     => $this->a->__( 'Latest' ),
			'popular'    => $this->a->__( 'Popular' ),
			'special'    => $this->a->__( 'Special' ),
			'related'    => $this->a->__( 'Related products' ),
			'arbitrary'  => $this->a->__( 'Arbitrary products'),
		);
	}

	/**
	 * Returns profile part inputs by its name
	 * @param string $field Profile part name
	 * @return array
	 */
	public function get_field_inputs( $field ) {
		$query = $this->db->query(
			"SELECT `inputs` FROM `" . DB_PREFIX . $this->a->fields_table . "`
			WHERE `name` = '" . $this->db->escape( $field ) . "'"
		);

		if( $query->num_rows ) {
			return explode( ',', $query->row['inputs'] );

		} else {
			return array();
		}
	}

	/**
	 * Saves profile data
	 * @param int $id Profile ID
	 * @param array $data Profile data
	 * @return boolean Operation status
	 */
	public function save_profile_data( $id, $data ) {
		if( ! is_scalar( $data ) ) {
			$data = nl2br( $this->a->json_encode( $data ) );
		}

		$query = $this->db->query(
			"UPDATE `" . DB_PREFIX . $this->a->profiles_table . "`
			set `data` = '" . $this->db->escape( $data ) . "'
			WHERE `profile_id` = " . (int)$id
		);

		return $this->db->countAffected();
	}	

	/**
	 * Saves template data
	 * @param int $id Template ID
	 * @param array $data Template data
	 * @return boolean Operation status
	 */
	public function save_template_data( $id, $data ) {
		if( ! is_scalar( $data ) ) {
			$data = nl2br( $this->a->json_encode( $data ) );
		}

		$query = $this->db->query(
			"UPDATE `" . DB_PREFIX . $this->a->templates_table . "`
			set `data` = '" . $this->db->escape( $data ) . "'
			WHERE `template_id` = " . (int)$id
		);

		return $this->db->countAffected();
	}	

	/**
	 * Clones profile
	 * @param int $id Profile iD 
	 * @return array Cloned profile
	 */
	public function clone_profile( $id ) {
		$parent_profile = $this->db->query(
			"SELECT * FROM `" . DB_PREFIX . $this->a->profiles_table . "`
			WHERE `profile_id` = " . (int)$id
		);

		if( ! $parent_profile || $parent_profile->num_rows === 0 ) {
			trigger_error( sprintf( 'Profile with ID %s does not exist', $id ) );
			return false;
		}

		$parent_profile = $parent_profile->row;
		$parent_profile['data'] = $this->a->object_to_array( json_decode( $parent_profile['data'] ) );
		$new_name = preg_replace( '/\-copy\(\d+\)/i', '', $parent_profile['data']['name'] ) . '-Copy(' . time() . ')';
		$parent_profile['data']['name'] = $new_name;
		$parent_profile['data'] = $this->a->json_encode( $parent_profile['data'] );

		if( ! $this->db->query(
			"INSERT INTO `" . DB_PREFIX . $this->a->profiles_table . "`
			(profile,fields,inputs,data,removable,content_fields)
			VALUES
				(
					'{$parent_profile['profile']}',
					'{$parent_profile['fields']}',
					'{$parent_profile['inputs']}',
					'{$parent_profile['data']}',
					'1',
					'{$parent_profile['content_fields']}'
				)"
			) || $this->db->countAffected() < 1 ) {

			return false;
		}

		return array( 'id' => $this->db->getLastId(), 'name' => $new_name );
	}

	/**
	 * Deletes profile
	 * @param int $id Profile ID
	 * @return boolean Operation status
	 */
	public function delete_profile( $id ) {

		if( ! is_numeric( $id ) ) {
			return false;
		}

		$this->db->query(
			"DELETE FROM `" . DB_PREFIX . $this->a->profiles_table . "`
			WHERE `profile_id` = " . (int)$id . "
				AND `removable` = 1"
		);

		return $this->db->countAffected() >= 1;
	}

	/**
	 * Renders template manager tab body
	 * @param array $template Template data
	 * @param string $id_prefix String to prefix store and language tabs below (eg "store" )
	 * @return string Tab contents
	 */
	public function render_template_tab_body( $template, $id_prefix = '' ) {
		$ret =
		'<div class="tab-content template-store">';

		foreach( $template['data'] as $store_id => $store_data ) {
			if( "content" === $store_id ) {
				continue;
			}

			$ret .= $this->get_store_tab_contents( $id_prefix, $store_id, $store_data, $template['template_id'] );
		}

		$ret .= 
		'</div>'; // .tab-content (store)

		return $ret;
	}

	/**
	 * Renders store tab contents
	 * @param string $id_prefix Common prefix string 
	 * @param int $store_id Store ID
	 * @param array $store_data Store part of template data
	 * @param int $template_id Current template ID
	 * @return string Store tab contents
	 */
	public function get_store_tab_contents( $id_prefix, $store_id, $store_data, $template_id ) {
		$languages = $this->a->get_languages();

		$langs = array();
		$lang_header = array();

		foreach( $languages as $l ) {
			if( false === ( $c = strstr( $l['code'], '-', true ) ) ) {
				$c = $l['code'];
			}

			$langs[ $c ] = $l;
		}

		$store_languages = array();
		if( ! isset( $store_data['lang'] ) ) {
			$store_data['lang'] = array();
		}

		foreach( $store_data['lang'] as $lang_code => $lang_data ) {
			if( $lang = $this->a->get_language( $lang_code ) ) {
				if( false === ( $c = strstr( $lang['code'], '-', true ) ) ) {
					$c = $lang['code'];
				}

				$store_languages[ $c ] = $lang;

				$lang_header[ $c ] = array(
					'id'    => $lang['language_id'],
					'name'  => $lang['name'],
					'image' => $this->a->get_lang_flag_url( $lang ),
				);
			}
		}

		$can_be_added = array_diff_key( $langs, $store_languages );

		if( $can_be_added ) {
			$options = array();
			foreach( $can_be_added as $be_added ) {
				$options[ $be_added['language_id'] ] = $be_added['name'];
			}

			$lang_header[] = array(
				'dropdown' => true,
				'name'     => $this->a->__( 'Add language' ),
				'options'  => $options,
			);
		}

		$ret =
		'<div class="tab-pane" id="' . $id_prefix . $store_id . '">';

		// Remove store button
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Remove store' ),
			'element' => $this->a->r( array(
				'type'        => 'button',
				'title'       => $this->a->__( 'Remove template content for current store' ),
				'icon'        => 'fa-close',
				'button_type' => 'danger',
				'custom_data' => 'data-value="' . $store_id . '"',
				'class'       => 'delete-store',
			) )
		) );

		$profiles = $this->profiles_to_select();
		$profiles[-1 ] = $this->a->__( "Default" );

		// Profile mapping select
		$ret .= $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Profile' ),
			'label_for' => 'available-profile',
			'cols'      => array( 'col-sm-2', 'col-sm-10' ),
			'element'   => $this->a->r( array(
				'type'        => 'select',
				'class'       => 'form-control mail-content available-profile',
				'value'       => $profiles,
				'active'      => isset( $store_data['profile'] ) ? $store_data['profile'] : -1,
				'custom_data' => 'data-type="profile"'
			) )
		) );

		// tip section
		$ret .= $this->a->r()->render_form_group( array(
			'label'     => ' ',
			'label_for' => '',
			'cols'      => array( 'col-sm-2', 'col-sm-10' ),
			'element'   => $this->a->r()->render_form_group( $this->a->__( 'You can map the template to a specific profile on the store level. To do so select profile name from the dropdown above. To use mapping from the "Profile mapping tab" select "Default". This mapping will have priority over mapping, made on the "Profile mapping tab"' ) ),
		) );

		// Cc
		$ret .= $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Carbon copy' ),
			'label_for' => '',
			'cols'      => array( 'col-sm-2', 'col-sm-10' ),
			'element'   => $this->a->r( array(
				'type'         => 'inputgroup',
				'addon_before' => 'Cc',
				'element'      => array(
					'type'        => 'text',
					'class'       => 'form-control mail-content',
					'value'       => isset( $store_data['cc'] ) ? $store_data['cc'] : '',
					'custom_data' => 'data-type="cc"',
				)
			) ),
			'description' => $this->a->__( 'Comma separated list of recipients' ),
		) );

		// Bcc
		$ret .= $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Blind carbon copy' ),
			'label_for' => '',
			'cols'      => array( 'col-sm-2', 'col-sm-10' ),
			'element'   => $this->a->r( array(
				'type'         => 'inputgroup',
				'addon_before' => 'Bcc',
				'element'      => array(
					'type'        => 'text',
					'class'       => 'form-control mail-content',
					'value'       => isset( $store_data['cc'] ) ? $store_data['bcc'] : '',
					'custom_data' => 'data-type="bcc"',
				),
			) ),
			'description' => $this->a->__( 'Comma separated list of recipients' ),
		) );

		// From (name) field
		$ret .= $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'From (name)' ),
			'label_for' => '',
			'cols'      => array( 'col-sm-2', 'col-sm-10' ),
			'element'   => $this->a->r( array(
				'type'         => 'inputgroup',
				'addon_before' => '&lt;Name&gt;',
				'element'      => array(
					'type'        => 'text',
					'class'       => 'form-control mail-content',
					'value'       => isset( $store_data['from_name'] ) ? $store_data['from_name'] : '',
					'custom_data' => 'data-type="from_name"',
				),
			) ),
			'description' => $this->a->__( 'Leave blank to use default value' ),
		) );

		// From (email) field
		$ret .= $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'From (email)' ),
			'label_for' => '',
			'cols'      => array( 'col-sm-2', 'col-sm-10' ),
			'element'   => $this->a->r( array(
				'type'         => 'inputgroup',
				'addon_before' => '<i class="fa fa-at"></i>',
				'element'      => array(
					'type'        => 'text',
					'class'       => 'form-control mail-content',
					'value'       => isset( $store_data['from_email'] ) ? $store_data['from_email'] : '',
					'custom_data' => 'data-type="from_email"',
				),
			) ),
			'description' => $this->a->__( 'Leave blank to use default value' ),
		) );

		// Return-to
		$ret .= $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Return path' ),
			'label_for' => '',
			'cols'      => array( 'col-sm-2', 'col-sm-10' ),
			'element'   => $this->a->r( array(
				'type'         => 'inputgroup',
				'addon_before' => '<i class="fa fa-at"></i>',
				'element'      => array(
					'type'        => 'text',
					'class'       => 'form-control mail-content',
					'value'       => isset( $store_data['return_path'] ) ? $store_data['return_path'] : '',
					'custom_data' => 'data-type="return_path"',
				),
			) ),
			'description' => $this->a->__( 'Specifies where reply should be sent' ),
		) );

		$key = uniqid();

		// Add attachment button
		$ret .= $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Attachment' ),
			'label_for' => '',
			'cols'      => array( 'col-sm-2', 'col-sm-10' ),
			'element'   => $this->a->r( array(
				'type'        => 'button',
				'css'         => 'font-weight: bold',
				'icon'        => 'fa-plus',
				'text_after'  => $this->a->__( 'Add' ),
				'button_type' => 'primary',
				'class'       => 'attachment',
				'custom_data' => 'data-key="' . $key . '"',
			) ),
		) );

		// Attachment
		$ret .= $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Attachments list' ),
			'label_for' => '',
			'cols'      => array( 'col-sm-2', 'col-sm-10' ),
			'css'       => 'display: none',
			'element'   => $this->a->r( array(
				'type'        => 'hidden',
				'value'       => isset( $store_data['attachment'] ) ? $store_data['attachment'] : '',
				'custom_data' => 'data-type="attachment" data-key="' . $key . '"',
				'class'       => 'mail-content attachment-field',
			) ),
		) );

		// Lang tab headers
		$ret .= $this->a->r()->render_panels_headers( array(
			'panels'       => $lang_header,
			'id_prefix'    => $id_prefix . $store_id  . '-',
			'class'        => 'lang-tab-headers', 
		) );

		$ret .=
		'<div class="tab-content template-lang">';

		foreach( $store_data['lang'] as $lang_code => $lang_data ) {
			$ret .= $this->get_lang_tab_content( $lang_code, $lang_data, $id_prefix . $store_id, $store_id, $template_id );
		}

		$ret .=
		'</div>'; // .tab-content (lang)

		$ret .=
		'</div>'; // .tab-pane (store)

		return $ret;
	}

	/**
	 * Renders template language tab's contents 
	 * @param string $lang_code Language code 
	 * @param array $lang_data Language part of template's data
	 * @param string $prefix Prefix string
	 * @param int $store_id Store ID 
	 * @param int $template_id Current template ID 
	 * @return string Language tab's contents
	 */
	public function get_lang_tab_content( $lang_code, $lang_data, $prefix, $store_id, $template_id ) {
		$ret =
		'<div class="tab-pane" id="' . $prefix . '-' . $lang_code . '">';

		// Remove language button
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Remove template language' ),
			'element' => $this->a->r( array(
				'type'        => 'button',
				'title'       => $this->a->__( 'Remove template content for current language' ),
				'icon'        => 'fa-close',
				'button_type' => 'danger',
				'custom_data' => 'data-value="' . $lang_code . '"',
				'class'       => 'delete-lang',
			) )
		) );

		$profiles = $this->profiles_to_select();
		$profiles[-1 ] = $this->a->__( "Default" );

		// Profile mapping select
		$ret .= $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Profile' ),
			'label_for' => 'available-profile',
			'cols'      => array( 'col-sm-2', 'col-sm-10' ),
			'element'   => $this->a->r( array(
				'type'        => 'select',
				'class'       => 'form-control mail-content available-profile',
				'value'       => $profiles,
				'active'      => isset( $lang_data['profile'] ) ? $lang_data['profile'] : -1,
				'custom_data' => 'data-type="profile"',
			) )
		) );

		// Tip section
		$ret .= $this->a->r()->render_form_group( array(
			'label'     => ' ',
			'label_for' => '',
			'cols'      => array( 'col-sm-2', 'col-sm-10' ),
			'element'   => $this->a->r()->render_form_group( $this->a->__( 'You can map the template to a specific profile on the language level. To do so select profile name from the dropdown above. To use the store level mapping select "Default". This mapping will have priority over the store level mapping' ) ),
		) );

		$profile = $this->a->get_template_profile( $store_id, $lang_code, $template_id );

		if( ! $profile ) {
			throw new \Advertikon\Exception( $this->a->__( 'Profile\'s data missing' ) );
		}

		// Mail subject
		$ret .= $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Subject' ),
			'label_for' => '',
			'cols'      => array( 'col-sm-2', 'col-sm-10' ),
			'element'   => $this->a->r( array(
				'type'        => 'textarea',
				'class'       => 'form-control mail-subject mail-content shortcode-able oneline',
				'value'       => isset( $lang_data['content']['subject'] ) ? $lang_data['content']['subject'] : '',
				'custom_data' => 'data-type="subject" data-height="35"',
				'placeholder' => $this->a->__( 'Leave blank to use system value' ),
			) ),
			'description' => $this->a->__( 'Letter subject.' ),
		) );

		// Mail contents
		if( in_array( 'content', $profile['content_fields'] ) ) {
			$ret .= $this->a->r()->render_form_group( array(
				'label'   => $this->a->__( 'Content' ),
				'element' => $this->a->r( array(
					'type'        => 'textarea',
					'value'       => isset( $lang_data['content']['content'] ) ?
						$lang_data['content']['content'] : '',
					'custom_data' => 'data-type="content"',
					'class'       => 'mail-content shortcode-able',
				) ),
				'description' => $this->a->__( 'The main contents of the template.' ),
			) );
		}

		$ret .=
		'</div>'; // .tab-pane (lang)

		return $ret;
	}

	/**
	 * Returns products for auto-fill field 
	 * @param string $q_string Product query string 
	 * @param int $start Start point
	 * @param int $limit Limit 
	 * @return array
	 */
	public function get_product_autofill( $q_string, $start = 0, $limit = 10 ) {
		$ret = array();
		$q_string = str_replace( array( '%', '_' ), array( '\\%', '\\_' ), $q_string );

		$query = $this->db->query(
			"SELECT
				SQL_CALC_FOUND_ROWS
				`pd`.`name` as 'text',
				`p`.`product_id` as 'id',
				`p`.`image` as 'image'
			FROM `" . DB_PREFIX . "product` as p
			LEFT JOIN `" . DB_PREFIX . "product_description` pd
				USING (`product_id`)
			WHERE `pd`.`name` LIKE '%" . $this->db->escape( $q_string ) . "%'
				AND `pd`.`language_id` = " . (int)$this->config->get( 'config_language_id' ) . "
			LIMIT " . (int)$start . ", " . (int)$limit
		);

		if( $query->num_rows ) {
			$ret['products'] = $query->rows;
			$count = $this->db->query( "SELECT FOUND_ROWS()" );
			$ret['total_count'] = $count->row['FOUND_ROWS()'];

		} else {
			$ret['products'] = array();
			$ret['total_count'] = 0;
		}

		return $ret;
	}

	/**
	 * Returns products list to be used in select element
	 * @param int|array $product_id Product(s) ID
	 * @return array
	 */
	public function get_product_for_select( $product_id ) {
		$ret = array();

		if( is_numeric( $product_id ) || is_array( $product_id ) ) {
			$query = $this->db->query(
				"SELECT `pd`.`name` as 'text', `p`.`product_id` as 'id'
				FROM `" . DB_PREFIX . "product` as p
				LEFT JOIN `" . DB_PREFIX . "product_description` pd
					USING (`product_id`)
				WHERE `p`.`product_id` IN (" . implode(',', (array)$product_id ) . ")
					AND `pd`.`language_id` = " . (int)$this->config->get( 'config_language_id' )
			);

			if( $query->num_rows ) {
				foreach( $query->rows as $row ) {
					$ret[ $row['id'] ] = $row['text'];
				}
			}

		} else {
			trigger_error( 'Invalid product iD: ' . $product_id );
		}

		return $ret;
	}

	/**
	 * Returns list of appearances for social media icons to use in select element
	 * @return array
	 */
	public function get_social_appearances() {
		$ret = array();
		$folder = DIR_IMAGE . 'social';

		foreach( scandir( $folder ) as $f ) {
			if ( substr( $f, 0, 1 ) === '.' ) {
				continue;
			}

			if ( is_file( $folder . '/' . $f ) ) {
				continue;
			}

			$ret[ $f ] = $this->appearance_name( $f );
		}

		return $ret;
	}

	/**
	 * Returns name for social icons set
	 * @param string $name Folder name
	 * @return string
	 */
	public function appearance_name( $name ) {
		return ucfirst( str_replace( '_', ' ', $name ) );
	}

	/**
	 * Saves profile mappings
	 * @since 1.1.0 - rebuilt
	 * @param array $data Configuration data
	 * @return boolean Operation status
	 */
	public function save_profile_mapping( $data ) {
		$level = $this->db->escape( $level );
		$id = $this->db->escape( $id );
		$profile_id = (int)$profile_id;

		$this->db->query(
			"DELETE FROM `" . DB_PREFIX . $this->a->profile_mapping_table . "`
			WHERE `level` = '$level'
				AND `id` = '$id' "
		);

		if( $profile_id > -1 ) {
			$this->db->query(
				"INSERT INTO `" . DB_PREFIX . $this->a->profile_mapping_table . "`
				(`level`,`id`,`profile_id`)
				VALUES('$level', '$id', '$profile_id' )"
			);

		} else {
			return true;
		}

		return $this->db->countAffected() > 0;
	}

	/**
	 * Saves setting value
	 * @param string $name Setting name 
	 * @param mixed $value Setting value 
	 * @param int $store_id Store ID, optional
	 * @return boolean Operation status
	 */
	public function set_setting( $name, $value, $store_id = 0 ) {
		$serialized = 0;
		$ret = false;

		if( ! is_scalar( $value ) ) {
			$value = json_encode( $value );
			$serialized = 1;
		}

		if( is_null( $this->a->config( $name ) ) ) {
			$query = $this->db->query(
				"INSERT INTO `" . DB_PREFIX . "setting`
				SET
					`store_id` = " . (int)$store_id . ",
					`code` = '" . $this->a->code . "',
					`key` = '" . $this->db->escape( $this->a->prefix_name( $name ) ) . "',
					`value` = '" . $this->db->escape( $value ) . "',
					`serialized` = $serialized"
			);

			$ret = $this->db->countAffected() > 0;

		} else {
			$query = $this->db->query(
				"UPDATE `" . DB_PREFIX . "setting`
				SET
					`value` = '" . $this->db->escape( $value ) . "',
					`serialized` = $serialized
				WHERE `code` = '" . $this->a->code . "'
					AND `store_id` = " . (int)$store_id . "
					AND `key` = '" . $this->db->escape( $this->a->prefix_name( $name ) ) . "'"
			);

			$ret = true;
		}

		if( $this->db->countAffected() > 0 ) {
			$this->config->set( $this->a->prefix_name( $name ), $value );
		}

		return $ret;
	}

	/**
	 * Renders color scheme picker element
	 * @param string $id Element ID
	 * @return string
	 */
	public function render_color_scheme_picker( $id ) {
		$element = <<<HTML
<div class="input-group color-scheme-picker" id="$id">
	<div class="input-group-btn">
		<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"
			aria-haspopup="true" aria-expanded="false">
			{$this->a->__( 'Color scheme' )} <span class="caret"></span>
		</button>
		<ul class="dropdown-menu">
			<li><a id="schem-anal" href="#">{$this->a->__( 'Analogous' )}</a></li>
			<li><a id="schem-mono" href="#">{$this->a->__( 'Monochromatic' )}</a></li>
			<li><a id="schem-split" href="#">{$this->a->__( 'Split complement' )}</a></li>
			<li><a id="schem-triad" href="#">{$this->a->__( 'Triad' )}</a></li>
			<li><a id="schem-tetrad" href="#">{$this->a->__( 'Tetrad' )}</a></li>
			<li><a id="schem-compl" href="#">{$this->a->__( 'Complement' )}</a></li>
		</ul>
	</div>
	<input type="text" class="form-control iris">
	<span class="input-group-addon"><i class="fa fa-paint-brush"></i></span>
</div>
HTML;

		return $this->a->r()->render_form_group( array(
			'label' => $this->a->__( 'Color scheme' ),
			'description' => $this->a->__( 'Change the color scheme in one click. Pick color scheme and set the main color' ),
			'element' => $element,
		) );
	}

	/**
	 * Removes archive entries
	 * @param int $days Expiration days number
	 * @return array Operation status data
	 */
	public function clear_archive( $days ) {
		$size = 0;
		$count = 0;
		$date_border = new DateTime( sprintf( '- %s days', $days ) );
		$border_timestamp = $date_border->getTimestamp();
		$fs = new \Advertikon\Fs();

		$fs->iterate_directory( $this->a->archive_dir, function( $file ) use( &$size, &$count, $border_timestamp, $fs ) {
			if( is_file( $file ) && filemtime( $file ) < $border_timestamp ) {
				$f_size = filesize( $file );

				if ( unlink( $file ) ) {
					$count++;
					$size += $f_size;
				}

			} else {
				if ( $file !== $this->a->archive_dir ) {
					@rmdir( $file );
				}
			}
		} );

		return array(
			'count'    => $count,
			'size'     => $size,
			'dir_size' => $this->a->format_bytes( $fs->get_dir_size( $this->a->archive_dir ) )
		);
	}

	/**
	 * Formats file list: puts each file name at new line and adds mime icon
	 * @param string $attchments Comma separated list of files
	 * @return string
	 */
	public function format_files( $attachments ) {
		$format = '';

		if ( ! $attachments ) {
			return $format;
		}

		$fs = new \Advertikon\Fs();

		foreach( explode( ',', (string)$attachments ) as $attachment ) {
			$attachment = trim( $attachment );
			$mime = 'undefined';

			if ( is_file( $this->a->attachments_root . $attachment ) &&
				function_exists( 'mime_content_type' ) ) {

				$mime = mime_content_type( $this->a->attachments_root . $attachment );
			}

			$format[] = '<i class="fa fa-2x fa-' . $fs->get_mime_icon( $mime ) .
				'"></i> <span style="vertical-align: super;">' . $attachment . '</span>';
		}

		return implode( '<br>', $format );
	}

	/**
	 * Cleans email history table
	 * @param string $id Comma separated list of IDs
	 * @return boolean Operation status
	 */
	public function clean_history( $ids = null ) {
		if ( is_null( $ids ) ) {
			$this->db->query( "TRUNCATE TABLE `" . DB_PREFIX . $this->a->history_table . "`" );
			
		} else {
			$this->db->query( "DELETE FROM `" . DB_PREFIX . $this->a->history_table . "` WHERE `id` IN (" . $ids . ")" );
		}

		return $this->db->countAffected() > 0;
	}

	/**
	 * Returns list of order statuses for which template is missing
	 * @return array
	 */
	public function get_missed_order_status_templates() {
		$this->load->model( 'localisation/order_status' );
		$order_statuses = $this->model_localisation_order_status->getOrderStatuses();
		$templates = $this->a->q( array(
			'table' => $this->a->templates_table,
			'query' => 'select',
			'where' => array(
				'operation' => 'like',
				'field'     => 'hook',
				'value'     => 'customer.order.update.%'
			),
		) );

		$existing_templates = array();
		if ( $templates ) {
			foreach( $templates as $template ) {
				if ( preg_match( '/\.(\d+)$/', $template['hook'], $m ) ) {
					$existing_templates[] = $m[1];
				}
			}
		}

		$missed_templates = array();
		foreach( $order_statuses as $order_status ) {
			if ( ! in_array( $order_status['order_status_id'], $existing_templates ) ) {
				$missed_templates[ $order_status['order_status_id'] ] = $order_status['name'];
			}
		}

		return $missed_templates;
	}

	/**
	 * Returns list of return statuses for which template is missing
	 * @return array
	 */
	public function get_missed_return_status_templates() {
		$this->load->model( 'localisation/return_status' );
		$order_statuses = $this->model_localisation_return_status->getReturnStatuses();
		$templates = $this->a->q( array(
			'table' => $this->a->templates_table,
			'query' => 'select',
			'where' => array(
				'operation' => 'like',
				'field'     => 'hook',
				'value'     => 'customer.addreturnhistory.%'
			),
		) );

		$existing_templates = array();
		if ( $templates ) {
			foreach( $templates as $template ) {
				if ( preg_match( '/\.(\d+)$/', $template['hook'], $m ) ) {
					$existing_templates[] = $m[1];
				}
			}
		}

		$missed_templates = array();
		foreach( $order_statuses as $order_status ) {
			if ( ! in_array( $order_status['return_status_id'], $existing_templates ) ) {
				$missed_templates[ $order_status['return_status_id'] ] = $order_status['name'];
			}
		}

		return $missed_templates;
	}

	/**
	 * Returns subscribers by its orders
	 * @param array $products Product's IDs 
	 * @param boolean $subscribers Flag, to restrict to newsletter subscribers only, default false 
	 * @param int $start Start offset, optional 
	 * @param int $limit Query limit, optional
	 * @return array
	 */
	public function get_order_subscribers( $products, $subscribers = false, $start = null, $limit = null ) {
		$implode = array();

		foreach ($products as $product_id) {
			$implode[] = "op.product_id = '" . (int)$product_id . "'";
		}

		$query = "SELECT DISTINCT `c`.`email`, `c`.`firstname` as `firstname`, `c`.`lastname` as `lastname` FROM `" . DB_PREFIX . "order` `o` LEFT JOIN `" . DB_PREFIX . "order_product` `op` ON (`o`.`order_id` = `op`.`order_id`) LEFT JOIN `" . DB_PREFIX . "customer` `c` ON (`o`.`email` = `c`.`email`) WHERE `op`.`product_id` IN (" . implode( ',', array_map( array( $this->a->q(), 'escape_db_value' ), (array)$products ) ) . ") AND `o`.`order_status_id` <> '0'";

		if ( $subscribers ) {
			$query .= " AND `c`.`newsletter` = 1";
		}

		if ( is_numeric( $limit ) ) {
			if ( is_numeric( $start ) ) {
				$start = (int)$start;

			} else {
				$start = 0;
			}

			$limit = (int)$limit;

			$query .= " LIMIT $start, $limit";
		}

		$q = $this->db->query( $query );

		return $q->rows;
	}

	/**
	 * @see admin/model/customer/customer.php
	 */
	public function get_total_customers( $data = array() ) {
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "customer";

		$implode = array();

		// TODO: escape it
		if ( ! empty( $data['customer_id'] )  ) {
			$implode[] = "customer_id in (" . implode( ', ', $data['customer_id'] ) . ")";
		}

		if (!empty($data['filter_name'])) {
			$implode[] = "CONCAT(firstname, ' ', lastname) LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_email'])) {
			$implode[] = "email LIKE '" . $this->db->escape($data['filter_email']) . "%'";
		}

		if (isset($data['filter_newsletter']) && !is_null($data['filter_newsletter'])) {
			$implode[] = "newsletter = '" . (int)$data['filter_newsletter'] . "'";
		}

		if (!empty($data['filter_customer_group_id'])) {
			$implode[] = "customer_group_id = '" . (int)$data['filter_customer_group_id'] . "'";
		}

		if (!empty($data['filter_ip'])) {
			$implode[] = "customer_id IN (SELECT customer_id FROM " . DB_PREFIX . "customer_ip WHERE ip = '" . $this->db->escape($data['filter_ip']) . "')";
		}

		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$implode[] = "status = '" . (int)$data['filter_status'] . "'";
		}

		if (isset($data['filter_approved']) && !is_null($data['filter_approved'])) {
			$implode[] = "approved = '" . (int)$data['filter_approved'] . "'";
		}

		if (!empty($data['filter_date_added'])) {
			$implode[] = "DATE(date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}

		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	/**
	 * @see admin/model/marketing/affiliate.php
	 */
	public function get_affiliates($data = array()) {
		$sql = "SELECT *, CONCAT(a.firstname, ' ', a.lastname) AS name, (SELECT SUM(at.amount) FROM " . DB_PREFIX . "affiliate_transaction at WHERE at.affiliate_id = a.affiliate_id GROUP BY at.affiliate_id) AS balance FROM " . DB_PREFIX . "affiliate a";

		$implode = array();

		// TODO: escape it
		if ( ! empty( $data['affiliate_id'] )  ) {
			$implode[] = "affiliate_id in (" . implode( ', ', $data['affiliate_id'] ) . ")";
		}

		if (!empty($data['filter_name'])) {
			$implode[] = "CONCAT(a.firstname, ' ', a.lastname) LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_email'])) {
			$implode[] = "LCASE(a.email) = '" . $this->db->escape(utf8_strtolower($data['filter_email'])) . "'";
		}

		if (!empty($data['filter_code'])) {
			$implode[] = "a.code = '" . $this->db->escape($data['filter_code']) . "'";
		}

		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$implode[] = "a.status = '" . (int)$data['filter_status'] . "'";
		}

		if (isset($data['filter_approved']) && !is_null($data['filter_approved'])) {
			$implode[] = "a.approved = '" . (int)$data['filter_approved'] . "'";
		}

		if (!empty($data['filter_date_added'])) {
			$implode[] = "DATE(a.date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}

		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}

		$sort_data = array(
			'name',
			'a.email',
			'a.code',
			'a.status',
			'a.approved',
			'a.date_added'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY name";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	/**
	 * @see admin/model/customer/customer.php
	 */
	public function get_customers($data = array()) {
		$sql = "SELECT *, CONCAT(c.firstname, ' ', c.lastname) AS name, cgd.name AS customer_group FROM " . DB_PREFIX . "customer c LEFT JOIN " . DB_PREFIX . "customer_group_description cgd ON (c.customer_group_id = cgd.customer_group_id) WHERE cgd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		$implode = array();

		// TODO: escape it
		if ( ! empty( $data['customer_id'] )  ) {
			$implode[] = "c.customer_id in (" . implode( ', ', $data['customer_id'] ) . ")";
		}

		if (!empty($data['filter_name'])) {
			$implode[] = "CONCAT(c.firstname, ' ', c.lastname) LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_email'])) {
			$implode[] = "c.email LIKE '" . $this->db->escape($data['filter_email']) . "%'";
		}

		if (isset($data['filter_newsletter']) && !is_null($data['filter_newsletter'])) {
			$implode[] = "c.newsletter = '" . (int)$data['filter_newsletter'] . "'";
		}

		if (!empty($data['filter_customer_group_id'])) {
			$implode[] = "c.customer_group_id = '" . (int)$data['filter_customer_group_id'] . "'";
		}

		if (!empty($data['filter_ip'])) {
			$implode[] = "c.customer_id IN (SELECT customer_id FROM " . DB_PREFIX . "customer_ip WHERE ip = '" . $this->db->escape($data['filter_ip']) . "')";
		}

		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$implode[] = "c.status = '" . (int)$data['filter_status'] . "'";
		}

		if (isset($data['filter_approved']) && !is_null($data['filter_approved'])) {
			$implode[] = "c.approved = '" . (int)$data['filter_approved'] . "'";
		}

		if (!empty($data['filter_date_added'])) {
			$implode[] = "DATE(c.date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}

		if ($implode) {
			$sql .= " AND " . implode(" AND ", $implode);
		}

		$sort_data = array(
			'name',
			'c.email',
			'customer_group',
			'c.status',
			'c.approved',
			'c.ip',
			'c.date_added'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY name";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	/**
	 * Returns newsletter statuses to use with select element
	 * @param string $table Table to fetch from
	 * @return array
	 */
	public function get_newsletter_statuses_for_select( $table ) {
		$st = array();

		$statuses = $this->a->q( array(
			'table'    => $table,
			'distinct' => true,
			'fields'   => 'status',
		) );

		if ( count( $statuses ) ) {
			foreach( $statuses as $status ) {
				$st[ $status['status'] ] = $this->a->get_status_name( $status['status'] );
			}
		}

		return $st;
	}

	/**
	 * Returns OpenCart newsletters
	 * @return array
	 */
	public function get_oc_newsletters() {
		$query = $this->a->q( array(
			'table'    => 'customer',
			'query'    => 'select',
			'fields'   => array(
				'sid'    => 'store_id',
				'active' => $this->a->create_query( array(
					'table' => 'customer',
					'query' => 'select',
					'count' => '*',
					'where' => array(
						array(
							'field'     => 'newsletter',
							'operation' => '=',
							'value'     => '1'
						),
						array(
							'field'     => 'store_id',
							'operation' => '=',
							'value'     => 'sid',
						),
					),
				) ),
				'inactive' => $this->a->create_query( array(
					'table' => 'customer',
					'query' => 'select',
					'count' => '*',
					'where' => array(
						array(
							'field'     => 'newsletter',
							'operation' => '=',
							'value'     => '0'
						),
						array(
							'field'     => 'store_id',
							'operation' => '=',
							'value'     => 'sid',
						),
					),
				) ),
			),
			'group_by' => 'store_id', 
		) );

		if ( $query ) {
			$this->load->model( 'setting/store' );
			$stores = $this->model_setting_store->getStores();
			$store_name = array( $this->config->get( 'config_name' ) );

			foreach( $stores as $st_name ) {
				$store_name[ $st_name['store_id'] ] = $st_name['name'];
			}

			$sn = array();

			foreach( $query as $store ) {
				$sn['id']          = 'oc-' . $store['sid'];
				$sn['date_added']  = '';
				$sn['name']        = $store_name[ $store['sid'] ];
				$sn['description'] = $this->a->__( 'OpenCart newsletter' );
				$sn['status']      = empty( $store['active'] ) ? 0 : 1;
				$sn['widget']      = '';
				$sn['active']      = $store['active'];
				$sn['inactive']    = $store['inactive'];
			}

			$ret[] = $sn;
		}

		return $ret;
	}

	/**
	 * Returns newsletter's filter's fields autofill values
	 * @param string $type Field name 
	 * @param string $query Query string
	 * @param int $start Start offset
	 * @param int $count Result count 
	 * @return array
	 */
	public function get_newsletter_autofill( $field, $query, $start, $count ) {
		$ret = array( 'filter' => array(), 'total_count' => 0 );

		$query = '%' . str_replace( array( '%', '_' ), array( '\\%', '\\_' ), $query ) . '%';

		if ( 'widget' === $field ) {
			$field_id = '`l`.`' . $field . '`';
			$field_name = '`w`.`name`';
			$field_where = $field_name;

		} else {
			$field_id = '\`l\`.\`' . $field . '\`';
			$field_name = $field_id;
			$field_where = $field_name;
		}

		$result = $this->db->query(
			"SELECT SQL_CALC_FOUND_ROWS DISTINCT " . $field_id . " as 'id', " . $field_name . " as 'text'
			FROM  `" . DB_PREFIX . $this->a->newsletter_list_table . "` `l`
			LEFT JOIN `" . DB_PREFIX . $this->a->newsletter_widget_table . "` `w`
				ON ( `l`.`widget` = `w`.`id` )
			WHERE " . $field_where . " LIKE ('" . $query . "')
			LIMIT " . (int)$start . ', ' . (int)$count
		);

		if ( $result && isset( $result->rows ) ) {
			$ret['filter'] = $result->rows;

			$calc = $this->a->q( array(
				'table'    => $this->a->newsletter_subscribers_table,
				'query'    => 'select',
				'function' => 'found_rows'
			) );

			if ( $calc ) {
				$ret['total_count'] = $calc['FOUND_ROWS()'];
			}
		}

		return $ret;
	}

	/**
	 * Returns total count of subscribers
	 * @param array $data Query data
	 * @return int
	 */
	public function get_total_subscribers( $data ) {
		$data['function'] = 'count(*)';
		$result = $this->a->q( $data );
		return $result['COUNT(*)'];
	}

	/**
	 * Returns subscribers
	 * @param array $data Query data
	 * @return array
	 */
	public function get_subscribers( $data ) {
		return $this->a->q( $data );
	}

	/**
	 * Returns list of available widgets for particular newsletter
	 * @param int $newsletter_id Newsletter ID
	 * @return array
	 */
	public function get_avail_widgets( $newsletter_id , $active ) {
		$ret = array( '-1' => $this->a->__( 'Select a widget' ) );

		$query = $this->db->query(
			"SELECT `w`.`name`, `w`.`id`, `n2w`.`newsletter_id`
			FROM  `" . DB_PREFIX . $this->a->newsletter_widget_table . "` w
				LEFT JOIN `" . DB_PREFIX . $this->a->newsletter_to_widget_table . "` n2w
					ON (`w`.`id` = `n2w`.`widget_id`)
			WHERE `n2w`.`newsletter_id` IS NULL OR `n2w`.`newsletter_id` = " . (int)$newsletter_id );

		if ( $query && $query->num_rows > 0 ) {
			foreach( $query->rows as $row ) {
				if ( $row['newsletter_id'] ) {
					$active['active'] = $row['id'];
				}
				
				$ret[ $row['id'] ] = $row['name'];
			}
		}

		return $ret;
	}

	/**
	 * Subscribers filter's autofill data
	 * @param string $field Field name 
	 * @param string $query Query string 
	 * @param int $start Start offset
	 * @param int $count Limit 
	 * @param int $id Newsletter ID
	 * @return array
	 */
	public function get_subscribers_autofill( $field, $query, $start, $count, $id ) {
		$ret = array(
			'filter'      => array(),
			'total_count' => 0,
		);

		$query = '%' . str_replace(
			array( '_', '%' ),
			array( '\\_', '\\%' ),
			$query
		) . '%';

		$result = $this->a->q( array(
			'table'    => $this->a->newsletter_subscribers_table,
			'query'    => 'select',
			'fields'   => array(
				'id'   => $field,
				'text' => $field,
 			),
			'distinct' => true,
			'calc'     => true,
			'where'    => array(
				array(
					'field'     => $field,
					'operation' => 'like',
					'value'     => $query,
				),
				array(
					'field'     => 'newsletter',
					'operation' => '=',
					'value'     => $id,
				),
			),
			'limit'    => $count,
			'start'    => $start, 
		) );

		if ( count( $result ) ) {
			$ret['filter'] = $result->getArrayCopy();

			$calc = $this->a->q( array(
				'table'    => $this->a->newsletter_subscribers_table,
				'query'    => 'select',
				'function' => 'found_rows'
			) );

			if ( $calc ) {
				$ret['total_count'] = $calc['FOUND_ROWS()'];
			}
		}

		return $ret;
	}

	/**
	 * Renders content of newsletter's captions tab
	 * @return string
	 */
	public function render_newsletter_caption_tab() {
		$ret = '';

		$ret .= $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Unsubscribe - cancellation code is missing' ),
			'label_for' => '', 
			'cols'      => array( 'col-sm-2', 'col-sm-10' ),
			'element'   => $this->a->r( array(
				'type' => 'lang_set',
				'element'   => array(
					'type'        => 'textarea',
					'class'       => 'form-control',
					'value'       => $this->a->__( 'The cancellation code is missing' ),
					'name'        => 'caption_cancellation_code_missing',
				),
			) ),
			'description' => $this->a->__( 'Message to show to newsletter subscriber on attempt to cancel subscription when code is missing' ),
		) );

		$ret .= $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Unsubscribe - cancellation code is expired' ),
			'label_for' => '', 
			'cols'      => array( 'col-sm-2', 'col-sm-10' ),
			'element'   => $this->a->r( array(
				'type' => 'lang_set',
				'element'   => array(
					'type'        => 'textarea',
					'class'       => 'form-control',
					'value'       => $this->a->__( 'The cancellation code has expired or is invalid' ),
					'name'        => 'caption_cancellation_code_expired',
				),
			) ),
			'description' => $this->a->__( 'Message to show to newsletter subscriber on attempt to cancel subscription when code is expired' ),
		) );

		$ret .= $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Unsubscribe - cancellation code is expired' ),
			'label_for' => '', 
			'cols'      => array( 'col-sm-2', 'col-sm-10' ),
			'element'   => $this->a->r( array(
				'type' => 'lang_set',
				'element'   => array(
					'type'        => 'textarea',
					'class'       => 'form-control',
					'value'       => $this->a->__( 'You are not subscribed to the newsletter' ),
					'name'        => 'caption_cancellation_newsletter_missing',
				),
			) ),
			'description' => $this->a->__( 'Message to show to newsletter subscriber on attempt to cancel subscription to which he/she doesn\'t subscribed' ),
		) );

		$ret .= $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Unsubscribe - server error' ),
			'label_for' => '', 
			'cols'      => array( 'col-sm-2', 'col-sm-10' ),
			'element'   => $this->a->r( array(
				'type' => 'lang_set',
				'element'   => array(
					'type'        => 'textarea',
					'class'       => 'form-control',
					'value'       => $this->a->__( 'Sorry, but due to technical reasons subscription cannot be canceled automatically. Contact us to cancel subscription manually'),
					'name'        => 'caption_cancellation_server_error',
				),
			) ),
			'description' => $this->a->__( 'Message to show to newsletter subscriber on attempt to cancel subscription when server error arise' ),
		) );

		$ret .= $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Ubsubscribe - success' ),
			'label_for' => '', 
			'cols'      => array( 'col-sm-2', 'col-sm-10' ),
			'element'   => $this->a->r( array(
				'type' => 'lang_set',
				'element'   => array(
					'type'        => 'textarea',
					'class'       => 'form-control',
					'value'       => $this->a->__( 'Subscription has been canceled successfully'),
					'name'        => 'caption_cancellation_success',
				),
			) ),
			'description' => $this->a->__( 'Message to show to newsletter subscriber on successful cancellation of subscription' ),
		) );

		$ret .= $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Archive missing' ),
			'label_for' => '', 
			'cols'      => array( 'col-sm-2', 'col-sm-10' ),
			'element'   => $this->a->r( array(
				'type' => 'lang_set',
				'element'   => array(
					'type'        => 'textarea',
					'class'       => 'form-control',
					'value'       => $this->a->__( 'Archive email you are searching for is not exists' ),
					'name'        => 'caption_archive_missing',
				),
			) ),
			'description' => $this->a->__( 'Message to show to newsletter subscriber when an email\'s copy to open in browser is missing' ),
		) );

		$ret .= $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Widget - missing email' ),
			'label_for' => '', 
			'cols'      => array( 'col-sm-2', 'col-sm-10' ),
			'element'   => $this->a->r( array(
				'type' => 'lang_set',
				'element'   => array(
					'type'        => 'textarea',
					'class'       => 'form-control',
					'value'       => $this->a->__( 'Invalid format of e-mail address' ),
					'name'        => 'caption_widget_email_error',
				),
			) ),
			'description' => $this->a->__( 'Message to show when email address in widget\'s opt-in box is invalid' ),
		) );

		$ret .= $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Widget - missing name' ),
			'label_for' => '', 
			'cols'      => array( 'col-sm-2', 'col-sm-10' ),
			'element'   => $this->a->r( array(
				'type' => 'lang_set',
				'element'   => array(
					'type'        => 'textarea',
					'class'       => 'form-control',
					'value'       => $this->a->__( 'You need to specify your name' ),
					'name'        => 'caption_widget_name_error',
				),
			) ),
			'description' => $this->a->__( 'Message to show when name in widget\'s opt-in box is missing' ),
		) );

		$ret .= $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Subscribe - missing email' ),
			'label_for' => '', 
			'cols'      => array( 'col-sm-2', 'col-sm-10' ),
			'element'   => $this->a->r( array(
				'type' => 'lang_set',
				'element'   => array(
					'type'        => 'textarea',
					'class'       => 'form-control',
					'value'       => $this->a->__( 'In order to create subscription you need to specify your e-mail address' ),
					'name'        => 'caption_subscribe_email_missing',
				),
			) ),
			'description' => $this->a->__( 'Message to show to newsletter subscriber on subscription attempt, when email address is missing' ),
		) );

		$ret .= $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Subscribe - missing name' ),
			'label_for' => '', 
			'cols'      => array( 'col-sm-2', 'col-sm-10' ),
			'element'   => $this->a->r( array(
				'type' => 'lang_set',
				'element'   => array(
					'type'        => 'textarea',
					'class'       => 'form-control',
					'value'       => $this->a->__( 'In order to create subscription you need to specify your name' ),
					'name'        => 'caption_subscribe_name_missing',
				),
			) ),
			'description' => $this->a->__( 'Message to show to newsletter subscriber on subscription attempt, when subscriber\'s name is missing' ),
		) );

		$ret .= $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Subscribe - server error' ),
			'label_for' => '', 
			'cols'      => array( 'col-sm-2', 'col-sm-10' ),
			'element'   => $this->a->r( array(
				'type' => 'lang_set',
				'element'   => array(
					'type'        => 'textarea',
					'class'       => 'form-control',
					'value'       => $this->a->__( 'Sorry, you cannot be subscribed automatically. Please contact us and we\'ll subscribe you manually' ),
					'name'        => 'caption_subscribe_server_error',
				),
			) ),
			'description' => $this->a->__( 'Message to show to newsletter subscriber on subscription attempt, when server error arise' ),
		) );

		$ret .= $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Subscribe - subscription exists' ),
			'label_for' => '', 
			'cols'      => array( 'col-sm-2', 'col-sm-10' ),
			'element'   => $this->a->r( array(
				'type' => 'lang_set',
				'element'   => array(
					'type'        => 'textarea',
					'class'       => 'form-control',
					'value'       => $this->a->__( 'You already have subscription to the newsletter' ),
					'name'        => 'caption_subscribe_exists',
				),
			) ),
			'description' => $this->a->__( 'Message to show to newsletter subscriber on subscription attempt, when newsletter\'s subscription already exists' ),
		) );

		$ret .= $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Subscribe - double opt-in' ),
			'label_for' => '', 
			'cols'      => array( 'col-sm-2', 'col-sm-10' ),
			'element'   => $this->a->r( array(
				'type' => 'lang_set',
				'element'   => array(
					'type'        => 'textarea',
					'class'       => 'form-control',
					'value'       => $this->a->__( 'Thank you for subscribing to the newsletter. But before you be able receiving it, you need to verify your email address. On your email address has been sent verification code with instructions' ),
					'name'        => 'caption_subscribe_double_opt_in',
				),
			) ),
			'description' => $this->a->__( 'Message to show to newsletter subscriber on subscription attempt, informs that email address confirmation required' ),
		) );

		$ret .= $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Subscribe - success' ),
			'label_for' => '', 
			'cols'      => array( 'col-sm-2', 'col-sm-10' ),
			'element'   => $this->a->r( array(
				'type' => 'lang_set',
				'element'   => array(
					'type'        => 'textarea',
					'class'       => 'form-control',
					'value'       => $this->a->__( 'Thank you for subscribing to the newsletter' ),
					'name'        => 'caption_subscribe_success',
				),
			) ),
			'description' => $this->a->__( 'Message to show to newsletter subscriber on successful subscription attempt' ),
		) );

		$ret .= $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Subscribe confirm - missing code' ),
			'label_for' => '', 
			'cols'      => array( 'col-sm-2', 'col-sm-10' ),
			'element'   => $this->a->r( array(
				'type' => 'lang_set',
				'element'   => array(
					'type'        => 'textarea',
					'class'       => 'form-control',
					'value'       => $this->a->__( 'Confirmation code is missing' ),
					'name'        => 'caption_confirm_missing_code',
				),
			) ),
			'description' => $this->a->__( 'Message to show to newsletter subscriber on attempt to confirm subscription when confirmation code is missing' ),
		) );

		$ret .= $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Subscribe confirm - code is expired' ),
			'label_for' => '', 
			'cols'      => array( 'col-sm-2', 'col-sm-10' ),
			'element'   => $this->a->r( array(
				'type' => 'lang_set',
				'element'   => array(
					'type'        => 'textarea',
					'class'       => 'form-control',
					'value'       => $this->a->__( 'The confirmation code is invalid or has expired. Contact us to subscribe you manually' ),
					'name'        => 'caption_confirm_expire_code',
				),
			) ),
			'description' => $this->a->__( 'Message to show to newsletter subscriber on attempt to confirm subscription when confirmation code has expired' ),
		) );

		$ret .= $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Subscribe confirm - server error' ),
			'label_for' => '', 
			'cols'      => array( 'col-sm-2', 'col-sm-10' ),
			'element'   => $this->a->r( array(
				'type' => 'lang_set',
				'element'   => array(
					'type'        => 'textarea',
					'class'       => 'form-control',
					'value'       => $this->a->__( 'We experiencing temporary problems with an automatic subscription process. Please, contact us to subscribe you manually' ),
					'name'        => 'caption_confirm_server_error',
				),
			) ),
			'description' => $this->a->__( 'Message to show to newsletter subscriber on attempt to confirm subscription when server error arise' ),
		) );

		$ret .= $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Subscribe confirm - missing newsletter' ),
			'label_for' => '', 
			'cols'      => array( 'col-sm-2', 'col-sm-10' ),
			'element'   => $this->a->r( array(
				'type' => 'lang_set',
				'element'   => array(
					'type'        => 'textarea',
					'class'       => 'form-control',
					'value'       => $this->a->__( 'Sorry, but newsletter, subscription to which you are trying to confirm, does not exist or was deleted' ),
					'name'        => 'caption_confirm_missing_newsletter',
				),
			) ),
			'description' => $this->a->__( 'Message to show to newsletter subscriber on attempt to confirm subscription when target newsletter doesn\'t exist' ),
		) );

		$ret .= $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Subscribe confirm - missing subscription' ),
			'label_for' => '', 
			'cols'      => array( 'col-sm-2', 'col-sm-10' ),
			'element'   => $this->a->r( array(
				'type' => 'lang_set',
				'element'   => array(
					'type'        => 'textarea',
					'class'       => 'form-control',
					'value'       => $this->a->__( 'Unfortunately, you have not yet subscribed to our newsletter. Try subscription process from the start or contact us to subscribe you manually' ),
					'name'        => 'caption_confirm_missing_subscription',
				),
			) ),
			'description' => $this->a->__( 'Message to show to newsletter subscriber on attempt to confirm subscription when he/she has no subscriptions at all' ),
		) );

		$ret .= $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Subscribe confirm - wrong subscription\'s status' ),
			'label_for' => '', 
			'cols'      => array( 'col-sm-2', 'col-sm-10' ),
			'element'   => $this->a->r( array(
				'type' => 'lang_set',
				'element'   => array(
					'type'        => 'textarea',
					'class'       => 'form-control',
					'value'       => $this->a->__( 'Sorry, but you don\'t have subscription which needs confirmation' ),
					'name'        => 'caption_confirm_wrong_status',
				),
			) ),
			'description' => $this->a->__( 'Message to show to newsletter subscriber on attempt to confirm subscription when subscription\'s status doesn\'t permit confirmation' ),
		) );

		$ret .= $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Subscribe confirm - success' ),
			'label_for' => '', 
			'cols'      => array( 'col-sm-2', 'col-sm-10' ),
			'element'   => $this->a->r( array(
				'type' => 'lang_set',
				'element'   => array(
					'type'        => 'textarea',
					'class'       => 'form-control',
					'value'       => $this->a->__( 'You have successfully confirmed subscription to the newsletter' ),
					'name'        => 'caption_confirm_success',
				),
			) ),
			'description' => $this->a->__( 'Message to show to newsletter subscriber on successful attempt to confirm subscription' ),
		) );

		$ret .= $this->a->r()->render_form_group( array(
			'label'     => $this->a->__( 'Save' ),
			'label_for' => '',
			'cols'      => array( 'col-sm-2', 'col-sm-10' ),
			'element'   => $this->a->r( array(
				'type'        => 'button',
				'button_type' => 'success',
				'text_after'  => $this->a->__( 'Save' ),
				'icon'        => 'fa-save',
				'custom_data' => 'data-url="' .
					$this->url->link(
						$this->a->type . '/' . $this->a->code . '/save_settings',
						'token=' . $this->session->data['token'],
						'SSL'
					) .
				'"',
				'id'          => 'save-captions',
			) ),
		) );

		return $ret;
	}

	/**
	 * Pre-set extension settings
	 * @return void
	 */
	public function set_settings() {

	}

	/**
	 * Imports subscribers from one newsletter to another, overwriting target data by source data  
	 * @param int $target Target newsletter's ID
	 * @param int $source Source newsletter's ID
	 * @param array $subscribers Subscribers groups IDs
	 * @return int Count of imported records
	 */
	public function import_subscribers_override( $target, $source, $subscribers ) {
		$query_insert = "INSERT INTO `" . DB_PREFIX . $this->a->newsletter_subscribers_table . "` (`name`, `email`,`status`, `date_added`, `newsletter`, `date_unsubscribe`) SELECT `t_from`.`name`, `t_from`.`email`,`t_from`.`status` ,NOW(), '" . (int)$target . "', `t_from`.`date_unsubscribe` FROM `" . DB_PREFIX . $this->a->newsletter_subscribers_table . "` as `t_from` WHERE `t_from`.`newsletter` = " . (int)$source;

		$query_delete = "DELETE `t1` FROM `" . DB_PREFIX . $this->a->newsletter_subscribers_table . "` `t1` JOIN `" . DB_PREFIX . $this->a->newsletter_subscribers_table . "` `t2` ON (`t1`.`email` = `t2`.`email`) WHERE `t1`.`newsletter` = " . (int)$target . " AND `t2`.`newsletter` = " . (int)$source;

		if ( ! in_array( 100, $subscribers ) ) {
			$query_insert .= " AND `t_from`.`status` IN ('" . implode( ', ', $subscribers ) . "')";
			$query_delete .= " AND `t1`.`status` IN (" . implode( ', ', $subscribers ) . ") AND `t1`.`status` IN (" . implode( ', ', $subscribers ) . ")";
		}

		$this->db->query( $query_delete );

		$import = $this->db->query( $query_insert );

		return $this->db->countAffected();
	}

	/**
	 * Imports subscribers from one newsletter to another, without overwriting target data by source data  
	 * @param int $target Target newsletter's ID
	 * @param int $source Source newsletter's ID
	 * @param array $subscribers Subscribers groups IDs
	 * @return int Count of imported records
	 */
	public function import_subscribers( $target, $source, $subscribers ) {
		$query_insert = "INSERT INTO `" . DB_PREFIX . $this->a->newsletter_subscribers_table . "`
		(`name`, `email`,`status`, `date_added`, `newsletter`, `date_unsubscribe`)
		SELECT `t_from`.`name`, `t_from`.`email`,`t_from`.`status`, NOW(), '" . (int)$target . "', `t_from`.`date_unsubscribe`
		FROM `" . DB_PREFIX . $this->a->newsletter_subscribers_table . "` as `t_from`
			LEFT JOIN `" . DB_PREFIX . $this->a->newsletter_subscribers_table . "` as `t_left`
				ON (`t_from`.`email` = `t_left`.`email` AND `t_left`.`newsletter` = " . (int)$target . ")
		WHERE `t_from`.`newsletter` = " . (int)$source . " AND `t_left`.`email` IS NULL";

		if ( ! in_array( 100, $subscribers ) ) {
			$query_insert .= " AND `t_from`.`status` IN ('" . implode( ', ', $subscribers ) . "')";
		}

		$import = $this->db->query( $query_insert );

		return $this->db->countAffected();
	}

	/**
	 * Imports subscribers from CSV file
	 * @param array $in_values Values
	 * @param array $in_names Names
	 * @param int $override Override flag
	 * @return int Count of affected rows
	 */
	public function import_csv( $in_values, $in_names, $override ) {
		$this->db->query( "CREATE TEMPORARY TABLE temp
			(`id` INT UNSIGNED AUTO_INCREMENT KEY,
			 `name` VARCHAR(255),
			 `email` VARCHAR(255),
			 `status` TINYINT UNSIGNED,
			 `date_added` DATETIME,
			 `newsletter` VARCHAR(255),
			 `date_unsubscribe` DATETIME
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin" );

		$self = $this;
		$a = current( $in_values );
		$newsletter = $a[ array_search( 'newsletter', $in_names ) ];
		$email_pos = array_search( 'email', $in_names );
		$del = array();

		$names = '(' . implode(
			',',
			array_map(
				function( $e ) use( $self ) {
					return "`" . $self->db->escape( $e ) . "`";
				},
				$in_names
			)
		) . ')';

		$values = array();

		foreach( $in_values as $val ) {
			$values[] = '(' . implode(
				', ',
				array_map(
					function( $e ) use( $self ) {
						return "'" . $self->db->escape( $e ) . "'";
					},
					$val
				)
			) . ')';

			if ( $override === 1 ) {
				$del[] = "'" . $this->db->escape( $val[ $email_pos ] ) . "'";
			}
		}

		$this->db->query( sprintf( "INSERT INTO temp %s VALUES %s", $names, implode( ',', $values ) ) );

		if ( $override === 1 ) {
			$this->db->query(
				"DELETE FROM `" . DB_PREFIX . $this->a->newsletter_subscribers_table . "`
				WHERE `newsletter` = " . (int)$newsletter . " AND `email` IN (" . implode( ',', $del ) . ")"
			);
		}

		// Insert unique emails for current newsletter
		$this->db->query(
			"INSERT INTO `" . DB_PREFIX . $this->a->newsletter_subscribers_table . "`
			(`name`, `email`,`status`, `date_added`, `newsletter`, `date_unsubscribe`)
			SELECT `t_from`.`name`, `t_from`.`email`,`t_from`.`status`, NOW(), `t_from`.`newsletter`, `t_from`.`date_unsubscribe`
			FROM `temp` as `t_from`
				LEFT JOIN `" . DB_PREFIX . $this->a->newsletter_subscribers_table . "` as `t_left`
					ON (`t_from`.`email` = `t_left`.`email` AND `t_left`.`newsletter` = `t_from`.`newsletter` )
			WHERE `t_left`.`email` IS NULL"
		);

		return $this->db->countAffected();
	}

	/**
	 * Returns content for queue tab
	 * @return string
	 */
	public function render_queue_tab() {
		$ret = '';

		// Queue status
		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Enable queue' ),
			'element' => $this->a->r()->render_fancy_checkbox( array(
				'value' => $this->a->config( 'queue' ),
				'id'    => 'setting-queue',
			) ),
		) );

		// Queue cron task action
		$ret .= $this->a->r()->render_form_group( array(
			'label' => $this->a->__( 'Cron job' ),
			'element' => $this->a->r( array(
				'type'        => 'text',
				'custom_data' => 'readonly="readonly"',
				'class'       => 'clipboard form-control',
				'value'       => 'wget -t 1 -O - ' . $this->a->get_store_url() . 'index.php?route=' . $this->a->type . '/' . $this->a->code . '/cron >/dev/null 2>&1',
			) ),
			'description' => $this->a->__( 'Run this task as server\'s cron job every minute (* * * * *)' ),
		) );

		$query = $this->a->q( array(
			'table'    => $this->a->queue_table,
			'query'    => 'select',
			'field' => array( 'count' => 'count(*)' ),
		) );

		// Queue information
		$ret .= $this->a->r()->render_form_group( array(
			'label' => $this->a->__( 'Queue status' ),
			'element' => '<b>' . $this->a->__( 'Queue length' ) .
			':</b> <span id="queue-length-text">' . $query['count'] . '</span>' .
				' ' . $this->a->__( 'item(s)' ) . '<br>' .
				'<b>' . $this->a->__( 'Status' ) . ':</b> ' .
				( $this->a->get_queue_status() ? '<i style="color:green">' .
				$this->a->__( 'Enabled' ) . '</i>' : $this->a->__( 'Disabled' ) ) ,
			'description' => $this->a->__( 'System needs 1-2 minutes to detect change of queue status' ),
		) );

		$ret .= $this->a->r()->render_form_group( array(
			'label' => $this->a->__( 'Manage queue' ),
			'element' => $this->a->r( array(
				'type' => 'buttongroup',
				'buttons' => array(
					array(
						'type'        => 'button',
						'icon'        => 'fa-rocket',
						'button_type' => 'success',
						'title'       => $this->a->__( 'Manually run the queue' ),
						'text_before' => $this->a->__( 'Run' ),
						'id'          => 'run-queue',
						'custom_data' => 'data-url="' . $this->url->link(
							$this->a->type . '/' . $this->a->code . '/run_queue',
							'token=' . $this->session->data['token'],
							'SSL'
						) . '"',
					),
					array(
						'type'        => 'button',
						'icon'        => 'fa-trash',
						'button_type' => 'danger',
						'title'       => $this->a->__( 'Flush the queue' ),
						'text_before' => $this->a->__( 'Flush' ),
						'id'          => 'flush-queue',
						'custom_data' => 'data-url="' . $this->url->link(
							$this->a->type . '/' . $this->a->code . '/flush_queue',
							'token=' . $this->session->data['token'],
							'SSL'
						) . '"',
					),
				),
			) ),
		) );

		// Sending attempts setting
		$ret .= $this->a->r()->render_form_group( array(
			'label' => $this->a->__( 'Sending attempts' ),
			'element' => $this->a->r( array(
				'type' => 'inputgroup',
				'element' => array(
					'type'        => 'number',
					'value'       => $this->a->config( 'queue_attempts' ),
					'class'       => 'form-control',
					'custom_data' => 'data-name="queue_attempts"',
				),
				'addon_after' => array(
					'type'        => 'button',
					'button_type' => 'primary',
					'icon'        => 'fa-save',
					'class'       => 'simple-setting',
				),
			) ),
			'description' => $this->a->__( 'Number of attempts to send email before remove it from the queue. Set empty to remove limitation' ),
		) );

		// Script run time setting
		$ret .= $this->a->r()->render_form_group( array(
			'label' => $this->a->__( 'Run time' ),
			'element' => $this->a->r( array(
				'type' => 'inputgroup',
				'element' => array(
					'type'        => 'number',
					'value'       => $this->a->config( 'queue_time' ) ? $this->a->config( 'queue_time' ) : 30,
					'class'       => 'form-control',
					'custom_data' => 'data-name="queue_time"',
				),
				'addon_after' => array(
					'type'        => 'button',
					'button_type' => 'primary',
					'icon'        => 'fa-save',
					'class'       => 'simple-setting',
				),
			) ),
			'description' => $this->a->__( 'Time (seconds) granted to script to process queue' ),
		) );

		return $ret;
	}

	/**
	 * Returns chart data
	 * @param int $id Newsletter ID
	 * @param array $where Where clause
	 * @return array
	 */
	public function get_chart_data( $id, $where = array() ) {
		$ret = array();
		$dates = $this->get_period_from_where( $where );

		if ( ! $dates ) {
			return $ret;
		}

		$from = $dates['from'];
		$to = $dates['to'];
		$diff = $from->diff( $to );

		if ( $diff->format( '%a' ) > 2 ) {
			$format = "%Y-%m-%d";

		} else {
			$format = "%Y-%m-%d %H:00";
		}

		$sent_r_q = $this->db->query(
			"SELECT
				COUNT( IF( `status` = " .  \Advertikon\Mail\Advertikon::EMAIL_STATUS_SUCCESS . ", 1, NULL ) ) as `success`,
				COUNT( IF( `status` = " .  \Advertikon\Mail\Advertikon::EMAIL_STATUS_FAIL . ", 1, NULL ) ) as`fail`,
				DATE_FORMAT( `date_added`, '" . $this->db->escape( $format ) . "') as `date` 
				FROM `" . DB_PREFIX . $this->a->history_table . "` " .
			$this->a->q()->create_where_clause( $where ) . "
			GROUP BY `date`"
		);

		$sent_q = new \Advertikon\Db_Result( $sent_r_q->rows );

		$viewed_r_q = $this->db->query(
			"SELECT
				COUNT( `date_viewed` ) as `viewed`,
				DATE_FORMAT( `date_viewed`, '" . $this->db->escape( $format ) . "') as `date` 
				FROM `" . DB_PREFIX . $this->a->history_table . "` " .
			$this->a->q()->create_where_clause( $this->a->q()->merge_where( $where, array(
				'field'     => 'date_viewed',
				'operation' => '<>',
				'value'     => 'NULL',
			) ) ) . "
			GROUP BY `date`"
		);

		$viewed_q = new \Advertikon\Db_Result( $viewed_r_q->rows );

		$visited_r_q = $this->db->query(
			"SELECT
				COUNT( `date_visited` ) as `visited`,
				DATE_FORMAT( `date_visited`, '" . $this->db->escape( $format ) . "') as `date` 
				FROM `" . DB_PREFIX . $this->a->history_table . "` " .
			$this->a->q()->create_where_clause( $this->a->q()->merge_where( $where, array(
				'field'     => 'date_visited',
				'operation' => '<>',
				'value'     => 'NULL',
			) ) ) . "
			GROUP BY `date`"
		);

		$visited_q = new \Advertikon\Db_Result( $visited_r_q->rows );

		$active_r_q = $this->db->query(
			"SELECT
				COUNT( IF( `status` = " .  \Advertikon\Mail\Advertikon::SUBSCRIBER_STATUS_ACTIVE . ", 1, NULL ) ) as `active`,
				DATE_FORMAT( `date_added`, '" . $this->db->escape( $format ) . "') as `date` 
				FROM `" . DB_PREFIX . $this->a->newsletter_subscribers_table . "` " .
			$this->a->q()->create_where_clause( $this->a->q()->merge_where( $where, array(
				'field'     => 'date_added',
				'operation' => '<>',
				'value'     => 'NULL',
			) ) ) . "
			GROUP BY `date`"
		);

		$active_q = new \Advertikon\Db_Result( $active_r_q->rows );

		array_walk_recursive( $where, function( &$v ) {
			if ( 'date_added' === $v ) {
				$v = 'date_unsubscribe';
			}
		} );

		$cancel_r_q = $this->db->query(
			"SELECT
				COUNT( IF( `status` = " .   \Advertikon\Mail\Advertikon::SUBSCRIBER_STATUS_CANCELLED . ", 1, NULL ) ) as `cancel`,
				DATE_FORMAT( `date_unsubscribe`, '" . $this->db->escape( $format ) . "') as `date` 
				FROM `" . DB_PREFIX . $this->a->newsletter_subscribers_table . "` " .
			$this->a->q()->create_where_clause( $this->a->q()->merge_where( $where, array(
				'field'     => 'date_unsubscribe',
				'operation' => '<>',
				'value'     => 'NULL',
			) ) ) . "
			GROUP BY `date`"
		);

		$cancel_q = new \Advertikon\Db_Result( $cancel_r_q->rows );

		$timeline = array();
		$success_d = array();
		$failed_d = array();
		$viewed_d = array();
		$visited_d = array();
		$active_d = array();
		$cancel_d = array();

		if ( count( $sent_q ) ) {
			foreach( $sent_q as $s ) {
				$timeline[] = $s['date'];
				$sent_q['date'] = $this->a->q()->parse_sql_date( $s['date'] );
			}
			
			$sent_q->rewind();
		}

		if ( count( $viewed_q ) ) {
			foreach( $viewed_q as $v ) {
				$timeline[] = $v['date'];
				$viewed_q['date'] = $this->a->q()->parse_sql_date( $v['date'] );
			}
			
			$viewed_q->rewind();
		}

		if ( count( $visited_q ) ) {
			foreach( $visited_q as $v ) {
				$timeline[] = $v['date'];
				$visited_q['date'] = $this->a->q()->parse_sql_date( $v['date'] );
			}
			
			$visited_q->rewind();
		}

		if ( count( $active_q ) ) {
			foreach( $active_q as $v ) {
				$timeline[] = $v['date'];
				$active_q['date'] = $this->a->q()->parse_sql_date( $v['date'] );
			}
			
			$active_q->rewind();
		}

		if ( count( $cancel_q ) ) {
			foreach( $cancel_q as $v ) {
				$timeline[] = $v['date'];
				$cancel_q['date'] = $this->a->q()->parse_sql_date( $v['date'] );
			}
			
			$cancel_q->rewind();
		}

		if ( $timeline ) {
			$timeline = array_unique( $timeline );
			array_walk( $timeline, array( $this->a->q(), 'parse_sql_date' ) );
			usort( $timeline, array( $this->a->q(), 'compare_sql_date' ) );

			foreach( $timeline as $t ) {

				$date = $t;
				$this->a->q()->sql_date_to_str( $t );

				if ( count( $sent_q ) ) {
					if ( ! $sent_q->is_end && $this->a->q()->compare_sql_date( $date, $sent_q['date'] ) === 0 ) {
						$success_d[] = array( 'x' => $t, 'y' => $sent_q['success'] );
						$failed_d[] = array( 'x' => $t, 'y' => $sent_q['fail'] );
						$sent_q->next();

					} else {
						$success_d[] = array( 'x' => $t, 'y' => 0, );
						$failed_d[] = array( 'x' => $t, 'y' => 0, );
					}
				}

				if ( count( $viewed_q ) ) {
					if ( ! $viewed_q->is_end && $this->a->q()->compare_sql_date( $date, $viewed_q['date'] ) === 0 ) {
						$viewed_d[] = array( 'x' => $t, 'y' => $viewed_q['viewed'] );
						$viewed_q->next();

					} else {
						$viewed_d[] = array( 'x' => $t, 'y' => 0, );
					}
				}

				if ( count( $visited_q ) ) {
					if ( ! $visited_q->is_end && $this->a->q()->compare_sql_date( $date, $visited_q['date'] ) === 0 ) {
						$visited_d[] = array( 'x' => $t, 'y' => $visited_q['visited'] );
						$visited_q->next();

					} else {
						$visited_d[] = array( 'x' => $t, 'y' => 0, );
					}
				}

				if ( count( $active_q ) ) {
					if ( ! $active_q->is_end && $this->a->q()->compare_sql_date( $date, $active_q['date'] ) === 0 ) {
						$active_d[] = array( 'x' => $t, 'y' => $active_q['active'] );
						$active_q->next();

					} else {
						$active_d[] = array( 'x' => $t, 'y' => 0, );
					}
				}

				if ( count( $cancel_q ) ) {
					if ( ! $cancel_q->is_end && $this->a->q()->compare_sql_date( $date, $cancel_q['date'] ) === 0 ) {
						$cancel_d[] = array( 'x' => $t, 'y' => $cancel_q['cancel'] );
						$cancel_q->next();

					} else {
						$cancel_d[] = array( 'x' => $t, 'y' => 0, );
					}
				}
			}
		}

		// Successfully sent emails 
		$ret[] = array(
			'label'  => $this->a->__( 'Sent' ),
			'data'   => $success_d,
			'id'     => 'sent',
		);

		// Opened emails
		$ret[] = array(
			'label'  => $this->a->__( 'Opened' ),
			'data'   => $viewed_d,
			'id'     => 'viewed',
		);

		// Visited emails
		$ret[] = array(
			'label'  => $this->a->__( 'Visited' ),
			'data'   => $visited_d,
			'id'     => 'visited',
		);

		// Failed emails
		$ret[] = array(
			'label'  => $this->a->__( 'Failed' ),
			'data'   => $failed_d,
			'id'     => 'failed',
		);

		// Active subscribers
		$ret[] = array(
			'label'  => $this->a->__( 'Subscribed' ),
			'data'   => $active_d,
			'id'     => 'active',
		);

		// Canceled subscriptions
		$ret[] = array(
			'label'  => $this->a->__( 'Canceled' ),
			'data'   => $cancel_d,
			'id'     => 'cancel',
		);

		return $ret;
	}

	/**
	 * Fetches newsletter summary details
	 * @param int $id Newsletter ID
	 * @return array
	 */
	public function get_total_chart_data( $id ) {
		$hq = $this->db->query(
			"SELECT
				COUNT( IF( `status` = " .  \Advertikon\Mail\Advertikon::EMAIL_STATUS_SUCCESS . ", 1, NULL ) ) as `success`,
				COUNT( IF( `status` = " .  \Advertikon\Mail\Advertikon::EMAIL_STATUS_FAIL . ", 1, NULL ) ) as`failed`,
				COUNT( `date_viewed` ) as`viewed`,
				COUNT( `date_visited` ) as`visited`
				FROM `" . DB_PREFIX . $this->a->history_table . "` 
			WHERE `newsletter` = " . (int)$id
		);

		$history = new \Advertikon\Db_Result( $hq->rows );

		$sq = $this->db->query(
			"SELECT
				COUNT( IF( `status` = " .  \Advertikon\Mail\Advertikon::SUBSCRIBER_STATUS_ACTIVE . ", 1, NULL ) ) as `active`,
				COUNT( IF( `status` = " .  \Advertikon\Mail\Advertikon::SUBSCRIBER_STATUS_CANCELLED . ", 1, NULL ) ) as`cancel`,
				COUNT( IF( `status` = " .  \Advertikon\Mail\Advertikon::SUBSCRIBER_STATUS_VERIFICATION . ", 1, NULL ) ) as`verification` 
				FROM `" . DB_PREFIX . $this->a->newsletter_subscribers_table . "` 
			WHERE `newsletter` = " . (int)$id
		);

		$subscribers = new \Advertikon\Db_Result( $sq->rows );

		$ret = array(
			array(
				'label'  => $this->a->__( 'Sent' ),
				'data'   => $history['success'],
				'id'     => 'sent',
			),
			array(
				'label'  => $this->a->__( 'Failed' ),
				'data'   => $history['failed'],
				'id'     => 'failed',
			),
			array(
				'label'  => $this->a->__( 'Viewed' ),
				'data'   => $history['viewed'],
				'id'     => 'viewed',
			),

			array(
				'label'  => $this->a->__( 'Visited' ),
				'data'   => $history['visited'],
				'id'     => 'visited',
			),
			array(
				'label'  => $this->a->__( 'Subscribed' ),
				'data'   => $subscribers['active'],
				'id'     => 'active',
			),
			array(
				'label'  => $this->a->__( 'Canceled' ),
				'data'   => $subscribers['cancel'],
				'id'     => 'cancel',
			),
			array(
				'label'  => $this->a->__( 'Email verification' ),
				'data'   => $subscribers['verification'],
				'id'     => 'verification',
			),
		);

		return $ret;
	}

	/**
	 * Fetches period range from WHERE clause
	 * @param array $where Where clause
	 * @param array &$periods Period parts
	 * @return array
	 */
	public function get_period_from_where( $where, &$periods = array() ) {
		if ( ! array( $where ) ) {
			trigger_error( sprintf( 'WHERE clause needs to be an array, %s given instead', gettype( $where ) ) );

			return false;
		}

		if ( is_array( current( $where ) ) ) {
			foreach( $where as $w ) {
				$result = $this->get_period_from_where( $w, $periods );

				if ( false === $result ) {
					return false;
				}
			}
		}

		if ( ! isset( $where['field'] ) || 'date_added' !== $where['field'] ) {
			return $periods;
		}


		if ( ! isset( $where['operation'] ) ) {
			trigger_error( 'To fetch period from WHERE clause it needs to have "operation" field' );
			
			return false;
		}

		$operation = htmlspecialchars_decode( $where['operation'] );

		if ( ! in_array( $operation, array( '>', '>=', '<', '<=' ) ) ) {
			trigger_error( sprintf( 'WHERE "%s" is not valid "operation" field\'s value to fetch periods' ) );

			return false;
		}

		if ( ! isset( $where['value'] ) || ! is_string( $where['field'] ) ) {
			 trigger_error( 'To fetch period from WHERE clause it needs to have string "value"' );
			
			return false;
		}

		try {
			$period = new DateTime( $where['value'] );

		} catch ( Exception $e ) {
			trigger_error( sprintf( '"%s" is int a valid date format' ) );

			return false;
		}
		
		switch ( $operation ) {
		case '>' :
		case '>=' :
			$this->add_from_period( $period, $periods );
			break;
		case '<' :
		case '<=' :
			$this->add_to_period( $period, $periods );
			break;

		}

		if ( empty ( $periods['from'] ) ) {
			$periods['from'] = new DateTime( "- 1 year" );
		}

		if ( empty ( $periods['to'] ) ) {
			$periods['to'] = new DateTime( "now" );
		}

		return $periods;
	}

	/**
	 * Adds from period fetched from WHERE clause
	 * @param object $period DateTame object 
	 * @param array &$periods From-To objects
	 * @return void
	 */
	public function add_from_period( $period, &$periods ) {
		if ( empty( $periods['from'] ) ) {
			$periods['from'] = $period;

		} elseif ( $periods['from'] > $period ) {
			$periods['from'] = $period;
		}
	}

	/**
	 * Adds to period fetched from WHERE clause
	 * @param object $period DateTame object 
	 * @param array &$periods From-To objects
	 * @return void
	 */
	public function add_to_period( $period, &$periods ) {
		if ( empty( $periods['to'] ) ) {
			$periods['to'] = $period;

		} elseif ( $periods['to'] < $period ) {
				$periods['to'] = $period;
		}
	}

	/**
	 * Returns formatted name for mail history record
	 * @param int $status_code Status code
	 * @return string
	 */
	public function get_history_status_name( $status_code ) {
		$name = '';

		switch( $status_code ) {
		case \Advertikon\Mail\Advertikon::EMAIL_STATUS_SUCCESS :
			$name = sprintf( '<b class="text-success">%s</b>', $this->a->__( 'Success' ) );
			break;
		case \Advertikon\Mail\Advertikon::EMAIL_STATUS_FAIL :
			$name = sprintf( '<b class="text-danger">%s</b>', $this->a->__( 'Failure' ) );
			break;
		}

		return $name;
	}

	/**
	 * Inports OC customers to target newsletter
	 * @param int $id Target newsletter's ID
	 * @param bool $override Flag to override existing records, default false
	 * @param array $where Where clause
	 * @return boolean|null
	 */
	public function import_subscribers_oc_customers( $id, $override = false, $where = array() ) {
		if ( $override ) {
			$this->db->query(
				"DELETE `s` FROM `" . DB_PREFIX . $this->a->newsletter_subscribers_table . "` `s`
				JOIN `" . DB_PREFIX . "customer` `o`
					USING(`email`)
				WHERE " . implode(
					' AND ', 
					$this->a->create_where_clause(
						$this->a->merge_where(
							$where,
							array(
								'field'     => 's.newsletter',
								'operation' => '=',
								'value'     => (int)$id
							)
						)
					)
				)
			);
		}

		$result = $this->db->query(
			"INSERT INTO `" . DB_PREFIX . $this->a->newsletter_subscribers_table . "`
				(`name`, `email`, `status`, `date_added`, `newsletter` )
			SELECT CONCAT( `firstname`, ' ', `lastname`), `o`.`email`, '" . \Advertikon\Mail\Advertikon::SUBSCRIBER_STATUS_ACTIVE . "', NOW(), '" . (int)$id . "'
			FROM `" . DB_PREFIX . "customer` `o`
			LEFT JOIN `" . DB_PREFIX . $this->a->newsletter_subscribers_table . "` `s`
				ON( `s`.`email` = `o`.`email` AND `s`.`newsletter` = '" . (int)$id . "' )
			WHERE " . implode(
				' AND ',
				$this->a->create_where_clause(
					$this->a->merge_where(
						$where,
						array(
							'field'     => 's.email',
							'operation' => '=',
							'value'     => 'NULL',
						)
					)
				)
			)
		);

		return $result;
	}

	/**
	 * Inports OC affiliates to target newsletter
	 * @param int $id Target newsletter's ID
	 * @param bool $override Flag to override existing records, default false
	 * @param array $where Where clause
	 * @return boolean|null
	 */
	public function import_subscribers_oc_affiliates( $id, $override = false, $where = array() ) {

		if ( $override ) {
			$this->db->query(
				"DELETE `s` FROM `" . DB_PREFIX . $this->a->newsletter_subscribers_table . "` `s`
				JOIN `" . DB_PREFIX . "affiliate` `a`
					USING(`email`)
				WHERE " . implode(
					' AND ', 
					$this->a->create_where_clause(
						$this->a->merge_where(
							$where,
							array(
								'field'     => 's.newsletter',
								'operation' => '=',
								'value'     => (int)$id
							)
						)
					)
				)
			);
		}

		$result = $this->db->query(
			"INSERT INTO `" . DB_PREFIX . $this->a->newsletter_subscribers_table . "`
				(`name`, `email`, `status`, `date_added`, `newsletter` )
			SELECT CONCAT( `firstname`, ' ', `lastname`), `a`.`email`, '" . \Advertikon\Mail\Advertikon::SUBSCRIBER_STATUS_ACTIVE . "', NOW(), '" . (int)$id . "'
			FROM `" . DB_PREFIX . "affiliate` `a`
			LEFT JOIN `" . DB_PREFIX . $this->a->newsletter_subscribers_table . "` `s`
				ON( `s`.`email` = `a`.`email`)
			WHERE " . implode(
				' AND ',
				$this->a->create_where_clause(
					$this->a->merge_where(
						$where,
						array(
							'field'     => 's.email',
							'operation' => '=',
							'value'     => 'NULL',
						)
					)
				) 
			)
		);

		return $result;
	}

	/**
	 * Inports OC product purchasers to target newsletter
	 * @param int $id Target newsletter's ID
	 * @param bool $override Flag to override existing records, default false
	 * @param array $where Where clause
	 * @return boolean|null
	 */
	public function import_subscribers_oc_products( $id, $override = false, $product_id = array() ) {
		if ( $override ) {
			$this->db->query(
				"DELETE FROM `" . DB_PREFIX . $this->a->newsletter_subscribers_table . "`
				WHERE
					`newsletter` = " . (int)$id . " AND
					`email` IN (
						SELECT `email`
						FROM `" . DB_PREFIX . "order` `o`
						LEFT JOIN `" . DB_PREFIX . "order_product` `op`
							USING(`order_id`)
						WHERE " . implode(
							' AND ',
							$this->a->create_where_clause(
								array(
									'field'     => 'op.product_id',
									'operation' => 'in',
									'value'     => $product_id
								)
							)
						) . ")"
					);
		}

		$result = $this->db->query(
			"INSERT INTO `" . DB_PREFIX . $this->a->newsletter_subscribers_table . "`
				(`email`, `name`, `status`, `date_added`, `newsletter` )
			SELECT DISTINCT
				`o`.`email`, CONCAT( `firstname`, ' ', `lastname`), '" . \Advertikon\Mail\Advertikon::SUBSCRIBER_STATUS_ACTIVE . "', NOW(), '" . (int)$id . "'
			FROM `" . DB_PREFIX . "order` `o`
			LEFT JOIN `" . DB_PREFIX . "order_product` `op`
				USING(`order_id`)
			LEFT JOIN `" . DB_PREFIX . $this->a->newsletter_subscribers_table . "` `s`
				ON( `s`.`email` = `o`.`email`)
			WHERE " . implode(
				' AND ',
				$this->a->create_where_clause(
					array(
						array(
							'field'     => 'op.product_id',
							'operation' => 'in',
							'value'     => $product_id
						),
						array(
							'field'     => 's.email',
							'operation' => '=',
							'value'     => 'NULL'
						)
					)
				)
			)
		);

		return $result;
	}

	/**
	 * Tests IMAP configurations
	 * @param string $url URL of Mail server
	 * @param string $port IMAP port of IMAP server 
	 * @param string $login Login
	 * @param string $pass Password
	 * @param boolean $ssl Flag to use secure connection
	 * @return void
	 */
	public function test_imap( $url, $port, $login, $pass, $ssl ) {
		$this->a->imap( array( function( $sp, &$c_count ) {
			$this->a->imap_in( $sp, 'LIST "" ""', $c_count++ );
			$out = $this->a->imap_out( $sp );

			if ( ! $out ) {
				throw new \Advertikon\Exception( $this->a->__( 'Failed to get list of folders' ) );
			}

		} ), $url, $port, $login, $pass, $ssl );
	}

	/**
	 * Renders bounce tab for admin area
	 * @return string
	 */
	public function render_bounce_tab() {
		$ret = '';

		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'IMAP server' ),
			'element' => $this->a->r( array(
				'type'         => 'inputgroup',
				'addon_before' => 'https://',
				'element'      => array(
					'type'  => 'text',
					'class' => 'form-control',
					'id'    => 'imap-url',
					'value' => $this->a->config( 'imap_url' ),
				),
			) ),
			'description' => $this->a->__( 'URL of IMAP server. Eg %s', 'imap.gmail.com, outlook.office365.com, imap.mail.yahoo.com' ),
		) );

		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Port' ),
			'element' => $this->a->r( array(
				'type'  => 'text',
				'class' => 'form-control',
				'id'    => 'imap-port',
				'value' => $this->a->config( 'imap_port', 993 ),
			) ),
			'description' => 'Default port for IMAP is 143 (SSL 993)',
		) );

		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Login name' ),
			'element' => $this->a->r( array(
				'type'  => 'text',
				'class' => 'form-control',
				'id'    => 'imap-login',
				'value' => $this->a->config( 'imap_login' ),
			) ),
			'description' => 'The name of your account at server',
		) );

		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Password' ),
			'element' => $this->a->r( array(
				'type' => 'inputgroup',
				'element' => array(
					'type'  => 'password',
					'class' => 'form-control',
					'id'    => 'imap-password',
					'value' => $this->a->config( 'imap_password' ),
				),
				'addon_after' => array(
					'type'  => 'button',
					'icon'  => 'fa-eye-slash',
					'id'    => 'show-imap-password',
					'title' => $this->a->__( 'Show password' ),
				),
			) ),
			'description' => 'Password of your account',
		) );

		$ret .= $this->a->r()->render_form_group( array(
			'label' => $this->a->__( 'Use SSL' ),
			'element' => $this->a->r()->render_fancy_checkbox( array(
				'value'  => $this->a->config( 'imap-ssl', 1 ),
				'id'     => 'imap-ssl',
			) ),
		) );

		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Check configuration' ),
			'element' => $this->a->r( array(
				'type'        => 'button',
				'button_type' => 'default',
				'icon'        => 'fa-cog',
				'custom_data' => 'data-url="' . $this->url->link(
					$this->a->type . '/' . $this->a->code . '/check_imap',
					'token=' . $this->session->data['token'],
					'SSL'
				) . '"',
				'id'          => 'check-imap',
			) ),
			'description' => $this->a->__( 'Check IMAP configuration' ),
		) );

		$ret .= $this->a->r()->render_form_group( array(
			'label' => $this->a->__( 'Enable blacklisting' ),
			'element' => $this->a->r()->render_fancy_checkbox( array(
				'value'  => $this->a->config( 'imap_blacklist' ),
				'id'     => 'imap-blacklist'
			) ),
		) );

		$ret .= $this->a->r()->render_form_group( array(
			'label' => $this->a->__( 'Unseen only' ),
			'element' => $this->a->r()->render_fancy_checkbox( array(
				'value'  => $this->a->config( 'imap_unseen' ),
				'id'     => 'imap-unseen'
			) ),
			'description' => $this->a->__( 'To improve performance only emails, marked as not been seen will be processed. In this case, you need to specify some action which will be performed on processed emails' ),
		) );

		$ret .= $this->a->r()->render_form_group( array(
			'label' => $this->a->__( 'Complete action' ),
			'element' => $this->a->r( array(
				'type' => 'select',
				'value' => array(
					\Advertikon\Mail\Advertikon::IMAP_ACTION_NOTING => $this->a->__( 'Do nothing' ),
					\Advertikon\Mail\Advertikon::IMAP_ACTION_SEEN   => $this->a->__( 'Mark as seen' ),
					\Advertikon\Mail\Advertikon::IMAP_ACTION_DELETE => $this->a->__( 'Delete' ),
				),
				'active' => $this->a->config( 'imap_action' ),
				'class'  => 'form-control',
				'id'     => 'imap-action'
			) ),
			'description' => $this->a->__( 'Specify what action to be performed on processed emails' ),
		) );

		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Blacklist' ),
			'element' => $this->a->r( array(
				'type'        => 'button',
				'button_type' => 'success',
				'icon'        => 'fa-ban',
				'custom_data' => 'data-url="' . $this->url->link(
					$this->a->type . '/' . $this->a->code . '/do_blacklist',
					'token=' . $this->session->data['token'],
					'SSL'
				) . '"',
				'id'          => 'do-blacklist',
			) ),
			'description' => $this->a->__( 'Run action manually. To run action as cron task - configure Cron job from "Queue" tab' ),
		) );

		$ret .= $this->a->r()->render_form_group( array(
			'label'   => $this->a->__( 'Save configuration' ),
			'element' => $this->a->r( array(
				'type'        => 'button',
				'button_type' => 'primary',
				'icon'        => 'fa-save',
				'custom_data' => 'data-url="' . $this->url->link(
					$this->a->type . '/' . $this->a->code . '/save_imap',
					'token=' . $this->session->data['token'],
					'SSL'
				) . '"',
				'id'          => 'save-imap',
			) ),
		) );

		return $ret;
	}

	/**
	 * Fix method
	 * @return void
	 */
	public function fix() {

		// TEXT to BLOB field conversion
		$q = $this->db->query( "describe `" . DB_PREFIX . $this->a->templates_table . "` `data`" );

		if ( $q->num_rows && isset( $q->row['Type'] ) && strtolower( $q->row['Type'] ) === 'text' ) {
			$this->db->query( "ALTER TABLE `" . DB_PREFIX . $this->a->templates_table . "` MODIFY `data` BLOB" );
			$this->db->query( "ALTER TABLE `" . DB_PREFIX . $this->a->profiles_table . "` MODIFY `data` BLOB" );
			$this->db->query( "ALTER TABLE `" . DB_PREFIX . $this->a->shortcodes_table . "` MODIFY `data` BLOB" );
			$this->db->query( "ALTER TABLE `" . DB_PREFIX . $this->a->newsletter_widget_table . "` MODIFY `data` BLOB" );
		} 
	}
}
