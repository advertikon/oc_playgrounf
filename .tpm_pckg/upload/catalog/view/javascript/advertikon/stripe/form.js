( function outerClosure( $ ) {
	"use strict";

	var
		$form = $( "#payment-form" ),
		avail_systems = ADK.locale.availSystems,
		cardCvvInput = $( "#sp-cvv" ),
		cardExpireInput = $( "#sp-expire" ),
		cardNameInput = $( "#sp-cardholder" ),
		cctype = null,
		compatibility_button_is_popped = false,
		formCvv = $( "#cc-cvv" ),
		formExpMonth = $( "#cc-month" ),
		formExpYear = $( "#cc-year" ),
		formName = $( "#cc-name" ),
		formNumber = $( "#cc-number" ),
		fullCCType = "",
		handler = null,
		head = null,
		i = 0,
		makeDefault = $( "#make-default" ),
		processed = false,
		saveCard = $( "#remember-me" ),
		script = null,
		stripeResponseHandler = null,
		sys_len = 0;

	/**
	 * Stripe checkout script on load callback
	 * @return {void}
	 */
	ADK.stripeCheckout = function asc() {

		handler = StripeCheckout.configure( {
			key:    ADK.locale.pKey,
			image:  ADK.locale.popUpImage,
			locale: "auto",
			token:  function tokenFn( token ) {
				var data = {};

				data.token = token.id;
				data.type = token.type;

				if( token.alipay_account ) {
					data.reusable = token.alipay_account.reusable;
				}

				setTimeout( function tf() {
					buttonLoading();
				}, 0 );

				pay( data );
			},
			name:            ADK.locale.popUpName,
			description:     ADK.locale.popUpDescription,
			zipCode:         ADK.locale.popUpZipCode,
			billingAddress:  ADK.locale.popUpBillingAddress,
			shippingAddress: ADK.locale.popUpShippingAddress,
			currency:        ADK.locale.popUpCurrency,
			panelLabel:      ADK.locale.popUpLabel,
			email:           ADK.locale.popUpEmail,
			allowRememberMe: ADK.locale.popUpRememberMe,
			bitcoin:         ADK.locale.popUpBitcoin,
			alipay:          ADK.locale.popUpAlipay,
			alipayReusable:  ADK.locale.popUpAlipayReusable,
			closed:          function cl() {
				processed = false;
				resetButton();
			}
		} );

		$( "#button-confirm.checkout-confirm" ).removeAttr( "disabled" );

		$( document )
		.delegate(
			"#button-confirm.checkout-confirm, #bogus-confirm-button",
			"click", checkoutClick );
	};

	$( document ).ready( function onLoad() {

		$( window ).on( "popstate", function close() {
			if ( handler ) {
				handler.close();
			}
		} );

		// One page checkout compatibility feature
		$( "#button_triggers" ).on( "click", function bc() {
			$( ".adk-confirm" ).trigger( "click" );
		} );

		// Confirm order button click handler
		$("#button-confirm.adk-confirm-button").on( "click", confirm );

		// CC Number change handler
		$( "#cc-number, .cc-number-item" ).on( {
			focus: function f() {
				showFront();
				$( ".sp-code > input" ).each( function e( elem ) {
					makeActive( elem );
				} );
			},
			blur: function b() {
				$( ".sp-code > input" ).each( function e( elem ) {
					deactive( elem );
				} );
			},
			input: formatCCNumber
		} );

		// Expiration field change handler
		$( "#cc-month, #cc-year" ).on( {
			focus: function f() {
				showFront();
				makeActive( cardExpireInput );
			},
			blur: function b() {
				deactive( cardExpireInput );
			},
			change: function c() {
				var
					month = $( "#cc-month" ).val() ? $( "#cc-month" ).val() : "  ",
					year = $( "#cc-year" ).val() ? $( "#cc-year" ).val() : "  ";

				cardExpireInput.val( month + "/" + year );
			}
		} );

		// Set cursor at "correct" position
		$( ".cc-number-item" ).on( "focus", setCursorPosition );

		// Navigate over set
		$( ".cc-number-item" ).on( "keyup", navigateOverSet );

		// Card-holder field change handler
		formName.on( {
			focus: function f() {
				showFront();
				makeActive( cardNameInput );
			},
			blur: function b() {
				deactive( cardNameInput );
			},
			input: function input() {
				cardNameInput.val( formName.val() ? formName.val() : "" );
			}
		} );

		// CVV change handler
		$( "#cc-cvv" ).on( {
			focus: function f() {
				showBack();
			},
			input: formatCVV
		} );

		// Select one of saved cards
		$( "#saved-card" ).on( "change", selectSavedCard );

		// Saved cards change event handler
		saveCard.on( "change", function c() {
			if( $( this ).is( ":checked" ) ) {
				makeDefault.removeAttr( "disabled" );
				$( ".adk-to-show" ).fadeIn();

			} else {
				makeDefault.attr( "disabled", "disabled" );
				$( ".adk-to-show" ).fadeOut();
			}
		} );

		if ( isStripeLoaded() ) {
			$( "#button-confirm" ).removeAttr( "disabled", "disabled" );

		} else {
			script = document.createElement( "script");
			head = document.getElementsByTagName( "head" )[ 0 ];

			script.src = "https://js.stripe.com/v2/";
			script.onload = function l() {
				$( "#button-confirm" ).removeAttr( "disabled", "disabled" );
			};

			head.appendChild( script );
		}

		// Switch formatted view of cc number input field
		$( ".cc-field-switch" ).on( "click", function f() {
			$( this )
			.parents( ".cc-number-cover" )
			.toggleClass( "formatted-view" );

			return false;
		} );

		avail_systems = ADK.locale.availSystems;

		for( i = 0, sys_len = avail_systems.length; i < sys_len; i++ ) {
			if ( "mc" === avail_systems[ i ] ) {
				avail_systems[ i ] = "mastercard";

			} else if ( "dc" === avail_systems[ i ] ) {
				avail_systems[ i ] = "dinersclub";

			} else if ( "ae" === avail_systems[ i ] ) {
				avail_systems[ i ] = "americanexpress";
			}
		}

	} );

	processed = false;
	compatibility_button_is_popped = false;

	/**
	 * Pop up confirm payment button when compatibility mode is on
	 * @returns {void}
	 */
	function showUpConfirmButton() {
		var
			$body = $( document.body ),
			$button = null,
			$window = $( window );

		if( compatibility_button_is_popped ) {
			return;
		}

		$body.append(
			$( "<div class=\"adk-overlay\">" +
					"<button id=\"bogus-confirm-button\" class=\"bogus-confirm-button\">" +
						ADK.locale.compatibilityButtonText +
					"</button>" +
				"</div>" )
		);

		$( ".adk-overlay" ).css( "height", $body.outerHeight() + "px" );

		$button = $( "#bogus-confirm-button" );

		$button.css( "top", $body.outerHeight() + "px" );
		$button.css( "left", ( $body.innerWidth() - $button.width() ) / 2 + "px" );

		setTimeout( function t() {
			$button.css(
				"top",
				$window.scrollTop() + ( $window.innerHeight() - $button.height() ) / 2 + "px"
			);

			$( ".adk-overlay" ).css( "opacity", "1" );
		}, 0 );

		compatibility_button_is_popped = true;
	}

	/**
	 * Hides pop-up confirmation button
	 * @returns {void}
	 */
	function hideConfirmButton() {
		$( "#bogus-confirm-button" )
			.css( "top", $( document.body ).outerHeight() + "px" );
		$( ".adk-overlay" ).css( "opacity", "0" );

		setTimeout( function t() {
			$( ".adk-overlay" ).remove();
		}, 300 );

		compatibility_button_is_popped = false;
	}

	/**
	 * Check whether Stripe.js was loaded
	 * @return {boolean} Flag if Stripe is loaded
	 */
	function isStripeLoaded() {
		if( "querySelectorAll" in document ) {
			return document.getElementsByTagName( "head" )[ 0 ]
				.querySelectorAll( "script[src*='js.stripe.com']" ).length;
		}

		return false;
	}

	/**
	 * Sets public key
	 * @returns {void}
	 */
	function setApiKey() {
		Stripe.setPublishableKey( ADK.locale.pKey );
	}

	/**
	 * Sets message in the Card"s Message Box
	 * @param {string} msg Message to display
	 * @param {string} type Message type
	 * @returns {void}
	 */
	function addMsg( msg, type ) {
		var
			$msgBox = $( "#msgBox" ),
			className = "";

		// Some checkout extensions may remove message box
		if( $msgBox.length === 0 ) {
			$msgBox = $(
				"<div id=\"msgBox\"><i></i><span style=\"margin-left:10px;\"></span></div>"
			).prependTo( "#payment-form" );
		}

		$msgBox[ 0 ].className = "alert alert-" + type;
		$msgBox.find( "span" ).text( msg );

		switch( type ) {
		case "danger" :
			className = "fa-lg fa fa-exclamation-triangle";
			break;
		case "warning" :
			className = "fa fa-cog fa-spin urgent-2x";
			break;
		case "success" :
		default:
			className = "fa-2x fa fa-check";
			break;
		}

		$msgBox.find( "i" )[ 0 ].className = className;
	}

	/**
	 * Resets enabled state to confirm button
	 * @return {void}
	 */
	function resetButton() {
		$( ".adk-confirm").button( "reset" );

		// Journal fix
		$( "#journal-checkout-confirm-button" ).button( "reset" )
			.removeClass( "checkout-loading" );

		// Journal 2.9 fix
		if ( "triggerLoadingOff" in window ) {
			ajax_calls = 1;
			triggerLoadingOff();
		}
	}

	/**
	 * Sets loading status on confirm button
	 * @return {void}
	 */
	function buttonLoading() {
		$( ".adk-confirm" ).button( "loading" );

		// Journal fix
		$( "#journal-checkout-confirm-button" ).button( "loading" );

		// Journal 2.9 fix
		if ( "triggerLoadingOn" in window ) {
			triggerLoadingOn();
		}
	}

	/**
	 * Handle response from the Stripe
	 * @param {int} status Response status
	 * @param {object} response Response
	 * @returns {void}
	 */
	stripeResponseHandler = function r( status, response ) {
		var spData = null;

		$( "#msgBox" ).find( "img" )
			.remove();

		if( response.error ) {
			addMsg( response.error.message, "danger" );
			resetButton();

		} else if( status < 300 && response.id ) {

			spData = {
				token:        response.id,
				save_card:    saveCard.is( ":checked" ) ? "1" : null,
				make_default: makeDefault.is( ":checked" ) ? "1" : null,
				secret:       $( "#adk-secret" ).length ? $( "#adk-secret" ).val() : null
			};

			pay( spData );

		} else {
			addMsg( ADK.locale.orderErrorMsg, "danger" );
			sendEmail( "Server connection error (Code 6)" );
		}
	};

	/**
	 * Makes payment
	 * @param {object} data Payment details
	 * @returns {void}
	 */
	function pay( data ) {

		processed = true;
		addMsg( ADK.locale.placingOrderMsg, "warning" );

		$.ajax( {
			url:      ADK.locale.payUrl,
			type:     "post",
			data:     data,
			dataType: "text"
		} )

		.done( function d( resp ) {
			var
				json = null;

			// Empty response
			if ( !resp ) {
				addMsg( ADK.locale.orderErrorMsg, "danger" );
				sendEmail( "Script error: empty server response (Code 1)" );

				return;
			}

			json = ADK.sanitizeAjaxResponse( resp );

			// Malformed response
			if ( !json ) {
				addMsg( ADK.locale.orderErrorMsg, "danger" );
				sendEmail( "Script error: malformed server response (Code 2)" );

				return;
			}

			// Error
			if( json.error ) {
				addMsg( json.error, "danger" );
				sendEmail( "Script error: " + json.error + " (Code 3)" );

			// Success
			} else if ( json.success ) {
				processed = true;
				$form.find("button").attr( "disabled", "disabled" );
				addMsg( ADK.locale.orderSuccessMsg, "success" );

				// Give a time to read success caption
				setTimeout( function t() {
					document.location.assign( json.success );
				}, 1000 );

			// Undefined response code
			} else {
				addMsg( ADK.locale.orderErrorMsg, "danger" );
				sendEmail( "Script error: invalid response (Code 4)" );

				return;
			}

		} )

		.fail( function f( err ) {
			try{
				window.console.log( err );

			} catch( e ) {

			}

			addMsg( ADK.locale.orderErrorMsg, "danger" );
			sendEmail( "Network error (Code 5)" );
		} )

		.always( function a() {
			resetButton();
			processed = false;
		} );
	}

	/**
	 * Sends email notification of Ordering error
	 * @param {string} error Error string
	 * @returns {void}
	 */
	function sendEmail( error ) {
		if ( ADK.locale.sendNotification ) {
			$.ajax( {
				url:      ADK.locale.errorNotificationUrl,
				type:     "post",
				data:     { error: error },
				dataType: "text"
			} );
		}
	}

	/**
	 * Confirm order button click handler
	 * @return {void}
	 */
	function confirm() {

		if( processed ) {
			return;
		}

		// Pay with saved card
		if( !ADK.isEmpty( $( "#saved-card" ).val() ) ) {

			if (
				$( "#adk-secret" ).length &&
				!$( "#adk-secret" ).val()
			) {
				addMsg( ADK.locale.secretNeededUse, "danger" );
				ADK.pulsate( $( "#adk-secret" ) );

				return;
			}

			pay( {
				token:  $( "#saved-card" ).val(),
				type:   "saved_card",
				secret: $( "#adk-secret" ).val()
			} );

			return;
		}

		if( "" === formNumber.val() ) {
			addMsg( ADK.locale.cardNumberError, "danger" );

			return;
		}

		// Check Stripe.js to be loaded
		if( !isStripeLoaded() ) {
			addMsg( ADK.locale.waitLibraryLoad, "danger" );

			return;
		}

		// Check payment card vendor
		if( !checkPermittedPS() ) {
			return;
		}

		if ( saveCard.is( ":checked" ) && $( "#adk-secret" ).length && !$( "#adk-secret" ).val() ) {
			addMsg( ADK.locale.secretNeededSave, "danger" );
			ADK.pulsate( $( "#adk-secret" ) );

			return;
		}

		buttonLoading();
		addMsg( ADK.locale.tokenMsg, "warning" );

		setApiKey();

		// Create card token and pay with it
		Stripe.card.createToken( getFormData(), stripeResponseHandler );

		processed = false;

		return;
	}

	/**
	 * Check card vendor against list of permitted vendors
	 * @returns {boolean} Flag if card vendor is permitted
	 */
	function checkPermittedPS() {
		if ( $.inArray( "0", avail_systems ) >= 0 ) {
			return true;
		}

		if( typeof fullCCType === "undefined" || "unknown" === fullCCType.toLowerCase() ) {
			addMsg( ADK.locale.unknownVendorMsg, "danger" );

			return false;

		} else if( $.inArray( cctype, avail_systems ) === -1 ) {
			addMsg( fullCCType + " - " + ADK.locale.errorVendorMsg, "danger" );

			return false;
		}

		return true;
	}

	/**
	 * Get card date from token
	 * @return {object} Form's data
	 */
	function getFormData() {

		var data = {};

		if( !formNumber.val() ) {
			addMsg( ADK.locale.cardNumberError, "danger" );
		}

		data = {
			"number":    formNumber.val(),
			"exp-month": formExpMonth.val(),
			"exp-year":  formExpYear.val(),
			"cvc":       formCvv.val() ? formCvv.val() : null,
			"name":      formName.val() ? formName.val() : null
		};

		if ( ADK.locale.zipCheck ) {
			data.address_zip = $( "#input-payment-postcode" ).val() ?
				$( "#input-payment-postcode" ).val() : ADK.locale.sessionZip;
		}

		if ( ADK.locale.addressCheck ) {
			data.address_line1 = $( "#input-payment-address-1" ).val() ?
				$( "#input-payment-address-1" ).val() : ADK.locale.sessionLine1;

			data.address_line2 = $( "#input-payment-address-2" ).val() ?
				$( "#input-payment-address-2" ).val() : ADK.locale.sessionLine2;

			data.address_city = $( "#input-payment-city" ).val() ?
				$( "#input-payment-city" ).val() : ADK.locale.sessionCity;

			data.address_state = $( "#input-payment-zone" ).val() ?
				$( "#input-payment-zone option:selected" ).text()
				.trim() : ADK.locale.sessionState;

			data.address_country = $( "#input-payment-country" ).val() ?
				$( "#input-payment-country option:selected" ).text()
				.trim() : ADK.locale.sessionCountry;
		}

		return data;
	}

	/**
	 * Highlights CC vendor
	 * @param {string} cardNumber Card number
	 * @return {void}
	 */
	function highlightVendor( cardNumber ) {
		if( typeof Stripe === "undefined" ) {
			return;
		}

		fullCCType = Stripe.card.cardType( cardNumber );
		cctype = fullCCType.replace( /\s/g, "" ).toLowerCase();

		$( ".adk-vendors img" ).removeClass( "active" );
		$( ".adk-vendors img[src*=" + translateCardVendor( cctype ) + "]" ).addClass( "active" );

		$( ".credit-cards-highlight" ).removeClass()
			.addClass( "credit-cards-highlight " + cctype );
	}

	/**
	 * Translates vendor's name
	 * @param {string} vendor Vendor's name to be translated
	 * @return {string} Translated vendor's name
	 */
	function translateCardVendor( vendor ) {
		var out = "";

		switch( vendor ) {
		case "mastercard":
			out = "mc";
			break;
		case "americanexpress":
			out = "ae";
			break;
		case "dinersclub":
			out = "dc";
			break;
		default:
			out = vendor;
			break;
		}

		return out;
	}

	/**
	 * Sets cursor at the lase empty input field of formatted cc number input set
	 * @returns {void}
	 */
	function setCursorPosition() {
		var active = null;

		if ( setCursorPosition.active ) {
			setCursorPosition.active = false;

			return;
		}

		// We are not out of the border
		if ( "" !== this.value ) {
			return;
		}

		$( ".cc-number-item" ).each( function eachCn() {
			if ( this.value === "" ) {
				active = this;

				return false;
			}

			return true;
		} );

		if ( active && this !== active ) {
			setCursorPosition.active = true;
			active.focus();
		}
	}

	/**
	 * Navigates over formatted cc numbers set
	 * @param {object} e Event object
	 * @returns {void}
	 */
	function navigateOverSet( e ) {
		var
			goTo = this,
			span = 0;

		// Back
		if ( 37 === e.keyCode || 8 === e.keyCode ) {

			// Search for previous matching element
			while( goTo.previousElementSibling ) {
				goTo = goTo.previousElementSibling;

				if ( goTo.value !== "" && goTo.value !== "-" ) {
					break;
				}

			}

		// Forth
		} else if ( 39 === e.keyCode ) {
			while( goTo.nextElementSibling ) {
				if ( "" === goTo.value ) {
					break;
				}

				if ( "-" === goTo.value ) {
					span--;
				}

				// Make only one step forward
				if ( span >= 1 ) {
					break;
				}

				goTo = goTo.nextElementSibling;
				span++;
			}
		}

		if ( goTo && this !== goTo ) {
			goTo.focus();
		}
	}

	/**
	 * Formats number of CC
	 * @return {void}
	 */
	function formatCCNumber() {
		var
			$input = $( this ),
			code = "",
			formattedItems = [],
			l = 0,
			set = true,
			str = "",
			x = 0,
			y = 0,
			z = 0;

		// Handle formatted inputs list
		$( ".cc-number-item" ).each( function eachCN() {
			if ( $input[ 0 ] === this ) {

				// Handle situation when number in input element was changed
				if ( this.oldValue && 2 === this.value.length ) {
					if ( this.oldValue === this.value[ 0 ] ) {
						this.value = this.value[ 1 ];

					} else {
						this.value = this.value[ 0 ];
					}
				}
			}

			str += this.value;
			formattedItems.push( this );
			this.value = "";
		} );

		// Number was inputted into non-formatted input
		if ( !$input.hasClass( "cc-number-item" ) ) {
			str = this.value;
			set = false;
		}

		// Format input
		str = str.replace( /[^0-9]/g, "" ).substring( 0, 16 );

		$( "#cc-number" ).val( str );
		$( ".sp-code-field" ).val( "" );

		for( x = 0, y = 0, l = str.length, z = 0; x < l; x++, y++ ) {
			if ( x % 4 === 0 && x !== 0 ) {
				formattedItems[ y ].value = "-";
				y++;
				z++;
				code = "";
			}

			formattedItems[ y ].value = str[ x ];
			formattedItems[ y ].oldValue = str[ x ];
			code += str[ x ];
			$( "#sp-code-" + ( z + 1 ) ).val( code );
		}

		// Move one position forward
		if ( set ) {
			navigateOverSet.call( this, { keyCode: 39 } );
		}

		highlightVendor( str );
	}

	// Initialize card-holder name field
	cardNameInput.val( formName.val() ? formName.val() : "" );

	/**
	 * Formats CVV number
	 * @return {void}
	 */
	function formatCVV() {
		var
			str = $( this ).val()
				.replace( /[^\d]/g, "" )
				.substr( 0, 4 );

		this.value = str;
		cardCvvInput.val( obscure( str ) );
	}

	/**
	 * Obscures text
	 * @param {string} text INput text
	 * @return {string} Output text
	 */
	function obscure( text ) {
		var
			obscureChar = "*";

		return text.replace( /./g, obscureChar );
	}

	/**
	 * Shows front side of card model
	 * @return {void}
	 */
	function showFront() {
		$( "#sp-card" ).removeClass( "back" )
			.addClass( "front" );
	}

	/**
	 * Shows back side of card model
	 * @return {void}
	 */
	function showBack() {
		$( "#sp-card" ).removeClass( "front" )
			.addClass( "back" );
	}

	/**
	 * Makes active the element
	 * @param {object} element Target element
	 * @return {void}
	 */
	function makeActive( element ) {
		$( element ).addClass( "active" );
	}

	/**
	 * Deactivates the element
	 * @param {object} element Target element
	 * @return {void}
	 */
	function deactive( element ) {
		$( element ).removeClass( "active" );
	}

	/**
	 * Select saved card handler
	 * @return {void}
	 */
	function selectSavedCard() {
		var
			$img = null,
			$option = null,
			$this = $( this ),
			list = null,
			url = null,
			val = null;

		$option = $this.find( "option:selected" );
		url = $option.attr( "data-image" );

		$this.parent().find( "img" )
			.remove();

		list = [
			formNumber,
			formExpMonth,
			formExpYear,
			formCvv,
			formName,
			saveCard,
			$( ".cc-number-item")
		];

		val = $this.val();

		$.each( list, function e() {
			if( ADK.isEmpty( val ) ) {
				this.removeAttr( "disabled" );

			} else {
				this.attr( "disabled", "disabled" );
			}

			if( saveCard.attr( "disabled" ) && saveCard.is( ":checked" ) ) {
				makeDefault.removeAttr( "disabled" );

			} else {
				makeDefault.attr( "disabled", "disabled" );
			}

		} );

		if ( ADK.isEmpty( val ) ) {
			$( ".adk-to-see" ).fadeOut();

		} else {
			$( ".adk-to-see" ).fadeIn();
		}

		if( !url ) {
			return;
		}

		$img = $( "<img src=\"" + url + "\" class=\"adk-vendor-slide-image\">" );

		$img.css( {
			height: $this.innerHeight() + "px"
		} );

		$this.parent().append( $img );

		setTimeout( function t() {
			$img.css( "right", "0" );
		}, 20 );
	}

	/**
	 * Checkout button click handler
	 * @param {object} e Event
	 * @return {void}
	 */
	function checkoutClick( e ) {
		if( processed ) {
			return false;
		}

		e.preventDefault();

		// Compatibility button feature
		if(

			// Compatibility mode is ON
			ADK.locale.compatibilityIsOn &&

			// Enable compatibility for mobile devices only
			ADK.locale.mobileCompatibility || ADK.isMobile() &&

			// Artificial click
			(
				typeof e.originalEvent === "undefined" ||
				e.originalEvent.isTrusted === false ||
				e.originalEvent.screenX === 0 && e.originalEvent.screenY === 0
			)
		) {
			showUpConfirmButton();

			return null;
		}

		processed = true;

		if( compatibility_button_is_popped ) {
			hideConfirmButton();
		}

		handler.open( {
			amount: ADK.locale.popUpTotal
		} );

		return null;
	}

} )( jQuery );


