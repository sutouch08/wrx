<?php
class Consign_stock_report_model extends CI_Model
{
  public function __construct()
  {
    parent::__construct();
  }

  public function count_rows($ds = [])
  {
    $this->ms
    ->from('OIBQ')
    ->join('OBIN', 'OIBQ.BinAbs = OBIN.AbsEntry', 'left')
    ->where('OIBQ.OnHandQty >', 0, FALSE);

    if(isset($ds['allProduct']) && $ds['allProduct'] == 0 && !empty($ds['pdFrom']) && !empty($ds['pdTo']))
    {
      $this->ms->where('OIBQ.ItemCode >=', $ds['pdFrom'])->where('OIBQ.ItemCode <=', $ds['pdTo']);
    }
    
    if(isset($ds['zoneCode']) && !empty($ds['zoneCode']))
    {
      $this->ms->where('OBIN.BinCode', $ds['zoneCode']);
    }
    else 
    {
      if(isset($ds['allWhouse']) && $ds['allWhouse'] == 0 && !empty($ds['warehouse']))
      {
        $this->ms->where_in('OBIN.WhsCode', $ds['warehouse']);
      }
    }

    return $this->ms->count_all_results();
  }

  public function get_consign_stock_zone($ds = [], $limit = 1000, $offset = 0)
  {
    $this->ms
    ->select('OITM.ItemCode AS product_code, OITM.ItemName AS product_name')
    ->select('ITM1.Price AS price')
    ->select('OBIN.WhsCode AS warehouse_code, OBIN.BinCode AS zone_code, OBIN.Descr AS zone_name, OIBQ.OnHandQty AS qty')
    ->from('OIBQ')
    ->join('OITM', 'OIBQ.ItemCode = OITM.ItemCode', 'left')
    ->join('ITM1', 'OITM.ItemCode = ITM1.ItemCode AND ITM1.PriceList = 11', 'left')
    ->join('OBIN', 'OIBQ.BinAbs = OBIN.AbsEntry', 'left')
    ->where('OIBQ.OnHandQty >', 0, FALSE);

    if(isset($ds['allProduct']) && $ds['allProduct'] == 0 && !empty($ds['pdFrom']) && !empty($ds['pdTo']))
    {
      $this->ms->where('OITM.ItemCode >=', $ds['pdFrom'])->where('OITM.ItemCode <=', $ds['pdTo']);
    }

    if(isset($ds['zoneCode']) && !empty($ds['zoneCode']))
    {
      $this->ms->where('OBIN.BinCode', $ds['zoneCode']);
    }
    else 
    {
      if(isset($ds['allWhouse']) && $ds['allWhouse'] == 0 && !empty($ds['warehouse']))
      {
        $this->ms->where_in('OBIN.WhsCode', $ds['warehouse']);
      }
    }

    $rs = $this->ms
    ->order_by('OBIN.WhsCode', 'ASC')
    ->order_by('OBIN.BinCode', 'ASC')
    ->order_by('OITM.ItemCode', 'ASC')
    ->limit($limit, $offset)
    ->get();

    return $rs->num_rows() > 0 ? $rs->result() : NULL;
  }  
}
