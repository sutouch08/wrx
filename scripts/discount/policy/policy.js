function addNew() {
  window.location.href = `${HOME}add_new`;
}

function edit(code) {
  window.location.href = `${HOME}edit/${code}`;
}

function viewDetail(code){
  window.location.href = `${HOME}view_detail/${code}`;
}

$('#start-date').datepicker({
  dateFormat:'dd-mm-yy',
  onClose:function(sd){
    $('#end-date').datepicker('option', 'minDate', sd);
  }
});

$('#end-date').datepicker({
  dateFormat:'dd-mm-yy',
  onClose:function(sd){
    $('#start-date').datepicker('option', 'maxDate', sd);
  }
});

function toggleActive(el, id) {
  const active = el.checked ? 1 : 0;

  $.ajax({
    url: `${HOME}set_active`,
    type: 'POST',
    cache: false,
    data: {
      'id': id,
      'active': active
    },
    success: function (rs) {
      if (rs.trim() !== 'success') {
        $(el).prop('checked', !active);

        showError(rs);
      }
    },
    error: function (rs) {
      showError(rs);
    }
  });
}

function confirmDelete(id, name) {
  swal({
    title: "คุณแน่ใจ ?",
    text: `ต้องการลบ '${name}' หรือไม่ ?`,
    type: "warning",
    showCancelButton: true,
    confirmButtonColor: "#FA5858",
    confirmButtonText: 'ใช่, ฉันต้องการลบ',
    cancelButtonText: 'ยกเลิก',
    closeOnConfirm: true
  }, function () {
    load_in();

    setTimeout(() => {
      $.ajax({
        url:`${HOME}/delete`,
        type:'POST',
        cache:false,
        data:{ id: id },
        success: function(rs){
          load_out();

          if(rs.trim() === 'success') {
            swal({
              title: 'Deleted',
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
      })
    }, 100);   
  });
}