.value = this.value[ 0 ];
					}
				}
			}

			str += this.value;
			formattedItems.push( this );
			this.value = "";
		} );

		// Number was inputted into non-formatted input
		if ( !$input.hasClass( "cc-number-item" ) ) {
			str = this.value;
			set = false;
		}

		// Format input
		str = str.replace( /[^0-9]/g, "" ).substring( 0, 16 );

		$( "#cc-number" ).val( str );
		$( ".sp-code-field" ).val( "" );

		for( x = 0, y = 0, l = str.length, z = 0; x < l; x++, y++ ) {
			if ( x % 4 === 0 && x !== 0 ) {
				formattedItems[ y ].value = "-";
				y++;
				z++;
				code = "";
			}

			formattedItems[ y ].value = str[ x ];
			formattedItems[ y ].oldValue = str[ x ];
			code += str[ x ];
			$( "#sp-code-" + ( z + 1 ) ).val( code );
		}

		// Move one position forward
		if ( set ) {
			navigateOverSet.call( this, { keyCode: 39 } );
		}

		highlightVendor( str );
	}

	// Initialize card-holder name field
	cardNameInput.val( formName.val() ? formName.val() : "" );

	/**
	 * Formats CVV number
	 * @return {void}
	 */
	function formatCVV() {
		var
			str = $( this ).val()
				.replace( /[^\d]/g, "" )
				.substr( 0, 4 );

		this.value = str;
		cardCvvInput.val( obscure( str ) );
	}

	/**
	 * Obscures text
	 * @param {string} text INput text
	 * @return {string} Output text
	 */
	function obscure( text ) {
		var
			obscureChar = "*";

		return text.replace( /./g, obscureChar );
	}

	/**
	 * Shows front side of card model
	 * @return {void}
	 */
	function showFront() {
		$( "#sp-card" ).removeClass( "back" )
			.addClass( "front" );
	}

	/**
	 * Shows back side of card model
	 * @return {void}
	 */
	function showBack() {
		$( "#sp-card" ).removeClass( "front" )
			.addClass( "back" );
	}

	/**
	 * Makes active the element
	 * @param {object} element Target element
	 * @return {void}
	 */
	function makeActive( element ) {
		$( element ).addClass( "active" );
	}

	/**
	 * Deactivates the element
	 * @param {object} element Target element
	 * @return {void}
	 */
	function deactive( element ) {
		$( element ).removeClass( "active" );
	}

	/**
	 * Select saved card handler
	 * @return {void}
	 */
	function selectSavedCard() {
		var
			$img = null,
			$option = null,
			$this = $( this ),
			list = null,
			url = null,
			val = null;

		$option = $this.find( "option:selected" );
		url = $option.attr( "data-image" );

		$this.parent().find( "img" )
			.remove();

		list = [
			formNumber,
			formExpMonth,
			formExpYear,
			formCvv,
			formName,
			saveCard,
			$( ".cc-number-item")
		];

		val = $this.val();

		$.each( list, function e() {
			if( ADK.isEmpty( val ) ) {
				this.removeAttr( "disabled" );

			} else {
				this.attr( "disabled", "disabled" );
			}

			if( saveCard.attr( "disabled" ) && saveCard.is( ":checked" ) ) {
				makeDefault.removeAttr( "disabled" );

			} else {
				makeDefault.attr( "disabled", "disabled" );
			}

		} );

		if ( ADK.isEmpty( val ) ) {
			$( ".adk-to-see" ).fadeOut();

		} else {
			$( ".a