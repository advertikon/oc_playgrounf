<?php echo $header; ?>
<?php echo $column_left; ?> 
<div id="content">
	<div class="page-header">
		<div class="container-fluid">
			<div class="pull-right">
				<?php echo $check_tls ?>
				<button
					type="submit"
					form="form-manufacturer"
					data-toggle="tooltip"
					title="<?php echo $button_save; ?>"
					class="btn btn-primary"
				>
					<i class="fa fa-save"></i>
				</button>
				<a
					href="<?php echo $cancel; ?>"
					data-toggle="tooltip"
					title="<?php echo $button_cancel; ?>"
					class="btn btn-default"
				>
					<i class="fa fa-reply"></i>
				</a>
			</div>
			<h1>
				<?php echo $a->__( 'Stripe' ); ?>
				<?php if( $a->config( 'test_mode' ) ) : ?>
				<span class="label label-warning"><?php echo $a->__( 'Test mode' ); ?></span>
				<?php endif; ?>
			</h1>
			<ul class="breadcrumb">
				<?php foreach ( $breadcrumbs as $breadcrumb ) : ?>
				<li>
					<a href="<?php echo $breadcrumb['href']; ?>">
						<?php echo $breadcrumb['text']; ?>
					</a>
				</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
	<div class="container-fluid">
	
		<?php if( ! $a->config( 'status' ) ) : ?>	
		<div class="alert alert-warning alert-dismissible" role="alert">
			<button type="button" class="close" data-dismiss="alert" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
			<?php echo $a->__( 'The extension is disabled' ); ?>
		</div>
		<?php endif; ?>

		<?php if( ! empty( $compatibility ) ) : ?>
			<?php foreach( $compatibility as $name => $err ) : ?>
				<?php foreach( $err as $err_name => $msg ) : ?>
					<?php foreach( $msg as $m ) : ?>
						<?php if ( 'error' === $err_name ) : ?>
		<div class="alert alert-danger alert-dismissible" role="alert">
			<i class="fa fa-exclamation-circle"></i> <?php echo sprintf( '%s [error]: %s', $name, $m ); ?>
			<button type="button" class="close" data-dismiss="alert">&times;</button>
		</div>
						<?php elseif( 'alert' === $err_name ) : ?> 
		<div class="alert alert-warning alert-dismissible" role="alert">
			<i class="fa fa-exclamation-circle"></i> <?php echo sprintf( '%s [alert]: %s', $name, $m ); ?>
			<button type="button" class="close" data-dismiss="alert">&times;</button>
		</div>
						<?php endif; ?>
					<?php endforeach; ?>
				<?php endforeach; ?>
			<?php endforeach; ?>
		<?php endif; ?>

		<?php if( ! empty( $error_warning ) ) : ?>
			<?php foreach( $error_warning as $msg ) : ?> 
		<div class="alert alert-danger alert-dismissible" role="alert">
			<i class="fa fa-exclamation-circle"></i> <?php echo $msg; ?>
			<button type="button" class="close" data-dismiss="alert">&times;</button>
		</div>
			<?php endforeach; ?>
		<?php endif; ?>

		<?php if( ! empty( $error_attention ) ) : ?>
			<?php foreach( $error_attention as $msg ) : ?>
		<div class="alert alert-warning alert-dismissible" role="alert">
			<button type="button" class="close" data-dismiss="alert" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
			<strong>Warning!</strong> <?php echo $msg; ?>
		</div>
			<?php endforeach; ?>
		<?php endif; ?>

		<?php if( ! empty( $success ) ) : ?>
		<div class="alert alert-success alert-dismissible" role="alert">
			<button type="button" class="close" data-dismiss="alert" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
			<?php echo $success; ?>
		</div>
		<?php endif; ?>

		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">
					<i class="fa fa-pencil"></i> <?php echo $a->__( 'Stripe Settings' ); ?>
				</h3>
			</div>
			<div class="panel-body">
				<ul class="nav nav-tabs">
					<li class="active sway-able">
						<a href="#tab-api" data-toggle="tab-top">
							<i class="fa fa-terminal tab-icon"> </i>
							<?php echo $a->__( 'API Keys' ); ?>
						</a>
					</li>
					<li class="sway-able">
						<a href="#tab-general" data-toggle="tab-top">
							<i class="fa fa-wrench tab-icon"> </i>
							<?php echo $a->__( 'General' ); ?>
						</a>
					</li>
					<li class="sway-able">
						<a href="#tab-system" data-toggle="tab-top">
							<i class="fa fa-cogs tab-icon"> </i>
							<?php echo $a->__( 'System' ); ?>
						</a>
					</li>
					<li class="sway-able">
						<a href="#tab-card" data-toggle="tab-top">
							<i class="fa fa-object-group tab-icon"> </i>
							<?php echo $a->__( 'Embedded checkout' ); ?>
						</a>
					</li>
					<li class="sway-able">
						<a href="#tab-stripe-checkout" data-toggle="tab-top">
							<i class="fa fa-object-ungroup tab-icon"> </i>
							<?php echo $a->__( 'Stripe checkout' ); ?>
						</a>
					</li>
					<li class="sway-able">
						<a href="#tab-button" data-toggle="tab-top">
							<i class="fa fa-hand-pointer-o tab-icon"> </i>
							<?php echo $a->__( 'Payment button' ); ?>
						</a>
					</li>
					<li class="sway-able">
						<a href="#tab-templates" data-toggle="tab-top">
						 	<i class="fa fa-puzzle-piece tab-icon"> </i>
							<?php echo $a->__( 'Templates' ); ?>
						</a>
					</li>
					<li class="sway-able">
						<a href="#tab-webhook" data-toggle="tab-top">
							<i class="fa fa-exchange tab-icon"> </i>
							<?php echo $a->__( 'Webhooks' ); ?>
						</a>
					</li>
					<li class="sway-able">
						<a href="#tab-plans" data-toggle="tab-top">
							<i class="fa fa-recycle tab-icon"> </i>
							<?php echo $a->__( 'Plans' ); ?>
						</a>
					</li>
					<li class="sway-able">
						<a href="#tab-plan-profile" data-toggle="tab-top">
							<i class="fa fa-book tab-icon"> </i>
							<?php echo $a->__( 'Profiles' ); ?>
						</a>
					</li>
					<li class="sway-able">
						<a href="#tab-plan-profile-map" data-toggle="tab-top">
							<i class="fa fa-map-o tab-icon"> </i>
							<?php echo $a->__( 'Mapping' ); ?>
						</a>
					</li>
					<li class="sway-able">
						<a href="#tab-caption" data-toggle="tab-top">
							<i class="fa fa-font tab-icon"> </i>
							<?php echo $a->__( 'Labels' ); ?>
						</a>
					</li>
					<li class="sway-able">
						<a href="#tab-support" data-toggle="tab-top">
							<i class="fa fa-life-saver tab-icon"> </i>
							<?php echo $a->__( 'Support' ); ?>
						</a>
					</li>
				</ul>
				<div class="tab-content">
					<form
						action="<?php echo $action; ?>"
						method="post"
						enctype="multipart/form-data"
						id="form-product"
						class="form-horizontal"
					>

