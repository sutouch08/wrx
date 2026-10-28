<div class="row">
  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 padding-5 table-responsive">
    <table class="table table-striped table-bordered table-medium" style="min-width:950px;">
      <thead>
        <tr>
          <th class="fix-width-60 text-center"></th>
          <th class="fix-width-40 middle text-center">#</th>
          <th class="fix-width-100 middle text-center">เลขที่</th>
          <th class="min-width-250 middle text-center">ชื่อกฏ</th>
          <th class="fix-width-100 middle text-center">ส่วนลด</th>
          <th class="fix-width-80 text-center">ลูกค้า</th>
          <th class="fix-width-80 text-center">สิ้นค้า</th>
          <th class="fix-width-80 text-center">ช่องทาง</th>
          <th class="fix-width-80 text-center">การชำระเงิน</th>
          <th class="fix-width-80 text-center">ขั้นต่ำ</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($rules)) : ?>
          <?php $no = 1; ?>
          <?php foreach ($rules as $rs) : ?>
            <tr id="row_<?php echo $rs->id; ?>">
              <td class="middle">
                <?php if (empty($view_detail)) : ?>
                  <button type="button" class="btn btn-minier btn-info" onclick="viewRuleDetail('<?php echo $rs->id; ?>')"><i class="fa fa-eye"></i></button>
                  <?php if ($this->pm->can_edit) : ?>
                    <button type="button" class="btn btn-minier btn-danger" onclick="unlinkRule(<?php echo $rs->id; ?>, '<?php echo $rs->code; ?>')"><i class="fa fa-trash"></i></button>
                  <?php endif; ?>
                <?php endif; ?>
              </td>
              <td class="middle text-center"><?php echo $no; ?></td>
              <td class="middle text-center"><?php echo $rs->code; ?></td>
              <td class="middle"><?php echo $rs->name; ?></td>
              <td class="middle text-center"><?php echo showItemDiscountLabel($rs->item_price, $rs->item_disc, $rs->item_disc_unit); ?></td>
              <td class="middle text-center"><?php echo ($rs->all_customer == 1 ? 'ทั้งหมด' : 'กำหนดค่า'); ?></td>
              <td class="middle text-center"><?php echo ($rs->all_product == 1 ? 'ทั้งหมด' : 'กำหนดค่า'); ?></td>
              <td class="middle text-center"><?php echo ($rs->all_channels == 1 ? 'ทั้งหมด' : 'กำหนดค่า'); ?></td>
              <td class="middle text-center"><?php echo ($rs->all_payment == 1 ? 'ทั้งหมด' : 'กำหนดค่า'); ?></td>
              <td class="middle text-center"><?php echo ($rs->qty > 0 ? $rs->qty . ' pcs' : ($rs->amount > 0 ? $rs->amount . ' ' . getConfig('CURRENTCY') : 'No')); ?></td>
            </tr>
            <?php $no++; ?>
          <?php endforeach; ?>
        <?php else : ?>
          <tr>
            <td colspan="10" class="text-center">
              <h4>ไม่พบรายการ</h4>
            </td>
          </tr>

        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>