<?php
class Channels_model extends CI_Model
{
  private $tb = "channels";

  public function __construct()
  {
    parent::__construct();
  }

  public function add(array $ds = array())
  {
    return ! empty($ds) ? $this->db->insert($this->tb, $ds) : FALSE;    
  }


  public function update($code, array $ds = array())
  {
    return ! empty($ds) ? $this->db->where('code', $code)->update($this->tb, $ds) : FALSE;    
  }


  public function delete($id)
  {
    return $this->db->where('id', $id)->delete($this->tb);
  }


  public function count_rows($ds = [])
  {
    if( ! empty($ds['code']))
    {
      $this->db->like('code', $ds['code']);
    }

    if( ! empty($ds['name']))
    {
      $this->db->like('name', $ds['name']);
    }

    return $this->db->count_all_results($this->tb);
  }


  public function get_list($ds = [], $perpage = 20, $offset = 0)
  {
    if( ! empty($ds['code']))
    {
      $this->db->like('code', $ds['code']);
    }

    if( ! empty($ds['name']))
    {
      $this->db->like('name', $ds['name']);
    }

    $rs = $this->db->limit($perpage, $offset)->get($this->tb);   
    return $rs->result();
  }


  public function get_channels($code)
  {
    $rs = $this->db->where('code', $code)->get($this->tb);
    return $rs->num_rows() === 1 ? $rs->row() : NULL;    
  }


  public function get($code)
  {
    $rs = $this->db->where('code', $code)->get($this->tb);
    return $rs->num_rows() === 1 ? $rs->row() : NULL;
  }


  public function get_by_id($id)
  {
    $rs = $this->db->where('id', $id)->get($this->tb);
    return $rs->num_rows() === 1 ? $rs->row() : NULL;
  }


  public function get_default()
  {
    $rs = $this->db->where('is_default', 1)->get($this->tb);
    return $rs->num_rows() === 1 ? $rs->row() : NULL;
  }


  public function get_online_list()
  {
    $rs = $this->db->where('is_online', 1)->get($this->tb);
    return $rs->num_rows() > 0 ? $rs->result() : NULL;
  }


  public function get_name($code = NULL)
  {
    if( ! empty($code))
    {
      $rs = $this->db->select('name')->where('code', $code)->get($this->tb);
      return $rs->num_rows() === 1 ? $rs->row()->name : "";
    }

    return "";
  }


  public function is_exists($code, $old_code = '')
  {
    if( ! empty($old_code)) $this->db->where('code !=', $old_code);
    return $this->db->where('code', $code)->count_all_results($this->tb) > 0;
  }


  public function is_exists_name($name, $old_code = '')
  {
    if( ! empty($old_code)) $this->db->where('code !=', $old_code);
    return $this->db->where('name', $name)->count_all_results($this->tb) > 0;
  }


	public function get_channels_array()
	{
    $arr = [];
		$rs = $this->db->get($this->tb);

		if($rs->num_rows() > 0)
		{			
			foreach($rs->result() as $ds)
			{
				$arr[$ds->code] = $ds->name;
			}			
		}

		return $arr;
	}


  public function get_all()
  {
    $rs = $this->db->order_by('position', 'ASC')->order_by('code', 'ASC')->get($this->tb);
    return $rs->num_rows() > 0 ? $rs->result() : NULL;
  }


  public function get_online_channels()
  {
    $rs = $this->db->where('is_online', 1)->get($this->tb);
    return $rs->num_rows() > 0 ? $rs->result() : NULL;
  }


  public function get_offline_channels()
  {
    $rs = $this->db->where('is_online', 0)->or_where('is_online', NULL)->get($this->tb);
    return $rs->num_rows() > 0 ? $rs->result() : NULL;
  }

  public function has_transection($code)
  {
    return $this->db->where('channels_code', $code)->count_all_results('orders') > 0;
  }
}
