<div class="account-wrapper">
<?php if ( ! $default ) : ?>
<div class="form-group">
	<div class="col-sm-12">
		<button
			type="button"
			class="btn btn-default pull-right delete-account"
			title="<?php echo $a->__( 'Delete account' ); ?>"
		>
			<i class="fa fa-close"></i>
		</button>
	</div>
</div>
<?php else: ?>
<span class="label label-success fa fa-tag" style="font-size: 2em"> <?php echo $a->__( 'Default account' ); ?></span>
<?php endif; ?>

<?php
	echo $account_name;
	echo $account_currency;
	echo $live_secret_key;
	echo $live_public_key;
	echo $test_secret_key;
	echo $test_public_key;
?>
</div>
