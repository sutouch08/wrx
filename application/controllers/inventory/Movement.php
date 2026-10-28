<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use OpenSpout\Writer\XLSX\Writer;
use OpenSpout\Common\Entity\Row;

class Movement extends PS_Controller
{
  public $menu_code = 'ICCKMV';
	public $menu_group_code = 'IC';
  public $menu_sub_group_code = 'CHECK';
	public $title = 'ตรวจสอบ Movement';  
  public $error;
  public $segment = 4;

  public function __construct()
  {
    parent::__construct();
    $this->home = base_url().'inventory/movement';
    $this->load->model('inventory/movement_model');
    $this->load->helper('warehouse');
  }

  public function index()
  {    
    $filter = array(
      'reference' => get_filter('reference', 'mv_reference', ''),
      'warehouse_code' => get_filter('warehouse_code', 'mv_warehouse_code', 'all'),
      'zone_code' => get_filter('zone_code', 'mv_zone_code', ''),
      'product_code' => get_filter('product_code', 'mv_product_code', ''),
      'from_date' => get_filter('from_date', 'mv_from_date', ''),
      'to_date' => get_filter('to_date', 'mv_to_date', ''),
      'range' => get_filter('range', 'mv_range', '500000')
    );

    if ($this->input->post('search'))
    {
      redirect($this->home);
      exit;
    }
    else
    {
      $perpage = get_rows();
      $filter['id'] = $this->movement_model->get_max_id($filter['range']);
      $rows = $this->movement_model->count_rows($filter);
      $filter['data'] = $this->movement_model->get_list($filter, $perpage, $this->uri->segment($this->segment));
      $init = pagination_config($this->home . '/index/', $rows, $perpage, $this->segment);
      $this->pagination->initialize($init);
      $this->load->view('inventory/movement/movement_list', $filter);
    }    
  }


  public function export_filter()
  {
    // This also needs to be increased in some cases. Can be changed to a higher value as per need)
    ini_set('memory_limit','2048M');
    
    $ds = json_decode($this->input->post('export-filter'), true);
    $token = $ds['token'];    
    $max_id = $this->movement_model->get_max_id($ds['range']);

    $filter = array(
      'reference' => $ds['reference'],
      'product_code' => $ds['product_code'],
      'warehouse_code' => $ds['warehouse_code'],
      'zone_code' => $ds['zone_code'],
      'from_date' => $ds['from_date'],
      'to_date' => $ds['to_date'],
      'id' => $max_id
    );

    $tempFile = tempnam(sys_get_temp_dir(), 'mv_');
    $fileName = "Stock-movement-".date('Y-m-d_H-i-s').".xlsx";
    $header = ['Reference', 'Item', 'BatchNumber', 'Warehouse', 'Zone', 'Move In', 'Move Out', 'Move Date'];

    try
    {
      $writer = new Writer();
      $writer->openToFile($tempFile);
      $writer->addRow(ROW::fromValues($header));
      
      $limit = 10000;      
      $max_loop = 10000; // Maximum number of loops to prevent infinite loop
      $rows = $this->movement_model->count_rows($filter);

      if($rows > 0)
      {
        while($rows > 0 && $max_loop > 0)
        {
          $list = $this->movement_model->get_export_data($filter, $limit);          
          $rows -= count($list ?? []);

          if(!empty($list))
          {
            foreach($list as $rs)
            {
              $writer->addRow(ROW::fromValues(array(
                $rs->reference,
                $rs->product_code,
                $rs->batchNum,
                $rs->warehouse_code,
                $rs->zone_code,
                (float) $rs->move_in,
                (float) $rs->move_out,
                thai_date($rs->date_upd, TRUE)
              )));

              $filter['id'] = $rs->id; // move  curcor to next id
            }
          }
          
          $max_loop--;
        }
      }

      $writer->close();
      setToken($token);
      while(ob_get_level()) ob_end_clean(); // Clear output buffer if any

      header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      header('Content-Disposition: attachment;filename="'.$fileName.'"');
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


  public function clear_filter()
  {
    $filter = array(
      'mv_reference',
      'mv_warehouse_code',
      'mv_zone_code',
      'mv_product_code',
      'mv_from_date',
      'mv_to_date',
      'mv_range'
    );

    return clear_filter($filter);
  }

} // end class
?>
