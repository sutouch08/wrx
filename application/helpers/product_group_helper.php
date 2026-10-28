<?php
function select_product_group(?string $code = '')
{
  $ci =& get_instance();
  $ci->load->model('masters/product_group_model');
  $result = $ci->product_group_model->get_data();
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
