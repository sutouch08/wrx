<?php $this->load->view('include/header'); ?>
<div class="row">
	<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 padding-5 padding-top-5">
		<h3 class="title"><?php echo $this->title; ?></h3>
	</div>
	<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 padding-5 text-right">
		<button type="button" class="btn btn-white btn-warning top-btn" onclick="goBack()"><i class="fa fa-arrow-left"></i> Back</button>		
	</div>
</div><!-- End Row -->
<hr />
<?php
$tab1 = $tab == 'infoTab' ? 'active in' : '';
$tab2 = $tab == 'billTab' ? 'active in' : '';
$tab3 = $tab == 'shipTab' ? 'active in' : '';
?>
<div class="row">
	<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 padding-5">
		<div class="tabable">
			<ul class="nav nav-tabs">
				<li class="<?php echo $tab1; ?>"><a href="#infoTab" data-toggle="tab" onclick="changeURL('<?php echo $ds->code; ?>','infoTab')">ข้อมูลลูกค้า</a></li>
				<li class="<?php echo $tab2; ?>"><a href="#billTab" data-toggle="tab" onclick="changeURL('<?php echo $ds->code; ?>','billTab')">ที่อยู่เปิดบิล</a></li>
				<li class="<?php echo $tab3; ?>"><a href="#shipTab" data-toggle="tab" onclick="changeURL('<?php echo $ds->code; ?>','shipTab')">ที่อยู่จัดส่ง</a></li>				
			</ul>

			<div class="tab-content">
				<div class="tab-pane fade <?php echo $tab1; ?>" id="infoTab">
					<?php $this->load->view('masters/customers/customers_info'); ?>
				</div>
				<div class="tab-pane fade <?php echo $tab2; ?>" id="billTab">
					<?php $this->load->view('masters/customers/customers_bill_to'); ?>
				</div>
				<div class="tab-pane fade <?php echo $tab3; ?>" id="shipTab">
					<?php $this->load->view('masters/customers/customers_ship_to'); ?>
				</div>
			</div>
		</div>
	</div>
</div>

<script src="<?php echo base_url(); ?>scripts/masters/customers.js?v=<?php echo date('Ymd'); ?>"></script>

<?php $this->load->view('include/footer'); ?>