<!--  ************************************ Tab Active Start ************************************ -->
						<div class="tab-pane active top-pane" id="tab-api">

							<div class="alert alert-success alert-dismissible" role="alert">
								<button type="button" class="close" data-dismiss="alert" aria-label="Close">
								<span aria-hidden="true">&times;</span>
								</button>
								<?php echo $a->__( 'You can link several Stripe accounts to the extension and map them to different currencies. Depending on the currency of the order extension will select the appropriate Stripe account. If there is no match - default account will be used' ); ?>
							</div>

							<?php echo $add_account_btn; ?>

							<?php
								foreach( $accounts as $account ) :
									echo $account;
								endforeach;
							?>

						</div>
<!--  *********************************** Tab Active End *************************************** -->

<!--  *********************************** Tab General Start ************************************ -->
						<div class="tab-pane top-pane" id="tab-general">

							<?php
								echo $payment_method;
								echo $payment_currency;
								echo $charge_description;
								echo $customer_description;
								echo $statement_descriptor;
								echo $total_min;
								echo $total_max;
								echo $geo_zone;
								echo $avail_systems;
								echo $stores;
								echo $customer_groups;
								echo $sort_order;
								echo $receipt_email;
								echo $notify_customer;
								echo $override;
								echo $hide_button;
								echo $button_class;
							?>

						</div>
<!--  ********************************** Tab General End *************************************** -->

