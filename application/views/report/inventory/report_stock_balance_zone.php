<?php $this->load->view("include/header"); ?>
<div class="row">
  <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 padding-5 padding-top-5">
    <h3 class="title"><i class="fa fa-bar-chart"></i><?php echo $this->title; ?></h3>
  </div>
  <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 text-right">
    <button type="button" class="btn btn-white btn-success top-btn" onclick="getReport()"><i class="fa fa-bar-chart"></i> รายงาน</button>
    <button type="button" class="btn btn-white btn-primary top-btn" onclick="doExport()"><i class="fa fa-file-excel-o"></i> ส่งออก</button>
    <button type="button" class="btn btn-white btn-purple top-btn" onclick="exportToCheck()"><i class="fa fa-file-excel-o"></i> ส่งออกยอดตั้งต้น</button>
  </div>
</div><!-- End Row -->
<hr />
<div class="row">
  <div class="col-lg-2 col-md-2 col-sm-2-harf col-xs-6 padding-5 ">
    <label class="display-block">สินค้า</label>
    <div class="btn-group width-100" style="height:30px;">
      <button type="button" class="btn btn-sm btn-primary width-50" id="btn-pd-all" onclick="toggleAllProduct(1)">ทั้งหมด</button>
      <button type="button" class="btn btn-sm width-50" id="btn-pd-range" onclick="toggleAllProduct(0)">เลือก</button>
    </div>
  </div>
  <div class="col-lg-2 col-md-2 col-sm-4 col-xs-6 padding-5">
    <label class="">เริ่มต้น</label>
    <input type="text" class="form-control input-sm text-center r" id="pdFrom" name="pdFrom" disabled>
  </div>
  <div class="col-lg-2 col-md-2 col-sm-4 col-xs-6 padding-5">
    <label class="">สิ้นสุด</label>
    <input type="text" class="form-control input-sm text-center r" id="pdTo" name="pdTo" disabled>
  </div>
  <div class="col-lg-2 col-md-2 col-sm-2-harf col-xs-6 padding-5 ">
    <label class="display-block">คลัง</label>
    <div class="btn-group width-100" style="height:30px;">
      <button type="button" class="btn btn-sm btn-primary width-50" id="btn-wh-all" onclick="toggleAllWarehouse(1)">ทั้งหมด</button>
      <button type="button" class="btn btn-sm width-50" id="btn-wh-range" onclick="toggleAllWarehouse(0)">เลือก</button>
    </div>
  </div>

  <div class="col-lg-2 col-md-2 col-sm-2-harf col-xs-6 padding-5 ">
    <label class="display-block">โซน</label>
    <div class="btn-group width-100" style="height:30px;">
      <button type="button" class="btn btn-sm btn-primary width-50" id="btn-zone-all" onclick="toggleAllZone(1)">ทั้งหมด</button>
      <button type="button" class="btn btn-sm width-50" id="btn-zone-range" onclick="toggleAllZone(0)">เลือก</button>
    </div>
  </div>

  <div class="col-lg-2 col-md-2 col-sm-7 col-xs-6 padding-5 ">
    <label class="display-block not-show">zone</label>
    <input type="text" class="form-control input-sm r" name="zoneName" id="zoneName" disabled>
  </div>
  <input type="hidden" id="allProduct" name="allProduct" value="1">
  <input type="hidden" id="allWarehouse" name="allWhouse" value="1">
  <input type="hidden" id="allZone" name="allZone" value="1">
  <input type="hidden" id="zoneCode" name="zoneCode" value="">  
</div>


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
              <input type="checkbox" class="ace" id="chk-wh-all" onchange="checkAllWh($(this))" />
              <span class="lbl">ทั้งหมด</span>
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
<hr>

<div class="row">
  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 table-responsive padding-5">
    <table class="table table-striped table-medium border-1" style="min-width:1000px;">
      <thead>
        <tr>
          <th class="fix-width-40 text-center">ลำดับ</th>
          <th class="fix-width-100">คลัง</th>
          <th class="min-width-200">โซน</th>
          <th class="fix-width-150">รหัส</th>
          <th class="min-width-200">สินค้า</th>
          <th class="fix-width-100 text-right">ราคาขาย</th>
          <th class="fix-width-100 text-right">คงเหลือ</th>
          <th class="fix-width-120 text-right">มูลค่า</th>
        </tr>
      </thead>
      <tbody id="rs">

      </tbody>
    </table>
  </div>
</div>

<form id="exportForm" method="post" action="<?php echo $this->home; ?>/export_to_check">
  <input type="hidden" name="zone_code" id="export-zone-code" />
  <input type="hidden" id="export-token" name="token">
</form>

<form id="export-form" method="post" action="<?php echo $this->home; ?>/do_export">
  <input type="hidden" id="data" name="data" />
  <input type="hidden" id="token" name="token">
</form>


<script id="template" type="text/x-handlebars-template">
  {{#each bs}}
    {{#if nodata}}
      <tr>
        <td colspan="8" class="text-center">
          <h4>----- ไม่พบสินค้าคงเหลือตามเงื่อนไขที่กำหนด -----</h4>
        </td>
      </tr>
    {{else}}
      {{#if @last}}
        <tr class="font-size-14">
          <td colspan="6" class="text-right">รวม</td>
          <td class="text-right">{{ totalQty }}</td>
          <td class="text-right">{{ totalAmount }}</td>
        </tr>
      {{else}}
        <tr>
          <td class="middle text-center">{{no}}</td>
          <td class="middle">{{ warehouse }}</td>
          <td class="middle">{{ zone }}</td>
          <td class="middle">{{ pdCode }}</td>
          <td class="middle">{{ pdName }}</td>
          <td class="middle text-right">{{ price }}</td>
          <td class="middle text-right">{{ qty }}</td>
          <td class="middle text-right">{{ amount }}</td>
        </tr>
      {{/if}}
    {{/if}}
  {{/each}}
</script>

<script src="<?php echo base_url(); ?>scripts/report/inventory/stock_balance_zone.js?v=<?php echo date("Ymd"); ?>"></script>
<?php $this->load->view("include/footer"); ?>