
function edit(code) {
  window.location.href = `${HOME}edit/${code}`;
}

function changeURL(style, tab) {
  let url = `${HOME}edit/${style}/${tab}`;
  let stObj = { stage: 'stage' };
  window.history.pushState(stObj, 'customers', url);
}

function getDelete(code, name) {
  swal({
    title: 'Are sure ?',
    text: 'ต้องการลบ ' + name + ' หรือไม่ ?',
    type: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#FA5858',
    confirmButtonText: 'ใช่, ฉันต้องการลบ',
    cancelButtonText: 'ยกเลิก',
    closeOnConfirm: false
  }, function () {
    $.ajax({
      url: `${HOME}delete`,
      type: 'POST',
      data: {
        'code': code
      },
      success: function (rs) {
        if (rs === 'success') {
          swal({
            title: 'Success',
            text: 'ลบข้อมูลเรียบร้อยแล้ว',
            type: 'success',
            timer: 1000
          });
          
          setTimeout(() => { refresh(); }, 1200);
        }
        else {
          showError(rs);
        }
      },
      error: function (rs) {
        showError(rs);
      }
    });
  })
}


function syncData(type = 'update') {
  load_in();

  $.ajax({
    url: `${HOME}syncData/${type}`,
    type: 'POST',
    cache: false,
    success: function (rs) {
      load_out();

      swal({
        title: 'Success',
        text: 'Sync completed',
        type: 'success'
      }, function () {
        goBack();
      });
    },
    error: function (rs) {
      showError(rs);
    }
  });
}


