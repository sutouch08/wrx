let lastfocus = 'item'; //-- to keep track of last focused input, 'item' or 'barcode' after add new row

$('#barcode-item').keyup(function (e) {
  if (e.keyCode == 13) {
    if ($(this).val() != '') {
      getItemByBarcode();
    }
  }
});

$('#barcode-item').focusout(function (e) {
  if ($(this).val() != '') {
    getItemByBarcode();
  }
});

$('#item-code').keyup(function (e) {
  if (e.keyCode == 13) {
    getItemByCode();
  }
});

$('#item-code').focusout(function (e) {
  getItemByCode();
});

$('#item-code').autocomplete({
  source: `${BASE_URL}auto_complete/get_product_code`,
  autoFocus: true,
  close: function () {
    if ($(this).val().trim() == 'no item found') {
      $(this).val('');
    }
  }
});

function getItemByCode() {
  let code = $('#item-code').val().trim();
  let zone_code = $('#zone_code').val();

  if (code != '') {
    $.ajax({
      url: `${HOME}get_item_by_code`,
      type: 'GET',
      cache: false,
      data: {
        'code': code,
        'zone_code': zone_code
      },
      success: function (rs) {
        lastfocus = 'item'; //-- set last focus to item after getting item by code

        if (isJson(rs)) {
          let ds = JSON.parse(rs);
          $('#product_code').val(ds.pdCode);
          $('#barcode-item').val(ds.barcode);
          $('#txt-price').val(ds.price);
          $('#txt-disc').val(ds.disc);
          $('#stock-qty').text(ds.stock);
          $('#count_stock').val(ds.count_stock);
          $('#txt-price').focus();
          $('#txt-price').select();
        }
        else {
          showError(rs);
          $('#product_code').val('');
          $('#barcode-item').val('');
          $('#txt-price').val('');
          $('#txt-disc').val('');
          $('#stock-qty').text(0);
          $('#count_stock').val(1);
        }
      },
      error: function (rs) {
        showError(rs);
      }
    });
  }
}

function getItemByBarcode() {
  let barcode = $('#barcode-item').val().trim();
  let zone_code = $('#zone_code').val();

  if (barcode != '') {
    $.ajax({
      url: `${HOME}get_item_by_barcode`,
      type: 'GET',
      cache: false,
      data: {
        'barcode': barcode,
        'zone_code': zone_code
      },
      success: function (rs) {
        if (isJson(rs)) {
          lastfocus = 'barcode'; //-- set last focus to barcode after getting item by barcode

          let ds = JSON.parse(rs);
          $('#product_code').val(ds.pdCode);
          $('#item-code').val(ds.product);
          $('#txt-price').val(ds.price);
          $('#txt-disc').val(ds.disc);
          $('#stock-qty').text(ds.stock);
          $('#count_stock').val(ds.count_stock);
          $('#txt-price').focus();
          $('#txt-price').select();
        }
        else {
          $('#product_code').val('');
          $('#item-code').val('');
          $('#txt-price').val('');
          $('#txt-disc').val('');
          $('#stock-qty').text(0);
          $('#count_stock').val(1);
          showError(rs);
        }
      },
      error: function (rs) {
        showError(rs);
      }
    });
  }
}

//--- skip to qty if space bar key press
$('#txt-price').keydown(function (e) {
  if (e.keyCode == 32) {
    e.preventDefault();
    $('#txt-qty').focus();
  }
});

$('#txt-price').keyup(function (e) {
  if (e.keyCode == 13 && $(this).val() != '') {
    $('#txt-disc').focus();
    $('#txt-disc').select();
  }

  calAmount();
});

$('#txt-price').focusout(function () {
  let amount = parseDefaultFloat($(this).val(), 0);

  if (amount <= 0) {
    $('#txt-disc').val(0);
  }

  if (amount < 0) {
    $(this).val(0);
  }
});

$('#txt-disc').keyup(function (e) {
  if (e.keyCode == 13) {
    $('#txt-qty').focus().select();    
  }

  calAmount();
});

$('#txt-qty').keyup(function (e) {
  if (e.keyCode == 13) {
    let qty = parseDefaultInt($(this).val(), 0);
    
    if (qty > 0) {
      return addToDetail();      
    }
  }

  calAmount();
});

function calAmount() {
  qty = parseDefault(parseInt($('#txt-qty').val()), 0);
  price = parseDefault(parseFloat($('#txt-price').val()), 0);
  disc = parseDiscount($('#txt-disc').val(), price);
  discount = disc.discountAmount * qty;
  amount = (price * qty) - discount;
  $('#txt-amount').text(addCommas(amount.toFixed(2)));
}


