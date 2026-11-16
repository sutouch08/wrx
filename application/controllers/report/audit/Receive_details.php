<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class Receive_details extends PS_Controller
{
  public $menu_code = 'RARCDT';
	public $menu_group_code = 'RE';
  public $menu_sub_group_code = 'REAUDIT';
	public $title = 'รายงานสถานะเอกสารขาเข้า แสดงรายละเอียด';
  
  public function __construct()
  {
    parent::__construct();
    $this->home = base_url().'report/audit/receive_details';
    $this->load->model('report/audit/receive_details_model');
    $this->load->model('masters/warehouse_model');
    $this->load->model('masters/customers_model');
  }

  public function index()
  {
    $ds = array(
      'warehouse_list' => $this->warehouse_model->get_all_warehouse_list()
    );

    $this->load->view('report/audit/report_receive_details', $ds);
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
      '0' => 'ดราฟท์',
      '1' => 'สำเร็จ',
      '2' => 'ยกเลิก',
      '3' => 'รอรับสินค้า',
      '4' => 'รอยืนยันรับ'
    );

    return $ds;
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
        $role = $json->role;
        $all = $json->allRole ? TRUE : FALSE;
        $result = array();

        $state = $this->state_array();
        $wh = $this->warehouse_array();

        $filter = array(
          "from_date" => $json->fromDate,
          "to_date" => $json->toDate,
          "is_expired" => $json->isExpired,
          "all_state" => $json->allState,
          "state" => $json->state,
          "all_warehouse" => $json->allWarehouse,
          "warehouse" => $json->warehouse
        );

        $limit = 1000;
        $no = 1;

        if( ! empty($role->WR) OR $all)
        {
          $details = $this->receive_details_model->WR($filter);

          if( ! empty($details))
          {
            foreach($details as $rs)
            {
              $arr = array(
                'no' => number($no),
                'code' => $rs->code,
                'customer_code' => $rs->vendor_code,
                'customer_name' => $rs->vendor_name,
                'reference' => $rs->po_code,
                'expired' => $rs->is_expire == 1 ? 'Y' : 'N',
                'date_add' => thai_date($rs->date_add, FALSE, '/'),
                'date_upd' => thai_date($rs->date_upd, FALSE, '/'),
                'total_amount' => number($this->receive_details_model->get_doc_total($rs->code, 'WR'), 2),
                'warehouse_name' => empty($rs->warehouse_code) ? NULL : (empty($wh[$rs->warehouse_code]) ? NULL : $wh[$rs->warehouse_code]),
                'state_name' => $state[$rs->status],
                'uname' => $rs->user,
                'emp_name' => $this->user_model->get_name($rs->user),
                'remark' => $rs->remark,
                'cancel_reason' => $rs->status == 2 ? $rs->cancle_reason : NULL
              );

              array_push($result, $arr);
              $no++;
            }
          }
        }


        if( ! empty($role->RT) OR $all)
        {
          $details = $this->receive_details_model->RT($filter);

          if( ! empty($details))
          {
            foreach($details as $rs)
            {
              $arr = array(
                'no' => number($no),
                'code' => $rs->code,
                'customer_code' => $rs->customer_code,
                'customer_name' => $rs->customer_name,
                'reference' => $rs->order_code,
                'expired' => $rs->is_expire == 1 ? 'Y' : 'N',
                'date_add' => thai_date($rs->date_add, FALSE, '/'),
                'date_upd' => thai_date($rs->date_upd, FALSE, '/'),
                'total_amount' => number($this->receive_details_model->get_doc_total($rs->code, 'RT'), 2),
                'warehouse_name' => empty($rs->warehouse_code) ? NULL : (empty($wh[$rs->warehouse_code]) ? NULL : $wh[$rs->warehouse_code]),
                'state_name' => $state[$rs->status],
                'uname' => $rs->user,
                'emp_name' => $this->user_model->get_name($rs->user),
                'remark' => $rs->remark,
                'cancel_reason' => $rs->status == 2 ? $rs->cancle_reason : NULL
              );

              array_push($result, $arr);
              $no++;
            }
          }
        }

        if( ! empty($role->RN) OR $all)
        {
          $details = $this->receive_details_model->RN($filter);

          if( ! empty($details))
          {
            foreach($details as $rs)
            {
              $arr = array(
                'no' => number($no),
                'code' => $rs->code,
                'customer_code' => $rs->empID,
                'customer_name' => $rs->empName,
                'reference' => $rs->lend_code,
                'expired' => $rs->is_expire == 1 ? 'Y' : 'N',
                'date_add' => thai_date($rs->date_add, FALSE, '/'),
                'date_upd' => thai_date($rs->date_upd, FALSE, '/'),
                'total_amount' => number($this->receive_details_model->get_doc_total($rs->code, 'RN'), 2),
                'warehouse_name' => empty($rs->to_warehouse) ? NULL : (empty($wh[$rs->to_warehouse]) ? NULL : $wh[$rs->to_warehouse]),
                'state_name' => $state[$rs->status],
                'uname' => $rs->user,
                'emp_name' => $this->user_model->get_name($rs->user ?? ''),
                'remark' => $rs->remark,
                'cancel_reason' => $rs->status == 2 ? $rs->cancle_reason : NULL
              );

              array_push($result, $arr);
              $no++;
            }
          }
        }

        if( ! empty($role->SM) OR $all)
        {
          $details = $this->receive_details_model->SM($filter);

          if( ! empty($details))
          {
            $custs = [];

            foreach($details as $rs)
            {
              $customer_name = empty($rs->customer_code) ? '' : (empty($custs[$rs->customer_code]) ? $this->customers_model->get_name($rs->customer_code) : $custs[$rs->customer_code]);
              if(! empty($rs->customer_code) && empty($custs[$rs->customer_code])) $custs[$rs->customer_code] = $customer_name;

              $arr = array(
                'no' => number($no),
                'code' => $rs->code,
                'customer_code' => $rs->customer_code,
                'customer_name' => $customer_name,
                'reference' => $rs->invoice,
                'expired' => $rs->is_expire == 1 ? 'Y' : 'N',
                'date_add' => thai_date($rs->date_add, FALSE, '/'),
                'date_upd' => thai_date($rs->date_upd, FALSE, '/'),
                'total_amount' => number($this->receive_details_model->get_doc_total($rs->code, 'SM'), 2),
                'warehouse_name' => empty($wh[$rs->warehouse_code]) ? NULL : $wh[$rs->warehouse_code],
                'state_name' => $state[$rs->status],
                'uname' => $rs->user,
                'emp_name' => $this->user_model->get_name($rs->user ?? ''),
                'remark' => $rs->remark,
                'cancel_reason' => $rs->status == 2 ? $rs->cancle_reason : NULL
              );

              array_push($result, $arr);
              $no++;
            }
          }
        }

        if( ! empty($role->CN) OR $all)
        {
          $details = $this->receive_details_model->CN($filter);

          if( ! empty($details))
          {
            $custs = [];

            foreach($details as $rs)
            {
              $customer_name = empty($rs->customer_code) ? '' : (empty($custs[$rs->customer_code]) ? $this->customers_model->get_name($rs->customer_code) : $custs[$rs->customer_code]);
              if(! empty($rs->customer_code) && empty($custs[$rs->customer_code])) $custs[$rs->customer_code] = $customer_name;

              $arr = array(
                'no' => number($no),
                'code' => $rs->code,
                'customer_code' => $rs->customer_code,
                'customer_name' => $customer_name,
                'reference' => $rs->invoice,
                'expired' => 'N',
                'date_add' => thai_date($rs->date_add, FALSE, '/'),
                'date_upd' => thai_date($rs->date_upd, FALSE, '/'),
                'total_amount' => number($this->receive_details_model->get_doc_total($rs->code, 'CN'), 2),
                'warehouse_name' => empty($wh[$rs->warehouse_code]) ? NULL : $wh[$rs->warehouse_code],
                'state_name' => $state[$rs->status],
                'uname' => $rs->user,
                'emp_name' => $this->user_model->get_name($rs->user),
                'remark' => $rs->remark,
                'cancel_reason' => $rs->status == 2 ? $rs->cancle_reason : NULL
              );

              array_push($result, $arr);
              $no++;
            }
          }
        }
      }
      else
      {
        $sc = FALSE;
        $this->error = "Invalid request data format";
      }
    }

    $arr = array(
      'status' => $sc === TRUE ? 'success' : 'failed',
      'message' => $sc === TRUE ? 'success' : $this->error,
      'result' => $sc === TRUE ? $result : NULL
    );

    echo json_encode($arr);
  }



  public function do_export()
  {
    ini_set('memory_limit','2048M'); // This also needs to be increased in some cases. Can be changed to a higher value as per need)
    ini_set('sqlsrv.ClientBufferMaxKBSize','2097152'); // Setting to 2048M
    ini_set('sqlsrv.client_buffer_max_kb_size','2097152'); // Setting to 512M - for pdo_sqlsrv

    $token = $this->input->post('token');        
    $state = $this->state_array();
    $wh = $this->warehouse_array();

    $role = $this->input->post('role');
    $all = $this->input->post('allRole') == 1 ? TRUE : FALSE;


    $allRole = $this->input->post('allRole');
    $allWarehouse = $this->input->post('allWarehouse');
    $allState = $this->input->post('allState');
    $is_expired = $this->input->post('is_expired');
    $fromDate = $this->input->post('fromDate');
    $toDate = $this->input->post('toDate');
    $warehouse = $this->input->post('warehouse');
    $status = $this->input->post('state');

    $filter = array(
      "from_date" => $fromDate,
      "to_date" => $toDate,
      "all_role" => $allRole,
      "is_expired" => $is_expired,
      "all_state" => $allState,
      "state" => $status,
      "all_warehouse" => $allWarehouse,
      "warehouse" => $warehouse
    );

    $excel = new Spreadsheet();
    $index = 0;

    if (! empty($role['WR']) or $all)
    {
      $sheet = $index === 0 ? $excel->getActiveSheet() : $excel->createSheet();      
      $sheet->setTitle('WR');

      $index++;

      //---  Report title
      $report_title = "รายงานสถานะเอกสารขาเข้า แสดงรายละเอียด";
      $role_title = "เอกสาร : รับสินค้าจากการซื้อ";
      $expire_title = "อายุเอกสาร : " . ($is_expired == 'all' ? 'ทั้งหมด' : ($is_expired == 1 ? 'เฉพาะที่หมดอายุ' : 'เฉพาะที่ไม่หมดอายุ'));
      $wh_title     = 'คลัง :  ' . ($allWarehouse == 1 ? 'ทั้งหมด' : $this->get_title($warehouse));
      $state_title = "สถานะ : " . ($allState == 1 ? "ทั้งหมด" : $this->get_state_title($status));
      //--- set report title header
      $sheet->setCellValue('A1', $report_title);
      $sheet->setCellValue('A2', $role_title);
      $sheet->setCellValue('A3', $expire_title);
      $sheet->setCellValue('A4', $state_title);
      $sheet->setCellValue('A5', $wh_title);
      $sheet->setCellValue('A6', 'วันที่เอกสาร : (' . thai_date($fromDate, '/') . ') - (' . thai_date($toDate, '/') . ')');

      //--- set Table header
      $row = 7;

      $sheet->setCellValue("A{$row}", 'ลำดับ');
      $sheet->setCellValue("B{$row}", 'วันที่');
      $sheet->setCellValue("C{$row}", 'เลขที่');
      $sheet->setCellValue("D{$row}", 'อ้างอิง');
      $sheet->setCellValue("E{$row}", 'มูลค่า');
      $sheet->setCellValue("F{$row}", 'รหัสลูกค้า');
      $sheet->setCellValue("G{$row}", 'ชื่อลูกค้า');
      $sheet->setCellValue("H{$row}", 'สถานะ');
      $sheet->setCellValue("I{$row}", 'หมดอายุ');
      $sheet->setCellValue("J{$row}", 'วันที่แก้ไข');
      $sheet->setCellValue("K{$row}", 'รหัสคลัง');
      $sheet->setCellValue("L{$row}", 'ชื่อคลัง');
      $sheet->setCellValue("M{$row}", 'Username');
      $sheet->setCellValue("N{$row}", 'พนักงาน');
      $sheet->setCellValue("O{$row}", 'หมายเหตุ');
      $sheet->setCellValue("P{$row}", 'cancel reason');
      $row++;

      //---- กำหนดความกว้างของคอลัมภ์
      $sheet->getColumnDimension('B')->setWidth(15);
      $sheet->getColumnDimension('C')->setWidth(20);
      $sheet->getColumnDimension('D')->setWidth(20);
      $sheet->getColumnDimension('E')->setWidth(20);
      $sheet->getColumnDimension('F')->setWidth(20);
      $sheet->getColumnDimension('G')->setWidth(90);
      $sheet->getColumnDimension('H')->setWidth(15);
      $sheet->getColumnDimension('I')->setWidth(10);
      $sheet->getColumnDimension('J')->setWidth(15);
      $sheet->getColumnDimension('K')->setWidth(15);
      $sheet->getColumnDimension('L')->setWidth(40);
      $sheet->getColumnDimension('M')->setWidth(15);
      $sheet->getColumnDimension('N')->setWidth(15);
      $sheet->getColumnDimension('O')->setWidth(40);
      $sheet->getColumnDimension('P')->setWidth(40);

      $details = $this->receive_details_model->WR($filter);

      if (! empty($details))
      {
        $no = 1;
        foreach ($details as $rs)
        {          
          $date_add = Date::PHPToExcel(new DateTime($rs->date_add));
          $date_upd = Date::PHPToExcel(new DateTime($rs->date_upd));
          $amount = $this->receive_details_model->get_doc_total($rs->code, 'WR');

          $sheet->setCellValue("A{$row}", $no);
          $sheet->setCellValue("B{$row}", $date_add);
          $sheet->setCellValue("C{$row}", $rs->code);
          $sheet->setCellValue("D{$row}", $rs->po_code);
          $sheet->setCellValue("E{$row}", round($amount ?? 0, 2));
          $sheet->setCellValue("F{$row}", $rs->vendor_code);
          $sheet->setCellValue("G{$row}", $rs->vendor_name);
          $sheet->setCellValue("H{$row}", $state[$rs->status]);
          $sheet->setCellValue("I{$row}", $rs->is_expire == 1 ? 'Y' : 'N');
          $sheet->setCellValue("J{$row}", $date_upd);
          $sheet->setCellValue("K{$row}", $rs->warehouse_code);
          $sheet->setCellValue("L{$row}", empty($rs->warehouse_code) ? '' : (empty($wh[$rs->warehouse_code]) ? NULL : $wh[$rs->warehouse_code]));
          $sheet->setCellValue("M{$row}", $rs->user);
          $sheet->setCellValue("N{$row}", $this->user_model->get_name($rs->user ?? ''));
          $sheet->setCellValue("O{$row}", $rs->remark);

          if ($rs->status == 2)
          {
            $sheet->setCellValue("P{$row}", $rs->cancle_reason);
          }

          $no++;
          $row++;
        }

        $sheet->getStyle("B10:B{$row}")->getNumberFormat()->setFormatCode('dd/mm/yyyy');
        $sheet->getStyle("J10:J{$row}")->getNumberFormat()->setFormatCode('dd/mm/yyyy');
        $sheet->getStyle("E10:E{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
      }
    } //--- endif WR

    if (! empty($role['RT']) or $all)
    {
      $sheet = $index === 0 ? $excel->getActiveSheet() : $excel->createSheet();      
      $sheet->setTitle('RT');
      $index++;

      //---  Report title
      $report_title = "รายงานสถานะเอกสารขาเข้า แสดงรายละเอียด";
      $role_title = "เอกสาร : รับสินค้าจากการแปรสภาพ";
      $expire_title = "อายุเอกสาร : " . ($is_expired == 'all' ? 'ทั้งหมด' : ($is_expired == 1 ? 'เฉพาะที่หมดอายุ' : 'เฉพาะที่ไม่หมดอายุ'));
      $wh_title     = 'คลัง :  ' . ($allWarehouse == 1 ? 'ทั้งหมด' : $this->get_title($warehouse));
      $state_title = "สถานะ : " . ($allState == 1 ? "ทั้งหมด" : $this->get_state_title($status));
      //--- set report title header
      $sheet->setCellValue('A1', $report_title);
      $sheet->setCellValue('A2', $role_title);
      $sheet->setCellValue('A3', $expire_title);
      $sheet->setCellValue('A4', $state_title);
      $sheet->setCellValue('A5', $wh_title);
      $sheet->setCellValue('A6', 'วันที่เอกสาร : (' . thai_date($fromDate, '/') . ') - (' . thai_date($toDate, '/') . ')');

      //--- set Table header
      $row = 7;

      $sheet->setCellValue("A{$row}", 'ลำดับ');
      $sheet->setCellValue("B{$row}", 'วันที่');
      $sheet->setCellValue("C{$row}", 'เลขที่');
      $sheet->setCellValue("D{$row}", 'อ้างอิง');
      $sheet->setCellValue("E{$row}", 'มูลค่า');
      $sheet->setCellValue("F{$row}", 'รหัสลูกค้า');
      $sheet->setCellValue("G{$row}", 'ชื่อลูกค้า');
      $sheet->setCellValue("H{$row}", 'สถานะ');
      $sheet->setCellValue("I{$row}", 'หมดอายุ');
      $sheet->setCellValue("J{$row}", 'วันที่แก้ไข');
      $sheet->setCellValue("K{$row}", 'รหัสคลัง');
      $sheet->setCellValue("L{$row}", 'ชื่อคลัง');
      $sheet->setCellValue("M{$row}", 'Username');
      $sheet->setCellValue("N{$row}", 'พนักงาน');
      $sheet->setCellValue("O{$row}", 'หมายเหตุ');
      $sheet->setCellValue("P{$row}", 'cancel reason');
      $row++;

      //---- กำหนดความกว้างของคอลัมภ์
      $sheet->getColumnDimension('B')->setWidth(15);
      $sheet->getColumnDimension('C')->setWidth(20);
      $sheet->getColumnDimension('D')->setWidth(20);
      $sheet->getColumnDimension('E')->setWidth(20);
      $sheet->getColumnDimension('F')->setWidth(20);
      $sheet->getColumnDimension('G')->setWidth(90);
      $sheet->getColumnDimension('H')->setWidth(15);
      $sheet->getColumnDimension('I')->setWidth(10);
      $sheet->getColumnDimension('J')->setWidth(15);
      $sheet->getColumnDimension('K')->setWidth(15);
      $sheet->getColumnDimension('L')->setWidth(40);
      $sheet->getColumnDimension('M')->setWidth(15);
      $sheet->getColumnDimension('N')->setWidth(15);
      $sheet->getColumnDimension('O')->setWidth(40);
      $sheet->getColumnDimension('P')->setWidth(40);

      $details = $this->receive_details_model->RT($filter);

      if (! empty($details))
      {
        $no = 1;
        foreach ($details as $rs)
        {         
          $date_add = Date::PHPToExcel(new DateTime($rs->date_add));
          $date_upd = Date::PHPToExcel(new DateTime($rs->date_upd));
          $amount = $this->receive_details_model->get_doc_total($rs->code, 'RT');

          $sheet->setCellValue("A{$row}", $no);
          $sheet->setCellValue("B{$row}", $date_add);
          $sheet->setCellValue("C{$row}", $rs->code);
          $sheet->setCellValue("D{$row}", $rs->order_code);
          $sheet->setCellValue("E{$row}", round($amount ?? 0, 2));
          $sheet->setCellValue("F{$row}", $rs->customer_code);
          $sheet->setCellValue("G{$row}", $rs->customer_name);
          $sheet->setCellValue("H{$row}", $state[$rs->status ?? 0]);
          $sheet->setCellValue("I{$row}", $rs->is_expire == 1 ? 'Y' : 'N');
          $sheet->setCellValue("J{$row}", $date_upd);
          $sheet->setCellValue("K{$row}", $rs->warehouse_code);
          $sheet->setCellValue("L{$row}", empty($rs->warehouse_code) ? '' : (empty($wh[$rs->warehouse_code]) ? NULL : $wh[$rs->warehouse_code]));
          $sheet->setCellValue("M{$row}", $rs->user);
          $sheet->setCellValue("N{$row}", $this->user_model->get_name($rs->user ?? ''));
          $sheet->setCellValue("O{$row}", $rs->remark);

          if ($rs->status == 2)
          {
            $sheet->setCellValue("P{$row}", $rs->cancle_reason);
          }

          $no++;
          $row++;
        }

        $sheet->getStyle("B10:B{$row}")->getNumberFormat()->setFormatCode('dd/mm/yyyy');
        $sheet->getStyle("J10:J{$row}")->getNumberFormat()->setFormatCode('dd/mm/yyyy');
        $sheet->getStyle("E10:E{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
      }
    } //--- endif RT


    if (! empty($role['RN']) or $all)
    {
      $sheet = $index === 0 ? $excel->getActiveSheet() : $excel->createSheet();      
      $sheet->setTitle('RN');
      $index++;

      //---  Report title
      $report_title = "รายงานสถานะเอกสารขาเข้า แสดงรายละเอียด";
      $role_title = "เอกสาร : คืนสินค้าจากการยืม";
      $expire_title = "อายุเอกสาร : " . ($is_expired == 'all' ? 'ทั้งหมด' : ($is_expired == 1 ? 'เฉพาะที่หมดอายุ' : 'เฉพาะที่ไม่หมดอายุ'));
      $wh_title     = 'คลัง :  ' . ($allWarehouse == 1 ? 'ทั้งหมด' : $this->get_title($warehouse));
      $state_title = "สถานะ : " . ($allState == 1 ? "ทั้งหมด" : $this->get_state_title($status));
      //--- set report title header
      $sheet->setCellValue('A1', $report_title);
      $sheet->setCellValue('A2', $role_title);
      $sheet->setCellValue('A3', $expire_title);
      $sheet->setCellValue('A4', $state_title);
      $sheet->setCellValue('A5', $wh_title);
      $sheet->setCellValue('A6', 'วันที่เอกสาร : (' . thai_date($fromDate, '/') . ') - (' . thai_date($toDate, '/') . ')');

      //--- set Table header
      $row = 7;

      $sheet->setCellValue("A{$row}", 'ลำดับ');
      $sheet->setCellValue("B{$row}", 'วันที่');
      $sheet->setCellValue("C{$row}", 'เลขที่');
      $sheet->setCellValue("D{$row}", 'อ้างอิง');
      $sheet->setCellValue("E{$row}", 'มูลค่า');
      $sheet->setCellValue("F{$row}", 'รหัสลูกค้า');
      $sheet->setCellValue("G{$row}", 'ชื่อลูกค้า');
      $sheet->setCellValue("H{$row}", 'สถานะ');
      $sheet->setCellValue("I{$row}", 'หมดอายุ');
      $sheet->setCellValue("J{$row}", 'วันที่แก้ไข');
      $sheet->setCellValue("K{$row}", 'รหัสคลัง');
      $sheet->setCellValue("L{$row}", 'ชื่อคลัง');
      $sheet->setCellValue("M{$row}", 'Username');
      $sheet->setCellValue("N{$row}", 'พนักงาน');
      $sheet->setCellValue("O{$row}", 'หมายเหตุ');
      $sheet->setCellValue("P{$row}", 'cancel reason');
      $row++;

      //---- กำหนดความกว้างของคอลัมภ์
      $sheet->getColumnDimension('B')->setWidth(15);
      $sheet->getColumnDimension('C')->setWidth(20);
      $sheet->getColumnDimension('D')->setWidth(20);
      $sheet->getColumnDimension('E')->setWidth(20);
      $sheet->getColumnDimension('F')->setWidth(20);
      $sheet->getColumnDimension('G')->setWidth(90);
      $sheet->getColumnDimension('H')->setWidth(15);
      $sheet->getColumnDimension('I')->setWidth(10);
      $sheet->getColumnDimension('J')->setWidth(15);
      $sheet->getColumnDimension('K')->setWidth(15);
      $sheet->getColumnDimension('L')->setWidth(40);
      $sheet->getColumnDimension('M')->setWidth(15);
      $sheet->getColumnDimension('N')->setWidth(15);
      $sheet->getColumnDimension('O')->setWidth(40);
      $sheet->getColumnDimension('P')->setWidth(40);

      $details = $this->receive_details_model->RN($filter);

      if (! empty($details))
      {
        $no = 1;
        foreach ($details as $rs)
        {          
          $date_add = Date::PHPToExcel(new DateTime($rs->date_add));
          $date_upd = Date::PHPToExcel(new DateTime($rs->date_upd));
          $amount = $this->receive_details_model->get_doc_total($rs->code, 'RN');

          $sheet->setCellValue("A{$row}", $no);
          $sheet->setCellValue("B{$row}", $date_add);
          $sheet->setCellValue("C{$row}", $rs->code);
          $sheet->setCellValue("D{$row}", $rs->lend_code);
          $sheet->setCellValue("E{$row}", round($amount ?? 0, 2));
          $sheet->setCellValue("F{$row}", $rs->empID);
          $sheet->setCellValue("G{$row}", $rs->empName);
          $sheet->setCellValue("H{$row}", $state[$rs->status]);
          $sheet->setCellValue("I{$row}", $rs->is_expire == 1 ? 'Y' : 'N');
          $sheet->setCellValue("J{$row}", $date_upd);
          $sheet->setCellValue("K{$row}", $rs->to_warehouse);
          $sheet->setCellValue("L{$row}", empty($rs->to_warehouse) ? '' : (empty($wh[$rs->to_warehouse]) ? NULL : $wh[$rs->to_warehouse]));
          $sheet->setCellValue("M{$row}", $rs->user);
          $sheet->setCellValue("N{$row}", $this->user_model->get_name($rs->user ?? ''));
          $sheet->setCellValue("O{$row}", $rs->remark);

          if ($rs->status == 2)
          {
            $sheet->setCellValue("P{$row}", $rs->cancle_reason);
          }

          $no++;
          $row++;
        }

        $sheet->getStyle("B10:B{$row}")->getNumberFormat()->setFormatCode('dd/mm/yyyy');
        $sheet->getStyle("J10:J{$row}")->getNumberFormat()->setFormatCode('dd/mm/yyyy');
        $sheet->getStyle("E10:E{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
      }
    } //--- end if RN


    if (! empty($role['SM']) or $all)
    {
      $sheet = $index === 0 ? $excel->getActiveSheet() : $excel->createSheet();     
      $sheet->setTitle('SM');
      $index++;

      //---  Report title
      $report_title = "รายงานสถานะเอกสารขาเข้า แสดงรายละเอียด";
      $role_title = "เอกสาร : ลดหนี้ขาย";
      $expire_title = "อายุเอกสาร : " . ($is_expired == 'all' ? 'ทั้งหมด' : ($is_expired == 1 ? 'เฉพาะที่หมดอายุ' : 'เฉพาะที่ไม่หมดอายุ'));
      $wh_title     = 'คลัง :  ' . ($allWarehouse == 1 ? 'ทั้งหมด' : $this->get_title($warehouse));
      $state_title = "สถานะ : " . ($allState == 1 ? "ทั้งหมด" : $this->get_state_title($status));
      //--- set report title header
      $sheet->setCellValue('A1', $report_title);
      $sheet->setCellValue('A2', $role_title);
      $sheet->setCellValue('A3', $expire_title);
      $sheet->setCellValue('A4', $state_title);
      $sheet->setCellValue('A5', $wh_title);
      $sheet->setCellValue('A6', 'วันที่เอกสาร : (' . thai_date($fromDate, '/') . ') - (' . thai_date($toDate, '/') . ')');

      //--- set Table header
      $row = 7;

      $sheet->setCellValue("A{$row}", 'ลำดับ');
      $sheet->setCellValue("B{$row}", 'วันที่');
      $sheet->setCellValue("C{$row}", 'เลขที่');
      $sheet->setCellValue("D{$row}", 'อ้างอิง');
      $sheet->setCellValue("E{$row}", 'มูลค่า');
      $sheet->setCellValue("F{$row}", 'รหัสลูกค้า');
      $sheet->setCellValue("G{$row}", 'ชื่อลูกค้า');
      $sheet->setCellValue("H{$row}", 'สถานะ');
      $sheet->setCellValue("I{$row}", 'หมดอายุ');
      $sheet->setCellValue("J{$row}", 'วันที่แก้ไข');
      $sheet->setCellValue("K{$row}", 'รหัสคลัง');
      $sheet->setCellValue("L{$row}", 'ชื่อคลัง');
      $sheet->setCellValue("M{$row}", 'Username');
      $sheet->setCellValue("N{$row}", 'พนักงาน');
      $sheet->setCellValue("O{$row}", 'หมายเหตุ');
      $sheet->setCellValue("P{$row}", 'cancel reason');
      $row++;

      //---- กำหนดความกว้างของคอลัมภ์
      $sheet->getColumnDimension('B')->setWidth(15);
      $sheet->getColumnDimension('C')->setWidth(20);
      $sheet->getColumnDimension('D')->setWidth(20);
      $sheet->getColumnDimension('E')->setWidth(20);
      $sheet->getColumnDimension('F')->setWidth(20);
      $sheet->getColumnDimension('G')->setWidth(90);
      $sheet->getColumnDimension('H')->setWidth(15);
      $sheet->getColumnDimension('I')->setWidth(10);
      $sheet->getColumnDimension('J')->setWidth(15);
      $sheet->getColumnDimension('K')->setWidth(15);
      $sheet->getColumnDimension('L')->setWidth(40);
      $sheet->getColumnDimension('M')->setWidth(15);
      $sheet->getColumnDimension('N')->setWidth(15);
      $sheet->getColumnDimension('O')->setWidth(40);
      $sheet->getColumnDimension('P')->setWidth(40);

      $details = $this->receive_details_model->SM($filter);

      if (! empty($details))
      {
        $custs = [];
        $no = 1;
        foreach ($details as $rs)
        {          
          $date_add = Date::PHPToExcel(new DateTime($rs->date_add));
          $date_upd = Date::PHPToExcel(new DateTime($rs->date_upd));
          $amount = $this->receive_details_model->get_doc_total($rs->code, 'SM');

          $customer_name = empty($rs->customer_code) ? '' : (empty($custs[$rs->customer_code]) ? $this->customers_model->get_name($rs->customer_code) : $custs[$rs->customer_code]);
          if(!empty($rs->customer_code) && empty($custs[$rs->customer_code])) $custs[$rs->customer_code] = $customer_name;

          $sheet->setCellValue("A{$row}", $no);
          $sheet->setCellValue("B{$row}", $date_add);
          $sheet->setCellValue("C{$row}", $rs->code);
          $sheet->setCellValue("D{$row}", $rs->invoice);
          $sheet->setCellValue("E{$row}", round($amount ?? 0, 2));
          $sheet->setCellValue("F{$row}", $rs->customer_code);
          $sheet->setCellValue("G{$row}", $customer_name);
          $sheet->setCellValue("H{$row}", $state[$rs->status]);
          $sheet->setCellValue("I{$row}", $rs->is_expire == 1 ? 'Y' : 'N');
          $sheet->setCellValue("J{$row}", $date_upd);
          $sheet->setCellValue("K{$row}", $rs->warehouse_code);
          $sheet->setCellValue("L{$row}", empty($rs->warehouse_code) ? NULL : (empty($wh[$rs->warehouse_code]) ? NULL : $wh[$rs->warehouse_code]));
          $sheet->setCellValue("M{$row}", $rs->user);
          $sheet->setCellValue("N{$row}", $this->user_model->get_name($rs->user ?? ''));
          $sheet->setCellValue("O{$row}", $rs->remark);

          if ($rs->status == 2)
          {
            $sheet->setCellValue("P{$row}", $rs->cancle_reason);
          }

          $no++;
          $row++;
        }

        $sheet->getStyle("B10:B{$row}")->getNumberFormat()->setFormatCode('dd/mm/yyyy');
        $sheet->getStyle("J10:J{$row}")->getNumberFormat()->setFormatCode('dd/mm/yyyy');
        $sheet->getStyle("E10:E{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
      }
    } //--- endif SM


    if (! empty($role['CN']) or $all)
    {
      $sheet = $index === 0 ? $excel->getActiveSheet() : $excel->createSheet();     
      $sheet->setTitle('CN');
      $index++;

      //---  Report title
      $report_title = "รายงานสถานะเอกสารขาเข้า แสดงรายละเอียด";
      $role_title = "เอกสาร : ลดหนี้ฝากขายเทียม";
      $expire_title = "อายุเอกสาร : " . ($is_expired == 'all' ? 'ทั้งหมด' : ($is_expired == 1 ? 'เฉพาะที่หมดอายุ' : 'เฉพาะที่ไม่หมดอายุ'));
      $wh_title     = 'คลัง :  ' . ($allWarehouse == 1 ? 'ทั้งหมด' : $this->get_title($warehouse));
      $state_title = "สถานะ : " . ($allState == 1 ? "ทั้งหมด" : $this->get_state_title($status));
      //--- set report title header
      $sheet->setCellValue('A1', $report_title);
      $sheet->setCellValue('A2', $role_title);
      $sheet->setCellValue('A3', $expire_title);
      $sheet->setCellValue('A4', $state_title);
      $sheet->setCellValue('A5', $wh_title);
      $sheet->setCellValue('A6', 'วันที่เอกสาร : (' . thai_date($fromDate, '/') . ') - (' . thai_date($toDate, '/') . ')');

      //--- set Table header
      $row = 7;

      $sheet->setCellValue("A{$row}", 'ลำดับ');
      $sheet->setCellValue("B{$row}", 'วันที่');
      $sheet->setCellValue("C{$row}", 'เลขที่');
      $sheet->setCellValue("D{$row}", 'อ้างอิง');
      $sheet->setCellValue("E{$row}", 'มูลค่า');
      $sheet->setCellValue("F{$row}", 'รหัสลูกค้า');
      $sheet->setCellValue("G{$row}", 'ชื่อลูกค้า');
      $sheet->setCellValue("H{$row}", 'สถานะ');
      $sheet->setCellValue("I{$row}", 'หมดอายุ');
      $sheet->setCellValue("J{$row}", 'วันที่แก้ไข');
      $sheet->setCellValue("K{$row}", 'รหัสคลัง');
      $sheet->setCellValue("L{$row}", 'ชื่อคลัง');
      $sheet->setCellValue("M{$row}", 'Username');
      $sheet->setCellValue("N{$row}", 'พนักงาน');
      $sheet->setCellValue("O{$row}", 'หมายเหตุ');
      $sheet->setCellValue("P{$row}", 'cancel reason');
      $row++;

      //---- กำหนดความกว้างของคอลัมภ์
      $sheet->getColumnDimension('B')->setWidth(15);
      $sheet->getColumnDimension('C')->setWidth(20);
      $sheet->getColumnDimension('D')->setWidth(20);
      $sheet->getColumnDimension('E')->setWidth(20);
      $sheet->getColumnDimension('F')->setWidth(20);
      $sheet->getColumnDimension('G')->setWidth(90);
      $sheet->getColumnDimension('H')->setWidth(15);
      $sheet->getColumnDimension('I')->setWidth(10);
      $sheet->getColumnDimension('J')->setWidth(15);
      $sheet->getColumnDimension('K')->setWidth(15);
      $sheet->getColumnDimension('L')->setWidth(40);
      $sheet->getColumnDimension('M')->setWidth(15);
      $sheet->getColumnDimension('N')->setWidth(15);
      $sheet->getColumnDimension('O')->setWidth(40);
      $sheet->getColumnDimension('P')->setWidth(40);

      $details = $this->receive_details_model->CN($filter);

      if (! empty($details))
      {
        $custs = [];
        $no = 1;
        foreach ($details as $rs)
        {          
          $date_add = Date::PHPToExcel(new DateTime($rs->date_add));
          $date_upd = Date::PHPToExcel(new DateTime($rs->date_upd));
          $amount = $this->receive_details_model->get_doc_total($rs->code, 'CN');

          $customer_name = empty($rs->customer_code) ? '' : (empty($custs[$rs->customer_code]) ? $this->customers_model->get_name($rs->customer_code) : $custs[$rs->customer_code]);
          if(!empty($rs->customer_code) && empty($custs[$rs->customer_code])) $custs[$rs->customer_code] = $customer_name;

          $sheet->setCellValue("A{$row}", $no);
          $sheet->setCellValue("B{$row}", $date_add);
          $sheet->setCellValue("C{$row}", $rs->code);
          $sheet->setCellValue("D{$row}", $rs->invoice);
          $sheet->setCellValue("E{$row}", round($amount ?? 0, 2));
          $sheet->setCellValue("F{$row}", $rs->customer_code);
          $sheet->setCellValue("G{$row}", $customer_name);
          $sheet->setCellValue("H{$row}", $state[$rs->status]);
          $sheet->setCellValue("I{$row}", 'N');
          $sheet->setCellValue("J{$row}", $date_upd);
          $sheet->setCellValue("K{$row}", $rs->warehouse_code);
          $sheet->setCellValue("L{$row}", empty($rs->warehouse_code) ? '' : (empty($wh[$rs->warehouse_code]) ? NULL : $wh[$rs->warehouse_code]));
          $sheet->setCellValue("M{$row}", $rs->user);
          $sheet->setCellValue("N{$row}", $this->user_model->get_name($rs->user ?? ''));
          $sheet->setCellValue("O{$row}", $rs->remark);

          if ($rs->status == 2)
          {
            $sheet->setCellValue("P{$row}", $rs->cancle_reason);
          }

          $no++;
          $row++;
        }

        $sheet->getStyle("B10:B{$row}")->getNumberFormat()->setFormatCode('dd/mm/yyyy');
        $sheet->getStyle("J10:J{$row}")->getNumberFormat()->setFormatCode('dd/mm/yyyy');
        $sheet->getStyle("E10:E{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
      }
    } //--- endif SM    

    setToken($token);
    $file_name = "Report Receive Details.xlsx";
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); /// form excel 2007 XLSX
    header('Content-Disposition: attachment;filename="'.$file_name.'"');
    $writer = new Xlsx($excel);
    $writer->save('php://output');
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



  private function get_state_title($ds = array())
  {
    $list = "";

    $states = array(
      '0' => 'ดราฟท์',
      '1' => 'สำเร็จ',
      '2' => 'ยกเลิก',
      '3' => 'รอรับสินค้า',
      '4' => 'รอยืนยันรับ'
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
