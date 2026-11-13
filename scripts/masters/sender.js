function addNew() {
	window.location.href = `${HOME}add_new`;
}


function edit(id) {
	window.location.href = `${HOME}edit/${id}`;
}

let click = false;

function save() {
	if (click) {
		return false;
	}

	click = true;

	let code = $('#code').val();
	let name = $('#name').val();
	let adr1 = $('#address1').val();
	let adr2 = $('#address2').val();
	let phone = $('#phone').val();
	let open = $('#open').val();
	let close = $('#close').val();
	let type = $('#type').val();
	let inlist = $('#in_list').is(':checked') ? 1 : 0;
	let force_tracking = $('#force_tracking').is(':checked') ? 1 : 0;
	let auto_gen = $('#auto_gen').is(':checked') ? 1 : 0;
	let prefix = $('#tracking_prefix').val();

	if (code.length === 0) {
		swal("กรุณากำหนดรหัส");
		click = false;
		return false;
	}

	if (name.length === 0) {
		swal("กรุณากำหนดชื่อ");
		click = false;
		return false;
	}

	$.ajax({
		url: `${HOME}add`,
		type: 'POST',
		cache: false,
		data: {
			'code': code,
			'name': name,
			'address1': adr1,
			'address2': adr2,
			'phone': phone,
			'open': open,
			'close': close,
			'type': type,
			'show_in_list': inlist,
			'force_tracking': force_tracking,
			'auto_gen': auto_gen,
			'prefix': prefix
		},
		success: function (rs) {
			click = false;
			if (rs.trim() === 'success') {
				swal({
					title: 'Success',
					type: 'success',
					timer: 1000
				});

				setTimeout(function () { addNew(); }, 1200);
			}
			else {
				click = false;
				showError(rs);
			}
		},
		error: function (rs) {
			click = false;
			showError(rs);
		}
	});
}


function update() {
	if (click) {
		return false;
	}

	click = true;

	let id = $('#id').val();
	let code = $('#code').val();
	let name = $('#name').val();
	let adr1 = $('#address1').val();
	let adr2 = $('#address2').val();
	let phone = $('#phone').val();
	let open = $('#open').val();
	let close = $('#close').val();
	let type = $('#type').val();
	let inlist = $('#in_list').is(':checked') ? 1 : 0;
	let force_tracking = $('#force_tracking').is(':checked') ? 1 : 0;
	let auto_gen = $('#auto_gen').is(':checked') ? 1 : 0;
	let prefix = $('#tracking_prefix').val();

	if (code.length === 0) {
		swal("กรุณากำหนดรหัส");
		click = false;
		return false;
	}

	if (name.length === 0) {
		swal("กรุณากำหนดชื่อ");
		click = false;
		return false;
	}

	$.ajax({
		url: `${HOME}update/${id}`,
		type: 'POST',
		cache: false,
		data: {
			'code': code,
			'name': name,
			'address1': adr1,
			'address2': adr2,
			'phone': phone,
			'open': open,
			'close': close,
			'type': type,
			'show_in_list': inlist,
			'force_tracking': force_tracking,
			'auto_gen': auto_gen,
			'prefix': prefix
		},
		success: function (rs) {
			click = false;
			if (rs.trim() === 'success') {
				swal({
					title: 'Success',
					type: 'success',
					timer: 1000
				});
			}
			else {
				showError(rs);
				click = false;
			}
		},
		error: function (rs) {
			click = false;
			showError(rs);
		}
	});
}


function getDelete(id, name) {
	swal({
		title: 'Are sure ?',
		text: 'ต้องการลบ ' + name + ' หรือไม่ ?',
		type: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#FA5858',
		confirmButtonText: 'ใช่, ฉันต้องการลบ',
		cancelButtonText: 'ยกเลิก',
		closeOnConfirm: true
	}, function () {
		load_in();

		setTimeout(() => {
			$.ajax({
				url: `${HOME}delete`,
				type: 'POST',
				cache: false,
				data: {
					'id': id
				},
				success: function (rs) {
					load_out();
					if (rs.trim() === 'success') {
						swal({
							title: 'Success',
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
				error: function (rs) {
					showError(rs);
				}
			})
		}, 100);
	});
}

function toggleAutoGen() {
	$('#auto_gen').prop('checked', false);

	if ($('#force_tracking').is(':checked')) {
		$('#gen_potion').removeClass('hide');
	}
	else {
		$('#gen_potion').addClass('hide');
		$('#prefix').addClass('hide');
	}
}


function togglePrefix() {
	if ($('#auto_gen').is(':checked')) {
		$('#prefix').removeClass('hide');
	}
	else {
		$('#prefix').addClass('hide');
	}
}


function toggleListing(id) {
	$.ajax({
		url: `${HOME}toggle_listing/${id}`,
		type: 'GET',
		cache: false,
		success: function (rs) {
			if (isJson(rs)) {
				let ds = JSON.parse(rs);

				$('#listing-' + id).html(ds.data);
			}
			else {
				showError(rs);
			}
		},
		error: function (rs) {
			showError(rs);
		}
	})
}
