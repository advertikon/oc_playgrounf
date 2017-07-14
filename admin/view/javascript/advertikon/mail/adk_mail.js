( function closure( $ ) {
	"use_strict";

	var
		extensionStatus = null;

	$( document ).ready( function documentOnReady() {

		extensionStatus = !ADK.isEmpty( $( "#status" ).val() );

		ADK.locale.showTips = !ADK.isEmpty( ADK.locale.showTips );

		if( ADK.locale.showTips ) {
			$( ".tip" ).css( "display", "block" );
		}

		ADK.activeTemplateTabs = [];
		ADK.saveTemplateAction = false;
		ADK.saveTemplateTimeout = null;
		ADK.templateConfigurations = [];
		ADK.saveTemplateConfigurationTimeout = null;

		document.getElementById( "preview" ).onload = previewLoaded;

		$( "#preview-reload" ).on( "click", reloadPreview );
		$( "#preview-template" ).on( "change", reloadPreview );

		// Load profile controls
		fetchProfileCOntrols();

		// Remove click ability from Iris color handler
		$( document ).delegate( "a.iris-square-value", "click", function irisHandlerClick() {
			return false;
		} );

		// Listen to measure units of template to change
		ADK.e.subscribe( "switchable.measure_units.switch.end", rangeChangeState );

		// Subscribe to profile controls fetched event
		ADK.e.subscribe( "profile.controls.fetch.end", initProfileControls );

		// Hide Iris color-picker functionality
		// If element is present in irisHideException list - do not hide color-picker
		$( document ).on( "click", documentClick );

		// Initialize preview control panel;s switch-ables
		$( ".preview-wrapper .switchable" ).each( function iterateOverSwitchables() {
			ADK.initSwitchable( this );
		} );

		// Fetch new profile
		$( "#profiles" ).on( "change", fetchProfileCOntrols );

		// Start to watch for image control to change its value
		watchElementChange();

		// Check whether there are unsaved data and prompt the user
		$( window ).on( "beforeunload", onUnload );

		// Select preview window size and orientation
		$( "#laptop, #tablet, #mobile, #rotate-view" ).on( "click", viewResize );

		// -----------------------------Contents manager tab ---------------------------------------

		// Show shortcode's tab
		$( document ).delegate( ".show-shortcodes-tab", "click", function showShortcodes( e ) {
			e.preventDefault();
			$( "a[href=#shortcodes-list]" ).trigger( "click" );
			window.scroll( 0, 0 );
		} );

		// Add store event listener
		$( document ).delegate( ".store-tab-headers .dropdown-menu a", "click", addStore );

		// Add language event listener
		$( document ).delegate( ".lang-tab-headers .dropdown-menu a", "click", addLang );

		// Delete store event listener
		$( document ).delegate( ".delete-store", "click", deleteStore );

		// Delete store event listener
		$( document ).delegate( ".delete-lang", "click", deleteLang );

		// Template undo/redo event listener
		$( document ).delegate( "#template-undo, #template-redo", "click", undoRedoTemplate );

		// Template undo/redo event listener
		$( document ).delegate( "#template-save", "click", saveTemplate );

		// Template undo/redo event listener
		$( document ).delegate( ".available-profile", "change", saveContent );

		// Change template event handler
		$( "#templates" ).on( "change", function changeTemplate() {
			$( "#preview-template" ).val( $( this ).val() );
			fetchTemplateControls();
			reloadPreview();
		} );

		$( "#send-email" ).on( "click", sendEmail );

		// Initialize new content of contents manager tab
		ADK.e.subscribe( "template.controls.fetch.start", beforeControlsFetch );

		// Initialize new content of contents manager tab
		ADK.e.subscribe( "template.controls.fetch.end", initContentManagerTab );

		// Subscribe to add snapshot event
		ADK.e.subscribe( "template.snapshot.add", onTemplateAddSnapshot );

		// Subscribe to remove snapshots event
		ADK.e.subscribe( "template.snapshot.clear", onTemplateClearSnapshots );

		// Initial fetch
		fetchTemplateControls();

		// -------------------------Shortcode manager tab-------------------------------------------

		initShortcodeTab();
		initVitrineTab();
		initSocialTab();
		initButtonTab();
		initQrcodeTab();
		initInvoiceTab();

		// Save shortcode event listener
		$( document ).delegate( ".save-shortcode", "click", saveShorcode );

		// Select shortcode event listener
		$( document ).delegate( ".select-shortcode", "change", fatchShorcode );

		// Delete shortcode event listener
		$( document ).delegate( ".delete-shortcode", "click", deleteShorcode );

		// Subscribe to fetch vitrine shortcode tab contents
		ADK.e.subscribe( "shortcode.vitrine.fetch.end", initVitrineTab );

		// Subscribe to fetch social shortcode tab contents
		ADK.e.subscribe( "shortcode.social.fetch.end", initSocialTab );

		// Subscribe to fetch button shortcode tab contents
		ADK.e.subscribe( "shortcode.button.fetch.end", initButtonTab );

		// Subscribe to fetch qr-code shortcode tab contents
		ADK.e.subscribe( "shortcode.qrcode.fetch.end", initQrcodeTab );

		// Subscribe to fetch invoice shortcode tab contents
		ADK.e.subscribe( "shortcode.invoice.fetch.end", initInvoiceTab );

		// Subscribe to all shortcode tabs fetch
		ADK.e.subscribe( "shortcode.*.fetch.end", initShortcodeTab );

		// Toggle social input
		ADK.e.subscribe( "switchable.switch.end", toggleSocial );

		// --------------------------------Profile mapping tab--------------------------------------

		// Set mapping select change event
		$( document ).delegate( ".template-configuration", "change", collectProfileConfiguration );

		// Make context based shortcodes filter
		$( "#filter-context" ).on( "change", contextFilter );

		// ---------------------------------Settings tab--------------------------------------------

		// Extension status setting
		$( "#status" ).on( "change", function statusChange() {
			extensionStatus = $( this ).val();

			setSetting( {
				name:  "status",
				value: extensionStatus
			},
			$( this ),
			showExensionDisableAlert
			);

		} );

		// Hints status setting
		$( "#hints" ).on( "change", function hintsChange() {
			var status = $( this ).val();

			setSetting(	{
				name:  "hint",
				value: status
			},
			$( this ),
			showExtensionHints
			);

			ADK.locale.showTips = status;

		} );

		// Extended newsletter
		$( "#extended_newsletter" ).on( "change", function extendedNewsletterChange() {
			setSetting(	{
				name:  this.id,
				value: $( this ).val()
			},
			$( this )
			);
		} );

		// Add custom template
		$( "#new-template-add" ).on( "click", addNewTemplateFunc );

		// Add custom template for newsletter status confirmations
		$( document ).delegate( ".add-newsletter-template", "click", addNTemplate );

		// Delete custom template
		$( document ).delegate( "#template-delete", "click", deleteTemplate );

		showExensionDisableAlert();

		// Change align/valign select's input group addon
		$( document ).delegate( "[id$=align]", "change", alignSelectChange );

		// Set new color scheme in response to main color select
		$( document ).delegate( "#invoice-color-scheme a", "click", pickScheme );

		// Set new color scheme in response to color scheme change
		$( document ).delegate( "#invoice-color-scheme input", "change", function pickColor() {
			var container = null;

			container = $( this ).parents( ".color-scheme-picker" );
			setInvoiceColorScheme.call( container );
		} );

		// Clear archive button click handler
		$( "#archive-clean" ).on( "click", function ca() {
			ADK.confirm( ADK.locale.cleanArchive.replace( /%s/, $( "#archive-days-value" ).val() ) )
				.yes( cleanArchive );
		} );

		// Switch sort order direction for sortable table
		$( document ).delegate( ".table-sort", "click", historySort );

		// Refresh history list on successful email sending
		ADK.e.subscribe(
			"email.send.end",
			fetchTableContents.bind( $( ".history-table" ).parents( "[data-url]" )[ 0 ] )
		);

		// Paginate over table contents
		$( ".tab-pane" ).delegate( ".pagination a", "click", paginate );

		// Show email log in pop-up widow
		$( "#history-content" ).delegate( ".history-log", "click", showLog );

		// Filter history by fields
		$( document ).delegate( ".apply-table-filter", "click", function filterHistory() {
			var
				container = $( this )
					.parents( ".table-overall" )
					.find( "table" )
					.parents( "[data-url]" )[ 0 ];

			fetchTableContents.call( container, { page: 1 } );
		} );

		// Reset history filtering
		$( document ).delegate( ".clear-table-filter", "click", resetFilter );

		$( "#history-pane .select2" ).select2( { width: "100%" } );

		filter_autofill_init( $( ".table-filter-autofill" ) );

		// Set custom date period to filter
		$( document ).delegate( ".table-filter-date", "change", filterDate );

		// Clear history click button handler
		$( "#clear-history" ).on( "click", cleanHistory );

		// Initialize missed order status templates list
		$( "#missed-order-templates-list, #missed-return-templates-list" ).select2( {
			width: "100%"
		} );

		// Add order status template
		$( "#add-status-template" ).on( "click", addStatusOrder );

		// Add order status template
		$( "#add-r-status-template" ).on( "click", addStatusReturn );

		fetchTableContents.call( $( "#newsletter-list" )[ 0 ] );

		// Add new newsletter
		$( "#add-newsletter" ).on( "click", addNewsletter );

		// Re-render newsletters list on new newsletter creation
		ADK.e.subscribe(
			"newsletter.add.end",
			function listner( newsletter ) {
				fetchTableContents.call( $( "#newsletter-list")[ 0 ] );
				$( "#select-newsletter" )
				.append(
					$( "<option value='" + newsletter.id + "'>" + newsletter.name + "</option>" )
				);
			}
		);

		ADK.e.subscribe( "newsletter.update.end", function callback() {
			fetchTableContents.call( $( "#newsletter-list")[ 0 ] );
		} );

		// Update newsletters' list after newsletter been deleted
		ADK.e.subscribe(
			"newsletter.delete.end",
			function callback( ids ) {
				fetchTableContents.call( $( "#newsletter-list" )[ 0 ] );

				$( "#select-newsletter option" ).each( function eachOption() {
					if ( $.inArray( $( this ).attr( "value" ), ids ) ) {
						$( this ).remove();
					}
				} );
			}
		);

		// Delete newsletter button click handler
		$( "#delete-newsletter" ).on( "click", function del() {
			ADK.confirm( ADK.locale.sureDeleteNewsletter ).yes( deleteNewsletter.bind( this ) );
		} );

		// Select newsletter to manage to
		$( "#select-newsletter" ).on( "change", fetchNewsletterControls );

		$( document).delegate( "#update-newsletter", "click", updateNews );

		// Add subscriber button click handler
		$( document ).delegate( "#add-subscriber", "click", addSubscriberClick );

		// Re-render subscribers list
		ADK.e.subscribe( "subscriber.add.end", function callback() {
			fetchTableContents.call( $( "#subscribers-list-contents" ) );
		} );

		// Initialize newsletter list's filter's status select
		$( ".newsletter-status-select" ).select2( {
			width: "100%"
		} );

		// Refresh list of subscribers
		$( document ).delegate( "#refresh-history", "click", function refreshSubsc() {
			fetchTableContents.call( $( "#history-content" )[ 0 ] );
		} );

		// Refresh list of subscribers
		$( document ).delegate( "#refresh-subscribers", "click", function refreshSubsc() {
			fetchTableContents.call( $( "#subscribers-list-contents" )[ 0 ] );
		} );

		// Update newsletters' list after newsletter been deleted
		ADK.e.subscribe(
			"subscriber.delete.end",
			function callback() {
				fetchTableContents.call( $( "#subscribers-list-contents" )[ 0 ] );
			}
		);

		// Delete newsletter button click handler
		$( document ).delegate( "#delete-subscriber", "click", function del() {
			ADK.confirm( ADK.locale.sureDeleteSubscriber ).yes( deleteSubscriber.bind( this ) );
		} );

		// Initialize subscription widget Iris controls
		$( ".widget-controls .iris" ).each( function eachIris() {
			ADK.initIris( this );
		} );

		// Subscription widget controls' change event handlers
		$( document ).delegate( ".widget-controls input", "change input", buildWidget );
		$( document ).delegate( ".widget-controls button", "click", buildWidget );

		// Initialize subscribe widget controls with delay
		setTimeout( function delayToInit() {
			$( "#form-wrapper" )
				.fix( { parent: $( "#form-wrapper" ).parents( ".fix-parent" )
					.eq( 0 )[ 0 ] } );

			$( ".widget-controls input" ).trigger( "change" );
		}, 2000 );

		// Set new color scheme in response to main color select
		$( document ).delegate( "#widget-color-scheme a", "click", pickSchemeWidget );

		// Set new color scheme in response to color scheme change
		$( document ).delegate( "#widget-color-scheme input", "change", function pickColor() {
			setWidgetColorScheme.call( $( this ).parents( ".color-scheme-picker" ) );
		} );

		// Initialize subscription widget color scheme
		$( "#widget-color-scheme input" ).val( "#d26464" );
		$( "#widget-color-scheme a:nth(1)" ).trigger( "click" );

		// Save widget click handler
		$( "#widget-save" ).on( "click", saveWidget );

		// Listen to widget be saved
		ADK.e.subscribe( "widget.save.end", widgetOnSave );

		// Select subscription widget handler
		$( "#widget-select" ).on( "change", fetchWidget );

		// Listen to widget data fetch
		ADK.e.subscribe( "widget.fetch.end", widgetOnFetch );

		// Delete subscription widget handler
		$( "#widget-delete" ).on( "click", function auSure() {
			ADK.confirm( ADK.locale.sureDeleteWidget ).yes( deleteWidget.bind( this ) );
		} );

		// Listen to widget delete event
		ADK.e.subscribe( "widget.delete.end", widgetOnDelete );

		$( "#save-captions" ).on( "click", saveCaptions );

		// Import subscribers click handler
		$( document ).delegate( "#import-subscribers", "click", inportSubscribers );

		$( document ).delegate( "#export-subscribers-filter", "change", filterSubscribers );

		// Listen to throttle status change
		ADK.e.subscribe( "switchable.click.throttle-item-on", changeBtnColor );
		ADK.e.subscribe( "switchable.click.throttle-traffic-on", changeBtnColor );

		// Enable/disable Queue option
		$( "#setting-queue" ).on( "change", function setQueue() {
			setSetting( {
				name:  "queue",
				value: $( this ).val()
			}, $( this ) );
		} );

		// Sett throttle setting
		$( "#throttle-item, #throttle-traffic" ).on( "click", setThrottle );

		// Simple setting save click handler
		$( document ).delegate( ".simple-setting", "click change", setSimpleSetting );

		$( document ).delegate( "#newsletter-chart-reload", "click", newsletterChart );

		ADK.e.subscribe( "chart.fetch.end", initSubscribersChart );

		// Initialize newsletter controls tab
		ADK.e.subscribe(
			"newsletter-controls.fetch.end", [ initNewsletterContrlsTab, fetchChartData ]
		);

		// Set new color scheme in response to main color select
		$( document ).delegate( "#profile-color-scheme a", "click", pickSchemeProfile );

		// Set new color scheme in response to color scheme change
		$( document ).delegate( "#profile-color-scheme input", "change", function pickColor() {
			setProfileColorScheme.call( $( this ).parents( ".color-scheme-picker" ) );
		} );

		// Select newsletter on click on it at newsletter list table
		$( document ).delegate( ".newsletter-line", "click", chooseNewsletter );

		// Enable/disable CSV fields to be exported
		$( document ).delegate( ".csv-item input", "change", filterSubscribers );

		$( document ).delegate( "#history-select-all", "change", function ch() {
			if ( $( this ).is( ":checked" ) ) {
				$( ".history-table input[type=checkbox]:not(#history-select-all)" )
					.prop( "checked", "checked" );

			} else {
				$( ".history-table input[type=checkbox]:not(#history-select-all)" )
					.removeAttr( "checked" );
			}
		} );

		// Run queue listener
		$( "#run-queue" ).on( "click", runQueue );

		// Flush queue listener
		$( "#flush-queue" ).on( "click", flushQueue );

		// Flash queue callback
		ADK.e.subscribe( "queue.flush", onQueueFlush );

		ADK.e.subscribe( [
			"template.controls.fetch.end",
			"profile.tmp.save",
			"template.snapshot.add",
			"shortcode.*.save.end",
			"switchable.click.preview-images",
			"profile.controls.fetch.end"
		], function cb() {
			if ( !ADK.isEmpty( ADK.locale.autoUpdate ) ) {
				reloadPreview();
			}
		} );

		// Listen to setting change event
		ADK.e.subscribe( "setting.*.changed", onSettingChange );

		// Manage subscription listener
		$( document ).delegate( ".manage-subscription", "click", manageSubscription );

		// Update subscribers list event listener
		ADK.e.subscribe( "subscriber.change", updateSubscribersList );

		// Save IMAP configurations event listener
		$( "#save-imap" ).on( "click", saveImap );

		// Check IMAP configurations event listener
		$( "#check-imap" ).on( "click", checkImap );

		// Hide/show IMAP password
		$( "#show-imap-password" ).on( "click", showImapPassword );

		// Run blacklisting action manually
		$( "#do-blacklist" ).on( "click", doBlacklist );

		// Pre-save captions
		if ( ADK.locale.saveCaption ) {
			saveCaptions.call( $( "#save-captions" ) );
		}

		// Hide/show table fields handler
		$( document ).delegate( ".table-show-field", "click", hideTableColumn );
	} );

	/**
	 * Deletes profile
	 * @returns {void}
	 */
	function deleteProfile() {
		var
			$button = $( "#profile-delete" ),
			id = $( "#profiles" ).val();

		$( "#profile-controls .wait-screen" ).addClass( "shown" );

		$.post(
			$button.attr( "data-url" ).replace( /&amp;/g, "&" ), { id: id } )

		.always( function deleteProfileAlways() {
			$( "#profile-controls .wait-screen" ).removeClass( "shown" );
		} )

		.fail( function deleteProfileFail() {
			ADK.alert( ADK.locale.networkError );
		} )

		.done( function deleteProfileDone( respStr ) {
			var resp = null;

			// If response is empty or doesn't contain JSON string
			if ( respStr ) {
				resp = ADK.checkResponse( respStr );

				if ( null === resp ) {
					return;
				}

				// Task have been saved
				if ( resp.success ) {
					ADK.n.notification( ADK.locale.profileDeleted );
					$( "#profiles option[value=" + id + "]" ).remove();
					$( "#profiles" ).trigger( "change" );

				// We got error
				} else if ( resp.error ) {
					ADK.alert( resp.error );

				// Something went wrong
				} else {
					ADK.aert( ADK.locale.undefServerResp );
				}

			} else {
				console.error( respStr );
				ADK.alert( ADK.locale.networkError );
			}
		} );
	}

	/**
	 * Creates needed object structure and populates it with value
	 * @param {object} object Initial object
	 * @param {string} pathIn Value name. Expects as "name" as well as "name[subname]"
	 * @param {mixed} value Value to set
	 * @returns {void}
	 */
	function setValue( object, pathIn, value ) {
		var
			part = null,
			path = null;

		path = pathIn.replace( /]/g, "" ).split( "[" );

		while( typeof ( part = path.shift() ) !== "undefined" ) {
			if( typeof object[ part ] === "undefined" ) {

				// Value (leaf)
				if( 0 === path.length ) {
					object[ part ] = value;
					break;

				// Node
				} else {
					object[ part ] = {};
				}
			}

			object = object[ part ];
		}
	}

	/**
	 * Saves changes to temp storage
	 * @param {function} callback OnDone callback
	 * @returns {void}
	 */
	function saveTmpChange( callback ) {
		var
			data = {
				data: {}
			},
			icon = null;

		$( "#profiles-manager [name]" ).each( function iterteOverNamedElements() {
			var
				$element = $( this ),
				name = $element.attr( "name" ),
				val = null;

			if ( !name ) {
				return true;
			}

			if ( "textarea" === this.tagName.toLowerCase() && $element.data( "summernote" ) ) {
				if ( $element.summernote( "isEmpty" ) ) {
					val = "";

				} else {
					val = $element.summernote( "code" );
				}

			} else {
				val = $element.val();
			}

			if ( name.indexOf( ADK.locale.prefix ) === 0 ) {
				setValue( data.data, name.substr( ADK.locale.prefix.length ), val );
			}

			return true;
		} );

		data.id = $( "#profiles" ).val();
		icon = ADK.n.hourglass();

		$.post( ADK.locale.changeUrl.replace( /&amp;/g, "&" ), data )

		.fail( function saveProfileFail() {
			ADk.alert( ADK.locale.networkError );
		} )

		.always( function saveProfileAlways() {
			if( typeof icon === "function" ) {
				icon();
			}

			callback();
		} )

		.done( function saveProfileDone( respStr ) {
			var resp = null;

			// If response is empty or doesn't contain JSON string
			if ( respStr ) {
				resp = ADK.checkResponse( respStr );

				if ( null === resp ) {
					return;
				}

				// Task have been saved
				if ( resp.success ) {
					$( "#profile-save" ).removeAttr( "disabled" );
					$( "#profile-undo" ).removeAttr( "disabled" );
					$( "#profile-redo" ).attr( "disabled", "disabled" );
					ADK.e.trigger( "profile.tmp.save" );

					// reloadPreview();

				// We got error
				} else if ( resp.error ) {
					ADK.n.alert( resp.error );

				// Something went wrong
				} else {
					ADK.n.alert( ADK.locale.networkError );
				}

			} else {
				ADK.n.alert( ADK.locale.networkError );
				console.error( resp );
			}
		} );
	}

	/**
	 * Iterates over all the controls and saves snapshot if needed
	 * @returns {void}
	 */
	function watchElementChange() {
		var saving = false;

		$( "#profiles-manager [name^=" + ADK.locale.prefix + "]" ).each( function whetherChanged() {
		// $( "#profiles-manager [name]" ).each( function whetherChanged() {
			if( typeof this.lastValue !== "undefined" && this.lastValue !== this.value ) {
				saving = true;
			}

			this.lastValue = this.value;
		} );

		if ( saving ) {
			saveTmpChange( function callback() {
				setTimeout( watchElementChange, 1000 );
			} );

		} else {
			setTimeout( watchElementChange, 1000 );
		}
	}

	/**
	 * Initializes profile controls
	 * @returns {void}
	 */
	function initProfileControls() {

		// Show info messages
		if( ADK.locale.showTips ) {
			$( ".tip" ).css( "display", "block" );
		}

		// Perform common slider initialization
		$( "#profile-controls .slider" ).each( function iterateOverProfileControlsSliders() {
			ADK.initSlider( this );
		} );

		// Perform common switchable initialization
		$( "#profile-controls .switchable" ).each( function iterateOverProfileControlSwitchables() {
			ADK.initSwitchable( this );
		} );

		// need to go before irisInit
		$( "#profile-color-scheme input" ).val( $( "#header-bg-color" ).val() );

		// Initialize iris color-pickers
		$( "#profile-controls .iris" ).each( function iterateOverIrisColorpickers() {
			ADK.initIris( this );
		} );

		// Calculate left column width depend on right column width for two column template layout
		$( "#columns-width-right" ).on( "input", function rightColumnWidthChange() {
			$( "#columns-width-left" ).val( 100 - this.value );
		} );

		// Summernote all the textarea withing tab
		$( "#profile-controls textarea" ).each( function iterateOverSummernoted() {
			var
				$textarea = $( this ),
				data = {

					// >= v0.7
					callbacks: {
						onEnter: function summerNoteOnChange( contents ) {
							$textarea.val( contents ).trigger( "change" );
						}
					},

					// <= 0.6
					onChange: function summernoteOnChange( contents ) {
						$textarea.val( contents ).trigger( "change" );
					}
				};

			data = initShortcodable( $textarea, data );
			$textarea.summernote( data );
		} );

		// Save profile
		$( "#profile-save" ).on( "click", function saveProfileData() {
			var
				$button = $( this ),
				data = {};

			$( "#profiles-manager [name]" ).each( function iterteOverNamedElements() {
				var
					$element = $( this ),
					name = $element.attr( "name" ),
					val = null;

				if ( !name ) {

					return true;
				}

				val = $element.val();

				if ( name.indexOf( ADK.locale.prefix ) === 0 ) {
					data[ name.substr( ADK.locale.prefix.length ) ] = val;
				}

				return true;
			} );

			data.id = $( "#profiles" ).val();

			$( "#profile-controls .wait-screen" ).addClass( "shown" );

			$.post( $button.attr( "data-url" ).replace( /&amp;/g, "&" ), data )

			.always( function saveProfileAlways() {
				$( "#profile-controls .wait-screen" ).removeClass( "shown" );
			} )

			.fail( function saveProfileFail() {
				ADK.alert( ADK.locale.networkError );
			} )

			.done( function saveProfileDone( respStr ) {
				var resp = null;

				// If response is empty or doesn't contain JSON string
				if ( respStr ) {
					resp = ADK.checkResponse( respStr );

					if ( null === resp ) {
						return;
					}

					// Task have been saved
					if ( resp.success ) {
						ADK.n.notification( ADK.locale.profileSaved );
						$( "#profile-save" ).attr( "disabled", "disabled" );
						$( "#profile-undo" ).attr( "disabled", "disabled" );
						$( "#profile-redo" ).attr( "disabled", "disabled" );

					// We got error
					} else if ( resp.error ) {
						ADK.alert( resp.error );

					// Something went wrong
					} else {
						ADK.alert( ADK.locale.undefServerResp );
					}

				} else {
					console.error( resp );
					ADK.alert( ADK.locale.networkError );
				}
			} );
		} );

		// Clone profile
		$( "#profile-clone" ).on( "click", function cloneProfileData() {
			var
				$button = $( this );

			$( "#profile-controls .wait-screen" ).addClass( "shown" );

			$.post(
				$button.attr( "data-url" ).replace( /&amp;/g, "&" ),
				{ id: $( "#profiles" ).val() }
			)

			.always( function cloneProfileAlways() {
				$( "#profile-controls .wait-screen" ).removeClass( "shown" );
			} )

			.fail( function cloneProfileFail() {
				ADK.alert( ADK.locale.networkError );
			} )

			.done( function cloneProfileDone( respStr ) {
				var resp = null;

				// If response is empty or doesn't contain JSON string
				if ( respStr ) {
					resp = ADK.checkResponse( respStr );

					if ( null === resp ) {
						return;
					}

					// Task have been saved
					if ( resp.success ) {
						ADK.n.notification( ADK.locale.profileCloned );

						$( "#profiles" ).append(
							"<option value='" + resp.success.id + "'>" +
								resp.success.name +
							"</option>"
						);

						$( "#profiles" ).val( resp.success.id )
						.trigger( "change" );

					// We got error
					} else if ( resp.error ) {
						ADK.alert( resp.error );

					// Something went wrong
					} else {
						ADK.alert( ADK.locale.undefServerResp );
					}

				} else {
					console.error( resp );
					ADK.alert( ADK.locale.networkError );
				}
			} );
		} );

		// Delete profile
		$( "#profile-delete" ).on( "click", function deleteProfileData() {
			ADK.confirm( ADK.locale.sureDeleteProfile ).yes( deleteProfile );
		} );

		// Undo/Redo profile changes
		$( "#profile-undo, #profile-redo" ).on( "click", function redoUndo() {

			$( "#profiles-manager .wait-screen").addClass( "shown" );

			$.post( $( this ).attr( "data-url" )
				.replace( /&amp;/g, "&" ), { id: $( "#profiles" ).val() } )

			.always( function fetchProfileControlsAlways() {
				$( "#profiles-manager .wait-screen").removeClass( "shown" );
			} )

			.done( function undoRedoDone( respStr ) {
				var resp = null;

				// If response is empty or doesn't contain JSON string
				if ( respStr ) {
					resp = ADK.checkResponse( respStr );

					if ( null === resp ) {
						return;
					}

					// Task have been saved
					if ( resp.success ) {
						$( "#profile-controls" ).html( resp.success );
						ADK.e.trigger( "profile.controls.fetch.end" );

					// We got error
					} else if ( resp.error ) {
						ADK.alert( resp.error );

					// Something went wrong
					} else {
						ADK.alert( ADK.locale.undefServerResp );
					}

				} else {
					console.error( resp );
					ADK.alert( ADK.locale.networkError );
				}
			} )

			.fail( function saveTaskActionFail() {
				ADK.alert( ADK.locale.networkError );
			} );
		} );

		// Initialize template width units measure
		ADK.measureUnitsSwitch( document.getElementById( "template-width-units" ) );
	}

	/**
	 * Changes state of template slider depend on template width measure units change
	 * @param {object} element Measure units switcher
	 * @returns {void}
	 */
	function rangeChangeState( element ) {
		if( "%" === element.value ) {

			// Enable profile width range slider
			$( "#profile-width-range-slider" ).slider( "enable" );
			$( "#template-range-min" ).removeAttr( "disabled" );
			$( "#template-range-max" ).removeAttr( "disabled" );

		} else {

			// Disable profile width range slider - since profile width is fixed
			$( "#profile-width-range-slider" ).slider( "disable" );
			$( "#template-range-min" ).attr( "disabled", "disabled" );
			$( "#template-range-max" ).attr( "disabled", "disabled" );
		}
	}

	/**
	 * Fetches profile controls
	 * @fires Event#profile.controls.fetch.end
	 * @returns {void}
	 */
	function fetchProfileCOntrols() {

		$( "#profiles-manager .wait-screen").addClass( "shown" );

		$.post( ADK.locale.profileUrl.replace( /&amp;/g, "&" ), { id: $( "#profiles" ).val() } )

		.always( function fetchProfileControlsAlways() {
			$( "#profiles-manager .wait-screen").removeClass( "shown" );
		} )

		.done( function fetchProfileControlsDone( respStr ) {
			var resp = null;

			// If response is empty or doesn't contain JSON string
			if ( respStr ) {
				resp = ADK.checkResponse( respStr );

				if ( null === resp ) {
					return;
				}

				// Task have been saved
				if ( resp.success ) {
					$( "#profile-controls" ).html( resp.success );
					ADK.e.trigger( "profile.controls.fetch.end" );

				// We got error
				} else if ( resp.error ) {
					ADK.alert( resp.error );

				// Something went wrong
				} else {
					ADK.alert( ADK.locale.undefServerResp );
				}

			} else {
				console.error( resp );
				ADK.alert( ADK.locale.networkError );
			}
		} )

		.fail( function saveTaskActionFail() {
			ADK.alert( ADK.locale.networkError );
		} );
	}

	/**
	 * Reloads preview iframe contents
	 * @returns {void}
	 */
	function reloadPreview() {
		var
			lang_id = getCurrentLanguage(),
			profile_id = getCurrentProfile(),
			store_id = getCurrentStore(),
			template_id = getCurrentTemplate(),
			url = ADK.locale.previewUrl.replace( /&amp;/m, "&" );

		if ( profile_id ) {
			url += "&profile_id=" + profile_id;
		}

		if ( template_id ) {
			url += "&template_id=" + template_id;
		}

		if ( lang_id ) {
			url += "&lang_id=" + lang_id;
		}

		if ( store_id ) {
			url += "&store_id=" + store_id;
		}

		url += "&show_img=" + ( document.getElementById( "preview-images" ) ?
			document.getElementById( "preview-images" ).val() : "1" );

		$( ".iframe-wrapper .wait-screen").addClass( "shown" );
		$( "#preview-reload" ).btnActive();
		$( "#preview" ).attr( "src", url );
	}

	/**
	 * Returns profile ID to use in preview window
	 * @returns {null|int} Profile ID or null
	 */
	function getCurrentProfile() {
		var ret = null;

		if ( $( "#profiles-manager" ).hasClass( "active" ) ) {
			ret = $( "#profiles" ).val();
		}

		return ret;
	}

	/**
	 * Returns template ID to use i preview window
	 * @returns {int} Template ID
	 */
	function getCurrentTemplate() {
		var ret = null;

		if ( $( "#profiles-manager" ).hasClass( "active" ) ) {
			ret = $( "#preview-template" ).val();

		} else {
			ret = $( "#templates" ).val();
		}

		return ret;
	}

	/**
	 * Returns language code to use as language for preview window
	 * @returns {null|string} Language code or null
	 */
	function getCurrentLanguage() {
		var
			match = null,
			ret = null,
			str = "";

		// Any tab but Profile Management
		if ( !$( "#profile-manager" ).hasClass( "active" ) ) {
			str = $( ".template-store .tab-pane.active .lang-tab-headers li.active a" )
				.attr( "href" );

			if ( str ) {
				match = str.match( /^#store\-[^\-]+\-([^\-]+)$/ );

				if( match && match[ 1 ] ) {
					ret = match[ 1 ];
				}
			}
		}

		return ret;
	}

	/**
	 * Returns store ID to use as language for preview window
	 * @returns {null|string} Language code or null
	 */
	function getCurrentStore() {
		var
			match = null,
			ret = null,
			str = "";

		// Any tab but Profile Management
		if ( !$( "#profile-manager" ).hasClass( "active" ) ) {
			str = $( ".store-tab-headers li.active a" ).attr( "href" );

			if ( str ) {
				match = str.match( /^#store\-([^\-]+)$/ );

				if( match && match[ 1 ] ) {
					ret = match[ 1 ];
				}
			}
		}

		return ret;
	}

	/**
	 * Preview iframe onLoad callback
	 * @returns {void}
	 */
	function previewLoaded() {
		$( ".iframe-wrapper .wait-screen").removeClass( "shown" );
		$( "#preview-reload" ).btnReset();

		$( "#preview" ).css( {
			height: $( "#preview" ).contents()
			        .find( "body" )
			        .height()
		} );
	}

	/**
	* Checks element to be enabled
	* @param {string} selector Element CSS selector
	* @returns {boolean} Enabled status
	*/
	function checkEnabled( selector ) {
		return $( selector ).length > 0 && !$( selector ).attr( "disabled" );
	}

	/**
	 * Runs action before controls fetch
	 * @returns {void}
	 */
	function beforeControlsFetch() {
		ADK.activeTemplateTabs = [];
		$( "#template-controls .nav-tabs li.active a" ).each( function eachActiveTab() {
			ADK.activeTemplateTabs.push( $( this ).attr( "href" ) );
		} );
	}

	/**
	 * Fetch template controls
	 * @fires Event#template.controls.fetch.start
	 * @fires Event#template.controls.fetch.end
	 * @returns {void}
	 */
	function fetchTemplateControls() {
		var data = {};

		ADK.e.trigger( "template.controls.fetch.start" );

		$( "#contents-manager .wait-screen").addClass( "shown" );

		data.template_id = $( "#templates" ).val();

		$.post( ADK.locale.templateUrl.replace( /&amp;/g, "&" ), data )

		.always( function fetchTemplateControlsAlways() {
			$( "#contents-manager .wait-screen").removeClass( "shown" );
		} )

		.done( function fetchTemplatesControlsDone( respStr ) {
			var resp = null;

			// If response is empty or doesn't contain JSON string
			if ( respStr ) {
				resp = ADK.checkResponse( respStr );

				if ( null === resp ) {
					return;
				}

				if ( resp.success ) {
					$( "#template-controls" ).html( resp.success );
					ADK.e.trigger( "template.controls.fetch.end" );

				// We got error
				} else if ( resp.error ) {
					ADK.alert( resp.error );

				// Something went wrong
				} else {
					ADK.alert( ADK.locale.ndefServerResp );
				}

			} else {
				console.error( resp );
				ADK.alert( ADK.locale.etworkError );
			}
		} )

		.fail( function fetchTemplateControlsFail() {
			ADK.alert( ADK.locale.etworkError );
		} );
	}

	/**
	 * Fetch template controls in response to undo/redo action
	 * @fires Event#template.controls.fetch.start
	 * @fires Event#template.controls.fetch.end
	 * @returns {void}
	 */
	function undoRedoTemplate() {
		var data = {};

		ADK.e.trigger( "template.controls.fetch.start" );

		$( "#contents-manager .wait-screen").addClass( "shown" );

		data.template_id = $( "#templates" ).val();

		$.post( $( this ).attr( "data-url" )
		.replace( /&amp;/g, "&" ), data )

		.always( function undoRedoTemplateAlways() {
			$( "#contents-manager .wait-screen").removeClass( "shown" );
		} )

		.done( function undoRedoTemplateDone( respStr ) {
			var resp = null;

			// If response is empty or doesn't contain JSON string
			if ( respStr ) {
				resp = ADK.checkResponse( respStr );

				if ( null === resp ) {
					return;
				}

				if ( resp.success ) {
					$( "#template-controls" ).html( resp.success );
					ADK.e.trigger( "template.controls.fetch.end" );

					// reloadPreview();

				// We got error
				} else if ( resp.error ) {
					ADK.alert( resp.error );

				// Something went wrong
				} else {
					ADK.alert( ADK.locale.ndefServerResp );
				}

			} else {
				console.error( resp );
				ADK.alert( ADK.locale.networkError );
			}
		} )

		.fail( function saveTaskActionFail() {
			ADK.alert( ADK.locale.etworkError );
		} );
	}

	/**
	 * Adds store to contents manager tab
	 * @param {object} evt Click event
	 * @fires Event#template.controls.fetch.start
	 * @fires Event#template.controls.fetch.end
	 * @fires Event#template.snapshot.add
	 * @returns {void}
	 */
	function addStore( evt ) {

		var data = {};

		if ( !evt.target ) {
			return;
		}

		evt.preventDefault();

		ADK.e.trigger( "template.controls.fetch.start" );

		$( "#contents-manager .wait-screen").addClass( "shown" );

		data.store_id = $( evt.target ).attr( "data-value" );
		data.template_id = $( "#templates" ).val();

		$.post( ADK.locale.addStoreUrl.replace( /&amp;/g, "&" ), data )

		.always( function addStoreAlways() {
			$( "#contents-manager .wait-screen").removeClass( "shown" );
		} )

		.done( function addStoreDone( respStr ) {
			var resp = null;

			// If response is empty or doesn't contain JSON string
			if ( respStr ) {
				resp = ADK.checkResponse( respStr );

				if ( null === resp ) {
					return;
				}

				if ( resp.success ) {
					$( "#template-controls" ).html( resp.success );
					ADK.e.trigger( "template.controls.fetch.end" );
					ADK.e.trigger( "template.snapshot.add" );

				// We got error
				} else if ( resp.error ) {
					ADK.alert( resp.error );

				// Something went wrong
				} else {
					ADK.alert( ADK.locale.undefServerResp );
				}

			} else {
				console.error( resp );
				ADK.alert( ADK.locale.networkError );
			}
		} )

		.fail( function addStoreFail() {
			ADK.alert( ADK.locale.networkError );
		} );
	}

	/**
	 * Removes store from contents manager tab
	 * @param {object} evt Click event
	 * @fires Event#template.controls.fetch.start
	 * @fires Event#template.controls.fetch.end
	 * @fires Event#template.snapshot.add
	 * @returns {void}
	 */
	function deleteStore( evt ) {
		var data = {};

		if ( !evt.target ) {
			return;
		}

		evt.preventDefault();
		ADK.e.trigger( "template.controls.fetch.start" );
		$( "#contents-manager .wait-screen").addClass( "shown" );

		data.store_id = $( this ).attr( "data-value" );
		data.template_id = $( "#templates" ).val();

		$.post( ADK.locale.deleteStoreUrl.replace( /&amp;/g, "&" ), data )

		.always( function deleteStoreAlways() {
			$( "#contents-manager .wait-screen").removeClass( "shown" );
		} )

		.done( function deleteStoreDone( respStr ) {
			var resp = null;

			// If response is empty or doesn't contain JSON string
			if ( respStr ) {
				resp = ADK.checkResponse( respStr );

				if ( null === resp ) {
					return;
				}

				if ( resp.success ) {
					$( "#template-controls" ).html( resp.success );
					ADK.e.trigger( "template.controls.fetch.end" );
					ADK.e.trigger( "template.snapshot.add" );

				// We got error
				} else if ( resp.error ) {
					ADK.alert( resp.error );

				// Something went wrong
				} else {
					ADK.alert( ADK.locale.undefServerResp );
				}

			} else {
				console.error( resp );
				ADK.alert( ADK.locale.networkError );
			}
		} )

		.fail( function deleteStoreFail() {
			ADK.alert( ADK.locale.networkError );
		} );
	}

	/**
	 * Adds language to contents manager tab
	 * @param {object} evt Click event
	 * @fires Event#template.controls.fetch.start
	 * @fires Event#template.controls.fetch.end
	 * @fires Event#template.snapshot.add
	 * @returns {void}
	 */
	function addLang( evt ) {
		var data = {};

		if ( !evt.target ) {
			return;
		}

		evt.preventDefault();
		ADK.e.trigger( "template.controls.fetch.start" );
		$( "#contents-manager .wait-screen").addClass( "shown" );

		data.lang_id = $( this ).attr( "data-value" );
		data.template_id = $( "#templates" ).val();
		data.store_id = $( ".store-tab-headers .active a" ).attr( "href" )
		.match( /\-([^\-]$)/ )[ 1 ];

		$.post( ADK.locale.addLangUrl.replace( /&amp;/g, "&" ), data )

		.always( function addLangAlways() {
			$( "#contents-manager .wait-screen").removeClass( "shown" );
		} )

		.done( function addLangDone( respStr ) {
			var resp = null;

			// If response is empty or doesn't contain JSON string
			if ( respStr ) {
				resp = ADK.checkResponse( respStr );

				if ( null === resp ) {
					return;
				}

				if ( resp.success ) {
					$( "#template-controls" ).html( resp.success );
					ADK.e.trigger( "template.controls.fetch.end" );
					ADK.e.trigger( "template.snapshot.add" );

				// We got error
				} else if ( resp.error ) {
					ADK.alert( resp.error );

				// Something went wrong
				} else {
					ADK.alert( ADK.locale.undefServerResp );
				}

			} else {
				console.error( resp );
				ADK.alert( ADK.locale.networkError );
			}
		} )

		.fail( function addLangFail() {
			ADK.alert( ADK.locale.networkError );
		} );
	}

	/**
	 * Removes language from store manager tab
	 * @param {object} evt Click event
	 * @fires Event#template.controls.fetch.start
	 * @fires Event#template.controls.fetch.end
	 * @fires Event#template.snapshot.add
	 * @returns {void}
	 */
	function deleteLang( evt ) {
		var
			data = {};

		if ( !evt.target ) {
			return;
		}

		evt.preventDefault();

		ADK.e.trigger( "template.controls.fetch.start" );
		$( "#contents-manager .wait-screen").addClass( "shown" );

		data.lang_id = $( this ).attr( "data-value" );
		data.template_id = $( "#templates" ).val();
		data.store_id = $( ".store-tab-headers .active a" ).attr( "href" )
			.match( /\-([^\-]+$)/ )[ 1 ];

		$.post( ADK.locale.deleteLangUrl.replace( /&amp;/g, "&" ), data )

		.always( function deleteLangAlways() {
			$( "#contents-manager .wait-screen").removeClass( "shown" );
		} )

		.done( function deleteLangDone( respStr ) {
			var resp = null;

			// If response is empty or doesn't contain JSON string
			if ( respStr ) {
				resp = ADK.checkResponse( respStr );

				if ( null === resp ) {
					return;
				}

				if ( resp.success ) {
					$( "#template-controls" ).html( resp.success );
					ADK.e.trigger( "template.controls.fetch.end" );
					ADK.e.trigger( "template.snapshot.add" );

				// We got error
				} else if ( resp.error ) {
					ADK.alert( resp.error );

				// Something went wrong
				} else {
					ADK.alert( ADK.locale.undefServerResp );
				}

			} else {
				console.error( resp );
				ADK.alert( ADK.locale.networkError );
			}
		} )

		.fail( function deleteStoreFail() {
			ADK.alert( ADK.locale.networkError );
		} );
	}

	/**
	 * Saves template content
	 * @fires Event#template.snapshot.add
	 * @returns {void}
	 */
	function saveContent() {

		// this - element which value was changed

		var data = {},
			icon = null;

		if ( ADK.saveTemplateAction ) {
			return;
		}

		ADK.saveTemplateAction = true;

		// Fill in data
		$( ".mail-content" ).each( function eachTemplateDatum() {
			var
				$editable = $editable = $( this ),
				langCode = null,
				parentId = $editable.parents( ".tab-pane" )
					.attr( "id" ),
				parts = {},
				storeId = null,
				type = $editable.attr( "data-type" ),
				value = null;

			if( "textarea" === $editable[ 0 ].tagName.toLowerCase() ) {
				value = $editable.summernote( "code" );

				if( $editable.hasClass( "oneline" ) ) {
					value = value
					.replace( /<br>|<\/p>/g, " " )
					.replace( /<[^>]*\/?>/g, "" );
				}

			} else {
				value = $editable.val();
			}

			if ( value < 1 && "profile" === type ) {
				value = null;
			}

			if ( null === value ) {
				return;
			}

			if ( parentId ) {
				parts = getStoreLang( parentId );

				if ( parts.store ) {

					storeId = parts.store;

					if( !data[ storeId ] ) {
						data[ storeId ] = {};
					}

					if ( parts.lang ) {

						langCode = parts.lang;

						if( !data[ storeId ].lang ) {
							data[ storeId ].lang = {};
						}

						if( !data[ storeId ].lang[ langCode ] ) {
							data[ storeId ].lang[ langCode ] = {};
						}

						// Language level profile mapping
						if ( "profile" === type ) {
							data[ storeId ].lang[ langCode ][ type ] = value;

						} else {
							if( !data[ storeId ].lang[ langCode ].content ) {
								data[ storeId ].lang[ langCode ].content = {};
							}

							data[ storeId ].lang[ langCode ].content[ type ] = value;
						}

					} else {
						data[ storeId ][ type ] = value;
					}

				} else {
					data[ type ] = value;
				}
			}
		} );

		icon = ADK.n.hourglass();

		$.post( ADK.locale.saveTemplateTempUrl.replace( /&amp;/g, "&" ), {
			data:        data,
			template_id: $( "#templates" ).val()
		} )

		.always( function saveContentAlways() {
			ADK.saveTemplateAction = false;
			icon();
		} )

		.done( function saveContentDone( respStr ) {
			var resp = null;

			// If response is empty or doesn't contain JSON string
			if ( respStr ) {
				resp = ADK.checkResponse( respStr );

				if ( null === resp ) {
					return;
				}

				if ( resp.success ) {
					ADK.e.trigger( "template.snapshot.add" );
					$( "#preview-template" ).val( $( "#templates" ).val() );

					// reloadPreview();
					rememberTemplateValues();
					$( "#contents-manager .raw-data" ).removeClass( "raw-data" );
				}

			} else {
				ADK.n.alert( ADK.locale.networkError );
				console.error( resp );
			}
		} )

		.fail( function addStoreFail() {
			ADK.n.alert( ADK.locale.networkError );
		} );
	}

	/**
	 * Saves template data
	 * @param {object} evt Click event
	 * @fires Event#template.snapshot.clear
	 * @returns {void}
	 */
	function saveTemplate( evt ) {
		var data = {};

		if ( !evt.target ) {
			return;
		}

		evt.preventDefault();

		$( "#contents-manager .wait-screen").addClass( "shown" );
		data.template_id = $( "#templates" ).val();

		$.post( ADK.locale.saveTemplateUrl.replace( /&amp;/g, "&" ), data )

		.always( function addStoreAlways() {
			$( "#contents-manager .wait-screen").removeClass( "shown" );
		} )

		.done( function addStoreDone( respStr ) {
			var resp = null;

			// If response is empty or doesn't contain JSON string
			if ( respStr ) {
				resp = ADK.checkResponse( respStr );

				if ( null === resp ) {
					return;
				}

				if ( resp.success ) {
					ADK.e.trigger( "template.snapshot.clear" );
					ADK.n.notification( ADK.locale.templateSaved );

				// We got error
				} else if ( resp.error ) {
					ADK.alert( resp.error );

				// Something went wrong
				} else {
					ADK.alert( ADK.locale.undefServerResp );
				}

			} else {
				console.error( resp );
				ADK.alert( ADK.locale.networkError );
			}
		} )

		.fail( function addStoreFail() {
			ADK.alert( ADK.locale.networkError );
		} );
	}

	/**
	 * Sets store-level or language-level profile
	 * @param {object} evt Click event
	 * @fires Event#template.controls.fetch.start
	 * @fires Event#template.controls.fetch.end
	 * @fires Event#template.snapshot.add
	 * @returns {void}
	 */
	// function setProfile( evt ) {

	// 	var
	// 		data = {},
	// 		storeLang = null;

	// 	if ( !evt.target ) {
	// 		return;
	// 	}

	// 	evt.preventDefault();

	// 	ADK.e.trigger( "template.controls.fetch.start" );

	// 	$( "#contents-manager .wait-screen").addClass( "shown" );

	// 	data.profile_id = $( this ).val();
	// 	data.template_id = $( "#templates" ).val();

	// 	storeLang = $( this ).parents( ".tab-pane" )
	// 	.eq( 0 );

	// 	if( storeLang.length > 0 ) {
	// 		storeLang = storeLang.attr( "id" );
	// 		storeLang = storeLang.match( /store\-([^\-]+)\-*([^\-]*)/ );

	// 		if( storeLang[ 1 ] ) {
	// 			data.store_id = storeLang[ 1 ];
	// 		}


	// 		if( storeLang[ 2 ] ) {
	// 			data.lang_id = storeLang[ 2 ];
	// 		}
	// 	}

	// 	$.post( ADK.locale.setProfileUrl.replace( /&amp;/g, "&" ), data )

	// 	.always( function setprofileAlways() {
	// 		$( "#contents-manager .wait-screen").removeClass( "shown" );
	// 	} )

	// 	.done( function setProfileDone( resp ) {

	// 		// If response is empty or doesn't contain JSON string
	// 		if ( resp ) {

	// 			if( isSessionexpired( resp ) ) {
	// 				ADK.alert( ADK.locale.sessionExpired );

	// 				return;
	// 			}

	// 			resp = sanitizeAjaxResponse( resp );

	// 			if ( null === resp ) {
	// 				ADK.alert( ADK.locale.parseError );

	// 				return;
	// 			}

	// 			if ( resp.success ) {
	// 				$( "#template-controls" ).html( resp.success );
	// 				ADK.e.trigger( "template.controls.fetch.end" );
	// 				ADK.e.trigger( "trigger.snapshot.add" );

	// 			// We got error
	// 			} else if ( resp.error ) {
	// 				ADK.alert( resp.error );

	// 			// Something went wrong
	// 			} else {
	// 				ADK.alert( ADK.locale.undefServerResp );
	// 			}

	// 		} else {
	// 			console.error( resp );
	// 			ADK.alert( ADK.locale.networkError );
	// 		}
	// 	} )

	// 	.fail( function deleteStoreFail() {
	// 		ADK.alert( ADK.locale.networkError );
	// 	} );
	// }

	/**
	 * Initializes contents manager tab controls
	 * @returns {void}
	 */
	function initContentManagerTab() {
		var $content = $( "#template-controls" );

		// Show info messages
		if( ADK.locale.showTips ) {
			$( ".tip" ).css( "display", "block" );
		}

		// Select previously active tab
		if( ADK.activeTemplateTabs.length ) {

			// Iterate over each tab set at contents manager tab
			$content.find( ".nav-tabs" ).each( function iterateOverPanelTabs() {
				var found = false;

				// Iterate over each tab of tab set in search of previously active tabs
				$( this ).children( "li:not(.tab-dropdown)" )
				.find( "a" )
				.each( function eachLiA() {
					if( $.inArray( $( this ).attr( "href" ), ADK.activeTemplateTabs ) !== -1 ) {
						$( this ).trigger( "click" );
						found = true;

						return false;
					}

					return true;
				} );

				// If tab was deleted - select first tab in tab set
				if( !found ) {
					$( this ).children( "li:not(.tab-dropdown)" )
					.eq( 0 )
					.find( "a" )
					.trigger( "click" );
				}
			} );

		// Or set first tab active
		} else {
			$content.find( ".nav-tabs" ).each( function iterateOverPanelTabs() {
				$( this ).children( "li:not(.tab-dropdown)" )
				.eq( 0 )
				.find( "a" )
				.trigger( "click" );
			} );
		}

		// Save template snapshot
		$content.find( ".mail-content" )
		.each( function eachSummernote() {
			var
				$element = $( this ),
				data = null;

			if( "textarea" === this.tagName.toLowerCase() ) {
				data = {
					callbacks: {
						onChange: save.bind( this )
					},
					onChange: save.bind( this ),
					height:   $element.attr( "data-height" ) || 250
				};

				data = initShortcodable( $element, data );
				$element.summernote( data );
				this.oldValue = $element.summernote( "code" );

			} else if ( "input" === this.tagName.toLowerCase() ) {
				this.oldValue = $element.val();
				$element.on( "input", save.bind( this ) );
			}
		} );

		// Pre-render attachments list and bind rendering on each change event
		$( ".attachment-field" ).each( function att() {
			ADK.renderAttachments.call( this );
		} );

		$content.find( ".fancy-checkbox" ).fancyCheckbox();
	}

	/**
	 * Initializes shortcodable input field
	 * @param {jQuery} $element Target element
	 * @param {object} data Initialization data
	 * @returns {object} Initialization data
	 */
	function initShortcodable( $element, data ) {
		data.height = $element.attr( "data-height" ) || 250;

		if ( $element.hasClass( "shortcode-able" ) ) {
			data.hint = ADK.summernoteHint;

			$element
			.parent()
				.find( ".help-block" )
				.append(
					"<a href='#' class='show-shortcodes-tab'><i> " +
						ADK.locale.shortcodeSupport + "</i></a>"
				);
		}

		if ( $element.hasClass( "oneline" ) ) {
			data.toolbar = false;
		}

		return data;
	}

	/**
	 * Saves content with delay
	 * @returns {void}
	 */
	function save() {

		// This - element which value was changed
		var self = this;

		$( this ).addClass( "raw-data" );

		if( ADK.saveTemplateAction ) {
			return;
		}

		clearTimeout( ADK.saveTemplateTimeout );
		ADK.saveTemplateTimeout = setTimeout( function timeout() {
			saveContent.call( self );
		}, 1000 );
	}

	/**
	 * Returns store ID and language code by template element ID
	 * @param {string} id Element ID
	 * @returns {object} Store ID, language code hash
	 */
	function getStoreLang( id ) {
		var parts = id.match( /^store\-([^\-]+)\-*(.*)$/ );

		return {
			store: parts[ 1 ],
			lang:  parts[ 2 ]
		};
	}

	/**
	 * Remembers new template values
	 * @returns {void}
	 */
	function rememberTemplateValues() {
		$( ".mail-content" ).each( function a() {
			if( "textarea" === this.tagName.toLowerCase() ) {
				this.oldValue = $( this ).summernote( "code" );

			} else {
				this.oldValue = $( this ).val();
			}
		} );
	}

	/**
	 * Add template snapshot event handler
	 * @returns {void}
	 */
	function onTemplateAddSnapshot() {
		$( "#template-save" ).removeAttr( "disabled" );
		$( "#template-undo" ).removeAttr( "disabled" );
		$( "#template-redo" ).attr( "disabled", "disabled" );
	}

	/**
	 * Clear template snapshots event handler
	 * @returns {void}
	 */
	function onTemplateClearSnapshots() {
		$( "#template-save" ).attr( "disabled", "disabled" );
		$( "#template-undo" ).attr( "disabled", "disabled" );
		$( "#template-redo" ).attr( "disabled", "disabled" );
	}

	/**
	 * Sends test email
	 * @fires Event#email.send.start
	 * @fires Event#email.send.end
	 * @fires Event#email.send.success
	 * @fires Event#email.send.failure
	 * @returns {void}
	 */
	function sendEmail() {
		var
			$button = $( this ),
			data = {
				to:          $( "#send-email-addr" ).val(),
				template_id: $( "#preview-template" ).val()
			};

		$button.btnActive();
		ADK.e.trigger( "email.send.start" );

		$.post( ADK.locale.sendEmailUrl.replace( /&amp;/g, "&" ), data )

		.always( function sendEmailAlways() {
			$button.btnReset();
			ADK.e.trigger( "email.send.end" );
		} )

		.done( function sendEmailtDone( respStr ) {
			var resp = null;

			// If response is empty or doesn't contain JSON string
			if ( respStr ) {
				resp = ADK.checkResponse( respStr );

				if ( null === resp ) {
					ADK.e.trigger( "email.send.failure" );

					return;
				}

				if ( resp.success ) {
					ADK.n.notification( resp.success );
					ADK.e.trigger( "email.send.success" );

				} else if ( resp.error ) {
					ADK.alert( resp.error );
					ADK.e.trigger( "email.send.failure" );

				} else {
					ADK.alert( ADK.locale.networkError );
					ADK.e.trigger( "email.send.failure" );
					console.error( resp );
				}

			} else {
				ADK.alert( ADK.locale.networkError );
				ADK.e.trigger( "email.send.failure" );
				console.error( resp );
			}
		} )

		.fail( function addStoreFail() {
			ADK.alert( ADK.locale.networkError );
			ADK.e.trigger( "email.send.failure" );
		} );
	}

	/**
	 * Make common initialization for all the shortcode tabs
	 * @returns {void}
	 */
	function initShortcodeTab() {
		$( "#shortcodes-manager .nav li.active a" ).each( function makeAllActive() {
			$( "#shortcodes-manager .tab-pane" + $( this ).attr( "href" ) ).addClass( "active" );
		} );
	}

	/**
	 * Initializes Vitrine shortcode tab's controls
	 * @returns {void}
	 */
	function initVitrineTab() {

		if( ADK.locale.showTips ) {
			$( ".tip" ).css( "display", "block" );
		}

		// Perform common slider initialization
		$( "#pane-vitrine .slider" ).each( function iterateOverVitrineSliders() {
			ADK.initSlider( this );
		} );

		// Perform common switchable initialization
		$( "#pane-vitrine .switchable" ).each( function iterateOverVitrineSwitchables() {
			ADK.initSwitchable( this );
		} );

		// Initialize iris color-pickers
		$( "#pane-vitrine .iris" ).each( function iterateOverVitrineIrisColorpickers() {
			ADK.initIris( this );
		} );

		// Product autofill selects
		$( "#vitrine-product-default, #vitrine-product-arbitrary" ).select2( {
			ajax: {
				url:      ADK.locale.productAutofillUrl.replace( /&amp;/g, "&" ),
				dataType: "json",
				data:     function formatSearchTerms( params ) {
					return {
						q:     params.term,
						page:  params.page,
						count: 10
					};
				},
				processResults: function processresult( data, params ) {
					params.page = params.page || 1;

					return {
						results:    data.products,
						pagination: {
							more: params.page * 10 < data.total_count
						}
					};
				},
				cache: true
			},
			escapeMarkup: function escapeMarkup( markup ) {

				return markup;
			},
			minimumInputLength: 1,
			templateResult:     function formatReponce( data, element ) {
				if( !data.image ) {
					return {};
				}

				return $( element )
					.attr( "value", data.id )
					.html(
						"<img src='" + ADK.locale.imageBase + data.image +
							"' style='height:50px' />&nbsp;" + data.text
					);
			},
			templateSelection: function formatRepoSelection( data ) {

				return $( "<span>" + data.text + "</span>" );
			},
			width: "100%"
		} );

		// Hide/show vitrine default product select
		$( "#vitrine-related" ).on( "change", shouldShowDefaultProduct );
		$( "#vitrine-type" ).on( "change", shouldShowDefaultProduct );

		shouldShowDefaultProduct();

		$( ".fancy-checkbox" ).fancyCheckbox();
	}

	/**
	 * Initializes Social shortcode tab's controls
	 * @returns {void}
	 */
	function initSocialTab() {

		if( ADK.locale.showTips ) {
			$( ".tip" ).css( "display", "block" );
		}

		// Perform common slider initialization
		$( "#pane-social .slider" ).each( function iterateOverVitrineSliders() {
			ADK.initSlider( this );
		} );

		// Perform common switchable initialization
		$( "#pane-social .switchable" ).each( function iterateOverVitrineSwitchables() {
			ADK.initSwitchable( this );
		} );

		// Initialize iris color-pickers
		$( "#pane-social .iris" ).each( function iterateOverVitrineIrisColorpickers() {
			ADK.initIris( this );
		} );

		// Initialize social inputs disabledness
		$( ".social-toggle" ).each( function eachSocialToggle() {
			toggleSocial( this );
		} );

		// Social appearance select
		$( "#social-appearance" ).select2( {
			escapeMarkup: function escapeMarkup( markup ) {
				return markup;
			},
			templateResult: function formatReponce( data, element ) {
				var
					i = 0,
					img = null,
					span = null,
					src = null;

				if( typeof data.id === "undefined" ) {
					return null;
				}

				if ( !ADK.nonExistentSocial ) {
					ADK.nonExistentSocial = [];
				}

				span = document.createElement( "span" );

				for( i = 0; i < ADK.locale.socialImageList.length; ++i ) {
					img = document.createElement( "img" );

					img.onerror = removeSocialSrc.bind( img );
					src = ADK.locale.imageBase + "social/" + data.id + "/" +
						ADK.locale.socialImageList[ i ] + ".png";

					if ( $.inArray( src, ADK.nonExistentSocial ) >= 0 ) {
						src = "";
					}

					img.src = ADK.locale.imageBase + "social/" + data.id + "/" +
						ADK.locale.socialImageList[ i ] + ".png";

					img.style.width = "40px";
					span.appendChild( img );
				}

				$( element ).append( span );

				return $( element ).attr( "value", data.id );
			},
			templateSelection: function formatRepoSelection( data ) {
				return $( "<span>" + data.text + "</span>" );
			},
			width: "100%"
		} );

		$( "#pane-social .fancy-checkbox" ).fancyCheckbox();
	}

	/**
	 * Replaces SRC attribute of non-existed social icon for default value
	 * @return {void}
	 */
	function removeSocialSrc() {
		ADK.nonExistentSocial.push( this.src );
		this.src = ADK.locale.imageBase + "no_image.png";
	}

	/**
	 * Enables/disables the shortcode button width control depend on status of full-width checkbox
	 * @param {boolean} status Checked status of full width checkbox
	 * @returns {void}
	 */
	function buttonFullWidth( status ) {
		if( status ) {
			$( "#button-width-value" ).attr( "disabled", "disabled" )
			.parents( ".form-group" )
			.find( ".slider" )
			.slider( "disable" );

		} else {
			$( "#button-width-value" ).removeAttr( "disabled" )
			.parents( ".form-group" )
			.find( ".slider" )
			.slider( "enable" );
		}
	}

	/**
	 * Initializes Button shortcode tab's controls
	 * @returns {void}
	 */
	function initButtonTab() {

		if( ADK.locale.showTips ) {
			$( ".tip" ).css( "display", "block" );
		}

		// Perform common slider initialization
		$( "#pane-button .slider" ).each( function iterateOverVitrineSliders() {
			ADK.initSlider( this );
		} );

		// Perform common switchable initialization
		$( "#pane-button .switchable" ).each( function iterateOverVitrineSwitchables() {
			ADK.initSwitchable( this );
		} );

		// Initialize iris color-pickers
		$( "#pane-button .iris" ).each( function iterateOverVitrineIrisColorpickers() {
			ADK.initIris( this );
		} );

		$( "#button-fullwidth" ).on( "change", function fullWidthChange() {
			buttonFullWidth( $( this ).is( ":checked" ) );
		} );

		$( "#pane-button .fancy-checkbox" ).fancyCheckbox();

		buttonFullWidth( $( "#button-fullwidth" ).is( ":checked" ) );
	}

	/**
	 * Initializes QR Code shortcode tab's controls
	 * @returns {void}
	 */
	function initQrcodeTab() {
		var data = {};

		if( ADK.locale.showTips ) {
			$( ".tip" ).css( "display", "block" );
		}

		// Perform common slider initialization
		$( "#pane-qrcode .slider" ).each( function iterateOverVitrineSliders() {
			ADK.initSlider( this );
		} );

		initShortcodable( $( "#qrcode-content" ), data );
		$( "#qrcode-content" ).summernote( data );
	}

	/**
	 * Initializes Invoice shortcode tab's controls
	 * @returns {void}
	 */
	function initInvoiceTab() {
		var pane = $( "#pane-invoice" );

		if( ADK.locale.showTips ) {
			$( ".tip" ).css( "display", "block" );
		}

		// Perform common slider initialization
		pane.find( ".slider" ).each( function iterateOverVitrineSliders() {
			ADK.initSlider( this );
		} );

		// Initialize iris color-pickers
		pane.find( ".iris" ).each( function iterateOverVitrineIrisColorpickers() {
			ADK.initIris( this );
		} );

		// Initialize checkboxes
		pane.find(".fancy-checkbox" ).fancyCheckbox();

		// Init color scheme
		pane.find( ".color-scheme-picker input" ).val( pane.find( "#invoice-header-color").val() )
			.trigger( "change" );
	}

	/**
	 * Saves shortcode
	 * @param {object} evt Save button click event
	 * @fires Event#shortcode.{category}.fetch.end
	 * @returns {void}
	 */
	function saveShorcode( evt ) {
		var
			$button = $( this ),
			data = {},
			isReturn = false,
			pane = null;

		if ( !evt.target ) {
			return;
		}

		// Get shortcode category
		data.category = this.id.match( /^[^\-]+\-([^\-]+)\-.*/ );
		if( data.category[ 1 ] ) {
			data.category = data.category[ 1 ];

		} else {
			ADK.alert( ADK.locale.missingCategoty );

			return;
		}

		// Collect all the shortcode data
		$button.parents( ".tab-pane" ).eq( 0 )
		.find( ".shortcode-data:not([disabled])" )
		.each( function iterateOverShortcodeData() {

			if( "checkbox" === this.type.toLowerCase() ) {
				data[ this.id ] = $( this ).is( ":checked" ) ? 1 : 0;

			} else {
				data[ this.id ] = $( this ).val();
				if( "" === data[ this.id ] ) {
					isReturn = true;
					ADK.alert( ADK.locale.needToFillIn );
					ADK.pulsate( this, 10000 );

					return false;
				}
			}

			return true;
		} );

		// Missing datum
		if( isReturn ) {
			return;
		}

		// Category specific data collection
		if( "vitrine" === data.category ) {
			data[ "vitrine-title-height" ] = $( "#vitrine-title-height-value" ).val();
			data[ "vitrine-img-width" ] = $( "#vitrine-img-width-value" ).val();
			data[ "vitrine-img-header-height" ] = $( "#vitrine-img-header-height-value" ).val();
			data[ "vitrine-img-margins" ] = $( "#vitrine-img-margins-value" ).val();
			data[ "vitrine-element-height" ] = $( "#vitrine-element-height-value" ).val();
			data[ "vitrine-element-width" ] = $( "#vitrine-element-width-value" ).val();

		} else if( "social" === data.category ) {
			data[ "social-title-height" ] = $( "#social-title-height-value" ).val();
			data[ "social-icon-height" ] = $( "#social-icon-height-value" ).val();
			data[ "social-icon-margin" ] = $( "#social-icon-margin-value" ).val();

		} else if ( "button" === data.category ) {
			data[ "button-caption-height" ] = $( "#button-caption-height-value" ).val();
			data[ "button-height" ] = $( "#button-height-value" ).val();
			data[ "button-width" ] = $( "#button-width-value" ).val();
			data[ "button-border-radius" ] = $( "#button-border-radius-value" ).val();
			data[ "button-border-width" ] = $( "#button-border-width-value" ).val();
			data[ "button-padding" ] = $( "#button-padding-value" ).val();

		} else if ( "qrcode" === data.category ) {
			data[ "qrcode-square" ] = $( "#qrcode-square-value" ).val();
			data[ "qrcode-border" ] = $( "#qrcode-border-value" ).val();

		} else if ( "invoice" === data.category ) {
			data[ "invoice-header-text-height" ] = $( "#invoice-header-text-height-value" ).val();
			data[ "invoice-body-text-height" ] = $( "#invoice-body-text-height-value" ).val();
			data[ "invoice-table-border-width" ] = $( "#invoice-table-border-width-value" ).val();
			data[ "invoice-header-border-width" ] = $( "#invoice-header-border-width-value" ).val();
			data[ "invoice-body-border-width" ] = $( "#invoice-body-border-width-value" ).val();
			data[ "invoice-product-image-width" ] = $( "#invoice-product-image-width-value" ).val();
		}

		$( "#shortcodes-manager .wait-screen").addClass( "shown" );
		$button.btnActive();

		$.post( ADK.locale.saveShortcodeUrl.replace( /&amp;/g, "&" ), data )

		.always( function saveShortcodeAlways() {
			$( "#shortcodes-manager .wait-screen").removeClass( "shown" );
			$button.btnReset();
		} )

		.done( function saveShortcodeDone( respStr ) {
			var
				resp = null,
				tab = null;

			// If response is empty or doesn't contain JSON string
			if ( respStr ) {
				resp = ADK.checkResponse( respStr );

				if ( null === resp ) {
					return;
				}

				if ( resp.success ) {
					ADK.n.notification( ADK.locale.shortcodeSaved );

					pane = $button.parents( ".tab-pane" )[ 0 ];
					pane.parentNode.removeChild( pane );

					$( ".shortcodes-manager" ).append( $( resp.success ) );

					tab = $( "a[href=#pane-" + data.category + "]" );
					$( "a[href^=#pane]" )
						.not( tab )
						.eq( 0 )
						.trigger( "click" );

					tab.trigger( "click" );

					ADK.e.trigger( "shortcode." + data.category + ".fetch.end" );
					ADK.e.trigger( "shortcode.*.save.end" );

				// We got error
				} else if ( resp.error ) {
					ADK.alert( resp.error );

				// Something went wrong
				} else {
					ADK.alert( ADK.locale.undefServerResp );
				}

			} else {
				console.error( resp );
				ADK.alert( ADK.locale.networkError );
			}
		} )

		.fail( function saveShortcodeFail() {
			ADK.alert( ADK.locale.networkError );
		} );
	}

	/**
	 * Fetches shortcode
	 * @param {object} evt Select shortcode select change event
	 * @fires Event#shortcode.fetch.end
	 * @returns {void}
	 */
	function fatchShorcode( evt ) {
		var
			$select = $( this ),
			category = null,
			data = {},
			pane = null,
			tab = null;

		if ( !evt.target ) {
			return;
		}

		// Get shortcode category
		category = this.id.match( /^([^\-]+)\-.*/ );

		if( category[ 1 ] ) {
			category = category[ 1 ];

		} else {
			ADK.alert( ADK.locale.missingCategoty );

			return;
		}

		// Get shortcode id
		data.shortcode_id = $select.val();
		data.category = category;

		$( "#shortcodes-manager .wait-screen").addClass( "shown" );

		$.post( ADK.locale.fetchShortcodeUrl.replace( /&amp;/g, "&" ), data )

		.always( function fetchShortcodeAlways() {
			$( "#shortcodes-manager .wait-screen").removeClass( "shown" );
		} )

		.done( function fetchShortcodeDone( respStr ) {
			var resp = null;

			// If response is empty or doesn't contain JSON string
			if ( respStr ) {
				resp = ADK.checkResponse( respStr );

				if ( null === resp ) {
					return;
				}

				if ( resp.success ) {
					pane = $select.parents( ".tab-pane" )[ 0 ];
					pane.parentNode.removeChild( pane );

					$( ".shortcodes-manager" ).append( $( resp.success ) );
					tab = $( "a[href=#pane-" + category + "]" );
					$( "a[href^=#pane]" )
						.not( tab )
						.eq( 0 )
						.trigger( "click" );

					tab.trigger( "click" );

					ADK.e.trigger( "shortcode." + category + ".fetch.end" );

				// We got error
				} else if ( resp.error ) {
					ADK.alert( resp.error );

				// Something went wrong
				} else {
					ADK.alert( ADK.locale.undefServerResp );
				}

			} else {
				console.error( resp );
				ADK.alert( ADK.locale.networkError );
			}
		} )

		.fail( function fatchShortcodeFail() {
			ADK.alert( ADK.locale.networkError );
		} );
	}

	/**
	 * Deletes shortcode
	 * @param {object} evt Save button click event
	 * @fires Event#shortcode.{category}.fetch.end
	 * @returns {void}
	 */
	function deleteShorcode( evt ) {
		var
			$button = $( this ),
			data = {},
			pane = null;

		if ( !evt.target ) {
			return;
		}

		// Get shortcode category
		data.category = this.id.match( /^[^\-]+\-([^\-]+)\-.*/ );
		if( data.category[ 1 ] ) {
			data.category = data.category[ 1 ];

		} else {
			ADK.alert( ADK.locale.missingCategoty );

			return;
		}

		data.shortcode_id = $button.parents( ".tab-pane" ).eq( 0 )
		.find( "select[id*=shortcode_id]" )
		.val();

		if( data.shortcode_id < 0 ) {
			ADK.alert( ADK.locale.noShortcode );

			return;
		}

		$( "#shortcodes-manager .wait-screen").addClass( "shown" );
		$button.btnActive();

		$.post( ADK.locale.deleteShortcodeUrl.replace( /&amp;/g, "&" ), data )

		.always( function delteShortcodeAlways() {
			$( "#shortcodes-manager .wait-screen").removeClass( "shown" );
			$button.btnReset();
		} )

		.done( function delteShortcodeDone( respStr ) {
			var resp = null;

			// If response is empty or doesn't contain JSON string
			if ( respStr ) {
				resp = ADK.checkResponse( respStr );

				if ( null === resp ) {
					return;
				}

				if ( resp.success ) {
					pane = $button.parents( ".tab-pane" )[ 0 ];
					pane.parentNode.removeChild( pane );

					$( ".shortcodes-manager" ).append( $( resp.success ) );
					ADK.e.trigger( "shortcode." + data.category + ".fetch.end" );

				// We got error
				} else if ( resp.error ) {
					ADK.alert( resp.error );

				// Something went wrong
				} else {
					ADK.alert( ADK.locale.undefServerResp );
				}

			} else {
				console.error( resp );
				ADK.alert( ADK.locale.networkError );
			}
		} )

		.fail( function saveShortcodeFail() {
			ADK.alert( ADK.locale.networkError );
		} );
	}

	/**
	 * Shows/hides vitrine shortcode default product select
	 * @returns {void}
	 */
	function shouldShowDefaultProduct() {
		if(
			!ADK.isEmpty( $( "#vitrine-related" ).val() ) ||
			$( "#vitrine-type").val() === "related"
		) {
			$( "#vitrine-product-default" ).parents( ".form-group" )
			.show();

		} else {
			$( "#vitrine-product-default" ).parents( ".form-group" )
			.hide();
		}

		if( $( "#vitrine-type").val() === "arbitrary" ) {
			$( "#vitrine-product-arbitrary" ).parents( ".form-group" )
			.show();

		} else {
			$( "#vitrine-product-arbitrary" ).parents( ".form-group" )
			.hide();
		}
	}

	/**
	 * Toggles social inputs disable-ability
	 * @param {object} element Switchable element
	 * @returns {void}
	 */
	function toggleSocial( element ) {
		var $element = $( element );

		if( !$element.hasClass( "social-toggle" ) ) {
			return;
		}

		if( ADK.isEmpty( element.val() ) ) {
			$element.parents( ".form-group" ).find( "input" )
			.attr( "disabled", "disabled" );

		} else {
			$element.parents( ".form-group" ).find( "input" )
			.removeAttr( "disabled" );
		}
	}

	/**
	 * Collects profile configuration and initiates its saving with delay
	 * @returns {void}
	 */
	function collectProfileConfiguration() {
		var
			$parent = null,
			$select = $( this ),
			data = {};

		$parent = $select.parents( "tr" );

		data.level = $parent.attr( "data-level" );
		data.id = $parent.find( "[data-level-id]" ).attr( "data-level-id" );

		$parent.find( ".template-configuration" ).each( function eachTemplateConfig() {
			data[ $( this ).attr( "data-name" ) ] = $( this ).val();
		} );

		ADK.templateConfigurations.push( data );

		clearTimeout( ADK.saveTemplateConfigurationTimeout );
		ADK.saveTemplateConfigurationTimeout = setTimeout( saveProfileMapping, 2000 );
	}

	/**
	 * Saves profile mapping
	 * @returns {void}
	 */
	function saveProfileMapping() {
		var
			data = {},
			hourglass = null;

		$.each( ADK.templateConfigurations, function eachConfigurations( i, v ) {
			data[ i ] = v;
		} );

		$( "#profile-mapping .wait-screen").addClass( "shown" );

		hourglass = ADK.n.hourglass();

		$.post( ADK.locale.saveProfileMappingUrl.replace( /&amp;/g, "&" ), { config: data } )

		.always( function saveProfileMappingAlways() {
			$( "#profile-mapping .wait-screen").removeClass( "shown" );
			ADK.templateConfigurations = [];
			hourglass();
		} )

		.done( function saveProfileMappingDone( respStr ) {
			var resp = null;

			// If response is empty or doesn't contain JSON string
			if ( respStr ) {
				resp = ADK.checkResponse( respStr );

				if ( null === resp ) {
					return;
				}

				if ( resp.success ) {
					ADK.n.notification( resp.success );

				// We got error
				} else if ( resp.error ) {
					ADK.alert( resp.error );

				// Something went wrong
				} else {
					ADK.alert( ADK.locale.undefServerResp );
				}

			} else {
				console.error( resp );
				ADK.alert( ADK.locale.networkError );
			}
		} )

		.fail( function saveShortcodeFail() {
			ADK.alert( ADK.locale.networkError );
		} );
	}

	/**
	 * Sets extension settings
	 * @param {object} data Settings date
	 * @param {element} elementIn View element
	 * @param {function} callback Success callback
	 * @returns {void}
	 */
	function setSetting( data, elementIn, callback ) {
		var element = null;

		if ( elementIn instanceof Element ) {
			element = $( element );

		} else {
			element = elementIn;
		}

		if ( element[ 0 ].tagName.toLowerCase() === "button" ) {
			element.btnActive();

		} else {
			disableElement( element );
		}

		$.post( ADK.locale.settingUrl.replace( /&amp;/g, "&" ), data )

		.always( function saveProfileMappingAlways() {
			if ( element[ 0 ].tagName.toLowerCase() === "button" ) {
				element.btnReset();

			} else {
				enableElement( element );
			}
		} )

		.done( function setSettingDone( respStr ) {
			var resp = null;

			// If response is empty or doesn't contain JSON string
			if ( respStr ) {
				resp = ADK.checkResponse( respStr );

				if ( null === resp ) {
					rollBackSetting( element );

					return;
				}

				if ( resp.success ) {
					ADK.n.notification( resp.success );

					if( typeof callback === "function" ) {
						callback();
					}

					ADK.e.trigger( "setting." + data.name + ".changed", data );

					return;

				// We got error
				} else if ( resp.error ) {
					ADK.alert( resp.error );
					rollBackSetting( element );

				// Something went wrong
				} else {
					ADK.alert( ADK.locale.undefServerResp );
					rollBackSetting( element );
				}

			} else {
				console.error( resp );
				ADK.alert( ADK.locale.networkError );
				rollBackSetting( element );
			}

			ADK.e.trigger( "setting." + data.name + ".error" );
		} )

		.fail( function saveShortcodeFail() {
			ADK.alert( ADK.locale.networkError );
			rollBackSetting( element );
		} );
	}

	/**
	 * Set previous setting value when failed change its value
	 * @param {jQuery} element Setting control
	 * @returns {void}
	 */
	function rollBackSetting( element ) {
		if( element.hasClass( "fancy-checkbox" ) ) {
			element.fancyCheckbox( "toggle-view" );
		}
	}

	/**
	 * Disables element
	 * @param {jQuery} element Element to be made disabled
	 * @returns {void}
	 */
	function disableElement( element ) {

		// Fancy checkbox
		if( element.hasClass( "fancyCheckbox" ) ) {
			element.fancyCheckbox( "disable" );

		// Other elements
		} else {
			element.attr( "disable", "disable" );
		}
	}

	/**
	 * Enables element
	 * @param {jQuery} element Element to be made disabled
	 * @returns {void}
	 */
	function enableElement( element ) {

		// Fancy checkbox
		if( element.hasClass( "fancyCheckbox" ) ) {
			element.fancyCheckbox( "enable" );

		// Other elements
		} else {
			element.removeAttr( "disable" );
		}
	}

	/**
	 * Adds new template
	 * @param {object} data Template data
	 * @returns {void}
	 */
	function addNewTemplate( data ) {
		var
			dataToSend = {};

		// Filter data
		$.each( data, function filterDara( i, v ) {
			if ( $.inArray( i, [ "callback", "callbackDone" ] ) === -1 ) {
				dataToSend[ i ] = v;
			}
		} );

		$.post(
			$( "#new-template-add" ).attr( "data-url" )
				.replace( /&amp;/g, "&" ),
				dataToSend
		)

		.always( function addTemplateAlways() {
			if ( typeof data.callback === "function" ) {
				data.callback();
			}
		} )

		.done( function addTemplateDone( respStr ) {
			var
				$previewTemplate = $( "#preview-template" ),
				$templates = $( "#templates" ),
				resp = null;

			// If response is empty or doesn't contain JSON string
			if ( respStr ) {
				resp = ADK.checkResponse( respStr );

				if ( null === resp ) {
					return;
				}

				if ( resp.success ) {
					ADK.n.notification( resp.success );

					// Order statuses
					if ( resp.template.length ) {
						$.each( resp.template, function addT() {
							$previewTemplate.append(
								$(
									"<option value='" + this.id + "'>" + this.name + "</option>"
								)
							);

							$templates.append(
								$(
									"<option value='" + this.id + "'>" + this.name + "</option>"
								)
							);
						} );

					// Custom template
					} else {
						$previewTemplate.append(
							$(
								"<option value='" + resp.template.id + "'>" +
									resp.template.name + "</option>"
							)
						);

						$templates.append(
							$(
								"<option value='" + resp.template.id + "'>" +
									resp.template.name + "</option>"
							)
						)
						.val( resp.template.id )
						.trigger( "change" );
					}

					if ( typeof data.callbackDone === "function" ) {
						data.callbackDone();
					}

				// We got error
				} else if ( resp.error ) {
					ADK.alert( resp.error, null, "modal-md" );

				// Something went wrong
				} else {
					ADK.alert( ADK.locale.undefServerResp );
				}

			} else {
				console.error( resp );
				ADK.alert( ADK.locale.networkError );
			}
		} )

		.fail( function addTemplateFail() {
			ADK.alert( ADK.locale.networkError );
		} );
	}

	/**
	 * Shows/hide disabled extension alert
	 * @return {void}
	 */
	function showExensionDisableAlert() {
		if( ADK.isEmpty( extensionStatus ) ) {
			$( ".extension-status" ).css( "display", "block" );

		} else {
			$( ".extension-status" ).css( "display", "none" );
		}
	}

	/**
	 * Shows/hide extension's tips
	 * @return {void}
	 */
	function showExtensionHints() {
		if( ADK.isEmpty( ADK.locale.showTips ) ) {
			$( ".tip" ).css( "display", "none" );

		} else {
			$( ".tip" ).css( "display", "block" );
		}
	}

	/**
	 * Implements color schema to shortcode "invoice table"
	 * @returns {void}
	 */
	function setInvoiceColorScheme() {
		var
			$bodyBorder = $( "#invoice-body-border-color" ),
			$bodyColor = $( "#invoice-body-color" ),
			$bodyText = $( "#invoice-body-text-color" ),
			$headerBorder = $( "#invoice-header-border-color" ),
			$headerColor = $( "#invoice-header-color" ),
			$headerText = $( "#invoice-header-text-color" ),
			$tableBorder = $( "#invoice-table-border-color" ),
			baseColor = null,
			bodyColor = null,
			color = this.find( "input" ).val(),
			colorSet = null,
			scheme = this[ 0 ].colorScheme;

		if ( !scheme ) {
			return;
		}

		switch ( scheme.toLowerCase() ) {
		case "analogous" :
			colorSet = tinycolor( color ).analogous();

			// Header
			$headerColor.val( colorSet[ 0 ].toHexString() ).trigger( "change" );

			if ( colorSet[ 0 ].isLight() ) {
				$headerText.val( "#000" ).trigger( "change" );

			} else {

				$headerText.val( "#fff" ).trigger( "change" );
			}

			$headerBorder.val( colorSet[ 4 ].toHexString() ).trigger( "change" );

			// Body
			bodyColor = colorSet[ 3 ].lighten( 30 );
			$bodyColor.val( bodyColor.toHexString() ).trigger( "change" );

			if ( bodyColor.isLight() ) {
				$bodyText.val( "#000" ).trigger( "change" );

			} else {

				$bodyText.val( "#fff" ).trigger( "change" );
			}

			$bodyBorder.val( colorSet[ 2 ].toHexString() ).trigger( "change" );

			// Table
			$tableBorder.val( colorSet[ 5 ].toHexString() ).trigger( "change" );
			break;
		case "monochromatic" :
			colorSet = tinycolor( color ).monochromatic();

			// Header
			$headerColor.val( colorSet[ 0 ].toHexString() ).trigger( "change" );

			if ( colorSet[ 0 ].isLight() ) {
				$headerText.val( "#000" ).trigger( "change" );

			} else {

				$headerText.val( "#fff" ).trigger( "change" );
			}

			$headerBorder.val( colorSet[ 3 ].toHexString() ).trigger( "change" );

			// Body
			bodyColor = colorSet[ 5 ].lighten( 40 );
			$bodyColor.val( bodyColor.toHexString() ).trigger( "change" );

			if ( bodyColor.isLight() ) {
				$bodyText.val( "#000" ).trigger( "change" );

			} else {

				$bodyText.val( "#fff" ).trigger( "change" );
			}

			$bodyBorder.val( colorSet[ 4 ].toHexString() ).trigger( "change" );

			// Table
			$tableBorder.val( colorSet[ 2 ].toHexString() ).trigger( "change" );
			break;
		case "split complement" :
			colorSet = tinycolor( color ).splitcomplement();

			// Header
			$headerColor.val( colorSet[ 0 ].toHexString() ).trigger( "change" );

			if ( colorSet[ 0 ].isLight() ) {
				$headerText.val( "#000" ).trigger( "change" );

			} else {

				$headerText.val( "#fff" ).trigger( "change" );
			}

			$headerBorder.val( colorSet[ 1 ].toHexString() ).trigger( "change" );

			// Body
			bodyColor = colorSet[ 1 ].lighten( 20 );
			$bodyColor.val( bodyColor.toHexString() ).trigger( "change" );

			if ( bodyColor.isLight() ) {
				$bodyText.val( "#000" ).trigger( "change" );

			} else {

				$bodyText.val( "#fff" ).trigger( "change" );
			}

			$bodyBorder.val( colorSet[ 0 ].toHexString() ).trigger( "change" );

			// Table
			$tableBorder.val( colorSet[ 2 ].toHexString() ).trigger( "change" );
			break;
		case "triad" :
			colorSet = tinycolor( color ).triad();

			// Header
			$headerColor.val( colorSet[ 0 ].toHexString() ).trigger( "change" );

			if ( colorSet[ 0 ].isLight() ) {
				$headerText.val( "#000" ).trigger( "change" );

			} else {

				$headerText.val( "#fff" ).trigger( "change" );
			}

			$headerBorder.val( colorSet[ 1 ].toHexString() ).trigger( "change" );

			// Body
			bodyColor = colorSet[ 1 ].lighten( 20 );
			$bodyColor.val( bodyColor.toHexString() ).trigger( "change" );

			if ( bodyColor.isLight() ) {
				$bodyText.val( "#000" ).trigger( "change" );

			} else {

				$bodyText.val( "#fff" ).trigger( "change" );
			}

			$bodyBorder.val( colorSet[ 0 ].toHexString() ).trigger( "change" );

			// Table
			$tableBorder.val( colorSet[ 2 ].toHexString() ).trigger( "change" );
			break;
		case "tetrad" :
			colorSet = tinycolor( color ).tetrad();

			// Header
			$headerColor.val( colorSet[ 0 ].toHexString() ).trigger( "change" );

			if ( colorSet[ 0 ].isLight() ) {
				$headerText.val( "#000" ).trigger( "change" );

			} else {

				$headerText.val( "#fff" ).trigger( "change" );
			}

			$headerBorder.val( colorSet[ 2 ].toHexString() ).trigger( "change" );

			// Body
			bodyColor = colorSet[ 1 ].lighten( 20 );
			$bodyColor.val( bodyColor.toHexString() ).trigger( "change" );

			if ( bodyColor.isLight() ) {
				$bodyText.val( "#000" ).trigger( "change" );

			} else {

				$bodyText.val( "#fff" ).trigger( "change" );
			}

			$bodyBorder.val( colorSet[ 0 ].toHexString() ).trigger( "change" );

			// Table
			$tableBorder.val( colorSet[ 3 ].toHexString() ).trigger( "change" );
			break;
		case "complement" :
			colorSet = tinycolor( color ).complement();
			baseColor = tinycolor( color );

			// Header
			$headerColor.val( baseColor.toHexString() ).trigger( "change" );

			if ( baseColor.isLight() ) {
				$headerText.val( "#000" ).trigger( "change" );
			} else {

				$headerText.val( "#fff" ).trigger( "change" );
			}

			$headerBorder.val( baseColor.darken( 15 ).toHexString() ).trigger( "change" );

			// Body
			$bodyBorder.val( colorSet.toHexString() ).trigger( "change" );

			bodyColor = colorSet.lighten( 30 );
			$bodyColor.val( bodyColor.toHexString() ).trigger( "change" );

			if ( bodyColor.isLight() ) {
				$bodyText.val( "#000" ).trigger( "change" );

			} else {

				$bodyText.val( "#fff" ).trigger( "change" );
			}

			// Table
			$tableBorder.val( baseColor.darken( 25 ).toHexString() ).trigger( "change" );
			break;
		default:
			break;
		}
	}

	/**
	 * Cleans archive records
	 * @returns {void}
	 */
	function cleanArchive() {
		var
			button = $( "#archive-clean" ),
			days = $( "#archive-days-value" ).val();

		button.btnActive();

		$.post( button.attr( "data-url" ).replace( /&amp;/g, "&" ), { days: days } )

		.always( function addTemplateAlways() {
			button.btnReset();
		} )

		.done( function deteteTemplateDone( respStr ) {
			var resp = null;

			// If response is empty or doesn't contain JSON string
			if ( respStr ) {
				resp = ADK.checkResponse( respStr );

				if ( null === resp ) {
					return;
				}

				if ( resp.success ) {
					ADK.n.notification( resp.success.text );

					button.parent().find( "b" )
						.text( resp.success.dir_size );

				// We got error
				} else if ( resp.error ) {
					ADK.alert( resp.error );

				// Something went wrong
				} else {
					ADK.alert( ADK.locale.undefServerResp );
				}

			} else {
				console.error( resp );
				ADK.alert( ADK.locale.networkError );
			}
		} )

		.fail( function addTemplateFail() {
			ADK.alert( ADK.locale.networkError );
		} );
	}

	/**
	 * Fetches email history
	 * @param {object} dataIn Filter data
	 * @returns {void}
	 */
	function fetchTableContents( dataIn ) {

		// Table's container
		var
			$this = $( this ),
			data = dataIn || {};

		if ( !data.where ) {
			data.where = collectFilterFields.call( this );
		}

		if ( !data.order_by ) {
			data.order_by = getSortOrder.call( this );
		}

		if ( !data.page ) {
			data.page = getTablePage.call( this );
		}

		$this
			.parents( ".tab-pane" )
			.eq( 0 )
			.find( ".wait-screen" )
			.addClass( "shown" );

		$.post( $this.attr( "data-url" ).replace( /&amp;/g, "&" ), data )

		.always( function fetchTebleContentsAlways() {
			$this
				.parents( ".tab-pane")
				.eq( 0 )
				.find( ".wait-screen" )
				.removeClass( "shown" );
		} )

		.done( function fetchTableContentsDone( resp ) {

			// If response is empty or doesn't contain JSON string
			if ( resp ) {

				if( ADK.isSessionexpired( resp ) ) {
					ADK.alert( ADK.locale.sessionExpired );

					return;
				}

				$this.empty().append( $( resp ) );
			}
		} )

		.fail( function fetchTableContentsFail() {
			ADK.alert( ADK.locale.networkError );
		} );
	}

	/**
	 * Collects filtering fields for sortable table
	 * @returns {object} Filtering data
	 */
	function collectFilterFields() {
		var

			// This = container
			$this = $( this ),
			data = [];

		// Collect filter values
		$this.parents( ".table-overall" )
		.find( ".table-filter" )
		.each( function autofillEach() {

			var
				$self = $( this ),
				date = null,
				date1 = null,
				date2 = null,
				parts = null,
				where = null;

			if ( !$self.val() ) {
				return data;
			}

			if ( $self.hasClass( "table-filter-date" ) ) {
				date = new Date();

				switch( $self.val().toLowerCase() ) {
				case "day" :
					date.setDate( date.getDate() - 1 );
					where = {
						field:     $self.attr( "data-type" ),
						value:     ADK.sqlTimeStr( date ),
						operation: ">"
					};
					break;
				case "week" :
					date.setDate( date.getDate() - 7 );
					where = {
						field:     $self.attr( "data-type" ),
						value:     ADK.sqlTimeStr( date ),
						operation: ">"
					};
					break;
				case "two_weeks" :
					date.setDate( date.getDate() - 14 );
					where = {
						field:     $self.attr( "data-type" ),
						value:     ADK.sqlTimeStr( date ),
						operation: ">"
					};
					break;
				case "month" :
					date = new Date();
					date.setMonth( date.getMonth() - 1 );
					where = {
						field:     $self.attr( "data-type" ),
						value:     ADK.sqlTimeStr( date ),
						operation: ">"
					};
					break;
				default:
					parts = $self.val().split( " " );

					if ( parts[ 0 ] && parts[ 1 ] ) {
						date1 = new Date( parts[ 0 ] );
						date2 = new Date( parts[ 1 ] );
						date2.setHours( 24 );
						date2.setMinutes( 0 );
						date2.setSeconds( 0 );

						if ( date1.toString() !== "Invalid Date" &&
							date2.toString() !== "Invalid Date" ) {

							// From date
							data.push( {
								field:     $self.attr( "data-type" ),
								value:     ADK.sqlTimeStr( date1 ),
								operation: ">="
							} );

							// To date
							data.push( {
								field:     $self.attr( "data-type" ),
								value:     ADK.sqlTimeStr( date2 ),
								operation: "<="
							} );
						}
					}
				}

			} else if ( "to" === $self.attr( "data-type" ) ) {
				where = {
					field: "to",
					value: $self.val().map( function m( v ) {
						return ADK.htmlSpecialcharsDecode( v );
					} ),
					operation: "in"
				};
			} else {
				where = {
					field:     $self.attr( "data-type" ),
					value:     $self.val(),
					operation: "in"
				};
			}

			if ( where ) {
				data.push( where );
			}

			return null;
		} );

		return data;
	}

	/**
	 * Returns sort order for sortable table
	 * @returns {object} Sort order object
	 */
	function getSortOrder() {
		var

			// This - container element
			active = $( this ).find( ".active-sort" ),
			data = {},
			order = active.hasClass( "fa-sort-amount-asc" ) ? "asc" : "desc",
			type = active.parents( "th" ).attr( "data-type" );

		if ( !type ) {
			return data;
		}

		data[ type ] = order;

		return data;
	}

	/**
	 * Returns current pagination page
	 * @returns {int} Page number
	 */
	function getTablePage() {
		var
			$parent = $( this )
				.parents( ".tab-pane" )
				.eq( 0 ),
			ret = $parent.find( ".pagination li.active a" ).attr( "href" ) ||
			$parent.find( ".pagination li.active" ).text() || 1;

		return ret;
	}

	/**
	 * Initializes table's auto-fills elements
	 * @param {object} list List of elements to initialized
	 * @returns {void}
	 */
	function filter_autofill_init( list ) {

		// History filter auto-fill selects
		list.select2( {
			ajax: {
				url:      ADK.locale.tableFilterAutofilllUrl.replace( /&amp;/g, "&" ),
				dataType: "json",
				data:     function formatSearchTerms( params ) {
					return {
						q:     params.term,
						page:  params.page,
						count: 10,
						type:  this.attr( "data-type" ),
						table: this.parents( ".table-overall" ).find( "table" )
								.attr( "data-type" ),
						custom: this.attr( "data-custom" )
					};
				},
				processResults: function processresult( data, params ) {
					params.page = params.page || 1;

					return {
						results:    data.filter,
						pagination: {
							more: params.page * 10 < data.total_count
						}
					};
				},
				cache: true
			},
			escapeMarkup: function escapeMarkup( markup ) {

				return markup;
			},
			minimumInputLength: 1,
			templateResult:     function formatReponce( data, element ) {
				if ( !data.id ) {
					return null;
				}

				return $( element )
					.attr( "value", ADK.htmlSpecialcharsEncode( data.id ) )
					.html( "<span>" + data.text.replace( /[<>]/g, " " ) + "</span>" );
			},
			templateSelection: function formatRepoSelection( data ) {

				return $( "<span>" + data.text.replace( /[<>]/g, " " ) + "</span>" );
			},
			width: "100%"
		} );
	}

	/**
	 * Cleans email history
	 * @returns {void}
	 */
	function cleanHistory() {
		var
			btn = this,
			list = [];

		$( ".history-table input:checked:not(#history-select-all)" ).each( function el() {
			list.push(
				$( this )
					.parents( "tr" )
					.attr( "data-id" )
			);
		} );

		ADK.confirm(
			ADK.locale
				.cleanHistory.replace( /%s/, list.length === 0 ? ADK.locale.all : list.length )
		).yes( function cl() {

			// Clicked button
			var $this = $( btn );

			$this.btnActive();

			$( "#history-pane .wait-screen" ).addClass( "shown" );

			$.get( ADK.locale.historyCleanUrl.replace( /&amp;/g, "&" ) + "&id=" + list.join( "," ) )

			.always( function clerHistoryAlways() {
				$( "#history-pane .wait-screen" ).removeClass( "shown" );
				$this.btnReset();
			} )

			.done( function cleraHistoryDone( respStr ) {
				var
					resp = null;

				// If response is empty or doesn't contain JSON string
				if ( respStr ) {
					resp = ADK.checkResponse( respStr );

					if ( null === resp ) {
						return;
					}

					if ( resp.success ) {
						$( "#refresh-history" ).trigger( "click" );
						ADK.n.notification( resp.success );

					} else if ( resp.error ) {
						ADK.n.alert( resp.error );

					} else {
						ADK.alert( ADK.locale.networkError );
					}

				} else {
					ADK.n.alert( ADK.locale.serverError );
				}
			} )

			.fail( function clearHistoryFail() {
				ADK.alert( ADK.locale.networkError );
			} );
		} );

	}

	/**
	 * Adds new newsletter
	 * @fires Event#newsletter.add.end
	 * @returns {void}
	 */
	function addNewsletter() {

		// Clicked button
		var $this = $( this );

		$this.btnActive();

		$.post( $this.attr( "data-url" ).replace( /&amp;/g, "&" ), {
			name:        $( "#add-newsletter-name").val(),
			description: $( "#add-newsletter-description" ).val()
		} )

		.always( function addNewsletterAlways() {
			$this.btnReset();
		} )

		.done( function addNewsletterDone( respStr ) {
			var resp = null;

			// If response is empty or doesn't contain JSON string
			if ( respStr ) {
				resp = ADK.checkResponse( respStr );

				if ( null === resp ) {
					return;
				}

				if ( resp.success ) {
					ADK.n.notification( resp.success );

					ADK.e.trigger( "newsletter.add.end", {
						id:   resp.id,
						name: $( "#add-newsletter-name").val()
					} );

				} else if ( resp.error ) {
					ADK.n.alert( resp.error );

				} else {
					ADK.alert( ADK.locale.networkError );
				}

			} else {
				ADK.n.alert( ADK.locale.serverError );
			}
		} )

		.fail( function addNewsletterFail() {
			ADK.alert( ADK.locale.networkError );
		} );
	}

	/**
	 * Updates newsletter
	 * @param {object} data Newsletter's data
	 * @fires Event#newsletter.update.end
	 * @returns {void}
	 */
	function updateNewsletter( data ) {

		// Clicked button
		var $this = $( this );

		$this.btnActive();

		$.post( ADK.locale.updateNewsletterUrl.replace( /&amp;/g, "&" ), data )

		.always( function updateNewsletterAlways() {
			$this.btnReset();
		} )

		.done( function updateNwsletterDone( respStr ) {
			var resp = null;

			// If response is empty or doesn't contain JSON string
			if ( respStr ) {
				resp = ADK.checkResponse( respStr );

				if ( null === resp ) {
					return;
				}

				if ( resp.success ) {
					ADK.n.notification( resp.success );
					ADK.e.trigger( "newsletter.update.end" );

				} else if ( resp.error ) {
					ADK.n.alert( resp.error );

				} else {
					ADK.alert( ADK.locale.networkError );
				}

			} else {
				ADK.n.alert( ADK.locale.serverError );
			}
		} )

		.fail( function updateNewsletterFail() {
			ADK.alert( ADK.locale.networkError );
		} );
	}

	/**
	 * Deletes newsletter
	 * @fires Event#newsletter.delete.end
	 * @returns {void}
	 */
	function deleteNewsletter() {

		// Clicked button
		var
			$table = null,
			$this = $( this ),
			ids = [],
			list = null;

		$table = $this.parents( ".tab-pane" ).find( "table" );
		list = $table.find( ".newsletter-select:checked" );

		if ( !list.length ) {
			ADK.n.alert( ADK.locale.selectNewsletter );
			ADK.pulsate( $table.find( ".newsletter-select" ) );

			return;
		}

		list.each( function listIterate() {
			ids.push( $( this ).parents( "tr" )
					.attr( "data-id" ) );
		} );

		$this.btnActive();

		$.post( $this.attr( "data-url" ).replace( /&amp;/g, "&" ), { ids: ids } )

		.always( function deleteNewsletterAlways() {
			$this.btnReset();
		} )

		.done( function deleteNewsletterDone( respStr ) {
			var resp = null;

			// If response is empty or doesn't contain JSON string
			if ( respStr ) {
				resp = ADK.checkResponse( respStr );

				if ( null === resp ) {
					return;
				}

				if ( resp.success ) {
					ADK.n.notification( resp.success );
					ADK.e.trigger( "newsletter.delete.end", { ids: ids } );

				} else if ( resp.error ) {
					ADK.n.alert( resp.error );

				} else {
					ADK.alert( ADK.locale.networkError );
				}

			} else {
				ADK.n.alert( ADK.locale.serverError );
			}
		} )

		.fail( function deleteNewsletterFail() {
			ADK.alert( ADK.locale.networkError );
		} );
	}

	/**
	 * Fetches newsletter controls
	 * @fires Event#newsletter-controls.fetch.end
	 * @returns {void}
	 */
	function fetchNewsletterControls() {
		var
			$container = $( "#newsletter-controls" ),
			id = $( this ).val();

		if ( -1 === parseInt( id, 10 ) ) {
			return;
		}

		$container
			.parents( ".tab-pane")
			.eq( 0 )
				.find( ".wait-screen" )
				.addClass( "shown" );

		$.post( $container.attr( "data-url" ).replace( /&amp;/g, "&" ), { id: id } )

		.always( function fetchNewsletterControlsAlways() {
			$container
				.parents( ".tab-pane")
				.eq( 0 )
					.find( ".wait-screen" )
					.removeClass( "shown" );
		} )

		.done( function fetchNewsletterControlsDone( resp ) {

			// If response is empty or doesn't contain JSON string
			if ( resp ) {

				if( ADK.isSessionexpired( resp ) ) {
					ADK.alert( ADK.locale.sessionExpired );

					return;
				}

				$container.empty().html( resp );
				ADK.e.trigger( "newsletter-controls.fetch.end" );

			} else {
				ADK.n.alert( ADK.locale.serverError );
			}
		} )

		.fail( function fetchNewsletterControlsFail() {
			ADK.alert( ADK.locale.networkError );
		} );
	}

	/**
	 * Adds new subscriber
	 * @param {object} data Subscribers data
	 * @fires Event#subscriber.add.end
	 * @returns {void}
	 */
	function addSubscriber( data ) {
		var
			$this = $( this );

		$this.btnActive();

		$.post( $this.attr( "data-url" ).replace( /&amp;/g, "&" ), data )

		.always( function addSubscriberAlways() {
			$this.btnReset();
		} )

		.done( function addSubscriberDone( respStr ) {
			var resp = null;

			// If response is empty or doesn't contain JSON string
			if ( respStr ) {
				resp = ADK.checkResponse( respStr );

				if ( null === resp ) {
					return;
				}

				if ( resp.success ) {
					ADK.n.notification( resp.success );
					ADK.e.trigger( "subscriber.add.end" );

				} else if ( resp.error ) {
					ADK.n.alert( resp.error );

				} else {
					ADK.alert( ADK.locale.networkError );
				}

			} else {
				ADK.n.alert( ADK.locale.serverError );
			}
		} )

		.fail( function addSubscriberFail() {
			ADK.alert( ADK.locale.networkError );
		} );
	}

	/**
	 * Deletes subscriber
	 * @fires Event#subscriber.delete.end
	 * @returns {void}
	 */
	function deleteSubscriber() {

		// Clicked button
		var
			$table = null,
			$this = $( this ),
			ids = [],
			list = null;

		$table = $this.parents( ".table-overall" ).find( "table" );
		list = $table.find( ".newsletter-select:checked" );

		if ( !list.length ) {
			ADK.n.alert( ADK.locale.selectNewsletter );
			ADK.pulsate( $table.find( ".newsletter-select" ) );

			return;
		}

		list.each( function listIterate() {
			ids.push( $( this ).parents( "tr" )
					.attr( "data-id" ) );
		} );

		$this.btnActive();

		$.post( $this.attr( "data-url" ).replace( /&amp;/g, "&" ), { ids: ids } )

		.always( function deleteSubscriberAlways() {
			$this.btnReset();
		} )

		.done( function deleteSubscriberDone( respStr ) {
			var resp = null;

			// If response is empty or doesn't contain JSON string
			if ( respStr ) {
				resp = ADK.checkResponse( respStr );

				if ( null === resp ) {
					return;
				}

				if ( resp.success ) {
					ADK.n.notification( resp.success );
					ADK.e.trigger( "subscriber.delete.end", { ids: ids } );

				} else if ( resp.error ) {
					ADK.n.alert( resp.error );

				} else {
					ADK.alert( ADK.locale.networkError );
				}

			} else {
				ADK.n.alert( ADK.locale.serverError );
			}
		} )

		.fail( function deleteSuscriberFail() {
			ADK.alert( ADK.locale.networkError );
		} );
	}

	/**
	 * Changes subscription widget appearance depending on settings
	 * @returns {void}
	 */
	function buildWidget() {

		switch( this.id ) {
		case "widget-width-value" :
		case "widget-width-units" :
			$( ".w-adk-widget-content" )
			.css( "width", $( "#widget-width-value" ).val() + $( "#widget-width-units" ).val() );
			break;
		case "widget-background" :
			$( ".w-adk-widget-content" ).css( "background-color", $( this ).val() );
			break;
		case "widget-title" :
			$( ".w-adk-widget-caption" ).text( $( this ).val() );
			break;
		case "widget-title-color" :
			$( ".w-adk-widget-caption" ).css( "color", $( this ).val() );
			break;
		case "widget-title-height-value" :
		case "widget-title-height-units" :
			$( ".w-adk-widget-caption" )
			.css(
				"font-size",
				$( "#widget-title-height-value" ).val() + $( "#widget-title-height-units" ).val()
			);
			break;
		case "widget-caption-color" :
			$( ".w-adk-widget-content label" ).css( "color", $( this ).val() );
			break;
		case "widget-caption-height-value" :
		case "widget-caption-height-units" :
			$( ".w-adk-widget-content label" )
			.css(
				"font-size",
				$( "#widget-caption-height-value" ).val() +
					$( "#widget-caption-height-units" ).val()
			);
			break;
		case "widget-button-text" :
			$( ".w-adk-widget-content button" ).text( $( this ).val() );
			break;
		case "widget-button-text-color" :
			$( ".w-adk-widget-content button" ).css( "color", $( this ).val() );
			break;
		case "widget-button-text-height-value" :
		case "widget-button-text-height-units" :
			$( ".w-adk-widget-content button" )
			.css(
				"font-size",
				$( "#widget-button-text-height-value" ).val() +
					$( "#widget-button-text-height-units" ).val()
			);
			break;
		case "widget-button-color" :
			$( ".w-adk-widget-content button" ).css( "background-color", $( this ).val() );
			break;
		case "widget-button-border-color" :
			$( ".w-adk-widget-content button" ).css( "border-color", $( this ).val() );
			break;
		case "widget-button-border-radius-value" :
			$( ".w-adk-widget-content button" ).css( "border-radius", $( this ).val() + "px" );
			break;
		case "widget-field-border-radius-value" :
			$( ".w-adk-widget-content input" ).css( "border-radius", $( this ).val() + "px" );
			break;
		case "widget-field-background" :
			$( ".w-adk-widget-content input" ).css( "background-color", $( this ).val() );
			break;
		case "widget-box-shadow-x-value" :
		case "widget-box-shadow-y-value" :
		case "widget-box-shadow-dispersion-value" :
			$( ".w-adk-widget-content" )
			.css(
				"box-shadow",
				$( "#widget-box-shadow-x-value" ).val() + "px " +
				$( "#widget-box-shadow-y-value" ).val() + "px " +
				$( "#widget-box-shadow-dispersion-value" ).val() + "px"
			);
			break;
		case "widget-border-radius-value" :
			$( ".w-adk-widget-content" ).css( "border-radius", $( this ).val() + "px" );
			break;
		default:
		}
	}

	/**
	 * Implements color schema for subscription widget
	 * @returns {void}
	 */
	function setWidgetColorScheme() {
		var
			$bgColor = $( "#widget-background" ),
			$buttonBorderColor = $( "#widget-button-border-color" ),
			$buttonColor = $( "#widget-button-color" ),
			$buttonTextColor = $( "#widget-button-text-color" ),
			$captionColor = $( "#widget-caption-color" ),
			$fieldsBgColor = $( "#widget-field-background" ),
			$titleColor = $( "#widget-title-color" ),
			baseColor = null,
			color = this.find( "input" ).val(),
			colorSet = null,
			scheme = this[ 0 ].colorScheme;

		if ( !scheme ) {
			return;
		}

		switch ( scheme.toLowerCase() ) {
		case "analogous" :
			colorSet = tinycolor( color ).analogous();

			$bgColor.val( colorSet[ 5 ].toHexString() ).trigger( "change" );

			if ( colorSet[ 5 ].isLight() ) {
				$captionColor.val( "#000" ).trigger( "change" );

			} else {
				$captionColor.val( "#fff" ).trigger( "change" );
			}

			$titleColor.val(
				colorSet[ 5 ].clone().darken( 30 )
					.toHexString()
			).trigger( "change" );

			$fieldsBgColor.val(
				colorSet[ 4 ].clone().lighten( 20 )
					.toHexString()
			).trigger( "change" );

			$buttonColor.val(
				colorSet[ 3 ].clone().darken( 20 )
				.toHexString()
			).trigger( "change" );

			$buttonBorderColor.val(
				colorSet[ 4 ].clone().darken( 40 )
					.toHexString()
			).trigger( "change" );

			$buttonTextColor.val(
				colorSet[ 5 ].toHexString()
			).trigger( "change" );

			break;
		case "monochromatic" :
			colorSet = tinycolor( color ).monochromatic();

			$bgColor.val( colorSet[ 0 ].toHexString() ).trigger( "change" );

			if ( colorSet[ 0 ].isLight() ) {
				$captionColor.val( "#000" ).trigger( "change" );

			} else {
				$captionColor.val( "#fff" ).trigger( "change" );
			}

			$titleColor.val(
				colorSet[ 3 ].toHexString()
			).trigger( "change" );

			$fieldsBgColor.val(
				colorSet[ 0 ].clone().lighten( 20 )
					.toHexString()
			).trigger( "change" );

			$buttonColor.val(
				colorSet[ 4 ].toHexString()
			).trigger( "change" );

			$buttonBorderColor.val(
				colorSet[ 3 ].toHexString()
			).trigger( "change" );

			$buttonTextColor.val(
				colorSet[ 0 ].toHexString()
			).trigger( "change" );
			break;
		case "split complement" :
			colorSet = tinycolor( color ).splitcomplement();

			$bgColor.val( colorSet[ 0 ].toHexString() ).trigger( "change" );

			if ( colorSet[ 0 ].isLight() ) {
				$captionColor.val( "#000" ).trigger( "change" );

			} else {
				$captionColor.val( "#fff" ).trigger( "change" );
			}

			$titleColor.val(
				colorSet[ 2 ].toHexString()
			).trigger( "change" );

			$fieldsBgColor.val(
				colorSet[ 1 ].toHexString()
			).trigger( "change" );

			$buttonColor.val(
				colorSet[ 2 ].toHexString()
			).trigger( "change" );

			$buttonBorderColor.val(
				colorSet[ 2 ].clone().darken( 40 )
					.toHexString()
			).trigger( "change" );

			$buttonTextColor.val(
				colorSet[ 1 ].toHexString()
			).trigger( "change" );
			break;
		case "triad" :
			colorSet = tinycolor( color ).triad();

			$bgColor.val( colorSet[ 0 ].toHexString() ).trigger( "change" );

			if ( colorSet[ 0 ].isLight() ) {
				$captionColor.val( "#000" ).trigger( "change" );

			} else {
				$captionColor.val( "#fff" ).trigger( "change" );
			}

			$titleColor.val(
				colorSet[ 2 ].toHexString()
			).trigger( "change" );

			$fieldsBgColor.val(
				colorSet[ 1 ].toHexString()
			).trigger( "change" );

			$buttonColor.val(
				colorSet[ 2 ].toHexString()
			).trigger( "change" );

			$buttonBorderColor.val(
				colorSet[ 2 ].clone().darken( 40 )
					.toHexString()
			).trigger( "change" );

			$buttonTextColor.val(
				colorSet[ 1 ].toHexString()
			).trigger( "change" );
			break;
		case "tetrad" :
			colorSet = tinycolor( color ).tetrad();

			$bgColor.val( colorSet[ 0 ].toHexString() ).trigger( "change" );

			if ( colorSet[ 0 ].isLight() ) {
				$captionColor.val( "#000" ).trigger( "change" );

			} else {
				$captionColor.val( "#fff" ).trigger( "change" );
			}

			$titleColor.val(
				colorSet[ 2 ].toHexString()
			).trigger( "change" );

			$fieldsBgColor.val(
				colorSet[ 2 ].toHexString()
			).trigger( "change" );

			$buttonColor.val(
				colorSet[ 3 ].toHexString()
			).trigger( "change" );

			$buttonBorderColor.val(
				colorSet[ 3 ].clone().darken( 40 )
					.toHexString()
			).trigger( "change" );

			$buttonTextColor.val(
				colorSet[ 1 ].toHexString()
			).trigger( "change" );
			break;
		case "complement" :
			colorSet = tinycolor( color ).complement();
			baseColor = tinycolor( color );

			$bgColor.val( baseColor.toHexString() ).trigger( "change" );

			if ( baseColor.isLight() ) {
				$captionColor.val( "#000" ).trigger( "change" );
				$titleColor.val( "#000" ).trigger( "change" );

			} else {
				$captionColor.val( "#fff" ).trigger( "change" );
				$titleColor.val( "#fff" ).trigger( "change" );
			}

			$fieldsBgColor.val(
				colorSet.toHexString()
			).trigger( "change" );

			$buttonBorderColor.val(
				colorSet.clone().darken( 40 )
					.toHexString()
			).trigger( "change" );

			$buttonColor.val(
				colorSet.clone().darken( 30 )
					.toHexString()
			).trigger( "change" );

			if ( colorSet.isLight() ) {
				$buttonTextColor.val( "#fff" ).trigger( "change" );

			} else {
				$buttonTextColor.val( "#000" ).trigger( "change" );
			}

			break;
		default:
			break;
		}
	}

	/**
	 * Saves subscription widget
	 * @fires Event#widget.save.end
	 * @returns {void}
	 */
	function saveWidget() {

		// Clicked button
		var
			$name_field = null,
			$this = $( this ),
			data = {};

		$( ".widget-controls [data-name]" ).each( function eachControl() {
			var
				$self = $( this ),
				name = $self.attr( "data-name" );

			if ( $self.hasClass( "dimension-wrapper" ) ) {
				data[ name ] = $self.find( "input" ).val() + $self.find( "button" ).val();

			} else {
				data[ name ] = $self.val();
			}

		} );

		if ( !data.name ) {
			ADK.n.alert( ADK.locale.needToFillIn );
			$name_field = $( ".widget-controls [data-name='name']" );
			ADK.pulsate( $name_field, 6000 );
			$name_field.scrollTo();

			return;
		}

		data.id = $( "#widget-select" ).val();
		$this.btnActive();

		$.post( $this.attr( "data-url" ).replace( /&amp;/g, "&" ), data )

		.always( function saveWidgetAlways() {
			$this.btnReset();
		} )

		.done( function saveWidgetDone( respStr ) {
			var resp = null;

			// If response is empty or doesn't contain JSON string
			if ( respStr ) {
				resp = ADK.checkResponse( respStr );

				if ( null === resp ) {
					return;
				}

				if ( resp.success ) {
					ADK.n.notification( resp.success );

					ADK.e.trigger( "widget.save.end", {
						id:     resp.id,
						name:   data.name,
						code:   resp.code,
						module: resp.module_id
					} );

				} else if ( resp.error ) {
					ADK.n.alert( resp.error );

				} else {
					ADK.alert( ADK.locale.networkError );
				}

			} else {
				ADK.n.alert( ADK.locale.serverError );
			}
		} )

		.fail( function saveWidgetFail() {
			ADK.alert( ADK.locale.networkError );
		} );
	}

	/**
	 * Fetches subscription widget data
	 * @fires Event#widget.fetch.end
	 * @returns {void}
	 */
	function fetchWidget() {

		// Clicked button
		var
			$select = $( "#widget-select" ),
			$this = $( this );

		if ( -1 === parseInt( $select.val(), 10 ) ) {
			return;
		}

		$this.btnActive();

		$.post( $select.attr( "data-url" ).replace( /&amp;/g, "&" ), { id: $select.val() } )

		.always( function fetchWidgetAlways() {
			$this.btnReset();
		} )

		.done( function fetchWidgetDone( respStr ) {
			var resp = null;

			// If response is empty or doesn't contain JSON string
			if ( respStr ) {
				resp = ADK.checkResponse( respStr );

				if ( null === resp ) {
					return;
				}

				if ( resp.success ) {
					ADK.e.trigger( "widget.fetch.end", resp.success );

				} else if ( resp.error ) {
					ADK.n.alert( resp.error );

				} else {
					ADK.alert( ADK.locale.networkError );
				}

			} else {
				ADK.n.alert( ADK.locale.serverError );
			}
		} )

		.fail( function saveWidgetFail() {
			ADK.alert( ADK.locale.networkError );
		} );
	}

	/**
	 * Deletes subscription widget data
	 * @fires Event#widget.delete.end
	 * @returns {void}
	 */
	function deleteWidget() {

		// Clicked button
		var
			$select = $( "#widget-select" ),
			$this = $( this );

		if ( -1 === parseInt( $select.val(), 10 ) ) {
			return;
		}

		$this.btnActive();

		$.post( $this.attr( "data-url" ).replace( /&amp;/g, "&" ), { id: $select.val() } )

		.always( function deleteWidgetAlways() {
			$this.btnReset();
		} )

		.done( function deleteWidgetDone( respStr ) {
			var resp = null;

			// If response is empty or doesn't contain JSON string
			if ( respStr ) {
				resp = ADK.checkResponse( respStr );

				if ( null === resp ) {
					return;
				}

				if ( resp.success ) {
					ADK.n.notification( resp.success );
					ADK.e.trigger( "widget.delete.end", $select.val() );

				} else if ( resp.error ) {
					ADK.n.alert( resp.error );

				} else {
					ADK.alert( ADK.locale.networkError );
				}

			} else {
				ADK.n.alert( ADK.locale.serverError );
			}
		} )

		.fail( function saveWidgetFail() {
			ADK.alert( ADK.locale.networkError );
		} );
	}

	/**
	 * Changes button's color for throttle setting depending on setting status
	 * @param {element} e Target element
	 * @returns {void}
	 */
	function changeBtnColor( e ) {
		var $this = $( e );

		if ( ADK.isEmpty( $this.val() ) ) {
			$this.removeClass( "btn-success" )
				.addClass( "btn-default" );

		} else {
			$this.removeClass( "btn-default" )
				.addClass( "btn-success" );
		}
	}

	/**
	 * Fetches chart data
	 * @param {boolean} all Flag whether to fetch all the charts
	 * @Fires Event#chart.fetch.end
	 * @returns {void}
	 */
	function newsletterChart( all ) {

		// Apply filter button
		var $this = $( this ),
			data = {};

		data.where = collectFilterFields.call( this );
		data.id = $( "#select-newsletter" ).val();
		data.all = typeof all === "boolean" ? all : false;
		$this.btnActive();

		$.post( $this.attr( "data-url" ).replace( /&amp;/g, "&" ), data )

		.always( function newsletterChartAlways() {
			$this.btnReset();
		} )

		.done( function newsletterChartDone( respStr ) {
			var resp = null;

			// If response is empty or doesn't contain JSON string
			if ( respStr ) {
				resp = ADK.checkResponse( respStr );

				if ( null === resp ) {
					return;
				}

				if ( resp.success ) {
					ADK.e.trigger( "chart.fetch.end", resp );

				} else if ( resp.error ) {
					ADK.n.alert( resp.error );

				} else {
					ADK.n.alert( ADK.locale.networkError );
				}

			} else {
				ADK.n.alert( ADK.locale.serverError );
			}
		} )

		.fail( function newsleterChartFail() {
			ADK.n.alert( ADK.locale.networkError );
		} );
	}

	/**
	 * Implements color schema for profile
	 * @returns {void}
	 */
	function setProfileColorScheme() {
		var
			$bgColor = $( "#bg-color" ),

			$bottomBgColor = $( "#bottom-bg-color" ),
			$bottomLinkColor = $( "#bottom-link-color" ),
			$bottomTextColor = $( "#bottom-text-color" ),

			$contentBgColor = $( "#content-bg-color" ),
			$contentLinkColor = $( "#content-link-color" ),
			$contentTextColor = $( "#content-text-color" ),

			$footerBgColor = $( "#footer-bg-color" ),
			$footerLinkColor = $( "#footer-link-color" ),
			$footerTextColor = $( "#footer-text-color" ),

			$headerBgColor = $( "#header-bg-color" ),
			$headerLinkColor = $( "#header-link-color" ),
			$headerTextColor = $( "#header-text-color" ),

			$topBgColor = $( "#top-bg-color" ),
			$topLinkColor = $( "#top-link-color" ),
			$topTextColor = $( "#top-text-color" ),

			baseColor = null,
			bottomBgColor = null,
			bottomLinkColor = null,
			bottomTextColor = null,
			color = this.find( "input" ).val(),
			colorSet = null,
			contentBgColor = null,
			contentLinkColor = null,
			contentTextColor = null,
			footerBgColor = null,
			footerLinkColor = null,
			footerTextColor = null,
			headerBgColor = null,
			headerLinkColor = null,
			headerTextColor = null,
			mainBg = null,
			scheme = this[ 0 ].colorScheme,
			topBgColor = null,
			topLinkColor = null,
			topTextColor = null;

		if ( !scheme ) {
			return;
		}

		switch ( scheme.toLowerCase() ) {
		case "analogous" :
			colorSet = tinycolor( color ).analogous();

			mainBg = colorSet[ 0 ].clone().darken( 40 );

			topBgColor = mainBg.clone();
			topTextColor = topBgColor.isLight() ? "#000" : "#fff";
			topLinkColor = topTextColor;

			headerBgColor = colorSet[ 0 ].clone();
			headerTextColor = headerBgColor.isLight() ? "#000" : "#fff";
			headerLinkColor = colorSet[ 5 ].clone().lighten( 10 )
				.toHexString();

			contentBgColor = mainBg.clone().lighten( 100 );
			contentTextColor = contentBgColor.isLight() ? "#000" : "#fff";
			contentLinkColor = colorSet[ 4 ].clone()
				.toHexString();

			footerBgColor = $( "#profiles option:selected" ).eq( 0 )
				.text()
				.toLowerCase() === "extended" ? mainBg.clone().lighten( 100 ) :
				colorSet[ 0 ].clone();
			footerTextColor = footerBgColor.isLight() ? "#000" : "#fff";
			footerLinkColor = footerTextColor;

			bottomBgColor = mainBg.clone();
			bottomTextColor = bottomBgColor.isLight() ? "#000" : "#fff";
			bottomLinkColor = bottomTextColor;

			break;
		case "monochromatic" :
			colorSet = tinycolor( color ).monochromatic();

			mainBg = colorSet[ 2 ].clone();

			topBgColor = mainBg.clone();
			topTextColor = topBgColor.isLight() ? "#000" : "#fff";
			topLinkColor = topTextColor;

			headerBgColor = colorSet[ 0 ].clone();
			headerTextColor = headerBgColor.isLight() ? "#000" : "#fff";
			headerLinkColor = colorSet[ 4 ].clone()
				.toHexString();

			contentBgColor = mainBg.clone().lighten( 100 );
			contentTextColor = contentBgColor.isLight() ? "#000" : "#fff";
			contentLinkColor = colorSet[ 4 ].clone()
				.toHexString();

			footerBgColor = $( "#profiles option:selected" ).eq( 0 )
				.text()
				.toLowerCase() === "extended" ? mainBg.clone().lighten( 100 ) :
				colorSet[ 0 ].clone();
			footerTextColor = footerBgColor.isLight() ? "#000" : "#fff";
			footerLinkColor = footerTextColor;

			bottomBgColor = mainBg.clone();
			bottomTextColor = bottomBgColor.isLight() ? "#000" : "#fff";
			bottomLinkColor = bottomTextColor;

			break;
		case "split complement" :
			colorSet = tinycolor( color ).splitcomplement();

			mainBg = colorSet[ 1 ].clone();

			topBgColor = mainBg.clone();
			topTextColor = topBgColor.isLight() ? "#000" : "#fff";
			topLinkColor = topTextColor;

			headerBgColor = colorSet[ 0 ].clone();
			headerTextColor = headerBgColor.isLight() ? "#000" : "#fff";
			headerLinkColor = colorSet[ 2 ].clone()
				.toHexString();

			contentBgColor = mainBg.clone().lighten( 100 );
			contentTextColor = contentBgColor.isLight() ? "#000" : "#fff";
			contentLinkColor = colorSet[ 2 ].clone()
				.toHexString();

			footerBgColor = $( "#profiles option:selected" ).eq( 0 )
				.text()
				.toLowerCase() === "extended" ? mainBg.clone().lighten( 100 ) :
				colorSet[ 0 ].clone();
			footerTextColor = footerBgColor.isLight() ? "#000" : "#fff";
			footerLinkColor = footerTextColor;

			bottomBgColor = mainBg.clone();
			bottomTextColor = bottomBgColor.isLight() ? "#000" : "#fff";
			bottomLinkColor = bottomTextColor;

			break;
		case "triad" :
			colorSet = tinycolor( color ).triad();

			mainBg = colorSet[ 1 ].clone();

			topBgColor = mainBg.clone();
			topTextColor = topBgColor.isLight() ? "#000" : "#fff";
			topLinkColor = topTextColor;

			headerBgColor = colorSet[ 0 ].clone();
			headerTextColor = headerBgColor.isLight() ? "#000" : "#fff";
			headerLinkColor = colorSet[ 2 ].clone()
				.toHexString();

			contentBgColor = mainBg.clone().lighten( 100 );
			contentTextColor = contentBgColor.isLight() ? "#000" : "#fff";
			contentLinkColor = colorSet[ 2 ].clone()
				.toHexString();

			footerBgColor = $( "#profiles option:selected" ).eq( 0 )
				.text()
				.toLowerCase() === "extended" ? mainBg.clone().lighten( 100 ) :
				colorSet[ 0 ].clone();
			footerTextColor = footerBgColor.isLight() ? "#000" : "#fff";
			footerLinkColor = footerTextColor;

			bottomBgColor = mainBg.clone();
			bottomTextColor = bottomBgColor.isLight() ? "#000" : "#fff";
			bottomLinkColor = bottomTextColor;

			break;
		case "tetrad" :
			colorSet = tinycolor( color ).tetrad();

			mainBg = colorSet[ 1 ].clone();

			topBgColor = mainBg.clone();
			topTextColor = topBgColor.isLight() ? "#000" : "#fff";
			topLinkColor = topTextColor;

			headerBgColor = colorSet[ 0 ].clone();
			headerTextColor = headerBgColor.isLight() ? "#000" : "#fff";
			headerLinkColor = colorSet[ 2 ].clone()
				.toHexString();

			contentBgColor = mainBg.clone().lighten( 100 );
			contentTextColor = contentBgColor.isLight() ? "#000" : "#fff";
			contentLinkColor = colorSet[ 2 ].clone()
				.toHexString();

			footerBgColor = $( "#profiles option:selected" ).eq( 0 )
				.text()
				.toLowerCase() === "extended" ? mainBg.clone().lighten( 100 ) :
				colorSet[ 0 ].clone();
			footerTextColor = footerBgColor.isLight() ? "#000" : "#fff";
			footerLinkColor = footerTextColor;

			bottomBgColor = mainBg.clone();
			bottomTextColor = bottomBgColor.isLight() ? "#000" : "#fff";
			bottomLinkColor = bottomTextColor;

			break;
		case "complement" :
			colorSet = tinycolor( color ).complement();
			baseColor = tinycolor( color );

			mainBg = colorSet.clone();

			topBgColor = mainBg.clone();
			topTextColor = topBgColor.isLight() ? "#000" : "#fff";
			topLinkColor = topTextColor;

			headerBgColor = baseColor.clone();
			headerTextColor = headerBgColor.isLight() ? "#000" : "#fff";
			headerLinkColor = colorSet.clone()
				.toHexString();

			contentBgColor = mainBg.clone().lighten( 100 );
			contentTextColor = contentBgColor.isLight() ? "#000" : "#fff";
			contentLinkColor = colorSet.clone()
				.toHexString();

			footerBgColor = $( "#profiles option:selected" ).eq( 0 )
				.text()
				.toLowerCase() === "extended" ? mainBg.clone().lighten( 100 ) :
				colorSet[ 0 ].clone();
			footerTextColor = footerBgColor.isLight() ? "#000" : "#fff";
			footerLinkColor = footerTextColor;

			bottomBgColor = mainBg.clone();
			bottomTextColor = bottomBgColor.isLight() ? "#000" : "#fff";
			bottomLinkColor = bottomTextColor;

			break;
		default:
			break;
		}

		$bgColor.val( mainBg.toHexString() ).trigger( "change" );

		$topBgColor.val( topBgColor.toHexString() ).trigger( "change" );
		$topTextColor.val( topTextColor ).trigger( "change" );
		$topLinkColor.val( topLinkColor ).trigger( "change" );

		$headerBgColor.val( headerBgColor.toHexString() ).trigger( "change" );
		$headerTextColor.val( headerTextColor ).trigger( "change" );
		$headerLinkColor.val( headerLinkColor ).trigger( "change" );

		$contentBgColor.val( contentBgColor.toHexString() ).trigger( "change" );
		$contentTextColor.val( contentTextColor ).trigger( "change" );
		$contentLinkColor.val( contentLinkColor ).trigger( "change" );

		$footerBgColor.val( footerBgColor.toHexString() ).trigger( "change" );
		$footerTextColor.val( footerTextColor ).trigger( "change" );
		$footerLinkColor.val( footerLinkColor ).trigger( "change" );

		$bottomBgColor.val( bottomBgColor.toHexString() ).trigger( "change" );
		$bottomTextColor.val( bottomTextColor ).trigger( "change" );
		$bottomLinkColor.val( bottomLinkColor ).trigger( "change" );
	}

	/**
	 * Document click handler
	 * @param {object} evt Event object
	 * @return {void}
	 */
	function documentClick( evt ) {
		var found = false;

		$.each( ADK.irisHideExceptions, function iterateOverIrisHideExceptions() {
			if( evt.target === this ) {
				found = true;

				return false;
			}

			return true;
		} );

		if( !found ) {
			$( ".iris-init" ).iris( "hide" );
		}
	}

	/**
	 * Page unload event handler
	 * @param {object} evt Event object
	 * @return {void}
	 */
	function onUnload( evt ) {
		var
			enabled = false,
			i = 0,
			len = 0,
			selectors = [ "#profile-save", "#profile-redo", "#template-save", "#template-redo" ];

		for ( i = 0, len = selectors.length; i < len; ++i ) {
			if( checkEnabled( selectors[ i ] ) ) {
				enabled = true;
				break;
			}
		}

		if ( enabled ) {
			evt.returnValue = "All unsaved data will be lost";

			return "All unsaved data will be lost";
		}

		return undefined;
	}

	/**
	 * Resize preview window handler
	 * @return {void}
	 */
	function viewResize() {
		var
			active = null,
			diff = 0,
			dimentions = {
				laptop: [ 1280 ],
				tablet: [ 1024, 768 ],
				mobile: [ 480, 320 ]
			},
			index = 0,
			laptop = $( "#laptop" ),
			mobile = $( "#mobile" ),
			rotate = $( "#rotate-view" ),
			tablet = $( "#tablet" ),
			wndw = null;

		// Orientation button was clicked
		if( "rotate-view" === this.id ) {
			rotate.parent().find( ".active" )
			.each( function searchForActive() {
				if( -1 !== $.inArray( this.id, [ "laptop", "mobile", "tablet" ] ) ) {
					active = this.id;

					return false;
				}

				return false;
			} );

		// One of the size buttons was clicked
		} else {
			active = this.id;
			laptop.removeClass( "active" );
			tablet.removeClass( "active" );
			mobile.removeClass( "active" );
			rotate.removeAttr( "disabled" );

			$( "#" + active ).addClass( "active" );

			if( "laptop" === active ) {

				// Laptop has only landscape orientation
				rotate[ 0 ].setValue( "landscape" );
				rotate.attr( "disabled", "disabled" );
			}
		}

		index = rotate.val() === "landscape" ? 0 : 1;
		$( ".iframe-wrapper" ).css( { width: dimentions[ active ][ index ] } );

		if( $( "#preview" ).contents().length &&
			( wndw = $( "#preview" ).contents()[ 0 ].defaultView ) ) {
			diff = parseInt( dimentions[ active ][ index ] - wndw.innerWidth, 10 );
			$( ".iframe-wrapper" ).css( { width: "+=" + diff } );
		}

		$( "#preview" ).css( {
			height: $( "#preview" ).contents()
			        .find( "body" )
			        .height()
		} );
	}

	/**
	 * Context filter
	 * @return {void}
	 */
	function contextFilter() {
		var
			count = 0,
			strict = false,
			val = $( this ).find( "option:selected" )
			.text();

		// Disable filter
		if( ADK.isEmpty( $( this ).val() ) ) {
			$( ".filter-context" ).each( function eachRow() {
				$( this ).parents( "tr" )
				.show()
				.find( ".filter-number" )
				.text( ++count );
			} );

			return;
		}

		strict = val.indexOf( "-" ) > 0;

		$( ".filter-context" ).each( function iterateOverContextRecords() {
			var
				element = $( this ),
				i = 0,
				parts = [],
				records = [],
				tempParts = [],
				y = 0;

			if( !this.context_records || !this.context_parts ) {
				records = element.text().split( "," );

				for( i = 0; i < records.length; ++i ) {
					records[ i ] = records[ i ].replace( /^\s+|\s+$/, "" );

					tempParts = records[ i ].split( "-" );
					for( y = 0; y < tempParts.length; ++y ) {
						parts.push( tempParts[ y ].replace( /^\s+|\s+$/, "" ) );
					}
				}

				this.context_records = records;
				this.context_parts = parts;
			}

			if( strict ) {
				if ( $.inArray( val, this.context_records ) === -1 ) {
					element.parents( "tr" ).hide();

				} else {
					element.parents( "tr" ).show()
					.find( ".filter-number" )
					.text( ++count );
				}

			} else {
				if ( $.inArray( val, this.context_parts ) === -1 ) {
					element.parents( "tr" ).hide();

				} else {
					element.parents( "tr" ).show()
					.find( ".filter-number" )
					.text( ++count );
				}
			}
		} );
	}

	/**
	 * Adds new template
	 * @return {void}
	 */
	function addNewTemplateFunc() {
		var
			button = $( this ),
			data = {};

		data.name = $( "#new-template-name" ).val();
		data.file = $( "#new-template-file" ).val();
		data.func = $( "#new-template-function" ).val();
		data.hook = $( "#new-template-hook" ).val();
		data.sample = $( "#new-template-sample" ).val();
		data.callback = function cBack() {
			button.btnReset();
		};

		if( !data.name ) {
			ADK.n.alert( ADK.locale.templateNameMandatory );
			ADK.pulsate( $( "#new-template-name" ) );

			return;
		}

		if ( !data.hook && ( !data.file || !data.func ) ) {
			if ( data.file || data.func ) {
				if( !data.file ) {
					ADK.n.alert( ADK.locale.templateFileMandatory );
					ADK.pulsate( $( "#new-template-file" ) );

					return;
				}

				if( !data.func ) {
					ADK.n.alert( ADK.locale.templateFunctionMandatory );
					ADK.pulsate( $( "#new-template-function" ) );

					return;
				}
			}

			if( !data.hook ) {
				ADK.n.alert( ADK.locale.templateAnyField );

				ADK.pulsate(
					$( "#new-template-function" ),
					$( "#new-template-file" ),
					$( "#new-template-hook" )
				);

				return;
			}
		}

		button.btnActive();
		addNewTemplate( data );
	}

	/**
	 * Add template
	 * @return {void}
	 */
	function addNTemplate() {
		var
			$this = $( this ),
			data = {};

		data.name = $this.attr( "data-name" );
		data.hook = $this.attr( "data-hook" );
		data.sample = $this.attr( "data-sample" );
		data.callback = function cBack() {
			$this.btnReset();
		};
		data.callbackDone = function cBDone() {
			$this.attr( "disabled", "disabled" );
		};

		$this.btnActive();
		addNewTemplate( data );
	}

	/**
	 * Deletes template
	 * @return {void}
	 */
	function deleteTemplate() {
		var
			button = $( this ),
			data = {};

		data.id = $( "#templates" ).val();
		button.btnActive();

		$.post( button.attr( "data-url" ).replace( /&amp;/g, "&" ), data )

		.always( function addTemplateAlways() {
			button.btnReset();
		} )

		.done( function deteteTemplateDone( respStr ) {
			var resp = null;

			// If response is empty or doesn't contain JSON string
			if ( respStr ) {
				resp = ADK.checkResponse( respStr );

				if ( null === resp ) {
					return;
				}

				if ( resp.success ) {
					ADK.n.notification( resp.success );

					$( "#preview-template" )
						.find( "option[value=" + data.id + "]" )
							.remove();

					$( "#templates" )
						.find( "option[value=" + data.id + "]" )
							.remove()
						.end()
						.find( "option" )
							.eq( 0 )
							.attr( "selected", "selected" )
						.end()
					.end()
						.trigger( "change" );

				// We got error
				} else if ( resp.error ) {
					ADK.alert( resp.error );

				// Something went wrong
				} else {
					ADK.alert( ADK.locale.undefServerResp );
				}

			} else {
				console.error( resp );
				ADK.alert( ADK.locale.networkError );
			}
		} )

		.fail( function addTemplateFail() {
			ADK.alert( ADK.locale.networkError );
		} );
	}

	/**
	 * Align select on change event handler
	 * @return {void}
	 */
	function alignSelectChange() {
		var $this = $( this ),
			className = "",
			i = $this.parent()
				.find( ".input-group-addon i.fa" ),
			value = $this.val();

		if( $.inArray( value, [ "left", "center", "right" ] ) >= 0 ) {
			i[ 0 ].className = "fa fa-align-" + value;

		} else {
			if( "top" === value ) {
				className = "fa fa-rotate-90 fa-align-left";

			} else if( "bottom" === value ) {
				className = "fa fa-rotate-90 fa-align-right";

			} else {
				className = "fa fa-rotate-90 fa-align-justify";
			}

			i[ 0 ].className = className;
		}
	}

	/**
	 * Change color scheme picker even handler
	 * @param {object} e Event object
	 * @return {void}
	 */
	function pickScheme( e ) {
		var
			$this = $( this ),
			container = null;

		e.preventDefault();
		container = $this.parents( ".color-scheme-picker" );
		container[ 0 ].colorScheme = $this.text();
		setInvoiceColorScheme.call( container );
	}

	/**
	 * Sorts history table's entries
	 * @return {void}
	 */
	function historySort() {
		var
			$this = $( this );

		$this.parents( "[data-url]" ).find( ".table-sort" )
			.removeClass( "active-sort" );

		$this.addClass( "active-sort" );

		if ( $this.hasClass( "fa-sort-amount-asc" ) ) {
			$this.removeClass( "fa-sort-amount-asc" ).addClass( "fa-sort-amount-desc" );

		} else {
			$this.removeClass( "fa-sort-amount-desc" ).addClass( "fa-sort-amount-asc" );
		}

		fetchTableContents.call( $this.parents( "table" ).parents( "[data-url]" )[ 0 ] );
	}

	/**
	 * Paginates over table's content
	 * @param {object} e Event object
	 * @return {void}
	 */
	function paginate( e ) {
		var
			$container = $( this ).parents( ".table-overall" )
				.find( ".adk-table" )
				.parents( "[data-url]" );

		e.preventDefault();

		$container.find( ".pagination li" ).removeClass( "active" );
		$( this ).parent()
			.addClass( "active" );

		fetchTableContents.call( $container[ 0 ] );
	}

	/**
	 * Shows email log
	 * @return {void}
	 */
	function showLog() {
		var $this = $( this );

		if( $this.attr( "data-log" ) ) {
			ADK.alert(
				"<div style='margin-bottom: 5px; height: 40px; position: relative;'>" +
					"<div style='display: inline-block; position: absolute; right: 0'>" +
						"<a href='" + ADK.locale.saveLogUrl + "&log_id=" +
							$this.parents( "tr" ).attr( "data-id" ) + "'>" +
							"<button type='button' class='btn btn-primary save-history-log' " +
								"data-i='fa-save' " +
								"title='" + ADK.locale.save + "'>" +
								"<i class='fa fa-save'></i>" +
							"</button>" +
						"</a>" +
					"</div>" +
				"</div>" +
				"<pre>" + $this.attr( "data-log" ) + "</pre>",
				ADK.locale.logCaption,
				"modal-lg"
			);
		}
	}

	/**
	 * Shows pop-up window insert custom date period to
	 * @return {void}
	 */
	function filterDate() {
		var
			datePickers = null,
			self = $( this );

		if ( "custom" === $( this ).val() ) {
			datePickers =
			"<div class='row'>" +
				"<div class='col-md-6'>" +
					"<div class='form-group'>" +
						"<div class='input-group date' id='datetimepicker1'>" +
							"<input type='text' class='form-control' />" +
							"<span class='input-group-addon'>" +
								"<span class='glyphicon glyphicon-calendar'></span>" +
							"</span>" +
						"</div>" +
					"</div>" +
				"</div>" +
				"<div class='col-md-6'>" +
					"<div class='form-group'>" +
						"<div class='input-group date' id='datetimepicker2'>" +
							"<input type='text' class='form-control' />" +
							"<span class='input-group-addon'>" +
								"<span class='glyphicon glyphicon-calendar'></span>" +
							"</span>" +
						"</div>" +
					"</div>" +
				"</div>" +
			"</div>";

			ADK.alert( datePickers, ADK.locale.pickPeriod, "modal-md" )

			// Modal shown callback handler
			.shown( function onAlerShow() {
				var options = {
					icons: {
						time: "fa fa-clock-o",
						date: "fa fa-calendar",
						up:   "fa fa-chevron-up",
						down: "fa fa-chevron-down"
					},
					format: "YYYY-MM-DD"
				};

				$( "#datetimepicker1" ).datetimepicker( options );
				$( "#datetimepicker2" ).datetimepicker( options );

				$( "#datetimepicker1" ).on( "dp.change", function firstPickerChange( e ) {
					$( "#datetimepicker2" ).data( "DateTimePicker" )
						.setMinDate( e.date );

					$( this ).data( "DateTimePicker" )
						.hide();
				} );

				$( "#datetimepicker2" ).on( "dp.change", function secondPicherChange( e ) {
					$( "#datetimepicker1" ).data( "DateTimePicker" )
						.setMaxDate( e.date );

					$( this ).data( "DateTimePicker" )
						.hide();
				} );

			} )

			// Modal on hide callback handler
			.hide( function anAlertHide() {
				var
					contains = false,
					dateString = "",
					dateVal = "",
					val1 = $( "#datetimepicker1 input" ).val(),
					val2 = $( "#datetimepicker2 input" ).val();

				if ( !val1 || !val2 ) {
					return;
				}

				dateString = ADK.locale.from + ": " + val1 + " " + ADK.locale.to + ": " + val2;
				dateVal = val1 + " " + val2;

				self.find( "option").each( function eachOption() {
					if( this.value === dateVal ) {
						contains = true;

						return false;
					}

					return true;
				} );

				if ( !contains ) {
					self.append( "<option value='" + dateVal + "'>" + dateString + "</option>" );
				}

				self.val( dateVal );
			} );
		}
	}

	/**
	 * Adds templates for order status emails
	 * @return {void}
	 */
	function addStatusOrder() {
		var
			$button = $( this ),
			$select = $( "#missed-order-templates-list" ),
			data = {},
			templates_list = $select.val();

		if ( !templates_list ) {
			ADK.n.alert( ADK.locale.emtyStatusTemplatesList );

			return;
		}

		$button.btnActive();

		data.statuses = templates_list;
		data.callback = function addOrderStatusCallback() {
			$button.btnReset();
		};
		data.callbackDone = function callbackDone() {
			$select.find( "option" ).each( function eachOption() {
				if ( $.inArray( $( this ).attr( "value" ), templates_list ) >= 0 ) {
					$( this ).remove();
				}
			} );

			$select.trigger( "change" );
		};

		addNewTemplate( data );
	}

	/**
	 * Adds templates for order status emails
	 * @return {void}
	 */
	function addStatusReturn() {
		var
			$button = $( this ),
			$select = $( "#missed-return-templates-list" ),
			data = {},
			templates_list = $select.val();

		if ( !templates_list ) {
			ADK.n.alert( ADK.locale.emtyStatusTemplatesList );

			return;
		}

		$button.btnActive();

		data.returns = templates_list;
		data.callback = function addOrderReturnCallback() {
			$button.btnReset();
		};
		data.callbackDone = function callbackDone() {
			$select.find( "option" ).each( function eachOption() {
				if ( $.inArray( $( this ).attr( "value" ), templates_list ) >= 0 ) {
					$( this ).remove();
				}
			} );

			$select.trigger( "change" );
		};

		addNewTemplate( data );
	}

	/**
	 * Initializes newsletter's controls tab
	 * @return {void}
	 */
	function initNewsletterContrlsTab() {
		var $container = $( "#newsletter-controls" );

		$container.find( ".fancy-checkbox" )
			.fancyCheckbox();

		$container.find( ".select2" ).select2( {
			width: "100%"
		} );

		filter_autofill_init( $container.find( ".table-filter-autofill" ) );

		$( "#newsletter-chart-period" ).val( "week" );
		newsletterChart.call( document.getElementById( "newsletter-chart-reload" ), true );

		$( ".csv-item-wrapper" ).sortable( {
			axis: false,
			stop: function onCh() {
				filterSubscribers();
			}
		} );

		$( ".csv-item-wrapper" ).disableSelection();

		$( "#fileupload" ).fileupload( {
			autoUpload: true,
			url:        ADK.locale.importCsvUrl.replace( /&amp;/, "&" ),
			formData:   function d() {
				return {
					1: { name: "email",      value: $( "#import-csv-email" ).val() },
					2: { name: "fullname",   value: $( "#import-csv-fullname" ).val() },
					3: { name: "firstname",  value: $( "#import-csv-firstname" ).val() },
					4: { name: "lastname",   value: $( "#import-csv-lastname" ).val() },
					5: { name: "override",   value: $( "#import-csv-override" ).val() },
					6: { name: "newsletter", value: $( "#select-newsletter" ).val() }
				};
			},
			done: function done( e, data ) {
				var resp = data.jqXHR.responseJSON;

				if ( resp.error ) {
					ADK.n.alert( resp.error );

				} else if ( resp.success ) {
					ADK.n.notification( resp.success );
					ADK.e.trigger( "subscriber.change" );
				}
			},
			fail: function fail() {
				ADK.n.alert( ADK.locale.scriptError );
			},
			always: function always( e ) {
				$( e.target ).btnReset();
			}
		} );
	}

	/**
	 * Update newsletter click handler
	 * @return {void}
	 */
	function updateNews() {
		var data = {};

		data.id = $( "#select-newsletter" ).val();

		if ( !data.id || -1 === parseInt( data.id, 10 ) ) {
			return;
		}

		$( this )
		.parents( ".newsletter-controls-wrapper" )
		.find( ".newsletter-control" )
		.each( function ec() {
			var $this = $( this );

			if ( $this.attr( "data-name") ) {
				data[ $this.attr( "data-name" ) ] = $this.val();
			}
		} );

		updateNewsletter.call( this, data );
	}

	/**
	 * Add subscriber button click handler
	 * @return {void}
	 */
	function addSubscriberClick() {
		var
			data = {},
			id = $( "#select-newsletter" ).val();

		if ( -1 === parseInt( id, 10 ) ) {
			return;
		}

		data.name = $( "#add-subscriber-name" ).val();
		data.email = $( "#add-subscriber-email" ).val();
		data.newsletter = id;

		// if ( !data.name ) {
		// 	ADK.n.alert( ADK.locale.subscriberNameNeeded );
		// 	ADK.pulsate( $( "#add-subscriber-name" ) );

		// 	return;
		// }

		if ( !data.email ) {
			ADK.n.alert( ADK.locale.subscriberEmailNeeded );
			ADK.pulsate( $( "#add-subscriber-email" ) );

			return;
		}

		addSubscriber.call( this, data );
	}

	/**
	 * Pick widget's color scheme handler
	 * @param {object} e Event object
	 * @return {void}
	 */
	function pickSchemeWidget( e ) {
		var
			$this = $( this ),
			container = null;

		e.preventDefault();
		container = $this.parents( ".color-scheme-picker" );
		container[ 0 ].colorScheme = $this.text();
		setWidgetColorScheme.call( container );
	}

	/**
	 * Widget save callback
	 * @param {object} data Widget's data
	 * @return {void}
	 */
	function widgetOnSave( data ) {
		var exists = false;

		$( "#widget-select option" ).each( function e() {
			if ( $( this ).attr( "value" ) === data.id ) {
				exists = true;

				return false;
			}

			return null;
		} );

		if ( !exists ) {
			$( "#widget-select" )
				.append( $( "<option value='" + data.id + "'>" + data.name + "</option>" ) );
		}

		$( "#widget-select" ).val( data.id );
		$( "#widget-code" ).val( data.code );
		$( "#widget-module-id" ).val( data.module );
	}

	/**
	 * Widget's data fetch callback
	 * @param {object} data Widget's data
	 * @return {void}
	 */
	function widgetOnFetch( data ) {
		$.each( JSON.parse( data ), function eachData( i, v ) {
			var
				$element = $( ".widget-controls [data-name='" + i + "']"),
				parts = null;

			if ( $element.length ) {
				if ( $element.hasClass( "dimension-wrapper" ) ) {
					parts = v.match( /(\d+)(.+)/ );

					if ( parts ) {
						$element.find( "button" ).val( parts[ 2 ] );
						$element.find( "input" ).val( parts[ 1 ] )
							.trigger( "change" );
					}

				} else {
					$element.val( v ).trigger( "change" );
				}
			}
		} );
	}

	/**
	 * Widget delete callback
	 * @param {int} id Widget's ID
	 * @return {void}
	 */
	function widgetOnDelete( id ) {
		$( "#widget-select option" ).each( function eachOption() {
			if ( $( this ).attr( "value" ) === id ) {
				$( this ).remove();
			}
		} );

		ADK.e.trigger(
			"widget.fetch.end",
			ADK.htmlSpecialcharsDecode( $( "#widget-select" ).attr( "data-defaults") )
		);

		// Initialize subscription widget color scheme
		$( "#widget-color-scheme input" ).val( "#d26464" );
		$( "#widget-color-scheme a:nth(1)" ).trigger( "click" );
	}

	/**
	 * Save caption click handler
	 * @return {void}
	 */
	function saveCaptions() {

		// Clicked button
		var
			$this = $( this ),
			data = {};

		data = $this.parents( ".tab-pane" )
			.eq( 0 )
			.getFormData();

		$this.btnActive();

		$.post( $this.attr( "data-url" ).replace( /&amp;/g, "&" ), data )

		.always( function saveCaptionAlways() {
			$this.btnReset();
		} )

		.done( function saveCaptionDone( respStr ) {
			var resp = null;

			// If response is empty or doesn't contain JSON string
			if ( respStr ) {
				resp = ADK.checkResponse( respStr );

				if ( null === resp ) {
					return;
				}

				if ( resp.success ) {
					ADK.n.notification( resp.success );

				} else if ( resp.error ) {
					ADK.n.alert( resp.error );

				} else {
					ADK.alert( ADK.locale.networkError );
				}

			} else {
				ADK.n.alert( ADK.locale.serverError );
			}
		} )

		.fail( function saveCaptionFail() {
			ADK.alert( ADK.locale.networkError );
		} );
	}

	/**
	 * Imports subscribers
	 * @return {void}
	 */
	function inportSubscribers() {

		// Clicked button
		var
			$this = $( this ),
			data = {};

		data.source = $( "#import-subscribers-source" ).val();
		data.subscribers = $( "#import-subscribers-subscribers" ).val();
		data.override = $( "#import-subscribers-override" ).val();
		data.target = $this.attr( "data-target" );

		if ( -1 === parseInt( data.source, 10 ) ) {
			ADK.n.alert( ADK.locale.needToFillIn );
			ADK.pulsate( $( "#import-subscribers-source" ) );

			return;
		}

		if ( !data.subscribers ) {
			ADK.n.alert( ADK.locale.subscribersNeeded );
			ADK.pulsate( $( "#import-subscribers-subscribers" ) );

			return;
		}

		$this.btnActive();

		$.post( $this.attr( "data-url" ).replace( /&amp;/g, "&" ), data )

		.always( function importSubscribersAlways() {
			$this.btnReset();
		} )

		.done( function importSubscribersDone( respStr ) {
			var resp = null;

			// If response is empty or doesn't contain JSON string
			if ( respStr ) {
				resp = ADK.checkResponse( respStr );

				if ( null === resp ) {
					return;
				}

				if ( resp.success ) {
					ADK.n.notification( resp.success );
					ADK.e.trigger( "subscriber.add.end" );

				} else if ( resp.error ) {
					ADK.n.alert( resp.error );

				} else {
					ADK.alert( ADK.locale.networkError );
				}

			} else {
				ADK.n.alert( ADK.locale.serverError );
			}
		} )

		.fail( function importSubscribersFail() {
			ADK.alert( ADK.locale.networkError );
		} );
	}

	/**
	 * Filters subscribers
	 * @return {void}
	 */
	function filterSubscribers() {
		var
			$link = $( "#export-subscribers-link" ),
			href = $link.attr( "href" ),
			sort = $( ".csv-item-wrapper input" ),
			sortText = [],
			v = $( "#export-subscribers-filter" ).val();

		if ( null !== v && v.length ) {
			href = href.replace( /filter=[^&]+/, "filter=" + v.join( "," ) );

		} else {
			href = href.replace( /filter=[^&]+/, "filter=100" );
		}

		$.each( sort, function es() {
			if ( $( this ).is( ":checked" ) ) {
				sortText.push( $( this ).attr( "data-id" ) );
			}
		} );

		if ( href.indexOf( "sort" ) === -1 ) {
			href += "&sort=";
		}

		href = href.replace( /sort=[^&]*/, "sort=" + sortText.join( "," ) );

		$link.attr( "href", href );
	}

	/**
	 * Sets throttle settings
	 * @return {void}
	 */
	function setThrottle() {
		var
			$button = null,
			$input = null,
			$parent = null,
			$this = $( this ),
			value = null;

		$parent = $this.parents( ".form-group" );
		$button = $parent.find( "button" ).not( $this );
		$input = $parent.find( "input" );
		value = ADK.isEmpty( $button.val() ) ? 0 : parseInt( $input.val(), 10 );

		setSetting( {
			name:  this.id,
			value: value
		}, $this );
	}

	/**
	 * Saves "simple" setting
	 * @return {void}
	 */
	function setSimpleSetting() {
		var
			$element = $( this ).parents( ".form-group" )
				.find( "input" ),
			name = $element.attr( "data-name" );

		if ( !name ) {
			console.error( "Attribute data-name is missing" );
			ADK.n.alert( ADK.locale.scriptError );

			return;
		}

		if ( $element.attr( "data-required" ) && !$element.val() ) {
			ADK.n.alert( ADK.locale.needToFillIn );
			ADK.pulsate( $element );
			ADK.scrollTo( $element );

			return;
		}

		setSetting( {
			name:  name,
			value: $element.val()
		}, $( this ) );
	}

	/**
	 * Initializes subscriber's chard data
	 * @param {object} data Chart data
	 * @return {void}
	 */
	function initSubscribersChart( data ) {
		var
			colors = {
				"sent":         "#3f60bd",
				"viewed":       "#7aae50",
				"visited":      "#ded133",
				"failed":       "#ccc",
				"active":       "#c55d97",
				"cancel":       "#f00",
				"verification": "#97b6c1"
			},
			dataSets = [],
			emailData = {
				type: "doughnut",
				data: {
					labels:   [],
					datasets: [ {
						data:                 [],
						backgroundColor:      [],
						hoverBackgroundColor: []
					} ]
				}
			},
			subscribersData = {
				type: "doughnut",
				data: {
					labels:   [],
					datasets: [ {
						data:                 [],
						backgroundColor:      [],
						hoverBackgroundColor: []
					} ]
				}
			};

		if ( !data.data || !data.data.length ) {
			console.error( "Invalid response format of chart's data", data.data );

			return;
		}

		$.each( data.data, function eachChartData() {

			dataSets.push( {
				label:                  this.label,
				data:                   this.data,
				labels:                 this.labels,
				fill:                   false,
				cubicInterpolationMode: "default",
				lineTension:            0,
				spanGaps:               true,
				backgroundColor:        colors[ this.id ],
				borderColor:            colors[ this.id ]
			} );
		} );

		ADK.myChart = new Chart( "newsletter-chart", {
			type: "line",
			data: {
				datasets: dataSets
			},
			options: {
				scales: {
					xAxes: [ {
						type:     "time",
						position: "bottom"
					} ]
				}
			}
		} );

		if ( data.summary ) {

			$.each( data.summary, function eachSummary() {
				var data = {
					label:                  this.label,
					data:                   this.data,
					labels:                 this.labels,
					fill:                   false,
					cubicInterpolationMode: "default",
					lineTension:            0,
					spanGaps:               true,
					backgroundColor:        colors[ this.id ],
					borderColor:            colors[ this.id ]
				};

				if ( $.inArray( this.id, [ "sent", "failed", "viewed", "visited" ] ) !== -1 ) {
					emailData.data.labels.push( this.label );
					emailData.data.datasets[ 0 ].data.push( this.data );
					emailData.data.datasets[ 0 ].backgroundColor.push( colors[ this.id ] );
					emailData.data.datasets[ 0 ].hoverBackgroundColor.push( colors[ this.id ] );

				} else if ( $.inArray( this.id, [ "active", "cancel", "verification" ] ) !== -1 ) {
					subscribersData.data.labels.push( this.label );
					subscribersData.data.datasets[ 0 ].data.push( this.data );
					subscribersData.data.datasets[ 0 ].backgroundColor.push( colors[ this.id ] );
				}
			} );

			ADK.emailChart = new Chart( "newsletter-summary-email", emailData );
			ADK.subscribersChart = new Chart( "newsletter-summary-subscription", subscribersData );
		}
	}

	/**
	 * Fetches chart's data
	 * @return {void}
	 */
	function fetchChartData() {

		// Import OC subscribers filter - customer
		$( "#filter-oc-customers" ).autocomplete( {
			source: function sour( request, response ) {
				$.ajax( {
					url: $( this ).attr( "data-url" )
						.replace( /&amp;/, "&" ) +
						"&filter_name=" + encodeURIComponent( request ),
					dataType: "json",
					success:  function ok(json) {
						response( $.map( json, function map( item ) {
							return {
								label: item.name,
								value: item.customer_id
							};
						} ) );
					}
				} );
			},
			select: function s( item ) {
				$( "#filter-oc-customers" ).val( "" );
				$( "#filter-oc-customer" + item.value ).remove();

				$( "#filter-oc-aux" )
				.append(
					"<div id=\"filter-oc-customer" + item.value + "\">" +
						"<i class=\"fa fa-minus-circle\"></i> " +
						item.label +
						"<input type=\"hidden\" name=\"customer[]\" value=\"" +
							item.value + "\" class=\"filter-oc\" />" +
					"</div>"
				);
			}
		} );

		// Import OC subscribers filter - affiliate
		$( "#filter-oc-affiliates" ).autocomplete( {
			source: function sour( request, response ) {
				$.ajax( {
					url: $( this ).attr( "data-url" )
						.replace( /&amp;/, "&" ) +
						"&filter_name=" + encodeURIComponent( request ),
					dataType: "json",
					success:  function ok(json) {
						response( $.map( json, function map( item ) {
							return {
								label: item.name,
								value: item.id
							};
						} ) );
					}
				} );
			},
			select: function s( item ) {
				$( "#filter-oc-affiliates" ).val( "" );
				$( "#filter-oc-affiliate" + item.value ).remove();

				$( "#filter-oc-aux" )
				.append(
					"<div id=\"filter-oc-affiliate" + item.value + "\">" +
						"<i class=\"fa fa-minus-circle\"></i> " +
						item.label +
						"<input type=\"hidden\" name=\"affiliate[]\" value=\"" +
							item.value + "\" class=\"filter-oc\" />" +
					"</div>"
				);
			}
		} );

		// Import OC subscribers filter - product
		$( "#filter-oc-products" ).autocomplete( {
			source: function sour( request, response ) {
				$.ajax( {
					url: $( this ).attr( "data-url" )
						.replace( /&amp;/, "&" ) +
						"&filter_name=" + encodeURIComponent( request ),
					dataType: "json",
					success:  function ok(json) {
						response( $.map( json, function map( item ) {
							return {
								label: item.name,
								value: item.product_id
							};
						} ) );
					}
				} );
			},
			select: function s( item ) {
				$( "#filter-oc-products" ).val( "" );
				$( "#filter-oc-product" + item.value ).remove();

				$( "#filter-oc-aux" )
				.append(
					"<div id=\"filter-oc-product" + item.value + "\">" +
						"<i class=\"fa fa-minus-circle\"></i> " +
						item.label +
						"<input type=\"hidden\" name=\"product[]\" value=\"" +
							item.value + "\" class=\"filter-oc\" />" +
					"</div>"
				);
			}
		} );

		$( "#filter-oc-aux" ).delegate( ".fa-minus-circle", "click", function clickWell() {
			$( this ).parent()
				.remove();
		} );

		$( "#import-oc-subscribers-select" ).on( "change", selectFilter );
		$( "#import-oc-subscribers-select" ).trigger( "change" );
		$( "#import-oc-subscribers" ).on( "click", importOc );
	}

	/**
	 * Select filter callback
	 * @return {void}
	 */
	function selectFilter() {
		var
			$this = $( this ),
			val = $this.val();

		$( ".filter-oc-wrapper" ).hide();

		if ( $( this ).val() ) {
			$.each( $( this ).val(), function eachVal() {
				$( ".wrapper-" + this ).show();
			} );
		}

		if ( null === val ) {
			$( "#filter-oc-aux" ).empty();

		} else {
			if ( $.inArray( "customer", val ) === -1 ) {
				$( "#filter-oc-aux [id^=filter-oc-customer]" ).remove();
			}

			if ( $.inArray( "affiliate", val ) === -1 ) {
				$( "#filter-oc-aux [id^=filter-oc-affiliate]" ).remove();
			}

			if ( $.inArray( "product", val ) === -1 ) {
				$( "#filter-oc-aux [id^=filter-oc-product]" ).remove();
			}
		}
	}

	/**
	 * Imports OC subscribers
	 * @return {void}
	 */
	function importOc() {
		var
			$this = $(this ),
			data = {};

		data = $( "#import-oc" ).getFormData();
		data.id = $( "#select-newsletter" ).val();

		$this.btnActive();

		$.post( $this.attr( "data-url" ).replace( /&amp;/g, "&" ), data )

		.always( function newsletterChartAlways() {
			$this.btnReset();
		} )

		.done( function newsletterChartDone( respStr ) {
			var resp = null;

			// If response is empty or doesn't contain JSON string
			if ( respStr ) {
				resp = ADK.checkResponse( respStr );

				if ( null === resp ) {
					return;
				}

				if ( resp.success ) {
					ADK.e.trigger( "subscriber.add.end" );
					ADK.n.notification( resp.success );

				} else if ( resp.error ) {
					ADK.n.alert( resp.error );

				} else {
					ADK.n.alert( ADK.locale.networkError );
				}

			} else {
				ADK.n.alert( ADK.locale.serverError );
			}
		} )

		.fail( function newsleterChartFail() {
			ADK.n.alert( ADK.locale.networkError );
		} );
	}

	/**
	 * Profile color scheme change handler
	 * @param {object} e Event object
	 * @return {void}
	 */
	function pickSchemeProfile( e ) {
		var
			$this = $( this ),
			container = null;

		e.preventDefault();
		container = $this.parents( ".color-scheme-picker" );
		container[ 0 ].colorScheme = $this.text();
		setProfileColorScheme.call( container );
	}

	/**
	 * Resets filter
	 * @return {void}
	 */
	function resetFilter() {
		var $container = $( this )
			.parents( ".table-overall" )
			.find( "table" )
			.parents( "[data-url]" );

		$container
			.parents( ".tab-pane" )
				.find( ".table-filter" )
				.val( null )
				.trigger( "change" );

		fetchTableContents.call( $container[ 0 ], { page: 1 } );
	}

	/**
	 * Selects newsletter and activates tab by click on newsletter row in newsletter list table
	 * @param {object} e Event
	 * @return {void}
	 */
	function chooseNewsletter( e ) {
		if ( e.target.type === "checkbox" ) {
			return;
		}

		$( "#select-newsletter" )
			.val( $( this ).attr( "data-id" ) )
			.trigger( "change" );

		$( "a[href=#newsletter-manage-pane]" ).trigger( "click" );
	}

	/**
	 * Runs queue
	 * @return {void}
	 */
	function runQueue() {
		var
			$this = $(this );

		$this.btnActive();

		$.post( $this.attr( "data-url" ).replace( /&amp;/g, "&" ) )

		.always( function queueRunAlways() {
			$this.btnReset();
		} )

		.done( function queueRunDone( respStr ) {
			var resp = null;

			// If response is empty or doesn't contain JSON string
			if ( respStr ) {
				resp = ADK.checkResponse( respStr );

				if ( null === resp ) {
					return;
				}

				if ( resp.success ) {
					ADK.n.notification( resp.success );

				} else if ( resp.error ) {
					ADK.n.alert( resp.error );

				} else {
					ADK.n.alert( ADK.locale.networkError );
				}

			} else {
				ADK.n.alert( ADK.locale.serverError );
			}
		} )

		.fail( function queueRunFail() {
			ADK.n.alert( ADK.locale.networkError );
		} );
	}

	/**
	 * Flashes the queue
	 * @return {void}
	 */
	function flushQueue() {
		var
			$this = $(this );

		$this.btnActive();

		$.post( $this.attr( "data-url" ).replace( /&amp;/g, "&" ) )

		.always( function queueFlushAlways() {
			$this.btnReset();
		} )

		.done( function queueFlushDone( respStr ) {
			var resp = null;

			// If response is empty or doesn't contain JSON string
			if ( respStr ) {
				resp = ADK.checkResponse( respStr );

				if ( null === resp ) {
					return;
				}

				if ( resp.success ) {
					ADK.e.trigger( "queue.flush" );
					ADK.n.notification( resp.success );

				} else if ( resp.error ) {
					ADK.n.alert( resp.error );

				} else {
					ADK.n.alert( ADK.locale.networkError );
				}

			} else {
				ADK.n.alert( ADK.locale.serverError );
			}
		} )

		.fail( function queueFlushFail() {
			ADK.n.alert( ADK.locale.networkError );
		} );
	}

	/**
	 * Queue flash callback
	 * @return {void}
	 */
	function onQueueFlush() {
		$( "#queue-length-text" ).text( "0" );
	}

	/**
	 * Change setting callback
	 * @param {object} data Setting's data
	 * @return {void}
	 */
	function onSettingChange( data ) {

		// Auto-update setting
		if ( "auto_update" === data.name ) {
			ADK.locale.autoUpdate = data.value;
		}
	}

	/**
	 * Subscribers manager function
	 * @return {void}
	 */
	function manageSubscription() {
		var
			$this = $(this );

		$this.btnActive();

		$.post( $this.attr( "data-url" ).replace( /&amp;/g, "&" ), {
			id:     $this.attr( "data-id" ),
			action: $this.attr( "data-action" )
		} )

		.always( function manageSubscriptionAlways() {
			$this.btnReset();
		} )

		.done( function manageSubscriptionDone( respStr ) {
			var resp = null;

			// If response is empty or doesn't contain JSON string
			if ( respStr ) {
				resp = ADK.checkResponse( respStr );

				if ( null === resp ) {
					return;
				}

				if ( resp.success ) {
					ADK.n.notification( resp.success );
					ADK.e.trigger( "subscriber.change" );

				} else if ( resp.error ) {
					ADK.n.alert( resp.error );

				} else {
					ADK.n.alert( ADK.locale.networkError );
				}

			} else {
				ADK.n.alert( ADK.locale.serverError );
			}
		} )

		.fail( function manageSubscriptionhFail() {
			ADK.n.alert( ADK.locale.networkError );
		} );
	}

	/**
	 * Update subscribers list callback
	 * @return {void}
	 */
	function updateSubscribersList() {
		$( "#refresh-subscribers" ).trigger( "click" );
	}

	/**
	 * Save IMAP configurations callback
	 * @return {void}
	 */
	function saveImap() {
		var
			$this = $(this );

		$this.btnActive();

		$.post( $this.attr( "data-url" ).replace( /&amp;/g, "&" ), {
			url:       $( "#imap-url" ).val(),
			port:      $( "#imap-port" ).val(),
			ssl:       $( "#imap-ssl" ).val(),
			blacklist: $( "#imap-blacklist" ).val(),
			unseen:    $( "#imap-unseen" ).val(),
			action:    $( "#imap-action" ).val(),
			login:     $( "#imap-login" ).val(),
			password:  $( "#imap-password" ).val()
		} )

		.always( function saveImapAlways() {
			$this.btnReset();
		} )

		.done( function saveImapDone( respStr ) {
			var resp = null;

			// If response is empty or doesn't contain JSON string
			if ( respStr ) {
				resp = ADK.checkResponse( respStr );

				if ( null === resp ) {
					return;
				}

				if ( resp.success ) {
					ADK.n.notification( resp.success );

				} else if ( resp.error ) {
					ADK.n.alert( resp.error );

				} else {
					ADK.n.alert( ADK.locale.networkError );
				}

			} else {
				ADK.n.alert( ADK.locale.serverError );
			}
		} )

		.fail( function saveImapFail() {
			ADK.n.alert( ADK.locale.networkError );
		} );
	}

	/**
	 * Test IMAP configuration callback
	 * @return {void}
	 */
	function checkImap() {
		var
			$this = $(this );

		$this.btnActive();

		$.post( $this.attr( "data-url" ).replace( /&amp;/g, "&" ), {
			url:      $( "#imap-url" ).val(),
			port:     $( "#imap-port" ).val(),
			ssl:      $( "#imap-ssl" ).val(),
			login:    $( "#imap-login" ).val(),
			password: $( "#imap-password" ).val()
		} )
		.always( function testImapAlways() {
			$this.btnReset();
		} )

		.done( function testImapDone( respStr ) {
			var resp = null;

			// If response is empty or doesn't contain JSON string
			if ( respStr ) {
				resp = ADK.checkResponse( respStr );

				if ( null === resp ) {
					return;
				}

				if ( resp.success ) {
					ADK.n.notification( resp.success );

				} else if ( resp.error ) {
					ADK.n.alert( resp.error );

				} else {
					ADK.n.alert( ADK.locale.networkError );
				}

			} else {
				ADK.n.alert( ADK.locale.serverError );
			}
		} )

		.fail( function testImapFail() {
			ADK.n.alert( ADK.locale.networkError );
		} );
	}

	/**
	 * Show/hide IMAP password callback
	 * @return {void}
	 */
	function showImapPassword() {
		var $i = $( this ).find("i" );

		if ( $i.hasClass( "fa-eye" ) ) {
			$i
				.removeClass( "fa-eye" )
				.addClass( "fa-eye-slash" );

			$i.parents( ".input-group" )
				.find( "input" )
				.attr( "type", "password" );

		} else {
			$i
				.removeClass( "fa-eye-slash" )
				.addClass( "fa-eye" );

			$i.parents( ".input-group" )
				.find( "input" )
				.attr( "type", "text" );
		}
	}

	/**
	 * Test IMAP configuration callback
	 * @return {void}
	 */
	function doBlacklist() {
		var
			$this = $(this );

		$this.btnActive();

		$.post( $this.attr( "data-url" ).replace( /&amp;/g, "&" ), {} )
		.always( function doBlacklistAlways() {
			$this.btnReset();
		} )

		.done( function doBlacklistDone( respStr ) {
			var resp = null;

			// If response is empty or doesn't contain JSON string
			if ( respStr ) {
				resp = ADK.checkResponse( respStr );

				if ( null === resp ) {
					return;
				}

				if ( resp.success ) {
					ADK.n.notification( resp.success );

				} else if ( resp.error ) {
					ADK.n.alert( resp.error );

				} else {
					ADK.n.alert( ADK.locale.networkError );
				}

			} else {
				ADK.n.alert( ADK.locale.serverError );
			}
		} )

		.fail( function doBlacklistFail() {
			ADK.n.alert( ADK.locale.networkError );
		} );
	}

	/**
	 * Shows/hides specific fields of a table
	 * @return {void}
	 */
	function hideTableColumn() {
		var
			$this = $( this ),
			$parent = $this.parents( ".btn-group" ),
			data = {},
			values = [];

		$( this ).toggleClass( "btn-default" );
		data.name = $parent.attr( "data-name");

		$parent.find( "button" ).each( function e() {
			if ( !$( this ).hasClass( "btn-default" ) ) {
				values.push( $( this ).attr( "data-value" ) );
			}
		} );

		data.value = values.join( "," );
		setSetting(
			data,
			$this,
			fetchTableContents.bind(
				$this
					.parents( ".table-overall" )
						.find( "table" )
							.parents( "[data-url]" )
							.eq( 0 )[ 0 ]
			)
		);
	}

} )( jQuery );
