<?php
class Order_channels_items_model extends CI_Model
{
  public function __construct()
  {
    parent::__construct();
  }

  public function count_rows(array $ds = array())
  {
    $this->db
    ->from('order_details AS od')
    ->join('orders AS o', 'od.order_code = o.code', 'left')
    ->where('o.role', 'S')
    ->where('od.date_add >=', from_date($ds['fromDate']))
    ->where('od.date_add <=', to_date($ds['toDate']))
    ->where('o.is_expired', 0);

    if (empty($ds['allChannels']) && !empty($ds['channels']))
    {
      $this->db->where_in('o.channels_code', $ds['channels']);
    }

    if (empty($ds['allPayments']) && !empty($ds['payments']))
    {
      $this->db->where_in('o.payment_code', $ds['payments']);
    }

    if (empty($ds['allWarehouse']) && !empty($ds['warehouse']))
    {
      $this->db->where_in('o.warehouse_code', $ds['warehouse']);
    }

    if (!empty($ds['state']))
    {
      $this->db->where_in('o.state', $ds['state']);
    }

    if (!empty($ds['pdFrom']) && !empty($ds['pdTo']))
    {
      $this->db
        ->where('od.product_code >=', $ds['pdFrom'])
        ->where('od.product_code <=', $ds['pdTo']);
    }

    return $this->db->count_all_results();
  }

  public function get_data(array $ds = array(), $limit = 10000, $offset = 0)
  {
    $this->db
      ->select('o.code, o.date_add, o.customer_code, o.customer_name, o.reference, o.shipping_code')
      ->select('o.id_address, o.customer_ref, o.channels_code, o.payment_code')
      ->select('o.shipping_fee, o.service_fee, o.state')
      ->select('od.product_code, od.price')
      ->select('od.qty, od.discount_amount, od.total_amount')
      ->from('order_details AS od')
      ->join('orders AS o', 'od.order_code = o.code', 'left')
      ->where('o.date_add >=', from_date($ds['fromDate']))
      ->where('o.date_add <=', to_date($ds['toDate']))
      ->where('o.role', 'S')
      ->where('o.is_expired', 0);

    if (empty($ds['allChannels']) && !empty($ds['channels']))
    {
      $this->db->where_in('o.channels_code', $ds['channels']);
    }

    if (empty($ds['allPayments']) && !empty($ds['payments']))
    {
      $this->db->where_in('o.payment_code', $ds['payments']);
    }

    if (empty($ds['allWarehouse']) && !empty($ds['warehouse']))
    {
      $this->db->where_in('o.warehouse_code', $ds['warehouse']);
    }

    if (!empty($ds['state']))
    {
      $this->db->where_in('o.state', $ds['state']);
    }

    if (!empty($ds['pdFrom']) && !empty($ds['pdTo']))
    {
      $this->db
        ->where('od.product_code >=', $ds['pdFrom'])
        ->where('od.product_code <=', $ds['pdTo']);
    }

    $this->db->order_by('o.code', 'ASC')->order_by('od.product_code', 'ASC');
    $rs = $this->db->limit($limit, $offset)->get();
    return $rs->num_rows() > 0 ? $rs->result() : NULL;
  }


  public function channels_array()
  {
    $ds = array();
    $rs = $this->db->select('code, name')->get('channels');

    if($rs->num_rows() > 0)
    {
      foreach($rs->result() as $rd)
      {
        $ds[$rd->code] = $rd->name;
      }
    }

    return $ds;
  }


  public function payment_array()
  {
    $ds = array();
    $rs = $this->db->select('code, name')->get('payment_method');

    if($rs->num_rows() > 0)
    {
      foreach($rs->result() as $rd)
      {
        $ds[$rd->code] = $rd->name;
      }
    }

    return $ds;
  }

  public function cancel_reason($order_code)
  {
    $rs = $this->db
    ->select('reason')
    ->where('code', $order_code)
    ->order_by('cancle_date', 'DESC')
    ->limit(1, 0)
    ->get('order_cancle_reason');

    if($rs->num_rows() > 0)
    {
      return $rs->row()->reason;
    }

    return NULL;
  }

} //--- end class

?>
