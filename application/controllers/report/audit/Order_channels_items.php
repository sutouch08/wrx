<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use OpenSpout\Writer\XLSX\Writer;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;

class Order_channels_items extends PS_Controller
{
  public $menu_code = 'RSODER';
	public $menu_group_code = 'RE';
  public $menu_sub_group_code = 'RESALE';
	public $title = 'รายงานออเดอร์แยกตามช่องทางขายแสดงรายการสินค้า';
  
  public function __construct()
  {
    parent::__construct();
    $this->home = base_url().'report/audit/order_channels_items';
    $this->load->model('report/audit/order_channels_items_model');
    $this->load->model('masters/products_model');
    $this->load->model('orders/orders_model');
    $this->load->model('masters/channels_model');
    $this->load->model('masters/payment_methods_model');
    $this->load->model('masters/warehouse_model');
  }

  public function index()
  {
    $ds = array(
      'channels_list' => $this->channels_model->get_all(),
      'payment_list' => $this->payment_methods_model->get_all(),
      'warehouse_list' => $this->warehouse_model->get_sell_warehouse_list()
    );

    $this->load->view('report/audit/report_order_channels_items', $ds);
  }


  public function do_export()
  {
    ini_set('memory_limit','2048M');
    ini_set('max_execution_time', 600);
    ini_set('sqlsrv.ClientBufferMaxKBSize','2097152');
    ini_set('sqlsrv.client_buffer_max_kb_size','2097152');

    $allProducts = $this->input->post('allProducts');
    $pdFrom = $this->input->post('pdFrom');
    $pdTo = $this->input->post('pdTo');

    $allWarehouse = $this->input->post('allWarhouse');
    $warehouse = $this->input->post('warehouse');

    $allChannels = $this->input->post('allChannels');
    $channels = $this->input->post('channels');

    $allPayments = $this->input->post('allPayments');
    $payments = $this->input->post('payments');

    $fromDate = $this->input->post('fromDate');
    $toDate = $this->input->post('toDate');

    $token = $this->input->post('token');

    $post_state = $this->input->post('state');

    $state_name = array(
      '1' => 'รอดำเนินการ',
      '2' => 'รอชำระเงิน',
      '3' => 'รอจัด',
      '4' => 'กำลังจัด',
      '5' => 'รอตรวจ',
      '6' => 'กำลังตรวจ',
      '7' => 'รอเปิดบิล',
      '8' => 'เปิดบิลแล้ว',
      '9' => 'ยกเลิก'
    );

    $ch_name = $this->order_channels_items_model->channels_array();
    $pm_name = $this->order_channels_items_model->payment_array();

    $state = array();
    $state_list = array();

    if(!empty($post_state))
    {
      foreach($post_state AS $key => $val)
      {
        if($val == 1)
        {
          $state[] = $key;
          $state_list[] = $state_name[$key];
        }
      }
    }
   
    $report_title = "รายงานออเดอร์แยกตามช่องทางขายแสดงรายการสินค้า";
    $wh_title = 'คลัง :  '. ($allWarehouse == 1 ? 'ทั้งหมด' : $this->get_title($warehouse));
    $pd_title = 'สินค้า :  '. ($allProducts == 1 ? 'ทั้งหมด' : '('.$pdFrom.') - ('.$pdTo.')');
    $ch_title = "ช่องทางขาย : ". ($allChannels == 1 ? 'ทั้งหมด' : $this->get_title($channels));
    $pay_title = "การชำระเงิน : ". ($allPayments == 1 ? "ทั้งหมด" : $this->get_title($payments));
    $state_title = "สถานะ : ".(empty($post_state) ? "ทั้งหมด" : $this->get_title($state_list));

    $ds = array(
      'allChannels' => $allChannels,
      'channels' => $channels,
      'allPayments' => $allPayments,
      'payments' => $payments,
      'allWarehouse' => $allWarehouse,
      'warehouse' => $warehouse,
      'allProducts' => $allProducts,
      'pdFrom' => $pdFrom,
      'pdTo' => $pdTo,
      'fromDate' => $fromDate,
      'toDate' => $toDate,
      'state' => $state
    );
    
    $count = $this->order_channels_items_model->count_rows($ds);    
    $rows = 0;
    $limit = 10000;
    $offset = 0;
    $max_loop = 100;
    $tempFile = tempnam(sys_get_temp_dir(), 'order_channels_items_');
    $fileName = "Report Order Channels Details.xlsx";

    $header = [
      'ลำดับ',
      'วันที่',
      'เอกสาร',
      'อ้างอิง',
      'เลขที่จัดส่ง',
      'ชื่อลูกค้า',
      'ช่องทางขาย',
      'ช่องทางการชำระเงิน',
      'สินค้า',
      'ราคา',
      'จำนวน',
      'ส่วนลด',
      'มูลค่า',
      'ค่าจัดส่ง',
      'ค่าบริการ',
      'สถานะ',
      'เหตุผลในการยกเลิก'
    ];

    $writer = new Writer();
    $writer->openToFile($tempFile);
    $style = (new Style())->setShouldWrapText(false);
    $writer->getOptions()->DEFAULT_ROW_STYLE = $style;
    $sheet = $writer->getCurrentSheet();
    $sheet->setName('Sales Online Channels Report');

    try
    {
      $writer->addRow(Row::fromValues([$report_title]));
      $writer->addRow(Row::fromValues([$ch_title]));
      $writer->addRow(Row::fromValues([$pay_title]));
      $writer->addRow(Row::fromValues([$pd_title]));
      $writer->addRow(Row::fromValues([$wh_title]));
      $writer->addRow(Row::fromValues([$state_title]));
      $writer->addRow(Row::fromValues(['วันที่เอกสาร : ('.thai_date($fromDate,'/') .') - ('.thai_date($toDate,'/').')']));
      $writer->addRow(Row::fromValues(['']));
      $writer->addRow(Row::fromValues($header));

      $writer->getOptions()->mergeCells(0, 1, 17, 1);
      $writer->getOptions()->mergeCells(0, 2, 17, 2);
      $writer->getOptions()->mergeCells(0, 3, 17, 3);
      $writer->getOptions()->mergeCells(0, 4, 17, 4);
      $writer->getOptions()->mergeCells(0, 5, 17, 5);
      $writer->getOptions()->mergeCells(0, 6, 17, 6);
      $writer->getOptions()->mergeCells(0, 7, 17, 7);

      $sheet->setColumnWidth(7, 1);
      $sheet->setColumnWidth(12, 2);
      $sheet->setColumnWidth(15, 3);
      $sheet->setColumnWidth(22, 4);
      $sheet->setColumnWidth(22, 5);
      $sheet->setColumnWidth(80, 6);
      $sheet->setColumnWidth(22, 7);
      $sheet->setColumnWidth(22, 8);
      $sheet->setColumnWidth(31, 9);
      $sheet->setColumnWidthForRange(12, 10, 15);     
      $sheet->setColumnWidth(15, 16);
      $sheet->setColumnWidth(50, 17);      

      if($count > 0)
      {
        $no = 1;

        while($rows < $count && $max_loop > 0)
        {
          $result = $this->order_channels_items_model->get_data($ds, $limit, $offset);
          
          if( ! empty($result))
          {
            foreach($result as $rs)
            {
              $writer->addRow(Row::fromValues([
                $no,
                thai_date($rs->date_add, FALSE),
                $rs->code,
                $rs->reference,
                $rs->shipping_code,
                $rs->customer_code.' | '.$rs->customer_name,
                !empty($rs->channels_code) ? (isset($ch_name[$rs->channels_code]) ? $ch_name[$rs->channels_code] : '') : '',
                !empty($rs->payment_code) ? (isset($pm_name[$rs->payment_code]) ? $pm_name[$rs->payment_code] : '') : '',
                $rs->product_code,
                round($rs->price, 2),
                round($rs->qty, 2),
                round($rs->discount_amount, 2),
                round($rs->total_amount, 2),
                round($rs->shipping_fee, 2),
                round($rs->service_fee, 2),
                $state_name[$rs->state],
                $rs->state == 9 ? $this->order_channels_items_model->cancel_reason($rs->code) : ''
              ]));

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


  public function get_title($ds = array())
  {
    $list = "";
    if(!empty($ds))
    {
      $i = 1;
      foreach($ds as $rs)
      {
        $list .= $i === 1 ? $rs : ", {$rs}";
        $i++;
      }
    }

    return $list;
  }
}
