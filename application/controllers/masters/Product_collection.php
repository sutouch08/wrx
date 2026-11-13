<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_collection extends PS_Controller
{
  public $menu_code = 'DBPDCT';
	public $menu_group_code = 'DB';
  public $menu_sub_group_code = 'PRODUCT';
	public $title = 'เพิ่ม/แก้ไข คอลเล็คชั่น';
  public $segment = 4;

  public function __construct()
  {
    parent::__construct();
    $this->home = base_url().'masters/product_collection';
    $this->load->model('masters/product_collection_model');
  }

  public function index()
  {
    $filter = array(
      'code' => get_filter('code', 'collection_code', ''),
      'name' => get_filter('name', 'collection_name', ''),
      'active' => get_filter('active', 'collection_active', 'all')
    );

    if($this->input->post('search'))
    {
      redirect($this->home);
      exit;
    }

		$perpage = get_rows();		
		$rows = $this->product_collection_model->count_rows($filter);	
		$filter['data'] = $this->product_collection_model->get_list($filter, $perpage, $this->uri->segment($this->segment));    

    if(!empty($filter['data']))
    {
      foreach($filter['data'] as $rs)
      {
        $rs->member = $this->product_collection_model->count_members($rs->code);
      }
    }

		$init	= pagination_config($this->home.'/index/', $rows, $perpage, $this->segment);
		$this->pagination->initialize($init);
    $this->load->view('masters/product_collection/product_collection_list', $filter);
  }


  public function add_new()
  {
    $this->load->view('masters/product_collection/product_collection_add');
  }

  public function add()
  {
    $code = trim($this->input->post('code') ?? '');
    $name = trim($this->input->post('name') ?? '');
    $active = $this->input->post('active') == 1 ? 1 : 0;

    if(!$this->pm->can_add)
    {
      echo get_error('permission');
      exit;
    }

    if(empty($code) || empty($name))
    {
      echo get_error('required');
      exit;
    }

    if($this->product_collection_model->is_exists($code))
    {
      echo get_error('exists', $code);
      exit;
    }

    if($this->product_collection_model->is_exists_name($name))
    {
      echo get_error('exists', $name);
      exit;
    }
    
    $ds = array(
      'code' => $code,
      'name' => $name,
      'active' => $active,
      'update_user' => $this->_user->uname
    );

    if(! $this->product_collection_model->add($ds))
    {
      echo get_error('insert');
      exit;
    }
    
    echo 'success';
  }

  public function edit($id)
  {
    $data = $this->product_collection_model->get_by_id($id);
    $this->load->view('masters/product_collection/product_collection_edit', $data);
  }

  public function update()
  {    
    $id = $this->input->post('id');
    $name = trim($this->input->post('name') ?? '');
    $active = $this->input->post('active') == 1 ? 1 : 0;

    if(!$this->pm->can_edit)
    {
      echo get_error('permission');
      exit;
    }

    if(empty($name))
    {
      echo get_error('required');
      exit;
    }

    if($this->product_collection_model->is_exists_name($name, $id))
    {
      echo get_error('exists', $name);
      exit;
    }

    $arr = array(
      'name' => $name,
      'active' => $active,
      'date_upd' => now(),
      'update_user' => $this->_user->uname
    );

    if( ! $this->product_collection_model->update($id, $arr))
    {
      echo get_error('update');
      exit;
    }

    echo 'success';
  }

  public function delete()
  {
    $id = $this->input->post('id');

    if( ! $this->pm->can_delete)
    {
      echo get_error('permission');
      exit;
    }

    $ds = $this->product_collection_model->get_by_id($id);

    if($ds)
    {
      if($this->product_collection_model->count_members($ds->code) > 0)
      {
        echo get_error('transection');
        exit;
      }

      if( ! $this->product_collection_model->delete($id))
      {
        echo get_error('delete');
        exit;
      }
    }

    echo 'success';
    exit;    
  }

  public function clear_filter()
	{
		return clear_filter(array('collection_code', 'collection_name', 'collection_active'));
	}
}