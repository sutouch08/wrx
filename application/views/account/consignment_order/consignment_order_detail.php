<div class="row">
  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 padding-5 table-responsive">
    <table class="table table-striped table-medium border-1" style="min-width:920px;">
      <thead>
        <tr>
          <?php if (!isset($view_detail)) : ?>
            <th class="fix-width-40 text-center">
              <label>
                <input type="checkbox" class="ace" id="chk-all" onchange="toggleCheckAll()" />
                <span class="lbl"></span>
              </label>
            </th>
          <?php endif; ?>
          <th class="fix-width-40 text-center">#</th>
          <th class="fix-width-150">รหัสสินค้า</th>
          <th class="min-width-250">สินค้า</th>
          <th class="fix-width-100 text-right">ราคา</th>
          <th class="fix-width-100 text-right">ส่วนลด</th>
          <th class="fix-width-100 text-right">จำนวน</th>
          <th class="fix-width-100 text-right">มูลค่า</th>
          <th class="fix-width-40"></th>
        </tr>
      </thead>
      <tbody id="detail-table">
        <?php if (!empty($details)) : ?>
          <?php $no = 1; ?>
          <?php $totalQty = 0; ?>
          <?php $totalAmount = 0; ?>
          <?php foreach ($details as $rs) : ?>
            <tr class="rox" id="row-<?php echo $rs->id; ?>">
              <?php if (!isset($view_detail)) : ?>
                <td class="middle text-center">
                  <label>
                    <input type="checkbox" class="ace chk" data-id="<?php echo $rs->id; ?>" />
                    <span class="lbl"></span>
                  </label>
                </td>
              <?php endif; ?>
              <td class="middle text-center no"><?php echo $no; ?></td>
              <td class="middle"><?php echo $rs->product_code; ?></td>
              <td class="middle hide-text"><?php echo $rs->product_name; ?></td>
              <td class="middle text-right">
                <span class="price" id="price-<?php echo $rs->id; ?>"><?php echo number($rs->price, 2); ?></span>
                <input type="number" class="form-control input-xs text-center hide input-price" id="input-price-<?php echo $rs->id; ?>" value="<?php echo round($rs->price, 2); ?>" />
              </td>
              <td class="middle text-right">
                <span class="disc" id="disc-<?php echo $rs->id; ?>"><?php echo $rs->discount; ?></span>
                <input type="text" class="form-control input-xs text-center hide input-disc" id="input-disc-<?php echo $rs->id; ?>" value="<?php echo $rs->discount; ?>" />
              </td>
              <td class="middle text-right qty" id="qty-<?php echo $rs->id; ?>"><?php echo number($rs->qty); ?></td>
              <td class="middle text-right amount" id="amount-<?php echo $rs->id; ?>"><?php echo number($rs->amount, 2); ?></td>
              <td class="middle text-center">
                <?php if (! isset($view_detail)) : ?>
                  <?php if ($rs->status == 0 && ($this->pm->can_edit or $this->pm->can_delete)) : ?>
                    <a href="javascript:deleteRow('<?php echo $rs->id; ?>', '<?php echo $rs->product_code; ?>')"><i class="fa fa-times fa-lg red"></i></a>
                  <?php endif; ?>
                <?php endif; ?>
              </td>
            </tr>
            <?php $no++; ?>
            <?php $totalQty += $rs->qty; ?>
            <?php $totalAmount += $rs->amount; ?>
          <?php endforeach; ?>
          <tr id="total-row">
            <?php if (!isset($view_detail)) : ?>
              <td></td>
            <?php endif; ?>
            <td colspan="5" class="middle text-right"><strong>รวม</strong></td>
            <td id="total-qty" class="middle text-right"><?php echo number($totalQty); ?></td>
            <td id="total-amount" class="middle text-right"><?php echo number($totalAmount, 2); ?></td>
            <td></td>
          </tr>
        <?php else : ?>
          <tr id="total-row">
            <?php if (!isset($view_detail)) : ?>
              <td></td>
            <?php endif; ?>
            <td colspan="5" class="middle text-right"><strong>รวม</strong></td>
            <td id="total-qty" class="middle text-right">0</td>
            <td id="total-amount" class="middle text-right">0</td>
            <td></td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script id="new-row-template" type="text/x-handlebars-template">
  <tr class="font-size-12 rox" id="row-{{id}}">
    <td class="middle text-center">
      <label>
        <input type="checkbox" class="ace chk" data-id="{{id}}" />
        <span class="lbl"></span>
      </label>
    </td>
    <td class="middle text-center no"></td>
    <td class="middle">{{product_code}}</td>
    <td class="middle">{{product_name}}</td>
    <td class="middle text-right">
      <span class="price" id="price-{{id}}">{{price}}</span>
      <input type="number" class="form-control input-xs text-center hide input-price" id="input-price-{{id}}" value="{{price}}" />
    </td>
    <td class="middle text-right">
      <span class="disc" id="disc-{{id}}">{{discount}}</span>
      <input type="text" class="form-control input-xs text-center hide input-disc" id="input-disc-{{id}}" value="{{discount}}" />
    </td>
    <td class="middle text-right qty" id="qty-{{id}}">{{qty}}</td>
    <td class="middle text-right amount" id="amount-{{id}}">{{amount}}</td>
    <td class="middle text-center"><a href="javascript:deleteRow('{{id}}', '{{product_code}}')"><i class="fa fa-times fa-lg red"></i></a></td>    
  </tr>
</script>


<script id="row-template" type="text/x-handlebars-template">
  <td class="middle text-center">
    <label>
      <input type="checkbox" class="ace chk" data-id="{{id}}" />
      <span class="lbl"></span>
    </label>
  </td>
  <td class="middle text-center no"></td>
  <td class="middle">{{product_code}}</td>
  <td class="middle">{{product_name}}</td>
  <td class="middle text-right price" id="price-{{id}}">{{price}}</td>
  <td class="middle text-right disc" id="disc-{{id}}">{{discount}}</td>
  <td class="middle text-right qty" id="qty-{{id}}">{{qty}}</td>
  <td class="middle text-right amount" id="amount-{{id}}">{{amount}}</td>
  <td class="middle text-center"><a href="javascript:deleteRow('{{id}}', '{{product_code}}')"><i class="fa fa-times fa-lg red"></i></a></td>
</script>