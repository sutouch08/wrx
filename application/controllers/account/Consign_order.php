<?php
defined('BASEPATH') or exit('No direct script access allowed');

use OpenSpout\Reader\XLSX\Reader;

class Consign_order extends PS_Controller
{
  public $menu_code = 'ACCSOD';
  public $menu_group_code = 'AC';
  public $menu_sub_group_code = '';
  public $title = 'ตัดยอดฝากขายแท้';
  public $segment = 4;

  public function __construct()
  {
    parent::__construct();
    $this->home = base_url() . 'account/consign_order';
    $this->load->model('account/consign_order_model');
    $this->load->model('masters/customers_model');
    $this->load->model('masters/zone_model');
    $this->load->model('masters/warehouse_model');
    $this->load->model('masters/products_model');
    $this->load->helper('discount');
    $this->load->helper('print');
    $this->load->helper('consignment_order');
  }

  public function index()
  {
    $filter = array(
      'code' => get_filter('code', 'consign_code', ''),
      'customer' => get_filter('customer', 'consign_customer', ''),
      'zone' => get_filter('zone', 'consign_zone', ''),
      'from_date' => get_filter('from_date', 'consign_from_date', ''),
      'to_date' => get_filter('to_date', 'consign_to_date', ''),
      'ref_code' => get_filter('ref_code', 'consign_ref_code', ''),
      'tax_status' => get_filter('tax_status', 'consign_tax_status', 'all'),
      'is_etax' => get_filter('is_etax', 'consign_is_etax', 'all'),
      'status' => get_filter('status', 'consign_status', 'all'),
      'is_api' => get_filter('is_api', 'consign_is_api', 'all'),
      'sap' => get_filter('sap', 'consign_sap', 'all')
    );

    if ($this->input->post('search'))
    {
      redirect($this->home);
      exit;
    }

    $perpage = get_rows();
    $rows = $this->consign_order_model->count_rows($filter);
    $docs = $this->consign_order_model->get_list($filter, $perpage, $this->uri->segment($this->segment));
    $init  = pagination_config($this->home . '/index/', $rows, $perpage, $this->segment);

    if (!empty($docs))
    {
      foreach ($docs as $rs)
      {
        $rs->amount = $this->consign_order_model->get_sum_amount($rs->code);
      }
    }

    $filter['docs'] = $docs;
    $this->pagination->initialize($init);
    $this->load->view('account/consign_order/consign_order_list', $filter);
  }

  public function add_new()
  {
    $this->load->view('account/consign_order/consign_order_add');
  }

  public function add()
  {
    $sc = TRUE;

    if ($this->pm->can_add)
    {
      $ds = json_decode($this->input->post('data') ?? '');

      if (! empty($ds))
      {
        $date = db_date($ds->date_add, TRUE);
        $bookcode = getConfig('BOOK_CODE_CONSIGN_SOLD') ?? '';
        $customer = $this->customers_model->get($ds->customer_code);

        if (empty($customer))
        {
          $sc = FALSE;
          $this->error = "ไม่พบข้อมูลลูกค้า : {$ds->customer_code}";
        }

        $zone = $sc === TRUE ? $this->zone_model->get($ds->zone_code) : NULL;

        if ($sc === TRUE && empty($zone))
        {
          $sc = FALSE;
          $this->error = "ไม่พบข้อมูลโซน : {$ds->zone_code}";
        }

        $code = $this->get_new_code($date);

        if (empty($code))
        {
          sleep(1);

          $code = $this->get_new_code($date);

          if (empty($code))
          {
            $sc = FALSE;
            $this->error = "Cannot generate new document number";
          }
        }

        if ($sc === TRUE)
        {
          $arr = array(
            'code' => $code,
            'bookcode' => $bookcode,
            'customer_code' => $customer->code,
            'customer_name' => $customer->name,
            'zone_code' => $zone->code,
            'zone_name' => $zone->name,
            'warehouse_code' => $zone->warehouse_code,
            'remark' => get_null($ds->remark),
            'date_add' => $date,
            'user' => $this->_user->uname
          );

          if (! $this->consign_order_model->add($arr))
          {
            $sc = FALSE;
            $this->error = "เพิ่มเอกสารไม่สำเร็จ";
          }
        }
      }
      else
      {
        $sc = FALSE;
        set_error('required');
      }
    }
    else
    {
      $sc = FALSE;
      set_error('permission');
    }

    $arr = array(
      'status' => $sc ? 'success' : 'error',
      'message' => $sc === TRUE ? 'success' : $this->error,
      'code' => isset($code) ? $code : ''
    );

    echo json_encode($arr);
  }

  public function edit($code)
  {
    $doc = $this->consign_order_model->get($code);
    $details = $this->consign_order_model->get_details($code);
    if (!empty($details))
    {
      foreach ($details as $rs)
      {
        $rs->barcode = $this->products_model->get_barcode($rs->product_code);
      }
    }

    $gb_auz = getConfig('ALLOW_UNDER_ZERO');
    $wh_auz = $this->warehouse_model->is_auz($doc->warehouse_code);
    $auz = $gb_auz == 1 ? 1 : ($wh_auz === TRUE ? 1 : 0);
    $ds = array(
      'doc' => $doc,
      'details' => $details,
      'auz' => $auz
    );

    $this->load->view('account/consign_order/consign_order_edit', $ds);
  }

