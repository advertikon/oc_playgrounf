( function scope() {
	"use strict";

	ADK.summernoteHint = null;

	/**
	 * Opens file browser window
	 * @param {string} actionUrl File browser back-end action URL
	 * @param {function} callback Callback function
	 *  this - button element, which opened the window
	 * @returns {void}
	 */
	ADK.openFileBrowser = function ob( actionUrl, callback ) {

		var id = null;

		if ( typeof this.modal === "undefined" ) {

			this.modal =
			$( "<div class=\"modal file-browser fade\" tabindex=\"-1\"" +
				" role=\"dialog\" aria-labelledby=\"modal alert messenger\">" +
						"<div class=\"modal-dialog modal-lg\">" +
						"<div class=\"modal-content\">" +
							"<div class=\"modal-header\">" +
								"<button type=\"button\" class=\"close\" data-dismiss=\"modal\"" +
									"aria-label=\"Close\">" +
										"<span aria-hidden=\"true\">&times;</span>" +
								"</button>" +
								"<h4 class=\"modal-title\">" + ADK.locale.fileBrowser + "</h4>" +
							"</div>" +
							"<div class=\"modal-body\"></div>" +
						"</div>" +
				"</div>" );

			this.modal.find( ".modal-body" )
				.loadElement( "iframe", actionUrl.replace( /&amp;/, "&" ) );

			// Tie iFrame to opening button
			id = Math.round( Math.random() * 1000000000 );
			this.modal.find( "iframe" )[ 0 ].button_id = id;
			$( this ).attr( "button_id", id );

			this.callback = callback;
			this.input = $( "input[data-key='" + $( this ).attr( "data-key" ) + "']" );
		}

		$( ".modal" ).not( this.modal )
			.modal( "hide" );
		this.modal.modal( "show" );
	};

	/**
	 * Adds email attachment
	 * @param {object} file File data
	 * @returns {void}
	 */
	ADK.addAttachment = function a( file ) {

		// Input element
		var $input = this.input,
			value = $input.val();

		try {
			if( !value || typeof value !== "string" ) {
				throw "Error";
			}

			value = JSON.parse( ADK.htmlSpecialcharsDecode( value ) );

		} catch ( ex ) {
			value = {};
		}

		value[ file.phash + file.hash ] = file;

		// Trigger update to re-render attachments list, input - to save template snapshot
		$input.val( JSON.stringify( value ) ).trigger( "update" )
			.trigger( "input" );
	};

	/**
	 * Renders attachments list
	 * @returns {void}
	 */
	ADK.renderAttachments = function r() {
		var
			$attachmentsWrapper = null,
			$tbody = null,

			// The attachments element
			$this = $( this ),
			attachments = $this.val();

		try {

			if( !attachments || typeof attachments !== "string" ) {
				throw "Empty";
			}

			attachments = JSON.parse( ADK.htmlSpecialcharsDecode( attachments ) );
			$attachmentsWrapper = $this.parent().find( ".attachments-wrapper" );

			// Insert attachments wrapper if not present
			if( $attachmentsWrapper.length === 0 ) {
				$attachmentsWrapper = $(
					"<div class='table-responsive attachments-wrapper'>" +
						"<table class='table table-condensed'>" +
							"<thead>" +
								"<tr>" +
									"<th></th>" +
									"<th>" + ADK.locale.name +
									"<th>" + ADK.locale.size +
									"<th>" + ADK.locale.embed + " " + ADK.locale.embedTooltip +
									"<th>" + ADK.locale.del +
								"<tr>" +
							"</thead>" +
							"<tbody></tbody>" +
						"</table>" +
					"</div>"
				);

				$this.after( $attachmentsWrapper );
			} else {
				$attachmentsWrapper.find( "tbody" ).empty();
			}

			$tbody = $attachmentsWrapper.find( "tbody" );
			$tbody[ 0 ].attachment = this;

			// Add attachment items
			$.each( attachments, function a( id, elem ) {
				if( this.name && this.size ) {
					$tbody.append(
						"<tr class='attachment-item' data-hash='" + id + "'>" +
							"<td>" +
								"<i class='fa fa-" + ADK.getMimeIcon( elem.mime ) +
									" attachment-item-icon'></i>" +
							"</td>" +
							"<td>" + elem.name + "</td>" +
							"<td>" + ADK.convertBytes( elem.size ) + "</td>" +
							"<td>" +
								"<span class='attachment-item-checkbox'>" +
									"<input type='hidden' class='fancy-checkbox' value='" +
										( ADK.isEmpty( elem.embed ) ? 0 : 1 ) + "'>" +
								"</span>" +
							"</td>" +
							"<td>" +
								"<button type='button' class='btn btn-danger'>" +
									"<i class='fa fa-close'></i>" +
								"</button>" +
							"</td>" +
						"</tr>"
					);
				}

				// Mark attachment as embedded
				$tbody.delegate( ".fancy-checkbox", "change", function a1() {
					var value = JSON.parse( $this.val() );

					value[ $( this ).parents( "tr" )
						.attr( "data-hash" ) ].embed = this.value;

					// Save template snapshot without rendering of attachments list
					$this.val( JSON.stringify( value ) ).trigger( "input" );
				} );

				// Delete attachment
				$tbody.delegate( "button", "click", function a2() {
					var value = JSON.parse( $this.val() );

					delete value[ $( this ).parents( "tr" )
						.attr( "data-hash" ) ];

					// Save template snapshot and re-render attachments list
					$this.val( JSON.stringify( value ) ).trigger( "input" )
						.trigger( "update" );
				} );
			} );

			if( 0 === $attachmentsWrapper.find( ".attachment-item" ).length ) {
				throw "Empty list";
			}

		} catch ( e ) {
			$this.parents( ".form-group" ).fadeOut();

			return;
		}

		$attachmentsWrapper.find( ".fancy-checkbox" ).fancyCheckbox();

		$this.parents( ".form-group" ).fadeIn();

	};

	/**
	 * Returns fa icon depend on file mime type
	 * @param {string} mime File mime type
	 * @returns {string} Fa file icon
	 */
	ADK.getMimeIcon = function m( mime ) {
		var icon = null;

		switch( mime ) {
		case "application/msword":
		case "application/vnd.ms-word.document.macroenabled.12" :
		case "application/vnd.ms-word.template.macroenabled.12" :
		case "application/vnd.openxmlformats-officedocument.wordprocessingml.document" :
		case "application/vnd.openxmlformats-officedocument.wordprocessingml.template" :
			icon = "file-word-o";
			break;
		case "application/rtf" :
			icon = "fle-text";
			break;
		case "application/pdf" :
			icon = "file-pdf-o";
			break;
		case "application/zip" :
			icon = "file-zip-o";
			break;
		case "application/vnd.ms-excel" :
		case "application/vnd.ms-excel.addin.macroenabled.12" :
		case "application/vnd.ms-excel.sheet.binary.macroenabled.12" :
		case "application/vnd.ms-excel.sheet.macroenabled.12" :
		case "application/vnd.ms-excel.template.macroenabled.12" :
		case "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" :
		case "application/vnd.openxmlformats-officedocument.spreadsheetml.template" :
			icon = "file-excel-o";
			break;
		case "application/vnd.ms-powerpoint" :
		case "application/vnd.ms-powerpoint.addin.macroenabled.12" :
		case "application/vnd.ms-powerpoint.presentation.macroenabled.12" :
		case "application/vnd.ms-powerpoint.slide.macroenabled.12" :
		case "application/vnd.ms-powerpoint.slideshow.macroenabled.12" :
		case "application/vnd.ms-powerpoint.template.macroenabled.12" :
		case "application/vnd.openxmlformats-officedocument.presentationml.presentation" :
		case "application/vnd.openxmlformats-officedocument.presentationml.slide" :
		case "application/vnd.openxmlformats-officedocument.presentationml.slideshow" :
		case "application/vnd.openxmlformats-officedocument.presentationml.template" :
			icon = "file-powerpoint-o";
			break;
		case "text/cache-manifest" :
		case "text/calendar" :
		case "text/css" :
		case "text/csv" :
		case "text/html" :
		case "text/x-php" :
			icon = "file-code-o";
			break;
		case "application/mp21" :
		case "application/mp4" :
		case "application/ogg" :
			icon = "file-audio";
			break;
		default:
			icon = null;
			break;
		}

		if( null === icon ) {
			if( /^text\//.test( mime ) ) {
				icon = "file-text-o";

			} else if ( /^application\//.test( mime ) ) {
				icon = "file-code-o";

			} else if ( /^audio\//.test( mime ) ) {
				icon = "file-audio-o";

			} else if ( /^image\//.test( mime ) ) {
				icon = "file-image-o";

			} else if ( /^video\//.test( mime ) ) {
				icon = "file-video-o";

			} else {
				icon = "file-o";
			}
		}

		return icon;
	};

	/**
	 * Mark image as embedded
	 * @returns {void}
	 */
	ADK.elfinderEmbed = function elf() {
		var
			$embedInput = null,
			$this = $( this );

		$embedInput = $this.find( ".embed-input" );

		if( ADK.isEmpty( $embedInput.val() ) ) {
			$embedInput.val( 1 );
			$this
				.removeClass( "attached" )
				.addClass( "embedded" );
		} else {
			$embedInput.val( 0 );
			$this
				.removeClass( "embedded" )
				.addClass( "attached" );
		}
	};

	/**
	 * Sets image
	 * @param {object} file FIle data
	 * @returns {void}
	 */
	ADK.setImg = function im( file ) {
		var fileUrl = ADK.locale.thumbnailUrl.replace( /&amp;/, "&" ) + "&path=" +
			file.path + "/" + file.name;

		this.input.val( file.path + "/" + file.name );
		$( this )
			.removeClass( "removing" )
			.find( "img" )
				.attr( "src", fileUrl );
	};

} )();

