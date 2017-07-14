<?php $a->document->addScript( $a->u()->admin_url() . 'view/javascript/advertikon/advertikon.js' ); ?>
<?php $a->document->addStyle( $a->u()->admin_url() . 'view/stylesheet/advertikon/advertikon.css' ); ?>
<style>
	.adk-payment .row > div{
		padding: 10px;
	}
	.adk-payment .adk-title {
		font-weight: bold;
	}
	.adk-payment .adk-input {
		max-width: 300px;
	}
	.adk-payment .adk-help {
		position: absolute;
		top: 0;
		right: -10px;
		bottom: 0;
		height: 20px;
		margin: auto;
		font-size: 20px;
		z-index: 10;
		cursor: help;
	}
	#adk-refresh {
		position: absolute;
		right: 35px;
	}
	.label {
		vertical-align: bottom;
	}
</style>
<div class="adk-payment">
	<button
		id="adk-refresh"
		class="btn btn-primary"
		type="button"
		title="<?php echo $a->__( 'Refresh' ); ?>"
		data-i="fa-refresh"
	>
		<i class="fa fa-refresh"></i>
	</button>
	<div class="row h3 text-primary text-center">
		<?php echo $a->__( 'Charge detalis' ); ?>
		<?php if( $order->livemode ) : ?>
		<!--<span class="label label-success">Live</span>-->
		<?php else : ?>
		<span class="label label-warning">Test</span>
		<?php endif; ?>
	</div>
	<div class="row">
		<div class="col-sm-2 adk-title"><?php echo $a->__( 'Charge ID:' ); ?></div>
		<div class="col-sm-10"><?php echo $order->id; ?></div>
	</div>
	<div class="row">
		<div class="col-sm-2 adk-title"><?php echo $a->__( 'Status' ); ?></div>
		<?php if( $order->dispute ) : ?>
		<div class="label label-danger" style="font-size:1em;">
			<?php echo $a->__( 'Disputed' ); ?>
		</div>
		<?php elseif( $order->status !== 'succeeded' ) : ?>
		<div class="label label-danger" style="font-size:1em;">
			<?php
				echo $a->__( 'Error' );
				if( $order->failure_message ) echo ' (' . $order->failure_message . ')';
			?>
		</div>
		<?php elseif( $order->refunded ) : ?>
		<div class="label label-default" style="font-size:1em;">
			<?php echo $a->__( 'Refunded' ); ?>
		</div>
		<?php elseif( $order->captured ) : ?>
		<div class="label label-success" style="font-size:1em;">
			<?php echo $a->__( 'Captured' ); ?>
		</div>
		<?php else : ?>
		<div class="label label-info" style="font-size:1em;">
			<?php
				echo $a->__(
					'Uncaptured. Will be released after %d day(s)',
					$a->remain_to_be_captured( $order )
				);
			?>
		</div>
		<?php endif; ?>
	</div>
	<?php if( $card_image ) : ?>
	<div class="row">
		<div class="col-sm-2 adk-title"><?php echo $a->__( 'Card vendor:' ); ?></div>
		<div class="col-sm-10">
			<img src="<?php echo $card_image; ?>" style="width:50px;">
		</div>
	</div>
	<?php endif; ?>
	<?php if( $last4 ) : ?>
	<div class="row">
		<div class="col-sm-2 adk-title"><?php echo $a->__( 'Card number:' ); ?></div>
		<div class="col-sm-10">
			<?php echo str_repeat( '*** ', 3 ) . $last4; ?>
		</div>
	</div>
	<?php endif; ?>
	<?php if( $card_type ) : ?>
	<div class="row">
		<div class="col-sm-2 adk-title"><?php echo $a->__( 'Card type:' ); ?></div>
		<div class="col-sm-10">
			<?php echo ucfirst( $card_type ); ?>
		</div>
	</div>
	<?php endif; ?>
	<?php if( $cvc_check ) : ?>
	<div class="row">
		<div class="col-sm-2 adk-title"><?php echo $a->__( 'CVC check:' ); ?></div>
		<div class="col-sm-10">
			<span style="font-size:1em;" class="<?php echo $model->get_check_class( $cvc_check ); ?>">
				<?php echo ucfirst( $cvc_check ); ?>
			</span>
		</div>
	</div>
	<?php endif; ?>
	<?php if( $address_line1_check ) : ?>
	<div class="row">
		<div class="col-sm-2 adk-title"><?php echo $a->__( 'Address Line 1 check:' ); ?></div>
		<div class="col-sm-10">
			<span style="font-size:1em;" class="<?php echo $model->get_check_class( $address_line1_check ); ?>">
				<?php echo ucfirst( $address_line1_check ); ?>
			</span>
		</div>
	</div>
	<?php endif; ?>
	<?php if( $address_zip_check ) : ?>
	<div class="row">
		<div class="col-sm-2 adk-title"><?php echo $a->__( 'ZIP-code check:' ); ?></div>
		<div class="col-sm-10">
			<span style="font-size:1em;" class="<?php echo $model->get_check_class( $address_zip_check ); ?>">
				<?php echo ucfirst( $address_zip_check ); ?>
			</span>
		</div>
	</div>
	<?php endif; ?>
	<?php if( $order->balance_transaction ) : ?>
	<div class="row">
		<div class="col-sm-2 adk-title"><?php echo $a->__( 'Balance transaction:' ); ?></div>
		<div class="col-sm-10">
			<?php echo $order->balance_transaction; ?>
		</div>
	</div>
	<?php endif; ?>
	<div class="row">
		<div class="col-sm-2 adk-title"><?php echo $a->__( 'Amount:' ); ?></div>
		<div class="col-sm-10">
			<?php
				echo $a->currency->format(
					$a->cents_to_amount(
						$order->amount - ( $order->amount_refunded ?: 0 ) ,
						$order->currency
					) ,
					strtoupper( $order->currency ),
					1
				);
			?>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-2 adk-title"><?php echo $a->__( 'Refunded:' ); ?></div>
		<div class="col-sm-10">
			<?php
				echo $a->currency->format(
					$a->cents_to_amount(
						$order->amount_refunded ,
						$order->currency
					),
					strtoupper( $order->currency ),
					1
				);
			?>
		</div>
	</div>
	<?php if( ! $order->captured && ! $order->refunded ) : ?>
	<div class="row">
		<div class="input-group col-sm-10 col-sm-offset-2 adk-input">
			<span class="input-group-addon" id="adk-capture-label">
				<?php echo strtoupper( $order->currency ); ?>
			</span>
			<input
				type="text"
				id="adk-capture"
				class="form-control"
				placeholder="<?php echo $a->__( 'Amount to capture' ); ?>"
				aria-describedby="adk-capture-label"
			>
			<i
				class="fa fa-question-circle adk-help text-info"
				data-toggle="tooltip"
				data-placement="right"
				title="<?php echo $a->__(
					'You may capture sum, which are less than total amount, the rest will be refunded'
				); ?>"
			></i>
			<div class="input-group-btn">
				 <button id="adk-capture-button" class="btn btn-default" type="button">
				 	<?php echo $a->__( 'Capture' ); ?>
				 </button>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-10 col-sm-offset-2">
			<input
				type="checkbox"
				id="adk-notify-capture"
				<?php if( $a->config( 'notify_customer' ) )echo 'checked="checked"'; ?>
			>
			<label>&nbsp;<?php echo $a->__( 'Notify customer' ); ?></label>
		</div>
		<div class="col-sm-10 col-sm-offset-2">
			<input
				type="checkbox"
				id="adk-overide-capture"
				<?php if( $a->config( 'override' ) )echo 'checked="checked"'; ?>
			>
			<label>&nbsp;<?php echo $a->__( 'Override if order status blocked' ); ?></label>
		</div>
	</div>
	<?php endif; ?>
	<?php if( isset( $order->refunds->data ) && $order->refunds->data ) : ?>
	<div class="row">
		<div class="col-sm-2 adk-title"><?php echo $a->__( 'Refunds list:' ); ?></div>
		<div class="input-group col-sm-10">
			<table class="table">
				<tr>
					<th><?php echo $a->__( 'Date' ); ?></th>
					<th><?php echo $a->__( 'Amount' ); ?></th>
					<th><?php echo $a->__( 'Transaction' ); ?></th>
					<th><?php echo $a->__( 'Reason' ); ?></th>
				</tr>
				<?php foreach( $order->refunds->data as $refund ) : ?>
				<tr>
					<td>
						<span class="hidden-xs"><?php echo date( 'r' , $refund->created ); ?></span>
						<span class="visible-xs-inline"><?php echo date( 'd/m/y' , $refund->created ); ?></span>
					</td>
					<td>
						<?php
							echo $a->currency->format(
								$a->cents_to_amount(
									$refund->amount,
									$refund->currency
								),
								strtoupper( $refund->currency ),
								1
							);
						?>
					</td>
					<td><?php echo $refund->balance_transaction; ?></td>
					<td><?php echo $refund->reason; ?></td>
				</tr>
				<?php endforeach; ?>
			</table>
		</div>
	</div>
	<?php endif; ?>
	<?php if( ! $order->refunded && $order->captured ) : ?>
	<div class="row">
		<div class="input-group col-sm-10 col-sm-offset-2 adk-input">
			<span class="input-group-addon" id="adk-refund-label">
				<?php echo strtoupper( $order->currency ); ?>
			</span>
			<input
				type="text"
				id="adk-refund"
				class="form-control"
				placeholder="<?php echo $a->__( 'Amount to refund' ); ?>"
				aria-describedby="adk-refund-label"
			>
			<div class="input-group-btn">
				 <button id="adk-refund-button" class="btn btn-default" type="button">
				 	<?php echo $a->__( 'Refund' ); ?>
				 </button>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-10 col-sm-offset-2">
			<input
				type="checkbox"
				id="adk-notify-refund"
				<?php if( $a->config( 'notify_customer' ) )echo 'checked="checked"'; ?>
			>
			<label>&nbsp;<?php echo $a->__( 'Notify customer' ); ?></label>
		</div>
		<div class="col-sm-10 col-sm-offset-2">
			<input
				type="checkbox"
				id="adk-overide-refund"
				<?php if( $a->config( 'override' ) )echo 'checked="checked"'; ?>
			>
			<label>&nbsp;<?php echo $a->__( 'Override if order status blocked' ); ?></label>
		</div>
	</div>
	<?php endif; ?> 
