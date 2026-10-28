function add() {
  clearErrorByClass('r');
  let h = {
    'name': $('#name').val().trim(),
    'start_date': $('#start-date').val().trim(),
    'end_date': $('#end-date').val().trim()
  };

  if (h.name.length == 0) {
    $('#name').hasError();
    return false;
  }

  if (!isDate(h.start_date) || !isDate(h.end_date)) {
    $('#start-date').hasError();
    $('#end-date').hasError();
    return false;
  }

  load_in();

  $.ajax({
    url: `${HOME}add`,
    type: 'POST',
    cache: false,
    data: {
      'data': JSON.stringify(h)
    },
    success: function (rs) {
      load_out();

      if (isJson(rs)) {
        let ds = JSON.parse(rs);

        if (ds.status === 'success') {
          edit(ds.code);
        }
        else {
          showError(ds.message);
        }
      }
      else {
        showError(rs);
      }
    },
    error: function (rs) {
      showError(rs);
    }
  });
}

function update() {
  clearErrorByClass('r');
  let h = {
    'id': $('#policy-id').val(),
    'name': $('#name').val().trim(),
    'start_date': $('#start-date').val().trim(),
    'end_date': $('#end-date').val().trim(),
    'active': $('#active').is(':checked') ? 1 : 0
  };

  if (h.name.length == 0) {
    $('#name').hasError();
    return false;
  }

  if (!isDate(h.start_date) || !isDate(h.end_date)) {
    $('#start-date').hasError();
    $('#end-date').hasError();
    return false;
  }

  if (h.active == $('#prev-active').val()
    && h.name == $('#prev-name').val()
    && h.start_date == $('#prev-start-date').val()
    && h.end_date == $('#prev-end-date').val()
  ) {
    return false;
  }

  load_in();

  $.ajax({
    url: `${HOME}update`,
    type: 'POST',
    cache: false,
    data: {
      'data': JSON.stringify(h)
    },
    success: function (rs) {
      load_out();

      if (rs.trim() === 'success') {
        swal({
          title: 'Success',
          type: 'success',
          timer: 1000
        });
      }
      else {
        showError(rs);
      }
    },
    error: function (rs) {
      showError(rs);
    }
  });
}

function addRule() {
  let id = $('#policy-id').val();

  if ($('.chk-rule:checked').length) {
    let rules = [];

    $('.chk-rule:checked').each(function () {
      rules.push($(this).val());
    });

    if (rules.length) {
      $('#rule-modal').modal('hide');

      load_in();

      $.ajax({
        url: `${HOME}add_rule`,
        type: 'POST',
        cache: false,
        data: {
          'id_policy': id,
          'rules': rules
        },
        success: function (rs) {
          load_out();

          if (rs.trim() == 'success') {
            setTimeout(() => { refresh(); }, 300);
          }
          else {
            showError(rs);
          }
        },
        error: function (rs) {
          showError(rs);
        }
      });
    }
  }
}

function showRuleList() {
  $('#rule-modal').modal('show');
}

function getActiveRuleList() {
  load_in();

  $.ajax({
    url: `${HOME}get_active_rule`,
    type: 'GET',
    cache: false,
    success: function (rs) {
      load_out();

      if (isJson(rs)) {
        source = $('#rule-template').html();
        data = $.parseJSON(rs);
        output = $('#result');
        render(source, data, output);
        showRuleList();
      }
      else {
        showError(rs);
      }
    },
    error: function (rs) {
      showError(rs);
    }
  });
}

function viewRuleDetail(id_rule) {
  const url = `${BASE_URL}discount/discount_rule/view_rule_detail/${id_rule}`;
  const width = 900;
  const height = 1000;
  const left = ($(document).width() - width) / 2;
  window.open(url, '_blank', `width=${width}, height=${height}, left=${left}, location=no, scrollbars=yes`);
}

function unlinkRule(id, name) {
  swal({
    title: "คุณแน่ใจ ?",
    text: `ต้องการลบ '${name}' ออกจากนโยบายหรือไม่ ?`,
    type: "warning",
    showCancelButton: true,
    confirmButtonColor: "#FA5858",
    confirmButtonText: 'ใช่, ฉันต้องการลบ',
    cancelButtonText: 'ยกเลิก',
    closeOnConfirm: false
  }, function () {
    load_in();

    setTimeout(() => {
      $.ajax({
        url: `${BASE_URL}discount/discount_rule/unlink_rule`,
        type: "POST",
        cache: false,
        data: {
          "id_rule": id
        },
        success: function (rs) {
          load_out();

          if (rs.trim() == 'success') {
            swal({
              title: 'Success',
              type: 'success',
              timer: 1000
            });

            $(`#row_${id}`).remove();
          }
          else {
            showError(rs);
          }
        },
        error: function (rs) {
          showError(rs);
        }
      });
    }, 100);
  });
}
