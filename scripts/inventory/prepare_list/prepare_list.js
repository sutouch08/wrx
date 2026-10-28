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

function exportFilter() {
  let h = {
    'order_code': $('#order-code').val().trim(),
    'pd_code': $('#pd-code').val().trim(),
    'zone_code': $('#zone-code').val().trim(),
    'warehouse_code': $('#warehouse').val(),
    'user': $('#user').val(),
    'from_date': $('#fromDate').val().trim(),
    'to_date': $('#toDate').val().trim(),
    'range': $('#range').val(),
    'token': generateUID()
  }

  if( ! isDate(h.from_date) || ! isDate(h.to_date)) {
    swal("กรุณาระบุวันที่");
    return false;
  }

  $('#export-data').val(JSON.stringify(h));
  get_download(h.token);
  $('#export-form').submit();
}
