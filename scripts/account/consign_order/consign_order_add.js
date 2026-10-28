function saveConsign() {
  let code = $('#consign_code').val();

  if ($('.rox').length == 0) {
    swal("ไม่พบรายการสินค้า");
    return false;
  }

  if (!validateQty()) {
    swal("พบรายการที่เป็น 0");
    return false;
  }

  swal({
    title: "บันทึกขายและตัดสต็อก",
    text: "เมื่อบันทึกแล้วจะไม่สามารถแก้ไขได้ ต้องการบันทึกหรือไม่ ?",
    type: "warning",
    showCancelButton: true,
    confirmButtonColor: "#8CC152",
    confirmButtonText: 'บันทึก',
    cancelButtonText: 'ยกเลิก',
    closeOnConfirm: true
  }, function () {
    load_in();

    $.ajax({
      url: `${HOME}save_consign/${code}`,
      type: 'POST',
      cache: false,
      success: function (rs) {
        load_out();

        if (rs.trim() == 'success') {
          setTimeout(() => {
            swal({
              title: 'Saved',
              type: 'success',
              timer: 1000
            });

            setTimeout(() => { viewDetail(code); }, 1200);
          }, 100);
        }
        else {
          setTimeout(() => { showError(rs); }, 100);
        }
      }
    });
  });
}

function validateQty() {
  $('.rox').removeClass('has-error');
  let hasZero = 0;

  $('.qty').each(function () {
    let qty = parseDefaultInt(removeCommas($(this).text()), 0);

    if (qty <= 0) {
      let id = $(this).attr('id');
      let arr = id.split('-');
      if (arr.length == 2) {
        id = arr[1];
        $('#row-' + id).addClass('has-error');
        hasZero++;
      }
    }
  });

  return hasZero === 0;
}

function unSaveConsign() {
  let code = $('#consign_code').val();
  let msg = '<center><span style="color:red;">ก่อนยืนยันการทำรายการนี้</span></center>';
  msg += '<center><span style="color:red;">คุณต้องแน่ใจว่าได้ลบ เอกสารใน SAP แล้ว</span></center>';
  msg += '<center><span style="color:red;">ต้องการยกเลิกการเปิดบิลหรือไม่</span></center>';

  swal({
    title: "ยกเลิกการเปิดบิล ?",
    text: msg,
    type: "warning",
    html: true,
    showCancelButton: true,
    confirmButtonColor: "#FA5858",
    confirmButtonText: 'ใช่, ฉันต้องการ',
    cancelButtonText: 'ยกเลิก',
    closeOnConfirm: true
  }, function () {
    load_in();

    $.ajax({
      url: `${HOME}unsave_consign/${code}`,
      type: 'POST',
      cache: false,
      success: function (rs) {
        load_out();

        if (rs.trim() == 'success') {
          setTimeout(() => {
            swal({
              title: 'Success',
              type: 'success',
              timer: 1000
            });

            setTimeout(() => { goEdit(code); }, 1200);
          }, 200);

        }
        else {
          setTimeout(() => { showError(rs); }, 100);
        }
      }
    });
  });
}

$('#date').datepicker({
  dateFormat: 'dd-mm-yy'
});

$("#customerCode").autocomplete({
  source: `${BASE_URL}auto_complete/get_customer_code_and_name`,
  autoFocus: true,
  close: function () {
    let arr = $(this).val().trim().split(' | ');

    if (arr.length == 2) {
      $("#customerCode").val(arr[0]);
      $("#customer").val(arr[1]);
      zoneInit(arr[0], true);
    }
    else {
      $("#customerCode").val('');
      $("#customer").val('');
      zoneInit('');
    }
  }
});

$("#customer").autocomplete({
  source: `${BASE_URL}auto_complete/get_customer_code_and_name`,
  autoFocus: true,
  close: function () {

    let arr = $(this).val().trim().split(' | ');
    if (arr.length == 2) {
      $("#customerCode").val(arr[0]);
      $("#customer").val(arr[1]);
      zoneInit(arr[0], true);
    } else {
      $("#customerCode").val('');
      $("#customer").val('');
      zoneInit('');
    }
  }
});

window.addEventListener('load', function () {
  let customer_code = $('#customerCode').val();
  zoneInit(customer_code, false);
});

function zoneInit(customer_code, edit) {
  if (edit) {
    $('#zone_code').val('');
    $('#zone').val('');
  }

  $('#zone').autocomplete({
    source: `${BASE_URL}auto_complete/get_consign_zone/${customer_code}`,
    autoFocus: true,
    close: function () {
      let arr = $(this).val().trim().split(' | ');

      if (arr.length == 2) {
        $('#zone_code').val(arr[0]);
        $('#zone').val(arr[1]);
      }
      else {
        $('#zone_code').val('');
        $('#zone').val('');
      }
    }
  });

  $('#zone_code').autocomplete({
    source: `${BASE_URL}auto_complete/get_consign_zone/${customer_code}`,
    autoFocus: true,
    close: function () {
      let arr = $(this).val().trim().split(' | ');

      if (arr.length == 2) {
        $('#zone_code').val(arr[0]);
        $('#zone').val(arr[1]);
      }
      else {
        $('#zone_code').val('');
        $('#zone').val('');
      }
    }
  });
}

