function goBack() {
  let id = $('#id').val();
  window.location.href = BASE_URL + 'masters/products/edit/' + id + '/itemTab';
}


$('#items-wizard').ace_wizard().on('finished.fu.wizard', function (e) { genItems();});

// $('#items-wizard').ace_wizard({})
// .on('actionclicked.fu.wizard', function(e, info) {})
// .on('changed.fu.wizard', function() {})
// .on('finished.fu.wizard', function (e) { genItems();})
// .on('stepclick.fu.wizard', function(e) {});


$('.colorBox').change(function () {
  let color = $(this).val();

  if ($(this).is(':checked')) {
    let txt = $(`#co-${color}`).text();
    $('#colorBox').append(`<input type="hidden" id="col-${color}" class="color" value="${color}" />`);
    $('.imageBox').append(`<option value="${color}">${txt}</option>`);
  }
  else {
    $(`#col-${color}`).remove();
    $(".imageBox option[value='" + color + "']").remove();
  }

  preGen();
});


$('.sizeBox').change(function () {
  let size = $(this).val();

  if ($(this).is(':checked')) {
    $('#sizeBox').append(`<input type="hidden" id="size-${size}" class="size" value="${size}" />`);
    addCostPrice(size);
  }
  else {
    $(`#size-${size}`).remove();
    removeCostPrice(size);
  }

  preGen();
});


function preGen() {
  let style = $('#style').val();
  let old_style = $('#old_style').val();
  let countColor = $('.color').length;
  let countSize = $('.size').length;
  old_style = old_style === undefined ? style : old_style;

  $('#preGen').html('');

  if (countColor > 0 && countSize > 0) {
    genColorAndSize(style, old_style);
  }

  if (countColor > 0 && countSize == 0) {
    genColorOnly(style, old_style);
  }

  if (countColor == 0 && countSize > 0) {
    genSizeOnly(style, old_style);
  }
}


function genColorAndSize(style, old_style) {
  $('.color').each(function () {
    let color = $(this).val();
    $('.size').each(function () {
      let size = $(this).val();
      let itemCode = `${style}-${color}-${size}`;
      let old_code = "";

      if (old_style != "") {
        old_code = `${old_style}-${color}-${size}`;
      }

      addItemRow(itemCode, color, size, old_code);
    });
  });
}


function genColorOnly(style, old_style) {
  $('.color').each(function () {
    let color = $(this).val();
    let itemCode = `${style}-${color}`;
    let old_code = "";
    if (old_style != "") {
      old_code = `${old_style}-${color}`;
    }

    addItemRow(itemCode, color, '', old_code);
  });
}


function genSizeOnly(style, old_style) {
  $('.size').each(function () {
    let size = $(this).val();
    let itemCode = `${style}-${size}`;
    let old_code = "";
    if (old_style != "") {
      old_code = `${old_style}-${size}`;
    }

    addItemRow(itemCode, '', size, old_code);
  });
}

function addItemRow(itemCode, color, size, old_code) {
  let row = `<tr id="${itemCode}">
    <td class="middle text-center td-${color}">img</td>
    <td class="middle">${itemCode}</td>
    <td class="middle">
    <input type="text" class="form-control input-sm" name="old_code[${itemCode}]" value="${old_code}"></td>
    </tr>`;

  $('#preGen').append(row);
}


$('.imageBox').change(function () {
  let id = $(this).attr('id');
  let color = $(this).val();
  let url = $(`#img-${id}`).attr('src');
  let img = `<img src="${url}" id="se-${id}" class="se-${id}" style="width:50px;" />`;

  if (color !== '') {
    $(`.td-${color}`).html(img);
  } 
  else {
    $(`.se-${id}`).remove();
  }
});



function addCostPrice(size) {
  let cost = removeCommas($('#cost').val());
  let price = removeCommas($('#price').val());
  let field = `<tr id="row-${size}">
    <td class="middle text-center"><strong>${size}</strong></td>
    <td><input type="number" name="cost[${size}]" class="text-right" value="${cost}" /></td>
    <td><input type="number" name="price[${size}]" class="text-right" value="${price}" /></td>
    <td></td>
    </tr>`;

  $('#setCostPrice').append(field);
}


function removeCostPrice(size) {
  $(`#row-${size}`).remove();
}


function genItems() {
  let style = $('#style').val();
  let countColor = $('.color').length;
  let countSize = $('.size').length;

  if (style.length == 0) {
    swal('ไม่พบรุ่นสินค้า');
    return false;
  }

  if (countColor == 0 && countSize == 0) {
    swal({
      title: 'Error!',
      text: 'ต้องกำหนดสีหรือไซส์อย่างน้อย 1 รายการ',
      type: 'error'
    });

    return false;
  }

  load_in();

  $('#genItemFrom').submit();
}
