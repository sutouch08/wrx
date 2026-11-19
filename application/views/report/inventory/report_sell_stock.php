<?php $this->load->view('include/header'); ?>
<div class="row">
  <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 padding-5 padding-top-5">
    <h3 class="title"><i class="fa fa-bar-chart"></i> <?php echo $this->title; ?></h3>
  </div>
  <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 padding-5 text-right">
    <button type="button" class="btn btn-white btn-success top-btn" onclick="getReport()"><i class="fa fa-bar-chart"></i> รายงาน</button>
    <button type="button" class="btn btn-white btn-primary top-btn" onclick="doExport()"><i class="fa fa-file-excel-o"></i> ส่งออก</button>
  </div>
</div><!-- End Row -->
<hr />

<div class="row">
  <div class="col-lg-1-harf col-md-2 col-sm-2 col-xs-6 padding-5">
    <label>สินค้า</label>
    <div class="btn-group width-100">
      <button type="button" class="btn btn-sm btn-primary width-50" id="btn-pd-all" onclick="toggleAllProduct(1)">ทั้งหมด</button>
      <button type="button" class="btn btn-sm width-50" id="btn-pd-range" onclick="toggleAllProduct(0)">เลือก</button>
    </div>
  </div>
  <div class="col-lg-2 col-md-2 col-sm-2 col-xs-6 padding-5">
    <label>เริ่มต้น</label>
    <input type="text" class="form-control input-sm r" id="pdFrom" name="pdFrom" placeholder="รหัสสินค้าเริ่มต้น" disabled />
  </div>
  <div class="col-lg-2 col-md-2 col-sm-2 col-xs-6 padding-5">
    <label>สิ้นสุด</label>
    <input type="text" class="form-control input-sm r" id="pdTo" name="pdTo" placeholder="รหัสสินค้าสิ้นสุด" disabled />
  </div>

  <div class="col-lg-1-harf col-md-2 col-sm-2 col-xs-6 padding-5">
    <label>คลัง</label>
    <div class="btn-group width-100">
      <button type="button" class="btn btn-sm btn-primary width-50" id="btn-wh-all" onclick="toggleAllWarehouse(1)">ทั้งหมด</button>
      <button type="button" class="btn btn-sm width-50" id="btn-wh-range" onclick="toggleAllWarehouse(0)">เลือก</button>
    </div>
  </div>

  <input type="hidden" id="allProduct" name="allProduct" value="1">
  <input type="hidden" id="allWarehouse" name="allWhouse" value="1">
</div>

<hr class="margin-top-15 margin-bottom-15" />

<div class="row">
  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 padding-5 table-responsive">
    <table class="table table-striped table-medium border-1" style="min-width:1080px;">
      <thead>
        <tr>
          <th class="fix-width-50 text-center">#</th>
          <th class="fix-width-100">คลัง</th>          
          <th class="fix-width-150">รหัสสินค้า</th>
          <th class="min-width-250">ชื่อสินค้า</th>
          <th class="fix-width-100 text-right">ราคาทุน</th>
          <th class="fix-width-100 text-right">คงเหลือ</th>
          <th class="fix-width-100 text-right">จอง</th>
          <th class="fix-width-100 text-right">พร้อมขาย</th>
          <th class="fix-width-100 text-right">มูลค่า</th>
          <th class="fix-width-30"></th>
        </tr>
      </thead>
      <tbody id="result-table">

      </tbody>
    </table>
  </div>
</div>

<form id="export-form" method="post" action="<?php echo $this->home; ?>/do_export">
  <input type="hidden" id="token" name="token">
  <input type="hidden" id="data" name="data">
</form>

<div class="modal fade" id="wh-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog" id="modal" style="width:500px;">
    <div class="modal-content">
      <div class="modal-header" style="border-bottom:solid 1px #ddd;">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="title text-center">เลือกคลัง</h4>
      </div>
      <div class="modal-body" id="modal_body">
        <div class="row">
          <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <label>
              <input type="checkbox" class="ace" onchange="checkAllWh($(this))" />
              <span class="lbl">&nbsp;&nbsp;ทั้งหมด</span>
            </label>
          </div>
        </div>
        <div class="row">
          <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="max-height: 400px; overflow-y: auto;">
            <?php if (!empty($whList)) : ?>
              <?php foreach ($whList as $rs) : ?>
                <label style="display:block;">
                  <input type="checkbox" class="ace chk" value="<?php echo $rs->code; ?>" />
                  <span class="lbl">&nbsp;&nbsp;<?php echo $rs->code . " | " . $rs->name; ?></span>
                </label>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default btn-block" data-dismiss="modal">ตกลง</button>
      </div>
    </div>
  </div>
</div>

<script id="template" type="text/x-handlebars-template">
  {{#each this}}
    {{#if nodata}}
      <tr>
        <td colspan="10" class="text-center">No Data Found</td>
      </tr>
    {{else}}
      {{#if @last}}
        <tr>
          <td colspan="5" class="text-right">รวม</td>
          <td class="text-right">{{totalOnHand}}</td>
          <td class="text-right">{{totalReserv}}</td>
          <td class="text-right">{{totalQty}}</td>
          <td class="text-right">{{totalAmount}}</td>
          <td></td>
        </tr>
      {{else}}
        <tr>
          <td class="text-center">{{no}}</td>
          <td>{{whsCode}}</td>          
          <td>{{pdCode}}</td>
          <td>{{pdName}}</td>
          <td class="text-right">{{cost}}</td>
          <td class="text-right">{{onHand}}</td>
          <td class="text-right">{{reserv}}</td>
          <td class="text-right">{{available}}</td>
          <td class="text-right">{{amount}}</td>
          <td></td>
        </tr>
      {{/if}}
    {{/if}}
  {{/each}}
</script>

<script src="<?php echo base_url(); ?>scripts/report/inventory/sell_stock.js?v=<?php echo date('Ymd'); ?>"></script>
<?php $this->load->view('include/footer'); ?>