$('#fromDate').datepicker({
  dateFormat:'dd-mm-yy',
  onClose: (sd) => {
    $('#toDate').datepicker('option', 'minDate', sd);
  }
});

$('#toDate').datepicker({
  dateFormat:'dd-mm-yy',
  onClose: (sd) => {
    $('#fromDate').datepicker('option', 'maxDate', sd);
  }
});

function exportFilter() {
  let h = {
    'reference' : $('#ref').val().trim(),
    'product_code' : $('#pd-code').val().trim(),
    'warehouse_code' : $('#warehouse').val(),
    'zone_code' : $('#zone-code').val().trim(),
    'from_date' : $('#fromDate').val().trim(),
    'to_date' : $('#toDate').val().trim(),
    'range' : $('#data-range').val(),
    'token' : generateUID()
  };

  $('#export-filter').val(JSON.stringify(h));
  get_download(h.token);
  $('#export-form').submit();
}
