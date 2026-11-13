<?php
class Sender_model extends CI_Model
{
	private $tb = "address_sender";

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
		return ! empty($ds) ? $this->db->where('id', $id)->update($this->tb, $ds) : FALSE;
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


  public function get_by_code($code)
  {
    $rs = $this->db->where('code', $code)->get($this->tb);
		return $rs->num_rows() === 1 ? $rs->row() : NULL;
  }


	public function get_id($code)
	{
		$rs = $this->db->select('id')->where('code', $code)->get($this->tb);
		return $rs->num_rows() === 1 ? $rs->row()->id : NULL;
	}


  public function get_all()
  {
    $rs = $this->db->get($this->tb);
		return $rs->num_rows() > 0 ? $rs->result() : NULL;
  }


	public function get_common_list($list =  array())
	{
		$this->db->where('show_in_list', 1);

		if(!empty($list))
		{
			$this->db->where_not_in('id', $list);
		}

		$rs = $this->db->get($this->tb);
		return $rs->num_rows() > 0 ? $rs->result() : NULL;
	}



	public function get_customer_sender_list($customer_code)
	{
		$rs = $this->db->where('customer_code', $customer_code)->get('address_transport');
		return $rs->num_rows() === 1 ? $rs->row() : NULL;
	}



	public function get_sender_in($arr)
	{
		$rs = $this->db->where_in('id', $arr)->get($this->tb);
		return $rs->num_rows() > 0 ? $rs->result() : NULL;
	}

  public function get_sender($id)
  {
    $rs = $this->db->where('id', $id)->get($this->tb);
		return $rs->num_rows() === 1 ? $rs->row() : NULL;
  }


  public function get_name($id)
  {
    $rs = $this->db->where('id', $id)->get($this->tb);
		return $rs->num_rows() === 1 ? $rs->row()->name : "";
  }


	public function is_exists_code($code, $id = NULL)
	{
		if(!empty($id)) $this->db->where('id !=', $id);
		return $this->db->where('code', $code)->count_all_results($this->tb) > 0;
	}


	public function is_exists_name($name, $id = NULL)
	{
		if( ! empty($id)) $this->db->where('id !=', $id);
		return $this->db->where('name', $name)->count_all_results($this->tb) > 0;
	}

  public function is_exists($name, $id = NULL)
  {
		if( ! empty($id)) $this->db->where('id !=', $id);
		return $this->db->where('name', $name)->count_all_results($this->tb) > 0;
  }

  public function count_rows(array $ds = array())
  {
		if(!empty($ds['code']))
		{
			$this->db->like('code', $ds['code']);
		}

		if(!empty($ds['name']))
		{
			$this->db->like('name', $ds['name']);
		}

		if(!empty($ds['addr']))
		{
			$this->db->group_start();
			$this->db->like('address1', $ds['addr'])->or_like('address2', $ds['addr']);
			$this->db->group_end();
		}

		if(isset($ds['phone']) && $ds['phone'] != '')
		{
			$this->db->like('phone', $ds['phone']);
		}

		if(!empty($ds['type']) && $ds['type'] !== 'all')
		{
			$this->db->where('type', $ds['type']);
		}

    if(isset($ds['listing']) && $ds['listing'] != 'all')
    {
      $this->db->where('show_in_list', $ds['listing']);
    }

		return $this->db->count_all_results($this->tb);

  }


  public function get_list(array $ds = array(), $perpage = 20, $offset = 0)
  {
		if (!empty($ds['code']))
		{
			$this->db->like('code', $ds['code']);
		}

		if (!empty($ds['name']))
		{
			$this->db->like('name', $ds['name']);
		}

		if (!empty($ds['addr']))
		{
			$this->db->group_start();
			$this->db->like('address1', $ds['addr'])->or_like('address2', $ds['addr']);
			$this->db->group_end();
		}

		if (isset($ds['phone']) && $ds['phone'] != '')
		{
			$this->db->like('phone', $ds['phone']);
		}

		if (!empty($ds['type']) && $ds['type'] !== 'all')
		{
			$this->db->where('type', $ds['type']);
		}

		if (isset($ds['listing']) && $ds['listing'] != 'all')
		{
			$this->db->where('show_in_list', $ds['listing']);
		}

		$this->db->order_by('code', 'DESC')->limit($perpage, $offset);
		$rs = $this->db->get($this->tb);
		return $rs->num_rows() > 0 ? $rs->result() : NULL;
  }

  public function search($txt)
  {
		$rs = $this->db->select('id')->like('name', $txt)->get($this->tb);
		return $rs->num_rows() > 0 ? $rs->result() : [];
  }


	public function get_main_sender($customer_code)
	{
		$rs = $this->db->select('main_sender')->where('customer_code', $customer_code)->get($this->tb);
		return $rs->num_rows() === 1 ? $rs->row()->main_sender : NULL;
	}
}
