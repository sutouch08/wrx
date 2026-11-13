<?php $this->load->view('include/header'); ?>
<div class="row">
  <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 padding-5 padding-top-5">
    <h3 class="title"><?php echo $this->title; ?></h3>
  </div>
  <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 padding-5 text-right">
    <button type="button" class="btn btn-white btn-default top-btn" onclick="goBack()"><i class="fa fa-arrow-left"></i> Back</button>
  </div>
</div>
<hr />
<div class="form-horizontal margin-top-30">
  <div class="form-group">
    <label class="col-lg-3 col-md-3 col-sm-3 col-xs-12 control-label no-padding-right">Code</label>
    <div class="col-lg-1-harf col-md-2 col-sm-2 col-xs-12">
      <input type="text" id="code" class="form-control input-sm r" maxlength="15" value="" onkeyup="validCode(this)" autocomplete="off" autofocus />
    </div>
  </div>

  <div class="form-group">
    <label class="col-lg-3 col-md-3 col-sm-3 col-xs-12 control-label no-padding-right">Name</label>
    <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">
      <input type="text" id="name" class="form-control input-sm r" maxlength="50" value="" />
    </div>
  </div>

  <div class="form-group">
    <label class="col-lg-3 col-md-3 col-sm-3 col-xs-12 control-label no-padding-right">Type</label>
    <div class="col-lg-1-harf col-md-2 col-sm-2 col-xs-12">
      <select id="role" class="form-control input-sm">
        <?php echo select_payment_role(); ?>
      </select>
    </div>
  </div>

  <div class="divider-hidden"></div>
  <div class="divider-hidden"></div>
  <div class="divider-hidden"></div>

  <div class="form-group">
    <div class="col-lg-3 col-lg-offset-3 col-md-3 col-md-offset-3 col-sm-3 col-sm-offset-3 col-xs-12">
      <button type="button" class="btn btn-white btn-success btn-100 btn-block-xs" onclick="add()"><i class="fa fa-plus"></i> Add</button>
    </div>
  </div>
</div>

<script src="<?php echo base_url(); ?>scripts/masters/payment_methods.js?v=<?php echo date('Ymd'); ?>"></script>
<script src="<?php echo base_url(); ?>scripts/code_validate.js"></script>
<?php $this->load->view('include/footer'); ?>