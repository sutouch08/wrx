<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use OpenSpout\Reader\XLSX\Reader;

class Auto_change_state extends PS_Controller
{   
  public $title = "Change State";  
  public $menu_code = 'SOATCS';
  public $menu_group_code = 'SO';
  
  public function __construct()
  {
    parent::__construct();  
    $this->home = base_url().'auto/auto_change_state';
    $this->load->model('inventory/delivery_order_model');
    $this->load->model('orders/orders_model');
    $this->load->model('orders/order_state_model');
    $this->load->library('export');
  }

  public function index()
  {
    $limit = getConfig('AUTO_CHANGE_STATE_LIMIT') ?? 100;

    $filter = array(
      'code' => get_filter('code', 'auto_code', ''),
      'status' => get_filter('status', 'auto_status', '0')
    );

    $rows = $this->count_all($filter);
    $data = $this->get_all($filter, $limit);

    $filter['count'] = empty($data) ? 0 : count($data);
    $filter['all'] = $rows;
    $filter['limit'] = $limit;
    $filter['data'] = $data;
    $this->load->view('auto/auto_change_state', $filter);
  }

  public function get_all($filter = array(), $limit = 100)
  {
    $this->db
    ->select('a.*, o.state')
    ->from('auto_send_to_sap_order AS a')
    ->join('orders AS o', 'a.code = o.code', 'left');

    if(!empty($filter['code']))
    {
      $this->db->like('a.code', $filter['code']);
    }

    if(isset($filter['status']) && $filter['status'] != 'all')
    {
      $this->db->where('a.status', $filter['status']);
    }

    $rs = $this->db
    ->order_by('a.status', 'ASC')
    ->order_by('a.code', 'ASC')
    ->limit($limit)
    ->get();

    if($rs->num_rows() > 0)
    {
      return $rs->result();
    }

    return NULL;
  }

  public function count_all(array $filter = array())
  {
    if (!empty($filter['code']))
    {
      $this->db->like('code', $filter['code']);
    }

    if(isset($filter['status']) && $filter['status'] != 'all')
    {
      $this->db->where('status', $filter['status']);
    }

    $count = $this->db->count_all_results('auto_send_to_sap_order');

    return $count;
  }

  public function update_status()
	{
    $sc = TRUE;
    $code = $this->input->post('code');
    $status = $this->input->post('status');
    $message = $this->input->post('message');

    $ds = array(
      'status' => $status,
      'message' => $message
    );

		if( ! $this->db->where('code', $code)->update('auto_send_to_sap_order', $ds))
    {
      $sc = FALSE;
      $this->error = "Update false";
    }

    echo $sc === TRUE ? 'success' : $this->error;
	}

  public function import_order()
  {
    $sc = TRUE;
    $file = isset( $_FILES['uploadFile'] ) ? $_FILES['uploadFile'] : FALSE;
    $path = $this->config->item('upload_path').'import_files/';
    $file = 'uploadFile';
    $config = array(
      "allowed_types" => "xlsx",
      "upload_path" => $path,
      "file_name"	=> "import_file",
      "max_size" => 5120,
      "overwrite" => TRUE
    );

    $this->load->library("upload", $config);

    if(! $this->upload->do_upload($file))
    {
      $sc = FALSE;
      $this->error = $this->upload->display_errors();
    }
    else 
    {
      $info = $this->upload->data();
      $file_path = $info['full_path'];
      $reader = new Reader();
      $reader->open($file_path);
      $sheet = $reader->getSheetIterator()->current();

      $isFirstRow = TRUE;
      $j = 0;
      $ds = [];
      $ro = [];
      
      foreach ($sheet->getRowIterator() as $row) 
      {
        if($isFirstRow)
        {
          $isFirstRow = FALSE;
          continue;
        }

        $cells = $row->getCells();
        $code = isset($cells[0]) ? trim($cells[0]->getValue()) : '';

        if(! empty($code))
        {
          $j++;
          $ro[] = array('code' => $code);
        }        

        if($j == 1000)
        {
          $j = 0;
          $ds[] = $ro;
          $ro = [];
        }
      }

      if(!empty($ro))
      {
        $ds[] = $ro;
      }
     
      $reader->close();

      if( ! empty($ds))
      {
        foreach($ds as $rows)
        {
          if( ! $this->insert($rows))
          {
            $sc = FALSE;
            $this->error = "Cannot insert data";
          }
        }
      }
      else 
      {
        $sc = FALSE;
        $this->error = "Cannot get data from import file : empty data set";
      }
    }

    $this->_response($sc);
  }

  public function insert(array $ds = array())
  {
    return !empty($ds) ? $this->db->insert_batch('auto_send_to_sap_order', $ds) : FALSE;
  }

  public function clear_data()
  {
    echo $this->db->query("TRUNCATE TABLE auto_send_to_sap_order") ? 'success' : 'Failed to clear data';    
  }

  public function change_order_limit()
  {
    $sc = TRUE;
    $limit = $this->input->post('limit');

    if($limit > 0)
    {
      $this->load->model('setting/config_model');

      if( ! $this->config_model->update('AUTO_CHANGE_STATE_LIMIT', $limit))
      {
        $sc = FALSE;
        $this->error = "Failed to update config value";
      }
    }
    else
    {
      $sc = FALSE;
      $this->error = "จำนวนต้องมากกว่า 0";
    }

    $this->_response($sc);
  }

}