<?php $this->load->view('include/header'); ?>
<div class="row">
  <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 padding-5 padding-top-5">
    <h3 class="title"><i class="fa fa-bar-chart"></i><?php echo $this->title; ?></h3>
  </div>
  <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 padding-5 text-right">
    <button type="button" class="btn btn-white btn-success top-btn" onclick="getReport()"><i class="fa fa-bar-chart"></i> รายงาน</button>
    <button type="button" class="btn btn-white btn-primary top-btn" onclick="doExport()"><i class="fa fa-file-excel-o"></i> ส่งออก</button>
  </div>
</div>
<hr />
<form id="reportForm" method="post" action="<?php echo $this->home; ?>/do_export">
  <div class="row">
    <div class="col-lg-2 col-md-2 col-sm-2 col-xs-6 padding-5">
      <label>สินค้า</label>
      <div class="btn-group width-100">
        <button type="button" class="btn btn-sm btn-primary width-50" id="btn-pd-all" onclick="toggleAllProduct(1)">ทั้งหมด</button>
        <button type="button" class="btn btn-sm width-50" id="btn-pd-range" onclick="toggleAllProduct(0)">เลือก</button>
      </div>
    </div>
    <div class="col-lg-2 col-md-2 col-sm-3 col-xs-6 padding-5">
      <label class="not-show">start</label>
      <input type="text" class="form-control input-sm text-center" id="pdFrom" name="pdFrom" disabled>
    </div>
    <div class="col-lg-2 col-md-2 col-sm-2 col-xs-6 padding-5">
      <label class="not-show">End</label>
      <input type="text" class="form-control input-sm text-center" id="pdTo" name="pdTo" disabled>
    </div>
    <div class="col-lg-2 col-md-2 col-sm-2 col-xs-6 padding-5">
      <label>คลัง</label>
      <div class="btn-group width-100">
        <button type="button" class="btn btn-sm btn-primary width-50" id="btn-wh-all" onclick="toggleAllWarehouse(1)">ทั้งหมด</button>
        <button type="button" class="btn btn-sm width-50" id="btn-wh-range" onclick="toggleAllWarehouse(0)">เลือก</button>
      </div>
    </div>

    <input type="hidden" id="allProduct" name="allProduct" value="1">
    <input type="hidden" id="allWarehouse" name="allWhouse" value="1">
  </div>


  <div class="modal fade" id="wh-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog" id="modal" style="width:500px;">
      <div class="modal-content">
        <div class="modal-header" style="border-bottom:solid 1px #ccc;">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="title" id="modal_title">เลือกคลัง</h4>
        </div>
        <div class="modal-body" id="modal_body" style="padding-top:10px;">
          <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
              <label>
                <input type="checkbox" class="ace" id="chk-wh-all" onclick="checkWhsAll(this)" />
                <span class="lbl">&nbsp;&nbsp;ทั้งหมด</span>
              </label>
            </div>
          </div>
          <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="max-height:400px; overflow-y:auto;">
              <?php if (!empty($whList)) : ?>
                <?php foreach ($whList as $rs) : ?>
                  <label style="display:block;">
                    <input type="checkbox" class="ace chk"
                      id="<?php echo $rs->code; ?>"
                      name="warehouse[<?php echo $rs->code; ?>]"
                      value="<?php echo $rs->code; ?>"
                      style="margin-right:10px;" />
                    <span class="lbl">&nbsp;&nbsp;<?php echo $rs->code; ?> | <?php echo $rs->name; ?></span>
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
</form>

<div class="row">
  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 padding-5 table-responsive">
    <table class="table table-striped table-medium border-1" style="min-width:1000px;">
      <thead>
        <tr>
          <th class="fix-width-50 middle text-center">#</th>
          <th class="fix-width-120 middle">บาร์โค้ด</th>
          <th class="fix-width-150 middle">รหัส</th>
          <th class="min-width-300 middle">สินค้า</th>
          <th class="fix-width-100 middle text-right">ทุน</th>
          <th class="fix-width-100 middle text-right">คงเหลือ</th>
          <th class="fix-width-150 middle text-right">มูลค่า</th>
          <th class="fix-width-30 middle"></th>
        </tr>
      </thead>
      <tbody id="rs">

      </tbody>
    </table>
  </div>
</div>


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
          <td colspan="5" class="text-right">รวม</td>
          <td class="text-right">{{ totalQty }}</td>
          <td class="text-right">{{ totalAmount }}</td>
          <td></td>
        </tr>
      {{else}}
        <tr class="font-size-12">
          <td class="middle text-center">{{no}}</td>
          <td class="middle">{{ barcode }}</td>
          <td class="middle">{{ pdCode }}</td>
          <td class="middle">{{ pdName }}</td>
          <td class="middle text-right">{{ cost }}</td>
          <td class="middle text-right">{{ qty }}</td>
          <td class="middle text-right">{{ amount }}</td>
          <td class="middle text-center"></td>
        </tr>
      {{/if}}
    {{/if}}
  {{/each}}
</script>

<script src="<?php echo base_url(); ?>scripts/report/inventory/stock_balance.js?v=<?php echo date('Ymd'); ?>"></script>
<?php $this->load->view('include/footer'); ?>