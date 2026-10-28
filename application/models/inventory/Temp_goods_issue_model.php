<?php
class Temp_goods_issue_model extends CI_Model
{
  public function __construct()
  {
    parent::__construct();
  }


  public function count_rows(array $ds = array())
  {
    if (!empty($ds['code']))
    {
      $this->mc->like('U_ECOMNO', $ds['code']);
    }

    if (!empty($ds['from_date']) && !empty($ds['to_date']))
    {
      $this->mc->where('DocDate >=', from_date($ds['from_date']));
      $this->mc->where('DocDate <=', to_date($ds['to_date']));
    }

    if ($ds['status'] != 'all')
    {
      if ($ds['status'] === 'Y')
      {
        $this->mc->where('F_Sap', 'Y');
      }
      else if ($ds['status'] === 'N')
      {
        $this->mc->where('F_Sap IS NULL', NULL, FALSE);
      }
      else if ($ds['status'] === 'E')
      {
        $this->mc->where('F_Sap', 'N');
      }
    }

    return $this->mc->count_all_results('OIGE');
  }

  public function get_list(array $ds = array(), $perpage = 20, $offset = 0)
  {
    $this->mc
      ->select('DocEntry,U_ECOMNO, DocDate, CardCode, CardName')
      ->select('F_E_Commerce, F_E_CommerceDate')
      ->select('F_Sap, F_SapDate')
      ->select('F_Error AS Message');

    if (!empty($ds['code']))
    {
      $this->mc->like('U_ECOMNO', $ds['code']);
    }

    if (!empty($ds['from_date']) && !empty($ds['to_date']))
    {
      $this->mc->where('DocDate >=', from_date($ds['from_date']));
      $this->mc->where('DocDate <=', to_date($ds['to_date']));
    }

    if ($ds['status'] != 'all')
    {
      if ($ds['status'] === 'Y')
      {
        $this->mc->where('F_Sap', 'Y');
      }
      else if ($ds['status'] === 'N')
      {
        $this->mc->where('F_Sap IS NULL', NULL, FALSE);
      }
      else if ($ds['status'] === 'E')
      {
        $this->mc->where('F_Sap', 'N');
      }
    }

    $rs = $this->mc
      ->order_by('DocDate', 'DESC')
      ->order_by('U_ECOMNO', 'DESC')
      ->limit($perpage, $offset)
      ->get('OIGE');

    return $rs->num_rows() > 0 ? $rs->result() : NULL;
  }

  public function get($docEntry)
  {
    $rs = $this->mc->where('DocEntry', $docEntry)->get('OIGE');
    return $rs->num_rows() === 1 ? $rs->row() : NULL;
  }

  public function get_detail($docEntry)
  {
    $rs = $this->mc
      ->select('U_ECOMNO, ItemCode, Dscription, Quantity, FisrtBin AS BinCode')
      ->where('DocEntry', $docEntry)
      ->get('IGE1');

    return $rs->num_rows() > 0 ? $rs->result() : NULL;
  }

  public function get_error_list()
  {
    $rs = $this->mc
      ->select('U_ECOMNO AS code')
      ->where('F_Sap', 'N')
      ->order_by('U_ECOMNO', 'ASC')
      ->get('OIGE');

    return $rs->num_rows() > 0 ? $rs->result() : NULL;
  }

  public function delete_temp_details($id)
  {
    return $this->mc->where('DocEntry', $id)->delete('IGE1');
  }

  public function delete_temp($id)
  {
    return $this->mc->where('DocEntry', $id)->delete('OIGE');
  }
}
