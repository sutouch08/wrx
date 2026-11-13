<?php
class Payment_methods_model extends CI_Model
{
  private $tb = "payment_method";

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

  public function update_by_id($id, array $ds = array())
  {
    return ! empty($ds) ? $this->db->where('id', $id)->update($this->tb, $ds) : FALSE;
  }

  public function delete($code)
  {
    return $this->db->where('code', $code)->delete($this->tb);
  }

  public function count_rows(array $ds = array())
  {
    if( ! empty($ds['code'])) 
    {
      $this->db->like('code', $ds['code']);
    }

    if( ! empty($ds['name']))
    {
      $this->db->like('name', $ds['name']);
    }

    if( isset($ds['term']) && $ds['term'] != 'all')
    {
      $this->db->where('has_term', $ds['term']);
    }

    if( isset($ds['role']) && $ds['role'] != 'all')
    {
      $this->db->where('role', $ds['role']);
    }

    return $this->db->count_all_results($this->tb);
  }

  public function get_list(array $ds = array(), $perpage = 20, $offset = 0)
  {
    if (! empty($ds['code']))
    {
      $this->db->like('code', $ds['code']);
    }

    if (! empty($ds['name']))
    {
      $this->db->like('name', $ds['name']);
    }

    if (isset($ds['term']) && $ds['term'] != 'all')
    {
      $this->db->where('has_term', $ds['term']);
    }

    if (isset($ds['role']) && $ds['role'] != 'all')
    {
      $this->db->where('role', $ds['role']);
    }

    $rs = $this->db->limit($perpage, $offset)->get($this->tb);
    return $rs->num_rows() > 0 ? $rs->result() : NULL;
  }

  public function get_payment_methods($code)
  {
    $rs = $this->db->where('code', $code)->get($this->tb);
    return $rs->num_rows() === 1 ? $rs->row() : NULL;
  }

  public function get($code)
  {
    $rs = $this->db->where('code', $code)->get($this->tb);
    return $rs->num_rows() === 1 ? $rs->row() : FALSE;
  }

  public function get_default()
  {
    $rs = $this->db->where('is_default', 1)->get($this->tb);
    return $rs->num_rows() > 0 ? $rs->row() : NULL;    
  }

  public function is_exists($code, $id = NULL)
  {
    if (! empty($id)) $this->db->where('id !=', $id);
    return $this->db->where('code', $code)->count_all_results($this->tb) > 0;
  }

  public function is_exists_name($name, $id = NULL)
  {
    if (! empty($id)) $this->db->where('id !=', $id);
    return $this->db->where('name', $name)->count_all_results($this->tb) > 0;
  }

  public function get_name($code)
  {
    $rs = $this->db->select('name')->where('code', $code)->get($this->tb);
    return $rs->num_rows() === 1 ? $rs->row()->name : "";
  }

  public function has_term($code)
  {
    return $this->db->where('code', $code)->where('has_term', 1)->count_all_results($this->tb) > 0;
  }

  public function get_payment_array()
  {
    $rs = $this->db->get($this->tb);

    if($rs->num_rows() > 0)
    {
      $arr = [];

      foreach($rs->result() as $ds)
      {
        $arr[$ds->code] = $ds->name;
      }

      return $arr;
    }
    
    return [];
  }

  public function has_transection($code)
  {
    return $this->db->where('payment_code', $code)->count_all_results('orders') > 0;
  }
}
