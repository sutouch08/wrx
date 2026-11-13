function addNew() {
	window.location.href = `${HOME}add_new`;
}

function edit(id) {
	window.location.href = `${HOME}edit/${id}`;
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
				data: { 'id': id },
				success: function (rs) {
					load_out();
					
					if (rs.trim() === 'success') {
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
			});
		}, 100);
	});
}

let click = false;

function add() {
	if (click) {
		return;
	}
	click = true;

	let h = {
		'customer_code': $('#customer_code').val(),
		'main_sender': $('#main_sender_id').val(),
		'second_sender': $('#second_sender_id').val(),
		'third_sender': $('#third_sender_id').val()
	};

	if (!h.customer_code || !h.main_sender) {
		click = false;
		swal('Required', 'กรุณาระบุลูกค้าและขนส่งหลัก', 'warning');
		return;
	}

	load_in();

	$.ajax({
		url: `${HOME}add`,
		type: 'POST',
		data: h,
		success: function (rs) {
			load_out();
			click = false;

			if (rs.trim() === 'success') {
				swal({
					title: 'Success',
					type: 'success',
					timer: 1000
				});

				setTimeout(() => { addNew() }, 1200);
			}
			else {
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
		return;
	}
	click = true;

	let h = {
		'id': $('#id').val(),
		'customer_code': $('#customer_code').val(),
		'main_sender': $('#main_sender_id').val(),
		'second_sender': $('#second_sender_id').val(),
		'third_sender': $('#third_sender_id').val()
	};

	if (!h.customer_code || !h.main_sender) {
		click = false;
		swal('Required', 'กรุณาระบุลูกค้าและขนส่งหลัก', 'warning');
		return;
	}

	load_in();

	$.ajax({
		url: `${HOME}update`,
		type: 'POST',
		data: h,
		success: function (rs) {
			load_out();
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
			}
		},
		error: function (rs) {
			click = false;
			showError(rs);
		}
	});
}

$('#customer_name').autocomplete({
	source: `${BASE_URL}auto_complete/get_customer_code_and_name`,
	autoFocus: true,
	close: function () {
		var rs = $(this).val();
		var arr = rs.split(' | ');
		if (arr.length == 2) {
			$(this).val(arr[1]);
			$("#customer_code").val(arr[0]);
			$('#customer_name').focus();
		}
		else {
			$(this).val('');
			$("#customer_code").val('');
		}
	}
});

$('#main_sender').autocomplete({
	source: `${BASE_URL}auto_complete/get_sender`,
	autoFocus: true,
	close: function () {
		var rs = $(this).val();
		var arr = rs.split(' | ');
		if (arr.length == 2) {
			$(this).val(arr[1]);
			$("#main_sender_id").val(arr[0]);
			$('#main_sender').focus();
		}
		else {
			$(this).val('');
			$("#main_sender_id").val('');
		}
	}
});

$('#second_sender').autocomplete({
	source: `${BASE_URL}auto_complete/get_sender`,
	autoFocus: true,
	close: function () {
		var rs = $(this).val();
		var arr = rs.split(' | ');
		if (arr.length == 2) {
			$(this).val(arr[1]);
			$("#second_sender_id").val(arr[0]);
			$('#second_sender').focus();
		}
		else {
			$(this).val('');
			$("#second_sender_id").val('');
		}
	}
});

$('#third_sender').autocomplete({
	source: `${BASE_URL}auto_complete/get_sender`,
	autoFocus: true,
	close: function () {
		var rs = $(this).val();
		var arr = rs.split(' | ');
		if (arr.length == 2) {
			$(this).val(arr[1]);
			$("#third_sender_id").val(arr[0]);
			$('#third_sender').focus();
		}
		else {
			$(this).val('');
			$("#third_sender_id").val('');
		}
	}
});
