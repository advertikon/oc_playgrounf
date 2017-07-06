<?php
/**
 * Advertikon Renderer Class
 * @author Advertikon
 * @package Advertikon
 * @version 2.8.11
 */

namespace Advertikon;

class Renderer {

	public $namespace = null;

	public function __construct( $namespace ) {
		$this->namespace = $namespace;
	}
	
	/**
	 * Renders Administration area panels headers
	 * @param Array $panels Panels list to be rendered
	 * @return void
	 */
	public function render_panels_headers( $data ) {
		$id        = isset( $data['id'] ) ? $data['id'] : '';
		$class     = isset( $data['class'] ) ? $data['class'] : '';
		$panels    = isset( $data['panels'] ) ? $data['panels'] : array();
		$id_prefix = isset( $data['id_prefix'] ) ? $data['id_prefix'] : '';

		$output = '<ul id="' . $id . '" class="nav nav-tabs ' . $class . '">';

		foreach( $panels as $panel_name => $panel ) {
			$output .= $this->render_panel_header( $panel, $panel_name, $id_prefix );
		}

		$output .= '</ul>';

		return $output;
	}

	/**
	 * Renders single panel header
	 * @param array $panel 
	 * @return string
	 */
	public function render_panel_header( $panel, $id = '', $id_prefix = '' ) {
		$ret = '';
		$id = $id_prefix . $id;
		$class = isset( $panel['class'] ) ? ' ' . $panel['class'] : '';

		if ( ! $panel ) {
			return '';
		}

		if ( isset( $panel['dropdown'] ) ) {

			$ret .=
			'<li role="presentation" class="dropdown tab-dropdown' . $class . '">' .
				'<a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">' .
				 	$panel['name'] .
					'<span class="caret"></span>' .
				'</a>' .
				'<ul class="dropdown-menu">';

			foreach( $panel['options'] as $key => $option ) {
				$ret .=
					'<li><a href="#" data-value="' . $key . '">' . $option . '</a></li>';
			}

			$ret .=
				'</ul>' .
			'</li>';

		} else {
			$ret .=
			'<li class="' . ( isset( $panel['active'] ) ? 'active' : '' ) . $class . '">' .
				'<a href="#' . $id . '" data-toggle="tab">' .
					( isset( $panel['image'] ) ? '<img style="margin-right:5px" src="' . $panel['image'] . '">' : '' ) .
					( isset( $panel['icon'] ) ? '<i style="margin-right:5px" class="fa ' . $panel['icon'] . '"></i>' : '' ) .
					$panel['name'] .
				'</a>' .
			'</li>';
		}

		return $ret;
	}

	/**
	 * Renders HTML element
	 * @param array $data Element data 
	 * @return string
	 */
	public function render_element( $data ) {
		if( ! $data ) {
			return '';
		}

		if( empty( $data['type'] ) ) {
			$data['type'] = 'text';
		}

		$ret = '';

		switch( $data['type'] ) {
			case 'text' :
			case 'number' :
			case 'password' :
			case 'file' :
			case 'hidden' :
			case 'file' :
				$ret = $this->render_input( $data );
				break;
			case 'select' :
			case 'multiselect' :
				$ret = $this->render_select( $data );
				break;
			case 'button' :
				$ret = $this->render_button( $data );
				break;
			case 'buttongroup' :
				$ret = $this->render_button_group( $data );
				break;
			case 'checkbox' :
				$ret = $this->render_checkbox( $data );
				break;
			case 'inputgroup' :
				$ret = $this->render_input_group( $data );
				break;
			case 'color' :
				$ret = $this->render_color( $data );
				break;
			case 'image' :
				$ret = $this->render_image( $data );
				break;
			case 'elfinder_image' :
				$ret = $this->render_elfinder_image( $data );
				break;
			case 'textarea' :
				$ret = $this->render_textarea( $data );
				break;
			case 'dimension' :
				$ret = $this->render_dimension( $data );
				break;
			case 'lang_set':
				$ret = $this->render_lang_set( $data );
				break;
			case 'submit':
				$ret = $this->render_submit( $data );
				break;
			case 'dropdown' :
				$ret = $this->render_dropdown( $data );
				break;
		}

		return $ret;
	}