  public function update()
  {
    $sc = TRUE;
    $ds = json_decode($this->input->post('data') ?? '');

    if (empty($ds))
    {
      $sc = FALSE;
      set_error('required');
    }

    if ($sc === TRUE && ! $this->pm->can_edit)
    {
      $sc = FALSE;
      set_error('permission');
    }

    if ($sc === TRUE && (empty($ds->code) || empty($ds->zone_code) || empty($ds->customer_code)))
    {
      $sc = FALSE;
      set_error('required');
    }

    if ($sc === TRUE)
    {
      $code = $ds->code;
      $doc = $this->consign_order_model->get($code);

      if (empty($doc))
      {
        $sc = FALSE;
        $this->error = "Invalid document {" . $code . "}";
      }
      else
      {
        if ($doc->status != 0)
        {
          $sc = FALSE;
          set_error('status');
        }
      }
    }

    if ($sc === TRUE)
    {
      $date_add = db_date($ds->date_add, TRUE);
      $customer = $this->customers_model->get($ds->customer_code);
      $zone = $this->zone_model->get($ds->zone_code);

      if (empty($customer))
      {
        $sc = FALSE;
        $this->error = "Invalid customer {" . $ds->customer_code . "}";
      }

      if ($sc === TRUE && empty($zone))
      {
        $sc = FALSE;
        $this->error = "Invalid zone {" . $ds->zone_code . "}";
      }
    }

    if ($sc === TRUE)
    {
      $arr = array(
        'customer_code' => $customer->code,
        'customer_name' => $customer->name,
        'zone_code' => $zone->code,
        'zone_name' => $zone->name,
        'warehouse_code' => $zone->warehouse_code,
        'remark' => get_null(trim($ds->remark)),
        'date_add' => $date_add,
        'user' => $this->_user->uname
      );

      if (! $this->consign_order_model->update($code, $arr))
      {
        $sc = FALSE;
        set_error('update');
      }
    }

    $this->_response($sc);
  }

  public function cancle($code)
  {
    $sc = TRUE;
    $this->load->model('inventory/delivery_order_model');

    if ($this->pm->can_delete)
    {
      $do = $this->delivery_order_model->get_sap_delivery_order($code);

      if (empty($do))
      {
        $doc = $this->consign_order_model->get($code);
        //--- check status
        if ($doc->status == 1)
        {
          $sc = FALSE;
          $this->error = "คุณต้องยกเลิกการบันทึกก่อนยกเลิกเอกสาร";
        }
        else
        {
          $this->db->trans_begin();
          //--- if WX loaded remove and change WX status
          if (!empty($doc->ref_code))
          {
            $this->load->model('inventory/consign_check_model');
            $this->consign_check_model->update_ref_code($doc->ref_code, NULL, 0);
          }

          if (! $this->consign_order_model->drop_details($code))
          {
            $sc = FALSE;
            $this->error = "ลบรายการไม่สำเร็จ";
          }
          else
          {
            $arr = array(
              'status' => 2,
              'cancle_reason' => $this->input->post('reason'),
              'cancle_user' => $this->_user->uname
            );
            if (! $this->consign_order_model->update($code, $arr))
            {
              $sc = FALSE;
              $this->error = "เปลี่ยนสถานะเอกสารไม่สำเร็จ";
            }
          }

          if ($sc === TRUE)
          {
            $this->db->trans_commit();
          }
          else
          {
            $this->db->trans_rollback();
          }

          if ($sc === TRUE)
          {
            //---- drop middle details
            $middle = $this->delivery_order_model->get_middle_delivery_order($code);

            if (!empty($middle))
            {
              foreach ($middle as $rows)
              {
                if ($this->delivery_order_model->drop_middle_exits_data($rows->DocEntry) === FALSE)
                {
                  $sc = FALSE;
                  $this->error = "ลบรายการที่ค้างใน Temp ไม่สำเร็จ";
                }
              }
            }
          }
        }
      }
      else
      {
        $sc = FALSE;
        $this->error = "เอกสารเข้า SAP แล้วไม่อนุญาติให้แก้ไข";
      }
    }
    else
    {
      $sc = FALSE;
      $this->error = "คุณไม่มีสิทธิ์ยกเลิกเอกสาร";
    }

    $this->_response($sc);
  }

  public function view_detail($code)
  {
    $this->load->model('inventory/delivery_order_model');
    $this->load->helper('print');
    $doc = $this->consign_order_model->get($code);
    $details = $this->consign_order_model->get_details($code);
    $do = $this->delivery_order_model->exists_sap_delivery_order($code);

    if (!empty($details))
    {
      foreach ($details as $rs)
      {
        $rs->barcode = $this->products_model->get_barcode($rs->product_code);
      }
    }

    $ds = array(
      'doc' => $doc,
      'details' => $details,
      'in_sap' => empty($do) ? FALSE : TRUE,
      'view_detail' => TRUE
    );

    $this->load->view('account/consign_order/consign_order_view_detail', $ds);
  }

