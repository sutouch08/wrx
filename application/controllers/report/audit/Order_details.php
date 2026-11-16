<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use OpenSpout\Writer\XLSX\Writer;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;

class Order_details extends PS_Controller
{
  public $menu_code = 'RAODDT';
	public $menu_group_code = 'RE';
  public $menu_sub_group_code = 'REAUDIT';
	public $title = 'รายงานสถานะเอกสารขาออก แสดงรายละเอียด';
 
  public function __construct()
  {
    parent::__construct();
    $this->home = base_url().'report/audit/order_details';
    $this->load->model('report/audit/order_details_model');
    $this->load->model('masters/products_model');
    $this->load->model('orders/orders_model');
    $this->load->model('masters/channels_model');
    $this->load->model('masters/payment_methods_model');
    $this->load->model('masters/warehouse_model');
    $this->load->model('masters/customers_model');
  }

  public function index()
  {
    $ds = array(
      'channels_list' => $this->channels_model->get_all(),
      'payment_list' => $this->payment_methods_model->get_all(),
      'warehouse_list' => $this->warehouse_model->get_sell_warehouse_list()
    );

    $this->load->view('report/audit/report_order_details', $ds);
  }

  private function channels_array()
  {
    $ds = array();
    $rs = $this->db->get('channels');

    if($rs->num_rows() > 0)
    {
      foreach($rs->result() as $rd)
      {
        $ds[$rd->code] = $rd->name;
      }
    }

    return $ds;
  }

  private function payment_array()
  {
    $ds = array();
    $rs = $this->db->get('payment_method');

    if($rs->num_rows() > 0)
    {
      foreach($rs->result() as $rd)
      {
        $ds[$rd->code] = $rd->name;
      }
    }

    return $ds;
  }

  private function warehouse_array()
  {
    $ds = array();
    $rs = $this->db->get('warehouse');

    if($rs->num_rows() > 0)
    {
      foreach($rs->result() as $rd)
      {
        $ds[$rd->code] = $rd->name;
      }
    }

    return $ds;
  }

  private function state_array()
  {
    $ds = array(
      '1' => 'รอดำเนินการ',
      '2' => 'รอชำระเงิน',
      '3' => 'รอจัดสินค้า',
      '4' => 'กำลังจัดสินค้า',
      '5' => 'รอตรวจสินค้า',
      '6' => 'กำลังตรวจสินค้า',
      '7' => 'รอการจัดส่ง',
      '8' => 'จัดส่งแล้ว',
      '9' => 'ยกเลิก'
    );

    return $ds;
  }

  private function cancel_reason($code)
  {
    $rs = $this->db->where('code', $code)->order_by('id', 'DESC')->limit(1)->get('order_cancle_reason');
    return $rs->num_rows() == 1 ? $rs->row()->reason : "";    
  }


  public function get_report()
  {
    ini_set('memory_limit','2048M'); 
    ini_set('max_execution_time', 600);
    ini_set('sqlsrv.ClientBufferMaxKBSize','2097152'); 
    ini_set('sqlsrv.client_buffer_max_kb_size','2097152');

    $sc = TRUE;

    if($this->input->get("json"))
    {
      $json = json_decode($this->input->get("json") ?? '{}');

      if( ! empty($json))
      {
        $filter = array(
          "from_date" => $json->fromDate,
          "to_date" => $json->toDate,
          "all_role" => $json->allRole,
          "role" => $json->role,
          "is_expired" => $json->isExpired,
          "is_preorder" => $json->isPreOrder,
          "all_state" => $json->allState,
          "state" => $json->state,
          "all_channels" => $json->allChannels,
          "channels" => $json->channels,
          "all_payment" => $json->allPayments,
          "payment" => $json->payment,
          "all_warehouse" => $json->allWarehouse,
          "warehouse" => $json->warehouse
        );

        $limit = 1000;
        $rows = $this->order_details_model->count_rows($filter);
        $details = $this->order_details_model->get_data($filter, $limit);
        $ds = array();

        if( ! empty($details))
        {
          $state = $this->state_array();
          $wh = $this->warehouse_array();
          $channels = $this->channels_array();
          $payment = $this->payment_array();
          $cust = array();
          $no = 1;

          foreach($details as $rs)
          {
            $rs->no = number($no);
            $rs->expired = $rs->is_expired == 1 ? 'Y' : 'N';
            $rs->is_preorder = $rs->is_pre_order == 1 ? 'Y' : 'N';
            $rs->date_add = thai_date($rs->date_add, FALSE, '/');
            $rs->date_upd = thai_date($rs->date_upd, FALSE, '/');
            $rs->total_amount = number($this->order_details_model->get_doc_total($rs->code), 2);
            $rs->channels_name = empty($channels[$rs->channels_code ?? '']) ? NULL : $channels[$rs->channels_code];
            $rs->payment_name = empty($payment[$rs->payment_code ?? '']) ? NULL : $payment[$rs->payment_code];
            $rs->warehouse_name = empty($wh[$rs->warehouse_code ?? '']) ? NULL : $wh[$rs->warehouse_code];
            $rs->state_name = $state[$rs->state ?? ''];
            $rs->uname = $rs->user;
            $rs->emp_name = $this->user_model->get_name($rs->user);
            $rs->cancel_reason = $rs->state == 9 ? $this->cancel_reason($rs->code) : NULL;

            if(empty($cust[$rs->customer_code ?? '']))
            {
              $customer_name = $this->customers_model->get_name($rs->customer_code ?? '');
              $cust[$rs->customer_code ?? ''] = $customer_name;
            }

            $rs->customer_name = $cust[$rs->customer_code ?? ''];
            $no++;
          }
        }

        $ds = array(
          "limit" => $limit,
          "rows" => $rows,
          "details" => $details
        );
      }
      else
      {
        $sc = FALSE;
        $this->error = "Invalid request data format";
      }
    }

    echo $sc === TRUE ? json_encode($ds) : $this->error;
  }


