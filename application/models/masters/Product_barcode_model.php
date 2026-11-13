<?php
class Product_barcode_model extends CI_Model
{
  public function __construct()
  {
    parent::__construct();
  }

  public function addEan13(array $ds = array())
  {
    return !empty($ds) ? $this->db->insert('barcode_ean13', $ds) : FALSE;
  }

  public function addLocal(array $ds = array())
  {
    return !empty($ds) ? $this->db->insert('barcode_local', $ds) : FALSE;
  }

  public function is_exists($barcode, $id = NULL)
  {
    if( ! empty($id)) $this->db->where('id !=', $id);        
    return $this->db->where('barcode', $barcode)->count_all_results('products') > 0;
  }

  public function get_last_barcode()
  {
    $rs = $this->db->select_max('barcode')->get('barcode_local');
    return $rs->row()->barcode;
  }

  public function get_last_ean_barcode()
  {
    $rs = $this->db->select_max('running')->get('barcode_ean13');
    return $rs->row()->running;
  }
}
