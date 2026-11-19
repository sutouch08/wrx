<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use OpenSpout\Writer\XLSX\Writer;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;

class Sales_consign_report extends PS_Controller
{
  public $menu_code = 'RSOCON';
	public $menu_group_code = 'RE';
  public $menu_sub_group_code = 'RESALE';
	public $title = 'รายงานตัดยอดฝากขายแท้ แสดงรายการสินค้า';
  
  public function __construct()
  {
    parent::__construct();
    $this->home = base_url().'report/sales/sales_consign_report';
    $this->load->model('report/sales/sales_consign_report_model');
    $this->load->model('masters/products_model');
    $this->load->model('masters/customers_model');
    $this->load->model('masters/zone_model');
    $this->load->model('masters/warehouse_model');
  }

  public function index()
  {
    $this->load->model('masters/warehouse_model');
    $whList = $this->warehouse_model->get_consign_list();
    $ds['whList'] = $whList;
    $this->load->view('report/sales/sales_consign_report', $ds);
  }


  public function get_report()
  {
    ini_set('memory_limit','2048M');  
    ini_set('max_execution_time', 600);

    $sc = TRUE;

    $bs = array(
      'data' => [],
      'total' => []
    );

    $ds = json_decode($this->input->get('data') ?? '{}');

    $filter = array(
      'fromDate' => $ds->fromDate,
      'toDate' => $ds->toDate,
      'allProduct' => $ds->allProduct,
      'pdFrom' => $ds->pdFrom,
      'pdTo' => $ds->pdTo,
      'allCustomer' => $ds->allCustomer,
      'cusFrom' => $ds->cusFrom,
      'cusTo' => $ds->cusTo,
      'allWarehouse' => $ds->allWhouse,
      'whsList' => $ds->whsList,
      'allZone' => $ds->allZone,
      'zone_code' => $ds->zoneCode
    );

    $limit = 1000;
    $offset = 0;
    $rows = $this->sales_consign_report_model->count_rows($filter);     
    
    if($rows > 2000)
    {
      $sc = FALSE;
      $this->error = "ข้อมูลมีปริมาณมากเกินกว่าจะแสดงผลได้ กรุณาส่งออกข้อมูลแทนการแสดงผลหน้าจอ";
    }

    if($rows === 0)
    {
      $bs[] = array('nodata' => 'nodata');
    }

    if($sc === TRUE && $rows > 0)
    {
      $no = 1;
      $totalQty = 0;
      $totalDiscount = 0;
      $totalAmount = 0;
      $totalCost = 0;

      $custTemp = [];
      $whTemp = [];
      $zoneTemp = [];

      while($rows > 0)
      {
        $result = $this->sales_consign_report_model->get_data($filter, $limit, $offset);        

        if(! empty($result))
        {
          $rows -= count($result);
          $offset += count($result);

          foreach ($result as $rs)
          {
            if(!in_array($rs->customer_code, $custTemp))
            {
              $custTemp[$rs->customer_code] = $this->customers_model->get_name($rs->customer_code);
            }

            if(!in_array($rs->warehouse_code, $whTemp))
            {
              $whTemp[$rs->warehouse_code] = $this->warehouse_model->get_name($rs->warehouse_code);
            }

            if(!in_array($rs->zone_code, $zoneTemp))
            {
              $zoneTemp[$rs->zone_code] = $this->zone_model->get_name($rs->zone_code);
            }

            $bs['data'][] = array(
              'no' => number($no),
              'date_add' => thai_date($rs->date_add, FALSE),
              'reference' => $rs->reference,
              'product_code' => $rs->product_code,
              'product_name' => $rs->product_name,
              'cost' => number($rs->cost, 2),
              'price' => number($rs->price, 2),
              'discount_label' => $rs->discount_label,
              'sell' => number($rs->sell, 2),
              'qty' => number($rs->qty),
              'total_discount' => number(($rs->discount_amount * $rs->qty), 2),
              'total_amount' => number($rs->total_amount, 2),
              'total_cost' => number($rs->total_cost, 2),
              'customer_code' => $rs->customer_code,
              'customer_name' => $custTemp[$rs->customer_code],
              'warehouse_code' => $rs->warehouse_code,
              'warehouse_name' => $whTemp[$rs->warehouse_code],
              'zone_code' => $rs->zone_code,
              'zone_name' => $zoneTemp[$rs->zone_code]
            );
           
            $totalQty += $rs->qty;
            $totalDiscount += ($rs->qty * $rs->discount_amount);
            $totalAmount += $rs->total_amount;
            $totalCost += $rs->total_cost;
            $no++;            
          }          
        }
      }

      $bs['total'] = array(
        'totalQty' => number($totalQty),
        'totalDiscount' => number($totalDiscount, 2),
        'totalAmount' => number($totalAmount, 2),
        'totalCost' => number($totalCost, 2)
      );      
    }

    echo $sc === TRUE ? json_encode($bs) : $this->error;
  }