$( document ).ready( function ready() {
	ADK.summernoteHint = {
		words:  ADK.locale.shortcodes,
		match:  /(\{[^}]{2,})$/,
		search: function summrnoteHintSearch( keyword, callback ) {
			callback( $.grep( this.words, function grepWords( item ) {
				return item.indexOf( keyword ) === 0;
			} ) );
		}
	};

	// Open file browser window to select mail attachment
	$( document ).delegate( ".attachment", "click", function a() {
		ADK.openFileBrowser.call( this, ADK.locale.elfinderAttachmentHref, ADK.addAttachment );
	} );

	// Listen to messages from frames
	window.addEventListener( "message", function c( e ) {
		var
			$button = $( "[button_id=" + e.data.buttonId + "]" ),
			attachment = {};

		// Message not from an add attachment window
		if( "file" !== e.data.type || 0 === $button.length ) {
			return;
		}

		$button[ 0 ].modal.modal( "hide" );

		attachment.name = e.data.name;
		attachment.path = e.data.path;
		attachment.size = e.data.size;
		attachment.mime = e.data.mime;
		attachment.hash = e.data.hash;
		attachment.phash = e.data.phash;

		if( typeof $button[ 0 ].callback === "function" ) {
			$button[ 0 ].callback( attachment );
		}
	} );

	// Bind rendering on each attachments list change event
	$( document ).delegate( ".attachment-field", "update", function ss() {
		ADK.renderAttachments.call( this );
	} );

	// Elfinder image click event handler
	$( document ).delegate( ".elfinder", "click", function click( e ) {
		e.preventDefault();

		if( typeof this.clickTimeout === "undefined" ) {
			this.clickTimeout = [];
		}

		this.clickTimeout.push( setTimeout( ADK.elfinderEmbed.bind( this ), 200 ) );
	} );

	// Elfinder image double click event handler
	$( document ).delegate( ".elfinder", "dblclick", function doubleClick() {

		// Remove pending click handlers
		$.each( this.clickTimeout, function clearTimeouts() {
			clearTimeout( this );
		} );

		ADK.openFileBrowser.call( this, ADK.locale.elfinderImgHref, ADK.setImg );

	} );

	$( document ).delegate( ".remove-image", "click", function removeImage( e ) {
		var $this = $( this );

		e.stopPropagation();
		e.preventDefault();

		$this
		.parent()
			.addClass( "removing" )
			.find( "input[data-key]" )
			.val( "" )
		.end()
			.find( "img" )
			.attr( "src", ADK.locale.thumbnailUrl.replace( /&amp;/, "&" ) + "&path=no_image.png" );
	} );

} );
