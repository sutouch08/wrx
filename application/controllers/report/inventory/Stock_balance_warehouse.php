<?php
defined('BASEPATH') or exit('No direct script access allowed');
use OpenSpout\Writer\XLSX\Writer;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;

class Stock_balance_warehouse extends PS_Controller
{
  public $menu_code = 'RICSBW';
  public $menu_group_code = 'RE';
  public $menu_sub_group_code = 'REINVT';
  public $title = 'รายงานสินค้าคงเหลือ แยกตามคลัง';

  public function __construct()
  {
    parent::__construct();
    $this->home = base_url() . 'report/inventory/stock_balance_warehouse';
    $this->load->model('report/inventory/stock_balance_report_model');
    $this->load->model('masters/warehouse_model');
    $this->load->model('masters/products_model');
    $this->load->helper('warehouse');
  }

  public function index()
  {    
    $ds['whList'] = $this->warehouse_model->get_all_warehouse_list();
    $this->load->view('report/inventory/report_stock_balance_warehouse', $ds);
  }

  public function get_item_code_and_name()
  {
    $txt = $_REQUEST['term'] ?? '';
    $this->db->select('code, name');

    if ($txt != '' && $txt != '*')
    {
      $this->db->like('code', $txt);
    }

    $rs = $this->db->limit(50)->get('products');

    if ($rs->num_rows() > 0)
    {
      $data = [];

      foreach ($rs->result() as $row)
      {
        $data[] = $row->code . ' | ' . $row->name;
      }

      echo json_encode($data);
    }
  }

  public function get_report()
  {
    $bs = [];
    $allProduct = $this->input->post('allProduct');
    $allWhouse = $this->input->post('allWhouse');
    $warehouse = $allWhouse == 0 ? $this->input->post('warehouse') ?? [] : warehouse_code_array();
    $pdFrom = $this->input->post('pdFrom');
    $pdTo = $this->input->post('pdTo');

    $filter = array(
      'allProduct' => $allProduct,
      'pdFrom' => $pdFrom,
      'pdTo' => $pdTo,
      'allWhouse' => $allWhouse,
      'warehouse' => $warehouse
    );

    $count = $this->stock_balance_report_model->count_stock_warehouse($filter);
    
    if ($count > 2000)
    {
      echo "ข้อมูลมีปริมาณมากเกินกว่าจะแสดงผลได้ กรุณาส่งออกข้อมูลแทนการแสดงผลหน้าจอ {$count}";
      exit;
    }

    $rows = 0;
    $limit = 10000;
    $offset = 0;
    $max_loop = 100;
    $no = 1;
    $totalQty = 0;
    $totalAmount = 0;

    $whs = [];
    
    if ($count > 0)
    {
      while($rows < $count && $max_loop > 0)
      {
        $data = $this->stock_balance_report_model->get_stock_warehouse($filter, $limit, $offset);
        
        if (!empty($data))
        {
          foreach ($data as $rs)
          {
            if(!isset($whs[$rs->WhsCode])) $whs[$rs->WhsCode] = $this->warehouse_model->get_name($rs->WhsCode);            
            
            $whsName = empty($whs[$rs->WhsCode]) ? '' : $whs[$rs->WhsCode];
            $cost = round($rs->Cost, 2);
            $qty = round($rs->OnHand, 2);
            $amount = $cost * $qty;

            $bs[] = array(
              'no' => $no,
              'warehouse_code' => $rs->WhsCode,
              'warehouse_name' => $whsName,
              'item_code' => $rs->ItemCode,
              'item_name' => $rs->ItemName,
              'cost' => number($cost, 2),
              'qty' => number($qty, 2),
              'amount' => number($amount, 2)
            );

            $totalQty += $qty;
            $totalAmount += $amount;
            $rows++;
            $no++;
          }
        }

        $offset += $limit;
        $max_loop--;
      }

      $bs[] = array(
        'total_qty' => number($totalQty, 2),
        'total_amount' => number($totalAmount, 2)
      );
    }

    echo json_encode($bs);
  }


