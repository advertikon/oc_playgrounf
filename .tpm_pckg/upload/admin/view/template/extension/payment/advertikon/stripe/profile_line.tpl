<div class="property" data-profile-id="<?php echo ( $profile ? $profile->id : '' ); ?>">
	<div class="edit-panel" >
		<?php
			echo $delete_button;
			echo $save_button;
		?>
	</div>
	<div class="row property-item">
		<div class="col-sm-4"><?php echo $a->__( 'Name' ); ?></div> 
		<div class="col-sm-8" data-property="name">
			<input class="form-control" value="<?php echo ( $profile ? $profile->name : '' ); ?>">
		</div>
	</div>
	<?php
	if ( is_null( $profile ) ) {
		$profile = new Advertikon\Stripe\Resource\Profile();
	}

	$totals = $option->totals();
	$profile_totals = $profile->totals_to_recurring;
	$force_totals = $profile->add_force;
	?>
	<div class="row property-item">
		<div class="col-sm-4"><?php echo $a->__( 'Include to recurring payment' ); ?>
			<?php
			echo $a
			->r()
			->render_popover( $a->__( 'Select which lines from an invoice should be present in recurring payment' ) );
			?>
		</div>
		<div class="col-sm-8" data-property="totals_to_recurring">
			<div class="checkbox-group totals-to-recurring">
			<?php foreach( $totals as $val => $name ) : ?>
				<label>
					<input
						type="checkbox"
						value="<?php echo $val; ?>"
						<?php echo ( in_array( $val, $profile_totals ) ? 'checked="checked"' : '' ); ?>
						<?php echo ( preg_match( '/(.*total)|(tax.*)/', $val ) ? 'disabled="disabled"' : '' ); ?>
					>
					<?php echo $name; ?>
				</label>
			<?php endforeach; ?>
			</div>
		</div>
	</div>
	<div class="row property-item">
		<div class="col-sm-4"><?php echo $a->__( 'Add to one-time payment' ); ?>
			<?php
				echo $a->
				r()->
				render_popover( $a->__( 'Select which lines, from an invoice, should be present only in one-time charge of the first recurring payment' ) );
			?>
		</div>
		<div class="col-sm-8" data-property="add_force">
			<div class="checkbox-group add-force">
			<?php foreach( $totals as $val => $name  ) : ?>
				<label>
					<input
						type="checkbox"
						value="<?php echo $val; ?>"
						<?php echo ( in_array( $val, $force_totals ) ? 'checked="checked"' : '' ); ?>
					>
						<?php echo $name ?>
				</label>
			<?php endforeach; ?>
			</div>
		</div>
	</div>
	<div class="row property-item">
		<div class="col-sm-4"><?php echo $a->__( 'Can be canceled by customer' ); ?></div>
			<div class="col-sm-8" data-property="user_abort">'
				<label>
				<?php echo $a->r()->render_fancy_checkbox( array(
					'text_on'  => $a->__( 'Yes' ),
					'text_off' => $a->__( 'No' ),
					'value'    => $profile->user_abort,
				) ); ?>
			</label>
		</div>
	</div>
	<div class="row property-item">
		<div class="col-sm-4"><?php echo $a->__( 'Can be canceled immediately' ); ?>
			<?php
			echo $a
				->r()
				->render_popover( $a->__( 'Whether subscription can be canceled immediately or at next period end only' )
			);
			?>
		</div>
		<div class="col-sm-8" data-property="cancel_now">
			<label>
				<?php echo $a->r()->render_fancy_checkbox( array(
					'text_on'  => $a->__( 'Yes' ),
					'text_off' => $a->__( 'No' ),
					'value'    => $profile->cancel_now,
				) ); ?>
			</label>
		</div>
	</div>
	<div class="row property-item">
		<div class="col-sm-4"><?php echo $a->__( 'Consider product options' ); ?>
			<?php
			echo $a
				->r()
				->render_popover( $a->__( 'If set to Yes, then options of a product (such as color, size) will affect recurring price' )
			);
			?>
		</div>
		<div class="col-sm-8" data-property="price_options">
			<label>
				<?php echo $a->r()->render_fancy_checkbox( array(
					'text_on'  => $a->__( 'Yes' ),
					'text_off' => $a->__( 'No' ),
					'value'    => $profile->price_options,
				) ); ?>
			</label>
		</div>
	</div>
	<div class="row property-item">
		<div class="col-sm-4"><?php echo $a->__( 'Make ordinary order' ); ?>
			<?php
				echo $a->r()->render_popover(
					$a->__( 'If set to Yes, then ordinary (non-recurring) order will be created along with recurring  for this product for the first charge' )
				)
			?>
		</div>
		<div class="col-sm-8" data-property="first_order">
			<label>
				<?php echo $a->r()->render_fancy_checkbox( array(
					'text_on'  => $a->__( 'Yes' ),
					'text_off' => $a->__( 'No' ),
					'value'    => $profile->first_order,
				) ); ?>
			</label>
		</div>
	</div>