function add() {
  clearErrorByClass('r');

  let h = {
    'customer_code': $('#customerCode').val().trim(),
    'customer_name': $('#customer').val().trim(),
    'date_add': $('#date').val(),
    'zone_code': $('#zone_code').val().trim(),
    'zone_name': $('#zone').val().trim(),
    'remark': $('#remark').val().trim()
  };

  if (!isDate(h.date_add)) {
    $('#date').hasError();
    return false;
  }

  if (h.customer_code.length == 0 || h.customer_name.length == 0) {
    $('#customerCode').hasError();
    $('#customer').hasError();
    return false;
  }

  if (h.zone_code.length == 0 || h.zone_name.length == 0) {
    $('#zone_code').hasError();
    $('#zone').hasError();
    return false;
  }

  load_in();

  $.ajax({
    url: `${HOME}add`,
    type: 'POST',
    cache: false,
    data: {
      'data': JSON.stringify(h)
    },
    success: function (rs) {
      load_out();

      if (isJson(rs)) {
        let ds = JSON.parse(rs);

        if (ds.status === 'success' && ds.code) {
          edit(ds.code);
        }
        else {
          showError(ds.message);
        }
      }
      else {
        showError(rs);
      }
    },
    error: function (rs) {
      showError(rs);
    }
  });
}

function getEdit() {
  $('.edit').removeAttr('disabled');
  $('#btn-edit').addClass('hide');
  $('#btn-update').removeClass('hide');
}

function update() {
  clearErrorByClass('edit');

  let h = {
    'code': $('#consign_code').val(),
    'date_add': $('#date').val(),
    'remark': $('#remark').val().trim(),
    'customer_code': $('#customerCode').val().trim(),
    'customer_name': $('#customer').val().trim(),
    'zone_code': $('#zone_code').val().trim(),
    'zone_name': $('#zone').val().trim()
  };

  if (!isDate(h.date_add)) {
    $('#date').hasError();
    return false;
  }

  if (h.customer_code.length == 0 || h.customer_name.length == 0) {
    $('#customerCode').hasError();
    $('#customer').hasError();
    return false;
  }

  if (h.zone_code.length == 0 || h.zone_name.length == 0) {
    $('#zone_code').hasError();
    $('#zone').hasError();
    return false;
  }

  load_in();

  $.ajax({
    url: `${HOME}update`,
    type: 'POST',
    cache: false,
    data: {
      'data': JSON.stringify(h)
    },
    success: function (rs) {
      load_out();
      if (rs.trim() === 'success') {
        swal({
          title: 'Updated',
          type: 'success',
          timer: 1000
        });

        $('.edit').attr('disabled', 'disabled');
        $('#btn-edit').removeClass('hide');
        $('#btn-update').addClass('hide');
      }
      else {
        showError(rs);
      }
    },
    error: function (rs) {
      showError(rs);
    }
  });
}

function deleteRow(id, code) {
  swal({
    title: "คุณแน่ใจ ?",
    text: "ต้องการลบ '" + code + "' หรือไม่ ?",
    type: "warning",
    showCancelButton: true,
    confirmButtonColor: "#FA5858",
    confirmButtonText: 'ใช่, ฉันต้องการลบ',
    cancelButtonText: 'ยกเลิก',
    closeOnConfirm: false
  }, function () {
    deleteDetail(id);
  });
}

function deleteDetail(id) {
  $.ajax({
    url: `${HOME}delete_detail/${id}`,
    type: 'POST',
    cache: false,
    success: function (rs) {
      if (rs.trim() === 'success') {
        swal({
          title: 'Deleted',
          type: 'success',
          timer: 1000
        });

        $('#row-' + id).remove();
        reIndex();
        updateTotalQty();
        updateTotalAmount();
      }
    }
  });
}

function toggleCheckAll() {
  let isChecked = $('#check-all').prop('checked');
  $('.chk').prop('checked', isChecked);
}

function removeChecked() {
  if ($('.chk:checked').length > 0) {
    let ids = [];

    $('.chk:checked').each(function () {
      ids.push($(this).data('id'));
    });

    swal({
      title: "คุณแน่ใจ ?",
      text: `ต้องการลบรายการที่เลือก (${ids.length} รายการ) หรือไม่ ?`,
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#FA5858",
      confirmButtonText: 'ใช่, ฉันต้องการลบ',
      cancelButtonText: 'ยกเลิก',
      closeOnConfirm: false
    }, function () {
      load_in();
      setTimeout(() => {
        let code = $('#consign_code').val();

        $.ajax({
          url: `${HOME}remove_checked`,
          type: 'POST',
          cache: false,
          data: {
            'code': code,
            'ids': ids
          },
          success: function (rs) {
            load_out();

            if (rs == 'success') {
              swal({
                title: 'Deleted',
                type: 'success',
                timer: 1000
              });

              ids.forEach(id => $(`#row-${id}`).remove());
              reIndex();
              updateTotalQty();
              updateTotalAmount();
            }
            else {
              showError(rs);
            }
          },
          error: function (rs) {
            showError(rs);
          }
        })
      }, 100);
    });
  }
}