</div>
<script>
( function( $ ) {

// Capture button click
$( "#adk-capture-button" ).on( "click" , function() {
	var
		oldText = "",
		val =  $( "#adk-capture" ).val();

	if( val == '' ) {
		ADK.n.alert( '<?php echo $a->__( "Amount should be greater than zero" ); ?>' );
		ADK.pulsate( $( "#adk-capture" ) );

		return;

	} else {
		if( ! confirm(
			'<?php echo $a->__( "Capture charge on sum of " ) ?>' + val +
			' <?php echo strtoupper( $order->currency); ?>'
		) ) {
			return;
		}
	}

	this.setAttribute( "disabled" , "disabled" );
	oldText = this.textContent;
	this.textContent = '<?php echo $a->__( "Capturing..." ); ?>';

	jQuery.ajax( {
		url: '<?php echo str_replace( "&amp;" , "&" , $capture_url ); ?>',
		type: 'GET',
		data: {
			order_id: '<?php echo $order_id; ?>',
			amount:   val,
			notify:   $( "#adk-notify-capture" ).is( ":checked" ) ? 1 : 0,
			override: $( "#adk-overide-capture" ).is( ":checked" ) ? 1 : 0
		}
	} )

	.done( function( resp ) {
		var
			def = null,
			fr = null;

		try{
			fr = $( resp );

			if( fr.find( "#adk-refresh" ).length !== 0 ) {
				def = addHistory(
					'<?php echo $captured_status; ?>' ,
					'<?php echo $a->__( "Capture of payment on amount of" ); ?> ' + val +
						' <?php echo strtoupper( $order->currency ); ?>',
					$( "#adk-notify-capture" ).is( ":checked" ) ? 1 : 0,
					$( "#adk-overide-capture" ).is( ":checked" ) ? 1 : 0
				);

				def.always( function(){
					getFresh();
				} );

				return;
			}

		} catch( e ) {

		}

		parseResponse( resp );	
	} )

	.fail( function() {
		alert( "<?php echo $a->__( 'Error occurred while passing request to the server. Charge was not captured' ); ?>" );
	} )

	.always( function() {
		$( '#adk-capture-button' ).removeAttr( "disabled" ).text( oldText );
	} );

} );

// Capture amount input field
$( "#adk-capture" ).on( "change" , function() {
	var max = parseFloat( '<?php echo $a->cents_to_amount( $order->amount , $order->currency ); ?>' ),
		val = this.value.replace( /[^0-9\\.]/g , '' );

	this.value = max < val ? max : val;
} );

// Refund button click
$( "#adk-refund-button" ).on( "click" , function() {
	var val =  $( "#adk-refund" ).val(),
		max = parseFloat(
			'<?php echo $a->cents_to_amount( $order->amount - $order->amount_refunded , $order->currency ); ?>'
		),
		oldText = null;

	if( val == '' ) {
		ADK.n.alert( '<?php echo $a->__( "Amount should be greater than zero" ); ?>' );

		return;

	} else {
		if( ! confirm(
			'<?php echo $a->__( "Refund charge on sum of " ) ?>' + val +
				' <?php echo strtoupper( $order->currency); ?>'
		) ) {
			return;
		}
	}

	this.setAttribute( "disabled" , "disabled" );
	oldText = this.textContent;
	this.textContent = '<?php echo $a->__( "Refunding..." ); ?>';

	jQuery.ajax( {
		url: '<?php echo str_replace( "&amp;" , "&" , $refund_url ); ?>',
		data: {
			order_id: '<?php echo $order_id; ?>',
			amount: val
		}
	} )

	.done( function( resp ) {
		var
			def = null,
			fr = null,
			stat = null;

		try{
			fr = $( resp );

			if( fr.find( "#adk-refresh" ).length !== 0 ) {
				stat = max == val ? '<?php echo $refunded_status; ?>' : '<?php echo $current_status; ?>',

				def = addHistory(
					stat,
					'<?php echo $a->__( "Refund of payment on amount of" ); ?> ' + val +
						' <?php echo strtoupper( $order->currency ); ?>',
					$( "#adk-notify-refund" ).is( ":checked" ) ? 1 : 0,
					$( "#adk-overide-refund" ).is( ":checked" ) ? 1 : 0
				);

				def.always( function(){
					getFresh();
				} );

				return;
			}

		} catch( e ) {

		}

		parseResponse( resp );	
	} )

	.fail( function() {
		ADK.alert(
			"<?php echo $a->__( 'Error occurred while passing request to the server. Charge was not refunded' ); ?>"
		);
	} )

	.always( function() {
		$( '#adk-refund-button' ).removeAttr( "disabled" ).text( oldText );
	} );

} );

// Refund amount input field
$( "#adk-refund" ).on( "change" , function() {
	var max = parseFloat(
			'<?php echo $a->cents_to_amount( ( $order->amount - $order->amount_refunded ) , $order->currency ); ?>'
		),
		val = this.value.replace( /[^0-9\\.]/g , '' );

	this.value = max < val ? max : val;
} );

// Refresh button
$( "#adk-refresh" ).on( "click" , function() {
	var btn = this;

	$( this ).btnActive();

	jQuery.ajax( {
		url: '<?php echo str_replace( "&amp;" , "&" , $refresh_url ); ?>',
		data: {
			order_id: '<?php echo $order_id; ?>'
		}
	} )

	.always( function(){
		$( btn ).btnReset();
	} )

	.fail( function(){
		ADK.alert( '<?php echo $a->__( "Network communication issue" ); ?>' );
	} )

	.done( function( resp ) {
		var fr = null;

		try {
			fr = $( resp );

			if( fr.find( "#adk-refresh" ).length !== 0 ) {
				$( "#tab-advertikon_stripe" )
					.empty()
					.append( resp );

				return;
			}

		} catch( e ) {

		}

		parseResponse( resp );
	} );

} );

// Retrieve order current status ID
$( "#button-history" ).on( "click" , function(){
	window.setTimeout( 'getFresh' , 2000 );
} );

function getFresh() {
	jQuery.ajax( {
		url: '<?php echo str_replace( "&amp;" , "&" , $order_url ); ?>'
	} )

	.done( function( resp ) {
		var fr = null;

		try{
			fr = $( resp );

			if( fr.find( "#adk-refresh" ).length !== 0 ) {
				$( "#tab-advertikon_stripe" )
					.empty()
					.append( resp );

				return;
			}

		} catch( e ) {

		}
	} );
}

/**
 * Get access to Add order history API
 * @param Integer {orderStatus} New order status
 * @param String {comment} Comment
 * @param Boolean {notify} Whether to notify customer
 * @return Deferred
 */
function addHistory( orderStatus , comment , notify , override ) {
	var def = $.Deferred();

	jQuery.ajax( {
		url: '<?php echo $history_url; ?>' + token,
		type: 'POST',
		data: {
			order_id: '<?php echo $order_id; ?>',
			order_status_id: orderStatus,
			comment: comment,
			notify: notify,
			override: override
		}
	} )

	.fail( function(){
		def.reject( '<?php echo $a->__( "Network communication issue while changing order status" ); ?>' );
	} )

	.done( function( resp ){
		$( "#history" ).load(
			"index.php?route=sale/order/history&token=<?php echo $token; ?>&order_id=<?php echo $order_id; ?>"
		);

		def.resolve( resp );
	} );

	return def;
}

/**
 * Alert messages depend on response
 * @param String {resp} XHR response
 */
function parseResponse( resp ) {
	var
		json = null,
		parts = null;

	if( resp ) {
		parts = resp.match( /^([^{]*)({[^}]+})(.*)$/ );

		if( ! parts || ! parts[ 2 ] ) {
			ADK.alert( '<?php echo $a->__( "Error occurred while getting response from the server" ); ?>' );

			return;
		}

		if( parts[ 1 ] || parts[ 3 ] ) {
			window.console.log( 'Advertikon Stripe: Captured Output - ' +
				( parts[ 1 ] ? parts[ 1 ] : '' ) + ( parts[ 3 ] ? parts[ 3 ] : '' ) );
		}

		try { 
			json = JSON.parse( parts[ 2 ] );

		} catch( err ) {
			ADK.alert( '<?php echo $a->__( "Error occurred while parsing server respond" ); ?>' );
			return;
		}

		if( json ) {
			if( json.error ) {
				ADK.alert( json.error );

			} else {
				ADK.alert( '<?php echo $a->__( "Undefined server response" ); ?>' );

				return;
			}

		} else {
			ADK.alert( '<?php echo $a->__( "Unable to parse server response" ); ?>' );

			return;
		}

	} else {
		ADK.alert( '<?php echo $a->__( "Error occurred while passing request to the server." ); ?>' );

		return;
	}

}

} ) ( jQuery );
</script>