function saveBillTo() {
  clearErrorByClass('r');
  let code = $('#code').val();
  let h = {
    'branch_code': $('#b-branch-code').val().trim(),
    'branch_name': $('#b-branch-name').val().trim(),
    'address': $('#b-address').val().trim(),
    'sub_district': $('#b-sub-district').val().trim(),
    'district': $('#b-district').val().trim(),
    'province': $('#b-province').val().trim(),
    'postcode': $('#b-postcode').val().trim(),
    'country': $('#b-country').val().trim(),
    'phone': $('#b-phone').val().trim()
  };

  if (h.branch_code === '') {
    $('#b-branch-code').hasError();
    return false;
  }

  if (h.branch_name === '') {
    $('#b-branch-name').hasError();
    return false;
  }

  if (h.address === '') {
    $('#b-address').hasError();
    return false;
  }

  if (h.sub_district === '') {
    $('#b-sub-district').hasError();
    return false;
  }

  if (h.district === '') {
    $('#b-district').hasError();
    return false;
  }

  if (h.province === '') {
    $('#b-province').hasError();
    return false;
  }

  load_in();

  $.ajax({
    url: `${HOME}add_bill_to/${code}`,
    type: 'POST',
    cache: false,
    data: {
      'data': JSON.stringify(h)
    },
    success: function (rs) {
      load_out();

      if (rs === 'success') {
        swal({
          title: 'Success',
          text: 'บันทึกข้อมูลเรียบร้อยแล้ว',
          type: 'success',
          timer: 1000
        });

        setTimeout(() => { refresh(); }, 1200);
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

function saveShipTo() {
  clearErrorByClass('r');
  let code = $('#code').val();
  let h = {
    'id': $('#s-id').val().trim(),
    'alias': $('#s-alias').val().trim(),
    'name': $('#s-name').val().trim(),
    'address': $('#s-address').val().trim(),
    'sub_district': $('#s-sub-district').val().trim(),
    'district': $('#s-district').val().trim(),
    'province': $('#s-province').val().trim(),
    'postcode': $('#s-postcode').val().trim(),
    'country': 'TH',
    'phone': $('#s-phone').val().trim(),
    'email': $('#s-email').val().trim(),
    'default': $('#s-default').is(':checked') ? 1 : 0
  };

  if (h.alias === '') {
    $('#s-alias').hasError();
    return false;
  }

  if (h.name === '') {
    $('#s-name').hasError();
    return false;
  }

  if (h.address === '') {
    $('#s-address').hasError();
    return false;
  }

  load_in();

  $.ajax({
    url: `${HOME}add_ship_to/${code}`,
    type: 'POST',
    cache: false,
    data: {
      'data': JSON.stringify(h)
    },
    success: function (rs) {
      load_out();

      if (rs === 'success') {
        swal({
          title: 'Success',
          text: 'บันทึกข้อมูลเรียบร้อยแล้ว',
          type: 'success',
          timer: 1000
        });

        setTimeout(() => { refresh(); }, 1200);
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

function setDefault(code, id) {
  let is_default = $('#btn-' + id).data('default');

  if (is_default == 1) {
    return false;
  }
  
  $.ajax({
    url: `${HOME}set_default_ship_to/${code}/${id}`,
    type: 'POST',
    cache: false,
    success: function (rs) {
      load_out();
      if (rs === 'success') {
        $('.btn-address').removeClass('btn-success');
        $('.btn-address').data('default', 0);
        $('#btn-' + id).addClass('btn-success');
        $('#btn-' + id).data('default', 1);
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

function getShipTo(id) {
  $.ajax({
    url: `${HOME}get_ship_to/${id}`,
    type: 'GET',
    cache: false,
    success: function (rs) {
      if(isJson(rs)) {
        let ds = JSON.parse(rs);
        $('#s-alias').val(ds.alias);
        $('#s-name').val(ds.name);
        $('#s-address').val(ds.address);
        $('#s-sub-district').val(ds.sub_district);
        $('#s-district').val(ds.district);
        $('#s-province').val(ds.province);
        $('#s-postcode').val(ds.postcode);
        $('#s-phone').val(ds.phone);
        $('#s-email').val(ds.email);
        $('#s-default').prop('checked', ds.is_default == 1);
        $('#s-id').val(ds.id);
      }
      else {
        showError(rs);
      }
    },
    error: function (rs) {
      showError(rs);
    }
  })
}

function deleteShipTo(id) {
  swal({
    title:'Are you sure?',
    text: 'คุณต้องการลบที่อยู่จัดส่งนี้หรือไม่?',
    type: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes',
    cancelButtonText: 'No',
    closeOnConfirm: true
  }, function() {
    $.ajax({
      url: `${HOME}delete_ship_to/${id}`,
      type: 'GET',
      cache: false,
      success: function (rs) {
        if (rs === 'success') {
          swal({
            title: 'Success',
            text: 'ลบที่อยู่จัดส่งเรียบร้อยแล้ว',
            type: 'success',
            timer: 1000
          });

          setTimeout(() => { refresh(); }, 1200);
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

$('#s-sub-district').autocomplete({
  source: `${BASE_URL}auto_complete/sub_district`,
  autoFocus: true,
  open: function () {
    let $ul = $(this).autocomplete('widget');
    $ul.css('width', 'auto');
  },
  close:function(e) {
    let adr = $(this).val().trim().split('>>');
    if (adr.length === 4) {
      $('#s-sub-district').val(adr[0].trim());
      $('#s-district').val(adr[1].trim());
      $('#s-province').val(adr[2].trim());
      $('#s-postcode').val(adr[3].trim());
    }
  }
});

$('#s-district').autocomplete({
  source: `${BASE_URL}auto_complete/district`,
  autoFocus: true,
  open: function () {
    let $ul = $(this).autocomplete('widget');
    $ul.css('width', 'auto');
  },
  close:function(e) {
    let adr = $(this).val().trim().split('>>');
    if (adr.length === 3) {
      $('#s-district').val(adr[0].trim());
      $('#s-province').val(adr[1].trim());
      $('#s-postcode').val(adr[2].trim());
    }
  }
});

$('#s-province').autocomplete({
  source: `${BASE_URL}auto_complete/province`,
  autoFocus: true,
  open: function () {
    let $ul = $(this).autocomplete('widget');
    $ul.css('width', 'auto');
  },
  close:function(e) {
    let adr = $(this).val().trim().split('>>');
    if (adr.length === 2) {
      $('#s-province').val(adr[0].trim());
      $('#s-postcode').val(adr[1].trim());
    }
  }
});

function clearShipTo() {
  $('#s-alias').val('');
  $('#s-name').val('');
  $('#s-address').val('');
  $('#s-sub-district').val('');
  $('#s-district').val('');
  $('#s-province').val('');
  $('#s-postcode').val('');
  $('#s-phone').val('');
  $('#s-email').val('');
  $('#s-default').prop('checked', false);
  $('#s-id').val('');
}

function update() {
  clearErrorByClass('r');

  let h = {
    'code' : $('#code').val(),
    'name' : $('#name').val().trim(),
    'tax_id' : $('#tax-id').val().trim(),
    'DebPayAcct' : $('#DebPayAcct').val(),
    'GroupCode' : $('#GroupCode').val(),
    'cmpPrivate' : $('#cmpPrivate').val(),
    'GroupNum' : $('#GroupNum').val(),
    'group' : $('#group').val(),
    'kind' : $('#kind').val(),
    'type' : $('#type').val(),
    'class' : $('#class').val(),
    'area' : $('#area').val(),
    'sale' : $('#sale').val(),
    'CreditLine' : $('#CreditLine').val(),
    'gp' : $('#gp').val(),
    'skip_overdue' : $('#skip-overdue').is(':checked') ? 1 : 0
  }

  if(h.name === '') {
    $('#name').hasError();
    return false;
  }

  load_in();

  $.ajax({
    url: `${HOME}update`,
    type: 'POST',
    data: {
      "data": JSON.stringify(h),
    },
    success: function(rs) {
      load_out();

      if(rs === 'success') {
        swal({
          title: 'Success',          
          type: 'success',
          timer:1000
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