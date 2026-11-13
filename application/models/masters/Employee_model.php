<?php
class Employee_model extends CI_Model
{
  public function __construct()
  {
    parent::__construct();
  }

  public function get($id)
  {
    $rs = $this->ms->select('empID, lastName, firstName, Active')->where('empID', $id)->get('OHEM');
    return $rs->num_rows() === 1 ? $rs->row() : NULL;
  }

  public function get_all($active = 0)
  {
    if(!empty($active)) $this->ms->where('Active', 'Y');
    $rs = $this->ms->select('empID, lastName, firstName, Active')->get('OHEM');
    return $rs->num_rows() > 0 ? $rs->result() : NULL;
  }

  public function get_name($id)
  {
    $rs = $this->ms->select('firstName, lastName')->where('empID', $id)->get('OHEM');
    return $rs->num_rows() === 1 ? $rs->row()->firstName.' '.$rs->row()->lastName : '';
  }

  public function search($txt)
  {
    $qr  = "SELECT empID, firstName, lastName FROM OHEM ";
    $qr .= "WHERE firstName LIKE N'%{$txt}%' OR lastName LIKE N'%{$txt}%'";
    $rs = $this->ms->query($qr);    
    return $rs->num_rows() > 0 ? $rs->result() : array();
  }
}
