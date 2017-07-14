<?php echo $header; ?>
<?php echo $column_left; ?> 
<div id="content" class="full-height">
	<div class="page-header">
		<div class="container-fluid">
			<div class="pull-right">

				<a href="<?php echo $cancel; ?>"
					data-toggle="tooltip"
					title="<?php echo $a->__( 'Back to modules' ); ?>"
					class="btn btn-default"
				>
					<i class="fa fa-reply"></i>
				</a>
			</div>
			<h1><?php echo $name ?></h1>
			<ul class="breadcrumb">
				<?php foreach ($breadcrumbs as $breadcrumb) : ?>
				<li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
	<div class="container-fluid">
		<?php if( ! empty( $error_warning ) ) : ?>
			<div class="alert alert-danger alert-dismissible" role="alert">
				<i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
				<button type="button" class="close" data-dismiss="alert">&times;</button>
			</div>
		<?php endif; ?>
		<div class="alert alert-danger alert-dismissible extension-status" role="alert">
				<i class="fa fa-exclamation-circle"></i> <?php echo $a->__( 'Extension is disabled' ); ?>
				<button type="button" class="close" data-dismiss="alert">&times;</button>
			</div>
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $a->__( '%s settings' , $name ); ?></h3>
			</div>
			<div class="panel-body main-panel-body">
				<form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-profile" target="preview" class="form-horizontal">

				<?php echo $a->r()->render_panels_headers( $panels ); ?>

					<div class="tab-content">

						<div class="tab-pane active extension-tab" id="profiles-manager">
							<div class="wrapper-with-wait-screen">
								<?php echo $profile_tip; ?>
								<?php echo $profiles; ?>
								<?php echo $send; ?>
								<div id="profile-controls"></div>
								<div class="wait-screen">
									<div class="spinner-holder">
										<i class="wait-spinner fa fa-spinner fa-pulse"></i>'
									</div>
								</div>
							</div>
						</div>

						<div class="tab-pane extension-tab" id="contents-manager">
							<div class="wrapper-with-wait-screen">
								<?php echo $contents_tip; ?>
								<div class="panel panel-default">
									<div class="panel-heading" data-toggle="collapse" href="#add-template-body" style="cursor: pointer;">
										<h3 class="panel-title"><i class="fa fa-chevron-down"></i> <?php echo $a->__( 'Add template' ); ?></h3>
									</div>
									<div id="add-template-body" class="panel-body collapse">
										<?php echo $create_template_tip; ?>
										<?php echo $add_template_row_1; ?>
										<?php echo $add_template_row_2; ?>
										<?php echo $add_template_row_3; ?>
										<?php echo $add_template_row_4; ?>
									</div>
								</div>
								<?php echo $templates; ?>
								<div id="template-controls"></div>
								<div class="wait-screen">
									<div class="spinner-holder">
										<i class="wait-spinner fa fa-spinner fa-pulse"></i>'
									</div>
								</div>
							</div>
						</div>

						<div class="tab-pane extension-tab" id="shortcodes-manager">
							<div class="wrapper-with-wait-screen">
								<?php echo $shortcode_tip; ?>
								<?php echo $shortcodes; ?>
								<div id="template-controls"></div>
								<div class="wait-screen">
									<div class="spinner-holder">
										<i class="wait-spinner fa fa-spinner fa-pulse"></i>'
									</div>
								</div>
							</div>
						</div>

						<div class="tab-pane extension-tab" id="profile-mapping">
							<div class="wrapper-with-wait-screen">
								<?php echo $mapping_tip; ?>

								<div class="panel panel-default">
									<div class="panel-heading"><?php echo $a->__( 'Store level' ); ?></div>
									<div class="panel-body">
										<?php echo $store_mapping; ?>
									</div>
								</div>

								<div class="panel panel-default">
									<div class="panel-heading"><?php echo $a->__( 'Language level' ); ?></div>
									<div class="panel-body">
										<?php echo $lang_mapping; ?>
									</div>
								</div>

								<div class="panel panel-default">
									<div class="panel-heading">
										<?php echo $a->__( 'Template level' ); ?>
									</div>
									<div class="container-fluid">
										<?php echo $missed_order_templates; ?>
										<?php echo $missed_return_templates; ?>
									</div>
									<div class="panel-body">
										<?php echo $template_mapping; ?>
									</div>
								</div>

								<div class="wait-screen">
									<div class="spinner-holder">
										<i class="wait-spinner fa fa-spinner fa-pulse"></i>'
									</div>
								</div>
							</div>
						</div>

						<div class="tab-pane extension-tab" id="shortcodes-list">
							<div class="wrapper-with-wait-screen">
								<?php echo $shortcode_list_tip; ?>
								<?php echo $context_list; ?>
								<table class="table table-striped">
									<thead>
										<tr>
											<th>#</th>
											<th><?php echo $a->__( 'Code' ); ?></th>
											<th><?php echo $a->__( 'Description' ); ?></th>
											<th><?php echo $a->__( 'Context' ); ?></th>
									</thead>
								<?php $s_count = 0; ?>
								<?php foreach( $shortcodes_list as $shortcode ) : ?>
									<tr>
										<td class="filter-number"><?php echo ++$s_count; ?></td>
										<td><?php echo $shortcode['shortcode']; ?></td>
										<td><?php echo $shortcode['description']; ?></td>
										<td class="filter-context"><?php echo implode( ',', $shortcode['context']); ?></td>
										</tr>
								<?php endforeach; ?>
								</table>
								<div class="wait-screen">
									<div class="spinner-holder">
										<i class="wait-spinner fa fa-spinner fa-pulse"></i>'
									</div>
								</div>
							</div>
						</div>

						<div class="tab-pane extension-tab" id="settings">
							<div class="wrapper-with-wait-screen">
								<?php 
								      echo $settings_tip;
								      echo $status;
								      echo $hints_on;
								      echo $archive_size;
								      echo $extended_newsletter;
								      echo $throttle_item;
								      echo $throttle_traffic;
								      echo $auto_update;
								?>
								<div id="template-controls"></div>
								<div class="wait-screen">
									<div class="spinner-holder">
										<i class="wait-spinner fa fa-spinner fa-pulse"></i>'
									</div>
								</div>
							</div>
						</div>

						<div class="tab-pane extension-tab" id="newsletter-pane">
							<div class="wrapper-with-wait-screen">
								<?php 
									echo $newsletter_tip;
									echo $a->r()->render_panels_headers( $newsletter_panels );
								?>
								<div class="tab-content">
									<div class="tab-pane active" id="newsletter-list-pane">
										<div class="wrapper-with-wait-screen table-overall">
											<div id="newsletter-filter" class="table-filter-wrapper">
												<?php echo $newsletter_filter; ?>
											</div>
											<div id="newsletter-list" data-url="<?php echo $newsletter_url; ?>"></div>
											<div class="wait-screen">
												<div class="spinner-holder">
													<i class="wait-spinner fa fa-spinner fa-pulse"></i>
												</div>
											</div>
										</div>
									</div>

									<div class="tab-pane" id="newsletter-manage-pane">
										<div class="wrapper-with-wait-screen">
											<?php echo $select_newsletter; ?>
											<div id="newsletter-controls" data-url="<?php echo $newsletter_controls_url; ?>"></div>
											<div class="wait-screen">
												<div class="spinner-holder">
													<i class="wait-spinner fa fa-spinner fa-pulse"></i>
												</div>
											</div>
										</div>
									</div>

									<div class="tab-pane" id="newsletter-add-pane">
										<div class="wrapper-with-wait-screen">
											<?php echo $add_newsletter; ?>
											<div class="wait-screen">
												<div class="spinner-holder">
													<i class="wait-spinner fa fa-spinner fa-pulse"></i>
												</div>
											</div>
										</div>
									</div>

									<div class="tab-pane" id="newsletter-form-pane">
										<div class="wrapper-with-wait-screen fix-parent">
											<?php echo $form_newsletter; ?>
											<div class="wait-screen">
												<div class="spinner-holder">
													<i class="wait-spinner fa fa-spinner fa-pulse"></i>
												</div>
											</div>
										</div>
									</div>

									<div class="tab-pane" id="newsletter-caption-pane">
										<div class="wrapper-with-wait-screen">
											<?php echo $newsletter_caption; ?>
											<div class="wait-screen">
												<div class="spinner-holder">
													<i class="wait-spinner fa fa-spinner fa-pulse"></i>
												</div>
											</div>
										</div>
									</div>

								</div>
								<div class="wait-screen">
									<div class="spinner-holder">
										<i class="wait-spinner fa fa-spinner fa-pulse"></i>'
									</div>
								</div>
							</div>
						</div>

						<div class="tab-pane extension-tab" id="history-pane">
							<div class="wrapper-with-wait-screen table-overall">
								<?php // echo $mapping_tip; ?>

								<div id="history-filter" class="table-filter-wrapper">
									<?php echo $history_filter; ?>
								</div>

								<div id="history-content" data-url="<?php echo $history_url; ?>"><?php echo $history; ?></div>

								<div class="wait-screen">
									<div class="spinner-holder">
										<i class="wait-spinner fa fa-spinner fa-pulse"></i>'
									</div>
								</div>
							</div>
						</div>

						<div class="tab-pane extension-tab" id="queue-pane">
							<div class="wrapper-with-wait-screen">
								<?php echo $queue_tip; ?>
								<?php echo $queue_content; ?>
								<div class="wait-screen">
									<div class="spinner-holder">
										<i class="wait-spinner fa fa-spinner fa-pulse"></i>'
									</div>
								</div>
							</div>
						</div>

						<div class="tab-pane extension-tab" id="bounced-emails">
							<div class="wrapper-with-wait-screen">
								<?php echo $bounce_tip; ?>
								<?php echo $bounce_content; ?>
								<div class="wait-screen">
									<div class="spinner-holder">
										<i class="wait-spinner fa fa-spinner fa-pulse"></i>'
									</div>
								</div>
							</div>
						</div>

					</div><!-- .tab-content -->
				</form><!-- #form -->
			</div><!-- .panel-body -->
		</div><!--.panel .panel-default -->
		<?php echo $preview_tip; ?>
		<div class="preview-wrapper">
			<nav class="navbar navbar-default">
				<div class="container-fluid">
					<div class="navbar-header">
						<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
						</button>
						<i class="fa fa-tv fa-2x" style="margin-top:12px"> Live Preview</i>
					</div>
					<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
						<form class="navbar-form navbar-right">
							<?php echo $preview_controls; ?>
						</form>
					</div>
				</div>
			</nav>
			<div class="iframe-wrapper">
				<iframe id="preview" class="preview-iframe" src=""></iframe>
				<div class="wait-screen">
					<div class="spinner-holder">
						<i class="wait-spinner fa fa-spinner fa-pulse"></i>'
					</div>
				</div>
			</div>
		</div> <!-- .preview-wrapper -->
	</div><!-- .container-fluid -->
	<div class="legal">
		<span>Adverti<b>k</b>on</span> &#169; 2015-<?php echo date( 'Y' );?> All Rights Reserved.<br>Version <?php echo $version; ?>
	</div>
</div><!-- #content -->
<script>
ADK.locale=<?php echo $locale; ?>;
</script>
<?php echo $footer; ?>
