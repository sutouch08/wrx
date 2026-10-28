<?php $this->load->view('include/header'); ?>
<div class="row">
  <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 padding-5 padding-top-5">
    <h4 class="title">รอดำเนินการ <?php echo $count; ?> ออเดอร์ จากทั้งหมด <?php echo number($all); ?></h4>
  </div>
  <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 padding-5 text-right">
    <button type="button" class="btn btn-white btn-info top-btn" onclick="refresh()"><i class="fa fa-refresh"></i> Refresh</button>
    <?php if ($this->pm->can_edit) : ?>
      <button type="button" class="btn btn-white btn-primary top-btn" onclick="getUploadFile()"><i class="fa fa-upload"></i> &nbsp; Import Order</button>
      <button type="button" class="btn btn-white btn-warning top-btn" onclick="clearAllData()"><i class="fa fa-times"></i> &nbsp; Clear All Data</button>
    <?php endif; ?>
  </div>
</div>
<hr />
<div class="row">
  <form id="searchForm" method="post" action="<?php echo $this->home; ?>">
    <div class="col-lg-2-harf col-md-2-harf col-sm-3-harf col-xs-4 padding-5">
      <div class="input-group">
        <span class="input-group-addon">รหัส</span>
        <input type="text" class="form-control input-sm" id="code" name="code" placeholder="Order Code" value="<?php echo $code; ?>" />
      </div>
    </div>
    <div class="col-lg-2 col-md-2-harf col-sm-3-harf col-xs-4 padding-5">
      <div class="input-group">
        <span class="input-group-addon">สถานะ</span>
        <select class="form-control input-sm" id="status" name="status">
          <option value="0" <?php echo is_selected($status, '0'); ?>>Pending</option>
          <option value="1" <?php echo is_selected($status, '1'); ?>>Success</option>
          <option value="3" <?php echo is_selected($status, '3'); ?>>Error</option>
          <option value="all" <?php echo is_selected($status, 'all'); ?>>All</option>
        </select>
      </div>
    </div>
    <div class="col-lg-1 col-md-1-harf col-sm-1-harf col-xs-3 padding-5">
      <button type="submit" class="btn btn-xs btn-primary btn-block" style="height:30px;">Search</button>
    </div>
    <div class="col-lg-1 col-md-1-harf col-sm-1-harf col-xs-3 padding-5">
      <button type="button" class="btn btn-xs btn-warning btn-block" onclick="clearOption()" style="height:30px;">Clear</button>
    </div>
  </form>
</div>
<hr />
<div class="row">
  <div class="col-lg-6-harf col-md-5-harf col-sm-3 col-xs-12">&nbsp;</div>
  <div class="col-lg-2 col-md-2-harf col-sm-4 col-xs-6 padding-5">
    <div class="input-group">
      <span class="input-group-addon">จำนวน</span>
      <input type="number" class="form-control input-sm text-center" id="order-limit" onchange="changeOrderLimit()" value="<?php echo $limit; ?>" />
    </div>
  </div>

  <div class="col-lg-2-harf col-md-2-harf col-sm-3-harf col-xs-9 padding-5">
    <div class="input-group">
      <span class="input-group-addon">State</span>
      <select class="form-control input-sm" id="state">
        <option value="">Select State</option>
        <option value="1">รอดำเนินการ</option>
        <option value="3">รอจัดสินค้า</option>
        <option value="7">รอเปิดบิล</option>
        <option value="9">ยกเลิก</option>
      </select>
    </div>
  </div>
  <div class="col-lg-1 col-md-1-harf col-sm-1-harf col-xs-3 padding-5">
    <button type="button" class="btn btn-xs btn-success btn-block" style="height:30px;" onclick="startExport()">GO</button>
  </div>
</div>
<hr />

<?php

$stateName = array(
  '1' => 'รอดำเนินการ',
  '2' => 'รอชำระเงิน',
  '3' => 'รอจัดสินค้า',
  '4' => 'กำลังจัด',
  '5' => 'รอตรวจ',
  '6' => 'กำลังตรวจ',
  '7' => 'รอเปิดบิล',
  '8' => 'เปิดบิลแล้ว',
  '9' => 'ยกเลิก'
);

?>
<div class="row" id="result">
  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 padding-5 table-responsive">
    <table class="table table-striped table-medium border-1" style="min-width:500px;">
      <thead>
        <tr>
          <th class="fix-width-40 text-center">#</th>
          <th class="fix-width-150">Order</th>
          <th class="fix-width-100">State</th>
          <th class="fix-width-100">Status</th>
          <th class="min-width-100">message</th>
        </tr>
      </thead>
      <tbody>
        <?php if (! empty($data)) : ?>
          <?php $no = 1; ?>
          <?php foreach ($data as $rs) : ?>
            <tr id="row-<?php echo $rs->id; ?>">
              <td class="text-center"><?php echo $no; ?></td>
              <td>
                <?php echo $rs->code; ?>
                <input type="hidden" class="order" data-id="<?php echo $rs->id; ?>" data-no="<?php echo $no; ?>" id="code-<?php echo $rs->id; ?>" value="<?php echo $rs->code; ?>" />
              </td>
              <td id="state-<?php echo $rs->id; ?>"><?php echo empty($stateName[$rs->state]) ? "Unknow" : $stateName[$rs->state]; ?></td>
              <td id="status-<?php echo $rs->id; ?>"><?php echo $rs->status == 0 ? "Pending" : ($rs->status == 1 ? "Success" : "Error"); ?></td>
              <td id="msg-<?php echo $rs->id; ?>"><?php echo $rs->message; ?></td>
            </tr>
            <?php $no++; ?>
          <?php endforeach; ?>
        <?php else : ?>
          <tr>
            <td colspan="5" class="text-center">---- No Order ----</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
    <input type="hidden" id="count" value="<?php echo $count; ?>" />
  </div>
