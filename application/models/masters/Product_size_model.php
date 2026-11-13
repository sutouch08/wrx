<?php
class Product_size_model extends CI_Model
{
  private $tb = "product_size";

  public function __construct()
  {
    parent::__construct();
  }

  public function add(array $ds = array())
  {
    return !empty($ds) ? $this->db->insert($this->tb, $ds) : FALSE;
  }

  public function update($code, array $ds = array())
  {
    return !empty($ds) ? $this->db->where('code', $code)->update($this->tb, $ds) : FALSE;
  }

  public function update_by_id($id, array $ds = array())
  {
    return !empty($ds) ? $this->db->where('id', $id)->update($this->tb, $ds) : FALSE;
  }

  public function delete($code)
  {
    return $this->db->where('code', $code)->delete($this->tb);
  }

  public function count_rows(array $ds = array())
  {
    if( ! empty($ds['code'])) $this->db->like('code', $ds['code']);
    if( ! empty($ds['name'])) $this->db->like('name', $ds['name']);
    return $this->db->count_all_results($this->tb);
  }

  public function get_list(array $ds = array(), $perpage = 20, $offset = 0)
  {
    if( ! empty($ds['code'])) $this->db->like('code', $ds['code']);
    if( ! empty($ds['name'])) $this->db->like('name', $ds['name']);
    $this->db->order_by('position', 'ASC')->order_by('code', 'ASC');
    $rs = $this->db->limit($perpage, $offset)->get($this->tb);
    return $rs->num_rows() > 0 ? $rs->result() : NULL;
  }

  public function get_all()
  {
    $rs = $this->db->order_by('position', 'ASC')->order_by('code', 'ASC')->get($this->tb);
    return $rs->num_rows() > 0 ? $rs->result() : NULL;
  }

  public function get($code)
  {
    $rs = $this->db->where('code', $code)->get($this->tb);
    return $rs->num_rows() === 1 ? $rs->row() : NULL;
  }

  public function get_by_id($id)
  {
    $rs = $this->db->where('id', $id)->get($this->tb);
    return $rs->num_rows() === 1 ? $rs->row() : NULL;
  }

  public function get_name($code)
  {
    $rs = $this->db->select('name')->where('code', $code)->get($this->tb);
    return $rs->num_rows() === 1 ? $rs->row()->name : "";
  }

  public function is_exists($code, $id = NULL)
  {
    if( ! empty($id)) $this->db->where('id !=', $id);
    return $this->db->where('code', $code)->count_all_results($this->tb) > 0;
  }

  public function is_exists_name($name, $id = NULL)
  {
    if( ! empty($id)) $this->db->where('id !=', $id);
    return $this->db->where('name', $name)->count_all_results($this->tb) > 0;
  }

  public function count_members($code)
  {
    return $this->db->where('size_code', $code)->count_all_results('products');
  }

  public function is_sap_exists($code)
  {
    return $this->mc->select('Code')->where('Code', $code)->count_all_results('SIZE') > 0;
  }

  public function add_sap_size(array $ds = array())
  {
    return !empty($ds) ? $this->mc->insert('SIZE', $ds) : FALSE;
  }

  public function update_sap_size($code, array $ds = array())
  {
    return !empty($ds) ? $this->mc->where('Code', $code)->update('SIZE', $ds) : FALSE;
  }
}
