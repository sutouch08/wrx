function addNew() {
  window.location.href = `${HOME}add_new`;
}

function edit(id) {
  window.location.href = `${HOME}edit/${id}`;
}

function confirmDelete(id, name) {
  swal({
    title: 'Are you sure?',
    text: `Do you want to delete '${name}' ?`,
    type: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#FA5858',
    confirmButtonText: 'Yes, delete it!',
    cancelButtonText: 'Cancel',
    closeOnConfirm: true
  }, function() {
    load_in();
    setTimeout(() => {
      $.ajax({
        url:`${HOME}delete`,
        type: 'POST',
        cache: false,
        data: {
          'id' : id
        },
        success: function(rs) {
          load_out();

          if(rs.trim() === 'success') {
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
        error:function(rs) {
          showError(rs);
        }
      });
    },100);    
  });
}

let click = false;

function add() {
  if(click) return;
  click = true;
  clearErrorByClass('r');
  let code = $('#code').val().trim();
  let name = $('#name').val().trim();
  let active = $('#active').is(':checked') ? 1 : 0;

  if(code === '') {
    $('#code').hasError();
    click = false;
    return;
  }

  if(name === '') {
    $('#name').hasError();
    click = false;
    return;
  }

  load_in();

  $.ajax({
    url:`${HOME}add`,
    type:'POST',
    cache:false,
    data:{
      'code' : code,
      'name' : name,
      'active' : active
    },
    success:function(rs) {
      click = false;
      load_out();
      if(rs.trim() === 'success') {
        swal({
          title: 'Success',
          type: 'success',
          timer: 1000
        });

        setTimeout(() => { addNew(); }, 1200);
      }
      else {
        showError(rs);
      }
    },
    error:function(rs) {
      showError(rs);
      click = false;
    }
  });
}

function update() {
  if(click) return;
  click = true;
  clearErrorByClass('r');
  let id = $('#id').val();
  let code = $('#code').val().trim();
  let name = $('#name').val().trim();
  let active = $('#active').is(':checked') ? 1 : 0;

  if(code === '') {
    $('#code').hasError();
    click = false;
    return;
  }

  if(name === '') {
    $('#name').hasError();
    click = false;
    return;
  }

  load_in();

  $.ajax({
    url:`${HOME}update`,
    type:'POST',
    cache:false,
    data:{
      'id' : id,
      'code' : code,
      'name' : name,
      'active' : active
    },
    success:function(rs) {
      click = false;
      load_out();
      if(rs.trim() === 'success') {
        swal({
          title: 'Success',
          type: 'success',
          timer: 1000
        });
      }
      else {
        showError(rs);
      }
    },
    error:function(rs) {
      showError(rs);
      click = false;
    }
  })
}