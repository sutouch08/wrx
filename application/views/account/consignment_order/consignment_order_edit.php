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
					<li class="info">
						<a href="javascript:getUploadFile()"><i class="fa fa-upload"></i>&nbsp; Import Excel</a>
					</li>
					<?php if (empty($doc->ref_code)) : ?>
						<li class="primary">
							<a href="javascript:getActiveCheckList()"><i class="fa fa-file-text-o"></i>&nbsp; เอกสารกระทบยอด</a>
						</li>
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
<hr class="" />
<div class="row">
	<div class="col-lg-1-harf col-md-2 col-sm-2 col-xs-4 padding-5">
		<label>เลขที่เอกสาร</label>
		<input type="text" class="form-control input-sm text-center" value="<?php echo $doc->code; ?>" disabled />
	</div>
	<div class="col-lg-1 col-md-1-harf col-sm-1-harf col-xs-4 padding-5">
		<label>วันที่</label>
		<input type="text" class="form-control input-sm text-center edit" name="date_add" id="date" value="<?php echo thai_date($doc->date_add); ?>" readonly disabled />
	</div>
	<div class="col-lg-1-harf col-md-2 col-sm-2 col-xs-4 padding-5">
		<label>รหัสลูกค้า</label>
		<input type="text" class="form-control input-sm text-center edit" name="customerCode" id="customerCode" value="<?php echo $doc->customer_code; ?>" disabled>
	</div>
	<div class="col-lg-7 col-md-6-harf col-sm-6-harf col-xs-12 padding-5">
		<label>ลูกค้า[ในระบบ]</label>
		<input type="text" class="form-control input-sm edit" name="customer" id="customer" value="<?php echo $doc->customer_name; ?>" disabled />
	</div>
	<div class="col-lg-1-harf col-md-2 col-sm-2 col-xs-4 padding-5">
		<label>รหัสโซน</label>
		<input type="text" class="form-control input-sm text-center edit" id="zone_code" name="zone_code" value="<?php echo $doc->zone_code; ?>" disabled />
	</div>
	<div class="col-lg-4 col-md-4-harf col-sm-4-harf col-xs-8 padding-5">
		<label>โซน[ฝากขาย]</label>
		<input type="text" class="form-control input-sm edit" name="zone" id="zone" value="<?php echo $doc->zone_name; ?>" disabled />
	</div>
	<div class="col-lg-5-harf col-md-4 col-sm-4 col-xs-9 padding-5">
		<label>หมายเหตุ</label>
		<input type="text" class="form-control input-sm edit" name="remark" id="remark" value="<?php echo $doc->remark; ?>" disabled />
	</div>

	<?php if ($this->pm->can_edit) : ?>
		<div class="col-lg-1 col-md-1-harf col-sm-1-harf col-xs-3 padding-5">
			<label class="not-show">Submit</label>
			<button type="button" class="btn btn-xs btn-warning btn-block" id="btn-edit" onclick="getEdit()"></i class="fa fa-pencil"></i> แก้ไข</button>
			<button type="button" class="btn btn-xs btn-success btn-block hide" id="btn-update" onclick="update()"><i class="fa fa-save"></i> บันทึก</button>
		</div>
	<?php endif; ?>
</div>
<hr class="margin-top-15">
<input type="hidden" name="consign_code" id="consign_code" value="<?php echo $doc->code; ?>">
<input type="hidden" name="auz" id="auz" value="<?php echo $auz; ?>">
<input type="file" class="hide" name="uploadFile" id="uploadFile" accept=".xlsx" />

<?php $this->load->view('account/consignment_order/consignment_order_control'); ?>
<?php $this->load->view('account/consignment_order/consignment_order_detail'); ?>


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


<script src="<?php echo base_url(); ?>scripts/account/consignment_order/consignment_order.js?v=<?php echo date('Ymd'); ?>"></script>
<script src="<?php echo base_url(); ?>scripts/account/consignment_order/consignment_order_add.js?v=<?php echo date('Ymd'); ?>"></script>
<script src="<?php echo base_url(); ?>scripts/account/consignment_order/consignment_order_control.js?v=<?php echo date('Ymd'); ?>"></script>

<?php $this->load->view('include/footer'); ?>