	/**
	 * Some stuff here 
	 * @param array $data 
	 * @return array
	 */
	public function fetch_element_data( $data ) {
		$return = array();

		$return['id']          = isset( $data['id'] ) ? htmlentities( $data['id'] ) : '';
		$return['name']        = ! empty( $data['name'] ) ? htmlentities( ADK( $this->namespace )->prefix_name( $data['name'] ) ) : '';
		$return['type']        = isset( $data['type'] ) ? htmlentities( $data['type'] ) : 'text';
		$return['placeholder'] = isset( $data['placeholder'] ) ? htmlentities( $data['placeholder'] ) : '';
		$return['class']       = isset( $data['class'] ) ? htmlentities( $data['class'] ) : '';
		$return['custom_data'] = '';
		$return['css']         = isset( $data['css'] ) ? $data['css'] : '';
		$return['values']      = isset( $data['value'] ) ? (array)$data['value'] : array();
		$return['active']      = isset( $data['active'] ) ? (array)$data['active'] : array();
		$return['title']       = isset( $data['title'] ) ? htmlentities( $data['title'] ) : '';

		if ( isset( $data['value' ] ) ) {
			if ( ! is_array( $data['value'] ) ) {
				$return['value'] = htmlentities( $data['value'] );

			} else {
				if ( 'select' === $return['type'] || 'dropdown' === $return['type'] ) {
					$return['value'] = $data['value'];

				} else {
					$return['value'] = htmlentities( current( $data['value'] ) );
				}
			}

		} else {
			$return['value'] = '';
		}

		if ( isset( $data['custom_data'] ) ) { 
			$custom_data_parts = array();
			preg_match_all( '/([^\'\"=\s]+)=(\'|\")(.+?)\2/s', $data['custom_data'], $m, PREG_SET_ORDER );

			if ( $m ) {
				foreach( $m as $attr ) {
					$custom_data_parts[] = htmlentities( $attr[1] ) . '="' . htmlentities( $attr[3] ) . '"';
				}
			}

			$return['custom_data'] = implode( ' ', $custom_data_parts );
		}

		return $return;
	}

	/**
	 * Renders single input element
	 * @param Array $data Input data 
	 * @return void
	 */
	public function render_input( $data ) {
		extract( $this->fetch_element_data( $data ) );		

		return 
		'<input
			type="' . $type . '"
			id="' . $id . '"
			name="' . $name . '"
			class="' . $class . '"
			value="' . $value . '"
			placeholder="' . $placeholder . '" ' .
			$custom_data .
		'>';
	}

	/**
	 * Renders single select element
	 * @param Array $data Select data
	 * @return void
	 */
	public function render_select( $data ) {
		extract( $this->fetch_element_data( $data ) );	
		$multiple  = ! empty( $data['multiple'] ) ? ' multiple ' : '';

		if ( $multiple && strpos( $name, '[' ) === false ) {
			$name .= '[]';
		}
		
		$ret = 
		'<select
			name="' . $name . '"
			id="' . $id . '"
			class="' . $class . '"' .
			$multiple . $custom_data .
			' style="' . $css . '"
		>';

		foreach( $values as $value => $text ) {
			$selected = $this->compare_select_value( $value, $active ) ? ' selected="selected"' : '';

			$ret .=
			'<option value="' . htmlentities( $value ) . '"' . $selected . '>' . $text . '</option>';
		}

		$ret .=
		'</select>';

		return $ret;
	}