  public function do_export()
  {
    ini_set('memory_limit', '2048M');
    ini_set('max_execution_time', 3600); // Set maximum execution time to 3600 seconds (60 minutes)
    ini_set('sqlsrv.ClientBufferMaxKBSize', '2097152'); // Setting to 2048M
    ini_set('sqlsrv.client_buffer_max_kb_size', '2097152'); // Setting to 512M - for pdo_sqlsrv
   
    $token = $this->input->post('token');
    $ds = json_decode($this->input->post('data') ?? '{}');

    if(empty($ds))
    {
      echo "Invalid input data";
      exit;
    }

    $allProduct = $ds->allProduct;
    $allWhouse = $ds->allWhouse;
    $warehouse = $allWhouse == 0 ? $ds->warehouse ?? [] : warehouse_code_array();
    $pdFrom = $ds->pdFrom;
    $pdTo = $ds->pdTo;

    $filter = array(
      'allProduct' => $allProduct,
      'pdFrom' => $pdFrom,
      'pdTo' => $pdTo,
      'allWhouse' => $allWhouse,
      'warehouse' => $warehouse
    );
        
    $wh_list = '';
    
    if (!empty($warehouse))
    {
      $i = 1;

      foreach ($warehouse as $wh)
      {
        $wh_list .= $i === 1 ? $wh : ', ' . $wh;
        $i++;
      }
    }

    //---  Report title
    $report_title = 'รายงานสินค้าคงเหลือ(หักยอดจอง) ณ วันที่  ' . thai_date(date('Y-m-d'), '/');
    $wh_title = 'คลัง :  ' . ($allWhouse == 1 ? 'ทั้งหมด' : $wh_list);
    $pd_title = 'สินค้า :  ' . ($allProduct == 1 ? 'ทั้งหมด' : '(' . $pdFrom . ') - (' . $pdTo . ')');

    $tempFile = tempnam(sys_get_temp_dir(), 'stock_balance_warehouse_');
    $fileName = "Stock-balance-warehouse.xlsx";

    try
    {
      $writer = new Writer();
      $writer->openToFile($tempFile);
      $sheet = $writer->getCurrentSheet();
      $sheet->setName("Stock Balance");
      $style = (new Style())->setShouldWrapText(false);
      $writer->getOptions()->DEFAULT_ROW_STYLE = $style;

      $writer->addRow(Row::fromValues([$report_title]));
      $writer->addRow(Row::fromValues([$wh_title]));
      $writer->addRow(Row::fromValues([$pd_title]));
      $writer->addRow(Row::fromValues(['']));
      $writer->addRow(Row::fromValues(['#', 'รหัสคลัง', 'ชื่อคลัง', 'รหัสสินค้า', 'สินค้า', 'ทุน (มาตรฐาน)', 'จำนวน', 'มูลค่า']));

      $writer->getOptions()->mergeCells(0, 1, 7, 1);
      $writer->getOptions()->mergeCells(0, 2, 7, 2);
      $writer->getOptions()->mergeCells(0, 3, 7, 3);

      $sheet->setColumnWidth(7, 1);
      $sheet->setColumnWidth(12, 2);
      $sheet->setColumnWidth(30, 3);
      $sheet->setColumnWidth(25, 4);
      $sheet->setColumnWidth(60, 5);
      $sheet->setColumnWidth(12, 6);
      $sheet->setColumnWidth(12, 7);
      $sheet->setColumnWidth(12, 8);
      
      $count = $this->stock_balance_report_model->count_stock_warehouse($filter);
      $rows = 0;
      $limit = 10000;
      $offset = 0;
      $max_loop = 100;
      $no = 1;
      $totalQty = 0;
      $totalAmount = 0;

      $whs = [];

      if($count > 0)
      {
        if ($count > 0)
        {
          while ($rows < $count && $max_loop > 0)
          {
            $data = $this->stock_balance_report_model->get_stock_warehouse($filter, $limit, $offset);

            if (! empty($data))
            {
              foreach ($data as $rs)
              {
                if(! isset($whs[$rs->WhsCode])) $whs[$rs->WhsCode] = $this->warehouse_model->get_name($rs->WhsCode);                                
                $whsName = empty($whs[$rs->WhsCode]) ? '' : $whs[$rs->WhsCode];
                $cost = round($rs->Cost, 2);
                $qty = round($rs->OnHand, 2);
                $amount = round($cost * $qty, 2);

                $writer->addRow(Row::fromValues([
                  $no,
                  $rs->WhsCode,
                  $whsName,
                  $rs->ItemCode,
                  $rs->ItemName,
                  $cost,
                  $qty,
                  $amount
                ]));

                $no++;
                $rows++;
                $totalQty += $qty;
                $totalAmount += $amount;
              }
            }

            $offset += $limit;
            $max_loop--;
          }
        }

        $writer->addRow(Row::fromValues(['', '', '', '', '', 'รวม', $totalQty, $totalAmount]));
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
}
