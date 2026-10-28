<div class="row">
  <div class="col-lg-1-harf col-md-1-harf col-sm-2 col-xs-4 padding-5">
    <label>บาร์โค้ดสินค้า</label>
    <input type="text" class="form-control input-sm" id="barcode-item" />
  </div>
  <div class="col-lg-2-harf col-md-2 col-sm-4 col-xs-8 padding-5">
    <label>สินค้า</label>
    <input type="text" class="form-control input-sm" id="item-code" />
  </div>

  <div class="col-lg-1 col-md-1-harf col-sm-2 col-xs-4 padding-5">
    <label>ราคา</label>
    <input type="number" class="form-control input-sm text-center" id="txt-price" />
  </div>

  <div class="col-lg-1-harf col-md-1-harf col-sm-2 col-xs-4 padding-5">
    <label>ส่วนลด</label>
    <input type="text" class="form-control input-sm text-center" id="txt-disc" />
  </div>

  <div class="col-lg-1 col-md-1 col-sm-2 col-xs-4 padding-5">
    <label>ในโซน</label>
    <label class="form-control input-sm text-center blue" style="margin-bottom:0px;" id="stock-qty">0</label>
  </div>
  <div class="col-lg-1 col-md-1 col-sm-1-harf col-xs-4 padding-5">
    <label>จำนวน</label>
    <input type="number" class="form-control input-sm text-center" id="txt-qty" />
  </div>

  <div class="col-lg-1-harf col-md-1-harf col-sm-2-harf col-xs-4 padding-5">
    <label>มูลค่า</label>
    <span class="form-control input-sm text-center" id="txt-amount">0</span>
  </div>

  <div class="col-lg-1 col-md-1 col-sm-1-harf col-xs-2 padding-5">
    <label class="not-show">submit</label>
    <button type="button" class="btn btn-xs btn-primary btn-block" onclick="addToDetail()">เพิ่ม</button>
  </div>
  <div class="col-lg-1 col-md-1 col-sm-1-harf col-xs-2 padding-5">
    <label class="not-show">Reset</label>
    <button type="button" class="btn btn-xs btn-default btn-block" onclick="clearFields()">เคลียร์</button>
  </div>
</div>

<input type="hidden" id="product_code" />
<input type="hidden" id="count_stock" value="1" />
<hr class="margin-top-15 margin-bottom-15" />

<div class="row margin-bottom-5">
  <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 padding-5">
    <?php if (getConfig('ALLOW_EDIT_PRICE')) : ?>
      <button type="button" class="btn btn-white btn-sm btn-purple" id="btn-edit-price" onclick="getEditPrice()">แก้ไขราคา</button>
      <button type="button" class="btn btn-white btn-sm btn-primary hide" id="btn-update-price" onclick="updatePrice()">บันทึกราคา</button>
    <?php endif; ?>
    <?php if (getConfig('ALLOW_EDIT_DISCOUNT')) : ?>
      <button type="button" class="btn btn-white btn-sm btn-purple" id="btn-edit-disc" onclick="getEditDiscount()">แก้ไขส่วนลด</button>
      <button type="button" class="btn btn-white btn-sm btn-primary hide" id="btn-update-disc" onclick="updateDiscount()">บันทึกส่วนลด</button>
    <?php endif; ?>
  </div>
  <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 padding-5 text-right">
    <?php if (! empty($doc->ref_code)) : ?>
      <button type="button" class="btn btn-white btn-sm btn-danger" onclick="clearImportDetail('<?php echo $doc->ref_code; ?>')"><i class="fa fa-times"></i> ลบเอกสารกระทบยอด (<?php echo $doc->ref_code; ?>)</button>
    <?php endif; ?>
    <button type="button" class="btn btn-white btn-sm btn-danger" onclick="removeChecked()"><i class="fa fa-check-square-o"></i> ลบที่เลือก</button>
  </div>
</div>