	/**
	 * Renders single button
	 * @param Array $data Button data
	 * @return void
	 */
	public function render_button( $data ) {
		extract( $this->fetch_element_data( $data ) );	
	
		$button_type = isset( $data['button_type'] ) ? htmlentities( $data['button_type'] ) : 'default';
		$fixed_width = isset( $data['fixed_width'] ) ? true : false;
		$icon        = isset( $data['icon'] ) ?
			'<i class="fa ' . htmlspecialchars( $data['icon'] ) . ( $fixed_width ? ' fa-fw' : '' ) . '"></i>' : '';

		$text_before = isset( $data['text_before'] ) ?
			'<i>' . $data['text_before'] . '</i>' . ( $icon ? ' ' : '' ) : '';

		$text_after  = isset( $data['text_after'] ) ?
			( $icon ? ' ' : '' ) . '<i>' . $data['text_after'] . '</i>' : '';

		$data_icon = $icon ? ' data-i ="' . htmlentities( $data['icon'] ) . '"' : '';
		$stack_before = isset( $data['stack'] ) ?
			'<span class="fa-stack fa-lg">' .
  				'<span class="fa ' . htmlentities( $data['stack'] ) . '"></span>' : '';
  		$stack_after = $stack_before ? '</span>' : '';

		$output =
		'<button
			type="' . $type . '"
			id="' . $id . '"
			name="' . $name . '"
			class="btn btn-' . $button_type . ' ' . $class . '"
			style="' . $css . '"
			title="' . $title . '" ' .
			$custom_data . ' ' . $data_icon .
		'>' .
			$text_before .
			$stack_before .
			$icon .
			$stack_after .
			$text_after .
		'</button>';

		return $output;

	}

	/**
	 * Renders submit button
	 * @param Array $data Button data
	 * @return void
	 */
	public function render_submit( $data ) {
		$data['type'] = 'submit';
		return $this->render_button( $data );

	}

	/**
	 * Renders single check box element
	 * @param Array $data Check box data
	 * @return void
	 */
	public function render_checkbox( $data ) {
		extract( $this->fetch_element_data( $data ) );	

		$checked = '';
		if( isset( $data['check_non_empty_value'] ) ) {
			$checked = ! empty( $value ) ? 'checked="checked"' : '';
		}
		$text_on = isset( $data['text_on'] ) ? htmlentities( $data['text_on'] ) : ADK( $this->namespace )->__( 'On' );
		$text_off = isset( $data['text_off'] ) ? htmlentities( $data['text_off'] ) : ADK( $this->namespace )->__( 'Off' );
		$custom_data .= ' data-text-on="' . $text_on . '" data-text-off="' . $text_off . '"';
		$label = ! empty( $data['label'] ) ? htmlentities( $data['label'] ) : ( $checked ? $text_on : $text_off );

		return 
		'<input
			type="checkbox"
			id="' . $id . '"
			name="' . $name . '"
			class="' . $class . '"
			value="' . $value . '" ' . $custom_data . ' ' . $checked .
		'>' .
			( $label ? '<label for="' . $id . '">' . $label . '</label>' : '' );
	}

	/**
	 * Renders Bootstrap form-group
	 * @param array $data Form-group data
	 * @return string
	 */
	public function render_form_group( $data ) {
		$label = isset( $data['label'] ) ? $data['label'] : '';
		$for = isset( $data['label_for'] ) ? htmlentities( $data['label_for'] ) : '';
		$element = isset( $data['element'] ) ? $data['element'] : '';

		if ( is_array( $element ) ) {
			$element = $this->render_element( $element );
		} 

		$cols = isset( $data['cols'] ) ? $data['cols'] : ( isset( $this->cols ) ? $this->cols : array( 'col-sm-2', 'col-sm-10', ) );
		$tooltip = isset( $data['tooltip'] ) ? $data['tooltip'] : '';
		$description = isset( $data['description'] ) ? $data['description'] : '';
		$css = isset( $data['css'] ) ? htmlentities( $data['css'] ) : '';
		$feedback = isset( $data['feedback'] ) ? $data['feedback'] : '';
		$has_status = isset( $data['status'] ) ? 'has-' . htmlentities( $data['status'] ) : '';
		$has_feedback = isset( $data['feedback'] ) ? ' have-feedback' : '';
		$class = isset( $data['class'] ) ? ' ' . htmlspecialchars( $data['class'] ) : '';

		if ( ! empty( $data['error'] ) ) {
			$has_status = 'have-error ';
			$has_feedback = ' have-feedback';
			$feedback =
			'<span class="fa fa-close form-control-feedback"></span>';

			$description = $data['error'];
		}

		$str =
		'<div class="form-group ' . $has_status . $class . '" style="' . $css . '">';

		if( $label ) {
			$str .=
			'<label for="' . $for . '" class="' . $cols[0] . '">' .
				$label . ' ' . $this->render_popover( $tooltip ) .
			'</label>';
		}

		$str .=
			'<div class="' . $cols[1] . $has_feedback . '">' .
				$element .
				'<span class="help-block">' . $description . '</span>' .
				$feedback .
			'</div>' .
		'</div>';

		return $str;
	}

