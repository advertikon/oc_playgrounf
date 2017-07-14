.w<?php echo $id; ?>-adk-widget-content{
	width: <?php echo $width; ?>;
	background-color: <?php echo $background_color; ?>;
	padding: 15px;
	border-radius: <?php echo $border_radius; ?>;
	box-shadow: <?php echo $box_shadow_x . ' ' . $box_shadow_y . ' ' . $box_shadow_dispersion; ?>;
	position: relative;
	z-index: 5;
}
.w<?php echo $id; ?>-adk-widget-caption{
	font-weight: bold;
	font-size: <?php echo $title_height; ?>;
	color: <php echo $title_color; ?>;
	text-align: center;

}
.w<?php echo $id; ?>-adk-widget-content input {
	width: 100%;
	background-color: <?php echo $field_background_color; ?>;
	padding: 5px;
	border-radius: <?php echo $field_border_radius; ?>;
}
.w<?php echo $id; ?>-adk-widget-content label {
	color: <?php echo $caption_color; ?>;
	font-size: <?php echo $caption_height; ?>;
	width: 100%;
}
.w<?php echo $id; ?>-adk-widget-content button {
	margin: 10px auto;
	padding: 10px;
	background-color: <?php echo $button_color; ?>;
	color: <?php echo $button_text_color; ?>;
	font-size: <?php echo $button_text_height; ?>;
	border: solid 1px <?php echo $button_border_color; ?>;
	border-radius: <?php echo $button_border_radius; ?>;
	display: block;
}
