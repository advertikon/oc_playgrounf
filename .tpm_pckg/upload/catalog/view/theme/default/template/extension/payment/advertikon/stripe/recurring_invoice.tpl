<style>
.adk-bold {
	font-weight: bold;
}
.adk-highlight {
	background-color: #F1F1F1;
}
.adk-charge-header {
	font-weight: bold;
    text-align: center;
    color: #2095C1;
    border-top: solid 2px #2097C4;
}
.adk-error {
	background-color: #FF5B5B;
    color: white;
    font-weight: bold;
}
.adk-hidden {
	display: none;
}
[data-adk-collapse] {
	cursor: pointer;
}
</style>
<div class="h3"><?php echo $a->caption( 'caption_charge_value' ); ?></div>

<table class="table table-responsive table-bordered">
	<thead>
		<th><?php echo $a->__( 'Name' ); ?></th>
		<th><?php echo $a->__( 'Value' ); ?></th>
	</thead>
	<tbody>

<?php if( $one_time_totals ) : ?>
		<tr><td colspan="2" class="adk-charge-header"><?php echo $a->__( 'One time charge' ); ?></td></tr>
		<tr>
			<td class="adk-bold"><?php echo $a->__( 'Product(s)' ); ?></td>
		<?php $pl = array(); foreach( $one_time_totals[ 'product' ] as $product ) { $pl[] = $product[ 'name' ]; } ?>
			<td><?php echo implode( ', ' , $pl ); ?></td>
		</tr>
<?php foreach( $one_time_totals[ 'total' ] as $total ) : ?>
<?php if( $total[ 'code' ] == 'total' && ( $min = $a->check_min_amount( $total[ 'value' ] , $currency ) ) !== true )$error = $a->__( 'Charge amount cannot be less than %s. Please use another payment method' , $model->currency->format( $min , $currency ) ) ;  ?>
		<tr class="adk-highlight">
			<td class="adk-bold"><?php echo $total[ 'title' ]; ?></td>
			<td <?php if( strtolower( $total[ 'code' ] ) == 'total' )echo 'class="adk-bold"'; ?> ><?php echo $model->currency->format( $total[ 'value' ] , $currency ); ?></td>
		</tr>
<?php endforeach; ?>

<?php if( isset( $error ) ) : ?>
<tr class="adk-error">
	<td colspan="2"><?php echo $error; ?></td>
</tr>
<?php endif; ?>

<?php endif; ?>


<?php if( $recurring_totals ) : ?>

<tr><td colspan="2" class="adk-charge-header"><?php echo $a->__( 'Recurring charge(s)' ); ?></td></tr>

<?php foreach( $recurring_totals as $p_id => $rt ) : ?>
<tr><td colspan="2">&nbsp;</td></tr>
<tr>
	<td colspan="2" class="adk-bold"><?php echo $rt[ 'product' ][ 'recurring' ][ 'name' ]; ?></td>
</tr>
<tr>
	<td class="adk-bold"><?php echo $a->__( 'Product' ); ?></td>
	<td><?php echo $rt[ 'product' ][ 'name' ]; ?></td>
</tr>

<?php foreach( $rt[ 'total' ][ 'recurring' ] as $total ) : ?>
<?php $t = 0; ?>

<tr class="adk-highlight">

	<?php if( strtolower( $total[ 'code' ] ) == 'total' ) : ?>
		<?php if( $rt[ 'total' ][ 'one_time' ] ) : ?>
	<td class="adk-bold"><?php echo $a->__( 'Recurring charge total' ); ?></td>
		<?php endif; ?>
	<?php else : ?>
	<td class="adk-bold"><?php echo $total[ 'title' ]; ?></td>
	<?php endif; ?>

	<?php if( strtolower( $total[ 'code' ] ) == 'total' ) : ?>
	<?php $t += $total[ 'value' ]; ?>
		<?php if( $rt[ 'total' ][ 'one_time' ] ) : ?>
	<td class="adk-bold"><?php echo $model->currency->format( $total[ 'value' ] , $currency ); ?></td>
		<?php endif; ?>
	<?php else : ?>
	<td><?php echo $model->currency->format( $total[ 'value' ] , $currency ); ?></td>
	<?php endif; ?>

</tr>

<?php endforeach; ?>


<?php if( $rt[ 'total' ][ 'one_time' ] ) : ?>

<tr><td colspan="2" class="adk-bold"><?php echo $a->__( 'One time charge to cover non-recurring fees' ); ?></td></tr>

<?php foreach( $rt[ 'total' ][ 'one_time' ] as $line ) : ?>
<tr class="adk-highlight">
	
	<?php if( strtolower( $line[ 'code' ] ) == 'total' ) : ?>
	<td class="adk-bold"><?php echo $a->__( 'One time charge total' ); ?></td>
	<?php else : ?>
	<td class="adk-bold"><?php echo $line[ 'title' ]; ?></td>
	<?php endif; ?>

	<?php if( strtolower( $line[ 'code' ] ) == 'total' ) : ?>
	<?php $t += $line[ 'value' ]; ?>
	<td class="adk-bold"><?php echo $a->currency->format( $line[ 'value' ] , $currency ); ?></td>
	<?php else : ?>
	<td><?php echo $model->currency->format( $line[ 'value' ] , $currency ); ?></td>
	<?php endif; ?>

</tr>
<?php endforeach; ?>

<?php endif; ?>
<?php $a->invoice_total = $t; ?>

<tr class="adk-highlight">
	<td class="adk-bold"><?php echo $a->__( 'Total' ); ?></td>
	<td class="adk-bold"><?php echo $model->currency->format( $t , $currency ); ?></td>
