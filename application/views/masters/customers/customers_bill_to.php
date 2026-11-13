<div class="form-horizontal">
	<div class="form-group">
    <label class="col-lg-2 col-md-2 col-sm-2 col-xs-12 control-label no-padding-right">รหัสสาขา</label>
    <div class="col-lg-1-harf col-md-1-harf col-sm-1-harf col-xs-12">
      <input type="text" id="b-branch-code" class="form-control input-sm r" placeholder="0000" value="<?php echo $bill->branch_code ?? '0000'; ?>" />
    </div>
  </div>

  <div class="form-group">
    <label class="col-lg-2 col-md-2 col-sm-2 col-xs-12 control-label no-padding-right">ชื่อสาขา</label>
    <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">
      <input type="text" id="b-branch-name" class="form-control input-sm r" placeholder="สำนักงานใหญ่" value="<?php echo $bill->branch_name ?? 'สำนักงานใหญ่'; ?>" />
    </div>
  </div>

  <div class="form-group">
    <label class="col-lg-2 col-md-2 col-sm-2 col-xs-12 control-label no-padding-right">ที่อยู่</label>
    <div class="col-lg-6 col-md-6 col-sm-8 col-xs-12">
      <input type="text" id="b-address" class="form-control input-sm r" placeholder="อาคาร/หมู่ที่/ถนน **ต้องการ" value="<?php echo $bill->address ?? ''; ?>" required/>
    </div>
  </div>

  <div class="form-group">
    <label class="col-lg-2 col-md-2 col-sm-2 col-xs-12 control-label no-padding-right">ตำบล/แขวง</label>
    <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
      <input type="text" id="b-sub-district" class="form-control input-sm r" placeholder="ตำบล/แขวง **ต้องการ" value="<?php echo $bill->sub_district ?? ''; ?>" required/>
    </div>
  </div>

  <div class="form-group">
    <label class="col-lg-2 col-md-2 col-sm-2 col-xs-12 control-label no-padding-right">อำเภอ/เขต</label>
    <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
      <input type="text" id="b-district" class="form-control input-sm r" placeholder="อำเภอ/เขต **ต้องการ" value="<?php echo $bill->district ?? ''; ?>" required/>
    </div>
  </div>

  <div class="form-group">
    <label class="col-lg-2 col-md-2 col-sm-2 col-xs-12 control-label no-padding-right">จังหวัด</label>
    <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
      <input type="text" id="b-province" class="form-control input-sm r" placeholder="จังหวัด **ต้องการ" value="<?php echo $bill->province ?? ''; ?>" required/>
    </div>
  </div>

  <div class="form-group">
    <label class="col-lg-2 col-md-2 col-sm-2 col-xs-12 control-label no-padding-right">รหัสไปรษณีย์</label>
    <div class="col-lg-1 col-md-1 col-sm-1 col-1-harf col-xs-12">
      <input type="text" id="b-postcode" class="form-control input-sm r" placeholder="10110" value="<?php echo $bill->postcode ?? ''; ?>" />
    </div>
  </div>

  <div class="form-group">
    <label class="col-lg-2 col-md-2 col-sm-2 col-xs-12 control-label no-padding-right">รหัสประเทศ</label>
    <div class="col-lg-1 col-md-1 col-sm-1 col-1 col-xs-12">
      <input type="text" id="b-country" class="form-control input-sm r text-center" placeholder="TH" value="<?php echo $bill->country ?? 'TH'; ?>" />
    </div>
  </div>

  <div class="form-group">
    <label class="col-lg-2 col-md-2 col-sm-2 col-xs-12 control-label no-padding-right">โทรศัพท์</label>
    <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">
      <input type="text" id="b-phone" class="form-control input-sm r" placeholder="000 000 0000" value="<?php echo $bill->phone ?? ''; ?>" />
    </div>
  </div>
  <div class="divider-hidden">

	</div>
  <div class="form-group">    
    <div class="col-lg-3 col-lg-offset-2 col-md-3 col-md-offset-2 col-sm-3 col-sm-offset-2 col-xs-12">
      <button type="button" class="btn btn-white btn-success btn-100 btn-block-xs" onclick="saveBillTo()"><i class="fa fa-save"></i>&nbsp;&nbsp;Save</button>      
    </div>  
  </div>
</div>