<!--  ********************************** Tab System Start ************************************** -->
						<div class="tab-pane top-pane" id="tab-system">

							<?php
								echo $status;
								echo $status_authorized;
								echo $status_captured;
								echo $status_voided;
								echo $show_systems;
								echo $debug;
								echo $test_mode;
								echo $uninstall_clear_settings;
								echo $uninstall_clear_db;
								echo $error_order_notification;
							?>		
							
						</div>
<!--  ******************************** Tab System End ****************************************** -->

<!--  ******************************** Tab Card Start ****************************************** -->
						<div class="tab-pane top-pane" id="tab-card">

							<div class="alert alert-success alert-dismissible" role="alert">
								<button type="button" class="close" data-dismiss="alert" aria-label="Close">
								<span aria-hidden="true">&times;</span>
								</button>
								<?php echo $a->__( 'If Stripe Checkout is disabled - this checkout method will be used. In such case, payment form will be embedded into checkout page' ); ?>
							</div>

							<?php
								echo $cvc_check;
								echo $zip_check;
								echo $address_check;
								echo $show_card_image;
								echo $form_width;
								echo $vendor_image_form;
								echo $vendor_image_form_width;
								echo $card_name;
								echo $remember_me;
								echo $saved_card_secret;
								echo $edit_cards;
								echo $log_activity;
								echo $check_customer_duplication;
								echo $pc_number_input;
							?>	

						</div>
<!--  ******************************** Tab Card End ******************************************** -->

<!--  ******************************** Stripe checkout Start *********************************** -->
						<div class="tab-pane top-pane" id="tab-stripe-checkout">

							<div class="alert alert-success alert-dismissible" role="alert">
								<button type="button" class="close" data-dismiss="alert" aria-label="Close">
								<span aria-hidden="true">&times;</span>
								</button>
								<?php echo $a->__( 'If Stripe Checkout is enabled then Stripe pop-up payment form will be used' ); ?>
							</div>

							<?php
								echo $checkout;
								echo $checkout_button_caption;
								echo $checkout_zip_code;
								echo $checkout_collect_payment;
								echo $checkout_collect_shipping;
								echo $checkout_remember_me;
								echo $checkout_bitcoin;
								echo $checkout_alipay;
								echo $checkout_alipay_reusable;
								echo $checkout_compatibility;
								echo $mobile_compatibility;
								echo $compatibility_button_text;
								echo $popup_image;
							?>

						</div>
<!--  ***************************** Stripe checkout End **************************************** -->		

<!--  ***************************** Payment button Start *************************************** -->
						<div class="tab-pane top-pane" id="tab-button">

							<div class="alert alert-success alert-dismissible" role="alert">
								<button type="button" class="close" data-dismiss="alert" aria-label="Close">
								<span aria-hidden="true">&times;</span>
								</button>
								<?php echo $a->__( 'For simple products (non-recurring, without required options) you may place "Pay in one click" button on the product page. Stripe pop-up form will be used to process payment card. Some settings (such as Bitcoin support, "Remember me" option) will be taken from "Stripe checkout" tab' ); ?>
							</div>

							<?php
								echo $button;
								echo $button_text;
								echo $button_name;
								echo $button_shipping;
								echo $describe_price;
								echo $button_text_height;
								echo $button_height;
								echo $button_radius;
								echo $button_margin_vertical;
								echo $button_margin_horizontal;
								echo $button_color;
								echo $button_text_color;
								echo $button_full_width;
							?>

						</div>
<!--  ******************************* Payment button End *************************************** -->	

<!--  ******************************* Tab Web-hook Start *************************************** -->
						<div class="tab-pane top-pane" id="tab-webhook">

							<!-- Set Web-hook endpoint Start -->
							<div class="form-group">
								<div class="col-sm-2"></div>
								<div class="col-sm-10">
									<a
										href="//dashboard.stripe.com/account/webhooks"
										target="_blank"
									>
										<?php echo $a->__( 'In order to configure Stripe\'s Web-hooks visit Stripe Dashboard' ); ?>
									</a>
								</div>
							</div>
							<!-- Set Web-hook endpoint End -->

							<?php
								echo $webhook_url;
								echo $create_subscription_callback;
								echo $create_subscription_callback_data;
								echo $cancel_subscription_callback;
								echo $cancel_subscription_callback_data;
							?>

						</div>
<!--  ************************************ Tab Web-hook End ************************************ -->

<!--  ************************************ Tab Template Start ********************************** -->
						<div class="tab-pane top-pane wrapper-with-wait-screen" id="tab-templates">

							<?php echo $template; ?>

						</div>