//--- ลบรายการนำเข้ายอดต่าง
function clearImportDetail(check_code) {
  swal({
    title: "คุณแน่ใจ ?",
    text: `ต้องการลบรายการนำเข้าจาก '${check_code}' หรือไม่ ?`,
    type: "warning",
    showCancelButton: true,
    confirmButtonColor: "#FA5858",
    confirmButtonText: 'ใช่, ฉันต้องการลบ',
    cancelButtonText: 'ยกเลิก',
    closeOnConfirm: false
  }, function () {
    load_in();
    let code = $('#consign_code').val();

    $.ajax({
      url: `${HOME}remove_import_details/${code}`,
      type: 'POST',
      cache: false,
      data: {
        'check_code': check_code
      },
      success: function (rs) {
        load_out();

        if (rs.trim() === 'success') {
          swal({
            title: 'Success',
            type: 'success',
            timer: 1000
          });

          setTimeout(() => {
            window.location.reload();
          }, 1500);

        }
        else {
          showError(rs);
        }
      },
      error: function (rs) {
        showError(rs);
      }
    });
  });
}

function getActiveCheckList() {
  let zone_code = $('#zone_code').val();
  load_in();

  $.ajax({
    url: `${HOME}get_active_check_list/${zone_code}`,
    type: 'GET',
    cache: false,
    success: function (rs) {
      load_out();
      if (isJson(rs)) {
        var source = $('#check-list-template').html();
        var data = $.parseJSON(rs);
        var output = $('#check-list-body');
        render(source, data, output);
        $('#check-list-modal').modal('show');
      }
      else {
        showError(rs);
      }
    },
    error: function (rs) {
      showError(rs);
    }
  });
}

function loadCheckDiff(check_code) {
  $('#check-list-modal').modal('hide');
  swal({
    title: "นำเข้ายอดต่าง",
    text: "ต้องการนำเข้ายอดต่างจากเอกสารกระทบยอด " + check_code + " หรือไม่ ?",
    type: "warning",
    showCancelButton: true,
    confirmButtonText: 'ใช่, ฉันต้องการ',
    cancelButtonText: 'ยกเลิก',
    closeOnConfirm: false
  }, function () {
    let code = $('#consign_code').val();
    load_in();
    $.ajax({
      url: `${HOME}load_check_diff/${code}`,
      type: 'POST',
      cache: false,
      data: {
        'check_code': check_code
      },
      success: function (rs) {
        load_out();
        var rs = $.trim(rs);
        if (rs == 'success') {
          swal({
            title: 'Success',
            type: 'success',
            timer: 1000
          });

          setTimeout(function () {
            window.location.reload();
          }, 1500);
        } else {
          swal('Error!', rs, 'error');
        }
      },
      error: function (rs) {
        showError(rs);
      }
    });
  });//--- swal
}

function getTemplate() {
  window.location.href = `${HOME}get_template_file`;
}

function getUploadFile() {
  $('#uploadFile').click();
}

const uploadFile = document.getElementById('uploadFile');

uploadFile.addEventListener('change', async function () {
  if (this.files.length > 0) {
    const file = this.files[0];
    if (file.size > 5000000) {
      swal("File size exceeds 5MB", "Please select a smaller file.", "error");
      this.value = '';
      return;
    }

    await uploadfile(file);
  }
});

async function uploadfile(file) {
  const code = $('#consign_code').val();
  const fd = new FormData();
  const url = `${HOME}import_excel_file`;
  fd.append('code', code);
  fd.append('excel', file);
  load_in();

  try {
    const response = await fetch(url, {
      method: 'POST',
      body: fd
    });

    const result = await response.text();

    load_out();

    if (result.trim() === 'success') {
      swal({
        title: 'Success',
        type: 'success',
        timer: 1000
      });

      setTimeout(function () {
        window.location.reload();
      }, 1200);

    }
    else {
      showError(result);
    }
  }
  catch (error) {
    load_out();
    showError("An error occurred while importing the sales order.");
    console.error('Error importing sales order:', error);
  }
}

function validateOrder() {
  var prefix = $('#prefix').val();
  var runNo = parseInt($('#runNo').val());
  let code = $('#code').val();
  if (code.length == 0) {
    add();
    return false;
  }

  let arr = code.split('-');

  if (arr.length == 2) {
    if (arr[0] !== prefix) {
      swal('Prefix ต้องเป็น ' + prefix);
      return false;
    } 
    else if (arr[1].length != (4 + runNo)) {
      swal('Run Number ไม่ถูกต้อง');
      return false;
    } 
    else {
      $.ajax({
        url: HOME + 'is_exists/' + code,
        type: 'GET',
        cache: false,
        success: function (rs) {
          if (rs == 'not_exists') {
            add();
          } 
          else {
            swal({
              title: 'Error!!',
              text: rs,
              type: 'error'
            });
          }
        }
      })
    }

  } 
  else {
    swal('เลขที่เอกสารไม่ถูกต้อง');
    return false;
  }
}
