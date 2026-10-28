function addNew() {
  window.location.href = `${HOME}add_new`;
}

function edit(code) {
  window.location.href = `${HOME}edit/${code}`;
}

function viewDetail(code) {
  window.location.href = `${HOME}view_detail/${code}`;
}

function getDelete(code) {
  swal({
    title: "คุณแน่ใจ ?",
    text: `ต้องการยกเลิก '${code}' หรือไม่ ?`,
    type: "warning",
    showCancelButton: true,
    confirmButtonColor: "#DD6B55",
    confirmButtonText: 'ใช่, ฉันต้องการ',
    cancelButtonText: 'ไม่ใช่',
    closeOnConfirm: true
  }, function () {
    $('#cancle-code').val(code);
    $('#cancle-reason').val('').removeClass('has-error');

    setTimeout(function () { cancle(code); }, 100);
  });
}

function cancle(code) {
  let reason = $('#cancle-reason').val().trim();

  if (reason.length < 10) {
    $('#cancle-modal').modal('show');
    return false;
  }

  load_in();

  $.ajax({
    url: `${HOME}cancle/${code}`,
    type: "POST",
    cache: false,
    data: {
      "reason": reason
    },
    success: function (rs) {
      load_out();

      if (rs.trim() == 'success') {
        swal({
          title: 'Cancled',
          type: 'success',
          timer: 1000
        });

        setTimeout(function () { refresh(); }, 1200);
      }
      else {
        showError(rs);
      }
    },
    error: function(rs) {
      showError(rs);
    }
  });
}

function doCancle() {
  let code = $('#cancle-code').val();
  let reason = $('#cancle-reason').val().trim();

  if (reason.length < 10) {
    $('#cancle-reason').addClass('has-error').focus();
    return false;
  }

  $('#cancle-modal').modal('hide');
  return cancle(code);
}

$('#cancle-modal').on('shown.bs.modal', function () {
  $('#cancle-reason').focus();
});

function doExport() {
  let code = $('#consign_code').val();  
  load_in();

  $.ajax({
    url: `${HOME}export_consign/${code}`,
    type: 'POST',
    cache: false,
    success: function (rs) {
      load_out();
      
      if (rs.trim() == 'success') {
        swal({
          title: 'success',
          type: 'success',
          timer: 1000
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
}

function sendToSAP(code) {
  load_in();

  $.ajax({
    url: `${HOME}export_consign/${code}`,
    type: 'POST',
    cache: false,
    success: function (rs) {
      load_out();
      
      if (rs.trim() == 'success') {
        swal({
          title: 'success',
          type: 'success',
          timer: 1000
        });
      }
      else {
        showError(rs);
      }
    },
    error: function(rs) {
      showError(rs);
    }
  });
}

$("#fromDate").datepicker({
  dateFormat: 'dd-mm-yy',
  onClose: function (ds) {
    $("#toDate").datepicker("option", "minDate", ds);
  }
});

$("#toDate").datepicker({
  dateFormat: 'dd-mm-yy',
  onClose: function (ds) {
    $("#fromDate").datepicker("option", "maxDate", ds);
  }
});

function printConsignOrder() {
  let code = $('#consign_code').val();
  let width = 800;
  let height = 900;
  let left = ($(document).width() - width) / 2;
  let url = `${HOME}print_consign/${code}`;
  window.open(url, "_blank", `width=${width}, height=${height}, left=${left}, scrollbars=yes`);
}
