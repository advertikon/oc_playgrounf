<div class="plans-container">' .
	<div class="alert alert-success alert-dismissible" role="alert">
		<button type="button" class="close" data-dismiss="alert" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		</button>
		<?php echo $a->__( 'At this tab, you can create a profile to define the logic of recurring payments: which total fields should be present in the recurring part of a payment and which - at the one-time payment, whether recurring payment can be canceled by customer etc. There are two predefined profiles: "NonRecurring" - applies to all ordinary products, existing in cart along with products which have a recurring plans and "Default" - applies to all products which have a recurring plans' ); ?>
	</div>
	<div class="edit-panel" >
		<div class="btn-group">
			<?php echo $add_profile; ?>
		</div>
	</div>
		<div class="property" data-profile-id="<?php echo $profile->id; ?>">
			<div class="edit-panel" >
				<?php echo $save_button; ?>
			</div>
			<div class="row property-item">
				<div class="col-sm-4"><?php echo $a->__( 'Name' ); ?></div> 
				<div class="col-sm-8" data-property="name">
					<input class="form-control" value="<?php echo $profile->name; ?>" readonly>
				</div>
			</div>
			<div class="row property-item">
				<div class="col-sm-4"><?php echo $a->__( 'Include to non-recurring payment' ); ?></div>
				<div class="col-sm-8" data-property="totals_to_recurring">
					<div class="checkbox-group totals-to-recurring">
				<?php foreach( $totals as $code => $name ) : ?>
						<label>
							<input
								type="checkbox"
								value="<?php echo $code; ?>"
								<?php echo ( in_array( $code, $profile_totals ) ? 'checked="checked"' : '' ); ?>
								<?php echo ( preg_match( '/(total)|(sub_total)|(tax.*)/', $code ) ? 'disabled="disabled"' : '' ); ?>
							>
							<?php echo $name; ?>
						</label>
				<?php endforeach; ?>
					</div>
				</div>
			</div>
		</div>
		<?php
		foreach( $profiles as $custom_profile ) {
			if ( ! in_array( $custom_profile->name, array( 'NonRecurring', 'Defaulty' ) ) ) {
				echo $self->profile_line( $custom_profile );
			}
		}
		?>
</div>
<?php echo $pagination->render(); ?>
