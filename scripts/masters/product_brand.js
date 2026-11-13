function addNew() {
  window.location.href = `${HOME}add_new`;
}

function edit(code) {
  window.location.href = `${HOME}edit/${code}`;
}

function confirmDelete(code, name, no) {
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
          code: code
        },
        success:function(rs) {
          load_out();
          if(rs === 'success') {
            $('#row-'+no).remove();
          } 
          else {
            showError(rs);
          }
        },
        error:function(rs) {
          showError(rs);
        }
      });
    }, 100);
  })
}

var click = 0;

function add() {
  if(click > 0) {
    return false;
  }

  click = 1;
  clearErrorByClass('r');
  let code = $('#code').val().trim();
  let name = $('#name').val().trim();

  if(code === '') {
    $('#code').hasError();
    click = 0;
    return false;
  }

  if(name === '') {
    $('#name').hasError();
    click = 0;
    return false;
  }

  load_in();

  $.ajax({
    url: `${HOME}add`,
    type: 'POST',
    cache: false,
    data: {
      code: code,
      name: name
    },
    success: function(rs) {
      load_out();
      click = 0;
      if(rs === 'success') {
        swal({
          title:'Success',
          type:'success',
          timer:1000
        });

        setTimeout(() => {
          addNew();
        }, 1200);
      } 
      else {
        showError(rs);
      }
    },
    error: function(rs) {
      load_out();
      click = 0;
      showError(rs);
    }
  })
}

function update() {
  if(click > 0) {
    return false;
  }

  click = 1;
  clearErrorByClass('r');
  let code = $('#code').val().trim();
  let name = $('#name').val().trim();

  if(code === '') {
    $('#code').hasError();
    click = 0;
    return false;
  }

  if(name === '') {
    $('#name').hasError();
    click = 0;
    return false;
  }

  load_in();

  $.ajax({
    url: `${HOME}update`,
    type: 'POST',
    cache: false,
    data: {
      code: code,
      name: name
    },
    success: function(rs) {
      load_out();
      click = 0;
      if(rs === 'success') {
        swal({
          title:'Success',
          type:'success',
          timer:1000
        });

        setTimeout(() => {
          edit(code);
        }, 1200);
      } 
      else {
        showError(rs);
      }
    },
    error: function(rs) {
      load_out();
      click = 0;
      showError(rs);
    }
  })
}
