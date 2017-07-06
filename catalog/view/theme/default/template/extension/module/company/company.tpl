<?php echo $header; ?>
<div class="container">
  <ul class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
    <?php } ?>
  </ul>
  <div class="row"><?php echo $column_left; ?>
    <?php if ($column_left && $column_right) { ?>
    <?php $class = 'col-sm-6'; ?>
    <?php } elseif ($column_left || $column_right) { ?>
    <?php $class = 'col-sm-9'; ?>
    <?php } else { ?>
    <?php $class = 'col-sm-12'; ?>
    <?php } ?>
    <div id="content" class="<?php echo $class; ?>"> <?php echo $content_top; ?>
      <h2><?php echo $text_edit_address; ?></h2>
      <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" class="form-horizontal">
        <fieldset>
        <div class="form-group required">
			<label class="col-sm-2 control-label" for="input-payment-company-name"><?php echo $adk->__( 'Company name' ); ?></label>
			<div class="col-sm-10">
				<input type="text" name="company[name]" value="<?php echo $company_name; ?>" placeholder="<?php echo $adk->__( 'Specify company name' ); ?>" id="input-payment-company-name" class="form-control">
				<?php if (isset( $error[ 'company_name' ] ) ) { ?>
		        <div class="text-danger"><?php echo $error['company_name']; ?></div>
		        <?php } ?>
			</div>
		</div>
		<div class="form-group required">
			<label class="col-sm-2 control-label" for="input-compant-vat"><?php echo $adk->__( 'VAT number' ); ?></label>
			<div class="col-sm-10">
				<input type="text" name="company[vat]" value="<?php echo $company_vat; ?>" placeholder="<?php echo $adk->__( 'Companies\' VAT number' ); ?>" id="input-payment-company-vat" class="form-control">
				<?php if (isset( $error[ 'company_vat' ] ) ) { ?>
		        <div class="text-danger"><?php echo $error['company_vat']; ?></div>
		        <?php } ?>
			</div>
		</div>
		<div class="form-group required">
			<label class="col-sm-2 control-label" for="input-payment-company-reg"><?php echo $adk->__( 'Reg. number' ); ?></label>
				<div class="col-sm-10">
				<input type="text" name="company[reg]" value="<?php echo $company_reg; ?>" placeholder="<?php echo $adk->__( 'Company registration number' ); ?>" id="input-payment-company-reg" class="form-control">
				<?php if (isset( $error[ 'company_reg' ] ) ) { ?>
		        <div class="text-danger"><?php echo $error['company_reg']; ?></div>
		        <?php } ?>
			</div>
		</div>
		<div class="form-group required">
			<label class="col-sm-2 control-label" for="input-payment-company-representative"><?php echo $adk->__( 'Representative' ); ?></label>
			<div class="col-sm-10">
				<input type="text" name="company[representative]" value="<?php echo $company_representative; ?>" placeholder="<?php echo $adk->__( 'Company legal representative' ); ?>" id="input-payment-company-representative" class="form-control">
				<?php if (isset( $error[ 'company_representative' ] ) ) { ?>
		        <div class="text-danger"><?php echo $error['company_representative']; ?></div>
		        <?php } ?>
			</div>
		</div>
		<div class="form-group required">
			<label class="col-sm-2 control-label" for="input-payment-company-address"><?php echo $adk->__( 'Street' ); ?></label>
			<div class="col-sm-10">
				<input type="text" name="company[address]" value="<?php echo $company_address; ?>" placeholder="<?php echo $adk->__( 'Address line 1' ); ?>" id="input-payment-company-address" class="form-control">
				<?php if (isset( $error[ 'company_address' ] ) ) { ?>
		        <div class="text-danger"><?php echo $error['company_address']; ?></div>
		        <?php } ?>
			</div>
		</div>
		<div class="form-group required">
			<label class="col-sm-2 control-label" for="input-payment-company-city"><?php echo $adk->__( 'City' ); ?></label>
			<div class="col-sm-10">
				<input type="text" name="company[city]" value="<?php echo $company_city; ?>" placeholder="<?php echo $adk->__( 'City' ); ?>" id="input-payment-company-city" class="form-control">
				<?php if (isset( $error[ 'company_city' ] ) ) { ?>
		        <div class="text-danger"><?php echo $error['company_city']; ?></div>
		        <?php } ?>
			</div>
		</div>
		<div class="form-group required">
			<label class="col-sm-2 control-label" for="input-payment-company-country"><?php echo $adk->__( 'Country' ); ?></label>
			<div class="col-sm-10">
				<select name="company[country]" id="input-payment-company-country" class="form-control">
					<option value=""><?php echo  $text_select;; ?></option>
					<?php foreach ( $countries as $country ) : ?>
					<?php if ( $country['country_id'] == $company_country_id ) : ?>
					<option value="<?php echo $country['country_id']; ?>" selected="selected"><?php echo $country['name']; ?></option>
					<?php else: ?>
					<option value="<?php echo $country['country_id']; ?>"><?php echo $country['name']; ?></option>
					<?php endif; ?>
					<?php endforeach; ?>
				</select>
				<?php if (isset( $error[ 'company_country' ] ) ) { ?>
		        <div class="text-danger"><?php echo $error['company_country']; ?></div>
		        <?php } ?>
			</div>
		</div>
		<div class="form-group required">
			<label class="col-sm-2 control-label" for="input-country-zone"><?php echo $adk->__( 'Zone' ); ?></label>
			<div class="col-sm-10">
				<select name="company[zone]" id="input-payment-company-zone" class="form-control"></select>
				<?php if (isset( $error[ 'company_zone_id' ] ) ) { ?>
		        <div class="text-danger"><?php echo $error['company_zone_id']; ?></div>
		        <?php } ?>
			</div>
		</div>
		<div class="form-group required">
			<label class="col-sm-2 control-label" for="input-payment-company-bank"><?php echo $adk->__( 'Bank' ); ?></label>
			<div class="col-sm-10">
				<input type="text" name="company[bank]" value="<?php echo $company_bank; ?>" placeholder="<?php echo $adk->__( 'Bank name' ); ?>" id="input-payment-company-bank" class="form-control">
				<?php if (isset( $error[ 'company_bank' ] ) ) { ?>
		        <div class="text-danger"><?php echo $error['company_bank']; ?></div>
		        <?php } ?>
			</div>
		</div>
		<div class="form-group required">
			<label class="col-sm-2 control-label" for="input-payment-company-city"><?php echo $adk->__( 'IBAN' ); ?></label>
			<div class="col-sm-10">
				<input type="text" name="company[iban]" value="<?php echo $company_iban; ?>" placeholder="<?php echo $adk->__( 'Bank account' ); ?>" id="input-payment-company-iban" class="form-control">
				<?php if (isset( $error[ 'company_iban' ] ) ) { ?>
		        <div class="text-danger"><?php echo $error['company_iban']; ?></div>
		        <?php } ?>
			</div>
		</div>
		<div class="form-group required">
			<label class="col-sm-2 control-label" for="input-payment-company-phone"><?php echo $adk->__( 'Phone' ); ?></label>
			<div class="col-sm-10">
				<input type="text" name="company[phone]" value="<?php echo $company_phone; ?>" placeholder="<?php echo $adk->__( 'Phone number' ); ?>" id="input-payment-company-phone" class="form-control">
				<?php if (isset( $error[ 'company_phone' ] ) ) { ?>
		        <div class="text-danger"><?php echo $error['company_phone']; ?></div>
		        <?php } ?>
			</div>
		</div>
        </fieldset>
        <div class="buttons clearfix">
          <div class="pull-left"><a href="<?php echo $back; ?>" class="btn btn-default"><?php echo $button_back; ?></a></div>
          <div class="pull-right">
            <input type="submit" value="<?php echo $button_continue; ?>" class="btn btn-primary" />
          </div>
        </div>
      </form>
      <?php echo $content_bottom; ?></div>
    <?php echo $column_right; ?></div>
