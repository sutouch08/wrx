<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sap_consign_stock_model extends CI_Model
{
  public function __construct()
  {
    parent::__construct();
  }

  public function get_list($ds = [], $perpage = 20, $offset = 0)
  {
    if( ! empty($ds['item_code']) OR ! empty($ds['zone_code']))
    {
      $this->ms
      ->select('I.ItemCode, I.ItemName, I.U_OLDCODE, B.BinCode, B.Descr, Q.OnHandQty')
      ->from('OIBQ Q')
      ->join('OBIN B', 'Q.BinAbs = B.AbsEntry', 'left')
      ->join('OITM I', 'Q.ItemCode = I.ItemCode', 'left')
      ->where_in('Q.WhsCode', $ds['whs']) //--- list of consign warehouses
      ->where('Q.OnHandQty !=', 0);

      if(!empty($ds['show_system']))
      {
        if($ds['show_system'] == 'no')
        {
          $this->ms->where('B.SysBin', 'N');
        }
      }

      if(!empty($ds['item_code']))
      {
        $this->ms->like('I.ItemCode', $ds['item_code']);        
      }

      if(!empty($ds['zone_code']))
      {
        $this->ms->like('B.BinCode', $ds['zone_code']);        
      }

      $this->ms->order_by('Q.ItemCode', 'ASC');
      $this->ms->order_by('B.BinCode', 'ASC');
      $rs = $this->ms->limit($perpage, $offset)->get();

      return $rs->num_rows() > 0 ? $rs->result() : NULL;
    }

    return NULL;
  }

  public function count_rows($ds = [])
  {
    if( ! empty($ds['item_code']) OR ! empty($ds['zone_code']))
    {
      $this->ms->from('OIBQ Q')
      ->join('OBIN B', 'Q.BinAbs = B.AbsEntry', 'left')
      ->join('OITM I', 'Q.ItemCode = I.ItemCode', 'left')
      ->where_in('Q.WhsCode', $ds['whs'])
      ->where('Q.OnHandQty !=', 0);

      if(!empty($ds['show_system']))
      {
        if($ds['show_system'] == 'no')
        {
          $this->ms->where('B.SysBin', 'N');
        }
      }

      if(!empty($ds['item_code']))
      {
        $this->ms->like('I.ItemCode', $ds['item_code']);
      }

      if(!empty($ds['zone_code']))
      {
        $this->ms->like('B.BinCode', $ds['zone_code']);
      }

      return $this->ms->count_all_results();
    }

    return 0;
  }

  //--- for export no limit
  public function get_data($ds = [], $limit = 1000, $offset = 0)
  {
    if(!empty($ds['item_code']) OR !empty($ds['zone_code']))
    {
      $this->ms
        ->select('I.ItemCode, I.ItemName, I.U_OLDCODE, B.BinCode, B.Descr, Q.OnHandQty')
        ->from('OIBQ Q')
        ->join('OBIN B', 'Q.BinAbs = B.AbsEntry', 'left')
        ->join('OITM I', 'Q.ItemCode = I.ItemCode', 'left')
        ->where_in('Q.WhsCode', $ds['whs']) //--- list of consign warehouses
        ->where('Q.OnHandQty !=', 0);

      if (!empty($ds['show_system']))
      {
        if ($ds['show_system'] == 'no')
        {
          $this->ms->where('B.SysBin', 'N');
        }
      }

      if (!empty($ds['item_code']))
      {
        $this->ms->like('I.ItemCode', $ds['item_code']);
      }

      if (!empty($ds['zone_code']))
      {
        $this->ms->like('B.BinCode', $ds['zone_code']);
      }

      $this->ms->order_by('Q.ItemCode', 'ASC');
      $this->ms->order_by('B.BinCode', 'ASC');
      $rs = $this->ms->limit($limit, $offset)->get();

      return $rs->num_rows() > 0 ? $rs->result() : NULL;
    }

    return NULL;
  }

  public function consign_warehouse_in()
  {
    $whs = ['x'];

    $rs = $this->db
    ->select('code')
    ->where('role', 2)
    ->where('is_consignment !=', 1)
    ->get('warehouse');

    if($rs->num_rows() > 0)
    {
      foreach($rs->result() as $row)
      {
        $whs[] = $row->code;
      }
    }

    return $whs;
  }
}
