<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Temp_receive_transform extends PS_Controller
{
  public $menu_code = 'TERTCK';
	public $menu_group_code = 'TE';
  public $menu_sub_group_code = 'TEADJUST';
	public $title = 'ตรวจสอบ Goods Receipt';
  
  public function __construct()
  {
    parent::__construct();
    $this->home = base_url().'inventory/temp_receive_transform';
    $this->load->model('inventory/temp_receive_transform_model');
  }


  public function index()
  {
    $filter = array(
      'code' => get_filter('code', 'temp_receive_code', ''),
      'from_date' => get_filter('from_date', 'temp_receive_from_date', ''),
      'to_date' => get_filter('to_date', 'temp_receive_to_date', ''),
      'status' => get_filter('status', 'temp_receive_status', 'all')
    );

		//--- แสดงผลกี่รายการต่อหน้า
		$perpage = get_rows();		
		$segment  = 4; //-- url segment
		$rows = $this->temp_receive_transform_model->count_rows($filter);
		$filter['orders'] = $this->temp_receive_transform_model->get_list($filter, $perpage, $this->uri->segment($segment));
		$init = pagination_config($this->home.'/index/', $rows, $perpage, $segment);
		$this->pagination->initialize($init);
    $this->load->view('inventory/temp_receive_transform/temp_list', $filter);
  }


  public function get_detail($docEntry)
  {
    $this->load->model('stock/stock_model');
    $detail = $this->temp_receive_transform_model->get_detail($docEntry);
    $code = "";

    if(!empty($detail))
    {
      foreach($detail as $rs)
      {
        $rs->onhand = $this->stock_model->get_stock_zone($rs->BinCode, $rs->ItemCode);
        $code = $rs->U_ECOMNO;
      }
    }

    $ds['details'] = $detail;
    $ds['code'] = $code;
    $this->load->view('inventory/temp_receive_transform/temp_detail', $ds);
  }

  function delete_temp($id)
  {
    $sc = TRUE;
    $doc = $this->temp_receive_transform_model->get($id);

    if (!empty($doc))
    {
      if ($doc->F_Sap === 'N' or $doc->F_Sap == NULL)
      {
        $this->mc->trans_begin();

        if(! $this->temp_receive_transform_model->delete_temp_details($id))
        {
          $sc = FALSE;
          $this->error = "Delete Temp details failed";
        }

        if($sc === TRUE && ! $this->temp_receive_transform_model->delete_temp($id))
        {
          $sc = FALSE;
          $this->error = "Delete Temp failed";
        }

        if($sc === TRUE)
        {
          $this->mc->trans_commit();
        }
        else
        {
          $this->mc->trans_rollback();
        }
      }
      else 
      {
        $sc = FALSE;
        $this->error = "Temp already imported to SAP not allow to delete";
      }
    }
    
    $this->_response($sc);
  }

  public function clear_filter()
  {
    $filter = array(
      'temp_receive_code',
      'temp_receive_from_date',
      'temp_receive_to_date',
      'temp_receive_status'
    );

    return clear_filter($filter);    
  }

}
