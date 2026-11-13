<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Package extends PS_Controller
{
  public $menu_code = 'DBPKSI';
	public $menu_group_code = 'DB';
  public $menu_sub_group_code = 'WAREHOUSE';
	public $title = 'เพิ่ม/แก้ไข Package';
  public $segment = 4;

  public function __construct()
  {
    parent::__construct();
    $this->home = base_url().'masters/package';
    $this->load->model('masters/package_model');
  }

  public function index()
  {
    $filter = array(
      'name' => get_filter('name', 'package_name', ''),
      'type' => get_filter('type', 'package_type', 'all'),
      'active' => get_filter('active', 'package_active', 'all')
    );

    if($this->input->post('search'))
    {
      redirect($this->home);
      exit;
    }
		
		$perpage = get_rows();		
		$rows = $this->package_model->count_rows($filter);		
		$filter['data'] = $this->package_model->get_list($filter, $perpage, $this->uri->segment($this->segment));
		$init	= pagination_config($this->home.'/index/', $rows, $perpage, $this->segment);
		$this->pagination->initialize($init);
    $this->load->view('masters/package/package_list', $filter);
  }


  public function add()
  {   
    $ds = json_decode($this->input->post('data') ?? '{}');
    
    if(empty($ds))
    {
      echo get_error('required');
      exit;
    }

    $arr = array(
      'name' => $ds->name,
      'type' => $ds->type,
      'width' => $ds->w,
      'length' => $ds->l,
      'height' => $ds->h
    );

    if (! $this->package_model->add($arr))
    {
      echo get_error('insert');
      exit;
    }

    echo 'success';    
  }


  public function get_edit($id)
  {
    $sc = TRUE;
    $ds = $this->package_model->get($id);

    if(empty($ds))
    {
      $sc = FALSE;
      $this->error  = "Package not found";
    }

    $arr = array(
      'status' => $sc === TRUE ? 'success' : 'failed',
      'message' => $sc === TRUE ? 'success' : $this->error,
      'data' => $sc === TRUE ? $ds : NULL
    );

    echo json_encode($arr);
  }


  public function update()
  {    
    $ds = json_decode($this->input->post('data') ?? '{}');

    if(empty($ds))
    {
      echo get_error('required');
      exit;
    }

    $arr = array(
      'name' => $ds->name,
      'type' => $ds->type,
      'width' => $ds->w,
      'length' => $ds->l,
      'height' => $ds->h,
      'active' => $ds->active == 0 ? 0 : 1
    );

    if( ! $this->package_model->update($ds->id, $arr))
    {
      echo get_error('update');
      exit;
    }

    echo 'success';
  }


  public function delete($id)
  {
    if( ! $this->pm->can_delete)
    {
      echo get_error('permission');
      exit;
    }

    if (! $this->package_model->delete($id))
    {      
      echo get_error('delete');
      exit;
    }

    echo 'success';
  }


  public function clear_filter()
	{
    return clear_filter(['package_name', 'package_type', 'package_active']);
  }
}
