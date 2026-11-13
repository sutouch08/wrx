<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Buffer extends PS_Controller
{
  public $menu_code = 'ICCKBF';
	public $menu_group_code = 'IC';
  public $menu_sub_group_code = 'CHECK';
	public $title = 'ตรวจสอบ BUFFER';
  
  public function __construct()
  {
    parent::__construct();
    $this->home = base_url().'inventory/buffer';
    $this->load->model('inventory/buffer_model');
  }


  public function index()
  {
    $filter = array(
      'order_code' => get_filter('order_code', 'order_code', ''),
      'zone_code' => get_filter('zone_code', 'zone_code', ''),
      'pd_code' => get_filter('pd_code', 'pd_code'),
      'from_date' => get_filter('from_date', 'from_date', ''),
      'to_date' => get_filter('to_date', 'to_date', '')
    );

		if($this->input->post('search'))
		{
			redirect($this->home);
			exit;
		}
		
		$perpage = get_rows();		
		$segment  = 4; //-- url segment
		$rows = $this->buffer_model->count_rows($filter);		
		$filter['data'] = $this->buffer_model->get_data($filter, $perpage, $this->uri->segment($segment));
		$init = pagination_config($this->home.'/index/', $rows, $perpage, $segment);
		$this->pagination->initialize($init);
    $this->load->view('inventory/buffer/buffer_view', $filter);
  }

	public function delete_buffer()
	{
		$sc = TRUE;
		$id = trim($this->input->post('id') ?? '');

		if(!empty($id))
		{
			$rs = $this->buffer_model->delete($id);

			if(!$rs)
			{
				$sc = FALSE;
				$this->error = "ลบรายการไม่สำเร็จ";
			}
		}
		else
		{
			$sc = FALSE;
			$this->error = "Missing required parameter : id";
		}

		echo $sc === TRUE ? 'success' : $this->error;
	}

  function clear_filter(){
    $filter = array('order_code', 'pd_code', 'zone_code', 'from_date', 'to_date');
    return clear_filter($filter);
  }
}
