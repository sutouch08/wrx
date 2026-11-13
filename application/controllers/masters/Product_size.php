<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_size extends PS_Controller
{
  public $menu_code = 'DBPDSI';
	public $menu_group_code = 'DB';
  public $menu_sub_group_code = 'PRODUCT';
	public $title = 'เพิ่ม/แก้ไข ไซส์';
  public $segment = 4;

  public function __construct()
  {
    parent::__construct();
    $this->home = base_url().'masters/product_size';
    $this->load->model('masters/product_size_model');
  }

  public function index()
  {
    $filter = array(
      'code' => get_filter('code', 'size_code', ''),
      'name' => get_filter('name', 'size_name', '')
    );

    if($this->input->post('search'))
    {
      redirect($this->home);
      exit;
    }
		
		$perpage = get_rows();
    $rows = $this->product_size_model->count_rows($filter);
    $filter['data'] = $this->product_size_model->get_list($filter, $perpage, $this->uri->segment($this->segment));

    if (! empty($filter['data']))
    {
      foreach ($filter['data'] as $rs)
      {
        $rs->member = $this->product_size_model->count_members($rs->code);
      }
    }

    $init = pagination_config($this->home . '/index/', $rows, $perpage, $this->segment);
    $this->pagination->initialize($init);
    $this->load->view('masters/product_size/product_size_list', $filter);
  }

  public function add_new()
  {
    $this->load->view('masters/product_size/product_size_add_view');    
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
    $pos = trim($this->input->post('position') ?? 1);

    if($code == '' || $name == '')
    {
      echo get_error('required');
      exit;
    }

    if($this->product_size_model->is_exists($code))
    {
      echo get_error('exists', $code);
      exit;
    }

    if($this->product_size_model->is_exists_name($name))
    {
      echo get_error('exists', $name);
      exit;
    }

    $ds = array(
      'code' => $code,
      'name' => $name,
      'position' => $pos
    );

    if( ! $this->product_size_model->add($ds))
    {
      echo get_error('insert');
      exit;
    }

    $this->export_to_sap($code);

    echo 'success';
  }

  public function edit($id)
  {
    $co = $this->product_size_model->get_by_id($id);
    $this->load->view('masters/product_size/product_size_edit_view', $co);
  }

  public function update()
  {    
    $id = $this->input->post('id');
    $code = trim($this->input->post('code') ?? '');
    $name = trim($this->input->post('name') ?? '');
    $pos = $this->input->post('position') ?? 1;

    if(!$this->pm->can_edit)
    {
      echo get_error('permission');
      exit;
    }

    if($code == '' || $name == '')
    {
      echo get_error('required');
      exit;
    }

    if($this->product_size_model->is_exists_name($name, $id))
    {
      echo get_error('exists', $name);
      exit;
    }

    $arr = array(
      'name' => $name,
      'position' => $pos,
      'user' => $this->_user->uname,
      'date_upd' => now()
    );

    if( ! $this->product_size_model->update_by_id($id, $arr))
    {
      echo get_error('update');
      exit;
    }

    $this->export_to_sap($code);

    echo 'success';
  }

  public function delete()
  {    
    $id = $this->input->post('id');

    if(!$this->pm->can_delete)
    {
      echo get_error('permission');
      exit;
    }

    $co = $this->product_size_model->get_by_id($id);

    if($co)
    {
      if($this->product_size_model->count_members($co->code) > 0)
      {
        echo get_error('transection');
        exit;
      }

      if( ! $this->product_size_model->delete($id))
      {
        echo get_error('delete');
        exit;
      }
    }

    echo 'success';
  }

  public function export_to_sap($code)
  {
    $co = $this->product_size_model->get($code);

    if(! $co) return FALSE;

    $arr = array(
      'Code' => $co->code,
      'Name' => $co->name,
      'UpdateDate' => sap_date(now(), TRUE),
      'Flag' => $this->product_size_model->is_sap_exists($co->code) ? 'U' : 'A'
    );

    return $this->product_size_model->add_sap_size($arr);      
  }

  public function clear_filter()
	{
    return clear_filter(['size_code', 'size_name']);
	}
}
