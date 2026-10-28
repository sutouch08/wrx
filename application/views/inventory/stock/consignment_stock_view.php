<?php $this->load->view('include/header'); ?>
<div class="row">
  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 padding-5">
    <h3 class="title"><?php echo $this->title; ?></h3>
  </div>
</div>
<hr />
<form id="searchForm" method="post" action="<?php echo current_url(); ?>">
  <div class="row">
    <div class="col-lg-2 col-md-3 col-sm-3 col-xs-12 padding-5">
      <label>Item Code</label>
      <input type="text" class="form-control input-sm search" id="item_code" name="item_code" value="<?php echo $item_code; ?>" />
    </div>

    <div class="col-lg-2 col-md-3 col-sm-3 col-xs-12 padding-5">
      <label>Zone Code</label>
      <input type="text" class="form-control input-sm search" id="zone_code" name="zone_code" value="<?php echo $zone_code; ?>" />
    </div>

    <div class="col-lg-1-harf col-md-1-harf col-sm-1-harf col-xs-12 padding-5">
      <label>System Zone</label>
      <select class="form-control input-sm" id="show_system" name="show_system">
        <option value="no" <?php echo is_selected('no', $show_system); ?>>Hide</option>
        <option value="yes" <?php echo is_selected('yes', $show_system); ?>>Show</option>
      </select>
    </div>

    <div class="col-lg-1 col-md-1-harf col-sm-1-harf col-xs-4 padding-5">
      <label class="display-block not-show">buton</label>
      <button type="submit" class="btn btn-xs btn-primary btn-block"><i class="fa fa-search"></i> ค้นหา</button>
    </div>
    <div class="col-lg-1 col-md-1-harf col-sm-1-harf col-xs-4 padding-5">
      <label class="display-block not-show">buton</label>
      <button type="button" class="btn btn-xs btn-warning btn-block" onclick="clearFilter()"><i class="fa fa-retweet"></i> Reset</button>
    </div>
    <div class="col-lg-1 col-md-1-harf col-sm-1-harf col-xs-4 padding-5">
      <label class="display-block not-show">buton</label>
      <button type="button" class="btn btn-xs btn-purple btn-block" onclick="doExport()"><i class="fa fa-file-excel-o"></i>&nbsp; Export</button>
    </div>
  </div>
  <input type="hidden" name="search" value="1">
</form>
<hr class="margin-top-15">
<form id="exportForm" method="post" action="<?php echo $this->home; ?>/export">
  <input type="hidden" id="item" name="item">
  <input type="hidden" id="zone" name="zone">
  <input type="hidden" id="system" name="system">
  <input type="hidden" id="token" name="token" value="<?php echo uniqid(); ?>">
</form>
<?php echo $this->pagination->create_links(); ?>
<div class="row">
  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 padding-5 table-responsive">
    <table class="table table-striped table-hover table-medium border-1" style="min-width:700px;">
      <tr>
        <th class="fix-width-50 text-center">#</th>
        <th class="fix-width-150">Item Code</th>
        <th class="fix-width-150">Zone Code</th>
        <th class="min-width-250">Zone Name</th>
        <th class="fix-width-100 text-right">Qty.</th>
        <th class="fix-width-20"></th>
      </tr>
      <tbody>
        <?php if (!empty($data)) : ?>
          <?php $no = $this->uri->segment($this->segment) + 1; ?>
          <?php foreach ($data as $rs) : ?>
            <tr>
              <td class="text-center no"><?php echo $no; ?></td>
              <td><?php echo $rs->ItemCode; ?></td>
              <td><?php echo $rs->BinCode; ?></td>
              <td><?php echo $rs->Descr; ?></td>
              <td class="text-right"><?php echo number($rs->OnHandQty, 2); ?></td>
              <td></td>
            </tr>
            <?php $no++; ?>
          <?php endforeach; ?>
        <?php else : ?>
          <tr>
            <td colspan="6" class="text-center">--- No Data Found ---</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
  function doExport() {
    var item = $('#item_code').val();
    var zone = $('#zone_code').val();
    var system = $('#show_system').val();
    var token = $('#token').val();
    if (item.length > 0 || zone.length > 0) {
      $('#item').val(item);
      $('#zone').val(zone);
      $('#system').val(system);
      get_download(token);
      $('#exportForm').submit();
    }
  }
</script>
<?php $this->load->view('include/footer'); ?>