<?php
class Movement_model extends CI_Model
{
  public function __construct()
  {
    parent::__construct();
  }

  public function add(array $ds = array())
  {
    if(!empty($ds))
    {
      return $this->db->insert('stock_movement', $ds);
    }

    return FALSE;
  }

  public function drop_movement($code)
  {
    return $this->db->where('reference', $code)->delete('stock_movement');
  }

  public function get_max_id($range = 0)
  {
    $range = intval($range);

    if($range == 0)
    {
      return 0;
    }

    $max_id = intval($this->db->select_max('id')->get('stock_movement')->row()->id);
    return $max_id >= $range ? $max_id - $range : 0;
  }

  public function get_list(array $ds = array(), $perpage = 20, $offset = 0)
  {
    $this->db->where('id >', intval($ds['id']));
    
    if( ! empty($ds['from_date']))
    {
      $this->db->where('date_upd >=', from_date($ds['from_date']));
    }

    if( ! empty($ds['to_date']))
    {
      $this->db->where('date_upd <=', to_date($ds['to_date']));
    }

    if( isset($ds['warehouse_code']) && $ds['warehouse_code'] != 'all')
    {
      $this->db->where('warehouse_code', $ds['warehouse_code']);
    }

    if( ! empty($ds['reference']))
    {
      $this->db->like('reference', $ds['reference'], 'after');
    }

    if( ! empty($ds['product_code']))
    {
      $this->db->like('product_code', $ds['product_code'], 'after');
    }

    if( ! empty($ds['zone_code']))
    {
      $this->db->like('zone_code', $ds['zone_code']);
    }

    $rs = $this->db
    ->order_by('id', 'DESC')
    ->limit($perpage, $offset)
    ->get('stock_movement');

    return $rs->num_rows() > 0 ? $rs->result() : NULL;    
  }


  public function count_rows(array $ds = array())
  {
    $this->db->where('id >', intval($ds['id']));

    if( ! empty($ds['from_date']))
    {
      $this->db->where('date_upd >=', from_date($ds['from_date']));
    }

    if( ! empty($ds['to_date']))
    {
      $this->db->where('date_upd <=', to_date($ds['to_date']));
    }

    if( isset($ds['warehouse_code']) && $ds['warehouse_code'] != 'all')
    {
      $this->db->where('warehouse_code', $ds['warehouse_code']);
    }

    if( ! empty($ds['reference']))
    {
      $this->db->like('reference', $ds['reference'], 'after');
    }

    if( ! empty($ds['product_code']))
    {
      $this->db->like('product_code', $ds['product_code'], 'after');
    }

    if( ! empty($ds['zone_code']))
    {
      $this->db->like('zone_code', $ds['zone_code']);
    }

    return $this->db->count_all_results('stock_movement');
  }


  public function get_export_data(array $ds = array(), $limit = 1000)
  {
    $this->db->where('id >', intval($ds['id']));

    if( ! empty($ds['from_date']))
    {
      $this->db->where('date_upd >=', from_date($ds['from_date']));
    }

    if( ! empty($ds['to_date']))
    {
      $this->db->where('date_upd <=', to_date($ds['to_date']));
    }

    if( isset($ds['warehouse_code']) && $ds['warehouse_code'] != 'all')
    {
      $this->db->where('warehouse_code', $ds['warehouse_code']);
    }

    if( ! empty($ds['reference']))
    {
      $this->db->like('reference', $ds['reference'], 'after');
    }

    if( ! empty($ds['product_code']))
    {
      $this->db->like('product_code', $ds['product_code'], 'after');
    }

    if( ! empty($ds['zone_code']))
    {
      $this->db->like('zone_code', $ds['zone_code']);
    }

    $rs = $this->db->order_by('id', 'ASC')->limit($limit)->get('stock_movement');

    return $rs->num_rows() > 0 ? $rs->result() : NULL;
  }
} 