  //---- add or update detail row by key in
  public function add_detail($code)
  {
    $sc = TRUE;
    if ($this->input->post('product_code'))
    {
      $doc = $this->consign_order_model->get($code);
      if (!empty($doc))
      {
        $this->load->model('stock/stock_model');

        $product_code = $this->input->post('product_code');
        $price = $this->input->post('price');
        $qty = $this->input->post('qty');
        $discLabel = $this->input->post('disc');
        $disc = parse_discount_text($discLabel, $price);
        $discount = $disc['discount_amount'];
        $amount = ($price - $discount) * $qty;

        $gb_auz = getConfig('ALLOW_UNDER_ZERO');
        $wh_auz = $this->warehouse_model->is_auz($doc->warehouse_code);
        $auz = $gb_auz == 1 ? TRUE : $wh_auz;

        $item = $this->products_model->get($product_code);
        $input_type = 1;  //--- 1 = key in , 2 = load diff, 3 = excel
        $stock = $item->count_stock == 1 ? $this->stock_model->get_stock_zone($doc->zone_code, $item->code) : 10000000;
        $c_qty = $item->count_stock == 1 ? $this->consign_order_model->get_unsave_qty($code, $item->code, $price, $discLabel, $input_type) : 0;
        $detail = $this->consign_order_model->get_exists_detail($code, $product_code, $price, $discLabel, $input_type);
        $sum_qty = $qty + $c_qty;
        $id = FALSE;

        if (empty($detail))
        {
          //--- ถ้าจำนวนที่ยังไม่บันทึก รวมกับจำนวนใหม่ไม่เกินยอดในโซน หรือ คลังสามารถติดลบได้
          if ($sum_qty <= $stock or $auz === TRUE)
          {
            //--- add new row
            $arr = array(
              'consign_code' => $code,
              'style_code' => $item->style_code,
              'product_code' => $item->code,
              'product_name' => $item->name,
              'cost' => $item->cost,
              'price' => $price,
              'qty' => $qty,
              'discount' => discountLabel($disc['discount1'], $disc['discount2'], $disc['discount3']),
              'discount_amount' => $discount * $qty,
              'amount' => $amount,
              'ref_code' => $doc->ref_code,
              'input_type' => $input_type
            );

            $id = $this->consign_order_model->add_detail($arr); //-- return id if success

            if ($id === FALSE)
            {
              $sc = FALSE;
              $this->error = "เพิ่มรายการไม่สำเร็จ";
            }
          }
          else
          {
            $sc = FALSE;
            $this->error = "<span>{$item->code} ยอดในโซนไม่พอตัด  ในโซน: {$stock} ยอดตัด : {$sum_qty} </span><br/>";
          }
        }
        else
        {
          //-- update new rows
          //--- ถ้าจำนวนที่ยังไม่บันทึก รวมกับจำนวนใหม่ไม่เกินยอดในโซน หรือ คลังสามารถติดลบได้
          $id = $detail->id;
          $new_qty = $qty + $detail->qty;
          if ($sum_qty <= $stock or $auz === TRUE)
          {
            //--- add new row
            $arr = array(
              'qty' => $new_qty,
              'discount_amount' => $discount * $new_qty,
              'amount' => ($price - $discount) * $new_qty
            );

            if (! $this->consign_order_model->update_detail($id, $arr))
            {
              $sc = FALSE;
              $this->error = "ปรับปรุงรายการไม่สำเร็จ";
            }
          }
          else
          {
            $sc = FALSE;
            $this->error = "<span>{$item->code} ยอดในโซนไม่พอตัด  ในโซน: {$stock} ยอดตัด : {$sum_qty} </span><br/>";
          }
        }
      }
      else
      {
        $sc = FALSE;
        $this->error = "เลขที่เอกสารไม่ถูกต้อง";
      }
    }
    else
    {
      $sc = FALSE;
      $this->error = "รหัสสินค้าไม่ถูกต้อง";
    }

    if ($sc === TRUE)
    {
      $rs = $this->consign_order_model->get_detail($id);
      $ds = array(
        'id' => $rs->id,
        'barcode' => $item->barcode,
        'product_code' => $rs->product_code,
        'product_name' => $rs->product_name,
        'product' => $rs->product_code . ' : ' . $rs->product_name,
        'price' => round($rs->price, 2),
        'qty' => $rs->qty,
        'discount' => $rs->discount,
        'amount' => $rs->amount
      );
    }

    echo $sc === TRUE ? json_encode($ds) : $this->error;
  }

