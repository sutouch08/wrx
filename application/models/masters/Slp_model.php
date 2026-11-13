<?php
class Slp_model extends CI_Model
{
  private $tb = "saleman";

  public function __construct()
  {
    parent::__construct();
  }


  public function get_all_slp()
  {
    $rs = $this->ms->select('SlpCode AS id, SlpName AS name, Active AS active')->get('OSLP');
    return $rs->num_rows() > 0 ? $rs->result() : NULL;
  }


  public function is_exists($id)
  {
    return $this->db->where('id', $id)->count_all_results($this->tb) > 0;    
  }


  public function add($ds = array())
  {
    return !empty($ds) ? $this->db->insert($this->tb, $ds) : FALSE;
  }


  public function update($id, $ds = array())
  {
    return !empty($ds) ? $this->db->where('id', $id)->update($this->tb, $ds) : FALSE;    
  }



  public function get_name($code)
  {
    $rs = $this->ms->select('SlpName')->where('SlpCode', $code)->get('OSLP');
    return $rs->num_rows() === 1 ? $rs->row()->SlpName : "";
  }


  public function count_rows($ds = array())
  {
    $this->db->where('id IS NOT NULL', NULL, FALSE);

    if($ds['active'] != 'all')
    {
      $this->db->where('active', $ds['active']);
    }

    if(!empty($ds['name']))
    {
      $this->db->like('name', $ds['name']);
    }

    return $this->db->count_all_results($this->tb);
  }


  public function get_list($ds = array(), $limit = 20, $offset = 0)
  {
    if($ds['active'] != 'all')
    {
      $this->db->where('active', $ds['active']);
    }

    if(!empty($ds['name']))
    {
      $this->db->like('name', $ds['name']);
    }

    $rs = $this->db->limit($limit, $offset)->get($this->tb);
    return $rs->result();
  }


  public function get_data()
  {
    $rs = $this->db->where('id > ', 0)->get($this->tb);
    return $rs->num_rows() > 0 ? $rs->result() : NULL;
  }

  public function get_all()
  {
    $rs = $this->db->get($this->tb);
    return $rs->num_rows() > 0 ? $rs->result() : NULL;
  }
}
