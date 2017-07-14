<?php echo $header; ?>
<?php echo $column_left; ?> 
<div id="content">
	<div class="page-header">
		<div class="container-fluid">
			<div class="pull-right">
				<button
					type="submit"
					id="f-submit"
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
				<?php echo $name; ?>
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

		<?php if( ! empty( $error ) ) : ?>
		<div class="alert alert-danger alert-dismissible" role="alert">
			<i class="fa fa-exclamation-circle"></i> <?php echo $error; ?>
			<button type="button" class="close" data-dismiss="alert">&times;</button>
		</div>
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
					<i class="fa fa-pencil"></i> <?php echo $a->__( 'Settings' ); ?>
				</h3>
			</div>
			<div class="tab-content" style="overflow: hidden;">
				<form id="main" action="<?php echo $action; ?>" method="post">
					<?php echo $status; ?>
				</form>
			</div>
		</div>		
	</div><!-- .container-fluid -->
	<div class="legal">
		Version <?php echo $version; ?>
	</div>
</div><!-- #content -->
<script>
	ADK.locale = <?php echo $locale; ?>;
	$( "#f-submit" ).on( "click", function() { document.getElementById( "main").submit(); } );
</script>
<?php echo $footer; ?>
