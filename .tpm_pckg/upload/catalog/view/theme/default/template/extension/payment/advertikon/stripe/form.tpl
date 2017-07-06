<link
	rel="stylesheet"
	type="text/css"
	href="<?php echo $a->u()->catalog_url(); ?>catalog/view/theme/default/stylesheet/advertikon/advertikon.css"
>
<link
	rel="stylesheet"
	type="text/css"
	href="<?php echo $a->u()->catalog_url(); ?>catalog/view/theme/default/stylesheet/advertikon/stripe/form.css"
>

<?php if( $recurring_invoices ) : ?>

<?php echo $recurring_invoices; ?>

<?php elseif( $model->is_different_payment_currency() ) : ?>

<div id="currency-notification" class="currency-notification">
<?php
	echo $a->caption( 'caption_charge_value' ) . ' ' .
	$a->currency->format(
		$total,
		$currency,
		1,
		true
	);
?>
</div>

<?php endif; ?> 

<div id="msgBox" role="alert"><i></i><span style="margin-left:10px;"></span></div>
<?php if( ! $a->config( 'checkout' ) ) : ?>
<form action="" method="POST" id="payment-form" class="form-horizontal- payment-form">
	<?php if( $a->config( 'show_card_image' ) ) : ?>
	<div
		class="adk-content adk-col-50 sp-payment"
		id="payment"
		class="payment"
		style="<?php echo $form_max_width; ?>"
	 >
	<?php else: ?>
	<div
		class="adk-content sp-payment"
		id="payment"
		class="payment"
		style="<?php echo $form_max_width; ?>"
	>
	<?php endif; ?>
		<div class="adk-row" id="header" class="header">
			<div>
				<h3 class="sp-header"><?php echo $a->caption( 'form_caption' ); ?></h3>
			</div>
		</div>
		<?php if ( $a->config( 'vendor_image_form' ) ) : ?>
		<div class="adk-row">
			<?php echo $vendors_tab; ?>
		</div>
		<?php endif; ?>
	<?php if( $a->config( 'card_name' ) ) : ?>
		<div class="adk-form-group">
			<label class="adk-col-30 adk-label" for='cc-name'>
				<?php echo $a->caption( 'caption_form_cardholder_name' ); ?>
			</label>
			<div class="adk-col-70">
				<input type="text" id="cc-name" class="adk-form-control" value="<?php echo $name; ?>" >
			</div>
		</div>
	<?php endif; ?>
		<div class="adk-form-group cc-number-cover formatted-view">
			<label class="adk-col-30 adk-label" for='cc-number'>
				<?php echo $a->caption( 'caption_form_card_nmber' ); ?>
			</label>
			<div class="adk-col-70">
				<input
					type="text"
					id="cc-number"
					class="adk-form-control simple-input"
					data-stripe="number"
					value=""
					maxlength="16"
					autocomplete="off"
				>
				<div class="adk-form-control cc-number-formatted formatted-input">
					<input class="cc-number-item"><!--
					--><input class="cc-number-item"><!--
					--><input class="cc-number-item"><!--
					--><input class="cc-number-item"><!--
					--><input class="cc-number-item"><!--
					--><input class="cc-number-item"><!--
					--><input class="cc-number-item"><!--
					--><input class="cc-number-item"><!--
					--><input class="cc-number-item"><!--
					--><input class="cc-number-item"><!--
					--><input class="cc-number-item"><!--
					--><input class="cc-number-item"><!--
					--><input class="cc-number-item"><!--
					--><input class="cc-number-item"><!--
					--><input class="cc-number-item"><!--
					--><input class="cc-number-item"><!--
					--><input class="cc-number-item"><!--
					--><input class="cc-number-item"><!--
					--><input class="cc-number-item">
				</div>
				<img
					src="<?php echo $image->resize( 'advertikon/stripe/switch.png', 25, 25 ); ?>"
					class="cc-field-switch"
					title="<?php echo $a->caption( 'caption_form_switch_mode' ); ?>"
				>
			</div>
		</div>
		<div class="adk-form-group">
			<label class="adk-col-30 adk-label" for="cc-month">
				<?php echo $a->caption( 'caption_form_card_expiration' ); ?>
			</label>
			<div class="adk-col-70">
				<select id="cc-month" data-stripe="exp-month" class="adk-form-control exp-select">
					<?php foreach ( $months as $name => $val ) : ?> 
					<option value="<?php echo $val; ?>"><?php echo $name; ?></option>
					<?php endforeach; ?>
				</select>
				<select id="cc-year" data-stripe="exp-year" class="adk-form-control exp-select"> 
					<?php foreach ( $option->next_year( 10 ) as $key => $val ) : ?>
					<option value="<?php echo $key; ?>"><?php echo $val; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
	<?php if( $a->config( 'cvc_check' ) ) : ?>
		<div class="adk-form-group">
			<label class="adk-col-30 adk-label" for="cc-cvv">
				<?php echo $a->caption( 'caption_form_card_cvc' ); ?>
			</label>
			<div class="adk-col-70">
				<input
					id="cc-cvv"
					type="password"
					data-stripe="cvc"
					value=""
					class="adk-form-control adk-cvc"
					autocomplete="off"
				>
			</div>
		</div>
	<?php endif; ?>
	<?php if( $show_remember_me ) : ?>
		<div class="adk-form-group">
			<div>
				<input id="remember-me" type="checkbox">
				<label for="remember-me">
					<?php echo $a->caption( 'caption_form_rerember_me' ); ?>
				</label>
				<div style="font-size:0.8em;line-height:1.3em;">
					<?php echo $a->caption( 'caption_form_rerember_me_description' ); ?>
				</div>
			</div>
		</div>
		<?php if( count( $saved_cards ) > 0 ) : ?>
		<div class="adk-form-group adk-to-show">
			<div>
				<input id="make-default" type="checkbox" disabled="disabled" >
				<label for="make-default">
					<?php echo $a->caption( 'caption_form_make_default' ); ?>
				</label>
				<div style="font-size:0.8em;line-height:1.3em;">
					<?php echo $a->caption( 'caption_form_make_default_description' ); ?>
				</div>
			</div>
		</div>
		<?php endif; ?>
	<?php endif; ?>
	<?php if( $saved_cards ) : ?>
		<div class="adk-form-group">
			<div style="overflow: hidden; position: relative;">
				<select class="adk-form-control" id="saved-card">
					<option value="0">
						<?php echo $a->caption( 'caption_form_select_card' ); ?>
					</option>
					<?php foreach( $saved_cards as $card ) : ?>
					<option
						value="<?php echo $card->id; ?>"
						data-image="<?php echo $a->get_brand_image( $card->brand ); ?>"
						class="<?php if( $default_card === $card->id )echo 'default-card'; ?>"
					>
						<?php
						echo '**** ' . $card->last4 .
						' (' . str_pad( $card->exp_month, 2, '0', STR_PAD_LEFT ) .
						'/' . $card->exp_year . ')';
						?>
					</option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
	<?php endif; ?>
	<?php if ( $a->config( 'saved_card_secret' ) ) : ?>
		<div class="adk-form-group adk-to-show adk-to-see">
			<div>
				<label for="adk-secret" class="adk-col-30 adk-label">
					<?php echo $a->caption( 'caption_form_card_password' ); ?>
				</label>
				<div class="adk-col-70">
					<input id="adk-secret" type="password" class="adk-form-control" >
				</div>
				<div style="font-size:0.8em;line-height:1.3em;">
					<?php echo $a->caption( 'caption_form_card_password_description' ); ?>
				</div>
			</div>
		</div>
	<?php endif; ?>
		<div class="adk-form-group" style="<?php echo ( ! empty( $hide_button ) ? 'display: none;' : '' ); ?>">
			<div class="adk-col-70">
				<button
					type="button"
					class="btn btn-primary adk-confirm-button pull-right- adk-confirm"
					id="button-confirm"
					data-loading-text="Processing"
					disabled="disabled"
				>
					<?php echo $button_confirm; ?>
				</button>
			</div>
		</div>
	</div>
	<?php if( $a->config( 'show_card_image' ) ) : ?>
	<!--[if gt IE 8]>-->
	<div class="adk-col-50 show-at-top">
		<div id="sp-container" class="sp-container">
			<div id="sp-card" class="sp-card">
				<div id="sp-face" class="sp-face">
					<div class="sp-code">
						<input id="sp-code-1" class="sp-code-field" readonly="readonly">
						<input id="sp-code-2" class="sp-code-field" readonly="readonly">
						<input id="sp-code-3" class="sp-code-field" readonly="readonly">
						<input id="sp-code-4" class="sp-code-field" readonly="readonly">
					</div>  
					<div class="sp-expire">
						<input id="sp-expire" class="sp-expire-field" readonly="readonly">
					</div>
					<div class="sp-cardholder">
						<input id="sp-cardholder" class="sp-cardholder-field" readonly="readonly">
					</div>
				</div>
				<div id="sp-back" class="sp-back">
					<div class="sp-cvv">
						<input id="sp-cvv" class="sp-cvv-field" readonly="readonly">
					</div>
				</div>
			</div>
		</div>
	</div>
	<!--<![endif]-->
	<?php endif; ?>
