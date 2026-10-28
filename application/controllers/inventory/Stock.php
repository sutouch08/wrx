<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;

class Stock extends PS_Controller
{
  public $menu_code = 'ICCKST';
	public $menu_group_code = 'IC';
  public $menu_sub_group_code = 'CHECK';
	public $title = 'ตรวจสอบสต็อกคงเหลือ(SAP)';
  public $segment = 4;

  public function __construct()
  {
    parent::__construct();
    $this->home = base_url().'inventory/stock';
    $this->load->model('inventory/sap_stock_model');
  }

  public function index()
  {
    $filter = array(
      'item_code' => get_filter('item_code', 'item_code', ''),
      'zone_code' => get_filter('zone_code', 'zone_code', ''),
      'show_system' => get_filter('show_system', 'show_system', 'no')
    );
		
		$perpage = get_rows();			
		$rows = $this->sap_stock_model->count_rows($filter);		
		$filter['data']= $this->sap_stock_model->get_list($filter, $perpage, $this->uri->segment($this->segment));
		$init = pagination_config($this->home.'/index/', $rows, $perpage, $this->segment);
		$this->pagination->initialize($init);
    $this->load->view('inventory/stock/stock_view', $filter);
  }

  public function export()
  {    
    $arr = array(
      'item_code' => $this->input->post('item'),
      'zone_code' => $this->input->post('zone'),
      'show_system' => $this->input->post('system')
    );

    $header = ['No.', 'ItemCode', 'OldCode', 'Description', 'BinCode', 'Bin Description', 'Qty'];
    $token = $this->input->post('token');
    $tempFile = tempnam(sys_get_temp_dir(), 'stock');
    $fileName = 'stock-'.date('Ymd-His').'.xlsx';
    
    try 
    {
      $writer = new Writer();
      $writer->openToFile($tempFile);
      $writer->addRow(Row::fromValues($header));

      $limit = 1000;
      $offset = 0;
      $max_loop = 10000; //-- safety limit to prevent infinite loop     
      $rows = $this->sap_stock_model->count_rows($arr);
      $no = 1;

      if($rows > 0)
      {        
        while($rows > 0 && $max_loop > 0)
        {
          $data = $this->sap_stock_model->get_data($arr, $limit, $offset);
          $count = count($data ?? []);
          $offset += $count;
          $rows -= $count;          

          if(!empty($data))
          {
            foreach($data as $rs)
            {
              $row = [
                $no,
                $rs->ItemCode,
                !empty($rs->U_OLDCODE) ? $rs->U_OLDCODE : '',
                $rs->ItemName,
                $rs->BinCode,
                $rs->Descr,
                round($rs->OnHandQty, 2)
              ];

              $writer->addRow(Row::fromValues($row));
              $no++;
            }
          }
          
          $max_loop--;
        }
      }
      
      $writer->close();

      //-- clear cookie token to stop client loading animation
      setToken($token);

      // clear output buffer before sending the file
      while (ob_get_level()) { ob_end_clean(); }

      header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      header('Content-Disposition: attachment;filename="' . $fileName . '"');
      header('Content-Length: ' . filesize($tempFile));
      header('Cache-Control: max-age=0');
      readfile($tempFile);
    }
    finally
    {
      if (file_exists($tempFile))
      {
        unlink($tempFile);
      }      
    }

    exit;
  }

  function clear_filter()
  {
    return clear_filter(['item_code', 'zone_code', 'show_system']);    
  }
} 