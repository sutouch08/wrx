<div class="row">
  <div class="col-lg-1-harf col-md-1-harf col-sm-1-harf col-xs-4 padding-5">
    <label>Policy No.</label>
    <input type="text" class="form-control input-sm text-center" value="<?php echo $policy->code; ?>" readonly />
  </div>

  <div class="col-lg-6-harf col-md-5-harf col-sm-5-harf col-xs-8 padding-5">
    <label>Description</label>
    <input type="text" class="form-control input-sm r" id="name" value="<?php echo $policy->name; ?>" />
  </div>

  <div class="col-lg-1 col-md-1-harf col-sm-1-harf col-xs-6 padding-5">
    <label>Start date</label>
    <input type="text" class="form-control input-sm text-center r" id="start-date" value="<?php echo thai_date($policy->start_date); ?>" readonly />
  </div>
  <div class="col-lg-1 col-md-1-harf col-sm-1-harf col-xs-6 padding-5">
    <label>End date</label>
    <input type="text" class="form-control input-sm text-center r" id="end-date" value="<?php echo thai_date($policy->end_date); ?>" readonly />
  </div>
  <div class="col-lg-1 col-md-1 col-sm-1 col-xs-4 padding-5 text-center">
    <label>Status</label>
    <label class="display-block padding-top-5" style="height:25px;">
      <input class="ace ace-switch ace-switch-6" type="checkbox" id="active" value="1" <?php echo is_checked($policy->active, '1'); ?>>
      <span class="lbl"></span>
    </label>
  </div>
  <div class="col-lg-1 col-md-1 col-sm-1 col-xs-8 padding-5">
    <label class="not-show">buton</label>
    <button type="button" class="btn btn-xs btn-success btn-block" id="btn-update" onclick="update()">Save</button>
  </div>  
</div>
<input type="hidden" id="policy-id" value="<?php echo $policy->id; ?>" />
<input type="hidden" id="prev-name" value="<?php echo $policy->name; ?>" />
<input type="hidden" id="prev-start-date" value="<?php echo $policy->start_date; ?>" />
<input type="hidden" id="prev-end-date" value="<?php echo $policy->end_date; ?>" />
<input type="hidden" id="prev-active" value="<?php echo $policy->active; ?>" />
<hr class="margin-top-15">