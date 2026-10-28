<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Prepare_list_model extends CI_Model
{
  private $tb = "prepare";

  public function __construct()
  {
    parent::__construct();
  }


  public function get_list(array $ds = array(), $perpage = 20, $offset = 0)
  {
    $this->db->where('id >', $ds['max_id'] ?? 0);

    if (! empty($ds['from_date']))
    {
      $this->db->where('date_upd >=', from_date($ds['from_date']));
    }

    if (! empty($ds['to_date']))
    {
      $this->db->where('date_upd <=', to_date($ds['to_date']));
    }

    if( ! empty($ds['order_code']))
    {
      $this->db->where('order_code', $ds['order_code']);
    }

    if( ! empty($ds['pd_code']))
    {
      $this->db->where('product_code', $ds['pd_code']);
    }

    if( isset($ds['warehouse_code']) && $ds['warehouse_code'] != 'all')
    {
      $this->db->where('warehouse_code', $ds['warehouse_code']);
    }

    if( ! empty($ds['zone_code']))
    {
      $this->db->where('zone_code', $ds['zone_code']);
    }

    if( isset($ds['user']) && $ds['user'] != 'all')
    {
      $this->db->where('user', $ds['user']);
    }        

    $rs = $this->db->order_by('id', 'DESC')->limit($perpage, $offset)->get($this->tb);

    return $rs->num_rows() > 0 ? $rs->result() : NULL;
  }


  public function count_rows(array $ds = array())
  {
    $this->db->where('id >', $ds['max_id'] ?? 0);

    if (! empty($ds['from_date']))
    {
      $this->db->where('date_upd >=', from_date($ds['from_date']));
    }

    if (! empty($ds['to_date']))
    {
      $this->db->where('date_upd <=', to_date($ds['to_date']));
    }

    if( ! empty($ds['order_code']))
    {
      $this->db->where('order_code', $ds['order_code']);
    }

    if( ! empty($ds['pd_code']))
    {
      $this->db->where('product_code', $ds['pd_code']);
    }

    if( isset($ds['warehouse_code']) && $ds['warehouse_code'] != 'all')
    {
      $this->db->where('warehouse_code', $ds['warehouse_code']);
    }

    if( ! empty($ds['zone_code']))
    {
      $this->db->where('zone_code', $ds['zone_code']);
    }

    if( isset($ds['user']) && $ds['user'] != 'all')
    {
      $this->db->where('user', $ds['user']);
    }    

    return $this->db->count_all_results($this->tb);
  }

  public function get_export_data(array $ds = array(), $limit = 1000)
  {
    $this->db
    ->select('p.*')
    ->select('o.date_add AS order_date, o.reference AS reference, o.channels_code AS channels_code, o.state AS state')
    ->from($this->tb.' AS p')
    ->join('orders AS o', 'p.order_code = o.code', 'left');

    $this->db->where('p.id >', $ds['max_id'] ?? 0);

    if (! empty($ds['from_date']))
    {
      $this->db->where('p.date_upd >=', from_date($ds['from_date']));
    }

    if (! empty($ds['to_date']))
    {
      $this->db->where('p.date_upd <=', to_date($ds['to_date']));
    }

    if (! empty($ds['order_code']))
    {
      $this->db->where('p.order_code', $ds['order_code']);
    }

    if (! empty($ds['pd_code']))
    {
      $this->db->where('p.product_code', $ds['pd_code']);
    }

    if (isset($ds['warehouse_code']) && $ds['warehouse_code'] != 'all')
    {
      $this->db->where('p.warehouse_code', $ds['warehouse_code']);
    }

    if (! empty($ds['zone_code']))
    {
      $this->db->where('p.zone_code', $ds['zone_code']);
    }

    if (isset($ds['user']) && $ds['user'] != 'all')
    {
      $this->db->where('p.user', $ds['user']);
    }

    $rs = $this->db->order_by('id', 'ASC')->limit($limit)->get();    

    return $rs->num_rows() > 0 ? $rs->result() : NULL;
  }


  public function get_order_state($code)
  {
    $rs = $this->db->select('state')->where('code', $code)->get('orders');

    if($rs->num_rows() === 1)
    {
      return $rs->row()->state;
    }

    return 0;
  }


  public function state_name_array()
  {
    $state = [];
    $rs = $this->db->get('order_state');

    if($rs->num_rows() > 0)
    {
      foreach($rs->result() as $ro)
      {
        $state[$ro->state] = $ro->name;
      }
    }

    return $state;
  }


  public function channels_name_array()
  {
    $channels = [];
    $rs = $this->db->get('channels');

    if($rs->num_rows() > 0)
    {
      foreach($rs->result() as $ro)
      {
        $channels[$ro->code] = $ro->name;
      }
    }

    return $channels;
  }


  public function get_order($code)
  {
    $rs = $this->db
    ->select('code, reference, channels_code, state, date_add')
    ->where('code', $code)
    ->get('orders');

    if($rs->num_rows() === 1)
    {
      return $rs->row();
    }

    return NULL;
  }

  public function get_max_id($range = 0)
  {
    $range = intval($range);

    if($range === 0)
    {
      return $range;
    }

    $max_id = $this->db->select('MAX(id) AS id')->get($this->tb)->row()->id;
    return $max_id > $range ? $max_id - $range : 0;
  }  
}
