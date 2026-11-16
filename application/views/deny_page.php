<?php if (isset($home) && $home){  $this->load->view('include/header'); } ?>
<div class="row">
  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
    <h1 class="display-block width-100 text-center red"><i class="fa fa-exclamation-triangle"></i></h1>    
    <h4 class="display-block width-100 text-center red">You are not authorized to access this page</h4>    
  </div>
</div>
<?php $this->load->view('include/footer'); ?>
<?php exit(); ?>