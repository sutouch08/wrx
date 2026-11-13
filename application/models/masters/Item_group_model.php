<?php
class Item_group_model extends CI_Model
{
  public function __construct()
  {
    parent::__construct();
  }

  public function get_data()
  {
    $rs = $this->ms
    ->select('ItmsGrpCod AS code, ItmsGrpNam AS name')
    ->where('Locked', 'N')
    ->get('OITB');

    return $rs->num_rows() > 0 ? $rs->result() : NULL;
  }
}
