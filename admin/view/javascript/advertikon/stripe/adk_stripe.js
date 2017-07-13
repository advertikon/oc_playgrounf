( function outerClosure( $ ) {
	"use strict";

	var
		autocompleteTimeout = null,
		currencies = [];

	// Add account name to each ajax request
	$.ajaxSetup( {
		beforeSend: function x( XHR, settings ) {
			settings.url += "&account=" + $( "#select-account" ).val();
		}
	} );

	// ******************* On document load stuff *****************************/

	$( document ).ready( function documentOnLoad() {

		// Summer-note
		$( ".summernote" ).each( function sm() {
			summernote.call( this );
		} );

		// Paginate plans tab
		$( ".tab-pane" ).delegate( ".pagination a[href]", "click", paginate );

		// Handle auto-complete fields actions
		// Auto-complete element should be child of input-group element or similar
		$( document.body ).delegate( "[data-autocomplete-url]", "focus input", autocomplete );

		// Hide all drop-down lists
		// Remove error state from all input fields
		$( document.body ).on( "click", makeSomeStuffOnBodyClick );

		// Listen to auto-complete done event
		$( document.body ).delegate(
			"[data-autocomplete-url]",
			"autocomplete.done",
			autocompleteDone
		);

		// Listen to auto-complete start event
		$( document.body ).delegate(
			"[data-autocomplete-url]",
			"autocomplete.start",
			autocompleteStart
		);

		// Listen to auto-complete error event
		$( document.body ).delegate( "[data-autocomplete-url]", "action.error", autocompleteError );

		// Listen to event on children"s content change (table cell modification)
		$( document.body ).delegate( "tr", "content.changed", onModifiy );

		// Perform main action on specific input element
		$( document.body ).delegate( ".input-group span.main", "click", doIt );

		// ********************************* Profile ***********************************************

		// Edit profile
		$( document.body ).delegate( ".save-profile", "click", editProfile );

		// Listen to profile page pagination
		ADK.e.subscribe(
			"profile.paginated",
			initProfileTab.bind( document.getElementById( "tab-plan-profile" ) )
		);

		// Add profile template
		$( document.body ).delegate( "#add-profile", "click", addProfileTemplate );

		// Remove profile record
		$( document.body ).delegate( ".delete-profile",	"click", function delPr() {
			ADK.confirm( ADK.locale.deleteProfileSure ).yes( removeProfile.bind( this ) );
		} );

		$( document.body ).delegate( ".map-select", "change", selectMapping );

		$( "#tab-plan-profile" ).on( "paginated", isRemoveProfileButtonActive );

		ADK.e.subscribe(
			[ "profile.*.deleted", "profile.*.updated" ],
			function callBack() {
				paginateProfilesMap( $( "#tab-plan-profile-map .pagination .active span" ).text() );
			}
		);

		$( document.body )
			.delegate( ".checkbox-group.totals-to-recurring input", "change", function d() {
				whatToShow( this );
			} );

		// Account templates handling
		$( "#tab-api" ).delegate( ".delete-account", "click", deleteAccount );

		/**
		 * Create unique account name (slug)
		 * Handle situation with repeated account names
		 */
		$( "#tab-api" ).delegate( ".account-name", "change", changeAccountName );

		$( "#select-account" ).on( "change", getPlans );

		$( "#add-account" ).on( "click", addAccount );

		// Existing currencies" codes
		$( ".account-currency" ).eq( 0 )
			.find( "option" )
			.each( function e() {
				currencies.push( $( this ).attr( "value" ) );
			} );

		$( "#tab-api" ).delegate( ".account-currency", "focus mousedown", function closure() {
			this.prevVal = this.value;
		} );

		$( "#tab-api" ).delegate( ".account-currency", "change", changeCurrency );

		$( "select.select2" ).each( initSelect2 );

		$( "#check-tls" ).on( "click", checkTls );

		checkAccountCurrency();

		// Fetch profiles
		paginateProfiles();

		// Fetch mapping
		paginateProfilesMap();

		// Fetch Recurring plans asynchronously
		// Fetch them at the end that not to block other XHR
		getPlans();

		// Scroll into view first field with error
		$( ".have-error" ).each( function checkErrors() {
			$( "a[href='#" + $(this).parents(".tab-pane")
				.attr("id") + "']" ).click();
			$( this ).scrollTo();

			return false;
		} );

		// Render ticket button
		renderTicketButton();

		// Rewrite default behavior so tab can be wrapped into elements
		$( "[data-toggle=tab-top]" ).each( function e() {
			this.onclick = function h() {
				$( "[data-toggle=tab-top]" )
					.parent()
					.removeClass( "active" );

				$( this )
					.parent()
					.addClass( "active" );

				$( ".top-pane" )
					.removeClass( "active" );

				$( ".top-pane" + $( this ).attr( "href" ) )
					.addClass( "active" );

				return false;
			};
		} );

		$( "[data-toggle=tab-top].active" ).click();
	} );

	// ************************** Functions ***********************************/

	/**
	 * Fetch Recurring plans" profiles asynchronously
	 * @param {(int|void)} page Page number
	 * @returns {void}
	 */
	function paginateProfiles( page ) {
		var
			$loader = null,
			$pane = $( "#tab-plan-profile" );

		$loader = $pane.find( ".wait-screen" );

		$loader.addClass( "shown" );

		$.get( ADK.locale.profileUrl.replace( /&amp;/g, "&" ), { page: page || 1 } )

		.always( function a() {
			$loader.removeClass( "shown" );
		} )

		.fail( function fail() {
			$pane.find( ".loader .msg" ).text( ADK.locale.errorCantProfilesList );
		} )

		.done( function d( resp ) {
			if( resp.substr( -7 ) === "success" ) {
				$pane[ 0 ].innerHTML = resp.slice( 0, -7 );

				isRemoveProfileButtonActive();

				$( ".property .checkbox-group.totals-to-recurring input" ).each( function e() {
					whatToShow( this );
				} );

				ADK.e.trigger( "profile.paginated" );

			} else if ( rest.substr( -5 ) === "error" ) {
				ADK.a.alert( resp.slice( 0, -5 ) );

			} else {
				$pane.find( ".loader .msg" ).text( ADK.locale.errorCantProfilesList );
			}
		} );
	}

	/**
	 * Hides/Shows an add by force setting item on status change of corresponding
	 * add to recurring item setting.
	 * @param {element} totalsToRecurring Total to recurring check-box element
	 * @returns {void}
	 */
	function whatToShow( totalsToRecurring ) {
		var
			addForce = $( totalsToRecurring )
				.parents( ".property" )
					.find(
						".checkbox-group.add-force input[value=" +
							totalsToRecurring.getAttribute( "value" ) + "]"
					);

		if ( $( totalsToRecurring ).is( ":checked" ) ) {
			addForce.parents( "label" ).fadeOut();

		} else {
			addForce.parents( "label" ).fadeIn();
		}
	}

	/**
	 * Whether remove profile buttons can be set active
	 * @returns {void}
	 */
	function isRemoveProfileButtonActive() {
		if(
			$( ".plans-container .property" ).length > 1 ||
			$( ".plans-container" ).parents( ".tab-pane" )
				.find( ".pagination .active span" )
				.text() > "1"
			) {
			$( "delete-profile" ).addClass( "active" );

		} else {
			$( ".delete-profile" ).removeClass( "active" );
		}
	}

	/**
	 * Fetch Recurring plans" profiles map asynchronously
	 * @param {(int|void)} page Page number
	 * @returns {void}
	 */
	function paginateProfilesMap( page ) {
		var
			$loader = null,
			$panel = $( "#tab-plan-profile-map" );

		$loader = $panel.find( ".wait-screen" );

		$loader.addClass( "shown" );

		$.get( ADK.locale.profileMapUrl.replace( /&amp;/g, "&" ), { page: page || 1 } )

		.always( function a() {
			$loader.removeClass( "shown" );
		} )

		.fail( function fail() {
			ADK.n.alert( ADK.locale.errorLoadProfilesMap );
		} )

		.done( function done( resp ) {
			if( resp.substr( -7 ) === "success" ) {
				$panel[ 0 ].innerHTML = resp.slice( 0, -7 );

			} else {
				ADK.n.alert( ADK.locale.errorLoadProfilesMap );
			}
		} );
	}

	/**
	 * Gets plans
	 * @returns {void}
	 * @triggers Event#plans.fetched
	 */
	function getPlans() {
		var wait = $( "#tab-plans .wait-screen" ).addClass( "shown" );

		$( "#tab-plans > *" ).not( ".static" )
			.remove();

		$.get( ADK.locale.plansUrl.replace( /&amp;/g, "&" ) )

		.always( function a() {
			wait.removeClass( "shown" );
		} )

		.fail( function fail() {
			ADK.n.alert( ADK.locale.plansError );
		} )

		.done( function done( resp ) {
			if( resp.substr( -7 ) === "success" ) {
				$( "#tab-plans" ).append( $( resp.slice( 0, -7 ) ) );
				ADK.e.trigger( "plans.fetched" );

			} else if ( resp.substr( -5 ) === "error" ) {
				ADK.n.alert( resp.slice( 0, -5 ) );

			} else {
				ADK.n.alert( ADK.locale.plansError );
			}
		} );
	}

	/**
	 * Checks existing accounts for currency
	 * Block/unblock add accounts button
	 * Return true if currency for different accounts do not repeated, false otherwise.
	 * @param {jQuery} input Element
	 * @returns {boolean} If currency is unique
	 */
	function checkAccountCurrency( input ) {
		var
			count = 0,
			exCur = [];

		$( ".account-currency" ).each( function e() {
			exCur.push( $( this ).val() );
		} );

		// Block add account button if account count equal to store currencies
		if( exCur.length === ADK.locale.currency.length ) {
			$( "#add-account" ).attr( "disabled", "disabled" );

		} else {
			$( "#add-account" ).removeAttr( "disabled" );
		}

		if( typeof input !== "undefined" ) {
			$( ".account-currency" ).each( function e() {
				if( this.value === input.val() ) {
					count++;
				}
			} );

			return count <= 1;
		}

		return true;
	}

	/**
	 * Set spare currency code to newly created account field
	 * @returns {void}
	 */
	function setCurrency() {
		$( ".account-currency" ).each( function ee() {
			var
				count = 0,
				cur = null,
				e = [],
				select = this,
				val = this.value;

			$( ".account-currency" ).each( function each() {
				if( select === this ) {
					return false;
				}

				if( val === this.value ) {
					count++;
				}

				return false;
			} );

			if( count ) {
				$( ".account-currency" ).each( function each() {
					e.push( this.value );
				} );

				$.each( currencies, function each() {
					if ( -1 === $.inArray( this, e ) ) {
						cur = this;

						return false;
					}

					return false;
				} );

				$( this ).val( cur );
			}
		} );
	}

	/**
	 * Check TLS 1.2 functionality
	 * @returns {void}
	 */
	function checkTls() {
		var $this = $( this );

		$this.btnActive();

		$.post( ADK.locale.checkTlsUrl.replace( /&amp;/, "&" ) )

		.always( function checkTlsAlways() {
			$this.btnReset();
		} )

		.fail( function checkTksFail() {
			ADK.alert( adkLocale.networkError );
		} )

		.done( function checkTlsDone( respStr ) {
			var resp = null;

			// If response is empty or doesn't contain JSON string
			if ( respStr ) {
				resp = ADK.sanitizeAjaxResponse( respStr );

				if ( null === resp ) {
					ADK.alert( ADK.locale.parseError );

					return;
				}

				// Task have been saved
				if ( resp.success ) {
					ADK.n.notification( resp.success );
					$this
						.removeClass( "btn-default btn-danger" )
						.addClass( "btn-success" );

				// We got error
				} else if ( resp.error ) {
					ADK.alert( resp.error );
					$this
						.removeClass( "btn-default btn-success" )
						.addClass( "btn-danger" );

				// Something went wrong
				} else {
					ADK.alert( ADK.locale.undefServerResp );
				}

			} else {
				console.error( resp );
				ADK.alert( ADK.locale.networkError );
			}
		} );
	}

	/**
	 * Initialize summer-note element
	 * @returns {void}
	 */
	function summernote() {
		var
			$this = $( this ),
			height = $this.attr( "data-height" ) ? $this.attr( "data-height" ) : 200,
			placeholder = $this.attr( "placeholder" ) ? $this.attr( "placeholder" ) : "",
			toolbar = [
			    [ "style", [ "bold", "italic", "underline", "clear" ] ],
			    [ "font", [ "strikethrough", "superscript", "subscript" ] ],
			    [ "fontsize", [ "fontsize" ] ],
			    [ "color", [ "color" ] ],
			    [ "para", [ "ul", "ol", "paragraph" ] ],
			    [ "height", [ "height" ] ]
			 ];

		$this.summernote( {
			height:      height,
			toolbar:     toolbar,
			placeholder: placeholder,
			hint:        {
				words:  ADK.locale.variables.split( "," ),
				match:  /(.{1,})/,
				search: function s( keyword, callback ) {
					callback( $.grep( this.words, function cb( item ) {
						return item.indexOf( keyword ) === 0;
					} ) );
				}
			},
			callbacks: {
				onChange: function ch( contents ) {
					var text = contents;

					if( $this.hasClass( "oneline" ) ) {
						text = text.replace( /(<br>|<\/p>)/g, " " ).replace( /<[^>]*>/g, "" );
					}

					$this.val( text );
				}
			}
		} );

		if( this.value ) {
			$this.summernote( "code", this.value );
		}
	}

	/**
	 * Paginates content
	 * @param {object} evt Event instance
	 * @returns {void}
	 */
	function paginate( evt ) {
		var pane = $( this ).parents( ".tab-pane" );

		evt.preventDefault();
		pane.find( ".pagination" ).addClass( "action" );

		$.get( evt.target.getAttribute( "href" ) )

		.always( function a() {
			pane.find( ".pagination" ).removeClass( "action" );
		} )

		.fail( function f() {
			ADK.alert( ADK.locale.plansError );
		} )

		.done( function d( resp ) {
			if( resp.substr( -7 ) === "success" ) {
				pane.children().not( ".static" )
					.remove();

				pane.append( $( resp.slice( 0, -7 ) ) );
				pane.triggerHandler( "paginated" );

			} else {
				ADK.alert( ADK.locale.plansError );
			}
		} );
	}

	/**
	 * Auto-complete callback
	 * @param {object} evt Event instance
	 * @returns {void}
	 */
	function autocomplete( evt ) {
		var
			parent = null,
			target = $( evt.target );

		parent = target.parent();

		// If "action" is already in action
		if( parent.hasClass( "busy" ) ) {
			return;
		}

		// Time out
		if( autocompleteTimeout ) {
			return;
		}

		autocompleteTimeout = setTimeout( function t() {
			autocompleteTimeout = null;
		}, 1000 );

		target.trigger( "autocomplete.start" );
		parent.removeClass( "has-error" ).addClass( "busy" );

		// Perform request
		$.post( evt.target.getAttribute( "data-autocomplete-url" ).replace( /&amp;/, "&" ),
			{
				query: evt.target.value,
				data:  target.attr( "data-autocomplete" )
			}, null, "json" )

		.always( function a() {
			parent.removeClass( "busy" );
		} )

		.fail( function func() {
			parent.addClass( "has-error" );
			parent.find( ".auto-list" ).fadeOut( 500, function cl() {
				this.remove();
			} );
		} )

		.done( function d( resp ) {
			var list = parent.find( ".auto-list" );

			list.empty();

			// If empty response
			if( !resp.length ) {
				parent.find( ".auto-list" ).fadeOut( 500, function cl() {
					this.remove();
				} );

				return;
			}

			// If response is a single value which is already inputed
			if( 1 === resp.length && resp[ 0 ].text === target.val() ) {
				parent.find( ".auto-list" ).fadeOut( 500, function cl() {
					this.remove();
				} );

				target.trigger( "autocomplete.done" );

				return;
			}

			// Create new auto-complete list, if not present
			if( !list.length ) {
				list = $( "<div class=\"auto-list\"></div>" );
				parent.append( list );
				list.fadeIn();
				list.delegate( ".auto-list-item", "click", function cl() {
					target.val( this.textContent );
					target.attr( "data-value", $( this ).attr( "data-value" ) );
					list.fadeOut( 500, function closure() {
						this.remove();
					} );

					target.trigger( "autocomplete.done" );
				} );
			}

			// Fill in each list item in return
			$.each( resp, function cl() {
				list.append( "<div class=\"auto-list-item\" data-value=\"" + this.value + "\">" +
					this.text + "</div>" );
			} );
		} );
	}

	/**
	 * Makes some action on body click
	 * @param {object} evt Event instance
	 * @returns {void}
	 */
	function makeSomeStuffOnBodyClick( evt ) {
		var
			exclude = null,
			lists = $( ".auto-list" ),
			target = $( evt.target );

		// Hide all the auto-fill drop-downs but active
		if( lists.length ) {
			exclude = $( evt.target ).parents( ".auto-list" );

			if( evt.target.getAttribute( "data-autocomplete-url" ) ) {
				$.extend(
					exclude,
					$( evt.target ).parent()
						.find( ".auto-list" )
				);
			}

			lists.not( exclude ).fadeOut( 500, function closure() {
				this.remove();
			} );
		}

		// Remove error message on click on element
		if(
			(
				target.hasClass( "oc-exists" ) ||
				target.hasClass( "descriptor" ) ||
				target.hasClass( "sp-plan" )
			) && target.parent().hasClass( "has-error" )
		) {
			target.parent().removeClass( "has-error" );
		}
	}

	/**
	 * Auto-complete done callback handler
	 * @param {object} evt Event instance
	 * @returns {void}
	 */
	function autocompleteDone( evt ) {
		$( evt.target ).parent()
			.find( "span.main" )
			.addClass( "active" );

		$( evt.target ).parent()
			.find( "span.main" )
			.click();
	}

	/**
	 * Auto-complete start action callback handler
	 * @param {object} evt Event instance
	 * @returns {void}
	 */
	function autocompleteStart( evt ) {
		$( evt.target ).parent()
			.find( "span.main" )
			.removeClass( "active" );
	}

	/**
	 * Auto-complete error handler
	 * @param {object} evt Event instance
	 * @returns {void}
	 */
	function autocompleteError( evt ) {
		$( evt.target ).parent()
			.find( "span.main" )
			.removeClass( "active" );
	}

	/**
	 * Table content modification callback handler
	 * @returns {void}
	 */
	function onModifiy() {

		// If one or more cells was removed - remove entire row
		if( $( this ).children( "td" ).length < 3 ) {
			$( this ).remove();
		}
	}

	/**
	 * Perform main action on specific input element
	 * @param {object} evt Event instance
	 * @returns {void}
	 */
	function doIt( evt ) {
		var
			data = {},
			input = null,

			// In case if event happens on child element
			parent = null,
			target = $( evt.target ).hasClass( "main" ) ? $( evt.target ) :
				$( evt.target ).parents( ".main" );

		parent = target.parent();

		if( !target.hasClass( "active" ) || parent.hasClass( "busy" ) ) {
			return;
		}

		if( target.hasClass( "delete" ) ) {
			if( !confirm( ADK.locale.deletePlanSure ) ) {
				return;
			}
		}

		input = target.parent().find( "input" );

		// Perform concrete action
		if( target.hasClass( "map" ) ) {
			data = {
				"oc_id": input.attr( "data-value" ),
				"plan":  target.parents( "tr" ).attr( "data-sp-plan" )
			};

		} else if ( target.hasClass( "unmap" ) ) {
			data = {
				"oc_id": input.attr( "data-plan-id" ),
				"plan":  target.parents( "tr" ).attr( "data-sp-plan" )
			};

		} else if ( target.hasClass( "descriptor" ) ) {
			data = {
				"statement":  input.val(),
				"sp-plan-id": target.parents( "tr" ).find( ".sp-plan" )
					.attr( "data-plan-id" )
			};

		} else if( target.hasClass( "rename" ) ) {
			data = {
				"name":       input.val(),
				"sp-plan-id": input.attr( "data-plan-id" )
			};

		} else if( target.hasClass( "delete" ) ) {
			data = {
				"sp-plan-id": input.attr( "data-plan-id" )
			};

		} else if( target.hasClass( "export" ) ) {
			data = {
				"plan": target.parents( "tr" ).attr( "data-sp-plan" )
			};
		}

		parent.addClass( "busy" ).removeClass( "has-error" );

		$.post( target.attr( "data-action" ).replace( /&amp;/g, "&" ), data )

		.always( function a() {
			parent.removeClass( "busy" );
		} )

		.fail( function f_closure() {
			input.trigger( "action.error" );
			parent.addClass( "has-error" );
		} )

		.done( function d( resp ) {
			var tr = null;

			if( resp.substr( -7 ) === "success" ) {
				tr = target.parents( "tr" );
				target.parents( "td" )[ 0 ].outerHTML = resp.slice( 0, -7 );
				tr.trigger( "content.changed" );

			} else {
				if( resp.substr( -5 ) === "error" ) {
					target.removeClass( "active" );
					ADK.alert( resp.slice( 0, -5 ) );
				}

				parent.addClass( "has-error" );
			}
		} );
	}

	/**
	 * Edit profile callback
	 * @returns {void}
	 */
	function editProfile() {
		var
			$button = $( this ),
			$panel = $button.parents( ".property" ),
			prop = {};

		$button.btnActive();

		$panel
			.find( "[data-property]" )
			.each( function e() {
				var param = [];

				$( this ).find( "input, select" )
				.each( function each() {
					var $this = $( this );

					// Collect all the check-boxes
					if(
						"checkbox" === this.type.toLowerCase() &&
						!( $this.is( ":checked" ) && $this.is( ":visible" ) )
					) {
						return;
					}

					param.push( this.value );
				} );

				prop[ this.getAttribute( "data-property" ) ] = param.join( "," );
				prop.id = $( this ).parents( ".property" )
					.attr( "data-profile-id" );
			} );

		$.post( $button.attr( "data-url" ).replace( /&amp;/, "&" ), { properties: prop } )

		.always( function a() {
			$button.btnReset();
		} )

		.fail( function fail() {
			ADK.alert( ADK.locale.networkError );
		} )

		.done( function done( respStr ) {
			var resp = null;

			resp = ADK.sanitizeAjaxResponse( respStr );

			if( resp.error ) {
				ADK.alert( resp.error );

				return;

			} else if( resp.success ) {
				ADK.n.notification( resp.success );

				if( resp.id ) {
					$panel.attr( "data-profile-id", resp.id );
					ADK.e.trigger( "profile." + resp.id + ".updated", $panel );
				}

			} else {
				ADK.n.alert( ADK.locale.serverError );
			}
		} );
	}

	/**
	 * Add profile template callback
	 * @returns {void}
	 */
	function addProfileTemplate() {
		$( ADK.locale.profileTemplate )
		.insertAfter(
			$( this ).parents( ".plans-container" )
				.find( ".property" )
				.last()
		)
		.find( ".checkbox-group.totals-to-recurring input" )
		.each( function e() {
			whatToShow( this );
		} )
		.end()
		.find( ".fancy-checkbox" )
		.fancyCheckbox();

		isRemoveProfileButtonActive();
	}

	/**
	 * Remove profile record callback
	 * @returns {void}
	 */
	function removeProfile() {
		var
			$button = $( this ),
			profileId = $button.parents( ".property" )
				.attr( "data-profile-id" );

		$button.btnActive();

		$.post( $button.attr( "data-url" ).replace( /&amp;/, "&" ), {
			profile_id: profileId
		} )

		.always( function a() {
			$button.btnReset();
		} )

		.fail( function fail() {
			ADK.alert( ADK.locale.networkError );
		} )

		.done( function done( respStr ) {
			var resp = null;

			resp = ADK.sanitizeAjaxResponse( respStr );

			if ( null === resp ) {
				return;
			}

			if( resp.error ) {
				ADK.n.alert( resp.error );

				return;

			} else if ( resp.success ) {
				$button.parents( ".property" )
					.remove();

				isRemoveProfileButtonActive();

				if(
					!$( ".plans-container .property" ).length &&
					$( ".plans-container" ).parents( ".tab-pane" )
						.find( ".pagination .active span" )
						.text() > 1
				) {
					paginateProfiles(
						$( ".plans-container" )
							.parents( ".tab-pane" )
							.find( ".pagination .active span" )
							.text() - 1
					);
				}

				ADK.e.trigger( "profile." + profileId + ".deleted", profileId );
				ADK.n.notification( resp.success );

			} else {
				ADK.alert( ADK.locale.serverError );
			}
		} );
	}

	/**
	 * Select mapping callback
	 * @returns {void}
	 */
	function selectMapping() {
		var
			select = this;

		$( select ).attr( "disabled", "disabled" );

		$.get( ADK.locale.profileMapMapUrl.replace( /&amp;/g, "&" ), {
			profile_id:   $( select ).val(),
			recurring_id: $( select ).parents( "tr" )
				.attr( "data-oc-plan-id" )
		} )

		.always( function a() {
			$( select ).removeAttr( "disabled" );
		} )

		.fail( function fail() {
			ADK.n.alert( ADK.locale.serverError );
		} )

		.done( function done( respStr ) {
			var resp = null;

			resp = ADK.sanitizeAjaxResponse( respStr );

			if( !resp ) {
				return;
			}

			if( resp.error ) {
				ADK.n.alert( resp.error );

				return;

			} else if( resp.success ) {
				ADK.n.notification( resp.success );

			} else {
				ADK.n.alert( ADK.locale.serverError );
			}

		} );
	}

	/**
	 * Change account name callback
	 * @returns {void}
	 */
	function changeAccountName() {
		var
			error = false,
			name = $( this ).val()
				.replace( /[^0-9A-Za-z_]/g, "" )
				.toLowerCase(),
			rg = new RegExp( "\\[" + name + "\\]" );

		$( this ).parents( "#tab-api" )
			.find( ".account-wrapper" )
			.not( $( this ).parents( ".account-wrapper" ) )
			.find( "[name]" )
			.each( function e() {
				if ( rg.test( $( this ).attr( "name" ) ) ) {
					ADK.alert( ADK.locale.errorNameExists );
					error = true;

					return false;
				}

				return null;
			} );

		if( error ) {
			return;
		}

		$( this ).parents( ".account-wrapper" )
			.find( "[name]" )
			.each( function e() {
				var
					currentName = null,
					me = $( this ),
					reg = new RegExp( replacment ),
					replacment = typeof me[ 0 ].oldName === "undefined" ? "temp" : me[ 0 ].oldName;

				currentName = me.attr( "name" );

				me.attr( "name", currentName.replace( reg, name ) );
				me[ 0 ].oldName = name;
			} );
	}

	/**
	 * Adds Stripe account
	 * @returns {void}
	 */
	function addAccount() {
		var
			$templ = $(
				ADK.locale.accountDetailsTemplate
				.replace( /{template_name}/g, Math.round( Math.random() * 1000000000 ) )
			);

		$templ.find( "[data-name]" ).each( function r() {
			$( this ).attr( "name", $( this ).attr( "data-name" ) );
		} );

		$( this ).parents( "#tab-api" )
			.find( ".account-wrapper:last-child" )
				.after( $templ );

		checkAccountCurrency();
		setCurrency();
	}

	/**
	 * Change currency callback
	 * @returns {void}
	 */
	function changeCurrency() {

		// Currencies duplicate
		if( !checkAccountCurrency( $( this ) ) ) {

			// Set previous value
			$( this ).val( this.prevVal );
			ADK.alert( ADK.locale.errorAcountSameCurrency );
		}
	}

	/**
	 * Initialize select2 element
	 * @returns {void}
	 */
	function initSelect2() {
		var select2Data = null;

		if( this.id === "avail_systems" ) {
			select2Data = $.extend( { width: "100%" }, {
				templateResult: function t( systems ) {
					if ( typeof systems.id === "undefined" ) {
						return systems.text;
					}

					if ( 0 == systems.id ) {
						return $( "<span style='padding: 6px 0;'>" + systems.text + "</span>" );
					}

					return $(
					"<span>" +
						"<span style=\"display:inline-block;width:60px\">" +
							"<img src=\"" + ADK.locale.imgStripeUrl +
							systems.element.value.toLowerCase() + ".svg\" style=\"height:30px\">" +
						"</span>" +
						systems.text +
					"</span>"
					);
				},
				templateSelection: function t( systems ) {
					if ( typeof systems.id === "undefined" ) {
						return systems.text;
					}

					if ( 0 == systems.id ) {
						return $( "<span style='padding: 6px 0;'>" + systems.text + "</span>" );
					}

					return $(
					"<span>" +
						"<img src=\"" + ADK.locale.imgStripeUrl +
							systems.element.value.toLowerCase() + ".svg\" style=\"height:30px\">" +
					"</span>"
					);
				}
			} );

			$( this ).select2( select2Data );

		} else {
			$( this ).select2( { width: "100%" } );
		}
	}

	/**
	 * Deletes Stripe account
	 * @returns {void}
	 */
	function deleteAccount() {
		$( this ).parents( ".account-wrapper" )
			.remove();
		checkAccountCurrency();
	}

	/**
	 * Run profile tab initialization
	 * @return {void}
	 */
	function initProfileTab() {
		$( this )
			.find( ".property .checkbox-group.totals-to-recurring input" )
			.each( function e() {
				whatToShow( this );
			} )
		.end()
			.find( ".fancy-checkbox" )
			.fancyCheckbox();
	}

	/**
	 * Loads ticket button
	 * @returns {void}
	 */
	function renderTicketButton() {
		$( "#ticket-wrapper" ).load( ADK.locale.ticketButtonUrl.replace( /&amp;/g, "&" ) );
	}

} )( jQuery );