<!--  ************************************ Tab Template End ************************************ -->

<!--  ************************************ Tab Plans Start ************************************* -->
						<div class="tab-pane top-pane wrapper-with-wait-screen" id="tab-plans">

							<div class="alert alert-success alert-dismissible static" role="alert">
								<button type="button" class="close" data-dismiss="alert" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
								<?php echo $a->__( 'Here you can delete or import Stripe\'s recurring plans to OpenCart' ); ?>
							</div>

							<?php echo $select_stripe_account; ?>

							<div class="wait-screen static">
								<div class="spinner-holder">
									<i class="fa fa-spinner fa-pulse wait-spinner"></i>
									<span class="msg"></span>
								</div>
							</div>

						</div>
<!-- ************************************ Tab Plans Stop *************************************** -->

<!--  *********************************** Tab Plan Profiles Start ****************************** -->
						<div class="tab-pane top-pane wrapper-with-wait-screen" id="tab-plan-profile">
							<div class="wait-screen static">
								<div class="spinner-holder">
									<i class="fa fa-spinner fa-pulse wait-spinner"></i>
									<span class="msg"></span>
								</div>
							</div>
						</div>
<!-- *********************************** Tab Plan Profiles Stop ******************************** -->

<!--  ********************************** Tab Plan Profiles Map Start *************************** -->
						<div class="tab-pane top-pane wrapper-with-wait-screen" id="tab-plan-profile-map">
							<div class="wait-screen static">
								<div class="spinner-holder">
									<i class="fa fa-spinner fa-pulse wait-spinner"></i>
									<span class="msg"></span>
								</div>
							</div>
						</div>
<!-- *********************************** Tab Plan Profiles Map Stop **************************** -->

<!--  ********************************** Tab Captions Customization Start ********************** -->
						<div class="tab-pane top-pane wrapper-with-wait-screen" id="tab-caption">
							<?php
								echo $title;
								echo $sandbox_title;
								echo $form_caption;
								echo $caption_charge_value;
								echo $caption_form_cardholder_name;
								echo $caption_form_card_nmber;
								echo $caption_form_switch_mode;
								echo $caption_form_card_expiration;
								echo $caption_form_card_cvc;
								echo $caption_form_rerember_me;
								echo $caption_form_rerember_me_description;
								echo $caption_form_make_default;
								echo $caption_form_make_default_description;
								echo $caption_form_select_card;
								echo $caption_form_card_password;
								echo $caption_form_card_password_description;
								echo $caption_wait_script;
								echo $caption_payment_error;
								echo $caption_payment_success;
								echo $caption_order_placing;
								echo $caption_token_create;
								echo $caption_button_placing;
								echo $caption_empty_card_number;
								echo $caption_unknown_vendor;
								echo $caption_forbidden_vendor;
								echo $caption_error_card_password_save;
								echo $caption_error_card_password_use;
								echo $caption_script_error;
							?>
							<div class="wait-screen static">
								<div class="spinner-holder">
									<i class="fa fa-spinner fa-pulse wait-spinner"></i>
									<span class="msg"></span>
								</div>
							</div>
						</div>
<!-- *********************************** Tab Captions Customization End ************************ -->
					</form><!-- #form -->
<!--  ********************************** Tab Support Start ************************************* -->
					<div class="tab-pane top-pane wrapper-with-wait-screen" id="tab-support">
						<div style="font-weight: bold; color: green;"><?php echo $a->__( 'Current version: %s', $version ); ?></div>
						<div id="ticket-wrapper"></div>
						<div class="wait-screen static">
							<div class="spinner-holder">
								<i class="fa fa-spinner fa-pulse wait-spinner"></i>
								<span class="msg"></span>
							</div>
						</div>
					</div>
<!-- *********************************** Tab Support End *************************************** -->
				</div><!-- .tab-content -->
			</div><!-- .panel-body -->
		</div><!--.panel .panel-default -->
	</div><!-- .container-fluid -->
	<div id="legal">
		<span>Adverti<b>k</b>on</span> &#169; 2015-<?php echo date( 'Y' );?> All Rights Reserved.<br>Version <?php echo $version; ?>
	</div>
</div><!-- #content -->
<script>
	ADK.locale = <?php echo $locale; ?>;
</script>
<?php echo $footer; ?>
