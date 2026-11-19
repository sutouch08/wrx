<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use OpenSpout\Writer\Xlsx\Writer;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class Consign_stock_zone extends PS_Controller
{
  public $menu_code = 'RCSSTZ';
	public $menu_group_code = 'RE';
  public $menu_sub_group_code = 'REINVT';
	public $title = 'รายงานสินค้าคงเหลือฝากขายแท้ แยกตามโซน';
 
  public function __construct()
  {
    parent::__construct();
    $this->home = base_url().'report/inventory/consign_stock_zone';
    $this->load->model('report/inventory/consign_stock_report_model');
    $this->load->model('masters/products_model');
    $this->load->model('masters/warehouse_model');
    $this->load->helper('warehouse');
  }

  public function index()
  {
    $ds['whList'] = $this->warehouse_model->get_consign_list();
    $this->load->view('report/inventory/report_consign_stock_zone', $ds);
  }

  public function get_zone_code_and_name($warehouse = '')
  {
    $txt = $_REQUEST['term'] ?? '';
    $this->db
    ->select('zone.code, zone.name')
    ->from('zone')
    ->join('warehouse', 'warehouse.code = zone.warehouse_code', 'left')    
    ->where('warehouse.role', 2)
    ->group_start()
    ->where('warehouse.is_consignment', 0)
    ->or_where('warehouse.is_consignment IS NULL', NULL, FALSE)
    ->group_end()
    ->where('warehouse.active', 1);

    if(!empty($warehouse))
    {
      $warehouse = urldecode($warehouse);
      $arr = explode('|', $warehouse);
      $this->db->where_in('warehouse.code', $arr);
    }

    if($txt != '' && $txt != '*')
    {
      $this->db->group_start();
      $this->db->like('zone.code', $txt);
      $this->db->or_like('zone.name', $txt);
      $this->db->group_end();
    }

    $this->db->order_by('warehouse.code', 'ASC')->order_by('zone.code', 'ASC');
    $rs = $this->db->limit(50)->get();

    if($rs->num_rows() > 0)
    {
      $ds = [];
      foreach($rs->result() AS $rs)
      {
        $ds[] = $rs->code.' | '.$rs->name;
      }

      echo json_encode($ds);
    }
    else 
    {
      echo json_encode([]);
    }
  }

  public function get_item_code_and_name()
  {
    $txt = $_REQUEST['term'] ?? '';
    $this->db->select('code, name');

    if($txt != '' && $txt != '*')
    {
      $this->db->like('code', $txt);
    }

    $rs = $this->db->limit(50)->get('products');

    if($rs->num_rows() > 0)
    {
      $ds = [];
      foreach($rs->result() AS $rs)
      {
        $ds[] = $rs->code.' | '.$rs->name;
      }

      echo json_encode($ds);
    }
    else 
    {
      echo json_encode([]);
    }
  }


  public function get_report()
  {
    ini_set('memory_limit','2048M');
    ini_set('sqlsrv.ClientBufferMaxKBSize','2097152'); // Setting to 512M
    
    $ds = json_decode($this->input->post('data') ?? '{}');

    if(empty($ds))
    {
      echo "Invalid input data";
      exit;
    }

    $filter = array(
      'allProduct' => $ds->allProduct ?? 1,
      'pdFrom' => $ds->pdFrom ?? '',
      'pdTo' => $ds->pdTo ?? '',
      'allWhouse' => $ds->allWhouse ?? 1,
      'warehouse' => $ds->allWhouse == 0 ? $ds->warehouse ?? [] : consign_warehouse_code_array(),
      'allZone' => $ds->allZone ?? 1,
      'zoneCode' => $ds->zoneCode ?? '',
      'zoneName' => $ds->zoneName ?? ''
    );
    
    $count = $this->consign_stock_report_model->count_rows($filter);
    
    if($count > 2000)
    {
      echo "ข้อมูลมีปริมาณ ".number($count)." รายการ กรุณาส่งออกข้อมูลแทนการแสดงผลหน้าจอ";
      exit;
    }

    $bs = array();
    $rows = 0;
    $limit = 10000;
    $offset = 0;
    $max_loop = 100;
    $totalQty = 0;
    $totalAmount = 0;
    $no = 1;

    if($count > 0)
    {
      while($rows < $count && $max_loop > 0)
      {
        $data = $this->consign_stock_report_model->get_consign_stock_zone($filter, $limit, $offset);

        if(!empty($data))
        {
          foreach($data as $rs)
          {
            $qty = round($rs->qty, 2);
            $price = round($rs->price, 2);
            $amount = round($qty * $price, 2);

            $bs[] = array(
              'no' => number($no),
              'warehouse' => $rs->warehouse_code,
              'zoneCode' => $rs->zone_code,
              'zoneName' => empty($rs->zone_name) ? $rs->zone_code : $rs->zone_name,
              'pdCode' => $rs->product_code,
              'pdName' => $rs->product_name,
              'qty' => number($qty),
              'price' => number($price),
              'amount' => number($amount)
            );

            $totalQty += $qty;
            $totalAmount += $amount;
            $no++;
            $rows++;
          }
        }

        $offset += $limit;
        $max_loop--;
      }

      $bs[] = array(
        'totalQty' => number($totalQty),
        'totalAmount' => number($totalAmount)
      );
    }

    echo json_encode($bs);    
  }


  public function do_export()
  {
    ini_set('memory_limit','2048M');
    ini_set('max_execution_time', 1200); //-- 20 minutes
    ini_set('sqlsrv.ClientBufferMaxKBSize','2097152'); // Setting to 512M

    $token = $this->input->post('token');
    $ds = json_decode($this->input->post('data') ?? '{}');

    if(empty($ds))
    {
      echo "Invalid data";
      exit;
    }

    $filter = array(
      'allProduct' => $ds->allProduct ?? 1,
      'pdFrom' => $ds->pdFrom ?? '',
      'pdTo' => $ds->pdTo ?? '',
      'allWhouse' => $ds->allWhouse ?? 1,
      'warehouse' => $ds->allWhouse == 0 ? $ds->warehouse ?? [] : consign_warehouse_code_array(),
      'allZone' => $ds->allZone ?? 1,
      'zoneCode' => $ds->zoneCode ?? ''
    );
    
    $wh_list = '';

    if($ds->allWhouse == 0 && !empty($ds->warehouse))
    {
      $i = 1;
      foreach($ds->warehouse as $wh)
      {
        $wh_list .= $i === 1 ? $wh : ', '.$wh;
        $i++;
      }
    }
    
    $report_title = "รายงานสินค้าคงเหลือ(ฝากขาย) ณ วันที่ ".date('d/m/Y');
    $whList = $ds->allWhouse == 1 ? 'ทั้งหมด' : $wh_list;
    $zoneList = $ds->allZone == 1 ? 'ทั้งหมด' : $ds->zoneCode." - ".$ds->zoneName;
    $productList  = $ds->allProduct == 1 ? 'ทั้งหมด' : '('.$ds->pdFrom.') - ('.$ds->pdTo.')';
    $header = ["#","รหัสคลัง","รหัสโซน","ชื่อโซน","รหัสสินค้า","ชื่อสินค้า", "ราคาขาย", "จำนวน", "มูลค่า"];
    $tempFile = tempnam(sys_get_temp_dir(), 'consign_stock_zone_');
    $fileName = "Report-Consign-stock-zone.xlsx";

    try 
    {
      $writer = new Writer();
      $writer->openToFile($tempFile);
      $style = (new Style())->setShouldWrapText(false);
      $writer->getOptions()->SET_DEFAULT_ROW_STYLE = $style;
      $sheet = $writer->getCurrentSheet();
      $sheet->setName('Stock Balance Report');

      $writer->addRow(Row::fromValues([$report_title]));
      $writer->addRow(Row::fromValues(['คลัง', $whList]));
      $writer->addRow(Row::fromValues(['โซน', $zoneList]));
      $writer->addRow(Row::fromValues(['สินค้า', $productList]));
      $writer->addRow(Row::fromValues([]));
      $writer->addRow(Row::fromValues($header));
      
      $writer->getOptions()->mergeCells(0, 1, 8, 1);
      $writer->getOptions()->mergeCells(1, 2, 8, 2);
      $writer->getOptions()->mergeCells(1, 3, 8, 3);
      $writer->getOptions()->mergeCells(1, 4, 8, 4);
      
      $sheet->setColumnWidth(7, 1);
      $sheet->setColumnWidth(12, 2);
      $sheet->setColumnWidth(20, 3);
      $sheet->setColumnWidth(30, 4);
      $sheet->setColumnWidth(30, 5);
      $sheet->setColumnWidth(60, 6);
      $sheet->setColumnWidth(12, 7);
      $sheet->setColumnWidth(12, 8);
      $sheet->setColumnWidth(12, 9);

      $count = $this->consign_stock_report_model->count_rows($filter);
      $rows = 0;
      $limit = 10000;
      $offset = 0;
      $max_loop = 100;
      $totalQty = 0;
      $totalAmount = 0;
      $no = 1;

      if($count > 0)
      {
        while($rows < $count && $max_loop > 0)
        {
          $data = $this->consign_stock_report_model->get_consign_stock_zone($filter, $limit, $offset);

          if( ! empty($data))
          {
            foreach($data as $rs)
            {
              $qty = round($rs->qty, 2);
              $price = round($rs->price, 2);
              $amount = round($qty * $price);

              $writer->addRow(Row::fromValues([
                $no,
                $rs->warehouse_code,
                $rs->zone_code,
                empty($rs->zone_name) ? $rs->zone_code : $rs->zone_name,
                $rs->product_code,
                $rs->product_name,
                $price,
                $qty,
                $amount
              ]));

              $totalQty += $qty;
              $totalAmount += $amount;
              $rows++;
              $no++;
            }
          }

          $offset += $limit;
          $max_loop--;
        }

        $writer->addRow(Row::fromValues(['', '', '', '', '', 'รวม', '', $totalQty, $totalAmount]));
      }

      $writer->close();
      setToken($token);
      while(ob_get_level()) ob_end_clean();
      header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      header('Content-Disposition: attachment; filename="'.$fileName.'"');      
      readfile($tempFile);
    }
    finally
    {
      if(file_exists($tempFile))
      {
        @unlink($tempFile);
      }
    }

    exit;
  }


  public function export_to_check()
  {
    ini_set('memory_limit','2048M');
    ini_set('max_execution_time', 300); // Set maximum execution time to 5 minutes
    ini_set('sqlsrv.ClientBufferMaxKBSize','2097152'); // Setting to 512M    
   
    $zoneCode = $this->input->post('zone_code') ?? '';
    $token = $this->input->post('token');

    $filter = array(
      'allProduct' => 1,
      'pdFrom' => '',
      'pdTo' => '',
      'allWhouse' => 0,
      'warehouse' => [],
      'allZone' => 0,
      'zoneCode' => $zoneCode
    );

    $count = $this->consign_stock_report_model->count_rows($filter);
    $rows = 0;
    $limit = 10000;
    $offset = 0;
    $max_loop = 100;
       
    $excel = new Spreadsheet();
    $sheet = $excel->getActiveSheet();

    $sheet->setTitle('Report Stock Zone');

    //--- set Table header
    $sheet->setCellValue('A1', 'barcode');
    $sheet->setCellValue('B1', 'item_code');
    $sheet->setCellValue('C1', 'qty');

    $row = 2;

    if($count > 0)
    {
      while($rows < $count && $max_loop > 0)
      {
        $data = $this->consign_stock_report_model->get_consign_stock_zone($filter, $limit, $offset);

        if( ! empty($data))
        {
          foreach($data as $rs)
          {
            $sheet->setCellValueExplicit('A'.$row, $this->products_model->get_barcode($rs->product_code), DataType::TYPE_STRING);
            $sheet->setCellValue('B'.$row, $rs->product_code);
            $sheet->setCellValue('C'.$row, $rs->qty);
            $row++;
            $rows++;
          }
        }

        $offset += $limit;
        $max_loop--;
      }    

      $sheet->getStyle('C2:C'.($row -1))->getNumberFormat()->setFormatCode('0');
    }

    setToken($token);
    $file_name = "Report Consign Stock Zone.xlsx";
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); /// form excel 2007 XLSX
    header('Content-Disposition: attachment;filename="'.$file_name.'"');
    $writer = new Xlsx($excel);
    $writer->save('php://output');
  }
}
