function toggleAllProduct(option) {
  $('#allProduct').val(option);

  if(option == 1) {
    $('#btn-pd-all').addClass('btn-primary');
    $('#btn-pd-range').removeClass('btn-primary');
    $('#pdFrom').val('').attr('disabled', 'disabled');
    $('#pdTo').val('').attr('disabled', 'disabled');
  }

  if(option == 0) {
    $('#btn-pd-all').removeClass('btn-primary');
    $('#btn-pd-range').addClass('btn-primary');
    $('#pdTo').val('').removeAttr('disabled');
    $('#pdFrom').val('').removeAttr('disabled').focus();
  }
}

$('#pdFrom').autocomplete({
  source:`${HOME}get_item_code_and_name`,
  autoFocus: true,
  close:function() {
    let arr = $(this).val().split(' | ');
    if(arr.length > 1) {
      pdFrom = arr[0];
      $(this).val(pdFrom);
      pdTo = $('#pdTo').val();

      if(pdTo.length > 0 && pdFrom.length > 0) {
        if(pdFrom > pdTo) {
          $('#pdFrom').val(pdTo);
          $('#pdTo').val(pdFrom);
        }
      }
    }
  }
});

$('#pdTo').autocomplete({
  source:`${HOME}get_item_code_and_name`,
  autoFocus: true,
  close:function() {
    let arr = $(this).val().split(' | ');
    if(arr.length > 1) {
      pdTo = arr[0];
      $(this).val(pdTo);
      pdFrom = $('#pdFrom').val();

      if(pdTo.length > 0 && pdFrom.length > 0) {
        if(pdFrom > pdTo) {
          $('#pdFrom').val(pdTo);
          $('#pdTo').val(pdFrom);
        }
      }
    }
  }
});

function toggleAllWarehouse(option) {
  $('#allWarehouse').val(option);

  if (option == 1) {
    $('#btn-wh-all').addClass('btn-primary');
    $('#btn-wh-range').removeClass('btn-primary');
  }

  if (option == 0) {
    $('#btn-wh-all').removeClass('btn-primary');
    $('#btn-wh-range').addClass('btn-primary');
    $('#wh-modal').modal('show');
  }
}

function checkAllWh(el) {
  $('.chk').prop('checked', el.is(':checked'));
}

function getReport() {
  clearErrorByClass('r');

  let h = {
    'allProduct': $('#allProduct').val(),
    'allWhouse': $('#allWarehouse').val(),
    'pdFrom': $('#pdFrom').val().trim(),
    'pdTo': $('#pdTo').val().trim(),
    'warehouse': []
  };

  if(h.allProduct == 0 && (h.pdFrom.length == 0 || h.pdTo.length == 0)) {
    $('#pdFrom').hasError();
    $('#pdTo').hasError();
    return false;
  }

  if(h.allWhouse == 0 && $('.chk:checked').length == 0) {
    $('#wh-modal').modal('show');
    return false;
  }

  if(h.allWhouse == 0) {
    $('.chk:checked').each(function() {
      h.warehouse.push($(this).val());
    });
  }

  load_in();

  $.ajax({
    url: `${HOME}get_report`,
    type: 'POST',
    data: h,
    success: function(rs) {
      load_out();
      
      if(isJson(rs)) {
        let data = JSON.parse(rs);
        let template  = $('#template').html();
        let output = $('#result-table');

        render(template, data, output);
      }
      else {
        showError(rs);
      }
    },
    error: function(rs) {
     showError(rs);
    }
  });
}

function doExport() {
  clearErrorByClass('r');

  let h = {
    'allProduct': $('#allProduct').val(),
    'allWhouse': $('#allWarehouse').val(),
    'pdFrom': $('#pdFrom').val().trim(),
    'pdTo': $('#pdTo').val().trim(),
    'warehouse': []
  };

  if (h.allProduct == 0 && (h.pdFrom.length == 0 || h.pdTo.length == 0)) {
    $('#pdFrom').hasError();
    $('#pdTo').hasError();
    return false;
  }

  if (h.allWhouse == 0 && $('.chk:checked').length == 0) {
    $('#wh-modal').modal('show');
    return false;
  }

  if (h.allWhouse == 0) {
    $('.chk:checked').each(function () {
      h.warehouse.push($(this).val());
    });
  }

  let token = generateUID();
  $('#token').val(token);
  $('#data').val(JSON.stringify(h));
  get_download(token);
  $('#export-form').submit();
}