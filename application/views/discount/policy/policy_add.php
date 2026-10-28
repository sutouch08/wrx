<?php $this->load->view('include/header'); ?>
<div class="row">
  <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 padding-5 padding-top-5">
    <h3 class="title"><?php echo $this->title; ?></h3>
  </div>
  <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 padding-5 text-right">
    <button type="button" class="btn btn-white btn-default top-btn" onclick="goBack()"><i class="fa fa-arrow-left"></i> กลับ</button>
  </div>
</div>
<hr />
<div class="row">
  <div class="col-lg-1-harf col-md-1-harf col-sm-1-harf col-xs-4 padding-5">
    <label>Policy No.</label>
    <input type="text" class="form-control input-sm" id="code" value="" disabled />
  </div>

  <div class="col-lg-6-harf col-md-6 col-sm-6 col-xs-8 padding-5">
    <label>Description</label>
    <input type="text" class="form-control input-sm r" id="name" value="" autocomplete="off" autofocus />
  </div>

  <div class="col-lg-1-harf col-md-1-harf col-sm-1-harf col-xs-4 padding-5">
    <label>Start date</label>
    <input type="text" class="form-control input-sm text-center" id="start-date" value="" required />
  </div>
  <div class="col-lg-1-harf col-md-1-harf col-sm-1-harf col-xs-4 padding-5">
    <label>End date</label>
    <input type="text" class="form-control input-sm text-center" id="end-date" value="" required />
  </div>
  <?php if ($this->pm->can_add) : ?>
    <div class="col-lg-1 col-md-1-harf col-sm-1-harf col-xs-4 padding-5">
      <label class="not-show">buton</label>
      <button type="button" class="btn btn-xs btn-success btn-block" onclick="add()">Add</button>
    </div>
  <?php endif; ?>
</div>
<hr class="margin-top-15">


<script src="<?php echo base_url(); ?>scripts/discount/policy/policy.js?v=<?php echo date('Ymd'); ?>"></script>
<script src="<?php echo base_url(); ?>scripts/discount/policy/policy_add.js?v=<?php echo date('Ymd'); ?>"></script>

<?php $this->load->view('include/footer'); ?>