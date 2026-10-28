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
<hr />
<form id="searchForm" method="post" action="<?php echo current_url(); ?>">
  <div class="row">
    <div class="col-lg-2 col-md-2 col-sm-2 col-xs-4 padding-5">
      <label>Policy Number</label>
      <input type="text" class="form-control input-sm search" name="code" value="<?php echo $code; ?>" />
    </div>

    <div class="col-lg-2 col-md-2 col-sm-2 col-xs-4 padding-5">
      <label>Description</label>
      <input type="text" class="form-control input-sm search" name="name" value="<?php echo $name; ?>" />
    </div>

    <div class="col-lg-1-harf col-md-2 col-sm-2 col-xs-4 padding-5">
      <label>Status</label>
      <select class="form-control input-sm filter" name="active">
        <option value="all">All</option>
        <option value="1" <?php echo is_selected('1', $active); ?>>Active</option>
        <option value="0" <?php echo is_selected('0', $active); ?>>Inactive</option>
      </select>
    </div>

    <div class="col-lg-1 col-md-1 col-sm-1 col-xs-3 padding-5">
      <label>Start Date</label>
      <input type="text" class="form-control input-sm" name="start_date" id="start-date" value="<?php echo $start_date; ?>" />
    </div>

    <div class="col-lg-1 col-md-1 col-sm-1 col-xs-3 padding-5">
      <label>End Date</label>
      <input type="text" class="form-control input-sm" name="end_date" id="end-date" value="<?php echo $end_date; ?>" />
    </div>

    <div class="col-lg-1-harf col-md-1-harf col-sm-1-harf col-xs-3 padding-5">
      <label class="not-show">buton</label>
      <button type="submit" class="btn btn-xs btn-primary btn-block"><i class="fa fa-search"></i> Search</button>
    </div>
    <div class="col-lg-1-harf col-md-1-harf col-sm-1-harf col-xs-3 padding-5">
      <label class="not-show">buton</label>
      <button type="button" class="btn btn-xs btn-warning btn-block" onclick="clearFilter()"><i class="fa fa-retweet"></i> Reset</button>
    </div>
  </div>
  <input type="hidden" name="search" value="1" />
</form>
<hr class="padding-5 margin-top-15">
<?php echo $this->pagination->create_links(); ?>

<div class="row">
  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 padding-5 table-responsive">
    <table class="table table-striped table-medium border-1">
      <thead>
        <tr>
          <th class="fix-width-100"></th>
          <th class="fix-width-40 text-center">#</th>
          <th class="fix-width-75 text-center">Status</th>
          <th class="fix-width-100">Policy No.</th>
          <th class="min-width-250">Description</th>
          <th class="fix-width-100">Start Date</th>
          <th class="fix-width-100">End Date</th>
          <th class="fix-width-120">Last modified</th>
          <th class="fix-width-150">Modified by</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($data)) : ?>
          <?php $no = $this->uri->segment($this->segment) + 1; ?>
          <?php foreach ($data as $rs) : ?>
            <tr id="row-<?php echo $rs->id; ?>">
              <td class="middle">
                <button type="button" class="btn btn-minier btn-info" onclick="viewDetail('<?php echo $rs->code; ?>')"><i class="fa fa-eye"></i></button>
                <?php if ($this->pm->can_edit) : ?>
                  <button type="button" class="btn btn-minier btn-warning" onclick="edit('<?php echo $rs->code; ?>')"><i class="fa fa-pencil"></i></button>
                <?php endif; ?>
                <?php if ($this->pm->can_delete) : ?>
                  <button type="button" class="btn btn-minier btn-danger" onclick="confirmDelete('<?php echo $rs->id; ?>', '<?php echo addslashes($rs->code); ?>')"><i class="fa fa-trash"></i></button>
                <?php endif; ?>
              </td>
              <td class="middle text-center no"><?php echo $no; ?></td>
              <td class="middle text-center">
                <?php if ($this->pm->can_edit) : ?>
                  <label style="height: 22px;">
                    <input class="ace ace-switch ace-switch-6" type="checkbox" value="1" onchange="toggleActive(this, <?php echo $rs->id; ?>)" <?php echo $rs->active ? 'checked' : ''; ?>>
                    <span class="lbl"></span>
                  </label>
                <?php else : ?>
                  <?php echo is_active($rs->active); ?>
                <?php endif; ?>
              </td>
              <td class="middle"><?php echo $rs->code; ?></td>
              <td class="middle"><?php echo $rs->name; ?></td>
              <td class="middle"><?php echo thai_date($rs->start_date); ?></td>
              <td class="middle"><?php echo thai_date($rs->end_date); ?></td>
              <td class="middle"><?php echo thai_date($rs->date_upd, TRUE); ?></td>
              <td class="middle"><?php echo $rs->update_user; ?></td>
            </tr>
            <?php $no++; ?>
          <?php endforeach; ?>
        <?php else : ?>
          <tr>
            <td colspan="10" class="text-center">--- No content ---</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script src="<?php echo base_url(); ?>scripts/discount/policy/policy.js"></script>
<script src="<?php echo base_url(); ?>scripts/discount/policy/policy_add.js"></script>

<?php $this->load->view('include/footer'); ?>