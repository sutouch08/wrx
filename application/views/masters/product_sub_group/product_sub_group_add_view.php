<?php $this->load->view('include/header'); ?>
<div class="row">
  <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 padding-5 padding-top-5">
    <h3 class="title"><?php echo $this->title; ?></h3>
  </div>
  <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 padding-5 text-right">
    <button type="button" class="btn btn-white btn-default top-btn" onclick="goBack()"><i class="fa fa-arrow-left"></i> Back</button>
  </div>
</div><!-- End Row -->
<hr class="" />
<div class="form-horizontal margin-top-30">
  <div class="form-group">
    <label class="col-lg-3 col-md-3 col-sm-3 col-xs-12 control-label no-padding-right">รหัส</label>
    <div class="col-lg-2 col-md-3 col-sm-3 col-xs-12">
      <input type="text" id="code" class="form-control input-sm r" maxlength="20" value="" autocomplete="off" autofocus />
    </div>
  </div>

  <div class="form-group">
    <label class="col-lg-3 col-md-3 col-sm-3 col-xs-12 control-label no-padding-right">ชื่อ</label>
    <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
      <input type="text" id="name" class="form-control input-sm r" maxlength="100" value="" autocomplete="off" />
    </div>
  </div>

  <div class="divider-hidden"></div>

  <div class="form-group">
    <div class="col-lg-2 col-lg-offset-3 col-md-3 col-md-offset-3 col-sm-3 col-sm-offset-3 col-xs-12">
      <button type="button" class="btn btn-white btn-success btn-100 btn-block-xs" onclick="add()"><i class="fa fa-plus"></i>&nbsp; Add</button>
    </div>
  </div>
</div>

<script src="<?php echo base_url(); ?>scripts/masters/product_sub_group.js?v=<?php echo date('Ymd'); ?>"></script>
<?php $this->load->view('include/footer'); ?>