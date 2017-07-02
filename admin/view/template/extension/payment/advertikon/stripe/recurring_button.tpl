<style>
#adk-recurring-buttons {
	text-align: right;
}
#adk-recurring-buttons .action {
	display: none;
}
</style>
<div id="adk-recurring-buttons">
<?php if( $customer_can_cancel ) : ?>
	<?php if( $cancel_now ) : ?>
	<button id="adk-cancel-now" type="button" class="btn btn-default" title="<?php echo $a->__( 'Cancel immediately' ); ?>" data-url="<?php echo $delete_now_url; ?>">
		<i class="fa fa-times"></i>
		<i class="fa fa-circle-o-notch fa-spin action"></i>
	</button>
	<?php endif; ?>
	<button id="adk-cancel-period" type="button" class="btn btn-default" title="<?php echo $a->__( 'Cancel at period end' ); ?>" data-url="<?php echo $delete_period_url; ?>">
		<i class="fa fa-calendar"></i>
		<i class="fa fa-circle-o-notch fa-spin action"></i>
	</button>
<?php endif; ?>
	<button id="adk-refresh" type="button" class="btn btn-default" title="<?php echo $a->__( 'Refresh' ); ?>" data-url="<?php echo $refresh_url; ?>">
		<i class="fa fa-refresh"></i>
		<i class="fa fa-refresh fa-spin action"></i>
	</button>
</div>
<script>
( function( $ ){
	function adkAction( elem ) {
		$( elem ).find( '.action' ).css( 'display' , 'inline-block' );
		$( elem ).attr( 'disabled' , 'disabled' );
		$( elem ).find( 'i' ).not( '.action' ).hide();
	}
	function adkStop( elem ) {
		$( elem ).find( 'i:hidden' ).show().end().find( '.action' ).hide().end().removeAttr( 'disabled' );
	}

	var error = '<?php echo $a->__( "Network error" ); ?>';
	$( '#adk-cancel-now' ).on( 'click' , function(){
		adkAction( $( '#adk-cancel-now' ) );
		$.get( $( this).attr( 'data-url' ).replace( /&amp;/g , '&' ) , { order_recurring_id: '<?php echo $order_recurring_id; ?>'  } )
		.done( function( resp ){
			if( resp.substr( -7 ) == 'success' ) {
				alert( resp.slice( 0 , -7 ) );
				$( '#adk-refresh' ).click();
			}
			else if( resp.substr( -5 ) == 'error' ) {
				alert( resp.slice( 0 , -5 ) );
			}
			else {
				alert( error );
			}
		} )
		.fail( function(){ alert( error ) } )
		.always( function(){ adkStop( $( '#adk-cancel-now' ) ) } );
	} );

	$( '#adk-cancel-period' ).on( 'click' , function(){
		adkAction( $( '#adk-cancel-period' ) );
		$.get( $( this).attr( 'data-url' ).replace( /&amp;/g , '&' ) , { order_recurring_id: '<?php echo $order_recurring_id; ?>'  } )
		.done( function( resp ){
			if( resp.substr( -7 ) == 'success' ) {
				alert( resp.slice( 0 , -7 ) );
				$( '#adk-refresh' ).click();
			}
			else if( resp.substr( -5 ) == 'error' ) {
				alert( resp.slice( 0 , -5 ) );
			}
			else {
				alert( error );
			}
		} )
		.fail( function(){ alert( error ) } )
		.always( function(){ adkStop( $( '#adk-cancel-period' ) ) } );
	} );

	$( '#adk-refresh' ).on( 'click' , function(){
		adkAction( $( '#adk-refresh' ) );
		$.get( $( this).attr( 'data-url' ).replace( /&amp;/g , '&' ) , { order_recurring_id: '<?php echo $order_recurring_id; ?>' , recurring_id: '<?php echo $order_recurring_id; ?>'  } )
		.done( function( resp ) {
			try{
				var f = $( resp );
				if( ! f.find( '#content' ).length ) {
					throw 'Invalid responce';
				}
				$( 'body' ).find( '#content' ).html( f.find( '#content' ).html() );
				console.log( f.find( '#content' ).html() );
			}
			catch( e ) {
				if( resp.substr( -5 ) == 'error' ) {
					alert( resp.slice( 0 , -5 ) );
				}
				else {
					alert( error );
				}
				//throw e;
			}
		} )
		.fail( function(){ alert( error ) } )
		.always( function(){ adkStop( $( '#adk-refresh' ) ) } );
	} );

} )( jQuery );
</script>

