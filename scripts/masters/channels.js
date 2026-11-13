function addNew() {
  window.location.href = `${HOME}add_new`;
}

function edit(code) {
  window.location.href = `${HOME}edit/${code}`;
}

function confirmDelete(id, name) {
  swal({
    title:'Are sure ?',
    text:'ต้องการลบ ' + name + ' หรือไม่ ?',
    type:'warning',
    showCancelButton: true,
		confirmButtonColor: '#FA5858',
		confirmButtonText: 'ใช่, ฉันต้องการลบ',
		cancelButtonText: 'ยกเลิก',
		closeOnConfirm: false
  },function() {
    load_in();

    setTimeout(() => {
      $.ajax({
        url: `${HOME}delete`,
        type:'POST',
        cache:false,
        data:{
          'id' : id
        },
        success:function(rs){
          load_out();

          if(rs.trim() === 'success') {
            swal({
              title: 'Deleted',
              text: 'ลบรายการเรียบร้อยแล้ว',
              type: 'success',
              timer:1000
            });

            $(`#row-${id}`).remove();
            reIndex();
          }
          else{
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

$('#customer-code').autocomplete({
  source:`${BASE_URL}auto_complete/get_customer_code_and_name`,
  autoFocus: true,
	close: function() {		
		let arr = $(this).val().split(' | ');
		if( arr.length == 2 ){
			var code = arr[0];
			var name = arr[1];
			$("#customer-code").val(code);
			$("#customer-name").val(name);
		}
    else{
			$("#customer-code").val('');
      $("#customer-name").val('');			
		}
	}
});


function toggleOnline(code){
  let option = $(`#online-${code}`).val();

  $.ajax({
    url:`${BASE_URL}masters/channels/toggle_online`,
    type:'POST',
    cache:false,
    data:{
      'code' : code,
      'is_online' : option
    },
    success:function(rs) {      
      if(rs.trim() == '1'){
        $(`#online-label-${code}`).html('<i class="fa fa-check green"></i>');
        $(`#online-${code}`).val(rs);
      }
      else if(rs.trim() == '0'){
        $(`#online-label-${code}`).html('<i class="fa fa-times"></i>');
        $(`#online-${code}`).val(rs);
      }
      else{
        showError(rs);
      }
    }
  })
}

let click = 0;

function add() {
  if(click == 1) {
    return;
  }

  click = 1;
  
  let code = $('#code').val().trim();
  let name = $('#name').val().trim();
  let customerCode = $('#customer-code').val().trim();
  let customerName = $('#customer-name').val().trim();
  let isOnline = $('#is-online').is(':checked') ? 1 : 0;

  if(!code) {
    showError('กรุณากรอกรหัส');
    click = 0;
    return;
  }

  if(!name) {
    showError('กรุณากรอกชื่อ');
    click = 0;
    return;
  }  
  
  load_in();

  $.ajax({
    url: `${HOME}add`,
    type: 'POST',
    cache: false,
    data: {
      'code': code,
      'name': name,
      'customer_code': customerCode,
      'customer_name' : customerName,
      'is_online': isOnline
    },
    success: function(rs) {
      load_out();

      if(rs.trim() === 'success') {
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
    error: function(rs) {     
      showError(rs);
      click = 0;
    }
  });
}

function update() {
  if(click == 1) {
    return;
  }

  click = 1;
  let code = $('#code').val().trim();
  let name = $('#name').val().trim();
  let customerCode = $('#customer-code').val().trim();
  let customerName = $('#customer-name').val().trim();
  let isOnline = $('#is-online').is(':checked') ? 1 : 0;

  if (!code) {
    showError('กรุณากรอกรหัส');
    click = 0;
    return;
  }

  if (!name) {
    showError('กรุณากรอกชื่อ');
    click = 0;
    return;
  }

  load_in();

  $.ajax({
    url: `${HOME}update`,
    type: 'POST',
    cache: false,
    data: {
      'code': code,
      'name': name,
      'customer_code': customerCode,
      'customer_name': customerName,
      'is_online': isOnline
    },
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
      load_out();
      showError(rs);
      click = 0;
    }
  });
}