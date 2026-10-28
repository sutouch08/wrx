<?php $this->load->view('include/header'); ?>
<div class="row">
	<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 padding-5 padding-top-5">
		<h3 class="title"><?php echo $this->title; ?></h3>
	</div>
	<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 padding-5 text-right">
		<button type="button" class="btn btn-white btn-warning top-btn" onclick="goBack()"><i class="fa fa-arrow-left"></i> กลับ</button>
		<div class="btn-group">
			<button data-toggle="dropdown" class="btn btn-primary btn-white dropdown-toggle margin-top-5" aria-expanded="false">
				<i class="ace-icon fa fa-cloud-upload icon-on-left"></i>
				นำเข้า
				<i class="ace-icon fa fa-angle-down icon-on-right"></i>
			</button>
			<ul class="dropdown-menu dropdown-menu-right">
				<li class="purple">
					<a href="javascript:getTemplate()"><i class="fa fa-download"></i>&nbsp; Template file</a>
				</li>
				<?php if (($this->pm->can_add or $this->pm->can_edit) && $doc->status == 0) : ?>
					<?php if ($doc->is_api == 0) : ?>
						<li class="info">
							<a href="javascript:getUploadFile()"><i class="fa fa-upload"></i>&nbsp; Import Excel</a>
						</li>
						<?php if (empty($doc->ref_code)) : ?>
							<li class="primary">
								<a href="javascript:getActiveCheckList()"><i class="fa fa-file-text-o"></i>&nbsp; เอกสารกระทบยอด</a>
							</li>
						<?php endif; ?>
					<?php endif; ?>
				<?php endif; ?>
			</ul>
		</div>
		<?php if ($doc->status == 0 && ($this->pm->can_add or $this->pm->can_edit)) : ?>
			<button type="button" class="btn btn-white btn-success top-btn" onclick="saveConsign()">
				<i class="fa fa-save"></i> บันทึก
			</button>
		<?php endif; ?>
	</div>
</div>
<hr />

<div class="row">
	<div class="col-lg-1-harf col-md-2 col-sm-2 col-xs-4 padding-5">
		<label>เลขที่เอกสาร</label>
		<input type="text" class="form-control input-sm text-center" value="<?php echo $doc->code; ?>" disabled />
	</div>
	<div class="col-lg-1-harf col-md-1-harf col-sm-1-harf col-xs-4 padding-5">
		<label>วันที่</label>
		<input type="text" class="form-control input-sm text-center edit" name="date_add" id="date" value="<?php echo thai_date($doc->date_add); ?>" readonly disabled />
	</div>
	<div class="col-lg-2 col-md-2 col-sm-2 col-xs-4 padding-5">
		<label>รหัสลูกค้า</label>
		<input type="text" class="form-control input-sm text-center edit" name="customerCode" id="customerCode" value="<?php echo $doc->customer_code; ?>" disabled>
	</div>
	<div class="col-lg-7 col-md-6-harf col-sm-6-harf col-xs-12 padding-5">
		<label>ลูกค้า[ในระบบ]</label>
		<input type="text" class="form-control input-sm edit" name="customer" id="customer" value="<?php echo $doc->customer_name; ?>" disabled />
	</div>
	<div class="col-lg-1 col-md-1-harf col-sm-1-harf col-xs-3 padding-5">
		<label>Tax Status</label>
		<input type="text" class="form-control input-sm text-center" id="tax-status" value="<?php echo $doc->tax_status == 1 ? 'Y' : 'N'; ?>" disabled />
	</div>
	<div class="col-lg-1 col-md-1-harf col-sm-1-harf col-xs-3 padding-5">
		<label>E-TAX</label>
		<input type="text" class="form-control input-sm text-center" value="<?php echo $doc->is_etax ? 'Y' : 'N'; ?>" disabled />
	</div>
	<div class="col-lg-2 col-md-2-harf col-sm-2-harf col-xs-6 padding-5">
		<label>รหัสโซน</label>
		<input type="text" class="form-control input-sm text-center edit" id="zone_code" name="zone_code" value="<?php echo $doc->zone_code; ?>" disabled />
	</div>

	<div class="col-lg-8 col-md-6-harf col-sm-6-harf col-xs-12 padding-5">
		<label>โซน[ฝากขาย]</label>
		<input type="text" class="form-control input-sm edit" name="zone" id="zone" value="<?php echo $doc->zone_name; ?>" disabled />
	</div>

	<div class="col-lg-10-harf col-md-10-harf col-sm-10-harf col-xs-9 padding-5">
		<label>หมายเหตุ</label>
		<input type="text" class="form-control input-sm edit" name="remark" id="remark" value="<?php echo $doc->remark; ?>" disabled />
	</div>

	<?php if ($doc->is_api == 0) : ?>
		<?php if ($this->pm->can_edit) : ?>
			<div class="col-lg-1-harf col-md-1-harf col-sm-1-harf col-xs-3 padding-5">
				<label class="not-show">Submit</label>
				<button type="button" class="btn btn-xs btn-warning btn-block" id="btn-edit" onclick="getEdit()"></i class="fa fa-pencil"></i> แก้ไข</button>
				<button type="button" class="btn btn-xs btn-success btn-block hide" id="btn-update" onclick="update()"><i class="fa fa-save"></i> บันทึก</button>
			</div>
		<?php endif; ?>
	<?php else : ?>
		<div class="col-lg-1-harf col-md-1-harf col-sm-1-harf col-xs-3 padding-5">
			<label>POS Ref.</label>
			<input type="text" class="form-control input-sm" id="pos-ref" value="<?php echo $doc->pos_ref; ?>" disabled />
		</div>
	<?php endif; ?>
