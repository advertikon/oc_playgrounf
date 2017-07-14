<style>
	.btn-success {
		background-color: #51ad51;
	}
</style>
<?php echo $header; ?>
<div class="container">
	<ul class="breadcrumb">
		<?php foreach ($breadcrumbs as $breadcrumb) { ?>
		<li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
		<?php } ?>
	</ul>
	<div class="row"><?php echo $column_left; ?>
		<?php if ($column_left && $column_right) { ?>
		<?php $class = 'col-sm-6'; ?>
		<?php } elseif ($column_left || $column_right) { ?>
		<?php $class = 'col-sm-9'; ?>
		<?php } else { ?>
		<?php $class = 'col-sm-12'; ?>
		<?php } ?>
		<div id="content" class="<?php echo $class; ?>">
			<?php echo $content_top; ?>
			<h1><?php echo $heading_title; ?></h1>

			<?php if( ! $cards ) : ?>
			<h2><?php echo $a->__( 'You have no saved cards' ); ?></h2>
			<?php else: ?>
			<div class="table-responsive">
				<table class="table table-hover" >
					<tr>
						<th><?php echo $a->__( 'Vendor' ); ?></th>
						<th><?php echo $a->__( 'Number' ); ?></th>
						<th><?php echo $a->__( 'Expiration' ); ?></th>
						<th><?php echo $a->__( 'Delete' ); ?></th>
						<th><?php echo $a->__( 'Set default' ) . '*'; ?></th>
					</tr>
					<?php foreach( $cards as $card ) : ?>
					<tr data-id="<?php echo $card->id; ?>"> 
						<td>
							<img
								src="<?php echo $a->get_brand_image( $card->brand ); ?>"
								style="width: 40px; height: auto;"
							>
						</td>
						<td><?php echo '**** **** **** ' . $card->last4; ?></td>
						<td><?php echo str_pad( $card->exp_month, 2, '0', STR_PAD_LEFT ) . '/' . $card->exp_year; ?></td>
						<td>
							<button
								type="button"
								class="btn btn-danger
								adk-delete"
								data-i="fa-close">
									<i class="fa fa-close"></i>
							</button>
						</td>
						<td>
							<button
								type="button"
								class="btn btn-<?php echo ( $card->id === $default_card ? 'success' : 'default' ); ?> adk-default"
								<?php if( $card->id === $default_card ) echo 'disabled="disabled"'; ?>
								data-i="fa-flag"
							>
								<i class="fa fa-flag"></i>
							</button
						></td>
					</tr>
					<?php endforeach; ?>
				</table>
			</div>
			<div>
			* <?php echo $a->__( 'The default card is used as payment source of recurring order' ); ?>
			</div>
			<?php endif; ?>
			<div class="buttons clearfix">
				<div class="pull-left">
					<a href="<?php echo $back; ?>" class="btn btn-default"><?php echo $button_back; ?></a>
				</div>
			</div>
		</div>
		<?php echo $content_bottom; ?>
	</div>
	<?php echo $column_right; ?>
</div>
<?php echo $footer; ?>

<script>

( function ( $ ) {
"use strict";

$.extend( ADK.locale, <?php echo json_encode( $locale ); ?> );

$( ".adk-delete" ).on( "click", function() {
	var
		self = this;

		ADK.confirm( ADK.locale.deleteCard )
		.yes(
			function() {
				delete_card.call( self, $( self ).parents( "tr" ).attr( "data-id" ) );
			 }
		);
} );

$( ".adk-default" ).on( "click", function() {
	default_card.call( this, $( this ).parents( "tr" ).attr( "data-id" ) );
} );

/**
 * Removes payment card form Stripe Dashboard
 * @param {string} card_id Card ID
 * @return {void}
 */
function delete_card( card_id ) {
	var $button  = $( this );

	$button.btnActive();

	$.post( ADK.locale.deleteUrl.replace( /&amp;/, "&" ), { card_id: card_id} )

	.always( function deleteCardAlways() {
		$button.btnReset();
	} )

	.fail( function deleteCardFail() {
		ADK.n.alert( ADK.locale.networkError );
	} )

	.done( function deleteCardDone( respStr ) {
		var resp = null;

		// If response is empty or doesn't contain JSON string
		if ( respStr ) {
			resp = ADK.sanitizeAjaxResponse( respStr );

			if ( null === resp ) {
				return;
			}

			// Task have been saved
			if ( resp.success ) {

				// If default card was deleted
				if( resp.default_source ) {
					$( ".adk-default" ).removeAttr( "disabled" )
						.removeClass( "btn-success" )
						.addClass( "btn-default" );

					$button.parents( "table" ).find( "tr[data-id=" + resp.default_source + "] .adk-default" )
						.attr( "disabled", "disabled" )
						.removeClass( "btn-default" )
						.addClass( "btn-success" );
				}

				$button.parents( "tr" ).remove();
				ADK.n.notification( ADK.locale.cardDeleted );
				
			// We got error
			} else if ( resp.error ) {
				ADK.n.alert( resp.error );

			// Something went wrong
			} else {
				ADK.n.alert( ADK.locale.scriptError );
			}

		} else {
			console.error( resp );
			ADK.n.alert( ADK.locale.networkError );
		}
	} );
}

/**
 * Sets card as default payment source
 * @param {string} card_id Card ID
 * @return {void}
 */
function default_card( card_id ) {
	var $button  = $( this );

	$button.btnActive();

	$.post( ADK.locale.defaultUrl.replace( /&amp;/, "&" ), { card_id: card_id} )

	.always( function defaultCardAlways() {
		$button.btnReset();
	} )

	.fail( function defaultCardFail() {
		modalAlert( ADK.locale.networkError );
	} )

	.done( function defaultCardDone( respStr ) {
		var resp = null;

		// If response is empty or doesn't contain JSON string
		if ( respStr ) {
			resp = ADK.sanitizeAjaxResponse( respStr );

			if ( null === resp ) {
				return;
			}

			// Success
			if ( resp.success ) {
				$( ".adk-default" ).removeAttr( "disabled" )
					.removeClass( "btn-success" )
					.addClass( "btn-default" );

				$button.attr( "disabled", "disabled" )
					.removeClass( "btn-default" )
					.addClass( "btn-success" );

				ADK.n.notification( ADK.locale.cardChanged );
				
			// We got error
			} else if ( resp.error ) {
				ADK.n.alert( resp.error );

			// Something went wrong
			} else {
				ADK.n.alert( ADK.locale.scriptError );
			}

		} else {
			console.error( resp );
			ADK.n.alert( ADK.locale.networkError );
		}
	} );
}

} ) ( jQuery )
</script>
