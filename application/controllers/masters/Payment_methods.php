<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Payment_methods extends PS_Controller
{
  public $menu_code = 'DBPAYM';
	public $menu_group_code = 'DB';
	public $title = 'Payment channels';
  public $segment = 4;

  public function __construct()
  {
    parent::__construct();
    $this->home = base_url().'masters/payment_methods';
    $this->load->model('masters/payment_methods_model');
    $this->load->helper('payment_method');
  }


  public function index()
  {
    $filter = array(
      'code' => get_filter('code', 'payment_code', ''),
      'name' => get_filter('name', 'payment_name', ''),
      'term' => get_filter('term', 'payment_term', 'all'),
      'role' => get_filter('role', 'payment_role', 'all')
    );

		if($this->input->post('search'))
		{
			redirect($this->home);
      exit;
		}
		
		$perpage = get_rows();
		$rows = $this->payment_methods_model->count_rows($filter);		
		$filter['data'] = $this->payment_methods_model->get_list($filter, $perpage, $this->uri->segment($this->segment));
		$init	= pagination_config($this->home.'/index/', $rows, $perpage, $this->segment);
		$this->pagination->initialize($init);
    $this->load->view('masters/payment_methods/payment_methods_list', $filter);
  }


  public function add_new()
  {
    $this->load->view('masters/payment_methods/payment_methods_add');
  }


  public function add()
  {
    if(!$this->pm->can_add)
    {
      echo get_error('permission');
      exit;
    }

    $code = trim($this->input->post('code') ?? '');
    $name = trim($this->input->post('name') ?? '');
    $role = $this->input->post('role') ?? '1';

    if($code === '' || $name === '')
    {
      echo get_error('required');
      exit;
    }

    if($this->payment_methods_model->is_exists($code))
    {
      echo get_error('exists', $code);
      exit;
    }

    if($this->payment_methods_model->is_exists_name($name))
    {
      echo get_error('exists', $name);
      exit;
    }

    $arr = array(
      'code' => $code,
      'name' => $name,
      'role' => $role,
      'has_term' => ($role == 4 OR $role == 5) ? 1 : 0
    );

    if( ! $this->payment_methods_model->add($arr))
    {
      echo get_error('insert');
      exit;
    }

    echo 'success';    
  }


  public function edit($code)
  {
    $data = $this->payment_methods_model->get_payment_methods($code);
    $this->load->view('masters/payment_methods/payment_methods_edit', $data);
  }


  public function update()
  {
    if(!$this->pm->can_edit)
    {
      echo get_error('permission');
      exit;
    }

    $id = $this->input->post('id');
    $code = trim($this->input->post('code') ?? '');
    $name = trim($this->input->post('name') ?? '');
    $role = $this->input->post('role') ?? '1';

    if($code === '' || $name === '')
    {
      echo get_error('required');
      exit;
    }

    if($this->payment_methods_model->is_exists_name($name, $id))
    {
      echo get_error('exists', $name);
      exit;
    }

    $arr = array(
      'name' => $name,
      'role' => $role,
      'has_term' => ($role == 4 OR $role == 5) ? 1 : 0
    );

    if( ! $this->payment_methods_model->update_by_id($id, $arr))
    {
      echo get_error('update');
      exit;
    }
    
    echo 'success';    
  }


  public function delete()
  {
    if(!$this->pm->can_delete)
    {
      set_error('permission');
      redirect($this->home);
    }

    $code = $this->input->post('code');

    if($this->payment_methods_model->has_transection($code))
    {
      echo get_error('transection');
      exit;
    }

    if(! $this->payment_methods_model->delete($code))
    {
      echo get_error('delete');
      exit;
    }

    echo 'success';
  }

  //--- เช็คว่าการชำระเงินเป็นแบบเครดิตหรือไม่
  public function is_credit_payment($code)
  {
    //---- ตรวจสอบว่าเป็นเครดิตหรือไม่
    echo $this->payment_methods_model->has_term($code) ? 1 : 0;
  }

  public function clear_filter()
	{
		return clear_filter(['payment_code', 'payment_name', 'payment_term', 'payment_role']);		
	}
}
