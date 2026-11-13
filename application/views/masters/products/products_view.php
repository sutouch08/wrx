<?php $this->load->view('include/header'); ?>
<div class="row">
	<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 padding-5 padding-top-5">
		<h3 class="title"><?php echo $this->title; ?></h3>
	</div>
	<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 padding-5 text-right">
		<?php if ($this->pm->can_add) : ?>
			<button type="button" class="btn btn-white btn-success top-btn" onclick="addNew()"><i class="fa fa-plus"></i> เพิมใหม่</button>
		<?php endif; ?>
		<button type="button" class="btn btn-white btn-info top-btn" onclick="export_filter()"><i class="fa fa-file-excel-o"></i> Export</button>
	</div>
</div><!-- End Row -->
<hr class="" />
<form id="searchForm" method="post" action="<?php echo current_url(); ?>">
	<div class="row">
		<div class="col-lg-1-harf col-md-2 col-sm-2 col-xs-6 padding-5">
			<label>รหัส</label>
			<input type="text" class="form-control input-sm search" name="code" id="code" value="<?php echo $code; ?>" />
		</div>

		<div class="col-lg-1-harf col-md-2 col-sm-2 col-xs-6 padding-5">
			<label>ชื่อ</label>
			<input type="text" class="form-control input-sm search" name="name" id="name" value="<?php echo $name; ?>" />
		</div>

		<div class="col-lg-1-harf col-md-2 col-sm-2 col-xs-6 padding-5">
			<label>กลุ่ม</label>
			<select class="form-control input-sm filter" name="group" id="group">
				<option value="all">ทั้งหมด</option>
				<option value="NULL" <?php echo is_selected('NULL', $group); ?>>ไม่ระบุ</option>
				<?php echo select_product_group($group); ?>
			</select>
		</div>

		<div class="col-lg-1-harf col-md-2 col-sm-2 col-xs-6 padding-5">
			<label>กลุ่มหลัก</label>
			<select class="form-control input-sm filter" name="main_group" id="main_group">
				<option value="all">ทั้งหมด</option>
				<option value="NULL" <?php echo is_selected('NULL', $main_group); ?>>ไม่ระบุ</option>
				<?php echo select_product_main_group($main_group); ?>
			</select>
		</div>

		<div class="col-lg-1-harf col-md-2 col-sm-2 col-xs-6 padding-5">
			<label>กลุ่มย่อย</label>
			<select class="form-control input-sm filter" name="sub_group" id="sub_group">
				<option value="all">ทั้งหมด</option>
				<option value="NULL" <?php echo is_selected('NULL', $sub_group); ?>>ไม่ระบุ</option>
				<?php echo select_product_sub_group($sub_group); ?>
			</select>
		</div>

		<div class="col-lg-1-harf col-md-2 col-sm-2 col-xs-6 padding-5">
			<label>หมวดหมู่</label>
			<select class="form-control input-sm filter" name="category" id="category">
				<option value="all">ทั้งหมด</option>
				<option value="NULL" <?php echo is_selected('NULL', $category); ?>>ไม่ระบุ</option>
				<?php echo select_product_category($category); ?>
			</select>
		</div>

		<div class="col-lg-1-harf col-md-2 col-sm-2 col-xs-6 padding-5">
			<label>ประเภท</label>
			<select class="form-control input-sm filter" name="kind" id="kind">
				<option value="all">ทั้งหมด</option>
				<option value="NULL" <?php echo is_selected('NULL', $kind); ?>>ไม่ระบุ</option>
				<?php echo select_product_kind($kind); ?>
			</select>
		</div>

		<div class="col-lg-1-harf col-md-2 col-sm-2 col-xs-6 padding-5">
			<label>ชนิด</label>
			<select class="form-control input-sm filter" name="type" id="type">
				<option value="all">ทั้งหมด</option>
				<option value="NULL" <?php echo is_selected('NULL', $type); ?>>ไม่ระบุ</option>
				<?php echo select_product_type($type); ?>
			</select>
		</div>

		<div class="col-lg-1-harf col-md-2 col-sm-2 col-xs-6 padding-5">
			<label>ยี่ห้อ</label>
			<select class="form-control input-sm filter" name="brand" id="brand">
				<option value="all">ทั้งหมด</option>
				<option value="NULL" <?php echo is_selected('NULL', $brand); ?>>ไม่ระบุ</option>
				<?php echo select_product_brand($brand); ?>
			</select>
		</div>

		<div class="col-lg-1-harf col-md-2 col-sm-2 col-xs-6 padding-5">
			<label>คอลเล็คชั่น</label>
			<select class="form-control input-sm filter" name="collection" id="collection">
				<option value="all">ทั้งหมด</option>
				<option value="NULL" <?php echo is_selected('NULL', $collection); ?>>ไม่ระบุ</option>
				<?php echo select_all_collection($collection); ?>
			</select>
		</div>

		<div class="col-lg-1 col-md-2 col-sm-2 col-xs-6 padding-5">
			<label>ปี</label>
			<select class="form-control input-sm filter" name="year" id="year">
				<option value="all">ทั้งหมด</option>
				<option value="0000" <?php echo is_selected('0000', $year); ?>>0000</option>
				<?php echo select_years($year); ?>
			</select>
		</div>

		<div class="col-lg-1 col-md-2 col-sm-2 col-xs-3 padding-5">
			<label>ขาย</label>
			<select class="form-control input-sm filter" name="sell" id="sell">
				<option value="all">ทั้งหมด</option>
				<option value="1" <?php echo is_selected('1', $sell); ?>>Yes</option>
				<option value="0" <?php echo is_selected('0', $sell); ?>>No</option>
			</select>
		</div>

		<div class="col-lg-1 col-md-2 col-sm-2 col-xs-3 padding-5">
			<label>Active</label>
			<select class="form-control input-sm filter" name="active" id="active">
				<option value="all">ทั้งหมด</option>
				<option value="1" <?php echo is_selected('1', $active); ?>>Yes</option>
				<option value="0" <?php echo is_selected('0', $active); ?>>No</option>
			</select>
		</div>

		<div class="col-lg-1-harf col-md-1-harf col-sm-1-harf col-xs-3 padding-5">
			<label class="not-show">buton</label>
			<button type="submit" class="btn btn-xs btn-primary btn-block"><i class="fa fa-search"></i> Search</button>
		</div>
		<div class="col-lg-1-harf col-md-1-harf col-sm-1-harf col-xs-3 padding-5">
			<label class="not-show">buton</label>
			<button type="button" class="btn btn-xs btn-warning btn-block" onclick="clearFilter()"><i class="fa fa-retweet"></i> Reset</button>
		</div>
		<input type="hidden" name="search" value="1" />
	</div>
