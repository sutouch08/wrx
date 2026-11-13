function addNew() {
  window.location.href = `${HOME}add_new`;
}

function edit(code) {
  window.location.href = `${HOME}edit/${code}`;
}

function confirmDelete(id, name) {
  swal({
    title: 'Are sure ?',
    text: `ต้องการลบ ${name} หรือไม่ ?`,
    type: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#FA5858',
    confirmButtonText: 'ใช่, ฉันต้องการลบ',
    cancelButtonText: 'ยกเลิก',
    closeOnConfirm: true
  }, function () {
    load_in();
    setTimeout(() => {
      $.ajax({
        url: `${HOME}delete`,
        type: 'POST',
        cache: false,
        data: {
          'id': id
        },
        success: function (rs) {
          load_out();

          if (rs === 'success') {
            swal({
              title: 'Success',
              type: 'success',
              timer: 1000
            });

            $(`#row-${id}`).remove();
            reIndex();
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
  })
}


function toggleActive(option, code) {
  $.ajax({
    url: `${HOME}set_active`,
    type: 'POST',
    cache: 'false',
    data: {
      'code': code,
      'active': option
    },
    success: function (rs) {
      if (rs.trim() != '') {
        $(`#${code}`).html(rs);
      }
    }
  });
}

let click = 0;

function add() {
  if (click > 0) {
    return false;
  }

  click = 1;
  clearErrorByClass('r');

  let code = $('#code').val().trim();
  let name = $('#name').val().trim();
  let group = $('#color_group').val();

  if (code == '') {
    $('#code').hasError();
    click = 0;
    return false;
  }

  if (name == '') {
    $('#name').hasError();
    click = 0;
    return false;
  }

  if (group == '') {
    $('#color_group').hasError();
    click = 0;
    return false;
  }

  load_in();

  $.ajax({
    url: `${HOME}add`,
    type: 'POST',
    cache: false,
    data: {
      'code': code,
      'name': name,
      'group': group
    },
    success: function (rs) {
      load_out();
      click = 0;

      if (rs.trim() === 'success') {
        swal({
          title: 'Success',
          type: 'success',
          timer: 1000
        });

        setTimeout(() => { addNew(); }, 1200);
      }
      else {
        showError(rs);
        click = 0;
      }
    },
    error: function (rs) {
      showError(rs);
      click = 0;
    }
  });
}


function update() {
  if (click > 0) {
    return false;
  }

  click = 1;
  clearErrorByClass('r');
  let id = $('#id').val();
  let code = $('#code').val().trim();
  let name = $('#name').val().trim() ;
  let group = $('#color_group').val();

  if (code == '') {
    $('#code').hasError();
    click = 0;
    return false;
  }

  if (name == '') {
    $('#name').hasError();
    click = 0;
    return false;
  }

  if (group == "") {
    $('#color_group').hasError();
    click = 0;
    return false;
  }

  load_in();

  $.ajax({
    url: `${HOME}update`,
    type: 'POST',
    cache: false,
    data: {
      'id': id,
      'code': code,
      'name': name,
      'group': group
    },
    success: function (rs) {
      load_out();
      click = 0;

      if (rs.trim() == 'success') {
        swal({
          title: 'Success',
          type: 'success',
          timer: 1000
        });
      }
      else {
        click = 0;
        showError(rs);
      }
    },
    error: function (rs) {
      showError(rs);
      click = 0;
    }
  });
}
