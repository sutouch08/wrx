<?php
class Customers_model extends CI_Model
{
  private $tb = "customers";

  public function __construct()
  {
    parent::__construct();
  }

  public function add_sap_customer(array $ds = array())
  {
    return ! empty($ds) ? $this->mc->insert('OCRD', $ds) : FALSE;    
  }

  public function update_sap_customer($code, $ds = array())
  {
    return ! empty($ds) ? $this->mc->where('CardCode', $code)->update('OCRD', $ds) : FALSE;    
  }

  public function sap_customer_exists($code)
  {
    return $this->mc->where('CardCode', $code)->count_all_results('OCRD') > 0;    
  }

  public function get_credit($code)
  {
    $rs = $this->ms
    ->select('CreditLine, Balance, DNotesBal, OrdersBal')
    ->where('CardCode', $code)
    ->get('OCRD');

    return $rs->num_rows() === 1 ? (floatval($rs->row()->CreditLine) - (floatval($rs->row()->Balance) + floatval($rs->row()->DNotesBal) + floatval($rs->row()->OrdersBal))) : 0.00;    
  }

  public function add(array $ds = array())
  {
    return ! empty($ds) ? $this->db->insert($this->tb, $ds) : FALSE;
  }

  public function update($code, array $ds = array())
  {
    return ! empty($ds) ? $this->db->where('code', $code)->update($this->tb, $ds) : FALSE;    
  }

  public function delete($code)
  {
    return $this->db->where('code', $code)->delete($this->tb);    
  }

  public function get($code)
  {
    $rs = $this->db->where('code', $code)->get($this->tb);
    return $rs->num_rows() === 1 ? $rs->row() : NULL;
  }

  public function get_name($code)
  {
    $rs = $this->db->select('name')->where('code', $code)->get($this->tb);
    return $rs->num_rows() === 1 ? $rs->row()->name : '';
  }

  public function count_rows(array $ds = array())
  {
    if($ds['code'] != "" && $ds['code'] !== NULL)
    {
      $this->db
      ->group_start()
      ->like('code', $ds['code'])
      ->or_like('name', $ds['code'])
      ->group_end();
    }

    if($ds['group'] != 'all')
    {
      if($ds['group'] === "NULL")
      {
        $this->db->where('group_code IS NULL', NULL, FALSE);
      }
      else
      {
        $this->db->where('group_code', $ds['group']);
      }
    }

    if($ds['kind'] != "all")
    {
      if($ds['kind'] === "NULL")
      {
        $this->db->where('kind_code IS NULL', NULL, FALSE);
      }
      else
      {
        $this->db->where('kind_code', $ds['kind']);
      }
    }

    if($ds['type'] != "all")
    {
      if($ds['type'] === "NULL")
      {
        $this->db->where('type_code IS NULL', NULL, FALSE);
      }
      else
      {
        $this->db->where('type_code', $ds['type']);
      }
    }

    if($ds['class'] != "all")
    {
      if($ds['class'] === 'NULL')
      {
        $this->db->where('class_code IS NULL', NULL, FALSE);
      }
      else
      {
        $this->db->where('class_code', $ds['class']);
      }
    }

    if($ds['area'] != 'all')
    {
      if($ds['area'] === "NULL")
      {
        $this->db->where('area_code IS NULL', NULL, FALSE);
      }
      else
      {
        $this->db->where('area_code', $ds['area']);
      }
    }

    if($ds['status'] != 'all')
    {
      $this->db->where('active', $ds['status']);
    }

    return $this->db->count_all_results($this->tb);
  }


