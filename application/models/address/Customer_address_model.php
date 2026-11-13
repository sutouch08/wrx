<?php
class Customer_address_model extends CI_Model
{
  public function __construct()
  {
    parent::__construct();
  }

  public function unset_default_shipping_address($customer_code)
  {
    return $this->db->where('customer_code', $customer_code)->update('address_ship_to', array('is_default' => 0));
  }

  public function set_default_shipping_address($id_address)
  {
    return $this->db->where('id', $id_address)->update('address_ship_to', array('is_default' => 1));
  }

  public function get_customer_bill_to_address($customer_code)
  {
    $rs = $this->db->where('customer_code', $customer_code)->get('address_bill_to');
    return $rs->num_rows() === 1 ? $rs->row() : NULL;
  }

  public function get_customer_ship_to_address($id)
  {
    $rs = $this->db->where('id', $id)->get('address_ship_to');
    return $rs->num_rows() === 1 ? $rs->row() : NULL;
  }

  public function get_ship_to_address($code)
  {
    $rs = $this->db->where('customer_code', $code)->get('address_ship_to');
    return $rs->num_rows() > 0 ? $rs->result() : NULL;
  }

  public function get_customer_ship_to_id($code)
  {
    $rs = $this->db
    ->where('customer_code', $code)
    ->order_by('is_default', 'DESC')
    ->order_by('id', 'DESC')
    ->limit(1)
    ->get('address_ship_to');

    return $rs->num_rows() === 1 ? $rs->row()->id : NULL;
  }

  public function add_bill_to(array $ds = array())
  {
    return ! empty($ds) ? $this->db->insert('address_bill_to', $ds) : FALSE;    
  }

  public function add_ship_to(array $ds = array())
  {
    if( !empty($ds))
    {
      if($this->db->insert('address_ship_to', $ds))
      {
        return $this->db->insert_id();
      }
    }

    return FALSE;
  }

  public function update_ship_to($id, array $ds = array())
  {
    return ! empty($ds) ? $this->db->where('id', $id)->update('address_ship_to', $ds) : FALSE;
  }

  public function delete_ship_to($id)
  {
    return $this->db->where('id', $id)->delete('address_ship_to');
  }
  
  public function add_sap_bill_to(array $ds = array())
  {
    return ! empty($ds) ? $this->mc->insert('CRD1', $ds) : FALSE;
  }

  public function update_bill_to($customer_code, array $ds = array())
  {
    return ! empty($ds) ? $this->db->where('customer_code', $customer_code)->update('address_bill_to', $ds) : FALSE;
  }

  public function update_sap_bill_to($code, $address, array $ds = array())
  {
    return ! empty($ds) ? $this->mc->where('CardCode', $code)->where('Address', $address)->where('AdresType', 'B')->update('CRD1', $ds) : FALSE;
  }

  //----- Ship To
  public function add_sap_ship_to(array $ds = array())
  {
    return ! empty($ds) ? $this->mc->insert('CRD1', $ds) : FALSE;
  }

  public function update_sap_ship_to($code, $address, array $ds = array())
  {
    return ! empty($ds) ? $this->mc->where('CardCode', $code)->where('Address', $address)->where('AdresType', 'S')->update('CRD1', $ds) : FALSE;
  }

  public function get_max_line_num($code, $type = 'B')
  {
    $rs = $this->mc->select_max('LineNum')->where('CardCode', $code)->where('AdresType', $type)->get('CRD1');
    return $rs->row()->LineNum;
  }

  public function is_sap_address_exists($code, $address, $type = 'B')
  {
    return $this->mc->where('Address', $address)->where('CardCode', $code)->where('AdresType', $type)->count_all_results('CRD1') > 0;   
  }

  public function get_max_code($code)
  {
    $rs = $this->db
    ->select_max('address_code')
    ->where('code', $code)
    ->order_by('address_code', 'DESC')
    ->get('address_ship_to');

    return $rs->num_rows() === 1 ? $rs->row()->address_code : NULL;    
  }
}
