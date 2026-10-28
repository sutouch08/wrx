<?php $this->load->view('include/header'); ?>
<div class="row">
  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 padding-5 padding-top-5">
    <h3 class="title"><?php echo $this->title; ?></h3>
  </div>  
</div>
<hr/>
<form id="searchForm" method="post" action="<?php echo current_url(); ?>">
  <div class="row">
    <div class="col-lg-2 col-md-2-harf col-sm-2-harf col-xs-6 padding-5">
      <label>เลขที่เอกสาร</label>
      <input type="text" class="form-control input-sm text-center" name="reference" id="ref" value="<?php echo $reference; ?>" />
    </div>
    <div class="col-lg-2 col-md-2-harf col-sm-2-harf col-xs-6 padding-5">
      <label>รหัสสินค้า</label>
      <input type="text" class="form-control input-sm text-center" name="product_code" id="pd-code" value="<?php echo $product_code; ?>" />
    </div>
    <div class="col-lg-4 col-md-4-harf col-sm-4-harf col-xs-6 padding-5">
      <label>คลัง</label>
      <select class="form-control input-sm" name="warehouse_code" id="warehouse">
        <option value="all">ทั้งหมด</option>
        <?php echo select_warehouse($warehouse_code); ?>
      </select>
    </div>
    <div class="col-lg-2 col-md-2-harf col-sm-2-harf col-xs-6 padding-5">
      <label>รหัสโซน</label>
      <input type="text" class="form-control input-sm text-center" name="zone_code" id="zone-code" value="<?php echo $zone_code; ?>" />
    </div>
    <div class="col-lg-2 col-md-3 col-sm-3 col-xs-6 padding-5">
      <label>วันที่</label>
      <div class="input-daterange input-group">
        <input type="text" class="form-control input-sm width-50 text-center from-date" name="from_date" id="fromDate" value="<?php echo $from_date; ?>" />
        <input type="text" class="form-control input-sm width-50 text-center" name="to_date" id="toDate" value="<?php echo $to_date; ?>" />
      </div>
    </div>
    <div class="col-lg-2 col-md-2 col-sm-3 col-xs-6 padding-5">
      <label>ช่วงข้อมูล</label>
      <select class="form-control input-sm" name="range" id="data-range">
        <option value="500000" <?php echo is_selected('500000', $range); ?>>500,000 รายการล่าสุด</option>
        <option value="1000000" <?php echo is_selected('1000000', $range); ?>>1,000,000 รายการล่าสุด</option>
        <option value="2000000" <?php echo is_selected('2000000', $range); ?>>2,000,000 รายการล่าสุด</option>
        <option value="3000000" <?php echo is_selected('3000000', $range); ?>>3,000,000 รายการล่าสุด</option>
        <option value="5000000" <?php echo is_selected('5000000', $range); ?>>5,000,000 รายการล่าสุด</option>
        <option value="0" <?php echo is_selected('0', $range); ?>>ทั้งหมด</option>
      </select>
    </div>
    <div class="col-lg-1 col-md-1-harf col-sm-1-harf col-xs-4 padding-5">
      <label class="display-block not-show">search</label>
      <button type="button" class="btn btn-xs btn-primary btn-block" onclick="getSearch()">Search</button>
    </div>
    <div class="col-lg-1 col-md-1-harf col-sm-1-harf col-xs-4 padding-5">
      <label class="display-block not-show">reset</label>
      <button type="button" class="btn btn-xs btn-warning btn-block" onclick="clearFilter()">Reset</button>
    </div>
    <div class="col-lg-1 col-md-1-harf col-sm-1-harf col-xs-4 padding-5">
      <label class="display-block not-show">export</label>
      <button type="button" class="btn btn-xs btn-purple btn-block" onclick="exportFilter()"><i class="fa fa-file-excel-o"></i>&nbsp; Export</button>
    </div>
  </div>
  <input type="hidden" name="search" value="1" />
</form>
<form id="export-form" action="<?php echo $this->home; ?>/export_filter" method="post">
  <input type="hidden" name="export-filter" id="export-filter" />  
</form>
<hr class="margin-top-15"/>
<?php echo $this->pagination->create_links(); ?>
<div class="row">
  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 padding-5 table-responsive">
    <table class="table table-striped border-1" style="min-width:1150px;">
      <thead>
        <tr class="font-size-11">
          <th class="fix-width-150">เลขที่เอกสาร</th>
          <th class="fix-width-200">รหัสสินค้า</th>
          <th class="fix-width-100">Batch No.</th>
          <th class="fix-width-150">รหัสคลัง</th>
          <th class="fix-width-200">รหัสโซน</th>
          <th class="fix-width-100">เข้า</th>
          <th class="fix-width-100">ออก</th>
          <th class="min-width-150">วันที่</th>
        </tr>
      </thead>
      <tbody>
    <?php if( ! empty($data)) : ?>
      <?php foreach($data as $rs) : ?>
        <tr class="font-size-11">
          <td><?php echo $rs->reference; ?></td>
          <td><?php echo $rs->product_code; ?></td>
          <td><?php echo $rs->batchNum; ?></td>
          <td><?php echo $rs->warehouse_code; ?></td>
          <td><?php echo $rs->zone_code; ?></td>
          <td><?php echo ac_format($rs->move_in, 2); ?></td>
          <td><?php echo ac_format($rs->move_out, 2); ?></td>
          <td><?php echo thai_date($rs->date_upd, TRUE); ?></td>
        </tr>
      <?php endforeach; ?>
    <?php else : ?>
      <tr><td colspan="7" class="text-center"> -- ไม่พบข้อมูล --</td></tr>
    <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
  $('#warehouse').select2();
</script>

<script src="<?php echo base_url(); ?>scripts/inventory/movement/movement.js?v=<?php echo date('YmdH'); ?>"></script>

<?php $this->load->view('include/footer'); ?>
