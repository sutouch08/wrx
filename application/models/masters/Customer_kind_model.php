<?php
class Customer_kind_model extends CI_Model
{
  private $tb = 'customer_kind';

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
    if(!empty($ds['code']))
    {
      $this->db->like('code', $ds['code']);
    }

    if(!empty($ds['name']))
    {
      $this->db->like('name', $ds['name']);
    }

    return $this->db->count_all_results($this->tb);
  }


  public function get_list(array $ds = array(), $perpage = 20, $offset = 0)
  {
    if(!empty($ds['code']))
    {
      $this->db->like('code', $ds['code']);
    }

    if(!empty($ds['name']))
    {
      $this->db->like('name', $ds['name']);
    }

    $rs = $this->db->limit($perpage, $offset)->get($this->tb);
    return $rs->result();
  }


  public function get_all()
  {
    $rs = $this->db->get($this->tb);
    return $rs->result();
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
    return $rs->num_rows() === 1 ? $rs->row()->name : NULL;
  }


  public function is_exists($code, $old_code = '')
  {
    if($old_code != '') $this->db->where('code !=', $old_code);    
    return $this->db->where('code', $code)->count_all_results($this->tb) > 0;
  }


  public function is_exists_name($name, $old_code = '')
  {
    if($old_code != '') $this->db->where('code !=', $old_code);
    return $this->db->where('name', $name)->count_all_results($this->tb) > 0;
  }
  
  public function has_transection($code)
  {
    return $this->db->where('kind_code', $code)->count_all_results('discount_rule_customer_kind') > 0;
  }
}
