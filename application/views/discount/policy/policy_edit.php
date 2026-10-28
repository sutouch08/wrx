<?php $this->load->view('include/header'); ?>
<div class="row">
  <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 padding-5 padding-top-5">
    <h3 class="title"><?php echo $this->title; ?></h3>
  </div>
  <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 padding-5 text-right">
    <button type="button" class="btn btn-white btn-default top-btn" onclick="goBack()"><i class="fa fa-arrow-left"></i> กลับ</button>
    <?php if ($this->pm->can_add || $this->pm->can_edit) : ?>
      <button type="button" class="btn btn-white btn-primary top-btn" onclick="getActiveRuleList()"><i class="fa fa-plus"></i> เพิ่มเงื่อนไข</button>
    <?php endif; ?>
  </div>
</div><!-- End Row -->
<hr class="padding-5" />

<?php $this->load->view('discount/policy/policy_edit_header'); ?>
<?php $this->load->view('discount/policy/policy_edit_rules'); ?>

<div class="modal fade" id="rule-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog" style="width:800px;">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title">เลือกกฎส่วนลด</h4>
      </div>
      <div class="modal-body" id="rule-body">
        <div class="row">
          <div class="scrollbar-inner">
            <div class="col-sm-12" style="width:800px; max-height:400px;" id="result">
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-default btn-50" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-sm btn-primary" id="btn-add-rule" onclick="addRule()"><i class="fa fa-plus"></i> Add to Policy</button>
      </div>
    </div>
  </div>
</div>

<script id="rule-template" type="text/x-handlebars-template">
  <table class="table table-medium border-1 margin-bottom-0" id="myTable">
    <thead>
      <tr>
        <th class="fix-width-50 text-center">เลือก</th>
        <th class="fix-width-100">รหัส</th>
        <th class="min-width-200">ชื่อกฏ</th>
        <th class="fix-width-100">แก้ไขล่าสุด</th>
      </tr>
    </thead>
    <tbody>
      {{#each this}}
        {{#if nodata}}
          <tr><td colspan="4" class="text-center">ไม่พบรายการ</td></tr>
        {{else}}
          <tr>
            <td class="text-center">
              <input type="checkbox" class="ace chk-rule" name="ruleId[{{id_rule}}]" id="ruleId_{{id_rule}}" value="{{id_rule}}" />
              <span class="lbl"></span>
            </td>
            <td>
              <label for="ruleId_{{id_rule}}" class="padding-5">{{ruleCode}}</label>
            </td>
            <td>
              <label for="ruleId_{{id_rule}}" class="padding-5">{{ruleName}}</label>
            </td>
            <td>
              <label for="ruleId_{{id_rule}}" class="padding-5">{{date_upd}}</label>
            </td>
          </tr>
        {{/if}}
      {{/each}}
    </tbody>
  </table>
</script>

<script src="<?php echo base_url(); ?>scripts/discount/policy/policy.js"></script>
<script src="<?php echo base_url(); ?>scripts/discount/policy/policy_add.js"></script>

<?php $this->load->view('include/footer'); ?>