</tr>

<?php if( ! $model->is_last_cycle( $rt['product']['recurring'] ) && isset( $model->session->data[ 'adk_next' ][ $p_id ] ) ) : ?>

<tr>
	<td colspan="2" class="adk-bold" data-adk-collapse=".next-charge-<?php echo $p_id; ?>">
		<span id="next-charge-list" class="fa fa-caret-down"></span>
		<?php echo $a->__( 'Next charge' ) .
		' (' . $model->get_next_plan_date(
					$recurring_totals[ $p_id ][ 'product' ][ 'recurring' ]
				)->format( 'D, d M Y' ) . ')'; ?>
	</td>
</tr>

<?php $asterixLine = false; ?>
<?php foreach( $model->session->data[ 'adk_next' ][ $p_id ] as $next ) : ?>
<?php
	if( in_array( $next[ 'code' ] , array( 'voucher' , 'coupon' ) ) ) {
		$asterix = '*';
		$asterixLine = true;
	}
	else {
		$asterix = '';
	}
?>

<tr class="adk-highlight adk-hidden next-charge-<?php echo $p_id; ?>">

	<td class="adk-bold"><?php echo $next[ 'title' ] . $asterix; ?></td>

	<?php if( strtolower( $next[ 'code' ] ) == 'total' ) : ?>
	<td class="adk-bold"><?php echo $model->currency->format( $next[ 'value' ] , $currency ); ?></td>
	<?php else : ?>
	<td><?php echo $model->currency->format( $next[ 'value' ] , $currency ); ?></td>
	<?php endif; ?>

</tr>

<?php endforeach; ?>

<?php if( $asterixLine ) : ?>
<tr class="adk-hidden next-charge-<?php echo $p_id; ?>"><td colspan="2"><?php echo $a->__( '%sVoucher or coupon will be applied if it will be still active' , '*' ); ?></td></tr>
<?php endif; ?>


<?php endif; ?>


<?php endforeach; ?>

<?php endif; ?>

	</tbody>
</table>
<script>
$( '[data-adk-collapse]' ).each( function(){
	$( this ).on( 'click' , function(){
		$( $( this ).attr( 'data-adk-collapse' ) ).toggle();

		if ( $( "#next-charge-list" ).hasClass( "fa-caret-down" ) ) {
			$( "#next-charge-list" )
			.removeClass( "fa-caret-down" )
			.addClass( "fa-caret-up" );

		} else {
			$( "#next-charge-list" )
			.removeClass( "fa-caret-up" )
			.addClass( "fa-caret-down" );
		}
	} );
});
</script>
reach( $recurring_totals as $p_id => $rt ) : ?>
<tr><td colspan="2">&nbsp;</td></tr>
<tr>
	<td colspan="2" class="adk-bold"><?php echo $rt[ 'product' ][ 'recurring' ][ 'name' ]; ?></td>
</tr>
<tr>
	<td class="adk-bold"><?php echo $a->__( 'Product' ); ?></td>
	<td><?php echo $rt[ 'product' ][ 'name' ]; ?></td>
</tr>

<?php foreach( $rt[ 'total' ][ 'recurring' ] as $total ) : ?>
<?php $t = 0; ?>

<tr class="adk-highlight">

	<?php if( strtolower( $total[ 'code' ] ) == 'total' ) : ?>
		<?php if( $rt[ 'total' ][ 'one_time' ] ) : ?>
	<td class="adk-bold"><?php echo $a->__( 'Recurring charge total' ); ?></td>
		<?php endif; ?>
	<?php else : ?>
	<td class="adk-bold"><?php echo $total[ 'title' ]; ?></td>
	<?php endif; ?>

	<?php if( strtolower( $total[ 'code' ] ) == 'total' ) : ?>
	<?php $t += $total[ 'value' ]; ?>
		<?php if( $rt[ 'total' ][ 'one_time' ] ) : ?>
	<td class="adk-bold"><?php echo $model->currency->format( $total[ 'value' ] , $currency ); ?></td>
		<?php endif; ?>
	<?php else : ?>
	<td><?php echo $model->currency->format( $total[ 'value' ] , $currency ); ?></td>
	<?php endif; ?>

</tr>

<?php endforeach; ?>


<?php if( $rt[ 'total' ][ 'one_time' ] ) : ?>

<tr><td colspan="2" class="adk-bold"><?php echo $a->__( 'One time charge to cover non-recurring fees' ); ?></td></tr>

<?php foreach( $rt[ 'total' ][ 'one_time' ] as $line ) : ?>
<tr class="adk-highlight">
	
	<?php if( strtolower( $line[ 'code' ] ) == 'total' ) : ?>
	<td class="adk-bold"><?php echo $a->__( 'One time charge total' ); ?></td>
	<?php else : ?>
	<td class="adk-bold"><?php echo $line[ 'title' ]; ?></td>
	<?php endif; ?>

	<?php if( strtolower( $line[ 'code' ] ) == 'total' ) : ?>
	<?php $t += $line[ 'value' ]; ?>
	<td class="adk-bold"><?php echo $a->currency->format( $line[ 'value' ] , $currency ); ?></td>
	<?php else : ?>
	<td><?php echo $model->currency->format( $line[ 'value' ] , $currency ); ?></td>
	<?php endif; ?>

</tr>
<?php endforeach; ?>

<?php endif; ?>
<?php $a->invoice_total = $t; ?>

<tr class="adk-highlight">
	<td class="adk-bold"><?php echo $a->__( 'Total' ); ?></td>
	<td class="adk-bold"><?php echo $model->currency->format( $t , $currency ); ?></td>
</tr>

<?php if( ! $mode