</div>
lete_button;
			echo $save_button;
		?>
	</div>
	<div class="row property-item">
		<div class="col-sm-4"><?php echo $a->__( 'Name' ); ?></div> 
		<div class="col-sm-8" data-property="name">
			<input class="form-control" value="<?php echo ( $profile ? $profile->name : '' ); ?>">
		</div>
	</div>
	<?php
	if ( is_null( $profile ) ) {
		$profile = new Advertikon\Stripe\Resource\Profile();
	}

	$totals = $option->totals();
	$profile_totals = $profile->totals_to_recurring;
	$force_totals = $profile->add_force;
	?>
	<div class="row property-item">
		<div class="col-sm-4"><?php echo $a->__( 'Include to recurring payment' ); ?>
			<?php
			echo $a
			->r()
			->render_popover( $a->__( 'Select which lines from an invoice should be present in recurring payment' ) );
			?>
		</div>
		<div class="col-sm-8" data-property="totals_to_recurring">
			<div class="checkbox-group totals-to-recurring">
			<?php foreach( $totals as $val => $name ) : ?>
				<label>
					<input
						type="checkbox"
						value="<?php echo $val; ?>"
						<?php echo ( in_array( $val, $profile_totals ) ? 'checked="checked"' : '' ); ?>
						<?php echo ( preg_match( '/(.*total)|(tax.*)/', $val ) ? 'disabled="disabled"' : '' ); ?>
					>
					<?php echo $name; ?>
				</label>
			<?php endforeach; ?>
			</div>
		</div>
	</div>
	<div class="row property-item">
		<div class="col-sm-4"><?php echo $a->__( 'Add to one-time payment' ); ?>
			<?php
				echo $a->
				r()->
				render_popover( $a->__( 'Select which lines, from an invoice, should be present only in one-time charge of the first recurring payment' ) );
			?>
		</div>
		<div class="col-sm-8" data-property="add_force">
			<div class="checkbox-group add-force">
			<?php foreach( $totals as $val => $name  ) : ?>
				<label>
					<input
						type="checkbox"
						value="<?php echo $val; ?>"
						<?php echo ( in_array( $val, $force_totals ) ? 'checked="checked"' : '' ); ?>
					>
						<?php echo $name ?>
				</label>
			<?php endforeach; ?>
			</div>
		</div>
	</div>
	<div class="row property-item">
		<div class="col-sm-4"><?php echo $a->__( 'Can be canceled by customer' ); ?></div>
			<div class="col-sm-8" data-property="user_abort">'
				<label>
				<?php echo $a->r()->render_fancy_checkbox( array(
					'text_on'  => $a->__( 'Yes' ),
					'text_off' => $a->__( 'No' ),
					'value'    => $profile->user_abort,
				) ); ?>
			</label>
		</div>
	</div>
	<div class="row property-item">
		<div class="col-sm-4"><?php echo $a->__( 'Can be canceled immediately' ); ?>
			<?php
			echo $a
				->r()
				->render_popover( $a->__( 'Whether subscription can be canceled immediately or at next period end only' )
			);
			?>
		</div>
		<div class="col-sm-8" data-property="cancel_now">
			<label>
				<?php echo $a->r()->render_fancy_checkbox( array(
					'text_on'  => $a->__( 'Yes' ),
					'text_off' => $a->__( 'No' ),
					'value'    => $profile->cancel_now,
				) ); ?>
			</label>
		</div>
	</div>
	<div class="row property-item">
		<div class="col-sm-4"><?php echo $a->__( 'Consider product options' ); ?>
			<?php
			echo $a
				->r()
				->render_popover( $a->__( 'If set to Yes, then options of a product (such as color, size) will affect recurring price' )
			);
			?>
		</div>
		<div class="col-sm-8" data-property="price_options">
			<label>
				<?php echo $a->r()->render_fancy_checkbox( array(
					'text_on'  => $a->__( 'Yes' ),
					'text_off' => $a->__( 'No' ),
					'value'    => $profile->price_options,
				) ); ?>
			</label>
		</div>
	</div>
	<div class="row property-item">
		<div class="col-sm-4"><?php echo $a->__( 'Make ordinary order' ); ?>
			<?php
				echo $a->r()->render_popover(
					$a->__( 'If set to Yes, then ordinary (non-recurring) order will be created along with recurring  for this product for the first charge' )
				)
			?>
		</div>
		<div class="col-sm-8" data-property="first_order">
			<label>
				<?php echo $a->r()->render_fancy_checkbox( array(
					'text_on'  => $a->__(