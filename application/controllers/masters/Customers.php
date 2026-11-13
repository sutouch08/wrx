<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Customers extends PS_Controller
{
  public $menu_code = 'DBCUST';
	public $menu_group_code = 'DB';
  public $menu_sub_group_code = 'CUSTOMER';
	public $title = 'เพิ่ม/แก้ไข รายชื่อลูกค้า';
  public $segment = 4;

  public function __construct()
  {
    parent::__construct();
    $this->home = base_url().'masters/customers';
    $this->load->model('masters/customers_model');
    $this->load->model('masters/customer_group_model');
    $this->load->model('masters/customer_kind_model');
    $this->load->model('masters/customer_type_model');
    $this->load->model('masters/customer_class_model');
    $this->load->model('masters/customer_area_model');
    $this->load->helper('customer');
    $this->load->helper('address');
  }


  public function index()
  {
    $filter = array(
      'code' => get_filter('code', 'cu_code', ''),
      'group' => get_filter('group', 'cu_group', 'all'),
      'kind' => get_filter('kind', 'cu_kind', 'all'),
      'type' => get_filter('type', 'cu_type', 'all'),
      'class' => get_filter('class', 'cu_class', 'all'),
      'area' => get_filter('area', 'cu_area', 'all'),
      'status' => get_filter('status', 'cu_status', 'all')
    );

    if ($this->input->post('search'))
    {
      redirect($this->home);
      exit;
    }

		$perpage = get_rows();
		$rows = $this->customers_model->count_rows($filter);		
		$filter['data'] = $this->customers_model->get_list($filter, $perpage, $this->uri->segment($this->segment));
    $init	= pagination_config($this->home.'/index/', $rows, $perpage, $this->segment);
		$this->pagination->initialize($init);
    $this->load->view('masters/customers/customers_view', $filter);
  }

  public function edit($code, $tab='infoTab')
  {
    $this->load->model('address/customer_address_model');
    $this->load->model('address/address_model');
    $rs = $this->customers_model->get($code);
    $bill_to = $this->customer_address_model->get_customer_bill_to_address($code);
    $ship_to = $this->customer_address_model->get_ship_to_address($code);

    $data['ds'] = $rs;
    $data['tab'] = $tab;
    $data['bill'] = $bill_to;
    $data['addr'] = $ship_to;

    $this->load->view('masters/customers/customers_edit_view', $data);
  }


  public function add_bill_to($code)
  {
    $sc = TRUE;
    $ds = json_decode($this->input->post('data') ?? '{}');

    if(empty($ds))
    {
      echo get_error('required');
      return;
    }

    $arr = array(
      'customer_code' => $code,
      'branch_code' => empty($ds->branch_code) ? '0000' : $ds->branch_code,
      'branch_name' => empty($ds->branch_name) ? 'สำนักงานใหญ่' : $ds->branch_name,
      'address' => $ds->address,
      'sub_district' => $ds->sub_district,
      'district' => $ds->district,
      'province' => $ds->province,
      'postcode' => $ds->postcode,
      'country' => empty($ds->country) ? 'TH' : $ds->country,
      'phone' => $ds->phone
    );

    $this->load->model('address/customer_address_model');
    $addr = $this->customer_address_model->get_customer_bill_to_address($code);

    if($addr)
    {
      if( ! $this->customer_address_model->update_bill_to($code, $arr))
      {
        $sc = FALSE;
        set_error('update');
      }
    }
    else
    {
      if( ! $this->customer_address_model->add_bill_to($arr))
      {
        $sc = FALSE;
        set_error('insert');
      }
    }

    $this->_response($sc);
  }

  public function add_ship_to($code)
  {
    $sc = TRUE;
    $ds = json_decode($this->input->post('data') ?? '{}');

    if(empty($ds))
    {
      echo get_error('required');
      return;
    }

    $id = $ds->id;

    $arr = array(
      'code' => $code,
      'customer_code' => $code,
      'alias' => $ds->alias,
      'name' => $ds->name,
      'address' => $ds->address,
      'sub_district' => $ds->sub_district,
      'district' => $ds->district,
      'province' => $ds->province,
      'postcode' => $ds->postcode,
      'country' => empty($ds->country) ? 'TH' : $ds->country,
      'phone' => $ds->phone,
      'email' => $ds->email,
      'is_default' => $ds->default
    );

    $this->load->model('address/customer_address_model');

    if(!empty($id))
    {
      if( ! $this->customer_address_model->update_ship_to($id, $arr))
      {
        $sc = FALSE;
        set_error('update');
      }
    }
    else
    {
      $id = $this->customer_address_model->add_ship_to($arr);

      if( ! $id)
      {
        $sc = FALSE;
        set_error('insert');
      }
    }

    if($sc === TRUE && $ds->default == 1)
    {
      $this->customer_address_model->unset_default_shipping_address($code);
      $this->customer_address_model->set_default_shipping_address($id);
    }

    $this->_response($sc);
  }

  public function set_default_ship_to($code, $id)
  {
    $sc = TRUE;
    $this->load->model('address/customer_address_model');

    if($this->customer_address_model->unset_default_shipping_address($code))
    {
      if (! $this->customer_address_model->set_default_shipping_address($id))
      {
        $sc = FALSE;
        set_error('update');
      }
    }
    else 
    {
      $sc = FALSE;
      set_error('update');
    }    
    
    $this->_response($sc);
  }

  public function get_ship_to($id)
  {
    $this->load->model('address/customer_address_model');
    $shipTo = $this->customer_address_model->get_customer_ship_to_address($id);

    if(!empty($shipTo))
    {
      echo json_encode($shipTo);
    }
    else
    {
      echo get_error('not_found');
    }
  }

  public function delete_ship_to($id)
  {
    $sc = TRUE;
    $this->load->model('address/customer_address_model');

    if(! $this->customer_address_model->delete_ship_to($id))
    {
      $sc = FALSE;
      set_error('delete');
    }

    $this->_response($sc);
  }

  public function export_bill_to_address($code)
  {
    $this->load->model('address/customer_address_model');
    $addr = $this->customer_address_model->get_customer_bill_to_address($code);
    if(!empty($addr))
    {
      $ex = $this->customer_address_model->is_sap_address_exists($code, $addr->address_code);
      if(! $ex)
      {
        $ds = array(
          'Address' => $addr->address_code,
          'CardCode' => $addr->customer_code,
          'Street' => $addr->address,
          'Block' => $addr->sub_district,
          'ZipCode' => $addr->postcode,
          'City' => $addr->province,
          'County' => $addr->district,
          'LineNum' => ($this->customer_address_model->get_max_line_num($code, 'B') + 1),
          'AdresType' => 'B',
          'Address2' => $addr->branch_code,
          'Address3' => $addr->branch_name,
          'F_E_Commerce' => $ex ? 'U' : 'A',
          'F_E_CommerceDate' => sap_date(now(), TRUE)
        );

        $this->customer_address_model->add_sap_bill_to($ds);
      }
      else
      {
        $ds = array(
          'Address' => $addr->address_code,
          'CardCode' => $addr->customer_code,
          'Street' => $addr->address,
          'Block' => $addr->sub_district,
          'ZipCode' => $addr->postcode,
          'City' => $addr->province,
          'County' => $addr->district,
          'AdresType' => 'B',
          'Address2' => $addr->branch_code,
          'Address3' => $addr->branch_name,
          'F_E_Commerce' => $ex ? 'U' : 'A',
          'F_E_CommerceDate' => sap_date(now(),TRUE)
        );

        $this->customer_address_model->update_sap_bill_to($code, $addr->address_code, $ds);
      }
    }
  }

  public function export_ship_to_address($id)
  {
    $this->load->model('address/customer_address_model');
    $addr = $this->customer_address_model->get_customer_ship_to_address($id);
    if(!empty($addr))
    {
      $ex = $this->customer_address_model->is_sap_address_exists($code, $rs->address_code, 'S');
      if(! $ex)
      {
        $ds = array(
          'Address' => $rs->address_code,
          'CardCode' => $rs->customer_code,
          'Street' => $rs->address,
          'Block' => $rs->sub_district,
          'ZipCode' => $rs->postcode,
          'City' => $rs->province,
          'County' => $rs->district,
          'LineNum' => ($this->customer_address_model->get_max_line_num($code, 'S') + 1),
          'AdresType' => 'S',
          'Address2' => '0000',
          'Address3' => 'สำนักงานใหญ่',
          'F_E_Commerce' => $ex ? 'U' : 'A',
          'F_E_CommerceDate' => sap_date(now(), TRUE)
        );

        $this->customer_address_model->add_sap_ship_to($ds);
      }
      else
      {
        $ds = array(
          'Address' => $rs->address_code,
          'CardCode' => $rs->customer_code,
          'Street' => $rs->address,
          'Block' => $rs->sub_district,
          'ZipCode' => $rs->postcode,
          'City' => $rs->province,
          'County' => $rs->district,
          'AdresType' => 'S',
          'Address2' => '0000',
          'Address3' => 'สำนักงานใหญ่',
          'F_E_Commerce' => $ex ? 'U' : 'A',
          'F_E_CommerceDate' => sap_date(now(), TRUE)
        );

        $this->customer_address_model->update_sap_ship_to($code, $rs->address_code, $ds);
      }
    }
  }

  public function set_default_shipping_address()
  {
    $sc = TRUE;
    $code = $this->input->post('customer_code');
    $id_address = $this->input->post('id_address');

    if(!empty($code) && !empty($id_address))
    {
      $this->load->model('address/customer_address_model');
      $this->db->trans_begin();

      if(! $this->customer_address_model->unset_default_shipping_address($code))
      {
        $sc = FALSE;
        $this->error = "Failed to unset default shipping address";
      }

      if($sc === TRUE && ! $this->customer_address_model->set_default_shipping_address($id_address))
      {
        $sc = FALSE;
        $this->error = "Failed to set default shipping address";
      }

      if($sc === TRUE)
      {
        $this->db->trans_commit();        
      }
      else
      {
        $this->db->trans_rollback();
      }
    }
    else
    {
      $sc = FALSE;
      $this->error = "Customer code or address ID is required";      
    }

    $this->_response($sc);
  }

  public function update()
  {    
    $ds = json_decode($this->input->post('data') ?? '{}');

    if(empty($ds) || empty($ds->code) || empty($ds->name)) 
    {
      echo get_error('required');
      exit;
    }    

    $arr = array(
      'name' => $ds->name,
      'Tax_Id' => $ds->tax_id,
      'DebPayAcct' => $ds->DebPayAcct,
      'GroupCode' => $ds->GroupCode,
      'cmpPrivate' => $ds->cmpPrivate,
      'GroupNum' => $ds->GroupNum,
      'group_code' => get_null($ds->group),
      'kind_code' => get_null($ds->kind),
      'type_code' => get_null($ds->type),
      'class_code' => get_null($ds->class),
      'area_code' => get_null($ds->area),
      'sale_code' => get_null($ds->sale),
      'CreditLine' => floatval($ds->CreditLine),
      'gp' => $ds->gp,
      'skip_overdue' => $ds->skip_overdue
    );    

    if(! $this->customers_model->update($ds->code, $arr))
    {
      echo get_error('update');
      exit;
    }

    echo 'success';
  }


  public function delete()
  {
    $code = $this->input->post('code') ?? '';

    if( ! $code)
    {
      echo get_error('required');
      exit;
    }

    if( ! $this->pm->can_delete)
    {
      echo get_error('permission');
      exit;
    }

    $customer = $this->customers_model->get($code);

    if( ! $customer)
    {
      echo get_error('not_found');
      exit;
    }

    if($this->customers_model->has_transection($code))
    {
      echo get_error('has_transection', $code);
      exit;
    }

    if( ! $this->customers_model->delete($code))
    {
      echo get_error('delete');
      exit;
    }

    echo 'success';    
  }

  public function do_export($code)
  {
    $this->load->model('masters/slp_model');
    $cs = $this->customers_model->get($code);
    if(!empty($cs))
    {
      $ds = array(
        'CardCode' => $cs->code,
        'CardName' => $cs->name,
        'CardType' => $cs->CardType,
        'GroupCode' => $cs->GroupCode,
        'CmpPrivate' => $cs->cmpPrivate,
        'SlpCode' => $cs->sale_code,
        //'SlpName' => $this->slp_model->get_name($cs->sale_code),
        'Currency' => getConfig('CURRENCY'),
        'GroupNum' => $cs->GroupNum,
        'VatStatus' => 'Y',
        'LicTradNum' => $cs->Tax_Id,
        'DebPayAcct' => $cs->DebPayAcct,
        'U_BPBACKLIST' => 'N',
        'F_E_Commerce' => 'A',
        'F_E_CommerceDate' => sap_date($cs->date_upd, TRUE)
      );

      if($this->customers_model->sap_customer_exists($cs->code))
      {
        $ds['F_E_Commerce'] = 'U';

        return $this->customers_model->update_sap_customer($cs->code, $ds);
      }
      else
      {
        return $this->customers_model->add_sap_customer($ds);
      }
    }

    return FALSE;
  }


  public function export_customer($code)
  {
    $rs = $this->do_export($code);

    if($rs === TRUE)
    {
      $this->export_bill_to_address($code);
      $this->export_ship_to_address($code);
      echo 'success';
    }
    else
    {
      echo 'Export fail';
    }
  }


  public function syncData($type = 'update')
  {
    $last_sync = $type == 'update' ? $this->customers_model->get_last_sync_date() : from_date('2020-01-01');
    $ds = $this->customers_model->get_update_data($last_sync);

    if(!empty($ds))
    {
      foreach($ds as $rs)
      {
        $arr = array(
          'code' => $rs->code,
          'name' => $rs->name,
          'Tax_Id' => $rs->Tax_Id,
          'DebPayAcct' => $rs->DebPayAcct,
          'CardType' => $rs->CardType,
          'GroupCode' => $rs->GroupCode,
          'cmpPrivate' => $rs->CmpPrivate,
          'GroupNum' => $rs->GroupNum,
          'sale_code' => $rs->sale_code,
          'CreditLine' => floatval($rs->CreditLine),
          'old_code' => $rs->old_code,
          'last_sync' => now()
        );

        if($this->customers_model->is_exists($rs->code))
        {
          $this->customers_model->update($rs->code, $arr);
        }
        else
        {
          $this->customers_model->add($arr);
        }
      }
    }

    echo 'success';
  }

  public function clear_filter()
	{
    $filter = array(
      'cu_code',
      'cu_status',
      'cu_group',
      'cu_kind',
      'cu_type',
      'cu_class',
      'cu_area'
    );

    return clear_filter($filter);
	}

  public function get_new_code($code)
  {
    $max = $this->customer_address_model->get_max_code($code);
    $max++;
    return $max;
  }

  public function get_ship_to_table()
  {
    $sc = TRUE;

    if($this->input->post('customer_code'))
    {
      $code = $this->input->post('customer_code');

      if(!empty($code))
      {
        $ds = array();
        $this->load->model('address/customer_address_model');
        $adrs = $this->customer_address_model->get_ship_to_address($code);

        if(!empty($adrs))
        {
          foreach($adrs as $rs)
          {
            $ds[] = array(
              'customer_code' => $code,
              'id' => $rs->id,
              'name' => $rs->name,
              'address' => $rs->address.' '.$rs->sub_district.' '.$rs->district.' '.$rs->province.' '.$rs->postcode,
              'phone' => $rs->phone,
              'email' => $rs->email,
              'alias' => $rs->alias,
              'default' => $rs->is_default == 1 ? 1 : ''
            );            
          }
        }
        else
        {
          $sc = FALSE;
        }
      }
      else
      {
        $sc = FALSE;
      }
    }

    echo $sc === TRUE ? json_encode($ds) : 'noaddress';
  }

  public function delete_shipping_address()
  {
    $this->load->model('address/address_model');
    $id = $this->input->post('id_address');
    $rs = $this->address_model->delete_shipping_address($id);
    echo $rs === TRUE ? 'success' : 'fail';
  }
} 
