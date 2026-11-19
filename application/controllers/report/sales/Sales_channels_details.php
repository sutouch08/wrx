<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class Sales_channels_details extends PS_Controller
{
  public $menu_code = 'RSOCDS';
	public $menu_group_code = 'RE';
  public $menu_sub_group_code = 'RESALE';
	public $title = 'รายงานออเดอร์ออนไลน์แสดงรายละเอียดการจัดส่ง(ไม่ใช่ยอดขาย)';
  
  public function __construct()
  {
    parent::__construct();
    $this->home = base_url().'report/sales/sales_channels_details';
    $this->load->model('report/sales/sales_report_model');
  }

  public function index()
  {
    $this->load->model('masters/channels_model');
    $channels = $this->channels_model->get_online_list();
    $ds['channels_list'] = $channels;
    $this->load->view('report/sales/report_sales_channels_details', $ds);
  }

  public function do_export()
  {
    ini_set('memory_limit','2048M');
    ini_set('max_execution_time', 600);
    ini_set('sqlsrv.ClientBufferMaxKBSize','2097152'); // Setting to 2048M
    ini_set('sqlsrv.client_buffer_max_kb_size','2097152'); // Setting to 512M - for pdo_sqlsrv

    $allChannels = $this->input->post('allChannels');
    $channels = $this->input->post('channels');
    $pdFrom = $this->input->post('pdFrom');
    $pdTo = $this->input->post('pdTo');
    $refCodeFrom = $this->input->post('refCodeFrom');
    $refCodeTo = $this->input->post('refCodeTo');
    $fromDate = $this->input->post('fromDate');
    $toDate = $this->input->post('toDate');

    $allProduct = (!empty($pdFrom) && !empty($pdTo)) ? 0 : 1;

    $token = $this->input->post('token');

    $ds = array(
      'all_channels' => $allChannels,
      'channels' => $channels,
      'item_from' => $pdFrom,
      'item_to' => $pdTo,
      'from_reference' => $refCodeFrom,
      'to_reference' => $refCodeTo,
      'from_date' => $fromDate,
      'to_date' => $toDate
    );

    $result = $this->sales_report_model->get_online_channels_details($ds);

    $ch_title = "";

    if(!empty($channels))
    {
      $i = 1;
      foreach($channels as $id_channels)
      {
        $ch_title .= $i == 1 ? $id_channels :', '.$id_channels;
        $i++;
      }
    }

    //---  Report title
    $report_title = 'รายงาน ออเดอร์ออนไลน์ แสดงรายละเอียดการจัดส่ง วันที่ ' . thai_date($fromDate,'/') .' ถึง '.thai_date($toDate, '/');
    $channels_title = 'ช่องทางการขาย : '.($allChannels == 1 ? 'ทั้งหมด' : $ch_title);
    $pd_title     = 'สินค้า :  '. ($allProduct == 1 ? 'ทั้งหมด' : '('.$pdFrom.') - ('.$pdTo.')');

    //--- load excel library
    $excel = new Spreadsheet();
    $sheet = $excel->getActiveSheet();
    $sheet->setTitle('Sales online channels Report');

    //--- set report title header
    $sheet->setCellValue('A1', $report_title);
    $sheet->mergeCells('A1:G1');
    $sheet->setCellValue('A2', $channels_title);
    $sheet->mergeCells('A2:G2');
    $sheet->setCellValue('A3', $pd_title);
    $sheet->mergeCells('A3:G3');
    $sheet->setCellValue('A4', 'วันที่เอกสาร : ('.thai_date($fromDate,'/') .') - ('.thai_date($toDate,'/').')');
    $sheet->mergeCells('A4:G4');
   
    $sheet->setCellValue('A5', 'ลำดับ');
    $sheet->setCellValue('B5', 'วันที่');
    $sheet->setCellValue('C5', 'เอกสาร');
    $sheet->setCellValue('D5', 'อ้างอิง');
    $sheet->setCellValue('E5', 'เลขที่จัดส่ง');
    $sheet->setCellValue('F5', 'ชื่อลูกค้า');
    $sheet->setCellValue('G5', 'ที่อยู่บรรทัด 1');
    $sheet->setCellValue('H5', 'ที่อยู่บรรทัด 2');
    $sheet->setCellValue('I5', 'อำเภอ');
    $sheet->setCellValue('J5', 'จังหวัด');
    $sheet->setCellValue('K5', 'รหัสไปรษณีย์');
    $sheet->setCellValue('L5', 'เบอร์โทรศัพท์');
    $sheet->setCellValue('M5', 'ช่องทางขาย');
    $sheet->setCellValue('N5', 'ช่องทางการชำระเงิน');
    $sheet->setCellValue('O5', 'สินค้า');
    $sheet->setCellValue('P5', 'ราคา');
    $sheet->setCellValue('Q5', 'จำนวน');
    $sheet->setCellValue('R5', 'ส่วนลด');
    $sheet->setCellValue('S5', 'มูลค่า');
    $sheet->setCellValue('T5', 'ค่าจัดส่ง');
    $sheet->setCellValue('U5', 'ค่าบริการ');
    $sheet->setCellValue('V5', 'สถานะ');
    $sheet->setCellValue('W5', 'ขนส่ง');

    foreach(range('A', 'W') as $column)
    {
      $sheet->getColumnDimension($column)->setAutoSize(true);
    }

    $row = 6;


    if(!empty($result))
    {
      $no = 1;
      $prev_code = NULL;
      $this->load->model('address/address_model');
      $adr = NULL;
      foreach($result as $rs)
      {       
        $date = Date::PHPToExcel(new DateTime($rs->date_add));

        if($prev_code != $rs->code)
        {
          if(!empty($rs->id_address))
          {
            $adr = $this->address_model->get_shipping_detail($rs->id_address);
          }
          else
          {
            $adr = $this->address_model->get_shipping_address_by_code($rs->customer_ref);
          }
        }

        if(empty($adr))
        {
          $adr = (object) [
            'name' => '',
            'address' => '',
            'sub_district' => '',
            'district' => '',
            'province' => '',
            'postcode' => '',
            'phone' => ''
          ];
        }
        
        $tracking = empty($rs->tracking_no) ? $rs->shipping_code : $rs->tracking_no;

        $sheet->setCellValue('A'.$row, $no);        
        $sheet->setCellValue('B'.$row, $date);   
        $sheet->setCellValue('C'.$row, $rs->code);
        $sheet->setCellValueExplicit('D'.$row, $rs->reference, DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('E'.$row, $tracking, DataType::TYPE_STRING);
        $sheet->setCellValue('F'.$row, $adr->name);
        $sheet->setCellValue('G'.$row, $adr->address);
        $sheet->setCellValue('H'.$row, $adr->sub_district);
        $sheet->setCellValue('I'.$row, $adr->district);
        $sheet->setCellValue('J'.$row, $adr->province);
        $sheet->setCellValueExplicit('K'.$row, $adr->postcode, DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('L'.$row, $adr->phone, DataType::TYPE_STRING);
        $sheet->setCellValue('M'.$row, $rs->channels);
        $sheet->setCellValue('N'.$row, $rs->payment);
        $sheet->setCellValue('O'.$row, $rs->product_code);
        $sheet->setCellValue('P'.$row, $rs->price);
        $sheet->setCellValue('Q'.$row, $rs->qty);
        $sheet->setCellValue('R'.$row, $rs->discount_amount);
        $sheet->setCellValue('S'.$row, $rs->total_amount);
        $sheet->setCellValue('T'.$row, $rs->shipping_fee);
        $sheet->setCellValue('U'.$row, $rs->service_fee);
        $sheet->setCellValue('V'.$row, $rs->state);
        $sheet->setCellValue('W'.$row, $rs->sender);
        $no++;
        $row++;
      }

      $sheet->getStyle('B6:B'.$row)->getNumberFormat()->setFormatCode('dd/mm/yyyy');

    }


    setToken($token);
    $file_name = "Repor Sale Online Channels Details.xlsx";
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="'.$file_name.'"');
    $writer = new Xlsx($excel);
    $writer->save('php://output');
  }
}