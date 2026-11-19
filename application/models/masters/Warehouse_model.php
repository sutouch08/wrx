<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Warehouse_model extends CI_Model
{  
  private $tb = "warehouse";
  private $tr = "warehouse_role";

  public function __construct()
  {
    parent::__construct();    
  }

  public function get($code)
  {
    $rs = $this->db
    ->select('warehouse.*, warehouse_role.name AS role_name')
    ->from($this->tb)
    ->join('warehouse_role', 'warehouse.role = warehouse_role.id', 'left')
    ->where('warehouse.code', $code)
    ->get();

    return $rs->num_rows() === 1 ? $rs->row() : NULL;
  }


  public function get_name($code)
  {
    $rs = $this->db->where('code', $code)->get($this->tb);
    return $rs->num_rows() === 1 ? $rs->row()->name : "";
  }


  public function add(array $ds = array())
  {
    return !empty($ds) ? $this->db->insert($this->tb, $ds) : FALSE;
  }


  public function update($code, array $ds = array())
  {
    return !empty($ds) ? $this->db->where('code', $code)->update($this->tb, $ds) : FALSE;    
  }


  public function delete($code)
  {
    return $this->db->where('code', $code)->delete($this->tb);
  }


  public function get_all_role()
  {
    $rs = $this->db->get($this->tr);
    return $rs->num_rows() > 0 ? $rs->result() : NULL;
  }


  public function count_rows(array $ds = array())
  {
    if( ! empty($ds['code']))
    {
      $this->db->like('code', $ds['code'])->or_like('name', $ds['code']);
    }

    if( ! empty($ds['role']) && $ds['role'] != 'all')
    {
      $this->db->where('role', $ds['role']);
    }

    if( isset($ds['active']) && $ds['active'] != 'all')
    {
      $this->db->where('active', $ds['active']);
    }

    if( isset($ds['sell']) && $ds['sell'] != 'all')
    {
      $this->db->where('sell', $ds['sell']);
    }

    if( isset($ds['prepare']) && $ds['prepare'] != 'all')
    {
      $this->db->where('prepare', $ds['prepare']);
    }

    if( isset($ds['lend']) && $ds['lend'] != 'all')
    {
      $this->db->where('lend', $ds['lend']);
    }

    if( isset($ds['auz']) && $ds['auz'] != 'all')
    {
      $this->db->where('auz', $ds['auz']);
    }

    if( isset($ds['is_pos']) && $ds['is_pos'] != 'all')
    {
      $this->db->where('warehouse.is_pos', $ds['is_pos']);
    }

    if(isset($ds['is_consignment']) && $ds['is_consignment'] != 'all')
    {
      if($ds['is_consignment'] == 1)
      {
        $this->db->where('is_consignment', $ds['is_consignment']);
      }
      else
      {
        $this->db
        ->group_start()
        ->where('is_consignment', 0)
        ->or_where('is_consignment IS NULL', NULL, FALSE)
        ->group_end();
      }
    }

    return $this->db->count_all_results($this->tb);
  }


  public function get_list(array $ds = array(), $perpage = 20, $offset = 0)
  {
    $this->db->select('warehouse.*, warehouse_role.name AS role_name');
    $this->db->from('warehouse')->join('warehouse_role', 'warehouse.role = warehouse_role.id');

    if(!empty($ds['code']))
    {
      $this->db->like('warehouse.code', $ds['code'])->or_like('warehouse.name', $ds['code']);
    }

    if(! empty($ds['role']) && $ds['role'] != 'all')
    {
      $this->db->where('warehouse.role', $ds['role']);
    }

    if( isset($ds['active']) && $ds['active'] != 'all')
    {
      $this->db->where('warehouse.active', $ds['active']);
    }

    if( isset($ds['sell']) && $ds['sell'] != 'all')
    {
      $this->db->where('warehouse.sell', $ds['sell']);
    }

    if( isset($ds['prepare']) && $ds['prepare'] != 'all')
    {
      $this->db->where('warehouse.prepare', $ds['prepare']);
    }

    if( isset($ds['lend']) && $ds['lend'] != 'all')
    {
      $this->db->where('warehouse.lend', $ds['lend']);
    }

    if( isset($ds['auz']) && $ds['auz'] != 'all')
    {
      $this->db->where('warehouse.auz', $ds['auz']);
    }

    if( isset($ds['is_pos']) && $ds['is_pos'] != 'all')
    {
      $this->db->where('warehouse.is_pos', $ds['is_pos']);
    }

    if(isset($ds['is_consignment']) && $ds['is_consignment'] != 'all')
    {
      if($ds['is_consignment'] == 1)
      {
        $this->db->where('warehouse.is_consignment', $ds['is_consignment']);
      }
      else
      {
        $this->db
        ->group_start()
        ->where('warehouse.is_consignment', 0)
        ->or_where('warehouse.is_consignment IS NULL', NULL, FALSE)
        ->group_end();
      }
    }
    
    $rs = $this->db->limit($perpage, $offset)->get();
    return $rs->num_rows() > 0 ? $rs->result() : NULL;
  }


  public function get_sell_warehouse_list($order_by = 'position', $direction = 'ASC')
  {
    $rs = $this->db
    ->where_in('role', [1, 2, 3])
    ->where('active', 1)
    ->where('sell', 1)
    ->order_by($order_by, $direction)
    ->get($this->tb);

    return $rs->num_rows() > 0 ? $rs->result() : NULL;
  }

  //--- เอาเฉพาะคลังที่สามารถยืมสินค้าได้
  public function get_lend_warehouse_list()
  {
    $rs = $this->db
    ->where('active', 1)
    ->where('lend', 1)
    ->order_by('position', 'ASC')
    ->get($this->tb);

    return $rs->num_rows() > 0 ? $rs->result() : NULL;
  }


  public function get_all_warehouse_list()
  {
    $rs = $this->db
    ->where('active', 1)
    ->order_by('code', 'ASC')
    ->get($this->tb);

    return $rs->num_rows() > 0 ? $rs->result() : NULL;
  }


  ///---- คลังฝากขายทั้งหมด
  public function get_consign_warehouse_list()
  {
    $rs = $this->db
    ->where('role', 2)
    ->where('active', 1)
    ->get($this->tb);
    return $rs->num_rows() > 0 ? $rs->result() : NULL;
  }


  public function get_transform_warehouse_list()
  {
    $rs = $this->db
    ->where('role', 7)
    ->where('active', 1)
    ->order_by('position', 'ASC')
    ->get($this->tb );

    return $rs->num_rows() > 0 ? $rs->result() : NULL;
  }


  //---- เอาเฉพาะคลังฝากขายแท้
  public function get_consign_list()
  {
    $rs = $this->db
    ->where('role', 2)
    ->group_start()
    ->where('is_consignment IS NULL', NULL, FALSE)
    ->or_where('is_consignment', 0)
    ->group_end()
    ->where('active', 1)
    ->order_by('code', 'ASC')
    ->get($this->tb);

    return $rs->num_rows() > 0 ? $rs->result() : NULL;
  }


  //---- เอาเฉพาะคลังฝากขายเทียม
  public function get_consignment_list()
  {
    $rs = $this->db
    ->where('role', 2)
    ->where('is_consignment', 1)
    ->where('active', 1)
    ->order_by('code', 'ASC')
    ->get($this->tb);

    return $rs->num_rows() > 0 ? $rs->result() : NULL;
  }


	public function get_common_list()
	{
		$rs = $this->db
		->where_in('role', array(1, 3, 4, 5))
		->where('active', 1)
		->order_by('code', 'ASC')
		->get($this->tb);

		return $rs->num_rows() > 0 ? $rs->result() : NULL;
	}


  public function get_lend_list()
	{
		$rs = $this->db
		->where('role', 8)
		->where('active', 1)
    ->order_by('code', 'ASC')
		->get($this->tb);

		return $rs->num_rows() > 0 ? $rs->result() : NULL;
	}


  public function count_zone($code)
  {
    return $this->db->where('warehouse_code', $code)->count_all_results('zone');
  }


  public function get_role_name($id)
  {
    $rs = $this->db->select('name')->where('id', $id)->get($this->tr);
    return $rs->num_rows() === 1 ? $rs->row()->name : "";
  }


  public function get_last_sync_date()
  {
    $rs = $this->db->select_max('last_sync')->get($this->tb);
    return $rs->num_rows() === 1 ? ($rs->row()->last_sync === NULL ? date('2019-01-01 00:00:00') : $rs->row()->last_sync) : date('2019-01-01 00:00:00');
  }


  public function get_new_data($last_sync)
  {
    $rs = $this->ms
    ->select('WhsCode AS code, WhsName AS name, Inactive, U_OLDWHSCODE AS old_code, U_LIMIT_AMOUNT AS limit_amount')
    ->where('createDate >=', sap_date($last_sync))
    ->or_where('updateDate >=', sap_date($last_sync))
    ->get('OWHS');

    return $rs->num_rows() > 0 ? $rs->result() : NULL;
  }


  public function get_all_warehouse()
  {    
    $rs = $this->ms
    ->select('WhsCode AS code, WhsName AS name')
    ->select('createDate, updateDate')
    ->get('OWHS');    
    return $rs->num_rows() > 0 ? $rs->result() : NULL;
  }


  public function has_zone($code)
  {    
    return $this->db->where('warehouse_code', $code)->count_all_results('zone') > 0;
  }


  public function is_exists($code)
  {
    return $this->db->where('code', $code)->count_all_results($this->tb) > 0;
  }


  public function is_sap_exists($code)
  {
    return $this->ms->where('WhsCode', $code)->count_all_results('OWHS') > 0;
  }


  public function is_auz($code)
  {
    return $this->db->where('code', $code)->where('auz', 1)->count_all_results($this->tb) > 0;
  }


	public function is_consignment($code)
	{
		return $this->db->where('code', $code)->where('is_consignment', 1)->count_all_results($this->tb) > 0;
	}


  public function is_stock_exists($role, $whsCode)
  {
    if ($role === 'C')
    {
      $this->cn = $this->load->database('cn', TRUE);
      return $this->cn->where('WhsCode', $whsCode)->where('OnHand >', 0)->count_all_results('OITW') > 0;
    }

    if($role === 'N')
    {
      return $this->ms->where('WhsCode', $whsCode)->where('OnHand >', 0)->count_all_results('OITW') > 0;
    }      
  }


  public function get_balance_amount($role, $whsCode)
  {
    if($role === 'C')
    {
      $this->cn = $this->load->database('cn', TRUE);
    }
    
    $qr  = "SELECT SUM(Amount) AS Amount FROM ";
    $qr .= "(SELECT (O.OnHand * P.Price) AS Amount FROM OITW AS O ";
    $qr .= "LEFT JOIN ITM1 AS P ON O.ItemCode = P.ItemCode ";
    $qr .= "WHERE O.WhsCode = '".$whsCode."' AND O.OnHand > 0 AND P.PriceList = 13) AS Amount";

    if($role === 'N')
    {
      $rs = $this->ms->query($qr);
    }

    if($role === 'C')
    {     
      $rs = $this->cn->query($qr);
    }

    if($rs->num_rows() === 1)
    {
      return $rs->row()->Amount > 0 ? round($rs->row()->Amount, 2) : 0.00;
    }

    return 0.00;
  }


  public function get_limit_amount($whsCode)
  {
    $rs = $this->db->select('limit_amount')->where('code', $whsCode)->get($this->tb);
    return $rs->num_rows() === 1 ? $rs->row()->limit_amount : 0.00;
  }
}
