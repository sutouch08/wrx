<?php
class Stock_balance_report_model extends CI_Model
{
  public function __construct()
  {
    parent::__construct();
  }

  public function count_rows($filter)
  {
    $this->ms->from('OIBQ')
    ->join('OITM', 'OIBQ.ItemCode = OITM.ItemCode', 'left')    
    ->join('OBIN', 'OIBQ.BinAbs = OBIN.AbsEntry','left')
    ->where('OIBQ.OnHandQty !=', 0, FALSE);

    if($filter['allProduct'] == 0 && !empty($filter['pdFrom']) && !empty($filter['pdTo']))
    {
      $this->ms->where('OITM.ItemCode >=', $filter['pdFrom'])->where('OITM.ItemCode <=', $filter['pdTo']);
    }

    if($filter['allZone'] == 1 && empty($filter['zoneCode']))
    {
      if($filter['allWhouse'] == 0 && !empty($filter['warehouse']))
      {
        $this->ms->where_in('OIBQ.WhsCode', $filter['warehouse']);
      }
    }

    if($filter['allZone'] == 0 && !empty($filter['zoneCode']))
    {
      $this->ms->where('OBIN.BinCode', $filter['zoneCode']);
    }

    return $this->ms->count_all_results();
  }

  public function get_stock_zone(array $ds = array(), $limit = 1000, $offset = 0)
  {
    $this->ms
    ->select('OITM.ItemCode AS product_code')
    ->select('OITM.ItemName AS product_name')
    ->select('ITM1.Price AS price')
    ->select('OBIN.WhsCode AS warehouse_code')
    ->select('OBIN.BinCode AS zone_code')
    ->select('OBIN.Descr AS zone_name')
    ->select('OIBQ.OnHandQty AS qty')
    ->from('OIBQ')
    ->join('OITM', 'OIBQ.ItemCode = OITM.ItemCode', 'left')
    ->join('ITM1', 'OITM.ItemCode = ITM1.ItemCode AND ITM1.PriceList = 11')
    ->join('OBIN', 'OIBQ.BinAbs = OBIN.AbsEntry','left')
    ->where('OIBQ.OnHandQty !=', 0, FALSE);

    if(!empty($ds))
    {
      if(isset($ds['allProduct']) && $ds['allProduct'] == 0 && !empty($ds['pdFrom']) && !empty($ds['pdTo']))
      {
        $this->ms->where('OITM.ItemCode >=', $ds['pdFrom'])->where('OITM.ItemCode <=', $ds['pdTo']);
      }

      if(isset($ds['allZone']) && $ds['allZone'] == 1 && empty($ds['zoneCode']))
      {
        if(isset($ds['allWhouse']) && $ds['allWhouse'] == 0 && !empty($ds['warehouse']))
        {
          $this->ms->where_in('OIBQ.WhsCode', $ds['warehouse']);
        }
      }

      if(isset($ds['allZone']) && $ds['allZone'] == 0 && !empty($ds['zoneCode']))
      {
        $this->ms->where('OBIN.BinCode', $ds['zoneCode']);
      }
    }

    $this->ms->order_by('OITM.ItemCode', 'ASC');
    $this->ms->order_by('OBIN.WhsCode', 'ASC');
    $this->ms->order_by('OBIN.BinCode', 'ASC');
    $rs = $this->ms->limit($limit, $offset)->get();
    return $rs->num_rows() > 0 ? $rs->result() : NULL;
  }


  public function count_stock_warehouse($ds = array())
  {
    $this->ms->where('OnHand >', 0);

    if(isset($ds['allWhouse']) && $ds['allWhouse'] == 0 && !empty($ds['warehouse']))
    {
      $this->ms->where_in('WhsCode', $ds['warehouse']);
    }

    if( isset($ds['allProduct']) && $ds['allProduct'] == 0 && ! empty($ds['pdFrom']) && !empty($ds['pdTo']))
    {
      $this->ms->where('ItemCode >=', $ds['pdFrom'])->where('ItemCode <=', $ds['pdTo']);
    }

    return $this->ms->count_all_results('OITW');
  }  

  public function get_stock_warehouse(array $ds = array(), $limit = 1000, $offset = 0)
  {
    $this->ms
    ->select('W.WhsCode, W.OnHand, I.ItemCode, I.ItemName, P.Price AS Cost')
    ->from('OITW AS W')
    ->join('OITM AS I', 'W.ItemCode = I.ItemCode', 'left')
    ->join('ITM1 AS P', 'I.ItemCode = P.ItemCode AND P.PriceList = 13', 'left')
    ->where('W.OnHand >', 0, FALSE);

    if(isset($ds['allWhouse']) && $ds['allWhouse'] == 0 && !empty($ds['warehouse']))
    {
      $this->ms->where_in('W.WhsCode', $ds['warehouse']);
    }

    if(isset($ds['allProduct']) && $ds['allProduct'] == 0 && !empty($ds['pdFrom']) && !empty($ds['pdTo']))
    {
      $this->ms->where('W.ItemCode >=', $ds['pdFrom']);
      $this->ms->where('W.ItemCode <=', $ds['pdTo']);
    }
        
    $rs = $this->ms
    ->order_by('W.WhsCode', 'ASC')
    ->order_by('W.ItemCode', 'ASC')
    ->limit($limit, $offset)
    ->get();
    
    return $rs->num_rows() > 0 ? $rs->result() : NULL;
  }

  //-- may be not use
  public function get_warehouse_stock($whsCode, $limit = 100, $offset = 0)
  {
    $rs = $this->ms
    ->select('W.WhsCode, W.OnHand, WH.WhsName, I.ItemCode, I.ItemName, P.Price AS Cost')
    ->from('OITW AS W')
    ->join('OWHS AS WH', 'W.WhsCode = WH.WhsCode', 'left')
    ->join('OITM AS I', 'W.ItemCode = I.ItemCode', 'left')
    ->join('ITM1 AS P', 'I.ItemCode = P.ItemCode AND P.PriceList = 13', 'left')
    ->where('W.WhsCode', $whsCode)
    ->where('W.OnHand >', 0, FALSE)
    ->order_by('W.ItemCode', 'ASC')
    ->limit($limit, $offset)
    ->get();

    return $rs->num_rows() > 0 ? $rs->result() : NULL;    
  }
}

