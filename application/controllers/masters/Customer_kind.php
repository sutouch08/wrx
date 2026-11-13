<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Customer_kind extends PS_Controller
{
  public $menu_code = 'DBCKIN';
	public $menu_group_code = 'DB';
  public $menu_sub_group_code = 'CUSTOMER';
	public $title = 'เพิ่ม/แก้ไข ประเภทลูกค้า';

  public function __construct()
  {
    parent::__construct();
    $this->home = base_url().'masters/customer_kind';
    $this->load->model('masters/customer_kind_model');
  }


  public function index()
  {
    $filter = array(
      'code' => get_filter('code', 'kind_code', ''),
      'name' => get_filter('name', 'kind_name', '')
    );

    if ($this->input->post('search'))
    {
      redirect($this->home);
      exit;
    }

    $perpage = get_rows();
    $segment = 4;
    $rows = $this->customer_kind_model->count_rows($filter);
    $filter['data'] = $this->customer_kind_model->get_list($filter, $perpage, $this->uri->segment($segment));
    $init  = pagination_config($this->home . '/index/', $rows, $perpage, $segment);
    $this->pagination->initialize($init);
    $this->load->view('masters/customer_kind/customer_kind_view', $filter);
  }


  public function add_new()
  {
    $this->load->view('masters/customer_kind/customer_kind_add_view');
  }


  public function add()
  {
    $code = trim($this->input->post('code') ?? '');
    $name = trim($this->input->post('name') ?? '');

    if (empty($code) || empty($name))
    {
      echo get_error('required');
      return;
    }

    if ($this->customer_kind_model->is_exists($code))
    {
      echo get_error('exists', $code);
      return;
    }

    if ($this->customer_kind_model->is_exists_name($name))
    {
      echo get_error('exists', $name);
      return;
    }

    $arr = array(
      'code' => $code,
      'name' => $name
    );

    if (! $this->customer_kind_model->add($arr))
    {
      echo get_error('add');
      return;
    }

    echo 'success';
  }


  public function edit($code)
  {
    $ds = $this->customer_kind_model->get($code);
    $this->load->view('masters/customer_kind/customer_kind_edit_view', $ds);
  }


  public function update()
  {
    $code = trim($this->input->post('code') ?? '');
    $name = trim($this->input->post('name') ?? '');

    if (empty($code) || empty($name))
    {
      echo get_error('required');
      return;
    }

    if ($this->customer_kind_model->is_exists_name($name, $code))
    {
      echo get_error('exists', $name);
      return;
    }

    $arr = array(
      'name' => $name
    );

    if (! $this->customer_kind_model->update($code, $arr))
    {
      echo get_error('update');
      return;
    }

    echo 'success';
  }


  public function delete()
  {
    if (! $this->pm->can_delete)
    {
      echo get_error('permission');
      return;
    }

    $id = $this->input->post('id');

    if (!$id)
    {
      echo get_error('required');
      return;
    }

    $rs = $this->customer_kind_model->get_by_id($id);

    if (! $rs)
    {
      echo get_error('notfound');
      return;
    }

    if ($this->customer_kind_model->has_transection($rs->code))
    {
      echo get_error('transection');
      return;
    }

    if (! $this->customer_kind_model->delete($rs->code))
    {
      echo get_error('delete');
      return;
    }

    echo 'success';
  }


  public function clear_filter()
  {
    return clear_filter(['kind_code', 'kind_name']);
  }
}
