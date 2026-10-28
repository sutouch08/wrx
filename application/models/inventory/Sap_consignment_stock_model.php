<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sap_consignment_stock_model extends CI_Model
{
  public function __construct()
  {
    parent::__construct();
  }

  public function consignment_warehouse_in()
  {
    $whs = ['x'];

    $rs = $this->db->select('code')->where('role', 2)->where('is_consignment', 1)->get('warehouse');
    if($rs->num_rows() > 0)
    {
      foreach($rs->result() as $row)
      {
        $whs[] = $row->code;
      }
    }

    return $whs;
  }

  public function get_list($ds = [], $perpage = 20, $offset = 0)
  {
    if(!empty($ds['item_code']) OR !empty($ds['zone_code']))
    {
      $this->cn
      ->select('I.ItemCode, I.ItemName, I.U_OLDCODE, B.BinCode, B.Descr, Q.OnHandQty')
      ->from('OIBQ Q')
      ->join('OBIN B', 'Q.BinAbs = B.AbsEntry', 'left')
      ->join('OITM I', 'Q.ItemCode = I.ItemCode', 'left')
      ->where_in('Q.WhsCode', $ds['whs'])
      ->where('Q.OnHandQty !=', 0);

      if(!empty($ds['show_system']))
      {
        if($ds['show_system'] == 'no')
        {
          $this->cn->where('B.SysBin', 'N');
        }
      }

      if(!empty($ds['item_code']))
      {
        $this->cn->like('I.ItemCode', $ds['item_code']);
      }

      if(!empty($ds['zone_code']))
      {
        $this->cn->like('B.BinCode', $ds['zone_code']);
      }

      $this->cn->order_by('Q.ItemCode', 'ASC');
      $this->cn->order_by('B.BinCode', 'ASC');
      $rs = $this->cn->limit($perpage, $offset)->get();

      return $rs->num_rows() > 0 ? $rs->result() : NULL;
    }

    return NULL;
  }

  public function count_rows($ds = [])
  {
    if(!empty($ds['item_code']) OR !empty($ds['zone_code']))
    {
      $this->cn
      ->from('OIBQ Q')
      ->join('OBIN B', 'Q.BinAbs = B.AbsEntry', 'left')
      ->join('OITM I', 'Q.ItemCode = I.ItemCode', 'left')
      ->where_in('Q.WhsCode', $ds['whs'])
      ->where('Q.OnHandQty !=', 0);

      if(!empty($ds['show_system']))
      {
        if($ds['show_system'] == 'no')
        {
          $this->cn->where('B.SysBin', 'N');
        }
      }

      if(!empty($ds['item_code']))
      {
        $this->cn->like('I.ItemCode', $ds['item_code']);
      }

      if(!empty($ds['zone_code']))
      {
        $this->cn->like('B.BinCode', $ds['zone_code']);
      }

      return $this->cn->count_all_results();
    }

    return 0;
  }

  public function get_data($ds = [], $limit = 1000, $offset = 0)
  {
    if(!empty($ds['item_code']) OR !empty($ds['zone_code']))
    {
      $this->cn
      ->select('I.ItemCode, I.ItemName, I.U_OLDCODE, B.BinCode, B.Descr, Q.OnHandQty')
      ->from('OIBQ Q')
      ->join('OBIN B', 'Q.BinAbs = B.AbsEntry', 'left')
      ->join('OITM I', 'Q.ItemCode = I.ItemCode', 'left')
      ->where_in('Q.WhsCode', $ds['whs'])
      ->where('Q.OnHandQty !=', 0);

      if(!empty($ds['show_system']))
      {
        if($ds['show_system'] == 'no')
        {
          $this->cn->where('B.SysBin', 'N');
        }
      }

      if(!empty($ds['item_code']))
      {
        $this->cn->like('I.ItemCode', $ds['item_code']);
      }

      if(!empty($ds['zone_code']))
      {
        $this->cn->like('B.BinCode', $ds['zone_code']);
      }

      $rs = $this->cn
      ->order_by('Q.ItemCode', 'ASC')
      ->order_by('B.BinCode', 'ASC')
      ->limit($limit, $offset)->get();

      return $rs->num_rows() > 0 ? $rs->result() : NULL;
    }

    return NULL;
  }


  public function get_stock_zone($zone_code, $pd_code)
  {
    $this->cn->select_sum('OIBQ.OnHandQty', 'qty')
    ->from('OBIN')
    ->join('OIBQ', 'OBIN.WhsCode = OIBQ.WhsCode AND OBIN.AbsEntry = OIBQ.BinAbs', 'left')
    ->where('OIBQ.ItemCode', $pd_code)
    ->where('OBIN.BinCode', $zone_code);
    $rs = $this->cn->get();
    if($rs->num_rows() == 1)
    {
      return intval($rs->row()->qty);
    }

    return 0;
  }
} 