	/**
	 * Renders bootstrap information box
	 * @param array $info Element data
	 * @return string
	 */
	public function render_info_box( $info ) {
		$ret =
		'<div class="alert alert-info alert-dismissible tip" role="alert">' .
			'<button type="button" class="close" data-dismiss="alert" aria-label="Close">' .
				'<span aria-hidden="true">&times;</span>' .
			'</button>' .
			'<i class="fa fa-info-circle fa-2x tip-icon"></i> ' . $info .
		'</div>';

		return $ret;
	}

	/**
	 * Renders bootstrap tooltip element
	 * @param string $tooltip Tooltip text 
	 * @return string
	 */
	public function render_tooltip( $tooltip ) {
		if( ! $tooltip ) {
			return '';
		}

		$str =
		'<span
			class="glyphicon"
			data-toggle="tooltip"
			title="' . htmlspecialchars( $tooltip ) . '"
			style="cursor:pointer;"
		>';

		return $str;
	}

	/**
	 * Renders bootstrap popover element
	 * @param string $text Popover text 
	 * @return string
	 */
	public function render_popover( $content, $title = '' ) {

		if( ! $content && ! $title ) {
			return '';
		}

		$str =
		'<span
			class="fa fa-question-circle popover-icon pull-right"
			title="' . htmlspecialchars( $title ) . '"
			data-content="' . htmlspecialchars( $content ) . '"
		>';

		return $str;
	}

	/**
	 * Renders bootstrap input-group element
	 * @param array $data Element data 
	 * @return string
	 */
	public function render_input_group( $data ) {
		$element = isset( $data['element'] ) ? $data['element'] : '';
		$addon_before = isset( $data['addon_before'] ) ? $data['addon_before'] : '';
		$addon_after = isset( $data['addon_after'] ) ? $data['addon_after'] : '';

		$str =
		'<div class="input-group">' .
			$this->render_addon( $addon_before ) .
			$this->render_element( $element ) . 
			$this->render_addon( $addon_after ) .
		'</div>';

		return $str;
	}

	/**
	 * Renders bootstrap button-group element
	 * @param array $data Element data
	 * @return string
	 */
	public function render_button_group( $data ) {
		extract( $this->fetch_element_data( $data ) );	

		$str =
		'<div class="btn-group" role="group" style="' . $css . '" ' . $custom_data . '>';

		if ( isset( $data['buttons'] ) ) {
			foreach( $data['buttons'] as $button ) {
				$str .= $this->render_element( $button );
			}
		}

		$str .=
		'</div>';

		return $str;
	}

	/**
	 * Renders button addon
	 * @param array|string $data Addon data
	 * @return string
	 */
	public function render_addon( $data ) {
		if( ! $data ) {
			return '';
		}

		$str = '';

		if( ! is_array( $data ) || empty( $data['type'] ) ) {
			$str .=
			'<span class="input-group-addon">' . $data . '</span>';

		} elseif( 'button' === $data['type'] ) {
			$str .=
			'<span class="input-group-btn">' . $this->render_element( $data ) . '</span>';

		} elseif ( 'buttons' === $data['type'] ) {
			$str .= '<span class="input-group-btn">';

			foreach( $data['buttons'] as $button ) {
				$str .= $this->render_element( $button );
			}

			$str .= '</span>';

		} else {
			$str .=
			'<span class="input-group-addon">' . $this->render_element( $data ) . '</span>';
		}

		return $str;
	}