</div>

<input type="file" class="hide" name="uploadFile" id="uploadFile" accept=".xlsx" />

<script>
  let finished = false;
  let max = 0;
  let orders = [];
  let state = 7;

  function startExport() {
    let stateSelected = $('#state').val();

    if (stateSelected == "") {
      swal("กรุณาเลือกสถานะ");
      return false;
    }

    state = stateSelected;
    max = parseDefault(parseInt($('#count').val()), 0);
    load_in();

    $('.order').each(function() {
      let code = $(this).val();
      let id = $(this).data('id');
      orders.push({
        'code': code,
        'id': id
      });
    });

    if (orders.length > 0 && max > 0) {
      do_export(0);
    }
  }

  function do_export(no) {
    let order = orders[no];
    let code = order.code;
    let id = order.id;

    if (finished == false) {
      if (code != null && code != "" && code != undefined) {
        $.ajax({
          url: `${BASE_URL}orders/orders/order_state_change`,
          type: 'POST',
          cache: false,
          data: {
            'order_code': code,
            'state': state
          },
          success: function(rs) {

            if (rs == 'success') {
              $(`#status-${id}`).text('Success');
              $(`#row-${id}`).addClass('text-success');
              no++;
              if (no == max) {
                update_status(code, 1, rs);
                finished = true;
                load_out();
              } else {
                update_status(code, 1, rs);

                do_export(no);
              }
            } else {
              $(`#status-${id}`).text('Error');
              $(`#msg-${id}`).text(rs);
              $(`#row-${id}`).addClass('text-error');
              no++;
              if (no == max) {
                update_status(code, 3, rs);
                finished = true;
                load_out();
              } else {
                update_status(code, 3, rs);
                do_export(no);
              }
            }
          }
        })
      }
    }
  }

  function update_status(code, status, message) {
    $.ajax({
      url: `${BASE_URL}auto/auto_change_state/update_status`,
      type: 'POST',
      cache: false,
      data: {
        'code': code,
        'status': status,
        'message': message
      },
      success: function(rs) {
        console.log(rs);
      },
      error: function(rs) {
        console.error(rs);
      }
    });
  }

  function getUploadFile() {
    $('#uploadFile').click();
  }

  const uploadFile = document.getElementById('uploadFile');

  uploadFile.addEventListener('change', async function() {
    if (this.files && this.files[0]) {
      var file = this.files[0];
      var size = file.size;

      if (size > 5000000) {
        swal("ขนาดไฟล์ใหญ่เกินไป", "ไฟล์แนบต้องมีขนาดไม่เกิน 5 MB", "error");
        this.value = '';
        return false;
      }

      await uploadfile(file);
    }
  });

  async function uploadfile(file) {
    const url = `${HOME}/import_order`;
    const fd = new FormData();
    fd.append('uploadFile', file);

    load_in();

    const response = await fetch(url, {
      method: 'POST',
      body: fd
    });

    load_out();

    const result = await response.text();

    if (result.trim() === 'success') {
      swal({
        title: 'นำเข้าเรียบร้อยแล้ว',
        type: 'success',
        html: true,
        timer: 1000
      });

      setTimeout(() => {
        refresh();
      }, 1200);
    } else {
      showError(result);
    }
  }

  function clearAllData() {
    swal({
      title: 'Clear Data',
      text: 'Clear All Data, Do you want to process this operation ?',
      type: 'warning',
      html: true,
      showCancelButton: true,
      cancelButtonText: 'No',
      confirmButtonText: 'Yes',
      closeOnConfirm: true
    }, function() {
      load_in();

      setTimeout(() => {
        $.ajax({
          url: `${HOME}clear_data`,
          type: 'GET',
          cache: false,
          success: function(rs) {
            load_out();

            if (rs.trim() === 'success') {
              swal({
                title: 'success',
                type: 'success',
                timer: 1000,
              });

              setTimeout(() => { refresh(); }, 1200);
            } 
            else {
              showError(rs);
            }
          },
          error: function(rs) {
            showError(rs);
          }
        });
      }, 100);
    })
  }

  function changeOrderLimit() {
    let limit = parseDefault(parseInt($('#order-limit').val()), 0);

    if (limit <= 0) {
      swal("จำนวนต้องมากกว่า 0");
      return false;
    }

    $.ajax({
      url: `${HOME}change_order_limit`,
      type: 'POST',
      cache: false,
      data: {
        'limit': limit
      },
      success: function(rs) {
        if (rs.trim() === 'success') {
          refresh();
        } 
        else {
          showError(rs);
        }
      },
      error: function(rs) {
        showError(rs);
      }
    })
  }

  function clearOption() {
    $('#code').val('');
    $('#status').val('0');
    $('#searchForm').submit();
  }
</script>


<?php $this->load->view('include/footer'); ?>