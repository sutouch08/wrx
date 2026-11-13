<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use OpenSpout\Writer\XLSX\Writer;
use OpenSpout\Common\Entity\Row;

class Prepare_list extends PS_Controller
{
  public $menu_code = 'ICPREP';
	public $menu_group_code = 'IC';
  public $menu_sub_group_code = 'CHECK';
	public $title = 'ตรวจสอบ รายการจัดสินค้า';    
  public $segment = 4;

  public function __construct()
  {
    parent::__construct();
    $this->home = base_url().'inventory/prepare_list';
    $this->load->model('inventory/prepare_list_model');
    $this->load->model('masters/zone_model');
    $this->load->helper('warehouse');
    $this->load->helper('state');
  }

  public function index()
  {
    $filter = array(
      'order_code' => get_filter('order_code', 'order_code', ''),
      'warehouse_code' => get_filter('warehouse_code', 'warehouse_code', 'all'),
      'zone_code' => get_filter('zone_code', 'zone_code', ''),
      'pd_code' => get_filter('pd_code', 'pd_code'),
      'user' => get_filter('user', 'user', 'all'),
      'from_date' => get_filter('from_date', 'from_date', ''),
      'to_date' => get_filter('to_date', 'to_date', ''),
      'range' => get_filter('range', 'range', 500000)
    );

    if($this->input->post('search'))
    {
      redirect($this->home);
      exit;
    }
    else
    {
      $perpage = get_rows();
      $filter['max_id'] = $this->prepare_list_model->get_max_id($filter['range']);
  		$rows = $this->prepare_list_model->count_rows($filter);
      $filter['data'] = $this->prepare_list_model->get_list($filter, $perpage, $this->uri->segment($this->segment));
  		$init = pagination_config($this->home.'/index/', $rows, $perpage, $this->segment);
  		$this->pagination->initialize($init);
      $this->load->view('inventory/prepare_list/prepare_list_view', $filter);
    }
  }


  public function export_filter()
  {    
    ini_set('memory_limit','2048M');
    $data = json_decode($this->input->post('data') ?? '', true);
       
    $filter = array(
      'order_code' => $data['order_code'],
      'warehouse_code' => $data['warehouse_code'],
      'zone_code' => $data['zone_code'],
      'pd_code' => $data['pd_code'],
      'user' => $data['user'],
      'from_date' => $data['from_date'],
      'to_date' => $data['to_date']           
    );

    $filter['max_id'] = $this->prepare_list_model->get_max_id($data['range']);
    $token = $data['token'];
    $tempFile = tempnam(sys_get_temp_dir(), 'pickLogs_');
    $fileName = "PickLogs-".date('Ymd-His').".xlsx";

   try
   {
      $writer = new Writer();
      $writer->openToFile($tempFile);
      $header = ['No', 'OrderDate', 'OrderNo', 'Reference', 'Channels', 'Item', 'Qty', 'Status', 'PickingDate', 'Warehouse', 'Zone', 'User'];
      $writer->addRow(Row::fromValues($header));

      $limit = 10000;
      $rows = $this->prepare_list_model->count_rows($filter);

      $stateName = $this->prepare_list_model->state_name_array();
      $channelsName = $this->prepare_list_model->channels_name_array();
      $userName = []; //--- cache for user names      
      $no = 1;         

      if($rows > 0)
      {               
        while($rows > 0)
        {
          // echo "Max ID: ".$filter['max_id'].", Rows left: $rows<br>";
          $list = $this->prepare_list_model->get_export_data($filter, $limit);
          $count = count($list ?? []);         
          $rows -= $count;
         
          if(! empty($list))
          {
            foreach ($list as $rs)
            {
              //--- check and cache user names
              if (empty($userName[$rs->user]))
              {
                $userName[$rs->user] = $this->user_model->get_name($rs->user);
              }

              $row = array(
                $no,
                thai_date($rs->order_date),
                $rs->order_code,
                $rs->reference,
                empty($channelsName[$rs->channels_code ?? '']) ? '' : $channelsName[$rs->channels_code ?? ''],
                $rs->product_code,
                $rs->qty,
                empty($stateName[$rs->state ?? '']) ? '' : $stateName[$rs->state ?? ''],
                thai_date($rs->date_upd, TRUE),
                $rs->warehouse_code,
                $rs->zone_code,
                empty($userName[$rs->user]) ? $rs->user : $userName[$rs->user]
              );

              $writer->addRow(Row::fromValues($row));
              $no++;

              $filter['max_id'] = $rs->id;
            }
          }
        }
      }

      $writer->close();
      setToken($token);
      while(ob_get_level() > 0) { ob_end_flush(); }
      header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      header('Content-Disposition: attachment;filename="'.$fileName.'"');
      header('Content-Length: ' . filesize($tempFile));
      header('Cache-Control: max-age=0');
      readfile($tempFile);    
    }    
    finally
    {
      if(file_exists($tempFile))
      {
        unlink($tempFile);
      }
    }

    exit;    
  }

  function clear_filter(){
    $filter = array('order_code', 'pd_code', 'warehouse_code', 'zone_code', 'user', 'from_date', 'to_date', 'range');
    return clear_filter($filter);
  }
}