	/**
	 * Renders 
	 * @param type $data color-picker element
	 * @return string
	 */
	public function render_color( $data ) {
		$data['type']  = 'text';
		$data['class'] = ( isset( $data['class'] ) ? $data['class'] . ' ' : '' ) . 'form-control';

		$str = $this->render_input_group( array(
			'element'     => $data,
			'addon_after' => '<i class="fa fa-paint-brush"></i>',
			)
		);

		return $str;
	}

	/**
	 * Renders image element
	 * @param array $data Element data
	 * @return string
	 */
	public function render_image( $data ) {
		extract( $this->fetch_element_data( $data ) );	
		ADK( $this->namespace )->load->model( 'tool/image' );

		$value_path = ! empty( $data['value'] ) ? htmlentities( $data['value'] ) : '';
		$img = $value_path ? $value_path : 'no_image.png';
		$value = htmlentities( ADK( $this->namespace )->model_tool_image->resize( $img, 100, 100 ) );

		$str =
		'<a
			href=""
			id="thumb-' . $id . '"
			data-toggle="image"
			class="img-thumbnail"
			data-original-title=""
			title=""
		>' .
			'<img
				src="' . $value . '"
				alt=""
				title=""
				data-placeholder="' . $value . '"
			>' .
		'</a>' .
		'<input
			class="img-value"
			type="hidden"
			name="' . $name . '"
			value="' . $value_path . '"
			id="' . $id . '"
		>';

		return $str;
	}


	/**
	 * Renders image element facilitated by Elfinder library
	 * @param array $data Element data
	 * @return string
	 */
	public function render_elfinder_image( $data ) {
		extract( $this->fetch_element_data( $data ) );	
		ADK( $this->namespace )->load->model( 'tool/image' );

		$value_path = ! empty( $data['value'] ) ? htmlentities( $data['value'] ) : '';
		$img = $value_path ? $value_path : 'no_image.png';
		$value = htmlentities( ADK( $this->namespace )->model_tool_image->resize( $img, 120, 120 ) );
		$embed = empty( $data['embed_value'] ) ? 0 : 1;
		$uid = uniqid();

		// One level up
		$embed_name = preg_replace( '/\[[^]]+\]$/', '[embed]', $name );
		if( ! $embed_name ) {
			$embed_name = '';
		}

		$str =
		'<a href="#" class="elfinder ' . ( $embed ? 'embedded' : 'attached' ) . ( empty( $value_path ) ? ' removing' : '' ) . '" data-key="' . $uid . '">' .
			'<img src="' . $value . '"' .'data-placeholder="' . $value . '" style="width: 120px; height: auto;">' .
			'<input type="hidden" name="' . $name . '" value="' . $value_path . '" id="' . $id . '" data-key="' . $uid . '">' .
			'<input type="hidden" name="' . $embed_name . '" value="' . $embed . '" class="embed-input">' .
			'<span class="disposition-name">' .
				'<span class="disposition-embedded"><i>' . ADK( $this->namespace )->__( 'Embedded' ) . '</i></span>' .
				'<span class="disposition-attached"><i>' . ADK( $this->namespace )->__( 'Attached' ) . '</i></span>' .
			'</span>' .
			'<i class="fa fa-close fa-fw remove-image"></i>' .
		'</a>';

		return $str;
	}

	/**
	 * Renders single input element
	 * @param Array $data Input data 
	 * @return void
	 */
	public function render_textarea( $data ) {
		extract( $this->fetch_element_data( $data ) );	

		$row = isset( $data['row'] ) ? htmlentities( $data['row'] ) : 3;

		return 
		'<textarea id="' . $id . '" name="' . $name . '" rows="' . $row . '" ' .
			'class="' . $class . '" placeholder="' . $placeholder . '" ' .
			$custom_data . '>' .
			htmlspecialchars_decode( $value ) .
		'</textarea>';
	}