</form>
<hr class="margin-top-15">
<form id="exportForm" action="<?php echo $this->home; ?>/export_filter" method="post">
	<input type="hidden" name="data" id="data">
	<input type="hidden" name="token" id="token">
</form>
<?php echo $this->pagination->create_links(); ?>
<div class="row">
	<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 padding-5 table-responsive" style="max-height:900px; min-height:400px; overflow:auto;">
		<table class="table table-striped table-bordered table-hover tableFixHead" style="min-width:2100px;">
			<thead>
				<tr class="font-size-11">
					<th class="fix-width-100 fix-header"></th>
					<th class="fix-width-50 middle text-center fix-header">#</th>
					<th class="fix-width-60 middle text-center fix-header">รูปภาพ</th>
					<th class="min-width-200 middle text-center fix-header">รหัส</th>
					<th class="fix-width-80 middle text-center fix-header">ราคา</th>
					<th class="fix-width-150 middle text-center fix-header">กลุ่ม</th>
					<th class="fix-width-150 middle text-center fix-header">กลุ่มหลัก</th>
					<th class="fix-width-150 middle text-center fix-header">กลุ่มย่อย</th>
					<th class="fix-width-150 middle text-center fix-header">หมวดหมู่</th>
					<th class="fix-width-150 middle text-center fix-header">ประเภท</th>
					<th class="fix-width-150 middle text-center fix-header">ชนิด</th>
					<th class="fix-width-150 middle text-center fix-header">ยี่ห้อ</th>
					<th class="fix-width-150 middle text-center fix-header">คอลเล็คชั่น</th>
					<th class="fix-width-80 middle text-center fix-header">ปี</th>
					<th class="fix-width-50 middle text-center fix-header">ขาย</th>
					<th class="fix-width-60 middle text-center fix-header">Active</th>
					<th class="fix-width-200 middle text-center fix-header">รหัสเก่า</th>
				</tr>
			</thead>
			<tbody>
				<?php if (!empty($data)) : ?>
					<?php $no = $this->uri->segment($this->segment) + 1; ?>
					<?php foreach ($data as $rs) : ?>
						<tr class="font-size-11" id="row-<?php echo $no; ?>">
							<td class="middle text-center">
								<button type="button" class="btn btn-minier btn-info" onclick="sendToSap('<?php echo $rs->code; ?>')"><i class="fa fa-send"></i></button>
								<?php if ($this->pm->can_edit) : ?>
									<button type="button" class="btn btn-minier btn-warning" onclick="getEdit('<?php echo $rs->id; ?>')"><i class="fa fa-pencil"></i></button>
								<?php endif; ?>
								<?php if ($this->pm->can_delete) : ?>
									<button type="button" class="btn btn-minier btn-danger" onclick="getDelete('<?php echo $rs->code; ?>', '<?php echo $rs->name; ?>', <?php echo $no; ?>)"><i class="fa fa-trash"></i></button>
								<?php endif; ?>								
							</td>
							<td class="middle text-center"><?php echo number($no); ?></td>
							<td class="middle text-center">
								<img src="<?php echo get_cover_image($rs->code, 'mini'); ?>" width="40" />
							</td>
							<td class="middle"> <?php echo $rs->code; ?></td>
							<td class="middle text-right"><?php echo number($rs->price, 2); ?></td>
							<td class="middle text-center"><?php echo $rs->group; ?></td>
							<td class="middle text-center"><?php echo $rs->main_group; ?></td>
							<td class="middle text-center"><?php echo $rs->sub_group; ?></td>
							<td class="middle text-center"><?php echo $rs->category; ?></td>
							<td class="middle text-center"><?php echo $rs->kind; ?></td>
							<td class="middle text-center"><?php echo $rs->type; ?></td>
							<td class="middle text-center"><?php echo $rs->brand; ?></td>
							<td class="middle text-center"><?php echo $rs->collection; ?></td>
							<td class="middle text-center"><?php echo $rs->year; ?></td>
							<td class="middle text-center"><?php echo is_active($rs->sell); ?></td>
							<td class="middle text-center"><?php echo is_active($rs->active); ?></td>
							<td class="middle"><?php echo $rs->old_code; ?></td>
						</tr>
						<?php $no++; ?>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
</div>

<?php if(! $this->agent->is_mobile()) : ?>
	<script>
		$('#group').select2();
		$('#main_group').select2();
		$('#sub_group').select2();
		$('#category').select2();
		$('#kind').select2();
		$('#type').select2();
		$('#brand').select2();
		$('#collection').select2();
	</script>
<?php endif; ?>
<script src="<?php echo base_url(); ?>scripts/masters/products.js?v=<?php echo date('Ymd'); ?>"></script>

<?php $this->load->view('include/footer'); ?>