<?php
class Transport_model extends CI_Model
{
  private $tb = 'address_transport';
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
    return !empty($ds) ? $this->db->where('id', $id)->update($this->tb, $ds) : FALSE;    
  }


  public function delete($id)
  {
    return $this->db->where('id', $id)->delete($this->tb);
  }


  public function get($id)
  {
    $rs = $this->db->where('id', $id)->get($this->tb);
    return $rs->num_rows() === 1 ? $rs->row() : NULL;
  }


  public function get_name($id)
  {
    $rs = $this->db->where('id', $id)->get($this->tb);
    return $rs->num_rows() === 1 ? $rs->row()->name : "";
  }


  public function is_exists($customer_code, $id = NULL)
  {
    if(!empty($id)) $this->db->where('id !=', $id);
    return $this->db->where('customer_code', $customer_code)->count_all_results($this->tb) > 0;
  }


  public function count_rows(array $ds = array())
  {
    $this->db
    ->from($this->tb . ' AS t')
    ->join('customers AS c', 't.customer_code = c.code', 'left');

    if( ! empty($ds['name']))
    {
      $this->db
      ->group_start()
      ->like('t.customer_code', $ds['name'])
      ->or_like('c.name', $ds['name'])
      ->group_end();
    }

    if(isset($ds['sender']) && $ds['sender'] != 'all')
    {
      $this->db
      ->group_start()
      ->where('main_sender', $ds['sender'])
      ->or_where('second_sender', $ds['sender'])
      ->or_where('third_sender', $ds['sender'])
      ->group_end();
    }

    return $this->db->count_all_results();
  }


  public function get_list(array $ds = array(), $perpage = 20, $offset = 0)
  {
    $this->db
    ->select('t.*, c.name AS customer_name')
    ->from($this->tb . ' AS t')
    ->join('customers AS c', 't.customer_code = c.code', 'left');

    if( ! empty($ds['name']))
    {
      $this->db
      ->group_start()
      ->like('t.customer_code', $ds['name'])
      ->or_like('c.name', $ds['name'])
      ->group_end();
    }

    if(isset($ds['sender']) && $ds['sender'] != 'all')
    {
      $this->db
      ->group_start()
      ->where('t.main_sender', $ds['sender'])
      ->or_where('t.second_sender', $ds['sender'])
      ->or_where('t.third_sender', $ds['sender'])
      ->group_end();
    }

    $rs = $this->db->order_by('t.id', 'DESC')->limit($perpage, $offset)->get();
    return $rs->num_rows() > 0 ? $rs->result() : NULL;
  }
} 
