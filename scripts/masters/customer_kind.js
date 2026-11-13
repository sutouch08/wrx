function addNew() {
  window.location.href = `${HOME}add_new`;
}

function edit(code) {
  window.location.href = `${HOME}edit/${code}`;
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

  if (code === '') {
    $('#code').hasError();
    click = 0;
    return false;
  }

  if (name === '') {
    $('#name').hasError();
    click = 0;
    return false;
  }

  load_in();

  $.ajax({
    url: `${HOME}add`,
    type: 'POST',
    data: { 'code': code, 'name': name },
    success: function (rs) {
      load_out();

      if (rs.trim() === 'success') {
        swal({
          title: 'Success',
          type: 'success',
          timer: 1000
        });

        setTimeout(() => { refresh(); }, 1200);
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
  })
}


function update() {
  if (click > 0) {
    return false;
  }

  click = 1;
  clearErrorByClass('r');

  let code = $('#code').val().trim();
  let name = $('#name').val().trim();

  if (code === '') {
    $('#code').hasError();
    click = 0;
    return false;
  }

  if (name === '') {
    $('#name').hasError();
    click = 0;
    return false;
  }

  load_in();

  $.ajax({
    url: `${HOME}update`,
    type: 'POST',
    data: { 'code': code, 'name': name },
    success: function (rs) {
      load_out();

      if (rs.trim() === 'success') {
        swal({
          title: 'Success',
          type: 'success',
          timer: 1000
        });

        setTimeout(() => { refresh(); }, 1200);
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
  })
}

function confirmDelete(id, name) {
  swal({
    title: 'Are sure ?',
    text: `ต้องการลบ '${name}' หรือไม่ ?`,
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
        data: { 'id': id },
        success: function (rs) {
          load_out();
          if (rs.trim() === 'success') {
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