  public function do_export()
  {
    ini_set('memory_limit', '2048M');
    ini_set('max_execution_time', 600);

    $token = $this->input->post('token');
    $ds = json_decode($this->input->post('data') ?? '{}');

    $filter = array(
      'fromDate' => $ds->fromDate,
      'toDate' => $ds->toDate,
      'allProduct' => $ds->allProduct,
      'pdFrom' => $ds->pdFrom,
      'pdTo' => $ds->pdTo,
      'allCustomer' => $ds->allCustomer,
      'cusFrom' => $ds->cusFrom,
      'cusTo' => $ds->cusTo,
      'allWarehouse' => $ds->allWhouse,
      'whsList' => $ds->whsList,
      'allZone' => $ds->allZone,
      'zone_code' => $ds->zoneCode
    );

    $wh_list = '';

    if (!empty($ds->whsList) && empty($ds->zoneCode))
    {
      $i = 1;
      foreach ($ds->whsList as $wh)
      {
        $wh_list .= $i === 1 ? $wh : ', ' . $wh;
        $i++;
      }
    }
   
    $report_title = "รายงานตัดยอดฝากขายแท้";
    $whList = $ds->allWhouse == 1 ? 'ทั้งหมด' : $wh_list;
    $zoneList = $ds->allZone == 1 ? 'ทั้งหมด' : $ds->zoneCode . " - " . $ds->zoneName;    
    $dateRange = "วันที่ " . thai_date($ds->fromDate, FALSE, '/') . ' - ' . thai_date($ds->toDate, FALSE, '/');
    $cusList = $ds->allCustomer == 1 ? 'ทั้งหมด' : "({$ds->cusFrom}) - ({$ds->cusTo})";

    $tempFile = tempnam(sys_get_temp_dir(), 'sales-consign-report-');
    $fileName = "sales-consign-report.xlsx";
    $writer = new Writer();
    $writer->openToFile($tempFile);
    $style = (new Style())->setShouldWrapText(false);
    $writer->getOptions()->DEFAULT_ROW_STYLE = $style;
    $sheet = $writer->getCurrentSheet();
    $sheet->setName('Sales Consign Report');

    $writer->addRow(Row::fromValues([$report_title]));
    $writer->addRow(Row::fromValues(["คลัง: {$whList}"]));
    $writer->addRow(Row::fromValues(["โซน: {$zoneList}"]));
    $writer->addRow(Row::fromValues(["ลูกค้า: {$cusList}"]));
    $writer->addRow(Row::fromValues([$dateRange]));
    $header = ["ลำดับ", "วันที่", "เลขที่", "รหัส", "สินค้า", "ทุน", "ราคา", "ขาย", "จำนวน", "ส่วนลด", "มูลค่าส่วนลด", "มูลค่ารวม", "ทุนรวม", "รหัสลูกค้า", "ชื่อลูกค้า", "รหัสคลัง", "ชื่อคลัง", "รหัสโซน", "ชื่อโซน"];
    $writer->addRow(Row::fromValues(['']));
    $writer->addRow(Row::fromValues($header));

    try
    {
      $writer->getOptions()->mergeCells(0, 1, 19, 1);
      $writer->getOptions()->mergeCells(0, 2, 19, 2);
      $writer->getOptions()->mergeCells(0, 3, 19, 3);
      $writer->getOptions()->mergeCells(0, 4, 19, 4);
      $writer->getOptions()->mergeCells(0, 5, 19, 5);
      $writer->getOptions()->mergeCells(0, 6, 19, 6);

      $sheet->setColumnWidth(7, 1);
      $sheet->setColumnWidth(12, 2);
      $sheet->setColumnWidth(15, 3);
      $sheet->setColumnWidth(22, 4);
      $sheet->setColumnWidth(80, 5);
      $sheet->setColumnWidthForRange(12, 6, 14);
      $sheet->setColumnWidth(60, 15);
      $sheet->setColumnWidth(12, 16);
      $sheet->setColumnWidth(60, 17);
      $sheet->setColumnWidth(15, 18);
      $sheet->setColumnWidth(50, 19);

      $count = $this->sales_consign_report_model->count_rows($filter);

      if ($count > 0)
      {
        $rows = 0;
        $limit = 10000;
        $offset = 0;
        $max_loop = 100;
        $no = 1;

        $custTemp = [];
        $whTemp = [];
        $zoneTemp = [];

        while ($rows < $count && $max_loop > 0)
        {
          $result = $this->sales_consign_report_model->get_data($filter, $limit, $offset);

          if (! empty($result))
          {
            foreach ($result as $rs)
            {
              if (!in_array($rs->customer_code, $custTemp))
              {
                $custTemp[$rs->customer_code] = $this->customers_model->get_name($rs->customer_code);
              }

              if (!in_array($rs->warehouse_code, $whTemp))
              {
                $whTemp[$rs->warehouse_code] = $this->warehouse_model->get_name($rs->warehouse_code);
              }

              if (!in_array($rs->zone_code, $zoneTemp))
              {
                $zoneTemp[$rs->zone_code] = $this->zone_model->get_name($rs->zone_code);
              }

              $row = [
                $no,
                thai_date($rs->date_add, FALSE),
                $rs->reference,
                $rs->product_code,
                $rs->product_name,
                round($rs->cost, 2),
                round($rs->price, 2),
                round($rs->sell, 2),
                round($rs->qty, 2),
                $rs->discount_label,
                round($rs->discount_amount, 2),
                round($rs->total_amount, 2),
                round($rs->total_cost, 2),
                $rs->customer_code,
                (empty($rs->customer_code) ? '' : $custTemp[$rs->customer_code]),
                $rs->warehouse_code,
                (empty($rs->warehouse_code) ? '' : $whTemp[$rs->warehouse_code]),
                $rs->zone_code,
                (empty($rs->zone_code) ? '' : $zoneTemp[$rs->zone_code])
              ];

              $writer->addRow(Row::fromValues($row));
              $no++;
              $rows++;
              $offset++;
            }
          }

          $max_loop--;
        }
      }

      $writer->close();
      setToken($token);
      while (ob_get_level()) ob_end_clean();
      header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      header('Content-Disposition: attachment;filename="' . $fileName . '"');
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
