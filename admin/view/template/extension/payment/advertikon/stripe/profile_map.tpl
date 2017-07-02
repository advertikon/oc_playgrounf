<div class="alert alert-success alert-dismissible" role="alert">
	<button type="button" class="close" data-dismiss="alert" aria-label="Close">
		<span aria-hidden="true">&times;</span>
	</button>
	<?php echo $a->__( 'At this tab, you can map any OpenCart\'s recurring plan to a profile, created at tab "Recurring Plan\'s Profiles"' ); ?>
</div>
<table class="table table-hover table-responsive" >
	<thead>
		<tr>
			<th><?php echo $a->__( 'OpenCart Recurring Profile' ); ?></th>
			<th><?php echo $a->__( 'Recurring plans\' profile' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach( $plans as $plan ) : ?>
		<tr data-oc-plan-id="<?php echo $plan->recurring_id; ?>">
		 	<td><?php echo $plan->name; ?></td>
		 	<td>
		 		<select class="form-control map-select">
			 	<?php foreach( $profiles as $profile ) : ?>
		 			<?php if ( $profile->name !== 'NonRecurring' ) : ?>
		 			<option value="<?php echo $profile->id; ?>"
		 				<?php echo ( $plan->profile_id === $profile->id ? 'selected="selected"' : '' ); ?>
		 			>
		 				<?php echo $profile->name; ?>
		 			</option>
		 			<?php endif; ?>
		 		<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php echo $pagination->render(); ?>
