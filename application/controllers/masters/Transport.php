<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Transport extends PS_Controller
{
	public $menu_code = 'DBTRSP';
	public $menu_group_code = 'DB';
	public $menu_sub_group_code = 'TRANSPORT';
	public $title = 'เชื่อมโยงขนส่ง';

	public function __construct()
	{
		parent::__construct();
		$this->home = base_url() . 'masters/transport';
		$this->load->model('masters/transport_model');
		$this->load->model('masters/sender_model');
		$this->load->model('masters/customers_model');
		$this->load->helper('customer_helper');
		$this->load->helper('sender_helper');
	}

	public function index()
	{
		$filter = array(
			'name' => get_filter('name', 'name', ''),
			'sender' => get_filter('sender', 'sender', 'all')
		);

		if ($this->input->post('search'))
		{
			redirect($this->home);
			exit;
		}

		$perpage = get_rows();
		$segment = 4; //-- url segment
		$rows = $this->transport_model->count_rows($filter);
		$filter['data'] = $this->transport_model->get_list($filter, $perpage, $this->uri->segment($segment));

		if (!empty($filter['data']))
		{
			foreach ($filter['data'] as $rs)
			{
				$rs->main_sender = $this->sender_model->get_name($rs->main_sender);
				$rs->second_sender = $this->sender_model->get_name($rs->second_sender);
				$rs->third_sender = $this->sender_model->get_name($rs->third_sender);
			}
		}

		$init	= pagination_config($this->home . '/index/', $rows, $perpage, $segment);
		$this->pagination->initialize($init);
		$this->load->view('masters/transport/transport_view', $filter);
	}


	public function add_new()
	{
		$this->load->view('masters/transport/transport_add');
	}


	public function add()
	{		
		$code = trim($this->input->post('customer_code') ?? '');
		$main = $this->input->post('main_sender');
		$second = $this->input->post('second_sender');
		$third = $this->input->post('third_sender');

		if($code === '' || ! $main)
		{
			echo get_error('required');
			exit;
		}

		if($this->transport_model->is_exists($code))
		{
			echo get_error('exists', $code);
			exit;
		}

		$arr = array(
			'customer_code' => $code,
			'main_sender' => $main,
			'second_sender' => $second,
			'third_sender' => $third
		);

		if( ! $this->transport_model->add($arr))
		{
			echo get_error('insert');
			exit;
		}

		echo 'success';
		exit;
	}


	public function edit($id)
	{
		$rs = $this->transport_model->get($id);

		if (!empty($rs))
		{
			$rs->customer_name = $this->customers_model->get_name($rs->customer_code);
			$rs->main_sender_name = $this->sender_model->get_name($rs->main_sender);
			$rs->second_sender_name = $this->sender_model->get_name($rs->second_sender);
			$rs->third_sender_name = $this->sender_model->get_name($rs->third_sender);
		}

		$this->load->view('masters/transport/transport_edit', $rs);
	}

	public function update()
	{
		$id = $this->input->post('id');
		$code = trim($this->input->post('customer_code') ?? '');
		$main = $this->input->post('main_sender');
		$second = $this->input->post('second_sender');
		$third = $this->input->post('third_sender');

		if(! $id ||$code === '' || ! $main)
		{
			echo get_error('required');
			exit;
		}

		if($this->transport_model->is_exists($code, $id))
		{
			echo get_error('exists', $code);
			exit;
		}

		$arr = array(
			'customer_code' => $code,
			'main_sender' => $main,
			'second_sender' => $second,
			'third_sender' => $third
		);

		if( ! $this->transport_model->update($id, $arr))
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
		if(! $id)
		{
			echo get_error('required');
			exit;
		}

		if(! $this->transport_model->delete($id))
		{
			echo get_error('delete');
			exit;
		}

		echo 'success';
		exit;
	}

	public function clear_filter()
	{
		return clear_filter(array('name', 'sender'));
	}
}
