<?php echo $header; ?>
<?php echo $column_left; ?> 
<div id="content">
	<div class="page-header">
		<div class="container-fluid">
			<div class="pull-right">
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
				<?php echo $a->__( 'xxxxxx' ); ?>
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
					<i class="fa fa-pencil"></i> <?php echo $a->__( 'XXXXXX Settings' ); ?>
				</h3>
			</div>
			<div class="panel-body">
				<ul class="nav nav-tabs">
					<li class="active sway-able">
						<a href="#tab-1" data-toggle="tab-top">
							<i class="fa fa-terminal tab-icon"> </i>
							<?php echo $a->__( 'XXXXXXX' ); ?>
						</a>
					</li>
					<li class="sway-able">
						<a href="#tab-2" data-toggle="tab-top">
							<i class="fa fa-wrench tab-icon"> </i>
							<?php echo $a->__( 'XXXXXX' ); ?>
						</a>
					</li>
					<li class="sway-able">
						<a href="#tab-3" data-toggle="tab-top">
							<i class="fa fa-cogs tab-icon"> </i>
							<?php echo $a->__( 'XXXXXX' ); ?>
						</a>
					</li>
					<li class="sway-able">
						<a href="#tab-support" data-toggle="tab-top">
							<i class="fa fa-cogs tab-icon"> </i>
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

<!--  ************************************ Tab XXXXXX Start ************************************ -->
						<div class="tab-pane active top-pane" id="tab-1">

							Tab 1

						</div>
<!--  *********************************** Tab XXXXXX End *************************************** -->

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