</div>
<script type="text/javascript"><!--
// Sort the custom fields
$('.form-group[data-sort]').detach().each(function() {
	if ($(this).attr('data-sort') >= 0 && $(this).attr('data-sort') <= $('.form-group').length) {
		$('.form-group').eq($(this).attr('data-sort')).before(this);
	}

	if ($(this).attr('data-sort') > $('.form-group').length) {
		$('.form-group:last').after(this);
	}

	if ($(this).attr('data-sort') < -$('.form-group').length) {
		$('.form-group:first').before(this);
	}
});
//--></script>
<script type="text/javascript"><!--
$('#input-payment-company-country').on('change', function() {
	$.ajax({
		url: 'index.php?route=account/account/country&country_id=' + this.value,
		dataType: 'json',
		beforeSend: function() {
			$('select[name=\'country_id\']').after(' <i class="fa fa-circle-o-notch fa-spin"></i>');
		},
		complete: function() {
			$('.fa-spin').remove();
		},
		success: function(json) {
			if (json['postcode_required'] == '1') {
				$('input[name=\'postcode\']').parent().parent().addClass('required');
			} else {
				$('input[name=\'postcode\']').parent().parent().removeClass('required');
			}

			html = '<option value=""><?php echo $text_select; ?></option>';

			if (json['zone'] && json['zone'] != '') {
				for (i = 0; i < json['zone'].length; i++) {
					html += '<option value="' + json['zone'][i]['zone_id'] + '"';

					if (json['zone'][i]['zone_id'] == '<?php echo $company_zone_id; ?>') {
						html += ' selected="selected"';
			  		}

			  		html += '>' + json['zone'][i]['name'] + '</option>';
				}
			} else {
				html += '<option value="0" selected="selected"><?php echo $text_none; ?></option>';
			}

			$('#input-payment-company-zone').html(html);
		},
		error: function(xhr, ajaxOptions, thrownError) {
			alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}
	});
});

$('#input-payment-company-country').trigger('change');
//--></script>
<?php echo $footer; ?>
