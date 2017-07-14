<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>

	<title><?php echo $this->get_template_subject( $template_content, $store_id, $lang_id ); ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0" />
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<!--[if !mso]><!-- -->
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<!-- <![endif]-->

	<!--[if gte mso 12]>
	<style type="text/css">
		* {
			mso-line-height-rule: exactly;
		}
		table {
			font-size:1px;
			mso-table-lspace: 0pt;
			mso-table-rspace: 0pt;
			mso-margin-top-alt: 1px;
			mso-table-lspace: 0pt;
			mso-table-rspace: 0pt;
			mso-margin-top-alt: 1px;
			line-height: 100%;
			height:100%;
		}
		html * {
			margin: 1px;
			line-height: 1em;
			height: 1em;
		}
		p {
			line-height: 100%;/*1em*/;
			height: 100%;/*1em;*/
		}
	</style>
	<![endif]-->

	<style>
	.imageFix{
		display:block;
	}

	* {
		mso-line-height-rule: exactly;
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

	.body{
		background-color: <?php echo $bg['color']; ?>;
	}

	.body-all {
		background-image: <?php echo $this->get_bg_img( $bg['img']['img'], ! empty( $bg['img']['embed'] ) ); ?>;
		background-repeat: <?php echo $bg['img']['repeat']; ?>;
	}

	.body img {
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
		width: <?php echo $template['width']['value'] . $template['width']['units'] ?>!important;

		<?php if( '%' === $template['width']['units'] ) : ?>
		max-width: <?php echo $template['range']['max'] . 'px'; ?>;
		min-width: <?php echo $template['range']['min'] . 'px'; ?>;
		<?php endif; ?>
	}

	.container {
		width: 100%;
	}

	.header {
		height: <?php echo $header['height']['value'] . $header['height']['units'] ?>;
		background-color: <?php echo $header['bg']['color']; ?>;
		background-image: <?php echo $this->get_bg_img( $header['bg']['img']['img'], ! empty( $header['bg']['img']['embed'] ) ); ?>;
		background-repeat: <?php echo $header['bg']['img']['repeat']; ?>;
	}

	.header-text a {
		color: <?php echo $header['link']['color']; ?>;
		font-size: <?php echo $header['link']['size']['value'] . $header['link']['size']['units']; ?>;
	}

	.header-logo {
		text-align: <?php echo $header['logo']['align']; ?>;
		vertical-align: <?php echo $header['logo']['valign']; ?>;
	}

	.header-logo a {
		display: block;
		font-size: 0;
	}

	.header-logo img {
		width: <?php echo $header['logo']['width']['value'] . $header['logo']['width']['units'] ?>;
	}

	.header-text {
		font-size: <?php echo $header['text']['size']['value'] . $header['text']['size']['units']; ?>;
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
		background-image: <?php echo $this->get_bg_img( $content['bg']['img']['img'], ! empty( $content['bg']['img']['embed'] ) ); ?>;
		background-repeat: <?php echo $content['bg']['img']['repeat']; ?>;
	}

	.content table {
		width: 100%;
	}

	.content p {
		margin: 0;
		padding: 0;
	}

	.footer {
		background-color: <?php echo $footer['bg']['color']; ?>;
		font-size: <?php echo $footer['text']['size']['value'] . $footer['text']['size']['units']; ?>;
		height: <?php echo $footer['height']['value'] . $footer['height']['units']; ?>;
		background-image: <?php echo $this->get_bg_img( $footer['bg']['img']['img'], ! empty( $footer['bg']['img']['embed'] ) ); ?>;
		background-repeat: <?php echo $footer['bg']['img']['repeat']; ?>;
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


	.spacer {
		font-size: 0;
		line-height: 0;
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
	</style> 

	<?php if( '%' === $template['width']['units'] ) : ?>
	<style>
	@media only screen and (min-width: <?php echo ( $template['range']['max'] + 1 ); ?>px) {
		.wrapper {width: <?php echo $template['range']['max']; ?>px!important;}
	}
	@media only screen and (min-width: 481px) {
		.vitrine-element {
			width: 125px!important;
		}
	}
	</style> 
	<?php endif; ?>

	</head>
	<body class="bodyy" marginwidth="0" marginheight="0" leftmargin="0" topmargin="0">

		<!-- Start Background -->
		<!--[if mso]>
		<table class="body-o body" cellpadding="0" cellspacing="0" style="border-collapse: collapse;" align="center">
		<![endif]-->
		<!--[if !mso]>--><!-- -->
		<table class="body-all body" cellpadding="0" cellspacing="0" style="border-collapse: collapse;" align="center">
		<!--<![endif]-->
			<tr>
				<td width="100%" valign="top" align="center"> 

				<!--[if (gte mso 9)|(IE)]>
				<table class="ie-width" align="center" cellpadding="0" cellspacing="0" border="0" <?php if( '%' === $template['width']['units'] )echo 'width="' . $template['range']['max'] . 'px"'; ?> >
					<tr>
						<td>
				<![endif]-->

					<!-- Start Wrapper  -->
					<table class="wrapper" cellpadding="0" cellspacing="0" border="0" align="center" >
						<tr>
							<td class="spacer spacer-header-top">&nbsp;</td><!-- Spacer -->
						</tr>
						<tr>
							<td align="center">

								<!-- Start Container  -->
								<table class="container" cellpadding="0" cellspacing="0" border="0">
									<tr>
										<td>
											<table class="header" width="100%" border="0" cellpadding="0" cellspacing="0">
												<?php if( $header['logo']['logo'] ) : ?>
												<tr>
													<td class="header-logo">
														<a href="<?php echo $this->get_store_url( true ); ?>">
															<img src="<?php echo $this->get_img( $header['logo']['logo'], ! empty( $header['logo']['embed'] ) ); ?>"/>
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
										<td class="spacer spacer-content-top">&nbsp;</td>
									</tr>
									<tr>
										<td class="content-wrapper">
											<table class="content" width="100%" border="0" cellpadding="20" cellspacing="0">
												<tr>
													<td>
														<?php echo $this->get_template_content( $template_content, 'content', $store_id, $lang_id ); ?>
													</td>
												</tr>
											</table>
										</td>
									</tr>
									<tr>
										<td class="spacer spacer-footer-top">&nbsp;</td>
									</tr>
									<tr>
										<td>
											<table class="footer" width="100%" border="0" cellpadding="20" cellspacing="0">
												<tr>
													<td class="footer-text">
														<?php echo $this->get_footer_content( $footer ); ?>
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
							<td class="spacer spacer-footer-bottom">&nbsp;</td><!-- Spacer -->
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
		<?php echo $this->get_tracking_pixel( $template_id ); ?>
	</body>
</html>
