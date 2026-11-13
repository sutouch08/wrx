<?php
class Product_color_model extends CI_Model
{
  private $tb = "product_color";
  private $tg = "product_color_group";

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

  public function delete_by_id($id)
  {
    return $this->db->where('id', $id)->delete($this->tb);
  }

  public function count_rows(array $ds = array())
  {
    if(isset($ds['status']) && $ds['status'] != 'all')
    {
      $this->db->where('active', $ds['status']);
    }

    if(!empty($ds['code']))
    {
      $this->db->like('code', $ds['code']);
    }

    if(!empty($ds['name']))
    {
      $this->db->like('name', $ds['name']);
    }

    if(isset($ds['color_group']) && $ds['color_group'] != 'all')
    {
      if($ds['color_group'] === 'NULL')
      {
        $this->db->where('id_group IS NULL', NULL, FALSE);
      }
      else
      {
        $this->db->where('id_group', $ds['color_group']);
      }
    }

    return $this->db->count_all_results($this->tb);
  }

  public function get_list($ds = array(), $perpage = 20, $offset = 0)
  {
    $this->db
    ->select('co.*')
    ->select('cg.code AS group_code, cg.name AS group_name')
    ->from('product_color AS co')
    ->join('product_color_group AS cg', 'co.id_group = cg.id', 'left');

    if(isset($ds['status']) && $ds['status'] != 'all')
    {
      $this->db->where('co.active', $ds['status']);
    }

    if(!empty($ds['code']))
    {
      $this->db->like('co.code', $ds['code']);
    }

    if(!empty($ds['name']))
    {
      $this->db->like('co.name', $ds['name']);
    }

    if(isset($ds['color_group']) && $ds['color_group'] != 'all' )
    {
      if($ds['color_group'] === 'NULL')
      {
        $this->db->where('co.id_group IS NULL', NULL, FALSE);
      }
      else
      {
        $this->db->where('co.id_group', $ds['color_group']);
      }
    }

    $rs = $this->db->limit($perpage, $offset)->get();
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
    return $rs->num_rows() === 1 ? $rs->row()->name : NULL;
  }

  public function get_all()
  {
    $rs = $this->db->get($this->tb);
    return $rs->num_rows() > 0 ? $rs->result() : NULL;
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

  public function set_active($code, $active)
  {
    return $this->db->set('active', $active)->where('code', $code)->update($this->tb);
  }

  public function count_members($code)
  {
    return $this->db->where('color_code', $code)->count_all_results('products');
  }

  public function get_color_group()
  {
    $rs = $this->db->order_by('name', 'ASC')->get($this->tg);
    return $rs->num_rows() > 0 ? $rs->result() : FALSE;
  }

  public function is_sap_exists($code)
  {
    return $this->mc->select('Code')->where('Code', $code)->count_all_results('COLOR') > 0;
  }

  public function add_sap_color(array $ds = array())
  {
    return !empty($ds) ? $this->mc->insert('COLOR', $ds) : FALSE;
  }

  public function update_sap_color($code, array $ds = array())
  {
    return !empty($ds) ? $this->mc->where('Code', $code)->update('COLOR', $ds) : FALSE;    
  }
}
