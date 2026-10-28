<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Sap_stock_model extends CI_Model
{
  public function __construct()
  {
    parent::__construct();
  }

  public function get_list($ds = [], $perpage = 20, $offset = 0)
  {
    if (!empty($ds['item_code']) or !empty($ds['zone_code']))
    {
      $this->ms
        ->select('OITM.ItemCode, OITM.ItemName, OITM.U_OLDCODE, OBIN.BinCode, OBIN.Descr, OIBQ.OnHandQty')
        ->from('OIBQ')
        ->join('OBIN', 'OIBQ.BinAbs = OBIN.AbsEntry', 'left')
        ->join('OITM', 'OIBQ.ItemCode = OITM.ItemCode', 'left')
        ->where('OIBQ.OnHandQty !=', 0);

      if (!empty($ds['show_system']))
      {
        if ($ds['show_system'] == 'no')
        {
          $this->ms->where('OBIN.SysBin', 'N');
        }
      }

      if (!empty($ds['item_code']))
      {
        $this->ms->like('OITM.ItemCode', trim($ds['item_code']));
      }

      if (!empty($ds['zone_code']))
      {
        $this->ms->like('OBIN.BinCode', trim($ds['zone_code']));
      }

      $rs = $this->ms
        ->order_by('OIBQ.ItemCode', 'ASC')
        ->order_by('OBIN.BinCode', 'ASC')
        ->limit($perpage, $offset)
        ->get();

      return $rs->num_rows() > 0 ? $rs->result() : NULL;
    }

    return NULL;
  }

  public function count_rows(array $ds = array())
  {
    if (!empty($ds['item_code']) or !empty($ds['zone_code']))
    {
      $this->ms
        ->from('OIBQ')
        ->join('OBIN', 'OIBQ.BinAbs = OBIN.AbsEntry', 'left')
        ->join('OITM', 'OIBQ.ItemCode = OITM.ItemCode', 'left')
        ->where('OIBQ.OnHandQty !=', 0);

      if (!empty($ds['show_system']))
      {
        if ($ds['show_system'] == 'no')
        {
          $this->ms->where('OBIN.SysBin', 'N');
        }
      }

      if (!empty($ds['item_code']))
      {
        $this->ms->like('OITM.ItemCode', trim($ds['item_code']));
      }

      if (!empty($ds['zone_code']))
      {
        $this->ms->like('OBIN.BinCode', trim($ds['zone_code']));
      }

      return $this->ms->count_all_results();
    }

    return 0;
  }

  public function get_data(array $ds = array(), $limit = 1000, $offset = 0)
  {
    if (!empty($ds['item_code']) or !empty($ds['zone_code']))
    {
      $this->ms
        ->select('OITM.ItemCode, OITM.ItemName, OITM.U_OLDCODE, OBIN.BinCode, OBIN.Descr, OIBQ.OnHandQty')
        ->from('OIBQ')
        ->join('OBIN', 'OIBQ.BinAbs = OBIN.AbsEntry', 'left')
        ->join('OITM', 'OIBQ.ItemCode = OITM.ItemCode', 'left')
        ->where('OIBQ.OnHandQty !=', 0);

      if (!empty($ds['show_system']))
      {
        if ($ds['show_system'] == 'no')
        {
          $this->ms->where('OBIN.SysBin', 'N');
        }
      }

      if (!empty($ds['item_code']))
      {
        $this->ms->like('OITM.ItemCode', trim($ds['item_code']));
      }

      if (!empty($ds['zone_code']))
      {
        $this->ms->like('OBIN.BinCode', trim($ds['zone_code']));
      }

      $rs = $this->ms
        ->order_by('OIBQ.ItemCode', 'ASC')
        ->order_by('OBIN.BinCode', 'ASC')
        ->limit($limit, $offset)
        ->get();

      return $rs->num_rows() > 0 ? $rs->result() : NULL;
    }

    return NULL;
  }
}
