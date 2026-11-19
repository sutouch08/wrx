<?php
class Inventory_report_model extends CI_Model
{
  public function __construct()
  {
    parent::__construct();
  }

  public function count_rows(array $ds = array())
  {
    $this->ms->where('OnHand >', 0, FALSE);
    
   
    if($ds['allProduct'] == 0 && !empty($ds['pdFrom']) && !empty($ds['pdTo']))
    {
      $this->ms->where('ItemCode >=', $ds['pdFrom'])->where('ItemCode <=', $ds['pdTo']);      
    }

    if($ds['allWhouse'] == 0 && !empty($ds['warehouse']))
    {
      $this->ms->where_in('WhsCode', $ds['warehouse']);      
    }
    
    return $this->ms->count_all_results('OITW');
  }

  public function get_stock_balance(array $ds = array(), $limit = 10000, $offset = 0)
  {
    $this->ms
      ->select('W.WhsCode, W.OnHand, I.ItemCode, I.ItemName, P.Price AS Cost')
      ->from('OITW AS W')
      ->join('OITM AS I', 'W.ItemCode = I.ItemCode', 'left')
      ->join('ITM1 AS P', 'I.ItemCode = P.ItemCode AND P.PriceList = 13', 'left')
      ->where('W.OnHand >', 0, FALSE);

    if (isset($ds['allWhouse']) && $ds['allWhouse'] == 0 && !empty($ds['warehouse']))
    {
      $this->ms->where_in('W.WhsCode', $ds['warehouse']);
    }

    if (isset($ds['allProduct']) && $ds['allProduct'] == 0 && !empty($ds['pdFrom']) && !empty($ds['pdTo']))
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


  public function getStock($option, $limit = 100, $offset = 0)
  {
    $this->ms
    ->select('OITW.ItemCode')
    ->select_sum('OITW.OnHand')
    ->from('OITW')
    ->join('OITM', 'OITW.ItemCode = OITM.ItemCode', 'left')
    ->where('OITW.OnHand >', 0, FALSE);

    if($option->allProduct == 0 && ! empty($option->pdFrom) && ! empty($option->pdTo))
    {
      $this->ms->where('OITM.U_MODEL >=', $option->pdFrom)->where('OITM.U_MODEL <=', $option->pdTo);
    }

    if($option->allWhouse == 0 && ! empty($option->whsList))
    {
      $this->ms->where_in('OITW.WhsCode', $option->whsList);
    }

    $rs = $this->ms->group_by('OITW.ItemCode')->order_by('OITW.ItemCode', 'ASC')->limit($limit, $offset)->get();

    if($rs->num_rows() > 0)
    {
      return $rs->result();
    }

    return NULL;
  }


  public function get_current_stock_balance($allProduct, $pdFrom, $pdTo, $allWhouse, $warehouse)
  {
    $this->ms
    ->select('OITW.ItemCode AS product_code')
    ->select_sum('OITW.OnHand', 'qty')
    ->from('OITW')
    ->join('OITM', 'OITW.ItemCode = OITM.ItemCode', 'left')
    ->where('OITW.OnHand >', 0, FALSE);

    if($allProduct == 0 && !empty($pdFrom) && !empty($pdTo))
    {
      $this->ms->where('OITM.U_MODEL >=', $pdFrom)->where('OITM.U_MODEL <=', $pdTo);
    }

    if($allWhouse == 0 && !empty($warehouse))
    {
      $this->ms->where_in('OITW.WhsCode', $warehouse);
    }

    $this->ms->group_by('OITW.ItemCode');
    $this->ms->order_by('OITW.ItemCode', 'ASC');
    $rs = $this->ms->get();

    if($rs->num_rows() > 0)
    {
      return $rs->result();
    }

    return FALSE;
  }


  public function get_reserv_stock($itemCode, $whsCode)
  {
    $rs = $this->db
    ->select_sum('od.qty', 'qty')
    ->from('order_details AS od')
    ->join('orders AS o', 'od.order_code = o.code', 'left')
    ->where('od.product_code', $itemCode)
    ->where('od.is_complete', 0)
    ->where('od.is_expired', 0)
    ->where('od.is_cancle', 0)
    ->where('od.is_count', 1)
    ->where('o.warehouse_code', $whsCode)
    ->get();

    return $rs->row()->qty ?? 0;
  }
}
