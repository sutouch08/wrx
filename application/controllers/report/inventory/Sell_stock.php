<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use OpenSpout\Writer\XLSX\Writer;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;

class Sell_stock extends PS_Controller
{
  public $menu_code = 'RICSST';
	public $menu_group_code = 'RE';
  public $menu_sub_group_code = 'REINVT';
	public $title = 'รายงานสินค้าคงเหลือหักยอดจอง';
  
  public function __construct()
  {
    parent::__construct();
    $this->home = base_url().'report/inventory/sell_stock';
    $this->load->model('report/inventory/inventory_report_model');
    $this->load->model('masters/products_model');
    $this->load->model('orders/orders_model');
    $this->load->model('masters/warehouse_model');
  }

  public function index()
  {
    $order_by = 'code';
    $direction = 'ASC';
    $ds['whList'] = $this->warehouse_model->get_sell_warehouse_list($order_by, $direction);
    $this->load->view('report/inventory/report_sell_stock', $ds);
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
    ini_set('memory_limit','2048M'); 
    ini_set('sqlsrv.ClientBufferMaxKBSize','2097152');
    ini_set('sqlsrv.client_buffer_max_kb_size','2097152');
    
    $allProduct = $this->input->post('allProduct');
    $pdFrom = $this->input->post('pdFrom');
    $pdTo = $this->input->post('pdTo');
    $allWhouse = $this->input->post('allWhouse');
    $warehouse = $this->input->post('warehouse');

    $filter = array(
      'allProduct' => $allProduct,
      'pdFrom' => $pdFrom,
      'pdTo' => $pdTo,
      'allWhouse' => $allWhouse,
      'warehouse' => $warehouse
    );

    $count = $this->inventory_report_model->count_rows($filter);

    if($count > 2000)
    {
      echo "ผลลัพธ์ของรายงานมีมากกว่า ".number($count)." รายการ กรุณาส่งออกเป็นไฟล์ Excel แทนการแสดงผลหน้าจอ";
      exit;
    }

    $bs = [];
    $rows = 0;
    $limit = 10000;
    $offset = 0;
    $max_loop = 100;
    $totalOnHand = 0;
    $totalReserv = 0;
    $totalQty = 0;
    $totalAmount = 0;
    $no = 1;

    if($count > 0)
    {
      while($rows < $count && $max_loop > 0)
      {
        $data = $this->inventory_report_model->get_stock_balance($filter, $limit, $offset);

        if(!empty($data))
        {
          foreach($data as $rs)
          {
            $qty = round($rs->OnHand, 2);
            $cost = round($rs->Cost, 2);
            $reserv_stock = round($this->inventory_report_model->get_reserv_stock($rs->ItemCode, $rs->WhsCode), 2);
            $availableStock = $qty - $reserv_stock;
            $amount = $cost * $availableStock;
            
            $bs[] = array(
              'no' => number($no),
              'pdCode' => $rs->ItemCode,
              'pdName' => $rs->ItemName,
              'whsCode' => $rs->WhsCode,
              'cost' => number($cost, 2),
              'onHand' => number($qty, 2),
              'reserv' => number($reserv_stock, 2),
              'available' => number($availableStock, 2),
              'amount' => number($amount, 2)
            );
            
            $no++;
            $rows++;
            $totalOnHand += $qty;
            $totalReserv += $reserv_stock;
            $totalQty += $availableStock;
            $totalAmount += $amount;
          }
        }      

        $offset += $limit;
        $max_loop--;
      }

      $bs[] = array(
        'totalOnHand' => number($totalOnHand, 2),
        'totalReserv' => number($totalReserv, 2),
        'totalQty' => number($totalQty),
        'totalAmount' => number($totalAmount, 2)
      );
    }

    echo json_encode($bs);
  }


