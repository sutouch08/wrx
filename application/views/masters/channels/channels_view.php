<?php $this->load->view('include/header'); ?>
<div class="row">
	<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 padding-5 padding-top-5">
		<h3 class="title"><?php echo $this->title; ?></h3>
	</div>
	<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 padding-5 text-right">
		<?php if ($this->pm->can_add) : ?>
			<button type="button" class="btn btn-white btn-success top-btn" onclick="addNew()"><i class="fa fa-plus"></i> Add New</button>
		<?php endif; ?>
	</div>
</div>
<hr class="" />
<form id="searchForm" method="post" action="<?php echo current_url(); ?>">
	<div class="row">
		<div class="col-lg-2 col-md-2 col-sm-2 col-xs-6 padding-5">
			<label>รหัส</label>
			<input type="text" class="form-control input-sm" name="code" id="code" value="<?php echo $code; ?>" />
		</div>

		<div class="col-lg-2 col-md-2 col-sm-2 col-xs-6 padding-5">
			<label>ชื่อ</label>
			<input type="text" class="form-control input-sm" name="name" id="name" value="<?php echo $name; ?>" />
		</div>

		<div class="col-lg-1-harf col-md-1-harf col-sm-1-harf col-xs-6 padding-5">
			<label class="display-block not-show">buton</label>
			<button type="submit" class="btn btn-xs btn-primary btn-block"><i class="fa fa-search"></i> Search</button>
		</div>
		<div class="col-lg-1-harf col-md-1-harf col-sm-1-harf col-xs-6 padding-5">
			<label class="display-block not-show">buton</label>
			<button type="button" class="btn btn-xs btn-warning btn-block" onclick="clearFilter()"><i class="fa fa-retweet"></i> Reset</button>
		</div>
	</div>
	<input type="hidden" name="search" value="1" />
</form>
<hr class="margin-top-15">
<?php echo $this->pagination->create_links(); ?>

<div class="row">
	<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 padding-5 table-responsive">
		<table class="table table-striped table-medium border-1" style="min-width:840px;">
			<thead>
				<tr>
					<th class="fix-width-60"></th>
					<th class="fix-width-40 middle text-center">#</th>
					<th class="fix-width-100 middle">รหัส</th>
					<th class="fix-width-200 middle">ชื่อ</th>
					<th class="fix-width-60 middle text-center">Online</th>
					<th class="fix-width-60 middle text-center">Default</th>
					<th class="min-width-300 middle">ลูกค้าเริ่มต้น</th>
				</tr>
			</thead>
			<tbody>
				<?php if (!empty($data)) : ?>
					<?php $no = $this->uri->segment(4) + 1; ?>
					<?php foreach ($data as $rs) : ?>
						<tr id="row-<?php echo $rs->id; ?>">
							<td class="middle">
								<?php if ($this->pm->can_edit) : ?>
									<button type="button" class="btn btn-minier btn-warning" onclick="edit('<?php echo $rs->code; ?>')">
										<i class="fa fa-pencil"></i>
									</button>
								<?php endif; ?>
								<?php if ($this->pm->can_delete) : ?>
									<button type="button" class="btn btn-minier btn-danger" onclick="confirmDelete('<?php echo $rs->id; ?>', '<?php echo $rs->name; ?>')">
										<i class="fa fa-trash"></i>
									</button>
								<?php endif; ?>
							</td>
							<td class="middle text-center no"><?php echo $no; ?></td>
							<td class="middle"><?php echo $rs->code; ?></td>
							<td class="middle"><?php echo $rs->name; ?></td>
							<td class="middle text-center">
								<?php if ($this->pm->can_edit) : ?>
									<input type="hidden" id="online-<?php echo $rs->code; ?>" value="<?php echo $rs->is_online; ?>" />
									<a href="javascript:void(0)" id="online-label-<?php echo $rs->code; ?>" onclick="toggleOnline('<?php echo $rs->code; ?>')">
										<?php if ($rs->is_online) : ?>
											<i class="fa fa-check green"></i>
										<?php else : ?>
											<i class="fa fa-times"></i>
										<?php endif; ?>
									</a>
								<?php else : ?>
									<?php if ($rs->is_online) : ?>
										<i class="fa fa-check green"></i>
									<?php else : ?>
										<i class="fa fa-times"></i>
									<?php endif; ?>
								<?php endif; ?>
							</td>
							<td class="middle text-center">
								<?php if ($rs->is_default) : ?>
									<i class="fa fa-check green"></i>
								<?php else : ?>
									<i class="fa fa-times"></i>
								<?php endif; ?>
							</td>
							<td class="middle"><?php echo $rs->customer_name; ?></td>
						</tr>
						<?php $no++; ?>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
</div>

<script src="<?php echo base_url(); ?>scripts/masters/channels.js"></script>

<?php $this->load->view('include/footer'); ?>