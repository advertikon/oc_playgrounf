( function() {
	var
		form = document.createElement( "form" ),
		w_content = document.createElement( "div" ),
		caption = document.createElement( "div" ),
		captionText = document.createTextNode( "<?php echo $title; ?>" ),
		nameLabel = document.createElement( "label" ),
		nameLabelText = document.createTextNode( "<?php echo $a->__( 'Name' ); ?>" ),
		emailLabel = document.createElement( "label" ),
		emailLabelText = document.createTextNode( "<?php echo $a->__( 'E-mail' ); ?>" ),
		name = document.createElement( "input" ),
		email = document.createElement( "input" ),
		button = document.createElement( "button" ),
		buttonText = document.createTextNode( "<?php echo $button_text; ?>" ),
		target = document.getElementById( "adk-widget-span" ),
		widgetId = document.createElement( "input" );

	function adk_ns_check( f ) {
		var
			e = null,
			ret = true;

		for( var i = 0, len = f.elements.length; i < len; i++ ) {
			e = f.elements[ i ];

			switch( e.id ) {
			case 'w<?php echo $id; ?>-adk-widget-name' :
				if ( "" === e.value ) {
					alert( "{$invalid_name}" );
					ret = false;
				}
				break;
			case 'w<?php echo $id; ?>-adk-widget-email' :
				if ( "" === e.value || !/[A-Za-z0-9][A-Za-z0-9_+-]*@[A-Za-z0-9_+-]+\.[A-Za-z]{2,4}/.test( e.value ) ) {
					alert( "<?php echo $invalid_email; ?>" );
					ret = false;
				}
				break;
			}
		}

		return ret;
	}

	if ( target && target.parentNode ) {
		target.parentNode.insertBefore( form, target );
		form.appendChild( w_content );
		w_content.appendChild( caption );
		w_content.appendChild( nameLabel );
		w_content.appendChild( name );
		w_content.appendChild( emailLabel );
		w_content.appendChild( email );
		w_content.appendChild( button );
		w_content.appendChild( widgetId );

		form.target = "_blank";
		form.method = "POST";
		form.action = "<?php echo $action; ?>";
		form.onsubmit = function(){return adk_ns_check( this )};

		w_content.className = "w<?php echo $id; ?>-adk-widget-content";

		caption.className = "w<?php echo $id; ?>-adk-widget-caption";
		caption.appendChild( captionText );

		nameLabel.for = "w<?php echo $id; ?>-adk-widget-name";
		nameLabel.appendChild( nameLabelText );

		name.id = "w<?php echo $id; ?>-adk-widget-name";
		name.name = "name";

		emailLabel.for = "w<?php echo $id; ?>-adk-widget-email";
		emailLabel.appendChild( emailLabelText );

		email.id = "w<?php echo $id; ?>-adk-widget-email";
		email.name = "email";

		button.type = "submit";
		button.appendChild( buttonText );

		widgetId.value = "<?php echo $id; ?>";
		widgetId.type = "hidden";
		widgetId.name = "widget_id";
	}
} )();
