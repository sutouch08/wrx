function syncData(){
  load_in();

  $.ajax({
    url:`${HOME}syncData`,
    type:'POST',
    cache:false,
    success:function(rs){
      load_out();

      if(rs.trim() == 'success'){
        swal({
          title:'Completed',
          type:'success',
          timer:1000
        });

        setTimeout(function() { goBack(); }, 1500);
      }
      else{
        showError(rs);
      }
    },
    error:function(rs) {
      showError(rs);
    }
  });
}