</form>
<?php else: ?><!-- pop-up checkout -->

<button
	type="button"
	class="btn btn-primary checkout-confirm adk-confirm"
	id="button-confirm"
	data-loading-text="Processing"
	disabled="disabled"
	style="<?php echo ( ! empty( $hide_button ) ? 'display: none;' : '' ); ?>"
>
	<?php echo $button_confirm; ?>
</button>

	<?php if( $a->config( 'button_class' ) ) : ?>
<div
	id="button_triggers"
	class="<?php echo htmlspecialchars( $a->config( 'button_class' ) ); ?>"
	style="display: none;"
></div>
	<?php endif; ?>
<?php endif; ?><!-- pop-up/embed option -->

<script>
window.ADK = { locale: <?php echo $locale; ?> };
</script>

<?php echo $script_url; ?>

<script>
var checkoutScript = document.createElement( 'script' );
checkoutScript.src = "https://checkout.stripe.com/checkout.js";
document.documentElement.appendChild( checkoutScript );
checkoutScript.onload = ADK.stripeCheckout;
</script>

isabled="disabled" >
				<label for="make-default">
					<?php echo $a->caption( 'caption_form_make_default' ); ?>
				</label>
				<div style="font-size:0.8em;line-height:1.3em;">
					<?php echo $a->caption( 'caption_form_make_default_description' ); ?>
				</div>
			</div>
		</div>
		<?php endif; ?>
	<?php endif; ?>
	<?php if( $saved_cards ) : ?>
		<div class="adk-form-group">
			<div style="overflow: hidden; position: relative;">
				<select class="adk-form-control" id="saved-card">
					<option value="0">
						<?php echo $a->caption( 'caption_form_select_card' ); ?>
					</option>
					<?php foreach( $saved_cards as $card ) : ?>
					<option
						value="<?php echo $card->id; ?>"
						data-image="<?php echo $a->get_brand_image( $card->brand ); ?>"
						class="<?php if( $default_card === $card->id )echo 'default-card'; ?>"
					>
						<?php
						echo '**** ' . $card->last4 .
						' (' . str_pad( $card->exp_month, 2, '0', STR_PAD_LEFT ) .
						'/' . $card->exp_year . ')';
						?>
					</option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
	<?php endif; ?>
	<?php if ( $a->config( 'saved_card_secret' ) ) : ?>
		<div class="adk-form-group adk-to-show adk-to-see">
			<div>
				<label for="adk-secret" class="adk-col-30 adk-label">
					<?php echo $a->caption( 'caption_form_card_password' ); ?>
				</label>
				<div class="adk-col-70">
					<input id="adk-secret" type="password" class="adk-form-control" >
				</div>
				<div style="font-size:0.8em;line-height:1.3em;">
					<?php echo $a->caption( 'caption_form_card_password_description' ); ?>
				</div>
			</div>
		</div>
	<?php endif; ?>
		<div class="adk-form-group" style="<?php echo ( ! empty( $hide_button ) ? 'display: none;' : '' ); ?>">
			<div class="adk-col-70">
				<button
					type="button"
					class="btn btn-primary adk-confirm-button pull-right- adk-confirm"
					id="button-confirm"
					data-loading-text="Processing"
					disabled="disabled"
				>
					<?php echo $button_confirm; ?>
				</button>
			</div>
		</div>
	</div>
	<?php if( $a->config( 'show_card_image' ) ) : ?>
	<!--[if gt IE 8]>-->
	<div class="adk-col-50 show-at-top">
		<div id="sp-container" class="sp-container">
			<div id="sp-card" class="sp-card">
				<div id="sp-face" class="sp-face">
					<div class="sp-code">
						<input id="sp-code-1" class="sp-code-field" readonly="readonly">
						<input id="sp-code-2" class="sp-code-field" readonly="readonly">
						<input id="sp-code-3" class="sp-code-field" readonly="readonly">
						<input id="sp-code-4" class="sp-code-field" readonly="readonly">
					</div>  
					<div class="sp-expire">
						<input id="sp-expire" class="sp-expire-field" readonly="readonly">
					</div>
					<div class="sp-cardholder">
						<input id="sp-cardholder" class="sp-cardholder-field" readonly="readonly">
					</div>
				</div>
				<div id="sp-back" class="sp-back">
					<div class="sp-cvv">
						<