function addToDetail() {
  let code = $('#consign_code').val();
  let qty = parseInt($('#txt-qty').val());
  let stock = parseInt($('#stock-qty').text());
  let product_code = $('#product_code').val();
  let price = $('#txt-price').val();
  let disc = $('#txt-disc').val();
  let auz = $('#auz').val();
  let count_stock = $('#count_stock').val();

  if (qty <= 0) {
    swal('จำนวนไม่ถูกต้อง');
    return false;
  }

  if (qty > stock && auz == 0 && count_stock == 1) {
    swal('ยอดในโซนไม่พอตัด');
    return false;
  }

  if (product_code == '') {
    swal('สินค้าไม่ถูกต้อง');
    return false;
  }

  load_in();

  $.ajax({
    url: `${HOME}add_detail/${code}`,
    type: 'POST',
    cache: false,
    data: {
      'product_code': product_code,
      'qty': qty,
      'price': price,
      'disc': disc
    },
    success: function (rs) {
      load_out();
      if (isJson(rs)) {
        let data = JSON.parse(rs);
        let id = data.id;

        if ($('#row-' + id).length == 1) {
          $('#input-qty-' + id).val(data.qty);
          $('#qty-' + id).text(addCommas(data.qty));
        }
        else {
          let source = $('#new-row-template').html();
          let output = $('#detail-table');
          render_prepend(source, data, output);
        }

        reIndex();
        reCalAll();
        clearFields();
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

function clearFields() {
  $('#barcode-item').val('');
  $('#item-code').val('');
  $('#txt-price').val('');
  $('#txt-disc').val('');
  $('#stock-qty').text(0);
  $('#txt-qty').val('');
  $('#txt-amount').text('');
  $('#product_code').val('');

  if(lastfocus === 'item') {
    $('#item-code').focus();
  }
  else {
    $('#barcode-item').focus();
  }
}

function focusRow(id) {
  $('.rox').removeClass('blue');
  $('#row-' + id).addClass('blue');
}

function reCal(id) {
  let price = parseDefault(parseFloat(removeCommas($('#input-price-' + id).val())), 0);
  let disc = parseDiscount($('#input-disc-' + id).val(), price);
  let qty = parseDefault(parseFloat(removeCommas($('#qty-' + id).text())), 1);
  let amount = qty * (price - disc.discountAmount);
  $('#amount-' + id).text(addCommas(amount.toFixed(2)));
  updateTotalAmount();
}

function reCalAll() {
  $('.rox').each(function (index, el) {
    let ids = $(this).attr('id').split('-');
    let id = ids[1];
    reCal(id);
  });

  updateTotalQty();
  updateTotalAmount();
}

function updateTotalAmount() {
  let total = 0;

  $('.amount').each(function (index, el) {
    let amount = parseDefault(parseFloat(removeCommas($(this).text())), 0);
    total += amount;
  });

  total = parseFloat(total).toFixed(2);
  $('#total-amount').text(addCommas(total));
}

function updateTotalQty() {
  let total = 0;

  $('.qty').each(function (index, el) {
    let qty = parseInt(removeCommas($(this).text()));
    total += qty;
  });

  $('#total-qty').text(addCommas(total));
}

function getEditDiscount() {
  $('.disc').addClass('hide');
  $('.input-disc').removeClass('hide');
  $('#btn-edit-disc').addClass('hide');
  $('#btn-update-disc').removeClass('hide');
}

function getEditPrice() {
  $('.price').addClass('hide');
  $('.input-price').removeClass('hide');
  $('#btn-edit-price').addClass('hide');
  $('#btn-update-price').removeClass('hide');
}

function nextFocus(el, className) {
  let cl = $('.' + className);
  let idx = cl.index(el);
  cl.eq(idx + 1).focus();
}

$('.input-price').keyup(function (e) {
  let ids = $(this).attr('id').split('-');
  let id = ids[2];
  let price = parseDefault(parseFloat($(this).val()), 0);

  if (price < 0) {
    swal('ราคาน้อยกว่า 0');
    $(this).val(0);
  }

  reCal(id);

  if (e.keyCode == 13) {
    nextFocus($(this), 'input-price');
  }
});

$('.input-disc').keyup(function (e) {
  let ids = $(this).attr('id').split('-');
  let id = ids[2];
  let price = parseDefault(parseFloat($('#input-price-' + id).val()), 0);
  let disc = parseDiscount($(this).val(), price);

  if (disc.discountAmount > price) {
    swal('ส่วนลดเกินราคา');
    $(this).val('');
  }

  if (disc.discountAmount < 0) {
    swal('ส่วนลดน้อยกว่า 0');
    $(this).val('');
  }

  reCal(id);

  if (e.keyCode == 13) {
    nextFocus($(this), 'input-disc');
  }
});

function updatePrice() {
  clearErrorByClass('input-price');  
  let code = $('#consign_code').val();
  let empty_qty = 0;
  let ds = [];

  $('.input-price').each(function (index, el) {
    let ids = $(this).attr('id').split('-');
    let id = ids[2];

    if ($(this).val() == '') {
      empty_qty++;
      $(this).addClass('has-error');
    } 
    else {
      let pName = 'price[' + id + ']';
      let price = $(this).val();
      let qty = removeCommas($('#qty-' + id).text());
      ds.push({ 'name': pName, 'value': price });      
    }
  });

  if (empty_qty > 0) {
    swal('พบรายการที่ไม่ถูกต้อง ' + empty_qty + ' รายการ');
    return false;
  }

  if (ds.length == 0) {
    swal('ไม่พบรายการ');
    return false;
  }

  load_in();

  $.ajax({
    url: `${HOME}update_price/${code}`,
    type: 'POST',
    cache: 'false',
    data: ds,
    success: function (rs) {
      load_out();
      
      if (rs.trim() == 'success') {
        swal({
          title: 'Updated',
          type: 'success',
          timer: 1000
        });

        setTimeout(function () { refresh();}, 1200);
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

function updateDiscount() {
  let code = $('#consign_code').val();
  let ds = [];

  $('.input-disc').each(function (index, el) {
    let ids = $(this).attr('id').split('-');
    let id = ids[2];
    let disc = $('#input-disc-' + id).val();
    let name = 'disc[' + id + ']';
    ds.push({ 'name': name, 'value': disc });
  });

  load_in();

  $.ajax({
    url: `${HOME}update_discount/${code}`,
    type: 'POST',
    cache: false,
    data: ds,
    success: function (rs) {
      load_out();
      
      if (rs.trim() == 'success') {
        swal({
          title: 'Updated',
          type: 'success',
          timer: 1000
        });

        setTimeout(function () { refresh(); }, 1200);
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
