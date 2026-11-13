<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_group extends PS_Controller
{
  public $menu_code = 'DBPDGP';
	public $menu_group_code = 'DB';
  public $menu_sub_group_code = 'PRODUCT';
	public $title = 'เพิ่ม/แก้ไข กลุ่มสินค้า';
  public $segment = 4;

  public function __construct()
  {
    parent::__construct();
    $this->home = base_url().'masters/product_group';
    $this->load->model('masters/product_group_model');
  }

  public function index()
  {
    $filter = array(
      'code' => get_filter('code', 'code', ''),
      'name' => get_filter('name', 'name', '')
    );

    if ($this->input->post('search'))
    {
      redirect($this->home);
      exit;
    }

    $perpage = get_rows();
    $rows = $this->product_group_model->count_rows($filter);
    $filter['data'] = $this->product_group_model->get_list($filter, $perpage, $this->uri->segment($this->segment));

    if (!empty($filter['data']))
    {
      foreach ($filter['data'] as $rs)
      {
        $rs->menber = $this->product_group_model->count_members($rs->code);
      }
    }

    $init  = pagination_config($this->home . '/index/', $rows, $perpage, $this->segment);
    $this->pagination->initialize($init);
    $this->load->view('masters/product_group/product_group_view', $filter);
  }

  public function add_new()
  {
    $this->load->view('masters/product_group/product_group_add_view');
  }

  public function add()
  {
    $code = trim($this->input->post('code') ?? '');
    $name = trim($this->input->post('name') ?? '');

    if (empty($code) || empty($name))
    {
      echo get_error('required');
      exit;
    }

    if ($this->product_group_model->is_exists($code))
    {
      echo get_error('exists', $code);
      exit;
    }

    if ($this->product_group_model->is_exists_name($name))
    {
      echo get_error('exists', $name);
      exit;
    }

    $arr = array(
      'code' => $code,
      'name' => $name
    );

    if (! $this->product_group_model->add($arr))
    {
      echo get_error('insert');
      exit;
    }

    echo 'success';
  }

  public function edit($code)
  {
    $ds = $this->product_group_model->get($code);
    $this->load->view('masters/product_group/product_group_edit_view', $ds);
  }

  public function update()
  {
    $code = $this->input->post('code') ?? '';
    $name = $this->input->post('name') ?? '';

    if (empty($code) || empty($name))
    {
      echo get_error('required');
      exit;
    }

    if ($this->product_group_model->is_exists_name($name, $code))
    {
      echo get_error('exists', $name);
      exit;
    }

    $arr = array('name' => $name);

    if (! $this->product_group_model->update($code, $arr))
    {
      echo get_error('update');
      exit;
    }

    echo 'success';
  }

  public function delete()
  {
    if (! $this->pm->can_delete)
    {
      echo get_error('permission');
      exit;
    }

    $code = $this->input->post('code') ?? '';

    if (empty($code))
    {
      echo get_error('required');
      exit;
    }

    if ($this->product_group_model->count_members($code) > 0)
    {
      echo get_error('transaction', $code);
      exit;
    }

    if (! $this->product_group_model->delete($code))
    {
      echo get_error('delete');
      exit;
    }

    echo 'success';
  }

  public function export_to_sap($code)
  {
    $rs = $this->product_group_model->get($code);

    if (!empty($rs))
    {
      $ext = $this->product_group_model->is_sap_exists($code);

      $arr = array(
        'Code' => $rs->code,
        'Name' => $rs->name,
        'UpdateDate' => sap_date(now(), TRUE),
        'OLDCODE' => NULL,
        'Flag' => $ext ? 'U' : 'A'
      );

      return $this->product_group_model->add_sap($arr);
    }

    return FALSE;
  }

  public function clear_filter()
  {
    return clear_filter(array('code', 'name'));
  }
}