  //---- add or update detail row by key in
  public function add_details($code)
  {
    $sc = TRUE;
    $data = $this->input->post('data');

    if (!empty($data))
    {
      $doc = $this->consign_order_model->get($code);

      if (!empty($doc))
      {
        $this->load->model('stock/stock_model');
        $gb_auz = getConfig('ALLOW_UNDER_ZERO');
        $wh_auz = $this->warehouse_model->is_auz($doc->warehouse_code);
        $auz = $gb_auz == 1 ? TRUE : $wh_auz;
        $discLabel = $this->input->post('discount');

        foreach ($data as $rs)
        {
          $product_code = $rs['code']; //-- รหัสสินค้า
          $qty = $rs['qty'];
          $item = $this->products_model->get($product_code);
          $price = $item->price;
          $disc = parse_discount_text($discLabel, $price);
          $discount = $disc['discount_amount'];
          $amount = ($price - $discount) * $qty;
          $input_type = 1;  //--- 1 = key in , 2 = load diff, 3 = excel
          $stock = $item->count_stock == 1 ? $this->stock_model->get_stock_zone($doc->zone_code, $item->code) : 10000000;
          $c_qty = $item->count_stock == 1 ? $this->consign_order_model->get_unsave_qty($code, $item->code, $price, $discLabel, $input_type) : 0;
          $detail = $this->consign_order_model->get_exists_detail($code, $product_code, $price, $discLabel, $input_type);
          $sum_qty = $qty + $c_qty;
          $id;

          if (empty($detail))
          {
            //--- ถ้าจำนวนที่ยังไม่บันทึก รวมกับจำนวนใหม่ไม่เกินยอดในโซน หรือ คลังสามารถติดลบได้
            if ($sum_qty <= $stock or $auz === TRUE)
            {
              //--- add new row
              $arr = array(
                'consign_code' => $code,
                'style_code' => $item->style_code,
                'product_code' => $item->code,
                'product_name' => $item->name,
                'cost' => $item->cost,
                'price' => $price,
                'qty' => $qty,
                'discount' => discountLabel($disc['discount1'], $disc['discount2'], $disc['discount3']),
                'discount_amount' => $discount * $qty,
                'amount' => $amount,
                'ref_code' => $doc->ref_code,
                'input_type' => $input_type
              );

              $id = $this->consign_order_model->add_detail($arr); //-- return id if success

              if ($id === FALSE)
              {
                $sc = FALSE;
                $this->error = "เพิ่มรายการไม่สำเร็จ";
              }
            }
            else
            {
              $sc = FALSE;
              $this->error .= "<span>{$item->code} ยอดในโซนไม่พอตัด  ในโซน: {$stock} ยอดตัด : {$sum_qty} </span><br/>";
            }
          }
          else
          {
            //-- update new rows
            //--- ถ้าจำนวนที่ยังไม่บันทึก รวมกับจำนวนใหม่ไม่เกินยอดในโซน หรือ คลังสามารถติดลบได้
            $id = $detail->id;
            $new_qty = $qty + $detail->qty;
            if ($sum_qty <= $stock or $auz === TRUE)
            {
              //--- add new row
              $arr = array(
                'qty' => $new_qty,
                'discount_amount' => $discount * $new_qty,
                'amount' => ($price - $discount) * $new_qty
              );

              if (! $this->consign_order_model->update_detail($id, $arr))
              {
                $sc = FALSE;
                $this->error = "ปรับปรุงรายการไม่สำเร็จ";
              }
            }
            else
            {
              $sc = FALSE;
              $this->error .= "<span>{$item->code} ยอดในโซนไม่พอตัด  ในโซน: {$stock} ยอดตัด : {$sum_qty} </span><br/>";
            }
          }
        }
      }
      else
      {
        $sc = FALSE;
        $this->error = "เลขที่เอกสารไม่ถูกต้อง";
      }
    }
    else
    {
      $sc = FALSE;
      $this->error = "ไม่พบข้อมูล";
    }

    $this->_response($sc);
  }

  public function save_consign($code)
  {
    $sc = TRUE;
    $this->load->model("stock/stock_model");
    $this->load->model("masters/warehouse_model");
    $this->load->model('inventory/movement_model');
    $this->load->model('inventory/delivery_order_model');
    $doc = $this->consign_order_model->get($code);
    $gb_auz = getConfig('ALLOW_UNDER_ZERO');
    $wh_auz = $this->warehouse_model->is_auz($doc->warehouse_code);
    $auz = $gb_auz == 1 ? TRUE : $wh_auz;

    if ($doc->status == 0)
    {
      $details = $this->consign_order_model->get_details($code);

      if (!empty($details))
      {
        $this->db->trans_begin();

        //--- check stock and update status each row
        foreach ($details as $rs)
        {
          //--- get item info
          $item = $this->products_model->get($rs->product_code);

          if (!empty($item))
          {
            $stock = $item->count_stock == 1 ? $this->stock_model->get_stock_zone($doc->zone_code, $item->code) : 1000000;
            $all_qty = $this->consign_order_model->get_sum_order_qty($doc->code, $item->code);

            if ($all_qty <= $stock or $auz)
            {
              $final_price = $rs->amount / $rs->qty;
              //--- ข้อมูลสำหรับบันทึกยอดขาย
              $arr = array(
                'reference' => $doc->code,
                'role'   => $doc->role, ///--- ตัดยอดฝากขาย(shop)
                'product_code'  => $rs->product_code,
                'product_name'  => $rs->product_name,
                'product_style' => $rs->style_code,
                'cost'  => $rs->cost,
                'price'  => $rs->price,
                'sell'  => $final_price,
                'qty'   => $rs->qty,
                'discount_label'  => $rs->discount,
                'discount_amount' => $rs->discount_amount,
                'total_amount'   => $rs->amount,
                'total_cost'   => $rs->cost * $rs->qty,
                'margin'  => ($final_price * $rs->qty) - ($rs->cost * $rs->qty),
                'id_policy'   => NULL,
                'id_rule'     => NULL,
                'customer_code' => $doc->customer_code,
                'customer_ref' => NULL,
                'sale_code'   => NULL,
                'user' => $doc->user,
                'date_add'  => $doc->date_add,
                'zone_code' => $doc->zone_code,
                'warehouse_code'  => $doc->warehouse_code,
                'update_user' => get_cookie('uname'),
                'budget_code' => NULL
              );

              //--- 1.บันทึกขาย
              if (! $this->delivery_order_model->sold($arr))
              {
                $sc = FALSE;
                $this->error = 'บันทึกขายไม่สำเร็จ';
                break;
              }

              //--- 2. update movement
              $arr = array(
                'reference' => $doc->code,
                'warehouse_code' => $doc->warehouse_code,
                'zone_code' => $doc->zone_code,
                'product_code' => $rs->product_code,
                'move_in' => 0,
                'move_out' => $rs->qty,
                'date_add' => $doc->date_add
              );

              if (! $this->movement_model->add($arr))
              {
                $sc = FALSE;
                $this->error = 'บันทึก movement ขาออกไม่สำเร็จ';
                break;
              }

              if (! $this->consign_order_model->change_detail_status($rs->id, 1))
              {
                $sc = FALSE;
                $this->error = "บันทึกรายการไม่สำเร็จ : {$rs->product_code}";
              }
            }
            else
            {
              $sc = FALSE;
              $this->error .= "<span>{$rs->product_code} ยอดในโซนไม่พอตัด  ในโซน: {$stock} ยอดตัด : {$all_qty} </span><br/>";
            }
          }
          else
          {
            $sc = FALSE;
            $this->error = "ไม่พบรายการสินค้า : {$rs->product_code}";
          }
        }

        //--- if no error
        if ($sc === TRUE)
        {
          if (! $this->consign_order_model->change_status($code, 1))
          {
            $sc = FALSE;
            $this->error = "บันทึกสถานะเอกสารไม่สำเร็จ";
          }
        }

        if ($sc === TRUE)
        {
          $this->db->trans_commit();
        }
        else
        {
          $this->db->trans_rollback();
        }

        if ($sc === TRUE)
        {
          $this->export_consign_sold($code);
        }
      }
    }
    else
    {
      $sc = FALSE;
      $this->error = "เอกสารถูกบันทึกไปแล้ว";
    }

    $this->_response($sc);
  }

