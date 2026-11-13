<?php
class Bank_model extends CI_Model
{
  private $tb = "bank_account";
  private $td = "bank";

  public function __construct()
  {
    parent::__construct();
  }

	public function add(array $ds = array())
	{
    return ! empty($ds) ? $this->db->insert($this->tb, $ds) : FALSE;
	}

	public function update($id, array $ds = array())
	{
    return !empty($ds) && !empty($id) ? $this->db->where('id', $id)->update($this->tb, $ds) : FALSE;
	}

	public function delete($id)
	{
		return $this->db->where('id', $id)->delete($this->tb);
	}

	public function get($id)
	{
		$rs = $this->db->where('id', $id)->get($this->tb);
    return $rs->num_rows() === 1 ? $rs->row() : NULL;
	}

	public function get_id($acc_no)
	{
		$rs = $this->db->select('id')->where('acc_no', $acc_no)->get($this->tb);
    return $rs->num_rows() === 1 ? $rs->row()->id : NULL;
	}

  public function get_active_bank()
  {
    $rs = $this->db->where('active', 1)->get($this->tb);
    return $rs->num_rows() > 0 ? $rs->result() : array();
  }

	public function is_exists($acc_no, $id = NULL)
	{
    if(! empty($id)) $this->db->where('id !=', $id);
    return $this->db->where('acc_no', $acc_no)->count_all_results($this->tb) > 0;
	}

  public function get_data()
  {
    $rs = $this->db->get($this->tb);
    return $rs->num_rows() > 0 ? $rs->result() : array();
  }

  public function count_rows(array $ds = array())
  {
    if(!empty($ds['bank_code']) && $ds['bank_code'] !== 'all')
    {
      $this->db->where('bank_code', $ds['bank_code']);
    }

    if(!empty($ds['account_name']))
    {
      $this->db->like('acc_name', $ds['account_name']);
    }

    if(!empty($ds['account_no']))
    {
      $this->db->like('acc_no', $ds['account_no']);
    }

    if(!empty($ds['branch']))
    {
      $this->db->like('branch', $ds['branch']);
    }

    return $this->db->count_all_results($this->tb);
  }

  public function get_list(array $ds = array(), $perpage = 20, $offset = 0)
  {
    if(!empty($ds['bank_code']) && $ds['bank_code'] !== 'all')
    {
      $this->db->where('bank_code', $ds['bank_code']);
    }

    if(!empty($ds['account_name']))
    {
      $this->db->like('acc_name', $ds['account_name']);
    }

    if(!empty($ds['account_no']))
    {
      $this->db->like('acc_no', $ds['account_no']);
    }

    if(!empty($ds['branch']))
    {
      $this->db->like('branch', $ds['branch']);
    }

    $rs = $this->db->order_by('id', 'ASC')->limit($perpage, $offset)->get($this->tb);
    return $rs->num_rows() > 0 ? $rs->result() : array();
  }

  public function get_account_detail($id)
  {
    $rs = $this->db->where('id', $id)->get($this->tb);
    return $rs->num_rows() === 1 ? $rs->row() : NULL;
  }

  public function get_banks($code = NULL)
  {
    if(!empty($code)) $this->db->where('code', $code);
    $rs = $this->db->get($this->td);
    return $rs->num_rows() > 0 ? $rs->result() : NULL;
  }

	public function get_bank($code)
	{
		$rs = $this->db->where('code', $code)->get($this->td);
    return $rs->num_rows() === 1 ? $rs->row() : NULL;
	}
}