	/**
	 * Renders fancy checkbox element
	 * @since 1.1.0
	 * @param array $data 
	 * @return string
	 */
	public function render_fancy_checkbox( $data ) {
		extract( $this->fetch_element_data( $data ) );	

		$value_on = isset( $data['value_on'] ) ? htmlentities( $data['value_on'] ) : 1;
		$value_off = isset( $data['value_off'] ) ? htmlentities( $data['value_off'] ) : 0;
		$value = isset( $data['value'] ) && $data['value'] == $value_on ? $value_on : $value_off;
		$text_on = isset( $data['text_on'] ) ? htmlentities( $data['text_on'] ) : ADK( $this->namespace )->__( 'On' );
		$text_off = isset( $data['text_off'] ) ? htmlentities( $data['text_off'] ) : ADK( $this->namespace )->__( 'Off' );
		$dependent_on = isset( $data['dependent_on'] ) ? htmlentities( $data['dependent_on' ] ) : '';
		$dependent_off = isset( $data['dependent_off'] ) ? htmlentities( $data['dependent_off' ] ) : '';

		$ret =
		'<input
			type="hidden"
			name="' . $name . '"
			id="' . $id . '"
			class="fancy-checkbox ' . $class . '"
			value="' . $value . '"
			data-text-on="' . $text_on . '"
			data-text-off="' . $text_off . '"
			data-value-on="' . $value_on . '"
			data-value-off="' . $value_off . '"
			data-dependent-on="' . $dependent_on . '"
			data-dependent-off="' . $dependent_off . '"
			' . $custom_data .
		'>';

		return $ret;
	}

	/**
	 * Renders dimensions form control
	 * @since 1.1.0
	 * @param array $data  Control data
	 * @return string
	 */
	public function render_dimension( $data ) {
		extract( $this->fetch_element_data( $data ) );	

		$values = isset( $data['values'] ) ? htmlentities( $data['values'] ) : 'px,%';
		$texts  = isset( $data['texts'] ) ? htmlentities( $data['texts'] ) : 'px,%';
		$titles = isset( $data['titles'] ) ? htmlentities( $data['titles'] ) :
			ADK( $this->namespace )->__( 'Width measured in pixels' ) . ',' .
			ADK( $this->namespace )->__( 'Width measured in percentage of available width' );

		$maxes  = isset( $data['maxes'] ) ? htmlentities( $data['maxes'] ) : '2000,100';
		$value = empty( $value ) ? 0 : $value;
		$units = isset( $data['units'] ) ? htmlentities( $data['units'] ) : 'px';
		$max = isset( $data['max'] ) ? 'data-max="' . htmlentities( $data['max'] ) . '"' : '';

		$str =
		'<div class="dimension-wrapper" ' . $custom_data . '>' .
			'<div class="dimension-slider-wrapper">' .
				'<div id="" class="slider" data-value1="' . $value . '" ' .
					'data-value1-target="#' . $id . '-value"' .
					$max .
				'>' .
				'</div>' .
			'</div>' .
			'<div class="dimension-input-gr-wrapper">' .
				$this->render_element( array(
					'type'    => 'inputgroup',
					'element' => array(
						'type'  => 'text',
						'id'    => $id . '-value',
						'name'  => $name ? $name . '[value]' : '',
						'value' => $value,
						'css'   => 'width:80px',
						'class' => 'form-control',
					),
					'addon_after' => array(
						'type'        => 'button',
						'id'          => $id . '-units',
						'name'        => $name ? $name . '[units]' : '',
						'text_before' => $units,
						'custom_data' => 'data-values="' . $values . '"
											data-texts="' . $texts . '"
											data-value="' . $units . '"
											data-titles="' . $titles . '"
											data-maxes="' . $maxes . '"
											data-toggle="tooltip"',

						'class'       => 'switchable measure-units',
					),
				) ) .
				'</div>' .
		'</div>';

		return $str;
	}