  public function unsave_consign($code)
  {
    $sc = TRUE;
    $this->load->model("stock/stock_model");
    $this->load->model("masters/warehouse_model");
    $this->load->model('inventory/movement_model');
    $this->load->model('inventory/invoice_model');
    $this->load->model('inventory/delivery_order_model');

    $doc = $this->consign_order_model->get($code);

    if ($doc->status == 1)
    {
      $do = $this->delivery_order_model->get_sap_delivery_order($code);

      if (empty($do))
      {
        $this->db->trans_begin();

        //--- remove movement
        if ($sc === TRUE && ! $this->movement_model->drop_movement($code))
        {
          $sc = FALSE;
          $this->error = "ลบ movement ไม่สำเร็จ";
        }

        //--- Remove sold data
        if ($sc === TRUE && ! $this->invoice_model->drop_all_sold($code))
        {
          $sc = FALSE;
          $this->error = "ลบยอดขาย ไม่สำเร็จ";
        }

        //--- change status details
        if ($sc === TRUE && ! $this->consign_order_model->change_all_detail_status($code, 0))
        {
          $sc = FALSE;
          $this->error = "เปลี่ยนสถานะรายการไม่สำเร็จ";
        }

        //--- change document status        
        if ($sc === TRUE && ! $this->consign_order_model->change_status($code, 0))
        {
          $sc = FALSE;
          $this->error = "เปลี่ยนสถานะเอกสารไม่สำเร็จ";
        }

        if ($sc === TRUE)
        {
          $this->db->trans_commit();
        }
        else
        {
          $this->db->trans_rollback();
        }

        if ($sc === TRUE)
        {
          //--- drop middle details
          $middle = $this->delivery_order_model->get_middle_delivery_order($code);

          if (!empty($middle))
          {
            foreach ($middle as $rows)
            {
              if ($this->delivery_order_model->drop_middle_exits_data($rows->DocEntry) === FALSE)
              {
                $sc = FALSE;
                $this->error = "ลบรายการที่ค้างใน Temp ไม่สำเร็จ";
              }
            }
          }
        }
      }
      else
      {
        $sc = FALSE;
        $this->error = "ยกเลิกเอกสารใน SAP ก่อนย้อนสถานะ";
      }
    }

    $this->_response($sc);
  }

  public function delete_detail($id)
  {
    $sc = TRUE;
    $ds = $this->consign_order_model->get_detail($id);

    if (!empty($ds))
    {
      if ($ds->status == 1)
      {
        $sc = FALSE;
        $this->error = "รายการถูกบันทึกแล้วไม่สามารถลบได้";
      }
      else
      {
        if (! $this->consign_order_model->delete_detail($id))
        {
          $sc = FALSE;
          $this->error = "ลบรายการไม่สำเร็จ";
        }
      }
    }
    else
    {
      $sc = FALSE;
      $this->error = "ไม่พบรายการที่ต้องการลบ";
    }

    $this->_response($sc);
  }

  public function remove_checked()
  {
    $sc = TRUE;

    if ($this->pm->can_add or $this->pm->can_edit)
    {
      $ids = $this->input->post('ids');
      $code = $this->input->post('code');

      if (empty($code) || empty($ids))
      {
        $sc = FALSE;
        set_error('required');
      }

      if ($sc === TRUE)
      {
        $doc = $this->consign_order_model->get($code);

        if (empty($doc))
        {
          $sc = FALSE;
          set_error('not_found');
        }
        else
        {
          if ($doc->status != 0)
          {
            $sc = FALSE;
            set_error('status');
          }
        }
      }

      if ($sc === TRUE)
      {
        if (! $this->consign_order_model->delete_details_by_ids($ids))
        {
          $sc = FALSE;
          set_error('delete_failed');
        }
      }
    }
    else
    {
      $sc = FALSE;
      set_error('permission');
    }

    $this->_response($sc);
  }

