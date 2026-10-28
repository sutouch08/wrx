<div class="row">
  <div class="col-lg-1-harf col-md-1-harf col-sm-1-harf col-xs-4 padding-5">
    <label>Policy No.</label>
    <input type="text" class="form-control input-sm text-center" value="<?php echo $policy->code; ?>" readonly />
  </div>

  <div class="col-lg-6-harf col-md-5-harf col-sm-5-harf col-xs-8 padding-5">
    <label>Description</label>
    <input type="text" class="form-control input-sm r" value="<?php echo $policy->name; ?>" readonly/>
  </div>

  <div class="col-lg-1 col-md-1-harf col-sm-1-harf col-xs-6 padding-5">
    <label>Start date</label>
    <input type="text" class="form-control input-sm text-center r" value="<?php echo thai_date($policy->start_date); ?>" readonly />
  </div>
  <div class="col-lg-1 col-md-1-harf col-sm-1-harf col-xs-6 padding-5">
    <label>End date</label>
    <input type="text" class="form-control input-sm text-center r" value="<?php echo thai_date($policy->end_date); ?>" readonly />
  </div>
  <div class="col-lg-1 col-md-1 col-sm-1 col-xs-4 padding-5 text-center">
    <label class="not-show">Status</label>
    <span class="form-control input-sm text-center text-label"><?php echo is_active($policy->active); ?>&nbsp; <?php echo $policy->active == 1 ? 'Active' : 'Inactive'; ?></span>    
  </div>  
</div>
<hr class="margin-top-15">