	/**
	 * Renders input field for each store languages
	 * @param Array $data Input data 
	 * @return void
	 */
	public function render_lang_set( $data ) {
		$ret = '';
		$languages = ADK( $this->namespace )->get_languages();
		$admin_lang = ADK( $this->namespace )->config->get( 'config_admin_language' );
		$id = uniqid();
		$d = isset( $data['element'] ) ? $data['element'] : array();
		$name = isset( $data['element']['name'] ) ? $data['element']['name'] : '';
		$key = isset( $data['key'] ) ? $data['key'] : '';
		$default_value = isset( $data['element']['value'] ) ? $data['element']['value'] : '';

		if ( count( $languages ) > 1 ) {
			$ret .= '<ul class="nav nav-tabs" role="tablist">';

			foreach( $languages as $language ) {
				$a_c = $admin_lang === $language['code'] ? 'active' : '';

				$ret .= '<li role="presentation" class="' . $a_c . '">' .
							'<a href="#caption-' . $id . '-' . $language['code'] . '" role="tab" data-toggle="tab">' .
								'<img src="' . ADK( $this->namespace )->get_lang_flag_url( $language ) . '">' .
							'</a>' .
						'</li>';
			}

			$ret .= '</ul>';
			$ret .= '<div class="tab-content">';

			foreach( $languages as $language ) {
				$a_c = $admin_lang === $language['code'] ? 'active' : '';
				$d['name'] = $name ? $name . '[' . $language['code'] . ']' : '';
				$d['value'] = ADK( $this->namespace )->get_lang_caption(
					! empty( $key ) ? $key : $name,
					$language['code'],
					$default_value
				);

				$ret .= '<div id="caption-' . $id . '-' . $language['code'] . '" class="tab-pane ' . $a_c . '" >';
				$ret .= $this->render_element( $d );
				$ret .= '</div>';	
			}

			$ret .= '</div>';
			
		} else {
			$language = current( $languages );
			$d['name'] = $name ? $name . '[' . $language['code'] . ']' : '';
			$d['value'] = ADK( $this->namespace )->get_lang_caption(
				! empty( $key ) ? $key : $name,
				$language['code'],
				$default_value
			);
			$ret .= $this->render_element( $d );
		}

		return $ret;
	}

	/**
	 * Renders dropdown element
	 * @param Array $data Check box data
	 * @return void
	 */
	public function render_dropdown( $data ) {
		extract( $this->fetch_element_data( $data ) );

		$button_type = empty( $button_type ) ? 'btn-default' : 'btn-' . $button_type; 

		$ret = 
		'<div class="btn-group ' . $class . '">
		<button
			type="button"
			class="btn ' . $button_type . ' dropdown-toggle"
			data-toggle="dropdown" 
			aria-haspopup="true"
			aria-expanded="false"
			style="' . $css . '"
		>
			<span class="dd-text">' . current( $active ) . '</span> <span class="caret"></span>
		</button>
		<ul class="dropdown-menu">';

		foreach( $value as $v ) {
			if ( isset( $v['label'] ) && isset( $v['href'] ) ) {
				$ret .= '<li><a href="' . $v['href'] . '">' . $v['label'] . '</a></li>';

			} elseif ( is_string( $v ) ) {
				$ret .= '<li><a href="#">' . $v . '</a></li>';
			}
		}
			$ret .=
			'</ul>
		</div>';

		return $ret;
	}

	/**
	 * Defines if $value belongs to select values
	 * @param String $value Value to search for
	 * @param Array $select_value Select element values
	 * @return Boolean
	 */
	public function compare_select_value( $value, $select_value ) {
		foreach( (array)$select_value as $sv ) {
			if( in_array( gettype( $sv ), array( 'array', 'object', 'resource' ) ) ) {
				continue;
			}

			if( $value == $sv ) {
				return true;
			}
		}

		return false;
	}
}
. '-value',
						'name'  => $name ? $name . '[value]' : '',
						'value' => $value,
						'css'   => 'width:80px',
						'class' => 'form-control',
					),
					'addon_after' => array(
						'type'        => 'button',
						'id'          => $id . '-units',
						'name'        => $name ? $name . '[