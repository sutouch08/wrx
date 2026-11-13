function addNew(){
  window.location.href = `${HOME}add_new`;
}

function edit(id) {
  window.location.href = `${HOME}edit/${id}`;
}

let click = false;

function add() {
  if(click) return;
  click = true;
  clearErrorByClass('r');
  let code = $('#code').val().trim();
  let name = $('#name').val().trim();
  let position = parseDefaultInt($('#position').val(), 1);

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
  
  $.ajax({
    url:`${HOME}add`,
    type:'POST',
    cache:false,
    data:{
      'code' : code,
      'name' : name,
      'position' : position
    },
    success:function(rs) {
      click = false;

      if(rs.trim() === 'success') {
        swal({
          title:'Success',
          type:'success',
          timer:1000
        });

        setTimeout(() => { addNew(); }, 1200);
      }
      else {
        showError(rs);
        click = false;
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
  let position = parseDefaultInt($('#position').val(), 1);
  
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
  
  $.ajax({
    url:`${HOME}update`,
    type:'POST',
    cache:false,
    data:{
      'id' : id,
      'code' : code,
      'name' : name,
      'position' : position
    },
    success:function(rs) {
      click = false;
      if(rs.trim() === 'success') {
        swal({
          title:'Success',
          type:'success',
          timer:1000
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


function confirmDelete(id, name){
  swal({
    title:'Are sure ?',
    text: `ต้องการลบ ${name} หรือไม่ ?`,
    type:'warning',
    showCancelButton: true,
		confirmButtonColor: '#FA5858',
		confirmButtonText: 'ใช่, ฉันต้องการลบ',
		cancelButtonText: 'ยกเลิก',
		closeOnConfirm: true
  },function() {
    setTimeout(() => {
      load_in();
      $.ajax({
        url:`${HOME}delete`,
        type:'POST',
        cache:false,
        data:{
          'id' : id
        },
        success:function(rs) {
          load_out();
          if(rs.trim() === 'success') {
            swal({
              title:'Success',
              type:'success',
              timer:1000
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
      })
    }, 100)
  });
}
