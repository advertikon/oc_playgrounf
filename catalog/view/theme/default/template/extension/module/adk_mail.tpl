<?php echo $header; ?>
<?php echo $column_left; ?> 
<div id="content" class="container">
	<div class="page-header">
		<div class="container-fluid">
			
			<h1><?php echo $title; ?></h1>
			<ul class="breadcrumb">
				<?php foreach ($breadcrumbs as $breadcrumb) : ?>
				<li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
	<div class="container-fluid">
		<?php if( ! empty( $error_warning ) ) : ?>
			<div class="alert alert-danger" role="alert">
				<i class="fa fa-exclamation-circle"></i> <?php echo $helper->__( 'Error' ); ?>
			</div>
			<div><?php echo $error_warning; ?></div>
		<?php elseif( ! empty( $success ) ) : ?>
			<div class="alert alert-success" role="alert">
				<i class="fa fa-exclamation-circle"></i> <?php echo $helper->__( 'Success' ); ?>
			</div>
			<div><?php echo $success; ?></div>
		<?php endif; ?>
	</div>
</div><!-- #content -->
<?php echo $footer; ?>