  public function import_excel_file()
  {
    $sc = TRUE;
    $this->load->model('stock/stock_model');
    $doc = NULL;
    $code = $this->input->post('code');
    $file = isset($_FILES['excel']) ? $_FILES['excel'] : FALSE;

    if (empty($code) or $file === FALSE)
    {
      $sc = FALSE;
      set_error('required');
      echo $this->error;
      return;
    }

    if ($sc === TRUE)
    {
      $doc = $this->consign_order_model->get($code);

      if (empty($doc))
      {
        $sc = FALSE;
        $this->error = "ไม่พบเอกสาร";
        echo $this->error;
        return;
      }
    }

    if ($file !== FALSE)
    {
      $file  = 'excel';

      $config = array(   // initial config for upload class
        "allowed_types" => "xlsx",
        "upload_path" => $this->config->item('consign_file_path'),
        "file_name"  => $code . '-' . date('YmdHis'),
        "max_size" => 5120,
        "overwrite" => TRUE
      );

      $this->load->library("upload", $config);

      if (! $this->upload->do_upload($file))
      {
        $sc = FALSE;
        $this->error = $this->upload->display_errors();
      }
      else
      {
        $info = $this->upload->data();
        $file_path = $info['full_path'];

        $gb_auz = getConfig('ALLOW_UNDER_ZERO');
        $wh_auz = $this->warehouse_model->is_auz($doc->warehouse_code);
        $auz = $gb_auz == 1 ? TRUE : $wh_auz;

        $reader = new Reader();
        $reader->open($file_path);
        $sheet = $reader->getSheetIterator()->current();
        $collection = [];

        if (empty($sheet))
        {
          $sc = FALSE;
          $this->error = "ไม่พบข้อมูลในไฟล์ Excel";
        }

        if ($sc === TRUE)
        {
          $isFirstRow = TRUE;
          $rowIndex = 1;

          foreach ($sheet->getRowIterator() as $row)
          {
            if ($sc === FALSE)
            {
              break;
            }

            if ($isFirstRow)
            {
              $isFirstRow = FALSE;
              $rowIndex++;
              continue;
            }

            $cells = $row->getCells();
            $product_code = isset($cells[0]) ? trim($cells[0]->getValue()) : '';

            if (!empty($product_code))
            {
              $price = isset($cells[1]) ? floatval(preg_replace('/[^\d.]/', '', $cells[1]->getValue())) : 0;
              $qty = isset($cells[2]) ? floatval(preg_replace('/[^\d.]/', '', $cells[2]->getValue())) : 1;
              $discLabel = isset($cells[3]) ? $cells[3]->getValue() : '';
              $item = empty($product_code) ? NULL : $this->products_model->get($product_code);

              if (! empty($item))
              {
                if ($item->active)
                {
                  $collection[] = (object) [
                    'rowNo' => $rowIndex,
                    'consign_code' => $code,
                    'style_code' => $item->style_code,
                    'product_code' => $item->code,
                    'product_name' => $item->name,
                    'cost' => $item->cost,
                    'price' => $price,
                    'qty' => $qty,
                    'discLabel' => $discLabel,
                    'count_stock' => $item->count_stock
                  ];
                }
                else
                {
                  $sc = FALSE;
                  $this->error = "Inactive Item : {$product_code} at row {$rowIndex}";
                }
              }
              else
              {
                $sc = FALSE;
                $this->error = "Item not found : {$product_code} at row {$rowIndex}";
              }
            }

            $rowIndex++;
          }
        }

        $reader->close();

        if ($sc === TRUE && ! empty($collection))
        {
          $this->db->trans_begin();

          foreach ($collection as $rs)
          {
            $disc = parse_discount_text($rs->discLabel, $rs->price);
            $discount = $disc['discount_amount'];
            $amount = ($rs->price - $discount) * $rs->qty;
            $input_type = 3;  //--- 1 = key in , 2 = load diff, 3 = excel
            $stock = $rs->count_stock == 1 ? $this->stock_model->get_stock_zone($doc->zone_code, $rs->product_code) : 10000000;
            $c_qty = $rs->count_stock == 1 ? $this->consign_order_model->get_unsave_qty($code, $rs->product_code, $rs->price, $rs->discLabel, $input_type) : 0;
            $diff = $stock - ($rs->qty + $c_qty);
            $detail = $this->consign_order_model->get_exists_detail($code, $rs->product_code, $rs->price, $rs->discLabel, $input_type);

            if (empty($detail))
            {
              //--- add new detail if stock is sufficient or authorized
              //--- check if stock is sufficient or if authorization is provided
              if (($rs->qty + $c_qty > $stock) || $auz)
              {
                $arr = array(
                  'consign_code' => $code,
                  'style_code' => $rs->style_code,
                  'product_code' => $rs->product_code,
                  'product_name' => $rs->product_name,
                  'cost' => $rs->cost,
                  'price' => $rs->price,
                  'qty' => $rs->qty,
                  'discount' => discountLabel($disc['discount1'], $disc['discount2'], $disc['discount3']),
                  'discount_amount' => $discount * $rs->qty,
                  'amount' => $amount,
                  'ref_code' => NULL,
                  'input_type' => $input_type
                );

                if (! $this->consign_order_model->add_detail($arr))
                {
                  $sc = FALSE;
                  $this->error = "Failed to add detail for product : {$rs->product_code} at row {$rs->rowNo}";
                }
              }
              else
              {
                $sc = FALSE;
                $this->error = "Insufficient stock for product : {$rs->product_code} at row {$rs->rowNo}";
              }
            }
            else
            {
              //-- update existing detail if stock is sufficient or warehouse allow negative stock
              //-- ถ้าจำนวนที่ยังไม่บันทึกรวมกับจำนวนใหม่ไม่เกินยอดในโซน หรือ คลังสามารถติดลบได้
              $new_qty = $rs->qty + $c_qty;

              if ($new_qty <= $stock || $auz)
              {
                //--- update detail row
                $arr = array(
                  'qty' => $new_qty,
                  'discount_amount' => $discount * $new_qty,
                  'amount' => ($rs->price - $discount) * $new_qty,
                );

                if (! $this->consign_order_model->update_detail($rs->id, $arr))
                {
                  $sc = FALSE;
                  $this->error = "Failed to update detail for product : {$rs->product_code} at row {$rs->rowNo}";
                }
              }
              else
              {
                $sc = FALSE;
                $this->error = "Insufficient stock {$diff} for product : {$rs->product_code} at row {$rs->rowNo}. Required: " . ($qty + $c_qty) . " Available: {$stock}";
              }
            }
          }

          if ($sc === TRUE)
          {
            $this->db->trans_commit();
          }
          else
          {
            $this->db->trans_rollback();
          }
        }
      }
    }
    else
    {
      $sc = FALSE;
      $this->error = "Upload file not found";
    }

    $this->_response($sc);
  }


