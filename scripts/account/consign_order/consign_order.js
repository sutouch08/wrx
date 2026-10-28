function addNew(){
  window.location.href = `${HOME}add_new`;
}

function viewDetail(code){
  window.location.href = `${HOME}view_detail/${code}`;
}

function edit(code)
{
  window.location.href = `${HOME}edit/${code}`;
}

//--- delete all data and cancle document
function getDelete(code){
	swal({
		title: "คุณแน่ใจ ?",
		text: "ต้องการยกเลิก '"+code+"' หรือไม่ ?",
		type: "warning",
		showCancelButton: true,
		confirmButtonColor: "#DD6B55",
		confirmButtonText: 'ใช่, ฉันต้องการ',
		cancelButtonText: 'ไม่ใช่',
		closeOnConfirm: true
		}, function(){
      $('#cancle-code').val(code);
      $('#cancle-reason').val('').removeClass('has-error');
      setTimeout(() => {
        cancle(code);
      }, 200);
	});
}

function cancle(code)
{	
	let reason = $('#cancle-reason').val().trim();

	if(reason.length < 10)
	{
		$('#cancle-modal').modal('show');
		return false;
	}

	load_in();

  $.ajax({
    url: `${HOME}cancle/${code}`,
    type:"POST",
    cache: false,
    data:{
      "reason" : reason
    },
    success: function(rs) {      
      if( rs.trim() === 'success' ) {
				swal({
					title: 'Success',
					type: 'success',
					timer: 1000
				});

				setTimeout(() => {
					window.location.reload();
				}, 1200);
			}
			else {
				showError(rs);
			}
    }
  });
}

function doCancle() {
	let code = $('#cancle-code').val();
	let reason = $('#cancle-reason').val().trim();

	if( reason.length < 10) {
    $('#cancle-reason').addClass('has-error').focus();
		return false;
	}

	$('#cancle-modal').modal('hide');
	return cancle(code);
}

$('#cancle-modal').on('shown.bs.modal', function() {
	$('#cancle-reason').focus();
});

function doExport() {
  let code = $('#consign_code').val();
  load_in();

  $.ajax({
    url: `${HOME}export_consign/${code}`,
    type:'POST',
    cache: false,
    success:function(rs) {
      load_out();
      
      if(rs.trim() == 'success') {
        swal({
          title:'success',
          type:'success',
          timer:1000
        });

        setTimeout(function(){
          window.location.reload();
        }, 1200);
      }
			else {
        showError(rs);
      }
    }
  });
}

$("#fromDate").datepicker({
	dateFormat: 'dd-mm-yy',
	onClose: function(ds){
		$("#toDate").datepicker("option", "minDate", ds);
	}
});

$("#toDate").datepicker({
	dateFormat: 'dd-mm-yy',
	onClose: function(ds){
		$("#fromDate").datepicker("option", "maxDate", ds);
	}
});

function printConsignOrder() {
	const code = $('#consign_code').val();
	const width = 800;
	const height = 900;
	const left = ($(document).width() - width) / 2;  
  const target = `${HOME}print_consign/${code}`;

	window.open(target, '_blank', `width=${width}, height=${height}, left=${left}, scrollbars=yes`);
}
