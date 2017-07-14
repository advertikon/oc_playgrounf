<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>

	<title><?php echo $this->get_template_subject( $template_content, $store_id, $lang_id ); ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0" />
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<!--[if !mso]>
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<![endif]-->

	<!--[if gte mso 15]>
	<style type="text/css">
	* {
		mso-line-height-rule: exactly;
	}
	table {
		font-size:1px;
		line-height:0;
		mso-table-lspace: 0pt;
		mso-table-rspace: 0pt;
		mso-margin-top-alt: 1px;
	}
	</style>
	<![endif]-->  

	<style>
	.imageFix{
		display:block;
	}

	p {
		padding: 0;
		margin: 0;
	}

	a {
		text-decoration: none;
	}

	a img{
		border:0 none;
	}

	table, td{
		border-collapse:collapse;
	}

	.body {
		background-color: <?php echo $bg['color']; ?>;
		background-image: <?php echo $this->get_bg_img( $bg['img']['img'], $show_img ); ?>;
		background-repeat: <?php echo $bg['img']['repeat']; ?>;
		width: 100%;
		height: 100%;
		min-width: 100%;
		margin:0;
		padding:0;
		-webkit-text-size-adjust:100%;
		-ms-text-size-adjust:100%;
		font-family:Arial,serif;
		border-width: 0;
	}

	.body img {
		/*display:block;*/
		border-width:0;
		margin:0;
		padding:0;
		-ms-interpolation-mode:bicubic;
		border:0 none;
		height:auto;
		line-height:100%;
		outline:none;
		text-decoration:none;
	}

	.wrapper {
		width: <?php echo $template['width']['value'] . $template['width']['units'] ?>;
		min-width: <?php echo $template['width']['value'] . $template['width']['units'] ?>;
		max-width: <?php echo $template['width']['value'] . $template['width']['units'] ?>;

		<?php if( '%' === $template['width']['units'] ) : ?>
		max-width: <?php echo $template['range']['max'] . 'px'; ?>;
		min-width: <?php echo $template['range']['min'] . 'px'; ?>;
		<?php endif; ?>
	}

	.container {
		width: 100%;
	}

	.top {
		height: <?php echo $top['height']['value'] . $top['height']['units'] ?>;
		background-color: <?php echo $top['bg']['color']; ?>;
		font-size: <?php echo $top['text']['size']['value'] . $top['text']['size']['units']; ?>;
		background-image: <?php echo $this->get_bg_img( $top['bg']['img']['img'], $show_img ); ?>;
		color: <?php echo $top['text']['color']; ?>;
	}

	.top a {
		font-size: <?php echo $top['link']['size']['value'] . $top['link']['size']['units']; ?>;
		color: <?php echo $top['link']['color']; ?>;
	}

	.top-text {
		color: <?php echo $top['text']['color']; ?>;
		text-align: <?php echo $top['text']['align']; ?>;
		vertical-align: <?php echo $top['text']['valign']; ?>;
	}

	.header {
		height: <?php echo $header['height']['value'] . $header['height']['units'] ?>;
		background-color: <?php echo $header['bg']['color']; ?>;
		font-size: <?php echo $header['text']['size']['value'] . $header['text']['size']['units']; ?>;
		background-image: <?php echo $this->get_bg_img( $header['bg']['img']['img'], $show_img ); ?>;
	}

	.header a {
		color: <?php echo $header['link']['color']; ?>;
		font-size: <?php echo $header['link']['size']['value'] . $header['link']['size']['units']; ?>;
	}

	.header-logo {
		text-align: <?php echo $header['logo']['align']; ?>;
		vertical-align: <?php echo $header['logo']['valign']; ?>;
	}

	.header-logo img {
		width: <?php echo $header['logo']['width']['value'] . $header['logo']['width']['units'] ?>;
	}

	.header-text {
		color: <?php echo $header['text']['color']; ?>;
		text-align: <?php echo $header['text']['align']; ?>;
		vertical-align: <?php echo $header['text']['valign']; ?>;
	}

	.content {
		font-size: <?php echo $content['text']['size']['value'] . $content['text']['size']['units']; ?>;
		color: <?php echo $content['text']['color']; ?>;
		text-align: <?php echo $content['text']['align']; ?>;
		vertical-align: <?php echo $content['text']['valign']; ?>;
	}

	.content a {
		color: <?php echo $content['link']['color']; ?>;
		font-size: <?php echo $content['link']['size']['value'] . $content['link']['size']['units']; ?>;
	}

	.content-wrapper {
		background-color: <?php echo $content['bg']['color']; ?>;
		background-image: <?php echo $this->get_bg_img( $content['bg']['img']['img'], $show_img ); ?>;
		width:  <?php echo $columns['width']; ?>%;
		background-repeat: <?php echo $content['bg']['img']['repeat']; ?>;
	}

	.sidebar {
		font-size: <?php echo $sidebar['text']['size']['value'] . $sidebar['text']['size']['units']; ?>;
		color: <?php echo $sidebar['text']['color']; ?>;
		text-align: <?php echo $sidebar['text']['align']; ?>;
		vertical-align: <?php echo $sidebar['text']['valign']; ?>;
	}

	.sidebar-wrapper {
		width:  <?php echo ( 100 - $columns['width'] ); ?>%;
		background-color: <?php echo $sidebar['bg']['color']; ?>;
		background-image: <?php echo $this->get_bg_img( $sidebar['bg']['img']['img'], $show_img ); ?>;
		background-repeat: <?php echo $sidebar['bg']['img']['repeat']; ?>;
	}

	.content table,
	.sidebar table {
		width: 100%;
	}

	.content p,
	.sidebar p {
		margin: 0;
		padding: 0;
	}

	.footer {
		background-color: <?php echo $footer['bg']['color']; ?>;
		font-size: <?php echo $footer['text']['size']['value'] . $footer['text']['size']['units']; ?>;
		background-image: <?php echo $this->get_bg_img( $footer['bg']['img']['img'], $show_img ); ?>;
		background-repeat: <?php echo $footer['bg']['img']['repeat']; ?>;
		height: <?php echo $footer['height']['value'] . $footer['height']['units']; ?>;
	}

	.footer a {
		color: <?php echo $footer['link']['color']; ?>;
		font-size: <?php echo $footer['link']['size']['value'] . $footer['link']['size']['units']; ?>;
	}

	.footer-text {
		color: <?php echo $footer['text']['color']; ?>;
		text-align: <?php echo $footer['text']['align']; ?>;
		vertical-align: <?php echo $footer['text']['valign']; ?>;
	}

	.bottom {
		height: <?php echo $bottom['height']['value'] . $bottom['height']['units'] ?>;
		background-color: <?php echo $bottom['bg']['color']; ?>;
		font-size: <?php echo $bottom['text']['size']['value'] . $bottom['text']['size']['units']; ?>;
		background-image: <?php echo $this->get_bg_img( $bottom['bg']['img']['img'], $show_img ); ?>;
	}

	.bottom a {
		color: <?php echo $bottom['link']['color']; ?>;
		font-size: <?php echo $bottom['link']['size']['value'] . $bottom['link']['size']['units']; ?>;
	}

	.bottom-text {
		color: <?php echo $bottom['text']['color']; ?>;
		text-align: <?php echo $bottom['text']['align']; ?>;
		vertical-align: <?php echo $bottom['text']['valign']; ?>;
	}

	.spacer {
		font-size: 0;
		line-height: 0;
	}

	.spacer-top-top {
		height: <?php echo $top['margin']['top']['size']['value'] . $top['margin']['top']['size']['units']; ?>;
	}

	.spacer-header-top {
		height: <?php echo $header['margin']['top']['size']['value'] . $header['margin']['top']['size']['units']; ?>;
	}

	.spacer-content-top {
		height: <?php echo $content['margin']['top']['size']['value'] . $content['margin']['top']['size']['units']; ?>;
	}

	.spacer-footer-top {
		height: <?php echo $footer['margin']['top']['size']['value'] . $footer['margin']['top']['size']['units']; ?>;
	}

	.spacer-footer-bottom {
		height: <?php echo $footer['margin']['bottom']['size']['value'] . $footer['margin']['bottom']['size']['units']; ?>;
	}

	.spacer-bottom-bottom {
		height: <?php echo $bottom['margin']['bottom']['size']['value'] . $bottom['margin']['bottom']['size']['units']; ?>;
	}

	.column-spacer {
		min-width: 5px;
	}

	.vitrine-element {
		width: 150px;
		display:inline-block;
	}

	.social-element {
		display: inline-block;
	}

	.full-width {
		min-width: 100%;
		width: 100%;
	}
	</style> 

	<?php if( '%' === $template['width']['units'] ) : ?>
	<style>
	@media only screen and (min-width: <?php echo ( $template['range']['max'] + 1 ); ?>px) {
		.wrapper {width: <?php echo $template['range']['max']; ?>px!important;}
	}

	@media only screen and (max-width: <?php echo ( $template['range']['min'] ); ?>px) {
		.wrapper {
			width: <?php echo $template['range']['min']; ?>px!important;
			min-width: <?php echo $template['range']['min']; ?>px!important;
		}
	}

	@media only screen and (min-width: 481px) {
		.vitrine-element {
			width: 125px!important;
		}
	}

	@media only screen and (max-width: 480px) {
		.content-wrapper,
		.sidebar-wrapper,
		.column-spacer {
			width: 100%!important;
			display:block!important;
		}

		.column-spacer {
			height: 5px;
		}
	}
	</style> 
	<?php endif; ?>

	</head>
	<body class="body full-width" marginwidth="0" marginheight="0" leftmargin="0" topmargin="0" style="overflow-y:hidden;">

		<!-- Start Background -->
		<table class="body full-width" cellpadding="0" cellspacing="0" style="border-collapse: collapse;" align="center" >
			<tr>
				<td class="full-width" width="100%" valign="top" align="center"> 

				<!--[if (gte mso 9)|(IE)]>
				<table class="ie-width" align="center" cellpadding="0" cellspacing="0" border="0" <?php if( '%' === $template['width']['units'] )echo 'width="' . $template['range']['max'] . 'px"'; ?> >
					<tr>
						<td>
				<![endif]-->

					<!-- Start Wrapper  -->
					<table class="wrapper" cellpadding="0" cellspacing="0" border="0" align="center" >
						<tr>
							<td class="spacer spacer-top-top">&nbsp;</td><!-- Spacer -->
						</tr>
						<tr>
							<td align="center">

								<!-- Start Container  -->
								<table class="container full-width" cellpadding="0" cellspacing="0" border="0" width="100%">
									<tr>
										<td colspan="3" class="spacer spacer-top-top full-width">&nbsp;</td>
									</tr>
									<tr>
										<td colspan="3">
											<table class="top full-width" width="100%" border="0" cellpadding="5" cellspacing="0">
												<tr>
													<td class="top-text">
														<?php echo $this->get_top_content( $top ); ?>
													</td>
												</tr>
											</table>
										</td>
									</tr>
									<tr>
										<td ccolspan="3" class="spacer spacer-header-top full-width">&nbsp;</td>
									</tr>
									<tr>
										<td colspan="3">
											<table class="header full-width" width="100%" border="0" cellpadding="5" cellspacing="0">
												<?php if( $header['logo']['logo'] ) : ?>
												<tr>
													<td class="header-logo">
														<a>
															<img src="<?php echo ( ! $show_img ? '' : ( defined( 'HTTPS_CATALOG' ) ? HTTPS_CATALOG : HTTPS_SERVER ) . 'image/' . $header['logo']['logo'] ); ?>"/>
														</a>
													</td>
												</tr>
												<?php endif; ?>
												<?php if( $this->has_content( $header ) ) : ?>
												<tr>
													<td class="header-text">
														<?php echo $this->get_header_content( $header ); ?>
													</td>
												</tr>
												<?php endif; ?>
											</table>
										</td>
									</tr>
									<tr>
										<td colspan="3" class="spacer spacer-content-top full-width">&nbsp;</td>
									</tr>
									<tr>
										<td class="content-wrapper" >
											<table class="content" width="100%" border="0" cellpadding="5" cellspacing="0">
												<tr>
													<td>
														<?php echo $this->get_template_content( $template_content, 'content', $store_id, $lang_id ); ?>
													</td>
												</tr>
											</table>
										</td>
										<td class="spacer column-spacer">&nbsp;</td>
										<td class="sidebar-wrapper" >
											<table class="sidebar" width="100%" border="0" cellpadding="5" cellspacing="0">
												<tr>
													<td>
														<?php echo $this->get_template_content( $template_content, 'sidebar', $store_id, $lang_id ); ?>
													</td>
												</tr>
											</table>
										</td>
									</tr>
									<tr>
										<td colspan="3" class="spacer spacer-footer-top">&nbsp;</td>
									</tr>
									<tr>
										<td colspan="3">
											<table class="footer" width="100%" border="0" cellpadding="5" cellspacing="0">
												<tr>
													<td class="footer-text">
														<?php echo $this->get_footer_content( $footer ); ?>
													</td>
												</tr>
											</table>
										</td>
									</tr>
									<tr>
										<td colspan="3" class="spacer spacer-footer-bottom">&nbsp;</td>
									</tr>
									<tr>
										<td colspan="3">
											<table class="bottom" width="100%" border="0" cellpadding="5" cellspacing="0">
												<tr>
													<td class="bottom-text">
														<?php echo $this->get_bottom_content( $bottom ); ?>
													</td>
												</tr>
											</table>
										</td>
									</tr>
								</table>
								<!-- Start Container  -->                   

							</td>
						</tr>
						<tr>
							<td ccolspan="3" lass="spacer spacer-bottom-bottom">&nbsp;</td>
						</tr>                        
					</table> 
					<!-- End Wrapper  -->  

					<!--[if (gte mso 9)|(IE)]>
							</td>
						</tr>
					</table>
					<![endif]-->

				</td>
			</tr>
		</table>
		<!-- End Background -->

	</body>
</html>
