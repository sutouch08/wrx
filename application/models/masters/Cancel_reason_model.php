<?php
class Cancel_reason_model extends CI_Model
{
  private $tb = "cancel_reason";

  public function __construct()
  {
    parent::__construct();
  }

  public function add(array $ds = array())
  {
    return !empty($ds) ? $this->db->insert($this->tb, $ds) : FALSE;
  }

  public function update($id, array $ds = array())
  {
    return !empty($ds) && !empty($id) ? $this->db->where('id', $id)->update($this->tb, $ds) : FALSE;
  }

  public function delete($id)
  {
    return $this->db->where('id', $id)->delete($this->tb);
  }

  public function count_rows(array $ds = array())
  {
    if( ! empty($ds['name']))
    {
      $this->db->like('name', $ds['name']);
    }

    if( isset($ds['active']) && $ds['active'] != 'all')
    {
      $this->db->where('active', $ds['active']);
    }

    return $this->db->count_all_results($this->tb);
  }

  public function get_list(array $ds = array(), $perpage = 20, $offset = 0)
  {
    if( ! empty($ds['name']))
    {
      $this->db->like('name', $ds['name']);
    }

    if( isset($ds['active']) && $ds['active'] != 'all')
    {
      $this->db->where('active', $ds['active']);
    }

    $rs = $this->db->limit($perpage, $offset)->get($this->tb);
    return $rs->num_rows() > 0 ? $rs->result() : NULL;
  }

  public function get($id)
  {
    $rs = $this->db->where('id', $id)->get($this->tb);
    return $rs->num_rows() == 1 ? $rs->row() : NULL;
  }

  public function get_name($id)
  {
    $rs = $this->db->select('name')->where('id', $id)->get($this->tb);
    return $rs->num_rows() > 0 ? $rs->row()->name : "";
  }

  public function is_exists($name, $id = NULL)
  {
    if( ! empty($id)) $this->db->where('id !=', $id);
    return $this->db->where('name', $name)->count_all_results($this->tb) > 0;
  }

  public function get_all_active()
  {
    $rs = $this->db->where('active', 1)->get($this->tb);
    return $rs->num_rows() > 0 ? $rs->result() : NULL;
  }

  public function get_all()
  {
    $rs = $this->db->get($this->tb);
    return $rs->num_rows() > 0 ? $rs->result() : NULL;
  }

  public function has_transection($id)
  {
    return $this->db->where('reason_id', $id)->count_all_results('order_cancle_reason') > 0;
  }
}