  public function get_active_check_list($zone_code)
  {
    $ds = array();
    $this->load->model('inventory/consign_check_model');
    $list = $this->consign_check_model->get_active_check_list($zone_code); //--- saved and not valid

    if (!empty($list))
    {
      foreach ($list as $rs)
      {
        $arr = array(
          'code' => $rs->code,
          'date_add' => thai_date($rs->date_add)
        );

        array_push($ds, $arr);
      }
    }
    else
    {
      array_push($ds, array('nodata' => 'nodata'));
    }

    echo json_encode($ds);
  }



  function load_check_diff($code)
  {
    $sc = TRUE;
    if ($this->input->post('check_code'))
    {
      $this->load->model('inventory/consign_check_model');
      $doc = $this->consign_order_model->get($code);
      $check_code = $this->input->post('check_code');
      $input_type = 2; //---- load diff
      $details = $this->consign_check_model->get_diff_details($check_code);
      if (!empty($details))
      {
        $this->db->trans_start();
        $this->consign_order_model->update_ref_code($code, $check_code);
        foreach ($details as $rs)
        {
          $item = $this->products_model->get($rs->product_code);
          $discLabel = $this->consign_order_model->get_item_gp($item->code, $doc->zone_code);
          $disc = parse_discount_text($discLabel, $item->price);
          $discount = $disc['discount_amount'];
          $amount = ($item->price - $discount) * $rs->diff;
          $detail = $this->consign_order_model->get_exists_detail($code, $item->code, $item->price, $discLabel, $input_type);
          if (empty($detail))
          {
            //--- add new row
            $arr = array(
              'consign_code' => $code,
              'style_code' => $item->style_code,
              'product_code' => $item->code,
              'product_name' => $item->name,
              'cost' => $item->cost,
              'price' => $item->price,
              'qty' => $rs->diff,
              'discount' => $discLabel,
              'discount_amount' => $discount * $rs->diff,
              'amount' => $amount,
              'ref_code' => $check_code,
              'input_type' => $input_type
            );

            $this->consign_order_model->add_detail($arr);
          }
          else
          {

            //-- update new rows
            //--- ถ้าจำนวนที่ยังไม่บันทึก รวมกับจำนวนใหม่ไม่เกินยอดในโซน หรือ คลังสามารถติดลบได้
            $new_qty = $rs->diff + $detail->qty;
            //--- add new row
            $arr = array(
              'qty' => $new_qty,
              'discount_amount' => $discount * $new_qty,
              'amount' => ($item->price - $discount) * $new_qty
            );

            $this->consign_order_model->update_detail($detail->id, $arr);
          }
        }
      }

      $this->consign_check_model->update_ref_code($check_code, $code, 1);

      $this->db->trans_complete();

      if ($this->db->trans_status() === FALSE)
      {
        $this->error = "เพิ่มรายการไม่สำเร็จ";
        $sc = FALSE;
      }
    }
    else
    {
      $sc = FALSE;
      $this->error = "ไม่พบเลขที่เอกสารกระทบยอด";
    }

    echo $sc === TRUE ? 'success' : $this->error;
  }


  public function remove_import_details($code)
  {
    $sc = TRUE;
    if ($this->input->post('check_code'))
    {
      $this->load->model('inventory/consign_check_model');
      $doc = $this->consign_order_model->get($code);
      $check_code = $this->input->post('check_code');
      $input_type = 2; //---- load diff

      $saved = $this->consign_order_model->has_saved_imported($code, $check_code);

      if ($saved === FALSE)
      {
        $this->db->trans_start();

        //--- delete details
        $this->consign_order_model->drop_import_details($code, $check_code);

        //--- update ref_code
        $this->consign_order_model->update_ref_code($code, NULL);

        //-- unlink consign_check
        $this->consign_check_model->update_ref_code($check_code, NULL, 0);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE)
        {
          $sc = FALSE;
          $this->error = "ลบรายการไม่สำเร็จ";
        }
      }
      else
      {
        $sc = FALSE;
        $this->error = "ไม่สามารถลบได้เนื่องจากรายการถูกบันทึกแล้ว";
      }
    }
    else
    {
      $sc = FALSE;
      $this->error = "ไม่พบเลขที่เอกสารกระทบยอด";
    }

