<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Channels extends PS_Controller
{
  public $menu_code = 'DBCHAN';
	public $menu_group_code = 'DB';
	public $title = 'ช่องทางการขาย';

  public function __construct()
  {
    parent::__construct();
    $this->home = base_url().'masters/channels';
    $this->load->model('masters/channels_model');
		$this->load->helper('channels');
  }


  public function index()
  {
    $filter = array(
      'code' => get_filter('code', 'channels_code', ''),
      'name' => get_filter('name', 'channels_name', '')
    );

    if ($this->input->post('search'))
    {
      redirect($this->home);
      exit;
    }

    $perpage = get_rows();		
		$segment = 4;
		$rows = $this->channels_model->count_rows($filter);		
		$filter['data'] = $this->channels_model->get_list($filter, $perpage, $this->uri->segment($segment));
		$init	= pagination_config($this->home.'/index/', $rows, $perpage, $segment);
		$this->pagination->initialize($init);
    $this->load->view('masters/channels/channels_view', $filter);
  }


  public function add_new()
  {
    $data['code'] = $this->session->flashdata('code');
    $data['name'] = $this->session->flashdata('name');
    $data['customer_code'] = $this->session->flashdata('customer_code');
    $data['customer_name'] = $this->session->flashdata('customer_name');
    $this->load->view('masters/channels/channels_add_view', $data);
  }


  public function add()
  {
    $code = trim($this->input->post('code') ?? '');
    $name = trim($this->input->post('name') ?? '');
    $customer_code = trim($this->input->post('customer_code') ?? '');
    $customer_name = trim($this->input->post('customer_name') ?? '');
    $is_online = $this->input->post('is_online') == 1 ? 1 : 0;

    if(empty($code) || empty($name) )
    {      
      echo get_error('required');
      return;
    }    

    if($this->channels_model->is_exists($code))
    {
      echo get_error('exists', $code);
      return;
    }

    if($this->channels_model->is_exists_name($name))
    {
      echo get_error('exists', $name);
      return;
    }

    $arr = [
      'code' => $code,
      'name' => $name,
      'customer_code' => empty($customer_code) ? NULL : $customer_code,
      'customer_name' => empty($customer_name) ? NULL : $customer_name,
      'is_online' => $is_online
    ];

    echo $this->channels_model->add($arr) ? 'success' : get_error('insert');
  }


  public function edit($code)
  {
    $data['data'] = $this->channels_model->get_channels($code);
    $this->load->view('masters/channels/channels_edit_view', $data);
  }



  public function update()
  {
    $code = trim($this->input->post('code') ?? '');
    $name = trim($this->input->post('name') ?? '');
    $customer_code = trim($this->input->post('customer_code') ?? '');
    $customer_name = trim($this->input->post('customer_name') ?? '');    
    $is_online = $this->input->post('is_online') == 1 ? 1 : 0;

    if(empty($code) || empty($name))
    {
      echo get_error('required');
      return;
    }

    if($this->channels_model->is_exists_name($name, $code))
    {
      echo get_error('exists', $name);
      return;
    }

    $arr = [
      'name' => $name,
      'customer_code' => empty($customer_code) ? NULL : $customer_code,
      'customer_name' => empty($customer_name) ? NULL : $customer_name,
      'is_online' => $is_online
    ];

    echo $this->channels_model->update($code, $arr) ? 'success' : get_error('update');    
  }


  public function delete()
  {    
    $id = $this->input->post('id');

    if(!$this->pm->can_delete)
    {
      echo get_error('permission');
      return;
    }

    $channels = $this->channels_model->get_by_id($id);

    if(empty($channels))
    {
      echo get_error('notfound');
      return;
    }

    if($this->channels_model->has_transection($channels->code))
    {
      echo get_error('transection');
      return;
    }

    echo $this->channels_model->delete($id) ? 'success' : get_error('delete');
  }

  public function toggle_online()
  {
    $sc = TRUE;
    $code = $this->input->post('code');
    if(!empty($code))
    {
      $current = $this->input->post('is_online');

      $option = empty($current) ? 1 : 0;
      $arr = array(
        'is_online' => $option
      );

      if($this->pm->can_add OR $this->pm->can_edit)
      {
        if(! $this->channels_model->update($code, $arr))
        {
          $sc = FALSE;
          $this->error = "Update failed";
        }
      }
      else
      {
        $sc = FALSE;
        $this->error = "No Permission";
      }
    }
    else
    {
      $sc = FALSE;
      $this->error = "Channels Code not found";
    }

    echo $sc === TRUE ? $option : $this->error;
  }


  public function clear_filter()
	{
		clear_filter(array('channels_code', 'channels_name'));
    echo 'done';
	}

}//--- end class
 ?>
