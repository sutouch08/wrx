<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use OpenSpout\Writer\Xlsx\Writer;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;

class Stock_balance extends PS_Controller
{
  public $menu_code = 'RICSTB';
	public $menu_group_code = 'RE';
  public $menu_sub_group_code = 'REINVT';
	public $title = 'รายงานสินค้าคงเหลือ';
 
  public function __construct()
  {
    parent::__construct();
    $this->home = base_url().'report/inventory/stock_balance';
    $this->load->model('report/inventory/inventory_report_model');
    $this->load->model('masters/products_model');
    $this->load->model('masters/warehouse_model');
  }

  public function index()
  {    
    $ds['whList'] = $this->warehouse_model->get_all_warehouse_list();
    $this->load->view('report/inventory/report_stock_balance', $ds);
  }


  public function get_report()
  {
    ini_set('memory_limit','2048M'); 
    ini_set('max_execution_time', 600);
    ini_set('sqlsrv.ClientBufferMaxKBSize','2097152');
    ini_set('sqlsrv.client_buffer_max_kb_size','2097152');

    $bs = [];

    $allProduct = $this->input->get('allProduct');
    $pdFrom = $this->input->get('pdFrom');
    $pdTo = $this->input->get('pdTo');
    $allWhouse = $this->input->get('allWhouse');
    $warehouse = $this->input->get('warehouse');

    $filter = array(
      'allProduct' => $allProduct,
      'pdFrom' => $pdFrom,
      'pdTo' => $pdTo,
      'allWhouse' => $allWhouse,
      'warehouse' => $warehouse
    );
    
    $count = $this->inventory_report_model->count_rows($filter);
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
        $result = $this->inventory_report_model->get_stock_balance($filter, $limit, $offset);

        if( ! empty($result))
        {
          foreach($result as $rs)
          {
            $item = $this->products_model->get_item($rs->product_code);

            if(!empty($item))
            {
              $bs[] = array(
                'no' => number($no),
                'barcode' => $item->barcode,
                'pdCode' => $item->code,
                'pdName' => $item->name,
                'cost' => number($item->cost, 2),
                'qty' => number($rs->qty),
                'amount' => number($item->cost * $rs->qty, 2)
              );

              $no++;

              $totalQty += $rs->qty;
              $totalAmount += ($rs->qty * $item->cost);
            }

            $rows++;
            $offset++;
          }
        }

        $max_loop--;
      }

      $bs[] = array(
        'totalQty' => number($totalQty),
        'totalAmount' => number($totalAmount, 2)
      );
    }
    else 
    {
      $bs[] = array('nodata' => 'nodata');
    }

    echo json_encode(['bs' => $bs]);
  }


  public function do_export()
  {
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

    $wh_list = '';

    if(!empty($warehouse))
    {
      $i = 1;
      foreach($warehouse as $wh)
      {
        $wh_list .= $i === 1 ? $wh : ', '.$wh;
        $i++;
      }
    }
    
    $report_title = 'รายงานสินค้าคงเหลือ ณ วันที่  '.thai_date(date('Y-m-d'), '/');
    $wh_title = 'คลัง :  '. ($allWhouse == 1 ? 'ทั้งหมด' : $wh_list);
    $pd_title = 'สินค้า :  '. ($allProduct == 1 ? 'ทั้งหมด' : '('.$pdFrom.') - ('.$pdTo.')');

    $tempFile = tempnam(sys_get_temp_dir(), 'stock_balance_');
    $fileName = "Report-stock-balance.xlsx";
    $writer = new writer();
    $writer->openToFile($tempFile);
    $style = (new Style())->setShouldWrapText(false);
    $writer->getOptions()->DEFAULT_ROW_STYLE = $style;
    $sheet  = $writer->getCurrentSheet();
    $sheet->setName("Stock Balance Report");

    $writer->addRow(Row::fromValues([$report_title]));
    $writer->addRow(Row::fromValues([$wh_title]));
    $writer->addRow(Row::fromValues([$pd_title]));
    $writer->addRow(Row::fromValues(['']));
    $writer->addRow(Row::fromValues(['ลำดับ', 'บาร์โค้ด', 'รหัส', 'สินค้า', 'ทุน', 'จำนวน', 'มูลค่า']));

    $writer->getOptions()->mergeCells(0, 1, 6, 1);
    $writer->getOptions()->mergeCells(0, 2, 6, 2);
    $writer->getOptions()->mergeCells(0, 3, 6, 3);

    $sheet->setColumnWidth(7, 1);
    $sheet->setColumnWidth(15, 2);
    $sheet->setColumnWidth(22, 3);
    $sheet->setColumnWidth(60, 4);
    $sheet->setColumnWidthForRange(12, 5, 7);

    $count = $this->inventory_report_model->count_rows($filter);
    $rows = 0;
    $limit = 10000;
    $offset = 0;
    $max_loop = 100;
    $no = 1;
    $totalQty = 0;
    $totalAmouont = 0;
    
    try 
    {
      if($count > 0)
      {
        while($rows < $count && $max_loop > 0)
        {
          $result = $this->inventory_report_model->get_stock_balance($filter, $limit, $offset);

          if(! empty($result))
          {
            foreach($result as $rs)
            {
              $item = $this->products_model->get_item($rs->product_code);

              if(!empty($item))
              {
                $cost = round($item->cost, 2);
                $qty = round($rs->qty, 2);
                $amount = round($cost * $qty, 2);

                $writer->addRow(Row::fromValues([
                  intval($no),
                  $item->barcode,
                  $item->code,
                  $item->name,
                  $cost,
                  $qty,
                  $amount
                ]));

                $no++;
                $totalQty += $qty;
                $totalAmouont += $amount;
              }

              $rows++;
              $offset++;
            }
          }
          
          $max_loop--;
        }

        $writer->addRow(Row::fromValues(['รวม', '', '', '', '', $totalQty, $totalAmouont]));
        $writer->getOptions()->mergeCells(0, $rows + 6, 4, $rows + 6);
      }

      $writer->close();
      setToken($token);
      while(ob_get_level()) ob_end_clean();
      header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      header('Content-Disposition: attachment;filename="'.$fileName.'"');
      readFile($tempFile);
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