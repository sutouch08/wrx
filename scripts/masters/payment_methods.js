function addNew(){
  window.location.href = `${HOME}add_new`;
}

function edit(code) {
  window.location.href = `${HOME}edit/${code}`;
}

let click = false;

function add() {
  if(click) {
    return;
  }
  click = true;
  clearErrorByClass('r');

  let code = $('#code').val().trim();
  let name = $('#name').val().trim();
  let role = $('#role').val();

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
      'role' : role
    },
    success:function(rs) {
      load_out();
      click = false;

      if(rs.trim() === 'success') {
        swal({
          title:'Success',
          type:'success',
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
  if(click) {
    return;
  }
  click = true;
  clearErrorByClass('r');
  let id = $('#id').val();
  let code = $('#code').val().trim();
  let name = $('#name').val().trim();
  let role = $('#role').val();

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
      'role' : role
    },
    success:function(rs) {
      load_out();
      click = false;

      if(rs.trim() === 'success') {
        swal({
          title:'Success',
          type:'success',
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
  });
}

function confirmDelete(code, name, id) {
  swal({
    title:'Are sure ?',
    text:`ต้องการลบ ${name} หรือไม่ ?`,
    type:'warning',
    showCancelButton: true,
		confirmButtonColor: '#FA5858',
		confirmButtonText: 'ใช่, ฉันต้องการลบ',
		cancelButtonText: 'ยกเลิก',
		closeOnConfirm: true
  },function() {
    load_in();

    setTimeout(() => {
      $.ajax({
        url:`${HOME}delete`,
        type:'POST',
        cache:false,
        data:{
          'code' : code
        },
        success:function(rs) {
          load_out();

          if(rs.trim() === 'success') {
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
      })
    }, 100);    
  })
}
