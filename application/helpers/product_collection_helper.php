<?php
function select_active_collection(?string $code = '')
{
  $ci =& get_instance();
  $ci->load->model('masters/product_collection_model');  
  $result = $ci->product_collection_model->get_all();
  $ds = '';

  if(!empty($result))
  {
    foreach($result as $rs)
    {
      if($rs->active OR $rs->code == $code)
      {
        $ds .= '<option value="'.$rs->code.'" '.is_selected($rs->code, $code).'>'.$rs->code.' | '.$rs->name.'</option>';        
      }
    }
  }

  return $ds;
}

function select_all_collection(?string $code = '')
{
  $ci =& get_instance();
  $ci->load->model('masters/product_collection_model');
  $result = $ci->product_collection_model->get_all();
  $ds = '';

  if(!empty($result))
  {
    foreach($result as $rs)
    {
      $ds .= '<option value="'.$rs->code.'" '.is_selected($rs->code, $code).'>'.$rs->code.' | '.$rs->name.'</option>';
    }
  }

  return $ds;
}