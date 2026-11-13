<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_color extends PS_Controller
{
  public $menu_code = 'DBPDCL';
	public $menu_group_code = 'DB';
  public $menu_sub_group_code = 'PRODUCT';
	public $title = 'เพิ่ม/แก้ไข สีสินค้า';
  public $segment = 4;

  public function __construct()
  {
    parent::__construct();
    $this->home = base_url().'masters/product_color';
    $this->load->model('masters/product_color_model');
    $this->load->helper('product_color');
  }


  public function index()
  {
    $filter = array(
      'code' => get_filter('code', 'color_code', ''),
      'name' => get_filter('name', 'color_name', ''),
      'color_group' => get_filter('color_group', 'color_group', 'all'),
      'status' => get_filter('status', 'color_status', 'all')
    );

    if($this->input->post('search'))
    {
      redirect($this->home);
      exit;
    }

    $perpage = get_rows();
    $rows = $this->product_color_model->count_rows($filter);    
    $filter['data'] = $this->product_color_model->get_list($filter, $perpage, $this->uri->segment($this->segment));

    if(!empty($filter['data']))
    {
      foreach($filter['data'] as $rs)
      {
        $rs->member = $this->product_color_model->count_members($rs->code);
      }
    }

    $init  = pagination_config($this->home.'/index/', $rows, $perpage, $this->segment);
		$this->pagination->initialize($init);
    $this->load->view('masters/product_color/product_color_view', $filter);
  }


  public function set_active()
  {
    $code = $this->input->post('code');
    $active = $this->input->post('active') == 1 ? 0 :1;
    if($code)
    {
      $rs = $this->product_color_model->set_active($code, $active);
      if($rs)
      {
        $sc = "<span class=\"pointer\" onClick=\"toggleActive({$active}, '{$code}')\">";
        $sc .= is_active($active);
        $sc .= "</span>";

        echo $sc;
      }
    }
  }

  public function add_new()
  {
    $this->load->view('masters/product_color/product_color_add_view');
  }


  public function add()
  {    
    $sc = TRUE;
    $code = trim($this->input->post('code') ?? '');
    $name = trim($this->input->post('name') ?? '');
    $group_id = $this->input->post('group');

    if(empty($code) || empty($name))
    {
      echo get_error('required');
      exit;      
    }

    if($this->product_color_model->is_exists($code))
    {
      echo get_error('exists', $code);
      exit;
    }

    if($this->product_color_model->is_exists_name($name))
    {
      echo get_error('exists', $name);
      exit;
    }

    $arr = array(
      'code' => $code,
      'name' => $name,
      'id_group' => $group_id
    );

    if( ! $this->product_color_model->add($arr))
    {
      $sc = FALSE;
      set_error('insert');
    }

    if($sc === TRUE) $this->export_to_sap($code);

    $this->_response($sc);    
  }


  public function edit($id)
  {
    $data = $this->product_color_model->get_by_id($id);
    $this->load->view('masters/product_color/product_color_edit_view', $data);
  }


  public function update()
  {    
    $sc = TRUE;
    $id = $this->input->post('id');
    $code = trim($this->input->post('code') ?? '');
    $name = trim($this->input->post('name') ?? '');
    $group_id = $this->input->post('group');

    if(empty($code) || empty($name) || empty($id))
    {
      echo get_error('required');
      exit;
    }

    if($this->product_color_model->is_exists_name($name, $id))
    {
      echo get_error('exists', $name);
      exit;
    }

    $arr = array(
      'name' => $name,
      'id_group' => $group_id
    );

    if( ! $this->product_color_model->update_by_id($id, $arr))
    {
      $sc = FALSE;
      set_error('update');
    }

    if($sc === TRUE) $this->export_to_sap($code);

    $this->_response($sc);    
  }


  public function delete()
  {    
    $id = $this->input->post('id');

    if( ! $this->pm->can_delete)
    {
      echo get_error('permission');
      exit;
    }
    
    $color = $this->product_color_model->get_by_id($id);

    if( ! empty($color))
    {
      if($this->product_color_model->count_members($color->code) > 0)
      {        
        echo get_error('transection');
        exit;
      }

      if( ! $this->product_color_model->delete_by_id($id))
      {
        echo get_error('delete');
        exit;
      }
    }
    
    echo 'success';    
  }


  public function export_to_sap($code)
  {
    $rs = $this->product_color_model->get($code);

    if(!empty($rs))
    {
      $ext = $this->product_color_model->is_sap_exists($code);

      $arr = array(
        'Code' => $rs->code,
        'Name' => $rs->name,
        'UpdateDate' => sap_date(now(), TRUE),
        'Flag' => $ext ? 'U' : 'A'
      );      

      return $this->product_color_model->add_sap_color($arr);
    }

    return FALSE;
  }


  public function clear_filter()
	{
    $filter = array('color_code', 'color_name', 'color_group', 'color_status');
    clear_filter($filter);
	}
}