  public function get_list(array $ds = array(), $perpage = 20, $offset = 0)
  {
    $this->db
    ->select('cu.*, cg.name AS group, ck.name AS kind, ct.name AS type, cc.name AS class, ca.name AS area')
    ->from('customers AS cu')
    ->join('customer_group AS cg', 'cu.group_code = cg.code', 'left')
    ->join('customer_kind AS ck', 'cu.kind_code = ck.code', 'left')
    ->join('customer_type AS ct', 'cu.type_code = ct.code', 'left')
    ->join('customer_class AS cc', 'cu.class_code = cc.code', 'left')
    ->join('customer_area AS ca', 'cu.area_code = ca.code', 'left');

    if($ds['code'] != "" && $ds['code'] !== NULL)
    {
      $this->db
      ->group_start()
      ->like('cu.code', $ds['code'])
      ->or_like('cu.name', $ds['code'])
      ->group_end();
    }

    if($ds['group'] != 'all')
    {
      if($ds['group'] === "NULL")
      {
        $this->db->where('cu.group_code IS NULL', NULL, FALSE);
      }
      else
      {
        $this->db->where('cu.group_code', $ds['group']);
      }
    }

    if($ds['kind'] != "all")
    {
      if($ds['kind'] === "NULL")
      {
        $this->db->where('cu.kind_code IS NULL', NULL, FALSE);
      }
      else
      {
        $this->db->where('cu.kind_code', $ds['kind']);
      }
    }

    if($ds['type'] != "all")
    {
      if($ds['type'] === "NULL")
      {
        $this->db->where('cu.type_code IS NULL', NULL, FALSE);
      }
      else
      {
        $this->db->where('cu.type_code', $ds['type']);
      }
    }

    if($ds['class'] != "all")
    {
      if($ds['class'] === 'NULL')
      {
        $this->db->where('cu.class_code IS NULL', NULL, FALSE);
      }
      else
      {
        $this->db->where('cu.class_code', $ds['class']);
      }
    }

    if($ds['area'] != 'all')
    {
      if($ds['area'] === "NULL")
      {
        $this->db->where('cu.area_code IS NULL', NULL, FALSE);
      }
      else
      {
        $this->db->where('cu.area_code', $ds['area']);
      }
    }

    if($ds['status'] != 'all')
    {
      $this->db->where('cu.active', $ds['status']);
    }

    $rs = $this->db->order_by('cu.code', 'ASC')->limit($perpage, $offset)->get();
    return $rs->num_rows() > 0 ? $rs->result() : NULL;
  }

  public function is_exists($code, $old_code = '')
  {
    if($old_code != '') $this->db->where('code !=', $old_code);
    return $this->db->where('code', $code)->count_all_results($this->tb) > 0;
  }

  public function is_exists_name($name, $code = '')
  {
    if(!empty($code)) $this->db->where('code !=', $code);
    return $this->db->where('name', $name)->count_all_results($this->tb) > 0;
  }

  public function get_sale_code($code)
  {
    $rs = $this->db->select('sale_code')->where('code', $code)->get($this->tb);
    return $rs->num_rows() === 1 ? $rs->row()->sale_code : '';
  }

  public function get_update_data($last_sync)
  {
    $rs = $this->ms
    ->select("CardCode AS code")
    ->select("CardName AS name")
    ->select("LicTradNum AS Tax_Id")
    ->select("DebPayAcct, CardType")
    ->select("GroupCode, CmpPrivate")
    ->select("GroupNum, SlpCode AS sale_code")
    ->select("validFor")
    ->select("CreditLine")
    ->select("U_WRX_BPOLDCODE AS old_code")
    ->where('CardType', 'C')
    ->group_start()
    ->where("UpdateDate >=", sap_date($last_sync))
    ->or_where('CreateDate >=', sap_date($last_sync))
    ->group_end()
    ->get('OCRD');

    return $rs->num_rows() > 0 ? $rs->result() : NULL;
  }

  public function search($txt)
  {
    $rs = $this->select('code')->like('code', $txt)->or_like('name', $txt)->get($this->tb);
    return $rs->num_rows() > 0 ? $rs->result() : [];
  }

  public function getGroupCode()
  {
    $rs = $this->ms
    ->select('GroupCode AS code, GroupName AS name')
    ->where('GroupType', 'C')
    ->get('OCRG');

    return $rs->num_rows() > 0 ? $rs->result() : NULL;
  }

  public function getGroupNum()
  {
    $rs = $this->ms
    ->select('GroupNum AS code, PymntGroup AS name')
    ->order_by('GroupNum', 'ASC')
    ->get('OCTG');

    return $rs->num_rows() > 0 ? $rs->result() : NULL;
  }

  public function getDebPayAcct()
  {
    $rs = $this->ms
    ->select('AcctCode AS code, AcctName AS name')
    ->order_by('AcctCode', 'ASC')
    ->get('OACT');

    return $rs->num_rows() > 0 ? $rs->result() : NULL;      
  }

  public function getSlp()
  {
    $rs = $this->ms
    ->select('SlpCode AS code, SlpName AS name')
    ->where('Active', 'Y')
    ->get('OSLP');

    return $rs->num_rows() > 0 ? $rs->result() : NULL;
  }

  public function get_last_sync_date()
  {
    $rs = $this->db->select_max('last_sync')->get('customers');

    if($rs->num_rows() === 1)
    {
      return $rs->row()->last_sync === NULL ? date('2019-01-01 00:00:00') : from_date($rs->row()->last_sync);
    }

    return date('2019-01-01 00:00:00');
  }

  public function has_transection($code)
  {
    return $this->db->where('customer_code', $code)->count_all_results('orders') > 0;
  }
}
