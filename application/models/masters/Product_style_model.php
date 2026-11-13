<?php
class Product_style_model extends CI_Model
{
  private $tb = "product_style";

  public function __construct()
  {
    parent::__construct();
  }

  public function add(array $ds = array())
  {
    return !empty($ds) ? ($this->db->insert($this->tb, $ds) ? $this->db->insert_id() : FALSE) : FALSE;
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

  public function count_sap_list($date_add, $date_upd)
  {
    return $this->ms
    ->where('U_MODEL IS NOT NULL', NULL, FALSE)
    ->where('U_MODEL !=', '')
    ->where('U_MODEL !=', '0')
    ->group_start()
    ->where('CreateDate >', $date_add)
    ->or_where('UpdateDate >', $date_upd)
    ->group_end()
    ->group_by('U_MODEL')
    ->count_all_results('OITM');   
  }

  public function get_sap_list($date_add, $date_upd, $limit, $offset)
  {
    $rs = $this->ms
    ->select('U_MODEL')
    ->where('U_MODEL IS NOT NULL', NULL, FALSE)
    ->where('U_MODEL !=', '')
    ->where('U_MODEL !=', '0')
    ->group_start()
    ->where('CreateDate >=', $date_add)
    ->or_where('UpdateDate >=', $date_upd)
    ->group_end()
    ->group_by('U_MODEL')
    ->limit($limit, $offset)
     ->get('OITM');

    return $rs->num_rows() > 0 ? $rs->result() : NULL;
  }

  public function get_sap_style($code)
  {
    $rs = $this->ms->distinct()
    ->select('OITM.U_MODEL, OITM.U_GROUP, OITM.U_MAJOR')
    ->select('OITM.U_CATE, OITM.U_SUBTYPE, OITM.U_TYPE')
    ->select('OITM.U_BRAND, OITM.U_YEAR, OITM.InvntItem, OITM.InvntryUom')
    ->select('ITM1.Price AS cost, ITM2.Price AS price')
    ->from('OITM')
    ->join('ITM1 AS ITM1', '(ITM1.ItemCode = OITM.ItemCode AND ITM1.PriceList = 13)','left')
    ->join('ITM1 AS ITM2', '(ITM2.ItemCode = OITM.ItemCode AND ITM2.PriceList = 11)', 'left')
    ->where('OITM.U_MODEL', $code)
    ->where('OITM.U_MODEL !=', '')
    ->where('OITM.U_MODEL !=','0')
    ->limit(1)
    ->get();

    return $rs->num_rows() > 0 ? $rs->row() : NULL;
  }

  public function count_rows(array $ds = array())
  {
    if (!empty($ds['code']))
    {
      $this->db->group_start();
      $this->db->like('code', $ds['code']);
      $this->db->or_like('old_code', $ds['code']);
      $this->db->group_end();
    }

    if (!empty($ds['name']))
    {
      $this->db->like('name', $ds['name']);
    }

    if (isset($ds['group']) && $ds['group'] != 'all')
    {
      $ds['group'] === 'NULL' ? $this->db->where('group_code IS NULL', NULL, FALSE) : $this->db->where('group_code', $ds['group']);      
    }

    if (isset($ds['main_group']) && $ds['main_group'] != 'all')
    {
      $ds['main_group'] === 'NULL' ? $this->db->where('main_group_code IS NULL', NULL, FALSE) : $this->db->where('main_group_code', $ds['main_group']);      
    }

    if (isset($ds['sub_group']) && $ds['sub_group'] != 'all')
    {
      $ds['sub_group'] === 'NULL' ? $this->db->where('sub_group_code IS NULL', NULL, FALSE) : $this->db->where('sub_group_code', $ds['sub_group']);      
    }

    if (isset($ds['category']) && $ds['category'] != 'all')
    {
      $ds['category'] === 'NULL' ? $this->db->where('category_code IS NULL', NULL, FALSE) : $this->db->where('category_code', $ds['category']);      
    }

    if (isset($ds['kind']) && $ds['kind'] != 'all')
    {
      $ds['kind'] === 'NULL' ? $this->db->where('kind_code IS NULL', NULL, FALSE) : $this->db->where('kind_code', $ds['kind']);      
    }

    if (isset($ds['type']) && $ds['type'] != 'all')
    {
      $ds['type'] === 'NULL' ? $this->db->where('type_code IS NULL', NULL, FALSE) : $this->db->where('type_code', $ds['type']);      
    }

    if (isset($ds['brand']) && $ds['brand'] != 'all')
    {
      $ds['brand'] === 'NULL' ? $this->db->where('brand_code IS NULL', NULL, FALSE) : $this->db->where('brand_code', $ds['brand']);      
    }

    if (isset($ds['collection']) && $ds['collection'] != 'all')
    {
      $ds['collection'] === 'NULL' ? $this->db->where('collection_code IS NULL', NULL, FALSE) : $this->db->where('collection_code', $ds['collection']);      
    }

    if (isset($ds['year']) && $ds['year'] != 'all')
    {
      $this->db->where('year', $ds['year']);
    }

    if (isset($ds['sell']) && $ds['sell'] != 'all')
    {
      $this->db->where('can_sell', $ds['sell']);
    }

    if (isset($ds['active']) && $ds['active'] != 'all')
    {
      $this->db->where('active', $ds['active']);
    }

    return $this->db->count_all_results($this->tb);
  }

  public function get_list($ds = array(), $perpage = 20, $offset = 0)
  {
    if (!empty($ds['code']))
    {
      $this->db->group_start();
      $this->db->like('code', $ds['code']);
      $this->db->or_like('old_code', $ds['code']);
      $this->db->group_end();
    }

    if (!empty($ds['name']))
    {
      $this->db->like('name', $ds['name']);
    }

    if (isset($ds['group']) && $ds['group'] != 'all')
    {
      $ds['group'] === 'NULL' ? $this->db->where('group_code IS NULL', NULL, FALSE) : $this->db->where('group_code', $ds['group']);
    }

    if (isset($ds['main_group']) && $ds['main_group'] != 'all')
    {
      $ds['main_group'] === 'NULL' ? $this->db->where('main_group_code IS NULL', NULL, FALSE) : $this->db->where('main_group_code', $ds['main_group']);
    }

    if (isset($ds['sub_group']) && $ds['sub_group'] != 'all')
    {
      $ds['sub_group'] === 'NULL' ? $this->db->where('sub_group_code IS NULL', NULL, FALSE) : $this->db->where('sub_group_code', $ds['sub_group']);
    }

    if (isset($ds['category']) && $ds['category'] != 'all')
    {
      $ds['category'] === 'NULL' ? $this->db->where('category_code IS NULL', NULL, FALSE) : $this->db->where('category_code', $ds['category']);
    }

    if (isset($ds['kind']) && $ds['kind'] != 'all')
    {
      $ds['kind'] === 'NULL' ? $this->db->where('kind_code IS NULL', NULL, FALSE) : $this->db->where('kind_code', $ds['kind']);
    }

    if (isset($ds['type']) && $ds['type'] != 'all')
    {
      $ds['type'] === 'NULL' ? $this->db->where('type_code IS NULL', NULL, FALSE) : $this->db->where('type_code', $ds['type']);
    }

    if (isset($ds['brand']) && $ds['brand'] != 'all')
    {
      $ds['brand'] === 'NULL' ? $this->db->where('brand_code IS NULL', NULL, FALSE) : $this->db->where('brand_code', $ds['brand']);
    }

    if (isset($ds['collection']) && $ds['collection'] != 'all')
    {
      $ds['collection'] === 'NULL' ? $this->db->where('collection_code IS NULL', NULL, FALSE) : $this->db->where('collection_code', $ds['collection']);
    }

    if (isset($ds['year']) && $ds['year'] != 'all')
    {
      $this->db->where('year', $ds['year']);
    }

    if (isset($ds['sell']) && $ds['sell'] != 'all')
    {
      $this->db->where('can_sell', $ds['sell']);
    }

    if (isset($ds['active']) && $ds['active'] != 'all')
    {
      $this->db->where('active', $ds['active']);
    }

    $rs = $this->db->order_by('date_upd', 'DESC')->limit($perpage, $offset)->get($this->tb);
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

  public function get_with_old_code($code)
  {
    $rs = $this->db->where('code', $code)->or_where('old_code', $code)->get($this->tb);
    return $rs->num_rows() > 0 ? ($rs->num_rows() === 1 ? $rs->row() : $rs->result()) : NULL;
  }

  public function get_name($code)
  {
    $rs = $this->db->select('name')->where('code', $code)->get($this->tb);
    return $rs->num_rows() === 1 ? $rs->row()->name : "";
  }
 
  public function is_exists($code, $old_code = '')
  {
    if(! empty($old_code)) $this->db->where('code !=', $old_code);    
    return $this->db->where('code', $code)->count_all_results($this->tb) > 0;
  }

  public function is_middle_exists($code)
  {
    return $this->mc->select('Code')->where('Code', $code)->count_all_results('MODEL') > 0;    
  }

  public function is_sap_exists($code)
  {
    return $this->ms->select('Code')->where('Code', $code)->count_all_results('MODEL') > 0;
  }

  public function add_sap_model(array $ds = array())
  {
    return ! empty($ds) ? $this->mc->insert('MODEL', $ds) : FALSE;
  }

  public function update_sap_model($code, array $ds = array())
  {
    return !empty($ds) ? $this->mc->where('Code', $code)->update('MODEL', $ds) : FALSE;
  }

  public function is_exists_name($name, $code = '')
  {
    if(!empty($code)) $this->db->where('code !=', $code);
    return $this->db->where('name', $name)->count_all_results($this->tb) > 0;
  }

  public function count_members($code)
  {
    return $this->db->where('style_code', $code)->count_all_results('products');
  }

  public function get_style_last_sync()
  {
    $rs = $this->db->select_max('last_sync')->get($this->tb);
    return $rs->row()->last_sync;
  }
}
