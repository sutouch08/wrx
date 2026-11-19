window.addEventListener('load', function() {
  zone_init();
});

function checkAllWh(el) {
  $('.chk').prop('checked', el.is(':checked'));
  zone_init();
}

function toggleAllProduct(option){
  $('#allProduct').val(option);
  if(option == 1) {
    $('#btn-pd-all').addClass('btn-primary');
    $('#btn-pd-range').removeClass('btn-primary');
    $('#pdFrom').val('');
    $('#pdFrom').attr('disabled', 'disabled');
    $('#pdTo').val('');
    $('#pdTo').attr('disabled', 'disabled');
    return
  }

  if(option == 0){
    $('#btn-pd-all').removeClass('btn-primary');
    $('#btn-pd-range').addClass('btn-primary');
    $('#pdFrom').removeAttr('disabled');
    $('#pdTo').removeAttr('disabled');
    $('#pdFrom').focus();
    return;
  }
}


$('#pdFrom').autocomplete({
  source :`${BASE_URL}auto_complete/get_item_code_and_name`,
  autoFocus:true,
  close:function(){
    var rs = $(this).val();
    var arr = rs.split(' | ');
    var pdFrom = arr[0];
    $(this).val(pdFrom);
    var pdTo = $('#pdTo').val();
    if(pdTo.length > 0 && pdFrom.length > 0){
      if(pdFrom > pdTo){
        $('#pdTo').val(pdFrom);
        $('#pdFrom').val(pdTo);
      }
    }
  }
});


$('#pdTo').autocomplete({
  source:`${BASE_URL}auto_complete/get_item_code_and_name`,
  autoFocus:true,
  close:function(){
    var rs = $(this).val();
    var arr = rs.split(' | ');
    var pdTo = arr[0];
    $(this).val(pdTo);
    var pdFrom = $('#pdFrom').val();
    if(pdTo.length > 0 && pdFrom.length > 0){
      if(pdFrom > pdTo){
        $('#pdTo').val(pdFrom);
        $('#pdFrom').val(pdTo);
      }
    }
  }
})

function toggleAllWarehouse(option){
  $('#allWarehouse').val(option);
  if(option == 1){
    $('#btn-wh-all').addClass('btn-primary');
    $('#btn-wh-range').removeClass('btn-primary');
    $('.chk').removeAttr('checked');
    return
  }

  if(option == 0){
    $('#btn-wh-all').removeClass('btn-primary');
    $('#btn-wh-range').addClass('btn-primary');
    $('#wh-modal').modal('show');
  }

  zone_init();
}


function toggleAllZone(option){
  $('#allZone').val(option);
  if(option == 1){
    $('#btn-zone-all').addClass('btn-primary');
    $('#btn-zone-range').removeClass('btn-primary');
    $('#zoneCode').val('');
    $('#zoneName').val('');
    $('#zoneName').attr('disabled', 'disabled');
    return
  }

  if(option == 0){
    $('#btn-zone-all').removeClass('btn-primary');
    $('#btn-zone-range').addClass('btn-primary');
    $('#zoneName').removeAttr('disabled');
    $('#zoneName').focus();
  }
}


function zone_init() {
  let warehouse = "";
  let i = 0;
  $('.chk:checked').each(function(index, el) {
    warehouse = warehouse + (i == 0 ? $(this).val() : "|"+$(this).val());
    i++;    
  });

  if(warehouse.length > 0) {
    warehouse = "/"+warehouse;
  }

  $('#zoneName').autocomplete({
    source:`${BASE_URL}auto_complete/get_zone_code_and_name${warehouse}`,
    autoFocus:true,
    close:function(){
      var rs = $(this).val();
      var rs = rs.split(' | ');
      if(rs.length == 2){
        $(this).val(rs[1]);
        $('#zoneCode').val(rs[0]);
      }
      else{
        $(this).val('');
        $('#zoneCode').val('');
      }
    }
  })
}


$('.chk').change(function(){
  zone_init();
})


function getReport() {
  clearErrorByClass('r');
  let h = {
    'allProduct' : $('#allProduct').val(),
    'allWhouse' : $('#allWarehouse').val(),
    'allZone' : $('#allZone').val(),
    'pdFrom' : $('#pdFrom').val(),
    'pdTo' : $('#pdTo').val(),
    'zoneCode' : $('#zoneCode').val(),
    'zoneName' : $('#zoneName').val(),
    'warehouse' : []
  };

  if(h.allProduct == 0 && (h.pdFrom.length == 0 || h.pdTo.length == 0)) {
    $('#pdFrom').addClass('has-error');
    $('#pdTo').addClass('has-error');
    return false;
  }
      
  if(h.allWhouse == 0 && $('.chk:checked').length == 0){    
    $('#wh-modal').modal('show');
    return false;
  }

  if(h.allZone == 0 && (h.zoneCode == '' || h.zoneName == '')){
    $('#zoneName').hasError();
    return false;    
  }
  
  if(h.allWhouse == 0) {
    $('.chk:checked').each(function(index, el) {
      h.warehouse.push($(this).val());
    });
  }
  
  load_in();

  $.ajax({
    url:`${HOME}get_report`,
    type:'POST',
    cache:false,
    data:{
      'data' : JSON.stringify(h)
    },
    success:function(rs) {
      load_out();

      if(isJson(rs)) {
        let ds = JSON.parse(rs);
        let source = $('#template').html();
        let output = $('#rs');
        render(source, ds, output);
      }
      else {
        showError(rs);
      }
    },
    error:function(rs) {
      showError(rs);
    }
  });
}


function doExport() {
  clearErrorByClass('r');

  let h = {
    'allProduct': $('#allProduct').val(),
    'allWhouse': $('#allWarehouse').val(),
    'allZone': $('#allZone').val(),
    'pdFrom': $('#pdFrom').val(),
    'pdTo': $('#pdTo').val(),
    'zoneCode': $('#zoneCode').val(),
    'zoneName': $('#zoneName').val(),
    'warehouse': []
  };

  if (h.allProduct == 0 && (h.pdFrom.length == 0 || h.pdTo.length == 0)) {
    $('#pdFrom').addClass('has-error');
    $('#pdTo').addClass('has-error');
    return false;
  }

  if (h.allWhouse == 0 && $('.chk:checked').length == 0) {
    $('#wh-modal').modal('show');
    return false;
  }

  if (h.allZone == 0 && (h.zoneCode == '' || h.zoneName == '')) {
    $('#zoneName').hasError();
    return false;
  }

  if (h.allWhouse == 0) {
    $('.chk:checked').each(function (index, el) {
      h.warehouse.push($(this).val());
    });
  }

  let token = generateUID();
  $('#token').val(token);
  //return false;
  $('#data').val(JSON.stringify(h));
  get_download(token);
  $('#export-form').submit();
}


function exportToCheck() {
  let zoneCode = $('#zoneCode').val();
  let zoneName = $('#zoneName').val();
 
  if(zoneCode.length == 0 || zoneName.length == 0)
  {
    swal("กรุณาระบุโซน");
    return false;
  }

  let token = generateUID();
  $('#export-token').val(token);
  $('#export-zone-code').val(zoneCode);
  get_download(token);
  $('#exportForm').submit();
}


