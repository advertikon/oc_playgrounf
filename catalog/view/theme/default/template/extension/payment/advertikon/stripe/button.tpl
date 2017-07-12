<?php echo $button; ?>
<?php if( $a->config( 'describe_price' ) ) : ?>
<div id="adk-describe" style="cursor: pointer;">* <?php echo $a->__( 'Details' ); ?>
	<i class="fa fa-caret-down"></i>
</div>

<style>
	#adk-details {
		border-collapse: collapse;
		border: solid 1px black;
		display: none;
		margin-bottom: <?php echo $margin_bottom; ?>;
	}
	#adk-details tr,
	#adk-details td {
		border: solid 1px black;
	}
	#adk-details td {
		padding: 5px;
	}
</style>

<div id="adk-details">
	<table style="width: 100%">
		<?php foreach( $totals as $total ) : ?>
		<tr>
			<td><?php echo $total['title']; ?></td>
			<td><?php echo $a->currency->format( $total['value'], $a->session->data['currency'] ); ?></td>
		</tr>
		<?php endforeach; ?>
	</table>
</div>
<?php endif; ?><!-- describe price -->

<script>
	ADK.locale = $.extend( ADK.locale, <?php echo $locale; ?> );
	var checkoutScript = document.createElement( "script" );

	$( "#adk-describe" ).on( "click", function(){
		if( $( "#adk-details" ).is( ":visible" ) ) {
			$( "#adk-details" ).slideUp();
			$( this ).find( "i" ).removeClass( "fa-caret-up" ).addClass( "fa-caret-down" );

		} else {
			$( "#adk-details" ).slideDown();
			$( this ).find( "i" ).removeClass( "fa-caret-down" ).addClass( "fa-caret-up" );
		}
	} );

	checkoutScript.src = "https://checkout.stripe.com/checkout.js";
	document.documentElement.appendChild( checkoutScript );
	checkoutScript.onload = stripeCheckout;

	/**
	 * Stripe checkout on-load callback
	 * @returns void
	 */
	function stripeCheckout() {

		var handler = StripeCheckout.configure( {
			key:    ADK.locale.publicKey,
			image:  ADK.locale.image,
			locale: "auto",
			token:   function( token, args  ) {
				pay( {
					token:      token,
					args:       args,
					product_id: ADK.locale.productId
				} );
			},
			name:            ADK.locale.name,
			description:     ADK.locale.description,
			zipCode:         ADK.locale.zipCode,
			billingAddress:  true,
			shippingAddress: true,
			currency:        ADK.locale.currency,
			panelLabel:      ADK.locale.label,
			email:           ADK.locale.email,
			allowRememberMe: ADK.locale.rememberMe,
			bitcoin:         ADK.locale.bitcoin,
			alipay:          ADK.locale.alipay
		} );

		$( "#adk-stripe-button" ).removeAttr( "disabled" );

		$( "#adk-stripe-button" ).on( "click", function( e ) {
			e.preventDefault();

			handler.open( {
				amount: ADK.locale.amount 
			} );
		} );
	}

	$( window ).on( "popstate", function() {
		handler.close();
	} );

	function pay( data ) {
		var
			$button = $( "#adk-stripe-button" );

		$button.oldCaption = $button.text();
		$button.text( ADK.locale.placigText )
			.attr( "disabled", "disabled" );

		$.ajax( {
				url:      ADK.locale.payUrl,
				type:     "post",
				data:     data,
				dataType: "text"
			} )

		.done( function( resp ) {
			var json = null;

			if( resp ) {
				json  = ADK.sanitizeAjaxResponse( resp );

				if( json ) {
					if( json.error ) {
						alert( json.error );

						return;

					} else if( json.success ) {
						$button.attr( "disabled" , "disabled" );
						alert( json.success );
						window.location.reload();

						return;
					}
				}
			}
			
			alert( ADK.locale.errorText );
		} )

		.fail( function( err ){
			try{window.console.log( err );}catch(e){}
			alert( ADK.locale.errorText );
		} )

		.always( function(){
			$button.removeAttr( "disabled" ).html( $button.oldCaption );
		} );
	}
</script>
