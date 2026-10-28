<?php
class Discount_policy_model extends CI_Model
{
  private $tb = 'discount_policy';
  private $td = 'discount_rule';

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

    if($rs->num_rows() == 1)
    {
      return $rs->row();
    }

    return NULL;
  }

  public function get_code($id)
  {
    $rs = $this->db->select('code')->where('id', $id)->get($this->tb);

    if($rs->num_rows() == 1)
    {
      return $rs->row()->code;
    }

    return NULL;
  }

  public function get_name($id)
  {
    $rs = $this->db->select('name')->where('id', $id)->get($this->tb);

    if($rs->num_rows() == 1)
    {
      return $rs->row()->name;
    }

    return NULL;
  }

  public function count_rows(array $ds = array())
  {   
    if (!empty($ds['code']))
    {
      $this->db->like('code', $ds['code']);
    }

    if (!empty($ds['name']))
    {
      $this->db->like('name', $ds['name']);
    }

    if (isset($ds['active']) && $ds['active'] != 'all')
    {
      $this->db->where('active', $ds['active']);
    }

    if (!empty($ds['start_date']))
    {
      $this->db->where('start_date >=', from_date($ds['start_date']));
    }

    if (!empty($ds['end_date']))
    {
      $this->db->where('end_date <=', to_date($ds['end_date']));
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

    if(isset($ds['active']) && $ds['active'] != 'all')
    {
      $this->db->where('active', $ds['active']);
    }

    if(!empty($ds['start_date']))
    {
      $this->db->where('start_date >=', from_date($ds['start_date']));
    }

    if(!empty($ds['end_date']))
    {
      $this->db->where('end_date <=', to_date($ds['end_date']));
    }

    $rs = $this->db->order_by('code', 'DESC')->limit($perpage, $offset)->get($this->tb);
   
    if($rs->num_rows() > 0)
    {
      return $rs->result();
    }

    return NULL;
  }
  
  public function get_policy_by_code($code)
  {
    $rs = $this->db->where('code', $code)->get($this->tb);

    if($rs->num_rows() == 1)
    {
      return $rs->row();
    }

    return NULL;
  }

  public function get_max_code($prefix = '')
  {
    if(! empty($prefix))
    {
      $rs = $this->db->select_max('code')->like('code', $prefix, 'after')->get($this->tb);

      if($rs->num_rows() == 1)
      {
        return $rs->row()->code;
      }      
    }

    return NULL;    
  }

  public function search($txt)
  {
    $rs = $this->db
    ->select('id')    
    ->like('code', $txt)
    ->like('name', $txt)
    ->get($this->tb);

    if($rs->num_rows() > 0)
    {
      return $rs->result();
    }

    return array();
  }

  public function has_rule($id)
  {
    return $this->db->where('id_policy', $id)->count_all_results($this->td) > 0;
  }

  public function has_transection($id)
  {
    return $this->db->where('id_policy', $id)->count_all_results('order_sold') > 0;
  }

}
