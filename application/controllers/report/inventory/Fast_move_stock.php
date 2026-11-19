<?php
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Fast_move_stock extends PS_Controller
{
  public $menu_code = 'RCFMST';
	public $menu_group_code = 'RE';
  public $menu_sub_group_code = 'REINVT';
	public $title = 'รายงานสินค้าคงเหลือในโซน Fast move';
 
  public function __construct()
  {
    parent::__construct();
    $this->home = base_url().'report/inventory/fast_move_stock';
    $this->load->model('report/inventory/fast_move_stock_model');
    $this->load->model('inventory/buffer_model');
  }


  public function index()
  {
    $min_stock = getConfig('MIN_STOCK');
    $min_stock = $min_stock <= 0 ? 0 : intval($min_stock);
    $this->load->view('report/inventory/report_fast_move_stock', ['min_stock' => $min_stock]);
  }


  public function get_report()
  {
    ini_set('memory_limit','2048M'); // This also needs to be increased in some cases. Can be changed to a higher value as per need)
    ini_set('sqlsrv.ClientBufferMaxKBSize','2097152'); // Setting to 2048M
    ini_set('sqlsrv.client_buffer_max_kb_size','2097152'); // Setting to 512M - for pdo_sqlsrv

    $sc = TRUE;
    $ds = json_decode($this->input->post('data'));
    $data = [];

    if( ! empty($ds))
    {
      $zones = $this->fast_move_stock_model->get_fast_move_zone($ds->zone_code);
      $min_stock = intval($ds->min_stock);
      $is_min = $ds->is_min == 0 ? 0 : 1;
      $no = 1;

      if( ! empty($zones))
      {
        $i = 0;
        $j = 0;
        $limit = 100;
        $zone = [];
        $stocks = [];

        foreach($zones as $zn)
        {
          if($i == $limit)
          {
            $i = 0;
            $j++;
          }

          $zone[$j][$i] = $zn->code;
          $i++;
        }

        if( ! empty($zone))
        {
          foreach($zone as $z)
          {
            if( ! empty($z))
            {
              $stocks[] = $this->fast_move_stock_model->get_stock_zone($z, $is_min, $min_stock, $ds->product_code);
            }
          }
        }

        if( ! empty($stocks))
        {
          foreach($stocks as $stock)
          {
            if( ! empty($stock))
            {
              foreach($stock as $rs)
              {
                $buffer = $this->buffer_model->get_buffer_zone($rs->zone_code, $rs->product_code);
                $qty = $rs->qty - $buffer;
                $qty = $qty < 0 ? 0 : $qty;

                if($is_min == 1)
                {
                  if($qty < $min_stock)
                  {
                    $data[] = array(
                      'no' => $no,
                      'zone_code' => $rs->zone_code,
                      'zone_name' => $rs->zone_name,
                      'product_code' => $rs->product_code,
                      'product_name' => $rs->product_name,
                      'qty' => $qty,
                      'color' => 'red'
                    );

                    $no++;
                  }
                }
                else
                {
                  $data[] = array(
                    'no' => $no,
                    'zone_code' => $rs->zone_code,
                    'zone_name' => $rs->zone_name,
                    'product_code' => $rs->product_code,
                    'product_name' => $rs->product_name,
                    'qty' => $qty,
                    'color' => $qty <= $min_stock ? 'red' : ''
                  );

                  $no++;
                }
              }
            }
          }
        }
      }
    }
    else
    {
      $sc = FALSE;
      set_error('required');
    }

    $arr = array(
      'status' => $sc === TRUE ? 'success' : 'failed',
      'message' => $sc === TRUE ? 'success' : $this->error,
      'data' => $data
    );

    echo json_encode($arr);
  }


  public function export_filter()
  {
    ini_set('memory_limit','2048M'); // This also needs to be increased in some cases. Can be changed to a higher value as per need)
    ini_set('sqlsrv.ClientBufferMaxKBSize','2097152'); // Setting to 2048M
    ini_set('sqlsrv.client_buffer_max_kb_size','2097152'); // Setting to 512M - for pdo_sqlsrv

    $ds = json_decode($this->input->post('data') ?? '{}');
    $token = $this->input->post('token');

    $min_stock = intval($ds->min_stock);
    $is_min = $ds->is_min == 0 ? 0 : 1;
    $no = 1;

    $report_title = 'รายงานสินค้าคงเหลือในโซน Fast move ณ วันที่  '.thai_date(date('Y-m-d'), '/');
    $excel = new Spreadsheet();
    $sheet = $excel->getActiveSheet();
    $sheet->setTitle('Fast Move Stock Report');

    //--- set report title header
    $sheet->setCellValue('A1', $report_title);
    $sheet->mergeCells('A1:F1');
    $sheet->setCellValue('A2', "Min Stock : {$min_stock}");
    $sheet->mergeCells('A2:F2');
    $sheet->setCellValue('A3', "แสดงเฉพาะยอดที่ต่ำกว่ากำหนด");
    $sheet->mergeCells('A3:F3');

    //--- set Table header
    $sheet->setCellValue('A4', '#');
    $sheet->setCellValue('B4', 'รหัสโซน');
    $sheet->setCellValue('C4', 'ชื่อโซน');
    $sheet->setCellValue('D4', 'รหัสสินค้า');
    $sheet->setCellValue('E4', 'ชื่อสินค้า');
    $sheet->setCellValue('F4', 'คงเหลือ');

    $row = 5;

    if( ! empty($ds))
    {
      $zones = $this->fast_move_stock_model->get_fast_move_zone(trim($ds->zone_code));

      if( ! empty($zones))
      {
        $i = 0;
        $j = 0;
        $limit = 500;
        $zone = [];
        $stocks = [];

        foreach($zones as $zn)
        {
          if($i == $limit)
          {
            $i = 0;
            $j++;
          }

          $zone[$j][$i] = $zn->code;
          $i++;
        }

        if( ! empty($zone))
        {
          foreach($zone as $z)
          {
            if( ! empty($z))
            {
              $stocks[] = $this->fast_move_stock_model->get_stock_zone($z, $is_min, $min_stock, $ds->product_code);
            }
          }
        }

        if( ! empty($stocks))
        {
          foreach($stocks as $stock)
          {
            if( ! empty($stock))
            {
              foreach($stock as $rs)
              {
                $buffer = $this->buffer_model->get_buffer_zone($rs->zone_code, $rs->product_code);
                $qty = $rs->qty - $buffer;
                $qty = $qty < 0 ? 0 : $qty;

                if($is_min == 1)
                {
                  if($qty < $min_stock)
                  {
                    $sheet->setCellValue("A{$row}", $no);
                    $sheet->setCellValue("B{$row}", $rs->zone_code);
                    $sheet->setCellValue("C{$row}", $rs->zone_name);
                    $sheet->setCellValue("D{$row}", $rs->product_code);
                    $sheet->setCellValue("E{$row}", $rs->product_name);
                    $sheet->setCellValue("F{$row}", $qty);
                    $sheet->getStyle("A{$row}:F{$row}")->getFont()->getColor()->setARGB(Color::COLOR_RED);
                    $no++;
                    $row++;
                  }
                }
                else
                {
                  $sheet->setCellValue("A{$row}", $no);
                  $sheet->setCellValue("B{$row}", $rs->zone_code);
                  $sheet->setCellValue("C{$row}", $rs->zone_name);
                  $sheet->setCellValue("D{$row}", $rs->product_code);
                  $sheet->setCellValue("E{$row}", $rs->product_name);
                  $sheet->setCellValue("F{$row}", $qty);

                  if($qty <= $min_stock)
                  {
                    $sheet->getStyle("A{$row}:F{$row}")->getFont()->getColor()->setARGB(Color::COLOR_RED);
                  }

                  $no++;
                  $row++;
                }
              }
            }
          }
        }
      } // end foreach
    }

    setToken($token);
    $file_name = "Report Fast Move Stock.xlsx";
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); /// form excel 2007 XLSX
    header('Content-Disposition: attachment;filename="'.$file_name.'"');
    $writer = new Xlsx($excel);
    $writer->save('php://output');
  }


  public function print_qr()
  {
    ini_set('memory_limit','2048M'); // This also needs to be increased in some cases. Can be changed to a higher value as per need)
    ini_set('sqlsrv.ClientBufferMaxKBSize','2097152'); // Setting to 2048M
    ini_set('sqlsrv.client_buffer_max_kb_size','2097152'); // Setting to 512M - for pdo_sqlsrv
    
    $ds = json_decode($this->input->post('data') ?? '{}');
    $data = [];

    if( ! empty($ds))
    {
      $zones = $this->fast_move_stock_model->get_fast_move_zone($ds->zone_code);
      $min_stock = intval($ds->min_stock);
      $is_min = $ds->is_min == 0 ? 0 : 1;

      if( ! empty($zones))
      {
        $i = 0;
        $j = 0;
        $limit = 100;
        $zone = [];
        $stocks = [];

        foreach($zones as $zn)
        {
          if($i == $limit)
          {
            $i = 0;
            $j++;
          }

          $zone[$j][$i] = $zn->code;
          $i++;
        }

        if( ! empty($zone))
        {
          foreach($zone as $z)
          {
            if( ! empty($z))
            {
              $stocks[] = $this->fast_move_stock_model->get_stock_zone($z, $is_min, $min_stock, $ds->product_code);
            }
          }
        }

        if( ! empty($stocks))
        {
          foreach($stocks as $stock)
          {
            if( ! empty($stock))
            {
              $this->load->library('ixqrcode');

              foreach($stock as $rs)
              {
                $buffer = $this->buffer_model->get_buffer_zone($rs->zone_code, $rs->product_code);
                $qty = $rs->qty - $buffer;
                $qty = $qty < 0 ? 0 : $qty;

                if($is_min == 1)
                {
                  if($qty < $min_stock)
                  {
                    $qr = array(
                      'data' => $rs->product_code,
                      'size' => 8,
                      'level' => 'H',
                      'savename' => NULL
                    );

                    ob_start();
                    $this->ixqrcode->generate($qr);
                    $qr = base64_encode(ob_get_contents());
                    ob_end_clean();

                    $data[] = (object)['file' => $qr, 'code' => $rs->product_code, 'zone' => $rs->zone_name];
                  }
                }
                else
                {
                  $qr = array(
                    'data' => $rs->product_code,
                    'size' => 8,
                    'level' => 'H',
                    'savename' => NULL
                  );

                  ob_start();
                  $this->ixqrcode->generate($qr);
                  $qr = base64_encode(ob_get_contents());
                  ob_end_clean();

                  $data[] = (object)['file' => $qr, 'code' => $rs->product_code, 'zone' => $rs->zone_name];
                }
              }
            }
          }
        }
      }
    }

    if( ! empty($data))
    {
      $this->load->library('printer');
      $this->load->view('print/print_fast_move_qr', ['items' => $data]);
    }
    else
    {
      echo "No data";
    }

  }

} //--- end class

?>
