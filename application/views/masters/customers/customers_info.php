<div class="form-horizontal">
	<div class="form-group margin-top-30">
		<label class="col-lg-3 col-md-3 col-sm-3 col-xs-12 control-label no-padding-right">รหัส</label>
		<div class="col-lg-1-harf col-md-2 col-sm-3 col-xs-12">
			<input type="text" class="form-control input-sm" value="<?php echo $ds->code; ?>" disabled />
			<input type="hidden" id="code" value="<?php echo $ds->code; ?>">
		</div>		
	</div>

	<div class="form-group">
		<label class="col-lg-3 col-md-3 col-sm-3 col-xs-12 control-label no-padding-right">ชื่อ</label>
		<div class="col-lg-4 col-md-5 col-sm-7 col-xs-12">
			<input type="text" id="name" class="form-control input-sm r" value="<?php echo escape_quotes($ds->name); ?>" />
		</div>		
	</div>
	
	<div class="form-group">
		<label class="col-lg-3 col-md-3 col-sm-3 col-xs-12 control-label no-padding-right">เลขประจำตัว/Tax ID</label>
		<div class="col-lg-4 col-md-5 col-sm-7 col-xs-12">
			<input type="text" id="tax-id" class="form-control input-sm" value="<?php echo $ds->Tax_Id; ?>" />
		</div>
	</div>

	<div class="form-group">
		<label class="col-lg-3 col-md-3 col-sm-3 col-xs-12 control-label no-padding-right">รหัสบัญชีลูกหนี้</label>
		<div class="col-lg-4 col-md-5 col-sm-7 col-xs-12">
			<select id="DebPayAcct" class="form-control input-sm" required>
				<option value="">เลือกรายการ</option>
				<?php echo select_DebPayAcct($ds->DebPayAcct); ?>
			</select>
		</div>
	</div>

	<div class="form-group">
		<label class="col-lg-3 col-md-3 col-sm-3 col-xs-12 control-label no-padding-right">กลุ่มลูกหนี้</label>
		<div class="col-lg-4 col-md-5 col-sm-7 col-xs-12">
			<select id="GroupCode" class="form-control input-sm" required>
				<?php echo select_GroupCode($ds->GroupCode); ?>
			</select>
		</div>
	</div>

	<div class="form-group">
		<label class="col-lg-3 col-md-3 col-sm-3 col-xs-12 control-label no-padding-right">รูปแบบ</label>
		<div class="col-lg-4 col-md-5 col-sm-7 col-xs-12">
			<select id="cmpPrivate" class="form-control input-sm">
				<option value="C" <?php echo is_selected('C', $ds->cmpPrivate); ?>>บริษัท/ร้าน</option>
				<option value="G" <?php echo is_selected('G', $ds->cmpPrivate); ?>>หน่วนงานรัฐ</option>
				<option value="I" <?php echo is_selected('I', $ds->cmpPrivate); ?>>บุคคลทั่วไป</option>
			</select>
		</div>
	</div>

	<div class="form-group">
		<label class="col-lg-3 col-md-3 col-sm-3 col-xs-12 control-label no-padding-right">รหัสกลุ่มเครดิต</label>
		<div class="col-lg-4 col-md-5 col-sm-7 col-xs-12">
			<select id="GroupNum" class="form-control input-sm">
				<?php echo select_GroupNum($ds->GroupNum); ?>
			</select>
		</div>
	</div>

	<div class="form-group">
		<label class="col-lg-3 col-md-3 col-sm-3 col-xs-12 control-label no-padding-right">กลุ่มลูกค้า</label>
		<div class="col-lg-4 col-md-5 col-sm-7 col-xs-12">
			<select id="group" class="form-control input-sm">
				<option value="">เลือกรายการ</option>
				<?php echo select_customer_group($ds->group_code); ?>
			</select>
		</div>		
	</div>

	<div class="form-group">
		<label class="col-lg-3 col-md-3 col-sm-3 col-xs-12 control-label no-padding-right">ประเภทลูกค้า</label>
		<div class="col-lg-4 col-md-5 col-sm-7 col-xs-12">
			<select id="kind" class="form-control input-sm">
				<option value="">เลือกรายการ</option>
				<?php echo select_customer_kind($ds->kind_code); ?>
			</select>
		</div>		
	</div>

	<div class="form-group">
		<label class="col-lg-3 col-md-3 col-sm-3 col-xs-12 control-label no-padding-right">ชนิดลูกค้า</label>
		<div class="col-lg-4 col-md-5 col-sm-7 col-xs-12">
			<select id="type" class="form-control input-sm">
				<option value="">เลือกรายการ</option>
				<?php echo select_customer_type($ds->type_code); ?>
			</select>
		</div>		
	</div>

	<div class="form-group">
		<label class="col-lg-3 col-md-3 col-sm-3 col-xs-12 control-label no-padding-right">เกรดลูกค้า</label>
		<div class="col-lg-4 col-md-5 col-sm-7 col-xs-12">
			<select id="class" class="form-control input-sm">
				<option value="">เลือกรายการ</option>
				<?php echo select_customer_class($ds->class_code); ?>
			</select>
		</div>		
	</div>

	<div class="form-group">
		<label class="col-lg-3 col-md-3 col-sm-3 col-xs-12 control-label no-padding-right">พื้นที่ขาย</label>
		<div class="col-lg-4 col-md-5 col-sm-7 col-xs-12">
			<select id="area" class="form-control input-sm">
				<option value="">เลือกรายการ</option>
				<?php echo select_customer_area($ds->area_code); ?>
			</select>
		</div>		
	</div>

	<div class="form-group">
		<label class="col-lg-3 col-md-3 col-sm-3 col-xs-12 control-label no-padding-right">พนักงานขาย</label>
		<div class="col-lg-4 col-md-5 col-sm-7 col-xs-12">
			<select id="sale" class="form-control input-sm">
				<?php echo select_sale($ds->sale_code); ?>
			</select>
		</div>
	</div>

	<div class="form-group">
		<label class="col-lg-3 col-md-3 col-sm-3 col-xs-12 control-label no-padding-right">วงเงินเครดิต</label>
		<div class="col-lg-3 col-md-4 col-sm-3 col-xs-12">
			<input type="number" id="CreditLine" class="form-control input-sm" value="<?php echo round($ds->CreditLine, 2); ?>" />
		</div>
	</div>

	<?php if ($ds->CreditLine > 0) : ?>
		<div class="form-group">
			<label class="col-lg-3 col-md-3 col-sm-3 col-xs-12 control-label no-padding-right">วงเงินคงเหลือ</label>
			<div class="col-lg-3 col-md-4 col-sm-3 col-xs-12">
				<input type="number" class="form-control input-sm" value="<?php echo round($this->customers_model->get_credit($ds->code), 2); ?>" disabled />
			</div>
		</div>
	<?php endif; ?>

	<div class="form-group">
		<label class="col-lg-3 col-md-3 col-sm-3 col-xs-12 control-label no-padding-right">GP(%)</label>
		<div class="col-lg-3 col-md-4 col-sm-3 col-xs-12">
			<input type="number" id="gp" class="form-control input-sm" value="<?php echo round($ds->gp, 2); ?>" />
		</div>
	</div>

	<div class="form-group">
		<label class="col-lg-3 col-md-3 col-sm-3 col-xs-12 control-label no-padding-right hidden-xs">Over due</label>
		<div class="col-lg-4 col-md-5 col-sm-7 col-xs-12">
			<label class="margin-top-5">
				<input type="checkbox" class="ace" id="skip-overdue" value="1" <?php echo is_checked('1', $ds->skip_overdue); ?>>
				<span class="lbl bigger-120"> ไม่ตรวจสอบยอดค้างชำระ</span>
			</label>
		</div>
	</div>


	<div class="divider-hidden"></div>

	<div class="form-group">		
		<div class="col-lg-4 col-lg-offset-3 col-md-5 col-md-offset-3 col-sm-7 col-sm-offset-3 col-xs-12">
			<button type="button" class="btn btn-white btn-success btn-100 btn-block-xs" onclick="update()"><i class="fa fa-save"></i> Save</button>			
		</div>		
	</div>	
</div>

<script>
	$('#DebPayAcct').select2();
	$('#GroupCode').select2();
	$('#cmpPrivate').select2();
	$('#GroupNum').select2();
	$('#group').select2();
	$('#kind').select2();
	$('#type').select2();
	$('#class').select2();
	$('#area').select2();
	$('#sale').select2();
</script>