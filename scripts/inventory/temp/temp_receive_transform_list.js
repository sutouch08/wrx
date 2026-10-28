$("#fromDate").datepicker({
  dateFormat: 'dd-mm-yy',
  onClose: function (sd) {
    $("#toDate").datepicker("option", "minDate", sd);
  }
});

$("#toDate").datepicker({
  dateFormat: 'dd-mm-yy',
  onClose: function (sd) {
    $("#fromDate").datepicker("option", "maxDate", sd);
  }
});

function get_detail(code) {
  const target = `${HOME}get_detail/${code}?nomenu`;
  const width = 1100;
  const height = 900;
  const left = ($(document).width() - width) / 2;
  window.open(target, "_blank", `width=${width}, height=${height}, left=${left}, scrollbars=yes`);
}

function deleteTemp(docEntry, code, row_no) {
  swal({
    title: 'คุณแน่ใจ ?',
    text: `ต้องการลบ ${code} หรือไม่ ?`,
    type: 'warning',
    showCancelButton: true,
    comfirmButtonColor: '#DD6855',
    confirmButtonText: 'ใช่ ฉันต้องการ',
    cancelButtonText: 'ไม่ใช่',
    closeOnConfirm: false
  }, function () {
    $.ajax({
      url: `${HOME}delete_temp/${docEntry}`,
      type: 'POST',
      cache: false,
      success: function (rs) {
        if (rs.trim() === 'success') {
          $(`#row-${row_no}`).remove();
          reIndex();

          swal({
            title: 'Success',
            text: `${code} has been deleted`,
            type: 'success',
            timer: 1000
          });
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