    echo $sc === TRUE ? 'success' : $this->error;
  }




  public function update_price($code)
  {
    $price_list = $this->input->post('price');
    if (! empty($price_list))
    {
      foreach ($price_list as $id => $price)
      {
        $detail = $this->consign_order_model->get_detail($id);
        if (!empty($detail))
        {
          $disc = parse_discount_text($detail->discount, $price);
          $discount = $disc['discount_amount']; //-- discount amount per pcs

          $arr = array(
            'price' => $price,
            'discount_amount' => $discount * $detail->qty,
            'amount' => ($price - $discount) * $detail->qty
          );

          $this->consign_order_model->update_detail($id, $arr);
        }
      }
    }

    echo 'success';
  }



  public function update_discount($code)
  {
    $dis_list = $this->input->post('disc');
    if (!empty($dis_list))
    {
      foreach ($dis_list as $id => $discLabel)
      {
        $detail = $this->consign_order_model->get_detail($id);
        if (!empty($detail))
        {
          $disc = parse_discount_text($discLabel, $detail->price);
          $discount = $disc['discount_amount'];

          $arr = array(
            'discount' => discountLabel($disc['discount1'], $disc['discount2'], $disc['discount3']),
            'discount_amount' => $discount * $detail->qty,
            'amount' => ($detail->price - $discount) * $detail->qty
          );

          $this->consign_order_model->update_detail($id, $arr);
        }
      }
    }

    echo 'success';
  }


  public function get_item_by_code()
  {
    if ($this->input->get('code'))
    {
      $this->load->model('stock/stock_model');

      $product_code = $this->input->get('code');
      $zone_code = $this->input->get('zone_code');
      $item = $this->products_model->get($product_code);
      if (!empty($item))
      {
        $gp  = $this->consign_order_model->get_item_gp($item->code, $zone_code);
        $stock = $item->count_stock == 1 ? $this->stock_model->get_stock_zone($zone_code, $item->code) : 0;

        $arr = array(
          'pdCode' => $item->code,
          'barcode' => $item->barcode,
          'product' => $item->code,
          'price' => round($item->price, 2),
          'disc' => $gp,
          'stock' => $stock,
          'count_stock' => $item->count_stock
        );

        $sc = json_encode($arr);
      }
      else
      {
        $sc = 'สินค้าไม่ถูกต้อง';
      }

      echo $sc;
    }
    else
    {
      echo "สินค้าไม่ถูกต้อง";
    }
  }


  public function get_item_by_barcode()
  {
    if ($this->input->get('barcode'))
    {
      $this->load->model('stock/stock_model');

      $barcode = $this->input->get('barcode');
      $zone_code = $this->input->get('zone_code');
      $item = $this->products_model->get_product_by_barcode($barcode);
      if (!empty($item))
      {
        $gp  = $this->consign_order_model->get_item_gp($item->code, $zone_code);
        $stock = $item->count_stock == 1 ? $this->stock_model->get_stock_zone($zone_code, $item->code) : 0;

        $arr = array(
          'pdCode' => $item->code,
          'barcode' => $item->barcode,
          'product' => $item->code,
          'price' => round($item->price, 2),
          'disc' => $gp,
          'stock' => $stock,
          'count_stock' => $item->count_stock
        );

        $sc = json_encode($arr);
      }
      else
      {
        $sc = 'สินค้าไม่ถูกต้อง';
      }

      echo $sc;
    }
    else
    {
      echo "สินค้าไม่ถูกต้อง";
    }
  }

  public function get_template_file()
  {
    $this->load->helper('download');
    $file = FCPATH . 'templates/wm-template.xlsx';

    if (file_exists($file))
    {
      force_download($file, NULL);
    }
    else
    {
      $this->page_error();
    }
  }


  public function export_consign($code)
  {
    $rs = $this->export_consign_sold($code);

    if ($rs === FALSE)
    {
      echo $this->error;
    }
    else
    {
      $this->consign_order_model->update($code, array('inv_code' => NULL));
      echo 'success';
    }
  }



  public function print_consign($code)
  {
    $this->load->library('printer');

    $doc = $this->consign_order_model->get($code);
    if (!empty($doc))
    {
      $doc->warehouse_name = $this->warehouse_model->get_name($doc->warehouse_code);
    }

    $details = $this->consign_order_model->get_details($code);
    if (!empty($details))
    {
      foreach ($details as $rs)
      {
        $rs->barcode = $this->products_model->get_barcode($rs->product_code);
      }
    }

    $ds = array(
      'doc' => $doc,
      'details' => $details
    );

    $this->load->view('print/print_consign_sold', $ds);
  }



  public function get_new_code($date)
  {
    $date = $date == '' ? date('Y-m-d') : $date;
    $Y = date('y', strtotime($date));
    $M = date('m', strtotime($date));
    $prefix = getConfig('PREFIX_CONSIGN_SOLD');
    $run_digit = getConfig('RUN_DIGIT_CONSIGN_SOLD');
    $pre = $prefix . '-' . $Y . $M;
    $code = $this->consign_order_model->get_max_code($pre);
    if (!empty($code))
    {
      $run_no = mb_substr($code, ($run_digit * -1), NULL, 'UTF-8') + 1;
      $new_code = $prefix . '-' . $Y . $M . sprintf('%0' . $run_digit . 'd', $run_no);
    }
    else
    {
      $new_code = $prefix . '-' . $Y . $M . sprintf('%0' . $run_digit . 'd', '001');
    }

    return $new_code;
  }



  public function export_consign_sold($code)
  {
    $sc = TRUE;
    $this->load->library('export');

    if (! $this->export->export_consign_order($code))
    {
      $sc = FALSE;
      $this->error = trim($this->export->error);
    }

    return $sc;
  }
  //--- end export_order



  public function clear_filter()
  {
    $filter = array(
      'consign_code',
      'consign_customer',
      'consign_zone',
      'consign_from_date',
      'consign_to_date',
      'consign_status',
      'consign_ref_code',
      'consign_is_api',
      'consign_sap',
      'consign_tax_status',
      'consign_is_etax'
    );

    clear_filter($filter);
  }
} //---- end class
