<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use OpenSpout\Writer\XLSX\Writer;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Stock_balance_zone extends PS_Controller
{
  public $menu_code = 'RICSBZ';
	public $menu_group_code = 'RE';
  public $menu_sub_group_code = 'REINVT';
	public $title = 'รายงานสินค้าคงเหลือ แยกตามโซน';
  
  public function __construct()
  {
    parent::__construct();
    $this->home = base_url().'report/inventory/stock_balance_zone';
    $this->load->model('report/inventory/stock_balance_report_model');
    $this->load->model('masters/products_model');
    $this->load->model('masters/warehouse_model');
  }

  public function index()
  {    
    $ds['whList'] = $this->warehouse_model->get_all_warehouse_list();
    $this->load->view('report/inventory/report_stock_balance_zone', $ds);
  }


  public function get_report()
  {
    ini_set('memory_limit','2048M');
    ini_set('max_execution_time', 600);
    ini_set('sqlsrv.ClientBufferMaxKBSize','2097152');
    ini_set('sqlsrv.client_buffer_max_kb_size','2097152');

    $ds = json_decode($this->input->post('data') ?? '{}');
    
    if(empty($ds))
    {
      echo "Invalid input data";
      exit;
    }

    $filter = array(
      'allProduct' => $ds->allProduct,
      'pdFrom' => $ds->pdFrom,
      'pdTo' => $ds->pdTo,
      'allWhouse' => $ds->allWhouse,
      'warehouse' => $ds->warehouse,
      'allZone' => $ds->allZone,
      'zoneCode' => $ds->zoneCode
    );
    
    $bs = array();

    $count = $this->stock_balance_report_model->count_rows($filter);

    if($count > 2000)
    {      
      echo "ข้อมูลมีปริมาณมากเกินกว่าจะแสดงผลได้ กรุณาส่งออกข้อมูลแทนการแสดงผลหน้าจอ";
      exit;
    }
    
    $rows = 0;
    $limit = 10000;
    $offset = 0;
    $max_loop = 100;
    $no = 1;
    $totalQty = 0;
    $totalAmount = 0;

    if($count > 0)
    {
      while($rows < $count && $max_loop > 0)
      {
        $result = $this->stock_balance_report_model->get_stock_zone($filter, $limit, $offset);

        if(! empty($result))
        {
          foreach($result as $rs)
          {
            $amount = round($rs->qty * $rs->price, 2);
            $qty = round($rs->qty, 2);

            $bs[] = array(
              'no' => number($no),
              'warehouse' => $rs->warehouse_code,
              'zone' => empty($rs->zone_name) ? $rs->zone_code : $rs->zone_name,
              'pdCode' => $rs->product_code,
              'pdName' => $rs->product_name,
              'price' => number($rs->price, 2),
              'qty' => number($qty),
              'amount' => number($amount, 2)
            );

            $totalQty += $qty;
            $totalAmount += $amount;
            $no++;
            $rows++;
            $offset++;
          }
        }
        
        $max_loop--;
      }
    }

    $bs[] = ['totalQty' => number($totalQty), 'totalAmount' => number($totalAmount, 2)];    
    echo json_encode(['bs' => $bs]);
  }


  public function do_export()
  {
    ini_set('memory_limit', '2048M');
    ini_set('max_execution_time', 600);
    ini_set('sqlsrv.ClientBufferMaxKBSize', '2097152');
    ini_set('sqlsrv.client_buffer_max_kb_size', '2097152');
    
    $ds = json_decode($this->input->post('data') ?? '{}');

    if (empty($ds))
    {
      echo "Invalid input data";
      exit;
    }

    $filter = array(
      'allProduct' => $ds->allProduct,
      'pdFrom' => $ds->pdFrom,
      'pdTo' => $ds->pdTo,
      'allWhouse' => $ds->allWhouse,
      'warehouse' => $ds->warehouse,
      'allZone' => $ds->allZone,
      'zoneCode' => $ds->zoneCode
    );

    $token = $this->input->post('token');
    
    $wh_list = '';

    if( ! empty($ds->warehouse) && empty($ds->zoneCode))
    {
      $i = 1;

      foreach($ds->warehouse as $wh)
      {
        $wh_list .= $i === 1 ? $wh : ', '.$wh;
        $i++;
      }
    }
    
    $report_title = "รายงานสินค้าคงเหลือแยกตามโซน ณ วันที่ ".date('d/m/Y');
    $whList = $ds->allWhouse == 1 ? 'ทั้งหมด' : $wh_list;
    $zoneList = $ds->allZone == 1 ? 'ทั้งหมด' : $ds->zoneCode." - ".$ds->zoneName;
    $productList  = $ds->allProduct == 1 ? 'ทั้งหมด' : '('.$ds->pdFrom.') - ('.$ds->pdTo.')';
    $tempFile = tempnam(sys_get_temp_dir(), 'stock_balance_zone_');
    $fileName = "Report-Stock-Zone.xlsx";

    try
    {
      $style = (new Style())->setShouldWrapText(false);
      $excel = new writer();
      $excel->openToFile($tempFile);
      $excel->getOptions()->DEFAULT_ROW_STYLE = $style;

      $sheet = $excel->getCurrentSheet();
      $sheet->setName("Stock Balance Zone");
      $sheet->setColumnWidth(7, 1);
      $sheet->setColumnWidth(12, 2);
      $sheet->setColumnWidth(20, 3);
      $sheet->setColumnWidth(30, 4);
      $sheet->setColumnWidth(30, 5);
      $sheet->setColumnWidth(60, 6);
      $sheet->setColumnWidth(10, 7);
      $sheet->setColumnWidth(10, 8);
      $sheet->setColumnWidth(12, 9);

      $excel->getOptions()->mergeCells(0, 1, 8, 1);
      $excel->getOptions()->mergeCells(1, 2, 8, 2);
      $excel->getOptions()->mergeCells(1, 3, 8, 3);
      $excel->getOptions()->mergeCells(1, 4, 8, 4);

      $excel->addRow(Row::fromValues([$report_title]));
      $excel->addRow(Row::fromValues(['คลัง', $whList]));
      $excel->addRow(Row::fromValues(['โซน', $zoneList]));
      $excel->addRow(Row::fromValues(['สินค้า', $productList]));
      $excel->addRow(Row::fromValues(['']));
      $excel->addRow(Row::fromValues(['#', 'รหัสคลัง', 'รหัสโซน', 'ชื่อโซน', 'รหัสสินค้า', 'ชื่อสินค้า', 'ราคาขาย', 'จำนวน', 'มูลค่า']));

      $count = $this->stock_balance_report_model->count_rows($filter);
      $rows = 0;
      $limit = 10000;
      $offset = 0;
      $max_loop = 100;
      $no = 1;
      $totalQty = 0;
      $totalAmount = 0;

      if ($count > 0)
      {
        while($rows < $count && $max_loop > 0)
        {
          $result = $this->stock_balance_report_model->get_stock_zone($filter, $limit, $offset);

          if(!empty($result))
          {
            foreach($result as $rs)
            {
              $qty = round($rs->qty, 2);
              $amount = round($rs->qty * $rs->price, 2);
              $price = round($rs->price, 2);

              $excel->addRow(Row::fromValues([
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
              $no++;
              $rows++;
              $offset++;
            }
          }
          
          $max_loop--;
        }

        $excel->addRow(Row::fromValues(['', '', '', '', '', '', 'รวม', $totalQty, $totalAmount]));
      }

      $excel->close();
      setToken($token);
      while(ob_get_level()) ob_end_clean();
      header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      header('Content-Disposition: attachment;filename="'.$fileName.'"');     
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
    ini_set('max_execution_time', 300);
    ini_set('sqlsrv.ClientBufferMaxKBSize','2097152');
    ini_set('pdo_sqlsrv.client_buffer_max_kb_size','2097152');

    $token = $this->input->post('token');
    $zoneCode = $this->input->post('zone_code') ?? '';

    $filter = array(
      'allProduct' => 1,
      'pdFrom' => NULL,
      'pdTo' => NULL,
      'allWhouse' => 1,
      'warehouse' => NULL,
      'allZone' => 0,
      'zoneCode' => $zoneCode
    );   

    $excel = new Spreadsheet();
    $sheet = $excel->getActiveSheet();
    $sheet->setTitle($zoneCode);
    $sheet->setCellvalue('A1', 'barcode');
    $sheet->setCellvalue('B1', 'item_code');
    $sheet->setCellvalue('C1', 'qty');

    $count = $this->stock_balance_report_model->count_rows($filter);
    $rows = 0;
    $limit = 10000;
    $offset = 0;
    $max_loop = 100;
    $row = 2;

    if($count > 0)
    {
      while($rows < $count && $max_loop > 0)
      {
        $result = $this->stock_balance_report_model->get_stock_zone($filter, $limit, $offset);
        
        if(!empty($result))
        {
          foreach($result as $rs)
          {
            $sheet->setCellvalue('A'.$row, $this->products_model->get_barcode($rs->product_code));
            $sheet->setCellvalue('B'.$row, $rs->product_code);
            $sheet->setCellvalue('C'.$row, $rs->qty);
            $row++;
            $rows++;
          }
        }

        $offset += $limit;
        $max_loop--;
      }
    }

    $sheet->getStyle('A2:A'.($row - 1))->getNumberFormat()->setFormatCode('0');
    
    setToken($token);
    $file_name = "Report Stock Zone.xlsx";
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); /// form excel 2007 XLSX
    header('Content-Disposition: attachment;filename="'.$file_name.'"');
    $writer = new Xlsx($excel);
    $writer->save('php://output');
  }
}