</div>
<?php if ($doc->tax_status == 1) : ?>
	<hr />
	<div class="row">
		<div class="divider"></div>
		<div class="col-lg-5 col-md-5 col-sm-5 col-xs-8 padding-5">
			<label>ชื่อสำหรับออกใบกำกับภาษี</label>
			<input type="text" class="width-100" id="name" value="<?php echo $doc->name; ?>" disabled />
		</div>
		<div class="col-lg-1-harf col-md-1-harf col-sm-2 col-xs-4 padding-5">
			<label>Tax ID</label>
			<input type="text" class="width-100 text-center" id="tax-id" value="<?php echo $doc->tax_id; ?>" disabled />
		</div>
		<div class="col-lg-1-harf col-md-1-harf col-sm-1 col-xs-4 padding-5">
			<label>รหัสสาขา</label>
			<input type="text" class="width-100 text-center" id="branch-code" value="<?php echo $doc->branch_code; ?>" disabled />
		</div>
		<div class="col-lg-1-harf col-md-2 col-sm-2 col-xs-4 padding-5">
			<label>ชื่อสาขา</label>
			<input type="text" class="width-100 text-center" id="branch-name" value="<?php echo $doc->branch_name; ?>" disabled />
		</div>
		<div class="col-lg-2-harf col-md-2 col-sm-2 col-xs-4 padding-5">
			<label>เบอร์โทร</label>
			<input type="text" class="width-100 text-center" id="phone" value="<?php echo $doc->phone; ?>" disabled />
		</div>
		<div class="col-lg-4 col-md-7 col-sm-4-harf col-xs-12 padding-5">
			<label>ที่อยุ่</label>
			<input type="text" class="width-100" id="address" value="<?php echo $doc->address; ?>" disabled />
		</div>
		<div class="col-lg-1-harf col-md-2-harf col-sm-2 col-xs-6 padding-5">
			<label>ตำบล</label>
			<input type="text" class="width-100" id="sub-district" value="<?php echo $doc->sub_district; ?>" disabled />
		</div>
		<div class="col-lg-1-harf col-md-2-harf col-sm-2 col-xs-6 padding-5">
			<label>อำเภอ</label>
			<input type="text" class="width-100" id="district" value="<?php echo $doc->district; ?>" disabled />
		</div>
		<div class="col-lg-1-harf col-md-2-harf col-sm-2 col-xs-6 padding-5">
			<label>จังหวัด</label>
			<input type="text" class="width-100" id="province" value="<?php echo $doc->province; ?>" disabled />
		</div>
		<div class="col-lg-1-harf col-md-1-harf col-sm-1-harf col-xs-6 padding-5">
			<label>ไปรษณีย์</label>
			<input type="text" class="width-100" id="postcode" value="<?php echo $doc->postcode; ?>" disabled />
		</div>
		<div class="col-lg-2 col-md-2-harf col-sm-3 col-xs-6 padding-5">
			<label>Email</label>
			<input type="text" class="width-100 text-center" value="<?php echo $doc->email; ?>" disabled />
		</div>
	</div>
<?php endif; ?>
<hr class="margin-top-15">

<input type="hidden" name="consign_code" id="consign_code" value="<?php echo $doc->code; ?>">
<input type="hidden" name="auz" id="auz" value="<?php echo $auz; ?>">
<input type="file" class="hide" name="uploadFile" id="uploadFile" accept=".xlsx" />

<?php if ($doc->is_api == 0) : ?>
	<?php $this->load->view('account/consign_order/consign_order_control'); ?>
<?php endif; ?>
<?php if ($doc->is_api == 1) : ?>
	<div class="row">
		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
			<p class="red text-center">** เอกสารนี้ถูกสร้างโดยระบบ POS จึงไม่สามารถแก้ไขรายการได้ **</p>
		</div>
	</div>
<?php endif; ?>
<?php $this->load->view('account/consign_order/consign_order_detail'); ?>


<div class="modal fade" id="check-list-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog" style="width:400px;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			</div>
			<div class="modal-body" id="check-list-body">

			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">ปิด</button>
			</div>
		</div>
	</div>
</div>


<script id="check-list-template" type="text/x-handlebars-template">
	<div class="row">
		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 padding-5">		
			<table class="table table-medium">				
				<tbody id="check-list-table">
			{{#each this}}
				{{#if nodata}}
					<tr>
						<td class="width-100text-center"><h4>ไม่พบรายการ</h4></td>
					</tr>
				{{else}}
						<tr>
							<td class="fix-width-100 middle">{{date_add}}</td>
							<td class="min-width-150 middle">{{code}}</td>
							<td class="fix-width-100 middle text-right">
								<button type="button" class="btn btn-xs btn-info btn-block" onclick="loadCheckDiff('{{code}}')">นำเข้ายอดต่าง</button>
							</td>
						</tr>
					{{/if}}
			{{/each}}
				</tbody>
			</table>
		</div>
	</div>
</script>

<script src="<?php echo base_url(); ?>scripts/account/consign_order/consign_order.js?v=<?php echo date('Ymd'); ?>"></script>
<script src="<?php echo base_url(); ?>scripts/account/consign_order/consign_order_add.js?v=<?php echo date('Ymd'); ?>"></script>
<script src="<?php echo base_url(); ?>scripts/account/consign_order/consign_order_control.js?v=<?php echo date('Ymd'); ?>"></script>

<?php $this->load->view('include/footer'); ?>