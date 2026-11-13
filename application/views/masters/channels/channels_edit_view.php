<?php $this->load->view('include/header'); ?>
<div class="row">
  <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 padding-5 padding-top-5">
    <h3 class="title"><?php echo $this->title; ?></h3>
  </div>
  <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 padding-5 text-right">
    <button type="button" class="btn btn-white btn-default top-btn" onclick="goBack()"><i class="fa fa-arrow-left"></i> Back</button>
  </div>
</div><!-- End Row -->
<hr />
<div class="form-horizontal">
  <div class="form-group">
    <label class="col-lg-3 col-md-3 col-sm-3 col-xs-12 control-label no-padding-right">รหัส</label>
    <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">
      <input type="text" id="code" class="form-control input-sm r" maxlength="10" value="<?php echo $data->code; ?>" disabled />
    </div>
  </div>

  <div class="form-group">
    <label class="col-lg-3 col-md-3 col-sm-3 col-xs-12 control-label no-padding-right">ชื่อ</label>
    <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">
      <input type="text" id="name" class="form-control input-sm r" value="<?php echo $data->name; ?>" autocomplete="off" />
    </div>
  </div>

  <div class="form-group">
    <label class="col-lg-3 col-md-3 col-sm-3 col-xs-12 control-label no-padding-right">ลูกค้าเริ่มต้น</label>
    <div class="col-lg-1-harf col-md-1-harf col-sm-1-harf col-xs-12 padding-right-5">
      <input type="text" id="customer-code" class="form-control input-sm r " value="<?php echo $data->customer_code; ?>" autocomplete="off" />
    </div>
    <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12 padding-left-5">
      <input type="text" id="customer-name" class="form-control input-sm r" value="<?php echo escape_quotes($data->customer_name); ?>" autocomplete="off" readonly />
    </div>
  </div>

  <div class="form-group">
    <label class="col-lg-3 col-md-3 col-sm-3 col-xs-12 control-label no-padding-right"></label>
    <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">
      <label>
        <input type="checkbox" class="ace" id="is-online" value="1" <?php echo $data->is_online ? 'checked' : ''; ?> />
        <span class="lbl">&nbsp; &nbsp;Online</span>
      </label>
    </div>
  </div>

  <div class="divider-hidden"></div>
  <div class="divider-hidden"></div>

  <div class="form-group">
    <label class="col-lg-3 col-md-3 col-sm-3 col-xs-12 control-label no-padding-right"></label>
    <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">
      <button type="button" class="btn btn-white btn-success btn-100 btn-block-xs" onclick="update()"><i class="fa fa-plus"></i>&nbsp; Update</button>
    </div>
  </div>
</div>

<script src="<?php echo base_url(); ?>scripts/masters/channels.js?v=<?php echo date('Ymd'); ?>"></script>
<script src="<?php echo base_url(); ?>scripts/code_validate.js?v=<?php echo date('Ymd'); ?>"></script>
<?php $this->load->view('include/footer'); ?>