  public function do_export()
  {
    ini_set('memory_limit','2048M');
    ini_set('max_execution_time', 1200); // Set maximum execution time to 5 minutes
    ini_set('sqlsrv.ClientBufferMaxKBSize','2097152'); // Setting to 2048M
    ini_set('sqlsrv.client_buffer_max_kb_size','2097152'); // Setting to 512M - for pdo_sqlsrv

    $token = $this->input->post('token');
    $ds = json_decode($this->input->post('data') ?? '{}');

    $allProduct = $ds->allProduct ?? 1;
    $pdFrom = $ds->pdFrom ?? '';
    $pdTo = $ds->pdTo ?? '';
    $allWhouse = $ds->allWhouse ?? 1;
    $warehouse = $ds->warehouse ?? [];

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

    $report_title = 'รายงานสินค้าคงเหลือหักยอดจอง ณ วันที่  '.thai_date(date('Y-m-d'), '/');
    $wh_title = 'คลัง :  '. ($allWhouse == 1 ? 'ทั้งหมด' : $wh_list);
    $pd_title = 'สินค้า :  '. ($allProduct == 1 ? 'ทั้งหมด' : '('.$pdFrom.') - ('.$pdTo.')');

    $tempFile = tempnam(sys_get_temp_dir(), 'sell_stock_');
    $fileName = "report-sell-stock.xlsx";

    try
    {
      $writer = new Writer();
      $writer->openToFile($tempFile);
      $sheet = $writer->getCurrentSheet();
      $sheet->setName("Sell Stock");
      $style = (new Style())->setShouldWrapText(false);
      $writer->getOptions()->DEFAULT_ROW_STYLE = $style;

      $writer->addRow(Row::fromValues([$report_title]));
      $writer->addRow(Row::fromValues([$wh_title]));
      $writer->addRow(Row::fromValues([$pd_title]));
      $writer->addRow(Row::fromValues(['']));
      $writer->addRow(Row::fromValues(['#', 'รหัสคลัง', 'รหัสสินค้า', 'สินค้า', 'ราคาทุน', 'คงเหลือ', 'จอง', 'พร้อมขาย', 'มูลค่า']));

      $writer->getOptions()->mergeCells(0, 1, 8, 1);
      $writer->getOptions()->mergeCells(0, 2, 8, 2);
      $writer->getOptions()->mergeCells(0, 3, 8, 3);

      $sheet->setColumnWidth(7, 1);
      $sheet->setColumnWidth(12, 2);
      $sheet->setColumnWidth(25, 3);
      $sheet->setColumnWidth(60, 4);
      $sheet->setColumnWidth(12, 5);
      $sheet->setColumnWidth(12, 6);
      $sheet->setColumnWidth(12, 7);
      $sheet->setColumnWidth(12, 8);
      $sheet->setColumnWidth(12, 9);

      $count = $this->inventory_report_model->count_rows($filter);
      $rows = 0;
      $limit = 10000;
      $offset = 0;
      $max_loop = 100;
      $no = 1;
      $totalOnHand = 0;
      $totalReserved = 0;
      $totalQty = 0;
      $totalAmount = 0;
      
      if ($count > 0)
      {
        if ($count > 0)
        {
          while ($rows < $count && $max_loop > 0)
          {
            $data = $this->inventory_report_model->get_stock_balance($filter, $limit, $offset);

            if (! empty($data))
            {
              foreach ($data as $rs)
              {                
                $cost = round($rs->Cost, 2);
                $qty = round($rs->OnHand, 2);
                $reserved = round($this->inventory_report_model->get_reserv_stock($rs->ItemCode, $rs->WhsCode), 2);
                $available = $qty - $reserved;
                $amount = round($cost * $available, 2);

                $writer->addRow(Row::fromValues([
                  $no,
                  $rs->WhsCode,                  
                  $rs->ItemCode,
                  $rs->ItemName,
                  $cost,
                  $qty,
                  $reserved,
                  $available,
                  $amount
                ]));

                $no++;
                $rows++;
                $totalOnHand += $qty;
                $totalReserved += $reserved;
                $totalQty += $available;
                $totalAmount += $amount;
              }
            }

            $offset += $limit;
            $max_loop--;
          }
        }

        $writer->addRow(Row::fromValues(['', '', '', '', 'รวม', $totalOnHand, $totalReserved, $totalQty, $totalAmount]));
      }

      $writer->close();
      setToken($token);
      while (ob_get_level()) ob_end_clean();
      header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      header('Content-Disposition: attachment; filename="' . $fileName . '"');
      readfile($tempFile);
    }
    finally
    {
      if (file_exists($tempFile))
      {
        @unlink($tempFile);
      }
    }

    exit;    
  }
}