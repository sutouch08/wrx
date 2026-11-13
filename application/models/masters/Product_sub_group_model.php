<?php
class Product_sub_group_model extends CI_Model
{
  private $tb = 'product_sub_group';
  private $sb = 'MAJOR';

  public function __construct()
  {
    parent::__construct();
  }

  public function add(array $ds = array())
  {
    return ! empty($ds) ? $this->db->insert($this->tb, $ds) : FALSE;
  }

  public function update($code, array $ds = array())
  {
    return ! empty($ds) ? $this->db->where('code', $code)->update($this->tb, $ds) : FALSE;
  }

  public function delete($code)
  {
    return $this->db->where('code', $code)->delete($this->tb);
  }

  public function count_rows($ds = array())
  {
    if (! empty($ds['code']))
    {
      $this->db->like('code', $ds['code']);
    }

    if (! empty($ds['name']))
    {
      $this->db->like('name', $ds['name']);
    }

    return $this->db->count_all_results($this->tb);
  }

  public function get_list($ds = array(), $perpage = 20, $offset = 0)
  {
    if (! empty($ds['code']))
    {
      $this->db->like('code', $ds['code']);
    }

    if (! empty($ds['name']))
    {
      $this->db->like('name', $ds['name']);
    }

    return $this->db->limit($perpage, $offset)->get($this->tb)->result();
  }

  public function get_all()
  {
    return $this->db->get($this->tb)->result();
  }

  public function get($code)
  {
    $rs = $this->db->where('code', $code)->get($this->tb);
    return $rs->num_rows() === 1 ? $rs->row() : NULL;
  }

  public function get_name($code)
  {
    $rs = $this->db->select('name')->where('code', $code)->get($this->tb);
    return $rs->num_rows() === 1 ? $rs->row()->name : '';
  }

  public function is_exists($code, $old_code = '')
  {
    if ($old_code != '') $this->db->where('code !=', $old_code);
    return $this->db->where('code', $code)->count_all_results($this->tb) > 0;
  }

  public function is_exists_name($name, $code = '')
  {
    if (! empty($code)) $this->db->where('code !=', $code);
    return $this->db->where('name', $name)->count_all_results($this->tb) > 0;
  }

  public function count_members($code)
  {
    return $this->db->where('sub_group_code', $code)->count_all_results('products');
  }

  public function is_sap_exists($code)
  {
    return $this->mc->select('Code')->where('Code', $code)->count_all_results($this->sb) > 0;
  }

  public function add_sap(array $ds = array())
  {
    return ! empty($ds) ? $this->mc->insert($this->sb, $ds) : FALSE;
  }

  public function update_sap($code, $ds = array())
  {
    return ! empty($ds) ? $this->mc->where('Code', $code)->update($this->sb, $ds) : FALSE;
  }
}