<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sender extends PS_Controller{
	public $menu_code = 'DBSEND'; //--- Add/Edit Users
	public $menu_group_code = 'DB';
	public $menu_sub_group_code = 'TRANSPORT'; //--- System security
	public $title = 'เพิ่ม/แก้ไข ขนส่ง';
	public $segment = 4;

  public function __construct()
  {
    parent::__construct();
    $this->home = base_url().'masters/sender';
		$this->load->model('masters/sender_model');
  }

  public function index()
  {
		$filter = array(
			'code' => get_filter('code', 'sender_code', ''),
			'name' => get_filter('name', 'sender_name', ''),
			'addr' => get_filter('addr', 'sender_addr', ''),
			'phone' => get_filter('phone', 'sender_phone', ''),
			'type' => get_filter('type', 'sender_type', 'all'),
			'listing' => get_filter('listing', 'sender_listing', 'all')
		);

		if($this->input->post('search'))
		{
			redirect($this->home);
			exit;
		}

		$perpage = get_rows();
		$rows = $this->sender_model->count_rows($filter);
		$filter['data'] = $this->sender_model->get_list($filter, $perpage, $this->uri->segment($this->segment));
		$init	= pagination_config($this->home.'/index/', $rows, $perpage, $this->segment);
		$this->pagination->initialize($init);
    $this->load->view('masters/sender/sender_view', $filter);
  }

	public function add_new()
	{
		$this->load->view('masters/sender/sender_add');
	}

	public function add()
	{		
		$code = trim($this->input->post('code') ?? '');
		$name = trim($this->input->post('name') ?? '');

		if(empty($code) || empty($name))
		{
			echo get_error('required');
			exit;
		}

		if($this->sender_model->is_exists_code($code))
		{
			echo get_error('exitst', $code);
			exit;
		}

		if($this->sender_model->is_exists_name($name))
		{
			echo get_error('exitst', $name);
			exit;
		}

		$arr = array(
			'code' => $code,
			'name' => $name,
			'address1' => get_null(trim($this->input->post('address1') ?? '')),
			'address2' => get_null(trim($this->input->post('address2') ?? '')),
			'phone' => get_null(trim($this->input->post('phone') ?? '')),
			'open' => trim($this->input->post('open') ?? ''),
			'close' => trim($this->input->post('close') ?? ''),
			'type' => trim($this->input->post('type') ?? ''),
			'show_in_list' => empty($this->input->post('show_in_list')) ? 0 : 1,
			'force_tracking' => empty($this->input->post('force_tracking')) ? 0 : 1,
			'auto_gen' => empty($this->input->post('auto_gen')) ? 0 : 1,
			'prefix' => get_null(trim($this->input->post('prefix') ?? ''))
		);

		if( ! $this->sender_model->add($arr))
		{
			echo get_error('insert');
			exit;
		}

		echo 'success';
		exit;
	}


	public function edit($id)
	{
		$rs = $this->sender_model->get($id);
		$this->load->view('masters/sender/sender_edit', $rs);
	}


	public function update($id)
	{		
		$code = trim($this->input->post('code') ?? '');
		$name = trim($this->input->post('name') ?? '');

		if(empty($code) || empty($name))
		{
			echo get_error('required');
			exit;
		}

		if($this->sender_model->is_exists_code($code, $id))
		{
			echo get_error('exitst', $code);
			exit;
		}

		if($this->sender_model->is_exists_name($name, $id))
		{
			echo get_error('exitst', $name);
			exit;
		}

		$arr = array(
			'code' => $code,
			'name' => $name,
			'address1' => get_null(trim($this->input->post('address1') ?? '')),
			'address2' => get_null(trim($this->input->post('address2') ?? '')),
			'phone' => get_null(trim($this->input->post('phone') ?? '')),
			'open' => trim($this->input->post('open') ?? ''),
			'close' => trim($this->input->post('close') ?? ''),
			'type' => trim($this->input->post('type') ?? ''),
			'show_in_list' => empty($this->input->post('show_in_list')) ? 0 : 1,
			'force_tracking' => empty($this->input->post('force_tracking')) ? 0 : 1,
			'auto_gen' => empty($this->input->post('auto_gen')) ? 0 : 1,
			'prefix' => get_null(trim($this->input->post('prefix') ?? ''))
		);

		if (! $this->sender_model->update($id, $arr))
		{
			echo get_error('update');
			exit;
		}

		echo 'success';
		exit;	
	}


	public function delete()
	{
		if(! $this->pm->can_delete)
		{
			echo get_error('permission');
			exit;
		}

		$id = $this->input->post('id');
		
		if(empty($id))
		{
			echo get_error('required');
			exit;
		}

		if( ! $this->sender_model->delete($id))
		{
			echo get_error('delete');
			exit;
		}

		echo 'success';
		exit;
	}


	public function toggle_listing($id)
	{
		$sc = TRUE;
		$sender = $this->sender_model->get($id);
		$listing = 0;

		if( ! empty($sender))
		{
			$listing = $sender->show_in_list == 1 ? 0 : 1;

			$arr = array(
				'show_in_list' => $listing
			);

			if( ! $this->sender_model->update($id, $arr))
			{
				$sc = FALSE;
				set_error('update');
			}
		}
		else
		{
			$sc = FALSE;
			set_error('notfound');
		}

		$arr = array(
			'status' => $sc === TRUE ? 'success' : 'failed',
			'message' => $sc === TRUE ? 'success' : $this->error,
			'data' => is_active($listing),
			'listing' => $listing
		);

		echo json_encode($arr);
	}


	public function clear_filter()
	{
		$filter = array('sender_code', 'sender_name', 'sender_addr', 'sender_phone', 'sender_type');
		clear_filter($filter);
	}

}//--- end class


 ?>
