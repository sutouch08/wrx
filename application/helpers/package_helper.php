<?php
function select_active_package($id = NULL)
{
  $ds = "";
  $ci =& get_instance();
  $ci->load->model('masters/package_model');
  $id = empty($id) ? getConfig('DEFAULT_PACKAGE') : $id;
  $active = 1;
  $options = $ci->package_model->get_all($active);

  if( ! empty($options))
  {
    foreach($options as $rs)
    {
      $ds .= '<option value="'.$rs->id.'" '.is_selected($rs->id, $id).'>'.$rs->name.' | '.$rs->width.'x'.$rs->length.'x'.$rs->height.'</option>';
    }
  }

  return $ds;
}
