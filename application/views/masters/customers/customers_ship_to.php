<div class="row">
  <div class="col-lg-7 col-md-6 col-sm-6 col-xs-12 padding-5 table-responsive">
    <table class="table table-striped table-medium border-1" style="min-width:600px;">
      <thead>
        <tr>
          <th class="fix-width-100"></th>
          <th class="fix-width-100">ชื่อเรียก</th>
          <th class="fix-width-100">ชื่อผู้รับ</th>
          <th class="min-width-300">ที่อยู่</th>          
        </tr>
      </thead>
      <tbody id="adrs">
        <?php if (!empty($addr)) : ?>
          <?php foreach ($addr as $rs) : ?>
            <tr id="<?php echo $rs->id; ?>">
              <td>
                <button type="button" 
                  class="btn btn-minier btn-address <?php echo $rs->is_default == 1 ? 'btn-success' : ''; ?>" 
                  id="btn-<?php echo $rs->id; ?>" data-default="<?php echo $rs->is_default; ?>"
                  onclick="setDefault('<?php echo $ds->code; ?>', '<?php echo $rs->id; ?>')">
                  <i class="fa fa-check"></i>
                </button>
                <button type="button" class="btn btn-minier btn-warning" onClick="getShipTo(<?php echo $rs->id; ?>)"><i class="fa fa-pencil"></i></button>
                <button type="button" class="btn btn-minier btn-danger" onClick="deleteShipTo(<?php echo $rs->id; ?>)"><i class="fa fa-trash"></i></button>
              </td>
              <td><?php echo $rs->alias; ?></td>
              <td><?php echo $rs->name; ?></td>
              <td><?php echo parse_address($rs); ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else : ?>
          <tr>
            <td colspan="6" class="text-center">ไม่พบที่อยู่</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <div class="col-lg-5 col-md-6 col-sm-6 col-xs-12">
    <div class="form-horizontal margin-top-30">
      <div class="form-group margin-top-30">
        <label class="col-lg-3 col-md-3 col-sm-3 control-label no-padding-right">ชื่อเรียก</label>
        <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12 padding-5">
          <input type="text" class="form-control input-sm r" id="s-alias" placeholder="ชื่อเรียก เช่น บ้าน ที่ทำงาน (จำเป็น)" />
        </div>
      </div>
      <div class="form-group">
        <label class="col-lg-3 col-md-3 col-sm-3 control-label no-padding-right">ชื่อผู้รับ</label>
        <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12 padding-5">
          <input type="text" class="form-control input-sm r" id="s-name" placeholder="ชื่อผู้รับ" />
        </div>
      </div>
      <div class="form-group">
        <label class="col-lg-3 col-md-3 col-sm-3 control-label no-padding-right">ที่อยู่</label>
        <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12 padding-5">
          <input type="text" class="form-control input-sm r" id="s-address" placeholder="เลขที่/หมู่/ซอย/ถนน" />
        </div>
      </div>
      <div class="form-group">
        <label class="col-lg-3 col-md-3 col-sm-3 control-label no-padding-right">ตำบล/แขวง</label>
        <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12 padding-5">
          <input type="text" class="form-control input-sm r" id="s-sub-district" placeholder="ตำบล/แขวง" />
        </div>
      </div>
      <div class="form-group">
        <label class="col-lg-3 col-md-3 col-sm-3 control-label no-padding-right">อำเภอ/เขต</label>
        <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12 padding-5">
          <input type="text" class="form-control input-sm r" id="s-district" placeholder="อำเภอ/เขต" />
        </div>
      </div>
      <div class="form-group">
        <label class="col-lg-3 col-md-3 col-sm-3 control-label no-padding-right">จังหวัด</label>
        <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12 padding-5">
          <input type="text" class="form-control input-sm r" id="s-province" placeholder="จังหวัด" />
        </div>
      </div>
      <div class="form-group">
        <label class="col-lg-3 col-md-3 col-sm-3 control-label no-padding-right">รหัสไปรษณีย์</label>
        <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12 padding-5">
          <input type="text" class="form-control input-sm r" id="s-postcode" placeholder="รหัสไปรษณีย์" />
        </div>
      </div>
      <div class="form-group">
        <label class="col-lg-3 col-md-3 col-sm-3 control-label no-padding-right">โทรศัพท์</label>
        <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12 padding-5">
          <input type="text" class="form-control input-sm r" id="s-phone" placeholder="โทรศัพท์" />
        </div>
      </div>
      <div class="form-group">
        <label class="col-lg-3 col-md-3 col-sm-3 control-label no-padding-right">อีเมล์</label>
        <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12 padding-5">
          <input type="text" class="form-control input-sm r" id="s-email" placeholder="อีเมล์" />
        </div>
      </div>
      <div class="form-group">
        <label class="col-lg-3 col-md-3 col-sm-3 control-label no-padding-right">&nbsp;</label>
        <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12 padding-5">
          <label>
            <input type="checkbox" id="s-default" class="ace" value="1" />
            <span class="lbl"> ตั้งเป็นที่อยู่เริ่มต้น</span>
          </label>
        </div>
      </div>
      <input type="hidden" id="s-id" value=""/><!-- address id -->
      <div class="divider-hidden"></div>      
      <div class="form-group">
        <div class="col-lg-8 col-lg-offset-3 col-md-8 col-md-offset-3 col-sm-8 col-sm-offset-3 col-xs-12 padding-5">
          <button type="button" class="btn btn-white btn-default" onclick="clearShipTo()">Clear</button>
          <button type="button" class="btn btn-white btn-success btn-100 pull-right" onclick="saveShipTo()">Save</button>
        </div>
      </div>
    </div>
  </div>
</div>