  public function do_export()
  {
    ini_set('memory_limit','2048M');
    ini_set('max_execution_time', 600);
    ini_set('sqlsrv.ClientBufferMaxKBSize','2097152');
    ini_set('sqlsrv.client_buffer_max_kb_size','2097152'); 

    $token = $this->input->post('token');
    $allRole = $this->input->post('allRole');
    $allWarehouse = $this->input->post('allWarehouse');
    $allChannels = $this->input->post('allChannels');
    $allPayments = $this->input->post('allPayments');
    $allState = $this->input->post('allState');
    $is_expired = $this->input->post('is_expired');
    $is_preorder = $this->input->post('is_preorder');
    $fromDate = $this->input->post('fromDate');
    $toDate = $this->input->post('toDate');
    $role = $this->input->post('role');
    $channels = $this->input->post('channels');
    $payments = $this->input->post('payments');
    $warehouse = $this->input->post('warehouse');
    $state = $this->input->post('state');

    $filter = array(
      "from_date" => $fromDate,
      "to_date" => $toDate,
      "all_role" => $allRole,
      "role" => $role,
      "is_expired" => $is_expired,
      "is_preorder" => $is_preorder,
      "all_state" => $allState,
      "state" => $state,
      "all_channels" => $allChannels,
      "channels" => $channels,
      "all_payment" => $allPayments,
      "payment" => $payments,
      "all_warehouse" => $allWarehouse,
      "warehouse" => $warehouse
    );
    
    //---  Report title
    $report_title = "รายงานสถานะเอกสารแสดงรายละเอียด";
    $role_title = "เอกสาร : " . ($allRole == 1 ? 'ทั้งหมด' : $this->get_role_title($role));
    $expire_title = "อายุเอกสาร : " . ($is_expired == 'all' ? 'ทั้งหมด' : ($is_expired == 1 ? 'เฉพาะที่หมดอายุ' : 'เฉพาะที่ไม่หมดอายุ'));
    $wh_title     = 'คลัง :  ' . ($allWarehouse == 1 ? 'ทั้งหมด' : $this->get_title($warehouse));
    $ch_title = "ช่องทางขาย : " . ($allChannels == 1 ? 'ทั้งหมด' : $this->get_title($channels));
    $pay_title = "การชำระเงิน : " . ($allPayments == 1 ? "ทั้งหมด" : $this->get_title($payments));
    $state_title = "สถานะ : " . ($allState == 1 ? "ทั้งหมด" : $this->get_state_title($state));
    $date_title = "วันที่เอกสาร : (" . thai_date($fromDate, '/') . ") - (" . thai_date($toDate, '/') . ")";

    $count = $this->order_details_model->count_rows($filter);
    
    $tempFile = tempnam(sys_get_temp_dir(), 'item_reserved_');
    $writer = new Writer();
    $writer->openToFile($tempFile);
    $style = (new Style())->setShouldWrapText(false);
    $writer->getOptions()->DEFAULT_ROW_STYLE = $style;
    

    try 
    {
      $header = ['#', 'วันที่', 'เลขที่', 'อ้างอิง', 'มูลค่า', 'รหัสลูกค้า', 'ชื่อลูกค้า', 'สถานะ', 'หมดอายุ', 'วันที่แก้ไข', 'ช่องทางขาย', 'การชำระเงิน', 'รหัสคลัง', 'ชื่อคลัง', 'Username', 'พนักงาน', 'หมายเหตุ', 'cancel reason', 'Pre-order', 'DO No'];

      $writer->addRow(Row::fromValues([$report_title]));
      $writer->addRow(Row::fromValues([$role_title]));
      $writer->addRow(Row::fromValues([$expire_title]));
      $writer->addRow(Row::fromValues([$state_title]));
      $writer->addRow(Row::fromValues([$ch_title]));
      $writer->addRow(Row::fromValues([$pay_title]));
      $writer->addRow(Row::fromValues([$wh_title]));
      $writer->addRow(Row::fromValues([$date_title]));
      $writer->addRow(Row::fromValues(['']));
      $writer->addRow(Row::fromValues($header));

      $writer->getOptions()->mergeCells(0, 1, 19, 1);
      $writer->getOptions()->mergeCells(0, 2, 19, 2);
      $writer->getOptions()->mergeCells(0, 3, 19, 3);
      $writer->getOptions()->mergeCells(0, 4, 19, 4);
      $writer->getOptions()->mergeCells(0, 5, 19, 5);
      $writer->getOptions()->mergeCells(0, 6, 19, 6);
      $writer->getOptions()->mergeCells(0, 7, 19, 7);
      $writer->getOptions()->mergeCells(0, 8, 19, 8);

      $sheet = $writer->getCurrentSheet();
      $sheet->setColumnWidth(7, 1);
      $sheet->setColumnWidth(12, 2);
      $sheet->setColumnWidth(15, 3);
      $sheet->setColumnWidth(20, 4);
      $sheet->setColumnWidth(15, 5);
      $sheet->setColumnWidth(12, 6);
      $sheet->setColumnWidth(70, 7);
      $sheet->setColumnWidth(15, 8);
      $sheet->setColumnWidthForRange(15, 9, 12);
      $sheet->setColumnWidth(10, 13);
      $sheet->setColumnWidth(50, 14);
      $sheet->setColumnWidth(20, 15);
      $sheet->setColumnWidth(50, 16);
      $sheet->setColumnWidth(90, 17);
      $sheet->setColumnWidth(40, 18);
      $sheet->setColumnWidth(10, 19);
      $sheet->setColumnWidth(10, 20);      

      $limit = 10000;
      $offset = 0;
      $max_loop = 100;
      $rows = 0;
     
      if ($count > 0)
      {
        $state = $this->state_array();
        $wh = $this->warehouse_array();
        $channels = $this->channels_array();
        $payment = $this->payment_array();
        $cust = array();
        $no = 1;

        while ($rows < $count && $max_loop > 0)
        {
          $details = $this->order_details_model->get_data($filter, $limit, $offset);

          if (!empty($details))
          {
            foreach ($details as $rs)
            {
              if (!empty($rs->customer_code) && empty($cust[$rs->customer_code]))
              {
                $customer_name = $this->customers_model->get_name($rs->customer_code);
                $cust[$rs->customer_code] = $customer_name;
              }

              $writer->addRow(Row::fromValues([
                $no,
                thai_date($rs->date_add),
                $rs->code,
                $rs->reference,
                round($this->order_details_model->get_doc_total($rs->code) ?? 0.00, 2),
                $rs->customer_code ?? '',
                empty($rs->customer_code) ? '' : $cust[$rs->customer_code],
                $state[$rs->state],
                $rs->is_expired == 1 ? 'Y' : 'N',
                thai_date($rs->date_upd),
                empty($rs->channels_code) ? '' : (empty($channels[$rs->channels_code]) ? NULL : $channels[$rs->channels_code]),
                empty($rs->payment_code) ? '' : (empty($payment[$rs->payment_code]) ? NULL : $payment[$rs->payment_code]),
                $rs->warehouse_code,
                empty($rs->warehouse_code) ? '' : (empty($wh[$rs->warehouse_code]) ? NULL : $wh[$rs->warehouse_code]),
                $rs->user,
                $this->user_model->get_name($rs->user),
                $rs->remark,
                $rs->state == 9 ? $this->cancel_reason($rs->code) : '',
                $rs->is_pre_order == 1 ? 'Y' : 'N',
                $rs->inv_code
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
      while (ob_get_level()) ob_end_clean();
      $file_name = "Report Order Details.xlsx";
      header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); 
      header('Content-Disposition: attachment;filename="' . $file_name . '"');
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


  private function get_role_title($ds = array())
  {
    $list = "";

    $roles = array(
      'S' => 'WO',
      'C' => 'WC',
      'N' => 'WT',
      'P' => 'WS',
      'U' => 'WU',
      'T' => 'WQ',
      'Q' => 'WV',
      'L' => 'WL'
    );

    if( ! empty($ds))
    {
      $i = 1;
      foreach($ds as $rs)
      {
        if( ! empty($roles[$rs]))
        {
          $list .= $i=== 1 ? $roles[$rs] : ", {$roles[$rs]}";
          $i++;
        }
      }
    }

    return $list;
  }


  private function get_state_title($ds = array())
  {
    $list = "";

    $states = array(
      "1" => "รอดำเนินการ",
      "2" => "รอชำระเงิน",
      "3" => "รอจัดสินค้า",
      "4" => "กำลังจัดสินค้า",
      "5" => "รอตรวจ",
      "6" => "กำลังตรวจ",
      "7" => "รอเปิดบิล",
      "8" => "เปิดบิลแล้ว",
      "9" => "ยกเลิก"
    );

    if( ! empty($ds))
    {
      $i = 1;
      foreach($ds as $rs)
      {
        if( ! empty($states[$rs]))
        {
          $list .= $i === 1 ? $states[$rs] : ", {$states[$rs]}";
          $i++;
        }
      }
    }

    return $list;
  }
} 
